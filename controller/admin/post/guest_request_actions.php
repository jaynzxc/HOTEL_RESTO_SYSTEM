<?php
/**
 * POST Controller - Admin Guest Request Actions
 * Handles updating, assigning, and completing guest requests
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

$config = require __DIR__ . '/../../../config/config.php';
$db = new Database($config['database']);

// Get user role from database
$user = $db->query(
    "SELECT role FROM users WHERE id = :id",
    ['id' => $_SESSION['user_id']]
)->fetch_one();

// Check if user has admin or staff role
if (!$user || !in_array($user['role'], ['admin', 'staff'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access'
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

try {
    $action = $_POST['action'] ?? '';

    // MARK REQUEST AS DONE
    if ($action === 'mark_done') {
        $request_id = intval($_POST['request_id'] ?? 0);

        if (!$request_id) {
            throw new Exception('Request ID required');
        }

        $db->beginTransaction();

        // Get request details
        $request = $db->query(
            "SELECT gi.*, u.id as user_id, u.email, u.full_name 
             FROM guest_interactions gi
             LEFT JOIN users u ON gi.user_id = u.id
             WHERE gi.id = :id",
            ['id' => $request_id]
        )->fetch_one();

        if (!$request) {
            throw new Exception('Request not found');
        }

        // Update request status
        $db->query(
            "UPDATE guest_interactions SET 
                status = 'done',
                completed_at = NOW(),
                updated_at = NOW()
             WHERE id = :id",
            ['id' => $request_id]
        );

        // Create notification for guest if they have account
        if ($request['user_id']) {
            $message = "Your request '{$request['subject']}' has been completed.";

            $db->query(
                "INSERT INTO notifications (user_id, title, message, type, icon, link, created_at) 
                 VALUES (:user_id, 'Request Completed', :message, 'success', 'fa-check-circle', '/src/customer_portal/my_requests.php', NOW())",
                [
                    'user_id' => $request['user_id'],
                    'message' => $message
                ]
            );
        }

        $db->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Request marked as completed'
        ]);
        exit();
    }

    // MARK REQUEST AS IN PROGRESS
    elseif ($action === 'mark_in_progress') {
        $request_id = intval($_POST['request_id'] ?? 0);

        if (!$request_id) {
            throw new Exception('Request ID required');
        }

        // Update request status
        $db->query(
            "UPDATE guest_interactions SET 
                status = 'in-progress',
                assigned_to = :assigned_to,
                updated_at = NOW()
             WHERE id = :id",
            [
                'id' => $request_id,
                'assigned_to' => $_SESSION['user_id']
            ]
        );

        echo json_encode([
            'success' => true,
            'message' => 'Request marked as in progress'
        ]);
        exit();
    }

    // ASSIGN REQUEST TO STAFF
    elseif ($action === 'assign_request') {
        $request_id = intval($_POST['request_id'] ?? 0);
        $staff_id = intval($_POST['staff_id'] ?? 0);

        if (!$request_id || !$staff_id) {
            throw new Exception('Request ID and Staff ID required');
        }

        // Update request assignment
        $db->query(
            "UPDATE guest_interactions SET 
                assigned_to = :staff_id,
                updated_at = NOW()
             WHERE id = :id",
            [
                'id' => $request_id,
                'staff_id' => $staff_id
            ]
        );

        echo json_encode([
            'success' => true,
            'message' => 'Request assigned successfully'
        ]);
        exit();
    }

    // GET REQUEST DETAILS
    elseif ($action === 'get_request_details') {
        $request_id = intval($_POST['request_id'] ?? 0);

        if (!$request_id) {
            throw new Exception('Request ID required');
        }

        $request = $db->query(
            "SELECT 
                gi.*,
                u.full_name as guest_name,
                u.email as guest_email,
                u.phone as guest_phone,
                u.room_number,
                a.full_name as assigned_to_name
             FROM guest_interactions gi
             LEFT JOIN users u ON gi.user_id = u.id
             LEFT JOIN users a ON gi.assigned_to = a.id
             WHERE gi.id = :id",
            ['id' => $request_id]
        )->fetch_one();

        if (!$request) {
            throw new Exception('Request not found');
        }

        echo json_encode([
            'success' => true,
            'request' => $request
        ]);
        exit();
    }

    // ADD RESPONSE TO REQUEST
    elseif ($action === 'add_response') {
        $request_id = intval($_POST['request_id'] ?? 0);
        $response = trim($_POST['response'] ?? '');

        if (!$request_id) {
            throw new Exception('Request ID required');
        }

        if (empty($response)) {
            throw new Exception('Response cannot be empty');
        }

        $db->beginTransaction();

        // Get request details
        $request = $db->query(
            "SELECT gi.*, u.id as user_id 
             FROM guest_interactions gi
             LEFT JOIN users u ON gi.user_id = u.id
             WHERE gi.id = :id",
            ['id' => $request_id]
        )->fetch_one();

        if (!$request) {
            throw new Exception('Request not found');
        }

        // Update request with response
        $db->query(
            "UPDATE guest_interactions SET 
                response = :response,
                status = 'in-progress',
                updated_at = NOW()
             WHERE id = :id",
            [
                'id' => $request_id,
                'response' => $response
            ]
        );

        // Notify guest if they have account
        if ($request['user_id']) {
            $db->query(
                "INSERT INTO notifications (user_id, title, message, type, icon, link, created_at) 
                 VALUES (:user_id, 'Response to Your Request', :message, 'info', 'fa-reply', '/src/customer_portal/my_requests.php', NOW())",
                [
                    'user_id' => $request['user_id'],
                    'message' => "Staff responded to your request: '{$request['subject']}'"
                ]
            );
        }

        $db->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Response added successfully'
        ]);
        exit();
    }

    // GET ALL STAFF FOR ASSIGNMENT
    elseif ($action === 'get_staff_list') {
        $staff = $db->query(
            "SELECT id, full_name, first_name, last_name, email, role 
             FROM users 
             WHERE role IN ('admin', 'staff') 
             ORDER BY full_name ASC",
            []
        )->find() ?: [];

        echo json_encode([
            'success' => true,
            'staff' => $staff
        ]);
        exit();
    } else {
        throw new Exception('Invalid action');
    }

} catch (Exception $e) {
    if (isset($db)) {
        $db->rollBack();
    }
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
exit();
?>