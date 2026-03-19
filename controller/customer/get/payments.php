<?php
session_start();
require_once __DIR__ . '/../../../Class/Database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !$_SESSION['logged_in']) {
    header('Location: ../../../src/login-register/login_form.php');
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

// Set points variable for easy access
$points = $user['loyalty_points'] ?? 0;
$member_tier = $user['member_tier'] ?? 'bronze';

// Get user's payment methods
$paymentMethods = $db->query(
    "SELECT * FROM payment_methods WHERE user_id = :user_id ORDER BY is_default DESC, created_at DESC",
    ['user_id' => $_SESSION['user_id']]
)->find();

// Get user's payments from payments table with approval status
$payments = $db->query(
    "SELECT p.*, 
        CASE 
            WHEN p.booking_type = 'hotel' THEN b.booking_reference
            WHEN p.booking_type = 'restaurant' THEN rr.reservation_reference
            ELSE NULL
        END as reference_number,
        CASE
            WHEN p.approval_status = 'approved' AND p.payment_status = 'completed' THEN 'completed'
            WHEN p.approval_status = 'pending' THEN 'pending'
            WHEN p.approval_status = 'rejected' THEN 'failed'
            ELSE p.payment_status
        END as display_status
     FROM payments p
     LEFT JOIN bookings b ON p.booking_id = b.id AND p.booking_type = 'hotel'
     LEFT JOIN restaurant_reservations rr ON p.booking_id = rr.id AND p.booking_type = 'restaurant'
     WHERE p.user_id = :user_id 
     ORDER BY p.created_at DESC 
     LIMIT 20",
    ['user_id' => $_SESSION['user_id']]
)->find();

// Get current balance from current_balance table
$balanceData = $db->query(
    "SELECT total_balance, pending_balance, available_balance 
     FROM current_balance 
     WHERE user_id = :user_id",
    ['user_id' => $_SESSION['user_id']]
)->fetch_one();

// If no balance record exists, create one
if (!$balanceData) {
    $db->query(
        "INSERT INTO current_balance (user_id, total_balance, pending_balance, available_balance) 
         VALUES (:user_id, 0, 0, 0)",
        ['user_id' => $_SESSION['user_id']]
    );
    $balanceData = [
        'total_balance' => 0,
        'pending_balance' => 0,
        'available_balance' => 0
    ];
}

$currentBalance = $balanceData['available_balance'] ?? 0;
$pendingBalance = $balanceData['pending_balance'] ?? 0;
$totalBalance = $balanceData['total_balance'] ?? 0;

// Get monthly summary from payments table (only approved payments)
$currentMonth = date('Y-m');
$monthlyStats = $db->query(
    "SELECT 
        COALESCE(SUM(amount), 0) as total,
        COUNT(*) as count
     FROM payments 
     WHERE user_id = :user_id 
        AND payment_status = 'completed'
        AND approval_status = 'approved'
        AND DATE_FORMAT(created_at, '%Y-%m') = :month",
    [
        'user_id' => $_SESSION['user_id'],
        'month' => $currentMonth
    ]
)->fetch_one();

// Get last month's total for comparison (only approved payments)
$lastMonth = date('Y-m', strtotime('-1 month'));
$lastMonthTotal = $db->query(
    "SELECT COALESCE(SUM(amount), 0) as total
     FROM payments 
     WHERE user_id = :user_id 
        AND payment_status = 'completed'
        AND approval_status = 'approved'
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



// Get recent unpaid bookings for quick payment (only available balance)
$recentUnpaid = $db->query(
    "SELECT 
        id,
        booking_reference as reference,
        'hotel' as type,
        total_amount as amount,
        check_in as date
     FROM bookings 
     WHERE user_id = :user_id AND payment_status = 'unpaid' AND status != 'cancelled'
     UNION ALL
     SELECT 
        id,
        reservation_reference as reference,
        'restaurant' as type,
        down_payment as amount,
        reservation_date as date
     FROM restaurant_reservations 
     WHERE user_id = :user_id AND payment_status = 'unpaid' AND status != 'cancelled'
     ORDER BY date DESC
     LIMIT 5",
    ['user_id' => $_SESSION['user_id']]
)->find();

// Get success and error messages from session
$success = $_SESSION['success'] ?? [];
$error = $_SESSION['error'] ?? [];

// Clear session messages
unset($_SESSION['success']);
unset($_SESSION['error']);

// Store data for view
$viewData = [
    'user' => $user,
    'paymentMethods' => $paymentMethods,
    'payments' => $payments,
    'currentBalance' => $currentBalance,
    'pendingBalance' => $pendingBalance,
    'totalBalance' => $totalBalance,
    'balanceData' => $balanceData,
    'monthlyStats' => $monthlyStats,
    'percentChange' => $percentChange,
    'initials' => $initials,
    
    'recentUnpaid' => $recentUnpaid,
    'success' => $success,
    'error' => $error,
    'lastMonthTotal' => $lastMonthTotal,
    'points' => $points,
    'member_tier' => $member_tier
];

// Extract variables for view
extract($viewData);
?>