<?php
/**
 * View - Admin Kitchen Orders (KOT)
 */
require_once '../../../controller/admin/get/restaurant/kitchen_orders.php';

// Set current page for navigation
$current_page = 'kitchen_orders';
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin · Kitchen Orders (KOT)</title>
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
      max-width: 600px;
      width: 90%;
      max-height: 85vh;
      overflow-y: auto;
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
      padding: 12px 24px;
      border-radius: 8px;
    }

    .toast.show {
      transform: translateX(0);
    }

    .toast.error {
      border-left-color: #ef4444;
    }

    .toast.success {
      border-left-color: #10b981;
    }

    .toast.info {
      border-left-color: #3b82f6;
    }

    @keyframes highlight {
      0% {
        background-color: #fef3c7;
      }

      100% {
        background-color: transparent;
      }
    }

    .highlight {
      animation: highlight 2s ease;
    }

    .status-badge {
      transition: all 0.2s;
    }

    .status-badge:hover {
      opacity: 0.8;
      transform: scale(1.05);
    }

    .filter-btn.active {
      background-color: #d97706;
      color: white;
    }
  </style>
</head>

<body class="bg-white font-sans antialiased">

  <!-- Toast notification container -->
  <div id="toast" class="toast hidden">
    <div class="flex items-center gap-3">
      <div class="h-8 w-8 rounded-full bg-amber-100 flex items-center justify-center text-amber-600">
        <i class="fas fa-bell"></i>
      </div>
      <div>
        <p id="toastMessage" class="text-sm font-medium text-slate-800">Notification</p>
        <p id="toastTime" class="text-xs text-slate-400">just now</p>
      </div>
    </div>
  </div>

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
        <input type="hidden" id="editOrderRef">

        <div class="mb-4">
          <label class="block text-sm font-medium text-slate-700 mb-1">Customer Name</label>
          <input type="text" id="editCustomer" class="w-full border border-slate-200 rounded-lg px-3 py-2" readonly>
        </div>

        <div class="mb-4">
          <label class="block text-sm font-medium text-slate-700 mb-1">Order Type</label>
          <input type="text" id="editType" class="w-full border border-slate-200 rounded-lg px-3 py-2" readonly>
        </div>

        <div class="mb-4">
          <label class="block text-sm font-medium text-slate-700 mb-1">Items</label>
          <div id="editItemsList" class="border border-slate-200 rounded-lg p-3 bg-slate-50 max-h-40 overflow-y-auto">
            <!-- Items will be populated here -->
          </div>
        </div>

        <div class="mb-4">
          <label class="block text-sm font-medium text-slate-700 mb-1">Status</label>
          <select id="editStatus" class="w-full border border-slate-200 rounded-lg px-3 py-2">
            <option value="new">New</option>
            <option value="preparing">Preparing</option>
            <option value="ready">Ready</option>
            <option value="urgent">Urgent</option>
            <option value="completed">Completed</option>
          </select>
        </div>

        <div class="mb-6">
          <label class="block text-sm font-medium text-slate-700 mb-1">Special Instructions</label>
          <textarea id="editInstructions" rows="2" readonly
            class="w-full border border-slate-200 rounded-lg px-3 py-2 bg-slate-50"></textarea>
        </div>

        <div class="flex gap-3">
          <button type="submit" class="flex-1 bg-amber-600 text-white py-2 rounded-lg hover:bg-amber-700">Update Status</button>
          <button type="button" onclick="closeModal()"
            class="flex-1 border border-slate-200 py-2 rounded-lg hover:bg-slate-50">Cancel</button>
        </div>
      </form>
    </div>
  </div>

  <!-- APP CONTAINER -->
  <div class="min-h-screen flex flex-col lg:flex-row">

    <!-- ========== SIDEBAR ========== -->
    <?php require_once '../components/admin_nav.php'; ?>

    <!-- ========== MAIN CONTENT ========== -->
    <main class="flex-1 p-5 lg:p-8 overflow-y-auto bg-white">

      <!-- header -->
      <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <div>
          <h1 class="text-2xl lg:text-3xl font-light text-slate-800">Kitchen Orders (KOT)</h1>
          <p class="text-sm text-slate-500 mt-0.5">real-time kitchen display system · manage food preparation</p>
        </div>
        <div class="flex gap-3 text-sm">
          <span class="bg-white border rounded-full px-4 py-2 flex items-center gap-2 shadow-sm">
            <i class="fas fa-calendar text-slate-400"></i> <?php echo $today; ?>
          </span>
          <span class="bg-white border rounded-full px-4 py-2 shadow-sm cursor-pointer hover:bg-slate-50 relative" id="notificationBell">
            <i class="fas fa-bell"></i>
            <?php if ($unread_count > 0): ?>
                <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-4 h-4 flex items-center justify-center"><?php echo $unread_count; ?></span>
            <?php endif; ?>
          </span>
        </div>
      </div>

      <!-- STATS CARDS -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm cursor-pointer hover:shadow-md transition"
             onclick="filterByStatus('new')">
          <p class="text-xs text-slate-500">New orders</p>
          <p class="text-2xl font-semibold text-blue-600" id="newOrdersCount"><?php echo $stats['new_orders']; ?></p>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm cursor-pointer hover:shadow-md transition"
             onclick="filterByStatus('preparing')">
          <p class="text-xs text-slate-500">Preparing</p>
          <p class="text-2xl font-semibold text-amber-600" id="preparingCount"><?php echo $stats['preparing_orders']; ?></p>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm cursor-pointer hover:shadow-md transition"
             onclick="filterByStatus('ready')">
          <p class="text-xs text-slate-500">Ready to serve</p>
          <p class="text-2xl font-semibold text-green-600" id="readyCount"><?php echo $stats['ready_orders']; ?></p>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
          <p class="text-xs text-slate-500">Completed (today)</p>
          <p class="text-2xl font-semibold" id="completedTodayCount"><?php echo $stats['completed_today']; ?></p>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
          <p class="text-xs text-slate-500">Avg prep time</p>
          <p class="text-2xl font-semibold" id="avgPrepTime"><?php echo $stats['avg_prep_time']; ?> min</p>
        </div>
      </div>

      <!-- FILTER TABS -->
      <div class="flex flex-wrap gap-2 border-b border-slate-200 pb-2 mb-6">
        <button onclick="filterOrders('all')" 
                class="filter-btn px-4 py-2 <?php echo $statusFilter == 'all' ? 'bg-amber-600 text-white' : 'bg-white border border-slate-200 hover:bg-slate-50'; ?> rounded-full text-sm">
          all orders
        </button>
        <button onclick="filterOrders('new')" 
                class="filter-btn px-4 py-2 <?php echo $statusFilter == 'new' ? 'bg-amber-600 text-white' : 'bg-white border border-slate-200 hover:bg-slate-50'; ?> rounded-full text-sm">
          new
        </button>
        <button onclick="filterOrders('preparing')" 
                class="filter-btn px-4 py-2 <?php echo $statusFilter == 'preparing' ? 'bg-amber-600 text-white' : 'bg-white border border-slate-200 hover:bg-slate-50'; ?> rounded-full text-sm">
          preparing
        </button>
        <button onclick="filterOrders('ready')" 
                class="filter-btn px-4 py-2 <?php echo $statusFilter == 'ready' ? 'bg-amber-600 text-white' : 'bg-white border border-slate-200 hover:bg-slate-50'; ?> rounded-full text-sm">
          ready
        </button>
        <button onclick="filterOrders('urgent')" 
                class="filter-btn px-4 py-2 <?php echo $statusFilter == 'urgent' ? 'bg-amber-600 text-white' : 'bg-white border border-slate-200 hover:bg-slate-50'; ?> rounded-full text-sm">
          urgent
        </button>
      </div>

      <!-- CUSTOMER ORDERS TABLE -->
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
              <th class="px-5 py-3 text-left font-medium">Wait Time</th>
              <th class="px-5 py-3 text-left font-medium">Action</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100" id="ordersTableBody">
            <?php if (empty($kitchenOrders)): ?>
                <tr>
                  <td colspan="8" class="px-5 py-8 text-center text-slate-500">No kitchen orders found</td>
                </tr>
            <?php else: ?>
                <?php foreach ($kitchenOrders as $order):
                  $items = json_decode($order['items'], true);
                  $itemSummary = is_array($items) ? count($items) . ' items' : 'N/A';
                  $customerDisplay = $order['customer_name'] ?? 'Guest';
                  if ($order['table_number']) {
                    $customerDisplay .= ' (Table ' . $order['table_number'] . ')';
                  }

                  $statusColors = [
                    'new' => 'bg-blue-100 text-blue-700',
                    'preparing' => 'bg-amber-100 text-amber-700',
                    'ready' => 'bg-green-100 text-green-700',
                    'urgent' => 'bg-red-100 text-red-700',
                    'completed' => 'bg-slate-100 text-slate-700'
                  ];
                  $statusColor = $statusColors[$order['status']] ?? 'bg-slate-100 text-slate-700';
                  $statusDisplay = strtoupper($order['status']);
                  ?>
                  <tr data-status="<?php echo $order['status']; ?>" data-order-id="<?php echo $order['order_reference']; ?>">
                    <td class="px-5 py-3 font-medium">#<?php echo $order['order_reference']; ?></td>
                    <td class="px-5 py-3"><?php echo htmlspecialchars($customerDisplay); ?></td>
                    <td class="px-5 py-3"><?php echo ucfirst($order['order_type']); ?></td>
                    <td class="px-5 py-3"><?php echo $itemSummary; ?></td>
                    <td class="px-5 py-3">
                      <span class="status-badge <?php echo $statusColor; ?> text-xs px-2 py-1 rounded-full"><?php echo $statusDisplay; ?></span>
                    </td>
                    <td class="px-5 py-3"><?php echo date('h:i A', strtotime($order['created_at'])); ?></td>
                    <td class="px-5 py-3"><?php echo $order['wait_time_minutes']; ?> min</td>
                    <td class="px-5 py-3">
                      <button class="edit-btn text-amber-600 hover:text-amber-800 mr-2" onclick="openEditModal('<?php echo $order['order_reference']; ?>')" title="Edit">
                        <i class="fas fa-pen-to-square"></i>
                      </button>
                      <?php if ($order['status'] !== 'urgent'): ?>
                          <button class="urgent-btn text-red-600 hover:text-red-800" onclick="markAsUrgent('<?php echo $order['order_reference']; ?>')" title="Mark Urgent">
                            <i class="fas fa-clock"></i>
                          </button>
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

      <!-- BOTTOM: EXPANDED PREPARATION QUEUE -->
      <div class="bg-white rounded-2xl border border-slate-200 p-6">
        <h2 class="font-semibold text-lg flex items-center gap-2 mb-4">
          <i class="fas fa-rectangle-list text-amber-600"></i> preparation queue
        </h2>

        <!-- Expanded queue table -->
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
                <th class="px-4 py-3 text-left font-medium">Action</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100" id="queueTableBody">
              <?php if (empty($prepQueue)): ?>
                  <tr>
                    <td colspan="9" class="px-4 py-8 text-center text-slate-500">No orders in preparation queue</td>
                  </tr>
              <?php else: ?>
                  <?php foreach ($prepQueue as $item):
                    $customerDisplay = $item['customer_name'] ?? 'Guest';
                    if ($item['table_number']) {
                      $customerDisplay = 'Table ' . $item['table_number'] . ' · ' . $customerDisplay;
                    }

                    $statusColors = [
                      'new' => 'bg-blue-100 text-blue-700',
                      'preparing' => 'bg-amber-100 text-amber-700',
                      'urgent' => 'bg-red-100 text-red-700'
                    ];
                    $statusColor = $statusColors[$item['status']] ?? 'bg-slate-100 text-slate-700';
                    $statusDisplay = strtoupper($item['status']);
                    ?>
                    <tr class="hover:bg-slate-50" data-queue-status="<?php echo $item['status']; ?>" data-order-id="<?php echo $item['order_reference']; ?>">
                      <td class="px-4 py-3 font-medium">#<?php echo $item['order_reference']; ?></td>
                      <td class="px-4 py-3"><?php echo htmlspecialchars($customerDisplay); ?></td>
                      <td class="px-4 py-3"><?php echo ucfirst($item['order_type']); ?></td>
                      <td class="px-4 py-3"><?php echo $item['item_count']; ?> items</td>
                      <td class="px-4 py-3">
                        <span class="<?php echo $statusColor; ?> text-xs px-2 py-1 rounded-full"><?php echo $statusDisplay; ?></span>
                      </td>
                      <td class="px-4 py-3"><?php echo $item['wait_time']; ?> min</td>
                      <td class="px-4 py-3"><?php echo $item['est_completion']; ?></td>
                      <td class="px-4 py-3">
                        <span class="<?php echo $item['priority_color']; ?>"><?php echo $item['priority']; ?></span>
                      </td>
                      <td class="px-4 py-3">
                        <button class="text-amber-600 hover:text-amber-800" onclick="openEditModal('<?php echo $item['order_reference']; ?>')">
                          <i class="fas fa-pen-to-square"></i>
                        </button>
                      </td>
                    </tr>
                  <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>

        <!-- Queue summary footer -->
        <div class="mt-4 pt-3 border-t border-slate-100 flex flex-wrap justify-between items-center text-sm text-slate-500">
          <div class="flex gap-4">
            <span><span class="font-medium text-slate-700" id="ordersInProgress"><?php echo $ordersInProgress; ?></span> orders in progress</span>
            <span><span class="font-medium text-slate-700" id="avgWaitTime"><?php echo $avgWaitTime; ?></span> avg min wait</span>
          </div>
          <div class="flex gap-2">
            <button id="refreshQueueBtn" class="text-amber-600 hover:text-amber-800 text-xs flex items-center gap-1" onclick="refreshQueue()">
              <i class="fas fa-clock"></i> refresh queue
            </button>
            <button id="viewAllBtn" class="text-amber-600 hover:text-amber-800 text-xs flex items-center gap-1" onclick="viewAllOrders()">
              <i class="fas fa-file-lines"></i> view all
            </button>
          </div>
        </div>
      </div>
    </main>
  </div>

  <script>
    // ========== FILTER FUNCTIONS ==========
    function filterOrders(status) {
      const url = new URL(window.location);
      if (status !== 'all') {
        url.searchParams.set('status', status);
      } else {
        url.searchParams.delete('status');
      }
      url.searchParams.set('page', '1');
      window.location.href = url.toString();
    }

    function filterByStatus(status) {
      filterOrders(status);
    }

    // ========== MODAL FUNCTIONS ==========
    function openEditModal(orderId) {
      Swal.fire({
        title: 'Loading...',
        text: 'Please wait',
        allowOutsideClick: false,
        didOpen: () => {
          Swal.showLoading();
        }
      });

      const formData = new FormData();
      formData.append('action', 'get_order_details');
      formData.append('order_id', orderId);

      fetch('../../../controller/admin/post/restaurant/kitchen_actions.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        Swal.close();
        if (data.success) {
          const order = data.order;
          const items = order.items || [];
          
          // Populate modal fields
          document.getElementById('editOrderId').value = order.id;
          document.getElementById('editOrderRef').value = order.order_reference;
          document.getElementById('editCustomer').value = order.customer_name || 'Guest';
          document.getElementById('editType').value = order.order_type || 'dine-in';
          document.getElementById('editStatus').value = order.status || 'new';
          document.getElementById('editInstructions').value = order.special_instructions || '';
          
          // Populate items list
          const itemsList = document.getElementById('editItemsList');
          itemsList.innerHTML = '';
          if (items.length > 0) {
            items.forEach(item => {
              const itemDiv = document.createElement('div');
              itemDiv.className = 'flex justify-between text-sm py-1 border-b last:border-0';
              itemDiv.innerHTML = `
                <span>${item.name || 'Item'}</span>
                <span class="font-medium">x${item.quantity || 1}</span>
              `;
              itemsList.appendChild(itemDiv);
            });
          } else {
            itemsList.innerHTML = '<p class="text-sm text-slate-400">No items</p>';
          }

          // Show modal
          document.getElementById('editModal').classList.add('show');
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
        showToast('An error occurred', 'error');
      });
    }

    function closeModal() {
      document.getElementById('editModal').classList.remove('show');
    }

    function saveOrderChanges(event) {
      event.preventDefault();

      const orderRef = document.getElementById('editOrderRef').value;
      const newStatus = document.getElementById('editStatus').value;

      Swal.fire({
        title: 'Updating Status...',
        text: 'Please wait',
        allowOutsideClick: false,
        didOpen: () => {
          Swal.showLoading();
        }
      });

      const formData = new FormData();
      formData.append('action', 'update_status');
      formData.append('order_id', orderRef);
      formData.append('status', newStatus);

      fetch('../../../controller/admin/post/restaurant/kitchen_actions.php', {
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
        showToast('An error occurred', 'error');
      });

      closeModal();
    }

    // ========== MARK AS URGENT ==========
    function markAsUrgent(orderId) {
      Swal.fire({
        title: 'Mark as Urgent?',
        text: 'This will prioritize this order in the kitchen queue.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d97706',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, mark urgent'
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
          formData.append('action', 'mark_urgent');
          formData.append('order_id', orderId);

          fetch('../../../controller/admin/post/restaurant/kitchen_actions.php', {
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
        }
      });
    }

    // ========== QUEUE FUNCTIONS ==========
    function refreshQueue() {
      showToast('Refreshing queue...', 'info');
      setTimeout(() => {
        location.reload();
      }, 1000);
    }

    function viewAllOrders() {
      filterOrders('all');
    }

    // ========== TOAST NOTIFICATION ==========
    function showToast(message, type = 'success') {
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

    // ========== NOTIFICATION BELL ==========
    document.getElementById('notificationBell')?.addEventListener('click', function() {
      window.location.href = '../notifications.php';
    });

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