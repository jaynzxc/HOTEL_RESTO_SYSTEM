<?php
/**
 * AJAX endpoint to check if email already exists
 */

session_start();
require_once '../../Class/Database.php';

header('Content-Type: application/json');

$config = require_once '../../config/config.php';
$db = new Database($config['database']);

$email = trim($_POST['email'] ?? '');

if (empty($email)) {
    echo json_encode(['exists' => false, 'error' => 'Email required']);
    exit();
}

try {
    $user = $db->query(
        "SELECT id FROM users WHERE LOWER(email) = LOWER(:email)",
        ['email' => $email]
    )->fetch_one();

    echo json_encode(['exists' => !empty($user)]);
} catch (Exception $e) {
    error_log("Email check error: " . $e->getMessage());
    echo json_encode(['exists' => false, 'error' => 'Database error']);
}
?>