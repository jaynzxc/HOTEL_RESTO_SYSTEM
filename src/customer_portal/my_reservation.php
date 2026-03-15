<?php
/**
 * My Reservations Page - PHP Version
 * Complete reservation management with database integration
 */

session_start();
require_once __DIR__ . '/../../Class/Database.php';
$config = require __DIR__ . '/../../config/config.php';
$db = new Database($config['database']);

// For local/testing: allow direct access without login using user_id from query string or session
$userId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : ($_SESSION['user_id'] ?? 0);

// Build a lightweight current user array compatible with this page
$currentUser = [
    'user_id'        => $userId,
    'first_name'     => $_SESSION['user_name'] ?? 'Guest',
    'last_name'      => '',
    'membership_tier'=> 'member',
    'loyalty_points' => 0,
];

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
            // Cancel booking
            $result = $db->query(
                "UPDATE hotel_bookings
                 SET booking_status = 'cancelled'
                 WHERE booking_id = :id AND user_id = :user_id",
                [
                    'id'      => $bookingId,
                    'user_id' => $currentUser['user_id'],
                ]
            );
            if ($result) {
                $success = 'Hotel booking cancelled successfully';
            } else {
                $error = 'Failed to cancel hotel booking';
            }
        }
    } elseif ($action === 'cancel_reservation') {
        $reservationId = $_POST['reservation_id'] ?? '';
        
        if (empty($reservationId)) {
            $error = 'Invalid reservation ID';
        } else {
            // Cancel reservation
            $result = $db->query(
                "UPDATE restaurant_reservations
                 SET reservation_status = 'cancelled'
                 WHERE reservation_id = :id AND user_id = :user_id",
                [
                    'id'      => $reservationId,
                    'user_id' => $currentUser['user_id'],
                ]
            );
            if ($result) {
                $success = 'Restaurant reservation cancelled successfully';
            } else {
                $error = 'Failed to cancel restaurant reservation';
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
            // Modify reservation
            $result = $db->query(
                "UPDATE restaurant_reservations
                 SET reservation_date = :date,
                     reservation_time = :time,
                     number_of_guests = :guests
                 WHERE reservation_id = :id AND user_id = :user_id",
                [
                    'date'    => $newDate,
                    'time'    => $newTime,
                    'guests'  => $newGuests,
                    'id'      => $reservationId,
                    'user_id' => $currentUser['user_id'],
                ]
            );
            if ($result) {
                $success = 'Restaurant reservation modified successfully';
            } else {
                $error = 'Failed to modify restaurant reservation';
            }
        }
    }
}

// Get user's reservations from hotel_bookings and restaurant_reservations tables
$hotelBookings = $db->query(
    "SELECT *
     FROM hotel_bookings
     WHERE user_id = :user_id
     ORDER BY check_in_date DESC
     LIMIT 20",
    ['user_id' => $currentUser['user_id']]
)->find();

$restaurantReservations = $db->query(
    "SELECT *
     FROM restaurant_reservations
     WHERE user_id = :user_id
     ORDER BY reservation_date DESC, reservation_time DESC
     LIMIT 20",
    ['user_id' => $currentUser['user_id']]
)->find();

// Helper functions
function formatCurrency($amount) {
    return '₱' . number_format($amount, 2);
}

function formatDate($date, $format = 'M d, Y') {
    if (empty($date)) return 'Not set';
    return date($format, strtotime($date));
}

function formatTime($time) {
    return date('h:i A', strtotime($time));
}

function getUserInitials($firstName, $lastName) {
    $firstInitial = strtoupper(substr($firstName ?? '', 0, 1));
    $lastInitial = strtoupper(substr($lastName ?? '', 0, 1));
    return ($firstInitial . $lastInitial) ?: '—';
}

function getBookingStatusClass($status) {
    $statusClasses = [
        'confirmed' => 'bg-green-100 text-green-700',
        'checked_in' => 'bg-blue-100 text-blue-700',
        'checked_out' => 'bg-slate-100 text-slate-700',
        'cancelled' => 'bg-red-100 text-red-700',
        'pending' => 'bg-amber-100 text-amber-700'
    ];
    return $statusClasses[$status] ?? 'bg-slate-100 text-slate-700';
}

function getReservationStatusClass($status) {
    $statusClasses = [
        'confirmed' => 'bg-green-100 text-green-700',
        'completed' => 'bg-slate-100 text-slate-700',
        'cancelled' => 'bg-red-100 text-red-700',
        'pending' => 'bg-amber-100 text-amber-700',
        'no_show' => 'bg-red-100 text-red-700'
    ];
    return $statusClasses[$status] ?? 'bg-slate-100 text-slate-700';
}

function isCancellable($status, $date) {
    if ($status === 'cancelled' || $status === 'completed' || $status === 'checked_out') {
        return false;
    }
    $reservationDate = new DateTime($date);
    $today = new DateTime();
    $interval = $today->diff($reservationDate);
    return $interval->days >= 1; // Can cancel if at least 1 day before
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
    <style>
        @keyframes slideIn {
            from { transform: translateX(-20px); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        .reservation-card {
            animation: slideIn 0.3s ease-out;
            transition: all 0.3s ease;
        }
        .reservation-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
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
    </style>
</head>
<body class="bg-slate-50 font-sans antialiased">
    <div class="min-h-screen flex flex-col lg:flex-row">
        <!-- Sidebar -->
        <aside class="lg:w-80 bg-white border-r border-slate-200 shadow-sm shrink-0">
            <div class="px-6 py-7 border-b border-slate-100">
                <div class="flex items-center gap-2 text-amber-700">
                    <i class="fa-solid fa-utensils text-xl"></i>
                    <i class="fa-solid fa-bed text-xl"></i>
                    <span class="font-semibold text-xl tracking-tight text-slate-800">Lùcas<span class="text-amber-600">.stay</span></span>
                </div>
                <p class="text-xs text-slate-500 mt-1">customer portal · my reservations</p>
            </div>
            <div class="flex items-center gap-3 px-6 py-5 border-b border-slate-100 bg-slate-50/80">
                <div class="h-12 w-12 rounded-full bg-amber-200 flex items-center justify-center text-amber-800 font-bold text-lg">
                    <?php echo getUserInitials($currentUser['first_name'] ?? '', $currentUser['last_name'] ?? ''); ?>
                </div>
                <div>
                    <p class="font-medium text-slate-800"><?php echo htmlspecialchars(($currentUser['first_name'] ?? '') . ' ' . ($currentUser['last_name'] ?? '')); ?></p>
                    <p class="text-xs text-slate-500 flex items-center gap-1"><i class="fa-regular fa-gem text-[11px]"></i> <span><?php echo htmlspecialchars($currentUser['membership_tier'] ?? 'member'); ?></span> · <span><?php echo number_format($currentUser['loyalty_points'] ?? 0); ?></span> pts</p>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="p-4 space-y-1.5 text-sm">
                <a href="index.php" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-700 hover:bg-amber-50 transition"><i class="fa-solid fa-table-cells-large w-5 text-slate-400"></i>Dashboard</a>
                <a href="my_profile.php" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-700 hover:bg-amber-50 transition"><i class="fa-regular fa-user w-5 text-slate-400"></i>My Profile</a>
                <a href="hotel_booking.php" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-700 hover:bg-amber-50 transition"><i class="fa-solid fa-hotel w-5 text-slate-400"></i>Hotel Booking</a>
                <a href="my_reservation.php" class="flex items-center gap-3 px-4 py-2.5 rounded-xl bg-amber-50 text-amber-800 font-medium"><i class="fa-regular fa-calendar-check w-5 text-amber-600"></i>My Reservations</a>
                <a href="restaurant_reservation.php" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-700 hover:bg-amber-50 transition"><i class="fa-regular fa-clock w-5 text-slate-400"></i>Restaurant Reservation</a>
                <a href="order_food.php" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-700 hover:bg-amber-50 transition"><i class="fa-solid fa-bag-shopping w-5 text-slate-400"></i>Menu / Order Food</a>
                <a href="payments.php" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-700 hover:bg-amber-50 transition"><i class="fa-regular fa-credit-card w-5 text-slate-400"></i>Payments</a>
                <a href="loyalty_rewards.php" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-700 hover:bg-amber-50 transition"><i class="fa-regular fa-star w-5 text-slate-400"></i>Loyalty Rewards</a>
                <a href="notifications.php" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-700 hover:bg-amber-50 transition relative"><i class="fa-regular fa-bell w-5 text-slate-400"></i>Notifications<span class="ml-auto bg-amber-100 text-amber-800 text-xs px-1.5 py-0.5 rounded-full">0</span></a>
                <div class="border-t border-slate-200 pt-3 mt-3">
                    <a href="logout.php" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-500 hover:bg-red-50 hover:text-red-700 transition"><i class="fa-solid fa-arrow-right-from-bracket w-5"></i>Logout</a>
                </div>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 p-5 lg:p-8 overflow-y-auto">
            <div class="max-w-6xl mx-auto">
                <!-- Header -->
                <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h1 class="text-2xl lg:text-3xl font-light text-slate-800">My Reservations</h1>
                        <p class="text-sm text-slate-500 mt-0.5">view and manage your upcoming and past stays & tables</p>
                    </div>
                    <div class="bg-white border border-slate-200 rounded-full px-4 py-2 text-sm flex items-center gap-2 shadow-sm">
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
                    <button type="button" onclick="switchTab('hotel')" id="hotelTab" class="flex-1 px-4 py-2 rounded-lg font-medium transition tab-active">
                        <i class="fa-solid fa-hotel mr-2"></i>Hotel Bookings
                    </button>
                    <button type="button" onclick="switchTab('restaurant')" id="restaurantTab" class="flex-1 px-4 py-2 rounded-lg font-medium transition">
                        <i class="fa-solid fa-utensils mr-2"></i>Restaurant Reservations
                    </button>
                </div>

                <!-- Hotel Bookings -->
                <div id="hotelBookings" class="grid gap-4 md:grid-cols-2">
                    <?php if (empty($hotelBookings)): ?>
                        <div class="bg-white rounded-2xl border border-slate-200 p-8 text-center">
                            <i class="fa-solid fa-bed text-4xl text-slate-300 mb-4"></i>
                            <h3 class="text-lg font-medium text-slate-800 mb-2">No hotel bookings yet</h3>
                            <p class="text-slate-500 mb-4">Book your first stay with us!</p>
                            <a href="hotel_booking.php" class="inline-flex items-center gap-2 bg-amber-600 hover:bg-amber-700 text-white px-4 py-2 rounded-xl font-medium transition">
                                <i class="fa-solid fa-plus"></i>
                                Book a Room
                            </a>
                        </div>
                    <?php else: ?>
                        <?php foreach ($hotelBookings as $booking): ?>
                            <?php
                                $rawStatus = $booking['booking_status'] ?? '';
                                $paymentStatus = $booking['payment_status'] ?? '';
                                // If booking is cancelled, always show cancelled and never allow payment
                                if ($rawStatus === 'cancelled') {
                                    $displayStatus = 'cancelled';
                                } else {
                                    $displayStatus = ($paymentStatus === 'pending') ? 'pending' : $rawStatus;
                                }
                            ?>
                            <div class="reservation-card bg-white rounded-2xl border border-slate-200 p-4 md:p-5">
                                <div class="flex flex-col gap-3">
                                    <!-- Booking Summary (organized) -->
                                    <div class="flex items-start gap-3 flex-1 min-w-0">
                                        <div class="w-9 h-9 bg-amber-100 rounded-full flex items-center justify-center">
                                            <i class="fa-solid fa-hotel text-amber-600 text-sm"></i>
                                        </div>
                                        <div class="flex-1 min-w-0 space-y-0.5">
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <h3 class="font-medium text-sm text-slate-800 truncate">
                                                    <?php echo htmlspecialchars($booking['booking_reference'] ?? ''); ?>
                                                </h3>
                                                <span class="status-badge <?php echo getBookingStatusClass($displayStatus); ?>">
                                                    <?php echo htmlspecialchars($displayStatus); ?>
                                                </span>
                                                <?php if ($paymentStatus === 'completed'): ?>
                                                    <span class="status-badge bg-emerald-100 text-emerald-700">
                                                        paid
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="flex flex-wrap items-center gap-2 text-[11px] text-slate-500">
                                                <span>
                                                    <i class="fa-regular fa-calendar-check mr-1 text-amber-600"></i>
                                                    <?php echo formatDate($booking['check_in_date'] ?? ''); ?>
                                                </span>
                                                <span class="hidden sm:inline text-slate-300">•</span>
                                                <span>
                                                    <i class="fa-regular fa-calendar-xmark mr-1 text-amber-600"></i>
                                                    <?php echo formatDate($booking['check_out_date'] ?? ''); ?>
                                                </span>
                                                <span class="hidden sm:inline text-slate-300">•</span>
                                                <span>
                                                    <i class="fa-regular fa-user mr-1 text-slate-400"></i>
                                                    <?php echo (int)($booking['number_of_guests'] ?? 1); ?> guests
                                                </span>
                                            </div>
                                            <p class="text-xs text-slate-500 truncate">
                                                Room:
                                                <span class="font-medium text-slate-700">
                                                    <?php echo htmlspecialchars($booking['room_type'] ?? ''); ?>
                                                </span>
                                                · <?php echo htmlspecialchars($booking['room_number'] ?? 'TBD'); ?>
                                            </p>
                                        </div>
                                    </div>
                                    <!-- Actions -->
                                    <div class="pt-3 border-t border-slate-100">
                                        <div class="flex flex-wrap items-center justify-end gap-2 text-[11px]">
                                            <?php if ($paymentStatus === 'pending' && $rawStatus !== 'cancelled'): ?>
                                                <a
                                                    href="payments.php?context=hotel&booking_id=<?php echo $booking['booking_id']; ?>"
                                                    class="inline-flex items-center gap-1 bg-amber-600 hover:bg-amber-700 text-white px-3 py-1.5 rounded-lg font-medium transition"
                                                >
                                                    <i class="fa-regular fa-credit-card"></i>
                                                    <span>Proceed</span>
                                                </a>
                                            <?php endif; ?>

                                            <?php if ($booking['booking_status'] === 'confirmed'): ?>
                                                <button
                                                    onclick="viewBookingDetails(this)"
                                                    class="inline-flex items-center gap-1 bg-slate-100 hover:bg-slate-200 text-slate-700 px-3 py-1.5 rounded-lg font-medium transition"
                                                    data-type="hotel"
                                                    data-reference="<?php echo htmlspecialchars($booking['booking_reference'] ?? ''); ?>"
                                                    data-status="<?php echo htmlspecialchars($booking['booking_status'] ?? ''); ?>"
                                                    data-room-type="<?php echo htmlspecialchars($booking['room_type'] ?? ''); ?>"
                                                    data-room-number="<?php echo htmlspecialchars($booking['room_number'] ?? 'TBD'); ?>"
                                                    data-check-in="<?php echo formatDate($booking['check_in_date'] ?? ''); ?>"
                                                    data-check-out="<?php echo formatDate($booking['check_out_date'] ?? ''); ?>"
                                                    data-guests="<?php echo (int)($booking['number_of_guests'] ?? 1); ?>"
                                                    data-amount="<?php echo formatCurrency($booking['total_amount'] ?? 0); ?>"
                                                    data-special-requests="<?php echo htmlspecialchars($booking['special_requests'] ?? 'None'); ?>"
                                                >
                                                    <i class="fa-regular fa-eye"></i>
                                                    <span>Details</span>
                                                </button>
                                                <?php if (isCancellable($booking['booking_status'], $booking['check_in_date'])): ?>
                                                    <form method="POST" onsubmit="return confirmCancelBooking()" class="inline-flex">
                                                        <input type="hidden" name="action" value="cancel_booking">
                                                        <input type="hidden" name="booking_id" value="<?php echo $booking['booking_id']; ?>">
                                                        <button type="submit" class="inline-flex items-center gap-1 bg-red-50 hover:bg-red-100 text-red-700 px-3 py-1.5 rounded-lg font-medium transition">
                                                            <i class="fa-regular fa-times-circle"></i>
                                                            <span>Cancel</span>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                            <?php elseif ($booking['booking_status'] === 'checked_in'): ?>
                                                <button class="inline-flex items-center gap-1 bg-blue-50 hover:bg-blue-100 text-blue-700 px-3 py-1.5 rounded-lg font-medium transition">
                                                    <i class="fa-solid fa-key"></i>
                                                    <span>Checked In</span>
                                                </button>
                                            <?php elseif ($booking['booking_status'] === 'checked_out'): ?>
                                                <button onclick="rateStay(<?php echo $booking['booking_id']; ?>)" class="inline-flex items-center gap-1 bg-amber-50 hover:bg-amber-100 text-amber-700 px-3 py-1.5 rounded-lg font-medium transition">
                                                    <i class="fa-regular fa-star"></i>
                                                    <span>Rate</span>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Restaurant Reservations -->
                <div id="restaurantReservations" class="grid gap-4 md:grid-cols-2 hidden">
                    <?php if (empty($restaurantReservations)): ?>
                        <div class="bg-white rounded-2xl border border-slate-200 p-8 text-center">
                            <i class="fa-solid fa-utensils text-4xl text-slate-300 mb-4"></i>
                            <h3 class="text-lg font-medium text-slate-800 mb-2">No restaurant reservations yet</h3>
                            <p class="text-slate-500 mb-4">Reserve a table at our restaurant!</p>
                            <a href="restaurant_reservation.php" class="inline-flex items-center gap-2 bg-amber-600 hover:bg-amber-700 text-white px-4 py-2 rounded-xl font-medium transition">
                                <i class="fa-solid fa-plus"></i>
                                Make Reservation
                            </a>
                        </div>
                    <?php else: ?>
                        <?php foreach ($restaurantReservations as $reservation): ?>
                            <?php
                                $resStatus = $reservation['reservation_status'] ?? '';
                                $depositAmount = (float)($reservation['deposit_amount'] ?? 0);
                                $depositPaid = (int)($reservation['deposit_paid'] ?? 0);
                                // Only allow payment and "pending" status if not cancelled
                                if ($resStatus === 'cancelled') {
                                    $paymentPending = false;
                                    $displayResStatus = 'cancelled';
                                } else {
                                    $paymentPending = !$depositPaid;
                                    $displayResStatus = $paymentPending ? 'pending' : $resStatus;
                                }
                            ?>
                            <div class="reservation-card bg-white rounded-2xl border border-slate-200 p-4 md:p-5">
                                <div class="flex flex-col gap-3">
                                    <!-- Reservation Summary (organized) -->
                                    <div class="flex items-start gap-3 flex-1 min-w-0">
                                        <div class="w-9 h-9 bg-amber-100 rounded-full flex items-center justify-center">
                                            <i class="fa-solid fa-utensils text-amber-600 text-sm"></i>
                                        </div>
                                        <div class="flex-1 min-w-0 space-y-0.5">
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <h3 class="font-medium text-sm text-slate-800 truncate">
                                                    Table Reservation
                                                </h3>
                                                <span class="status-badge <?php echo getReservationStatusClass($displayResStatus); ?>">
                                                    <?php echo htmlspecialchars($displayResStatus); ?>
                                                </span>
                                                <?php if ($depositAmount > 0 && $depositPaid): ?>
                                                    <span class="status-badge bg-emerald-100 text-emerald-700">
                                                        deposit paid
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="flex flex-wrap items-center gap-2 text-[11px] text-slate-500">
                                                <span>
                                                    <i class="fa-regular fa-calendar mr-1 text-amber-600"></i>
                                                    <?php echo formatDate($reservation['reservation_date'] ?? ''); ?>
                                                </span>
                                                <span class="hidden sm:inline text-slate-300">•</span>
                                                <span>
                                                    <i class="fa-regular fa-clock mr-1 text-amber-600"></i>
                                                    <?php echo formatTime($reservation['reservation_time'] ?? ''); ?>
                                                </span>
                                                <span class="hidden sm:inline text-slate-300">•</span>
                                                <span>
                                                    <i class="fa-regular fa-user mr-1 text-slate-400"></i>
                                                    <?php echo (int)($reservation['number_of_guests'] ?? 1); ?> guests
                                                </span>
                                            </div>
                                            <p class="text-xs text-slate-500 truncate">
                                                Table
                                                <span class="font-medium text-slate-700">
                                                    <?php echo htmlspecialchars($reservation['table_number'] ?? 'TBD'); ?>
                                                </span>
                                            </p>
                                        </div>
                                    </div>
                                    <!-- Actions -->
                                    <div class="pt-3 border-t border-slate-100">
                                        <div class="flex flex-wrap items-center justify-end gap-2 text-[11px]">
                                            <?php if ($paymentPending && $resStatus !== 'cancelled'): ?>
                                                <a
                                                    href="payments.php?context=restaurant&reservation_id=<?php echo $reservation['reservation_id']; ?>"
                                                    class="inline-flex items-center gap-1 bg-amber-600 hover:bg-amber-700 text-white px-3 py-1.5 rounded-lg font-medium transition"
                                                >
                                                    <i class="fa-regular fa-credit-card"></i>
                                                    <span>Proceed</span>
                                                </a>
                                            <?php endif; ?>

                                            <?php if ($reservation['reservation_status'] === 'confirmed'): ?>
                                                <button
                                                    onclick="viewReservationDetails(this)"
                                                    class="inline-flex items-center gap-1 bg-slate-100 hover:bg-slate-200 text-slate-700 px-3 py-1.5 rounded-lg font-medium transition"
                                                    data-type="restaurant"
                                                    data-status="<?php echo htmlspecialchars($reservation['reservation_status'] ?? ''); ?>"
                                                    data-date="<?php echo formatDate($reservation['reservation_date'] ?? ''); ?>"
                                                    data-time="<?php echo formatTime($reservation['reservation_time'] ?? ''); ?>"
                                                    data-guests="<?php echo (int)($reservation['number_of_guests'] ?? 1); ?>"
                                                    data-table="<?php echo htmlspecialchars($reservation['table_number'] ?? 'TBD'); ?>"
                                                    data-special-requests="<?php echo htmlspecialchars($reservation['special_requests'] ?? 'None'); ?>"
                                                >
                                                    <i class="fa-regular fa-eye"></i>
                                                    <span>Details</span>
                                                </button>
                                                <?php if (isCancellable($reservation['reservation_status'], $reservation['reservation_date'])): ?>
                                                    <form method="POST" onsubmit="return confirmCancelReservation()" class="inline-flex">
                                                        <input type="hidden" name="action" value="cancel_reservation">
                                                        <input type="hidden" name="reservation_id" value="<?php echo $reservation['reservation_id']; ?>">
                                                        <button type="submit" class="inline-flex items-center gap-1 bg-red-50 hover:bg-red-100 text-red-700 px-3 py-1.5 rounded-lg font-medium transition">
                                                            <i class="fa-regular fa-times-circle"></i>
                                                            <span>Cancel</span>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                                <button
                                                    onclick="modifyReservation(<?php echo $reservation['reservation_id']; ?>, '<?php echo $reservation['reservation_date']; ?>', '<?php echo $reservation['reservation_time']; ?>', '<?php echo $reservation['number_of_guests']; ?>')"
                                                    class="inline-flex items-center gap-1 bg-amber-50 hover:bg-amber-100 text-amber-700 px-3 py-1.5 rounded-lg font-medium transition"
                                                >
                                                    <i class="fa-regular fa-edit"></i>
                                                    <span>Modify</span>
                                                </button>
                                            <?php elseif ($reservation['reservation_status'] === 'completed'): ?>
                                                <button onclick="rateDining(<?php echo $reservation['reservation_id']; ?>)" class="inline-flex items-center gap-1 bg-amber-50 hover:bg-amber-100 text-amber-700 px-3 py-1.5 rounded-lg font-medium transition">
                                                    <i class="fa-regular fa-star"></i>
                                                    <span>Rate</span>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Need help section -->
                <div class="mt-8 bg-amber-50 border border-amber-200 rounded-2xl p-5 flex flex-wrap items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <i class="fa-regular fa-circle-question text-3xl text-amber-600"></i>
                        <div>
                            <p class="font-medium text-slate-800">need help with a reservation?</p>
                            <p class="text-xs text-slate-600">contact our support team or modify online</p>
                        </div>
                    </div>
                    <button type="button" class="bg-white border border-amber-600 text-amber-700 px-5 py-2 rounded-xl text-sm hover:bg-amber-50">
                        contact support
                    </button>
                </div>

                <!-- Bottom hint -->
                <div class="mt-10 text-center text-xs text-slate-400 border-t pt-6">
                    ✅ My Reservations module — receives reservations after payment from hotel and restaurant bookings
                </div>
            </div>
        </main>
    </div>

    <!-- Details Modal -->
    <div id="detailsModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-2xl p-6 w-full max-w-lg">
            <div class="flex items-start justify-between gap-4 mb-4">
                <div>
                    <h3 id="detailsTitle" class="text-lg font-semibold text-slate-800 mb-1">Reservation Details</h3>
                    <p id="detailsSubtitle" class="text-xs text-slate-500"></p>
                </div>
                <button type="button" onclick="closeDetailsModal()" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
            <div id="detailsBody" class="space-y-3 text-sm text-slate-700">
                <!-- Filled dynamically -->
            </div>
            <div class="mt-5 flex justify-end">
                <button type="button" onclick="closeDetailsModal()" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-medium">
                    Close
                </button>
            </div>
        </div>
    </div>

    <!-- Modification Modal -->
    <div id="modifyModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-2xl p-6 w-full max-w-md">
            <h3 class="text-lg font-semibold text-slate-800 mb-4">Modify Reservation</h3>
            <form method="POST">
                <input type="hidden" name="action" value="modify_reservation">
                <input type="hidden" name="reservation_id" id="modifyReservationId">
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Date</label>
                        <input type="date" name="new_date" id="modifyDate" required class="w-full border border-slate-200 rounded-xl px-4 py-2 focus:ring-2 focus:ring-amber-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Time</label>
                        <select name="new_time" id="modifyTime" required class="w-full border border-slate-200 rounded-xl px-4 py-2 focus:ring-2 focus:ring-amber-500 outline-none">
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
                        <select name="new_guests" id="modifyGuests" required class="w-full border border-slate-200 rounded-xl px-4 py-2 focus:ring-2 focus:ring-amber-500 outline-none">
                            <option value="1">1 guest</option>
                            <option value="2">2 guests</option>
                            <option value="3">3 guests</option>
                            <option value="4">4 guests</option>
                            <option value="5">5 guests</option>
                            <option value="6">6 guests</option>
                            <option value="7">7 guests</option>
                            <option value="8">8 guests</option>
                        </select>
                    </div>
                </div>
                
                <div class="flex gap-3 mt-6">
                    <button type="button" onclick="closeModifyModal()" class="flex-1 bg-slate-100 hover:bg-slate-200 text-slate-700 px-4 py-2 rounded-xl font-medium transition">
                        Cancel
                    </button>
                    <button type="submit" class="flex-1 bg-amber-600 hover:bg-amber-700 text-white px-4 py-2 rounded-xl font-medium transition">
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
        
        // Confirmation dialogs
        function confirmCancelBooking() {
            return confirm('Are you sure you want to cancel this hotel booking? This action cannot be undone.');
        }
        
        function confirmCancelReservation() {
            return confirm('Are you sure you want to cancel this restaurant reservation? This action cannot be undone.');
        }
        
        // Details modal helpers
        function openDetailsModal(title, subtitle, bodyHtml) {
            document.getElementById('detailsTitle').textContent = title;
            document.getElementById('detailsSubtitle').textContent = subtitle || '';
            document.getElementById('detailsBody').innerHTML = bodyHtml;
            const modal = document.getElementById('detailsModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeDetailsModal() {
            const modal = document.getElementById('detailsModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        // View details
        function viewBookingDetails(buttonEl) {
            const ref = buttonEl.dataset.reference || '';
            const status = buttonEl.dataset.status || '';
            const roomType = buttonEl.dataset.roomType || '';
            const roomNumber = buttonEl.dataset.roomNumber || 'TBD';
            const checkIn = buttonEl.dataset.checkIn || '';
            const checkOut = buttonEl.dataset.checkOut || '';
            const guests = buttonEl.dataset.guests || '';
            const amount = buttonEl.dataset.amount || '';
            const special = buttonEl.dataset.specialRequests || 'None';

            const subtitle = `Status: ${status}`;
            const body = `
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-slate-500 text-xs">Booking Reference</p>
                        <p class="font-medium">${ref}</p>
                    </div>
                    <div>
                        <p class="text-slate-500 text-xs">Room</p>
                        <p class="font-medium">${roomType} · ${roomNumber}</p>
                    </div>
                    <div>
                        <p class="text-slate-500 text-xs">Check-in</p>
                        <p class="font-medium">${checkIn}</p>
                    </div>
                    <div>
                        <p class="text-slate-500 text-xs">Check-out</p>
                        <p class="font-medium">${checkOut}</p>
                    </div>
                    <div>
                        <p class="text-slate-500 text-xs">Guests</p>
                        <p class="font-medium">${guests} guest${guests == 1 ? '' : 's'}</p>
                    </div>
                    <div>
                        <p class="text-slate-500 text-xs">Total Amount</p>
                        <p class="font-semibold text-amber-700">${amount}</p>
                    </div>
                </div>
                <div class="mt-3">
                    <p class="text-slate-500 text-xs mb-1">Special Requests</p>
                    <p class="text-sm">${special}</p>
                </div>
            `;

            openDetailsModal('Hotel Booking Details', subtitle, body);
        }
        
        function viewReservationDetails(buttonEl) {
            const status = buttonEl.dataset.status || '';
            const date = buttonEl.dataset.date || '';
            const time = buttonEl.dataset.time || '';
            const guests = buttonEl.dataset.guests || '';
            const table = buttonEl.dataset.table || 'TBD';
            const special = buttonEl.dataset.specialRequests || 'None';

            const subtitle = `Status: ${status}`;
            const body = `
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-slate-500 text-xs">Date</p>
                        <p class="font-medium">${date}</p>
                    </div>
                    <div>
                        <p class="text-slate-500 text-xs">Time</p>
                        <p class="font-medium">${time}</p>
                    </div>
                    <div>
                        <p class="text-slate-500 text-xs">Guests</p>
                        <p class="font-medium">${guests} guest${guests == 1 ? '' : 's'}</p>
                    </div>
                    <div>
                        <p class="text-slate-500 text-xs">Table</p>
                        <p class="font-medium">${table}</p>
                    </div>
                </div>
                <div class="mt-3">
                    <p class="text-slate-500 text-xs mb-1">Special Requests</p>
                    <p class="text-sm">${special}</p>
                </div>
            `;

            openDetailsModal('Restaurant Reservation Details', subtitle, body);
        }
        
        // Rating functions
        function rateStay(bookingId) {
            // In a real implementation, this would open a rating modal
            alert('Rate your stay feature coming soon!');
        }
        
        function rateDining(reservationId) {
            // In a real implementation, this would open a rating modal
            alert('Rate your dining experience feature coming soon!');
        }
        
        // Modify reservation
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
    </script>
</body>
</html>
