<?php
session_start();

require_once __DIR__ . '/../../../Class/Database.php';

$config = require_once __DIR__ . '/../../../config/config.php';
$db = new Database($config['database']);

$_SESSION['error'] ??= [];
$_SESSION['success'] ??= [];
$_SESSION['form_data'] ??= [];

$user = $db->query(
    "SELECT id, full_name, email, phone, loyalty_points FROM users WHERE id = :id",
    ['id' => $_SESSION['user_id']]
)->fetch_one();

$myReviews = $db->query(
    "SELECT * FROM reviews WHERE user_id = :user_id ORDER BY created_at DESC",
    ['user_id' => $_SESSION['user_id']]
)->find();

$guestReviews = $db->query(
    "SELECT r.*, u.full_name as user_name,
            SUBSTRING(u.full_name, 1, 1) as initial
    FROM reviews r 
    JOIN users u ON r.user_id = u.id 
    WHERE r.user_id != :user_id 
    ORDER BY r.created_at DESC 
    LIMIT 10",
    ['user_id' => $_SESSION['user_id']]
)->find();

$points = $user['loyalty_points'] ?? 0;

$name_parts = explode(' ', $user['full_name']);
$initials = strtoupper(substr($name_parts[0], 0, 1) . (isset($name_parts[1]) ? substr($name_parts[1], 0, 1) : ''));
