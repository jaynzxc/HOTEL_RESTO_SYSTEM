<?php
/**
 * GET Controller - Admin Housekeeping & Maintenance
 * Handles fetching housekeeping tasks, maintenance requests, and staff data
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

// Get filter parameters
$statusFilter = isset($_GET['status']) ? $_GET['status'] : 'all';
$searchFilter = isset($_GET['search']) ? $_GET['search'] : '';

// Get all rooms with housekeeping status
$rooms = $db->query(
    "SELECT 
        r.id as room_number,
        r.name as room_type,
        CASE 
            WHEN rm.id IS NOT NULL AND rm.cleaned_at IS NULL AND rm.completed_at IS NULL THEN 
                CASE 
                    WHEN rm.condition_status = 'damage' THEN 'maintenance'
                    ELSE 'maintenance'
                END
            WHEN b.id IS NOT NULL AND b.status IN ('confirmed', 'checked-in') THEN 'occupied'
            ELSE 'available'
        END as room_status,
        COALESCE(rm.condition_status, 'good') as condition_status,
        rm.notes as maintenance_notes,
        rm.reported_at,
        rm.id as maintenance_id,
        b.id as booking_id,
        CONCAT(b.guest_first_name, ' ', b.guest_last_name) as current_guest
     FROM rooms r
     LEFT JOIN room_maintenance rm ON r.id = rm.room_id AND rm.cleaned_at IS NULL AND rm.completed_at IS NULL
     LEFT JOIN bookings b ON r.id = b.room_id 
        AND b.status IN ('confirmed', 'checked-in')
        AND b.check_out >= CURDATE()
     ORDER BY r.id ASC",
    []
)->find() ?: [];

// Get housekeeping tasks from room_maintenance - FIXED: removed assigned_to reference
$housekeepingTasks = $db->query(
    "SELECT 
        rm.*,
        r.id as room_number,
        r.name as room_type,
        u.full_name as reported_by_name,
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

// Get maintenance requests (pending only)
$maintenanceRequests = $db->query(
    "SELECT 
        rm.*,
        r.id as room_number,
        u.full_name as reported_by_name,
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

// Get housekeeping staff (users with staff role)
$staff = $db->query(
    "SELECT id, full_name, first_name, last_name, email, role
     FROM users 
     WHERE role IN ('admin', 'staff')
     ORDER BY full_name ASC",
    []
)->find() ?: [];

// Get statistics
$stats = [
    'clean' => 0,
    'dirty' => 0,
    'in_progress' => 0,
    'maintenance' => 0,
    'staff_on_duty' => count($staff)
];

foreach ($rooms as $room) {
    if ($room['room_status'] === 'available') {
        $stats['clean']++;
    } elseif ($room['room_status'] === 'occupied') {
        $stats['dirty']++;
    }
}

foreach ($housekeepingTasks as $task) {
    if ($task['task_status'] === 'pending') {
        $stats['in_progress']++;
    } elseif ($task['task_status'] === 'maintenance') {
        $stats['maintenance']++;
    }
}

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
        SUM(CASE WHEN b.id IS NOT NULL AND b.status IN ('confirmed', 'checked-in') THEN 1 ELSE 0 END) as occupied
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

// Get unread notifications count
try {
    $unread_result = $db->query(
        "SELECT COUNT(*) as count FROM notifications 
         WHERE user_id = :user_id AND is_read = 0",
        ['user_id' => $_SESSION['user_id']]
    )->fetch_one();
    $unread_count = $unread_result['count'] ?? 0;
} catch (Exception $e) {
    $unread_count = 0;
}

// Store data for view
$viewData = [
    'admin' => $admin,
    'initials' => $initials,
    'rooms' => $rooms,
    'housekeepingTasks' => $housekeepingTasks,
    'maintenanceRequests' => $maintenanceRequests,
    'staff' => $staff,
    'supplies' => $supplies,
    'floorStats' => $floorStats,
    'stats' => $stats,
    'statusFilter' => $statusFilter,
    'searchFilter' => $searchFilter,
    'unread_count' => $unread_count,
    'today' => date('F j, Y')
];

// Extract variables for view
extract($viewData);
?>