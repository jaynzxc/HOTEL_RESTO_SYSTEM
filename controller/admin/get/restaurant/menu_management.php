<?php
/**
 * GET Controller - Admin Menu Management
 * Handles fetching menu items, categories, and statistics
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
$categoryFilter = isset($_GET['category']) ? $_GET['category'] : 'all';
$searchFilter = isset($_GET['search']) ? $_GET['search'] : '';
$statusFilter = isset($_GET['status']) ? $_GET['status'] : 'all';
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Build WHERE clause
$whereConditions = ["1=1"];
$queryParams = [];

// Category filter
if ($categoryFilter !== 'all') {
    $whereConditions[] = "LOWER(category) = :category";
    $queryParams['category'] = strtolower($categoryFilter);
}

// Status filter
if ($statusFilter !== 'all') {
    if ($statusFilter === 'available') {
        $whereConditions[] = "status = 'available' AND is_available = 1 AND stock > 0";
    } elseif ($statusFilter === 'out_of_stock') {
        $whereConditions[] = "(status = 'out_of_stock' OR stock <= 0)";
    } elseif ($statusFilter === 'special') {
        $whereConditions[] = "status = 'special'";
    } elseif ($statusFilter === 'disabled') {
        $whereConditions[] = "is_available = 0";
    }
}

// Search filter
if (!empty($searchFilter)) {
    $whereConditions[] = "(name LIKE :search1 OR description LIKE :search2 OR item_code LIKE :search3)";
    $searchTerm = '%' . $searchFilter . '%';
    $queryParams['search1'] = $searchTerm;
    $queryParams['search2'] = $searchTerm;
    $queryParams['search3'] = $searchTerm;
}

$whereClause = implode(' AND ', $whereConditions);

// Get total count for pagination
$countQuery = "SELECT COUNT(*) as total FROM menu_items WHERE $whereClause";
$countResult = $db->query($countQuery, $queryParams)->fetch_one();
$totalItems = $countResult['total'];
$totalPages = ceil($totalItems / $limit);

// Get menu items - FIXED: Removed item_code from query since it doesn't exist yet
$menuItems = $db->query(
    "SELECT 
        id,
        name,
        description,
        category,
        price,
        COALESCE(cost, price * 0.5) as cost,
        COALESCE(stock, 0) as stock,
        status,
        is_available,
        image_url,
        preparation_time,
        CONCAT(
            CASE 
                WHEN LOWER(category) = 'mains' THEN 'M'
                WHEN LOWER(category) = 'appetizers' THEN 'A'
                WHEN LOWER(category) = 'desserts' THEN 'D'
                WHEN LOWER(category) = 'beverages' THEN 'B'
                ELSE 'X'
            END,
            LPAD(id, 3, '0')
        ) as item_code,
        created_at,
        updated_at
     FROM menu_items
     WHERE $whereClause
     ORDER BY 
        CASE 
            WHEN status = 'special' THEN 1
            WHEN is_available = 1 THEN 2
            ELSE 3
        END,
        category ASC,
        name ASC
     LIMIT $limit OFFSET $offset",
    $queryParams
)->find() ?: [];

// Get all distinct categories
$categories = $db->query(
    "SELECT DISTINCT LOWER(category) as category FROM menu_items WHERE category IS NOT NULL ORDER BY category ASC",
    []
)->find() ?: [];

// Get statistics
$stats = $db->query(
    "SELECT 
        COUNT(*) as total_items,
        SUM(CASE WHEN is_available = 1 AND status != 'out_of_stock' AND stock > 0 THEN 1 ELSE 0 END) as available,
        SUM(CASE WHEN status = 'out_of_stock' OR stock <= 0 THEN 1 ELSE 0 END) as out_of_stock,
        SUM(CASE WHEN status = 'special' THEN 1 ELSE 0 END) as specials,
        COUNT(DISTINCT category) as total_categories
     FROM menu_items",
    []
)->fetch_one();

// If no stats, provide defaults
if (!$stats) {
    $stats = [
        'total_items' => 0,
        'available' => 0,
        'out_of_stock' => 0,
        'specials' => 0,
        'total_categories' => 0
    ];
}

// Get low stock items (stock <= 10)
$lowStockItems = $db->query(
    "SELECT name, stock FROM menu_items 
     WHERE stock <= 10 AND stock > 0 AND is_available = 1
     ORDER BY stock ASC
     LIMIT 5",
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



// Store data for view
$viewData = [
    'admin' => $admin,
    'initials' => $initials,
    'menuItems' => $menuItems,
    'categories' => $categories,
    'stats' => $stats,
    'lowStockItems' => $lowStockItems,
    'totalItems' => $totalItems,
    'totalPages' => $totalPages,
    'currentPage' => $page,
    'categoryFilter' => $categoryFilter,
    'statusFilter' => $statusFilter,
    'searchFilter' => $searchFilter,
    
    'today' => date('F j, Y')
];

// Extract variables for view
extract($viewData);
?>