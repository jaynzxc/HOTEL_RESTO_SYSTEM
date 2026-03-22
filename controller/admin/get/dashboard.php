<?php
/**
 * GET Controller - Admin Dashboard
 * Handles fetching all dashboard data for admin view
 */

session_start();
require_once __DIR__ . '/../../../Class/Database.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || !$_SESSION['logged_in']) {
    header('Location: ../../../src/login-register/login_form.php');
    exit();
}

$config = require __DIR__ . '/../../../config/config.php';
$db = new Database($config['database']);

// Get admin user data
$admin = $db->query(
    "SELECT id, full_name, first_name, last_name, email, role, avatar
     FROM users WHERE id = :id",
    ['id' => $_SESSION['user_id']]
)->fetch_one();

// Get today's date
$today = date('Y-m-d');

// STATISTICS CARDS DATA

// Total rooms
$totalRooms = $db->query(
    "SELECT COUNT(*) as count FROM rooms WHERE is_available = 1"
)->fetch_one()['count'] ?? 48;

// Occupied rooms today (rooms with active bookings)
$occupiedRooms = $db->query(
    "SELECT COUNT(DISTINCT room_id) as count 
     FROM bookings 
     WHERE status IN ('confirmed', 'checked_in') 
     AND check_in <= :today AND check_out >= :today",
    ['today' => $today]
)->fetch_one()['count'] ?? 0;

// Today's bookings (new bookings created today)
$todayBookings = $db->query(
    "SELECT COUNT(*) as count 
     FROM bookings 
     WHERE DATE(created_at) = :today",
    ['today' => $today]
)->fetch_one()['count'] ?? 0;

// Today's restaurant orders
$todayOrders = $db->query(
    "SELECT COUNT(*) as count 
     FROM food_orders 
     WHERE DATE(created_at) = :today",
    ['today' => $today]
)->fetch_one()['count'] ?? 0;

// Today's sales from completed orders
$todaySales = $db->query(
    "SELECT COALESCE(SUM(total_amount), 0) as total 
     FROM food_orders 
     WHERE DATE(created_at) = :today AND status = 'completed'",
    ['today' => $today]
)->fetch_one()['total'] ?? 0;

// Low stock items
$lowStockItems = $db->query(
    "SELECT COUNT(*) as count FROM inventory WHERE stock <= reorder_level"
)->fetch_one()['count'] ?? 0;

// Today's check-ins (bookings starting today with confirmed status)
$todayCheckins = $db->query(
    "SELECT COUNT(*) as count 
     FROM bookings 
     WHERE check_in = :today AND status = 'confirmed'",
    ['today' => $today]
)->fetch_one()['count'] ?? 0;

// Pending check-ins (bookings starting today with pending status)
$pendingCheckins = $db->query(
    "SELECT COUNT(*) as count 
     FROM bookings 
     WHERE check_in = :today AND status = 'pending'",
    ['today' => $today]
)->fetch_one()['count'] ?? 0;

// UPCOMING CHECK-INS (next 5)
$upcomingCheckins = $db->query(
    "SELECT 
        b.id,
        b.booking_reference,
        CONCAT(b.guest_first_name, ' ', b.guest_last_name) as guest_name,
        b.room_name,
        b.check_in,
        b.status
     FROM bookings b
     WHERE b.check_in >= :today 
        AND b.status IN ('confirmed', 'pending')
     ORDER BY b.check_in ASC
     LIMIT 5",
    ['today' => $today]
)->find() ?: [];

// UPCOMING CHECK-OUTS (next 5)
$upcomingCheckouts = $db->query(
    "SELECT 
        b.id,
        b.booking_reference,
        CONCAT(b.guest_first_name, ' ', b.guest_last_name) as guest_name,
        b.room_name,
        b.check_out,
        b.status
     FROM bookings b
     WHERE b.check_out >= :today 
        AND b.status IN ('confirmed', 'checked_in')
     ORDER BY b.check_out ASC
     LIMIT 5",
    ['today' => $today]
)->find() ?: [];

// TODAY'S TABLE RESERVATIONS
$tableReservations = $db->query(
    "SELECT 
        rr.id,
        rr.reservation_reference,
        CONCAT(rr.guest_first_name, ' ', rr.guest_last_name) as guest_name,
        rr.table_number,
        rr.guests,
        rr.reservation_time,
        rr.status
     FROM restaurant_reservations rr
     WHERE rr.reservation_date = :today
        AND rr.status IN ('confirmed', 'pending')
     ORDER BY rr.reservation_time ASC
     LIMIT 5",
    ['today' => $today]
)->find() ?: [];

// TODAY'S EVENTS - FIXED: added location column
$todayEvents = $db->query(
    "SELECT id, event_name, event_time, location, description
     FROM events 
     WHERE event_date = :today
     ORDER BY event_time ASC
     LIMIT 5",
    ['today' => $today]
)->find() ?: [];

// WEEKLY SALES DATA (last 7 days)
$weeklySales = $db->query(
    "SELECT 
        DATE(created_at) as date,
        COALESCE(SUM(total_amount), 0) as total
     FROM food_orders 
     WHERE created_at >= DATE_SUB(:today, INTERVAL 6 DAY)
        AND status = 'completed'
     GROUP BY DATE(created_at)
     ORDER BY date ASC",
    ['today' => $today]
)->find() ?: [];

// Calculate week-over-week growth
$lastWeekSales = $db->query(
    "SELECT COALESCE(SUM(total_amount), 0) as total
     FROM food_orders 
     WHERE created_at >= DATE_SUB(:today, INTERVAL 13 DAY)
        AND created_at < DATE_SUB(:today, INTERVAL 6 DAY)
        AND status = 'completed'",
    ['today' => $today]
)->fetch_one()['total'] ?? 0;

$thisWeekSales = array_sum(array_column($weeklySales, 'total'));
$salesGrowth = $lastWeekSales > 0 ? (($thisWeekSales - $lastWeekSales) / $lastWeekSales) * 100 : 0;

// Occupancy rate
$occupancyRate = $totalRooms > 0 ? round(($occupiedRooms / $totalRooms) * 100) : 0;

// Order category distribution - FIXED: More reliable approach
$orderCategories = [];
$recentOrdersData = $db->query(
    "SELECT items FROM food_orders WHERE DATE(created_at) = :today LIMIT 50",
    ['today' => $today]
)->find() ?: [];

// Process order categories from JSON data
$categoryCounts = ['appetizers' => 0, 'mains' => 0, 'desserts' => 0, 'beverages' => 0];

foreach ($recentOrdersData as $order) {
    if ($order && isset($order['items'])) {
        $items = is_string($order['items']) ? json_decode($order['items'], true) : $order['items'];
        if (is_array($items)) {
            foreach ($items as $item) {
                if (isset($item['category'])) {
                    $categoryCounts[$item['category']] = ($categoryCounts[$item['category']] ?? 0) + 1;
                }
            }
        }
    }
}

foreach ($categoryCounts as $category => $count) {
    if ($count > 0) {
        $orderCategories[] = ['category' => $category, 'count' => $count];
    }
}

// RECENT BOOKINGS (last 5)
$recentBookings = $db->query(
    "SELECT 
        b.id,
        CONCAT(b.guest_first_name, ' ', b.guest_last_name) as guest_name,
        b.room_name,
        b.check_in,
        b.status
     FROM bookings b
     ORDER BY b.created_at DESC
     LIMIT 5"
)->find() ?: [];

// RECENT ORDERS (last 5) - FIXED: proper table_info handling
$recentOrders = $db->query(
    "SELECT 
        fo.id,
        fo.order_reference,
        fo.order_type,
        fo.total_amount,
        fo.status,
        JSON_LENGTH(fo.items) as item_count
     FROM food_orders fo
     ORDER BY fo.created_at DESC
     LIMIT 5"
)->find() ?: [];

// Format recent orders with proper table info
foreach ($recentOrders as &$order) {
    if (strpos($order['order_type'], 'dine-in') !== false) {
        // Extract table number from order_type if needed
        $order['table_info'] = 'Dine-in';
    } else {
        $order['table_info'] = ucfirst($order['order_type']);
    }
}

// Get admin initials
$initials = 'AD';
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
    'totalRooms' => $totalRooms,
    'occupiedRooms' => $occupiedRooms,
    'todayBookings' => $todayBookings,
    'todayOrders' => $todayOrders,
    'todaySales' => $todaySales,
    'lowStockItems' => $lowStockItems,
    'todayCheckins' => $todayCheckins,
    'pendingCheckins' => $pendingCheckins,
    'upcomingCheckins' => $upcomingCheckins,
    'upcomingCheckouts' => $upcomingCheckouts,
    'tableReservations' => $tableReservations,
    'todayEvents' => $todayEvents,
    'weeklySales' => $weeklySales,
    'salesGrowth' => $salesGrowth,
    'thisWeekSales' => $thisWeekSales,
    'occupancyRate' => $occupancyRate,
    'orderCategories' => $orderCategories,
    'recentBookings' => $recentBookings,
    'recentOrders' => $recentOrders,
    'today' => $today
];

// Extract variables for view
extract($viewData);
?>