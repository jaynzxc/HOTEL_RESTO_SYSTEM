<?php
require_once '../../controller/customer/get/menu.php';
?>
<!DOCTYPE html>
<html lang="en">

  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu / Order Food · Customer Portal</title>
    <!-- Tailwind via CDN + Font Awesome 6 -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Add this in the head section -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
      .menu-item button {
        transition: all 0.15s ease;
      }

      .category-filter button.active {
        background-color: #d97706;
        color: white;
        border-color: #d97706;
      }

      .order-history-item {
        transition: all 0.2s ease;
      }

      .order-history-item:hover {
        background-color: #fef3e2;
        border-color: #d97706;
      }

      #orderHistoryContainer::-webkit-scrollbar {
        width: 4px;
      }

      #orderHistoryContainer::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
      }

      #orderHistoryContainer::-webkit-scrollbar-thumb {
        background: #d97706;
        border-radius: 10px;
      }

      #orderItemsContainer::-webkit-scrollbar {
        width: 4px;
      }

      #orderItemsContainer::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
      }

      #orderItemsContainer::-webkit-scrollbar-thumb {
        background: #d97706;
        border-radius: 10px;
      }
    </style>
  </head>

  <body class="bg-slate-50 font-sans antialiased">

    <!-- main flex wrapper (sidebar + content) -->
    <div class="min-h-screen flex flex-col lg:flex-row">

      <!-- ========== SIDEBAR (same customer portal, Menu active) ========== -->
      <?php require './components/customer_nav.php' ?>

      <!-- ========== MAIN CONTENT (MENU / ORDER FOOD PAGE) ========== -->
      <main class="flex-1 p-5 lg:p-8 overflow-y-auto">

        <!-- header -->
        <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
          <div>
            <h1 class="text-2xl lg:text-3xl font-light text-slate-800">Menu / Order Food</h1>
            <p class="text-sm text-slate-500 mt-0.5">order from Azure Restaurant & other outlets</p>
          </div>
          <div
            class="bg-white border border-slate-200 rounded-full px-4 py-2 text-sm flex items-center gap-2 shadow-sm">
            <i class="fa-regular fa-calendar text-slate-400"></i> wednesday, 21 may 2025
          </div>
        </div>

        <!-- ===== TWO COLUMN: menu items (left) & current order (right) ===== -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

          <!-- LEFT: menu categories and items (2/3 width) -->
          <div class="lg:col-span-2 space-y-6">

            <!-- category tabs / filter -->
            <div class="flex flex-wrap gap-2 border-b border-slate-200 pb-2 category-filter" id="categoryFilter">
              <button data-cat="all" class="px-4 py-2 bg-amber-600 text-white rounded-full text-sm active">all</button>
              <button data-cat="appetizers"
                class="px-4 py-2 bg-white border border-slate-200 rounded-full text-sm hover:bg-slate-50">appetizers</button>
              <button data-cat="mains"
                class="px-4 py-2 bg-white border border-slate-200 rounded-full text-sm hover:bg-slate-50">mains</button>
              <button data-cat="desserts"
                class="px-4 py-2 bg-white border border-slate-200 rounded-full text-sm hover:bg-slate-50">desserts</button>
              <button data-cat="beverages"
                class="px-4 py-2 bg-white border border-slate-200 rounded-full text-sm hover:bg-slate-50">beverages</button>
            </div>

            <!-- menu grid (items with data-category and data-price) -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4" id="menuGrid">
              <?php foreach ($menuItems as $item): ?>
                <div
                  class="menu-item bg-white rounded-2xl border border-slate-200 p-4 flex gap-3 hover:shadow-md transition"
                  data-category="<?php echo $item['category']; ?>"
                  data-name="<?php echo htmlspecialchars($item['name']); ?>" data-price="<?php echo $item['price']; ?>">
                  <div class="h-20 w-20 bg-slate-200 rounded-xl shrink-0 flex items-center justify-center text-slate-400">
                    <?php if (!empty($item['image_url'])): ?>
                      <img src="<?php echo htmlspecialchars($item['image_url']); ?>"
                        alt="<?php echo htmlspecialchars($item['name']); ?>" class="w-full h-full object-cover rounded-xl">
                    <?php else: ?>
                      <i class="fa-regular fa-image"></i>
                    <?php endif; ?>
                  </div>
                  <div class="flex-1">
                    <div class="flex items-start justify-between">
                      <h3 class="font-semibold"><?php echo htmlspecialchars($item['name']); ?></h3>
                      <span class="text-amber-700 font-bold">₱<?php echo number_format($item['price']); ?></span>
                    </div>
                    <p class="text-xs text-slate-500 mt-0.5"><?php echo htmlspecialchars($item['description']); ?></p>
                    <div class="flex items-center justify-between mt-2">
                      <span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full">available</span>
                      <button
                        class="add-to-order text-amber-700 text-sm border border-amber-600 px-3 py-1 rounded-lg hover:bg-amber-50">add</button>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          </div>

          <!-- RIGHT: current order cart & summary -->
          <div class="space-y-5">
            <!-- Current Order Cart -->
            <div class="bg-white rounded-2xl border border-slate-200 p-5 sticky top-4">
              <h2 class="font-semibold text-lg flex items-center gap-2 border-b pb-3"><i
                  class="fa-solid fa-bag-shopping text-amber-600"></i> your order</h2>

              <!-- order items container (dynamic) -->
              <div id="orderItemsContainer" class="space-y-3 mt-4 max-h-80 overflow-y-auto">
                <div class="text-sm text-slate-400 text-center py-4" id="emptyCartMsg">Your cart is empty.</div>
              </div>

              <!-- summary area (dynamically filled) -->
              <div id="summaryDetails" class="space-y-2 text-sm">
                <!-- subtotals appear here via js, initially blank -->
              </div>

              <!-- delivery / pickup options -->
              <div class="mt-4">
                <label class="block text-xs text-slate-500 mb-1">order type</label>
                <select id="orderType" class="w-full border border-slate-200 rounded-xl p-2 text-sm">
                  <option value="dine-in">dine-in</option>
                  <option value="takeaway">takeaway</option>
                  <option value="room delivery">room delivery</option>
                </select>
              </div>

              <button id="placeOrderBtn"
                class="w-full bg-amber-600 hover:bg-amber-700 text-white py-3 rounded-xl font-medium mt-5 transition">place
                order</button>
              <p class="text-xs text-slate-400 text-center mt-3">estimated ready: 25-35 min</p>
            </div>

            <!-- loyalty / promo card -->
            <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4">
              <div class="flex items-center gap-2 text-amber-700">
                <i class="fa-regular fa-star"></i>
                <span class="font-semibold text-sm">loyalty reward</span>
              </div>
              <p class="text-xs text-slate-600 mt-1">use 240 pts to get 1 free halo-halo!</p>
              <button id="applyPointsBtn" class="text-xs bg-amber-600 text-white px-3 py-1.5 rounded-lg mt-2">apply
                points</button>
            </div>

            <!-- Order History Section -->
            <div class="bg-white rounded-2xl border border-slate-200 p-5">
              <div class="flex items-center justify-between mb-3">
                <h3 class="font-semibold flex items-center gap-2">
                  <i class="fa-regular fa-clock-rotate-left text-amber-600"></i> recent orders
                </h3>
                <span class="text-xs bg-slate-100 text-slate-600 px-2 py-1 rounded-full" id="orderCount">
                  <?php echo count($orderHistory); ?>
                </span>
              </div>

              <div id="orderHistoryContainer" class="space-y-3 max-h-60 overflow-y-auto">
                <?php if (empty($orderHistory)): ?>
                  <div class="text-sm text-slate-400 text-center py-4">
                    <i class="fa-regular fa-receipt text-2xl mb-2 text-slate-300"></i>
                    <p>No order history yet.</p>
                    <p class="text-xs mt-1">Your orders will appear here</p>
                  </div>
                <?php else: ?>
                  <?php foreach ($orderHistory as $order): ?>
                    <div class="border border-slate-100 rounded-xl p-3 order-history-item"
                      data-order-id="<?php echo $order['id']; ?>">
                      <div class="flex items-start justify-between">
                        <div>
                          <span class="font-mono text-xs bg-slate-100 px-2 py-0.5 rounded">
                            #<?php echo substr($order['order_reference'], -8); ?>
                          </span>
                          <p class="text-xs text-slate-500 mt-1"><?php echo $order['item_count']; ?> items ·
                            <?php echo $order['order_type']; ?>
                          </p>
                        </div>
                        <span class="text-sm font-semibold">₱<?php echo number_format($order['total_amount'], 2); ?></span>
                      </div>
                      <div class="flex items-center justify-between mt-2">
                        <span class="text-xs px-2 py-0.5 rounded-full 
                          <?php
                          echo $order['status'] == 'completed' ? 'bg-green-100 text-green-700' :
                            ($order['status'] == 'pending' ? 'bg-amber-100 text-amber-700' :
                              ($order['status'] == 'preparing' ? 'bg-blue-100 text-blue-700' :
                                'bg-red-100 text-red-700'));
                          ?>">
                          <?php echo $order['status']; ?>
                        </span>
                        <div class="flex gap-2">
                          <button onclick="viewOrderDetails(<?php echo $order['id']; ?>)"
                            class="text-xs text-amber-700 hover:underline">
                            <i class="fa-regular fa-eye mr-1"></i>view
                          </button>
                          <?php if (in_array($order['status'], ['pending', 'preparing'])): ?>
                            <button onclick="cancelOrder(<?php echo $order['id']; ?>)"
                              class="text-xs text-red-600 hover:underline">
                              <i class="fa-regular fa-times-circle mr-1"></i>cancel
                            </button>
                          <?php endif; ?>
                        </div>
                      </div>
                      <p class="text-[10px] text-slate-400 mt-1"><?php echo $order['time_ago']; ?></p>
                    </div>
                  <?php endforeach; ?>
                <?php endif; ?>
              </div>

              <?php if (!empty($orderHistory)): ?>
                <a href="./order_history.php" class="block text-center text-xs text-amber-700 mt-3 hover:underline">
                  view all orders <i class="fa-regular fa-arrow-right ml-1"></i>
                </a>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </main>
    </div>

    <script>
      (function () {
        // ----- DATA & STATE -----
        let order = [];
        let pointsUsed = 0; // Track points used in current order
        let freeItemAdded = false; // Track if free item already added

        const userPoints = <?php echo $user['loyalty_points'] ?? 0; ?>;
        const menuItems = <?php echo json_encode($menuItems); ?>;
        const orderHistory = <?php echo json_encode($orderHistory); ?>;
        const hasOutstandingBalance = <?php echo $hasOutstandingBalance ? 'true' : 'false'; ?>;
        const outstandingAmount = <?php echo $totalOutstanding; ?>;

        // Helper to generate unique id
        function generateItemId(name) {
          return name.replace(/\s/g, '') + '-' + Date.now() + '-' + Math.random().toString(36).substr(2, 5);
        }

        // ----- RENDER ORDER CART -----
        const container = document.getElementById('orderItemsContainer');
        const summaryDetails = document.getElementById('summaryDetails');
        const placeOrderBtn = document.getElementById('placeOrderBtn');
        const orderTypeSelect = document.getElementById('orderType');

        function renderOrder() {
          if (!container) return;
          container.innerHTML = '';

          if (order.length === 0) {
            container.innerHTML = '<div class="text-sm text-slate-400 text-center py-4">Your cart is empty.</div>';
            if (summaryDetails) summaryDetails.innerHTML = '';
            return;
          }

          // Build order items
          order.forEach((item) => {
            const itemDiv = document.createElement('div');
            itemDiv.className = 'flex justify-between items-center order-item border-b border-slate-100 pb-2 mb-2';
            itemDiv.dataset.itemId = item.id;

            // Show different styling for free items
            const priceDisplay = item.isFree
              ? '<span class="text-sm font-medium text-green-600">FREE</span>'
              : `<span class="text-sm font-medium">₱${(item.price * item.quantity).toFixed(2)}</span>`;

            itemDiv.innerHTML = `
            <div class="flex-1">
              <span class="font-medium ${item.isFree ? 'text-green-600' : ''}">${item.name} ${item.isFree ? '🎁' : ''}</span>
              <div class="flex items-center gap-2 mt-1">
                <button class="quantity-btn text-amber-600 hover:text-amber-800" data-id="${item.id}" data-action="decrease">
                  <i class="fa-solid fa-minus-circle"></i>
                </button>
                <span class="text-sm font-medium">${item.quantity}</span>
                <button class="quantity-btn text-amber-600 hover:text-amber-800" data-id="${item.id}" data-action="increase">
                  <i class="fa-solid fa-plus-circle"></i>
                </button>
              </div>
            </div>
            <div class="text-right">
              ${priceDisplay}
              <button class="text-rose-500 text-xs block mt-1 trash-btn" data-id="${item.id}" data-name="${item.name}">
                <i class="fa-regular fa-trash-can"></i>
              </button>
            </div>
          `;
            container.appendChild(itemDiv);
          });

          // Attach event listeners
          attachOrderEvents();
          updateSummary();
        }

        function attachOrderEvents() {
          // Quantity buttons
          document.querySelectorAll('.quantity-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
              const id = btn.dataset.id;
              const action = btn.dataset.action;
              const item = order.find(i => i.id === id);
              if (item) {
                if (action === 'increase') {
                  item.quantity += 1;
                } else if (action === 'decrease') {
                  item.quantity -= 1;
                  if (item.quantity <= 0) {
                    // If removing a free item, reset points tracking
                    if (item.isFree) {
                      pointsUsed = 0;
                      freeItemAdded = false;
                    }
                    order = order.filter(i => i.id !== id);
                  }
                }
                renderOrder();
              }
            });
          });

          // Remove buttons
          document.querySelectorAll('.trash-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
              const id = btn.dataset.id;
              removeFromOrder(id);
            });
          });
        }

        // compute totals and display with points discount
        function updateSummary() {
          if (!summaryDetails) return;
          if (order.length === 0) {
            summaryDetails.innerHTML = '';
            return;
          }

          // Calculate subtotal (excluding free items)
          const subtotal = order.reduce((acc, i) => acc + (i.isFree ? 0 : i.price * i.quantity), 0);
          const service = Math.round(subtotal * 0.05 * 100) / 100;
          const totalBeforePoints = subtotal + service;

          // Calculate point value (1 point = ₱1)
          const pointValue = pointsUsed; // Since 240 points = ₱240 value

          // Final total after points deduction
          const finalTotal = Math.max(0, totalBeforePoints - pointValue);

          summaryDetails.innerHTML = `
            <div class="flex justify-between items-center text-slate-400 pt-2 border-t border-dashed">
              <span>subtotal</span><span>₱${subtotal.toFixed(2)}</span>
            </div>
            <div class="flex justify-between items-center text-slate-400">
              <span>service fee (5%)</span><span>₱${service.toFixed(2)}</span>
            </div>
            ${pointsUsed > 0 ? `
            <div class="flex justify-between items-center text-green-600">
              <span>points discount (-${pointsUsed} pts)</span><span>-₱${pointValue.toFixed(2)}</span>
            </div>
            ` : ''}
            <div class="flex justify-between items-center font-bold text-lg border-t pt-2">
              <span>total</span><span>₱${finalTotal.toFixed(2)}</span>
            </div>
          `;
        }

        // ----- ORDER MODIFIERS -----
        function addToOrder(name, price) {
          // Check if trying to add regular Halo-Halo
          if (name === 'Halo-Halo' && freeItemAdded) {
            Swal.fire({
              title: 'Free Item Already Added',
              text: 'You already have a free Halo-Halo. Add another regular one?',
              icon: 'question',
              showCancelButton: true,
              confirmButtonColor: '#d97706',
              confirmButtonText: 'Yes, add regular'
            }).then((result) => {
              if (result.isConfirmed) {
                const existing = order.find(i => i.name === name && !i.isFree);
                if (existing) {
                  existing.quantity += 1;
                } else {
                  order.push({
                    id: generateItemId(name),
                    name: name,
                    price: price,
                    quantity: 1,
                    isFree: false
                  });
                }
                renderOrder();
              }
            });
            return;
          }

          const existing = order.find(i => i.name === name && i.isFree === false);
          if (existing) {
            existing.quantity += 1;
          } else {
            order.push({
              id: generateItemId(name),
              name: name,
              price: price,
              quantity: 1,
              isFree: false
            });
          }
          renderOrder();
        }

        function removeFromOrder(id) {
          const item = order.find(i => i.id === id);
          if (item && item.isFree) {
            pointsUsed = 0;
            freeItemAdded = false;
          }
          order = order.filter(i => i.id !== id);
          renderOrder();
        }

        // ----- ATTACH ADD BUTTONS TO MENU -----
        document.querySelectorAll('.add-to-order').forEach(btn => {
          btn.addEventListener('click', (e) => {
            const menuItem = btn.closest('.menu-item');
            if (!menuItem) return;
            const name = menuItem.dataset.name;
            const price = parseInt(menuItem.dataset.price);
            if (name && !isNaN(price)) {
              addToOrder(name, price);
            }
          });
        });

        // ----- CATEGORY FILTER -----
        const filterButtons = document.querySelectorAll('#categoryFilter button');
        const menuItemElements = document.querySelectorAll('.menu-item');

        filterButtons.forEach(btn => {
          btn.addEventListener('click', () => {
            filterButtons.forEach(b => {
              b.classList.remove('bg-amber-600', 'text-white');
              b.classList.add('bg-white', 'border', 'border-slate-200', 'text-slate-700');
            });
            btn.classList.remove('bg-white', 'border', 'border-slate-200', 'text-slate-700');
            btn.classList.add('bg-amber-600', 'text-white');

            const cat = btn.dataset.cat;
            menuItemElements.forEach(item => {
              if (cat === 'all' || item.dataset.category === cat) {
                item.style.display = 'flex';
              } else {
                item.style.display = 'none';
              }
            });
          });
        });

        // ----- APPLY POINTS (free halo-halo) -----
        document.getElementById('applyPointsBtn')?.addEventListener('click', () => {
          // First, check if user has outstanding balance
          if (hasOutstandingBalance) {
            Swal.fire({
              title: 'Outstanding Balance Detected',
              html: `
                <div class="text-left">
                  <p class="mb-3 text-amber-700">You have an outstanding balance of <strong>₱${outstandingAmount.toFixed(2)}</strong>.</p>
                  <p class="mb-3">Please clear your balance before using loyalty points.</p>
                  <a href="./payments.php" class="inline-block bg-amber-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-amber-700 transition">
                    Go to Payments
                  </a>
                </div>
              `,
              icon: 'warning',
              confirmButtonColor: '#d97706',
              confirmButtonText: 'OK',
              showCancelButton: false
            });
            return;
          }

          if (userPoints < 240) {
            Swal.fire({
              title: 'Insufficient Points',
              text: `You need 240 points to redeem a free Halo-Halo. You have ${userPoints} points.`,
              icon: 'warning',
              confirmButtonColor: '#d97706'
            });
            return;
          }

          if (freeItemAdded) {
            Swal.fire({
              title: 'Already Redeemed',
              text: 'You have already redeemed a free Halo-Halo in this order.',
              icon: 'info',
              confirmButtonColor: '#d97706'
            });
            return;
          }

          // Check if there's a regular Halo-Halo in cart
          const hasRegularHalo = order.some(i => i.name === 'Halo-Halo' && !i.isFree);

          if (!hasRegularHalo) {
            Swal.fire({
              title: 'Add Regular Halo-Halo First',
              text: 'Add a regular Halo-Halo to your cart first, then apply points to get a free one.',
              icon: 'info',
              confirmButtonColor: '#d97706'
            });
            return;
          }

          const freeItemName = 'Halo-Halo (loyalty free)';

          // Add free item to order
          order.push({
            id: generateItemId(freeItemName),
            name: freeItemName,
            price: 0,
            quantity: 1,
            isFree: true // Mark as free item
          });

          pointsUsed = 240; // Track points used
          freeItemAdded = true;

          renderOrder();

          Swal.fire({
            title: 'Points Applied!',
            text: '240 points will be deducted when you place your order.',
            icon: 'success',
            confirmButtonColor: '#d97706',
            timer: 2000
          });
        });

        // ----- PLACE ORDER -----
        if (placeOrderBtn) {
          placeOrderBtn.addEventListener('click', () => {
            if (order.length === 0) {
              Swal.fire({
                title: 'Empty Cart',
                text: 'Your cart is empty. Add some delicious food!',
                icon: 'warning',
                confirmButtonColor: '#d97706'
              });
              return;
            }

            // Check if trying to use points with outstanding balance
            if (pointsUsed > 0 && hasOutstandingBalance) {
              Swal.fire({
                title: 'Cannot Use Points',
                text: 'Please clear your outstanding balance before using loyalty points.',
                icon: 'warning',
                confirmButtonColor: '#d97706'
              });
              return;
            }

            // Calculate totals
            const subtotal = order.reduce((acc, i) => acc + (i.isFree ? 0 : i.price * i.quantity), 0);
            const service = Math.round(subtotal * 0.05 * 100) / 100;
            const totalBeforePoints = subtotal + service;

            // Apply points discount (1 point = ₱1)
            const pointValue = pointsUsed;
            const finalTotal = Math.max(0, totalBeforePoints - pointValue);

            const orderType = document.getElementById('orderType')?.value || 'dine-in';

            // Check if user has enough points if they're using them
            if (pointsUsed > 0 && userPoints < pointsUsed) {
              Swal.fire({
                title: 'Insufficient Points',
                text: `You no longer have enough points. You have ${userPoints} points but need ${pointsUsed}.`,
                icon: 'error',
                confirmButtonColor: '#d97706'
              });
              return;
            }

            // Prepare order data
            const orderData = {
              items: order.map(item => ({
                name: item.name,
                price: item.price,
                quantity: item.quantity,
                isFree: item.isFree || false
              })),
              order_type: orderType,
              subtotal: subtotal,
              service_fee: service,
              total: finalTotal, // Send the discounted total
              points_used: pointsUsed // Send points used to server
            };

            // Show loading
            Swal.fire({
              title: 'Placing your order...',
              html: 'Please wait',
              allowOutsideClick: false,
              didOpen: () => {
                Swal.showLoading();
              }
            });

            // Send to server
            fetch('../../controller/customer/post/place_order.php', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
              },
              body: new URLSearchParams({
                items: JSON.stringify(orderData.items),
                order_type: orderData.order_type,
                subtotal: orderData.subtotal,
                service_fee: orderData.service_fee,
                total: orderData.total,
                points_used: orderData.points_used
              })
            })
              .then(response => response.json())
              .then(data => {
                if (data.success) {
                  // Calculate points earned (based on original subtotal, not discounted total)
                  const pointsEarned = Math.floor(subtotal / 100) * 5;

                  Swal.fire({
                    title: 'Order Placed!',
                    html: `
                    <div class="text-left">
                      <p><strong>Reference:</strong> ${data.order.reference}</p>
                      <p><strong>Subtotal:</strong> ₱${subtotal.toFixed(2)}</p>
                      <p><strong>Service Fee:</strong> ₱${service.toFixed(2)}</p>
                      <p><strong>Points Discount:</strong> -₱${pointValue.toFixed(2)}</p>
                      <p><strong>Final Total:</strong> ₱${finalTotal.toFixed(2)}</p>
                      <p><strong>Points Used:</strong> ${pointsUsed}</p>
                      <p><strong>Points Earned:</strong> +${pointsEarned}</p>
                      <p><strong>Net Points Change:</strong> ${pointsEarned - pointsUsed}</p>
                    </div>
                  `,
                    icon: 'success',
                    confirmButtonColor: '#d97706',
                    confirmButtonText: 'View Orders'
                  }).then(() => {
                    // Reset everything
                    order = [];
                    pointsUsed = 0;
                    freeItemAdded = false;
                    renderOrder();
                    location.reload(); // Reload to show updated points
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
                console.error('Error:', error);
                Swal.fire({
                  title: 'Error',
                  text: 'Failed to place order. Please try again.',
                  icon: 'error',
                  confirmButtonColor: '#d97706'
                });
              });
          });
        }

        // ----- ORDER HISTORY FUNCTIONS -----
        window.viewOrderDetails = function (orderId) {
          // Find order in PHP data
          const order = orderHistory.find(o => o.id == orderId);

          if (!order) {
            Swal.fire({
              title: 'Error',
              text: 'Order not found',
              icon: 'error',
              confirmButtonColor: '#d97706'
            });
            return;
          }

          let itemsHtml = '';
          const items = typeof order.items === 'string' ? JSON.parse(order.items) : order.items_array || [];

          items.forEach(item => {
            itemsHtml += `
              <div class="flex justify-between text-sm border-b border-slate-100 pb-1 mb-1">
                <span>${item.quantity}x ${item.name} ${item.isFree ? '🎁' : ''}</span>
                <span>${item.isFree ? 'FREE' : '₱' + (item.price * item.quantity).toFixed(2)}</span>
              </div>
            `;
          });

          Swal.fire({
            title: 'Order Details',
            html: `
              <div class="text-left">
                <p class="text-xs text-slate-500 mb-1">Reference: #${order.order_reference}</p>
                <div class="bg-slate-50 p-3 rounded-lg mb-3">
                  ${itemsHtml}
                  <div class="flex justify-between font-semibold mt-2 pt-2 border-t">
                    <span>Total</span>
                    <span class="text-amber-700">₱${order.total_amount.toFixed(2)}</span>
                  </div>
                </div>
                <div class="grid grid-cols-2 gap-2 text-xs">
                  <div>
                    <span class="text-slate-500">Status:</span>
                    <span class="ml-1 font-medium">${order.status}</span>
                  </div>
                  <div>
                    <span class="text-slate-500">Type:</span>
                    <span class="ml-1 font-medium">${order.order_type}</span>
                  </div>
                  <div>
                    <span class="text-slate-500">Points used:</span>
                    <span class="ml-1 font-medium">${order.points_used || 0}</span>
                  </div>
                  <div>
                    <span class="text-slate-500">Points earned:</span>
                    <span class="ml-1 font-medium text-green-600">+${order.points_earned || 0}</span>
                  </div>
                </div>
                <p class="text-[10px] text-slate-400 mt-3">${order.time_ago || ''}</p>
              </div>
            `,
            confirmButtonColor: '#d97706',
            confirmButtonText: 'Close'
          });
        };

        window.cancelOrder = function (orderId) {
          Swal.fire({
            title: 'Cancel Order?',
            text: 'Are you sure you want to cancel this order? This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d97706',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Yes, cancel it'
          }).then((result) => {
            if (result.isConfirmed) {
              // Show loading
              Swal.fire({
                title: 'Cancelling order...',
                html: 'Please wait',
                allowOutsideClick: false,
                didOpen: () => {
                  Swal.showLoading();
                }
              });

              // Send cancel request
              fetch('../../controller/customer/post/place_order.php', {
                method: 'POST',
                headers: {
                  'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                  action: 'cancel_order',
                  order_id: orderId
                })
              })
                .then(response => response.json())
                .then(data => {
                  if (data.success) {
                    Swal.fire({
                      title: 'Cancelled!',
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
                  console.error('Error:', error);
                  Swal.fire({
                    title: 'Error',
                    text: 'Failed to cancel order. Please try again.',
                    icon: 'error',
                    confirmButtonColor: '#d97706'
                  });
                });
            }
          });
        };

        // Ensure cart is empty on load
        window.addEventListener('load', function () {
          order = [];
          pointsUsed = 0;
          freeItemAdded = false;
          renderOrder();
        });
      })();
    </script>
  </body>

</html>