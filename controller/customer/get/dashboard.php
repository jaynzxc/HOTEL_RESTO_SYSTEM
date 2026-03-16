<?php
/**
 * GET Controller - Customer Dashboard
 * Handles fetching all dashboard data for the current user
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

// Get current user data
$user = $db->query(
    "SELECT id, full_name, first_name, last_name, email, phone, 
            loyalty_points, member_tier, avatar, created_at
     FROM users WHERE id = :id",
    ['id' => $_SESSION['user_id']]
)->fetch_one();

// Get current balance with proper error handling
$balanceData = $db->query(
    "SELECT total_balance, pending_balance, available_balance 
     FROM current_balance 
     WHERE user_id = :user_id",
    ['user_id' => $_SESSION['user_id']]
)->fetch_one();

// Ensure balance has all required keys with default values
$balance = [
    'total_balance' => 0,
    'pending_balance' => 0,
    'available_balance' => 0
];

if ($balanceData) {
    $balance = [
        'total_balance' => $balanceData['total_balance'] ?? 0,
        'pending_balance' => $balanceData['pending_balance'] ?? 0,
        'available_balance' => $balanceData['available_balance'] ?? 0
    ];
}

// Get active booking (most recent pending/confirmed booking)
$activeBooking = $db->query(
    "SELECT id, booking_reference, room_name, check_in, check_out, 
            status, total_amount, nights, adults, children
     FROM bookings 
     WHERE user_id = :user_id 
     AND booking_type = 'hotel'
     AND status IN ('pending', 'confirmed')
     AND check_out >= CURDATE()
     ORDER BY check_in ASC
     LIMIT 1",
    ['user_id' => $_SESSION['user_id']]
)->fetch_one();

// Get today's reservation
$today = date('Y-m-d');
$todayReservation = $db->query(
    "SELECT id, reservation_reference, reservation_time, guests, status
     FROM restaurant_reservations 
     WHERE user_id = :user_id 
     AND reservation_date = :today
     AND status IN ('pending', 'confirmed')
     LIMIT 1",
    [
        'user_id' => $_SESSION['user_id'],
        'today' => $today
    ]
)->fetch_one();

// Get recent bookings (for the bookings list)
$recentBookings = $db->query(
    "SELECT id, booking_reference, room_name, check_in, check_out, status, total_amount
     FROM bookings 
     WHERE user_id = :user_id 
     AND booking_type = 'hotel'
     ORDER BY created_at DESC
     LIMIT 5",
    ['user_id' => $_SESSION['user_id']]
)->find() ?: [];

// Get latest food order
$latestOrder = $db->query(
    "SELECT total_amount, status FROM food_orders 
     WHERE user_id = :user_id 
     ORDER BY created_at DESC LIMIT 1",
    ['user_id' => $_SESSION['user_id']]
)->fetch_one();

// Get recent notifications
$notifications = $db->query(
    "SELECT id, title, message, type, icon, is_read, created_at
     FROM notifications 
     WHERE user_id = :user_id
     ORDER BY created_at DESC
     LIMIT 5",
    ['user_id' => $_SESSION['user_id']]
)->find() ?: [];

// Format notifications for display
foreach ($notifications as &$notif) {
    $created = new DateTime($notif['created_at']);
    $now = new DateTime();
    $diff = $now->diff($created);

    if ($diff->days > 0) {
        $notif['time_ago'] = $diff->days . ' day' . ($diff->days > 1 ? 's' : '') . ' ago';
    } elseif ($diff->h > 0) {
        $notif['time_ago'] = $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
    } elseif ($diff->i > 0) {
        $notif['time_ago'] = $diff->i . ' minute' . ($diff->i > 1 ? 's' : '') . ' ago';
    } else {
        $notif['time_ago'] = 'just now';
    }

    $notif['icon'] = $notif['icon'] ?? 'fa-bell';
}

// Get payment methods
$paymentMethods = $db->query(
    "SELECT id, method_type, display_name, is_default
     FROM payment_methods 
     WHERE user_id = :user_id
     ORDER BY is_default DESC
     LIMIT 3",
    ['user_id' => $_SESSION['user_id']]
)->find() ?: [];

// Get unread notifications count
$unread_count = $db->query(
    "SELECT COUNT(*) as count FROM notifications 
     WHERE user_id = :user_id AND is_read = 0",
    ['user_id' => $_SESSION['user_id']]
)->fetch_one()['count'] ?? 0;

// Calculate points to next tier
$points = $user['loyalty_points'] ?? 0;
$member_tier = $user['member_tier'] ?? 'bronze';

$nextTier = '';
$pointsNeeded = 0;

switch ($member_tier) {
    case 'bronze':
        $nextTier = 'silver';
        $pointsNeeded = max(0, 1000 - $points);
        break;
    case 'silver':
        $nextTier = 'gold';
        $pointsNeeded = max(0, 2000 - $points);
        break;
    case 'gold':
        $nextTier = 'platinum';
        $pointsNeeded = max(0, 5000 - $points);
        break;
    case 'platinum':
        $nextTier = 'platinum (max)';
        $pointsNeeded = 0;
        break;
}

// Get user initials
$initials = 'G';
if ($user) {
    $first_name = $user['first_name'] ?? '';
    $last_name = $user['last_name'] ?? '';
    $full_name = $user['full_name'] ?? '';

    if (empty($first_name) && empty($last_name) && !empty($full_name)) {
        $name_parts = explode(' ', $full_name, 2);
        $first_name = $name_parts[0];
        $last_name = $name_parts[1] ?? '';
    }

    $initials = strtoupper(
        substr($first_name, 0, 1) .
        (isset($last_name) ? substr($last_name, 0, 1) : '')
    );
}

// Get greeting based on time of day
$hour = date('H');
if ($hour < 12) {
    $greeting = 'good morning';
} elseif ($hour < 18) {
    $greeting = 'good afternoon';
} else {
    $greeting = 'good evening';
}

// Store data for view
$viewData = [
    'user' => $user,
    'balance' => $balance,
    'activeBooking' => $activeBooking,
    'todayReservation' => $todayReservation,
    'recentBookings' => $recentBookings,
    'notifications' => $notifications,
    'paymentMethods' => $paymentMethods,
    'unread_count' => $unread_count,
    'initials' => $initials,
    'points' => $points,
    'member_tier' => $member_tier,
    'nextTier' => $nextTier,
    'pointsNeeded' => $pointsNeeded,
    'greeting' => $greeting,
    'latestOrder' => $latestOrder
];

// Extract variables for view
extract($viewData);
?>