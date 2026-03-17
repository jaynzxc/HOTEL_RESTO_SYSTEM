<?php
/**
 * View - Admin Events & Conference
 */
require_once '../../../controller/admin/get/events_conference.php';

// Set current page for navigation
$current_page = 'events_conference';
?>

<!DOCTYPE html>
<html lang="en">

  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin · Events & Conference</title>
    <!-- Tailwind via CDN + Font Awesome 6 -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
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

      /* Modal animations */
      .modal-enter {
        animation: modalFadeIn 0.3s ease;
      }

      @keyframes modalFadeIn {
        from {
          opacity: 0;
          transform: scale(0.95);
        }

        to {
          opacity: 1;
          transform: scale(1);
        }
      }

      /* Calendar styles */
      .calendar-day {
        transition: all 0.2s;
      }

      .calendar-day:hover {
        background-color: #fef3c7;
        transform: scale(1.05);
      }

      .calendar-day.has-event {
        background-color: #fef3c7;
        border: 1px solid #f59e0b;
      }

      .toast {
        position: fixed;
        bottom: 20px;
        right: 20px;
        background: white;
        border-left: 4px solid #d97706;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        z-index: 1100;
        transform: translateX(400px);
        transition: transform 0.3s ease;
      }

      .toast.show {
        transform: translateX(0);
      }

      .pagination-btn {
        transition: all 0.2s;
      }

      .pagination-btn:hover:not(:disabled) {
        background-color: #fef3c7;
      }
    </style>
  </head>

  <body class="bg-white font-sans antialiased">

    <!-- Toast Notification -->
    <div id="toast" class="toast bg-white rounded-xl p-4 min-w-[300px] shadow-lg border border-amber-200 hidden">
      <div class="flex items-center gap-3">
        <div class="h-8 w-8 rounded-full bg-amber-100 flex items-center justify-center text-amber-600">
          <i class="fa-regular fa-bell"></i>
        </div>
        <div>
          <p id="toastMessage" class="text-sm font-medium text-slate-800">Notification</p>
          <p id="toastTime" class="text-xs text-slate-400">just now</p>
        </div>
      </div>
    </div>

    <!-- APP CONTAINER: flex row (sidebar + main) -->
    <div class="min-h-screen flex flex-col lg:flex-row">

      <!-- ========== SIDEBAR ========== -->
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

        <!-- ===== SIDEBAR MENU ===== -->
        <nav class="p-4 space-y-2 text-sm">

          <!-- Dashboard -->
          <a href="../dashboard.php"
            class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-700 hover:bg-amber-50 transition">
            <i class="fa-solid fa-table-cells-large w-5 text-slate-400"></i>
            <span>Dashboard</span>
          </a>

          <!-- HOTEL MANAGEMENT GROUP -->
          <details class="group" open>
            <summary
              class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-amber-800 bg-amber-50 cursor-pointer transition-side">
              <i class="fa-solid fa-hotel w-5 text-amber-600"></i>
              <span class="font-medium">HOTEL MANAGEMENT</span>
              <i class="fa-solid fa-chevron-right dropdown-arrow ml-auto text-xs text-amber-600"></i>
            </summary>
            <div class="ml-6 mt-1 space-y-1 pl-3 border-l-2 border-amber-200">
              <a href="../hotel_management/front_desk_reception.php"
                class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i
                  class="fa-regular fa-reception w-4 text-slate-400"></i> Front Desk / Reception</a>
              <a href="../hotel_management/room_management.php"
                class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i
                  class="fa-solid fa-bed w-4 text-slate-400"></i> Room Management</a>
              <a href="../hotel_management/reservation_&_booking.php"
                class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i
                  class="fa-regular fa-calendar-check w-4 text-slate-400"></i> Reservations & Booking</a>
              <a href="../hotel_management/housekeeping_&_maintenance.php"
                class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i
                  class="fa-solid fa-broom w-4 text-slate-400"></i> Housekeeping & Maintenance</a>
              <a href="../hotel_management/event_&_conference.php"
                class="flex items-center gap-2 px-4 py-2 rounded-lg bg-amber-100/50 text-amber-700 font-medium"><i
                  class="fa-regular fa-calendar w-4 text-amber-600"></i> Events & Conference</a>
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
              <a href="../restaurant_management/table_reservation.php"
                class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i
                  class="fa-regular fa-clock w-4"></i> Table Reservation</a>
              <a href="../restaurant_management/menu_management.php"
                class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i
                  class="fa-solid fa-bars w-4"></i> Menu Management</a>
              <a href="../restaurant_management/orders_pos.php"
                class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i
                  class="fa-solid fa-cash-register w-4"></i> Orders / POS</a>
              <a href="../restaurant_management/kitchen_orders.php"
                class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i
                  class="fa-solid fa-fire w-4"></i> Kitchen Orders (KOT)</a>
              <a href="../restaurant_management/wait_staff_management.php"
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
              <a href="../customer_management/customer_relationship.php"
                class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i
                  class="fa-regular fa-handshake w-4"></i> Guest Relationship (CRM)</a>
              <a href="../customer_management/loyalty_rewards.php"
                class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i
                  class="fa-regular fa-star w-4"></i> Loyalty & Rewards</a>
              <a href="../customer_management/customer_feedback_&_reviews.php"
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
              <a href="../operations/inventory_&_stocks.php"
                class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i
                  class="fa-solid fa-boxes w-4"></i> Inventory & Stock</a>
              <a href="../operations/billing_&_payment.php"
                class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i
                  class="fa-regular fa-credit-card w-4"></i> Billing & Payments</a>
              <a href="../operations/payment_gateway.php"
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
              <a href="../marketing/hotelmarketing_&_promotions.php"
                class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i
                  class="fa-regular fa-gem w-4"></i> Hotel Marketing & Promotions</a>
              <a href="../marketing/online_ordering_integration.php"
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
              <a href="../reports_&_analytics/sales_report.php"
                class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i
                  class="fa-solid fa-chart-line w-4"></i> Sales Reports</a>
              <a href="../reports_&_analytics/booking_reports.php"
                class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i
                  class="fa-regular fa-calendar w-4"></i> Booking Reports</a>
              <a href="../reports_&_analytics/analytics_dashboard.php"
                class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i
                  class="fa-solid fa-chart-pie w-4"></i> Analytics Dashboard</a>
            </div>
          </details>

          <!-- SYSTEM -->
          <details class="group" open>
            <summary
              class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-700 hover:bg-amber-50 cursor-pointer">
              <i class="fa-solid fa-computer w-5 text-slate-400 group-open:text-amber-600"></i>
              <span class="font-medium">SYSTEM</span>
              <i class="fa-solid fa-chevron-right dropdown-arrow ml-auto text-xs text-slate-400"></i>
            </summary>
            <div class="ml-6 mt-1 space-y-1 pl-3 border-l-2 border-amber-100">
              <a href="../system/channel_management.php"
                class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i
                  class="fa-solid fa-code-branch w-4"></i> Channel Management</a>
              <a href="../system/door_lock_integration.php"
                class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i
                  class="fa-solid fa-lock w-4"></i> Door Lock Integration</a>
              <a href="../system/settings.php"
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

      <!-- ========== MAIN CONTENT (EVENTS & CONFERENCE) ========== -->
      <main class="flex-1 p-5 lg:p-8 overflow-y-auto bg-white">
        <div id="notificationArea" class="hidden mb-4 p-3 rounded-lg text-sm"></div>

        <!-- header with title and date -->
        <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
          <div>
            <h1 class="text-2xl lg:text-3xl font-light text-slate-800">Events & Conference</h1>
            <p class="text-sm text-slate-500 mt-0.5">manage meetings, weddings, banquets, and all hotel events</p>
          </div>
          <div class="flex gap-3 text-sm">
            <span class="bg-white border rounded-full px-4 py-2 flex items-center gap-2 shadow-sm"><i
                class="fa-regular fa-calendar text-slate-400"></i> <span id="currentDate"></span></span>
            <span class="bg-white border rounded-full px-4 py-2 shadow-sm relative cursor-pointer"
              id="notificationBell">
              <i class="fa-regular fa-bell"></i>
              <?php if ($unread_count > 0): ?>
                <span
                  class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-4 h-4 flex items-center justify-center"><?php echo $unread_count; ?></span>
              <?php endif; ?>
            </span>
          </div>
        </div>

        <!-- ===== STATS CARDS ===== -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
          <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs text-slate-500">Today's events</p>
            <p class="text-2xl font-semibold" id="todaysEventsCount"><?php echo $stats['todays_count']; ?></p>
          </div>
          <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs text-slate-500">Upcoming (7 days)</p>
            <p class="text-2xl font-semibold" id="upcomingEventsCount"><?php echo $stats['upcoming_count']; ?></p>
          </div>
          <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs text-slate-500">Total venues</p>
            <p class="text-2xl font-semibold" id="totalVenuesCount"><?php echo $stats['total_venues']; ?></p>
          </div>
          <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs text-slate-500">Occupied venues</p>
            <p class="text-2xl font-semibold" id="occupiedVenuesCount"><?php echo $stats['occupied_venues']; ?></p>
          </div>
          <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs text-slate-500">Revenue this month</p>
            <p class="text-2xl font-semibold" id="monthlyRevenue">
              ₱<?php echo number_format($stats['monthly_revenue'] / 1000, 1); ?>k</p>
          </div>
        </div>

        <!-- ===== QUICK ACTIONS BAR ===== -->
        <div
          class="bg-white rounded-2xl border border-slate-200 p-4 mb-6 flex flex-wrap gap-3 items-center justify-between">
          <div class="flex gap-2 flex-wrap">
            <button class="bg-amber-600 text-white px-4 py-2 rounded-xl text-sm" id="newEventBtn">+ new event</button>
            <button class="border border-slate-200 px-4 py-2 rounded-xl text-sm hover:bg-slate-50"
              id="checkAvailabilityBtn">check availability</button>
            <button class="border border-slate-200 px-4 py-2 rounded-xl text-sm hover:bg-slate-50"
              id="venuesBtn">venues</button>
            <button class="border border-slate-200 px-4 py-2 rounded-xl text-sm hover:bg-slate-50"
              id="calendarViewBtn">calendar view</button>
          </div>
          <div class="relative">
            <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
            <input type="text" placeholder="search events..."
              class="border border-slate-200 rounded-xl pl-9 pr-4 py-2 text-sm w-64 focus:ring-1 focus:ring-amber-500 outline-none"
              id="searchEvents">
          </div>
        </div>

        <!-- ===== TODAY'S EVENTS HIGHLIGHT ===== -->
        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-5 mb-8">
          <h2 class="font-semibold text-lg flex items-center gap-2 mb-3"><i
              class="fa-regular fa-calendar-check text-amber-600"></i> today's events (<span
              id="todayDateDisplay"></span>)</h2>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4" id="todaysEventsContainer">
            <!-- Dynamically populated by JS -->
          </div>
        </div>

        <!-- ===== UPCOMING EVENTS TABLE ===== -->
        <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden mb-8">
          <div class="p-4 border-b border-slate-200 bg-slate-50 flex items-center justify-between">
            <h2 class="font-semibold flex items-center gap-2"><i class="fa-regular fa-calendar text-amber-600"></i>
              upcoming events (next 7 days)</h2>
            <button class="text-sm text-amber-700 border border-amber-600 px-3 py-1 rounded-lg hover:bg-amber-50"
              id="exportBtn">export</button>
          </div>
          <div class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead class="text-slate-500 text-xs border-b">
                <tr>
                  <td class="p-3">Event</td>
                  <td class="p-3">Type</td>
                  <td class="p-3">Venue</td>
                  <td class="p-3">Date & Time</td>
                  <td class="p-3">Guests</td>
                  <td class="p-3">Status</td>
                  <td class="p-3">Actions</td>
                </tr>
              </thead>
              <tbody class="divide-y" id="upcomingEventsTableBody">
                <!-- Dynamically populated by JS -->
              </tbody>
            </table>
          </div>
          <div class="p-4 border-t border-slate-200 flex items-center justify-between">
            <span class="text-xs text-slate-500" id="tablePaginationInfo">Showing 1-5 of 0 upcoming events</span>
            <div class="flex gap-2" id="paginationControls">
              <button class="border border-slate-200 px-3 py-1 rounded-lg text-sm pagination-btn" id="prevPage"
                disabled>Previous</button>
              <button class="bg-amber-600 text-white px-3 py-1 rounded-lg text-sm page-btn" data-page="1">1</button>
              <button class="border border-slate-200 px-3 py-1 rounded-lg text-sm page-btn" data-page="2">2</button>
              <button class="border border-slate-200 px-3 py-1 rounded-lg text-sm page-btn" data-page="3">3</button>
              <button class="border border-slate-200 px-3 py-1 rounded-lg text-sm pagination-btn" id="nextPage"
                disabled>Next</button>
            </div>
          </div>
        </div>

        <!-- ===== BOTTOM: VENUES & QUICK BOOK ===== -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <!-- venue availability -->
          <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 p-5">
            <h2 class="font-semibold text-lg flex items-center gap-2 mb-3"><i
                class="fa-regular fa-building text-amber-600"></i> venue availability (today)</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4" id="venueAvailabilityContainer">
              <!-- Dynamically populated by JS -->
            </div>
          </div>

          <!-- quick event booking -->
          <div class="bg-amber-50 border border-amber-200 rounded-2xl p-5">
            <h3 class="font-semibold flex items-center gap-2 mb-3"><i class="fa-regular fa-plus text-amber-600"></i>
              quick event booking</h3>
            <div class="space-y-3" id="quickBookingForm">
              <div>
                <label class="block text-xs text-slate-500 mb-1">event name</label>
                <input type="text" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm bg-white"
                  id="bookingEventName" placeholder="e.g., Team Building">
              </div>
              <div>
                <label class="block text-xs text-slate-500 mb-1">venue</label>
                <select class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm bg-white" id="bookingVenue">
                  <?php foreach ($venues as $venue): ?>
                    <option value="<?php echo $venue['id']; ?>"><?php echo htmlspecialchars($venue['name']); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div>
                <label class="block text-xs text-slate-500 mb-1">date</label>
                <input type="date" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm bg-white"
                  id="bookingDate" value="<?php echo $today; ?>">
              </div>
              <div>
                <label class="block text-xs text-slate-500 mb-1">guests</label>
                <input type="number" placeholder="estimated pax"
                  class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm bg-white" id="bookingGuests">
              </div>
              <button class="w-full bg-amber-600 text-white py-2 rounded-xl text-sm hover:bg-amber-700"
                id="checkQuickAvailability">check availability</button>
            </div>
          </div>
        </div>

        <!-- New Event Modal -->
        <div id="newEventModal" class="hidden fixed inset-0 bg-black bg-opacity-50 items-center justify-center z-50">
          <div class="bg-white rounded-2xl w-full max-w-2xl modal-enter shadow-xl max-h-[90vh] overflow-y-auto">
            <!-- Header -->
            <div class="flex items-center justify-between p-6 border-b border-slate-100 sticky top-0 bg-white">
              <h3 class="text-xl font-semibold text-slate-800">Create New Event</h3>
              <button class="text-slate-400 hover:text-slate-600 transition-colors" id="closeModalBtn">
                <i class="fa-solid fa-times text-xl"></i>
              </button>
            </div>

            <!-- Form -->
            <div class="p-6">
              <div class="space-y-4">
                <!-- Event Name -->
                <div>
                  <label class="block text-sm font-medium text-slate-700 mb-1.5">
                    Event Name <span class="text-amber-600">*</span>
                  </label>
                  <input type="text" placeholder="e.g., Garcia Wedding, Tech Conference 2025"
                    class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-amber-500 focus:border-transparent transition"
                    id="modalEventName">
                </div>

                <!-- Row: Event Type & Venue -->
                <div class="grid grid-cols-2 gap-3">
                  <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">
                      Type <span class="text-amber-600">*</span>
                    </label>
                    <select
                      class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-amber-500 focus:border-transparent bg-white"
                      id="modalEventType">
                      <option value="wedding">💒 Wedding</option>
                      <option value="meeting">👥 Meeting</option>
                      <option value="conference">🎤 Conference</option>
                      <option value="birthday">🎂 Birthday</option>
                      <option value="social">🥂 Social</option>
                      <option value="corporate">🏢 Corporate</option>
                      <option value="other">📌 Other</option>
                    </select>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">
                      Venue <span class="text-amber-600">*</span>
                    </label>
                    <select
                      class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-amber-500 focus:border-transparent bg-white"
                      id="modalEventVenue">
                      <option value="">Select a venue...</option>
                      <?php foreach ($venues as $venue): ?>
                        <option value="<?php echo $venue['id']; ?>" data-capacity="<?php echo $venue['capacity']; ?>">
                          <?php echo htmlspecialchars($venue['name']); ?> - Capacity: <?php echo $venue['capacity']; ?>
                          (₱<?php echo number_format($venue['price_per_hour']); ?>/hr)
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                </div>

                <!-- Date & Time -->
                <div class="grid grid-cols-2 gap-3">
                  <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">
                      Date <span class="text-amber-600">*</span>
                    </label>
                    <input type="date"
                      class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                      id="modalEventDate" min="<?php echo date('Y-m-d'); ?>">
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">
                      Time <span class="text-amber-600">*</span>
                    </label>
                    <input type="time"
                      class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                      id="modalEventTime">
                  </div>
                </div>

                <!-- Row: Guests & Status -->
                <div class="grid grid-cols-2 gap-3">
                  <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">
                      Guests <span class="text-amber-600">*</span>
                    </label>
                    <input type="number" placeholder="e.g., 150" min="1"
                      class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                      id="modalEventGuests">
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">
                      Status
                    </label>
                    <select
                      class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-amber-500 focus:border-transparent bg-white"
                      id="modalEventStatus">
                      <option value="confirmed" class="text-emerald-600">✓ Confirmed</option>
                      <option value="pending" class="text-amber-600">⏳ Pending</option>
                      <option value="tentative" class="text-blue-600">❓ Tentative</option>
                      <option value="cancelled" class="text-rose-600">✗ Cancelled</option>
                    </select>
                  </div>
                </div>

                <!-- Contact Information -->
                <div class="grid grid-cols-2 gap-3">
                  <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">
                      Contact Person
                    </label>
                    <input type="text" placeholder="e.g., Maria Santos"
                      class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                      id="modalEventContact">
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">
                      Contact Phone
                    </label>
                    <input type="text" placeholder="e.g., 0917 123 4567"
                      class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                      id="modalEventPhone">
                  </div>
                </div>

                <!-- Special Requirements -->
                <div>
                  <label class="block text-sm font-medium text-slate-700 mb-1.5">
                    Special Requirements <span class="text-xs text-slate-400 font-normal">(optional)</span>
                  </label>
                  <textarea rows="3"
                    placeholder="e.g., Vegan options, AV setup, stage decoration, parking requirements..."
                    class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-amber-500 focus:border-transparent resize-none"
                    id="modalEventRequests"></textarea>
                </div>

                <!-- Form Actions -->
                <div class="flex gap-3 pt-4 border-t">
                  <button
                    class="flex-1 bg-amber-600 hover:bg-amber-700 text-white font-medium px-4 py-2.5 rounded-xl transition-colors flex items-center justify-center gap-2"
                    id="saveEventBtn">
                    <i class="fa-regular fa-floppy-disk"></i>
                    Save Event
                  </button>
                  <button
                    class="flex-1 border border-slate-200 hover:bg-slate-50 text-slate-700 font-medium px-4 py-2.5 rounded-xl transition-colors flex items-center justify-center gap-2"
                    id="cancelModalBtn">
                    <i class="fa-regular fa-times"></i>
                    Cancel
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Edit Event Modal -->
        <div id="editEventModal" class="hidden fixed inset-0 bg-black bg-opacity-50 items-center justify-center z-50">
          <div class="bg-white rounded-2xl w-full max-w-2xl modal-enter shadow-xl max-h-[90vh] overflow-y-auto">
            <!-- Header -->
            <div class="flex items-center justify-between p-6 border-b border-slate-100 sticky top-0 bg-white">
              <h3 class="text-xl font-semibold text-slate-800">Edit Event</h3>
              <button class="text-slate-400 hover:text-slate-600 transition-colors" id="closeEditModalBtn">
                <i class="fa-solid fa-times text-xl"></i>
              </button>
            </div>

            <!-- Form -->
            <div class="p-6">
              <div class="space-y-4">
                <input type="hidden" id="editEventId">

                <!-- Event Name -->
                <div>
                  <label class="block text-sm font-medium text-slate-700 mb-1.5">
                    Event Name <span class="text-amber-600">*</span>
                  </label>
                  <input type="text" placeholder="e.g., Garcia Wedding, Tech Conference 2025"
                    class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-amber-500 focus:border-transparent transition"
                    id="editEventName">
                </div>

                <!-- Row: Event Type & Venue -->
                <div class="grid grid-cols-2 gap-3">
                  <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">
                      Type <span class="text-amber-600">*</span>
                    </label>
                    <select
                      class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-amber-500 focus:border-transparent bg-white"
                      id="editEventType">
                      <option value="wedding">💒 Wedding</option>
                      <option value="meeting">👥 Meeting</option>
                      <option value="conference">🎤 Conference</option>
                      <option value="birthday">🎂 Birthday</option>
                      <option value="social">🥂 Social</option>
                      <option value="corporate">🏢 Corporate</option>
                      <option value="other">📌 Other</option>
                    </select>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">
                      Venue <span class="text-amber-600">*</span>
                    </label>
                    <select
                      class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-amber-500 focus:border-transparent bg-white"
                      id="editEventVenue">
                      <option value="">Select a venue...</option>
                      <?php foreach ($venues as $venue): ?>
                        <option value="<?php echo $venue['id']; ?>" data-capacity="<?php echo $venue['capacity']; ?>">
                          <?php echo htmlspecialchars($venue['name']); ?> - Capacity: <?php echo $venue['capacity']; ?>
                          (₱<?php echo number_format($venue['price_per_hour']); ?>/hr)
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                </div>

                <!-- Date & Time -->
                <div class="grid grid-cols-2 gap-3">
                  <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">
                      Date <span class="text-amber-600">*</span>
                    </label>
                    <input type="date"
                      class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                      id="editEventDate" min="<?php echo date('Y-m-d'); ?>">
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">
                      Time <span class="text-amber-600">*</span>
                    </label>
                    <input type="time"
                      class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                      id="editEventTime">
                  </div>
                </div>

                <!-- Row: Guests & Status -->
                <div class="grid grid-cols-2 gap-3">
                  <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">
                      Guests <span class="text-amber-600">*</span>
                    </label>
                    <input type="number" placeholder="e.g., 150" min="1"
                      class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                      id="editEventGuests">
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">
                      Status
                    </label>
                    <select
                      class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-amber-500 focus:border-transparent bg-white"
                      id="editEventStatus">
                      <option value="confirmed" class="text-emerald-600">✓ Confirmed</option>
                      <option value="pending" class="text-amber-600">⏳ Pending</option>
                      <option value="tentative" class="text-blue-600">❓ Tentative</option>
                      <option value="cancelled" class="text-rose-600">✗ Cancelled</option>
                    </select>
                  </div>
                </div>

                <!-- Contact Information -->
                <div class="grid grid-cols-2 gap-3">
                  <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">
                      Contact Person
                    </label>
                    <input type="text" placeholder="e.g., Maria Santos"
                      class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                      id="editEventContact">
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">
                      Contact Phone
                    </label>
                    <input type="text" placeholder="e.g., 0917 123 4567"
                      class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                      id="editEventPhone">
                  </div>
                </div>

                <!-- Special Requirements -->
                <div>
                  <label class="block text-sm font-medium text-slate-700 mb-1.5">
                    Special Requirements <span class="text-xs text-slate-400 font-normal">(optional)</span>
                  </label>
                  <textarea rows="3"
                    placeholder="e.g., Vegan options, AV setup, stage decoration, parking requirements..."
                    class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-amber-500 focus:border-transparent resize-none"
                    id="editEventRequests"></textarea>
                </div>

                <!-- Form Actions -->
                <div class="flex gap-3 pt-4 border-t">
                  <button
                    class="flex-1 bg-amber-600 hover:bg-amber-700 text-white font-medium px-4 py-2.5 rounded-xl transition-colors flex items-center justify-center gap-2"
                    id="updateEventBtn">
                    <i class="fa-regular fa-pen-to-square"></i>
                    Update Event
                  </button>
                  <button
                    class="flex-1 border border-slate-200 hover:bg-slate-50 text-slate-700 font-medium px-4 py-2.5 rounded-xl transition-colors flex items-center justify-center gap-2"
                    id="cancelEditModalBtn">
                    <i class="fa-regular fa-times"></i>
                    Cancel
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Availability Check Modal -->
        <div id="availabilityModal"
          class="hidden fixed inset-0 bg-black bg-opacity-50 items-center justify-center z-50">
          <div class="bg-white rounded-2xl p-6 w-full max-w-3xl modal-enter max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center mb-4 sticky top-0 bg-white">
              <h3 class="text-xl font-semibold">Venue Availability</h3>
              <button class="text-slate-400 hover:text-slate-600" id="closeAvailabilityModal">&times;</button>
            </div>
            <div class="mb-4">
              <label class="block text-sm text-slate-500 mb-2">Select Date</label>
              <input type="date" class="border rounded-xl px-3 py-2 w-full" id="availabilityDate"
                value="<?php echo $today; ?>">
            </div>
            <div id="availabilityResults" class="space-y-3">
              <!-- Results will be populated here -->
            </div>
          </div>
        </div>

        <!-- Venues Management Modal -->
        <div id="venuesModal" class="hidden fixed inset-0 bg-black bg-opacity-50 items-center justify-center z-50">
          <div class="bg-white rounded-2xl p-6 w-full max-w-4xl modal-enter max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center mb-4 sticky top-0 bg-white">
              <h3 class="text-xl font-semibold">Venues Management</h3>
              <button class="text-slate-400 hover:text-slate-600" id="closeVenuesModal">&times;</button>
            </div>

            <!-- Venues List -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6" id="venuesManagementContainer">
              <!-- Populated by JavaScript -->
            </div>

            <!-- Add New Venue Form -->
            <div class="border-t pt-4">
              <h4 class="font-semibold mb-3">Add New Venue</h4>
              <div class="grid grid-cols-2 gap-3">
                <input type="text" placeholder="Venue Name" class="border rounded-xl px-3 py-2" id="newVenueName">
                <input type="number" placeholder="Capacity" class="border rounded-xl px-3 py-2" id="newVenueCapacity">
                <input type="text" placeholder="Location" class="border rounded-xl px-3 py-2" id="newVenueLocation">
                <input type="number" placeholder="Price per hour (₱)" class="border rounded-xl px-3 py-2"
                  id="newVenuePrice">
                <textarea placeholder="Description" class="border rounded-xl px-3 py-2 col-span-2"
                  id="newVenueDescription" rows="2"></textarea>
                <textarea placeholder="Amenities (comma separated)" class="border rounded-xl px-3 py-2 col-span-2"
                  id="newVenueAmenities" rows="2"></textarea>
                <button class="bg-amber-600 text-white px-4 py-2 rounded-xl col-span-2" id="addVenueBtn">Add
                  Venue</button>
              </div>
            </div>
          </div>
        </div>

        <!-- Calendar View Modal -->
        <div id="calendarModal" class="hidden fixed inset-0 bg-black bg-opacity-50 items-center justify-center z-50">
          <div class="bg-white rounded-2xl p-6 w-full max-w-4xl modal-enter max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center mb-4 sticky top-0 bg-white">
              <h3 class="text-xl font-semibold">Event Calendar - <?php echo date('F Y'); ?></h3>
              <button class="text-slate-400 hover:text-slate-600" id="closeCalendarModal">&times;</button>
            </div>
            <div class="grid grid-cols-7 gap-1 text-center mb-2">
              <div class="font-semibold text-sm">Sun</div>
              <div class="font-semibold text-sm">Mon</div>
              <div class="font-semibold text-sm">Tue</div>
              <div class="font-semibold text-sm">Wed</div>
              <div class="font-semibold text-sm">Thu</div>
              <div class="font-semibold text-sm">Fri</div>
              <div class="font-semibold text-sm">Sat</div>
            </div>
            <div id="calendarGrid" class="grid grid-cols-7 gap-1">
              <!-- Calendar will be populated here -->
            </div>
            <div class="mt-4" id="calendarEventDetails">
              <!-- Selected date events will be shown here -->
            </div>
          </div>
        </div>
      </main>
    </div>

    <script>
      // ==================== DATA STORE ====================
      let events = <?php echo json_encode($events); ?>;
      let todaysEvents = <?php echo json_encode($todaysEvents); ?>;
      let upcomingEvents = <?php echo json_encode($upcomingEvents); ?>;
      let venues = <?php echo json_encode($venues); ?>;
      let notifications = [];
      let currentPage = 1;
      const itemsPerPage = 5;
      let filteredEvents = [...upcomingEvents];

      // ==================== UTILITY FUNCTIONS ====================
      function formatDate(dateStr) {
        if (!dateStr) return 'N/A';
        const options = { year: 'numeric', month: 'long', day: 'numeric' };
        return new Date(dateStr).toLocaleDateString('en-US', options);
      }

      function formatTime(timeStr) {
        if (!timeStr) return 'N/A';
        return new Date('1970-01-01T' + timeStr).toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
      }

      function showToast(message, type = 'info') {
        const toast = document.getElementById('toast');
        const toastMessage = document.getElementById('toastMessage');
        const toastTime = document.getElementById('toastTime');
        const now = new Date();

        toastMessage.textContent = message;
        toastTime.textContent = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
        toast.classList.remove('hidden');

        setTimeout(() => { toast.classList.add('show'); }, 10);
        setTimeout(() => {
          toast.classList.remove('show');
          setTimeout(() => { toast.classList.add('hidden'); }, 300);
        }, 3000);
      }

      function showNotification(message, type = 'success') {
        showToast(message, type);
      }

      function addNotification(msg) {
        notifications.push({ id: Date.now(), message: msg, read: false });
        updateNotificationBadge();
      }

      function updateNotificationBadge() {
        const badge = document.getElementById('notificationBadge');
        const unread = <?php echo $unread_count; ?> + notifications.filter(n => !n.read).length;
        if (unread > 0) {
          badge.classList.remove('hidden');
          badge.textContent = unread;
        } else {
          badge.classList.add('hidden');
        }
      }

      function updateStats() {
        document.getElementById('todaysEventsCount').textContent = '<?php echo $stats['todays_count']; ?>';
        document.getElementById('upcomingEventsCount').textContent = '<?php echo $stats['upcoming_count']; ?>';
        document.getElementById('totalVenuesCount').textContent = '<?php echo $stats['total_venues']; ?>';
        document.getElementById('occupiedVenuesCount').textContent = '<?php echo $stats['occupied_venues']; ?>';
      }

      // ==================== RENDER FUNCTIONS ====================
      function renderTodaysEvents() {
        const container = document.getElementById('todaysEventsContainer');

        if (todaysEvents.length === 0) {
          container.innerHTML = '<div class="col-span-3 text-center text-slate-500 py-4">No events scheduled for today</div>';
          return;
        }

        container.innerHTML = todaysEvents.map(event => {
          const typeColors = {
            'wedding': 'bg-purple-100 text-purple-700',
            'meeting': 'bg-blue-100 text-blue-700',
            'conference': 'bg-indigo-100 text-indigo-700',
            'birthday': 'bg-pink-100 text-pink-700',
            'social': 'bg-orange-100 text-orange-700',
            'corporate': 'bg-gray-100 text-gray-700',
            'other': 'bg-slate-100 text-slate-700'
          };

          const statusColors = {
            'confirmed': 'bg-green-100 text-green-700',
            'pending': 'bg-yellow-100 text-yellow-700',
            'tentative': 'bg-blue-100 text-blue-700',
            'cancelled': 'bg-red-100 text-red-700'
          };

          return `
            <div class="bg-white rounded-xl p-4 border border-amber-100 hover:shadow-md transition">
              <div class="flex items-center gap-2 mb-2">
                <span class="${typeColors[event.event_type] || 'bg-gray-100 text-gray-700'} text-xs px-2 py-1 rounded-full">${event.event_type || 'event'}</span>
                <span class="text-xs text-slate-400">${formatTime(event.event_time)}</span>
              </div>
              <p class="font-semibold">${event.event_name}</p>
              <p class="text-xs text-slate-500">${event.venue_name || 'TBD'} · ${event.guests || 0} guests</p>
              <div class="flex justify-between items-center mt-3">
                <span class="${statusColors[event.status] || 'bg-green-100 text-green-700'} text-xs px-2 py-0.5 rounded-full">${event.status || 'confirmed'}</span>
                <button class="text-amber-700 text-xs hover:underline edit-event-btn" data-id="${event.id}">manage</button>
              </div>
            </div>
          `;
        }).join('');
      }

      function renderUpcomingTable() {
        const today = '<?php echo $today; ?>';

        // Filter events that are in the next 7 days
        filteredEvents = upcomingEvents.filter(e => {
          const eventDate = new Date(e.event_date);
          const todayDate = new Date(today);
          const diffTime = eventDate - todayDate;
          const diffDays = diffTime / (1000 * 60 * 60 * 24);
          return diffDays > 0 && diffDays <= 7;
        }).sort((a, b) => new Date(a.event_date) - new Date(b.event_date));

        const totalPages = Math.ceil(filteredEvents.length / itemsPerPage) || 1;
        const start = (currentPage - 1) * itemsPerPage;
        const end = start + itemsPerPage;
        const paginatedEvents = filteredEvents.slice(start, end);

        const tbody = document.getElementById('upcomingEventsTableBody');

        if (paginatedEvents.length === 0) {
          tbody.innerHTML = '<tr><td colspan="7" class="text-center p-4 text-slate-500">No upcoming events</td></tr>';
          document.getElementById('tablePaginationInfo').textContent = 'Showing 0 events';
        } else {
          tbody.innerHTML = paginatedEvents.map(event => {
            const typeColors = {
              'wedding': 'bg-purple-100 text-purple-700',
              'meeting': 'bg-blue-100 text-blue-700',
              'conference': 'bg-indigo-100 text-indigo-700',
              'birthday': 'bg-pink-100 text-pink-700',
              'social': 'bg-orange-100 text-orange-700',
              'corporate': 'bg-gray-100 text-gray-700',
              'other': 'bg-slate-100 text-slate-700'
            };

            const statusColors = {
              'confirmed': 'bg-green-100 text-green-700',
              'pending': 'bg-yellow-100 text-yellow-700',
              'tentative': 'bg-blue-100 text-blue-700',
              'cancelled': 'bg-red-100 text-red-700'
            };

            return `
              <tr class="hover:bg-slate-50 transition">
                <td class="p-3 font-medium">${event.event_name}</td>
                <td class="p-3"><span class="${typeColors[event.event_type] || 'bg-gray-100 text-gray-700'} px-2 py-0.5 rounded-full text-xs">${event.event_type || 'event'}</span></td>
                <td class="p-3">${event.venue_name || 'TBD'}</td>
                <td class="p-3">${formatDate(event.event_date)}, ${formatTime(event.event_time)}</td>
                <td class="p-3">${event.guests || 0}</td>
                <td class="p-3"><span class="${statusColors[event.status] || 'bg-green-100 text-green-700'} px-2 py-0.5 rounded-full text-xs">${event.status || 'confirmed'}</span></td>
                <td class="p-3">
                  <button class="text-amber-600 hover:text-amber-700 text-xs mr-2 edit-event-btn" data-id="${event.id}" title="Edit">
                    <i class="fa-regular fa-pen-to-square"></i>
                  </button>
                  <button class="text-red-600 hover:text-red-700 text-xs delete-event-btn" data-id="${event.id}" title="Delete">
                    <i class="fa-regular fa-trash-can"></i>
                  </button>
                </td>
              </tr>
            `;
          }).join('');
        }

        document.getElementById('tablePaginationInfo').textContent =
          `Showing ${Math.min(start + 1, filteredEvents.length)}-${Math.min(end, filteredEvents.length)} of ${filteredEvents.length} upcoming events`;

        // Update pagination buttons
        const prevBtn = document.getElementById('prevPage');
        const nextBtn = document.getElementById('nextPage');

        prevBtn.disabled = currentPage === 1;
        nextBtn.disabled = currentPage === totalPages || filteredEvents.length === 0;

        prevBtn.classList.toggle('opacity-50', currentPage === 1);
        prevBtn.classList.toggle('cursor-not-allowed', currentPage === 1);
        nextBtn.classList.toggle('opacity-50', currentPage === totalPages || filteredEvents.length === 0);
        nextBtn.classList.toggle('cursor-not-allowed', currentPage === totalPages || filteredEvents.length === 0);

        // Update page buttons
        document.querySelectorAll('.page-btn').forEach((btn, index) => {
          const pageNum = index + 1;
          if (pageNum <= totalPages) {
            btn.style.display = 'inline-block';
            btn.classList.toggle('bg-amber-600', pageNum === currentPage);
            btn.classList.toggle('text-white', pageNum === currentPage);
            btn.classList.toggle('border', pageNum !== currentPage);
            btn.classList.toggle('border-slate-200', pageNum !== currentPage);
          } else {
            btn.style.display = 'none';
          }
        });
      }

      function renderVenues() {
        const container = document.getElementById('venueAvailabilityContainer');
        container.innerHTML = venues.map(venue => {
          const statusColors = {
            'available': 'bg-green-100 text-green-700',
            'occupied': 'bg-amber-100 text-amber-700',
            'setup': 'bg-blue-100 text-blue-700',
            'maintenance': 'bg-red-100 text-red-700'
          };
          return `
            <div class="border rounded-xl p-4 hover:shadow-md transition">
              <div class="flex justify-between items-center">
                <span class="font-medium">${venue.name}</span>
                <span class="${statusColors[venue.status] || 'bg-gray-100 text-gray-700'} text-xs px-2 py-0.5 rounded-full">${venue.status}</span>
              </div>
              <p class="text-xs text-slate-500 mt-1">Capacity: ${venue.capacity} · ${venue.location || 'Main Building'}</p>
              <p class="text-xs text-amber-600 mt-1">₱${parseFloat(venue.price_per_hour).toLocaleString()}/hour</p>
            </div>
          `;
        }).join('');
      }

      // ==================== EVENT HANDLERS ====================
      document.getElementById('newEventBtn').addEventListener('click', () => {
        document.getElementById('newEventModal').classList.remove('hidden');
        // Set default date to today
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('modalEventDate').value = today;
        document.getElementById('modalEventTime').value = '14:00';
      });

      document.getElementById('closeModalBtn').addEventListener('click', () => {
        document.getElementById('newEventModal').classList.add('hidden');
        resetNewEventForm();
      });

      document.getElementById('cancelModalBtn').addEventListener('click', () => {
        document.getElementById('newEventModal').classList.add('hidden');
        resetNewEventForm();
      });

      function resetNewEventForm() {
        document.getElementById('modalEventName').value = '';
        document.getElementById('modalEventDate').value = '';
        document.getElementById('modalEventTime').value = '';
        document.getElementById('modalEventGuests').value = '';
        document.getElementById('modalEventContact').value = '';
        document.getElementById('modalEventPhone').value = '';
        document.getElementById('modalEventRequests').value = '';
      }

      document.getElementById('saveEventBtn').addEventListener('click', () => {
        const name = document.getElementById('modalEventName').value.trim();
        const type = document.getElementById('modalEventType').value;
        const venue_id = document.getElementById('modalEventVenue').value;
        const date = document.getElementById('modalEventDate').value;
        const time = document.getElementById('modalEventTime').value;
        const guests = document.getElementById('modalEventGuests').value;
        const status = document.getElementById('modalEventStatus').value;
        const contact = document.getElementById('modalEventContact')?.value || '';
        const phone = document.getElementById('modalEventPhone')?.value || '';
        const requirements = document.getElementById('modalEventRequests')?.value || '';

        // Validation
        if (!name) {
          showNotification('Please enter event name', 'error');
          return;
        }
        if (!venue_id) {
          showNotification('Please select a venue', 'error');
          return;
        }
        if (!date) {
          showNotification('Please select date', 'error');
          return;
        }
        if (!time) {
          showNotification('Please select time', 'error');
          return;
        }
        if (!guests || guests < 1) {
          showNotification('Please enter valid number of guests', 'error');
          return;
        }

        Swal.fire({
          title: 'Creating Event...',
          text: 'Please wait',
          allowOutsideClick: false,
          didOpen: () => {
            Swal.showLoading();
          }
        });

        const formData = new FormData();
        formData.append('action', 'create_event');
        formData.append('event_name', name);
        formData.append('event_type', type);
        formData.append('venue_id', venue_id);
        formData.append('event_date', date);
        formData.append('event_time', time);
        formData.append('guests', guests);
        formData.append('status', status);
        formData.append('contact_person', contact);
        formData.append('contact_phone', phone);
        formData.append('requirements', requirements);

        fetch('../../../controller/admin/post/events_actions.php', {
          method: 'POST',
          body: formData
        })
          .then(response => response.json())
          .then(data => {
            Swal.close();
            if (data.success) {
              Swal.fire({
                title: 'Success!',
                text: data.message,
                icon: 'success',
                confirmButtonColor: '#d97706'
              }).then(() => {
                location.reload();
              });
            } else {
              Swal.fire({
                title: 'Error',
                text: data.message,
                icon: 'error',
                confirmButtonColor: '#d97706'
              });
            }
          })
          .catch(error => {
            Swal.close();
            showNotification('An error occurred', 'error');
          });
      });

      // Edit Event Functions
      function openEditModal(eventId) {
        const event = events.find(e => e.id == eventId);
        if (!event) return;

        document.getElementById('editEventId').value = event.id;
        document.getElementById('editEventName').value = event.event_name || '';
        document.getElementById('editEventType').value = event.event_type || 'other';
        document.getElementById('editEventVenue').value = event.venue_id || '';
        document.getElementById('editEventDate').value = event.event_date || '';
        document.getElementById('editEventTime').value = event.event_time || '';
        document.getElementById('editEventGuests').value = event.guests || 0;
        document.getElementById('editEventStatus').value = event.status || 'confirmed';
        document.getElementById('editEventContact').value = event.contact_person || '';
        document.getElementById('editEventPhone').value = event.contact_phone || '';
        document.getElementById('editEventRequests').value = event.special_requirements || '';

        document.getElementById('editEventModal').classList.remove('hidden');
      }

      document.getElementById('closeEditModalBtn').addEventListener('click', () => {
        document.getElementById('editEventModal').classList.add('hidden');
      });

      document.getElementById('cancelEditModalBtn').addEventListener('click', () => {
        document.getElementById('editEventModal').classList.add('hidden');
      });

      document.getElementById('updateEventBtn').addEventListener('click', () => {
        const eventId = document.getElementById('editEventId').value;
        const name = document.getElementById('editEventName').value.trim();
        const type = document.getElementById('editEventType').value;
        const venue_id = document.getElementById('editEventVenue').value;
        const date = document.getElementById('editEventDate').value;
        const time = document.getElementById('editEventTime').value;
        const guests = document.getElementById('editEventGuests').value;
        const status = document.getElementById('editEventStatus').value;
        const contact = document.getElementById('editEventContact').value.trim();
        const phone = document.getElementById('editEventPhone').value.trim();
        const requirements = document.getElementById('editEventRequests').value.trim();

        if (!name || !venue_id || !date || !time || !guests) {
          showNotification('Please fill in all required fields', 'error');
          return;
        }

        Swal.fire({
          title: 'Updating Event...',
          text: 'Please wait',
          allowOutsideClick: false,
          didOpen: () => {
            Swal.showLoading();
          }
        });

        const formData = new FormData();
        formData.append('action', 'update_event');
        formData.append('event_id', eventId);
        formData.append('event_name', name);
        formData.append('event_type', type);
        formData.append('venue_id', venue_id);
        formData.append('event_date', date);
        formData.append('event_time', time);
        formData.append('guests', guests);
        formData.append('status', status);
        formData.append('contact_person', contact);
        formData.append('contact_phone', phone);
        formData.append('requirements', requirements);

        fetch('../../../controller/admin/post/events_actions.php', {
          method: 'POST',
          body: formData
        })
          .then(response => response.json())
          .then(data => {
            Swal.close();
            if (data.success) {
              Swal.fire({
                title: 'Success!',
                text: data.message,
                icon: 'success',
                confirmButtonColor: '#d97706'
              }).then(() => {
                location.reload();
              });
            } else {
              Swal.fire({
                title: 'Error',
                text: data.message,
                icon: 'error',
                confirmButtonColor: '#d97706'
              });
            }
          });
      });

      // Delete Event Function
      function deleteEvent(eventId) {
        Swal.fire({
          title: 'Delete Event?',
          text: 'Are you sure you want to delete this event? This action cannot be undone.',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#d97706',
          cancelButtonColor: '#6b7280',
          confirmButtonText: 'Yes, delete'
        }).then((result) => {
          if (result.isConfirmed) {
            Swal.fire({
              title: 'Deleting...',
              text: 'Please wait',
              allowOutsideClick: false,
              didOpen: () => {
                Swal.showLoading();
              }
            });

            const formData = new FormData();
            formData.append('action', 'delete_event');
            formData.append('event_id', eventId);

            fetch('../../../controller/admin/post/events_actions.php', {
              method: 'POST',
              body: formData
            })
              .then(response => response.json())
              .then(data => {
                Swal.close();
                if (data.success) {
                  Swal.fire({
                    title: 'Deleted!',
                    text: data.message,
                    icon: 'success',
                    confirmButtonColor: '#d97706'
                  }).then(() => {
                    location.reload();
                  });
                } else {
                  Swal.fire({
                    title: 'Error',
                    text: data.message,
                    icon: 'error',
                    confirmButtonColor: '#d97706'
                  });
                }
              });
          }
        });
      }

      // Check Availability Button
      document.getElementById('checkAvailabilityBtn').addEventListener('click', () => {
        const modal = document.getElementById('availabilityModal');
        modal.classList.remove('hidden');
        checkAvailability('<?php echo $today; ?>');
      });

      function checkAvailability(date) {
        const container = document.getElementById('availabilityResults');

        fetch('../../../controller/admin/post/events_actions.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: 'action=check_availability&date=' + date
        })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              if (data.venues.length === 0) {
                container.innerHTML = '<p class="text-center text-slate-500 py-4">No venues found</p>';
              } else {
                container.innerHTML = data.venues.map(venue => {
                  const isAvailable = venue.booking_status === 'available';

                  return `
                    <div class="border rounded-xl p-4 hover:shadow-md transition">
                      <div class="flex justify-between items-center">
                        <div>
                          <span class="font-medium text-lg">${venue.name}</span>
                          <span class="text-xs text-slate-500 ml-2">capacity: ${venue.capacity}</span>
                        </div>
                        <span class="${isAvailable ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'} text-xs px-2 py-1 rounded-full">
                          ${isAvailable ? 'Available' : 'Booked'}
                        </span>
                      </div>
                      <p class="text-xs text-slate-500 mt-1">${venue.location || 'Main Building'}</p>
                      <p class="text-xs text-amber-600 mt-1">₱${parseFloat(venue.price_per_hour).toLocaleString()}/hour</p>
                      ${!isAvailable ? `
                        <div class="mt-2 text-sm bg-amber-50 p-2 rounded">
                          <p class="font-medium">Booked for:</p>
                          <p class="text-xs">${venue.event_name} at ${formatTime(venue.event_time)}</p>
                        </div>
                      ` : ''}
                    </div>
                  `;
                }).join('');
              }
            }
          });
      }

      document.getElementById('closeAvailabilityModal').addEventListener('click', () => {
        document.getElementById('availabilityModal').classList.add('hidden');
      });

      document.getElementById('availabilityDate').addEventListener('change', (e) => {
        checkAvailability(e.target.value);
      });

      // Venues Button
      document.getElementById('venuesBtn').addEventListener('click', () => {
        const modal = document.getElementById('venuesModal');
        modal.classList.remove('hidden');
        renderVenuesManagement();
      });

      function renderVenuesManagement() {
        const container = document.getElementById('venuesManagementContainer');

        fetch('../../../controller/admin/post/events_actions.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: 'action=get_venues'
        })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              if (data.venues.length === 0) {
                container.innerHTML = '<p class="text-center text-slate-500 py-4">No venues found</p>';
              } else {
                container.innerHTML = data.venues.map(venue => {
                  const statusColors = {
                    'available': 'bg-green-100 text-green-700',
                    'occupied': 'bg-amber-100 text-amber-700',
                    'setup': 'bg-blue-100 text-blue-700',
                    'maintenance': 'bg-red-100 text-red-700'
                  };

                  const amenities = venue.amenities ? venue.amenities.split(',').map(a => a.trim()) : [];

                  return `
                  <div class="border rounded-xl p-4 hover:shadow-md transition">
                    <div class="flex justify-between items-start">
                      <div>
                        <h4 class="font-semibold text-lg">${venue.name}</h4>
                        <p class="text-xs text-slate-500 mt-1">${venue.location || 'Main Building'}</p>
                      </div>
                      <span class="${statusColors[venue.status] || 'bg-gray-100 text-gray-700'} text-xs px-2 py-1 rounded-full">
                        ${venue.status}
                      </span>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-2 mt-3 text-sm">
                      <div>
                        <span class="text-xs text-slate-500">Capacity</span>
                        <p class="font-medium">${venue.capacity} pax</p>
                      </div>
                      <div>
                        <span class="text-xs text-slate-500">Price/hour</span>
                        <p class="font-medium text-amber-600">₱${parseFloat(venue.price_per_hour).toLocaleString()}</p>
                      </div>
                    </div>
                    
                    ${amenities.length > 0 ? `
                      <div class="mt-2">
                        <span class="text-xs text-slate-500">Amenities</span>
                        <div class="flex flex-wrap gap-1 mt-1">
                          ${amenities.map(a => `<span class="text-xs bg-slate-100 px-2 py-0.5 rounded-full">${a}</span>`).join('')}
                        </div>
                      </div>
                    ` : ''}
                    
                    ${venue.description ? `
                      <p class="text-xs text-slate-600 mt-2">${venue.description}</p>
                    ` : ''}
                    
                    <div class="mt-3 flex gap-2 border-t pt-2">
                      <select class="text-xs border rounded-lg px-2 py-1 flex-1" onchange="updateVenueStatus(${venue.id}, this.value)">
                        <option value="available" ${venue.status === 'available' ? 'selected' : ''}>Available</option>
                        <option value="occupied" ${venue.status === 'occupied' ? 'selected' : ''}>Occupied</option>
                        <option value="setup" ${venue.status === 'setup' ? 'selected' : ''}>Setup</option>
                        <option value="maintenance" ${venue.status === 'maintenance' ? 'selected' : ''}>Maintenance</option>
                      </select>
                      <button class="text-xs text-red-600 hover:underline px-2" onclick="deleteVenue(${venue.id})">
                        <i class="fa-regular fa-trash-can"></i>
                      </button>
                    </div>
                  </div>
                `;
                }).join('');
              }
            }
          });
      }

      window.updateVenueStatus = function (venueId, status) {
        const formData = new FormData();
        formData.append('action', 'update_venue_status');
        formData.append('venue_id', venueId);
        formData.append('status', status);

        fetch('../../../controller/admin/post/events_actions.php', {
          method: 'POST',
          body: formData
        })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              showNotification('Venue status updated', 'success');
              renderVenuesManagement();
              renderVenues();
            }
          });
      };

      window.deleteVenue = function (venueId) {
        Swal.fire({
          title: 'Delete Venue?',
          text: 'Are you sure you want to delete this venue?',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#d97706',
          cancelButtonColor: '#6b7280',
          confirmButtonText: 'Yes, delete'
        }).then((result) => {
          if (result.isConfirmed) {
            Swal.fire({
              title: 'Deleting...',
              text: 'Please wait',
              allowOutsideClick: false,
              didOpen: () => {
                Swal.showLoading();
              }
            });

            const formData = new FormData();
            formData.append('action', 'delete_venue');
            formData.append('venue_id', venueId);

            fetch('../../../controller/admin/post/events_actions.php', {
              method: 'POST',
              body: formData
            })
              .then(response => response.json())
              .then(data => {
                Swal.close();
                if (data.success) {
                  Swal.fire({
                    title: 'Deleted!',
                    text: data.message,
                    icon: 'success',
                    confirmButtonColor: '#d97706'
                  }).then(() => {
                    renderVenuesManagement();
                    renderVenues();
                  });
                } else {
                  Swal.fire({
                    title: 'Error',
                    text: data.message,
                    icon: 'error',
                    confirmButtonColor: '#d97706'
                  });
                }
              });
          }
        });
      };

      document.getElementById('closeVenuesModal').addEventListener('click', () => {
        document.getElementById('venuesModal').classList.add('hidden');
      });

      document.getElementById('addVenueBtn').addEventListener('click', () => {
        const name = document.getElementById('newVenueName').value.trim();
        const capacity = document.getElementById('newVenueCapacity').value;
        const location = document.getElementById('newVenueLocation').value.trim();
        const price = document.getElementById('newVenuePrice').value;
        const description = document.getElementById('newVenueDescription').value.trim();
        const amenities = document.getElementById('newVenueAmenities').value.trim();

        if (!name || !capacity) {
          showNotification('Please fill in venue name and capacity', 'error');
          return;
        }

        Swal.fire({
          title: 'Adding Venue...',
          text: 'Please wait',
          allowOutsideClick: false,
          didOpen: () => {
            Swal.showLoading();
          }
        });

        const formData = new FormData();
        formData.append('action', 'add_venue');
        formData.append('venue_name', name);
        formData.append('capacity', capacity);
        formData.append('location', location);
        formData.append('price_per_hour', price || 0);
        formData.append('description', description);
        formData.append('amenities', amenities);

        fetch('../../../controller/admin/post/events_actions.php', {
          method: 'POST',
          body: formData
        })
          .then(response => response.json())
          .then(data => {
            Swal.close();
            if (data.success) {
              showNotification('Venue added successfully', 'success');
              // Clear form
              document.getElementById('newVenueName').value = '';
              document.getElementById('newVenueCapacity').value = '';
              document.getElementById('newVenueLocation').value = '';
              document.getElementById('newVenuePrice').value = '';
              document.getElementById('newVenueDescription').value = '';
              document.getElementById('newVenueAmenities').value = '';
              // Refresh venues
              renderVenuesManagement();
              renderVenues();
            } else {
              showNotification(data.message, 'error');
            }
          });
      });

      // Calendar View
      document.getElementById('calendarViewBtn').addEventListener('click', () => {
        const modal = document.getElementById('calendarModal');
        modal.classList.remove('hidden');
        renderCalendar();
      });

      function renderCalendar() {
        const calendarGrid = document.getElementById('calendarGrid');
        const today = new Date();
        const year = today.getFullYear();
        const month = today.getMonth();

        const firstDay = new Date(year, month, 1).getDay();
        const daysInMonth = new Date(year, month + 1, 0).getDate();

        let calendarHTML = '';

        // Empty cells for days before month start
        for (let i = 0; i < firstDay; i++) {
          calendarHTML += '<div class="h-16 border rounded-lg bg-slate-50"></div>';
        }

        // Fill in the days
        for (let day = 1; day <= daysInMonth; day++) {
          const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
          const eventsOnDay = events.filter(e => e.event_date === dateStr);
          const hasEvent = eventsOnDay.length > 0;

          calendarHTML += `
            <div class="h-16 border rounded-lg p-1 calendar-day ${hasEvent ? 'has-event cursor-pointer' : ''}" 
                 data-date="${dateStr}"
                 onclick="showDateEvents('${dateStr}')">
              <span class="font-semibold text-sm">${day}</span>
              ${hasEvent ? `<span class="block text-xs text-amber-600 mt-1">${eventsOnDay.length} event(s)</span>` : ''}
            </div>
          `;
        }

        calendarGrid.innerHTML = calendarHTML;
      }

      window.showDateEvents = function (dateStr) {
        const eventsOnDate = events.filter(e => e.event_date === dateStr);
        const detailsDiv = document.getElementById('calendarEventDetails');

        if (eventsOnDate.length === 0) {
          detailsDiv.innerHTML = '<p class="text-center text-slate-500">No events scheduled for this date</p>';
        } else {
          detailsDiv.innerHTML = `
            <h4 class="font-semibold mb-2">Events on ${formatDate(dateStr)}:</h4>
            <div class="space-y-2 max-h-60 overflow-y-auto">
              ${eventsOnDate.map(event => `
                <div class="border rounded-lg p-2 text-sm hover:bg-amber-50 transition">
                  <div class="flex justify-between">
                    <span class="font-medium">${event.event_name}</span>
                    <span class="text-xs text-slate-500">${formatTime(event.event_time)}</span>
                  </div>
                  <div class="text-xs text-slate-500">${event.venue_name || 'TBD'} · ${event.guests || 0} guests</div>
                </div>
              `).join('')}
            </div>
          `;
        }
      };

      document.getElementById('closeCalendarModal').addEventListener('click', () => {
        document.getElementById('calendarModal').classList.add('hidden');
      });

      // Export Button
      document.getElementById('exportBtn').addEventListener('click', () => {
        Swal.fire({
          title: 'Export Events',
          text: 'Choose export format',
          icon: 'question',
          showCancelButton: true,
          confirmButtonColor: '#d97706',
          cancelButtonColor: '#6b7280',
          confirmButtonText: 'JSON',
          cancelButtonText: 'CSV'
        }).then((result) => {
          const format = result.isConfirmed ? 'json' : 'csv';

          const form = document.createElement('form');
          form.method = 'POST';
          form.action = '../../../controller/admin/post/events_actions.php';
          form.innerHTML = `
            <input type="hidden" name="action" value="export_events">
            <input type="hidden" name="format" value="${format}">
          `;
          document.body.appendChild(form);
          form.submit();
          document.body.removeChild(form);
        });
      });

      // Search
      document.getElementById('searchEvents').addEventListener('input', (e) => {
        const searchTerm = e.target.value.toLowerCase();
        if (searchTerm === '') {
          filteredEvents = upcomingEvents;
        } else {
          filteredEvents = events.filter(e =>
            (e.event_name && e.event_name.toLowerCase().includes(searchTerm)) ||
            (e.event_type && e.event_type.toLowerCase().includes(searchTerm)) ||
            (e.venue_name && e.venue_name.toLowerCase().includes(searchTerm))
          );
        }
        currentPage = 1;
        renderUpcomingTable();
      });

      // Pagination
      document.getElementById('prevPage').addEventListener('click', () => {
        if (currentPage > 1) {
          currentPage--;
          renderUpcomingTable();
        }
      });

      document.getElementById('nextPage').addEventListener('click', () => {
        const maxPage = Math.ceil(filteredEvents.length / itemsPerPage);
        if (currentPage < maxPage) {
          currentPage++;
          renderUpcomingTable();
        }
      });

      document.querySelectorAll('.page-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
          currentPage = parseInt(e.target.dataset.page);
          renderUpcomingTable();
        });
      });

      // Event delegation for edit and delete buttons
      document.addEventListener('click', (e) => {
        if (e.target.classList.contains('edit-event-btn') || e.target.closest('.edit-event-btn')) {
          const btn = e.target.classList.contains('edit-event-btn') ? e.target : e.target.closest('.edit-event-btn');
          const eventId = btn.dataset.id;
          openEditModal(eventId);
        }

        if (e.target.classList.contains('delete-event-btn') || e.target.closest('.delete-event-btn')) {
          const btn = e.target.classList.contains('delete-event-btn') ? e.target : e.target.closest('.delete-event-btn');
          const eventId = btn.dataset.id;
          deleteEvent(eventId);
        }
      });

      // Notification bell
      document.getElementById('notificationBell').addEventListener('click', () => {
        notifications.forEach(n => n.read = true);
        updateNotificationBadge();
        showNotification(`${notifications.length} notification(s) marked as read`, 'info');
      });

      // Quick availability check
      document.getElementById('checkQuickAvailability').addEventListener('click', () => {
        const venueId = document.getElementById('bookingVenue').value;
        const date = document.getElementById('bookingDate').value;
        const guests = document.getElementById('bookingGuests').value;

        if (!date) {
          showNotification('Please select a date', 'error');
          return;
        }

        fetch('../../../controller/admin/post/events_actions.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: 'action=check_availability&date=' + date
        })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              const venue = data.venues.find(v => v.id == venueId);
              if (venue && venue.booking_status === 'available') {
                showNotification(`${venue.name} is available for ${guests || 'your'} guests!`, 'success');
              } else {
                showNotification(`This venue is already booked on ${formatDate(date)}`, 'error');
              }
            }
          });
      });

      // Close modals when clicking outside
      window.addEventListener('click', (e) => {
        if (e.target.classList.contains('fixed')) {
          document.getElementById('newEventModal').classList.add('hidden');
          document.getElementById('editEventModal').classList.add('hidden');
          document.getElementById('availabilityModal').classList.add('hidden');
          document.getElementById('venuesModal').classList.add('hidden');
          document.getElementById('calendarModal').classList.add('hidden');
        }
      });

      // ==================== INITIALIZE ====================
      document.getElementById('currentDate').textContent = formatDate('<?php echo $today; ?>');
      document.getElementById('todayDateDisplay').textContent = formatDate('<?php echo $today; ?>').toLowerCase();

      updateStats();
      renderTodaysEvents();
      renderUpcomingTable();
      renderVenues();

      addNotification('Welcome to Events & Conference Management');
      addNotification(`${todaysEvents.length} events scheduled for today`);
    </script>
  </body>

</html>