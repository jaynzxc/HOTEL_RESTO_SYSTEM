<?php
/**
 * View - Customer Dashboard
 */
require_once '../../controller/customer/get/dashboard.php';
?>
<!DOCTYPE html>
<html lang="en">

  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lùcas · Customer Dashboard</title>
    <!-- Tailwind CSS (CDN) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../output.css">
    <!-- Font Awesome 6 (free) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
      .transition-hover {
        transition: all 0.2s ease;
      }

      .booking-item {
        transition: all 0.2s ease;
      }

      .booking-item:hover {
        background-color: #fef3e2;
      }

      .stat-card {
        transition: all 0.3s ease;
      }

      .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
      }
    </style>
  </head>

  <body class="bg-slate-50 font-sans antialiased">

    <!-- whole portal wrapper -->
    <div class="min-h-screen flex flex-col lg:flex-row">

      <!-- ========== S I D E B A R ========== -->
      <?php require './components/customer_nav.php' ?>

      <!-- ========== M A I N   C O N T E N T  ========== -->
      <main class="flex-1 p-5 lg:p-8 overflow-y-auto">

        <!-- top welcome + FIXED NOTIFICATION & SEARCH with icons -->
        <div class="flex flex-wrap items-center justify-between gap-4 mb-8">
          <div>
            <h1 class="text-2xl lg:text-3xl font-light text-slate-800"><?php echo $greeting; ?>, <span
                class="font-semibold"
                id="welcomeName"><?php echo htmlspecialchars($user['first_name'] ?? 'guest'); ?></span> 👋</h1>
            <p class="text-sm text-slate-500 mt-0.5">your stay · <span id="currentDate"></span></p>
          </div>
          <!-- fixed notification + search row (icons added) -->
          <div class="flex items-center gap-3">
            <!-- notification bell with icon + badge - now links to notifications -->
            <a href="./notifications.php" class="relative">
              <i class="fas fa-bell text-2xl text-slate-500 hover:text-amber-600 transition cursor-pointer"></i>
              <?php if ($unread_count > 0): ?>
                <span
                  class="absolute -top-1 -right-1 bg-amber-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full ring-2 ring-white"
                  id="headerNotificationBadge"><?php echo $unread_count; ?></span>
              <?php endif; ?>
            </a>
            <!-- search bar with icon -->
            <div
              class="bg-white border border-slate-200 rounded-full px-4 py-2 flex items-center gap-2 text-sm w-64 shadow-sm">
              <i class="fa-solid fa-magnifying-glass text-amber-400"></i>
              <input type="text" placeholder="search rooms, food..."
                class="w-full outline-none bg-transparent text-slate-700 placeholder:text-slate-400">
              <i class="fa-solid fa-sliders text-slate-400 hover:text-amber-600 cursor-pointer transition"></i>
            </div>
          </div>
        </div>

        <!-- quick actions cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
          <!-- Active Booking Card -->
          <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4 stat-card">
            <div class="h-12 w-12 rounded-full bg-amber-100 flex items-center justify-center text-amber-700 text-xl">
              <i class="fa-solid fa-bed"></i>
            </div>
            <div>
              <p class="text-xs text-slate-500">active booking</p>
              <p class="font-semibold text-lg" id="activeBookingRoom">
                <?php echo $activeBooking['room_name'] ?? '—'; ?>
              </p>
              <span class="text-xs px-2 py-0.5 rounded-full" id="bookingStatus" style="background-color: <?php
              echo $activeBooking['status'] == 'confirmed' ? '#dcfce7' :
                ($activeBooking['status'] == 'pending' ? '#fef3c7' : '#f1f5f9');
              ?>; color: <?php
              echo $activeBooking['status'] == 'confirmed' ? '#166534' :
                ($activeBooking['status'] == 'pending' ? '#92400e' : '#475569');
              ?>;">
                <?php echo $activeBooking['status'] ?? 'no booking'; ?>
              </span>
            </div>
          </div>

          <!-- Today's Reservation Card -->
          <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4 stat-card">
            <div class="h-12 w-12 rounded-full bg-amber-100 flex items-center justify-center text-amber-700 text-xl">
              <i class="fas fa-clock"></i>
            </div>
            <div>
              <p class="text-xs text-slate-500">table tonight?</p>
              <p class="font-semibold text-lg" id="reservationTime">
                <?php
                if ($todayReservation) {
                  $time = date('g:i A', strtotime($todayReservation['reservation_time']));
                  echo $time . ' · ' . $todayReservation['guests'] . ' pax';
                } else {
                  echo '—';
                }
                ?>
              </p>
              <span class="text-xs" id="reservationNotice" style="color: <?php
              echo $todayReservation['status'] == 'confirmed' ? '#166534' :
                ($todayReservation['status'] == 'pending' ? '#92400e' : '#64748b');
              ?>;">
                <?php echo $todayReservation['status'] ?? 'no reservation'; ?>
              </span>
            </div>
          </div>

          <!-- Food Order Card -->
          <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4 stat-card">
            <div class="h-12 w-12 rounded-full bg-amber-100 flex items-center justify-center text-amber-700 text-xl">
              <i class="fa-solid fa-bag-shopping"></i>
            </div>
            <div>
              <p class="text-xs text-slate-500">food order</p>
              <p class="font-semibold text-lg" id="foodOrderAmount">
                <?php
                if ($latestOrder) {
                  echo '₱' . number_format($latestOrder['total_amount'], 2);
                } else {
                  echo '₱0';
                }
                ?>
              </p>
              <span class="text-xs text-slate-500" id="foodOrderStatus">
                <?php echo $latestOrder['status'] ?? 'empty'; ?>
              </span>
            </div>
          </div>

          <!-- Rewards Card -->
          <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4 stat-card">
            <div class="h-12 w-12 rounded-full bg-amber-100 flex items-center justify-center text-amber-700 text-xl">
              <i class="fas fa-star"></i>
            </div>
            <div>
              <p class="text-xs text-slate-500">rewards</p>
              <p class="font-semibold text-lg" id="pointsDisplay"><?php echo number_format($points); ?> pts</p>
              <span class="text-xs text-slate-400" id="pointsMessage">
                <?php
                if ($points >= 5000)
                  echo '<span class="text-purple-600">platinum</span>';
                elseif ($points >= 2000)
                  echo '<span class="text-amber-600">gold</span>';
                elseif ($points >= 1000)
                  echo '<span class="text-slate-600">silver</span>';
                else
                  echo 'earn points';
                ?>
              </span>
            </div>
          </div>
        </div>

        <!-- TWO COLUMN LAYOUT: left modules + right summary -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

          <!-- left col: main modules -->
          <div class="lg:col-span-2 space-y-6">

            <!-- Hotel room booking card -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200">
              <div class="flex items-center justify-between mb-3">
                <h2 class="font-semibold text-slate-800 flex items-center gap-2">
                  <i class="fa-solid fa-hotel text-amber-600"></i> hotel booking · available rooms
                </h2>
                <a href="./hotel_booking.php" class="text-sm text-amber-700 hover:underline">view all</a>
              </div>
              <div class="grid grid-cols-2 gap-3">
                <a href="./hotel_booking.php"
                  class="border rounded-xl p-3 hover:shadow-sm cursor-pointer block transition">
                  <span class="font-medium">deluxe twin</span>
                  <span class="block text-xs text-slate-500">PHP 4,200 / night</span>
                </a>
                <a href="./hotel_booking.php"
                  class="border rounded-xl p-3 hover:shadow-sm cursor-pointer block transition">
                  <span class="font-medium">ocean suite</span>
                  <span class="block text-xs text-slate-500">PHP 6,900 / night</span>
                </a>
              </div>
              <div class="flex gap-3 mt-4 text-sm">
                <div class="flex items-center gap-1 bg-slate-100 p-2 rounded-lg">
                  <i class="fas fa-calendar"></i>
                  <span id="defaultDates">
                    <?php
                    if ($activeBooking) {
                      echo date('M d', strtotime($activeBooking['check_in'])) . ' - ' . date('M d', strtotime($activeBooking['check_out']));
                    } else {
                      echo 'select dates';
                    }
                    ?>
                  </span>
                </div>
                <a href="./hotel_booking.php"
                  class="bg-amber-600 text-white px-4 py-2 rounded-xl text-sm font-medium hover:bg-amber-700 transition inline-block">
                  check availability
                </a>
              </div>
            </div>

            <!-- Restaurant Reservation & Menu together -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200">
              <div class="flex flex-wrap gap-6">
                <div class="flex-1">
                  <h2 class="font-semibold text-slate-800 flex items-center gap-2 mb-2">
                    <i class="fas fa-clock text-amber-600"></i> restaurant reservation
                  </h2>
                  <p class="text-xs text-slate-500 mb-2">today, <span id="todayDate"></span> · 2 guests</p>
                  <select id="reservationTimeSelect" class="border border-slate-200 rounded-lg p-2 text-sm w-full mb-2">
                    <option value="19:00:00">7:00 PM</option>
                    <option value="19:30:00">7:30 PM</option>
                    <option value="20:00:00">8:00 PM</option>
                  </select>
                  <a href="./restaurant_reservation.php"
                    class="block w-full text-center bg-white border border-amber-600 text-amber-700 py-2 rounded-xl text-sm hover:bg-amber-50 transition">
                    reserve table
                  </a>
                </div>
                <div class="flex-1">
                  <h2 class="font-semibold text-slate-800 flex items-center gap-2 mb-2">
                    <i class="fa-solid fa-bag-shopping text-amber-600"></i> menu / order
                  </h2>
                  <div class="flex justify-between text-sm"><span>🍜 sinigang</span><span>₱320</span></div>
                  <div class="flex justify-between text-sm"><span>🥩 sisig</span><span>₱290</span></div>
                  <div class="flex justify-between text-sm border-b pb-1"><span>🍚 rice</span><span>₱50</span></div>
                  <div class="flex justify-between font-medium mt-1"><span>total</span><span>₱660</span></div>
                  <a href="./order_food.php"
                    class="block w-full text-center bg-amber-600 text-white py-2 mt-3 rounded-xl text-sm hover:bg-amber-700 transition">
                    order now
                  </a>
                </div>
              </div>
            </div>

            <!-- Payment & Loyalty -->
            <div
              class="bg-white p-5 rounded-2xl border border-slate-200 flex flex-wrap gap-4 items-center justify-between">
              <div>
                <span class="text-xs text-slate-400">payment due</span>
                <p class="font-semibold text-xl" id="paymentDue">
                  <?php
                  $availableBalance = isset($balance['available_balance']) ? $balance['available_balance'] : 0;
                  if ($availableBalance > 0):
                    ?>
                    ₱<?php echo number_format($availableBalance, 2); ?> <span class="text-xs text-slate-400">/ due</span>
                  <?php else: ?>
                    ₱0 <span class="text-xs text-slate-400">/ no balance</span>
                  <?php endif; ?>
                </p>
                <div class="flex gap-2 mt-1 flex-wrap" id="paymentMethodsDisplay">
                  <?php if (empty($paymentMethods)): ?>
                    <span class="bg-slate-100 text-xs px-2 py-1 rounded-full">no methods</span>
                  <?php else: ?>
                    <?php foreach ($paymentMethods as $method): ?>
                      <span class="<?php
                      echo $method['method_type'] == 'gcash' ? 'bg-blue-50 text-blue-700' :
                        ($method['method_type'] == 'cash' ? 'bg-green-50 text-green-700' : 'bg-slate-100');
                      ?> text-xs px-2 py-1 rounded-full flex items-center">
                        <?php
                        if ($method['method_type'] == 'gcash')
                          echo '<i class="fa-brands fa-gcash mr-1"></i>';
                        elseif ($method['method_type'] == 'cash')
                          echo '<i class="fa-solid fa-money-bill-wave mr-1"></i>';
                        else
                          echo '<i class="fas fa-credit-card mr-1"></i>';
                        echo $method['display_name'] ?? $method['method_type'];
                        if ($method['is_default'])
                          echo ' <span class="ml-1 text-[10px]">(default)</span>';
                        ?>
                      </span>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </div>
                <a href="./payments.php" class="text-xs text-amber-700 mt-2 hover:underline block">manage payments →</a>
              </div>
              <div class="border-l pl-4">
                <p class="text-xs text-amber-700">loyalty points</p>
                <p class="font-bold text-2xl" id="loyaltyPoints"><?php echo number_format($points); ?></p>
                <p class="text-xs text-slate-500" id="loyaltyMessage">
                  <?php
                  if ($points >= 5000)
                    echo 'platinum member · 10% bonus';
                  elseif ($points >= 2000)
                    echo 'gold member · 5% bonus';
                  elseif ($points >= 1000)
                    echo 'silver member · 2% bonus';
                  else
                    echo 'earn points with every purchase';
                  ?>
                </p>
                <a href="./loyalty_rewards.php" class="text-xs text-amber-700 mt-2 hover:underline block">view rewards
                  →</a>
              </div>
            </div>

            <!-- Reviews & feedback -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200">
              <h2 class="font-semibold text-slate-800 flex gap-2 items-center">
                <i class="fas fa-star text-amber-600"></i> how was your last stay?
              </h2>
              <div class="flex gap-3 my-2">
                <div class="flex text-yellow-400 text-xl cursor-pointer" id="starRating">
                  <i class="fas fa-star" data-rating="1"></i>
                  <i class="fas fa-star" data-rating="2"></i>
                  <i class="fas fa-star" data-rating="3"></i>
                  <i class="fas fa-star" data-rating="4"></i>
                  <i class="fas fa-star" data-rating="5"></i>
                </div>
                <span class="text-xs text-slate-500">tap to rate</span>
              </div>
              <textarea id="reviewText" rows="2" placeholder="write a review (optional) …"
                class="w-full border border-slate-200 rounded-xl p-2 text-sm"></textarea>
              <button onclick="submitReview()"
                class="bg-amber-600 text-white px-5 py-2 mt-2 rounded-xl text-sm hover:bg-amber-700 transition">submit
                feedback</button>
            </div>
          </div>

          <!-- right column: summary cards -->
          <div class="space-y-5">
            <!-- Booking Management -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200">
              <h3 class="font-semibold flex items-center gap-1">
                <i class="fas fa-rectangle-list text-amber-600"></i> my bookings
              </h3>
              <div class="mt-3 space-y-3" id="bookingsList">
                <?php if (empty($recentBookings)): ?>
                  <div class="text-center text-slate-400 py-4">
                    <i class="fas fa-calendar-xmark text-3xl mb-2 text-slate-300"></i>
                    <p>No bookings yet.</p>
                    <p class="text-xs mt-1 text-slate-400">book your first stay!</p>
                  </div>
                <?php else: ?>
                  <?php foreach ($recentBookings as $booking): ?>
                    <div class="flex justify-between items-center border-b pb-2 booking-item p-2 rounded">
                      <div>
                        <span class="font-medium text-sm">#<?php echo substr($booking['booking_reference'], -8); ?></span>
                        <p class="text-xs text-slate-500">
                          <?php echo $booking['room_name']; ?> ·
                          <?php echo date('M d', strtotime($booking['check_in'])); ?>-<?php echo date('d', strtotime($booking['check_out'])); ?>
                        </p>
                      </div>
                      <span class="text-xs <?php
                      echo $booking['status'] == 'confirmed' ? 'bg-green-100 text-green-700' :
                        ($booking['status'] == 'pending' ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-700');
                      ?> px-2 py-0.5 rounded-full">
                        <?php echo $booking['status']; ?>
                      </span>
                    </div>
                  <?php endforeach; ?>
                <?php endif; ?>
              </div>
              <a href="./my_reservation.php" class="text-xs text-amber-700 block mt-2 hover:underline">view all bookings
                →</a>
            </div>

            <!-- Notifications & updates -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200">
              <div class="flex items-center justify-between">
                <h3 class="font-semibold flex gap-1"><i class="fas fa-bell text-amber-600"></i> updates</h3>
                <?php if ($unread_count > 0): ?>
                  <span class="bg-amber-100 text-amber-800 text-xs px-2 py-0.5 rounded-full"
                    id="notificationBadge"><?php echo $unread_count; ?> new</span>
                <?php else: ?>
                  <span class="bg-slate-100 text-slate-600 text-xs px-2 py-0.5 rounded-full"
                    id="notificationBadge">0</span>
                <?php endif; ?>
              </div>
              <ul class="text-sm space-y-2 mt-3" id="notificationsList">
                <?php if (empty($notifications)): ?>
                  <li class="text-slate-400 text-xs text-center py-4">
                    <i class="fas fa-bell-slash text-2xl mb-2 text-slate-300"></i>
                    <p>No notifications</p>
                  </li>
                <?php else: ?>
                  <?php foreach ($notifications as $notif): ?>
                    <li class="flex gap-2 text-xs border-b border-slate-100 pb-2 last:border-0">
                      <i
                        class="fas <?php echo $notif['icon'] ?? 'fa-bell'; ?> <?php echo $notif['is_read'] ? 'text-slate-400' : 'text-amber-600'; ?> mt-0.5"></i>
                      <span>
                        <span class="font-medium"><?php echo htmlspecialchars($notif['title']); ?></span>
                        <span
                          class="text-slate-500 block"><?php echo htmlspecialchars(substr($notif['message'], 0, 40)) . (strlen($notif['message']) > 40 ? '...' : ''); ?></span>
                        <span class="text-[10px] text-slate-400"><?php echo $notif['time_ago'] ?? ''; ?></span>
                      </span>
                    </li>
                  <?php endforeach; ?>
                <?php endif; ?>
              </ul>
              <a href="./notifications.php" class="text-xs text-amber-700 block mt-2 hover:underline">view all →</a>
            </div>

            <!-- Support / help center -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200">
              <h3 class="font-semibold flex items-center gap-2 mb-3"><i
                  class="fas fa-circle-question text-amber-600"></i> help & support</h3>
              <div class="space-y-2 text-sm">
                <p class="flex items-center gap-2"><i class="fa-solid fa-phone text-amber-600 w-4"></i> +63 (2) 1234
                  5678</p>
                <p class="flex items-center gap-2"><i class="fa-solid fa-comment text-amber-600 w-4"></i> chat with
                  concierge</p>
                <p class="flex items-center gap-2"><i class="fa-solid fa-envelope text-amber-600 w-4"></i>
                  support@lucas.stay</p>
              </div>
              <button onclick="contactSupport()"
                class="w-full border border-amber-600 text-amber-700 rounded-xl py-2 mt-4 text-sm hover:bg-amber-50 transition">
                visit FAQ
              </button>
            </div>

            <!-- profile summary -->
            <div
              class="bg-gradient-to-r from-amber-50 to-amber-100 p-4 rounded-2xl border border-amber-200 flex items-center gap-3">
              <div
                class="h-12 w-12 rounded-full bg-amber-200 flex items-center justify-center text-amber-800 font-bold text-lg">
                <?php echo $initials; ?>
              </div>
              <div class="flex-1">
                <p class="font-medium text-sm" id="profileName">
                  <?php echo htmlspecialchars($user['full_name'] ?? 'Guest'); ?>
                </p>
                <p class="text-xs text-slate-600" id="profileContact" class="truncate">
                  <?php echo htmlspecialchars($user['email'] ?? '—'); ?>
                </p>
                <div class="flex gap-3 mt-1">
                  <a href="./my_profile.php" class="text-xs text-amber-700 hover:underline">edit profile</a>
                  <a href="./my_profile.php" class="text-xs text-amber-700 hover:underline">view details</a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </main>
    </div>

    <script>
      (function () {
        // ---------- DATA FROM DATABASE ----------
        const userData = {
          id: <?php echo $_SESSION['user_id']; ?>,
          name: '<?php echo addslashes($user['full_name'] ?? 'Guest'); ?>',
          firstName: '<?php echo addslashes($user['first_name'] ?? ''); ?>',
          lastName: '<?php echo addslashes($user['last_name'] ?? ''); ?>',
          email: '<?php echo addslashes($user['email'] ?? ''); ?>',
          phone: '<?php echo addslashes($user['phone'] ?? ''); ?>',
          points: <?php echo $points; ?>,
          tier: '<?php echo $member_tier; ?>',
          initials: '<?php echo $initials; ?>',
          fullName: '<?php echo addslashes($user['full_name'] ?? 'Guest'); ?>'
        };

        const balance = {
          total: <?php echo $balance['total_balance']; ?>,
          pending: <?php echo $balance['pending_balance']; ?>,
          available: <?php echo $balance['available_balance']; ?>
        };

        const activeBooking = <?php echo json_encode($activeBooking); ?>;
        const todayReservation = <?php echo json_encode($todayReservation); ?>;
        const recentBookings = <?php echo json_encode($recentBookings); ?>;
        const notifications = <?php echo json_encode($notifications); ?>;
        const paymentMethods = <?php echo json_encode($paymentMethods); ?>;
        const unreadCount = <?php echo $unread_count; ?>;
        const pointsNeeded = <?php echo $pointsNeeded; ?>;
        const greeting = '<?php echo $greeting; ?>';
        const latestOrder = <?php echo json_encode($latestOrder); ?>;

        // ---------- DATE AND TIME ----------
        function updateDateTime() {
          const now = new Date();
          const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
          const dateEl = document.getElementById('currentDate');
          if (dateEl) {
            dateEl.textContent = now.toLocaleDateString('en-US', options).toLowerCase();
          }

          const today = new Date();
          const month = today.toLocaleDateString('en-US', { month: 'short' });
          const day = today.getDate();
          const todayDateEl = document.getElementById('todayDate');
          if (todayDateEl) {
            todayDateEl.textContent = `${month} ${day}`;
          }
        }

        // ---------- STAR RATING ----------
        function initStarRating() {
          const stars = document.querySelectorAll('#starRating i');
          stars.forEach(star => {
            star.addEventListener('mouseenter', function () {
              const rating = parseInt(this.dataset.rating);
              stars.forEach((s, index) => {
                if (index < rating) {
                  s.className = 'fa-solid fa-star text-yellow-400';
                } else {
                  s.className = 'fas fa-star text-yellow-400';
                }
              });
            });

            star.addEventListener('mouseleave', function () {
              stars.forEach(s => {
                s.className = 'fas fa-star text-yellow-400';
              });
            });

            star.addEventListener('click', function () {
              const rating = parseInt(this.dataset.rating);
              stars.forEach((s, index) => {
                if (index < rating) {
                  s.className = 'fa-solid fa-star text-yellow-400';
                } else {
                  s.className = 'fas fa-star text-yellow-400';
                }
              });
              localStorage.setItem('lastRating', rating);
            });
          });
        }

        // ---------- ACTION FUNCTIONS ----------
        window.makeReservation = function () {
          window.location.href = './restaurant_reservation.php';
        };

        window.addToCart = function () {
          window.location.href = './order_food.php';
        };

        window.submitReview = function () {
          const review = document.getElementById('reviewText').value;
          const rating = localStorage.getItem('lastRating') || 0;

          if (review.trim() || rating > 0) {
            Swal.fire({
              title: 'Thank You!',
              text: 'Your feedback has been submitted.',
              icon: 'success',
              confirmButtonColor: '#d97706',
              timer: 2000
            });
            document.getElementById('reviewText').value = '';

            // Reset stars
            document.querySelectorAll('#starRating i').forEach(s => {
              s.className = 'fas fa-star text-yellow-400';
            });
            localStorage.removeItem('lastRating');
          } else {
            Swal.fire({
              title: 'Oops...',
              text: 'Please write a review or rate us',
              icon: 'info',
              confirmButtonColor: '#d97706'
            });
          }
        };

        window.contactSupport = function () {
          Swal.fire({
            title: 'Contact Support',
            html: `
              <div class="text-left">
                <p><i class="fa-solid fa-phone text-amber-600 mr-2"></i> +63 (2) 1234 5678</p>
                <p><i class="fa-solid fa-envelope text-amber-600 mr-2"></i> support@lucas.stay</p>
                <p class="mt-2 text-sm">Available 24/7 for your convenience.</p>
              </div>
            `,
            icon: 'info',
            confirmButtonColor: '#d97706',
            confirmButtonText: 'Close'
          });
        };

        // ---------- CHECK FOR PENDING RESERVATIONS ----------
        function checkPendingReservations() {
          const pendingReservation = sessionStorage.getItem('pendingRestaurantReservation');
          if (pendingReservation) {
            try {
              const reservation = JSON.parse(pendingReservation);
              // Update UI with pending info
              const resTimeEl = document.getElementById('reservationTime');
              const resNoticeEl = document.getElementById('reservationNotice');

              if (resTimeEl) {
                resTimeEl.textContent = `${reservation.time} · ${reservation.guests} pax`;
              }
              if (resNoticeEl) {
                resNoticeEl.textContent = 'pending payment';
                resNoticeEl.className = 'text-xs text-amber-600';
              }

              sessionStorage.removeItem('pendingRestaurantReservation');
            } catch (e) {
              console.error('Error parsing pending reservation', e);
            }
          }

          const pendingBooking = sessionStorage.getItem('pendingBooking');
          if (pendingBooking) {
            try {
              const booking = JSON.parse(pendingBooking);
              // Update UI with pending info
              const activeRoomEl = document.getElementById('activeBookingRoom');
              const bookingStatusEl = document.getElementById('bookingStatus');

              if (activeRoomEl) {
                activeRoomEl.textContent = booking.room_name || '—';
              }
              if (bookingStatusEl) {
                bookingStatusEl.textContent = 'pending';
                bookingStatusEl.className = 'text-xs bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full';
              }

              sessionStorage.removeItem('pendingBooking');
            } catch (e) {
              console.error('Error parsing pending booking', e);
            }
          }
        }

        // ---------- UPDATE FOOD ORDER CARD ----------
        function updateFoodOrderCard() {
          const amountEl = document.getElementById('foodOrderAmount');
          const statusEl = document.getElementById('foodOrderStatus');

          if (latestOrder && amountEl && statusEl) {
            amountEl.textContent = '₱' + parseFloat(latestOrder.total_amount).toFixed(2);
            statusEl.textContent = latestOrder.status;

            // Add color based on status
            if (latestOrder.status === 'completed') {
              statusEl.className = 'text-xs text-green-600';
            } else if (latestOrder.status === 'pending') {
              statusEl.className = 'text-xs text-amber-600';
            } else if (latestOrder.status === 'preparing') {
              statusEl.className = 'text-xs text-blue-600';
            } else {
              statusEl.className = 'text-xs text-slate-500';
            }
          }
        }

        // ---------- INITIALIZE ----------
        document.addEventListener('DOMContentLoaded', function () {
          updateDateTime();
          checkPendingReservations();
          initStarRating();
          updateFoodOrderCard();

          // Set interval to update time every minute
          setInterval(updateDateTime, 60000);
        });

      })();
    </script>
  </body>

</html>