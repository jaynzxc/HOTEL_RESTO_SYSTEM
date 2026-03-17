<?php
/**
 * POST Controller - Admin Reservation Actions
 * Handles confirming, cancelling, and managing reservations
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

    // CONFIRM RESERVATION - Check balance first
    if ($action === 'confirm_reservation') {
        $booking_id = intval($_POST['booking_id'] ?? 0);

        if (!$booking_id) {
            throw new Exception('Booking ID required');
        }

        // Get booking details with user balance info
        $booking = $db->query(
            "SELECT b.*, u.id as user_id, u.full_name, u.email, u.loyalty_points,
                    COALESCE(cb.total_balance, 0) as outstanding_balance
             FROM bookings b
             LEFT JOIN users u ON b.user_id = u.id
             LEFT JOIN current_balance cb ON u.id = cb.user_id
             WHERE b.id = :id",
            ['id' => $booking_id]
        )->fetch_one();

        if (!$booking) {
            throw new Exception('Booking not found');
        }

        // Check if guest has outstanding balance
        $hasOutstandingBalance = $booking['outstanding_balance'] > 0;

        // If there's an outstanding balance, return warning before confirming
        if ($hasOutstandingBalance) {
            echo json_encode([
                'success' => false,
                'requires_action' => true,
                'has_outstanding_balance' => true,
                'message' => 'This guest has an outstanding balance',
                'warning' => "Guest has an outstanding balance of ₱" . number_format($booking['outstanding_balance'], 2),
                'balance_amount' => $booking['outstanding_balance'],
                'booking' => [
                    'id' => $booking['id'],
                    'guest_name' => $booking['guest_first_name'] . ' ' . $booking['guest_last_name'],
                    'booking_ref' => $booking['booking_reference'],
                    'check_in' => $booking['check_in'],
                    'check_out' => $booking['check_out'],
                    'total_amount' => $booking['total_amount']
                ]
            ]);
            exit();
        }

        $db->beginTransaction();

        // Calculate points to award (5 points per ₱100 of subtotal)
        $points_to_award = floor($booking['subtotal'] / 100) * 5;

        // Update booking status
        $db->query(
            "UPDATE bookings SET 
                status = 'confirmed',
                updated_at = NOW()
             WHERE id = :id",
            ['id' => $booking_id]
        );

        // Award points to user if they have an account
        if ($booking['user_id'] && $points_to_award > 0) {
            $db->query(
                "UPDATE users SET loyalty_points = loyalty_points + :points 
                 WHERE id = :user_id",
                [
                    'points' => $points_to_award,
                    'user_id' => $booking['user_id']
                ]
            );
        }

        // Create notification for guest if they have account
        if ($booking['user_id']) {
            $message = "Your reservation for {$booking['room_name']} from " . date('M d', strtotime($booking['check_in'])) . " to " . date('M d', strtotime($booking['check_out'])) . " has been confirmed.";

            if ($points_to_award > 0) {
                $message .= " You earned {$points_to_award} loyalty points!";
            }

            $db->query(
                "INSERT INTO notifications (user_id, title, message, type, icon, link, created_at) 
                 VALUES (:user_id, 'Reservation Confirmed', :message, 'success', 'fa-calendar-check', '/src/customer_portal/my_reservation.php', NOW())",
                [
                    'user_id' => $booking['user_id'],
                    'message' => $message
                ]
            );
        }

        // Create notification for staff
        $staffMessage = "Reservation for {$booking['guest_first_name']} {$booking['guest_last_name']} has been confirmed";
        if ($points_to_award > 0) {
            $staffMessage .= " (Awarded {$points_to_award} points)";
        }

        $db->query(
            "INSERT INTO notifications (user_id, title, message, type, icon, created_at) 
             VALUES (:user_id, 'Reservation Confirmed', :message, 'success', 'fa-check-circle', NOW())",
            [
                'user_id' => $_SESSION['user_id'],
                'message' => $staffMessage
            ]
        );

        $db->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Reservation confirmed successfully' . ($points_to_award > 0 ? " and {$points_to_award} points awarded." : ""),
            'points_awarded' => $points_to_award
        ]);
        exit();
    }

    // FORCE CONFIRM RESERVATION (ignore balance)
    elseif ($action === 'confirm_reservation_force') {
        $booking_id = intval($_POST['booking_id'] ?? 0);
        $ignore_balance = $_POST['ignore_balance'] ?? 0;

        if (!$booking_id) {
            throw new Exception('Booking ID required');
        }

        $db->beginTransaction();

        // Get booking details
        $booking = $db->query(
            "SELECT b.*, u.id as user_id, u.full_name, u.email,
                    COALESCE(cb.total_balance, 0) as outstanding_balance
             FROM bookings b
             LEFT JOIN users u ON b.user_id = u.id
             LEFT JOIN current_balance cb ON u.id = cb.user_id
             WHERE b.id = :id",
            ['id' => $booking_id]
        )->fetch_one();

        if (!$booking) {
            throw new Exception('Booking not found');
        }

        // Calculate points to award
        $points_to_award = floor($booking['subtotal'] / 100) * 5;

        // Update booking status
        $db->query(
            "UPDATE bookings SET 
                status = 'confirmed',
                updated_at = NOW()
             WHERE id = :id",
            ['id' => $booking_id]
        );

        // Award points to user if they have an account
        if ($booking['user_id'] && $points_to_award > 0) {
            $db->query(
                "UPDATE users SET loyalty_points = loyalty_points + :points 
                 WHERE id = :user_id",
                [
                    'points' => $points_to_award,
                    'user_id' => $booking['user_id']
                ]
            );
        }

        // Create notification for guest if they have account
        if ($booking['user_id']) {
            $message = "Your reservation for {$booking['room_name']} from " . date('M d', strtotime($booking['check_in'])) . " to " . date('M d', strtotime($booking['check_out'])) . " has been confirmed.";

            if ($booking['outstanding_balance'] > 0) {
                $message .= " Please note: You have an outstanding balance of ₱" . number_format($booking['outstanding_balance'], 2) . ". Please settle this before check-in.";
            }

            if ($points_to_award > 0) {
                $message .= " You earned {$points_to_award} loyalty points!";
            }

            $db->query(
                "INSERT INTO notifications (user_id, title, message, type, icon, link, created_at) 
                 VALUES (:user_id, 'Reservation Confirmed', :message, 'success', 'fa-calendar-check', '/src/customer_portal/my_reservation.php', NOW())",
                [
                    'user_id' => $booking['user_id'],
                    'message' => $message
                ]
            );
        }

        // Create notification for staff
        $staffMessage = "Reservation for {$booking['guest_first_name']} {$booking['guest_last_name']} has been confirmed";
        if ($booking['outstanding_balance'] > 0) {
            $staffMessage .= " (Outstanding balance: ₱" . number_format($booking['outstanding_balance'], 2) . ")";
        }
        if ($points_to_award > 0) {
            $staffMessage .= " (Awarded {$points_to_award} points)";
        }

        $db->query(
            "INSERT INTO notifications (user_id, title, message, type, icon, created_at) 
             VALUES (:user_id, 'Reservation Confirmed', :message, 'success', 'fa-check-circle', NOW())",
            [
                'user_id' => $_SESSION['user_id'],
                'message' => $staffMessage
            ]
        );

        $db->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Reservation confirmed successfully',
            'has_outstanding_balance' => $booking['outstanding_balance'] > 0,
            'balance_amount' => $booking['outstanding_balance'],
            'points_awarded' => $points_to_award
        ]);
        exit();
    }

    // CANCEL RESERVATION
    elseif ($action === 'cancel_reservation') {
        $booking_id = intval($_POST['booking_id'] ?? 0);
        $reason = trim($_POST['reason'] ?? '');

        if (!$booking_id) {
            throw new Exception('Booking ID required');
        }

        $db->beginTransaction();

        // Get booking details with points calculation
        $booking = $db->query(
            "SELECT b.*, u.id as user_id, u.full_name, u.email, u.loyalty_points
             FROM bookings b
             LEFT JOIN users u ON b.user_id = u.id
             WHERE b.id = :id",
            ['id' => $booking_id]
        )->fetch_one();

        if (!$booking) {
            throw new Exception('Booking not found');
        }

        // Calculate points that were earned from this booking (for deduction if already confirmed)
        $points_earned = floor($booking['subtotal'] / 100) * 5;

        // Update booking status
        $db->query(
            "UPDATE bookings SET 
                status = 'cancelled',
                updated_at = NOW()
             WHERE id = :id",
            ['id' => $booking_id]
        );

        // If booking was confirmed, deduct the points that were awarded
        if ($booking['status'] === 'confirmed' && $booking['user_id'] && $points_earned > 0) {
            $db->query(
                "UPDATE users SET loyalty_points = loyalty_points - :points 
                 WHERE id = :user_id AND loyalty_points >= :points",
                [
                    'points' => $points_earned,
                    'user_id' => $booking['user_id']
                ]
            );
        }

        // Check if guest has outstanding balance (for notification)
        $balanceData = null;
        if ($booking['user_id']) {
            $balanceData = $db->query(
                "SELECT total_balance FROM current_balance WHERE user_id = :user_id",
                ['user_id' => $booking['user_id']]
            )->fetch_one();
        }

        // Create notification for guest if they have account
        if ($booking['user_id']) {
            $message = "Your reservation for {$booking['room_name']} from " . date('M d', strtotime($booking['check_in'])) . " to " . date('M d', strtotime($booking['check_out'])) . " has been cancelled.";

            if ($reason) {
                $message .= " Reason: $reason";
            }

            if ($booking['status'] === 'confirmed' && $points_earned > 0) {
                $message .= " {$points_earned} points have been deducted.";
            }

            $db->query(
                "INSERT INTO notifications (user_id, title, message, type, icon, link, created_at) 
                 VALUES (:user_id, 'Reservation Cancelled', :message, 'warning', 'fa-ban', '/src/customer_portal/my_reservation.php', NOW())",
                [
                    'user_id' => $booking['user_id'],
                    'message' => $message
                ]
            );
        }

        // Create notification for staff
        $staffMessage = "Reservation for {$booking['guest_first_name']} {$booking['guest_last_name']} has been cancelled";

        if ($reason) {
            $staffMessage .= ": $reason";
        }

        if ($booking['status'] === 'confirmed' && $points_earned > 0) {
            $staffMessage .= " (Deducted {$points_earned} points)";
        }

        if ($balanceData && $balanceData['total_balance'] > 0) {
            $staffMessage .= " (Outstanding balance: ₱" . number_format($balanceData['total_balance'], 2) . ")";
        }

        $db->query(
            "INSERT INTO notifications (user_id, title, message, type, icon, created_at) 
             VALUES (:user_id, 'Reservation Cancelled', :message, 'warning', 'fa-times-circle', NOW())",
            [
                'user_id' => $_SESSION['user_id'],
                'message' => $staffMessage
            ]
        );

        $db->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Reservation cancelled successfully' .
                ($booking['status'] === 'confirmed' && $points_earned > 0 ? " and {$points_earned} points deducted." : ""),
            'points_deducted' => ($booking['status'] === 'confirmed' && $points_earned > 0) ? $points_earned : 0
        ]);
        exit();
    }

    // CANCEL RESERVATION WITH BALANCE NOTICE
    elseif ($action === 'cancel_reservation_with_balance') {
        $booking_id = intval($_POST['booking_id'] ?? 0);
        $balance_amount = floatval($_POST['balance_amount'] ?? 0);
        $reason = trim($_POST['reason'] ?? '');

        if (!$booking_id) {
            throw new Exception('Booking ID required');
        }

        $db->beginTransaction();

        // Get booking details
        $booking = $db->query(
            "SELECT b.*, u.id as user_id, u.full_name, u.email, u.loyalty_points
             FROM bookings b
             LEFT JOIN users u ON b.user_id = u.id
             WHERE b.id = :id",
            ['id' => $booking_id]
        )->fetch_one();

        if (!$booking) {
            throw new Exception('Booking not found');
        }

        // Calculate points that were earned (if any)
        $points_earned = floor($booking['subtotal'] / 100) * 5;

        // Update booking status
        $db->query(
            "UPDATE bookings SET 
                status = 'cancelled',
                updated_at = NOW()
             WHERE id = :id",
            ['id' => $booking_id]
        );

        // If booking was confirmed, deduct points
        if ($booking['status'] === 'confirmed' && $booking['user_id'] && $points_earned > 0) {
            $db->query(
                "UPDATE users SET loyalty_points = loyalty_points - :points 
                 WHERE id = :user_id AND loyalty_points >= :points",
                [
                    'points' => $points_earned,
                    'user_id' => $booking['user_id']
                ]
            );
        }

        // Create notification for guest if they have account
        if ($booking['user_id']) {
            $message = "Your reservation for {$booking['room_name']} from " . date('M d', strtotime($booking['check_in'])) . " to " . date('M d', strtotime($booking['check_out'])) . " has been cancelled due to outstanding balance.";

            if ($balance_amount > 0) {
                $message .= " Please settle your outstanding balance of ₱" . number_format($balance_amount, 2) . " to make new reservations.";
            }

            if ($reason) {
                $message .= " Reason: $reason";
            }

            if ($booking['status'] === 'confirmed' && $points_earned > 0) {
                $message .= " {$points_earned} points have been deducted.";
            }

            $db->query(
                "INSERT INTO notifications (user_id, title, message, type, icon, link, created_at) 
                 VALUES (:user_id, 'Reservation Cancelled - Outstanding Balance', :message, 'warning', 'fa-ban', '/src/customer_portal/my_reservation.php', NOW())",
                [
                    'user_id' => $booking['user_id'],
                    'message' => $message
                ]
            );
        }

        // Create notification for staff
        $staffMessage = "Reservation for {$booking['guest_first_name']} {$booking['guest_last_name']} has been cancelled due to outstanding balance";

        if ($balance_amount > 0) {
            $staffMessage .= " (₱" . number_format($balance_amount, 2) . ")";
        }

        if ($reason) {
            $staffMessage .= ": $reason";
        }

        if ($booking['status'] === 'confirmed' && $points_earned > 0) {
            $staffMessage .= " (Deducted {$points_earned} points)";
        }

        $db->query(
            "INSERT INTO notifications (user_id, title, message, type, icon, created_at) 
             VALUES (:user_id, 'Reservation Cancelled - Balance Issue', :message, 'warning', 'fa-times-circle', NOW())",
            [
                'user_id' => $_SESSION['user_id'],
                'message' => $staffMessage
            ]
        );

        $db->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Reservation cancelled successfully due to outstanding balance',
            'points_deducted' => ($booking['status'] === 'confirmed' && $points_earned > 0) ? $points_earned : 0
        ]);
        exit();
    }

    // GET RESERVATION DETAILS
    elseif ($action === 'get_reservation_details') {
        $booking_id = intval($_POST['booking_id'] ?? 0);

        if (!$booking_id) {
            throw new Exception('Booking ID required');
        }

        $booking = $db->query(
            "SELECT 
                b.*,
                u.full_name,
                u.email,
                u.phone,
                u.member_tier,
                u.loyalty_points,
                u.preferences,
                u.allergies,
                COALESCE(cb.total_balance, 0) as outstanding_balance
             FROM bookings b
             LEFT JOIN users u ON b.user_id = u.id
             LEFT JOIN current_balance cb ON u.id = cb.user_id
             WHERE b.id = :id",
            ['id' => $booking_id]
        )->fetch_one();

        if (!$booking) {
            throw new Exception('Booking not found');
        }

        // Calculate points that would be earned if confirmed
        $points_to_earn = floor($booking['subtotal'] / 100) * 5;

        echo json_encode([
            'success' => true,
            'booking' => $booking,
            'outstanding_balance' => $booking['outstanding_balance'],
            'points_to_earn' => $points_to_earn
        ]);
        exit();
    }

    // BULK CONFIRM
    elseif ($action === 'bulk_confirm') {
        $booking_ids = json_decode($_POST['booking_ids'] ?? '[]', true);

        if (empty($booking_ids)) {
            throw new Exception('No bookings selected');
        }

        // Check for balances first
        $balanceWarnings = [];
        foreach ($booking_ids as $booking_id) {
            $booking = $db->query(
                "SELECT b.*, u.id as user_id,
                        COALESCE(cb.total_balance, 0) as outstanding_balance
                 FROM bookings b
                 LEFT JOIN users u ON b.user_id = u.id
                 LEFT JOIN current_balance cb ON u.id = cb.user_id
                 WHERE b.id = :id",
                ['id' => $booking_id]
            )->fetch_one();

            if ($booking && $booking['outstanding_balance'] > 0) {
                $balanceWarnings[] = [
                    'booking_id' => $booking_id,
                    'guest_name' => $booking['guest_first_name'] . ' ' . $booking['guest_last_name'],
                    'balance' => $booking['outstanding_balance']
                ];
            }
        }

        if (!empty($balanceWarnings)) {
            echo json_encode([
                'success' => false,
                'requires_action' => true,
                'has_outstanding_balances' => true,
                'warnings' => $balanceWarnings,
                'message' => 'Some guests have outstanding balances'
            ]);
            exit();
        }

        $db->beginTransaction();
        $count = 0;
        $total_points_awarded = 0;

        foreach ($booking_ids as $booking_id) {
            // Get booking details for points calculation
            $booking = $db->query(
                "SELECT subtotal, user_id FROM bookings WHERE id = :id",
                ['id' => $booking_id]
            )->fetch_one();

            // Calculate points
            $points_to_award = floor($booking['subtotal'] / 100) * 5;

            // Update booking status
            $db->query(
                "UPDATE bookings SET 
                    status = 'confirmed',
                    updated_at = NOW()
                 WHERE id = :id AND status = 'pending'",
                ['id' => $booking_id]
            );

            // Award points
            if ($booking['user_id'] && $points_to_award > 0) {
                $db->query(
                    "UPDATE users SET loyalty_points = loyalty_points + :points 
                     WHERE id = :user_id",
                    [
                        'points' => $points_to_award,
                        'user_id' => $booking['user_id']
                    ]
                );
                $total_points_awarded += $points_to_award;
            }
            $count++;
        }

        $db->commit();

        echo json_encode([
            'success' => true,
            'message' => "$count reservations confirmed successfully" .
                ($total_points_awarded > 0 ? " and $total_points_awarded total points awarded." : ""),
            'total_points_awarded' => $total_points_awarded
        ]);
        exit();
    }

    // EXPORT RESERVATIONS
    elseif ($action === 'export_reservations') {
        $format = $_POST['format'] ?? 'csv';
        $status = $_POST['status'] ?? 'all';
        $month = $_POST['month'] ?? 'all';

        $where = ["check_in >= CURDATE()"];
        if ($status !== 'all') {
            $where[] = "status = '$status'";
        }
        if ($month !== 'all') {
            $where[] = "MONTH(check_in) = $month";
        }

        $whereClause = implode(' AND ', $where);

        $reservations = $db->query(
            "SELECT 
                booking_reference as 'Booking #',
                CONCAT(guest_first_name, ' ', guest_last_name) as 'Guest Name',
                room_name as 'Room Type',
                DATE_FORMAT(check_in, '%M %d, %Y') as 'Check-in',
                DATE_FORMAT(check_out, '%M %d, %Y') as 'Check-out',
                nights as 'Nights',
                adults as 'Adults',
                children as 'Children',
                total_amount as 'Total Amount',
                status as 'Status',
                payment_status as 'Payment',
                special_requests as 'Special Requests'
             FROM bookings
             WHERE $whereClause
             ORDER BY check_in ASC",
            []
        )->find() ?: [];

        if ($format === 'csv') {
            $filename = 'reservations_export_' . date('Y-m-d') . '.csv';
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="' . $filename . '"');

            $output = fopen('php://output', 'w');

            if (!empty($reservations)) {
                fputcsv($output, array_keys($reservations[0]));
                foreach ($reservations as $row) {
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