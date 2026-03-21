<?php
/**
 * GET Controller - Admin Sales Reports
 * Handles fetching all sales data for analytics and reports
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

// Get filter parameters
$period = isset($_GET['period']) ? $_GET['period'] : 'month';
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-30 days'));
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

// If period is set, calculate date range
switch ($period) {
    case 'today':
        $start_date = date('Y-m-d');
        $end_date = date('Y-m-d');
        break;
    case 'yesterday':
        $start_date = date('Y-m-d', strtotime('-1 day'));
        $end_date = date('Y-m-d', strtotime('-1 day'));
        break;
    case 'week':
        $start_date = date('Y-m-d', strtotime('-7 days'));
        $end_date = date('Y-m-d');
        break;
    case 'month':
        $start_date = date('Y-m-d', strtotime('-30 days'));
        $end_date = date('Y-m-d');
        break;
    case 'year':
        $start_date = date('Y-m-d', strtotime('-365 days'));
        $end_date = date('Y-m-d');
        break;
}

// ========== TOP STATS CARDS ==========

// Total Revenue (Hotel Bookings + Restaurant Down Payments)
$totalRevenue = $db->query(
    "SELECT 
        (SELECT COALESCE(SUM(total_amount), 0) FROM bookings 
         WHERE DATE(created_at) BETWEEN :start_date AND :end_date) +
        (SELECT COALESCE(SUM(down_payment), 0) FROM restaurant_reservations 
         WHERE DATE(created_at) BETWEEN :start_date AND :end_date) as total",
    ['start_date' => $start_date, 'end_date' => $end_date]
)->fetch_one()['total'] ?? 0;

// Total Orders (Bookings + Reservations)
$totalOrders = $db->query(
    "SELECT 
        (SELECT COUNT(*) FROM bookings 
         WHERE DATE(created_at) BETWEEN :start_date AND :end_date) +
        (SELECT COUNT(*) FROM restaurant_reservations 
         WHERE DATE(created_at) BETWEEN :start_date AND :end_date) as total",
    ['start_date' => $start_date, 'end_date' => $end_date]
)->fetch_one()['total'] ?? 0;

// Average Order Value
$avgOrderValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;

// Hotel Revenue
$hotelRevenue = $db->query(
    "SELECT COALESCE(SUM(total_amount), 0) as total FROM bookings 
     WHERE DATE(created_at) BETWEEN :start_date AND :end_date",
    ['start_date' => $start_date, 'end_date' => $end_date]
)->fetch_one()['total'] ?? 0;

// Restaurant Revenue
$restaurantRevenue = $db->query(
    "SELECT COALESCE(SUM(down_payment), 0) as total FROM restaurant_reservations 
     WHERE DATE(created_at) BETWEEN :start_date AND :end_date",
    ['start_date' => $start_date, 'end_date' => $end_date]
)->fetch_one()['total'] ?? 0;

// Get previous period for comparison
$prev_start = date('Y-m-d', strtotime($start_date . ' -' . (strtotime($end_date) - strtotime($start_date)) / (60 * 60 * 24) . ' days'));
$prev_end = date('Y-m-d', strtotime($start_date . ' -1 day'));

$prevRevenue = $db->query(
    "SELECT 
        (SELECT COALESCE(SUM(total_amount), 0) FROM bookings 
         WHERE DATE(created_at) BETWEEN :start_date AND :end_date) +
        (SELECT COALESCE(SUM(down_payment), 0) FROM restaurant_reservations 
         WHERE DATE(created_at) BETWEEN :start_date AND :end_date) as total",
    ['start_date' => $prev_start, 'end_date' => $prev_end]
)->fetch_one()['total'] ?? 0;

$revenueGrowth = $prevRevenue > 0 ? round((($totalRevenue - $prevRevenue) / $prevRevenue) * 100, 1) : 0;

// ========== DAILY SALES TREND ==========

$dailySales = $db->query(
    "SELECT 
        DATE(created_at) as date,
        'hotel' as source,
        COUNT(*) as order_count,
        COALESCE(SUM(total_amount), 0) as revenue
     FROM bookings 
     WHERE DATE(created_at) BETWEEN :start_date AND :end_date
     GROUP BY DATE(created_at)
     
     UNION ALL
     
     SELECT 
        DATE(created_at) as date,
        'restaurant' as source,
        COUNT(*) as order_count,
        COALESCE(SUM(down_payment), 0) as revenue
     FROM restaurant_reservations 
     WHERE DATE(created_at) BETWEEN :start_date AND :end_date
     GROUP BY DATE(created_at)
     
     ORDER BY date ASC",
    ['start_date' => $start_date, 'end_date' => $end_date]
)->find() ?: [];

// Process daily sales data
$dailyLabels = [];
$dailyHotelRevenue = [];
$dailyRestaurantRevenue = [];
$dailyTotalRevenue = [];

$current = strtotime($start_date);
$end = strtotime($end_date);

// Index the data by date
$dailyIndexed = [];
foreach ($dailySales as $item) {
    if (!isset($dailyIndexed[$item['date']])) {
        $dailyIndexed[$item['date']] = ['hotel' => 0, 'restaurant' => 0];
    }
    $dailyIndexed[$item['date']][$item['source']] = (float) $item['revenue'];
}

while ($current <= $end) {
    $date = date('Y-m-d', $current);
    $dailyLabels[] = date('M d', $current);

    $hotelRev = isset($dailyIndexed[$date]['hotel']) ? $dailyIndexed[$date]['hotel'] : 0;
    $restRev = isset($dailyIndexed[$date]['restaurant']) ? $dailyIndexed[$date]['restaurant'] : 0;

    $dailyHotelRevenue[] = $hotelRev;
    $dailyRestaurantRevenue[] = $restRev;
    $dailyTotalRevenue[] = $hotelRev + $restRev;

    $current = strtotime('+1 day', $current);
}

// ========== REVENUE BY CATEGORY ==========

$categoryData = [
    ['name' => 'Room Bookings', 'revenue' => $hotelRevenue, 'color' => 'blue', 'percentage' => $totalRevenue > 0 ? round(($hotelRevenue / $totalRevenue) * 100, 1) : 0],
    ['name' => 'Restaurant', 'revenue' => $restaurantRevenue, 'color' => 'green', 'percentage' => $totalRevenue > 0 ? round(($restaurantRevenue / $totalRevenue) * 100, 1) : 0],
];

// You can add more categories if you have events or other services
// For now, we'll just have two main categories

// ========== REVENUE BY PAYMENT METHOD ==========

$paymentMethodData = $db->query(
    "SELECT 
        payment_method,
        COUNT(*) as count,
        COALESCE(SUM(amount), 0) as revenue
     FROM payments 
     WHERE DATE(created_at) BETWEEN :start_date AND :end_date
     AND payment_status = 'completed' 
     AND approval_status = 'approved'
     GROUP BY payment_method
     ORDER BY revenue DESC",
    ['start_date' => $start_date, 'end_date' => $end_date]
)->find() ?: [];

// If no payment data, provide default
if (empty($paymentMethodData)) {
    $paymentMethodData = [
        ['payment_method' => 'GCash', 'count' => 0, 'revenue' => 0],
        ['payment_method' => 'Credit card', 'count' => 0, 'revenue' => 0],
        ['payment_method' => 'Cash', 'count' => 0, 'revenue' => 0],
        ['payment_method' => 'Bank transfer', 'count' => 0, 'revenue' => 0]
    ];
}

// ========== TOP SELLING ITEMS ==========

// Hotel rooms
$topRooms = $db->query(
    "SELECT 
        room_name as item_name,
        'Hotel' as category,
        COUNT(*) as units_sold,
        COALESCE(SUM(total_amount), 0) as revenue
     FROM bookings 
     WHERE DATE(created_at) BETWEEN :start_date AND :end_date
     AND room_name IS NOT NULL
     GROUP BY room_name
     ORDER BY revenue DESC
     LIMIT 5",
    ['start_date' => $start_date, 'end_date' => $end_date]
)->find() ?: [];

// Restaurant items would come from food_orders table
// For now, we'll use placeholder data since food_orders doesn't have items breakdown easily
$topItems = $topRooms;

// ========== TODAY'S SUMMARY ==========

$today = date('Y-m-d');

$todayRevenue = $db->query(
    "SELECT 
        (SELECT COALESCE(SUM(total_amount), 0) FROM bookings WHERE DATE(created_at) = :today) +
        (SELECT COALESCE(SUM(down_payment), 0) FROM restaurant_reservations WHERE DATE(created_at) = :today) as total",
    ['today' => $today]
)->fetch_one()['total'] ?? 0;

$todayOrders = $db->query(
    "SELECT 
        (SELECT COUNT(*) FROM bookings WHERE DATE(created_at) = :today) +
        (SELECT COUNT(*) FROM restaurant_reservations WHERE DATE(created_at) = :today) as total",
    ['today' => $today]
)->fetch_one()['total'] ?? 0;

$todayOccupancy = $db->query(
    "SELECT COALESCE(SUM(nights), 0) as occupied_nights FROM bookings 
     WHERE DATE(check_in) = :today AND status != 'cancelled'",
    ['today' => $today]
)->fetch_one()['occupied_nights'] ?? 0;

$totalRooms = $db->query("SELECT COUNT(*) as count FROM rooms")->fetch_one()['count'] ?? 10;
$todayOccupancyRate = $totalRooms > 0 ? round(($todayOccupancy / $totalRooms) * 100, 1) : 0;

// Projected month end (simple projection based on average daily revenue)
$daysInMonth = date('t');
$currentDay = date('j');
$avgDailyRevenue = $currentDay > 0 ? $todayRevenue / $currentDay : 0;
$projectedRevenue = $avgDailyRevenue * $daysInMonth;

// Store data for view
$viewData = [
    'period' => $period,
    'start_date' => $start_date,
    'end_date' => $end_date,
    'totalRevenue' => $totalRevenue,
    'totalOrders' => $totalOrders,
    'avgOrderValue' => round($avgOrderValue, 2),
    'hotelRevenue' => $hotelRevenue,
    'restaurantRevenue' => $restaurantRevenue,
    'revenueGrowth' => $revenueGrowth,
    'dailyLabels' => $dailyLabels,
    'dailyTotalRevenue' => $dailyTotalRevenue,
    'dailyHotelRevenue' => $dailyHotelRevenue,
    'dailyRestaurantRevenue' => $dailyRestaurantRevenue,
    'categoryData' => $categoryData,
    'paymentMethodData' => $paymentMethodData,
    'topItems' => $topItems,
    'todayRevenue' => $todayRevenue,
    'todayOrders' => $todayOrders,
    'todayOccupancyRate' => $todayOccupancyRate,
    'projectedRevenue' => $projectedRevenue
];

// Extract variables for view
extract($viewData);
?>