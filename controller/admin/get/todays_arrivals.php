<?php
/**
 * GET Controller - Admin Today's Arrivals
 * Handles fetching all guests checking in today from database only
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

// Get today's date
$today = date('Y-m-d');

// Get filter parameters
$statusFilter = isset($_GET['status']) ? $_GET['status'] : 'all';
$searchFilter = isset($_GET['search']) ? $_GET['search'] : '';

// Build WHERE clause for today's arrivals
$whereConditions = ["DATE(b.check_in) = :today", "b.status != 'cancelled'"];

if ($statusFilter !== 'all') {
    if ($statusFilter === 'vip') {
        // VIP guests are those with gold/platinum tier
        $whereConditions[] = "u.member_tier IN ('gold', 'platinum')";
    } else {
        $whereConditions[] = "b.status = :status";
    }
}

if (!empty($searchFilter)) {
    $searchFilter = $db->escape($searchFilter);
    $whereConditions[] = "(CONCAT(b.guest_first_name, ' ', b.guest_last_name) LIKE '%$searchFilter%' OR b.booking_reference LIKE '%$searchFilter%')";
}

$whereClause = implode(' AND ', $whereConditions);

$queryParams = ['today' => $today];
if ($statusFilter !== 'all' && $statusFilter !== 'vip') {
    $queryParams['status'] = $statusFilter;
}

// Get today's arrivals from bookings table
$arrivals = $db->query(
    "SELECT 
        b.id,
        b.booking_reference as bookingNo,
        CONCAT(b.guest_first_name, ' ', b.guest_last_name) as guest,
        b.room_name as roomType,
        b.room_id,
        b.room_assigned as roomAssigned,
        b.check_in as checkInDate,
        b.check_in_time as checkInTime,
        b.check_out as checkOutDate,
        b.nights,
        b.adults,
        b.children,
        b.total_amount,
        b.payment_status,
        b.status,
        b.special_requests as specialRequests,
        b.created_at,
        u.id as user_id,
        u.member_tier,
        u.loyalty_points,
        u.phone as guest_phone,
        u.email as guest_email,
        CASE WHEN u.member_tier IN ('gold', 'platinum') THEN 1 ELSE 0 END as vip
     FROM bookings b
     LEFT JOIN users u ON b.user_id = u.id
     WHERE $whereClause
     ORDER BY b.check_in_time ASC, b.check_in ASC",
    $queryParams
)->find() ?: [];

// Get all available rooms for today
$availableRooms = $db->query(
    "SELECT 
        r.id,
        r.name,
        r.price,
        r.max_occupancy,
        r.beds,
        r.view
     FROM rooms r
     WHERE r.is_available = 1
        AND NOT EXISTS (
            SELECT 1 FROM bookings b 
            WHERE (b.room_id = r.id OR b.room_assigned = r.id)
                AND b.status IN ('confirmed', 'checked_in')
                AND b.check_in <= :today 
                AND b.check_out > :today
        )
     ORDER BY r.id ASC",
    ['today' => $today]
)->find() ?: [];

// Get statistics
$totalArrivals = count($arrivals);
$pendingArrivals = count(array_filter($arrivals, fn($a) => $a['status'] === 'pending'));
$confirmedArrivals = count(array_filter($arrivals, fn($a) => $a['status'] === 'confirmed'));
$checkedInArrivals = count(array_filter($arrivals, fn($a) => $a['status'] === 'checked_in'));
$roomsAssigned = count(array_filter($arrivals, fn($a) => !empty($a['roomAssigned'])));

// Group arrivals by time slot
$timeSlots = [
    'morning' => ['name' => 'Morning (6AM - 12PM)', 'color' => 'bg-amber-100 text-amber-700', 'icon' => '🌅', 'guests' => []],
    'afternoon' => ['name' => 'Afternoon (12PM - 5PM)', 'color' => 'bg-amber-200 text-amber-800', 'icon' => '☀️', 'guests' => []],
    'evening' => ['name' => 'Evening (5PM - 12AM)', 'color' => 'bg-amber-300 text-amber-900', 'icon' => '🌙', 'guests' => []],
    'lateNight' => ['name' => 'Late Night (12AM - 6AM)', 'color' => 'bg-indigo-100 text-indigo-800', 'icon' => '🌃', 'guests' => []]
];

foreach ($arrivals as $arrival) {
    // Use check_in_time if available, otherwise use a default (2PM)
    if (!empty($arrival['checkInTime'])) {
        $hour = (int) date('H', strtotime($arrival['checkInTime']));
        $displayTime = date('g:i A', strtotime($arrival['checkInTime']));
    } else {
        // Default to 2PM if no time specified
        $hour = 14;
        $displayTime = '2:00 PM';
    }
    $arrival['displayTime'] = $displayTime;

    if ($hour >= 6 && $hour < 12) {
        $timeSlots['morning']['guests'][] = $arrival;
    } elseif ($hour >= 12 && $hour < 17) {
        $timeSlots['afternoon']['guests'][] = $arrival;
    } elseif ($hour >= 17 && $hour < 24) {
        $timeSlots['evening']['guests'][] = $arrival;
    } else {
        $timeSlots['lateNight']['guests'][] = $arrival;
    }
}

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
    'arrivals' => $arrivals,
    'availableRooms' => $availableRooms,
    'timeSlots' => $timeSlots,
    'totalArrivals' => $totalArrivals,
    'pendingArrivals' => $pendingArrivals,
    'confirmedArrivals' => $confirmedArrivals,
    'checkedInArrivals' => $checkedInArrivals,
    'roomsAssigned' => $roomsAssigned,
    'statusFilter' => $statusFilter,
    'searchFilter' => $searchFilter,
    
    'today' => date('F j, Y'),
    'todaySql' => $today
];

// Extract variables for view
extract($viewData);
?>