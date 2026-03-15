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
      from { transform: translateX(100%); opacity: 0; }
      to { transform: translateX(0); opacity: 1; }
    }
    .toast { animation: slideIn 0.3s ease-out; }
    .input-error { border-color: #ef4444 !important; }
  </style>
</head>
<body class="bg-slate-50 font-sans antialiased">
  <div class="min-h-screen flex flex-col lg:flex-row">
    <!-- Sidebar -->
    <aside class="lg:w-80 bg-white border-r border-slate-200 shadow-sm shrink-0">
      <div class="px-6 py-7 border-b border-slate-100">
        <div class="flex items-center gap-2 text-amber-700">
          <i class="fa-solid fa-utensils text-xl"></i>
          <i class="fa-solid fa-bed text-xl"></i>
          <span class="font-semibold text-xl tracking-tight text-slate-800">Lùcas<span class="text-amber-600">.stay</span></span>
        </div>
        <p class="text-xs text-slate-500 mt-1">customer portal · my profile</p>
      </div>
      <div class="flex items-center gap-3 px-6 py-5 border-b border-slate-100 bg-slate-50/80" id="userSummary">
        <div class="h-12 w-12 rounded-full bg-amber-200 flex items-center justify-center text-amber-800 font-bold text-lg">MC</div>
        <div>
          <p class="font-medium text-slate-800 user-name">Mia Cruz</p>
          <p class="text-xs text-slate-500 flex items-center gap-1"><i class="fa-regular fa-gem text-[11px]"></i> gold member · <span class="user-points">1,240</span> pts</p>
        </div>
      </div>

     <nav class="p-4 space-y-1.5 text-sm">
      <a href="./index.html" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-700 hover:bg-amber-50 transition"><i class="fa-solid fa-table-cells-large w-5 text-slate-400"></i>Dashboard</a>
      <a href="./my_profile.html" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-700 hover:bg-amber-50 transition"><i class="fa-regular fa-user w-5 text-slate-400"></i>My Profile</a>
      <a href="./hotel_booking.html" class="flex items-center gap-3 px-4 py-2.5 rounded-xl bg-amber-50 text-amber-800 font-medium"><i class="fa-solid fa-hotel w-5 text-amber-600"></i>Hotel Booking</a>
      <a href="./my_reservation.html" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-700 hover:bg-amber-50 transition"><i class="fa-regular fa-calendar-check w-5 text-slate-400"></i>My Reservations</a>
      <a href="./restaurant_reservation.html" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-700 hover:bg-amber-50 transition"><i class="fa-regular fa-clock w-5 text-slate-400"></i>Restaurant Reservation</a>
      <a href="./order_food.html" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-700 hover:bg-amber-50 transition"><i class="fa-solid fa-bag-shopping w-5 text-slate-400"></i>Menu / Order Food</a>
      <a href="./payments.html" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-700 hover:bg-amber-50 transition"><i class="fa-regular fa-credit-card w-5 text-slate-400"></i>Payments</a>
      <a href="./loyalty_rewards.html" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-700 hover:bg-amber-50 transition"><i class="fa-regular fa-star w-5 text-slate-400"></i>Loyalty Rewards</a>
      <a href="./Notifications.html" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-700 hover:bg-amber-50 transition relative"><i class="fa-regular fa-bell w-5 text-slate-400"></i>Notifications<span class="ml-auto bg-amber-100 text-amber-800 text-xs px-1.5 py-0.5 rounded-full">3</span></a>
      <div class="border-t border-slate-200 pt-3 mt-3">
        <a href="./login_form.html" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-500 hover:bg-red-50 hover:text-red-700 transition"><i class="fa-solid fa-arrow-right-from-bracket w-5"></i>Logout</a>
      </div>
     </nav>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 p-5 lg:p-8 overflow-y-auto">
      <!-- Header -->
      <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
          <h1 class="text-2xl lg:text-3xl font-light text-slate-800">My Profile</h1>
          <p class="text-sm text-slate-500 mt-0.5">manage your personal information and preferences</p>
        </div>
        <div class="bg-white border border-slate-200 rounded-full px-4 py-2 text-sm flex items-center gap-2 shadow-sm">
          <i class="fa-regular fa-calendar text-slate-400"></i> <span id="currentDateTime"></span>
        </div>
      </div>

      <!-- Profile Grid -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column -->
        <div class="lg:col-span-1 space-y-5">
          <!-- Avatar Card -->
          <div class="bg-white rounded-2xl border border-slate-200 p-6 text-center">
            <div class="relative inline-block">
              <div class="h-28 w-28 rounded-full bg-amber-200 mx-auto flex items-center justify-center text-amber-800 font-bold text-4xl border-4 border-white shadow-md" id="userInitials">MC</div>
              <button onclick="changePhoto()" class="absolute bottom-1 right-1 bg-white rounded-full p-2 shadow-md border border-slate-200 hover:bg-slate-50 transition" title="change photo">
                <i class="fa-solid fa-camera text-slate-600 text-sm"></i>
              </button>
            </div>
            <h2 class="font-semibold text-xl mt-3 user-name">Mia Cruz</h2>
            <p class="text-sm text-slate-500">member since march 2024</p>
            <div class="flex justify-center gap-2 mt-3">
              <span class="bg-amber-100 text-amber-800 text-xs px-3 py-1 rounded-full flex items-center gap-1"><i class="fa-regular fa-gem"></i> <span class="user-membership">gold</span> tier</span>
              <span class="bg-slate-100 text-slate-700 text-xs px-3 py-1 rounded-full"><span class="user-points">1,240</span> pts</span>
            </div>
            <div class="border-t border-slate-100 mt-5 pt-4 text-left">
              <p class="text-xs text-slate-400 flex items-center gap-2"><i class="fa-regular fa-circle-check text-green-600 w-4"></i> email verified</p>
              <p class="text-xs text-slate-400 flex items-center gap-2 mt-2"><i class="fa-regular fa-circle-check text-green-600 w-4"></i> phone verified</p>
              <p class="text-xs text-slate-400 flex items-center gap-2 mt-2"><i class="fa-regular fa-id-card w-4"></i> preferred guest</p>
            </div>
          </div>

          <!-- Loyalty Progress -->
          <div class="bg-gradient-to-br from-amber-50 to-amber-100/50 rounded-2xl border border-amber-200 p-5">
            <div class="flex items-center gap-3">
              <i class="fa-regular fa-star text-2xl text-amber-600"></i>
              <div class="flex-1">
                <p class="font-medium text-sm">loyalty progress</p>
                <p class="text-xs text-slate-600"><span class="points-to-next">760</span> more pts to reach platinum</p>
                <div class="w-full bg-amber-200 h-1.5 rounded-full mt-2">
                  <div class="bg-amber-600 h-1.5 rounded-full" id="pointsProgress" style="width: 62%"></div>
                </div>
              </div>
            </div>
          </div>

          <!-- Account Actions -->
          <div class="bg-white rounded-2xl border border-slate-200 p-5">
            <h3 class="font-semibold text-sm flex items-center gap-2"><i class="fa-regular fa-trash-can text-rose-400"></i> account actions</h3>
            <button onclick="deactivateAccount()" class="w-full text-left text-sm text-rose-600 hover:bg-rose-50 p-2 rounded-lg mt-2 transition">deactivate account</button>
            <button onclick="downloadData()" class="w-full text-left text-sm text-slate-600 hover:bg-slate-50 p-2 rounded-lg transition">download my data</button>
          </div>
        </div>

        <!-- Right Column - Forms -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Personal Information -->
          <div class="bg-white rounded-2xl border border-slate-200 p-6">
            <h2 class="font-semibold text-lg flex items-center gap-2 border-b pb-3"><i class="fa-regular fa-user text-amber-600"></i> personal information</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mt-5">
              <div>
                <label class="block text-xs text-slate-500 mb-1">first name <span class="text-red-400">*</span></label>
                <input type="text" id="firstName" value="Mia" class="w-full border border-slate-200 rounded-xl p-3 text-sm focus:ring-2 focus:ring-amber-500 outline-none" required>
              </div>
              <div>
                <label class="block text-xs text-slate-500 mb-1">last name <span class="text-red-400">*</span></label>
                <input type="text" id="lastName" value="Cruz" class="w-full border border-slate-200 rounded-xl p-3 text-sm focus:ring-2 focus:ring-amber-500 outline-none" required>
              </div>
              <div>
                <label class="block text-xs text-slate-500 mb-1">date of birth</label>
                <input type="date" id="dob" value="1994-05-12" class="w-full border border-slate-200 rounded-xl p-3 text-sm text-slate-600 focus:ring-2 focus:ring-amber-500 outline-none">
              </div>
              <div>
                <label class="block text-xs text-slate-500 mb-1">gender</label>
                <select id="gender" class="w-full border border-slate-200 rounded-xl p-3 text-sm bg-white focus:ring-2 focus:ring-amber-500 outline-none">
                  <option selected>female</option>
                  <option>male</option>
                  <option>prefer not to say</option>
                </select>
              </div>
              <div>
                <label class="block text-xs text-slate-500 mb-1">nationality</label>
                <input type="text" id="nationality" value="Filipino" class="w-full border border-slate-200 rounded-xl p-3 text-sm focus:ring-2 focus:ring-amber-500 outline-none">
              </div>
            </div>
          </div>

          <!-- Contact Details -->
          <div class="bg-white rounded-2xl border border-slate-200 p-6">
            <h2 class="font-semibold text-lg flex items-center gap-2 border-b pb-3"><i class="fa-regular fa-address-book text-amber-600"></i> contact details</h2>
            <div class="space-y-4 mt-5">
              <div>
                <label class="block text-xs text-slate-500 mb-1">email address <span class="text-red-400">*</span></label>
                <div class="flex gap-2 items-center">
                  <input type="email" id="email" value="mia.cruz@email.com" class="flex-1 border border-slate-200 rounded-xl p-3 text-sm focus:ring-2 focus:ring-amber-500 outline-none" required>
                  <span class="bg-green-100 text-green-700 text-xs px-2 py-1 rounded-full">verified</span>
                </div>
              </div>
              <div>
                <label class="block text-xs text-slate-500 mb-1">mobile number <span class="text-red-400">*</span></label>
                <div class="flex gap-2 items-center">
                  <input type="tel" id="phone" value="+63 917 555 1234" class="flex-1 border border-slate-200 rounded-xl p-3 text-sm focus:ring-2 focus:ring-amber-500 outline-none" required>
                  <span class="bg-green-100 text-green-700 text-xs px-2 py-1 rounded-full">verified</span>
                </div>
              </div>
              <div>
                <label class="block text-xs text-slate-500 mb-1">alternative phone (optional)</label>
                <input type="tel" id="altPhone" placeholder="+63 ..." class="w-full border border-slate-200 rounded-xl p-3 text-sm focus:ring-2 focus:ring-amber-500 outline-none">
              </div>
            </div>
          </div>

          <!-- Address & Preferences -->
          <div class="bg-white rounded-2xl border border-slate-200 p-6">
            <h2 class="font-semibold text-lg flex items-center gap-2 border-b pb-3"><i class="fa-regular fa-map text-amber-600"></i> address & preferences</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mt-5">
              <div class="md:col-span-2">
                <label class="block text-xs text-slate-500 mb-1">street address</label>
                <input type="text" id="address" value="15 B. Gonzales St., Barangay San Antonio" class="w-full border border-slate-200 rounded-xl p-3 text-sm focus:ring-2 focus:ring-amber-500 outline-none">
              </div>
              <div>
                <label class="block text-xs text-slate-500 mb-1">city</label>
                <input type="text" id="city" value="Makati" class="w-full border border-slate-200 rounded-xl p-3 text-sm focus:ring-2 focus:ring-amber-500 outline-none">
              </div>
              <div>
                <label class="block text-xs text-slate-500 mb-1">postal code</label>
                <input type="text" id="postalCode" value="1203" class="w-full border border-slate-200 rounded-xl p-3 text-sm focus:ring-2 focus:ring-amber-500 outline-none">
              </div>
              <div>
                <label class="block text-xs text-slate-500 mb-1">country</label>
                <input type="text" id="country" value="Philippines" class="w-full border border-slate-200 rounded-xl p-3 text-sm focus:ring-2 focus:ring-amber-500 outline-none">
              </div>
              <div>
                <label class="block text-xs text-slate-500 mb-1">preferred language</label>
                <select id="language" class="w-full border border-slate-200 rounded-xl p-3 text-sm bg-white focus:ring-2 focus:ring-amber-500 outline-none">
                  <option selected>English</option>
                  <option>Filipino</option>
                  <option>Japanese</option>
                </select>
              </div>
            </div>
          </div>

          <!-- Change Password -->
          <div class="bg-white rounded-2xl border border-slate-200 p-6">
            <h2 class="font-semibold text-lg flex items-center gap-2 border-b pb-3"><i class="fa-solid fa-lock text-amber-600"></i> change password</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mt-5">
              <div class="md:col-span-2">
                <label class="block text-xs text-slate-500 mb-1">current password</label>
                <input type="password" id="currentPassword" value="••••••••" class="w-full border border-slate-200 rounded-xl p-3 text-sm focus:ring-2 focus:ring-amber-500 outline-none">
              </div>
              <div>
                <label class="block text-xs text-slate-500 mb-1">new password</label>
                <input type="password" id="newPassword" class="w-full border border-slate-200 rounded-xl p-3 text-sm focus:ring-2 focus:ring-amber-500 outline-none">
              </div>
              <div>
                <label class="block text-xs text-slate-500 mb-1">confirm new password</label>
                <input type="password" id="confirmPassword" class="w-full border border-slate-200 rounded-xl p-3 text-sm focus:ring-2 focus:ring-amber-500 outline-none">
              </div>
            </div>
            <p class="text-xs text-slate-400 mt-2">minimum 8 characters, with uppercase & number</p>
          </div>

          <!-- Notification Preferences -->
          <div class="bg-white rounded-2xl border border-slate-200 p-6">
            <h2 class="font-semibold text-lg flex items-center gap-2 border-b pb-3"><i class="fa-regular fa-bell text-amber-600"></i> notification preferences</h2>
            <div class="space-y-3 mt-4">
              <label class="flex items-center gap-3 text-sm cursor-pointer">
                <input type="checkbox" id="emailNotifications" checked class="rounded text-amber-600 focus:ring-amber-500">
                email about booking confirmations
              </label>
              <label class="flex items-center gap-3 text-sm cursor-pointer">
                <input type="checkbox" id="smsNotifications" checked class="rounded text-amber-600 focus:ring-amber-500">
                SMS for reservation reminders
              </label>
              <label class="flex items-center gap-3 text-sm cursor-pointer">
                <input type="checkbox" id="promoNotifications" class="rounded text-amber-600 focus:ring-amber-500">
                promotional offers & newsletters
              </label>
              <label class="flex items-center gap-3 text-sm cursor-pointer">
                <input type="checkbox" id="loyaltyNotifications" checked class="rounded text-amber-600 focus:ring-amber-500">
                loyalty reward updates
              </label>
            </div>
          </div>

          <!-- Save Buttons -->
          <div class="flex flex-wrap items-center gap-4 pt-4">
            <button onclick="saveProfile()" class="bg-amber-600 hover:bg-amber-700 text-white px-8 py-3 rounded-xl font-medium shadow-sm transition">save changes</button>
            <button onclick="resetForm()" class="border border-slate-300 bg-white hover:bg-slate-50 px-8 py-3 rounded-xl font-medium text-slate-700 transition">reset</button>
          </div>
        </div>
      </div>

      <!-- Bottom Hint -->
      <div class="mt-10 text-center text-xs text-slate-400 border-t pt-6">
        ✅ My Profile module — personal info, contact, address, password, preferences, and account actions.
      </div>
    </main>
  </div>

  <!-- Toast Container -->
  <div id="toastContainer" class="fixed top-4 right-4 z-50 space-y-2"></div>

  <script>
    // ==================== GLOBAL STATE ====================
    const App = {
      user: {
        name: 'Mia Cruz',
        initials: 'MC',
        email: 'mia.cruz@email.com',
        phone: '+63 917 555 1234',
        membership: 'gold',
        points: 1240,
        joinDate: '2024-03-15',
        dob: '1994-05-12',
        gender: 'female',
        nationality: 'Filipino',
        altPhone: '',
        address: '15 B. Gonzales St., Barangay San Antonio',
        city: 'Makati',
        postalCode: '1203',
        country: 'Philippines',
        language: 'English'
      },
      
      notifications: [
        { id: 1, read: false },
        { id: 2, read: false },
        { id: 3, read: false }
      ],
      
      init() {
        this.loadState();
        this.updateUI();
        this.setupEventListeners();
        this.updateDateTime();
        setInterval(() => this.updateDateTime(), 60000);
      },
      
      loadState() {
        const saved = localStorage.getItem('lucasPortal');
        if(saved) {
          try {
            const data = JSON.parse(saved);
            if(data.user) this.user = { ...this.user, ...data.user };
            if(data.notifications) this.notifications = data.notifications;
          } catch(e) {
            console.error('Failed to load state:', e);
          }
        }
      },
      
      saveState() {
        localStorage.setItem('lucasPortal', JSON.stringify({
          user: this.user,
          notifications: this.notifications
        }));
      },
      
      updateUI() {
        document.querySelectorAll('.user-name').forEach(el => el.textContent = this.user.name);
        document.querySelectorAll('.user-points').forEach(el => el.textContent = this.user.points.toLocaleString());
        document.querySelectorAll('.user-membership').forEach(el => el.textContent = this.user.membership);
        document.getElementById('userInitials').textContent = this.user.initials;
        
        const unreadCount = this.notifications.filter(n => !n.read).length;
        document.querySelectorAll('.notification-count').forEach(el => el.textContent = unreadCount);
        
        const pointsToNext = 2000 - this.user.points;
        document.querySelectorAll('.points-to-next').forEach(el => el.textContent = Math.max(0, pointsToNext));
        
        const progress = (this.user.points / 2000) * 100;
        const progressBar = document.getElementById('pointsProgress');
        if(progressBar) progressBar.style.width = `${Math.min(progress, 100)}%`;
      },
      
      updateDateTime() {
        const now = new Date();
        const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        const dateStr = now.toLocaleDateString('en-US', options);
        const dateElement = document.getElementById('currentDateTime');
        if(dateElement) dateElement.textContent = dateStr;
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

    // ==================== NAVIGATION ====================
    function navigateTo(page) {
      const urls = {
        'dashboard': 'dashboard.html',
        'profile': 'my_profile.html',
        'hotel': 'hotel_booking.html',
        'reservations': 'my_reservation.html',
        'restaurant': 'restaurant_reservation.html',
        'menu': 'order_food.html',
        'payments': 'payments.html',
        'loyalty': 'loyalty_rewards.html',
        'reviews': 'reviews.html',
        'notifications': 'Notifications.html',
        'help': 'help_n_support.html'
      };
      
      if(urls[page]) {
        window.location.href = urls[page];
      } else {
        App.showToast('Page not found', 'error');
      }
    }

    function logout() {
      if(confirm('Are you sure you want to logout?')) {
        window.location.href = 'index.html';
      }
    }

    // ==================== PROFILE FUNCTIONS ====================
    function saveProfile() {
      // Get form values
      const firstName = document.getElementById('firstName').value.trim();
      const lastName = document.getElementById('lastName').value.trim();
      const email = document.getElementById('email').value.trim();
      const phone = document.getElementById('phone').value.trim();
      const dob = document.getElementById('dob').value;
      const gender = document.getElementById('gender').value;
      const nationality = document.getElementById('nationality').value;
      const altPhone = document.getElementById('altPhone').value.trim();
      const address = document.getElementById('address').value;
      const city = document.getElementById('city').value;
      const postalCode = document.getElementById('postalCode').value;
      const country = document.getElementById('country').value;
      const language = document.getElementById('language').value;

      // Validate required fields
      if(!firstName || !lastName) {
        App.showToast('First name and last name are required', 'error');
        return;
      }

      if(!email) {
        App.showToast('Email is required', 'error');
        return;
      }

      if(!App.validateEmail(email)) {
        App.showToast('Please enter a valid email address', 'error');
        return;
      }

      if(!phone) {
        App.showToast('Phone number is required', 'error');
        return;
      }

      if(!App.validatePhone(phone)) {
        App.showToast('Please enter a valid phone number', 'error');
        return;
      }

      // Update user object
      App.user = {
        ...App.user,
        name: `${firstName} ${lastName}`,
        initials: `${firstName[0]}${lastName[0]}`,
        email,
        phone,
        dob,
        gender,
        nationality,
        altPhone,
        address,
        city,
        postalCode,
        country,
        language
      };

      // Save notification preferences
      const preferences = {
        email: document.getElementById('emailNotifications').checked,
        sms: document.getElementById('smsNotifications').checked,
        promo: document.getElementById('promoNotifications').checked,
        loyalty: document.getElementById('loyaltyNotifications').checked
      };
      localStorage.setItem('notificationPreferences', JSON.stringify(preferences));

      App.saveState();
      App.updateUI();
      App.showToast('Profile updated successfully!', 'success');
    }

    function changePhoto() {
      // Simulate photo upload
      const fileInput = document.createElement('input');
      fileInput.type = 'file';
      fileInput.accept = 'image/*';
      fileInput.onchange = (e) => {
        if(e.target.files[0]) {
          App.showToast('Photo updated successfully!', 'success');
        }
      };
      fileInput.click();
    }

    function deactivateAccount() {
      if(confirm('Are you absolutely sure? This action cannot be undone.')) {
        if(confirm('All your data will be permanently deleted. Continue?')) {
          localStorage.removeItem('lucasPortal');
          App.showToast('Account deactivated', 'info');
          setTimeout(() => window.location.href = 'index.html', 1500);
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

    function changePassword() {
      const current = document.getElementById('currentPassword').value;
      const newPass = document.getElementById('newPassword').value;
      const confirm = document.getElementById('confirmPassword').value;

      if(!current || !newPass || !confirm) {
        App.showToast('Please fill all password fields', 'error');
        return;
      }

      if(newPass !== confirm) {
        App.showToast('New passwords do not match', 'error');
        return;
      }

      if(newPass.length < 8) {
        App.showToast('Password must be at least 8 characters', 'error');
        return;
      }

      if(!/[A-Z]/.test(newPass)) {
        App.showToast('Password must contain at least one uppercase letter', 'error');
        return;
      }

      if(!/[0-9]/.test(newPass)) {
        App.showToast('Password must contain at least one number', 'error');
        return;
      }

      App.showToast('Password changed successfully!', 'success');
      
      // Clear password fields
      document.getElementById('currentPassword').value = '';
      document.getElementById('newPassword').value = '';
      document.getElementById('confirmPassword').value = '';
    }

    function resetForm() {
      if(confirm('Reset all changes?')) {
        document.getElementById('firstName').value = App.user.name.split(' ')[0];
        document.getElementById('lastName').value = App.user.name.split(' ')[1] || '';
        document.getElementById('email').value = App.user.email;
        document.getElementById('phone').value = App.user.phone;
        document.getElementById('dob').value = App.user.dob || '1994-05-12';
        document.getElementById('gender').value = App.user.gender || 'female';
        document.getElementById('nationality').value = App.user.nationality || 'Filipino';
        document.getElementById('altPhone').value = App.user.altPhone || '';
        document.getElementById('address').value = App.user.address || '';
        document.getElementById('city').value = App.user.city || '';
        document.getElementById('postalCode').value = App.user.postalCode || '';
        document.getElementById('country').value = App.user.country || '';
        document.getElementById('language').value = App.user.language || 'English';
        
        App.showToast('Form reset', 'info');
      }
    }

    function setupEventListeners() {
      // Real-time validation
      document.getElementById('email')?.addEventListener('blur', function() {
        if(this.value && !App.validateEmail(this.value)) {
          this.classList.add('border-red-500');
        } else {
          this.classList.remove('border-red-500');
        }
      });

      document.getElementById('phone')?.addEventListener('blur', function() {
        if(this.value && !App.validatePhone(this.value)) {
          this.classList.add('border-red-500');
        } else {
          this.classList.remove('border-red-500');
        }
      });

      // Password strength indicator
      document.getElementById('newPassword')?.addEventListener('input', function() {
        const strength = calculatePasswordStrength(this.value);
        const indicator = document.getElementById('passwordStrength');
        if(!indicator) {
          const div = document.createElement('div');
          div.id = 'passwordStrength';
          div.className = 'text-xs mt-1';
          this.parentNode.appendChild(div);
        }
        const strengthEl = document.getElementById('passwordStrength');
        if(strengthEl) {
          strengthEl.textContent = `Strength: ${strength}`;
          strengthEl.className = `text-xs mt-1 ${
            strength === 'Strong' ? 'text-green-600' :
            strength === 'Medium' ? 'text-amber-600' :
            'text-red-600'
          }`;
        }
      });
    }

    function calculatePasswordStrength(password) {
      if(password.length < 8) return 'Weak';
      let score = 0;
      if(/[A-Z]/.test(password)) score++;
      if(/[a-z]/.test(password)) score++;
      if(/[0-9]/.test(password)) score++;
      if(/[^A-Za-z0-9]/.test(password)) score++;
      
      if(score >= 4 && password.length >= 10) return 'Strong';
      if(score >= 3) return 'Medium';
      return 'Weak';
    }

    // Initialize
    document.addEventListener('DOMContentLoaded', () => {
      App.init();
      setupEventListeners();
      
      // Load notification preferences
      const prefs = localStorage.getItem('notificationPreferences');
      if(prefs) {
        try {
          const p = JSON.parse(prefs);
          document.getElementById('emailNotifications').checked = p.email !== false;
          document.getElementById('smsNotifications').checked = p.sms !== false;
          document.getElementById('promoNotifications').checked = p.promo || false;
          document.getElementById('loyaltyNotifications').checked = p.loyalty !== false;
        } catch(e) {}
      }
    });
  </script>
</body>
</html>