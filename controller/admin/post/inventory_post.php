<?php
/**
 * POST Controller - Admin Inventory & Stock Actions
 */

// Enable error logging but don't display errors
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

session_start();
require_once __DIR__ . '/../../../Class/Database.php';

// Set JSON header for AJAX response
header('Content-Type: application/json');

try {
    // Check if user is logged in
    if (!isset($_SESSION['user_id']) || !$_SESSION['logged_in']) {
        throw new Exception('Please login to continue');
    }

    $config = require __DIR__ . '/../../../config/config.php';
    $db = new Database($config['database']);

    // Get user role
    $user = $db->query(
        "SELECT role FROM users WHERE id = :id",
        ['id' => $_SESSION['user_id']]
    )->fetch_one();

    // Check if user has admin role
    if (!$user || $user['role'] !== 'admin') {
        throw new Exception('Unauthorized access');
    }

    // Check if it's a POST request
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    $action = $_POST['action'] ?? '';

    // ADD NEW ITEM
    if ($action === 'add_item') {
        $item_name = trim($_POST['item_name'] ?? '');
        $category = $_POST['category'] ?? '';
        $stock = intval($_POST['stock'] ?? 0);
        $reorder_level = intval($_POST['reorder_level'] ?? 10);
        $unit = $_POST['unit'] ?? 'pcs';

        if (empty($item_name) || empty($category)) {
            throw new Exception('Item name and category are required');
        }

        // Check if item already exists
        $existing = $db->query(
            "SELECT id FROM inventory WHERE item_name = :name",
            ['name' => $item_name]
        )->fetch_one();

        if ($existing) {
            throw new Exception('Item already exists');
        }

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

        // Log movement
        if ($stock > 0) {
            $new_id = $db->lastInsertId();
            $db->query(
                "INSERT INTO stock_movements (inventory_id, type, quantity, previous_stock, new_stock, reason, created_by, created_at) 
                 VALUES (:id, 'in', :quantity, 0, :quantity, 'Initial stock', :user_id, NOW())",
                [
                    'id' => $new_id,
                    'quantity' => $stock,
                    'user_id' => $_SESSION['user_id']
                ]
            );
        }

        echo json_encode(['success' => true, 'message' => 'Item added successfully']);
        exit();
    }

    // UPDATE ITEM
    elseif ($action === 'update_item') {
        $item_id = intval($_POST['item_id'] ?? 0);
        $item_name = trim($_POST['item_name'] ?? '');
        $category = $_POST['category'] ?? '';
        $reorder_level = intval($_POST['reorder_level'] ?? 10);
        $unit = $_POST['unit'] ?? 'pcs';

        if (!$item_id || empty($item_name)) {
            throw new Exception('Item ID and name required');
        }

        $db->query(
            "UPDATE inventory SET 
                item_name = :name,
                category = :category,
                reorder_level = :reorder_level,
                unit = :unit,
                updated_at = NOW()
             WHERE id = :id",
            [
                'id' => $item_id,
                'name' => $item_name,
                'category' => $category,
                'reorder_level' => $reorder_level,
                'unit' => $unit
            ]
        );

        echo json_encode(['success' => true, 'message' => 'Item updated successfully']);
        exit();
    }

    // ADJUST STOCK
    elseif ($action === 'adjust_stock') {
        $item_id = intval($_POST['item_id'] ?? 0);
        $adjustment = intval($_POST['adjustment'] ?? 0);
        $reason = trim($_POST['reason'] ?? 'Stock adjustment');

        error_log("Adjusting stock - Item ID: $item_id, Adjustment: $adjustment, Reason: $reason");

        if (!$item_id) {
            throw new Exception('Item ID required');
        }

        if ($adjustment == 0) {
            throw new Exception('Adjustment amount cannot be zero');
        }

        // Get current stock
        $item = $db->query(
            "SELECT stock, item_name, unit, reorder_level FROM inventory WHERE id = :id",
            ['id' => $item_id]
        )->fetch_one();

        if (!$item) {
            throw new Exception('Item not found');
        }

        $new_stock = $item['stock'] + $adjustment;
        if ($new_stock < 0) {
            throw new Exception('Stock cannot be negative');
        }

        $db->beginTransaction();

        // Update stock
        $db->query(
            "UPDATE inventory SET stock = :stock, updated_at = NOW() WHERE id = :id",
            ['id' => $item_id, 'stock' => $new_stock]
        );

        // Log movement
        $type = $adjustment > 0 ? 'in' : 'out';
        $db->query(
            "INSERT INTO stock_movements (inventory_id, type, quantity, previous_stock, new_stock, reason, created_by, created_at) 
             VALUES (:id, :type, :quantity, :prev, :new, :reason, :user_id, NOW())",
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

        // Create notification for low stock
        if ($new_stock <= $item['reorder_level'] && $new_stock > 0) {
            // Check if admin_notifications table exists
            try {
                $db->query(
                    "INSERT INTO admin_notifications (admin_id, title, message, type, icon, created_at) 
                     VALUES (:admin_id, 'Low Stock Alert', :message, 'warning', 'fa-box', NOW())",
                    [
                        'admin_id' => $_SESSION['user_id'],
                        'message' => "{$item['item_name']} stock is low ({$new_stock} {$item['unit']})"
                    ]
                );
            } catch (Exception $e) {
                // Table might not exist, ignore
                error_log("Could not create notification: " . $e->getMessage());
            }
        }

        $db->commit();

        echo json_encode(['success' => true, 'message' => 'Stock adjusted successfully']);
        exit();
    }

    // DELETE ITEM
    elseif ($action === 'delete_item') {
        $item_id = intval($_POST['item_id'] ?? 0);

        if (!$item_id) {
            throw new Exception('Item ID required');
        }

        $db->query("DELETE FROM inventory WHERE id = :id", ['id' => $item_id]);

        echo json_encode(['success' => true, 'message' => 'Item deleted successfully']);
        exit();
    }

    // GET ITEM DETAILS
    elseif ($action === 'get_item') {
        $item_id = intval($_POST['item_id'] ?? 0);

        if (!$item_id) {
            throw new Exception('Item ID required');
        }

        $item = $db->query(
            "SELECT * FROM inventory WHERE id = :id",
            ['id' => $item_id]
        )->fetch_one();

        if (!$item) {
            throw new Exception('Item not found');
        }

        echo json_encode(['success' => true, 'item' => $item]);
        exit();
    }

    // GET LOW STOCK ITEMS
    elseif ($action === 'get_low_stock') {
        $items = $db->query(
            "SELECT * FROM inventory 
             WHERE stock <= reorder_level AND stock > 0
             ORDER BY (stock / reorder_level) ASC",
            []
        )->find() ?: [];

        echo json_encode(['success' => true, 'items' => $items]);
        exit();
    }

    // GET SUPPLIERS
    elseif ($action === 'get_suppliers') {
        $suppliers = $db->query(
            "SELECT * FROM suppliers ORDER BY name ASC",
            []
        )->find() ?: [];

        echo json_encode(['success' => true, 'suppliers' => $suppliers]);
        exit();
    }

    // ADD SUPPLIER
    elseif ($action === 'add_supplier') {
        $name = trim($_POST['name'] ?? '');
        $contact = trim($_POST['contact'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $address = trim($_POST['address'] ?? '');

        if (empty($name)) {
            throw new Exception('Supplier name required');
        }

        $db->query(
            "INSERT INTO suppliers (name, contact_person, phone, email, address, created_at) 
             VALUES (:name, :contact, :phone, :email, :address, NOW())",
            [
                'name' => $name,
                'contact' => $contact,
                'phone' => $phone,
                'email' => $email,
                'address' => $address
            ]
        );

        echo json_encode(['success' => true, 'message' => 'Supplier added successfully']);
        exit();
    }

    // CREATE PURCHASE ORDER
    elseif ($action === 'create_po') {
        $supplier_id = intval($_POST['supplier_id'] ?? 0);
        $items = json_decode($_POST['items'] ?? '[]', true);
        $notes = trim($_POST['notes'] ?? '');
        $expected_delivery = $_POST['expected_delivery'] ?? date('Y-m-d', strtotime('+7 days'));

        if (!$supplier_id || empty($items)) {
            throw new Exception('Supplier and items required');
        }

        // Generate PO number
        $po_number = 'PO-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));

        $db->beginTransaction();

        // Calculate total
        $total = 0;
        foreach ($items as $item) {
            $total += $item['quantity'] * $item['unit_price'];
        }

        // Insert PO
        $db->query(
            "INSERT INTO purchase_orders (po_number, supplier_id, order_date, expected_delivery, total_amount, notes, created_by, created_at) 
             VALUES (:po_number, :supplier_id, NOW(), :expected_delivery, :total, :notes, :user_id, NOW())",
            [
                'po_number' => $po_number,
                'supplier_id' => $supplier_id,
                'expected_delivery' => $expected_delivery,
                'total' => $total,
                'notes' => $notes,
                'user_id' => $_SESSION['user_id']
            ]
        );

        $po_id = $db->lastInsertId();

        // Insert PO items
        foreach ($items as $item) {
            $db->query(
                "INSERT INTO purchase_order_items (po_id, inventory_id, quantity, unit_price, total_price) 
                 VALUES (:po_id, :inventory_id, :quantity, :unit_price, :total)",
                [
                    'po_id' => $po_id,
                    'inventory_id' => $item['inventory_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total' => $item['quantity'] * $item['unit_price']
                ]
            );
        }

        $db->commit();

        echo json_encode(['success' => true, 'message' => 'Purchase order created: ' . $po_number]);
        exit();
    } else {
        throw new Exception('Invalid action: ' . $action);
    }

} catch (Exception $e) {
    // Log the error
    error_log('Inventory POST Controller Error: ' . $e->getMessage());
    error_log('POST Data: ' . print_r($_POST, true));

    // Return JSON error response
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
    exit();
}
?>