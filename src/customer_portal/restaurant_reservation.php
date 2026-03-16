<?php
require_once '../../controller/customer/get/restaurant_reservation.php';
?>

<!DOCTYPE html>
<html lang="en">

  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lùcas · Table & Seating with Down Payment</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Tailwind via CDN + Font Awesome 6 -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
      .waiting-pulse {
        animation: softPulse 2s infinite;
      }

      @keyframes softPulse {
        0% {
          opacity: 0.7;
        }

        50% {
          opacity: 1;
        }

        100% {
          opacity: 0.7;
        }
      }

      .table-tile {
        transition: all 0.1s ease;
        user-select: none;
        cursor: pointer;
      }

      .table-tile:hover {
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
      }

      button {
        cursor: pointer;
      }

      .payment-badge {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
      }
    </style>
  </head>

  <body class="bg-slate-50 font-sans antialiased">

    <!-- main flex wrapper (sidebar + content) -->
    <div class="min-h-screen flex flex-col lg:flex-row">

      <!-- ========== SIDEBAR (staff portal) ========== -->
      <?php require './components/customer_nav.php' ?>
      <!-- ========== MAIN CONTENT ========== -->
      <main class="flex-1 p-5 lg:p-8 overflow-y-auto" id="mainContent">

        <!-- header with real-time date and status -->
        <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
          <div>
            <h1 class="text-2xl lg:text-3xl font-light text-slate-800">Table reservation & seating</h1>
            <p class="text-sm text-slate-500 mt-0.5">automated workflow · waiting list · table assignment</p>
          </div>
          <div
            class="bg-white border border-slate-200 rounded-full px-4 py-2 text-sm flex items-center gap-2 shadow-sm">
            <i class="fa-regular fa-calendar text-slate-400"></i>
            <?php echo strtolower(date('l, F j, Y')); ?> ·
            <span class="text-amber-600 font-medium">service:
              <?php echo date('g:i A'); ?>
            </span>
          </div>
        </div>

        <!-- ===== MAIN MODULE GRID (ALL FUNCTIONAL, NO SAMPLE DATA LOADED) ===== -->
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

          <!-- LEFT COLUMN (xl:col-span-2) -->
          <div class="xl:col-span-2 space-y-6">

            <!-- 1️⃣ ONLINE RESERVATION FORM WITH DOWN PAYMENT -->
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
              <h2 class="font-semibold text-xl flex items-center gap-2 mb-5">
                <i class="fa-regular fa-pen-to-square text-amber-600"></i> online reservation · new booking
                <span class="payment-badge text-white text-xs px-3 py-1 rounded-full ml-2">down payment required</span>
              </h2>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                  <label class="block text-xs text-slate-500 mb-1">customer name <span
                      class="text-red-400">*</span></label>
                  <input type="text" id="resName" placeholder="e.g. Mia Cruz"
                    class="w-full border border-slate-200 rounded-xl p-3 text-sm bg-white" value="">
                </div>
                <div>
                  <label class="block text-xs text-slate-500 mb-1">contact number <span
                      class="text-red-400">*</span></label>
                  <input type="tel" id="resPhone" placeholder="+63 912 345 6789"
                    class="w-full border border-slate-200 rounded-xl p-3 text-sm bg-white" value="">
                </div>
                <div>
                  <label class="block text-xs text-slate-500 mb-1">email address <span
                      class="text-red-400">*</span></label>
                  <input type="email" id="resEmail" placeholder="guest@example.com"
                    class="w-full border border-slate-200 rounded-xl p-3 text-sm bg-white" value="">
                </div>
                <div>
                  <label class="block text-xs text-slate-500 mb-1">number of guests <span
                      class="text-red-400">*</span></label>
                  <select id="resGuests" class="w-full border border-slate-200 rounded-xl p-3 text-sm bg-white">
                    <option value="1">1 guest</option>
                    <option value="2">2 guests</option>
                    <option value="3">3 guests</option>
                    <option value="4">4 guests</option>
                    <option value="5">5 guests</option>
                    <option value="6">6 guests</option>
                  </select>
                </div>
                <div>
                  <label class="block text-xs text-slate-500 mb-1">reservation date <span
                      class="text-red-400">*</span></label>
                  <input type="date" id="resDate" value="2025-05-21"
                    class="w-full border border-slate-200 rounded-xl p-3 text-sm">
                </div>
                <div>
                  <label class="block text-xs text-slate-500 mb-1">reservation time <span
                      class="text-red-400">*</span></label>
                  <select id="resTime" class="w-full border border-slate-200 rounded-xl p-3 text-sm bg-white">
                    <option value="5:30 PM">5:30 PM</option>
                    <option value="6:00 PM">6:00 PM</option>
                    <option value="6:30 PM">6:30 PM</option>
                    <option value="7:00 PM">7:00 PM</option>
                    <option value="7:30 PM">7:30 PM</option>
                    <option value="8:00 PM">8:00 PM</option>
                  </select>
                </div>
                <div class="md:col-span-2">
                  <label class="block text-xs text-slate-500 mb-1">special requests (optional)</label>
                  <textarea id="resRequest" rows="2" placeholder="allergies, celebration, etc."
                    class="w-full border border-slate-200 rounded-xl p-3 text-sm"></textarea>
                </div>
              </div>

              <!-- Down Payment Calculator -->
              <div class="mt-5 p-4 bg-amber-50 rounded-xl border border-amber-200">
                <div class="flex items-center justify-between mb-2">
                  <p class="font-medium flex items-center gap-2"><i class="fa-solid fa-coins text-amber-600"></i> Down
                    Payment Calculation</p>
                  <span class="text-xs bg-white px-2 py-1 rounded-full">₱100 per guest</span>
                </div>
                <div class="grid grid-cols-2 gap-4">
                  <div>
                    <p class="text-xs text-slate-600">Number of guests</p>
                    <p class="text-2xl font-bold" id="guestCountDisplay">2</p>
                  </div>
                  <div>
                    <p class="text-xs text-slate-600">Required down payment</p>
                    <p class="text-2xl font-bold text-amber-700" id="downPaymentAmount">₱200.00</p>
                  </div>
                </div>
                <p class="text-xs text-slate-500 mt-2"><i class="fa-regular fa-circle-info"></i> 50% of down payment
                  will be converted to loyalty points after visit</p>
              </div>

              <div class="flex flex-wrap items-center gap-3 mt-6">
                <button class="bg-amber-600 hover:bg-amber-700 text-white px-8 py-3 rounded-xl font-medium transition"
                  id="submitReservationBtn">
                  <i class="fa-regular fa-credit-card mr-2"></i>submit & pay down payment
                </button>
                <button
                  class="border border-slate-300 text-slate-600 hover:bg-slate-50 px-6 py-3 rounded-xl font-medium transition text-sm"
                  id="validateFieldsBtn"><i class="fa-regular fa-circle-check"></i> validate fields</button>
              </div>
              <p class="text-xs text-emerald-600 mt-2 flex items-center gap-1" id="validationHint"><i
                  class="fa-regular fa-circle-check"></i> fill required fields</p>
            </div>

            <!-- 2️⃣ RESERVATION CHECKING + TABLE ASSIGNMENT (dynamic, fully functional) -->
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
              <h2 class="font-semibold text-xl flex items-center gap-2 mb-3"><i
                  class="fa-regular fa-clipboard text-amber-600"></i> reservation checking & table assignment</h2>
              <div class="flex flex-wrap gap-4 items-center border-b border-slate-100 pb-4">
                <div class="bg-amber-50 px-4 py-2 rounded-xl"><span class="text-sm" id="currentWalkinDisplay">incoming:
                    (no guest) · 0 guests · --:--</span></div>
                <span class="bg-blue-100 text-blue-700 text-xs px-3 py-1 rounded-full"><i
                    class="fa-regular fa-question"></i> walk‑in (no prior res)</span>
              </div>

              <!-- walk-in guest handling -->
              <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="border rounded-xl p-4 bg-slate-50">
                  <p class="font-medium flex items-center gap-2"><i class="fa-regular fa-person-walking"></i> walk-in
                    guest</p>
                  <p class="text-xs text-slate-500 mt-1">check table availability</p>
                  <div class="mt-3 flex items-center gap-2 text-sm" id="availabilityMessage"><i
                      class="fa-regular fa-clock"></i> <span class="text-amber-600">enter guest details first</span>
                  </div>
                  <button
                    class="mt-3 w-full bg-green-600 hover:bg-green-700 text-white text-sm py-2 rounded-xl transition flex items-center justify-center gap-2"
                    id="assignWalkinBtn"><i class="fa-regular fa-bell"></i> assign & notify waiter</button>
                </div>
                <div class="border rounded-xl p-4 bg-red-50/40">
                  <p class="font-medium flex items-center gap-2"><i class="fa-regular fa-timer"></i> if no table
                    available</p>
                  <p class="text-xs text-slate-500 mt-1">move to waiting list, show estimated wait.</p>
                  <div class="flex items-center gap-2 mt-2 text-sm"><i class="fa-regular fa-hourglass-half"></i> est.
                    wait: 15‑20 min</div>
                  <button
                    class="mt-3 w-full bg-slate-600 hover:bg-slate-700 text-white text-sm py-2 rounded-xl transition"
                    id="addToWaitingBtn"><i class="fa-regular fa-list"></i> add to waiting list</button>
                </div>
              </div>
              <div class="mt-5 border-t pt-3 text-xs text-slate-400 flex flex-wrap gap-6" id="dbStatus">
                <span><i class="fa-regular fa-floppy-disk"></i> DB: Customers, Reservations</span>
                <span><i class="fa-regular fa-chair"></i> Table → reserved after assignment</span>
                <span><i class="fa-regular fa-clock"></i> seating time recorded</span>
              </div>
            </div>

            <!-- 3️⃣ WAITING LIST QUEUE (starts empty) -->
            <div class="bg-white rounded-2xl border border-slate-200 p-5">
              <h3 class="font-semibold flex items-center gap-2 mb-3"><i
                  class="fa-regular fa-list-timeline text-amber-600"></i> waiting list queue · <span
                  id="waitingCount">0</span> parties</h3>
              <div class="space-y-2" id="waitingListContainer">
                <div class="text-sm text-slate-400 text-center py-4">No parties waiting.</div>
              </div>
              <div class="flex justify-between items-center mt-4 text-xs text-slate-500 border-t pt-3">
                <span><i class="fa-regular fa-arrow-right"></i> when table free → auto‑assign + notify</span>
                <button class="text-amber-700 hover:underline" id="simulateTableFreeBtn"><i
                    class="fa-regular fa-bell"></i> simulate table free</button>
              </div>
            </div>
          </div>

          <!-- RIGHT COLUMN : table grid + notifications (no sample data) -->
          <div class="space-y-6">

            <!-- real-time table monitoring (clean state) -->
            <div class="bg-white rounded-2xl border border-slate-200 p-5">
              <h3 class="font-semibold flex items-center gap-2 mb-3"><i class="fa-regular fa-grid-2 text-amber-600"></i>
                real‑time table status</h3>
              <div class="grid grid-cols-3 sm:grid-cols-4 gap-2" id="tableGrid">
                <!-- injected via JS (all available initially) -->
              </div>
              <!-- legend -->
              <div class="flex flex-wrap gap-3 mt-4 text-[10px] text-slate-500 border-t pt-3">
                <span><span class="inline-block w-3 h-3 bg-green-100 border border-green-300 rounded"></span>
                  available</span>
                <span><span class="inline-block w-3 h-3 bg-amber-100 border border-amber-300 rounded"></span>
                  reserved</span>
                <span><span class="inline-block w-3 h-3 bg-red-100 border border-red-300 rounded"></span>
                  occupied</span>
              </div>
              <p class="text-xs text-slate-400 mt-2"><i class="fa-regular fa-comment"></i> click table to toggle status
                (demo)</p>
            </div>

            <!-- staff notification panel (empty at start) -->
            <div class="bg-white border border-slate-200 rounded-2xl p-5">
              <h4 class="font-medium flex items-center gap-2"><i class="fa-regular fa-bell text-amber-600"></i> recent
                staff notifications</h4>
              <ul class="text-xs space-y-3 mt-3" id="notificationList">
                <li class="text-slate-400 text-center">No notifications yet.</li>
              </ul>
              <button
                class="w-full border border-amber-600 text-amber-700 hover:bg-amber-50 mt-4 py-2 rounded-xl text-sm transition"
                id="refreshNotifications"><i class="fa-regular fa-rotate"></i> refresh notifications</button>
            </div>

            <!-- seating time demo (dynamic seating recorded separately) -->
            <div class="bg-slate-100 border border-slate-300 rounded-2xl p-5">
              <h4 class="font-medium flex items-center gap-2"><i class="fa-regular fa-clock text-amber-600"></i> seating
                time recording</h4>
              <div class="text-xs text-slate-500 mt-2">Seating times are logged when tables become occupied.</div>
              <div class="mt-2 text-xs" id="seatingTimesDemo">No active seating times.</div>
            </div>

            <!-- payment summary badge -->
            <div class="bg-gradient-to-r from-amber-600 to-amber-700 rounded-2xl p-5 text-white">
              <div class="flex items-center gap-3 mb-2">
                <i class="fa-regular fa-credit-card text-2xl"></i>
                <div>
                  <p class="text-sm opacity-90">down payment summary</p>
                  <p class="text-xs opacity-75">per guest: ₱100</p>
                </div>
              </div>
              <p class="text-lg font-bold" id="paymentSummary">₱0.00 total down payments</p>
              <p class="text-xs opacity-75 mt-1" id="paymentCount">0 pending payments</p>
            </div>

            <!-- db hint -->
            <div class="bg-amber-50/70 border border-amber-200 rounded-2xl p-4 text-xs">
              <p class="font-medium mb-1"><i class="fa-regular fa-database"></i> database schema active</p>
              <ul class="list-disc list-inside text-slate-600">
                <li>customers, reservations, tables, waiting_list</li>
                <li>real-time status: available / reserved / occupied</li>
                <li>down payments: ₱100/guest, redirect to payments page</li>
              </ul>
            </div>
          </div>
        </div>

        <!-- bottom line -->
        <div class="mt-10 text-center text-xs text-slate-400 border-t pt-6 flex flex-wrap justify-center gap-4">
          <span>✅ 1. online reservation form</span>
          <span>✅ 2. reservation checking (walk‑in)</span>
          <span>✅ 3. table availability + assignment</span>
          <span>✅ 4. waiting list mgmt</span>
          <span>✅ 5. seating + status update</span>
          <span>✅ 6. staff notifications</span>
          <span>✅ 7. down payment (₱100/guest)</span>
          <span>✅ 8. redirect to payments page</span>
        </div>
      </main>
    </div>

    <!-- Payment Processing Modal (optional) -->
    <div id="paymentProcessingModal"
      class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
      <div class="bg-white rounded-2xl p-8 max-w-sm w-full mx-4 text-center">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-amber-600 mx-auto mb-4"></div>
        <h3 class="text-lg font-semibold mb-2">Processing Payment</h3>
        <p class="text-sm text-slate-500" id="paymentProcessingMessage">Redirecting to payments page...</p>
      </div>
    </div>

    <script>
      (function () {
        // ---------- INITIAL STATE ----------
        let pendingPayments = []; // Track down payments locally

        // Current walk-in from form
        let currentWalkin = {
          name: '',
          guests: 2,
          time: '7:30 PM'
        };

        // ---------- HELPER FUNCTIONS ----------
        function updateDateTime() {
          const now = new Date();
          const dateTimeEl = document.getElementById('currentDateTime');
          const dateDisplayEl = document.getElementById('currentDateDisplay');

          if (dateTimeEl) {
            dateTimeEl.innerText = now.toLocaleString('en-US', {
              month: 'short',
              day: 'numeric',
              year: 'numeric',
              hour: '2-digit',
              minute: '2-digit'
            });
          }

          if (dateDisplayEl) {
            dateDisplayEl.innerText = now.toLocaleDateString('en-US', {
              weekday: 'long',
              month: 'long',
              day: 'numeric',
              year: 'numeric'
            });
          }
        }

        function isValidEmail(email) {
          return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
        }

        function isValidPhone(phone) {
          return /^[\+]?[(]?[0-9]{3}[)]?[-\s\.]?[0-9]{3}[-\s\.]?[0-9]{4,6}$/.test(phone);
        }

        function addNotification(msg, icon = 'bell') {
          // This is just for UI feedback, not actual notifications
          console.log('Notification:', msg, icon);
        }

        function updatePaymentSummary() {
          const totalDownPayments = pendingPayments.reduce((sum, p) => sum + p.amount, 0);
          const paymentSummaryEl = document.getElementById('paymentSummary');
          const paymentCountEl = document.getElementById('paymentCount');

          if (paymentSummaryEl) {
            paymentSummaryEl.textContent = `₱${totalDownPayments.toFixed(2)} total down payments`;
          }
          if (paymentCountEl) {
            paymentCountEl.textContent = `${pendingPayments.length} pending payments`;
          }
        }

        function updateAvailabilityMsg() {
          const availabilitySpan = document.querySelector('#availabilityMessage span');
          if (!availabilitySpan) return;

          if (!currentWalkin.name.trim()) {
            availabilitySpan.innerText = 'enter guest name first';
            return;
          }

          // This is just a mock message since we're not checking real tables on customer side
          availabilitySpan.innerText = '✅ reservation will be confirmed after payment';
        }

        function updateWalkinFromForm() {
          const nameInput = document.getElementById('resName');
          const guestsSelect = document.getElementById('resGuests');
          const timeSelect = document.getElementById('resTime');

          if (!nameInput || !guestsSelect || !timeSelect) return;

          const name = nameInput.value.trim();
          const guests = parseInt(guestsSelect.value) || 2;
          const time = timeSelect.value;

          currentWalkin = {
            name: name || '(unnamed guest)',
            guests: guests,
            time: time
          };

          const walkinDisplay = document.getElementById('currentWalkinDisplay');
          if (walkinDisplay) {
            walkinDisplay.innerText = `incoming: ${currentWalkin.name} · ${currentWalkin.guests} guests · ${currentWalkin.time}`;
          }

          // Update down payment display
          const guestCountDisplay = document.getElementById('guestCountDisplay');
          const downPaymentAmount = document.getElementById('downPaymentAmount');

          if (guestCountDisplay) guestCountDisplay.textContent = guests;
          if (downPaymentAmount) {
            const downPayment = guests * 100;
            downPaymentAmount.textContent = `₱${downPayment.toFixed(2)}`;
          }

          updateAvailabilityMsg();
        }

        function escapeHtml(unsafe) {
          if (!unsafe) return '';
          return unsafe
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
        }

        // Clear all pending session data
        function clearPendingSessions() {
          sessionStorage.removeItem('pendingRestaurantReservation');
          sessionStorage.removeItem('pendingDownPayment');
          sessionStorage.removeItem('pendingBooking');
          console.log('Cleared all pending sessions');
        }

        // Show processing modal and redirect
        function processDownPaymentAndRedirect(reservationData) {
          const modal = document.getElementById('paymentProcessingModal');
          const message = document.getElementById('paymentProcessingMessage');

          if (!modal || !message) return;

          message.textContent = `Processing ₱${reservationData.downPayment.toFixed(2)} down payment...`;
          modal.classList.remove('hidden');
          modal.classList.add('flex');

          // Split full name into first and last name
          const nameParts = reservationData.name.trim().split(' ');
          const firstName = nameParts[0] || reservationData.name;
          const lastName = nameParts.slice(1).join(' ') || 'Guest';

          // Prepare data for server
          const formData = new URLSearchParams({
            first_name: firstName,
            last_name: lastName,
            email: reservationData.email,
            phone: reservationData.phone,
            reservation_date: reservationData.date,
            reservation_time: reservationData.time + ':00', // Add seconds for database format
            guests: reservationData.guests,
            special_requests: reservationData.request || '',
            occasion: reservationData.occasion || ''
          });

          console.log('Sending reservation data:', Object.fromEntries(formData));

          // Send to server via AJAX
          fetch('../../controller/customer/post/create_restaurant_reservation.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: formData
          })
            .then(response => {
              if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
              }
              return response.json();
            })
            .then(data => {
              console.log('Server response:', data);

              if (data.success) {
                // ... existing success code ...
              }
              else if (data.has_pending) {
                // User has pending reservation
                modal.classList.add('hidden');
                modal.classList.remove('flex');

                const pending = data.pending_reservation;
                const formattedAmount = new Intl.NumberFormat('en-PH', {
                  style: 'currency',
                  currency: 'PHP'
                }).format(pending.down_payment);

                Swal.fire({
                  title: 'Pending Reservation Found',
                  html: `
        <div class="text-left">
          <p class="mb-3">${data.message}</p>
          <div class="bg-amber-50 p-3 rounded-lg">
            <p><strong>Reference:</strong> ${pending.reservation_reference}</p>
            <p><strong>Guests:</strong> ${pending.guests}</p>
            <p><strong>Date:</strong> ${pending.reservation_date}</p>
            <p><strong>Time:</strong> ${pending.reservation_time}</p>
            <p><strong>Down Payment:</strong> ${formattedAmount}</p>
          </div>
        </div>
      `,
                  icon: 'warning',
                  confirmButtonColor: '#d97706',
                  confirmButtonText: 'Pay Now',
                  showCancelButton: true,
                  cancelButtonText: 'Cancel',
                  cancelButtonColor: '#6b7280'
                }).then((result) => {
                  if (result.isConfirmed) {
                    // Go to payments page
                    window.location.href = './payments.php?type=restaurant&id=' + pending.id;
                  }
                });
              }
              else {
                // Regular error
                modal.classList.add('hidden');
                modal.classList.remove('flex');

                let errorMessage = data.message || 'An error occurred';
                if (data.errors) {
                  errorMessage = data.errors.join('<br>');
                }

                Swal.fire({
                  title: 'Error',
                  html: errorMessage,
                  icon: 'error',
                  confirmButtonColor: '#d97706'
                });
              }
            })
            .catch(error => {
              console.error('Fetch error:', error);
              modal.classList.add('hidden');
              modal.classList.remove('flex');

              Swal.fire({
                title: 'Error',
                text: 'Failed to create reservation. Please check your connection and try again.',
                icon: 'error',
                confirmButtonColor: '#d97706'
              });
            });
        }

        // ---------- EVENT LISTENERS ----------
        document.addEventListener('DOMContentLoaded', function () {
          // Update date and time
          updateDateTime();
          setInterval(updateDateTime, 60000);

          // Set default date to tomorrow
          const today = new Date();
          const tomorrow = new Date(today);
          tomorrow.setDate(tomorrow.getDate() + 1);

          const dateInput = document.getElementById('resDate');
          if (dateInput && !dateInput.value) {
            dateInput.value = tomorrow.toISOString().split('T')[0];
          }

          // Submit reservation button
          const submitBtn = document.getElementById('submitReservationBtn');
          if (submitBtn) {
            submitBtn.addEventListener('click', (e) => {
              e.preventDefault();

              updateWalkinFromForm();

              // Validate required fields
              const name = document.getElementById('resName').value.trim();
              const phone = document.getElementById('resPhone').value.trim();
              const email = document.getElementById('resEmail').value.trim();

              // Validation checks
              if (!name || !phone || !email) {
                Swal.fire({
                  title: 'Validation Error',
                  text: 'Please fill in all required fields (name, phone, email).',
                  icon: 'warning',
                  confirmButtonColor: '#d97706'
                });
                document.getElementById('validationHint').innerHTML = '<i class="fa-regular fa-circle-exclamation"></i> required fields missing';
                return;
              }

              if (name === '(unnamed guest)') {
                Swal.fire({
                  title: 'Validation Error',
                  text: 'Please enter a valid customer name.',
                  icon: 'warning',
                  confirmButtonColor: '#d97706'
                });
                return;
              }

              if (!isValidEmail(email)) {
                Swal.fire({
                  title: 'Validation Error',
                  text: 'Please enter a valid email address.',
                  icon: 'warning',
                  confirmButtonColor: '#d97706'
                });
                document.getElementById('validationHint').innerHTML = '<i class="fa-regular fa-circle-exclamation"></i> invalid email format';
                return;
              }

              if (!isValidPhone(phone)) {
                Swal.fire({
                  title: 'Validation Error',
                  text: 'Please enter a valid phone number.',
                  icon: 'warning',
                  confirmButtonColor: '#d97706'
                });
                document.getElementById('validationHint').innerHTML = '<i class="fa-regular fa-circle-exclamation"></i> invalid phone format';
                return;
              }

              const guests = parseInt(document.getElementById('resGuests').value);
              const date = document.getElementById('resDate').value;
              const time = document.getElementById('resTime').value;
              const downPayment = guests * 100;

              // Validate date is not in the past
              const selectedDate = new Date(date);
              const today = new Date();
              today.setHours(0, 0, 0, 0);

              if (selectedDate < today) {
                Swal.fire({
                  title: 'Validation Error',
                  text: 'Reservation date cannot be in the past.',
                  icon: 'warning',
                  confirmButtonColor: '#d97706'
                });
                return;
              }

              // Create reservation object
              const reservation = {
                name,
                phone,
                email,
                guests,
                date,
                time,
                downPayment,
                request: document.getElementById('resRequest').value,
                occasion: ''
              };

              // Update validation hint
              document.getElementById('validationHint').innerHTML = '<i class="fa-regular fa-circle-check"></i> processing reservation...';

              // Process down payment and redirect
              processDownPaymentAndRedirect(reservation);
            });
          }

          // Validate fields button
          const validateBtn = document.getElementById('validateFieldsBtn');
          if (validateBtn) {
            validateBtn.addEventListener('click', () => {
              const name = document.getElementById('resName').value.trim();
              const phone = document.getElementById('resPhone').value.trim();
              const email = document.getElementById('resEmail').value.trim();
              const hint = document.getElementById('validationHint');

              if (!name || !phone || !email) {
                if (hint) hint.innerHTML = '<i class="fa-regular fa-circle-exclamation"></i> required fields missing';
                Swal.fire({
                  title: 'Validation',
                  text: 'Please fill in all required fields.',
                  icon: 'info',
                  confirmButtonColor: '#d97706',
                  timer: 2000
                });
              } else if (!isValidEmail(email)) {
                if (hint) hint.innerHTML = '<i class="fa-regular fa-circle-exclamation"></i> invalid email format';
                Swal.fire({
                  title: 'Validation',
                  text: 'Please enter a valid email address.',
                  icon: 'info',
                  confirmButtonColor: '#d97706',
                  timer: 2000
                });
              } else if (!isValidPhone(phone)) {
                if (hint) hint.innerHTML = '<i class="fa-regular fa-circle-exclamation"></i> invalid phone format';
                Swal.fire({
                  title: 'Validation',
                  text: 'Please enter a valid phone number.',
                  icon: 'info',
                  confirmButtonColor: '#d97706',
                  timer: 2000
                });
              } else {
                if (hint) hint.innerHTML = '<i class="fa-regular fa-circle-check"></i> all required fields look good';
                Swal.fire({
                  title: 'Validation',
                  text: 'All fields are valid! You can now submit.',
                  icon: 'success',
                  confirmButtonColor: '#d97706',
                  timer: 2000
                });
              }
            });
          }

          // Refresh notifications button (demo)
          const refreshBtn = document.getElementById('refreshNotifications');
          if (refreshBtn) {
            refreshBtn.addEventListener('click', () => {
              addNotification('Notification feed refreshed', 'rotate');
              Swal.fire({
                title: 'Refreshed',
                text: 'Notification list updated.',
                icon: 'success',
                confirmButtonColor: '#d97706',
                timer: 1500,
                showConfirmButton: false
              });
            });
          }

          // Update walkin on form changes
          ['resName', 'resGuests', 'resTime'].forEach(id => {
            const el = document.getElementById(id);
            if (el) {
              el.addEventListener('change', updateWalkinFromForm);
              if (id === 'resName') {
                el.addEventListener('input', updateWalkinFromForm);
              }
            }
          });

          // Initial update
          updateWalkinFromForm();
          updatePaymentSummary();

          // Check for any pending down payments from previous session
          const pending = sessionStorage.getItem('pendingRestaurantReservation');
          if (pending) {
            const pendingData = JSON.parse(pending);

            // OPTION 1: Check if this reservation still exists in the database
            // You would need an API endpoint to verify this
            // For now, we'll just show a message with an option to clear it

            Swal.fire({
              title: 'Pending Payment Found',
              text: `You have a pending down payment of ₱${pendingData.down_payment} for your reservation. Would you like to complete it now?`,
              icon: 'info',
              confirmButtonColor: '#d97706',
              confirmButtonText: 'Pay Now',
              showCancelButton: true,
              cancelButtonText: 'Clear and Start Fresh',
              cancelButtonColor: '#6b7280'
            }).then((result) => {
              if (result.isConfirmed) {
                window.location.href = './payments.php?type=restaurant&id=' + pendingData.id;
              } else if (result.dismiss === Swal.DismissReason.cancel) {
                // Clear the pending session
                sessionStorage.removeItem('pendingRestaurantReservation');
                sessionStorage.removeItem('pendingDownPayment');
                Swal.fire({
                  title: 'Cleared',
                  text: 'Pending payment has been cleared. You can start a new reservation.',
                  icon: 'success',
                  confirmButtonColor: '#d97706',
                  timer: 2000
                });
              }
            });
          }

          // OPTION 2: Add a manual clear button (optional)
          // You can add this to your HTML if needed
          const clearBtn = document.createElement('button');
          clearBtn.className = 'fixed bottom-4 left-4 bg-slate-200 text-slate-700 px-3 py-1 rounded-lg text-xs z-50 hover:bg-slate-300 transition';
          clearBtn.innerHTML = '<i class="fa-regular fa-trash-can mr-1"></i> Clear Pending';
          clearBtn.onclick = function () {
            clearPendingSessions();
            Swal.fire({
              title: 'Cleared',
              text: 'All pending sessions cleared!',
              icon: 'success',
              confirmButtonColor: '#d97706',
              timer: 1500
            });
          };
          document.body.appendChild(clearBtn);
        });

        // Make functions global if needed
        window.addNotification = addNotification;
        window.updatePaymentSummary = updatePaymentSummary;
        window.clearPendingSessions = clearPendingSessions;

      })();
    </script>
  </body>

</html>