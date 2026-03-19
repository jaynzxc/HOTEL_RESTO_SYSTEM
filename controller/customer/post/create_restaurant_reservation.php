<?php
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
    $user_id = $_SESSION['user_id'];

    // ========== CHECK FOR PENDING HOTEL BOOKINGS ==========
    $pendingBooking = $db->query(
        "SELECT id, booking_reference, room_name, check_in, check_out, total_amount, status, payment_status
         FROM bookings 
         WHERE user_id = :user_id 
         AND status IN ('pending', 'confirmed')
         AND payment_status = 'unpaid'
         AND check_out >= CURDATE()
         ORDER BY created_at DESC 
         LIMIT 1",
        ['user_id' => $user_id]
    )->fetch_one();

    if ($pendingBooking) {
        $formattedAmount = '₱' . number_format($pendingBooking['total_amount'], 2);

        echo json_encode([
            'success' => false,
            'has_pending' => true,
            'type' => 'hotel_booking',
            'message' => 'You have an unpaid hotel booking that needs payment or your payment has not been approved before creating a restaurant reservation.',
            'pending_item' => [
                'id' => $pendingBooking['id'],
                'reference' => $pendingBooking['booking_reference'],
                'type' => 'hotel',
                'details' => "Room: {$pendingBooking['room_name']}",
                'date' => $pendingBooking['check_in'],
                'amount' => $pendingBooking['total_amount'],
                'amount_formatted' => $formattedAmount
            ]
        ]);
        exit();
    }

    // ========== CHECK FOR PENDING RESTAURANT RESERVATIONS ==========
    $pendingReservations = $db->query(
        "SELECT id, reservation_reference, guests, reservation_date, reservation_time, down_payment, points_earned, status, payment_status
         FROM restaurant_reservations 
         WHERE user_id = :user_id 
         AND status IN ('pending', 'confirmed')
         AND payment_status = 'unpaid'
         AND reservation_date >= CURDATE()
         ORDER BY created_at DESC
         LIMIT 1",
        ['user_id' => $user_id]
    )->fetch_one();

    if ($pendingReservations) {
        $formattedAmount = '₱' . number_format($pendingReservations['down_payment'], 2);

        echo json_encode([
            'success' => false,
            'has_pending' => true,
            'type' => 'restaurant_reservation',
            'message' => 'You already have a pending restaurant reservation. Please complete payment for your existing reservation before creating a new one.',
            'pending_item' => [
                'id' => $pendingReservations['id'],
                'reference' => $pendingReservations['reservation_reference'],
                'type' => 'restaurant',
                'details' => "{$pendingReservations['guests']} guests",
                'date' => $pendingReservations['reservation_date'],
                'time' => $pendingReservations['reservation_time'],
                'amount' => $pendingReservations['down_payment'],
                'amount_formatted' => $formattedAmount,
                'points_earned' => $pendingReservations['points_earned']
            ]
        ]);
        exit();
    }

    // ========== CHECK BALANCE APPROVAL STATUS ==========
    $balance = $db->query(
        "SELECT * FROM current_balance 
         WHERE user_id = :user_id",
        ['user_id' => $user_id]
    )->fetch_one();

    if ($balance) {
        // Check if there's a pending approval
        if ($balance['admin_approval'] === 'pending') {
            echo json_encode([
                'success' => false,
                'has_pending' => true,
                'type' => 'balance_pending',
                'message' => 'Your account has a pending balance that needs admin approval before you can make new reservations.',
                'pending_item' => [
                    'amount' => $balance['pending_balance'],
                    'total' => $balance['total_balance'],
                    'available' => $balance['available_balance'],
                    'amount_formatted' => '₱' . number_format($balance['pending_balance'], 2)
                ]
            ]);
            exit();
        }

        // Check if there's a rejected balance
        if ($balance['admin_approval'] === 'rejected' && $balance['pending_balance'] > 0) {
            echo json_encode([
                'success' => false,
                'has_pending' => true,
                'type' => 'balance_rejected',
                'message' => 'Your previous payment was rejected. Please contact support to resolve this before making new reservations.',
                'pending_item' => [
                    'amount' => $balance['pending_balance'],
                    'total' => $balance['total_balance'],
                    'reason' => $balance['rejection_reason'],
                    'amount_formatted' => '₱' . number_format($balance['pending_balance'], 2)
                ]
            ]);
            exit();
        }
    }

    // ========== CHECK FOR PENDING PAYMENTS ==========
    $pendingPayment = $db->query(
        "SELECT id, payment_reference, amount, payment_method, approval_status
         FROM payments 
         WHERE user_id = :user_id 
         AND approval_status = 'pending'
         ORDER BY created_at DESC 
         LIMIT 1",
        ['user_id' => $user_id]
    )->fetch_one();

    if ($pendingPayment) {
        echo json_encode([
            'success' => false,
            'has_pending' => true,
            'type' => 'payment_pending',
            'message' => 'You have a payment pending admin approval. Please wait for approval before creating a new reservation.',
            'pending_item' => [
                'reference' => $pendingPayment['payment_reference'],
                'amount' => $pendingPayment['amount'],
                'amount_formatted' => '₱' . number_format($pendingPayment['amount'], 2),
                'method' => $pendingPayment['payment_method']
            ]
        ]);
        exit();
    }

    // ========== ALL CHECKS PASSED - PROCEED WITH RESERVATION CREATION ==========

    // Get and validate input
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $reservation_date = $_POST['reservation_date'] ?? '';
    $reservation_time = $_POST['reservation_time'] ?? '';
    $guests = intval($_POST['guests'] ?? 0);
    $special_requests = trim($_POST['special_requests'] ?? '');
    $occasion = trim($_POST['occasion'] ?? '');

    // Validation
    $errors = [];

    if (empty($first_name))
        $errors[] = 'First name is required';
    if (empty($last_name))
        $errors[] = 'Last name is required';
    if (empty($email))
        $errors[] = 'Email is required';
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL))
        $errors[] = 'Invalid email format';
    if (empty($phone))
        $errors[] = 'Phone number is required';
    if (empty($reservation_date))
        $errors[] = 'Reservation date is required';
    if (empty($reservation_time))
        $errors[] = 'Reservation time is required';
    if ($guests < 1)
        $errors[] = 'Number of guests must be at least 1';

    // Validate date (cannot be in the past)
    $res_date = new DateTime($reservation_date);
    $today = new DateTime('today');
    if ($res_date < $today) {
        $errors[] = 'Reservation date cannot be in the past';
    }

    if (!empty($errors)) {
        echo json_encode([
            'success' => false,
            'errors' => $errors
        ]);
        exit();
    }

    // Calculate down payment (₱100 per guest)
    $down_payment = $guests * 100;

    // Calculate points to earn (1 point per ₱10 spent on down payment)
    // This is just for display/reference, NOT automatically added to user
    $points_earned = floor($down_payment / 10);

    // Generate unique reservation reference
    $year = date('Y');
    $month = date('m');
    $random = strtoupper(substr(uniqid(), -6));
    $reservation_reference = "REST-{$year}{$month}-{$random}";

    // Start transaction
    $db->query("START TRANSACTION");

    // Insert restaurant reservation with points_earned (for tracking only)
    $db->query(
        "INSERT INTO restaurant_reservations (
            reservation_reference, user_id, guest_first_name, guest_last_name,
            guest_email, guest_phone, reservation_date, reservation_time,
            guests, special_requests, occasion, down_payment, points_earned,
            payment_status, status, created_at, updated_at
        ) VALUES (
            :reservation_reference, :user_id, :first_name, :last_name,
            :email, :phone, :reservation_date, :reservation_time,
            :guests, :special_requests, :occasion, :down_payment, :points_earned,
            'unpaid', 'pending', NOW(), NOW()
        )",
        [
            'reservation_reference' => $reservation_reference,
            'user_id' => $user_id,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'email' => $email,
            'phone' => $phone,
            'reservation_date' => $reservation_date,
            'reservation_time' => $reservation_time,
            'guests' => $guests,
            'special_requests' => $special_requests,
            'occasion' => $occasion,
            'down_payment' => $down_payment,
            'points_earned' => $points_earned
        ]
    );

    $reservation_id = $db->lastInsertId();

    // Create notification for the user
    $notification_message = "Your reservation for $guests guests on $reservation_date at $reservation_time has been created. ";
    $notification_message .= "Down payment: ₱" . number_format($down_payment, 2);
    if ($points_earned > 0) {
        $notification_message .= " You'll earn $points_earned loyalty points (admin will add after payment).";
    }

    $db->query(
        "INSERT INTO notifications (user_id, title, message, type, icon, link, created_at) 
         VALUES (:user_id, 'Restaurant Reservation Created', :message, 'success', 'fa-utensils', :link, NOW())",
        [
            'user_id' => $user_id,
            'message' => $notification_message,
            'link' => '/src/customer_portal/my_reservation.php'
        ]
    );

    // DO NOT UPDATE user loyalty_points here - let admin handle it

    // Commit transaction
    $db->query("COMMIT");

    // Get updated balance
    $balance = $db->query(
        "SELECT total_balance, pending_balance, available_balance 
         FROM current_balance 
         WHERE user_id = :user_id",
        ['user_id' => $user_id]
    )->fetch_one();

    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Reservation created successfully!',
        'reservation' => [
            'id' => $reservation_id,
            'reference' => $reservation_reference,
            'guests' => $guests,
            'date' => $reservation_date,
            'time' => $reservation_time,
            'down_payment' => $down_payment,
            'points_earned' => $points_earned
        ],
        'balance' => $balance,
        'note' => 'Points will be added by admin after payment confirmation'
    ]);

} catch (Exception $e) {
    // Rollback on error
    $db->query("ROLLBACK");

    error_log("Restaurant reservation error: " . $e->getMessage());

    echo json_encode([
        'success' => false,
        'message' => 'An error occurred. Please try again.'
    ]);
}
exit();
?>