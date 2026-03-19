<?php
/**
 * View - Admin Customer Relationship (CRM)
 */
require_once '../../../controller/admin/get/customer_relationship.php';
$current_page = 'customer_relationship';
?>
<!DOCTYPE html>
<html lang="en">

  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin · Guest Relationship (CRM)</title>
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

      .guest-row {
        transition: all 0.2s ease;
      }

      .guest-row:hover {
        background-color: #fef3e2;
      }
    </style>
  </head>

  <body class="bg-white font-sans antialiased">

    <!-- APP CONTAINER: flex row (sidebar + main) -->
    <div class="min-h-screen flex flex-col lg:flex-row">

      <!-- ========== SIDEBAR ========== -->
      <?php require_once '../components/admin_nav.php'; ?>


      <!-- ========== MAIN CONTENT (GUEST RELATIONSHIP CRM) ========== -->
      <main class="flex-1 p-5 lg:p-8 overflow-y-auto bg-white">

        <!-- header with title and date -->
        <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
          <div>
            <h1 class="text-2xl lg:text-3xl font-light text-slate-800">Guest Relationship (CRM)</h1>
            <p class="text-sm text-slate-500 mt-0.5">manage guest profiles, preferences, and communication history</p>
          </div>
          <div class="flex gap-3 text-sm">
            <span class="bg-white border rounded-full px-4 py-2 flex items-center gap-2 shadow-sm"><i
                class="fas fa-calendar text-slate-400"></i> <?php echo $today; ?></span>
            <span class="bg-white border rounded-full px-4 py-2 shadow-sm"><i class="fas fa-bell"></i></span>
          </div>
        </div>

        <!-- ===== STATS CARDS ===== -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
          <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs text-slate-500">Total guests</p>
            <p class="text-2xl font-semibold"><?php echo number_format($stats['total_guests']); ?></p>
          </div>
          <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs text-slate-500">Active this month</p>
            <p class="text-2xl font-semibold"><?php echo number_format($stats['active_this_month']); ?></p>
          </div>
          <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs text-slate-500">VIP guests</p>
            <p class="text-2xl font-semibold text-amber-600"><?php echo number_format($stats['vip_guests']); ?></p>
          </div>
          <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs text-slate-500">New this week</p>
            <p class="text-2xl font-semibold text-green-600"><?php echo number_format($stats['new_this_week']); ?></p>
          </div>
          <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs text-slate-500">Retention rate</p>
            <p class="text-2xl font-semibold"><?php echo $stats['retention_rate']; ?>%</p>
          </div>
        </div>

        <!-- ===== ACTION BAR ===== -->
        <div
          class="bg-white rounded-2xl border border-slate-200 p-4 mb-6 flex flex-wrap gap-3 items-center justify-between">
          <div class="flex gap-2 flex-wrap">
            <button onclick="addGuest()"
              class="bg-amber-600 text-white px-4 py-2 rounded-xl text-sm hover:bg-amber-700 transition">+ add
              guest</button>
            <button onclick="importGuests()"
              class="border border-slate-200 px-4 py-2 rounded-xl text-sm hover:bg-slate-50 transition">import</button>
            <button onclick="exportGuests()"
              class="border border-slate-200 px-4 py-2 rounded-xl text-sm hover:bg-slate-50 transition">export</button>
            <button onclick="sendBulkEmail()"
              class="border border-slate-200 px-4 py-2 rounded-xl text-sm hover:bg-slate-50 transition">send
              email</button>
          </div>
          <div class="relative">
            <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
            <input type="text" id="searchInput" placeholder="search guests..."
              class="border border-slate-200 rounded-xl pl-9 pr-4 py-2 text-sm w-64 focus:ring-1 focus:ring-amber-500 outline-none">
          </div>
        </div>

        <!-- ===== GUEST LIST TABLE ===== -->
        <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden mb-8">
          <div class="p-4 border-b border-slate-200 bg-slate-50 flex items-center justify-between">
            <h2 class="font-semibold flex items-center gap-2"><i class="fas fa-address-book text-amber-600"></i>
              guest directory</h2>
            <div class="flex gap-2">
              <button onclick="showFilter()"
                class="text-sm text-amber-700 border border-amber-600 px-3 py-1 rounded-lg hover:bg-amber-50 transition">
                <i class="fa-solid fa-filter mr-1"></i> filter
              </button>
              <?php if ($tierFilter !== 'all' || $stayFilter > 0 || !empty($searchFilter)): ?>
                <button onclick="clearFilters()"
                  class="text-sm text-slate-600 border border-slate-200 px-3 py-1 rounded-lg hover:bg-slate-50 transition">
                  <i class="fa-solid fa-times mr-1"></i> clear filters
                </button>
              <?php endif; ?>
            </div>
          </div>
          <div class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead class="text-slate-500 text-xs border-b">
                <tr>
                  <td class="p-3">Guest</td>
                  <td class="p-3">Contact</td>
                  <td class="p-3">Last stay</td>
                  <td class="p-3">Total stays</td>
                  <td class="p-3">Tier</td>
                  <td class="p-3">Preferences</td>
                  <td class="p-3">Actions</td>
                </tr>
              </thead>
              <tbody id="guestsTableBody" class="divide-y">
                <?php if (empty($guests)): ?>
                  <tr>
                    <td colspan="7" class="p-6 text-center text-slate-400">No guests found</td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($guests as $guest):
                    $tierColors = [
                      'platinum' => 'bg-purple-100 text-purple-700',
                      'gold' => 'bg-amber-100 text-amber-700',
                      'silver' => 'bg-slate-100 text-slate-600',
                      'bronze' => 'bg-orange-100 text-orange-700'
                    ];
                    $tierClass = $tierColors[strtolower($guest['member_tier'])] ?? 'bg-slate-100 text-slate-600';

                    $bgColors = ['bg-purple-200', 'bg-amber-200', 'bg-blue-200', 'bg-green-200', 'bg-pink-200'];
                    $textColors = ['text-purple-800', 'text-amber-800', 'text-blue-800', 'text-green-800', 'text-pink-800'];
                    $randIndex = abs(crc32($guest['id'])) % 5;
                    $bgColor = $bgColors[$randIndex];
                    $textColor = $textColors[$randIndex];
                    ?>
                    <tr class="guest-row" data-name="<?php echo strtolower($guest['full_name']); ?>"
                      data-email="<?php echo strtolower($guest['email']); ?>">
                      <td class="p-3">
                        <div class="flex items-center gap-2">
                          <div
                            class="h-8 w-8 rounded-full <?php echo $bgColor; ?> flex items-center justify-center <?php echo $textColor; ?> font-bold text-xs">
                            <?php echo $guest['initials'] ?: substr($guest['full_name'], 0, 2); ?>
                          </div>
                          <div>
                            <span class="font-medium"><?php echo htmlspecialchars($guest['full_name']); ?></span>
                            <p class="text-xs text-slate-400">ID:
                              #G<?php echo str_pad($guest['id'], 5, '0', STR_PAD_LEFT); ?></p>
                          </div>
                        </div>
                      </td>
                      <td class="p-3">
                        <?php echo htmlspecialchars($guest['email']); ?><br>
                        <span class="text-xs"><?php echo htmlspecialchars($guest['phone'] ?? 'No phone'); ?></span>
                      </td>
                      <td class="p-3">
                        <?php echo $guest['last_stay'] ? date('M d, Y', strtotime($guest['last_stay'])) : 'Never'; ?>
                      </td>
                      <td class="p-3"><?php echo $guest['total_stays']; ?></td>
                      <td class="p-3"><span
                          class="<?php echo $tierClass; ?> px-2 py-0.5 rounded-full text-xs"><?php echo ucfirst($guest['member_tier']); ?></span>
                      </td>
                      <td class="p-3 max-w-[200px] truncate"><?php echo htmlspecialchars($guest['preferences'] ?? '—'); ?>
                      </td>
                      <td class="p-3">
                        <button onclick="viewGuest(<?php echo $guest['id']; ?>)"
                          class="text-amber-700 text-xs hover:underline mr-2">view</button>
                        <button onclick="messageGuest(<?php echo $guest['id']; ?>)"
                          class="text-blue-600 text-xs hover:underline">message</button>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
          <div class="p-4 border-t border-slate-200 flex items-center justify-between">
            <span class="text-xs text-slate-500">Showing <?php echo count($guests); ?> of
              <?php echo number_format($totalGuests); ?> guests</span>
            <div class="flex gap-2">
              <button onclick="changePage(<?php echo $currentPage - 1; ?>)"
                class="border border-slate-200 px-3 py-1 rounded-lg text-sm hover:bg-slate-50 transition" <?php echo $currentPage <= 1 ? 'disabled' : ''; ?>>Previous</button>
              <?php for ($i = 1; $i <= min(5, $totalPages); $i++): ?>
                <button onclick="changePage(<?php echo $i; ?>)"
                  class="border <?php echo $i == $currentPage ? 'bg-amber-600 text-white' : 'border-slate-200 hover:bg-slate-50'; ?> px-3 py-1 rounded-lg text-sm transition"><?php echo $i; ?></button>
              <?php endfor; ?>
              <?php if ($totalPages > 5): ?>
                <span class="px-2">...</span>
              <?php endif; ?>
              <button onclick="changePage(<?php echo $currentPage + 1; ?>)"
                class="border border-slate-200 px-3 py-1 rounded-lg text-sm hover:bg-slate-50 transition" <?php echo $currentPage >= $totalPages ? 'disabled' : ''; ?>>Next</button>
            </div>
          </div>
        </div>

        <!-- ===== BOTTOM: RECENT INTERACTIONS & BIRTHDAYS ===== -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

          <!-- recent guest interactions -->
          <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 p-5">
            <h2 class="font-semibold text-lg flex items-center gap-2 mb-3"><i
                class="fas fa-clock text-amber-600"></i> recent interactions</h2>
            <div class="space-y-3">
              <?php if (empty($recentInteractions)): ?>
                <p class="text-sm text-slate-400 text-center py-4">No recent interactions</p>
              <?php else: ?>
                <?php foreach ($recentInteractions as $interaction):
                  $colors = ['bg-purple-200', 'bg-amber-200', 'bg-blue-200', 'bg-green-200'];
                  $randColor = $colors[array_rand($colors)];
                  ?>
                  <div class="flex items-center gap-3 border-b pb-2 last:border-0">
                    <div
                      class="h-8 w-8 rounded-full <?php echo $randColor; ?> flex items-center justify-center text-xs font-bold flex-shrink-0">
                      <?php echo $interaction['initials'] ?: substr($interaction['user_name'], 0, 2); ?>
                    </div>
                    <div class="flex-1">
                      <p class="font-medium text-sm"><?php echo htmlspecialchars($interaction['user_name']); ?></p>
                      <p class="text-xs text-slate-500"><?php echo htmlspecialchars($interaction['description']); ?></p>
                    </div>
                    <span class="text-xs text-slate-400"><?php echo $interaction['time_ago']; ?></span>
                  </div>
                <?php endforeach; ?>
              <?php endif; ?>
            </div>
          </div>

          <!-- upcoming birthdays / special occasions -->
          <div class="bg-amber-50 border border-amber-200 rounded-2xl p-5">
            <h3 class="font-semibold flex items-center gap-2 mb-3"><i
                class="fas fa-cake-candles text-amber-600"></i> upcoming celebrations</h3>
            <ul class="space-y-2">
              <?php if (empty($celebrations)): ?>
                <li class="text-sm text-slate-500 italic">No upcoming celebrations</li>
              <?php else: ?>
                <?php foreach ($celebrations as $celeb): ?>
                  <li class="flex justify-between items-center">
                    <span><?php echo htmlspecialchars($celeb['full_name']); ?></span>
                    <span class="text-xs text-amber-600"><?php echo $celeb['type']; ?> ·
                      <?php echo $celeb['date']; ?></span>
                  </li>
                <?php endforeach; ?>
              <?php endif; ?>
            </ul>
            <button onclick="sendBirthdayGreetings()"
              class="w-full border border-amber-600 text-amber-700 rounded-xl py-2 text-sm mt-4 hover:bg-amber-100 transition">send
              greetings</button>
          </div>
        </div>
      </main>
    </div>

    <script>
      // Global variables
      let currentPage = <?php echo $currentPage; ?>;
      let totalPages = <?php echo $totalPages; ?>;

      let currentTier = '<?php echo $tierFilter; ?>';
      let currentStay = <?php echo $stayFilter; ?>;
      let currentSearch = '<?php echo $searchFilter; ?>';

      // ========== SEARCH FUNCTIONALITY ==========
      document.addEventListener('DOMContentLoaded', function () {
        // Set search input value from URL
        const searchInput = document.getElementById('searchInput');
        if (searchInput && currentSearch) {
          searchInput.value = currentSearch;
        }

        // Search on Enter key
        if (searchInput) {
          searchInput.addEventListener('keypress', function (e) {
            if (e.key === 'Enter') {
              performSearch();
            }
          });

          // Add search button
          const searchBtn = document.createElement('button');
          searchBtn.className = 'absolute right-2 top-1/2 -translate-y-1/2 bg-amber-600 text-white px-3 py-1 rounded-lg text-xs hover:bg-amber-700 transition';
          searchBtn.innerHTML = '<i class="fa-solid fa-search"></i>';
          searchBtn.onclick = performSearch;
          searchInput.parentNode.appendChild(searchBtn);
        }
      });

      // Perform search
      function performSearch() {
        const search = document.getElementById('searchInput').value;
        const url = new URL(window.location);
        if (search) {
          url.searchParams.set('search', search);
        } else {
          url.searchParams.delete('search');
        }
        // Preserve existing filters
        if (currentTier !== 'all') url.searchParams.set('tier', currentTier);
        if (currentStay > 0) url.searchParams.set('stay', currentStay);
        window.location.href = url.toString();
      }

      // ========== PAGINATION ==========
      function changePage(page) {
        if (page < 1 || page > totalPages) return;
        const url = new URL(window.location);
        url.searchParams.set('page', page);
        // Preserve filters
        if (currentTier !== 'all') url.searchParams.set('tier', currentTier);
        if (currentStay > 0) url.searchParams.set('stay', currentStay);
        if (currentSearch) url.searchParams.set('search', currentSearch);
        window.location.href = url.toString();
      }

      // ========== FILTER FUNCTIONALITY ==========
      function showFilter() {
        Swal.fire({
          title: 'Filter Guests',
          html: `
                <div class="text-left space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Membership Tier</label>
                        <select id="filterTier" class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:ring-1 focus:ring-amber-500 outline-none">
                            <option value="all" ${currentTier === 'all' ? 'selected' : ''}>All Tiers</option>
                            <option value="platinum" ${currentTier === 'platinum' ? 'selected' : ''}>Platinum</option>
                            <option value="gold" ${currentTier === 'gold' ? 'selected' : ''}>Gold</option>
                            <option value="silver" ${currentTier === 'silver' ? 'selected' : ''}>Silver</option>
                            <option value="bronze" ${currentTier === 'bronze' ? 'selected' : ''}>Bronze</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Last Activity</label>
                        <select id="filterLastStay" class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:ring-1 focus:ring-amber-500 outline-none">
                            <option value="0" ${currentStay == 0 ? 'selected' : ''}>Any time</option>
                            <option value="7" ${currentStay == 7 ? 'selected' : ''}>Last 7 days</option>
                            <option value="30" ${currentStay == 30 ? 'selected' : ''}>Last 30 days</option>
                            <option value="90" ${currentStay == 90 ? 'selected' : ''}>Last 90 days</option>
                        </select>
                    </div>
                    <div class="flex gap-2 pt-2">
                        <button onclick="applyFilters()" class="flex-1 bg-amber-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-amber-700 transition">Apply Filters</button>
                        <button onclick="clearFilters()" class="flex-1 border border-slate-300 text-slate-700 px-4 py-2 rounded-lg text-sm hover:bg-slate-50 transition">Clear All</button>
                    </div>
                </div>
            `,
          showConfirmButton: false,
          showCloseButton: true,
          width: '400px'
        });
      }

      // Apply filters from modal
      function applyFilters() {
        const tier = document.getElementById('filterTier').value;
        const stay = document.getElementById('filterLastStay').value;

        const url = new URL(window.location);
        if (tier !== 'all') url.searchParams.set('tier', tier);
        else url.searchParams.delete('tier');

        if (stay > 0) url.searchParams.set('stay', stay);
        else url.searchParams.delete('stay');

        // Preserve search if exists
        if (currentSearch) url.searchParams.set('search', currentSearch);

        window.location.href = url.toString();
      }

      // Clear all filters
      function clearFilters() {
        window.location.href = window.location.pathname;
      }

      // ========== GUEST MANAGEMENT ==========
      function addGuest() {
        Swal.fire({
          title: 'Add New Guest',
          html: `
                <div class="text-left space-y-3 max-h-96 overflow-y-auto px-1">
                    <div>
                        <label class="text-sm font-medium text-slate-700">Full Name *</label>
                        <input type="text" id="fullName" class="w-full border border-slate-300 rounded-lg px-3 py-2 mt-1 focus:ring-1 focus:ring-amber-500 outline-none" placeholder="e.g. John Doe">
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-700">Email *</label>
                        <input type="email" id="email" class="w-full border border-slate-300 rounded-lg px-3 py-2 mt-1 focus:ring-1 focus:ring-amber-500 outline-none" placeholder="guest@email.com">
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-700">Phone</label>
                        <input type="text" id="phone" class="w-full border border-slate-300 rounded-lg px-3 py-2 mt-1 focus:ring-1 focus:ring-amber-500 outline-none" placeholder="+63 912 345 6789">
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-700">Birthday</label>
                        <input type="date" id="birthday" class="w-full border border-slate-300 rounded-lg px-3 py-2 mt-1 focus:ring-1 focus:ring-amber-500 outline-none">
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-700">Preferences</label>
                        <textarea id="preferences" class="w-full border border-slate-300 rounded-lg px-3 py-2 mt-1 focus:ring-1 focus:ring-amber-500 outline-none" rows="2" placeholder="e.g. High floor, extra pillows"></textarea>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-700">Allergies</label>
                        <input type="text" id="allergies" class="w-full border border-slate-300 rounded-lg px-3 py-2 mt-1 focus:ring-1 focus:ring-amber-500 outline-none" placeholder="e.g. Peanuts, shellfish">
                    </div>
                </div>
            `,
          showCancelButton: true,
          confirmButtonColor: '#d97706',
          cancelButtonColor: '#6b7280',
          confirmButtonText: 'Add Guest',
          preConfirm: () => {
            const name = document.getElementById('fullName').value;
            const email = document.getElementById('email').value;
            if (!name || !email) {
              Swal.showValidationMessage('Name and email are required');
              return false;
            }
            return {
              name: name,
              email: email,
              phone: document.getElementById('phone').value,
              birthday: document.getElementById('birthday').value,
              preferences: document.getElementById('preferences').value,
              allergies: document.getElementById('allergies').value
            };
          }
        }).then((result) => {
          if (result.isConfirmed) {
            Swal.fire({
              title: 'Adding Guest...',
              text: 'Please wait',
              allowOutsideClick: false,
              didOpen: () => {
                Swal.showLoading();
              }
            });

            const formData = new FormData();
            formData.append('action', 'add_guest');
            formData.append('full_name', result.value.name);
            formData.append('email', result.value.email);
            formData.append('phone', result.value.phone);
            formData.append('birthday', result.value.birthday);
            formData.append('preferences', result.value.preferences);
            formData.append('allergies', result.value.allergies);

            fetch('../../../controller/admin/post/customer_actions.php', {
              method: 'POST',
              body: formData
            })
              .then(response => response.json())
              .then(data => {
                if (data.success) {
                  Swal.fire({
                    title: 'Success!',
                    html: `
                                <p>${data.message}</p>
                                <div class="mt-3 p-3 bg-amber-50 rounded-lg">
                                    <p class="text-sm font-medium">Temporary Password:</p>
                                    <p class="text-lg font-bold text-amber-700">${data.temp_password}</p>
                                    <p class="text-xs text-slate-500 mt-1">Please share this with the guest.</p>
                                </div>
                            `,
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

      // View Guest Details
      function viewGuest(userId) {
        Swal.fire({
          title: 'Loading...',
          text: 'Please wait',
          allowOutsideClick: false,
          didOpen: () => {
            Swal.showLoading();
          }
        });

        const formData = new FormData();
        formData.append('action', 'get_guest');
        formData.append('user_id', userId);

        fetch('../../../controller/admin/post/customer_actions.php', {
          method: 'POST',
          body: formData
        })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              let bookingsHtml = '';
              if (data.bookings.length > 0) {
                data.bookings.forEach(b => {
                  bookingsHtml += `<li class="text-sm border-b border-slate-100 py-1">${b.room_name} · ${new Date(b.check_in).toLocaleDateString()} - ${new Date(b.check_out).toLocaleDateString()}</li>`;
                });
              } else {
                bookingsHtml = '<li class="text-sm text-slate-500">No bookings yet</li>';
              }

              let redemptionsHtml = '';
              if (data.redemptions.length > 0) {
                data.redemptions.forEach(r => {
                  redemptionsHtml += `<li class="text-sm border-b border-slate-100 py-1">${r.reward_name} · ${r.points_cost} pts (${new Date(r.created_at).toLocaleDateString()})</li>`;
                });
              } else {
                redemptionsHtml = '<li class="text-sm text-slate-500">No redemptions yet</li>';
              }

              let notificationsHtml = '';
              if (data.notifications && data.notifications.length > 0) {
                data.notifications.forEach(n => {
                  notificationsHtml += `<li class="text-sm border-b border-slate-100 py-1">${n.title} · ${new Date(n.created_at).toLocaleDateString()}</li>`;
                });
              } else {
                notificationsHtml = '<li class="text-sm text-slate-500">No notifications</li>';
              }

              Swal.fire({
                title: 'Guest Details',
                html: `
                        <div class="text-left max-h-96 overflow-y-auto px-2">
                            <div class="grid grid-cols-2 gap-3 mb-4">
                                <div class="col-span-2 bg-amber-50 p-3 rounded-lg">
                                    <p class="font-semibold text-lg">${data.guest.full_name}</p>
                                    <p class="text-sm text-slate-600">${data.guest.email}</p>
                                    <p class="text-sm text-slate-600">${data.guest.phone || 'No phone'}</p>
                                </div>
                                
                                <div class="border rounded-lg p-2">
                                    <p class="text-xs text-slate-500">Points</p>
                                    <p class="font-bold text-lg">${data.guest.loyalty_points}</p>
                                </div>
                                <div class="border rounded-lg p-2">
                                    <p class="text-xs text-slate-500">Tier</p>
                                    <p class="font-bold text-lg capitalize">${data.guest.member_tier}</p>
                                </div>
                                <div class="border rounded-lg p-2">
                                    <p class="text-xs text-slate-500">Total Stays</p>
                                    <p class="font-bold text-lg">${data.guest.total_stays}</p>
                                </div>
                                <div class="border rounded-lg p-2">
                                    <p class="text-xs text-slate-500">Total Spent</p>
                                    <p class="font-bold text-lg">₱${data.guest.total_spent.toLocaleString()}</p>
                                </div>
                            </div>

                            <div class="mb-3">
                                <p class="font-medium text-sm mb-1">Personal Information</p>
                                <div class="grid grid-cols-2 gap-2 text-sm bg-slate-50 p-2 rounded">
                                    <p><span class="text-slate-500">Member since:</span> ${new Date(data.guest.created_at).toLocaleDateString()}</p>
                                    <p><span class="text-slate-500">Last login:</span> ${data.guest.last_login ? new Date(data.guest.last_login).toLocaleDateString() : 'Never'}</p>
                                    <p><span class="text-slate-500">Birthday:</span> ${data.guest.birthday ? new Date(data.guest.birthday).toLocaleDateString() : 'Not set'}</p>
                                    <p><span class="text-slate-500">Anniversary:</span> ${data.guest.anniversary ? new Date(data.guest.anniversary).toLocaleDateString() : 'Not set'}</p>
                                </div>
                            </div>

                            <div class="mb-3">
                                <p class="font-medium text-sm mb-1">Preferences</p>
                                <p class="text-sm bg-slate-50 p-2 rounded">${data.guest.preferences || 'None'}</p>
                            </div>

                            <div class="mb-3">
                                <p class="font-medium text-sm mb-1">Allergies</p>
                                <p class="text-sm bg-slate-50 p-2 rounded">${data.guest.allergies || 'None'}</p>
                            </div>

                            <div class="mb-3">
                                <p class="font-medium text-sm mb-1">Recent Bookings</p>
                                <ul class="text-sm bg-slate-50 p-2 rounded list-disc pl-4">
                                    ${bookingsHtml}
                                </ul>
                            </div>

                            <div class="mb-3">
                                <p class="font-medium text-sm mb-1">Recent Redemptions</p>
                                <ul class="text-sm bg-slate-50 p-2 rounded list-disc pl-4">
                                    ${redemptionsHtml}
                                </ul>
                            </div>

                            <div class="mb-3">
                                <p class="font-medium text-sm mb-1">Recent Notifications</p>
                                <ul class="text-sm bg-slate-50 p-2 rounded list-disc pl-4">
                                    ${notificationsHtml}
                                </ul>
                            </div>
                        </div>
                    `,
                confirmButtonColor: '#d97706',
                confirmButtonText: 'Close',
                showCancelButton: true,
                cancelButtonText: 'Edit',
                cancelButtonColor: '#6b7280',
                width: '600px'
              }).then((result) => {
                if (result.dismiss === Swal.DismissReason.cancel) {
                  editGuest(data.guest);
                }
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

      // Edit Guest
      function editGuest(guest) {
        Swal.fire({
          title: 'Edit Guest',
          html: `
                <div class="text-left space-y-3 max-h-96 overflow-y-auto px-1">
                    <div>
                        <label class="text-sm font-medium text-slate-700">Full Name *</label>
                        <input type="text" id="fullName" class="w-full border border-slate-300 rounded-lg px-3 py-2 mt-1" value="${guest.full_name.replace(/"/g, '&quot;')}">
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-700">Email *</label>
                        <input type="email" id="email" class="w-full border border-slate-300 rounded-lg px-3 py-2 mt-1" value="${guest.email}">
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-700">Phone</label>
                        <input type="text" id="phone" class="w-full border border-slate-300 rounded-lg px-3 py-2 mt-1" value="${guest.phone || ''}">
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-700">Birthday</label>
                        <input type="date" id="birthday" class="w-full border border-slate-300 rounded-lg px-3 py-2 mt-1" value="${guest.birthday || ''}">
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-700">Anniversary</label>
                        <input type="date" id="anniversary" class="w-full border border-slate-300 rounded-lg px-3 py-2 mt-1" value="${guest.anniversary || ''}">
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-700">Preferences</label>
                        <textarea id="preferences" class="w-full border border-slate-300 rounded-lg px-3 py-2 mt-1" rows="2">${guest.preferences || ''}</textarea>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-700">Allergies</label>
                        <input type="text" id="allergies" class="w-full border border-slate-300 rounded-lg px-3 py-2 mt-1" value="${guest.allergies || ''}">
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-700">Address</label>
                        <input type="text" id="address" class="w-full border border-slate-300 rounded-lg px-3 py-2 mt-1" value="${guest.address || ''}">
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-700">City</label>
                        <input type="text" id="city" class="w-full border border-slate-300 rounded-lg px-3 py-2 mt-1" value="${guest.city || ''}">
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-700">Postal Code</label>
                        <input type="text" id="postal" class="w-full border border-slate-300 rounded-lg px-3 py-2 mt-1" value="${guest.postal_code || ''}">
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-700">Country</label>
                        <input type="text" id="country" class="w-full border border-slate-300 rounded-lg px-3 py-2 mt-1" value="${guest.country || 'Philippines'}">
                    </div>
                </div>
            `,
          showCancelButton: true,
          confirmButtonColor: '#d97706',
          cancelButtonColor: '#6b7280',
          confirmButtonText: 'Update Guest',
          preConfirm: () => {
            const name = document.getElementById('fullName').value;
            const email = document.getElementById('email').value;
            if (!name || !email) {
              Swal.showValidationMessage('Name and email are required');
              return false;
            }
            return {
              user_id: guest.id,
              name: name,
              email: email,
              phone: document.getElementById('phone').value,
              birthday: document.getElementById('birthday').value,
              anniversary: document.getElementById('anniversary').value,
              preferences: document.getElementById('preferences').value,
              allergies: document.getElementById('allergies').value,
              address: document.getElementById('address').value,
              city: document.getElementById('city').value,
              postal: document.getElementById('postal').value,
              country: document.getElementById('country').value
            };
          }
        }).then((result) => {
          if (result.isConfirmed) {
            Swal.fire({
              title: 'Updating...',
              text: 'Please wait',
              allowOutsideClick: false,
              didOpen: () => {
                Swal.showLoading();
              }
            });

            const formData = new FormData();
            formData.append('action', 'update_guest');
            formData.append('user_id', result.value.user_id);
            formData.append('full_name', result.value.name);
            formData.append('email', result.value.email);
            formData.append('phone', result.value.phone);
            formData.append('birthday', result.value.birthday);
            formData.append('anniversary', result.value.anniversary);
            formData.append('preferences', result.value.preferences);
            formData.append('allergies', result.value.allergies);
            formData.append('address', result.value.address);
            formData.append('city', result.value.city);
            formData.append('postal_code', result.value.postal);
            formData.append('country', result.value.country);

            fetch('../../../controller/admin/post/customer_actions.php', {
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

      // ========== MESSAGING ==========
      function messageGuest(userId) {
        Swal.fire({
          title: 'Send Message',
          html: `
                <div class="text-left space-y-3">
                    <div>
                        <label class="text-sm font-medium text-slate-700">Subject</label>
                        <input type="text" id="subject" class="w-full border border-slate-300 rounded-lg px-3 py-2 mt-1" placeholder="e.g. Welcome offer">
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-700">Message</label>
                        <textarea id="message" class="w-full border border-slate-300 rounded-lg px-3 py-2 mt-1" rows="4" placeholder="Type your message..."></textarea>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-700">Send via</label>
                        <select id="messageType" class="w-full border border-slate-300 rounded-lg px-3 py-2 mt-1">
                            <option value="email">Email only</option>
                            <option value="sms">SMS only</option>
                            <option value="both">Both email and SMS</option>
                        </select>
                    </div>
                </div>
            `,
          showCancelButton: true,
          confirmButtonColor: '#d97706',
          cancelButtonColor: '#6b7280',
          confirmButtonText: 'Send Message',
          preConfirm: () => {
            const subject = document.getElementById('subject').value;
            const message = document.getElementById('message').value;
            if (!subject || !message) {
              Swal.showValidationMessage('Subject and message are required');
              return false;
            }
            return {
              user_id: userId,
              subject: subject,
              message: message,
              type: document.getElementById('messageType').value
            };
          }
        }).then((result) => {
          if (result.isConfirmed) {
            Swal.fire({
              title: 'Sending...',
              text: 'Please wait',
              allowOutsideClick: false,
              didOpen: () => {
                Swal.showLoading();
              }
            });

            const formData = new FormData();
            formData.append('action', 'send_message');
            formData.append('user_id', result.value.user_id);
            formData.append('subject', result.value.subject);
            formData.append('message', result.value.message);
            formData.append('type', result.value.type);

            fetch('../../../controller/admin/post/customer_actions.php', {
              method: 'POST',
              body: formData
            })
              .then(response => response.json())
              .then(data => {
                if (data.success) {
                  Swal.fire({
                    title: 'Sent!',
                    text: data.message,
                    icon: 'success',
                    confirmButtonColor: '#d97706'
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

      // ========== BIRTHDAY GREETINGS ==========
      function sendBirthdayGreetings() {
        Swal.fire({
          title: 'Send Birthday Greetings',
          html: `
                <div class="text-left">
                    <p class="mb-3 text-slate-600">Send birthday wishes to all guests with upcoming birthdays?</p>
                    <div class="mb-3">
                        <label class="text-sm font-medium text-slate-700">Custom Message (optional)</label>
                        <textarea id="customMessage" class="w-full border border-slate-300 rounded-lg px-3 py-2 mt-1" rows="3" 
                            placeholder="Happy Birthday! We hope you have a wonderful day..."></textarea>
                    </div>
                    <div class="text-xs text-slate-500 bg-slate-50 p-2 rounded">
                        <p><i class="fa-solid fa-info-circle mr-1"></i> Use [name] to insert guest's name.</p>
                        <p>Default message will be used if left empty.</p>
                    </div>
                </div>
            `,
          showCancelButton: true,
          confirmButtonColor: '#d97706',
          cancelButtonColor: '#6b7280',
          confirmButtonText: 'Send Greetings',
          preConfirm: () => {
            return {
              message: document.getElementById('customMessage')?.value || ''
            };
          }
        }).then((result) => {
          if (result.isConfirmed) {
            Swal.fire({
              title: 'Sending...',
              text: 'Please wait',
              allowOutsideClick: false,
              didOpen: () => {
                Swal.showLoading();
              }
            });

            const formData = new FormData();
            formData.append('action', 'send_birthday_greetings');
            formData.append('custom_message', result.value.message);

            fetch('../../../controller/admin/post/customer_actions.php', {
              method: 'POST',
              body: formData
            })
              .then(response => response.json())
              .then(data => {
                if (data.success) {
                  let message = data.message;
                  if (data.sent && data.sent.length > 0) {
                    message += '<br><br><span class="font-medium">Sent to:</span><br>' + data.sent.join('<br>');
                  }
                  Swal.fire({
                    title: 'Success!',
                    html: message,
                    icon: 'success',
                    confirmButtonColor: '#d97706'
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

      // Send Anniversary Greetings
      function sendAnniversaryGreetings() {
        Swal.fire({
          title: 'Send Anniversary Greetings',
          html: `
                <div class="text-left">
                    <p class="mb-3 text-slate-600">Send anniversary wishes to all guests with upcoming anniversaries?</p>
                    <div class="mb-3">
                        <label class="text-sm font-medium text-slate-700">Custom Message (optional)</label>
                        <textarea id="customMessage" class="w-full border border-slate-300 rounded-lg px-3 py-2 mt-1" rows="3" 
                            placeholder="Happy Anniversary! We're honored to celebrate with you..."></textarea>
                    </div>
                    <div class="text-xs text-slate-500 bg-slate-50 p-2 rounded">
                        <p><i class="fa-solid fa-info-circle mr-1"></i> Use [name] to insert guest's name.</p>
                        <p>Default message will be used if left empty.</p>
                    </div>
                </div>
            `,
          showCancelButton: true,
          confirmButtonColor: '#d97706',
          cancelButtonColor: '#6b7280',
          confirmButtonText: 'Send Greetings',
          preConfirm: () => {
            return {
              message: document.getElementById('customMessage')?.value || ''
            };
          }
        }).then((result) => {
          if (result.isConfirmed) {
            Swal.fire({
              title: 'Sending...',
              text: 'Please wait',
              allowOutsideClick: false,
              didOpen: () => {
                Swal.showLoading();
              }
            });

            const formData = new FormData();
            formData.append('action', 'send_anniversary_greetings');
            formData.append('custom_message', result.value.message);

            fetch('../../../controller/admin/post/customer_actions.php', {
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

      // ========== BULK EMAIL ==========
      function sendBulkEmail() {
        Swal.fire({
          title: 'Send Bulk Email',
          html: `
                <div class="text-left space-y-3">
                    <div>
                        <label class="text-sm font-medium text-slate-700">Subject</label>
                        <input type="text" id="bulkSubject" class="w-full border border-slate-300 rounded-lg px-3 py-2 mt-1" placeholder="e.g. Special offer">
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-700">Message</label>
                        <textarea id="bulkMessage" class="w-full border border-slate-300 rounded-lg px-3 py-2 mt-1" rows="4" placeholder="Type your message... Use [name] for personalization"></textarea>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-700">Send to</label>
                        <select id="bulkFilter" class="w-full border border-slate-300 rounded-lg px-3 py-2 mt-1">
                            <option value="all">All guests</option>
                            <option value="active">Active this month</option>
                            <option value="vip">VIP (Gold/Platinum)</option>
                            <option value="new">New this week</option>
                        </select>
                    </div>
                    <div class="text-xs text-slate-500 bg-slate-50 p-2 rounded">
                        <i class="fa-solid fa-info-circle mr-1"></i> Use [name] to personalize each message.
                    </div>
                </div>
            `,
          showCancelButton: true,
          confirmButtonColor: '#d97706',
          cancelButtonColor: '#6b7280',
          confirmButtonText: 'Send',
          preConfirm: () => {
            const subject = document.getElementById('bulkSubject').value;
            const message = document.getElementById('bulkMessage').value;
            if (!subject || !message) {
              Swal.showValidationMessage('Subject and message are required');
              return false;
            }
            return {
              subject: subject,
              message: message,
              filter: document.getElementById('bulkFilter').value
            };
          }
        }).then((result) => {
          if (result.isConfirmed) {
            Swal.fire({
              title: 'Sending...',
              text: 'Please wait',
              allowOutsideClick: false,
              didOpen: () => {
                Swal.showLoading();
              }
            });

            const formData = new FormData();
            formData.append('action', 'send_bulk_email');
            formData.append('subject', result.value.subject);
            formData.append('message', result.value.message);
            formData.append('filter', result.value.filter);

            fetch('../../../controller/admin/post/customer_actions.php', {
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

      // ========== IMPORT/EXPORT ==========
      function exportGuests() {
        Swal.fire({
          title: 'Export Guests',
          html: `
                <div class="text-left space-y-3">
                    <div>
                        <label class="text-sm font-medium text-slate-700">Filter</label>
                        <select id="exportFilter" class="w-full border border-slate-300 rounded-lg px-3 py-2 mt-1">
                            <option value="all">All guests</option>
                            <option value="active">Active this month</option>
                            <option value="vip">VIP (Gold/Platinum)</option>
                            <option value="new">New this week</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-700">Format</label>
                        <select id="exportFormat" class="w-full border border-slate-300 rounded-lg px-3 py-2 mt-1">
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
              filter: document.getElementById('exportFilter').value,
              format: document.getElementById('exportFormat').value
            };
          }
        }).then((result) => {
          if (result.isConfirmed) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '../../controller/admin/post/customer_actions.php';
            form.innerHTML = `
                    <input type="hidden" name="action" value="export_guests">
                    <input type="hidden" name="filter" value="${result.value.filter}">
                    <input type="hidden" name="format" value="${result.value.format}">
                `;
            document.body.appendChild(form);
            form.submit();
            document.body.removeChild(form);
          }
        });
      }

      function importGuests() {
        Swal.fire({
          title: 'Import Guests',
          html: `
                <div class="text-left">
                    <p class="mb-2 text-sm text-slate-600">Upload a CSV file with the following columns:</p>
                    <div class="bg-slate-50 p-2 rounded text-xs font-mono mb-3">
                        full_name, email, phone, birthday, preferences, allergies, address, city, country
                    </div>
                    <input type="file" id="csvFile" accept=".csv" class="w-full border border-slate-300 rounded-lg px-3 py-2">
                </div>
            `,
          showCancelButton: true,
          confirmButtonColor: '#d97706',
          cancelButtonColor: '#6b7280',
          confirmButtonText: 'Upload & Import',
          preConfirm: () => {
            const file = document.getElementById('csvFile').files[0];
            if (!file) {
              Swal.showValidationMessage('Please select a CSV file');
              return false;
            }
            return file;
          }
        }).then((result) => {
          if (result.isConfirmed) {
            Swal.fire({
              title: 'Importing...',
              text: 'Please wait',
              allowOutsideClick: false,
              didOpen: () => {
                Swal.showLoading();
              }
            });

            const formData = new FormData();
            formData.append('action', 'import_guests');
            formData.append('csv_file', result.value);

            fetch('../../../controller/admin/post/customer_actions.php', {
              method: 'POST',
              body: formData
            })
              .then(response => response.json())
              .then(data => {
                if (data.success) {
                  let message = data.message;
                  if (data.errors && data.errors.length > 0) {
                    message += '<br><br><span class="font-medium text-red-600">Errors:</span><br>' + data.errors.join('<br>');
                  }
                  if (data.imported && data.imported.length > 0) {
                    message += '<br><br><span class="font-medium">Imported:</span><br>' + data.imported.join('<br>');
                  }
                  Swal.fire({
                    title: 'Import Complete',
                    html: message,
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

      // ========== UTILITY FUNCTIONS ==========
      function formatDate(dateString) {
        if (!dateString) return 'Not set';
        return new Date(dateString).toLocaleDateString('en-US', {
          year: 'numeric',
          month: 'short',
          day: 'numeric'
        });
      }

      function getInitials(name) {
        if (!name) return '?';
        return name.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2);
      }

      function getTierColor(tier) {
        const colors = {
          'platinum': 'bg-purple-100 text-purple-700',
          'gold': 'bg-amber-100 text-amber-700',
          'silver': 'bg-slate-100 text-slate-600',
          'bronze': 'bg-orange-100 text-orange-700'
        };
        return colors[tier?.toLowerCase()] || 'bg-slate-100 text-slate-600';
      }
    </script>
  </body>

</html>