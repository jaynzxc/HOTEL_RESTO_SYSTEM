<?php
/**
 * GET Controller - Admin Customer Feedback & Reviews
 * Handles fetching all reviews and feedback data with pagination
 */

session_start();
require_once __DIR__ . '/../../../Class/Database.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || !$_SESSION['logged_in']) {
    header('Location: ../../view/auth/login.php');
    exit();
}

$config = require __DIR__ . '/../../../config/config.php';
$db = new Database($config['database']);

// Get admin user data
$admin = $db->query(
    "SELECT id, full_name, first_name, last_name, email, role, avatar
     FROM users WHERE id = :id",
    ['id' => $_SESSION['user_id']]
)->fetch_one();

// Get filter parameters
$statusFilter = isset($_GET['status']) ? $_GET['status'] : 'all';
$ratingFilter = isset($_GET['rating']) ? (int) $_GET['rating'] : 0;
$searchFilter = isset($_GET['search']) ? $_GET['search'] : '';
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Build WHERE clause for reviews
$whereConditions = ["1=1"];
$queryParams = [];

if ($statusFilter === 'pending') {
    $whereConditions[] = "rr.id IS NULL"; // No response yet
} elseif ($statusFilter === 'responded') {
    $whereConditions[] = "rr.id IS NOT NULL"; // Has response
}

if ($ratingFilter > 0) {
    $whereConditions[] = "r.rating = :rating";
    $queryParams['rating'] = $ratingFilter;
}

if (!empty($searchFilter)) {
    $whereConditions[] = "(r.review_text LIKE :search OR u.full_name LIKE :search)";
    $queryParams['search'] = '%' . $searchFilter . '%';
}

$whereClause = implode(' AND ', $whereConditions);

// Get total count for pagination
$countResult = $db->query(
    "SELECT COUNT(*) as total 
     FROM reviews r
     JOIN users u ON r.user_id = u.id
     LEFT JOIN review_responses rr ON r.id = rr.review_id
     WHERE $whereClause",
    $queryParams
)->fetch_one();
$totalReviews = $countResult['total'];
$totalPages = ceil($totalReviews / $limit);

// Get all reviews with user details and response info - WITH PAGINATION
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
     WHERE $whereClause
     ORDER BY r.created_at DESC
     LIMIT $limit OFFSET $offset",
    $queryParams
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

// Ensure stats have default values
if (!$stats) {
    $stats = [
        'total_reviews' => 0,
        'avg_rating' => 0,
        'pending_responses' => 0,
        'this_month' => 0,
        'five_star' => 0,
        'four_star' => 0,
        'three_star' => 0,
        'two_star' => 0,
        'one_star' => 0
    ];
}

// Get rating distribution
$ratingDistribution = $db->query(
    "SELECT 
        rating,
        COUNT(*) as count,
        ROUND(COUNT(*) * 100.0 / (SELECT COUNT(*) FROM reviews WHERE 1=1), 1) as percentage
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
    'today' => date('F j, Y'),
    'currentPage' => $page,
    'totalPages' => $totalPages,
    'totalReviews' => $totalReviews,
    'statusFilter' => $statusFilter,
    'ratingFilter' => $ratingFilter,
    'searchFilter' => $searchFilter,
    'limit' => $limit
];

// Extract variables for view
extract($viewData);
?>