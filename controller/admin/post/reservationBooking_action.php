<?php
/**
 * POST Controller - Admin Reservation Actions
 * Handles creating, updating, confirming, editing, and cancelling reservations
 */

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

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

try {
    $config = require __DIR__ . '/../../../config/config.php';
    $db = new Database($config['database']);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database connection error: ' . $e->getMessage()
    ]);
    exit();
}

// Get user role from database
try {
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
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error checking user role: ' . $e->getMessage()
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

    // GET RESERVATION DETAILS FOR EDITING
    if ($action === 'get_reservation_details') {
        $booking_id = intval($_POST['booking_id'] ?? 0);

        if (!$booking_id) {
            echo json_encode([
                'success' => false,
                'message' => 'Booking ID required'
            ]);
            exit();
        }

        try {
            // First, get the booking details
            $booking = $db->query(
                "SELECT 
                    b.id,
                    b.booking_reference,
                    b.user_id,
                    b.guest_first_name,
                    b.guest_last_name,
                    b.guest_email,
                    b.guest_phone,
                    b.check_in,
                    b.check_out,
                    b.nights,
                    b.room_id,
                    b.room_name,
                    b.room_price,
                    b.adults,
                    b.children,
                    b.subtotal,
                    b.tax,
                    b.total_amount,
                    b.status,
                    b.payment_status,
                    b.special_requests,
                    b.points_earned,
                    b.points_awarded,
                    b.points_awarded_at,
                    b.created_at,
                    b.updated_at,
                    u.id as user_id,
                    u.full_name,
                    u.email,
                    u.phone,
                    u.member_tier,
                    u.loyalty_points
                 FROM bookings b
                 LEFT JOIN users u ON b.user_id = u.id
                 WHERE b.id = :id",
                ['id' => $booking_id]
            )->fetch_one();

            if (!$booking) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Booking not found'
                ]);
                exit();
            }

            // Format dates for input fields
            $booking['check_in'] = date('Y-m-d', strtotime($booking['check_in']));
            $booking['check_out'] = date('Y-m-d', strtotime($booking['check_out']));

            // Ensure points_awarded is set
            if (!isset($booking['points_awarded'])) {
                $booking['points_awarded'] = 0;
            }

            echo json_encode([
                'success' => true,
                'booking' => $booking
            ]);
            exit();
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ]);
            exit();
        }
    }

    // EDIT RESERVATION
    elseif ($action === 'edit_reservation') {
        $booking_id = intval($_POST['booking_id'] ?? 0);
        $guest_first_name = trim($_POST['guest_first_name'] ?? '');
        $guest_last_name = trim($_POST['guest_last_name'] ?? '');
        $guest_email = trim($_POST['guest_email'] ?? '');
        $guest_phone = trim($_POST['guest_phone'] ?? '');
        $check_in = $_POST['check_in'] ?? '';
        $check_out = $_POST['check_out'] ?? '';
        $adults = intval($_POST['adults'] ?? 2);
        $children = intval($_POST['children'] ?? 0);
        $room_id = $_POST['room_id'] ?? '';
        $special_requests = trim($_POST['special_requests'] ?? '');
        $status = $_POST['status'] ?? 'pending';
        $payment_status = $_POST['payment_status'] ?? 'unpaid';

        // Validation
        if (!$booking_id) {
            throw new Exception('Booking ID required');
        }
        if (empty($guest_first_name)) {
            throw new Exception('First name is required');
        }
        if (empty($guest_last_name)) {
            throw new Exception('Last name is required');
        }
        if (empty($check_in)) {
            throw new Exception('Check-in date is required');
        }
        if (empty($check_out)) {
            throw new Exception('Check-out date is required');
        }
        if (empty($room_id)) {
            throw new Exception('Room is required');
        }

        // Get current booking
        $currentBooking = $db->query(
            "SELECT * FROM bookings WHERE id = :id",
            ['id' => $booking_id]
        )->fetch_one();

        if (!$currentBooking) {
            throw new Exception('Booking not found');
        }

        // Get room details
        $room = $db->query(
            "SELECT * FROM rooms WHERE id = :id",
            ['id' => $room_id]
        )->fetch_one();

        if (!$room) {
            throw new Exception('Room not found');
        }

        // Calculate nights
        $checkInDate = new DateTime($check_in);
        $checkOutDate = new DateTime($check_out);
        $nights = $checkInDate->diff($checkOutDate)->days;

        if ($nights < 1) {
            throw new Exception('Check-out must be after check-in');
        }

        // Calculate totals
        $subtotal = floatval($room['price']) * $nights;
        $tax = $subtotal * 0.12;
        $total_amount = $subtotal + $tax;

        // Calculate points to earn
        $points_earned = floor($total_amount / 100) * 5;

        $db->beginTransaction();

        // Update booking
        $db->query(
            "UPDATE bookings SET 
                guest_first_name = :first_name,
                guest_last_name = :last_name,
                guest_email = :email,
                guest_phone = :phone,
                check_in = :check_in,
                check_out = :check_out,
                nights = :nights,
                room_id = :room_id,
                room_name = :room_name,
                room_price = :room_price,
                adults = :adults,
                children = :children,
                subtotal = :subtotal,
                tax = :tax,
                total_amount = :total,
                special_requests = :special_requests,
                status = :status,
                payment_status = :payment_status,
                points_earned = :points,
                updated_at = NOW()
             WHERE id = :id",
            [
                'id' => $booking_id,
                'first_name' => $guest_first_name,
                'last_name' => $guest_last_name,
                'email' => $guest_email,
                'phone' => $guest_phone,
                'check_in' => $check_in,
                'check_out' => $check_out,
                'nights' => $nights,
                'room_id' => $room_id,
                'room_name' => $room['name'],
                'room_price' => $room['price'],
                'adults' => $adults,
                'children' => $children,
                'subtotal' => $subtotal,
                'tax' => $tax,
                'total' => $total_amount,
                'special_requests' => $special_requests,
                'status' => $status,
                'payment_status' => $payment_status,
                'points' => $points_earned
            ]
        );

        // Handle room availability based on status changes
        $old_room_id = $currentBooking['room_id'];
        $old_status = $currentBooking['status'];

        if ($old_room_id != $room_id) {
            // Old room might become available if no other active bookings
            checkAndUpdateRoomAvailability($db, $old_room_id);
            // New room might become unavailable if this booking is active
            checkAndUpdateRoomAvailability($db, $room_id);
        } elseif ($old_status != $status) {
            // Status changed, check both old and new status effects
            if (in_array($status, ['confirmed', 'checked-in'])) {
                // Booking became active - make room unavailable
                $db->query(
                    "UPDATE rooms SET is_available = 0 WHERE id = :room_id",
                    ['room_id' => $room_id]
                );
            } elseif (
                in_array($old_status, ['confirmed', 'checked-in']) &&
                in_array($status, ['pending', 'cancelled', 'completed'])
            ) {
                // Booking became inactive - check if room should be available
                checkAndUpdateRoomAvailability($db, $room_id);
            }
        }

        // Create notification for admin
        $db->query(
            "INSERT INTO notifications (user_id, title, message, type, icon, created_at) 
             VALUES (:user_id, 'Booking Updated', :message, 'info', 'fa-pen-to-square', NOW())",
            [
                'user_id' => $_SESSION['user_id'],
                'message' => "Booking #{$currentBooking['booking_reference']} was updated"
            ]
        );

        // If booking has a user, notify them
        if ($currentBooking['user_id']) {
            $db->query(
                "INSERT INTO notifications (user_id, title, message, type, icon, link, created_at) 
                 VALUES (:user_id, 'Your Booking Was Updated', :message, 'info', 'fa-pen-to-square', '/src/customer_portal/my_reservation.php', NOW())",
                [
                    'user_id' => $currentBooking['user_id'],
                    'message' => "Your booking #{$currentBooking['booking_reference']} has been updated by staff"
                ]
            );
        }

        $db->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Booking updated successfully',
            'booking' => [
                'id' => $booking_id,
                'reference' => $currentBooking['booking_reference'],
                'guest' => $guest_first_name . ' ' . $guest_last_name,
                'total' => $total_amount,
                'points_earned' => $points_earned
            ]
        ]);
        exit();
    }

    // CREATE NEW BOOKING
    elseif ($action === 'create_booking') {
        $guest_name = trim($_POST['guest_name'] ?? '');
        $room_id = $_POST['room_id'] ?? '';
        $check_in = $_POST['check_in'] ?? '';
        $check_out = $_POST['check_out'] ?? '';
        $adults = intval($_POST['adults'] ?? 2);
        $children = intval($_POST['children'] ?? 0);
        $special_requests = trim($_POST['special_requests'] ?? '');

        // Validation
        if (empty($guest_name)) {
            throw new Exception('Guest name is required');
        }
        if (empty($room_id)) {
            throw new Exception('Room type is required');
        }
        if (empty($check_in)) {
            throw new Exception('Check-in date is required');
        }
        if (empty($check_out)) {
            throw new Exception('Check-out date is required');
        }

        // Get room details
        $room = $db->query(
            "SELECT * FROM rooms WHERE id = :id",
            ['id' => $room_id]
        )->fetch_one();

        if (!$room) {
            throw new Exception('Room not found');
        }

        // Calculate nights
        $checkInDate = new DateTime($check_in);
        $checkOutDate = new DateTime($check_out);
        $nights = $checkInDate->diff($checkOutDate)->days;

        if ($nights < 1) {
            throw new Exception('Check-out must be after check-in');
        }

        // Calculate totals
        $subtotal = floatval($room['price']) * $nights;
        $tax = $subtotal * 0.12;
        $total_amount = $subtotal + $tax;

        // Calculate points to earn (for tracking only)
        $points_earned = floor($total_amount / 100) * 5;

        // Split guest name into first and last
        $name_parts = explode(' ', trim($guest_name), 2);
        $first_name = $name_parts[0];
        $last_name = $name_parts[1] ?? '';

        // Generate booking reference
        $reference = 'HOT-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));

        $db->beginTransaction();

        // Insert booking
        $db->query(
            "INSERT INTO bookings (
                booking_reference, user_id, guest_first_name, guest_last_name,
                guest_email, guest_phone, check_in, check_out, nights,
                room_id, room_name, room_price, adults, children,
                subtotal, tax, total_amount, special_requests,
                points_earned, points_awarded, status, payment_status, created_at, updated_at
            ) VALUES (
                :reference, NULL, :first_name, :last_name,
                '', '', :check_in, :check_out, :nights,
                :room_id, :room_name, :room_price, :adults, :children,
                :subtotal, :tax, :total, :special_requests,
                :points, 0, 'pending', 'unpaid', NOW(), NOW()
            )",
            [
                'reference' => $reference,
                'first_name' => $first_name,
                'last_name' => $last_name,
                'check_in' => $check_in,
                'check_out' => $check_out,
                'nights' => $nights,
                'room_id' => $room_id,
                'room_name' => $room['name'],
                'room_price' => $room['price'],
                'adults' => $adults,
                'children' => $children,
                'subtotal' => $subtotal,
                'tax' => $tax,
                'total' => $total_amount,
                'special_requests' => $special_requests,
                'points' => $points_earned
            ]
        );

        $booking_id = $db->lastInsertId();

        // Create notification for admin
        $db->query(
            "INSERT INTO notifications (user_id, title, message, type, icon, created_at) 
             VALUES (:user_id, 'New Booking Created', :message, 'success', 'fa-plus-circle', NOW())",
            [
                'user_id' => $_SESSION['user_id'],
                'message' => "New booking #{$reference} created for {$guest_name}"
            ]
        );

        $db->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Booking created successfully',
            'booking' => [
                'id' => $booking_id,
                'reference' => $reference,
                'guest' => $guest_name,
                'room' => $room['name'],
                'check_in' => $check_in,
                'check_out' => $check_out,
                'nights' => $nights,
                'total' => $total_amount,
                'points_earned' => $points_earned
            ]
        ]);
        exit();
    }

    // UPDATE RESERVATION STATUS
    elseif ($action === 'update_status') {
        $booking_id = intval($_POST['booking_id'] ?? 0);
        $new_status = $_POST['status'] ?? '';

        if (!$booking_id) {
            throw new Exception('Booking ID required');
        }

        if (!in_array($new_status, ['pending', 'confirmed', 'checked-in', 'completed', 'cancelled'])) {
            throw new Exception('Invalid status');
        }

        $db->beginTransaction();

        // Get current booking
        $booking = $db->query(
            "SELECT * FROM bookings WHERE id = :id",
            ['id' => $booking_id]
        )->fetch_one();

        if (!$booking) {
            throw new Exception('Booking not found');
        }

        $old_status = $booking['status'];

        // Update status
        $db->query(
            "UPDATE bookings SET status = :status, updated_at = NOW() WHERE id = :id",
            [
                'status' => $new_status,
                'id' => $booking_id
            ]
        );

        // Handle room availability based on status change
        if ($new_status === 'confirmed' || $new_status === 'checked-in') {
            // Room is now occupied - mark as unavailable
            $db->query(
                "UPDATE rooms SET is_available = 0 WHERE id = :room_id",
                ['room_id' => $booking['room_id']]
            );
        } elseif (
            in_array($old_status, ['confirmed', 'checked-in']) &&
            in_array($new_status, ['pending', 'cancelled', 'completed'])
        ) {
            // Room was active but now is not - check if it should be available
            checkAndUpdateRoomAvailability($db, $booking['room_id']);
        }

        // Create notification for admin
        $db->query(
            "INSERT INTO notifications (user_id, title, message, type, icon, created_at) 
             VALUES (:user_id, 'Booking Status Updated', :message, 'info', 'fa-calendar-check', NOW())",
            [
                'user_id' => $_SESSION['user_id'],
                'message' => "Booking #{$booking['booking_reference']} status changed from {$old_status} to {$new_status}"
            ]
        );

        // If booking has a user, notify them
        if ($booking['user_id']) {
            $status_messages = [
                'confirmed' => 'Your booking has been confirmed!',
                'checked-in' => 'You have been checked in. Enjoy your stay!',
                'completed' => 'Your stay has been completed. Thank you!',
                'cancelled' => 'Your booking has been cancelled.'
            ];

            $user_message = $status_messages[$new_status] ?? "Your booking status has been updated to {$new_status}";

            $db->query(
                "INSERT INTO notifications (user_id, title, message, type, icon, link, created_at) 
                 VALUES (:user_id, 'Booking Status Update', :message, 'info', 'fa-info-circle', '/src/customer_portal/my_reservation.php', NOW())",
                [
                    'user_id' => $booking['user_id'],
                    'message' => $user_message . " (Booking #{$booking['booking_reference']})"
                ]
            );
        }

        $db->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Status updated successfully',
            'old_status' => $old_status,
            'new_status' => $new_status
        ]);
        exit();
    }

    // ADD POINTS TO USER (ADMIN ONLY)
    elseif ($action === 'add_points') {
        $booking_id = intval($_POST['booking_id'] ?? 0);

        if (!$booking_id) {
            throw new Exception('Booking ID required');
        }

        $db->beginTransaction();

        // Get booking with user
        $booking = $db->query(
            "SELECT b.*, u.id as user_id, u.loyalty_points 
             FROM bookings b
             LEFT JOIN users u ON b.user_id = u.id
             WHERE b.id = :id",
            ['id' => $booking_id]
        )->fetch_one();

        if (!$booking) {
            throw new Exception('Booking not found');
        }

        if (!$booking['user_id']) {
            throw new Exception('This booking is not associated with a registered user');
        }

        // Check if points already awarded
        if (isset($booking['points_awarded']) && $booking['points_awarded'] == 1) {
            throw new Exception('Points already awarded for this booking');
        }

        // Add points to user
        $db->query(
            "UPDATE users SET loyalty_points = loyalty_points + :points WHERE id = :user_id",
            [
                'points' => $booking['points_earned'],
                'user_id' => $booking['user_id']
            ]
        );

        // Mark points as awarded
        $db->query(
            "UPDATE bookings SET points_awarded = 1, points_awarded_at = NOW() WHERE id = :id",
            ['id' => $booking_id]
        );

        // Create notification for user
        $db->query(
            "INSERT INTO notifications (user_id, title, message, type, icon, link, created_at) 
             VALUES (:user_id, 'Loyalty Points Awarded!', :message, 'loyalty', 'fa-star', '/src/customer_portal/loyalty_rewards.php', NOW())",
            [
                'user_id' => $booking['user_id'],
                'message' => "You've earned {$booking['points_earned']} loyalty points for your booking #{$booking['booking_reference']}"
            ]
        );

        // Create notification for admin
        $db->query(
            "INSERT INTO notifications (user_id, title, message, type, icon, created_at) 
             VALUES (:user_id, 'Points Awarded', :message, 'success', 'fa-star', NOW())",
            [
                'user_id' => $_SESSION['user_id'],
                'message' => "Added {$booking['points_earned']} points to user for booking #{$booking['booking_reference']}"
            ]
        );

        $db->commit();

        echo json_encode([
            'success' => true,
            'message' => "{$booking['points_earned']} points added to user successfully"
        ]);
        exit();
    }

    // CHECK ROOM AVAILABILITY
    elseif ($action === 'check_availability') {
        $check_in = $_POST['check_in'] ?? '';
        $check_out = $_POST['check_out'] ?? '';

        if (empty($check_in) || empty($check_out)) {
            throw new Exception('Check-in and check-out dates required');
        }

        // Get available rooms (not booked for these dates)
        $available = $db->query(
            "SELECT r.* 
             FROM rooms r
             WHERE r.id NOT IN (
                 SELECT room_id FROM bookings 
                 WHERE (
                     (check_in <= :check_out AND check_out >= :check_in)
                 ) AND status NOT IN ('cancelled', 'completed')
             ) AND r.is_available = 1
             ORDER BY r.price ASC",
            [
                'check_in' => $check_in,
                'check_out' => $check_out
            ]
        )->find() ?: [];

        echo json_encode([
            'success' => true,
            'available_rooms' => $available,
            'count' => count($available)
        ]);
        exit();
    }

    // GET NOTIFICATIONS
    elseif ($action === 'get_notifications') {
        $notifications = $db->query(
            "SELECT * FROM notifications 
             WHERE user_id = :user_id 
             ORDER BY created_at DESC 
             LIMIT 20",
            ['user_id' => $_SESSION['user_id']]
        )->find() ?: [];

        // Mark as read
        $db->query(
            "UPDATE notifications SET is_read = 1, read_at = NOW() 
             WHERE user_id = :user_id AND is_read = 0",
            ['user_id' => $_SESSION['user_id']]
        );

        echo json_encode([
            'success' => true,
            'notifications' => $notifications
        ]);
        exit();
    }

    // MARK NOTIFICATION AS READ
    elseif ($action === 'mark_notification_read') {
        $notification_id = intval($_POST['notification_id'] ?? 0);

        if (!$notification_id) {
            throw new Exception('Notification ID required');
        }

        $db->query(
            "UPDATE notifications SET is_read = 1, read_at = NOW() 
             WHERE id = :id AND user_id = :user_id",
            [
                'id' => $notification_id,
                'user_id' => $_SESSION['user_id']
            ]
        );

        echo json_encode([
            'success' => true,
            'message' => 'Notification marked as read'
        ]);
        exit();
    } else {
        throw new Exception('Invalid action: ' . $action);
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

// Helper function to check and update room availability
function checkAndUpdateRoomAvailability($db, $room_id)
{
    if (!$room_id)
        return;

    // Check if there are any active bookings for this room
    $activeBookings = $db->query(
        "SELECT COUNT(*) as count 
         FROM bookings 
         WHERE room_id = :room_id 
         AND status IN ('confirmed', 'checked-in')
         AND check_out >= CURDATE()",
        ['room_id' => $room_id]
    )->fetch_one();

    // If no active bookings, mark room as available
    if ($activeBookings['count'] == 0) {
        $db->query(
            "UPDATE rooms SET is_available = 1 WHERE id = :room_id",
            ['room_id' => $room_id]
        );
    } else {
        $db->query(
            "UPDATE rooms SET is_available = 0 WHERE id = :room_id",
            ['room_id' => $room_id]
        );
    }
}

exit();
?>