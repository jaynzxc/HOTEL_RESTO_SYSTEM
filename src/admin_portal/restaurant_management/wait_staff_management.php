<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin · Wait Staff Management</title>
  <!-- Tailwind via CDN + Font Awesome 6 -->
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <style>
    /* exact same dropdown styles from index2.html */
    .transition-side { transition: all 0.2s ease; }
    .dropdown-arrow { transition: transform 0.2s; }
    details[open] .dropdown-arrow { transform: rotate(90deg); }
    details > summary { list-style: none; }
    details summary::-webkit-details-marker { display: none; }
    
    /* Modal styles */
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
      max-width: 500px;
      width: 90%;
      max-height: 85vh;
      overflow-y: auto;
    }
    
    /* Toast notification */
    .toast {
      position: fixed;
      bottom: 20px;
      right: 20px;
      background-color: #10b981;
      color: white;
      padding: 12px 24px;
      border-radius: 9999px;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
      transform: translateY(100px);
      opacity: 0;
      transition: all 0.3s ease;
      z-index: 1100;
    }
    
    .toast.show {
      transform: translateY(0);
      opacity: 1;
    }
    
    .toast.error {
      background-color: #ef4444;
    }
    
    .toast.info {
      background-color: #3b82f6;
    }
    
    /* Hidden row for search/filter */
    .hidden-row {
      display: none;
    }
    
    /* Performance modal stars */
    .star-rating {
      color: #fbbf24;
      font-size: 1.5rem;
      cursor: pointer;
    }
    
    .star-rating .star {
      transition: all 0.2s;
    }
    
    .star-rating .star:hover {
      transform: scale(1.2);
    }

    /* Edit modal specific styles */
    .form-group {
      margin-bottom: 1rem;
    }
    
    .form-label {
      display: block;
      font-size: 0.875rem;
      font-weight: 500;
      color: #334155;
      margin-bottom: 0.25rem;
    }
    
    .form-input {
      width: 100%;
      border: 1px solid #e2e8f0;
      border-radius: 0.5rem;
      padding: 0.5rem 0.75rem;
      font-size: 0.875rem;
    }
    
    .form-input:focus {
      outline: none;
      ring: 1px solid #f59e0b;
      border-color: #f59e0b;
    }
  </style>
</head>
<body class="bg-white font-sans antialiased">

  <!-- Toast notification container -->
  <div id="toast" class="toast"></div>

  <!-- Add Staff Modal -->
  <div id="addStaffModal" class="modal">
    <div class="modal-content p-6">
      <div class="flex justify-between items-center mb-4">
        <h3 class="text-xl font-semibold">Add New Staff</h3>
        <button onclick="closeModal('addStaffModal')" class="text-slate-400 hover:text-slate-600">
          <i class="fa-solid fa-xmark text-2xl"></i>
        </button>
      </div>
      <form id="addStaffForm" onsubmit="saveNewStaff(event)">
        <div class="grid grid-cols-2 gap-4">
          <div class="mb-4 col-span-2">
            <label class="block text-sm font-medium text-slate-700 mb-1">Full Name</label>
            <input type="text" id="newStaffName" required class="w-full border border-slate-200 rounded-lg px-3 py-2">
          </div>
          
          <div class="mb-4">
            <label class="block text-sm font-medium text-slate-700 mb-1">Position</label>
            <select id="newStaffPosition" class="w-full border border-slate-200 rounded-lg px-3 py-2">
              <option value="Waiter">Waiter</option>
              <option value="Senior Waiter">Senior Waiter</option>
              <option value="Head Waiter">Head Waiter</option>
              <option value="Trainee">Trainee</option>
            </select>
          </div>
          
          <div class="mb-4">
            <label class="block text-sm font-medium text-slate-700 mb-1">Shift</label>
            <select id="newStaffShift" class="w-full border border-slate-200 rounded-lg px-3 py-2">
              <option value="7:00 AM - 4:00 PM">Morning (7AM - 4PM)</option>
              <option value="12:00 PM - 9:00 PM">Afternoon (12PM - 9PM)</option>
              <option value="4:00 PM - 11:00 PM">Evening (4PM - 11PM)</option>
            </select>
          </div>
          
          <div class="mb-4">
            <label class="block text-sm font-medium text-slate-700 mb-1">Status</label>
            <select id="newStaffStatus" class="w-full border border-slate-200 rounded-lg px-3 py-2">
              <option value="on duty">On Duty</option>
              <option value="break">Break</option>
              <option value="off duty">Off Duty</option>
            </select>
          </div>
          
          <div class="mb-4">
            <label class="block text-sm font-medium text-slate-700 mb-1">Assigned Tables</label>
            <input type="text" id="newStaffTables" placeholder="e.g., Tables 1-4" class="w-full border border-slate-200 rounded-lg px-3 py-2">
          </div>
        </div>
        
        <div class="flex gap-3 mt-4">
          <button type="submit" class="flex-1 bg-amber-600 text-white py-2 rounded-lg hover:bg-amber-700">Add Staff</button>
          <button type="button" onclick="closeModal('addStaffModal')" class="flex-1 border border-slate-200 py-2 rounded-lg hover:bg-slate-50">Cancel</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Edit Staff Modal -->
  <div id="editStaffModal" class="modal">
    <div class="modal-content p-6">
      <div class="flex justify-between items-center mb-4">
        <h3 class="text-xl font-semibold">Edit Staff</h3>
        <button onclick="closeModal('editStaffModal')" class="text-slate-400 hover:text-slate-600">
          <i class="fa-solid fa-xmark text-2xl"></i>
        </button>
      </div>
      <form id="editStaffForm" onsubmit="saveEditStaff(event)">
        <input type="hidden" id="editStaffIndex">
        <div class="grid grid-cols-2 gap-4">
          <div class="mb-4 col-span-2">
            <label class="block text-sm font-medium text-slate-700 mb-1">Full Name</label>
            <input type="text" id="editStaffName" required class="w-full border border-slate-200 rounded-lg px-3 py-2">
          </div>
          
          <div class="mb-4">
            <label class="block text-sm font-medium text-slate-700 mb-1">Position</label>
            <select id="editStaffPosition" class="w-full border border-slate-200 rounded-lg px-3 py-2">
              <option value="Waiter">Waiter</option>
              <option value="Senior Waiter">Senior Waiter</option>
              <option value="Head Waiter">Head Waiter</option>
              <option value="Trainee">Trainee</option>
            </select>
          </div>
          
          <div class="mb-4">
            <label class="block text-sm font-medium text-slate-700 mb-1">Shift</label>
            <select id="editStaffShift" class="w-full border border-slate-200 rounded-lg px-3 py-2">
              <option value="7:00 AM - 4:00 PM">Morning (7AM - 4PM)</option>
              <option value="12:00 PM - 9:00 PM">Afternoon (12PM - 9PM)</option>
              <option value="4:00 PM - 11:00 PM">Evening (4PM - 11PM)</option>
            </select>
          </div>
          
          <div class="mb-4">
            <label class="block text-sm font-medium text-slate-700 mb-1">Status</label>
            <select id="editStaffStatus" class="w-full border border-slate-200 rounded-lg px-3 py-2">
              <option value="on duty">On Duty</option>
              <option value="break">Break</option>
              <option value="off duty">Off Duty</option>
            </select>
          </div>
          
          <div class="mb-4">
            <label class="block text-sm font-medium text-slate-700 mb-1">Assigned Tables</label>
            <input type="text" id="editStaffTables" class="w-full border border-slate-200 rounded-lg px-3 py-2">
          </div>
          
          <div class="mb-4">
            <label class="block text-sm font-medium text-slate-700 mb-1">Rating</label>
            <input type="number" id="editStaffRating" step="0.1" min="0" max="5" class="w-full border border-slate-200 rounded-lg px-3 py-2">
          </div>
        </div>
        
        <div class="flex gap-3 mt-4">
          <button type="submit" class="flex-1 bg-amber-600 text-white py-2 rounded-lg hover:bg-amber-700">Save Changes</button>
          <button type="button" onclick="closeModal('editStaffModal')" class="flex-1 border border-slate-200 py-2 rounded-lg hover:bg-slate-50">Cancel</button>
        </div>
      </form>
    </div>
  </div>

  <!-- View Schedule Modal -->
  <div id="viewScheduleModal" class="modal">
    <div class="modal-content p-6">
      <div class="flex justify-between items-center mb-4">
        <h3 class="text-xl font-semibold" id="scheduleModalTitle">Staff Schedule</h3>
        <button onclick="closeModal('viewScheduleModal')" class="text-slate-400 hover:text-slate-600">
          <i class="fa-solid fa-xmark text-2xl"></i>
        </button>
      </div>
      <div id="scheduleContent" class="space-y-4">
        <!-- Dynamic schedule content will be inserted here -->
      </div>
      <div class="flex gap-3 mt-6">
        <button onclick="closeModal('viewScheduleModal')" class="flex-1 border border-slate-200 py-2 rounded-lg hover:bg-slate-50">Close</button>
      </div>
    </div>
  </div>

  <!-- Create Schedule Modal -->
  <div id="scheduleModal" class="modal">
    <div class="modal-content p-6">
      <div class="flex justify-between items-center mb-4">
        <h3 class="text-xl font-semibold">Create Schedule</h3>
        <button onclick="closeModal('scheduleModal')" class="text-slate-400 hover:text-slate-600">
          <i class="fa-solid fa-xmark text-2xl"></i>
        </button>
      </div>
      <form id="scheduleForm" onsubmit="saveSchedule(event)">
        <div class="mb-4">
          <label class="block text-sm font-medium text-slate-700 mb-1">Shift Date</label>
          <input type="date" id="scheduleDate" required class="w-full border border-slate-200 rounded-lg px-3 py-2">
        </div>
        
        <div class="mb-4">
          <label class="block text-sm font-medium text-slate-700 mb-1">Shift Type</label>
          <select id="scheduleType" class="w-full border border-slate-200 rounded-lg px-3 py-2">
            <option value="Morning">Morning (7AM - 4PM)</option>
            <option value="Afternoon">Afternoon (12PM - 9PM)</option>
            <option value="Evening">Evening (4PM - 11PM)</option>
          </select>
        </div>
        
        <div class="mb-4">
          <label class="block text-sm font-medium text-slate-700 mb-1">Assign Staff</label>
          <div class="space-y-2 max-h-40 overflow-y-auto border border-slate-200 rounded-lg p-3" id="staffCheckboxes">
            <!-- Dynamic staff checkboxes will be inserted here -->
          </div>
        </div>
        
        <div class="mb-4">
          <label class="block text-sm font-medium text-slate-700 mb-1">Notes (optional)</label>
          <textarea id="scheduleNotes" rows="2" class="w-full border border-slate-200 rounded-lg px-3 py-2"></textarea>
        </div>
        
        <div class="flex gap-3 mt-4">
          <button type="submit" class="flex-1 bg-amber-600 text-white py-2 rounded-lg hover:bg-amber-700">Create Schedule</button>
          <button type="button" onclick="closeModal('scheduleModal')" class="flex-1 border border-slate-200 py-2 rounded-lg hover:bg-slate-50">Cancel</button>
        </div>
      </form>
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
        <div class="mb-4">
          <label class="block text-sm font-medium text-slate-700 mb-1">Select Staff</label>
          <select id="assignStaff" class="w-full border border-slate-200 rounded-lg px-3 py-2">
            <!-- Dynamic staff options will be inserted here -->
          </select>
        </div>
        
        <div class="mb-4">
          <label class="block text-sm font-medium text-slate-700 mb-1">Table Assignment</label>
          <input type="text" id="tableAssignment" placeholder="e.g., Tables 1-4 or Section A" class="w-full border border-slate-200 rounded-lg px-3 py-2">
        </div>
        
        <div class="mb-4">
          <label class="block text-sm font-medium text-slate-700 mb-1">Table Numbers</label>
          <div class="grid grid-cols-5 gap-2" id="tableGrid">
            <!-- Table numbers 1-25 will be generated here -->
          </div>
        </div>
        
        <div class="flex gap-3 mt-4">
          <button type="submit" class="flex-1 bg-amber-600 text-white py-2 rounded-lg hover:bg-amber-700">Save Assignment</button>
          <button type="button" onclick="closeModal('assignTablesModal')" class="flex-1 border border-slate-200 py-2 rounded-lg hover:bg-slate-50">Cancel</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Performance Review Modal -->
  <div id="performanceModal" class="modal">
    <div class="modal-content p-6">
      <div class="flex justify-between items-center mb-4">
        <h3 class="text-xl font-semibold">Performance Review</h3>
        <button onclick="closeModal('performanceModal')" class="text-slate-400 hover:text-slate-600">
          <i class="fa-solid fa-xmark text-2xl"></i>
        </button>
      </div>
      <div id="performanceContent">
        <!-- Dynamic performance content will be inserted here -->
      </div>
    </div>
  </div>

  <!-- Export Modal -->
  <div id="exportModal" class="modal">
    <div class="modal-content p-6">
      <div class="flex justify-between items-center mb-4">
        <h3 class="text-xl font-semibold">Export Staff Data</h3>
        <button onclick="closeModal('exportModal')" class="text-slate-400 hover:text-slate-600">
          <i class="fa-solid fa-xmark text-2xl"></i>
        </button>
      </div>
      <div class="space-y-4">
        <p class="text-sm text-slate-600">Choose export format:</p>
        <div class="grid grid-cols-2 gap-3">
          <button onclick="exportData('csv')" class="border border-slate-200 rounded-lg p-4 text-center hover:bg-amber-50 transition">
            <i class="fa-solid fa-file-csv text-2xl text-green-600 mb-2"></i>
            <p class="text-sm font-medium">CSV</p>
          </button>
          <button onclick="exportData('excel')" class="border border-slate-200 rounded-lg p-4 text-center hover:bg-amber-50 transition">
            <i class="fa-solid fa-file-excel text-2xl text-green-600 mb-2"></i>
            <p class="text-sm font-medium">Excel</p>
          </button>
          <button onclick="exportData('pdf')" class="border border-slate-200 rounded-lg p-4 text-center hover:bg-amber-50 transition">
            <i class="fa-solid fa-file-pdf text-2xl text-red-600 mb-2"></i>
            <p class="text-sm font-medium">PDF</p>
          </button>
          <button onclick="exportData('print')" class="border border-slate-200 rounded-lg p-4 text-center hover:bg-amber-50 transition">
            <i class="fa-solid fa-print text-2xl text-blue-600 mb-2"></i>
            <p class="text-sm font-medium">Print</p>
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- APP CONTAINER: flex row (sidebar + main) -->
  <div class="min-h-screen flex flex-col lg:flex-row">

    <!-- ========== SIDEBAR (exact copy from index2.html) ========== -->
    <aside class="lg:w-80 bg-white border-r border-slate-200 shadow-sm lg:min-h-screen shrink-0 overflow-y-auto">
      <!-- brand -->
      <div class="px-5 py-6 border-b border-slate-100 flex items-center gap-2">
        <i class="fa-solid fa-utensils text-amber-600 text-xl"></i>
        <i class="fa-solid fa-bed text-amber-600 text-xl"></i>
        <span class="font-semibold text-lg tracking-tight text-slate-800">HNR<span class="text-amber-600"> Admin</span></span>
      </div>

      <!-- admin badge -->
      <div class="flex items-center gap-3 px-5 py-4 border-b border-slate-100 bg-slate-50/60">
        <div class="h-9 w-9 rounded-full bg-amber-200 flex items-center justify-center text-amber-800 font-bold">A</div>
        <div>
          <p class="font-medium text-sm">Admin User</p>
          <p class="text-xs text-slate-500">role</p>
        </div>
      </div>

      <!-- ===== SIDEBAR MENU (grouped with dropdowns) ===== -->
      <nav class="p-4 space-y-2 text-sm">

        <!-- Dashboard -->
        <a href="../dashboard.html" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-700 hover:bg-amber-50 transition">
          <i class="fa-solid fa-table-cells-large w-5 text-slate-400"></i>
          <span>Dashboard</span>
        </a>

        <!-- HOTEL MANAGEMENT GROUP -->
        <details class="group" open>
          <summary class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-700 hover:bg-amber-50 cursor-pointer">
            <i class="fa-solid fa-hotel w-5 text-slate-400 group-open:text-amber-600"></i>
            <span class="font-medium">HOTEL MANAGEMENT</span>
            <i class="fa-solid fa-chevron-right dropdown-arrow ml-auto text-xs text-slate-400"></i>
          </summary>
          <div class="ml-6 mt-1 space-y-1 pl-3 border-l-2 border-amber-100">
            <a href="../hotel_Management/Front_Desk_Reception.html" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i class="fa-regular fa-reception w-4 text-slate-400"></i> Front Desk / Reception</a>
            <a href="../hotel_Management/room_management.html" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i class="fa-solid fa-bed w-4 text-slate-400"></i> Room Management</a>
            <a href="../hotel_Management/reservation_&_booking.html" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i class="fa-regular fa-calendar-check w-4 text-slate-400"></i> Reservations & Booking</a>
            <a href="../hotel_Management/housekeeping_&_maintenance.html" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i class="fa-solid fa-broom w-4 text-slate-400"></i> Housekeeping & Maintenance</a>
            <a href="../hotel_Management/event_&_conference.html" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i class="fa-regular fa-calendar w-4 text-slate-400"></i> Events & Conference</a>
          </div>
        </details>

        <!-- RESTAURANT MANAGEMENT GROUP - open with Wait Staff Management highlighted -->
        <details class="group" open>
          <summary class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-amber-800 bg-amber-50 cursor-pointer transition-side">
            <i class="fa-solid fa-utensils w-5 text-amber-600"></i>
            <span class="font-medium">RESTAURANT MANAGEMENT</span>
            <i class="fa-solid fa-chevron-right dropdown-arrow ml-auto text-xs text-amber-600"></i>
          </summary>
          <div class="ml-6 mt-1 space-y-1 pl-3 border-l-2 border-amber-200">
            <a href="../restaurant_management/table_reservation.html" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i class="fa-regular fa-clock w-4 text-slate-400"></i> Table Reservation</a>
            <a href="../restaurant_management/menu_management.html" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i class="fa-solid fa-bars w-4 text-slate-400"></i> Menu Management</a>
            <a href="../restaurant_management/orders_pos.html" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i class="fa-solid fa-cash-register w-4 text-slate-400"></i> Orders / POS</a>
            <a href="../restaurant_management/kitchen_orders.html" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i class="fa-solid fa-fire w-4 text-slate-400"></i> Kitchen Orders (KOT)</a>
            <a href="../restaurant_management/wait_staff_management.html" class="flex items-center gap-2 px-4 py-2 rounded-lg bg-amber-100/50 text-amber-700 font-medium"><i class="fa-regular fa-user w-4 text-amber-600"></i> Wait Staff Management</a>
          </div>
        </details>

        <!-- CUSTOMER MANAGEMENT -->
        <details class="group" open>
          <summary class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-700 hover:bg-amber-50 cursor-pointer">
            <i class="fa-regular fa-address-book w-5 text-slate-400 group-open:text-amber-600"></i>
            <span class="font-medium">CUSTOMER MANAGEMENT</span>
            <i class="fa-solid fa-chevron-right dropdown-arrow ml-auto text-xs text-slate-400"></i>
          </summary>
          <div class="ml-6 mt-1 space-y-1 pl-3 border-l-2 border-amber-100">
            <a href="../customer_management/customer_relationship.html" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i class="fa-regular fa-handshake w-4"></i> Guest Relationship (CRM)</a>
            <a href="../customer_management/loyalty_rewards.html" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i class="fa-regular fa-star w-4"></i> Loyalty & Rewards</a>
            <a href="../customer_management/customer_feedback_&_reviews.html" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i class="fa-regular fa-pen-to-square w-4"></i> Customer Feedback & Reviews</a>
          </div>
        </details>

        <!-- OPERATIONS -->
        <details class="group" open>
          <summary class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-700 hover:bg-amber-50 cursor-pointer">
            <i class="fa-solid fa-gears w-5 text-slate-400 group-open:text-amber-600"></i>
            <span class="font-medium">OPERATIONS</span>
            <i class="fa-solid fa-chevron-right dropdown-arrow ml-auto text-xs text-slate-400"></i>
          </summary>
          <div class="ml-6 mt-1 space-y-1 pl-3 border-l-2 border-amber-100">
            <a href="../operations/inventory_&_stocks.html" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i class="fa-solid fa-boxes w-4"></i> Inventory & Stock</a>
            <a href="../operations/billing_&_payment.html" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i class="fa-regular fa-credit-card w-4"></i> Billing & Payments</a>
            <a href="../operations/payment_gateway.html" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i class="fa-solid fa-wifi w-4"></i> Payment Gateway</a>
          </div>
        </details>

        <!-- MARKETING -->
        <details class="group" open>
          <summary class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-700 hover:bg-amber-50 cursor-pointer">
            <i class="fa-solid fa-megaphone w-5 text-slate-400 group-open:text-amber-600"></i>
            <span class="font-medium">MARKETING</span>
            <i class="fa-solid fa-chevron-right dropdown-arrow ml-auto text-xs text-slate-400"></i>
          </summary>
          <div class="ml-6 mt-1 space-y-1 pl-3 border-l-2 border-amber-100">
            <a href="../marketing/hotelmarketing_&_promotions.html" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i class="fa-regular fa-gem w-4"></i> Hotel Marketing & Promotions</a>
            <a href="../marketing/online_ordering_integration.html" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i class="fa-solid fa-cart-shopping w-4"></i> Online Ordering Integration</a>
          </div>
        </details>

        <!-- REPORTS & ANALYTICS -->
        <details class="group" open>
          <summary class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-700 hover:bg-amber-50 cursor-pointer">
            <i class="fa-solid fa-chart-simple w-5 text-slate-400 group-open:text-amber-600"></i>
            <span class="font-medium">REPORTS & ANALYTICS</span>
            <i class="fa-solid fa-chevron-right dropdown-arrow ml-auto text-xs text-slate-400"></i>
          </summary>
          <div class="ml-6 mt-1 space-y-1 pl-3 border-l-2 border-amber-100">
            <a href="../reports_&_analytics/sales_report.html" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i class="fa-solid fa-chart-line w-4"></i> Sales Reports</a>
            <a href="../reports_&_analytics/booking_reports.html" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i class="fa-regular fa-calendar w-4"></i> Booking Reports</a>
            <a href="../reports_&_analytics/analytics_dashboard.html" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i class="fa-solid fa-chart-pie w-4"></i> Analytics Dashboard</a>
          </div>
        </details>

        <!-- SYSTEM -->
        <details class="group" open>
          <summary class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-700 hover:bg-amber-50 cursor-pointer">
            <i class="fa-solid fa-computer w-5 text-slate-400 group-open:text-amber-600"></i>
            <span class="font-medium">SYSTEM</span>
            <i class="fa-solid fa-chevron-right dropdown-arrow ml-auto text-xs text-slate-400"></i>
          </summary>
          <div class="ml-6 mt-1 space-y-1 pl-3 border-l-2 border-amber-100">
            <a href="../system/channel_management.html" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i class="fa-solid fa-code-branch w-4"></i> Channel Management</a>
            <a href="../system/door_lock_integration.html" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i class="fa-solid fa-lock w-4"></i> Door Lock Integration</a>
            <a href="../system/settings.html" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i class="fa-solid fa-sliders w-4"></i> Settings</a>
          </div>
        </details>

        <!-- logout -->
        <div class="border-t border-slate-200 pt-3 mt-3">
          <a href="../../login-register/login_form.html" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-500 hover:bg-red-50 hover:text-red-700">
            <i class="fa-solid fa-arrow-right-from-bracket w-5"></i>
            <span>Logout</span>
          </a>
        </div>
      </nav>
    </aside>

    <!-- ========== MAIN CONTENT (WAIT STAFF MANAGEMENT) ========== -->
    <main class="flex-1 p-5 lg:p-8 overflow-y-auto bg-white">

      <!-- header with title and date -->
      <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <div>
          <h1 class="text-2xl lg:text-3xl font-light text-slate-800">Wait Staff Management</h1>
          <p class="text-sm text-slate-500 mt-0.5">manage schedules, assignments, and performance of wait staff</p>
        </div>
        <div class="flex gap-3 text-sm">
          <span class="bg-white border rounded-full px-4 py-2 flex items-center gap-2 shadow-sm"><i class="fa-regular fa-calendar text-slate-400"></i> May 21, 2025</span>
          <span class="bg-white border rounded-full px-4 py-2 shadow-sm"><i class="fa-regular fa-bell"></i></span>
        </div>
      </div>

      <!-- ===== STATS CARDS ===== -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
          <p class="text-xs text-slate-500">Total staff</p>
          <p class="text-2xl font-semibold" id="totalStaff">24</p>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
          <p class="text-xs text-slate-500">On duty</p>
          <p class="text-2xl font-semibold text-green-600" id="onDutyCount">12</p>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
          <p class="text-xs text-slate-500">Break</p>
          <p class="text-2xl font-semibold text-amber-600" id="breakCount">4</p>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
          <p class="text-xs text-slate-500">Off duty</p>
          <p class="text-2xl font-semibold text-slate-400" id="offDutyCount">8</p>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
          <p class="text-xs text-slate-500">Tables assigned</p>
          <p class="text-2xl font-semibold" id="tablesAssigned">18/24</p>
        </div>
      </div>

      <!-- ===== ACTION BAR ===== -->
      <div class="bg-white rounded-2xl border border-slate-200 p-4 mb-6 flex flex-wrap gap-3 items-center justify-between">
        <div class="flex gap-2 flex-wrap">
          <button onclick="openModal('addStaffModal')" class="bg-amber-600 text-white px-4 py-2 rounded-xl text-sm hover:bg-amber-700 transition">+ add staff</button>
          <button onclick="openScheduleModal()" class="border border-slate-200 px-4 py-2 rounded-xl text-sm hover:bg-slate-50 transition">create schedule</button>
          <button onclick="openAssignTablesModal()" class="border border-slate-200 px-4 py-2 rounded-xl text-sm hover:bg-slate-50 transition">assign tables</button>
          <button onclick="openPerformanceModal()" class="border border-slate-200 px-4 py-2 rounded-xl text-sm hover:bg-slate-50 transition">performance</button>
        </div>
        <div class="relative">
          <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
          <input type="text" id="searchInput" onkeyup="searchStaff()" placeholder="search staff..." class="border border-slate-200 rounded-xl pl-9 pr-4 py-2 text-sm w-64 focus:ring-1 focus:ring-amber-500 outline-none">
        </div>
      </div>

      <!-- ===== STAFF LIST TABLE ===== -->
      <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden mb-8">
        <div class="p-4 border-b border-slate-200 bg-slate-50 flex items-center justify-between">
          <h2 class="font-semibold flex items-center gap-2"><i class="fa-regular fa-user text-amber-600"></i> wait staff roster</h2>
          <div class="flex gap-2">
            <button onclick="openExportModal()" class="text-sm text-amber-700 border border-amber-600 px-3 py-1 rounded-lg hover:bg-amber-50 transition">export</button>
          </div>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full text-sm" id="staffTable">
            <thead class="text-slate-500 text-xs border-b">
              <tr>
                <td class="p-3">Name</td>
                <td class="p-3">Position</td>
                <td class="p-3">Shift</td>
                <td class="p-3">Status</td>
                <td class="p-3">Assigned tables</td>
                <td class="p-3">Performance</td>
                <td class="p-3">Actions</td>
              </tr>
            </thead>
            <tbody class="divide-y" id="staffTableBody">
              <tr data-name="John Doe" data-position="Senior Waiter" data-status="on duty">
                <td class="p-3">
                  <div class="flex items-center gap-2">
                    <div class="h-8 w-8 rounded-full bg-amber-200 flex items-center justify-center text-amber-800 font-bold text-xs">JD</div>
                    <span class="font-medium">John Doe</span>
                  </div>
                </td>
                <td class="p-3">Senior Waiter</td>
                <td class="p-3">7:00 AM - 4:00 PM</td>
                <td class="p-3"><span class="status-badge bg-green-100 text-green-700 px-2 py-0.5 rounded-full text-xs">on duty</span></td>
                <td class="p-3">Tables 1-4</td>
                <td class="p-3">
                  <div class="flex items-center gap-1">
                    <span class="text-yellow-400">★★★★★</span>
                    <span class="text-xs">5.0</span>
                  </div>
                </td>
                <td class="p-3">
                  <button onclick="editStaff('John Doe', 0)" class="text-amber-700 text-xs hover:underline mr-2">edit</button>
                  <button onclick="viewSchedule('John Doe', 0)" class="text-blue-600 text-xs hover:underline">schedule</button>
                </td>
              </tr>
              <tr data-name="Jane Smith" data-position="Waiter" data-status="on duty">
                <td class="p-3">
                  <div class="flex items-center gap-2">
                    <div class="h-8 w-8 rounded-full bg-amber-200 flex items-center justify-center text-amber-800 font-bold text-xs">JS</div>
                    <span class="font-medium">Jane Smith</span>
                  </div>
                </td>
                <td class="p-3">Waiter</td>
                <td class="p-3">7:00 AM - 4:00 PM</td>
                <td class="p-3"><span class="status-badge bg-green-100 text-green-700 px-2 py-0.5 rounded-full text-xs">on duty</span></td>
                <td class="p-3">Tables 5-8</td>
                <td class="p-3">
                  <div class="flex items-center gap-1">
                    <span class="text-yellow-400">★★★★☆</span>
                    <span class="text-xs">4.5</span>
                  </div>
                </td>
                <td class="p-3">
                  <button onclick="editStaff('Jane Smith', 1)" class="text-amber-700 text-xs hover:underline mr-2">edit</button>
                  <button onclick="viewSchedule('Jane Smith', 1)" class="text-blue-600 text-xs hover:underline">schedule</button>
                </td>
              </tr>
              <tr data-name="Maria Cruz" data-position="Head Waiter" data-status="on duty">
                <td class="p-3">
                  <div class="flex items-center gap-2">
                    <div class="h-8 w-8 rounded-full bg-amber-200 flex items-center justify-center text-amber-800 font-bold text-xs">MC</div>
                    <span class="font-medium">Maria Cruz</span>
                  </div>
                </td>
                <td class="p-3">Head Waiter</td>
                <td class="p-3">12:00 PM - 9:00 PM</td>
                <td class="p-3"><span class="status-badge bg-green-100 text-green-700 px-2 py-0.5 rounded-full text-xs">on duty</span></td>
                <td class="p-3">Section A (Tables 10-15)</td>
                <td class="p-3">
                  <div class="flex items-center gap-1">
                    <span class="text-yellow-400">★★★★★</span>
                    <span class="text-xs">5.0</span>
                  </div>
                </td>
                <td class="p-3">
                  <button onclick="editStaff('Maria Cruz', 2)" class="text-amber-700 text-xs hover:underline mr-2">edit</button>
                  <button onclick="viewSchedule('Maria Cruz', 2)" class="text-blue-600 text-xs hover:underline">schedule</button>
                </td>
              </tr>
              <tr data-name="Antonio Reyes" data-position="Waiter" data-status="break">
                <td class="p-3">
                  <div class="flex items-center gap-2">
                    <div class="h-8 w-8 rounded-full bg-amber-200 flex items-center justify-center text-amber-800 font-bold text-xs">AR</div>
                    <span class="font-medium">Antonio Reyes</span>
                  </div>
                </td>
                <td class="p-3">Waiter</td>
                <td class="p-3">12:00 PM - 9:00 PM</td>
                <td class="p-3"><span class="status-badge bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full text-xs">break</span></td>
                <td class="p-3">Tables 16-19</td>
                <td class="p-3">
                  <div class="flex items-center gap-1">
                    <span class="text-yellow-400">★★★★☆</span>
                    <span class="text-xs">4.2</span>
                  </div>
                </td>
                <td class="p-3">
                  <button onclick="editStaff('Antonio Reyes', 3)" class="text-amber-700 text-xs hover:underline mr-2">edit</button>
                  <button onclick="viewSchedule('Antonio Reyes', 3)" class="text-blue-600 text-xs hover:underline">schedule</button>
                </td>
              </tr>
              <tr data-name="Lisa Garcia" data-position="Waiter" data-status="on duty">
                <td class="p-3">
                  <div class="flex items-center gap-2">
                    <div class="h-8 w-8 rounded-full bg-amber-200 flex items-center justify-center text-amber-800 font-bold text-xs">LG</div>
                    <span class="font-medium">Lisa Garcia</span>
                  </div>
                </td>
                <td class="p-3">Waiter</td>
                <td class="p-3">4:00 PM - 11:00 PM</td>
                <td class="p-3"><span class="status-badge bg-green-100 text-green-700 px-2 py-0.5 rounded-full text-xs">on duty</span></td>
                <td class="p-3">Tables 20-24</td>
                <td class="p-3">
                  <div class="flex items-center gap-1">
                    <span class="text-yellow-400">★★★★☆</span>
                    <span class="text-xs">4.8</span>
                  </div>
                </td>
                <td class="p-3">
                  <button onclick="editStaff('Lisa Garcia', 4)" class="text-amber-700 text-xs hover:underline mr-2">edit</button>
                  <button onclick="viewSchedule('Lisa Garcia', 4)" class="text-blue-600 text-xs hover:underline">schedule</button>
                </td>
              </tr>
              <tr data-name="Mike Tan" data-position="Waiter" data-status="off duty">
                <td class="p-3">
                  <div class="flex items-center gap-2">
                    <div class="h-8 w-8 rounded-full bg-amber-200 flex items-center justify-center text-amber-800 font-bold text-xs">MT</div>
                    <span class="font-medium">Mike Tan</span>
                  </div>
                </td>
                <td class="p-3">Waiter</td>
                <td class="p-3">off</td>
                <td class="p-3"><span class="status-badge bg-slate-100 text-slate-600 px-2 py-0.5 rounded-full text-xs">off duty</span></td>
                <td class="p-3">—</td>
                <td class="p-3">
                  <div class="flex items-center gap-1">
                    <span class="text-yellow-400">★★★☆☆</span>
                    <span class="text-xs">3.5</span>
                  </div>
                </td>
                <td class="p-3">
                  <button onclick="editStaff('Mike Tan', 5)" class="text-amber-700 text-xs hover:underline mr-2">edit</button>
                  <button onclick="viewSchedule('Mike Tan', 5)" class="text-blue-600 text-xs hover:underline">schedule</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="p-4 border-t border-slate-200 flex items-center justify-between">
          <span class="text-xs text-slate-500" id="paginationInfo">Showing 1-6 of 24 staff</span>
          <div class="flex gap-2" id="paginationButtons">
            <button onclick="changePage('prev')" class="border border-slate-200 px-3 py-1 rounded-lg text-sm hover:bg-slate-50">Previous</button>
            <button onclick="changePage(1)" class="bg-amber-600 text-white px-3 py-1 rounded-lg text-sm page-btn">1</button>
            <button onclick="changePage(2)" class="border border-slate-200 px-3 py-1 rounded-lg text-sm hover:bg-slate-50 page-btn">2</button>
            <button onclick="changePage(3)" class="border border-slate-200 px-3 py-1 rounded-lg text-sm hover:bg-slate-50 page-btn">3</button>
            <button onclick="changePage(4)" class="border border-slate-200 px-3 py-1 rounded-lg text-sm hover:bg-slate-50 page-btn">4</button>
            <button onclick="changePage('next')" class="border border-slate-200 px-3 py-1 rounded-lg text-sm hover:bg-slate-50">Next</button>
          </div>
        </div>
      </div>

      <!-- ===== BOTTOM: SCHEDULE & PERFORMANCE ===== -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- today's schedule summary -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 p-5">
          <h2 class="font-semibold text-lg flex items-center gap-2 mb-3"><i class="fa-regular fa-calendar text-amber-600"></i> today's shift schedule</h2>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4" id="shiftSummary">
            <div class="border rounded-xl p-3">
              <p class="font-medium text-sm">Morning</p>
              <p class="text-xs text-slate-500">7:00 AM - 4:00 PM</p>
              <p class="text-lg font-semibold mt-1" id="morningCount">4 staff</p>
              <p class="text-xs text-green-600" id="morningStaff">John, Jane, etc.</p>
            </div>
            <div class="border rounded-xl p-3">
              <p class="font-medium text-sm">Afternoon</p>
              <p class="text-xs text-slate-500">12:00 PM - 9:00 PM</p>
              <p class="text-lg font-semibold mt-1" id="afternoonCount">5 staff</p>
              <p class="text-xs text-green-600" id="afternoonStaff">Maria, Antonio, etc.</p>
            </div>
            <div class="border rounded-xl p-3">
              <p class="font-medium text-sm">Evening</p>
              <p class="text-xs text-slate-500">4:00 PM - 11:00 PM</p>
              <p class="text-lg font-semibold mt-1" id="eveningCount">3 staff</p>
              <p class="text-xs text-green-600" id="eveningStaff">Lisa, etc.</p>
            </div>
          </div>
        </div>

        <!-- top performers -->
        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-5">
          <h3 class="font-semibold flex items-center gap-2 mb-3"><i class="fa-regular fa-star text-amber-600"></i> top performers (this week)</h3>
          <ul class="space-y-2" id="topPerformersList">
            <li class="flex justify-between items-center">
              <span>John Doe</span>
              <span class="text-yellow-400 text-sm">★★★★★</span>
            </li>
            <li class="flex justify-between items-center">
              <span>Maria Cruz</span>
              <span class="text-yellow-400 text-sm">★★★★★</span>
            </li>
            <li class="flex justify-between items-center">
              <span>Lisa Garcia</span>
              <span class="text-yellow-400 text-sm">★★★★☆</span>
            </li>
            <li class="flex justify-between items-center">
              <span>Jane Smith</span>
              <span class="text-yellow-400 text-sm">★★★★☆</span>
            </li>
          </ul>
        </div>
      </div>
    </main>
  </div>

  <script>
    // ========== STAFF DATA ==========
    let staffMembers = [
      { name: 'John Doe', initials: 'JD', position: 'Senior Waiter', shift: '7:00 AM - 4:00 PM', status: 'on duty', tables: 'Tables 1-4', rating: 5.0 },
      { name: 'Jane Smith', initials: 'JS', position: 'Waiter', shift: '7:00 AM - 4:00 PM', status: 'on duty', tables: 'Tables 5-8', rating: 4.5 },
      { name: 'Maria Cruz', initials: 'MC', position: 'Head Waiter', shift: '12:00 PM - 9:00 PM', status: 'on duty', tables: 'Section A (Tables 10-15)', rating: 5.0 },
      { name: 'Antonio Reyes', initials: 'AR', position: 'Waiter', shift: '12:00 PM - 9:00 PM', status: 'break', tables: 'Tables 16-19', rating: 4.2 },
      { name: 'Lisa Garcia', initials: 'LG', position: 'Waiter', shift: '4:00 PM - 11:00 PM', status: 'on duty', tables: 'Tables 20-24', rating: 4.8 },
      { name: 'Mike Tan', initials: 'MT', position: 'Waiter', shift: 'off', status: 'off duty', tables: '—', rating: 3.5 }
    ];

    // Schedule data storage
    let staffSchedules = {};

    // ========== PAGINATION VARIABLES ==========
    let currentPage = 1;
    const itemsPerPage = 5;

    // ========== INITIALIZATION ==========
    document.addEventListener('DOMContentLoaded', function() {
      updateStats();
      updatePagination();
      generateTableGrid();
    });

    // ========== MODAL FUNCTIONS ==========
    function openModal(modalId) {
      document.getElementById(modalId).classList.add('show');
    }
    
    function closeModal(modalId) {
      document.getElementById(modalId).classList.remove('show');
    }

    // ========== ADD STAFF FUNCTION ==========
    function saveNewStaff(event) {
      event.preventDefault();
      
      const name = document.getElementById('newStaffName').value;
      const position = document.getElementById('newStaffPosition').value;
      const shift = document.getElementById('newStaffShift').value;
      const status = document.getElementById('newStaffStatus').value;
      const tables = document.getElementById('newStaffTables').value || '—';
      
      // Generate initials
      const initials = name.split(' ').map(n => n[0]).join('').toUpperCase();
      
      // Create new staff object
      const newStaff = {
        name: name,
        initials: initials,
        position: position,
        shift: shift,
        status: status,
        tables: tables,
        rating: 0
      };
      
      staffMembers.push(newStaff);
      
      // Add to table
      addStaffToTable(newStaff, staffMembers.length - 1);
      
      updateStats();
      updatePagination();
      
      closeModal('addStaffModal');
      document.getElementById('addStaffForm').reset();
      showToast('Staff added successfully!', 'success');
    }

    function addStaffToTable(staff, index) {
      const tbody = document.getElementById('staffTableBody');
      const newRow = document.createElement('tr');
      newRow.setAttribute('data-name', staff.name);
      newRow.setAttribute('data-position', staff.position);
      newRow.setAttribute('data-status', staff.status);
      
      // Determine status color
      let statusClass = '';
      if (staff.status === 'on duty') statusClass = 'bg-green-100 text-green-700';
      else if (staff.status === 'break') statusClass = 'bg-amber-100 text-amber-700';
      else statusClass = 'bg-slate-100 text-slate-600';
      
      // Generate star rating
      const stars = getStarRating(staff.rating);
      
      newRow.innerHTML = `
        <td class="p-3">
          <div class="flex items-center gap-2">
            <div class="h-8 w-8 rounded-full bg-amber-200 flex items-center justify-center text-amber-800 font-bold text-xs">${staff.initials}</div>
            <span class="font-medium">${staff.name}</span>
          </div>
        </td>
        <td class="p-3">${staff.position}</td>
        <td class="p-3">${staff.shift}</td>
        <td class="p-3"><span class="status-badge ${statusClass} px-2 py-0.5 rounded-full text-xs">${staff.status}</span></td>
        <td class="p-3">${staff.tables}</td>
        <td class="p-3">
          <div class="flex items-center gap-1">
            <span class="text-yellow-400">${stars}</span>
            <span class="text-xs">${staff.rating.toFixed(1)}</span>
          </div>
        </td>
        <td class="p-3">
          <button onclick="editStaff('${staff.name}', ${index})" class="text-amber-700 text-xs hover:underline mr-2">edit</button>
          <button onclick="viewSchedule('${staff.name}', ${index})" class="text-blue-600 text-xs hover:underline">schedule</button>
        </td>
      `;
      
      tbody.appendChild(newRow);
    }

    function getStarRating(rating) {
      const fullStars = Math.floor(rating);
      const halfStar = rating % 1 >= 0.5 ? 1 : 0;
      const emptyStars = 5 - fullStars - halfStar;
      
      return '★'.repeat(fullStars) + (halfStar ? '½' : '') + '☆'.repeat(emptyStars);
    }

    // ========== EDIT STAFF FUNCTIONS ==========
    function editStaff(name, index) {
      const staff = staffMembers[index];
      if (!staff) return;
      
      document.getElementById('editStaffIndex').value = index;
      document.getElementById('editStaffName').value = staff.name;
      document.getElementById('editStaffPosition').value = staff.position;
      document.getElementById('editStaffShift').value = staff.shift;
      document.getElementById('editStaffStatus').value = staff.status;
      document.getElementById('editStaffTables').value = staff.tables;
      document.getElementById('editStaffRating').value = staff.rating;
      
      openModal('editStaffModal');
    }

    function saveEditStaff(event) {
      event.preventDefault();
      
      const index = document.getElementById('editStaffIndex').value;
      const name = document.getElementById('editStaffName').value;
      const position = document.getElementById('editStaffPosition').value;
      const shift = document.getElementById('editStaffShift').value;
      const status = document.getElementById('editStaffStatus').value;
      const tables = document.getElementById('editStaffTables').value;
      const rating = parseFloat(document.getElementById('editStaffRating').value);
      
      // Update staff in array
      staffMembers[index] = {
        ...staffMembers[index],
        name: name,
        position: position,
        shift: shift,
        status: status,
        tables: tables,
        rating: rating,
        initials: name.split(' ').map(n => n[0]).join('').toUpperCase()
      };
      
      // Refresh table display
      refreshTable();
      
      updateStats();
      
      closeModal('editStaffModal');
      showToast('Staff information updated successfully!', 'success');
    }

    function refreshTable() {
      const tbody = document.getElementById('staffTableBody');
      tbody.innerHTML = '';
      
      staffMembers.forEach((staff, index) => {
        addStaffToTable(staff, index);
      });
      
      updatePagination();
    }

    // ========== SCHEDULE FUNCTIONS ==========
    function viewSchedule(name, index) {
      const staff = staffMembers[index];
      const schedule = staffSchedules[name] || [];
      
      document.getElementById('scheduleModalTitle').textContent = `${name}'s Schedule`;
      
      let scheduleHtml = '';
      
      if (schedule.length === 0) {
        scheduleHtml = '<p class="text-sm text-slate-500 text-center py-4">No schedule found for this staff member.</p>';
      } else {
        scheduleHtml = '<div class="space-y-3">';
        schedule.forEach((shift, i) => {
          scheduleHtml += `
            <div class="border rounded-lg p-3">
              <div class="flex justify-between items-center">
                <div>
                  <p class="font-medium">${shift.date}</p>
                  <p class="text-sm text-slate-600">${shift.shift}</p>
                  <p class="text-xs text-slate-500">${shift.notes || 'No notes'}</p>
                </div>
                <span class="bg-amber-100 text-amber-700 text-xs px-2 py-1 rounded-full">${shift.shiftType}</span>
              </div>
            </div>
          `;
        });
        scheduleHtml += '</div>';
      }
      
      document.getElementById('scheduleContent').innerHTML = scheduleHtml;
      openModal('viewScheduleModal');
    }

    function openScheduleModal() {
      // Populate staff checkboxes
      const container = document.getElementById('staffCheckboxes');
      container.innerHTML = '';
      
      staffMembers.forEach((staff, index) => {
        if (staff.status !== 'off duty') {
          const div = document.createElement('div');
          div.className = 'flex items-center gap-2';
          div.innerHTML = `
            <input type="checkbox" id="staff_${index}" value="${staff.name}" class="rounded border-slate-300">
            <label for="staff_${index}">${staff.name} (${staff.position})</label>
          `;
          container.appendChild(div);
        }
      });
      
      openModal('scheduleModal');
    }

    function saveSchedule(event) {
      event.preventDefault();
      
      const date = document.getElementById('scheduleDate').value;
      const shiftType = document.getElementById('scheduleType').value;
      const notes = document.getElementById('scheduleNotes').value;
      
      // Get shift time based on type
      let shiftTime = '';
      if (shiftType === 'Morning') shiftTime = '7:00 AM - 4:00 PM';
      else if (shiftType === 'Afternoon') shiftTime = '12:00 PM - 9:00 PM';
      else shiftTime = '4:00 PM - 11:00 PM';
      
      // Get selected staff
      const selectedStaff = [];
      const checkboxes = document.querySelectorAll('#staffCheckboxes input:checked');
      checkboxes.forEach(cb => {
        selectedStaff.push(cb.value);
      });
      
      if (selectedStaff.length === 0) {
        showToast('Please select at least one staff member', 'error');
        return;
      }
      
      // Save schedule for each selected staff
      selectedStaff.forEach(staffName => {
        if (!staffSchedules[staffName]) {
          staffSchedules[staffName] = [];
        }
        
        staffSchedules[staffName].push({
          date: date,
          shiftType: shiftType,
          shift: shiftTime,
          notes: notes
        });
      });
      
      showToast(`Schedule created for ${selectedStaff.length} staff members`, 'success');
      closeModal('scheduleModal');
      document.getElementById('scheduleForm').reset();
    }

    // ========== ASSIGN TABLES FUNCTIONS ==========
    function openAssignTablesModal() {
      // Populate staff dropdown
      const select = document.getElementById('assignStaff');
      select.innerHTML = '<option value="">Select Staff...</option>';
      
      staffMembers.forEach(staff => {
        const option = document.createElement('option');
        option.value = staff.name;
        option.textContent = `${staff.name} (${staff.position})`;
        select.appendChild(option);
      });
      
      openModal('assignTablesModal');
    }

    function generateTableGrid() {
      const grid = document.getElementById('tableGrid');
      grid.innerHTML = '';
      
      for (let i = 1; i <= 25; i++) {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'border border-slate-200 rounded-lg p-2 text-xs hover:bg-amber-50 transition';
        btn.textContent = i;
        btn.onclick = function() {
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
      
      const staffName = document.getElementById('assignStaff').value;
      const tables = document.getElementById('tableAssignment').value;
      
      if (!staffName) {
        showToast('Please select a staff member', 'error');
        return;
      }
      
      if (!tables) {
        showToast('Please assign tables', 'error');
        return;
      }
      
      // Update staff's table assignment
      const staffIndex = staffMembers.findIndex(s => s.name === staffName);
      if (staffIndex !== -1) {
        staffMembers[staffIndex].tables = tables;
        
        // Update table display
        refreshTable();
      }
      
      showToast(`Tables assigned to ${staffName}`, 'success');
      closeModal('assignTablesModal');
      document.getElementById('assignTablesForm').reset();
    }

    // ========== PERFORMANCE FUNCTIONS ==========
    function openPerformanceModal() {
      const content = document.getElementById('performanceContent');
      
      let html = '<div class="space-y-4">';
      
      // Sort staff by rating
      const sortedStaff = [...staffMembers].sort((a, b) => b.rating - a.rating);
      
      sortedStaff.forEach((staff, index) => {
        const stars = getStarRating(staff.rating);
        html += `
          <div class="border-b pb-3">
            <div class="flex justify-between items-center">
              <div>
                <p class="font-medium">${index + 1}. ${staff.name}</p>
                <p class="text-xs text-slate-500">${staff.position}</p>
              </div>
              <div class="text-right">
                <div class="text-yellow-400">${stars}</div>
                <p class="text-sm font-medium">${staff.rating.toFixed(1)}</p>
              </div>
            </div>
          </div>
        `;
      });
      
      html += '</div>';
      content.innerHTML = html;
      
      openModal('performanceModal');
    }

    // ========== EXPORT FUNCTIONS ==========
    function openExportModal() {
      openModal('exportModal');
    }

    function exportData(format) {
      closeModal('exportModal');
      showToast(`Preparing ${format.toUpperCase()} export...`, 'info');
      
      setTimeout(() => {
        // Simulate export completion
        showToast(`Staff data exported as ${format.toUpperCase()} successfully!`, 'success');
      }, 2000);
    }

    // ========== SEARCH FUNCTION ==========
    function searchStaff() {
      const searchTerm = document.getElementById('searchInput').value.toLowerCase();
      const rows = document.querySelectorAll('#staffTableBody tr');
      
      rows.forEach(row => {
        const name = row.getAttribute('data-name').toLowerCase();
        const position = row.getAttribute('data-position').toLowerCase();
        const status = row.getAttribute('data-status').toLowerCase();
        
        if (name.includes(searchTerm) || position.includes(searchTerm) || status.includes(searchTerm)) {
          row.classList.remove('hidden-row');
        } else {
          row.classList.add('hidden-row');
        }
      });
      
      currentPage = 1;
      updatePagination();
    }

    // ========== UPDATE STATISTICS ==========
    function updateStats() {
      const total = staffMembers.length;
      const onDuty = staffMembers.filter(s => s.status === 'on duty').length;
      const break_ = staffMembers.filter(s => s.status === 'break').length;
      const offDuty = staffMembers.filter(s => s.status === 'off duty').length;
      
      document.getElementById('totalStaff').textContent = total;
      document.getElementById('onDutyCount').textContent = onDuty;
      document.getElementById('breakCount').textContent = break_;
      document.getElementById('offDutyCount').textContent = offDuty;
      
      // Update shift counts
      const morning = staffMembers.filter(s => s.shift === '7:00 AM - 4:00 PM').length;
      const afternoon = staffMembers.filter(s => s.shift === '12:00 PM - 9:00 PM').length;
      const evening = staffMembers.filter(s => s.shift === '4:00 PM - 11:00 PM').length;
      
      document.getElementById('morningCount').textContent = morning + ' staff';
      document.getElementById('afternoonCount').textContent = afternoon + ' staff';
      document.getElementById('eveningCount').textContent = evening + ' staff';
      
      // Update staff names in shift summary
      const morningStaff = staffMembers.filter(s => s.shift === '7:00 AM - 4:00 PM').map(s => s.name.split(' ')[0]).join(', ');
      const afternoonStaff = staffMembers.filter(s => s.shift === '12:00 PM - 9:00 PM').map(s => s.name.split(' ')[0]).join(', ');
      const eveningStaff = staffMembers.filter(s => s.shift === '4:00 PM - 11:00 PM').map(s => s.name.split(' ')[0]).join(', ');
      
      document.getElementById('morningStaff').textContent = morningStaff || 'None';
      document.getElementById('afternoonStaff').textContent = afternoonStaff || 'None';
      document.getElementById('eveningStaff').textContent = eveningStaff || 'None';
    }

    // ========== PAGINATION FUNCTIONS ==========
    function updatePagination() {
      const rows = document.querySelectorAll('#staffTableBody tr:not(.hidden-row)');
      const totalItems = rows.length;
      const totalPages = Math.max(1, Math.ceil(totalItems / itemsPerPage));
      
      if (currentPage > totalPages) currentPage = totalPages;
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
      
      updatePaginationButtons(totalPages);
    }

    function updatePaginationButtons(totalPages) {
      const container = document.getElementById('paginationButtons');
      if (!container) return;
      
      let buttons = `<button onclick="changePage('prev')" class="border border-slate-200 px-3 py-1 rounded-lg text-sm hover:bg-slate-50" ${currentPage === 1 ? 'disabled' : ''}>Previous</button>`;
      
      for (let i = 1; i <= totalPages; i++) {
        buttons += `<button onclick="changePage(${i})" class="border px-3 py-1 rounded-lg text-sm page-btn ${i === currentPage ? 'bg-amber-600 text-white' : 'border-slate-200 hover:bg-slate-50'}">${i}</button>`;
      }
      
      buttons += `<button onclick="changePage('next')" class="border border-slate-200 px-3 py-1 rounded-lg text-sm hover:bg-slate-50" ${currentPage === totalPages ? 'disabled' : ''}>Next</button>`;
      
      container.innerHTML = buttons;
    }

    function changePage(direction) {
      const rows = document.querySelectorAll('#staffTableBody tr:not(.hidden-row)');
      const totalPages = Math.max(1, Math.ceil(rows.length / itemsPerPage));
      
      if (direction === 'prev' && currentPage > 1) {
        currentPage--;
      } else if (direction === 'next' && currentPage < totalPages) {
        currentPage++;
      } else if (typeof direction === 'number') {
        if (direction >= 1 && direction <= totalPages) {
          currentPage = direction;
        }
      }
      
      updatePagination();
    }

    // ========== TOAST NOTIFICATION ==========
    function showToast(message, type = 'success') {
      const toast = document.getElementById('toast');
      toast.textContent = message;
      toast.className = 'toast';
      
      if (type === 'error') {
        toast.classList.add('error');
      } else if (type === 'info') {
        toast.classList.add('info');
      }
      
      toast.classList.add('show');
      
      setTimeout(() => {
        toast.classList.remove('show');
      }, 3000);
    }

    // Close modals when clicking outside
    window.addEventListener('click', function(event) {
      if (event.target.classList.contains('modal')) {
        event.target.classList.remove('show');
      }
    });

    // Close modals with Escape key
    document.addEventListener('keydown', function(event) {
      if (event.key === 'Escape') {
        document.querySelectorAll('.modal.show').forEach(modal => {
          modal.classList.remove('show');
        });
      }
    });
  </script>
</body>
</html>