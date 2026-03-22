<!DOCTYPE html>
<html lang="en">

  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin · Online Ordering Integration</title>
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

    <!-- ========== MAIN CONTENT (ONLINE ORDERING INTEGRATION) ========== -->
    <main class="flex-1 p-5 lg:p-8 overflow-y-auto bg-white">

      <!-- header with title and date -->
      <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <div>
          <h1 class="text-2xl lg:text-3xl font-light text-slate-800">Online Ordering Integration</h1>
          <p class="text-sm text-slate-500 mt-0.5">manage third-party delivery platforms and online ordering channels</p>
        </div>
        <div class="flex gap-3 text-sm">
          <span class="bg-white border rounded-full px-4 py-2 flex items-center gap-2 shadow-sm"><i class="fas fa-calendar text-slate-400"></i> May 21, 2025</span>
          <span class="bg-white border rounded-full px-4 py-2 shadow-sm"><i class="fas fa-bell"></i></span>
        </div>
      </div>

      <!-- ===== STATS CARDS ===== -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
          <p class="text-xs text-slate-500">Connected platforms</p>
          <p class="text-2xl font-semibold">4</p>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
          <p class="text-xs text-slate-500">Today's online orders</p>
          <p class="text-2xl font-semibold">42</p>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
          <p class="text-xs text-slate-500">Revenue (online)</p>
          <p class="text-2xl font-semibold">₱38,450</p>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
          <p class="text-xs text-slate-500">Commission fees</p>
          <p class="text-2xl font-semibold text-amber-600">₱3,845</p>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
          <p class="text-xs text-slate-500">Avg. order value</p>
          <p class="text-2xl font-semibold">₱915</p>
        </div>
      </div>

      <!-- ===== CONNECTED PLATFORMS ===== -->
      <h2 class="font-semibold text-lg mb-3 flex items-center gap-2"><i class="fa-solid fa-plug text-amber-600"></i> connected platforms</h2>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
        <!-- Foodpanda -->
        <div class="bg-white rounded-2xl border border-slate-200 p-5 hover:shadow-md transition">
          <div class="flex items-center gap-3 mb-3">
            <div class="h-10 w-10 rounded-full bg-pink-100 flex items-center justify-center text-pink-600 text-xl"><i class="fa-solid fa-bag-shopping"></i></div>
            <div>
              <h3 class="font-semibold">Foodpanda</h3>
              <p class="text-xs text-slate-500">Delivery</p>
            </div>
          </div>
          <div class="flex justify-between text-sm mb-2">
            <span class="text-slate-500">Status</span>
            <span class="bg-green-100 text-green-700 px-2 py-0.5 rounded-full text-xs">connected</span>
          </div>
          <div class="flex justify-between text-sm mb-2">
            <span class="text-slate-500">Commission</span>
            <span>25%</span>
          </div>
          <div class="flex justify-between text-sm">
            <span class="text-slate-500">Today's orders</span>
            <span>18</span>
          </div>
          <button class="w-full border border-amber-600 text-amber-700 rounded-xl py-2 text-sm mt-4 hover:bg-amber-50">manage</button>
        </div>

        <!-- GrabFood -->
        <div class="bg-white rounded-2xl border border-slate-200 p-5 hover:shadow-md transition">
          <div class="flex items-center gap-3 mb-3">
            <div class="h-10 w-10 rounded-full bg-green-100 flex items-center justify-center text-green-600 text-xl"><i class="fa-solid fa-motorcycle"></i></div>
            <div>
              <h3 class="font-semibold">GrabFood</h3>
              <p class="text-xs text-slate-500">Delivery</p>
            </div>
          </div>
          <div class="flex justify-between text-sm mb-2">
            <span class="text-slate-500">Status</span>
            <span class="bg-green-100 text-green-700 px-2 py-0.5 rounded-full text-xs">connected</span>
          </div>
          <div class="flex justify-between text-sm mb-2">
            <span class="text-slate-500">Commission</span>
            <span>22%</span>
          </div>
          <div class="flex justify-between text-sm">
            <span class="text-slate-500">Today's orders</span>
            <span>15</span>
          </div>
          <button class="w-full border border-amber-600 text-amber-700 rounded-xl py-2 text-sm mt-4 hover:bg-amber-50">manage</button>
        </div>

        <!-- Lalamove -->
        <div class="bg-white rounded-2xl border border-slate-200 p-5 hover:shadow-md transition">
          <div class="flex items-center gap-3 mb-3">
            <div class="h-10 w-10 rounded-full bg-yellow-100 flex items-center justify-center text-yellow-600 text-xl"><i class="fa-solid fa-truck"></i></div>
            <div>
              <h3 class="font-semibold">Lalamove</h3>
              <p class="text-xs text-slate-500">On-demand delivery</p>
            </div>
          </div>
          <div class="flex justify-between text-sm mb-2">
            <span class="text-slate-500">Status</span>
            <span class="bg-green-100 text-green-700 px-2 py-0.5 rounded-full text-xs">connected</span>
          </div>
          <div class="flex justify-between text-sm mb-2">
            <span class="text-slate-500">Integration</span>
            <span>API</span>
          </div>
          <div class="flex justify-between text-sm">
            <span class="text-slate-500">Deliveries today</span>
            <span>9</span>
          </div>
          <button class="w-full border border-amber-600 text-amber-700 rounded-xl py-2 text-sm mt-4 hover:bg-amber-50">manage</button>
        </div>

        <!-- In-house website -->
        <div class="bg-white rounded-2xl border border-slate-200 p-5 hover:shadow-md transition">
          <div class="flex items-center gap-3 mb-3">
            <div class="h-10 w-10 rounded-full bg-amber-100 flex items-center justify-center text-amber-600 text-xl"><i class="fa-solid fa-globe"></i></div>
            <div>
              <h3 class="font-semibold">HNR Website</h3>
              <p class="text-xs text-slate-500">Direct orders</p>
            </div>
          </div>
          <div class="flex justify-between text-sm mb-2">
            <span class="text-slate-500">Status</span>
            <span class="bg-green-100 text-green-700 px-2 py-0.5 rounded-full text-xs">active</span>
          </div>
          <div class="flex justify-between text-sm mb-2">
            <span class="text-slate-500">Commission</span>
            <span>0%</span>
          </div>
          <div class="flex justify-between text-sm">
            <span class="text-slate-500">Today's orders</span>
            <span>8</span>
          </div>
          <button class="w-full border border-amber-600 text-amber-700 rounded-xl py-2 text-sm mt-4 hover:bg-amber-50">settings</button>
        </div>
      </div>

      <!-- ===== AVAILABLE INTEGRATIONS ===== -->
      <h2 class="font-semibold text-lg mb-3 flex items-center gap-2"><i class="fas fa-plus text-amber-600"></i> available integrations</h2>
      <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-8">
        <div class="border border-dashed border-slate-300 rounded-2xl p-4 text-center hover:bg-slate-50 cursor-pointer">
          <i class="fa-solid fa-utensils text-2xl text-slate-400 mb-1"></i>
          <p class="text-sm font-medium">GoFood</p>
          <p class="text-xs text-slate-400">connect</p>
        </div>
        <div class="border border-dashed border-slate-300 rounded-2xl p-4 text-center hover:bg-slate-50 cursor-pointer">
          <i class="fa-solid fa-bicycle text-2xl text-slate-400 mb-1"></i>
          <p class="text-sm font-medium">Deliveroo</p>
          <p class="text-xs text-slate-400">connect</p>
        </div>
        <div class="border border-dashed border-slate-300 rounded-2xl p-4 text-center hover:bg-slate-50 cursor-pointer">
          <i class="fa-solid fa-shop text-2xl text-slate-400 mb-1"></i>
          <p class="text-sm font-medium">ShopeeFood</p>
          <p class="text-xs text-slate-400">connect</p>
        </div>
        <div class="border border-dashed border-slate-300 rounded-2xl p-4 text-center hover:bg-slate-50 cursor-pointer">
          <i class="fa-brands fa-square-whatsapp text-2xl text-slate-400 mb-1"></i>
          <p class="text-sm font-medium">WhatsApp</p>
          <p class="text-xs text-slate-400">connect</p>
        </div>
        <div class="border border-dashed border-slate-300 rounded-2xl p-4 text-center hover:bg-slate-50 cursor-pointer">
          <i class="fa-brands fa-facebook-messenger text-2xl text-slate-400 mb-1"></i>
          <p class="text-sm font-medium">Messenger</p>
          <p class="text-xs text-slate-400">connect</p>
        </div>
      </div>

      <!-- ===== RECENT ONLINE ORDERS ===== -->
      <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden mb-8">
        <div class="p-4 border-b border-slate-200 bg-slate-50 flex items-center justify-between">
          <h2 class="font-semibold flex items-center gap-2"><i class="fas fa-rectangle-list text-amber-600"></i> recent online orders</h2>
          <button class="text-sm text-amber-700 border border-amber-600 px-3 py-1 rounded-lg hover:bg-amber-50">sync all</button>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead class="text-slate-500 text-xs border-b">
              <tr>
                <td class="p-3">Order #</td>
                <td class="p-3">Platform</td>
                <td class="p-3">Customer</td>
                <td class="p-3">Items</td>
                <td class="p-3">Total</td>
                <td class="p-3">Status</td>
                <td class="p-3">Time</td>
              </tr>
            </thead>
            <tbody class="divide-y">
              <tr>
                <td class="p-3 font-medium">FP-12345</td>
                <td class="p-3">Foodpanda</td>
                <td class="p-3">John D.</td>
                <td class="p-3">3 items</td>
                <td class="p-3">₱850</td>
                <td class="p-3"><span class="bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full text-xs">preparing</span></td>
                <td class="p-3">10 min ago</td>
              </tr>
              <tr>
                <td class="p-3 font-medium">GRAB-7890</td>
                <td class="p-3">GrabFood</td>
                <td class="p-3">Maria S.</td>
                <td class="p-3">2 items</td>
                <td class="p-3">₱540</td>
                <td class="p-3"><span class="bg-green-100 text-green-700 px-2 py-0.5 rounded-full text-xs">delivered</span></td>
                <td class="p-3">25 min ago</td>
              </tr>
              <tr>
                <td class="p-3 font-medium">WEB-001</td>
                <td class="p-3">Website</td>
                <td class="p-3">Anna R.</td>
                <td class="p-3">4 items</td>
                <td class="p-3">₱1,250</td>
                <td class="p-3"><span class="bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full text-xs">preparing</span></td>
                <td class="p-3">35 min ago</td>
              </tr>
              <tr>
                <td class="p-3 font-medium">LALA-456</td>
                <td class="p-3">Lalamove</td>
                <td class="p-3">Robert T.</td>
                <td class="p-3">2 items</td>
                <td class="p-3">₱390</td>
                <td class="p-3"><span class="bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full text-xs">picked up</span></td>
                <td class="p-3">50 min ago</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- ===== BOTTOM: API SETTINGS & COMMISSION SUMMARY ===== -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- api settings -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 p-5">
          <h2 class="font-semibold text-lg flex items-center gap-2 mb-3"><i class="fa-solid fa-code text-amber-600"></i> API & webhook settings</h2>
          <div class="space-y-3">
            <div class="flex justify-between items-center bg-slate-50 p-3 rounded-lg">
              <span class="font-mono text-xs">https://api.hnr/orders/webhook/foodpanda</span>
              <span class="bg-green-100 text-green-700 text-xs px-2 py-0.5 rounded-full">active</span>
            </div>
            <div class="flex justify-between items-center bg-slate-50 p-3 rounded-lg">
              <span class="font-mono text-xs">https://api.hnr/orders/webhook/grab</span>
              <span class="bg-green-100 text-green-700 text-xs px-2 py-0.5 rounded-full">active</span>
            </div>
            <div class="flex justify-between items-center bg-slate-50 p-3 rounded-lg">
              <span class="font-mono text-xs">sk_live_integration_8f7d3a1b...</span>
              <button class="text-amber-600 text-xs hover:underline">copy</button>
            </div>
          </div>
        </div>

        <!-- commission summary -->
        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-5">
          <h3 class="font-semibold flex items-center gap-2 mb-3"><i class="fas fa-chart-pie text-amber-600"></i> commission summary</h3>
          <ul class="space-y-2">
            <li class="flex justify-between items-center">
              <span>Foodpanda (25%)</span>
              <span>₱2,125</span>
            </li>
            <li class="flex justify-between items-center">
              <span>GrabFood (22%)</span>
              <span>₱1,188</span>
            </li>
            <li class="flex justify-between items-center">
              <span>Lalamove (variable)</span>
              <span>₱532</span>
            </li>
            <li class="border-t pt-2 mt-2 flex justify-between font-semibold">
              <span>Total fees</span>
              <span>₱3,845</span>
            </li>
          </ul>
        </div>
      </div>
    </main>
  </div>
</body>
</html>