<?php
/**
 * POST Controller - Place Food Order
 * Handles placing food orders and saving to database
 */

session_start();
require_once __DIR__ . '/../../../Class/Database.php';

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
    $action = $_POST['action'] ?? 'place_order';

    // Handle cancel order action (DELETE)
    if ($action === 'cancel_order') {
        $order_id = intval($_POST['order_id'] ?? 0);

        if (!$order_id) {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid order ID'
            ]);
            exit();
        }

        // Start transaction
        $db->beginTransaction();

        // Get order details first (including points info)
        $order = $db->query(
            "SELECT id, points_used, points_earned, status 
             FROM food_orders 
             WHERE id = :id AND user_id = :user_id FOR UPDATE",
            [
                'id' => $order_id,
                'user_id' => $_SESSION['user_id']
            ]
        )->fetch_one();

        if (!$order) {
            throw new Exception('Order not found');
        }

        if (!in_array($order['status'], ['pending', 'preparing'])) {
            throw new Exception('Order cannot be cancelled at this stage');
        }

        // Refund points if they were used
        if ($order['points_used'] > 0) {
            $db->query(
                "UPDATE users SET loyalty_points = loyalty_points + :points 
                 WHERE id = :user_id",
                [
                    'points' => $order['points_used'],
                    'user_id' => $_SESSION['user_id']
                ]
            );
        }

        // Remove points earned if they were awarded
        if ($order['points_earned'] > 0) {
            $db->query(
                "UPDATE users SET loyalty_points = loyalty_points - :points 
                 WHERE id = :user_id",
                [
                    'points' => $order['points_earned'],
                    'user_id' => $_SESSION['user_id']
                ]
            );
        }

        // DELETE the order from database (completely remove)
        $db->query(
            "DELETE FROM food_orders WHERE id = :id AND user_id = :user_id",
            [
                'id' => $order_id,
                'user_id' => $_SESSION['user_id']
            ]
        );

        // Create notification about cancellation
        $db->query(
            "INSERT INTO notifications (user_id, title, message, type, icon, link, created_at) 
             VALUES (:user_id, 'Order Cancelled', :message, 'warning', 'fa-bag-shopping', :link, NOW())",
            [
                'user_id' => $_SESSION['user_id'],
                'message' => "Your order #{$order_id} has been cancelled and removed." .
                    ($order['points_used'] > 0 ? " {$order['points_used']} points have been refunded." : ""),
                'link' => '/src/customer_portal/order_food.php'
            ]
        );

        $db->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Order cancelled and removed successfully' .
                ($order['points_used'] > 0 ? " - {$order['points_used']} points refunded." : ".")
        ]);
        exit();
    }

    // Handle place order action (INSERT)
    // Get order data
    $items = json_decode($_POST['items'] ?? '[]', true);
    $order_type = $_POST['order_type'] ?? 'dine-in';
    $subtotal = floatval($_POST['subtotal'] ?? 0);
    $service_fee = floatval($_POST['service_fee'] ?? 0);
    $total = floatval($_POST['total'] ?? 0);
    $points_used = intval($_POST['points_used'] ?? 0);

    // Validation
    if (empty($items)) {
        echo json_encode([
            'success' => false,
            'message' => 'Your cart is empty'
        ]);
        exit();
    }

    if ($total <= 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid order total'
        ]);
        exit();
    }

    // Check if user has enough points if they're using them
    if ($points_used > 0) {
        $userPoints = $db->query(
            "SELECT loyalty_points FROM users WHERE id = :user_id",
            ['user_id' => $_SESSION['user_id']]
        )->fetch_one();

        if ($userPoints['loyalty_points'] < $points_used) {
            echo json_encode([
                'success' => false,
                'message' => 'Insufficient loyalty points'
            ]);
            exit();
        }
    }

    // Start transaction
    $db->beginTransaction();

    // Generate unique order reference
    $year = date('Y');
    $month = date('m');
    $random = strtoupper(substr(uniqid(), -6));
    $order_reference = "FOOD-{$year}{$month}-{$random}";

    // Calculate points earned (5 points per ₱100 of subtotal)
    $points_earned = floor($subtotal / 100) * 5;

    // Get promo code data if applied
    $promoCodeId = null;
    $promoCode = null;
    $discountApplied = 0;

    if (!empty($_POST['promo_code_id'])) {
        $promoCodeId = intval($_POST['promo_code_id']);
        $discountApplied = floatval($_POST['discount_applied'] ?? 0);

        // Get promo code details for validation
        $promoData = $db->query(
            "SELECT * FROM promo_codes WHERE id = :id",
            ['id' => $promoCodeId]
        )->fetch_one();

        if ($promoData) {
            $promoCode = $promoData['code'];

            // Update usage count
            $db->query(
                "UPDATE promo_codes SET used_count = used_count + 1 WHERE id = :id",
                ['id' => $promoCodeId]
            );
        }
    }

    // Insert food order with promo code fields
    $db->query(
        "INSERT INTO food_orders (
        order_reference, user_id, items, order_type, subtotal,
        service_fee, total_amount, points_used, points_earned, 
        promo_code_id, promo_code, discount_applied, status, created_at
    ) VALUES (
        :order_reference, :user_id, :items, :order_type, :subtotal,
        :service_fee, :total, :points_used, :points_earned,
        :promo_code_id, :promo_code, :discount_applied, 'pending', NOW()
    )",
        [
            'order_reference' => $order_reference,
            'user_id' => $_SESSION['user_id'],
            'items' => json_encode($items),
            'order_type' => $order_type,
            'subtotal' => $subtotal,
            'service_fee' => $service_fee,
            'total' => $total,
            'points_used' => $points_used,
            'points_earned' => $points_earned,
            'promo_code_id' => $promoCodeId,
            'promo_code' => $promoCode,
            'discount_applied' => $discountApplied
        ]
    );

    $order_id = $db->lastInsertId();

    // Deduct points if used
    if ($points_used > 0) {
        $db->query(
            "UPDATE users SET loyalty_points = loyalty_points - :points WHERE id = :user_id",
            [
                'points' => $points_used,
                'user_id' => $_SESSION['user_id']
            ]
        );
    }

    // Award points for the order
    if ($points_earned > 0) {
        $db->query(
            "UPDATE users SET loyalty_points = loyalty_points + :points WHERE id = :user_id",
            [
                'points' => $points_earned,
                'user_id' => $_SESSION['user_id']
            ]
        );
    }

    // Create notification
    $db->query(
        "INSERT INTO notifications (user_id, title, message, type, icon, link, created_at) 
         VALUES (:user_id, 'Order Placed', :message, 'success', 'fa-bag-shopping', :link, NOW())",
        [
            'user_id' => $_SESSION['user_id'],
            'message' => "Your order #{$order_reference} has been placed. Total: ₱" . number_format($total, 2),
            'link' => '/src/customer_portal/order_food.php'
        ]
    );

    // Commit transaction
    $db->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Order placed successfully!',
        'order' => [
            'id' => $order_id,
            'reference' => $order_reference,
            'total' => $total,
            'points_earned' => $points_earned,
            'points_used' => $points_used
        ]
    ]);

} catch (Exception $e) {
    $db->rollBack();
    error_log("Order error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage() ?: 'An error occurred. Please try again.'
    ]);
}
exit();
?>