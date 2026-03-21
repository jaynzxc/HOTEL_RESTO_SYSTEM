<?php
/**
 * GET Controller - Admin Booking Reports Analytics
 * Handles fetching all booking AND reservation data for analytics and reports
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
    case 'week':
        $start_date = date('Y-m-d', strtotime('-7 days'));
        $end_date = date('Y-m-d');
        break;
    case 'month':
        $start_date = date('Y-m-d', strtotime('-30 days'));
        $end_date = date('Y-m-d');
        break;
    case 'quarter':
        $start_date = date('Y-m-d', strtotime('-90 days'));
        $end_date = date('Y-m-d');
        break;
    case 'year':
        $start_date = date('Y-m-d', strtotime('-365 days'));
        $end_date = date('Y-m-d');
        break;
}

// Debug: Log the date range
error_log("Reports - Date Range: $start_date to $end_date");

// ========== COMBINED STATISTICS ==========

$totalHotelBookings = $db->query(
    "SELECT COUNT(*) as total FROM bookings 
     WHERE created_at >= :start_date AND created_at < :end_date + INTERVAL 1 DAY",
    ['start_date' => $start_date . ' 00:00:00', 'end_date' => $end_date]
)->fetch_one()['total'] ?? 0;

// Get total reservations (restaurant)
$totalRestaurantReservations = $db->query(
    "SELECT COUNT(*) as total FROM restaurant_reservations 
     WHERE DATE(created_at) BETWEEN :start_date AND :end_date",
    ['start_date' => $start_date, 'end_date' => $end_date]
)->fetch_one()['total'] ?? 0;

$totalBookings = $totalHotelBookings + $totalRestaurantReservations;

// Get total revenue (hotel)
$totalHotelRevenue = $db->query(
    "SELECT COALESCE(SUM(total_amount), 0) as total 
     FROM bookings 
     WHERE DATE(created_at) BETWEEN :start_date AND :end_date",
    ['start_date' => $start_date, 'end_date' => $end_date]
)->fetch_one()['total'] ?? 0;

// Get total revenue (restaurant - down payments)
$totalRestaurantRevenue = $db->query(
    "SELECT COALESCE(SUM(down_payment), 0) as total 
     FROM restaurant_reservations 
     WHERE DATE(created_at) BETWEEN :start_date AND :end_date",
    ['start_date' => $start_date, 'end_date' => $end_date]
)->fetch_one()['total'] ?? 0;

$totalRevenue = $totalHotelRevenue + $totalRestaurantRevenue;

// Get average booking value
$avgBookingValue = $totalBookings > 0 ? $totalRevenue / $totalBookings : 0;

// Get average length of stay (hotel only)
$avgStay = $db->query(
    "SELECT COALESCE(AVG(nights), 0) as avg_stay 
     FROM bookings 
     WHERE DATE(created_at) BETWEEN :start_date AND :end_date",
    ['start_date' => $start_date, 'end_date' => $end_date]
)->fetch_one()['avg_stay'] ?? 0;

// Get cancellation rate (combined)
$cancelledHotel = $db->query(
    "SELECT COUNT(*) as count FROM bookings 
     WHERE status = 'cancelled' 
     AND DATE(created_at) BETWEEN :start_date AND :end_date",
    ['start_date' => $start_date, 'end_date' => $end_date]
)->fetch_one()['count'] ?? 0;

$cancelledRestaurant = $db->query(
    "SELECT COUNT(*) as count FROM restaurant_reservations 
     WHERE status = 'cancelled' 
     AND DATE(created_at) BETWEEN :start_date AND :end_date",
    ['start_date' => $start_date, 'end_date' => $end_date]
)->fetch_one()['count'] ?? 0;

$totalCancelled = $cancelledHotel + $cancelledRestaurant;
$cancellationRate = $totalBookings > 0 ? round(($totalCancelled / $totalBookings) * 100, 1) : 0;

// Get total number of rooms (for occupancy calculation)
$totalRooms = $db->query("SELECT COUNT(*) as count FROM rooms")->fetch_one()['count'] ?? 10;

// Get occupied nights (hotel only)
$occupiedNights = $db->query(
    "SELECT SUM(nights) as total FROM bookings 
     WHERE status != 'cancelled'
     AND DATE(check_in) BETWEEN :start_date AND :end_date",
    ['start_date' => $start_date, 'end_date' => $end_date]
)->fetch_one()['total'] ?? 0;

$dateDiff = (strtotime($end_date) - strtotime($start_date)) / (60 * 60 * 24) + 1;
$totalNights = $totalRooms * $dateDiff;
$occupancyRate = $totalNights > 0 ? round(($occupiedNights / $totalNights) * 100, 1) : 0;

// Get RevPAR (Revenue Per Available Room) - hotel only
$revPAR = $totalNights > 0 ? round($totalHotelRevenue / $totalNights, 2) : 0;

// ========== TREND DATA ==========

// Combined trend data for chart
$trendData = $db->query(
    "SELECT 
        DATE(created_at) as date,
        'hotel' as type,
        COUNT(*) as count,
        COALESCE(SUM(total_amount), 0) as revenue
     FROM bookings 
     WHERE DATE(created_at) BETWEEN :start_date AND :end_date
     GROUP BY DATE(created_at)
     
     UNION ALL
     
     SELECT 
        DATE(created_at) as date,
        'restaurant' as type,
        COUNT(*) as count,
        COALESCE(SUM(down_payment), 0) as revenue
     FROM restaurant_reservations 
     WHERE DATE(created_at) BETWEEN :start_date AND :end_date
     GROUP BY DATE(created_at)
     
     ORDER BY date ASC",
    ['start_date' => $start_date, 'end_date' => $end_date]
)->find() ?: [];

// Fill in missing dates
$trendLabels = [];
$trendCounts = [];
$trendRevenue = [];
$hotelTrendCounts = [];
$restaurantTrendCounts = [];

$current = strtotime($start_date);
$end = strtotime($end_date);

// Index the data by date and type
$trendDataIndexed = [];
foreach ($trendData as $item) {
    $trendDataIndexed[$item['date']][$item['type']] = $item;
}

while ($current <= $end) {
    $date = date('Y-m-d', $current);
    $trendLabels[] = date('M d', $current);

    $hotelCount = isset($trendDataIndexed[$date]['hotel']) ? (int) $trendDataIndexed[$date]['hotel']['count'] : 0;
    $restaurantCount = isset($trendDataIndexed[$date]['restaurant']) ? (int) $trendDataIndexed[$date]['restaurant']['count'] : 0;
    $hotelRevenue = isset($trendDataIndexed[$date]['hotel']) ? (float) $trendDataIndexed[$date]['hotel']['revenue'] : 0;
    $restaurantRevenue = isset($trendDataIndexed[$date]['restaurant']) ? (float) $trendDataIndexed[$date]['restaurant']['revenue'] : 0;

    $hotelTrendCounts[] = $hotelCount;
    $restaurantTrendCounts[] = $restaurantCount;
    $trendCounts[] = $hotelCount + $restaurantCount;
    $trendRevenue[] = $hotelRevenue + $restaurantRevenue;

    $current = strtotime('+1 day', $current);
}

// ========== BREAKDOWN BY TYPE ==========

$typeData = [
    ['type' => 'Hotel Bookings', 'count' => $totalHotelBookings, 'revenue' => $totalHotelRevenue],
    ['type' => 'Restaurant Reservations', 'count' => $totalRestaurantReservations, 'revenue' => $totalRestaurantRevenue]
];

$typeLabels = ['Hotel Bookings', 'Restaurant Reservations'];
$typeCounts = [$totalHotelBookings, $totalRestaurantReservations];
$typeRevenue = [$totalHotelRevenue, $totalRestaurantRevenue];

// ========== STATUS DISTRIBUTION ==========

$hotelStatusData = $db->query(
    "SELECT 
        status,
        COUNT(*) as count,
        COALESCE(SUM(total_amount), 0) as revenue
     FROM bookings 
     WHERE DATE(created_at) BETWEEN :start_date AND :end_date
     GROUP BY status",
    ['start_date' => $start_date, 'end_date' => $end_date]
)->find() ?: [];

$restaurantStatusData = $db->query(
    "SELECT 
        status,
        COUNT(*) as count,
        COALESCE(SUM(down_payment), 0) as revenue
     FROM restaurant_reservations 
     WHERE DATE(created_at) BETWEEN :start_date AND :end_date
     GROUP BY status",
    ['start_date' => $start_date, 'end_date' => $end_date]
)->find() ?: [];

// Combine status data
$statusMap = [];
foreach ($hotelStatusData as $item) {
    $statusMap[$item['status']] = [
        'count' => (int) $item['count'],
        'revenue' => (float) $item['revenue']
    ];
}
foreach ($restaurantStatusData as $item) {
    if (!isset($statusMap[$item['status']])) {
        $statusMap[$item['status']] = [
            'count' => (int) $item['count'],
            'revenue' => (float) $item['revenue']
        ];
    } else {
        $statusMap[$item['status']]['count'] += (int) $item['count'];
        $statusMap[$item['status']]['revenue'] += (float) $item['revenue'];
    }
}

$statusLabels = array_keys($statusMap);
$statusCounts = array_column($statusMap, 'count');
$statusRevenue = array_column($statusMap, 'revenue');

// ========== RECENT ACTIVITY (Combined) ==========

$recentActivity = $db->query(
    "SELECT 
        'hotel' as type,
        id,
        booking_reference as reference,
        CONCAT(guest_first_name, ' ', guest_last_name) as guest_name,
        room_name as item_name,
        check_in as start_date,
        check_out as end_date,
        nights as quantity,
        total_amount as amount,
        status,
        created_at
     FROM bookings 
     WHERE DATE(created_at) BETWEEN :start_date AND :end_date
     
     UNION ALL
     
     SELECT 
        'restaurant' as type,
        id,
        reservation_reference as reference,
        CONCAT(guest_first_name, ' ', guest_last_name) as guest_name,
        CONCAT('Table for ', guests) as item_name,
        reservation_date as start_date,
        reservation_date as end_date,
        guests as quantity,
        down_payment as amount,
        status,
        created_at
     FROM restaurant_reservations 
     WHERE DATE(created_at) BETWEEN :start_date AND :end_date
     
     ORDER BY created_at DESC
     LIMIT 20",
    ['start_date' => $start_date, 'end_date' => $end_date]
)->find() ?: [];

// ========== CANCELLATION SUMMARY ==========

$lostHotelRevenue = $db->query(
    "SELECT COALESCE(SUM(total_amount), 0) as lost
     FROM bookings 
     WHERE status = 'cancelled'
     AND DATE(created_at) BETWEEN :start_date AND :end_date",
    ['start_date' => $start_date, 'end_date' => $end_date]
)->fetch_one()['lost'] ?? 0;

$lostRestaurantRevenue = $db->query(
    "SELECT COALESCE(SUM(down_payment), 0) as lost
     FROM restaurant_reservations 
     WHERE status = 'cancelled'
     AND DATE(created_at) BETWEEN :start_date AND :end_date",
    ['start_date' => $start_date, 'end_date' => $end_date]
)->fetch_one()['lost'] ?? 0;

$lostRevenue = $lostHotelRevenue + $lostRestaurantRevenue;

// Store data for view
$viewData = [
    'period' => $period,
    'start_date' => $start_date,
    'end_date' => $end_date,
    'totalBookings' => $totalBookings,
    'totalHotelBookings' => $totalHotelBookings,
    'totalRestaurantReservations' => $totalRestaurantReservations,
    'totalRevenue' => $totalRevenue,
    'totalHotelRevenue' => $totalHotelRevenue,
    'totalRestaurantRevenue' => $totalRestaurantRevenue,
    'avgBookingValue' => round($avgBookingValue, 2),
    'avgStay' => round($avgStay, 1),
    'cancellationRate' => $cancellationRate,
    'occupancyRate' => $occupancyRate,
    'revPAR' => $revPAR,
    'trendLabels' => $trendLabels,
    'trendCounts' => $trendCounts,
    'trendRevenue' => $trendRevenue,
    'hotelTrendCounts' => $hotelTrendCounts,
    'restaurantTrendCounts' => $restaurantTrendCounts,
    'typeLabels' => $typeLabels,
    'typeCounts' => $typeCounts,
    'typeRevenue' => $typeRevenue,
    'statusLabels' => $statusLabels,
    'statusCounts' => $statusCounts,
    'statusRevenue' => $statusRevenue,
    'recentActivity' => $recentActivity,
    'totalCancelled' => $totalCancelled,
    'lostRevenue' => $lostRevenue
];

// Extract variables for view
extract($viewData);
?>