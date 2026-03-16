<?php
session_start();
require_once __DIR__ . '/../../../Class/Database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !$_SESSION['logged_in']) {
    header('Location: ../../view/auth/login.php');
    exit();
}

$config = require_once __DIR__ . '/../../../config/config.php';
$db = new Database($config['database']);

// Initialize session arrays
$_SESSION['error'] ??= [];
$_SESSION['success'] ??= [];

// Get user data
$user = $db->query(
    "SELECT id, full_name, first_name, last_name, email, phone, loyalty_points, member_tier, avatar
     FROM users WHERE id = :id",
    ['id' => $_SESSION['user_id']]
)->fetch_one();

// Get user's payment methods
$paymentMethods = $db->query(
    "SELECT * FROM payment_methods WHERE user_id = :user_id ORDER BY is_default DESC, created_at DESC",
    ['user_id' => $_SESSION['user_id']]
)->find();

// Get user's payments from payments table
$payments = $db->query(
    "SELECT p.*, 
        CASE 
            WHEN p.booking_type = 'hotel' THEN b.booking_reference
            WHEN p.booking_type = 'restaurant' THEN rr.reservation_reference
            ELSE NULL
        END as reference_number
     FROM payments p
     LEFT JOIN bookings b ON p.booking_id = b.id AND p.booking_type = 'hotel'
     LEFT JOIN restaurant_reservations rr ON p.booking_id = rr.id AND p.booking_type = 'restaurant'
     WHERE p.user_id = :user_id 
     ORDER BY p.created_at DESC 
     LIMIT 20",
    ['user_id' => $_SESSION['user_id']]
)->find();

// Calculate current balance (sum of unpaid bookings)
$pendingBalance = $db->query(
    "SELECT COALESCE(SUM(total_amount), 0) as balance 
     FROM bookings 
     WHERE user_id = :user_id AND payment_status = 'unpaid' AND status != 'cancelled'",
    ['user_id' => $_SESSION['user_id']]
)->fetch_one();
$currentBalance = $pendingBalance['balance'] ?? 0;

// Get monthly summary from payments table
$currentMonth = date('Y-m');
$monthlyStats = $db->query(
    "SELECT 
        COALESCE(SUM(amount), 0) as total,
        COUNT(*) as count
     FROM payments 
     WHERE user_id = :user_id 
        AND payment_status = 'completed'
        AND DATE_FORMAT(created_at, '%Y-%m') = :month",
    [
        'user_id' => $_SESSION['user_id'],
        'month' => $currentMonth
    ]
)->fetch_one();

// Get last month's total for comparison
$lastMonth = date('Y-m', strtotime('-1 month'));
$lastMonthTotal = $db->query(
    "SELECT COALESCE(SUM(amount), 0) as total
     FROM payments 
     WHERE user_id = :user_id 
        AND payment_status = 'completed'
        AND DATE_FORMAT(created_at, '%Y-%m') = :month",
    [
        'user_id' => $_SESSION['user_id'],
        'month' => $lastMonth
    ]
)->fetch_one();

// Calculate percentage change
$percentChange = 0;
if ($lastMonthTotal['total'] > 0) {
    $percentChange = (($monthlyStats['total'] - $lastMonthTotal['total']) / $lastMonthTotal['total']) * 100;
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

// Get unread notifications count (placeholder)
$unread_count = 3;

