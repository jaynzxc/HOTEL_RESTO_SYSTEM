<?php
/**
 * View - Admin Booking Reports (Combined Hotel & Restaurant)
 */
require_once '../../../controller/admin/get/analytics/booking_reports.php';
$current_page = 'booking_reports';
?>
<!DOCTYPE html>
<html lang="en">

  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin · Booking Reports</title>
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

      .type-badge-hotel {
        background-color: #d97706;
        color: white;
      }

      .type-badge-restaurant {
        background-color: #10b981;
        color: white;
      }
    </style>
  </head>

  <body class="bg-white font-sans antialiased">

    <!-- APP CONTAINER: flex row (sidebar + main) -->
    <div class="min-h-screen flex flex-col lg:flex-row">

      <!-- ========== SIDEBAR ========== -->
      <?php require_once '../components/admin_nav.php'; ?>

      <!-- ========== MAIN CONTENT (BOOKING REPORTS) ========== -->
      <main class="flex-1 p-5 lg:p-8 overflow-y-auto bg-white">

        <!-- header with title and date -->
        <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
          <div>
            <h1 class="text-2xl lg:text-3xl font-light text-slate-800">Booking Reports</h1>
            <p class="text-sm text-slate-500 mt-0.5">analyze hotel bookings & restaurant reservations</p>
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
            <button type="submit" name="period" value="week"
              class="<?php echo $period == 'week' ? 'bg-amber-600 text-white' : 'border border-slate-200 hover:bg-slate-50'; ?> px-4 py-2 rounded-xl text-sm transition">This
              week</button>
            <button type="submit" name="period" value="month"
              class="<?php echo $period == 'month' ? 'bg-amber-600 text-white' : 'border border-slate-200 hover:bg-slate-50'; ?> px-4 py-2 rounded-xl text-sm transition">This
              month</button>
            <button type="submit" name="period" value="quarter"
              class="<?php echo $period == 'quarter' ? 'bg-amber-600 text-white' : 'border border-slate-200 hover:bg-slate-50'; ?> px-4 py-2 rounded-xl text-sm transition">This
              quarter</button>
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
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
          <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs text-slate-500">Total bookings</p>
            <p class="text-2xl font-semibold"><?php echo number_format($totalBookings); ?></p>
            <div class="flex gap-2 mt-1 text-xs">
              <span class="bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full">Hotel:
                <?php echo number_format($totalHotelBookings); ?></span>
              <span class="bg-green-100 text-green-700 px-2 py-0.5 rounded-full">Restaurant:
                <?php echo number_format($totalRestaurantReservations); ?></span>
            </div>
          </div>
          <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs text-slate-500">Total revenue</p>
            <p class="text-2xl font-semibold">₱<?php echo number_format($totalRevenue); ?></p>
            <div class="flex gap-2 mt-1 text-xs">
              <span class="bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full">Hotel:
                ₱<?php echo number_format($totalHotelRevenue); ?></span>
              <span class="bg-green-100 text-green-700 px-2 py-0.5 rounded-full">Restaurant:
                ₱<?php echo number_format($totalRestaurantRevenue); ?></span>
            </div>
          </div>
          <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs text-slate-500">Avg. booking value</p>
            <p class="text-2xl font-semibold">₱<?php echo number_format($avgBookingValue); ?></p>
          </div>
          <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs text-slate-500">Cancellation rate</p>
            <p
              class="text-2xl font-semibold <?php echo $cancellationRate > 10 ? 'text-amber-600' : 'text-green-600'; ?>">
              <?php echo $cancellationRate; ?>%
            </p>
            <span class="text-xs text-slate-500"><?php echo number_format($totalCancelled); ?> cancelled</span>
          </div>
          <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs text-slate-500">Avg. length of stay</p>
            <p class="text-2xl font-semibold"><?php echo $avgStay; ?> nights</p>
          </div>
          <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs text-slate-500">Occupancy rate</p>
            <p class="text-2xl font-semibold"><?php echo $occupancyRate; ?>%</p>
          </div>
          <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs text-slate-500">RevPAR</p>
            <p class="text-2xl font-semibold">₱<?php echo number_format($revPAR); ?></p>
          </div>
          <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs text-slate-500">Lost revenue</p>
            <p class="text-2xl font-semibold text-rose-600">₱<?php echo number_format($lostRevenue); ?></p>
          </div>
        </div>

        <!-- ===== BOOKING TREND CHART ===== -->
        <div class="bg-white rounded-2xl border border-slate-200 p-5 mb-8">
          <div class="flex items-center justify-between mb-4">
            <h2 class="font-semibold text-lg flex items-center gap-2"><i
                class="fa-solid fa-chart-line text-amber-600"></i> booking trend</h2>
            <div class="flex gap-2">
              <select id="trendDataType" class="border border-slate-200 rounded-lg px-3 py-1 text-sm"
                onchange="toggleTrendChart()">
                <option value="count">Count</option>
                <option value="revenue">Revenue</option>
              </select>
              <select id="trendChartType" class="border border-slate-200 rounded-lg px-3 py-1 text-sm"
                onchange="toggleTrendChart()">
                <option value="combined">Combined</option>
                <option value="stacked">Stacked by type</option>
              </select>
            </div>
          </div>
          <div class="chart-container">
            <canvas id="trendChart"></canvas>
          </div>
        </div>

        <!-- ===== BOOKING BREAKDOWN ===== -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">

          <!-- by type (hotel vs restaurant) -->
          <div class="bg-white rounded-2xl border border-slate-200 p-5">
            <div class="flex items-center justify-between mb-3">
              <h2 class="font-semibold text-lg flex items-center gap-2"><i class="fas fa-chart-pie text-amber-600"></i>
                bookings by type</h2>
              <select id="typeChartType" class="border border-slate-200 rounded-lg px-3 py-1 text-sm"
                onchange="toggleTypeChart()">
                <option value="count">Count</option>
                <option value="revenue">Revenue</option>
              </select>
            </div>
            <div class="chart-container">
              <canvas id="typeChart"></canvas>
            </div>
          </div>

          <!-- by status -->
          <div class="bg-white rounded-2xl border border-slate-200 p-5">
            <div class="flex items-center justify-between mb-3">
              <h2 class="font-semibold text-lg flex items-center gap-2"><i class="fas fa-tag text-amber-600"></i>
                bookings by status</h2>
              <select id="statusChartType" class="border border-slate-200 rounded-lg px-3 py-1 text-sm"
                onchange="toggleStatusChart()">
                <option value="count">Count</option>
                <option value="revenue">Revenue</option>
              </select>
            </div>
            <div class="chart-container">
              <canvas id="statusChart"></canvas>
            </div>
          </div>
        </div>

        <!-- ===== RECENT ACTIVITY TABLE ===== -->
        <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden mb-8">
          <div class="p-4 border-b border-slate-200 bg-slate-50 flex items-center justify-between">
            <h2 class="font-semibold flex items-center gap-2"><i class="fas fa-rectangle-list text-amber-600"></i>
              recent activity</h2>
            <button onclick="exportData()"
              class="text-sm text-amber-700 border border-amber-600 px-3 py-1 rounded-lg hover:bg-amber-50">export</button>
          </div>
          <div class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead class="text-slate-500 text-xs border-b">
                <tr>
                  <td class="p-3">Type</td>
                  <td class="p-3">Reference</td>
                  <td class="p-3">Guest</td>
                  <td class="p-3">Details</td>
                  <td class="p-3">Date</td>
                  <td class="p-3">Amount</td>
                  <td class="p-3">Status</td>
                </tr>
              </thead>
              <tbody class="divide-y">
                <?php if (empty($recentActivity)): ?>
                  <tr>
                    <td colspan="7" class="p-6 text-center text-slate-400">No activity found for this period</td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($recentActivity as $item): ?>
                    <tr>
                      <td class="p-3">
                        <span
                          class="<?php echo $item['type'] == 'hotel' ? 'bg-amber-100 text-amber-700' : 'bg-green-100 text-green-700'; ?> px-2 py-0.5 rounded-full text-xs">
                          <?php echo $item['type'] == 'hotel' ? 'Hotel' : 'Restaurant'; ?>
                        </span>
                      </td>
                      <td class="p-3 font-medium"><?php echo htmlspecialchars($item['reference']); ?></td>
                      <td class="p-3"><?php echo htmlspecialchars($item['guest_name']); ?></td>
                      <td class="p-3"><?php echo htmlspecialchars($item['item_name']); ?></td>
                      <td class="p-3"><?php echo date('M d, Y', strtotime($item['created_at'])); ?></td>
                      <td class="p-3">₱<?php echo number_format($item['amount']); ?></td>
                      <td class="p-3">
                        <span class="<?php
                        echo $item['status'] == 'confirmed' ? 'bg-green-100 text-green-700' :
                          ($item['status'] == 'pending' ? 'bg-amber-100 text-amber-700' :
                            ($item['status'] == 'cancelled' ? 'bg-red-100 text-red-700' :
                              ($item['status'] == 'completed' ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-700')));
                        ?> px-2 py-0.5 rounded-full text-xs">
                          <?php echo ucfirst($item['status']); ?>
                        </span>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
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
          <button onclick="window.print()"
            class="bg-amber-600 text-white px-4 py-2 rounded-xl text-sm hover:bg-amber-700"><i
              class="fas fa-print mr-2"></i>Print</button>
        </div>
      </main>
    </div>

    <script>
      // Initialize charts
      document.addEventListener('DOMContentLoaded', function () {
        initTrendChart();
        initTypeChart();
        initStatusChart();
      });

      // Trend Chart
      let trendChart;
      function initTrendChart() {
        const ctx = document.getElementById('trendChart').getContext('2d');

        trendChart = new Chart(ctx, {
          type: 'line',
          data: {
            labels: <?php echo json_encode($trendLabels); ?>,
            datasets: [{
              label: 'Total Bookings',
              data: <?php echo json_encode($trendCounts); ?>,
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
              legend: {
                display: true,
                position: 'top'
              }
            },
            scales: {
              y: {
                beginAtZero: true,
                ticks: {
                  stepSize: 1
                }
              }
            }
          }
        });
      }

      function toggleTrendChart() {
        const dataType = document.getElementById('trendDataType').value;
        const chartType = document.getElementById('trendChartType').value;

        let datasets = [];

        if (chartType === 'combined') {
          const data = dataType === 'count' ? <?php echo json_encode($trendCounts); ?> : <?php echo json_encode($trendRevenue); ?>;
          const label = dataType === 'count' ? 'Total Bookings' : 'Total Revenue (₱)';

          datasets = [{
            label: label,
            data: data,
            borderColor: '#d97706',
            backgroundColor: 'rgba(217, 119, 6, 0.1)',
            tension: 0.4,
            fill: true
          }];
        } else {
          // Stacked by type
          if (dataType === 'count') {
            datasets = [
              {
                label: 'Hotel Bookings',
                data: <?php echo json_encode($hotelTrendCounts); ?>,
                borderColor: '#d97706',
                backgroundColor: 'rgba(217, 119, 6, 0.5)',
                tension: 0.4,
                stack: 'combined'
              },
              {
                label: 'Restaurant Reservations',
                data: <?php echo json_encode($restaurantTrendCounts); ?>,
                borderColor: '#10b981',
                backgroundColor: 'rgba(16, 185, 129, 0.5)',
                tension: 0.4,
                stack: 'combined'
              }
            ];
          } else {
            // Revenue data would need separate hotel/restaurant revenue arrays
            // For now, just show combined
            datasets = [{
              label: 'Total Revenue (₱)',
              data: <?php echo json_encode($trendRevenue); ?>,
              borderColor: '#d97706',
              backgroundColor: 'rgba(217, 119, 6, 0.1)',
              tension: 0.4,
              fill: true
            }];
          }
        }

        trendChart.data.datasets = datasets;
        trendChart.update();
      }

      // Type Chart (Hotel vs Restaurant)
      let typeChart;
      function initTypeChart() {
        const ctx = document.getElementById('typeChart').getContext('2d');

        typeChart = new Chart(ctx, {
          type: 'doughnut',
          data: {
            labels: <?php echo json_encode($typeLabels); ?>,
            datasets: [{
              data: <?php echo json_encode($typeCounts); ?>,
              backgroundColor: ['#d97706', '#10b981'],
              borderWidth: 0
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: {
                position: 'bottom'
              }
            }
          }
        });
      }

      function toggleTypeChart() {
        const type = document.getElementById('typeChartType').value;
        const data = type === 'count' ? <?php echo json_encode($typeCounts); ?> : <?php echo json_encode($typeRevenue); ?>;

        typeChart.data.datasets[0].data = data;
        typeChart.update();
      }

      // Status Chart
      let statusChart;
      function initStatusChart() {
        const ctx = document.getElementById('statusChart').getContext('2d');

        statusChart = new Chart(ctx, {
          type: 'doughnut',
          data: {
            labels: <?php echo json_encode($statusLabels); ?>,
            datasets: [{
              data: <?php echo json_encode($statusCounts); ?>,
              backgroundColor: [
                '#10b981', // confirmed - green
                '#f59e0b', // pending - amber
                '#ef4444', // cancelled - red
                '#3b82f6', // completed - blue
                '#6b7280'  // other - gray
              ],
              borderWidth: 0
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: {
                position: 'bottom'
              }
            }
          }
        });
      }

      function toggleStatusChart() {
        const type = document.getElementById('statusChartType').value;
        const data = type === 'count' ? <?php echo json_encode($statusCounts); ?> : <?php echo json_encode($statusRevenue); ?>;

        statusChart.data.datasets[0].data = data;
        statusChart.update();
      }

      // Export functions
      function exportData() {
        window.location.href = '../../../controller/admin/post/analytics/export_reports.php?type=combined&format=csv&start=<?php echo $start_date; ?>&end=<?php echo $end_date; ?>';
      }

      function exportPDF() {
        window.location.href = '../../../controller/admin/post/analytics/export_reports.php?type=combined&format=pdf&start=<?php echo $start_date; ?>&end=<?php echo $end_date; ?>';
      }

      function exportExcel() {
        window.location.href = '../../../controller/admin/post/analytics/export_reports.php?type=combined&format=excel&start=<?php echo $start_date; ?>&end=<?php echo $end_date; ?>';
      }
    </script>
  </body>

</html>