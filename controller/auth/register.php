<?php
require_once '../../Class/Database.php';

$config = require_once '../../config/config.php';
$db = new Database($config['database']);

session_start();

$_SESSION['error'] ??= [];
$_SESSION['success'] ??= [];
$_SESSION['old'] ??= [];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location:' . ($_SERVER['HTTP_REFERER'] ?? '/'));
    exit();
}

$errors = [];

// Get and sanitize inputs
$full_name = trim(filter_input(INPUT_POST, 'full_name', FILTER_SANITIZE_STRING));
$email = trim(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL));
$phone = trim(filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING));
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';
$terms = isset($_POST['terms']) ? true : false;

// Get client IP address for security logging
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

// ===== VALIDATION =====

// Full Name validation
if (empty($full_name)) {
    $errors['full_name'] = 'Full name is required';
} elseif (strlen($full_name) < 2 || strlen($full_name) > 100) {
    $errors['full_name'] = 'Full name must be between 2 and 100 characters';
} elseif (!preg_match("/^[a-zA-Z\s]+$/", $full_name)) {
    $errors['full_name'] = 'Full name can only contain letters and spaces';
}

// Email validation
if (empty($email)) {
    $errors['email'] = 'Email is required';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'Invalid email format';
} elseif (strlen($email) > 100) {
    $errors['email'] = 'Email must not exceed 100 characters';
} elseif (!preg_match("/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/", $email)) {
    $errors['email'] = 'Invalid email format';
}

// Phone validation (Philippine format)
if (empty($phone)) {
    $errors['phone'] = 'Phone number is required';
} else {
    // Remove all non-numeric characters for validation
    $phone_clean = preg_replace('/\D/', '', $phone);
    if (!preg_match("/^(63|0)[0-9]{10}$/", $phone_clean)) {
        $errors['phone'] = 'Invalid Philippine phone number format (e.g., +639123456789 or 09123456789)';
    } elseif (strlen($phone_clean) < 11 || strlen($phone_clean) > 13) {
        $errors['phone'] = 'Phone number must be 11-13 digits';
    }
}

// Password validation
if (empty($password)) {
    $errors['password'] = 'Password is required';
} elseif (strlen($password) < 8) {
    $errors['password'] = 'Password must be at least 8 characters';
} elseif (strlen($password) > 100) {
    $errors['password'] = 'Password must not exceed 100 characters';
} elseif (!preg_match("/[A-Z]/", $password)) {
    $errors['password'] = 'Password must contain at least one uppercase letter';
} elseif (!preg_match("/[a-z]/", $password)) {
    $errors['password'] = 'Password must contain at least one lowercase letter';
} elseif (!preg_match("/\d/", $password)) {
    $errors['password'] = 'Password must contain at least one number';
} elseif (!preg_match("/[^A-Za-z0-9]/", $password)) {
    $errors['password'] = 'Password must contain at least one special character';
}

// Confirm password
if ($password !== $confirm_password) {
    $errors['confirm_password'] = 'Passwords do not match';
}

// Terms agreement
if (!$terms) {
    $errors['terms'] = 'You must agree to the Terms of Service and Privacy Policy';
}

// Check for suspicious patterns (common weak passwords)
$weak_passwords = ['password123', '12345678', 'qwerty123', 'admin123', 'welcome123', 'password'];
if (in_array(strtolower($password), $weak_passwords)) {
    $errors['password'] = 'Password is too common. Please choose a stronger password';
}

// Check for email domain (block temporary/disposable email domains)
$disposable_domains = ['tempmail.com', 'throwaway.com', '10minutemail.com', 'guerrillamail.com', 'mailinator.com'];
$email_domain = substr(strrchr($email, "@"), 1);
if (in_array($email_domain, $disposable_domains)) {
    $errors['email'] = 'Temporary email addresses are not allowed';
}

if (!empty($errors)) {
    $_SESSION['error'] = $errors;
    $_SESSION['old'] = [
        'full_name' => $full_name,
        'email' => $email,
        'phone' => $phone
    ];
    header('Location:' . ($_SERVER['HTTP_REFERER'] ?? '/'));
    exit();
}

try {
    // Check for existing email (case-insensitive)
    $existingEmail = $db->query(
        "SELECT id, status FROM users WHERE LOWER(email) = LOWER(:email)",
        ['email' => $email]
    )->fetch_one();

    if ($existingEmail) {
        if ($existingEmail['status'] === 'inactive') {
            $errors['email'] = 'This email is registered but inactive. Please contact support.';
        } else {
            $errors['email'] = 'Email already registered';
        }
        $_SESSION['error'] = $errors;
        $_SESSION['old'] = [
            'full_name' => $full_name,
            'phone' => $phone
        ];
        header('Location:' . ($_SERVER['HTTP_REFERER'] ?? '/'));
        exit();
    }

    // Check for existing phone
    $existingPhone = $db->query(
        "SELECT id FROM users WHERE phone = :phone",
        ['phone' => $phone]
    )->fetch_one();

    if ($existingPhone) {
        $errors['phone'] = 'Phone number already registered';
        $_SESSION['error'] = $errors;
        $_SESSION['old'] = [
            'full_name' => $full_name,
            'email' => $email
        ];
        header('Location:' . ($_SERVER['HTTP_REFERER'] ?? '/'));
        exit();
    }

    // Rate limiting - Check for too many registration attempts from same IP
    $recentAttempts = $db->query(
        "SELECT COUNT(*) as count FROM login_history 
         WHERE ip_address = :ip AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)",
        ['ip' => $ip_address]
    )->fetch_one();

    if ($recentAttempts && $recentAttempts['count'] > 5) {
        $errors['database'] = 'Too many registration attempts. Please try again later.';
        $_SESSION['error'] = $errors;
        header('Location:' . ($_SERVER['HTTP_REFERER'] ?? '/'));
        exit();
    }

    // Hash password with strong bcrypt
    $hashed_password = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

    // Generate email verification token
    $verification_token = bin2hex(random_bytes(32));
    $token_expires = date('Y-m-d H:i:s', strtotime('+24 hours'));

    // Insert new user
    $db->query(
        "INSERT INTO users (full_name, email, phone, password, email_verification_token, email_verification_expires, status, created_at) 
        VALUES (:full_name, :email, :phone, :password, :token, :expires, 'active', NOW())",
        [
            'full_name' => $full_name,
            'email' => $email,
            'phone' => $phone,
            'password' => $hashed_password,
            'token' => $verification_token,
            'expires' => $token_expires
        ]
    );

    $user_id = $db->lastInsertId();

    // Log registration attempt in login_history
    $db->query(
        "INSERT INTO login_history (user_id, user_name, ip_address, user_agent, status, created_at) 
         VALUES (:user_id, :user_name, :ip, :agent, 'success', NOW())",
        [
            'user_id' => $user_id,
            'user_name' => $full_name,
            'ip' => $ip_address,
            'agent' => $user_agent
        ]
    );

    // TODO: Send verification email (implement email sending here)
    // For now, we'll just set a success message

    $_SESSION['success'][] = 'Registration successful! Please login to continue.';

    // Clear old session data
    unset($_SESSION['old']);

    // Redirect to login page
    header('Location: ../../view/auth/login_form.php');
    exit();

} catch (Exception $e) {
    error_log("Registration error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());

    // Log failed registration attempt
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
    } catch (Exception $logError) {
        error_log("Failed to log registration attempt: " . $logError->getMessage());
    }

    $_SESSION['error']['database'] = 'Registration failed. Please try again later.';
    $_SESSION['old'] = [
        'full_name' => $full_name,
        'email' => $email,
        'phone' => $phone
    ];
    header('Location:' . ($_SERVER['HTTP_REFERER'] ?? '/'));
    exit();
}
?>