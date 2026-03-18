<!DOCTYPE html>
<html lang="en">

  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin · Menu Management</title>
    <!-- Tailwind via CDN + Font Awesome 6 -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
      /* exact same dropdown styles from index2.php */
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
        max-width: 600px;
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

      /* Bulk edit checkbox highlight */
      .bulk-selected {
        background-color: #fef3c7 !important;
      }

      .hidden-row {
        display: none;
      }

      /* Disabled item style */
      .status-badge.disabled {
        opacity: 0.7;
      }
    </style>
  </head>

  <body class="bg-white font-sans antialiased">

    <!-- Toast notification container -->
    <div id="toast" class="toast"></div>

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
          <div class="grid grid-cols-2 gap-4">
            <div class="mb-4 col-span-2">
              <label class="block text-sm font-medium text-slate-700 mb-1">Item Name</label>
              <input type="text" id="newItemName" required class="w-full border border-slate-200 rounded-lg px-3 py-2">
            </div>

            <div class="mb-4">
              <label class="block text-sm font-medium text-slate-700 mb-1">Category</label>
              <select id="newItemCategory" onchange="generateItemCode()"
                class="w-full border border-slate-200 rounded-lg px-3 py-2">
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
              <input type="text" id="newItemCode" readonly
                class="w-full border border-slate-200 rounded-lg px-3 py-2 bg-slate-50">
            </div>

            <div class="mb-4">
              <label class="block text-sm font-medium text-slate-700 mb-1">Price (₱)</label>
              <input type="number" id="newItemPrice" required min="0"
                class="w-full border border-slate-200 rounded-lg px-3 py-2">
            </div>

            <div class="mb-4">
              <label class="block text-sm font-medium text-slate-700 mb-1">Cost (₱)</label>
              <input type="number" id="newItemCost" required min="0"
                class="w-full border border-slate-200 rounded-lg px-3 py-2">
            </div>

            <div class="mb-4">
              <label class="block text-sm font-medium text-slate-700 mb-1">Stock</label>
              <input type="number" id="newItemStock" required min="0"
                class="w-full border border-slate-200 rounded-lg px-3 py-2">
            </div>

            <div class="mb-4">
              <label class="block text-sm font-medium text-slate-700 mb-1">Status</label>
              <select id="newItemStatus" class="w-full border border-slate-200 rounded-lg px-3 py-2">
                <option value="available">Available</option>
                <option value="out of stock">Out of Stock</option>
                <option value="special">Special</option>
              </select>
            </div>

            <div class="mb-4 col-span-2">
              <label class="block text-sm font-medium text-slate-700 mb-1">Description (optional)</label>
              <textarea id="newItemDescription" rows="2"
                class="w-full border border-slate-200 rounded-lg px-3 py-2"></textarea>
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
          <div class="mb-4">
            <label class="block text-sm font-medium text-slate-700 mb-1">Category Name</label>
            <input type="text" id="newCategoryName" required
              class="w-full border border-slate-200 rounded-lg px-3 py-2">
          </div>

          <div class="mb-4">
            <label class="block text-sm font-medium text-slate-700 mb-1">Description (optional)</label>
            <textarea id="newCategoryDescription" rows="2"
              class="w-full border border-slate-200 rounded-lg px-3 py-2"></textarea>
          </div>

          <div class="mb-4">
            <label class="block text-sm font-medium text-slate-700 mb-1">Display Order</label>
            <input type="number" id="newCategoryOrder" value="1" min="1"
              class="w-full border border-slate-200 rounded-lg px-3 py-2">
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

    <!-- Bulk Edit Panel -->
    <div id="bulkEditPanel"
      class="fixed bottom-0 left-0 right-0 bg-white border-t border-amber-200 shadow-lg p-4 transform translate-y-full transition-transform duration-300 z-50"
      style="margin-left: 320px;">
      <div class="container mx-auto flex flex-wrap items-center justify-between">
        <div>
          <span class="font-semibold" id="selectedCount">0</span> items selected
        </div>
        <div class="flex gap-3">
          <select id="bulkStatus" class="border border-slate-200 rounded-lg px-3 py-2 text-sm">
            <option value="">Change Status...</option>
            <option value="available">Available</option>
            <option value="out of stock">Out of Stock</option>
            <option value="special">Special</option>
          </select>
          <select id="bulkCategory" class="border border-slate-200 rounded-lg px-3 py-2 text-sm">
            <option value="">Change Category...</option>
            <option value="Mains">Mains</option>
            <option value="Appetizers">Appetizers</option>
            <option value="Desserts">Desserts</option>
            <option value="Beverages">Beverages</option>
            <option value="Specials">Specials</option>
            <option value="Sides">Sides</option>
          </select>
          <input type="number" id="bulkPrice" placeholder="Set Price"
            class="border border-slate-200 rounded-lg px-3 py-2 text-sm w-32">
          <button onclick="applyBulkEdit()" class="bg-amber-600 text-white px-4 py-2 rounded-lg text-sm">Apply</button>
          <button onclick="cancelBulkEdit()"
            class="border border-slate-200 px-4 py-2 rounded-lg text-sm">Cancel</button>
        </div>
      </div>
    </div>

    <!-- APP CONTAINER: flex row (sidebar + main) -->
    <div class="min-h-screen flex flex-col lg:flex-row">

      <!-- ========== SIDEBAR (exact copy from index2.php) ========== -->
      <?php require '../components/admin_nav.php' ?>


      <!-- ========== MAIN CONTENT (MENU MANAGEMENT) ========== -->
      <main class="flex-1 p-5 lg:p-8 overflow-y-auto bg-white">

        <!-- header with title and date -->
        <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
          <div>
            <h1 class="text-2xl lg:text-3xl font-light text-slate-800">Menu Management</h1>
            <p class="text-sm text-slate-500 mt-0.5">manage food items, categories, pricing, and availability</p>
          </div>
          <div class="flex gap-3 text-sm">
            <span class="bg-white border rounded-full px-4 py-2 flex items-center gap-2 shadow-sm"><i
                class="fa-regular fa-calendar text-slate-400"></i> May 21, 2025</span>
            <span class="bg-white border rounded-full px-4 py-2 shadow-sm"><i class="fa-regular fa-bell"></i></span>
          </div>
        </div>

        <!-- ===== STATS CARDS ===== -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
          <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs text-slate-500">Total items</p>
            <p class="text-2xl font-semibold" id="totalItems">48</p>
          </div>
          <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs text-slate-500">Categories</p>
            <p class="text-2xl font-semibold" id="totalCategories">6</p>
          </div>
          <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs text-slate-500">Available</p>
            <p class="text-2xl font-semibold text-green-600" id="availableCount">42</p>
          </div>
          <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs text-slate-500">Out of stock</p>
            <p class="text-2xl font-semibold text-rose-600" id="outOfStockCount">6</p>
          </div>
          <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs text-slate-500">Specials</p>
            <p class="text-2xl font-semibold text-amber-600" id="specialsCount">8</p>
          </div>
        </div>

        <!-- ===== ACTION BAR ===== -->
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
              class="border border-slate-200 rounded-xl pl-9 pr-4 py-2 text-sm w-64 focus:ring-1 focus:ring-amber-500 outline-none">
          </div>
        </div>

        <!-- ===== CATEGORY TABS ===== -->
        <div class="flex flex-wrap gap-2 border-b border-slate-200 pb-2 mb-6">
          <button id="catAll" onclick="filterByCategory('all', event)"
            class="category-filter px-4 py-2 bg-amber-600 text-white rounded-full text-sm">all</button>
          <button id="catAppetizers" onclick="filterByCategory('appetizers', event)"
            class="category-filter px-4 py-2 bg-white border border-slate-200 rounded-full text-sm hover:bg-slate-50">appetizers</button>
          <button id="catMains" onclick="filterByCategory('mains', event)"
            class="category-filter px-4 py-2 bg-white border border-slate-200 rounded-full text-sm hover:bg-slate-50">mains</button>
          <button id="catDesserts" onclick="filterByCategory('desserts', event)"
            class="category-filter px-4 py-2 bg-white border border-slate-200 rounded-full text-sm hover:bg-slate-50">desserts</button>
          <button id="catBeverages" onclick="filterByCategory('beverages', event)"
            class="category-filter px-4 py-2 bg-white border border-slate-200 rounded-full text-sm hover:bg-slate-50">beverages</button>
          <button id="catSpecials" onclick="filterByCategory('specials', event)"
            class="category-filter px-4 py-2 bg-white border border-slate-200 rounded-full text-sm hover:bg-slate-50">specials</button>
          <button id="catSides" onclick="filterByCategory('sides', event)"
            class="category-filter px-4 py-2 bg-white border border-slate-200 rounded-full text-sm hover:bg-slate-50">sides</button>
        </div>

        <!-- ===== MENU ITEMS TABLE ===== -->
        <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden mb-8">
          <div class="overflow-x-auto">
            <table class="w-full text-sm" id="menuTable">
              <thead class="bg-slate-50 text-slate-500 text-xs border-b">
                <tr>
                  <td class="p-4 w-10">
                    <input type="checkbox" id="selectAll" onclick="toggleSelectAll()" class="rounded border-slate-300">
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
                <tr data-category="mains" data-status="available">
                  <td class="p-4"><input type="checkbox" class="item-checkbox rounded border-slate-300"
                      onclick="updateSelectedCount()"></td>
                  <td class="p-4">
                    <div class="flex items-center gap-3">
                      <div class="h-10 w-10 bg-slate-200 rounded-lg flex items-center justify-center text-slate-500"><i
                          class="fa-regular fa-image text-xs"></i></div>
                      <div><span class="font-medium">Sinigang na Baboy</span>
                        <p class="text-xs text-slate-400">#M001</p>
                      </div>
                    </div>
                  </td>
                  <td class="p-4">Mains</td>
                  <td class="p-4 font-medium">₱320</td>
                  <td class="p-4">₱180</td>
                  <td class="p-4">45</td>
                  <td class="p-4"><span
                      class="status-badge bg-green-100 text-green-700 px-2 py-0.5 rounded-full text-xs">available</span>
                  </td>
                  <td class="p-4">
                    <button class="text-amber-700 text-xs hover:underline mr-2" onclick="editItem(this)">edit</button>
                    <button class="text-rose-600 text-xs hover:underline"
                      onclick="toggleItemStatus(this)">disable</button>
                  </td>
                </tr>
                <tr data-category="mains" data-status="available">
                  <td class="p-4"><input type="checkbox" class="item-checkbox rounded border-slate-300"
                      onclick="updateSelectedCount()"></td>
                  <td class="p-4">
                    <div class="flex items-center gap-3">
                      <div class="h-10 w-10 bg-slate-200 rounded-lg flex items-center justify-center text-slate-500"><i
                          class="fa-regular fa-image text-xs"></i></div>
                      <div><span class="font-medium">Sizzling Sisig</span>
                        <p class="text-xs text-slate-400">#M002</p>
                      </div>
                    </div>
                  </td>
                  <td class="p-4">Mains</td>
                  <td class="p-4 font-medium">₱290</td>
                  <td class="p-4">₱150</td>
                  <td class="p-4">32</td>
                  <td class="p-4"><span
                      class="status-badge bg-green-100 text-green-700 px-2 py-0.5 rounded-full text-xs">available</span>
                  </td>
                  <td class="p-4">
                    <button class="text-amber-700 text-xs hover:underline mr-2" onclick="editItem(this)">edit</button>
                    <button class="text-rose-600 text-xs hover:underline"
                      onclick="toggleItemStatus(this)">disable</button>
                  </td>
                </tr>
                <tr data-category="mains" data-status="available">
                  <td class="p-4"><input type="checkbox" class="item-checkbox rounded border-slate-300"
                      onclick="updateSelectedCount()"></td>
                  <td class="p-4">
                    <div class="flex items-center gap-3">
                      <div class="h-10 w-10 bg-slate-200 rounded-lg flex items-center justify-center text-slate-500"><i
                          class="fa-regular fa-image text-xs"></i></div>
                      <div><span class="font-medium">Crispy Pata</span>
                        <p class="text-xs text-slate-400">#M003</p>
                      </div>
                    </div>
                  </td>
                  <td class="p-4">Mains</td>
                  <td class="p-4 font-medium">₱550</td>
                  <td class="p-4">₱300</td>
                  <td class="p-4">18</td>
                  <td class="p-4"><span
                      class="status-badge bg-green-100 text-green-700 px-2 py-0.5 rounded-full text-xs">available</span>
                  </td>
                  <td class="p-4">
                    <button class="text-amber-700 text-xs hover:underline mr-2" onclick="editItem(this)">edit</button>
                    <button class="text-rose-600 text-xs hover:underline"
                      onclick="toggleItemStatus(this)">disable</button>
                  </td>
                </tr>
                <tr data-category="desserts" data-status="available">
                  <td class="p-4"><input type="checkbox" class="item-checkbox rounded border-slate-300"
                      onclick="updateSelectedCount()"></td>
                  <td class="p-4">
                    <div class="flex items-center gap-3">
                      <div class="h-10 w-10 bg-slate-200 rounded-lg flex items-center justify-center text-slate-500"><i
                          class="fa-regular fa-image text-xs"></i></div>
                      <div><span class="font-medium">Halo-Halo</span>
                        <p class="text-xs text-slate-400">#D001</p>
                      </div>
                    </div>
                  </td>
                  <td class="p-4">Desserts</td>
                  <td class="p-4 font-medium">₱150</td>
                  <td class="p-4">₱70</td>
                  <td class="p-4">23</td>
                  <td class="p-4"><span
                      class="status-badge bg-green-100 text-green-700 px-2 py-0.5 rounded-full text-xs">available</span>
                  </td>
                  <td class="p-4">
                    <button class="text-amber-700 text-xs hover:underline mr-2" onclick="editItem(this)">edit</button>
                    <button class="text-rose-600 text-xs hover:underline"
                      onclick="toggleItemStatus(this)">disable</button>
                  </td>
                </tr>
                <tr data-category="beverages" data-status="out of stock">
                  <td class="p-4"><input type="checkbox" class="item-checkbox rounded border-slate-300"
                      onclick="updateSelectedCount()"></td>
                  <td class="p-4">
                    <div class="flex items-center gap-3">
                      <div class="h-10 w-10 bg-slate-200 rounded-lg flex items-center justify-center text-slate-500"><i
                          class="fa-regular fa-image text-xs"></i></div>
                      <div><span class="font-medium">Fresh Buko Juice</span>
                        <p class="text-xs text-slate-400">#B001</p>
                      </div>
                    </div>
                  </td>
                  <td class="p-4">Beverages</td>
                  <td class="p-4 font-medium">₱90</td>
                  <td class="p-4">₱30</td>
                  <td class="p-4">0</td>
                  <td class="p-4"><span
                      class="status-badge bg-rose-100 text-rose-700 px-2 py-0.5 rounded-full text-xs">out of
                      stock</span></td>
                  <td class="p-4">
                    <button class="text-amber-700 text-xs hover:underline mr-2" onclick="editItem(this)">edit</button>
                    <button class="text-amber-600 text-xs hover:underline" onclick="restockItem(this)">restock</button>
                  </td>
                </tr>
                <tr data-category="sides" data-status="available">
                  <td class="p-4"><input type="checkbox" class="item-checkbox rounded border-slate-300"
                      onclick="updateSelectedCount()"></td>
                  <td class="p-4">
                    <div class="flex items-center gap-3">
                      <div class="h-10 w-10 bg-slate-200 rounded-lg flex items-center justify-center text-slate-500"><i
                          class="fa-regular fa-image text-xs"></i></div>
                      <div><span class="font-medium">Garlic Rice</span>
                        <p class="text-xs text-slate-400">#S001</p>
                      </div>
                    </div>
                  </td>
                  <td class="p-4">Sides</td>
                  <td class="p-4 font-medium">₱50</td>
                  <td class="p-4">₱20</td>
                  <td class="p-4">120</td>
                  <td class="p-4"><span
                      class="status-badge bg-green-100 text-green-700 px-2 py-0.5 rounded-full text-xs">available</span>
                  </td>
                  <td class="p-4">
                    <button class="text-amber-700 text-xs hover:underline mr-2" onclick="editItem(this)">edit</button>
                    <button class="text-rose-600 text-xs hover:underline"
                      onclick="toggleItemStatus(this)">disable</button>
                  </td>
                </tr>
                <tr data-category="specials" data-status="special">
                  <td class="p-4"><input type="checkbox" class="item-checkbox rounded border-slate-300"
                      onclick="updateSelectedCount()"></td>
                  <td class="p-4">
                    <div class="flex items-center gap-3">
                      <div class="h-10 w-10 bg-slate-200 rounded-lg flex items-center justify-center text-slate-500"><i
                          class="fa-regular fa-image text-xs"></i></div>
                      <div><span class="font-medium">Seafood Kare-Kare</span>
                        <p class="text-xs text-slate-400">#M004</p>
                      </div>
                    </div>
                  </td>
                  <td class="p-4">Specials</td>
                  <td class="p-4 font-medium">₱480</td>
                  <td class="p-4">₱260</td>
                  <td class="p-4">12</td>
                  <td class="p-4"><span
                      class="status-badge bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full text-xs">special</span>
                  </td>
                  <td class="p-4">
                    <button class="text-amber-700 text-xs hover:underline mr-2" onclick="editItem(this)">edit</button>
                    <button class="text-rose-600 text-xs hover:underline"
                      onclick="toggleItemStatus(this)">disable</button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="p-4 border-t border-slate-200 flex items-center justify-between">
            <span class="text-xs text-slate-500" id="paginationInfo">Showing 1-5 of 7 items</span>
            <div class="flex gap-2" id="paginationButtons">
              <button onclick="changePage('prev')"
                class="border border-slate-200 px-3 py-1 rounded-lg text-sm hover:bg-slate-50">Previous</button>
              <button onclick="changePage(1)"
                class="border px-3 py-1 rounded-lg text-sm page-btn bg-amber-600 text-white">1</button>
              <button onclick="changePage(2)"
                class="border border-slate-200 px-3 py-1 rounded-lg text-sm hover:bg-slate-50 page-btn">2</button>
              <button onclick="changePage('next')"
                class="border border-slate-200 px-3 py-1 rounded-lg text-sm hover:bg-slate-50">Next</button>
            </div>
          </div>
        </div>
      </main>
    </div>

    <script>
      // ========== ITEM CODE GENERATION ==========
      function generateItemCode() {
        const category = document.getElementById('newItemCategory').value;
        const rows = document.querySelectorAll('#menuTableBody tr');

        // Get category prefix
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

        // Find the highest number for this category
        let maxNum = 0;
        rows.forEach(row => {
          const codeCell = row.cells[1].querySelector('.text-xs');
          if (codeCell) {
            const code = codeCell.textContent;
            if (code.startsWith('#' + prefix)) {
              const num = parseInt(code.substring(prefix.length + 1)) || 0;
              maxNum = Math.max(maxNum, num);
            }
          }
        });

        // Generate new code
        const newNum = maxNum + 1;
        const newCode = `#${prefix}${newNum.toString().padStart(3, '0')}`;
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

        const name = document.getElementById('newItemName').value;
        const category = document.getElementById('newItemCategory').value;
        const code = document.getElementById('newItemCode').value;
        const price = document.getElementById('newItemPrice').value;
        const cost = document.getElementById('newItemCost').value;
        const stock = document.getElementById('newItemStock').value;
        const status = document.getElementById('newItemStatus').value;

        // Get table body
        const tbody = document.getElementById('menuTableBody');

        // Create new row
        const newRow = document.createElement('tr');
        newRow.setAttribute('data-category', category.toLowerCase());
        newRow.setAttribute('data-status', status);

        // Determine status colors
        let statusClass = '';
        if (status === 'available') statusClass = 'bg-green-100 text-green-700';
        else if (status === 'out of stock') statusClass = 'bg-rose-100 text-rose-700';
        else if (status === 'special') statusClass = 'bg-amber-100 text-amber-700';

        newRow.innerHTML = `
        <td class="p-4"><input type="checkbox" class="item-checkbox rounded border-slate-300" onclick="updateSelectedCount()" style="display: none;"></td>
        <td class="p-4">
          <div class="flex items-center gap-3">
            <div class="h-10 w-10 bg-slate-200 rounded-lg flex items-center justify-center text-slate-500"><i class="fa-regular fa-image text-xs"></i></div>
            <div><span class="font-medium">${name}</span><p class="text-xs text-slate-400">${code}</p></div>
          </div>
        </td>
        <td class="p-4">${category}</td>
        <td class="p-4 font-medium">₱${price}</td>
        <td class="p-4">₱${cost}</td>
        <td class="p-4">${stock}</td>
        <td class="p-4"><span class="status-badge ${statusClass} px-2 py-0.5 rounded-full text-xs">${status}</span></td>
        <td class="p-4">
          <button class="text-amber-700 text-xs hover:underline mr-2" onclick="editItem(this)">edit</button>
          <button class="text-rose-600 text-xs hover:underline" onclick="toggleItemStatus(this)">${status === 'out of stock' ? 'restock' : 'disable'}</button>
        </td>
      `;

        tbody.appendChild(newRow);

        // Update stats
        updateStats();

        // Reset pagination and show all items
        document.getElementById('searchInput').value = '';
        filterByCategory('all', { target: document.getElementById('catAll') });

        closeModal('addItemModal');
        document.getElementById('addItemForm').reset();
        showToast('New item added successfully!', 'success');
      }

      // ========== ADD NEW CATEGORY ==========
      function saveNewCategory(event) {
        event.preventDefault();

        const name = document.getElementById('newCategoryName').value;
        const displayOrder = document.getElementById('newCategoryOrder').value;

        // Add new category tab
        const tabsContainer = document.querySelector('.category-filter').parentNode;
        const newTab = document.createElement('button');
        newTab.id = `cat${name.replace(/\s+/g, '')}`;
        newTab.className = 'category-filter px-4 py-2 bg-white border border-slate-200 rounded-full text-sm hover:bg-slate-50';
        newTab.setAttribute('onclick', `filterByCategory('${name.toLowerCase()}', event)`);
        newTab.textContent = name.toLowerCase();

        // Insert at the end before the closing of the container
        tabsContainer.appendChild(newTab);

        // Update categories count
        const catCount = document.getElementById('totalCategories');
        catCount.textContent = parseInt(catCount.textContent) + 1;

        closeModal('addCategoryModal');
        document.getElementById('addCategoryForm').reset();
        showToast('New category added successfully!', 'success');
      }

      // ========== BULK EDIT FUNCTIONS ==========
      let bulkEditActive = false;

      function toggleBulkEdit() {
        const panel = document.getElementById('bulkEditPanel');
        const checkboxes = document.querySelectorAll('.item-checkbox');

        if (!bulkEditActive) {
          panel.style.transform = 'translateY(0)';
          // Show checkboxes
          checkboxes.forEach(cb => cb.style.display = 'inline-block');
          document.getElementById('selectAll').style.display = 'inline-block';
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

        // Highlight selected rows
        checkboxes.forEach(cb => {
          if (cb.checked) {
            cb.closest('tr').classList.add('bulk-selected');
          } else {
            cb.closest('tr').classList.remove('bulk-selected');
          }
        });

        // Update select all checkbox
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
        const status = document.getElementById('bulkStatus').value;
        const category = document.getElementById('bulkCategory').value;
        const price = document.getElementById('bulkPrice').value;

        const selectedRows = Array.from(document.querySelectorAll('.item-checkbox:checked'))
          .map(cb => cb.closest('tr'));

        if (selectedRows.length === 0) {
          showToast('No items selected', 'error');
          return;
        }

        selectedRows.forEach(row => {
          if (status) {
            const statusBadge = row.querySelector('.status-badge');
            let statusClass = '';
            if (status === 'available') statusClass = 'bg-green-100 text-green-700';
            else if (status === 'out of stock') statusClass = 'bg-rose-100 text-rose-700';
            else if (status === 'special') statusClass = 'bg-amber-100 text-amber-700';

            statusBadge.className = `status-badge ${statusClass} px-2 py-0.5 rounded-full text-xs`;
            statusBadge.textContent = status;
            row.setAttribute('data-status', status);
          }

          if (category) {
            row.cells[2].textContent = category;
            row.setAttribute('data-category', category.toLowerCase());
          }

          if (price) {
            const priceCell = row.cells[3];
            priceCell.textContent = `₱${price}`;
          }
        });

        updateStats();
        cancelBulkEdit();
        showToast(`Updated ${selectedRows.length} items`, 'success');
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

      // ========== SEARCH FUNCTION ==========
      function searchMenu() {
        const searchTerm = document.getElementById('searchInput').value.toLowerCase();
        const rows = document.querySelectorAll('#menuTableBody tr');

        rows.forEach(row => {
          const itemName = row.cells[1].querySelector('.font-medium').textContent.toLowerCase();
          const itemCode = row.cells[1].querySelector('.text-xs').textContent.toLowerCase();
          const category = row.cells[2].textContent.toLowerCase();

          if (itemName.includes(searchTerm) || itemCode.includes(searchTerm) || category.includes(searchTerm)) {
            row.classList.remove('hidden-row');
          } else {
            row.classList.add('hidden-row');
          }
        });

        // Reset to first page and update pagination
        currentPage = 1;
        updatePagination();
      }

      // ========== FILTER BY CATEGORY ==========
      function filterByCategory(category, event) {
        // Update active tab styling
        document.querySelectorAll('.category-filter').forEach(btn => {
          btn.classList.remove('bg-amber-600', 'text-white');
          btn.classList.add('bg-white', 'border', 'border-slate-200', 'text-slate-700');
        });

        event.target.classList.remove('bg-white', 'border', 'border-slate-200', 'text-slate-700');
        event.target.classList.add('bg-amber-600', 'text-white');

        const rows = document.querySelectorAll('#menuTableBody tr');

        rows.forEach(row => {
          if (category === 'all') {
            row.classList.remove('hidden-row');
          } else {
            const rowCategory = row.getAttribute('data-category');
            if (rowCategory === category) {
              row.classList.remove('hidden-row');
            } else {
              row.classList.add('hidden-row');
            }
          }
        });

        // Reset to first page and update pagination
        currentPage = 1;
        updatePagination();
      }

      // ========== ITEM ACTIONS ==========
      function editItem(button) {
        const row = button.closest('tr');
        const name = row.cells[1].querySelector('.font-medium').textContent;

        showToast(`Editing ${name} - Edit feature coming soon!`, 'info');
      }

      function toggleItemStatus(button) {
        const row = button.closest('tr');
        const statusBadge = row.querySelector('.status-badge');
        const currentStatus = row.getAttribute('data-status');

        if (currentStatus === 'available') {
          statusBadge.className = 'status-badge bg-slate-100 text-slate-700 px-2 py-0.5 rounded-full text-xs';
          statusBadge.textContent = 'disabled';
          row.setAttribute('data-status', 'disabled');
          button.textContent = 'enable';
        } else if (currentStatus === 'disabled') {
          statusBadge.className = 'status-badge bg-green-100 text-green-700 px-2 py-0.5 rounded-full text-xs';
          statusBadge.textContent = 'available';
          row.setAttribute('data-status', 'available');
          button.textContent = 'disable';
        } else if (currentStatus === 'out of stock') {
          statusBadge.className = 'status-badge bg-green-100 text-green-700 px-2 py-0.5 rounded-full text-xs';
          statusBadge.textContent = 'available';
          row.setAttribute('data-status', 'available');
          row.cells[5].textContent = '50'; // Set default stock
          button.textContent = 'disable';
        } else if (currentStatus === 'special') {
          statusBadge.className = 'status-badge bg-slate-100 text-slate-700 px-2 py-0.5 rounded-full text-xs';
          statusBadge.textContent = 'disabled';
          row.setAttribute('data-status', 'disabled');
          button.textContent = 'enable';
        }

        updateStats();
        showToast('Item status updated', 'success');
      }

      function restockItem(button) {
        const row = button.closest('tr');
        const stockCell = row.cells[5];
        stockCell.textContent = '50';

        // Change status to available
        const statusBadge = row.querySelector('.status-badge');
        statusBadge.className = 'status-badge bg-green-100 text-green-700 px-2 py-0.5 rounded-full text-xs';
        statusBadge.textContent = 'available';
        row.setAttribute('data-status', 'available');
        button.textContent = 'disable';

        updateStats();
        showToast('Item restocked successfully!', 'success');
      }

      // ========== UPDATE STATISTICS ==========
      function updateStats() {
        const rows = document.querySelectorAll('#menuTableBody tr');
        const totalItems = rows.length;

        let available = 0, outOfStock = 0, specials = 0, disabled = 0;

        rows.forEach(row => {
          const status = row.getAttribute('data-status');
          if (status === 'available') available++;
          else if (status === 'out of stock') outOfStock++;
          else if (status === 'special') specials++;
          else if (status === 'disabled') disabled++;
        });

        document.getElementById('totalItems').textContent = totalItems;
        document.getElementById('availableCount').textContent = available;
        document.getElementById('outOfStockCount').textContent = outOfStock;
        document.getElementById('specialsCount').textContent = specials;
      }

      // ========== PAGINATION ==========
      let currentPage = 1;
      const itemsPerPage = 5;

      function changePage(direction) {
        const rows = document.querySelectorAll('#menuTableBody tr:not(.hidden-row)');
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

        // Hide all rows first
        rows.forEach((row, index) => {
          if (index >= (currentPage - 1) * itemsPerPage && index < currentPage * itemsPerPage) {
            row.style.display = '';
          } else {
            row.style.display = 'none';
          }
        });

        // Update pagination info
        const start = rows.length > 0 ? (currentPage - 1) * itemsPerPage + 1 : 0;
        const end = rows.length > 0 ? Math.min(currentPage * itemsPerPage, rows.length) : 0;
        document.getElementById('paginationInfo').textContent =
          rows.length > 0 ? `Showing ${start}-${end} of ${rows.length} items` : 'Showing 0 items';

        // Update page buttons
        updatePaginationButtons(totalPages);
      }

      function updatePaginationButtons(totalPages) {
        const container = document.getElementById('paginationButtons');
        if (!container) return;

        let buttons = '';

        // Previous button
        buttons += `<button onclick="changePage('prev')" class="border border-slate-200 px-3 py-1 rounded-lg text-sm hover:bg-slate-50" ${currentPage === 1 ? 'disabled' : ''}>Previous</button>`;

        // Page buttons
        for (let i = 1; i <= totalPages; i++) {
          buttons += `<button onclick="changePage(${i})" class="border px-3 py-1 rounded-lg text-sm page-btn ${i === currentPage ? 'bg-amber-600 text-white' : 'border-slate-200 hover:bg-slate-50'}">${i}</button>`;
        }

        // Next button
        buttons += `<button onclick="changePage('next')" class="border border-slate-200 px-3 py-1 rounded-lg text-sm hover:bg-slate-50" ${currentPage === totalPages ? 'disabled' : ''}>Next</button>`;

        container.innerHTML = buttons;
      }

      function updatePagination() {
        const rows = document.querySelectorAll('#menuTableBody tr:not(.hidden-row)');
        const totalPages = Math.max(1, Math.ceil(rows.length / itemsPerPage));
        if (currentPage > totalPages) currentPage = totalPages;
        changePage(currentPage);
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

      // Initialize
      document.addEventListener('DOMContentLoaded', function () {
        // Hide checkboxes initially
        document.querySelectorAll('.item-checkbox').forEach(cb => cb.style.display = 'none');
        document.getElementById('selectAll').style.display = 'none';

        // Initialize pagination
        updateStats();
        updatePagination();
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