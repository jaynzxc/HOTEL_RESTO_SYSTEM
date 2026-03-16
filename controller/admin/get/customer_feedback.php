<?php
/**
 * GET Controller - Admin Customer Feedback & Reviews
 * Handles fetching all reviews and feedback data
 */

session_start();
require_once __DIR__ . '/../../../Class/Database.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || !$_SESSION['logged_in']) {
    header('Location: ../../view/auth/login.php');
    exit();
}

// // Check if user has admin role
// if (($_SESSION['user_role'] ?? 'customer') !== 'admin') {
//     header('Location: ../../view/customer_portal/dashboard.php');
//     exit();
// }

$config = require __DIR__ . '/../../../config/config.php';
$db = new Database($config['database']);

// Get admin user data
$admin = $db->query(
    "SELECT id, full_name, first_name, last_name, email, role, avatar
     FROM users WHERE id = :id",
    ['id' => $_SESSION['user_id']]
)->fetch_one();

// Get all reviews with user details and response info
$reviews = $db->query(
    "SELECT 
        r.id,
        r.experience,
        r.rating,
        r.review_text,
        r.detail,
        r.icon,
        r.created_at,
        u.id as user_id,
        u.full_name,
        u.first_name,
        u.last_name,
        u.avatar,
        u.member_tier,
        rr.id as response_id,
        rr.response_text,
        rr.responded_at,
        rr.responded_by,
        CONCAT(ru.first_name, ' ', ru.last_name) as responder_name
     FROM reviews r
     JOIN users u ON r.user_id = u.id
     LEFT JOIN review_responses rr ON r.id = rr.review_id
     LEFT JOIN users ru ON rr.responded_by = ru.id
     ORDER BY r.created_at DESC
     LIMIT 50",
    []
)->find() ?: [];

// Get review statistics
$stats = $db->query(
    "SELECT 
        COUNT(*) as total_reviews,
        COALESCE(AVG(r.rating), 0) as avg_rating,
        SUM(CASE WHEN rr.id IS NULL THEN 1 ELSE 0 END) as pending_responses,
        SUM(CASE WHEN MONTH(r.created_at) = MONTH(CURDATE()) 
                  AND YEAR(r.created_at) = YEAR(CURDATE()) THEN 1 ELSE 0 END) as this_month,
        SUM(CASE WHEN r.rating = 5 THEN 1 ELSE 0 END) as five_star,
        SUM(CASE WHEN r.rating = 4 THEN 1 ELSE 0 END) as four_star,
        SUM(CASE WHEN r.rating = 3 THEN 1 ELSE 0 END) as three_star,
        SUM(CASE WHEN r.rating = 2 THEN 1 ELSE 0 END) as two_star,
        SUM(CASE WHEN r.rating = 1 THEN 1 ELSE 0 END) as one_star
     FROM reviews r
     LEFT JOIN review_responses rr ON r.id = rr.review_id",
    []
)->fetch_one();

// Get rating distribution
$ratingDistribution = $db->query(
    "SELECT 
        rating,
        COUNT(*) as count,
        ROUND(COUNT(*) * 100.0 / (SELECT COUNT(*) FROM reviews), 1) as percentage
     FROM reviews
     GROUP BY rating
     ORDER BY rating DESC",
    []
)->find() ?: [];

// Ensure all ratings 1-5 are represented
$allRatings = [];
for ($i = 5; $i >= 1; $i--) {
    $found = false;
    foreach ($ratingDistribution as $dist) {
        if ($dist['rating'] == $i) {
            $allRatings[] = $dist;
            $found = true;
            break;
        }
    }
    if (!$found) {
        $allRatings[] = [
            'rating' => $i,
            'count' => 0,
            'percentage' => 0
        ];
    }
}

// Get common feedback topics
$topics = $db->query(
    "SELECT 
        SUM(CASE WHEN LOWER(review_text) LIKE '%staff%' OR LOWER(review_text) LIKE '%service%' 
                  OR LOWER(review_text) LIKE '%friendly%' OR LOWER(review_text) LIKE '%helpful%' THEN 1 ELSE 0 END) as staff_mentions,
        SUM(CASE WHEN LOWER(review_text) LIKE '%clean%' OR LOWER(review_text) LIKE '%spotless%' 
                  OR LOWER(review_text) LIKE '%tidy%' OR LOWER(review_text) LIKE '%neat%' THEN 1 ELSE 0 END) as cleanliness_mentions,
        SUM(CASE WHEN LOWER(review_text) LIKE '%wait%' OR LOWER(review_text) LIKE '%slow%' 
                  OR LOWER(review_text) LIKE '%delay%' OR LOWER(review_text) LIKE '%late%' THEN 1 ELSE 0 END) as wait_time_mentions,
        SUM(CASE WHEN LOWER(review_text) LIKE '%food%' OR LOWER(review_text) LIKE '%delicious%' 
                  OR LOWER(review_text) LIKE '%tasty%' OR LOWER(review_text) LIKE '%yummy%' THEN 1 ELSE 0 END) as food_mentions,
        SUM(CASE WHEN LOWER(review_text) LIKE '%room%' OR LOWER(review_text) LIKE '%bed%' 
                  OR LOWER(review_text) LIKE '%comfortable%' THEN 1 ELSE 0 END) as room_mentions,
        COUNT(*) as total
     FROM reviews",
    []
)->fetch_one();

$totalTopics = $topics['total'] ?: 1;
$staffPositive = round(($topics['staff_mentions'] / $totalTopics) * 100);
$cleanlinessPositive = round(($topics['cleanliness_mentions'] / $totalTopics) * 100);
$waitTimeIssues = round(($topics['wait_time_mentions'] / $totalTopics) * 100);
$foodPositive = round(($topics['food_mentions'] / $totalTopics) * 100);
$roomPositive = round(($topics['room_mentions'] / $totalTopics) * 100);

// Get response templates
$templates = $db->query(
    "SELECT id, name, template_text, category
     FROM response_templates
     ORDER BY category, name"
)->find() ?: [];

// Get admin initials
$initials = 'A';
if ($admin) {
    $first_name = $admin['first_name'] ?? '';
    $last_name = $admin['last_name'] ?? '';
    $full_name = $admin['full_name'] ?? 'Admin';

    if (!empty($first_name) && !empty($last_name)) {
        $initials = strtoupper(substr($first_name, 0, 1) . substr($last_name, 0, 1));
    } elseif (!empty($full_name)) {
        $name_parts = explode(' ', trim($full_name), 2);
        $initials = strtoupper(substr($name_parts[0], 0, 1));
        if (isset($name_parts[1])) {
            $initials .= strtoupper(substr($name_parts[1], 0, 1));
        }
    }
}

// Store data for view
$viewData = [
    'admin' => $admin,
    'initials' => $initials,
    'reviews' => $reviews,
    'stats' => $stats,
    'ratingDistribution' => $allRatings,
    'staffPositive' => $staffPositive,
    'cleanlinessPositive' => $cleanlinessPositive,
    'waitTimeIssues' => $waitTimeIssues,
    'foodPositive' => $foodPositive,
    'roomPositive' => $roomPositive,
    'templates' => $templates,
    'today' => date('F j, Y')
];

// Extract variables for view
extract($viewData);
?>