<?php
/**
 * View - Admin Loyalty & Rewards
 */
require_once '../../../controller/admin/get/loyalty_rewards.php';
?>
<!DOCTYPE html>
<html lang="en">

  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin · Loyalty & Rewards</title>
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

      .stat-card {
        transition: all 0.2s ease;
      }

      .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
      }
    </style>
  </head>

  <body class="bg-white font-sans antialiased">

    <!-- APP CONTAINER: flex row (sidebar + main) -->
    <div class="min-h-screen flex flex-col lg:flex-row">

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
            <?php echo $initials; ?>
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

          <!-- Dashboard -->
          <a href="../dashboard.php"
            class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-700 hover:bg-amber-50 transition">
            <i class="fa-solid fa-table-cells-large w-5 text-slate-400"></i>
            <span>Dashboard</span>
          </a>

          <!-- HOTEL MANAGEMENT GROUP -->
          <details class="group" open>
            <summary
              class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-700 hover:bg-amber-50 cursor-pointer">
              <i class="fa-solid fa-hotel w-5 text-slate-400 group-open:text-amber-600"></i>
              <span class="font-medium">HOTEL MANAGEMENT</span>
              <i class="fa-solid fa-chevron-right dropdown-arrow ml-auto text-xs text-slate-400"></i>
            </summary>
            <div class="ml-6 mt-1 space-y-1 pl-3 border-l-2 border-amber-100">
              <a href="../hotel_management/front_desk_reception.php"
                class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i
                  class="fa-regular fa-reception w-4 text-slate-400"></i> Front Desk / Reception</a>
              <a href="../hotel_management/room_management.php"
                class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i
                  class="fa-solid fa-bed w-4 text-slate-400"></i> Room Management</a>
              <a href="../hotel_management/reservation_&_booking.php"
                class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i
                  class="fa-regular fa-calendar-check w-4 text-slate-400"></i> Reservations & Booking</a>
              <a href="../hotel_management/housekeeping_&_maintenance.php"
                class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i
                  class="fa-solid fa-broom w-4 text-slate-400"></i> Housekeeping & Maintenance</a>
              <a href="../hotel_management/event_&_conference.php"
                class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i
                  class="fa-regular fa-calendar w-4 text-slate-400"></i> Events & Conference</a>
            </div>
          </details>

          <!-- RESTAURANT MANAGEMENT GROUP -->
          <details class="group" open>
            <summary
              class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-700 hover:bg-amber-50 cursor-pointer">
              <i class="fa-solid fa-utensils w-5 text-slate-400 group-open:text-amber-600"></i>
              <span class="font-medium">RESTAURANT MANAGEMENT</span>
              <i class="fa-solid fa-chevron-right dropdown-arrow ml-auto text-xs text-slate-400"></i>
            </summary>
            <div class="ml-6 mt-1 space-y-1 pl-3 border-l-2 border-amber-100">
              <a href="../restaurant_management/table_reservation.php"
                class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i
                  class="fa-regular fa-clock w-4"></i> Table Reservation</a>
              <a href="../restaurant_management/menu_management.php"
                class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i
                  class="fa-solid fa-bars w-4"></i> Menu Management</a>
              <a href="../restaurant_management/orders_pos.php"
                class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i
                  class="fa-solid fa-cash-register w-4"></i> Orders / POS</a>
              <a href="../restaurant_management/kitchen_orders.php"
                class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i
                  class="fa-solid fa-fire w-4"></i> Kitchen Orders (KOT)</a>
              <a href="../restaurant_management/wait_staff_management.php"
                class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i
                  class="fa-regular fa-user w-4"></i> Wait Staff Management</a>
            </div>
          </details>

          <!-- CUSTOMER MANAGEMENT - open with Loyalty & Rewards highlighted -->
          <details class="group" open>
            <summary
              class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-amber-800 bg-amber-50 cursor-pointer transition-side">
              <i class="fa-regular fa-address-book w-5 text-amber-600"></i>
              <span class="font-medium">CUSTOMER MANAGEMENT</span>
              <i class="fa-solid fa-chevron-right dropdown-arrow ml-auto text-xs text-amber-600"></i>
            </summary>
            <div class="ml-6 mt-1 space-y-1 pl-3 border-l-2 border-amber-200">
              <a href="../customer_management/customer_relationship.php"
                class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i
                  class="fa-regular fa-handshake w-4 text-slate-400"></i> Guest Relationship (CRM)</a>
              <a href="../customer_management/loyalty_rewards.php"
                class="flex items-center gap-2 px-4 py-2 rounded-lg bg-amber-100/50 text-amber-700 font-medium"><i
                  class="fa-regular fa-star w-4 text-amber-600"></i> Loyalty & Rewards</a>
              <a href="../customer_management/customer_feedback_&_reviews.php"
                class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i
                  class="fa-regular fa-pen-to-square w-4 text-slate-400"></i> Customer Feedback & Reviews</a>
            </div>
          </details>

          <!-- OPERATIONS -->
          <details class="group" open>
            <summary
              class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-700 hover:bg-amber-50 cursor-pointer">
              <i class="fa-solid fa-gears w-5 text-slate-400 group-open:text-amber-600"></i>
              <span class="font-medium">OPERATIONS</span>
              <i class="fa-solid fa-chevron-right dropdown-arrow ml-auto text-xs text-slate-400"></i>
            </summary>
            <div class="ml-6 mt-1 space-y-1 pl-3 border-l-2 border-amber-100">
              <a href="../operations/inventory_&_stocks.php"
                class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i
                  class="fa-solid fa-boxes w-4"></i> Inventory & Stock</a>
              <a href="../operations/billing_&_payment.php"
                class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i
                  class="fa-regular fa-credit-card w-4"></i> Billing & Payments</a>
              <a href="../operations/payment_gateway.php"
                class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i
                  class="fa-solid fa-wifi w-4"></i> Payment Gateway</a>
            </div>
          </details>

          <!-- MARKETING -->
          <details class="group" open>
            <summary
              class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-700 hover:bg-amber-50 cursor-pointer">
              <i class="fa-solid fa-megaphone w-5 text-slate-400 group-open:text-amber-600"></i>
              <span class="font-medium">MARKETING</span>
              <i class="fa-solid fa-chevron-right dropdown-arrow ml-auto text-xs text-slate-400"></i>
            </summary>
            <div class="ml-6 mt-1 space-y-1 pl-3 border-l-2 border-amber-100">
              <a href="../marketing/hotelmarketing_&_promotions.php"
                class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i
                  class="fa-regular fa-gem w-4"></i> Hotel Marketing & Promotions</a>
              <a href="../marketing/online_ordering_integration.php"
                class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i
                  class="fa-solid fa-cart-shopping w-4"></i> Online Ordering Integration</a>
            </div>
          </details>

          <!-- REPORTS & ANALYTICS -->
          <details class="group" open>
            <summary
              class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-700 hover:bg-amber-50 cursor-pointer">
              <i class="fa-solid fa-chart-simple w-5 text-slate-400 group-open:text-amber-600"></i>
              <span class="font-medium">REPORTS & ANALYTICS</span>
              <i class="fa-solid fa-chevron-right dropdown-arrow ml-auto text-xs text-slate-400"></i>
            </summary>
            <div class="ml-6 mt-1 space-y-1 pl-3 border-l-2 border-amber-100">
              <a href="../reports_&_analytics/sales_report.php"
                class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i
                  class="fa-solid fa-chart-line w-4"></i> Sales Reports</a>
              <a href="../reports_&_analytics/booking_reports.php"
                class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i
                  class="fa-regular fa-calendar w-4"></i> Booking Reports</a>
              <a href="../reports_&_analytics/analytics_dashboard.php"
                class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i
                  class="fa-solid fa-chart-pie w-4"></i> Analytics Dashboard</a>
            </div>
          </details>

          <!-- SYSTEM -->
          <details class="group" open>
            <summary
              class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-700 hover:bg-amber-50 cursor-pointer">
              <i class="fa-solid fa-computer w-5 text-slate-400 group-open:text-amber-600"></i>
              <span class="font-medium">SYSTEM</span>
              <i class="fa-solid fa-chevron-right dropdown-arrow ml-auto text-xs text-slate-400"></i>
            </summary>
            <div class="ml-6 mt-1 space-y-1 pl-3 border-l-2 border-amber-100">
              <a href="../system/channel_management.php"
                class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i
                  class="fa-solid fa-code-branch w-4"></i> Channel Management</a>
              <a href="../system/door_lock_integration.php"
                class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i
                  class="fa-solid fa-lock w-4"></i> Door Lock Integration</a>
              <a href="../system/settings.php"
                class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-amber-50"><i
                  class="fa-solid fa-sliders w-4"></i> Settings</a>
            </div>
          </details>

          <!-- logout -->
          <div class="border-t border-slate-200 pt-3 mt-3">
            <a href="../../controller/auth/logout.php"
              class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-500 hover:bg-red-50 hover:text-red-700">
              <i class="fa-solid fa-arrow-right-from-bracket w-5"></i>
              <span>Logout</span>
            </a>
          </div>
        </nav>
      </aside>

      <!-- ========== MAIN CONTENT (LOYALTY & REWARDS) ========== -->
      <main class="flex-1 p-5 lg:p-8 overflow-y-auto bg-white">

        <!-- header with title and date -->
        <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
          <div>
            <h1 class="text-2xl lg:text-3xl font-light text-slate-800">Loyalty & Rewards</h1>
            <p class="text-sm text-slate-500 mt-0.5">manage loyalty tiers, points, rewards, and member benefits</p>
          </div>
          <div class="flex gap-3 text-sm">
            <span class="bg-white border rounded-full px-4 py-2 flex items-center gap-2 shadow-sm"><i
                class="fa-regular fa-calendar text-slate-400"></i>
              <?php echo $today; ?>
            </span>
            <span class="bg-white border rounded-full px-4 py-2 shadow-sm"><i class="fa-regular fa-bell"></i></span>
          </div>
        </div>

        <!-- ===== STATS CARDS ===== -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
          <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm stat-card">
            <p class="text-xs text-slate-500">Total members</p>
            <p class="text-2xl font-semibold">
              <?php echo number_format($stats['total_members'] ?? 0); ?>
            </p>
          </div>
          <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm stat-card">
            <p class="text-xs text-slate-500">Active this month</p>
            <p class="text-2xl font-semibold">
              <?php echo number_format($stats['active_this_month'] ?? 0); ?>
            </p>
          </div>
          <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm stat-card">
            <p class="text-xs text-slate-500">Points issued</p>
            <p class="text-2xl font-semibold">
              <?php echo number_format(($stats['total_points'] ?? 0) / 1000, 1); ?>k
            </p>
          </div>
          <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm stat-card">
            <p class="text-xs text-slate-500">Rewards redeemed</p>
            <p class="text-2xl font-semibold">
              <?php echo number_format($stats['total_redemptions'] ?? 0); ?>
            </p>
          </div>
          <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm stat-card">
            <p class="text-xs text-slate-500">Conversion rate</p>
            <p class="text-2xl font-semibold">
              <?php echo $stats['conversion_rate'] ?? 0; ?>%
            </p>
          </div>
        </div>

        <!-- ===== TIER OVERVIEW ===== -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
          <div class="bg-slate-50 border border-slate-200 rounded-2xl p-5 text-center">
            <span class="bg-slate-200 text-slate-700 text-xs px-3 py-1 rounded-full">bronze</span>
            <p class="text-2xl font-bold mt-2">
              <?php echo number_format($tiers['bronze']['count']); ?>
            </p>
            <p class="text-xs text-slate-500">members</p>
            <p class="text-xs text-slate-400 mt-1">0-499 pts</p>
          </div>
          <div class="bg-slate-50 border border-slate-200 rounded-2xl p-5 text-center">
            <span class="bg-slate-200 text-slate-700 text-xs px-3 py-1 rounded-full">silver</span>
            <p class="text-2xl font-bold mt-2">
              <?php echo number_format($tiers['silver']['count']); ?>
            </p>
            <p class="text-xs text-slate-500">members</p>
            <p class="text-xs text-slate-400 mt-1">500-999 pts</p>
          </div>
          <div class="bg-amber-50 border border-amber-200 rounded-2xl p-5 text-center">
            <span class="bg-amber-200 text-amber-800 text-xs px-3 py-1 rounded-full">gold</span>
            <p class="text-2xl font-bold mt-2">
              <?php echo number_format($tiers['gold']['count']); ?>
            </p>
            <p class="text-xs text-slate-500">members</p>
            <p class="text-xs text-slate-400 mt-1">1,000-1,999 pts</p>
          </div>
          <div class="bg-purple-50 border border-purple-200 rounded-2xl p-5 text-center">
            <span class="bg-purple-200 text-purple-800 text-xs px-3 py-1 rounded-full">platinum</span>
            <p class="text-2xl font-bold mt-2">
              <?php echo number_format($tiers['platinum']['count']); ?>
            </p>
            <p class="text-xs text-slate-500">members</p>
            <p class="text-xs text-slate-400 mt-1">2,000+ pts</p>
          </div>
        </div>

        <!-- ===== ACTION BAR ===== -->
        <div
          class="bg-white rounded-2xl border border-slate-200 p-4 mb-6 flex flex-wrap gap-3 items-center justify-between">
          <div class="flex gap-2 flex-wrap">
            <button onclick="createReward()"
              class="bg-amber-600 text-white px-4 py-2 rounded-xl text-sm hover:bg-amber-700 transition">+ create
              reward</button>
            <button onclick="adjustPoints()"
              class="border border-slate-200 px-4 py-2 rounded-xl text-sm hover:bg-slate-50 transition">adjust
              points</button>
            <button onclick="tierSettings()"
              class="border border-slate-200 px-4 py-2 rounded-xl text-sm hover:bg-slate-50 transition">tier
              settings</button>
            <button onclick="exportData()"
              class="border border-slate-200 px-4 py-2 rounded-xl text-sm hover:bg-slate-50 transition">export</button>
          </div>
          <div class="relative">
            <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
            <input type="text" id="searchInput" placeholder="search members..."
              class="border border-slate-200 rounded-xl pl-9 pr-4 py-2 text-sm w-64 focus:ring-1 focus:ring-amber-500 outline-none">
          </div>
        </div>

        <!-- ===== TOP MEMBERS TABLE ===== -->
        <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden mb-8">
          <div class="p-4 border-b border-slate-200 bg-slate-50 flex items-center justify-between">
            <h2 class="font-semibold flex items-center gap-2"><i class="fa-regular fa-star text-amber-600"></i> top
              members by points</h2>
            <div class="flex gap-2">
              <button onclick="viewAllMembers()"
                class="text-sm text-amber-700 border border-amber-600 px-3 py-1 rounded-lg hover:bg-amber-50 transition">view
                all</button>
            </div>
          </div>
          <div class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead class="text-slate-500 text-xs border-b">
                <tr>
                  <td class="p-3">Member</td>
                  <td class="p-3">Tier</td>
                  <td class="p-3">Points balance</td>
                  <td class="p-3">Lifetime points</td>
                  <td class="p-3">Last activity</td>
                  <td class="p-3">Actions</td>
                </tr>
              </thead>
              <tbody id="membersTableBody" class="divide-y">
                <?php if (empty($topMembers)): ?>
                  <tr>
                    <td colspan="6" class="p-6 text-center text-slate-400">No members found</td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($topMembers as $index => $member):
                    $colors = ['bg-purple-200', 'bg-amber-200', 'bg-amber-200', 'bg-slate-200', 'bg-slate-200'];
                    $textColors = ['text-purple-800', 'text-amber-800', 'text-amber-800', 'text-slate-600', 'text-slate-600'];
                    $bgColor = $colors[$index % count($colors)];
                    $textColor = $textColors[$index % count($textColors)];
                    $tierColors = [
                      'platinum' => 'bg-purple-100 text-purple-700',
                      'gold' => 'bg-amber-100 text-amber-700',
                      'silver' => 'bg-slate-100 text-slate-600',
                      'bronze' => 'bg-orange-100 text-orange-700'
                    ];
                    $tierClass = $tierColors[strtolower($member['member_tier'])] ?? 'bg-slate-100 text-slate-600';
                    ?>
                    <tr class="member-row" data-name="<?php echo strtolower($member['full_name']); ?>">
                      <td class="p-3">
                        <div class="flex items-center gap-2">
                          <div
                            class="h-8 w-8 rounded-full <?php echo $bgColor; ?> flex items-center justify-center <?php echo $textColor; ?> font-bold text-xs">
                            <?php
                            $name_parts = explode(' ', $member['full_name']);
                            echo strtoupper(substr($name_parts[0], 0, 1) . (isset($name_parts[1]) ? substr($name_parts[1], 0, 1) : ''));
                            ?>
                          </div>
                          <div>
                            <span class="font-medium">
                              <?php echo htmlspecialchars($member['full_name']); ?>
                            </span>
                            <p class="text-xs text-slate-400">ID: #G
                              <?php echo str_pad($member['id'], 5, '0', STR_PAD_LEFT); ?>
                            </p>
                          </div>
                        </div>
                      </td>
                      <td class="p-3"><span class="<?php echo $tierClass; ?> px-2 py-0.5 rounded-full text-xs">
                          <?php echo ucfirst($member['member_tier']); ?>
                        </span></td>
                      <td class="p-3 font-semibold">
                        <?php echo number_format($member['loyalty_points']); ?>
                      </td>
                      <td class="p-3">
                        <?php echo number_format($member['lifetime_points']); ?>
                      </td>
                      <td class="p-3">
                        <?php echo $member['last_activity'] ?? 'Never'; ?>
                      </td>
                      <td class="p-3">
                        <button onclick="viewMember(<?php echo $member['id']; ?>)"
                          class="text-amber-700 text-xs hover:underline mr-2">view</button>
                        <button
                          onclick="adjustMemberPoints(<?php echo $member['id']; ?>, <?php echo $member['loyalty_points']; ?>)"
                          class="text-blue-600 text-xs hover:underline">adjust</button>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
          <div class="p-4 border-t border-slate-200 flex items-center justify-between">
            <span class="text-xs text-slate-500">Showing top
              <?php echo count($topMembers); ?> of
              <?php echo number_format($stats['total_members'] ?? 0); ?> members
            </span>
            <div class="flex gap-2">
              <button
                class="border border-slate-200 px-3 py-1 rounded-lg text-sm hover:bg-slate-50 transition">Previous</button>
              <button class="bg-amber-600 text-white px-3 py-1 rounded-lg text-sm">1</button>
              <button
                class="border border-slate-200 px-3 py-1 rounded-lg text-sm hover:bg-slate-50 transition">2</button>
              <button
                class="border border-slate-200 px-3 py-1 rounded-lg text-sm hover:bg-slate-50 transition">3</button>
              <button
                class="border border-slate-200 px-3 py-1 rounded-lg text-sm hover:bg-slate-50 transition">Next</button>
            </div>
          </div>
        </div>

        <!-- ===== BOTTOM: AVAILABLE REWARDS & RECENT REDEMPTIONS ===== -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

          <!-- available rewards -->
          <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 p-5">
            <div class="flex items-center justify-between mb-3">
              <h2 class="font-semibold text-lg flex items-center gap-2"><i
                  class="fa-regular fa-gift text-amber-600"></i> available rewards</h2>
              <button onclick="manageRewards()" class="text-sm text-amber-700 hover:underline">manage all</button>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <?php foreach ($rewards as $reward): ?>
                <div class="border rounded-xl p-3 flex justify-between items-center hover:shadow-md transition">
                  <div>
                    <p class="font-medium">
                      <?php echo htmlspecialchars($reward['reward_name']); ?>
                    </p>
                    <p class="text-xs text-slate-500">
                      <?php echo number_format($reward['points_cost']); ?> points
                    </p>
                  </div>
                  <span class="bg-green-100 text-green-700 text-xs px-2 py-1 rounded-full">active</span>
                </div>
              <?php endforeach; ?>
            </div>
          </div>

          <!-- recent redemptions -->
          <div class="bg-amber-50 border border-amber-200 rounded-2xl p-5">
            <h3 class="font-semibold flex items-center gap-2 mb-3"><i
                class="fa-regular fa-clock-rotate-left text-amber-600"></i> recent redemptions</h3>
            <ul class="space-y-2 max-h-48 overflow-y-auto">
              <?php if (empty($recentRedemptions)): ?>
                <li class="text-sm text-slate-500 italic">No recent redemptions</li>
              <?php else: ?>
                <?php foreach ($recentRedemptions as $redemption): ?>
                  <li class="flex justify-between items-center border-b border-amber-200 pb-1 last:border-0">
                    <span class="text-sm font-medium">
                      <?php echo htmlspecialchars($redemption['user_name']); ?>
                    </span>
                    <span class="text-xs">
                      <?php echo htmlspecialchars($redemption['reward_name']); ?> ·
                      <?php echo $redemption['points_cost']; ?> pts
                    </span>
                  </li>
                <?php endforeach; ?>
              <?php endif; ?>
            </ul>
          </div>
        </div>
      </main>
    </div>

    <script>
      // Global variables
      let currentPage = <?php echo $currentPage; ?>;
      let totalPages = <?php echo $totalPages; ?>;

      // Search functionality
      document.getElementById('searchInput').addEventListener('keyup', function () {
        const searchTerm = this.value.toLowerCase();
        const rows = document.querySelectorAll('.member-row');

        rows.forEach(row => {
          const name = row.dataset.name;
          row.style.display = name.includes(searchTerm) ? '' : 'none';
        });
      });

      // Pagination
      function changePage(page) {
        if (page < 1 || page > totalPages) return;
        window.location.href = `?page=${page}`;
      }

      // Create Reward
      function createReward() {
        Swal.fire({
          title: 'Create New Reward',
          html: `
        <div class="text-left space-y-3">
          <div>
            <label class="text-sm font-medium">Reward Name *</label>
            <input type="text" id="rewardName" class="w-full border rounded-lg p-2" placeholder="e.g. Free Coffee">
          </div>
          <div>
            <label class="text-sm font-medium">Description</label>
            <textarea id="rewardDesc" class="w-full border rounded-lg p-2" rows="2" placeholder="Describe the reward..."></textarea>
          </div>
          <div>
            <label class="text-sm font-medium">Points Cost *</label>
            <input type="number" id="pointsCost" class="w-full border rounded-lg p-2" placeholder="240">
          </div>
          <div>
            <label class="text-sm font-medium">Category</label>
            <select id="rewardCategory" class="w-full border rounded-lg p-2">
              <option value="beverage">Beverage</option>
              <option value="dining">Dining</option>
              <option value="hotel">Hotel</option>
              <option value="spa">Spa</option>
              <option value="other">Other</option>
            </select>
          </div>
          <div>
            <label class="text-sm font-medium">Stock Limit (optional)</label>
            <input type="number" id="stockLimit" class="w-full border rounded-lg p-2" placeholder="Leave empty for unlimited">
          </div>
        </div>
      `,
          showCancelButton: true,
          confirmButtonColor: '#d97706',
          cancelButtonColor: '#6b7280',
          confirmButtonText: 'Create Reward',
          preConfirm: () => {
            const name = document.getElementById('rewardName').value;
            const points = document.getElementById('pointsCost').value;
            if (!name || !points) {
              Swal.showValidationMessage('Please fill all required fields');
              return false;
            }
            return {
              name: name,
              desc: document.getElementById('rewardDesc').value,
              points: points,
              category: document.getElementById('rewardCategory').value,
              stock: document.getElementById('stockLimit').value
            };
          }
        }).then((result) => {
          if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('action', 'create_reward');
            formData.append('reward_name', result.value.name);
            formData.append('description', result.value.desc);
            formData.append('points_cost', result.value.points);
            formData.append('category', result.value.category);
            formData.append('stock_limit', result.value.stock);

            fetch('../../../controller/admin/post/loyalty_actions.php', {
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
              })
              .catch(error => {
                Swal.fire({
                  title: 'Error',
                  text: 'An error occurred',
                  icon: 'error',
                  confirmButtonColor: '#d97706'
                });
              });
          }
        });
      }

      // Edit Reward
      function editReward(rewardId, name, desc, points, category, isActive, stock) {
        Swal.fire({
          title: 'Edit Reward',
          html: `
        <div class="text-left space-y-3">
          <div>
            <label class="text-sm font-medium">Reward Name *</label>
            <input type="text" id="rewardName" class="w-full border rounded-lg p-2" value="${name.replace(/"/g, '&quot;')}">
          </div>
          <div>
            <label class="text-sm font-medium">Description</label>
            <textarea id="rewardDesc" class="w-full border rounded-lg p-2" rows="2">${desc.replace(/"/g, '&quot;')}</textarea>
          </div>
          <div>
            <label class="text-sm font-medium">Points Cost *</label>
            <input type="number" id="pointsCost" class="w-full border rounded-lg p-2" value="${points}">
          </div>
          <div>
            <label class="text-sm font-medium">Category</label>
            <select id="rewardCategory" class="w-full border rounded-lg p-2">
              <option value="beverage" ${category === 'beverage' ? 'selected' : ''}>Beverage</option>
              <option value="dining" ${category === 'dining' ? 'selected' : ''}>Dining</option>
              <option value="hotel" ${category === 'hotel' ? 'selected' : ''}>Hotel</option>
              <option value="spa" ${category === 'spa' ? 'selected' : ''}>Spa</option>
              <option value="other" ${category === 'other' ? 'selected' : ''}>Other</option>
            </select>
          </div>
          <div>
            <label class="text-sm font-medium">Stock Limit</label>
            <input type="number" id="stockLimit" class="w-full border rounded-lg p-2" value="${stock || ''}" placeholder="Leave empty for unlimited">
          </div>
          <div class="flex items-center gap-2">
            <input type="checkbox" id="isActive" ${isActive == 1 ? 'checked' : ''}>
            <label class="text-sm">Active</label>
          </div>
        </div>
      `,
          showCancelButton: true,
          confirmButtonColor: '#d97706',
          cancelButtonColor: '#6b7280',
          confirmButtonText: 'Update Reward',
          preConfirm: () => {
            const name = document.getElementById('rewardName').value;
            const points = document.getElementById('pointsCost').value;
            if (!name || !points) {
              Swal.showValidationMessage('Please fill all required fields');
              return false;
            }
            return {
              name: name,
              desc: document.getElementById('rewardDesc').value,
              points: points,
              category: document.getElementById('rewardCategory').value,
              stock: document.getElementById('stockLimit').value,
              active: document.getElementById('isActive').checked ? 1 : 0
            };
          }
        }).then((result) => {
          if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('action', 'update_reward');
            formData.append('reward_id', rewardId);
            formData.append('reward_name', result.value.name);
            formData.append('description', result.value.desc);
            formData.append('points_cost', result.value.points);
            formData.append('category', result.value.category);
            formData.append('stock_limit', result.value.stock);
            formData.append('is_active', result.value.active);

            fetch('../../../controller/admin/post/loyalty_actions.php', {
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

      // Delete Reward
      function deleteReward(rewardId) {
        Swal.fire({
          title: 'Delete Reward?',
          text: 'Are you sure you want to delete this reward?',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#d97706',
          cancelButtonColor: '#6b7280',
          confirmButtonText: 'Yes, delete it'
        }).then((result) => {
          if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('action', 'delete_reward');
            formData.append('reward_id', rewardId);

            fetch('../../../controller/admin/post/loyalty_actions.php', {
              method: 'POST',
              body: formData
            })
              .then(response => response.json())
              .then(data => {
                if (data.success) {
                  Swal.fire({
                    title: 'Deleted!',
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

      // Adjust Points
      function adjustPoints() {
        Swal.fire({
          title: 'Adjust Member Points',
          html: `
        <div class="text-left space-y-3">
          <div>
            <label class="text-sm font-medium">Select Member</label>
            <select id="memberSelect" class="w-full border rounded-lg p-2">
              <option value="">Select member...</option>
              <?php foreach ($topMembers as $member): ?>
                                <option value="<?php echo $member['id']; ?>" data-points="<?php echo $member['loyalty_points']; ?>">
                                  <?php echo addslashes($member['full_name']); ?> (<?php echo $member['loyalty_points']; ?> pts)
                                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label class="text-sm font-medium">Points</label>
            <input type="number" id="pointsAmount" class="w-full border rounded-lg p-2" min="1">
          </div>
          <div>
            <label class="text-sm font-medium">Action</label>
            <select id="adjustType" class="w-full border rounded-lg p-2">
              <option value="add">Add Points</option>
              <option value="subtract">Subtract Points</option>
            </select>
          </div>
          <div>
            <label class="text-sm font-medium">Reason (optional)</label>
            <input type="text" id="adjustReason" class="w-full border rounded-lg p-2" placeholder="e.g. Complaint resolution">
          </div>
        </div>
      `,
          showCancelButton: true,
          confirmButtonColor: '#d97706',
          cancelButtonColor: '#6b7280',
          confirmButtonText: 'Adjust Points',
          preConfirm: () => {
            const memberId = document.getElementById('memberSelect').value;
            const points = document.getElementById('pointsAmount').value;
            if (!memberId || !points) {
              Swal.showValidationMessage('Please select member and enter points');
              return false;
            }
            return {
              user_id: memberId,
              points: points,
              type: document.getElementById('adjustType').value,
              reason: document.getElementById('adjustReason').value
            };
          }
        }).then((result) => {
          if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('action', 'adjust_points');
            formData.append('user_id', result.value.user_id);
            formData.append('points', result.value.points);
            formData.append('type', result.value.type);
            formData.append('reason', result.value.reason);

            fetch('../../../controller/admin/post/loyalty_actions.php', {
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

      // Adjust specific member points
      function adjustMemberPoints(userId, currentPoints) {
        Swal.fire({
          title: 'Adjust Points',
          html: `
        <div class="text-left space-y-3">
          <p>Current points: <strong>${currentPoints}</strong></p>
          <div>
            <label class="text-sm font-medium">Points</label>
            <input type="number" id="pointsAmount" class="w-full border rounded-lg p-2" min="1">
          </div>
          <div>
            <label class="text-sm font-medium">Action</label>
            <select id="adjustType" class="w-full border rounded-lg p-2">
              <option value="add">Add Points</option>
              <option value="subtract">Subtract Points</option>
            </select>
          </div>
          <div>
            <label class="text-sm font-medium">Reason (optional)</label>
            <input type="text" id="adjustReason" class="w-full border rounded-lg p-2" placeholder="e.g. Complaint resolution">
          </div>
        </div>
      `,
          showCancelButton: true,
          confirmButtonColor: '#d97706',
          cancelButtonColor: '#6b7280',
          confirmButtonText: 'Adjust',
          preConfirm: () => {
            const points = document.getElementById('pointsAmount').value;
            if (!points) {
              Swal.showValidationMessage('Please enter points amount');
              return false;
            }
            return {
              points: points,
              type: document.getElementById('adjustType').value,
              reason: document.getElementById('adjustReason').value
            };
          }
        }).then((result) => {
          if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('action', 'adjust_points');
            formData.append('user_id', userId);
            formData.append('points', result.value.points);
            formData.append('type', result.value.type);
            formData.append('reason', result.value.reason);

            fetch('../../../controller/admin/post/loyalty_actions.php', {
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

      // Tier Settings
      function tierSettings() {
        Swal.fire({
          title: 'Tier Settings',
          html: `
        <div class="text-left space-y-3">
          <div>
            <label class="text-sm font-medium">Bronze threshold</label>
            <input type="number" id="bronze" class="w-full border rounded-lg p-2" value="0" min="0">
          </div>
          <div>
            <label class="text-sm font-medium">Silver threshold</label>
            <input type="number" id="silver" class="w-full border rounded-lg p-2" value="500" min="0">
          </div>
          <div>
            <label class="text-sm font-medium">Gold threshold</label>
            <input type="number" id="gold" class="w-full border rounded-lg p-2" value="1000" min="0">
          </div>
          <div>
            <label class="text-sm font-medium">Platinum threshold</label>
            <input type="number" id="platinum" class="w-full border rounded-lg p-2" value="2000" min="0">
          </div>
        </div>
      `,
          showCancelButton: true,
          confirmButtonColor: '#d97706',
          cancelButtonColor: '#6b7280',
          confirmButtonText: 'Save Settings',
          preConfirm: () => {
            return {
              bronze: document.getElementById('bronze').value,
              silver: document.getElementById('silver').value,
              gold: document.getElementById('gold').value,
              platinum: document.getElementById('platinum').value
            };
          }
        }).then((result) => {
          if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('action', 'update_tiers');
            formData.append('bronze', result.value.bronze);
            formData.append('silver', result.value.silver);
            formData.append('gold', result.value.gold);
            formData.append('platinum', result.value.platinum);

            fetch('../../../controller/admin/post/loyalty_actions.php', {
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

      // Export Data
      function exportData() {
        Swal.fire({
          title: 'Export Data',
          html: `
        <div class="text-left space-y-3">
          <div>
            <label class="text-sm font-medium">Export Type</label>
            <select id="exportType" class="w-full border rounded-lg p-2">
              <option value="members">Members</option>
              <option value="rewards">Rewards</option>
              <option value="redemptions">Redemptions</option>
            </select>
          </div>
          <div>
            <label class="text-sm font-medium">Format</label>
            <select id="exportFormat" class="w-full border rounded-lg p-2">
              <option value="csv">CSV</option>
              <option value="json">JSON</option>
            </select>
          </div>
        </div>
      `,
          showCancelButton: true,
          confirmButtonColor: '#d97706',
          cancelButtonColor: '#6b7280',
          confirmButtonText: 'Export',
          preConfirm: () => {
            return {
              type: document.getElementById('exportType').value,
              format: document.getElementById('exportFormat').value
            };
          }
        }).then((result) => {
          if (result.isConfirmed) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '../../controller/admin/post/loyalty_actions.php';
            form.innerHTML = `
          <input type="hidden" name="action" value="export_data">
          <input type="hidden" name="export_type" value="${result.value.type}">
          <input type="hidden" name="format" value="${result.value.format}">
        `;
            document.body.appendChild(form);
            form.submit();
            document.body.removeChild(form);
          }
        });
      }

      // View Member Details
      function viewMember(userId) {
        const formData = new FormData();
        formData.append('action', 'get_member');
        formData.append('user_id', userId);

        fetch('../../../controller/admin/post/loyalty_actions.php', {
          method: 'POST',
          body: formData
        })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              let redemptionsHtml = '';
              if (data.redemptions.length > 0) {
                data.redemptions.forEach(r => {
                  redemptionsHtml += `<li class="text-sm border-b pb-1">${r.reward_name} - ${r.points_cost} pts (${new Date(r.created_at).toLocaleDateString()})</li>`;
                });
              } else {
                redemptionsHtml = '<li class="text-sm text-slate-500">No redemptions yet</li>';
              }

              Swal.fire({
                title: 'Member Details',
                html: `
            <div class="text-left">
              <p><strong>Name:</strong> ${data.member.full_name}</p>
              <p><strong>Email:</strong> ${data.member.email}</p>
              <p><strong>Phone:</strong> ${data.member.phone || 'N/A'}</p>
              <p><strong>Points:</strong> ${data.member.loyalty_points}</p>
              <p><strong>Tier:</strong> ${data.member.member_tier}</p>
              <p><strong>Member since:</strong> ${new Date(data.member.created_at).toLocaleDateString()}</p>
              <p><strong>Last login:</strong> ${data.member.last_login ? new Date(data.member.last_login).toLocaleDateString() : 'Never'}</p>
              
              <div class="mt-4">
                <p class="font-medium">Recent Redemptions:</p>
                <ul class="list-disc pl-4 mt-1">
                  ${redemptionsHtml}
                </ul>
              </div>
            </div>
          `,
                confirmButtonColor: '#d97706',
                confirmButtonText: 'Close'
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

      // Manage All Rewards
      function manageRewards() {
        const formData = new FormData();
        formData.append('action', 'get_rewards');

        fetch('../../../controller/admin/post/loyalty_actions.php', {
          method: 'POST',
          body: formData
        })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              let rewardsHtml = '';
              data.rewards.forEach(r => {
                rewardsHtml += `
            <div class="border rounded-lg p-3 mb-2 flex justify-between items-center">
              <div>
                <p class="font-medium">${r.reward_name}</p>
                <p class="text-xs text-slate-500">${r.points_cost} pts · ${r.category}</p>
              </div>
              <div class="flex gap-2">
                <button onclick="editReward(${r.id}, '${r.reward_name.replace(/'/g, "\\'")}', '${r.description ? r.description.replace(/'/g, "\\'") : ''}', ${r.points_cost}, '${r.category}', ${r.is_active}, ${r.stock_limit || 'null'})" class="text-blue-600 hover:underline text-xs">Edit</button>
                <button onclick="deleteReward(${r.id})" class="text-red-600 hover:underline text-xs">Delete</button>
              </div>
            </div>
          `;
              });

              Swal.fire({
                title: 'Manage Rewards',
                html: `
            <div class="max-h-96 overflow-y-auto">
              ${rewardsHtml}
            </div>
            <button onclick="createReward()" class="mt-3 bg-amber-600 text-white px-4 py-2 rounded-lg text-sm w-full">+ Create New Reward</button>
          `,
                showConfirmButton: false,
                showCloseButton: true
              });
            }
          });
      }

      // View All Members
      function viewAllMembers() {
        window.location.href = '../customer_management/customer_relationship.php';
      }
    </script>
  </body>

</html>