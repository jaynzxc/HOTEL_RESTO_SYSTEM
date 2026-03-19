<?php
session_start();

require_once __DIR__ . '/../../../Class/Database.php';

$config = require_once __DIR__ . '/../../../config/config.php';
$db = new Database($config['database']);

$_SESSION['error'] ??= [];
$_SESSION['success'] ??= [];
$_SESSION['form_data'] ??= [];

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !$_SESSION['logged_in']) {
    header('Location: ../../../src/login-register/login_form.php');
    exit();
}

$user = $db->query(
    "SELECT id, full_name, email, phone, loyalty_points, member_tier, avatar
     FROM users WHERE id = :id",
    ['id' => $_SESSION['user_id']]
)->fetch_one();

// Get user's reviews with admin responses
$myReviews = $db->query(
    "SELECT r.*, 
            rr.id as response_id, 
            rr.response_text, 
            rr.responded_at,
            CONCAT(ru.first_name, ' ', ru.last_name) as responder_name
     FROM reviews r
     LEFT JOIN review_responses rr ON r.id = rr.review_id
     LEFT JOIN users ru ON rr.responded_by = ru.id
     WHERE r.user_id = :user_id 
     ORDER BY r.created_at DESC",
    ['user_id' => $_SESSION['user_id']]
)->find();

// Get other users' reviews with admin responses
$guestReviews = $db->query(
    "SELECT r.*, 
            u.full_name as user_name,
            u.member_tier,
            SUBSTRING(u.full_name, 1, 1) as initial,
            rr.id as response_id, 
            rr.response_text as admin_response, 
            rr.responded_at as response_date,
            CONCAT(ru.first_name, ' ', ru.last_name) as responder_name
     FROM reviews r 
     JOIN users u ON r.user_id = u.id 
     LEFT JOIN review_responses rr ON r.id = rr.review_id
     LEFT JOIN users ru ON rr.responded_by = ru.id
     WHERE r.user_id != :user_id 
     ORDER BY r.created_at DESC 
     LIMIT 10",
    ['user_id' => $_SESSION['user_id']]
)->find();



$points = $user['loyalty_points'] ?? 0;

$name_parts = explode(' ', trim($user['full_name']));
$initials = strtoupper(substr($name_parts[0], 0, 1) . (isset($name_parts[1]) ? substr($name_parts[1], 0, 1) : ''));

// Clear session form data
unset($_SESSION['form_data']);

// Store data for view
$viewData = [
    'user' => $user,
    'myReviews' => $myReviews,
    'guestReviews' => $guestReviews,
    'points' => $points,
    'initials' => $initials,
];

// Extract variables for view
extract($viewData);
?>