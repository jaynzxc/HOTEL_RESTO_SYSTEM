<?php require_once '../../controller/customer/get/hotel_booking.php' ?>

<!DOCTYPE html>
<html lang="en">

  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lùcas · hotel booking</title>
    <!-- Tailwind via CDN + Font Awesome 6 + SweetAlert2 -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
      .step-indicator {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 2rem;
        flex-wrap: wrap;
        gap: 0.5rem;
      }

      .step {
        display: flex;
        flex-direction: column;
        align-items: center;
        position: relative;
        flex: 1;
        min-width: 120px;
        cursor: pointer;
      }

      .step:not(:last-child)::after {
        content: '';
        position: absolute;
        top: 1.5rem;
        right: -50%;
        width: 100%;
        height: 2px;
        background: #e5e7eb;
        z-index: 0;
      }

      .step.active:not(:last-child)::after,
      .step.completed:not(:last-child)::after {
        background: #d97706;
      }

      .step-number {
        width: 3rem;
        height: 3rem;
        border-radius: 9999px;
        background: #f3f4f6;
        color: #6b7280;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        margin-bottom: 0.5rem;
        position: relative;
        z-index: 1;
        border: 2px solid #e5e7eb;
      }

      .step.active .step-number {
        background: #d97706;
        color: white;
        border-color: #d97706;
      }

      .step.completed .step-number {
        background: #10b981;
        color: white;
        border-color: #10b981;
      }

      .step-title {
        font-size: 0.75rem;
        font-weight: 500;
        color: #6b7280;
        text-align: center;
      }

      .step.active .step-title {
        color: #d97706;
        font-weight: 600;
      }

      .step.completed .step-title {
        color: #10b981;
      }

      .room-card {
        border: 2px solid #e5e7eb;
        border-radius: 1rem;
        padding: 1rem;
        cursor: pointer;
        transition: all 0.2s;
      }

      .room-card:hover {
        border-color: #d97706;
        transform: translateY(-2px);
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
      }

      .room-card.selected {
        border-color: #d97706;
        background-color: #fffbeb;
      }

      .room-price {
        font-size: 1.5rem;
        font-weight: 700;
        color: #1f2937;
      }

      .room-price small {
        font-size: 0.875rem;
        font-weight: normal;
        color: #6b7280;
      }

      .amenity-tag {
        background-color: #f3f4f6;
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        color: #4b5563;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
      }

      .btn {
        padding: 0.75rem 1.5rem;
        border-radius: 0.75rem;
        font-size: 0.875rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
      }

      .btn-primary {
        background-color: #d97706;
        color: white;
      }

      .btn-primary:hover {
        background-color: #b45309;
      }

      .btn-outline {
        background-color: transparent;
        border: 1px solid #e5e7eb;
        color: #4b5563;
      }

      .btn-outline:hover {
        background-color: #f9fafb;
      }

      .progress-bar {
        height: 0.5rem;
        background-color: #e5e7eb;
        border-radius: 9999px;
        overflow: hidden;
        margin-bottom: 2rem;
      }

      .progress-fill {
        height: 100%;
        background-color: #d97706;
        transition: width 0.3s ease;
      }

      .required-asterisk {
        color: #ef4444;
        margin-left: 0.25rem;
      }

      .input-error {
        border-color: #ef4444 !important;
      }

      .error-message {
        color: #ef4444;
        font-size: 0.75rem;
        margin-top: 0.25rem;
      }
    </style>
  </head>

  <body class="bg-slate-50 font-sans antialiased">

    <div class="min-h-screen flex flex-col lg:flex-row">

      <!-- ========== SIDEBAR ========== -->
      <?php require './components/customer_nav.php' ?>

      <!-- ========== MAIN CONTENT ========== -->
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
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
          <div>
            <h1 class="text-2xl lg:text-3xl font-light text-slate-800">Hotel booking</h1>
            <p class="text-sm text-slate-500 mt-0.5" id="currentDateTime"></p>
          </div>
          <div
            class="bg-white border border-slate-200 rounded-full px-4 py-2 text-sm flex items-center gap-2 shadow-sm">
            <i class="fa-regular fa-calendar text-slate-400"></i> <span id="currentDateDisplay"></span>
          </div>
        </div>

        <!-- PROGRESS & STEPS -->
        <div class="progress-bar">
          <div class="progress-fill" id="progressFill" style="width: 50%"></div>
        </div>

        <div class="step-indicator">
          <div class="step active" id="step1" onclick="attemptStepChange(1)">
            <div class="step-number">1</div>
            <div class="step-title">Guest details</div>
          </div>
          <div class="step" id="step2" onclick="attemptStepChange(2)">
            <div class="step-number">2</div>
            <div class="step-title">Select room</div>
          </div>
        </div>

        <!-- STEP 1: Guest Details -->
        <div id="step1-content" class="block">
          <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
            <h2 class="text-xl font-semibold mb-6 flex items-center gap-2"><i
                class="fa-regular fa-user text-amber-600"></i> Guest Details</h2>

            <div id="step1Errors" class="hidden bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-lg mb-4">
            </div>

            <div class="space-y-4">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="block text-xs text-slate-500 mb-1">First name <span
                      class="required-asterisk">*</span></label>
                  <input type="text" id="firstName"
                    value="<?php echo htmlspecialchars($_SESSION['form_data']['first_name'] ?? $user['first_name'] ?? ''); ?>"
                    placeholder="e.g. Maria"
                    class="w-full border p-3 rounded-xl focus:ring-2 focus:ring-amber-500 outline-none">
                  <div class="error-message hidden" id="firstNameError"></div>
                </div>
                <div>
                  <label class="block text-xs text-slate-500 mb-1">Last name <span
                      class="required-asterisk">*</span></label>
                  <input type="text" id="lastName"
                    value="<?php echo htmlspecialchars($_SESSION['form_data']['last_name'] ?? $user['last_name'] ?? ''); ?>"
                    placeholder="e.g. Santos"
                    class="w-full border p-3 rounded-xl focus:ring-2 focus:ring-amber-500 outline-none">
                  <div class="error-message hidden" id="lastNameError"></div>
                </div>
              </div>

              <div>
                <label class="block text-xs text-slate-500 mb-1">Email <span class="required-asterisk">*</span></label>
                <input type="email" id="email"
                  value="<?php echo htmlspecialchars($_SESSION['form_data']['email'] ?? $user['email'] ?? ''); ?>"
                  placeholder="name@example.com"
                  class="w-full border p-3 rounded-xl focus:ring-2 focus:ring-amber-500 outline-none">
                <div class="error-message hidden" id="emailError"></div>
              </div>

              <div>
                <label class="block text-xs text-slate-500 mb-1">Phone <span class="required-asterisk">*</span></label>
                <input type="tel" id="phone"
                  value="<?php echo htmlspecialchars($_SESSION['form_data']['phone'] ?? $user['phone'] ?? ''); ?>"
                  placeholder="+63 912 345 6789"
                  class="w-full border p-3 rounded-xl focus:ring-2 focus:ring-amber-500 outline-none">
                <div class="error-message hidden" id="phoneError"></div>
              </div>

              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-xs text-slate-500 mb-1">Check-in <span
                      class="required-asterisk">*</span></label>
                  <input type="date" id="step1Checkin"
                    value="<?php echo htmlspecialchars($_SESSION['form_data']['check_in'] ?? ''); ?>"
                    min="<?php echo date('Y-m-d'); ?>"
                    class="w-full border p-3 rounded-xl focus:ring-2 focus:ring-amber-500 outline-none">
                  <div class="error-message hidden" id="checkinError"></div>
                </div>
                <div>
                  <label class="block text-xs text-slate-500 mb-1">Check-out <span
                      class="required-asterisk">*</span></label>
                  <input type="date" id="step1Checkout"
                    value="<?php echo htmlspecialchars($_SESSION['form_data']['check_out'] ?? ''); ?>"
                    min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>"
                    class="w-full border p-3 rounded-xl focus:ring-2 focus:ring-amber-500 outline-none">
                  <div class="error-message hidden" id="checkoutError"></div>
                </div>
              </div>

              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-xs text-slate-500 mb-1">Adults</label>
                  <select id="adults"
                    class="w-full border p-3 rounded-xl focus:ring-2 focus:ring-amber-500 outline-none">
                    <?php for ($i = 1; $i <= 4; $i++): ?>
                      <option value="<?php echo $i; ?>" <?php echo ($i == 2) ? 'selected' : ''; ?>><?php echo $i; ?>
                        Adult<?php echo $i > 1 ? 's' : ''; ?></option>
                    <?php endfor; ?>
                  </select>
                </div>
                <div>
                  <label class="block text-xs text-slate-500 mb-1">Children</label>
                  <select id="children"
                    class="w-full border p-3 rounded-xl focus:ring-2 focus:ring-amber-500 outline-none">
                    <?php for ($i = 0; $i <= 3; $i++): ?>
                      <option value="<?php echo $i; ?>"><?php echo $i; ?> Child<?php echo $i != 1 ? 'ren' : ''; ?>
                      </option>
                    <?php endfor; ?>
                  </select>
                </div>
              </div>

              <div>
                <label class="block text-xs text-slate-500 mb-1">Special requests (optional)</label>
                <textarea id="specialRequests" rows="2" placeholder="e.g., early check-in, extra pillows..."
                  class="w-full border p-3 rounded-xl focus:ring-2 focus:ring-amber-500 outline-none"><?php echo htmlspecialchars($_SESSION['form_data']['special_requests'] ?? ''); ?></textarea>
              </div>

              <div class="flex justify-end">
                <button onclick="validateStep1()" class="btn btn-primary">
                  Continue to room selection <i class="fa-regular fa-arrow-right"></i>
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- STEP 2: Select Room -->
        <div id="step2-content" class="hidden">
          <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
            <h2 class="text-xl font-semibold mb-4"><i class="fa-regular fa-bed text-amber-600"></i> Select your room
            </h2>

            <div id="roomSelectionContainer" class="space-y-3">
              <?php foreach ($rooms as $room): ?>
                <div class="room-card" id="room<?php echo $room['id']; ?>"
                  onclick="selectRoom('<?php echo $room['id']; ?>', '<?php echo addslashes($room['name']); ?>', <?php echo $room['price']; ?>)">
                  <div class="flex justify-between items-start">
                    <div>
                      <span class="text-lg font-semibold">Room <?php echo $room['id']; ?> ·
                        <?php echo htmlspecialchars($room['name']); ?></span>
                      <?php if (!empty($room['amenities'])): ?>
                        <span class="ml-2 amenity-tag"><i class="fa-regular fa-wifi"></i>
                          <?php echo htmlspecialchars($room['amenities']); ?></span>
                      <?php endif; ?>
                    </div>
                    <div class="room-price">₱<?php echo number_format($room['price']); ?> <small>/night</small></div>
                  </div>
                  <div class="mt-1 text-sm text-gray-600">
                    <span class="mr-4"><i class="fa-regular fa-bed"></i>
                      <?php echo htmlspecialchars($room['beds']); ?></span>
                    <span><i class="fa-regular fa-mountain"></i> <?php echo htmlspecialchars($room['view']); ?></span>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>

            <div class="mt-6 p-4 bg-amber-50 rounded-xl hidden" id="bookingSummary">
              <h3 class="font-semibold mb-2">Booking Summary</h3>
              <div class="space-y-1 text-sm">
                <p><span class="text-slate-600">Room:</span> <span id="summaryRoomName"></span></p>
                <p><span class="text-slate-600">Price per night:</span> ₱<span id="summaryPricePerNight"></span></p>
                <p><span class="text-slate-600">Nights:</span> <span id="summaryNights"></span></p>
                <p><span class="text-slate-600">Subtotal:</span> ₱<span id="summarySubtotal"></span></p>
                <p><span class="text-slate-600">Tax (12%):</span> ₱<span id="summaryTax"></span></p>
                <p class="font-semibold text-amber-800">Total: ₱<span id="summaryTotal"></span></p>
                <p class="text-xs text-green-600">You'll earn <span id="summaryPoints"></span> loyalty points!</p>
              </div>
            </div>

            <div class="flex gap-3 mt-6">
              <button onclick="attemptBackToStep(1)" class="flex-1 btn btn-outline">
                <i class="fa-regular fa-arrow-left"></i> Back
              </button>
              <button onclick="createBooking()" class="flex-1 btn btn-primary" id="proceedBtn" disabled>
                Proceed to payment <i class="fa-regular fa-credit-card"></i>
              </button>
            </div>
          </div>
        </div>

        <!-- bottom hint -->
        <div class="mt-8 text-center text-xs text-slate-400 border-t pt-4">
          ✅ Complete your booking to earn loyalty points!
        </div>
      </main>
    </div>

    <script>
      (function () {
        // ---------- global state ----------
        let currentStep = 1;
        let step1Completed = false;
        let selectedRoomId = null,
          selectedRoomName = null,
          selectedRoomPrice = 0;

        // Form data from step 1
        let step1Data = {
          firstName: '<?php echo addslashes($user['first_name'] ?? ''); ?>',
          lastName: '<?php echo addslashes($user['last_name'] ?? ''); ?>',
          email: '<?php echo addslashes($user['email'] ?? ''); ?>',
          phone: '<?php echo addslashes($user['phone'] ?? ''); ?>',
          checkIn: '',
          checkOut: '',
          adults: 2,
          children: 0,
          specialRequests: ''
        };

        // ---------- helpers ----------
        function updateDateTime() {
          const now = new Date();
          document.getElementById('currentDateTime').innerText = now.toLocaleString('en-US', {
            month: 'short',
            day: 'numeric',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
          });
          document.getElementById('currentDateDisplay').innerText = now.toLocaleDateString('en-US', {
            weekday: 'long',
            month: 'long',
            day: 'numeric',
            year: 'numeric'
          });
        }
        updateDateTime();
        setInterval(updateDateTime, 60000);

        function isValidEmail(e) {
          return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(e);
        }

        function isValidPhone(p) {
          return /^[\+]?[(]?[0-9]{3}[)]?[-\s\.]?[0-9]{3}[-\s\.]?[0-9]{4,6}$/.test(p);
        }

        function showError(title, message) {
          Swal.fire({
            title: title,
            text: message,
            icon: 'warning',
            confirmButtonColor: '#d97706'
          });
        }

        function showSuccess(message) {
          Swal.fire({
            title: 'Success!',
            text: message,
            icon: 'success',
            confirmButtonColor: '#d97706',
            timer: 2000
          });
        }

        // step navigation
        window.attemptStepChange = function (targetStep) {
          if (targetStep === currentStep) return;
          if (targetStep < currentStep) {
            goToStep(targetStep);
            return;
          }
          if (targetStep > currentStep) {
            if (!step1Completed) {
              showError('Cannot proceed', 'Please complete step 1 first');
              return;
            }
            goToStep(targetStep);
          }
        };

        window.attemptBackToStep = function (t) {
          if (t < currentStep) goToStep(t);
        };

        function goToStep(step) {
          for (let i = 1; i <= 2; i++) {
            document.getElementById(`step${i}-content`).classList.add('hidden');
          }
          document.getElementById(`step${step}-content`).classList.remove('hidden');

          for (let i = 1; i <= 2; i++) {
            let el = document.getElementById(`step${i}`);
            el.classList.remove('active', 'completed');
            if (i === 1 && step1Completed) el.classList.add('completed');
            if (i === step) el.classList.add('active');
          }

          let progress = step === 2 ? 100 : 50;
          if (step1Completed) progress = step === 1 ? 50 : 100;
          document.getElementById('progressFill').style.width = progress + '%';
          currentStep = step;
        }

        // clear field errors
        function clearFieldError(fieldId) {
          const input = document.getElementById(fieldId);
          const errorEl = document.getElementById(fieldId + 'Error');
          if (input) input.classList.remove('input-error');
          if (errorEl) {
            errorEl.classList.add('hidden');
            errorEl.innerText = '';
          }
        }

        // show field error
        function showFieldError(fieldId, message) {
          const input = document.getElementById(fieldId);
          const errorEl = document.getElementById(fieldId + 'Error');
          if (input) input.classList.add('input-error');
          if (errorEl) {
            errorEl.innerText = message;
            errorEl.classList.remove('hidden');
          }
        }

        // step 1 validation
        window.validateStep1 = function () {
          // Clear previous errors
          const errorFields = ['firstName', 'lastName', 'email', 'phone', 'step1Checkin', 'step1Checkout'];
          errorFields.forEach(f => clearFieldError(f));

          // Get values
          const firstName = document.getElementById('firstName').value.trim();
          const lastName = document.getElementById('lastName').value.trim();
          const email = document.getElementById('email').value.trim();
          const phone = document.getElementById('phone').value.trim();
          const checkIn = document.getElementById('step1Checkin').value;
          const checkOut = document.getElementById('step1Checkout').value;
          const adults = document.getElementById('adults').value;
          const children = document.getElementById('children').value;
          const specialRequests = document.getElementById('specialRequests').value;

          let isValid = true;

          // Validate each field
          if (!firstName) {
            showFieldError('firstName', 'First name is required');
            isValid = false;
          }

          if (!lastName) {
            showFieldError('lastName', 'Last name is required');
            isValid = false;
          }

          if (!email) {
            showFieldError('email', 'Email is required');
            isValid = false;
          } else if (!isValidEmail(email)) {
            showFieldError('email', 'Please enter a valid email address');
            isValid = false;
          }

          if (!phone) {
            showFieldError('phone', 'Phone number is required');
            isValid = false;
          } else if (!isValidPhone(phone)) {
            showFieldError('phone', 'Please enter a valid phone number');
            isValid = false;
          }

          if (!checkIn) {
            showFieldError('step1Checkin', 'Check-in date is required');
            isValid = false;
          }

          if (!checkOut) {
            showFieldError('step1Checkout', 'Check-out date is required');
            isValid = false;
          }

          if (checkIn && checkOut) {
            const checkInDate = new Date(checkIn);
            const checkOutDate = new Date(checkOut);
            const today = new Date();
            today.setHours(0, 0, 0, 0);

            if (checkInDate < today) {
              showFieldError('step1Checkin', 'Check-in date cannot be in the past');
              isValid = false;
            }

            if (checkOutDate <= checkInDate) {
              showFieldError('step1Checkout', 'Check-out must be after check-in');
              isValid = false;
            }
          }

          if (!isValid) return;

          // Save step 1 data
          step1Data = {
            firstName,
            lastName,
            email,
            phone,
            checkIn,
            checkOut,
            adults,
            children,
            specialRequests
          };

          step1Completed = true;
          goToStep(2);
        };

        // step 2 functions
        window.selectRoom = function (id, name, price) {
          document.querySelectorAll('.room-card').forEach(el => el.classList.remove('selected'));
          document.getElementById(`room${id}`).classList.add('selected');
          selectedRoomId = id;
          selectedRoomName = name;
          selectedRoomPrice = price;

          // Enable proceed button
          document.getElementById('proceedBtn').disabled = false;

          // Calculate and show summary
          const checkIn = new Date(step1Data.checkIn);
          const checkOut = new Date(step1Data.checkOut);
          const nights = Math.max(1, Math.ceil((checkOut - checkIn) / (1000 * 60 * 60 * 24)));
          const subtotal = price * nights;
          const tax = Math.round(subtotal * 0.12 * 100) / 100;
          const total = subtotal + tax;
          const pointsEarned = Math.floor(subtotal / 100) * 5;

          document.getElementById('summaryRoomName').innerText = name;
          document.getElementById('summaryPricePerNight').innerText = price.toLocaleString();
          document.getElementById('summaryNights').innerText = nights;
          document.getElementById('summarySubtotal').innerText = subtotal.toLocaleString();
          document.getElementById('summaryTax').innerText = tax.toLocaleString();
          document.getElementById('summaryTotal').innerText = total.toLocaleString();
          document.getElementById('summaryPoints').innerText = pointsEarned;

          document.getElementById('bookingSummary').classList.remove('hidden');
        };

        // create booking via AJAX
        window.createBooking = function () {
          if (!selectedRoomId) {
            showError('No room selected', 'Please select a room to continue');
            return;
          }

          // Prepare data
          const bookingData = {
            first_name: step1Data.firstName,
            last_name: step1Data.lastName,
            email: step1Data.email,
            phone: step1Data.phone,
            check_in: step1Data.checkIn,
            check_out: step1Data.checkOut,
            adults: step1Data.adults,
            children: step1Data.children,
            special_requests: step1Data.specialRequests,
            room_id: selectedRoomId,
            room_name: selectedRoomName,
            room_price: selectedRoomPrice
          };

          // Show loading
          Swal.fire({
            title: 'Creating your booking...',
            html: 'Please wait',
            allowOutsideClick: false,
            didOpen: () => {
              Swal.showLoading();
            }
          });

          // Send AJAX request
          fetch('../../controller/customer/post/create_booking.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams(bookingData)
          })
            .then(response => response.json())
            .then(data => {
              if (data.success) {
                // Save to sessionStorage for payment page
                sessionStorage.setItem('pendingBooking', JSON.stringify(data.booking));

                Swal.fire({
                  title: 'Booking Created!',
                  html: `
                        <div class="text-left">
                            <p><strong>Reference:</strong> ${data.booking.reference}</p>
                            <p><strong>Room:</strong> ${data.booking.room_name}</p>
                            <p><strong>Total:</strong> ₱${data.booking.total.toLocaleString()}</p>
                            <p><strong>Points Earned:</strong> +${data.booking.points_earned}</p>
                        </div>
                    `,
                  icon: 'success',
                  confirmButtonColor: '#d97706',
                  confirmButtonText: 'Proceed to Payment'
                }).then((result) => {
                  if (result.isConfirmed) {
                    window.location.href = './payments.php';
                  }
                });
              }
              else if (data.has_pending) {
                // User has pending booking
                Swal.close();

                const pending = data.pending_booking;
                const formattedAmount = new Intl.NumberFormat('en-PH', {
                  style: 'currency',
                  currency: 'PHP'
                }).format(pending.total_amount);

                Swal.fire({
                  title: 'Pending Booking Found',
                  html: `
                        <div class="text-left">
                            <p class="mb-3">${data.message}</p>
                            <div class="bg-amber-50 p-3 rounded-lg">
                                <p><strong>Reference:</strong> ${pending.booking_reference}</p>
                                <p><strong>Room:</strong> ${pending.room_name}</p>
                                <p><strong>Check-in:</strong> ${pending.check_in}</p>
                                <p><strong>Check-out:</strong> ${pending.check_out}</p>
                                <p><strong>Total:</strong> ${formattedAmount}</p>
                            </div>
                        </div>
                    `,
                  icon: 'warning',
                  confirmButtonColor: '#d97706',
                  confirmButtonText: 'Pay Now',
                  showCancelButton: true,
                  cancelButtonText: 'Cancel',
                  cancelButtonColor: '#6b7280'
                }).then((result) => {
                  if (result.isConfirmed) {
                    // Go to payments page
                    window.location.href = './payments.php?type=hotel&id=' + pending.id;
                  }
                });
              }
              else {
                let errorMessage = data.message || 'An error occurred';
                if (data.errors) {
                  errorMessage = data.errors.join('<br>');
                }
                Swal.fire({
                  title: 'Error',
                  html: errorMessage,
                  icon: 'error',
                  confirmButtonColor: '#d97706'
                });
              }
            })
            .catch(error => {
              console.error('Error:', error);
              Swal.fire({
                title: 'Error',
                text: 'Failed to create booking. Please try again.',
                icon: 'error',
                confirmButtonColor: '#d97706'
              });
            });
        };
        // Initialize
        document.addEventListener('DOMContentLoaded', function () {
          // Set default dates if empty
          const today = new Date();
          const tomorrow = new Date(today);
          tomorrow.setDate(tomorrow.getDate() + 1);
          const nextWeek = new Date(today);
          nextWeek.setDate(nextWeek.getDate() + 3);

          if (!document.getElementById('step1Checkin').value) {
            document.getElementById('step1Checkin').value = tomorrow.toISOString().split('T')[0];
          }
          if (!document.getElementById('step1Checkout').value) {
            document.getElementById('step1Checkout').value = nextWeek.toISOString().split('T')[0];
          }

          // Clear session form data after 1 second (to prevent showing old messages)
          setTimeout(() => {
            <?php unset($_SESSION['form_data']); ?>
          }, 1000);
        });

      })();
    </script>
  </body>

</html>

<?php
// Clear session messages after displaying
unset($_SESSION['success']);
unset($_SESSION['error']);
unset($_SESSION['form_data']);
?>