<?php require_once '../../controller/customer/get/reviews.php' ?>

<!DOCTYPE html>
<html lang="en">

  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reviews · Customer Portal</title>
    <!-- Tailwind via CDN + Font Awesome 6 -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
      .review-card {
        transition: box-shadow 0.2s;
      }

      .rating-star {
        cursor: pointer;
        transition: color 0.1s;
      }

      .hover-scale:hover {
        transform: translateY(-2px);
      }
    </style>
  </head>

  <body class="bg-slate-50 font-sans antialiased">

    <!-- main flex wrapper (sidebar + content) -->
    <div class="min-h-screen flex flex-col lg:flex-row">

      <!-- ========== SIDEBAR (customer portal) ========== -->
      <?php require './components/customer_nav.php' ?>

      <!-- ========== MAIN CONTENT (REVIEWS PAGE) ========== -->
      <main class="flex-1 p-5 lg:p-8 overflow-y-auto">

        <!-- header -->
        <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
          <div>
            <h1 class="text-2xl lg:text-3xl font-light text-slate-800">Reviews & Feedback</h1>
            <p class="text-sm text-slate-500 mt-0.5">share your experience and read past reviews</p>
          </div>
          <div
            class="bg-white border border-slate-200 rounded-full px-4 py-2 text-sm flex items-center gap-2 shadow-sm">
            <i class="fas fa-calendar text-slate-400"></i>
            <?php echo date('l, d F Y'); ?>
          </div>
        </div>

        <?php require_once '../message.php'; ?>

        <!-- ===== LEAVE A REVIEW SECTION ===== -->
        <div class="bg-white rounded-2xl border border-slate-200 p-6 mb-8">
          <h2 class="font-semibold text-xl flex items-center gap-2 mb-4">
            <i class="fas fa-pen-to-square text-amber-600"></i>
            <span id="formTitle">write a new review</span>
          </h2>

          <form method="POST" action="../../controller/customer/post/reviews.php" id="reviewForm">
            <input type="hidden" name="action" id="formAction" value="add_review">
            <input type="hidden" name="review_id" id="reviewId" value="">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
              <div>
                <label class="block text-xs text-slate-500 mb-1">select experience *</label>
                <select name="experience" id="reviewExperience"
                  class="w-full border <?php echo isset($_SESSION['error']['experience']) ? 'border-red-500' : 'border-slate-200'; ?> rounded-xl p-3 text-sm bg-white">
                  <option value="">Select an experience</option>
                  <option value="Hotel stay · Deluxe Twin">Hotel stay · Deluxe Twin</option>
                  <option value="Hotel stay · Ocean Suite">Hotel stay · Ocean Suite</option>
                  <option value="Restaurant · Azure">Restaurant · Azure</option>
                  <option value="Room service">Room service</option>
                  <option value="Spa & Wellness">Spa & Wellness</option>
                </select>
              </div>

              <div>
                <label class="block text-xs text-slate-500 mb-1">your rating *</label>
                <div class="flex items-center gap-1 text-3xl text-slate-300" id="ratingStars">
                  <i class="fas fa-star rating-star" data-value="1"></i>
                  <i class="fas fa-star rating-star" data-value="2"></i>
                  <i class="fas fa-star rating-star" data-value="3"></i>
                  <i class="fas fa-star rating-star" data-value="4"></i>
                  <i class="fas fa-star rating-star" data-value="5"></i>
                  <input type="hidden" name="rating" id="ratingValue" value="0">
                  <span class="text-sm text-slate-500 ml-3" id="ratingDisplay">0/5</span>
                </div>
              </div>

              <div class="md:col-span-2">
                <label class="block text-xs text-slate-500 mb-1">your review *</label>
                <textarea name="review_text" id="reviewText" rows="4" placeholder="Tell us about your experience..."
                  class="w-full border <?php echo isset($_SESSION['error']['review_text']) ? 'border-red-500' : 'border-slate-200'; ?> rounded-xl p-3 text-sm"><?php echo htmlspecialchars($_SESSION['form_data']['review_text'] ?? ''); ?></textarea>
              </div>

              <div class="md:col-span-2">
                <label class="block text-xs text-slate-500 mb-1">additional details (optional)</label>
                <input type="text" name="detail" id="reviewDetail"
                  value="<?php echo htmlspecialchars($_SESSION['form_data']['detail'] ?? ''); ?>"
                  placeholder="e.g., room number, specific dish, etc."
                  class="w-full border border-slate-200 rounded-xl p-3 text-sm">
              </div>
            </div>

            <div class="flex gap-3 mt-4">
              <button type="submit"
                class="bg-amber-600 hover:bg-amber-700 text-white px-6 py-3 rounded-xl font-medium transition"
                id="submitBtn">submit review</button>
              <button type="button"
                class="border border-slate-300 px-6 py-3 rounded-xl text-slate-700 hover:bg-slate-50 transition"
                id="cancelEditBtn" style="display: none;">cancel</button>
              <button type="reset"
                class="border border-slate-300 px-6 py-3 rounded-xl text-slate-700 hover:bg-slate-50 transition">clear</button>
            </div>
          </form>

          <p class="text-xs text-green-600 mt-3 flex items-center gap-1" id="pointsHint">
            <i class="fas fa-circle-check"></i> earn 20 loyalty points per review!
          </p>
        </div>

        <!-- ===== MY PAST REVIEWS ===== -->
        <h2 class="font-semibold text-xl mb-4 flex items-center gap-2">
          <i class="fas fa-clock-rotate-left text-amber-600"></i> my past reviews
        </h2>

        <div id="myReviewsContainer" class="space-y-4 mb-8">
          <?php if (empty($myReviews)): ?>
            <div class="text-slate-400 text-center py-8 bg-white rounded-2xl border border-slate-200">
              You haven't written any reviews yet.
            </div>
          <?php else: ?>
            <?php foreach ($myReviews as $review): ?>
              <div class="bg-white rounded-2xl border border-slate-200 p-5 review-card"
                data-review-id="<?php echo $review['id']; ?>">
                <div class="flex flex-wrap items-start justify-between gap-3">
                  <div class="flex gap-3 flex-1">
                    <div
                      class="h-12 w-12 rounded-full bg-amber-100 flex items-center justify-center text-amber-700 flex-shrink-0">
                      <i class="fa-solid <?php echo $review['icon'] ?? 'fa-pen'; ?>"></i>
                    </div>
                    <div class="flex-1">
                      <div class="flex items-center gap-2 flex-wrap">
                        <h3 class="font-semibold"><?php echo htmlspecialchars($review['experience']); ?></h3>
                        <div class="flex text-sm">
                          <?php for ($i = 1; $i <= 5; $i++): ?>
                            <span class="<?php echo $i <= $review['rating'] ? 'text-yellow-400' : 'text-slate-300'; ?>">
                              <?php echo $i <= $review['rating'] ? '★' : '☆'; ?>
                            </span>
                          <?php endfor; ?>
                        </div>
                        <span class="text-xs text-slate-400"><?php echo $review['rating']; ?>.0</span>
                      </div>
                      <p class="text-xs text-slate-500">
                        <?php echo date('M d, Y', strtotime($review['created_at'])); ?>
                        <?php if (!empty($review['detail'])): ?> ·
                          <?php echo htmlspecialchars($review['detail']); ?>
                        <?php endif; ?>
                      </p>
                      <p class="text-sm mt-2">"<?php echo nl2br(htmlspecialchars($review['review_text'])); ?>"</p>

                      <!-- Admin Response Section -->
                      <?php if (!empty($review['response_id'])): ?>
                        <div class="mt-3 p-3 bg-amber-50 rounded-xl border-l-4 border-amber-300">
                          <div class="flex items-center gap-2 mb-1">
                            <i class="fas fa-reply text-amber-600 text-xs"></i>
                            <p class="text-xs font-medium text-amber-700">Management response:</p>
                            <span class="text-[10px] text-slate-500">by
                              <?php echo htmlspecialchars($review['responder_name'] ?? 'Admin'); ?></span>
                          </div>
                          <p class="text-xs text-slate-700"><?php echo nl2br(htmlspecialchars($review['response_text'])); ?>
                          </p>
                          <p class="text-[10px] text-slate-400 mt-1">
                            <?php echo date('M d, Y', strtotime($review['responded_at'])); ?>
                          </p>
                        </div>
                      <?php else: ?>
                        <p class="text-xs text-amber-600 mt-2 italic">Awaiting management response...</p>
                      <?php endif; ?>

                      <div class="flex items-center gap-3 mt-3">
                        <span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full">published</span>
                        <button type="button" class="text-xs text-amber-700 hover:underline edit-review"
                          data-id="<?php echo $review['id']; ?>"
                          data-experience="<?php echo htmlspecialchars($review['experience']); ?>"
                          data-rating="<?php echo $review['rating']; ?>"
                          data-text="<?php echo htmlspecialchars($review['review_text']); ?>"
                          data-detail="<?php echo htmlspecialchars($review['detail'] ?? ''); ?>">
                          edit
                        </button>
                        <form method="POST" action="../../controller/customer/post/reviews.php" style="display: inline;"
                          onsubmit="return confirm('Delete this review?');">
                          <input type="hidden" name="action" value="delete_review">
                          <input type="hidden" name="review_id" value="<?php echo $review['id']; ?>">
                          <button type="submit" class="text-xs text-rose-600 hover:underline">delete</button>
                        </form>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
        <!-- ===== RECENT REVIEWS FROM OTHER GUESTS ===== -->
        <!-- ===== RECENT REVIEWS FROM OTHER GUESTS ===== -->
        <h2 class="font-semibold text-xl mb-4 flex items-center gap-2">
          <i class="fas fa-comments text-amber-600"></i> recent guest reviews
        </h2>

        <div id="guestReviewsContainer" class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <?php if (empty($guestReviews)): ?>
            <div class="text-slate-400 text-center py-8 bg-white rounded-2xl border border-slate-200 md:col-span-2">
              No guest reviews yet.
            </div>
          <?php else: ?>
            <?php foreach ($guestReviews as $review): ?>
              <div class="bg-white border border-slate-200 rounded-2xl p-4 hover-scale transition">
                <div class="flex items-center gap-2 mb-2">
                  <div
                    class="h-8 w-8 rounded-full bg-amber-200 flex items-center justify-center text-xs font-bold uppercase text-amber-800">
                    <?php echo htmlspecialchars($review['initial']); ?>
                  </div>
                  <div>
                    <p class="font-medium text-sm"><?php echo htmlspecialchars($review['user_name']); ?></p>
                    <div class="flex text-xs">
                      <?php for ($i = 1; $i <= 5; $i++): ?>
                        <span class="<?php echo $i <= $review['rating'] ? 'text-yellow-400' : 'text-slate-300'; ?>">
                          <?php echo $i <= $review['rating'] ? '★' : '☆'; ?>
                        </span>
                      <?php endfor; ?>
                    </div>
                  </div>
                  <?php if (!empty($review['member_tier']) && $review['member_tier'] !== 'bronze'): ?>
                    <span class="ml-auto text-xs px-2 py-0.5 rounded-full <?php
                    echo $review['member_tier'] == 'platinum' ? 'bg-purple-100 text-purple-700' :
                      ($review['member_tier'] == 'gold' ? 'bg-amber-100 text-amber-700' :
                        'bg-slate-100 text-slate-700');
                    ?>">
                      <?php echo ucfirst($review['member_tier']); ?>
                    </span>
                  <?php endif; ?>
                </div>
                <p class="text-sm">"<?php echo nl2br(htmlspecialchars($review['review_text'])); ?>"</p>

                <!-- Admin Response for Guest Reviews -->
                <?php if (!empty($review['admin_response'])): ?>
                  <div class="mt-2 p-2 bg-amber-50 rounded-lg border-l-2 border-amber-300 text-xs">
                    <p class="font-medium text-amber-700 flex items-center gap-1">
                      <i class="fas fa-reply"></i> Management response:
                    </p>
                    <p class="text-slate-600 mt-1"><?php echo nl2br(htmlspecialchars($review['admin_response'])); ?></p>
                    <p class="text-[10px] text-slate-400 mt-1">
                      <?php echo date('M d, Y', strtotime($review['response_date'])); ?>
                    </p>
                  </div>
                <?php endif; ?>

                <p class="text-xs text-slate-400 mt-2 flex items-center gap-1">
                  <i class="fas fa-clock"></i>
                  <?php echo time_elapsed_string($review['created_at']); ?> ·
                  <span class="text-slate-500"><?php echo htmlspecialchars($review['experience']); ?></span>
                </p>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
        <!-- bottom hint -->
        <div class="mt-10 text-center text-xs text-slate-400 border-t pt-6">
          ✅ Share your experience and earn loyalty points!
        </div>
      </main>
    </div>

    <script>
      (function () {
        // DOM elements
        const ratingStars = document.querySelectorAll('.rating-star');
        const ratingDisplay = document.getElementById('ratingDisplay');
        const ratingValue = document.getElementById('ratingValue');
        const reviewForm = document.getElementById('reviewForm');
        const formAction = document.getElementById('formAction');
        const formTitle = document.getElementById('formTitle');
        const reviewId = document.getElementById('reviewId');
        const reviewExperience = document.getElementById('reviewExperience');
        const reviewText = document.getElementById('reviewText');
        const reviewDetail = document.getElementById('reviewDetail');
        const submitBtn = document.getElementById('submitBtn');
        const cancelEditBtn = document.getElementById('cancelEditBtn');

        let currentRating = <?php echo $_SESSION['form_data']['rating'] ?? 0; ?>;

        // Initialize stars from session if available
        if (currentRating > 0) {
          ratingStars.forEach((star, idx) => {
            if (idx < currentRating) {
              star.classList.remove('fas', 'text-slate-300');
              star.classList.add('fa-solid', 'text-yellow-400');
            }
          });
          ratingDisplay.innerText = currentRating + '/5';
          ratingValue.value = currentRating;
        }

        // Star rating setup
        ratingStars.forEach(star => {
          star.addEventListener('click', () => {
            const val = parseInt(star.dataset.value);
            currentRating = val;

            ratingStars.forEach((s, idx) => {
              if (idx < val) {
                s.classList.remove('fas', 'text-slate-300');
                s.classList.add('fa-solid', 'text-yellow-400');
              } else {
                s.classList.remove('fa-solid', 'text-yellow-400');
                s.classList.add('fas', 'text-slate-300');
              }
            });

            ratingDisplay.innerText = val + '/5';
            ratingValue.value = val;
          });
        });

        // Edit review
        document.querySelectorAll('.edit-review').forEach(btn => {
          btn.addEventListener('click', function () {
            const id = this.dataset.id;
            const experience = this.dataset.experience;
            const rating = parseInt(this.dataset.rating);
            const text = this.dataset.text;
            const detail = this.dataset.detail;

            // Set form to edit mode
            formAction.value = 'update_review';
            reviewId.value = id;
            formTitle.innerText = 'edit review';
            submitBtn.innerText = 'update review';

            // Populate form
            for (let i = 0; i < reviewExperience.options.length; i++) {
              if (reviewExperience.options[i].value === experience) {
                reviewExperience.selectedIndex = i;
                break;
              }
            }

            reviewText.value = text;
            reviewDetail.value = detail;

            // Set stars
            currentRating = rating;
            ratingStars.forEach((s, idx) => {
              if (idx < rating) {
                s.classList.remove('fas', 'text-slate-300');
                s.classList.add('fa-solid', 'text-yellow-400');
              } else {
                s.classList.remove('fa-solid', 'text-yellow-400');
                s.classList.add('fas', 'text-slate-300');
              }
            });
            ratingDisplay.innerText = rating + '/5';
            ratingValue.value = rating;

            // Show cancel button
            cancelEditBtn.style.display = 'inline-block';

            // Scroll to form
            reviewForm.scrollIntoView({
              behavior: 'smooth'
            });
          });
        });

        // Cancel edit
        cancelEditBtn.addEventListener('click', function () {
          resetForm();
        });

        // Reset form
        function resetForm() {
          formAction.value = 'add_review';
          reviewId.value = '';
          formTitle.innerText = 'write a new review';
          submitBtn.innerText = 'submit review';
          reviewForm.reset();

          // Reset stars
          currentRating = 0;
          ratingStars.forEach(s => {
            s.classList.remove('fa-solid', 'text-yellow-400');
            s.classList.add('fas', 'text-slate-300');
          });
          ratingDisplay.innerText = '0/5';
          ratingValue.value = '0';

          // Hide cancel button
          cancelEditBtn.style.display = 'none';
        }

        // Clear form on reset
        document.querySelector('button[type="reset"]').addEventListener('click', resetForm);
      })();
    </script>

    <?php
    // Helper function for time elapsed
    function time_elapsed_string($datetime)
    {
      $now = new DateTime;
      $ago = new DateTime($datetime);
      $diff = $now->diff($ago);

      if ($diff->y > 0)
        return $diff->y . ' year' . ($diff->y > 1 ? 's' : '') . ' ago';
      if ($diff->m > 0)
        return $diff->m . ' month' . ($diff->m > 1 ? 's' : '') . ' ago';
      if ($diff->d > 0)
        return $diff->d . ' day' . ($diff->d > 1 ? 's' : '') . ' ago';
      if ($diff->h > 0)
        return $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
      if ($diff->i > 0)
        return $diff->i . ' minute' . ($diff->i > 1 ? 's' : '') . ' ago';
      return 'just now';
    }
    ?>
  </body>

</html>