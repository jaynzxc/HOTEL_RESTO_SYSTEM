<?php
session_start();

require_once __DIR__ . '/../../../Class/Database.php';

if (!isset($_SESSION['user_id']) || !$_SESSION['logged_in']) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

$config = require_once __DIR__ . '/../../../config/config.php';
$db = new Database($config['database']);

$reward_name = $_POST['reward_name'] ?? '';
$points_cost = (int) ($_POST['points_cost'] ?? 0);
$experience = $_POST['experience'] ?? '';

// Log the received data for debugging
error_log("Redeem attempt: reward_name=$reward_name, points_cost=$points_cost, experience=$experience");

if (empty($reward_name) || $points_cost <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid reward data']);
    exit();
}

try {
    $db->beginTransaction();

    // Check for outstanding balance AND pending payments from current_balance
    $balanceData = $db->query(
        "SELECT total_balance, pending_balance, available_balance 
         FROM current_balance 
         WHERE user_id = :user_id",
        ['user_id' => $_SESSION['user_id']]
    )->fetch_one();

    error_log("Balance data: " . print_r($balanceData, true));

    // Block redemption if there's ANY outstanding balance OR pending payments
    if ($balanceData) {
        $totalOutstanding = $balanceData['total_balance'] ?? 0;
        $pendingBalance = $balanceData['pending_balance'] ?? 0;

        // Check if total_balance > 0 OR pending_balance > 0
        if ($totalOutstanding > 0 || $pendingBalance > 0) {
            $message = 'Please clear your ';
            if ($totalOutstanding > 0 && $pendingBalance > 0) {
                $message .= 'outstanding balance (₱' . number_format($totalOutstanding, 2) . ') and pending payments (₱' . number_format($pendingBalance, 2) . ')';
            } elseif ($totalOutstanding > 0) {
                $message .= 'outstanding balance of ₱' . number_format($totalOutstanding, 2);
            } elseif ($pendingBalance > 0) {
                $message .= 'pending payments of ₱' . number_format($pendingBalance, 2) . ' (waiting for admin approval)';
            }
            $message .= ' before redeeming rewards.';
            throw new Exception($message);
        }
    }

    // Get user with lock
    $user = $db->query(
        "SELECT loyalty_points FROM users WHERE id = :id FOR UPDATE",
        ['id' => $_SESSION['user_id']]
    )->fetch_one();

    if (!$user) {
        throw new Exception('User not found');
    }

    $current_points = $user['loyalty_points'];
    error_log("Current user points: $current_points");

    if ($current_points < $points_cost) {
        throw new Exception('Insufficient points');
    }

    // Check if reward exists in rewards table
    $reward = $db->query(
        "SELECT * FROM rewards WHERE reward_name = :reward_name AND is_active = 1",
        ['reward_name' => $reward_name]
    )->fetch_one();

    if ($reward) {
        // Check stock if applicable
        if ($reward['stock_limit'] !== null) {
            $available = $reward['stock_limit'] - $reward['times_redeemed'];
            if ($available <= 0) {
                throw new Exception('This reward is out of stock');
            }
        }
    }

    // Update user points
    $updateResult = $db->query(
        "UPDATE users SET loyalty_points = loyalty_points - :cost WHERE id = :user_id",
        [
            'cost' => $points_cost,
            'user_id' => $_SESSION['user_id']
        ]
    );

    error_log("Update user points result: " . ($updateResult ? "success" : "failed"));

    // Insert redemption record
    $insertResult = $db->query(
        "INSERT INTO redemptions (user_id, reward_name, points_cost, experience, status, created_at) 
         VALUES (:user_id, :reward_name, :points_cost, :experience, 'pending', NOW())",
        [
            'user_id' => $_SESSION['user_id'],
            'reward_name' => $reward_name,
            'points_cost' => $points_cost,
            'experience' => $experience
        ]
    );

    error_log("Insert redemption result: " . ($insertResult ? "success" : "failed"));

    // If there was an error with the insert, throw exception
    if (!$insertResult) {
        throw new Exception('Failed to record redemption');
    }

    // Update reward times_redeemed if it exists
    if ($reward) {
        $db->query(
            "UPDATE rewards SET times_redeemed = times_redeemed + 1 WHERE id = :id",
            ['id' => $reward['id']]
        );
    }

    // Create notification for admin about redemption
    $admins = $db->query(
        "SELECT id FROM users WHERE role = 'admin'"
    )->find();

    foreach ($admins as $admin) {
        $db->query(
            "INSERT INTO notifications (user_id, title, message, type, icon, link, created_at) 
             VALUES (:user_id, 'Reward Redemption', :message, 'info', 'fa-gift', :link, NOW())",
            [
                'user_id' => $admin['id'],
                'message' => "User redeemed: $reward_name for $points_cost points",
                'link' => '/src/admin/customer_management/loyalty_rewards.php'
            ]
        );
    }

    $db->commit();

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
    $db->rollBack();
    error_log("Redeem error: " . $e->getMessage());

    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>