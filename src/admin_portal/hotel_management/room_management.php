<?php
/**
 * View - Admin Room Management
 */
require_once '../../../controller/admin/get/room_management.php';

// Set current page for navigation
$current_page = 'room_management';
?>
<!DOCTYPE html>
<html lang="en">

  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin · Room Management</title>
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
    </style>
  </head>

  <body class="bg-white font-sans antialiased">

    <!-- Toast Notification -->
    <div id="toast" class="toast bg-white rounded-xl p-4 min-w-[300px] shadow-lg border border-amber-200 hidden">
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

    <!-- APP CONTAINER -->
    <div class="min-h-screen flex flex-col lg:flex-row">

      <!-- ========== SIDEBAR ========== -->
      <?php require_once '../components/admin_nav.php'; ?>

      <!-- ========== MAIN CONTENT ========== -->
      <main class="flex-1 p-5 lg:p-8 overflow-y-auto bg-white">

        <!-- header -->
        <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
          <div>
            <h1 class="text-2xl lg:text-3xl font-light text-slate-800">Room Management</h1>
            <p class="text-sm text-slate-500 mt-0.5">view and manage all rooms, statuses, and maintenance</p>
          </div>
          <div class="flex gap-3 text-sm">
            <span class="bg-white border rounded-full px-4 py-2 flex items-center gap-2 shadow-sm">
              <i class="fa-regular fa-calendar text-slate-400"></i>
              <span id="currentDate">
                <?php echo $today; ?>
              </span>
            </span>
            <span class="bg-white border rounded-full px-4 py-2 shadow-sm cursor-pointer hover:bg-slate-50 relative"
              id="notificationBell">
              <i class="fa-regular fa-bell"></i>
              <?php if ($unread_count > 0): ?>
                <span
                  class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-4 h-4 flex items-center justify-center">
                  <?php echo $unread_count; ?>
                </span>
              <?php endif; ?>
            </span>
          </div>
        </div>

        <!-- ROOM STATISTICS CARDS -->
        <div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
          <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs text-slate-500">Total rooms</p>
            <p class="text-2xl font-semibold" id="totalRooms">
              <?php echo $stats['total']; ?>
            </p>
          </div>
          <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs text-slate-500">Available</p>
            <p class="text-2xl font-semibold text-green-600" id="availableRooms">
              <?php echo $stats['available']; ?>
            </p>
          </div>
          <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs text-slate-500">Occupied</p>
            <p class="text-2xl font-semibold text-blue-600" id="occupiedRooms">
              <?php echo $stats['occupied']; ?>
            </p>
          </div>
          <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs text-slate-500">Dirty / cleaning</p>
            <p class="text-2xl font-semibold text-amber-600" id="dirtyRooms">0</p>
          </div>
          <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs text-slate-500">Out of order</p>
            <p class="text-2xl font-semibold text-rose-600" id="outOfOrderRooms">
              <?php echo count($maintenanceItems); ?>
            </p>
          </div>
        </div>

        <!-- FILTER AND SEARCH -->
        <div
          class="bg-white rounded-2xl border border-slate-200 p-4 mb-6 flex flex-wrap gap-3 items-center justify-between">
          <div class="flex gap-2 flex-wrap" id="filterButtons">
            <button
              class="filter-btn <?php echo $statusFilter == 'all' ? 'bg-amber-600 text-white' : 'border border-slate-200 hover:bg-slate-50'; ?> px-4 py-2 rounded-xl text-sm"
              data-filter="all">all rooms</button>
            <button
              class="filter-btn <?php echo $statusFilter == 'available' ? 'bg-amber-600 text-white' : 'border border-slate-200 hover:bg-slate-50'; ?> px-4 py-2 rounded-xl text-sm"
              data-filter="available">available</button>
            <button
              class="filter-btn <?php echo $statusFilter == 'occupied' ? 'bg-amber-600 text-white' : 'border border-slate-200 hover:bg-slate-50'; ?> px-4 py-2 rounded-xl text-sm"
              data-filter="occupied">occupied</button>
            <button
              class="filter-btn <?php echo $statusFilter == 'dirty' ? 'bg-amber-600 text-white' : 'border border-slate-200 hover:bg-slate-50'; ?> px-4 py-2 rounded-xl text-sm"
              data-filter="dirty">dirty</button>
            <button
              class="filter-btn <?php echo $statusFilter == 'out of order' ? 'bg-amber-600 text-white' : 'border border-slate-200 hover:bg-slate-50'; ?> px-4 py-2 rounded-xl text-sm"
              data-filter="out of order">maintenance</button>
          </div>
          <div class="relative">
            <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
            <input type="text" id="searchInput" placeholder="search room..."
              value="<?php echo htmlspecialchars($searchFilter); ?>"
              class="border border-slate-200 rounded-xl pl-9 pr-4 py-2 text-sm w-64 focus:ring-1 focus:ring-amber-500 outline-none">
          </div>
        </div>

        <!-- ROOMS TABLE -->
        <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden mb-8">
          <div class="overflow-x-auto">
            <table class="w-full text-sm" id="roomsTable">
              <thead class="bg-slate-50 text-slate-500 text-xs border-b">
                <tr>
                  <td class="p-4">Room</td>
                  <td class="p-4">Type</td>
                  <td class="p-4">Price</td>
                  <td class="p-4">Status</td>
                  <td class="p-4">Housekeeping</td>
                  <td class="p-4">Guest</td>
                  <td class="p-4">Actions</td>
                </tr>
              </thead>
              <tbody class="divide-y" id="tableBody">
                <?php foreach ($rooms as $room): ?>
                  <tr>
                    <td class="p-4 font-medium">
                      <?php echo htmlspecialchars($room['room_number']); ?>
                    </td>
                    <td class="p-4">
                      <?php echo htmlspecialchars($room['type']); ?>
                    </td>
                    <td class="p-4">₱
                      <?php echo number_format($room['price']); ?>
                    </td>
                    <td class="p-4">
                      <span class="status-badge px-2 py-1 rounded-full text-xs font-medium
                    <?php
                    switch ($room['status']) {
                      case 'available':
                        echo 'bg-green-100 text-green-700';
                        break;
                      case 'occupied':
                        echo 'bg-blue-100 text-blue-700';
                        break;
                      default:
                        echo 'bg-slate-100 text-slate-700';
                    }
                    ?>">
                        <?php echo $room['status']; ?>
                      </span>
                    </td>
                    <td class="p-4">
                      <span class="px-2 py-1 rounded-full text-xs font-medium
                    <?php
                    switch ($room['housekeeping']) {
                      case 'clean':
                        echo 'bg-green-100 text-green-700';
                        break;
                      case 'pending':
                        echo 'bg-amber-100 text-amber-700';
                        break;
                      case 'maintenance':
                        echo 'bg-rose-100 text-rose-700';
                        break;
                      default:
                        echo 'bg-slate-100 text-slate-700';
                    }
                    ?>">
                        <?php echo $room['housekeeping']; ?>
                      </span>
                    </td>
                    <td class="p-4 <?php echo $room['guest'] ? '' : 'text-slate-400'; ?>">
                      <?php echo $room['guest'] ? htmlspecialchars($room['guest']) : '—'; ?>
                    </td>
                    <td class="p-4">
                      <div class="flex gap-2">
                        <button onclick="editRoom('<?php echo $room['room_number']; ?>')"
                          class="text-amber-700 hover:underline text-xs" title="Edit">
                          <i class="fa-regular fa-pen-to-square"></i>
                        </button>
                        <button onclick="showRoomDetails('<?php echo $room['room_number']; ?>')"
                          class="text-blue-600 hover:underline text-xs" title="Details">
                          <i class="fa-regular fa-eye"></i>
                        </button>
                        <?php if ($room['status'] === 'available'): ?>
                          <button onclick="assignRoom('<?php echo $room['room_number']; ?>')"
                            class="text-green-600 hover:underline text-xs" title="Assign Guest">
                            <i class="fa-regular fa-user-plus"></i>
                          </button>
                        <?php endif; ?>
                        <?php if ($room['status'] !== 'out of order'): ?>
                          <button onclick="reportMaintenance('<?php echo $room['room_number']; ?>')"
                            class="text-rose-600 hover:underline text-xs" title="Report Maintenance">
                            <i class="fa-solid fa-wrench"></i>
                          </button>
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
              <?php echo (($currentPage - 1) * $limit + 1); ?>-
              <?php echo min($currentPage * $limit, $totalRooms); ?> of
              <?php echo $totalRooms; ?> rooms
            </span>
            <div class="flex gap-2" id="paginationControls">
              <button
                class="border border-slate-200 px-3 py-1 rounded-lg text-sm <?php echo $currentPage <= 1 ? 'opacity-50 cursor-not-allowed' : ''; ?>"
                id="prevPage" <?php echo $currentPage <= 1 ? 'disabled' : ''; ?> onclick="changePage(
                <?php echo $currentPage - 1; ?>)">
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
                id="nextPage" <?php echo $currentPage >= $totalPages ? 'disabled' : ''; ?> onclick="changePage(
                <?php echo $currentPage + 1; ?>)">
                Next
              </button>
            </div>
          </div>
        </div>

        <!-- BOTTOM: MAINTENANCE SCHEDULE & QUICK ACTIONS -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <!-- upcoming maintenance -->
          <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 p-5">
            <h2 class="font-semibold text-lg flex items-center gap-2 mb-3">
              <i class="fa-solid fa-wrench text-amber-600"></i> Scheduled maintenance
            </h2>
            <div class="space-y-3" id="maintenanceList">
              <?php if (empty($maintenanceItems)): ?>
                <p class="text-sm text-slate-500 text-center py-4">No maintenance scheduled</p>
              <?php else: ?>
                <?php foreach ($maintenanceItems as $item): ?>
                  <div class="flex justify-between items-center border-b pb-2">
                    <div>
                      <span class="font-medium">Room
                        <?php echo htmlspecialchars($item['room_number']); ?>
                      </span>
                      <p class="text-xs text-slate-500">
                        <?php echo htmlspecialchars($item['notes']); ?> ·
                        <?php echo date('M d, h:i A', strtotime($item['reported_at'])); ?>
                      </p>
                    </div>
                    <span class="bg-yellow-100 text-yellow-700 text-xs px-2 py-0.5 rounded-full">pending</span>
                  </div>
                <?php endforeach; ?>
              <?php endif; ?>
            </div>
          </div>

          <!-- quick actions -->
          <div class="bg-amber-50 border border-amber-200 rounded-2xl p-5">
            <h3 class="font-semibold flex items-center gap-2 mb-3">
              <i class="fa-regular fa-bolt text-amber-600"></i> quick actions
            </h3>
            <div class="grid grid-cols-2 gap-2">
              <button class="bg-white border border-slate-200 rounded-xl p-3 text-sm hover:bg-amber-100"
                onclick="addNewRoom()">
                add new room
              </button>
              <button class="bg-white border border-slate-200 rounded-xl p-3 text-sm hover:bg-amber-100"
                onclick="bulkUpdate()">
                bulk update
              </button>
              <button class="bg-white border border-slate-200 rounded-xl p-3 text-sm hover:bg-amber-100"
                onclick="viewMaintenanceLog()">
                maintenance log
              </button>
              <button class="bg-white border border-slate-200 rounded-xl p-3 text-sm hover:bg-amber-100"
                onclick="manageRoomTypes()">
                room types
              </button>
            </div>
          </div>
        </div>

      </main>
    </div>

    <!-- Room Details Modal -->
    <div id="roomModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
      <div class="bg-white rounded-2xl p-6 max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-xl font-semibold">Room Details</h3>
          <button onclick="closeRoomModal()" class="text-slate-400 hover:text-slate-600">
            <i class="fa-solid fa-xmark text-2xl"></i>
          </button>
        </div>
        <div id="roomDetails" class="space-y-4">
          <!-- Details will be populated by JavaScript -->
        </div>
        <div class="mt-6 flex gap-3">
          <button onclick="closeRoomModal()"
            class="flex-1 border border-slate-200 px-4 py-2 rounded-xl hover:bg-slate-50">Close</button>
          <button id="modalActionBtn" onclick="editCurrentRoom()"
            class="flex-1 bg-amber-600 text-white px-4 py-2 rounded-xl hover:bg-amber-700">Edit Room</button>
        </div>
      </div>
    </div>

    <!-- Assign Room Modal -->
    <div id="assignModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
      <div class="bg-white rounded-2xl p-6 max-w-md w-full mx-4">
        <h3 class="text-lg font-semibold mb-4">Assign Room to Guest</h3>
        <input type="hidden" id="assignRoomNumber">
        <div class="space-y-4">
          <div>
            <label class="block text-sm text-slate-600 mb-1">Guest Name</label>
            <input type="text" id="assignGuestName" class="w-full border rounded-xl p-2" placeholder="Full name">
          </div>
          <div>
            <label class="block text-sm text-slate-600 mb-1">Check-in Date</label>
            <input type="date" id="assignCheckIn" class="w-full border rounded-xl p-2"
              min="<?php echo date('Y-m-d'); ?>">
          </div>
          <div>
            <label class="block text-sm text-slate-600 mb-1">Check-out Date</label>
            <input type="date" id="assignCheckOut" class="w-full border rounded-xl p-2"
              min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">
          </div>
        </div>
        <div class="flex gap-3 mt-6">
          <button onclick="closeAssignModal()"
            class="flex-1 border border-slate-200 px-4 py-2 rounded-xl hover:bg-slate-50">Cancel</button>
          <button onclick="submitAssign()"
            class="flex-1 bg-amber-600 text-white px-4 py-2 rounded-xl hover:bg-amber-700">Assign</button>
        </div>
      </div>
    </div>

    <!-- Maintenance Modal -->
    <div id="maintenanceModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
      <div class="bg-white rounded-2xl p-6 max-w-md w-full mx-4">
        <h3 class="text-lg font-semibold mb-4">Report Maintenance Issue</h3>
        <input type="hidden" id="maintenanceRoomNumber">
        <div class="space-y-4">
          <div>
            <label class="block text-sm text-slate-600 mb-1">Issue Description</label>
            <textarea id="maintenanceIssue" class="w-full border rounded-xl p-2" rows="3"
              placeholder="Describe the issue..."></textarea>
          </div>
          <div>
            <label class="block text-sm text-slate-600 mb-1">Priority</label>
            <select id="maintenancePriority" class="w-full border rounded-xl p-2">
              <option value="low">Low</option>
              <option value="medium" selected>Medium</option>
              <option value="high">High</option>
            </select>
          </div>
          <div>
            <label class="block text-sm text-slate-600 mb-1">Schedule Date (optional)</label>
            <input type="date" id="maintenanceDate" class="w-full border rounded-xl p-2"
              min="<?php echo date('Y-m-d'); ?>">
          </div>
        </div>
        <div class="flex gap-3 mt-6">
          <button onclick="closeMaintenanceModal()"
            class="flex-1 border border-slate-200 px-4 py-2 rounded-xl hover:bg-slate-50">Cancel</button>
          <button onclick="submitMaintenance()"
            class="flex-1 bg-amber-600 text-white px-4 py-2 rounded-xl hover:bg-amber-700">Report</button>
        </div>
      </div>
    </div>

    <!-- Edit Room Modal -->
    <div id="editRoomModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
      <div class="bg-white rounded-2xl p-6 max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-xl font-semibold">Edit Room</h3>
          <button onclick="closeEditRoomModal()" class="text-slate-400 hover:text-slate-600">
            <i class="fa-solid fa-xmark text-2xl"></i>
          </button>
        </div>

        <form id="editRoomForm" onsubmit="submitEditRoom(event)">
          <input type="hidden" name="action" value="edit_room">
          <input type="hidden" name="room_id" id="edit_room_id">

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
              <label class="block text-sm text-slate-600 mb-1">Room Number</label>
              <input type="text" id="edit_room_number" class="w-full border rounded-xl p-2 bg-slate-50" readonly
                disabled>
            </div>
            <div>
              <label class="block text-sm text-slate-600 mb-1">Room Type <span class="text-red-500">*</span></label>
              <input type="text" id="edit_room_name" name="name" required class="w-full border rounded-xl p-2">
            </div>
            <div>
              <label class="block text-sm text-slate-600 mb-1">Price per Night <span
                  class="text-red-500">*</span></label>
              <input type="number" id="edit_price" name="price" required min="0" step="0.01"
                class="w-full border rounded-xl p-2">
            </div>
            <div>
              <label class="block text-sm text-slate-600 mb-1">Max Occupancy</label>
              <input type="number" id="edit_max_occupancy" name="max_occupancy" min="1" max="10"
                class="w-full border rounded-xl p-2">
            </div>
            <div>
              <label class="block text-sm text-slate-600 mb-1">Beds</label>
              <input type="text" id="edit_beds" name="beds" class="w-full border rounded-xl p-2"
                placeholder="e.g., 1 King Bed">
            </div>
            <div>
              <label class="block text-sm text-slate-600 mb-1">View</label>
              <input type="text" id="edit_view" name="view" class="w-full border rounded-xl p-2"
                placeholder="e.g., Ocean View">
            </div>
            <div class="md:col-span-2">
              <label class="block text-sm text-slate-600 mb-1">Amenities</label>
              <textarea id="edit_amenities" name="amenities" rows="2" class="w-full border rounded-xl p-2"
                placeholder="WiFi, TV, Aircon, etc."></textarea>
            </div>
            <div class="md:col-span-2">
              <label class="flex items-center gap-2">
                <input type="checkbox" id="edit_is_available" name="is_available" class="rounded text-amber-600">
                <span class="text-sm text-slate-600">Room is available for booking</span>
              </label>
            </div>
          </div>

          <div class="flex gap-3">
            <button type="button" onclick="closeEditRoomModal()"
              class="flex-1 border border-slate-200 px-4 py-2 rounded-xl hover:bg-slate-50">Cancel</button>
            <button type="submit" class="flex-1 bg-amber-600 text-white px-4 py-2 rounded-xl hover:bg-amber-700">Update
              Room</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Add New Room Modal -->
    <div id="addRoomModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
      <div class="bg-white rounded-2xl p-6 max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-xl font-semibold">Add New Room</h3>
          <button onclick="closeAddRoomModal()" class="text-slate-400 hover:text-slate-600">
            <i class="fa-solid fa-xmark text-2xl"></i>
          </button>
        </div>

        <form id="addRoomForm" onsubmit="submitAddRoom(event)">
          <input type="hidden" name="action" value="add_new_room">

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
              <label class="block text-sm text-slate-600 mb-1">Room Number <span class="text-red-500">*</span></label>
              <input type="text" name="room_id" required class="w-full border rounded-xl p-2" placeholder="e.g., 301">
            </div>
            <div>
              <label class="block text-sm text-slate-600 mb-1">Room Type <span class="text-red-500">*</span></label>
              <input type="text" name="name" required class="w-full border rounded-xl p-2"
                placeholder="e.g., Deluxe Twin">
            </div>
            <div>
              <label class="block text-sm text-slate-600 mb-1">Price per Night <span
                  class="text-red-500">*</span></label>
              <input type="number" name="price" required min="0" step="0.01" class="w-full border rounded-xl p-2">
            </div>
            <div>
              <label class="block text-sm text-slate-600 mb-1">Max Occupancy</label>
              <input type="number" name="max_occupancy" min="1" max="10" value="2" class="w-full border rounded-xl p-2">
            </div>
            <div>
              <label class="block text-sm text-slate-600 mb-1">Beds</label>
              <input type="text" name="beds" class="w-full border rounded-xl p-2" placeholder="e.g., 1 King Bed">
            </div>
            <div>
              <label class="block text-sm text-slate-600 mb-1">View</label>
              <input type="text" name="view" class="w-full border rounded-xl p-2" placeholder="e.g., Ocean View">
            </div>
            <div class="md:col-span-2">
              <label class="block text-sm text-slate-600 mb-1">Amenities</label>
              <textarea name="amenities" rows="2" class="w-full border rounded-xl p-2"
                placeholder="WiFi, TV, Aircon, etc."></textarea>
            </div>
          </div>

          <div class="flex gap-3">
            <button type="button" onclick="closeAddRoomModal()"
              class="flex-1 border border-slate-200 px-4 py-2 rounded-xl hover:bg-slate-50">Cancel</button>
            <button type="submit" class="flex-1 bg-amber-600 text-white px-4 py-2 rounded-xl hover:bg-amber-700">Add
              Room</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Bulk Update Modal -->
    <div id="bulkModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
      <div class="bg-white rounded-2xl p-6 max-w-md w-full mx-4">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-xl font-semibold">Bulk Update Rooms</h3>
          <button onclick="closeBulkModal()" class="text-slate-400 hover:text-slate-600">
            <i class="fa-solid fa-xmark text-2xl"></i>
          </button>
        </div>

        <div class="mb-4">
          <p class="text-sm text-slate-600 mb-2">Select action:</p>
          <select id="bulkAction" class="w-full border rounded-xl p-2 mb-4">
            <option value="set_available">Mark as Available</option>
            <option value="set_unavailable">Mark as Unavailable</option>
            <option value="update_price">Update Price</option>
          </select>

          <div id="bulkPriceField" class="hidden">
            <label class="block text-sm text-slate-600 mb-1">New Price (₱)</label>
            <input type="number" id="bulkPrice" class="w-full border rounded-xl p-2" min="0" step="0.01">
          </div>

          <div class="mt-4">
            <p class="text-sm font-medium">Selected Rooms:</p>
            <div id="selectedRoomsList" class="max-h-32 overflow-y-auto border rounded-xl p-2 mt-2 text-sm">
              <!-- Will be populated by JavaScript -->
            </div>
          </div>
        </div>

        <div class="flex gap-3">
          <button onclick="closeBulkModal()"
            class="flex-1 border border-slate-200 px-4 py-2 rounded-xl hover:bg-slate-50">Cancel</button>
          <button onclick="submitBulkUpdate()"
            class="flex-1 bg-amber-600 text-white px-4 py-2 rounded-xl hover:bg-amber-700">Apply</button>
        </div>
      </div>
    </div>

    <!-- Maintenance Log Modal -->
    <div id="maintenanceLogModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
      <div class="bg-white rounded-2xl p-6 max-w-3xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-xl font-semibold">Maintenance Log</h3>
          <button onclick="closeMaintenanceLogModal()" class="text-slate-400 hover:text-slate-600">
            <i class="fa-solid fa-xmark text-2xl"></i>
          </button>
        </div>

        <div id="maintenanceLogContent" class="space-y-3">
          <!-- Will be populated by JavaScript -->
        </div>
      </div>
    </div>

    <!-- Room Types Modal -->
    <div id="roomTypesModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
      <div class="bg-white rounded-2xl p-6 max-w-2xl w-full mx-4">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-xl font-semibold">Room Types Statistics</h3>
          <button onclick="closeRoomTypesModal()" class="text-slate-400 hover:text-slate-600">
            <i class="fa-solid fa-xmark text-2xl"></i>
          </button>
        </div>

        <div id="roomTypesContent" class="space-y-3">
          <!-- Will be populated by JavaScript -->
        </div>
      </div>
    </div>

    <script>
      // Pass PHP data to JavaScript
      const totalPages = <?php echo $totalPages; ?>;
      const currentPage = <?php echo $currentPage; ?>;
      const currentFilter = '<?php echo $statusFilter; ?>';
      const currentSearch = '<?php echo $searchFilter; ?>';

      // Global variables for bulk update
      let selectedRooms = [];

      // Toast notification
      function showToast(message, type = 'info') {
        const toast = document.getElementById('toast');
        const toastMessage = document.getElementById('toastMessage');
        const toastTime = document.getElementById('toastTime');
        const now = new Date();

        // Set icon based on type
        const icon = toast.querySelector('.h-8.w-8 i');
        if (icon) {
          icon.className = type === 'success' ? 'fa-regular fa-circle-check' :
            type === 'error' ? 'fa-regular fa-circle-exclamation' :
              type === 'warning' ? 'fa-regular fa-triangle-exclamation' :
                'fa-regular fa-bell';
        }

        toastMessage.textContent = message;
        toastTime.textContent = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
        toast.classList.remove('hidden');

        setTimeout(() => { toast.classList.add('show'); }, 10);
        setTimeout(() => {
          toast.classList.remove('show');
          setTimeout(() => { toast.classList.add('hidden'); }, 300);
        }, 3000);
      }

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

      // Room details
      function showRoomDetails(roomNumber) {
        Swal.fire({
          title: 'Loading...',
          text: 'Please wait',
          allowOutsideClick: false,
          didOpen: () => { Swal.showLoading(); }
        });

        const formData = new FormData();
        formData.append('action', 'get_room_details');
        formData.append('room_id', roomNumber);

        fetch('../../../controller/admin/post/room_actions.php', {
          method: 'POST',
          body: formData
        })
          .then(response => response.json())
          .then(data => {
            Swal.close();
            if (data.success) {
              const r = data.room;
              const details = document.getElementById('roomDetails');

              const statusColor = r.is_available == 1 ? 'bg-green-100 text-green-700' : 'bg-blue-100 text-blue-700';
              const statusText = r.is_available == 1 ? 'Available' : 'Occupied';

              details.innerHTML = `
                <div class="grid grid-cols-2 gap-4">
                  <div>
                    <p class="text-xs text-slate-500">Room Number</p>
                    <p class="font-medium text-lg">${r.id}</p>
                  </div>
                  <div>
                    <p class="text-xs text-slate-500">Status</p>
                    <p><span class="${statusColor} px-2 py-1 rounded-full text-xs font-medium">${statusText}</span></p>
                  </div>
                  <div>
                    <p class="text-xs text-slate-500">Room Type</p>
                    <p class="font-medium">${r.name || 'Standard'}</p>
                  </div>
                  <div>
                    <p class="text-xs text-slate-500">Price</p>
                    <p class="font-medium text-amber-700">₱${parseFloat(r.price).toLocaleString()}/night</p>
                  </div>
                  <div>
                    <p class="text-xs text-slate-500">Max Occupancy</p>
                    <p class="font-medium">${r.max_occupancy || 2} guests</p>
                  </div>
                  <div>
                    <p class="text-xs text-slate-500">Beds</p>
                    <p class="font-medium">${r.beds || 'Standard'}</p>
                  </div>
                  <div>
                    <p class="text-xs text-slate-500">View</p>
                    <p class="font-medium">${r.view || 'City View'}</p>
                  </div>
                </div>
                <div class="border-t pt-4">
                  <p class="text-xs text-slate-500 mb-2">Amenities</p>
                  <p class="text-sm bg-slate-50 p-3 rounded-lg">${r.amenities || 'No amenities listed'}</p>
                </div>
                ${r.current_guest ? `
                  <div class="border-t pt-4 bg-amber-50 p-3 rounded-lg">
                    <p class="text-xs text-amber-700 font-medium mb-2">Current Guest</p>
                    <p class="font-medium">${r.current_guest}</p>
                    <p class="text-xs text-slate-600 mt-1">
                      <i class="fa-regular fa-calendar mr-1"></i>
                      Check-in: ${r.check_in} | Check-out: ${r.check_out}
                    </p>
                    <p class="text-xs text-slate-500 mt-1">Booking: ${r.booking_reference || 'N/A'}</p>
                  </div>
                ` : ''}
              `;

              document.getElementById('roomModal').classList.remove('hidden');
              document.getElementById('roomModal').classList.add('flex');
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

      function closeRoomModal() {
        document.getElementById('roomModal').classList.add('hidden');
        document.getElementById('roomModal').classList.remove('flex');
      }

      function editCurrentRoom() {
        const roomNumber = document.getElementById('roomDetails').querySelector('.font-medium.text-lg').textContent;
        closeRoomModal();
        editRoom(roomNumber);
      }

      // Edit Room functions
      function editRoom(roomNumber) {
        Swal.fire({
          title: 'Loading...',
          text: 'Please wait',
          allowOutsideClick: false,
          didOpen: () => { Swal.showLoading(); }
        });

        const formData = new FormData();
        formData.append('action', 'get_room_details');
        formData.append('room_id', roomNumber);

        fetch('../../../controller/admin/post/room_actions.php', {
          method: 'POST',
          body: formData
        })
          .then(response => response.json())
          .then(data => {
            Swal.close();
            if (data.success) {
              const r = data.room;

              // Populate edit form
              document.getElementById('edit_room_id').value = r.id;
              document.getElementById('edit_room_number').value = r.id;
              document.getElementById('edit_room_name').value = r.name || '';
              document.getElementById('edit_price').value = r.price || 0;
              document.getElementById('edit_max_occupancy').value = r.max_occupancy || 2;
              document.getElementById('edit_beds').value = r.beds || '';
              document.getElementById('edit_view').value = r.view || '';
              document.getElementById('edit_amenities').value = r.amenities || '';
              document.getElementById('edit_is_available').checked = r.is_available == 1;

              // Show modal
              document.getElementById('editRoomModal').classList.remove('hidden');
              document.getElementById('editRoomModal').classList.add('flex');
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

      function closeEditRoomModal() {
        document.getElementById('editRoomModal').classList.add('hidden');
        document.getElementById('editRoomModal').classList.remove('flex');
      }

      function submitEditRoom(event) {
        event.preventDefault();

        const formData = new FormData(document.getElementById('editRoomForm'));

        Swal.fire({
          title: 'Updating...',
          text: 'Please wait',
          allowOutsideClick: false,
          didOpen: () => { Swal.showLoading(); }
        });

        fetch('../../../controller/admin/post/room_actions.php', {
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

      // Add New Room functions
      function addNewRoom() {
        document.getElementById('addRoomModal').classList.remove('hidden');
        document.getElementById('addRoomModal').classList.add('flex');
      }

      function closeAddRoomModal() {
        document.getElementById('addRoomModal').classList.add('hidden');
        document.getElementById('addRoomModal').classList.remove('flex');
        document.getElementById('addRoomForm').reset();
      }

      function submitAddRoom(event) {
        event.preventDefault();

        const formData = new FormData(document.getElementById('addRoomForm'));

        Swal.fire({
          title: 'Adding Room...',
          text: 'Please wait',
          allowOutsideClick: false,
          didOpen: () => { Swal.showLoading(); }
        });

        fetch('../../../controller/admin/post/room_actions.php', {
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
                  <p class="text-sm mt-2"><strong>Room Number:</strong> ${data.room_id}</p>
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
          });
      }

      // Assign room
      function assignRoom(roomNumber) {
        document.getElementById('assignRoomNumber').value = roomNumber;
        document.getElementById('assignModal').classList.remove('hidden');
        document.getElementById('assignModal').classList.add('flex');

        // Set default dates
        const today = new Date();
        const tomorrow = new Date(today);
        tomorrow.setDate(tomorrow.getDate() + 1);
        const nextWeek = new Date(today);
        nextWeek.setDate(nextWeek.getDate() + 3);

        document.getElementById('assignCheckIn').value = tomorrow.toISOString().split('T')[0];
        document.getElementById('assignCheckOut').value = nextWeek.toISOString().split('T')[0];
      }

      function closeAssignModal() {
        document.getElementById('assignModal').classList.add('hidden');
        document.getElementById('assignModal').classList.remove('flex');
        document.getElementById('assignGuestName').value = '';
      }

      function submitAssign() {
        const roomNumber = document.getElementById('assignRoomNumber').value;
        const guestName = document.getElementById('assignGuestName').value.trim();
        const checkIn = document.getElementById('assignCheckIn').value;
        const checkOut = document.getElementById('assignCheckOut').value;
        const adults = document.getElementById('assignAdults')?.value || 1;
        const children = document.getElementById('assignChildren')?.value || 0;

        if (!guestName || !checkIn || !checkOut) {
          showToast('Please fill in all fields', 'error');
          return;
        }

        Swal.fire({
          title: 'Assigning Room...',
          text: 'Please wait',
          allowOutsideClick: false,
          didOpen: () => { Swal.showLoading(); }
        });

        const formData = new FormData();
        formData.append('action', 'assign_room');
        formData.append('room_id', roomNumber);
        formData.append('guest_name', guestName);
        formData.append('check_in', checkIn);
        formData.append('check_out', checkOut);
        formData.append('adults', adults);
        formData.append('children', children);

        fetch('../../../controller/admin/post/room_actions.php', {
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
                  <p class="text-sm mt-2"><strong>Booking Reference:</strong> ${data.booking_reference}</p>
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
          });

        closeAssignModal();
      }

      // Maintenance
      function reportMaintenance(roomNumber) {
        document.getElementById('maintenanceRoomNumber').value = roomNumber;
        document.getElementById('maintenanceModal').classList.remove('hidden');
        document.getElementById('maintenanceModal').classList.add('flex');

        // Set default date to tomorrow
        const tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);
        document.getElementById('maintenanceDate').value = tomorrow.toISOString().split('T')[0];
      }

      function closeMaintenanceModal() {
        document.getElementById('maintenanceModal').classList.add('hidden');
        document.getElementById('maintenanceModal').classList.remove('flex');
        document.getElementById('maintenanceIssue').value = '';
      }

      function submitMaintenance() {
        const roomNumber = document.getElementById('maintenanceRoomNumber').value;
        const issue = document.getElementById('maintenanceIssue').value.trim();
        const priority = document.getElementById('maintenancePriority').value;
        const date = document.getElementById('maintenanceDate').value;

        if (!issue) {
          showToast('Please describe the issue', 'error');
          return;
        }

        Swal.fire({
          title: 'Reporting...',
          text: 'Please wait',
          allowOutsideClick: false,
          didOpen: () => { Swal.showLoading(); }
        });

        const formData = new FormData();
        formData.append('action', 'add_maintenance');
        formData.append('room_id', roomNumber);
        formData.append('issue', issue);
        formData.append('priority', priority);
        formData.append('scheduled_date', date);

        fetch('../../../controller/admin/post/room_actions.php', {
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

        closeMaintenanceModal();
      }

      // Bulk Update functions
      function bulkUpdate() {
        // Fetch all rooms for selection
        Swal.fire({
          title: 'Loading...',
          text: 'Please wait',
          allowOutsideClick: false,
          didOpen: () => { Swal.showLoading(); }
        });

        fetch('../../../controller/admin/post/room_actions.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: 'action=get_all_rooms'
        })
          .then(response => response.json())
          .then(data => {
            Swal.close();
            if (data.success) {
              selectedRooms = [];
              showRoomSelectionModal(data.rooms);
            } else {
              showToast('Failed to load rooms', 'error');
            }
          });
      }

      function showRoomSelectionModal(rooms) {
        let html = `
          <div class="p-4">
            <p class="mb-3 text-sm">Select rooms to update:</p>
            <div class="max-h-60 overflow-y-auto border rounded-lg p-2">
        `;

        rooms.forEach(room => {
          const status = room.is_available == 1 ? 'Available' : 'Occupied';
          html += `
            <label class="flex items-center gap-2 p-2 hover:bg-slate-50 rounded cursor-pointer">
              <input type="checkbox" class="room-checkbox rounded text-amber-600" value="${room.id}">
              <span class="text-sm">
                <span class="font-medium">${room.id}</span> - ${room.name} 
                <span class="text-xs ${room.is_available == 1 ? 'text-green-600' : 'text-blue-600'}">(${status})</span>
                <span class="text-xs text-slate-500 ml-2">₱${parseFloat(room.price).toLocaleString()}</span>
              </span>
            </label>
          `;
        });

        html += `
            </div>
            <div class="mt-4 flex gap-2">
              <button onclick="selectAllRooms()" class="text-xs text-amber-600 hover:underline">Select All</button>
              <button onclick="deselectAllRooms()" class="text-xs text-slate-500 hover:underline">Deselect All</button>
            </div>
          </div>
        `;

        Swal.fire({
          title: 'Bulk Update - Select Rooms',
          html: html,
          showCancelButton: true,
          confirmButtonColor: '#d97706',
          cancelButtonColor: '#6b7280',
          confirmButtonText: 'Next',
          preConfirm: () => {
            const checkboxes = document.querySelectorAll('.room-checkbox:checked');
            if (checkboxes.length === 0) {
              Swal.showValidationMessage('Please select at least one room');
              return false;
            }
            selectedRooms = Array.from(checkboxes).map(cb => cb.value);
            return true;
          }
        }).then((result) => {
          if (result.isConfirmed) {
            showBulkActionModal();
          }
        });
      }

      function selectAllRooms() {
        document.querySelectorAll('.room-checkbox').forEach(cb => cb.checked = true);
      }

      function deselectAllRooms() {
        document.querySelectorAll('.room-checkbox').forEach(cb => cb.checked = false);
      }

      function showBulkActionModal() {
        // Update selected rooms list
        const listHtml = selectedRooms.map(room => `<span class="inline-block bg-amber-100 text-amber-800 px-2 py-1 rounded text-xs mr-1 mb-1">${room}</span>`).join('');
        document.getElementById('selectedRoomsList').innerHTML = listHtml || '<span class="text-slate-400">No rooms selected</span>';

        // Show modal
        document.getElementById('bulkModal').classList.remove('hidden');
        document.getElementById('bulkModal').classList.add('flex');

        // Setup action change handler
        document.getElementById('bulkAction').addEventListener('change', function () {
          const priceField = document.getElementById('bulkPriceField');
          priceField.style.display = this.value === 'update_price' ? 'block' : 'none';
        });
      }

      function closeBulkModal() {
        document.getElementById('bulkModal').classList.add('hidden');
        document.getElementById('bulkModal').classList.remove('flex');
      }

      function submitBulkUpdate() {
        const action = document.getElementById('bulkAction').value;
        const price = document.getElementById('bulkPrice').value;

        if (selectedRooms.length === 0) {
          showToast('No rooms selected', 'error');
          return;
        }

        if (action === 'update_price' && (!price || price <= 0)) {
          showToast('Please enter a valid price', 'error');
          return;
        }

        Swal.fire({
          title: 'Processing...',
          text: 'Please wait',
          allowOutsideClick: false,
          didOpen: () => { Swal.showLoading(); }
        });

        const formData = new FormData();
        formData.append('action', 'bulk_update');
        formData.append('room_ids', selectedRooms.join(','));
        formData.append('bulk_action', action);
        if (action === 'update_price') {
          formData.append('value', price);
        }

        fetch('../../../controller/admin/post/room_actions.php', {
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

        closeBulkModal();
      }

      // Maintenance Log
      function viewMaintenanceLog() {
        Swal.fire({
          title: 'Loading...',
          text: 'Please wait',
          allowOutsideClick: false,
          didOpen: () => { Swal.showLoading(); }
        });

        fetch('../../../controller/admin/post/room_actions.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: 'action=get_maintenance_list'
        })
          .then(response => response.json())
          .then(data => {
            Swal.close();
            if (data.success) {
              let html = '';

              if (data.maintenance.length === 0) {
                html = '<p class="text-center text-slate-500 py-8">No maintenance records found</p>';
              } else {
                data.maintenance.forEach(item => {
                  const priorityColor =
                    item.condition_status === 'damage' ? 'bg-red-100 text-red-700' :
                      item.condition_status === 'maintenance' ? 'bg-amber-100 text-amber-700' :
                        'bg-blue-100 text-blue-700';

                  const priorityText =
                    item.condition_status === 'damage' ? 'High' :
                      item.condition_status === 'maintenance' ? 'Medium' : 'Low';

                  html += `
                    <div class="border rounded-lg p-4 mb-3 hover:shadow-md transition">
                      <div class="flex justify-between items-start">
                        <div>
                          <span class="font-medium">Room ${item.room_number}</span>
                          <span class="ml-2 px-2 py-0.5 rounded-full text-xs ${priorityColor}">${priorityText} Priority</span>
                        </div>
                        <span class="text-xs text-slate-400">${new Date(item.reported_at).toLocaleString()}</span>
                      </div>
                      <p class="text-sm mt-2">${item.notes}</p>
                      <div class="flex justify-between items-center mt-3">
                        <span class="text-xs text-slate-500">Reported by: ${item.reported_by_name || 'Unknown'}</span>
                        <button onclick="completeMaintenance(${item.id})" class="text-xs bg-green-100 text-green-700 px-2 py-1 rounded hover:bg-green-200">
                          <i class="fa-regular fa-circle-check mr-1"></i>Mark Complete
                        </button>
                      </div>
                    </div>
                  `;
                });
              }

              document.getElementById('maintenanceLogContent').innerHTML = html;
              document.getElementById('maintenanceLogModal').classList.remove('hidden');
              document.getElementById('maintenanceLogModal').classList.add('flex');
            }
          });
      }

      function completeMaintenance(maintenanceId) {
        Swal.fire({
          title: 'Complete Maintenance?',
          text: 'Mark this maintenance task as completed?',
          icon: 'question',
          showCancelButton: true,
          confirmButtonColor: '#d97706',
          cancelButtonColor: '#6b7280',
          confirmButtonText: 'Yes, complete'
        }).then((result) => {
          if (result.isConfirmed) {
            Swal.fire({
              title: 'Processing...',
              text: 'Please wait',
              allowOutsideClick: false,
              didOpen: () => { Swal.showLoading(); }
            });

            const formData = new FormData();
            formData.append('action', 'complete_maintenance');
            formData.append('maintenance_id', maintenanceId);

            fetch('../../../controller/admin/post/room_actions.php', {
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
                    viewMaintenanceLog(); // Refresh the log
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

      function closeMaintenanceLogModal() {
        document.getElementById('maintenanceLogModal').classList.add('hidden');
        document.getElementById('maintenanceLogModal').classList.remove('flex');
      }

      // Room Types Statistics
      function manageRoomTypes() {
        Swal.fire({
          title: 'Loading...',
          text: 'Please wait',
          allowOutsideClick: false,
          didOpen: () => { Swal.showLoading(); }
        });

        fetch('../../../controller/admin/post/room_actions.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: 'action=get_room_types'
        })
          .then(response => response.json())
          .then(data => {
            Swal.close();
            if (data.success) {
              let html = '';

              if (data.types.length === 0) {
                html = '<p class="text-center text-slate-500 py-8">No room types found</p>';
              } else {
                html = `
                  <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-slate-500 text-xs">
                      <tr>
                        <td class="p-2">Room Type</td>
                        <td class="p-2 text-right">Total</td>
                        <td class="p-2 text-right">Available</td>
                        <td class="p-2 text-right">Avg Price</td>
                        <td class="p-2 text-right">Occupancy %</td>
                      </tr>
                    </thead>
                    <tbody class="divide-y">
                `;

                data.types.forEach(type => {
                  const occupancyRate = type.count > 0 ?
                    Math.round(((type.count - type.available) / type.count) * 100) : 0;

                  html += `
                    <tr>
                      <td class="p-2 font-medium">${type.type}</td>
                      <td class="p-2 text-right">${type.count}</td>
                      <td class="p-2 text-right text-green-600">${type.available}</td>
                      <td class="p-2 text-right">₱${parseFloat(type.avg_price).toLocaleString()}</td>
                      <td class="p-2 text-right">
                        <span class="${occupancyRate > 80 ? 'text-red-600' : occupancyRate > 50 ? 'text-amber-600' : 'text-green-600'}">
                          ${occupancyRate}%
                        </span>
                      </td>
                    </tr>
                  `;
                });

                html += '</tbody></table>';
              }

              document.getElementById('roomTypesContent').innerHTML = html;
              document.getElementById('roomTypesModal').classList.remove('hidden');
              document.getElementById('roomTypesModal').classList.add('flex');
            }
          });
      }

      function closeRoomTypesModal() {
        document.getElementById('roomTypesModal').classList.add('hidden');
        document.getElementById('roomTypesModal').classList.remove('flex');
      }

      // Update room status (for future implementation)
      function updateRoomStatus(roomNumber, status) {
        // This would be used for quick status toggles
        showToast(`Status update for room ${roomNumber}`, 'info');
      }

      // Notification bell
      document.getElementById('notificationBell').addEventListener('click', function () {
        window.location.href = '../notifications.php';
      });

      // Close modals on outside click
      window.onclick = function (event) {
        const roomModal = document.getElementById('roomModal');
        const assignModal = document.getElementById('assignModal');
        const maintenanceModal = document.getElementById('maintenanceModal');
        const editRoomModal = document.getElementById('editRoomModal');
        const addRoomModal = document.getElementById('addRoomModal');
        const bulkModal = document.getElementById('bulkModal');
        const maintenanceLogModal = document.getElementById('maintenanceLogModal');
        const roomTypesModal = document.getElementById('roomTypesModal');

        if (roomModal && event.target === roomModal) {
          closeRoomModal();
        }
        if (assignModal && event.target === assignModal) {
          closeAssignModal();
        }
        if (maintenanceModal && event.target === maintenanceModal) {
          closeMaintenanceModal();
        }
        if (editRoomModal && event.target === editRoomModal) {
          closeEditRoomModal();
        }
        if (addRoomModal && event.target === addRoomModal) {
          closeAddRoomModal();
        }
        if (bulkModal && event.target === bulkModal) {
          closeBulkModal();
        }
        if (maintenanceLogModal && event.target === maintenanceLogModal) {
          closeMaintenanceLogModal();
        }
        if (roomTypesModal && event.target === roomTypesModal) {
          closeRoomTypesModal();
        }
      }
    </script>
  </body>

</html>