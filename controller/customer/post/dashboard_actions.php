<?php
/**
 * POST Controller - Customer Dashboard Actions
 * Handles review submission, contact support, and other dashboard interactions
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

    // ==================== SUBMIT REVIEW ====================
    if ($action === 'submit_review') {
        $rating = intval($_POST['rating'] ?? 0);
        $review_text = trim($_POST['review_text'] ?? '');
        $experience = trim($_POST['experience'] ?? 'Hotel Stay');

        // Validate input
        if ($rating < 1 || $rating > 5) {
            throw new Exception('Please select a rating between 1 and 5 stars');
        }

        if (empty($review_text)) {
            throw new Exception('Please write a review');
        }

        $user_id = $_SESSION['user_id'];

        // Check if user has already rated 5 times today
        $today = date('Y-m-d');
        $ratingsToday = $db->query(
            "SELECT COUNT(*) as count FROM reviews 
             WHERE user_id = :user_id 
             AND DATE(created_at) = :today",
            ['user_id' => $user_id, 'today' => $today]
        )->fetch_one()['count'] ?? 0;

        if ($ratingsToday >= 5) {
            throw new Exception('You have reached the daily limit of 5 ratings. Please try again tomorrow.');
        }

        // Start transaction
        $db->beginTransaction();

        // Insert review
        $db->query(
            "INSERT INTO reviews (user_id, experience, rating, review_text, created_at) 
             VALUES (:user_id, :experience, :rating, :review_text, NOW())",
            [
                'user_id' => $user_id,
                'experience' => $experience,
                'rating' => $rating,
                'review_text' => $review_text
            ]
        );

        $review_id = $db->lastInsertId();

        // Award 1 loyalty point per review (max 5 per day)
        $points_earned = 1;

        $db->query(
            "UPDATE users SET loyalty_points = loyalty_points + :points WHERE id = :id",
            ['points' => $points_earned, 'id' => $user_id]
        );

        // Create notification for user
        $db->query(
            "INSERT INTO notifications (user_id, title, message, type, icon, link, created_at) 
             VALUES (:user_id, 'Review Submitted', :message, 'success', 'fa-star', '/src/customer_portal/my_reviews.php', NOW())",
            [
                'user_id' => $user_id,
                'message' => "Thank you for your feedback! You've earned $points_earned loyalty point. (" . ($ratingsToday + 1) . "/5 ratings today)"
            ]
        );

        // Create notification for admins about new review
        $admins = $db->query(
            "SELECT id FROM users WHERE role IN ('admin', 'staff')"
        )->find() ?: [];

        foreach ($admins as $admin) {
            $db->query(
                "INSERT INTO admin_notifications (admin_id, title, message, type, icon, link, created_at) 
                 VALUES (:admin_id, 'New Review', :message, 'info', 'fa-star', '/src/admin_portal/customer_management/customer_feedback.php', NOW())",
                [
                    'admin_id' => $admin['id'],
                    'message' => "A guest has left a $rating-star review: " . substr($review_text, 0, 50) . (strlen($review_text) > 50 ? '...' : '')
                ]
            );
        }

        $db->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Review submitted successfully',
            'points_earned' => $points_earned,
            'ratings_today' => $ratingsToday + 1,
            'remaining' => 5 - ($ratingsToday + 1)
        ]);
        exit();
    }

    // ==================== CONTACT SUPPORT ====================
    elseif ($action === 'contact_support') {
        $subject = trim($_POST['subject'] ?? '');
        $message = trim($_POST['message'] ?? '');

        if (empty($subject) || empty($message)) {
            throw new Exception('Subject and message are required');
        }

        $user_id = $_SESSION['user_id'];

        // Get user info
        $user = $db->query(
            "SELECT full_name, email, phone FROM users WHERE id = :id",
            ['id' => $user_id]
        )->fetch_one();

        // Notify admins about support request
        $admins = $db->query(
            "SELECT id FROM users WHERE role IN ('admin', 'staff')"
        )->find() ?: [];

        foreach ($admins as $admin) {
            $db->query(
                "INSERT INTO admin_notifications (admin_id, title, message, type, icon, link, created_at) 
                 VALUES (:admin_id, 'Support Request', :message, 'warning', 'fa-headset', '/src/admin_portal/customer_management/guest_interactions.php', NOW())",
                [
                    'admin_id' => $admin['id'],
                    'message' => "From: {$user['full_name']} - Subject: $subject"
                ]
            );
        }

        // Create notification for user confirming request
        $db->query(
            "INSERT INTO notifications (user_id, title, message, type, icon, created_at) 
             VALUES (:user_id, 'Support Request Received', :message, 'info', 'fa-headset', NOW())",
            [
                'user_id' => $user_id,
                'message' => "We've received your support request: '$subject'. A team member will respond within 24 hours."
            ]
        );

        echo json_encode([
            'success' => true,
            'message' => 'Support request sent successfully'
        ]);
        exit();
    }

    // ==================== UPDATE PROFILE ====================
    elseif ($action === 'update_profile') {
        $first_name = trim($_POST['first_name'] ?? '');
        $last_name = trim($_POST['last_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $preferences = trim($_POST['preferences'] ?? '');
        $allergies = trim($_POST['allergies'] ?? '');
        $birthday = $_POST['birthday'] ?? null;
        $anniversary = $_POST['anniversary'] ?? null;

        if (empty($first_name) || empty($last_name)) {
            throw new Exception('First name and last name are required');
        }

        $full_name = $first_name . ' ' . $last_name;
        $user_id = $_SESSION['user_id'];

        $db->query(
            "UPDATE users SET 
                first_name = :first_name,
                last_name = :last_name,
                full_name = :full_name,
                phone = :phone,
                preferences = :preferences,
                allergies = :allergies,
                birthday = :birthday,
                anniversary = :anniversary,
                updated_at = NOW()
             WHERE id = :id",
            [
                'first_name' => $first_name,
                'last_name' => $last_name,
                'full_name' => $full_name,
                'phone' => $phone,
                'preferences' => $preferences,
                'allergies' => $allergies,
                'birthday' => $birthday,
                'anniversary' => $anniversary,
                'id' => $user_id
            ]
        );

        // Create notification for user
        $db->query(
            "INSERT INTO notifications (user_id, title, message, type, icon, created_at) 
             VALUES (:user_id, 'Profile Updated', :message, 'success', 'fa-user-check', NOW())",
            [
                'user_id' => $user_id,
                'message' => 'Your profile has been updated successfully.'
            ]
        );

        echo json_encode([
            'success' => true,
            'message' => 'Profile updated successfully'
        ]);
        exit();
    }

    // ==================== GET USER DATA ====================
    elseif ($action === 'get_user_data') {
        $user_id = $_SESSION['user_id'];

        // Get user data
        $user = $db->query(
            "SELECT id, full_name, first_name, last_name, email, phone, 
                    loyalty_points, member_tier, preferences, allergies, 
                    birthday, anniversary, created_at
             FROM users WHERE id = :id",
            ['id' => $user_id]
        )->fetch_one();

        // Get balance
        $balance = $db->query(
            "SELECT total_balance, pending_balance, available_balance 
             FROM current_balance WHERE user_id = :user_id",
            ['user_id' => $user_id]
        )->fetch_one() ?: ['total_balance' => 0, 'pending_balance' => 0, 'available_balance' => 0];

        // Get recent bookings
        $bookings = $db->query(
            "SELECT id, booking_reference, room_name, check_in, check_out, status 
             FROM bookings WHERE user_id = :user_id AND booking_type = 'hotel'
             ORDER BY created_at DESC LIMIT 3",
            ['user_id' => $user_id]
        )->find() ?: [];

        // Get recent reservations
        $reservations = $db->query(
            "SELECT id, reservation_reference, reservation_date, reservation_time, guests, status 
             FROM restaurant_reservations WHERE user_id = :user_id
             ORDER BY created_at DESC LIMIT 3",
            ['user_id' => $user_id]
        )->find() ?: [];

        // Get recent orders
        $orders = $db->query(
            "SELECT id, order_reference, total_amount, status 
             FROM food_orders WHERE user_id = :user_id
             ORDER BY created_at DESC LIMIT 3",
            ['user_id' => $user_id]
        )->find() ?: [];

        echo json_encode([
            'success' => true,
            'user' => $user,
            'balance' => $balance,
            'bookings' => $bookings,
            'reservations' => $reservations,
            'orders' => $orders
        ]);
        exit();
    }

    // ==================== INVALID ACTION ====================
    else {
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