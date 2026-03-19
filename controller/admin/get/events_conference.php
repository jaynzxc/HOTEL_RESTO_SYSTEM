<?php
/**
 * GET Controller - Admin Events & Conference
 * Handles fetching events, venues, and statistics
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

// Get today's date
$today = date('Y-m-d');
$nextWeek = date('Y-m-d', strtotime('+7 days'));

// Get all events with venue details
$events = $db->query(
    "SELECT 
        e.*,
        v.name as venue_name,
        v.capacity as venue_capacity,
        v.location as venue_location
     FROM events e
     LEFT JOIN venues v ON e.venue_id = v.id
     ORDER BY e.event_date ASC, e.event_time ASC",
    []
)->find() ?: [];

// Get today's events
$todaysEvents = $db->query(
    "SELECT 
        e.*,
        v.name as venue_name,
        v.capacity as venue_capacity
     FROM events e
     LEFT JOIN venues v ON e.venue_id = v.id
     WHERE e.event_date = :today
     ORDER BY e.event_time ASC",
    ['today' => $today]
)->find() ?: [];

// Get upcoming events (next 7 days)
$upcomingEvents = $db->query(
    "SELECT 
        e.*,
        v.name as venue_name,
        v.capacity as venue_capacity
     FROM events e
     LEFT JOIN venues v ON e.venue_id = v.id
     WHERE e.event_date > :today 
        AND e.event_date <= :nextWeek
     ORDER BY e.event_date ASC, e.event_time ASC",
    [
        'today' => $today,
        'nextWeek' => $nextWeek
    ]
)->find() ?: [];

// Get all venues
$venues = $db->query(
    "SELECT * FROM venues ORDER BY capacity DESC",
    []
)->find() ?: [];

// Get statistics
$stats = [
    'todays_count' => count($todaysEvents),
    'upcoming_count' => count($upcomingEvents),
    'total_venues' => count($venues),
    'occupied_venues' => count(array_filter($venues, function ($v) {
        return $v['status'] !== 'available';
    }))
];

// Get monthly revenue from events (from event payments if you have them)
$monthlyRevenue = $db->query(
    "SELECT COALESCE(SUM(b.total_amount), 0) as total
     FROM bookings b
     WHERE b.booking_type = 'event' 
        AND MONTH(b.created_at) = MONTH(CURDATE())
        AND YEAR(b.created_at) = YEAR(CURDATE())",
    []
)->fetch_one();
$stats['monthly_revenue'] = $monthlyRevenue['total'] ?? 0;

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
    'events' => $events,
    'todaysEvents' => $todaysEvents,
    'upcomingEvents' => $upcomingEvents,
    'venues' => $venues,
    'stats' => $stats,
    
    'today' => $today
];

// Extract variables for view
extract($viewData);
?>