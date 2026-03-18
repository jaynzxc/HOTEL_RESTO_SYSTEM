<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin · Table Reservation</title>
  <!-- Tailwind via CDN + Font Awesome 6 -->
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <style>
    /* exact same dropdown styles from index2.php */
    .transition-side { transition: all 0.2s ease; }
    .dropdown-arrow { transition: transform 0.2s; }
    details[open] .dropdown-arrow { transform: rotate(90deg); }
    details > summary { list-style: none; }
    details summary::-webkit-details-marker { display: none; }
    /* unified toast style for both export and actions */
    .toast-notification {
      position: fixed;
      bottom: 2rem; right: 2rem;
      background: #1e293b;
      color: white;
      padding: 0.75rem 1.5rem;
      border-radius: 999px;
      font-size: 0.875rem;
      font-weight: 500;
      box-shadow: 0 10px 15px -3px rgba(0,0,0,0.3);
      opacity: 0;
      transition: opacity 0.2s ease;
      pointer-events: none;
      z-index: 200;
      display: flex;
      align-items: center;
      gap: 0.5rem;
      border: 1px solid rgba(255,255,255,0.1);
    }
    .toast-notification.show { opacity: 1; }
    .toast-notification i { font-size: 1.1rem; }
    /* style for disabled action buttons */
    .action-btn:disabled {
      opacity: 0.4;
      cursor: not-allowed;
      pointer-events: none;
    }
    /* Pagination active state */
    .pagination-btn.active {
      background-color: #d97706;
      color: white;
      border-color: #d97706;
    }
    .pagination-btn:disabled {
      opacity: 0.4;
      cursor: not-allowed;
    }
  </style>
</head>
<body class="bg-white font-sans antialiased">

  <!-- unified toast notification (replaces both exportToast and actionToast) -->
  <div id="toastNotification" class="toast-notification">
    <i class="fa-regular fa-circle-check"></i>
    <span>Ready</span>
  </div>

  <!-- APP CONTAINER: flex row (sidebar + main) -->
  <div class="min-h-screen flex flex-col lg:flex-row">

    <!-- ========== SIDEBAR (with added toggle functionality for details) ========== -->
          <?php require '../components/admin_nav.php' ?>


    <!-- ========== MAIN CONTENT (TABLE RESERVATION) ========== -->
    <main class="flex-1 p-5 lg:p-8 overflow-y-auto bg-white">

      <!-- header with title and date -->
      <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <div>
          <h1 class="text-2xl lg:text-3xl font-light text-slate-800">Table Reservation</h1>
          <p class="text-sm text-slate-500 mt-0.5">manage restaurant table bookings, walk-ins, and guest preferences</p>
        </div>
        <div class="flex gap-3 text-sm">
          <span class="bg-white border rounded-full px-4 py-2 flex items-center gap-2 shadow-sm"><i class="fa-regular fa-calendar text-slate-400"></i> May 21, 2025</span>
          <span class="bg-white border rounded-full px-4 py-2 shadow-sm"><i class="fa-regular fa-bell"></i></span>
        </div>
      </div>

      <!-- ===== STATS CARDS ===== -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
          <p class="text-xs text-slate-500">Today's reservations</p>
          <p class="text-2xl font-semibold">24</p>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
          <p class="text-xs text-slate-500">Total guests</p>
          <p class="text-2xl font-semibold">86</p>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
          <p class="text-xs text-slate-500">Available tables</p>
          <p class="text-2xl font-semibold text-green-600">8</p>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
          <p class="text-xs text-slate-500">Walk-ins</p>
          <p class="text-2xl font-semibold text-blue-600">5</p>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
          <p class="text-xs text-slate-500">No-shows</p>
          <p class="text-2xl font-semibold text-rose-600">2</p>
        </div>
      </div>

      <!-- ===== FILTER AND SEARCH BAR (with interactive filter buttons) ===== -->
      <div class="bg-white rounded-2xl border border-slate-200 p-4 mb-6 flex flex-wrap gap-3 items-center justify-between">
        <div class="flex gap-2 flex-wrap" id="filterButtonGroup">
          <button id="filterAll" class="bg-amber-600 text-white px-4 py-2 rounded-xl text-sm filter-btn active-filter" data-filter="all">all reservations</button>
          <button id="filterUpcoming" class="border border-slate-200 px-4 py-2 rounded-xl text-sm hover:bg-slate-50 filter-btn" data-filter="upcoming">upcoming</button>
          <button id="filterSeated" class="border border-slate-200 px-4 py-2 rounded-xl text-sm hover:bg-slate-50 filter-btn" data-filter="seated">seated</button>
          <button id="filterWaitlist" class="border border-slate-200 px-4 py-2 rounded-xl text-sm hover:bg-slate-50 filter-btn" data-filter="waitlist">waitlist</button>
          <button id="filterCancelled" class="border border-slate-200 px-4 py-2 rounded-xl text-sm hover:bg-slate-50 filter-btn" data-filter="cancelled">cancelled</button>
        </div>
        <div class="flex gap-2">
          <div class="relative">
            <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
            <input type="text" id="searchInput" placeholder="search guest..." class="border border-slate-200 rounded-xl pl-9 pr-4 py-2 text-sm w-48 focus:ring-1 focus:ring-amber-500 outline-none">
          </div>
        </div>
      </div>

      <!-- ===== TODAY'S RESERVATIONS TABLE WITH PAGINATION ===== -->
      <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden mb-8">
        <div class="p-4 border-b border-slate-200 bg-slate-50 flex items-center justify-between">
          <h2 class="font-semibold flex items-center gap-2"><i class="fa-regular fa-clock text-amber-600"></i> today's reservations (may 21, 2025)</h2>
          <div class="flex gap-2">
            <button id="exportButton" class="text-sm text-amber-700 border border-amber-600 px-3 py-1 rounded-lg hover:bg-amber-50">export</button>
          </div>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full text-sm" id="reservationTable">
            <thead class="text-slate-500 text-xs border-b">
              <tr>
                <td class="p-3">Time</td>
                <td class="p-3">Guest</td>
                <td class="p-3">Table</td>
                <td class="p-3">Pax</td>
                <td class="p-3">Status</td>
                <td class="p-3">Special requests</td>
                <td class="p-3">Actions</td>
              </tr>
            </thead>
            <tbody class="divide-y" id="tableBody">
              <!-- Original static rows - will be replaced by pagination but kept for filter reference -->
            </tbody>
          </table>
        </div>
        
        <!-- Pagination Controls (only this part is modified to include the function) -->
        <div class="p-4 border-t border-slate-200 flex items-center justify-between">
          <span class="text-xs text-slate-500" id="resultCount">Showing 1-4 of 24 reservations today</span>
          <div class="flex gap-2" id="paginationControls">
            <button class="pagination-btn border border-slate-200 px-3 py-1 rounded-lg text-sm" data-page="prev" onclick="changePage('prev')">Previous</button>
            <button class="pagination-btn border border-slate-200 px-3 py-1 rounded-lg text-sm" data-page="1" onclick="changePage(1)">1</button>
            <button class="pagination-btn border border-slate-200 px-3 py-1 rounded-lg text-sm" data-page="2" onclick="changePage(2)">2</button>
            <button class="pagination-btn border border-slate-200 px-3 py-1 rounded-lg text-sm" data-page="3" onclick="changePage(3)">3</button>
            <button class="pagination-btn border border-slate-200 px-3 py-1 rounded-lg text-sm" data-page="4" onclick="changePage(4)">4</button>
            <button class="pagination-btn border border-slate-200 px-3 py-1 rounded-lg text-sm" data-page="5" onclick="changePage(5)">5</button>
            <button class="pagination-btn border border-slate-200 px-3 py-1 rounded-lg text-sm" data-page="6" onclick="changePage(6)">6</button>
            <button class="pagination-btn border border-slate-200 px-3 py-1 rounded-lg text-sm" data-page="next" onclick="changePage('next')">Next</button>
          </div>
        </div>
      </div>

      <!-- ===== BOTTOM: TABLE AVAILABILITY & QUICK ACTIONS ===== -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- table availability grid -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 p-5">
          <h2 class="font-semibold text-lg flex items-center gap-2 mb-3"><i class="fa-regular fa-table text-amber-600"></i> table availability (7:00 PM - 9:00 PM)</h2>
          <div class="grid grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3">
            <!-- table grid unchanged ... -->
            <div class="border rounded-lg p-2 text-center bg-green-50 border-green-200"><span class="text-sm font-medium">T1</span><span class="text-xs text-green-600 block">2 pax</span><span class="text-xs text-green-700">available</span></div>
            <div class="border rounded-lg p-2 text-center bg-green-50 border-green-200"><span class="text-sm font-medium">T2</span><span class="text-xs text-green-600 block">4 pax</span><span class="text-xs text-green-700">available</span></div>
            <div class="border rounded-lg p-2 text-center bg-red-50 border-red-200"><span class="text-sm font-medium">T3</span><span class="text-xs text-red-600 block">2 pax</span><span class="text-xs text-red-700">booked</span></div>
            <div class="border rounded-lg p-2 text-center bg-green-50 border-green-200"><span class="text-sm font-medium">T4</span><span class="text-xs text-green-600 block">2 pax</span><span class="text-xs text-green-700">available</span></div>
            <div class="border rounded-lg p-2 text-center bg-red-50 border-red-200"><span class="text-sm font-medium">T5</span><span class="text-xs text-red-600 block">2 pax</span><span class="text-xs text-red-700">booked</span></div>
            <div class="border rounded-lg p-2 text-center bg-green-50 border-green-200"><span class="text-sm font-medium">T6</span><span class="text-xs text-green-600 block">4 pax</span><span class="text-xs text-green-700">available</span></div>
            <div class="border rounded-lg p-2 text-center bg-red-50 border-red-200"><span class="text-sm font-medium">T7</span><span class="text-xs text-red-600 block">4 pax</span><span class="text-xs text-red-700">booked</span></div>
            <div class="border rounded-lg p-2 text-center bg-amber-50 border-amber-200"><span class="text-sm font-medium">T8</span><span class="text-xs text-amber-600 block">6 pax</span><span class="text-xs text-amber-700">waitlist</span></div>
            <div class="border rounded-lg p-2 text-center bg-green-50 border-green-200"><span class="text-sm font-medium">T9</span><span class="text-xs text-green-600 block">6 pax</span><span class="text-xs text-green-700">available</span></div>
            <div class="border rounded-lg p-2 text-center bg-red-50 border-red-200"><span class="text-sm font-medium">T10</span><span class="text-xs text-red-600 block">2 pax</span><span class="text-xs text-red-700">booked</span></div>
            <div class="border rounded-lg p-2 text-center bg-green-50 border-green-200"><span class="text-sm font-medium">T11</span><span class="text-xs text-green-600 block">8 pax</span><span class="text-xs text-green-700">available</span></div>
            <div class="border rounded-lg p-2 text-center bg-red-50 border-red-200"><span class="text-sm font-medium">T12</span><span class="text-xs text-red-600 block">2 pax</span><span class="text-xs text-red-700">booked</span></div>
          </div>
          <div class="flex gap-4 mt-3 text-xs text-slate-500">
            <span><span class="inline-block w-3 h-3 bg-green-50 border border-green-200 rounded mr-1"></span> available</span>
            <span><span class="inline-block w-3 h-3 bg-red-50 border border-red-200 rounded mr-1"></span> booked</span>
            <span><span class="inline-block w-3 h-3 bg-amber-50 border border-amber-200 rounded mr-1"></span> waitlist</span>
          </div>
        </div>
      </div>
    </main>
  </div>

  <!-- JavaScript for filter, export, automated action buttons with unified toast notification and pagination -->
  <script>
    (function() {
      // ---------------- PAGINATION VARIABLES ----------------
      let currentPage = 1;
      const rowsPerPage = 4;
      
      // Preserve original static rows for filter reference
      const originalRows = Array.from(document.querySelectorAll('#tableBody tr')).map(row => ({
        element: row,
        status: row.getAttribute('data-status'),
        guest: row.querySelector('td:nth-child(2)')?.innerText || ''
      }));

      // Store all rows for pagination (including hidden cancelled row)
      const allReservations = Array.from(document.querySelectorAll('#tableBody tr')).map(row => ({
        element: row.cloneNode(true), // Clone to preserve original
        status: row.getAttribute('data-status'),
        html: row.outerHTML
      }));

      // Function to render current page
      function renderPage() {
        const tbody = document.getElementById('tableBody');
        const start = (currentPage - 1) * rowsPerPage;
        const end = start + rowsPerPage;
        const pageRows = allReservations.slice(start, end);
        
        tbody.innerHTML = pageRows.map(r => r.php).join('');
        
        // Update result count
        const total = allReservations.length;
        const startCount = start + 1;
        const endCount = Math.min(end, total);
        document.getElementById('resultCount').textContent = 
          `Showing ${startCount}-${endCount} of ${total} reservations today`;
        
        // Re-apply current filter and search after page render
        const activeFilter = document.querySelector('.filter-btn.bg-amber-600')?.getAttribute('data-filter') || 'all';
        updateFilter(activeFilter);
        
        // Re-run search if there's a search term
        const searchTerm = document.getElementById('searchInput')?.value.trim().toLowerCase() || '';
        if (searchTerm) {
          applySearch(searchTerm);
        }
        
        // Refresh action button states
        refreshAllButtons();
        updatePaginationUI();
      }

      // Make changePage globally accessible
      window.changePage = function(page) {
        const totalPages = Math.ceil(allReservations.length / rowsPerPage);
        
        if (page === 'prev') {
          if (currentPage > 1) currentPage--;
          else return;
        } else if (page === 'next') {
          if (currentPage < totalPages) currentPage++;
          else return;
        } else {
          if (page >= 1 && page <= totalPages) {
            currentPage = page;
          } else {
            return;
          }
        }
        
        renderPage();
      };

      function updatePaginationUI() {
        const totalPages = Math.ceil(allReservations.length / rowsPerPage);
        const buttons = document.querySelectorAll('.pagination-btn');
        
        buttons.forEach(btn => {
          btn.classList.remove('active', 'bg-amber-600', 'text-white');
          const pageAttr = btn.getAttribute('data-page');
          
          if (pageAttr === 'prev' || pageAttr === 'next') {
            if (pageAttr === 'prev') {
              btn.disabled = currentPage === 1;
            } else if (pageAttr === 'next') {
              btn.disabled = currentPage === totalPages;
            }
          } else {
            const pageNum = parseInt(pageAttr);
            if (pageNum === currentPage) {
              btn.classList.add('active', 'bg-amber-600', 'text-white');
            }
          }
        });
      }

      // ---------------- UNIFIED TOAST NOTIFICATION ----------------
      const toast = document.getElementById('toastNotification');
      const toastIcon = toast.querySelector('i');
      const toastMessage = toast.querySelector('span');
      
      function showToast(iconClass, message, duration = 2200) {
        toastIcon.className = iconClass;
        toastMessage.innerText = message;
        toast.classList.add('show');
        setTimeout(() => {
          toast.classList.remove('show');
        }, duration);
      }

      // ---------------- AUTOMATED ACTION BUTTONS ----------------
      function updateRowStatus(row, newStatus, bgColor, textColor, displayText) {
        const statusCell = row.querySelector('td:nth-child(5) span.status-badge');
        if (statusCell) {
          statusCell.className = `status-badge ${bgColor} ${textColor} px-2 py-0.5 rounded-full text-xs`;
          statusCell.innerText = displayText;
          row.setAttribute('data-status', newStatus);
        }
      }

      function refreshActionButtonsForRow(row) {
        const status = row.getAttribute('data-status');
        const actionContainer = row.querySelector('.action-container');
        if (!actionContainer) return;

        const seatBtn = actionContainer.querySelector('.seat-btn');
        const cancelBtn = actionContainer.querySelector('.cancel-btn');
        const confirmBtn = actionContainer.querySelector('.confirm-btn');
        const notifyBtn = actionContainer.querySelector('.notify-btn');
        const orderBtn = actionContainer.querySelector('.order-btn');
        const checkBtn = actionContainer.querySelector('.check-btn');

        const allowed = {
          'pending':   { confirm: true, cancel: true, seat: false, notify: false, order: false, check: false },
          'confirmed': { seat: true, cancel: true, confirm: false, notify: false, order: false, check: false },
          'seated':    { order: true, check: true, seat: false, cancel: false, confirm: false, notify: false },
          'waitlist':  { notify: true, cancel: true, seat: false, confirm: false, order: false, check: false },
          'cancelled': { seat: false, cancel: false, confirm: false, notify: false, order: false, check: false },
          'completed': { seat: false, cancel: false, confirm: false, notify: false, order: false, check: false }
        };

        const rules = allowed[status] || allowed.pending;

        if (seatBtn) seatBtn.disabled = !rules.seat;
        if (cancelBtn) cancelBtn.disabled = !rules.cancel;
        if (confirmBtn) confirmBtn.disabled = !rules.confirm;
        if (notifyBtn) notifyBtn.disabled = !rules.notify;
        if (orderBtn) orderBtn.disabled = !rules.order;
        if (checkBtn) checkBtn.disabled = !rules.check;
      }

      function refreshAllButtons() {
        document.querySelectorAll('#tableBody tr').forEach(row => refreshActionButtonsForRow(row));
      }

      // Delegate action buttons
      const tableBody = document.getElementById('tableBody');
      if (tableBody) {
        tableBody.addEventListener('click', function(e) {
          const target = e.target;
          if (!target.classList.contains('action-btn')) return;
          if (target.disabled) {
            showToast('fa-regular fa-circle-exclamation', 'Action not allowed in current status');
            return;
          }

          e.preventDefault();
          const row = target.closest('tr');
          if (!row) return;
          
          const guest = row.querySelector('td:nth-child(2)')?.innerText || 'Guest';
          const tableInfo = row.querySelector('td:nth-child(3)')?.innerText || '';

          if (target.classList.contains('seat-btn')) {
            updateRowStatus(row, 'seated', 'bg-blue-100', 'text-blue-700', 'seated');
            showToast('fa-regular fa-bell', `${guest} seated at ${tableInfo}`);
          }
          else if (target.classList.contains('cancel-btn')) {
            updateRowStatus(row, 'cancelled', 'bg-rose-100', 'text-rose-700', 'cancelled');
            showToast('fa-regular fa-bell', `${guest} cancelled`);
          }
          else if (target.classList.contains('notify-btn')) {
            showToast('fa-regular fa-bell', `Notification sent to ${guest}`);
          }
          else if (target.classList.contains('confirm-btn')) {
            updateRowStatus(row, 'confirmed', 'bg-green-100', 'text-green-700', 'confirmed');
            showToast('fa-regular fa-bell', `${guest} confirmed`);
          }
          else if (target.classList.contains('order-btn')) {
            showToast('fa-regular fa-bell', `Order taken for ${guest}`);
          }
          else if (target.classList.contains('check-btn')) {
            updateRowStatus(row, 'completed', 'bg-purple-100', 'text-purple-700', 'completed');
            showToast('fa-regular fa-bell', `Check processed for ${guest}`);
          }

          refreshActionButtonsForRow(row);
        });
      }

      // ---------------- EXPORT BUTTON ----------------
      const exportBtn = document.getElementById('exportButton');
      
      function escapeCsvField(field) {
        if (field.includes(',') || field.includes('"') || field.includes('\n')) {
          return '"' + field.replace(/"/g, '""') + '"';
        }
        return field;
      }

      if (exportBtn) {
        exportBtn.addEventListener('click', function(e) {
          e.preventDefault();

          const allRows = document.querySelectorAll('#tableBody tr');
          const visibleRows = Array.from(allRows).filter(row => {
            const style = window.getComputedStyle(row);
            return style.display !== 'none';
          });

          const headers = ['Time', 'Guest', 'Table', 'Pax', 'Status', 'Special requests'];
          let csvContent = headers.map(h => escapeCsvField(h)).join(',') + '\n';

          visibleRows.forEach(row => {
            const time = row.querySelector('td:nth-child(1)')?.innerText.trim() || '';
            const guest = row.querySelector('td:nth-child(2)')?.innerText.trim() || '';
            const table = row.querySelector('td:nth-child(3)')?.innerText.trim() || '';
            const pax = row.querySelector('td:nth-child(4)')?.innerText.trim() || '';
            const statusSpan = row.querySelector('td:nth-child(5) span.status-badge');
            const status = statusSpan ? statusSpan.innerText.trim() : '';
            const special = row.querySelector('td:nth-child(6)')?.innerText.trim() || '—';

            const rowData = [time, guest, table, pax, status, special];
            csvContent += rowData.map(field => escapeCsvField(field)).join(',') + '\n';
          });

          const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
          const url = URL.createObjectURL(blob);
          const link = document.createElement('a');
          link.href = url;
          link.setAttribute('download', `table_reservations_${new Date().toISOString().slice(0,10)}.csv`);
          document.body.appendChild(link);
          link.click();
          document.body.removeChild(link);
          URL.revokeObjectURL(url);

          showToast('fa-regular fa-file-csv', `📋 Exported ${visibleRows.length} reservation(s) as CSV`);
        });
      }

      // ---------------- FILTER & SEARCH (preserved exactly) ----------------
      const filterButtons = document.querySelectorAll('.filter-btn');
      const tableRows = document.querySelectorAll('#tableBody tr');
      const resultSpan = document.getElementById('resultCount');
      
      function updateFilter(filterValue) {
        let visibleCount = 0;
        document.querySelectorAll('#tableBody tr').forEach(row => {
          const statusElement = row.querySelector('td:nth-child(5) span.status-badge');
          if (!statusElement) return;
          const statusText = statusElement.innerText.trim().toLowerCase();
          
          let show = false;
          if (filterValue === 'all') {
            show = true;
          } else if (filterValue === 'upcoming') {
            if (statusText === 'pending' || statusText === 'confirmed') show = true;
          } else if (filterValue === 'seated') {
            if (statusText === 'seated') show = true;
          } else if (filterValue === 'waitlist') {
            if (statusText === 'waitlist') show = true;
          } else if (filterValue === 'cancelled') {
            if (statusText === 'cancelled') show = true;
          }
          
          if (show) {
            row.style.display = '';
            visibleCount++;
          } else {
            row.style.display = 'none';
          }
        });
        
        // Update result count based on visible rows
        resultSpan.innerText = visibleCount === 0 ? 'Showing 0 of 24 reservations today' : `Showing 1-${visibleCount} of 24 reservations today`;
      }

      function applySearch(term) {
        document.querySelectorAll('#tableBody tr').forEach(row => {
          const guestCell = row.querySelector('td:nth-child(2)');
          const guestName = guestCell ? guestCell.innerText.trim().toLowerCase() : '';
          const nameMatch = term === '' || guestName.includes(term);
          
          // Get current filter state
          const activeFilterBtn = document.querySelector('.filter-btn.bg-amber-600');
          const currentFilter = activeFilterBtn ? activeFilterBtn.getAttribute('data-filter') : 'all';
          const statusElement = row.querySelector('td:nth-child(5) span.status-badge');
          const statusText = statusElement ? statusElement.innerText.trim().toLowerCase() : '';
          
          let statusMatch = false;
          if (currentFilter === 'all') statusMatch = true;
          else if (currentFilter === 'upcoming' && (statusText === 'pending' || statusText === 'confirmed')) statusMatch = true;
          else if (currentFilter === 'seated' && statusText === 'seated') statusMatch = true;
          else if (currentFilter === 'waitlist' && statusText === 'waitlist') statusMatch = true;
          else if (currentFilter === 'cancelled' && statusText === 'cancelled') statusMatch = true;
          
          if (statusMatch && nameMatch) {
            row.style.display = '';
          } else {
            row.style.display = 'none';
          }
        });
        
        let visible = 0;
        document.querySelectorAll('#tableBody tr').forEach(r => { if (r.style.display !== 'none') visible++; });
        resultSpan.innerText = visible === 0 ? 'Showing 0 of 24 reservations today' : `Showing 1-${visible} of 24 reservations today`;
      }
      
      filterButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
          filterButtons.forEach(b => {
            b.classList.remove('bg-amber-600', 'text-white');
            b.classList.add('border', 'border-slate-200', 'text-slate-700');
          });
          this.classList.add('bg-amber-600', 'text-white');
          this.classList.remove('border', 'border-slate-200');
          
          const filter = this.getAttribute('data-filter');
          updateFilter(filter);
        });
      });
      
      const searchInput = document.getElementById('searchInput');
      if (searchInput) {
        searchInput.addEventListener('input', function() {
          const term = this.value.trim().toLowerCase();
          applySearch(term);
        });
      }
      
      // initialize: show all, make cancelled row visible for 'all' filter
      document.querySelectorAll('#tableBody tr[data-status="cancelled"]').forEach(row => {
        row.style.display = ''; 
      });
      
      // Initialize pagination
      renderPage();
      
      // Initial button states based on status
      refreshAllButtons();
      
    })();
  </script>
</body>
</html>