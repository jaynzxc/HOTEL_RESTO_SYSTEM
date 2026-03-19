<?php
/**
 * POST Controller - Admin Housekeeping & Maintenance Actions
 * Handles updating task status, assigning staff (from HR API), and maintenance requests
 */

// Enable error logging but don't display errors
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

session_start();
require_once __DIR__ . '/../../../Class/Database.php';

// Set JSON header for AJAX response
header('Content-Type: application/json');

try {
    // Check if user is logged in
    if (!isset($_SESSION['user_id']) || !$_SESSION['logged_in']) {
        throw new Exception('Please login to continue');
    }

    $config = require __DIR__ . '/../../../config/config.php';
    $db = new Database($config['database']);

    // Get user role from database
    $user = $db->query(
        "SELECT role FROM users WHERE id = :id",
        ['id' => $_SESSION['user_id']]
    )->fetch_one();

    // Check if user has admin or staff role
    if (!$user || !in_array($user['role'], ['admin', 'staff'])) {
        throw new Exception('Unauthorized access');
    }

    // HR API Configuration
    define('HR_API_BASE', 'https://humanresource.up.railway.app/api');
    define('HR_API_KEY', 'core_system_2026_key_54321');

    // Function to get staff from HR API
    function getHrStaff($employeeId = null)
    {
        $url = HR_API_BASE . '/employee-attendance.php?api_key=' . HR_API_KEY;

        // Always filter by Hotel department
        $url .= '&department=Hotel';

        if ($employeeId) {
            $url .= '&employee_id=' . urlencode($employeeId);
        }

        error_log("HR API Call: " . $url);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200) {
            $data = json_decode($response, true);

            // Check the structure of the response
            if (isset($data['success']) && $data['success'] === true && isset($data['data']['employees'])) {
                return $data['data']['employees'];
            } elseif (isset($data['employees'])) {
                return $data['employees'];
            } elseif (is_array($data)) {
                return $data;
            }
        }

        error_log("HR API returned non-200: " . $httpCode);
        return [];
    }

    // Function to extract employee ID correctly from HR data
    function getEmployeeId($staff)
    {
        // Try different possible ID fields
        if (isset($staff['employee']['employee_number']) && !empty($staff['employee']['employee_number'])) {
            return $staff['employee']['employee_number'];
        }
        if (isset($staff['employee']['id']) && !empty($staff['employee']['id'])) {
            return $staff['employee']['id'];
        }
        if (isset($staff['employee_number']) && !empty($staff['employee_number'])) {
            return $staff['employee_number'];
        }
        if (isset($staff['id']) && !empty($staff['id'])) {
            return $staff['id'];
        }
        return null;
    }

    // Function to get employee full name
    function getEmployeeName($staff)
    {
        if (isset($staff['employee']['full_name']) && !empty($staff['employee']['full_name'])) {
            return $staff['employee']['full_name'];
        }
        if (isset($staff['full_name']) && !empty($staff['full_name'])) {
            return $staff['full_name'];
        }
        return 'Unknown Staff';
    }

    // Check if it's a POST request
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    $action = $_POST['action'] ?? '';

    // UPDATE TASK STATUS
    if ($action === 'update_task_status') {
        $task_id = intval($_POST['task_id'] ?? 0);
        $status = $_POST['status'] ?? '';

        if (!$task_id) {
            throw new Exception('Task ID required');
        }

        $db->beginTransaction();

        if ($status === 'completed' || $status === 'clean') {
            // Mark as completed
            $db->query(
                "UPDATE room_maintenance SET 
                    cleaned_at = NOW(),
                    completed_at = NOW(),
                    updated_at = NOW()
                 WHERE id = :id",
                ['id' => $task_id]
            );

            // Get room_id to update availability
            $task = $db->query(
                "SELECT room_id, assigned_hr_employee_id FROM room_maintenance WHERE id = :id",
                ['id' => $task_id]
            )->fetch_one();

            if ($task) {
                // Check if room has any other pending maintenance
                $pending = $db->query(
                    "SELECT COUNT(*) as count FROM room_maintenance 
                     WHERE room_id = :room_id 
                     AND cleaned_at IS NULL 
                     AND completed_at IS NULL",
                    ['room_id' => $task['room_id']]
                )->fetch_one();

                if ($pending['count'] == 0) {
                    $db->query(
                        "UPDATE rooms SET is_available = 1 WHERE id = :id",
                        ['id' => $task['room_id']]
                    );
                }

                // Create notification for admin
                $db->query(
                    "INSERT INTO notifications (user_id, title, message, type, icon, created_at) 
                     VALUES (:user_id, 'Task Completed', :message, 'success', 'fa-check-circle', NOW())",
                    [
                        'user_id' => $_SESSION['user_id'],
                        'message' => "Task #{$task_id} has been completed"
                    ]
                );
            }
        } elseif ($status === 'in-progress') {
            $db->query(
                "UPDATE room_maintenance SET 
                    updated_at = NOW()
                 WHERE id = :id",
                ['id' => $task_id]
            );
        }

        $db->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Task status updated successfully'
        ]);
        exit();
    }

    // ASSIGN CLEANING TASK TO STAFF
    elseif ($action === 'assign_cleaning_task') {
        $room_number = $_POST['room_number'] ?? '';
        $employee_id = trim($_POST['employee_id'] ?? '');
        $notes = trim($_POST['notes'] ?? 'Room needs cleaning');

        error_log("ASSIGN CLEANING - Room: " . $room_number);
        error_log("ASSIGN CLEANING - Employee ID: " . $employee_id);

        if (empty($room_number) || empty($employee_id)) {
            throw new Exception('Room number and Employee ID required');
        }

        // First, let's get all staff to verify the employee exists
        $allStaff = getHrStaff();
        $foundStaff = null;

        foreach ($allStaff as $staff) {
            $emp = $staff['employee'] ?? $staff;
            $empId = $emp['employee_number'] ?? $emp['id'] ?? '';

            if ($empId == $employee_id) {
                $foundStaff = $staff;
                break;
            }
        }

        if (!$foundStaff) {
            throw new Exception('Staff member not found in HR system');
        }

        $emp = $foundStaff['employee'] ?? $foundStaff;
        $staffName = getEmployeeName($foundStaff);

        $db->beginTransaction();

        // Insert into room_maintenance as a cleaning task
        $db->query(
            "INSERT INTO room_maintenance (
            room_id, condition_status, priority, reported_at, reported_by, 
            assigned_hr_employee_id, notes, created_at, updated_at
        ) VALUES (
            :room_id, 'minor', 'medium', NOW(), :reported_by, 
            :employee_id, :notes, NOW(), NOW()
        )",
            [
                'room_id' => $room_number,
                'reported_by' => $_SESSION['user_id'],
                'employee_id' => $employee_id,
                'notes' => 'Cleaning task: ' . $notes
            ]
        );

        // Create notification for admin
        $db->query(
            "INSERT INTO notifications (user_id, title, message, type, icon, created_at) 
         VALUES (:user_id, 'Cleaning Task Assigned', :message, 'success', 'fa-broom', NOW())",
            [
                'user_id' => $_SESSION['user_id'],
                'message' => "Cleaning task for room {$room_number} assigned to {$staffName}"
            ]
        );

        $db->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Cleaning task assigned successfully to ' . $staffName
        ]);
        exit();
    }
    // GET TASK DETAILS
    elseif ($action === 'get_task_details') {
        $task_id = intval($_POST['task_id'] ?? 0);

        if (!$task_id) {
            throw new Exception('Task ID required');
        }

        $task = $db->query(
            "SELECT rm.*, r.id as room_number, u.full_name as reported_by_name
         FROM room_maintenance rm
         LEFT JOIN rooms r ON rm.room_id = r.id
         LEFT JOIN users u ON rm.reported_by = u.id
         WHERE rm.id = :id",
            ['id' => $task_id]
        )->fetch_one();

        if (!$task) {
            throw new Exception('Task not found');
        }

        // Get assigned staff details from HR API if assigned
        $task['assigned_to_name'] = 'Unassigned';

        if (!empty($task['assigned_hr_employee_id'])) {
            // Try to get specific employee
            $hrStaff = getHrStaff($task['assigned_hr_employee_id']);

            if (!empty($hrStaff)) {
                // Check different possible structures
                if (isset($hrStaff[0]['employee'])) {
                    $staff = $hrStaff[0]['employee'];
                    $task['assigned_to_name'] = $staff['full_name'] ?? 'Unknown Staff';
                } elseif (isset($hrStaff[0]['full_name'])) {
                    $task['assigned_to_name'] = $hrStaff[0]['full_name'];
                } else {
                    $task['assigned_to_name'] = 'Unknown Staff';
                }
            } else {
                // If specific search fails, get all staff and try to find match
                $allStaff = getHrStaff();
                $found = false;

                foreach ($allStaff as $staff) {
                    $emp = $staff['employee'] ?? $staff;
                    $empId = $emp['employee_number'] ?? $emp['id'] ?? '';

                    // Try exact match
                    if ($empId == $task['assigned_hr_employee_id']) {
                        $task['assigned_to_name'] = $emp['full_name'] ?? 'Unknown Staff';
                        $found = true;
                        break;
                    }

                    // Try numeric match
                    $numericId = preg_replace('/[^0-9]/', '', $task['assigned_hr_employee_id']);
                    $empNumericId = preg_replace('/[^0-9]/', '', $empId);

                    if (!empty($numericId) && $numericId === $empNumericId) {
                        $task['assigned_to_name'] = $emp['full_name'] ?? 'Unknown Staff';
                        $found = true;
                        break;
                    }
                }

                if (!$found) {
                    $task['assigned_to_name'] = 'Unknown Staff (ID: ' . $task['assigned_hr_employee_id'] . ')';
                }
            }
        }

        echo json_encode([
            'success' => true,
            'task' => $task
        ]);
        exit();
    }

    // ASSIGN TASK TO STAFF (using HR employee ID)
    elseif ($action === 'assign_task') {
        $task_id = intval($_POST['task_id'] ?? 0);
        $employee_id = trim($_POST['employee_id'] ?? '');

        error_log("ASSIGN TASK - Task ID: " . $task_id);
        error_log("ASSIGN TASK - Employee ID received: " . $employee_id);

        if (!$task_id || empty($employee_id)) {
            throw new Exception('Task ID and Employee ID required');
        }

        // First, let's get all staff to see what IDs are available
        $allStaff = getHrStaff();
        error_log("ASSIGN TASK - Total staff from API: " . count($allStaff));

        // Try to find the staff member by checking different ID fields
        $foundStaff = null;
        $foundById = null;

        foreach ($allStaff as $staff) {
            $emp = $staff['employee'] ?? $staff;

            // Check various ID fields
            $possibleIds = [
                $emp['employee_number'] ?? null,
                $emp['id'] ?? null,
                $emp['emp_id'] ?? null,
                $emp['employee_id'] ?? null,
                $staff['employee_number'] ?? null,
                $staff['id'] ?? null
            ];

            foreach ($possibleIds as $id) {
                if ($id && (string) $id === (string) $employee_id) {
                    $foundStaff = $staff;
                    $foundById = $id;
                    break 2;
                }
            }
        }

        if (!$foundStaff) {
            // Debug: Log all available staff IDs
            $availableIds = [];
            foreach ($allStaff as $staff) {
                $emp = $staff['employee'] ?? $staff;
                $id = $emp['employee_number'] ?? $emp['id'] ?? 'unknown';
                $name = $emp['full_name'] ?? 'unknown';
                $availableIds[] = "$id ($name)";
            }
            error_log("ASSIGN TASK - Available staff IDs: " . implode(', ', $availableIds));

            throw new Exception('Staff member not found in HR system. ID attempted: ' . $employee_id);
        }

        $emp = $foundStaff['employee'] ?? $foundStaff;
        $staffName = getEmployeeName($foundStaff);
        $correctId = $emp['employee_number'] ?? $emp['id'] ?? $employee_id;

        error_log("ASSIGN TASK - Found staff: " . $staffName . " with ID: " . $correctId);

        $db->beginTransaction();

        // First, check if the assigned_hr_employee_id column exists
        try {
            // Try to update using the new column
            $db->query(
                "UPDATE room_maintenance SET 
                    assigned_hr_employee_id = :employee_id,
                    updated_at = NOW()
                 WHERE id = :id",
                [
                    'id' => $task_id,
                    'employee_id' => $correctId
                ]
            );
        } catch (Exception $e) {
            // If column doesn't exist, try to add it
            if (strpos($e->getMessage(), 'Unknown column') !== false) {
                $db->query("ALTER TABLE room_maintenance ADD COLUMN assigned_hr_employee_id varchar(50) DEFAULT NULL AFTER assigned_to");
                // Retry the update
                $db->query(
                    "UPDATE room_maintenance SET 
                        assigned_hr_employee_id = :employee_id,
                        updated_at = NOW()
                     WHERE id = :id",
                    [
                        'id' => $task_id,
                        'employee_id' => $correctId
                    ]
                );
            } else {
                throw $e;
            }
        }

        // Create notification for admin
        $db->query(
            "INSERT INTO notifications (user_id, title, message, type, icon, created_at) 
             VALUES (:user_id, 'Task Assigned', :message, 'success', 'fa-user-check', NOW())",
            [
                'user_id' => $_SESSION['user_id'],
                'message' => "Task #{$task_id} assigned to {$staffName}"
            ]
        );

        $db->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Task assigned successfully to ' . $staffName
        ]);
        exit();
    }

    // GET ALL PENDING TASKS FOR ASSIGNMENT
    elseif ($action === 'get_pending_tasks') {
        $tasks = $db->query(
            "SELECT 
                rm.id,
                rm.room_id,
                rm.notes,
                rm.priority,
                rm.condition_status,
                r.id as room_number,
                CASE 
                    WHEN rm.condition_status = 'damage' THEN 'high'
                    WHEN rm.condition_status = 'maintenance' THEN 'medium'
                    ELSE 'low'
                END as priority_level
             FROM room_maintenance rm
             LEFT JOIN rooms r ON rm.room_id = r.id
             WHERE rm.assigned_hr_employee_id IS NULL 
             AND rm.cleaned_at IS NULL 
             AND rm.completed_at IS NULL
             ORDER BY 
                CASE 
                    WHEN rm.condition_status = 'damage' THEN 1
                    WHEN rm.condition_status = 'maintenance' THEN 2
                    ELSE 3
                END,
                rm.reported_at ASC",
            []
        )->find() ?: [];

        echo json_encode([
            'success' => true,
            'tasks' => $tasks
        ]);
        exit();
    }

    // GET ALL HOTEL STAFF FROM HR API
    elseif ($action === 'get_hotel_staff') {
        $hrStaff = getHrStaff();
        $formattedStaff = [];

        foreach ($hrStaff as $staff) {
            $emp = $staff['employee'] ?? $staff;
            $status = $staff['status'] ?? [];

            // Get the correct ID
            $staffId = $emp['employee_number'] ?? $emp['id'] ?? '';
            $staffName = getEmployeeName($staff);

            $formattedStaff[] = [
                'id' => $staffId,
                'full_name' => $staffName,
                'position' => $emp['position'] ?? 'Staff',
                'department' => $emp['department'] ?? 'Hotel',
                'present' => $status['present'] ?? false,
                'status_message' => $status['message'] ?? 'Unknown',
                'avatar' => null
            ];
        }

        echo json_encode([
            'success' => true,
            'staff' => $formattedStaff
        ]);
        exit();
    }

    // GET HOUSEKEEPING STAFF ONLY
    elseif ($action === 'get_housekeeping_staff') {
        $hrStaff = getHrStaff();
        $housekeepingStaff = [];

        foreach ($hrStaff as $staff) {
            $emp = $staff['employee'] ?? $staff;
            $position = strtolower($emp['position'] ?? '');
            $department = strtolower($emp['department'] ?? '');

            if (
                strpos($position, 'housekeeping') !== false ||
                strpos($position, 'clean') !== false ||
                strpos($position, 'room attendant') !== false ||
                strpos($department, 'housekeeping') !== false
            ) {

                $staffId = $emp['employee_number'] ?? $emp['id'] ?? '';
                $staffName = getEmployeeName($staff);

                $housekeepingStaff[] = [
                    'id' => $staffId,
                    'full_name' => $staffName,
                    'position' => $emp['position'] ?? 'Housekeeping Staff',
                    'present' => $staff['status']['present'] ?? false,
                    'status_message' => $staff['status']['message'] ?? 'Unknown'
                ];
            }
        }

        echo json_encode([
            'success' => true,
            'staff' => $housekeepingStaff
        ]);
        exit();
    }

    // GET MAINTENANCE STAFF ONLY
    elseif ($action === 'get_maintenance_staff') {
        $hrStaff = getHrStaff();
        $maintenanceStaff = [];

        foreach ($hrStaff as $staff) {
            $emp = $staff['employee'] ?? $staff;
            $position = strtolower($emp['position'] ?? '');
            $department = strtolower($emp['department'] ?? '');

            if (
                strpos($position, 'maintenance') !== false ||
                strpos($position, 'technician') !== false ||
                strpos($position, 'engineer') !== false ||
                strpos($department, 'maintenance') !== false ||
                strpos($department, 'engineering') !== false
            ) {

                $staffId = $emp['employee_number'] ?? $emp['id'] ?? '';
                $staffName = getEmployeeName($staff);

                $maintenanceStaff[] = [
                    'id' => $staffId,
                    'full_name' => $staffName,
                    'position' => $emp['position'] ?? 'Maintenance Staff',
                    'present' => $staff['status']['present'] ?? false,
                    'status_message' => $staff['status']['message'] ?? 'Unknown'
                ];
            }
        }

        echo json_encode([
            'success' => true,
            'staff' => $maintenanceStaff
        ]);
        exit();
    }

    // ADD MAINTENANCE REQUEST
    elseif ($action === 'add_maintenance') {
        $room_id = $_POST['room_id'] ?? '';
        $issue = trim($_POST['issue'] ?? '');
        $priority = $_POST['priority'] ?? 'medium';
        $scheduled_date = $_POST['scheduled_date'] ?? '';

        if (empty($room_id) || empty($issue)) {
            throw new Exception('Room ID and issue description required');
        }

        $db->beginTransaction();

        // Convert priority to condition_status
        $condition_status = 'maintenance';
        if ($priority === 'high') {
            $condition_status = 'damage';
        } elseif ($priority === 'low') {
            $condition_status = 'minor';
        }

        $db->query(
            "INSERT INTO room_maintenance (
                room_id, condition_status, priority, reported_at, reported_by, notes, created_at, updated_at
             ) VALUES (
                :room_id, :condition_status, :priority, NOW(), :reported_by, :notes, NOW(), NOW()
             )",
            [
                'room_id' => $room_id,
                'condition_status' => $condition_status,
                'priority' => $priority,
                'reported_by' => $_SESSION['user_id'],
                'notes' => $issue . ($scheduled_date ? " (Scheduled: $scheduled_date)" : "")
            ]
        );

        // Update room availability
        $db->query(
            "UPDATE rooms SET is_available = 0 WHERE id = :id",
            ['id' => $room_id]
        );

        // Create notification for admin
        $db->query(
            "INSERT INTO notifications (user_id, title, message, type, icon, created_at) 
             VALUES (:user_id, 'New Maintenance Request', :message, 'warning', 'fa-wrench', NOW())",
            [
                'user_id' => $_SESSION['user_id'],
                'message' => "New maintenance request for room {$room_id}: {$issue}"
            ]
        );

        $db->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Maintenance request added successfully'
        ]);
        exit();
    }

    // UPDATE SUPPLIES
    elseif ($action === 'update_supplies') {
        $supply_id = intval($_POST['supply_id'] ?? 0);
        $stock = intval($_POST['stock'] ?? 0);

        if (!$supply_id) {
            throw new Exception('Supply ID required');
        }

        if ($stock < 0) {
            throw new Exception('Stock cannot be negative');
        }

        $db->query(
            "UPDATE inventory SET stock = :stock, updated_at = NOW() WHERE id = :id",
            [
                'id' => $supply_id,
                'stock' => $stock
            ]
        );

        // Get supply name for notification
        $supply = $db->query(
            "SELECT item_name FROM inventory WHERE id = :id",
            ['id' => $supply_id]
        )->fetch_one();

        // Create notification for low stock
        if ($stock <= 10) {
            $db->query(
                "INSERT INTO notifications (user_id, title, message, type, icon, created_at) 
                 VALUES (:user_id, 'Low Stock Alert', :message, 'warning', 'fa-box', NOW())",
                [
                    'user_id' => $_SESSION['user_id'],
                    'message' => "{$supply['item_name']} stock is low ({$stock} units)"
                ]
            );
        }

        echo json_encode([
            'success' => true,
            'message' => 'Supply stock updated successfully'
        ]);
        exit();
    }

    // COMPLETE MAINTENANCE
    elseif ($action === 'complete_maintenance') {
        $maintenance_id = intval($_POST['maintenance_id'] ?? 0);

        if (!$maintenance_id) {
            throw new Exception('Maintenance ID required');
        }

        $db->beginTransaction();

        // Get room_id and assigned_to before completing
        $maintenance = $db->query(
            "SELECT room_id, assigned_hr_employee_id FROM room_maintenance WHERE id = :id",
            ['id' => $maintenance_id]
        )->fetch_one();

        $db->query(
            "UPDATE room_maintenance SET 
                cleaned_at = NOW(), 
                completed_at = NOW(),
                updated_at = NOW()
             WHERE id = :id",
            ['id' => $maintenance_id]
        );

        if ($maintenance) {
            // Check if room has any other pending maintenance
            $pending = $db->query(
                "SELECT COUNT(*) as count FROM room_maintenance 
                 WHERE room_id = :room_id 
                 AND cleaned_at IS NULL 
                 AND completed_at IS NULL",
                ['room_id' => $maintenance['room_id']]
            )->fetch_one();

            // If no other pending maintenance, make room available
            if ($pending['count'] == 0) {
                $db->query(
                    "UPDATE rooms SET is_available = 1 WHERE id = :id",
                    ['id' => $maintenance['room_id']]
                );
            }

            // Notify admin of completion
            $db->query(
                "INSERT INTO notifications (user_id, title, message, type, icon, created_at) 
                 VALUES (:user_id, 'Task Completed', :message, 'success', 'fa-check-circle', NOW())",
                [
                    'user_id' => $_SESSION['user_id'],
                    'message' => "Maintenance task for room {$maintenance['room_id']} has been completed"
                ]
            );
        }

        $db->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Maintenance marked as completed'
        ]);
        exit();
    } else {
        throw new Exception('Invalid action');
    }

} catch (Exception $e) {
    // Log the error
    error_log('POST Controller Error: ' . $e->getMessage());
    error_log('POST Data: ' . print_r($_POST, true));

    // Return JSON error response
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
    exit();
}
?>