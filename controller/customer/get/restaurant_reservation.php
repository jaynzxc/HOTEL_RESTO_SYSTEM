<?php
/**
 * GET Controller - Staff Restaurant Reservation
 * Handles fetching tables, reservations, waiting list, and notifications
 */

session_start();
require_once __DIR__ . '/../../../Class/Database.php';

// Check if user is logged in and is staff/admin
if (!isset($_SESSION['user_id']) || !$_SESSION['logged_in']) {
    header('Location: ../../../src/login-register/login_form.php');
    exit();
}

$config = require __DIR__ . '/../../../config/config.php';
$db = new Database($config['database']);

// Get current staff user data
$user = $db->query(
    "SELECT id, full_name, first_name, last_name, email, loyalty_points, member_tier
     FROM users WHERE id = :id",
    ['id' => $_SESSION['user_id']]
)->fetch_one();

// Set points variable for easy access
$points = $user['loyalty_points'] ?? 0;
$member_tier = $user['member_tier'] ?? 'bronze';

// Get all tables
$tables = $db->query(
    "SELECT 
        table_number as id,
        table_number,
        capacity as seats,
        status,
        location
     FROM restaurant_tables
     ORDER BY table_number ASC"
)->find() ?: [];

// Get today's reservations
$today = date('Y-m-d');
$reservations = $db->query(
    "SELECT 
        rr.id as reservation_id,
        rr.reservation_reference,
        rr.guest_first_name,
        rr.guest_last_name,
        CONCAT(rr.guest_first_name, ' ', rr.guest_last_name) as guest_name,
        rr.guest_email,
        rr.guest_phone,
        rr.reservation_date,
        rr.reservation_time,
        rr.guests as number_of_guests,
        rr.table_number,
        rr.special_requests,
        rr.occasion,
        rr.down_payment as deposit_amount,
        rr.payment_status,
        rr.status as reservation_status,
        rr.created_at
     FROM restaurant_reservations rr
     WHERE rr.reservation_date = :today
        OR (rr.reservation_date >= :today AND rr.status IN ('pending', 'confirmed'))
     ORDER BY rr.reservation_date ASC, rr.reservation_time ASC
     LIMIT 50",
    ['today' => $today]
)->find() ?: [];

// Get waiting list
$waitingList = $db->query(
    "SELECT 
        wl.id as waiting_id,
        wl.guest_name,
        wl.guest_phone,
        wl.party_size as guests,
        wl.requested_time,
        wl.wait_started_at,
        TIMESTAMPDIFF(MINUTE, wl.wait_started_at, NOW()) as wait_minutes,
        wl.estimated_wait_minutes as est_minutes,
        wl.status
     FROM waiting_list wl
     WHERE wl.status = 'waiting'
     ORDER BY wl.wait_started_at ASC
     LIMIT 20"
)->find() ?: [];

// Get recent notifications
$notifications = $db->query(
    "SELECT 
        n.id,
        n.title,
        n.message,
        n.type,
        n.icon,
        n.link,
        n.is_read,
        DATE_FORMAT(n.created_at, '%h:%i %p') as time_formatted,
        n.created_at
     FROM notifications n
     WHERE n.user_id = :user_id OR n.user_id IS NULL
     ORDER BY n.created_at DESC
     LIMIT 20",
    ['user_id' => $_SESSION['user_id']]
)->find() ?: [];

// Get unread notifications count
try {
    $unread_result = $db->query(
        "SELECT COUNT(*) as count FROM notifications 
         WHERE (user_id = :user_id OR user_id IS NULL) AND is_read = 0",
        ['user_id' => $_SESSION['user_id']]
    )->fetch_one();
    $unread_count = $unread_result['count'] ?? 0;
} catch (Exception $e) {
    $unread_count = 0;
}

// Calculate today's down payment summary
$downPaymentSummary = $db->query(
    "SELECT 
        COUNT(*) as total_reservations,
        COALESCE(SUM(down_payment), 0) as total_down_payments,
        COUNT(CASE WHEN payment_status = 'unpaid' THEN 1 END) as pending_payments
     FROM restaurant_reservations
     WHERE reservation_date = :today",
    ['today' => $today]
)->fetch_one() ?: ['total_reservations' => 0, 'total_down_payments' => 0, 'pending_payments' => 0];

// Get user initials
$initials = 'ST';
$first_name = $user['first_name'] ?? '';
$last_name = $user['last_name'] ?? '';
$full_name = $user['full_name'] ?? 'Staff';

if (!empty($first_name) && !empty($last_name)) {
    $initials = strtoupper(substr($first_name, 0, 1) . substr($last_name, 0, 1));
} elseif (!empty($full_name)) {
    $name_parts = explode(' ', trim($full_name), 2);
    $initials = strtoupper(substr($name_parts[0], 0, 1));
    if (isset($name_parts[1])) {
        $initials .= strtoupper(substr($name_parts[1], 0, 1));
    }
}

// Get role display name
$roleDisplay = match ($user['role'] ?? 'staff') {
    'admin' => 'administrator',
    'staff' => 'maître d\'',
    default => 'staff'
};

// Get success and error messages from session
$success = $_SESSION['success'] ?? [];
$error = $_SESSION['error'] ?? [];

// Clear session messages
unset($_SESSION['success']);
unset($_SESSION['error']);


// Store data for view
$viewData = [
    'user' => $user,
    'tables' => $tables,
    'reservations' => $reservations,
    'waitingList' => $waitingList,
    'notifications' => $notifications,

    'downPaymentSummary' => $downPaymentSummary,
    'initials' => $initials,
    'roleDisplay' => $roleDisplay,
    'success' => $success,
    'error' => $error,
    'today' => $today,
    'current_time' => date('h:i A'),
    'points' => $points,
    'member_tier' => $member_tier
];

// Extract variables for view
extract($viewData);