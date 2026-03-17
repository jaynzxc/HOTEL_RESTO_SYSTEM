<?php
/**
 * GET Controller - Admin Today's Departures
 * Handles fetching all guests checking out today
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

// Get today's date
$today = date('Y-m-d');

// Get filter parameters
$statusFilter = isset($_GET['status']) ? $_GET['status'] : 'all';
$searchFilter = isset($_GET['search']) ? $_GET['search'] : '';

// Build WHERE clause for today's departures
$whereConditions = ["DATE(b.check_out) = :today"];

if ($statusFilter !== 'all') {
    if ($statusFilter === 'pending') {
        $whereConditions[] = "b.status = 'checked_in'";
    } elseif ($statusFilter === 'checked-out') {
        $whereConditions[] = "b.status = 'completed'";
    } elseif ($statusFilter === 'late') {
        $whereConditions[] = "b.check_out_time > '12:00:00'";
    }
}

if (!empty($searchFilter)) {
    $searchFilter = $db->escape($searchFilter);
    $whereConditions[] = "(CONCAT(b.guest_first_name, ' ', b.guest_last_name) LIKE '%$searchFilter%' OR b.room_assigned LIKE '%$searchFilter%' OR b.booking_reference LIKE '%$searchFilter%')";
}

$whereClause = implode(' AND ', $whereConditions);

$queryParams = ['today' => $today];

// Get today's departures from bookings table
$departures = $db->query(
    "SELECT 
        b.id,
        b.booking_reference as bookingNo,
        CONCAT(b.guest_first_name, ' ', b.guest_last_name) as guest,
        b.room_name as roomType,
        b.room_assigned as room,
        b.check_in as checkInDate,
        b.check_out as checkOutDate,
        b.check_out_time as checkOutTime,
        b.nights,
        b.adults,
        b.children,
        b.total_amount as billAmount,
        b.payment_status,
        b.status,
        b.special_requests,
        b.created_at,
        u.id as user_id,
        u.member_tier,
        u.loyalty_points,
        u.phone as guest_phone,
        u.email as guest_email,
        CASE WHEN u.member_tier IN ('gold', 'platinum') THEN 1 ELSE 0 END as vip,
        COALESCE(cb.total_balance, 0) as total_balance
     FROM bookings b
     LEFT JOIN users u ON b.user_id = u.id
     LEFT JOIN current_balance cb ON b.user_id = cb.user_id
     WHERE $whereClause
     ORDER BY b.check_out_time ASC, b.room_assigned ASC",
    $queryParams
)->find() ?: [];

// Get food orders for additional charges - FIXED VERSION
$foodCharges = [];
if (!empty($departures)) {
    $userIds = array_column(array_filter($departures, fn($d) => !empty($d['user_id'])), 'user_id');

    if (!empty($userIds)) {
        // Create placeholders for IN clause
        $placeholders = implode(',', array_fill(0, count($userIds), '?'));

        // Prepare parameters - just user IDs, no extra date parameter
        $params = $userIds;

        $foodOrders = $db->query(
            "SELECT user_id, COALESCE(SUM(total_amount), 0) as total 
             FROM food_orders 
             WHERE user_id IN ($placeholders) 
                AND DATE(created_at) = CURDATE()
             GROUP BY user_id",
            $params
        )->find() ?: [];

        foreach ($foodOrders as $fo) {
            $foodCharges[$fo['user_id']] = $fo['total'];
        }
    }
}

// Process departures and add additional data
foreach ($departures as &$departure) {
    // Get food charges if any
    $additionalCharges = isset($foodCharges[$departure['user_id']]) ? $foodCharges[$departure['user_id']] : 0;
    $departure['additionalCharges'] = $additionalCharges;
    $departure['totalBill'] = $departure['billAmount'] + $additionalCharges;

    // Format check-out time
    $departure['checkOutTimeFormatted'] = !empty($departure['checkOutTime'])
        ? date('g:i A', strtotime($departure['checkOutTime']))
        : '11:00 AM';

    // Determine if express checkout is available (total_balance == 0 AND status is checked_in)
    $departure['express'] = ($departure['total_balance'] == 0 && $departure['status'] == 'checked_in');

    // Check if late checkout
    $departure['lateCheckout'] = !empty($departure['checkOutTime']) && $departure['checkOutTime'] > '12:00:00';

    // Check if room needs cleaning
    $departure['needsCleaning'] = in_array($departure['status'], ['checked_in', 'completed']);
}

// Get statistics
$totalDepartures = count($departures);
$pendingDepartures = count(array_filter($departures, fn($d) => $d['status'] === 'checked_in'));
$checkedOutDepartures = count(array_filter($departures, fn($d) => $d['status'] === 'completed'));
$roomsToClean = count(array_filter($departures, fn($d) => $d['needsCleaning']));

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
    'departures' => $departures,
    'totalDepartures' => $totalDepartures,
    'pendingDepartures' => $pendingDepartures,
    'checkedOutDepartures' => $checkedOutDepartures,
    'roomsToClean' => $roomsToClean,
    'statusFilter' => $statusFilter,
    'searchFilter' => $searchFilter,
    'unread_count' => $unread_count,
    'today' => date('F j, Y'),
    'todaySql' => $today
];

// Extract variables for view
extract($viewData);
?>