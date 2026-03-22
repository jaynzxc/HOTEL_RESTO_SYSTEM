<?php
/**
 * View - Admin Inventory & Stock Management
 */
require_once '../../../controller/admin/get/inventory_get.php';

// Set current page for navigation
$current_page = 'inventory_&_stocks';
?>
<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Admin · Inventory & Stock</title>
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
                        <h1 class="text-2xl lg:text-3xl font-light text-slate-800">Inventory & Stock</h1>
                        <p class="text-sm text-slate-500 mt-0.5">manage supplies, track stock levels, and handle
                            reorders</p>
                    </div>
                    <div class="flex gap-3 text-sm">
                        <span class="bg-white border rounded-full px-4 py-2 flex items-center gap-2 shadow-sm">
                            <i class="fas fa-calendar text-slate-400"></i> <?php echo $today; ?>
                        </span>
                        <span
                            class="bg-white border rounded-full px-4 py-2 shadow-sm cursor-pointer hover:bg-slate-50 relative"
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
                        <p class="text-xs text-slate-500">Total items</p>
                        <p class="text-2xl font-semibold"><?php echo $stats['total_items']; ?></p>
                    </div>
                    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
                        <p class="text-xs text-slate-500">In stock</p>
                        <p class="text-2xl font-semibold text-green-600"><?php echo $stats['in_stock']; ?></p>
                    </div>
                    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm cursor-pointer hover:shadow-md transition"
                        onclick="filterByStatus('low')">
                        <p class="text-xs text-slate-500">Low stock</p>
                        <p class="text-2xl font-semibold text-amber-600"><?php echo $stats['low_stock']; ?></p>
                    </div>
                    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm cursor-pointer hover:shadow-md transition"
                        onclick="filterByStatus('out')">
                        <p class="text-xs text-slate-500">Out of stock</p>
                        <p class="text-2xl font-semibold text-rose-600"><?php echo $stats['out_of_stock']; ?></p>
                    </div>
                    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
                        <p class="text-xs text-slate-500">To reorder</p>
                        <p class="text-2xl font-semibold"><?php echo $stats['to_reorder']; ?></p>
                    </div>
                </div>

                <!-- ACTION BAR -->
                <div
                    class="bg-white rounded-2xl border border-slate-200 p-4 mb-6 flex flex-wrap gap-3 items-center justify-between">
                    <div class="flex gap-2 flex-wrap">
                        <button class="bg-amber-600 text-white px-4 py-2 rounded-xl text-sm hover:bg-amber-700"
                            onclick="openAddItemModal()">
                            <i class="fa-solid fa-plus mr-1"></i> add item
                        </button>
                        <button class="border border-slate-200 px-4 py-2 rounded-xl text-sm hover:bg-slate-50"
                            onclick="openCreatePOModal()">
                            <i class="fa-solid fa-file-invoice mr-1"></i> create PO
                        </button>
                        <button class="border border-slate-200 px-4 py-2 rounded-xl text-sm hover:bg-slate-50"
                            onclick="openReceiveModal()">
                            <i class="fa-solid fa-truck mr-1"></i> receive stock
                        </button>
                        <button class="border border-slate-200 px-4 py-2 rounded-xl text-sm hover:bg-slate-50"
                            onclick="exportInventory()">
                            <i class="fa-solid fa-download mr-1"></i> export
                        </button>
                    </div>
                    <div class="relative">
                        <i
                            class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                        <input type="text" id="searchInput" placeholder="search inventory..."
                            value="<?php echo htmlspecialchars($searchFilter); ?>"
                            class="border border-slate-200 rounded-xl pl-9 pr-4 py-2 text-sm w-64 focus:ring-1 focus:ring-amber-500 outline-none">
                    </div>
                </div>

                <!-- CATEGORY TABS -->
                <div class="flex flex-wrap gap-2 border-b border-slate-200 pb-2 mb-6">
                    <button data-category="all"
                        class="category-tab <?php echo $categoryFilter == 'all' ? 'bg-amber-600 text-white' : 'bg-white border border-slate-200 hover:bg-slate-50'; ?> px-4 py-2 rounded-full text-sm">all</button>
                    <button data-category="Food"
                        class="category-tab <?php echo $categoryFilter == 'Food' ? 'bg-amber-600 text-white' : 'bg-white border border-slate-200 hover:bg-slate-50'; ?> px-4 py-2 rounded-full text-sm">food
                        & beverage</button>
                    <button data-category="Meat"
                        class="category-tab <?php echo $categoryFilter == 'Meat' ? 'bg-amber-600 text-white' : 'bg-white border border-slate-200 hover:bg-slate-50'; ?> px-4 py-2 rounded-full text-sm">meat</button>
                    <button data-category="Housekeeping"
                        class="category-tab <?php echo $categoryFilter == 'Housekeeping' ? 'bg-amber-600 text-white' : 'bg-white border border-slate-200 hover:bg-slate-50'; ?> px-4 py-2 rounded-full text-sm">housekeeping</button>
                    <button data-category="Linens"
                        class="category-tab <?php echo $categoryFilter == 'Linens' ? 'bg-amber-600 text-white' : 'bg-white border border-slate-200 hover:bg-slate-50'; ?> px-4 py-2 rounded-full text-sm">linens</button>
                    <button data-category="Amenities"
                        class="category-tab <?php echo $categoryFilter == 'Amenities' ? 'bg-amber-600 text-white' : 'bg-white border border-slate-200 hover:bg-slate-50'; ?> px-4 py-2 rounded-full text-sm">amenities</button>
                    <button data-category="Maintenance"
                        class="category-tab <?php echo $categoryFilter == 'Maintenance' ? 'bg-amber-600 text-white' : 'bg-white border border-slate-200 hover:bg-slate-50'; ?> px-4 py-2 rounded-full text-sm">maintenance</button>
                    <button data-category="Supply"
                        class="category-tab <?php echo $categoryFilter == 'Supply' ? 'bg-amber-600 text-white' : 'bg-white border border-slate-200 hover:bg-slate-50'; ?> px-4 py-2 rounded-full text-sm">supplies</button>
                </div>

                <!-- INVENTORY TABLE -->
                <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden mb-8">
                    <div class="p-4 border-b border-slate-200 bg-slate-50 flex items-center justify-between">
                        <h2 class="font-semibold flex items-center gap-2"><i
                                class="fa-solid fa-boxes text-amber-600"></i> current inventory</h2>
                        <button
                            class="text-sm text-amber-700 border border-amber-600 px-3 py-1 rounded-lg hover:bg-amber-50"
                            onclick="filterLowStock()">
                            low stock only
                        </button>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm" id="inventoryTable">
                            <thead class="text-slate-500 text-xs border-b">
                                <tr>
                                    <th class="p-3 text-left">Item</th>
                                    <th class="p-3 text-left">Category</th>
                                    <th class="p-3 text-left">Stock</th>
                                    <th class="p-3 text-left">Unit</th>
                                    <th class="p-3 text-left">Reorder level</th>
                                    <th class="p-3 text-left">Status</th>
                                    <th class="p-3 text-left">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y" id="inventoryTableBody">
                                <?php foreach ($inventory as $item): ?>
                                    <tr data-stock="<?php echo $item['stock']; ?>"
                                        data-reorder="<?php echo $item['reorder_level']; ?>">
                                        <td class="p-3 font-medium"><?php echo htmlspecialchars($item['item_name']); ?></td>
                                        <td class="p-3"><?php echo htmlspecialchars($item['category']); ?></td>
                                        <td
                                            class="p-3 font-medium <?php echo $item['stock'] <= $item['reorder_level'] && $item['stock'] > 0 ? 'text-amber-600' : ($item['stock'] <= 0 ? 'text-rose-600' : 'text-green-600'); ?>">
                                            <?php echo $item['stock']; ?>
                                        </td>
                                        <td class="p-3"><?php echo htmlspecialchars($item['unit']); ?></td>
                                        <td class="p-3"><?php echo $item['reorder_level']; ?></td>
                                        <td class="p-3">
                                            <?php if ($item['stock'] <= 0): ?>
                                                <span class="bg-rose-100 text-rose-700 px-2 py-0.5 rounded-full text-xs">out of
                                                    stock</span>
                                            <?php elseif ($item['stock'] <= $item['reorder_level']): ?>
                                                <span class="bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full text-xs">low
                                                    stock</span>
                                            <?php else: ?>
                                                <span class="bg-green-100 text-green-700 px-2 py-0.5 rounded-full text-xs">in
                                                    stock</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="p-3">
                                            <button onclick="editItem(<?php echo $item['id']; ?>)"
                                                class="text-amber-700 text-xs hover:underline mr-2">
                                                <i class="fas fa-pen-to-square"></i>
                                            </button>
                                            <button
                                                onclick="adjustStock(<?php echo $item['id']; ?>, '<?php echo htmlspecialchars($item['item_name']); ?>', <?php echo $item['stock']; ?>)"
                                                class="text-blue-600 text-xs hover:underline">
                                                <i class="fas fa-chart-line"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (empty($inventory)): ?>
                                    <tr>
                                        <td colspan="7" class="p-8 text-center text-slate-500">No inventory items found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- BOTTOM: REORDER LIST & SUPPLIERS -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- items to reorder -->
                    <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 p-5">
                        <h2 class="font-semibold text-lg flex items-center gap-2 mb-3"><i
                                class="fas fa-clock text-amber-600"></i> items to reorder</h2>
                        <div class="space-y-3" id="reorderList">
                            <?php foreach ($itemsToReorder as $item): ?>
                                <div class="flex justify-between items-center border-b pb-2">
                                    <div>
                                        <span class="font-medium"><?php echo htmlspecialchars($item['item_name']); ?></span>
                                        <p class="text-xs text-slate-500">Current: <?php echo $item['stock']; ?> | Reorder
                                            at: <?php echo $item['reorder_level']; ?></p>
                                    </div>
                                    <button
                                        onclick="reorderItem(<?php echo $item['id']; ?>, '<?php echo htmlspecialchars($item['item_name']); ?>')"
                                        class="bg-amber-600 text-white px-3 py-1 rounded-lg text-xs hover:bg-amber-700">reorder</button>
                                </div>
                            <?php endforeach; ?>
                            <?php if (empty($itemsToReorder)): ?>
                                <p class="text-sm text-slate-500 text-center py-4">All items are well stocked</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- supplier contacts -->
                    <div class="bg-amber-50 border border-amber-200 rounded-2xl p-5">
                        <h3 class="font-semibold flex items-center gap-2 mb-3"><i
                                class="fas fa-truck text-amber-600"></i> primary suppliers</h3>
                        <ul class="space-y-2" id="supplierList">
                            <?php foreach ($suppliers as $supplier): ?>
                                <li class="text-sm border-b pb-1">
                                    <span class="font-medium"><?php echo htmlspecialchars($supplier['name']); ?></span>
                                    <p class="text-xs text-slate-500">
                                        Contact: <?php echo htmlspecialchars($supplier['contact_person']); ?> ·
                                        <?php echo htmlspecialchars($supplier['phone']); ?>
                                    </p>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <button onclick="openAddSupplierModal()"
                            class="mt-3 text-sm text-amber-700 hover:underline w-full text-center">
                            + add supplier
                        </button>
                    </div>
                </div>
            </main>
        </div>

        <!-- Add Item Modal -->
        <div id="addItemModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
            <div class="bg-white rounded-2xl p-6 max-w-md w-full mx-4">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-semibold">Add New Item</h3>
                    <button onclick="closeAddItemModal()" class="text-slate-400 hover:text-slate-600"><i
                            class="fa-solid fa-xmark text-2xl"></i></button>
                </div>
                <form id="addItemForm">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm text-slate-600 mb-1">Item Name *</label>
                            <input type="text" id="itemName" class="w-full border rounded-xl p-2" required>
                        </div>
                        <div>
                            <label class="block text-sm text-slate-600 mb-1">Category *</label>
                            <select id="itemCategory" class="w-full border rounded-xl p-2">
                                <option value="Food">Food</option>
                                <option value="Meat">Meat</option>
                                <option value="Housekeeping">Housekeeping</option>
                                <option value="Linens">Linens</option>
                                <option value="Amenities">Amenities</option>
                                <option value="Maintenance">Maintenance</option>
                                <option value="Supply">Supply</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm text-slate-600 mb-1">Initial Stock</label>
                            <input type="number" id="itemStock" class="w-full border rounded-xl p-2" value="0" min="0">
                        </div>
                        <div>
                            <label class="block text-sm text-slate-600 mb-1">Unit</label>
                            <input type="text" id="itemUnit" class="w-full border rounded-xl p-2" value="pcs"
                                placeholder="e.g., kg, pcs, bottles">
                        </div>
                        <div>
                            <label class="block text-sm text-slate-600 mb-1">Reorder Level</label>
                            <input type="number" id="itemReorderLevel" class="w-full border rounded-xl p-2" value="10"
                                min="0">
                        </div>
                    </div>
                    <div class="flex gap-3 mt-6">
                        <button type="button" onclick="closeAddItemModal()"
                            class="flex-1 border border-slate-200 px-4 py-2 rounded-xl hover:bg-slate-50">Cancel</button>
                        <button type="submit"
                            class="flex-1 bg-amber-600 text-white px-4 py-2 rounded-xl hover:bg-amber-700">Add
                            Item</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Edit Item Modal -->
        <div id="editItemModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
            <div class="bg-white rounded-2xl p-6 max-w-md w-full mx-4">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-semibold">Edit Item</h3>
                    <button onclick="closeEditItemModal()" class="text-slate-400 hover:text-slate-600"><i
                            class="fa-solid fa-xmark text-2xl"></i></button>
                </div>
                <form id="editItemForm">
                    <input type="hidden" id="editItemId">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm text-slate-600 mb-1">Item Name *</label>
                            <input type="text" id="editItemName" class="w-full border rounded-xl p-2" required>
                        </div>
                        <div>
                            <label class="block text-sm text-slate-600 mb-1">Category *</label>
                            <select id="editItemCategory" class="w-full border rounded-xl p-2">
                                <option value="Food">Food</option>
                                <option value="Meat">Meat</option>
                                <option value="Housekeeping">Housekeeping</option>
                                <option value="Linens">Linens</option>
                                <option value="Amenities">Amenities</option>
                                <option value="Maintenance">Maintenance</option>
                                <option value="Supply">Supply</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm text-slate-600 mb-1">Unit</label>
                            <input type="text" id="editItemUnit" class="w-full border rounded-xl p-2">
                        </div>
                        <div>
                            <label class="block text-sm text-slate-600 mb-1">Reorder Level</label>
                            <input type="number" id="editItemReorderLevel" class="w-full border rounded-xl p-2" min="0">
                        </div>
                    </div>
                    <div class="flex gap-3 mt-6">
                        <button type="button" onclick="closeEditItemModal()"
                            class="flex-1 border border-slate-200 px-4 py-2 rounded-xl hover:bg-slate-50">Cancel</button>
                        <button type="submit"
                            class="flex-1 bg-amber-600 text-white px-4 py-2 rounded-xl hover:bg-amber-700">Update</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Adjust Stock Modal -->
        <div id="adjustStockModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
            <div class="bg-white rounded-2xl p-6 max-w-md w-full mx-4">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-semibold">Adjust Stock</h3>
                    <button onclick="closeAdjustStockModal()" class="text-slate-400 hover:text-slate-600"><i
                            class="fa-solid fa-xmark text-2xl"></i></button>
                </div>
                <form id="adjustStockForm">
                    <input type="hidden" id="adjustItemId">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm text-slate-600 mb-1">Item</label>
                            <p id="adjustItemName" class="font-medium"></p>
                        </div>
                        <div>
                            <label class="block text-sm text-slate-600 mb-1">Current Stock</label>
                            <p id="adjustCurrentStock" class="font-medium"></p>
                        </div>
                        <div>
                            <label class="block text-sm text-slate-600 mb-1">Adjustment (+/-)</label>
                            <input type="number" id="adjustAmount" class="w-full border rounded-xl p-2"
                                placeholder="e.g., +10 or -5" value="0" step="1">
                        </div>
                        <div>
                            <label class="block text-sm text-slate-600 mb-1">Reason</label>
                            <input type="text" id="adjustReason" class="w-full border rounded-xl p-2"
                                placeholder="Stock count, damaged, etc.">
                        </div>
                    </div>
                    <div class="flex gap-3 mt-6">
                        <button type="button" onclick="closeAdjustStockModal()"
                            class="flex-1 border border-slate-200 px-4 py-2 rounded-xl hover:bg-slate-50">Cancel</button>
                        <button type="submit"
                            class="flex-1 bg-amber-600 text-white px-4 py-2 rounded-xl hover:bg-amber-700">Adjust
                            Stock</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Create PO Modal -->
        <div id="createPOModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
            <div class="bg-white rounded-2xl p-6 max-w-3xl w-full mx-4 max-h-[90vh] overflow-y-auto">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-semibold">Create Purchase Order</h3>
                    <button onclick="closeCreatePOModal()" class="text-slate-400 hover:text-slate-600"><i
                            class="fa-solid fa-xmark text-2xl"></i></button>
                </div>
                <form id="createPOForm">
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm text-slate-600 mb-1">Supplier *</label>
                            <select id="poSupplier" class="w-full border rounded-xl p-2" required>
                                <option value="">Select supplier...</option>
                                <?php foreach ($suppliers as $supplier): ?>
                                    <option value="<?php echo $supplier['id']; ?>">
                                        <?php echo htmlspecialchars($supplier['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm text-slate-600 mb-1">Expected Delivery</label>
                            <input type="date" id="poExpectedDelivery" class="w-full border rounded-xl p-2"
                                value="<?php echo date('Y-m-d', strtotime('+7 days')); ?>">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm text-slate-600 mb-1">Items *</label>
                        <div id="poItems" class="space-y-2">
                            <div class="po-item flex gap-2 items-center">
                                <select class="po-item-select flex-1 border rounded-xl p-2" required>
                                    <option value="">Select item...</option>
                                    <?php foreach ($inventory as $item): ?>
                                        <option value="<?php echo $item['id']; ?>"
                                            data-unit="<?php echo htmlspecialchars($item['unit']); ?>">
                                            <?php echo htmlspecialchars($item['item_name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <input type="number" class="po-item-qty w-24 border rounded-xl p-2" placeholder="Qty"
                                    min="1" value="1">
                                <input type="number" class="po-item-price w-32 border rounded-xl p-2"
                                    placeholder="Unit price" step="0.01" min="0">
                                <button type="button" class="text-red-500 hover:text-red-700"
                                    onclick="removePOItem(this)"><i class="fa-solid fa-trash"></i></button>
                            </div>
                        </div>
                        <button type="button" class="mt-2 text-sm text-amber-700 hover:underline"
                            onclick="addPOItem()">+ add item</button>
                    </div>

                    <div>
                        <label class="block text-sm text-slate-600 mb-1">Notes</label>
                        <textarea id="poNotes" class="w-full border rounded-xl p-2" rows="2"></textarea>
                    </div>

                    <div class="flex gap-3 mt-6">
                        <button type="button" onclick="closeCreatePOModal()"
                            class="flex-1 border border-slate-200 px-4 py-2 rounded-xl hover:bg-slate-50">Cancel</button>
                        <button type="submit"
                            class="flex-1 bg-amber-600 text-white px-4 py-2 rounded-xl hover:bg-amber-700">Create
                            PO</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Add Supplier Modal -->
        <div id="addSupplierModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
            <div class="bg-white rounded-2xl p-6 max-w-md w-full mx-4">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-semibold">Add Supplier</h3>
                    <button onclick="closeAddSupplierModal()" class="text-slate-400 hover:text-slate-600"><i
                            class="fa-solid fa-xmark text-2xl"></i></button>
                </div>
                <form id="addSupplierForm">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm text-slate-600 mb-1">Supplier Name *</label>
                            <input type="text" id="supplierName" class="w-full border rounded-xl p-2" required>
                        </div>
                        <div>
                            <label class="block text-sm text-slate-600 mb-1">Contact Person</label>
                            <input type="text" id="supplierContact" class="w-full border rounded-xl p-2">
                        </div>
                        <div>
                            <label class="block text-sm text-slate-600 mb-1">Phone</label>
                            <input type="text" id="supplierPhone" class="w-full border rounded-xl p-2">
                        </div>
                        <div>
                            <label class="block text-sm text-slate-600 mb-1">Email</label>
                            <input type="email" id="supplierEmail" class="w-full border rounded-xl p-2">
                        </div>
                        <div>
                            <label class="block text-sm text-slate-600 mb-1">Address</label>
                            <textarea id="supplierAddress" class="w-full border rounded-xl p-2" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="flex gap-3 mt-6">
                        <button type="button" onclick="closeAddSupplierModal()"
                            class="flex-1 border border-slate-200 px-4 py-2 rounded-xl hover:bg-slate-50">Cancel</button>
                        <button type="submit"
                            class="flex-1 bg-amber-600 text-white px-4 py-2 rounded-xl hover:bg-amber-700">Add
                            Supplier</button>
                    </div>
                </form>
            </div>
        </div>

        <script>
            // Toast notification
            function showToast(message, type = 'info') {
                const toast = document.getElementById('toast');
                const toastMessage = document.getElementById('toastMessage');
                const toastTime = document.getElementById('toastTime');
                const now = new Date();

                toastMessage.textContent = message;
                toastTime.textContent = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
                toast.classList.remove('hidden');
                setTimeout(() => toast.classList.add('show'), 10);
                setTimeout(() => {
                    toast.classList.remove('show');
                    setTimeout(() => toast.classList.add('hidden'), 300);
                }, 3000);
            }

            // Category filters
            document.querySelectorAll('.category-tab').forEach(tab => {
                tab.addEventListener('click', function () {
                    const category = this.dataset.category;
                    const url = new URL(window.location);
                    if (category !== 'all') {
                        url.searchParams.set('category', category);
                    } else {
                        url.searchParams.delete('category');
                    }
                    window.location.href = url.toString();
                });
            });

            // Search
            document.getElementById('searchInput').addEventListener('keypress', function (e) {
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

            // Filter low stock
            function filterLowStock() {
                const url = new URL(window.location);
                url.searchParams.set('status', 'low');
                window.location.href = url.toString();
            }

            function filterByStatus(status) {
                const url = new URL(window.location);
                if (status !== 'all') {
                    url.searchParams.set('status', status);
                } else {
                    url.searchParams.delete('status');
                }
                window.location.href = url.toString();
            }

            // Add Item Modal
            function openAddItemModal() {
                document.getElementById('addItemModal').classList.remove('hidden');
                document.getElementById('addItemModal').classList.add('flex');
            }

            function closeAddItemModal() {
                document.getElementById('addItemModal').classList.add('hidden');
                document.getElementById('addItemModal').classList.remove('flex');
                document.getElementById('addItemForm').reset();
            }

            document.getElementById('addItemForm').addEventListener('submit', async function (e) {
                e.preventDefault();

                const formData = new FormData();
                formData.append('action', 'add_item');
                formData.append('item_name', document.getElementById('itemName').value);
                formData.append('category', document.getElementById('itemCategory').value);
                formData.append('stock', document.getElementById('itemStock').value);
                formData.append('unit', document.getElementById('itemUnit').value);
                formData.append('reorder_level', document.getElementById('itemReorderLevel').value);

                Swal.fire({ title: 'Adding...', text: 'Please wait', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

                const response = await fetch('../../../controller/admin/post/inventory_post.php', { method: 'POST', body: formData });
                const data = await response.json();

                if (data.success) {
                    Swal.fire({ title: 'Success!', text: data.message, icon: 'success', confirmButtonColor: '#d97706' }).then(() => location.reload());
                } else {
                    Swal.fire({ title: 'Error', text: data.message, icon: 'error', confirmButtonColor: '#d97706' });
                }
                closeAddItemModal();
            });

            // Edit Item
            async function editItem(id) {
                const formData = new FormData();
                formData.append('action', 'get_item');
                formData.append('item_id', id);

                const response = await fetch('../../../controller/admin/post/inventory_post.php', { method: 'POST', body: formData });
                const data = await response.json();

                if (data.success) {
                    document.getElementById('editItemId').value = data.item.id;
                    document.getElementById('editItemName').value = data.item.item_name;
                    document.getElementById('editItemCategory').value = data.item.category;
                    document.getElementById('editItemUnit').value = data.item.unit;
                    document.getElementById('editItemReorderLevel').value = data.item.reorder_level;
                    document.getElementById('editItemModal').classList.remove('hidden');
                    document.getElementById('editItemModal').classList.add('flex');
                }
            }

            function closeEditItemModal() {
                document.getElementById('editItemModal').classList.add('hidden');
                document.getElementById('editItemModal').classList.remove('flex');
            }

            document.getElementById('editItemForm').addEventListener('submit', async function (e) {
                e.preventDefault();

                const formData = new FormData();
                formData.append('action', 'update_item');
                formData.append('item_id', document.getElementById('editItemId').value);
                formData.append('item_name', document.getElementById('editItemName').value);
                formData.append('category', document.getElementById('editItemCategory').value);
                formData.append('unit', document.getElementById('editItemUnit').value);
                formData.append('reorder_level', document.getElementById('editItemReorderLevel').value);

                Swal.fire({ title: 'Updating...', text: 'Please wait', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

                const response = await fetch('../../../controller/admin/post/inventory_post.php', { method: 'POST', body: formData });
                const data = await response.json();

                if (data.success) {
                    Swal.fire({ title: 'Success!', text: data.message, icon: 'success', confirmButtonColor: '#d97706' }).then(() => location.reload());
                } else {
                    Swal.fire({ title: 'Error', text: data.message, icon: 'error', confirmButtonColor: '#d97706' });
                }
                closeEditItemModal();
            });

            // Adjust Stock
            function adjustStock(id, name, currentStock) {
                document.getElementById('adjustItemId').value = id;
                document.getElementById('adjustItemName').textContent = name;
                document.getElementById('adjustCurrentStock').textContent = currentStock;
                document.getElementById('adjustAmount').value = 0;
                document.getElementById('adjustReason').value = '';
                document.getElementById('adjustStockModal').classList.remove('hidden');
                document.getElementById('adjustStockModal').classList.add('flex');
            }

            function closeAdjustStockModal() {
                document.getElementById('adjustStockModal').classList.add('hidden');
                document.getElementById('adjustStockModal').classList.remove('flex');
            }

            document.getElementById('adjustStockForm').addEventListener('submit', async function (e) {
                e.preventDefault();

                const formData = new FormData();
                formData.append('action', 'adjust_stock');
                formData.append('item_id', document.getElementById('adjustItemId').value);
                formData.append('adjustment', document.getElementById('adjustAmount').value);
                formData.append('reason', document.getElementById('adjustReason').value);

                Swal.fire({ title: 'Adjusting...', text: 'Please wait', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

                const response = await fetch('../../../controller/admin/post/inventory_post.php', { method: 'POST', body: formData });
                const data = await response.json();

                if (data.success) {
                    Swal.fire({ title: 'Success!', text: data.message, icon: 'success', confirmButtonColor: '#d97706' }).then(() => location.reload());
                } else {
                    Swal.fire({ title: 'Error', text: data.message, icon: 'error', confirmButtonColor: '#d97706' });
                }
                closeAdjustStockModal();
            });

            // PO Functions
            function openCreatePOModal() {
                document.getElementById('createPOModal').classList.remove('hidden');
                document.getElementById('createPOModal').classList.add('flex');
            }

            function closeCreatePOModal() {
                document.getElementById('createPOModal').classList.add('hidden');
                document.getElementById('createPOModal').classList.remove('flex');
            }

            function addPOItem() {
                const container = document.getElementById('poItems');
                const newItem = document.createElement('div');
                newItem.className = 'po-item flex gap-2 items-center';
                newItem.innerHTML = `
                <select class="po-item-select flex-1 border rounded-xl p-2" required>
                    <option value="">Select item...</option>
                    <?php foreach ($inventory as $item): ?>
                        <option value="<?php echo $item['id']; ?>" data-unit="<?php echo htmlspecialchars($item['unit']); ?>"><?php echo htmlspecialchars($item['item_name']); ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="number" class="po-item-qty w-24 border rounded-xl p-2" placeholder="Qty" min="1" value="1">
                <input type="number" class="po-item-price w-32 border rounded-xl p-2" placeholder="Unit price" step="0.01" min="0">
                <button type="button" class="text-red-500 hover:text-red-700" onclick="removePOItem(this)"><i class="fa-solid fa-trash"></i></button>
            `;
                container.appendChild(newItem);
            }

            function removePOItem(btn) {
                btn.parentElement.remove();
            }

            document.getElementById('createPOForm').addEventListener('submit', async function (e) {
                e.preventDefault();

                const items = [];
                document.querySelectorAll('.po-item').forEach(item => {
                    const select = item.querySelector('.po-item-select');
                    const qty = item.querySelector('.po-item-qty').value;
                    const price = item.querySelector('.po-item-price').value;

                    if (select.value && qty && price) {
                        items.push({
                            inventory_id: select.value,
                            quantity: parseInt(qty),
                            unit_price: parseFloat(price)
                        });
                    }
                });

                if (items.length === 0) {
                    showToast('Please add at least one item', 'error');
                    return;
                }

                const formData = new FormData();
                formData.append('action', 'create_po');
                formData.append('supplier_id', document.getElementById('poSupplier').value);
                formData.append('items', JSON.stringify(items));
                formData.append('expected_delivery', document.getElementById('poExpectedDelivery').value);
                formData.append('notes', document.getElementById('poNotes').value);

                Swal.fire({ title: 'Creating PO...', text: 'Please wait', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

                const response = await fetch('../../../controller/admin/post/inventory_post.php', { method: 'POST', body: formData });
                const data = await response.json();

                if (data.success) {
                    Swal.fire({ title: 'Success!', text: data.message, icon: 'success', confirmButtonColor: '#d97706' }).then(() => location.reload());
                } else {
                    Swal.fire({ title: 'Error', text: data.message, icon: 'error', confirmButtonColor: '#d97706' });
                }
                closeCreatePOModal();
            });

            // Supplier Functions
            function openAddSupplierModal() {
                document.getElementById('addSupplierModal').classList.remove('hidden');
                document.getElementById('addSupplierModal').classList.add('flex');
            }

            function closeAddSupplierModal() {
                document.getElementById('addSupplierModal').classList.add('hidden');
                document.getElementById('addSupplierModal').classList.remove('flex');
                document.getElementById('addSupplierForm').reset();
            }

            document.getElementById('addSupplierForm').addEventListener('submit', async function (e) {
                e.preventDefault();

                const formData = new FormData();
                formData.append('action', 'add_supplier');
                formData.append('name', document.getElementById('supplierName').value);
                formData.append('contact', document.getElementById('supplierContact').value);
                formData.append('phone', document.getElementById('supplierPhone').value);
                formData.append('email', document.getElementById('supplierEmail').value);
                formData.append('address', document.getElementById('supplierAddress').value);

                Swal.fire({ title: 'Adding...', text: 'Please wait', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

                const response = await fetch('../../../controller/admin/post/inventory_post.php', { method: 'POST', body: formData });
                const data = await response.json();

                if (data.success) {
                    Swal.fire({ title: 'Success!', text: data.message, icon: 'success', confirmButtonColor: '#d97706' }).then(() => location.reload());
                } else {
                    Swal.fire({ title: 'Error', text: data.message, icon: 'error', confirmButtonColor: '#d97706' });
                }
                closeAddSupplierModal();
            });

            // Reorder item
            function reorderItem(id, name) {
                Swal.fire({
                    title: 'Reorder Item',
                    text: `Create purchase order for ${name}?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#d97706',
                    confirmButtonText: 'Yes, create PO'
                }).then((result) => {
                    if (result.isConfirmed) {
                        openCreatePOModal();
                        // Could pre-select the item here
                    }
                });
            }

            function openReceiveModal() {
                Swal.fire({
                    title: 'Receive Stock',
                    text: 'hindi ko alam ilalagay dito',
                    icon: 'info',
                    confirmButtonColor: '#d97706'
                });
            }

            function exportInventory() {
                window.location.href = '../../../controller/admin/get/export_inventory.php';
            }

            // Notification bell
            document.getElementById('notificationBell').addEventListener('click', function () {
                window.location.href = '../notifications.php';
            });

            // Close modals on outside click
            window.onclick = function (event) {
                const modals = ['addItemModal', 'editItemModal', 'adjustStockModal', 'createPOModal', 'addSupplierModal'];
                modals.forEach(modalId => {
                    const modal = document.getElementById(modalId);
                    if (modal && event.target === modal) {
                        modal.classList.add('hidden');
                        modal.classList.remove('flex');
                    }
                });
            }
        </script>
    </body>

</html>