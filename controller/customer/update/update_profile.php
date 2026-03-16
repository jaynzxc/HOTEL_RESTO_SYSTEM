<?php
session_start();
require_once __DIR__ . '/../../../Class/Database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location:' . $_SERVER['HTTP_REFERER'] ?? '/');
    exit();
}

$config = require_once __DIR__ . '/../../../config/config.php';
$db = new Database($config['database']);

$_SESSION['error'] ??= [];
$_SESSION['success'] ??= [];
$_SESSION['form_data'] ??= $_POST;

$action = $_POST['action'] ?? '';

try {
    if ($action === 'update_profile') {
        $first_name = trim($_POST['first_name'] ?? '');
        $last_name = trim($_POST['last_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $alternative_phone = trim($_POST['alternative_phone'] ?? '');
        $date_of_birth = !empty($_POST['date_of_birth']) ? $_POST['date_of_birth'] : null;
        $gender = $_POST['gender'] ?? null;
        $nationality = trim($_POST['nationality'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $city = trim($_POST['city'] ?? '');
        $postal_code = trim($_POST['postal_code'] ?? '');
        $country = trim($_POST['country'] ?? 'Philippines');
        $preferred_language = $_POST['preferred_language'] ?? 'English';

        $notify_email = isset($_POST['notify_email']) ? 1 : 0;
        $notify_sms = isset($_POST['notify_sms']) ? 1 : 0;
        $notify_promo = isset($_POST['notify_promo']) ? 1 : 0;
        $notify_loyalty = isset($_POST['notify_loyalty']) ? 1 : 0;

        $errors = [];

        if (empty($first_name)) {
            $errors['first_name'] = 'First name is required';
        }

        if (empty($last_name)) {
            $errors['last_name'] = 'Last name is required';
        }

        if (empty($email)) {
            $errors['email'] = 'Email is required';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid email format';
        }

        if (empty($phone)) {
            $errors['phone'] = 'Phone number is required';
        } elseif (!preg_match("/^[\+]?[(]?[0-9]{3}[)]?[-\s\.]?[0-9]{3}[-\s\.]?[0-9]{4,6}$/", $phone)) {
            $errors['phone'] = 'Invalid phone number format';
        }

        if (!empty($email)) {
            $existing = $db->query(
                "SELECT id FROM users WHERE email = :email AND id != :user_id",
                ['email' => $email, 'user_id' => $_SESSION['user_id']]
            )->fetch_one();

            if ($existing) {
                $errors['email'] = 'Email already registered to another account';
            }
        }

        if (!empty($phone)) {
            $existing = $db->query(
                "SELECT id FROM users WHERE phone = :phone AND id != :user_id",
                ['phone' => $phone, 'user_id' => $_SESSION['user_id']]
            )->fetch_one();

            if ($existing) {
                $errors['phone'] = 'Phone number already registered to another account';
            }
        }

        if (!empty($errors)) {
            $_SESSION['error'] = $errors;
            $_SESSION['form_data'] = $_POST;
            header('Location:' . $_SERVER['HTTP_REFERER'] ?? '/');
            exit();
        }

        $current_user = $db->query(
            "SELECT email FROM users WHERE id = :id",
            ['id' => $_SESSION['user_id']]
        )->fetch_one();

        $email_changed = ($current_user['email'] !== $email);

        $full_name = $first_name . ' ' . $last_name;

        $db->query(
            "UPDATE users SET 
                full_name = :full_name,
                first_name = :first_name,
                last_name = :last_name,
                email = :email,
                phone = :phone,
                alternative_phone = :alternative_phone,
                date_of_birth = :date_of_birth,
                gender = :gender,
                nationality = :nationality,
                address = :address,
                city = :city,
                postal_code = :postal_code,
                country = :country,
                preferred_language = :preferred_language,
                notify_email = :notify_email,
                notify_sms = :notify_sms,
                notify_promo = :notify_promo,
                notify_loyalty = :notify_loyalty,
                updated_at = NOW()
            WHERE id = :user_id",
            [
                'user_id' => $_SESSION['user_id'],
                'full_name' => $full_name,
                'first_name' => $first_name,
                'last_name' => $last_name,
                'email' => $email,
                'phone' => $phone,
                'alternative_phone' => $alternative_phone,
                'date_of_birth' => $date_of_birth,
                'gender' => $gender,
                'nationality' => $nationality,
                'address' => $address,
                'city' => $city,
                'postal_code' => $postal_code,
                'country' => $country,
                'preferred_language' => $preferred_language,
                'notify_email' => $notify_email,
                'notify_sms' => $notify_sms,
                'notify_promo' => $notify_promo,
                'notify_loyalty' => $notify_loyalty
            ]
        );

        if ($email_changed) {
            $db->query(
                "UPDATE users SET email_verified = 0 WHERE id = :id",
                ['id' => $_SESSION['user_id']]
            );
            $_SESSION['success'][] = 'Profile updated! Please verify your new email address.';
        } else {
            $_SESSION['success'][] = 'Profile updated successfully!';
        }

    } elseif ($action === 'change_password') {
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        $errors = [];

        if (empty($current_password)) {
            $errors['current_password'] = 'Current password is required';
        }

        if (empty($new_password)) {
            $errors['new_password'] = 'New password is required';
        } elseif (strlen($new_password) < 8) {
            $errors['new_password'] = 'Password must be at least 8 characters';
        } elseif (!preg_match("/^(?=.*[A-Z])(?=.*[0-9])/", $new_password)) {
            $errors['new_password'] = 'Password must contain at least one uppercase letter and one number';
        }

        if ($new_password !== $confirm_password) {
            $errors['confirm_password'] = 'Passwords do not match';
        }

        if (!empty($errors)) {
            $_SESSION['error'] = $errors;
            header('Location:' . $_SERVER['HTTP_REFERER'] ?? '/');
            exit();
        }

        $user = $db->query(
            "SELECT password FROM users WHERE id = :id",
            ['id' => $_SESSION['user_id']]
        )->fetch_one();

        if (!password_verify($current_password, $user['password'])) {
            $_SESSION['error']['current_password'] = 'Current password is incorrect';
            header('Location:' . $_SERVER['HTTP_REFERER'] ?? '/');
            exit();
        }

        $hashed_password = password_hash($new_password, PASSWORD_BCRYPT, ['cost' => 12]);

        $db->query(
            "UPDATE users SET password = :password WHERE id = :id",
            [
                'password' => $hashed_password,
                'id' => $_SESSION['user_id']
            ]
        );

        $_SESSION['success'][] = 'Password changed successfully!';

    } elseif ($action === 'upload_avatar') {
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/jpg'];
            $file_type = $_FILES['avatar']['type'];
            $file_size = $_FILES['avatar']['size'];

            if (!in_array($file_type, $allowed_types)) {
                $_SESSION['error']['avatar'] = 'Invalid file type. Please upload JPG, PNG, or GIF';
                header('Location:' . $_SERVER['HTTP_REFERER'] ?? '/');
                exit();
            }

            if ($file_size > 2 * 1024 * 1024) {
                $_SESSION['error']['avatar'] = 'File too large. Maximum size is 2MB';
                header('Location:' . $_SERVER['HTTP_REFERER'] ?? '/');
                exit();
            }

            $upload_dir = __DIR__ . '/../../../uploads/avatars/';

            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $extension = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
            $filename = 'avatar_' . $_SESSION['user_id'] . '_' . time() . '.' . $extension;
            $filepath = $upload_dir . $filename;

            if (move_uploaded_file($_FILES['avatar']['tmp_name'], $filepath)) {
                $old_avatar = $db->query(
                    "SELECT avatar FROM users WHERE id = :id",
                    ['id' => $_SESSION['user_id']]
                )->fetch_one();

                if (!empty($old_avatar['avatar'])) {
                    $old_file = __DIR__ . '/../../../' . $old_avatar['avatar'];
                    if (file_exists($old_file)) {
                        unlink($old_file);
                    }
                }

                $db->query(
                    "UPDATE users SET avatar = :avatar WHERE id = :id",
                    [
                        'avatar' => 'uploads/avatars/' . $filename,
                        'id' => $_SESSION['user_id']
                    ]
                );

                $_SESSION['success'][] = 'Profile picture updated successfully!';
            } else {
                $_SESSION['error']['avatar'] = 'Failed to upload image';
            }
        } else {
            $_SESSION['error']['avatar'] = 'No file uploaded';
        }
    }

} catch (Exception $e) {
    $_SESSION['error']['database'] = 'An error occurred. Please try again.';
    error_log("Profile error: " . $e->getMessage());
}

header('Location:' . $_SERVER['HTTP_REFERER'] ?? '/');
exit();