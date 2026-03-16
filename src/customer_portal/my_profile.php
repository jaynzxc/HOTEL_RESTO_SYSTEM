<?php require_once '../../controller/customer/get/profile.php' ?>
<!DOCTYPE html>
<html lang="en">

  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile · Lùcas Customer Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
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

      .toast {
        animation: slideIn 0.3s ease-out;
      }

      .input-error {
        border-color: #ef4444 !important;
      }
    </style>
  </head>

  <body class="bg-slate-50 font-sans antialiased">
    <div class="min-h-screen flex flex-col lg:flex-row">
      <?php require './components/customer_nav.php' ?>

      <!-- Main Content -->
      <main class="flex-1 p-5 lg:p-8 overflow-y-auto">
        <!-- Header -->
        <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
          <div>
            <h1 class="text-2xl lg:text-3xl font-light text-slate-800">My Profile</h1>
            <p class="text-sm text-slate-500 mt-0.5">manage your personal information and preferences</p>
          </div>
          <div
            class="bg-white border border-slate-200 rounded-full px-4 py-2 text-sm flex items-center gap-2 shadow-sm">
            <i class="fa-regular fa-calendar text-slate-400"></i>
            <span id="currentDateTime"></span>
          </div>
        </div>

        <!-- Display Messages from Session -->
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

        <!-- Profile Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <!-- Left Column -->
          <div class="lg:col-span-1 space-y-5">
            <!-- Avatar Card -->
            <div class="bg-white rounded-2xl border border-slate-200 p-6 text-center">
              <div class="relative inline-block">
                <div
                  class="h-28 w-28 rounded-full bg-amber-200 mx-auto flex items-center justify-center text-amber-800 font-bold text-4xl border-4 border-white shadow-md overflow-hidden"
                  id="userInitials">
                  <?php if (!empty($user['avatar'])): ?>
                    <img src="../../../<?php echo htmlspecialchars($user['avatar']); ?>" alt="Avatar"
                      class="w-full h-full object-cover">
                  <?php else: ?>
                    <?php echo htmlspecialchars($initials); ?>
                  <?php endif; ?>
                </div>
                <form action="  " method="POST" enctype="multipart/form-data" id="avatarForm">
                  <input type="hidden" name="action" value="upload_avatar">
                  <input type="file" name="avatar" id="avatarInput" accept="image/*" class="hidden"
                    onchange="document.getElementById('avatarForm').submit()">
                  <button type="button" onclick="document.getElementById('avatarInput').click()"
                    class="absolute bottom-1 right-1 bg-white rounded-full p-2 shadow-md border border-slate-200 hover:bg-slate-50 transition"
                    title="change photo">
                    <i class="fa-solid fa-camera text-slate-600 text-sm"></i>
                  </button>
                </form>
              </div>
              <h2 class="font-semibold text-xl mt-3 user-name">
                <?php echo htmlspecialchars($user['full_name'] ?? 'Guest'); ?>
              </h2>
              <p class="text-sm text-slate-500">member since
                <?php echo strtolower($user['member_since'] ?? date('F Y')); ?>
              </p>
              <div class="flex justify-center gap-2 mt-3">
                <span class="bg-amber-100 text-amber-800 text-xs px-3 py-1 rounded-full flex items-center gap-1">
                  <i class="fa-regular fa-gem"></i> <span
                    class="user-membership"><?php echo $user['member_tier']; ?></span> tier
                </span>
                <span class="bg-slate-100 text-slate-700 text-xs px-3 py-1 rounded-full">
                  <span class="user-points"><?php echo number_format($user['loyalty_points']); ?></span> pts
                </span>
              </div>
              <div class="border-t border-slate-100 mt-5 pt-4 text-left">
                <p class="text-xs text-slate-400 flex items-center gap-2">
                  <i
                    class="fa-regular fa-circle-check <?php echo ($user['email_verified'] ?? 0) ? 'text-green-600' : 'text-slate-300'; ?> w-4"></i>
                  email <?php echo ($user['email_verified'] ?? 0) ? 'verified' : 'unverified'; ?>
                </p>
                <p class="text-xs text-slate-400 flex items-center gap-2 mt-2">
                  <i
                    class="fa-regular fa-circle-check <?php echo ($user['phone_verified'] ?? 0) ? 'text-green-600' : 'text-slate-300'; ?> w-4"></i>
                  phone <?php echo ($user['phone_verified'] ?? 0) ? 'verified' : 'unverified'; ?>
                </p>
                <p class="text-xs text-slate-400 flex items-center gap-2 mt-2">
                  <i class="fa-regular fa-id-card w-4"></i>
                  <?php echo ucfirst($user['role'] ?? 'customer'); ?> account
                </p>
              </div>
            </div>

            <!-- Loyalty Progress -->
            <div class="bg-gradient-to-br from-amber-50 to-amber-100/50 rounded-2xl border border-amber-200 p-5">
              <div class="flex items-center gap-3">
                <i class="fa-regular fa-star text-2xl text-amber-600"></i>
                <div class="flex-1">
                  <p class="font-medium text-sm">loyalty progress</p>
                  <?php if ($next_tier_name !== 'platinum (max)'): ?>
                    <p class="text-xs text-slate-600">
                      <span class="points-to-next"><?php echo number_format($points_to_next); ?></span> more pts to reach
                      <?php echo $next_tier_name; ?>
                    </p>
                  <?php else: ?>
                    <p class="text-xs text-slate-600">You've reached the highest tier!</p>
                  <?php endif; ?>
                  <div class="w-full bg-amber-200 h-1.5 rounded-full mt-2">
                    <div class="bg-amber-600 h-1.5 rounded-full" id="pointsProgress"
                      style="width: <?php echo $progress_percentage; ?>%"></div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Account Actions -->
            <div class="bg-white rounded-2xl border border-slate-200 p-5">
              <h3 class="font-semibold text-sm flex items-center gap-2"><i
                  class="fa-regular fa-trash-can text-rose-400"></i> account actions</h3>
              <button onclick="deactivateAccount()"
                class="w-full text-left text-sm text-rose-600 hover:bg-rose-50 p-2 rounded-lg mt-2 transition">deactivate
                account</button>
              <button onclick="downloadData()"
                class="w-full text-left text-sm text-slate-600 hover:bg-slate-50 p-2 rounded-lg transition">download my
                data</button>
            </div>
          </div>

          <!-- Right Column - Forms -->
          <div class="lg:col-span-2 space-y-6">
            <!-- Personal Information Form -->
            <form action="../../controller/customer/update/update_profile.php" method="POST"
              class="bg-white rounded-2xl border border-slate-200 p-6">
              <input type="hidden" name="action" value="update_profile">

              <h2 class="font-semibold text-lg flex items-center gap-2 border-b pb-3">
                <i class="fa-regular fa-user text-amber-600"></i> personal information
              </h2>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mt-5">
                <div>
                  <label class="block text-xs text-slate-500 mb-1">first name <span
                      class="text-red-400">*</span></label>
                  <input type="text" name="first_name" id="firstName"
                    value="<?php echo htmlspecialchars($_SESSION['form_data']['first_name'] ?? $user['first_name'] ?? ''); ?>"
                    class="w-full border <?php echo isset($_SESSION['error']['first_name']) ? 'border-red-500' : 'border-slate-200'; ?> rounded-xl p-3 text-sm focus:ring-2 focus:ring-amber-500 outline-none"
                    required>
                </div>

                <div>
                  <label class="block text-xs text-slate-500 mb-1">last name <span class="text-red-400">*</span></label>
                  <input type="text" name="last_name" id="lastName"
                    value="<?php echo htmlspecialchars($_SESSION['form_data']['last_name'] ?? $user['last_name'] ?? ''); ?>"
                    class="w-full border <?php echo isset($_SESSION['error']['last_name']) ? 'border-red-500' : 'border-slate-200'; ?> rounded-xl p-3 text-sm focus:ring-2 focus:ring-amber-500 outline-none"
                    required>
                </div>

                <div>
                  <label class="block text-xs text-slate-500 mb-1">date of birth</label>
                  <input type="date" name="date_of_birth" id="dob"
                    value="<?php echo htmlspecialchars($_SESSION['form_data']['date_of_birth'] ?? $user['date_of_birth'] ?? ''); ?>"
                    class="w-full border border-slate-200 rounded-xl p-3 text-sm text-slate-600 focus:ring-2 focus:ring-amber-500 outline-none">
                </div>

                <div>
                  <label class="block text-xs text-slate-500 mb-1">gender</label>
                  <select name="gender" id="gender"
                    class="w-full border border-slate-200 rounded-xl p-3 text-sm bg-white focus:ring-2 focus:ring-amber-500 outline-none">
                    <option value="female" <?php echo (($_SESSION['form_data']['gender'] ?? $user['gender'] ?? '') == 'female') ? 'selected' : ''; ?>>female</option>
                    <option value="male" <?php echo (($_SESSION['form_data']['gender'] ?? $user['gender'] ?? '') == 'male') ? 'selected' : ''; ?>>male</option>
                    <option value="prefer not to say" <?php echo (($_SESSION['form_data']['gender'] ?? $user['gender'] ?? '') == 'prefer not to say') ? 'selected' : ''; ?>>prefer not to say</option>
                  </select>
                </div>

                <div>
                  <label class="block text-xs text-slate-500 mb-1">nationality</label>
                  <input type="text" name="nationality" id="nationality"
                    value="<?php echo htmlspecialchars($_SESSION['form_data']['nationality'] ?? $user['nationality'] ?? ''); ?>"
                    class="w-full border border-slate-200 rounded-xl p-3 text-sm focus:ring-2 focus:ring-amber-500 outline-none">
                </div>
              </div>

              <!-- Contact Details -->
              <div class="bg-white rounded-2xl border border-slate-200 p-6 mt-6">
                <h2 class="font-semibold text-lg flex items-center gap-2 border-b pb-3">
                  <i class="fa-regular fa-address-book text-amber-600"></i> contact details
                </h2>

                <div class="space-y-4 mt-5">
                  <div>
                    <label class="block text-xs text-slate-500 mb-1">email address <span
                        class="text-red-400">*</span></label>
                    <div class="flex gap-2 items-center">
                      <input type="email" name="email" id="email"
                        value="<?php echo htmlspecialchars($_SESSION['form_data']['email'] ?? $user['email'] ?? ''); ?>"
                        class="flex-1 border <?php echo isset($_SESSION['error']['email']) ? 'border-red-500' : 'border-slate-200'; ?> rounded-xl p-3 text-sm focus:ring-2 focus:ring-amber-500 outline-none"
                        required>
                      <?php if ($user['email_verified'] ?? 0): ?>
                        <span class="bg-green-100 text-green-700 text-xs px-2 py-1 rounded-full">verified</span>
                      <?php else: ?>
                        <span class="bg-yellow-100 text-yellow-700 text-xs px-2 py-1 rounded-full">unverified</span>
                      <?php endif; ?>
                    </div>
                  </div>

                  <div>
                    <label class="block text-xs text-slate-500 mb-1">mobile number <span
                        class="text-red-400">*</span></label>
                    <div class="flex gap-2 items-center">
                      <input type="tel" name="phone" id="phone"
                        value="<?php echo htmlspecialchars($_SESSION['form_data']['phone'] ?? $user['phone'] ?? ''); ?>"
                        class="flex-1 border <?php echo isset($_SESSION['error']['phone']) ? 'border-red-500' : 'border-slate-200'; ?> rounded-xl p-3 text-sm focus:ring-2 focus:ring-amber-500 outline-none"
                        required>
                      <?php if ($user['phone_verified'] ?? 0): ?>
                        <span class="bg-green-100 text-green-700 text-xs px-2 py-1 rounded-full">verified</span>
                      <?php else: ?>
                        <span class="bg-yellow-100 text-yellow-700 text-xs px-2 py-1 rounded-full">unverified</span>
                      <?php endif; ?>
                    </div>
                  </div>

                  <div>
                    <label class="block text-xs text-slate-500 mb-1">alternative phone (optional)</label>
                    <input type="tel" name="alternative_phone" id="altPhone"
                      value="<?php echo htmlspecialchars($_SESSION['form_data']['alternative_phone'] ?? $user['alternative_phone'] ?? ''); ?>"
                      placeholder="+63 ..."
                      class="w-full border border-slate-200 rounded-xl p-3 text-sm focus:ring-2 focus:ring-amber-500 outline-none">
                  </div>
                </div>
              </div>

              <!-- Address & Preferences -->
              <div class="bg-white rounded-2xl border border-slate-200 p-6">
                <h2 class="font-semibold text-lg flex items-center gap-2 border-b pb-3">
                  <i class="fa-regular fa-map text-amber-600"></i> address & preferences
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mt-5">
                  <div class="md:col-span-2">
                    <label class="block text-xs text-slate-500 mb-1">street address</label>
                    <input type="text" name="address" id="address"
                      value="<?php echo htmlspecialchars($_SESSION['form_data']['address'] ?? $user['address'] ?? ''); ?>"
                      class="w-full border border-slate-200 rounded-xl p-3 text-sm focus:ring-2 focus:ring-amber-500 outline-none">
                  </div>

                  <div>
                    <label class="block text-xs text-slate-500 mb-1">city</label>
                    <input type="text" name="city" id="city"
                      value="<?php echo htmlspecialchars($_SESSION['form_data']['city'] ?? $user['city'] ?? ''); ?>"
                      class="w-full border border-slate-200 rounded-xl p-3 text-sm focus:ring-2 focus:ring-amber-500 outline-none">
                  </div>

                  <div>
                    <label class="block text-xs text-slate-500 mb-1">postal code</label>
                    <input type="text" name="postal_code" id="postalCode"
                      value="<?php echo htmlspecialchars($_SESSION['form_data']['postal_code'] ?? $user['postal_code'] ?? ''); ?>"
                      class="w-full border border-slate-200 rounded-xl p-3 text-sm focus:ring-2 focus:ring-amber-500 outline-none">
                  </div>

                  <div>
                    <label class="block text-xs text-slate-500 mb-1">country</label>
                    <input type="text" name="country" id="country"
                      value="<?php echo htmlspecialchars($_SESSION['form_data']['country'] ?? $user['country'] ?? 'Philippines'); ?>"
                      class="w-full border border-slate-200 rounded-xl p-3 text-sm focus:ring-2 focus:ring-amber-500 outline-none">
                  </div>

                  <div>
                    <label class="block text-xs text-slate-500 mb-1">preferred language</label>
                    <select name="preferred_language" id="language"
                      class="w-full border border-slate-200 rounded-xl p-3 text-sm bg-white focus:ring-2 focus:ring-amber-500 outline-none">
                      <option value="English" <?php echo (($_SESSION['form_data']['preferred_language'] ?? $user['preferred_language'] ?? 'English') == 'English') ? 'selected' : ''; ?>>English</option>
                      <option value="Filipino" <?php echo (($_SESSION['form_data']['preferred_language'] ?? $user['preferred_language'] ?? '') == 'Filipino') ? 'selected' : ''; ?>>Filipino</option>
                      <option value="Japanese" <?php echo (($_SESSION['form_data']['preferred_language'] ?? $user['preferred_language'] ?? '') == 'Japanese') ? 'selected' : ''; ?>>Japanese</option>
                    </select>
                  </div>
                </div>
              </div>

              <!-- Notification Preferences -->
              <div class="bg-white rounded-2xl border border-slate-200 p-6">
                <h2 class="font-semibold text-lg flex items-center gap-2 border-b pb-3">
                  <i class="fa-regular fa-bell text-amber-600"></i> notification preferences
                </h2>
                <div class="space-y-3 mt-4">
                  <label class="flex items-center gap-3 text-sm cursor-pointer">
                    <input type="checkbox" name="notify_email" id="emailNotifications" <?php echo (($_SESSION['form_data']['notify_email'] ?? $user['notify_email'] ?? 1) ? 'checked' : ''); ?>
                      class="rounded text-amber-600 focus:ring-amber-500">
                    email about booking confirmations
                  </label>
                  <label class="flex items-center gap-3 text-sm cursor-pointer">
                    <input type="checkbox" name="notify_sms" id="smsNotifications" <?php echo (($_SESSION['form_data']['notify_sms'] ?? $user['notify_sms'] ?? 1) ? 'checked' : ''); ?>
                      class="rounded text-amber-600 focus:ring-amber-500">
                    SMS for reservation reminders
                  </label>
                  <label class="flex items-center gap-3 text-sm cursor-pointer">
                    <input type="checkbox" name="notify_promo" id="promoNotifications" <?php echo (($_SESSION['form_data']['notify_promo'] ?? $user['notify_promo'] ?? 0) ? 'checked' : ''); ?>
                      class="rounded text-amber-600 focus:ring-amber-500">
                    promotional offers & newsletters
                  </label>
                  <label class="flex items-center gap-3 text-sm cursor-pointer">
                    <input type="checkbox" name="notify_loyalty" id="loyaltyNotifications" <?php echo (($_SESSION['form_data']['notify_loyalty'] ?? $user['notify_loyalty'] ?? 1) ? 'checked' : ''); ?>
                      class="rounded text-amber-600 focus:ring-amber-500">
                    loyalty reward updates
                  </label>
                </div>
              </div>

              <!-- Save Buttons -->
              <div class="flex flex-wrap items-center gap-4 pt-4">
                <button type="submit"
                  class="bg-amber-600 hover:bg-amber-700 text-white px-8 py-3 rounded-xl font-medium shadow-sm transition">save
                  changes</button>
                <button type="button" onclick="resetForm()"
                  class="border border-slate-300 bg-white hover:bg-slate-50 px-8 py-3 rounded-xl font-medium text-slate-700 transition">reset</button>
              </div>
            </form>

            <!-- Change Password Form (Separate Form) -->
            <form action="../../controller/customer/update/update_profile.php" method="POST"
              class="bg-white rounded-2xl border border-slate-200 p-6">
              <input type="hidden" name="action" value="change_password">

              <h2 class="font-semibold text-lg flex items-center gap-2 border-b pb-3">
                <i class="fa-solid fa-lock text-amber-600"></i> change password
              </h2>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mt-5">
                <div class="md:col-span-2">
                  <label class="block text-xs text-slate-500 mb-1">current password</label>
                  <input type="password" name="current_password" id="currentPassword"
                    class="w-full border <?php echo isset($_SESSION['error']['current_password']) ? 'border-red-500' : 'border-slate-200'; ?> rounded-xl p-3 text-sm focus:ring-2 focus:ring-amber-500 outline-none">
                </div>
                <div>
                  <label class="block text-xs text-slate-500 mb-1">new password</label>
                  <input type="password" name="new_password" id="newPassword"
                    class="w-full border <?php echo isset($_SESSION['error']['new_password']) ? 'border-red-500' : 'border-slate-200'; ?> rounded-xl p-3 text-sm focus:ring-2 focus:ring-amber-500 outline-none">
                </div>
                <div>
                  <label class="block text-xs text-slate-500 mb-1">confirm new password</label>
                  <input type="password" name="confirm_password" id="confirmPassword"
                    class="w-full border <?php echo isset($_SESSION['error']['confirm_password']) ? 'border-red-500' : 'border-slate-200'; ?> rounded-xl p-3 text-sm focus:ring-2 focus:ring-amber-500 outline-none">
                </div>
              </div>
              <p class="text-xs text-slate-400 mt-2">minimum 8 characters, with uppercase & number</p>

              <div class="mt-4">
                <button type="submit"
                  class="bg-amber-600 hover:bg-amber-700 text-white px-6 py-2 rounded-xl font-medium shadow-sm transition text-sm">update
                  password</button>
              </div>
            </form>
          </div>
        </div>
      </main>
    </div>

    <!-- Toast Container -->
    <div id="toastContainer" class="fixed top-4 right-4 z-50 space-y-2"></div>

    <script>
      // ==================== GLOBAL STATE ====================
      const App = {
        user: {
          name: '<?php echo addslashes($user['full_name'] ?? ''); ?>',
          firstName: '<?php echo addslashes($user['first_name'] ?? ''); ?>',
          lastName: '<?php echo addslashes($user['last_name'] ?? ''); ?>',
          initials: '<?php echo addslashes($initials); ?>',
          email: '<?php echo addslashes($user['email'] ?? ''); ?>',
          phone: '<?php echo addslashes($user['phone'] ?? ''); ?>',
          membership: '<?php echo $user['member_tier'] ?? 'bronze'; ?>',
          points: <?php echo $user['loyalty_points'] ?? 0; ?>,
          dob: '<?php echo $user['date_of_birth'] ?? ''; ?>',
          gender: '<?php echo $user['gender'] ?? 'female'; ?>',
          nationality: '<?php echo addslashes($user['nationality'] ?? ''); ?>',
          altPhone: '<?php echo addslashes($user['alternative_phone'] ?? ''); ?>',
          address: '<?php echo addslashes($user['address'] ?? ''); ?>',
          city: '<?php echo addslashes($user['city'] ?? ''); ?>',
          postalCode: '<?php echo addslashes($user['postal_code'] ?? ''); ?>',
          country: '<?php echo addslashes($user['country'] ?? 'Philippines'); ?>',
          language: '<?php echo addslashes($user['preferred_language'] ?? 'English'); ?>'
        },

        notifications: [
          { id: 1, read: false },
          { id: 2, read: false },
          { id: 3, read: false }
        ],

        init() {
          this.updateDateTime();
          setInterval(() => this.updateDateTime(), 60000);
        },

        updateDateTime() {
          const now = new Date();
          const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
          const dateStr = now.toLocaleDateString('en-US', options);
          const dateElement = document.getElementById('currentDateTime');
          if (dateElement) dateElement.textContent = dateStr;
        },

        showToast(message, type = 'info', duration = 3000) {
          const container = document.getElementById('toastContainer');
          const toast = document.createElement('div');

          const colors = {
            success: 'bg-green-500',
            error: 'bg-red-500',
            info: 'bg-amber-500',
            warning: 'bg-orange-500'
          };

          toast.className = `toast ${colors[type]} text-white px-6 py-3 rounded-xl shadow-lg flex items-center gap-3`;
          toast.innerHTML = `
                    <i class="fa-regular ${type === 'success' ? 'fa-circle-check' : type === 'error' ? 'fa-circle-exclamation' : 'fa-bell'}"></i>
                    <span class="text-sm font-medium">${message}</span>
                `;

          container.appendChild(toast);

          setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(100%)';
            setTimeout(() => toast.remove(), 300);
          }, duration);
        },

        validateEmail(email) {
          return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
        },

        validatePhone(phone) {
          return /^[\+]?[(]?[0-9]{3}[)]?[-\s\.]?[0-9]{3}[-\s\.]?[0-9]{4,6}$/.test(phone);
        }
      };

      // ==================== PROFILE FUNCTIONS ====================
      function resetForm() {
        if (confirm('Reset all changes?')) {
          document.getElementById('firstName').value = App.user.firstName;
          document.getElementById('lastName').value = App.user.lastName;
          document.getElementById('email').value = App.user.email;
          document.getElementById('phone').value = App.user.phone;
          document.getElementById('dob').value = App.user.dob;
          document.getElementById('gender').value = App.user.gender;
          document.getElementById('nationality').value = App.user.nationality;
          document.getElementById('altPhone').value = App.user.altPhone;
          document.getElementById('address').value = App.user.address;
          document.getElementById('city').value = App.user.city;
          document.getElementById('postalCode').value = App.user.postalCode;
          document.getElementById('country').value = App.user.country;
          document.getElementById('language').value = App.user.language;

          App.showToast('Form reset', 'info');
        }
      }

      function deactivateAccount() {
        if (confirm('Are you absolutely sure? This action cannot be undone.')) {
          if (confirm('All your data will be permanently deleted. Continue?')) {
            App.showToast('Account deactivated', 'info');
            setTimeout(() => window.location.href = '../../controller/auth/logout.php', 1500);
          }
        }
      }

      function downloadData() {
        const data = {
          user: App.user,
          timestamp: new Date().toISOString()
        };

        const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `lucas-account-data-${new Date().toISOString().split('T')[0]}.json`;
        a.click();
        URL.revokeObjectURL(url);

        App.showToast('Data download started', 'success');
      }

      function setupEventListeners() {
        // Real-time validation
        document.getElementById('email')?.addEventListener('blur', function () {
          if (this.value && !App.validateEmail(this.value)) {
            this.classList.add('border-red-500');
          } else {
            this.classList.remove('border-red-500');
          }
        });

        document.getElementById('phone')?.addEventListener('blur', function () {
          if (this.value && !App.validatePhone(this.value)) {
            this.classList.add('border-red-500');
          } else {
            this.classList.remove('border-red-500');
          }
        });
      }

      // Initialize
      document.addEventListener('DOMContentLoaded', () => {
        App.init();
        setupEventListeners();
      });
    </script>
  </body>

</html>