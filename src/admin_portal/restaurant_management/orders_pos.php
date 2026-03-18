<!DOCTYPE html>
<html lang="en">

  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin · Orders / POS</title>
    <!-- Tailwind via CDN + Font Awesome 6 -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
      /* exact same dropdown styles from index2.php */
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

      /* Animation for queue updates */
      @keyframes pulse {
        0% {
          background-color: #fef3c7;
        }

        100% {
          background-color: transparent;
        }
      }

      .queue-update {
        animation: pulse 1s ease;
      }

      /* Disabled button style */
      button:disabled {
        opacity: 0.5;
        cursor: not-allowed;
      }

      button:disabled:hover {
        background-color: transparent;
      }
    </style>
  </head>

  <body class="bg-white font-sans antialiased">

    <!-- Toast notification container -->
    <div id="toast" class="toast"></div>

    <!-- Order Details Modal -->
    <div id="orderDetailsModal" class="modal">
      <div class="modal-content p-6">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-xl font-semibold" id="modalOrderTitle">Order Details</h3>
          <button onclick="closeModal('orderDetailsModal')" class="text-slate-400 hover:text-slate-600">
            <i class="fa-solid fa-xmark text-2xl"></i>
          </button>
        </div>
        <div id="orderDetailsContent" class="space-y-4">
          <!-- Dynamic content will be inserted here -->
        </div>
        <div class="flex gap-3 mt-6">
          <button onclick="closeModal('orderDetailsModal')"
            class="flex-1 border border-slate-200 py-2 rounded-lg hover:bg-slate-50">Close</button>
        </div>
      </div>
    </div>

    <!-- APP CONTAINER: flex row (sidebar + main) -->
    <div class="min-h-screen flex flex-col lg:flex-row">

      <!-- ========== SIDEBAR (exact copy from index2.php) ========== -->
      <?php require '../components/admin_nav.php' ?>

      <!-- ========== MAIN CONTENT (ORDERS / POS) ========== -->
      <main class="flex-1 p-5 lg:p-8 overflow-y-auto bg-white">

        <!-- header with title and date -->
        <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
          <div>
            <h1 class="text-2xl lg:text-3xl font-light text-slate-800">Orders / POS</h1>
            <p class="text-sm text-slate-500 mt-0.5">manage dine-in, take-out, and delivery orders</p>
          </div>
          <div class="flex gap-3 text-sm">
            <span class="bg-white border rounded-full px-4 py-2 flex items-center gap-2 shadow-sm"><i
                class="fa-regular fa-calendar text-slate-400"></i> May 21, 2025</span>
            <span class="bg-white border rounded-full px-4 py-2 shadow-sm"><i class="fa-regular fa-bell"></i></span>
          </div>
        </div>

        <!-- ===== STATS CARDS ===== -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
          <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs text-slate-500">Active orders</p>
            <p class="text-2xl font-semibold" id="statsActiveOrders">18</p>
          </div>
          <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs text-slate-500">Dine-in</p>
            <p class="text-2xl font-semibold" id="statsDineIn">10</p>
          </div>
          <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs text-slate-500">Take-out</p>
            <p class="text-2xl font-semibold" id="statsTakeout">5</p>
          </div>
          <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs text-slate-500">Delivery</p>
            <p class="text-2xl font-semibold" id="statsDelivery">3</p>
          </div>
          <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs text-slate-500">Today's revenue</p>
            <p class="text-2xl font-semibold" id="statsRevenue">₱24,580</p>
          </div>
        </div>

        <!-- ===== ORDER TYPE TABS ===== -->
        <div class="flex flex-wrap gap-2 border-b border-slate-200 pb-2 mb-6">
          <button id="filterAll" onclick="filterOrders('all', event)"
            class="filter-btn px-4 py-2 bg-amber-600 text-white rounded-full text-sm">all orders</button>
          <button id="filterDineIn" onclick="filterOrders('dine-in', event)"
            class="filter-btn px-4 py-2 bg-white border border-slate-200 rounded-full text-sm hover:bg-slate-50">dine-in</button>
          <button id="filterTakeout" onclick="filterOrders('takeout', event)"
            class="filter-btn px-4 py-2 bg-white border border-slate-200 rounded-full text-sm hover:bg-slate-50">take-out</button>
          <button id="filterDelivery" onclick="filterOrders('delivery', event)"
            class="filter-btn px-4 py-2 bg-white border border-slate-200 rounded-full text-sm hover:bg-slate-50">delivery</button>
          <button id="filterCompleted" onclick="filterOrders('completed', event)"
            class="filter-btn px-4 py-2 bg-white border border-slate-200 rounded-full text-sm hover:bg-slate-50">completed</button>
        </div>

        <!-- ===== ACTIVE ORDERS TABLE ===== -->
        <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden mb-8">
          <div class="p-4 border-b border-slate-200 bg-slate-50 flex items-center justify-between">
            <h2 class="font-semibold flex items-center gap-2"><i class="fa-solid fa-cash-register text-amber-600"></i>
              active orders</h2>
            <div class="flex gap-2">
              <button onclick="refreshOrders()"
                class="border border-slate-200 px-4 py-2 rounded-xl text-sm hover:bg-slate-50">
                <i class="fa-solid fa-rotate-right mr-1"></i> refresh
              </button>
            </div>
          </div>
          <div class="overflow-x-auto">
            <table class="w-full text-sm" id="ordersTable">
              <thead class="text-slate-500 text-xs border-b">
                <tr>
                  <td class="p-3">Order #</td>
                  <td class="p-3">Time</td>
                  <td class="p-3">Type</td>
                  <td class="p-3">Table / Guest</td>
                  <td class="p-3">Items</td>
                  <td class="p-3">Total</td>
                  <td class="p-3">Status</td>
                  <td class="p-3">Actions</td>
                </tr>
              </thead>
              <tbody class="divide-y" id="ordersTableBody">
                <tr data-order-id="OR1001" data-type="dine-in" data-status="preparing">
                  <td class="p-3 font-medium">#OR1001</td>
                  <td class="p-3">12:30 PM</td>
                  <td class="p-3"><span
                      class="type-badge bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full text-xs">dine-in</span></td>
                  <td class="p-3">Table 4 · Cruz</td>
                  <td class="p-3">3 items</td>
                  <td class="p-3 font-medium">₱850</td>
                  <td class="p-3"><span
                      class="status-badge bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full text-xs">preparing</span>
                  </td>
                  <td class="p-3">
                    <button onclick="viewOrder('OR1001')"
                      class="text-amber-700 text-xs hover:underline mr-2">view</button>
                    <button onclick="updateOrderStatus('OR1001', 'ready')"
                      class="text-green-600 text-xs hover:underline">ready</button>
                  </td>
                </tr>
                <tr data-order-id="OR1002" data-type="takeout" data-status="preparing">
                  <td class="p-3 font-medium">#OR1002</td>
                  <td class="p-3">12:45 PM</td>
                  <td class="p-3"><span
                      class="type-badge bg-green-100 text-green-700 px-2 py-0.5 rounded-full text-xs">take-out</span>
                  </td>
                  <td class="p-3">Kim, Jiyeon</td>
                  <td class="p-3">2 items</td>
                  <td class="p-3 font-medium">₱540</td>
                  <td class="p-3"><span
                      class="status-badge bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full text-xs">preparing</span>
                  </td>
                  <td class="p-3">
                    <button onclick="viewOrder('OR1002')"
                      class="text-amber-700 text-xs hover:underline mr-2">view</button>
                    <button onclick="updateOrderStatus('OR1002', 'ready')"
                      class="text-green-600 text-xs hover:underline">ready</button>
                  </td>
                </tr>
                <tr data-order-id="OR1003" data-type="dine-in" data-status="served">
                  <td class="p-3 font-medium">#OR1003</td>
                  <td class="p-3">1:00 PM</td>
                  <td class="p-3"><span
                      class="type-badge bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full text-xs">dine-in</span></td>
                  <td class="p-3">Table 9 · Reyes</td>
                  <td class="p-3">5 items</td>
                  <td class="p-3 font-medium">₱1,890</td>
                  <td class="p-3"><span
                      class="status-badge bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full text-xs">served</span></td>
                  <td class="p-3">
                    <button onclick="viewOrder('OR1003')"
                      class="text-amber-700 text-xs hover:underline mr-2">view</button>
                    <button onclick="updateOrderStatus('OR1003', 'completed')"
                      class="text-blue-600 text-xs hover:underline">payment</button>
                  </td>
                </tr>
                <tr data-order-id="OR1004" data-type="delivery" data-status="preparing">
                  <td class="p-3 font-medium">#OR1004</td>
                  <td class="p-3">1:15 PM</td>
                  <td class="p-3"><span
                      class="type-badge bg-purple-100 text-purple-700 px-2 py-0.5 rounded-full text-xs">delivery</span>
                  </td>
                  <td class="p-3">Santos, Anna</td>
                  <td class="p-3">4 items</td>
                  <td class="p-3 font-medium">₱1,250</td>
                  <td class="p-3"><span
                      class="status-badge bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full text-xs">preparing</span>
                  </td>
                  <td class="p-3">
                    <button onclick="viewOrder('OR1004')"
                      class="text-amber-700 text-xs hover:underline mr-2">view</button>
                    <button onclick="updateOrderStatus('OR1004', 'ready')"
                      class="text-green-600 text-xs hover:underline">ready</button>
                  </td>
                </tr>
                <tr data-order-id="OR1005" data-type="takeout" data-status="preparing">
                  <td class="p-3 font-medium">#OR1005</td>
                  <td class="p-3">1:30 PM</td>
                  <td class="p-3"><span
                      class="type-badge bg-green-100 text-green-700 px-2 py-0.5 rounded-full text-xs">take-out</span>
                  </td>
                  <td class="p-3">Tan, Michelle</td>
                  <td class="p-3">2 items</td>
                  <td class="p-3 font-medium">₱390</td>
                  <td class="p-3"><span
                      class="status-badge bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full text-xs">preparing</span>
                  </td>
                  <td class="p-3">
                    <button onclick="viewOrder('OR1005')"
                      class="text-amber-700 text-xs hover:underline mr-2">view</button>
                    <button onclick="updateOrderStatus('OR1005', 'ready')"
                      class="text-green-600 text-xs hover:underline">ready</button>
                  </td>
                </tr>
                <tr data-order-id="OR1006" data-type="dine-in" data-status="preparing">
                  <td class="p-3 font-medium">#OR1006</td>
                  <td class="p-3">1:45 PM</td>
                  <td class="p-3"><span
                      class="type-badge bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full text-xs">dine-in</span></td>
                  <td class="p-3">Table 2 · Garcia</td>
                  <td class="p-3">3 items</td>
                  <td class="p-3 font-medium">₱760</td>
                  <td class="p-3"><span
                      class="status-badge bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full text-xs">preparing</span>
                  </td>
                  <td class="p-3">
                    <button onclick="viewOrder('OR1006')"
                      class="text-amber-700 text-xs hover:underline mr-2">view</button>
                    <button onclick="updateOrderStatus('OR1006', 'ready')"
                      class="text-green-600 text-xs hover:underline">ready</button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="p-4 border-t border-slate-200 flex items-center justify-between">
            <span class="text-xs text-slate-500" id="paginationInfo">Showing 1-6 of 6 orders</span>
            <div class="flex gap-2" id="paginationButtons">
              <!-- Pagination buttons will be dynamically generated -->
            </div>
          </div>
        </div>

        <!-- ===== ORDER QUEUE ===== -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <!-- kitchen queue summary (connected to kitchen orders) -->
          <div class="bg-amber-50 border border-amber-200 rounded-2xl p-5">
            <div class="flex justify-between items-center mb-3">
              <h3 class="font-semibold flex items-center gap-2"><i class="fa-regular fa-clock text-amber-600"></i>
                kitchen queue</h3>
              <a href="../restaurant_management/kitchen_orders.php" class="text-xs text-amber-600 hover:underline">view
                full kitchen display →</a>
            </div>
            <ul class="space-y-3" id="kitchenQueue">
              <!-- Dynamic queue items will be inserted here -->
            </ul>
            <div class="mt-4 pt-3 border-t border-amber-200 flex justify-between text-xs text-amber-700">
              <span><span class="font-semibold" id="queueCount">5</span> orders in kitchen</span>
              <span>avg prep time: <span id="avgPrepTime">12</span> min</span>
            </div>
          </div>

          <!-- ready for pickup/delivery queue -->
          <div class="bg-green-50 border border-green-200 rounded-2xl p-5">
            <h3 class="font-semibold flex items-center gap-2 mb-3"><i
                class="fa-regular fa-circle-check text-green-600"></i> ready for pickup/delivery</h3>
            <ul class="space-y-3" id="readyQueue">
              <!-- Dynamic ready items will be inserted here -->
            </ul>
            <div class="mt-4 pt-3 border-t border-green-200">
              <span class="text-xs text-green-700"><span class="font-semibold" id="readyCount">1</span> order
                waiting</span>
            </div>
          </div>
        </div>
      </main>
    </div>

    <script>
      // ========== ORDER DATA ==========
      const orders = [
        { id: 'OR1001', time: '12:30 PM', type: 'dine-in', customer: 'Cruz', table: 'Table 4', items: 3, total: 850, status: 'preparing', orderItems: ['Sinigang na Baboy', 'Garlic Rice x2'] },
        { id: 'OR1002', time: '12:45 PM', type: 'takeout', customer: 'Kim, Jiyeon', table: null, items: 2, total: 540, status: 'preparing', orderItems: ['Sizzling Sisig', 'Garlic Rice'] },
        { id: 'OR1003', time: '1:00 PM', type: 'dine-in', customer: 'Reyes', table: 'Table 9', items: 5, total: 1890, status: 'served', orderItems: ['Crispy Pata x2', 'Garlic Rice x3'] },
        { id: 'OR1004', time: '1:15 PM', type: 'delivery', customer: 'Santos, Anna', table: null, items: 4, total: 1250, status: 'preparing', orderItems: ['Seafood Kare-Kare', 'Halo-Halo x2', 'Garlic Rice'] },
        { id: 'OR1005', time: '1:30 PM', type: 'takeout', customer: 'Tan, Michelle', table: null, items: 2, total: 390, status: 'preparing', orderItems: ['Sinigang', 'Rice'] },
        { id: 'OR1006', time: '1:45 PM', type: 'dine-in', customer: 'Garcia', table: 'Table 2', items: 3, total: 760, status: 'preparing', orderItems: ['Sizzling Sisig', 'Garlic Rice x2'] }
      ];

      // Queue data
      let kitchenQueue = [];
      let readyQueue = [];

      // ========== PAGINATION VARIABLES ==========
      let currentPage = 1;
      const itemsPerPage = 5;

      // ========== INITIALIZATION ==========
      document.addEventListener('DOMContentLoaded', function () {
        updateQueues();
        updateStats();
        updatePagination();
      });

      // ========== QUEUE FUNCTIONS (Connected to Kitchen Orders) ==========
      function updateQueues() {
        // Update kitchen queue (preparing orders)
        kitchenQueue = orders.filter(order => order.status === 'preparing');

        // Update ready queue (ready for pickup/serve)
        readyQueue = orders.filter(order => order.status === 'ready' || order.status === 'served');

        renderQueues();
      }

      function renderQueues() {
        // Render kitchen queue
        const kitchenQueueEl = document.getElementById('kitchenQueue');
        kitchenQueueEl.innerHTML = '';

        if (kitchenQueue.length === 0) {
          kitchenQueueEl.innerHTML = '<li class="text-sm text-slate-500 text-center py-4">No orders in kitchen</li>';
        } else {
          kitchenQueue.forEach(order => {
            const li = document.createElement('li');
            li.className = 'flex justify-between items-center border-b border-amber-200 pb-2 queue-item';
            li.setAttribute('data-order-id', order.id);

            let customerInfo = order.table ? `${order.table} · ${order.customer}` : order.customer;

            li.innerHTML = `
            <div>
              <span class="font-medium">#${order.id}</span>
              <p class="text-xs text-slate-500">${customerInfo} · ${order.items} items</p>
            </div>
            <div class="flex items-center gap-2">
              <span class="bg-amber-100 text-amber-700 text-xs px-2 py-0.5 rounded-full">preparing</span>
              <button onclick="updateOrderStatus('${order.id}', 'ready')" class="text-xs text-green-600 hover:underline">✓ ready</button>
            </div>
          `;
            kitchenQueueEl.appendChild(li);
          });
        }

        // Render ready queue
        const readyQueueEl = document.getElementById('readyQueue');
        readyQueueEl.innerHTML = '';

        if (readyQueue.length === 0) {
          readyQueueEl.innerHTML = '<li class="text-sm text-slate-500 text-center py-4">No orders ready</li>';
        } else {
          readyQueue.forEach(order => {
            const li = document.createElement('li');
            li.className = 'flex justify-between items-center border-b border-green-200 pb-2';
            li.setAttribute('data-order-id', order.id);

            let customerInfo = order.table ? `${order.table} · ${order.customer}` : order.customer;
            let actionText = order.type === 'delivery' ? 'dispatch' : 'serve';
            let nextStatus = order.type === 'delivery' ? 'completed' : 'served';

            li.innerHTML = `
            <div>
              <span class="font-medium">#${order.id}</span>
              <p class="text-xs text-slate-500">${customerInfo} · ${order.items} items</p>
            </div>
            <div class="flex items-center gap-2">
              <span class="bg-green-100 text-green-700 text-xs px-2 py-0.5 rounded-full">${order.status === 'served' ? 'served' : 'ready'}</span>
              <button onclick="updateOrderStatus('${order.id}', '${nextStatus}')" class="text-xs text-blue-600 hover:underline">${actionText}</button>
            </div>
          `;
            readyQueueEl.appendChild(li);
          });
        }

        // Update counts
        document.getElementById('queueCount').textContent = kitchenQueue.length;
        document.getElementById('readyCount').textContent = readyQueue.length;
      }

      // ========== ORDER ACTIONS ==========
      function updateOrderStatus(orderId, newStatus) {
        const order = orders.find(o => o.id === orderId);
        if (!order) return;

        // Update order status
        order.status = newStatus;

        // Find the row in the table
        const row = document.querySelector(`tr[data-order-id="${orderId}"]`);
        if (row) {
          // Update data-status attribute
          row.setAttribute('data-status', newStatus);

          // Update status badge
          const statusBadge = row.querySelector('.status-badge');
          if (statusBadge) {
            let statusClass = '';

            if (newStatus === 'preparing') {
              statusClass = 'bg-amber-100 text-amber-700';
            } else if (newStatus === 'ready') {
              statusClass = 'bg-green-100 text-green-700';
            } else if (newStatus === 'served') {
              statusClass = 'bg-blue-100 text-blue-700';
            } else if (newStatus === 'completed') {
              statusClass = 'bg-slate-100 text-slate-700';
            }

            statusBadge.className = `status-badge ${statusClass} px-2 py-0.5 rounded-full text-xs`;
            statusBadge.textContent = newStatus;
          }

          // Update action buttons
          const actionCell = row.cells[7];
          updateActionButtons(actionCell, order);
        }

        // Update queues
        updateQueues();
        updateStats();

        // Highlight queue item
        highlightQueueItem(orderId);

        // Show success message
        showToast(`Order #${orderId} updated to ${newStatus}`, 'success');

        // Notify kitchen if needed
        if (newStatus === 'preparing') {
          notifyKitchen(orderId, 'added to kitchen queue');
        } else if (newStatus === 'ready') {
          notifyKitchen(orderId, 'ready for pickup');
        } else if (newStatus === 'completed') {
          notifyKitchen(orderId, 'order completed');
        }

        // Update pagination to reflect changes
        updatePagination();
      }

      function updateActionButtons(actionCell, order) {
        // Clear existing buttons
        actionCell.innerHTML = '';

        // View button (always present)
        const viewBtn = document.createElement('button');
        viewBtn.className = 'text-amber-700 text-xs hover:underline mr-2';
        viewBtn.textContent = 'view';
        viewBtn.setAttribute('onclick', `viewOrder('${order.id}')`);
        actionCell.appendChild(viewBtn);

        // Status-specific action button
        if (order.status === 'preparing') {
          // Preparing -> Ready
          const actionBtn = document.createElement('button');
          actionBtn.className = 'text-green-600 text-xs hover:underline';
          actionBtn.textContent = 'ready';
          actionBtn.setAttribute('onclick', `updateOrderStatus('${order.id}', 'ready')`);
          actionCell.appendChild(actionBtn);
        } else if (order.status === 'ready') {
          // Ready -> Served (for dine-in) or Completed (for delivery/takeout)
          const actionBtn = document.createElement('button');
          if (order.type === 'delivery') {
            actionBtn.className = 'text-purple-600 text-xs hover:underline';
            actionBtn.textContent = 'dispatch';
            actionBtn.setAttribute('onclick', `updateOrderStatus('${order.id}', 'completed')`);
          } else {
            actionBtn.className = 'text-blue-600 text-xs hover:underline';
            actionBtn.textContent = 'serve';
            actionBtn.setAttribute('onclick', `updateOrderStatus('${order.id}', 'served')`);
          }
          actionCell.appendChild(actionBtn);
        } else if (order.status === 'served') {
          // Served -> Completed
          const actionBtn = document.createElement('button');
          actionBtn.className = 'text-blue-600 text-xs hover:underline';
          actionBtn.textContent = 'payment';
          actionBtn.setAttribute('onclick', `updateOrderStatus('${order.id}', 'completed')`);
          actionCell.appendChild(actionBtn);
        }
      }

      // ========== KITCHEN NOTIFICATION ==========
      function notifyKitchen(orderId, message) {
        // Simulate sending notification to kitchen display
        console.log(`[KITCHEN NOTIFICATION] Order #${orderId}: ${message}`);
        showToast(`Kitchen notified: Order #${orderId} ${message}`, 'info');

        // Store in sessionStorage (simulating real-time communication)
        const kitchenNotification = {
          orderId: orderId,
          message: message,
          timestamp: new Date().toISOString()
        };

        let notifications = JSON.parse(sessionStorage.getItem('kitchenNotifications') || '[]');
        notifications.push(kitchenNotification);
        sessionStorage.setItem('kitchenNotifications', JSON.stringify(notifications));
      }

      // ========== VIEW ORDER DETAILS ==========
      function viewOrder(orderId) {
        const order = orders.find(o => o.id === orderId);
        if (!order) return;

        const modalContent = document.getElementById('orderDetailsContent');
        const orderItems = order.orderItems || ['Item 1', 'Item 2'];

        let itemsHtml = '';
        orderItems.forEach(item => {
          itemsHtml += `<li class="flex justify-between text-sm"><span>${item}</span></li>`;
        });

        modalContent.innerHTML = `
        <div class="border-b pb-3">
          <p class="text-sm text-slate-500">Order #${order.id}</p>
          <p class="text-lg font-semibold">${order.customer}</p>
        </div>
        <div class="grid grid-cols-2 gap-3 text-sm">
          <div>
            <p class="text-slate-500">Time</p>
            <p class="font-medium">${order.time}</p>
          </div>
          <div>
            <p class="text-slate-500">Type</p>
            <p class="font-medium capitalize">${order.type}</p>
          </div>
          <div>
            <p class="text-slate-500">${order.table ? 'Table' : 'Customer'}</p>
            <p class="font-medium">${order.table || order.customer}</p>
          </div>
          <div>
            <p class="text-slate-500">Total</p>
            <p class="font-medium">₱${order.total}</p>
          </div>
        </div>
        <div>
          <p class="text-sm text-slate-500 mb-2">Items</p>
          <ul class="space-y-1">
            ${itemsHtml}
          </ul>
        </div>
        <div>
          <p class="text-sm text-slate-500">Status</p>
          <p class="text-sm font-medium capitalize mt-1">${order.status}</p>
        </div>
      `;

        document.getElementById('modalOrderTitle').textContent = `Order #${orderId}`;
        openModal('orderDetailsModal');
      }

      // ========== FILTER ORDERS ==========
      function filterOrders(type, event) {
        // Update active button styling
        document.querySelectorAll('.filter-btn').forEach(btn => {
          btn.classList.remove('bg-amber-600', 'text-white');
          btn.classList.add('bg-white', 'border', 'border-slate-200', 'text-slate-700');
        });

        event.target.classList.remove('bg-white', 'border', 'border-slate-200', 'text-slate-700');
        event.target.classList.add('bg-amber-600', 'text-white');

        const rows = document.querySelectorAll('#ordersTableBody tr');

        rows.forEach(row => {
          if (type === 'all') {
            row.style.display = '';
          } else {
            const rowType = row.getAttribute('data-type');
            const rowStatus = row.getAttribute('data-status');

            if (type === 'completed') {
              if (rowStatus === 'completed') {
                row.style.display = '';
              } else {
                row.style.display = 'none';
              }
            } else if (rowType === type) {
              row.style.display = '';
            } else {
              row.style.display = 'none';
            }
          }
        });

        // Reset to first page and update pagination
        currentPage = 1;
        updatePagination();
      }

      // ========== REFRESH ORDERS ==========
      function refreshOrders() {
        showToast('Refreshing orders...', 'info');

        // Simulate refresh
        setTimeout(() => {
          updateQueues();
          updateStats();
          updatePagination();
          showToast('Orders refreshed!', 'success');
        }, 1000);
      }

      // ========== UPDATE STATISTICS ==========
      function updateStats() {
        const activeOrders = orders.filter(o => o.status !== 'completed').length;
        const dineIn = orders.filter(o => o.type === 'dine-in' && o.status !== 'completed').length;
        const takeout = orders.filter(o => o.type === 'takeout' && o.status !== 'completed').length;
        const delivery = orders.filter(o => o.type === 'delivery' && o.status !== 'completed').length;

        document.getElementById('statsActiveOrders').textContent = activeOrders;
        document.getElementById('statsDineIn').textContent = dineIn;
        document.getElementById('statsTakeout').textContent = takeout;
        document.getElementById('statsDelivery').textContent = delivery;
      }

      // ========== HIGHLIGHT QUEUE ITEM ==========
      function highlightQueueItem(orderId) {
        const queueItems = document.querySelectorAll(`.queue-item[data-order-id="${orderId}"]`);
        queueItems.forEach(item => {
          item.classList.add('queue-update');
          setTimeout(() => {
            item.classList.remove('queue-update');
          }, 1000);
        });
      }

      // ========== FIXED PAGINATION FUNCTIONS ==========
      function updatePagination() {
        const rows = document.querySelectorAll('#ordersTableBody tr:not([style*="display: none"])');
        const totalItems = rows.length;
        const totalPages = Math.max(1, Math.ceil(totalItems / itemsPerPage));

        // Ensure current page is valid
        if (currentPage > totalPages) {
          currentPage = totalPages;
        }
        if (currentPage < 1) {
          currentPage = 1;
        }

        // Hide/show rows based on current page
        rows.forEach((row, index) => {
          if (index >= (currentPage - 1) * itemsPerPage && index < currentPage * itemsPerPage) {
            row.style.display = '';
          } else {
            row.style.display = 'none';
          }
        });

        // Update pagination info
        const start = totalItems > 0 ? (currentPage - 1) * itemsPerPage + 1 : 0;
        const end = totalItems > 0 ? Math.min(currentPage * itemsPerPage, totalItems) : 0;
        document.getElementById('paginationInfo').textContent =
          totalItems > 0 ? `Showing ${start}-${end} of ${totalItems} orders` : 'Showing 0 orders';

        // Generate pagination buttons
        generatePaginationButtons(totalPages);
      }

      function generatePaginationButtons(totalPages) {
        const container = document.getElementById('paginationButtons');
        if (!container) return;

        let buttons = '';

        // Previous button
        buttons += `<button onclick="changePage('prev')" class="border border-slate-200 px-3 py-1 rounded-lg text-sm hover:bg-slate-50" ${currentPage === 1 ? 'disabled' : ''}>Previous</button>`;

        // Page buttons
        for (let i = 1; i <= totalPages; i++) {
          buttons += `<button onclick="changePage(${i})" class="border px-3 py-1 rounded-lg text-sm page-btn ${i === currentPage ? 'bg-amber-600 text-white' : 'border-slate-200 hover:bg-slate-50'}">${i}</button>`;
        }

        // Next button
        buttons += `<button onclick="changePage('next')" class="border border-slate-200 px-3 py-1 rounded-lg text-sm hover:bg-slate-50" ${currentPage === totalPages ? 'disabled' : ''}>Next</button>`;

        container.innerHTML = buttons;
      }

      function changePage(direction) {
        const rows = document.querySelectorAll('#ordersTableBody tr:not([style*="display: none"])');
        const totalPages = Math.max(1, Math.ceil(rows.length / itemsPerPage));

        if (direction === 'prev' && currentPage > 1) {
          currentPage--;
        } else if (direction === 'next' && currentPage < totalPages) {
          currentPage++;
        } else if (typeof direction === 'number') {
          if (direction >= 1 && direction <= totalPages) {
            currentPage = direction;
          }
        }

        updatePagination();
      }

      // ========== MODAL FUNCTIONS ==========
      function openModal(modalId) {
        document.getElementById(modalId).classList.add('show');
      }

      function closeModal(modalId) {
        document.getElementById(modalId).classList.remove('show');
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

      // Close modals when clicking outside
      window.addEventListener('click', function (event) {
        if (event.target.classList.contains('modal')) {
          event.target.classList.remove('show');
        }
      });

      // Close modals with Escape key
      document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
          document.querySelectorAll('.modal.show').forEach(modal => {
            modal.classList.remove('show');
          });
        }
      });
    </script>
  </body>

</html>