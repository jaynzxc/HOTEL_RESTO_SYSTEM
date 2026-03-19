<?php
/**
 * GET Controller - Admin All Guest Requests
 * Handles fetching all guest requests from guest_interactions table
 */

session_start();
require_once __DIR__ . '/../../../Class/Database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !$_SESSION['logged_in']) {
    header('Location: ../../../src/login-register/login_form.php');
    exit();
}

$config = require __DIR__ . '/../../../config/config.php';
$db = new Database($config['database']);

// Get user role from database
$user = $db->query(
    "SELECT role FROM users WHERE id = :id",
    ['id' => $_SESSION['user_id']]
)->fetch_one();

// Check if user has admin or staff role
if (!$user || !in_array($user['role'], ['admin', 'staff'])) {
    header('Location: ../../customer_portal/dashboard.php');
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
$searchFilter = isset($_GET['search']) ? $_GET['search'] : '';

// Build WHERE clause
$whereConditions = ["1=1"]; // Start with true condition
$queryParams = [];

if ($statusFilter !== 'all') {
    $whereConditions[] = "gi.status = :status";
    $queryParams['status'] = $statusFilter;
}

if (!empty($searchFilter)) {
    $whereConditions[] = "(CONCAT(u.first_name, ' ', u.last_name) LIKE :search1 OR u.full_name LIKE :search2 OR gi.message LIKE :search3 OR gi.subject LIKE :search4)";
    $searchTerm = '%' . $searchFilter . '%';
    $queryParams['search1'] = $searchTerm;
    $queryParams['search2'] = $searchTerm;
    $queryParams['search3'] = $searchTerm;
    $queryParams['search4'] = $searchTerm;
}

$whereClause = implode(' AND ', $whereConditions);

// Get all guest interactions/requests
// REMOVED u.room_number since it doesn't exist in users table
$requests = $db->query(
    "SELECT 
        gi.id,
        gi.type,
        gi.subject,
        gi.message,
        gi.created_at,
        gi.status,
        gi.priority,
        gi.assigned_to,
        gi.response,
        gi.completed_at,
        u.id as user_id,
        u.full_name as guest_name,
        u.first_name,
        u.last_name,
        u.email as guest_email,
        u.phone as guest_phone,
        -- You can get room from bookings if needed, but for now set as NULL or 'N/A'
        NULL as room_number,
        CONCAT(u.first_name, ' ', u.last_name) as guest_full_name,
        a.full_name as assigned_to_name
     FROM guest_interactions gi
     LEFT JOIN users u ON gi.user_id = u.id
     LEFT JOIN users a ON gi.assigned_to = a.id
     WHERE $whereClause
     ORDER BY 
        CASE 
            WHEN gi.status = 'pending' THEN 1
            WHEN gi.status = 'in-progress' THEN 2
            WHEN gi.status = 'done' THEN 3
            ELSE 4
        END,
        gi.created_at DESC",
    $queryParams
)->find() ?: [];

// Get statistics
$stats = $db->query(
    "SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN status = 'in-progress' THEN 1 ELSE 0 END) as in_progress,
        SUM(CASE WHEN status = 'done' THEN 1 ELSE 0 END) as done
     FROM guest_interactions",
    []
)->fetch_one();

// If no stats, provide defaults
if (!$stats) {
    $stats = [
        'total' => 0,
        'pending' => 0,
        'in_progress' => 0,
        'done' => 0
    ];
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
    'requests' => $requests,
    'stats' => $stats,
    'statusFilter' => $statusFilter,
    'searchFilter' => $searchFilter,

    'today' => date('F j, Y'),
    'todaySql' => date('Y-m-d')
];

// Extract variables for view
extract($viewData);
?>