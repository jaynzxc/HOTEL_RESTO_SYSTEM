<?php
/**
 * Admin Notification Component - Shows ALL admin notifications to all admins
 * 
 * This file expects:
 * - $db (Database connection)
 * - $_SESSION['user_id'] (current admin user ID) - used for marking as read only
 */

// Get ALL admin notifications (not filtered by admin_id)
try {
    // Count unread notifications for THIS admin
    $unread_result = $db->query(
        "SELECT COUNT(*) as count FROM admin_notifications 
         WHERE is_read = 0",  // Count all unread notifications
        []
    )->fetch_one();
    $unread_count = $unread_result['count'] ?? 0;
} catch (Exception $e) {
    $unread_count = 0;
}

// Get ALL recent notifications (no admin_id filter)
$recent_notifications = $db->query(
    "SELECT id, admin_id, title, message, type, icon, is_read, 
            DATE_FORMAT(created_at, '%h:%i %p') as time_formatted,
            DATE_FORMAT(created_at, '%b %d, %Y') as date_formatted,
            created_at
     FROM admin_notifications 
     ORDER BY created_at DESC
     LIMIT 20",
    []
)->find() ?: [];
?>

<!-- Notification Bell with Dropdown -->
<div class="relative" id="notificationComponent">
    <!-- Bell Icon with Badge -->
    <button id="adminNotificationBell" class="relative p-2 rounded-full hover:bg-slate-100 transition"
        onclick="toggleAdminNotificationDropdown(event)">
        <i class="fas fa-bell text-slate-600"></i>
        <?php if ($unread_count > 0): ?>
            <span
                class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center admin-notification-badge">
                <?php echo $unread_count > 99 ? '99+' : $unread_count; ?>
            </span>
        <?php endif; ?>
    </button>

    <!-- Notification Dropdown (hidden by default) -->
    <div id="adminNotificationDropdown"
        class="hidden absolute right-0 mt-2 w-96 bg-white rounded-xl shadow-lg border border-slate-200 z-50 overflow-hidden">
        <div class="p-3 border-b border-slate-200 bg-slate-50 flex justify-between items-center">
            <h3 class="font-semibold text-sm">Admin Notifications</h3>
            <div class="flex gap-2">
                <?php if ($unread_count > 0): ?>
                    <button onclick="markAllAdminNotificationsRead()" class="text-xs text-amber-600 hover:underline"
                        id="markAllAdminReadBtn">
                        Mark all as read
                    </button>
                <?php endif; ?>
                <button onclick="clearAllNotifications()" class="text-xs text-red-600 hover:underline" id="clearAllBtn">
                    Clear all
                </button>
            </div>
        </div>

        <div class="max-h-96 overflow-y-auto" id="adminNotificationList">
            <?php if (empty($recent_notifications)): ?>
                <div class="p-4 text-center text-slate-400 text-sm">
                    <i class="fas fa-bell-slash text-2xl mb-2 opacity-50"></i>
                    <p>No admin notifications</p>
                </div>
            <?php else: ?>
                <?php foreach ($recent_notifications as $notif):
                    $bgColor = $notif['is_read'] ? 'bg-white' : 'bg-amber-50';
                    $borderColor = $notif['is_read'] ? 'border-slate-200' : 'border-amber-300';

                    // Icon color based on type
                    $iconColor = match ($notif['type']) {
                        'success' => 'text-green-500',
                        'warning' => 'text-amber-500',
                        'danger' => 'text-red-500',
                        'info' => 'text-blue-500',
                        default => 'text-slate-500'
                    };

                    // Default icon based on type if not provided
                    $icon = $notif['icon'] ?? match ($notif['type']) {
                        'success' => 'fa-circle-check',
                        'warning' => 'fa-triangle-exclamation',
                        'danger' => 'fa-circle-exclamation',
                        'info' => 'fa-circle-info',
                        default => 'fa-bell'
                    };

                    // Determine who sent this (optional)
                    $senderInfo = '';
                    if ($notif['admin_id'] == 0) {
                        $senderInfo = '<span class="text-xs font-medium text-purple-500 ml-2">[HR]</span>';
                    } else if ($notif['admin_id']) {
                        $senderInfo = '<span class="text-xs text-blue-500 ml-2">[Admin]</span>';
                    }
                    ?>
                    <div class="admin-notification-item <?php echo $bgColor; ?> border-l-4 <?php echo $borderColor; ?> p-3 hover:bg-slate-50 transition group relative"
                        data-id="<?php echo $notif['id']; ?>">
                        <div class="flex gap-3">
                            <div class="flex-shrink-0">
                                <i class="fas <?php echo $icon; ?> <?php echo $iconColor; ?>"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center">
                                    <p class="text-sm font-medium text-slate-800 truncate">
                                        <?php echo htmlspecialchars($notif['title']); ?>
                                    </p>
                                    <?php echo $senderInfo; ?>
                                </div>
                                <p class="text-xs text-slate-600 line-clamp-2">
                                    <?php echo htmlspecialchars($notif['message']); ?>
                                </p>
                                <p class="text-xs text-slate-400 mt-1">
                                    <i class="far fa-clock mr-1"></i>
                                    <?php echo $notif['time_formatted']; ?> · <?php echo $notif['date_formatted']; ?>
                                </p>
                            </div>
                            <div class="flex items-center gap-2">
                                <?php if (!$notif['is_read']): ?>
                                    <span class="w-2 h-2 bg-amber-500 rounded-full flex-shrink-0"></span>
                                <?php endif; ?>
                                <button onclick="removeNotification(<?php echo $notif['id']; ?>, event)"
                                    class="opacity-0 group-hover:opacity-100 transition text-slate-400 hover:text-red-600"
                                    title="Remove notification">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="p-2 border-t border-slate-200 bg-slate-50 text-center">
            <button onclick="clearAllNotifications()" class="text-xs text-red-600 hover:underline block w-full py-1">
                Clear all notifications
            </button>
        </div>
    </div>
</div>

<script>
    // Admin Notification dropdown functionality
    function toggleAdminNotificationDropdown(event) {
        event.stopPropagation();
        const dropdown = document.getElementById('adminNotificationDropdown');
        dropdown.classList.toggle('hidden');
    }

    // Close dropdown when clicking outside
    document.addEventListener('click', function (event) {
        const dropdown = document.getElementById('adminNotificationDropdown');
        const bell = document.getElementById('adminNotificationBell');

        if (!dropdown || !bell) return;

        if (!dropdown.contains(event.target) && !bell.contains(event.target)) {
            dropdown.classList.add('hidden');
        }
    });

    // Mark as read when clicking on notification (optional)
    function openAdminNotification(element) {
        const notificationId = element.dataset.id;
        markAdminNotificationAsRead(notificationId);
    }

    // Mark single admin notification as read
    function markAdminNotificationAsRead(notificationId) {
        fetch('../../../controller/admin/post/admin_notification_actions.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=mark_as_read&notification_id=' + notificationId
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateAdminNotificationBadge();
                    // Update UI
                    const item = document.querySelector(`.admin-notification-item[data-id="${notificationId}"]`);
                    if (item) {
                        item.classList.remove('bg-amber-50');
                        item.classList.add('bg-white');
                        const dot = item.querySelector('.w-2.h-2.bg-amber-500');
                        if (dot) dot.remove();
                    }
                }
            })
            .catch(error => console.error('Error:', error));
    }

    // Remove single notification
    function removeNotification(notificationId, event) {
        event.stopPropagation(); // Prevent triggering the parent click

        if (!confirm('Remove this notification?')) return;

        fetch('../../../controller/admin/post/admin_notification_actions.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=remove_notification&notification_id=' + notificationId
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Remove the notification element from DOM
                    const item = document.querySelector(`.admin-notification-item[data-id="${notificationId}"]`);
                    if (item) {
                        item.remove();
                    }

                    updateAdminNotificationBadge();

                    // Check if list is empty
                    const list = document.getElementById('adminNotificationList');
                    if (list && list.children.length === 0) {
                        list.innerHTML = `
                    <div class="p-4 text-center text-slate-400 text-sm">
                        <i class="fas fa-bell-slash text-2xl mb-2 opacity-50"></i>
                        <p>No admin notifications</p>
                    </div>
                `;
                    }

                    if (typeof showToast === 'function') {
                        showToast('Notification removed', 'success');
                    }
                }
            })
            .catch(error => console.error('Error:', error));
    }

    // Clear all notifications
    function clearAllNotifications() {
        if (!confirm('Remove all notifications? This cannot be undone.')) return;

        fetch('../../../controller/admin/post/admin_notification_actions.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=clear_all'
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Clear the list
                    const list = document.getElementById('adminNotificationList');
                    if (list) {
                        list.innerHTML = `
                    <div class="p-4 text-center text-slate-400 text-sm">
                        <i class="fas fa-bell-slash text-2xl mb-2 opacity-50"></i>
                        <p>No admin notifications</p>
                    </div>
                `;
                    }

                    updateAdminNotificationBadge();

                    // Hide mark all button
                    const markAllBtn = document.getElementById('markAllAdminReadBtn');
                    if (markAllBtn) markAllBtn.style.display = 'none';

                    if (typeof showToast === 'function') {
                        showToast('All notifications cleared', 'success');
                    }
                }
            })
            .catch(error => console.error('Error:', error));
    }

    // Mark all admin notifications as read
    function markAllAdminNotificationsRead() {
        fetch('../../../controller/admin/post/admin_notification_actions.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=mark_all_as_read'
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.querySelectorAll('.admin-notification-item').forEach(item => {
                        item.classList.remove('bg-amber-50');
                        item.classList.add('bg-white');
                        const dot = item.querySelector('.w-2.h-2.bg-amber-500');
                        if (dot) dot.remove();
                    });
                    updateAdminNotificationBadge();

                    // Hide mark all button
                    const markAllBtn = document.getElementById('markAllAdminReadBtn');
                    if (markAllBtn) markAllBtn.style.display = 'none';

                    if (typeof showToast === 'function') {
                        showToast('All notifications marked as read', 'success');
                    }
                }
            })
            .catch(error => console.error('Error:', error));
    }

    // Update admin notification badge count
    function updateAdminNotificationBadge() {
        fetch('../../../controller/admin/post/admin_notification_actions.php?action=get_unread_count', {
            method: 'GET'
        })
            .then(response => response.json())
            .then(data => {
                const badge = document.querySelector('.admin-notification-badge');
                const bell = document.getElementById('adminNotificationBell');

                if (data.count > 0) {
                    if (badge) {
                        badge.textContent = data.count > 99 ? '99+' : data.count;
                    } else {
                        const newBadge = document.createElement('span');
                        newBadge.className = 'absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center admin-notification-badge';
                        newBadge.textContent = data.count > 99 ? '99+' : data.count;
                        bell.appendChild(newBadge);
                    }
                } else {
                    if (badge) badge.remove();
                }
            });
    }

    // Periodically check for new notifications (every 30 seconds)
    setInterval(updateAdminNotificationBadge, 30000);
</script>

<style>
    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    /* Remove button appears on hover */
    .group:hover .group-hover\:opacity-100 {
        opacity: 1;
    }
</style>