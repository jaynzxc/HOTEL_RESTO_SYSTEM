<?php
/**
 * View - Admin Reservations & Booking
 */
require_once '../../../controller/admin/get/reservations_booking.php';

// Set current page for navigation
$current_page = 'reservation_&_booking';
?>
<!DOCTYPE html>
<html lang="en">

  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin · Reservations & Booking</title>
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
            <h1 class="text-2xl lg:text-3xl font-light text-slate-800">Reservations & Booking</h1>
            <p class="text-sm text-slate-500 mt-0.5">manage all hotel reservations, modify, confirm, or cancel bookings
            </p>
          </div>
          <div class="flex gap-3 text-sm">
            <span class="bg-white border rounded-full px-4 py-2 flex items-center gap-2 shadow-sm">
              <i class="fas fa-calendar text-slate-400"></i>
              <span id="currentDate"><?php echo date('F j, Y'); ?></span>
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

        <!-- STATS CARDS -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
          <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs text-slate-500">Total reservations</p>
            <p class="text-2xl font-semibold" id="totalReservations"><?php echo $stats['total']; ?></p>
            <span class="text-xs text-green-600" id="totalGrowth">↑
              <?php echo $stats['confirmed'] + $stats['pending']; ?> active</span>
          </div>
          <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs text-slate-500">Confirmed</p>
            <p class="text-2xl font-semibold text-green-600" id="confirmedCount"><?php echo $stats['confirmed']; ?></p>
          </div>
          <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs text-slate-500">Pending</p>
            <p class="text-2xl font-semibold text-amber-600" id="pendingCount"><?php echo $stats['pending']; ?></p>
          </div>
          <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs text-slate-500">Checked-in</p>
            <p class="text-2xl font-semibold text-blue-600" id="checkedInCount"><?php echo $stats['checked_in']; ?></p>
          </div>
          <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs text-slate-500">Cancelled</p>
            <p class="text-2xl font-semibold text-rose-600" id="cancelledCount"><?php echo $stats['cancelled']; ?></p>
          </div>
        </div>

        <!-- TODAY'S ARRIVALS & DEPARTURES -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-6">
          <div class="bg-green-50 border border-green-200 rounded-2xl p-4 flex items-center gap-4">
            <div class="h-12 w-12 rounded-full bg-green-200 flex items-center justify-center text-green-700">
              <i class="fas fa-calendar-check text-xl"></i>
            </div>
            <div>
              <p class="text-sm text-green-700 font-medium">Today's arrivals</p>
              <p class="text-2xl font-bold" id="todayArrivals"><?php echo $todayStats['arrivals'] ?? 0; ?></p>
              <p class="text-xs text-green-600" id="pendingArrivals"><?php echo $todayStats['pending_arrivals'] ?? 0; ?>
                pending check-in</p>
            </div>
            <a href="./arrival/arrivals_today.php"
              class="ml-auto bg-green-600 text-white px-4 py-2 rounded-xl text-sm hover:bg-green-700">view</a>
          </div>
          <div class=" bg-orange-50 border border-orange-200 rounded-2xl p-4 flex items-center gap-4">
            <div class="h-12 w-12 rounded-full bg-orange-200 flex items-center justify-center text-orange-700">
              <i class="fas fa-calendar-xmark text-xl"></i>
            </div>
            <div>
              <p class="text-sm text-orange-700 font-medium">Today's departures</p>
              <p class="text-2xl font-bold" id="todayDepartures"><?php echo $todayStats['departures'] ?? 0; ?></p>
              <p class="text-xs text-orange-600" id="pendingDepartures">
                <?php echo $todayStats['pending_departures'] ?? 0; ?> pending check-out
              </p>
            </div>
            <a href="./departure/departure_today.php"
              class="ml-auto bg-orange-600 text-white px-4 py-2 rounded-xl text-sm hover:bg-orange-700"
              onclick="showDepartures()">view</a>
          </div>
        </div>

        <!-- FILTER AND SEARCH -->
        <div
          class="bg-white rounded-2xl border border-slate-200 p-4 mb-6 flex flex-wrap gap-3 items-center justify-between">
          <div class="flex gap-2 flex-wrap" id="filterButtons">
            <button
              class="filter-btn <?php echo $statusFilter == 'all' ? 'bg-amber-600 text-white' : 'border border-slate-200 hover:bg-slate-50'; ?> px-4 py-2 rounded-xl text-sm"
              data-filter="all">all</button>
            <button
              class="filter-btn <?php echo $statusFilter == 'confirmed' ? 'bg-amber-600 text-white' : 'border border-slate-200 hover:bg-slate-50'; ?> px-4 py-2 rounded-xl text-sm"
              data-filter="confirmed">confirmed</button>
            <button
              class="filter-btn <?php echo $statusFilter == 'pending' ? 'bg-amber-600 text-white' : 'border border-slate-200 hover:bg-slate-50'; ?> px-4 py-2 rounded-xl text-sm"
              data-filter="pending">pending</button>
            <button
              class="filter-btn <?php echo $statusFilter == 'checked-in' ? 'bg-amber-600 text-white' : 'border border-slate-200 hover:bg-slate-50'; ?> px-4 py-2 rounded-xl text-sm"
              data-filter="checked-in">checked-in</button>
            <button
              class="filter-btn <?php echo $statusFilter == 'cancelled' ? 'bg-amber-600 text-white' : 'border border-slate-200 hover:bg-slate-50'; ?> px-4 py-2 rounded-xl text-sm"
              data-filter="cancelled">cancelled</button>
          </div>
          <div class="relative">
            <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
            <input type="text" id="searchInput" placeholder="search by guest or booking #..."
              value="<?php echo htmlspecialchars($searchFilter); ?>"
              class="border border-slate-200 rounded-xl pl-9 pr-4 py-2 text-sm w-64 focus:ring-1 focus:ring-amber-500 outline-none">
          </div>
        </div>

        <!-- RESERVATIONS TABLE -->
        <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden mb-8">
          <div class="overflow-x-auto">
            <table class="w-full text-sm" id="reservationsTable">
              <thead class="bg-slate-50 text-slate-500 text-xs border-b">
                <tr>
                  <td class="p-4">Booking #</td>
                  <td class="p-4">Guest</td>
                  <td class="p-4">Room</td>
                  <td class="p-4">Check-in</td>
                  <td class="p-4">Check-out</td>
                  <td class="p-4">Nights</td>
                  <td class="p-4">Status</td>
                  <td class="p-4">Points</td>
                  <td class="p-4">Actions</td>
                </tr>
              </thead>
              <tbody class="divide-y" id="tableBody">
                <?php foreach ($reservations as $res): ?>
                  <tr>
                    <td class="p-4 font-medium"><?php echo htmlspecialchars($res['bookingNo']); ?></td>
                    <td class="p-4"><?php echo htmlspecialchars($res['guest']); ?></td>
                    <td class="p-4"><?php echo htmlspecialchars($res['room']); ?></td>
                    <td class="p-4"><?php echo date('M d, Y', strtotime($res['checkIn'])); ?></td>
                    <td class="p-4"><?php echo date('M d, Y', strtotime($res['checkOut'])); ?></td>
                    <td class="p-4"><?php echo $res['nights']; ?></td>
                    <td class="p-4">
                      <span class="status-badge px-2 py-1 rounded-full text-xs font-medium
      <?php
      switch ($res['status']) {
        case 'confirmed':
          echo 'bg-green-100 text-green-700';
          break;
        case 'pending':
          echo 'bg-yellow-100 text-yellow-700';
          break;
        case 'checked-in':
          echo 'bg-blue-100 text-blue-700';
          break;
        case 'completed':
          echo 'bg-slate-100 text-slate-700';
          break;
        case 'cancelled':
          echo 'bg-red-100 text-red-700';
          break;
        default:
          echo 'bg-slate-100 text-slate-700';
      }
      ?>">
                                          <?php echo $res['status']; ?>
                      </span>
                    </td>
                    <td class="p-4">
                                        <?php if ($res['points_earned'] > 0): ?>
                        <span
                          class="text-xs font-medium <?php echo (isset($res['points_awarded']) && $res['points_awarded']) ? 'text-green-600' : 'text-amber-600'; ?> cursor-help"
                          title="<?php echo (isset($res['points_awarded']) && $res['points_awarded']) ? 'Points already awarded: ' . $res['points_earned'] : 'Points to earn: ' . $res['points_earned'] . ' (add manually)'; ?>">
                          +<?php echo $res['points_earned']; ?>
                          <i
                            class="fas <?php echo (isset($res['points_awarded']) && $res['points_awarded']) ? 'fa-circle-check text-green-500' : 'fa-star text-amber-500'; ?>"></i>
                        </span>
                                        <?php else: ?>
                        <span class="text-xs text-slate-400">—</span>
                                        <?php endif; ?>
                    </td>
                    <td class="p-4">
                      <div class="flex gap-2">
                        <button onclick="editReservation(<?php echo $res['id']; ?>)"
                          class="text-amber-700 hover:underline text-xs" title="Edit">
                          <i class="fas fa-pen-to-square"></i>
                        </button>

                        <?php if ($res['status'] !== 'cancelled' && $res['status'] !== 'completed'): ?>
                          <button onclick="updateStatus(<?php echo $res['id']; ?>, '<?php echo $res['status']; ?>')"
                            class="text-blue-600 hover:underline text-xs" title="Update Status">
                            <i class="fas fa-arrow-rotate-right"></i>
                          </button>
                                          <?php endif; ?>

                        <?php if ($res['status'] === 'cancelled'): ?>
                          <button onclick="archiveReservation(<?php echo $res['id']; ?>)"
                            class="text-slate-400 hover:text-slate-600 text-xs" title="Archive">
                            <i class="fas fa-box-archive"></i>
                          </button>
                                          <?php endif; ?>

                        <!-- Points button - disabled if already awarded -->
                                          <?php if ($res['points_earned'] > 0 && isset($res['user_id']) && $res['user_id']): ?>
                                            <?php if (isset($res['points_awarded']) && $res['points_awarded']): ?>
                            <!-- Already awarded - disabled button -->
                            <button disabled class="text-slate-300 cursor-not-allowed text-xs"
                              title="Points already awarded (<?php echo $res['points_earned']; ?> points)">
                              <i class="fas fa-star"></i>
                            </button>
                                            <?php else: ?>
                            <!-- Not yet awarded - active button -->
                            <button onclick="addPoints(<?php echo $res['id']; ?>, <?php echo $res['points_earned']; ?>)"
                              class="text-amber-600 hover:text-amber-700 text-xs"
                              title="Add <?php echo $res['points_earned']; ?> points to user">
                              <i class="fas fa-star"></i>
                            </button>
                                            <?php endif; ?>
                                          <?php endif; ?>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <div class="p-4 border-t border-slate-200 flex items-center justify-between">
            <span class="text-xs text-slate-500" id="paginationInfo">
              Showing
              <?php echo (($currentPage - 1) * $limit + 1); ?>-<?php echo min($currentPage * $limit, $totalReservations); ?>
              of <?php echo $totalReservations; ?> reservations
            </span>
            <div class="flex gap-2" id="paginationControls">
              <button
                class="border border-slate-200 px-3 py-1 rounded-lg text-sm <?php echo $currentPage <= 1 ? 'opacity-50 cursor-not-allowed' : ''; ?>"
                id="prevPage" <?php echo $currentPage <= 1 ? 'disabled' : ''; ?>
                onclick="changePage(<?php echo $currentPage - 1; ?>)">
                Previous
              </button>
              <?php for ($i = 1; $i <= min(5, $totalPages); $i++): ?>
                <button
                  class="page-btn <?php echo $i == $currentPage ? 'bg-amber-600 text-white' : 'border border-slate-200'; ?> px-3 py-1 rounded-lg text-sm"
                  data-page="<?php echo $i; ?>" onclick="changePage(<?php echo $i; ?>)">
                  <?php echo $i; ?>
                </button>
              <?php endfor; ?>
              <?php if ($totalPages > 5): ?>
                <span class="px-2">...</span>
                <button class="border border-slate-200 px-3 py-1 rounded-lg text-sm"
                  onclick="changePage(<?php echo $totalPages; ?>)">
                  <?php echo $totalPages; ?>
                </button>
              <?php endif; ?>
              <button
                class="border border-slate-200 px-3 py-1 rounded-lg text-sm <?php echo $currentPage >= $totalPages ? 'opacity-50 cursor-not-allowed' : ''; ?>"
                id="nextPage" <?php echo $currentPage >= $totalPages ? 'disabled' : ''; ?>
                onclick="changePage(<?php echo $currentPage + 1; ?>)">
                Next
              </button>
            </div>
          </div>
        </div>

        <!-- BOTTOM: NEW BOOKING & RECENT ACTIVITY -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <!-- new booking quick form -->
          <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 p-5">
            <h2 class="font-semibold text-lg flex items-center gap-2 mb-3">
              <i class="fas fa-plus text-amber-600"></i> quick new booking
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs text-slate-500 mb-1">guest name</label>
                <input type="text" id="guestName" placeholder="full name"
                  class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm">
              </div>
              <div>
                <label class="block text-xs text-slate-500 mb-1">room type</label>
                <select id="roomType" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm">
                  <option value="">Select room...</option>
                  <?php foreach ($availableRooms as $room): ?>
                    <option value="<?php echo $room['id']; ?>" data-price="<?php echo $room['price']; ?>">
                      <?php echo htmlspecialchars($room['name']); ?> - ₱<?php echo number_format($room['price']); ?>/night
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div>
                <label class="block text-xs text-slate-500 mb-1">check-in</label>
                <input type="date" id="checkIn" min="<?php echo date('Y-m-d'); ?>"
                  class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm">
              </div>
              <div>
                <label class="block text-xs text-slate-500 mb-1">check-out</label>
                <input type="date" id="checkOut" min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>"
                  class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm">
              </div>
              <div>
                <label class="block text-xs text-slate-500 mb-1">adults</label>
                <select id="adults" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm">
                  <?php for ($i = 1; $i <= 4; $i++): ?>
                    <option value="<?php echo $i; ?>"><?php echo $i; ?> adult<?php echo $i > 1 ? 's' : ''; ?></option>
                  <?php endfor; ?>
                </select>
              </div>
              <div>
                <label class="block text-xs text-slate-500 mb-1">children</label>
                <select id="children" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm">
                  <?php for ($i = 0; $i <= 3; $i++): ?>
                    <option value="<?php echo $i; ?>"><?php echo $i; ?> child<?php echo $i != 1 ? 'ren' : ''; ?></option>
                  <?php endfor; ?>
                </select>
              </div>
              <div class="md:col-span-2">
                <label class="block text-xs text-slate-500 mb-1">special requests (optional)</label>
                <textarea id="specialRequests" rows="2"
                  class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm"></textarea>
              </div>
            </div>
            <div class="flex gap-3 mt-4">
              <button class="bg-amber-600 hover:bg-amber-700 text-white px-5 py-2 rounded-xl text-sm"
                onclick="createBooking()">
                create booking
              </button>
              <button class="border border-slate-300 px-5 py-2 rounded-xl text-sm hover:bg-slate-50"
                onclick="checkAvailability()">
                check availability
              </button>
            </div>
            <div id="availabilityResult" class="mt-3 text-sm hidden"></div>
          </div>

          <!-- recent activity -->
          <div class="bg-amber-50 border border-amber-200 rounded-2xl p-5">
            <h3 class="font-semibold flex items-center gap-2 mb-3">
              <i class="fas fa-clock-rotate-left text-amber-600"></i> recent activity
            </h3>
            <ul class="space-y-3 text-sm" id="recentActivityList">
              <?php foreach ($recentActivity as $activity): ?>
                <li class="flex gap-2">
                  <span class="text-amber-600">●</span>
                  <span class="text-xs">
                    <?php
                    echo htmlspecialchars($activity['booking']) . ' ' .
                      $activity['action'] . ' by ' .
                      htmlspecialchars($activity['guest']);
                    if ($activity['minutes_ago'] < 60) {
                      echo ' (' . $activity['minutes_ago'] . ' min ago)';
                    } else {
                      echo ' (' . floor($activity['minutes_ago'] / 60) . ' hours ago)';
                    }
                    ?>
                  </span>
                </li>
              <?php endforeach; ?>
              <?php if (empty($recentActivity)): ?>
                <li class="text-xs text-slate-500">No recent activity</li>
              <?php endif; ?>
            </ul>
          </div>
        </div>

      </main>
    </div>

    <!-- Status Update Modal -->
    <div id="statusModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
      <div class="bg-white rounded-2xl p-6 max-w-md w-full mx-4">
        <h3 class="text-lg font-semibold mb-4">Update Reservation Status</h3>
        <input type="hidden" id="statusBookingId">
        <select id="newStatus" class="w-full border border-slate-200 rounded-xl p-3 mb-4">
          <option value="pending">Pending</option>
          <option value="confirmed">Confirmed</option>
          <option value="checked-in">Checked-in</option>
          <option value="completed">Completed</option>
          <option value="cancelled">Cancelled</option>
        </select>
        <div class="flex gap-3">
          <button onclick="closeStatusModal()"
            class="flex-1 border border-slate-200 px-4 py-2 rounded-xl hover:bg-slate-50">Cancel</button>
          <button onclick="submitStatusUpdate()"
            class="flex-1 bg-amber-600 text-white px-4 py-2 rounded-xl hover:bg-amber-700">Update</button>
        </div>
      </div>
    </div>

    <!-- Edit Reservation Modal -->
    <div id="editModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
      <div class="bg-white rounded-2xl p-6 max-w-3xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-xl font-semibold">Edit Reservation</h3>
          <button onclick="closeEditModal()" class="text-slate-400 hover:text-slate-600">
            <i class="fa-solid fa-xmark text-2xl"></i>
          </button>
        </div>

        <form id="editForm" onsubmit="submitEdit(event)">
          <input type="hidden" name="action" value="edit_reservation">
          <input type="hidden" name="booking_id" id="edit_booking_id">

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
              <label class="block text-xs text-slate-500 mb-1">First Name <span class="text-red-500">*</span></label>
              <input type="text" id="edit_first_name" name="guest_first_name" required
                class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-amber-500 outline-none">
            </div>
            <div>
              <label class="block text-xs text-slate-500 mb-1">Last Name <span class="text-red-500">*</span></label>
              <input type="text" id="edit_last_name" name="guest_last_name" required
                class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-amber-500 outline-none">
            </div>
            <div>
              <label class="block text-xs text-slate-500 mb-1">Email</label>
              <input type="email" id="edit_email" name="guest_email"
                class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-amber-500 outline-none">
            </div>
            <div>
              <label class="block text-xs text-slate-500 mb-1">Phone</label>
              <input type="text" id="edit_phone" name="guest_phone"
                class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-amber-500 outline-none">
            </div>
            <div>
              <label class="block text-xs text-slate-500 mb-1">Check-in <span class="text-red-500">*</span></label>
              <input type="date" id="edit_check_in" name="check_in" required min="<?php echo date('Y-m-d'); ?>"
                class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-amber-500 outline-none">
            </div>
            <div>
              <label class="block text-xs text-slate-500 mb-1">Check-out <span class="text-red-500">*</span></label>
              <input type="date" id="edit_check_out" name="check_out" required
                min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>"
                class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-amber-500 outline-none">
            </div>
            <div>
              <label class="block text-xs text-slate-500 mb-1">Room <span class="text-red-500">*</span></label>
              <select id="edit_room_id" name="room_id" required
                class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-amber-500 outline-none">
                <option value="">Select room...</option>
                <?php foreach ($availableRooms as $room): ?>
                  <option value="<?php echo $room['id']; ?>" data-price="<?php echo $room['price']; ?>">
                    <?php echo htmlspecialchars($room['name']); ?> - ₱
                    <?php echo number_format($room['price']); ?>/night
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div>
              <label class="block text-xs text-slate-500 mb-1">Adults</label>
              <select id="edit_adults" name="adults"
                class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-amber-500 outline-none">
                <?php for ($i = 1; $i <= 4; $i++): ?>
                  <option value="<?php echo $i; ?>">
                    <?php echo $i; ?> adult
                    <?php echo $i > 1 ? 's' : ''; ?>
                  </option>
                <?php endfor; ?>
              </select>
            </div>
            <div>
              <label class="block text-xs text-slate-500 mb-1">Children</label>
              <select id="edit_children" name="children"
                class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-amber-500 outline-none">
                <?php for ($i = 0; $i <= 3; $i++): ?>
                  <option value="<?php echo $i; ?>">
                    <?php echo $i; ?> child
                    <?php echo $i != 1 ? 'ren' : ''; ?>
                  </option>
                <?php endfor; ?>
              </select>
            </div>
            <div>
              <label class="block text-xs text-slate-500 mb-1">Status</label>
              <select id="edit_status" name="status"
                class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-amber-500 outline-none">
                <option value="pending">Pending</option>
                <option value="confirmed">Confirmed</option>
                <option value="checked-in">Checked-in</option>
                <option value="completed">Completed</option>
                <option value="cancelled">Cancelled</option>
              </select>
            </div>
            <div>
              <label class="block text-xs text-slate-500 mb-1">Payment Status</label>
              <select id="edit_payment_status" name="payment_status"
                class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-amber-500 outline-none">
                <option value="unpaid">Unpaid</option>
                <option value="paid">Paid</option>
                <option value="refunded">Refunded</option>
              </select>
            </div>
          </div>

          <div class="mb-4">
            <label class="block text-xs text-slate-500 mb-1">Special Requests</label>
            <textarea id="edit_special_requests" name="special_requests" rows="2"
              class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-amber-500 outline-none"></textarea>
          </div>

          <div id="editSummary" class="bg-amber-50 p-4 rounded-xl mb-4 hidden">
            <h4 class="font-semibold text-amber-800 mb-2">Booking Summary</h4>
            <div class="grid grid-cols-2 gap-2 text-sm">
              <div>Nights:</div>
              <div id="edit_nights_display">0</div>
              <div>Subtotal:</div>
              <div id="edit_subtotal_display">₱0.00</div>
              <div>Tax (12%):</div>
              <div id="edit_tax_display">₱0.00</div>
              <div class="font-bold">Total:</div>
              <div class="font-bold" id="edit_total_display">₱0.00</div>
              <div class="text-amber-600">Points to earn:</div>
              <div class="text-amber-600" id="edit_points_display">0</div>
            </div>
          </div>

          <div class="flex gap-3">
            <button type="button" onclick="closeEditModal()"
              class="flex-1 border border-slate-200 px-4 py-2 rounded-xl hover:bg-slate-50">Cancel</button>
            <button type="submit" class="flex-1 bg-amber-600 text-white px-4 py-2 rounded-xl hover:bg-amber-700">Update
              Booking</button>
          </div>
        </form>
      </div>
    </div>

    <script>
      // Pass PHP data to JavaScript
      const totalPages = <?php echo $totalPages; ?>;
      const currentPage = <?php echo $currentPage; ?>;
      const currentFilter = '<?php echo $statusFilter; ?>';
      const currentSearch = '<?php echo $searchFilter; ?>';

      // Toast notification
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

      // Edit reservation function (MODAL VERSION - KEEP THIS ONE)
      function editReservation(id) {
        Swal.fire({
          title: 'Loading...',
          text: 'Please wait',
          allowOutsideClick: false,
          didOpen: () => { Swal.showLoading(); }
        });

        const formData = new FormData();
        formData.append('action', 'get_reservation_details');
        formData.append('booking_id', id);

        fetch('../../../controller/admin/post/reservationBooking_action.php', {
          method: 'POST',
          body: formData
        })
          .then(response => response.json())
          .then(data => {
            Swal.close();
            if (data.success) {
              const b = data.booking;

              // Populate form
              document.getElementById('edit_booking_id').value = b.id;
              document.getElementById('edit_first_name').value = b.guest_first_name || '';
              document.getElementById('edit_last_name').value = b.guest_last_name || '';
              document.getElementById('edit_email').value = b.guest_email || '';
              document.getElementById('edit_phone').value = b.guest_phone || '';
              document.getElementById('edit_check_in').value = b.check_in;
              document.getElementById('edit_check_out').value = b.check_out;
              document.getElementById('edit_room_id').value = b.room_id || '';
              document.getElementById('edit_adults').value = b.adults || 2;
              document.getElementById('edit_children').value = b.children || 0;
              document.getElementById('edit_status').value = b.status || 'pending';
              document.getElementById('edit_payment_status').value = b.payment_status || 'unpaid';
              document.getElementById('edit_special_requests').value = b.special_requests || '';

              // Calculate and show summary
              updateEditSummary();

              // Show modal
              document.getElementById('editModal').classList.remove('hidden');
              document.getElementById('editModal').classList.add('flex');
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
              text: 'An error occurred',
              icon: 'error',
              confirmButtonColor: '#d97706'
            });
          });
      }

      // Close edit modal
      function closeEditModal() {
        document.getElementById('editModal').classList.add('hidden');
        document.getElementById('editModal').classList.remove('flex');
      }

      // Update edit summary
      function updateEditSummary() {
        const checkIn = document.getElementById('edit_check_in').value;
        const checkOut = document.getElementById('edit_check_out').value;
        const roomSelect = document.getElementById('edit_room_id');
        const selectedOption = roomSelect.options[roomSelect.selectedIndex];

        if (!checkIn || !checkOut || !selectedOption.value) {
          document.getElementById('editSummary').classList.add('hidden');
          return;
        }

        const price = parseFloat(selectedOption.dataset.price || 0);
        const checkInDate = new Date(checkIn);
        const checkOutDate = new Date(checkOut);
        const nights = Math.max(1, Math.ceil((checkOutDate - checkInDate) / (1000 * 60 * 60 * 24)));

        const subtotal = price * nights;
        const tax = subtotal * 0.12;
        const total = subtotal + tax;
        const points = Math.floor(total / 100) * 5;

        document.getElementById('edit_nights_display').textContent = nights;
        document.getElementById('edit_subtotal_display').textContent = '₱' + subtotal.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        document.getElementById('edit_tax_display').textContent = '₱' + tax.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        document.getElementById('edit_total_display').textContent = '₱' + total.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        document.getElementById('edit_points_display').textContent = points;

        document.getElementById('editSummary').classList.remove('hidden');
      }

      // Submit edit form
      function submitEdit(event) {
        event.preventDefault();

        const formData = new FormData(document.getElementById('editForm'));

        Swal.fire({
          title: 'Updating...',
          text: 'Please wait',
          allowOutsideClick: false,
          didOpen: () => { Swal.showLoading(); }
        });

        fetch('../../../controller/admin/post/reservationBooking_action.php', {
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
                    <p class="text-sm mt-2"><strong>Reference:</strong> ${data.booking.reference}</p>
                    <p class="text-sm"><strong>Guest:</strong> ${data.booking.guest}</p>
                    <p class="text-sm"><strong>Total:</strong> ₱${data.booking.total.toLocaleString()}</p>
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
              text: 'An error occurred',
              icon: 'error',
              confirmButtonColor: '#d97706'
            });
          });
      }

      // View notifications
      function viewNotifications() {
        Swal.fire({
          title: 'Loading...',
          text: 'Please wait',
          allowOutsideClick: false,
          didOpen: () => { Swal.showLoading(); }
        });

        const formData = new FormData();
        formData.append('action', 'get_notifications');

        fetch('../../../controller/admin/post/reservationBooking_action.php', {
          method: 'POST',
          body: formData
        })
          .then(response => response.json())
          .then(data => {
            Swal.close();
            if (data.success) {
              let html = '<div class="max-h-96 overflow-y-auto">';

              if (data.notifications.length === 0) {
                html += '<p class="text-center text-slate-500 py-4">No notifications</p>';
              } else {
                data.notifications.forEach(notif => {
                  const date = new Date(notif.created_at).toLocaleString();
                  const bgColor = notif.is_read ? 'bg-white' : 'bg-amber-50';
                  html += `
                        <div class="${bgColor} p-3 rounded-lg mb-2 border-l-4 border-amber-500">
                            <p class="font-medium">${notif.title}</p>
                            <p class="text-xs text-slate-600">${notif.message}</p>
                            <p class="text-xs text-slate-400 mt-1">${date}</p>
                        </div>
                    `;
                });
              }

              html += '</div>';

              Swal.fire({
                title: 'Notifications',
                html: html,
                icon: 'info',
                confirmButtonColor: '#d97706',
                width: '500px'
              });
            }
          });
      }

      // Add event listeners for edit form
      document.getElementById('edit_check_in')?.addEventListener('change', updateEditSummary);
      document.getElementById('edit_check_out')?.addEventListener('change', updateEditSummary);
      document.getElementById('edit_room_id')?.addEventListener('change', updateEditSummary);

      // Update notification bell click
      document.getElementById('notificationBell').addEventListener('click', viewNotifications);

      // Filter by status
      function filterByStatus(status) {
        const url = new URL(window.location);
        if (status !== 'all') {
          url.searchParams.set('status', status);
        } else {
          url.searchParams.delete('status');
        }
        url.searchParams.set('page', '1');
        window.location.href = url.toString();
      }

      // Search
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

      // Filter buttons
      document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.addEventListener('click', function () {
          filterByStatus(this.dataset.filter);
        });
      });

      // Change page
      function changePage(page) {
        if (page < 1 || page > totalPages) return;
        const url = new URL(window.location);
        url.searchParams.set('page', page);
        window.location.href = url.toString();
      }

      // Update status modal
      function updateStatus(id, currentStatus) {
        document.getElementById('statusBookingId').value = id;
        document.getElementById('newStatus').value = currentStatus;
        document.getElementById('statusModal').classList.remove('hidden');
        document.getElementById('statusModal').classList.add('flex');
      }

      function closeStatusModal() {
        document.getElementById('statusModal').classList.add('hidden');
        document.getElementById('statusModal').classList.remove('flex');
      }

      function submitStatusUpdate() {
        const id = document.getElementById('statusBookingId').value;
        const newStatus = document.getElementById('newStatus').value;

        Swal.fire({
          title: 'Update Status',
          text: `Are you sure you want to change status to ${newStatus}?`,
          icon: 'question',
          showCancelButton: true,
          confirmButtonColor: '#d97706',
          cancelButtonColor: '#6b7280',
          confirmButtonText: 'Yes, update'
        }).then((result) => {
          if (result.isConfirmed) {
            Swal.fire({
              title: 'Processing...',
              text: 'Please wait',
              allowOutsideClick: false,
              didOpen: () => { Swal.showLoading(); }
            });

            const formData = new FormData();
            formData.append('action', 'update_status');
            formData.append('booking_id', id);
            formData.append('status', newStatus);

            fetch('../../../controller/admin/post/reservationBooking_action.php', {
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
                  text: 'An error occurred',
                  icon: 'error',
                  confirmButtonColor: '#d97706'
                });
              });
          }
        });

        closeStatusModal();
      }

      // Add points to user
      // Add points to user
      function addPoints(id, points) {
        // First check if points are already awarded
        fetch('../../../controller/admin/post/reservationBooking_action.php', {
          method: 'POST',
          body: new URLSearchParams({
            'action': 'get_reservation_details',
            'booking_id': id
          })
        })
          .then(response => response.json())
          .then(data => {
            if (data.success && data.booking.points_awarded) {
              Swal.fire({
                title: 'Already Awarded',
                text: `Points (${points}) have already been awarded for this booking.`,
                icon: 'info',
                confirmButtonColor: '#d97706'
              });
              return;
            }

            // If not awarded, proceed with confirmation
            Swal.fire({
              title: 'Add Loyalty Points',
              html: `
        <p>Are you sure you want to add <strong>${points} points</strong> to this user?</p>
        <p class="text-xs text-slate-500 mt-2">This action cannot be undone.</p>
      `,
              icon: 'question',
              showCancelButton: true,
              confirmButtonColor: '#d97706',
              cancelButtonColor: '#6b7280',
              confirmButtonText: 'Yes, add points'
            }).then((result) => {
              if (result.isConfirmed) {
                Swal.fire({
                  title: 'Processing...',
                  text: 'Please wait',
                  allowOutsideClick: false,
                  didOpen: () => { Swal.showLoading(); }
                });

                const formData = new FormData();
                formData.append('action', 'add_points');
                formData.append('booking_id', id);

                fetch('../../../controller/admin/post/reservationBooking_action.php', {
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
                  });
              }
            });
          });
      }
      // Archive reservation
      function archiveReservation(id) {
        Swal.fire({
          title: 'Archive Reservation?',
          text: 'This will move the reservation to archive. You can restore it later.',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#d97706',
          cancelButtonColor: '#6b7280',
          confirmButtonText: 'Yes, archive'
        }).then((result) => {
          if (result.isConfirmed) {
            showToast('Archive feature coming soon', 'info');
          }
        });
      }

      // Show arrivals
      function showArrivals() {
        filterByStatus('checked-in');
      }

      // Show departures
      function showDepartures() {
        filterByStatus('checked-in');
      }

      // Check availability
      function checkAvailability() {
        const checkIn = document.getElementById('checkIn').value;
        const checkOut = document.getElementById('checkOut').value;

        if (!checkIn || !checkOut) {
          showToast('Please select check-in and check-out dates', 'error');
          return;
        }

        Swal.fire({
          title: 'Checking Availability...',
          text: 'Please wait',
          allowOutsideClick: false,
          didOpen: () => { Swal.showLoading(); }
        });

        const formData = new FormData();
        formData.append('action', 'check_availability');
        formData.append('check_in', checkIn);
        formData.append('check_out', checkOut);

        fetch('../../../controller/admin/post/reservationBooking_action.php', {
          method: 'POST',
          body: formData
        })
          .then(response => response.json())
          .then(data => {
            Swal.close();
            if (data.success) {
              const resultDiv = document.getElementById('availabilityResult');
              if (data.count > 0) {
                resultDiv.innerHTML = `
              <div class="bg-green-50 text-green-700 p-3 rounded-lg">
                <i class="fas fa-circle-check mr-2"></i>
                ${data.count} rooms available for these dates
              </div>
            `;
              } else {
                resultDiv.innerHTML = `
              <div class="bg-red-50 text-red-700 p-3 rounded-lg">
                <i class="fas fa-circle-exclamation mr-2"></i>
                No rooms available for these dates
              </div>
            `;
              }
              resultDiv.classList.remove('hidden');
            } else {
              showToast(data.message, 'error');
            }
          });
      }

      // Create booking
      function createBooking() {
        const guestName = document.getElementById('guestName').value.trim();
        const roomId = document.getElementById('roomType').value;
        const checkIn = document.getElementById('checkIn').value;
        const checkOut = document.getElementById('checkOut').value;
        const adults = document.getElementById('adults').value;
        const children = document.getElementById('children').value;
        const specialRequests = document.getElementById('specialRequests').value;

        if (!guestName || !roomId || !checkIn || !checkOut) {
          showToast('Please fill in all required fields', 'error');
          return;
        }

        Swal.fire({
          title: 'Creating Booking...',
          text: 'Please wait',
          allowOutsideClick: false,
          didOpen: () => { Swal.showLoading(); }
        });

        const formData = new FormData();
        formData.append('action', 'create_booking');
        formData.append('guest_name', guestName);
        formData.append('room_id', roomId);
        formData.append('check_in', checkIn);
        formData.append('check_out', checkOut);
        formData.append('adults', adults);
        formData.append('children', children);
        formData.append('special_requests', specialRequests);

        fetch('../../../controller/admin/post/reservationBooking_action.php', {
          method: 'POST',
          body: formData
        })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              Swal.fire({
                title: 'Success!',
                html: `
              <p>Booking created successfully!</p>
              <p class="text-sm mt-2"><strong>Reference:</strong> ${data.booking.reference}</p>
              <p class="text-sm"><strong>Guest:</strong> ${data.booking.guest}</p>
              <p class="text-sm"><strong>Room:</strong> ${data.booking.room}</p>
              <p class="text-sm"><strong>Total:</strong> ₱${data.booking.total.toLocaleString()}</p>
              <p class="text-xs text-amber-600 mt-2">Points to earn: ${data.booking.points_earned} (add manually after payment)</p>
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
              text: 'An error occurred',
              icon: 'error',
              confirmButtonColor: '#d97706'
            });
          });
      }

      // Auto-refresh every 60 seconds (optional)
      // setInterval(() => { location.reload(); }, 60000);
    </script>
  </body>

</html>