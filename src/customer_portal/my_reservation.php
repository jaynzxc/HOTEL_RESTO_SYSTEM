<?php
/**
 * My Reservations Page - PHP Version
 * Complete reservation management with database integration
 */

session_start();
require_once __DIR__ . '/../../Class/Database.php';
$config = require __DIR__ . '/../../config/config.php';
$db = new Database($config['database']);

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !$_SESSION['logged_in']) {
    header('Location: ../../view/auth/login.php');
    exit();
}

$userId = $_SESSION['user_id'];

// Get user data
$user = $db->query(
    "SELECT id, full_name, first_name, last_name, email, loyalty_points, member_tier
     FROM users WHERE id = :id",
    ['id' => $userId]
)->fetch_one();

// Set points variable for easy access
$points = $user['loyalty_points'] ?? 0;
$member_tier = $user['member_tier'] ?? 'bronze';

// Handle form submissions
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'cancel_booking') {
        $bookingId = $_POST['booking_id'] ?? '';

        if (empty($bookingId)) {
            $error = 'Invalid booking ID';
        } else {
            try {
                $db->beginTransaction();

                // Get booking details before cancelling - need to calculate points earned
                $booking = $db->query(
                    "SELECT id, total_amount, subtotal, status, payment_status 
                     FROM bookings 
                     WHERE id = :id AND user_id = :user_id",
                    [
                        'id' => $bookingId,
                        'user_id' => $userId
                    ]
                )->fetch_one();

                if (!$booking) {
                    throw new Exception('Booking not found');
                }

                if ($booking['status'] === 'cancelled') {
                    throw new Exception('Booking is already cancelled');
                }

                // Calculate points earned from this booking (5 points per ₱100 of subtotal)
                $points_earned = floor($booking['subtotal'] / 100) * 5;

                // Cancel booking
                $result = $db->query(
                    "UPDATE bookings
                     SET status = 'cancelled', updated_at = NOW()
                     WHERE id = :id AND user_id = :user_id",
                    [
                        'id' => $bookingId,
                        'user_id' => $userId
                    ]
                );

                if ($result) {
                    // Deduct points that were earned from this booking
                    if ($points_earned > 0) {
                        $db->query(
                            "UPDATE users SET loyalty_points = loyalty_points - :points 
                             WHERE id = :user_id",
                            [
                                'points' => $points_earned,
                                'user_id' => $userId
                            ]
                        );
                    }

                    // Add notification
                    $db->query(
                        "INSERT INTO notifications (user_id, title, message, type, icon, created_at) 
                         VALUES (:user_id, 'Booking Cancelled', :message, 'warning', 'fa-times-circle', NOW())",
                        [
                            'user_id' => $userId,
                            'message' => "Your booking #{$bookingId} has been cancelled. " .
                                ($points_earned > 0 ? "{$points_earned} points have been deducted." : "")
                        ]
                    );

                    $db->commit();
                    $success = 'Hotel booking cancelled successfully' .
                        ($points_earned > 0 ? " and {$points_earned} points deducted." : "");
                } else {
                    throw new Exception('Failed to cancel booking');
                }
            } catch (Exception $e) {
                $db->rollBack();
                $error = $e->getMessage();
            }
        }
    } elseif ($action === 'cancel_reservation') {
        $reservationId = $_POST['reservation_id'] ?? '';

        if (empty($reservationId)) {
            $error = 'Invalid reservation ID';
        } else {
            try {
                $db->beginTransaction();

                // Get reservation details - need to calculate points earned
                $reservation = $db->query(
                    "SELECT id, down_payment, status, payment_status 
                     FROM restaurant_reservations 
                     WHERE id = :id AND user_id = :user_id",
                    [
                        'id' => $reservationId,
                        'user_id' => $userId
                    ]
                )->fetch_one();

                if (!$reservation) {
                    throw new Exception('Reservation not found');
                }

                if ($reservation['status'] === 'cancelled') {
                    throw new Exception('Reservation is already cancelled');
                }

                // Calculate points earned from this reservation (1 point per ₱10 down payment)
                $points_earned = floor($reservation['down_payment'] / 10);

                // Cancel reservation
                $result = $db->query(
                    "UPDATE restaurant_reservations
                     SET status = 'cancelled', updated_at = NOW()
                     WHERE id = :id AND user_id = :user_id",
                    [
                        'id' => $reservationId,
                        'user_id' => $userId
                    ]
                );

                if ($result) {
                    // Deduct points that were earned from this reservation
                    if ($points_earned > 0) {
                        $db->query(
                            "UPDATE users SET loyalty_points = loyalty_points - :points 
                             WHERE id = :user_id",
                            [
                                'points' => $points_earned,
                                'user_id' => $userId
                            ]
                        );
                    }

                    // Add notification
                    $db->query(
                        "INSERT INTO notifications (user_id, title, message, type, icon, created_at) 
                         VALUES (:user_id, 'Reservation Cancelled', :message, 'warning', 'fa-times-circle', NOW())",
                        [
                            'user_id' => $userId,
                            'message' => "Your restaurant reservation #{$reservationId} has been cancelled. " .
                                ($points_earned > 0 ? "{$points_earned} points have been deducted." : "")
                        ]
                    );

                    $db->query("COMMIT");
                    $success = 'Restaurant reservation cancelled successfully' .
                        ($points_earned > 0 ? " and {$points_earned} points deducted." : "");
                } else {
                    throw new Exception('Failed to cancel reservation');
                }
            } catch (Exception $e) {
                $db->rollBack();
                $error = $e->getMessage();
            }
        }
    } elseif ($action === 'modify_reservation') {
        $reservationId = $_POST['reservation_id'] ?? '';
        $newDate = $_POST['new_date'] ?? '';
        $newTime = $_POST['new_time'] ?? '';
        $newGuests = $_POST['new_guests'] ?? '';

        if (empty($reservationId) || empty($newDate) || empty($newTime) || empty($newGuests)) {
            $error = 'Please fill in all fields';
        } else {
            try {
                // Modify reservation
                $result = $db->query(
                    "UPDATE restaurant_reservations
                     SET reservation_date = :date,
                         reservation_time = :time,
                         guests = :guests,
                         updated_at = NOW()
                     WHERE id = :id AND user_id = :user_id",
                    [
                        'date' => $newDate,
                        'time' => $newTime,
                        'guests' => $newGuests,
                        'id' => $reservationId,
                        'user_id' => $userId
                    ]
                );

                if ($result) {
                    $success = 'Restaurant reservation modified successfully';
                } else {
                    $error = 'Failed to modify restaurant reservation';
                }
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        }
    }
}

// Get user's hotel bookings from bookings table (not hotel_bookings)
$hotelBookings = $db->query(
    "SELECT 
        id as booking_id,
        booking_reference,
        guest_first_name,
        guest_last_name,
        room_name as room_type,
        room_id,
        check_in as check_in_date,
        check_out as check_out_date,
        adults + children as number_of_guests,
        adults,
        children,
        nights,
        subtotal,
        tax,
        total_amount,
        special_requests,
        status as booking_status,
        payment_status,
        created_at,
        updated_at
     FROM bookings
     WHERE user_id = :user_id AND booking_type = 'hotel'
     ORDER BY check_in DESC
     LIMIT 20",
    ['user_id' => $userId]
)->find();

// Get user's restaurant reservations
$restaurantReservations = $db->query(
    "SELECT 
        id as reservation_id,
        reservation_reference,
        guest_first_name,
        guest_last_name,
        reservation_date,
        reservation_time,
        guests as number_of_guests,
        table_number,
        special_requests,
        occasion,
        down_payment as deposit_amount,
        CASE WHEN payment_status = 'paid' THEN 1 ELSE 0 END as deposit_paid,
        status as reservation_status,
        payment_status,
        created_at,
        updated_at
     FROM restaurant_reservations
     WHERE user_id = :user_id
     ORDER BY reservation_date DESC, reservation_time DESC
     LIMIT 20",
    ['user_id' => $userId]
)->find();

// Get user's current balance
$balance = $db->query(
    "SELECT total_balance, pending_balance, available_balance 
     FROM current_balance 
     WHERE user_id = :user_id",
    ['user_id' => $userId]
)->fetch_one();

// Get unread notifications count
try {
    $unread_result = $db->query(
        "SELECT COUNT(*) as count FROM notifications 
         WHERE user_id = :user_id AND is_read = 0",
        ['user_id' => $userId]
    )->fetch_one();
    $unread_count = $unread_result['count'] ?? 0;
} catch (Exception $e) {
    $unread_count = 0;
}

// Helper functions
function formatCurrency($amount)
{
    return '₱' . number_format($amount, 2);
}

function formatDate($date, $format = 'M d, Y')
{
    if (empty($date))
        return 'Not set';
    return date($format, strtotime($date));
}

function formatTime($time)
{
    return date('h:i A', strtotime($time));
}

function getUserInitials($firstName, $lastName)
{
    $firstInitial = strtoupper(substr($firstName ?? '', 0, 1));
    $lastInitial = strtoupper(substr($lastName ?? '', 0, 1));
    return ($firstInitial . $lastInitial) ?: '—';
}

function getBookingStatusClass($status)
{
    $statusClasses = [
        'confirmed' => 'bg-green-100 text-green-700',
        'pending' => 'bg-amber-100 text-amber-700',
        'cancelled' => 'bg-red-100 text-red-700',
        'completed' => 'bg-slate-100 text-slate-700'
    ];
    return $statusClasses[$status] ?? 'bg-slate-100 text-slate-700';
}

function getReservationStatusClass($status)
{
    $statusClasses = [
        'confirmed' => 'bg-green-100 text-green-700',
        'completed' => 'bg-slate-100 text-slate-700',
        'cancelled' => 'bg-red-100 text-red-700',
        'pending' => 'bg-amber-100 text-amber-700',
        'no_show' => 'bg-red-100 text-red-700'
    ];
    return $statusClasses[$status] ?? 'bg-slate-100 text-slate-700';
}

function isCancellable($status, $date)
{
    if ($status === 'cancelled' || $status === 'completed') {
        return false;
    }
    $reservationDate = new DateTime($date);
    $today = new DateTime();
    return $reservationDate > $today;
}
?>

<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>My Reservations · Lùcas Customer Portal</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <style>
            @keyframes slideIn {
                from {
                    transform: translateX(-20px);
                    opacity: 0;
                }

                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }

            .reservation-card {
                animation: slideIn 0.3s ease-out;
                transition: all 0.3s ease;
            }

            .reservation-card:hover {
                transform: translateY(-2px);
                box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            }

            .tab-active {
                background: linear-gradient(135deg, #f59e0b, #d97706);
                color: white;
            }

            .status-badge {
                font-size: 0.75rem;
                padding: 0.25rem 0.75rem;
                border-radius: 9999px;
                font-weight: 500;
            }

            .balance-card {
                background: linear-gradient(135deg, #f59e0b, #d97706);
            }
        </style>
    </head>

    <body class="bg-slate-50 font-sans antialiased">
        <div class="min-h-screen flex flex-col lg:flex-row">
            <!-- Sidebar -->
            <?php require './components/customer_nav.php' ?>

            <!-- Main Content -->
            <main class="flex-1 p-5 lg:p-8 overflow-y-auto">
                <div class="max-w-6xl mx-auto">
                    <!-- Header -->
                    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h1 class="text-2xl lg:text-3xl font-light text-slate-800">My Reservations</h1>
                            <p class="text-sm text-slate-500 mt-0.5">view and manage your upcoming and past stays &
                                tables</p>
                        </div>
                        <div
                            class="bg-white border border-slate-200 rounded-full px-4 py-2 text-sm flex items-center gap-2 shadow-sm">
                            <i class="fa-regular fa-calendar text-slate-400"></i>
                            <span id="currentDate">
                                <?php echo strtolower(date('l, F j, Y')); ?>
                            </span>
                        </div>
                    </div>

                    <!-- Success/Error Messages -->
                    <?php if ($success): ?>
                        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl mb-6">
                            <div class="flex items-center gap-2">
                                <i class="fa-solid fa-check-circle"></i>
                                <span><?php echo htmlspecialchars($success); ?></span>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($error): ?>
                        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-6">
                            <div class="flex items-center gap-2">
                                <i class="fa-solid fa-exclamation-circle"></i>
                                <span><?php echo htmlspecialchars($error); ?></span>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Tab Navigation -->
                    <div class="flex mb-6 bg-slate-100 rounded-xl p-1">
                        <button type="button" onclick="switchTab('hotel')" id="hotelTab"
                            class="flex-1 px-4 py-2 rounded-lg font-medium transition tab-active">
                            <i class="fa-solid fa-hotel mr-2"></i>Hotel Bookings
                        </button>
                        <button type="button" onclick="switchTab('restaurant')" id="restaurantTab"
                            class="flex-1 px-4 py-2 rounded-lg font-medium transition">
                            <i class="fa-solid fa-utensils mr-2"></i>Restaurant Reservations
                        </button>
                    </div>

                    <!-- Hotel Bookings -->
                    <div id="hotelBookings" class="grid gap-4 md:grid-cols-2">
                        <?php if (empty($hotelBookings)): ?>
                            <div class="bg-white rounded-2xl border border-slate-200 p-8 text-center md:col-span-2">
                                <i class="fa-solid fa-bed text-4xl text-slate-300 mb-4"></i>
                                <h3 class="text-lg font-medium text-slate-800 mb-2">No hotel bookings yet</h3>
                                <p class="text-slate-500 mb-4">Book your first stay with us and earn loyalty points!</p>
                                <a href="hotel_booking.php"
                                    class="inline-flex items-center gap-2 bg-amber-600 hover:bg-amber-700 text-white px-4 py-2 rounded-xl font-medium transition">
                                    <i class="fa-solid fa-plus"></i>
                                    Book a Room
                                </a>
                            </div>
                        <?php else: ?>
                            <?php foreach ($hotelBookings as $booking): ?>
                                <?php
                                $status = $booking['booking_status'] ?? 'pending';
                                $paymentStatus = $booking['payment_status'] ?? 'unpaid';
                                $isPaid = $paymentStatus === 'paid';
                                $isCancelled = $status === 'cancelled';
                                $canCancel = !$isCancelled && !$isPaid && isCancellable($status, $booking['check_in_date']);
                                ?>
                                <div class="reservation-card bg-white rounded-2xl border border-slate-200 p-4 md:p-5">
                                    <div class="flex flex-col gap-3">
                                        <!-- Header -->
                                        <div class="flex items-start justify-between">
                                            <div class="flex items-center gap-2">
                                                <div class="w-8 h-8 bg-amber-100 rounded-full flex items-center justify-center">
                                                    <i class="fa-solid fa-hotel text-amber-600 text-xs"></i>
                                                </div>
                                                <div>
                                                    <span class="text-xs text-slate-500">Reference</span>
                                                    <p class="font-mono text-xs font-medium">
                                                        <?php echo htmlspecialchars($booking['booking_reference'] ?? ''); ?>
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="flex gap-1">
                                                <span class="status-badge <?php echo getBookingStatusClass($status); ?>">
                                                    <?php echo htmlspecialchars($status); ?>
                                                </span>
                                                <?php if ($isPaid): ?>
                                                    <span class="status-badge bg-emerald-100 text-emerald-700">
                                                        paid
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>

                                        <!-- Details -->
                                        <div class="grid grid-cols-2 gap-2 text-xs">
                                            <div>
                                                <p class="text-slate-500">Check-in</p>
                                                <p class="font-medium"><?php echo formatDate($booking['check_in_date']); ?></p>
                                            </div>
                                            <div>
                                                <p class="text-slate-500">Check-out</p>
                                                <p class="font-medium"><?php echo formatDate($booking['check_out_date']); ?></p>
                                            </div>
                                            <div>
                                                <p class="text-slate-500">Room</p>
                                                <p class="font-medium truncate">
                                                    <?php echo htmlspecialchars($booking['room_type'] ?? ''); ?>
                                                </p>
                                            </div>
                                            <div>
                                                <p class="text-slate-500">Guests</p>
                                                <p class="font-medium"><?php echo $booking['number_of_guests'] ?? 1; ?></p>
                                            </div>
                                            <div class="col-span-2">
                                                <p class="text-slate-500">Total Amount</p>
                                                <p class="font-semibold text-amber-700">
                                                    <?php echo formatCurrency($booking['total_amount'] ?? 0); ?>
                                                </p>
                                            </div>
                                        </div>

                                        <!-- Actions -->
                                        <div class="pt-3 border-t border-slate-100 flex justify-end gap-2">
                                            <?php if (!$isPaid && !$isCancelled): ?>
                                                <a href="payments.php?type=hotel&id=<?php echo $booking['booking_id']; ?>"
                                                    class="inline-flex items-center gap-1 bg-amber-600 hover:bg-amber-700 text-white px-3 py-1.5 rounded-lg text-xs font-medium transition">
                                                    <i class="fa-regular fa-credit-card"></i>
                                                    Pay Now
                                                </a>
                                            <?php endif; ?>

                                            <button
                                                onclick="viewBookingDetails(<?php echo htmlspecialchars(json_encode($booking)); ?>)"
                                                class="inline-flex items-center gap-1 bg-slate-100 hover:bg-slate-200 text-slate-700 px-3 py-1.5 rounded-lg text-xs font-medium transition">
                                                <i class="fa-regular fa-eye"></i>
                                                Details
                                            </button>

                                            <?php if ($canCancel): ?>
                                                <button onclick="cancelBooking(<?php echo $booking['booking_id']; ?>)"
                                                    class="inline-flex items-center gap-1 bg-red-50 hover:bg-red-100 text-red-700 px-3 py-1.5 rounded-lg text-xs font-medium transition">
                                                    <i class="fa-regular fa-times-circle"></i>
                                                    Cancel
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <!-- Restaurant Reservations -->
                    <div id="restaurantReservations" class="grid gap-4 md:grid-cols-2 hidden">
                        <?php if (empty($restaurantReservations)): ?>
                            <div class="bg-white rounded-2xl border border-slate-200 p-8 text-center md:col-span-2">
                                <i class="fa-solid fa-utensils text-4xl text-slate-300 mb-4"></i>
                                <h3 class="text-lg font-medium text-slate-800 mb-2">No restaurant reservations yet</h3>
                                <p class="text-slate-500 mb-4">Reserve a table at our restaurant and earn points!</p>
                                <a href="restaurant_reservation.php"
                                    class="inline-flex items-center gap-2 bg-amber-600 hover:bg-amber-700 text-white px-4 py-2 rounded-xl font-medium transition">
                                    <i class="fa-solid fa-plus"></i>
                                    Make Reservation
                                </a>
                            </div>
                        <?php else: ?>
                            <?php foreach ($restaurantReservations as $reservation): ?>
                                <?php
                                $status = $reservation['reservation_status'] ?? 'pending';
                                $paymentStatus = $reservation['payment_status'] ?? 'unpaid';
                                $depositPaid = $reservation['deposit_paid'] ?? 0;
                                $isPaid = $paymentStatus === 'paid' || $depositPaid;
                                $isCancelled = $status === 'cancelled';
                                $canCancel = !$isCancelled && !$isPaid && isCancellable($status, $reservation['reservation_date']);
                                ?>
                                <div class="reservation-card bg-white rounded-2xl border border-slate-200 p-4 md:p-5">
                                    <div class="flex flex-col gap-3">
                                        <!-- Header -->
                                        <div class="flex items-start justify-between">
                                            <div class="flex items-center gap-2">
                                                <div class="w-8 h-8 bg-amber-100 rounded-full flex items-center justify-center">
                                                    <i class="fa-solid fa-utensils text-amber-600 text-xs"></i>
                                                </div>
                                                <div>
                                                    <span class="text-xs text-slate-500">Table for</span>
                                                    <p class="font-medium text-sm">
                                                        <?php echo $reservation['number_of_guests'] ?? 1; ?> guests
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="flex gap-1">
                                                <span class="status-badge <?php echo getReservationStatusClass($status); ?>">
                                                    <?php echo htmlspecialchars($status); ?>
                                                </span>
                                                <?php if ($isPaid): ?>
                                                    <span class="status-badge bg-emerald-100 text-emerald-700">
                                                        paid
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>

                                        <!-- Details -->
                                        <div class="grid grid-cols-2 gap-2 text-xs">
                                            <div>
                                                <p class="text-slate-500">Date</p>
                                                <p class="font-medium">
                                                    <?php echo formatDate($reservation['reservation_date']); ?>
                                                </p>
                                            </div>
                                            <div>
                                                <p class="text-slate-500">Time</p>
                                                <p class="font-medium">
                                                    <?php echo formatTime($reservation['reservation_time']); ?>
                                                </p>
                                            </div>
                                            <div>
                                                <p class="text-slate-500">Table</p>
                                                <p class="font-medium">
                                                    <?php echo htmlspecialchars($reservation['table_number'] ?? 'TBD'); ?>
                                                </p>
                                            </div>
                                            <?php if ($reservation['deposit_amount'] > 0): ?>
                                                <div>
                                                    <p class="text-slate-500">Deposit</p>
                                                    <p class="font-medium">
                                                        <?php echo formatCurrency($reservation['deposit_amount']); ?>
                                                    </p>
                                                </div>
                                            <?php endif; ?>
                                        </div>

                                        <!-- Actions -->
                                        <div class="pt-3 border-t border-slate-100 flex justify-end gap-2">
                                            <?php if (!$isPaid && !$isCancelled && $reservation['deposit_amount'] > 0): ?>
                                                <a href="payments.php?type=restaurant&id=<?php echo $reservation['reservation_id']; ?>"
                                                    class="inline-flex items-center gap-1 bg-amber-600 hover:bg-amber-700 text-white px-3 py-1.5 rounded-lg text-xs font-medium transition">
                                                    <i class="fa-regular fa-credit-card"></i>
                                                    Pay Deposit
                                                </a>
                                            <?php endif; ?>

                                            <button
                                                onclick="viewReservationDetails(<?php echo htmlspecialchars(json_encode($reservation)); ?>)"
                                                class="inline-flex items-center gap-1 bg-slate-100 hover:bg-slate-200 text-slate-700 px-3 py-1.5 rounded-lg text-xs font-medium transition">
                                                <i class="fa-regular fa-eye"></i>
                                                Details
                                            </button>

                                            <?php if ($canCancel): ?>
                                                <button onclick="cancelReservation(<?php echo $reservation['reservation_id']; ?>)"
                                                    class="inline-flex items-center gap-1 bg-red-50 hover:bg-red-100 text-red-700 px-3 py-1.5 rounded-lg text-xs font-medium transition">
                                                    <i class="fa-regular fa-times-circle"></i>
                                                    Cancel
                                                </button>
                                            <?php endif; ?>

                                            <?php if ($status === 'confirmed' && !$isPaid && !$isCancelled): ?>
                                                <button
                                                    onclick="modifyReservation(<?php echo $reservation['reservation_id']; ?>, '<?php echo $reservation['reservation_date']; ?>', '<?php echo $reservation['reservation_time']; ?>', <?php echo $reservation['number_of_guests']; ?>)"
                                                    class="inline-flex items-center gap-1 bg-amber-50 hover:bg-amber-100 text-amber-700 px-3 py-1.5 rounded-lg text-xs font-medium transition">
                                                    <i class="fa-regular fa-edit"></i>
                                                    Modify
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <!-- Need help section -->
                    <div
                        class="mt-8 bg-amber-50 border border-amber-200 rounded-2xl p-5 flex flex-wrap items-center justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <i class="fa-regular fa-circle-question text-3xl text-amber-600"></i>
                            <div>
                                <p class="font-medium text-slate-800">need help with a reservation?</p>
                                <p class="text-xs text-slate-600">contact our support team or modify online</p>
                            </div>
                        </div>
                        <button onclick="contactSupport()"
                            class="bg-white border border-amber-600 text-amber-700 px-5 py-2 rounded-xl text-sm hover:bg-amber-50">
                            contact support
                        </button>
                    </div>

                    <!-- Bottom hint -->
                    <div class="mt-10 text-center text-xs text-slate-400 border-t pt-6">
                        ✅ Manage all your reservations in one place
                    </div>
                </div>
            </main>
        </div>

        <!-- Details Modal -->
        <div id="detailsModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
            <div class="bg-white rounded-2xl p-6 w-full max-w-lg max-h-[90vh] overflow-y-auto">
                <div class="flex items-start justify-between gap-4 mb-4">
                    <div>
                        <h3 id="detailsTitle" class="text-lg font-semibold text-slate-800 mb-1">Reservation Details</h3>
                        <p id="detailsSubtitle" class="text-xs text-slate-500"></p>
                    </div>
                    <button onclick="closeDetailsModal()" class="text-slate-400 hover:text-slate-600">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>
                <div id="detailsBody" class="space-y-3 text-sm text-slate-700"></div>
                <div class="mt-5 flex justify-end">
                    <button onclick="closeDetailsModal()"
                        class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-medium">
                        Close
                    </button>
                </div>
            </div>
        </div>

        <!-- Modification Modal -->
        <div id="modifyModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
            <div class="bg-white rounded-2xl p-6 w-full max-w-md">
                <h3 class="text-lg font-semibold text-slate-800 mb-4">Modify Reservation</h3>
                <form method="POST" action="">
                    <input type="hidden" name="action" value="modify_reservation">
                    <input type="hidden" name="reservation_id" id="modifyReservationId">

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Date</label>
                            <input type="date" name="new_date" id="modifyDate" required
                                min="<?php echo date('Y-m-d'); ?>"
                                class="w-full border border-slate-200 rounded-xl px-4 py-2 focus:ring-2 focus:ring-amber-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Time</label>
                            <select name="new_time" id="modifyTime" required
                                class="w-full border border-slate-200 rounded-xl px-4 py-2 focus:ring-2 focus:ring-amber-500 outline-none">
                                <option value="11:00:00">11:00 AM</option>
                                <option value="11:30:00">11:30 AM</option>
                                <option value="12:00:00">12:00 PM</option>
                                <option value="12:30:00">12:30 PM</option>
                                <option value="13:00:00">1:00 PM</option>
                                <option value="13:30:00">1:30 PM</option>
                                <option value="14:00:00">2:00 PM</option>
                                <option value="18:00:00">6:00 PM</option>
                                <option value="18:30:00">6:30 PM</option>
                                <option value="19:00:00">7:00 PM</option>
                                <option value="19:30:00">7:30 PM</option>
                                <option value="20:00:00">8:00 PM</option>
                                <option value="20:30:00">8:30 PM</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Number of Guests</label>
                            <select name="new_guests" id="modifyGuests" required
                                class="w-full border border-slate-200 rounded-xl px-4 py-2 focus:ring-2 focus:ring-amber-500 outline-none">
                                <?php for ($i = 1; $i <= 8; $i++): ?>
                                    <option value="<?php echo $i; ?>"><?php echo $i; ?>
                                        guest<?php echo $i > 1 ? 's' : ''; ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>

                    <div class="flex gap-3 mt-6">
                        <button type="button" onclick="closeModifyModal()"
                            class="flex-1 bg-slate-100 hover:bg-slate-200 text-slate-700 px-4 py-2 rounded-xl font-medium transition">
                            Cancel
                        </button>
                        <button type="submit"
                            class="flex-1 bg-amber-600 hover:bg-amber-700 text-white px-4 py-2 rounded-xl font-medium transition">
                            Update
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <script>
            // Tab switching
            function switchTab(tab) {
                const hotelTab = document.getElementById('hotelTab');
                const restaurantTab = document.getElementById('restaurantTab');
                const hotelBookings = document.getElementById('hotelBookings');
                const restaurantReservations = document.getElementById('restaurantReservations');

                if (tab === 'hotel') {
                    hotelTab.classList.add('tab-active');
                    restaurantTab.classList.remove('tab-active');
                    hotelBookings.classList.remove('hidden');
                    restaurantReservations.classList.add('hidden');
                } else {
                    restaurantTab.classList.add('tab-active');
                    hotelTab.classList.remove('tab-active');
                    restaurantReservations.classList.remove('hidden');
                    hotelBookings.classList.add('hidden');
                }
            }

            // Cancel functions with SweetAlert
            function cancelBooking(bookingId) {
                Swal.fire({
                    title: 'Cancel Booking?',
                    text: 'Are you sure you want to cancel this hotel booking? This action cannot be undone and points earned will be deducted.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d97706',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Yes, cancel it'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = '';
                        form.innerHTML = `
                        <input type="hidden" name="action" value="cancel_booking">
                        <input type="hidden" name="booking_id" value="${bookingId}">
                    `;
                        document.body.appendChild(form);
                        form.submit();
                    }
                });
            }

            function cancelReservation(reservationId) {
                Swal.fire({
                    title: 'Cancel Reservation?',
                    text: 'Are you sure you want to cancel this restaurant reservation? This action cannot be undone and points earned will be deducted.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d97706',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Yes, cancel it'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = '';
                        form.innerHTML = `
                        <input type="hidden" name="action" value="cancel_reservation">
                        <input type="hidden" name="reservation_id" value="${reservationId}">
                    `;
                        document.body.appendChild(form);
                        form.submit();
                    }
                });
            }

            // Details modal functions
            function viewBookingDetails(booking) {
                document.getElementById('detailsTitle').textContent = 'Hotel Booking Details';
                document.getElementById('detailsSubtitle').textContent = `Reference: ${booking.booking_reference}`;

                const body = `
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-slate-500 text-xs">Guest Name</p>
                        <p class="font-medium">${booking.guest_first_name} ${booking.guest_last_name}</p>
                    </div>
                    <div>
                        <p class="text-slate-500 text-xs">Room</p>
                        <p class="font-medium">${booking.room_type}</p>
                    </div>
                    <div>
                        <p class="text-slate-500 text-xs">Check-in</p>
                        <p class="font-medium">${formatDate(booking.check_in_date)}</p>
                    </div>
                    <div>
                        <p class="text-slate-500 text-xs">Check-out</p>
                        <p class="font-medium">${formatDate(booking.check_out_date)}</p>
                    </div>
                    <div>
                        <p class="text-slate-500 text-xs">Adults</p>
                        <p class="font-medium">${booking.adults || 0}</p>
                    </div>
                    <div>
                        <p class="text-slate-500 text-xs">Children</p>
                        <p class="font-medium">${booking.children || 0}</p>
                    </div>
                    <div>
                        <p class="text-slate-500 text-xs">Subtotal</p>
                        <p class="font-medium">₱${Number(booking.subtotal).toLocaleString()}</p>
                    </div>
                    <div>
                        <p class="text-slate-500 text-xs">Tax (12%)</p>
                        <p class="font-medium">₱${Number(booking.tax).toLocaleString()}</p>
                    </div>
                    <div class="col-span-2">
                        <p class="text-slate-500 text-xs">Total Amount</p>
                        <p class="font-semibold text-amber-700 text-lg">₱${Number(booking.total_amount).toLocaleString()}</p>
                    </div>
                </div>
                <div class="mt-4 pt-4 border-t">
                    <p class="text-slate-500 text-xs mb-1">Special Requests</p>
                    <p class="text-sm">${booking.special_requests || 'None'}</p>
                </div>
            `;

                document.getElementById('detailsBody').innerHTML = body;
                document.getElementById('detailsModal').classList.remove('hidden');
                document.getElementById('detailsModal').classList.add('flex');
            }

            function viewReservationDetails(reservation) {
                document.getElementById('detailsTitle').textContent = 'Restaurant Reservation Details';
                document.getElementById('detailsSubtitle').textContent = `${reservation.number_of_guests} guests`;

                const body = `
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-slate-500 text-xs">Guest Name</p>
                        <p class="font-medium">${reservation.guest_first_name} ${reservation.guest_last_name}</p>
                    </div>
                    <div>
                        <p class="text-slate-500 text-xs">Table</p>
                        <p class="font-medium">${reservation.table_number || 'TBD'}</p>
                    </div>
                    <div>
                        <p class="text-slate-500 text-xs">Date</p>
                        <p class="font-medium">${formatDate(reservation.reservation_date)}</p>
                    </div>
                    <div>
                        <p class="text-slate-500 text-xs">Time</p>
                        <p class="font-medium">${formatTime(reservation.reservation_time)}</p>
                    </div>
                    <div>
                        <p class="text-slate-500 text-xs">Occasion</p>
                        <p class="font-medium">${reservation.occasion || 'None'}</p>
                    </div>
                    <div>
                        <p class="text-slate-500 text-xs">Deposit</p>
                        <p class="font-medium">₱${Number(reservation.deposit_amount || 0).toLocaleString()}</p>
                    </div>
                </div>
                <div class="mt-4 pt-4 border-t">
                    <p class="text-slate-500 text-xs mb-1">Special Requests</p>
                    <p class="text-sm">${reservation.special_requests || 'None'}</p>
                </div>
            `;

                document.getElementById('detailsBody').innerHTML = body;
                document.getElementById('detailsModal').classList.remove('hidden');
                document.getElementById('detailsModal').classList.add('flex');
            }

            function closeDetailsModal() {
                document.getElementById('detailsModal').classList.add('hidden');
                document.getElementById('detailsModal').classList.remove('flex');
            }

            // Modify functions
            function modifyReservation(reservationId, currentDate, currentTime, currentGuests) {
                document.getElementById('modifyReservationId').value = reservationId;
                document.getElementById('modifyDate').value = currentDate;
                document.getElementById('modifyTime').value = currentTime;
                document.getElementById('modifyGuests').value = currentGuests;
                document.getElementById('modifyModal').classList.remove('hidden');
                document.getElementById('modifyModal').classList.add('flex');
            }

            function closeModifyModal() {
                document.getElementById('modifyModal').classList.add('hidden');
                document.getElementById('modifyModal').classList.remove('flex');
            }

            // Contact support
            function contactSupport() {
                Swal.fire({
                    title: 'Contact Support',
                    text: 'Please call us at +63 (2) 1234 5678 or email support@lucas.stay',
                    icon: 'info',
                    confirmButtonColor: '#d97706'
                });
            }

            // Helper functions
            function formatDate(dateString) {
                if (!dateString) return 'Not set';
                const date = new Date(dateString);
                return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
            }

            function formatTime(timeString) {
                if (!timeString) return 'Not set';
                const [hours, minutes] = timeString.split(':');
                const hour = parseInt(hours);
                const ampm = hour >= 12 ? 'PM' : 'AM';
                const hour12 = hour % 12 || 12;
                return `${hour12}:${minutes} ${ampm}`;
            }

            // Close modals on outside click
            window.onclick = function (event) {
                const detailsModal = document.getElementById('detailsModal');
                const modifyModal = document.getElementById('modifyModal');

                if (detailsModal && event.target === detailsModal) {
                    closeDetailsModal();
                }
                if (modifyModal && event.target === modifyModal) {
                    closeModifyModal();
                }
            }
        </script>
    </body>

</html>