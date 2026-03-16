<?php
/**
 * POST Controller - Admin Customer Actions (CRM)
 * Handles all guest management actions
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

// Check if user has admin role
if (!$user || $user['role'] !== 'admin') {
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

    // ADD NEW GUEST
    if ($action === 'add_guest') {
        $full_name = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $birthday = $_POST['birthday'] ?? null;
        $preferences = trim($_POST['preferences'] ?? '');
        $allergies = trim($_POST['allergies'] ?? '');

        if (empty($full_name) || empty($email)) {
            throw new Exception('Name and email are required');
        }

        // Check if email already exists
        $existing = $db->query(
            "SELECT id FROM users WHERE email = :email",
            ['email' => $email]
        )->fetch_one();

        if ($existing) {
            throw new Exception('Email already exists');
        }

        // Generate random password
        $temp_password = bin2hex(random_bytes(4));
        $hashed_password = password_hash($temp_password, PASSWORD_DEFAULT);

        // Split full name
        $name_parts = explode(' ', $full_name, 2);
        $first_name = $name_parts[0];
        $last_name = $name_parts[1] ?? '';

        $db->query(
            "INSERT INTO users (
                full_name, first_name, last_name, email, phone, 
                birthday, preferences, allergies, role, password, 
                email_verified, created_at
            ) VALUES (
                :full_name, :first_name, :last_name, :email, :phone,
                :birthday, :preferences, :allergies, 'customer', :password,
                1, NOW()
            )",
            [
                'full_name' => $full_name,
                'first_name' => $first_name,
                'last_name' => $last_name,
                'email' => $email,
                'phone' => $phone,
                'birthday' => $birthday,
                'preferences' => $preferences,
                'allergies' => $allergies,
                'password' => $hashed_password
            ]
        );

        $user_id = $db->lastInsertId();

        // Create notification for admin
        $db->query(
            "INSERT INTO notifications (user_id, title, message, type, icon, created_at) 
             VALUES (:user_id, 'New Guest Added', :message, 'success', 'fa-user-plus', NOW())",
            [
                'user_id' => $_SESSION['user_id'],
                'message' => "Guest $full_name has been added to the system"
            ]
        );

        echo json_encode([
            'success' => true,
            'message' => 'Guest added successfully',
            'user_id' => $user_id,
            'temp_password' => $temp_password
        ]);
        exit();
    }

    // GET GUEST DETAILS
    elseif ($action === 'get_guest') {
        $user_id = intval($_POST['user_id'] ?? 0);

        if (!$user_id) {
            throw new Exception('Invalid user ID');
        }

        $guest = $db->query(
            "SELECT 
                u.id, u.full_name, u.first_name, u.last_name, u.email, u.phone,
                u.loyalty_points, u.member_tier, u.created_at, u.last_login,
                u.birthday, u.anniversary, u.preferences, u.allergies,
                u.address, u.city, u.postal_code, u.country,
                COALESCE((
                    SELECT COUNT(*) FROM bookings WHERE user_id = u.id
                ), 0) as total_stays,
                COALESCE((
                    SELECT SUM(total_amount) FROM bookings WHERE user_id = u.id
                ), 0) as total_spent
             FROM users u
             WHERE u.id = :id",
            ['id' => $user_id]
        )->fetch_one();

        if (!$guest) {
            throw new Exception('Guest not found');
        }

        // Get recent bookings
        $bookings = $db->query(
            "SELECT * FROM bookings WHERE user_id = :user_id ORDER BY created_at DESC LIMIT 5",
            ['user_id' => $user_id]
        )->find() ?: [];

        // Get recent redemptions
        $redemptions = $db->query(
            "SELECT * FROM redemptions WHERE user_id = :user_id ORDER BY created_at DESC LIMIT 5",
            ['user_id' => $user_id]
        )->find() ?: [];

        // Get recent notifications
        $notifications = $db->query(
            "SELECT * FROM notifications WHERE user_id = :user_id ORDER BY created_at DESC LIMIT 5",
            ['user_id' => $user_id]
        )->find() ?: [];

        echo json_encode([
            'success' => true,
            'guest' => $guest,
            'bookings' => $bookings,
            'redemptions' => $redemptions,
            'notifications' => $notifications
        ]);
        exit();
    }

    // UPDATE GUEST
    elseif ($action === 'update_guest') {
        $user_id = intval($_POST['user_id'] ?? 0);
        $full_name = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $birthday = $_POST['birthday'] ?? null;
        $anniversary = $_POST['anniversary'] ?? null;
        $preferences = trim($_POST['preferences'] ?? '');
        $allergies = trim($_POST['allergies'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $city = trim($_POST['city'] ?? '');
        $postal_code = trim($_POST['postal_code'] ?? '');
        $country = trim($_POST['country'] ?? 'Philippines');

        if (!$user_id || empty($full_name) || empty($email)) {
            throw new Exception('Required fields missing');
        }

        // Check if email exists for another user
        $existing = $db->query(
            "SELECT id FROM users WHERE email = :email AND id != :id",
            ['email' => $email, 'id' => $user_id]
        )->fetch_one();

        if ($existing) {
            throw new Exception('Email already used by another guest');
        }

        $name_parts = explode(' ', $full_name, 2);
        $first_name = $name_parts[0];
        $last_name = $name_parts[1] ?? '';

        $db->query(
            "UPDATE users SET 
                full_name = :full_name,
                first_name = :first_name,
                last_name = :last_name,
                email = :email,
                phone = :phone,
                birthday = :birthday,
                anniversary = :anniversary,
                preferences = :preferences,
                allergies = :allergies,
                address = :address,
                city = :city,
                postal_code = :postal_code,
                country = :country,
                updated_at = NOW()
             WHERE id = :id",
            [
                'id' => $user_id,
                'full_name' => $full_name,
                'first_name' => $first_name,
                'last_name' => $last_name,
                'email' => $email,
                'phone' => $phone,
                'birthday' => $birthday,
                'anniversary' => $anniversary,
                'preferences' => $preferences,
                'allergies' => $allergies,
                'address' => $address,
                'city' => $city,
                'postal_code' => $postal_code,
                'country' => $country
            ]
        );

        // Create notification for admin
        $db->query(
            "INSERT INTO notifications (user_id, title, message, type, icon, created_at) 
             VALUES (:user_id, 'Guest Updated', :message, 'info', 'fa-user-pen', NOW())",
            [
                'user_id' => $_SESSION['user_id'],
                'message' => "Guest $full_name has been updated"
            ]
        );

        echo json_encode([
            'success' => true,
            'message' => 'Guest updated successfully'
        ]);
        exit();
    }

    // SEND MESSAGE
    elseif ($action === 'send_message') {
        $user_id = intval($_POST['user_id'] ?? 0);
        $subject = trim($_POST['subject'] ?? '');
        $message = trim($_POST['message'] ?? '');
        $type = $_POST['type'] ?? 'email'; // email, sms, both

        if (!$user_id || empty($subject) || empty($message)) {
            throw new Exception('Required fields missing');
        }

        $guest = $db->query(
            "SELECT full_name, email, phone FROM users WHERE id = :id",
            ['id' => $user_id]
        )->fetch_one();

        if (!$guest) {
            throw new Exception('Guest not found');
        }

        // Create notification for the guest
        $db->query(
            "INSERT INTO notifications (user_id, title, message, type, icon, created_at) 
             VALUES (:user_id, :title, :message, 'info', 'fa-envelope', NOW())",
            [
                'user_id' => $user_id,
                'title' => $subject,
                'message' => $message
            ]
        );

        // Create notification for admin (confirmation)
        $db->query(
            "INSERT INTO notifications (user_id, title, message, type, icon, created_at) 
             VALUES (:user_id, 'Message Sent', :message, 'success', 'fa-paper-plane', NOW())",
            [
                'user_id' => $_SESSION['user_id'],
                'message' => "Message sent to $guest[full_name]: $subject"
            ]
        );

        // Log interaction
        $db->query(
            "INSERT INTO guest_interactions (user_id, admin_id, type, subject, message, created_at) 
             VALUES (:user_id, :admin_id, :type, :subject, :message, NOW())",
            [
                'user_id' => $user_id,
                'admin_id' => $_SESSION['user_id'],
                'type' => $type,
                'subject' => $subject,
                'message' => $message
            ]
        );

        echo json_encode([
            'success' => true,
            'message' => 'Message sent successfully',
            'guest' => $guest
        ]);
        exit();
    }

    // SEND BIRTHDAY GREETINGS
    elseif ($action === 'send_birthday_greetings') {
        $user_ids = $_POST['user_ids'] ?? [];
        $custom_message = trim($_POST['custom_message'] ?? '');

        if (empty($user_ids)) {
            // Get all upcoming birthdays (next 14 days)
            $celebrations = $db->query(
                "SELECT id, full_name, birthday, email, phone 
                 FROM users
                 WHERE role = 'customer'
                    AND birthday IS NOT NULL
                    AND DATE_FORMAT(birthday, '%m-%d') BETWEEN DATE_FORMAT(NOW(), '%m-%d') AND DATE_FORMAT(DATE_ADD(NOW(), INTERVAL 14 DAY), '%m-%d')",
                []
            )->find() ?: [];

            $user_ids = array_column($celebrations, 'id');
        } else {
            // Get details for selected users
            $placeholders = implode(',', array_fill(0, count($user_ids), '?'));
            $celebrations = $db->query(
                "SELECT id, full_name, birthday FROM users WHERE id IN ($placeholders)",
                $user_ids
            )->find() ?: [];
        }

        $count = 0;
        $sent = [];

        foreach ($celebrations as $celebration) {
            $birthday = new DateTime($celebration['birthday']);
            $age = $birthday->diff(new DateTime())->y;

            // Default birthday message
            $message = $custom_message ?: "Happy Birthday, {$celebration['full_name']}! We hope you have a wonderful day. As a token of our appreciation, enjoy a special treat on us during your next visit. - The Lùcas Team";

            // Insert notification for user
            $db->query(
                "INSERT INTO notifications (user_id, title, message, type, icon, created_at) 
                 VALUES (:user_id, 'Happy Birthday! 🎂', :message, 'promo', 'fa-cake-candles', NOW())",
                [
                    'user_id' => $celebration['id'],
                    'message' => $message
                ]
            );

            $sent[] = $celebration['full_name'];
            $count++;
        }

        // Create notification for admin
        $db->query(
            "INSERT INTO notifications (user_id, title, message, type, icon, created_at) 
             VALUES (:user_id, 'Birthday Greetings Sent', :message, 'success', 'fa-gift', NOW())",
            [
                'user_id' => $_SESSION['user_id'],
                'message' => "Birthday greetings sent to $count guest" . ($count > 1 ? 's' : '')
            ]
        );

        echo json_encode([
            'success' => true,
            'message' => "Birthday greetings sent to $count guests",
            'sent' => $sent
        ]);
        exit();
    }

    // SEND ANNIVERSARY GREETINGS
    elseif ($action === 'send_anniversary_greetings') {
        $user_ids = $_POST['user_ids'] ?? [];
        $custom_message = trim($_POST['custom_message'] ?? '');

        if (empty($user_ids)) {
            // Get all upcoming anniversaries (next 14 days)
            $celebrations = $db->query(
                "SELECT id, full_name, anniversary 
                 FROM users
                 WHERE role = 'customer'
                    AND anniversary IS NOT NULL
                    AND DATE_FORMAT(anniversary, '%m-%d') BETWEEN DATE_FORMAT(NOW(), '%m-%d') AND DATE_FORMAT(DATE_ADD(NOW(), INTERVAL 14 DAY), '%m-%d')",
                []
            )->find() ?: [];

            $user_ids = array_column($celebrations, 'id');
        }

        $count = 0;
        $sent = [];

        foreach ($celebrations as $celebration) {
            $anniv = new DateTime($celebration['anniversary']);
            $years = $anniv->diff(new DateTime())->y;

            // Default anniversary message
            $message = $custom_message ?: "Happy Anniversary, {$celebration['full_name']}! We're honored to be part of your special day. Celebrate with us and enjoy a complimentary dessert on your next visit. - The Lùcas Team";

            // Insert notification for user
            $db->query(
                "INSERT INTO notifications (user_id, title, message, type, icon, created_at) 
                 VALUES (:user_id, 'Happy Anniversary! 💝', :message, 'promo', 'fa-heart', NOW())",
                [
                    'user_id' => $celebration['id'],
                    'message' => $message
                ]
            );

            $sent[] = $celebration['full_name'];
            $count++;
        }

        // Create notification for admin
        $db->query(
            "INSERT INTO notifications (user_id, title, message, type, icon, created_at) 
             VALUES (:user_id, 'Anniversary Greetings Sent', :message, 'success', 'fa-gift', NOW())",
            [
                'user_id' => $_SESSION['user_id'],
                'message' => "Anniversary greetings sent to $count guest" . ($count > 1 ? 's' : '')
            ]
        );

        echo json_encode([
            'success' => true,
            'message' => "Anniversary greetings sent to $count guests",
            'sent' => $sent
        ]);
        exit();
    }

    // SEND BULK EMAIL
    elseif ($action === 'send_bulk_email') {
        $subject = trim($_POST['subject'] ?? '');
        $message = trim($_POST['message'] ?? '');
        $filter = $_POST['filter'] ?? 'all';

        if (empty($subject) || empty($message)) {
            throw new Exception('Subject and message are required');
        }

        // Build query based on filter
        $where = "role = 'customer'";
        if ($filter === 'active') {
            $where .= " AND last_login >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
        } elseif ($filter === 'vip') {
            $where .= " AND member_tier IN ('gold', 'platinum')";
        } elseif ($filter === 'new') {
            $where .= " AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
        }

        $guests = $db->query(
            "SELECT id, full_name, email FROM users WHERE $where",
            []
        )->find() ?: [];

        $count = 0;
        foreach ($guests as $guest) {
            // Personalize message
            $personalized = str_replace('[name]', $guest['full_name'], $message);

            // Insert notification for each guest
            $db->query(
                "INSERT INTO notifications (user_id, title, message, type, icon, created_at) 
                 VALUES (:user_id, :title, :message, 'promo', 'fa-bullhorn', NOW())",
                [
                    'user_id' => $guest['id'],
                    'title' => $subject,
                    'message' => $personalized
                ]
            );
            $count++;
        }

        // Create notification for admin
        $db->query(
            "INSERT INTO notifications (user_id, title, message, type, icon, created_at) 
             VALUES (:user_id, 'Bulk Email Sent', :message, 'success', 'fa-envelopes-bulk', NOW())",
            [
                'user_id' => $_SESSION['user_id'],
                'message' => "Bulk email '$subject' sent to $count guests"
            ]
        );

        echo json_encode([
            'success' => true,
            'message' => "Emails sent to $count guests",
            'count' => $count
        ]);
        exit();
    }

    // APPLY FILTER
    elseif ($action === 'apply_filter') {
        $tier = $_POST['tier'] ?? 'all';
        $stay = $_POST['stay'] ?? '0';
        $search = $_POST['search'] ?? '';

        // Build redirect URL with filters
        $params = [];
        if ($tier !== 'all')
            $params[] = "tier=$tier";
        if ($stay > 0)
            $params[] = "stay=$stay";
        if (!empty($search))
            $params[] = "search=" . urlencode($search);

        $queryString = !empty($params) ? '?' . implode('&', $params) : '';

        echo json_encode([
            'success' => true,
            'redirect' => $queryString
        ]);
        exit();
    }

    // EXPORT GUESTS
    elseif ($action === 'export_guests') {
        $format = $_POST['format'] ?? 'csv';
        $filter = $_POST['filter'] ?? 'all';

        $where = "WHERE role = 'customer'";
        if ($filter === 'active') {
            $where .= " AND last_login >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
        } elseif ($filter === 'vip') {
            $where .= " AND member_tier IN ('gold', 'platinum')";
        } elseif ($filter === 'new') {
            $where .= " AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
        }

        $guests = $db->query(
            "SELECT 
                id, full_name, email, phone, loyalty_points, member_tier,
                created_at, last_login, preferences, allergies,
                birthday, anniversary, address, city, country
             FROM users
             $where
             ORDER BY full_name ASC",
            []
        )->find() ?: [];

        if ($format === 'csv') {
            $filename = 'guests_export_' . date('Y-m-d') . '.csv';
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="' . $filename . '"');

            $output = fopen('php://output', 'w');

            // Headers
            if (!empty($guests)) {
                fputcsv($output, array_keys($guests[0]));

                // Data
                foreach ($guests as $row) {
                    fputcsv($output, $row);
                }
            }
            fclose($output);
            exit();
        } else {
            header('Content-Type: application/json');
            header('Content-Disposition: attachment; filename="guests_export_' . date('Y-m-d') . '.json"');
            echo json_encode($guests, JSON_PRETTY_PRINT);
            exit();
        }
    }

    // IMPORT GUESTS (CSV)
    elseif ($action === 'import_guests') {
        if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('Please upload a valid CSV file');
        }

        $file = $_FILES['csv_file']['tmp_name'];
        $handle = fopen($file, 'r');

        // Get headers
        $headers = fgetcsv($handle);
        $required = ['full_name', 'email'];

        $count = 0;
        $errors = [];
        $imported = [];

        while (($data = fgetcsv($handle)) !== FALSE) {
            $row = array_combine($headers, $data);

            // Validate required fields
            if (empty($row['full_name']) || empty($row['email'])) {
                $errors[] = "Row " . ($count + 2) . ": Missing required fields";
                continue;
            }

            // Check if email exists
            $existing = $db->query(
                "SELECT id FROM users WHERE email = :email",
                ['email' => $row['email']]
            )->fetch_one();

            if ($existing) {
                $errors[] = "Row " . ($count + 2) . ": Email already exists";
                continue;
            }

            // Split name
            $name_parts = explode(' ', $row['full_name'], 2);
            $first_name = $name_parts[0];
            $last_name = $name_parts[1] ?? '';

            // Generate random password
            $temp_password = bin2hex(random_bytes(4));
            $hashed_password = password_hash($temp_password, PASSWORD_DEFAULT);

            $db->query(
                "INSERT INTO users (
                    full_name, first_name, last_name, email, phone,
                    birthday, preferences, allergies, address, city, country,
                    role, password, email_verified, created_at
                ) VALUES (
                    :full_name, :first_name, :last_name, :email, :phone,
                    :birthday, :preferences, :allergies, :address, :city, :country,
                    'customer', :password, 1, NOW()
                )",
                [
                    'full_name' => $row['full_name'],
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                    'email' => $row['email'],
                    'phone' => $row['phone'] ?? '',
                    'birthday' => $row['birthday'] ?? null,
                    'preferences' => $row['preferences'] ?? '',
                    'allergies' => $row['allergies'] ?? '',
                    'address' => $row['address'] ?? '',
                    'city' => $row['city'] ?? '',
                    'country' => $row['country'] ?? 'Philippines',
                    'password' => $hashed_password
                ]
            );

            $imported[] = $row['full_name'];
            $count++;
        }

        fclose($handle);

        // Create notification for admin
        $db->query(
            "INSERT INTO notifications (user_id, title, message, type, icon, created_at) 
             VALUES (:user_id, 'Guests Imported', :message, 'success', 'fa-file-import', NOW())",
            [
                'user_id' => $_SESSION['user_id'],
                'message' => "Successfully imported $count guests" . (!empty($errors) ? " with " . count($errors) . " errors" : "")
            ]
        );

        echo json_encode([
            'success' => true,
            'message' => "Successfully imported $count guests",
            'imported' => $imported,
            'errors' => $errors,
            'temp_password' => $temp_password ?? null
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