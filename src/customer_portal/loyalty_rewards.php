<?php require_once '../../controller/customer/get/loyalty_rewards.php' ?>


<!DOCTYPE html>
<html lang="en">

  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loyalty Rewards · Customer Portal </title>
    <!-- Tailwind via CDN + Font Awesome 6 -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
      .progress-bar {
        transition: width 0.3s ease;
      }

      .toast {
        position: fixed;
        bottom: 20px;
        right: 20px;
        z-index: 1000;
        animation: slideIn 0.3s ease;
      }

      @keyframes slideIn {
        from {
          transform: translateX(100%);
          opacity: 0;
        }

        to {
          transform: translateX(0);
          opacity: 1;
        }
      }
    </style>
  </head>

  <body class="bg-slate-50 font-sans antialiased">

    <!-- Toast notification container -->
    <div id="toast" class="toast hidden"></div>

    <!-- main flex wrapper (sidebar + content) -->
    <div class="min-h-screen flex flex-col lg:flex-row">

      <!-- ========== SIDEBAR (customer portal) ========== -->
      <aside class="lg:w-80 bg-white border-r border-slate-200 shadow-sm shrink-0">
        <div class="px-6 py-7 border-b border-slate-100">
          <div class="flex items-center gap-2 text-amber-700">
            <i class="fa-solid fa-utensils text-xl"></i>
            <i class="fa-solid fa-bed text-xl"></i>
            <span class="font-semibold text-xl tracking-tight text-slate-800">Lùcas<span
                class="text-amber-600">.stay</span></span>
          </div>
          <p class="text-xs text-slate-500 mt-1">customer portal · loyalty rewards</p>
        </div>

        <!-- user summary with actual data -->
        <div class="flex items-center gap-3 px-6 py-5 border-b border-slate-100 bg-slate-50/80">
          <div
            class="h-12 w-12 rounded-full bg-amber-200 flex items-center justify-center text-amber-800 font-bold text-lg">
            <?php echo htmlspecialchars($initials); ?>
          </div>
          <div>
            <p class="font-medium text-slate-800"><?php echo htmlspecialchars($user['full_name']); ?></p>
            <p class="text-xs text-slate-500 flex items-center gap-1">
              <i class="fa-regular fa-gem text-[11px]"></i> <?php echo $tier; ?> · <span
                id="sidebarPoints"><?php echo $points; ?></span> pts
            </p>
          </div>
        </div>

        <!-- navigation -->
        <nav class="p-4 space-y-1.5 text-sm">
          <a href="../customer_portal/dashboard.php"
            class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-700 hover:bg-amber-50 transition"><i
              class="fa-solid fa-table-cells-large w-5 text-slate-400"></i>Dashboard</a>
          <a href="../customer_portal/my_profile.php"
            class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-700 hover:bg-amber-50 transition"><i
              class="fa-regular fa-user w-5 text-slate-400"></i>My Profile</a>
          <a href="../customer_portal/hotel_booking.php"
            class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-700 hover:bg-amber-50 transition"><i
              class="fa-solid fa-hotel w-5 text-slate-400"></i>Hotel Booking</a>
          <a href="../customer_portal/my_reservation.php"
            class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-700 hover:bg-amber-50 transition"><i
              class="fa-regular fa-calendar-check w-5 text-slate-400"></i>My Reservations</a>
          <a href="../customer_portal/restaurant_reservation.php"
            class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-700 hover:bg-amber-50 transition"><i
              class="fa-regular fa-clock w-5 text-slate-400"></i>Restaurant Reservation</a>
          <a href="../customer_portal/order_food.php"
            class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-700 hover:bg-amber-50 transition"><i
              class="fa-solid fa-bag-shopping w-5 text-slate-400"></i>Menu / Order Food</a>
          <a href="../customer_portal/payments.php"
            class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-700 hover:bg-amber-50 transition"><i
              class="fa-regular fa-credit-card w-5 text-slate-400"></i>Payments</a>
          <a href="../customer_portal/loyalty_rewards.php"
            class="flex items-center gap-3 px-4 py-2.5 rounded-xl bg-amber-50 text-amber-800 font-medium"><i
              class="fa-regular fa-star w-5 text-amber-600"></i>Loyalty Rewards</a>
          <a href="../customer_portal/notifications.php"
            class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-700 hover:bg-amber-50 transition relative"><i
              class="fa-regular fa-bell w-5 text-slate-400"></i>Notifications<span
              class="ml-auto bg-amber-100 text-amber-800 text-xs px-1.5 py-0.5 rounded-full">3</span></a>
          <div class="border-t border-slate-200 pt-3 mt-3">
            <a href="../../controller/auth/logout.php"
              class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-500 hover:bg-red-50 hover:text-red-700 transition"><i
                class="fa-solid fa-arrow-right-from-bracket w-5"></i>Logout</a>
          </div>
        </nav>
      </aside>

      <!-- ========== MAIN CONTENT (LOYALTY REWARDS PAGE) ========== -->
      <main class="flex-1 p-5 lg:p-8 overflow-y-auto">

        <!-- Display Messages -->
        <?php if (!empty($_SESSION['success'])): ?>
          <div class="bg-green-50 border border-green-200 text-green-600 px-4 py-3 rounded-lg mb-4">
            <?php foreach ($_SESSION['success'] as $message): ?>
              <p class="text-sm"><i class="fa-regular fa-circle-check mr-2"></i><?php echo htmlspecialchars($message); ?>
              </p>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <?php if (!empty($_SESSION['error'])): ?>
          <div class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-lg mb-4">
            <ul class="list-disc list-inside text-sm">
              <?php foreach ($_SESSION['error'] as $field => $message): ?>
                <li><?php echo htmlspecialchars($message); ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <!-- header -->
        <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
          <div>
            <h1 class="text-2xl lg:text-3xl font-light text-slate-800">Loyalty Rewards</h1>
            <p class="text-sm text-slate-500 mt-0.5">earn points and enjoy exclusive perks</p>
          </div>
          <div
            class="bg-white border border-slate-200 rounded-full px-4 py-2 text-sm flex items-center gap-2 shadow-sm">
            <i class="fa-regular fa-calendar text-slate-400"></i> <span id="currentDate"></span>
          </div>
        </div>

        <!-- ===== TIER & POINTS OVERVIEW ===== -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
          <!-- points balance card -->
          <div class="bg-linear-to-br from-amber-600 to-amber-700 text-white rounded-2xl p-6 shadow-md lg:col-span-1">
            <p class="text-sm opacity-90 flex items-center gap-1"><i class="fa-regular fa-star"></i> your points balance
            </p>
            <p class="text-4xl font-bold mt-2" id="pointsBalance"><?php echo $points; ?></p>
            <p class="text-xs opacity-80 mt-1">≈ ₱<?php echo number_format($points * 0.5); ?> value</p>
            <div class="mt-4 flex gap-2">
              <button
                class="bg-white text-amber-700 px-4 py-2 rounded-xl text-sm font-medium hover:bg-amber-50 transition"
                id="redeemDummyBtn">redeem</button>
              <button
                class="border border-white text-white px-4 py-2 rounded-xl text-sm font-medium hover:bg-amber-600 transition">learn
                how</button>
            </div>
          </div>

          <!-- tier status & progress -->
          <div class="bg-white rounded-2xl border border-slate-200 p-5 lg:col-span-2">
            <div class="flex items-center justify-between">
              <h3 class="font-semibold flex items-center gap-2"><i class="fa-regular fa-gem text-amber-600"></i> current
                tier: <span class="bg-slate-100 text-slate-700 px-3 py-1 rounded-full text-sm"
                  id="currentTier"><?php echo $tier; ?></span></h3>
              <p class="text-sm text-slate-500" id="nextTierLabel">next: <?php echo $nextTier; ?></p>
            </div>
            <div class="mt-4">
              <div class="flex justify-between text-sm mb-1">
                <span id="progressCurrent"><?php echo $points; ?> pts</span>
                <span id="progressTarget"><?php echo $nextThreshold; ?> pts</span>
              </div>
              <div class="w-full bg-slate-200 h-2.5 rounded-full">
                <div class="w-<?php echo $progress; ?>% bg-amber-600 h-2.5 rounded-full progress-bar" id="progressBar"
                  style="width: <?php echo $progress; ?>%;"></div>
              </div>
              <p class="text-xs text-slate-500 mt-2" id="progressMessage">
                <?php echo $pointsToNext > 0 ? $pointsToNext . ' more points to reach ' . $nextTier : 'You\'ve reached ' . $tier . ' tier!'; ?>
              </p>
            </div>
            <div class="grid grid-cols-3 gap-3 mt-5 text-center">
              <div class="border-r"><span class="font-bold text-lg" id="perk1"><?php echo $perks[0]; ?></span>
                <p class="text-xs text-slate-500">extra discount</p>
              </div>
              <div class="border-r"><span class="font-bold text-lg" id="perk2"><?php echo $perks[1]; ?></span>
                <p class="text-xs text-slate-500">points on dining</p>
              </div>
              <div><span class="font-bold text-lg" id="perk3"><?php echo $perks[2]; ?></span>
                <p class="text-xs text-slate-500">welcome perk</p>
              </div>
            </div>
          </div>
        </div>

        <!-- ===== AVAILABLE REWARDS TO REDEEM ===== -->
        <h2 class="font-semibold text-xl mb-4 flex items-center gap-2"><i class="fa-regular fa-gift text-amber-600"></i>
          rewards you can redeem</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 mb-8">

          <!-- reward 1 -->
          <div class="bg-white rounded-2xl border border-slate-200 p-5 hover:shadow-md transition reward-card"
            data-points="240">
            <div
              class="h-12 w-12 rounded-full bg-amber-100 flex items-center justify-center text-amber-700 text-xl mb-3">
              <i class="fa-regular fa-mug-hot"></i>
            </div>
            <h3 class="font-semibold">Free Coffee / Tea</h3>
            <p class="text-xs text-slate-500 mt-1">any hot beverage at Azure Lounge</p>
            <div class="flex items-center justify-between mt-4">
              <span class="font-bold text-amber-700">240 pts</span>
              <button class="redeem-btn bg-amber-600 hover:bg-amber-700 text-white px-4 py-2 rounded-xl text-sm"
                data-reward="Free Coffee / Tea" data-cost="240" data-experience="Beverage">redeem</button>
            </div>
          </div>

          <!-- reward 2 -->
          <div class="bg-white rounded-2xl border border-slate-200 p-5 hover:shadow-md transition reward-card"
            data-points="480">
            <div
              class="h-12 w-12 rounded-full bg-amber-100 flex items-center justify-center text-amber-700 text-xl mb-3">
              <i class="fa-regular fa-bowl-food"></i>
            </div>
            <h3 class="font-semibold">Complimentary Breakfast</h3>
            <p class="text-xs text-slate-500 mt-1">for one person at Azure Restaurant</p>
            <div class="flex items-center justify-between mt-4">
              <span class="font-bold text-amber-700">480 pts</span>
              <button class="redeem-btn bg-amber-600 hover:bg-amber-700 text-white px-4 py-2 rounded-xl text-sm"
                data-reward="Complimentary Breakfast" data-cost="480" data-experience="Dining">redeem</button>
            </div>
          </div>

          <!-- reward 3 -->
          <div class="bg-white rounded-2xl border border-slate-200 p-5 hover:shadow-md transition reward-card"
            data-points="600">
            <div
              class="h-12 w-12 rounded-full bg-amber-100 flex items-center justify-center text-amber-700 text-xl mb-3">
              <i class="fa-regular fa-clock"></i>
            </div>
            <h3 class="font-semibold">Late Check-out (2pm)</h3>
            <p class="text-xs text-slate-500 mt-1">subject to availability</p>
            <div class="flex items-center justify-between mt-4">
              <span class="font-bold text-amber-700">600 pts</span>
              <button class="redeem-btn bg-amber-600 hover:bg-amber-700 text-white px-4 py-2 rounded-xl text-sm"
                data-reward="Late Check-out" data-cost="600" data-experience="Hotel Stay">redeem</button>
            </div>
          </div>

          <!-- reward 4 -->
          <div class="bg-white rounded-2xl border border-slate-200 p-5 hover:shadow-md transition reward-card"
            data-points="360">
            <div
              class="h-12 w-12 rounded-full bg-amber-100 flex items-center justify-center text-amber-700 text-xl mb-3">
              <i class="fa-regular fa-wine-glass"></i>
            </div>
            <h3 class="font-semibold">Welcome Drink (2 pax)</h3>
            <p class="text-xs text-slate-500 mt-1">signature cocktail or mocktail</p>
            <div class="flex items-center justify-between mt-4">
              <span class="font-bold text-amber-700">360 pts</span>
              <button class="redeem-btn bg-amber-600 hover:bg-amber-700 text-white px-4 py-2 rounded-xl text-sm"
                data-reward="Welcome Drink" data-cost="360" data-experience="Dining">redeem</button>
            </div>
          </div>

          <!-- reward 5 -->
          <div class="bg-white rounded-2xl border border-slate-200 p-5 hover:shadow-md transition reward-card"
            data-points="1200">
            <div
              class="h-12 w-12 rounded-full bg-amber-100 flex items-center justify-center text-amber-700 text-xl mb-3">
              <i class="fa-regular fa-hotel"></i>
            </div>
            <h3 class="font-semibold">Room Upgrade (next stay)</h3>
            <p class="text-xs text-slate-500 mt-1">deluxe to suite (subject to availability)</p>
            <div class="flex items-center justify-between mt-4">
              <span class="font-bold text-amber-700">1,200 pts</span>
              <button class="redeem-btn bg-amber-600 hover:bg-amber-700 text-white px-4 py-2 rounded-xl text-sm"
                data-reward="Room Upgrade" data-cost="1200" data-experience="Hotel Stay">redeem</button>
            </div>
          </div>

          <!-- reward 6 -->
          <div class="bg-white rounded-2xl border border-slate-200 p-5 hover:shadow-md transition reward-card"
            data-points="800">
            <div
              class="h-12 w-12 rounded-full bg-amber-100 flex items-center justify-center text-amber-700 text-xl mb-3">
              <i class="fa-regular fa-tag"></i>
            </div>
            <h3 class="font-semibold">₱500 Discount</h3>
            <p class="text-xs text-slate-500 mt-1">on any hotel booking</p>
            <div class="flex items-center justify-between mt-4">
              <span class="font-bold text-amber-700">800 pts</span>
              <button class="redeem-btn bg-amber-600 hover:bg-amber-700 text-white px-4 py-2 rounded-xl text-sm"
                data-reward="₱500 Discount" data-cost="800" data-experience="Hotel Stay">redeem</button>
            </div>
          </div>
        </div>

        <!-- ===== POINTS EARNING HISTORY ===== -->
        <div class="bg-white rounded-2xl border border-slate-200 p-6 mb-8">
          <div class="flex items-center justify-between mb-4">
            <h2 class="font-semibold text-lg flex items-center gap-2"><i
                class="fa-regular fa-clock-rotate-left text-amber-600"></i> points history</h2>
            <a href="#" class="text-sm text-amber-700 hover:underline">view all</a>
          </div>
          <div class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead class="text-slate-400 text-xs border-b">
                <tr>
                  <td class="pb-2">date</td>
                  <td>activity</td>
                  <td>points</td>
                  <td>balance</td>
                </tr>
              </thead>
              <tbody id="pointsHistoryBody" class="divide-y">
                <?php if (empty($pointsHistory)): ?>
                  <tr>
                    <td colspan="4" class="py-6 text-center text-slate-400">No points history yet.</td>
                  </tr>
                <?php else: ?>
                  <?php
                  $running_balance = $points;
                  foreach ($pointsHistory as $entry):
                    $date = date('M d, Y', strtotime($entry['date']));
                    $points_change = $entry['points'];
                    $running_balance -= $points_change; // Reverse calculate
                    ?>
                    <tr class="text-sm">
                      <td class="py-2"><?php echo $date; ?></td>
                      <td><?php echo htmlspecialchars($entry['description']); ?></td>
                      <td class="<?php echo $points_change > 0 ? 'text-green-600' : 'text-amber-600'; ?>">
                        <?php echo $points_change > 0 ? '+' . $points_change : $points_change; ?>
                      </td>
                      <td><?php echo $running_balance; ?></td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>

        <!-- ===== HOW TO EARN MORE (static info) ===== -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
          <div class="bg-amber-50 border border-amber-200 rounded-2xl p-5 flex items-start gap-3">
            <i class="fa-regular fa-bed text-2xl text-amber-600"></i>
            <div>
              <p class="font-medium">hotel stays</p>
              <p class="text-xs text-slate-600">5 pts per ₱100 spent</p>
            </div>
          </div>
          <div class="bg-amber-50 border border-amber-200 rounded-2xl p-5 flex items-start gap-3">
            <i class="fa-regular fa-utensils text-2xl text-amber-600"></i>
            <div>
              <p class="font-medium">dining</p>
              <p class="text-xs text-slate-600">3 pts per ₱100 (tier bonuses apply)</p>
            </div>
          </div>
          <div class="bg-amber-50 border border-amber-200 rounded-2xl p-5 flex items-start gap-3">
            <i class="fa-regular fa-calendar-check text-2xl text-amber-600"></i>
            <div>
              <p class="font-medium">events & spa</p>
              <p class="text-xs text-slate-600">2 pts per ₱100</p>
            </div>
          </div>
        </div>

        <!-- bottom hint -->
        <div class="mt-10 text-center text-xs text-slate-400 border-t pt-6">
          ✅ Earn points with every stay and dining experience!
        </div>
      </main>
    </div>

    <script>
      (function () {
        // DOM elements
        const pointsBalanceSpan = document.getElementById('pointsBalance');
        const sidebarPointsSpan = document.getElementById('sidebarPoints');
        const currentTierSpan = document.getElementById('currentTier');
        const nextTierLabel = document.getElementById('nextTierLabel');
        const progressCurrent = document.getElementById('progressCurrent');
        const progressTarget = document.getElementById('progressTarget');
        const progressBar = document.getElementById('progressBar');
        const progressMessage = document.getElementById('progressMessage');
        const perk1 = document.getElementById('perk1');
        const perk2 = document.getElementById('perk2');
        const perk3 = document.getElementById('perk3');
        const dateSpan = document.getElementById('currentDate');
        const toast = document.getElementById('toast');

        // Current points from PHP
        let currentPoints = <?php echo $points; ?>;

        // Set current date
        const now = new Date();
        const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        dateSpan.innerText = now.toLocaleDateString('en-US', options).toLowerCase();

        // Show toast message
        function showToast(message, type = 'success') {
          toast.className = `toast ${type === 'success' ? 'bg-green-600' : 'bg-red-600'} text-white px-6 py-3 rounded-xl shadow-lg`;
          toast.innerHTML = `<i class="fa-regular ${type === 'success' ? 'fa-circle-check' : 'fa-circle-exclamation'} mr-2"></i>${message}`;
          toast.classList.remove('hidden');

          setTimeout(() => {
            toast.classList.add('hidden');
          }, 3000);
        }

        // Update points in UI
        function updatePoints(newPoints) {
          currentPoints = newPoints;
          pointsBalanceSpan.innerText = currentPoints;
          sidebarPointsSpan.innerText = currentPoints;

          // Refresh the page to update tier and progress
          // You can also update dynamically here, but for simplicity we'll reload
          setTimeout(() => {
            location.reload();
          }, 1500);
        }

        // Redeem buttons
        document.querySelectorAll('.redeem-btn').forEach(btn => {
          btn.addEventListener('click', async (e) => {
            const rewardName = btn.dataset.reward;
            const cost = parseInt(btn.dataset.cost);
            const experience = btn.dataset.experience;

            if (currentPoints < cost) {
              showToast(`Insufficient points. You need ${cost - currentPoints} more points.`, 'error');
              return;
            }

            if (!confirm(`Redeem ${rewardName} for ${cost} points?`)) {
              return;
            }

            // Disable button to prevent double submission
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin mr-2"></i>processing...';

            try {
              const formData = new FormData();
              formData.append('reward_name', rewardName);
              formData.append('points_cost', cost);
              formData.append('experience', experience);

              const response = await fetch('../../controller/customer/post/redeem.php', {
                method: 'POST',
                body: formData
              });

              const result = await response.json();

              if (result.success) {
                showToast(result.message, 'success');
                updatePoints(result.new_points);
              } else {
                showToast(result.message, 'error');
                btn.disabled = false;
                btn.innerHTML = 'redeem';
              }
            } catch (error) {
              showToast('An error occurred. Please try again.', 'error');
              btn.disabled = false;
              btn.innerHTML = 'redeem';
            }
          });
        });

        // Dummy redeem button (top)
        document.getElementById('redeemDummyBtn').addEventListener('click', () => {
          alert('Select a reward below to redeem.');
        });

        // For demo/testing: double-click to add points (remove in production)
        pointsBalanceSpan.addEventListener('dblclick', () => {
          let extra = prompt('Add test points (simulation)', '100');
          if (extra && !isNaN(parseInt(extra))) {
            // This is just for testing - in production, points come from real activities
            alert('Test mode: Points would be added through reviews and purchases');
          }
        });
      })();
    </script>
  </body>

</html>