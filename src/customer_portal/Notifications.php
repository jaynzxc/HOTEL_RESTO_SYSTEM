<?php

require_once '../../controller/customer/get/notifications.php';
?>

<!DOCTYPE html>
<html lang="en">

  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications · Customer Portal (clean, no data)</title>
    <!-- Tailwind via CDN + Font Awesome 6 -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  </head>

  <body class="bg-slate-50 font-sans antialiased">

    <!-- main flex wrapper (sidebar + content) -->
    <div class="min-h-screen flex flex-col lg:flex-row">

      <!-- ========== SIDEBAR (customer portal) ========== -->
      <?php require './components/customer_nav.php' ?>

      <!-- ========== MAIN CONTENT (NOTIFICATIONS PAGE - CLEAN) ========== -->
      <main class="flex-1 p-5 lg:p-8 overflow-y-auto">

        <!-- header -->
        <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
          <div>
            <h1 class="text-2xl lg:text-3xl font-light text-slate-800">Notifications</h1>
            <p class="text-sm text-slate-500 mt-0.5">stay updated with your bookings and promos</p>
          </div>
          <div class="flex gap-2">
            <button
              class="bg-white border border-slate-200 rounded-full px-4 py-2 text-sm flex items-center gap-2 hover:bg-slate-50"
              id="markAllReadBtn">
              <i class="fas fa-circle-check"></i> mark all as read
            </button>
            <div
              class="bg-white border border-slate-200 rounded-full px-4 py-2 text-sm flex items-center gap-2 shadow-sm">
              <i class="fas fa-calendar text-slate-400"></i> <span id="currentDate"></span>
            </div>
          </div>
        </div>

        <!-- ===== FILTER TABS (functional counters) ===== -->
        <div class="flex flex-wrap gap-2 border-b border-slate-200 pb-2 mb-6" id="filterTabs">
          <button class="filter-btn px-4 py-2 bg-amber-600 text-white rounded-full text-sm" data-filter="all">all <span
              id="allCount" class="ml-1 text-xs opacity-90">(0)</span></button>
          <button class="filter-btn px-4 py-2 bg-white border border-slate-200 rounded-full text-sm hover:bg-slate-50"
            data-filter="unread">unread <span id="unreadCount" class="ml-1 text-xs text-slate-500">(0)</span></button>
          <button class="filter-btn px-4 py-2 bg-white border border-slate-200 rounded-full text-sm hover:bg-slate-50"
            data-filter="bookings">bookings <span id="bookingsCount"
              class="ml-1 text-xs text-slate-500">(0)</span></button>
          <button class="filter-btn px-4 py-2 bg-white border border-slate-200 rounded-full text-sm hover:bg-slate-50"
            data-filter="promos">promos <span id="promosCount" class="ml-1 text-xs text-slate-500">(0)</span></button>
          <button class="filter-btn px-4 py-2 bg-white border border-slate-200 rounded-full text-sm hover:bg-slate-50"
            data-filter="system">system <span id="systemCount" class="ml-1 text-xs text-slate-500">(0)</span></button>
        </div>

        <!-- ===== NOTIFICATIONS LIST (empty) ===== -->
        <div id="notificationsContainer" class="space-y-3">
          <div class="text-center py-12 bg-white rounded-2xl border border-slate-200">
            <i class="fas fa-bell-slash text-4xl text-slate-300 mb-3"></i>
            <p class="text-slate-500">No notifications yet.</p>
            <p class="text-xs text-slate-400 mt-1">We'll notify you when something arrives.</p>
          </div>
        </div>

        <!-- ===== LOAD MORE (hidden when empty) ===== -->
        <div class="text-center mt-8" id="loadMoreContainer">
          <button
            class="border border-amber-600 text-amber-700 px-6 py-2 rounded-full text-sm hover:bg-amber-50 transition"
            id="loadMoreBtn">load more</button>
        </div>

        <!-- ===== NOTIFICATION PREFERENCES LINK ===== -->
        <div
          class="bg-amber-50 border border-amber-200 rounded-2xl p-5 mt-8 flex flex-wrap items-center justify-between gap-4">
          <div class="flex items-center gap-3">
            <i class="fas fa-bell-slash text-2xl text-amber-600"></i>
            <div>
              <p class="font-medium">manage notification preferences</p>
              <p class="text-xs text-slate-600">choose which alerts you receive via email, SMS, or in-app</p>
            </div>
          </div>
          <button
            class="bg-white border border-amber-600 text-amber-700 px-5 py-2 rounded-xl text-sm hover:bg-amber-50 transition"
            id="preferencesBtn">preferences</button>
        </div>

        <!-- bottom hint -->
        <div class="mt-10 text-center text-xs text-slate-400 border-t pt-6">
          ✅ Notifications module — clean state, zero notifications. Functional buttons (add demo, mark read).
        </div>
      </main>
    </div>

    <script>
      (function () {
        // ---------- LOAD DATA FROM DATABASE ----------
        let notifications = <?php echo json_encode($notifications); ?>;
        let counts = <?php echo json_encode($counts); ?>;
        let currentFilter = 'all';

        // DOM elements
        const container = document.getElementById('notificationsContainer');
        const allCountSpan = document.getElementById('allCount');
        const unreadCountSpan = document.getElementById('unreadCount');
        const bookingsCountSpan = document.getElementById('bookingsCount');
        const promosCountSpan = document.getElementById('promosCount');
        const systemCountSpan = document.getElementById('systemCount');
        const sidebarBadge = document.querySelector('nav .relative span.bg-amber-100');
        const filterButtons = document.querySelectorAll('.filter-btn');
        const markAllBtn = document.getElementById('markAllReadBtn');
        const loadMoreBtn = document.getElementById('loadMoreBtn');
        const dateSpan = document.getElementById('currentDate');
        const preferencesBtn = document.getElementById('preferencesBtn');

        // Helper: format date
        const now = new Date();
        dateSpan.innerText = now.toLocaleDateString('en-US', {
          weekday: 'long',
          year: 'numeric',
          month: 'long',
          day: 'numeric'
        }).toLowerCase();

        // Helper: get icon for category
        function getIcon(category) {
          const iconMap = {
            'booking': 'fas fa-calendar-check',
            'reminder': 'fas fa-clock',
            'promo': 'fas fa-gem',
            'payment': 'fas fa-credit-card',
            'points': 'fas fa-star',
            'review': 'fas fa-pen-to-square',
            'system': 'fas fa-circle-info'
          };
          return iconMap[category] || 'fas fa-bell';
        }

        // Render notifications based on filter
        function renderNotifications() {
          if (notifications.length === 0) {
            container.innerHTML = `
            <div class="text-center py-12 bg-white rounded-2xl border border-slate-200">
              <i class="fas fa-bell-slash text-4xl text-slate-300 mb-3"></i>
              <p class="text-slate-500">No notifications yet.</p>
              <p class="text-xs text-slate-400 mt-1">We'll notify you when something arrives.</p>
            </div>
          `;
            if (loadMoreBtn) {
              loadMoreBtn.disabled = true;
              loadMoreBtn.classList.add('opacity-50', 'cursor-not-allowed');
            }
            return;
          }

          if (loadMoreBtn) {
            loadMoreBtn.disabled = false;
            loadMoreBtn.classList.remove('opacity-50', 'cursor-not-allowed');
          }

          let filtered = notifications;
          if (currentFilter === 'unread') filtered = notifications.filter(n => !n.read);
          else if (currentFilter === 'bookings') filtered = notifications.filter(n => n.category === 'booking' || n.category === 'reminder');
          else if (currentFilter === 'promos') filtered = notifications.filter(n => n.category === 'promo');
          else if (currentFilter === 'system') filtered = notifications.filter(n => ['system', 'payment', 'points', 'review'].includes(n.category));

          if (filtered.length === 0) {
            container.innerHTML = `<div class="text-center py-12 bg-white rounded-2xl border border-slate-200"><p class="text-slate-500">No notifications in this category.</p></div>`;
            return;
          }

          let html = '';
          filtered.forEach(n => {
            const borderClass = n.read ? 'border-slate-200' : 'border-l-4 border-amber-600';
            const bgClass = 'bg-white';
            const badge = n.read ? '' : '<span class="bg-amber-600 text-white text-xs px-2 py-0.5 rounded-full ml-2">new</span>';

            html += `
            <div class="${borderClass} rounded-r-2xl rounded-l-none ${bgClass} p-5 flex flex-wrap items-start gap-4 shadow-sm notification-item" data-id="${n.id}">
              <div class="h-10 w-10 rounded-full bg-amber-100 flex items-center justify-center text-amber-700 shrink-0">
                <i class="${getIcon(n.category)}"></i>
              </div>
              <div class="flex-1">
                <div class="flex items-center gap-2 flex-wrap">
                  <h3 class="font-semibold">${escapeHtml(n.title)}</h3>
                  ${badge}
                </div>
                <p class="text-sm text-slate-600 mt-1">${escapeHtml(n.message)}</p>
                <div class="flex items-center gap-4 mt-2 text-xs text-slate-400">
                  <span><i class="fas fa-clock mr-1"></i> ${n.time_ago}</span>
                  ${n.link ? `<a href="${n.link}" class="text-amber-700 hover:underline view-action" data-id="${n.id}">${n.view_text}</a>` : ''}
                  <button class="text-slate-500 hover:underline dismiss-action" data-id="${n.id}">dismiss</button>
                </div>
              </div>
            </div>
          `;
          });
          container.innerHTML = html;

          // Update counts
          updateCounts();
        }

        // Update filter counts and sidebar badge
        function updateCounts() {
          const total = notifications.length;
          const unread = notifications.filter(n => !n.read).length;
          const bookings = notifications.filter(n => n.category === 'booking' || n.category === 'reminder').length;
          const promos = notifications.filter(n => n.category === 'promo').length;
          const system = notifications.filter(n => ['system', 'payment', 'points', 'review'].includes(n.category)).length;

          if (allCountSpan) allCountSpan.innerText = `(${total})`;
          if (unreadCountSpan) unreadCountSpan.innerText = `(${unread})`;
          if (bookingsCountSpan) bookingsCountSpan.innerText = `(${bookings})`;
          if (promosCountSpan) promosCountSpan.innerText = `(${promos})`;
          if (systemCountSpan) systemCountSpan.innerText = `(${system})`;

          // Update sidebar badge
          const sidebarBadge = document.querySelector('nav .relative span.bg-amber-100');
          if (sidebarBadge) {
            sidebarBadge.innerText = unread;
            if (unread === 0) {
              sidebarBadge.style.display = 'none';
            } else {
              sidebarBadge.style.display = 'inline-block';
            }
          }
        }

        // Escape HTML to prevent XSS
        function escapeHtml(unsafe) {
          if (!unsafe) return '';
          return unsafe
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
        }

        // Dismiss notification
        function dismissNotification(id) {
          fetch('../../controller/customer/post/notification_actions.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
              action: 'dismiss',
              notification_id: id
            })
          })
            .then(response => response.json())
            .then(data => {
              if (data.success) {
                notifications = notifications.filter(n => n.id != id);
                renderNotifications();
                showToast(data.message, 'success');
              } else {
                showToast(data.message, 'error');
              }
            })
            .catch(error => {
              console.error('Error:', error);
              showToast('Failed to dismiss notification', 'error');
            });
        }

        // Mark as read
        function markAsRead(id) {
          fetch('../../controller/customer/post/notification_actions.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
              action: 'mark_read',
              notification_id: id
            })
          })
            .then(response => response.json())
            .then(data => {
              if (data.success) {
                const notif = notifications.find(n => n.id == id);
                if (notif) {
                  notif.read = true;
                  renderNotifications();
                }
              }
            })
            .catch(error => console.error('Error:', error));
        }

        // Mark all as read
        function markAllRead() {
          fetch('../../controller/customer/post/notification_actions.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
              action: 'mark_read'
            })
          })
            .then(response => response.json())
            .then(data => {
              if (data.success) {
                notifications.forEach(n => n.read = true);
                renderNotifications();
                showToast(data.message, 'success');
              } else {
                showToast(data.message, 'error');
              }
            })
            .catch(error => {
              console.error('Error:', error);
              showToast('Failed to mark all as read', 'error');
            });
        }

        // Show toast notification
        function showToast(message, type = 'success') {
          const toast = document.createElement('div');
          toast.className = `fixed bottom-4 right-4 ${type === 'success' ? 'bg-green-600' : 'bg-red-600'} text-white px-6 py-3 rounded-xl shadow-lg z-50 animate-bounce`;
          toast.innerHTML = `<i class="fas ${type === 'success' ? 'fa-circle-check' : 'fa-circle-exclamation'} mr-2"></i>${message}`;
          document.body.appendChild(toast);

          setTimeout(() => {
            toast.remove();
          }, 3000);
        }

        // Filter change
        filterButtons.forEach(btn => {
          btn.addEventListener('click', () => {
            filterButtons.forEach(b => {
              b.classList.remove('bg-amber-600', 'text-white');
              b.classList.add('bg-white', 'border', 'border-slate-200', 'text-slate-700');
            });
            btn.classList.remove('bg-white', 'border', 'border-slate-200', 'text-slate-700');
            btn.classList.add('bg-amber-600', 'text-white');
            currentFilter = btn.dataset.filter;
            renderNotifications();
          });
        });

        // Event delegation for view/dismiss
        container.addEventListener('click', (e) => {
          const dismissBtn = e.target.closest('.dismiss-action');
          const viewLink = e.target.closest('.view-action');

          if (dismissBtn) {
            e.preventDefault();
            const id = dismissBtn.dataset.id;
            dismissNotification(id);
          } else if (viewLink) {
            const id = viewLink.dataset.id;
            markAsRead(id);
            // Don't prevent default - let the link work
          }
        });

        // Mark all as read
        if (markAllBtn) {
          markAllBtn.addEventListener('click', markAllRead);
        }

        // Load more (pagination)
        if (loadMoreBtn) {
          loadMoreBtn.addEventListener('click', () => {
            // In a real implementation, you'd load more notifications from the server
            showToast('Loading more notifications...', 'info');
          });
        }

        // Preferences button
        if (preferencesBtn) {
          preferencesBtn.addEventListener('click', () => {
            alert('Notification preferences will be available soon.');
          });
        }

        // Update user info in sidebar
        const sidebarUser = document.querySelector('.flex.items-center.gap-3.px-6.py-5');
        if (sidebarUser) {
          const initialsSpan = sidebarUser.querySelector('.h-12.w-12');
          const nameSpan = sidebarUser.querySelector('.font-medium.text-slate-800');
          const tierSpan = sidebarUser.querySelector('.text-xs.text-slate-500 span');
          const pointsSpan = sidebarUser.querySelectorAll('.text-xs.text-slate-500 span')[1];

          if (initialsSpan) initialsSpan.innerText = '<?php echo $initials; ?>';
          if (nameSpan) nameSpan.innerText = '<?php echo addslashes($user['full_name'] ?? 'Guest'); ?>';
          if (tierSpan) tierSpan.innerText = '<?php echo $user['member_tier'] ?? 'bronze'; ?>';
          if (pointsSpan) pointsSpan.innerText = '<?php echo number_format($user['loyalty_points'] ?? 0); ?>';
        }

        // Initialize
        renderNotifications();

        // Remove demo double-click handler if you had one
      })();
    </script>
  </body>

</html>