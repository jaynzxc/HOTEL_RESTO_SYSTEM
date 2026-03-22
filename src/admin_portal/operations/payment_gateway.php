<!DOCTYPE html>
<html lang="en">

  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin · Payment Gateway</title>
    <!-- Tailwind via CDN + Font Awesome 6 -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
      /* exact same dropdown styles from index2.html */
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
    </style>
  </head>

  <body class="bg-white font-sans antialiased">

    <!-- APP CONTAINER: flex row (sidebar + main) -->
    <div class="min-h-screen flex flex-col lg:flex-row">

      <!-- ========== SIDEBAR (exact copy from index2.html) ========== -->
      <?php require_once '../components/admin_nav.php'; ?>
      <!-- ========== MAIN CONTENT (PAYMENT GATEWAY) ========== -->
      <main class="flex-1 p-5 lg:p-8 overflow-y-auto bg-white">

        <!-- header with title and date -->
        <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
          <div>
            <h1 class="text-2xl lg:text-3xl font-light text-slate-800">Payment Gateway</h1>
            <p class="text-sm text-slate-500 mt-0.5">configure and monitor payment providers, view transaction logs</p>
          </div>
          <div class="flex gap-3 text-sm">
            <span class="bg-white border rounded-full px-4 py-2 flex items-center gap-2 shadow-sm"><i
                class="fas fa-calendar text-slate-400"></i> May 21, 2025</span>
            <span class="bg-white border rounded-full px-4 py-2 shadow-sm"><i class="fas fa-bell"></i></span>
          </div>
        </div>

        <!-- ===== STATS CARDS ===== -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
          <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs text-slate-500">Gateway status</p>
            <p class="text-2xl font-semibold text-green-600">Active</p>
          </div>
          <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs text-slate-500">Success rate</p>
            <p class="text-2xl font-semibold">98.5%</p>
          </div>
          <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs text-slate-500">Today's volume</p>
            <p class="text-2xl font-semibold">₱124.5k</p>
          </div>
          <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs text-slate-500">Failed today</p>
            <p class="text-2xl font-semibold text-rose-600">3</p>
          </div>
          <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs text-slate-500">Avg processing</p>
            <p class="text-2xl font-semibold">1.2s</p>
          </div>
        </div>

        <!-- ===== CONNECTED GATEWAYS ===== -->
        <h2 class="font-semibold text-lg mb-3 flex items-center gap-2"><i class="fa-solid fa-plug text-amber-600"></i>
          connected gateways</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-8">
          <!-- GCash -->
          <div class="bg-white rounded-2xl border border-slate-200 p-5 hover:shadow-md transition">
            <div class="flex items-center gap-3 mb-3">
              <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 text-xl"><i
                  class="fa-brands fa-gcash"></i></div>
              <div>
                <h3 class="font-semibold">GCash</h3>
                <p class="text-xs text-slate-500">PayMaya Philippines</p>
              </div>
            </div>
            <div class="flex justify-between text-sm mb-2">
              <span class="text-slate-500">Status</span>
              <span class="bg-green-100 text-green-700 px-2 py-0.5 rounded-full text-xs">connected</span>
            </div>
            <div class="flex justify-between text-sm mb-2">
              <span class="text-slate-500">Merchant ID</span>
              <span class="font-mono text-xs">GCH-1234-5678</span>
            </div>
            <div class="flex justify-between text-sm">
              <span class="text-slate-500">Success rate</span>
              <span>99.2%</span>
            </div>
            <button
              class="w-full border border-amber-600 text-amber-700 rounded-xl py-2 text-sm mt-4 hover:bg-amber-50">configure</button>
          </div>

          <!-- Credit Card (Stripe) -->
          <div class="bg-white rounded-2xl border border-slate-200 p-5 hover:shadow-md transition">
            <div class="flex items-center gap-3 mb-3">
              <div
                class="h-10 w-10 rounded-full bg-purple-100 flex items-center justify-center text-purple-600 text-xl"><i
                  class="fa-brands fa-stripe"></i></div>
              <div>
                <h3 class="font-semibold">Stripe</h3>
                <p class="text-xs text-slate-500">Credit / Debit cards</p>
              </div>
            </div>
            <div class="flex justify-between text-sm mb-2">
              <span class="text-slate-500">Status</span>
              <span class="bg-green-100 text-green-700 px-2 py-0.5 rounded-full text-xs">connected</span>
            </div>
            <div class="flex justify-between text-sm mb-2">
              <span class="text-slate-500">Account</span>
              <span class="font-mono text-xs">acct_1234****</span>
            </div>
            <div class="flex justify-between text-sm">
              <span class="text-slate-500">Success rate</span>
              <span>98.7%</span>
            </div>
            <button
              class="w-full border border-amber-600 text-amber-700 rounded-xl py-2 text-sm mt-4 hover:bg-amber-50">configure</button>
          </div>

          <!-- PayPal -->
          <div class="bg-white rounded-2xl border border-slate-200 p-5 hover:shadow-md transition">
            <div class="flex items-center gap-3 mb-3">
              <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 text-xl"><i
                  class="fa-brands fa-paypal"></i></div>
              <div>
                <h3 class="font-semibold">PayPal</h3>
                <p class="text-xs text-slate-500">International</p>
              </div>
            </div>
            <div class="flex justify-between text-sm mb-2">
              <span class="text-slate-500">Status</span>
              <span class="bg-green-100 text-green-700 px-2 py-0.5 rounded-full text-xs">connected</span>
            </div>
            <div class="flex justify-between text-sm mb-2">
              <span class="text-slate-500">Email</span>
              <span class="text-xs">payments@lucas.stay</span>
            </div>
            <div class="flex justify-between text-sm">
              <span class="text-slate-500">Success rate</span>
              <span>97.5%</span>
            </div>
            <button
              class="w-full border border-amber-600 text-amber-700 rounded-xl py-2 text-sm mt-4 hover:bg-amber-50">configure</button>
          </div>
        </div>

        <!-- ===== AVAILABLE GATEWAYS TO ADD ===== -->
        <h2 class="font-semibold text-lg mb-3 flex items-center gap-2"><i class="fas fa-plus text-amber-600"></i>
          available gateways</h2>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
          <div
            class="border border-dashed border-slate-300 rounded-2xl p-4 text-center hover:bg-slate-50 cursor-pointer">
            <i class="fa-brands fa-cc-visa text-2xl text-slate-400 mb-1"></i>
            <p class="text-sm font-medium">Visa Direct</p>
            <p class="text-xs text-slate-400">connect</p>
          </div>
          <div
            class="border border-dashed border-slate-300 rounded-2xl p-4 text-center hover:bg-slate-50 cursor-pointer">
            <i class="fa-brands fa-cc-mastercard text-2xl text-slate-400 mb-1"></i>
            <p class="text-sm font-medium">Mastercard</p>
            <p class="text-xs text-slate-400">connect</p>
          </div>
          <div
            class="border border-dashed border-slate-300 rounded-2xl p-4 text-center hover:bg-slate-50 cursor-pointer">
            <i class="fa-brands fa-apple-pay text-2xl text-slate-400 mb-1"></i>
            <p class="text-sm font-medium">Apple Pay</p>
            <p class="text-xs text-slate-400">connect</p>
          </div>
          <div
            class="border border-dashed border-slate-300 rounded-2xl p-4 text-center hover:bg-slate-50 cursor-pointer">
            <i class="fa-brands fa-google-pay text-2xl text-slate-400 mb-1"></i>
            <p class="text-sm font-medium">Google Pay</p>
            <p class="text-xs text-slate-400">connect</p>
          </div>
        </div>

        <!-- ===== RECENT GATEWAY TRANSACTIONS ===== -->
        <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden mb-8">
          <div class="p-4 border-b border-slate-200 bg-slate-50 flex items-center justify-between">
            <h2 class="font-semibold flex items-center gap-2"><i class="fas fa-rectangle-list text-amber-600"></i>
              recent gateway transactions</h2>
            <button class="text-sm text-amber-700 border border-amber-600 px-3 py-1 rounded-lg hover:bg-amber-50">view
              all</button>
          </div>
          <div class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead class="text-slate-500 text-xs border-b">
                <tr>
                  <td class="p-3">Transaction ID</td>
                  <td class="p-3">Gateway</td>
                  <td class="p-3">Amount</td>
                  <td class="p-3">Status</td>
                  <td class="p-3">Time</td>
                  <td class="p-3">Response</td>
                </tr>
              </thead>
              <tbody class="divide-y">
                <tr>
                  <td class="p-3 font-mono text-xs">TXN-1234-5678</td>
                  <td class="p-3">GCash</td>
                  <td class="p-3">₱4,200</td>
                  <td class="p-3"><span
                      class="bg-green-100 text-green-700 px-2 py-0.5 rounded-full text-xs">success</span></td>
                  <td class="p-3">2 min ago</td>
                  <td class="p-3 text-xs text-green-600">approved</td>
                </tr>
                <tr>
                  <td class="p-3 font-mono text-xs">TXN-1234-5679</td>
                  <td class="p-3">Stripe</td>
                  <td class="p-3">₱6,900</td>
                  <td class="p-3"><span
                      class="bg-green-100 text-green-700 px-2 py-0.5 rounded-full text-xs">success</span></td>
                  <td class="p-3">15 min ago</td>
                  <td class="p-3 text-xs text-green-600">approved</td>
                </tr>
                <tr>
                  <td class="p-3 font-mono text-xs">TXN-1234-5680</td>
                  <td class="p-3">PayPal</td>
                  <td class="p-3">₱3,500</td>
                  <td class="p-3"><span class="bg-rose-100 text-rose-700 px-2 py-0.5 rounded-full text-xs">failed</span>
                  </td>
                  <td class="p-3">1 hour ago</td>
                  <td class="p-3 text-xs text-rose-600">insufficient funds</td>
                </tr>
                <tr>
                  <td class="p-3 font-mono text-xs">TXN-1234-5681</td>
                  <td class="p-3">GCash</td>
                  <td class="p-3">₱8,500</td>
                  <td class="p-3"><span
                      class="bg-green-100 text-green-700 px-2 py-0.5 rounded-full text-xs">success</span></td>
                  <td class="p-3">2 hours ago</td>
                  <td class="p-3 text-xs text-green-600">approved</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- ===== BOTTOM: WEBHOOKS & API KEYS ===== -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

          <!-- webhook endpoints -->
          <div class="bg-white rounded-2xl border border-slate-200 p-5">
            <h2 class="font-semibold text-lg flex items-center gap-2 mb-3"><i
                class="fas fa-circle-nodes text-amber-600"></i> webhook endpoints</h2>
            <div class="space-y-2 text-sm">
              <div class="flex justify-between items-center bg-slate-50 p-2 rounded-lg">
                <span class="font-mono text-xs">https://api.hnr/payments/gcash/webhook</span>
                <span class="bg-green-100 text-green-700 text-xs px-2 py-0.5 rounded-full">active</span>
              </div>
              <div class="flex justify-between items-center bg-slate-50 p-2 rounded-lg">
                <span class="font-mono text-xs">https://api.hnr/payments/stripe/webhook</span>
                <span class="bg-green-100 text-green-700 text-xs px-2 py-0.5 rounded-full">active</span>
              </div>
            </div>
            <button
              class="border border-amber-600 text-amber-700 rounded-xl py-2 text-sm mt-4 hover:bg-amber-50 w-full">add
              webhook</button>
          </div>

          <!-- api keys -->
          <div class="bg-amber-50 border border-amber-200 rounded-2xl p-5">
            <h3 class="font-semibold flex items-center gap-2 mb-3"><i class="fa-solid fa-key text-amber-600"></i> API
              keys (sandbox)</h3>
            <div class="space-y-2">
              <div class="flex justify-between items-center bg-white p-2 rounded-lg border">
                <span class="font-mono text-xs">pk_live_1234...abcd</span>
                <button class="text-amber-600 text-xs hover:underline">copy</button>
              </div>
              <div class="flex justify-between items-center bg-white p-2 rounded-lg border">
                <span class="font-mono text-xs">sk_live_5678...wxyz</span>
                <button class="text-amber-600 text-xs hover:underline">copy</button>
              </div>
            </div>
            <button
              class="border border-amber-600 text-amber-700 rounded-xl py-2 text-sm mt-4 hover:bg-amber-100 w-full">regenerate</button>
          </div>
        </div>
      </main>
    </div>
  </body>

</html>