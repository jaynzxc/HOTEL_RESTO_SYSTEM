<?php
/**
 * GET Controller - Customer Notifications
 * Handles fetching all notifications for the current user
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

// Get current user data
$user = $db->query(
    "SELECT id, full_name, first_name, last_name, email, phone, loyalty_points, member_tier, avatar
     FROM users WHERE id = :id",
    ['id' => $_SESSION['user_id']]
)->fetch_one();

// Get all notifications for the user
$notifications = $db->query(
    "SELECT 
        n.id,
        n.title,
        n.message,
        n.type,
        n.icon,
        n.link,
        n.is_read as `read`,
        n.created_at,
        CASE 
            WHEN n.type = 'success' THEN 'booking'
            WHEN n.type = 'promo' THEN 'promo'
            WHEN n.type = 'warning' THEN 'system'
            WHEN n.type = 'info' THEN 'system'
            WHEN n.type = 'loyalty' THEN 'points'
            ELSE 'system'
        END as category,
        CASE 
            WHEN n.type = 'success' THEN 'view booking'
            WHEN n.type = 'promo' THEN 'view offer'
            WHEN n.type = 'loyalty' THEN 'view points'
            ELSE 'view details'
        END as view_text
     FROM notifications n
     WHERE n.user_id = :user_id OR n.user_id IS NULL
     ORDER BY n.created_at DESC
     LIMIT 50",
    ['user_id' => $_SESSION['user_id']]
)->find() ?: [];

// Format notifications for display
foreach ($notifications as &$notif) {
    // Format time ago
    $created = new DateTime($notif['created_at']);
    $now = new DateTime();
    $diff = $now->diff($created);

    if ($diff->days > 0) {
        if ($diff->days == 1) {
            $notif['time_ago'] = 'yesterday';
        } else {
            $notif['time_ago'] = $diff->days . ' days ago';
        }
    } elseif ($diff->h > 0) {
        if ($diff->h == 1) {
            $notif['time_ago'] = '1 hour ago';
        } else {
            $notif['time_ago'] = $diff->h . ' hours ago';
        }
    } elseif ($diff->i > 0) {
        if ($diff->i == 1) {
            $notif['time_ago'] = '1 minute ago';
        } else {
            $notif['time_ago'] = $diff->i . ' minutes ago';
        }
    } else {
        $notif['time_ago'] = 'just now';
    }

    // Set default icon based on type if not set
    if (empty($notif['icon'])) {
        switch ($notif['type']) {
            case 'success':
                $notif['icon'] = 'fa-calendar-check';
                break;
            case 'warning':
                $notif['icon'] = 'fa-triangle-exclamation';
                break;
            case 'promo':
                $notif['icon'] = 'fa-tag';
                break;
            case 'loyalty':
                $notif['icon'] = 'fa-star';
                break;
            default:
                $notif['icon'] = 'fa-circle-info';
        }
    }
}

// Get counts by category
$counts = [
    'total' => count($notifications),
    'unread' => count(array_filter($notifications, fn($n) => !$n['read'])),
    'bookings' => count(array_filter($notifications, fn($n) => in_array($n['category'], ['booking', 'reminder']))),
    'promos' => count(array_filter($notifications, fn($n) => $n['category'] === 'promo')),
    'system' => count(array_filter($notifications, fn($n) => in_array($n['category'], ['system', 'payment', 'points', 'review'])))
];

// Get user initials
$initials = 'G';
if ($user) {
    $first_name = $user['first_name'] ?? '';
    $last_name = $user['last_name'] ?? '';
    $full_name = $user['full_name'] ?? '';

    if (empty($first_name) && empty($last_name) && !empty($full_name)) {
        $name_parts = explode(' ', $full_name, 2);
        $first_name = $name_parts[0];
        $last_name = $name_parts[1] ?? '';
    }

    $initials = strtoupper(
        substr($first_name, 0, 1) .
        (isset($last_name) ? substr($last_name, 0, 1) : '')
    );
}

// Get unread notifications count (for sidebar)
$unread_count = $counts['unread'];

// Store data for view
$viewData = [
    'user' => $user,
    'notifications' => $notifications,
    'counts' => $counts,
    
    'initials' => $initials
];

// Extract variables for view
extract($viewData);
?>