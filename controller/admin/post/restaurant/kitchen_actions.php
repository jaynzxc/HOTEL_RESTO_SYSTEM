<?php
/**
 * POST Controller - Admin Kitchen Actions
 * Handles updating kitchen order status, marking as urgent, etc.
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

    // UPDATE KITCHEN ORDER STATUS
    if ($action === 'update_status') {
        $order_id = $_POST['order_id'] ?? '';
        $new_status = $_POST['status'] ?? '';

        if (empty($order_id)) {
            throw new Exception('Order ID required');
        }

        if (!in_array($new_status, ['new', 'preparing', 'ready', 'urgent', 'completed'])) {
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

        // Create notification for POS
        $message = "Order #{$order_id} status changed from {$old_status} to {$new_status}";
        if ($new_status === 'urgent') {
            $message = "⚠️ URGENT: Order #{$order_id} marked as urgent!";
        } elseif ($new_status === 'ready') {
            $message = "✅ Order #{$order_id} is ready for serving!";
        }

        $db->query(
            "INSERT INTO notifications (user_id, title, message, type, icon, created_at) 
             VALUES (:user_id, 'Kitchen Update', :message, 'info', 'fa-utensils', NOW())",
            [
                'user_id' => $_SESSION['user_id'],
                'message' => $message
            ]
        );

        $db->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Order status updated successfully',
            'old_status' => $old_status,
            'new_status' => $new_status
        ]);
        exit();
    }

    // UPDATE ORDER DETAILS
    elseif ($action === 'update_order_details') {
        $order_id = $_POST['order_id'] ?? '';
        $customer_name = trim($_POST['customer_name'] ?? '');
        $order_type = $_POST['order_type'] ?? '';
        $items = $_POST['items'] ?? '';
        $status = $_POST['status'] ?? '';
        $special_instructions = trim($_POST['special_instructions'] ?? '');

        if (empty($order_id)) {
            throw new Exception('Order ID required');
        }

        // Update order - note: items is stored as JSON, so we need to handle carefully
        // For simplicity, we're just updating non-JSON fields
        $db->query(
            "UPDATE food_orders SET 
                status = :status,
                updated_at = NOW()
             WHERE order_reference = :ref",
            [
                'status' => $status,
                'ref' => $order_id
            ]
        );

        echo json_encode([
            'success' => true,
            'message' => 'Order updated successfully'
        ]);
        exit();
    }

    // MARK AS URGENT
    elseif ($action === 'mark_urgent') {
        $order_id = $_POST['order_id'] ?? '';

        if (empty($order_id)) {
            throw new Exception('Order ID required');
        }

        $db->query(
            "UPDATE food_orders SET 
                status = 'urgent',
                updated_at = NOW()
             WHERE order_reference = :ref",
            ['ref' => $order_id]
        );

        // Create urgent notification
        $db->query(
            "INSERT INTO notifications (user_id, title, message, type, icon, created_at) 
             VALUES (:user_id, '⚠️ URGENT ORDER', :message, 'warning', 'fa-exclamation-triangle', NOW())",
            [
                'user_id' => $_SESSION['user_id'],
                'message' => "Order #{$order_id} marked as URGENT!"
            ]
        );

        echo json_encode([
            'success' => true,
            'message' => 'Order marked as urgent'
        ]);
        exit();
    }

    // GET ORDER DETAILS FOR EDITING
    elseif ($action === 'get_order_details') {
        $order_id = $_POST['order_id'] ?? '';

        if (empty($order_id)) {
            throw new Exception('Order ID required');
        }

        $order = $db->query(
            "SELECT 
                fo.*,
                u.full_name as customer_name,
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