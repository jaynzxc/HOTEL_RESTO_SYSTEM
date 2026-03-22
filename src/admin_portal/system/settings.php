<?php
/**
 * View - Admin Settings
 */
require_once '../../../controller/admin/get/settings_get.php';

$current_page = 'settings';
?>
<!DOCTYPE html>
<html lang="en">

  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin · Settings</title>
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
        max-width: 600px;
        width: 90%;
        max-height: 85vh;
        overflow-y: auto;
      }

      .settings-section {
        display: none;
      }

      .settings-section.active {
        display: block;
      }

      .toggle-switch {
        position: relative;
        display: inline-block;
        width: 50px;
        height: 24px;
      }

      .toggle-switch input {
        opacity: 0;
        width: 0;
        height: 0;
      }

      .toggle-slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ccc;
        transition: .4s;
        border-radius: 24px;
      }

      .toggle-slider:before {
        position: absolute;
        content: "";
        height: 20px;
        width: 20px;
        left: 2px;
        bottom: 2px;
        background-color: white;
        transition: .4s;
        border-radius: 50%;
      }

      input:checked+.toggle-slider {
        background-color: #f59e0b;
      }

      input:checked+.toggle-slider:before {
        transform: translateX(26px);
      }

      .status-badge {
        padding: 2px 8px;
        border-radius: 9999px;
        font-size: 10px;
        font-weight: 500;
      }

      .status-active {
        background-color: #d1fae5;
        color: #065f46;
      }

      .status-inactive {
        background-color: #fee2e2;
        color: #991b1b;
      }
    </style>
  </head>

  <body class="bg-slate-50 font-sans antialiased">

    <!-- APP CONTAINER -->
    <div class="min-h-screen flex flex-col lg:flex-row">

      <!-- ========== SIDEBAR ========== -->
      <?php require_once '../components/admin_nav.php'; ?>

      <!-- ========== MAIN CONTENT ========== -->
      <main class="flex-1 p-5 lg:p-8 overflow-y-auto bg-slate-50">

        <!-- header -->
        <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
          <div>
            <h1 class="text-2xl lg:text-3xl font-light text-slate-800">Settings</h1>
            <p class="text-sm text-slate-500 mt-0.5">configure system preferences, user permissions, and general
              settings</p>
          </div>
          <div class="flex gap-3 text-sm">
            <span class="bg-white border rounded-full px-4 py-2 flex items-center gap-2 shadow-sm">
              <i class="fas fa-calendar text-slate-400"></i> <?php echo $today; ?>
            </span>
            <span class="bg-white border rounded-full px-4 py-2 shadow-sm cursor-pointer hover:bg-slate-50 relative"
              id="notificationBell">
              <i class="fa-regular fa-bell"></i>
              <?php if ($unread_count > 0): ?>
                <span
                  class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-4 h-4 flex items-center justify-center"><?php echo $unread_count; ?></span>
              <?php endif; ?>
            </span>
          </div>
        </div>

        <!-- ===== SETTINGS TABS ===== -->
        <div class="flex flex-wrap gap-2 border-b border-slate-200 pb-2 mb-6">
          <button onclick="showSettingsTab('general')" id="tabGeneral"
            class="settings-tab px-4 py-2 bg-amber-600 text-white rounded-full text-sm">general</button>
          <button onclick="showSettingsTab('users')" id="tabUsers"
            class="settings-tab px-4 py-2 bg-white border border-slate-200 rounded-full text-sm hover:bg-slate-50">users
            & roles</button>
          <button onclick="showSettingsTab('notifications')" id="tabNotifications"
            class="settings-tab px-4 py-2 bg-white border border-slate-200 rounded-full text-sm hover:bg-slate-50">notifications</button>
          <button onclick="showSettingsTab('taxes')" id="tabTaxes"
            class="settings-tab px-4 py-2 bg-white border border-slate-200 rounded-full text-sm hover:bg-slate-50">taxes
            & fees</button>
          <button onclick="showSettingsTab('backup')" id="tabBackup"
            class="settings-tab px-4 py-2 bg-white border border-slate-200 rounded-full text-sm hover:bg-slate-50">backup</button>
          <button onclick="showSettingsTab('security')" id="tabSecurity"
            class="settings-tab px-4 py-2 bg-white border border-slate-200 rounded-full text-sm hover:bg-slate-50">security</button>
        </div>

        <!-- ===== GENERAL SETTINGS SECTION ===== -->
        <div id="sectionGeneral" class="settings-section active">
          <div class="bg-white rounded-2xl border border-slate-200 p-6 mb-8">
            <h2 class="font-semibold text-lg flex items-center gap-2 mb-4"><i
                class="fas fa-building text-amber-600"></i> hotel information</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
              <div>
                <label class="block text-xs text-slate-500 mb-1">hotel name</label>
                <input type="text" id="hotelName"
                  value="<?php echo htmlspecialchars($settings['general']['hotel_name'] ?? 'Hotel & Restaurant'); ?>"
                  class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:ring-1 focus:ring-amber-500 outline-none">
              </div>
              <div>
                <label class="block text-xs text-slate-500 mb-1">address</label>
                <input type="text" id="hotelAddress"
                  value="<?php echo htmlspecialchars($settings['general']['hotel_address'] ?? '123 Bonifacio St., Makati City'); ?>"
                  class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm">
              </div>
              <div>
                <label class="block text-xs text-slate-500 mb-1">contact number</label>
                <input type="text" id="hotelContact"
                  value="<?php echo htmlspecialchars($settings['general']['hotel_contact'] ?? '+63 2 1234 5678'); ?>"
                  class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm">
              </div>
              <div>
                <label class="block text-xs text-slate-500 mb-1">email</label>
                <input type="email" id="hotelEmail"
                  value="<?php echo htmlspecialchars($settings['general']['hotel_email'] ?? 'info@example.com'); ?>"
                  class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm">
              </div>
              <div>
                <label class="block text-xs text-slate-500 mb-1">tax ID / VAT</label>
                <input type="text" id="hotelTaxId"
                  value="<?php echo htmlspecialchars($settings['general']['hotel_tax_id'] ?? '123-456-789-000'); ?>"
                  class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm">
              </div>
              <div>
                <label class="block text-xs text-slate-500 mb-1">timezone</label>
                <select id="hotelTimezone"
                  class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm bg-white">
                  <option value="Asia/Manila" <?php echo ($settings['general']['hotel_timezone'] ?? 'Asia/Manila') == 'Asia/Manila' ? 'selected' : ''; ?>>Asia/Manila (GMT+8)</option>
                  <option value="Asia/Singapore" <?php echo ($settings['general']['hotel_timezone'] ?? '') == 'Asia/Singapore' ? 'selected' : ''; ?>>Asia/Singapore</option>
                  <option value="Asia/Tokyo" <?php echo ($settings['general']['hotel_timezone'] ?? '') == 'Asia/Tokyo' ? 'selected' : ''; ?>>Asia/Tokyo</option>
                </select>
              </div>
            </div>
            <div class="flex justify-end gap-3 mt-5">
              <button onclick="saveGeneralSettings()"
                class="bg-amber-600 text-white px-5 py-2 rounded-xl text-sm hover:bg-amber-700">save changes</button>
            </div>
          </div>

          <!-- Currency & Regional Settings -->
          <div class="bg-white rounded-2xl border border-slate-200 p-6">
            <h2 class="font-semibold text-lg flex items-center gap-2 mb-4"><i
                class="fas fa-currency text-amber-600">₱</i> currency & regional</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
              <div>
                <label class="block text-xs text-slate-500 mb-1">currency</label>
                <select id="hotelCurrency"
                  class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm bg-white">
                  <option value="PHP" <?php echo ($settings['regional']['currency'] ?? 'PHP') == 'PHP' ? 'selected' : ''; ?>>PHP (₱)</option>
                  <option value="USD" <?php echo ($settings['regional']['currency'] ?? '') == 'USD' ? 'selected' : ''; ?>>
                    USD ($)</option>
                  <option value="EUR" <?php echo ($settings['regional']['currency'] ?? '') == 'EUR' ? 'selected' : ''; ?>>
                    EUR (€)</option>
                  <option value="SGD" <?php echo ($settings['regional']['currency'] ?? '') == 'SGD' ? 'selected' : ''; ?>>
                    SGD (S$)</option>
                </select>
              </div>
              <div>
                <label class="block text-xs text-slate-500 mb-1">date format</label>
                <select id="dateFormat" class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm bg-white">
                  <option value="m/d/Y" <?php echo ($settings['regional']['date_format'] ?? 'm/d/Y') == 'm/d/Y' ? 'selected' : ''; ?>>MM/DD/YYYY</option>
                  <option value="d/m/Y" <?php echo ($settings['regional']['date_format'] ?? '') == 'd/m/Y' ? 'selected' : ''; ?>>DD/MM/YYYY</option>
                  <option value="Y-m-d" <?php echo ($settings['regional']['date_format'] ?? '') == 'Y-m-d' ? 'selected' : ''; ?>>YYYY-MM-DD</option>
                </select>
              </div>
              <div>
                <label class="block text-xs text-slate-500 mb-1">time format</label>
                <select id="timeFormat" class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm bg-white">
                  <option value="12" <?php echo ($settings['regional']['time_format'] ?? '12') == '12' ? 'selected' : ''; ?>>12-hour (AM/PM)</option>
                  <option value="24" <?php echo ($settings['regional']['time_format'] ?? '') == '24' ? 'selected' : ''; ?>>24-hour</option>
                </select>
              </div>
              <div>
                <label class="block text-xs text-slate-500 mb-1">week starts on</label>
                <select id="weekStart" class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm bg-white">
                  <option value="Monday" <?php echo ($settings['regional']['week_start'] ?? 'Monday') == 'Monday' ? 'selected' : ''; ?>>Monday</option>
                  <option value="Sunday" <?php echo ($settings['regional']['week_start'] ?? '') == 'Sunday' ? 'selected' : ''; ?>>Sunday</option>
                  <option value="Saturday" <?php echo ($settings['regional']['week_start'] ?? '') == 'Saturday' ? 'selected' : ''; ?>>Saturday</option>
                </select>
              </div>
            </div>
            <div class="flex justify-end gap-3 mt-5">
              <button onclick="saveRegionalSettings()"
                class="bg-amber-600 text-white px-5 py-2 rounded-xl text-sm hover:bg-amber-700">save regional
                settings</button>
            </div>
          </div>
        </div>

        <!-- ===== USERS & ROLES SECTION ===== -->
        <div id="sectionUsers" class="settings-section">
          <div class="bg-white rounded-2xl border border-slate-200 p-6 mb-8">
            <div class="flex items-center justify-between mb-4">
              <h2 class="font-semibold text-lg flex items-center gap-2"><i class="fas fa-users text-amber-600"></i> user
                roles & permissions</h2>
              <button onclick="openAddRoleModal()" class="bg-amber-600 text-white px-4 py-2 rounded-xl text-sm">+ add
                role</button>
            </div>
            <div class="overflow-x-auto">
              <table class="w-full text-sm">
                <thead class="text-slate-500 text-xs border-b">
                  <tr>
                    <th class="p-2 text-left">Role</th>
                    <th class="p-2 text-left">Users</th>
                    <th class="p-2 text-left">Permissions</th>
                    <th class="p-2 text-left">Status</th>
                    <th class="p-2 text-left">Actions</th>
                  </tr>
                </thead>
                <tbody class="divide-y" id="rolesTableBody">
                  <?php foreach ($roles as $role): ?>
                    <tr data-role-id="<?php echo $role['id']; ?>">
                      <td class="p-2 font-medium"><?php echo htmlspecialchars($role['role_name']); ?></td>
                      <td class="p-2">
                        <?php
                        $userCount = count(array_filter($users, function ($u) use ($role) {
                          return $u['role_id'] == $role['id'];
                        }));
                        echo $userCount;
                        ?>
                      </td>
                      <td class="p-2 text-xs">
                        <?php
                        $perms = json_decode($role['permissions'], true);
                        $permList = [];
                        if ($perms['full_access'] ?? false)
                          $permList[] = 'Full access';
                        if ($perms['hotel'] ?? false)
                          $permList[] = 'Hotel';
                        if ($perms['restaurant'] ?? false)
                          $permList[] = 'Restaurant';
                        if ($perms['customer'] ?? false)
                          $permList[] = 'Customer';
                        if ($perms['operations'] ?? false)
                          $permList[] = 'Operations';
                        if ($perms['reports'] ?? false)
                          $permList[] = 'Reports';
                        if ($perms['system'] ?? false)
                          $permList[] = 'System';
                        echo implode(', ', array_slice($permList, 0, 3)) . (count($permList) > 3 ? '...' : '');
                        ?>
                      </td>
                      <td class="p-2">
                        <span
                          class="status-badge <?php echo $role['is_active'] ? 'status-active' : 'status-inactive'; ?>">
                          <?php echo $role['is_active'] ? 'active' : 'inactive'; ?>
                        </span>
                      </td>
                      <td class="p-2">
                        <button onclick="editRole(<?php echo $role['id']; ?>)"
                          class="text-amber-700 text-xs hover:underline mr-2">edit</button>
                        <button
                          onclick="deleteRole(<?php echo $role['id']; ?>, '<?php echo htmlspecialchars($role['role_name']); ?>')"
                          class="text-rose-600 text-xs hover:underline">delete</button>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>

          <!-- User Management -->
          <div class="bg-white rounded-2xl border border-slate-200 p-6">
            <h2 class="font-semibold text-lg flex items-center gap-2 mb-4"><i class="fas fa-user text-amber-600"></i>
              user management</h2>
            <div class="overflow-x-auto">
              <table class="w-full text-sm">
                <thead class="text-slate-500 text-xs border-b">
                  <th class="p-2 text-left">User</th>
                  <th class="p-2 text-left">Role</th>
                  <th class="p-2 text-left">Email</th>
                  <th class="p-2 text-left">Status</th>
                  <th class="p-2 text-left">Last login</th>
                  <th class="p-2 text-left">Actions</th>
                  </tr>
                </thead>
                <tbody class="divide-y" id="usersTableBody">
                  <?php foreach ($users as $userItem): ?>
                    <tr>
                      <td class="p-2">
                        <div class="flex items-center gap-2">
                          <div
                            class="h-8 w-8 rounded-full bg-amber-200 flex items-center justify-center text-amber-800 font-bold text-xs">
                            <?php echo strtoupper(substr($userItem['full_name'], 0, 1)); ?>
                          </div>
                          <span><?php echo htmlspecialchars($userItem['full_name']); ?></span>
                        </div>
                      </td>
                      <td class="p-2">
                        <select onchange="updateUserRole(<?php echo $userItem['id']; ?>, this.value)"
                          class="border rounded-lg px-2 py-1 text-xs">
                          <option value="">No Role</option>
                          <?php foreach ($roles as $role): ?>
                            <option value="<?php echo $role['id']; ?>" <?php echo $userItem['role_id'] == $role['id'] ? 'selected' : ''; ?>>
                              <?php echo htmlspecialchars($role['role_name']); ?>
                            </option>
                          <?php endforeach; ?>
                        </select>
                      </td>
                      <td class="p-2"><?php echo htmlspecialchars($userItem['email']); ?></td>
                      <td class="p-2">
                        <span
                          class="status-badge <?php echo $userItem['status'] == 'active' ? 'status-active' : 'status-inactive'; ?>">
                          <?php echo $userItem['status']; ?>
                        </span>
                      </td>
                      <td class="p-2 text-xs">
                        <?php echo $userItem['last_login'] ? date('M d, H:i', strtotime($userItem['last_login'])) : 'Never'; ?>
                      </td>
                      <td class="p-2">
                        <button
                          onclick="toggleUserStatus(<?php echo $userItem['id']; ?>, '<?php echo $userItem['status']; ?>')"
                          class="text-amber-700 text-xs hover:underline">
                          <?php echo $userItem['status'] == 'active' ? 'suspend' : 'activate'; ?>
                        </button>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- ===== NOTIFICATIONS SECTION ===== -->
        <div id="sectionNotifications" class="settings-section">
          <div class="bg-white rounded-2xl border border-slate-200 p-6 mb-8">
            <h2 class="font-semibold text-lg flex items-center gap-2 mb-4"><i class="fas fa-bell text-amber-600"></i>
              notification preferences</h2>

            <div class="space-y-4">
              <div class="border rounded-lg p-4">
                <h3 class="font-medium mb-3">Email Notifications</h3>
                <div class="space-y-2">
                  <label class="flex items-center justify-between">
                    <span class="text-sm">New booking alerts</span>
                    <label class="toggle-switch">
                      <input type="checkbox" id="emailBookings" <?php echo ($settings['notifications']['email_bookings'] ?? true) ? 'checked' : ''; ?>>
                      <span class="toggle-slider"></span>
                    </label>
                  </label>
                  <label class="flex items-center justify-between">
                    <span class="text-sm">Check-in/out notifications</span>
                    <label class="toggle-switch">
                      <input type="checkbox" id="emailCheckin" <?php echo ($settings['notifications']['email_checkin'] ?? true) ? 'checked' : ''; ?>>
                      <span class="toggle-slider"></span>
                    </label>
                  </label>
                  <label class="flex items-center justify-between">
                    <span class="text-sm">Housekeeping updates</span>
                    <label class="toggle-switch">
                      <input type="checkbox" id="emailHousekeeping" <?php echo ($settings['notifications']['email_housekeeping'] ?? false) ? 'checked' : ''; ?>>
                      <span class="toggle-slider"></span>
                    </label>
                  </label>
                  <label class="flex items-center justify-between">
                    <span class="text-sm">Low inventory alerts</span>
                    <label class="toggle-switch">
                      <input type="checkbox" id="emailInventory" <?php echo ($settings['notifications']['email_inventory'] ?? true) ? 'checked' : ''; ?>>
                      <span class="toggle-slider"></span>
                    </label>
                  </label>
                </div>
              </div>

              <div class="border rounded-lg p-4">
                <h3 class="font-medium mb-3">SMS Notifications</h3>
                <div class="space-y-2">
                  <label class="flex items-center justify-between">
                    <span class="text-sm">Urgent requests</span>
                    <label class="toggle-switch">
                      <input type="checkbox" id="smsUrgent" <?php echo ($settings['notifications']['sms_urgent'] ?? true) ? 'checked' : ''; ?>>
                      <span class="toggle-slider"></span>
                    </label>
                  </label>
                  <label class="flex items-center justify-between">
                    <span class="text-sm">Maintenance alerts</span>
                    <label class="toggle-switch">
                      <input type="checkbox" id="smsMaintenance" <?php echo ($settings['notifications']['sms_maintenance'] ?? true) ? 'checked' : ''; ?>>
                      <span class="toggle-slider"></span>
                    </label>
                  </label>
                </div>
              </div>

              <div class="border rounded-lg p-4">
                <h3 class="font-medium mb-3">Daily Reports</h3>
                <div class="space-y-2">
                  <label class="flex items-center justify-between">
                    <span class="text-sm">Daily sales summary</span>
                    <label class="toggle-switch">
                      <input type="checkbox" id="reportSales" <?php echo ($settings['notifications']['report_sales'] ?? false) ? 'checked' : ''; ?>>
                      <span class="toggle-slider"></span>
                    </label>
                  </label>
                  <label class="flex items-center justify-between">
                    <span class="text-sm">Weekly performance report</span>
                    <label class="toggle-switch">
                      <input type="checkbox" id="reportWeekly" <?php echo ($settings['notifications']['report_weekly'] ?? true) ? 'checked' : ''; ?>>
                      <span class="toggle-slider"></span>
                    </label>
                  </label>
                </div>
              </div>
            </div>

            <div class="flex justify-end gap-3 mt-5">
              <button onclick="saveNotificationSettings()"
                class="bg-amber-600 text-white px-5 py-2 rounded-xl text-sm hover:bg-amber-700">save
                preferences</button>
            </div>
          </div>
        </div>

        <!-- ===== TAXES & FEES SECTION ===== -->
        <div id="sectionTaxes" class="settings-section">
          <div class="bg-white rounded-2xl border border-slate-200 p-6 mb-8">
            <h2 class="font-semibold text-lg flex items-center gap-2 mb-4"><i class="fas fa-percent text-amber-600"></i>
              taxes & fees configuration</h2>

            <div class="space-y-4">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                  <label class="block text-sm font-medium text-slate-700 mb-1">VAT (%)</label>
                  <input type="number" id="taxVat" value="<?php echo $settings['taxes']['tax_vat'] ?? 12; ?>" min="0"
                    max="100" step="0.1" class="w-full border border-slate-200 rounded-lg px-3 py-2">
                </div>
                <div>
                  <label class="block text-sm font-medium text-slate-700 mb-1">Service Charge (%)</label>
                  <input type="number" id="taxService"
                    value="<?php echo $settings['taxes']['tax_service_charge'] ?? 10; ?>" min="0" max="100" step="0.1"
                    class="w-full border border-slate-200 rounded-lg px-3 py-2">
                </div>
                <div>
                  <label class="block text-sm font-medium text-slate-700 mb-1">City Tax (per night)</label>
                  <div class="flex gap-2">
                    <span
                      class="inline-flex items-center px-3 bg-slate-100 border border-r-0 border-slate-200 rounded-l-lg">₱</span>
                    <input type="number" id="taxCity" value="<?php echo $settings['taxes']['tax_city'] ?? 50; ?>"
                      min="0" class="w-full border border-slate-200 rounded-r-lg px-3 py-2">
                  </div>
                </div>
                <div>
                  <label class="block text-sm font-medium text-slate-700 mb-1">Tourist Tax (%)</label>
                  <input type="number" id="taxTourist" value="<?php echo $settings['taxes']['tax_tourist'] ?? 0; ?>"
                    min="0" max="100" step="0.1" class="w-full border border-slate-200 rounded-lg px-3 py-2">
                </div>
              </div>

              <div class="border-t pt-4">
                <h3 class="font-medium mb-3">Additional Fees</h3>
                <div id="additionalFeesContainer" class="space-y-3">
                  <?php foreach ($additionalFees as $index => $fee): ?>
                    <div class="flex items-center gap-4">
                      <input type="text" value="<?php echo htmlspecialchars($fee['name']); ?>" placeholder="Fee name"
                        class="flex-1 border border-slate-200 rounded-lg px-3 py-2">
                      <div class="flex gap-2">
                        <span
                          class="inline-flex items-center px-3 bg-slate-100 border border-r-0 border-slate-200 rounded-l-lg">₱</span>
                        <input type="number" value="<?php echo $fee['amount']; ?>"
                          class="w-24 border border-slate-200 rounded-r-lg px-3 py-2">
                      </div>
                      <button onclick="this.closest('div').remove()" class="text-rose-600 hover:text-rose-800"><i
                          class="fas fa-trash-can"></i></button>
                    </div>
                  <?php endforeach; ?>
                </div>
                <button onclick="addFee()" class="mt-3 text-amber-600 text-sm hover:underline">
                  <i class="fas fa-plus mr-1"></i> add fee
                </button>
              </div>
            </div>

            <div class="flex justify-end gap-3 mt-5">
              <button onclick="saveTaxSettings()"
                class="bg-amber-600 text-white px-5 py-2 rounded-xl text-sm hover:bg-amber-700">save tax
                settings</button>
            </div>
          </div>
        </div>

        <!-- ===== BACKUP SECTION ===== -->
        <div id="sectionBackup" class="settings-section">
          <div class="bg-white rounded-2xl border border-slate-200 p-6 mb-8">
            <h2 class="font-semibold text-lg flex items-center gap-2 mb-4"><i
                class="fas fa-hard-drive text-amber-600"></i> backup & restore</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div class="border rounded-lg p-4">
                <h3 class="font-medium mb-3">Backup Status</h3>
                <div class="space-y-3">
                  <div class="flex justify-between items-center">
                    <span class="text-sm text-slate-600">Last backup</span>
                    <span class="text-sm font-medium" id="lastBackupTime">
                      <?php echo !empty($backupHistory) ? date('M d, Y H:i', strtotime($backupHistory[0]['created_at'])) : 'Never'; ?>
                    </span>
                  </div>
                  <div class="flex justify-between items-center">
                    <span class="text-sm text-slate-600">Backup size</span>
                    <span class="text-sm font-medium" id="backupSize">
                      <?php echo !empty($backupHistory) ? $backupHistory[0]['backup_size'] : 'N/A'; ?>
                    </span>
                  </div>
                  <div class="flex justify-between items-center">
                    <span class="text-sm text-slate-600">Auto-backup</span>
                    <span class="bg-green-100 text-green-700 px-2 py-0.5 rounded-full text-xs" id="backupFrequency">
                      <?php echo $settings['backup']['backup_frequency'] ?? 'daily'; ?>
                    </span>
                  </div>
                  <div class="flex justify-between items-center">
                    <span class="text-sm text-slate-600">Next scheduled</span>
                    <span class="text-sm font-medium" id="nextBackup">
                      <?php
                      $next = new DateTime();
                      $freq = $settings['backup']['backup_frequency'] ?? 'daily';
                      if ($freq == 'daily')
                        $next->modify('+1 day');
                      elseif ($freq == 'weekly')
                        $next->modify('+1 week');
                      elseif ($freq == 'monthly')
                        $next->modify('+1 month');
                      echo $next->format('M d, Y H:i');
                      ?>
                    </span>
                  </div>
                </div>
              </div>

              <div class="border rounded-lg p-4">
                <h3 class="font-medium mb-3">Backup Settings</h3>
                <div class="space-y-3">
                  <div>
                    <label class="block text-sm text-slate-600 mb-1">Auto-backup frequency</label>
                    <select id="backupFrequencySelect" class="w-full border border-slate-200 rounded-lg px-3 py-2">
                      <option value="daily" <?php echo ($settings['backup']['backup_frequency'] ?? 'daily') == 'daily' ? 'selected' : ''; ?>>Daily</option>
                      <option value="weekly" <?php echo ($settings['backup']['backup_frequency'] ?? '') == 'weekly' ? 'selected' : ''; ?>>Weekly</option>
                      <option value="monthly" <?php echo ($settings['backup']['backup_frequency'] ?? '') == 'monthly' ? 'selected' : ''; ?>>Monthly</option>
                      <option value="manual" <?php echo ($settings['backup']['backup_frequency'] ?? '') == 'manual' ? 'selected' : ''; ?>>Manual only</option>
                    </select>
                  </div>
                  <div>
                    <label class="block text-sm text-slate-600 mb-1">Backup time</label>
                    <input type="time" id="backupTime"
                      value="<?php echo $settings['backup']['backup_time'] ?? '03:00'; ?>"
                      class="w-full border border-slate-200 rounded-lg px-3 py-2">
                  </div>
                  <div>
                    <label class="flex items-center gap-2">
                      <input type="checkbox" id="backupIncludeFiles" <?php echo ($settings['backup']['backup_include_files'] ?? '1') == '1' ? 'checked' : ''; ?>
                        class="rounded border-slate-300">
                      <span class="text-sm">Include media files</span>
                    </label>
                  </div>
                  <div>
                    <label class="flex items-center gap-2">
                      <input type="checkbox" id="backupCompress" <?php echo ($settings['backup']['backup_compress'] ?? '1') == '1' ? 'checked' : ''; ?> class="rounded border-slate-300">
                      <span class="text-sm">Compress backups</span>
                    </label>
                  </div>
                </div>
              </div>
            </div>

            <div class="flex gap-3 mt-5">
              <button onclick="backupNow()"
                class="flex-1 bg-amber-600 text-white py-2 rounded-xl text-sm hover:bg-amber-700">backup now</button>
              <button onclick="saveBackupSettings()"
                class="flex-1 border border-amber-600 text-amber-700 py-2 rounded-xl text-sm hover:bg-amber-50">save
                settings</button>
            </div>
          </div>

          <!-- Backup History -->
          <div class="bg-white rounded-2xl border border-slate-200 p-6">
            <h2 class="font-semibold text-lg flex items-center gap-2 mb-4"><i class="fas fa-clock text-amber-600"></i>
              backup history</h2>
            <div class="overflow-x-auto">
              <table class="w-full text-sm">
                <thead class="text-slate-500 text-xs border-b">
                  <th class="p-2 text-left">Date</th>
                  <th class="p-2 text-left">Size</th>
                  <th class="p-2 text-left">Type</th>
                  <th class="p-2 text-left">Status</th>
                  <th class="p-2 text-left">Actions</th>
                  </tr>
                </thead>
                <tbody class="divide-y" id="backupHistoryTable">
                  <?php foreach ($backupHistory as $backup): ?>
                    <tr>
                      <td class="p-2"><?php echo date('M d, Y H:i', strtotime($backup['created_at'])); ?></td>
                      <td class="p-2"><?php echo $backup['backup_size'] ?? 'N/A'; ?></td>
                      <td class="p-2 capitalize"><?php echo $backup['backup_type']; ?></td>
                      <td class="p-2"><span
                          class="status-badge <?php echo $backup['status'] == 'completed' ? 'status-active' : 'status-inactive'; ?>"><?php echo $backup['status']; ?></span>
                      </td>
                      <td class="p-2">
                        <button onclick="restoreBackup(<?php echo $backup['id']; ?>)"
                          class="text-amber-700 text-xs hover:underline mr-2">restore</button>
                        <button onclick="downloadBackup(<?php echo $backup['id']; ?>)"
                          class="text-blue-600 text-xs hover:underline">download</button>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- ===== SECURITY SECTION ===== -->
        <div id="sectionSecurity" class="settings-section">
          <!-- <div class="bg-white rounded-2xl border border-slate-200 p-6 mb-8">
            <h2 class="font-semibold text-lg flex items-center gap-2 mb-4"><i
                class="fa-solid fa-shield text-amber-600"></i> security settings</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div class="border rounded-lg p-4">
                <h3 class="font-medium mb-3">Authentication</h3>
                <div class="space-y-3">
                  <div class="flex items-center justify-between">
                    <span class="text-sm">Two-factor authentication</span>
                    <label class="toggle-switch">
                      <input type="checkbox" id="security2fa" <?php echo ($settings['security']['security_2fa'] ?? '1') == '1' ? 'checked' : ''; ?>>
                      <span class="toggle-slider"></span>
                    </label>
                  </div>
                  <div class="flex items-center justify-between">
                    <span class="text-sm">Remember me</span>
                    <label class="toggle-switch">
                      <input type="checkbox" id="securityRemember" <?php echo ($settings['security']['security_remember_me'] ?? '1') == '1' ? 'checked' : ''; ?>>
                      <span class="toggle-slider"></span>
                    </label>
                  </div>
                  <div>
                    <label class="block text-sm text-slate-600 mb-1">Session timeout</label>
                    <select id="securityTimeout" class="w-full border border-slate-200 rounded-lg px-3 py-2">
                      <option value="30" <?php echo ($settings['security']['security_session_timeout'] ?? '30') == '30' ? 'selected' : ''; ?>>30 minutes</option>
                      <option value="60" <?php echo ($settings['security']['security_session_timeout'] ?? '') == '60' ? 'selected' : ''; ?>>1 hour</option>
                      <option value="120" <?php echo ($settings['security']['security_session_timeout'] ?? '') == '120' ? 'selected' : ''; ?>>2 hours</option>
                      <option value="240" <?php echo ($settings['security']['security_session_timeout'] ?? '') == '240' ? 'selected' : ''; ?>>4 hours</option>
                    </select>
                  </div>
                </div>
              </div>

              <div class="border rounded-lg p-4">
                <h3 class="font-medium mb-3">Password Policy</h3>
                <div class="space-y-3">
                  <div>
                    <label class="flex items-center gap-2">
                      <input type="checkbox" id="policyMinLength" <?php echo ($settings['security']['security_min_length'] ?? '1') == '1' ? 'checked' : ''; ?>
                        class="rounded border-slate-300">
                      <span class="text-sm">Minimum length (8 characters)</span>
                    </label>
                  </div>
                  <div>
                    <label class="flex items-center gap-2">
                      <input type="checkbox" id="policyUppercase" <?php echo ($settings['security']['security_uppercase'] ?? '1') == '1' ? 'checked' : ''; ?>
                        class="rounded border-slate-300">
                      <span class="text-sm">Require uppercase letter</span>
                    </label>
                  </div>
                  <div>
                    <label class="flex items-center gap-2">
                      <input type="checkbox" id="policyNumber" <?php echo ($settings['security']['security_number'] ?? '1') == '1' ? 'checked' : ''; ?> class="rounded border-slate-300">
                      <span class="text-sm">Require number</span>
                    </label>
                  </div>
                  <div>
                    <label class="flex items-center gap-2">
                      <input type="checkbox" id="policySpecial" <?php echo ($settings['security']['security_special'] ?? '0') == '1' ? 'checked' : ''; ?> class="rounded border-slate-300">
                      <span class="text-sm">Require special character</span>
                    </label>
                  </div>
                  <div>
                    <label class="block text-sm text-slate-600 mb-1">Password expiry</label>
                    <select id="policyExpiry" class="w-full border border-slate-200 rounded-lg px-3 py-2">
                      <option value="0" <?php echo ($settings['security']['security_password_expiry'] ?? '90') == '0' ? 'selected' : ''; ?>>Never</option>
                      <option value="30" <?php echo ($settings['security']['security_password_expiry'] ?? '') == '30' ? 'selected' : ''; ?>>30 days</option>
                      <option value="60" <?php echo ($settings['security']['security_password_expiry'] ?? '') == '60' ? 'selected' : ''; ?>>60 days</option>
                      <option value="90" <?php echo ($settings['security']['security_password_expiry'] ?? '90') == '90' ? 'selected' : ''; ?>>90 days</option>
                    </select>
                  </div>
                </div>
              </div>
            </div>

            <div class="flex justify-end gap-3 mt-5">
              <button onclick="saveSecuritySettings()"
                class="bg-amber-600 text-white px-5 py-2 rounded-xl text-sm hover:bg-amber-700">save security
                settings</button>
            </div>
          </div> -->

          <!-- Login History -->
          <div class="bg-white rounded-2xl border border-slate-200 p-6">
            <h2 class="font-semibold text-lg flex items-center gap-2 mb-4"><i class="fas fa-clock text-amber-600"></i>
              recent login activity</h2>
            <div class="overflow-x-auto">
              <table class="w-full text-sm">
                <thead class="text-slate-500 text-xs border-b">
                  <th class="p-2 text-left">User</th>
                  <th class="p-2 text-left">Time</th>
                  <th class="p-2 text-left">IP Address</th>
                  <th class="p-2 text-left">Device</th>
                  <th class="p-2 text-left">Status</th>
                  </tr>
                </thead>
                <tbody class="divide-y">
                  <?php foreach ($loginHistory as $login): ?>
                    <tr>
                      <td class="p-2">
                        <?php echo htmlspecialchars($login['full_name'] ?? $login['user_name'] ?? 'Unknown'); ?>
                      </td>
                      <td class="p-2"><?php echo date('M d, H:i', strtotime($login['created_at'])); ?></td>
                      <td class="p-2"><?php echo $login['ip_address'] ?? 'N/A'; ?></td>
                      <td class="p-2 text-xs">
                        <?php echo $login['user_agent'] ? substr($login['user_agent'], 0, 50) . '...' : 'N/A'; ?>
                      <td class="p-2">
                        <span
                          class="status-badge <?php echo $login['status'] == 'success' ? 'status-active' : 'status-inactive'; ?>">
                          <?php echo $login['status']; ?>
                        </span>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                  <?php if (empty($loginHistory)): ?>
                    <tr>
                      <td colspan="5" class="p-8 text-center text-slate-500">No login history found</td>
                    </tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- ===== DANGER ZONE ===== -->
        <div class="bg-rose-50 border border-rose-200 rounded-2xl p-5 mt-8">
          <h2 class="font-semibold text-lg flex items-center gap-2 mb-3 text-rose-700"><i
              class="fas fa-circle-exclamation text-rose-600"></i> danger zone</h2>
          <div class="space-y-3">
            <div class="flex items-center justify-between">
              <div>
                <p class="font-medium">export all data</p>
                <p class="text-xs text-slate-500">download complete system data as JSON</p>
              </div>
              <button onclick="exportAllData()"
                class="border border-rose-300 text-rose-700 px-4 py-2 rounded-xl text-sm hover:bg-rose-100">export</button>
            </div>
            <div class="flex items-center justify-between">
              <div>
                <p class="font-medium">clear system cache</p>
                <p class="text-xs text-slate-500">temporary files and cached data</p>
              </div>
              <button onclick="clearCache()"
                class="border border-rose-300 text-rose-700 px-4 py-2 rounded-xl text-sm hover:bg-rose-100">clear</button>
            </div>
            <div class="flex items-center justify-between">
              <div>
                <p class="font-medium text-rose-700">delete system data</p>
                <p class="text-xs text-slate-500">permanent deletion (use with caution)</p>
              </div>
              <button onclick="confirmDeleteData()"
                class="bg-rose-600 text-white px-4 py-2 rounded-xl text-sm hover:bg-rose-700">delete</button>
            </div>
          </div>
        </div>
      </main>
    </div>

    <!-- Add Role Modal -->
    <div id="addRoleModal" class="modal">
      <div class="modal-content p-6">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-xl font-semibold">Add New Role</h3>
          <button onclick="closeModal('addRoleModal')" class="text-slate-400 hover:text-slate-600">
            <i class="fa-solid fa-xmark text-2xl"></i>
          </button>
        </div>
        <form id="addRoleForm" onsubmit="saveNewRole(event)">
          <div class="mb-4">
            <label class="block text-sm font-medium text-slate-700 mb-1">Role Name</label>
            <input type="text" id="roleName" required class="w-full border border-slate-200 rounded-lg px-3 py-2">
          </div>

          <div class="mb-4">
            <label class="block text-sm font-medium text-slate-700 mb-1">Description</label>
            <textarea id="roleDescription" rows="2"
              class="w-full border border-slate-200 rounded-lg px-3 py-2"></textarea>
          </div>

          <div class="mb-4">
            <label class="block text-sm font-medium text-slate-700 mb-1">Permissions</label>
            <div class="space-y-2 border rounded-lg p-3">
              <label class="flex items-center gap-2">
                <input type="checkbox" id="permFull" class="rounded border-slate-300"
                  onchange="toggleAllPermissions(this.checked)">
                <span class="text-sm font-medium">Full system access</span>
              </label>
              <div class="ml-6 space-y-2 border-l-2 pl-3">
                <label class="flex items-center gap-2">
                  <input type="checkbox" id="permHotel" class="perm-checkbox rounded border-slate-300">
                  <span class="text-sm">Hotel management</span>
                </label>
                <label class="flex items-center gap-2">
                  <input type="checkbox" id="permRestaurant" class="perm-checkbox rounded border-slate-300">
                  <span class="text-sm">Restaurant management</span>
                </label>
                <label class="flex items-center gap-2">
                  <input type="checkbox" id="permCustomer" class="perm-checkbox rounded border-slate-300">
                  <span class="text-sm">Customer management</span>
                </label>
                <label class="flex items-center gap-2">
                  <input type="checkbox" id="permOps" class="perm-checkbox rounded border-slate-300">
                  <span class="text-sm">Operations</span>
                </label>
                <label class="flex items-center gap-2">
                  <input type="checkbox" id="permReports" class="perm-checkbox rounded border-slate-300">
                  <span class="text-sm">Reports & analytics</span>
                </label>
                <label class="flex items-center gap-2">
                  <input type="checkbox" id="permSystem" class="perm-checkbox rounded border-slate-300">
                  <span class="text-sm">System settings</span>
                </label>
              </div>
            </div>
          </div>

          <div class="flex gap-3 mt-4">
            <button type="submit" class="flex-1 bg-amber-600 text-white py-2 rounded-lg hover:bg-amber-700">Add
              Role</button>
            <button type="button" onclick="closeModal('addRoleModal')"
              class="flex-1 border border-slate-200 py-2 rounded-lg hover:bg-slate-50">Cancel</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Edit Role Modal -->
    <div id="editRoleModal" class="modal">
      <div class="modal-content p-6">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-xl font-semibold">Edit Role</h3>
          <button onclick="closeModal('editRoleModal')" class="text-slate-400 hover:text-slate-600">
            <i class="fa-solid fa-xmark text-2xl"></i>
          </button>
        </div>
        <form id="editRoleForm" onsubmit="saveEditedRole(event)">
          <input type="hidden" id="editRoleId">
          <div class="mb-4">
            <label class="block text-sm font-medium text-slate-700 mb-1">Role Name</label>
            <input type="text" id="editRoleName" required class="w-full border border-slate-200 rounded-lg px-3 py-2">
          </div>

          <div class="mb-4">
            <label class="block text-sm font-medium text-slate-700 mb-1">Description</label>
            <textarea id="editRoleDescription" rows="2"
              class="w-full border border-slate-200 rounded-lg px-3 py-2"></textarea>
          </div>

          <div class="mb-4">
            <label class="block text-sm font-medium text-slate-700 mb-1">Permissions</label>
            <div class="space-y-2 border rounded-lg p-3">
              <label class="flex items-center gap-2">
                <input type="checkbox" id="editPermFull" class="rounded border-slate-300"
                  onchange="toggleEditAllPermissions(this.checked)">
                <span class="text-sm font-medium">Full system access</span>
              </label>
              <div class="ml-6 space-y-2 border-l-2 pl-3">
                <label class="flex items-center gap-2">
                  <input type="checkbox" id="editPermHotel" class="edit-perm-checkbox rounded border-slate-300">
                  <span class="text-sm">Hotel management</span>
                </label>
                <label class="flex items-center gap-2">
                  <input type="checkbox" id="editPermRestaurant" class="edit-perm-checkbox rounded border-slate-300">
                  <span class="text-sm">Restaurant management</span>
                </label>
                <label class="flex items-center gap-2">
                  <input type="checkbox" id="editPermCustomer" class="edit-perm-checkbox rounded border-slate-300">
                  <span class="text-sm">Customer management</span>
                </label>
                <label class="flex items-center gap-2">
                  <input type="checkbox" id="editPermOps" class="edit-perm-checkbox rounded border-slate-300">
                  <span class="text-sm">Operations</span>
                </label>
                <label class="flex items-center gap-2">
                  <input type="checkbox" id="editPermReports" class="edit-perm-checkbox rounded border-slate-300">
                  <span class="text-sm">Reports & analytics</span>
                </label>
                <label class="flex items-center gap-2">
                  <input type="checkbox" id="editPermSystem" class="edit-perm-checkbox rounded border-slate-300">
                  <span class="text-sm">System settings</span>
                </label>
              </div>
            </div>
          </div>

          <div class="mb-4">
            <label class="flex items-center gap-2">
              <input type="checkbox" id="editRoleActive" class="rounded border-slate-300">
              <span class="text-sm">Active</span>
            </label>
          </div>

          <div class="flex gap-3 mt-4">
            <button type="submit" class="flex-1 bg-amber-600 text-white py-2 rounded-lg hover:bg-amber-700">Save
              Changes</button>
            <button type="button" onclick="closeModal('editRoleModal')"
              class="flex-1 border border-slate-200 py-2 rounded-lg hover:bg-slate-50">Cancel</button>
          </div>
        </form>
      </div>
    </div>

    <script>
      // ========== TAB NAVIGATION ==========
      function showSettingsTab(tabName) {
        document.querySelectorAll('.settings-section').forEach(section => {
          section.classList.remove('active');
        });
        document.getElementById(`section${tabName.charAt(0).toUpperCase() + tabName.slice(1)}`).classList.add('active');

        document.querySelectorAll('.settings-tab').forEach(tab => {
          tab.classList.remove('bg-amber-600', 'text-white');
          tab.classList.add('bg-white', 'border', 'border-slate-200', 'text-slate-700');
        });

        const activeTab = document.getElementById(`tab${tabName.charAt(0).toUpperCase() + tabName.slice(1)}`);
        activeTab.classList.remove('bg-white', 'border', 'border-slate-200', 'text-slate-700');
        activeTab.classList.add('bg-amber-600', 'text-white');
      }

      // ========== MODAL FUNCTIONS ==========
      function openModal(modalId) {
        document.getElementById(modalId).classList.add('show');
      }

      function closeModal(modalId) {
        document.getElementById(modalId).classList.remove('show');
      }

      function openAddRoleModal() {
        document.getElementById('addRoleForm').reset();
        document.getElementById('permFull').checked = false;
        document.querySelectorAll('.perm-checkbox').forEach(cb => cb.checked = false);
        openModal('addRoleModal');
      }

      function toggleAllPermissions(checked) {
        document.querySelectorAll('.perm-checkbox').forEach(cb => cb.checked = checked);
      }

      function toggleEditAllPermissions(checked) {
        document.querySelectorAll('.edit-perm-checkbox').forEach(cb => cb.checked = checked);
      }

      // ========== GENERAL SETTINGS ==========
      function saveGeneralSettings() {
        const formData = new FormData();
        formData.append('action', 'save_general');
        formData.append('hotel_name', document.getElementById('hotelName').value);
        formData.append('hotel_address', document.getElementById('hotelAddress').value);
        formData.append('hotel_contact', document.getElementById('hotelContact').value);
        formData.append('hotel_email', document.getElementById('hotelEmail').value);
        formData.append('hotel_tax_id', document.getElementById('hotelTaxId').value);
        formData.append('hotel_timezone', document.getElementById('hotelTimezone').value);

        Swal.fire({
          title: 'Saving...',
          text: 'Please wait',
          allowOutsideClick: false,
          didOpen: () => Swal.showLoading()
        });

        fetch('../../../controller/admin/post/settings_post.php', {
          method: 'POST',
          body: formData
        })
          .then(response => response.json())
          .then(data => {
            Swal.close();
            if (data.success) {
              Swal.fire({ title: 'Success!', text: data.message, icon: 'success', confirmButtonColor: '#d97706' });
            } else {
              Swal.fire({ title: 'Error', text: data.message, icon: 'error', confirmButtonColor: '#d97706' });
            }
          })
          .catch(error => {
            Swal.close();
            Swal.fire({ title: 'Error', text: 'Failed to save settings', icon: 'error', confirmButtonColor: '#d97706' });
          });
      }

      function saveRegionalSettings() {
        const formData = new FormData();
        formData.append('action', 'save_regional');
        formData.append('currency', document.getElementById('hotelCurrency').value);
        formData.append('date_format', document.getElementById('dateFormat').value);
        formData.append('time_format', document.getElementById('timeFormat').value);
        formData.append('week_start', document.getElementById('weekStart').value);

        Swal.fire({
          title: 'Saving...',
          text: 'Please wait',
          allowOutsideClick: false,
          didOpen: () => Swal.showLoading()
        });

        fetch('../../../controller/admin/post/settings_post.php', {
          method: 'POST',
          body: formData
        })
          .then(response => response.json())
          .then(data => {
            Swal.close();
            if (data.success) {
              Swal.fire({ title: 'Success!', text: data.message, icon: 'success', confirmButtonColor: '#d97706' });
            } else {
              Swal.fire({ title: 'Error', text: data.message, icon: 'error', confirmButtonColor: '#d97706' });
            }
          });
      }

      // ========== NOTIFICATION SETTINGS ==========
      function saveNotificationSettings() {
        const formData = new FormData();
        formData.append('action', 'save_notifications');
        formData.append('email_bookings', document.getElementById('emailBookings').checked ? '1' : '0');
        formData.append('email_checkin', document.getElementById('emailCheckin').checked ? '1' : '0');
        formData.append('email_housekeeping', document.getElementById('emailHousekeeping').checked ? '1' : '0');
        formData.append('email_inventory', document.getElementById('emailInventory').checked ? '1' : '0');
        formData.append('sms_urgent', document.getElementById('smsUrgent').checked ? '1' : '0');
        formData.append('sms_maintenance', document.getElementById('smsMaintenance').checked ? '1' : '0');
        formData.append('report_sales', document.getElementById('reportSales').checked ? '1' : '0');
        formData.append('report_weekly', document.getElementById('reportWeekly').checked ? '1' : '0');

        Swal.fire({
          title: 'Saving...',
          text: 'Please wait',
          allowOutsideClick: false,
          didOpen: () => Swal.showLoading()
        });

        fetch('../../../controller/admin/post/settings_post.php', {
          method: 'POST',
          body: formData
        })
          .then(response => response.json())
          .then(data => {
            Swal.close();
            if (data.success) {
              Swal.fire({ title: 'Success!', text: data.message, icon: 'success', confirmButtonColor: '#d97706' });
            } else {
              Swal.fire({ title: 'Error', text: data.message, icon: 'error', confirmButtonColor: '#d97706' });
            }
          });
      }

      // ========== TAXES & FEES ==========
      function addFee() {
        const container = document.getElementById('additionalFeesContainer');
        const newFee = document.createElement('div');
        newFee.className = 'flex items-center gap-4';
        newFee.innerHTML = `
                <input type="text" placeholder="Fee name" class="flex-1 border border-slate-200 rounded-lg px-3 py-2">
                <div class="flex gap-2">
                    <span class="inline-flex items-center px-3 bg-slate-100 border border-r-0 border-slate-200 rounded-l-lg">₱</span>
                    <input type="number" value="0" class="w-24 border border-slate-200 rounded-r-lg px-3 py-2">
                </div>
                <button onclick="this.closest('div').remove()" class="text-rose-600 hover:text-rose-800"><i class="fas fa-trash-can"></i></button>
            `;
        container.appendChild(newFee);
      }

      function saveTaxSettings() {
        const fees = [];
        document.querySelectorAll('#additionalFeesContainer > div').forEach(div => {
          const inputs = div.querySelectorAll('input');
          if (inputs[0] && inputs[0].value.trim()) {
            fees.push({
              name: inputs[0].value,
              amount: parseFloat(inputs[1].value) || 0
            });
          }
        });

        const formData = new FormData();
        formData.append('action', 'save_taxes');
        formData.append('tax_vat', document.getElementById('taxVat').value);
        formData.append('tax_service', document.getElementById('taxService').value);
        formData.append('tax_city', document.getElementById('taxCity').value);
        formData.append('tax_tourist', document.getElementById('taxTourist').value);
        formData.append('additional_fees', JSON.stringify(fees));

        Swal.fire({
          title: 'Saving...',
          text: 'Please wait',
          allowOutsideClick: false,
          didOpen: () => Swal.showLoading()
        });

        fetch('../../../controller/admin/post/settings_post.php', {
          method: 'POST',
          body: formData
        })
          .then(response => response.json())
          .then(data => {
            Swal.close();
            if (data.success) {
              Swal.fire({ title: 'Success!', text: data.message, icon: 'success', confirmButtonColor: '#d97706' });
            } else {
              Swal.fire({ title: 'Error', text: data.message, icon: 'error', confirmButtonColor: '#d97706' });
            }
          });
      }

      // ========== BACKUP FUNCTIONS ==========
      function backupNow() {
        Swal.fire({
          title: 'Creating Backup...',
          text: 'Please wait',
          allowOutsideClick: false,
          didOpen: () => Swal.showLoading()
        });

        const formData = new FormData();
        formData.append('action', 'create_backup');
        formData.append('backup_type', 'manual');

        fetch('../../../controller/admin/post/settings_post.php', {
          method: 'POST',
          body: formData
        })
          .then(response => response.json())
          .then(data => {
            Swal.close();
            if (data.success) {
              Swal.fire({ title: 'Success!', text: data.message, icon: 'success', confirmButtonColor: '#d97706' }).then(() => {
                location.reload();
              });
            } else {
              Swal.fire({ title: 'Error', text: data.message, icon: 'error', confirmButtonColor: '#d97706' });
            }
          });
      }

      function saveBackupSettings() {
        const formData = new FormData();
        formData.append('action', 'save_backup');
        formData.append('backup_frequency', document.getElementById('backupFrequencySelect').value);
        formData.append('backup_time', document.getElementById('backupTime').value);
        formData.append('backup_include_files', document.getElementById('backupIncludeFiles').checked ? '1' : '0');
        formData.append('backup_compress', document.getElementById('backupCompress').checked ? '1' : '0');

        Swal.fire({
          title: 'Saving...',
          text: 'Please wait',
          allowOutsideClick: false,
          didOpen: () => Swal.showLoading()
        });

        fetch('../../../controller/admin/post/settings_post.php', {
          method: 'POST',
          body: formData
        })
          .then(response => response.json())
          .then(data => {
            Swal.close();
            if (data.success) {
              Swal.fire({ title: 'Success!', text: data.message, icon: 'success', confirmButtonColor: '#d97706' }).then(() => {
                location.reload();
              });
            } else {
              Swal.fire({ title: 'Error', text: data.message, icon: 'error', confirmButtonColor: '#d97706' });
            }
          });
      }

      function restoreBackup(backupId) {
        Swal.fire({
          title: 'Restore Backup?',
          text: 'This will restore data from the backup. Current data will be overwritten.',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#d97706',
          cancelButtonColor: '#6b7280',
          confirmButtonText: 'Yes, restore'
        }).then((result) => {
          if (result.isConfirmed) {
            Swal.fire({
              title: 'Restoring...',
              text: 'Please wait',
              allowOutsideClick: false,
              didOpen: () => Swal.showLoading()
            });

            const formData = new FormData();
            formData.append('action', 'restore_backup');
            formData.append('backup_id', backupId);

            fetch('../../../controller/admin/post/settings_post.php', {
              method: 'POST',
              body: formData
            })
              .then(response => response.json())
              .then(data => {
                Swal.close();
                if (data.success) {
                  Swal.fire({ title: 'Success!', text: data.message, icon: 'success', confirmButtonColor: '#d97706' }).then(() => {
                    location.reload();
                  });
                } else {
                  Swal.fire({ title: 'Error', text: data.message, icon: 'error', confirmButtonColor: '#d97706' });
                }
              });
          }
        });
      }

      function downloadBackup(backupId) {
        Swal.fire({
          title: 'Downloading...',
          text: 'Preparing backup for download',
          allowOutsideClick: false,
          didOpen: () => Swal.showLoading()
        });

        const formData = new FormData();
        formData.append('action', 'download_backup');
        formData.append('backup_id', backupId);

        fetch('../../../controller/admin/post/settings_post.php', {
          method: 'POST',
          body: formData
        })
          .then(response => response.json())
          .then(data => {
            Swal.close();
            if (data.success) {
              Swal.fire({ title: 'Success!', text: data.message, icon: 'success', confirmButtonColor: '#d97706' });
            } else {
              Swal.fire({ title: 'Error', text: data.message, icon: 'error', confirmButtonColor: '#d97706' });
            }
          });
      }

      // ========== SECURITY SETTINGS ==========
      function saveSecuritySettings() {
        const formData = new FormData();
        formData.append('action', 'save_security');
        formData.append('security_2fa', document.getElementById('security2fa').checked ? '1' : '0');
        formData.append('security_remember', document.getElementById('securityRemember').checked ? '1' : '0');
        formData.append('security_timeout', document.getElementById('securityTimeout').value);
        formData.append('security_min_length', document.getElementById('policyMinLength').checked ? '1' : '0');
        formData.append('security_uppercase', document.getElementById('policyUppercase').checked ? '1' : '0');
        formData.append('security_number', document.getElementById('policyNumber').checked ? '1' : '0');
        formData.append('security_special', document.getElementById('policySpecial').checked ? '1' : '0');
        formData.append('security_expiry', document.getElementById('policyExpiry').value);

        Swal.fire({
          title: 'Saving...',
          text: 'Please wait',
          allowOutsideClick: false,
          didOpen: () => Swal.showLoading()
        });

        fetch('../../../controller/admin/post/settings_post.php', {
          method: 'POST',
          body: formData
        })
          .then(response => response.json())
          .then(data => {
            Swal.close();
            if (data.success) {
              Swal.fire({ title: 'Success!', text: data.message, icon: 'success', confirmButtonColor: '#d97706' });
            } else {
              Swal.fire({ title: 'Error', text: data.message, icon: 'error', confirmButtonColor: '#d97706' });
            }
          });
      }

      // ========== USER ROLE FUNCTIONS ==========
      function saveNewRole(event) {
        event.preventDefault();

        const permissions = {
          full_access: document.getElementById('permFull').checked,
          hotel: document.getElementById('permHotel').checked,
          restaurant: document.getElementById('permRestaurant').checked,
          customer: document.getElementById('permCustomer').checked,
          operations: document.getElementById('permOps').checked,
          reports: document.getElementById('permReports').checked,
          system: document.getElementById('permSystem').checked
        };

        const formData = new FormData();
        formData.append('action', 'add_role');
        formData.append('role_name', document.getElementById('roleName').value);
        formData.append('description', document.getElementById('roleDescription').value);
        formData.append('permissions', JSON.stringify(permissions));

        Swal.fire({
          title: 'Adding Role...',
          text: 'Please wait',
          allowOutsideClick: false,
          didOpen: () => Swal.showLoading()
        });

        fetch('../../../controller/admin/post/settings_post.php', {
          method: 'POST',
          body: formData
        })
          .then(response => response.json())
          .then(data => {
            Swal.close();
            if (data.success) {
              Swal.fire({ title: 'Success!', text: data.message, icon: 'success', confirmButtonColor: '#d97706' }).then(() => {
                location.reload();
              });
            } else {
              Swal.fire({ title: 'Error', text: data.message, icon: 'error', confirmButtonColor: '#d97706' });
            }
          });

        closeModal('addRoleModal');
      }

      function editRole(roleId) {
        // Fetch role data from the table row
        const row = document.querySelector(`tr[data-role-id="${roleId}"]`);
        const roleName = row.cells[0].textContent;
        const roleDesc = row.cells[1].textContent;
        const permText = row.cells[2].textContent;

        document.getElementById('editRoleId').value = roleId;
        document.getElementById('editRoleName').value = roleName;
        document.getElementById('editRoleDescription').value = '';

        // Set permissions based on text
        const hasFullAccess = permText.includes('Full access');
        document.getElementById('editPermFull').checked = hasFullAccess;

        document.getElementById('editPermHotel').checked = permText.includes('Hotel') || hasFullAccess;
        document.getElementById('editPermRestaurant').checked = permText.includes('Restaurant') || hasFullAccess;
        document.getElementById('editPermCustomer').checked = permText.includes('Customer') || hasFullAccess;
        document.getElementById('editPermOps').checked = permText.includes('Operations') || hasFullAccess;
        document.getElementById('editPermReports').checked = permText.includes('Reports') || hasFullAccess;
        document.getElementById('editPermSystem').checked = permText.includes('System') || hasFullAccess;

        document.getElementById('editRoleActive').checked = row.cells[3].textContent.includes('active');

        openModal('editRoleModal');
      }

      function saveEditedRole(event) {
        event.preventDefault();

        const permissions = {
          full_access: document.getElementById('editPermFull').checked,
          hotel: document.getElementById('editPermHotel').checked,
          restaurant: document.getElementById('editPermRestaurant').checked,
          customer: document.getElementById('editPermCustomer').checked,
          operations: document.getElementById('editPermOps').checked,
          reports: document.getElementById('editPermReports').checked,
          system: document.getElementById('editPermSystem').checked
        };

        const formData = new FormData();
        formData.append('action', 'update_role');
        formData.append('role_id', document.getElementById('editRoleId').value);
        formData.append('role_name', document.getElementById('editRoleName').value);
        formData.append('description', document.getElementById('editRoleDescription').value);
        formData.append('permissions', JSON.stringify(permissions));
        formData.append('is_active', document.getElementById('editRoleActive').checked ? '1' : '0');

        Swal.fire({
          title: 'Updating Role...',
          text: 'Please wait',
          allowOutsideClick: false,
          didOpen: () => Swal.showLoading()
        });

        fetch('../../../controller/admin/post/settings_post.php', {
          method: 'POST',
          body: formData
        })
          .then(response => response.json())
          .then(data => {
            Swal.close();
            if (data.success) {
              Swal.fire({ title: 'Success!', text: data.message, icon: 'success', confirmButtonColor: '#d97706' }).then(() => {
                location.reload();
              });
            } else {
              Swal.fire({ title: 'Error', text: data.message, icon: 'error', confirmButtonColor: '#d97706' });
            }
          });

        closeModal('editRoleModal');
      }

      function deleteRole(roleId, roleName) {
        Swal.fire({
          title: 'Delete Role?',
          text: `Are you sure you want to delete the role "${roleName}"? This action cannot be undone.`,
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#d97706',
          cancelButtonColor: '#6b7280',
          confirmButtonText: 'Yes, delete'
        }).then((result) => {
          if (result.isConfirmed) {
            Swal.fire({
              title: 'Deleting...',
              text: 'Please wait',
              allowOutsideClick: false,
              didOpen: () => Swal.showLoading()
            });

            const formData = new FormData();
            formData.append('action', 'delete_role');
            formData.append('role_id', roleId);

            fetch('../../../controller/admin/post/settings_post.php', {
              method: 'POST',
              body: formData
            })
              .then(response => response.json())
              .then(data => {
                Swal.close();
                if (data.success) {
                  Swal.fire({ title: 'Success!', text: data.message, icon: 'success', confirmButtonColor: '#d97706' }).then(() => {
                    location.reload();
                  });
                } else {
                  Swal.fire({ title: 'Error', text: data.message, icon: 'error', confirmButtonColor: '#d97706' });
                }
              });
          }
        });
      }

      function updateUserRole(userId, roleId) {
        const formData = new FormData();
        formData.append('action', 'update_user_role');
        formData.append('user_id', userId);
        formData.append('role_id', roleId);

        Swal.fire({
          title: 'Updating...',
          text: 'Please wait',
          allowOutsideClick: false,
          didOpen: () => Swal.showLoading()
        });

        fetch('../../../controller/admin/post/settings_post.php', {
          method: 'POST',
          body: formData
        })
          .then(response => response.json())
          .then(data => {
            Swal.close();
            if (data.success) {
              Swal.fire({ title: 'Success!', text: data.message, icon: 'success', confirmButtonColor: '#d97706' });
            } else {
              Swal.fire({ title: 'Error', text: data.message, icon: 'error', confirmButtonColor: '#d97706' });
            }
          });
      }

      function toggleUserStatus(userId, currentStatus) {
        const newStatus = currentStatus === 'active' ? 'suspended' : 'active';

        Swal.fire({
          title: `${currentStatus === 'active' ? 'Suspend' : 'Activate'} User?`,
          text: `Are you sure you want to ${currentStatus === 'active' ? 'suspend' : 'activate'} this user?`,
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#d97706',
          cancelButtonColor: '#6b7280',
          confirmButtonText: `Yes, ${currentStatus === 'active' ? 'suspend' : 'activate'}`
        }).then((result) => {
          if (result.isConfirmed) {
            Swal.fire({
              title: 'Updating...',
              text: 'Please wait',
              allowOutsideClick: false,
              didOpen: () => Swal.showLoading()
            });

            const formData = new FormData();
            formData.append('action', 'toggle_user_status');
            formData.append('user_id', userId);
            formData.append('status', newStatus);

            fetch('../../../controller/admin/post/settings_post.php', {
              method: 'POST',
              body: formData
            })
              .then(response => response.json())
              .then(data => {
                Swal.close();
                if (data.success) {
                  Swal.fire({ title: 'Success!', text: data.message, icon: 'success', confirmButtonColor: '#d97706' }).then(() => {
                    location.reload();
                  });
                } else {
                  Swal.fire({ title: 'Error', text: data.message, icon: 'error', confirmButtonColor: '#d97706' });
                }
              });
          }
        });
      }

      // ========== DANGER ZONE FUNCTIONS ==========
      function exportAllData() {
        Swal.fire({
          title: 'Exporting Data...',
          text: 'Please wait while we prepare your data export',
          allowOutsideClick: false,
          didOpen: () => Swal.showLoading()
        });

        const formData = new FormData();
        formData.append('action', 'export_data');

        fetch('../../../controller/admin/post/settings_post.php', {
          method: 'POST',
          body: formData
        })
          .then(response => response.json())
          .then(data => {
            Swal.close();
            if (data.success) {
              // Create download link
              const blob = new Blob([data.data], { type: 'application/json' });
              const url = URL.createObjectURL(blob);
              const a = document.createElement('a');
              a.href = url;
              a.download = data.filename;
              document.body.appendChild(a);
              a.click();
              document.body.removeChild(a);
              URL.revokeObjectURL(url);

              Swal.fire({ title: 'Success!', text: data.message, icon: 'success', confirmButtonColor: '#d97706' });
            } else {
              Swal.fire({ title: 'Error', text: data.message, icon: 'error', confirmButtonColor: '#d97706' });
            }
          });
      }

      function clearCache() {
        Swal.fire({
          title: 'Clear Cache?',
          text: 'This will clear all temporary files and cached data.',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#d97706',
          cancelButtonColor: '#6b7280',
          confirmButtonText: 'Yes, clear'
        }).then((result) => {
          if (result.isConfirmed) {
            Swal.fire({
              title: 'Clearing...',
              text: 'Please wait',
              allowOutsideClick: false,
              didOpen: () => Swal.showLoading()
            });

            const formData = new FormData();
            formData.append('action', 'clear_cache');

            fetch('../../../controller/admin/post/settings_post.php', {
              method: 'POST',
              body: formData
            })
              .then(response => response.json())
              .then(data => {
                Swal.close();
                if (data.success) {
                  Swal.fire({ title: 'Success!', text: data.message, icon: 'success', confirmButtonColor: '#d97706' });
                } else {
                  Swal.fire({ title: 'Error', text: data.message, icon: 'error', confirmButtonColor: '#d97706' });
                }
              });
          }
        });
      }

      function confirmDeleteData() {
        Swal.fire({
          title: '⚠️ DANGER ZONE ⚠️',
          html: `
                    <div class="text-left">
                        <p class="mb-2 text-rose-700 font-bold">This action will permanently delete ALL system data!</p>
                        <p class="mb-4">This includes:</p>
                        <ul class="list-disc list-inside mb-4 text-sm">
                            <li>All user accounts</li>
                            <li>All bookings and reservations</li>
                            <li>All inventory records</li>
                            <li>All menu items</li>
                            <li>All campaigns and promo codes</li>
                            <li>All system settings</li>
                        </ul>
                        <p class="mb-4">Type <strong class="text-rose-700">DELETE</strong> to confirm:</p>
                        <input type="text" id="deleteConfirmInput" class="w-full border border-rose-300 rounded-lg px-3 py-2" placeholder="DELETE">
                    </div>
                `,
          icon: 'error',
          showCancelButton: true,
          confirmButtonColor: '#ef4444',
          cancelButtonColor: '#6b7280',
          confirmButtonText: 'Delete All Data',
          preConfirm: () => {
            const input = document.getElementById('deleteConfirmInput').value;
            if (input !== 'DELETE') {
              Swal.showValidationMessage('Please type DELETE to confirm');
              return false;
            }
            return true;
          }
        }).then((result) => {
          if (result.isConfirmed) {
            Swal.fire({
              title: 'Processing...',
              text: 'Please wait',
              allowOutsideClick: false,
              didOpen: () => Swal.showLoading()
            });

            const formData = new FormData();
            formData.append('action', 'delete_data');
            formData.append('confirm', 'DELETE');

            fetch('../../../controller/admin/post/settings_post.php', {
              method: 'POST',
              body: formData
            })
              .then(response => response.json())
              .then(data => {
                Swal.close();
                if (data.success) {
                  Swal.fire({ title: 'Request Submitted', text: data.message, icon: 'warning', confirmButtonColor: '#d97706' });
                } else {
                  Swal.fire({ title: 'Error', text: data.message, icon: 'error', confirmButtonColor: '#d97706' });
                }
              });
          }
        });
      }

      // ========== INITIALIZATION ==========
      document.addEventListener('DOMContentLoaded', function () {
        showSettingsTab('general');
      });

      // Close modals when clicking outside
      window.addEventListener('click', function (event) {
        if (event.target.classList.contains('modal')) {
          event.target.classList.remove('show');
        }
      });

      // Close modals with Escape key
      document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
          document.querySelectorAll('.modal.show').forEach(modal => {
            modal.classList.remove('show');
          });
        }
      });

      // Notification bell
      document.getElementById('notificationBell').addEventListener('click', function () {
        window.location.href = '../notifications.php';
      });
    </script>
  </body>

</html>