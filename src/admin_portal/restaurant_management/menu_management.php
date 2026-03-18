<?php
/**
 * View - Admin Menu Management
 */
require_once '../../../controller/admin/get/restaurant/menu_management.php';

// Set current page for navigation
$current_page = 'menu_management';
?>
<!DOCTYPE html>
<html lang="en">

  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin · Menu Management</title>
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

      .bulk-selected {
        background-color: #fef3c7 !important;
      }

      .hidden-row {
        display: none;
      }

      .status-badge.disabled {
        opacity: 0.7;
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

      .low-stock {
        background-color: #fee2e2;
      }
    </style>
  </head>

  <body class="bg-white font-sans antialiased">

    <!-- Toast notification -->
    <div id="toast" class="toast hidden">
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

    <!-- Add Item Modal -->
    <div id="addItemModal" class="modal">
      <div class="modal-content p-6">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-xl font-semibold">Add New Menu Item</h3>
          <button onclick="closeModal('addItemModal')" class="text-slate-400 hover:text-slate-600">
            <i class="fa-solid fa-xmark text-2xl"></i>
          </button>
        </div>
        <form id="addItemForm" onsubmit="saveNewItem(event)">
          <input type="hidden" name="action" value="add_item">

          <div class="grid grid-cols-2 gap-4">
            <div class="mb-4 col-span-2">
              <label class="block text-sm font-medium text-slate-700 mb-1">Item Name</label>
              <input type="text" id="newItemName" name="name" required
                class="w-full border border-slate-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-amber-500 outline-none">
            </div>

            <div class="mb-4">
              <label class="block text-sm font-medium text-slate-700 mb-1">Category</label>
              <select id="newItemCategory" name="category" onchange="generateItemCode()"
                class="w-full border border-slate-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-amber-500 outline-none">
                <option value="Mains">Mains</option>
                <option value="Appetizers">Appetizers</option>
                <option value="Desserts">Desserts</option>
                <option value="Beverages">Beverages</option>
                <option value="Specials">Specials</option>
                <option value="Sides">Sides</option>
              </select>
            </div>

            <div class="mb-4">
              <label class="block text-sm font-medium text-slate-700 mb-1">Item Code</label>
              <input type="text" id="newItemCode" name="item_code" readonly
                class="w-full border border-slate-200 rounded-lg px-3 py-2 bg-slate-50">
            </div>

            <div class="mb-4">
              <label class="block text-sm font-medium text-slate-700 mb-1">Price (₱)</label>
              <input type="number" id="newItemPrice" name="price" required min="0" step="0.01"
                class="w-full border border-slate-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-amber-500 outline-none">
            </div>

            <div class="mb-4">
              <label class="block text-sm font-medium text-slate-700 mb-1">Cost (₱)</label>
              <input type="number" id="newItemCost" name="cost" required min="0" step="0.01"
                class="w-full border border-slate-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-amber-500 outline-none">
            </div>

            <div class="mb-4">
              <label class="block text-sm font-medium text-slate-700 mb-1">Stock</label>
              <input type="number" id="newItemStock" name="stock" required min="0"
                class="w-full border border-slate-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-amber-500 outline-none">
            </div>

            <div class="mb-4">
              <label class="block text-sm font-medium text-slate-700 mb-1">Prep Time (min)</label>
              <input type="number" id="newItemPrepTime" name="preparation_time" value="15" min="1"
                class="w-full border border-slate-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-amber-500 outline-none">
            </div>

            <div class="mb-4">
              <label class="block text-sm font-medium text-slate-700 mb-1">Status</label>
              <select id="newItemStatus" name="status"
                class="w-full border border-slate-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-amber-500 outline-none">
                <option value="available">Available</option>
                <option value="out_of_stock">Out of Stock</option>
                <option value="special">Special</option>
              </select>
            </div>

            <div class="mb-4 col-span-2">
              <label class="block text-sm font-medium text-slate-700 mb-1">Description (optional)</label>
              <textarea id="newItemDescription" name="description" rows="2"
                class="w-full border border-slate-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-amber-500 outline-none"></textarea>
            </div>
          </div>

          <div class="flex gap-3 mt-4">
            <button type="submit" class="flex-1 bg-amber-600 text-white py-2 rounded-lg hover:bg-amber-700">Add
              Item</button>
            <button type="button" onclick="closeModal('addItemModal')"
              class="flex-1 border border-slate-200 py-2 rounded-lg hover:bg-slate-50">Cancel</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Edit Item Modal -->
    <div id="editItemModal" class="modal">
      <div class="modal-content p-6">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-xl font-semibold">Edit Menu Item</h3>
          <button onclick="closeModal('editItemModal')" class="text-slate-400 hover:text-slate-600">
            <i class="fa-solid fa-xmark text-2xl"></i>
          </button>
        </div>
        <form id="editItemForm" onsubmit="updateItem(event)">
          <input type="hidden" name="action" value="update_item">
          <input type="hidden" id="editItemId" name="item_id">

          <div class="grid grid-cols-2 gap-4">
            <div class="mb-4 col-span-2">
              <label class="block text-sm font-medium text-slate-700 mb-1">Item Name</label>
              <input type="text" id="editItemName" name="name" required
                class="w-full border border-slate-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-amber-500 outline-none">
            </div>

            <div class="mb-4">
              <label class="block text-sm font-medium text-slate-700 mb-1">Category</label>
              <select id="editItemCategory" name="category"
                class="w-full border border-slate-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-amber-500 outline-none">
                <option value="Mains">Mains</option>
                <option value="Appetizers">Appetizers</option>
                <option value="Desserts">Desserts</option>
                <option value="Beverages">Beverages</option>
                <option value="Specials">Specials</option>
                <option value="Sides">Sides</option>
              </select>
            </div>

            <div class="mb-4">
              <label class="block text-sm font-medium text-slate-700 mb-1">Price (₱)</label>
              <input type="number" id="editItemPrice" name="price" required min="0" step="0.01"
                class="w-full border border-slate-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-amber-500 outline-none">
            </div>

            <div class="mb-4">
              <label class="block text-sm font-medium text-slate-700 mb-1">Cost (₱)</label>
              <input type="number" id="editItemCost" name="cost" required min="0" step="0.01"
                class="w-full border border-slate-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-amber-500 outline-none">
            </div>

            <div class="mb-4">
              <label class="block text-sm font-medium text-slate-700 mb-1">Stock</label>
              <input type="number" id="editItemStock" name="stock" required min="0"
                class="w-full border border-slate-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-amber-500 outline-none">
            </div>

            <div class="mb-4">
              <label class="block text-sm font-medium text-slate-700 mb-1">Prep Time (min)</label>
              <input type="number" id="editItemPrepTime" name="preparation_time" min="1"
                class="w-full border border-slate-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-amber-500 outline-none">
            </div>

            <div class="mb-4">
              <label class="block text-sm font-medium text-slate-700 mb-1">Status</label>
              <select id="editItemStatus" name="status"
                class="w-full border border-slate-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-amber-500 outline-none">
                <option value="available">Available</option>
                <option value="out_of_stock">Out of Stock</option>
                <option value="special">Special</option>
              </select>
            </div>

            <div class="mb-4 col-span-2">
              <label class="block text-sm font-medium text-slate-700 mb-1">Description (optional)</label>
              <textarea id="editItemDescription" name="description" rows="2"
                class="w-full border border-slate-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-amber-500 outline-none"></textarea>
            </div>
          </div>

          <div class="flex gap-3 mt-4">
            <button type="submit" class="flex-1 bg-amber-600 text-white py-2 rounded-lg hover:bg-amber-700">Update
              Item</button>
            <button type="button" onclick="closeModal('editItemModal')"
              class="flex-1 border border-slate-200 py-2 rounded-lg hover:bg-slate-50">Cancel</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Add Category Modal -->
    <div id="addCategoryModal" class="modal">
      <div class="modal-content p-6">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-xl font-semibold">Add New Category</h3>
          <button onclick="closeModal('addCategoryModal')" class="text-slate-400 hover:text-slate-600">
            <i class="fa-solid fa-xmark text-2xl"></i>
          </button>
        </div>
        <form id="addCategoryForm" onsubmit="saveNewCategory(event)">
          <input type="hidden" name="action" value="add_category">

          <div class="mb-4">
            <label class="block text-sm font-medium text-slate-700 mb-1">Category Name</label>
            <input type="text" id="newCategoryName" name="category_name" required
              class="w-full border border-slate-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-amber-500 outline-none">
          </div>

          <div class="mb-4">
            <label class="block text-sm font-medium text-slate-700 mb-1">Description (optional)</label>
            <textarea id="newCategoryDescription" name="description" rows="2"
              class="w-full border border-slate-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-amber-500 outline-none"></textarea>
          </div>

          <div class="mb-4">
            <label class="block text-sm font-medium text-slate-700 mb-1">Display Order</label>
            <input type="number" id="newCategoryOrder" name="display_order" value="1" min="1"
              class="w-full border border-slate-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-amber-500 outline-none">
          </div>

          <div class="flex gap-3 mt-4">
            <button type="submit" class="flex-1 bg-amber-600 text-white py-2 rounded-lg hover:bg-amber-700">Add
              Category</button>
            <button type="button" onclick="closeModal('addCategoryModal')"
              class="flex-1 border border-slate-200 py-2 rounded-lg hover:bg-slate-50">Cancel</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Restock Modal -->
    <div id="restockModal" class="modal">
      <div class="modal-content p-6">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-xl font-semibold">Restock Item</h3>
          <button onclick="closeModal('restockModal')" class="text-slate-400 hover:text-slate-600">
            <i class="fa-solid fa-xmark text-2xl"></i>
          </button>
        </div>
        <input type="hidden" id="restockItemId">
        <div class="mb-4">
          <label class="block text-sm font-medium text-slate-700 mb-1">Quantity to Add</label>
          <input type="number" id="restockQuantity" value="50" min="1"
            class="w-full border border-slate-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-amber-500 outline-none">
        </div>
        <div class="flex gap-3">
          <button onclick="processRestock()"
            class="flex-1 bg-amber-600 text-white py-2 rounded-lg hover:bg-amber-700">Restock</button>
          <button onclick="closeModal('restockModal')"
            class="flex-1 border border-slate-200 py-2 rounded-lg hover:bg-slate-50">Cancel</button>
        </div>
      </div>
    </div>

    <!-- Bulk Edit Panel -->
    <div id="bulkEditPanel"
      class="fixed bottom-0 left-0 right-0 bg-white border-t border-amber-200 shadow-lg p-4 transform translate-y-full transition-transform duration-300 z-50"
      style="margin-left: 320px;">
      <div class="container mx-auto flex flex-wrap items-center justify-between">
        <div>
          <span class="font-semibold" id="selectedCount">0</span> items selected
        </div>
        <div class="flex gap-3 flex-wrap">
          <select id="bulkStatus" class="border border-slate-200 rounded-lg px-3 py-2 text-sm">
            <option value="">Change Status...</option>
            <option value="available">Available</option>
            <option value="out_of_stock">Out of Stock</option>
            <option value="special">Special</option>
            <option value="disabled">Disabled</option>
          </select>
          <select id="bulkCategory" class="border border-slate-200 rounded-lg px-3 py-2 text-sm">
            <option value="">Change Category...</option>
            <?php foreach ($categories as $cat): ?>
              <option value="<?php echo $cat['category']; ?>"><?php echo $cat['category']; ?></option>
            <?php endforeach; ?>
          </select>
          <input type="number" id="bulkPrice" placeholder="Set Price"
            class="border border-slate-200 rounded-lg px-3 py-2 text-sm w-32">
          <button onclick="applyBulkEdit()"
            class="bg-amber-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-amber-700">Apply</button>
          <button onclick="cancelBulkEdit()"
            class="border border-slate-200 px-4 py-2 rounded-lg text-sm hover:bg-slate-50">Cancel</button>
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
            <h1 class="text-2xl lg:text-3xl font-light text-slate-800">Menu Management</h1>
            <p class="text-sm text-slate-500 mt-0.5">manage food items, categories, pricing, and availability</p>
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

        <!-- Low Stock Alert -->
        <?php if (!empty($lowStockItems)): ?>
          <div class="bg-red-50 border border-red-200 rounded-2xl p-4 mb-6 flex items-center justify-between">
            <div class="flex items-center gap-3">
              <div class="h-10 w-10 rounded-full bg-red-200 flex items-center justify-center text-red-700">
                <i class="fa-regular fa-triangle-exclamation"></i>
              </div>
              <div>
                <p class="font-medium text-red-800">Low Stock Alert</p>
                <p class="text-sm text-red-600">
                  <?php foreach ($lowStockItems as $item): ?>
                    <?php echo $item['name']; ?> (<?php echo $item['stock']; ?> left) ·
                  <?php endforeach; ?>
                </p>
              </div>
            </div>
            <button onclick="showLowStockItems()"
              class="bg-red-600 text-white px-4 py-2 rounded-xl text-sm hover:bg-red-700">
              View All
            </button>
          </div>
        <?php endif; ?>

        <!-- STATS CARDS -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
          <div
            class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm cursor-pointer hover:shadow-md transition"
            onclick="filterByStatus('all')">
            <p class="text-xs text-slate-500">Total items</p>
            <p class="text-2xl font-semibold" id="totalItems"><?php echo $stats['total_items']; ?></p>
          </div>
          <div
            class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm cursor-pointer hover:shadow-md transition"
            onclick="filterByCategory('all')">
            <p class="text-xs text-slate-500">Categories</p>
            <p class="text-2xl font-semibold" id="totalCategories"><?php echo $stats['total_categories']; ?></p>
          </div>
          <div
            class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm cursor-pointer hover:shadow-md transition"
            onclick="filterByStatus('available')">
            <p class="text-xs text-slate-500">Available</p>
            <p class="text-2xl font-semibold text-green-600" id="availableCount"><?php echo $stats['available']; ?></p>
          </div>
          <div
            class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm cursor-pointer hover:shadow-md transition"
            onclick="filterByStatus('out_of_stock')">
            <p class="text-xs text-slate-500">Out of stock</p>
            <p class="text-2xl font-semibold text-rose-600" id="outOfStockCount"><?php echo $stats['out_of_stock']; ?>
            </p>
          </div>
          <div
            class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm cursor-pointer hover:shadow-md transition"
            onclick="filterByStatus('special')">
            <p class="text-xs text-slate-500">Specials</p>
            <p class="text-2xl font-semibold text-amber-600" id="specialsCount"><?php echo $stats['specials']; ?></p>
          </div>
        </div>

        <!-- ACTION BAR -->
        <div
          class="bg-white rounded-2xl border border-slate-200 p-4 mb-6 flex flex-wrap gap-3 items-center justify-between">
          <div class="flex gap-2 flex-wrap">
            <button onclick="openModal('addItemModal')"
              class="bg-amber-600 text-white px-4 py-2 rounded-xl text-sm hover:bg-amber-700 transition">+ add new
              item</button>
            <button onclick="openModal('addCategoryModal')"
              class="bg-amber-600 text-white px-4 py-2 rounded-xl text-sm hover:bg-amber-700 transition">+ add
              category</button>
            <button onclick="toggleBulkEdit()"
              class="border border-slate-200 px-4 py-2 rounded-xl text-sm hover:bg-slate-50 transition">bulk
              edit</button>
          </div>
          <div class="relative">
            <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
            <input type="text" id="searchInput" onkeyup="searchMenu()" placeholder="search menu items..."
              value="<?php echo htmlspecialchars($searchFilter); ?>"
              class="border border-slate-200 rounded-xl pl-9 pr-4 py-2 text-sm w-64 focus:ring-1 focus:ring-amber-500 outline-none">
          </div>
        </div>

        <!-- CATEGORY TABS -->
        <div class="flex flex-wrap gap-2 border-b border-slate-200 pb-2 mb-6">
          <button id="catAll" onclick="filterByCategory('all', event)"
            class="category-filter px-4 py-2 <?php echo $categoryFilter == 'all' ? 'bg-amber-600 text-white' : 'bg-white border border-slate-200 hover:bg-slate-50'; ?> rounded-full text-sm">
            all
          </button>
          <?php
          $categoryList = ['appetizers', 'mains', 'desserts', 'beverages', 'specials', 'sides'];
          foreach ($categoryList as $cat):
            ?>
            <button id="cat<?php echo ucfirst($cat); ?>" onclick="filterByCategory('<?php echo $cat; ?>', event)"
              class="category-filter px-4 py-2 <?php echo $categoryFilter == $cat ? 'bg-amber-600 text-white' : 'bg-white border border-slate-200 hover:bg-slate-50'; ?> rounded-full text-sm">
              <?php echo $cat; ?>
            </button>
          <?php endforeach; ?>
        </div>

        <!-- MENU ITEMS TABLE -->
        <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden mb-8">
          <div class="overflow-x-auto">
            <table class="w-full text-sm" id="menuTable">
              <thead class="bg-slate-50 text-slate-500 text-xs border-b">
                <tr>
                  <td class="p-4 w-10">
                    <input type="checkbox" id="selectAll" onclick="toggleSelectAll()" class="rounded border-slate-300"
                      style="display: none;">
                  </td>
                  <td class="p-4">Item</td>
                  <td class="p-4">Category</td>
                  <td class="p-4">Price</td>
                  <td class="p-4">Cost</td>
                  <td class="p-4">Stock</td>
                  <td class="p-4">Status</td>
                  <td class="p-4">Actions</td>
                </tr>
              </thead>
              <tbody class="divide-y" id="menuTableBody">
                <?php if (empty($menuItems)): ?>
                  <tr>
                    <td colspan="8" class="p-8 text-center text-slate-500">No menu items found</td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($menuItems as $item):
                    $statusClass = '';
                    $statusText = ucfirst(str_replace('_', ' ', $item['status']));

                    if ($item['status'] == 'available' && $item['is_available']) {
                      $statusClass = 'bg-green-100 text-green-700';
                    } elseif ($item['status'] == 'out_of_stock' || $item['stock'] <= 0) {
                      $statusClass = 'bg-rose-100 text-rose-700';
                      $statusText = 'Out of Stock';
                    } elseif ($item['status'] == 'special') {
                      $statusClass = 'bg-amber-100 text-amber-700';
                    } else {
                      $statusClass = 'bg-slate-100 text-slate-700';
                    }

                    $lowStockClass = ($item['stock'] <= 10 && $item['stock'] > 0) ? 'low-stock' : '';
                    ?>
                    <tr data-id="<?php echo $item['id']; ?>" data-category="<?php echo strtolower($item['category']); ?>"
                      data-status="<?php echo $item['status']; ?>" data-available="<?php echo $item['is_available']; ?>"
                      class="<?php echo $lowStockClass; ?>">
                      <td class="p-4">
                        <input type="checkbox" class="item-checkbox rounded border-slate-300"
                          onclick="updateSelectedCount()" style="display: none;">
                      </td>
                      <td class="p-4">
                        <div class="flex items-center gap-3">
                          <div class="h-10 w-10 bg-slate-200 rounded-lg flex items-center justify-center text-slate-500">
                            <i class="fa-regular fa-image text-xs"></i>
                          </div>
                          <div>
                            <span class="font-medium"><?php echo htmlspecialchars($item['name']); ?></span>
                            <p class="text-xs text-slate-400">#<?php echo $item['item_code']; ?></p>
                          </div>
                        </div>
                      </td>
                      <td class="p-4"><?php echo $item['category']; ?></td>
                      <td class="p-4 font-medium">₱<?php echo number_format($item['price'], 2); ?></td>
                      <td class="p-4">₱<?php echo number_format($item['cost'], 2); ?></td>
                      <td class="p-4 <?php echo $item['stock'] <= 10 ? 'text-rose-600 font-medium' : ''; ?>">
                        <?php echo $item['stock']; ?>
                        <?php if ($item['stock'] <= 10 && $item['stock'] > 0): ?>
                          <i class="fa-regular fa-triangle-exclamation text-rose-500 ml-1" title="Low stock"></i>
                        <?php endif; ?>
                      </td>
                      <td class="p-4">
                        <span class="status-badge <?php echo $statusClass; ?> px-2 py-0.5 rounded-full text-xs">
                          <?php echo $statusText; ?>
                        </span>
                      </td>
                      <td class="p-4">
                        <button class="text-amber-700 text-xs hover:underline mr-2"
                          onclick="editItem(<?php echo $item['id']; ?>)">
                          <i class="fa-regular fa-pen-to-square"></i> edit
                        </button>
                        <?php if ($item['status'] == 'out_of_stock' || $item['stock'] <= 0): ?>
                          <button class="text-emerald-600 text-xs hover:underline"
                            onclick="openRestockModal(<?php echo $item['id']; ?>)">
                            <i class="fa-regular fa-box"></i> restock
                          </button>
                        <?php else: ?>
                          <button class="text-rose-600 text-xs hover:underline"
                            onclick="toggleItemStatus(<?php echo $item['id']; ?>, this)">
                            <i class="fa-regular fa-ban"></i> disable
                          </button>
                        <?php endif; ?>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <div class="p-4 border-t border-slate-200 flex items-center justify-between">
            <span class="text-xs text-slate-500" id="paginationInfo">
              Showing
              <?php echo (($currentPage - 1) * $limit + 1); ?>-<?php echo min($currentPage * $limit, $totalItems); ?> of
              <?php echo $totalItems; ?> items
            </span>
            <div class="flex gap-2" id="paginationButtons">
              <button onclick="changePage('prev')"
                class="border border-slate-200 px-3 py-1 rounded-lg text-sm hover:bg-slate-50 <?php echo $currentPage <= 1 ? 'opacity-50 cursor-not-allowed' : ''; ?>"
                <?php echo $currentPage <= 1 ? 'disabled' : ''; ?>>
                Previous
              </button>

              <?php for ($i = 1; $i <= min(5, $totalPages); $i++): ?>
                <button onclick="changePage(<?php echo $i; ?>)"
                  class="border px-3 py-1 rounded-lg text-sm page-btn <?php echo $i == $currentPage ? 'bg-amber-600 text-white' : 'border-slate-200 hover:bg-slate-50'; ?>">
                  <?php echo $i; ?>
                </button>
              <?php endfor; ?>

              <?php if ($totalPages > 5): ?>
                <span class="px-2">...</span>
                <button onclick="changePage(<?php echo $totalPages; ?>)"
                  class="border border-slate-200 px-3 py-1 rounded-lg text-sm hover:bg-slate-50">
                  <?php echo $totalPages; ?>
                </button>
              <?php endif; ?>

              <button onclick="changePage('next')"
                class="border border-slate-200 px-3 py-1 rounded-lg text-sm hover:bg-slate-50 <?php echo $currentPage >= $totalPages ? 'opacity-50 cursor-not-allowed' : ''; ?>"
                <?php echo $currentPage >= $totalPages ? 'disabled' : ''; ?>>
                Next
              </button>
            </div>
          </div>
        </div>
      </main>
    </div>

    <script>
      // ========== GLOBAL VARIABLES ==========
      let currentPage = <?php echo $currentPage; ?>;
      const totalPages = <?php echo $totalPages; ?>;
      let bulkEditActive = false;

      // ========== TOAST NOTIFICATION ==========
      function showToast(message, type = 'success') {
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

      // ========== ITEM CODE GENERATION ==========
      function generateItemCode() {
        const category = document.getElementById('newItemCategory').value;

        let prefix = '';
        switch (category) {
          case 'Mains': prefix = 'M'; break;
          case 'Appetizers': prefix = 'A'; break;
          case 'Desserts': prefix = 'D'; break;
          case 'Beverages': prefix = 'B'; break;
          case 'Specials': prefix = 'S'; break;
          case 'Sides': prefix = 'SD'; break;
          default: prefix = 'X';
        }

        // Get existing codes from the table
        let maxNum = 0;
        document.querySelectorAll('#menuTableBody tr').forEach(row => {
          const codeCell = row.querySelector('td:nth-child(2) .text-xs');
          if (codeCell) {
            const code = codeCell.textContent.replace('#', '');
            if (code.startsWith(prefix)) {
              const num = parseInt(code.substring(prefix.length)) || 0;
              maxNum = Math.max(maxNum, num);
            }
          }
        });

        const newNum = maxNum + 1;
        const newCode = prefix + newNum.toString().padStart(3, '0');
        document.getElementById('newItemCode').value = newCode;
      }

      // ========== MODAL FUNCTIONS ==========
      function openModal(modalId) {
        document.getElementById(modalId).classList.add('show');
        if (modalId === 'addItemModal') {
          generateItemCode();
        }
      }

      function closeModal(modalId) {
        document.getElementById(modalId).classList.remove('show');
      }

      // ========== ADD NEW ITEM ==========
      function saveNewItem(event) {
        event.preventDefault();

        const formData = new FormData(document.getElementById('addItemForm'));

        Swal.fire({
          title: 'Adding Item...',
          text: 'Please wait',
          allowOutsideClick: false,
          didOpen: () => {
            Swal.showLoading();
          }
        });

        fetch('../../../controller/admin/post/restaurant/menu_actions.php', {
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
            Swal.close();
            showToast('An error occurred', 'error');
          });
      }

      // ========== EDIT ITEM ==========
      function editItem(itemId) {
        Swal.fire({
          title: 'Loading...',
          text: 'Please wait',
          allowOutsideClick: false,
          didOpen: () => {
            Swal.showLoading();
          }
        });

        const formData = new FormData();
        formData.append('action', 'get_item_details');
        formData.append('item_id', itemId);

        fetch('../../../controller/admin/post/restaurant/menu_actions.php', {
          method: 'POST',
          body: formData
        })
          .then(response => response.json())
          .then(data => {
            Swal.close();
            if (data.success) {
              const item = data.item;

              document.getElementById('editItemId').value = item.id;
              document.getElementById('editItemName').value = item.name;
              document.getElementById('editItemCategory').value = item.category;
              document.getElementById('editItemPrice').value = item.price;
              document.getElementById('editItemCost').value = item.cost;
              document.getElementById('editItemStock').value = item.stock;
              document.getElementById('editItemPrepTime').value = item.preparation_time || 15;
              document.getElementById('editItemStatus').value = item.status;
              document.getElementById('editItemDescription').value = item.description || '';

              openModal('editItemModal');
            } else {
              showToast(data.message, 'error');
            }
          });
      }

      function updateItem(event) {
        event.preventDefault();

        const formData = new FormData(document.getElementById('editItemForm'));

        Swal.fire({
          title: 'Updating Item...',
          text: 'Please wait',
          allowOutsideClick: false,
          didOpen: () => {
            Swal.showLoading();
          }
        });

        fetch('../../../controller/admin/post/restaurant/menu_actions.php', {
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

      // ========== TOGGLE ITEM STATUS ==========
      function toggleItemStatus(itemId, button) {
        Swal.fire({
          title: 'Toggle Status',
          text: 'Change item availability?',
          icon: 'question',
          showCancelButton: true,
          confirmButtonColor: '#d97706',
          cancelButtonColor: '#6b7280',
          confirmButtonText: 'Yes, toggle'
        }).then((result) => {
          if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('action', 'toggle_status');
            formData.append('item_id', itemId);

            fetch('../../../controller/admin/post/restaurant/menu_actions.php', {
              method: 'POST',
              body: formData
            })
              .then(response => response.json())
              .then(data => {
                if (data.success) {
                  showToast(data.message, 'success');
                  setTimeout(() => location.reload(), 1000);
                } else {
                  showToast(data.message, 'error');
                }
              });
          }
        });
      }

      // ========== RESTOCK ITEM ==========
      function openRestockModal(itemId) {
        document.getElementById('restockItemId').value = itemId;
        openModal('restockModal');
      }

      function processRestock() {
        const itemId = document.getElementById('restockItemId').value;
        const quantity = document.getElementById('restockQuantity').value;

        const formData = new FormData();
        formData.append('action', 'restock_item');
        formData.append('item_id', itemId);
        formData.append('quantity', quantity);

        Swal.fire({
          title: 'Restocking...',
          text: 'Please wait',
          allowOutsideClick: false,
          didOpen: () => {
            Swal.showLoading();
          }
        });

        fetch('../../../controller/admin/post/restaurant/menu_actions.php', {
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
              }).then(() => {
                closeModal('restockModal');
                location.reload();
              });
            } else {
              showToast(data.message, 'error');
            }
          });
      }

      // ========== ADD CATEGORY ==========
      function saveNewCategory(event) {
        event.preventDefault();

        const formData = new FormData(document.getElementById('addCategoryForm'));

        Swal.fire({
          title: 'Adding Category...',
          text: 'Please wait',
          allowOutsideClick: false,
          didOpen: () => {
            Swal.showLoading();
          }
        });

        fetch('../../../controller/admin/post/restaurant/menu_actions.php', {
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
              }).then(() => {
                closeModal('addCategoryModal');
                // Add new category to dropdowns
                const newCategory = data.category;
                const bulkCategory = document.getElementById('bulkCategory');
                if (bulkCategory) {
                  const option = document.createElement('option');
                  option.value = newCategory;
                  option.textContent = newCategory;
                  bulkCategory.appendChild(option);
                }
                showToast(data.message, 'success');
              });
            } else {
              showToast(data.message, 'error');
            }
          });
      }

      // ========== BULK EDIT FUNCTIONS ==========
      function toggleBulkEdit() {
        const panel = document.getElementById('bulkEditPanel');
        const checkboxes = document.querySelectorAll('.item-checkbox');
        const selectAll = document.getElementById('selectAll');

        if (!bulkEditActive) {
          panel.style.transform = 'translateY(0)';
          checkboxes.forEach(cb => cb.style.display = 'inline-block');
          selectAll.style.display = 'inline-block';
          bulkEditActive = true;
        } else {
          cancelBulkEdit();
        }
      }

      function toggleSelectAll() {
        const selectAll = document.getElementById('selectAll');
        const checkboxes = document.querySelectorAll('.item-checkbox');

        checkboxes.forEach(cb => {
          cb.checked = selectAll.checked;
          if (selectAll.checked) {
            cb.closest('tr').classList.add('bulk-selected');
          } else {
            cb.closest('tr').classList.remove('bulk-selected');
          }
        });

        updateSelectedCount();
      }

      function updateSelectedCount() {
        const checkboxes = document.querySelectorAll('.item-checkbox');
        const selected = Array.from(checkboxes).filter(cb => cb.checked);

        document.getElementById('selectedCount').textContent = selected.length;

        checkboxes.forEach(cb => {
          if (cb.checked) {
            cb.closest('tr').classList.add('bulk-selected');
          } else {
            cb.closest('tr').classList.remove('bulk-selected');
          }
        });

        const selectAll = document.getElementById('selectAll');
        if (selected.length === checkboxes.length && checkboxes.length > 0) {
          selectAll.checked = true;
          selectAll.indeterminate = false;
        } else if (selected.length === 0) {
          selectAll.checked = false;
          selectAll.indeterminate = false;
        } else {
          selectAll.indeterminate = true;
        }
      }

      function applyBulkEdit() {
        const selected = Array.from(document.querySelectorAll('.item-checkbox:checked'))
          .map(cb => cb.closest('tr').dataset.id);

        if (selected.length === 0) {
          showToast('No items selected', 'error');
          return;
        }

        const status = document.getElementById('bulkStatus').value;
        const category = document.getElementById('bulkCategory').value;
        const price = document.getElementById('bulkPrice').value;

        if (!status && !category && !price) {
          showToast('No changes selected', 'error');
          return;
        }

        Swal.fire({
          title: 'Applying Bulk Edit...',
          text: 'Please wait',
          allowOutsideClick: false,
          didOpen: () => {
            Swal.showLoading();
          }
        });

        const formData = new FormData();
        formData.append('action', 'bulk_update');
        formData.append('item_ids', JSON.stringify(selected));
        if (status) formData.append('status', status);
        if (category) formData.append('category', category);
        if (price) formData.append('price', price);

        fetch('../../../controller/admin/post/restaurant/menu_actions.php', {
          method: 'POST',
          body: formData
        })
          .then(response => response.json())
          .then(data => {
            Swal.close();
            if (data.success) {
              showToast(data.message, 'success');
              setTimeout(() => location.reload(), 1000);
            } else {
              showToast(data.message, 'error');
            }
          });
      }

      function cancelBulkEdit() {
        const panel = document.getElementById('bulkEditPanel');
        const checkboxes = document.querySelectorAll('.item-checkbox');
        const selectAll = document.getElementById('selectAll');

        panel.style.transform = 'translateY(100%)';
        checkboxes.forEach(cb => {
          cb.checked = false;
          cb.style.display = 'none';
          cb.closest('tr').classList.remove('bulk-selected');
        });
        selectAll.checked = false;
        selectAll.style.display = 'none';
        selectAll.indeterminate = false;

        document.getElementById('bulkStatus').value = '';
        document.getElementById('bulkCategory').value = '';
        document.getElementById('bulkPrice').value = '';

        bulkEditActive = false;
        document.getElementById('selectedCount').textContent = '0';
      }

      // ========== FILTER FUNCTIONS ==========
      function filterByCategory(category, event) {
        const url = new URL(window.location);
        if (category !== 'all') {
          url.searchParams.set('category', category);
        } else {
          url.searchParams.delete('category');
        }
        url.searchParams.set('page', '1');
        window.location.href = url.toString();
      }

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

      // ========== SEARCH FUNCTION ==========
      function searchMenu() {
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

      // ========== PAGINATION ==========
      function changePage(direction) {
        let newPage = currentPage;

        if (direction === 'prev') {
          newPage = currentPage - 1;
        } else if (direction === 'next') {
          newPage = currentPage + 1;
        } else {
          newPage = direction;
        }

        if (newPage < 1 || newPage > totalPages) return;

        const url = new URL(window.location);
        url.searchParams.set('page', newPage);
        window.location.href = url.toString();
      }

      // ========== LOW STOCK ALERT ==========
      function showLowStockItems() {
        filterByStatus('out_of_stock');
      }

      // ========== NOTIFICATION BELL ==========
      document.getElementById('notificationBell')?.addEventListener('click', function () {
        window.location.href = '../notifications.php';
      });

      // ========== INITIALIZE ==========
      document.addEventListener('DOMContentLoaded', function () {
        // Hide checkboxes initially
        document.querySelectorAll('.item-checkbox').forEach(cb => cb.style.display = 'none');
        document.getElementById('selectAll').style.display = 'none';
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
          cancelBulkEdit();
        }
      });
    </script>
  </body>

</html>