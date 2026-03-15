<?php
session_start();

// Initialize session arrays
$_SESSION['error'] ??= [];
$_SESSION['success'] ??= [];
$_SESSION['old'] ??= [];
?>

<!DOCTYPE html>
<html lang="en">

  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register · Customer Portal</title>
    <!-- Tailwind CSS - compiled via CLI -->
    <link href="/src/output.css" rel="stylesheet">
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  </head>

  <body class="bg-slate-100 font-sans antialiased flex items-center justify-center min-h-screen p-4">

    <!-- main card: clean, centered, no side panel -->
    <div class="w-full max-w-md bg-white rounded-2xl shadow-xl p-8">

      <!-- brand (simplified) -->
      <div class="flex justify-center items-center gap-2 text-amber-700 mb-3">
        <i class="fa-solid fa-utensils text-xl"></i>
        <i class="fa-solid fa-bed text-xl"></i>
        <span class="font-semibold text-xl tracking-tight text-slate-800">Lùcas<span
            class="text-amber-600">.stay</span></span>
      </div>

      <!-- title -->
      <h1 class="text-3xl font-bold text-center text-amber-600 mb-6">Create Account</h1>

      <?php require_once '../message.php'; ?>

      <!--registration -->
      <div id="registerForm">
        <form id="registerFormElement" action="../../controller/auth/register.php" method="POST">
          <div class="space-y-4">
            <!-- Full Name -->
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">Full Name</label>
              <div class="relative">
                <i class="fa-regular fa-user absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                <input type="text" id="fullName" name="full_name"
                  value="<?php echo htmlspecialchars($_SESSION['old']['full_name'] ?? ''); ?>" placeholder="John Doe"
                  class="w-full border <?php echo isset($_SESSION['error']['full_name']) ? 'border-red-500' : 'border-slate-200'; ?> rounded-xl pl-9 pr-4 py-2.5 text-sm focus:ring-1 focus:ring-amber-500 outline-none"
                  required>
              </div>
              <?php if (isset($_SESSION['error']['full_name'])): ?>
                <p class="text-xs text-red-600 mt-1">
                  <?php echo htmlspecialchars($_SESSION['error']['full_name']); ?>
                </p>
              <?php else: ?>
                <p id="nameError" class="text-xs text-red-600 mt-1 hidden"></p>
              <?php endif; ?>
            </div>

            <!-- Email -->
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">Email</label>
              <div class="relative">
                <i class="fa-regular fa-envelope absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                <input type="email" id="email" name="email"
                  value="<?php echo htmlspecialchars($_SESSION['old']['email'] ?? ''); ?>" placeholder="you@example.com"
                  class="w-full border <?php echo isset($_SESSION['error']['email']) ? 'border-red-500' : 'border-slate-200'; ?> rounded-xl pl-9 pr-4 py-2.5 text-sm focus:ring-1 focus:ring-amber-500 outline-none"
                  required>
              </div>
              <?php if (isset($_SESSION['error']['email'])): ?>
                <p class="text-xs text-red-600 mt-1">
                  <?php echo htmlspecialchars($_SESSION['error']['email']); ?>
                </p>
              <?php else: ?>
                <p id="emailError" class="text-xs text-red-600 mt-1 hidden"></p>
              <?php endif; ?>
            </div>

            <!-- Phone -->
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">Phone Number</label>
              <div class="relative">
                <i class="fa-solid fa-phone absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                <input type="tel" id="phone" name="phone"
                  value="<?php echo htmlspecialchars($_SESSION['old']['phone'] ?? ''); ?>"
                  placeholder="+63 912 345 6789"
                  class="w-full border <?php echo isset($_SESSION['error']['phone']) ? 'border-red-500' : 'border-slate-200'; ?> rounded-xl pl-9 pr-4 py-2.5 text-sm focus:ring-1 focus:ring-amber-500 outline-none"
                  required>
              </div>
              <?php if (isset($_SESSION['error']['phone'])): ?>
                <p class="text-xs text-red-600 mt-1">
                  <?php echo htmlspecialchars($_SESSION['error']['phone']); ?>
                </p>
              <?php else: ?>
                <p id="phoneError" class="text-xs text-red-600 mt-1 hidden"></p>
              <?php endif; ?>
            </div>

            <!-- Password -->
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">Password</label>
              <div class="relative">
                <i class="fa-solid fa-lock absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                <input type="password" id="password" name="password" placeholder="••••••••"
                  class="w-full border <?php echo isset($_SESSION['error']['password']) ? 'border-red-500' : 'border-slate-200'; ?> rounded-xl pl-9 pr-10 py-2.5 text-sm focus:ring-1 focus:ring-amber-500 outline-none"
                  required>
                <button type="button"
                  class="toggle-password absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                  <i class="fa-regular fa-eye-slash text-sm"></i>
                </button>
              </div>
              <?php if (isset($_SESSION['error']['password'])): ?>
                <p class="text-xs text-red-600 mt-1">
                  <?php echo htmlspecialchars($_SESSION['error']['password']); ?>
                </p>
              <?php else: ?>
                <p id="passwordError" class="text-xs text-red-600 mt-1 hidden"></p>
              <?php endif; ?>
              <!-- Password strength meter -->
              <div class="mt-2 h-1 w-full bg-slate-200 rounded-full overflow-hidden">
                <div id="passwordStrength" class="h-full w-0 transition-all duration-300"></div>
              </div>
              <p id="strengthText" class="text-xs text-slate-500 mt-1"></p>
            </div>

            <!-- Confirm Password -->
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">Confirm Password</label>
              <div class="relative">
                <i class="fa-solid fa-lock absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                <input type="password" id="confirmPassword" name="confirm_password" placeholder="••••••••"
                  class="w-full border <?php echo isset($_SESSION['error']['confirm_password']) ? 'border-red-500' : 'border-slate-200'; ?> rounded-xl pl-9 pr-10 py-2.5 text-sm focus:ring-1 focus:ring-amber-500 outline-none"
                  required>
                <button type="button"
                  class="toggle-password absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                  <i class="fa-regular fa-eye-slash text-sm"></i>
                </button>
              </div>
              <?php if (isset($_SESSION['error']['confirm_password'])): ?>
                <p class="text-xs text-red-600 mt-1">
                  <?php echo htmlspecialchars($_SESSION['error']['confirm_password']); ?>
                </p>
              <?php else: ?>
                <p id="confirmError" class="text-xs text-red-600 mt-1 hidden"></p>
              <?php endif; ?>
            </div>

            <!-- Terms and Conditions -->
            <div class="flex items-start gap-2 text-sm">
              <input type="checkbox" id="terms" name="terms" <?php echo isset($_SESSION['error']['terms']) ? '' : 'checked'; ?> class="mt-1 rounded text-amber-600 focus:ring-amber-500">
              <span class="text-slate-600">I agree to the <a href="#" id="termsLink"
                  class="text-amber-600 hover:underline">Terms of Service</a> and <a href="#" id="privacyLink"
                  class="text-amber-600 hover:underline">Privacy Policy</a></span>
            </div>
            <?php if (isset($_SESSION['error']['terms'])): ?>
              <p class="text-xs text-red-600 mt-1">
                <?php echo htmlspecialchars($_SESSION['error']['terms']); ?>
              </p>
            <?php else: ?>
              <p id="termsError" class="text-xs text-red-600 mt-1 hidden"></p>
            <?php endif; ?>

            <!-- Submit Button -->
            <button type="submit" id="registerBtn"
              class="w-full bg-amber-600 hover:bg-amber-700 text-white py-2.5 rounded-xl font-medium transition shadow-sm">Create
              Account</button>
          </div>
        </form>

        <!-- Login link -->
        <p class="text-center text-sm text-slate-500 mt-6">
          Already have an account? <a href="login.php" class="text-amber-600 font-medium hover:underline">Sign in</a>
        </p>
      </div>

    </div>

    <script>
      // Wait for DOM to load
      document.addEventListener('DOMContentLoaded', function () {

        // ===== PASSWORD VISIBILITY TOGGLE =====
        const toggleButtons = document.querySelectorAll('.toggle-password');

        toggleButtons.forEach(button => {
          button.addEventListener('click', function () {
            const input = this.previousElementSibling;
            const icon = this.querySelector('i');

            if (input.type === 'password') {
              input.type = 'text';
              icon.classList.remove('fa-eye-slash');
              icon.classList.add('fa-eye');
            } else {
              input.type = 'password';
              icon.classList.remove('fa-eye');
              icon.classList.add('fa-eye-slash');
            }
          });
        });

        // ===== GET ELEMENTS =====
        const fullName = document.getElementById('fullName');
        const email = document.getElementById('email');
        const phone = document.getElementById('phone');
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('confirmPassword');
        const terms = document.getElementById('terms');

        // Error elements
        const nameError = document.getElementById('nameError');
        const emailError = document.getElementById('emailError');
        const phoneError = document.getElementById('phoneError');
        const passwordError = document.getElementById('passwordError');
        const confirmError = document.getElementById('confirmError');
        const termsError = document.getElementById('termsError');

        // Password strength elements
        const strengthBar = document.getElementById('passwordStrength');
        const strengthText = document.getElementById('strengthText');

        // ===== REAL-TIME VALIDATION =====

        // Full Name validation
        if (fullName) {
          fullName.addEventListener('input', function () {
            const value = this.value.trim();
            if (value.length < 2) {
              showFieldError(nameError, 'Name must be at least 2 characters');
            } else if (!isValidName(value)) {
              showFieldError(nameError, 'Name should only contain letters and spaces');
            } else {
              hideFieldError(nameError);
            }
          });
        }

        // Email validation
        if (email) {
          email.addEventListener('input', function () {
            const value = this.value.trim();
            if (!isValidEmail(value)) {
              showFieldError(emailError, 'Please enter a valid email address');
            } else {
              hideFieldError(emailError);
            }
          });
        }

        // Phone validation
        if (phone) {
          phone.addEventListener('input', function () {
            const value = this.value.trim();
            if (!isValidPhone(value)) {
              showFieldError(phoneError, 'Please enter a valid phone number');
            } else {
              hideFieldError(phoneError);
            }
          });
        }

        // Password strength meter
        if (password) {
          password.addEventListener('input', function () {
            const value = this.value;
            const strength = checkPasswordStrength(value);
            updateStrengthMeter(strength);

            if (value.length > 0 && value.length < 6) {
              showFieldError(passwordError, 'Password must be at least 6 characters');
            } else {
              hideFieldError(passwordError);
            }

            // Also check confirm password if may laman
            if (confirmPassword && confirmPassword.value.trim() !== '') {
              checkPasswordMatch();
            }
          });
        }

        // Confirm password validation
        if (confirmPassword) {
          confirmPassword.addEventListener('input', checkPasswordMatch);
        }

        // ===== HELPER FUNCTIONS =====

        function isValidEmail(email) {
          const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
          return emailRegex.test(email);
        }

        function isValidName(name) {
          const nameRegex = /^[A-Za-z\s]+$/;
          return nameRegex.test(name);
        }

        function isValidPhone(phone) {
          const phoneRegex = /^[\+]?[(]?[0-9]{3}[)]?[-\s\.]?[0-9]{3}[-\s\.]?[0-9]{4,6}$/;
          return phoneRegex.test(phone);
        }

        function checkPasswordStrength(password) {
          let strength = 0;

          if (password.length >= 6) strength += 1;
          if (password.length >= 8) strength += 1;
          if (/[A-Z]/.test(password)) strength += 1;
          if (/[0-9]/.test(password)) strength += 1;
          if (/[^A-Za-z0-9]/.test(password)) strength += 1;

          return Math.min(strength, 4); // Max of 4
        }

        function updateStrengthMeter(strength) {
          const colors = ['bg-red-500', 'bg-orange-500', 'bg-yellow-500', 'bg-blue-500', 'bg-green-500'];
          const texts = ['Very Weak', 'Weak', 'Fair', 'Good', 'Strong'];
          const widths = ['w-1/5', 'w-2/5', 'w-3/5', 'w-4/5', 'w-full'];

          if (password.value.length === 0) {
            strengthBar.style.width = '0';
            strengthText.textContent = '';
            return;
          }

          strengthBar.className = `h-full ${colors[strength]} ${widths[strength]}`;
          strengthText.textContent = `Password strength: ${texts[strength]}`;
        }

        function checkPasswordMatch() {
          const passwordValue = password.value;
          const confirmValue = confirmPassword.value;

          if (confirmValue && passwordValue !== confirmValue) {
            showFieldError(confirmError, 'Passwords do not match');
          } else if (confirmValue && passwordValue === confirmValue) {
            hideFieldError(confirmError);
          }
        }

        function showFieldError(element, message) {
          if (element) {
            element.textContent = message;
            element.classList.remove('hidden');
          }
        }

        function hideFieldError(element) {
          if (element) {
            element.classList.add('hidden');
          }
        }

        // ===== TERMS AND PRIVACY LINKS =====
        const termsLink = document.getElementById('termsLink');
        const privacyLink = document.getElementById('privacyLink');

        if (termsLink) {
          termsLink.addEventListener('click', function (e) {
            e.preventDefault();
            alert('Terms of Service would open here');
          });
        }

        if (privacyLink) {
          privacyLink.addEventListener('click', function (e) {
            e.preventDefault();
            alert('Privacy Policy would open here');
          });
        }

      }); // End DOMContentLoaded
    </script>
  </body>

</html>

<?php
// Clear old input after displaying
unset($_SESSION['error']);
unset($_SESSION['old']);
?>