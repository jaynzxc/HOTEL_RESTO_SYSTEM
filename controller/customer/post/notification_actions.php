<?php
/**
 * POST Controller - Notification Actions
 * Handles marking notifications as read, dismiss, etc.
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

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
    exit();
}

$config = require __DIR__ . '/../../../config/config.php';
$db = new Database($config['database']);

try {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'mark_read':
            $notification_id = $_POST['notification_id'] ?? null;

            if ($notification_id) {
                // Mark specific notification as read
                $db->query(
                    "UPDATE notifications SET is_read = 1, read_at = NOW() 
                     WHERE id = :id AND user_id = :user_id",
                    [
                        'id' => $notification_id,
                        'user_id' => $_SESSION['user_id']
                    ]
                );
            } else {
                // Mark all as read
                $db->query(
                    "UPDATE notifications SET is_read = 1, read_at = NOW() 
                     WHERE user_id = :user_id AND is_read = 0",
                    ['user_id' => $_SESSION['user_id']]
                );
            }

            echo json_encode([
                'success' => true,
                'message' => $notification_id ? 'Notification marked as read' : 'All notifications marked as read'
            ]);
            break;

        case 'dismiss':
            $notification_id = $_POST['notification_id'] ?? 0;

            if (!$notification_id) {
                throw new Exception('Notification ID is required');
            }

            // Delete notification (or mark as dismissed)
            $db->query(
                "DELETE FROM notifications WHERE id = :id AND user_id = :user_id",
                [
                    'id' => $notification_id,
                    'user_id' => $_SESSION['user_id']
                ]
            );

            echo json_encode([
                'success' => true,
                'message' => 'Notification dismissed'
            ]);
            break;

        default:
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