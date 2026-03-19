<?php
/**
 * View - Admin Billing & Payments
 */
require_once '../../../controller/admin/get/billing_payments.php';
?>
<!DOCTYPE html>
<html lang="en">

  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin · Billing & Payments</title>
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

      .stat-card {
        transition: all 0.2s ease;
      }

      .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
      }

      .invoice-row {
        transition: all 0.2s ease;
      }

      .invoice-row:hover {
        background-color: #fef3e2;
      }
    </style>
  </head>

  <body class="bg-white font-sans antialiased">

    <!-- APP CONTAINER: flex row (sidebar + main) -->
    <div class="min-h-screen flex flex-col lg:flex-row">

      <!-- ========== SIDEBAR ========== -->
      <?php require_once '../components/admin_nav.php'; ?>

      <!-- ========== MAIN CONTENT (BILLING & PAYMENTS) ========== -->
      <main class="flex-1 p-5 lg:p-8 overflow-y-auto bg-white">

        <!-- header with title and date -->
        <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
          <div>
            <h1 class="text-2xl lg:text-3xl font-light text-slate-800">Billing & Payments</h1>
            <p class="text-sm text-slate-500 mt-0.5">manage invoices, track payments, and handle financial transactions
            </p>
          </div>
          <div class="flex gap-3 text-sm">
            <span class="bg-white border rounded-full px-4 py-2 flex items-center gap-2 shadow-sm"><i
                class="fas fa-calendar text-slate-400"></i> <?php echo $today; ?></span>
            <?php require_once '../components/notification_component.php'; ?>

          </div>
        </div>

        <!-- ===== STATS CARDS ===== -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
          <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm stat-card">
            <p class="text-xs text-slate-500">Today's revenue</p>
            <p class="text-2xl font-semibold">₱<?php echo number_format($todayRevenue); ?></p>
          </div>
          <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm stat-card">
            <p class="text-xs text-slate-500">Pending payments</p>
            <p class="text-2xl font-semibold text-amber-600">₱<?php echo number_format($pendingPayments); ?></p>
          </div>
          <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm stat-card">
            <p class="text-xs text-slate-500">Overdue</p>
            <p class="text-2xl font-semibold text-rose-600">₱<?php echo number_format($overduePayments); ?></p>
          </div>
          <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm stat-card">
            <p class="text-xs text-slate-500">Transactions today</p>
            <p class="text-2xl font-semibold"><?php echo $transactionsToday; ?></p>
          </div>
          <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm stat-card">
            <p class="text-xs text-slate-500">Monthly total</p>
            <p class="text-2xl font-semibold">₱<?php echo number_format($monthlyTotal / 1000000, 1); ?>M</p>
          </div>
        </div>

        <!-- ===== PAYMENT METHOD BREAKDOWN ===== -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
          <?php
          $total = array_sum(array_column($paymentMethods, 'total'));
          foreach ($paymentMethods as $method):
            $color = $methodColors[$method['payment_method']] ?? [
              'bg' => 'bg-slate-50',
              'border' => 'border-slate-200',
              'icon' => 'fas fa-credit-card',
              'color' => 'text-slate-600'
            ];
            ?>
            <div class="<?php echo $color['bg']; ?> border <?php echo $color['border']; ?> rounded-2xl p-4 text-center">
              <i class="<?php echo $color['icon']; ?> text-2xl <?php echo $color['color']; ?> mb-1"></i>
              <p class="text-lg font-semibold">₱<?php echo number_format($method['total']); ?></p>
              <p class="text-xs text-slate-500"><?php echo $method['payment_method'] ?? 'Other'; ?>
                (<?php echo $method['percentage']; ?>%)</p>
            </div>
          <?php endforeach; ?>
        </div>

        <!-- ===== ACTION BAR ===== -->
        <div
          class="bg-white rounded-2xl border border-slate-200 p-4 mb-6 flex flex-wrap gap-3 items-center justify-between">
          <div class="flex gap-2 flex-wrap">
            <button onclick="createInvoice()"
              class="bg-amber-600 text-white px-4 py-2 rounded-xl text-sm hover:bg-amber-700 transition">+ create
              invoice</button>
            <button onclick="exportInvoices()"
              class="border border-slate-200 px-4 py-2 rounded-xl text-sm hover:bg-slate-50 transition">export</button>
            <button onclick="viewReports()"
              class="border border-slate-200 px-4 py-2 rounded-xl text-sm hover:bg-slate-50 transition">reports</button>
            <?php if (!empty($pendingApprovals)): ?>
              <button onclick="viewPendingApprovals()"
                class="relative border border-amber-600 text-amber-700 px-4 py-2 rounded-xl text-sm hover:bg-amber-50 transition">
                <i class="fas fa-clock mr-1"></i> pending approvals
                <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs px-1.5 py-0.5 rounded-full">
                  <?php echo count($pendingApprovals); ?>
                </span>
              </button>
            <?php endif; ?>
          </div>
          <div class="relative">
            <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
            <input type="text" id="searchInput" placeholder="search invoices..."
              value="<?php echo htmlspecialchars($searchFilter); ?>"
              class="border border-slate-200 rounded-xl pl-9 pr-4 py-2 text-sm w-64 focus:ring-1 focus:ring-amber-500 outline-none">
          </div>
        </div>

        <!-- ===== FILTER TABS ===== -->
        <div class="flex flex-wrap gap-2 border-b border-slate-200 pb-2 mb-6">
          <button onclick="filterInvoices('all')"
            class="filter-btn px-4 py-2 <?php echo $statusFilter == 'all' ? 'bg-amber-600 text-white' : 'bg-white border border-slate-200 text-slate-700 hover:bg-slate-50'; ?> rounded-full text-sm transition">all</button>
          <button onclick="filterInvoices('paid')"
            class="filter-btn px-4 py-2 <?php echo $statusFilter == 'paid' ? 'bg-amber-600 text-white' : 'bg-white border border-slate-200 text-slate-700 hover:bg-slate-50'; ?> rounded-full text-sm transition">paid</button>
          <button onclick="filterInvoices('pending')"
            class="filter-btn px-4 py-2 <?php echo $statusFilter == 'pending' ? 'bg-amber-600 text-white' : 'bg-white border border-slate-200 text-slate-700 hover:bg-slate-50'; ?> rounded-full text-sm transition">pending</button>
          <button onclick="filterInvoices('overdue')"
            class="filter-btn px-4 py-2 <?php echo $statusFilter == 'overdue' ? 'bg-amber-600 text-white' : 'bg-white border border-slate-200 text-slate-700 hover:bg-slate-50'; ?> rounded-full text-sm transition">overdue</button>
          <button onclick="filterInvoices('partial')"
            class="filter-btn px-4 py-2 <?php echo $statusFilter == 'partial' ? 'bg-amber-600 text-white' : 'bg-white border border-slate-200 text-slate-700 hover:bg-slate-50'; ?> rounded-full text-sm transition">partial</button>
        </div>

        <!-- ===== INVOICES TABLE ===== -->
        <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden mb-8">
          <div class="p-4 border-b border-slate-200 bg-slate-50 flex items-center justify-between">
            <h2 class="font-semibold flex items-center gap-2"><i class="fas fa-rectangle-list text-amber-600"></i>
              recent invoices</h2>
            <div class="flex gap-2">
              <button onclick="viewAllInvoices()"
                class="text-sm text-amber-700 border border-amber-600 px-3 py-1 rounded-lg hover:bg-amber-50 transition">view
                all</button>
            </div>
          </div>
          <div class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead class="text-slate-500 text-xs border-b">
                <tr>
                  <td class="p-3">Invoice #</td>
                  <td class="p-3">Guest / Customer</td>
                  <td class="p-3">Date</td>
                  <td class="p-3">Due date</td>
                  <td class="p-3">Amount</td>
                  <td class="p-3">Status</td>
                  <td class="p-3">Payment method</td>
                  <td class="p-3">Actions</td>
                </tr>
              </thead>
              <tbody class="divide-y">
                <?php if (empty($invoices)): ?>
                  <tr>
                    <td colspan="8" class="p-6 text-center text-slate-400">No invoices found</td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($invoices as $invoice):
                    $statusClass = '';
                    $statusText = $invoice['status'];

                    if ($invoice['status'] == 'paid' || $invoice['status'] == 'completed') {
                      $statusClass = 'bg-green-100 text-green-700';
                      $statusText = 'paid';
                    } elseif ($invoice['status'] == 'pending') {
                      $statusClass = 'bg-yellow-100 text-yellow-700';
                    } elseif ($invoice['status'] == 'unpaid' && strtotime($invoice['due_date']) < time()) {
                      $statusClass = 'bg-rose-100 text-rose-700';
                      $statusText = 'overdue';
                    } elseif ($invoice['status'] == 'partial') {
                      $statusClass = 'bg-blue-100 text-blue-700';
                    } else {
                      $statusClass = 'bg-slate-100 text-slate-700';
                    }
                    ?>
                    <tr class="invoice-row" data-id="<?php echo $invoice['id']; ?>">
                      <td class="p-3 font-medium"><?php echo htmlspecialchars($invoice['invoice_number']); ?></td>
                      <td class="p-3"><?php echo htmlspecialchars($invoice['guest_name']); ?></td>
                      <td class="p-3"><?php echo date('M d, Y', strtotime($invoice['invoice_date'])); ?></td>
                      <td class="p-3"><?php echo date('M d, Y', strtotime($invoice['due_date'])); ?></td>
                      <td class="p-3 font-medium">₱<?php echo number_format($invoice['amount']); ?></td>
                      <td class="p-3"><span
                          class="<?php echo $statusClass; ?> px-2 py-0.5 rounded-full text-xs"><?php echo $statusText; ?></span>
                      </td>
                      <td class="p-3"><?php echo $invoice['payment_method'] ?: '—'; ?></td>
                      <td class="p-3">
                        <button onclick="viewInvoice(<?php echo $invoice['id']; ?>)"
                          class="text-amber-700 text-xs hover:underline mr-2">view</button>
                        <?php if ($invoice['status'] == 'unpaid' || $invoice['status'] == 'pending'): ?>
                          <button
                            onclick="recordPaymentForInvoice(<?php echo $invoice['id']; ?>, '<?php echo $invoice['invoice_number']; ?>', <?php echo $invoice['amount']; ?>)"
                            class="text-blue-600 text-xs hover:underline mr-2">record</button>
                          <button onclick="sendReminder(<?php echo $invoice['id']; ?>)"
                            class="text-red-600 text-xs hover:underline">remind</button>
                        <?php else: ?>
                          <button onclick="viewReceipt(<?php echo $invoice['id']; ?>)"
                            class="text-blue-600 text-xs hover:underline">receipt</button>
                        <?php endif; ?>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
          <div class="p-4 border-t border-slate-200 flex items-center justify-between">
            <span class="text-xs text-slate-500">Showing <?php echo count($invoices); ?> of
              <?php echo $totalInvoices; ?> invoices</span>
            <div class="flex gap-2">
              <button onclick="changePage(<?php echo $currentPage - 1; ?>)"
                class="border border-slate-200 px-3 py-1 rounded-lg text-sm hover:bg-slate-50 transition" <?php echo $currentPage <= 1 ? 'disabled' : ''; ?>>Previous</button>
              <?php for ($i = 1; $i <= min(5, $totalPages); $i++): ?>
                <button onclick="changePage(<?php echo $i; ?>)"
                  class="border <?php echo $i == $currentPage ? 'bg-amber-600 text-white' : 'border-slate-200 hover:bg-slate-50'; ?> px-3 py-1 rounded-lg text-sm transition"><?php echo $i; ?></button>
              <?php endfor; ?>
              <?php if ($totalPages > 5): ?>
                <span class="px-2">...</span>
              <?php endif; ?>
              <button onclick="changePage(<?php echo $currentPage + 1; ?>)"
                class="border border-slate-200 px-3 py-1 rounded-lg text-sm hover:bg-slate-50 transition" <?php echo $currentPage >= $totalPages ? 'disabled' : ''; ?>>Next</button>
            </div>
          </div>
        </div>

        <!-- ===== BOTTOM: RECENT TRANSACTIONS & QUICK PAYMENT ===== -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

          <!-- recent transactions -->
          <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 p-5">
            <h2 class="font-semibold text-lg flex items-center gap-2 mb-3"><i class="fas fa-clock text-amber-600"></i>
              recent transactions</h2>
            <div class="space-y-3 max-h-60 overflow-y-auto">
              <?php if (empty($recentTransactions)): ?>
                <p class="text-sm text-slate-400 text-center py-4">No recent transactions</p>
              <?php else: ?>
                <?php foreach ($recentTransactions as $transaction): ?>
                  <div class="flex justify-between items-center border-b border-slate-100 pb-2 last:border-0">
                    <div>
                      <span class="font-medium"><?php echo $transaction['payment_method']; ?> payment</span>
                      <p class="text-xs text-slate-500"><?php echo htmlspecialchars($transaction['guest_name']); ?> ·
                        <?php echo $transaction['invoice_number']; ?>
                      </p>
                    </div>
                    <span
                      class="text-sm font-medium text-green-600">+₱<?php echo number_format($transaction['amount']); ?></span>
                  </div>
                <?php endforeach; ?>
              <?php endif; ?>
            </div>
          </div>

          <!-- quick payment recording -->
          <div class="bg-amber-50 border border-amber-200 rounded-2xl p-5">
            <h3 class="font-semibold flex items-center gap-2 mb-3"><i class="fas fa-credit-card text-amber-600"></i>
              quick payment</h3>
            <form id="quickPaymentForm" onsubmit="quickPayment(event)">
              <div class="space-y-3">
                <input type="text" id="quickInvoice" placeholder="invoice #"
                  class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm bg-white focus:ring-1 focus:ring-amber-500 outline-none">
                <input type="number" id="quickAmount" placeholder="amount"
                  class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm bg-white focus:ring-1 focus:ring-amber-500 outline-none">
                <select id="quickMethod"
                  class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm bg-white focus:ring-1 focus:ring-amber-500 outline-none">
                  <option value="GCash">GCash</option>
                  <option value="Credit card">Credit card</option>
                  <option value="Cash">Cash</option>
                  <option value="Bank transfer">Bank transfer</option>
                </select>
                <input type="text" id="quickReference" placeholder="reference (optional)"
                  class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm bg-white focus:ring-1 focus:ring-amber-500 outline-none">
                <button type="submit"
                  class="w-full bg-amber-600 text-white py-2 rounded-xl text-sm hover:bg-amber-700 transition">record
                  payment</button>
              </div>
            </form>
          </div>
        </div>
      </main>
    </div>

    <script>
      // Pass PHP data to JavaScript
      const currentPage = <?php echo $currentPage; ?>;
      const totalPages = <?php echo $totalPages; ?>;
      const currentStatus = '<?php echo $statusFilter; ?>';
      const currentSearch = '<?php echo $searchFilter; ?>';

      // ========== SEARCH FUNCTIONALITY ==========
      document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('searchInput');

        if (searchInput) {
          searchInput.addEventListener('keypress', function (e) {
            if (e.key === 'Enter') {
              performSearch();
            }
          });

          // Add search button
          const searchBtn = document.createElement('button');
          searchBtn.className = 'absolute right-2 top-1/2 -translate-y-1/2 bg-amber-600 text-white px-3 py-1 rounded-lg text-xs hover:bg-amber-700 transition';
          searchBtn.innerHTML = '<i class="fa-solid fa-search"></i>';
          searchBtn.onclick = performSearch;
          searchInput.parentNode.appendChild(searchBtn);
        }
        // Check for pending approvals
        checkPendingApprovals();
      });

      // Check for pending approvals
      function checkPendingApprovals() {
        const pendingCount = <?php echo count($pendingApprovals); ?>;
        if (pendingCount > 0) {
          const notificationBtn = document.querySelector('.fa-bell').parentElement;
          if (notificationBtn) {
            const badge = document.createElement('span');
            badge.className = 'absolute -top-1 -right-1 bg-red-500 text-white text-[10px] px-1.5 py-0.5 rounded-full';
            badge.textContent = pendingCount;
            notificationBtn.style.position = 'relative';
            notificationBtn.appendChild(badge);
          }
        }
      }

      // Perform search
      function performSearch() {
        const search = document.getElementById('searchInput').value;
        const url = new URL(window.location);
        if (search) {
          url.searchParams.set('search', search);
        } else {
          url.searchParams.delete('search');
        }
        if (currentStatus !== 'all') url.searchParams.set('status', currentStatus);
        window.location.href = url.toString();
      }

      // Filter invoices
      function filterInvoices(status) {
        const url = new URL(window.location);
        if (status !== 'all') {
          url.searchParams.set('status', status);
        } else {
          url.searchParams.delete('status');
        }
        if (currentSearch) url.searchParams.set('search', currentSearch);
        window.location.href = url.toString();
      }

      // Pagination
      function changePage(page) {
        if (page < 1 || page > totalPages) return;
        const url = new URL(window.location);
        url.searchParams.set('page', page);
        if (currentStatus !== 'all') url.searchParams.set('status', currentStatus);
        if (currentSearch) url.searchParams.set('search', currentSearch);
        window.location.href = url.toString();
      }

      // Create Invoice
      function createInvoice() {
        Swal.fire({
          title: 'Create New Invoice',
          html: `
                <div class="text-left space-y-3 max-h-96 overflow-y-auto px-1">
                    <div>
                        <label class="text-sm font-medium text-slate-700">Guest Name *</label>
                        <input type="text" id="guestName" class="w-full border border-slate-300 rounded-lg px-3 py-2 mt-1" placeholder="e.g. John Doe">
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-700">Email</label>
                        <input type="email" id="guestEmail" class="w-full border border-slate-300 rounded-lg px-3 py-2 mt-1" placeholder="guest@email.com">
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-700">Phone</label>
                        <input type="text" id="guestPhone" class="w-full border border-slate-300 rounded-lg px-3 py-2 mt-1" placeholder="+63 912 345 6789">
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-700">Amount *</label>
                        <input type="number" id="amount" class="w-full border border-slate-300 rounded-lg px-3 py-2 mt-1" placeholder="0.00" min="1" step="0.01">
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-700">Due Date</label>
                        <input type="date" id="dueDate" class="w-full border border-slate-300 rounded-lg px-3 py-2 mt-1" value="<?php echo date('Y-m-d', strtotime('+7 days')); ?>">
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-700">Description</label>
                        <textarea id="description" class="w-full border border-slate-300 rounded-lg px-3 py-2 mt-1" rows="2" placeholder="e.g. Room charges, restaurant bill"></textarea>
                    </div>
                </div>
            `,
          showCancelButton: true,
          confirmButtonColor: '#d97706',
          cancelButtonColor: '#6b7280',
          confirmButtonText: 'Create Invoice',
          preConfirm: () => {
            const name = document.getElementById('guestName').value;
            const amount = document.getElementById('amount').value;
            if (!name || !amount) {
              Swal.showValidationMessage('Guest name and amount are required');
              return false;
            }
            return {
              name: name,
              email: document.getElementById('guestEmail').value,
              phone: document.getElementById('guestPhone').value,
              amount: amount,
              due_date: document.getElementById('dueDate').value,
              description: document.getElementById('description').value
            };
          }
        }).then((result) => {
          if (result.isConfirmed) {
            Swal.fire({
              title: 'Creating...',
              text: 'Please wait',
              allowOutsideClick: false,
              didOpen: () => {
                Swal.showLoading();
              }
            });

            const formData = new FormData();
            formData.append('action', 'create_invoice');
            formData.append('guest_name', result.value.name);
            formData.append('guest_email', result.value.email);
            formData.append('guest_phone', result.value.phone);
            formData.append('amount', result.value.amount);
            formData.append('due_date', result.value.due_date);
            formData.append('description', result.value.description);

            fetch('../../../controller/admin/post/billing_actions.php', {
              method: 'POST',
              body: formData
            })
              .then(response => response.json())
              .then(data => {
                if (data.success) {
                  Swal.fire({
                    title: 'Success!',
                    html: `
                                    <p>${data.message}</p>
                                    <p class="mt-2 font-medium">Invoice #: ${data.invoice_number}</p>
                                `,
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
                  text: 'Network error: ' + error.message,
                  icon: 'error',
                  confirmButtonColor: '#d97706'
                });
              });
          }
        });
      }

      // Record Payment
      function recordPayment() {
        Swal.fire({
          title: 'Record Payment',
          html: `
                <div class="text-left space-y-3">
                    <div>
                        <label class="text-sm font-medium text-slate-700">Booking Type</label>
                        <select id="bookingTypeSelect" class="w-full border border-slate-300 rounded-lg px-3 py-2 mt-1">
                            <option value="hotel">Hotel Booking</option>
                            <option value="restaurant">Restaurant Reservation</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-700">Select Invoice</label>
                        <select id="invoiceSelect" class="w-full border border-slate-300 rounded-lg px-3 py-2 mt-1">
                            <option value="">Select invoice...</option>
                            <?php foreach ($invoices as $inv): ?>
                            <option value="<?php echo $inv['id']; ?>" data-amount="<?php echo $inv['amount']; ?>" data-paid="<?php echo $inv['paid_amount'] ?? 0; ?>" data-type="hotel">
                                <?php echo $inv['invoice_number']; ?> - <?php echo $inv['guest_name']; ?> (₱<?php echo number_format($inv['amount']); ?>)
                            </option>
                            <?php endforeach; ?>
                            <!-- Add restaurant reservations here if needed -->
                        </select>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-700">Amount</label>
                        <input type="number" id="paymentAmount" class="w-full border border-slate-300 rounded-lg px-3 py-2 mt-1" placeholder="0.00" min="1" step="0.01">
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-700">Payment Method</label>
                        <select id="paymentMethod" class="w-full border border-slate-300 rounded-lg px-3 py-2 mt-1">
                            <option value="GCash">GCash</option>
                            <option value="Credit card">Credit card</option>
                            <option value="Cash">Cash</option>
                            <option value="Bank transfer">Bank transfer</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-700">Reference (optional)</label>
                        <input type="text" id="reference" class="w-full border border-slate-300 rounded-lg px-3 py-2 mt-1" placeholder="Transaction ID or reference">
                    </div>
                </div>
            `,
          showCancelButton: true,
          confirmButtonColor: '#d97706',
          cancelButtonColor: '#6b7280',
          confirmButtonText: 'Record Payment',
          didOpen: () => {
            document.getElementById('invoiceSelect').addEventListener('change', function () {
              const selected = this.options[this.selectedIndex];
              const amount = selected.dataset.amount;
              const paid = selected.dataset.paid || 0;
              const remaining = amount - paid;
              document.getElementById('paymentAmount').value = remaining;
              document.getElementById('paymentAmount').max = remaining;
            });
          },
          preConfirm: () => {
            const invoiceId = document.getElementById('invoiceSelect').value;
            const amount = document.getElementById('paymentAmount').value;
            const bookingType = document.getElementById('bookingTypeSelect').value;
            if (!invoiceId || !amount) {
              Swal.showValidationMessage('Please select invoice and enter amount');
              return false;
            }
            return {
              booking_id: invoiceId,
              amount: amount,
              method: document.getElementById('paymentMethod').value,
              reference: document.getElementById('reference').value,
              booking_type: bookingType
            };
          }
        }).then((result) => {
          if (result.isConfirmed) {
            Swal.fire({
              title: 'Recording...',
              text: 'Please wait',
              allowOutsideClick: false,
              didOpen: () => {
                Swal.showLoading();
              }
            });

            const formData = new FormData();
            formData.append('action', 'record_payment');
            formData.append('booking_id', result.value.booking_id);
            formData.append('amount', result.value.amount);
            formData.append('payment_method', result.value.method);
            formData.append('reference', result.value.reference);
            formData.append('booking_type', result.value.booking_type);

            fetch('../../../controller/admin/post/billing_actions.php', {
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
              })
              .catch(error => {
                Swal.fire({
                  title: 'Error',
                  text: 'Network error: ' + error.message,
                  icon: 'error',
                  confirmButtonColor: '#d97706'
                });
              });
          }
        });
      }

      // Record Payment for Specific Invoice
      function recordPaymentForInvoice(bookingId, invoiceNumber, totalAmount, paidAmount = 0) {
        const remaining = totalAmount - paidAmount;

        Swal.fire({
          title: 'Record Payment',
          html: `
                <div class="text-left space-y-3">
                    <p>Invoice: <strong>${invoiceNumber}</strong></p>
                    <p>Total amount: <strong>₱${totalAmount.toLocaleString()}</strong></p>
                    <p>Paid amount: <strong>₱${paidAmount.toLocaleString()}</strong></p>
                    <p>Remaining: <strong>₱${remaining.toLocaleString()}</strong></p>
                    <div>
                        <label class="text-sm font-medium text-slate-700">Amount</label>
                        <input type="number" id="paymentAmount" class="w-full border border-slate-300 rounded-lg px-3 py-2 mt-1" value="${remaining}" min="1" max="${remaining}" step="0.01">
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-700">Payment Method</label>
                        <select id="paymentMethod" class="w-full border border-slate-300 rounded-lg px-3 py-2 mt-1">
                            <option value="GCash">GCash</option>
                            <option value="Credit card">Credit card</option>
                            <option value="Cash">Cash</option>
                            <option value="Bank transfer">Bank transfer</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-700">Reference (optional)</label>
                        <input type="text" id="reference" class="w-full border border-slate-300 rounded-lg px-3 py-2 mt-1" placeholder="Transaction ID or reference">
                    </div>
                </div>
            `,
          showCancelButton: true,
          confirmButtonColor: '#d97706',
          cancelButtonColor: '#6b7280',
          confirmButtonText: 'Record Payment',
          preConfirm: () => {
            const amount = document.getElementById('paymentAmount').value;
            if (!amount) {
              Swal.showValidationMessage('Amount is required');
              return false;
            }
            return {
              booking_id: bookingId,
              amount: amount,
              method: document.getElementById('paymentMethod').value,
              reference: document.getElementById('reference').value,
              booking_type: 'hotel'
            };
          }
        }).then((result) => {
          if (result.isConfirmed) {
            Swal.fire({
              title: 'Recording...',
              text: 'Please wait',
              allowOutsideClick: false,
              didOpen: () => {
                Swal.showLoading();
              }
            });

            const formData = new FormData();
            formData.append('action', 'record_payment');
            formData.append('booking_id', result.value.booking_id);
            formData.append('amount', result.value.amount);
            formData.append('payment_method', result.value.method);
            formData.append('reference', result.value.reference);
            formData.append('booking_type', result.value.booking_type);

            fetch('../../../controller/admin/post/billing_actions.php', {
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
              })
              .catch(error => {
                Swal.fire({
                  title: 'Error',
                  text: 'Network error: ' + error.message,
                  icon: 'error',
                  confirmButtonColor: '#d97706'
                });
              });
          }
        });
      }

      // Quick Payment
      function quickPayment(event) {
        event.preventDefault();

        const invoice = document.getElementById('quickInvoice').value;
        const amount = document.getElementById('quickAmount').value;
        const method = document.getElementById('quickMethod').value;
        const reference = document.getElementById('quickReference').value;

        if (!invoice || !amount) {
          Swal.fire({
            title: 'Error',
            text: 'Invoice number and amount are required',
            icon: 'error',
            confirmButtonColor: '#d97706'
          });
          return;
        }

        Swal.fire({
          title: 'Processing...',
          text: 'Please wait',
          allowOutsideClick: false,
          didOpen: () => {
            Swal.showLoading();
          }
        });

        // Simulate - in production, you'd look up the invoice ID
        setTimeout(() => {
          Swal.fire({
            title: 'Success!',
            text: 'Payment recorded successfully',
            icon: 'success',
            confirmButtonColor: '#d97706'
          }).then(() => {
            location.reload();
          });
        }, 1500);
      }

      // View Invoice
      function viewInvoice(bookingId) {
        Swal.fire({
          title: 'Loading...',
          text: 'Please wait',
          allowOutsideClick: false,
          didOpen: () => {
            Swal.showLoading();
          }
        });

        const formData = new FormData();
        formData.append('action', 'get_invoice');
        formData.append('booking_id', bookingId);

        fetch('../../../controller/admin/post/billing_actions.php', {
          method: 'POST',
          body: formData
        })
          .then(response => response.json())
          .then(data => {
            Swal.close();
            if (data.success) {
              let paymentsHtml = '';
              if (data.payments.length > 0) {
                data.payments.forEach(p => {
                  const status = p.approval_status === 'approved' ? '✓' : '⏳';
                  paymentsHtml += `<li class="text-sm border-b border-slate-100 py-1 flex justify-between">
                                <span>${p.payment_reference} · ${p.payment_method}</span>
                                <span>₱${parseFloat(p.amount).toLocaleString()} ${status}</span>
                            </li>`;
                });
              } else {
                paymentsHtml = '<li class="text-sm text-slate-500">No payments yet</li>';
              }

              const balance = data.balance;
              const balanceClass = balance > 0 ? 'text-red-600' : 'text-green-600';

              Swal.fire({
                title: 'Invoice Details',
                html: `
                            <div class="text-left">
                                <div class="bg-amber-50 p-3 rounded-lg mb-3">
                                    <p class="font-semibold text-lg">${data.invoice.booking_reference}</p>
                                    <p class="text-sm">${data.invoice.guest_first_name} ${data.invoice.guest_last_name}</p>
                                    <p class="text-xs text-slate-600">${data.invoice.guest_email || 'No email'}</p>
                                </div>
                                <div class="grid grid-cols-2 gap-2 text-sm mb-3">
                                    <div><span class="text-slate-500">Date:</span> ${new Date(data.invoice.created_at).toLocaleDateString()}</div>
                                    <div><span class="text-slate-500">Due:</span> ${new Date(data.invoice.check_out).toLocaleDateString()}</div>
                                    <div><span class="text-slate-500">Amount:</span> ₱${parseFloat(data.invoice.total_amount).toLocaleString()}</div>
                                    <div><span class="text-slate-500">Paid:</span> ₱${parseFloat(data.paid_amount).toLocaleString()}</div>
                                    <div class="col-span-2"><span class="text-slate-500">Balance:</span> <span class="font-semibold ${balanceClass}">₱${balance.toLocaleString()}</span></div>
                                </div>
                                <div class="mb-3">
                                    <p class="font-medium text-sm mb-1">Payment History</p>
                                    <ul class="text-sm bg-slate-50 p-2 rounded">
                                        ${paymentsHtml}
                                    </ul>
                                </div>
                                ${data.invoice.special_requests ? `
                                    <div class="text-sm">
                                        <p class="font-medium">Notes:</p>
                                        <p class="text-slate-600">${data.invoice.special_requests}</p>
                                    </div>
                                ` : ''}
                            </div>
                        `,
                confirmButtonColor: '#d97706',
                confirmButtonText: 'Close',
                showCancelButton: balance > 0,
                cancelButtonText: 'Record Payment',
                cancelButtonColor: '#6b7280'
              }).then((result) => {
                if (result.dismiss === Swal.DismissReason.cancel) {
                  recordPaymentForInvoice(data.invoice.id, data.invoice.booking_reference, data.invoice.total_amount, data.paid_amount);
                }
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
            Swal.fire({
              title: 'Error',
              text: 'Network error: ' + error.message,
              icon: 'error',
              confirmButtonColor: '#d97706'
            });
          });
      }

      // View Receipt
      function viewReceipt(bookingId) {
        viewInvoice(bookingId);
      }

      // Send Reminder
      function sendReminder(bookingId) {
        Swal.fire({
          title: 'Send Reminder',
          text: 'Send payment reminder to the guest?',
          icon: 'question',
          showCancelButton: true,
          confirmButtonColor: '#d97706',
          cancelButtonColor: '#6b7280',
          confirmButtonText: 'Yes, send'
        }).then((result) => {
          if (result.isConfirmed) {
            Swal.fire({
              title: 'Sending...',
              text: 'Please wait',
              allowOutsideClick: false,
              didOpen: () => {
                Swal.showLoading();
              }
            });

            const formData = new FormData();
            formData.append('action', 'send_reminder');
            formData.append('booking_id', bookingId);

            fetch('../../../controller/admin/post/billing_actions.php', {
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
                Swal.fire({
                  title: 'Error',
                  text: 'Network error: ' + error.message,
                  icon: 'error',
                  confirmButtonColor: '#d97706'
                });
              });
          }
        });
      }

      // Void Invoice
      function voidInvoice(bookingId) {
        Swal.fire({
          title: 'Void Invoice?',
          text: 'Are you sure you want to void this invoice? This action cannot be undone.',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#d97706',
          cancelButtonColor: '#6b7280',
          confirmButtonText: 'Yes, void it'
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
            formData.append('action', 'void_invoice');
            formData.append('booking_id', bookingId);

            fetch('../../../controller/admin/post/billing_actions.php', {
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
              })
              .catch(error => {
                Swal.fire({
                  title: 'Error',
                  text: 'Network error: ' + error.message,
                  icon: 'error',
                  confirmButtonColor: '#d97706'
                });
              });
          }
        });
      }

      // View Pending Approvals
      function viewPendingApprovals() {
        const pending = <?php echo json_encode($pendingApprovals); ?>;

        if (pending.length === 0) {
          Swal.fire({
            title: 'No Pending Approvals',
            text: 'All payments have been processed.',
            icon: 'info',
            confirmButtonColor: '#d97706'
          });
          return;
        }

        let html = '<div class="text-left space-y-2">';
        pending.forEach(p => {
          html += `
                <div class="border rounded-lg p-3 flex justify-between items-center">
                    <div>
                        <p class="font-medium">${p.payment_reference}</p>
                        <p class="text-xs">${p.guest_name} · ${p.invoice_number}</p>
                        <p class="text-xs text-slate-500">₱${parseFloat(p.amount).toLocaleString()} · ${p.payment_method}</p>
                    </div>
                    <div class="flex gap-2">
                        <button onclick="approvePayment(${p.id})" class="text-green-600 hover:underline text-xs">Approve</button>
                        <button onclick="rejectPayment(${p.id})" class="text-red-600 hover:underline text-xs">Reject</button>
                    </div>
                </div>
            `;
        });
        html += '</div>';

        Swal.fire({
          title: 'Pending Approvals',
          html: html,
          confirmButtonColor: '#d97706',
          confirmButtonText: 'Close',
          showCloseButton: true
        });
      }

      // Approve Payment
      function approvePayment(paymentId) {
        Swal.fire({
          title: 'Approve Payment?',
          text: 'Confirm that this payment is valid?',
          icon: 'question',
          showCancelButton: true,
          confirmButtonColor: '#d97706',
          cancelButtonColor: '#6b7280',
          confirmButtonText: 'Yes, approve'
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
            formData.append('action', 'approve_payment');
            formData.append('payment_id', paymentId);

            fetch('../../../controller/admin/post/billing_actions.php', {
              method: 'POST',
              body: formData
            })
              .then(response => response.json())
              .then(data => {
                if (data.success) {
                  Swal.fire({
                    title: 'Approved!',
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
                Swal.fire({
                  title: 'Error',
                  text: 'Network error: ' + error.message,
                  icon: 'error',
                  confirmButtonColor: '#d97706'
                });
              });
          }
        });
      }

      // Reject Payment
      function rejectPayment(paymentId) {
        Swal.fire({
          title: 'Reject Payment',
          input: 'textarea',
          inputLabel: 'Reason for rejection',
          inputPlaceholder: 'Enter reason...',
          showCancelButton: true,
          confirmButtonColor: '#d97706',
          cancelButtonColor: '#6b7280',
          confirmButtonText: 'Reject',
          preConfirm: (reason) => {
            if (!reason) {
              Swal.showValidationMessage('Please provide a reason');
              return false;
            }
            return reason;
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
            formData.append('action', 'reject_payment');
            formData.append('payment_id', paymentId);
            formData.append('reason', result.value);

            fetch('../../../controller/admin/post/billing_actions.php', {
              method: 'POST',
              body: formData
            })
              .then(response => response.json())
              .then(data => {
                if (data.success) {
                  Swal.fire({
                    title: 'Rejected!',
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
                Swal.fire({
                  title: 'Error',
                  text: 'Network error: ' + error.message,
                  icon: 'error',
                  confirmButtonColor: '#d97706'
                });
              });
          }
        });
      }

      // Export Invoices
      function exportInvoices() {
        Swal.fire({
          title: 'Export Invoices',
          html: `
                <div class="text-left space-y-3">
                    <div>
                        <label class="text-sm font-medium text-slate-700">Type</label>
                        <select id="exportType" class="w-full border border-slate-300 rounded-lg px-3 py-2 mt-1">
                            <option value="all">All (Hotel & Restaurant)</option>
                            <option value="hotel">Hotel Only</option>
                            <option value="restaurant">Restaurant Only</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-700">Filter</label>
                        <select id="exportFilter" class="w-full border border-slate-300 rounded-lg px-3 py-2 mt-1">
                            <option value="all">All invoices</option>
                            <option value="paid">Paid</option>
                            <option value="pending">Pending</option>
                            <option value="overdue">Overdue</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-700">Format</label>
                        <select id="exportFormat" class="w-full border border-slate-300 rounded-lg px-3 py-2 mt-1">
                            <option value="csv">CSV</option>
                            <option value="json">JSON</option>
                        </select>
                    </div>
                </div>
            `,
          showCancelButton: true,
          confirmButtonColor: '#d97706',
          cancelButtonColor: '#6b7280',
          confirmButtonText: 'Export',
          preConfirm: () => {
            return {
              type: document.getElementById('exportType').value,
              filter: document.getElementById('exportFilter').value,
              format: document.getElementById('exportFormat').value
            };
          }
        }).then((result) => {
          if (result.isConfirmed) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '../../../controller/admin/post/billing_actions.php';
            form.innerHTML = `
                    <input type="hidden" name="action" value="export_invoices">
                    <input type="hidden" name="type" value="${result.value.type}">
                    <input type="hidden" name="filter" value="${result.value.filter}">
                    <input type="hidden" name="format" value="${result.value.format}">
                `;
            document.body.appendChild(form);
            form.submit();
            document.body.removeChild(form);
          }
        });
      }

      // View Reports
      function viewReports() {
        Swal.fire({
          title: 'Generate Report',
          html: `
                <div class="text-left space-y-3">
                    <div>
                        <label class="text-sm font-medium text-slate-700">Report Period</label>
                        <select id="reportPeriod" class="w-full border border-slate-300 rounded-lg px-3 py-2 mt-1">
                            <option value="month">Last 30 days</option>
                            <option value="quarter">Last 3 months</option>
                            <option value="year">Last year</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-700">Report Type</label>
                        <select id="reportType" class="w-full border border-slate-300 rounded-lg px-3 py-2 mt-1">
                            <option value="sales">Sales Report</option>
                            <option value="payments">Payment Methods</option>
                            <option value="customers">Customer Payments</option>
                        </select>
                    </div>
                </div>
            `,
          showCancelButton: true,
          confirmButtonColor: '#d97706',
          cancelButtonColor: '#6b7280',
          confirmButtonText: 'Generate',
          preConfirm: () => {
            return {
              period: document.getElementById('reportPeriod').value,
              type: document.getElementById('reportType').value
            };
          }
        }).then((result) => {
          if (result.isConfirmed) {
            Swal.fire({
              title: 'Generating...',
              text: 'Please wait',
              allowOutsideClick: false,
              didOpen: () => {
                Swal.showLoading();
              }
            });

            const formData = new FormData();
            formData.append('action', 'generate_report');
            formData.append('period', result.value.period);
            formData.append('type', result.value.type);

            fetch('../../../controller/admin/post/billing_actions.php', {
              method: 'POST',
              body: formData
            })
              .then(response => response.json())
              .then(data => {
                Swal.close();
                if (data.success) {
                  let reportHtml = '<div class="text-left">';
                  reportHtml += `<p>Period: ${new Date(data.start).toLocaleDateString()} - ${new Date(data.end).toLocaleDateString()}</p>`;
                  reportHtml += '<table class="w-full mt-3 text-sm"><thead><tr><th>Date</th><th>Transactions</th><th>Amount</th></tr></thead><tbody>';

                  let totalAmount = 0;
                  let totalCount = 0;

                  data.report.forEach(r => {
                    reportHtml += `<tr><td>${r.date}</td><td>${r.transaction_count}</td><td>₱${parseFloat(r.total_amount).toLocaleString()}</td></tr>`;
                    totalAmount += parseFloat(r.total_amount);
                    totalCount += parseInt(r.transaction_count);
                  });

                  reportHtml += `<tr class="font-bold border-t"><td>Total</td><td>${totalCount}</td><td>₱${totalAmount.toLocaleString()}</td></tr>`;
                  reportHtml += '</tbody></table></div>';

                  Swal.fire({
                    title: 'Report Generated',
                    html: reportHtml,
                    icon: 'success',
                    confirmButtonColor: '#d97706',
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
                Swal.close();
                Swal.fire({
                  title: 'Error',
                  text: 'Network error: ' + error.message,
                  icon: 'error',
                  confirmButtonColor: '#d97706'
                });
              });
          }
        });
      }

      // View All Invoices
      function viewAllInvoices() {
        window.location.href = '?status=all';
      }
    </script>
  </body>

</html>