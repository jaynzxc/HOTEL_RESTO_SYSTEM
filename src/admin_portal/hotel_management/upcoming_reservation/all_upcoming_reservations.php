<?php
/**
 * View - Admin All Upcoming Reservations
 */
require_once '../../../../controller/admin/get/all_reservations.php';
$current_page = 'all_upcoming_reservations';
?>
<!DOCTYPE html>
<html lang="en">

  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin · All Upcoming Reservations</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Tailwind via CDN + Font Awesome 6 -->
    <script src="https://cdn.tailwindcss.com"></script>
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

      /* Status badges na pwedeng i-click */
      .status-badge {
        cursor: pointer;
        transition: all 0.2s;
      }

      .status-badge:hover {
        opacity: 0.8;
        transform: scale(1.05);
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

    <!-- APP CONTAINER -->
    <div class="min-h-screen flex flex-col lg:flex-row">

      <?php require_once '../../components/admin_nav.php'; ?>


      <!-- ========== MAIN CONTENT: ALL UPCOMING RESERVATIONS ========== -->
      <main class="flex-1 p-5 lg:p-8 overflow-y-auto bg-white">

        <!-- Header with back button -->
        <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
          <div>
            <div class="flex items-center gap-2 text-sm text-slate-500 mb-1">
              <a href="../front_desk_reception.html" class="hover:text-amber-600 transition">
                <i class="fa-regular fa-arrow-left"></i> Back to Front Desk
              </a>
            </div>
            <h1 class="text-2xl lg:text-3xl font-light text-slate-800 flex items-center gap-2">
              <i class="fa-regular fa-rectangle-list text-amber-600"></i> All Upcoming Reservations
            </h1>
            <p class="text-sm text-slate-500 mt-0.5">complete list of all future reservations</p>
          </div>
          <div class="flex gap-2">
            <span class="bg-white border rounded-full px-4 py-2 flex items-center gap-2 shadow-sm text-sm">
              <i class="fa-regular fa-calendar text-slate-400"></i> <span id="currentDate"></span>
            </span>
          </div>
        </div>

        <!-- Summary Cards - Pwedeng i-click -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
          <div
            class="bg-white p-4 rounded-xl border border-slate-200 hover:border-amber-300 hover:shadow-md transition cursor-pointer"
            onclick="filterByStatus('all')">
            <div class="flex items-center gap-3">
              <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-700">
                <i class="fa-regular fa-calendar"></i>
              </div>
              <div>
                <p class="text-xs text-slate-500">Total Reservations</p>
                <p class="text-xl font-semibold" id="totalReservations">0</p>
              </div>
            </div>
          </div>
          <div
            class="bg-white p-4 rounded-xl border border-slate-200 hover:border-green-300 hover:shadow-md transition cursor-pointer"
            onclick="filterByStatus('confirmed')">
            <div class="flex items-center gap-3">
              <div class="h-10 w-10 rounded-full bg-green-100 flex items-center justify-center text-green-700">
                <i class="fa-regular fa-circle-check"></i>
              </div>
              <div>
                <p class="text-xs text-slate-500">Confirmed</p>
                <p class="text-xl font-semibold" id="confirmedCount">0</p>
              </div>
            </div>
          </div>
          <div
            class="bg-white p-4 rounded-xl border border-slate-200 hover:border-yellow-300 hover:shadow-md transition cursor-pointer"
            onclick="filterByStatus('pending')">
            <div class="flex items-center gap-3">
              <div class="h-10 w-10 rounded-full bg-yellow-100 flex items-center justify-center text-yellow-700">
                <i class="fa-regular fa-clock"></i>
              </div>
              <div>
                <p class="text-xs text-slate-500">Pending</p>
                <p class="text-xl font-semibold" id="pendingCount">0</p>
              </div>
            </div>
          </div>
          <div
            class="bg-white p-4 rounded-xl border border-slate-200 hover:border-red-300 hover:shadow-md transition cursor-pointer"
            onclick="filterByStatus('cancelled')">
            <div class="flex items-center gap-3">
              <div class="h-10 w-10 rounded-full bg-red-100 flex items-center justify-center text-red-700">
                <i class="fa-regular fa-ban"></i>
              </div>
              <div>
                <p class="text-xs text-slate-500">Cancelled</p>
                <p class="text-xl font-semibold" id="cancelledCount">0</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Filter tabs -->
        <div class="flex flex-wrap gap-2 mb-6">
          <button class="filter-btn px-4 py-2 rounded-full text-sm border bg-amber-50 border-amber-300 text-amber-800"
            data-filter="all">All</button>
          <button
            class="filter-btn px-4 py-2 rounded-full text-sm border border-slate-200 text-slate-600 hover:bg-amber-50"
            data-filter="confirmed">Confirmed</button>
          <button
            class="filter-btn px-4 py-2 rounded-full text-sm border border-slate-200 text-slate-600 hover:bg-amber-50"
            data-filter="pending">Pending</button>
          <button
            class="filter-btn px-4 py-2 rounded-full text-sm border border-slate-200 text-slate-600 hover:bg-amber-50"
            data-filter="cancelled">Cancelled</button>
        </div>

        <!-- Search and Filter Bar -->
        <div class="flex flex-wrap gap-3 mb-6">
          <div class="flex-1 min-w-50">
            <div class="relative">
              <i class="fa-regular fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
              <input type="text" id="searchInput" placeholder="Search by guest name or room..."
                class="w-full border border-slate-200 rounded-xl py-2 pl-10 pr-4 focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
            </div>
          </div>
          <select id="monthFilter"
            class="border border-slate-200 rounded-xl px-4 py-2 focus:ring-2 focus:ring-amber-500">
            <option value="all">All Months</option>
            <option value="5">May 2025</option>
            <option value="6">June 2025</option>
            <option value="7">July 2025</option>
            <option value="8">August 2025</option>
          </select>
          <button onclick="exportReservations()"
            class="border border-amber-600 text-amber-700 px-4 py-2 rounded-xl hover:bg-amber-50 transition flex items-center gap-2">
            <i class="fa-regular fa-file-excel"></i> Export
          </button>
        </div>

        <!-- Reservations Table -->
        <div class="bg-white rounded-2xl border border-slate-200 p-5">
          <div class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead class="text-slate-400 text-xs border-b">
                <tr>
                  <td class="pb-3">Guest Name</td>
                  <td class="pb-3">Room Type</td>
                  <td class="pb-3">Check-in</td>
                  <td class="pb-3">Check-out</td>
                  <td class="pb-3">Nights</td>
                  <td class="pb-3">Guests</td>
                  <td class="pb-3">Status</td>
                  <td class="pb-3">Action</td>
                </tr>
              </thead>
              <tbody class="divide-y" id="reservationsTableBody">
                <!-- populated by JavaScript -->
              </tbody>
            </table>
          </div>
          <div id="emptyMessage" class="text-center py-8 text-slate-400 hidden">No reservations found</div>

          <!-- Pagination -->
          <div class="flex items-center justify-between mt-4 pt-4 border-t">
            <p class="text-sm text-slate-500" id="showingInfo">Showing 0 reservations</p>
            <div class="flex gap-2">
              <button
                class="pagination-btn px-3 py-1 border rounded-lg text-sm hover:bg-amber-50 disabled:opacity-50 disabled:cursor-not-allowed"
                id="prevPage" onclick="changePage('prev')">
                <i class="fa-regular fa-chevron-left"></i> Prev
              </button>
              <span class="px-3 py-1 bg-amber-50 text-amber-700 border border-amber-200 rounded-lg text-sm"
                id="currentPage">1</span>
              <button
                class="pagination-btn px-3 py-1 border rounded-lg text-sm hover:bg-amber-50 disabled:opacity-50 disabled:cursor-not-allowed"
                id="nextPage" onclick="changePage('next')">
                Next <i class="fa-regular fa-chevron-right"></i>
              </button>
            </div>
          </div>
        </div>
      </main>
    </div>

    <script>
      // Pass PHP data to JavaScript
      const reservationsData = <?php echo json_encode($reservations); ?>;
      const stats = <?php echo json_encode($stats); ?>;
      const months = <?php echo json_encode($months); ?>;

      // Global variables
      let currentFilter = '<?php echo $statusFilter; ?>';
      let currentMonth = <?php echo $monthFilter; ?>;
      let currentSearch = '<?php echo $searchFilter; ?>';
      let currentPage = <?php echo $currentPage; ?>;
      const itemsPerPage = 10;
      let filteredData = [...reservationsData];

      // DOM elements
      const tbody = document.getElementById('reservationsTableBody');
      const emptyMsg = document.getElementById('emptyMessage');
      const showingInfo = document.getElementById('showingInfo');
      const searchInput = document.getElementById('searchInput');
      const monthFilter = document.getElementById('monthFilter');
      const currentPageSpan = document.getElementById('currentPage');
      const prevBtn = document.getElementById('prevPage');
      const nextBtn = document.getElementById('nextPage');

      // Initialize
      document.addEventListener('DOMContentLoaded', function () {
        updateDate();
        updateSummary();
        renderTable();
        setupEventListeners();
        populateMonthFilter();
      });

      // Populate month filter from actual data
      function populateMonthFilter() {
        const monthNames = ['January', 'February', 'March', 'April', 'May', 'June',
          'July', 'August', 'September', 'October', 'November', 'December'];

        let options = '<option value="all">All Months</option>';

        if (months && months.length > 0) {
          months.forEach(m => {
            const selected = m.month == currentMonth ? 'selected' : '';
            options += `<option value="${m.month}" ${selected}>${monthNames[m.month - 1]} 2025 (${m.count})</option>`;
          });
        }

        monthFilter.innerHTML = options;
      }

      // Setup event listeners
      function setupEventListeners() {
        searchInput.addEventListener('input', function () {
          currentSearch = this.value;
          currentPage = 1;
          filterData();
        });

        monthFilter.addEventListener('change', function () {
          currentMonth = this.value;
          currentPage = 1;
          filterData();
        });
      }

      // Display current date
      function updateDate() {
        const date = new Date();
        const options = { year: 'numeric', month: 'long', day: 'numeric' };
        document.getElementById('currentDate').textContent = date.toLocaleDateString('en-US', options);
      }

      // Show toast notification
      function showToast(message, type = 'info') {
        const toast = document.getElementById('toast');
        const toastMessage = document.getElementById('toastMessage');
        const toastTime = document.getElementById('toastTime');
        const now = new Date();

        // Set icon based on type
        const icon = toast.querySelector('.h-8.w-8 i');
        if (icon) {
          icon.className = type === 'success' ? 'fa-regular fa-circle-check' :
            type === 'error' ? 'fa-regular fa-circle-exclamation' :
              'fa-regular fa-bell';
        }

        toastMessage.textContent = message;
        toastTime.textContent = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
        toast.classList.remove('hidden');

        setTimeout(() => { toast.classList.add('show'); }, 10);
        setTimeout(() => {
          toast.classList.remove('show');
          setTimeout(() => { toast.classList.add('hidden'); }, 300);
        }, 3000);
      }

      // Format date to readable format
      function formatDate(dateString) {
        const options = { month: 'short', day: 'numeric', year: 'numeric' };
        return new Date(dateString).toLocaleDateString('en-US', options);
      }

      // Update summary cards
      function updateSummary() {
        document.getElementById('totalReservations').textContent = stats?.total || 0;
        document.getElementById('confirmedCount').textContent = stats?.confirmed || 0;
        document.getElementById('pendingCount').textContent = stats?.pending || 0;
        document.getElementById('cancelledCount').textContent = stats?.cancelled || 0;
      }

      // Filter by status (from cards)
      window.filterByStatus = function (status) {
        const url = new URL(window.location);
        if (status !== 'all') {
          url.searchParams.set('status', status);
        } else {
          url.searchParams.delete('status');
        }
        url.searchParams.set('page', '1');
        window.location.href = url.toString();
      };

      // Filter and search function
      function filterData() {
        let data = [...reservationsData];

        // Filter by status
        if (currentFilter !== 'all') {
          data = data.filter(r => {
            const rStatus = r.status || 'pending';
            return rStatus === currentFilter;
          });
        }

        // Filter by month
        if (currentMonth !== 'all' && currentMonth !== '0') {
          data = data.filter(r => {
            const checkInMonth = new Date(r.checkIn).getMonth() + 1;
            return checkInMonth === parseInt(currentMonth);
          });
        }

        // Search by guest name or room type
        if (currentSearch) {
          const searchTerm = currentSearch.toLowerCase();
          data = data.filter(r =>
            (r.guest && r.guest.toLowerCase().includes(searchTerm)) ||
            (r.roomType && r.roomType.toLowerCase().includes(searchTerm))
          );
        }

        filteredData = data;

        // Update URL with search
        const url = new URL(window.location);
        if (currentSearch) {
          url.searchParams.set('search', currentSearch);
        } else {
          url.searchParams.delete('search');
        }
        if (currentMonth !== 'all' && currentMonth !== '0') {
          url.searchParams.set('month', currentMonth);
        } else {
          url.searchParams.delete('month');
        }
        window.history.replaceState({}, '', url);

        renderTable();
      }

      // Render table with pagination
      function renderTable() {
        const totalPages = Math.ceil(filteredData.length / itemsPerPage) || 1;
        const start = (currentPage - 1) * itemsPerPage;
        const end = start + itemsPerPage;
        const paginatedData = filteredData.slice(start, end);

        let html = '';

        if (paginatedData.length === 0) {
          tbody.innerHTML = '<tr><td colspan="8" class="py-8 text-center text-slate-400">No reservations found</td></tr>';
          emptyMsg.classList.add('hidden');
          showingInfo.textContent = 'Showing 0 reservations';
          currentPageSpan.textContent = currentPage;
          prevBtn.disabled = currentPage === 1;
          nextBtn.disabled = true;
          return;
        }

        paginatedData.forEach(reservation => {
          const displayStatus = reservation.status || 'pending';
          const statusColor = {
            'confirmed': 'bg-green-100 text-green-700',
            'pending': 'bg-yellow-100 text-yellow-700',
            'cancelled': 'bg-red-100 text-red-700',
            'checked_in': 'bg-blue-100 text-blue-700',
            'completed': 'bg-slate-100 text-slate-700',
            '': 'bg-yellow-100 text-yellow-700'
          }[displayStatus] || 'bg-slate-100 text-slate-700';

          const guests = (reservation.adults || 0) + (reservation.children || 0);

          // Determine which action buttons to show
          let actionButtons = '';

          if (displayStatus === 'pending') {
            actionButtons = `
                    <div class="flex gap-2">
                        <button onclick="confirmReservation(${reservation.id})" class="text-xs text-green-600 border border-green-600 px-2 py-1 rounded hover:bg-green-50 transition">
                            <i class="fa-regular fa-circle-check mr-1"></i>confirm
                        </button>
                        <button onclick="viewReservationDetails(${reservation.id})" class="text-xs text-blue-600 border border-blue-600 px-2 py-1 rounded hover:bg-blue-50 transition">
                            <i class="fa-regular fa-eye mr-1"></i>view
                        </button>
                    </div>
                `;
          } else if (displayStatus === 'confirmed') {
            actionButtons = `
                    <div class="flex gap-2">
                        <button onclick="viewReservationDetails(${reservation.id})" class="text-xs text-blue-600 border border-blue-600 px-2 py-1 rounded hover:bg-blue-50 transition">
                            <i class="fa-regular fa-eye mr-1"></i>view
                        </button>
                        <button onclick="cancelReservation(${reservation.id})" class="text-xs text-red-600 border border-red-600 px-2 py-1 rounded hover:bg-red-50 transition">
                            <i class="fa-regular fa-ban mr-1"></i>cancel
                        </button>
                    </div>
                `;
          } else {
            actionButtons = `
                    <div class="flex gap-2">
                        <button onclick="viewReservationDetails(${reservation.id})" class="text-xs text-blue-600 border border-blue-600 px-2 py-1 rounded hover:bg-blue-50 transition">
                            <i class="fa-regular fa-eye mr-1"></i>view
                        </button>
                    </div>
                `;
          }

          html += `<tr class="hover:bg-slate-50 transition">
                <td class="py-3 px-2 font-medium">
                    <div class="flex items-center gap-2">
                        ${reservation.guest}
                        ${reservation.vip ? '<span class="text-yellow-600 text-xs"><i class="fa-solid fa-crown"></i></span>' : ''}
                    </div>
                </td>
                <td class="py-3 px-2">${reservation.roomType || 'N/A'}</td>
                <td class="py-3 px-2">${formatDate(reservation.checkIn)}</td>
                <td class="py-3 px-2">${formatDate(reservation.checkOut)}</td>
                <td class="py-3 px-2 text-center">${reservation.nights || 1}</td>
                <td class="py-3 px-2 text-center">${guests}</td>
                <td class="py-3 px-2">
                    <span class="${statusColor} px-2 py-1 rounded-full text-xs font-medium cursor-pointer status-badge" onclick="filterByStatus('${displayStatus}')">
                        ${displayStatus}
                    </span>
                </td>
                <td class="py-3 px-2">${actionButtons}</td>
            </tr>`;
        });

        tbody.innerHTML = html;
        emptyMsg.classList.add('hidden');

        // Update pagination info
        showingInfo.textContent = `Showing ${start + 1}-${Math.min(end, filteredData.length)} of ${filteredData.length} reservations`;
        currentPageSpan.textContent = currentPage;

        // Update pagination buttons
        prevBtn.disabled = currentPage === 1;
        nextBtn.disabled = currentPage === totalPages || totalPages === 0;
      }

      // Change page
      window.changePage = function (direction) {
        const totalPages = Math.ceil(filteredData.length / itemsPerPage) || 1;

        if (direction === 'prev' && currentPage > 1) {
          currentPage--;
        } else if (direction === 'next' && currentPage < totalPages) {
          currentPage++;
        }

        const url = new URL(window.location);
        url.searchParams.set('page', currentPage);
        window.location.href = url.toString();
      };

      // ========== RESERVATION ACTION FUNCTIONS ==========

      // Confirm reservation - Check balance first (like customer portal)
      window.confirmReservation = function (id) {
        Swal.fire({
          title: 'Confirm Reservation?',
          text: 'Are you sure you want to confirm this reservation?',
          icon: 'question',
          showCancelButton: true,
          confirmButtonColor: '#d97706',
          cancelButtonColor: '#6b7280',
          confirmButtonText: 'Yes, confirm',
          cancelButtonText: 'Cancel'
        }).then((result) => {
          if (result.isConfirmed) {
            Swal.fire({
              title: 'Checking guest balance...',
              text: 'Please wait',
              allowOutsideClick: false,
              didOpen: () => {
                Swal.showLoading();
              }
            });

            const formData = new FormData();
            formData.append('action', 'confirm_reservation');
            formData.append('booking_id', id);

            fetch('../../../../controller/admin/post/reservation_actions.php', {
              method: 'POST',
              body: formData
            })
              .then(response => response.json())
              .then(data => {
                if (data.success) {
                  // Success - no outstanding balance
                  let message = data.message;
                  if (data.points_awarded > 0) {
                    message = `Reservation confirmed successfully! ✨ Guest earned ${data.points_awarded} loyalty points.`;
                  }

                  Swal.fire({
                    title: 'Confirmed!',
                    text: message,
                    icon: 'success',
                    confirmButtonColor: '#d97706'
                  }).then(() => {
                    location.reload();
                  });
                }
                else if (data.requires_action && data.has_outstanding_balance) {
                  // Outstanding balance detected - show warning with options
                  showBalanceWarning(data);
                } else {
                  // Other error
                  Swal.fire({
                    title: 'Error',
                    text: data.message || 'An error occurred',
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
        });
      };

      // Show balance warning with options (like customer portal style)
      function showBalanceWarning(data) {
        const balanceFormatted = new Intl.NumberFormat('en-PH', {
          style: 'currency',
          currency: 'PHP',
          minimumFractionDigits: 2
        }).format(data.balance_amount);

        Swal.fire({
          title: 'Outstanding Balance Detected',
          html: `
                <div class="text-left">
                    <div class="bg-red-50 border-l-4 border-red-500 rounded-lg p-4 mb-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fa-regular fa-circle-exclamation text-red-600 text-xl"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-red-700 font-medium">This guest has an unpaid balance</p>
                                <p class="text-sm text-red-600 mt-1">${data.warning}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-amber-50 rounded-lg p-4 mb-4 border border-amber-200">
                        <p class="font-medium text-amber-800 mb-2">Booking Details</p>
                        <table class="w-full text-sm">
                            <tr>
                                <td class="py-1 text-slate-600">Guest:</td>
                                <td class="py-1 font-medium">${data.booking.guest_name}</td>
                            </tr>
                            <tr>
                                <td class="py-1 text-slate-600">Booking Ref:</td>
                                <td class="py-1 font-medium">${data.booking.booking_ref}</td>
                            </tr>
                            <tr>
                                <td class="py-1 text-slate-600">Check-in:</td>
                                <td class="py-1 font-medium">${new Date(data.booking.check_in).toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' })}</td>
                            </tr>
                            <tr>
                                <td class="py-1 text-slate-600">Check-out:</td>
                                <td class="py-1 font-medium">${new Date(data.booking.check_out).toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' })}</td>
                            </tr>
                            <tr>
                                <td class="py-1 text-slate-600">Total Amount:</td>
                                <td class="py-1 font-medium text-amber-700">${new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' }).format(data.booking.total_amount)}</td>
                            </tr>
                        </table>
                    </div>
                    
                    <p class="text-sm text-slate-600 mb-2">What would you like to do?</p>
                </div>
            `,
          icon: 'warning',
          showCancelButton: true,
          showDenyButton: true,
          confirmButtonColor: '#d97706',
          cancelButtonColor: '#dc2626',
          denyButtonColor: '#6b7280',
          confirmButtonText: '<i class="fa-regular fa-check mr-1"></i> Confirm Anyway',
          cancelButtonText: '<i class="fa-regular fa-ban mr-1"></i> Cancel Reservation',
          denyButtonText: '<i class="fa-regular fa-arrow-left mr-1"></i> Go Back',
          reverseButtons: true
        }).then((result) => {
          if (result.isConfirmed) {
            // User chose to proceed with confirmation despite balance
            forceConfirmReservation(data.booking.id, data.balance_amount);
          } else if (result.dismiss === Swal.DismissReason.cancel) {
            // User chose to cancel the reservation
            showCancelWithBalanceDialog(data.booking.id, data.balance_amount);
          }
          // If deny (Go Back), just close the dialog
        });
      }

      // Show cancel with balance dialog (like customer portal)
      function showCancelWithBalanceDialog(bookingId, balanceAmount) {
        Swal.fire({
          title: 'Cancel Reservation Due to Balance?',
          html: `
                <div class="text-left">
                    <p class="mb-3">Are you sure you want to cancel this reservation due to outstanding balance?</p>
                    <div class="bg-red-50 border border-red-200 rounded-lg p-3 mb-3">
                        <p class="text-sm text-red-700">
                            <i class="fa-regular fa-circle-exclamation mr-1"></i>
                            The guest will be notified that their reservation was cancelled due to unpaid balance of ${new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' }).format(balanceAmount)}.
                        </p>
                    </div>
                    <div class="mt-3">
                        <label class="block text-sm font-medium text-slate-700 mb-1 text-left">Reason (optional):</label>
                        <textarea id="cancelReason" class="w-full border rounded-lg p-2 text-sm" rows="2" placeholder="Enter reason for cancellation..."></textarea>
                    </div>
                </div>
            `,
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#dc2626',
          cancelButtonColor: '#6b7280',
          confirmButtonText: 'Yes, cancel reservation',
          cancelButtonText: 'No, keep it',
          preConfirm: () => {
            return {
              reason: document.getElementById('cancelReason').value
            };
          }
        }).then((result) => {
          if (result.isConfirmed) {
            cancelReservationWithBalance(bookingId, balanceAmount, result.value?.reason || '');
          }
        });
      }

      // Force confirm reservation despite balance
      function forceConfirmReservation(id, balanceAmount) {
        Swal.fire({
          title: 'Processing...',
          text: 'Please wait',
          allowOutsideClick: false,
          didOpen: () => {
            Swal.showLoading();
          }
        });

        const formData = new FormData();
        formData.append('action', 'confirm_reservation_force');
        formData.append('booking_id', id);
        formData.append('ignore_balance', '1');

        fetch('../../../../controller/admin/post/reservation_actions.php', {
          method: 'POST',
          body: formData
        })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              const balanceFormatted = new Intl.NumberFormat('en-PH', {
                style: 'currency',
                currency: 'PHP',
                minimumFractionDigits: 2
              }).format(balanceAmount);

              let pointsMessage = '';
              if (data.points_awarded > 0) {
                pointsMessage = `<p class="text-sm text-green-600 mt-2">✨ Guest earned ${data.points_awarded} loyalty points!</p>`;
              }

              Swal.fire({
                title: 'Reservation Confirmed!',
                html: `
                        <div>
                            <p class="mb-3">${data.message}</p>
                            <div class="bg-yellow-50 border-l-4 border-yellow-400 rounded-lg p-4 text-left">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <i class="fa-regular fa-triangle-exclamation text-yellow-600"></i>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-yellow-700 font-medium">Outstanding Balance Reminder</p>
                                        <p class="text-sm text-yellow-600 mt-1">Guest still has an outstanding balance of ${balanceFormatted}</p>
                                        <p class="text-xs text-yellow-500 mt-2">Please advise guest to settle before check-in.</p>
                                    </div>
                                </div>
                            </div>
                            ${pointsMessage}
                        </div>
                    `,
                icon: 'warning',
                confirmButtonColor: '#d97706',
                confirmButtonText: 'OK'
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

      // Cancel reservation with balance notice
      function cancelReservationWithBalance(id, balanceAmount, reason) {
        Swal.fire({
          title: 'Processing...',
          text: 'Please wait',
          allowOutsideClick: false,
          didOpen: () => {
            Swal.showLoading();
          }
        });

        const formData = new FormData();
        formData.append('action', 'cancel_reservation_with_balance');
        formData.append('booking_id', id);
        formData.append('balance_amount', balanceAmount);
        formData.append('reason', reason);

        fetch('../../../../controller/admin/post/reservation_actions.php', {
          method: 'POST',
          body: formData
        })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              let message = data.message;
              if (data.points_deducted > 0) {
                message += ` ${data.points_deducted} points deducted from guest account.`;
              }

              Swal.fire({
                title: 'Cancelled!',
                text: message,
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

      // Cancel reservation
      window.cancelReservation = function (id) {
        Swal.fire({
          title: 'Cancel Reservation?',
          input: 'textarea',
          inputLabel: 'Reason for cancellation (optional)',
          inputPlaceholder: 'Enter reason...',
          showCancelButton: true,
          confirmButtonColor: '#d97706',
          cancelButtonColor: '#6b7280',
          confirmButtonText: 'Yes, cancel',
          cancelButtonText: 'No, keep it',
          icon: 'warning',
          inputValidator: (value) => {
            return null; // Allow empty reason
          }
        }).then((result) => {
          if (result.isConfirmed) {
            Swal.fire({
              title: 'Processing...',
              text: 'Please wait',
              allowOutsideClick: false,
              didOpen: () => {
                Swal.showLoading();
              }
            });

            const formData = new FormData();
            formData.append('action', 'cancel_reservation');
            formData.append('booking_id', id);
            formData.append('reason', result.value || '');

            fetch('../../../../controller/admin/post/reservation_actions.php', {
              method: 'POST',
              body: formData
            })
              .then(response => response.json())
              .then(data => {
                if (data.success) {
                  let message = data.message;
                  if (data.points_deducted > 0) {
                    message += ` ${data.points_deducted} points deducted from guest account.`;
                  }

                  Swal.fire({
                    title: 'Cancelled!',
                    text: message,
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
        });
      };

      // View reservation details
      window.viewReservationDetails = function (id) {
        Swal.fire({
          title: 'Loading...',
          text: 'Please wait',
          allowOutsideClick: false,
          didOpen: () => {
            Swal.showLoading();
          }
        });

        const formData = new FormData();
        formData.append('action', 'get_reservation_details');
        formData.append('booking_id', id);

        fetch('../../../../controller/admin/post/reservation_actions.php', {
          method: 'POST',
          body: formData
        })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              const b = data.booking;
              const guests = (b.adults || 0) + (b.children || 0);
              const statusColor = {
                'confirmed': 'bg-green-100 text-green-700',
                'pending': 'bg-yellow-100 text-yellow-700',
                'cancelled': 'bg-red-100 text-red-700',
                'checked_in': 'bg-blue-100 text-blue-700',
                'completed': 'bg-slate-100 text-slate-700',
                '': 'bg-yellow-100 text-yellow-700'
              }[b.status || 'pending'];

              const hasBalance = data.outstanding_balance > 0;
              const balanceFormatted = new Intl.NumberFormat('en-PH', {
                style: 'currency',
                currency: 'PHP',
                minimumFractionDigits: 2
              }).format(data.outstanding_balance);

              const pointsToEarn = data.points_to_earn > 0 ?
                `<p class="text-xs text-green-600 mt-1">✨ Will earn ${data.points_to_earn} points when confirmed</p>` : '';

              Swal.fire({
                title: 'Reservation Details',
                html: `
                        <div class="text-left max-h-96 overflow-y-auto px-2">
                            <!-- Outstanding Balance Alert (if any) -->
                            ${hasBalance ? `
                            <div class="bg-red-50 border-l-4 border-red-500 rounded-lg p-3 mb-4">
                                <div class="flex items-start">
                                    <i class="fa-regular fa-circle-exclamation text-red-600 mr-2 mt-0.5"></i>
                                    <div>
                                        <p class="text-red-700 font-medium text-sm">Outstanding Balance</p>
                                        <p class="text-red-600 font-bold">${balanceFormatted}</p>
                                    </div>
                                </div>
                            </div>
                            ` : ''}
                            
                            <!-- Guest Information -->
                            <div class="bg-amber-50 p-3 rounded-lg mb-4">
                                <p class="font-semibold text-lg">${b.guest_first_name} ${b.guest_last_name}</p>
                                <p class="text-sm text-slate-600">${b.guest_email || 'No email'}</p>
                                <p class="text-sm text-slate-600">${b.guest_phone || 'No phone'}</p>
                                ${b.member_tier ? `<p class="text-xs text-amber-600 mt-1"><i class="fa-regular fa-gem mr-1"></i>${b.member_tier} member (${b.loyalty_points || 0} pts)</p>` : ''}
                                ${pointsToEarn}
                            </div>
                            
                            <!-- Booking Details -->
                            <div class="grid grid-cols-2 gap-3 mb-4">
                                <div class="border rounded-lg p-2">
                                    <p class="text-xs text-slate-500">Booking #</p>
                                    <p class="font-medium text-sm">${b.booking_reference}</p>
                                </div>
                                <div class="border rounded-lg p-2">
                                    <p class="text-xs text-slate-500">Status</p>
                                    <p class="font-medium text-sm"><span class="${statusColor} px-2 py-0.5 rounded-full">${b.status || 'pending'}</span></p>
                                </div>
                                <div class="border rounded-lg p-2">
                                    <p class="text-xs text-slate-500">Room</p>
                                    <p class="font-medium text-sm">${b.room_name} ${b.room_assigned ? `(Room ${b.room_assigned})` : ''}</p>
                                </div>
                                <div class="border rounded-lg p-2">
                                    <p class="text-xs text-slate-500">Guests</p>
                                    <p class="font-medium text-sm">${guests} (${b.adults} adults, ${b.children} children)</p>
                                </div>
                            </div>
                            
                            <!-- Dates -->
                            <div class="grid grid-cols-2 gap-3 mb-4">
                                <div class="border rounded-lg p-2">
                                    <p class="text-xs text-slate-500">Check-in</p>
                                    <p class="font-medium">${new Date(b.check_in).toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric', year: 'numeric' })}</p>
                                    ${b.check_in_time ? `<p class="text-xs text-slate-400">${b.check_in_time}</p>` : ''}
                                </div>
                                <div class="border rounded-lg p-2">
                                    <p class="text-xs text-slate-500">Check-out</p>
                                    <p class="font-medium">${new Date(b.check_out).toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric', year: 'numeric' })}</p>
                                    ${b.check_out_time ? `<p class="text-xs text-slate-400">${b.check_out_time}</p>` : ''}
                                </div>
                                <div class="border rounded-lg p-2">
                                    <p class="text-xs text-slate-500">Nights</p>
                                    <p class="font-medium">${b.nights || 1}</p>
                                </div>
                                <div class="border rounded-lg p-2">
                                    <p class="text-xs text-slate-500">Created</p>
                                    <p class="font-medium text-sm">${new Date(b.created_at).toLocaleDateString()}</p>
                                </div>
                            </div>
                            
                            <!-- Payment -->
                            <div class="grid grid-cols-2 gap-3 mb-4">
                                <div class="border rounded-lg p-2">
                                    <p class="text-xs text-slate-500">Subtotal</p>
                                    <p class="font-medium">₱${Number(b.subtotal || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</p>
                                </div>
                                <div class="border rounded-lg p-2">
                                    <p class="text-xs text-slate-500">Tax (12%)</p>
                                    <p class="font-medium">₱${Number(b.tax || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</p>
                                </div>
                                <div class="border rounded-lg p-2 col-span-2">
                                    <p class="text-xs text-slate-500">Total Amount</p>
                                    <p class="font-bold text-amber-700">₱${Number(b.total_amount).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</p>
                                </div>
                                <div class="border rounded-lg p-2">
                                    <p class="text-xs text-slate-500">Payment Status</p>
                                    <p class="font-medium ${b.payment_status === 'paid' ? 'text-green-600' : 'text-amber-600'}">${b.payment_status || 'unpaid'}</p>
                                </div>
                            </div>
                            
                            ${b.special_requests ? `
                            <div class="mb-3">
                                <p class="text-xs text-slate-500 mb-1">Special Requests</p>
                                <p class="text-sm bg-slate-50 p-2 rounded-lg">${b.special_requests}</p>
                            </div>
                            ` : ''}
                            
                            ${b.preferences ? `
                            <div class="mb-3">
                                <p class="text-xs text-slate-500 mb-1">Guest Preferences</p>
                                <p class="text-sm bg-slate-50 p-2 rounded-lg">${b.preferences}</p>
                            </div>
                            ` : ''}
                            
                            ${b.allergies ? `
                            <div class="mb-3">
                                <p class="text-xs text-slate-500 mb-1">Allergies/Restrictions</p>
                                <p class="text-sm bg-red-50 p-2 rounded-lg text-red-700">${b.allergies}</p>
                            </div>
                            ` : ''}
                        </div>
                    `,
                confirmButtonColor: '#d97706',
                confirmButtonText: 'Close',
                width: '600px'
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
      };

      // Export function
      window.exportReservations = function () {
        Swal.fire({
          title: 'Export Reservations',
          html: `
                <div class="text-left">
                    <p class="mb-3">Choose export format:</p>
                    <select id="exportFormat" class="w-full border rounded-lg p-2 mb-3">
                        <option value="csv">CSV (Excel)</option>
                    </select>
                    <p class="text-xs text-slate-500">Current filters will be applied to export.</p>
                </div>
            `,
          showCancelButton: true,
          confirmButtonColor: '#d97706',
          cancelButtonColor: '#6b7280',
          confirmButtonText: 'Export',
          preConfirm: () => {
            return document.getElementById('exportFormat').value;
          }
        }).then((result) => {
          if (result.isConfirmed) {
            showToast(`📊 Exporting reservations as ${result.value.toUpperCase()}...`);

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '../../../../controller/admin/post/reservation_actions.php';
            form.innerHTML = `
                    <input type="hidden" name="action" value="export_reservations">
                    <input type="hidden" name="format" value="${result.value}">
                    <input type="hidden" name="status" value="${currentFilter}">
                    <input type="hidden" name="month" value="${currentMonth}">
                `;
            document.body.appendChild(form);
            form.submit();
            document.body.removeChild(form);
          }
        });
      };

      // Filter button listeners
      document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.addEventListener('click', function () {
          const filter = this.dataset.filter;
          filterByStatus(filter);
        });
      });

      // Initialize filter buttons based on current filter
      document.querySelectorAll('.filter-btn').forEach(btn => {
        if (btn.dataset.filter === currentFilter) {
          btn.classList.add('bg-amber-50', 'border-amber-300', 'text-amber-800');
          btn.classList.remove('border-slate-200', 'text-slate-600');
        } else {
          btn.classList.remove('bg-amber-50', 'border-amber-300', 'text-amber-800');
          btn.classList.add('border-slate-200', 'text-slate-600');
        }
      });
    </script>
  </body>

</html>