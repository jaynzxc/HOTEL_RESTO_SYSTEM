<?php
session_start();

require_once __DIR__ . '/../../../Class/Database.php';

if (!isset($_SESSION['user_id']) || !$_SESSION['logged_in']) {
    header('Location: ../../view/auth/login.php');
    exit();
}

$config = require_once __DIR__ . '/../../../config/config.php';
$db = new Database($config['database']);

$_SESSION['error'] ??= [];
$_SESSION['success'] ??= [];

$user = $db->query(
    "SELECT id, full_name, email, phone, loyalty_points FROM users WHERE id = :id",
    ['id' => $_SESSION['user_id']]
)->fetch_one();

// Get outstanding balance from current_balance table - USING ALL THREE BALANCE TYPES
$balanceData = $db->query(
    "SELECT total_balance, pending_balance, available_balance 
     FROM current_balance 
     WHERE user_id = :user_id",
    ['user_id' => $_SESSION['user_id']]
)->fetch_one();

$totalOutstanding = 0;
$hasOutstandingBalance = false;
$hasPendingPayments = false;
$balanceMessage = '';

if ($balanceData) {
    $totalOutstanding = $balanceData['total_balance'] ?? 0;
    $pendingBalance = $balanceData['pending_balance'] ?? 0;
    $availableBalance = $balanceData['available_balance'] ?? 0;

    // Check for ANY outstanding balance (total_balance > 0) OR pending payments
    $hasOutstandingBalance = $totalOutstanding > 0;
    $hasPendingPayments = $pendingBalance > 0;

    // Create appropriate message based on balance status
    if ($totalOutstanding > 0 && $pendingBalance > 0) {
        $balanceMessage = "You have ₱" . number_format($totalOutstanding, 2) . " outstanding (₱" . number_format($pendingBalance, 2) . " pending approval)";
    } elseif ($totalOutstanding > 0) {
        $balanceMessage = "You have ₱" . number_format($totalOutstanding, 2) . " outstanding balance";
    } elseif ($pendingBalance > 0) {
        $balanceMessage = "You have ₱" . number_format($pendingBalance, 2) . " in payments pending approval";
        // Still block redemptions if there are pending payments
        $hasOutstandingBalance = true;
    }
} else {
    // Fallback: calculate from bookings and reservations if no current_balance record
    $outstandingBookings = $db->query(
        "SELECT COALESCE(SUM(total_amount), 0) as total 
         FROM bookings 
         WHERE user_id = :user_id AND payment_status = 'unpaid' AND status != 'cancelled'",
        ['user_id' => $_SESSION['user_id']]
    )->fetch_one();

    $outstandingReservations = $db->query(
        "SELECT COALESCE(SUM(down_payment), 0) as total 
         FROM restaurant_reservations 
         WHERE user_id = :user_id AND payment_status = 'unpaid' AND status != 'cancelled'",
        ['user_id' => $_SESSION['user_id']]
    )->fetch_one();

    $totalOutstanding = ($outstandingBookings['total'] ?? 0) + ($outstandingReservations['total'] ?? 0);
    $hasOutstandingBalance = $totalOutstanding > 0;
    $balanceMessage = $hasOutstandingBalance ? "You have ₱" . number_format($totalOutstanding, 2) . " outstanding balance" : '';
}

// Get points history
$pointsHistory = $db->query(
    "SELECT 
        'review' as activity_type,
        created_at as date,
        CONCAT('Review: ', LEFT(review_text, 30), '...') as description,
        20 as points,
        'earned' as type
     FROM reviews 
     WHERE user_id = :user_id
     
     UNION ALL
     
     SELECT 
        'redemption' as activity_type,
        created_at as date,
        CONCAT('Redeemed: ', reward_name, ' (', points_cost, ' pts)') as description,
        -points_cost as points,
        'redeemed' as type
     FROM redemptions 
     WHERE user_id = :user_id
     
     ORDER BY date DESC 
     LIMIT 20",
    ['user_id' => $_SESSION['user_id']]
)->find();

if (!$pointsHistory) {
    $pointsHistory = [];
}

// Get available rewards from database
$availableRewards = $db->query(
    "SELECT * FROM rewards WHERE is_active = 1 ORDER BY points_cost ASC"
)->find();

if (!$availableRewards) {
    $availableRewards = [];
}

$points = $user['loyalty_points'] ?? 0;

// Tier calculation
if ($points >= 5000) {
    $tier = 'platinum';
    $nextTier = 'platinum';
    $nextThreshold = 5000;
    $currentThreshold = 5000;
    $perks = ['10%', '3x', 'suite upgrade'];
    $pointsToNext = 0;
} elseif ($points >= 2000) {
    $tier = 'gold';
    $nextTier = 'platinum';
    $nextThreshold = 5000;
    $currentThreshold = 2000;
    $perks = ['5%', '2x', 'welcome drink'];
    $pointsToNext = $nextThreshold - $points;
} elseif ($points >= 1000) {
    $tier = 'silver';
    $nextTier = 'gold';
    $nextThreshold = 2000;
    $currentThreshold = 1000;
    $perks = ['2%', '1.5x', 'free coffee'];
    $pointsToNext = $nextThreshold - $points;
} else {
    $tier = 'bronze';
    $nextTier = 'silver';
    $nextThreshold = 1000;
    $currentThreshold = 0;
    $perks = ['0%', '1x', '—'];
    $pointsToNext = $nextThreshold - $points;
}

// Calculate progress percentage
if ($tier === 'platinum' && $points >= 5000) {
    $progress = 100;
} else {
    $progress = ($points - $currentThreshold) / ($nextThreshold - $currentThreshold) * 100;
    $progress = min(100, max(0, $progress));
}

// Get user initials
$name_parts = explode(' ', trim($user['full_name']));
$initials = strtoupper(substr($name_parts[0], 0, 1) . (isset($name_parts[1]) ? substr($name_parts[1], 0, 1) : ''));

// Store data for view
$viewData = [
    'user' => $user,
    'pointsHistory' => $pointsHistory,
    'availableRewards' => $availableRewards,
    'points' => $points,
    'tier' => $tier,
    'nextTier' => $nextTier,
    'nextThreshold' => $nextThreshold,
    'currentThreshold' => $currentThreshold,
    'perks' => $perks,
    'pointsToNext' => $pointsToNext,
    'progress' => $progress,
    'initials' => $initials,
    'totalOutstanding' => $totalOutstanding,
    'pendingBalance' => $pendingBalance ?? 0,
    'availableBalance' => $availableBalance ?? 0,
    'hasOutstandingBalance' => $hasOutstandingBalance,
    'hasPendingPayments' => $hasPendingPayments ?? false,
    'balanceMessage' => $balanceMessage
];

// Extract variables for view
extract($viewData);
?>