<?php
/**
 * View - Admin All Guest Requests
 */
require_once '../../../../controller/admin/get/all_guest_requests.php';

// Set current page for navigation
$current_page = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="en">

  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin · All Guest Requests</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Tailwind via CDN + Font Awesome 6 -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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
      <?php require_once '../../components/admin_nav.php'; ?>

      <!-- ========== MAIN CONTENT: ALL REQUESTS ========== -->
      <main class="flex-1 p-5 lg:p-8 overflow-y-auto bg-white">

        <!-- Header with back button -->
        <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
          <div>
            <div class="flex items-center gap-2 text-sm text-slate-500 mb-1">
              <a href="../front_desk_reception.php" class="hover:text-amber-600 transition">
                <i class="fa-regular fa-arrow-left"></i> Back to Front Desk
              </a>
            </div>
            <h1 class="text-2xl lg:text-3xl font-light text-slate-800 flex items-center gap-2">
              <i class="fa-regular fa-message text-amber-600"></i> All Guest Requests
            </h1>
            <p class="text-sm text-slate-500 mt-0.5">complete list of all guest requests</p>
          </div>
          <div class="flex gap-2">
            <span class="bg-white border rounded-full px-4 py-2 flex items-center gap-2 shadow-sm text-sm">
              <i class="fa-regular fa-calendar text-slate-400"></i> <span id="currentDate"></span>
            </span>
          </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
          <div
            class="bg-white p-4 rounded-xl border border-slate-200 hover:border-amber-300 hover:shadow-md transition cursor-pointer"
            onclick="filterByStatus('all')">
            <div class="flex items-center gap-3">
              <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-700">
                <i class="fa-regular fa-message"></i>
              </div>
              <div>
                <p class="text-xs text-slate-500">Total Requests</p>
                <p class="text-xl font-semibold" id="totalRequests"><?php echo $stats['total']; ?></p>
              </div>
            </div>
          </div>
          <div
            class="bg-white p-4 rounded-xl border border-slate-200 hover:border-yellow-300 hover:shadow-md transition cursor-pointer"
            onclick="filterByStatus('pending')">
            <div class="flex items-center gap-3">
              <div class="h-10 w-10 rounded-full bg-yellow-100 flex items-center justify-center text-yellow-700">
                <i class="fa-regular fa-clock"></i>
              </div>
              <div>
                <p class="text-xs text-slate-500">Pending</p>
                <p class="text-xl font-semibold" id="pendingCount"><?php echo $stats['pending']; ?></p>
              </div>
            </div>
          </div>
          <div
            class="bg-white p-4 rounded-xl border border-slate-200 hover:border-blue-300 hover:shadow-md transition cursor-pointer"
            onclick="filterByStatus('in-progress')">
            <div class="flex items-center gap-3">
              <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-700">
                <i class="fa-regular fa-spinner"></i>
              </div>
              <div>
                <p class="text-xs text-slate-500">In Progress</p>
                <p class="text-xl font-semibold" id="inProgressCount"><?php echo $stats['in_progress']; ?></p>
              </div>
            </div>
          </div>
          <div
            class="bg-white p-4 rounded-xl border border-slate-200 hover:border-green-300 hover:shadow-md transition cursor-pointer"
            onclick="filterByStatus('done')">
            <div class="flex items-center gap-3">
              <div class="h-10 w-10 rounded-full bg-green-100 flex items-center justify-center text-green-700">
                <i class="fa-regular fa-circle-check"></i>
              </div>
              <div>
                <p class="text-xs text-slate-500">Done</p>
                <p class="text-xl font-semibold" id="doneCount"><?php echo $stats['done']; ?></p>
              </div>
            </div>
          </div>
        </div>

        <!-- Search Bar -->
        <div class="mb-6">
          <div class="relative">
            <i class="fa-regular fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
            <input type="text" id="searchInput" placeholder="Search by guest name, request, or subject..."
              class="w-full border border-slate-200 rounded-xl py-2 pl-10 pr-4 focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
              value="<?php echo htmlspecialchars($searchFilter); ?>">
          </div>
        </div>

        <!-- Filter tabs -->
        <div class="flex flex-wrap gap-2 mb-6">
          <button
            class="filter-btn px-4 py-2 rounded-full text-sm border <?php echo $statusFilter == 'all' ? 'bg-amber-50 border-amber-300 text-amber-800' : 'border-slate-200 text-slate-600 hover:bg-amber-50'; ?>"
            data-filter="all">All</button>
          <button
            class="filter-btn px-4 py-2 rounded-full text-sm border <?php echo $statusFilter == 'pending' ? 'bg-amber-50 border-amber-300 text-amber-800' : 'border-slate-200 text-slate-600 hover:bg-amber-50'; ?>"
            data-filter="pending">Pending</button>
          <button
            class="filter-btn px-4 py-2 rounded-full text-sm border <?php echo $statusFilter == 'in-progress' ? 'bg-amber-50 border-amber-300 text-amber-800' : 'border-slate-200 text-slate-600 hover:bg-amber-50'; ?>"
            data-filter="in-progress">In Progress</button>
          <button
            class="filter-btn px-4 py-2 rounded-full text-sm border <?php echo $statusFilter == 'done' ? 'bg-amber-50 border-amber-300 text-amber-800' : 'border-slate-200 text-slate-600 hover:bg-amber-50'; ?>"
            data-filter="done">Done</button>
        </div>

        <!-- Requests Table -->
        <div class="bg-white rounded-2xl border border-slate-200 p-5">
          <div class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead class="text-slate-400 text-xs border-b">
                <tr>
                  <td class="pb-3">Room / Guest</td>
                  <td class="pb-3">Request</td>
                  <td class="pb-3">Date & Time</td>
                  <td class="pb-3">Status</td>
                  <td class="pb-3">Priority</td>
                  <td class="pb-3">Assigned To</td>
                  <td class="pb-3">Action</td>
                </tr>
              </thead>
              <tbody class="divide-y" id="requestsTableBody">
                <!-- populated by JavaScript -->
              </tbody>
            </table>
          </div>
          <div id="emptyMessage"
            class="text-center py-8 text-slate-400 <?php echo !empty($requests) ? 'hidden' : ''; ?>">
            No requests found
          </div>
        </div>
      </main>
    </div>

    <!-- Request Details Modal -->
    <div id="requestModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
      <div class="bg-white rounded-2xl p-6 max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-xl font-semibold">Request Details</h3>
          <button onclick="closeRequestModal()" class="text-slate-400 hover:text-slate-600">
            <i class="fa-solid fa-xmark text-2xl"></i>
          </button>
        </div>
        <div id="requestDetails" class="space-y-4">
          <!-- Details will be populated by JavaScript -->
        </div>
        <div class="mt-6 flex gap-3">
          <button onclick="closeRequestModal()"
            class="flex-1 border border-slate-200 text-slate-700 px-4 py-2 rounded-xl text-sm font-medium hover:bg-slate-50 transition">Close</button>
          <button id="modalActionBtn" onclick="handleModalAction()"
            class="flex-1 bg-amber-600 hover:bg-amber-700 text-white px-4 py-2 rounded-xl text-sm font-medium transition">Mark
            as Done</button>
        </div>
      </div>
    </div>

    <!-- Response Modal -->
    <div id="responseModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
      <div class="bg-white rounded-2xl p-6 max-w-md w-full mx-4">
        <h3 class="text-lg font-semibold mb-4">Add Response</h3>
        <textarea id="responseText" rows="4"
          class="w-full border border-slate-200 rounded-xl p-3 text-sm focus:ring-2 focus:ring-amber-500 outline-none"
          placeholder="Enter your response to this request..."></textarea>
        <input type="hidden" id="responseRequestId">
        <div class="flex gap-3 mt-4">
          <button onclick="closeResponseModal()"
            class="flex-1 border border-slate-200 text-slate-700 px-4 py-2 rounded-xl text-sm font-medium hover:bg-slate-50 transition">Cancel</button>
          <button onclick="submitResponse()"
            class="flex-1 bg-amber-600 hover:bg-amber-700 text-white px-4 py-2 rounded-xl text-sm font-medium transition">Submit</button>
        </div>
      </div>
    </div>

    <!-- Assign Staff Modal -->
    <div id="assignModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
      <div class="bg-white rounded-2xl p-6 max-w-md w-full mx-4">
        <h3 class="text-lg font-semibold mb-4">Assign to Staff</h3>
        <select id="staffSelect"
          class="w-full border border-slate-200 rounded-xl p-3 text-sm focus:ring-2 focus:ring-amber-500 outline-none mb-4">
          <option value="">Select staff member...</option>
        </select>
        <input type="hidden" id="assignRequestId">
        <div class="flex gap-3">
          <button onclick="closeAssignModal()"
            class="flex-1 border border-slate-200 text-slate-700 px-4 py-2 rounded-xl text-sm font-medium hover:bg-slate-50 transition">Cancel</button>
          <button onclick="submitAssign()"
            class="flex-1 bg-amber-600 hover:bg-amber-700 text-white px-4 py-2 rounded-xl text-sm font-medium transition">Assign</button>
        </div>
      </div>
    </div>

    <script>
      // Pass PHP data to JavaScript
      const requestsData = <?php echo json_encode($requests); ?>;
      const stats = <?php echo json_encode($stats); ?>;
      const currentFilter = '<?php echo $statusFilter; ?>';
      const currentSearch = '<?php echo $searchFilter; ?>';

      // Global variables
      let filteredData = [...requestsData];
      let currentRequestId = null;

      // Display current date
      function updateDate() {
        const date = new Date();
        const options = { year: 'numeric', month: 'long', day: 'numeric' };
        document.getElementById('currentDate').textContent = date.toLocaleDateString('en-US', options);
      }

      // Show toast notification
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

      // Format date
      function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', {
          month: 'short',
          day: 'numeric',
          hour: '2-digit',
          minute: '2-digit'
        });
      }

      // Get priority badge class
      function getPriorityClass(priority) {
        switch (priority?.toLowerCase()) {
          case 'high': return 'bg-red-100 text-red-700';
          case 'medium': return 'bg-yellow-100 text-yellow-700';
          case 'low': return 'bg-green-100 text-green-700';
          default: return 'bg-slate-100 text-slate-700';
        }
      }

      // Get status badge class
      function getStatusClass(status) {
        switch (status) {
          case 'pending': return 'bg-yellow-100 text-yellow-700';
          case 'in-progress': return 'bg-blue-100 text-blue-700';
          case 'done': return 'bg-green-100 text-green-700';
          default: return 'bg-slate-100 text-slate-700';
        }
      }

      // Filter by status
      window.filterByStatus = function (status) {
        const url = new URL(window.location);
        if (status !== 'all') {
          url.searchParams.set('status', status);
        } else {
          url.searchParams.delete('status');
        }
        window.location.href = url.toString();
      };

      // Search functionality
      const searchInput = document.getElementById('searchInput');
      if (searchInput) {
        searchInput.addEventListener('keypress', function (e) {
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
      }

      // Render requests table
      function renderRequests() {
        const tbody = document.getElementById('requestsTableBody');
        const emptyMsg = document.getElementById('emptyMessage');

        if (!tbody) return;

        if (filteredData.length === 0) {
          tbody.innerHTML = '';
          emptyMsg.classList.remove('hidden');
          return;
        }

        emptyMsg.classList.add('hidden');

        let html = '';
        filteredData.forEach(request => {
          const guestName = request.guest_full_name || request.guest_name || 'Guest';
          const roomNumber = request.room_number || 'N/A';
          const assignedTo = request.assigned_to_name || 'Unassigned';

          html += `<tr class="hover:bg-slate-50 transition">
                    <td class="py-3">
                        <div class="flex flex-col">
                            <span class="font-medium">Room ${roomNumber}</span>
                            <span class="text-xs text-slate-500">${guestName}</span>
                        </div>
                    </td>
                    <td>
                        <div class="flex flex-col">
                            <span class="font-medium">${request.subject || 'Request'}</span>
                            <span class="text-xs text-slate-500 truncate max-w-[200px]">${request.message || ''}</span>
                        </div>
                    </td>
                    <td class="text-slate-500">${formatDate(request.created_at)}</td>
                    <td>
                        <span class="${getStatusClass(request.status)} px-2 py-1 rounded-full text-xs font-medium status-badge" 
                              onclick="filterByStatus('${request.status}')">
                            ${request.status || 'pending'}
                        </span>
                    </td>
                    <td>
                        <span class="${getPriorityClass(request.priority)} px-2 py-1 rounded-full text-xs">
                            ${request.priority || 'normal'}
                        </span>
                    </td>
                    <td class="text-slate-600 text-xs">${assignedTo}</td>
                    <td>
                        <div class="flex gap-2">
                            <button onclick="viewRequest(${request.id})" class="text-xs text-blue-600 border border-blue-600 px-2 py-1 rounded hover:bg-blue-50 transition" title="View details">
                                <i class="fa-regular fa-eye"></i>
                            </button>
                            ${request.status !== 'done' ? `
                                <button onclick="openResponseModal(${request.id})" class="text-xs text-amber-600 border border-amber-600 px-2 py-1 rounded hover:bg-amber-50 transition" title="Add response">
                                    <i class="fa-regular fa-reply"></i>
                                </button>
                                <button onclick="openAssignModal(${request.id})" class="text-xs text-purple-600 border border-purple-600 px-2 py-1 rounded hover:bg-purple-50 transition" title="Assign staff">
                                    <i class="fa-regular fa-user"></i>
                                </button>
                                <button onclick="markAsDone(${request.id})" class="text-xs text-green-600 border border-green-600 px-2 py-1 rounded hover:bg-green-50 transition" title="Mark as done">
                                    <i class="fa-regular fa-circle-check"></i>
                                </button>
                            ` : ''}
                        </div>
                    </td>
                </tr>`;
        });

        tbody.innerHTML = html;
      }

      // Mark request as done
      window.markAsDone = function (requestId) {
        Swal.fire({
          title: 'Mark as Completed?',
          text: 'Are you sure you want to mark this request as done?',
          icon: 'question',
          showCancelButton: true,
          confirmButtonColor: '#d97706',
          cancelButtonColor: '#6b7280',
          confirmButtonText: 'Yes, mark as done'
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
            formData.append('action', 'mark_done');
            formData.append('request_id', requestId);

            fetch('../../../../controller/admin/post/guest_request_actions.php', {
              method: 'POST',
              body: formData
            })
              .then(response => response.json())
              .then(data => {
                if (data.success) {
                  Swal.fire({
                    title: 'Completed!',
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
                  text: 'An error occurred. Please try again.',
                  icon: 'error',
                  confirmButtonColor: '#d97706'
                });
              });
          }
        });
      };

      // View request details
      window.viewRequest = function (requestId) {
        currentRequestId = requestId;

        Swal.fire({
          title: 'Loading...',
          text: 'Please wait',
          allowOutsideClick: false,
          didOpen: () => {
            Swal.showLoading();
          }
        });

        const formData = new FormData();
        formData.append('action', 'get_request_details');
        formData.append('request_id', requestId);

        fetch('../../../../controller/admin/post/guest_request_actions.php', {
          method: 'POST',
          body: formData
        })
          .then(response => response.json())
          .then(data => {
            Swal.close();

            if (data.success) {
              const r = data.request;
              const details = document.getElementById('requestDetails');

              details.innerHTML = `
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <p class="text-xs text-slate-500">Guest</p>
                                <p class="font-medium">${r.guest_name || 'Guest'}</p>
                                <p class="text-xs text-slate-500 mt-1">${r.guest_email || ''}</p>
                                <p class="text-xs text-slate-500">${r.guest_phone || ''}</p>
                            </div>
                            <div>
                                <p class="text-xs text-slate-500">Room</p>
                                <p class="font-medium">${r.room_number || 'N/A'}</p>
                                <p class="text-xs text-slate-500 mt-1">Requested: ${formatDate(r.created_at)}</p>
                            </div>
                        </div>
                        <div class="border-t pt-4">
                            <p class="text-xs text-slate-500 mb-1">Subject</p>
                            <p class="font-medium mb-3">${r.subject || 'General Request'}</p>
                            
                            <p class="text-xs text-slate-500 mb-1">Message</p>
                            <p class="bg-slate-50 p-3 rounded-lg mb-3">${r.message || 'No message provided'}</p>
                            
                            ${r.response ? `
                                <p class="text-xs text-slate-500 mb-1">Staff Response</p>
                                <p class="bg-green-50 p-3 rounded-lg">${r.response}</p>
                            ` : ''}
                        </div>
                        <div class="grid grid-cols-2 gap-4 mt-4 pt-4 border-t">
                            <div>
                                <p class="text-xs text-slate-500">Status</p>
                                <p><span class="${getStatusClass(r.status)} px-2 py-1 rounded-full text-xs">${r.status}</span></p>
                            </div>
                            <div>
                                <p class="text-xs text-slate-500">Priority</p>
                                <p><span class="${getPriorityClass(r.priority)} px-2 py-1 rounded-full text-xs">${r.priority || 'normal'}</span></p>
                            </div>
                            <div>
                                <p class="text-xs text-slate-500">Type</p>
                                <p class="capitalize">${r.type || 'request'}</p>
                            </div>
                            <div>
                                <p class="text-xs text-slate-500">Assigned To</p>
                                <p>${r.assigned_to_name || 'Unassigned'}</p>
                            </div>
                        </div>
                    `;

              const modalActionBtn = document.getElementById('modalActionBtn');
              if (r.status === 'done') {
                modalActionBtn.style.display = 'none';
              } else {
                modalActionBtn.style.display = 'block';
                modalActionBtn.textContent = 'Mark as Done';
                modalActionBtn.onclick = () => markAsDone(r.id);
              }

              document.getElementById('requestModal').classList.remove('hidden');
              document.getElementById('requestModal').classList.add('flex');
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
              text: 'An error occurred. Please try again.',
              icon: 'error',
              confirmButtonColor: '#d97706'
            });
          });
      };

      // Close request modal
      window.closeRequestModal = function () {
        document.getElementById('requestModal').classList.add('hidden');
        document.getElementById('requestModal').classList.remove('flex');
      };

      // Open response modal
      window.openResponseModal = function (requestId) {
        document.getElementById('responseRequestId').value = requestId;
        document.getElementById('responseModal').classList.remove('hidden');
        document.getElementById('responseModal').classList.add('flex');
      };

      // Close response modal
      window.closeResponseModal = function () {
        document.getElementById('responseModal').classList.add('hidden');
        document.getElementById('responseModal').classList.remove('flex');
        document.getElementById('responseText').value = '';
      };

      // Submit response
      window.submitResponse = function () {
        const requestId = document.getElementById('responseRequestId').value;
        const response = document.getElementById('responseText').value.trim();

        if (!response) {
          Swal.fire({
            title: 'Error',
            text: 'Please enter a response',
            icon: 'error',
            confirmButtonColor: '#d97706'
          });
          return;
        }

        Swal.fire({
          title: 'Submitting...',
          text: 'Please wait',
          allowOutsideClick: false,
          didOpen: () => {
            Swal.showLoading();
          }
        });

        const formData = new FormData();
        formData.append('action', 'add_response');
        formData.append('request_id', requestId);
        formData.append('response', response);

        fetch('../../../../controller/admin/post/guest_request_actions.php', {
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
                closeResponseModal();
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
              text: 'An error occurred. Please try again.',
              icon: 'error',
              confirmButtonColor: '#d97706'
            });
          });
      };

      // Open assign modal
      window.openAssignModal = function (requestId) {
        document.getElementById('assignRequestId').value = requestId;

        // Load staff list
        const formData = new FormData();
        formData.append('action', 'get_staff_list');

        fetch('../../../../controller/admin/post/guest_request_actions.php', {
          method: 'POST',
          body: formData
        })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              const select = document.getElementById('staffSelect');
              select.innerHTML = '<option value="">Select staff member...</option>';

              data.staff.forEach(staff => {
                const option = document.createElement('option');
                option.value = staff.id;
                option.textContent = `${staff.full_name} (${staff.role})`;
                select.appendChild(option);
              });

              document.getElementById('assignModal').classList.remove('hidden');
              document.getElementById('assignModal').classList.add('flex');
            }
          });
      };

      // Close assign modal
      window.closeAssignModal = function () {
        document.getElementById('assignModal').classList.add('hidden');
        document.getElementById('assignModal').classList.remove('flex');
      };

      // Submit assignment
      window.submitAssign = function () {
        const requestId = document.getElementById('assignRequestId').value;
        const staffId = document.getElementById('staffSelect').value;

        if (!staffId) {
          Swal.fire({
            title: 'Error',
            text: 'Please select a staff member',
            icon: 'error',
            confirmButtonColor: '#d97706'
          });
          return;
        }

        Swal.fire({
          title: 'Assigning...',
          text: 'Please wait',
          allowOutsideClick: false,
          didOpen: () => {
            Swal.showLoading();
          }
        });

        const formData = new FormData();
        formData.append('action', 'assign_request');
        formData.append('request_id', requestId);
        formData.append('staff_id', staffId);

        fetch('../../../../controller/admin/post/guest_request_actions.php', {
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
                closeAssignModal();
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
              text: 'An error occurred. Please try again.',
              icon: 'error',
              confirmButtonColor: '#d97706'
            });
          });
      };

      // Handle modal action button
      window.handleModalAction = function () {
        if (currentRequestId) {
          markAsDone(currentRequestId);
        }
      };

      // Close modals on outside click
      window.onclick = function (event) {
        const requestModal = document.getElementById('requestModal');
        const responseModal = document.getElementById('responseModal');
        const assignModal = document.getElementById('assignModal');

        if (requestModal && event.target === requestModal) {
          closeRequestModal();
        }
        if (responseModal && event.target === responseModal) {
          closeResponseModal();
        }
        if (assignModal && event.target === assignModal) {
          closeAssignModal();
        }
      };

      // Initialize
      document.addEventListener('DOMContentLoaded', function () {
        updateDate();
        renderRequests();
      });
    </script>
  </body>

</html>