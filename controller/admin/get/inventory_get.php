<?php
/**
 * GET Controller - Admin Inventory & Stock Management
 */

session_start();
require_once __DIR__ . '/../../../Class/Database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !$_SESSION['logged_in']) {
    header('Location: ../../view/auth/login.php');
    exit();
}

$config = require __DIR__ . '/../../../config/config.php';
$db = new Database($config['database']);

// Get user role from database
$user = $db->query(
    "SELECT role FROM users WHERE id = :id",
    ['id' => $_SESSION['user_id']]
)->fetch_one();

// Check if user has admin role
if (!$user || $user['role'] !== 'admin') {
    header('Location: ../../view/customer_portal/dashboard.php');
    exit();
}

// Get admin user data
$admin = $db->query(
    "SELECT id, full_name, first_name, last_name, email, role, avatar
     FROM users WHERE id = :id",
    ['id' => $_SESSION['user_id']]
)->fetch_one();

// Get filter parameters
$categoryFilter = isset($_GET['category']) ? $_GET['category'] : 'all';
$searchFilter = isset($_GET['search']) ? $_GET['search'] : '';
$statusFilter = isset($_GET['status']) ? $_GET['status'] : 'all';

// Build inventory query with filters
$query = "SELECT * FROM inventory WHERE 1=1";
$params = [];

if ($categoryFilter !== 'all') {
    $query .= " AND category = :category";
    $params['category'] = $categoryFilter;
}

if ($searchFilter) {
    $query .= " AND item_name LIKE :search";
    $params['search'] = "%$searchFilter%";
}

$query .= " ORDER BY 
    CASE 
        WHEN stock <= reorder_level THEN 1
        ELSE 2
    END,
    item_name ASC";

$inventory = $db->query($query, $params)->find() ?: [];

// Calculate statistics
$totalItems = count($inventory);
$inStock = 0;
$lowStock = 0;
$outOfStock = 0;
$toReorder = 0;

foreach ($inventory as $item) {
    if ($item['stock'] <= 0) {
        $outOfStock++;
    } elseif ($item['stock'] <= $item['reorder_level']) {
        $lowStock++;
        $toReorder++;
    } else {
        $inStock++;
    }
}

$stats = [
    'total_items' => $totalItems,
    'in_stock' => $inStock,
    'low_stock' => $lowStock,
    'out_of_stock' => $outOfStock,
    'to_reorder' => $toReorder
];

// Get items that need reordering
$itemsToReorder = array_filter($inventory, function ($item) {
    return $item['stock'] <= $item['reorder_level'] && $item['stock'] > 0;
});

// Get suppliers
$suppliers = $db->query(
    "SELECT * FROM suppliers ORDER BY name ASC",
    []
)->find() ?: [];

// Get recent stock movements
$recentMovements = $db->query(
    "SELECT sm.*, i.item_name, i.unit 
     FROM stock_movements sm
     LEFT JOIN inventory i ON sm.inventory_id = i.id
     ORDER BY sm.created_at DESC
     LIMIT 10",
    []
)->find() ?: [];

// Get admin initials
$initials = 'A';
if ($admin) {
    $first_name = $admin['first_name'] ?? '';
    $last_name = $admin['last_name'] ?? '';
    $full_name = $admin['full_name'] ?? 'Admin';

    if (!empty($first_name) && !empty($last_name)) {
        $initials = strtoupper(substr($first_name, 0, 1) . substr($last_name, 0, 1));
    } elseif (!empty($full_name)) {
        $name_parts = explode(' ', trim($full_name), 2);
        $initials = strtoupper(substr($name_parts[0], 0, 1));
        if (isset($name_parts[1])) {
            $initials .= strtoupper(substr($name_parts[1], 0, 1));
        }
    }
}

// Get unread notifications count
try {
    $unread_result = $db->query(
        "SELECT COUNT(*) as count FROM admin_notifications 
         WHERE admin_id = :admin_id AND is_read = 0",
        ['admin_id' => $_SESSION['user_id']]
    )->fetch_one();
    $unread_count = $unread_result['count'] ?? 0;
} catch (Exception $e) {
    $unread_count = 0;
}

// Store data for view
$viewData = [
    'admin' => $admin,
    'initials' => $initials,
    'inventory' => $inventory,
    'stats' => $stats,
    'itemsToReorder' => $itemsToReorder,
    'suppliers' => $suppliers,
    'recentMovements' => $recentMovements,
    'categoryFilter' => $categoryFilter,
    'searchFilter' => $searchFilter,
    'statusFilter' => $statusFilter,
    'unread_count' => $unread_count,
    'today' => date('F j, Y')
];

// Extract variables for view
extract($viewData);
?>