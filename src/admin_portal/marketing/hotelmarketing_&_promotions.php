<?php
/**
 * View - Admin Hotel Marketing & Promotions
 */
require_once '../../../controller/admin/get/marketing_get.php';

$current_page = 'hotel_marketing';
?>
<!DOCTYPE html>
<html lang="en">

  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin · Hotel Marketing & Promotions</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        max-width: 800px;
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
        .toast.show { transform: translateX(0); }
        .campaign-card {
            transition: all 0.2s ease;
        }
        .campaign-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px -12px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body class="bg-white font-sans antialiased">

    <!-- Toast Notification -->
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

    <!-- APP CONTAINER -->
    <div class="min-h-screen flex flex-col lg:flex-row">

        <!-- ========== SIDEBAR ========== -->
        <?php require_once '../components/admin_nav.php'; ?>

        <!-- ========== MAIN CONTENT ========== -->
        <main class="flex-1 p-5 lg:p-8 overflow-y-auto bg-white">

            <!-- header -->
            <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
                <div>
                    <h1 class="text-2xl lg:text-3xl font-light text-slate-800">Hotel Marketing & Promotions</h1>
                    <p class="text-sm text-slate-500 mt-0.5">manage campaigns, discounts, and promotional offers</p>
                </div>
                <div class="flex gap-3 text-sm">
                    <span class="bg-white border rounded-full px-4 py-2 flex items-center gap-2 shadow-sm">
                        <i class="fas fa-calendar text-slate-400"></i> <?php echo $today; ?>
                    </span>
                    <span class="bg-white border rounded-full px-4 py-2 shadow-sm cursor-pointer hover:bg-slate-50 relative" id="notificationBell">
                        <i class="fa-regular fa-bell"></i>
                        <?php if ($unread_count > 0): ?>
                              <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-4 h-4 flex items-center justify-center"><?php echo $unread_count; ?></span>
                        <?php endif; ?>
                    </span>
                </div>
            </div>

            <!-- STATS CARDS -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm cursor-pointer hover:shadow-md transition" onclick="openAnalyticsModal()">
                    <p class="text-xs text-slate-500">Active campaigns</p>
                    <p class="text-2xl font-semibold"><?php echo $stats['active_campaigns']; ?></p>
                </div>
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm cursor-pointer hover:shadow-md transition" onclick="openAnalyticsModal()">
                    <p class="text-xs text-slate-500">Total revenue</p>
                    <p class="text-2xl font-semibold text-green-600">₱<?php echo number_format($stats['total_revenue']); ?></p>
                    <p class="text-xs <?php echo $stats['revenue_change'] >= 0 ? 'text-green-500' : 'text-red-500'; ?> mt-1">
                        <?php echo $stats['revenue_change'] >= 0 ? '↑' : '↓'; ?> <?php echo abs($stats['revenue_change']); ?>% vs last period
                    </p>
                </div>
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm cursor-pointer hover:shadow-md transition" onclick="openAnalyticsModal()">
                    <p class="text-xs text-slate-500">Redemptions</p>
                    <p class="text-2xl font-semibold"><?php echo number_format($stats['total_redemptions']); ?></p>
                </div>
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm cursor-pointer hover:shadow-md transition" onclick="openAnalyticsModal()">
                    <p class="text-xs text-slate-500">Conversion rate</p>
                    <p class="text-2xl font-semibold"><?php echo $stats['conversion_rate']; ?>%</p>
                </div>
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm cursor-pointer hover:shadow-md transition" onclick="openAnalyticsModal()">
                    <p class="text-xs text-slate-500">ROI</p>
                    <p class="text-2xl font-semibold <?php echo $stats['roi'] >= 0 ? 'text-green-600' : 'text-red-600'; ?>">
                        <?php echo $stats['roi']; ?>%
                    </p>
                </div>
            </div>

            <!-- ACTION BAR -->
            <div class="bg-white rounded-2xl border border-slate-200 p-4 mb-6 flex flex-wrap gap-3 items-center justify-between">
                <div class="flex gap-2 flex-wrap">
                    <button class="bg-amber-600 text-white px-4 py-2 rounded-xl text-sm hover:bg-amber-700" onclick="openCampaignModal()">
                        <i class="fas fa-plus mr-1"></i> new campaign
                    </button>
                    <button class="border border-slate-200 px-4 py-2 rounded-xl text-sm hover:bg-slate-50" onclick="openPromoModal()">
                        <i class="fas fa-ticket mr-1"></i> create promo
                    </button>
                    <button class="border border-slate-200 px-4 py-2 rounded-xl text-sm hover:bg-slate-50" onclick="openEmailBlastModal()">
                        <i class="fas fa-envelope mr-1"></i> email blast
                    </button>
                    <button class="border border-slate-200 px-4 py-2 rounded-xl text-sm hover:bg-slate-50" onclick="openAnalyticsModal()">
                        <i class="fas fa-chart-line mr-1"></i> analytics
                    </button>
                </div>
                <div class="relative">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    <input type="text" id="searchInput" placeholder="search campaigns..." value="<?php echo htmlspecialchars($searchFilter); ?>"
                        class="border border-slate-200 rounded-xl pl-9 pr-4 py-2 text-sm w-64 focus:ring-1 focus:ring-amber-500 outline-none">
                </div>
            </div>

            <!-- FILTER TABS -->
            <div class="flex flex-wrap gap-2 border-b border-slate-200 pb-2 mb-6">
                <button data-status="all" class="status-filter <?php echo $statusFilter == 'all' ? 'bg-amber-600 text-white' : 'bg-white border border-slate-200 hover:bg-slate-50'; ?> px-4 py-2 rounded-full text-sm">all campaigns</button>
                <button data-status="active" class="status-filter <?php echo $statusFilter == 'active' ? 'bg-amber-600 text-white' : 'bg-white border border-slate-200 hover:bg-slate-50'; ?> px-4 py-2 rounded-full text-sm">active</button>
                <button data-status="scheduled" class="status-filter <?php echo $statusFilter == 'scheduled' ? 'bg-amber-600 text-white' : 'bg-white border border-slate-200 hover:bg-slate-50'; ?> px-4 py-2 rounded-full text-sm">scheduled</button>
                <button data-status="ended" class="status-filter <?php echo $statusFilter == 'ended' ? 'bg-amber-600 text-white' : 'bg-white border border-slate-200 hover:bg-slate-50'; ?> px-4 py-2 rounded-full text-sm">ended</button>
                <button data-status="draft" class="status-filter <?php echo $statusFilter == 'draft' ? 'bg-amber-600 text-white' : 'bg-white border border-slate-200 hover:bg-slate-50'; ?> px-4 py-2 rounded-full text-sm">drafts</button>
            </div>

            <!-- CAMPAIGNS GRID -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 mb-8" id="campaignsGrid">
                <?php foreach ($campaigns as $campaign): ?>
                      <?php
                      $statusColors = [
                        'active' => 'bg-green-100 text-green-700',
                        'scheduled' => 'bg-blue-100 text-blue-700',
                        'draft' => 'bg-slate-100 text-slate-600',
                        'ended' => 'bg-slate-100 text-slate-600',
                        'cancelled' => 'bg-red-100 text-red-700'
                      ];
                      $statusColor = $statusColors[$campaign['status']] ?? 'bg-slate-100 text-slate-600';

                      $discountText = '';
                      if ($campaign['discount_percent']) {
                        $discountText = $campaign['discount_percent'] . '% off';
                      } elseif ($campaign['discount_amount']) {
                        $discountText = '₱' . number_format($campaign['discount_amount']) . ' off';
                      }

                      $redemptionPercent = $campaign['redemption_limit'] > 0 ? round(($campaign['redemptions_count'] / $campaign['redemption_limit']) * 100) : 0;
                      $endDate = new DateTime($campaign['end_date']);
                      $todayDate = new DateTime();
                      $daysLeft = $todayDate <= $endDate ? $todayDate->diff($endDate)->days : 0;
                      ?>
                      <div class="campaign-card bg-white rounded-2xl border border-slate-200 p-5 hover:shadow-md transition" data-campaign-id="<?php echo $campaign['id']; ?>">
                          <div class="flex justify-between items-start mb-2">
                              <span class="<?php echo $statusColor; ?> text-xs px-2 py-0.5 rounded-full"><?php echo ucfirst($campaign['status']); ?></span>
                              <?php if ($campaign['status'] === 'active' && $daysLeft > 0): ?>
                                    <span class="text-xs text-slate-400">ends in <?php echo $daysLeft; ?> days</span>
                              <?php elseif ($campaign['status'] === 'scheduled'): ?>
                                    <span class="text-xs text-slate-400">starts <?php echo date('M d', strtotime($campaign['start_date'])); ?></span>
                              <?php elseif ($campaign['status'] === 'ended'): ?>
                                    <span class="text-xs text-slate-400">ended <?php echo date('M d', strtotime($campaign['end_date'])); ?></span>
                              <?php endif; ?>
                          </div>
                          <h3 class="font-semibold text-lg mt-1"><?php echo htmlspecialchars($campaign['campaign_name']); ?></h3>
                          <p class="text-xs text-slate-500 mb-3">
                              <?php echo htmlspecialchars($discountText ?: substr($campaign['description'], 0, 50)); ?>
                          </p>
                          <div class="space-y-2 text-sm">
                              <?php if ($campaign['redemption_limit'] > 0): ?>
                                    <div>
                                        <div class="flex justify-between text-xs text-slate-500 mb-1">
                                            <span>Redemptions</span>
                                            <span><?php echo number_format($campaign['redemptions_count']); ?> / <?php echo number_format($campaign['redemption_limit']); ?></span>
                                        </div>
                                        <div class="w-full bg-slate-100 rounded-full h-1.5">
                                            <div class="bg-amber-500 h-1.5 rounded-full" style="width: <?php echo $redemptionPercent; ?>%"></div>
                                        </div>
                                    </div>
                              <?php endif; ?>
                              <div class="flex justify-between">
                                  <span class="text-slate-500">Revenue generated</span>
                                  <span class="font-medium">₱<?php echo number_format($campaign['revenue_generated']); ?></span>
                              </div>
                          </div>
                          <div class="flex gap-2 mt-4">
                              <button onclick="openCampaignModal(<?php echo $campaign['id']; ?>)" class="flex-1 border border-amber-600 text-amber-700 py-2 rounded-xl text-sm hover:bg-amber-50">edit</button>
                              <button onclick="viewCampaignDetails(<?php echo $campaign['id']; ?>)" class="flex-1 bg-amber-600 text-white py-2 rounded-xl text-sm hover:bg-amber-700">view</button>
                          </div>
                      </div>
                <?php endforeach; ?>
                <?php if (empty($campaigns)): ?>
                      <div class="col-span-full text-center py-12 text-slate-500">
                          <i class="fas fa-megaphone text-4xl mb-3 text-slate-300"></i>
                          <p>No campaigns found</p>
                          <button onclick="openCampaignModal()" class="mt-3 text-amber-600 hover:underline">Create your first campaign</button>
                      </div>
                <?php endif; ?>
            </div>

            <!-- BOTTOM: PROMO CODES & PERFORMANCE -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- active promo codes -->
                <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 p-5">
                    <h2 class="font-semibold text-lg flex items-center gap-2 mb-3"><i class="fas fa-ticket text-amber-600"></i> active promo codes</h2>
                    <div class="space-y-3" id="promoCodesList">
                        <?php foreach ($promoCodes as $code): ?>
                              <div class="flex justify-between items-center border-b pb-2">
                                  <div>
                                      <span class="font-medium font-mono"><?php echo htmlspecialchars($code['code']); ?></span>
                                      <p class="text-xs text-slate-500"><?php echo htmlspecialchars($code['description'] ?: ($code['campaign_name'] ?? 'General promo')); ?></p>
                                  </div>
                                  <div class="flex items-center gap-3">
                                      <span class="bg-green-100 text-green-700 text-xs px-2 py-0.5 rounded-full"><?php echo $code['used_count']; ?> used</span>
                                      <button onclick="copyToClipboard('<?php echo $code['code']; ?>')" class="text-amber-600 hover:text-amber-800">
                                          <i class="fas fa-copy"></i>
                                      </button>
                                  </div>
                              </div>
                        <?php endforeach; ?>
                        <?php if (empty($promoCodes)): ?>
                              <p class="text-sm text-slate-500 text-center py-4">No active promo codes</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- quick stats -->
                <div class="bg-amber-50 border border-amber-200 rounded-2xl p-5">
                    <h3 class="font-semibold flex items-center gap-2 mb-3"><i class="fas fa-chart-bar text-amber-600"></i> quick stats</h3>
                    <ul class="space-y-3">
                        <li class="flex justify-between items-center">
                            <span>Total campaigns</span>
                            <span class="font-semibold"><?php echo $stats['total_campaigns']; ?></span>
                        </li>
                        <li class="flex justify-between items-center">
                            <span>Total budget</span>
                            <span class="font-semibold">₱<?php echo number_format($stats['total_budget']); ?></span>
                        </li>
                        <li class="flex justify-between items-center">
                            <span>Total revenue</span>
                            <span class="font-semibold text-green-600">₱<?php echo number_format($stats['total_revenue']); ?></span>
                        </li>
                        <li class="flex justify-between items-center">
                            <span>Net profit</span>
                            <span class="font-semibold <?php echo ($stats['total_revenue'] - $stats['total_budget']) >= 0 ? 'text-green-600' : 'text-red-600'; ?>">
                                ₱<?php echo number_format($stats['total_revenue'] - $stats['total_budget']); ?>
                            </span>
                        </li>
                    </ul>
                </div>
            </div>
        </main>
    </div>

    <!-- Analytics Modal -->
    <div id="analyticsModal" class="modal">
        <div class="modal-content p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-semibold">Campaign Analytics Dashboard</h3>
                <button onclick="closeAnalyticsModal()" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark text-2xl"></i></button>
            </div>
            <div class="space-y-6">
                <!-- Summary Stats -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="bg-slate-50 rounded-xl p-3 text-center">
                        <p class="text-xs text-slate-500">Total Revenue</p>
                        <p class="text-xl font-bold text-green-600">₱<?php echo number_format($stats['total_revenue']); ?></p>
                    </div>
                    <div class="bg-slate-50 rounded-xl p-3 text-center">
                        <p class="text-xs text-slate-500">Total Budget</p>
                        <p class="text-xl font-bold text-amber-600">₱<?php echo number_format($stats['total_budget']); ?></p>
                    </div>
                    <div class="bg-slate-50 rounded-xl p-3 text-center">
                        <p class="text-xs text-slate-500">Net Profit</p>
                        <p class="text-xl font-bold <?php echo ($stats['total_revenue'] - $stats['total_budget']) >= 0 ? 'text-green-600' : 'text-red-600'; ?>">
                            ₱<?php echo number_format($stats['total_revenue'] - $stats['total_budget']); ?>
                        </p>
                    </div>
                    <div class="bg-slate-50 rounded-xl p-3 text-center">
                        <p class="text-xs text-slate-500">ROI</p>
                        <p class="text-xl font-bold <?php echo $stats['roi'] >= 0 ? 'text-green-600' : 'text-red-600'; ?>">
                            <?php echo $stats['roi']; ?>%
                        </p>
                    </div>
                </div>

                <!-- Charts -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h4 class="font-semibold mb-2">Campaign Status Distribution</h4>
                        <canvas id="statusChart" width="300" height="200"></canvas>
                    </div>
                    <div>
                        <h4 class="font-semibold mb-2">Revenue vs Budget</h4>
                        <canvas id="revenueChart" width="300" height="200"></canvas>
                    </div>
                </div>

                <!-- Top Campaigns -->
                <div>
                    <h4 class="font-semibold mb-2">Top Performing Campaigns</h4>
                    <div class="space-y-2 max-h-60 overflow-y-auto">
                        <?php
                        $topCampaigns = array_slice(array_filter($campaigns, function ($c) {
                          return $c['revenue_generated'] > 0;
                        }), 0, 5);
                        usort($topCampaigns, function ($a, $b) {
                          return $b['revenue_generated'] <=> $a['revenue_generated'];
                        });
                        ?>
                        <?php foreach ($topCampaigns as $campaign): ?>
                              <div class="flex justify-between items-center border-b pb-2">
                                  <div>
                                      <span class="font-medium"><?php echo htmlspecialchars($campaign['campaign_name']); ?></span>
                                      <p class="text-xs text-slate-500"><?php echo $campaign['redemptions_count']; ?> redemptions</p>
                                  </div>
                                  <div class="text-right">
                                      <span class="font-semibold text-green-600">₱<?php echo number_format($campaign['revenue_generated']); ?></span>
                                  </div>
                              </div>
                        <?php endforeach; ?>
                        <?php if (empty($topCampaigns)): ?>
                              <p class="text-sm text-slate-500 text-center py-4">No revenue data yet</p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button onclick="closeAnalyticsModal()" class="px-4 py-2 border border-slate-200 rounded-xl hover:bg-slate-50">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Campaign Details Modal -->
    <div id="campaignDetailsModal" class="modal">
        <div class="modal-content p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-semibold" id="campaignDetailsTitle">Campaign Details</h3>
                <button onclick="closeCampaignDetailsModal()" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark text-2xl"></i></button>
            </div>
            <div id="campaignDetailsContent">
                <!-- Dynamic content -->
            </div>
            <div class="flex gap-3 mt-6">
                <button onclick="closeCampaignDetailsModal()" class="flex-1 border border-slate-200 px-4 py-2 rounded-xl hover:bg-slate-50">Close</button>
                <button id="editCampaignBtn" onclick="" class="flex-1 bg-amber-600 text-white px-4 py-2 rounded-xl hover:bg-amber-700">Edit Campaign</button>
            </div>
        </div>
    </div>

    <!-- Campaign Modal -->
    <div id="campaignModal" class="modal">
        <div class="modal-content p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-semibold" id="campaignModalTitle">Create New Campaign</h3>
                <button onclick="closeCampaignModal()" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark text-2xl"></i></button>
            </div>
            <form id="campaignForm">
                <input type="hidden" id="campaignId" value="0">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm text-slate-600 mb-1">Campaign Name *</label>
                        <input type="text" id="campaignName" class="w-full border rounded-xl p-2" required>
                    </div>
                    <div>
                        <label class="block text-sm text-slate-600 mb-1">Campaign Type</label>
                        <select id="campaignType" class="w-full border rounded-xl p-2">
                            <option value="discount">Discount</option>
                            <option value="package">Package Deal</option>
                            <option value="event">Event</option>
                            <option value="seasonal">Seasonal</option>
                            <option value="flash_sale">Flash Sale</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm text-slate-600 mb-1">Description</label>
                        <textarea id="campaignDescription" rows="2" class="w-full border rounded-xl p-2"></textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm text-slate-600 mb-1">Discount % (optional)</label>
                            <input type="number" id="campaignDiscountPercent" class="w-full border rounded-xl p-2" step="0.01" min="0" max="100">
                        </div>
                        <div>
                            <label class="block text-sm text-slate-600 mb-1">Fixed Amount (optional)</label>
                            <input type="number" id="campaignDiscountAmount" class="w-full border rounded-xl p-2" step="0.01" min="0">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm text-slate-600 mb-1">Start Date *</label>
                            <input type="datetime-local" id="campaignStartDate" class="w-full border rounded-xl p-2" required>
                        </div>
                        <div>
                            <label class="block text-sm text-slate-600 mb-1">End Date *</label>
                            <input type="datetime-local" id="campaignEndDate" class="w-full border rounded-xl p-2" required>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm text-slate-600 mb-1">Status</label>
                            <select id="campaignStatus" class="w-full border rounded-xl p-2">
                                <option value="draft">Draft</option>
                                <option value="scheduled">Scheduled</option>
                                <option value="active">Active</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm text-slate-600 mb-1">Target Audience</label>
                            <select id="campaignTarget" class="w-full border rounded-xl p-2">
                                <option value="all">All Customers</option>
                                <option value="members">Members Only</option>
                                <option value="vip">VIP Members</option>
                                <option value="loyalty">Loyalty Program</option>
                                <option value="new">New Customers</option>
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm text-slate-600 mb-1">Redemption Limit</label>
                            <input type="number" id="campaignRedemptionLimit" class="w-full border rounded-xl p-2" min="0" placeholder="Unlimited">
                        </div>
                        <div>
                            <label class="block text-sm text-slate-600 mb-1">Budget (optional)</label>
                            <input type="number" id="campaignBudget" class="w-full border rounded-xl p-2" step="0.01" min="0">
                        </div>
                    </div>
                </div>
                <div class="flex gap-3 mt-6">
                    <button type="button" onclick="closeCampaignModal()" class="flex-1 border border-slate-200 px-4 py-2 rounded-xl hover:bg-slate-50">Cancel</button>
                    <button type="submit" class="flex-1 bg-amber-600 text-white px-4 py-2 rounded-xl hover:bg-amber-700">Save Campaign</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Promo Code Modal -->
    <div id="promoModal" class="modal">
        <div class="modal-content p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-semibold">Create Promo Code</h3>
                <button onclick="closePromoModal()" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark text-2xl"></i></button>
            </div>
            <form id="promoForm">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm text-slate-600 mb-1">Promo Code *</label>
                        <input type="text" id="promoCode" class="w-full border rounded-xl p-2 uppercase" placeholder="e.g., SUMMER20" required>
                    </div>
                    <div>
                        <label class="block text-sm text-slate-600 mb-1">Campaign (Optional)</label>
                        <select id="promoCampaignId" class="w-full border rounded-xl p-2">
                            <option value="">None (General Promo)</option>
                            <?php foreach ($campaigns as $campaign): ?>
                                  <option value="<?php echo $campaign['id']; ?>"><?php echo htmlspecialchars($campaign['campaign_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm text-slate-600 mb-1">Description</label>
                        <input type="text" id="promoDescription" class="w-full border rounded-xl p-2" placeholder="e.g., 20% off deluxe rooms">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm text-slate-600 mb-1">Discount Type</label>
                            <select id="promoDiscountType" class="w-full border rounded-xl p-2">
                                <option value="percentage">Percentage (%)</option>
                                <option value="fixed">Fixed Amount (₱)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm text-slate-600 mb-1">Discount Value *</label>
                            <input type="number" id="promoDiscountValue" class="w-full border rounded-xl p-2" step="0.01" min="0" required>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm text-slate-600 mb-1">Min. Purchase (optional)</label>
                            <input type="number" id="promoMinPurchase" class="w-full border rounded-xl p-2" step="0.01" min="0">
                        </div>
                        <div>
                            <label class="block text-sm text-slate-600 mb-1">Max Discount (optional)</label>
                            <input type="number" id="promoMaxDiscount" class="w-full border rounded-xl p-2" step="0.01" min="0">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm text-slate-600 mb-1">Valid From *</label>
                            <input type="datetime-local" id="promoValidFrom" class="w-full border rounded-xl p-2" required>
                        </div>
                        <div>
                            <label class="block text-sm text-slate-600 mb-1">Valid To *</label>
                            <input type="datetime-local" id="promoValidTo" class="w-full border rounded-xl p-2" required>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm text-slate-600 mb-1">Usage Limit</label>
                            <input type="number" id="promoUsageLimit" class="w-full border rounded-xl p-2" min="0" placeholder="Unlimited">
                        </div>
                        <div>
                            <label class="block text-sm text-slate-600 mb-1">Per User Limit</label>
                            <input type="number" id="promoPerUserLimit" class="w-full border rounded-xl p-2" min="1" value="1">
                        </div>
                    </div>
                </div>
                <div class="flex gap-3 mt-6">
                    <button type="button" onclick="closePromoModal()" class="flex-1 border border-slate-200 px-4 py-2 rounded-xl hover:bg-slate-50">Cancel</button>
                    <button type="submit" class="flex-1 bg-amber-600 text-white px-4 py-2 rounded-xl hover:bg-amber-700">Create Promo</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Email Blast Modal -->
    <div id="emailBlastModal" class="modal">
        <div class="modal-content p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-semibold">Send Email Blast</h3>
                <button onclick="closeEmailBlastModal()" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark text-2xl"></i></button>
            </div>
            <form id="emailBlastForm">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm text-slate-600 mb-1">Select Template (Optional)</label>
                        <select id="emailTemplate" class="w-full border rounded-xl p-2">
                            <option value="">Custom Email</option>
                            <?php foreach ($emailTemplates as $template): ?>
                                  <option value="<?php echo $template['id']; ?>"><?php echo htmlspecialchars($template['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm text-slate-600 mb-1">Target Audience</label>
                        <select id="emailAudience" class="w-full border rounded-xl p-2">
                            <option value="all">All Customers</option>
                            <option value="vip">VIP Members (Gold/Platinum)</option>
                            <option value="loyalty">Loyalty Program Members</option>
                            <option value="recent">Recent Customers (Last 30 days)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm text-slate-600 mb-1">Subject *</label>
                        <input type="text" id="emailSubject" class="w-full border rounded-xl p-2" required>
                    </div>
                    <div>
                        <label class="block text-sm text-slate-600 mb-1">Content *</label>
                        <textarea id="emailContent" rows="6" class="w-full border rounded-xl p-2" placeholder="Dear {name},&#10;&#10;Your message here...&#10;&#10;{link}" required></textarea>
                        <p class="text-xs text-slate-400 mt-1">Use {name} for customer name, {link} for booking link</p>
                    </div>
                </div>
                <div class="flex gap-3 mt-6">
                    <button type="button" onclick="closeEmailBlastModal()" class="flex-1 border border-slate-200 px-4 py-2 rounded-xl hover:bg-slate-50">Cancel</button>
                    <button type="submit" class="flex-1 bg-amber-600 text-white px-4 py-2 rounded-xl hover:bg-amber-700">Send Blast</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Toast notification
        function showToast(message, type = 'success') {
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

        // Copy to clipboard
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => {
                showToast(`Copied: ${text}`, 'success');
            });
        }

        // Filter by status
        document.querySelectorAll('.status-filter').forEach(btn => {
            btn.addEventListener('click', function() {
                const status = this.dataset.status;
                const url = new URL(window.location);
                if (status !== 'all') {
                    url.searchParams.set('status', status);
                } else {
                    url.searchParams.delete('status');
                }
                window.location.href = url.toString();
            });
        });

        // Search
        document.getElementById('searchInput').addEventListener('keypress', function(e) {
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

        // Analytics Modal with Charts
        let statusChart, revenueChart;
        
        function openAnalyticsModal() {
            document.getElementById('analyticsModal').classList.add('show');
            
            // Initialize charts if not already done
            setTimeout(() => {
                // Status Distribution Chart
                const statusCtx = document.getElementById('statusChart')?.getContext('2d');
                if (statusCtx && !statusChart) {
                    statusChart = new Chart(statusCtx, {
                        type: 'doughnut',
                        data: {
                            labels: ['Active', 'Scheduled', 'Ended', 'Draft'],
                            datasets: [{
                                data: [
                                    <?php echo $stats['active_campaigns']; ?>,
                                    <?php echo $stats['scheduled_campaigns']; ?>,
                                    <?php echo $stats['ended_campaigns']; ?>,
                                    <?php echo $stats['draft_campaigns']; ?>
                                ],
                                backgroundColor: ['#10b981', '#3b82f6', '#6b7280', '#f59e0b'],
                                borderWidth: 0
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: true,
                            plugins: {
                                legend: { position: 'bottom' }
                            }
                        }
                    });
                }
                
                // Revenue vs Budget Chart
                const revenueCtx = document.getElementById('revenueChart')?.getContext('2d');
                if (revenueCtx && !revenueChart) {
                    revenueChart = new Chart(revenueCtx, {
                        type: 'bar',
                        data: {
                            labels: ['Revenue', 'Budget'],
                            datasets: [{
                                data: [
                                    <?php echo $stats['total_revenue']; ?>,
                                    <?php echo $stats['total_budget']; ?>
                                ],
                                backgroundColor: ['#10b981', '#f59e0b'],
                                borderRadius: 8
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: true,
                            plugins: {
                                legend: { display: false }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        callback: function(value) {
                                            return '₱' + value.toLocaleString();
                                        }
                                    }
                                }
                            }
                        }
                    });
                }
            }, 100);
        }
        
        function closeAnalyticsModal() {
            document.getElementById('analyticsModal').classList.remove('show');
        }

        // Campaign Details Modal
        function viewCampaignDetails(campaignId) {
            // Find campaign in the campaigns array
            const campaigns = <?php echo json_encode($campaigns); ?>;
            const campaign = campaigns.find(c => c.id == campaignId);
            
            if (campaign) {
                const content = document.getElementById('campaignDetailsContent');
                const startDate = new Date(campaign.start_date).toLocaleDateString();
                const endDate = new Date(campaign.end_date).toLocaleDateString();
                const discountText = campaign.discount_percent ? campaign.discount_percent + '% off' : 
                                     (campaign.discount_amount ? '₱' + campaign.discount_amount + ' off' : 'N/A');
                const revenuePercent = campaign.budget > 0 ? (campaign.revenue_generated / campaign.budget * 100).toFixed(1) : 0;
                
                content.innerHTML = `
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-3">
                            <div class="bg-slate-50 p-3 rounded-lg">
                                <p class="text-xs text-slate-500">Campaign Name</p>
                                <p class="font-semibold">${escapeHtml(campaign.campaign_name)}</p>
                            </div>
                            <div class="bg-slate-50 p-3 rounded-lg">
                                <p class="text-xs text-slate-500">Type</p>
                                <p class="font-semibold capitalize">${campaign.campaign_type}</p>
                            </div>
                            <div class="bg-slate-50 p-3 rounded-lg">
                                <p class="text-xs text-slate-500">Discount</p>
                                <p class="font-semibold">${discountText}</p>
                            </div>
                            <div class="bg-slate-50 p-3 rounded-lg">
                                <p class="text-xs text-slate-500">Status</p>
                                <p class="font-semibold capitalize">${campaign.status}</p>
                            </div>
                            <div class="bg-slate-50 p-3 rounded-lg">
                                <p class="text-xs text-slate-500">Period</p>
                                <p class="font-semibold text-sm">${startDate} - ${endDate}</p>
                            </div>
                            <div class="bg-slate-50 p-3 rounded-lg">
                                <p class="text-xs text-slate-500">Target Audience</p>
                                <p class="font-semibold capitalize">${campaign.target_audience}</p>
                            </div>
                            <div class="bg-slate-50 p-3 rounded-lg">
                                <p class="text-xs text-slate-500">Redemptions</p>
                                <p class="font-semibold">${campaign.redemptions_count} / ${campaign.redemption_limit || '∞'}</p>
                            </div>
                            <div class="bg-slate-50 p-3 rounded-lg">
                                <p class="text-xs text-slate-500">Revenue Generated</p>
                                <p class="font-semibold text-green-600">₱${campaign.revenue_generated.toLocaleString()}</p>
                            </div>
                        </div>
                        ${campaign.description ? `
                            <div class="bg-slate-50 p-3 rounded-lg">
                                <p class="text-xs text-slate-500">Description</p>
                                <p class="text-sm">${escapeHtml(campaign.description)}</p>
                            </div>
                        ` : ''}
                        ${campaign.budget > 0 ? `
                            <div class="bg-slate-50 p-3 rounded-lg">
                                <p class="text-xs text-slate-500">Budget Utilization</p>
                                <div class="flex justify-between text-sm mb-1">
                                    <span>₱${campaign.revenue_generated.toLocaleString()} / ₱${campaign.budget.toLocaleString()}</span>
                                    <span>${revenuePercent}%</span>
                                </div>
                                <div class="w-full bg-slate-200 rounded-full h-2">
                                    <div class="bg-amber-500 h-2 rounded-full" style="width: ${Math.min(100, revenuePercent)}%"></div>
                                </div>
                            </div>
                        ` : ''}
                    </div>
                `;
                
                document.getElementById('editCampaignBtn').onclick = () => {
                    closeCampaignDetailsModal();
                    openCampaignModal(campaignId);
                };
                
                document.getElementById('campaignDetailsModal').classList.add('show');
            }
        }
        
        function closeCampaignDetailsModal() {
            document.getElementById('campaignDetailsModal').classList.remove('show');
        }
        
        function escapeHtml(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // Campaign Modal
        let currentCampaignId = 0;
        
        function openCampaignModal(campaignId = 0) {
            currentCampaignId = campaignId;
            if (campaignId > 0) {
                document.getElementById('campaignModalTitle').textContent = 'Edit Campaign';
                loadCampaignData(campaignId);
            } else {
                document.getElementById('campaignModalTitle').textContent = 'Create New Campaign';
                document.getElementById('campaignForm').reset();
                document.getElementById('campaignId').value = 0;
                // Set default dates
                const now = new Date();
                const startDate = new Date(now.setHours(0,0,0,0));
                const endDate = new Date(now.setDate(now.getDate() + 30));
                document.getElementById('campaignStartDate').value = startDate.toISOString().slice(0,16);
                document.getElementById('campaignEndDate').value = endDate.toISOString().slice(0,16);
            }
            document.getElementById('campaignModal').classList.add('show');
        }

        function closeCampaignModal() {
            document.getElementById('campaignModal').classList.remove('show');
        }

        async function loadCampaignData(campaignId) {
            const formData = new FormData();
            formData.append('action', 'get_campaign');
            formData.append('campaign_id', campaignId);

            const response = await fetch('../../../controller/admin/post/marketing_post.php', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();

            if (data.success) {
                const c = data.campaign;
                document.getElementById('campaignId').value = c.id;
                document.getElementById('campaignName').value = c.campaign_name;
                document.getElementById('campaignType').value = c.campaign_type;
                document.getElementById('campaignDescription').value = c.description || '';
                document.getElementById('campaignDiscountPercent').value = c.discount_percent || '';
                document.getElementById('campaignDiscountAmount').value = c.discount_amount || '';
                document.getElementById('campaignStartDate').value = c.start_date.replace(' ', 'T');
                document.getElementById('campaignEndDate').value = c.end_date.replace(' ', 'T');
                document.getElementById('campaignStatus').value = c.status;
                document.getElementById('campaignTarget').value = c.target_audience;
                document.getElementById('campaignRedemptionLimit').value = c.redemption_limit || '';
                document.getElementById('campaignBudget').value = c.budget || '';
            }
        }

        document.getElementById('campaignForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData();
            const campaignId = document.getElementById('campaignId').value;
            
            formData.append('action', campaignId > 0 ? 'update_campaign' : 'create_campaign');
            formData.append('campaign_id', campaignId);
            formData.append('campaign_name', document.getElementById('campaignName').value);
            formData.append('campaign_type', document.getElementById('campaignType').value);
            formData.append('description', document.getElementById('campaignDescription').value);
            formData.append('discount_percent', document.getElementById('campaignDiscountPercent').value);
            formData.append('discount_amount', document.getElementById('campaignDiscountAmount').value);
            formData.append('start_date', document.getElementById('campaignStartDate').value);
            formData.append('end_date', document.getElementById('campaignEndDate').value);
            formData.append('status', document.getElementById('campaignStatus').value);
            formData.append('target_audience', document.getElementById('campaignTarget').value);
            formData.append('redemption_limit', document.getElementById('campaignRedemptionLimit').value);
            formData.append('budget', document.getElementById('campaignBudget').value);

            Swal.fire({ title: 'Saving...', text: 'Please wait', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

            const response = await fetch('../../../controller/admin/post/marketing_post.php', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();

            if (data.success) {
                Swal.fire({ title: 'Success!', text: data.message, icon: 'success', confirmButtonColor: '#d97706' }).then(() => {
                    location.reload();
                });
            } else {
                Swal.fire({ title: 'Error', text: data.message, icon: 'error', confirmButtonColor: '#d97706' });
            }
            closeCampaignModal();
        });

        // Promo Modal
        function openPromoModal() {
            document.getElementById('promoForm').reset();
            // Set default dates
            const now = new Date();
            const startDate = new Date(now.setHours(0,0,0,0));
            const endDate = new Date(now.setDate(now.getDate() + 30));
            document.getElementById('promoValidFrom').value = startDate.toISOString().slice(0,16);
            document.getElementById('promoValidTo').value = endDate.toISOString().slice(0,16);
            document.getElementById('promoModal').classList.add('show');
        }

        function closePromoModal() {
            document.getElementById('promoModal').classList.remove('show');
        }

        document.getElementById('promoForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData();
            formData.append('action', 'create_promo_code');
            formData.append('campaign_id', document.getElementById('promoCampaignId').value);
            formData.append('code', document.getElementById('promoCode').value);
            formData.append('description', document.getElementById('promoDescription').value);
            formData.append('discount_type', document.getElementById('promoDiscountType').value);
            formData.append('discount_value', document.getElementById('promoDiscountValue').value);
            formData.append('min_purchase', document.getElementById('promoMinPurchase').value);
            formData.append('max_discount', document.getElementById('promoMaxDiscount').value);
            formData.append('valid_from', document.getElementById('promoValidFrom').value);
            formData.append('valid_to', document.getElementById('promoValidTo').value);
            formData.append('usage_limit', document.getElementById('promoUsageLimit').value);
            formData.append('per_user_limit', document.getElementById('promoPerUserLimit').value);

            Swal.fire({ title: 'Creating...', text: 'Please wait', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

            const response = await fetch('../../../controller/admin/post/marketing_post.php', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();

            if (data.success) {
                Swal.fire({ title: 'Success!', text: data.message, icon: 'success', confirmButtonColor: '#d97706' }).then(() => {
                    location.reload();
                });
            } else {
                Swal.fire({ title: 'Error', text: data.message, icon: 'error', confirmButtonColor: '#d97706' });
            }
            closePromoModal();
        });

        // Email Blast Modal
        function openEmailBlastModal() {
            document.getElementById('emailBlastForm').reset();
            document.getElementById('emailBlastModal').classList.add('show');
        }

        function closeEmailBlastModal() {
            document.getElementById('emailBlastModal').classList.remove('show');
        }

        document.getElementById('emailTemplate').addEventListener('change', async function() {
            const templateId = this.value;
            if (templateId) {
                const templates = <?php echo json_encode($emailTemplates); ?>;
                const template = templates.find(t => t.id == templateId);
                if (template) {
                    document.getElementById('emailSubject').value = template.subject;
                    document.getElementById('emailContent').value = template.content;
                }
            }
        });

        document.getElementById('emailBlastForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData();
            formData.append('action', 'send_email_blast');
            formData.append('template_id', document.getElementById('emailTemplate').value);
            formData.append('audience', document.getElementById('emailAudience').value);
            formData.append('subject', document.getElementById('emailSubject').value);
            formData.append('content', document.getElementById('emailContent').value);

            Swal.fire({ title: 'Sending...', text: 'Please wait', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

            const response = await fetch('../../../controller/admin/post/marketing_post.php', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();

            if (data.success) {
                Swal.fire({ title: 'Success!', text: data.message, icon: 'success', confirmButtonColor: '#d97706' });
            } else {
                Swal.fire({ title: 'Error', text: data.message, icon: 'error', confirmButtonColor: '#d97706' });
            }
            closeEmailBlastModal();
        });

        // Notification bell
        document.getElementById('notificationBell').addEventListener('click', function() {
            window.location.href = '../notifications.php';
        });

        // Close modals on outside click
        window.onclick = function(event) {
            const modals = ['analyticsModal', 'campaignDetailsModal', 'campaignModal', 'promoModal', 'emailBlastModal'];
            modals.forEach(modalId => {
                const modal = document.getElementById(modalId);
                if (modal && event.target === modal) {
                    modal.classList.remove('show');
                }
            });
        }
    </script>
</body>
</html>