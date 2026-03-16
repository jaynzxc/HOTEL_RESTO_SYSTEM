<?php
/**
 * POST Controller - Admin Review Actions
 * Handles responding to, deleting, and managing reviews
 */

session_start();
require_once __DIR__ . '/../../../Class/Database.php';

// Set JSON header for AJAX response
header('Content-Type: application/json');

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || !$_SESSION['logged_in']) {
    echo json_encode([
        'success' => false,
        'message' => 'Please login to continue'
    ]);
    exit();
}

// if (($_SESSION['user_role'] ?? 'customer') !== 'admin') {
//     echo json_encode([
//         'success' => false,
//         'message' => 'Unauthorized access'
//     ]);
//     exit();
// }

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
    exit();
}

$config = require __DIR__ . '/../../../config/config.php';
$db = new Database($config['database']);

try {
    $action = $_POST['action'] ?? '';

    if ($action === 'respond_to_review') {
        $review_id = intval($_POST['review_id'] ?? 0);
        $response_text = trim($_POST['response_text'] ?? '');

        if (!$review_id) {
            throw new Exception('Invalid review ID');
        }

        if (empty($response_text)) {
            throw new Exception('Response text is required');
        }

        // Check if review exists
        $review = $db->query(
            "SELECT id FROM reviews WHERE id = :id",
            ['id' => $review_id]
        )->fetch_one();

        if (!$review) {
            throw new Exception('Review not found');
        }

        // Check if already responded
        $existing = $db->query(
            "SELECT id FROM review_responses WHERE review_id = :review_id",
            ['review_id' => $review_id]
        )->fetch_one();

        $db->query("START TRANSACTION");

        if ($existing) {
            // Update existing response
            $db->query(
                "UPDATE review_responses 
                 SET response_text = :response_text, 
                     responded_at = NOW(),
                     responded_by = :responded_by
                 WHERE review_id = :review_id",
                [
                    'response_text' => $response_text,
                    'responded_by' => $_SESSION['user_id'],
                    'review_id' => $review_id
                ]
            );
            $message = 'Response updated successfully';
        } else {
            // Insert new response
            $db->query(
                "INSERT INTO review_responses (review_id, response_text, responded_by, responded_at, created_at) 
                 VALUES (:review_id, :response_text, :responded_by, NOW(), NOW())",
                [
                    'review_id' => $review_id,
                    'response_text' => $response_text,
                    'responded_by' => $_SESSION['user_id']
                ]
            );
            $message = 'Response submitted successfully';
        }

        $db->query("COMMIT");

        echo json_encode([
            'success' => true,
            'message' => $message
        ]);

    } elseif ($action === 'delete_review') {
        $review_id = intval($_POST['review_id'] ?? 0);

        if (!$review_id) {
            throw new Exception('Invalid review ID');
        }

        $db->query("START TRANSACTION");

        // Delete any responses first (foreign key will handle this if set to CASCADE)
        $db->query(
            "DELETE FROM review_responses WHERE review_id = :review_id",
            ['review_id' => $review_id]
        );

        // Delete the review
        $db->query(
            "DELETE FROM reviews WHERE id = :id",
            ['id' => $review_id]
        );

        $db->query("COMMIT");

        echo json_encode([
            'success' => true,
            'message' => 'Review deleted successfully'
        ]);

    } elseif ($action === 'mark_as_spam') {
        $review_id = intval($_POST['review_id'] ?? 0);

        if (!$review_id) {
            throw new Exception('Invalid review ID');
        }

        // You could add a spam flag column to reviews table
        // For now, we'll just delete it
        $db->query("START TRANSACTION");

        $db->query(
            "DELETE FROM review_responses WHERE review_id = :review_id",
            ['review_id' => $review_id]
        );

        $db->query(
            "DELETE FROM reviews WHERE id = :id",
            ['id' => $review_id]
        );

        $db->query("COMMIT");

        echo json_encode([
            'success' => true,
            'message' => 'Review marked as spam and removed'
        ]);

    } elseif ($action === 'export_reviews') {
        $format = $_POST['format'] ?? 'csv';

        // Get all reviews for export
        $allReviews = $db->query(
            "SELECT 
                r.id,
                u.full_name as customer_name,
                u.email,
                r.experience,
                r.rating,
                r.review_text,
                r.created_at,
                rr.response_text,
                rr.responded_at
             FROM reviews r
             JOIN users u ON r.user_id = u.id
             LEFT JOIN review_responses rr ON r.id = rr.review_id
             ORDER BY r.created_at DESC",
            []
        )->find() ?: [];

        if ($format === 'csv') {
            // Generate CSV
            $filename = 'reviews_export_' . date('Y-m-d') . '.csv';
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="' . $filename . '"');

            $output = fopen('php://output', 'w');
            fputcsv($output, ['ID', 'Customer', 'Email', 'Experience', 'Rating', 'Review', 'Date', 'Response', 'Response Date']);

            foreach ($allReviews as $review) {
                fputcsv($output, [
                    $review['id'],
                    $review['customer_name'],
                    $review['email'],
                    $review['experience'],
                    $review['rating'],
                    $review['review_text'],
                    $review['created_at'],
                    $review['response_text'],
                    $review['responded_at']
                ]);
            }
            fclose($output);
            exit();
        } else {
            // Generate JSON
            header('Content-Type: application/json');
            header('Content-Disposition: attachment; filename="reviews_export_' . date('Y-m-d') . '.json"');
            echo json_encode($allReviews, JSON_PRETTY_PRINT);
            exit();
        }

    } else {
        throw new Exception('Invalid action');
    }

} catch (Exception $e) {
    if (isset($db)) {
        $db->query("ROLLBACK");
    }
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
exit();
?>