<?php
/**
 * POST Controller - Admin Orders / POS Actions
 * Handles updating order status, managing kitchen queue
 */

session_start();
require_once __DIR__ . '/../../../../Class/Database.php';

// Set JSON header for AJAX response
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !$_SESSION['logged_in']) {
    echo json_encode([
        'success' => false,
        'message' => 'Please login to continue'
    ]);
    exit();
}

$config = require __DIR__ . '/../../../../config/config.php';
$db = new Database($config['database']);

// Get user role from database
$user = $db->query(
    "SELECT role FROM users WHERE id = :id",
    ['id' => $_SESSION['user_id']]
)->fetch_one();

// Check if user has admin or staff role
if (!$user || !in_array($user['role'], ['admin', 'staff'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access'
    ]);
    exit();
}

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
    exit();
}

try {
    $action = $_POST['action'] ?? '';

    // UPDATE ORDER STATUS
    if ($action === 'update_status') {
        $order_id = $_POST['order_id'] ?? '';
        $new_status = $_POST['status'] ?? '';

        if (empty($order_id)) {
            throw new Exception('Order ID required');
        }

        if (!in_array($new_status, ['pending', 'preparing', 'ready', 'served', 'completed', 'cancelled'])) {
            throw new Exception('Invalid status');
        }

        $db->beginTransaction();

        // Get current order
        $order = $db->query(
            "SELECT * FROM food_orders WHERE order_reference = :ref",
            ['ref' => $order_id]
        )->fetch_one();

        if (!$order) {
            throw new Exception('Order not found');
        }

        $old_status = $order['status'];

        // Update order status
        $db->query(
            "UPDATE food_orders SET 
                status = :status,
                updated_at = NOW()
             WHERE order_reference = :ref",
            [
                'status' => $new_status,
                'ref' => $order_id
            ]
        );

        // Award points if order is completed
        if ($new_status === 'completed' && $order['points_earned'] > 0 && $order['user_id']) {
            $db->query(
                "UPDATE users SET loyalty_points = loyalty_points + :points WHERE id = :user_id",
                [
                    'points' => $order['points_earned'],
                    'user_id' => $order['user_id']
                ]
            );

            // Create notification for user
            $db->query(
                "INSERT INTO notifications (user_id, title, message, type, icon, link, created_at) 
                 VALUES (:user_id, 'Points Earned!', :message, 'loyalty', 'fa-star', '/src/customer_portal/loyalty_rewards.php', NOW())",
                [
                    'user_id' => $order['user_id'],
                    'message' => "You earned {$order['points_earned']} points from your order #{$order_id}"
                ]
            );
        }

        // Create notification for kitchen
        $db->query(
            "INSERT INTO notifications (user_id, title, message, type, icon, created_at) 
             VALUES (:user_id, 'Order Status Update', :message, 'info', 'fa-utensils', NOW())",
            [
                'user_id' => $_SESSION['user_id'],
                'message' => "Order #{$order_id} status changed from {$old_status} to {$new_status}"
            ]
        );

        $db->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Order status updated successfully',
            'old_status' => $old_status,
            'new_status' => $new_status,
            'points_earned' => ($new_status === 'completed') ? $order['points_earned'] : 0
        ]);
        exit();
    }

    // GET ORDER DETAILS
    elseif ($action === 'get_order_details') {
        $order_id = $_POST['order_id'] ?? '';

        if (empty($order_id)) {
            throw new Exception('Order ID required');
        }

        $order = $db->query(
            "SELECT 
                fo.*,
                u.full_name,
                u.email,
                u.phone,
                rt.table_number
             FROM food_orders fo
             LEFT JOIN users u ON fo.user_id = u.id
             LEFT JOIN restaurant_reservations rr ON rr.user_id = fo.user_id AND rr.reservation_date = CURDATE()
             LEFT JOIN restaurant_tables rt ON rr.table_number = rt.table_number
             WHERE fo.order_reference = :ref",
            ['ref' => $order_id]
        )->fetch_one();

        if (!$order) {
            throw new Exception('Order not found');
        }

        // Parse items JSON
        $order['items'] = json_decode($order['items'], true);

        echo json_encode([
            'success' => true,
            'order' => $order
        ]);
        exit();
    }

    // GET KITCHEN QUEUE
    elseif ($action === 'get_kitchen_queue') {
        $queue = $db->query(
            "SELECT 
                fo.id,
                fo.order_reference,
                fo.items,
                fo.order_type,
                fo.created_at,
                JSON_LENGTH(fo.items) as item_count,
                u.full_name as customer_name,
                rt.table_number,
                TIMESTAMPDIFF(MINUTE, fo.created_at, NOW()) as minutes_ago
             FROM food_orders fo
             LEFT JOIN users u ON fo.user_id = u.id
             LEFT JOIN restaurant_reservations rr ON rr.user_id = fo.user_id AND rr.reservation_date = CURDATE()
             LEFT JOIN restaurant_tables rt ON rr.table_number = rt.table_number
             WHERE fo.status = 'preparing'
             ORDER BY fo.created_at ASC",
            []
        )->find() ?: [];

        echo json_encode([
            'success' => true,
            'queue' => $queue
        ]);
        exit();
    }

    // GET READY QUEUE
    elseif ($action === 'get_ready_queue') {
        $queue = $db->query(
            "SELECT 
                fo.id,
                fo.order_reference,
                fo.items,
                fo.order_type,
                fo.status,
                JSON_LENGTH(fo.items) as item_count,
                u.full_name as customer_name,
                rt.table_number
             FROM food_orders fo
             LEFT JOIN users u ON fo.user_id = u.id
             LEFT JOIN restaurant_reservations rr ON rr.user_id = fo.user_id AND rr.reservation_date = CURDATE()
             LEFT JOIN restaurant_tables rt ON rr.table_number = rt.table_number
             WHERE fo.status IN ('ready', 'served')
             ORDER BY 
                CASE 
                    WHEN fo.status = 'ready' THEN 1
                    WHEN fo.status = 'served' THEN 2
                END,
                fo.updated_at ASC",
            []
        )->find() ?: [];

        echo json_encode([
            'success' => true,
            'queue' => $queue
        ]);
        exit();
    }

    // CREATE NEW ORDER
    elseif ($action === 'create_order') {
        $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : null;
        $customer_name = trim($_POST['customer_name'] ?? '');
        $order_type = $_POST['order_type'] ?? 'dine-in';
        $items = json_decode($_POST['items'] ?? '[]', true);
        $subtotal = floatval($_POST['subtotal'] ?? 0);
        $service_fee = floatval($_POST['service_fee'] ?? 0);
        $total_amount = floatval($_POST['total_amount'] ?? 0);
        $points_used = intval($_POST['points_used'] ?? 0);

        if (empty($items)) {
            throw new Exception('Order must have at least one item');
        }

        if (!$user_id && empty($customer_name)) {
            throw new Exception('Customer information required');
        }

        $db->beginTransaction();

        // Calculate points earned (1 point per ₱100)
        $points_earned = floor($total_amount / 100) * 5;

        // Generate order reference
        $year = date('Y');
        $month = date('m');
        $random = strtoupper(substr(uniqid(), -4));
        $order_ref = "ORD-{$year}{$month}-{$random}";

        // Insert order
        $db->query(
            "INSERT INTO food_orders (
                order_reference, user_id, items, order_type, subtotal,
                service_fee, total_amount, points_used, points_earned,
                status, created_at, updated_at
             ) VALUES (
                :ref, :user_id, :items, :type, :subtotal,
                :service_fee, :total, :points_used, :points_earned,
                'pending', NOW(), NOW()
             )",
            [
                'ref' => $order_ref,
                'user_id' => $user_id,
                'items' => json_encode($items),
                'type' => $order_type,
                'subtotal' => $subtotal,
                'service_fee' => $service_fee,
                'total' => $total_amount,
                'points_used' => $points_used,
                'points_earned' => $points_earned
            ]
        );

        // Deduct points if used
        if ($points_used > 0 && $user_id) {
            $db->query(
                "UPDATE users SET loyalty_points = GREATEST(0, loyalty_points - :points) WHERE id = :user_id",
                [
                    'points' => $points_used,
                    'user_id' => $user_id
                ]
            );
        }

        // Create notification for kitchen
        $db->query(
            "INSERT INTO notifications (user_id, title, message, type, icon, created_at) 
             VALUES (:user_id, 'New Order', :message, 'info', 'fa-utensils', NOW())",
            [
                'user_id' => $_SESSION['user_id'],
                'message' => "New order #{$order_ref} created"
            ]
        );

        $db->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Order created successfully',
            'order_reference' => $order_ref,
            'points_earned' => $points_earned
        ]);
        exit();
    }

    // CANCEL ORDER
    elseif ($action === 'cancel_order') {
        $order_id = $_POST['order_id'] ?? '';
        $reason = trim($_POST['reason'] ?? '');

        if (empty($order_id)) {
            throw new Exception('Order ID required');
        }

        $db->beginTransaction();

        // Get order details
        $order = $db->query(
            "SELECT * FROM food_orders WHERE order_reference = :ref",
            ['ref' => $order_id]
        )->fetch_one();

        if (!$order) {
            throw new Exception('Order not found');
        }

        // Update order status
        $db->query(
            "UPDATE food_orders SET 
                status = 'cancelled',
                updated_at = NOW()
             WHERE order_reference = :ref",
            ['ref' => $order_id]
        );

        // Refund points if they were used
        if ($order['points_used'] > 0 && $order['user_id']) {
            $db->query(
                "UPDATE users SET loyalty_points = loyalty_points + :points WHERE id = :user_id",
                [
                    'points' => $order['points_used'],
                    'user_id' => $order['user_id']
                ]
            );
        }

        // Create notification
        $db->query(
            "INSERT INTO notifications (user_id, title, message, type, icon, created_at) 
             VALUES (:user_id, 'Order Cancelled', :message, 'warning', 'fa-ban', NOW())",
            [
                'user_id' => $_SESSION['user_id'],
                'message' => "Order #{$order_id} cancelled" . ($reason ? ": {$reason}" : "")
            ]
        );

        $db->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Order cancelled successfully'
        ]);
        exit();
    } else {
        throw new Exception('Invalid action');
    }

} catch (Exception $e) {
    if (isset($db)) {
        $db->rollBack();
    }
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
exit();
?>