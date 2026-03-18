<?php
/**
 * GET Controller - Admin Table Reservation
 * Handles fetching restaurant reservations, table status, and statistics
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
$statusFilter = isset($_GET['status']) ? $_GET['status'] : 'all';
$dateFilter = isset($_GET['date']) ? $_GET['date'] : 'all';
$searchFilter = isset($_GET['search']) ? $_GET['search'] : '';
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Build WHERE clause
$whereConditions = ["1=1"];
$queryParams = [];

// Date filter
if ($dateFilter === 'today') {
    $whereConditions[] = "rr.reservation_date = CURDATE()";
} elseif ($dateFilter === 'tomorrow') {
    $whereConditions[] = "rr.reservation_date = DATE_ADD(CURDATE(), INTERVAL 1 DAY)";
} elseif ($dateFilter === 'this_week') {
    $whereConditions[] = "rr.reservation_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)";
} elseif ($dateFilter === 'this_month') {
    $whereConditions[] = "MONTH(rr.reservation_date) = MONTH(CURDATE()) AND YEAR(rr.reservation_date) = YEAR(CURDATE())";
} elseif ($dateFilter === 'past') {
    $whereConditions[] = "rr.reservation_date < CURDATE()";
} elseif ($dateFilter !== 'all' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateFilter)) {
    $whereConditions[] = "rr.reservation_date = :date";
    $queryParams['date'] = $dateFilter;
}

// Status filter
if ($statusFilter !== 'all') {
    $whereConditions[] = "rr.status = :status";
    $queryParams['status'] = $statusFilter;
}

// Search filter
if (!empty($searchFilter)) {
    $whereConditions[] = "(CONCAT(rr.guest_first_name, ' ', rr.guest_last_name) LIKE :search1 OR rr.guest_email LIKE :search2 OR rr.guest_phone LIKE :search3)";
    $searchTerm = '%' . $searchFilter . '%';
    $queryParams['search1'] = $searchTerm;
    $queryParams['search2'] = $searchTerm;
    $queryParams['search3'] = $searchTerm;
}

$whereClause = implode(' AND ', $whereConditions);

// Get total count for pagination
$countQuery = "SELECT COUNT(*) as total FROM restaurant_reservations rr WHERE $whereClause";
$countResult = $db->query($countQuery, $queryParams)->fetch_one();
$totalReservations = $countResult['total'];
$totalPages = ceil($totalReservations / $limit);

// Get all restaurant reservations with user balance info
$reservations = $db->query(
    "SELECT 
        rr.id,
        rr.reservation_reference,
        CONCAT(rr.guest_first_name, ' ', rr.guest_last_name) as guest_name,
        rr.guest_email,
        rr.guest_phone,
        rr.reservation_date,
        DATE_FORMAT(rr.reservation_time, '%h:%i %p') as formatted_time,
        rr.reservation_time,
        rr.guests,
        rr.table_number,
        rr.special_requests,
        rr.occasion,
        rr.status,
        rr.payment_status,
        rr.down_payment,
        rr.points_earned,
        rr.points_awarded,
        rr.user_id,
        rr.created_at,
        u.member_tier,
        u.loyalty_points,
        COALESCE(cb.total_balance, 0) as current_balance,
        DATEDIFF(rr.reservation_date, CURDATE()) as days_difference,
        CASE 
            WHEN rr.user_id IS NOT NULL AND COALESCE(cb.total_balance, 0) > 0 THEN 1
            ELSE 0
        END as has_outstanding_balance
     FROM restaurant_reservations rr
     LEFT JOIN users u ON rr.user_id = u.id
     LEFT JOIN current_balance cb ON rr.user_id = cb.user_id
     WHERE $whereClause
     ORDER BY 
        CASE 
            WHEN rr.reservation_date >= CURDATE() THEN 0
            ELSE 1
        END,
        rr.reservation_date ASC,
        rr.reservation_time ASC
     LIMIT $limit OFFSET $offset",
    $queryParams
)->find() ?: [];

// Get all reservations for statistics (unfiltered)
$allReservations = $db->query(
    "SELECT * FROM restaurant_reservations",
    []
)->find() ?: [];

// Get waiting list
$waitingList = $db->query(
    "SELECT * FROM waiting_list 
     WHERE status = 'waiting'
     ORDER BY wait_started_at ASC",
    []
)->find() ?: [];

// Get table status
$tables = $db->query(
    "SELECT * FROM restaurant_tables 
     ORDER BY id ASC",
    []
)->find() ?: [];

// Calculate statistics
$total_guests = 0;
$walk_ins = 0;
$no_shows = 0;
$today_reservations = 0;
$upcoming_reservations = 0;
$completed_today = 0;
$total_unpaid_balance = 0;

foreach ($allReservations as $res) {
    $total_guests += $res['guests'] ?? 0;
    if ($res['status'] === 'no-show')
        $no_shows++;
    if ($res['reservation_date'] == date('Y-m-d')) {
        $today_reservations++;
        if ($res['status'] === 'completed' || $res['status'] === 'seated') {
            $completed_today++;
        }
    }
    if ($res['reservation_date'] >= date('Y-m-d') && !in_array($res['status'], ['cancelled', 'completed', 'no-show'])) {
        $upcoming_reservations++;
    }
}

// Count walk-ins (reservations with 'WALK' in reference)
foreach ($reservations as $res) {
    if (strpos($res['reservation_reference'], 'WALK') !== false) {
        $walk_ins++;
    }
}

// Get total unpaid balance from current_balance
$balanceStats = $db->query(
    "SELECT COALESCE(SUM(total_balance), 0) as total_unpaid FROM current_balance",
    []
)->fetch_one();
$total_unpaid_balance = $balanceStats['total_unpaid'] ?? 0;

$stats = [
    'total_reservations' => count($allReservations),
    'today_reservations' => $today_reservations,
    'upcoming_reservations' => $upcoming_reservations,
    'total_guests' => $total_guests,
    'available_tables' => count(array_filter($tables, function ($t) {
        return $t['status'] === 'available'; })),
    'walk_ins' => $walk_ins,
    'no_shows' => $no_shows,
    'completed_today' => $completed_today,
    'total_unpaid_balance' => $total_unpaid_balance,
    'guests_with_balance' => count(array_filter($reservations, function ($r) {
        return $r['has_outstanding_balance'] > 0; }))
];

// Get available dates for filter dropdown
$availableDates = $db->query(
    "SELECT DISTINCT reservation_date 
     FROM restaurant_reservations 
     WHERE reservation_date >= CURDATE()
     ORDER BY reservation_date ASC
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
    'reservations' => $reservations,
    'allReservations' => $allReservations,
    'waitingList' => $waitingList,
    'tables' => $tables,
    'stats' => $stats,
    'availableDates' => $availableDates,
    'totalReservations' => $totalReservations,
    'totalPages' => $totalPages,
    'currentPage' => $page,
    'statusFilter' => $statusFilter,
    'dateFilter' => $dateFilter,
    'searchFilter' => $searchFilter,
    'unread_count' => $unread_count,
    'today' => date('Y-m-d'),
    'todayFormatted' => date('F j, Y')
];

// Extract variables for view
extract($viewData);
?>