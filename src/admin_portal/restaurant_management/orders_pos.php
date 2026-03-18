<?php
/**
 * View - Admin Orders / POS
 */
require_once '../../../controller/admin/get/restaurant/orders_pos.php';

// Set current page for navigation
$current_page = 'orders_pos';
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin · Orders / POS</title>
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
      max-width: 500px;
      width: 90%;
      max-height: 80vh;
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

    button:disabled {
      opacity: 0.5;
      cursor: not-allowed;
    }

    button:disabled:hover {
      background-color: transparent;
    }

    .pagination-btn.active {
      background-color: #d97706;
      color: white;
      border-color: #d97706;
    }
  </style>
</head>

<body class="bg-white font-sans antialiased">

  <!-- Toast notification container -->
  <div id="toast" class="toast hidden">
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

  <!-- APP CONTAINER -->
  <div class="min-h-screen flex flex-col lg:flex-row">

    <!-- ========== SIDEBAR ========== -->
    <?php require_once '../components/admin_nav.php'; ?>

    <!-- ========== MAIN CONTENT ========== -->
    <main class="flex-1 p-5 lg:p-8 overflow-y-auto bg-white">

      <!-- header -->
      <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <div>
          <h1 class="text-2xl lg:text-3xl font-light text-slate-800">Orders / POS</h1>
          <p class="text-sm text-slate-500 mt-0.5">manage dine-in, take-out, and delivery orders</p>
        </div>
        <div class="flex gap-3 text-sm">
          <span class="bg-white border rounded-full px-4 py-2 flex items-center gap-2 shadow-sm">
            <i class="fa-regular fa-calendar text-slate-400"></i> <?php echo $today; ?>
          </span>
          <span class="bg-white border rounded-full px-4 py-2 shadow-sm cursor-pointer hover:bg-slate-50 relative" id="notificationBell">
            <i class="fa-regular fa-bell"></i>
            <?php if ($unread_count > 0): ?>
                <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-4 h-4 flex items-center justify-center"><?php echo $unread_count; ?></span>
            <?php endif; ?>
          </span>
        </div>
      </div>

      <!-- STATS CARDS -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm cursor-pointer hover:shadow-md transition"
            onclick="filterByStatus('active')">
          <p class="text-xs text-slate-500">Active orders</p>
          <p class="text-2xl font-semibold" id="statsActiveOrders"><?php echo $stats['active_orders']; ?></p>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm cursor-pointer hover:shadow-md transition"
            onclick="filterByType('dine-in')">
          <p class="text-xs text-slate-500">Dine-in</p>
          <p class="text-2xl font-semibold" id="statsDineIn"><?php echo $stats['dine_in']; ?></p>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm cursor-pointer hover:shadow-md transition"
            onclick="filterByType('takeout')">
          <p class="text-xs text-slate-500">Take-out</p>
          <p class="text-2xl font-semibold" id="statsTakeout"><?php echo $stats['takeout']; ?></p>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm cursor-pointer hover:shadow-md transition"
            onclick="filterByType('delivery')">
          <p class="text-xs text-slate-500">Delivery</p>
          <p class="text-2xl font-semibold" id="statsDelivery"><?php echo $stats['delivery']; ?></p>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
          <p class="text-xs text-slate-500">Today's revenue</p>
          <p class="text-2xl font-semibold" id="statsRevenue">₱<?php echo number_format($stats['today_revenue'], 2); ?></p>
        </div>
      </div>

      <!-- ORDER TYPE TABS -->
      <div class="flex flex-wrap gap-2 border-b border-slate-200 pb-2 mb-6">
        <button onclick="filterOrders('all')" 
                class="filter-btn px-4 py-2 <?php echo $typeFilter == 'all' ? 'bg-amber-600 text-white' : 'bg-white border border-slate-200 hover:bg-slate-50'; ?> rounded-full text-sm">all orders</button>
        <button onclick="filterOrders('dine-in')" 
                class="filter-btn px-4 py-2 <?php echo $typeFilter == 'dine-in' ? 'bg-amber-600 text-white' : 'bg-white border border-slate-200 hover:bg-slate-50'; ?> rounded-full text-sm">dine-in</button>
        <button onclick="filterOrders('takeout')" 
                class="filter-btn px-4 py-2 <?php echo $typeFilter == 'takeout' ? 'bg-amber-600 text-white' : 'bg-white border border-slate-200 hover:bg-slate-50'; ?> rounded-full text-sm">take-out</button>
        <button onclick="filterOrders('delivery')" 
                class="filter-btn px-4 py-2 <?php echo $typeFilter == 'delivery' ? 'bg-amber-600 text-white' : 'bg-white border border-slate-200 hover:bg-slate-50'; ?> rounded-full text-sm">delivery</button>
        <button onclick="filterOrders('completed')" 
                class="filter-btn px-4 py-2 <?php echo $statusFilter == 'completed' ? 'bg-amber-600 text-white' : 'bg-white border border-slate-200 hover:bg-slate-50'; ?> rounded-full text-sm">completed</button>
      </div>

      <!-- ACTIVE ORDERS TABLE -->
      <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden mb-8">
        <div class="p-4 border-b border-slate-200 bg-slate-50 flex items-center justify-between">
          <h2 class="font-semibold flex items-center gap-2"><i class="fa-solid fa-cash-register text-amber-600"></i>active orders</h2>
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
              <?php if (empty($orders)): ?>
                  <tr>
                    <td colspan="8" class="p-8 text-center text-slate-500">No orders found</td>
                  </tr>
              <?php else: ?>
                  <?php foreach ($orders as $order):
                    $typeColors = [
                      'dine-in' => 'bg-blue-100 text-blue-700',
                      'takeout' => 'bg-green-100 text-green-700',
                      'delivery' => 'bg-purple-100 text-purple-700'
                    ];
                    $statusColors = [
                      'pending' => 'bg-slate-100 text-slate-700',
                      'preparing' => 'bg-amber-100 text-amber-700',
                      'ready' => 'bg-green-100 text-green-700',
                      'served' => 'bg-blue-100 text-blue-700',
                      'completed' => 'bg-emerald-100 text-emerald-700',
                      'cancelled' => 'bg-red-100 text-red-700'
                    ];
                    $typeColor = $typeColors[$order['order_type']] ?? 'bg-gray-100 text-gray-700';
                    $statusColor = $statusColors[$order['status']] ?? 'bg-gray-100 text-gray-700';

                    $items = json_decode($order['items'], true);
                    $itemCount = is_array($items) ? count($items) : 0;

                    $customerInfo = $order['customer_name'] ?? 'Guest';
                    if ($order['table_number']) {
                      $customerInfo = 'Table ' . $order['table_number'] . ' · ' . $customerInfo;
                    }
                    ?>
                    <tr data-order-id="<?php echo $order['order_reference']; ?>" 
                        data-type="<?php echo $order['order_type']; ?>" 
                        data-status="<?php echo $order['status']; ?>">
                      <td class="p-3 font-medium">#<?php echo $order['order_reference']; ?></td>
                      <td class="p-3"><?php echo date('h:i A', strtotime($order['created_at'])); ?></td>
                      <td class="p-3"><span class="type-badge <?php echo $typeColor; ?> px-2 py-0.5 rounded-full text-xs"><?php echo $order['order_type']; ?></span></td>
                      <td class="p-3"><?php echo $customerInfo; ?></td>
                      <td class="p-3"><?php echo $itemCount; ?> items</td>
                      <td class="p-3 font-medium">₱<?php echo number_format($order['total_amount'], 2); ?></td>
                      <td class="p-3"><span class="status-badge <?php echo $statusColor; ?> px-2 py-0.5 rounded-full text-xs"><?php echo $order['status']; ?></span></td>
                      <td class="p-3 action-cell">
                        <!-- Actions populated by JavaScript -->
                      </td>
                    </tr>
                  <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
        <div class="p-4 border-t border-slate-200 flex items-center justify-between">
          <span class="text-xs text-slate-500" id="paginationInfo">
            Showing <?php echo (($currentPage - 1) * $limit + 1); ?>-<?php echo min($currentPage * $limit, $totalOrders); ?> of <?php echo $totalOrders; ?> orders
          </span>
          <div class="flex gap-2" id="paginationButtons">
            <!-- Pagination buttons will be dynamically generated -->
          </div>
        </div>
      </div>

      <!-- ORDER QUEUE -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- kitchen queue summary -->
        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-5">
          <div class="flex justify-between items-center mb-3">
            <h3 class="font-semibold flex items-center gap-2"><i class="fa-regular fa-clock text-amber-600"></i>kitchen queue</h3>
            <a href="../restaurant_management/kitchen_orders.php" class="text-xs text-amber-600 hover:underline">view full kitchen display →</a>
          </div>
          <ul class="space-y-3" id="kitchenQueue">
            <?php if (empty($kitchenQueue)): ?>
                <li class="text-sm text-slate-500 text-center py-4">No orders in kitchen</li>
            <?php else: ?>
                <?php foreach ($kitchenQueue as $item):
                  $items = json_decode($item['items'], true);
                  $itemCount = is_array($items) ? count($items) : 0;
                  $customerInfo = $item['customer_name'] ?? 'Guest';
                  if ($item['table_number']) {
                    $customerInfo = 'Table ' . $item['table_number'] . ' · ' . $customerInfo;
                  }
                  ?>
                  <li class="flex justify-between items-center border-b border-amber-200 pb-2 queue-item" data-order-id="<?php echo $item['order_reference']; ?>">
                    <div>
                      <span class="font-medium">#<?php echo $item['order_reference']; ?></span>
                      <p class="text-xs text-slate-500"><?php echo $customerInfo; ?> · <?php echo $itemCount; ?> items · <?php echo $item['minutes_ago']; ?> min ago</p>
                    </div>
                    <div class="flex items-center gap-2">
                      <span class="bg-amber-100 text-amber-700 text-xs px-2 py-0.5 rounded-full">preparing</span>
                      <button onclick="updateOrderStatus('<?php echo $item['order_reference']; ?>', 'ready')" class="text-xs text-green-600 hover:underline">✓ ready</button>
                    </div>
                  </li>
                <?php endforeach; ?>
            <?php endif; ?>
          </ul>
          <div class="mt-4 pt-3 border-t border-amber-200 flex justify-between text-xs text-amber-700">
            <span><span class="font-semibold" id="queueCount"><?php echo count($kitchenQueue); ?></span> orders in kitchen</span>
            <span>avg prep time: <span id="avgPrepTime"><?php echo $avgPrepTime; ?></span> min</span>
          </div>
        </div>

        <!-- ready for pickup/delivery queue -->
        <div class="bg-green-50 border border-green-200 rounded-2xl p-5">
          <h3 class="font-semibold flex items-center gap-2 mb-3"><i class="fa-regular fa-circle-check text-green-600"></i> ready for pickup/delivery</h3>
          <ul class="space-y-3" id="readyQueue">
            <?php if (empty($readyQueue)): ?>
                <li class="text-sm text-slate-500 text-center py-4">No orders ready</li>
            <?php else: ?>
                <?php foreach ($readyQueue as $item):
                  $items = json_decode($item['items'], true);
                  $itemCount = is_array($items) ? count($items) : 0;
                  $customerInfo = $item['customer_name'] ?? 'Guest';
                  if ($item['table_number']) {
                    $customerInfo = 'Table ' . $item['table_number'] . ' · ' . $customerInfo;
                  }
                  $actionText = $item['order_type'] === 'delivery' ? 'dispatch' : 'serve';
                  $nextStatus = $item['order_type'] === 'delivery' ? 'completed' : 'served';
                  ?>
                  <li class="flex justify-between items-center border-b border-green-200 pb-2" data-order-id="<?php echo $item['order_reference']; ?>">
                    <div>
                      <span class="font-medium">#<?php echo $item['order_reference']; ?></span>
                      <p class="text-xs text-slate-500"><?php echo $customerInfo; ?> · <?php echo $itemCount; ?> items</p>
                    </div>
                    <div class="flex items-center gap-2">
                      <span class="bg-green-100 text-green-700 text-xs px-2 py-0.5 rounded-full"><?php echo $item['status']; ?></span>
                      <button onclick="updateOrderStatus('<?php echo $item['order_reference']; ?>', '<?php echo $nextStatus; ?>')" class="text-xs text-blue-600 hover:underline"><?php echo $actionText; ?></button>
                    </div>
                  </li>
                <?php endforeach; ?>
            <?php endif; ?>
          </ul>
          <div class="mt-4 pt-3 border-t border-green-200">
            <span class="text-xs text-green-700"><span class="font-semibold" id="readyCount"><?php echo count($readyQueue); ?></span> order waiting</span>
          </div>
        </div>
      </div>
    </main>
  </div>

  <script>
    // ========== GLOBAL VARIABLES ==========
    let currentPage = <?php echo $currentPage; ?>;
    const totalPages = <?php echo $totalPages; ?>;
    const itemsPerPage = <?php echo $limit; ?>;

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

    // ========== INITIALIZATION ==========
    document.addEventListener('DOMContentLoaded', function() {
      updateActionButtons();
      updatePagination();
    });

    // ========== UPDATE ACTION BUTTONS ==========
    function updateActionButtons() {
      document.querySelectorAll('#ordersTableBody tr').forEach(row => {
        const orderId = row.dataset.orderId;
        const status = row.dataset.status;
        const type = row.dataset.type;
        const actionCell = row.querySelector('.action-cell');
        
        if (!actionCell) return;
        
        actionCell.innerHTML = '';
        
        // View button
        const viewBtn = document.createElement('button');
        viewBtn.className = 'text-amber-700 text-xs hover:underline mr-2';
        viewBtn.textContent = 'view';
        viewBtn.setAttribute('onclick', `viewOrder('${orderId}')`);
        actionCell.appendChild(viewBtn);
        
        // Status-specific action button
        if (status === 'preparing') {
          const actionBtn = document.createElement('button');
          actionBtn.className = 'text-green-600 text-xs hover:underline';
          actionBtn.textContent = 'ready';
          actionBtn.setAttribute('onclick', `updateOrderStatus('${orderId}', 'ready')`);
          actionCell.appendChild(actionBtn);
        } else if (status === 'ready') {
          const actionBtn = document.createElement('button');
          if (type === 'delivery') {
            actionBtn.className = 'text-purple-600 text-xs hover:underline';
            actionBtn.textContent = 'dispatch';
            actionBtn.setAttribute('onclick', `updateOrderStatus('${orderId}', 'completed')`);
          } else {
            actionBtn.className = 'text-blue-600 text-xs hover:underline';
            actionBtn.textContent = 'serve';
            actionBtn.setAttribute('onclick', `updateOrderStatus('${orderId}', 'served')`);
          }
          actionCell.appendChild(actionBtn);
        } else if (status === 'served') {
          const actionBtn = document.createElement('button');
          actionBtn.className = 'text-blue-600 text-xs hover:underline';
          actionBtn.textContent = 'payment';
          actionBtn.setAttribute('onclick', `updateOrderStatus('${orderId}', 'completed')`);
          actionCell.appendChild(actionBtn);
        } else if (status === 'pending') {
          const actionBtn = document.createElement('button');
          actionBtn.className = 'text-amber-600 text-xs hover:underline';
          actionBtn.textContent = 'process';
          actionBtn.setAttribute('onclick', `updateOrderStatus('${orderId}', 'preparing')`);
          actionCell.appendChild(actionBtn);
        }
      });
    }

    // ========== UPDATE ORDER STATUS ==========
    function updateOrderStatus(orderId, newStatus) {
      Swal.fire({
        title: 'Update Status',
        text: `Change order #${orderId} status to ${newStatus}?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#d97706',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, update'
      }).then((result) => {
        if (result.isConfirmed) {
          Swal.fire({
            title: 'Updating...',
            text: 'Please wait',
            allowOutsideClick: false,
            didOpen: () => {
              Swal.showLoading();
            }
          });

          const formData = new FormData();
          formData.append('action', 'update_status');
          formData.append('order_id', orderId);
          formData.append('status', newStatus);

          fetch('../../../controller/admin/post/restaurant/orders_actions.php', {
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
        }
      });
    }

    // ========== VIEW ORDER DETAILS ==========
    function viewOrder(orderId) {
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

      fetch('../../../controller/admin/post/restaurant/orders_actions.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        Swal.close();
        if (data.success) {
          const order = data.order;
          const items = order.items || [];
          
          let itemsHtml = '';
          items.forEach(item => {
            itemsHtml += `<li class="flex justify-between text-sm border-b py-1"><span>${item.name || 'Item'}</span><span class="font-medium">₱${parseFloat(item.price || 0).toFixed(2)}</span></li>`;
          });

          const modalContent = document.getElementById('orderDetailsContent');
          modalContent.innerHTML = `
            <div class="border-b pb-3">
              <p class="text-sm text-slate-500">Order #${order.order_reference}</p>
              <p class="text-lg font-semibold">${order.full_name || 'Guest'}</p>
            </div>
            <div class="grid grid-cols-2 gap-3 text-sm">
              <div>
                <p class="text-slate-500">Time</p>
                <p class="font-medium">${new Date(order.created_at).toLocaleTimeString()}</p>
              </div>
              <div>
                <p class="text-slate-500">Type</p>
                <p class="font-medium capitalize">${order.order_type}</p>
              </div>
              <div>
                <p class="text-slate-500">${order.table_number ? 'Table' : 'Contact'}</p>
                <p class="font-medium">${order.table_number || order.phone || 'N/A'}</p>
              </div>
              <div>
                <p class="text-slate-500">Total</p>
                <p class="font-medium">₱${parseFloat(order.total_amount).toFixed(2)}</p>
              </div>
            </div>
            <div>
              <p class="text-sm text-slate-500 mb-2">Items (${items.length})</p>
              <ul class="space-y-1 max-h-40 overflow-y-auto">
                ${itemsHtml || '<li class="text-sm text-slate-400">No items</li>'}
              </ul>
            </div>
            <div class="grid grid-cols-2 gap-3 text-sm pt-2 border-t">
              <div>
                <p class="text-slate-500">Subtotal</p>
                <p class="font-medium">₱${parseFloat(order.subtotal).toFixed(2)}</p>
              </div>
              <div>
                <p class="text-slate-500">Service Fee</p>
                <p class="font-medium">₱${parseFloat(order.service_fee).toFixed(2)}</p>
              </div>
              ${order.points_used > 0 ? `
              <div>
                <p class="text-slate-500">Points Used</p>
                <p class="font-medium text-amber-600">${order.points_used}</p>
              </div>
              ` : ''}
              ${order.points_earned > 0 ? `
              <div>
                <p class="text-slate-500">Points Earned</p>
                <p class="font-medium text-green-600">+${order.points_earned}</p>
              </div>
              ` : ''}
            </div>
            <div>
              <p class="text-sm text-slate-500">Status</p>
              <p class="text-sm font-medium capitalize mt-1">${order.status}</p>
            </div>
          `;

          document.getElementById('modalOrderTitle').textContent = `Order #${orderId}`;
          openModal('orderDetailsModal');
        } else {
          showToast(data.message, 'error');
        }
      })
      .catch(error => {
        Swal.close();
        showToast('An error occurred', 'error');
      });
    }

    // ========== FILTER FUNCTIONS ==========
    function filterOrders(type) {
      const url = new URL(window.location);
      if (type === 'all') {
        url.searchParams.delete('type');
        url.searchParams.delete('status');
      } else if (type === 'completed') {
        url.searchParams.set('status', 'completed');
        url.searchParams.delete('type');
      } else {
        url.searchParams.set('type', type);
        url.searchParams.delete('status');
      }
      url.searchParams.set('page', '1');
      window.location.href = url.toString();
    }

    function filterByType(type) {
      filterOrders(type);
    }

    function filterByStatus(status) {
      if (status === 'active') {
        url.searchParams.delete('status');
      } else {
        url.searchParams.set('status', status);
      }
      url.searchParams.set('page', '1');
      window.location.href = url.toString();
    }

    // ========== REFRESH ORDERS ==========
    function refreshOrders() {
      showToast('Refreshing orders...', 'info');
      setTimeout(() => {
        location.reload();
      }, 1000);
    }

    // ========== PAGINATION FUNCTIONS ==========
    function updatePagination() {
      const rows = document.querySelectorAll('#ordersTableBody tr:not([style*="display: none"])');
      const totalItems = rows.length;
      const totalPages = Math.max(1, Math.ceil(totalItems / itemsPerPage));

      if (currentPage > totalPages) {
        currentPage = totalPages;
      }
      if (currentPage < 1) {
        currentPage = 1;
      }

      rows.forEach((row, index) => {
        if (index >= (currentPage - 1) * itemsPerPage && index < currentPage * itemsPerPage) {
          row.style.display = '';
        } else {
          row.style.display = 'none';
        }
      });

      const start = totalItems > 0 ? (currentPage - 1) * itemsPerPage + 1 : 0;
      const end = totalItems > 0 ? Math.min(currentPage * itemsPerPage, totalItems) : 0;
      document.getElementById('paginationInfo').textContent =
        totalItems > 0 ? `Showing ${start}-${end} of ${totalItems} orders` : 'Showing 0 orders';

      generatePaginationButtons(totalPages);
    }

    function generatePaginationButtons(totalPages) {
      const container = document.getElementById('paginationButtons');
      if (!container) return;

      let buttons = '';

      buttons += `<button onclick="changePage('prev')" class="border border-slate-200 px-3 py-1 rounded-lg text-sm hover:bg-slate-50" ${currentPage === 1 ? 'disabled' : ''}>Previous</button>`;

      for (let i = 1; i <= totalPages; i++) {
        buttons += `<button onclick="changePage(${i})" class="border px-3 py-1 rounded-lg text-sm page-btn ${i === currentPage ? 'bg-amber-600 text-white' : 'border-slate-200 hover:bg-slate-50'}">${i}</button>`;
      }

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

    // ========== NOTIFICATION BELL ==========
    document.getElementById('notificationBell')?.addEventListener('click', function() {
      window.location.href = '../notifications.php';
    });

    // Close modals when clicking outside
    window.addEventListener('click', function(event) {
      if (event.target.classList.contains('modal')) {
        event.target.classList.remove('show');
      }
    });

    // Close modals with Escape key
    document.addEventListener('keydown', function(event) {
      if (event.key === 'Escape') {
        document.querySelectorAll('.modal.show').forEach(modal => {
          modal.classList.remove('show');
        });
      }
    });
  </script>
</body>
</html>