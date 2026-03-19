<?php
/**
 * GET Controller - Admin Room Management
 * Handles fetching all rooms with statistics and maintenance schedules
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
$searchFilter = isset($_GET['search']) ? $_GET['search'] : '';
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Build WHERE clause for rooms
$whereConditions = ["1=1"];
$queryParams = [];

if ($statusFilter !== 'all') {
    switch ($statusFilter) {
        case 'available':
            $whereConditions[] = "r.is_available = 1 AND rm.id IS NULL AND r.needs_cleaning = 0";
            break;
        case 'reserved':
            $whereConditions[] = "b.id IS NOT NULL AND b.status = 'confirmed' AND b.check_out >= CURDATE()";
            break;
        case 'occupied':
            $whereConditions[] = "b.id IS NOT NULL AND b.status = 'checked-in' AND b.check_out >= CURDATE()";
            break;
        case 'dirty':
            $whereConditions[] = "r.is_available = 1 AND b.id IS NULL AND rm.id IS NULL";
            $whereConditions[] = "r.needs_cleaning = 1";
            break;
        case 'maintenance':
        case 'out of order':
            $whereConditions[] = "rm.id IS NOT NULL AND rm.cleaned_at IS NULL AND rm.completed_at IS NULL";
            break;
    }
}

if (!empty($searchFilter)) {
    $whereConditions[] = "(r.id LIKE :search1 OR r.name LIKE :search2)";
    $searchTerm = '%' . $searchFilter . '%';
    $queryParams['search1'] = $searchTerm;
    $queryParams['search2'] = $searchTerm;
}

$whereClause = implode(' AND ', $whereConditions);

// Get total count for pagination
$countQuery = "SELECT COUNT(DISTINCT r.id) as total 
               FROM rooms r
               LEFT JOIN bookings b ON r.id = b.room_id 
                  AND b.status IN ('confirmed', 'checked-in')
                  AND b.check_out >= CURDATE()
               LEFT JOIN room_maintenance rm ON r.id = rm.room_id 
                  AND rm.cleaned_at IS NULL 
                  AND rm.completed_at IS NULL
               WHERE $whereClause";
$countResult = $db->query($countQuery, $queryParams)->fetch_one();
$totalRooms = $countResult['total'];
$totalPages = ceil($totalRooms / $limit);

// Get all rooms with current booking and maintenance info
$rooms = $db->query(
    "SELECT 
        r.id as room_id,
        r.id as room_number,
        r.name as type,
        r.price,
        r.max_occupancy,
        r.beds,
        r.view,
        r.amenities,
        r.is_available,
        r.needs_cleaning,
        CASE 
            WHEN rm.id IS NOT NULL AND rm.cleaned_at IS NULL AND rm.completed_at IS NULL THEN 'maintenance'
            WHEN b.id IS NOT NULL AND b.status = 'checked-in' AND b.check_out >= CURDATE() THEN 'occupied'
            WHEN b.id IS NOT NULL AND b.status = 'confirmed' AND b.check_out >= CURDATE() THEN 'reserved'
            WHEN r.needs_cleaning = 1 THEN 'dirty'
            ELSE 'available'
        END as status,
        CASE 
            WHEN rm.id IS NOT NULL AND rm.cleaned_at IS NULL AND rm.completed_at IS NULL THEN 'maintenance'
            WHEN r.needs_cleaning = 1 THEN 'dirty'
            ELSE 'clean'
        END as housekeeping,
        CONCAT(b.guest_first_name, ' ', b.guest_last_name) as guest,
        b.check_in,
        b.check_out,
        b.booking_reference,
        b.status as booking_status,
        rm.id as maintenance_id,
        rm.condition_status as maintenance_priority,
        rm.notes as maintenance_notes,
        rm.reported_at as maintenance_reported
     FROM rooms r
     LEFT JOIN bookings b ON r.id = b.room_id 
        AND b.status IN ('confirmed', 'checked-in')
        AND b.check_out >= CURDATE()
     LEFT JOIN room_maintenance rm ON r.id = rm.room_id 
        AND rm.cleaned_at IS NULL 
        AND rm.completed_at IS NULL
     WHERE $whereClause
     ORDER BY 
        CASE 
            WHEN rm.condition_status = 'damage' THEN 1
            WHEN rm.condition_status = 'maintenance' THEN 2
            WHEN r.needs_cleaning = 1 THEN 3
            WHEN b.status = 'checked-in' THEN 4
            WHEN b.status = 'confirmed' THEN 5
            ELSE 6
        END,
        r.id ASC
     LIMIT $limit OFFSET $offset",
    $queryParams
)->find() ?: [];

// Get room statistics
$stats = $db->query(
    "SELECT 
        COUNT(DISTINCT r.id) as total,
        SUM(CASE WHEN b.id IS NOT NULL AND b.status = 'checked-in' AND b.check_out >= CURDATE() THEN 1 ELSE 0 END) as occupied,
        SUM(CASE WHEN b.id IS NOT NULL AND b.status = 'confirmed' AND b.check_out >= CURDATE() THEN 1 ELSE 0 END) as reserved,
        SUM(CASE WHEN rm.id IS NOT NULL AND rm.cleaned_at IS NULL AND rm.completed_at IS NULL THEN 1 ELSE 0 END) as maintenance,
        SUM(CASE WHEN r.needs_cleaning = 1 AND b.id IS NULL AND rm.id IS NULL THEN 1 ELSE 0 END) as dirty,
        SUM(CASE WHEN r.is_available = 1 AND b.id IS NULL AND rm.id IS NULL AND r.needs_cleaning = 0 THEN 1 ELSE 0 END) as available
     FROM rooms r
     LEFT JOIN bookings b ON r.id = b.room_id 
        AND b.status IN ('confirmed', 'checked-in')
        AND b.check_out >= CURDATE()
     LEFT JOIN room_maintenance rm ON r.id = rm.room_id 
        AND rm.cleaned_at IS NULL 
        AND rm.completed_at IS NULL",
    []
)->fetch_one();

// Get maintenance schedules
$maintenanceItems = $db->query(
    "SELECT 
        rm.*,
        r.id as room_number,
        u.full_name as reported_by_name,
        CASE 
            WHEN rm.condition_status = 'damage' THEN 'urgent'
            WHEN rm.condition_status = 'maintenance' THEN 'high'
            ELSE 'normal'
        END as priority_level
     FROM room_maintenance rm
     LEFT JOIN rooms r ON rm.room_id = r.id
     LEFT JOIN users u ON rm.reported_by = u.id
     WHERE rm.cleaned_at IS NULL AND rm.completed_at IS NULL
     ORDER BY 
        CASE 
            WHEN rm.condition_status = 'damage' THEN 1
            WHEN rm.condition_status = 'maintenance' THEN 2
            ELSE 3
        END,
        rm.reported_at ASC
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



// Store data for view
$viewData = [
    'admin' => $admin,
    'initials' => $initials,
    'rooms' => $rooms,
    'stats' => $stats,
    'maintenanceItems' => $maintenanceItems,
    'totalRooms' => $totalRooms,
    'totalPages' => $totalPages,
    'currentPage' => $page,
    'statusFilter' => $statusFilter,
    'searchFilter' => $searchFilter,
    
    'today' => date('F j, Y')
];

// Extract variables for view
extract($viewData);
?>