<?php
/**
 * View - Admin Sales Reports
 */
require_once '../../../controller/admin/get/analytics/sales_reports.php';
$current_page = 'sales_reports';
?>
<!DOCTYPE html>
<html lang="en">

  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin · Sales Reports</title>
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
        height: 250px;
        width: 100%;
      }
    </style>
  </head>

  <body class="bg-white font-sans antialiased">

    <!-- APP CONTAINER: flex row (sidebar + main) -->
    <div class="min-h-screen flex flex-col lg:flex-row">

      <!-- ========== SIDEBAR ========== -->
      <?php require_once '../components/admin_nav.php'; ?>

      <!-- ========== MAIN CONTENT (SALES REPORTS) ========== -->
      <main class="flex-1 p-5 lg:p-8 overflow-y-auto bg-white">

        <!-- header with title and date -->
        <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
          <div>
            <h1 class="text-2xl lg:text-3xl font-light text-slate-800">Sales Reports</h1>
            <p class="text-sm text-slate-500 mt-0.5">comprehensive sales analytics, revenue breakdown, and performance
              metrics</p>
          </div>
          <div class="flex gap-3 text-sm">
            <span class="bg-white border rounded-full px-4 py-2 flex items-center gap-2 shadow-sm"><i
                class="fas fa-calendar text-slate-400"></i> <?php echo date('F j, Y'); ?></span>
            <?php require_once '../components/notification_component.php'; ?>
          </div>
        </div>

        <!-- ===== DATE RANGE PICKER ===== -->
        <form method="GET" action=""
          class="bg-white rounded-2xl border border-slate-200 p-4 mb-6 flex flex-wrap gap-3 items-center justify-between">
          <div class="flex gap-2 flex-wrap items-center">
            <span class="text-sm text-slate-500">Period:</span>
            <button type="submit" name="period" value="today"
              class="<?php echo $period == 'today' ? 'bg-amber-600 text-white' : 'border border-slate-200 hover:bg-slate-50'; ?> px-4 py-2 rounded-xl text-sm transition">Today</button>
            <button type="submit" name="period" value="yesterday"
              class="<?php echo $period == 'yesterday' ? 'bg-amber-600 text-white' : 'border border-slate-200 hover:bg-slate-50'; ?> px-4 py-2 rounded-xl text-sm transition">Yesterday</button>
            <button type="submit" name="period" value="week"
              class="<?php echo $period == 'week' ? 'bg-amber-600 text-white' : 'border border-slate-200 hover:bg-slate-50'; ?> px-4 py-2 rounded-xl text-sm transition">This
              week</button>
            <button type="submit" name="period" value="month"
              class="<?php echo $period == 'month' ? 'bg-amber-600 text-white' : 'border border-slate-200 hover:bg-slate-50'; ?> px-4 py-2 rounded-xl text-sm transition">This
              month</button>
            <button type="submit" name="period" value="year"
              class="<?php echo $period == 'year' ? 'bg-amber-600 text-white' : 'border border-slate-200 hover:bg-slate-50'; ?> px-4 py-2 rounded-xl text-sm transition">This
              year</button>
          </div>
          <div class="flex gap-2">
            <input type="date" name="start_date" class="border border-slate-200 rounded-xl px-3 py-2 text-sm"
              value="<?php echo $start_date; ?>">
            <span class="text-slate-400">—</span>
            <input type="date" name="end_date" class="border border-slate-200 rounded-xl px-3 py-2 text-sm"
              value="<?php echo $end_date; ?>">
            <button type="submit"
              class="bg-amber-600 text-white px-4 py-2 rounded-xl text-sm hover:bg-amber-700 transition">apply</button>
          </div>
        </form>

        <!-- ===== TOP STATS CARDS ===== -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
          <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs text-slate-500">Total revenue</p>
            <p class="text-2xl font-semibold">₱<?php echo number_format($totalRevenue); ?></p>
            <span class="text-xs <?php echo $revenueGrowth >= 0 ? 'text-green-600' : 'text-red-600'; ?>">
              <?php echo $revenueGrowth >= 0 ? '↑' : '↓'; ?> <?php echo abs($revenueGrowth); ?>% vs last period
            </span>
          </div>
          <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs text-slate-500">Total orders</p>
            <p class="text-2xl font-semibold"><?php echo number_format($totalOrders); ?></p>
          </div>
          <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs text-slate-500">Avg. order value</p>
            <p class="text-2xl font-semibold">₱<?php echo number_format($avgOrderValue); ?></p>
          </div>
          <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs text-slate-500">Hotel revenue</p>
            <p class="text-2xl font-semibold">₱<?php echo number_format($hotelRevenue); ?></p>
          </div>
          <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs text-slate-500">Restaurant revenue</p>
            <p class="text-2xl font-semibold">₱<?php echo number_format($restaurantRevenue); ?></p>
          </div>
        </div>

        <!-- ===== SALES CHART ===== -->
        <div class="bg-white rounded-2xl border border-slate-200 p-5 mb-8">
          <div class="flex items-center justify-between mb-4">
            <h2 class="font-semibold text-lg flex items-center gap-2"><i
                class="fa-solid fa-chart-line text-amber-600"></i> daily sales trend</h2>
            <div class="flex gap-2">
              <select id="salesChartType" class="border border-slate-200 rounded-lg px-3 py-1 text-sm"
                onchange="toggleSalesChart()">
                <option value="total">Total Revenue</option>
                <option value="stacked">Stacked by source</option>
              </select>
            </div>
          </div>
          <div class="chart-container">
            <canvas id="salesChart"></canvas>
          </div>
        </div>

        <!-- ===== REVENUE BREAKDOWN ===== -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">

          <!-- by category -->
          <div class="bg-white rounded-2xl border border-slate-200 p-5">
            <h2 class="font-semibold text-lg flex items-center gap-2 mb-3"><i
                class="fas fa-chart-pie text-amber-600"></i> revenue by category</h2>
            <div class="space-y-3">
              <?php foreach ($categoryData as $category): ?>
                <div class="flex items-center gap-2">
                  <span class="text-sm w-28"><?php echo $category['name']; ?></span>
                  <div class="flex-1 h-2 bg-slate-100 rounded-full overflow-hidden">
                    <div class="h-full bg-<?php echo $category['color']; ?>-500"
                      style="width: <?php echo $category['percentage']; ?>%;"></div>
                  </div>
                  <span class="text-sm font-medium">₱<?php echo number_format($category['revenue']); ?>
                    (<?php echo $category['percentage']; ?>%)</span>
                </div>
              <?php endforeach; ?>
            </div>
          </div>

          <!-- by payment method -->
          <div class="bg-white rounded-2xl border border-slate-200 p-5">
            <h2 class="font-semibold text-lg flex items-center gap-2 mb-3"><i
                class="fas fa-credit-card text-amber-600"></i> revenue by payment method</h2>
            <div class="space-y-3">
              <?php
              $totalPaymentRevenue = array_sum(array_column($paymentMethodData, 'revenue'));
              foreach ($paymentMethodData as $method):
                $percentage = $totalPaymentRevenue > 0 ? round(($method['revenue'] / $totalPaymentRevenue) * 100, 1) : 0;
                $colors = ['GCash' => 'blue', 'Credit card' => 'green', 'Cash' => 'amber', 'Bank transfer' => 'purple'];
                $color = $colors[$method['payment_method']] ?? 'gray';
                ?>
                <div class="flex items-center gap-2">
                  <span class="text-sm w-24"><?php echo $method['payment_method']; ?></span>
                  <div class="flex-1 h-2 bg-slate-100 rounded-full overflow-hidden">
                    <div class="h-full bg-<?php echo $color; ?>-500" style="width: <?php echo $percentage; ?>%;"></div>
                  </div>
                  <span class="text-sm font-medium">₱<?php echo number_format($method['revenue']); ?>
                    (<?php echo $percentage; ?>%)</span>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>

        <!-- ===== TOP SELLING ITEMS & DAILY SUMMARY ===== -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">

          <!-- top selling items -->
          <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 p-5">
            <h2 class="font-semibold text-lg flex items-center gap-2 mb-3"><i
                class="fa-solid fa-crown text-amber-600"></i> top selling items</h2>
            <div class="overflow-x-auto">
              <table class="w-full text-sm">
                <thead class="text-slate-500 text-xs border-b">
                  <tr>
                    <td class="p-2">Item</td>
                    <td class="p-2">Category</td>
                    <td class="p-2">Units sold</td>
                    <td class="p-2">Revenue</td>
                  </tr>
                </thead>
                <tbody class="divide-y">
                  <?php if (empty($topItems)): ?>
                    <tr>
                      <td colspan="4" class="p-4 text-center text-slate-400">No sales data available</td>
                    </tr>
                  <?php else: ?>
                    <?php foreach ($topItems as $item): ?>
                      <tr>
                        <td class="p-2 font-medium"><?php echo htmlspecialchars($item['item_name']); ?></td>
                        <td class="p-2"><?php echo $item['category']; ?></td>
                        <td class="p-2"><?php echo number_format($item['units_sold']); ?></td>
                        <td class="p-2">₱<?php echo number_format($item['revenue']); ?></td>
                      </tr>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>

          <!-- daily summary -->
          <div class="bg-amber-50 border border-amber-200 rounded-2xl p-5">
            <h3 class="font-semibold flex items-center gap-2 mb-3"><i class="fas fa-sun text-amber-600"></i> today's
              summary</h3>
            <ul class="space-y-2">
              <li class="flex justify-between items-center">
                <span>Revenue</span>
                <span class="font-semibold">₱<?php echo number_format($todayRevenue); ?></span>
              </li>
              <li class="flex justify-between items-center">
                <span>Orders</span>
                <span class="font-semibold"><?php echo number_format($todayOrders); ?></span>
              </li>
              <li class="flex justify-between items-center">
                <span>Occupancy</span>
                <span class="font-semibold"><?php echo $todayOccupancyRate; ?>%</span>
              </li>
              <li class="flex justify-between items-center border-t pt-2 mt-2">
                <span>Projected month end</span>
                <span class="font-semibold text-green-600">₱<?php echo number_format($projectedRevenue); ?></span>
              </li>
            </ul>
          </div>
        </div>

        <!-- ===== EXPORT BUTTONS ===== -->
        <div class="flex justify-end gap-3 mb-4">
          <button onclick="exportPDF()"
            class="border border-amber-600 text-amber-700 px-4 py-2 rounded-xl text-sm hover:bg-amber-50"><i
              class="fas fa-file-pdf mr-2"></i>PDF</button>
          <button onclick="exportExcel()"
            class="border border-amber-600 text-amber-700 px-4 py-2 rounded-xl text-sm hover:bg-amber-50"><i
              class="fas fa-file-excel mr-2"></i>Excel</button>
          <button onclick="window.print()"
            class="bg-amber-600 text-white px-4 py-2 rounded-xl text-sm hover:bg-amber-700"><i
              class="fas fa-print mr-2"></i>Print</button>
        </div>
      </main>
    </div>

    <script>
      // Initialize charts
      document.addEventListener('DOMContentLoaded', function () {
        initSalesChart();
      });

      // Sales Chart
      let salesChart;
      function initSalesChart() {
        const ctx = document.getElementById('salesChart').getContext('2d');

        salesChart = new Chart(ctx, {
          type: 'bar',
          data: {
            labels: <?php echo json_encode($dailyLabels); ?>,
            datasets: [{
              label: 'Daily Revenue (₱)',
              data: <?php echo json_encode($dailyTotalRevenue); ?>,
              backgroundColor: '#d97706',
              borderRadius: 4
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: {
                display: true,
                position: 'top'
              }
            },
            scales: {
              y: {
                beginAtZero: true,
                ticks: {
                  callback: function (value) {
                    return '₱' + value.toLocaleString();
                  }
                }
              }
            }
          }
        });
      }

      function toggleSalesChart() {
        const type = document.getElementById('salesChartType').value;

        if (type === 'total') {
          salesChart.data.datasets = [{
            label: 'Daily Revenue (₱)',
            data: <?php echo json_encode($dailyTotalRevenue); ?>,
            backgroundColor: '#d97706',
            borderRadius: 4
          }];
        } else {
          salesChart.data.datasets = [
            {
              label: 'Hotel Revenue',
              data: <?php echo json_encode($dailyHotelRevenue); ?>,
              backgroundColor: '#d97706',
              stack: 'combined',
              borderRadius: 4
            },
            {
              label: 'Restaurant Revenue',
              data: <?php echo json_encode($dailyRestaurantRevenue); ?>,
              backgroundColor: '#10b981',
              stack: 'combined',
              borderRadius: 4
            }
          ];
        }

        salesChart.update();
      }

      // Export functions
      function exportPDF() {
        window.location.href = '../../../controller/admin/post/analytics/export_reports.php?type=sales&format=pdf&start=<?php echo $start_date; ?>&end=<?php echo $end_date; ?>';
      }

      function exportExcel() {
        window.location.href = '../../../controller/admin/post/analytics/export_reports.php?type=sales&format=excel&start=<?php echo $start_date; ?>&end=<?php echo $end_date; ?>';
      }
    </script>
  </body>

</html>