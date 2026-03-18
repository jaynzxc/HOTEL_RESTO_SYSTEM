<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin · Kitchen Orders (KOT)</title>
  <!-- Tailwind via CDN + Font Awesome 6 -->
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <style>
    /* exact same dropdown styles from index2.html */
    .transition-side { transition: all 0.2s ease; }
    .dropdown-arrow { transition: transform 0.2s; }
    details[open] .dropdown-arrow { transform: rotate(90deg); }
    details > summary { list-style: none; }
    details summary::-webkit-details-marker { display: none; }
    
    /* Modal styles */
    .modal {
      display: none;
      position: fixed;
      z-index: 1000;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.5);
      align-items: center;
      justify-content: center;
    }
    
    .modal.show {
      display: flex;
    }
    
    .modal-content {
      background-color: white;
      border-radius: 1rem;
      max-width: 500px;
      width: 90%;
      max-height: 80vh;
      overflow-y: auto;
    }
    
    /* Toast notification */
    .toast {
      position: fixed;
      bottom: 20px;
      right: 20px;
      background-color: #10b981;
      color: white;
      padding: 12px 24px;
      border-radius: 9999px;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
      transform: translateY(100px);
      opacity: 0;
      transition: all 0.3s ease;
      z-index: 1100;
    }
    
    .toast.show {
      transform: translateY(0);
      opacity: 1;
    }
    
    .toast.error {
      background-color: #ef4444;
    }
    
    .toast.info {
      background-color: #3b82f6;
    }
    
    /* Animation for highlighting */
    @keyframes highlight {
      0% { background-color: #fef3c7; }
      100% { background-color: transparent; }
    }
    
    .highlight {
      animation: highlight 2s ease;
    }
  </style>
</head>
<body class="bg-white font-sans antialiased">

  <!-- Toast notification container -->
  <div id="toast" class="toast"></div>

  <!-- Edit Order Modal -->
  <div id="editModal" class="modal">
    <div class="modal-content p-6">
      <div class="flex justify-between items-center mb-4">
        <h3 class="text-xl font-semibold">Edit Order</h3>
        <button onclick="closeModal()" class="text-slate-400 hover:text-slate-600">
          <i class="fa-solid fa-xmark text-2xl"></i>
        </button>
      </div>
      <form id="editOrderForm" onsubmit="saveOrderChanges(event)">
        <input type="hidden" id="editOrderId">
        
        <div class="mb-4">
          <label class="block text-sm font-medium text-slate-700 mb-1">Customer Name</label>
          <input type="text" id="editCustomer" class="w-full border border-slate-200 rounded-lg px-3 py-2">
        </div>
        
        <div class="mb-4">
          <label class="block text-sm font-medium text-slate-700 mb-1">Order Type</label>
          <select id="editType" class="w-full border border-slate-200 rounded-lg px-3 py-2">
            <option value="Dine-in">Dine-in</option>
            <option value="Take-out">Take-out</option>
            <option value="Delivery">Delivery</option>
          </select>
        </div>
        
        <div class="mb-4">
          <label class="block text-sm font-medium text-slate-700 mb-1">Items</label>
          <textarea id="editItems" rows="3" class="w-full border border-slate-200 rounded-lg px-3 py-2"></textarea>
        </div>
        
        <div class="mb-4">
          <label class="block text-sm font-medium text-slate-700 mb-1">Status</label>
          <select id="editStatus" class="w-full border border-slate-200 rounded-lg px-3 py-2">
            <option value="new">New</option>
            <option value="preparing">Preparing</option>
            <option value="ready">Ready</option>
            <option value="urgent">Urgent</option>
          </select>
        </div>
        
        <div class="mb-6">
          <label class="block text-sm font-medium text-slate-700 mb-1">Special Instructions</label>
          <textarea id="editInstructions" rows="2" class="w-full border border-slate-200 rounded-lg px-3 py-2"></textarea>
        </div>
        
        <div class="flex gap-3">
          <button type="submit" class="flex-1 bg-amber-600 text-white py-2 rounded-lg hover:bg-amber-700">Save Changes</button>
          <button type="button" onclick="closeModal()" class="flex-1 border border-slate-200 py-2 rounded-lg hover:bg-slate-50">Cancel</button>
        </div>
      </form>
    </div>
  </div>

  <!-- APP CONTAINER: flex row (sidebar + main) -->
  <div class="min-h-screen flex flex-col lg:flex-row">

    <!-- ========== SIDEBAR (exact copy from index2.html) ========== -->
    <aside class="lg:w-80 bg-white border-r border-slate-200 shadow-sm lg:min-h-screen shrink-0 overflow-y-auto">
      <!-- brand -->
      <div class="px-5 py-6 border-b border-slate-100 flex items-center gap-2">
        <i class="fa-solid fa-utensils text-amber-600 text-xl"></i>
        <i class="fa-solid fa-bed text-amber-600 text-xl"></i>
        <span class="font-semibold text-lg tracking-tight text-slate-800">HNR<span class="text-amber-600"> Admin</span></span>
      </div>

      <!-- admin badge -->
      <div class="flex items-center gap-3 px-5 py-4 border-b border-slate-100 bg-slate-50/60">
        <div class="h-9 w-9 rounded-full bg-amber-200 flex items-center justify-center text-amber-800 font-bold">A</div>
        <div>
          <p class="font-medium text-sm">Admin User</p>
          <p class="text-xs text-slate-500">role</p>
        </div>
      </div>

      <!-- ===== SIDEBAR MENU (grouped with dropdowns) ===== -->
      <nav class="p-4 space-y-2 text-sm">

        <!-- Dashboard -->
        <a href="../dashboard.html" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-700 hover:bg-amber-50 transition">
          <i class="fa-solid fa-table-cells-large w-5 text-slate-400"></i>
          <span>Dashboard</span>
        </a>

        <!-- HOTEL MANAGEMENT GROUP -->
        <details class="group" open>
          <summary class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-700 hover:bg-amber-50 cursor-pointer">
            <i class="fa-solid fa-hotel w-5 text-slate-400 group-open:text-amber-600"></i>
            <span class="font-medium">HOTEL MANAGEMENT</span>
            <i class="fa-solid fa-chevron-right dropdown-arrow ml-auto text-xs text-slate-400"></i>
          </summary>
          <div class="ml-6 mt-1 space-y-1 pl-3 border-l-2 border-amber-100">
            <a href="../hotel_management/Front_Desk_Reception.html" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i class="fa-regular fa-reception w-4 text-slate-400"></i> Front Desk / Reception</a>
            <a href="../hotel_management/room_management.html" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i class="fa-solid fa-bed w-4 text-slate-400"></i> Room Management</a>
            <a href="../hotel_management/reservation_&_booking.html" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i class="fa-regular fa-calendar-check w-4 text-slate-400"></i> Reservations & Booking</a>
            <a href="../hotel_management/housekeeping_&_maintenance.html" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i class="fa-solid fa-broom w-4 text-slate-400"></i> Housekeeping & Maintenance</a>
            <a href="../hotel_management/event_&_conference.html" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i class="fa-regular fa-calendar w-4 text-slate-400"></i> Events & Conference</a>
          </div>
        </details>

        <!-- RESTAURANT MANAGEMENT GROUP - open with Kitchen Orders (KOT) highlighted -->
        <details class="group" open>
          <summary class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-amber-800 bg-amber-50 cursor-pointer transition-side">
            <i class="fa-solid fa-utensils w-5 text-amber-600"></i>
            <span class="font-medium">RESTAURANT MANAGEMENT</span>
            <i class="fa-solid fa-chevron-right dropdown-arrow ml-auto text-xs text-amber-600"></i>
          </summary>
          <div class="ml-6 mt-1 space-y-1 pl-3 border-l-2 border-amber-200">
            <a href="../restaurant_management/table_reservation.html" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i class="fa-regular fa-clock w-4 text-slate-400"></i> Table Reservation</a>
            <a href="../restaurant_management/menu_management.html" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i class="fa-solid fa-bars w-4 text-slate-400"></i> Menu Management</a>
            <a href="../restaurant_management/orders_pos.html" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i class="fa-solid fa-cash-register w-4 text-slate-400"></i> Orders / POS</a>
            <a href="../restaurant_management/kitchen_orders.html" class="flex items-center gap-2 px-4 py-2 rounded-lg bg-amber-100/50 text-amber-700 font-medium"><i class="fa-solid fa-fire w-4 text-amber-600"></i> Kitchen Orders (KOT)</a>
            <a href="../restaurant_management/wait_staff_management.html" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i class="fa-regular fa-user w-4 text-slate-400"></i> Wait Staff Management</a>
          </div>
        </details>

        <!-- CUSTOMER MANAGEMENT -->
        <details class="group" open>
          <summary class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-700 hover:bg-amber-50 cursor-pointer">
            <i class="fa-regular fa-address-book w-5 text-slate-400 group-open:text-amber-600"></i>
            <span class="font-medium">CUSTOMER MANAGEMENT</span>
            <i class="fa-solid fa-chevron-right dropdown-arrow ml-auto text-xs text-slate-400"></i>
          </summary>
          <div class="ml-6 mt-1 space-y-1 pl-3 border-l-2 border-amber-100">
            <a href="../customer_management/customer_relationship.html" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i class="fa-regular fa-handshake w-4"></i> Guest Relationship (CRM)</a>
            <a href="../customer_management/loyalty_rewards.html" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i class="fa-regular fa-star w-4"></i> Loyalty & Rewards</a>
            <a href="../customer_management/customer_feedback_&_reviews.html" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i class="fa-regular fa-pen-to-square w-4"></i> Customer Feedback & Reviews</a>
          </div>
        </details>

        <!-- OPERATIONS -->
        <details class="group" open>
          <summary class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-700 hover:bg-amber-50 cursor-pointer">
            <i class="fa-solid fa-gears w-5 text-slate-400 group-open:text-amber-600"></i>
            <span class="font-medium">OPERATIONS</span>
            <i class="fa-solid fa-chevron-right dropdown-arrow ml-auto text-xs text-slate-400"></i>
          </summary>
          <div class="ml-6 mt-1 space-y-1 pl-3 border-l-2 border-amber-100">
            <a href="../hotel_management/inventory_&_stocks.html" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i class="fa-solid fa-boxes w-4"></i> Inventory & Stock</a>
            <a href="../hotel_management/billing_&_payment.html" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i class="fa-regular fa-credit-card w-4"></i> Billing & Payments</a>
            <a href="../hotel_management/payment_gateway.html" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i class="fa-solid fa-wifi w-4"></i> Payment Gateway</a>
          </div>
        </details>

        <!-- MARKETING -->
        <details class="group" open>
          <summary class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-700 hover:bg-amber-50 cursor-pointer">
            <i class="fa-solid fa-megaphone w-5 text-slate-400 group-open:text-amber-600"></i>
            <span class="font-medium">MARKETING</span>
            <i class="fa-solid fa-chevron-right dropdown-arrow ml-auto text-xs text-slate-400"></i>
          </summary>
          <div class="ml-6 mt-1 space-y-1 pl-3 border-l-2 border-amber-100">
            <a href="../hotel_management/hotelmarketing_&_promotions.html" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i class="fa-regular fa-gem w-4"></i> Hotel Marketing & Promotions</a>
            <a href="../hotel_management/online_ordering_integration.html" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i class="fa-solid fa-cart-shopping w-4"></i> Online Ordering Integration</a>
          </div>
        </details>

        <!-- REPORTS & ANALYTICS -->
        <details class="group" open>
          <summary class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-700 hover:bg-amber-50 cursor-pointer">
            <i class="fa-solid fa-chart-simple w-5 text-slate-400 group-open:text-amber-600"></i>
            <span class="font-medium">REPORTS & ANALYTICS</span>
            <i class="fa-solid fa-chevron-right dropdown-arrow ml-auto text-xs text-slate-400"></i>
          </summary>
          <div class="ml-6 mt-1 space-y-1 pl-3 border-l-2 border-amber-100">
            <a href="../reports_&_analytics/sales_report.html" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i class="fa-solid fa-chart-line w-4"></i> Sales Reports</a>
            <a href="../reports_&_analytics/booking_reports.html" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i class="fa-regular fa-calendar w-4"></i> Booking Reports</a>
            <a href="../reports_&_analytics/analytics_dashboard.html" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i class="fa-solid fa-chart-pie w-4"></i> Analytics Dashboard</a>
          </div>
        </details>

        <!-- SYSTEM -->
        <details class="group" open>
          <summary class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-700 hover:bg-amber-50 cursor-pointer">
            <i class="fa-solid fa-computer w-5 text-slate-400 group-open:text-amber-600"></i>
            <span class="font-medium">SYSTEM</span>
            <i class="fa-solid fa-chevron-right dropdown-arrow ml-auto text-xs text-slate-400"></i>
          </summary>
          <div class="ml-6 mt-1 space-y-1 pl-3 border-l-2 border-amber-100">
            <a href="../hotel_management/channel_management.html" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i class="fa-solid fa-code-branch w-4"></i> Channel Management</a>
            <a href="../hotel_management/door_lock_integration.html" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i class="fa-solid fa-lock w-4"></i> Door Lock Integration</a>
            <a href="../hotel_management/settings.html" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i class="fa-solid fa-sliders w-4"></i> Settings</a>
          </div>
        </details>

        <!-- logout -->
        <div class="border-t border-slate-200 pt-3 mt-3">
          <a href="../../login-register/login_form.html" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-500 hover:bg-red-50 hover:text-red-700">
            <i class="fa-solid fa-arrow-right-from-bracket w-5"></i>
            <span>Logout</span>
          </a>
        </div>
      </nav>
    </aside>

    <!-- ========== MAIN CONTENT (KITCHEN ORDERS - KOT) ========== -->
    <main class="flex-1 p-5 lg:p-8 overflow-y-auto bg-white">

      <!-- header with title and date -->
      <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <div>
          <h1 class="text-2xl lg:text-3xl font-light text-slate-800">Kitchen Orders (KOT)</h1>
          <p class="text-sm text-slate-500 mt-0.5">real-time kitchen display system · manage food preparation</p>
        </div>
        <div class="flex gap-3 text-sm">
          <span class="bg-white border rounded-full px-4 py-2 flex items-center gap-2 shadow-sm"><i class="fa-regular fa-calendar text-slate-400"></i> May 21, 2025</span>
          <span class="bg-white border rounded-full px-4 py-2 shadow-sm"><i class="fa-regular fa-bell"></i></span>
        </div>
      </div>

      <!-- ===== STATS CARDS ===== (these stay on top) -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
          <p class="text-xs text-slate-500">New orders</p>
          <p class="text-2xl font-semibold text-blue-600">6</p>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
          <p class="text-xs text-slate-500">Preparing</p>
          <p class="text-2xl font-semibold text-amber-600">8</p>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
          <p class="text-xs text-slate-500">Ready to serve</p>
          <p class="text-2xl font-semibold text-green-600">4</p>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
          <p class="text-xs text-slate-500">Completed (today)</p>
          <p class="text-2xl font-semibold">42</p>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
          <p class="text-xs text-slate-500">Avg prep time</p>
          <p class="text-2xl font-semibold">12 min</p>
        </div>
      </div>

      <!-- ===== FILTER TABS (all orders, new, preparing, ready, urgent) with functions ===== -->
      <div class="flex flex-wrap gap-2 border-b border-slate-200 pb-2 mb-6">
        <button id="filterAll" class="filter-btn px-4 py-2 bg-amber-600 text-white rounded-full text-sm">all orders</button>
        <button id="filterNew" class="filter-btn px-4 py-2 bg-white border border-slate-200 rounded-full text-sm hover:bg-slate-50">new</button>
        <button id="filterPreparing" class="filter-btn px-4 py-2 bg-white border border-slate-200 rounded-full text-sm hover:bg-slate-50">preparing</button>
        <button id="filterReady" class="filter-btn px-4 py-2 bg-white border border-slate-200 rounded-full text-sm hover:bg-slate-50">ready</button>
        <button id="filterUrgent" class="filter-btn px-4 py-2 bg-white border border-slate-200 rounded-full text-sm hover:bg-slate-50">urgent</button>
      </div>

      <!-- ===== CUSTOMER ORDERS TABLE (instead of cards) ===== -->
      <div id="ordersTableContainer" class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-x-auto mb-8">
        <table class="min-w-full text-sm" id="ordersTable">
          <thead class="bg-slate-50 text-slate-600 border-b border-slate-200">
            <tr>
              <th class="px-5 py-3 text-left font-medium">Order #</th>
              <th class="px-5 py-3 text-left font-medium">Customer</th>
              <th class="px-5 py-3 text-left font-medium">Type</th>
              <th class="px-5 py-3 text-left font-medium">Items</th>
              <th class="px-5 py-3 text-left font-medium">Status</th>
              <th class="px-5 py-3 text-left font-medium">Time</th>
              <th class="px-5 py-3 text-left font-medium">Special Instructions</th>
              <th class="px-5 py-3 text-left font-medium">Action</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100" id="ordersTableBody">
            <!-- Order 1: New -->
            <tr data-status="new" data-order-id="KOT-1001">
              <td class="px-5 py-3 font-medium">#KOT-1001</td>
              <td class="px-5 py-3">Cruz</td>
              <td class="px-5 py-3">Dine-in (Table 4)</td>
              <td class="px-5 py-3">1x Sinigang, 2x Rice</td>
              <td class="px-5 py-3"><span class="bg-blue-100 text-blue-700 text-xs px-2 py-1 rounded-full">NEW</span></td>
              <td class="px-5 py-3">12:30 PM</td>
              <td class="px-5 py-3">no onions, extra spicy</td>
              <td class="px-5 py-3">
                <button class="edit-btn text-amber-600 hover:text-amber-800" onclick="openEditModal('KOT-1001')"><i class="fa-regular fa-pen-to-square"></i></button>
              </td>
            </tr>
            <!-- Order 2: Preparing -->
            <tr data-status="preparing" data-order-id="KOT-1002">
              <td class="px-5 py-3 font-medium">#KOT-1002</td>
              <td class="px-5 py-3">Kim, Jiyeon</td>
              <td class="px-5 py-3">Take-out</td>
              <td class="px-5 py-3">1x Sisig, 1x Rice</td>
              <td class="px-5 py-3"><span class="bg-amber-100 text-amber-700 text-xs px-2 py-1 rounded-full">PREPARING</span></td>
              <td class="px-5 py-3">12:45 PM</td>
              <td class="px-5 py-3">—</td>
              <td class="px-5 py-3">
                <button class="edit-btn text-amber-600 hover:text-amber-800" onclick="openEditModal('KOT-1002')"><i class="fa-regular fa-pen-to-square"></i></button>
              </td>
            </tr>
            <!-- Order 3: Urgent -->
            <tr data-status="urgent" data-order-id="KOT-1003">
              <td class="px-5 py-3 font-medium">#KOT-1003</td>
              <td class="px-5 py-3">Reyes</td>
              <td class="px-5 py-3">Dine-in (Table 9)</td>
              <td class="px-5 py-3">2x Crispy Pata, 3x Rice</td>
              <td class="px-5 py-3"><span class="bg-red-100 text-red-700 text-xs px-2 py-1 rounded-full">URGENT</span></td>
              <td class="px-5 py-3">1:00 PM</td>
              <td class="px-5 py-3">well-done crispy pata</td>
              <td class="px-5 py-3">
                <button class="edit-btn text-amber-600 hover:text-amber-800" onclick="openEditModal('KOT-1003')"><i class="fa-regular fa-pen-to-square"></i></button>
              </td>
            </tr>
            <!-- Order 4: Preparing -->
            <tr data-status="preparing" data-order-id="KOT-1004">
              <td class="px-5 py-3 font-medium">#KOT-1004</td>
              <td class="px-5 py-3">Santos, Anna</td>
              <td class="px-5 py-3">Delivery</td>
              <td class="px-5 py-3">1x Kare-Kare, 2x Halo-Halo, 1x Rice</td>
              <td class="px-5 py-3"><span class="bg-amber-100 text-amber-700 text-xs px-2 py-1 rounded-full">PREPARING</span></td>
              <td class="px-5 py-3">1:15 PM</td>
              <td class="px-5 py-3">separate containers</td>
              <td class="px-5 py-3">
                <button class="edit-btn text-amber-600 hover:text-amber-800" onclick="openEditModal('KOT-1004')"><i class="fa-regular fa-pen-to-square"></i></button>
              </td>
            </tr>
            <!-- Order 5: New -->
            <tr data-status="new" data-order-id="KOT-1005">
              <td class="px-5 py-3 font-medium">#KOT-1005</td>
              <td class="px-5 py-3">Tan, Michelle</td>
              <td class="px-5 py-3">Take-out</td>
              <td class="px-5 py-3">1x Sinigang, 1x Rice</td>
              <td class="px-5 py-3"><span class="bg-blue-100 text-blue-700 text-xs px-2 py-1 rounded-full">NEW</span></td>
              <td class="px-5 py-3">1:30 PM</td>
              <td class="px-5 py-3">—</td>
              <td class="px-5 py-3">
                <button class="edit-btn text-amber-600 hover:text-amber-800" onclick="openEditModal('KOT-1005')"><i class="fa-regular fa-pen-to-square"></i></button>
              </td>
            </tr>
            <!-- Order 6: Ready -->
            <tr data-status="ready" data-order-id="KOT-1006">
              <td class="px-5 py-3 font-medium">#KOT-1006</td>
              <td class="px-5 py-3">Garcia</td>
              <td class="px-5 py-3">Dine-in (Table 2)</td>
              <td class="px-5 py-3">1x Sisig, 2x Rice</td>
              <td class="px-5 py-3"><span class="bg-green-100 text-green-700 text-xs px-2 py-1 rounded-full">READY</span></td>
              <td class="px-5 py-3">1:45 PM</td>
              <td class="px-5 py-3">—</td>
              <td class="px-5 py-3">
                <button class="edit-btn text-amber-600 hover:text-amber-800" onclick="openEditModal('KOT-1006')"><i class="fa-regular fa-pen-to-square"></i></button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- ===== BOTTOM: EXPANDED PREPARATION QUEUE (full width) ===== -->
      <div class="bg-white rounded-2xl border border-slate-200 p-6">
        <h2 class="font-semibold text-lg flex items-center gap-2 mb-4"><i class="fa-regular fa-rectangle-list text-amber-600"></i> preparation queue</h2>
        
        <!-- Expanded queue table with more details -->
        <div class="overflow-x-auto">
          <table class="min-w-full text-sm">
            <thead class="bg-slate-50 text-slate-600 border-b border-slate-200">
              <tr>
                <th class="px-4 py-3 text-left font-medium">Order #</th>
                <th class="px-4 py-3 text-left font-medium">Customer</th>
                <th class="px-4 py-3 text-left font-medium">Type</th>
                <th class="px-4 py-3 text-left font-medium">Items</th>
                <th class="px-4 py-3 text-left font-medium">Status</th>
                <th class="px-4 py-3 text-left font-medium">Wait Time</th>
                <th class="px-4 py-3 text-left font-medium">Est. Completion</th>
                <th class="px-4 py-3 text-left font-medium">Priority</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100" id="queueTableBody">
              <tr class="hover:bg-slate-50" data-queue-status="new">
                <td class="px-4 py-3 font-medium">#KOT-1001</td>
                <td class="px-4 py-3">Cruz</td>
                <td class="px-4 py-3">Table 4</td>
                <td class="px-4 py-3">3 items</td>
                <td class="px-4 py-3"><span class="bg-blue-100 text-blue-700 text-xs px-2 py-1 rounded-full">NEW</span></td>
                <td class="px-4 py-3">0 min</td>
                <td class="px-4 py-3">12:45 PM</td>
                <td class="px-4 py-3"><span class="text-blue-600">Normal</span></td>
              </tr>
              <tr class="hover:bg-slate-50" data-queue-status="preparing">
                <td class="px-4 py-3 font-medium">#KOT-1002</td>
                <td class="px-4 py-3">Kim, Jiyeon</td>
                <td class="px-4 py-3">Take-out</td>
                <td class="px-4 py-3">2 items</td>
                <td class="px-4 py-3"><span class="bg-amber-100 text-amber-700 text-xs px-2 py-1 rounded-full">PREPARING</span></td>
                <td class="px-4 py-3">8 min</td>
                <td class="px-4 py-3">12:53 PM</td>
                <td class="px-4 py-3"><span class="text-amber-600">Medium</span></td>
              </tr>
              <tr class="hover:bg-slate-50" data-queue-status="urgent">
                <td class="px-4 py-3 font-medium">#KOT-1003</td>
                <td class="px-4 py-3">Reyes</td>
                <td class="px-4 py-3">Table 9</td>
                <td class="px-4 py-3">5 items</td>
                <td class="px-4 py-3"><span class="bg-red-100 text-red-700 text-xs px-2 py-1 rounded-full">URGENT</span></td>
                <td class="px-4 py-3">25 min</td>
                <td class="px-4 py-3">1:25 PM</td>
                <td class="px-4 py-3"><span class="text-red-600 font-semibold">High</span></td>
              </tr>
              <tr class="hover:bg-slate-50" data-queue-status="preparing">
                <td class="px-4 py-3 font-medium">#KOT-1004</td>
                <td class="px-4 py-3">Santos, Anna</td>
                <td class="px-4 py-3">Delivery</td>
                <td class="px-4 py-3">4 items</td>
                <td class="px-4 py-3"><span class="bg-amber-100 text-amber-700 text-xs px-2 py-1 rounded-full">PREPARING</span></td>
                <td class="px-4 py-3">12 min</td>
                <td class="px-4 py-3">1:27 PM</td>
                <td class="px-4 py-3"><span class="text-amber-600">Medium</span></td>
              </tr>
              <tr class="hover:bg-slate-50" data-queue-status="new">
                <td class="px-4 py-3 font-medium">#KOT-1005</td>
                <td class="px-4 py-3">Tan, Michelle</td>
                <td class="px-4 py-3">Take-out</td>
                <td class="px-4 py-3">2 items</td>
                <td class="px-4 py-3"><span class="bg-blue-100 text-blue-700 text-xs px-2 py-1 rounded-full">NEW</span></td>
                <td class="px-4 py-3">0 min</td>
                <td class="px-4 py-3">1:45 PM</td>
                <td class="px-4 py-3"><span class="text-blue-600">Normal</span></td>
              </tr>
              <tr class="hover:bg-slate-50" data-queue-status="ready">
                <td class="px-4 py-3 font-medium">#KOT-1006</td>
                <td class="px-4 py-3">Garcia</td>
                <td class="px-4 py-3">Table 2</td>
                <td class="px-4 py-3">3 items</td>
                <td class="px-4 py-3"><span class="bg-green-100 text-green-700 text-xs px-2 py-1 rounded-full">READY</span></td>
                <td class="px-4 py-3">Completed</td>
                <td class="px-4 py-3">1:45 PM</td>
                <td class="px-4 py-3"><span class="text-green-600">Serving</span></td>
              </tr>
            </tbody>
          </table>
        </div>
        
        <!-- Queue summary footer with functions -->
        <div class="mt-4 pt-3 border-t border-slate-100 flex flex-wrap justify-between items-center text-sm text-slate-500">
          <div class="flex gap-4">
            <span><span class="font-medium text-slate-700" id="ordersInProgress">5</span> orders in progress</span>
            <span><span class="font-medium text-slate-700" id="avgWaitTime">12.4</span> avg min wait</span>
          </div>
          <div class="flex gap-2">
            <button id="refreshQueueBtn" class="text-amber-600 hover:text-amber-800 text-xs flex items-center gap-1" onclick="refreshQueue()">
              <i class="fa-regular fa-clock"></i> refresh queue
            </button>
            <button id="viewAllBtn" class="text-amber-600 hover:text-amber-800 text-xs flex items-center gap-1" onclick="viewAllOrders()">
              <i class="fa-regular fa-file-lines"></i> view all
            </button>
          </div>
        </div>
      </div>
    </main>
  </div>

  <script>
    // Filter functionality for the orders table
    document.addEventListener('DOMContentLoaded', function() {
      // Get all filter buttons
      const filterAll = document.getElementById('filterAll');
      const filterNew = document.getElementById('filterNew');
      const filterPreparing = document.getElementById('filterPreparing');
      const filterReady = document.getElementById('filterReady');
      const filterUrgent = document.getElementById('filterUrgent');
      
      // Get all filter buttons for styling
      const filterButtons = document.querySelectorAll('.filter-btn');
      
      // Get all order rows
      const orderRows = document.querySelectorAll('#ordersTableBody tr');
      
      // Function to update active button styling
      function setActiveButton(activeButton) {
        filterButtons.forEach(button => {
          button.classList.remove('bg-amber-600', 'text-white');
          button.classList.add('bg-white', 'border', 'border-slate-200', 'text-slate-700');
        });
        
        activeButton.classList.remove('bg-white', 'border', 'border-slate-200', 'text-slate-700');
        activeButton.classList.add('bg-amber-600', 'text-white');
      }
      
      // Function to filter orders
      window.filterOrders = function(status) {
        orderRows.forEach(row => {
          if (status === 'all') {
            row.style.display = '';
          } else {
            const rowStatus = row.getAttribute('data-status');
            if (rowStatus === status) {
              row.style.display = '';
            } else {
              row.style.display = 'none';
            }
          }
        });
      }
      
      // Add click event listeners
      filterAll.addEventListener('click', function() {
        setActiveButton(this);
        filterOrders('all');
      });
      
      filterNew.addEventListener('click', function() {
        setActiveButton(this);
        filterOrders('new');
      });
      
      filterPreparing.addEventListener('click', function() {
        setActiveButton(this);
        filterOrders('preparing');
      });
      
      filterReady.addEventListener('click', function() {
        setActiveButton(this);
        filterOrders('ready');
      });
      
      filterUrgent.addEventListener('click', function() {
        setActiveButton(this);
        filterOrders('urgent');
      });
      
      // Initialize - show all orders
      filterOrders('all');
    });

    // ========== MODAL FUNCTIONS ==========
    function openEditModal(orderId) {
      // Find the order row
      const orderRow = document.querySelector(`tr[data-order-id="${orderId}"]`);
      if (!orderRow) return;
      
      // Get order details
      const cells = orderRow.cells;
      const customer = cells[1].textContent;
      const type = cells[2].textContent;
      const items = cells[3].textContent;
      const status = cells[4].querySelector('span').textContent.toLowerCase();
      const instructions = cells[6].textContent;
      
      // Populate modal fields
      document.getElementById('editOrderId').value = orderId;
      document.getElementById('editCustomer').value = customer;
      
      // Fix type selection
      if (type.includes('Dine-in')) {
        document.getElementById('editType').value = 'Dine-in';
      } else if (type.includes('Take-out')) {
        document.getElementById('editType').value = 'Take-out';
      } else if (type.includes('Delivery')) {
        document.getElementById('editType').value = 'Delivery';
      }
      
      document.getElementById('editItems').value = items;
      document.getElementById('editStatus').value = status;
      document.getElementById('editInstructions').value = instructions === '—' ? '' : instructions;
      
      // Show modal
      document.getElementById('editModal').classList.add('show');
    }
    
    function closeModal() {
      document.getElementById('editModal').classList.remove('show');
    }
    
    function saveOrderChanges(event) {
      event.preventDefault();
      
      const orderId = document.getElementById('editOrderId').value;
      const customer = document.getElementById('editCustomer').value;
      const type = document.getElementById('editType').value;
      const items = document.getElementById('editItems').value;
      const status = document.getElementById('editStatus').value;
      const instructions = document.getElementById('editInstructions').value;
      
      // Find and update the order row
      const orderRow = document.querySelector(`tr[data-order-id="${orderId}"]`);
      if (orderRow) {
        const cells = orderRow.cells;
        cells[1].textContent = customer;
        
        // Update type based on selection
        if (type === 'Dine-in') {
          // Try to preserve table number if it exists
          const currentType = cells[2].textContent;
          const tableMatch = currentType.match(/\(Table \d+\)/);
          if (tableMatch) {
            cells[2].textContent = `Dine-in ${tableMatch[0]}`;
          } else {
            cells[2].textContent = 'Dine-in (Table 4)';
          }
        } else {
          cells[2].textContent = type;
        }
        
        cells[3].textContent = items;
        
        // Update status badge
        const statusSpan = cells[4].querySelector('span');
        const statusColors = {
          'new': 'bg-blue-100 text-blue-700',
          'preparing': 'bg-amber-100 text-amber-700',
          'ready': 'bg-green-100 text-green-700',
          'urgent': 'bg-red-100 text-red-700'
        };
        statusSpan.className = `${statusColors[status]} text-xs px-2 py-1 rounded-full`;
        statusSpan.textContent = status.toUpperCase();
        
        cells[6].textContent = instructions || '—';
        
        // Update data-status attribute
        orderRow.setAttribute('data-status', status);
      }
      
      // Also update queue table if needed
      updateQueueRow(orderId, customer, type, items, status);
      
      showToast('Order updated successfully!', 'success');
      closeModal();
    }
    
    function updateQueueRow(orderId, customer, type, items, status) {
      const queueRows = document.querySelectorAll('#queueTableBody tr');
      for (let row of queueRows) {
        if (row.cells[0].textContent === `#${orderId}`) {
          row.cells[1].textContent = customer;
          
          // Update type in queue table
          if (type === 'Dine-in') {
            row.cells[2].textContent = 'Table 4';
          } else {
            row.cells[2].textContent = type;
          }
          
          row.cells[3].textContent = items.split(',').length + ' items';
          
          // Update status badge
          const statusSpan = row.cells[4].querySelector('span');
          const statusColors = {
            'new': 'bg-blue-100 text-blue-700',
            'preparing': 'bg-amber-100 text-amber-700',
            'ready': 'bg-green-100 text-green-700',
            'urgent': 'bg-red-100 text-red-700'
          };
          statusSpan.className = `${statusColors[status]} text-xs px-2 py-1 rounded-full`;
          statusSpan.textContent = status.toUpperCase();
          
          // Update wait time based on status
          const waitTimeCell = row.cells[5];
          if (status === 'ready') {
            waitTimeCell.textContent = 'Completed';
          } else if (status === 'new') {
            waitTimeCell.textContent = '0 min';
          }
          
          // Update priority based on status
          const priorityCell = row.cells[7];
          const prioritySpan = priorityCell.querySelector('span');
          const priorityColors = {
            'new': 'text-blue-600',
            'preparing': 'text-amber-600',
            'ready': 'text-green-600',
            'urgent': 'text-red-600 font-semibold'
          };
          const priorityTexts = {
            'new': 'Normal',
            'preparing': 'Medium',
            'ready': 'Serving',
            'urgent': 'High'
          };
          prioritySpan.className = priorityColors[status];
          prioritySpan.textContent = priorityTexts[status];
          
          // Update data-queue-status attribute
          row.setAttribute('data-queue-status', status);
          break;
        }
      }
    }
    
    // ========== QUEUE FUNCTIONS ==========
    function refreshQueue() {
      // Simulate refreshing queue data
      showToast('Refreshing queue...', 'info');
      
      // Simulate loading effect
      const refreshBtn = document.getElementById('refreshQueueBtn');
      const originalText = refreshBtn.innerHTML;
      refreshBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> refreshing...';
      refreshBtn.disabled = true;
      
      setTimeout(() => {
        // Update random wait times to simulate refresh
        const queueRows = document.querySelectorAll('#queueTableBody tr');
        queueRows.forEach(row => {
          if (row.getAttribute('data-queue-status') !== 'ready') {
            const waitTimeCell = row.cells[5];
            const currentWait = parseInt(waitTimeCell.textContent) || 0;
            // Increment wait time slightly
            if (currentWait !== 0 && !isNaN(currentWait)) {
              waitTimeCell.textContent = (currentWait + 1) + ' min';
            }
          }
        });
        
        // Update average wait time
        const avgWaitElement = document.getElementById('avgWaitTime');
        const currentAvg = parseFloat(avgWaitElement.textContent);
        avgWaitElement.textContent = (currentAvg + 0.2).toFixed(1);
        
        refreshBtn.innerHTML = originalText;
        refreshBtn.disabled = false;
        showToast('Queue refreshed!', 'success');
      }, 1500);
    }
    
    function viewAllOrders() {
      // Trigger the "all orders" filter
      const filterAllBtn = document.getElementById('filterAll');
      filterAllBtn.click();
      
      // Get the orders table container
      const ordersContainer = document.getElementById('ordersTableContainer');
      
      // Scroll to the orders table with smooth behavior
      ordersContainer.scrollIntoView({ 
        behavior: 'smooth',
        block: 'start'
      });
      
      // Add a temporary highlight effect to the table
      ordersContainer.classList.add('highlight');
      
      // Remove highlight after animation
      setTimeout(() => {
        ordersContainer.classList.remove('highlight');
      }, 2000);
      
      // Show toast notification
      showToast('Showing all orders', 'info');
    }
    
    // ========== TOAST NOTIFICATION ==========
    function showToast(message, type = 'success') {
      const toast = document.getElementById('toast');
      toast.textContent = message;
      toast.className = 'toast';
      
      if (type === 'error') {
        toast.classList.add('error');
      } else if (type === 'info') {
        toast.classList.add('info');
      }
      
      toast.classList.add('show');
      
      setTimeout(() => {
        toast.classList.remove('show');
      }, 3000);
    }
    
    // Close modal when clicking outside
    window.addEventListener('click', function(event) {
      const modal = document.getElementById('editModal');
      if (event.target === modal) {
        closeModal();
      }
    });
    
    // Close modal with Escape key
    document.addEventListener('keydown', function(event) {
      if (event.key === 'Escape') {
        closeModal();
      }
    });
  </script>
</body>
</html>