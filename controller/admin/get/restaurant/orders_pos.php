<?php
/**
 * GET Controller - Admin Orders / POS
 * Handles fetching orders, statistics, and queue data
 */

session_start();
require_once __DIR__ . '/../../../../Class/Database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !$_SESSION['logged_in']) {
    header('Location: ../../view/auth/login.php');
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
$typeFilter = isset($_GET['type']) ? $_GET['type'] : 'all';
$statusFilter = isset($_GET['status']) ? $_GET['status'] : 'all';
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Build WHERE clause - FIXED: Added table prefixes
$whereConditions = ["1=1"];
$queryParams = [];

// Type filter
if ($typeFilter !== 'all') {
    $whereConditions[] = "fo.order_type = :type";
    $queryParams['type'] = $typeFilter;
}

// Status filter
if ($statusFilter !== 'all') {
    $whereConditions[] = "fo.status = :status";
    $queryParams['status'] = $statusFilter;
}

$whereClause = implode(' AND ', $whereConditions);

// Get total count for pagination
$countQuery = "SELECT COUNT(*) as total FROM food_orders fo WHERE $whereClause";
$countResult = $db->query($countQuery, $queryParams)->fetch_one();
$totalOrders = $countResult['total'];
$totalPages = ceil($totalOrders / $limit);

// Get orders with user details - FIXED: Added table prefixes for all columns
$orders = $db->query(
    "SELECT 
        fo.id,
        fo.order_reference,
        fo.user_id,
        fo.items,
        fo.order_type,
        fo.subtotal,
        fo.service_fee,
        fo.total_amount,
        fo.points_used,
        fo.points_earned,
        fo.status,
        fo.created_at,
        fo.updated_at,
        u.full_name as customer_name,
        u.first_name,
        u.last_name,
        u.phone,
        rt.table_number,
        rr.id as reservation_id
     FROM food_orders fo
     LEFT JOIN users u ON fo.user_id = u.id
     LEFT JOIN restaurant_reservations rr ON rr.user_id = fo.user_id AND rr.reservation_date = CURDATE()
     LEFT JOIN restaurant_tables rt ON rr.table_number = rt.table_number
     WHERE $whereClause
     ORDER BY 
        CASE 
            WHEN fo.status = 'pending' THEN 1
            WHEN fo.status = 'preparing' THEN 2
            WHEN fo.status = 'ready' THEN 3
            WHEN fo.status = 'served' THEN 4
            WHEN fo.status = 'completed' THEN 5
            WHEN fo.status = 'cancelled' THEN 6
            ELSE 7
        END,
        fo.created_at DESC
     LIMIT $limit OFFSET $offset",
    $queryParams
)->find() ?: [];

// Get kitchen queue (preparing orders)
$kitchenQueue = $db->query(
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

// Get ready queue (ready for pickup/serve)
$readyQueue = $db->query(
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

// Get statistics - FIXED: Added table prefix in CASE statements
$stats = $db->query(
    "SELECT 
        COUNT(CASE WHEN fo.status NOT IN ('completed', 'cancelled') THEN 1 END) as active_orders,
        COUNT(CASE WHEN fo.order_type = 'dine-in' AND fo.status NOT IN ('completed', 'cancelled') THEN 1 END) as dine_in,
        COUNT(CASE WHEN fo.order_type = 'takeout' AND fo.status NOT IN ('completed', 'cancelled') THEN 1 END) as takeout,
        COUNT(CASE WHEN fo.order_type = 'delivery' AND fo.status NOT IN ('completed', 'cancelled') THEN 1 END) as delivery,
        COALESCE(SUM(CASE WHEN DATE(fo.created_at) = CURDATE() AND fo.status = 'completed' THEN fo.total_amount ELSE 0 END), 0) as today_revenue
     FROM food_orders fo",
    []
)->fetch_one();

// If no stats, provide defaults
if (!$stats) {
    $stats = [
        'active_orders' => 0,
        'dine_in' => 0,
        'takeout' => 0,
        'delivery' => 0,
        'today_revenue' => 0
    ];
}

// Calculate average preparation time (from pending to ready)
$avgPrepTime = $db->query(
    "SELECT AVG(TIMESTAMPDIFF(MINUTE, fo.created_at, fo.updated_at)) as avg_time
     FROM food_orders fo
     WHERE fo.status IN ('ready', 'served', 'completed')
     AND fo.updated_at > fo.created_at
     LIMIT 50",
    []
)->fetch_one();
$avgPrepTime = $avgPrepTime ? round($avgPrepTime['avg_time']) : 12;

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
        "SELECT COUNT(*) as count FROM notifications 
         WHERE user_id = :user_id AND is_read = 0",
        ['user_id' => $_SESSION['user_id']]
    )->fetch_one();
    $unread_count = $unread_result['count'] ?? 0;
} catch (Exception $e) {
    $unread_count = 0;
}

// Store data for view
$viewData = [
    'admin' => $admin,
    'initials' => $initials,
    'orders' => $orders,
    'kitchenQueue' => $kitchenQueue,
    'readyQueue' => $readyQueue,
    'stats' => $stats,
    'avgPrepTime' => $avgPrepTime,
    'totalOrders' => $totalOrders,
    'totalPages' => $totalPages,
    'currentPage' => $page,
    'typeFilter' => $typeFilter,
    'statusFilter' => $statusFilter,
    'unread_count' => $unread_count,
    'today' => date('F j, Y')
];

// Extract variables for view
extract($viewData);
?>