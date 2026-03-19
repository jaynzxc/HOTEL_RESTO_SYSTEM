<?php
require_once '../../Class/Database.php';

$config = require_once '../../config/config.php';
$db = new Database($config['database']);

session_start();

// Redirect if already logged in
if (isset($_SESSION['user_id']) && $_SESSION['logged_in'] === true) {
    // Fix: Your condition had 'admin' twice, now checks properly
    if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'staff') {
        header('Location: ../../src/admin_portal/dashboard.php');
        exit();
    } elseif ($_SESSION['role'] === 'customer') {
        header('Location: ../../src/customer_portal/dashboard.php');
        exit();
    }
}

// Initialize session arrays
$_SESSION['error'] ??= [];
$_SESSION['success'] ??= [];
$_SESSION['old'] ??= [];

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Fix: Use null coalescing properly and provide a default
    $redirect = $_SERVER['HTTP_REFERER'] ?? '../../view/auth/login.php';
    header('Location: ' . $redirect);
    exit();
}

$errors = [];

$email = trim(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL));
$password = $_POST['password'] ?? '';
$remember_me = isset($_POST['remember_me']) ? true : false;

// Validation
if (empty($email)) {
    $errors['email'] = 'Email is required';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'Invalid email format';
}

if (empty($password)) {
    $errors['password'] = 'Password is required';
}

if (!empty($errors)) {
    $_SESSION['error'] = $errors;
    $_SESSION['old']['email'] = $email;
    // Fix: Use proper redirect with default
    $redirect = $_SERVER['HTTP_REFERER'] ?? '../../view/auth/login.php';
    header('Location: ' . $redirect);
    exit();
}

try {
    // Fix: Added status check to ensure account is active
    $user = $db->query(
        "SELECT id, full_name, email, phone, role, password, status 
         FROM users WHERE email = :email",
        ['email' => $email]
    )->fetch_one();

    if (!$user) {
        $_SESSION['error']['login'] = 'Invalid email or password';
        $_SESSION['old']['email'] = $email;
        $redirect = $_SERVER['HTTP_REFERER'] ?? '../../view/auth/login.php';
        header('Location: ' . $redirect);
        exit();
    }

    // Check if account is active
    if ($user['status'] !== 'active') {
        $_SESSION['error']['login'] = 'Your account is not active. Please contact support.';
        $_SESSION['old']['email'] = $email;
        $redirect = $_SERVER['HTTP_REFERER'] ?? '../../view/auth/login.php';
        header('Location: ' . $redirect);
        exit();
    }

    if (!password_verify($password, $user['password'])) {
        $_SESSION['error']['login'] = 'Invalid email or password';
        $_SESSION['old']['email'] = $email;
        $redirect = $_SERVER['HTTP_REFERER'] ?? '../../view/auth/login.php';
        header('Location: ' . $redirect);
        exit();
    }

    if (password_needs_rehash($user['password'], PASSWORD_DEFAULT, ['cost' => 12])) {
        $new_hashed_password = password_hash($password, PASSWORD_DEFAULT, ['cost' => 12]);

        $db->query(
            "UPDATE users SET password = :password WHERE id = :id",
            [
                'password' => $new_hashed_password,
                'id' => $user['id']
            ]
        );
    }

    // Update last login time
    $db->query(
        "UPDATE users SET last_login = NOW() WHERE id = :id",
        ['id' => $user['id']]
    );

    // Set session variables
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['full_name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_phone'] = $user['phone'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['logged_in'] = true;
    $_SESSION['login_time'] = time();

    // Handle remember me
    if ($remember_me) {
        $remember_token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+30 days'));

        $db->query(
            "UPDATE users SET remember_token = :token, token_expires = :expires WHERE id = :id",
            [
                'token' => $remember_token,
                'expires' => $expires,
                'id' => $user['id']
            ]
        );

        setcookie('remember_token', $remember_token, time() + (86400 * 30), '/', '', false, true);
        setcookie('user_email', $email, time() + (86400 * 30), '/');
    }

    $_SESSION['success'][] = 'Welcome back, ' . $user['full_name'] . '!';

    // Redirect based on role
    if ($user['role'] === 'admin' || $user['role'] === 'staff') {
        header('Location: ../../src/admin_portal/dashboard.php');
    } else {
        header('Location: ../../src/customer_portal/dashboard.php');
    }
    exit();

} catch (Exception $e) {
    error_log("Login error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());

    $_SESSION['error']['database'] = 'Login failed. Please try again later.';
    $_SESSION['old']['email'] = $email;
    $redirect = $_SERVER['HTTP_REFERER'] ?? '../../view/auth/login.php';
    header('Location: ' . $redirect);
    exit();
}