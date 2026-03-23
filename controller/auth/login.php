<?php
require_once '../../Class/Database.php';

// Load configuration (automatically detects Railway or local)
$config = require_once '../../config/config.php';

// Create database connection with error handling
try {
    $db = new Database($config['database']);
} catch (PDOException $e) {
    // Log the error and show user-friendly message
    error_log("Login: Database connection failed - " . $e->getMessage());
    
    if ($config['debug']) {
        die("Database connection error: " . $e->getMessage());
    } else {
        die("Unable to connect to the database. Please try again later.");
    }
}

session_start();

// Redirect if already logged in
if (isset($_SESSION['user_id']) && $_SESSION['logged_in'] === true) {
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
    $redirect = $_SERVER['HTTP_REFERER'] ?? '../../view/auth/login.php';
    header('Location: ' . $redirect);
    exit();
}

$errors = [];
$email = trim(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL));
$password = $_POST['password'] ?? '';
$remember_me = isset($_POST['remember_me']) ? true : false;

// Get client IP address
function getClientIP()
{
    $ipaddress = '';
    if (isset($_SERVER['HTTP_CLIENT_IP']))
        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
    else if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    else if (isset($_SERVER['HTTP_X_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
    else if (isset($_SERVER['HTTP_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
    else if (isset($_SERVER['HTTP_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_FORWARDED'];
    else if (isset($_SERVER['REMOTE_ADDR']))
        $ipaddress = $_SERVER['REMOTE_ADDR'];
    else
        $ipaddress = 'UNKNOWN';
    return $ipaddress;
}

$ip_address = getClientIP();
$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';

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

    // Log failed login attempt
    try {
        $db->query(
            "INSERT INTO login_history (user_id, user_name, ip_address, user_agent, status, created_at) 
             VALUES (NULL, :email, :ip, :agent, 'failed', NOW())",
            [
                'email' => $email,
                'ip' => $ip_address,
                'agent' => $user_agent
            ]
        );
    } catch (Exception $e) {
        error_log("Failed to log login attempt: " . $e->getMessage());
    }

    $redirect = $_SERVER['HTTP_REFERER'] ?? '../../view/auth/login.php';
    header('Location: ' . $redirect);
    exit();
}

try {
    $user = $db->query(
        "SELECT id, full_name, email, phone, role, password, status 
         FROM users WHERE email = :email",
        ['email' => $email]
    )->fetch_one();

    if (!$user) {
        $_SESSION['error']['login'] = 'Invalid email or password';
        $_SESSION['old']['email'] = $email;

        // Log failed login attempt
        $db->query(
            "INSERT INTO login_history (user_id, user_name, ip_address, user_agent, status, created_at) 
             VALUES (NULL, :email, :ip, :agent, 'failed', NOW())",
            [
                'email' => $email,
                'ip' => $ip_address,
                'agent' => $user_agent
            ]
        );

        $redirect = $_SERVER['HTTP_REFERER'] ?? '../../view/auth/login.php';
        header('Location: ' . $redirect);
        exit();
    }

    // Check if account is active
    if ($user['status'] !== 'active') {
        $_SESSION['error']['login'] = 'Your account is not active. Please contact support.';
        $_SESSION['old']['email'] = $email;

        // Log failed login attempt (account inactive)
        $db->query(
            "INSERT INTO login_history (user_id, user_name, ip_address, user_agent, status, created_at) 
             VALUES (:user_id, :user_name, :ip, :agent, 'failed', NOW())",
            [
                'user_id' => $user['id'],
                'user_name' => $user['full_name'],
                'ip' => $ip_address,
                'agent' => $user_agent
            ]
        );

        $redirect = $_SERVER['HTTP_REFERER'] ?? '../../view/auth/login.php';
        header('Location: ' . $redirect);
        exit();
    }

    if (!password_verify($password, $user['password'])) {
        $_SESSION['error']['login'] = 'Invalid email or password';
        $_SESSION['old']['email'] = $email;

        // Log failed login attempt (wrong password)
        $db->query(
            "INSERT INTO login_history (user_id, user_name, ip_address, user_agent, status, created_at) 
             VALUES (:user_id, :user_name, :ip, :agent, 'failed', NOW())",
            [
                'user_id' => $user['id'],
                'user_name' => $user['full_name'],
                'ip' => $ip_address,
                'agent' => $user_agent
            ]
        );

        $redirect = $_SERVER['HTTP_REFERER'] ?? '../../view/auth/login.php';
        header('Location: ' . $redirect);
        exit();
    }

    // Rehash password if needed
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

    // Log successful login
    $db->query(
        "INSERT INTO login_history (user_id, user_name, ip_address, user_agent, status, created_at) 
         VALUES (:user_id, :user_name, :ip, :agent, 'success', NOW())",
        [
            'user_id' => $user['id'],
            'user_name' => $user['full_name'],
            'ip' => $ip_address,
            'agent' => $user_agent
        ]
    );

    // Set session variables
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['full_name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_phone'] = $user['phone'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['logged_in'] = true;
    $_SESSION['login_time'] = time();
    $_SESSION['login_ip'] = $ip_address;

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

    // Log database error as failed login
    try {
        $db->query(
            "INSERT INTO login_history (user_id, user_name, ip_address, user_agent, status, created_at) 
             VALUES (NULL, :email, :ip, :agent, 'failed', NOW())",
            [
                'email' => $email,
                'ip' => $ip_address,
                'agent' => $user_agent
            ]
        );
    } catch (Exception $e) {
        error_log("Failed to log login attempt: " . $e->getMessage());
    }

    $redirect = $_SERVER['HTTP_REFERER'] ?? '../../view/auth/login.php';
    header('Location: ' . $redirect);
    exit();
}
?>