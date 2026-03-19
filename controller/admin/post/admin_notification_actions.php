<?php
/**
 * POST Controller - Admin Notification Actions
 * Handles marking, removing, and clearing admin notifications
 */

session_start();
require_once __DIR__ . '/../../../Class/Database.php';

// Set JSON header for AJAX response
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !$_SESSION['logged_in']) {
    echo json_encode([
        'success' => false,
        'message' => 'Please login to continue'
    ]);
    exit();
}

// Check if user has admin role
$config = require __DIR__ . '/../../../config/config.php';
$db = new Database($config['database']);

$user = $db->query(
    "SELECT role FROM users WHERE id = :id",
    ['id' => $_SESSION['user_id']]
)->fetch_one();

if (!$user || !in_array($user['role'], ['admin', 'staff'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access'
    ]);
    exit();
}

// Check if it's a GET request for unread count
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get_unread_count') {
    try {
        $unread_result = $db->query(
            "SELECT COUNT(*) as count FROM admin_notifications WHERE is_read = 0",
            []
        )->fetch_one();

        echo json_encode([
            'success' => true,
            'count' => $unread_result['count'] ?? 0
        ]);
        exit();
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'count' => 0
        ]);
        exit();
    }
}

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
    exit();
}

try {
    $action = $_POST['action'] ?? '';

    // Mark single admin notification as read
    if ($action === 'mark_as_read') {
        $notification_id = intval($_POST['notification_id'] ?? 0);

        if (!$notification_id) {
            throw new Exception('Notification ID required');
        }

        $db->query(
            "UPDATE admin_notifications SET is_read = 1, read_at = NOW() 
             WHERE id = :id",
            ['id' => $notification_id]
        );

        echo json_encode([
            'success' => true,
            'message' => 'Notification marked as read'
        ]);
        exit();
    }

    // Mark all admin notifications as read
    elseif ($action === 'mark_all_as_read') {
        $db->query(
            "UPDATE admin_notifications SET is_read = 1, read_at = NOW() 
             WHERE is_read = 0"
        );

        echo json_encode([
            'success' => true,
            'message' => 'All notifications marked as read'
        ]);
        exit();
    }

    // Remove single notification (delete permanently)
    elseif ($action === 'remove_notification') {
        $notification_id = intval($_POST['notification_id'] ?? 0);

        if (!$notification_id) {
            throw new Exception('Notification ID required');
        }

        $db->query(
            "DELETE FROM admin_notifications WHERE id = :id",
            ['id' => $notification_id]
        );

        echo json_encode([
            'success' => true,
            'message' => 'Notification removed'
        ]);
        exit();
    }

    // Clear all notifications (delete all)
    elseif ($action === 'clear_all') {
        $db->query("DELETE FROM admin_notifications");

        echo json_encode([
            'success' => true,
            'message' => 'All notifications cleared'
        ]);
        exit();
    } else {
        throw new Exception('Invalid action');
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
exit();
?>