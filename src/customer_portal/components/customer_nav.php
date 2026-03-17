<?php
/**
 * Customer Portal Navigation Component
 * Reusable navigation for all customer pages
 * 
 * Usage: require_once 'path/to/customer_nav.php';
 * 
 * Required variables:
 * - $current_page: string (e.g., 'dashboard', 'profile', 'hotel_booking', etc.)
 * - $user: array with user data (full_name, first_name, last_name, etc.)
 * - $initials: string (user initials for avatar)
 * - $unread_count: int (unread notifications count)
 * - $member_tier: string (user's membership tier)
 * - $points: int (user's loyalty points)
 */

// Set default values if not provided
$current_page = $current_page ?? 'dashboard';
$member_tier = $member_tier ?? 'bronze';
$points = $points ?? 0;
$unread_count = $unread_count ?? 0;
$initials = $initials ?? 'G';

// Define navigation items
$nav_items = [
    'dashboard' => [
        'icon' => 'fa-solid fa-table-cells-large',
        'label' => 'Dashboard',
        'url' => './index.php'
    ],
    'profile' => [
        'icon' => 'fa-regular fa-user',
        'label' => 'My Profile',
        'url' => './my_profile.php'
    ],
    'hotel_booking' => [
        'icon' => 'fa-solid fa-hotel',
        'label' => 'Hotel Booking',
        'url' => './hotel_booking.php'
    ],
    'my_reservation' => [
        'icon' => 'fa-regular fa-calendar-check',
        'label' => 'My Reservations',
        'url' => './my_reservation.php'
    ],
    'restaurant_reservation' => [
        'icon' => 'fa-regular fa-clock',
        'label' => 'Restaurant Reservation',
        'url' => './restaurant_reservation.php'
    ],
    'order_food' => [
        'icon' => 'fa-solid fa-bag-shopping',
        'label' => 'Menu / Order Food',
        'url' => './order_food.php'
    ],
    'payments' => [
        'icon' => 'fa-regular fa-credit-card',
        'label' => 'Payments',
        'url' => './payments.php'
    ],
    'loyalty_rewards' => [
        'icon' => 'fa-regular fa-star',
        'label' => 'Loyalty Rewards',
        'url' => './loyalty_rewards.php'
    ],
    'notifications' => [
        'icon' => 'fa-regular fa-bell',
        'label' => 'Notifications',
        'url' => './notifications.php',
        'badge' => $unread_count > 0 ? $unread_count : null
    ]
];
?>

<!-- ========== SIDEBAR ========== -->
<aside class="lg:w-80 bg-white border-r border-slate-200 shadow-sm shrink-0">
    <!-- brand / header -->
    <div class="px-6 py-7 border-b border-slate-100">
        <div class="flex items-center gap-2 text-amber-700">
            <i class="fa-solid fa-utensils text-xl"></i>
            <i class="fa-solid fa-bed text-xl"></i>
            <span class="font-semibold text-xl tracking-tight text-slate-800 ml-1">
                Lùcas<span class="text-amber-600">.stay</span>
            </span>
        </div>
        <p class="text-xs text-slate-500 mt-1 tracking-wide">customer portal ·
            <?php echo str_replace('_', ' ', $current_page); ?>
        </p>
    </div>

    <!-- user summary -->
    <div class="flex items-center gap-3 px-6 py-5 border-b border-slate-100 bg-slate-50/80">
        <div class="h-12 w-12 rounded-full bg-amber-200 flex items-center justify-center text-amber-800 font-bold text-lg overflow-hidden"
            id="userInitials">
            <?php if (!empty($user['avatar'] ?? '')): ?>
                <img src="../../../<?php echo htmlspecialchars($user['avatar']); ?>" alt="Avatar"
                    class="w-full h-full object-cover">
            <?php else: ?>
                <?php echo htmlspecialchars($initials); ?>
            <?php endif; ?>
        </div>
        <div>
            <p class="font-medium text-slate-800" id="displayName">
                <?php echo htmlspecialchars($user['full_name'] ?? 'Guest'); ?>
            </p>
            <div class="flex items-center gap-1 text-xs text-slate-500">
                <i class="fa-regular fa-gem text-[11px]"></i>
                <span id="loyaltyTier"><?php echo htmlspecialchars($member_tier); ?></span>
                <span class="mx-1">·</span>
                <span class="flex items-center gap-1">
                    <i class="fa-regular fa-star text-amber-500"></i>
                    <span id="points"><?php echo number_format($points); ?></span> pts
                </span>
            </div>
            <!-- Added note about points being admin-managed (only visible on hover for clean UI) -->
            <p class="text-[10px] text-amber-600 mt-0.5 opacity-70 hover:opacity-100 transition-opacity cursor-help"
                title="Loyalty points are added by admin after payment verification">
                <i class="fa-regular fa-circle-info mr-1"></i>admin-managed
            </p>
        </div>
    </div>

    <!-- navigation menu -->
    <nav class="p-4 space-y-1.5 text-sm">
        <?php foreach ($nav_items as $key => $item): ?>
            <?php
            $is_active = ($current_page === $key);
            $active_class = $is_active ? 'bg-amber-50 text-amber-800 font-medium' : 'text-slate-700 hover:bg-amber-50 transition';
            $icon_class = $is_active ? 'text-amber-600' : 'text-slate-400';
            ?>
            <a href="<?php echo $item['url']; ?>"
                class="flex items-center gap-3 px-4 py-2.5 rounded-xl <?php echo $active_class; ?>">
                <i class="<?php echo $item['icon']; ?> w-5 <?php echo $icon_class; ?>"></i>
                <?php echo $item['label']; ?>
                <?php if (isset($item['badge']) && $item['badge']): ?>
                    <span class="ml-auto bg-amber-100 text-amber-800 text-xs px-1.5 py-0.5 rounded-full">
                        <?php echo $item['badge']; ?>
                    </span>
                <?php endif; ?>
            </a>
        <?php endforeach; ?>

        <!-- Logout -->
        <div class="border-t border-slate-200 pt-3 mt-3">
            <a href="../../controller/auth/logout.php"
                class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-500 hover:bg-red-50 hover:text-red-700 transition">
                <i class="fa-solid fa-arrow-right-from-bracket w-5"></i>Logout
            </a>
        </div>
    </nav>

    <!-- upgrade card (optional - can be conditionally included) -->
    <?php if (isset($show_upgrade_card) && $show_upgrade_card): ?>
        <div class="mx-4 mt-3 p-4 bg-linear-to-br from-amber-50 to-amber-100/60 rounded-2xl border border-amber-200/60">
            <div class="flex items-center gap-2 text-amber-700">
                <i class="fa-regular fa-gem"></i>
                <span class="font-semibold text-sm" id="upgradeTitle">
                    <?php
                    $nextTier = $nextTier ?? '';
                    echo $nextTier != 'platinum (max)' ? 'unlock ' . $nextTier . ' status' : 'platinum member';
                    ?>
                </span>
            </div>
            <p class="text-xs text-slate-600 mt-1" id="upgradeMessage">
                <?php if (($pointsNeeded ?? 0) > 0): ?>
                    Need <span id="pointsToGold"><?php echo $pointsNeeded ?? 0; ?></span> more pts →
                    <?php
                    echo $nextTier == 'silver' ? '2% bonus' :
                        ($nextTier == 'gold' ? '5% bonus' :
                            ($nextTier == 'platinum' ? 'suite upgrade' : ''));
                    ?>
                <?php else: ?>
                    ✨ You're at max tier! Enjoy your platinum benefits.
                <?php endif; ?>
            </p>
            <!-- Added note in upgrade card about admin-managed points -->
            <p class="text-[10px] text-amber-500 mt-2 border-t border-amber-200/40 pt-2">
                <i class="fa-regular fa-clock mr-1"></i>
                Points updated by admin after payment verification
            </p>
        </div>
    <?php endif; ?>
</aside>

<!-- Optional: Add a small script to update points display if needed -->
<script>
    // This function can be called after payment to refresh points display
    // but points will only change when admin updates them
    function refreshPoints() {
        fetch('../../controller/customer/get/current_points.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('points').textContent = data.points.toLocaleString();
                    document.getElementById('loyaltyTier').textContent = data.tier;

                    // Update upgrade card if it exists
                    if (data.pointsNeeded !== undefined) {
                        const pointsNeededEl = document.getElementById('pointsToGold');
                        if (pointsNeededEl) pointsNeededEl.textContent = data.pointsNeeded;
                    }
                }
            })
            .catch(err => console.error('Failed to refresh points:', err));
    }
</script>