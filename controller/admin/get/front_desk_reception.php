<?php
/**
 * GET Controller - Admin Front Desk / Reception
 * Handles fetching today's arrivals, departures, guest requests, and statistics
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

// Get today's arrivals (check-ins)
$arrivals = $db->query(
    "SELECT 
        b.id,
        b.booking_reference,
        CONCAT(b.guest_first_name, ' ', b.guest_last_name) as guest_name,
        b.room_name as room,
        b.room_id,
        b.check_in,
        b.check_in_time,
        b.status,
        b.payment_status,
        b.total_amount,
        b.adults,
        b.children,
        b.special_requests
     FROM bookings b
     WHERE b.check_in = :today 
        AND b.status IN ('confirmed', 'pending')
     ORDER BY b.check_in_time ASC, b.id ASC",
    ['today' => $today]
)->find() ?: [];

// Get today's departures (check-outs) - FIXED: Removed the subquery that was causing the error
$departures = $db->query(
    "SELECT 
        b.id,
        b.booking_reference,
        CONCAT(b.guest_first_name, ' ', b.guest_last_name) as guest_name,
        b.room_name as room,
        b.room_id,
        b.check_out,
        b.check_out_time,
        b.status,
        b.payment_status,
        b.total_amount,
        b.adults,
        b.children
     FROM bookings b
     WHERE b.check_out = :today 
        AND b.status IN ('confirmed', 'checked-in')
     ORDER BY b.check_out_time ASC, b.id ASC",
    ['today' => $today]
)->find() ?: [];

// Get active guest requests
$guestRequests = $db->query(
    "SELECT 
        gi.id,
        gi.user_id,
        gi.subject,
        gi.message,
        gi.status,
        gi.priority,
        gi.created_at,
        CONCAT(u.first_name, ' ', u.last_name) as guest_name,
        u.id as room_number
     FROM guest_interactions gi
     LEFT JOIN users u ON gi.user_id = u.id
     WHERE gi.status IN ('pending', 'in-progress')
     ORDER BY 
        CASE 
            WHEN gi.priority = 'high' THEN 1
            WHEN gi.priority = 'medium' THEN 2
            ELSE 3
        END,
        gi.created_at ASC
     LIMIT 10",
    []
)->find() ?: [];

// Get room statistics
$roomStats = $db->query(
    "SELECT 
        COUNT(*) as total_rooms,
        SUM(CASE WHEN is_available = 1 THEN 1 ELSE 0 END) as available,
        SUM(CASE WHEN is_available = 0 THEN 1 ELSE 0 END) as occupied
     FROM rooms",
    []
)->fetch_one();

// If no room stats, provide defaults
if (!$roomStats) {
    $roomStats = [
        'total_rooms' => 0,
        'available' => 0,
        'occupied' => 0
    ];
}

// Get today's summary
$todaySummary = $db->query(
    "SELECT 
        SUM(CASE WHEN b.check_in = :today AND b.status = 'checked-in' THEN 1 ELSE 0 END) as checked_in,
        SUM(CASE WHEN b.check_out = :today AND b.status = 'completed' THEN 1 ELSE 0 END) as checked_out,
        SUM(CASE WHEN b.check_in = :today AND b.status = 'pending' THEN 1 ELSE 0 END) as pending_checkin,
        SUM(CASE WHEN b.check_out = :today AND b.status = 'checked-in' THEN 1 ELSE 0 END) as pending_checkout
     FROM bookings b",
    ['today' => $today]
)->fetch_one();

// If no summary, provide defaults
if (!$todaySummary) {
    $todaySummary = [
        'checked_in' => 0,
        'checked_out' => 0,
        'pending_checkin' => 0,
        'pending_checkout' => 0
    ];
}

// Get upcoming reservations (next 3 days)
$upcomingReservations = $db->query(
    "SELECT 
        b.id,
        b.booking_reference,
        CONCAT(b.guest_first_name, ' ', b.guest_last_name) as guest_name,
        b.room_name,
        b.check_in,
        b.check_out,
        b.nights,
        b.status,
        b.payment_status,
        b.total_amount
     FROM bookings b
     WHERE b.check_in > :today 
        AND b.check_in <= DATE_ADD(:today, INTERVAL 3 DAY)
        AND b.status IN ('confirmed', 'pending')
     ORDER BY b.check_in ASC, b.check_in_time ASC
     LIMIT 10",
    ['today' => $today]
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
    'arrivals' => $arrivals,
    'departures' => $departures,
    'guestRequests' => $guestRequests,
    'roomStats' => $roomStats,
    'todaySummary' => $todaySummary,
    'upcomingReservations' => $upcomingReservations,
    
    'today' => $today
];

// Extract variables for view
extract($viewData);
?>