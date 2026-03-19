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
$_SESSION['form_data'] ??= [];

// Get user data
$user = $db->query(
    "SELECT id, full_name, first_name, last_name, email, phone, loyalty_points, member_tier, avatar
     FROM users WHERE id = :id",
    ['id' => $_SESSION['user_id']]
)->fetch_one();

// Set points variable for easy access
$points = $user['loyalty_points'] ?? 0;
$member_tier = $user['member_tier'] ?? 'bronze';

// Get available rooms
$rooms = $db->query(
    "SELECT * FROM rooms WHERE is_available = 1 ORDER BY price"
)->find();

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
