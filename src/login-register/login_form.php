<?php
session_start();
$_SESSION['error'] ??= [];
$_SESSION['success'] ??= [];
$_SESSION['old'] ??= [];
?>
<!DOCTYPE html>
<html lang="en">

  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login · Customer Portal</title>
    <!-- Tailwind CSS -->
    <link href="/src/output.css" rel="stylesheet">
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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
      <div class="flex mb-2">
        <h1 class="flex-1 pb-3 text-center text-3xl font-bold text-amber-600 transition">Log In</h1>
      </div>

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

      <!-- LOGIN FORM -->
      <div id="loginForm">
        <form action="../../controller/auth/login.php" method="POST">
          <div class="space-y-4">
            <!-- Email -->
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">Email</label>
              <div class="relative">
                <i class="fas fa-envelope absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                <input type="email" name="email" id="email"
                  value="<?php echo htmlspecialchars($_SESSION['old']['email'] ?? ''); ?>" placeholder="you@example.com"
                  class="w-full border border-slate-200 rounded-xl pl-9 pr-4 py-2.5 text-sm focus:ring-1 focus:ring-amber-500 outline-none"
                  required>
              </div>
            </div>

            <!-- Password -->
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">Password</label>
              <div class="relative">
                <i class="fa-solid fa-lock absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                <input type="password" name="password" id="password" placeholder="••••••••"
                  class="w-full border border-slate-200 rounded-xl pl-9 pr-10 py-2.5 text-sm focus:ring-1 focus:ring-amber-500 outline-none"
                  required>
                <button type="button"
                  class="toggle-password absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                  <i class="fas fa-eye-slash text-sm"></i>
                </button>
              </div>
            </div>

            <!-- Remember Me & Forgot Password -->
            <div class="flex items-center justify-between text-sm">
              <label class="flex items-center gap-2">
                <input type="checkbox" name="remember_me" id="remember_me"
                  class="rounded text-amber-600 focus:ring-amber-500">
                <span class="text-slate-600">Remember me</span>
              </label>
              <a href="#" id="forgotPassword" class="text-amber-600 hover:underline">Forgot password?</a>
            </div>

            <!-- Submit Button -->
            <button type="submit" id="loginBtn"
              class="w-full bg-amber-600 hover:bg-amber-700 text-white py-2.5 rounded-xl font-medium transition shadow-sm">
              Sign in
            </button>
          </div>
        </form>

        <!-- Security Info -->
        <div class="mt-4 text-center">
          <p class="text-xs text-slate-400">
            <i class="fas fa-shield-alt mr-1"></i>
            Your login activity is monitored for security
          </p>
        </div>

        <!-- Register Link -->
        <p class="text-center text-sm text-slate-500 mt-5">
          Don't have an account? <a href="./register_form.php"
            class="text-amber-600 font-medium hover:underline">Register</a>
        </p>
      </div>
    </div>

    <script>
      document.addEventListener('DOMContentLoaded', function () {
        // Password visibility toggle
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

        // Check for remembered email from cookie
        <?php if (isset($_COOKIE['user_email'])): ?>
          document.getElementById('email').value = '<?php echo $_COOKIE['user_email']; ?>';
          document.getElementById('remember_me').checked = true;
        <?php endif; ?>

        // Forgot password link
        document.getElementById('forgotPassword').addEventListener('click', function (e) {
          e.preventDefault();
          const email = document.getElementById('email').value.trim();

          if (email && isValidEmail(email)) {
            window.location.href = 'forgot-password.php?email=' + encodeURIComponent(email);
          } else {
            Swal.fire({
              title: 'Email Required',
              text: 'Please enter your email address first',
              icon: 'info',
              confirmButtonColor: '#d97706'
            });
            document.getElementById('email').focus();
          }
        });

        // Loading state on form submit
        const loginForm = document.querySelector('form');
        const loginBtn = document.getElementById('loginBtn');

        loginForm.addEventListener('submit', function () {
          loginBtn.disabled = true;
          loginBtn.classList.add('opacity-50', 'cursor-not-allowed');
          loginBtn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin mr-2"></i>Signing in...';
        });

        // Email validation helper
        function isValidEmail(email) {
          const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
          return emailRegex.test(email);
        }
      });
    </script>
  </body>

</html>

<?php
unset($_SESSION['old']);
?>