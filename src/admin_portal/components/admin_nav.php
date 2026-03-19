<?php
/**
 * Admin Portal Navigation Component
 * Reusable navigation for all admin pages
 * 
 * Usage: require_once 'path/to/admin_nav.php';
 * 
 * Required variables:
 * - $admin: array with admin user data (full_name, role, etc.)
 * - $initials: string (admin initials for avatar)
 * - $current_page: string (e.g., 'dashboard', 'customer_feedback', etc.) - defaults to current file name
 */

// Set default values if not provided
$current_page = $current_page ?? basename($_SERVER['PHP_SELF'], '.php');
$admin = $admin ?? ['full_name' => 'Admin User', 'role' => 'administrator'];
$initials = $initials ?? 'A';

// Define navigation structure with correct paths based on your directory structure
$nav_groups = [
    'dashboard' => [
        'type' => 'single',
        'icon' => 'fa-solid fa-table-cells-large',
        'label' => 'Dashboard',
        'url' => '/src/admin_portal/dashboard.php'
    ],
    'hotel_management' => [
        'type' => 'group',
        'icon' => 'fa-solid fa-hotel',
        'label' => 'HOTEL MANAGEMENT',
        'items' => [
            'front_desk' => [
                'label' => 'Front Desk / Reception',
                'url' => '/src/admin_portal/hotel_management/front_desk_reception.php',
                'icon' => 'fas fa-reception'
            ],
            'room_management' => [
                'label' => 'Room Management',
                'url' => '/src/admin_portal/hotel_management/room_management.php',
                'icon' => 'fa-solid fa-bed'
            ],
            'reservations_booking' => [
                'label' => 'Reservations & Booking',
                'url' => '/src/admin_portal/hotel_management/reservation_&_booking.php',
                'icon' => 'fas fa-calendar-check'
            ],
            'housekeeping' => [
                'label' => 'Housekeeping & Maintenance',
                'url' => '/src/admin_portal/hotel_management/housekeeping_&_maintenance.php',
                'icon' => 'fa-solid fa-broom'
            ],
            'events_conference' => [
                'label' => 'Events & Conference',
                'url' => '/src/admin_portal/hotel_management/event_&_conference.php',
                'icon' => 'fas fa-calendar'
            ]
        ]
    ],
    'restaurant_management' => [
        'type' => 'group',
        'icon' => 'fa-solid fa-utensils',
        'label' => 'RESTAURANT MANAGEMENT',
        'items' => [
            'table_reservation' => [
                'label' => 'Table Reservation',
                'url' => '/src/admin_portal/restaurant_management/table_reservation.php',
                'icon' => 'fas fa-clock'
            ],
            'menu_management' => [
                'label' => 'Menu Management',
                'url' => '/src/admin_portal/restaurant_management/menu_management.php',
                'icon' => 'fa-solid fa-bars'
            ],
            'orders_pos' => [
                'label' => 'Orders / POS',
                'url' => '/src/admin_portal/restaurant_management/orders_pos.php',
                'icon' => 'fa-solid fa-cash-register'
            ],
            'kitchen_orders' => [
                'label' => 'Kitchen Orders (KOT)',
                'url' => '/src/admin_portal/restaurant_management/kitchen_orders.php',
                'icon' => 'fa-solid fa-fire'
            ],
            'wait_staff' => [
                'label' => 'Wait Staff Management',
                'url' => '/src/admin_portal/restaurant_management/wait_staff_management.php',
                'icon' => 'fas fa-user'
            ]
        ]
    ],
    'customer_management' => [
        'type' => 'group',
        'icon' => 'fas fa-address-book',
        'label' => 'CUSTOMER MANAGEMENT',
        'items' => [
            'crm' => [
                'label' => 'Guest Relationship (CRM)',
                'url' => '/src/admin_portal/customer_management/customer_relationship.php',
                'icon' => 'fas fa-handshake'
            ],
            'loyalty_rewards' => [
                'label' => 'Loyalty & Rewards',
                'url' => '/src/admin_portal/customer_management/loyalty_rewards.php',
                'icon' => 'fas fa-star'
            ],
            'feedback_reviews' => [
                'label' => 'Customer Feedback & Reviews',
                'url' => '/src/admin_portal/customer_management/customer_feedback_&_reviews.php',
                'icon' => 'fas fa-pen-to-square'
            ]
        ]
    ],
    'operations' => [
        'type' => 'group',
        'icon' => 'fa-solid fa-gears',
        'label' => 'OPERATIONS',
        'items' => [
            'inventory' => [
                'label' => 'Inventory & Stock',
                'url' => '/src/admin_portal/operations/inventory_&_stocks.php',
                'icon' => 'fa-solid fa-boxes'
            ],
            'billing_payments' => [
                'label' => 'Billing & Payments',
                'url' => '/src/admin_portal/operations/billing_&_payment.php',
                'icon' => 'fas fa-credit-card'
            ],
            'payment_gateway' => [
                'label' => 'Payment Gateway',
                'url' => '/src/admin_portal/operations/payment_gateway.php',
                'icon' => 'fa-solid fa-wifi'
            ]
        ]
    ],
    'marketing' => [
        'type' => 'group',
        'icon' => 'fa-solid fa-megaphone',
        'label' => 'MARKETING',
        'items' => [
            'promotions' => [
                'label' => 'Hotel Marketing & Promotions',
                'url' => '/src/admin_portal/marketing/hotelmarketing_&_promotions.php',
                'icon' => 'fas fa-gem'
            ],
            'online_ordering' => [
                'label' => 'Online Ordering Integration',
                'url' => '/src/admin_portal/marketing/online_ordering_integration.php',
                'icon' => 'fa-solid fa-cart-shopping'
            ]
        ]
    ],
    'reports_analytics' => [
        'type' => 'group',
        'icon' => 'fa-solid fa-chart-simple',
        'label' => 'REPORTS & ANALYTICS',
        'items' => [
            'sales_reports' => [
                'label' => 'Sales Reports',
                'url' => '/src/admin_portal/reports_&_analytics/sales_report.php',
                'icon' => 'fa-solid fa-chart-line'
            ],
            'booking_reports' => [
                'label' => 'Booking Reports',
                'url' => '/src/admin_portal/reports_&_analytics/booking_reports.php',
                'icon' => 'fas fa-calendar'
            ],
            'analytics_dashboard' => [
                'label' => 'Analytics Dashboard',
                'url' => '/src/admin_portal/reports_&_analytics/analytics_dashboard.php',
                'icon' => 'fa-solid fa-chart-pie'
            ]
        ]
    ],
    'system' => [
        'type' => 'group',
        'icon' => 'fa-solid fa-computer',
        'label' => 'SYSTEM',
        'items' => [
            'channel_management' => [
                'label' => 'Channel Management',
                'url' => '/src/admin_portal/system/channel_management.php',
                'icon' => 'fa-solid fa-code-branch'
            ],
            'door_lock' => [
                'label' => 'Door Lock Integration',
                'url' => '/src/admin_portal/system/door_lock_integration.php',
                'icon' => 'fa-solid fa-lock'
            ],
            'settings' => [
                'label' => 'Settings',
                'url' => '/src/admin_portal/system/settings.php',
                'icon' => 'fa-solid fa-sliders'
            ]
        ]
    ]
];

// Helper function to check if a page is active
function isPageActive($url, $current_page)
{
    $url_parts = explode('/', $url);
    $filename = end($url_parts);
    $page_name = basename($filename, '.php');
    return $page_name === $current_page;
}

// Helper function to check if a group contains the active page
function isGroupActive($group_items, $current_page)
{
    foreach ($group_items as $item) {
        if (isPageActive($item['url'], $current_page)) {
            return true;
        }
    }
    return false;
}
?>

<!-- ========== SIDEBAR ========== -->
<aside class="lg:w-80 bg-white border-r border-slate-200 shadow-sm lg:min-h-screen shrink-0 overflow-y-auto">
    <!-- brand -->
    <div class="px-5 py-6 border-b border-slate-100 flex items-center gap-2">
        <i class="fa-solid fa-utensils text-amber-600 text-xl"></i>
        <i class="fa-solid fa-bed text-amber-600 text-xl"></i>
        <span class="font-semibold text-lg tracking-tight text-slate-800">HNR<span class="text-amber-600">
                Admin</span></span>
    </div>

    <!-- admin badge -->
    <div class="flex items-center gap-3 px-5 py-4 border-b border-slate-100 bg-slate-50/60">
        <div class="h-9 w-9 rounded-full bg-amber-200 flex items-center justify-center text-amber-800 font-bold">
            <?php echo htmlspecialchars($initials); ?>
        </div>
        <div>
            <p class="font-medium text-sm">
                <?php echo htmlspecialchars($admin['full_name'] ?? 'Admin User'); ?>
            </p>
            <p class="text-xs text-slate-500">
                <?php echo htmlspecialchars($admin['role'] ?? 'administrator'); ?>
            </p>
        </div>
    </div>

    <!-- ===== SIDEBAR MENU ===== -->
    <nav class="p-4 space-y-2 text-sm">

        <!-- Dashboard (Single Item) -->
        <?php
        $dashboard_active = isPageActive($nav_groups['dashboard']['url'], $current_page);
        ?>
        <a href="<?php echo $nav_groups['dashboard']['url']; ?>"
            class="flex items-center gap-3 px-4 py-2.5 rounded-xl <?php echo $dashboard_active ? 'bg-amber-50 text-amber-800 font-medium' : 'text-slate-700 hover:bg-amber-50 transition'; ?>">
            <i
                class="<?php echo $nav_groups['dashboard']['icon']; ?> w-5 <?php echo $dashboard_active ? 'text-amber-600' : 'text-slate-400'; ?>"></i>
            <span>
                <?php echo $nav_groups['dashboard']['label']; ?>
            </span>
        </a>

        <!-- Navigation Groups -->
        <?php foreach ($nav_groups as $key => $group): ?>
            <?php if ($key === 'dashboard')
                continue; // Skip dashboard as it's handled separately ?>

            <?php
            $group_active = isGroupActive($group['items'], $current_page);
            $group_open = $group_active ? 'open' : 'open'; // You can set to 'open' to keep all groups open by default
            ?>

            <details class="group" <?php echo $group_open; ?>>
                <summary
                    class="flex items-center gap-3 px-4 py-2.5 rounded-xl <?php echo $group_active ? 'text-amber-800 bg-amber-50' : 'text-slate-700 hover:bg-amber-50'; ?> cursor-pointer transition-side">
                    <i
                        class="<?php echo $group['icon']; ?> w-5 <?php echo $group_active ? 'text-amber-600' : 'text-slate-400 group-open:text-amber-600'; ?>"></i>
                    <span class="font-medium">
                        <?php echo $group['label']; ?>
                    </span>
                    <i
                        class="fa-solid fa-chevron-right dropdown-arrow ml-auto text-xs <?php echo $group_active ? 'text-amber-600' : 'text-slate-400'; ?>"></i>
                </summary>
                <div
                    class="ml-6 mt-1 space-y-1 pl-3 border-l-2 <?php echo $group_active ? 'border-amber-200' : 'border-amber-100'; ?>">
                    <?php foreach ($group['items'] as $item_key => $item): ?>
                        <?php
                        $item_active = isPageActive($item['url'], $current_page);
                        ?>
                        <a href="<?php echo $item['url']; ?>"
                            class="flex items-center gap-2 px-4 py-2 rounded-lg <?php echo $item_active ? 'bg-amber-100/50 text-amber-700 font-medium' : 'text-slate-600 hover:bg-amber-50'; ?>">
                            <i
                                class="<?php echo $item['icon']; ?> w-4 <?php echo $item_active ? 'text-amber-600' : 'text-slate-400'; ?>"></i>
                            <?php echo $item['label']; ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </details>
        <?php endforeach; ?>

        <!-- logout -->
        <div class="border-t border-slate-200 pt-3 mt-3">
            <a href="/controller/auth/logout.php"
                class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-500 hover:bg-red-50 hover:text-red-700">
                <i class="fa-solid fa-arrow-right-from-bracket w-5"></i>
                <span>Logout</span>
            </a>
        </div>
    </nav>
</aside>

<style>
    .dropdown-arrow {
        transition: transform 0.2s;
    }

    details[open] .dropdown-arrow {
        transform: rotate(90deg);
    }

    details>summary {
        list-style: none;
        cursor: pointer;
    }

    details summary::-webkit-details-marker {
        display: none;
    }

    .transition-side {
        transition: all 0.2s ease;
    }
</style>