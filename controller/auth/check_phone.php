<?php
/**
 * AJAX endpoint to check if phone number already exists
 */

session_start();
require_once '../../Class/Database.php';

header('Content-Type: application/json');

$config = require_once '../../config/config.php';
$db = new Database($config['database']);

$phone = trim($_POST['phone'] ?? '');

if (empty($phone)) {
    echo json_encode(['exists' => false, 'error' => 'Phone required']);
    exit();
}

// Clean phone number (remove non-numeric characters)
$phone_clean = preg_replace('/\D/', '', $phone);

try {
    $user = $db->query(
        "SELECT id FROM users WHERE phone = :phone",
        ['phone' => $phone_clean]
    )->fetch_one();

    echo json_encode(['exists' => !empty($user)]);
} catch (Exception $e) {
    error_log("Phone check error: " . $e->getMessage());
    echo json_encode(['exists' => false, 'error' => 'Database error']);
}
?>