<?php
/**
 * GET Controller - Admin Reservations & Booking
 * Handles fetching all reservations with statistics
 */

session_start();
require_once __DIR__ . '/../../../Class/Database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !$_SESSION['logged_in']) {
    header('Location: ../../../src/login-register/login_form.php');
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

// Build WHERE clause
$whereConditions = ["1=1"];
$queryParams = [];

if ($statusFilter !== 'all') {
    $whereConditions[] = "b.status = :status";
    $queryParams['status'] = $statusFilter;
}

if (!empty($searchFilter)) {
    $whereConditions[] = "(b.booking_reference LIKE :search1 OR CONCAT(b.guest_first_name, ' ', b.guest_last_name) LIKE :search2)";
    $searchTerm = '%' . $searchFilter . '%';
    $queryParams['search1'] = $searchTerm;
    $queryParams['search2'] = $searchTerm;
}

$whereClause = implode(' AND ', $whereConditions);

// Get total count for pagination
$countQuery = "SELECT COUNT(*) as total FROM bookings b WHERE $whereClause";
$countResult = $db->query($countQuery, $queryParams)->fetch_one();
$totalReservations = $countResult['total'];
$totalPages = ceil($totalReservations / $limit);

// Get all reservations - FIXED: Removed SQL comments
$reservations = $db->query(
    "SELECT 
        b.id,
        b.user_id,
        b.booking_reference as bookingNo,
        CONCAT(b.guest_first_name, ' ', b.guest_last_name) as guest,
        b.room_name as room,
        b.room_assigned as roomAssigned,
        b.check_in as checkIn,
        b.check_out as checkOut,
        b.nights,
        b.adults,
        b.children,
        b.total_amount as amount,
        b.payment_status,
        b.status,
        b.points_earned,
        b.points_awarded,
        b.special_requests,
        b.created_at,
        u.member_tier,
        u.loyalty_points
     FROM bookings b
     LEFT JOIN users u ON b.user_id = u.id
     WHERE $whereClause
     ORDER BY 
        CASE 
            WHEN b.status = 'pending' THEN 1
            WHEN b.status = 'confirmed' THEN 2
            WHEN b.status = 'checked-in' THEN 3
            WHEN b.status = 'completed' THEN 4
            WHEN b.status = 'cancelled' THEN 5
            ELSE 6
        END,
        b.check_in ASC
     LIMIT $limit OFFSET $offset",
    $queryParams
)->find() ?: [];

// Get statistics
$stats = $db->query(
    "SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN status = 'checked-in' THEN 1 ELSE 0 END) as checked_in,
        SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled,
        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed
     FROM bookings",
    []
)->fetch_one();

// If no stats, provide defaults
if (!$stats) {
    $stats = [
        'total' => 0,
        'confirmed' => 0,
        'pending' => 0,
        'checked_in' => 0,
        'cancelled' => 0,
        'completed' => 0
    ];
}

// Get today's arrivals and departures
$today = date('Y-m-d');
$todayStats = $db->query(
    "SELECT 
        SUM(CASE WHEN check_in = :today THEN 1 ELSE 0 END) as arrivals,
        SUM(CASE WHEN check_out = :today THEN 1 ELSE 0 END) as departures,
        SUM(CASE WHEN check_in = :today AND status = 'pending' THEN 1 ELSE 0 END) as pending_arrivals,
        SUM(CASE WHEN check_out = :today AND status = 'checked-in' THEN 1 ELSE 0 END) as pending_departures
     FROM bookings",
    ['today' => $today]
)->fetch_one();

// Get recent activity
$recentActivity = $db->query(
    "SELECT 
        b.booking_reference as booking,
        CONCAT(COALESCE(b.guest_first_name, ''), ' ', COALESCE(b.guest_last_name, '')) as guest,
        b.status as action,
        b.updated_at as time,
        TIMESTAMPDIFF(MINUTE, b.updated_at, NOW()) as minutes_ago
     FROM bookings b
     WHERE b.updated_at IS NOT NULL
     ORDER BY b.updated_at DESC
     LIMIT 10",
    []
)->find() ?: [];

// Get available rooms for quick booking
$availableRooms = $db->query(
    "SELECT id, name, price, max_occupancy 
     FROM rooms 
     WHERE is_available = 1 
     ORDER BY name ASC",
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
    'reservations' => $reservations,
    'stats' => $stats,
    'todayStats' => $todayStats,
    'recentActivity' => $recentActivity,
    'availableRooms' => $availableRooms,
    'totalReservations' => $totalReservations,
    'totalPages' => $totalPages,
    'currentPage' => $page,
    'statusFilter' => $statusFilter,
    'searchFilter' => $searchFilter,
    
    'today' => $today,
    'limit' => $limit
];

// Extract variables for view
extract($viewData);
?>