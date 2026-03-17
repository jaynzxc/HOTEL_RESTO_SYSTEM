<?php
/**
 * POST Controller - Admin Departure Actions
 * Handles check-out, express checkout, and housekeeping notifications
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

    // CHECK-OUT GUEST
    if ($action === 'check_out') {
        $booking_id = intval($_POST['booking_id'] ?? 0);
        $payment_method = $_POST['payment_method'] ?? 'cash';
        $room_condition = $_POST['room_condition'] ?? 'good';
        $additional_charges = floatval($_POST['additional_charges'] ?? 0);

        if (!$booking_id) {
            throw new Exception('Booking ID is required');
        }

        $db->beginTransaction();

        // Get booking details
        $booking = $db->query(
            "SELECT b.*, u.id as user_id, u.full_name, u.email 
             FROM bookings b
             LEFT JOIN users u ON b.user_id = u.id
             WHERE b.id = :id FOR UPDATE",
            ['id' => $booking_id]
        )->fetch_one();

        if (!$booking) {
            throw new Exception('Booking not found');
        }

        // Update booking status to completed with check-out time
        $db->query(
            "UPDATE bookings SET 
                status = 'completed',
                check_out_time = NOW(),
                updated_at = NOW()
             WHERE id = :id",
            ['id' => $booking_id]
        );

        // If there are additional charges, create a payment record
        if ($additional_charges > 0) {
            $payment_ref = "PAY-" . date('Ymd') . "-" . strtoupper(substr(uniqid(), -6));

            $db->query(
                "INSERT INTO payments (
                    payment_reference, user_id, booking_type, booking_id,
                    amount, payment_method, payment_status, approval_status,
                    payment_date, created_at
                ) VALUES (
                    :ref, :user_id, 'hotel', :booking_id,
                    :amount, :method, 'completed', 'approved',
                    NOW(), NOW()
                )",
                [
                    'ref' => $payment_ref,
                    'user_id' => $booking['user_id'],
                    'booking_id' => $booking_id,
                    'amount' => $additional_charges,
                    'method' => $payment_method
                ]
            );

            // Update current balance
            if ($booking['user_id']) {
                $db->query(
                    "UPDATE current_balance 
                     SET total_balance = total_balance - :amount,
                         available_balance = available_balance - :amount
                     WHERE user_id = :user_id",
                    [
                        'amount' => $additional_charges,
                        'user_id' => $booking['user_id']
                    ]
                );
            }
        }

        // Log room condition for housekeeping
        $db->query(
            "INSERT INTO room_maintenance (room_id, condition_status, reported_at, reported_by, notes) 
             VALUES (:room_id, :condition, NOW(), :reported_by, :notes)
             ON DUPLICATE KEY UPDATE 
                condition_status = VALUES(condition_status),
                reported_at = VALUES(reported_at),
                reported_by = VALUES(reported_by),
                notes = VALUES(notes)",
            [
                'room_id' => $booking['room_assigned'],
                'condition' => $room_condition,
                'reported_by' => $_SESSION['user_id'],
                'notes' => "Room vacated on " . date('Y-m-d H:i:s')
            ]
        );

        // Create notification for staff
        $db->query(
            "INSERT INTO notifications (user_id, title, message, type, icon, created_at) 
             VALUES (:user_id, 'Guest Checked Out', :message, 'success', 'fa-door-open', NOW())",
            [
                'user_id' => $_SESSION['user_id'],
                'message' => "Guest {$booking['guest_first_name']} {$booking['guest_last_name']} checked out from Room {$booking['room_assigned']}"
            ]
        );

        // Notify housekeeping
        $db->query(
            "INSERT INTO notifications (user_id, title, message, type, icon, link, created_at) 
             VALUES (NULL, 'Room Ready for Cleaning', :message, 'info', 'fa-broom', '/src/admin_portal/hotel_management/housekeeping.php', NOW())",
            [
                'message' => "Room {$booking['room_assigned']} needs cleaning (Condition: $room_condition)"
            ]
        );

        $db->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Guest checked out successfully'
        ]);
        exit();
    }

    // BULK CHECK-OUT (Express)
    elseif ($action === 'bulk_checkout') {
        $booking_ids = json_decode($_POST['booking_ids'] ?? '[]', true);

        if (empty($booking_ids)) {
            throw new Exception('No bookings selected');
        }

        $db->beginTransaction();
        $count = 0;
        $rooms = [];

        foreach ($booking_ids as $booking_id) {
            // Verify booking exists and has zero balance
            $booking = $db->query(
                "SELECT b.*, cb.total_balance 
                 FROM bookings b
                 LEFT JOIN current_balance cb ON b.user_id = cb.user_id
                 WHERE b.id = :id AND b.status = 'checked_in'",
                ['id' => $booking_id]
            )->fetch_one();

            if ($booking && $booking['total_balance'] == 0) {
                $db->query(
                    "UPDATE bookings SET 
                        status = 'completed',
                        check_out_time = NOW(),
                        updated_at = NOW()
                     WHERE id = :id",
                    ['id' => $booking_id]
                );

                $rooms[] = $booking['room_assigned'];
                $count++;

                // Log for housekeeping
                if ($booking['room_assigned']) {
                    $db->query(
                        "INSERT INTO room_maintenance (room_id, condition_status, reported_at, reported_by, notes) 
                         VALUES (:room_id, 'good', NOW(), :reported_by, 'Express check-out')
                         ON DUPLICATE KEY UPDATE 
                            condition_status = 'good',
                            reported_at = NOW()",
                        [
                            'room_id' => $booking['room_assigned'],
                            'reported_by' => $_SESSION['user_id']
                        ]
                    );
                }
            }
        }

        // Notify housekeeping about bulk check-out
        if (!empty($rooms)) {
            $room_list = implode(', ', array_filter($rooms));
            $db->query(
                "INSERT INTO notifications (user_id, title, message, type, icon, link, created_at) 
                 VALUES (NULL, 'Bulk Check-out Complete', :message, 'info', 'fa-broom', '/src/admin_portal/hotel_management/housekeeping.php', NOW())",
                [
                    'message' => "$count rooms checked out via express: $room_list"
                ]
            );
        }

        $db->commit();

        echo json_encode([
            'success' => true,
            'message' => "$count guests checked out successfully",
            'count' => $count,
            'rooms' => $rooms
        ]);
        exit();
    }

    // NOTIFY HOUSEKEEPING
    elseif ($action === 'notify_housekeeping') {
        $room_ids = json_decode($_POST['room_ids'] ?? '[]', true);

        if (empty($room_ids)) {
            // If no specific rooms, get all rooms that need cleaning
            $rooms = $db->query(
                "SELECT room_assigned FROM bookings 
                 WHERE status IN ('checked_in', 'completed') 
                    AND DATE(check_out) = CURDATE()
                    AND room_assigned IS NOT NULL"
            )->find() ?: [];

            $room_ids = array_column($rooms, 'room_assigned');
        }

        if (empty($room_ids)) {
            throw new Exception('No rooms need cleaning');
        }

        $room_list = implode(', ', $room_ids);
        $count = count($room_ids);

        $db->query(
            "INSERT INTO notifications (user_id, title, message, type, icon, link, created_at) 
             VALUES (NULL, 'Housekeeping Alert', :message, 'info', 'fa-broom', '/src/admin_portal/hotel_management/housekeeping.php', NOW())",
            [
                'message' => "$count rooms need cleaning: $room_list"
            ]
        );

        echo json_encode([
            'success' => true,
            'message' => "Housekeeping notified for $count rooms",
            'rooms' => $room_ids
        ]);
        exit();
    }

    // GET GUEST BILL DETAILS
    elseif ($action === 'get_bill_details') {
        $booking_id = intval($_POST['booking_id'] ?? 0);

        if (!$booking_id) {
            throw new Exception('Booking ID required');
        }

        $booking = $db->query(
            "SELECT 
                b.*,
                COALESCE(cb.total_balance, 0) as total_balance,
                COALESCE(SUM(fo.total_amount), 0) as food_charges
             FROM bookings b
             LEFT JOIN current_balance cb ON b.user_id = cb.user_id
             LEFT JOIN food_orders fo ON b.user_id = fo.user_id 
                AND DATE(fo.created_at) BETWEEN b.check_in AND b.check_out
             WHERE b.id = :id
             GROUP BY b.id",
            ['id' => $booking_id]
        )->fetch_one();

        if (!$booking) {
            throw new Exception('Booking not found');
        }

        // Get room maintenance history
        $maintenance = $db->query(
            "SELECT * FROM room_maintenance 
             WHERE room_id = :room_id 
             ORDER BY reported_at DESC LIMIT 1",
            ['room_id' => $booking['room_assigned']]
        )->fetch_one();

        echo json_encode([
            'success' => true,
            'bill' => [
                'room_charges' => $booking['total_amount'],
                'food_charges' => $booking['food_charges'] ?? 0,
                'service_charges' => 0,
                'total' => $booking['total_amount'] + ($booking['food_charges'] ?? 0),
                'balance' => $booking['total_balance'],
                'paid' => $booking['total_balance'] == 0
            ],
            'guest' => [
                'name' => $booking['guest_first_name'] . ' ' . $booking['guest_last_name'],
                'room' => $booking['room_assigned'],
                'check_in' => $booking['check_in'],
                'check_out' => $booking['check_out'],
                'nights' => $booking['nights'],
                'status' => $booking['status']
            ],
            'maintenance' => $maintenance
        ]);
        exit();
    }

    // GET STATISTICS
    elseif ($action === 'get_statistics') {
        $today = date('Y-m-d');

        $stats = $db->query(
            "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'checked_in' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                COUNT(DISTINCT room_assigned) as rooms
             FROM bookings
             WHERE DATE(check_out) = :today",
            ['today' => $today]
        )->fetch_one();

        echo json_encode([
            'success' => true,
            'stats' => $stats
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