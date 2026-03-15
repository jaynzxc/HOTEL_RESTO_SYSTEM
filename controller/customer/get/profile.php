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

// Initialize session arrays for messages
$_SESSION['error'] ??= [];
$_SESSION['success'] ??= [];
$_SESSION['form_data'] ??= [];

// Get user data with all profile fields
$user = $db->query(
    "SELECT 
        id, full_name, first_name, last_name, email, phone, alternative_phone,
        date_of_birth, gender, nationality, address, city, postal_code, country,
        preferred_language, loyalty_points, role, status, email_verified, phone_verified,
        notify_email, notify_sms, notify_promo, notify_loyalty, avatar, member_tier,
        DATE_FORMAT(created_at, '%M %Y') as member_since
     FROM users WHERE id = :id",
    ['id' => $_SESSION['user_id']]
)->fetch_one();

if (!$user) {
    session_destroy();
    header('Location: ../../view/auth/login.php');
    exit();
}

// Calculate tier based on points if not set
if (empty($user['member_tier'])) {
    $points = $user['loyalty_points'] ?? 0;
    if ($points >= 2000) {
        $user['member_tier'] = 'platinum';
    } elseif ($points >= 1000) {
        $user['member_tier'] = 'gold';
    } elseif ($points >= 500) {
        $user['member_tier'] = 'silver';
    } else {
        $user['member_tier'] = 'bronze';
    }
}

// Get user initials for avatar
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

// Calculate points to next tier
$next_tier_threshold = 0;
$next_tier_name = '';

if ($user['member_tier'] == 'bronze') {
    $next_tier_threshold = 500;
    $next_tier_name = 'silver';
} elseif ($user['member_tier'] == 'silver') {
    $next_tier_threshold = 1000;
    $next_tier_name = 'gold';
} elseif ($user['member_tier'] == 'gold') {
    $next_tier_threshold = 2000;
    $next_tier_name = 'platinum';
} else {
    $next_tier_threshold = $user['loyalty_points'];
    $next_tier_name = 'platinum (max)';
}

$points_to_next = max(0, $next_tier_threshold - $user['loyalty_points']);
$progress_percentage = $next_tier_threshold > 0
    ? min(100, ($user['loyalty_points'] / $next_tier_threshold) * 100)
    : 100;

// Get unread notifications count (placeholder)
$unread_count = 3;

// Clear session data after displaying
unset($_SESSION['error']);
unset($_SESSION['success']);
unset($_SESSION['form_data']);