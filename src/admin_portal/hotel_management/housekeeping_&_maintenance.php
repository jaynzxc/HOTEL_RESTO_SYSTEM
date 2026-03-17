<?php
/**
 * View - Admin Housekeeping & Maintenance
 */
require_once '../../../controller/admin/get/housekeeping_maintenance.php';

// Set current page for navigation
$current_page = 'housekeeping_&_maintenance';
?>
<!DOCTYPE html>
<html lang="en">

  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin · Housekeeping & Maintenance</title>
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
            <h1 class="text-2xl lg:text-3xl font-light text-slate-800">Housekeeping & Maintenance</h1>
            <p class="text-sm text-slate-500 mt-0.5">manage room cleaning status, staff assignments, and maintenance
              requests</p>
          </div>
          <div class="flex gap-3 text-sm">
            <span class="bg-white border rounded-full px-4 py-2 flex items-center gap-2 shadow-sm">
              <i class="fa-regular fa-calendar text-slate-400"></i> <?php echo $today; ?>
            </span>
            <span class="bg-white border rounded-full px-4 py-2 shadow-sm cursor-pointer hover:bg-slate-50 relative"
              id="notificationBell">
              <i class="fa-regular fa-bell"></i>
              <?php if ($unread_count > 0): ?>
                <span
                  class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-4 h-4 flex items-center justify-center"><?php echo $unread_count; ?></span>
              <?php endif; ?>
            </span>
          </div>
        </div>

        <!-- STATS CARDS -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
          <div
            class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm cursor-pointer hover:shadow-md transition"
            onclick="filterByStatus('clean')">
            <p class="text-xs text-slate-500">Clean rooms</p>
            <p class="text-2xl font-semibold text-green-600"><?php echo $stats['clean']; ?></p>
          </div>
          <div
            class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm cursor-pointer hover:shadow-md transition"
            onclick="filterByStatus('dirty')">
            <p class="text-xs text-slate-500">Dirty / to clean</p>
            <p class="text-2xl font-semibold text-amber-600"><?php echo $stats['dirty']; ?></p>
          </div>
          <div
            class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm cursor-pointer hover:shadow-md transition"
            onclick="filterByStatus('in-progress')">
            <p class="text-xs text-slate-500">In progress</p>
            <p class="text-2xl font-semibold text-blue-600"><?php echo $stats['in_progress']; ?></p>
          </div>
          <div
            class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm cursor-pointer hover:shadow-md transition"
            onclick="filterByStatus('maintenance')">
            <p class="text-xs text-slate-500">Maintenance</p>
            <p class="text-2xl font-semibold text-rose-600"><?php echo $stats['maintenance']; ?></p>
          </div>
          <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs text-slate-500">Staff on duty</p>
            <p class="text-2xl font-semibold"><?php echo $stats['staff_on_duty']; ?></p>
          </div>
        </div>

        <!-- FILTER AND SEARCH -->
        <div
          class="bg-white rounded-2xl border border-slate-200 p-4 mb-6 flex flex-wrap gap-3 items-center justify-between">
          <div class="flex gap-2 flex-wrap" id="taskFilterTabs">
            <button data-filter="all"
              class="filter-tab <?php echo $statusFilter == 'all' ? 'bg-amber-600 text-white' : 'border border-slate-200 hover:bg-slate-50'; ?> px-4 py-2 rounded-xl text-sm">all
              tasks</button>
            <button data-filter="dirty"
              class="filter-tab <?php echo $statusFilter == 'dirty' ? 'bg-amber-600 text-white' : 'border border-slate-200 hover:bg-slate-50'; ?> px-4 py-2 rounded-xl text-sm">to
              clean</button>
            <button data-filter="in-progress"
              class="filter-tab <?php echo $statusFilter == 'in-progress' ? 'bg-amber-600 text-white' : 'border border-slate-200 hover:bg-slate-50'; ?> px-4 py-2 rounded-xl text-sm">in
              progress</button>
            <button data-filter="maintenance"
              class="filter-tab <?php echo $statusFilter == 'maintenance' ? 'bg-amber-600 text-white' : 'border border-slate-200 hover:bg-slate-50'; ?> px-4 py-2 rounded-xl text-sm">maintenance</button>
            <button data-filter="clean"
              class="filter-tab <?php echo $statusFilter == 'clean' ? 'bg-amber-600 text-white' : 'border border-slate-200 hover:bg-slate-50'; ?> px-4 py-2 rounded-xl text-sm">completed</button>
          </div>
          <div class="relative">
            <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
            <input type="text" id="roomSearchInput" placeholder="search room..."
              value="<?php echo htmlspecialchars($searchFilter); ?>"
              class="border border-slate-200 rounded-xl pl-9 pr-4 py-2 text-sm w-64 focus:ring-1 focus:ring-amber-500 outline-none">
          </div>
        </div>

        <!-- TWO COLUMN LAYOUT -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">

          <!-- LEFT: Housekeeping tasks list -->
          <div class="lg:col-span-2 space-y-6">

            <!-- housekeeping tasks table -->
            <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
              <div class="p-4 border-b border-slate-200 bg-slate-50 flex items-center justify-between">
                <h2 class="font-semibold flex items-center gap-2"><i class="fa-solid fa-broom text-amber-600"></i>
                  Housekeeping tasks</h2>
                <button class="text-sm text-amber-700 border border-amber-600 px-3 py-1 rounded-lg hover:bg-amber-50"
                  onclick="openAssignTasksModal()">assign tasks</button>
              </div>
              <div class="overflow-x-auto">
                <table class="w-full text-sm" id="taskTable">
                  <thead class="text-slate-500 text-xs border-b">
                    <tr>
                      <td class="p-3">Room</td>
                      <td class="p-3">Status</td>
                      <td class="p-3">Priority</td>
                      <td class="p-3">Assigned to</td>
                      <td class="p-3">Last updated</td>
                      <td class="p-3">Actions</td>
                    </tr>
                  </thead>
                  <tbody class="divide-y" id="taskTableBody">
                    <?php foreach ($housekeepingTasks as $task): ?>
                      <tr data-room="<?php echo $task['room_number']; ?>"
                        data-status="<?php echo $task['task_status']; ?>">
                        <td class="p-3 font-medium"><?php echo htmlspecialchars($task['room_number']); ?></td>
                        <td class="p-3">
                          <span class="status-badge px-2 py-1 rounded-full text-xs font-medium
                        <?php
                        switch ($task['task_status']) {
                          case 'clean':
                            echo 'bg-green-100 text-green-700';
                            break;
                          case 'dirty':
                            echo 'bg-amber-100 text-amber-700';
                            break;
                          case 'in-progress':
                            echo 'bg-blue-100 text-blue-700';
                            break;
                          case 'maintenance':
                            echo 'bg-rose-100 text-rose-700';
                            break;
                          default:
                            echo 'bg-slate-100 text-slate-700';
                        }
                        ?>">
                            <?php echo $task['task_status']; ?>
                          </span>
                        </td>
                        <td class="p-3">
                          <span class="px-2 py-1 rounded-full text-xs font-medium
                        <?php
                        switch ($task['priority']) {
                          case 'high':
                            echo 'bg-red-100 text-red-700';
                            break;
                          case 'medium':
                            echo 'bg-amber-100 text-amber-700';
                            break;
                          case 'low':
                            echo 'bg-green-100 text-green-700';
                            break;
                          default:
                            echo 'bg-slate-100 text-slate-700';
                        }
                        ?>">
                            <?php echo $task['priority']; ?>
                          </span>
                        </td>
                        <td class="p-3"><?php echo $task['assigned_to_name'] ?? '—'; ?></td>
                        <td class="p-3">
                          <?php echo $task['updated_at'] ? date('M d, h:i A', strtotime($task['updated_at'])) : date('M d, h:i A', strtotime($task['reported_at'])); ?>
                        </td>
                        <td class="p-3">
                          <div class="flex gap-2">
                            <button onclick="viewTaskDetails(<?php echo $task['id']; ?>)"
                              class="text-blue-600 hover:underline text-xs" title="View details">
                              <i class="fa-regular fa-eye"></i>
                            </button>
                            <?php if ($task['task_status'] !== 'clean'): ?>
                              <button
                                onclick="updateTaskStatus(<?php echo $task['id']; ?>, '<?php echo $task['task_status']; ?>')"
                                class="text-green-600 hover:underline text-xs" title="Mark as done">
                                <i class="fa-regular fa-circle-check"></i>
                              </button>
                            <?php endif; ?>
                            <?php if (!$task['assigned_to']): ?>
                              <button onclick="assignTask(<?php echo $task['id']; ?>)"
                                class="text-amber-600 hover:underline text-xs" title="Assign staff">
                                <i class="fa-regular fa-user"></i>
                              </button>
                            <?php endif; ?>
                          </div>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                    <?php if (empty($housekeepingTasks)): ?>
                      <tr>
                        <td colspan="6" class="p-8 text-center text-slate-500">No housekeeping tasks found</td>
                      </tr>
                    <?php endif; ?>
                  </tbody>
                </table>
              </div>
            </div>

            <!-- maintenance requests -->
            <div class="bg-white rounded-2xl border border-slate-200 p-5">
              <h2 class="font-semibold text-lg flex items-center gap-2 mb-3"><i
                  class="fa-solid fa-wrench text-amber-600"></i> active maintenance requests</h2>
              <div class="space-y-3">
                <?php foreach ($maintenanceRequests as $request): ?>
                  <div
                    class="flex justify-between items-center border-b pb-2 cursor-pointer hover:bg-amber-50 p-2 rounded transition"
                    onclick="viewMaintenanceDetails(<?php echo $request['id']; ?>)">
                    <div>
                      <span class="font-medium">Room <?php echo htmlspecialchars($request['room_number']); ?></span>
                      <p class="text-xs text-slate-500"><?php echo htmlspecialchars($request['notes']); ?> · reported
                        <?php echo date('h:i A', strtotime($request['reported_at'])); ?>
                      </p>
                    </div>
                    <span class="px-2 py-1 rounded-full text-xs font-medium
                  <?php
                  switch ($request['priority_level']) {
                    case 'urgent':
                      echo 'bg-red-100 text-red-700';
                      break;
                    case 'high':
                      echo 'bg-orange-100 text-orange-700';
                      break;
                    case 'normal':
                      echo 'bg-yellow-100 text-yellow-700';
                      break;
                    default:
                      echo 'bg-blue-100 text-blue-700';
                  }
                  ?>">
                      <?php echo $request['priority_level']; ?>
                    </span>
                  </div>
                <?php endforeach; ?>
                <?php if (empty($maintenanceRequests)): ?>
                  <p class="text-sm text-slate-500 text-center py-4">No active maintenance requests</p>
                <?php endif; ?>
              </div>
            </div>
          </div>

          <!-- RIGHT: Staff & Quick Actions -->
          <div class="space-y-5">
            <!-- housekeeping staff on duty -->
            <div class="bg-white rounded-2xl border border-slate-200 p-5">
              <h3 class="font-semibold flex items-center gap-2 mb-3"><i class="fa-regular fa-user text-amber-600"></i>
                housekeeping staff</h3>
              <ul class="space-y-2">
                <?php foreach ($staff as $member): ?>
                  <li class="flex justify-between items-center p-2 hover:bg-slate-50 rounded cursor-pointer"
                    onclick="showStaffDetails(<?php echo $member['id']; ?>)">
                    <span>
                      <span class="font-medium"><?php echo htmlspecialchars($member['full_name']); ?></span>
                      <span class="text-xs text-slate-500 ml-1">(<?php echo $member['role']; ?>)</span>
                    </span>
                    <span class="bg-green-100 text-green-700 text-xs px-2 py-0.5 rounded-full">on duty</span>
                  </li>
                <?php endforeach; ?>
              </ul>
              <button
                class="w-full border border-amber-600 text-amber-700 rounded-xl py-2 text-sm mt-4 hover:bg-amber-50"
                onclick="openManageStaffModal()">manage staff</button>
            </div>

            <!-- linen / supplies summary -->
            <div class="bg-white rounded-2xl border border-slate-200 p-5">
              <h3 class="font-semibold flex items-center gap-2 mb-2"><i
                  class="fa-regular fa-basket-shopping text-amber-600"></i> linen & supplies</h3>
              <div class="space-y-2 text-sm">
                <?php foreach ($supplies as $supply): ?>
                  <div class="flex justify-between items-center p-2 hover:bg-slate-50 rounded cursor-pointer"
                    onclick="updateSupply(<?php echo $supply['id']; ?>, '<?php echo htmlspecialchars($supply['item_name']); ?>', <?php echo $supply['stock']; ?>)">
                    <span><?php echo htmlspecialchars($supply['item_name']); ?></span>
                    <span
                      class="<?php echo $supply['stock'] <= $supply['reorder_level'] ? 'text-amber-600 font-medium' : 'text-green-600'; ?>">
                      <?php echo $supply['stock']; ?>   <?php echo $supply['unit']; ?>
                      <?php if ($supply['stock'] <= $supply['reorder_level']): ?>
                        <i class="fa-regular fa-triangle-exclamation ml-1" title="Low stock"></i>
                      <?php endif; ?>
                    </span>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
          </div>
        </div>

        <!-- BOTTOM: ROOM STATUS SUMMARY -->
        <div class="bg-white rounded-2xl border border-slate-200 p-5">
          <h2 class="font-semibold text-lg flex items-center gap-2 mb-3"><i
              class="fa-regular fa-building text-amber-600"></i> floor status summary</h2>
          <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-3">
            <?php foreach ($floorStats as $floor): ?>
              <div class="border rounded-xl p-3 text-center cursor-pointer hover:shadow-md transition"
                onclick="showFloorDetails('<?php echo $floor['floor']; ?>')">
                <span class="text-xs text-slate-500">Floor <?php echo $floor['floor']; ?></span>
                <div class="flex justify-center gap-1 mt-2">
                  <?php
                  $clean_count = $floor['total_rooms'] - $floor['occupied'] - $floor['maintenance'];
                  for ($i = 0; $i < $floor['total_rooms']; $i++):
                    $color = 'bg-green-500'; // clean
                    if ($i < $floor['maintenance']) {
                      $color = 'bg-rose-500';
                    } elseif ($i < $floor['maintenance'] + $floor['occupied']) {
                      $color = 'bg-blue-500';
                    }
                    ?>
                    <span class="<?php echo $color; ?> w-3 h-3 rounded-full" title="<?php
                        if ($color === 'bg-green-500')
                          echo 'Clean';
                        elseif ($color === 'bg-blue-500')
                          echo 'Occupied';
                        elseif ($color === 'bg-rose-500')
                          echo 'Maintenance';
                        ?>"></span>
                  <?php endfor; ?>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
          <div class="flex gap-4 mt-3 text-xs text-slate-500">
            <span><span class="inline-block w-3 h-3 bg-green-500 rounded-full mr-1"></span> clean</span>
            <span><span class="inline-block w-3 h-3 bg-blue-500 rounded-full mr-1"></span> occupied</span>
            <span><span class="inline-block w-3 h-3 bg-rose-500 rounded-full mr-1"></span> maintenance</span>
          </div>
        </div>
      </main>
    </div>

    <!-- Task Details Modal -->
    <div id="taskModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
      <div class="bg-white rounded-2xl p-6 max-w-lg w-full mx-4">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-xl font-semibold">Task Details</h3>
          <button onclick="closeTaskModal()" class="text-slate-400 hover:text-slate-600">
            <i class="fa-solid fa-xmark text-2xl"></i>
          </button>
        </div>
        <div id="taskDetails" class="space-y-3">
          <!-- Populated by JavaScript -->
        </div>
        <div class="mt-6 flex gap-3">
          <button onclick="closeTaskModal()"
            class="flex-1 border border-slate-200 px-4 py-2 rounded-xl hover:bg-slate-50">Close</button>
          <button id="taskActionBtn" onclick="handleTaskAction()"
            class="flex-1 bg-amber-600 text-white px-4 py-2 rounded-xl hover:bg-amber-700">Mark as Done</button>
        </div>
      </div>
    </div>

    <!-- Assign Task Modal -->
    <div id="assignTaskModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
      <div class="bg-white rounded-2xl p-6 max-w-md w-full mx-4">
        <h3 class="text-lg font-semibold mb-4">Assign Task to Staff</h3>
        <input type="hidden" id="assignTaskId">
        <select id="assignStaffId" class="w-full border border-slate-200 rounded-xl p-3 mb-4">
          <option value="">Select staff member...</option>
          <?php foreach ($staff as $member): ?>
            <option value="<?php echo $member['id']; ?>"><?php echo htmlspecialchars($member['full_name']); ?>
              (<?php echo $member['role']; ?>)</option>
          <?php endforeach; ?>
        </select>
        <div class="flex gap-3">
          <button onclick="closeAssignTaskModal()"
            class="flex-1 border border-slate-200 px-4 py-2 rounded-xl hover:bg-slate-50">Cancel</button>
          <button onclick="submitTaskAssignment()"
            class="flex-1 bg-amber-600 text-white px-4 py-2 rounded-xl hover:bg-amber-700">Assign</button>
        </div>
      </div>
    </div>

    <!-- Add Maintenance Modal -->
    <div id="addMaintenanceModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
      <div class="bg-white rounded-2xl p-6 max-w-md w-full mx-4">
        <h3 class="text-lg font-semibold mb-4">Report Maintenance Issue</h3>
        <div class="space-y-4">
          <div>
            <label class="block text-sm text-slate-600 mb-1">Room Number</label>
            <input type="text" id="maintenanceRoom" class="w-full border rounded-xl p-2" placeholder="e.g., 305">
          </div>
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
          <button onclick="closeAddMaintenanceModal()"
            class="flex-1 border border-slate-200 px-4 py-2 rounded-xl hover:bg-slate-50">Cancel</button>
          <button onclick="submitMaintenance()"
            class="flex-1 bg-amber-600 text-white px-4 py-2 rounded-xl hover:bg-amber-700">Report</button>
        </div>
      </div>
    </div>

    <!-- Update Supply Modal -->
    <div id="supplyModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
      <div class="bg-white rounded-2xl p-6 max-w-md w-full mx-4">
        <h3 class="text-lg font-semibold mb-4">Update Supply Stock</h3>
        <input type="hidden" id="supplyId">
        <div class="space-y-4">
          <div>
            <label class="block text-sm text-slate-600 mb-1">Supply Name</label>
            <p id="supplyName" class="font-medium"></p>
          </div>
          <div>
            <label class="block text-sm text-slate-600 mb-1">Current Stock</label>
            <input type="number" id="supplyStock" class="w-full border rounded-xl p-2" min="0">
          </div>
        </div>
        <div class="flex gap-3 mt-6">
          <button onclick="closeSupplyModal()"
            class="flex-1 border border-slate-200 px-4 py-2 rounded-xl hover:bg-slate-50">Cancel</button>
          <button onclick="submitSupplyUpdate()"
            class="flex-1 bg-amber-600 text-white px-4 py-2 rounded-xl hover:bg-amber-700">Update</button>
        </div>
      </div>
    </div>

    <!-- Assign Tasks Modal (for bulk assignment) -->
    <div id="assignTasksModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
      <div class="bg-white rounded-2xl p-6 max-w-3xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-xl font-semibold">Assign Tasks to Staff</h3>
          <button onclick="closeAssignTasksModal()" class="text-slate-400 hover:text-slate-600">
            <i class="fa-solid fa-xmark text-2xl"></i>
          </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Pending Tasks List -->
          <div>
            <h4 class="font-medium mb-3">Pending Tasks</h4>
            <div id="pendingTasksList" class="space-y-2 max-h-96 overflow-y-auto pr-2">
              <!-- Populated by JavaScript -->
            </div>
          </div>

          <!-- Staff List -->
          <div>
            <h4 class="font-medium mb-3">Staff Members</h4>
            <div id="staffWorkloadList" class="space-y-3 max-h-96 overflow-y-auto pr-2">
              <!-- Populated by JavaScript -->
            </div>
          </div>
        </div>

        <div class="mt-6 flex gap-3">
          <button onclick="closeAssignTasksModal()"
            class="flex-1 border border-slate-200 px-4 py-2 rounded-xl hover:bg-slate-50">Close</button>
          <button onclick="refreshTaskData()"
            class="flex-1 bg-amber-600 text-white px-4 py-2 rounded-xl hover:bg-amber-700">
            <i class="fa-regular fa-rotate mr-2"></i>Refresh
          </button>
        </div>
      </div>
    </div>

    <!-- Manage Staff Modal -->
    <div id="manageStaffModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
      <div class="bg-white rounded-2xl p-6 max-w-3xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-xl font-semibold">Manage Staff</h3>
          <button onclick="closeManageStaffModal()" class="text-slate-400 hover:text-slate-600">
            <i class="fa-solid fa-xmark text-2xl"></i>
          </button>
        </div>

        <!-- Staff List with Status -->
        <div class="space-y-4">
          <?php foreach ($staff as $member): ?>
            <div class="border rounded-xl p-4 hover:shadow-md transition">
              <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                  <div
                    class="w-10 h-10 rounded-full bg-amber-100 flex items-center justify-center text-amber-700 font-bold">
                    <?php echo strtoupper(substr($member['full_name'], 0, 1)); ?>
                  </div>
                  <div>
                    <p class="font-medium">
                      <?php echo htmlspecialchars($member['full_name']); ?>
                    </p>
                    <p class="text-xs text-slate-500">
                      <?php echo ucfirst($member['role']); ?>
                    </p>
                  </div>
                </div>
                <div class="flex items-center gap-3">
                  <select onchange="updateStaffStatus(<?php echo $member['id']; ?>, this.value)"
                    class="border rounded-lg px-3 py-1 text-sm">
                    <option value="on_duty" selected>On Duty</option>
                    <option value="break">On Break</option>
                    <option value="off_duty">Off Duty</option>
                  </select>
                  <button onclick="viewStaffTasks(<?php echo $member['id']; ?>)"
                    class="text-amber-600 hover:text-amber-700" title="View Tasks">
                    <i class="fa-regular fa-list"></i>
                  </button>
                </div>
              </div>
              <div class="mt-2 text-xs text-slate-500">
                <span class="mr-3"><i class="fa-regular fa-clock mr-1"></i>Last active: Today, 10:30 AM</span>
                <span><i class="fa-regular fa-broom mr-1"></i>Assigned tasks: <span
                    id="taskCount-<?php echo $member['id']; ?>">0</span></span>
              </div>
            </div>
          <?php endforeach; ?>
        </div>

        <!-- Add Staff Section -->
        <div class="mt-6 pt-4 border-t">
          <h4 class="font-medium mb-3">Add New Staff Member</h4>
          <div class="flex gap-3">
            <input type="text" id="newStaffName" placeholder="Full Name"
              class="flex-1 border rounded-xl px-4 py-2 text-sm">
            <select id="newStaffRole" class="border rounded-xl px-4 py-2 text-sm">
              <option value="staff">Housekeeping Staff</option>
              <option value="admin">Supervisor</option>
            </select>
            <button onclick="addNewStaff()"
              class="bg-amber-600 text-white px-4 py-2 rounded-xl text-sm hover:bg-amber-700">
              <i class="fa-regular fa-plus mr-1"></i>Add
            </button>
          </div>
        </div>

        <div class="mt-6 flex justify-end">
          <button onclick="closeManageStaffModal()"
            class="px-6 py-2 border border-slate-200 rounded-xl hover:bg-slate-50">Close</button>
        </div>
      </div>
    </div>

    <script>
      // Pass PHP data to JavaScript
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

      // Filter by status
      function filterByStatus(status) {
        const url = new URL(window.location);
        if (status !== 'all') {
          url.searchParams.set('status', status);
        } else {
          url.searchParams.delete('status');
        }
        window.location.href = url.toString();
      }

      // Search
      document.getElementById('roomSearchInput').addEventListener('keypress', function (e) {
        if (e.key === 'Enter') {
          const url = new URL(window.location);
          if (this.value.trim()) {
            url.searchParams.set('search', this.value.trim());
          } else {
            url.searchParams.delete('search');
          }
          window.location.href = url.toString();
        }
      });

      // Filter tabs
      document.querySelectorAll('.filter-tab').forEach(btn => {
        btn.addEventListener('click', function () {
          filterByStatus(this.dataset.filter);
        });
      });

      // Task Details
      let currentTaskId = null;

      function viewTaskDetails(taskId) {
        currentTaskId = taskId;

        Swal.fire({
          title: 'Loading...',
          text: 'Please wait',
          allowOutsideClick: false,
          didOpen: () => { Swal.showLoading(); }
        });

        const formData = new FormData();
        formData.append('action', 'get_task_details');
        formData.append('task_id', taskId);

        fetch('../../../controller/admin/post/housekeeping_actions.php', {
          method: 'POST',
          body: formData
        })
          .then(response => response.json())
          .then(data => {
            Swal.close();
            if (data.success) {
              const t = data.task;
              const details = document.getElementById('taskDetails');

              const priorityColor =
                t.condition_status === 'damage' ? 'bg-red-100 text-red-700' :
                  t.condition_status === 'maintenance' ? 'bg-amber-100 text-amber-700' :
                    'bg-green-100 text-green-700';

              const priorityText =
                t.condition_status === 'damage' ? 'High' :
                  t.condition_status === 'maintenance' ? 'Medium' : 'Low';

              details.innerHTML = `
            <div class="grid grid-cols-2 gap-3">
              <div>
                <p class="text-xs text-slate-500">Room</p>
                <p class="font-medium">${t.room_number}</p>
              </div>
              <div>
                <p class="text-xs text-slate-500">Priority</p>
                <p><span class="${priorityColor} px-2 py-1 rounded-full text-xs">${priorityText}</span></p>
              </div>
              <div>
                <p class="text-xs text-slate-500">Reported</p>
                <p class="text-sm">${new Date(t.reported_at).toLocaleString()}</p>
              </div>
              <div>
                <p class="text-xs text-slate-500">Assigned To</p>
                <p class="text-sm">${t.assigned_to_name || 'Unassigned'}</p>
              </div>
            </div>
            <div class="border-t pt-3">
              <p class="text-xs text-slate-500 mb-1">Issue Description</p>
              <p class="text-sm bg-slate-50 p-3 rounded-lg">${t.notes || 'No description'}</p>
            </div>
          `;

              const actionBtn = document.getElementById('taskActionBtn');
              if (t.cleaned_at) {
                actionBtn.style.display = 'none';
              } else {
                actionBtn.style.display = 'block';
                actionBtn.textContent = 'Mark as Completed';
              }

              document.getElementById('taskModal').classList.remove('hidden');
              document.getElementById('taskModal').classList.add('flex');
            }
          });
      }

      function closeTaskModal() {
        document.getElementById('taskModal').classList.add('hidden');
        document.getElementById('taskModal').classList.remove('flex');
      }

      function handleTaskAction() {
        if (currentTaskId) {
          updateTaskStatus(currentTaskId, 'in-progress');
        }
      }

      // Update Task Status
      function updateTaskStatus(taskId, currentStatus) {
        const newStatus = currentStatus === 'clean' ? 'clean' : 'completed';

        Swal.fire({
          title: 'Update Status',
          text: `Mark this task as completed?`,
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
            formData.append('action', 'update_task_status');
            formData.append('task_id', taskId);
            formData.append('status', 'clean');

            fetch('../../../controller/admin/post/housekeeping_actions.php', {
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

        closeTaskModal();
      }

      // Assign Task
      function assignTask(taskId) {
        document.getElementById('assignTaskId').value = taskId;
        document.getElementById('assignTaskModal').classList.remove('hidden');
        document.getElementById('assignTaskModal').classList.add('flex');
      }

      function closeAssignTaskModal() {
        document.getElementById('assignTaskModal').classList.add('hidden');
        document.getElementById('assignTaskModal').classList.remove('flex');
      }

      function submitTaskAssignment() {
        const taskId = document.getElementById('assignTaskId').value;
        const staffId = document.getElementById('assignStaffId').value;

        if (!staffId) {
          showToast('Please select a staff member', 'error');
          return;
        }

        Swal.fire({
          title: 'Assigning...',
          text: 'Please wait',
          allowOutsideClick: false,
          didOpen: () => { Swal.showLoading(); }
        });

        const formData = new FormData();
        formData.append('action', 'assign_task');
        formData.append('task_id', taskId);
        formData.append('staff_id', staffId);

        fetch('../../../controller/admin/post/housekeeping_actions.php', {
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

        closeAssignTaskModal();
      }

      // Add Maintenance
      function openAddMaintenanceModal() {
        document.getElementById('addMaintenanceModal').classList.remove('hidden');
        document.getElementById('addMaintenanceModal').classList.add('flex');
      }

      function closeAddMaintenanceModal() {
        document.getElementById('addMaintenanceModal').classList.add('hidden');
        document.getElementById('addMaintenanceModal').classList.remove('flex');
        document.getElementById('maintenanceRoom').value = '';
        document.getElementById('maintenanceIssue').value = '';
      }

      function submitMaintenance() {
        const roomNumber = document.getElementById('maintenanceRoom').value.trim();
        const issue = document.getElementById('maintenanceIssue').value.trim();
        const priority = document.getElementById('maintenancePriority').value;
        const date = document.getElementById('maintenanceDate').value;

        if (!roomNumber || !issue) {
          showToast('Please fill in all required fields', 'error');
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

        fetch('../../../controller/admin/post/housekeeping_actions.php', {
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

        closeAddMaintenanceModal();
      }

      // View Maintenance Details
      function viewMaintenanceDetails(maintenanceId) {
        viewTaskDetails(maintenanceId);
      }

      // Update Supply
      function updateSupply(supplyId, supplyName, currentStock) {
        document.getElementById('supplyId').value = supplyId;
        document.getElementById('supplyName').textContent = supplyName;
        document.getElementById('supplyStock').value = currentStock;
        document.getElementById('supplyModal').classList.remove('hidden');
        document.getElementById('supplyModal').classList.add('flex');
      }

      function closeSupplyModal() {
        document.getElementById('supplyModal').classList.add('hidden');
        document.getElementById('supplyModal').classList.remove('flex');
      }

      function submitSupplyUpdate() {
        const supplyId = document.getElementById('supplyId').value;
        const stock = document.getElementById('supplyStock').value;

        Swal.fire({
          title: 'Updating...',
          text: 'Please wait',
          allowOutsideClick: false,
          didOpen: () => { Swal.showLoading(); }
        });

        const formData = new FormData();
        formData.append('action', 'update_supplies');
        formData.append('supply_id', supplyId);
        formData.append('stock', stock);

        fetch('../../../controller/admin/post/housekeeping_actions.php', {
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

        closeSupplyModal();
      }

      // Staff functions
      function showStaffDetails(staffId) {
        showToast('Staff details feature coming soon', 'info');
      }

      function openManageStaffModal() {
        showToast('Manage staff feature coming soon', 'info');
      }

      function openAssignTasksModal() {
        showToast('Assign tasks feature coming soon', 'info');
      }

      // Floor details
      function showFloorDetails(floor) {
        filterByStatus('all');
        document.getElementById('roomSearchInput').value = floor;
        document.getElementById('roomSearchInput').dispatchEvent(new Event('keypress', { key: 'Enter' }));
      }

      // Notification bell
      document.getElementById('notificationBell').addEventListener('click', function () {
        window.location.href = '../notifications.php';
      });

      // Close modals on outside click
      window.onclick = function (event) {
        const taskModal = document.getElementById('taskModal');
        const assignTaskModal = document.getElementById('assignTaskModal');
        const addMaintenanceModal = document.getElementById('addMaintenanceModal');
        const supplyModal = document.getElementById('supplyModal');

        if (taskModal && event.target === taskModal) {
          closeTaskModal();
        }
        if (assignTaskModal && event.target === assignTaskModal) {
          closeAssignTaskModal();
        }
        if (addMaintenanceModal && event.target === addMaintenanceModal) {
          closeAddMaintenanceModal();
        }
        if (supplyModal && event.target === supplyModal) {
          closeSupplyModal();
        }
      }
    </script>
  </body>

</html>