<?php
/**
 * GET Controller - Admin Customer Relationship (CRM)
 * Handles fetching all guest data for CRM
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
// if (!$user || $user['role'] !== 'admin') {
//     header('Location: ../../view/customer_portal/dashboard.php');
//     exit();
// }

// Get admin user data
$admin = $db->query(
    "SELECT id, full_name, first_name, last_name, email, role, avatar
     FROM users WHERE id = :id",
    ['id' => $_SESSION['user_id']]
)->fetch_one();

// Get filter parameters
$tierFilter = isset($_GET['tier']) ? $_GET['tier'] : 'all';
$stayFilter = isset($_GET['stay']) ? (int) $_GET['stay'] : 0;
$searchFilter = isset($_GET['search']) ? $_GET['search'] : '';

// STATISTICS CARDS
$stats = $db->query(
    "SELECT 
        COUNT(*) as total_guests,
        SUM(CASE WHEN last_login >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) as active_this_month,
        SUM(CASE WHEN member_tier IN ('gold', 'platinum') THEN 1 ELSE 0 END) as vip_guests,
        SUM(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) as new_this_week,
        ROUND(
            (SELECT COUNT(DISTINCT user_id) FROM bookings WHERE created_at >= DATE_SUB(NOW(), INTERVAL 180 DAY)) * 100.0 / 
            NULLIF((SELECT COUNT(*) FROM users WHERE created_at < DATE_SUB(NOW(), INTERVAL 180 DAY) AND role = 'customer'), 0), 1
        ) as retention_rate
     FROM users WHERE role = 'customer'",
    []
)->fetch_one();

// Ensure stats have default values
if (!$stats) {
    $stats = [
        'total_guests' => 0,
        'active_this_month' => 0,
        'vip_guests' => 0,
        'new_this_week' => 0,
        'retention_rate' => 0
    ];
}

// Build WHERE clause for filtering with parameters
$whereConditions = ["role = 'customer'"];
$queryParams = [];

if ($tierFilter !== 'all') {
    $whereConditions[] = "member_tier = :tier";
    $queryParams['tier'] = $tierFilter;
}

if ($stayFilter > 0) {
    $whereConditions[] = "last_login >= DATE_SUB(NOW(), INTERVAL :stay DAY)";
    $queryParams['stay'] = $stayFilter;
}

if (!empty($searchFilter)) {
    $whereConditions[] = "(full_name LIKE :search OR email LIKE :search OR phone LIKE :search)";
    $queryParams['search'] = '%' . $searchFilter . '%';
}

$whereClause = implode(' AND ', $whereConditions);

// GUEST LIST with pagination
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Get total count with filters
$countResult = $db->query(
    "SELECT COUNT(*) as total FROM users WHERE $whereClause",
    $queryParams
)->fetch_one();
$totalGuests = $countResult['total'] ?? 0;
$totalPages = ceil($totalGuests / $limit);

// IMPORTANT: For LIMIT and OFFSET, we need to use them as integers directly in the query
// We cannot use named placeholders for LIMIT/OFFSET as they are treated as strings
$guests = $db->query(
    "SELECT 
        u.id,
        u.full_name,
        u.email,
        u.phone,
        u.loyalty_points,
        u.member_tier,
        u.created_at,
        u.last_login,
        u.preferences,
        u.allergies,
        u.birthday,
        u.anniversary,
        COALESCE((
            SELECT COUNT(*) FROM bookings WHERE user_id = u.id
        ), 0) as total_stays,
        COALESCE((
            SELECT MAX(check_out) FROM bookings WHERE user_id = u.id
        ), u.created_at) as last_stay,
        CONCAT(LEFT(u.first_name, 1), LEFT(u.last_name, 1)) as initials
     FROM users u
     WHERE $whereClause
     ORDER BY u.loyalty_points DESC
     LIMIT $limit OFFSET $offset",  // Direct integer values, no placeholders
    $queryParams  // Only the WHERE clause parameters
)->find() ?: [];

// RECENT INTERACTIONS
$recentInteractions = $db->query(
    "SELECT 
        'booking' as type,
        u.id as user_id,
        u.full_name as user_name,
        CONCAT(LEFT(u.first_name, 1), LEFT(u.last_name, 1)) as initials,
        CONCAT('Made a new booking · ', b.room_name, ', ', DATE_FORMAT(b.check_in, '%b %e')) as description,
        b.created_at as created_at,
        u.member_tier
     FROM bookings b
     JOIN users u ON b.user_id = u.id
     WHERE b.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
     
     UNION ALL
     
     SELECT 
        'feedback' as type,
        u.id as user_id,
        u.full_name as user_name,
        CONCAT(LEFT(u.first_name, 1), LEFT(u.last_name, 1)) as initials,
        CONCAT('Sent feedback · ', r.rating, ' stars') as description,
        r.created_at as created_at,
        u.member_tier
     FROM reviews r
     JOIN users u ON r.user_id = u.id
     WHERE r.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
     
     UNION ALL
     
     SELECT 
        'redemption' as type,
        u.id as user_id,
        u.full_name as user_name,
        CONCAT(LEFT(u.first_name, 1), LEFT(u.last_name, 1)) as initials,
        CONCAT('Redeemed ', rd.reward_name) as description,
        rd.created_at as created_at,
        u.member_tier
     FROM redemptions rd
     JOIN users u ON rd.user_id = u.id
     WHERE rd.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
     
     UNION ALL
     
     SELECT 
        'notification' as type,
        u.id as user_id,
        u.full_name as user_name,
        CONCAT(LEFT(u.first_name, 1), LEFT(u.last_name, 1)) as initials,
        CONCAT('Received: ', n.title) as description,
        n.created_at as created_at,
        u.member_tier
     FROM notifications n
     JOIN users u ON n.user_id = u.id
     WHERE n.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
     
     ORDER BY created_at DESC
     LIMIT 10",
    []
)->find() ?: [];

// Format time ago for interactions
foreach ($recentInteractions as &$interaction) {
    $created = new DateTime($interaction['created_at']);
    $now = new DateTime();
    $diff = $now->diff($created);

    if ($diff->days > 0) {
        $interaction['time_ago'] = $diff->days == 1 ? 'yesterday' : $diff->days . ' days ago';
    } elseif ($diff->h > 0) {
        $interaction['time_ago'] = $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
    } elseif ($diff->i > 0) {
        $interaction['time_ago'] = $diff->i . ' minute' . ($diff->i > 1 ? 's' : '') . ' ago';
    } else {
        $interaction['time_ago'] = 'just now';
    }
}

// UPCOMING CELEBRATIONS
$celebrations = $db->query(
    "SELECT 
        id,
        full_name,
        birthday,
        anniversary,
        CONCAT(LEFT(first_name, 1), LEFT(last_name, 1)) as initials,
        member_tier,
        email,
        phone
     FROM users
     WHERE role = 'customer'
        AND (
            (birthday IS NOT NULL AND DATE_FORMAT(birthday, '%m-%d') BETWEEN DATE_FORMAT(NOW(), '%m-%d') AND DATE_FORMAT(DATE_ADD(NOW(), INTERVAL 14 DAY), '%m-%d'))
            OR (anniversary IS NOT NULL AND DATE_FORMAT(anniversary, '%m-%d') BETWEEN DATE_FORMAT(NOW(), '%m-%d') AND DATE_FORMAT(DATE_ADD(NOW(), INTERVAL 14 DAY), '%m-%d'))
        )
     ORDER BY 
        CASE 
            WHEN birthday IS NOT NULL THEN DATE_FORMAT(birthday, '%m-%d')
            ELSE DATE_FORMAT(anniversary, '%m-%d')
        END ASC
     LIMIT 10",
    []
)->find() ?: [];

// Format celebrations
foreach ($celebrations as &$celebration) {
    if ($celebration['birthday']) {
        $date = new DateTime($celebration['birthday']);
        $celebration['type'] = 'Birthday';
        $celebration['date'] = $date->format('M j');
        $celebration['days'] = daysUntil($date->format('m-d'));
    } elseif ($celebration['anniversary']) {
        $date = new DateTime($celebration['anniversary']);
        $celebration['type'] = 'Anniversary';
        $celebration['date'] = $date->format('M j');
        $celebration['days'] = daysUntil($date->format('m-d'));
    }
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

// Helper function for days until celebration
function daysUntil($monthDay)
{
    $today = new DateTime();
    $celebration = DateTime::createFromFormat('m-d', $monthDay);
    $celebration->setDate($today->format('Y'), $celebration->format('m'), $celebration->format('d'));

    if ($celebration < $today) {
        $celebration->modify('+1 year');
    }

    $diff = $today->diff($celebration);
    return $diff->days;
}

// Store data for view
$viewData = [
    'admin' => $admin,
    'initials' => $initials,
    'stats' => $stats,
    'guests' => $guests,
    'recentInteractions' => $recentInteractions,
    'celebrations' => $celebrations,
    'currentPage' => $page,
    'totalPages' => $totalPages,
    'totalGuests' => $totalGuests,
    'today' => date('F j, Y'),
    'tierFilter' => $tierFilter,
    'stayFilter' => $stayFilter,
    'searchFilter' => $searchFilter
];

// Extract variables for view
extract($viewData);
?>