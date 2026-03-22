<?php
/**
 * GET Controller - Admin Marketing & Promotions
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

// Get user role
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
$statusFilter = isset($_GET['status']) ? $_GET['status'] : 'all';
$typeFilter = isset($_GET['type']) ? $_GET['type'] : 'all';
$searchFilter = isset($_GET['search']) ? $_GET['search'] : '';

// Build campaigns query
$query = "SELECT * FROM campaigns WHERE 1=1";
$params = [];

if ($statusFilter !== 'all') {
    $query .= " AND status = :status";
    $params['status'] = $statusFilter;
}

if ($typeFilter !== 'all') {
    $query .= " AND campaign_type = :type";
    $params['type'] = $typeFilter;
}

if ($searchFilter) {
    $query .= " AND (campaign_name LIKE :search OR description LIKE :search)";
    $params['search'] = "%$searchFilter%";
}

$query .= " ORDER BY 
    CASE 
        WHEN status = 'active' THEN 1
        WHEN status = 'scheduled' THEN 2
        WHEN status = 'draft' THEN 3
        WHEN status = 'ended' THEN 4
        ELSE 5
    END,
    start_date DESC";

$campaigns = $db->query($query, $params)->find() ?: [];

// Get REAL revenue from bookings (completed and paid)
$realRevenue = $db->query(
    "SELECT COALESCE(SUM(total_amount), 0) as total_revenue 
     FROM bookings 
     WHERE status = 'completed' AND payment_status = 'paid'",
    []
)->fetch_one();

$totalRevenue = floatval($realRevenue['total_revenue'] ?? 0);

// Get revenue from last period (previous month) for comparison
$lastMonthRevenue = $db->query(
    "SELECT COALESCE(SUM(total_amount), 0) as total_revenue 
     FROM bookings 
     WHERE status = 'completed' AND payment_status = 'paid' 
     AND created_at BETWEEN DATE_SUB(CURDATE(), INTERVAL 2 MONTH) AND DATE_SUB(CURDATE(), INTERVAL 1 MONTH)",
    []
)->fetch_one();

$lastMonthRevenueAmount = floatval($lastMonthRevenue['total_revenue'] ?? 0);

$revenueChange = 0;
if ($lastMonthRevenueAmount > 0) {
    $revenueChange = round((($totalRevenue - $lastMonthRevenueAmount) / $lastMonthRevenueAmount) * 100, 2);
}

// Get total redemptions from campaign_redemptions table
$totalRedemptions = $db->query(
    "SELECT COUNT(*) as total FROM campaign_redemptions",
    []
)->fetch_one();
$redemptionsCount = intval($totalRedemptions['total'] ?? 0);

// Get total campaign budget (sum of all campaign budgets)
$totalBudget = $db->query(
    "SELECT COALESCE(SUM(budget), 0) as total_budget FROM campaigns WHERE status != 'draft'",
    []
)->fetch_one();
$totalBudgetAmount = floatval($totalBudget['total_budget'] ?? 0);

// Calculate ROI based on actual revenue vs budget
$roi = 0;
if ($totalBudgetAmount > 0) {
    $roi = round((($totalRevenue - $totalBudgetAmount) / $totalBudgetAmount) * 100, 2);
}

// Get active campaigns count
$activeCampaigns = count(array_filter($campaigns, function ($c) {
    return $c['status'] === 'active';
}));

// Calculate conversion rate (redemptions vs target)
$totalTarget = array_sum(array_map(function ($c) {
    return $c['redemption_limit'] ?: 0;
}, $campaigns));
$conversionRate = $totalTarget > 0 ? round(($redemptionsCount / $totalTarget) * 100, 2) : 0;

$stats = [
    'active_campaigns' => $activeCampaigns,
    'total_revenue' => $totalRevenue,
    'total_redemptions' => $redemptionsCount,
    'conversion_rate' => $conversionRate,
    'roi' => $roi,
    'revenue_change' => $revenueChange,
    'total_campaigns' => count($campaigns),
    'scheduled_campaigns' => count(array_filter($campaigns, function ($c) {
        return $c['status'] === 'scheduled';
    })),
    'ended_campaigns' => count(array_filter($campaigns, function ($c) {
        return $c['status'] === 'ended';
    })),
    'draft_campaigns' => count(array_filter($campaigns, function ($c) {
        return $c['status'] === 'draft';
    })),
    'total_budget' => $totalBudgetAmount,
    'last_month_revenue' => $lastMonthRevenueAmount
];

// Get active promo codes
$promoCodes = $db->query(
    "SELECT pc.*, c.campaign_name 
     FROM promo_codes pc
     LEFT JOIN campaigns c ON pc.campaign_id = c.id
     WHERE pc.is_active = 1 
     AND pc.valid_to >= NOW()
     ORDER BY pc.used_count DESC
     LIMIT 10",
    []
)->find() ?: [];

// Get email templates
$emailTemplates = $db->query(
    "SELECT * FROM email_templates WHERE is_active = 1 ORDER BY created_at DESC",
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
    'campaigns' => $campaigns,
    'stats' => $stats,
    'promoCodes' => $promoCodes,
    'emailTemplates' => $emailTemplates,
    'statusFilter' => $statusFilter,
    'typeFilter' => $typeFilter,
    'searchFilter' => $searchFilter,
    'unread_count' => $unread_count,
    'today' => date('F j, Y')
];

extract($viewData);
?>