<?php
session_start();

// Fix the path to Database.php
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

// Get user data with loyalty points
$user = $db->query(
    "SELECT id, full_name, email, phone, loyalty_points FROM users WHERE id = :id",
    ['id' => $_SESSION['user_id']]
)->fetch_one();

// Get points history from reviews and other activities
// For now, we'll get from reviews table
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
     -- Add other point-earning activities here in the future
     SELECT 
        'redemption' as activity_type,
        created_at as date,
        CONCAT('Redeemed: ', experience) as description,
        -20 as points,
        'redeemed' as type
     FROM redemptions 
     WHERE user_id = :user_id
     ORDER BY date DESC 
     LIMIT 20",
    ['user_id' => $_SESSION['user_id']]
)->find();

// If no history yet, create empty array
if (!$pointsHistory) {
    $pointsHistory = [];
}

// Calculate tier based on points
$points = $user['loyalty_points'] ?? 0;

// Determine tier
if ($points >= 2000) {
    $tier = 'gold';
    $nextTier = 'platinum';
    $nextThreshold = 3000;
    $currentThreshold = 2000;
    $perks = ['5%', '2x', 'welcome drink'];
} elseif ($points >= 1000) {
    $tier = 'silver';
    $nextTier = 'gold';
    $nextThreshold = 2000;
    $currentThreshold = 1000;
    $perks = ['2%', '1.5x', 'free coffee'];
} else {
    $tier = 'member';
    $nextTier = 'silver';
    $nextThreshold = 1000;
    $currentThreshold = 0;
    $perks = ['0%', '1x', '—'];
}

// Calculate progress to next tier
$pointsToNext = $nextThreshold - $points;
$progress = ($points - $currentThreshold) / ($nextThreshold - $currentThreshold) * 100;
$progress = min(100, max(0, $progress)); // Clamp between 0 and 100

// Get user initials
$name_parts = explode(' ', $user['full_name']);
$initials = strtoupper(substr($name_parts[0], 0, 1) . (isset($name_parts[1]) ? substr($name_parts[1], 0, 1) : '')); 

// Clear session data after displaying
unset($_SESSION['error']);
unset($_SESSION['success']);
?>