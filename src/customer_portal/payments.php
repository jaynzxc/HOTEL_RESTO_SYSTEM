<?php require_once '../../controller/customer/get/payments.php' ?>

<!DOCTYPE html>
<html lang="en">

  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payments · Customer Portal</title>
    <!-- Tailwind via CDN + Font Awesome 6 -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
      .receipt-print {
        font-family: monospace;
      }

      @media print {
        body * {
          visibility: hidden;
        }

        #receiptModal,
        #receiptModal * {
          visibility: visible;
        }

        #receiptModal {
          position: absolute;
          left: 0;
          top: 0;
          width: 100%;
          background: white;
          padding: 20px;
        }

        .no-print {
          display: none !important;
        }
      }

      .payment-highlight {
        border-left: 4px solid #f59e0b;
      }

      .toast {
        animation: slideIn 0.3s ease-out;
      }

      @keyframes slideIn {
        from {
          transform: translateX(100%);
          opacity: 0;
        }

        to {
          transform: translateX(0);
          opacity: 1;
        }
      }
    </style>
  </head>

  <body class="bg-slate-50 font-sans antialiased">

    <!-- Toast Container -->
    <div id="toastContainer" class="fixed top-4 right-4 z-50 space-y-2"></div>

    <!-- main flex wrapper (sidebar + content) -->
    <div class="min-h-screen flex flex-col lg:flex-row">

      <!-- ========== SIDEBAR ========== -->
      <?php require './components/customer_nav.php' ?>
      <!-- ========== MAIN CONTENT ========== -->
      <main class="flex-1 p-5 lg:p-8 overflow-y-auto">

        <!-- Display Messages -->
        <?php if (!empty($_SESSION['success'])): ?>
            <div class="bg-green-50 border border-green-200 text-green-600 px-4 py-3 rounded-lg mb-4">
              <?php foreach ($_SESSION['success'] as $message): ?>
                  <p class="text-sm"><i class="fa-regular fa-circle-check mr-2"></i><?php echo htmlspecialchars($message); ?>
                  </p>
              <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($_SESSION['error'])): ?>
            <div class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-lg mb-4">
              <ul class="list-disc list-inside text-sm">
                <?php foreach ($_SESSION['error'] as $field => $message): ?>
                    <li><?php echo htmlspecialchars($message); ?></li>
                <?php endforeach; ?>
              </ul>
            </div>
        <?php endif; ?>

        <!-- header -->
        <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
          <div>
            <h1 class="text-2xl lg:text-3xl font-light text-slate-800">Payments</h1>
            <p class="text-sm text-slate-500 mt-0.5">manage your transactions and payment methods</p>
          </div>
          <div
            class="bg-white border border-slate-200 rounded-full px-4 py-2 text-sm flex items-center gap-2 shadow-sm">
            <i class="fa-regular fa-calendar text-slate-400"></i>
            <span id="currentDate"></span>
          </div>
        </div>

        <!-- Pending Restaurant Down Payment Alert (shown when redirected) -->
        <div id="pendingRestaurantPaymentAlert" class="mb-6 bg-amber-50 border border-amber-200 rounded-2xl p-4 hidden">
          <div class="flex items-start gap-3">
            <div class="bg-amber-100 rounded-full p-2">
              <i class="fa-solid fa-utensils text-amber-600"></i>
            </div>
            <div class="flex-1">
              <h3 class="font-semibold text-amber-800">Restaurant Reservation Down Payment Required</h3>
              <p class="text-sm text-amber-700 mt-1" id="pendingPaymentDetails"></p>
              <div class="flex gap-3 mt-3">
                <button onclick="processRestaurantDownPayment()"
                  class="bg-amber-600 hover:bg-amber-700 text-white px-4 py-2 rounded-xl text-sm font-medium transition">
                  <i class="fa-regular fa-credit-card mr-2"></i>Pay Now
                </button>
                <button onclick="dismissRestaurantPayment()"
                  class="border border-amber-300 text-amber-700 px-4 py-2 rounded-xl text-sm font-medium hover:bg-amber-100 transition">
                  Later
                </button>
              </div>
            </div>
            <button onclick="dismissRestaurantPayment()" class="text-amber-400 hover:text-amber-600">
              <i class="fa-solid fa-xmark text-xl"></i>
            </button>
          </div>
        </div>

        <!-- Pending Hotel Booking Alert (shown when redirected) -->
        <div id="pendingHotelPaymentAlert" class="mb-6 bg-blue-50 border border-blue-200 rounded-2xl p-4 hidden">
          <div class="flex items-start gap-3">
            <div class="bg-blue-100 rounded-full p-2">
              <i class="fa-solid fa-hotel text-blue-600"></i>
            </div>
            <div class="flex-1">
              <h3 class="font-semibold text-blue-800">Hotel Booking Payment Required</h3>
              <p class="text-sm text-blue-700 mt-1" id="pendingHotelDetails"></p>
              <div class="flex gap-3 mt-3">
                <button onclick="processHotelPayment()"
                  class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl text-sm font-medium transition">
                  <i class="fa-regular fa-credit-card mr-2"></i>Pay Now
                </button>
                <button onclick="dismissHotelPayment()"
                  class="border border-blue-300 text-blue-700 px-4 py-2 rounded-xl text-sm font-medium hover:bg-blue-100 transition">
                  Later
                </button>
              </div>
            </div>
            <button onclick="dismissHotelPayment()" class="text-blue-400 hover:text-blue-600">
              <i class="fa-solid fa-xmark text-xl"></i>
            </button>
          </div>
        </div>

        <!-- ===== BALANCE & QUICK ACTIONS ===== -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-8">
          <!-- current balance card -->
          <div class="bg-gradient-to-br from-amber-600 to-amber-700 text-white rounded-2xl p-6 shadow-md">
            <p class="text-sm opacity-90 flex items-center gap-1"><i class="fa-regular fa-credit-card"></i> current
              balance</p>
            <p class="text-3xl font-bold mt-2" id="currentBalance">₱<?php echo number_format($currentBalance, 2); ?>
            </p>
            <p class="text-xs opacity-80 mt-1" id="balanceMessage">
              <?php echo $currentBalance > 0 ? 'due for payment' : 'no outstanding balance'; ?>
            </p>
            <button id="payNowBtn"
              class="mt-4 bg-white text-amber-700 px-4 py-2 rounded-xl text-sm font-medium hover:bg-amber-50 transition <?php echo $currentBalance > 0 ? '' : 'opacity-50 cursor-not-allowed'; ?>"
              <?php echo $currentBalance > 0 ? '' : 'disabled'; ?>>
              pay now
            </button>
          </div>
          <!-- payment methods summary -->
          <div class="bg-white rounded-2xl border border-slate-200 p-5">
            <h3 class="font-semibold flex items-center gap-2"><i class="fa-regular fa-credit-card text-amber-600"></i>
              payment methods</h3>
            <div class="mt-3 space-y-2" id="paymentMethodsSummary">
              <?php if (empty($paymentMethods)): ?>
                  <p class="text-sm text-slate-500 italic">no payment methods added</p>
              <?php else: ?>
                  <?php foreach (array_slice($paymentMethods, 0, 3) as $method): ?>
                      <div class="flex items-center gap-2 text-sm">
                        <?php
                        $icon = '';
                        switch ($method['method_type']) {
                          case 'gcash':
                            $icon = '<i class="fa-brands fa-gcash text-blue-500 text-lg"></i>';
                            break;
                          case 'visa':
                            $icon = '<i class="fa-brands fa-cc-visa text-slate-400"></i>';
                            break;
                          case 'mastercard':
                            $icon = '<i class="fa-brands fa-cc-mastercard text-slate-400"></i>';
                            break;
                          case 'cash':
                            $icon = '<i class="fa-solid fa-money-bill-wave text-amber-600"></i>';
                            break;
                        }
                        echo $icon;
                        ?>
                        <span><?php echo htmlspecialchars($method['account_name']); ?></span>
                        <?php if ($method['is_default']): ?>
                            <span class="text-xs bg-green-100 text-green-700 px-2 rounded-full">default</span>
                        <?php endif; ?>
                      </div>
                  <?php endforeach; ?>
                  <?php if (count($paymentMethods) > 3): ?>
                      <p class="text-xs text-slate-400 mt-1">+<?php echo count($paymentMethods) - 3; ?> more</p>
                  <?php endif; ?>
              <?php endif; ?>
            </div>
            <button onclick="openAddPaymentModal()"
              class="text-amber-700 text-sm mt-3 flex items-center gap-1 hover:underline"><i
                class="fa-regular fa-plus"></i> add new</button>
          </div>
          <!-- recent transaction summary -->
          <div class="bg-white rounded-2xl border border-slate-200 p-5">
            <h3 class="font-semibold flex items-center gap-2"><i
                class="fa-regular fa-clock-rotate-left text-amber-600"></i> this month</h3>
            <p class="text-2xl font-bold mt-2" id="monthlyTotal">
              ₱<?php echo number_format($monthlyStats['total'], 2); ?>
            </p>
            <p class="text-xs text-slate-500" id="monthlyCount">
              total spent · <?php echo $monthlyStats['count']; ?>
              transaction<?php echo $monthlyStats['count'] != 1 ? 's' : ''; ?>
            </p>
            <p class="text-xs text-slate-400 mt-2" id="monthlyComparison">
              <?php if ($lastMonthTotal['total'] > 0): ?>
                  <?php
                  $symbol = $percentChange >= 0 ? '↑' : '↓';
                  $color = $percentChange >= 0 ? 'text-green-600' : 'text-red-600';
                  ?>
                  <span class="<?php echo $color; ?>"><?php echo $symbol; ?>
                    <?php echo number_format(abs($percentChange), 1); ?>% from last month</span>
              <?php elseif ($monthlyStats['total'] > 0): ?>
                  <span class="text-green-600">↑ new spending this month</span>
              <?php else: ?>
                  no transactions this month
              <?php endif; ?>
            </p>
          </div>
        </div>

        <!-- ===== PAYMENT METHODS DETAILED ===== -->
        <div class="bg-white rounded-2xl border border-slate-200 p-6 mb-8">
          <div class="flex items-center justify-between mb-4">
            <h2 class="font-semibold text-lg flex items-center gap-2"><i
                class="fa-regular fa-credit-card text-amber-600"></i> your payment methods</h2>
            <button onclick="openAddPaymentModal()"
              class="bg-amber-600 hover:bg-amber-700 text-white px-4 py-2 rounded-xl text-sm">+ add method</button>
          </div>
          <div id="paymentMethodsGrid" class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <?php if (empty($paymentMethods)): ?>
                <div class="col-span-full text-center py-8 text-slate-500">
                  <i class="fa-regular fa-credit-card text-4xl mb-3 text-slate-300"></i>
                  <p class="text-sm">No payment methods added yet</p>
                  <p class="text-xs text-slate-400 mt-1">Add a payment method to get started</p>
                </div>
            <?php else: ?>
                <?php foreach ($paymentMethods as $index => $method): ?>
                    <div class="border border-slate-200 rounded-xl p-4 flex items-center justify-between">
                      <div class="flex items-center gap-3">
                        <div class="h-10 w-10 <?php
                        switch ($method['method_type']) {
                          case 'gcash':
                            echo 'bg-blue-100 text-blue-600';
                            break;
                          case 'cash':
                            echo 'bg-amber-100 text-amber-600';
                            break;
                          default:
                            echo 'bg-slate-100 text-slate-600';
                        }
                        ?> rounded-full flex items-center justify-center">
                          <?php
                          switch ($method['method_type']) {
                            case 'gcash':
                              echo '<i class="fa-brands fa-gcash text-xl"></i>';
                              break;
                            case 'visa':
                              echo '<i class="fa-brands fa-cc-visa text-xl"></i>';
                              break;
                            case 'mastercard':
                              echo '<i class="fa-brands fa-cc-mastercard text-xl"></i>';
                              break;
                            case 'cash':
                              echo '<i class="fa-solid fa-money-bill-wave text-xl"></i>';
                              break;
                          }
                          ?>
                        </div>
                        <div>
                          <p class="font-medium"><?php echo htmlspecialchars($method['display_name']); ?></p>
                          <p class="text-xs text-slate-500">
                            <?php echo htmlspecialchars($method['account_name']); ?>
                            <?php if (!empty($method['expiry_date'])): ?>
                                · <?php echo htmlspecialchars($method['expiry_date']); ?>
                            <?php endif; ?>
                          </p>
                        </div>
                      </div>
                      <div class="flex gap-2">
                        <?php if ($method['is_default']): ?>
                            <span class="bg-green-100 text-green-700 text-xs px-2 py-1 rounded-full">default</span>
                        <?php endif; ?>
                        <button onclick="setDefaultPayment(<?php echo $method['id']; ?>)"
                          class="text-slate-400 hover:text-amber-600" title="Set as default">
                          <i class="fa-regular fa-star"></i>
                        </button>
                        <button onclick="deletePaymentMethod(<?php echo $method['id']; ?>)"
                          class="text-slate-400 hover:text-red-600">
                          <i class="fa-regular fa-trash-can"></i>
                        </button>
                      </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>

        <!-- ===== RECENT TRANSACTIONS ===== -->
        <div class="bg-white rounded-2xl border border-slate-200 p-6">
          <div class="flex items-center justify-between mb-4">
            <h2 class="font-semibold text-lg flex items-center gap-2"><i
                class="fa-regular fa-rectangle-list text-amber-600"></i> recent transactions</h2>
            <button onclick="viewAllTransactions()" class="text-sm text-amber-700 hover:underline">view all</button>
          </div>
          <div class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead class="text-slate-400 text-xs border-b">
                <tr>
                  <td class="pb-2">date</td>
                  <td>description</td>
                  <td>amount</td>
                  <td>status</td>
                  <td></td>
                </tr>
              </thead>
              <tbody id="transactionsTable" class="divide-y">
                <?php if (empty($transactions)): ?>
                    <tr>
                      <td colspan="5" class="py-12 text-center text-slate-500">
                        <i class="fa-regular fa-clock text-3xl mb-2 text-slate-300"></i>
                        <p class="text-sm">No recent transactions</p>
                        <p class="text-xs text-slate-400 mt-1">Your transactions will appear here</p>
                      </td>
                    </tr>
                <?php else: ?>
                    <?php foreach (array_slice($transactions, 0, 5) as $transaction): ?>
                        <tr>
                          <td class="py-3">
                            <?php echo date('M d, Y', strtotime($transaction['created_at'])); ?>
                          </td>
                          <td><?php echo htmlspecialchars($transaction['description']); ?></td>
                          <td class="font-medium">₱<?php echo number_format($transaction['amount'], 2); ?></td>
                          <td>
                            <?php
                            $statusClass = '';
                            switch ($transaction['status']) {
                              case 'completed':
                                $statusClass = 'bg-green-100 text-green-700';
                                break;
                              case 'pending':
                                $statusClass = 'bg-yellow-100 text-yellow-700';
                                break;
                              case 'failed':
                                $statusClass = 'bg-red-100 text-red-700';
                                break;
                            }
                            ?>
                            <span class="<?php echo $statusClass; ?> px-2 py-0.5 rounded-full text-xs">
                              <?php echo $transaction['status']; ?>
                            </span>
                          </td>
                          <td>
                            <?php if ($transaction['status'] === 'completed'): ?>
                                <button onclick="viewReceipt(<?php echo $transaction['id']; ?>)"
                                  class="text-amber-700 text-xs">receipt</button>
                            <?php endif; ?>
                          </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>

        <!-- ===== PROMO / PAYMENT INFO ===== -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mt-8">
          <div class="bg-amber-50 border border-amber-200 rounded-2xl p-5 flex items-center gap-4">
            <i class="fa-regular fa-circle-question text-3xl text-amber-600"></i>
            <div>
              <p class="font-medium">payment support</p>
              <p class="text-xs text-slate-600">contact billing@lucas.stay or +63 2 1234 5678</p>
            </div>
          </div>
          <div class="bg-white border border-slate-200 rounded-2xl p-5 flex items-center gap-4">
            <i class="fa-regular fa-gem text-2xl text-amber-600"></i>
            <div>
              <p class="font-medium">earn points with every payment</p>
              <p class="text-xs text-slate-500">5 pts per ₱100 spent</p>
            </div>
          </div>
        </div>

        <!-- Add Payment Method Modal -->
        <div id="addPaymentModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
          <div class="bg-white rounded-2xl p-6 max-w-md w-full mx-4">
            <div class="flex justify-between items-center mb-4">
              <h3 class="text-xl font-semibold">Add Payment Method</h3>
              <button onclick="closeAddPaymentModal()" class="text-slate-400 hover:text-slate-600">
                <i class="fa-solid fa-xmark text-2xl"></i>
              </button>
            </div>
            <form id="paymentMethodForm" onsubmit="addPaymentMethod(event)">
              <input type="hidden" name="action" value="add_payment_method">

              <div class="mb-4">
                <label class="block text-sm font-medium text-slate-700 mb-2">Payment Type</label>
                <select id="paymentType" name="method_type"
                  class="w-full border border-slate-200 rounded-xl px-4 py-2.5 focus:outline-none focus:border-amber-600"
                  required>
                  <option value="">Select type</option>
                  <option value="gcash">GCash</option>
                  <option value="visa">Visa</option>
                  <option value="mastercard">Mastercard</option>
                  <option value="cash">Cash on arrival</option>
                </select>
              </div>
              <div class="mb-4">
                <label class="block text-sm font-medium text-slate-700 mb-2">Account Name</label>
                <input type="text" id="accountName" name="account_name"
                  class="w-full border border-slate-200 rounded-xl px-4 py-2.5 focus:outline-none focus:border-amber-600"
                  required>
              </div>
              <div class="mb-4" id="cardNumberField">
                <label class="block text-sm font-medium text-slate-700 mb-2" id="cardNumberLabel">Card Number /
                  Account</label>
                <input type="text" id="accountNumber" name="account_number"
                  class="w-full border border-slate-200 rounded-xl px-4 py-2.5 focus:outline-none focus:border-amber-600">
              </div>
              <div class="mb-4" id="expiryField">
                <label class="block text-sm font-medium text-slate-700 mb-2">Expiry Date</label>
                <input type="month" id="expiryDate" name="expiry_date"
                  class="w-full border border-slate-200 rounded-xl px-4 py-2.5 focus:outline-none focus:border-amber-600">
              </div>
              <div class="flex gap-3">
                <button type="button" onclick="closeAddPaymentModal()"
                  class="flex-1 border border-slate-200 text-slate-700 px-4 py-2.5 rounded-xl text-sm font-medium hover:bg-slate-50 transition">Cancel</button>
                <button type="submit"
                  class="flex-1 bg-amber-600 hover:bg-amber-700 text-white px-4 py-2.5 rounded-xl text-sm font-medium transition">Add
                  Method</button>
              </div>
            </form>
          </div>
        </div>

        <!-- Make Payment Modal -->
        <div id="paymentModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
          <div class="bg-white rounded-2xl p-6 max-w-md w-full mx-4">
            <div class="flex justify-between items-center mb-4">
              <h3 class="text-xl font-semibold">Make Payment</h3>
              <button onclick="closePaymentModal()" class="text-slate-400 hover:text-slate-600">
                <i class="fa-solid fa-xmark text-2xl"></i>
              </button>
            </div>
            <form id="paymentForm" onsubmit="processPayment(event)">
              <input type="hidden" name="action" value="process_payment">
              <input type="hidden" name="booking_id" id="paymentBookingId" value="">

              <div class="mb-4">
                <label class="block text-sm font-medium text-slate-700 mb-2">Amount (₱)</label>
                <input type="number" id="paymentAmount" name="amount"
                  class="w-full border border-slate-200 rounded-xl px-4 py-2.5 focus:outline-none focus:border-amber-600"
                  required min="1" step="0.01" value="<?php echo $currentBalance; ?>">
              </div>
              <div class="mb-4">
                <label class="block text-sm font-medium text-slate-700 mb-2">Description</label>
                <input type="text" id="paymentDescription" name="description"
                  class="w-full border border-slate-200 rounded-xl px-4 py-2.5 focus:outline-none focus:border-amber-600"
                  required>
              </div>
              <div class="mb-4">
                <label class="block text-sm font-medium text-slate-700 mb-2">Payment Method</label>
                <select id="paymentMethod" name="payment_method_id"
                  class="w-full border border-slate-200 rounded-xl px-4 py-2.5 focus:outline-none focus:border-amber-600"
                  required>
                  <option value="">Select method</option>
                  <?php foreach ($paymentMethods as $method): ?>
                      <option value="<?php echo $method['id']; ?>" <?php echo $method['is_default'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($method['display_name'] . ' - ' . $method['account_name']); ?>
                      </option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="flex gap-3">
                <button type="button" onclick="closePaymentModal()"
                  class="flex-1 border border-slate-200 text-slate-700 px-4 py-2.5 rounded-xl text-sm font-medium hover:bg-slate-50 transition">Cancel</button>
                <button type="submit"
                  class="flex-1 bg-amber-600 hover:bg-amber-700 text-white px-4 py-2.5 rounded-xl text-sm font-medium transition">Pay
                  Now</button>
              </div>
            </form>
          </div>
        </div>

        <!-- Receipt Modal -->
        <div id="receiptModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
          <div class="bg-white rounded-2xl p-6 max-w-md w-full mx-4">
            <div class="flex justify-between items-center mb-4">
              <h3 class="text-xl font-semibold">Payment Receipt</h3>
              <button onclick="closeReceiptModal()" class="text-slate-400 hover:text-slate-600">
                <i class="fa-solid fa-xmark text-2xl"></i>
              </button>
            </div>
            <div id="receiptContent" class="space-y-3">
              <!-- Receipt content will be inserted here dynamically -->
            </div>
            <div class="mt-6 flex gap-3">
              <button onclick="printReceipt()"
                class="flex-1 bg-amber-600 hover:bg-amber-700 text-white px-4 py-2.5 rounded-xl text-sm font-medium transition">
                <i class="fa-solid fa-print mr-2"></i>Print
              </button>
              <button onclick="closeReceiptModal()"
                class="flex-1 border border-slate-200 text-slate-700 px-4 py-2.5 rounded-xl text-sm font-medium hover:bg-slate-50 transition">Close</button>
            </div>
          </div>
        </div>
      </main>
    </div>
    <script>
      // Pass PHP variables to JavaScript
      const userData = {
        id: <?php echo $_SESSION['user_id']; ?>,
        name: '<?php echo addslashes($user['full_name'] ?? ''); ?>',
        points: <?php echo $user['loyalty_points'] ?? 0; ?>,
        tier: '<?php echo $user['member_tier'] ?? 'bronze'; ?>'
      };

      const balanceData = {
        total: <?php echo $balanceData['total_balance'] ?? 0; ?>,
        pending: <?php echo $balanceData['pending_balance'] ?? 0; ?>,
        available: <?php echo $balanceData['available_balance'] ?? 0; ?>
      };

      let paymentMethods = <?php echo json_encode($paymentMethods); ?>;
      let payments = <?php echo json_encode($payments); ?>;
      let currentBalance = balanceData.available;
      let totalBalance = balanceData.total;
      let pendingBalance = balanceData.pending;
      let loyaltyPoints = userData.points;

      // Store pending payments
      let pendingRestaurantPayment = null;
      let pendingRestaurantReservation = null;
      let pendingHotelPayment = null;
      let pendingHotelReservation = null;

      // Initialize the page
      document.addEventListener('DOMContentLoaded', function () {
        updateDate();
        checkForPendingHotelBooking();
        checkForPendingRestaurantPayment();
        setupPaymentTypeHandler();
        updateUI();
        updatePaymentsTable();
        updateBalanceMessage();
      });

      // Helper functions
      function showToast(message, type = 'success') {
        const container = document.getElementById('toastContainer');
        const toast = document.createElement('div');

        const colors = {
          success: 'bg-green-500',
          error: 'bg-red-500',
          info: 'bg-amber-500',
          warning: 'bg-orange-500'
        };

        toast.className = `toast ${colors[type]} text-white px-6 py-3 rounded-xl shadow-lg flex items-center gap-3`;
        toast.innerHTML = `
            <i class="fa-regular ${type === 'success' ? 'fa-circle-check' : type === 'error' ? 'fa-circle-exclamation' : 'fa-bell'}"></i>
            <span class="text-sm font-medium">${message}</span>
        `;

        container.appendChild(toast);

        setTimeout(() => {
          toast.style.opacity = '0';
          toast.style.transform = 'translateX(100%)';
          setTimeout(() => toast.remove(), 300);
        }, 3000);
      }

      function updateDate() {
        const date = new Date();
        const options = {
          weekday: 'long',
          year: 'numeric',
          month: 'long',
          day: 'numeric'
        };
        document.getElementById('currentDate').textContent = date.toLocaleDateString('en-US', options).toLowerCase();
      }

      function updateBalanceMessage() {
        const balanceMessage = document.getElementById('balanceMessage');
        const payNowBtn = document.getElementById('payNowBtn');

        if (balanceMessage) {
          if (currentBalance > 0) {
            balanceMessage.textContent = 'due for payment';
            if (payNowBtn) {
              payNowBtn.disabled = false;
              payNowBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            }
          } else {
            balanceMessage.textContent = 'no outstanding balance';
            if (payNowBtn) {
              payNowBtn.disabled = true;
              payNowBtn.classList.add('opacity-50', 'cursor-not-allowed');
            }
          }
        }
      }

      function setupPaymentTypeHandler() {
        const paymentType = document.getElementById('paymentType');
        if (paymentType) {
          paymentType.addEventListener('change', function (e) {
            const type = e.target.value;
            const expiryField = document.getElementById('expiryField');
            const cardNumberField = document.getElementById('cardNumberField');
            const cardNumberLabel = document.getElementById('cardNumberLabel');
            const accountNumber = document.getElementById('accountNumber');

            if (type === 'cash') {
              expiryField.style.display = 'none';
              cardNumberLabel.textContent = 'Reference (optional)';
              accountNumber.required = false;
            } else {
              expiryField.style.display = 'block';
              cardNumberLabel.textContent = type === 'gcash' ? 'GCash Number' : 'Card Number';
              accountNumber.required = true;
            }
          });
        }
      }

      function updatePaymentsTable() {
        const tableBody = document.getElementById('transactionsTable');
        if (!tableBody) return;

        if (!payments || payments.length === 0) {
          tableBody.innerHTML = `
                <tr>
                    <td colspan="5" class="py-12 text-center text-slate-500">
                        <i class="fa-regular fa-clock text-3xl mb-2 text-slate-300"></i>
                        <p class="text-sm">No recent transactions</p>
                        <p class="text-xs text-slate-400 mt-1">Your transactions will appear here</p>
                    </td>
                </tr>
            `;
          return;
        }

        let html = '';
        const recentPayments = payments.slice(0, 5);

        recentPayments.forEach(payment => {
          const date = new Date(payment.created_at).toLocaleDateString('en-US', {
            month: 'short',
            day: 'numeric',
            year: 'numeric'
          });

          let statusClass = '';
          switch (payment.payment_status) {
            case 'completed':
              statusClass = 'bg-green-100 text-green-700';
              break;
            case 'pending':
              statusClass = 'bg-yellow-100 text-yellow-700';
              break;
            case 'failed':
              statusClass = 'bg-red-100 text-red-700';
              break;
          }

          const description = payment.booking_type === 'hotel' ? 'Hotel Booking' : 'Restaurant Reservation';

          html += `
                <tr>
                    <td class="py-3">${date}</td>
                    <td>${description}</td>
                    <td class="font-medium">₱${parseFloat(payment.amount).toFixed(2)}</td>
                    <td>
                        <span class="${statusClass} px-2 py-0.5 rounded-full text-xs">
                            ${payment.payment_status}
                        </span>
                    </td>
                    <td>
                        <button onclick="viewReceipt(${payment.id})" class="text-amber-700 text-xs hover:underline">
                            receipt
                        </button>
                    </td>
                </tr>
            `;
        });

        tableBody.innerHTML = html;
      }

      // Check for pending payments
      function checkForPendingRestaurantPayment() {
        const pendingDownPayment = sessionStorage.getItem('pendingDownPayment');
        if (pendingDownPayment) {
          try {
            const paymentData = JSON.parse(pendingDownPayment);
            pendingRestaurantPayment = {
              amount: paymentData.amount,
              description: paymentData.description,
              customerName: paymentData.customerName,
              customerEmail: paymentData.customerEmail,
              customerPhone: paymentData.customerPhone
            };
            pendingRestaurantReservation = {
              id: `REST${Date.now()}`,
              type: 'restaurant',
              status: 'confirmed',
              title: 'Restaurant Table',
              details: {
                guests: paymentData.guests || 2,
                date: paymentData.date || new Date().toISOString().split('T')[0],
                time: paymentData.time || '7:00 PM',
                table: paymentData.table || 'TBD',
                downPayment: paymentData.amount,
                customerName: paymentData.customerName,
                customerEmail: paymentData.customerEmail,
                customerPhone: paymentData.customerPhone
              },
              category: 'upcoming'
            };
            showRestaurantPaymentAlert(pendingRestaurantPayment);
            sessionStorage.removeItem('pendingDownPayment');
          } catch (e) {
            console.error('Error parsing pending restaurant payment', e);
          }
        }
      }

      function checkForPendingHotelBooking() {
        const pendingBooking = sessionStorage.getItem('pendingBooking');
        if (pendingBooking) {
          try {
            const bookingData = JSON.parse(pendingBooking);
            pendingHotelPayment = {
              amount: bookingData.total || 5000,
              description: bookingData.description || 'Hotel booking',
              roomName: bookingData.room_name,
              checkIn: bookingData.check_in,
              checkOut: bookingData.check_out,
              adults: bookingData.adults || 2,
              bookingId: bookingData.id
            };
            pendingHotelReservation = {
              id: `HOTEL${Date.now()}`,
              type: 'hotel',
              status: 'confirmed',
              title: bookingData.room_name || 'Hotel Room',
              details: {
                room: bookingData.room_name || 'Deluxe Room',
                checkIn: bookingData.check_in || new Date().toISOString().split('T')[0],
                checkOut: bookingData.check_out || new Date(Date.now() + 86400000 * 3).toISOString().split('T')[0],
                adults: bookingData.adults || 2,
                total: bookingData.total || 5000
              },
              category: 'upcoming'
            };
            showHotelPaymentAlert(pendingHotelPayment);
            sessionStorage.removeItem('pendingBooking');
          } catch (e) {
            console.error('Error parsing pending hotel booking', e);
          }
        }
      }

      function showRestaurantPaymentAlert(payment) {
        const alert = document.getElementById('pendingRestaurantPaymentAlert');
        const details = document.getElementById('pendingPaymentDetails');

        if (!alert || !details) return;

        const formattedAmount = new Intl.NumberFormat('en-PH', {
          style: 'currency',
          currency: 'PHP'
        }).format(payment.amount);

        details.innerHTML = `
            <span class="font-bold">${formattedAmount}</span> for ${payment.description}<br>
            <span class="text-xs">Customer: ${payment.customerName || 'Guest'}</span>
        `;

        alert.classList.remove('hidden');
      }

      function showHotelPaymentAlert(payment) {
        const alert = document.getElementById('pendingHotelPaymentAlert');
        const details = document.getElementById('pendingHotelDetails');

        if (!alert || !details) return;

        const formattedAmount = new Intl.NumberFormat('en-PH', {
          style: 'currency',
          currency: 'PHP'
        }).format(payment.amount);

        details.innerHTML = `
            <span class="font-bold">${formattedAmount}</span> for ${payment.description}<br>
            <span class="text-xs">Room: ${payment.roomName || 'Standard Room'} · ${payment.adults} guests</span><br>
            <span class="text-xs">Check-in: ${payment.checkIn || 'Today'} · Check-out: ${payment.checkOut || 'Later'}</span>
        `;

        alert.classList.remove('hidden');
      }

      window.dismissRestaurantPayment = function () {
        const alert = document.getElementById('pendingRestaurantPaymentAlert');
        if (alert) alert.classList.add('hidden');
        pendingRestaurantPayment = null;
        pendingRestaurantReservation = null;
      };

      window.dismissHotelPayment = function () {
        const alert = document.getElementById('pendingHotelPaymentAlert');
        if (alert) alert.classList.add('hidden');
        pendingHotelPayment = null;
        pendingHotelReservation = null;
      };

      window.processRestaurantDownPayment = function () {
        if (!pendingRestaurantPayment) {
          dismissRestaurantPayment();
          return;
        }
        if (paymentMethods.length === 0) {
          showToast('Please add a payment method first', 'warning');
          openAddPaymentModal();
          sessionStorage.setItem('pendingRestaurantAfterMethod', JSON.stringify({
            payment: pendingRestaurantPayment,
            reservation: pendingRestaurantReservation
          }));
          return;
        }
        openPaymentModal();
        document.getElementById('paymentAmount').value = pendingRestaurantPayment.amount;
        document.getElementById('paymentDescription').value = pendingRestaurantPayment.description;
        document.getElementById('paymentForm').dataset.isRestaurantPayment = 'true';
        document.getElementById('paymentForm').dataset.restaurantReservation = JSON.stringify(pendingRestaurantReservation);
        dismissRestaurantPayment();
      };

      window.processHotelPayment = function () {
        if (!pendingHotelPayment) {
          dismissHotelPayment();
          return;
        }
        if (paymentMethods.length === 0) {
          showToast('Please add a payment method first', 'warning');
          openAddPaymentModal();
          sessionStorage.setItem('pendingHotelAfterMethod', JSON.stringify({
            payment: pendingHotelPayment,
            reservation: pendingHotelReservation
          }));
          return;
        }
        openPaymentModal();
        document.getElementById('paymentAmount').value = pendingHotelPayment.amount;
        document.getElementById('paymentDescription').value = pendingHotelPayment.description;
        if (pendingHotelPayment.bookingId) {
          document.getElementById('paymentBookingId').value = pendingHotelPayment.bookingId;
        }
        document.getElementById('paymentForm').dataset.isHotelPayment = 'true';
        document.getElementById('paymentForm').dataset.hotelReservation = JSON.stringify(pendingHotelReservation);
        dismissHotelPayment();
      };

      // Modal functions
      window.openAddPaymentModal = function () {
        const modal = document.getElementById('addPaymentModal');
        if (modal) {
          modal.classList.remove('hidden');
          modal.classList.add('flex');
        }
      };

      window.closeAddPaymentModal = function () {
        const modal = document.getElementById('addPaymentModal');
        if (modal) {
          modal.classList.add('hidden');
          modal.classList.remove('flex');
          document.getElementById('paymentMethodForm')?.reset();
        }
      };

      window.openPaymentModal = function () {
        if (currentBalance <= 0) {
          showToast('No outstanding balance to pay', 'info');
          return;
        }

        if (paymentMethods.length === 0) {
          showToast('Please add a payment method first', 'warning');
          openAddPaymentModal();
          return;
        }

        const modal = document.getElementById('paymentModal');
        if (modal) {
          // Set the amount to the current balance
          document.getElementById('paymentAmount').value = currentBalance.toFixed(2);
          document.getElementById('paymentDescription').value = 'Payment for outstanding balance';
          modal.classList.remove('hidden');
          modal.classList.add('flex');
        }
      };

      window.closePaymentModal = function () {
        const modal = document.getElementById('paymentModal');
        if (modal) {
          modal.classList.add('hidden');
          modal.classList.remove('flex');
          document.getElementById('paymentForm')?.reset();
          document.getElementById('paymentBookingId').value = '';
          delete document.getElementById('paymentForm')?.dataset.isRestaurantPayment;
          delete document.getElementById('paymentForm')?.dataset.restaurantReservation;
          delete document.getElementById('paymentForm')?.dataset.isHotelPayment;
          delete document.getElementById('paymentForm')?.dataset.hotelReservation;
        }
      };

      window.closeReceiptModal = function () {
        const modal = document.getElementById('receiptModal');
        if (modal) {
          modal.classList.add('hidden');
          modal.classList.remove('flex');
        }
      };

      // Payment method functions
      window.addPaymentMethod = async function (event) {
        event.preventDefault();

        const formData = new FormData(event.target);
        formData.append('action', 'add_payment_method');

        try {
          const response = await fetch('../../controller/customer/post/process_payment.php', {
            method: 'POST',
            body: formData
          });

          const result = await response.json();

          if (result.success) {
            showToast(result.message, 'success');
            setTimeout(() => location.reload(), 1500);
          } else {
            if (result.errors) {
              result.errors.forEach(error => showToast(error, 'error'));
            } else {
              showToast(result.message || 'Failed to add payment method', 'error');
            }
          }
        } catch (error) {
          console.error('Error:', error);
          showToast('An error occurred. Please try again.', 'error');
        }
      };

      window.setDefaultPayment = async function (methodId) {
        try {
          const formData = new FormData();
          formData.append('action', 'set_default_method');
          formData.append('method_id', methodId);

          const response = await fetch('../../controller/customer/post/process_payment.php', {
            method: 'POST',
            body: formData
          });

          const result = await response.json();

          if (result.success) {
            showToast(result.message, 'success');
            setTimeout(() => location.reload(), 1500);
          } else {
            showToast(result.message || 'Failed to set default method', 'error');
          }
        } catch (error) {
          console.error('Error:', error);
          showToast('An error occurred. Please try again.', 'error');
        }
      };

      window.deletePaymentMethod = async function (methodId) {
        if (!confirm('Are you sure you want to delete this payment method?')) {
          return;
        }

        try {
          const formData = new FormData();
          formData.append('action', 'delete_payment_method');
          formData.append('method_id', methodId);

          const response = await fetch('../../controller/customer/post/process_payment.php', {
            method: 'POST',
            body: formData
          });

          const result = await response.json();

          if (result.success) {
            showToast(result.message, 'success');
            setTimeout(() => location.reload(), 1500);
          } else {
            showToast(result.message || 'Failed to delete payment method', 'error');
          }
        } catch (error) {
          console.error('Error:', error);
          showToast('An error occurred. Please try again.', 'error');
        }
      };

      // Payment processing
      window.processPayment = async function (event) {
        event.preventDefault();

        const formData = new FormData(event.target);
        formData.append('action', 'process_payment');

        const isRestaurantPayment = document.getElementById('paymentForm')?.dataset.isRestaurantPayment === 'true';
        const isHotelPayment = document.getElementById('paymentForm')?.dataset.isHotelPayment === 'true';
        const restaurantReservation = document.getElementById('paymentForm')?.dataset.restaurantReservation;
        const hotelReservation = document.getElementById('paymentForm')?.dataset.hotelReservation;

        if (isRestaurantPayment) {
          formData.append('payment_type', 'restaurant');
        } else if (isHotelPayment) {
          formData.append('payment_type', 'hotel');
        }

        try {
          const response = await fetch('../../controller/customer/post/process_payment.php', {
            method: 'POST',
            body: formData
          });

          const result = await response.json();

          if (result.success) {
            showToast(result.message, 'success');
            closePaymentModal();

            // Update local balance
            const paidAmount = parseFloat(formData.get('amount'));
            currentBalance = Math.max(0, currentBalance - paidAmount);
            totalBalance = Math.max(0, totalBalance - paidAmount);
            updateUI();
            updateBalanceMessage();

            // Handle restaurant payment redirect
            if (isRestaurantPayment && restaurantReservation) {
              try {
                const reservation = JSON.parse(restaurantReservation);
                sessionStorage.setItem('newReservation', JSON.stringify(reservation));
                setTimeout(() => {
                  window.location.href = './my_reservation.php';
                }, 1500);
                return;
              } catch (e) {
                console.error('Error processing restaurant reservation', e);
              }
            }

            // Handle hotel payment redirect
            if (isHotelPayment && hotelReservation) {
              try {
                const reservation = JSON.parse(hotelReservation);
                sessionStorage.setItem('newReservation', JSON.stringify(reservation));
                setTimeout(() => {
                  window.location.href = './my_reservation.php';
                }, 1500);
                return;
              } catch (e) {
                console.error('Error processing hotel reservation', e);
              }
            }

            // Show receipt for normal payments
            setTimeout(() => {
              if (result.receipt && result.receipt.payment_id) {
                viewReceipt(result.receipt.payment_id);
              }
            }, 500);
          } else {
            if (result.errors) {
              result.errors.forEach(error => showToast(error, 'error'));
            } else {
              showToast(result.message || 'Payment failed', 'error');
            }
          }
        } catch (error) {
          console.error('Error:', error);
          showToast('An error occurred. Please try again.', 'error');
        }
      };

      // Receipt functions
      window.viewReceipt = function (paymentId) {
        const payment = payments.find(p => p.id == paymentId);
        if (!payment) {
          showToast('Payment not found', 'error');
          return;
        }

        const receiptContent = document.getElementById('receiptContent');
        const formattedAmount = new Intl.NumberFormat('en-PH', {
          style: 'currency',
          currency: 'PHP'
        }).format(payment.amount);

        receiptContent.innerHTML = `
            <div class="text-center border-b pb-3">
                <p class="font-bold text-lg">Lùcas.stay</p>
                <p class="text-xs text-slate-500">Payment Receipt</p>
            </div>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-slate-500">Date:</span>
                    <span>${new Date(payment.created_at).toLocaleString()}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Reference:</span>
                    <span class="font-mono">${payment.payment_reference}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Description:</span>
                    <span>${payment.booking_type == 'hotel' ? 'Hotel Booking' : 'Restaurant Reservation'}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Payment Method:</span>
                    <span>${payment.payment_method}</span>
                </div>
                <div class="flex justify-between border-t pt-2 mt-2">
                    <span class="font-bold">Amount:</span>
                    <span class="font-bold text-lg">${formattedAmount}</span>
                </div>
                <div class="flex justify-between text-xs text-slate-500">
                    <span>Status:</span>
                    <span class="text-green-600">${payment.payment_status}</span>
                </div>
            </div>
        `;

        document.getElementById('receiptModal').classList.remove('hidden');
        document.getElementById('receiptModal').classList.add('flex');
      };

      window.printReceipt = function () {
        window.print();
      };

      window.viewAllTransactions = function () {
        if (payments.length === 0) {
          showToast('No transactions to display', 'info');
          return;
        }
        showToast('View all transactions feature coming soon', 'info');
      };

      // Update UI functions
      function updateUI() {
        const balanceEl = document.getElementById('currentBalance');
        const pointsEl = document.getElementById('loyaltyPoints');
        const totalBalanceEl = document.querySelector('.total-balance'); // Add if you have this element
        const pendingBalanceEl = document.querySelector('.pending-balance'); // Add if you have this element

        if (balanceEl) balanceEl.textContent = `₱${currentBalance.toFixed(2)}`;
        if (pointsEl) pointsEl.textContent = loyaltyPoints.toLocaleString();
        if (totalBalanceEl) totalBalanceEl.textContent = `₱${totalBalance.toFixed(2)}`;
        if (pendingBalanceEl) pendingBalanceEl.textContent = `₱${pendingBalance.toFixed(2)}`;
      }

      // Pay now button
      const payNowBtn = document.getElementById('payNowBtn');
      if (payNowBtn) {
        payNowBtn.addEventListener('click', openPaymentModal);
      }

      // Initialize balance message on load
      updateBalanceMessage();
    </script>
  </body>

</html>

<?php
// Clear session messages after displaying
unset($_SESSION['success']);
unset($_SESSION['error']);
?>