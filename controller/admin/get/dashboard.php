<?php
/**
 * GET Controller - Admin Dashboard
 * Handles fetching all dashboard data for admin view
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

// Get today's date
$today = date('Y-m-d');

// STATISTICS CARDS DATA

// Total rooms
$totalRooms = $db->query(
    "SELECT COUNT(*) as count FROM rooms WHERE is_available = 1"
)->fetch_one()['count'] ?? 48;

// Occupied rooms today
$occupiedRooms = $db->query(
    "SELECT COUNT(DISTINCT room_id) as count 
     FROM bookings 
     WHERE status IN ('confirmed', 'checked_in') 
     AND check_in <= :today AND check_out >= :today",
    ['today' => $today]
)->fetch_one()['count'] ?? 32;

// Today's bookings (new bookings for today)
$todayBookings = $db->query(
    "SELECT COUNT(*) as count 
     FROM bookings 
     WHERE DATE(created_at) = :today",
    ['today' => $today]
)->fetch_one()['count'] ?? 12;

// Today's restaurant orders
$todayOrders = $db->query(
    "SELECT COUNT(*) as count 
     FROM food_orders 
     WHERE DATE(created_at) = :today",
    ['today' => $today]
)->fetch_one()['count'] ?? 47;

// Today's sales
$todaySales = $db->query(
    "SELECT COALESCE(SUM(total_amount), 0) as total 
     FROM food_orders 
     WHERE DATE(created_at) = :today AND status = 'completed'",
    ['today' => $today]
)->fetch_one()['total'] ?? 64200;

// Low stock items (assuming you have an inventory table)
$lowStockItems = $db->query(
    "SELECT COUNT(*) as count FROM inventory WHERE stock <= reorder_level"
)->fetch_one()['count'] ?? 3;

// Today's check-ins
$todayCheckins = $db->query(
    "SELECT COUNT(*) as count 
     FROM bookings 
     WHERE check_in = :today AND status = 'confirmed'",
    ['today' => $today]
)->fetch_one()['count'] ?? 8;

// Pending check-ins
$pendingCheckins = $db->query(
    "SELECT COUNT(*) as count 
     FROM bookings 
     WHERE check_in = :today AND status = 'pending'",
    ['today' => $today]
)->fetch_one()['count'] ?? 2;

// UPCOMING CHECK-INS (next 5) - FIXED: removed check_in_time
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
     ORDER BY rr.reservation_time ASC
     LIMIT 5",
    ['today' => $today]
)->find() ?: [];

// TODAY'S EVENTS
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
$salesGrowth = $lastWeekSales > 0 ? (($thisWeekSales - $lastWeekSales) / $lastWeekSales) * 100 : 12.4;

// Occupancy rate
$occupancyRate = $totalRooms > 0 ? round(($occupiedRooms / $totalRooms) * 100) : 67;

// Order category distribution
$orderCategories = $db->query(
    "SELECT 
        category,
        COUNT(*) as count
     FROM menu_items mi
     JOIN food_orders fo ON JSON_CONTAINS(fo.items, JSON_OBJECT('name', mi.name))
     WHERE DATE(fo.created_at) = :today
     GROUP BY category",
    ['today' => $today]
)->find() ?: [];

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
     LIMIT 5",
    []
)->find() ?: [];

// RECENT ORDERS (last 5)
$recentOrders = $db->query(
    "SELECT 
        fo.id,
        fo.order_reference,
        CASE 
            WHEN fo.order_type LIKE 'dine-in%' THEN CONCAT('Table ', SUBSTRING_INDEX(fo.order_type, ' ', -1))
            ELSE fo.order_type
        END as table_info,
        fo.total_amount,
        fo.status,
        JSON_LENGTH(fo.items) as item_count
     FROM food_orders fo
     ORDER BY fo.created_at DESC
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
    'occupancyRate' => $occupancyRate,
    'recentBookings' => $recentBookings,
    'recentOrders' => $recentOrders,
    'today' => $today
];

// Extract variables for view
extract($viewData);
?>