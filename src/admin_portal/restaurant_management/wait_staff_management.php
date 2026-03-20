<?php
/**
 * View - Admin Wait Staff Management
 */
require_once '../../../controller/admin/get/restaurant/wait_staff_management.php';

// Set current page for navigation
$current_page = 'wait_staff_management';
?>
<!DOCTYPE html>
<html lang="en">

  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin · Wait Staff Management</title>
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
        max-width: 600px;
        width: 90%;
        max-height: 85vh;
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

      .hidden-row {
        display: none;
      }

      .status-badge {
        transition: all 0.2s;
      }

      .status-badge:hover {
        opacity: 0.8;
        transform: scale(1.05);
      }

      .hr-api-badge {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      }

      .refresh-btn {
        transition: all 0.3s;
      }

      .refresh-btn:hover {
        transform: rotate(180deg);
      }

      .attendance-indicator {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 4px;
      }

      /* Star Rating Styles */
      .rating-star {
        cursor: pointer;
        transition: all 0.2s;
      }

      .rating-star.active {
        color: #fbbf24;
      }

      .rating-star.active i {
        font-family: "Font Awesome 6 Free";
        font-weight: 900;
      }

      .rating-star:hover {
        transform: scale(1.1);
      }

      .rating-display {
        display: inline-flex;
        gap: 2px;
      }

      .rating-display i {
        font-size: 12px;
      }

      .average-rating {
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
      }
    </style>
  </head>

  <body class="bg-white font-sans antialiased">

    <!-- Toast notification container -->
    <div id="toast" class="toast hidden">
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

    <!-- Add Note Modal with Rating -->
    <div id="noteModal" class="modal">
      <div class="modal-content p-6">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-xl font-semibold">Add Staff Note & Rating</h3>
          <button onclick="closeModal('noteModal')" class="text-slate-400 hover:text-slate-600">
            <i class="fa-solid fa-xmark text-2xl"></i>
          </button>
        </div>
        <form id="noteForm" onsubmit="saveNote(event)">
          <input type="hidden" id="noteEmployeeId">
          <input type="hidden" id="noteEmployeeName">

          <div class="mb-4">
            <label class="block text-sm font-medium text-slate-700 mb-2">Rating (Optional)</label>
            <div class="flex gap-2 mb-3">
              <select id="ratingType" class="border border-slate-200 rounded-lg px-3 py-2 text-sm flex-1">
                <option value="overall">Overall Performance</option>
                <option value="performance">Work Performance</option>
                <option value="attitude">Attitude & Teamwork</option>
                <option value="punctuality">Punctuality & Attendance</option>
              </select>
            </div>
            <div class="flex gap-2 justify-center py-2" id="starRating">
              <?php for ($i = 1; $i <= 5; $i++): ?>
                <button type="button" data-rating="<?php echo $i; ?>"
                  class="rating-star text-3xl text-gray-300 hover:text-yellow-400 transition">
                  <i class="fa-regular fa-star"></i>
                </button>
              <?php endfor; ?>
            </div>
            <input type="hidden" id="noteRating" value="">
            <p class="text-xs text-slate-400 text-center mt-2">Click on stars to rate</p>
          </div>

          <div class="mb-4">
            <label class="block text-sm font-medium text-slate-700 mb-1">Note / Feedback</label>
            <textarea id="noteContent" rows="4" required
              placeholder="Write your feedback, observations, or notes here..."
              class="w-full border border-slate-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-amber-500"></textarea>
          </div>

          <div class="flex gap-3 mt-4">
            <button type="submit" class="flex-1 bg-amber-600 text-white py-2 rounded-lg hover:bg-amber-700">
              <i class="fas fa-save mr-2"></i>Save Note
            </button>
            <button type="button" onclick="closeModal('noteModal')"
              class="flex-1 border border-slate-200 py-2 rounded-lg hover:bg-slate-50">
              Cancel
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- View Notes Modal with Rating Display -->
    <div id="viewNotesModal" class="modal">
      <div class="modal-content p-6">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-xl font-semibold" id="viewNotesTitle">Staff Notes & Ratings</h3>
          <button onclick="closeModal('viewNotesModal')" class="text-slate-400 hover:text-slate-600">
            <i class="fa-solid fa-xmark text-2xl"></i>
          </button>
        </div>

        <!-- Average Rating Display -->
        <div id="avgRatingDisplay" class="mb-4 p-4 average-rating rounded-lg hidden">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
              <i class="fas fa-star text-yellow-500"></i>
              <span class="font-semibold text-slate-700">Average Rating:</span>
            </div>
            <div id="avgRatingStars" class="flex gap-1"></div>
            <span id="avgRatingValue" class="text-sm font-medium text-slate-700"></span>
          </div>
        </div>

        <!-- Notes List -->
        <div id="notesList" class="space-y-3 max-h-96 overflow-y-auto">
          <!-- Notes will be loaded here -->
        </div>

        <div class="flex gap-3 mt-4">
          <button onclick="closeModal('viewNotesModal')"
            class="flex-1 border border-slate-200 py-2 rounded-lg hover:bg-slate-50">
            Close
          </button>
        </div>
      </div>
    </div>

    <!-- Assign Tables Modal -->
    <div id="assignTablesModal" class="modal">
      <div class="modal-content p-6">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-xl font-semibold">Assign Tables</h3>
          <button onclick="closeModal('assignTablesModal')" class="text-slate-400 hover:text-slate-600">
            <i class="fa-solid fa-xmark text-2xl"></i>
          </button>
        </div>
        <form id="assignTablesForm" onsubmit="saveTableAssignment(event)">
          <input type="hidden" id="assignEmployeeId">

          <div class="mb-4">
            <label class="block text-sm font-medium text-slate-700 mb-1">Table Assignment</label>
            <input type="text" id="tableAssignment" placeholder="e.g., Tables 1-4 or Section A"
              class="w-full border border-slate-200 rounded-lg px-3 py-2" required>
          </div>

          <div class="mb-4">
            <label class="block text-sm font-medium text-slate-700 mb-1">Select Tables</label>
            <div class="grid grid-cols-5 gap-2" id="tableGrid">
              <!-- Table numbers 1-25 will be generated here -->
            </div>
            <p class="text-xs text-slate-400 mt-2">Click on table numbers to select/deselect</p>
          </div>

          <div class="flex gap-3 mt-4">
            <button type="submit" class="flex-1 bg-amber-600 text-white py-2 rounded-lg hover:bg-amber-700">
              <i class="fas fa-check mr-2"></i>Save Assignment
            </button>
            <button type="button" onclick="closeModal('assignTablesModal')"
              class="flex-1 border border-slate-200 py-2 rounded-lg hover:bg-slate-50">
              Cancel
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- APP CONTAINER -->
    <div class="min-h-screen flex flex-col lg:flex-row">

      <!-- ========== SIDEBAR ========== -->
      <?php require_once '../components/admin_nav.php'; ?>

      <!-- ========== MAIN CONTENT ========== -->
      <main class="flex-1 p-5 lg:p-8 overflow-y-auto bg-white">

        <!-- header with title and date -->
        <div class="flex flex-wrap items-center justify-between gap-3 mb-6">

          <!-- LEFT SIDE -->
          <div>
            <h1 class="text-2xl lg:text-3xl font-light text-slate-800">
              Wait Staff Management
            </h1>
            <p class="text-sm text-slate-500 mt-0.5">
              real-time staff data from HR system with performance ratings
            </p>
          </div>

          <!-- RIGHT SIDE -->
          <div class="flex items-center gap-3 text-sm">

            <!-- HR Status -->
            <?php if (!$hrApiConnected): ?>
              <span
                class="bg-red-100 text-red-700 border border-red-200 rounded-full px-4 py-2 flex items-center gap-2 shadow-sm">
                <i class="fas fa-circle-exclamation"></i> HR API Offline
              </span>
            <?php else: ?>
              <span
                class="bg-green-100 text-green-700 border border-green-200 rounded-full px-4 py-2 flex items-center gap-2 shadow-sm">
                <i class="fas fa-circle-check"></i> HR API Connected
              </span>
            <?php endif; ?>

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

        <!-- STATS CARDS from HR API -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-4 mb-8">
          <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs text-slate-500">Total staff</p>
            <p class="text-2xl font-semibold" id="totalStaff"><?php echo $summary['total_employees']; ?></p>
          </div>
          <div
            class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm cursor-pointer hover:shadow-md transition"
            onclick="filterByStatus('present')">
            <p class="text-xs text-slate-500">Present</p>
            <p class="text-2xl font-semibold text-green-600" id="presentCount"><?php echo $summary['present']; ?></p>
          </div>
          <div
            class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm cursor-pointer hover:shadow-md transition"
            onclick="filterByStatus('absent')">
            <p class="text-xs text-slate-500">Absent</p>
            <p class="text-2xl font-semibold text-red-600" id="absentCount"><?php echo $summary['absent']; ?></p>
          </div>
          <div
            class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm cursor-pointer hover:shadow-md transition"
            onclick="filterByStatus('late')">
            <p class="text-xs text-slate-500">Late</p>
            <p class="text-2xl font-semibold text-yellow-600" id="lateCount"><?php echo $summary['late']; ?></p>
          </div>
          <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs text-slate-500">No schedule</p>
            <p class="text-2xl font-semibold text-purple-600" id="noScheduleCount">
              <?php echo $summary['no_schedule']; ?>
            </p>
          </div>
          <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs text-slate-500">Tables assigned</p>
            <p class="text-2xl font-semibold" id="tablesAssigned">
              <?php echo $tablesAssigned; ?>/<?php echo $totalTables; ?>
            </p>
          </div>
        </div>

        <!-- ACTION BAR -->
        <div
          class="bg-white rounded-2xl border border-slate-200 p-4 mb-6 flex flex-wrap gap-3 items-center justify-between">
          <div class="flex gap-2 flex-wrap">
            <button onclick="refreshHRData()"
              class="border border-slate-200 px-4 py-2 rounded-xl text-sm hover:bg-slate-50 transition refresh-btn">
              <i class="fa-solid fa-rotate-right mr-1"></i> refresh from HR
            </button>
            <button onclick="openAssignTablesModal()"
              class="border border-slate-200 px-4 py-2 rounded-xl text-sm hover:bg-slate-50 transition">
              <i class="fas fa-table mr-1"></i>assign tables
            </button>
          </div>
          <div class="relative">
            <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
            <input type="text" id="searchInput" onkeyup="searchStaff()" placeholder="search staff..."
              class="border border-slate-200 rounded-xl pl-9 pr-4 py-2 text-sm w-64 focus:ring-1 focus:ring-amber-500 outline-none"
              value="<?php echo htmlspecialchars($searchFilter); ?>">
          </div>
        </div>

        <!-- STAFF LIST TABLE -->
        <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden mb-8">
          <div class="p-4 border-b border-slate-200 bg-slate-50 flex items-center justify-between">
            <h2 class="font-semibold flex items-center gap-2">
              <i class="fas fa-user text-amber-600"></i> restaurant staff roster
              <span class="text-xs bg-amber-100 text-amber-700 px-2 py-1 rounded-full">live from HR</span>
            </h2>
          </div>
          <div class="overflow-x-auto">
            <table class="w-full text-sm" id="staffTable">
              <thead class="text-slate-500 text-xs border-b">
                <tr>
                  <th class="p-3 text-left">Employee</th>
                  <th class="p-3 text-left">Position</th>
                  <th class="p-3 text-left">Schedule</th>
                  <th class="p-3 text-left">Status</th>
                  <th class="p-3 text-left">Attendance</th>
                  <th class="p-3 text-left">Rating</th>
                  <th class="p-3 text-left">Assigned tables</th>
                  <th class="p-3 text-left">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y" id="staffTableBody">
                <?php if (empty($staffMembers)): ?>
                  <tr>
                    <td colspan="8" class="p-8 text-center text-slate-500">No staff members found</td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($staffMembers as $staff):
                    $emp = $staff['employee'];
                    $shift = $staff['shift'];
                    $attendance = $staff['attendance'];
                    $status = $staff['status'];
                    $avgRating = $staff['avg_rating'] ?? null;

                    $statusColors = [
                      'present' => 'bg-green-100 text-green-700',
                      'absent' => 'bg-red-100 text-red-700',
                      'late' => 'bg-yellow-100 text-yellow-700',
                      'completed' => 'bg-blue-100 text-blue-700'
                    ];
                    $statusColor = $statusColors[$status['status']] ?? 'bg-gray-100 text-gray-700';

                    $fullName = $emp['full_name'] ?? 'Unknown';
                    $firstName = $emp['first_name'] ?? explode(' ', $fullName)[0];
                    $lastName = $emp['last_name'] ?? (explode(' ', $fullName)[1] ?? '');
                    $initials = strtoupper(substr($firstName, 0, 1) . (substr($lastName, 0, 1) ?: ''));
                    ?>
                    <tr data-id="<?php echo $emp['employee_number']; ?>" data-name="<?php echo strtolower($fullName); ?>"
                      data-position="<?php echo strtolower($emp['position'] ?? ''); ?>"
                      data-status="<?php echo $status['status']; ?>">
                      <td class="p-3">
                        <div class="flex items-center gap-2">
                          <div
                            class="h-8 w-8 rounded-full bg-amber-200 flex items-center justify-center text-amber-800 font-bold text-xs">
                            <?php echo $initials; ?>
                          </div>
                          <div>
                            <span class="font-medium"><?php echo htmlspecialchars($fullName); ?></span>
                            <p class="text-xs text-slate-500"><?php echo $emp['employee_number']; ?></p>
                          </div>
                        </div>
                      </td>
                      <td class="p-3">
                        <span class="font-medium"><?php echo htmlspecialchars($emp['position'] ?? 'Staff'); ?></span>
                        <p class="text-xs text-slate-500"><?php echo htmlspecialchars($emp['department'] ?? ''); ?></p>
                      </td>
                      <td class="p-3">
                        <?php if ($shift): ?>
                          <span class="font-medium"><?php echo $shift['shift_name'] ?? 'Regular Shift'; ?></span>
                          <p class="text-xs text-slate-500"><?php echo $shift['start_time'] ?? '--'; ?> -
                            <?php echo $shift['end_time'] ?? '--'; ?>
                          </p>
                          <?php if ($shift['is_default_shift'] ?? false): ?>
                            <span class="text-xs text-amber-600">(default)</span>
                          <?php endif; ?>
                        <?php else: ?>
                          <span class="text-slate-400">No schedule</span>
                        <?php endif; ?>
                      </td>
                      <td class="p-3">
                        <span class="status-badge <?php echo $statusColor; ?> px-2 py-1 rounded-full text-xs">
                          <span class="attendance-indicator <?php
                          echo $status['present'] ? 'bg-green-500' :
                            ($status['status'] === 'late' ? 'bg-yellow-500' : 'bg-red-500');
                          ?>"></span>
                          <?php echo ucfirst($status['status']); ?>
                          <?php if ($status['is_late']): ?>
                            <span class="ml-1">(<?php echo $status['late_minutes']; ?> min)</span>
                          <?php endif; ?>
                        </span>
                      </td>
                      <td class="p-3">
                        <?php if ($attendance): ?>
                          <div class="text-xs">
                            <p><i class="fas fa-clock text-green-500 mr-1"></i> In:
                              <?php echo date('h:i A', strtotime($attendance['clock_in'])); ?>
                            </p>
                            <?php if ($attendance['clock_out']): ?>
                              <p><i class="fas fa-clock text-red-500 mr-1"></i> Out:
                                <?php echo date('h:i A', strtotime($attendance['clock_out'])); ?>
                              </p>
                            <?php endif; ?>
                            <p class="text-slate-500 mt-1">Hours: <?php echo $attendance['regular_hours']; ?></p>
                          </div>
                        <?php else: ?>
                          <span class="text-slate-400">No record</span>
                        <?php endif; ?>
                      </td>
                      <td class="p-3" id="rating-<?php echo $emp['employee_number']; ?>">
                        <?php if ($avgRating): ?>
                          <div class="flex items-center gap-1">
                            <div class="rating-display">
                              <?php
                              $fullStars = floor($avgRating);
                              $hasHalf = $avgRating - $fullStars >= 0.5;
                              for ($i = 1; $i <= 5; $i++):
                                if ($i <= $fullStars):
                                  echo '<i class="fa-solid fa-star text-yellow-400 text-xs"></i>';
                                elseif ($hasHalf && $i == $fullStars + 1):
                                  echo '<i class="fa-solid fa-star-half-alt text-yellow-400 text-xs"></i>';
                                else:
                                  echo '<i class="fa-regular fa-star text-gray-300 text-xs"></i>';
                                endif;
                              endfor;
                              ?>
                            </div>
                            <span class="text-xs text-slate-500 ml-1">(<?php echo number_format($avgRating, 1); ?>)</span>
                          </div>
                        <?php else: ?>
                          <span class="text-xs text-slate-400">No ratings yet</span>
                        <?php endif; ?>
                      </td>
                      <td class="p-3" id="tables-<?php echo $emp['employee_number']; ?>">
                        <?php
                        // You would fetch this from local DB
                        echo '—';
                        ?>
                      </td>
                      <td class="p-3">
                        <button
                          onclick="openAssignTablesModal('<?php echo $emp['employee_number']; ?>', '<?php echo htmlspecialchars($fullName); ?>')"
                          class="text-amber-600 hover:text-amber-800 text-xs mr-2" title="Assign Tables">
                          <i class="fas fa-table"></i>
                        </button>
                        <button
                          onclick="openNoteModal('<?php echo $emp['employee_number']; ?>', '<?php echo htmlspecialchars($fullName); ?>')"
                          class="text-blue-600 hover:text-blue-800 text-xs mr-2" title="Add Note & Rating">
                          <i class="fas fa-star"></i>
                        </button>
                        <button
                          onclick="viewNotes('<?php echo $emp['employee_number']; ?>', '<?php echo htmlspecialchars($fullName); ?>')"
                          class="text-slate-600 hover:text-slate-800 text-xs" title="View Notes & Ratings">
                          <i class="fas fa-eye"></i>
                        </button>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
          <div class="p-4 border-t border-slate-200 flex items-center justify-between">
            <span class="text-xs text-slate-500" id="paginationInfo">
              Showing
              <?php echo (($currentPage - 1) * $limit + 1); ?>-<?php echo min($currentPage * $limit, $totalStaff); ?> of
              <?php echo $totalStaff; ?> staff
            </span>
            <div class="flex gap-2" id="paginationButtons">
              <!-- Pagination buttons will be generated by JavaScript -->
            </div>
          </div>
        </div>

        <!-- BOTTOM: SCHEDULE SUMMARY & PERFORMANCE INSIGHTS -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <!-- today's shift schedule -->
          <div class="bg-white rounded-2xl border border-slate-200 p-5">
            <h2 class="font-semibold text-lg flex items-center gap-2 mb-3">
              <i class="fas fa-calendar text-amber-600"></i> today's shift schedule
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4" id="shiftSummary">
              <div class="border rounded-xl p-3">
                <p class="font-medium text-sm">Morning</p>
                <p class="text-xs text-slate-500">7:00 AM - 4:00 PM</p>
                <p class="text-lg font-semibold mt-1" id="morningCount">
                  <?php echo $shiftSummary['morning_count']; ?>
                  staff
                </p>
                <p class="text-xs text-green-600 truncate" id="morningStaff">
                  <?php echo $shiftSummary['morning_staff']; ?>
                </p>
              </div>
              <div class="border rounded-xl p-3">
                <p class="font-medium text-sm">Afternoon</p>
                <p class="text-xs text-slate-500">12:00 PM - 9:00 PM</p>
                <p class="text-lg font-semibold mt-1" id="afternoonCount">
                  <?php echo $shiftSummary['afternoon_count']; ?> staff
                </p>
                <p class="text-xs text-green-600 truncate" id="afternoonStaff">
                  <?php echo $shiftSummary['afternoon_staff']; ?>
                </p>
              </div>
              <div class="border rounded-xl p-3">
                <p class="font-medium text-sm">Evening</p>
                <p class="text-xs text-slate-500">4:00 PM - 11:00 PM</p>
                <p class="text-lg font-semibold mt-1" id="eveningCount">
                  <?php echo $shiftSummary['evening_count']; ?>
                  staff
                </p>
                <p class="text-xs text-green-600 truncate" id="eveningStaff">
                  <?php echo $shiftSummary['evening_staff']; ?>
                </p>
              </div>
            </div>
          </div>

          <!-- performance insights -->
          <div class="bg-gradient-to-r from-amber-50 to-orange-50 border border-amber-200 rounded-2xl p-5">
            <h3 class="font-semibold flex items-center gap-2 mb-3">
              <i class="fas fa-chart-line text-amber-600"></i> performance insights
            </h3>
            <div class="space-y-3">
              <div class="flex justify-between items-center">
                <span>Completion rate</span>
                <span class="font-semibold text-lg">
                  <?php echo $summary['completion_rate']; ?>%
                </span>
              </div>
              <div class="w-full bg-gray-200 rounded-full h-2.5">
                <div class="bg-amber-600 h-2.5 rounded-full" style="width: <?php echo $summary['completion_rate']; ?>%">
                </div>
              </div>
              <div class="grid grid-cols-2 gap-2 mt-3 text-sm">
                <div class="bg-white p-2 rounded-lg">
                  <p class="text-xs text-slate-500">With attendance</p>
                  <p class="font-semibold">
                    <?php echo $summary['with_attendance'] ?? 0; ?> staff
                  </p>
                </div>
                <div class="bg-white p-2 rounded-lg">
                  <p class="text-xs text-slate-500">With schedule</p>
                  <p class="font-semibold">
                    <?php echo ($summary['total_employees'] - $summary['no_schedule']); ?> staff
                  </p>
                </div>
              </div>
              <div class="border-t border-amber-200 pt-3 mt-2">
                <p class="text-xs text-amber-700">
                  <i class="fas fa-info-circle mr-1"></i>
                  Rate staff performance to track progress and identify top performers
                </p>
              </div>
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
      let currentRating = 0;

      // ========== TOAST NOTIFICATION ==========
      function showToast(message, type = 'success') {
        const toast = document.getElementById('toast');
        const toastMessage = document.getElementById('toastMessage');
        const toastTime = document.getElementById('toastTime');
        const now = new Date();

        toast.className = 'toast ' + type;
        toastMessage.textContent = message;
        toastTime.textContent = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
        toast.classList.remove('hidden');

        setTimeout(() => { toast.classList.add('show'); }, 10);
        setTimeout(() => {
          toast.classList.remove('show');
          setTimeout(() => { toast.classList.add('hidden'); }, 300);
        }, 3000);
      }

      // ========== MODAL FUNCTIONS ==========
      function openModal(modalId) {
        document.getElementById(modalId).classList.add('show');
      }

      function closeModal(modalId) {
        document.getElementById(modalId).classList.remove('show');
      }

      // ========== STAR RATING FUNCTIONS ==========
      function initStarRating() {
        const stars = document.querySelectorAll('#starRating .rating-star');
        const ratingInput = document.getElementById('noteRating');

        stars.forEach(star => {
          star.addEventListener('click', function () {
            const rating = parseInt(this.dataset.rating);
            currentRating = rating;
            ratingInput.value = rating;

            // Update star display
            stars.forEach((s, index) => {
              if (index < rating) {
                s.classList.add('active');
                s.innerHTML = '<i class="fa-solid fa-star"></i>';
              } else {
                s.classList.remove('active');
                s.innerHTML = '<i class="fa-regular fa-star"></i>';
              }
            });
          });

          // Hover effect
          star.addEventListener('mouseenter', function () {
            const hoverRating = parseInt(this.dataset.rating);
            stars.forEach((s, index) => {
              if (index < hoverRating) {
                s.innerHTML = '<i class="fa-solid fa-star"></i>';
              } else {
                s.innerHTML = '<i class="fa-regular fa-star"></i>';
              }
            });
          });
        });

        // Reset on mouse leave
        const starContainer = document.getElementById('starRating');
        if (starContainer) {
          starContainer.addEventListener('mouseleave', function () {
            stars.forEach((s, index) => {
              if (index < currentRating) {
                s.innerHTML = '<i class="fa-solid fa-star"></i>';
              } else {
                s.innerHTML = '<i class="fa-regular fa-star"></i>';
              }
            });
          });
        }
      }

      // Helper function to generate star display HTML
      function renderStars(rating) {
        if (!rating || rating === 0) return '';
        const fullStars = Math.floor(rating);
        const hasHalf = rating - fullStars >= 0.5;
        let stars = '';

        for (let i = 1; i <= 5; i++) {
          if (i <= fullStars) {
            stars += '<i class="fa-solid fa-star text-yellow-400 text-xs"></i>';
          } else if (hasHalf && i === fullStars + 1) {
            stars += '<i class="fa-solid fa-star-half-alt text-yellow-400 text-xs"></i>';
          } else {
            stars += '<i class="fa-regular fa-star text-gray-300 text-xs"></i>';
          }
        }

        return stars;
      }

      // Helper function to escape HTML
      function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
      }

      // ========== REFRESH HR DATA ==========
      function refreshHRData() {
        showToast('Refreshing data from HR system...', 'info');
        setTimeout(() => {
          location.reload();
        }, 1000);
      }

      // ========== NOTE FUNCTIONS ==========
      function openNoteModal(employeeId, employeeName) {
        document.getElementById('noteEmployeeId').value = employeeId;
        document.getElementById('noteEmployeeName').value = employeeName;
        document.getElementById('noteContent').value = '';
        document.getElementById('noteRating').value = '';
        document.getElementById('ratingType').value = 'overall';
        currentRating = 0;

        // Reset stars
        document.querySelectorAll('#starRating .rating-star').forEach(star => {
          star.classList.remove('active');
          star.innerHTML = '<i class="fa-regular fa-star"></i>';
        });

        openModal('noteModal');
      }

      function saveNote(event) {
        event.preventDefault();

        const employeeId = document.getElementById('noteEmployeeId').value;
        const note = document.getElementById('noteContent').value;
        const rating = document.getElementById('noteRating').value;
        const ratingType = document.getElementById('ratingType').value;

        if (!note.trim()) {
          showToast('Please enter a note', 'error');
          return;
        }

        Swal.fire({
          title: 'Saving Note...',
          text: 'Please wait',
          allowOutsideClick: false,
          didOpen: () => {
            Swal.showLoading();
          }
        });

        const formData = new FormData();
        formData.append('action', 'add_note');
        formData.append('employee_id', employeeId);
        formData.append('note', note);
        if (rating) {
          formData.append('rating', rating);
          formData.append('rating_type', ratingType);
        }

        fetch('../../../controller/admin/post/restaurant/staff_actions.php', {
          method: 'POST',
          body: formData
        })
          .then(response => {
            if (!response.ok) {
              throw new Error('Network response was not ok');
            }
            return response.text().then(text => {
              try {
                return JSON.parse(text);
              } catch (e) {
                console.error('Invalid JSON response:', text.substring(0, 200));
                throw new Error('Server returned invalid JSON');
              }
            });
          })
          .then(data => {
            Swal.close();
            if (data.success) {
              let message = data.message;
              if (data.rating) {
                const stars = '★'.repeat(data.rating) + '☆'.repeat(5 - data.rating);
                message += ` (Rating: ${stars} ${data.rating}/5)`;
              }
              Swal.fire({
                title: 'Success!',
                html: message,
                icon: 'success',
                confirmButtonColor: '#d97706'
              }).then(() => {
                closeModal('noteModal');
                document.getElementById('noteContent').value = '';
                document.getElementById('noteRating').value = '';
                currentRating = 0;
                // Reset stars
                document.querySelectorAll('#starRating .rating-star').forEach(star => {
                  star.classList.remove('active');
                  star.innerHTML = '<i class="fa-regular fa-star"></i>';
                });
                // Refresh the page to show updated rating
                setTimeout(() => location.reload(), 1000);
              });
            } else {
              showToast(data.message, 'error');
            }
          })
          .catch(error => {
            Swal.close();
            console.error('Error:', error);
            showToast('Error: ' + error.message, 'error');
          });
      }

      function viewNotes(employeeId, employeeName) {
        Swal.fire({
          title: 'Loading Notes...',
          text: 'Please wait',
          allowOutsideClick: false,
          didOpen: () => {
            Swal.showLoading();
          }
        });

        const formData = new FormData();
        formData.append('action', 'get_notes');
        formData.append('employee_id', employeeId);

        fetch('../../../controller/admin/post/restaurant/staff_actions.php', {
          method: 'POST',
          body: formData
        })
          .then(response => {
            if (!response.ok) {
              throw new Error('Network response was not ok');
            }
            return response.text().then(text => {
              try {
                return JSON.parse(text);
              } catch (e) {
                console.error('Invalid JSON response:', text.substring(0, 200));
                throw new Error('Server returned invalid JSON');
              }
            });
          })
          .then(data => {
            Swal.close();
            if (data.success) {
              const notesList = document.getElementById('notesList');
              document.getElementById('viewNotesTitle').textContent = `Notes & Ratings - ${employeeName}`;

              // Display average rating
              const avgRatingDiv = document.getElementById('avgRatingDisplay');
              if (data.avg_rating && data.avg_rating > 0) {
                avgRatingDiv.classList.remove('hidden');
                document.getElementById('avgRatingStars').innerHTML = renderStars(data.avg_rating);
                document.getElementById('avgRatingValue').textContent =
                  `${data.avg_rating} out of 5 (${data.total_ratings} rating${data.total_ratings !== 1 ? 's' : ''})`;
              } else {
                avgRatingDiv.classList.add('hidden');
              }

              if (!data.notes || data.notes.length === 0) {
                notesList.innerHTML = '<p class="text-center text-slate-500 py-4">No notes or ratings found</p>';
              } else {
                let html = '';
                data.notes.forEach(note => {
                  const date = note.created_at ? new Date(note.created_at).toLocaleString() : 'Unknown date';
                  const ratingDisplay = note.rating ? `
                    <div class="flex items-center gap-2 mt-1 p-2 bg-amber-50 rounded">
                      <span class="text-xs font-medium text-slate-600 capitalize">${note.rating_type.replace('_', ' ')}:</span>
                      <div class="rating-display">${renderStars(note.rating)}</div>
                      <span class="text-xs font-semibold text-amber-600">(${note.rating}/5)</span>
                    </div>
                  ` : '';

                  html += `
                    <div class="border rounded-lg p-3 bg-white hover:shadow-md transition">
                      <div class="flex items-start gap-2">
                        <i class="fas fa-quote-left text-amber-400 text-xs mt-1"></i>
                        <p class="text-sm flex-1">${escapeHtml(note.note)}</p>
                      </div>
                      ${ratingDisplay}
                      <div class="flex justify-between items-center mt-2 pt-2 border-t border-slate-100">
                        <p class="text-xs text-slate-500">
                          <i class="fas fa-user-circle mr-1"></i>By: ${note.created_by_name || 'Admin'}
                        </p>
                        <p class="text-xs text-slate-400">
                          <i class="far fa-clock mr-1"></i>${date}
                        </p>
                      </div>
                    </div>
                  `;
                });
                notesList.innerHTML = html;
              }

              openModal('viewNotesModal');
            } else {
              showToast(data.message || 'Failed to load notes', 'error');
            }
          })
          .catch(error => {
            Swal.close();
            console.error('Error:', error);
            showToast('Error loading notes: ' + error.message, 'error');
          });
      }

      // ========== ASSIGN TABLES FUNCTIONS ==========
      function openAssignTablesModal(employeeId = null, employeeName = null) {
        if (employeeId) {
          document.getElementById('assignEmployeeId').value = employeeId;
          if (employeeName) {
            console.log(`Assigning tables for ${employeeName}`);
          }
        }
        generateTableGrid();
        openModal('assignTablesModal');
      }

      function generateTableGrid() {
        const grid = document.getElementById('tableGrid');
        if (!grid) return;

        grid.innerHTML = '';

        for (let i = 1; i <= 25; i++) {
          const btn = document.createElement('button');
          btn.type = 'button';
          btn.className = 'border border-slate-200 rounded-lg p-2 text-xs hover:bg-amber-50 transition';
          btn.textContent = i;
          btn.onclick = function () {
            this.classList.toggle('bg-amber-200');
            this.classList.toggle('border-amber-400');
            updateTableAssignment();
          };
          grid.appendChild(btn);
        }
      }

      function updateTableAssignment() {
        const selectedTables = [];
        document.querySelectorAll('#tableGrid button.bg-amber-200').forEach(btn => {
          selectedTables.push(btn.textContent);
        });

        if (selectedTables.length > 0) {
          document.getElementById('tableAssignment').value = `Tables ${selectedTables.join(', ')}`;
        } else {
          document.getElementById('tableAssignment').value = '';
        }
      }

      function saveTableAssignment(event) {
        event.preventDefault();

        const employeeId = document.getElementById('assignEmployeeId').value;
        const tables = document.getElementById('tableAssignment').value;

        if (!employeeId) {
          showToast('Employee ID is missing', 'error');
          return;
        }

        if (!tables) {
          showToast('Please assign tables', 'error');
          return;
        }

        Swal.fire({
          title: 'Assigning Tables...',
          text: 'Please wait',
          allowOutsideClick: false,
          didOpen: () => {
            Swal.showLoading();
          }
        });

        const formData = new FormData();
        formData.append('action', 'assign_tables');
        formData.append('employee_id', employeeId);
        formData.append('tables', tables);

        fetch('../../../controller/admin/post/restaurant/staff_actions.php', {
          method: 'POST',
          body: formData
        })
          .then(response => {
            if (!response.ok) {
              throw new Error('Network response was not ok');
            }
            return response.text().then(text => {
              try {
                return JSON.parse(text);
              } catch (e) {
                console.error('Invalid JSON response:', text.substring(0, 200));
                throw new Error('Server returned invalid JSON');
              }
            });
          })
          .then(data => {
            Swal.close();
            if (data.success) {
              const tableCell = document.getElementById(`tables-${employeeId}`);
              if (tableCell) {
                tableCell.textContent = tables;
              }

              Swal.fire({
                title: 'Success!',
                text: data.message,
                icon: 'success',
                confirmButtonColor: '#d97706'
              }).then(() => {
                closeModal('assignTablesModal');
              });
            } else {
              showToast(data.message || 'Failed to assign tables', 'error');
            }
          })
          .catch(error => {
            Swal.close();
            console.error('Error:', error);
            showToast('Error: ' + error.message, 'error');
          });
      }

      // ========== FILTER FUNCTIONS ==========
      function filterByStatus(status) {
        const url = new URL(window.location);
        url.searchParams.set('status', status);
        url.searchParams.set('page', '1');
        window.location.href = url.toString();
      }

      // ========== SEARCH FUNCTION ==========
      function searchStaff() {
        const searchTerm = document.getElementById('searchInput').value;
        const url = new URL(window.location);
        if (searchTerm.trim()) {
          url.searchParams.set('search', searchTerm.trim());
        } else {
          url.searchParams.delete('search');
        }
        url.searchParams.set('page', '1');
        window.location.href = url.toString();
      }

      // ========== PAGINATION FUNCTIONS ==========
      function updatePagination() {
        const rows = document.querySelectorAll('#staffTableBody tr');
        const totalItems = rows.length;
        const totalPagesCount = Math.max(1, Math.ceil(totalItems / itemsPerPage));

        if (currentPage > totalPagesCount) currentPage = totalPagesCount;
        if (currentPage < 1) currentPage = 1;

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
          totalItems > 0 ? `Showing ${start}-${end} of ${totalItems} staff` : 'Showing 0 staff';

        generatePaginationButtons(totalPagesCount);
      }

      function generatePaginationButtons(totalPagesCount) {
        const container = document.getElementById('paginationButtons');
        if (!container) return;

        let buttons = `<button onclick="changePage('prev')" class="border border-slate-200 px-3 py-1 rounded-lg text-sm hover:bg-slate-50" ${currentPage === 1 ? 'disabled' : ''}>Previous</button>`;

        for (let i = 1; i <= totalPagesCount; i++) {
          buttons += `<button onclick="changePage(${i})" class="border px-3 py-1 rounded-lg text-sm page-btn ${i === currentPage ? 'bg-amber-600 text-white' : 'border-slate-200 hover:bg-slate-50'}">${i}</button>`;
        }

        buttons += `<button onclick="changePage('next')" class="border border-slate-200 px-3 py-1 rounded-lg text-sm hover:bg-slate-50" ${currentPage === totalPagesCount ? 'disabled' : ''}>Next</button>`;

        container.innerHTML = buttons;
      }

      function changePage(direction) {
        const rows = document.querySelectorAll('#staffTableBody tr');
        const totalPagesCount = Math.max(1, Math.ceil(rows.length / itemsPerPage));

        if (direction === 'prev' && currentPage > 1) {
          currentPage--;
        } else if (direction === 'next' && currentPage < totalPagesCount) {
          currentPage++;
        } else if (typeof direction === 'number') {
          if (direction >= 1 && direction <= totalPagesCount) {
            currentPage = direction;
          }
        }

        updatePagination();
      }

      // ========== INITIALIZATION ==========
      document.addEventListener('DOMContentLoaded', function () {
        updatePagination();
        initStarRating();

        // Initialize the assign employee ID field if it doesn't exist
        const assignField = document.getElementById('assignEmployeeId');
        if (!assignField) {
          const modal = document.getElementById('assignTablesModal');
          if (modal) {
            const form = modal.querySelector('form');
            if (form) {
              const hiddenInput = document.createElement('input');
              hiddenInput.type = 'hidden';
              hiddenInput.id = 'assignEmployeeId';
              hiddenInput.name = 'employee_id';
              form.prepend(hiddenInput);
            }
          }
        }
      });

      // Close modals when clicking outside
      window.addEventListener('click', function (event) {
        if (event.target.classList.contains('modal')) {
          event.target.classList.remove('show');
        }
      });

      // Close modals with Escape key
      document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
          document.querySelectorAll('.modal.show').forEach(modal => {
            modal.classList.remove('show');
          });
        }
      });
    </script>
  </body>

</html>