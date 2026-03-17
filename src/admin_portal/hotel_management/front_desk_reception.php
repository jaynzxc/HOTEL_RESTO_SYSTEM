<?php
/**
 * View - Admin Front Desk / Reception
 */
require_once '../../../controller/admin/get/front_desk_reception.php';

// Set current page for navigation
$current_page = 'front_desk_reception';
?>
<!DOCTYPE html>
<html lang="en">

  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin · Front Desk / Reception</title>
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

      .modal.active {
        display: flex;
      }

      .modal-content {
        animation: slideIn 0.3s ease;
      }

      @keyframes slideIn {
        from {
          transform: translateY(-50px);
          opacity: 0;
        }

        to {
          transform: translateY(0);
          opacity: 1;
        }
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
    <div id="toast" class="toast bg-white rounded-xl p-4 min-w-75 shadow-lg border border-amber-200 hidden">
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

    <!-- Check-in Modal -->
    <div id="checkinModal" class="modal">
      <div class="modal-content bg-white rounded-2xl p-6 max-w-md w-full mx-4">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-xl font-semibold flex items-center gap-2">
            <i class="fa-regular fa-calendar-check text-amber-600"></i> Check-in Guest
          </h3>
          <button onclick="closeModal('checkinModal')" class="text-slate-400 hover:text-slate-600">
            <i class="fa-solid fa-xmark text-2xl"></i>
          </button>
        </div>

        <div id="checkinGuestInfo" class="mb-4 p-3 bg-slate-50 rounded-xl">
          <!-- Dynamic guest info will appear here -->
        </div>

        <input type="hidden" id="checkinBookingId">

        <div class="space-y-3 mb-6">
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Room Number</label>
            <input type="text" id="checkinRoomNumber"
              class="w-full border border-slate-200 rounded-xl p-3 focus:ring-2 focus:ring-amber-500" readonly>
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Number of Guests</label>
            <select id="checkinGuestCount"
              class="w-full border border-slate-200 rounded-xl p-3 focus:ring-2 focus:ring-amber-500">
              <option value="1 Adult">1 Adult</option>
              <option value="2 Adults" selected>2 Adults</option>
              <option value="3 Adults">3 Adults</option>
              <option value="4 Adults">4 Adults</option>
              <option value="Family with Kids">Family with Kids</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">ID Type</label>
            <select id="checkinIdType"
              class="w-full border border-slate-200 rounded-xl p-3 focus:ring-2 focus:ring-amber-500">
              <option value="Passport">Passport</option>
              <option value="Driver's License">Driver's License</option>
              <option value="National ID">National ID</option>
              <option value="Company ID">Company ID</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">ID Number</label>
            <input type="text" id="checkinIdNumber"
              class="w-full border border-slate-200 rounded-xl p-3 focus:ring-2 focus:ring-amber-500"
              placeholder="Enter ID number" required>
          </div>
        </div>

        <div class="flex gap-3">
          <button onclick="processCheckin()"
            class="flex-1 bg-amber-600 text-white py-3 rounded-xl hover:bg-amber-700 transition font-medium">
            Confirm Check-in
          </button>
          <button onclick="closeModal('checkinModal')"
            class="flex-1 border border-slate-200 py-3 rounded-xl hover:bg-slate-50 transition">
            Cancel
          </button>
        </div>
      </div>
    </div>

    <!-- Check-out Modal -->
    <div id="checkoutModal" class="modal">
      <div class="modal-content bg-white rounded-2xl p-6 max-w-md w-full mx-4">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-xl font-semibold flex items-center gap-2">
            <i class="fa-regular fa-calendar-xmark text-amber-600"></i> Check-out Guest
          </h3>
          <button onclick="closeModal('checkoutModal')" class="text-slate-400 hover:text-slate-600">
            <i class="fa-solid fa-xmark text-2xl"></i>
          </button>
        </div>

        <div id="checkoutGuestInfo" class="mb-4 p-3 bg-slate-50 rounded-xl">
          <!-- Dynamic guest info will appear here -->
        </div>

        <input type="hidden" id="checkoutBookingId">

        <div class="space-y-3 mb-6">
          <div class="flex justify-between items-center border-b pb-2">
            <span class="text-slate-600">Room Charges</span>
            <span class="font-semibold" id="checkoutRoomCharges">₱0</span>
          </div>
          <div class="flex justify-between items-center border-b pb-2">
            <span class="text-slate-600">Mini Bar</span>
            <span class="font-semibold" id="checkoutMiniBar">₱0</span>
          </div>
          <div class="flex justify-between items-center border-b pb-2">
            <span class="text-slate-600">Restaurant</span>
            <span class="font-semibold" id="checkoutRestaurant">₱0</span>
          </div>
          <div class="flex justify-between items-center text-lg font-bold pt-2">
            <span>Total</span>
            <span class="text-amber-600" id="checkoutTotal">₱0</span>
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Payment Method</label>
            <select id="checkoutPaymentMethod"
              class="w-full border border-slate-200 rounded-xl p-3 focus:ring-2 focus:ring-amber-500">
              <option value="cash">Cash</option>
              <option value="credit_card">Credit Card</option>
              <option value="debit_card">Debit Card</option>
              <option value="gcash">GCash</option>
              <option value="company_bill">Company Bill</option>
            </select>
          </div>
        </div>

        <div class="flex gap-3">
          <button onclick="processCheckout()"
            class="flex-1 bg-amber-600 text-white py-3 rounded-xl hover:bg-amber-700 transition font-medium">
            Process Payment
          </button>
          <button onclick="closeModal('checkoutModal')"
            class="flex-1 border border-slate-200 py-3 rounded-xl hover:bg-slate-50 transition">
            Cancel
          </button>
        </div>
      </div>
    </div>

    <!-- Walk-in Guest Modal -->
    <div id="walkinModal" class="modal">
      <div class="modal-content bg-white rounded-2xl p-6 max-w-md w-full mx-4">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-xl font-semibold flex items-center gap-2">
            <i class="fa-regular fa-user text-amber-600"></i> Walk-in Guest
          </h3>
          <button onclick="closeModal('walkinModal')" class="text-slate-400 hover:text-slate-600">
            <i class="fa-solid fa-xmark text-2xl"></i>
          </button>
        </div>

        <div class="space-y-3 mb-6">
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Full Name</label>
            <input type="text" id="walkinName"
              class="w-full border border-slate-200 rounded-xl p-3 focus:ring-2 focus:ring-amber-500"
              placeholder="e.g. Juan Dela Cruz" required>
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Room Type</label>
            <select id="walkinRoomType"
              class="w-full border border-slate-200 rounded-xl p-3 focus:ring-2 focus:ring-amber-500">
              <option value="Deluxe">Deluxe Room - ₱4,200/night</option>
              <option value="Superior">Superior Room - ₱3,500/night</option>
              <option value="Suite">Ocean Suite - ₱6,900/night</option>
              <option value="Twin">Twin Room - ₱3,800/night</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Number of Nights</label>
            <input type="number" id="walkinNights"
              class="w-full border border-slate-200 rounded-xl p-3 focus:ring-2 focus:ring-amber-500" value="1" min="1">
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Adults</label>
            <select id="walkinAdults"
              class="w-full border border-slate-200 rounded-xl p-3 focus:ring-2 focus:ring-amber-500">
              <option value="1">1 Adult</option>
              <option value="2" selected>2 Adults</option>
              <option value="3">3 Adults</option>
              <option value="4">4 Adults</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Children</label>
            <select id="walkinChildren"
              class="w-full border border-slate-200 rounded-xl p-3 focus:ring-2 focus:ring-amber-500">
              <option value="0">0 Children</option>
              <option value="1">1 Child</option>
              <option value="2">2 Children</option>
              <option value="3">3 Children</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Contact Number</label>
            <input type="text" id="walkinContact"
              class="w-full border border-slate-200 rounded-xl p-3 focus:ring-2 focus:ring-amber-500"
              placeholder="09xx xxx xxxx" required>
          </div>
        </div>

        <div class="flex gap-3">
          <button onclick="processWalkin()"
            class="flex-1 bg-amber-600 text-white py-3 rounded-xl hover:bg-amber-700 transition font-medium">
            Create Booking
          </button>
          <button onclick="closeModal('walkinModal')"
            class="flex-1 border border-slate-200 py-3 rounded-xl hover:bg-slate-50 transition">
            Cancel
          </button>
        </div>
      </div>
    </div>

    <!-- Notify Guest Modal -->
    <div id="notifyModal" class="modal">
      <div class="modal-content bg-white rounded-2xl p-6 max-w-md w-full mx-4">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-xl font-semibold flex items-center gap-2">
            <i class="fa-regular fa-bell text-amber-600"></i> Notify Guest
          </h3>
          <button onclick="closeModal('notifyModal')" class="text-slate-400 hover:text-slate-600">
            <i class="fa-solid fa-xmark text-2xl"></i>
          </button>
        </div>

        <div id="notifyGuestInfo" class="mb-4 p-3 bg-slate-50 rounded-xl">
          <!-- Dynamic guest info will appear here -->
        </div>

        <input type="hidden" id="notifyBookingId">

        <div class="space-y-3 mb-6">
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Message</label>
            <textarea id="notifyMessage" rows="3"
              class="w-full border border-slate-200 rounded-xl p-3 focus:ring-2 focus:ring-amber-500"
              placeholder="Enter your message..."></textarea>
          </div>
        </div>

        <div class="flex gap-3">
          <button onclick="sendNotification()"
            class="flex-1 bg-amber-600 text-white py-3 rounded-xl hover:bg-amber-700 transition font-medium">
            Send Notification
          </button>
          <button onclick="closeModal('notifyModal')"
            class="flex-1 border border-slate-200 py-3 rounded-xl hover:bg-slate-50 transition">
            Cancel
          </button>
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
            <h1 class="text-2xl lg:text-3xl font-light text-slate-800">Front Desk / Reception</h1>
            <p class="text-sm text-slate-500 mt-0.5">manage check-ins, check-outs, and guest requests</p>
          </div>
          <div class="flex gap-3 text-sm">
            <span class="bg-white border rounded-full px-4 py-2 flex items-center gap-2 shadow-sm">
              <i class="fa-regular fa-calendar text-slate-400"></i> <span
                id="currentDate"><?php echo date('F j, Y'); ?></span>
            </span>
            <span class="bg-white border rounded-full px-4 py-2 shadow-sm cursor-pointer hover:bg-slate-50 relative"
              onclick="viewNotifications()">
              <i class="fa-regular fa-bell"></i>
              <?php if ($unread_count > 0): ?>
                <span
                  class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-4 h-4 flex items-center justify-center"><?php echo $unread_count; ?></span>
              <?php endif; ?>
            </span>
          </div>
        </div>

        <!-- TOP STATS CARDS -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
          <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <div class="flex items-center gap-3">
              <div class="h-10 w-10 rounded-full bg-green-100 flex items-center justify-center text-green-700"><i
                  class="fa-regular fa-calendar-check"></i></div>
              <div>
                <p class="text-xs text-slate-500">Arrivals today</p>
                <p class="text-2xl font-semibold" id="arrivalsCount"><?php echo count($arrivals); ?></p>
              </div>
            </div>
          </div>
          <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <div class="flex items-center gap-3">
              <div class="h-10 w-10 rounded-full bg-orange-100 flex items-center justify-center text-orange-700"><i
                  class="fa-regular fa-calendar-xmark"></i></div>
              <div>
                <p class="text-xs text-slate-500">Departures today</p>
                <p class="text-2xl font-semibold" id="departuresCount"><?php echo count($departures); ?></p>
              </div>
            </div>
          </div>
          <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <div class="flex items-center gap-3">
              <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-700"><i
                  class="fa-regular fa-building"></i></div>
              <div>
                <p class="text-xs text-slate-500">Occupied rooms</p>
                <p class="text-2xl font-semibold" id="occupiedRooms"><?php echo $roomStats['occupied'] ?? 0; ?></p>
              </div>
            </div>
          </div>
          <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <div class="flex items-center gap-3">
              <div class="h-10 w-10 rounded-full bg-purple-100 flex items-center justify-center text-purple-700"><i
                  class="fa-regular fa-message"></i></div>
              <div>
                <p class="text-xs text-slate-500">Guest requests</p>
                <p class="text-2xl font-semibold" id="guestRequests"><?php echo count($guestRequests); ?></p>
              </div>
            </div>
          </div>
        </div>

        <!-- TWO COLUMN LAYOUT -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">

          <!-- LEFT: Check-ins and Check-outs -->
          <div class="lg:col-span-2 space-y-6">

            <!-- Today's arrivals / check-in queue -->
            <div class="bg-white rounded-2xl border border-slate-200 p-5">
              <div class="flex items-center justify-between mb-4">
                <h2 class="font-semibold text-lg flex items-center gap-2"><i
                    class="fa-regular fa-calendar-check text-amber-600"></i> Today's arrivals (check-in)</h2>
                <button onclick="openWalkinModal()"
                  class="text-sm text-amber-700 border border-amber-600 px-3 py-1 rounded-lg hover:bg-amber-50">+ quick
                  check-in</button>
              </div>
              <div class="overflow-x-auto">
                <table class="w-full text-sm" id="checkinTable">
                  <thead class="text-slate-400 text-xs border-b">
                    <tr>
                      <td class="pb-2">Guest</td>
                      <td>Room</td>
                      <td>Time</td>
                      <td>Status</td>
                      <td>Action</td>
                    </tr>
                  </thead>
                  <tbody class="divide-y" id="checkinBody">
                    <?php if (empty($arrivals)): ?>
                      <tr>
                        <td colspan="5" class="py-4 text-center text-slate-500">No arrivals today</td>
                      </tr>
                    <?php else: ?>
                      <?php foreach ($arrivals as $arrival): ?>
                        <tr data-booking-id="<?php echo $arrival['id']; ?>"
                          data-guest="<?php echo htmlspecialchars($arrival['guest_name']); ?>"
                          data-room="<?php echo htmlspecialchars($arrival['room']); ?>">
                          <td class="py-3"><?php echo htmlspecialchars($arrival['guest_name']); ?></td>
                          <td><?php echo htmlspecialchars($arrival['room']); ?></td>
                          <td>
                            <?php echo $arrival['check_in_time'] ? date('h:i A', strtotime($arrival['check_in_time'])) : '2:00 PM'; ?>
                          </td>
                          <td>
                            <span
                              class="status-badge px-2 py-0.5 rounded-full text-xs
                          <?php echo $arrival['status'] == 'confirmed' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700'; ?>">
                              <?php echo $arrival['status']; ?>
                            </span>
                          </td>
                          <td>
                            <?php if ($arrival['status'] != 'checked-in'): ?>
                              <button onclick="openCheckinModal(this)"
                                class="text-amber-700 text-xs border border-amber-600 px-2 py-1 rounded hover:bg-amber-50">check
                                in</button>
                            <?php else: ?>
                              <span class="text-xs text-green-600"><i class="fa-regular fa-circle-check"></i> checked
                                in</span>
                            <?php endif; ?>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </tbody>
                </table>
              </div>
            </div>

            <!-- Today's departures / check-out queue -->
            <div class="bg-white rounded-2xl border border-slate-200 p-5">
              <div class="flex items-center justify-between mb-4">
                <h2 class="font-semibold text-lg flex items-center gap-2"><i
                    class="fa-regular fa-calendar-xmark text-amber-600"></i> Today's departures (check-out)</h2>
                <button onclick="batchCheckout()"
                  class="text-sm text-amber-700 border border-amber-600 px-3 py-1 rounded-lg hover:bg-amber-50">batch
                  check-out</button>
              </div>
              <div class="overflow-x-auto">
                <table class="w-full text-sm" id="checkoutTable">
                  <thead class="text-slate-400 text-xs border-b">
                    <tr>
                      <td class="pb-2">Guest</td>
                      <td>Room</td>
                      <td>Time</td>
                      <td>Status</td>
                      <td>Amount</td>
                      <td>Action</td>
                    </tr>
                  </thead>
                  <tbody class="divide-y" id="checkoutBody">
                    <?php if (empty($departures)): ?>
                      <tr>
                        <td colspan="6" class="py-4 text-center text-slate-500">No departures today</td>
                      </tr>
                    <?php else: ?>
                      <?php foreach ($departures as $departure): ?>
                        <tr data-booking-id="<?php echo $departure['id']; ?>"
                          data-guest="<?php echo htmlspecialchars($departure['guest_name']); ?>"
                          data-room="<?php echo htmlspecialchars($departure['room']); ?>"
                          data-total="<?php echo $departure['total_amount']; ?>">
                          <td class="py-3"><?php echo htmlspecialchars($departure['guest_name']); ?></td>
                          <td><?php echo htmlspecialchars($departure['room']); ?></td>
                          <td>
                            <?php echo $departure['check_out_time'] ? date('h:i A', strtotime($departure['check_out_time'])) : '11:00 AM'; ?>
                          </td>
                          <td>
                            <span
                              class="status-badge px-2 py-0.5 rounded-full text-xs
                          <?php echo $departure['status'] == 'checked-in' ? 'bg-blue-100 text-blue-700' : 'bg-green-100 text-green-700'; ?>">
                              <?php echo $departure['status']; ?>
                            </span>
                          </td>
                          <td>₱<?php echo number_format($departure['total_amount'], 2); ?></td>
                          <td>
                            <?php if ($departure['status'] != 'completed'): ?>
                              <button onclick="openCheckoutModal(this)"
                                class="text-amber-700 text-xs border border-amber-600 px-2 py-1 rounded hover:bg-amber-50">check
                                out</button>
                            <?php else: ?>
                              <span class="text-xs text-green-600"><i class="fa-regular fa-circle-check"></i> completed</span>
                            <?php endif; ?>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <!-- RIGHT: Guest requests & quick actions -->
          <div class="space-y-5">
            <!-- active guest requests -->
            <div class="bg-white rounded-2xl border border-slate-200 p-5">
              <h3 class="font-semibold flex items-center gap-2 mb-3"><i
                  class="fa-regular fa-message text-amber-600"></i> Active guest requests</h3>
              <ul class="space-y-3" id="requestsList">
                <?php if (empty($guestRequests)): ?>
                  <li class="text-center text-slate-500 py-2">No active requests</li>
                <?php else: ?>
                  <?php foreach ($guestRequests as $request): ?>
                    <li class="flex justify-between items-center border-b pb-2"
                      data-request-id="<?php echo $request['id']; ?>">
                      <div>
                        <span class="font-medium text-sm">Room
                          <?php echo htmlspecialchars($request['room_number'] ?? 'N/A'); ?></span>
                        <p class="text-xs text-slate-500"><?php echo htmlspecialchars($request['subject'] ?: 'Request'); ?>
                        </p>
                      </div>
                      <div class="flex items-center gap-2">
                        <span
                          class="px-2 py-0.5 rounded-full text-xs
                      <?php echo $request['status'] == 'pending' ? 'bg-yellow-100 text-yellow-700' : 'bg-blue-100 text-blue-700'; ?>">
                          <?php echo $request['status']; ?>
                        </span>
                        <button onclick="completeRequest(<?php echo $request['id']; ?>)"
                          class="text-xs text-green-600 hover:text-green-700">
                          <i class="fa-regular fa-circle-check"></i>
                        </button>
                      </div>
                    </li>
                  <?php endforeach; ?>
                <?php endif; ?>
              </ul>
              <button onclick="window.location.href='../view_all_request/all_guest_requests.php'"
                class="w-full border border-amber-600 text-amber-700 rounded-xl py-2 text-sm mt-4 hover:bg-amber-50">view
                all requests</button>
            </div>

            <!-- today's summary -->
            <div class="bg-white rounded-2xl border border-slate-200 p-5">
              <h3 class="font-semibold flex items-center gap-2 mb-2"><i
                  class="fa-regular fa-clipboard text-amber-600"></i> today's summary</h3>
              <div class="space-y-1 text-sm" id="todaySummary">
                <div class="flex justify-between">
                  <span>Checked in</span>
                  <span id="checkedInCount"><?php echo $todaySummary['checked_in'] ?? 0; ?></span>
                </div>
                <div class="flex justify-between">
                  <span>Checked out</span>
                  <span id="checkedOutCount"><?php echo $todaySummary['checked_out'] ?? 0; ?></span>
                </div>
                <div class="flex justify-between">
                  <span>Pending check-in</span>
                  <span id="pendingCheckin"><?php echo $todaySummary['pending_checkin'] ?? 0; ?></span>
                </div>
                <div class="flex justify-between">
                  <span>Pending check-out</span>
                  <span id="pendingCheckout"><?php echo $todaySummary['pending_checkout'] ?? 0; ?></span>
                </div>
                <div class="flex justify-between">
                  <span>Available rooms</span>
                  <span id="availableRooms"><?php echo $roomStats['available'] ?? 0; ?></span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- BOTTOM: Upcoming reservations -->
        <div class="bg-white rounded-2xl border border-slate-200 p-5">
          <div class="flex items-center justify-between mb-4">
            <h2 class="font-semibold text-lg flex items-center gap-2"><i
                class="fa-regular fa-rectangle-list text-amber-600"></i> Upcoming reservations (next 3 days)</h2>
            <a href="../upcoming_reservation/all_upcoming_reservations.php"
              class="text-sm text-amber-700 hover:underline">view all</a>
          </div>
          <div class="overflow-x-auto">
            <table class="w-full text-sm" id="upcomingTable">
              <thead class="text-slate-400 text-xs border-b">
                <tr>
                  <td class="pb-2">Guest</td>
                  <td>Room type</td>
                  <td>Check-in</td>
                  <td>Check-out</td>
                  <td>Nights</td>
                  <td>Status</td>
                  <td>Action</td>
                </tr>
              </thead>
              <tbody class="divide-y" id="upcomingBody">
                <?php if (empty($upcomingReservations)): ?>
                  <tr>
                    <td colspan="7" class="py-4 text-center text-slate-500">No upcoming reservations</td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($upcomingReservations as $res): ?>
                    <tr data-booking-id="<?php echo $res['id']; ?>"
                      data-guest="<?php echo htmlspecialchars($res['guest_name']); ?>">
                      <td class="py-2"><?php echo htmlspecialchars($res['guest_name']); ?></td>
                      <td><?php echo htmlspecialchars($res['room_name']); ?></td>
                      <td><?php echo date('M d', strtotime($res['check_in'])); ?></td>
                      <td><?php echo date('M d', strtotime($res['check_out'])); ?></td>
                      <td><?php echo $res['nights']; ?></td>
                      <td>
                        <span
                          class="px-2 py-0.5 rounded-full text-xs
                      <?php echo $res['status'] == 'confirmed' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700'; ?>">
                          <?php echo $res['status']; ?>
                        </span>
                      </td>
                      <td>
                        <?php if ($res['status'] == 'pending'): ?>
                          <button onclick="confirmReservation(<?php echo $res['id']; ?>)"
                            class="text-xs text-amber-600 hover:text-amber-700">confirm</button>
                        <?php else: ?>
                          <button
                            onclick="openNotifyModal(<?php echo $res['id']; ?>, '<?php echo htmlspecialchars($res['guest_name']); ?>')"
                            class="text-xs text-amber-600 hover:text-amber-700">notify</button>
                        <?php endif; ?>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </main>
    </div>

    <script>
      // ==================== GLOBAL VARIABLES ====================
      let currentBookingId = null;
      let currentGuestName = '';
      let currentRoomNumber = '';

      // ==================== INITIALIZATION ====================
      document.addEventListener('DOMContentLoaded', function () {
        updateDate();
      });

      function updateDate() {
        // Date is already set from PHP
      }

      // ==================== TOAST NOTIFICATION ====================
      function showToast(message, type = 'info') {
        const toast = document.getElementById('toast');
        const toastMessage = document.getElementById('toastMessage');
        const toastTime = document.getElementById('toastTime');
        const now = new Date();

        toastMessage.textContent = message;
        toastTime.textContent = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
        toast.classList.remove('hidden');

        setTimeout(() => {
          toast.classList.add('show');
        }, 10);

        setTimeout(() => {
          toast.classList.remove('show');
          setTimeout(() => {
            toast.classList.add('hidden');
          }, 300);
        }, 3000);
      }

      // ==================== MODAL FUNCTIONS ====================
      function openModal(modalId) {
        document.getElementById(modalId).classList.add('active');
      }

      function closeModal(modalId) {
        document.getElementById(modalId).classList.remove('active');
      }

      // Check-in Modal
      function openCheckinModal(button) {
        const row = button.closest('tr');
        const bookingId = row.dataset.bookingId;
        const guestName = row.dataset.guest;
        const roomNumber = row.dataset.room;

        currentBookingId = bookingId;
        currentGuestName = guestName;
        currentRoomNumber = roomNumber;

        document.getElementById('checkinBookingId').value = bookingId;
        document.getElementById('checkinGuestInfo').innerHTML = `
        <p class="font-medium">${guestName}</p>
        <p class="text-sm text-slate-500">Room: ${roomNumber}</p>
      `;
        document.getElementById('checkinRoomNumber').value = roomNumber;
        document.getElementById('checkinIdNumber').value = '';

        openModal('checkinModal');
      }

      function processCheckin() {
        const bookingId = document.getElementById('checkinBookingId').value;
        const roomNumber = document.getElementById('checkinRoomNumber').value;
        const guestCount = document.getElementById('checkinGuestCount').value;
        const idType = document.getElementById('checkinIdType').value;
        const idNumber = document.getElementById('checkinIdNumber').value;

        if (!idNumber) {
          showToast('Please enter ID number', 'error');
          return;
        }

        Swal.fire({
          title: 'Processing Check-in',
          text: 'Please wait...',
          allowOutsideClick: false,
          didOpen: () => {
            Swal.showLoading();
          }
        });

        const formData = new FormData();
        formData.append('action', 'process_checkin');
        formData.append('booking_id', bookingId);
        formData.append('room_number', roomNumber);
        formData.append('guest_count', guestCount);
        formData.append('id_type', idType);
        formData.append('id_number', idNumber);

        fetch('../../../controller/admin/post/front_desk_actions.php', {
          method: 'POST',
          body: formData
        })
          .then(response => response.json())
          .then(data => {
            Swal.close();
            if (data.success) {
              // Update UI
              const row = document.querySelector(`tr[data-booking-id="${bookingId}"]`);
              if (row) {
                const statusCell = row.querySelector('td:nth-child(4) span');
                statusCell.textContent = 'checked-in';
                statusCell.className = 'status-badge px-2 py-0.5 rounded-full text-xs bg-blue-100 text-blue-700';

                const actionCell = row.querySelector('td:last-child');
                actionCell.innerHTML = '<span class="text-xs text-green-600"><i class="fa-regular fa-circle-check"></i> checked in</span>';
              }

              // Update stats
              updateStats();

              showToast(data.message, 'success');
              closeModal('checkinModal');
            } else {
              showToast(data.message, 'error');
            }
          })
          .catch(error => {
            Swal.close();
            showToast('An error occurred', 'error');
          });
      }

      // Check-out Modal
      function openCheckoutModal(button) {
        const row = button.closest('tr');
        const bookingId = row.dataset.bookingId;
        const guestName = row.dataset.guest;
        const roomNumber = row.dataset.room;
        const totalAmount = parseFloat(row.dataset.total);

        currentBookingId = bookingId;
        currentGuestName = guestName;
        currentRoomNumber = roomNumber;

        // Random additional charges for demo
        const miniBar = Math.floor(Math.random() * 1000) + 200;
        const restaurant = Math.floor(Math.random() * 1500) + 300;
        const total = totalAmount + miniBar + restaurant;

        document.getElementById('checkoutBookingId').value = bookingId;
        document.getElementById('checkoutGuestInfo').innerHTML = `
        <p class="font-medium">${guestName}</p>
        <p class="text-sm text-slate-500">Room: ${roomNumber}</p>
      `;
        document.getElementById('checkoutRoomCharges').textContent = `₱${totalAmount.toLocaleString()}`;
        document.getElementById('checkoutMiniBar').textContent = `₱${miniBar.toLocaleString()}`;
        document.getElementById('checkoutRestaurant').textContent = `₱${restaurant.toLocaleString()}`;
        document.getElementById('checkoutTotal').textContent = `₱${total.toLocaleString()}`;

        openModal('checkoutModal');
      }

      function processCheckout() {
        const bookingId = document.getElementById('checkoutBookingId').value;
        const roomCharges = parseFloat(document.getElementById('checkoutRoomCharges').textContent.replace('₱', '').replace(',', ''));
        const miniBar = parseFloat(document.getElementById('checkoutMiniBar').textContent.replace('₱', '').replace(',', ''));
        const restaurant = parseFloat(document.getElementById('checkoutRestaurant').textContent.replace('₱', '').replace(',', ''));
        const paymentMethod = document.getElementById('checkoutPaymentMethod').value;

        Swal.fire({
          title: 'Processing Check-out',
          text: 'Please wait...',
          allowOutsideClick: false,
          didOpen: () => {
            Swal.showLoading();
          }
        });

        const formData = new FormData();
        formData.append('action', 'process_checkout');
        formData.append('booking_id', bookingId);
        formData.append('room_charges', roomCharges);
        formData.append('mini_bar', miniBar);
        formData.append('restaurant', restaurant);
        formData.append('payment_method', paymentMethod);

        fetch('../../../controller/admin/post/front_desk_actions.php', {
          method: 'POST',
          body: formData
        })
          .then(response => response.json())
          .then(data => {
            Swal.close();
            if (data.success) {
              // Update UI
              const row = document.querySelector(`tr[data-booking-id="${bookingId}"]`);
              if (row) {
                const statusCell = row.querySelector('td:nth-child(4) span');
                statusCell.textContent = 'completed';
                statusCell.className = 'status-badge px-2 py-0.5 rounded-full text-xs bg-green-100 text-green-700';

                const actionCell = row.querySelector('td:last-child');
                actionCell.innerHTML = '<span class="text-xs text-green-600"><i class="fa-regular fa-circle-check"></i> completed</span>';
              }

              // Update stats
              updateStats();

              showToast(`Check-out completed. Total: ₱${data.total.toLocaleString()}`, 'success');
              closeModal('checkoutModal');
            } else {
              showToast(data.message, 'error');
            }
          })
          .catch(error => {
            Swal.close();
            showToast('An error occurred', 'error');
          });
      }

      // Walk-in Modal
      function openWalkinModal() {
        document.getElementById('walkinName').value = '';
        document.getElementById('walkinNights').value = '1';
        document.getElementById('walkinContact').value = '';
        openModal('walkinModal');
      }

      function processWalkin() {
        const name = document.getElementById('walkinName').value.trim();
        const roomType = document.getElementById('walkinRoomType').value;
        const nights = document.getElementById('walkinNights').value;
        const adults = document.getElementById('walkinAdults').value;
        const children = document.getElementById('walkinChildren').value;
        const contact = document.getElementById('walkinContact').value.trim();

        if (!name || !contact) {
          showToast('Please fill in all required fields', 'error');
          return;
        }

        Swal.fire({
          title: 'Creating Booking',
          text: 'Please wait...',
          allowOutsideClick: false,
          didOpen: () => {
            Swal.showLoading();
          }
        });

        const formData = new FormData();
        formData.append('action', 'create_walkin');
        formData.append('guest_name', name);
        formData.append('room_type', roomType);
        formData.append('nights', nights);
        formData.append('adults', adults);
        formData.append('children', children);
        formData.append('contact', contact);

        fetch('../../../controller/admin/post/front_desk_actions.php', {
          method: 'POST',
          body: formData
        })
          .then(response => response.json())
          .then(data => {
            Swal.close();
            if (data.success) {
              // Add to check-in table
              const checkinBody = document.getElementById('checkinBody');
              const newRow = document.createElement('tr');
              newRow.setAttribute('data-booking-id', data.booking.id);
              newRow.setAttribute('data-guest', name);
              newRow.setAttribute('data-room', data.booking.room);

              newRow.innerHTML = `
            <td class="py-3">${name}</td>
            <td>${data.booking.room}</td>
            <td>${new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' })}</td>
            <td><span class="status-badge bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full text-xs">checked-in</span></td>
            <td><span class="text-xs text-green-600"><i class="fa-regular fa-circle-check"></i> checked in</span></td>
          `;

              // Remove "no arrivals" row if exists
              if (checkinBody.children.length === 1 && checkinBody.children[0].children.length === 1) {
                checkinBody.innerHTML = '';
              }

              checkinBody.appendChild(newRow);

              // Update stats
              document.getElementById('arrivalsCount').textContent = parseInt(document.getElementById('arrivalsCount').textContent) + 1;
              document.getElementById('occupiedRooms').textContent = parseInt(document.getElementById('occupiedRooms').textContent) + 1;

              showToast(data.message, 'success');
              closeModal('walkinModal');
            } else {
              showToast(data.message, 'error');
            }
          })
          .catch(error => {
            Swal.close();
            showToast('An error occurred', 'error');
          });
      }

      // Guest Requests
      function completeRequest(requestId) {
        Swal.fire({
          title: 'Complete Request',
          text: 'Mark this request as completed?',
          icon: 'question',
          showCancelButton: true,
          confirmButtonColor: '#d97706',
          cancelButtonColor: '#6b7280',
          confirmButtonText: 'Yes, complete'
        }).then((result) => {
          if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('action', 'update_request');
            formData.append('request_id', requestId);
            formData.append('status', 'done');

            fetch('../../../controller/admin/post/front_desk_actions.php', {
              method: 'POST',
              body: formData
            })
              .then(response => response.json())
              .then(data => {
                if (data.success) {
                  // Update UI
                  const requestItem = document.querySelector(`li[data-request-id="${requestId}"]`);
                  if (requestItem) {
                    const statusSpan = requestItem.querySelector('span:first-child');
                    statusSpan.textContent = 'done';
                    statusSpan.className = 'px-2 py-0.5 rounded-full text-xs bg-green-100 text-green-700';

                    const completeBtn = requestItem.querySelector('button');
                    completeBtn.disabled = true;
                    completeBtn.classList.add('opacity-50', 'cursor-not-allowed');
                  }

                  // Update request count
                  const requestsCount = document.getElementById('guestRequests');
                  requestsCount.textContent = parseInt(requestsCount.textContent) - 1;

                  showToast('Request marked as completed', 'success');
                }
              });
          }
        });
      }

      // Upcoming Reservations
      function confirmReservation(bookingId) {
        Swal.fire({
          title: 'Confirm Reservation',
          text: 'Confirm this reservation?',
          icon: 'question',
          showCancelButton: true,
          confirmButtonColor: '#d97706',
          cancelButtonColor: '#6b7280',
          confirmButtonText: 'Yes, confirm'
        }).then((result) => {
          if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('action', 'confirm_reservation');
            formData.append('booking_id', bookingId);

            fetch('../../../controller/admin/post/front_desk_actions.php', {
              method: 'POST',
              body: formData
            })
              .then(response => response.json())
              .then(data => {
                if (data.success) {
                  // Update UI
                  const row = document.querySelector(`tr[data-booking-id="${bookingId}"]`);
                  if (row) {
                    const statusCell = row.querySelector('td:nth-child(6) span');
                    statusCell.textContent = 'confirmed';
                    statusCell.className = 'px-2 py-0.5 rounded-full text-xs bg-green-100 text-green-700';

                    const actionCell = row.querySelector('td:last-child');
                    actionCell.innerHTML = '<button onclick="openNotifyModal(' + bookingId + ', \'' + row.dataset.guest + '\')" class="text-xs text-amber-600 hover:text-amber-700">notify</button>';
                  }

                  showToast('Reservation confirmed', 'success');
                }
              });
          }
        });
      }

      function openNotifyModal(bookingId, guestName) {
        document.getElementById('notifyBookingId').value = bookingId;
        document.getElementById('notifyGuestInfo').innerHTML = `<p class="font-medium">${guestName}</p>`;
        document.getElementById('notifyMessage').value = '';
        openModal('notifyModal');
      }

      function sendNotification() {
        const bookingId = document.getElementById('notifyBookingId').value;
        const message = document.getElementById('notifyMessage').value.trim();

        Swal.fire({
          title: 'Sending...',
          text: 'Please wait',
          allowOutsideClick: false,
          didOpen: () => {
            Swal.showLoading();
          }
        });

        const formData = new FormData();
        formData.append('action', 'notify_guest');
        formData.append('booking_id', bookingId);
        formData.append('message', message);

        fetch('../../../controller/admin/post/front_desk_actions.php', {
          method: 'POST',
          body: formData
        })
          .then(response => response.json())
          .then(data => {
            Swal.close();
            if (data.success) {
              showToast('Notification sent to guest', 'success');
              closeModal('notifyModal');
            } else {
              showToast(data.message, 'error');
            }
          });
      }

      // Batch Checkout
      function batchCheckout() {
        const pendingRows = document.querySelectorAll('#checkoutBody tr[data-status="checked-in"]');

        if (pendingRows.length === 0) {
          showToast('No pending check-outs', 'info');
          return;
        }

        Swal.fire({
          title: 'Batch Check-out',
          text: `Process check-out for ${pendingRows.length} guests?`,
          icon: 'question',
          showCancelButton: true,
          confirmButtonColor: '#d97706',
          cancelButtonColor: '#6b7280',
          confirmButtonText: 'Yes, process all'
        }).then((result) => {
          if (result.isConfirmed) {
            // Process each pending checkout
            pendingRows.forEach(row => {
              const button = row.querySelector('button');
              if (button) {
                openCheckoutModal(button);
              }
            });
          }
        });
      }

      // Update Statistics
      function updateStats() {
        // This would ideally fetch fresh stats from server
        // For now, just increment/decrement locally
        const checkedIn = document.getElementById('checkedInCount');
        checkedIn.textContent = parseInt(checkedIn.textContent) + 1;

        const pendingCheckin = document.getElementById('pendingCheckin');
        pendingCheckin.textContent = Math.max(0, parseInt(pendingCheckin.textContent) - 1);
      }

      // View Notifications
      function viewNotifications() {
        window.location.href = '../notifications.php';
      }
    </script>
  </body>

</html>