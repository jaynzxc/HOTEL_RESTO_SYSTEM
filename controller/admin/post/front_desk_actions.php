<?php
/**
 * POST Controller - Admin Front Desk Actions
 * Handles check-in, check-out, guest requests, and walk-in bookings
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

    // PROCESS CHECK-IN
    if ($action === 'process_checkin') {
        $booking_id = intval($_POST['booking_id'] ?? 0);
        $room_number = $_POST['room_number'] ?? '';
        $guest_count = $_POST['guest_count'] ?? '2 Adults';
        $id_type = $_POST['id_type'] ?? '';
        $id_number = $_POST['id_number'] ?? '';

        if (!$booking_id) {
            throw new Exception('Booking ID required');
        }

        if (empty($id_number)) {
            throw new Exception('ID number is required');
        }

        $db->beginTransaction();

        // Get booking details
        $booking = $db->query(
            "SELECT * FROM bookings WHERE id = :id",
            ['id' => $booking_id]
        )->fetch_one();

        if (!$booking) {
            throw new Exception('Booking not found');
        }

        // Update booking status
        $db->query(
            "UPDATE bookings SET 
                status = 'checked-in',
                check_in_time = NOW(),
                room_assigned = :room_number,
                updated_at = NOW()
             WHERE id = :id",
            [
                'id' => $booking_id,
                'room_number' => $room_number
            ]
        );

        // Update room availability
        $db->query(
            "UPDATE rooms SET is_available = 0 WHERE id = :room_id",
            ['room_id' => $booking['room_id']]
        );

        // Create interaction record for check-in
        $db->query(
            "INSERT INTO guest_interactions (
                user_id, admin_id, type, subject, message, status, created_at
             ) VALUES (
                :user_id, :admin_id, 'note', 'Guest Check-in', :message, 'done', NOW()
             )",
            [
                'user_id' => $booking['user_id'],
                'admin_id' => $_SESSION['user_id'],
                'message' => "Guest checked in to room {$room_number}. ID: {$id_type} - {$id_number}"
            ]
        );

        // Create notification
        $db->query(
            "INSERT INTO notifications (user_id, title, message, type, icon, created_at) 
             VALUES (:user_id, 'Check-in Completed', :message, 'success', 'fa-calendar-check', NOW())",
            [
                'user_id' => $_SESSION['user_id'],
                'message' => "Guest {$booking['guest_first_name']} {$booking['guest_last_name']} checked in to room {$room_number}"
            ]
        );

        $db->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Check-in completed successfully',
            'booking' => [
                'id' => $booking_id,
                'guest' => $booking['guest_first_name'] . ' ' . $booking['guest_last_name'],
                'room' => $room_number
            ]
        ]);
        exit();
    }

    // PROCESS CHECK-OUT
    elseif ($action === 'process_checkout') {
        $booking_id = intval($_POST['booking_id'] ?? 0);
        $room_charges = floatval($_POST['room_charges'] ?? 0);
        $mini_bar = floatval($_POST['mini_bar'] ?? 0);
        $restaurant = floatval($_POST['restaurant'] ?? 0);
        $payment_method = $_POST['payment_method'] ?? 'cash';

        if (!$booking_id) {
            throw new Exception('Booking ID required');
        }

        $db->beginTransaction();

        // Get booking details
        $booking = $db->query(
            "SELECT * FROM bookings WHERE id = :id",
            ['id' => $booking_id]
        )->fetch_one();

        if (!$booking) {
            throw new Exception('Booking not found');
        }

        $total = $room_charges + $mini_bar + $restaurant;

        // Generate payment reference
        $payment_ref = 'PAY-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));

        // Check if payments table uses 'amount' or 'total_amount' column
        // Try to insert with 'amount' first, if it fails, try 'total_amount'
        try {
            $db->query(
                "INSERT INTO payments (
                    payment_reference, user_id, booking_type, booking_id, amount,
                    payment_method, payment_status, payment_date, created_at
                 ) VALUES (
                    :ref, :user_id, 'hotel', :booking_id, :amount,
                    :method, 'completed', NOW(), NOW()
                 )",
                [
                    'ref' => $payment_ref,
                    'user_id' => $booking['user_id'],
                    'booking_id' => $booking_id,
                    'amount' => $total,
                    'method' => $payment_method
                ]
            );
        } catch (Exception $e) {
            // If 'amount' column doesn't exist, try 'total_amount'
            $db->query(
                "INSERT INTO payments (
                    payment_reference, user_id, booking_type, booking_id, total_amount,
                    payment_method, payment_status, payment_date, created_at
                 ) VALUES (
                    :ref, :user_id, 'hotel', :booking_id, :amount,
                    :method, 'completed', NOW(), NOW()
                 )",
                [
                    'ref' => $payment_ref,
                    'user_id' => $booking['user_id'],
                    'booking_id' => $booking_id,
                    'amount' => $total,
                    'method' => $payment_method
                ]
            );
        }

        // Update booking status
        $db->query(
            "UPDATE bookings SET 
                status = 'completed',
                check_out_time = NOW(),
                payment_status = 'paid',
                updated_at = NOW()
             WHERE id = :id",
            ['id' => $booking_id]
        );

        // Update room availability
        $db->query(
            "UPDATE rooms SET is_available = 1 WHERE id = :room_id",
            ['room_id' => $booking['room_id']]
        );

        // Create interaction record
        $db->query(
            "INSERT INTO guest_interactions (
                user_id, admin_id, type, subject, message, status, created_at
             ) VALUES (
                :user_id, :admin_id, 'note', 'Guest Check-out', :message, 'done', NOW()
             )",
            [
                'user_id' => $booking['user_id'],
                'admin_id' => $_SESSION['user_id'],
                'message' => "Guest checked out. Total paid: ₱" . number_format($total, 2)
            ]
        );

        // Create notification
        $db->query(
            "INSERT INTO notifications (user_id, title, message, type, icon, created_at) 
             VALUES (:user_id, 'Check-out Completed', :message, 'success', 'fa-calendar-xmark', NOW())",
            [
                'user_id' => $_SESSION['user_id'],
                'message' => "Guest {$booking['guest_first_name']} {$booking['guest_last_name']} checked out. Total: ₱" . number_format($total, 2)
            ]
        );

        $db->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Check-out completed successfully',
            'total' => $total,
            'payment_ref' => $payment_ref
        ]);
        exit();
    }

    // CREATE WALK-IN BOOKING
    elseif ($action === 'create_walkin') {
        $guest_name = trim($_POST['guest_name'] ?? '');
        $room_type = $_POST['room_type'] ?? '';
        $nights = intval($_POST['nights'] ?? 1);
        $contact = trim($_POST['contact'] ?? '');
        $adults = intval($_POST['adults'] ?? 2);
        $children = intval($_POST['children'] ?? 0);

        if (empty($guest_name) || empty($room_type) || empty($contact)) {
            throw new Exception('All fields are required');
        }

        // Split guest name
        $name_parts = explode(' ', trim($guest_name), 2);
        $first_name = $name_parts[0];
        $last_name = $name_parts[1] ?? '';

        // Get room details
        $room = $db->query(
            "SELECT * FROM rooms WHERE name LIKE :type LIMIT 1",
            ['type' => '%' . $room_type . '%']
        )->fetch_one();

        if (!$room) {
            throw new Exception('Room type not found');
        }

        // Calculate dates
        $check_in = date('Y-m-d');
        $check_out = date('Y-m-d', strtotime("+{$nights} days"));

        // Calculate totals
        $subtotal = $room['price'] * $nights;
        $tax = $subtotal * 0.12;
        $total = $subtotal + $tax;
        $points_earned = floor($total / 100) * 5;

        // Generate booking reference
        $reference = 'WK' . date('Ymd') . strtoupper(substr(uniqid(), -4));

        $db->beginTransaction();

        // Insert booking
        $db->query(
            "INSERT INTO bookings (
                booking_reference, guest_first_name, guest_last_name,
                guest_phone, check_in, check_out, nights,
                room_id, room_name, room_price, adults, children,
                subtotal, tax, total_amount, points_earned,
                status, payment_status, created_at, updated_at
            ) VALUES (
                :reference, :first_name, :last_name,
                :phone, :check_in, :check_out, :nights,
                :room_id, :room_name, :price, :adults, :children,
                :subtotal, :tax, :total, :points,
                'checked-in', 'unpaid', NOW(), NOW()
            )",
            [
                'reference' => $reference,
                'first_name' => $first_name,
                'last_name' => $last_name,
                'phone' => $contact,
                'check_in' => $check_in,
                'check_out' => $check_out,
                'nights' => $nights,
                'room_id' => $room['id'],
                'room_name' => $room['name'],
                'price' => $room['price'],
                'adults' => $adults,
                'children' => $children,
                'subtotal' => $subtotal,
                'tax' => $tax,
                'total' => $total,
                'points' => $points_earned
            ]
        );

        $booking_id = $db->lastInsertId();

        // Update room availability
        $db->query(
            "UPDATE rooms SET is_available = 0 WHERE id = :id",
            ['id' => $room['id']]
        );

        // Create notification
        $db->query(
            "INSERT INTO notifications (user_id, title, message, type, icon, created_at) 
             VALUES (:user_id, 'Walk-in Booking Created', :message, 'success', 'fa-user-plus', NOW())",
            [
                'user_id' => $_SESSION['user_id'],
                'message' => "Walk-in booking created for {$guest_name} - Room {$room['name']}"
            ]
        );

        $db->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Walk-in booking created successfully',
            'booking' => [
                'id' => $booking_id,
                'reference' => $reference,
                'guest' => $guest_name,
                'room' => $room['name'],
                'check_in' => $check_in,
                'check_out' => $check_out,
                'total' => $total
            ]
        ]);
        exit();
    }

    // UPDATE GUEST REQUEST
    elseif ($action === 'update_request') {
        $request_id = intval($_POST['request_id'] ?? 0);
        $status = $_POST['status'] ?? '';

        if (!$request_id) {
            throw new Exception('Request ID required');
        }

        $db->query(
            "UPDATE guest_interactions SET 
                status = :status,
                updated_at = NOW()
             WHERE id = :id",
            [
                'id' => $request_id,
                'status' => $status
            ]
        );

        echo json_encode([
            'success' => true,
            'message' => 'Request status updated'
        ]);
        exit();
    }

    // CONFIRM RESERVATION
    elseif ($action === 'confirm_reservation') {
        $booking_id = intval($_POST['booking_id'] ?? 0);

        if (!$booking_id) {
            throw new Exception('Booking ID required');
        }

        $db->query(
            "UPDATE bookings SET status = 'confirmed', updated_at = NOW() WHERE id = :id",
            ['id' => $booking_id]
        );

        echo json_encode([
            'success' => true,
            'message' => 'Reservation confirmed'
        ]);
        exit();
    }

    // NOTIFY GUEST
    elseif ($action === 'notify_guest') {
        $booking_id = intval($_POST['booking_id'] ?? 0);
        $message = trim($_POST['message'] ?? '');

        if (!$booking_id) {
            throw new Exception('Booking ID required');
        }

        // Get booking details
        $booking = $db->query(
            "SELECT * FROM bookings WHERE id = :id",
            ['id' => $booking_id]
        )->fetch_one();

        if ($booking['user_id']) {
            $db->query(
                "INSERT INTO notifications (user_id, title, message, type, icon, link, created_at) 
                 VALUES (:user_id, 'Reminder from Front Desk', :message, 'info', 'fa-bell', '/src/customer_portal/my_reservation.php', NOW())",
                [
                    'user_id' => $booking['user_id'],
                    'message' => $message ?: 'Reminder about your upcoming stay'
                ]
            );
        }

        echo json_encode([
            'success' => true,
            'message' => 'Notification sent to guest'
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