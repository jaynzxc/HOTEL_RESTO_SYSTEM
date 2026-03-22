<?php
/**
 * GET Controller - Admin Settings
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

// Get user role
$user = $db->query(
    "SELECT u.*, ur.role_name, ur.permissions 
     FROM users u
     LEFT JOIN user_roles ur ON u.role_id = ur.id
     WHERE u.id = :id",
    ['id' => $_SESSION['user_id']]
)->fetch_one();

// Check if user has admin role
if (!$user || $user['role'] !== 'admin') {
    header('Location: ../../view/customer_portal/dashboard.php');
    exit();
}

// Get all system settings grouped by category
$settings = $db->query(
    "SELECT * FROM system_settings ORDER BY category, setting_key",
    []
)->find() ?: [];

// Group settings by category
$groupedSettings = [];
foreach ($settings as $setting) {
    $groupedSettings[$setting['category']][$setting['setting_key']] = $setting['setting_value'];
}

// Get all user roles
$roles = $db->query(
    "SELECT * FROM user_roles ORDER BY id",
    []
)->find() ?: [];

// Get users with roles
$users = $db->query(
    "SELECT u.id, u.full_name, u.email, u.status, u.last_login, 
            ur.role_name, ur.id as role_id
     FROM users u
     LEFT JOIN user_roles ur ON u.role_id = ur.id
     WHERE u.role = 'staff' OR u.role = 'admin'
     ORDER BY u.id DESC
     LIMIT 20",
    []
)->find() ?: [];

// Get recent backup history
$backupHistory = $db->query(
    "SELECT * FROM backup_history ORDER BY created_at DESC LIMIT 10",
    []
)->find() ?: [];

// Get recent login history
$loginHistory = $db->query(
    "SELECT lh.*, u.full_name 
     FROM login_history lh
     LEFT JOIN users u ON lh.user_id = u.id
     ORDER BY lh.created_at DESC 
     LIMIT 10",
    []
)->find() ?: [];

// Get admin user data
$admin = $db->query(
    "SELECT id, full_name, first_name, last_name, email, role, avatar
     FROM users WHERE id = :id",
    ['id' => $_SESSION['user_id']]
)->fetch_one();

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

// Get unread notifications count
try {
    $unread_result = $db->query(
        "SELECT COUNT(*) as count FROM admin_notifications 
         WHERE admin_id = :admin_id AND is_read = 0",
        ['admin_id' => $_SESSION['user_id']]
    )->fetch_one();
    $unread_count = $unread_result['count'] ?? 0;
} catch (Exception $e) {
    $unread_count = 0;
}

// Parse additional fees if exists
$additionalFees = [];
if (isset($groupedSettings['taxes']['additional_fees'])) {
    $additionalFees = json_decode($groupedSettings['taxes']['additional_fees'], true) ?: [];
}

// Store data for view
$viewData = [
    'admin' => $admin,
    'initials' => $initials,
    'settings' => $groupedSettings,
    'roles' => $roles,
    'users' => $users,
    'backupHistory' => $backupHistory,
    'loginHistory' => $loginHistory,
    'additionalFees' => $additionalFees,
    'unread_count' => $unread_count,
    'today' => date('F j, Y'),
    'currentDateTime' => date('Y-m-d H:i:s')
];

extract($viewData);
?>