<?php
/**
 * View - Admin Analytics Dashboard
 */
require_once '../../../controller/admin/get/analytics/analytics_dashboard.php';
$current_page = 'analytics_dashboard';
?>
<!DOCTYPE html>
<html lang="en">

  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin · Analytics Dashboard</title>
    <!-- Tailwind via CDN + Font Awesome 6 -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

      .chart-container {
        position: relative;
        height: 160px;
        width: 100%;
      }
    </style>
  </head>

  <body class="bg-white font-sans antialiased">

    <!-- APP CONTAINER: flex row (sidebar + main) -->
    <div class="min-h-screen flex flex-col lg:flex-row">

      <!-- ========== SIDEBAR ========== -->
      <?php require_once '../components/admin_nav.php'; ?>

      <!-- ========== MAIN CONTENT (ANALYTICS DASHBOARD) ========== -->
      <main class="flex-1 p-5 lg:p-8 overflow-y-auto bg-white">

        <!-- header with title and date -->
        <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
          <div>
            <h1 class="text-2xl lg:text-3xl font-light text-slate-800">Analytics Dashboard</h1>
            <p class="text-sm text-slate-500 mt-0.5">key performance indicators and business intelligence at a glance
            </p>
          </div>
          <div class="flex gap-3 text-sm">
            <span class="bg-white border rounded-full px-4 py-2 flex items-center gap-2 shadow-sm"><i
                class="fas fa-calendar text-slate-400"></i>
              <?php echo $today; ?>
            </span>
            <?php require_once '../components/notification_component.php'; ?>
          </div>
        </div>

        <!-- ===== KPI CARDS ===== -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
          <div class="bg-gradient-to-br from-amber-50 to-amber-100 rounded-2xl p-5 border border-amber-200">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-xs text-amber-700">Total Revenue (MTD)</p>
                <p class="text-3xl font-bold text-slate-800">₱
                  <?php echo number_format($totalRevenue); ?>
                </p>
                <p class="text-xs <?php echo $revenueGrowth >= 0 ? 'text-green-600' : 'text-red-600'; ?> mt-1">
                  <?php echo $revenueGrowth >= 0 ? '↑' : '↓'; ?>
                  <?php echo abs($revenueGrowth); ?>% vs last month
                </p>
              </div>
              <div class="h-12 w-12 rounded-full bg-amber-200 flex items-center justify-center text-amber-700"><i
                  class="fa-solid fa-peso-sign text-xl"></i></div>
            </div>
          </div>
          <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-2xl p-5 border border-blue-200">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-xs text-blue-700">Occupancy Rate</p>
                <p class="text-3xl font-bold text-slate-800">
                  <?php echo $occupancyRate; ?>%
                </p>
                <p class="text-xs <?php echo $occupancyGrowth >= 0 ? 'text-green-600' : 'text-red-600'; ?> mt-1">
                  <?php echo $occupancyGrowth >= 0 ? '↑' : '↓'; ?>
                  <?php echo abs($occupancyGrowth); ?>% vs last month
                </p>
              </div>
              <div class="h-12 w-12 rounded-full bg-blue-200 flex items-center justify-center text-blue-700"><i
                  class="fa-solid fa-bed text-xl"></i></div>
            </div>
          </div>
          <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-2xl p-5 border border-green-200">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-xs text-green-700">Avg. Daily Rate</p>
                <p class="text-3xl font-bold text-slate-800">₱
                  <?php echo number_format($adr); ?>
                </p>
                <p class="text-xs <?php echo $adrGrowth >= 0 ? 'text-green-600' : 'text-red-600'; ?> mt-1">
                  <?php echo $adrGrowth >= 0 ? '↑' : '↓'; ?>
                  <?php echo abs($adrGrowth); ?>% vs last month
                </p>
              </div>
              <div class="h-12 w-12 rounded-full bg-green-200 flex items-center justify-center text-green-700"><i
                  class="fa-solid fa-tag text-xl"></i></div>
            </div>
          </div>
          <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-2xl p-5 border border-purple-200">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-xs text-purple-700">RevPAR</p>
                <p class="text-3xl font-bold text-slate-800">₱
                  <?php echo number_format($revPAR); ?>
                </p>
                <p class="text-xs <?php echo $revparGrowth >= 0 ? 'text-green-600' : 'text-red-600'; ?> mt-1">
                  <?php echo $revparGrowth >= 0 ? '↑' : '↓'; ?>
                  <?php echo abs($revparGrowth); ?>% vs last month
                </p>
              </div>
              <div class="h-12 w-12 rounded-full bg-purple-200 flex items-center justify-center text-purple-700"><i
                  class="fa-solid fa-chart-line text-xl"></i></div>
            </div>
          </div>
        </div>

        <!-- ===== DATE RANGE SELECTOR ===== -->
        <form method="GET" action=""
          class="bg-white rounded-2xl border border-slate-200 p-4 mb-6 flex flex-wrap gap-3 items-center justify-between">
          <div class="flex gap-2 flex-wrap items-center">
            <span class="text-sm text-slate-500">Compare:</span>
            <button type="submit" name="period" value="month"
              class="<?php echo (!isset($_GET['period']) || $_GET['period'] == 'month') ? 'bg-amber-600 text-white' : 'border border-slate-200 hover:bg-slate-50'; ?> px-4 py-2 rounded-xl text-sm">Last
              30 days</button>
            <button type="submit" name="period" value="quarter"
              class="<?php echo ($_GET['period'] ?? '') == 'quarter' ? 'bg-amber-600 text-white' : 'border border-slate-200 hover:bg-slate-50'; ?> px-4 py-2 rounded-xl text-sm">Last
              quarter</button>
            <button type="submit" name="period" value="year"
              class="<?php echo ($_GET['period'] ?? '') == 'year' ? 'bg-amber-600 text-white' : 'border border-slate-200 hover:bg-slate-50'; ?> px-4 py-2 rounded-xl text-sm">Last
              year</button>
            <button type="submit" name="period" value="custom"
              class="<?php echo ($_GET['period'] ?? '') == 'custom' ? 'bg-amber-600 text-white' : 'border border-slate-200 hover:bg-slate-50'; ?> px-4 py-2 rounded-xl text-sm">Custom</button>
          </div>
          <div class="flex gap-2">
            <span class="text-sm text-slate-500">vs.</span>
            <select name="compare" class="border border-slate-200 rounded-lg px-3 py-2 text-sm">
              <option value="previous">Previous period</option>
              <option value="year">Previous year</option>
            </select>
          </div>
        </form>

        <!-- ===== CHARTS ROW 1 ===== -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">

          <!-- revenue trend -->
          <div class="bg-white rounded-2xl border border-slate-200 p-5">
            <h2 class="font-semibold text-lg flex items-center gap-2 mb-3"><i
                class="fa-solid fa-chart-line text-amber-600"></i> revenue trend (last 30 days)</h2>
            <div class="chart-container">
              <canvas id="revenueChart"></canvas>
            </div>
          </div>

          <!-- booking source pie chart -->
          <div class="bg-white rounded-2xl border border-slate-200 p-5">
            <h2 class="font-semibold text-lg flex items-center gap-2 mb-3"><i
                class="fa-solid fa-chart-pie text-amber-600"></i> booking sources</h2>
            <div class="chart-container">
              <canvas id="sourceChart"></canvas>
            </div>
          </div>
        </div>

        <!-- ===== CHARTS ROW 2 ===== -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">

          <!-- occupancy trend -->
          <div class="bg-white rounded-2xl border border-slate-200 p-5">
            <h2 class="font-semibold text-lg flex items-center gap-2 mb-3"><i
                class="fa-solid fa-percent text-amber-600"></i> occupancy trend</h2>
            <div class="chart-container">
              <canvas id="occupancyChart"></canvas>
            </div>
          </div>

          <!-- guest satisfaction -->
          <div class="bg-white rounded-2xl border border-slate-200 p-5">
            <h2 class="font-semibold text-lg flex items-center gap-2 mb-3"><i class="fas fa-star text-amber-600"></i>
              guest satisfaction scores</h2>
            <div class="space-y-3">
              <div class="flex items-center gap-2">
                <span class="text-sm w-24">Overall</span>
                <div class="flex-1 h-2 bg-slate-100 rounded-full overflow-hidden">
                  <div class="h-full bg-green-500" style="width: <?php echo ($overallRating / 5) * 100; ?>%;"></div>
                </div>
                <span class="text-sm font-medium">
                  <?php echo $overallRating; ?>/5
                </span>
              </div>
              <div class="flex items-center gap-2">
                <span class="text-sm w-24">Cleanliness</span>
                <div class="flex-1 h-2 bg-slate-100 rounded-full overflow-hidden">
                  <div class="h-full bg-green-500" style="width: <?php echo ($cleanlinessRating / 5) * 100; ?>%;"></div>
                </div>
                <span class="text-sm font-medium">
                  <?php echo $cleanlinessRating; ?>/5
                </span>
              </div>
              <div class="flex items-center gap-2">
                <span class="text-sm w-24">Service</span>
                <div class="flex-1 h-2 bg-slate-100 rounded-full overflow-hidden">
                  <div class="h-full bg-green-500" style="width: <?php echo ($serviceRating / 5) * 100; ?>%;"></div>
                </div>
                <span class="text-sm font-medium">
                  <?php echo $serviceRating; ?>/5
                </span>
              </div>
              <div class="flex items-center gap-2">
                <span class="text-sm w-24">Facilities</span>
                <div class="flex-1 h-2 bg-slate-100 rounded-full overflow-hidden">
                  <div class="h-full bg-green-500" style="width: <?php echo ($facilitiesRating / 5) * 100; ?>%;"></div>
                </div>
                <span class="text-sm font-medium">
                  <?php echo $facilitiesRating; ?>/5
                </span>
              </div>
              <div class="flex items-center gap-2">
                <span class="text-sm w-24">Value</span>
                <div class="flex-1 h-2 bg-slate-100 rounded-full overflow-hidden">
                  <div class="h-full bg-green-500" style="width: <?php echo ($valueRating / 5) * 100; ?>%;"></div>
                </div>
                <span class="text-sm font-medium">
                  <?php echo $valueRating; ?>/5
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- ===== BOTTOM: TOP PERFORMERS & INSIGHTS ===== -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

          <!-- top performing rooms -->
          <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 p-5">
            <h2 class="font-semibold text-lg flex items-center gap-2 mb-3"><i
                class="fa-solid fa-crown text-amber-600"></i> top performing room types</h2>
            <div class="overflow-x-auto">
              <table class="w-full text-sm">
                <thead class="text-slate-500 text-xs border-b">
                  <tr>
                    <td class="p-2">Room type</td>
                    <td class="p-2">Occupancy</td>
                    <td class="p-2">ADR</td>
                    <td class="p-2">RevPAR</td>
                    <td class="p-2">Revenue</td>
                  </tr>
                </thead>
                <tbody class="divide-y">
                  <?php if (empty($topRooms)): ?>
                    <tr>
                      <td colspan="5" class="p-4 text-center text-slate-400">No room data available</td>
                    </tr>
                  <?php else: ?>
                    <?php foreach ($topRooms as $room): ?>
                      <tr>
                        <td class="p-2 font-medium">
                          <?php echo htmlspecialchars($room['room_name']); ?>
                        </td>
                        <td class="p-2">
                          <?php echo $room['occupancy']; ?>%
                        </td>
                        <td class="p-2">₱
                          <?php echo number_format($room['avg_rate']); ?>
                        </td>
                        <td class="p-2">₱
                          <?php echo number_format($room['revpar']); ?>
                        </td>
                        <td class="p-2">₱
                          <?php echo number_format($room['revenue']); ?>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>

          <!-- key insights -->
          <div class="bg-amber-50 border border-amber-200 rounded-2xl p-5">
            <h3 class="font-semibold flex items-center gap-2 mb-3"><i class="fas fa-lightbulb text-amber-600"></i> key
              insights</h3>
            <ul class="space-y-3">
              <li class="flex gap-2 text-sm">
                <span class="<?php echo $weekendVsWeekday > 0 ? 'text-green-600' : 'text-red-600'; ?>">
                  <?php echo $weekendVsWeekday > 0 ? '↑' : '↓'; ?>
                </span>
                <span>Weekend occupancy
                  <?php echo abs($weekendVsWeekday); ?>%
                  <?php echo $weekendVsWeekday > 0 ? 'higher' : 'lower'; ?> than weekdays
                </span>
              </li>
              <li class="flex gap-2 text-sm">
                <span class="text-amber-600">→</span>
                <span>OTA commissions: ₱
                  <?php echo number_format($otaCommission); ?> this month
                </span>
              </li>
              <li class="flex gap-2 text-sm">
                <span class="text-green-600">↑</span>
                <span>Direct bookings:
                  <?php echo $directBookings; ?> this month
                </span>
              </li>
              <li class="flex gap-2 text-sm">
                <span class="text-blue-600">i</span>
                <span>Average lead time:
                  <?php echo $avgLeadTime; ?> days
                </span>
              </li>
            </ul>
          </div>
        </div>

        <!-- ===== EXPORT BUTTONS ===== -->
        <div class="flex justify-end gap-3 mt-6 mb-4">
          <button onclick="exportPDF()"
            class="border border-amber-600 text-amber-700 px-4 py-2 rounded-xl text-sm hover:bg-amber-50"><i
              class="fas fa-file-pdf mr-2"></i>PDF</button>
          <button onclick="exportExcel()"
            class="border border-amber-600 text-amber-700 px-4 py-2 rounded-xl text-sm hover:bg-amber-50"><i
              class="fas fa-file-excel mr-2"></i>Excel</button>
          <button onclick="exportDashboard()"
            class="bg-amber-600 text-white px-4 py-2 rounded-xl text-sm hover:bg-amber-700"><i
              class="fas fa-image mr-2"></i>Export Dashboard</button>
        </div>
      </main>
    </div>

    <script>
      // Initialize charts
      document.addEventListener('DOMContentLoaded', function () {
        initRevenueChart();
        initSourceChart();
        initOccupancyChart();
      });

      // Revenue Chart
      function initRevenueChart() {
        const ctx = document.getElementById('revenueChart').getContext('2d');
        new Chart(ctx, {
          type: 'line',
          data: {
            labels: <?php
            echo json_encode($revenueLabels); ?>,
            datasets: [{
              label: 'Daily Revenue (₱)',
              data: <?php
              echo json_encode($revenueData); ?>,
              borderColor: '#d97706',
              backgroundColor: 'rgba(217, 119, 6, 0.1)',
              tension: 0.4,
              fill: true
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: { display: false }
            },
            scales: {
              y: {
                beginAtZero: true,
                ticks: {
                  callback: function (value) {
                    return '₱' + (value / 1000) + 'k';
                  }
                }
              }
            }
          }
        });
      }

      // Source Chart
      function initSourceChart() {
        const ctx = document.getElementById('sourceChart').getContext('2d');
        new Chart(ctx, {
          type: 'doughnut',
          data: {
            labels: <?php
            echo json_encode($sourceLabels); ?>,
            datasets: [{
              data: <?php
              echo json_encode($sourceCounts); ?>,
              backgroundColor: <?php
              echo json_encode($sourceColors); ?>,
              borderWidth: 0
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: { position: 'bottom' }
            }
          }
        });
      }

      // Occupancy Chart
      function initOccupancyChart() {
        const ctx = document.getElementById('occupancyChart').getContext('2d');
        new Chart(ctx, {
          type: 'line',
          data: {
            labels: <?php
            echo json_encode($occupancyLabels); ?>,
            datasets: [{
              label: 'Occupancy Rate (%)',
              data: <?php
              echo json_encode($occupancyData); ?>,
              borderColor: '#3b82f6',
              backgroundColor: 'rgba(59, 130, 246, 0.1)',
              tension: 0.4,
              fill: true
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: { display: false }
            },
            scales: {
              y: {
                beginAtZero: true,
                max: 100,
                ticks: {
                  callback: function (value) {
                    return value + '%';
                  }
                }
              }
            }
          }
        });
      }

      // Export functions
      function exportPDF() {
        window.location.href = '../../../controller/admin/post/analytics/export_reports.php?type=dashboard&format=pdf';
      }

      function exportExcel() {
        window.location.href = '../../../controller/admin/post/analytics/export_reports.php?type=dashboard&format=excel';
      }

      function exportDashboard() {
        // This could capture the dashboard as an image
        alert('Dashboard export feature coming soon!');
      }
    </script>
  </body>

</html>