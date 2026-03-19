<?php
/**
 * GET Controller - Admin Loyalty & Rewards
 * Handles fetching all loyalty program data
 */

session_start();
require_once __DIR__ . '/../../../Class/Database.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || !$_SESSION['logged_in']) {
    header('Location: ../../view/auth/login.php');
    exit();
}

// // Check if user has admin role
// if (($_SESSION['user_role'] ?? 'customer') !== 'admin') {
//     header('Location: ../../view/customer_portal/dashboard.php');
//     exit();
// }

$config = require __DIR__ . '/../../../config/config.php';
$db = new Database($config['database']);

// Get admin user data
$admin = $db->query(
    "SELECT id, full_name, first_name, last_name, email, role, avatar
     FROM users WHERE id = :id",
    ['id' => $_SESSION['user_id']]
)->fetch_one();

// STATISTICS CARDS
$stats = $db->query(
    "SELECT 
        COUNT(*) as total_members,
        SUM(CASE WHEN last_login >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) as active_this_month,
        SUM(loyalty_points) as total_points,
        COALESCE((SELECT COUNT(*) FROM redemptions), 0) as total_redemptions,
        ROUND(
            (SELECT COUNT(*) FROM redemptions WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)) * 100.0 / 
            NULLIF((SELECT COUNT(*) FROM users WHERE loyalty_points > 0), 0), 1
        ) as conversion_rate
     FROM users WHERE role = 'customer'",
    []
)->fetch_one();

// Ensure stats have default values
if (!$stats) {
    $stats = [
        'total_members' => 0,
        'active_this_month' => 0,
        'total_points' => 0,
        'total_redemptions' => 0,
        'conversion_rate' => 0
    ];
}

// TIER DISTRIBUTION
$tierDistribution = $db->query(
    "SELECT 
        member_tier,
        COUNT(*) as count,
        MIN(loyalty_points) as min_points,
        MAX(loyalty_points) as max_points
     FROM users
     WHERE role = 'customer'
     GROUP BY member_tier
     ORDER BY FIELD(member_tier, 'platinum', 'gold', 'silver', 'bronze')",
    []
)->find() ?: [];

// Format tier data for display with defaults
$tiers = [
    'bronze' => ['count' => 0, 'min' => 0, 'max' => 499, 'color' => 'slate', 'bg' => 'bg-slate-50', 'border' => 'border-slate-200', 'badge' => 'bg-slate-200 text-slate-700'],
    'silver' => ['count' => 0, 'min' => 500, 'max' => 999, 'color' => 'slate', 'bg' => 'bg-slate-50', 'border' => 'border-slate-200', 'badge' => 'bg-slate-200 text-slate-700'],
    'gold' => ['count' => 0, 'min' => 1000, 'max' => 1999, 'color' => 'amber', 'bg' => 'bg-amber-50', 'border' => 'border-amber-200', 'badge' => 'bg-amber-200 text-amber-800'],
    'platinum' => ['count' => 0, 'min' => 2000, 'max' => 999999, 'color' => 'purple', 'bg' => 'bg-purple-50', 'border' => 'border-purple-200', 'badge' => 'bg-purple-200 text-purple-800']
];

foreach ($tierDistribution as $tier) {
    $tierName = strtolower($tier['member_tier']);
    if (isset($tiers[$tierName])) {
        $tiers[$tierName]['count'] = $tier['count'];
    }
}

// TOP MEMBERS with pagination - FIXED LIMIT SYNTAX
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$totalMembers = $stats['total_members'];
$totalPages = ceil($totalMembers / $limit);

// FIXED: Use direct integer values in query instead of placeholders for LIMIT
$topMembers = $db->query(
    "SELECT 
        u.id,
        u.full_name,
        u.email,
        u.loyalty_points,
        u.member_tier,
        u.last_login,
        COALESCE((
            SELECT SUM(points_cost) FROM redemptions WHERE user_id = u.id
        ), 0) as lifetime_points,
        DATE_FORMAT(u.last_login, '%b %d, %Y') as last_activity
     FROM users u
     WHERE u.role = 'customer'
     ORDER BY u.loyalty_points DESC
     LIMIT $limit OFFSET $offset",
    [] // Empty array since we're not using placeholders
)->find() ?: [];

// AVAILABLE REWARDS
$rewards = $db->query(
    "SELECT 
        id,
        reward_name,
        points_cost,
        description,
        category,
        is_active,
        stock_limit,
        times_redeemed,
        created_at
     FROM rewards
     WHERE is_active = 1
     ORDER BY points_cost ASC",
    []
)->find() ?: [];

// ALL REWARDS (for management)
$allRewards = $db->query(
    "SELECT 
        id,
        reward_name,
        points_cost,
        description,
        category,
        is_active,
        stock_limit,
        times_redeemed,
        created_at
     FROM rewards
     ORDER BY is_active DESC, points_cost ASC",
    []
)->find() ?: [];

// RECENT REDEMPTIONS
$recentRedemptions = $db->query(
    "SELECT 
        r.id,
        r.reward_name,
        r.points_cost,
        r.created_at,
        u.full_name as user_name,
        u.id as user_id,
        u.member_tier,
        DATE_FORMAT(r.created_at, '%b %d, %Y') as formatted_date
     FROM redemptions r
     JOIN users u ON r.user_id = u.id
     ORDER BY r.created_at DESC
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
    'stats' => $stats,
    'tiers' => $tiers,
    'topMembers' => $topMembers,
    'rewards' => $rewards,
    'allRewards' => $allRewards,
    'recentRedemptions' => $recentRedemptions,
    'currentPage' => $page,
    'totalPages' => $totalPages,
    'totalMembers' => $totalMembers,
    
    'today' => date('F j, Y')
];

// Extract variables for view
extract($viewData);
?>