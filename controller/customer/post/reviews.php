<?php
session_start();
require_once '../../../Class/Database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location:' . $_SERVER['HTTP_REFERER'] ?? '/');
    exit();
}

$config = require_once '../../../config/config.php';
$db = new Database($config['database']);

$_SESSION['error'] ??= [];
$_SESSION['success'] ??= [];
$_SESSION['form_data'] ??= [];

$action = $_POST['action'] ?? '';

try {
    if ($action === 'add_review' || $action === 'update_review') {
        $experience = $_POST['experience'] ?? '';
        $rating = (int) ($_POST['rating'] ?? 0);
        $review_text = trim($_POST['review_text'] ?? '');
        $detail = $_POST['detail'] ?? null;

        $errors = [];
        if (empty($experience))
            $errors['experience'] = 'Please select an experience';
        if ($rating < 1 || $rating > 5)
            $errors['rating'] = 'Please select a rating';
        if (empty($review_text))
            $errors['review_text'] = 'Please write your review';

        if (!empty($errors)) {
            $_SESSION['error'] = $errors;
            $_SESSION['form_data'] = $_POST;
        } else {
            if ($action === 'add_review') {
                $db->query(
                    "INSERT INTO reviews (user_id, experience, rating, review_text, detail, icon) 
                     VALUES (:user_id, :experience, :rating, :review_text, :detail, 'fa-pen')",
                    [
                        'user_id' => $_SESSION['user_id'],
                        'experience' => $experience,
                        'rating' => $rating,
                        'review_text' => $review_text,
                        'detail' => $detail
                    ]
                );

                $db->query(
                    "UPDATE users SET loyalty_points = loyalty_points + 1 WHERE id = :user_id",
                    ['user_id' => $_SESSION['user_id']]
                );

                $_SESSION['success'][] = 'Review added successfully! +1 points';
            } else {
                $review_id = $_POST['review_id'] ?? 0;

                $review = $db->query(
                    "SELECT id FROM reviews WHERE id = :id AND user_id = :user_id",
                    ['id' => $review_id, 'user_id' => $_SESSION['user_id']]
                )->fetch_one();

                if ($review) {
                    $db->query(
                        "UPDATE reviews SET experience = :experience, rating = :rating, 
                         review_text = :review_text, detail = :detail WHERE id = :id",
                        [
                            'id' => $review_id,
                            'experience' => $experience,
                            'rating' => $rating,
                            'review_text' => $review_text,
                            'detail' => $detail
                        ]
                    );
                    $_SESSION['success'][] = 'Review updated successfully!';
                }
            }
        }
    }

    if ($action === 'delete_review') {
        $review_id = $_POST['review_id'] ?? 0;

        $db->query(
            "DELETE FROM reviews WHERE id = :id AND user_id = :user_id",
            ['id' => $review_id, 'user_id' => $_SESSION['user_id']]
        );

        if ($db->count() > 0) {
            $_SESSION['success'][] = 'Review deleted successfully';

            $db->query(
                "UPDATE users SET loyalty_points = loyalty_points - 1 WHERE id = :user_id",
                ['user_id' => $_SESSION['user_id']]
            );
        }
    }

} catch (Exception $e) {
    $_SESSION['error']['database'] = 'An error occurred. Please try again.';
    error_log("Review error: " . $e->getMessage());
}

header('Location:' . $_SERVER['HTTP_REFERER'] ?? '/');
exit();
