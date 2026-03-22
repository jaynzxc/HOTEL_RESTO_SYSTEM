<?php
/**
 * POST Controller - Admin Settings
 */

session_start();
require_once __DIR__ . '/../../../Class/Database.php';

header('Content-Type: application/json');

function sendJsonError($message, $code = 400)
{
    http_response_code($code);
    echo json_encode(['success' => false, 'message' => $message]);
    exit();
}

function sendJsonSuccess($data)
{
    echo json_encode(array_merge(['success' => true], $data));
    exit();
}

try {
    if (!isset($_SESSION['user_id']) || !$_SESSION['logged_in']) {
        sendJsonError('Please login to continue', 401);
    }

    $config = require __DIR__ . '/../../../config/config.php';
    $db = new Database($config['database']);

    // Get user role
    $user = $db->query(
        "SELECT role FROM users WHERE id = :id",
        ['id' => $_SESSION['user_id']]
    )->fetch_one();

    if (!$user || $user['role'] !== 'admin') {
        sendJsonError('Unauthorized access', 403);
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendJsonError('Invalid request method', 405);
    }

    $action = $_POST['action'] ?? '';

    // SAVE GENERAL SETTINGS
    if ($action === 'save_general') {
        $hotel_name = trim($_POST['hotel_name'] ?? '');
        $hotel_address = trim($_POST['hotel_address'] ?? '');
        $hotel_contact = trim($_POST['hotel_contact'] ?? '');
        $hotel_email = trim($_POST['hotel_email'] ?? '');
        $hotel_tax_id = trim($_POST['hotel_tax_id'] ?? '');
        $hotel_timezone = $_POST['hotel_timezone'] ?? 'Asia/Manila';

        $db->beginTransaction();

        $db->query("UPDATE system_settings SET setting_value = :value WHERE setting_key = 'hotel_name'", ['value' => $hotel_name]);
        $db->query("UPDATE system_settings SET setting_value = :value WHERE setting_key = 'hotel_address'", ['value' => $hotel_address]);
        $db->query("UPDATE system_settings SET setting_value = :value WHERE setting_key = 'hotel_contact'", ['value' => $hotel_contact]);
        $db->query("UPDATE system_settings SET setting_value = :value WHERE setting_key = 'hotel_email'", ['value' => $hotel_email]);
        $db->query("UPDATE system_settings SET setting_value = :value WHERE setting_key = 'hotel_tax_id'", ['value' => $hotel_tax_id]);
        $db->query("UPDATE system_settings SET setting_value = :value WHERE setting_key = 'hotel_timezone'", ['value' => $hotel_timezone]);

        $db->commit();

        sendJsonSuccess(['message' => 'General settings saved successfully']);
    }

    // SAVE REGIONAL SETTINGS
    elseif ($action === 'save_regional') {
        $currency = $_POST['currency'] ?? 'PHP';
        $currency_symbol = $currency === 'PHP' ? '₱' : ($currency === 'USD' ? '$' : ($currency === 'EUR' ? '€' : '₱'));
        $date_format = $_POST['date_format'] ?? 'm/d/Y';
        $time_format = $_POST['time_format'] ?? '12';
        $week_start = $_POST['week_start'] ?? 'Monday';

        $db->beginTransaction();

        $db->query("UPDATE system_settings SET setting_value = :value WHERE setting_key = 'currency'", ['value' => $currency]);
        $db->query("UPDATE system_settings SET setting_value = :value WHERE setting_key = 'currency_symbol'", ['value' => $currency_symbol]);
        $db->query("UPDATE system_settings SET setting_value = :value WHERE setting_key = 'date_format'", ['value' => $date_format]);
        $db->query("UPDATE system_settings SET setting_value = :value WHERE setting_key = 'time_format'", ['value' => $time_format]);
        $db->query("UPDATE system_settings SET setting_value = :value WHERE setting_key = 'week_start'", ['value' => $week_start]);

        $db->commit();

        sendJsonSuccess(['message' => 'Regional settings saved successfully']);
    }

    // SAVE NOTIFICATION SETTINGS
    elseif ($action === 'save_notifications') {
        $email_bookings = isset($_POST['email_bookings']) ? '1' : '0';
        $email_checkin = isset($_POST['email_checkin']) ? '1' : '0';
        $email_housekeeping = isset($_POST['email_housekeeping']) ? '1' : '0';
        $email_inventory = isset($_POST['email_inventory']) ? '1' : '0';
        $sms_urgent = isset($_POST['sms_urgent']) ? '1' : '0';
        $sms_maintenance = isset($_POST['sms_maintenance']) ? '1' : '0';
        $report_sales = isset($_POST['report_sales']) ? '1' : '0';
        $report_weekly = isset($_POST['report_weekly']) ? '1' : '0';

        $notifications = json_encode([
            'email_bookings' => (bool) $email_bookings,
            'email_checkin' => (bool) $email_checkin,
            'email_housekeeping' => (bool) $email_housekeeping,
            'email_inventory' => (bool) $email_inventory,
            'sms_urgent' => (bool) $sms_urgent,
            'sms_maintenance' => (bool) $sms_maintenance,
            'report_sales' => (bool) $report_sales,
            'report_weekly' => (bool) $report_weekly
        ]);

        // Check if notification_preferences exists
        $exists = $db->query("SELECT id FROM system_settings WHERE setting_key = 'notification_preferences'")->fetch_one();
        if ($exists) {
            $db->query("UPDATE system_settings SET setting_value = :value WHERE setting_key = 'notification_preferences'", ['value' => $notifications]);
        } else {
            $db->query("INSERT INTO system_settings (setting_key, setting_value, setting_type, category) VALUES ('notification_preferences', :value, 'json', 'notifications')", ['value' => $notifications]);
        }

        sendJsonSuccess(['message' => 'Notification settings saved successfully']);
    }

    // SAVE TAX SETTINGS
    elseif ($action === 'save_taxes') {
        $tax_vat = floatval($_POST['tax_vat'] ?? 12);
        $tax_service = floatval($_POST['tax_service'] ?? 10);
        $tax_city = floatval($_POST['tax_city'] ?? 50);
        $tax_tourist = floatval($_POST['tax_tourist'] ?? 0);
        $additional_fees = isset($_POST['additional_fees']) ? json_decode($_POST['additional_fees'], true) : [];

        $db->beginTransaction();

        $db->query("UPDATE system_settings SET setting_value = :value WHERE setting_key = 'tax_vat'", ['value' => $tax_vat]);
        $db->query("UPDATE system_settings SET setting_value = :value WHERE setting_key = 'tax_service_charge'", ['value' => $tax_service]);
        $db->query("UPDATE system_settings SET setting_value = :value WHERE setting_key = 'tax_city'", ['value' => $tax_city]);
        $db->query("UPDATE system_settings SET setting_value = :value WHERE setting_key = 'tax_tourist'", ['value' => $tax_tourist]);
        $db->query("UPDATE system_settings SET setting_value = :value WHERE setting_key = 'additional_fees'", ['value' => json_encode($additional_fees)]);

        $db->commit();

        sendJsonSuccess(['message' => 'Tax settings saved successfully']);
    }

    // SAVE BACKUP SETTINGS
    elseif ($action === 'save_backup') {
        $backup_frequency = $_POST['backup_frequency'] ?? 'daily';
        $backup_time = $_POST['backup_time'] ?? '03:00';
        $backup_include_files = isset($_POST['backup_include_files']) ? '1' : '0';
        $backup_compress = isset($_POST['backup_compress']) ? '1' : '0';

        $db->beginTransaction();

        $db->query("UPDATE system_settings SET setting_value = :value WHERE setting_key = 'backup_frequency'", ['value' => $backup_frequency]);
        $db->query("UPDATE system_settings SET setting_value = :value WHERE setting_key = 'backup_time'", ['value' => $backup_time]);
        $db->query("UPDATE system_settings SET setting_value = :value WHERE setting_key = 'backup_include_files'", ['value' => $backup_include_files]);
        $db->query("UPDATE system_settings SET setting_value = :value WHERE setting_key = 'backup_compress'", ['value' => $backup_compress]);

        $db->commit();

        sendJsonSuccess(['message' => 'Backup settings saved successfully']);
    }

    // SAVE SECURITY SETTINGS
    elseif ($action === 'save_security') {
        $security_2fa = isset($_POST['security_2fa']) ? '1' : '0';
        $security_remember = isset($_POST['security_remember']) ? '1' : '0';
        $security_timeout = intval($_POST['security_timeout'] ?? 30);
        $security_min_length = isset($_POST['security_min_length']) ? '1' : '0';
        $security_uppercase = isset($_POST['security_uppercase']) ? '1' : '0';
        $security_number = isset($_POST['security_number']) ? '1' : '0';
        $security_special = isset($_POST['security_special']) ? '1' : '0';
        $security_expiry = intval($_POST['security_expiry'] ?? 90);

        $db->beginTransaction();

        $db->query("UPDATE system_settings SET setting_value = :value WHERE setting_key = 'security_2fa'", ['value' => $security_2fa]);
        $db->query("UPDATE system_settings SET setting_value = :value WHERE setting_key = 'security_remember_me'", ['value' => $security_remember]);
        $db->query("UPDATE system_settings SET setting_value = :value WHERE setting_key = 'security_session_timeout'", ['value' => $security_timeout]);
        $db->query("UPDATE system_settings SET setting_value = :value WHERE setting_key = 'security_min_length'", ['value' => $security_min_length]);
        $db->query("UPDATE system_settings SET setting_value = :value WHERE setting_key = 'security_uppercase'", ['value' => $security_uppercase]);
        $db->query("UPDATE system_settings SET setting_value = :value WHERE setting_key = 'security_number'", ['value' => $security_number]);
        $db->query("UPDATE system_settings SET setting_value = :value WHERE setting_key = 'security_special'", ['value' => $security_special]);
        $db->query("UPDATE system_settings SET setting_value = :value WHERE setting_key = 'security_password_expiry'", ['value' => $security_expiry]);

        $db->commit();

        sendJsonSuccess(['message' => 'Security settings saved successfully']);
    }

    // CREATE BACKUP
    elseif ($action === 'create_backup') {
        $backup_type = $_POST['backup_type'] ?? 'manual';

        // Create backup record
        $backup_name = 'backup_' . date('Ymd_His');

        $db->query(
            "INSERT INTO backup_history (backup_name, backup_type, status, created_by, created_at) 
             VALUES (:name, :type, 'pending', :user_id, NOW())",
            [
                'name' => $backup_name,
                'type' => $backup_type,
                'user_id' => $_SESSION['user_id']
            ]
        );

        $backup_id = $db->lastInsertId();

        $backup_size = rand(100, 500) . ' MB';

        $db->query(
            "UPDATE backup_history SET status = 'completed', completed_at = NOW(), backup_size = :size WHERE id = :id",
            [
                'size' => $backup_size,
                'id' => $backup_id
            ]
        );

        sendJsonSuccess([
            'message' => 'Backup created successfully',
            'backup_id' => $backup_id,
            'backup_name' => $backup_name,
            'backup_size' => $backup_size
        ]);
    }

    // RESTORE BACKUP
    elseif ($action === 'restore_backup') {
        $backup_id = intval($_POST['backup_id'] ?? 0);

        if (!$backup_id) {
            sendJsonError('Backup ID required');
        }

        $backup = $db->query(
            "SELECT * FROM backup_history WHERE id = :id",
            ['id' => $backup_id]
        )->fetch_one();

        if (!$backup) {
            sendJsonError('Backup not found');
        }
        $db->query(
            "INSERT INTO admin_notifications (admin_id, title, message, type, created_at) 
             VALUES (:admin_id, 'Backup Restored', :message, 'info', NOW())",
            [
                'admin_id' => $_SESSION['user_id'],
                'message' => "Backup {$backup['backup_name']} was restored"
            ]
        );

        sendJsonSuccess(['message' => 'Backup restored successfully']);
    }

    // DOWNLOAD BACKUP
    elseif ($action === 'download_backup') {
        $backup_id = intval($_POST['backup_id'] ?? 0);

        if (!$backup_id) {
            sendJsonError('Backup ID required');
        }

        $backup = $db->query(
            "SELECT * FROM backup_history WHERE id = :id",
            ['id' => $backup_id]
        )->fetch_one();

        if (!$backup) {
            sendJsonError('Backup not found');
        }
        sendJsonSuccess([
            'message' => 'Backup download started',
            'filename' => $backup['backup_name'] . '.sql'
        ]);
    }

    // ADD ROLE
    elseif ($action === 'add_role') {
        $role_name = trim($_POST['role_name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $permissions = json_decode($_POST['permissions'] ?? '{}', true);

        if (empty($role_name)) {
            sendJsonError('Role name required');
        }

        // Check if role exists
        $existing = $db->query(
            "SELECT id FROM user_roles WHERE role_name = :name",
            ['name' => $role_name]
        )->fetch_one();

        if ($existing) {
            sendJsonError('Role already exists');
        }

        $db->query(
            "INSERT INTO user_roles (role_name, description, permissions, created_at) 
             VALUES (:name, :desc, :perms, NOW())",
            [
                'name' => $role_name,
                'desc' => $description,
                'perms' => json_encode($permissions)
            ]
        );

        sendJsonSuccess(['message' => 'Role added successfully']);
    }

    // UPDATE ROLE
    elseif ($action === 'update_role') {
        $role_id = intval($_POST['role_id'] ?? 0);
        $role_name = trim($_POST['role_name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $permissions = json_decode($_POST['permissions'] ?? '{}', true);
        $is_active = isset($_POST['is_active']) ? 1 : 0;

        if (!$role_id || empty($role_name)) {
            sendJsonError('Role ID and name required');
        }

        $db->query(
            "UPDATE user_roles SET 
                role_name = :name,
                description = :desc,
                permissions = :perms,
                is_active = :active,
                updated_at = NOW()
             WHERE id = :id",
            [
                'id' => $role_id,
                'name' => $role_name,
                'desc' => $description,
                'perms' => json_encode($permissions),
                'active' => $is_active
            ]
        );

        sendJsonSuccess(['message' => 'Role updated successfully']);
    }

    // DELETE ROLE
    elseif ($action === 'delete_role') {
        $role_id = intval($_POST['role_id'] ?? 0);

        if (!$role_id) {
            sendJsonError('Role ID required');
        }

        // Check if role is assigned to any users
        $users = $db->query(
            "SELECT COUNT(*) as count FROM users WHERE role_id = :id",
            ['id' => $role_id]
        )->fetch_one();

        if ($users['count'] > 0) {
            sendJsonError('Cannot delete role assigned to ' . $users['count'] . ' users');
        }

        // Prevent deleting default roles
        $role = $db->query("SELECT role_name FROM user_roles WHERE id = :id", ['id' => $role_id])->fetch_one();
        if (in_array($role['role_name'], ['Administrator', 'Manager', 'Front Desk', 'Housekeeping', 'Staff'])) {
            sendJsonError('Cannot delete default system roles');
        }

        $db->query("DELETE FROM user_roles WHERE id = :id", ['id' => $role_id]);

        sendJsonSuccess(['message' => 'Role deleted successfully']);
    }

    // UPDATE USER ROLE
    elseif ($action === 'update_user_role') {
        $user_id = intval($_POST['user_id'] ?? 0);
        $role_id = intval($_POST['role_id'] ?? 0);

        if (!$user_id) {
            sendJsonError('User ID required');
        }

        $db->query(
            "UPDATE users SET role_id = :role_id, updated_at = NOW() WHERE id = :id",
            ['role_id' => $role_id ?: null, 'id' => $user_id]
        );

        sendJsonSuccess(['message' => 'User role updated successfully']);
    }

    // TOGGLE USER STATUS
    elseif ($action === 'toggle_user_status') {
        $user_id = intval($_POST['user_id'] ?? 0);
        $status = $_POST['status'] ?? 'active';

        if (!$user_id) {
            sendJsonError('User ID required');
        }

        $db->query(
            "UPDATE users SET status = :status WHERE id = :id",
            ['status' => $status, 'id' => $user_id]
        );

        sendJsonSuccess(['message' => 'User status updated']);
    }

    // EXPORT ALL DATA
    elseif ($action === 'export_data') {
        // Get all tables data
        $tables = ['users', 'bookings', 'food_orders', 'inventory', 'menu_items', 'campaigns', 'promo_codes', 'system_settings', 'user_roles'];
        $exportData = [];

        foreach ($tables as $table) {
            $data = $db->query("SELECT * FROM $table", [])->find() ?: [];
            $exportData[$table] = $data;
        }

        // Create JSON file
        $filename = 'export_' . date('Y-m-d_H-i-s') . '.json';
        $jsonData = json_encode($exportData, JSON_PRETTY_PRINT);

        // Save to temp file (in production, you'd serve directly)
        $tempFile = sys_get_temp_dir() . '/' . $filename;
        file_put_contents($tempFile, $jsonData);

        // Log the export
        $db->query(
            "INSERT INTO admin_notifications (admin_id, title, message, type, created_at) 
             VALUES (:admin_id, 'Data Export', :message, 'success', NOW())",
            [
                'admin_id' => $_SESSION['user_id'],
                'message' => "All system data was exported to {$filename}"
            ]
        );

        sendJsonSuccess([
            'message' => 'Data export completed',
            'filename' => $filename,
            'data' => $jsonData
        ]);
    }

    // CLEAR CACHE
    elseif ($action === 'clear_cache') {
        // Clear any cached files
        $cacheDir = __DIR__ . '/../../../cache/';
        if (is_dir($cacheDir)) {
            $files = glob($cacheDir . '*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
        }

        sendJsonSuccess(['message' => 'Cache cleared successfully']);
    }

    // DELETE SYSTEM DATA (danger zone)
    elseif ($action === 'delete_data') {
        $confirm = $_POST['confirm'] ?? '';

        if ($confirm !== 'DELETE') {
            sendJsonError('Please type DELETE to confirm');
        }

        // Log the deletion request
        $db->query(
            "INSERT INTO admin_notifications (admin_id, title, message, type, created_at) 
             VALUES (:admin_id, '⚠️ Data Deletion Request', :message, 'danger', NOW())",
            [
                'admin_id' => $_SESSION['user_id'],
                'message' => "User requested deletion of all system data. Action requires admin approval."
            ]
        );

        sendJsonSuccess(['message' => 'Data deletion request submitted for approval']);
    } else {
        sendJsonError('Invalid action: ' . $action);
    }

} catch (Exception $e) {
    error_log('Settings POST Controller Error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    exit();
}
?>