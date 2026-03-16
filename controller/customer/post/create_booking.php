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
    // Check if user has any pending hotel bookings
    $pendingBookings = $db->query(
        "SELECT COUNT(*) as count 
         FROM bookings 
         WHERE user_id = :user_id 
         AND booking_type = 'hotel'
         AND status = 'pending' 
         AND payment_status = 'unpaid'
         AND check_in >= CURDATE()",
        ['user_id' => $_SESSION['user_id']]
    )->fetch_one();

    if ($pendingBookings['count'] > 0) {
        // Get the pending booking details
        $pending = $db->query(
            "SELECT id, booking_reference, room_name, check_in, check_out, nights, total_amount
             FROM bookings 
             WHERE user_id = :user_id 
             AND booking_type = 'hotel'
             AND status = 'pending' 
             AND payment_status = 'unpaid'
             AND check_in >= CURDATE()
             ORDER BY created_at DESC
             LIMIT 1",
            ['user_id' => $_SESSION['user_id']]
        )->fetch_one();

        echo json_encode([
            'success' => false,
            'has_pending' => true,
            'message' => 'You already have a pending hotel booking. Please complete payment for your existing booking before creating a new one.',
            'pending_booking' => $pending
        ]);
        exit();
    }

    // Get and validate input
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $check_in = $_POST['check_in'] ?? '';
    $check_out = $_POST['check_out'] ?? '';
    $room_id = $_POST['room_id'] ?? '';
    $room_name = $_POST['room_name'] ?? '';
    $room_price = floatval($_POST['room_price'] ?? 0);
    $adults = intval($_POST['adults'] ?? 2);
    $children = intval($_POST['children'] ?? 0);
    $special_requests = trim($_POST['special_requests'] ?? '');

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
    if (empty($check_in))
        $errors[] = 'Check-in date is required';
    if (empty($check_out))
        $errors[] = 'Check-out date is required';
    if (empty($room_id))
        $errors[] = 'Please select a room';

    // Validate dates
    $check_in_date = new DateTime($check_in);
    $check_out_date = new DateTime($check_out);
    $today = new DateTime('today');

    if ($check_in_date < $today) {
        $errors[] = 'Check-in date cannot be in the past';
    }

    if ($check_out_date <= $check_in_date) {
        $errors[] = 'Check-out date must be after check-in date';
    }

    // Calculate nights
    $nights = $check_in_date->diff($check_out_date)->days;
    if ($nights < 1)
        $nights = 1;

    if (!empty($errors)) {
        echo json_encode([
            'success' => false,
            'errors' => $errors
        ]);
        exit();
    }

    // Calculate totals
    $subtotal = $room_price * $nights;
    $tax = round($subtotal * 0.12, 2); // 12% VAT
    $total_amount = $subtotal + $tax;

    // Generate unique booking reference
    $year = date('Y');
    $month = date('m');
    $random = strtoupper(substr(uniqid(), -6));
    $booking_reference = "HOT-{$year}{$month}-{$random}";

    // Start transaction
    $db->query("START TRANSACTION");

    // Insert booking
    $db->query(
        "INSERT INTO bookings (
            booking_reference, user_id, guest_first_name, guest_last_name,
            guest_email, guest_phone, booking_type, check_in, check_out,
            nights, room_id, room_name, room_price, adults, children,
            subtotal, tax, total_amount, special_requests, status, payment_status
        ) VALUES (
            :booking_reference, :user_id, :first_name, :last_name,
            :email, :phone, 'hotel', :check_in, :check_out,
            :nights, :room_id, :room_name, :room_price, :adults, :children,
            :subtotal, :tax, :total_amount, :special_requests, 'pending', 'unpaid'
        )",
        [
            'booking_reference' => $booking_reference,
            'user_id' => $_SESSION['user_id'],
            'first_name' => $first_name,
            'last_name' => $last_name,
            'email' => $email,
            'phone' => $phone,
            'check_in' => $check_in,
            'check_out' => $check_out,
            'nights' => $nights,
            'room_id' => $room_id,
            'room_name' => $room_name,
            'room_price' => $room_price,
            'adults' => $adults,
            'children' => $children,
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total_amount' => $total_amount,
            'special_requests' => $special_requests
        ]
    );

    $booking_id = $db->lastInsertId();

    // Award loyalty points (5 points per ₱100 spent)
    $points_earned = floor($subtotal / 100) * 5;
    if ($points_earned > 0) {
        $db->query(
            "UPDATE users SET loyalty_points = loyalty_points + :points WHERE id = :user_id",
            [
                'points' => $points_earned,
                'user_id' => $_SESSION['user_id']
            ]
        );
    }

    // Create notification for the user
    $db->query(
        "INSERT INTO notifications (user_id, title, message, type, icon, link, created_at) 
         VALUES (:user_id, :title, :message, 'success', 'fa-hotel', :link, NOW())",
        [
            'user_id' => $_SESSION['user_id'],
            'title' => 'Booking Created',
            'message' => "Your booking for $room_name from $check_in to $check_out has been created. Total: ₱" . number_format($total_amount, 2),
            'link' => '/src/customer_portal/my_reservation.php'
        ]
    );

    // Commit transaction
    $db->query("COMMIT");

    // Get updated balance for response
    $balance = $db->query(
        "SELECT total_balance, pending_balance, available_balance 
         FROM current_balance 
         WHERE user_id = :user_id",
        ['user_id' => $_SESSION['user_id']]
    )->fetch_one();

    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Booking created successfully!',
        'booking' => [
            'id' => $booking_id,
            'reference' => $booking_reference,
            'total' => $total_amount,
            'nights' => $nights,
            'room_name' => $room_name,
            'check_in' => $check_in,
            'check_out' => $check_out,
            'points_earned' => $points_earned
        ],
        'balance' => $balance
    ]);

} catch (Exception $e) {
    // Rollback on error
    $db->query("ROLLBACK");

    error_log("Booking error: " . $e->getMessage());

    echo json_encode([
        'success' => false,
        'message' => 'An error occurred. Please try again.'
    ]);
}
exit();