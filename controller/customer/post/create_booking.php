<?php
/**
 * POST Controller - Create Hotel Booking with Promo Code
 * Handles hotel booking creation with promo code discount
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

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
    exit();
}

try {
    // Get user ID from session
    $user_id = $_SESSION['user_id'];

    // ========== CHECK FOR ACTIVE PENDING/UNPAID BOOKINGS ==========
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
        echo json_encode([
            'success' => false,
            'has_pending' => true,
            'type' => 'booking',
            'message' => 'You have an active unpaid booking that needs payment before creating a new one.',
            'pending_item' => $pendingBooking
        ]);
        exit();
    }

    // ========== CHECK FOR ACTIVE PENDING/UNPAID RESTAURANT RESERVATIONS ==========
    $pendingReservation = $db->query(
        "SELECT id, reservation_reference, reservation_date, reservation_time, guests, down_payment, status, payment_status
         FROM restaurant_reservations 
         WHERE user_id = :user_id 
         AND status IN ('pending', 'confirmed')
         AND payment_status = 'unpaid'
         AND reservation_date >= CURDATE()
         ORDER BY created_at DESC 
         LIMIT 1",
        ['user_id' => $user_id]
    )->fetch_one();

    if ($pendingReservation) {
        echo json_encode([
            'success' => false,
            'has_pending' => true,
            'type' => 'reservation',
            'message' => 'You have an active unpaid restaurant reservation that needs payment before creating a new booking.',
            'pending_item' => $pendingReservation
        ]);
        exit();
    }

    // ========== CHECK BALANCE APPROVAL STATUS ==========
    $balance = $db->query(
        "SELECT * FROM current_balance WHERE user_id = :user_id",
        ['user_id' => $user_id]
    )->fetch_one();

    if ($balance && $balance['admin_approval'] === 'pending') {
        echo json_encode([
            'success' => false,
            'has_pending' => true,
            'type' => 'balance_pending',
            'message' => 'You have a payment pending admin approval. Please wait before making new bookings.',
            'pending_item' => ['amount' => $balance['pending_balance']]
        ]);
        exit();
    }

    // ========== GET PROMO CODE DATA IF APPLIED ==========
    $promoCodeId = null;
    $promoCode = null;
    $discountApplied = 0;

    if (!empty($_POST['promo_code_id'])) {
        $promoCodeId = intval($_POST['promo_code_id']);
        $discountApplied = floatval($_POST['discount_applied'] ?? 0);

        // Get promo code details for validation
        $promoData = $db->query(
            "SELECT * FROM promo_codes WHERE id = :id",
            ['id' => $promoCodeId]
        )->fetch_one();

        if ($promoData) {
            $promoCode = $promoData['code'];

            // Update usage count
            $db->query(
                "UPDATE promo_codes SET used_count = used_count + 1 WHERE id = :id",
                ['id' => $promoCodeId]
            );
        }
    }

    // Get and validate input data
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $checkIn = $_POST['check_in'] ?? '';
    $checkOut = $_POST['check_out'] ?? '';
    $adults = intval($_POST['adults'] ?? 2);
    $children = intval($_POST['children'] ?? 0);
    $specialRequests = trim($_POST['special_requests'] ?? '');
    $roomId = $_POST['room_id'] ?? '';
    $roomName = $_POST['room_name'] ?? '';
    $roomPrice = floatval($_POST['room_price'] ?? 0);

    // Validation array
    $errors = [];

    // Validate required fields
    if (empty($firstName))
        $errors['first_name'] = 'First name is required';
    if (empty($lastName))
        $errors['last_name'] = 'Last name is required';
    if (empty($email))
        $errors['email'] = 'Email is required';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL))
        $errors['email'] = 'Invalid email format';
    if (empty($phone))
        $errors['phone'] = 'Phone number is required';
    if (empty($checkIn))
        $errors['check_in'] = 'Check-in date is required';
    if (empty($checkOut))
        $errors['check_out'] = 'Check-out date is required';
    if (empty($roomId))
        $errors['room'] = 'Please select a room';

    // Validate dates
    $checkInDate = new DateTime($checkIn);
    $checkOutDate = new DateTime($checkOut);
    $today = new DateTime('today');

    if ($checkInDate < $today) {
        $errors['check_in'] = 'Check-in date cannot be in the past';
    }

    if ($checkOutDate <= $checkInDate) {
        $errors['check_out'] = 'Check-out must be after check-in';
    }

    // Calculate nights
    $interval = $checkInDate->diff($checkOutDate);
    $nights = $interval->days;

    if ($nights < 1) {
        $errors['dates'] = 'Minimum stay is 1 night';
    }

    // If there are errors, return them
    if (!empty($errors)) {
        echo json_encode([
            'success' => false,
            'errors' => array_values($errors)
        ]);
        exit();
    }

    // Calculate totals with promo discount
    $subtotal = $roomPrice * $nights;
    $tax = $subtotal * 0.12;
    $totalAmount = $subtotal + $tax;

    // Apply discount if promo code was used
    if ($discountApplied > 0) {
        $totalAmount = $totalAmount - $discountApplied;
    }

    // Calculate points to earn (5 points per ₱100 spent after discount)
    $pointsEarned = floor($totalAmount / 100) * 5;

    // Generate unique booking reference
    $reference = 'HOT-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));

    // Start transaction
    $db->beginTransaction();

    // Insert booking
    $db->query(
        "INSERT INTO bookings (
            booking_reference, user_id, guest_first_name, guest_last_name, 
            guest_email, guest_phone, check_in, check_out, nights,
            room_id, room_name, room_price, adults, children,
            subtotal, tax, total_amount, special_requests,
            promo_code_id, promo_code, discount_applied,
            points_earned, status, payment_status, created_at
        ) VALUES (
            :reference, :user_id, :first_name, :last_name,
            :email, :phone, :check_in, :check_out, :nights,
            :room_id, :room_name, :room_price, :adults, :children,
            :subtotal, :tax, :total, :special_requests,
            :promo_code_id, :promo_code, :discount_applied,
            :points, 'pending', 'unpaid', NOW()
        )",
        [
            'reference' => $reference,
            'user_id' => $user_id,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'phone' => $phone,
            'check_in' => $checkIn,
            'check_out' => $checkOut,
            'nights' => $nights,
            'room_id' => $roomId,
            'room_name' => $roomName,
            'room_price' => $roomPrice,
            'adults' => $adults,
            'children' => $children,
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $totalAmount,
            'special_requests' => $specialRequests,
            'promo_code_id' => $promoCodeId,
            'promo_code' => $promoCode,
            'discount_applied' => $discountApplied,
            'points' => $pointsEarned
        ]
    );

    // Get the inserted booking ID
    $bookingId = $db->lastInsertId();

    // Create notification for user
    $notification_message = "Your booking for $roomName from $checkIn to $checkOut has been created. Total: ₱" . number_format($totalAmount, 2);
    if ($discountApplied > 0) {
        $notification_message .= " (₱" . number_format($discountApplied, 2) . " discount applied)";
    }
    if ($pointsEarned > 0) {
        $notification_message .= " You'll earn $pointsEarned loyalty points after payment.";
    }

    $db->query(
        "INSERT INTO notifications (
            user_id, title, message, type, icon, link, created_at
        ) VALUES (
            :user_id, 'Booking Created', :message, 'success', 'fa-hotel', '/src/customer_portal/my_reservation.php', NOW()
        )",
        [
            'user_id' => $user_id,
            'message' => $notification_message
        ]
    );

    // Commit transaction
    $db->commit();

    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Booking created successfully',
        'booking' => [
            'id' => $bookingId,
            'reference' => $reference,
            'room_name' => $roomName,
            'check_in' => $checkIn,
            'check_out' => $checkOut,
            'nights' => $nights,
            'total' => $totalAmount,
            'discount_applied' => $discountApplied,
            'promo_code' => $promoCode,
            'points_earned' => $pointsEarned
        ],
        'note' => 'Points will be added after payment confirmation'
    ]);

} catch (Exception $e) {
    // Rollback transaction on error
    if (isset($db)) {
        $db->rollBack();
    }

    error_log("Booking creation error: " . $e->getMessage());
    error_log("POST data: " . print_r($_POST, true));

    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
exit();
?>