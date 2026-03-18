<?php
require_once '../../../../controller/admin/get/todays_departures.php';

$current_page = 'departure_today';

?>

<!DOCTYPE html>
<html lang="en">

  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin · Today's Departures</title>
    <!-- Tailwind via CDN + Font Awesome 6 -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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
    </style>
  </head>

  <body class="bg-white font-sans antialiased">

    <div class="min-h-screen flex flex-col lg:flex-row">
      <!-- Same sidebar as arrivals page -->
      <?php require_once '../../components/admin_nav.php'; ?>

      <!-- Main Content -->
      <main class="flex-1 p-5 lg:p-8 overflow-y-auto bg-white">
        <!-- Header with back button -->
        <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
          <div class="flex items-center gap-4">
            <a href="/src/admin_portal/hotel_management/reservation_&_booking.php"
              class="text-amber-600 hover:text-amber-700">
              <i class="fa-solid fa-arrow-left text-xl"></i>
            </a>
            <div>
              <h1 class="text-2xl lg:text-3xl font-light text-slate-800">Today's Departures</h1>
              <p class="text-sm text-slate-500 mt-0.5">guests checking out today · <span id="currentDate"></span></p>
            </div>
          </div>
          <div class="flex gap-3 text-sm">
            <span class="bg-white border rounded-full px-4 py-2 flex items-center gap-2 shadow-sm">
              <i class="fa-regular fa-clock text-slate-400"></i>
              <span id="currentTime"></span>
            </span>
            <span class="bg-white border rounded-full px-4 py-2 shadow-sm cursor-pointer hover:bg-slate-50"
              id="refreshBtn">
              <i class="fa-solid fa-rotate-right"></i>
            </span>
          </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-6">
          <div class="bg-orange-50 border border-orange-200 rounded-2xl p-5">
            <p class="text-sm text-orange-700 font-medium">Total Departures</p>
            <p class="text-3xl font-bold text-orange-700" id="totalDepartures">0</p>
          </div>
          <div class="bg-yellow-50 border border-yellow-200 rounded-2xl p-5">
            <p class="text-sm text-yellow-700 font-medium">Pending Check-out</p>
            <p class="text-3xl font-bold text-yellow-700" id="pendingDepartures">0</p>
          </div>
          <div class="bg-green-50 border border-green-200 rounded-2xl p-5">
            <p class="text-sm text-green-700 font-medium">Checked-out</p>
            <p class="text-3xl font-bold text-green-700" id="checkedOut">0</p>
          </div>
          <div class="bg-blue-50 border border-blue-200 rounded-2xl p-5">
            <p class="text-sm text-blue-700 font-medium">Rooms to Clean</p>
            <p class="text-3xl font-bold text-blue-700" id="roomsToClean">0</p>
          </div>
        </div>

        <!-- Quick Actions -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">
          <div class="bg-linear-to-r from-blue-50 to-indigo-50 rounded-2xl p-5 border border-blue-200">
            <div class="flex items-center justify-between">
              <div>
                <h3 class="font-semibold text-lg mb-1">Express Check-out</h3>
                <p class="text-sm text-slate-600">Process multiple check-outs quickly</p>
              </div>
              <button onclick="openBulkCheckout()"
                class="bg-blue-600 text-white px-5 py-2.5 rounded-xl hover:bg-blue-700">
                <i class="fa-regular fa-bolt mr-2"></i>Start
              </button>
            </div>
          </div>
          <div class="bg-linear-to-r from-amber-50 to-orange-50 rounded-2xl p-5 border border-amber-200">
            <div class="flex items-center justify-between">
              <div>
                <h3 class="font-semibold text-lg mb-1">Housekeeping Alert</h3>
                <p class="text-sm text-slate-600"><span id="roomsToCleanAlert">0</span> rooms will need cleaning</p>
              </div>
              <button onclick="notifyHousekeeping()"
                class="bg-amber-600 text-white px-5 py-2.5 rounded-xl hover:bg-amber-700">
                <i class="fa-regular fa-broom mr-2"></i>Notify
              </button>
            </div>
          </div>
        </div>

        <!-- Filter and Search -->
        <div
          class="bg-white rounded-2xl border border-slate-200 p-4 mb-6 flex flex-wrap gap-3 items-center justify-between">
          <div class="flex gap-2 flex-wrap">
            <button class="bg-amber-600 text-white px-4 py-2 rounded-xl text-sm filter-btn" data-filter="all">all
              departures</button>
            <button class="border border-slate-200 px-4 py-2 rounded-xl text-sm hover:bg-slate-50 filter-btn"
              data-filter="pending">pending</button>
            <button class="border border-slate-200 px-4 py-2 rounded-xl text-sm hover:bg-slate-50 filter-btn"
              data-filter="checked-out">checked-out</button>
            <button class="border border-slate-200 px-4 py-2 rounded-xl text-sm hover:bg-slate-50 filter-btn"
              data-filter="late">late check-out</button>
            <button class="border border-slate-200 px-4 py-2 rounded-xl text-sm hover:bg-slate-50 filter-btn"
              data-filter="express">express</button>
          </div>
          <div class="relative">
            <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
            <input type="text" id="searchInput" placeholder="search by name or room #..."
              class="border border-slate-200 rounded-xl pl-9 pr-4 py-2 text-sm w-64 focus:ring-1 focus:ring-amber-500 outline-none">
          </div>
        </div>

        <!-- Departure Timeline -->
        <div class="mb-8">
          <h2 class="font-semibold text-lg mb-4">Check-out Timeline</h2>
          <div class="relative">
            <!-- Timeline line -->
            <div class="absolute left-8 top-0 bottom-0 w-0.5 bg-slate-200"></div>

            <div class="space-y-6" id="timelineContainer"></div>
          </div>
        </div>

        <!-- Departures Table -->
        <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
          <div class="p-4 border-b border-slate-200 flex justify-between items-center">
            <h2 class="font-semibold">All Departures</h2>
            <div class="flex gap-2">
              <button class="text-xs text-amber-600 hover:underline" onclick="exportDepartures()">export</button>
              <button class="text-xs text-amber-600 hover:underline" onclick="printDepartures()">print</button>
            </div>
          </div>
          <div class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead class="bg-slate-50 text-slate-500 text-xs border-b">
                <tr>
                  <td class="p-4">Room</td>
                  <td class="p-4">Guest</td>
                  <td class="p-4">Booking #</td>
                  <td class="p-4">Check-out Time</td>
                  <td class="p-4">Status</td>
                  <td class="p-4">Bill Amount</td>
                  <td class="p-4">Payment</td>
                  <td class="p-4">Actions</td>
                </tr>
              </thead>
              <tbody class="divide-y" id="tableBody"></tbody>
            </table>
          </div>
          <div class="p-4 border-t border-slate-200 flex items-center justify-between">
            <span class="text-xs text-slate-500" id="paginationInfo"></span>
            <div class="flex gap-2" id="paginationControls"></div>
          </div>
        </div>

        <!-- Express Check-out Panel (hidden by default) -->
        <div id="expressPanel"
          class="fixed right-0 top-0 h-full w-96 bg-white shadow-2xl transform transition-transform translate-x-full z-50">
          <div class="p-6">
            <div class="flex justify-between items-center mb-6">
              <h3 class="text-xl font-semibold">Express Check-out</h3>
              <button onclick="closeExpressPanel()" class="text-slate-400 hover:text-slate-600">
                <i class="fa-solid fa-times"></i>
              </button>
            </div>
            <div class="space-y-4" id="expressList"></div>
            <button onclick="processBulkCheckout()"
              class="w-full bg-green-600 text-white py-3 rounded-xl mt-6 hover:bg-green-700">
              Process Selected (0)
            </button>
          </div>
        </div>
      </main>
    </div>

    <!-- Check-out Modal -->
    <div id="checkoutModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
      <div class="bg-white rounded-2xl p-6 w-full max-w-md">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-xl font-semibold">Process Check-out</h3>
          <button onclick="closeCheckoutModal()" class="text-slate-400 hover:text-slate-600">
            <i class="fa-solid fa-times"></i>
          </button>
        </div>
        <form id="checkoutForm" class="space-y-4">
          <input type="hidden" id="checkoutBookingNo">
          <div class="bg-slate-50 p-4 rounded-xl">
            <p class="text-sm" id="checkoutGuestInfo"></p>
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Final Bill</label>
            <div class="border border-slate-200 rounded-xl p-4">
              <div class="flex justify-between mb-2">
                <span>Room Charges</span>
                <span id="roomCharges">$450.00</span>
              </div>
              <div class="flex justify-between mb-2">
                <span>Additional Services</span>
                <span id="additionalCharges">$85.50</span>
              </div>
              <div class="flex justify-between font-semibold pt-2 border-t">
                <span>Total</span>
                <span id="totalBill">$535.50</span>
              </div>
            </div>
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Payment Method</label>
            <select id="paymentMethod" class="w-full border border-slate-200 rounded-xl px-4 py-2">
              <option value="cash">Cash</option>
              <option value="card">Credit Card</option>
              <option value="mobile">Mobile Payment</option>
              <option value="invoice">Invoice</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Room Condition</label>
            <select id="roomCondition" class="w-full border border-slate-200 rounded-xl px-4 py-2">
              <option value="good">Good - No issues</option>
              <option value="minor">Minor issues</option>
              <option value="maintenance">Needs maintenance</option>
              <option value="damage">Damage reported</option>
            </select>
          </div>
          <div class="flex gap-3 mt-4">
            <button type="button" onclick="processCheckout()"
              class="bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded-xl text-sm flex-1">Confirm
              Check-out</button>
            <button type="button" onclick="closeCheckoutModal()"
              class="border border-slate-300 px-5 py-2 rounded-xl text-sm">Cancel</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Toast notification -->
    <div id="toast"
      class="fixed bottom-4 right-4 bg-slate-800 text-white px-4 py-2 rounded-lg shadow-lg transform transition-transform translate-y-20 opacity-0">
    </div>
    <script>
      // Pass PHP data to JavaScript
      const departures = <?php echo json_encode($departures); ?>;
      let currentFilter = '<?php echo $statusFilter; ?>';
      let currentSearch = '<?php echo $searchFilter; ?>';
      let currentPage = 1;
      const rowsPerPage = 5;
      let selectedForExpress = [];

      // Initialize
      document.addEventListener('DOMContentLoaded', function () {
        updateDateTime();
        renderAll();
        setupEventListeners();
      });

      function updateDateTime() {
        const now = new Date();
        const options = { year: 'numeric', month: 'long', day: 'numeric' };
        document.getElementById('currentDate').textContent = now.toLocaleDateString('en-US', options);
        document.getElementById('currentTime').textContent = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
      }

      function renderAll() {
        renderTimeline();
        renderTable();
        updateSummaryCards();
      }

      function renderTimeline() {
        const timeline = document.getElementById('timelineContainer');
        timeline.innerHTML = '';

        if (departures.length === 0) {
          timeline.innerHTML = '<div class="text-center py-8 text-slate-400">No departures scheduled for today</div>';
          return;
        }

        const filteredDepartures = getFilteredDepartures();

        // Sort by check-out time
        filteredDepartures.sort((a, b) => {
          const timeA = a.checkOutTime || '11:00:00';
          const timeB = b.checkOutTime || '11:00:00';
          return timeA.localeCompare(timeB);
        });

        filteredDepartures.forEach((dep) => {
          const checkOutTime = dep.checkOutTimeFormatted || '11:00 AM';
          const isLate = dep.lateCheckout;
          const item = document.createElement('div');
          item.className = 'relative pl-16 mb-4';

          let statusDotClass = 'bg-blue-500';
          if (dep.status === 'completed') {
            statusDotClass = 'bg-green-500';
          } else if (isLate) {
            statusDotClass = 'bg-orange-500';
          }

          item.innerHTML = `
                <div class="absolute left-6 top-2 w-4 h-4 rounded-full ${statusDotClass}"></div>
                <div class="bg-white border rounded-xl p-4 hover:shadow-md transition">
                    <div class="flex justify-between items-start">
                        <div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="font-medium">Room ${dep.room || 'TBD'}</span>
                                <span class="text-sm text-slate-600">· ${dep.guest}</span>
                                ${dep.vip ? '<span class="text-xs bg-yellow-100 text-yellow-700 px-2 py-0.5 rounded-full"><i class="fa-solid fa-crown"></i> VIP</span>' : ''}
                                ${dep.express ? '<span class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded-full">express ready</span>' : ''}
                            </div>
                            <p class="text-xs text-slate-500 mt-1">Booking: ${dep.bookingNo} · ${dep.roomType}</p>
                        </div>
                        <span class="text-sm font-medium">${checkOutTime}</span>
                    </div>
                    <div class="flex justify-between items-center mt-3">
                        <span class="text-sm">Bill: ₱${dep.totalBill.toFixed(2)}</span>
                        <div class="flex gap-2">
                            ${dep.status === 'checked_in' ?
              `<button onclick="openCheckoutModal(${dep.id})" class="bg-green-600 text-white text-xs px-3 py-1.5 rounded-lg hover:bg-green-700">check out</button>` :
              dep.status === 'completed' ?
                `<span class="text-green-600 text-xs"><i class="fa-regular fa-check-circle"></i> completed</span>` :
                `<span class="text-slate-400 text-xs">${dep.status}</span>`
            }
                            <button onclick="viewGuestDetails(${dep.id})" class="border border-slate-200 text-xs px-3 py-1.5 rounded-lg hover:bg-slate-50">details</button>
                        </div>
                    </div>
                </div>
            `;
          timeline.appendChild(item);
        });
      }

      function renderTable() {
        const filteredDepartures = getFilteredDepartures();
        const totalPages = Math.ceil(filteredDepartures.length / rowsPerPage);
        const start = (currentPage - 1) * rowsPerPage;
        const end = start + rowsPerPage;
        const pageDepartures = filteredDepartures.slice(start, end);

        const tbody = document.getElementById('tableBody');
        tbody.innerHTML = '';

        if (pageDepartures.length === 0) {
          tbody.innerHTML = '<tr><td colspan="8" class="p-8 text-center text-slate-400">No departures found</td></tr>';
          return;
        }

        pageDepartures.forEach(dep => {
          const row = document.createElement('tr');
          const checkOutTime = dep.checkOutTimeFormatted || '11:00 AM';

          const statusColors = {
            'checked_in': 'bg-yellow-100 text-yellow-700',
            'completed': 'bg-green-100 text-green-700',
            'pending': 'bg-slate-100 text-slate-700',
            'confirmed': 'bg-blue-100 text-blue-700'
          };

          row.innerHTML = `
                <td class="p-4 font-medium">${dep.room || '—'}</td>
                <td class="p-4">
                    ${dep.guest}
                    ${dep.vip ? '<span class="ml-2 text-yellow-600 text-xs"><i class="fa-solid fa-crown"></i></span>' : ''}
                </td>
                <td class="p-4">${dep.bookingNo}</td>
                <td class="p-4">${checkOutTime}</td>
                <td class="p-4"><span class="${statusColors[dep.status] || 'bg-slate-100 text-slate-700'} px-2 py-0.5 rounded-full text-xs">${dep.status}</span></td>
                <td class="p-4">₱${dep.totalBill.toFixed(2)}</td>
                <td class="p-4">
                    <span class="${dep.total_balance == 0 ? 'text-green-600' : 'text-amber-600'} text-xs">
                        ${dep.total_balance == 0 ? 'paid' : 'due: ₱' + dep.total_balance.toFixed(2)}
                    </span>
                </td>
                <td class="p-4">
                    ${dep.status === 'checked_in' ?
              `<button onclick="openCheckoutModal(${dep.id})" class="text-green-600 hover:underline text-xs mr-2">check out</button>` :
              ''
            }
                    <button onclick="viewGuestDetails(${dep.id})" class="text-amber-600 hover:underline text-xs mr-2">view</button>
                    ${dep.status === 'checked_in' && dep.total_balance == 0 ?
              `<button onclick="addToExpress(${dep.id})" class="text-purple-600 hover:underline text-xs">express</button>` :
              ''
            }
                </td>
            `;
          tbody.appendChild(row);
        });

        // Update pagination
        document.getElementById('paginationInfo').textContent =
          `Showing ${start + 1}-${Math.min(end, filteredDepartures.length)} of ${filteredDepartures.length} departures`;

        const paginationControls = document.getElementById('paginationControls');
        paginationControls.innerHTML = '';

        if (totalPages > 1) {
          for (let i = 1; i <= totalPages; i++) {
            paginationControls.innerHTML += `
                    <button class="${i === currentPage ? 'bg-amber-600 text-white' : 'border border-slate-200'} px-3 py-1 rounded-lg text-sm page-btn" data-page="${i}">${i}</button>
                `;
          }
        }

        document.querySelectorAll('.page-btn').forEach(btn => {
          btn.addEventListener('click', function () {
            currentPage = parseInt(this.dataset.page);
            renderTable();
          });
        });
      }

      function getFilteredDepartures() {
        return departures.filter(dep => {
          // Status filter
          if (currentFilter !== 'all') {
            if (currentFilter === 'late') {
              if (!dep.lateCheckout) return false;
            } else if (currentFilter === 'checked-out') {
              if (dep.status !== 'completed') return false;
            } else if (currentFilter === 'pending') {
              if (dep.status !== 'checked_in') return false;
            } else if (currentFilter === 'express') {
              if (!dep.express) return false;
            }
          }

          // Search filter
          if (currentSearch) {
            const searchTerm = currentSearch.toLowerCase();
            return (dep.guest && dep.guest.toLowerCase().includes(searchTerm)) ||
              (dep.room && dep.room.toLowerCase().includes(searchTerm)) ||
              (dep.bookingNo && dep.bookingNo.toLowerCase().includes(searchTerm));
          }
          return true;
        });
      }

      function updateSummaryCards() {
        document.getElementById('totalDepartures').textContent = departures.length;
        document.getElementById('pendingDepartures').textContent = departures.filter(d => d.status === 'checked_in').length;
        document.getElementById('checkedOut').textContent = departures.filter(d => d.status === 'completed').length;
        document.getElementById('roomsToClean').textContent = departures.filter(d => d.status === 'checked_in' || d.status === 'completed').length;
        document.getElementById('roomsToCleanAlert').textContent = departures.filter(d => d.status === 'checked_in' || d.status === 'completed').length;
      }

      function setupEventListeners() {
        // Search
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
          searchInput.addEventListener('input', function (e) {
            currentSearch = e.target.value.toLowerCase();
            currentPage = 1;
            renderAll();
          });

          // Add search button
          const searchBtn = document.createElement('button');
          searchBtn.className = 'absolute right-2 top-1/2 -translate-y-1/2 bg-amber-600 text-white px-3 py-1 rounded-lg text-xs hover:bg-amber-700 transition';
          searchBtn.innerHTML = '<i class="fa-solid fa-search"></i>';
          searchBtn.onclick = function () {
            currentSearch = searchInput.value.toLowerCase();
            currentPage = 1;
            renderAll();
          };
          searchInput.parentNode.appendChild(searchBtn);
        }

        // Filters
        document.querySelectorAll('.filter-btn').forEach(btn => {
          btn.addEventListener('click', function () {
            document.querySelectorAll('.filter-btn').forEach(b => {
              b.classList.remove('bg-amber-600', 'text-white');
              b.classList.add('border', 'border-slate-200');
            });
            this.classList.remove('border', 'border-slate-200');
            this.classList.add('bg-amber-600', 'text-white');

            currentFilter = this.dataset.filter;
            currentPage = 1;
            renderAll();
          });
        });

        // Refresh
        const refreshBtn = document.getElementById('refreshBtn');
        if (refreshBtn) {
          refreshBtn.addEventListener('click', function () {
            location.reload();
          });
        }
      }

      // Checkout functions
      function openCheckoutModal(bookingId) {
        const departure = departures.find(d => d.id === bookingId);
        if (departure) {
          document.getElementById('checkoutBookingNo').value = bookingId;
          document.getElementById('checkoutGuestInfo').textContent =
            `Room ${departure.room || 'TBD'} · ${departure.guest} · Check-out: ${departure.checkOutTimeFormatted || '11:00 AM'}`;
          document.getElementById('roomCharges').textContent = `₱${departure.billAmount.toFixed(2)}`;
          document.getElementById('additionalCharges').textContent = `₱${departure.additionalCharges.toFixed(2)}`;
          document.getElementById('totalBill').textContent = `₱${departure.totalBill.toFixed(2)}`;
          document.getElementById('checkoutModal').classList.remove('hidden');
          document.getElementById('checkoutModal').classList.add('flex');
        }
      }

      function closeCheckoutModal() {
        document.getElementById('checkoutModal').classList.add('hidden');
        document.getElementById('checkoutModal').classList.remove('flex');
      }

      function processCheckout() {
        const bookingId = document.getElementById('checkoutBookingNo').value;
        const paymentMethod = document.getElementById('paymentMethod').value;
        const roomCondition = document.getElementById('roomCondition').value;

        Swal.fire({
          title: 'Processing Check-out',
          text: 'Please wait...',
          allowOutsideClick: false,
          didOpen: () => {
            Swal.showLoading();
          }
        });

        const formData = new FormData();
        formData.append('action', 'check_out');
        formData.append('booking_id', bookingId);
        formData.append('payment_method', paymentMethod);
        formData.append('room_condition', roomCondition);

        fetch('../../../../controller/admin/post/departure_actions.php', {
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
          });
      }

      // Express check-out functions
      function openBulkCheckout() {
        const expressDepartures = departures.filter(d => d.status === 'checked_in' && d.total_balance == 0);

        if (expressDepartures.length === 0) {
          showNotification('No guests eligible for express check-out');
          return;
        }

        const expressPanel = document.getElementById('expressPanel');
        expressPanel.classList.remove('translate-x-full');

        const expressList = document.getElementById('expressList');
        expressList.innerHTML = '';

        expressDepartures.forEach(dep => {
          const item = document.createElement('div');
          item.className = 'border rounded-xl p-4 mb-2';
          item.innerHTML = `
                <div class="flex items-center gap-3">
                    <input type="checkbox" value="${dep.id}" class="express-checkbox w-4 h-4 text-amber-600">
                    <div>
                        <p class="font-medium">Room ${dep.room || 'TBD'} · ${dep.guest}</p>
                        <p class="text-xs text-slate-500">Bill: ₱${dep.totalBill.toFixed(2)}</p>
                    </div>
                </div>
            `;
          expressList.appendChild(item);
        });
      }

      function closeExpressPanel() {
        document.getElementById('expressPanel').classList.add('translate-x-full');
      }

      function processBulkCheckout() {
        const checkboxes = document.querySelectorAll('.express-checkbox:checked');
        if (checkboxes.length === 0) {
          showNotification('Please select at least one guest');
          return;
        }

        const bookingIds = Array.from(checkboxes).map(cb => cb.value);

        Swal.fire({
          title: 'Processing Express Check-out',
          text: 'Please wait...',
          allowOutsideClick: false,
          didOpen: () => {
            Swal.showLoading();
          }
        });

        const formData = new FormData();
        formData.append('action', 'bulk_checkout');
        formData.append('booking_ids', JSON.stringify(bookingIds));

        fetch('../../../../controller/admin/post/departure_actions.php', {
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
          });
      }

      function addToExpress(bookingId) {
        const departure = departures.find(d => d.id === bookingId);
        if (departure && !selectedForExpress.includes(bookingId)) {
          selectedForExpress.push(bookingId);
          showNotification(`${departure.guest} added to express check-out`);
        }
      }

      // Other functions
      function notifyHousekeeping() {
        const roomsToClean = departures
          .filter(d => d.status === 'checked_in' || d.status === 'completed')
          .map(d => d.room)
          .filter(r => r);

        if (roomsToClean.length === 0) {
          showNotification('No rooms need cleaning');
          return;
        }

        const formData = new FormData();
        formData.append('action', 'notify_housekeeping');
        formData.append('room_ids', JSON.stringify(roomsToClean));

        fetch('../../../../controller/admin/post/departure_actions.php', {
          method: 'POST',
          body: formData
        })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              showNotification(data.message);
            }
          });
      }

      function viewGuestDetails(bookingId) {
        const departure = departures.find(d => d.id === bookingId);
        if (!departure) return;

        Swal.fire({
          title: 'Guest Details',
          html: `
                <div class="text-left max-h-96 overflow-y-auto">
                    <p><strong>Name:</strong> ${departure.guest}</p>
                    <p><strong>Room:</strong> ${departure.room || 'Not assigned'}</p>
                    <p><strong>Booking #:</strong> ${departure.bookingNo}</p>
                    <p><strong>Room Type:</strong> ${departure.roomType}</p>
                    <p><strong>Check-in:</strong> ${new Date(departure.checkInDate).toLocaleDateString()}</p>
                    <p><strong>Check-out:</strong> ${new Date(departure.checkOutDate).toLocaleDateString()}</p>
                    <p><strong>Check-out Time:</strong> ${departure.checkOutTimeFormatted || '11:00 AM'}</p>
                    <p><strong>Nights:</strong> ${departure.nights}</p>
                    <p><strong>Guests:</strong> ${departure.adults} adults, ${departure.children} children</p>
                    <p><strong>Room Charges:</strong> ₱${departure.billAmount.toFixed(2)}</p>
                    <p><strong>Additional Charges:</strong> ₱${departure.additionalCharges.toFixed(2)}</p>
                    <p><strong>Total Bill:</strong> ₱${departure.totalBill.toFixed(2)}</p>
                    <p><strong>Balance:</strong> ${departure.total_balance == 0 ? 'Paid' : '₱' + departure.total_balance.toFixed(2)}</p>
                    <p><strong>Status:</strong> ${departure.status}</p>
                    ${departure.vip ? '<p><strong>VIP:</strong> Yes</p>' : ''}
                    ${departure.special_requests ? `<p><strong>Special Requests:</strong> ${departure.special_requests}</p>` : ''}
                </div>
            `,
          confirmButtonColor: '#d97706',
          width: '500px'
        });
      }

      function exportDepartures() {
        let csv = 'Room,Guest,Booking #,Check-out Time,Status,Bill Amount,Payment Status\n';
        departures.forEach(d => {
          csv += `${d.room || 'N/A'},${d.guest},${d.bookingNo},${d.checkOutTimeFormatted || '11:00 AM'},${d.status},${d.totalBill.toFixed(2)},${d.total_balance == 0 ? 'Paid' : 'Due'}\n`;
        });

        const blob = new Blob([csv], { type: 'text/csv' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `departures_${new Date().toISOString().split('T')[0]}.csv`;
        a.click();
      }

      function printDepartures() {
        window.print();
      }

      function showNotification(message) {
        const toast = document.getElementById('toast');
        toast.textContent = message;
        toast.classList.remove('translate-y-20', 'opacity-0');
        setTimeout(() => {
          toast.classList.add('translate-y-20', 'opacity-0');
        }, 3000);
      }

      // Auto-refresh every 60 seconds
      setInterval(updateDateTime, 60000);
    </script>
  </body>

</html>