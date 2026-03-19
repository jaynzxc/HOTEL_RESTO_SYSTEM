<?php
/**
 * Simple Admin Notification API - No Security
 * Allows inserting notifications into admin_notifications table
 * POST only - accepts JSON data
 */

header('Content-Type: application/json');

// Allow from any origin (CORS)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed. Use POST.'
    ]);
    exit();
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// If no JSON, try POST form data
if (!$input) {
    $input = $_POST;
}

// Validate required fields
$required = ['title', 'message'];
$missing = [];

foreach ($required as $field) {
    if (empty($input[$field])) {
        $missing[] = $field;
    }
}

if (!empty($missing)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Missing required fields: ' . implode(', ', $missing)
    ]);
    exit();
}

// Database connection
try {
    require_once __DIR__ . '/../../Class/Database.php';
    $config = require __DIR__ . '/../../config/config.php';
    $db = new Database($config['database']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed: ' . $e->getMessage()
    ]);
    exit();
}

// Prepare notification data with defaults
$admin_id = isset($input['admin_id']) ? intval($input['admin_id']) : 0;
$title = trim($input['title']);
$message = trim($input['message']);
$type = isset($input['type']) ? $input['type'] : 'info';
$icon = isset($input['icon']) ? $input['icon'] : 'fa-bell';
$link = isset($input['link']) ? $input['link'] : null;

// Validate type
$valid_types = ['info', 'success', 'warning', 'danger'];
if (!in_array($type, $valid_types)) {
    $type = 'info';
}

try {
    // Insert notification
    $db->query(
        "INSERT INTO admin_notifications (
            admin_id, title, message, type, icon, link, created_at
        ) VALUES (
            :admin_id, :title, :message, :type, :icon, :link, NOW()
        )",
        [
            'admin_id' => $admin_id,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'icon' => $icon,
            'link' => $link
        ]
    );

    $notification_id = $db->lastInsertId();

    http_response_code(201);
    echo json_encode([
        'success' => true,
        'message' => 'Notification created successfully',
        'data' => [
            'id' => $notification_id,
            'admin_id' => $admin_id,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'icon' => $icon,
            'link' => $link,
            'created_at' => date('Y-m-d H:i:s')
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to create notification: ' . $e->getMessage()
    ]);
}
?>