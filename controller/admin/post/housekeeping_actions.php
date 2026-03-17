<?php
/**
 * POST Controller - Admin Housekeeping & Maintenance Actions
 * Handles updating task status, assigning staff, and maintenance requests
 */

session_start();
require_once __DIR__ . '/../../../Class/Database.php';

// Set JSON header for AJAX response
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !$_SESSION['logged_in']) {
    echo json_encode([
        'success' => false,
        'message' => 'Please login to continue'
    ]);
    exit();
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
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access'
    ]);
    exit();
}

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
    exit();
}

try {
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
                "SELECT room_id FROM room_maintenance WHERE id = :id",
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

        echo json_encode([
            'success' => true,
            'task' => $task
        ]);
        exit();
    }

    // ASSIGN TASK TO STAFF
    elseif ($action === 'assign_task') {
        $task_id = intval($_POST['task_id'] ?? 0);
        $staff_id = intval($_POST['staff_id'] ?? 0);

        if (!$task_id || !$staff_id) {
            throw new Exception('Task ID and Staff ID required');
        }

        // Verify staff exists
        $staff = $db->query(
            "SELECT id, full_name FROM users WHERE id = :id AND role IN ('admin', 'staff')",
            ['id' => $staff_id]
        )->fetch_one();

        if (!$staff) {
            throw new Exception('Staff member not found');
        }

        $db->beginTransaction();

        // Add assigned_to column to room_maintenance if not exists
        try {
            $db->query("ALTER TABLE room_maintenance ADD COLUMN IF NOT EXISTS assigned_to int(10) UNSIGNED DEFAULT NULL AFTER reported_by");
        } catch (Exception $e) {
            // Column might already exist
        }

        $db->query(
            "UPDATE room_maintenance SET 
                assigned_to = :staff_id,
                updated_at = NOW()
             WHERE id = :id",
            [
                'id' => $task_id,
                'staff_id' => $staff_id
            ]
        );

        // Create notification for staff
        $db->query(
            "INSERT INTO notifications (user_id, title, message, type, icon, created_at) 
             VALUES (:user_id, 'New Task Assigned', :message, 'info', 'fa-broom', NOW())",
            [
                'user_id' => $staff_id,
                'message' => "A new maintenance task has been assigned to you"
            ]
        );

        // Create notification for admin
        $db->query(
            "INSERT INTO notifications (user_id, title, message, type, icon, created_at) 
             VALUES (:user_id, 'Task Assigned', :message, 'success', 'fa-user-check', NOW())",
            [
                'user_id' => $_SESSION['user_id'],
                'message' => "Task #{$task_id} assigned to {$staff['full_name']}"
            ]
        );

        $db->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Task assigned successfully to ' . $staff['full_name']
        ]);
        exit();
    }

    // GET ALL TASKS FOR ASSIGNMENT
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
             WHERE rm.assigned_to IS NULL 
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

    // GET ALL STAFF WITH WORKLOAD
    elseif ($action === 'get_staff_with_workload') {
        $staff = $db->query(
            "SELECT 
                u.id,
                u.full_name,
                u.role,
                COUNT(rm.id) as assigned_tasks,
                SUM(CASE WHEN rm.condition_status = 'damage' THEN 1 ELSE 0 END) as urgent_tasks
             FROM users u
             LEFT JOIN room_maintenance rm ON u.id = rm.assigned_to 
                AND rm.cleaned_at IS NULL 
                AND rm.completed_at IS NULL
             WHERE u.role IN ('admin', 'staff')
             GROUP BY u.id
             ORDER BY assigned_tasks ASC, u.full_name ASC",
            []
        )->find() ?: [];

        echo json_encode([
            'success' => true,
            'staff' => $staff
        ]);
        exit();
    }

    // UPDATE STAFF STATUS (on/off duty)
    elseif ($action === 'update_staff_status') {
        $staff_id = intval($_POST['staff_id'] ?? 0);
        $status = $_POST['status'] ?? '';

        if (!$staff_id) {
            throw new Exception('Staff ID required');
        }

        if (!in_array($status, ['on_duty', 'off_duty', 'break'])) {
            throw new Exception('Invalid status');
        }

        // You would need a staff_status table or add column to users
        // For now, we'll create a notification
        $db->query(
            "INSERT INTO notifications (user_id, title, message, type, icon, created_at) 
             VALUES (:user_id, 'Staff Status Updated', :message, 'info', 'fa-user-clock', NOW())",
            [
                'user_id' => $_SESSION['user_id'],
                'message' => "Staff member status updated to {$status}"
            ]
        );

        echo json_encode([
            'success' => true,
            'message' => 'Staff status updated successfully'
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

        // Create notification for all staff
        $staff_list = $db->query(
            "SELECT id FROM users WHERE role IN ('admin', 'staff')",
            []
        )->find() ?: [];

        foreach ($staff_list as $staff_member) {
            $db->query(
                "INSERT INTO notifications (user_id, title, message, type, icon, created_at) 
                 VALUES (:user_id, 'New Maintenance Request', :message, 'warning', 'fa-wrench', NOW())",
                [
                    'user_id' => $staff_member['id'],
                    'message' => "New maintenance request for room {$room_id}: {$issue}"
                ]
            );
        }

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

    // GET ALL STAFF
    elseif ($action === 'get_staff') {
        $staff = $db->query(
            "SELECT id, full_name, first_name, last_name, role
             FROM users 
             WHERE role IN ('admin', 'staff')
             ORDER BY full_name ASC",
            []
        )->find() ?: [];

        echo json_encode([
            'success' => true,
            'staff' => $staff
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
            "SELECT room_id, assigned_to FROM room_maintenance WHERE id = :id",
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

            // Notify assigned staff if any
            if ($maintenance['assigned_to']) {
                $db->query(
                    "INSERT INTO notifications (user_id, title, message, type, icon, created_at) 
                     VALUES (:user_id, 'Task Completed', :message, 'success', 'fa-check-circle', NOW())",
                    [
                        'user_id' => $maintenance['assigned_to'],
                        'message' => "Your assigned task for room {$maintenance['room_id']} has been marked as completed"
                    ]
                );
            }
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
    if (isset($db)) {
        $db->rollBack();
    }
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
exit();
?>