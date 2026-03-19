<?php
/**
 * View - Admin Table Reservation
 */
require_once '../../../controller/admin/get/restaurant/table_reservation.php';

// Set current page for navigation
$current_page = 'table_reservation';
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin · Table Reservation</title>
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

    .status-badge {
      cursor: pointer;
      transition: all 0.2s;
    }

    .status-badge:hover {
      opacity: 0.8;
      transform: scale(1.05);
    }

    .pagination-btn.active {
      background-color: #d97706;
      color: white;
      border-color: #d97706;
    }

    .pagination-btn:disabled {
      opacity: 0.4;
      cursor: not-allowed;
    }

    .action-btn:disabled {
      opacity: 0.4;
      cursor: not-allowed;
      pointer-events: none;
    }

    .table-tile {
      transition: all 0.2s;
      cursor: pointer;
    }

    .table-tile:hover {
      transform: scale(1.05);
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .table-tile.selected {
      ring: 2px solid #d97706;
      border-color: #d97706;
    }

    .warning-icon {
      color: #f59e0b;
      cursor: help;
    }
    
    .points-badge {
      background: linear-gradient(135deg, #f59e0b, #d97706);
      color: white;
    }
  </style>
</head>

<body class="bg-white font-sans antialiased">

  <!-- Toast Notification -->
  <div id="toast" class="toast bg-white rounded-xl p-4 min-w-[300px] shadow-lg border border-amber-200 hidden">
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

  <!-- APP CONTAINER -->
  <div class="min-h-screen flex flex-col lg:flex-row">

    <!-- ========== SIDEBAR ========== -->
    <?php require_once '../components/admin_nav.php'; ?>

    <!-- ========== MAIN CONTENT ========== -->
    <main class="flex-1 p-5 lg:p-8 overflow-y-auto bg-white">

      <!-- header -->
      <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <div>
          <h1 class="text-2xl lg:text-3xl font-light text-slate-800">Table Reservation</h1>
          <p class="text-sm text-slate-500 mt-0.5">manage restaurant table bookings, walk-ins, and guest preferences
          </p>
        </div>
        <div class="flex gap-3 text-sm">
          <span class="bg-white border rounded-full px-4 py-2 flex items-center gap-2 shadow-sm">
            <i class="fas fa-calendar text-slate-400"></i> <?php echo date('F j, Y', strtotime($today)); ?>
          </span>
          <span class="bg-white border rounded-full px-4 py-2 shadow-sm cursor-pointer hover:bg-slate-50 relative"
            id="notificationBell">
            <i class="fas fa-bell"></i>
            <?php if ($unread_count > 0): ?>
              <span
                class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-4 h-4 flex items-center justify-center"><?php echo $unread_count; ?></span>
            <?php endif; ?>
          </span>
        </div>
      </div>

      <!-- STATS CARDS - Updated with Balance Card -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-4 mb-8">
        <div
          class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm cursor-pointer hover:shadow-md transition"
          onclick="filterByDate('today')">
          <p class="text-xs text-slate-500">Today's reservations</p>
          <p class="text-2xl font-semibold" id="todayReservations"><?php echo $stats['today_reservations']; ?></p>
        </div>
        <div
          class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm cursor-pointer hover:shadow-md transition"
          onclick="filterByStatus('upcoming')">
          <p class="text-xs text-slate-500">Upcoming</p>
          <p class="text-2xl font-semibold text-blue-600" id="upcomingReservations">
            <?php echo $stats['upcoming_reservations']; ?>
          </p>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
          <p class="text-xs text-slate-500">Total reservations</p>
          <p class="text-2xl font-semibold" id="totalReservations"><?php echo $stats['total_reservations']; ?></p>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
          <p class="text-xs text-slate-500">Total guests</p>
          <p class="text-2xl font-semibold" id="totalGuests"><?php echo $stats['total_guests']; ?></p>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
          <p class="text-xs text-slate-500">Available tables</p>
          <p class="text-2xl font-semibold text-green-600" id="availableTables">
            <?php echo $stats['available_tables']; ?>
          </p>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm cursor-pointer hover:shadow-md transition"
             onclick="showUnpaidBalanceDetails()">
          <p class="text-xs text-slate-500">Unpaid Balance</p>
          <p class="text-2xl font-semibold text-amber-600" id="totalUnpaidBalance">
            ₱<?php echo number_format($stats['total_unpaid_balance'], 2); ?>
          </p>
          <p class="text-xs text-slate-500 mt-1"><?php echo $stats['guests_with_balance']; ?> guests</p>
        </div>
      </div>

      <!-- FILTER AND SEARCH BAR - Updated with date filter -->
      <div
        class="bg-white rounded-2xl border border-slate-200 p-4 mb-6 flex flex-wrap gap-3 items-center justify-between">
        <div class="flex flex-wrap gap-2 items-center">
          <!-- Status Filter Buttons -->
          <div class="flex gap-2 flex-wrap" id="filterButtonGroup">
            <button
              class="filter-btn <?php echo $statusFilter == 'all' ? 'bg-amber-600 text-white' : 'border border-slate-200 hover:bg-slate-50'; ?> px-4 py-2 rounded-xl text-sm"
              data-filter="all">all</button>
            <button
              class="filter-btn <?php echo $statusFilter == 'pending' ? 'bg-amber-600 text-white' : 'border border-slate-200 hover:bg-slate-50'; ?> px-4 py-2 rounded-xl text-sm"
              data-filter="pending">pending</button>
            <button
              class="filter-btn <?php echo $statusFilter == 'confirmed' ? 'bg-amber-600 text-white' : 'border border-slate-200 hover:bg-slate-50'; ?> px-4 py-2 rounded-xl text-sm"
              data-filter="confirmed">confirmed</button>
            <button
              class="filter-btn <?php echo $statusFilter == 'seated' ? 'bg-amber-600 text-white' : 'border border-slate-200 hover:bg-slate-50'; ?> px-4 py-2 rounded-xl text-sm"
              data-filter="seated">seated</button>
            <button
              class="filter-btn <?php echo $statusFilter == 'completed' ? 'bg-amber-600 text-white' : 'border border-slate-200 hover:bg-slate-50'; ?> px-4 py-2 rounded-xl text-sm"
              data-filter="completed">completed</button>
            <button
              class="filter-btn <?php echo $statusFilter == 'cancelled' ? 'bg-amber-600 text-white' : 'border border-slate-200 hover:bg-slate-50'; ?> px-4 py-2 rounded-xl text-sm"
              data-filter="cancelled">cancelled</button>
            <button
              class="filter-btn <?php echo $statusFilter == 'no-show' ? 'bg-amber-600 text-white' : 'border border-slate-200 hover:bg-slate-50'; ?> px-4 py-2 rounded-xl text-sm"
              data-filter="no-show">no-show</button>
          </div>

          <!-- Date Filter Dropdown -->
          <select id="dateFilter"
            class="border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-1 focus:ring-amber-500 outline-none">
            <option value="all" <?php echo $dateFilter == 'all' ? 'selected' : ''; ?>>All Dates</option>
            <option value="today" <?php echo $dateFilter == 'today' ? 'selected' : ''; ?>>Today</option>
            <option value="tomorrow" <?php echo $dateFilter == 'tomorrow' ? 'selected' : ''; ?>>Tomorrow</option>
            <option value="this_week" <?php echo $dateFilter == 'this_week' ? 'selected' : ''; ?>>This Week</option>
            <option value="this_month" <?php echo $dateFilter == 'this_month' ? 'selected' : ''; ?>>This Month</option>
            <option value="past" <?php echo $dateFilter == 'past' ? 'selected' : ''; ?>>Past Reservations</option>
            <?php foreach ($availableDates as $date): ?>
              <option value="<?php echo $date['reservation_date']; ?>" <?php echo $dateFilter == $date['reservation_date'] ? 'selected' : ''; ?>>
                <?php echo date('M d, Y', strtotime($date['reservation_date'])); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="flex gap-2">
          <div class="relative">
            <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
            <input type="text" id="searchInput" placeholder="search guest..."
              value="<?php echo htmlspecialchars($searchFilter); ?>"
              class="border border-slate-200 rounded-xl pl-9 pr-4 py-2 text-sm w-48 focus:ring-1 focus:ring-amber-500 outline-none">
          </div>
          <button class="border border-amber-600 text-amber-700 px-3 py-2 rounded-xl text-sm hover:bg-amber-50"
            id="addWalkinBtn">
            <i class="fas fa-plus mr-1"></i>walk-in
          </button>
        </div>
      </div>

      <!-- RESERVATIONS TABLE -->
      <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden mb-8">
        <div class="p-4 border-b border-slate-200 bg-slate-50 flex items-center justify-between">
          <h2 class="font-semibold flex items-center gap-2">
            <i class="fas fa-clock text-amber-600"></i> reservations
          </h2>
          <div class="flex gap-2">
            <button id="exportBtn"
              class="text-sm text-amber-700 border border-amber-600 px-3 py-1 rounded-lg hover:bg-amber-50">export</button>
          </div>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full text-sm" id="reservationTable">
            <thead class="text-slate-500 text-xs border-b">
              <tr>
                <td class="p-3">Date</td>
                <td class="p-3">Time</td>
                <td class="p-3">Guest</td>
                <td class="p-3">Table</td>
                <td class="p-3">Pax</td>
                <td class="p-3">Status</td>
                <td class="p-3">Payment</td>
                <td class="p-3">Points</td>
                <td class="p-3">Special requests</td>
                <td class="p-3">Actions</td>
              </tr>
            </thead>
            <tbody class="divide-y" id="tableBody">
              <?php if (empty($reservations)): ?>
                <tr>
                  <td colspan="10" class="p-4 text-center text-slate-500">No reservations found</td>
                </tr>
              <?php else: ?>
                <?php foreach ($reservations as $res):
                  $statusColors = [
                    'pending' => 'bg-yellow-100 text-yellow-700',
                    'confirmed' => 'bg-green-100 text-green-700',
                    'seated' => 'bg-blue-100 text-blue-700',
                    'cancelled' => 'bg-red-100 text-red-700',
                    'completed' => 'bg-purple-100 text-purple-700',
                    'no-show' => 'bg-gray-100 text-gray-700'
                  ];
                  $paymentColors = [
                    'unpaid' => 'bg-red-100 text-red-700',
                    'paid' => 'bg-green-100 text-green-700',
                    'refunded' => 'bg-gray-100 text-gray-700'
                  ];
                  $statusColor = $statusColors[$res['status']] ?? 'bg-gray-100 text-gray-700';
                  $paymentColor = $paymentColors[$res['payment_status']] ?? 'bg-gray-100 text-gray-700';
                  ?>
                  <tr data-id="<?php echo $res['id']; ?>" 
                     data-status="<?php echo $res['status']; ?>"
                     data-guest="<?php echo htmlspecialchars($res['guest_name']); ?>"
                     data-user-id="<?php echo $res['user_id']; ?>"
                     data-payment-status="<?php echo $res['payment_status']; ?>"
                     data-points="<?php echo $res['points_earned']; ?>"
                     data-points-awarded="<?php echo $res['points_awarded']; ?>">
                    
                    <td class="p-3">
                      <?php
                      $dateClass = '';
                      if ($res['reservation_date'] == date('Y-m-d')) {
                        $dateClass = 'text-amber-600 font-medium';
                      } elseif ($res['reservation_date'] < date('Y-m-d')) {
                        $dateClass = 'text-slate-400';
                      }
                      ?>
                      <span class="<?php echo $dateClass; ?>"><?php echo date('M d, Y', strtotime($res['reservation_date'])); ?></span>
                    </td>
                    <td class="p-3">
                      <?php echo $res['formatted_time'] ?? date('h:i A', strtotime($res['reservation_time'])); ?>
                    </td>
                    <td class="p-3 font-medium">
                      <div class="flex items-center gap-1">
                        <?php echo htmlspecialchars($res['guest_name']); ?>
                        <?php if ($res['has_outstanding_balance']): ?>
                          <i class="fas fa-circle-exclamation text-amber-500 cursor-help" 
                             title="Guest has outstanding balance of ₱<?php echo number_format($res['current_balance'], 2); ?>"></i>
                        <?php endif; ?>
                        <?php if ($res['member_tier'] && $res['member_tier'] !== 'bronze'): ?>
                          <i class="fas fa-gem text-amber-500" title="<?php echo ucfirst($res['member_tier']); ?> member"></i>
                        <?php endif; ?>
                      </div>
                    </td>
                    <td class="p-3"><?php echo $res['table_number'] ?: 'TBD'; ?></td>
                    <td class="p-3"><?php echo $res['guests']; ?></td>
                    <td class="p-3">
                      <span class="status-badge <?php echo $statusColor; ?> px-2 py-0.5 rounded-full text-xs">
                        <?php echo ucfirst($res['status']); ?>
                      </span>
                    </td>
                    <td class="p-3">
                      <span class="<?php echo $paymentColor; ?> px-2 py-0.5 rounded-full text-xs">
                        <?php echo ucfirst($res['payment_status']); ?>
                      </span>
                      <?php if ($res['down_payment'] > 0): ?>
                        <span class="text-xs text-amber-600 block mt-1">₱<?php echo number_format($res['down_payment']); ?></span>
                      <?php endif; ?>
                    </td>
                    <td class="p-3">
                      <?php if ($res['points_earned'] > 0): ?>
                        <span class="text-xs font-medium <?php echo $res['points_awarded'] ? 'text-green-600' : 'text-amber-600'; ?>">
                          +<?php echo $res['points_earned']; ?>
                          <?php if ($res['points_awarded']): ?>
                            <i class="fas fa-circle-check text-green-500" title="Points awarded"></i>
                          <?php else: ?>
                            <i class="fas fa-star text-amber-500" title="Points to earn"></i>
                          <?php endif; ?>
                        </span>
                      <?php else: ?>
                        <span class="text-xs text-slate-400">—</span>
                      <?php endif; ?>
                    </td>
                    <td class="p-3 max-w-[150px] truncate">
                      <?php echo htmlspecialchars($res['special_requests'] ?: '—'); ?>
                    </td>
                    <td class="p-3">
                      <div class="flex gap-2 action-container">
                        <?php if ($res['status'] === 'pending' || $res['status'] === 'confirmed'): ?>
                          <button class="action-btn seat-btn text-xs text-blue-600 hover:underline" title="Seat guest">
                            <i class="fas fa-chair"></i>
                          </button>
                        <?php endif; ?>
                        
                        <?php if (!in_array($res['status'], ['cancelled', 'completed', 'no-show'])): ?>
                          <button class="action-btn cancel-btn text-xs text-red-600 hover:underline" title="Cancel">
                            <i class="fas fa-ban"></i>
                          </button>
                        <?php endif; ?>
                        
                        <?php if ($res['status'] === 'pending'): ?>
                          <button class="action-btn confirm-btn text-xs text-green-600 hover:underline" title="Confirm">
                            <i class="fas fa-check-circle"></i>
                          </button>
                        <?php endif; ?>
                        
                        <?php if ($res['status'] === 'seated'): ?>
                          <button class="action-btn order-btn text-xs text-purple-600 hover:underline" title="Take order">
                            <i class="fas fa-receipt"></i>
                          </button>
                          <button class="action-btn check-btn text-xs text-emerald-600 hover:underline"
                            title="Process check">
                            <i class="fas fa-credit-card"></i>
                          </button>
                        <?php endif; ?>
                        
                        <?php if ($res['payment_status'] === 'paid' && !$res['points_awarded'] && $res['user_id']): ?>
                          <button class="action-btn points-btn text-xs text-amber-600 hover:underline" 
                                  title="Award <?php echo $res['points_earned']; ?> points"
                                  onclick="awardPoints(<?php echo $res['id']; ?>, <?php echo $res['points_earned']; ?>)">
                            <i class="fas fa-star"></i>
                          </button>
                        <?php endif; ?>
                        
                        <button class="action-btn details-btn text-xs text-slate-600 hover:underline"
                          title="View details">
                          <i class="fas fa-eye"></i>
                        </button>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>

        <!-- Pagination Controls -->
        <div class="p-4 border-t border-slate-200 flex items-center justify-between">
          <span class="text-xs text-slate-500" id="resultCount">
            Showing <?php echo (($currentPage - 1) * $limit + 1); ?>-<?php echo min($currentPage * $limit, $totalReservations); ?>
            of <?php echo $totalReservations; ?> reservations
          </span>
          <div class="flex gap-2" id="paginationControls">
            <button
              class="pagination-btn border border-slate-200 px-3 py-1 rounded-lg text-sm <?php echo $currentPage <= 1 ? 'opacity-50 cursor-not-allowed' : ''; ?>"
              id="prevPage" <?php echo $currentPage <= 1 ? 'disabled' : ''; ?>>
              Previous
            </button>

            <?php for ($i = 1; $i <= min(5, $totalPages); $i++): ?>
              <button
                class="pagination-btn <?php echo $i == $currentPage ? 'bg-amber-600 text-white' : 'border border-slate-200'; ?> px-3 py-1 rounded-lg text-sm page-btn"
                data-page="<?php echo $i; ?>">
                <?php echo $i; ?>
              </button>
            <?php endfor; ?>

            <?php if ($totalPages > 5): ?>
              <span class="px-2">...</span>
              <button class="pagination-btn border border-slate-200 px-3 py-1 rounded-lg text-sm page-btn"
                data-page="<?php echo $totalPages; ?>">
                <?php echo $totalPages; ?>
              </button>
            <?php endif; ?>

            <button
              class="pagination-btn border border-slate-200 px-3 py-1 rounded-lg text-sm <?php echo $currentPage >= $totalPages ? 'opacity-50 cursor-not-allowed' : ''; ?>"
              id="nextPage" <?php echo $currentPage >= $totalPages ? 'disabled' : ''; ?>>
              Next
            </button>
          </div>
        </div>
      </div>

      <!-- BOTTOM: TABLE AVAILABILITY & WAITLIST -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- table availability grid -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 p-5">
          <h2 class="font-semibold text-lg flex items-center gap-2 mb-3">
            <i class="fas fa-table text-amber-600"></i> table availability
          </h2>
          <div class="grid grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3" id="tableGrid">
            <?php foreach ($tables as $table):
              $statusColors = [
                'available' => 'bg-green-50 border-green-200 text-green-700',
                'occupied' => 'bg-red-50 border-red-200 text-red-700',
                'reserved' => 'bg-amber-50 border-amber-200 text-amber-700'
              ];
              $colorClass = $statusColors[$table['status']] ?? 'bg-gray-50 border-gray-200 text-gray-700';
              ?>
              <div class="table-tile border rounded-lg p-2 text-center <?php echo $colorClass; ?>"
                data-table="<?php echo $table['table_number']; ?>" 
                data-status="<?php echo $table['status']; ?>"
                data-capacity="<?php echo $table['capacity']; ?>"
                onclick="selectTable('<?php echo $table['table_number']; ?>')">
                <span class="text-sm font-medium"><?php echo $table['table_number']; ?></span>
                <span class="text-xs block"><?php echo $table['capacity']; ?> pax</span>
                <span class="text-xs status-text"><?php echo ucfirst($table['status']); ?></span>
              </div>
            <?php endforeach; ?>
          </div>
          <div class="flex gap-4 mt-3 text-xs text-slate-500">
            <span><span class="inline-block w-3 h-3 bg-green-50 border border-green-200 rounded mr-1"></span>
              available</span>
            <span><span class="inline-block w-3 h-3 bg-red-50 border border-red-200 rounded mr-1"></span>
              occupied</span>
            <span><span class="inline-block w-3 h-3 bg-amber-50 border border-amber-200 rounded mr-1"></span>
              reserved</span>
          </div>
        </div>

        <!-- waitlist -->
        <div class="bg-white rounded-2xl border border-slate-200 p-5">
          <h3 class="font-semibold flex items-center gap-2 mb-3">
            <i class="fas fa-list-timeline text-amber-600"></i> waitlist (<span
              id="waitlistCount"><?php echo count($waitingList); ?></span>)
          </h3>
          <div class="space-y-2 max-h-60 overflow-y-auto" id="waitlistContainer">
            <?php if (empty($waitingList)): ?>
              <p class="text-sm text-slate-500 text-center py-2">No guests waiting</p>
            <?php else: ?>
              <?php foreach ($waitingList as $wait): ?>
                <div class="flex justify-between items-center p-2 bg-slate-50 rounded hover:bg-slate-100 transition"
                  data-waitlist-id="<?php echo $wait['id']; ?>">
                  <div>
                    <p class="font-medium text-sm"><?php echo htmlspecialchars($wait['guest_name']); ?></p>
                    <p class="text-xs text-slate-500">
                      <?php echo $wait['party_size']; ?> guests · 
                      waiting <?php echo floor((time() - strtotime($wait['wait_started_at'])) / 60); ?> min
                      <?php if ($wait['requested_time']): ?>
                        · requested <?php echo date('h:i A', strtotime($wait['requested_time'])); ?>
                      <?php endif; ?>
                    </p>
                  </div>
                  <div class="flex gap-1">
                    <button class="text-xs text-green-600 hover:underline seat-waitlist-btn" title="Seat"
                      onclick="seatFromWaitlist(<?php echo $wait['id']; ?>)">
                      <i class="fas fa-chair"></i>
                    </button>
                    <button class="text-xs text-red-600 hover:underline remove-waitlist-btn" title="Remove"
                      onclick="removeFromWaitlist(<?php echo $wait['id']; ?>)">
                      <i class="fas fa-trash-can"></i>
                    </button>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
          <button class="w-full border border-amber-600 text-amber-700 rounded-xl py-2 text-sm mt-4 hover:bg-amber-50"
            id="addToWaitlistBtn">
            + add to waitlist
          </button>
        </div>
      </div>
    </main>
  </div>

  <!-- Walk-in Modal -->
  <div id="walkinModal" class="hidden fixed inset-0 bg-black bg-opacity-50 items-center justify-center z-50">
    <div class="bg-white rounded-2xl p-6 w-full max-w-md modal-enter">
      <div class="flex justify-between items-center mb-4">
        <h3 class="text-xl font-semibold">Walk-in Reservation</h3>
        <button class="text-slate-400 hover:text-slate-600" id="closeWalkinModal">&times;</button>
      </div>

      <div class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Guest Name *</label>
          <input type="text" id="walkinName" class="w-full border rounded-xl p-2" placeholder="Full name">
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Phone Number</label>
          <input type="text" id="walkinPhone" class="w-full border rounded-xl p-2" placeholder="Contact number">
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Email (optional)</label>
          <input type="email" id="walkinEmail" class="w-full border rounded-xl p-2" placeholder="Email address">
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Party Size *</label>
            <input type="number" id="walkinGuests" class="w-full border rounded-xl p-2" min="1" value="2">
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Time *</label>
            <input type="time" id="walkinTime" class="w-full border rounded-xl p-2" value="19:00">
          </div>
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Table (optional)</label>
          <select id="walkinTable" class="w-full border rounded-xl p-2">
            <option value="">Auto-assign</option>
            <?php foreach ($tables as $table): ?>
              <?php if ($table['status'] === 'available'): ?>
                <option value="<?php echo $table['table_number']; ?>" data-capacity="<?php echo $table['capacity']; ?>">
                  <?php echo $table['table_number']; ?> (<?php echo $table['capacity']; ?> pax)
                </option>
              <?php endif; ?>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Special Requests</label>
          <textarea id="walkinRequests" rows="2" class="w-full border rounded-xl p-2"
            placeholder="Any special requests..."></textarea>
        </div>
        
        <div class="bg-amber-50 p-3 rounded-lg">
          <p class="text-xs text-amber-700 flex items-center gap-1">
            <i class="fas fa-circle-info"></i>
            Down payment: ₱500 per guest · 5 points per ₱100
          </p>
        </div>

        <div class="flex gap-3 pt-2">
          <button class="flex-1 bg-amber-600 text-white py-2 rounded-xl hover:bg-amber-700" id="saveWalkinBtn">Create
            Reservation</button>
          <button class="flex-1 border border-slate-200 py-2 rounded-xl hover:bg-slate-50"
            id="cancelWalkinBtn">Cancel</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Waitlist Modal -->
  <div id="waitlistModal" class="hidden fixed inset-0 bg-black bg-opacity-50 items-center justify-center z-50">
    <div class="bg-white rounded-2xl p-6 w-full max-w-md modal-enter">
      <div class="flex justify-between items-center mb-4">
        <h3 class="text-xl font-semibold">Add to Waitlist</h3>
        <button class="text-slate-400 hover:text-slate-600" id="closeWaitlistModal">&times;</button>
      </div>

      <div class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Guest Name *</label>
          <input type="text" id="waitlistName" class="w-full border rounded-xl p-2" placeholder="Full name">
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Phone Number</label>
          <input type="text" id="waitlistPhone" class="w-full border rounded-xl p-2" placeholder="Contact number">
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Party Size *</label>
          <input type="number" id="waitlistSize" class="w-full border rounded-xl p-2" min="1" value="2">
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Requested Time (optional)</label>
          <input type="time" id="waitlistTime" class="w-full border rounded-xl p-2">
        </div>

        <div class="flex gap-3 pt-2">
          <button class="flex-1 bg-amber-600 text-white py-2 rounded-xl hover:bg-amber-700" id="saveWaitlistBtn">Add
            to Waitlist</button>
          <button class="flex-1 border border-slate-200 py-2 rounded-xl hover:bg-slate-50"
            id="cancelWaitlistBtn">Cancel</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Reservation Details Modal -->
  <div id="detailsModal" class="hidden fixed inset-0 bg-black bg-opacity-50 items-center justify-center z-50">
    <div class="bg-white rounded-2xl p-6 w-full max-w-lg modal-enter">
      <div class="flex justify-between items-center mb-4">
        <h3 class="text-xl font-semibold">Reservation Details</h3>
        <button class="text-slate-400 hover:text-slate-600" id="closeDetailsModal">&times;</button>
      </div>

      <div id="detailsContent" class="space-y-3">
        <!-- Populated by JS -->
      </div>

      <div class="flex gap-3 pt-4">
        <button class="flex-1 bg-amber-600 text-white py-2 rounded-xl hover:bg-amber-700"
          id="closeDetailsBtn">Close</button>
      </div>
    </div>
  </div>

  <script>
    // ==================== GLOBAL VARIABLES ====================
    let currentPage = <?php echo $currentPage; ?>;
    const totalPages = <?php echo $totalPages; ?>;
    let allReservations = [];
    let selectedTable = null;

    // Initialize from PHP data
    const reservationsData = <?php echo json_encode($reservations); ?>;
    const tablesData = <?php echo json_encode($tables); ?>;
    let waitlistData = <?php echo json_encode($waitingList); ?>;

    // ==================== UTILITY FUNCTIONS ====================
    function showToast(message, type = 'info') {
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

    function updateStats() {
      const visibleRows = document.querySelectorAll('#tableBody tr:not([style*="display: none"])');
      let totalGuests = 0;
      
      visibleRows.forEach(row => {
        const pax = parseInt(row.querySelector('td:nth-child(5)')?.innerText || '0');
        totalGuests += pax;
      });

      document.getElementById('totalReservations').textContent = visibleRows.length;
      document.getElementById('totalGuests').textContent = totalGuests;
    }

    // ==================== FILTER FUNCTIONS ====================
    window.filterByDate = function (date) {
      const url = new URL(window.location);
      if (date === 'today') {
        url.searchParams.set('date', 'today');
      } else {
        url.searchParams.set('date', date);
      }
      url.searchParams.set('page', '1');
      window.location.href = url.toString();
    };

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

    // Date filter change handler
    document.getElementById('dateFilter').addEventListener('change', function () {
      const url = new URL(window.location);
      if (this.value !== 'all') {
        url.searchParams.set('date', this.value);
      } else {
        url.searchParams.delete('date');
      }
      url.searchParams.set('page', '1');
      window.location.href = url.toString();
    });

    // Filter buttons
    document.querySelectorAll('.filter-btn').forEach(btn => {
      btn.addEventListener('click', function () {
        filterByStatus(this.dataset.filter);
      });
    });

    // Search with Enter key
    document.getElementById('searchInput').addEventListener('keypress', function (e) {
      if (e.key === 'Enter') {
        const url = new URL(window.location);
        if (this.value.trim()) {
          url.searchParams.set('search', this.value.trim());
        } else {
          url.searchParams.delete('search');
        }
        url.searchParams.set('page', '1');
        window.location.href = url.toString();
      }
    });

    // ==================== PAGINATION FUNCTIONS ====================
    window.changePage = function (page) {
      if (page < 1 || page > totalPages) return;
      const url = new URL(window.location);
      url.searchParams.set('page', page);
      window.location.href = url.toString();
    };

    document.getElementById('prevPage')?.addEventListener('click', function () {
      changePage(<?php echo $currentPage - 1; ?>);
    });

    document.getElementById('nextPage')?.addEventListener('click', function () {
      changePage(<?php echo $currentPage + 1; ?>);
    });

    document.querySelectorAll('.page-btn').forEach(btn => {
      btn.addEventListener('click', function () {
        changePage(parseInt(this.dataset.page));
      });
    });

    // ==================== TABLE SELECTION ====================
    window.selectTable = function (tableNumber) {
      const tableTile = document.querySelector(`[data-table="${tableNumber}"]`);
      if (!tableTile) return;
      
      const status = tableTile.dataset.status;
      if (status !== 'available') {
        showToast(`Table ${tableNumber} is not available (${status})`, 'error');
        return;
      }

      document.querySelectorAll('.table-tile').forEach(t => {
        t.classList.remove('ring-2', 'ring-amber-600', 'border-amber-600');
      });

      tableTile.classList.add('ring-2', 'ring-amber-600', 'border-amber-600');
      selectedTable = tableNumber;
      showToast(`Table ${tableNumber} selected`, 'success');
    };

    // ==================== ACTION BUTTONS ====================
    function updateReservationStatus(reservationId, status, tableNumber = null) {
      const formData = new FormData();
      formData.append('action', 'update_status');
      formData.append('reservation_id', reservationId);
      formData.append('status', status);
      if (tableNumber) formData.append('table_number', tableNumber);

      return fetch('../../../controller/admin/post/restaurant/table_actions.php', {
        method: 'POST',
        body: formData
      }).then(response => response.json());
    }

    // Event delegation for action buttons
    document.getElementById('tableBody').addEventListener('click', async function (e) {
      const target = e.target.closest('.action-btn');
      if (!target) return;

      e.preventDefault();
      const row = target.closest('tr');
      if (!row) return;

      const reservationId = row.dataset.id;
      const guestName = row.dataset.guest;
      const currentStatus = row.dataset.status;

      if (target.classList.contains('seat-btn')) {
        if (!selectedTable) {
          showToast('Please select a table first by clicking on it', 'error');
          return;
        }

        const result = await Swal.fire({
          title: 'Seat Guest',
          text: `Seat ${guestName} at table ${selectedTable}?`,
          icon: 'question',
          showCancelButton: true,
          confirmButtonColor: '#d97706',
          confirmButtonText: 'Yes, seat'
        });

        if (result.isConfirmed) {
          try {
            const data = await updateReservationStatus(reservationId, 'seated', selectedTable);
            
            if (data.success) {
              if (data.has_outstanding_balance) {
                Swal.fire({
                  title: 'Guest Seated',
                  html: `
                    <p>${guestName} seated at table ${selectedTable}</p>
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 mt-3">
                      <p class="text-yellow-700 font-medium flex items-center gap-2">
                        <i class="fas fa-triangle-exclamation"></i>
                        Outstanding Balance Warning
                      </p>
                      <p class="text-sm text-yellow-600 mt-1">${data.warning}</p>
                    </div>
                  `,
                  icon: 'warning',
                  confirmButtonColor: '#d97706'
                }).then(() => {
                  location.reload();
                });
              } else {
                Swal.fire({
                  title: 'Success!',
                  text: `${guestName} seated at table ${selectedTable}`,
                  icon: 'success',
                  confirmButtonColor: '#d97706'
                }).then(() => {
                  location.reload();
                });
              }
            }
          } catch (error) {
            showToast('Error updating status', 'error');
          }
        }
      }
      else if (target.classList.contains('cancel-btn')) {
        const result = await Swal.fire({
          title: 'Cancel Reservation',
          text: `Cancel reservation for ${guestName}?`,
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#d97706',
          cancelButtonColor: '#6b7280',
          confirmButtonText: 'Yes, cancel'
        });

        if (result.isConfirmed) {
          try {
            const data = await updateReservationStatus(reservationId, 'cancelled');
            if (data.success) {
              showToast(`Reservation cancelled for ${guestName}`, 'success');
              setTimeout(() => location.reload(), 1000);
            }
          } catch (error) {
            showToast('Error updating status', 'error');
          }
        }
      }
      else if (target.classList.contains('confirm-btn')) {
        try {
          const data = await updateReservationStatus(reservationId, 'confirmed');
          if (data.success) {
            if (data.has_outstanding_balance) {
              Swal.fire({
                title: 'Reservation Confirmed',
                html: `
                  <p>Reservation confirmed for ${guestName}</p>
                  <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 mt-3">
                    <p class="text-yellow-700 font-medium">${data.warning}</p>
                  </div>
                `,
                icon: 'warning',
                confirmButtonColor: '#d97706'
              }).then(() => {
                location.reload();
              });
            } else {
              showToast(`Reservation confirmed for ${guestName}`, 'success');
              setTimeout(() => location.reload(), 1000);
            }
          }
        } catch (error) {
          showToast('Error updating status', 'error');
        }
      }
      else if (target.classList.contains('details-btn')) {
        const formData = new FormData();
        formData.append('action', 'get_reservation_details');
        formData.append('reservation_id', reservationId);

        try {
          const response = await fetch('../../../controller/admin/post/restaurant/table_actions.php', {
            method: 'POST',
            body: formData
          });
          const data = await response.json();

          if (data.success) {
            const r = data.reservation;
            const detailsContent = document.getElementById('detailsContent');
            detailsContent.innerHTML = `
              <div class="grid grid-cols-2 gap-3">
                <div>
                  <p class="text-xs text-slate-500">Guest</p>
                  <p class="font-medium">${r.guest_first_name} ${r.guest_last_name}</p>
                </div>
                <div>
                  <p class="text-xs text-slate-500">Contact</p>
                  <p class="text-sm">${r.guest_phone || 'N/A'}</p>
                  <p class="text-xs">${r.guest_email || 'N/A'}</p>
                </div>
                <div>
                  <p class="text-xs text-slate-500">Date & Time</p>
                  <p class="text-sm">${r.reservation_date} at ${r.reservation_time}</p>
                </div>
                <div>
                  <p class="text-xs text-slate-500">Table</p>
                  <p class="text-sm">${r.table_number || 'Not assigned'}</p>
                </div>
                <div>
                  <p class="text-xs text-slate-500">Guests</p>
                  <p class="text-sm">${r.guests} pax</p>
                </div>
                <div>
                  <p class="text-xs text-slate-500">Down Payment</p>
                  <p class="text-sm text-amber-600">₱${parseFloat(r.down_payment || 0).toLocaleString()}</p>
                </div>
                <div>
                  <p class="text-xs text-slate-500">Payment Status</p>
                  <p class="text-sm"><span class="${r.payment_status === 'paid' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'} px-2 py-0.5 rounded-full text-xs">${r.payment_status}</span></p>
                </div>
                <div>
                  <p class="text-xs text-slate-500">Points</p>
                  <p class="text-sm">${r.points_earned || 0} ${r.points_awarded ? '(awarded)' : ''}</p>
                </div>
                <div>
                  <p class="text-xs text-slate-500">Occasion</p>
                  <p class="text-sm">${r.occasion || 'None'}</p>
                </div>
                ${data.has_outstanding_balance ? `
                <div class="col-span-2">
                  <div class="bg-amber-50 p-2 rounded-lg">
                    <p class="text-xs text-amber-700 font-medium flex items-center gap-1">
                      <i class="fas fa-circle-exclamation"></i>
                      Outstanding Balance: ₱${parseFloat(data.outstanding_balance).toLocaleString()}
                    </p>
                  </div>
                </div>
                ` : ''}
                <div class="col-span-2">
                  <p class="text-xs text-slate-500">Special Requests</p>
                  <p class="text-sm bg-slate-50 p-2 rounded">${r.special_requests || 'None'}</p>
                </div>
              </div>
            `;
            document.getElementById('detailsModal').classList.remove('hidden');
          }
        } catch (error) {
          showToast('Error loading details', 'error');
        }
      }
    });

    // ==================== AWARD POINTS FUNCTION ====================
    window.awardPoints = async function(reservationId, points) {
      const result = await Swal.fire({
        title: 'Award Loyalty Points',
        html: `
          <p>Are you sure you want to award <strong>${points} points</strong> to this guest?</p>
          <p class="text-xs text-slate-500 mt-2">This action cannot be undone.</p>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#d97706',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, award points'
      });

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
        formData.append('action', 'award_points');
        formData.append('reservation_id', reservationId);

        try {
          const response = await fetch('../../../controller/admin/post/restaurant/table_actions.php', {
            method: 'POST',
            body: formData
          });
          const data = await response.json();

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
        } catch (error) {
          Swal.fire({
            title: 'Error',
            text: 'An error occurred',
            icon: 'error',
            confirmButtonColor: '#d97706'
          });
        }
      }
    };

    // ==================== SHOW UNPAID BALANCE DETAILS ====================
    window.showUnpaidBalanceDetails = function() {
      const rowsWithBalance = Array.from(document.querySelectorAll('#tableBody tr')).filter(row => {
        return row.querySelector('.fa-circle-exclamation') !== null;
      });

      if (rowsWithBalance.length === 0) {
        showToast('No guests with outstanding balance in current view', 'info');
        return;
      }

      let html = '<div class="max-h-96 overflow-y-auto">';
      rowsWithBalance.forEach(row => {
        const guest = row.querySelector('td:nth-child(3)')?.innerText.replace(/[^\w\s]/g, '') || 'Unknown';
        const table = row.querySelector('td:nth-child(4)')?.innerText || 'TBD';
        const status = row.querySelector('td:nth-child(6) span')?.innerText || '';
        const balanceIcon = row.querySelector('.fa-circle-exclamation');
        const title = balanceIcon?.getAttribute('title') || 'Has outstanding balance';
        
        html += `
          <div class="border-b py-2 flex justify-between items-center">
            <div>
              <p class="font-medium">${guest}</p>
              <p class="text-xs text-slate-500">Table ${table} · ${status}</p>
            </div>
            <span class="text-xs text-amber-600">${title}</span>
          </div>
        `;
      });
      html += '</div>';

      Swal.fire({
        title: 'Guests with Outstanding Balance',
        html: html,
        icon: 'info',
        confirmButtonColor: '#d97706',
        width: '500px'
      });
    };

    // ==================== WAITLIST FUNCTIONS ====================
    document.getElementById('addToWaitlistBtn').addEventListener('click', () => {
      document.getElementById('waitlistModal').classList.remove('hidden');
    });

    document.getElementById('closeWaitlistModal').addEventListener('click', () => {
      document.getElementById('waitlistModal').classList.add('hidden');
    });

    document.getElementById('cancelWaitlistBtn').addEventListener('click', () => {
      document.getElementById('waitlistModal').classList.add('hidden');
    });

    document.getElementById('saveWaitlistBtn').addEventListener('click', async () => {
      const name = document.getElementById('waitlistName').value.trim();
      const phone = document.getElementById('waitlistPhone').value.trim();
      const size = document.getElementById('waitlistSize').value;
      const time = document.getElementById('waitlistTime').value;

      if (!name || !size) {
        showToast('Please fill in required fields', 'error');
        return;
      }

      Swal.fire({
        title: 'Adding to Waitlist...',
        text: 'Please wait',
        allowOutsideClick: false,
        didOpen: () => {
          Swal.showLoading();
        }
      });

      const formData = new FormData();
      formData.append('action', 'add_to_waitlist');
      formData.append('guest_name', name);
      formData.append('guest_phone', phone);
      formData.append('party_size', size);
      formData.append('requested_time', time);

      try {
        const response = await fetch('../../../controller/admin/post/restaurant/table_actions.php', {
          method: 'POST',
          body: formData
        });
        const data = await response.json();

        if (data.success) {
          showToast('Guest added to waitlist', 'success');
          document.getElementById('waitlistModal').classList.add('hidden');
          setTimeout(() => location.reload(), 1000);
        } else {
          showToast(data.message, 'error');
        }
      } catch (error) {
        showToast('Error adding to waitlist', 'error');
      }
    });

    window.seatFromWaitlist = async function (waitlistId) {
      if (!selectedTable) {
        showToast('Please select a table first', 'error');
        return;
      }

      const result = await Swal.fire({
        title: 'Seat from Waitlist',
        text: `Seat guest at table ${selectedTable}?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#d97706',
        confirmButtonText: 'Yes, seat'
      });

      if (result.isConfirmed) {
        Swal.fire({
          title: 'Seating...',
          text: 'Please wait',
          allowOutsideClick: false,
          didOpen: () => {
            Swal.showLoading();
          }
        });

        const formData = new FormData();
        formData.append('action', 'seat_from_waitlist');
        formData.append('waitlist_id', waitlistId);
        formData.append('table_number', selectedTable);

        try {
          const response = await fetch('../../../controller/admin/post/restaurant/table_actions.php', {
            method: 'POST',
            body: formData
          });
          const data = await response.json();

          if (data.success) {
            showToast('Guest seated successfully', 'success');
            setTimeout(() => location.reload(), 1000);
          } else {
            showToast(data.message, 'error');
          }
        } catch (error) {
          showToast('Error seating guest', 'error');
        }
      }
    };

    window.removeFromWaitlist = async function (waitlistId) {
      const result = await Swal.fire({
        title: 'Remove from Waitlist',
        text: 'Remove this guest from waitlist?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d97706',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, remove'
      });

      if (result.isConfirmed) {
        Swal.fire({
          title: 'Removing...',
          text: 'Please wait',
          allowOutsideClick: false,
          didOpen: () => {
            Swal.showLoading();
          }
        });

        const formData = new FormData();
        formData.append('action', 'remove_from_waitlist');
        formData.append('waitlist_id', waitlistId);

        try {
          const response = await fetch('../../../controller/admin/post/restaurant/table_actions.php', {
            method: 'POST',
            body: formData
          });
          const data = await response.json();

          if (data.success) {
            showToast('Guest removed from waitlist', 'success');
            setTimeout(() => location.reload(), 1000);
          } else {
            showToast(data.message, 'error');
          }
        } catch (error) {
          showToast('Error removing guest', 'error');
        }
      }
    };

    // ==================== WALK-IN MODAL ====================
    document.getElementById('addWalkinBtn').addEventListener('click', () => {
      document.getElementById('walkinModal').classList.remove('hidden');
    });

    document.getElementById('closeWalkinModal').addEventListener('click', () => {
      document.getElementById('walkinModal').classList.add('hidden');
    });

    document.getElementById('cancelWalkinBtn').addEventListener('click', () => {
      document.getElementById('walkinModal').classList.add('hidden');
    });

    document.getElementById('saveWalkinBtn').addEventListener('click', async () => {
      const name = document.getElementById('walkinName').value.trim();
      const phone = document.getElementById('walkinPhone').value.trim();
      const email = document.getElementById('walkinEmail').value.trim();
      const guests = document.getElementById('walkinGuests').value;
      const time = document.getElementById('walkinTime').value;
      const requests = document.getElementById('walkinRequests').value.trim();

      if (!name || !guests || !time) {
        showToast('Please fill in required fields', 'error');
        return;
      }

      Swal.fire({
        title: 'Checking Availability...',
        text: 'Please wait',
        allowOutsideClick: false,
        didOpen: () => {
          Swal.showLoading();
        }
      });

      const checkFormData = new FormData();
      checkFormData.append('action', 'check_availability');
      checkFormData.append('guests', guests);

      try {
        const checkResponse = await fetch('../../../controller/admin/post/restaurant/table_actions.php', {
          method: 'POST',
          body: checkFormData
        });
        const checkData = await checkResponse.json();

        Swal.close();

        if (checkData.available) {
          proceedWithWalkin(name, phone, email, guests, time, requests);
        } else {
          const result = await Swal.fire({
            title: 'No Tables Available',
            html: `
              <div class="text-left">
                <p class="mb-3">There are no tables available for ${guests} guests at this time.</p>
                <p>Would you like to add this guest to the waitlist?</p>
                <div class="mt-3 bg-amber-50 p-3 rounded-lg">
                  <p class="text-sm font-medium">Guest: ${name}</p>
                  <p class="text-sm">Party: ${guests} guests</p>
                  <p class="text-sm">Time: ${time}</p>
                </div>
              </div>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d97706',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Yes, add to waitlist',
            cancelButtonText: 'Cancel'
          });

          if (result.isConfirmed) {
            addToWaitlist(name, phone, guests, time);
          }
        }
      } catch (error) {
        Swal.close();
        showToast('Error checking availability', 'error');
      }
    });

    async function proceedWithWalkin(name, phone, email, guests, time, requests) {
      Swal.fire({
        title: 'Creating Reservation...',
        text: 'Please wait',
        allowOutsideClick: false,
        didOpen: () => {
          Swal.showLoading();
        }
      });

      const formData = new FormData();
      formData.append('action', 'create_walkin');
      formData.append('guest_name', name);
      formData.append('guest_phone', phone);
      formData.append('guest_email', email);
      formData.append('guests', guests);
      formData.append('reservation_time', time);
      formData.append('special_requests', requests);

      try {
        const response = await fetch('../../../controller/admin/post/restaurant/table_actions.php', {
          method: 'POST',
          body: formData
        });
        const data = await response.json();

        Swal.close();

        if (data.success) {
          if (data.added_to_waitlist) {
            Swal.fire({
              title: 'Added to Waitlist',
              html: `
                <p>${data.message}</p>
                <p class="text-sm text-amber-600 mt-2">We'll notify you when a table becomes available.</p>
              `,
              icon: 'info',
              confirmButtonColor: '#d97706'
            }).then(() => {
              document.getElementById('walkinModal').classList.add('hidden');
              location.reload();
            });
          } else {
            Swal.fire({
              title: 'Success!',
              html: `
                <p>${data.message}</p>
                <p class="text-sm mt-2">Table <strong>${data.table_assigned}</strong> assigned (capacity ${data.capacity})</p>
                <p class="text-xs text-amber-600 mt-1">Points to earn: ${data.points_earned} · Down payment: ₱${data.down_payment.toLocaleString()}</p>
              `,
              icon: 'success',
              confirmButtonColor: '#d97706'
            }).then(() => {
              document.getElementById('walkinModal').classList.add('hidden');
              location.reload();
            });
          }
        } else {
          Swal.fire({
            title: 'Error',
            text: data.message,
            icon: 'error',
            confirmButtonColor: '#d97706'
          });
        }
      } catch (error) {
        Swal.close();
        showToast('Error creating reservation', 'error');
      }
    }

    async function addToWaitlist(name, phone, guests, time) {
      Swal.fire({
        title: 'Adding to Waitlist...',
        text: 'Please wait',
        allowOutsideClick: false,
        didOpen: () => {
          Swal.showLoading();
        }
      });

      const formData = new FormData();
      formData.append('action', 'add_to_waitlist');
      formData.append('guest_name', name);
      formData.append('guest_phone', phone || '');
      formData.append('party_size', guests);
      formData.append('requested_time', time);

      try {
        const response = await fetch('../../../controller/admin/post/restaurant/table_actions.php', {
          method: 'POST',
          body: formData
        });
        const data = await response.json();

        Swal.close();

        if (data.success) {
          Swal.fire({
            title: 'Added to Waitlist',
            text: 'Guest has been added to the waitlist',
            icon: 'success',
            confirmButtonColor: '#d97706'
          }).then(() => {
            document.getElementById('walkinModal').classList.add('hidden');
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
      } catch (error) {
        Swal.close();
        showToast('Error adding to waitlist', 'error');
      }
    }

    // ==================== AUTO-SEAT WAITLIST CHECK ====================
    async function checkAndSeatWaitlist() {
      try {
        const formData = new FormData();
        formData.append('action', 'check_and_seat_waitlist');

        const response = await fetch('../../../controller/admin/post/restaurant/table_actions.php', {
          method: 'POST',
          body: formData
        });
        const data = await response.json();

        if (data.success && data.seated > 0) {
          showToast(`${data.seated} guest(s) automatically seated from waitlist`, 'success');
          setTimeout(() => location.reload(), 1500);
        }
      } catch (error) {
        console.error('Error checking waitlist:', error);
      }
    }

    setInterval(checkAndSeatWaitlist, 30000);

    // ==================== EXPORT FUNCTION ====================
    document.getElementById('exportBtn').addEventListener('click', () => {
      const form = document.createElement('form');
      form.method = 'POST';
      form.action = '../../../controller/admin/post/restaurant/table_actions.php';
      form.innerHTML = `
        <input type="hidden" name="action" value="export_reservations">
        <input type="hidden" name="date" value="<?php echo $today; ?>">
      `;
      document.body.appendChild(form);
      form.submit();
      document.body.removeChild(form);
      showToast('Exporting reservations...', 'info');
    });

    // ==================== CLOSE MODALS ====================
    document.getElementById('closeDetailsModal').addEventListener('click', () => {
      document.getElementById('detailsModal').classList.add('hidden');
    });

    document.getElementById('closeDetailsBtn').addEventListener('click', () => {
      document.getElementById('detailsModal').classList.add('hidden');
    });

    window.addEventListener('click', (e) => {
      if (e.target.classList.contains('fixed')) {
        document.getElementById('walkinModal')?.classList.add('hidden');
        document.getElementById('waitlistModal')?.classList.add('hidden');
        document.getElementById('detailsModal')?.classList.add('hidden');
      }
    });

    // ==================== INITIALIZE ====================
    document.addEventListener('DOMContentLoaded', function () {
      updateStats();
    });
  </script>
</body>

</html>   