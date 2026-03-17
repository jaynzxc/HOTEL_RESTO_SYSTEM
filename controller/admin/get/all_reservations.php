<?php
/**
 * GET Controller - Admin All Upcoming Reservations
 * Handles fetching all future reservations
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
$monthFilter = isset($_GET['month']) ? (int) $_GET['month'] : 0;
$searchFilter = isset($_GET['search']) ? $_GET['search'] : '';
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Build WHERE clause for upcoming reservations
// FIXED: Remove the "b.status != 'cancelled'" condition to show all statuses
$whereConditions = ["b.check_in >= CURDATE()"];
$queryParams = [];

if ($statusFilter !== 'all') {
    $whereConditions[] = "b.status = :status";
    $queryParams['status'] = $statusFilter;
}

if ($monthFilter > 0) {
    $whereConditions[] = "MONTH(b.check_in) = :month";
    $queryParams['month'] = $monthFilter;
}

if (!empty($searchFilter)) {
    // Use parameterized query instead of direct escaping
    $whereConditions[] = "(CONCAT(b.guest_first_name, ' ', b.guest_last_name) LIKE :search1 OR b.room_name LIKE :search2 OR b.booking_reference LIKE :search3)";
    $searchTerm = '%' . $searchFilter . '%';
    $queryParams['search1'] = $searchTerm;
    $queryParams['search2'] = $searchTerm;
    $queryParams['search3'] = $searchTerm;
}

$whereClause = implode(' AND ', $whereConditions);

// Get total count for pagination
$countQuery = "SELECT COUNT(*) as total FROM bookings b WHERE $whereClause";
$countResult = $db->query($countQuery, $queryParams)->fetch_one();
$totalReservations = $countResult['total'];
$totalPages = ceil($totalReservations / $limit);

// Get all upcoming reservations - FIXED: Include all statuses
$reservations = $db->query(
    "SELECT 
        b.id,
        b.booking_reference as bookingRef,
        CONCAT(b.guest_first_name, ' ', b.guest_last_name) as guest,
        b.room_name as roomType,
        b.room_assigned as room,
        b.check_in as checkIn,
        b.check_out as checkOut,
        b.nights,
        b.adults,
        b.children,
        (b.adults + b.children) as guests,
        b.total_amount as amount,
        b.payment_status,
        b.status,
        b.special_requests,
        b.created_at,
        b.points_earned,
        b.points_used,
        b.points_discount,
        u.id as user_id,
        u.member_tier,
        u.loyalty_points,
        u.phone as contact,
        u.email,
        CASE WHEN u.member_tier IN ('gold', 'platinum') THEN 1 ELSE 0 END as vip
     FROM bookings b
     LEFT JOIN users u ON b.user_id = u.id
     WHERE $whereClause
     ORDER BY b.check_in ASC
     LIMIT $limit OFFSET $offset",
    $queryParams
)->find() ?: [];

// Get statistics - FIXED: Include all statuses in stats
$stats = $db->query(
    "SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled
     FROM bookings
     WHERE check_in >= CURDATE()",
    []
)->fetch_one();

// If no stats, provide defaults
if (!$stats) {
    $stats = [
        'total' => 0,
        'confirmed' => 0,
        'pending' => 0,
        'cancelled' => 0
    ];
}

// Get monthly breakdown for filter
$months = $db->query(
    "SELECT 
        MONTH(check_in) as month,
        COUNT(*) as count
     FROM bookings
     WHERE check_in >= CURDATE()
     GROUP BY MONTH(check_in)
     ORDER BY month ASC",
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
    'reservations' => $reservations,
    'stats' => $stats,
    'months' => $months,
    'totalReservations' => $totalReservations,
    'currentPage' => $page,
    'totalPages' => $totalPages,
    'statusFilter' => $statusFilter,
    'monthFilter' => $monthFilter,
    'searchFilter' => $searchFilter,
    'unread_count' => $unread_count,
    'today' => date('F j, Y'),
    'todaySql' => date('Y-m-d')
];

// Extract variables for view
extract($viewData);
?>