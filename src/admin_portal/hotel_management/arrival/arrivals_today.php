<?php
/**
 * View - Admin Today's Arrivals
 * Pure database-driven view - no static data
 */
require_once '../../../../controller/admin/get/todays_arrivals.php';

$current_page = 'arrivals_today';

?>
<!DOCTYPE html>
<html lang="en">

  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin · Today's Arrivals</title>
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

      .arrival-card {
        transition: all 0.2s ease;
      }

      .arrival-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
      }

      .vip-card {
        border-left: 4px solid #fbbf24;
      }
    </style>
  </head>

  <body class="bg-white font-sans antialiased">

    <!-- APP CONTAINER: flex row (sidebar + main) -->
    <div class="min-h-screen flex flex-col lg:flex-row">

      <!-- ========== SIDEBAR ========== -->
      <?php require_once '../../components/admin_nav.php'; ?>

      <!-- ========== MAIN CONTENT (TODAY'S ARRIVALS) ========== -->
      <main class="flex-1 p-5 lg:p-8 overflow-y-auto bg-white">

        <!-- header with title and back button -->
        <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
          <div class="flex items-center gap-4">
            <a href="../hotel_management/arrival/reservation_&_booking.php" class="text-amber-600 hover:text-amber-700">
              <i class="fa-solid fa-arrow-left text-xl"></i>
            </a>
            <div>
              <h1 class="text-2xl lg:text-3xl font-light text-slate-800">Today's Arrivals</h1>
              <p class="text-sm text-slate-500 mt-0.5">guests checking in today · <span id="currentDate">
                  <?php echo $today; ?>
                </span></p>
            </div>
          </div>
          <div class="flex gap-3 text-sm">
            <span class="bg-white border rounded-full px-4 py-2 flex items-center gap-2 shadow-sm">
              <i class="fas fa-clock text-slate-400"></i>
              <span id="currentTime"></span>
            </span>
            <button class="bg-white border rounded-full px-4 py-2 shadow-sm hover:bg-slate-50 transition"
              id="refreshBtn">
              <i class="fa-solid fa-rotate-right"></i>
            </button>
          </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
          <div class="bg-green-50 border border-green-200 rounded-2xl p-5">
            <p class="text-sm text-green-700 font-medium">Total Arrivals Today</p>
            <p class="text-3xl font-bold text-green-700" id="totalArrivals">
              <?php echo $totalArrivals; ?>
            </p>
          </div>
          <div class="bg-yellow-50 border border-yellow-200 rounded-2xl p-5">
            <p class="text-sm text-yellow-700 font-medium">Pending Check-in</p>
            <p class="text-3xl font-bold text-yellow-700" id="pendingArrivals">
              <?php echo $pendingArrivals; ?>
            </p>
          </div>
          <div class="bg-blue-50 border border-blue-200 rounded-2xl p-5">
            <p class="text-sm text-blue-700 font-medium">Rooms Assigned</p>
            <p class="text-3xl font-bold text-blue-700" id="roomsAssigned">
              <?php echo $roomsAssigned; ?>
            </p>
          </div>
        </div>

        <!-- Filter and Search -->
        <div
          class="bg-white rounded-2xl border border-slate-200 p-4 mb-6 flex flex-wrap gap-3 items-center justify-between">
          <div class="flex gap-2 flex-wrap">
            <button
              class="filter-btn px-4 py-2 rounded-xl text-sm <?php echo $statusFilter == 'all' ? 'bg-amber-600 text-white' : 'border border-slate-200 hover:bg-slate-50'; ?>"
              data-filter="all">all arrivals (
              <?php echo $totalArrivals; ?>)
            </button>
            <button
              class="filter-btn px-4 py-2 rounded-xl text-sm <?php echo $statusFilter == 'pending' ? 'bg-amber-600 text-white' : 'border border-slate-200 hover:bg-slate-50'; ?>"
              data-filter="pending">pending (
              <?php echo $pendingArrivals; ?>)
            </button>
            <button
              class="filter-btn px-4 py-2 rounded-xl text-sm <?php echo $statusFilter == 'confirmed' ? 'bg-amber-600 text-white' : 'border border-slate-200 hover:bg-slate-50'; ?>"
              data-filter="confirmed">confirmed (
              <?php echo $confirmedArrivals; ?>)
            </button>
            <button
              class="filter-btn px-4 py-2 rounded-xl text-sm <?php echo $statusFilter == 'checked_in' ? 'bg-amber-600 text-white' : 'border border-slate-200 hover:bg-slate-50'; ?>"
              data-filter="checked_in">checked-in (
              <?php echo $checkedInArrivals; ?>)
            </button>
            <button
              class="filter-btn px-4 py-2 rounded-xl text-sm <?php echo $statusFilter == 'vip' ? 'bg-amber-600 text-white' : 'border border-slate-200 hover:bg-slate-50'; ?>"
              data-filter="vip">vip guests</button>
          </div>
          <div class="relative">
            <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
            <input type="text" id="searchInput" value="<?php echo htmlspecialchars($searchFilter); ?>"
              placeholder="search by name or booking #..."
              class="border border-slate-200 rounded-xl pl-9 pr-4 py-2 text-sm w-64 focus:ring-1 focus:ring-amber-500 outline-none">
          </div>
        </div>

        <!-- Time Slots / Schedule View -->
        <div class="mb-6">
          <div class="flex items-center gap-4 mb-3">
            <h2 class="font-semibold text-lg">Arrival Schedule</h2>
            <span class="text-xs bg-slate-100 px-3 py-1 rounded-full">estimated check-in time</span>
          </div>

          <?php foreach ($timeSlots as $slotKey => $slot): ?>
            <div class="mb-4">
              <div class="flex items-center gap-2 mb-2">
                <span class="<?php echo $slot['color']; ?> text-xs font-medium px-3 py-1 rounded-full">
                  <?php echo $slot['icon']; ?>
                  <?php echo $slot['name']; ?>
                </span>
                <span class="text-xs text-slate-500" id="<?php echo $slotKey; ?>Count">
                  <?php echo count($slot['guests']); ?> guests
                </span>
              </div>
              <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3" id="<?php echo $slotKey; ?>Slot">
                <?php if (empty($slot['guests'])): ?>
                  <div class="col-span-full text-center py-4 text-slate-400 bg-slate-50 rounded-xl">
                    No arrivals in this time slot
                  </div>
                <?php else: ?>
                  <?php foreach ($slot['guests'] as $arrival): ?>
                    <div
                      class="bg-white border <?php echo $arrival['vip'] ? 'border-yellow-300 vip-card' : 'border-slate-200'; ?> rounded-xl p-4 shadow-sm arrival-card"
                      data-id="<?php echo $arrival['id']; ?>">
                      <div class="flex justify-between items-start mb-2">
                        <span class="font-medium">
                          <?php echo htmlspecialchars($arrival['guest']); ?>
                        </span>
                        <?php if ($arrival['vip']): ?>
                          <span class="text-yellow-600 text-xs"><i class="fa-solid fa-crown"></i> VIP</span>
                        <?php endif; ?>
                      </div>
                      <div class="text-xs text-slate-500 mb-2">
                        <?php echo $arrival['bookingNo']; ?> ·
                        <?php echo $arrival['roomType']; ?>
                        <?php if ($arrival['nights']): ?> ·
                          <?php echo $arrival['nights']; ?> nights
                        <?php endif; ?>
                      </div>
                      <div class="flex justify-between items-center">
                        <span class="text-xs"><i class="fas fa-clock mr-1"></i>
                          <?php echo $arrival['displayTime'] ?? '2:00 PM'; ?>
                        </span>
                        <span class="<?php
                        echo $arrival['status'] == 'pending' ? 'bg-yellow-100 text-yellow-700' :
                          ($arrival['status'] == 'confirmed' ? 'bg-green-100 text-green-700' :
                            'bg-blue-100 text-blue-700');
                        ?> px-2 py-0.5 rounded-full text-xs">
                          <?php echo $arrival['status']; ?>
                        </span>
                      </div>
                      <?php if ($arrival['specialRequests']): ?>
                        <div class="text-xs text-slate-400 mt-2">
                          <i class="fas fa-note-sticky mr-1"></i>
                          <?php echo htmlspecialchars(substr($arrival['specialRequests'], 0, 30)) . (strlen($arrival['specialRequests']) > 30 ? '...' : ''); ?>
                        </div>
                      <?php endif; ?>
                      <div class="flex gap-2 mt-3">
                        <?php if ($arrival['status'] == 'pending'): ?>
                          <button onclick="openCheckinModal(<?php echo $arrival['id']; ?>)"
                            class="flex-1 bg-green-600 text-white text-xs py-1.5 rounded-lg hover:bg-green-700 transition">check-in</button>
                        <?php elseif ($arrival['status'] == 'confirmed'): ?>
                          <button onclick="assignRoom(<?php echo $arrival['id']; ?>)"
                            class="flex-1 bg-amber-600 text-white text-xs py-1.5 rounded-lg hover:bg-amber-700 transition">assign
                            room</button>
                        <?php else: ?>
                          <button onclick="viewDetails(<?php echo $arrival['id']; ?>)"
                            class="flex-1 border border-slate-200 text-xs py-1.5 rounded-lg hover:bg-slate-50 transition">view</button>
                        <?php endif; ?>
                        <button onclick="contactGuest(<?php echo $arrival['id']; ?>)"
                          class="border border-slate-200 text-xs py-1.5 rounded-lg hover:bg-slate-50 transition px-2">
                          <i class="fas fa-message"></i>
                        </button>
                      </div>
                    </div>
                  <?php endforeach; ?>
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>

        <!-- Table View (Alternative view) -->
        <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
          <div class="p-4 border-b border-slate-200 flex justify-between items-center">
            <h2 class="font-semibold">All Arrivals</h2>
            <div class="flex gap-2">
              <button onclick="exportList()" class="text-xs text-amber-600 hover:underline">export list</button>
              <button onclick="printList()" class="text-xs text-amber-600 hover:underline">print</button>
            </div>
          </div>
          <div class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead class="bg-slate-50 text-slate-500 text-xs border-b">
                <tr>
                  <td class="p-4">Booking #</td>
                  <td class="p-4">Guest</td>
                  <td class="p-4">Room Type</td>
                  <td class="p-4">Room Assigned</td>
                  <td class="p-4">Check-in Date</td>
                  <td class="p-4">Nights</td>
                  <td class="p-4">Status</td>
                  <td class="p-4">Payment</td>
                  <td class="p-4">Actions</td>
                </tr>
              </thead>
              <tbody class="divide-y" id="tableBody">
                <?php if (empty($arrivals)): ?>
                  <tr>
                    <td colspan="9" class="p-8 text-center text-slate-400">
                      No arrivals scheduled for today
                    </td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($arrivals as $arrival): ?>
                    <tr>
                      <td class="p-4 font-medium">
                        <?php echo $arrival['bookingNo']; ?>
                      </td>
                      <td class="p-4">
                        <?php echo htmlspecialchars($arrival['guest']); ?>
                        <?php if ($arrival['vip']): ?>
                          <span class="ml-2 text-yellow-600 text-xs"><i class="fa-solid fa-crown"></i></span>
                        <?php endif; ?>
                      </td>
                      <td class="p-4">
                        <?php echo $arrival['roomType']; ?>
                      </td>
                      <td class="p-4">
                        <?php echo $arrival['roomAssigned'] ?: '—'; ?>
                      </td>
                      <td class="p-4">
                        <?php echo date('M d, Y', strtotime($arrival['checkInDate'])); ?>
                      </td>
                      <td class="p-4">
                        <?php echo $arrival['nights']; ?>
                      </td>
                      <td class="p-4">
                        <span class="<?php
                        echo $arrival['status'] == 'pending' ? 'bg-yellow-100 text-yellow-700' :
                          ($arrival['status'] == 'confirmed' ? 'bg-green-100 text-green-700' :
                            'bg-blue-100 text-blue-700');
                        ?> px-2 py-0.5 rounded-full text-xs">
                          <?php echo $arrival['status']; ?>
                        </span>
                      </td>
                      <td class="p-4">
                        <span
                          class="<?php echo $arrival['payment_status'] == 'paid' ? 'text-green-600' : 'text-amber-600'; ?> text-xs">
                          <?php echo $arrival['payment_status']; ?>
                        </span>
                      </td>
                      <td class="p-4">
                        <?php if ($arrival['status'] == 'pending'): ?>
                          <button onclick="openCheckinModal(<?php echo $arrival['id']; ?>)"
                            class="text-green-600 hover:underline text-xs mr-2">check-in</button>
                        <?php elseif ($arrival['status'] == 'confirmed'): ?>
                          <button onclick="assignRoom(<?php echo $arrival['id']; ?>)"
                            class="text-amber-600 hover:underline text-xs mr-2">assign</button>
                        <?php else: ?>
                          <button onclick="viewDetails(<?php echo $arrival['id']; ?>)"
                            class="text-blue-600 hover:underline text-xs mr-2">view</button>
                        <?php endif; ?>
                        <button onclick="contactGuest(<?php echo $arrival['id']; ?>)"
                          class="text-slate-400 hover:text-slate-600 text-xs">contact</button>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
          <div class="p-4 border-t border-slate-200 flex items-center justify-between">
            <span class="text-xs text-slate-500">Showing
              <?php echo count($arrivals); ?> arrivals for
              <?php echo date('F j, Y'); ?>
            </span>
          </div>
        </div>

      </main>
    </div>

    <!-- Quick Check-in Modal -->
    <div id="checkinModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
      <div class="bg-white rounded-2xl p-6 w-full max-w-md">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-xl font-semibold">Quick Check-in</h3>
          <button onclick="closeModal()" class="text-slate-400 hover:text-slate-600">
            <i class="fa-solid fa-times"></i>
          </button>
        </div>

        <!-- Guest Info Summary (dynamic) -->
        <div id="guestInfoSummary" class="mb-4 p-3 bg-slate-50 rounded-xl hidden">
          <div class="flex items-center justify-between mb-2">
            <span class="font-medium" id="guestName"></span>
            <span id="guestVipBadge" class="hidden text-yellow-600 text-xs"><i class="fa-solid fa-crown"></i> VIP</span>
          </div>
          <div class="text-xs text-slate-600 space-y-1">
            <p><span class="font-medium">Booking:</span> <span id="guestBookingNo"></span></p>
            <p><span class="font-medium">Room:</span> <span id="guestRoomType"></span></p>
            <p><span class="font-medium">Guests:</span> <span id="guestCount"></span> (<span id="guestAdults"></span>
              adults, <span id="guestChildren"></span> children)</p>
            <p><span class="font-medium">Stay:</span> <span id="guestNights"></span> nights</p>
            <p><span class="font-medium">Total:</span> ₱<span id="guestTotal"></span></p>
          </div>
        </div>

        <form id="checkinForm" class="space-y-4" onsubmit="processCheckin(event)">
          <input type="hidden" id="checkinBookingId">
          <input type="hidden" id="guestPaymentStatus">

          <!-- Payment Status Warning (shown if unpaid) -->
          <div id="paymentWarning" class="hidden bg-amber-50 border border-amber-200 rounded-xl p-3 mb-2">
            <div class="flex items-center gap-2 text-amber-700">
              <i class="fa-solid fa-triangle-exclamation"></i>
              <span class="text-sm font-medium">Payment Required</span>
            </div>
            <p class="text-xs text-amber-600 mt-1">This guest has an outstanding balance. Please collect payment before
              check-in.</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Room Number</label>
            <select id="roomNumber"
              class="w-full border border-slate-200 rounded-xl px-4 py-2 focus:ring-1 focus:ring-amber-500 outline-none"
              required>
              <option value="">Select available room</option>
              <?php if (empty($availableRooms)): ?>
                <option value="" disabled>No rooms available</option>
              <?php else: ?>
                <?php foreach ($availableRooms as $room): ?>
                  <option value="<?php echo $room['id']; ?>" data-capacity="<?php echo $room['max_occupancy']; ?>">
                    Room <?php echo $room['id']; ?> - <?php echo $room['name']; ?>
                    (max <?php echo $room['max_occupancy']; ?> pax) - ₱<?php echo number_format($room['price']); ?>/night
                  </option>
                <?php endforeach; ?>
              <?php endif; ?>
            </select>
            <p id="roomCapacityWarning" class="text-xs text-amber-600 mt-1 hidden">
              <i class="fa-solid fa-circle-exclamation mr-1"></i>Selected room may not have enough capacity for this
              party
            </p>
          </div>

          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">ID Verification</label>
            <select id="idVerification"
              class="w-full border border-slate-200 rounded-xl px-4 py-2 focus:ring-1 focus:ring-amber-500 outline-none">
              <option value="verified">Verified</option>
              <option value="pending">Pending</option>
            </select>
          </div>

          <!-- Dynamic Payment Status Display -->
          <div id="paymentStatusSection">
            <label class="block text-sm font-medium text-slate-700 mb-1">Payment Status</label>
            <div class="flex items-center gap-3">
              <select id="paymentStatus"
                class="flex-1 border border-slate-200 rounded-xl px-4 py-2 focus:ring-1 focus:ring-amber-500 outline-none">
                <option value="paid">Paid</option>
                <option value="pending">Pending</option>
                <option value="partial">Partial</option>
              </select>
              <span id="paymentStatusBadge" class="text-xs px-2 py-1 rounded-full hidden"></span>
            </div>
          </div>

          <!-- Amount Paid (if partial) -->
          <div id="amountPaidSection" class="hidden">
            <label class="block text-sm font-medium text-slate-700 mb-1">Amount Paid (₱)</label>
            <input type="number" id="amountPaid"
              class="w-full border border-slate-200 rounded-xl px-4 py-2 focus:ring-1 focus:ring-amber-500 outline-none"
              min="0" step="0.01">
          </div>

          <!-- Special Requests (if any) -->
          <div id="specialRequestsSection" class="hidden">
            <label class="block text-sm font-medium text-slate-700 mb-1">Special Requests</label>
            <p id="specialRequestsText" class="text-sm text-slate-600 bg-slate-50 p-2 rounded-lg"></p>
          </div>

          <div class="flex gap-3 mt-4">
            <button type="submit"
              class="bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded-xl text-sm flex-1 transition">Confirm
              Check-in</button>
            <button type="button" onclick="closeModal()"
              class="border border-slate-300 px-5 py-2 rounded-xl text-sm hover:bg-slate-50 transition">Cancel</button>
          </div>
        </form>
      </div>
    </div>
    <!-- Toast notification -->
    <div id="toast"
      class="fixed bottom-4 right-4 bg-slate-800 text-white px-4 py-2 rounded-lg shadow-lg transform transition-transform translate-y-20 opacity-0 z-50"
      style="transition: all 0.3s ease;">
      <span id="toastMessage"></span>
    </div>

    <script>
      // Pass PHP data to JavaScript (all from database)
      const arrivals = <?php echo json_encode($arrivals); ?>;
      const availableRooms = <?php echo json_encode($availableRooms); ?>;
      let currentFilter = '<?php echo $statusFilter; ?>';
      let currentSearch = '<?php echo $searchFilter; ?>';
      let currentPage = 1;
      const rowsPerPage = 5;

      // ========== INITIALIZATION ==========
      document.addEventListener('DOMContentLoaded', function () {
        updateDateTime();
        setupEventListeners();
        updateSummaryCards();

        // Setup payment status change handler
        const paymentStatus = document.getElementById('paymentStatus');
        if (paymentStatus) {
          paymentStatus.addEventListener('change', function () {
            if (this.value === 'partial') {
              document.getElementById('amountPaidSection').classList.remove('hidden');
            } else {
              document.getElementById('amountPaidSection').classList.add('hidden');
            }
          });
        }

        // Setup room capacity check
        const roomSelect = document.getElementById('roomNumber');
        if (roomSelect) {
          roomSelect.addEventListener('change', checkRoomCapacity);
        }
      });

      // ========== DATE & TIME ==========
      function updateDateTime() {
        const now = new Date();
        const options = { year: 'numeric', month: 'long', day: 'numeric' };
        const dateEl = document.getElementById('currentDate');
        const timeEl = document.getElementById('currentTime');

        if (dateEl) dateEl.textContent = now.toLocaleDateString('en-US', options);
        if (timeEl) timeEl.textContent = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
      }

      // ========== EVENT LISTENERS ==========
      function setupEventListeners() {
        // Search input
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
          searchInput.addEventListener('keypress', function (e) {
            if (e.key === 'Enter') {
              performSearch();
            }
          });

          // Add search button
          const searchBtn = document.createElement('button');
          searchBtn.className = 'absolute right-2 top-1/2 -translate-y-1/2 bg-amber-600 text-white px-3 py-1 rounded-lg text-xs hover:bg-amber-700 transition';
          searchBtn.innerHTML = '<i class="fa-solid fa-search"></i>';
          searchBtn.onclick = performSearch;
          searchInput.parentNode.appendChild(searchBtn);
        }

        // Filter buttons
        document.querySelectorAll('.filter-btn').forEach(btn => {
          btn.addEventListener('click', function () {
            const filter = this.dataset.filter;
            const url = new URL(window.location);
            if (filter !== 'all') {
              url.searchParams.set('status', filter);
            } else {
              url.searchParams.delete('status');
            }
            if (currentSearch) {
              url.searchParams.set('search', currentSearch);
            }
            window.location.href = url.toString();
          });
        });

        // Refresh button
        const refreshBtn = document.getElementById('refreshBtn');
        if (refreshBtn) {
          refreshBtn.addEventListener('click', function () {
            location.reload();
          });
        }

        // Export button
        const exportBtn = document.querySelector('[onclick="exportList()"]');
        if (exportBtn) {
          exportBtn.addEventListener('click', exportList);
        }

        // Print button
        const printBtn = document.querySelector('[onclick="printList()"]');
        if (printBtn) {
          printBtn.addEventListener('click', printList);
        }
      }

      // ========== SEARCH ==========
      function performSearch() {
        const search = document.getElementById('searchInput').value;
        const url = new URL(window.location);
        if (search) {
          url.searchParams.set('search', search);
        } else {
          url.searchParams.delete('search');
        }
        if (currentFilter !== 'all') {
          url.searchParams.set('status', currentFilter);
        }
        window.location.href = url.toString();
      }

      // ========== SUMMARY CARDS ==========
      function updateSummaryCards() {
        const totalEl = document.getElementById('totalArrivals');
        const pendingEl = document.getElementById('pendingArrivals');
        const roomsEl = document.getElementById('roomsAssigned');

        if (totalEl) totalEl.textContent = arrivals.length;
        if (pendingEl) pendingEl.textContent = arrivals.filter(a => a.status === 'pending').length;
        if (roomsEl) roomsEl.textContent = arrivals.filter(a => a.roomAssigned).length;
      }

      // ========== CHECK-IN MODAL ==========
      function openCheckinModal(bookingId) {
        // Find the arrival data
        const arrival = arrivals.find(a => a.id == bookingId);
        if (!arrival) {
          showNotification('Booking not found');
          return;
        }

        // Set booking ID
        document.getElementById('checkinBookingId').value = bookingId;

        // Show guest info summary
        document.getElementById('guestName').textContent = arrival.guest;
        document.getElementById('guestBookingNo').textContent = arrival.bookingNo;
        document.getElementById('guestRoomType').textContent = arrival.roomType;
        document.getElementById('guestAdults').textContent = arrival.adults || 0;
        document.getElementById('guestChildren').textContent = arrival.children || 0;
        document.getElementById('guestNights').textContent = arrival.nights || 1;
        document.getElementById('guestTotal').textContent = (arrival.total_amount || 0).toLocaleString();

        const totalGuests = (arrival.adults || 0) + (arrival.children || 0);
        document.getElementById('guestCount').textContent = totalGuests;
        document.getElementById('guestPaymentStatus').value = arrival.payment_status || 'unpaid';

        // Show VIP badge if applicable
        const vipBadge = document.getElementById('guestVipBadge');
        if (vipBadge) {
          if (arrival.vip) {
            vipBadge.classList.remove('hidden');
          } else {
            vipBadge.classList.add('hidden');
          }
        }

        // Show guest info section
        document.getElementById('guestInfoSummary').classList.remove('hidden');

        // Show payment warning if unpaid
        const paymentWarning = document.getElementById('paymentWarning');
        const paymentStatusSelect = document.getElementById('paymentStatus');

        if (arrival.payment_status === 'unpaid') {
          paymentWarning.classList.remove('hidden');
          paymentStatusSelect.value = 'pending';
        } else {
          paymentWarning.classList.add('hidden');
          if (arrival.payment_status === 'paid') {
            paymentStatusSelect.value = 'paid';
          } else if (arrival.payment_status === 'partial') {
            paymentStatusSelect.value = 'partial';
            document.getElementById('amountPaidSection').classList.remove('hidden');
          }
        }

        // Show special requests if any
        const specialRequestsSection = document.getElementById('specialRequestsSection');
        const specialRequestsText = document.getElementById('specialRequestsText');

        if (arrival.specialRequests && arrival.specialRequests.trim() !== '') {
          specialRequestsText.textContent = arrival.specialRequests;
          specialRequestsSection.classList.remove('hidden');
        } else {
          specialRequestsSection.classList.add('hidden');
        }

        // Pre-select room if already assigned
        if (arrival.roomAssigned) {
          const roomSelect = document.getElementById('roomNumber');
          for (let i = 0; i < roomSelect.options.length; i++) {
            if (roomSelect.options[i].value === arrival.roomAssigned) {
              roomSelect.selectedIndex = i;
              break;
            }
          }
        }

        // Show modal
        document.getElementById('checkinModal').classList.remove('hidden');
        document.getElementById('checkinModal').classList.add('flex');

        // Check room capacity
        checkRoomCapacity();
      }

      function closeModal() {
        document.getElementById('checkinModal').classList.add('hidden');
        document.getElementById('checkinModal').classList.remove('flex');

        // Reset form
        document.getElementById('checkinForm').reset();
        document.getElementById('guestInfoSummary').classList.add('hidden');
        document.getElementById('paymentWarning').classList.add('hidden');
        document.getElementById('amountPaidSection').classList.add('hidden');
        document.getElementById('specialRequestsSection').classList.add('hidden');
        document.getElementById('roomCapacityWarning').classList.add('hidden');
      }

      function checkRoomCapacity() {
        const roomSelect = document.getElementById('roomNumber');
        const selectedOption = roomSelect.options[roomSelect.selectedIndex];
        const capacity = parseInt(selectedOption?.dataset?.capacity || 0);
        const guestCount = parseInt(document.getElementById('guestCount').textContent);
        const warningEl = document.getElementById('roomCapacityWarning');

        if (capacity > 0 && guestCount > capacity && warningEl) {
          warningEl.classList.remove('hidden');
        } else if (warningEl) {
          warningEl.classList.add('hidden');
        }
      }

      function processCheckin(event) {
        event.preventDefault();

        const bookingId = document.getElementById('checkinBookingId').value;
        const roomNumber = document.getElementById('roomNumber').value;
        const idVerification = document.getElementById('idVerification').value;
        const paymentStatus = document.getElementById('paymentStatus').value;
        const amountPaid = document.getElementById('amountPaid').value;

        if (!roomNumber) {
          showNotification('Please select a room number');
          return;
        }

        // Check capacity
        const roomSelect = document.getElementById('roomNumber');
        const selectedOption = roomSelect.options[roomSelect.selectedIndex];
        const capacity = parseInt(selectedOption?.dataset?.capacity || 0);
        const guestCount = parseInt(document.getElementById('guestCount').textContent);

        if (capacity > 0 && guestCount > capacity) {
          Swal.fire({
            title: 'Warning',
            text: `This room has a maximum capacity of ${capacity} guests but you have ${guestCount} guests. Continue anyway?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d97706',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Yes, continue'
          }).then((result) => {
            if (result.isConfirmed) {
              submitCheckin(bookingId, roomNumber, idVerification, paymentStatus, amountPaid);
            }
          });
        } else {
          submitCheckin(bookingId, roomNumber, idVerification, paymentStatus, amountPaid);
        }
      }

      function submitCheckin(bookingId, roomNumber, idVerification, paymentStatus, amountPaid) {
        Swal.fire({
          title: 'Processing Check-in',
          text: 'Please wait...',
          allowOutsideClick: false,
          didOpen: () => {
            Swal.showLoading();
          }
        });

        const formData = new FormData();
        formData.append('action', 'check_in');
        formData.append('booking_id', bookingId);
        formData.append('room_number', roomNumber);
        formData.append('id_verification', idVerification);
        formData.append('payment_status', paymentStatus);
        if (paymentStatus === 'partial' && amountPaid) {
          formData.append('amount_paid', amountPaid);
        }

        fetch('../../controller/admin/post/arrival_actions.php', {
          method: 'POST',
          body: formData
        })
          .then(response => response.json())
          .then(data => {
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
            Swal.fire({
              title: 'Error',
              text: 'An error occurred. Please try again.',
              icon: 'error',
              confirmButtonColor: '#d97706'
            });
          });
      }

      // ========== ASSIGN ROOM ==========
      function assignRoom(bookingId) {
        openCheckinModal(bookingId);
      }

      // ========== CONTACT GUEST ==========
      function contactGuest(bookingId) {
        const arrival = arrivals.find(a => a.id == bookingId);

        Swal.fire({
          title: 'Contact Guest',
          input: 'textarea',
          inputLabel: 'Message',
          inputValue: `Hello ${arrival?.guest?.split(' ')[0] || 'Guest'}, this is the front desk. Please proceed to the front desk for check-in.`,
          inputPlaceholder: 'Type your message...',
          showCancelButton: true,
          confirmButtonColor: '#d97706',
          cancelButtonColor: '#6b7280',
          confirmButtonText: 'Send',
          preConfirm: (message) => {
            if (!message) {
              Swal.showValidationMessage('Please enter a message');
              return false;
            }

            const formData = new FormData();
            formData.append('action', 'contact_guest');
            formData.append('booking_id', bookingId);
            formData.append('message', message);

            return fetch('../../controller/admin/post/arrival_actions.php', {
              method: 'POST',
              body: formData
            })
              .then(response => response.json())
              .then(data => {
                if (!data.success) {
                  throw new Error(data.message);
                }
                return data;
              })
              .catch(error => {
                Swal.showValidationMessage(error.message);
              });
          }
        }).then((result) => {
          if (result.isConfirmed) {
            Swal.fire({
              title: 'Sent!',
              text: result.value.message,
              icon: 'success',
              confirmButtonColor: '#d97706'
            });
          }
        });
      }

      // ========== VIEW DETAILS ==========
      function viewDetails(bookingId) {
        const formData = new FormData();
        formData.append('action', 'get_guest_details');
        formData.append('booking_id', bookingId);

        fetch('../../controller/admin/post/arrival_actions.php', {
          method: 'POST',
          body: formData
        })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              const guest = data.guest;

              let historyHtml = '';
              if (data.history && data.history.length > 0) {
                historyHtml = '<div class="mt-4"><p class="font-medium text-sm mb-2">Previous Stays:</p><div class="space-y-2">';
                data.history.forEach(h => {
                  historyHtml += `<div class="text-xs bg-slate-50 p-2 rounded">
                            ${h.booking_reference} · ${h.room_name} · ${new Date(h.check_in).toLocaleDateString()}
                        </div>`;
                });
                historyHtml += '</div></div>';
              }

              const paymentStatusClass = guest.payment_status === 'paid' ? 'text-green-600' :
                (guest.payment_status === 'unpaid' ? 'text-red-600' : 'text-amber-600');

              Swal.fire({
                title: 'Guest Details',
                html: `
                        <div class="text-left max-h-96 overflow-y-auto px-2">
                            <div class="bg-amber-50 p-3 rounded-lg mb-3">
                                <p class="font-semibold text-lg">${guest.guest_first_name} ${guest.guest_last_name}</p>
                                ${guest.member_tier ? `<p class="text-xs text-amber-600 capitalize">${guest.member_tier} member</p>` : ''}
                            </div>
                            
                            <div class="grid grid-cols-2 gap-2 text-sm mb-3">
                                <div><span class="text-slate-500">Booking:</span> ${guest.booking_reference}</div>
                                <div><span class="text-slate-500">Room:</span> ${guest.room_name}</div>
                                <div><span class="text-slate-500">Check-in:</span> ${new Date(guest.check_in).toLocaleDateString()}</div>
                                <div><span class="text-slate-500">Check-out:</span> ${new Date(guest.check_out).toLocaleDateString()}</div>
                                <div><span class="text-slate-500">Nights:</span> ${guest.nights}</div>
                                <div><span class="text-slate-500">Guests:</span> ${guest.adults} adults, ${guest.children} children</div>
                                <div><span class="text-slate-500">Status:</span> ${guest.status}</div>
                                <div><span class="text-slate-500">Payment:</span> <span class="${paymentStatusClass}">${guest.payment_status}</span></div>
                                <div class="col-span-2"><span class="text-slate-500">Amount:</span> ₱${parseFloat(guest.total_amount).toLocaleString()}</div>
                            </div>
                            
                            ${guest.guest_email ? `<p class="text-sm mb-1"><span class="text-slate-500">Email:</span> ${guest.guest_email}</p>` : ''}
                            ${guest.guest_phone ? `<p class="text-sm mb-1"><span class="text-slate-500">Phone:</span> ${guest.guest_phone}</p>` : ''}
                            ${guest.preferences ? `<p class="text-sm mb-1"><span class="text-slate-500">Preferences:</span> ${guest.preferences}</p>` : ''}
                            ${guest.allergies ? `<p class="text-sm mb-1"><span class="text-slate-500">Allergies:</span> ${guest.allergies}</p>` : ''}
                            ${guest.special_requests ? `<p class="text-sm mb-1"><span class="text-slate-500">Requests:</span> ${guest.special_requests}</p>` : ''}
                            
                            ${historyHtml}
                        </div>
                    `,
                confirmButtonColor: '#d97706',
                confirmButtonText: 'Close',
                width: '500px'
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

      // ========== EXPORT & PRINT ==========
      function exportList() {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '../../controller/admin/post/arrival_actions.php';
        form.innerHTML = `
            <input type="hidden" name="action" value="export_arrivals">
            <input type="hidden" name="format" value="csv">
        `;
        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);

        showNotification('Exporting arrivals list...');
      }

      function printList() {
        window.print();
        showNotification('Preparing print view...');
      }

      // ========== TOAST NOTIFICATION ==========
      function showNotification(message) {
        const toast = document.getElementById('toast');
        const toastMessage = document.getElementById('toastMessage');

        if (!toast || !toastMessage) return;

        toastMessage.textContent = message;
        toast.classList.remove('translate-y-20', 'opacity-0');

        setTimeout(() => {
          toast.classList.add('translate-y-20', 'opacity-0');
        }, 3000);
      }

      // ========== AUTO-REFRESH ==========
      setInterval(() => {
        updateDateTime();
      }, 60000);
    </script>
  </body>

</html>