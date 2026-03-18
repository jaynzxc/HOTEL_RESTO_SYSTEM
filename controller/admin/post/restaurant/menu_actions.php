<?php
/**
 * POST Controller - Admin Menu Actions
 * Handles creating, updating, deleting menu items and categories
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

    // ADD NEW MENU ITEM
    if ($action === 'add_item') {
        $name = trim($_POST['name'] ?? '');
        $category = $_POST['category'] ?? '';
        $price = floatval($_POST['price'] ?? 0);
        $cost = floatval($_POST['cost'] ?? 0);
        $stock = intval($_POST['stock'] ?? 0);
        $status = $_POST['status'] ?? 'available';
        $description = trim($_POST['description'] ?? '');
        $preparation_time = intval($_POST['preparation_time'] ?? 15);

        // Validation
        if (empty($name)) {
            throw new Exception('Item name is required');
        }
        if (empty($category)) {
            throw new Exception('Category is required');
        }
        if ($price <= 0) {
            throw new Exception('Price must be greater than 0');
        }
        if ($cost < 0) {
            throw new Exception('Cost cannot be negative');
        }
        if ($stock < 0) {
            throw new Exception('Stock cannot be negative');
        }

        $is_available = ($status !== 'disabled' && $status !== 'out_of_stock') ? 1 : 0;

        $db->beginTransaction();

        // Insert menu item - FIXED: Removed item_code from INSERT
        $db->query(
            "INSERT INTO menu_items (
                name, category, price, cost, stock, 
                status, is_available, description, preparation_time,
                created_at, updated_at
             ) VALUES (
                :name, :category, :price, :cost, :stock,
                :status, :is_available, :description, :prep_time,
                NOW(), NOW()
             )",
            [
                'name' => $name,
                'category' => $category,
                'price' => $price,
                'cost' => $cost,
                'stock' => $stock,
                'status' => $status,
                'is_available' => $is_available,
                'description' => $description,
                'prep_time' => $preparation_time
            ]
        );

        $item_id = $db->lastInsertId();

        // Generate item code based on ID
        $prefix = '';
        switch (strtolower($category)) {
            case 'mains':
                $prefix = 'M';
                break;
            case 'appetizers':
                $prefix = 'A';
                break;
            case 'desserts':
                $prefix = 'D';
                break;
            case 'beverages':
                $prefix = 'B';
                break;
            default:
                $prefix = 'X';
        }
        $item_code = $prefix . str_pad($item_id, 3, '0', STR_PAD_LEFT);

        // Update with item code
        $db->query(
            "UPDATE menu_items SET item_code = :code WHERE id = :id",
            [
                'code' => $item_code,
                'id' => $item_id
            ]
        );

        // Create notification for low stock if applicable
        if ($stock <= 10) {
            $db->query(
                "INSERT INTO notifications (user_id, title, message, type, icon, created_at) 
                 VALUES (:user_id, 'Low Stock Alert', :message, 'warning', 'fa-box', NOW())",
                [
                    'user_id' => $_SESSION['user_id'],
                    'message' => "New item {$name} has low stock ({$stock} units)"
                ]
            );
        }

        $db->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Menu item added successfully',
            'item_id' => $item_id,
            'item_code' => $item_code
        ]);
        exit();
    }

    // UPDATE MENU ITEM
    elseif ($action === 'update_item') {
        $item_id = intval($_POST['item_id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $category = $_POST['category'] ?? '';
        $price = floatval($_POST['price'] ?? 0);
        $cost = floatval($_POST['cost'] ?? 0);
        $stock = intval($_POST['stock'] ?? 0);
        $status = $_POST['status'] ?? 'available';
        $description = trim($_POST['description'] ?? '');
        $preparation_time = intval($_POST['preparation_time'] ?? 15);

        if (!$item_id) {
            throw new Exception('Item ID required');
        }

        $is_available = ($status !== 'disabled' && $status !== 'out_of_stock') ? 1 : 0;

        // Get old stock for notification
        $oldItem = $db->query(
            "SELECT stock, name FROM menu_items WHERE id = :id",
            ['id' => $item_id]
        )->fetch_one();

        $db->query(
            "UPDATE menu_items SET 
                name = :name,
                category = :category,
                price = :price,
                cost = :cost,
                stock = :stock,
                status = :status,
                is_available = :is_available,
                description = :description,
                preparation_time = :prep_time,
                updated_at = NOW()
             WHERE id = :id",
            [
                'id' => $item_id,
                'name' => $name,
                'category' => $category,
                'price' => $price,
                'cost' => $cost,
                'stock' => $stock,
                'status' => $status,
                'is_available' => $is_available,
                'description' => $description,
                'prep_time' => $preparation_time
            ]
        );

        // Create notification if stock became low
        if ($oldItem && $stock <= 10 && $oldItem['stock'] > 10) {
            $db->query(
                "INSERT INTO notifications (user_id, title, message, type, icon, created_at) 
                 VALUES (:user_id, 'Low Stock Alert', :message, 'warning', 'fa-box', NOW())",
                [
                    'user_id' => $_SESSION['user_id'],
                    'message' => "{$oldItem['name']} stock is now low ({$stock} units)"
                ]
            );
        }

        echo json_encode([
            'success' => true,
            'message' => 'Menu item updated successfully'
        ]);
        exit();
    }

    // TOGGLE ITEM STATUS
    elseif ($action === 'toggle_status') {
        $item_id = intval($_POST['item_id'] ?? 0);

        if (!$item_id) {
            throw new Exception('Item ID required');
        }

        $item = $db->query(
            "SELECT is_available, status, name FROM menu_items WHERE id = :id",
            ['id' => $item_id]
        )->fetch_one();

        if (!$item) {
            throw new Exception('Item not found');
        }

        $new_status = $item['is_available'] ? 0 : 1;
        $new_status_text = $new_status ? 'available' : 'disabled';

        $db->query(
            "UPDATE menu_items SET 
                is_available = :available,
                status = :status,
                updated_at = NOW()
             WHERE id = :id",
            [
                'id' => $item_id,
                'available' => $new_status,
                'status' => $new_status_text
            ]
        );

        echo json_encode([
            'success' => true,
            'message' => 'Item status toggled successfully',
            'new_status' => $new_status_text
        ]);
        exit();
    }

    // RESTOCK ITEM
    elseif ($action === 'restock_item') {
        $item_id = intval($_POST['item_id'] ?? 0);
        $quantity = intval($_POST['quantity'] ?? 50);

        if (!$item_id) {
            throw new Exception('Item ID required');
        }

        $item = $db->query(
            "SELECT name, stock FROM menu_items WHERE id = :id",
            ['id' => $item_id]
        )->fetch_one();

        if (!$item) {
            throw new Exception('Item not found');
        }

        $new_stock = $item['stock'] + $quantity;

        $db->query(
            "UPDATE menu_items SET 
                stock = :stock,
                status = 'available',
                is_available = 1,
                updated_at = NOW()
             WHERE id = :id",
            [
                'id' => $item_id,
                'stock' => $new_stock
            ]
        );

        echo json_encode([
            'success' => true,
            'message' => "Item restocked successfully. New stock: {$new_stock}",
            'new_stock' => $new_stock
        ]);
        exit();
    }

    // BULK UPDATE ITEMS
    elseif ($action === 'bulk_update') {
        $item_ids = json_decode($_POST['item_ids'] ?? '[]', true);
        $status = $_POST['status'] ?? '';
        $category = $_POST['category'] ?? '';
        $price = isset($_POST['price']) ? floatval($_POST['price']) : null;

        if (empty($item_ids)) {
            throw new Exception('No items selected');
        }

        $placeholders = implode(',', array_fill(0, count($item_ids), '?'));
        $updates = [];
        $params = [];

        if (!empty($status)) {
            $is_available = ($status !== 'disabled' && $status !== 'out_of_stock') ? 1 : 0;
            $updates[] = "status = ?, is_available = ?";
            $params[] = $status;
            $params[] = $is_available;
        }

        if (!empty($category)) {
            $updates[] = "category = ?";
            $params[] = $category;
        }

        if ($price !== null && $price > 0) {
            $updates[] = "price = ?";
            $params[] = $price;
        }

        if (empty($updates)) {
            throw new Exception('No updates specified');
        }

        $updates[] = "updated_at = NOW()";
        $query = "UPDATE menu_items SET " . implode(', ', $updates) . " WHERE id IN ($placeholders)";

        // Add item IDs to params
        foreach ($item_ids as $id) {
            $params[] = $id;
        }

        $db->query($query, $params);

        echo json_encode([
            'success' => true,
            'message' => count($item_ids) . ' items updated successfully'
        ]);
        exit();
    }

    // DELETE ITEM
    elseif ($action === 'delete_item') {
        $item_id = intval($_POST['item_id'] ?? 0);

        if (!$item_id) {
            throw new Exception('Item ID required');
        }

        // Check if item is used in any orders
        $orderCount = $db->query(
            "SELECT COUNT(*) as count FROM food_orders WHERE JSON_SEARCH(items, 'all', :id) IS NOT NULL",
            ['id' => $item_id]
        )->fetch_one();

        if ($orderCount && $orderCount['count'] > 0) {
            // Soft delete - just mark as disabled
            $db->query(
                "UPDATE menu_items SET is_available = 0, status = 'disabled' WHERE id = :id",
                ['id' => $item_id]
            );
            $message = 'Item disabled (used in existing orders)';
        } else {
            // Hard delete
            $db->query("DELETE FROM menu_items WHERE id = :id", ['id' => $item_id]);
            $message = 'Item deleted successfully';
        }

        echo json_encode([
            'success' => true,
            'message' => $message
        ]);
        exit();
    }

    // ADD CATEGORY
    elseif ($action === 'add_category') {
        $category_name = trim($_POST['category_name'] ?? '');

        if (empty($category_name)) {
            throw new Exception('Category name is required');
        }

        // Since we don't have a separate categories table, we'll just return success
        echo json_encode([
            'success' => true,
            'message' => 'Category added successfully',
            'category' => $category_name
        ]);
        exit();
    }

    // GET ITEM DETAILS
    elseif ($action === 'get_item_details') {
        $item_id = intval($_POST['item_id'] ?? 0);

        if (!$item_id) {
            throw new Exception('Item ID required');
        }

        $item = $db->query(
            "SELECT * FROM menu_items WHERE id = :id",
            ['id' => $item_id]
        )->fetch_one();

        if (!$item) {
            throw new Exception('Item not found');
        }

        // Generate item code if not present
        if (empty($item['item_code'])) {
            $prefix = '';
            switch (strtolower($item['category'])) {
                case 'mains':
                    $prefix = 'M';
                    break;
                case 'appetizers':
                    $prefix = 'A';
                    break;
                case 'desserts':
                    $prefix = 'D';
                    break;
                case 'beverages':
                    $prefix = 'B';
                    break;
                default:
                    $prefix = 'X';
            }
            $item['item_code'] = $prefix . str_pad($item['id'], 3, '0', STR_PAD_LEFT);
        }

        echo json_encode([
            'success' => true,
            'item' => $item
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