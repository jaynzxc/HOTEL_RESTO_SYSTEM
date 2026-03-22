<?php
/**
 * GET Controller - Admin Kitchen Orders (KOT)
 * Handles fetching kitchen orders, queue, statistics, and food inventory
 */

session_start();
require_once __DIR__ . '/../../../../Class/Database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !$_SESSION['logged_in']) {
    header('Location: ../../../../src/login-register/login_form.php');
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
$statusFilter = isset($_GET['status']) ? $_GET['status'] : 'all';
$priorityFilter = isset($_GET['priority']) ? $_GET['priority'] : 'all';
$searchFilter = isset($_GET['search']) ? $_GET['search'] : '';

// Build WHERE clause for kitchen orders (excluding completed and cancelled)
$whereConditions = ["fo.status IN ('new', 'preparing', 'ready', 'urgent')"];
$queryParams = [];

// Status filter
if ($statusFilter !== 'all') {
    $whereConditions[] = "fo.status = :status";
    $queryParams['status'] = $statusFilter;
}

// Priority filter
if ($priorityFilter === 'high') {
    $whereConditions[] = "fo.status = 'urgent'";
} elseif ($priorityFilter === 'medium') {
    $whereConditions[] = "fo.status = 'preparing'";
} elseif ($priorityFilter === 'low') {
    $whereConditions[] = "fo.status = 'new'";
}

// Search filter
if (!empty($searchFilter)) {
    $whereConditions[] = "(fo.order_reference LIKE :search1 OR u.full_name LIKE :search2 OR u.first_name LIKE :search3 OR u.last_name LIKE :search4)";
    $searchTerm = '%' . $searchFilter . '%';
    $queryParams['search1'] = $searchTerm;
    $queryParams['search2'] = $searchTerm;
    $queryParams['search3'] = $searchTerm;
    $queryParams['search4'] = $searchTerm;
}

$whereClause = implode(' AND ', $whereConditions);

// Get kitchen orders
$kitchenOrders = $db->query(
    "SELECT 
        fo.id,
        fo.order_reference,
        fo.user_id,
        fo.items,
        fo.order_type,
        fo.subtotal,
        fo.status,
        fo.created_at,
        fo.updated_at,
        u.full_name as customer_name,
        u.first_name,
        u.last_name,
        u.phone,
        rt.table_number,
        rr.id as reservation_id,
        JSON_LENGTH(fo.items) as item_count,
        TIMESTAMPDIFF(MINUTE, fo.created_at, NOW()) as wait_time_minutes,
        CASE 
            WHEN fo.status = 'urgent' THEN 'High'
            WHEN fo.status = 'preparing' THEN 'Medium'
            WHEN fo.status = 'new' THEN 'Normal'
            ELSE 'Normal'
        END as priority,
        CASE 
            WHEN fo.status = 'urgent' THEN 1
            WHEN fo.status = 'preparing' THEN 2
            WHEN fo.status = 'new' THEN 3
            WHEN fo.status = 'ready' THEN 4
            ELSE 5
        END as priority_order
     FROM food_orders fo
     LEFT JOIN users u ON fo.user_id = u.id
     LEFT JOIN restaurant_reservations rr ON rr.user_id = fo.user_id AND rr.reservation_date = CURDATE()
     LEFT JOIN restaurant_tables rt ON rr.table_number = rt.table_number
     WHERE $whereClause
     ORDER BY priority_order ASC, fo.created_at ASC",
    $queryParams
)->find() ?: [];

// Get statistics
$stats = $db->query(
    "SELECT 
        COUNT(CASE WHEN fo.status = 'new' THEN 1 END) as new_orders,
        COUNT(CASE WHEN fo.status = 'preparing' THEN 1 END) as preparing_orders,
        COUNT(CASE WHEN fo.status = 'ready' THEN 1 END) as ready_orders,
        COUNT(CASE WHEN fo.status = 'urgent' THEN 1 END) as urgent_orders,
        COUNT(CASE WHEN DATE(fo.created_at) = CURDATE() AND fo.status = 'completed' THEN 1 END) as completed_today,
        COALESCE(ROUND(AVG(CASE WHEN fo.status IN ('ready', 'completed') 
                THEN TIMESTAMPDIFF(MINUTE, fo.created_at, fo.updated_at) 
                ELSE NULL END)), 0) as avg_prep_time
     FROM food_orders fo
     WHERE fo.created_at >= CURDATE()",
    []
)->fetch_one();

// If no stats, provide defaults
if (!$stats) {
    $stats = [
        'new_orders' => 0,
        'preparing_orders' => 0,
        'ready_orders' => 0,
        'urgent_orders' => 0,
        'completed_today' => 0,
        'avg_prep_time' => 0
    ];
}

// Get preparation queue with estimated completion times
$prepQueue = $db->query(
    "SELECT 
        fo.id,
        fo.order_reference,
        fo.items,
        fo.order_type,
        fo.status,
        fo.created_at,
        JSON_LENGTH(fo.items) as item_count,
        u.full_name as customer_name,
        rt.table_number,
        TIMESTAMPDIFF(MINUTE, fo.created_at, NOW()) as wait_time,
        DATE_FORMAT(DATE_ADD(fo.created_at, INTERVAL 15 MINUTE), '%h:%i %p') as est_completion,
        CASE 
            WHEN fo.status = 'urgent' THEN 'High'
            WHEN fo.status = 'preparing' THEN 'Medium'
            ELSE 'Normal'
        END as priority,
        CASE 
            WHEN fo.status = 'urgent' THEN 'text-red-600 font-semibold'
            WHEN fo.status = 'preparing' THEN 'text-amber-600'
            ELSE 'text-blue-600'
        END as priority_color
     FROM food_orders fo
     LEFT JOIN users u ON fo.user_id = u.id
     LEFT JOIN restaurant_reservations rr ON rr.user_id = fo.user_id AND rr.reservation_date = CURDATE()
     LEFT JOIN restaurant_tables rt ON rr.table_number = rt.table_number
     WHERE fo.status IN ('new', 'preparing', 'urgent')
     ORDER BY 
        CASE 
            WHEN fo.status = 'urgent' THEN 1
            WHEN fo.status = 'preparing' THEN 2
            WHEN fo.status = 'new' THEN 3
        END,
        fo.created_at ASC",
    []
)->find() ?: [];

// Get FOOD INVENTORY ONLY (for kitchen stock management)
$foodInventory = $db->query(
    "SELECT id, item_name, category, stock, reorder_level, unit, created_at, updated_at
     FROM inventory 
     WHERE category IN ('Food', 'Meat', 'Beverage', 'Vegetable', 'Fruit', 'Dairy', 'Spice', 'Sauce', 'Oil')
     ORDER BY 
        CASE 
            WHEN stock <= reorder_level THEN 1
            ELSE 2
        END,
        item_name ASC",
    []
)->find() ?: [];

// Get low stock food items for kitchen warning
$lowStockFoodItems = $db->query(
    "SELECT id, item_name, stock, reorder_level, unit 
     FROM inventory 
     WHERE category IN ('Food', 'Meat', 'Beverage', 'Vegetable', 'Fruit', 'Dairy', 'Spice', 'Sauce', 'Oil')
     AND stock <= reorder_level 
     ORDER BY (stock / reorder_level) ASC 
     LIMIT 10",
    []
)->find() ?: [];

// Calculate orders in progress
$ordersInProgress = count(array_filter($kitchenOrders, function ($o) {
    return in_array($o['status'], ['new', 'preparing', 'urgent']);
}));

// Calculate average wait time from the prep queue
$avgWaitTime = 0;
if (!empty($prepQueue)) {
    $totalWait = array_sum(array_column($prepQueue, 'wait_time'));
    $avgWaitTime = round($totalWait / count($prepQueue), 1);
}

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

// Store data for view
$viewData = [
    'admin' => $admin,
    'initials' => $initials,
    'kitchenOrders' => $kitchenOrders,
    'prepQueue' => $prepQueue,
    'stats' => $stats,
    'ordersInProgress' => $ordersInProgress,
    'avgWaitTime' => $avgWaitTime,
    'statusFilter' => $statusFilter,
    'priorityFilter' => $priorityFilter,
    'searchFilter' => $searchFilter,
    'foodInventory' => $foodInventory,
    'lowStockFoodItems' => $lowStockFoodItems,
    'today' => date('F j, Y')
];

// Extract variables for view
extract($viewData);
?>