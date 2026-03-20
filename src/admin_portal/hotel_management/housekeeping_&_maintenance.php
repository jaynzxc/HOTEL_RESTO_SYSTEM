<?php
/**
 * View - Admin Housekeeping & Maintenance
 * Integrated with HR API for staff data
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

      .staff-avatar {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: bold;
        font-size: 14px;
      }

      .status-indicator {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 6px;
      }

      .status-present {
        background-color: #10b981;
        box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.2);
      }

      .status-absent {
        background-color: #ef4444;
        box-shadow: 0 0 0 2px rgba(239, 68, 68, 0.2);
      }

      .status-break {
        background-color: #f59e0b;
        box-shadow: 0 0 0 2px rgba(245, 158, 11, 0.2);
      }

      .hr-badge {
        background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
        color: white;
        font-size: 10px;
        padding: 2px 6px;
        border-radius: 12px;
        margin-left: 6px;
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

          <!-- LEFT SIDE -->
          <div>
            <h1 class="text-2xl lg:text-3xl font-light text-slate-800">
              Housekeeping & Maintenance
            </h1>
            <p class="text-sm text-slate-500 mt-0.5">
              manage room cleaning status, staff assignments, and maintenance requests
            </p>

            <?php if ($hr_api_connected): ?>
              <span class="inline-flex items-center gap-1 text-xs text-green-600 mt-1">
                <i class="fa-solid fa-circle-check"></i>
                Connected to HR system · <?php echo $totalHotelStaff; ?> hotel staff
              </span>
            <?php else: ?>
              <span class="inline-flex items-center gap-1 text-xs text-amber-600 mt-1">
                <i class="fa-solid fa-triangle-exclamation"></i>
                HR system unavailable
              </span>
            <?php endif; ?>
          </div>

          <!-- RIGHT SIDE -->
          <div class="flex items-center gap-3 text-sm">

            <!-- Date -->
            <span class="bg-white border rounded-full px-4 py-2 flex items-center gap-2 shadow-sm">
              <i class="fas fa-calendar text-slate-400"></i>
              <?php echo $today; ?>
            </span>

            <!-- Notifications -->
            <?php require_once '../components/notification_component.php'; ?>

            <!-- Job Requisition Icon -->
            <a href="../jobRequisition.php"
              class="bg-white border rounded-full px-3 py-2 flex items-center justify-center shadow-sm hover:bg-slate-100 transition">
              <i class="fas fa-briefcase text-slate-600"></i>
            </a>

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
            <p class="text-2xl font-semibold">
              <?php echo $stats['staff_on_duty']; ?>/<?php echo $stats['total_hotel_staff']; ?>
            </p>
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
                  Maintenance Tasks</h2>
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
                    <?php if (!empty($housekeepingTasks)): ?>
                      <?php foreach ($housekeepingTasks as $task): ?>
                        <tr data-room="<?php echo $task['room_number']; ?>"
                          data-status="<?php echo $task['task_status']; ?>" data-task-id="<?php echo $task['id']; ?>">
                          <td class="p-3 font-medium"><?php echo htmlspecialchars($task['room_number']); ?></td>
                          <td class="p-3">
                            <span class="status-badge px-2 py-1 rounded-full text-xs font-medium
                                    <?php
                                    switch ($task['task_status']) {
                                      case 'clean':
                                        echo 'bg-green-100 text-green-700';
                                        break;
                                      case 'pending':
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
                          <td class="p-3">
                            <?php if (!empty($task['assigned_to_name']) && $task['assigned_to_name'] !== '—'): ?>
                              <div class="flex items-center gap-2">
                                <div class="staff-avatar">
                                  <?php echo strtoupper(substr($task['assigned_to_name'], 0, 1)); ?>
                                </div>
                                <span class="text-sm"><?php echo htmlspecialchars($task['assigned_to_name']); ?></span>
                              </div>
                            <?php else: ?>
                              <span class="text-slate-400">—</span>
                            <?php endif; ?>
                          </td>
                          <td class="p-3">
                            <?php echo $task['updated_at'] ? date('M d, h:i A', strtotime($task['updated_at'])) : date('M d, h:i A', strtotime($task['reported_at'])); ?>
                          </td>
                          <td class="p-3">
                            <div class="flex gap-2">
                              <button onclick="viewTaskDetails(<?php echo $task['id']; ?>)"
                                class="text-blue-600 hover:underline text-xs" title="View details">
                                <i class="fas fa-eye"></i>
                              </button>

                              <?php if ($task['task_status'] !== 'clean' && $task['task_status'] !== 'completed'): ?>
                                <button
                                  onclick="updateTaskStatus(<?php echo $task['id']; ?>, '<?php echo $task['task_status']; ?>')"
                                  class="text-green-600 hover:underline text-xs" title="Mark as done">
                                  <i class="fas fa-circle-check"></i>
                                </button>
                              <?php else: ?>
                                <span class="text-green-400 text-xs" title="Already completed">
                                  <i class="fas fa-circle-check opacity-50"></i>
                                </span>
                              <?php endif; ?>

                              <?php if (empty($task['assigned_hr_employee_id']) && $task['task_status'] !== 'clean' && $task['task_status'] !== 'completed'): ?>
                                <button onclick="assignTask(<?php echo $task['id']; ?>)"
                                  class="text-amber-600 hover:underline text-xs" title="Assign staff">
                                  <i class="fas fa-user"></i>
                                </button>
                              <?php elseif (!empty($task['assigned_hr_employee_id'])): ?>
                                <span class="text-amber-400 text-xs"
                                  title="Already assigned to <?php echo htmlspecialchars($task['assigned_to_name']); ?>">
                                  <i class="fas fa-user-check opacity-50"></i>
                                </span>
                              <?php endif; ?>
                            </div>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    <?php else: ?>
                      <tr>
                        <td colspan="6" class="p-8 text-center text-slate-500">No maintenance tasks found</td>
                      </tr>
                    <?php endif; ?>
                  </tbody>
                </table>
              </div>
            </div>

            <!-- Cleaning Tasks Section -->
            <?php if (!empty($dirtyRooms)): ?>
              <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden mt-6">
                <div class="p-4 border-b border-slate-200 bg-amber-50 flex items-center justify-between">
                  <h2 class="font-semibold flex items-center gap-2"><i class="fa-solid fa-broom text-amber-600"></i>
                    Cleaning Tasks</h2>
                </div>
                <div class="overflow-x-auto">
                  <table class="w-full text-sm">
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
                    <tbody class="divide-y">
                      <?php foreach ($dirtyRooms as $dirtyRoom): ?>
                        <tr data-room="<?php echo $dirtyRoom['room_number']; ?>" data-status="pending">
                          <td class="p-3 font-medium"><?php echo htmlspecialchars($dirtyRoom['room_number']); ?></td>
                          <td class="p-3">
                            <span class="px-2 py-1 rounded-full text-xs font-medium bg-amber-100 text-amber-700">
                              pending
                            </span>
                          </td>
                          <td class="p-3">
                            <span class="px-2 py-1 rounded-full text-xs font-medium bg-amber-100 text-amber-700">
                              medium
                            </span>
                          </td>
                          <td class="p-3">
                            <?php
                            // Check if there's an assigned cleaning staff from room_maintenance
                            $assignedCleaner = null;
                            foreach ($housekeepingTasks as $task) {
                              if (
                                $task['room_number'] == $dirtyRoom['room_number'] &&
                                strpos($task['notes'], 'Cleaning task:') === 0
                              ) {
                                $assignedCleaner = $task['assigned_to_name'];
                                break;
                              }
                            }
                            ?>
                            <?php if ($assignedCleaner): ?>
                              <div class="flex items-center gap-2">
                                <div class="staff-avatar">
                                  <?php echo strtoupper(substr($assignedCleaner, 0, 1)); ?>
                                </div>
                                <span class="text-sm"><?php echo htmlspecialchars($assignedCleaner); ?></span>
                              </div>
                            <?php else: ?>
                              <span class="text-slate-400">—</span>
                            <?php endif; ?>
                          </td>
                          <td class="p-3">
                            <?php echo date('M d, h:i A'); ?>
                          </td>
                          <td class="p-3">
                            <div class="flex gap-2">
                              <button onclick="viewDirtyRoomDetails('<?php echo $dirtyRoom['room_number']; ?>')"
                                class="text-blue-600 hover:underline text-xs" title="View details">
                                <i class="fas fa-eye"></i>
                              </button>
                              <button onclick="markRoomAsClean('<?php echo $dirtyRoom['room_number']; ?>')"
                                class="text-green-600 hover:underline text-xs" title="Mark as clean">
                                <i class="fas fa-circle-check"></i>
                              </button>
                              <button onclick="assignCleaningTask('<?php echo $dirtyRoom['room_number']; ?>')"
                                class="text-amber-600 hover:underline text-xs" title="Assign cleaning staff">
                                <i class="fas fa-user"></i>
                              </button>
                            </div>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                </div>
              </div>
            <?php endif; ?>

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
                      <?php if (!empty($request['assigned_to_name']) && $request['assigned_to_name'] !== '—'): ?>
                        <span class="text-xs text-green-600 ml-2">
                          <i class="fas fa-user-check"></i>
                          <?php echo htmlspecialchars($request['assigned_to_name']); ?>
                        </span>
                      <?php endif; ?>
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
            <!-- Staff Tabs -->
            <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
              <div class="border-b border-slate-200">
                <div class="flex">
                  <button
                    class="staff-tab active flex-1 py-3 text-sm font-medium text-amber-600 border-b-2 border-amber-600"
                    data-tab="all">
                    All Staff (<?php echo count($allStaff); ?>)
                  </button>
                  <button class="staff-tab flex-1 py-3 text-sm font-medium text-slate-500 hover:text-slate-700"
                    data-tab="housekeeping">
                    Housekeeping (<?php echo count($housekeepingStaff); ?>)
                  </button>
                  <button class="staff-tab flex-1 py-3 text-sm font-medium text-slate-500 hover:text-slate-700"
                    data-tab="maintenance">
                    Maintenance (<?php echo count($maintenanceStaff); ?>)
                  </button>
                </div>
              </div>

              <!-- All Staff Panel -->
              <div class="staff-panel p-4" id="panel-all">
                <h3 class="text-xs font-medium text-slate-400 uppercase tracking-wider mb-3">Hotel Staff</h3>
                <ul class="space-y-3 max-h-[400px] overflow-y-auto">
                  <?php foreach ($allStaff as $member): ?>
                    <li class="flex items-start justify-between p-2 hover:bg-slate-50 rounded cursor-pointer"
                      onclick="showStaffDetails('<?php echo $member['id']; ?>')">
                      <div class="flex items-start gap-3">
                        <div class="staff-avatar">
                          <?php echo strtoupper(substr($member['full_name'], 0, 1)); ?>
                        </div>
                        <div>
                          <div class="flex items-center gap-2">
                            <span class="font-medium text-sm"><?php echo htmlspecialchars($member['full_name']); ?></span>
                            <span class="hr-badge">HR</span>
                          </div>
                          <p class="text-xs text-slate-500"><?php echo htmlspecialchars($member['position']); ?></p>
                          <?php if (isset($member['status']['present']) && $member['status']['present']): ?>
                            <span class="text-xs text-green-600">
                              <span class="status-indicator status-present"></span>
                              On duty
                            </span>
                          <?php else: ?>
                            <span class="text-xs text-slate-400">
                              <span class="status-indicator status-absent"></span>
                              Off duty
                            </span>
                          <?php endif; ?>
                        </div>
                      </div>
                      <?php if (isset($member['status']['is_late']) && $member['status']['is_late']): ?>
                        <span class="text-xs text-amber-600 whitespace-nowrap">
                          <i class="fas fa-clock"></i> Late
                        </span>
                      <?php endif; ?>
                    </li>
                  <?php endforeach; ?>
                </ul>
              </div>

              <!-- Housekeeping Panel -->
              <div class="staff-panel p-4 hidden" id="panel-housekeeping">
                <h3 class="text-xs font-medium text-slate-400 uppercase tracking-wider mb-3">Housekeeping Staff</h3>
                <ul class="space-y-3 max-h-[400px] overflow-y-auto">
                  <?php foreach ($housekeepingStaff as $member): ?>
                    <li class="flex items-start justify-between p-2 hover:bg-slate-50 rounded cursor-pointer"
                      onclick="showStaffDetails('<?php echo $member['id']; ?>')">
                      <div class="flex items-start gap-3">
                        <div class="staff-avatar">
                          <?php echo strtoupper(substr($member['full_name'], 0, 1)); ?>
                        </div>
                        <div>
                          <div class="flex items-center gap-2">
                            <span class="font-medium text-sm"><?php echo htmlspecialchars($member['full_name']); ?></span>
                            <span class="hr-badge">HR</span>
                          </div>
                          <p class="text-xs text-slate-500"><?php echo htmlspecialchars($member['position']); ?></p>
                          <?php if (isset($member['status']['present']) && $member['status']['present']): ?>
                            <span class="text-xs text-green-600">
                              <span class="status-indicator status-present"></span>
                              On duty
                            </span>
                          <?php else: ?>
                            <span class="text-xs text-slate-400">
                              <span class="status-indicator status-absent"></span>
                              Off duty
                            </span>
                          <?php endif; ?>
                        </div>
                      </div>
                      <?php if (isset($member['status']['is_late']) && $member['status']['is_late']): ?>
                        <span class="text-xs text-amber-600 whitespace-nowrap">
                          <i class="fas fa-clock"></i> Late
                        </span>
                      <?php endif; ?>
                    </li>
                  <?php endforeach; ?>
                  <?php if (empty($housekeepingStaff)): ?>
                    <li class="text-center text-slate-500 py-4">No housekeeping staff found</li>
                  <?php endif; ?>
                </ul>
              </div>

              <!-- Maintenance Panel -->
              <div class="staff-panel p-4 hidden" id="panel-maintenance">
                <h3 class="text-xs font-medium text-slate-400 uppercase tracking-wider mb-3">Maintenance Staff</h3>
                <ul class="space-y-3 max-h-[400px] overflow-y-auto">
                  <?php foreach ($maintenanceStaff as $member): ?>
                    <li class="flex items-start justify-between p-2 hover:bg-slate-50 rounded cursor-pointer"
                      onclick="showStaffDetails('<?php echo $member['id']; ?>')">
                      <div class="flex items-start gap-3">
                        <div class="staff-avatar">
                          <?php echo strtoupper(substr($member['full_name'], 0, 1)); ?>
                        </div>
                        <div>
                          <div class="flex items-center gap-2">
                            <span class="font-medium text-sm"><?php echo htmlspecialchars($member['full_name']); ?></span>
                            <span class="hr-badge">HR</span>
                          </div>
                          <p class="text-xs text-slate-500"><?php echo htmlspecialchars($member['position']); ?></p>
                          <?php if (isset($member['status']['present']) && $member['status']['present']): ?>
                            <span class="text-xs text-green-600">
                              <span class="status-indicator status-present"></span>
                              On duty
                            </span>
                          <?php else: ?>
                            <span class="text-xs text-slate-400">
                              <span class="status-indicator status-absent"></span>
                              Off duty
                            </span>
                          <?php endif; ?>
                        </div>
                      </div>
                      <?php if (isset($member['status']['is_late']) && $member['status']['is_late']): ?>
                        <span class="text-xs text-amber-600 whitespace-nowrap">
                          <i class="fas fa-clock"></i> Late
                        </span>
                      <?php endif; ?>
                    </li>
                  <?php endforeach; ?>
                  <?php if (empty($maintenanceStaff)): ?>
                    <li class="text-center text-slate-500 py-4">No maintenance staff found</li>
                  <?php endif; ?>
                </ul>
              </div>

              <div class="p-4 border-t border-slate-200">
                <button class="w-full border border-amber-600 text-amber-700 rounded-xl py-2 text-sm hover:bg-amber-50"
                  onclick="refreshStaffData()">
                  <i class="fas fa-rotate mr-2"></i>Refresh Staff Data
                </button>
              </div>
            </div>

            <!-- linen / supplies summary -->
            <div class="bg-white rounded-2xl border border-slate-200 p-5">
              <h3 class="font-semibold flex items-center gap-2 mb-2"><i
                  class="fas fa-basket-shopping text-amber-600"></i> linen & supplies</h3>
              <div class="space-y-2 text-sm">
                <?php foreach ($supplies as $supply): ?>
                  <div class="flex justify-between items-center p-2 hover:bg-slate-50 rounded cursor-pointer"
                    onclick="updateSupply(<?php echo $supply['id']; ?>, '<?php echo htmlspecialchars($supply['item_name']); ?>', <?php echo $supply['stock']; ?>)">
                    <span><?php echo htmlspecialchars($supply['item_name']); ?></span>
                    <span
                      class="<?php echo $supply['stock'] <= $supply['reorder_level'] ? 'text-amber-600 font-medium' : 'text-green-600'; ?>">
                      <?php echo $supply['stock']; ?>   <?php echo $supply['unit']; ?>
                      <?php if ($supply['stock'] <= $supply['reorder_level']): ?>
                        <i class="fas fa-triangle-exclamation ml-1" title="Low stock"></i>
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
          <h2 class="font-semibold text-lg flex items-center gap-2 mb-3"><i class="fas fa-building text-amber-600"></i>
            floor status summary</h2>
          <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-3">
            <?php foreach ($floorStats as $floor): ?>
              <div class="border rounded-xl p-3 text-center cursor-pointer hover:shadow-md transition"
                onclick="showFloorDetails('<?php echo $floor['floor']; ?>')">
                <span class="text-xs text-slate-500">Floor <?php echo $floor['floor']; ?></span>
                <div class="flex justify-center gap-1 mt-2 flex-wrap">
                  <?php
                  $maintenance = intval($floor['maintenance'] ?? 0);
                  $occupied = intval($floor['occupied'] ?? 0);
                  $dirty = intval($floor['dirty'] ?? 0);
                  $clean = $floor['total_rooms'] - $maintenance - $occupied - $dirty;

                  // Show dots in order: maintenance (red), occupied (blue), dirty (amber), clean (green)
                  for ($i = 0; $i < $maintenance; $i++): ?>
                    <span class="bg-rose-500 w-3 h-3 rounded-full" title="Maintenance"></span>
                  <?php endfor; ?>
                  <?php for ($i = 0; $i < $occupied; $i++): ?>
                    <span class="bg-blue-500 w-3 h-3 rounded-full" title="Occupied"></span>
                  <?php endfor; ?>
                  <?php for ($i = 0; $i < $dirty; $i++): ?>
                    <span class="bg-amber-500 w-3 h-3 rounded-full" title="Dirty - Needs Cleaning"></span>
                  <?php endfor; ?>
                  <?php for ($i = 0; $i < $clean; $i++): ?>
                    <span class="bg-green-500 w-3 h-3 rounded-full" title="Clean"></span>
                  <?php endfor; ?>
                </div>
                <div class="text-xs mt-1 text-slate-400">
                  <span class="text-amber-600"><?php echo $dirty; ?> dirty</span>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
          <div class="flex gap-4 mt-3 text-xs text-slate-500 flex-wrap">
            <span><span class="inline-block w-3 h-3 bg-green-500 rounded-full mr-1"></span> clean</span>
            <span><span class="inline-block w-3 h-3 bg-blue-500 rounded-full mr-1"></span> occupied</span>
            <span><span class="inline-block w-3 h-3 bg-amber-500 rounded-full mr-1"></span> dirty</span>
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

    <!-- Assign Task Modal (updated for HR staff) -->
    <div id="assignTaskModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
      <div class="bg-white rounded-2xl p-6 max-w-md w-full mx-4">
        <h3 class="text-lg font-semibold mb-4">Assign Task to Staff</h3>
        <input type="hidden" id="assignTaskId">

        <!-- Staff Type Tabs -->
        <div class="flex gap-2 mb-4">
          <button class="staff-type-tab active px-3 py-1 text-sm rounded-lg bg-amber-600 text-white"
            data-type="housekeeping">
            Housekeeping
          </button>
          <button class="staff-type-tab px-3 py-1 text-sm rounded-lg border border-slate-200 hover:bg-slate-50"
            data-type="maintenance">
            Maintenance
          </button>
          <button class="staff-type-tab px-3 py-1 text-sm rounded-lg border border-slate-200 hover:bg-slate-50"
            data-type="all">
            All Staff
          </button>
        </div>

        <!-- Staff List Container -->
        <div id="staffListContainer" class="max-h-96 overflow-y-auto mb-4 border border-slate-200 rounded-xl p-2">
          <!-- Dynamically loaded via AJAX -->
          <div class="text-center text-slate-500 py-4">
            <i class="fa-solid fa-spinner fa-spin"></i> Loading staff...
          </div>
        </div>

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

          <!-- Staff List with Workload -->
          <div>
            <h4 class="font-medium mb-3">Available Staff</h4>
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
            <i class="fas fa-rotate mr-2"></i>Refresh
          </button>
        </div>
      </div>
    </div>

    <!-- Staff Details Modal -->
    <div id="staffDetailsModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
      <div class="bg-white rounded-2xl p-6 max-w-md w-full mx-4">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-xl font-semibold">Staff Details</h3>
          <button onclick="closeStaffDetailsModal()" class="text-slate-400 hover:text-slate-600">
            <i class="fa-solid fa-xmark text-2xl"></i>
          </button>
        </div>
        <div id="staffDetailsContent" class="space-y-4">
          <!-- Populated by JavaScript -->
        </div>
      </div>
    </div>

    <!-- Assign Cleaning Task Modal -->
    <div id="assignCleaningModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
      <div class="bg-white rounded-2xl p-6 max-w-md w-full mx-4">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-xl font-semibold">Assign Cleaning Task</h3>
          <button onclick="closeAssignCleaningModal()" class="text-slate-400 hover:text-slate-600">
            <i class="fa-solid fa-xmark text-2xl"></i>
          </button>
        </div>

        <input type="hidden" id="assignCleaningRoom">

        <div class="mb-4">
          <label class="block text-sm text-slate-600 mb-2">Room Number</label>
          <p id="cleaningRoomDisplay" class="font-medium text-lg bg-slate-50 p-2 rounded-lg"></p>
        </div>

        <!-- Staff Type Tabs for Cleaning -->
        <div class="flex gap-2 mb-4">
          <button class="cleaning-staff-tab active px-3 py-1 text-sm rounded-lg bg-amber-600 text-white"
            data-type="housekeeping">
            Housekeeping
          </button>
          <button class="cleaning-staff-tab px-3 py-1 text-sm rounded-lg border border-slate-200 hover:bg-slate-50"
            data-type="all">
            All Staff
          </button>
        </div>

        <!-- Staff List Container for Cleaning -->
        <div id="cleaningStaffListContainer"
          class="max-h-96 overflow-y-auto mb-4 border border-slate-200 rounded-xl p-2">
          <div class="text-center text-slate-500 py-4">
            <i class="fa-solid fa-spinner fa-spin"></i> Loading staff...
          </div>
        </div>

        <div class="mb-4">
          <label class="block text-sm text-slate-600 mb-1">Notes (Optional)</label>
          <textarea id="cleaningNotes" rows="2" class="w-full border rounded-xl p-2"
            placeholder="Add any special instructions..."></textarea>
        </div>

        <div class="flex gap-3">
          <button onclick="closeAssignCleaningModal()"
            class="flex-1 border border-slate-200 px-4 py-2 rounded-xl hover:bg-slate-50">Cancel</button>
          <button onclick="submitCleaningAssignment()"
            class="flex-1 bg-amber-600 text-white px-4 py-2 rounded-xl hover:bg-amber-700">Assign</button>
        </div>
      </div>
    </div>

    <script>
      // Pass PHP data to JavaScript
      const currentFilter = '<?php echo $statusFilter; ?>';
      const currentSearch = '<?php echo $searchFilter; ?>';
      const hrApiConnected = <?php echo $hr_api_connected ? 'true' : 'false'; ?>;

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
        // Update tabs
        setActiveFilterTab(status);

        // Filter tasks
        filterTasks(status);

        // Update URL
        const url = new URL(window.location);
        if (status !== 'all') {
          url.searchParams.set('status', status);
        } else {
          url.searchParams.delete('status');
        }
        window.history.pushState({}, '', url);
      }

      // Filter tasks by status
      function filterTasks(status) {
        // Filter maintenance tasks
        const maintenanceRows = document.querySelectorAll('#taskTableBody tr');
        let visibleCount = 0;

        maintenanceRows.forEach(row => {
          const taskStatus = row.getAttribute('data-status');
          let matches = false;

          if (status === 'all') {
            matches = true;
          } else if (status === 'clean') {
            matches = taskStatus === 'clean' || taskStatus === 'completed';
          } else if (status === 'dirty') {
            matches = taskStatus === 'pending';
          } else if (status === 'maintenance') {
            matches = taskStatus === 'maintenance';
          } else if (status === 'in-progress') {
            matches = taskStatus === 'in-progress';
          } else {
            matches = taskStatus === status;
          }

          if (matches) {
            row.style.display = '';
            visibleCount++;
          } else {
            row.style.display = 'none';
          }
        });

        // Filter cleaning tasks if they exist in a separate table
        const cleaningRows = document.querySelectorAll('#cleaningTableBody tr');
        if (cleaningRows.length > 0) {
          cleaningRows.forEach(row => {
            const taskStatus = row.getAttribute('data-status');
            let matches = false;

            if (status === 'all') {
              matches = true;
            } else if (status === 'dirty') {
              matches = taskStatus === 'pending';
            } else {
              matches = false; // Cleaning tasks only show in 'all' and 'dirty' filters
            }

            if (matches) {
              row.style.display = '';
              visibleCount++;
            } else {
              row.style.display = 'none';
            }
          });
        }

        // Show "no results" message if needed
        const noResultsRow = document.getElementById('noFilterResults');
        if (visibleCount === 0) {
          if (!noResultsRow) {
            const tbody = document.getElementById('taskTableBody');
            const tr = document.createElement('tr');
            tr.id = 'noFilterResults';
            tr.innerHTML = '<td colspan="6" class="p-8 text-center text-slate-500">No tasks match the selected filter</td>';
            tbody.appendChild(tr);
          }
        } else if (noResultsRow) {
          noResultsRow.remove();
        }
      }

      // Update active tab styling
      function setActiveFilterTab(status) {
        document.querySelectorAll('.filter-tab').forEach(tab => {
          const tabStatus = tab.getAttribute('data-filter');
          if (tabStatus === status) {
            tab.classList.add('bg-amber-600', 'text-white');
            tab.classList.remove('border', 'border-slate-200', 'hover:bg-slate-50');
          } else {
            tab.classList.remove('bg-amber-600', 'text-white');
            tab.classList.add('border', 'border-slate-200', 'hover:bg-slate-50');
          }
        });
      }

      // Search
      document.getElementById('roomSearchInput').addEventListener('keyup', function () {
        const searchTerm = this.value.toLowerCase().trim();

        // Search in maintenance tasks
        const maintenanceRows = document.querySelectorAll('#taskTableBody tr');
        const currentFilter = document.querySelector('.filter-tab.bg-amber-600')?.dataset.filter || 'all';

        maintenanceRows.forEach(row => {
          const roomNumber = row.getAttribute('data-room')?.toLowerCase() || '';
          const taskStatus = row.getAttribute('data-status');

          const matchesFilter = currentFilter === 'all' || taskStatus === currentFilter;
          const matchesSearch = searchTerm === '' || roomNumber.includes(searchTerm);

          row.style.display = matchesFilter && matchesSearch ? '' : 'none';
        });

        // Search in cleaning tasks
        const cleaningRows = document.querySelectorAll('#cleaningTableBody tr');
        if (cleaningRows.length > 0) {
          cleaningRows.forEach(row => {
            const roomNumber = row.getAttribute('data-room')?.toLowerCase() || '';
            const taskStatus = row.getAttribute('data-status');

            const matchesFilter = currentFilter === 'all' || currentFilter === 'dirty';
            const matchesSearch = searchTerm === '' || roomNumber.includes(searchTerm);

            row.style.display = matchesFilter && matchesSearch ? '' : 'none';
          });
        }

        // Update URL
        const url = new URL(window.location);
        if (this.value.trim()) {
          url.searchParams.set('search', this.value.trim());
        } else {
          url.searchParams.delete('search');
        }
        window.history.pushState({}, '', url);
      });

      // Filter tabs
      document.querySelectorAll('.filter-tab').forEach(btn => {
        btn.addEventListener('click', function () {
          const status = this.dataset.filter;
          filterTasks(status);
          setActiveFilterTab(status);

          const url = new URL(window.location);
          if (status !== 'all') {
            url.searchParams.set('status', status);
          } else {
            url.searchParams.delete('status');
          }
          window.history.pushState({}, '', url);
        });
      });

      // Staff tabs
      document.querySelectorAll('.staff-tab').forEach(tab => {
        tab.addEventListener('click', function () {
          document.querySelectorAll('.staff-tab').forEach(t => {
            t.classList.remove('active', 'text-amber-600', 'border-b-2', 'border-amber-600');
            t.classList.add('text-slate-500');
          });
          this.classList.add('active', 'text-amber-600', 'border-b-2', 'border-amber-600');
          this.classList.remove('text-slate-500');

          const tabName = this.dataset.tab;
          document.querySelectorAll('.staff-panel').forEach(panel => {
            panel.classList.add('hidden');
          });
          document.getElementById(`panel-${tabName}`).classList.remove('hidden');
        });
      });

      // ========== MAINTENANCE TASK FUNCTIONS ==========
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

      function updateTaskStatus(taskId, currentStatus) {
        if (currentStatus === 'clean' || currentStatus === 'completed') {
          showToast('This task is already completed', 'info');
          return;
        }

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

      function assignTask(taskId) {
        const taskRow = document.querySelector(`tr[data-task-id="${taskId}"]`);
        const statusCell = taskRow?.querySelector('td:nth-child(2) span');
        const status = statusCell?.textContent.trim().toLowerCase();

        if (status === 'clean' || status === 'completed') {
          showToast('Cannot assign completed tasks', 'error');
          return;
        }

        document.getElementById('assignTaskId').value = taskId;
        document.getElementById('assignTaskModal').classList.remove('hidden');
        document.getElementById('assignTaskModal').classList.add('flex');
        loadStaffForAssignment('housekeeping');
      }

      function loadStaffForAssignment(type = 'housekeeping') {
        const container = document.getElementById('staffListContainer');
        container.innerHTML = '<div class="text-center text-slate-500 py-4"><i class="fa-solid fa-spinner fa-spin"></i> Loading staff...</div>';

        let action = 'get_hotel_staff';
        if (type === 'housekeeping') {
          action = 'get_housekeeping_staff';
        } else if (type === 'maintenance') {
          action = 'get_maintenance_staff';
        }

        const formData = new FormData();
        formData.append('action', action);

        fetch('../../../controller/admin/post/housekeeping_actions.php', {
          method: 'POST',
          body: formData
        })
          .then(response => response.json())
          .then(data => {
            if (data.success && data.staff) {
              let html = '';
              data.staff.forEach(staff => {
                const statusClass = staff.present ? 'text-green-600' : 'text-slate-400';
                const statusDot = staff.present ?
                  '<span class="status-indicator status-present"></span>' :
                  '<span class="status-indicator status-absent"></span>';

                html += `
                            <label class="flex items-center p-3 hover:bg-slate-50 rounded-lg cursor-pointer border-b last:border-b-0">
                                <input type="radio" name="selectedStaff" value="${staff.id}" class="mr-3 text-amber-600 focus:ring-amber-500">
                                <div class="flex-1 flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="staff-avatar">
                                            ${staff.full_name.charAt(0).toUpperCase()}
                                        </div>
                                        <div>
                                            <p class="font-medium text-sm">${staff.full_name}</p>
                                            <p class="text-xs text-slate-500">${staff.position || 'Staff'}</p>
                                        </div>
                                    </div>
                                    <span class="text-xs ${statusClass}">
                                        ${statusDot}
                                        ${staff.present ? 'On Duty' : 'Off Duty'}
                                    </span>
                                </div>
                            </label>
                        `;
              });
              container.innerHTML = html || '<div class="text-center text-slate-500 py-4">No staff available</div>';
            } else {
              container.innerHTML = '<div class="text-center text-red-500 py-4">Failed to load staff</div>';
            }
          })
          .catch(error => {
            container.innerHTML = '<div class="text-center text-red-500 py-4">Error loading staff</div>';
            console.error('Error:', error);
          });
      }

      // Staff type tabs in assign modal
      document.querySelectorAll('.staff-type-tab').forEach(tab => {
        tab.addEventListener('click', function () {
          document.querySelectorAll('.staff-type-tab').forEach(t => {
            t.classList.remove('active', 'bg-amber-600', 'text-white');
            t.classList.add('border', 'border-slate-200', 'hover:bg-slate-50');
          });
          this.classList.add('active', 'bg-amber-600', 'text-white');
          this.classList.remove('border', 'border-slate-200', 'hover:bg-slate-50');

          loadStaffForAssignment(this.dataset.type);
        });
      });

      function closeAssignTaskModal() {
        document.getElementById('assignTaskModal').classList.add('hidden');
        document.getElementById('assignTaskModal').classList.remove('flex');
      }

      function submitTaskAssignment() {
        const taskId = document.getElementById('assignTaskId').value;
        const selectedStaff = document.querySelector('input[name="selectedStaff"]:checked');

        if (!selectedStaff) {
          showToast('Please select a staff member', 'error');
          return;
        }

        const employeeId = selectedStaff.value;

        Swal.fire({
          title: 'Assigning...',
          text: 'Please wait',
          allowOutsideClick: false,
          didOpen: () => { Swal.showLoading(); }
        });

        const formData = new FormData();
        formData.append('action', 'assign_task');
        formData.append('task_id', taskId);
        formData.append('employee_id', employeeId);

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

      function viewMaintenanceDetails(maintenanceId) {
        viewTaskDetails(maintenanceId);
      }

      // ========== CLEANING TASK FUNCTIONS ==========
      function viewDirtyRoomDetails(roomNumber) {
        Swal.fire({
          title: 'Room ' + roomNumber,
          html: `
                <div class="text-left">
                    <p class="mb-2"><strong>Status:</strong> Needs Cleaning</p>
                    <p class="mb-2"><strong>Priority:</strong> Medium</p>
                    <p class="mb-2"><strong>Reported:</strong> After guest checkout</p>
                    <p class="mb-2"><strong>Description:</strong> Room requires housekeeping service</p>
                </div>
            `,
          icon: 'info',
          confirmButtonColor: '#d97706'
        });
      }

      function markRoomAsClean(roomNumber) {
        Swal.fire({
          title: 'Mark Room as Clean',
          text: `Mark room ${roomNumber} as clean and ready for guests?`,
          icon: 'question',
          showCancelButton: true,
          confirmButtonColor: '#d97706',
          cancelButtonColor: '#6b7280',
          confirmButtonText: 'Yes, mark clean'
        }).then((result) => {
          if (result.isConfirmed) {
            Swal.fire({
              title: 'Processing...',
              text: 'Please wait',
              allowOutsideClick: false,
              didOpen: () => { Swal.showLoading(); }
            });

            const formData = new FormData();
            formData.append('action', 'mark_as_clean');
            formData.append('room_id', roomNumber);

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
        });
      }

      function assignCleaningTask(roomNumber) {
        document.getElementById('assignCleaningRoom').value = roomNumber;
        document.getElementById('cleaningRoomDisplay').textContent = roomNumber;
        document.getElementById('assignCleaningModal').classList.remove('hidden');
        document.getElementById('assignCleaningModal').classList.add('flex');
        loadCleaningStaff('housekeeping');
      }

      function closeAssignCleaningModal() {
        document.getElementById('assignCleaningModal').classList.add('hidden');
        document.getElementById('assignCleaningModal').classList.remove('flex');
        document.getElementById('cleaningNotes').value = '';
      }

      function loadCleaningStaff(type = 'housekeeping') {
        const container = document.getElementById('cleaningStaffListContainer');
        container.innerHTML = '<div class="text-center text-slate-500 py-4"><i class="fa-solid fa-spinner fa-spin"></i> Loading staff...</div>';

        let action = 'get_hotel_staff';
        if (type === 'housekeeping') {
          action = 'get_housekeeping_staff';
        }

        const formData = new FormData();
        formData.append('action', action);

        fetch('../../../controller/admin/post/housekeeping_actions.php', {
          method: 'POST',
          body: formData
        })
          .then(response => response.json())
          .then(data => {
            if (data.success && data.staff) {
              let html = '';
              data.staff.forEach(staff => {
                const statusClass = staff.present ? 'text-green-600' : 'text-slate-400';
                const statusDot = staff.present ?
                  '<span class="status-indicator status-present"></span>' :
                  '<span class="status-indicator status-absent"></span>';

                html += `
                            <label class="flex items-center p-3 hover:bg-slate-50 rounded-lg cursor-pointer border-b last:border-b-0">
                                <input type="radio" name="cleaningStaff" value="${staff.id}" class="mr-3 text-amber-600 focus:ring-amber-500">
                                <div class="flex-1 flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="staff-avatar">
                                            ${staff.full_name.charAt(0).toUpperCase()}
                                        </div>
                                        <div>
                                            <p class="font-medium text-sm">${staff.full_name}</p>
                                            <p class="text-xs text-slate-500">${staff.position || 'Staff'}</p>
                                        </div>
                                    </div>
                                    <span class="text-xs ${statusClass}">
                                        ${statusDot}
                                        ${staff.present ? 'On Duty' : 'Off Duty'}
                                    </span>
                                </div>
                            </label>
                        `;
              });
              container.innerHTML = html || '<div class="text-center text-slate-500 py-4">No staff available</div>';
            } else {
              container.innerHTML = '<div class="text-center text-red-500 py-4">Failed to load staff</div>';
            }
          })
          .catch(error => {
            container.innerHTML = '<div class="text-center text-red-500 py-4">Error loading staff</div>';
            console.error('Error:', error);
          });
      }

      document.querySelectorAll('.cleaning-staff-tab').forEach(tab => {
        tab.addEventListener('click', function () {
          document.querySelectorAll('.cleaning-staff-tab').forEach(t => {
            t.classList.remove('active', 'bg-amber-600', 'text-white');
            t.classList.add('border', 'border-slate-200', 'hover:bg-slate-50');
          });
          this.classList.add('active', 'bg-amber-600', 'text-white');
          this.classList.remove('border', 'border-slate-200', 'hover:bg-slate-50');

          loadCleaningStaff(this.dataset.type);
        });
      });

      function submitCleaningAssignment() {
        const roomNumber = document.getElementById('assignCleaningRoom').value;
        const selectedStaff = document.querySelector('input[name="cleaningStaff"]:checked');
        const notes = document.getElementById('cleaningNotes').value.trim();

        if (!selectedStaff) {
          showToast('Please select a staff member', 'error');
          return;
        }

        const employeeId = selectedStaff.value;

        Swal.fire({
          title: 'Assigning...',
          text: 'Please wait',
          allowOutsideClick: false,
          didOpen: () => { Swal.showLoading(); }
        });

        const formData = new FormData();
        formData.append('action', 'assign_cleaning_task');
        formData.append('room_number', roomNumber);
        formData.append('employee_id', employeeId);
        formData.append('notes', notes || 'Room needs cleaning');

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

        closeAssignCleaningModal();
      }

      // ========== SUPPLY FUNCTIONS ==========
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

      // ========== STAFF FUNCTIONS ==========
      function showStaffDetails(staffId) {
        const allStaff = <?php echo json_encode($allStaff); ?>;
        const staff = allStaff.find(s => s.id == staffId);

        if (!staff) {
          showToast('Staff details not found', 'error');
          return;
        }

        const content = document.getElementById('staffDetailsContent');
        const status = staff.status || {};
        const schedule = staff.schedule || {};
        const attendance = staff.attendance || {};

        content.innerHTML = `
            <div class="text-center mb-4">
                <div class="staff-avatar w-16 h-16 text-2xl mx-auto mb-2">
                    ${staff.full_name.charAt(0).toUpperCase()}
                </div>
                <h4 class="font-semibold text-lg">${staff.full_name}</h4>
                <p class="text-sm text-slate-500">${staff.position}</p>
                <span class="hr-badge inline-block mt-1">HR System</span>
            </div>
            
            <div class="grid grid-cols-2 gap-3 text-sm">
                <div>
                    <p class="text-xs text-slate-500">Department</p>
                    <p>${staff.department || 'Hotel'}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-500">Employee ID</p>
                    <p>${staff.id}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-500">Email</p>
                    <p class="truncate">${staff.email || 'N/A'}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-500">Phone</p>
                    <p>${staff.phone || 'N/A'}</p>
                </div>
            </div>

            <div class="border-t pt-3">
                <p class="text-xs text-slate-500 mb-2">Current Status</p>
                <div class="bg-slate-50 p-3 rounded-lg">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm">Attendance:</span>
                        <span class="${status.present ? 'text-green-600' : 'text-slate-400'}">
                            ${status.present ? 'On Duty' : 'Off Duty'}
                            ${status.is_late ? '<span class="text-amber-600 ml-2">(Late)</span>' : ''}
                        </span>
                    </div>
                    ${schedule ? `
                        <div class="flex items-center justify-between text-sm">
                            <span>Shift:</span>
                            <span>${schedule.shift_name || 'Regular'} (${schedule.start_time || '09:00'} - ${schedule.end_time || '18:00'})</span>
                        </div>
                    ` : ''}
                    ${attendance && attendance.clock_in ? `
                        <div class="flex items-center justify-between text-sm mt-2">
                            <span>Clock In:</span>
                            <span>${new Date(attendance.clock_in).toLocaleTimeString()}</span>
                        </div>
                    ` : ''}
                </div>
            </div>

            <div class="border-t pt-3">
                <p class="text-xs text-slate-500 mb-2">Hourly Rate</p>
                <p class="text-lg font-semibold text-amber-600">₱${parseFloat(staff.hourly_rate || 0).toFixed(2)}/hr</p>
            </div>
        `;

        document.getElementById('staffDetailsModal').classList.remove('hidden');
        document.getElementById('staffDetailsModal').classList.add('flex');
      }

      function closeStaffDetailsModal() {
        document.getElementById('staffDetailsModal').classList.add('hidden');
        document.getElementById('staffDetailsModal').classList.remove('flex');
      }

      function refreshStaffData() {
        showToast('Refreshing staff data...', 'info');
        setTimeout(() => {
          location.reload();
        }, 1000);
      }

      // ========== MAINTENANCE REQUEST FUNCTIONS ==========
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

      // ========== ASSIGN TASKS MODAL FUNCTIONS ==========
      function openAssignTasksModal() {
        document.getElementById('assignTasksModal').classList.remove('hidden');
        document.getElementById('assignTasksModal').classList.add('flex');
        loadPendingTasks();
        loadStaffWorkload();
      }

      function closeAssignTasksModal() {
        document.getElementById('assignTasksModal').classList.add('hidden');
        document.getElementById('assignTasksModal').classList.remove('flex');
      }

      function loadPendingTasks() {
        const container = document.getElementById('pendingTasksList');

        const formData = new FormData();
        formData.append('action', 'get_pending_tasks');

        fetch('../../../controller/admin/post/housekeeping_actions.php', {
          method: 'POST',
          body: formData
        })
          .then(response => response.json())
          .then(data => {
            let html = '';

            if (data.success && data.tasks) {
              data.tasks.forEach(task => {
                const priorityColor = task.priority_level === 'high' ? 'bg-red-100 text-red-700' :
                  task.priority_level === 'medium' ? 'bg-amber-100 text-amber-700' :
                    'bg-green-100 text-green-700';

                html += `
                            <div class="border rounded-lg p-3 hover:shadow-md transition cursor-pointer" onclick="quickAssignTask(${task.id})">
                                <div class="flex justify-between items-start mb-2">
                                    <span class="font-medium">Room ${task.room_number}</span>
                                    <span class="px-2 py-0.5 rounded-full text-xs font-medium ${priorityColor}">
                                        ${task.priority_level}
                                    </span>
                                </div>
                                <p class="text-xs text-slate-500 truncate">${task.notes || 'Maintenance required'}</p>
                            </div>
                        `;
              });
            }

            // Add dirty rooms from PHP
            <?php if (!empty($dirtyRooms)): ?>
              <?php foreach ($dirtyRooms as $dirtyRoom): ?>
                html += `
                            <div class="border rounded-lg p-3 hover:shadow-md transition cursor-pointer bg-amber-50" onclick="assignCleaningTask('<?php echo $dirtyRoom['room_number']; ?>')">
                                <div class="flex justify-between items-start mb-2">
                                    <span class="font-medium">Room <?php echo $dirtyRoom['room_number']; ?></span>
                                    <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">
                                        cleaning
                                    </span>
                                </div>
                                <p class="text-xs text-slate-500 truncate">Room needs cleaning</p>
                            </div>
                        `;
              <?php endforeach; ?>
            <?php endif; ?>

            container.innerHTML = html || '<div class="text-center text-slate-500 py-4">No pending tasks</div>';
          });
      }

      function loadStaffWorkload() {
        const container = document.getElementById('staffWorkloadList');

        const formData = new FormData();
        formData.append('action', 'get_hotel_staff');

        fetch('../../../controller/admin/post/housekeeping_actions.php', {
          method: 'POST',
          body: formData
        })
          .then(response => response.json())
          .then(data => {
            if (data.success && data.staff) {
              let html = '';
              data.staff.forEach(staff => {
                const statusClass = staff.present ? 'text-green-600' : 'text-slate-400';
                const statusDot = staff.present ?
                  '<span class="status-indicator status-present"></span>' :
                  '<span class="status-indicator status-absent"></span>';

                html += `
                            <div class="border rounded-lg p-3 hover:shadow-md transition">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center gap-2">
                                        <div class="staff-avatar">
                                            ${staff.full_name.charAt(0).toUpperCase()}
                                        </div>
                                        <div>
                                            <p class="font-medium text-sm">${staff.full_name}</p>
                                            <p class="text-xs text-slate-500">${staff.position || 'Staff'}</p>
                                        </div>
                                    </div>
                                    <span class="text-xs ${statusClass}">
                                        ${statusDot}
                                        ${staff.present ? 'On Duty' : 'Off Duty'}
                                    </span>
                                </div>
                                <div class="text-xs text-slate-500">
                                    <span class="hr-badge">HR</span>
                                </div>
                            </div>
                        `;
              });
              container.innerHTML = html || '<div class="text-center text-slate-500 py-4">No staff available</div>';
            }
          });
      }

      function quickAssignTask(taskId) {
        closeAssignTasksModal();
        assignTask(taskId);
      }

      function refreshTaskData() {
        loadPendingTasks();
        loadStaffWorkload();
        showToast('Data refreshed', 'success');
      }

      // ========== FLOOR DETAILS ==========
      function showFloorDetails(floor) {
        filterByStatus('all');
        document.getElementById('roomSearchInput').value = floor;
        setTimeout(() => {
          const event = new Event('keyup');
          document.getElementById('roomSearchInput').dispatchEvent(event);
        }, 100);
      }


      // ========== INITIALIZE ON PAGE LOAD ==========
      document.addEventListener('DOMContentLoaded', function () {
        const urlParams = new URLSearchParams(window.location.search);
        const initialFilter = urlParams.get('status') || 'all';

        setActiveFilterTab(initialFilter);
        filterTasks(initialFilter);

        const searchParam = urlParams.get('search');
        if (searchParam) {
          document.getElementById('roomSearchInput').value = searchParam;
          setTimeout(() => {
            const event = new Event('keyup');
            document.getElementById('roomSearchInput').dispatchEvent(event);
          }, 100);
        }
      });

      // ========== CLOSE MODALS ON OUTSIDE CLICK ==========
      window.onclick = function (event) {
        const taskModal = document.getElementById('taskModal');
        const assignTaskModal = document.getElementById('assignTaskModal');
        const assignCleaningModal = document.getElementById('assignCleaningModal');
        const addMaintenanceModal = document.getElementById('addMaintenanceModal');
        const supplyModal = document.getElementById('supplyModal');
        const assignTasksModal = document.getElementById('assignTasksModal');
        const staffDetailsModal = document.getElementById('staffDetailsModal');

        if (taskModal && event.target === taskModal) {
          closeTaskModal();
        }
        if (assignTaskModal && event.target === assignTaskModal) {
          closeAssignTaskModal();
        }
        if (assignCleaningModal && event.target === assignCleaningModal) {
          closeAssignCleaningModal();
        }
        if (addMaintenanceModal && event.target === addMaintenanceModal) {
          closeAddMaintenanceModal();
        }
        if (supplyModal && event.target === supplyModal) {
          closeSupplyModal();
        }
        if (assignTasksModal && event.target === assignTasksModal) {
          closeAssignTasksModal();
        }
        if (staffDetailsModal && event.target === staffDetailsModal) {
          closeStaffDetailsModal();
        }
      }
    </script>
  </body>

</html>