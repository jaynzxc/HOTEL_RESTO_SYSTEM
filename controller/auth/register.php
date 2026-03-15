<?php
require_once '../../Class/Database.php';

$config = require_once '../../config/config.php';
$db = new Database($config['database']);

session_start();

$_SESSION['error'] ??= [];
$_SESSION['success'] ??= [];
$_SESSION['old'] ??= [];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location:' . $_SERVER['HTTP_REFERER'] ?? '/');
    exit();
}

$errors = [];

$full_name = trim(filter_input(INPUT_POST, 'full_name', FILTER_SANITIZE_STRING));
$email = trim(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL));
$phone = trim(filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING));
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';
$terms = isset($_POST['terms']) ? true : false;

if (empty($full_name)) {
    $errors['full_name'] = 'Full name is required';
} elseif (strlen($full_name) < 2 || strlen($full_name) > 100) {
    $errors['full_name'] = 'Full name must be between 2 and 100 characters';
} elseif (!preg_match("/^[a-zA-Z\s]+$/", $full_name)) {
    $errors['full_name'] = 'Full name can only contain letters and spaces';
}

if (empty($email)) {
    $errors['email'] = 'Email is required';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'Invalid email format';
} elseif (strlen($email) > 100) {
    $errors['email'] = 'Email must not exceed 100 characters';
}

if (empty($phone)) {
    $errors['phone'] = 'Phone number is required';
} elseif (!preg_match("/^(\+63|0)[0-9]{10}$/", preg_replace('/\s+/', '', $phone))) {
    $errors['phone'] = 'Invalid Philippine phone number format (e.g., +639123456789 or 09123456789)';
}

if (empty($password)) {
    $errors['password'] = 'Password is required';
} elseif (strlen($password) < 8) {
    $errors['password'] = 'Password must be at least 8 characters';
} elseif (!preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/", $password)) {
    $errors['password'] = 'Password must contain at least one uppercase letter, one lowercase letter, and one number';
}

if ($password !== $confirm_password) {
    $errors['confirm_password'] = 'Passwords do not match';
}

if (!$terms) {
    $errors['terms'] = 'You must agree to the Terms of Service and Privacy Policy';
}

if (!empty($errors)) {
    $_SESSION['error'] = $errors;
    $_SESSION['old'] = [
        'full_name' => $full_name,
        'email' => $email,
        'phone' => $phone
    ];
    header('Location:' . $_SERVER['HTTP_REFERER'] ?? '/');
    exit();
}

try {
    $existingEmail = $db->query("SELECT id FROM users WHERE email = :email", [
        'email' => $email
    ])->fetch_one();

    if ($existingEmail) {
        $_SESSION['error']['email'] = 'Email already registered';
        $_SESSION['old'] = [
            'full_name' => $full_name,
            'phone' => $phone
        ];
        header('Location:' . $_SERVER['HTTP_REFERER'] ?? '/');
        exit();
    }

    $existingPhone = $db->query("SELECT id FROM users WHERE phone = :phone", [
        'phone' => $phone
    ])->fetch_one();

    if ($existingPhone) {
        $_SESSION['error']['phone'] = 'Phone number already registered';
        $_SESSION['old'] = [
            'full_name' => $full_name,
            'email' => $email
        ];
        header('Location:' . $_SERVER['HTTP_REFERER'] ?? '/');
        exit();
    }

    $hashed_password = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

    $db->query(
        "INSERT INTO users (full_name, email, phone, password, created_at) 
        VALUES (:full_name, :email, :phone, :password, NOW())",
        [
            'full_name' => $full_name,
            'email' => $email,
            'phone' => $phone,
            'password' => $hashed_password
        ]
    );

    $user_id = $db->lastInsertId();

    $_SESSION['success'][] = 'Registration successful! Please login.';

    header('Location:' . $_SERVER['HTTP_REFERER'] ?? '/');
    exit();

} catch (Exception $e) {
    error_log("Registration error: " . $e->getMessage());

    $_SESSION['error']['database'] = 'Registration failed. Please try again later.';
    $_SESSION['old'] = [
        'full_name' => $full_name,
        'email' => $email,
        'phone' => $phone
    ];
    header('Location:' . $_SERVER['HTTP_REFERER'] ?? '/');
    exit();
}