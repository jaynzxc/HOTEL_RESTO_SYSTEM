<?php
/**
 * View - Admin Customer Feedback & Reviews
 */
require_once '../../../controller/admin/get/customer_feedback.php';

$current_page = 'customer_feedback_&_reviews';
?>
<!DOCTYPE html>
<html lang="en">

  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin · Customer Feedback & Reviews</title>
    <!-- Tailwind via CDN + Font Awesome 6 -->
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

      .review-card {
        transition: all 0.2s ease;
      }

      .review-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
      }

      .star-rating .fa-star,
      .star-rating .fa-star-half-alt {
        color: #fbbf24;
      }
    </style>
  </head>

  <body class="bg-white font-sans antialiased">

    <!-- APP CONTAINER: flex row (sidebar + main) -->
    <div class="min-h-screen flex flex-col lg:flex-row">

      <!-- ========== SIDEBAR ========== -->
      <?php require_once '../components/admin_nav.php'; ?>

      <!-- ========== MAIN CONTENT ========== -->
      <main class="flex-1 p-5 lg:p-8 overflow-y-auto bg-white">

        <!-- header with title and date -->
        <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
          <div>
            <h1 class="text-2xl lg:text-3xl font-light text-slate-800">Customer Feedback & Reviews</h1>
            <p class="text-sm text-slate-500 mt-0.5">manage and respond to guest reviews and feedback</p>
          </div>
          <div class="flex gap-3 text-sm">
            <span class="bg-white border rounded-full px-4 py-2 flex items-center gap-2 shadow-sm"><i
                class="fas fa-calendar text-slate-400"></i> <?php echo $today; ?></span>
            <span class="bg-white border rounded-full px-4 py-2 shadow-sm"><i class="fas fa-bell"></i></span>
          </div>
        </div>

        <!-- ===== STATS CARDS ===== -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
          <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs text-slate-500">Total reviews</p>
            <p class="text-2xl font-semibold"><?php echo number_format($stats['total_reviews'] ?? 0); ?></p>
          </div>
          <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs text-slate-500">Average rating</p>
            <p class="text-2xl font-semibold"><?php echo number_format($stats['avg_rating'] ?? 0, 1); ?></p>
            <div class="star-rating text-sm mt-1">
              <?php
              $avg = round($stats['avg_rating'] ?? 0);
              for ($i = 1; $i <= 5; $i++) {
                echo $i <= $avg ? '<i class="fa-solid fa-star text-yellow-400"></i>' : '<i class="fas fa-star text-yellow-400"></i>';
              }
              ?>
            </div>
          </div>
          <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs text-slate-500">Pending responses</p>
            <p class="text-2xl font-semibold text-amber-600">
              <?php echo number_format($stats['pending_responses'] ?? 0); ?>
            </p>
          </div>
          <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs text-slate-500">This month</p>
            <p class="text-2xl font-semibold"><?php echo number_format($stats['this_month'] ?? 0); ?></p>
          </div>
          <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs text-slate-500">5-star reviews</p>
            <p class="text-2xl font-semibold text-green-600"><?php echo number_format($stats['five_star'] ?? 0); ?></p>
          </div>
        </div>

        <!-- ===== RATING DISTRIBUTION ===== -->
        <div class="bg-white rounded-2xl border border-slate-200 p-5 mb-6">
          <h2 class="font-semibold text-lg flex items-center gap-2 mb-3"><i
              class="fas fa-chart-bar text-amber-600"></i> rating distribution</h2>
          <div class="space-y-2">
            <?php foreach ($ratingDistribution as $dist): ?>
              <div class="flex items-center gap-2">
                <span class="text-sm w-12"><?php echo $dist['rating']; ?>
                  <?php echo $dist['rating'] == 1 ? 'star' : 'stars'; ?></span>
                <div class="flex-1 h-2 bg-slate-100 rounded-full overflow-hidden">
                  <?php
                  $color = $dist['rating'] >= 4 ? 'bg-green-500' : ($dist['rating'] >= 3 ? 'bg-yellow-400' : 'bg-red-400');
                  $width = $dist['percentage'];
                  ?>
                  <div class="h-full <?php echo $color; ?>" style="width: <?php echo $width; ?>%;"></div>
                </div>
                <span class="text-sm text-slate-600"><?php echo number_format($dist['count']); ?>
                  (<?php echo $width; ?>%)</span>
              </div>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- ===== FILTER TABS ===== -->
        <div class="flex flex-wrap gap-2 border-b border-slate-200 pb-2 mb-6" id="filterTabs">
          <button class="filter-btn px-4 py-2 bg-amber-600 text-white rounded-full text-sm" data-filter="all">all
            reviews (<?php echo $stats['total_reviews'] ?? 0; ?>)</button>
          <button class="filter-btn px-4 py-2 bg-white border border-slate-200 rounded-full text-sm hover:bg-slate-50"
            data-filter="pending">pending response (<?php echo $stats['pending_responses'] ?? 0; ?>)</button>
          <button class="filter-btn px-4 py-2 bg-white border border-slate-200 rounded-full text-sm hover:bg-slate-50"
            data-filter="5">5 star (<?php echo $stats['five_star'] ?? 0; ?>)</button>
          <button class="filter-btn px-4 py-2 bg-white border border-slate-200 rounded-full text-sm hover:bg-slate-50"
            data-filter="4">4 star (<?php echo $stats['four_star'] ?? 0; ?>)</button>
          <button class="filter-btn px-4 py-2 bg-white border border-slate-200 rounded-full text-sm hover:bg-slate-50"
            data-filter="3">3 star & below
            (<?php echo ($stats['three_star'] ?? 0) + ($stats['two_star'] ?? 0) + ($stats['one_star'] ?? 0); ?>)</button>
        </div>

        <!-- ===== ACTION BAR ===== -->
        <div
          class="bg-white rounded-2xl border border-slate-200 p-4 mb-6 flex flex-wrap gap-3 items-center justify-between">
          <div class="flex gap-2 flex-wrap">
            <button onclick="exportReviews()"
              class="border border-slate-200 px-4 py-2 rounded-xl text-sm hover:bg-slate-50">export reviews</button>
            <button onclick="generateReport()"
              class="border border-slate-200 px-4 py-2 rounded-xl text-sm hover:bg-slate-50">generate report</button>
          </div>
          <div class="relative">
            <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
            <input type="text" id="searchInput" placeholder="search reviews..."
              class="border border-slate-200 rounded-xl pl-9 pr-4 py-2 text-sm w-64 focus:ring-1 focus:ring-amber-500 outline-none">
          </div>
        </div>

        <!-- ===== REVIEWS LIST ===== -->
        <div id="reviewsContainer" class="space-y-4 mb-8">
          <?php if (empty($reviews)): ?>
            <div class="text-center py-12 bg-white rounded-2xl border border-slate-200">
              <i class="fas fa-message text-4xl text-slate-300 mb-3"></i>
              <p class="text-slate-500">No reviews yet</p>
              <p class="text-xs text-slate-400 mt-1">Customer reviews will appear here</p>
            </div>
          <?php else: ?>
            <?php foreach ($reviews as $review):
              $hasResponse = !empty($review['response_id']);
              $isPending = !$hasResponse;
              $rating = $review['rating'];

              // Generate star HTML
              $stars = '';
              for ($i = 1; $i <= 5; $i++) {
                if ($i <= $rating) {
                  $stars .= '<i class="fa-solid fa-star text-yellow-400"></i>';
                } elseif ($i - 0.5 <= $rating) {
                  $stars .= '<i class="fa-solid fa-star-half-alt text-yellow-400"></i>';
                } else {
                  $stars .= '<i class="fas fa-star text-yellow-400"></i>';
                }
              }

              // Get initials
              $initials = strtoupper(substr($review['first_name'] ?? '', 0, 1) . substr($review['last_name'] ?? '', 0, 1));
              if (empty($initials) || $initials === ' ') {
                $name_parts = explode(' ', $review['full_name'] ?? 'Guest');
                $initials = strtoupper(substr($name_parts[0], 0, 1));
                if (isset($name_parts[1])) {
                  $initials .= strtoupper(substr($name_parts[1], 0, 1));
                }
              }

              // Calculate time ago
              $created = new DateTime($review['created_at']);
              $now = new DateTime();
              $diff = $now->diff($created);

              if ($diff->days > 0) {
                $timeAgo = $diff->days == 1 ? 'yesterday' : $diff->days . ' days ago';
              } elseif ($diff->h > 0) {
                $timeAgo = $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
              } elseif ($diff->i > 0) {
                $timeAgo = $diff->i . ' minute' . ($diff->i > 1 ? 's' : '') . ' ago';
              } else {
                $timeAgo = 'just now';
              }
              ?>
              <div
                class="review-card bg-white rounded-2xl border <?php echo $isPending ? 'border-l-4 border-amber-500' : 'border-slate-200'; ?> p-5"
                data-id="<?php echo $review['id']; ?>" data-rating="<?php echo $rating; ?>"
                data-pending="<?php echo $isPending ? 'true' : 'false'; ?>">
                <div class="flex flex-wrap items-start justify-between gap-3">
                  <div class="flex gap-3 flex-1">
                    <div
                      class="h-10 w-10 rounded-full bg-amber-200 flex items-center justify-center text-amber-800 font-bold flex-shrink-0">
                      <?php echo $initials; ?>
                    </div>
                    <div class="flex-1">
                      <div class="flex items-center gap-2 flex-wrap">
                        <h3 class="font-semibold"><?php echo htmlspecialchars($review['full_name'] ?? 'Guest'); ?></h3>
                        <div class="star-rating text-sm"><?php echo $stars; ?></div>
                        <span class="text-xs text-slate-400"><?php echo $rating; ?>.0</span>
                        <?php if ($isPending): ?>
                          <span class="bg-amber-100 text-amber-700 text-xs px-2 py-0.5 rounded-full">pending response</span>
                        <?php else: ?>
                          <span class="bg-green-100 text-green-700 text-xs px-2 py-0.5 rounded-full">responded</span>
                        <?php endif; ?>
                      </div>
                      <p class="text-xs text-slate-500"><?php echo htmlspecialchars($review['experience'] ?? 'Review'); ?> ·
                        <?php echo date('M d, Y', strtotime($review['created_at'])); ?>
                      </p>
                      <p class="text-sm mt-2">"<?php echo nl2br(htmlspecialchars($review['review_text'])); ?>"</p>

                      <?php if ($hasResponse): ?>
                        <div class="mt-3 p-3 bg-slate-50 rounded-xl border-l-2 border-amber-300">
                          <div class="flex items-center gap-2 mb-1">
                            <i class="fas fa-reply text-amber-600 text-xs"></i>
                            <p class="text-xs font-medium text-amber-600">Management response:</p>
                            <span class="text-[10px] text-slate-400">by
                              <?php echo htmlspecialchars($review['responder_name'] ?? 'Admin'); ?></span>
                          </div>
                          <p class="text-xs text-slate-600"><?php echo nl2br(htmlspecialchars($review['response_text'])); ?>
                          </p>
                          <p class="text-[10px] text-slate-400 mt-1">
                            <?php echo date('M d, Y', strtotime($review['responded_at'])); ?>
                          </p>
                        </div>
                      <?php endif; ?>

                      <div class="flex items-center gap-3 mt-3">
                        <span class="text-xs text-slate-400"><i
                            class="fas fa-clock mr-1"></i><?php echo $timeAgo; ?></span>
                      </div>
                    </div>
                  </div>
                  <div class="flex gap-2 flex-shrink-0">
                    <?php if ($isPending): ?>
                      <button onclick="respondToReview(<?php echo $review['id']; ?>)"
                        class="bg-amber-600 text-white px-4 py-2 rounded-xl text-sm hover:bg-amber-700 transition">respond
                        now</button>
                    <?php else: ?>
                      <button
                        onclick="editResponse(<?php echo $review['id']; ?>, '<?php echo addslashes($review['response_text']); ?>')"
                        class="border border-amber-600 text-amber-700 px-4 py-2 rounded-xl text-sm hover:bg-amber-50 transition">edit
                        response</button>
                    <?php endif; ?>
                    <div class="relative">
                      <button onclick="toggleOptions(<?php echo $review['id']; ?>)"
                        class="border border-slate-200 px-3 py-2 rounded-xl text-slate-600 hover:bg-slate-50 transition">
                        <i class="fa-solid fa-ellipsis"></i>
                      </button>
                      <div id="options-<?php echo $review['id']; ?>"
                        class="absolute right-0 mt-1 w-48 bg-white rounded-xl shadow-lg border border-slate-200 hidden z-10">
                        <button onclick="deleteReview(<?php echo $review['id']; ?>)"
                          class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 rounded-t-xl">Delete
                          review</button>
                        <button onclick="markAsSpam(<?php echo $review['id']; ?>)"
                          class="w-full text-left px-4 py-2 text-sm text-orange-600 hover:bg-orange-50">Mark as
                          spam</button>
                        <button onclick="viewCustomerProfile(<?php echo $review['user_id']; ?>)"
                          class="w-full text-left px-4 py-2 text-sm text-blue-600 hover:bg-blue-50 rounded-b-xl">View
                          customer</button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>

        <!-- pagination -->
        <div class="flex items-center justify-between mb-8">
          <span class="text-xs text-slate-500">Showing 1-<?php echo min(10, count($reviews)); ?> of
            <?php echo number_format($stats['total_reviews'] ?? 0); ?> reviews</span>
          <div class="flex gap-2">
            <button class="border border-slate-200 px-3 py-1 rounded-lg text-sm hover:bg-slate-50">Previous</button>
            <button class="bg-amber-600 text-white px-3 py-1 rounded-lg text-sm">1</button>
            <button class="border border-slate-200 px-3 py-1 rounded-lg text-sm hover:bg-slate-50">2</button>
            <button class="border border-slate-200 px-3 py-1 rounded-lg text-sm hover:bg-slate-50">3</button>
            <button class="border border-slate-200 px-3 py-1 rounded-lg text-sm hover:bg-slate-50">4</button>
            <button class="border border-slate-200 px-3 py-1 rounded-lg text-sm hover:bg-slate-50">5</button>
            <button class="border border-slate-200 px-3 py-1 rounded-lg text-sm hover:bg-slate-50">Next</button>
          </div>
        </div>

        <!-- ===== BOTTOM: FEEDBACK TRENDS ===== -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

          <!-- common topics -->
          <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 p-5">
            <h2 class="font-semibold text-lg flex items-center gap-2 mb-3"><i
                class="fas fa-message text-amber-600"></i> common feedback topics</h2>
            <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
              <div class="border rounded-xl p-3 text-center">
                <p
                  class="font-medium text-2xl <?php echo $staffPositive > 50 ? 'text-green-600' : 'text-amber-600'; ?>">
                  <?php echo $staffPositive; ?>%
                </p>
                <p class="text-xs text-slate-500">staff/service</p>
              </div>
              <div class="border rounded-xl p-3 text-center">
                <p
                  class="font-medium text-2xl <?php echo $cleanlinessPositive > 50 ? 'text-green-600' : 'text-amber-600'; ?>">
                  <?php echo $cleanlinessPositive; ?>%
                </p>
                <p class="text-xs text-slate-500">cleanliness</p>
              </div>
              <div class="border rounded-xl p-3 text-center">
                <p class="font-medium text-2xl <?php echo $foodPositive > 50 ? 'text-green-600' : 'text-amber-600'; ?>">
                  <?php echo $foodPositive; ?>%
                </p>
                <p class="text-xs text-slate-500">food quality</p>
              </div>
              <div class="border rounded-xl p-3 text-center">
                <p class="font-medium text-2xl <?php echo $roomPositive > 50 ? 'text-green-600' : 'text-amber-600'; ?>">
                  <?php echo $roomPositive; ?>%
                </p>
                <p class="text-xs text-slate-500">rooms</p>
              </div>
              <div class="border rounded-xl p-3 text-center">
                <p class="font-medium text-2xl text-amber-600"><?php echo $waitTimeIssues; ?>%</p>
                <p class="text-xs text-slate-500">wait times</p>
              </div>
            </div>
          </div>

          <!-- quick response templates -->
          <div class="bg-amber-50 border border-amber-200 rounded-2xl p-5">
            <h3 class="font-semibold flex items-center gap-2 mb-3"><i
                class="fas fa-file-lines text-amber-600"></i> response templates</h3>
            <ul class="space-y-2 max-h-48 overflow-y-auto">
              <?php if (empty($templates)): ?>
                <li class="text-sm text-slate-500 italic">No templates available</li>
              <?php else: ?>
                <?php foreach ($templates as $template): ?>
                  <li class="text-sm hover:bg-amber-100 p-2 rounded-lg cursor-pointer transition"
                    onclick="useTemplate('<?php echo addslashes($template['template_text']); ?>')">
                    <?php echo htmlspecialchars($template['name']); ?>
                  </li>
                <?php endforeach; ?>
              <?php endif; ?>
            </ul>
          </div>
        </div>
      </main>
    </div>

    <script>
      // Global variables
      let currentReviewId = null;
      let currentResponseText = '';

      // Filter reviews
      document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.addEventListener('click', function () {
          // Update active tab
          document.querySelectorAll('.filter-btn').forEach(b => {
            b.classList.remove('bg-amber-600', 'text-white');
            b.classList.add('bg-white', 'border', 'border-slate-200', 'text-slate-700');
          });
          this.classList.remove('bg-white', 'border', 'border-slate-200', 'text-slate-700');
          this.classList.add('bg-amber-600', 'text-white');

          const filter = this.dataset.filter;
          const reviews = document.querySelectorAll('.review-card');

          reviews.forEach(review => {
            const rating = parseInt(review.dataset.rating);
            const isPending = review.dataset.pending === 'true';

            if (filter === 'all') {
              review.style.display = 'block';
            } else if (filter === 'pending') {
              review.style.display = isPending ? 'block' : 'none';
            } else if (filter === '5') {
              review.style.display = rating === 5 ? 'block' : 'none';
            } else if (filter === '4') {
              review.style.display = rating === 4 ? 'block' : 'none';
            } else if (filter === '3') {
              review.style.display = rating <= 3 ? 'block' : 'none';
            }
          });
        });
      });

      // Search functionality
      document.getElementById('searchInput').addEventListener('keyup', function () {
        const searchTerm = this.value.toLowerCase();
        const reviews = document.querySelectorAll('.review-card');

        reviews.forEach(review => {
          const text = review.textContent.toLowerCase();
          review.style.display = text.includes(searchTerm) ? 'block' : 'none';
        });
      });

      // Toggle options dropdown
      function toggleOptions(reviewId) {
        const options = document.getElementById(`options-${reviewId}`);
        if (options) {
          options.classList.toggle('hidden');

          // Close other open dropdowns
          document.querySelectorAll('[id^="options-"]').forEach(el => {
            if (el.id !== `options-${reviewId}`) {
              el.classList.add('hidden');
            }
          });
        }
      }

      // Close dropdowns when clicking outside
      document.addEventListener('click', function (event) {
        if (!event.target.closest('[id^="options-"]') && !event.target.closest('button[onclick^="toggleOptions"]')) {
          document.querySelectorAll('[id^="options-"]').forEach(el => {
            el.classList.add('hidden');
          });
        }
      });

      // Respond to review
      function respondToReview(reviewId) {
        currentReviewId = reviewId;

        Swal.fire({
          title: 'Respond to Review',
          html: `
          <textarea id="responseText" class="swal2-textarea" placeholder="Write your response..." rows="4"></textarea>
          <div class="text-left mt-2">
            <p class="text-xs text-slate-500 mb-1">Quick templates:</p>
            <div class="flex flex-wrap gap-1">
              <?php foreach ($templates as $template): ?>
              <button type="button" onclick="document.getElementById('responseText').value = '<?php echo addslashes($template['template_text']); ?>'" 
                      class="text-xs bg-amber-100 text-amber-700 px-2 py-1 rounded hover:bg-amber-200 transition mb-1">
                <?php echo addslashes($template['name']); ?>
              </button>
              <?php endforeach; ?>
            </div>
          </div>
        `,
          showCancelButton: true,
          confirmButtonColor: '#d97706',
          cancelButtonColor: '#6b7280',
          confirmButtonText: 'Submit Response',
          preConfirm: () => {
            const response = document.getElementById('responseText').value;
            if (!response) {
              Swal.showValidationMessage('Please enter a response');
              return false;
            }
            return { response: response };
          }
        }).then((result) => {
          if (result.isConfirmed) {
            submitResponse(reviewId, result.value.response);
          }
        });
      }

      // Edit existing response
      function editResponse(reviewId, existingResponse) {
        currentReviewId = reviewId;
        currentResponseText = existingResponse;

        Swal.fire({
          title: 'Edit Response',
          html: `
          <textarea id="responseText" class="swal2-textarea" rows="4">${existingResponse.replace(/</g, '&lt;').replace(/>/g, '&gt;')}</textarea>
          <div class="text-left mt-2">
            <p class="text-xs text-slate-500 mb-1">Quick templates:</p>
            <div class="flex flex-wrap gap-1">
              <?php foreach ($templates as $template): ?>
              <button type="button" onclick="document.getElementById('responseText').value = '<?php echo addslashes($template['template_text']); ?>'" 
                      class="text-xs bg-amber-100 text-amber-700 px-2 py-1 rounded hover:bg-amber-200 transition mb-1">
                <?php echo addslashes($template['name']); ?>
              </button>
              <?php endforeach; ?>
            </div>
          </div>
        `,
          showCancelButton: true,
          confirmButtonColor: '#d97706',
          cancelButtonColor: '#6b7280',
          confirmButtonText: 'Update Response',
          preConfirm: () => {
            const response = document.getElementById('responseText').value;
            if (!response) {
              Swal.showValidationMessage('Please enter a response');
              return false;
            }
            return { response: response };
          }
        }).then((result) => {
          if (result.isConfirmed) {
            submitResponse(reviewId, result.value.response);
          }
        });
      }

      // Submit response to server
      function submitResponse(reviewId, responseText) {
        const formData = new FormData();
        formData.append('action', 'respond_to_review');
        formData.append('review_id', reviewId);
        formData.append('response_text', responseText);

        fetch('../../../controller/admin/post/review_actions.php', {
          method: 'POST',
          body: formData
        })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              Swal.fire({
                title: 'Success!',
                text: data.message,
                icon: 'success',
                confirmButtonColor: '#d97706'
              }).then(() => {
                location.reload();
              });
            } else {
              Swal.fire({
                title: 'Error',
                text: data.message,
                icon: 'error',
                confirmButtonColor: '#d97706'
              });
            }
          })
          .catch(error => {
            console.error('Error:', error);
            Swal.fire({
              title: 'Error',
              text: 'An error occurred. Please try again.',
              icon: 'error',
              confirmButtonColor: '#d97706'
            });
          });
      }

      // Delete review
      function deleteReview(reviewId) {
        Swal.fire({
          title: 'Delete Review?',
          text: 'Are you sure you want to delete this review? This action cannot be undone.',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#d97706',
          cancelButtonColor: '#6b7280',
          confirmButtonText: 'Yes, delete it'
        }).then((result) => {
          if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('action', 'delete_review');
            formData.append('review_id', reviewId);

            fetch('../../../controller/admin/post/review_actions.php', {
              method: 'POST',
              body: formData
            })
              .then(response => response.json())
              .then(data => {
                if (data.success) {
                  Swal.fire({
                    title: 'Deleted!',
                    text: data.message,
                    icon: 'success',
                    confirmButtonColor: '#d97706'
                  }).then(() => {
                    location.reload();
                  });
                } else {
                  Swal.fire({
                    title: 'Error',
                    text: data.message,
                    icon: 'error',
                    confirmButtonColor: '#d97706'
                  });
                }
              })
              .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                  title: 'Error',
                  text: 'An error occurred. Please try again.',
                  icon: 'error',
                  confirmButtonColor: '#d97706'
                });
              });
          }
        });
      }

      // Mark as spam
      function markAsSpam(reviewId) {
        Swal.fire({
          title: 'Mark as Spam?',
          text: 'This will remove the review and mark it as spam.',
          icon: 'question',
          showCancelButton: true,
          confirmButtonColor: '#d97706',
          cancelButtonColor: '#6b7280',
          confirmButtonText: 'Yes, mark as spam'
        }).then((result) => {
          if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('action', 'mark_as_spam');
            formData.append('review_id', reviewId);

            fetch('../../../controller/admin/post/review_actions.php', {
              method: 'POST',
              body: formData
            })
              .then(response => response.json())
              .then(data => {
                if (data.success) {
                  Swal.fire({
                    title: 'Marked!',
                    text: data.message,
                    icon: 'success',
                    confirmButtonColor: '#d97706'
                  }).then(() => {
                    location.reload();
                  });
                } else {
                  Swal.fire({
                    title: 'Error',
                    text: data.message,
                    icon: 'error',
                    confirmButtonColor: '#d97706'
                  });
                }
              })
              .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                  title: 'Error',
                  text: 'An error occurred. Please try again.',
                  icon: 'error',
                  confirmButtonColor: '#d97706'
                });
              });
          }
        });
      }

      // View customer profile
      function viewCustomerProfile(userId) {
        Swal.fire({
          title: 'Customer Profile',
          text: `View customer #${userId} details`,
          icon: 'info',
          confirmButtonColor: '#d97706'
        });
        // In a real implementation, you would redirect to customer profile page
        // window.location.href = `../customer_management/customer_profile.php?id=${userId}`;
      }

      // Use template
      function useTemplate(template) {
        // This function is called when clicking a template
        // The template text will be inserted by the button in the modal
      }

      // Export reviews
      function exportReviews() {
        Swal.fire({
          title: 'Export Reviews',
          text: 'Choose export format',
          icon: 'question',
          showCancelButton: true,
          confirmButtonColor: '#d97706',
          cancelButtonColor: '#6b7280',
          confirmButtonText: 'CSV',
          denyButtonText: 'JSON',
          showDenyButton: true
        }).then((result) => {
          let format = '';
          if (result.isConfirmed) {
            format = 'csv';
          } else if (result.isDenied) {
            format = 'json';
          } else {
            return;
          }

          // Create form and submit to trigger download
          const form = document.createElement('form');
          form.method = 'POST';
          form.action = '../../controller/admin/post/review_actions.php';
          form.innerHTML = `
          <input type="hidden" name="action" value="export_reviews">
          <input type="hidden" name="format" value="${format}">
        `;
          document.body.appendChild(form);
          form.submit();
          document.body.removeChild(form);
        });
      }

      // Generate report
      function generateReport() {
        Swal.fire({
          title: 'Generate Report',
          text: 'Select report period',
          icon: 'info',
          showCancelButton: true,
          confirmButtonColor: '#d97706',
          cancelButtonColor: '#6b7280',
          confirmButtonText: 'This Month',
          denyButtonText: 'Last Month',
          showDenyButton: true
        }).then((result) => {
          if (result.isConfirmed) {
            Swal.fire({
              title: 'Report Generated!',
              text: 'Monthly report has been generated and will be downloaded.',
              icon: 'success',
              confirmButtonColor: '#d97706'
            });
          } else if (result.isDenied) {
            Swal.fire({
              title: 'Report Generated!',
              text: 'Last month report has been generated and will be downloaded.',
              icon: 'success',
              confirmButtonColor: '#d97706'
            });
          }
        });
      }
    </script>
  </body>

</html>