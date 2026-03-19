<?php
/**
 * GET Controller - Admin Housekeeping & Maintenance
 * Handles fetching housekeeping tasks, maintenance requests, and staff data from HR API
 */

session_start();
require_once __DIR__ . '/../../../Class/Database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !$_SESSION['logged_in']) {
    header('Location: ../../view/auth/login.php');
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
    header('Location: ../../view/customer_portal/dashboard.php');
    exit();
}

// Get admin user data
$admin = $db->query(
    "SELECT id, full_name, first_name, last_name, email, role, avatar
     FROM users WHERE id = :id",
    ['id' => $_SESSION['user_id']]
)->fetch_one();

// HR API Configuration
define('HR_API_BASE', 'https://humanresource.up.railway.app/api');
define('HR_API_KEY', 'core_system_2026_key_54321');

// Set timezone to Asia/Manila
date_default_timezone_set('Asia/Manila');

// Function to call HR API
function callHrApi($endpoint, $params = [])
{
    $url = HR_API_BASE . $endpoint . '?api_key=' . HR_API_KEY;

    foreach ($params as $key => $value) {
        if (!empty($value)) {
            $url .= '&' . urlencode($key) . '=' . urlencode($value);
        }
    }

    error_log("Calling HR API: " . $url);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($curlError) {
        error_log("HR API CURL Error: " . $curlError);
        return ['success' => false, 'error' => $curlError];
    }

    if ($httpCode !== 200) {
        error_log("HR API Error: HTTP $httpCode");
        return ['success' => false, 'error' => "HTTP $httpCode"];
    }

    return json_decode($response, true);
}

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

function getEmployeeId($staff)
{
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

// Get filter parameters
$statusFilter = isset($_GET['status']) ? $_GET['status'] : 'all';
$searchFilter = isset($_GET['search']) ? $_GET['search'] : '';
$date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

// Fetch Hotel department staff from HR API
$apiParams = [
    'department' => 'Hotel',
    'date' => $date
];

$hrData = callHrApi('/employee-attendance.php', $apiParams);
$hrEmployees = [];
$hrApiConnected = false;

if ($hrData && isset($hrData['success']) && $hrData['success'] === true && isset($hrData['data']['employees'])) {
    $hrEmployees = $hrData['data']['employees'];
    $hrApiConnected = true;
    error_log("Found " . count($hrEmployees) . " employees in Hotel department");
} else {
    error_log("HR API connection failed or returned invalid data");
}

// Process Hotel staff and categorize by role/position
$hotelStaff = [];
$housekeepingStaff = [];
$maintenanceStaff = [];
$frontDeskStaff = [];
$allStaff = [];
$staffLookup = [];

foreach ($hrEmployees as $staff) {
    $emp = $staff['employee'] ?? $staff;
    $position = strtolower($emp['position'] ?? '');
    $department = strtolower($emp['department'] ?? '');

    $staffId = getEmployeeId($staff);
    $staffName = getEmployeeName($staff);

    if (!$staffId) {
        error_log("Staff has no ID: " . print_r($staff, true));
        continue;
    }

    $staffObject = [
        'id' => $staffId,
        'full_name' => $staffName,
        'first_name' => $emp['first_name'] ?? '',
        'last_name' => $emp['last_name'] ?? '',
        'position' => $emp['position'] ?? 'Staff',
        'department' => $emp['department'] ?? 'Hotel',
        'email' => $emp['email'] ?? '',
        'phone' => $emp['phone'] ?? '',
        'status' => $staff['status'] ?? ['present' => false, 'status' => 'absent'],
        'schedule' => $staff['schedule'] ?? null,
        'attendance' => $staff['attendance'] ?? null,
        'avatar' => null,
        'hourly_rate' => $emp['hourly_rate'] ?? 0
    ];

    $staffLookup[$staffId] = $staffObject;
    $numericId = preg_replace('/[^0-9]/', '', $staffId);
    if (!empty($numericId) && $numericId != $staffId) {
        $staffLookup[$numericId] = $staffObject;
    }

    $allStaff[] = $staffObject;

    if (
        strpos($position, 'housekeeping') !== false ||
        strpos($position, 'clean') !== false ||
        strpos($position, 'room attendant') !== false ||
        strpos($position, 'maid') !== false ||
        strpos($position, 'houseman') !== false ||
        strpos($department, 'housekeeping') !== false
    ) {
        $housekeepingStaff[] = $staffObject;
    }

    if (
        strpos($position, 'maintenance') !== false ||
        strpos($position, 'technician') !== false ||
        strpos($position, 'repair') !== false ||
        strpos($position, 'engineer') !== false ||
        strpos($position, 'electrician') !== false ||
        strpos($position, 'plumber') !== false ||
        strpos($department, 'maintenance') !== false ||
        strpos($department, 'engineering') !== false
    ) {
        $maintenanceStaff[] = $staffObject;
    }

    if (
        strpos($position, 'front desk') !== false ||
        strpos($position, 'reception') !== false ||
        strpos($position, 'concierge') !== false
    ) {
        $frontDeskStaff[] = $staffObject;
    }

    $hotelStaff[] = $staffObject;
}

error_log("Staff lookup created with " . count($staffLookup) . " entries");

// Get all rooms with housekeeping status
$rooms = $db->query(
    "SELECT 
        r.id as room_number,
        r.name as room_type,
        CASE 
            WHEN rm.id IS NOT NULL AND rm.cleaned_at IS NULL AND rm.completed_at IS NULL THEN 'maintenance'
            WHEN b.id IS NOT NULL AND b.status IN ('confirmed', 'checked-in') AND b.check_out >= CURDATE() THEN 'occupied'
            WHEN r.needs_cleaning = 1 THEN 'dirty'
            ELSE 'available'
        END as room_status,
        COALESCE(rm.condition_status, 'good') as condition_status,
        rm.notes as maintenance_notes,
        rm.reported_at,
        rm.id as maintenance_id,
        rm.assigned_hr_employee_id,
        b.id as booking_id,
        CONCAT(b.guest_first_name, ' ', b.guest_last_name) as current_guest,
        r.needs_cleaning
     FROM rooms r
     LEFT JOIN room_maintenance rm ON r.id = rm.room_id AND rm.cleaned_at IS NULL AND rm.completed_at IS NULL
     LEFT JOIN bookings b ON r.id = b.room_id 
        AND b.status IN ('confirmed', 'checked-in')
        AND b.check_out >= CURDATE()
     ORDER BY 
        CASE 
            WHEN r.needs_cleaning = 1 THEN 1
            WHEN rm.condition_status = 'damage' THEN 2
            WHEN rm.condition_status = 'maintenance' THEN 3
            ELSE 4
        END,
        r.id ASC",
    []
)->find() ?: [];

// Get DIRTY ROOMS specifically
$dirtyRooms = $db->query(
    "SELECT 
        r.id as room_number,
        r.name as room_type,
        'dirty' as room_status,
        NULL as condition_status,
        NULL as maintenance_notes,
        NULL as reported_at,
        NULL as maintenance_id,
        NULL as assigned_hr_employee_id,
        NULL as booking_id,
        NULL as current_guest,
        r.needs_cleaning
     FROM rooms r
     WHERE r.needs_cleaning = 1
     AND NOT EXISTS (
        SELECT 1 FROM room_maintenance rm 
        WHERE rm.room_id = r.id 
        AND rm.cleaned_at IS NULL 
        AND rm.completed_at IS NULL
     )
     AND NOT EXISTS (
        SELECT 1 FROM bookings b 
        WHERE b.room_id = r.id 
        AND b.status IN ('confirmed', 'checked-in')
        AND b.check_out >= CURDATE()
     )
     ORDER BY r.id ASC",
    []
)->find() ?: [];

// Get housekeeping tasks from room_maintenance ONLY (no dirty rooms here)
$housekeepingTasks = $db->query(
    "SELECT 
        rm.*,
        r.id as room_number,
        r.name as room_type,
        u.full_name as reported_by_name,
        rm.assigned_hr_employee_id,
        CASE 
            WHEN rm.condition_status = 'damage' THEN 'high'
            WHEN rm.condition_status = 'maintenance' THEN 'medium'
            ELSE 'low'
        END as priority,
        CASE
            WHEN rm.completed_at IS NOT NULL THEN 'completed'
            WHEN rm.cleaned_at IS NOT NULL THEN 'clean'
            WHEN rm.condition_status = 'damage' THEN 'maintenance'
            ELSE 'pending'
        END as task_status
     FROM room_maintenance rm
     LEFT JOIN rooms r ON rm.room_id = r.id
     LEFT JOIN users u ON rm.reported_by = u.id
     ORDER BY 
        CASE 
            WHEN rm.condition_status = 'damage' THEN 1
            WHEN rm.condition_status = 'maintenance' THEN 2
            ELSE 3
        END,
        rm.reported_at DESC",
    []
)->find() ?: [];

// Enrich tasks with HR staff names
foreach ($housekeepingTasks as &$task) {
    $task['assigned_to_name'] = '—';
    $task['assigned_to_details'] = null;

    if (!empty($task['assigned_hr_employee_id'])) {
        $assignedId = trim($task['assigned_hr_employee_id']);

        if (isset($staffLookup[$assignedId])) {
            $task['assigned_to_name'] = $staffLookup[$assignedId]['full_name'];
            $task['assigned_to_details'] = $staffLookup[$assignedId];
        } else {
            $numericId = preg_replace('/[^0-9]/', '', $assignedId);
            if (!empty($numericId) && isset($staffLookup[$numericId])) {
                $task['assigned_to_name'] = $staffLookup[$numericId]['full_name'];
                $task['assigned_to_details'] = $staffLookup[$numericId];
            }
        }
    }
}

// Get maintenance requests (pending only)
$maintenanceRequests = $db->query(
    "SELECT 
        rm.*,
        r.id as room_number,
        u.full_name as reported_by_name,
        rm.assigned_hr_employee_id,
        CASE 
            WHEN rm.condition_status = 'damage' THEN 'urgent'
            WHEN rm.condition_status = 'maintenance' THEN 'high'
            ELSE 'normal'
        END as priority_level
     FROM room_maintenance rm
     LEFT JOIN rooms r ON rm.room_id = r.id
     LEFT JOIN users u ON rm.reported_by = u.id
     WHERE rm.cleaned_at IS NULL AND rm.completed_at IS NULL
     ORDER BY 
        CASE 
            WHEN rm.condition_status = 'damage' THEN 1
            WHEN rm.condition_status = 'maintenance' THEN 2
            ELSE 3
        END,
        rm.reported_at ASC",
    []
)->find() ?: [];

// Enrich maintenance requests with HR staff names
foreach ($maintenanceRequests as &$request) {
    $request['assigned_to_name'] = '—';
    if (!empty($request['assigned_hr_employee_id'])) {
        $assignedId = trim($request['assigned_hr_employee_id']);

        if (isset($staffLookup[$assignedId])) {
            $request['assigned_to_name'] = $staffLookup[$assignedId]['full_name'];
        } else {
            $numericId = preg_replace('/[^0-9]/', '', $assignedId);
            if (!empty($numericId) && isset($staffLookup[$numericId])) {
                $request['assigned_to_name'] = $staffLookup[$numericId]['full_name'];
            }
        }
    }
}

// Calculate statistics
$staffOnDuty = 0;
foreach ($allStaff as $staff) {
    if (isset($staff['status']['present']) && $staff['status']['present'] === true) {
        $staffOnDuty++;
    }
}

// Calculate room stats
$clean_rooms = 0;
$dirty_rooms = 0;
$occupied_rooms = 0;
$maintenance_rooms = 0;

foreach ($rooms as $room) {
    if ($room['room_status'] === 'available') {
        $clean_rooms++;
    } elseif ($room['room_status'] === 'occupied') {
        $occupied_rooms++;
    } elseif ($room['room_status'] === 'dirty') {
        $dirty_rooms++;
    } elseif ($room['room_status'] === 'maintenance') {
        $maintenance_rooms++;
    }
}

// Calculate in_progress from tasks
$in_progress = 0;
foreach ($housekeepingTasks as $task) {
    if ($task['task_status'] === 'pending' || $task['task_status'] === 'in-progress') {
        $in_progress++;
    }
}

$stats = [
    'clean' => $clean_rooms,
    'dirty' => $dirty_rooms,
    'occupied' => $occupied_rooms,
    'in_progress' => $in_progress,
    'maintenance' => $maintenance_rooms,
    'staff_on_duty' => $staffOnDuty,
    'total_hotel_staff' => count($allStaff),
    'housekeeping_staff' => count($housekeepingStaff),
    'maintenance_staff' => count($maintenanceStaff)
];

// Get linen and supplies inventory
$supplies = $db->query(
    "SELECT id, item_name, stock, reorder_level, unit
     FROM inventory
     WHERE category IN ('Food', 'Supply')
     ORDER BY 
        CASE 
            WHEN stock <= reorder_level THEN 1
            ELSE 2
        END,
        item_name ASC",
    []
)->find() ?: [];

// Get floor status summary
$floorStats = $db->query(
    "SELECT 
        LEFT(r.id, 1) as floor,
        COUNT(*) as total_rooms,
        SUM(CASE WHEN rm.id IS NOT NULL AND rm.cleaned_at IS NULL AND rm.completed_at IS NULL THEN 1 ELSE 0 END) as maintenance,
        SUM(CASE WHEN b.id IS NOT NULL AND b.status IN ('confirmed', 'checked-in') AND b.check_out >= CURDATE() THEN 1 ELSE 0 END) as occupied,
        SUM(CASE WHEN r.needs_cleaning = 1 AND b.id IS NULL AND rm.id IS NULL THEN 1 ELSE 0 END) as dirty
     FROM rooms r
     LEFT JOIN room_maintenance rm ON r.id = rm.room_id AND rm.cleaned_at IS NULL AND rm.completed_at IS NULL
     LEFT JOIN bookings b ON r.id = b.room_id 
        AND b.status IN ('confirmed', 'checked-in')
        AND b.check_out >= CURDATE()
     GROUP BY LEFT(r.id, 1)
     ORDER BY floor ASC",
    []
)->find() ?: [];

// Get admin initials
$initials = 'A';
if ($admin) {
    $first_name = $admin['first_name'] ?? '';
    $last_name = $admin['last_name'] ?? '';
    $full_name = $admin['full_name'] ?? 'Admin';

    if (!empty($first_name) && !empty($last_name)) {
        $initials = strtoupper(substr($first_name, 0, 1) . substr($last_name, 0, 1));
    } elseif (!empty($full_name)) {
        $name_parts = explode(' ', trim($full_name), 2);
        $initials = strtoupper(substr($name_parts[0], 0, 1));
        if (isset($name_parts[1])) {
            $initials .= strtoupper(substr($name_parts[1], 0, 1));
        }
    }
}



// Store data for view
$viewData = [
    'admin' => $admin,
    'initials' => $initials,
    'rooms' => $rooms,
    'dirtyRooms' => $dirtyRooms,
    'housekeepingTasks' => $housekeepingTasks,
    'maintenanceRequests' => $maintenanceRequests,
    'allStaff' => $allStaff,
    'hotelStaff' => $hotelStaff,
    'housekeepingStaff' => $housekeepingStaff,
    'maintenanceStaff' => $maintenanceStaff,
    'frontDeskStaff' => $frontDeskStaff,
    'supplies' => $supplies,
    'floorStats' => $floorStats,
    'stats' => $stats,
    'statusFilter' => $statusFilter,
    'searchFilter' => $searchFilter,
    
    'today' => date('F j, Y'),
    'api_date' => $date,
    'hr_api_connected' => $hrApiConnected,
    'totalHotelStaff' => count($allStaff)
];

extract($viewData);
?>