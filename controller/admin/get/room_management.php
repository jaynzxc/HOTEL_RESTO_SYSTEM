<?php
/**
 * GET Controller - Admin Room Management
 * Handles fetching all rooms with statistics and maintenance schedules
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
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Build WHERE clause for rooms
$whereConditions = ["1=1"];
$queryParams = [];

if ($statusFilter !== 'all') {
    $whereConditions[] = "r.is_available = :status";
    // Convert filter to is_available value
    $statusValue = 1; // available by default
    switch ($statusFilter) {
        case 'available':
            $statusValue = 1;
            break;
        case 'occupied':
        case 'dirty':
        case 'out of order':
            $statusValue = 0;
            break;
    }
    $queryParams['status'] = $statusValue;
}

if (!empty($searchFilter)) {
    $whereConditions[] = "(r.id LIKE :search1 OR r.name LIKE :search2)";
    $searchTerm = '%' . $searchFilter . '%';
    $queryParams['search1'] = $searchTerm;
    $queryParams['search2'] = $searchTerm;
}

$whereClause = implode(' AND ', $whereConditions);

// Get total count for pagination
$countQuery = "SELECT COUNT(*) as total FROM rooms r WHERE $whereClause";
$countResult = $db->query($countQuery, $queryParams)->fetch_one();
$totalRooms = $countResult['total'];
$totalPages = ceil($totalRooms / $limit);

// Get all rooms with current booking info
$rooms = $db->query(
    "SELECT 
        r.id as room_id,
        r.id as room_number,
        r.name as type,
        r.price,
        r.max_occupancy,
        r.beds,
        r.view,
        r.amenities,
        r.is_available,
        CASE 
            WHEN r.is_available = 1 THEN 'available'
            ELSE 'occupied'
        END as status,
        'clean' as housekeeping, -- Default, you can add housekeeping table later
        CONCAT(b.guest_first_name, ' ', b.guest_last_name) as guest,
        b.check_in,
        b.check_out,
        b.booking_reference
     FROM rooms r
     LEFT JOIN bookings b ON r.id = b.room_id 
        AND b.status IN ('confirmed', 'checked-in')
        AND b.check_out >= CURDATE()
     WHERE $whereClause
     ORDER BY r.id ASC
     LIMIT $limit OFFSET $offset",
    $queryParams
)->find() ?: [];

// Get room statistics
$stats = $db->query(
    "SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN is_available = 1 THEN 1 ELSE 0 END) as available,
        SUM(CASE WHEN is_available = 0 THEN 1 ELSE 0 END) as occupied
     FROM rooms",
    []
)->fetch_one();

// Get maintenance schedules
// Get maintenance schedules - FIXED: Removed completed_at reference
$maintenanceItems = $db->query(
    "SELECT 
        rm.*,
        r.id as room_number,
        u.full_name as reported_by_name
     FROM room_maintenance rm
     LEFT JOIN rooms r ON rm.room_id = r.id
     LEFT JOIN users u ON rm.reported_by = u.id
     WHERE rm.cleaned_at IS NULL  -- Using cleaned_at instead of completed_at
     ORDER BY rm.reported_at ASC
     LIMIT 10",
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
    'stats' => $stats,
    'maintenanceItems' => $maintenanceItems,
    'totalRooms' => $totalRooms,
    'totalPages' => $totalPages,
    'currentPage' => $page,
    'statusFilter' => $statusFilter,
    'searchFilter' => $searchFilter,
    'unread_count' => $unread_count,
    'today' => date('F j, Y')
];

// Extract variables for view
extract($viewData);
?>