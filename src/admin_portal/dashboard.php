<?php
/**
 * View - Admin Dashboard
 */
require_once '../../controller/admin/get/dashboard.php';
?>
<!DOCTYPE html>
<html lang="en">

  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin · Hotel & Restaurant Management</title>
    <!-- Custom Tailwind CSS (compiled) -->
    <link href="/src/output.css" rel="stylesheet">
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
      /* tiny custom for dropdowns and side hover */
      .transition-side {
        transition: all 0.2s ease;
      }

      .dropdown-arrow {
        transition: transform 0.2s;
      }

      details[open] .dropdown-arrow {
        transform: rotate(90deg);
      }

      details>summary {
        list-style: none;
      }

      details summary::-webkit-details-marker {
        display: none;
      }

      /* Status badge colors */
      .status-confirmed {
        @apply bg-green-100 text-green-700;
      }

      .status-pending {
        @apply bg-amber-100 text-amber-700;
      }

      .status-cancelled {
        @apply bg-red-100 text-red-700;
      }

      .status-completed {
        @apply bg-slate-100 text-slate-700;
      }

      .status-preparing {
        @apply bg-blue-100 text-blue-700;
      }

      .status-served {
        @apply bg-emerald-100 text-emerald-700;
      }

      .status-ready {
        @apply bg-purple-100 text-purple-700;
      }

      .status-waitlist {
        @apply bg-orange-100 text-orange-700;
      }
    </style>
  </head>

  <body>
    <!-- APP CONTAINER: flex row (sidebar + main) -->
    <div class="min-h-screen flex flex-col lg:flex-row">

      <!-- ========== SIDEBAR (GROUPED WITH DROPDOWNS) ========== -->
      <aside class="lg:w-80 bg-white border-r border-slate-200 shadow-sm lg:min-h-screen shrink-0 overflow-y-auto">
        <!-- brand -->
        <div class="px-5 py-6 border-b border-slate-100 flex items-center gap-2">
          <i class="fa-solid fa-utensils text-amber-600 text-xl"></i>
          <i class="fa-solid fa-bed text-amber-600 text-xl"></i>
          <span class="font-semibold text-lg tracking-tight text-slate-800">HNR<span class="text-amber-600">
              Admin</span></span>
        </div>

        <!-- admin badge -->
        <div class="flex items-center gap-3 px-5 py-4 border-b border-slate-100 bg-slate-50/60">
          <div class="h-9 w-9 rounded-full bg-amber-200 flex items-center justify-center text-amber-800 font-bold">
            <?php echo $initials; ?>
          </div>
          <div>
            <p class="font-medium text-sm"><?php echo htmlspecialchars($admin['full_name'] ?? 'Admin User'); ?></p>
            <p class="text-xs text-slate-500"><?php echo htmlspecialchars($admin['role'] ?? 'administrator'); ?></p>
          </div>
        </div>

        <!-- ===== SIDEBAR MENU (grouped with dropdowns) ===== -->
        <nav class="p-4 space-y-2 text-sm">

          <!-- Dashboard (top level, no dropdown) -->
          <a href="./dashboard.php"
            class="flex items-center gap-3 px-4 py-2.5 rounded-xl bg-amber-50 text-amber-800 font-medium transition-hover">
            <i class="fa-solid fa-table-cells-large w-5 text-amber-600"></i>
            <span>Dashboard</span>
          </a>

          <!-- HOTEL MANAGEMENT GROUP (dropdown) -->
          <details class="group" open>
            <summary
              class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-700 hover:bg-amber-50 cursor-pointer transition-side">
              <i class="fa-solid fa-hotel w-5 text-slate-400 group-open:text-amber-600"></i>
              <span class="font-medium">HOTEL MANAGEMENT</span>
              <i class="fa-solid fa-chevron-right dropdown-arrow ml-auto text-xs text-slate-400"></i>
            </summary>
            <div class="ml-6 mt-1 space-y-1 pl-3 border-l-2 border-amber-100">
              <a href="../admin_portal/hotel_management/front_desk_reception.php"
                class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i
                  class="fa-regular fa-reception w-4 text-slate-400"></i> Front Desk / Reception</a>
              <a href="../admin_portal/hotel_management/room_management.php"
                class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i
                  class="fa-solid fa-bed w-4 text-slate-400"></i> Room Management</a>
              <a href="../admin_portal/hotel_management/arrival/reservation_&_booking.php"
                class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i
                  class="fa-regular fa-calendar-check w-4 text-slate-400"></i> Reservations & Booking</a>
              <a href="../admin_portal/hotel_management/housekeeping_&_maintenance.php"
                class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i
                  class="fa-solid fa-broom w-4 text-slate-400"></i> Housekeeping & Maintenance</a>
              <a href="../admin_portal/hotel_management/event_&_conference.php"
                class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i
                  class="fa-regular fa-calendar w-4 text-slate-400"></i> Events & Conference</a>
            </div>
          </details>

          <!-- RESTAURANT MANAGEMENT GROUP -->
          <details class="group" open>
            <summary
              class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-700 hover:bg-amber-50 cursor-pointer">
              <i class="fa-solid fa-utensils w-5 text-slate-400 group-open:text-amber-600"></i>
              <span class="font-medium">RESTAURANT MANAGEMENT</span>
              <i class="fa-solid fa-chevron-right dropdown-arrow ml-auto text-xs text-slate-400"></i>
            </summary>
            <div class="ml-6 mt-1 space-y-1 pl-3 border-l-2 border-amber-100">
              <a href="../admin_portal/restaurant_management/table_reservation.php"
                class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i
                  class="fa-regular fa-clock w-4"></i> Table Reservation</a>
              <a href="../admin_portal/restaurant_management/menu_management.php"
                class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i
                  class="fa-solid fa-bars w-4"></i> Menu Management</a>
              <a href="../admin_portal/restaurant_management/orders_pos.php"
                class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i
                  class="fa-solid fa-cash-register w-4"></i> Orders / POS</a>
              <a href="../admin_portal/restaurant_management/kitchen_orders.php"
                class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i
                  class="fa-solid fa-fire w-4"></i> Kitchen Orders (KOT)</a>
              <a href="../admin_portal/restaurant_management/wait_staff_management.php"
                class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i
                  class="fa-regular fa-user w-4"></i> Wait Staff Management</a>
            </div>
          </details>

          <!-- CUSTOMER MANAGEMENT -->
          <details class="group" open>
            <summary
              class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-700 hover:bg-amber-50 cursor-pointer">
              <i class="fa-regular fa-address-book w-5 text-slate-400 group-open:text-amber-600"></i>
              <span class="font-medium">CUSTOMER MANAGEMENT</span>
              <i class="fa-solid fa-chevron-right dropdown-arrow ml-auto text-xs text-slate-400"></i>
            </summary>
            <div class="ml-6 mt-1 space-y-1 pl-3 border-l-2 border-amber-100">
              <a href="../admin_portal/customer_management/customer_relationship.php"
                class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i
                  class="fa-regular fa-handshake w-4"></i> Guest Relationship (CRM)</a>
              <a href="../admin_portal/customer_management/loyalty_rewards.php"
                class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i
                  class="fa-regular fa-star w-4"></i> Loyalty & Rewards</a>
              <a href="../admin_portal/customer_management/customer_feedback_&_reviews.php"
                class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i
                  class="fa-regular fa-pen-to-square w-4"></i> Customer Feedback & Reviews</a>
            </div>
          </details>

          <!-- OPERATIONS -->
          <details class="group" open>
            <summary
              class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-700 hover:bg-amber-50 cursor-pointer">
              <i class="fa-solid fa-gears w-5 text-slate-400 group-open:text-amber-600"></i>
              <span class="font-medium">OPERATIONS</span>
              <i class="fa-solid fa-chevron-right dropdown-arrow ml-auto text-xs text-slate-400"></i>
            </summary>
            <div class="ml-6 mt-1 space-y-1 pl-3 border-l-2 border-amber-100">
              <a href="../admin_portal/operations/inventory_&_stocks.php"
                class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i
                  class="fa-solid fa-boxes w-4"></i> Inventory & Stock</a>
              <a href="../admin_portal/operations/billing_&_payment.php"
                class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i
                  class="fa-regular fa-credit-card w-4"></i> Billing & Payments</a>
              <a href="../admin_portal/operations/payment_gateway.php"
                class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i
                  class="fa-solid fa-wifi w-4"></i> Payment Gateway</a>
            </div>
          </details>

          <!-- MARKETING -->
          <details class="group" open>
            <summary
              class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-700 hover:bg-amber-50 cursor-pointer">
              <i class="fa-solid fa-megaphone w-5 text-slate-400 group-open:text-amber-600"></i>
              <span class="font-medium">MARKETING</span>
              <i class="fa-solid fa-chevron-right dropdown-arrow ml-auto text-xs text-slate-400"></i>
            </summary>
            <div class="ml-6 mt-1 space-y-1 pl-3 border-l-2 border-amber-100">
              <a href="../admin_portal/marketing/hotelmarketing_&_promotions.php"
                class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i
                  class="fa-regular fa-gem w-4"></i> Hotel Marketing & Promotions</a>
              <a href="../admin_portal/marketing/online_ordering_integration.php"
                class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i
                  class="fa-solid fa-cart-shopping w-4"></i> Online Ordering Integration</a>
            </div>
          </details>

          <!-- REPORTS & ANALYTICS -->
          <details class="group" open>
            <summary
              class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-700 hover:bg-amber-50 cursor-pointer">
              <i class="fa-solid fa-chart-simple w-5 text-slate-400 group-open:text-amber-600"></i>
              <span class="font-medium">REPORTS & ANALYTICS</span>
              <i class="fa-solid fa-chevron-right dropdown-arrow ml-auto text-xs text-slate-400"></i>
            </summary>
            <div class="ml-6 mt-1 space-y-1 pl-3 border-l-2 border-amber-100">
              <a href="../admin_portal/reports_&_analytics/sales_report.php"
                class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i
                  class="fa-solid fa-chart-line w-4"></i> Sales Reports</a>
              <a href="../admin_portal/reports_&_analytics/booking_reports.php"
                class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i
                  class="fa-regular fa-calendar w-4"></i> Booking Reports</a>
              <a href="../admin_portal/reports_&_analytics/analytics_dashboard.php"
                class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i
                  class="fa-solid fa-chart-pie w-4"></i> Analytics Dashboard</a>
            </div>
          </details>

          <!-- SYSTEM (with special items: door lock integration) -->
          <details class="group" open>
            <summary
              class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-700 hover:bg-amber-50 cursor-pointer">
              <i class="fa-solid fa-computer w-5 text-slate-400 group-open:text-amber-600"></i>
              <span class="font-medium">SYSTEM</span>
              <i class="fa-solid fa-chevron-right dropdown-arrow ml-auto text-xs text-slate-400"></i>
            </summary>
            <div class="ml-6 mt-1 space-y-1 pl-3 border-l-2 border-amber-100">
              <a href="../admin_portal/system/channel_management.php"
                class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i
                  class="fa-solid fa-code-branch w-4"></i> Channel Management</a>
              <a href="../admin_portal/system/door_lock_integration.php"
                class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i
                  class="fa-solid fa-lock w-4"></i> Door Lock Integration</a>
              <a href="../admin_portal/system/settings.php"
                class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i
                  class="fa-solid fa-sliders w-4"></i> Settings</a>
            </div>
          </details>

          <!-- logout -->
          <div class="border-t border-slate-200 pt-3 mt-3">
            <a href="../../controller/auth/logout.php"
              class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-500 hover:bg-red-50 hover:text-red-700">
              <i class="fa-solid fa-arrow-right-from-bracket w-5"></i>
              <span>Logout</span>
            </a>
          </div>
        </nav>
      </aside>

      <!-- ========== MAIN CONTENT (DASHBOARD OVERVIEW) ========== -->
      <main class="flex-1 p-5 lg:p-8 overflow-y-auto">

        <!-- header / page title -->
        <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
          <h1 class="text-2xl font-semibold text-slate-800">Dashboard overview</h1>
          <div class="flex gap-3 text-sm">
            <span class="bg-white border rounded-full px-4 py-2 flex items-center gap-2">
              <i class="fa-regular fa-calendar text-slate-400"></i>
              <?php echo date('F j, Y'); ?>
            </span>
            <span class="bg-white border rounded-full px-4 py-2">
              <i class="fa-regular fa-bell"></i>
            </span>
          </div>
        </div>

        <!-- ===== TOP STATISTIC CARDS (8 cards) ===== -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
          <div class="bg-white p-5 rounded-2xl border border-slate-200 flex items-center gap-3 shadow-sm">
            <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-700"><i
                class="fa-solid fa-door-open"></i></div>
            <div>
              <p class="text-xs text-slate-500">Total rooms</p>
              <p class="text-xl font-semibold"><?php echo $totalRooms; ?></p>
            </div>
          </div>
          <div class="bg-white p-5 rounded-2xl border border-slate-200 flex items-center gap-3 shadow-sm">
            <div class="h-10 w-10 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-700"><i
                class="fa-solid fa-person-walking-luggage"></i></div>
            <div>
              <p class="text-xs text-slate-500">Occupied</p>
              <p class="text-xl font-semibold"><?php echo $occupiedRooms; ?></p>
            </div>
          </div>
          <div class="bg-white p-5 rounded-2xl border border-slate-200 flex items-center gap-3 shadow-sm">
            <div class="h-10 w-10 rounded-full bg-amber-100 flex items-center justify-center text-amber-700"><i
                class="fa-regular fa-calendar-check"></i></div>
            <div>
              <p class="text-xs text-slate-500">Today's bookings</p>
              <p class="text-xl font-semibold"><?php echo $todayBookings; ?></p>
            </div>
          </div>
          <div class="bg-white p-5 rounded-2xl border border-slate-200 flex items-center gap-3 shadow-sm">
            <div class="h-10 w-10 rounded-full bg-orange-100 flex items-center justify-center text-orange-700"><i
                class="fa-solid fa-utensils"></i></div>
            <div>
              <p class="text-xs text-slate-500">Restaurant orders</p>
              <p class="text-xl font-semibold"><?php echo $todayOrders; ?></p>
            </div>
          </div>
          <div class="bg-white p-5 rounded-2xl border border-slate-200 flex items-center gap-3 shadow-sm">
            <div class="h-10 w-10 rounded-full bg-green-100 flex items-center justify-center text-green-700"><i
                class="fa-solid fa-peso-sign"></i></div>
            <div>
              <p class="text-xs text-slate-500">Sales today</p>
              <p class="text-xl font-semibold">₱<?php echo number_format($todaySales / 1000, 1); ?>k</p>
            </div>
          </div>
          <div class="bg-white p-5 rounded-2xl border border-slate-200 flex items-center gap-3 shadow-sm">
            <div class="h-10 w-10 rounded-full bg-rose-100 flex items-center justify-center text-rose-700"><i
                class="fa-solid fa-triangle-exclamation"></i></div>
            <div>
              <p class="text-xs text-slate-500">Low stock items</p>
              <p class="text-xl font-semibold"><?php echo $lowStockItems; ?></p>
            </div>
          </div>
          <div class="bg-white p-5 rounded-2xl border border-slate-200 flex items-center gap-3 shadow-sm lg:col-span-2">
            <div class="h-10 w-10 rounded-full bg-purple-100 flex items-center justify-center text-purple-700"><i
                class="fa-regular fa-clock"></i></div>
            <div>
              <p class="text-xs text-slate-500">check-ins today / pending</p>
              <p class="text-xl font-semibold"><?php echo $todayCheckins; ?> / <?php echo $pendingCheckins; ?></p>
            </div>
          </div>
        </div>

        <!-- MIDDLE SECTION: Left (activities) + Right (charts) -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">

          <!-- LEFT: Today's activities (check-in/out, reservations, events) -->
          <div class="lg:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-5">
            <!-- upcoming check-ins -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200">
              <h3 class="font-semibold flex items-center gap-2 text-slate-700"><i
                  class="fa-regular fa-calendar-check text-amber-600"></i> Upcoming check-ins</h3>
              <ul class="mt-3 space-y-2 text-sm">
                <?php if (empty($upcomingCheckins)): ?>
                  <li class="text-slate-400 text-center py-2">No upcoming check-ins</li>
                <?php else: ?>
                  <?php foreach ($upcomingCheckins as $checkin): ?>
                    <li class="flex justify-between">
                      <span>🔹 <?php echo htmlspecialchars($checkin['room_name'] ?? ''); ?> ·
                        <?php
                        $name_parts = explode(' ', $checkin['guest_name'] ?? 'Guest');
                        echo htmlspecialchars($name_parts[0]);
                        ?>
                      </span>
                      <span class="text-slate-500"><?php echo date('M d', strtotime($checkin['check_in'])); ?></span>
                    </li>
                  <?php endforeach; ?>
                <?php endif; ?>
              </ul>
            </div>
            <!-- upcoming check-outs -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200">
              <h3 class="font-semibold flex items-center gap-2 text-slate-700"><i
                  class="fa-regular fa-calendar-xmark text-amber-600"></i> Upcoming check-outs</h3>
              <ul class="mt-3 space-y-2 text-sm">
                <?php if (empty($upcomingCheckouts)): ?>
                  <li class="text-slate-400 text-center py-2">No upcoming check-outs</li>
                <?php else: ?>
                  <?php foreach ($upcomingCheckouts as $checkout): ?>
                    <li class="flex justify-between">
                      <span>🔹 <?php echo htmlspecialchars($checkout['room_name'] ?? ''); ?> ·
                        <?php
                        $name_parts = explode(' ', $checkout['guest_name'] ?? 'Guest');
                        echo htmlspecialchars($name_parts[0]);
                        ?>
                      </span>
                      <span class="text-slate-500"><?php echo date('M d', strtotime($checkout['check_out'])); ?></span>
                    </li>
                  <?php endforeach; ?>
                <?php endif; ?>
              </ul>
            </div>
            <!-- table reservations -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200">
              <h3 class="font-semibold flex items-center gap-2 text-slate-700"><i
                  class="fa-regular fa-clock text-amber-600"></i> Table reservations</h3>
              <ul class="mt-3 space-y-2 text-sm">
                <?php if (empty($tableReservations)): ?>
                  <li class="text-slate-400 text-center py-2">No table reservations today</li>
                <?php else: ?>
                  <?php foreach ($tableReservations as $res): ?>
                    <li>🍽️ Table <?php echo htmlspecialchars($res['table_number'] ?? '?'); ?> ·
                      <?php echo $res['guests']; ?> pax ·
                      <?php echo date('g:i A', strtotime($res['reservation_time'])); ?>
                      (<?php echo htmlspecialchars(explode(' ', $res['guest_name'] ?? '')[0] ?? 'Guest'); ?>)
                    </li>
                  <?php endforeach; ?>
                <?php endif; ?>
              </ul>
            </div>
            <!-- events -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200">
              <h3 class="font-semibold flex items-center gap-2 text-slate-700"><i
                  class="fa-regular fa-calendar-plus text-amber-600"></i> Events today</h3>
              <ul class="mt-3 space-y-2 text-sm">
                <?php if (empty($todayEvents)): ?>
                  <li class="text-slate-400 text-center py-2">No events scheduled today</li>
                <?php else: ?>
                  <?php foreach ($todayEvents as $event): ?>
                    <li>🎤 <?php echo htmlspecialchars($event['event_name']); ?> ·
                      <?php echo date('g:i A', strtotime($event['event_time'])); ?>
                      (<?php echo htmlspecialchars($event['location'] ?? 'TBD'); ?>)
                    </li>
                  <?php endforeach; ?>
                <?php endif; ?>
              </ul>
            </div>
          </div>

          <!-- RIGHT: CHARTS (placeholder visuals) -->
          <div class="space-y-5">
            <!-- daily sales mini chart -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200">
              <h3 class="font-semibold text-sm flex items-center gap-1"><i
                  class="fa-solid fa-chart-line text-amber-600"></i> Daily sales (this week)</h3>
              <div class="flex items-end gap-1 h-20 mt-4">
                <?php
                // Define days of week
                $days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];

                // If no sales data, create empty array with 7 days of zero sales
                if (empty($weeklySales)) {
                  $chartData = array_fill(0, 7, 0);
                  $maxSales = 1; // Avoid division by zero
                } else {
                  // Extract sales values
                  $chartData = array_column($weeklySales, 'total');
                  $maxSales = max($chartData) ?: 1; // Use 1 if max is 0 to avoid division by zero
                }

                // Generate bars for each day
                for ($i = 0; $i < 7; $i++):
                  // Get sales value for this day (if exists)
                  $saleValue = $chartData[$i] ?? 0;

                  // Calculate height percentage (minimum 4px for visibility)
                  $height = $maxSales > 0 ? max(4, ($saleValue / $maxSales) * 100) : 4;

                  // Color gradient based on value
                  $colorClass = 'bg-amber-200';
                  if ($saleValue > 0) {
                    if ($saleValue > $maxSales * 0.7)
                      $colorClass = 'bg-amber-500';
                    else if ($saleValue > $maxSales * 0.4)
                      $colorClass = 'bg-amber-400';
                    else if ($saleValue > $maxSales * 0.1)
                      $colorClass = 'bg-amber-300';
                  }
                  ?>
                  <div class="w-1/6 flex flex-col items-center">
                    <div class="<?php echo $colorClass; ?> w-full rounded-t" style="height: <?php echo $height; ?>px;">
                    </div>
                    <span class="text-[10px] mt-1 text-slate-500"><?php echo $days[$i]; ?></span>
                  </div>
                <?php endfor; ?>
              </div>
              <p class="text-xs text-slate-400 mt-2">
                <?php
                if ($thisWeekSales > 0):
                  echo $salesGrowth >= 0 ? '+' : '';
                  echo number_format($salesGrowth, 1); ?>% vs last week
                <?php else: ?>
                  No sales data this week
                <?php endif; ?>
              </p>
            </div>
            <!-- room occupancy -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200">
              <h3 class="font-semibold text-sm flex items-center gap-1"><i
                  class="fa-solid fa-chart-pie text-amber-600"></i> Occupancy rate</h3>
              <div class="mt-3 flex items-center gap-2">
                <div class="w-full bg-slate-200 h-2 rounded-full">
                  <div class="w-<?php echo $occupancyRate; ?>% bg-amber-600 h-2 rounded-full"
                    style="width: <?php echo $occupancyRate; ?>%;"></div>
                </div>
                <span class="text-sm"><?php echo $occupancyRate; ?>%</span>
              </div>
              <p class="text-xs text-slate-500 mt-1"><?php echo $occupiedRooms; ?>/<?php echo $totalRooms; ?> rooms
                occupied</p>
            </div>
            <!-- restaurant orders distribution -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200">
              <h3 class="font-semibold text-sm flex items-center gap-1"><i
                  class="fa-solid fa-chart-simple text-amber-600"></i> Orders by category</h3>
              <p class="text-xs mt-2">
                <?php
                $totalCat = array_sum(array_column($orderCategories, 'count')) ?: 100;
                foreach ($orderCategories as $cat):
                  $percentage = $totalCat > 0 ? round(($cat['count'] / $totalCat) * 100) : 0;
                  $icon = $cat['category'] == 'mains' ? '🍜' : ($cat['category'] == 'desserts' ? '🍰' : '🥤');
                  echo $icon . ' ' . ucfirst($cat['category']) . ' ' . $percentage . '% • ';
                endforeach;
                if (empty($orderCategories))
                  echo 'No orders today';
                ?>
              </p>
            </div>
          </div>
        </div>

        <!-- BOTTOM SECTION: tables (recent bookings & orders) -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <!-- recent bookings table -->
          <div class="bg-white p-5 rounded-2xl border border-slate-200">
            <div class="flex items-center justify-between mb-3">
              <h3 class="font-semibold"><i class="fa-regular fa-rectangle-list text-amber-600 mr-1"></i> Recent bookings
              </h3>
              <a href="../admin_portal/hotel_management/arrival/reservation_&_booking.php"
                class="text-xs text-amber-700 hover:underline">view all</a>
            </div>
            <table class="w-full text-sm">
              <thead class="text-slate-400 text-xs border-b">
                <tr>
                  <td class="pb-2">Guest</td>
                  <td>Room</td>
                  <td>Check-in</td>
                  <td>Status</td>
                </tr>
              </thead>
              <tbody class="divide-y">
                <?php if (empty($recentBookings)): ?>
                  <tr>
                    <td colspan="4" class="py-4 text-center text-slate-400">No recent bookings</td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($recentBookings as $booking): ?>
                    <tr>
                      <td class="py-2"><?php echo htmlspecialchars($booking['guest_name'] ?? 'Guest'); ?></td>
                      <td><?php echo htmlspecialchars($booking['room_name'] ?? 'N/A'); ?></td>
                      <td><?php echo date('M d', strtotime($booking['check_in'])); ?></td>
                      <td>
                        <span class="status-<?php echo $booking['status']; ?> px-2 py-0.5 rounded-full text-xs">
                          <?php echo $booking['status']; ?>
                        </span>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>

          <!-- recent orders table -->
          <div class="bg-white p-5 rounded-2xl border border-slate-200">
            <div class="flex items-center justify-between mb-3">
              <h3 class="font-semibold"><i class="fa-solid fa-bag-shopping text-amber-600 mr-1"></i> Recent orders</h3>
              <a href="../admin_portal/restaurant_management/orders_pos.php"
                class="text-xs text-amber-700 hover:underline">view all</a>
            </div>
            <table class="w-full text-sm">
              <thead class="text-slate-400 text-xs border-b">
                <tr>
                  <td class="pb-2">Table/guest</td>
                  <td>Order</td>
                  <td>Amount</td>
                  <td>Status</td>
                </tr>
              </thead>
              <tbody class="divide-y">
                <?php if (empty($recentOrders)): ?>
                  <tr>
                    <td colspan="4" class="py-4 text-center text-slate-400">No recent orders</td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($recentOrders as $order): ?>
                    <tr>
                      <td class="py-2"><?php echo htmlspecialchars($order['table_info'] ?? 'Takeaway'); ?></td>
                      <td><?php echo $order['item_count']; ?> items</td>
                      <td>₱<?php echo number_format($order['total_amount']); ?></td>
                      <td>
                        <span class="status-<?php echo $order['status']; ?> px-2 py-0.5 rounded-full text-xs">
                          <?php echo $order['status']; ?>
                        </span>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </main>
    </div>
  </body>

</html>