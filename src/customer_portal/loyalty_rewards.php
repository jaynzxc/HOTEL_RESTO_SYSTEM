<?php require_once '../../controller/customer/get/loyalty_rewards.php' ?>

<!DOCTYPE html>
<html lang="en">

  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loyalty Rewards · Customer Portal </title>
    <link rel="stylesheet" href="../output.css">
    <!-- Tailwind via CDN + Font Awesome 6 -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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

      .reward-card.disabled {
        opacity: 0.6;
        pointer-events: none;
      }

      .balance-warning {
        background: linear-gradient(135deg, #ef4444, #dc2626);
      }
    </style>
  </head>

  <body class="bg-slate-50 font-sans antialiased">

    <!-- Toast notification container -->
    <div id="toast" class="toast hidden"></div>

    <!-- main flex wrapper (sidebar + content) -->
    <div class="min-h-screen flex flex-col lg:flex-row">

      <!-- ========== SIDEBAR (customer portal) ========== -->
      <?php require './components/customer_nav.php' ?>

      <!-- ========== MAIN CONTENT (LOYALTY REWARDS PAGE) ========== -->
      <main class="flex-1 p-5 lg:p-8 overflow-y-auto">

        <!-- Display Messages -->
        <?php if (!empty($_SESSION['success'])): ?>
          <div class="bg-green-50 border border-green-200 text-green-600 px-4 py-3 rounded-lg mb-4">
            <?php foreach ($_SESSION['success'] as $message): ?>
              <p class="text-sm"><i class="fas fa-circle-check mr-2"></i><?php echo htmlspecialchars($message); ?>
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

        <!-- Outstanding Balance Warning -->
        <?php if ($hasOutstandingBalance): ?>
          <div class="balance-warning text-white rounded-2xl p-4 mb-6 flex items-center justify-between">
            <div class="flex items-center gap-3">
              <i class="fa-solid fa-exclamation-triangle text-2xl"></i>
              <div>
                <p class="font-semibold">Outstanding Balance Detected</p>
                <p class="text-sm opacity-90">You have ₱<?php echo number_format($totalOutstanding, 2); ?> in unpaid
                  bookings.</p>
                <p class="text-xs opacity-75 mt-1">Please clear your balance before redeeming rewards.</p>
              </div>
            </div>
            <a href="./payments.php"
              class="bg-white text-red-600 px-4 py-2 rounded-xl text-sm font-medium hover:bg-red-50 transition">
              Go to Payments
            </a>
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
            <i class="fas fa-calendar text-slate-400"></i> <span id="currentDate"></span>
          </div>
        </div>

        <!-- ===== TIER & POINTS OVERVIEW ===== -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
          <!-- points balance card -->
          <div class="bg-linear-to-br from-amber-600 to-amber-700 text-white rounded-2xl p-6 shadow-md lg:col-span-1">
            <p class="text-sm opacity-90 flex items-center gap-1"><i class="fas fa-star"></i> your points balance
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
              <h3 class="font-semibold flex items-center gap-2"><i class="fas fa-gem text-amber-600"></i> current
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
        <h2 class="font-semibold text-xl mb-4 flex items-center gap-2"><i class="fas fa-gift text-amber-600"></i>
          rewards you can redeem</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 mb-8">

          <?php if (empty($availableRewards)): ?>
            <div class="col-span-3 text-center py-8 text-slate-500">
              <i class="fas fa-face-frown text-4xl mb-3 opacity-50"></i>
              <p>No rewards available at the moment.</p>
            </div>
          <?php else: ?>
            <?php foreach ($availableRewards as $reward):
              // Determine icon based on category
              $icon = 'fa-gift';
              switch ($reward['category']) {
                case 'beverage':
                  $icon = 'fa-mug-hot';
                  break;
                case 'dining':
                  $icon = 'fa-utensils';
                  break;
                case 'hotel':
                  $icon = 'fa-hotel';
                  break;
                case 'spa':
                  $icon = 'fa-spa';
                  break;
                default:
                  $icon = 'fa-gift';
              }
              ?>
              <div
                class="bg-white rounded-2xl border border-slate-200 p-5 hover:shadow-md transition reward-card <?php echo $hasOutstandingBalance ? 'opacity-60' : ''; ?>"
                data-points="<?php echo $reward['points_cost']; ?>">
                <div
                  class="h-12 w-12 rounded-full bg-amber-100 flex items-center justify-center text-amber-700 text-xl mb-3">
                  <i class="fas <?php echo $icon; ?>"></i>
                </div>
                <h3 class="font-semibold"><?php echo htmlspecialchars($reward['reward_name']); ?></h3>
                <p class="text-xs text-slate-500 mt-1"><?php echo htmlspecialchars($reward['description']); ?></p>
                <div class="flex items-center justify-between mt-4">
                  <span class="font-bold text-amber-700"><?php echo number_format($reward['points_cost']); ?> pts</span>
                  <button
                    class="redeem-btn bg-amber-600 hover:bg-amber-700 text-white px-4 py-2 rounded-xl text-sm <?php echo $hasOutstandingBalance ? 'opacity-50 cursor-not-allowed' : ''; ?>"
                    data-reward="<?php echo htmlspecialchars($reward['reward_name']); ?>"
                    data-cost="<?php echo $reward['points_cost']; ?>"
                    data-experience="<?php echo htmlspecialchars($reward['category']); ?>" <?php echo $hasOutstandingBalance ? 'disabled' : ''; ?>>
                    redeem
                  </button>
                </div>
                <?php if ($reward['stock_limit'] !== null): ?>
                  <p class="text-xs text-slate-400 mt-2">
                    <i class="fas fa-box mr-1"></i>
                    <?php echo max(0, $reward['stock_limit'] - $reward['times_redeemed']); ?> left
                  </p>
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
        <!-- ===== POINTS EARNING HISTORY ===== -->
        <div class="bg-white rounded-2xl border border-slate-200 p-6 mb-8">
          <div class="flex items-center justify-between mb-4">
            <h2 class="font-semibold text-lg flex items-center gap-2"><i
                class="fas fa-clock-rotate-left text-amber-600"></i> points history</h2>
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
            <i class="fas fa-bed text-2xl text-amber-600"></i>
            <div>
              <p class="font-medium">hotel stays</p>
              <p class="text-xs text-slate-600">5 pts per ₱100 spent</p>
            </div>
          </div>
          <div class="bg-amber-50 border border-amber-200 rounded-2xl p-5 flex items-start gap-3">
            <i class="fas fa-utensils text-2xl text-amber-600"></i>
            <div>
              <p class="font-medium">dining</p>
              <p class="text-xs text-slate-600">3 pts per ₱100 (tier bonuses apply)</p>
            </div>
          </div>
          <div class="bg-amber-50 border border-amber-200 rounded-2xl p-5 flex items-start gap-3">
            <i class="fas fa-calendar-check text-2xl text-amber-600"></i>
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
        const hasOutstandingBalance = <?php echo $hasOutstandingBalance ? 'true' : 'false'; ?>;
        const outstandingAmount = <?php echo $totalOutstanding; ?>;

        // Set current date
        const now = new Date();
        const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        dateSpan.innerText = now.toLocaleDateString('en-US', options).toLowerCase();

        // Show toast message
        function showToast(message, type = 'success') {
          toast.className = `toast ${type === 'success' ? 'bg-green-600' : 'bg-red-600'} text-white px-6 py-3 rounded-xl shadow-lg`;
          toast.innerHTML = `<i class="fas ${type === 'success' ? 'fa-circle-check' : 'fa-circle-exclamation'} mr-2"></i>${message}`;
          toast.classList.remove('hidden');

          setTimeout(() => {
            toast.classList.add('hidden');
          }, 3000);
        }

        // Show balance warning
        function showBalanceWarning() {
          Swal.fire({
            title: 'Outstanding Balance Detected',
            html: `
              <div class="text-left">
                <p class="mb-3 text-red-600">You have an outstanding balance of <strong>₱${outstandingAmount.toFixed(2)}</strong>.</p>
                <p class="mb-3">Please clear your balance before redeeming rewards.</p>
                <a href="./payments.php" class="inline-block bg-amber-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-amber-700 transition">
                  Go to Payments
                </a>
              </div>
            `,
            icon: 'warning',
            confirmButtonColor: '#d97706',
            confirmButtonText: 'OK',
            showCancelButton: false
          });
        }

        // Update points in UI
        function updatePoints(newPoints) {
          currentPoints = newPoints;
          pointsBalanceSpan.innerText = currentPoints;
          sidebarPointsSpan.innerText = currentPoints;

          // Refresh the page to update tier and progress
          setTimeout(() => {
            location.reload();
          }, 1500);
        }

        // Redeem buttons
        document.querySelectorAll('.redeem-btn').forEach(btn => {
          btn.addEventListener('click', async (e) => {
            e.preventDefault();

            // Check for outstanding balance first
            if (hasOutstandingBalance) {
              showBalanceWarning();
              return;
            }

            const rewardName = btn.dataset.reward;
            const cost = parseInt(btn.dataset.cost);
            const experience = btn.dataset.experience;

            if (currentPoints < cost) {
              showToast(`Insufficient points. You need ${cost - currentPoints} more points.`, 'error');
              return;
            }

            // Use SweetAlert for confirmation
            const result = await Swal.fire({
              title: 'Redeem Reward?',
              html: `Redeem <strong>${rewardName}</strong> for <strong>${cost} points</strong>?`,
              icon: 'question',
              showCancelButton: true,
              confirmButtonColor: '#d97706',
              cancelButtonColor: '#6b7280',
              confirmButtonText: 'Yes, redeem'
            });

            if (!result.isConfirmed) {
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
              btn.disabled = false;
              btn.innerHTML = 'redeem';
            }
          });
        });

        // Dummy redeem button (top)
        document.getElementById('redeemDummyBtn').addEventListener('click', () => {
          if (hasOutstandingBalance) {
            showBalanceWarning();
          } else {
            Swal.fire({
              title: 'Redeem Rewards',
              text: 'Select a reward below to redeem your points.',
              icon: 'info',
              confirmButtonColor: '#d97706'
            });
          }
        });

        // For demo/testing: double-click to add points (remove in production)
        pointsBalanceSpan.addEventListener('dblclick', () => {
          if (hasOutstandingBalance) {
            showBalanceWarning();
            return;
          }
          let extra = prompt('Add test points (simulation)', '100');
          if (extra && !isNaN(parseInt(extra))) {
            alert('Test mode: Points would be added through reviews and purchases');
          }
        });
      })();
    </script>
  </body>

</html>