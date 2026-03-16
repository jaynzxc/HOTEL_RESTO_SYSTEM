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
    // Check if user has any pending reservations
    $pendingReservations = $db->query(
        "SELECT COUNT(*) as count 
         FROM restaurant_reservations 
         WHERE user_id = :user_id 
         AND status = 'pending' 
         AND payment_status = 'unpaid'
         AND reservation_date >= CURDATE()",
        ['user_id' => $_SESSION['user_id']]
    )->fetch_one();

    if ($pendingReservations['count'] > 0) {
        // Get the pending reservation details
        $pending = $db->query(
            "SELECT id, reservation_reference, guests, reservation_date, reservation_time, down_payment
             FROM restaurant_reservations 
             WHERE user_id = :user_id 
             AND status = 'pending' 
             AND payment_status = 'unpaid'
             AND reservation_date >= CURDATE()
             ORDER BY created_at DESC
             LIMIT 1",
            ['user_id' => $_SESSION['user_id']]
        )->fetch_one();

        echo json_encode([
            'success' => false,
            'has_pending' => true,
            'message' => 'You already have a pending reservation. Please complete payment for your existing reservation before creating a new one.',
            'pending_reservation' => $pending
        ]);
        exit();
    }

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

    // Generate unique reservation reference
    $year = date('Y');
    $month = date('m');
    $random = strtoupper(substr(uniqid(), -6));
    $reservation_reference = "REST-{$year}{$month}-{$random}";

    // Start transaction
    $db->query("START TRANSACTION");

    // Insert restaurant reservation
    $db->query(
        "INSERT INTO restaurant_reservations (
            reservation_reference, user_id, guest_first_name, guest_last_name,
            guest_email, guest_phone, reservation_date, reservation_time,
            guests, special_requests, occasion, down_payment, payment_status,
            status, created_at, updated_at
        ) VALUES (
            :reservation_reference, :user_id, :first_name, :last_name,
            :email, :phone, :reservation_date, :reservation_time,
            :guests, :special_requests, :occasion, :down_payment, 'unpaid',
            'pending', NOW(), NOW()
        )",
        [
            'reservation_reference' => $reservation_reference,
            'user_id' => $_SESSION['user_id'],
            'first_name' => $first_name,
            'last_name' => $last_name,
            'email' => $email,
            'phone' => $phone,
            'reservation_date' => $reservation_date,
            'reservation_time' => $reservation_time,
            'guests' => $guests,
            'special_requests' => $special_requests,
            'occasion' => $occasion,
            'down_payment' => $down_payment
        ]
    );

    $reservation_id = $db->lastInsertId();

    // Award loyalty points (if applicable - 1 point per ₱10 spent on down payment)
    $points_earned = floor($down_payment / 10);
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
         VALUES (:user_id, 'Restaurant Reservation Created', :message, 'success', 'fa-utensils', :link, NOW())",
        [
            'user_id' => $_SESSION['user_id'],
            'message' => "Your reservation for $guests guests on $reservation_date at $reservation_time has been created. Down payment: ₱" . number_format($down_payment, 2),
            'link' => '/src/customer_portal/my_reservation.php'
        ]
    );

    // Commit transaction
    $db->query("COMMIT");

    // Get updated balance
    $balance = $db->query(
        "SELECT total_balance, pending_balance, available_balance 
         FROM current_balance 
         WHERE user_id = :user_id",
        ['user_id' => $_SESSION['user_id']]
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
        'balance' => $balance
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