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
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .password-strength-meter {
            transition: all 0.3s ease;
        }
        .input-valid {
            border-color: #10b981 !important;
            background-image: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="%2310b981"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>');
            background-repeat: no-repeat;
            background-position: right 10px center;
            background-size: 16px;
        }
        .input-invalid {
            border-color: #ef4444 !important;
        }
        .loading-spinner {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid #f3f3f3;
            border-top: 2px solid #d97706;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>

<body class="bg-slate-100 font-sans antialiased flex items-center justify-center min-h-screen p-4">

    <!-- main card -->
    <div class="w-full max-w-md bg-white rounded-2xl shadow-xl p-8">

        <!-- brand -->
        <div class="flex justify-center items-center gap-2 text-amber-700 mb-3">
            <i class="fa-solid fa-utensils text-xl"></i>
            <i class="fa-solid fa-bed text-xl"></i>
            <span class="font-semibold text-xl tracking-tight text-slate-800">Lùcas<span
                    class="text-amber-600">.stay</span></span>
        </div>

        <!-- title -->
        <h1 class="text-3xl font-bold text-center text-amber-600 mb-6">Create Account</h1>

        <!-- DISPLAY SUCCESS MESSAGES -->
        <?php if (!empty($_SESSION['success'])): ?>
              <div class="bg-green-50 border border-green-200 text-green-600 px-4 py-3 rounded-lg mb-4">
                  <?php foreach ($_SESSION['success'] as $message): ?>
                        <p class="text-sm"><i class="fas fa-circle-check mr-2"></i><?php echo htmlspecialchars($message); ?></p>
                  <?php endforeach; ?>
              </div>
              <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <!-- DISPLAY ERROR MESSAGES -->
        <?php if (!empty($_SESSION['error'])): ?>
              <div class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-lg mb-4">
                  <ul class="list-disc list-inside text-sm">
                      <?php foreach ($_SESSION['error'] as $field => $message): ?>
                            <li><?php echo htmlspecialchars($message); ?></li>
                      <?php endforeach; ?>
                  </ul>
              </div>
              <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <!-- Registration Form -->
        <div id="registerForm">
            <form id="registerFormElement" action="../../controller/auth/register.php" method="POST">
                <div class="space-y-4">
                    <!-- Full Name -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Full Name</label>
                        <div class="relative">
                            <i class="fas fa-user absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                            <input type="text" id="fullName" name="full_name"
                                value="<?php echo htmlspecialchars($_SESSION['old']['full_name'] ?? ''); ?>" 
                                placeholder="John Doe"
                                class="w-full border border-slate-200 rounded-xl pl-9 pr-4 py-2.5 text-sm focus:ring-1 focus:ring-amber-500 outline-none"
                                required>
                        </div>
                        <div id="nameFeedback" class="text-xs mt-1"></div>
                    </div>

                    <!-- Email -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                        <div class="relative">
                            <i class="fas fa-envelope absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                            <input type="email" id="email" name="email"
                                value="<?php echo htmlspecialchars($_SESSION['old']['email'] ?? ''); ?>" 
                                placeholder="you@example.com"
                                class="w-full border border-slate-200 rounded-xl pl-9 pr-10 py-2.5 text-sm focus:ring-1 focus:ring-amber-500 outline-none"
                                required>
                            <div id="emailCheckIcon" class="absolute right-3 top-1/2 -translate-y-1/2 hidden"></div>
                        </div>
                        <div id="emailFeedback" class="text-xs mt-1"></div>
                    </div>

                    <!-- Phone -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Phone Number</label>
                        <div class="relative">
                            <i class="fa-solid fa-phone absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                            <input type="tel" id="phone" name="phone"
                                value="<?php echo htmlspecialchars($_SESSION['old']['phone'] ?? ''); ?>"
                                placeholder="+63 912 345 6789"
                                class="w-full border border-slate-200 rounded-xl pl-9 pr-10 py-2.5 text-sm focus:ring-1 focus:ring-amber-500 outline-none"
                                required>
                            <div id="phoneCheckIcon" class="absolute right-3 top-1/2 -translate-y-1/2 hidden"></div>
                        </div>
                        <div id="phoneFeedback" class="text-xs mt-1"></div>
                    </div>

                    <!-- Password -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Password</label>
                        <div class="relative">
                            <i class="fa-solid fa-lock absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                            <input type="password" id="password" name="password" placeholder="••••••••"
                                class="w-full border border-slate-200 rounded-xl pl-9 pr-10 py-2.5 text-sm focus:ring-1 focus:ring-amber-500 outline-none"
                                required>
                            <button type="button"
                                class="toggle-password absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                                <i class="fas fa-eye-slash text-sm"></i>
                            </button>
                        </div>
                        <!-- Password strength meter -->
                        <div class="mt-2 h-1 w-full bg-slate-200 rounded-full overflow-hidden">
                            <div id="passwordStrength" class="h-full w-0 transition-all duration-300 password-strength-meter"></div>
                        </div>
                        <div id="strengthText" class="text-xs text-slate-500 mt-1"></div>
                        <div id="passwordFeedback" class="text-xs mt-1"></div>
                        <ul id="passwordRequirements" class="text-xs space-y-1 mt-2 hidden">
                            <li id="reqLength" class="text-slate-400"><i class="fas fa-circle mr-1 text-[8px]"></i> At least 8 characters</li>
                            <li id="reqUppercase" class="text-slate-400"><i class="fas fa-circle mr-1 text-[8px]"></i> At least 1 uppercase letter</li>
                            <li id="reqLowercase" class="text-slate-400"><i class="fas fa-circle mr-1 text-[8px]"></i> At least 1 lowercase letter</li>
                            <li id="reqNumber" class="text-slate-400"><i class="fas fa-circle mr-1 text-[8px]"></i> At least 1 number</li>
                            <li id="reqSpecial" class="text-slate-400"><i class="fas fa-circle mr-1 text-[8px]"></i> At least 1 special character</li>
                        </ul>
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Confirm Password</label>
                        <div class="relative">
                            <i class="fa-solid fa-lock absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                            <input type="password" id="confirmPassword" name="confirm_password" placeholder="••••••••"
                                class="w-full border border-slate-200 rounded-xl pl-9 pr-10 py-2.5 text-sm focus:ring-1 focus:ring-amber-500 outline-none"
                                required>
                            <button type="button"
                                class="toggle-password absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                                <i class="fas fa-eye-slash text-sm"></i>
                            </button>
                        </div>
                        <div id="confirmFeedback" class="text-xs mt-1"></div>
                    </div>

                    <!-- Terms and Conditions -->
                    <div class="flex items-start gap-2 text-sm">
                        <input type="checkbox" id="terms" name="terms" checked class="mt-1 rounded text-amber-600 focus:ring-amber-500">
                        <span class="text-slate-600">I agree to the <a href="#" id="termsLink"
                            class="text-amber-600 hover:underline">Terms of Service</a> and <a href="#" id="privacyLink"
                            class="text-amber-600 hover:underline">Privacy Policy</a></span>
                    </div>
                    <div id="termsFeedback" class="text-xs mt-1"></div>

                    <!-- Security Note -->
                    <div class="text-center text-xs text-slate-400">
                        <i class="fas fa-shield-alt mr-1"></i> Your information is protected and secure
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" id="registerBtn"
                        class="w-full bg-amber-600 hover:bg-amber-700 text-white py-2.5 rounded-xl font-medium transition shadow-sm">
                        Create Account
                    </button>
                </div>
            </form>

            <!-- Login link -->
            <p class="text-center text-sm text-slate-500 mt-6">
                Already have an account? <a href="./login_form.php" class="text-amber-600 font-medium hover:underline">Sign in</a>
            </p>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Get elements
            const fullName = document.getElementById('fullName');
            const email = document.getElementById('email');
            const phone = document.getElementById('phone');
            const password = document.getElementById('password');
            const confirmPassword = document.getElementById('confirmPassword');
            const terms = document.getElementById('terms');
            const registerBtn = document.getElementById('registerBtn');
            
            // Feedback elements
            const nameFeedback = document.getElementById('nameFeedback');
            const emailFeedback = document.getElementById('emailFeedback');
            const phoneFeedback = document.getElementById('phoneFeedback');
            const passwordFeedback = document.getElementById('passwordFeedback');
            const confirmFeedback = document.getElementById('confirmFeedback');
            const termsFeedback = document.getElementById('termsFeedback');
            
            // Password requirement elements
            const reqLength = document.getElementById('reqLength');
            const reqUppercase = document.getElementById('reqUppercase');
            const reqLowercase = document.getElementById('reqLowercase');
            const reqNumber = document.getElementById('reqNumber');
            const reqSpecial = document.getElementById('reqSpecial');
            
            const strengthBar = document.getElementById('passwordStrength');
            const strengthText = document.getElementById('strengthText');
            
            let emailTimeout, phoneTimeout;
            
            // Toggle password visibility
            document.querySelectorAll('.toggle-password').forEach(button => {
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
            
            // ===== REAL-TIME VALIDATION =====
            
            // Full Name validation
            if (fullName) {
                fullName.addEventListener('input', function () {
                    const value = this.value.trim();
                    if (value.length === 0) {
                        showFeedback(nameFeedback, '', '');
                        this.classList.remove('input-valid', 'input-invalid');
                    } else if (value.length < 2) {
                        showFeedback(nameFeedback, 'Name must be at least 2 characters', 'error');
                        this.classList.add('input-invalid');
                        this.classList.remove('input-valid');
                    } else if (!/^[A-Za-z\s]+$/.test(value)) {
                        showFeedback(nameFeedback, 'Name should only contain letters and spaces', 'error');
                        this.classList.add('input-invalid');
                        this.classList.remove('input-valid');
                    } else {
                        showFeedback(nameFeedback, '✓ Valid name', 'success');
                        this.classList.add('input-valid');
                        this.classList.remove('input-invalid');
                    }
                });
            }
            
            // Email validation with real-time check
            if (email) {
                email.addEventListener('input', function () {
                    const value = this.value.trim();
                    clearTimeout(emailTimeout);
                    
                    if (value.length === 0) {
                        showFeedback(emailFeedback, '', '');
                        this.classList.remove('input-valid', 'input-invalid');
                        document.getElementById('emailCheckIcon').classList.add('hidden');
                        return;
                    }
                    
                    if (!isValidEmail(value)) {
                        showFeedback(emailFeedback, 'Please enter a valid email address', 'error');
                        this.classList.add('input-invalid');
                        this.classList.remove('input-valid');
                        return;
                    }
                    
                    // Show loading indicator
                    const emailIcon = document.getElementById('emailCheckIcon');
                    emailIcon.innerHTML = '<div class="loading-spinner"></div>';
                    emailIcon.classList.remove('hidden');
                    
                    emailTimeout = setTimeout(() => {
                        checkEmailAvailability(value);
                    }, 500);
                });
            }
            
            // Phone validation with real-time check
            if (phone) {
                phone.addEventListener('input', function () {
                    const value = this.value.trim();
                    clearTimeout(phoneTimeout);
                    
                    if (value.length === 0) {
                        showFeedback(phoneFeedback, '', '');
                        this.classList.remove('input-valid', 'input-invalid');
                        document.getElementById('phoneCheckIcon').classList.add('hidden');
                        return;
                    }
                    
                    if (!isValidPhone(value)) {
                        showFeedback(phoneFeedback, 'Invalid Philippine phone number format', 'error');
                        this.classList.add('input-invalid');
                        this.classList.remove('input-valid');
                        return;
                    }
                    
                    // Show loading indicator
                    const phoneIcon = document.getElementById('phoneCheckIcon');
                    phoneIcon.innerHTML = '<div class="loading-spinner"></div>';
                    phoneIcon.classList.remove('hidden');
                    
                    phoneTimeout = setTimeout(() => {
                        checkPhoneAvailability(value);
                    }, 500);
                });
            }
            
            // Password validation
            if (password) {
                password.addEventListener('input', function () {
                    const value = this.value;
                    updatePasswordRequirements(value);
                    updatePasswordStrength(value);
                    
                    if (value.length === 0) {
                        showFeedback(passwordFeedback, '', '');
                        document.getElementById('passwordRequirements').classList.add('hidden');
                    } else {
                        document.getElementById('passwordRequirements').classList.remove('hidden');
                        validatePassword(value);
                    }
                    
                    // Check confirm password if not empty
                    if (confirmPassword && confirmPassword.value.trim() !== '') {
                        checkPasswordMatch();
                    }
                });
                
                password.addEventListener('focus', function () {
                    if (this.value.length > 0) {
                        document.getElementById('passwordRequirements').classList.remove('hidden');
                    }
                });
                
                password.addEventListener('blur', function () {
                    if (this.value.length === 0) {
                        document.getElementById('passwordRequirements').classList.add('hidden');
                    }
                });
            }
            
            // Confirm password validation
            if (confirmPassword) {
                confirmPassword.addEventListener('input', checkPasswordMatch);
            }
            
            // Terms validation
            if (terms) {
                terms.addEventListener('change', function () {
                    if (!this.checked) {
                        showFeedback(termsFeedback, 'You must agree to the terms', 'error');
                    } else {
                        showFeedback(termsFeedback, '', '');
                    }
                });
            }
            
            // Form submission validation
            document.getElementById('registerFormElement').addEventListener('submit', function (e) {
                let isValid = true;
                
                // Validate all fields
                if (!fullName.value.trim() || fullName.value.trim().length < 2 || !/^[A-Za-z\s]+$/.test(fullName.value.trim())) {
                    isValid = false;
                    fullName.classList.add('input-invalid');
                    showFeedback(nameFeedback, 'Please enter a valid name', 'error');
                }
                
                if (!isValidEmail(email.value.trim())) {
                    isValid = false;
                    email.classList.add('input-invalid');
                    showFeedback(emailFeedback, 'Please enter a valid email', 'error');
                }
                
                if (!isValidPhone(phone.value.trim())) {
                    isValid = false;
                    phone.classList.add('input-invalid');
                    showFeedback(phoneFeedback, 'Please enter a valid phone number', 'error');
                }
                
                if (!isPasswordValid(password.value)) {
                    isValid = false;
                    password.classList.add('input-invalid');
                    showFeedback(passwordFeedback, 'Password does not meet requirements', 'error');
                }
                
                if (password.value !== confirmPassword.value) {
                    isValid = false;
                    confirmPassword.classList.add('input-invalid');
                    showFeedback(confirmFeedback, 'Passwords do not match', 'error');
                }
                
                if (!terms.checked) {
                    isValid = false;
                    showFeedback(termsFeedback, 'You must agree to the terms', 'error');
                }
                
                if (!isValid) {
                    e.preventDefault();
                    Swal.fire({
                        title: 'Validation Error',
                        text: 'Please check all fields and try again.',
                        icon: 'error',
                        confirmButtonColor: '#d97706'
                    });
                } else {
                    registerBtn.disabled = true;
                    registerBtn.innerHTML = '<div class="loading-spinner mx-auto"></div> Creating account...';
                }
            });
            
            // ===== HELPER FUNCTIONS =====
            
            function isValidEmail(email) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                return emailRegex.test(email);
            }
            
            function isValidPhone(phone) {
                const phoneClean = phone.replace(/\D/g, '');
                return /^(63|0)[0-9]{10}$/.test(phoneClean);
            }
            
            function isPasswordValid(password) {
                return password.length >= 8 &&
                       /[A-Z]/.test(password) &&
                       /[a-z]/.test(password) &&
                       /\d/.test(password) &&
                       /[^A-Za-z0-9]/.test(password);
            }
            
            function validatePassword(password) {
                if (password.length >= 8 && /[A-Z]/.test(password) && /[a-z]/.test(password) && /\d/.test(password) && /[^A-Za-z0-9]/.test(password)) {
                    showFeedback(passwordFeedback, '✓ Strong password', 'success');
                    password.classList.add('input-valid');
                    password.classList.remove('input-invalid');
                } else {
                    showFeedback(passwordFeedback, 'Password does not meet requirements', 'error');
                    password.classList.add('input-invalid');
                    password.classList.remove('input-valid');
                }
            }
            
            function updatePasswordRequirements(password) {
                // Length check
                if (password.length >= 8) {
                    reqLength.innerHTML = '<i class="fas fa-check-circle text-green-500 mr-1 text-xs"></i> At least 8 characters';
                    reqLength.classList.remove('text-slate-400');
                    reqLength.classList.add('text-green-600');
                } else {
                    reqLength.innerHTML = '<i class="fas fa-circle mr-1 text-[8px]"></i> At least 8 characters';
                    reqLength.classList.remove('text-green-600');
                    reqLength.classList.add('text-slate-400');
                }
                
                // Uppercase check
                if (/[A-Z]/.test(password)) {
                    reqUppercase.innerHTML = '<i class="fas fa-check-circle text-green-500 mr-1 text-xs"></i> At least 1 uppercase letter';
                    reqUppercase.classList.remove('text-slate-400');
                    reqUppercase.classList.add('text-green-600');
                } else {
                    reqUppercase.innerHTML = '<i class="fas fa-circle mr-1 text-[8px]"></i> At least 1 uppercase letter';
                    reqUppercase.classList.remove('text-green-600');
                    reqUppercase.classList.add('text-slate-400');
                }
                
                // Lowercase check
                if (/[a-z]/.test(password)) {
                    reqLowercase.innerHTML = '<i class="fas fa-check-circle text-green-500 mr-1 text-xs"></i> At least 1 lowercase letter';
                    reqLowercase.classList.remove('text-slate-400');
                    reqLowercase.classList.add('text-green-600');
                } else {
                    reqLowercase.innerHTML = '<i class="fas fa-circle mr-1 text-[8px]"></i> At least 1 lowercase letter';
                    reqLowercase.classList.remove('text-green-600');
                    reqLowercase.classList.add('text-slate-400');
                }
                
                // Number check
                if (/\d/.test(password)) {
                    reqNumber.innerHTML = '<i class="fas fa-check-circle text-green-500 mr-1 text-xs"></i> At least 1 number';
                    reqNumber.classList.remove('text-slate-400');
                    reqNumber.classList.add('text-green-600');
                } else {
                    reqNumber.innerHTML = '<i class="fas fa-circle mr-1 text-[8px]"></i> At least 1 number';
                    reqNumber.classList.remove('text-green-600');
                    reqNumber.classList.add('text-slate-400');
                }
                
                // Special character check
                if (/[^A-Za-z0-9]/.test(password)) {
                    reqSpecial.innerHTML = '<i class="fas fa-check-circle text-green-500 mr-1 text-xs"></i> At least 1 special character';
                    reqSpecial.classList.remove('text-slate-400');
                    reqSpecial.classList.add('text-green-600');
                } else {
                    reqSpecial.innerHTML = '<i class="fas fa-circle mr-1 text-[8px]"></i> At least 1 special character';
                    reqSpecial.classList.remove('text-green-600');
                    reqSpecial.classList.add('text-slate-400');
                }
            }
            
            function updatePasswordStrength(password) {
                let strength = 0;
                let color = '';
                let text = '';
                
                if (password.length >= 8) strength++;
                if (password.length >= 10) strength++;
                if (/[A-Z]/.test(password)) strength++;
                if (/[0-9]/.test(password)) strength++;
                if (/[^A-Za-z0-9]/.test(password)) strength++;
                
                strength = Math.min(strength, 4);
                
                const colors = ['bg-red-500', 'bg-orange-500', 'bg-yellow-500', 'bg-blue-500', 'bg-green-500'];
                const texts = ['Very Weak', 'Weak', 'Fair', 'Good', 'Strong'];
                
                if (password.length === 0) {
                    strengthBar.style.width = '0';
                    strengthText.textContent = '';
                } else {
                    strengthBar.className = `h-full ${colors[strength]} transition-all duration-300`;
                    strengthBar.style.width = `${(strength + 1) * 20}%`;
                    strengthText.textContent = `Password strength: ${texts[strength]}`;
                }
            }
            
            function checkPasswordMatch() {
                if (confirmPassword.value.trim() === '') {
                    showFeedback(confirmFeedback, '', '');
                    confirmPassword.classList.remove('input-valid', 'input-invalid');
                } else if (password.value !== confirmPassword.value) {
                    showFeedback(confirmFeedback, 'Passwords do not match', 'error');
                    confirmPassword.classList.add('input-invalid');
                    confirmPassword.classList.remove('input-valid');
                } else {
                    showFeedback(confirmFeedback, '✓ Passwords match', 'success');
                    confirmPassword.classList.add('input-valid');
                    confirmPassword.classList.remove('input-invalid');
                }
            }
            
            async function checkEmailAvailability(email) {
                try {
                    const response = await fetch('../../controller/auth/check_email.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: new URLSearchParams({ email: email })
                    });
                    const data = await response.json();
                    
                    const emailIcon = document.getElementById('emailCheckIcon');
                    
                    if (data.exists) {
                        showFeedback(emailFeedback, 'Email already registered', 'error');
                        email.classList.add('input-invalid');
                        email.classList.remove('input-valid');
                        emailIcon.innerHTML = '<i class="fas fa-times-circle text-red-500"></i>';
                    } else {
                        showFeedback(emailFeedback, '✓ Email available', 'success');
                        email.classList.add('input-valid');
                        email.classList.remove('input-invalid');
                        emailIcon.innerHTML = '<i class="fas fa-check-circle text-green-500"></i>';
                    }
                } catch (error) {
                    console.error('Error checking email:', error);
                    document.getElementById('emailCheckIcon').classList.add('hidden');
                }
            }
            
            async function checkPhoneAvailability(phone) {
                try {
                    const phoneClean = phone.replace(/\D/g, '');
                    const response = await fetch('../../controller/auth/check_phone.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: new URLSearchParams({ phone: phoneClean })
                    });
                    const data = await response.json();
                    
                    const phoneIcon = document.getElementById('phoneCheckIcon');
                    
                    if (data.exists) {
                        showFeedback(phoneFeedback, 'Phone number already registered', 'error');
                        phone.classList.add('input-invalid');
                        phone.classList.remove('input-valid');
                        phoneIcon.innerHTML = '<i class="fas fa-times-circle text-red-500"></i>';
                    } else {
                        showFeedback(phoneFeedback, '✓ Phone number available', 'success');
                        phone.classList.add('input-valid');
                        phone.classList.remove('input-invalid');
                        phoneIcon.innerHTML = '<i class="fas fa-check-circle text-green-500"></i>';
                    }
                } catch (error) {
                    console.error('Error checking phone:', error);
                    document.getElementById('phoneCheckIcon').classList.add('hidden');
                }
            }
            
            function showFeedback(element, message, type) {
                if (!element) return;
                element.textContent = message;
                if (type === 'error') {
                    element.classList.add('text-red-600');
                    element.classList.remove('text-green-600');
                } else if (type === 'success') {
                    element.classList.add('text-green-600');
                    element.classList.remove('text-red-600');
                } else {
                    element.classList.remove('text-red-600', 'text-green-600');
                }
            }
            
            // Terms and Privacy Links
            document.getElementById('termsLink')?.addEventListener('click', (e) => {
                e.preventDefault();
                Swal.fire({
                    title: 'Terms of Service',
                    html: '<div class="text-left"><p>By using our service, you agree to our terms...</p></div>',
                    confirmButtonColor: '#d97706'
                });
            });
            
            document.getElementById('privacyLink')?.addEventListener('click', (e) => {
                e.preventDefault();
                Swal.fire({
                    title: 'Privacy Policy',
                    html: '<div class="text-left"><p>We value your privacy and protect your data...</p></div>',
                    confirmButtonColor: '#d97706'
                });
            });
        });
    </script>
</body>

</html>

<?php
// Clear old input after displaying
unset($_SESSION['error']);
unset($_SESSION['old']);
?>