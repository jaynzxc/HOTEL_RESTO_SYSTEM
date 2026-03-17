<?php
/**
 * POST Controller - Admin Arrival Actions
 * Handles check-in, room assignment, and guest communication
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

    // CHECK-IN GUEST
    if ($action === 'check_in') {
        $booking_id = intval($_POST['booking_id'] ?? 0);
        $room_number = trim($_POST['room_number'] ?? '');
        $id_verification = $_POST['id_verification'] ?? 'verified';
        $payment_status = $_POST['payment_status'] ?? 'paid';

        if (!$booking_id || !$room_number) {
            throw new Exception('Booking ID and room number are required');
        }

        $db->beginTransaction();

        // Get booking details
        $booking = $db->query(
            "SELECT b.*, u.id as user_id, u.full_name, u.email, u.phone, u.member_tier 
             FROM bookings b
             LEFT JOIN users u ON b.user_id = u.id
             WHERE b.id = :id FOR UPDATE",
            ['id' => $booking_id]
        )->fetch_one();

        if (!$booking) {
            throw new Exception('Booking not found');
        }

        // Check if room is available
        $today = date('Y-m-d');
        $roomAvailable = $db->query(
            "SELECT COUNT(*) as count FROM bookings 
             WHERE (room_id = :room_number OR room_assigned = :room_number) 
                AND status IN ('confirmed', 'checked_in')
                AND check_in <= :today 
                AND check_out > :today
                AND id != :booking_id",
            [
                'room_number' => $room_number,
                'today' => $today,
                'booking_id' => $booking_id
            ]
        )->fetch_one()['count'] == 0;

        if (!$roomAvailable) {
            throw new Exception('Room is not available');
        }

        // Update booking
        $db->query(
            "UPDATE bookings SET 
                status = 'checked_in',
                room_assigned = :room_number,
                check_in_time = NOW(),
                updated_at = NOW()
             WHERE id = :id",
            [
                'room_number' => $room_number,
                'id' => $booking_id
            ]
        );

        // Create notification for staff
        $db->query(
            "INSERT INTO notifications (user_id, title, message, type, icon, created_at) 
             VALUES (:user_id, 'Guest Checked In', :message, 'success', 'fa-door-open', NOW())",
            [
                'user_id' => $_SESSION['user_id'],
                'message' => "Guest {$booking['guest_first_name']} {$booking['guest_last_name']} checked into Room $room_number"
            ]
        );

        // Notify guest if they have account
        if ($booking['user_id']) {
            $welcome_message = "Welcome to Lùcas! You have been checked into Room $room_number. ";
            $welcome_message .= "Your stay is from " . date('M d', strtotime($booking['check_in'])) . " to " . date('M d', strtotime($booking['check_out'])) . ". ";
            $welcome_message .= "Enjoy your stay!";

            $db->query(
                "INSERT INTO notifications (user_id, title, message, type, icon, link, created_at) 
                 VALUES (:user_id, 'Welcome to Lùcas!', :message, 'success', 'fa-hotel', '/src/customer_portal/my_reservation.php', NOW())",
                [
                    'user_id' => $booking['user_id'],
                    'message' => $welcome_message
                ]
            );
        }

        // Log interaction
        $db->query(
            "INSERT INTO guest_interactions (user_id, admin_id, type, message, created_at) 
             VALUES (:user_id, :admin_id, 'note', :message, NOW())",
            [
                'user_id' => $booking['user_id'] ?: 0,
                'admin_id' => $_SESSION['user_id'],
                'message' => "Guest checked in to Room $room_number"
            ]
        );

        $db->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Guest checked in successfully',
            'booking' => $booking
        ]);
        exit();
    }

    // ASSIGN ROOM
    elseif ($action === 'assign_room') {
        $booking_id = intval($_POST['booking_id'] ?? 0);
        $room_number = trim($_POST['room_number'] ?? '');

        if (!$booking_id || !$room_number) {
            throw new Exception('Booking ID and room number are required');
        }

        $db->beginTransaction();

        // Check if room is available
        $today = date('Y-m-d');
        $roomAvailable = $db->query(
            "SELECT COUNT(*) as count FROM bookings 
             WHERE (room_id = :room_number OR room_assigned = :room_number) 
                AND status IN ('confirmed', 'checked_in')
                AND check_in <= :today 
                AND check_out > :today
                AND id != :booking_id",
            [
                'room_number' => $room_number,
                'today' => $today,
                'booking_id' => $booking_id
            ]
        )->fetch_one()['count'] == 0;

        if (!$roomAvailable) {
            throw new Exception('Room is not available');
        }

        $db->query(
            "UPDATE bookings SET 
                room_assigned = :room_number,
                status = CASE WHEN status = 'pending' THEN 'confirmed' ELSE status END,
                updated_at = NOW()
             WHERE id = :id",
            [
                'room_number' => $room_number,
                'id' => $booking_id
            ]
        );

        $db->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Room assigned successfully'
        ]);
        exit();
    }

    // CONTACT GUEST
    elseif ($action === 'contact_guest') {
        $booking_id = intval($_POST['booking_id'] ?? 0);
        $message = trim($_POST['message'] ?? '');

        if (!$booking_id) {
            throw new Exception('Booking ID required');
        }

        $booking = $db->query(
            "SELECT b.*, u.id as user_id, u.email, u.phone 
             FROM bookings b
             LEFT JOIN users u ON b.user_id = u.id
             WHERE b.id = :id",
            ['id' => $booking_id]
        )->fetch_one();

        if (!$booking) {
            throw new Exception('Booking not found');
        }

        if ($booking['user_id']) {
            $default_message = "Hello {$booking['guest_first_name']}, this is the front desk. ";
            $default_message .= "Your check-in time is scheduled for today. Please proceed to the front desk when you arrive.";

            $db->query(
                "INSERT INTO notifications (user_id, title, message, type, icon, created_at) 
                 VALUES (:user_id, 'Message from Front Desk', :message, 'info', 'fa-concierge-bell', NOW())",
                [
                    'user_id' => $booking['user_id'],
                    'message' => $message ?: $default_message
                ]
            );
        }

        // Log interaction
        $db->query(
            "INSERT INTO guest_interactions (user_id, admin_id, type, message, created_at) 
             VALUES (:user_id, :admin_id, 'note', :message, NOW())",
            [
                'user_id' => $booking['user_id'] ?: 0,
                'admin_id' => $_SESSION['user_id'],
                'message' => $message ?: "Contacted guest for check-in"
            ]
        );

        echo json_encode([
            'success' => true,
            'message' => 'Message sent to guest'
        ]);
        exit();
    }

    // GET AVAILABLE ROOMS
    elseif ($action === 'get_available_rooms') {
        $date = $_POST['date'] ?? date('Y-m-d');

        $rooms = $db->query(
            "SELECT 
                r.id,
                r.name,
                r.price,
                r.max_occupancy,
                r.beds,
                r.view
             FROM rooms r
             WHERE r.is_available = 1
                AND NOT EXISTS (
                    SELECT 1 FROM bookings b 
                    WHERE (b.room_id = r.id OR b.room_assigned = r.id)
                        AND b.status IN ('confirmed', 'checked_in')
                        AND b.check_in <= :date 
                        AND b.check_out > :date
                )
             ORDER BY r.id ASC",
            ['date' => $date]
        )->find() ?: [];

        echo json_encode([
            'success' => true,
            'rooms' => $rooms
        ]);
        exit();
    }

    // GET GUEST DETAILS
    elseif ($action === 'get_guest_details') {
        $booking_id = intval($_POST['booking_id'] ?? 0);

        if (!$booking_id) {
            throw new Exception('Booking ID required');
        }

        $details = $db->query(
            "SELECT 
                b.*,
                u.full_name,
                u.email,
                u.phone,
                u.member_tier,
                u.loyalty_points,
                u.preferences,
                u.allergies,
                u.birthday,
                u.anniversary
             FROM bookings b
             LEFT JOIN users u ON b.user_id = u.id
             WHERE b.id = :id",
            ['id' => $booking_id]
        )->fetch_one();

        if (!$details) {
            throw new Exception('Booking not found');
        }

        // Get guest's stay history
        $history = $db->query(
            "SELECT 
                booking_reference,
                check_in,
                check_out,
                room_name,
                status
             FROM bookings
             WHERE user_id = :user_id AND id != :id
             ORDER BY check_in DESC
             LIMIT 5",
            [
                'user_id' => $details['user_id'] ?: 0,
                'id' => $booking_id
            ]
        )->find() ?: [];

        echo json_encode([
            'success' => true,
            'guest' => $details,
            'history' => $history
        ]);
        exit();
    }

    // GET TODAY'S STATISTICS
    elseif ($action === 'get_statistics') {
        $today = date('Y-m-d');

        $stats = $db->query(
            "SELECT 
                COUNT(*) as total_arrivals,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed,
                SUM(CASE WHEN status = 'checked_in' THEN 1 ELSE 0 END) as checked_in,
                SUM(CASE WHEN room_assigned IS NOT NULL THEN 1 ELSE 0 END) as rooms_assigned
             FROM bookings
             WHERE DATE(check_in) = :today AND status != 'cancelled'",
            ['today' => $today]
        )->fetch_one();

        echo json_encode([
            'success' => true,
            'stats' => $stats
        ]);
        exit();
    }

    // EXPORT ARRIVALS
    elseif ($action === 'export_arrivals') {
        $format = $_POST['format'] ?? 'csv';

        $arrivals = $db->query(
            "SELECT 
                booking_reference as 'Booking #',
                CONCAT(guest_first_name, ' ', guest_last_name) as 'Guest Name',
                room_name as 'Room Type',
                COALESCE(room_assigned, 'Not Assigned') as 'Room Assigned',
                DATE_FORMAT(check_in, '%M %d, %Y') as 'Check-in Date',
                DATE_FORMAT(check_in_time, '%h:%i %p') as 'Check-in Time',
                nights as 'Nights',
                adults as 'Adults',
                children as 'Children',
                status as 'Status',
                payment_status as 'Payment',
                total_amount as 'Total Amount',
                special_requests as 'Special Requests'
             FROM bookings
             WHERE DATE(check_in) = CURDATE() AND status != 'cancelled'
             ORDER BY check_in ASC",
            []
        )->find() ?: [];

        if ($format === 'csv') {
            $filename = 'arrivals_' . date('Y-m-d') . '.csv';
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="' . $filename . '"');

            $output = fopen('php://output', 'w');

            if (!empty($arrivals)) {
                fputcsv($output, array_keys($arrivals[0]));
                foreach ($arrivals as $row) {
                    fputcsv($output, $row);
                }
            }
            fclose($output);
            exit();
        }
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