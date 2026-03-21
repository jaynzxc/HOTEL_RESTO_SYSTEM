<?php
/**
 * GET Controller - Admin Analytics Dashboard
 * Handles fetching all KPI data for the analytics dashboard
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

// Get current date
$today = date('Y-m-d');
$firstDayOfMonth = date('Y-m-01');
$lastDayOfMonth = date('Y-m-t');
$firstDayOfLastMonth = date('Y-m-01', strtotime('-1 month'));
$lastDayOfLastMonth = date('Y-m-t', strtotime('-1 month'));

// ========== KPI CARDS DATA ==========

// Total Revenue (current month)
$totalRevenue = $db->query(
    "SELECT 
        (SELECT COALESCE(SUM(b.total_amount), 0) FROM bookings b 
         WHERE DATE(b.created_at) BETWEEN :start AND :end) +
        (SELECT COALESCE(SUM(r.down_payment), 0) FROM restaurant_reservations r 
         WHERE DATE(r.created_at) BETWEEN :start AND :end) as total",
    ['start' => $firstDayOfMonth, 'end' => $lastDayOfMonth]
)->fetch_one()['total'] ?? 0;

// Previous month revenue for comparison
$prevMonthRevenue = $db->query(
    "SELECT 
        (SELECT COALESCE(SUM(b.total_amount), 0) FROM bookings b 
         WHERE DATE(b.created_at) BETWEEN :start AND :end) +
        (SELECT COALESCE(SUM(r.down_payment), 0) FROM restaurant_reservations r 
         WHERE DATE(r.created_at) BETWEEN :start AND :end) as total",
    ['start' => $firstDayOfLastMonth, 'end' => $lastDayOfLastMonth]
)->fetch_one()['total'] ?? 0;

$revenueGrowth = $prevMonthRevenue > 0
    ? round((($totalRevenue - $prevMonthRevenue) / $prevMonthRevenue) * 100, 1)
    : 0;

// Occupancy Rate
$totalRooms = $db->query("SELECT COUNT(*) as count FROM rooms")->fetch_one()['count'] ?? 10;

$occupiedNights = $db->query(
    "SELECT COALESCE(SUM(b.nights), 0) as total FROM bookings b 
     WHERE b.status != 'cancelled'
     AND DATE(b.check_in) BETWEEN :start AND :end",
    ['start' => $firstDayOfMonth, 'end' => $lastDayOfMonth]
)->fetch_one()['total'] ?? 0;

$daysInMonth = date('t');
$totalPossibleNights = $totalRooms * $daysInMonth;
$occupancyRate = $totalPossibleNights > 0
    ? round(($occupiedNights / $totalPossibleNights) * 100, 1)
    : 0;

// Previous month occupancy
$prevOccupiedNights = $db->query(
    "SELECT COALESCE(SUM(b.nights), 0) as total FROM bookings b 
     WHERE b.status != 'cancelled'
     AND DATE(b.check_in) BETWEEN :start AND :end",
    ['start' => $firstDayOfLastMonth, 'end' => $lastDayOfLastMonth]
)->fetch_one()['total'] ?? 0;

$prevDaysInMonth = date('t', strtotime('-1 month'));
$prevTotalPossibleNights = $totalRooms * $prevDaysInMonth;
$prevOccupancyRate = $prevTotalPossibleNights > 0
    ? round(($prevOccupiedNights / $prevTotalPossibleNights) * 100, 1)
    : 0;

$occupancyGrowth = round($occupancyRate - $prevOccupancyRate, 1);

// Average Daily Rate (ADR)
$roomRevenue = $db->query(
    "SELECT COALESCE(SUM(b.total_amount), 0) as total FROM bookings b 
     WHERE DATE(b.created_at) BETWEEN :start AND :end",
    ['start' => $firstDayOfMonth, 'end' => $lastDayOfMonth]
)->fetch_one()['total'] ?? 0;

$roomNights = $db->query(
    "SELECT COALESCE(SUM(b.nights), 0) as total FROM bookings b 
     WHERE DATE(b.created_at) BETWEEN :start AND :end",
    ['start' => $firstDayOfMonth, 'end' => $lastDayOfMonth]
)->fetch_one()['total'] ?? 0;

$adr = $roomNights > 0 ? round($roomRevenue / $roomNights, 2) : 0;

// Previous month ADR
$prevRoomRevenue = $db->query(
    "SELECT COALESCE(SUM(b.total_amount), 0) as total FROM bookings b 
     WHERE DATE(b.created_at) BETWEEN :start AND :end",
    ['start' => $firstDayOfLastMonth, 'end' => $lastDayOfLastMonth]
)->fetch_one()['total'] ?? 0;

$prevRoomNights = $db->query(
    "SELECT COALESCE(SUM(b.nights), 0) as total FROM bookings b 
     WHERE DATE(b.created_at) BETWEEN :start AND :end",
    ['start' => $firstDayOfLastMonth, 'end' => $lastDayOfLastMonth]
)->fetch_one()['total'] ?? 0;

$prevAdr = $prevRoomNights > 0 ? round($prevRoomRevenue / $prevRoomNights, 2) : 0;
$adrGrowth = $prevAdr > 0 ? round((($adr - $prevAdr) / $prevAdr) * 100, 1) : 0;

// Revenue Per Available Room (RevPAR)
$revPAR = $totalPossibleNights > 0 ? round($roomRevenue / $totalPossibleNights, 2) : 0;
$prevRevPAR = $prevTotalPossibleNights > 0 ? round($prevRoomRevenue / $prevTotalPossibleNights, 2) : 0;
$revparGrowth = $prevRevPAR > 0 ? round((($revPAR - $prevRevPAR) / $prevRevPAR) * 100, 1) : 0;

// ========== REVENUE TREND (LAST 30 DAYS) ==========

$revenueTrend = $db->query(
    "SELECT 
        DATE(t.date) as date,
        COALESCE((SELECT SUM(b.total_amount) FROM bookings b WHERE DATE(b.created_at) = DATE(t.date)), 0) +
        COALESCE((SELECT SUM(r.down_payment) FROM restaurant_reservations r WHERE DATE(r.created_at) = DATE(t.date)), 0) as daily_revenue
     FROM (
         SELECT DATE_SUB(CURDATE(), INTERVAL n DAY) as date
         FROM (
             SELECT 0 n UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4
             UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9
             UNION SELECT 10 UNION SELECT 11 UNION SELECT 12 UNION SELECT 13 UNION SELECT 14
             UNION SELECT 15 UNION SELECT 16 UNION SELECT 17 UNION SELECT 18 UNION SELECT 19
             UNION SELECT 20 UNION SELECT 21 UNION SELECT 22 UNION SELECT 23 UNION SELECT 24
             UNION SELECT 25 UNION SELECT 26 UNION SELECT 27 UNION SELECT 28 UNION SELECT 29
         ) numbers
     ) t
     ORDER BY t.date ASC"
)->find() ?: [];

$revenueLabels = [];
$revenueData = [];

foreach ($revenueTrend as $item) {
    $revenueLabels[] = date('M d', strtotime($item['date']));
    $revenueData[] = (float) $item['daily_revenue'];
}

// ========== BOOKING SOURCES ==========

$bookingSources = $db->query(
    "SELECT 
        CASE 
            WHEN b.booking_reference LIKE 'HOT%' THEN 'Direct'
            WHEN b.booking_reference LIKE 'OTA%' THEN 'OTA'
            WHEN b.booking_reference LIKE 'CORP%' THEN 'Corporate'
            ELSE 'Other'
        END as source,
        COUNT(*) as count,
        COALESCE(SUM(b.total_amount), 0) as revenue
     FROM bookings b
     WHERE DATE(b.created_at) BETWEEN :start AND :end
     GROUP BY source
     ORDER BY count DESC",
    ['start' => $firstDayOfMonth, 'end' => $lastDayOfMonth]
)->find() ?: [];

// If no data, provide defaults
if (empty($bookingSources)) {
    $bookingSources = [
        ['source' => 'Direct', 'count' => 0, 'revenue' => 0],
        ['source' => 'OTA', 'count' => 0, 'revenue' => 0],
        ['source' => 'Corporate', 'count' => 0, 'revenue' => 0],
        ['source' => 'Other', 'count' => 0, 'revenue' => 0]
    ];
}

$sourceLabels = [];
$sourceCounts = [];
$sourceColors = ['#3b82f6', '#10b981', '#8b5cf6', '#d97706'];

foreach ($bookingSources as $source) {
    $sourceLabels[] = $source['source'];
    $sourceCounts[] = (int) $source['count'];
}

// ========== OCCUPANCY TREND ==========

$occupancyTrend = $db->query(
    "SELECT 
        DATE(b.check_in) as date,
        COALESCE(SUM(b.nights), 0) as occupied_nights,
        COUNT(*) as bookings_count
     FROM bookings b
     WHERE b.status != 'cancelled'
     AND DATE(b.check_in) BETWEEN DATE_SUB(CURDATE(), INTERVAL 30 DAY) AND CURDATE()
     GROUP BY DATE(b.check_in)
     ORDER BY date ASC"
)->find() ?: [];

$occupancyLabels = [];
$occupancyData = [];

$current = strtotime('-29 days');
$end = strtotime('today');
$occupancyIndexed = [];

foreach ($occupancyTrend as $item) {
    $occupancyIndexed[$item['date']] = (int) $item['occupied_nights'];
}

while ($current <= $end) {
    $date = date('Y-m-d', $current);
    $occupancyLabels[] = date('M d', $current);
    $occupancyData[] = isset($occupancyIndexed[$date])
        ? round(($occupancyIndexed[$date] / $totalRooms) * 100, 1)
        : 0;
    $current = strtotime('+1 day', $current);
}

// ========== GUEST SATISFACTION SCORES ==========

// Get average ratings from reviews
$avgRatings = $db->query(
    "SELECT 
        COALESCE(AVG(r.rating), 0) as overall,
        COALESCE(AVG(CASE WHEN r.experience LIKE '%clean%' THEN r.rating ELSE NULL END), 0) as cleanliness,
        COALESCE(AVG(CASE WHEN r.experience LIKE '%service%' THEN r.rating ELSE NULL END), 0) as service,
        COALESCE(AVG(CASE WHEN r.experience LIKE '%room%' THEN r.rating ELSE NULL END), 0) as facilities,
        COALESCE(AVG(CASE WHEN r.experience LIKE '%value%' OR r.experience LIKE '%price%' THEN r.rating ELSE NULL END), 0) as value
     FROM reviews r
     WHERE r.created_at >= DATE_SUB(NOW(), INTERVAL 90 DAY)"
)->fetch_one();

$overallRating = round($avgRatings['overall'] ?? 4.5, 1);
$cleanlinessRating = round($avgRatings['cleanliness'] ?? 4.6, 1);
$serviceRating = round($avgRatings['service'] ?? 4.4, 1);
$facilitiesRating = round($avgRatings['facilities'] ?? 4.3, 1);
$valueRating = round($avgRatings['value'] ?? 4.2, 1);

// ========== TOP PERFORMING ROOMS ==========

$topRooms = $db->query(
    "SELECT 
        b.room_name,
        COUNT(*) as bookings,
        COALESCE(SUM(b.nights), 0) as total_nights,
        COALESCE(SUM(b.total_amount), 0) as revenue,
        ROUND(AVG(b.total_amount / b.nights), 2) as avg_rate
     FROM bookings b
     WHERE DATE(b.created_at) BETWEEN :start AND :end
     AND b.room_name IS NOT NULL
     GROUP BY b.room_name
     ORDER BY revenue DESC
     LIMIT 4",
    ['start' => $firstDayOfMonth, 'end' => $lastDayOfMonth]
)->find() ?: [];

// Calculate occupancy and RevPAR for each room
foreach ($topRooms as &$room) {
    $room['occupancy'] = $daysInMonth > 0
        ? round(($room['total_nights'] / $daysInMonth) * 100, 1)
        : 0;
    $room['revpar'] = round($room['avg_rate'] * ($room['occupancy'] / 100), 2);
}

// ========== KEY INSIGHTS ==========

// Weekend vs Weekday occupancy
$weekendOccupancy = $db->query(
    "SELECT 
        SUM(CASE WHEN DAYOFWEEK(b.check_in) IN (1,7) THEN b.nights ELSE 0 END) as weekend_nights,
        SUM(CASE WHEN DAYOFWEEK(b.check_in) NOT IN (1,7) THEN b.nights ELSE 0 END) as weekday_nights
     FROM bookings b
     WHERE b.status != 'cancelled'
     AND DATE(b.check_in) BETWEEN :start AND :end",
    ['start' => $firstDayOfMonth, 'end' => $lastDayOfMonth]
)->fetch_one();

$weekendNights = $weekendOccupancy['weekend_nights'] ?? 0;
$weekdayNights = $weekendOccupancy['weekday_nights'] ?? 0;
$weekendDays = 8; // Approximate weekends in a month
$weekdayDays = $daysInMonth - $weekendDays;
$weekendOccupancyRate = $weekendDays > 0 ? round(($weekendNights / ($totalRooms * $weekendDays)) * 100, 1) : 0;
$weekdayOccupancyRate = $weekdayDays > 0 ? round(($weekdayNights / ($totalRooms * $weekdayDays)) * 100, 1) : 0;
$weekendVsWeekday = $weekendOccupancyRate - $weekdayOccupancyRate;

// OTA commission estimate (assuming 15% average commission)
$otaRevenue = 0;
foreach ($bookingSources as $source) {
    if ($source['source'] === 'OTA') {
        $otaRevenue = $source['revenue'];
        break;
    }
}
$otaCommission = round($otaRevenue * 0.15, 2);

// Direct bookings count
$directBookings = 0;
foreach ($bookingSources as $source) {
    if ($source['source'] === 'Direct') {
        $directBookings = $source['count'];
        break;
    }
}

// Average lead time
$avgLeadTime = $db->query(
    "SELECT COALESCE(AVG(DATEDIFF(b.check_in, b.created_at)), 0) as avg_lead 
     FROM bookings b
     WHERE b.status != 'cancelled'
     AND DATE(b.created_at) BETWEEN :start AND :end",
    ['start' => $firstDayOfMonth, 'end' => $lastDayOfMonth]
)->fetch_one()['avg_lead'] ?? 0;

// Store data for view
$viewData = [
    'totalRevenue' => $totalRevenue,
    'revenueGrowth' => $revenueGrowth,
    'occupancyRate' => $occupancyRate,
    'occupancyGrowth' => $occupancyGrowth,
    'adr' => $adr,
    'adrGrowth' => $adrGrowth,
    'revPAR' => $revPAR,
    'revparGrowth' => $revparGrowth,
    'revenueLabels' => $revenueLabels,
    'revenueData' => $revenueData,
    'sourceLabels' => $sourceLabels,
    'sourceCounts' => $sourceCounts,
    'sourceColors' => $sourceColors,
    'occupancyLabels' => $occupancyLabels,
    'occupancyData' => $occupancyData,
    'overallRating' => $overallRating,
    'cleanlinessRating' => $cleanlinessRating,
    'serviceRating' => $serviceRating,
    'facilitiesRating' => $facilitiesRating,
    'valueRating' => $valueRating,
    'topRooms' => $topRooms,
    'weekendVsWeekday' => $weekendVsWeekday,
    'otaCommission' => $otaCommission,
    'directBookings' => $directBookings,
    'avgLeadTime' => round($avgLeadTime),
    'today' => date('F j, Y')
];

// Extract variables for view
extract($viewData);
?>