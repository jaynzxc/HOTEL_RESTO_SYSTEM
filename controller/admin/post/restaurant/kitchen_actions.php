<?php
/**
 * POST Controller - Admin Kitchen Actions
 * Handles updating kitchen order status with inventory integration
 */

// Enable error logging but don't display errors
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

session_start();
require_once __DIR__ . '/../../../../Class/Database.php';

// Set JSON header for AJAX response
header('Content-Type: application/json');

// Function to return JSON error
function sendJsonError($message, $code = 400)
{
    http_response_code($code);
    echo json_encode([
        'success' => false,
        'message' => $message
    ]);
    exit();
}

// Function to return JSON success
function sendJsonSuccess($data)
{
    echo json_encode(array_merge(['success' => true], $data));
    exit();
}

try {
    // Check if user is logged in
    if (!isset($_SESSION['user_id']) || !$_SESSION['logged_in']) {
        sendJsonError('Please login to continue', 401);
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
        sendJsonError('Unauthorized access', 403);
    }

    // Check if it's a POST request
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendJsonError('Invalid request method', 405);
    }

    $action = $_POST['action'] ?? '';

    // Function to check if an item is food-related
    function isFoodItem($itemName)
    {
        $foodKeywords = [
            'rice',
            'chicken',
            'pork',
            'beef',
            'fish',
            'vegetable',
            'fruit',
            'milk',
            'cheese',
            'egg',
            'bread',
            'butter',
            'sugar',
            'salt',
            'pepper',
            'sauce',
            'oil',
            'beverage',
            'drink',
            'juice',
            'coffee',
            'tea',
            'soda'
        ];

        $itemLower = strtolower($itemName);
        foreach ($foodKeywords as $keyword) {
            if (strpos($itemLower, $keyword) !== false) {
                return true;
            }
        }
        return false;
    }

    // Function to check and deduct inventory (only for food items)
    function checkAndDeductInventory($db, $items)
    {
        $insufficientItems = [];
        $updatedItems = [];

        foreach ($items as $item) {
            $itemName = $item['name'] ?? '';
            $quantity = $item['quantity'] ?? 1;

            // Skip non-food items
            if (!isFoodItem($itemName)) {
                continue;
            }

            // Find inventory item by name
            $inventory = $db->query(
                "SELECT id, item_name, stock, reorder_level, unit, category 
                 FROM inventory 
                 WHERE LOWER(item_name) LIKE LOWER(:name)
                 AND category IN ('Food', 'Meat', 'Beverage', 'Vegetable', 'Fruit', 'Dairy', 'Spice', 'Sauce', 'Oil')
                 LIMIT 1",
                ['name' => '%' . $itemName . '%']
            )->fetch_one();

            // If found, check stock
            if ($inventory) {
                if ($inventory['stock'] < $quantity) {
                    $insufficientItems[] = [
                        'item' => $itemName,
                        'required' => $quantity,
                        'available' => $inventory['stock'],
                        'unit' => $inventory['unit']
                    ];
                } else {
                    $updatedItems[] = [
                        'id' => $inventory['id'],
                        'name' => $inventory['item_name'],
                        'quantity' => $quantity,
                        'current_stock' => $inventory['stock'],
                        'new_stock' => $inventory['stock'] - $quantity
                    ];
                }
            }
        }

        return [
            'success' => empty($insufficientItems),
            'insufficient' => $insufficientItems,
            'to_update' => $updatedItems
        ];
    }

    // UPDATE KITCHEN ORDER STATUS
    if ($action === 'update_status') {
        $order_id = $_POST['order_id'] ?? '';
        $new_status = $_POST['status'] ?? '';

        if (empty($order_id)) {
            sendJsonError('Order ID required');
        }

        if (!in_array($new_status, ['new', 'preparing', 'ready', 'urgent', 'completed'])) {
            sendJsonError('Invalid status');
        }

        $db->beginTransaction();

        // Get current order with items
        $order = $db->query(
            "SELECT fo.*, u.full_name as customer_name 
             FROM food_orders fo
             LEFT JOIN users u ON fo.user_id = u.id
             WHERE fo.order_reference = :ref",
            ['ref' => $order_id]
        )->fetch_one();

        if (!$order) {
            throw new Exception('Order not found');
        }

        $old_status = $order['status'];
        $items = json_decode($order['items'], true);

        // If completing the order, deduct inventory
        if ($new_status === 'completed' && $old_status !== 'completed') {
            $inventoryCheck = checkAndDeductInventory($db, $items);

            if (!$inventoryCheck['success']) {
                $db->rollBack();
                $errorMsg = "Cannot complete order. Insufficient stock:\n";
                foreach ($inventoryCheck['insufficient'] as $item) {
                    $errorMsg .= "\n- {$item['item']}: Need {$item['required']} {$item['unit']}, Available: {$item['available']}";
                }

                sendJsonError($errorMsg);
            }

            // Update inventory
            foreach ($inventoryCheck['to_update'] as $item) {
                $db->query(
                    "UPDATE inventory SET 
                        stock = :new_stock,
                        updated_at = NOW()
                     WHERE id = :id",
                    [
                        'new_stock' => $item['new_stock'],
                        'id' => $item['id']
                    ]
                );

                // Log stock movement
                $db->query(
                    "INSERT INTO stock_movements 
                        (inventory_id, type, quantity, previous_stock, new_stock, reason, reference_type, reference_id, created_by, created_at) 
                     VALUES 
                        (:id, 'out', :quantity, :prev, :new, 'Kitchen order completed', 'kitchen_order', :order_ref, :user_id, NOW())",
                    [
                        'id' => $item['id'],
                        'quantity' => $item['quantity'],
                        'prev' => $item['current_stock'],
                        'new' => $item['new_stock'],
                        'order_ref' => $order_id,
                        'user_id' => $_SESSION['user_id']
                    ]
                );
            }
        }

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

        $db->commit();

        sendJsonSuccess([
            'message' => 'Order status updated successfully',
            'old_status' => $old_status,
            'new_status' => $new_status,
            'inventory_updated' => ($new_status === 'completed')
        ]);
    }

    // GET ORDER DETAILS
    elseif ($action === 'get_order_details') {
        $order_id = $_POST['order_id'] ?? '';

        if (empty($order_id)) {
            sendJsonError('Order ID required');
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
            sendJsonError('Order not found');
        }

        // Parse items JSON
        $order['items'] = json_decode($order['items'], true);

        sendJsonSuccess(['order' => $order]);
    }

    // MARK AS URGENT
    elseif ($action === 'mark_urgent') {
        $order_id = $_POST['order_id'] ?? '';

        if (empty($order_id)) {
            sendJsonError('Order ID required');
        }

        $db->query(
            "UPDATE food_orders SET 
                status = 'urgent',
                updated_at = NOW()
             WHERE order_reference = :ref",
            ['ref' => $order_id]
        );

        sendJsonSuccess(['message' => 'Order marked as urgent']);
    }

    // UPDATE KITCHEN STOCK
    elseif ($action === 'update_kitchen_stock') {
        $item_id = intval($_POST['item_id'] ?? 0);
        $adjustment = intval($_POST['adjustment'] ?? 0);
        $reason = trim($_POST['reason'] ?? 'Kitchen stock adjustment');

        if (!$item_id) {
            sendJsonError('Item ID required');
        }

        if ($adjustment == 0) {
            sendJsonError('Adjustment amount cannot be zero');
        }

        // Get current stock
        $item = $db->query(
            "SELECT stock, item_name, unit, reorder_level FROM inventory WHERE id = :id",
            ['id' => $item_id]
        )->fetch_one();

        if (!$item) {
            sendJsonError('Item not found');
        }

        $new_stock = $item['stock'] + $adjustment;
        if ($new_stock < 0) {
            sendJsonError('Stock cannot be negative');
        }

        $db->beginTransaction();

        // Update stock
        $db->query(
            "UPDATE inventory SET 
                stock = :stock,
                updated_at = NOW()
             WHERE id = :id",
            ['id' => $item_id, 'stock' => $new_stock]
        );

        // Log movement
        $type = $adjustment > 0 ? 'in' : 'out';
        $db->query(
            "INSERT INTO stock_movements 
                (inventory_id, type, quantity, previous_stock, new_stock, reason, reference_type, created_by, created_at) 
             VALUES 
                (:id, :type, :quantity, :prev, :new, :reason, 'kitchen_adjustment', :user_id, NOW())",
            [
                'id' => $item_id,
                'type' => $type,
                'quantity' => abs($adjustment),
                'prev' => $item['stock'],
                'new' => $new_stock,
                'reason' => $reason,
                'user_id' => $_SESSION['user_id']
            ]
        );

        $db->commit();

        sendJsonSuccess([
            'message' => 'Stock updated successfully',
            'new_stock' => $new_stock
        ]);
    }

    // ADD FOOD ITEM
    elseif ($action === 'add_food_item') {
        $item_name = trim($_POST['item_name'] ?? '');
        $category = $_POST['category'] ?? 'Food';
        $stock = intval($_POST['stock'] ?? 0);
        $reorder_level = intval($_POST['reorder_level'] ?? 10);
        $unit = $_POST['unit'] ?? 'kg';

        if (empty($item_name)) {
            sendJsonError('Item name required');
        }

        // Check if item already exists
        $existing = $db->query(
            "SELECT id FROM inventory WHERE item_name = :name",
            ['name' => $item_name]
        )->fetch_one();

        if ($existing) {
            sendJsonError('Item already exists');
        }

        $db->beginTransaction();

        $db->query(
            "INSERT INTO inventory (item_name, category, stock, reorder_level, unit, created_at, updated_at) 
             VALUES (:name, :category, :stock, :reorder_level, :unit, NOW(), NOW())",
            [
                'name' => $item_name,
                'category' => $category,
                'stock' => $stock,
                'reorder_level' => $reorder_level,
                'unit' => $unit
            ]
        );

        $new_id = $db->lastInsertId();

        if ($stock > 0) {
            $db->query(
                "INSERT INTO stock_movements (inventory_id, type, quantity, previous_stock, new_stock, reason, created_by, created_at) 
                 VALUES (:id, 'in', :quantity, 0, :quantity, 'Initial kitchen stock', :user_id, NOW())",
                [
                    'id' => $new_id,
                    'quantity' => $stock,
                    'user_id' => $_SESSION['user_id']
                ]
            );
        }

        $db->commit();

        sendJsonSuccess(['message' => 'Food item added successfully']);
    }

    // GET LOW STOCK ITEMS (for kitchen)
    elseif ($action === 'get_low_stock_items') {
        $lowStock = $db->query(
            "SELECT id, item_name, stock, reorder_level, unit, category 
             FROM inventory 
             WHERE category IN ('Food', 'Meat', 'Beverage', 'Vegetable', 'Fruit', 'Dairy', 'Spice', 'Sauce', 'Oil')
             AND stock <= reorder_level 
             ORDER BY (stock / reorder_level) ASC 
             LIMIT 10",
            []
        )->find() ?: [];

        sendJsonSuccess(['items' => $lowStock]);
    }

    // DELETE FOOD ITEM
    elseif ($action === 'delete_food_item') {
        $item_id = intval($_POST['item_id'] ?? 0);

        if (!$item_id) {
            sendJsonError('Item ID required');
        }

        $db->query("DELETE FROM inventory WHERE id = :id", ['id' => $item_id]);

        sendJsonSuccess(['message' => 'Item deleted successfully']);
    }

    // GET FOOD ITEM DETAILS
    elseif ($action === 'get_food_item') {
        $item_id = intval($_POST['item_id'] ?? 0);

        if (!$item_id) {
            sendJsonError('Item ID required');
        }

        $item = $db->query(
            "SELECT * FROM inventory WHERE id = :id",
            ['id' => $item_id]
        )->fetch_one();

        if (!$item) {
            sendJsonError('Item not found');
        }

        sendJsonSuccess(['item' => $item]);
    }

    // CHECK INVENTORY BEFORE PREPARING
    elseif ($action === 'check_inventory') {
        $items = json_decode($_POST['items'] ?? '[]', true);

        if (empty($items)) {
            sendJsonError('No items to check');
        }

        $inventoryCheck = checkAndDeductInventory($db, $items);

        sendJsonSuccess([
            'has_sufficient' => $inventoryCheck['success'],
            'insufficient_items' => $inventoryCheck['insufficient'],
            'message' => $inventoryCheck['success'] ? 'All food items have sufficient stock' : 'Some food items have insufficient stock'
        ]);
    } else {
        sendJsonError('Invalid action: ' . $action);
    }

} catch (Exception $e) {
    // Log the error
    error_log('Kitchen POST Controller Error: ' . $e->getMessage());
    error_log('POST Data: ' . print_r($_POST, true));

    // Return JSON error response
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
    exit();
}
?>