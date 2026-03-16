<?php
/**
 * POST Controller - Admin Loyalty Actions
 * Handles all loyalty program management actions
 */

session_start();
require_once __DIR__ . '/../../../Class/Database.php';

// Set JSON header for AJAX response
header('Content-Type: application/json');

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || !$_SESSION['logged_in']) {
    echo json_encode([
        'success' => false,
        'message' => 'Please login to continue'
    ]);
    exit();
}

// if (($_SESSION['user_role'] ?? 'customer') !== 'admin') {
//     echo json_encode([
//         'success' => false,
//         'message' => 'Unauthorized access'
//     ]);
//     exit();
// }

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
    exit();
}

$config = require __DIR__ . '/../../../config/config.php';
$db = new Database($config['database']);

try {
    $action = $_POST['action'] ?? '';

    // CREATE REWARD
    if ($action === 'create_reward') {
        $reward_name = trim($_POST['reward_name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $points_cost = intval($_POST['points_cost'] ?? 0);
        $category = $_POST['category'] ?? 'other';
        $stock_limit = !empty($_POST['stock_limit']) ? intval($_POST['stock_limit']) : null;

        if (empty($reward_name)) {
            throw new Exception('Reward name is required');
        }
        if ($points_cost <= 0) {
            throw new Exception('Points cost must be greater than 0');
        }

        $db->query(
            "INSERT INTO rewards (reward_name, description, points_cost, category, stock_limit, created_at) 
             VALUES (:name, :desc, :cost, :category, :stock, NOW())",
            [
                'name' => $reward_name,
                'desc' => $description,
                'cost' => $points_cost,
                'category' => $category,
                'stock' => $stock_limit
            ]
        );

        echo json_encode([
            'success' => true,
            'message' => 'Reward created successfully'
        ]);
        exit();
    }

    // UPDATE REWARD
    elseif ($action === 'update_reward') {
        $reward_id = intval($_POST['reward_id'] ?? 0);
        $reward_name = trim($_POST['reward_name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $points_cost = intval($_POST['points_cost'] ?? 0);
        $category = $_POST['category'] ?? 'other';
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        $stock_limit = !empty($_POST['stock_limit']) ? intval($_POST['stock_limit']) : null;

        if (!$reward_id) {
            throw new Exception('Invalid reward ID');
        }
        if (empty($reward_name)) {
            throw new Exception('Reward name is required');
        }
        if ($points_cost <= 0) {
            throw new Exception('Points cost must be greater than 0');
        }

        $db->query(
            "UPDATE rewards 
             SET reward_name = :name, description = :desc, points_cost = :cost, 
                 category = :category, is_active = :active, stock_limit = :stock
             WHERE id = :id",
            [
                'id' => $reward_id,
                'name' => $reward_name,
                'desc' => $description,
                'cost' => $points_cost,
                'category' => $category,
                'active' => $is_active,
                'stock' => $stock_limit
            ]
        );

        echo json_encode([
            'success' => true,
            'message' => 'Reward updated successfully'
        ]);
        exit();
    }

    // DELETE REWARD
    elseif ($action === 'delete_reward') {
        $reward_id = intval($_POST['reward_id'] ?? 0);

        if (!$reward_id) {
            throw new Exception('Invalid reward ID');
        }

        // Check if reward has been redeemed
        $redemptions = $db->query(
            "SELECT COUNT(*) as count FROM redemptions WHERE reward_id = :id",
            ['id' => $reward_id]
        )->fetch_one();

        if ($redemptions['count'] > 0) {
            // Soft delete - just deactivate
            $db->query(
                "UPDATE rewards SET is_active = 0 WHERE id = :id",
                ['id' => $reward_id]
            );
            $message = 'Reward deactivated (has redemption history)';
        } else {
            // Hard delete
            $db->query(
                "DELETE FROM rewards WHERE id = :id",
                ['id' => $reward_id]
            );
            $message = 'Reward deleted successfully';
        }

        echo json_encode([
            'success' => true,
            'message' => $message
        ]);
        exit();
    }

    // ADJUST MEMBER POINTS
    elseif ($action === 'adjust_points') {
        $user_id = intval($_POST['user_id'] ?? 0);
        $points = intval($_POST['points'] ?? 0);
        $type = $_POST['type'] ?? 'add'; // add or subtract
        $reason = trim($_POST['reason'] ?? '');

        if (!$user_id || $points <= 0) {
            throw new Exception('Invalid user ID or points amount');
        }

        $db->query("START TRANSACTION");

        // Get current points
        $user = $db->query(
            "SELECT loyalty_points FROM users WHERE id = :id FOR UPDATE",
            ['id' => $user_id]
        )->fetch_one();

        if (!$user) {
            throw new Exception('User not found');
        }

        $new_points = $user['loyalty_points'];
        if ($type === 'add') {
            $new_points += $points;
        } else {
            if ($user['loyalty_points'] < $points) {
                throw new Exception('Insufficient points to subtract');
            }
            $new_points -= $points;
        }

        // Update user points
        $db->query(
            "UPDATE users SET loyalty_points = :points WHERE id = :id",
            ['points' => $new_points, 'id' => $user_id]
        );

        // Create notification for user
        $db->query(
            "INSERT INTO notifications (user_id, title, message, type, icon, created_at) 
             VALUES (:user_id, 'Points Adjusted', :message, 'info', 'fa-star', NOW())",
            [
                'user_id' => $user_id,
                'message' => "Your points have been adjusted by " . ($type === 'add' ? '+' : '-') . "$points. Reason: " . ($reason ?: 'Administrative adjustment')
            ]
        );

        $db->query("COMMIT");

        echo json_encode([
            'success' => true,
            'message' => 'Points adjusted successfully',
            'new_points' => $new_points
        ]);
        exit();
    }

    // UPDATE TIER THRESHOLDS
    elseif ($action === 'update_tiers') {
        $bronze = intval($_POST['bronze'] ?? 0);
        $silver = intval($_POST['silver'] ?? 500);
        $gold = intval($_POST['gold'] ?? 1000);
        $platinum = intval($_POST['platinum'] ?? 2000);

        // Store in settings table (create if not exists)
        $db->query(
            "INSERT INTO settings (setting_key, setting_value) VALUES 
             ('tier_bronze', :bronze),
             ('tier_silver', :silver),
             ('tier_gold', :gold),
             ('tier_platinum', :platinum)
             ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)",
            [
                'bronze' => $bronze,
                'silver' => $silver,
                'gold' => $gold,
                'platinum' => $platinum
            ]
        );

        // Update all users' tiers based on new thresholds
        $db->query("
            UPDATE users SET member_tier = 
            CASE 
                WHEN loyalty_points >= :platinum THEN 'platinum'
                WHEN loyalty_points >= :gold THEN 'gold'
                WHEN loyalty_points >= :silver THEN 'silver'
                ELSE 'bronze'
            END
        ", [
            'platinum' => $platinum,
            'gold' => $gold,
            'silver' => $silver
        ]);

        echo json_encode([
            'success' => true,
            'message' => 'Tier thresholds updated successfully'
        ]);
        exit();
    }

    // EXPORT DATA
    elseif ($action === 'export_data') {
        $format = $_POST['format'] ?? 'csv';
        $type = $_POST['export_type'] ?? 'members'; // members, rewards, redemptions

        if ($type === 'members') {
            $data = $db->query(
                "SELECT id, full_name, email, phone, loyalty_points, member_tier, last_login, created_at
                 FROM users WHERE role = 'customer'
                 ORDER BY loyalty_points DESC",
                []
            )->find() ?: [];
        } elseif ($type === 'rewards') {
            $data = $db->query(
                "SELECT * FROM rewards ORDER BY points_cost ASC",
                []
            )->find() ?: [];
        } else {
            $data = $db->query(
                "SELECT r.id, u.full_name, r.reward_name, r.points_cost, r.created_at
                 FROM redemptions r
                 JOIN users u ON r.user_id = u.id
                 ORDER BY r.created_at DESC",
                []
            )->find() ?: [];
        }

        if ($format === 'csv') {
            $filename = $type . '_export_' . date('Y-m-d') . '.csv';
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="' . $filename . '"');

            $output = fopen('php://output', 'w');

            // Headers
            if (!empty($data)) {
                fputcsv($output, array_keys($data[0]));

                // Data
                foreach ($data as $row) {
                    fputcsv($output, $row);
                }
            }
            fclose($output);
            exit();
        } else {
            header('Content-Type: application/json');
            header('Content-Disposition: attachment; filename="' . $type . '_export_' . date('Y-m-d') . '.json"');
            echo json_encode($data, JSON_PRETTY_PRINT);
            exit();
        }
    }

    // GET MEMBER DETAILS
    elseif ($action === 'get_member') {
        $user_id = intval($_POST['user_id'] ?? 0);

        if (!$user_id) {
            throw new Exception('Invalid user ID');
        }

        $member = $db->query(
            "SELECT id, full_name, email, phone, loyalty_points, member_tier, 
                    created_at, last_login
             FROM users WHERE id = :id",
            ['id' => $user_id]
        )->fetch_one();

        if (!$member) {
            throw new Exception('Member not found');
        }

        // Get redemption history
        $redemptions = $db->query(
            "SELECT * FROM redemptions WHERE user_id = :user_id ORDER BY created_at DESC LIMIT 5",
            ['user_id' => $user_id]
        )->find() ?: [];

        echo json_encode([
            'success' => true,
            'member' => $member,
            'redemptions' => $redemptions
        ]);
        exit();
    }

    // GET ALL REWARDS (for management)
    elseif ($action === 'get_rewards') {
        $rewards = $db->query(
            "SELECT * FROM rewards ORDER BY is_active DESC, points_cost ASC",
            []
        )->find() ?: [];

        echo json_encode([
            'success' => true,
            'rewards' => $rewards
        ]);
        exit();
    } else {
        throw new Exception('Invalid action');
    }

} catch (Exception $e) {
    if (isset($db)) {
        $db->query("ROLLBACK");
    }
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
exit();
?>