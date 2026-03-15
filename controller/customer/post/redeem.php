<?php
session_start();

require_once __DIR__ . '/../../../Class/Database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !$_SESSION['logged_in']) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

$config = require_once __DIR__ . '/../../../config/config.php';
$db = new Database($config['database']);

// Get POST data
$reward_name = $_POST['reward_name'] ?? '';
$points_cost = (int) ($_POST['points_cost'] ?? 0);
$experience = $_POST['experience'] ?? '';

// Validate
if (empty($reward_name) || $points_cost <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid reward data']);
    exit();
}

try {
    // Start transaction
    $db->query("START TRANSACTION");

    // Get current user points
    $user = $db->query(
        "SELECT loyalty_points FROM users WHERE id = :id FOR UPDATE",
        ['id' => $_SESSION['user_id']]
    )->fetch_one();

    if (!$user) {
        throw new Exception('User not found');
    }

    $current_points = $user['loyalty_points'];

    // Check if user has enough points
    if ($current_points < $points_cost) {
        throw new Exception('Insufficient points');
    }

    // Deduct points
    $db->query(
        "UPDATE users SET loyalty_points = loyalty_points - :cost WHERE id = :user_id",
        [
            'cost' => $points_cost,
            'user_id' => $_SESSION['user_id']
        ]
    );

    // Record redemption
    $db->query(
        "INSERT INTO redemptions (user_id, reward_name, points_cost, experience, status, created_at) 
         VALUES (:user_id, :reward_name, :points_cost, :experience, 'pending', NOW())",
        [
            'user_id' => $_SESSION['user_id'],
            'reward_name' => $reward_name,
            'points_cost' => $points_cost,
            'experience' => $experience
        ]
    );

    // Commit transaction
    $db->query("COMMIT");

    // Get updated points
    $updated_user = $db->query(
        "SELECT loyalty_points FROM users WHERE id = :id",
        ['id' => $_SESSION['user_id']]
    )->fetch_one();

    echo json_encode([
        'success' => true,
        'message' => 'Reward redeemed successfully!',
        'new_points' => $updated_user['loyalty_points']
    ]);

} catch (Exception $e) {
    // Rollback on error
    $db->query("ROLLBACK");

    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>