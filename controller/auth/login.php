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

$email = trim(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL));
$password = $_POST['password'] ?? '';
$remember_me = isset($_POST['remember_me']) ? true : false;

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
    header('Location:' . $_SERVER['HTTP_REFERER'] ?? '/');
    exit();
}

try {
    $user = $db->query(
        "SELECT id, full_name, email, phone, password FROM users WHERE email = :email",
        ['email' => $email]
    )->fetch_one();

    if (!$user) {
        $_SESSION['error']['login'] = 'Invalid email or password';
        $_SESSION['old']['email'] = $email;
        header('Location:' . $_SERVER['HTTP_REFERER'] ?? '/');
        exit();
    }

    if (!password_verify($password, $user['password'])) {
        $_SESSION['error']['login'] = 'Invalid email or password';
        $_SESSION['old']['email'] = $email;
        header('Location:' . $_SERVER['HTTP_REFERER'] ?? '/');
        exit();
    }

    if (password_needs_rehash($user['password'], PASSWORD_BCRYPT, ['cost' => 12])) {
        $new_hashed_password = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

        $db->query(
            "UPDATE users SET password = :password WHERE id = :id",
            [
                'password' => $new_hashed_password,
                'id' => $user['id']
            ]
        );
    }

    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['full_name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_phone'] = $user['phone'];
    $_SESSION['logged_in'] = true;
    $_SESSION['login_time'] = time();

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


    header('Location: /index.php');
    exit();

} catch (Exception $e) {
    error_log("Login error: " . $e->getMessage());

    $_SESSION['error']['database'] = 'Login failed. Please try again later.';
    $_SESSION['old']['email'] = $email;
    header('Location:' . $_SERVER['HTTP_REFERER'] ?? '/');
    exit();
}