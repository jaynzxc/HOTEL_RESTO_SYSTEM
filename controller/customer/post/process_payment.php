<?php
session_start();
require_once __DIR__ . '/../../../Class/Database.php';

// Set JSON header for AJAX response
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !$_SESSION['logged_in']) {
    echo json_encode([
        'success' => false,
        'message' => 'Please login to continue'
    ]);
    exit();
}

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
    exit();
}

$config = require_once __DIR__ . '/../../../config/config.php';
$db = new Database($config['database']);

try {
    $action = $_POST['action'] ?? '';

    if ($action === 'process_payment') {
        // Get payment data
        $amount = floatval($_POST['amount'] ?? 0);
        $description = trim($_POST['description'] ?? '');
        $payment_method_id = intval($_POST['payment_method_id'] ?? 0);
        $booking_id = isset($_POST['booking_id']) ? intval($_POST['booking_id']) : null;
        $payment_type = $_POST['payment_type'] ?? 'other';

        // Validation
        $errors = [];

        if ($amount <= 0) {
            $errors[] = 'Invalid payment amount';
        }

        if (empty($description)) {
            $errors[] = 'Payment description is required';
        }

        if ($payment_method_id <= 0) {
            $errors[] = 'Please select a payment method';
        }

        if (!empty($errors)) {
            echo json_encode([
                'success' => false,
                'errors' => $errors
            ]);
            exit();
        }

        // Get payment method details
        $paymentMethod = $db->query(
            "SELECT * FROM payment_methods WHERE id = :id AND user_id = :user_id",
            [
                'id' => $payment_method_id,
                'user_id' => $_SESSION['user_id']
            ]
        )->fetch_one();

        if (!$paymentMethod) {
            echo json_encode([
                'success' => false,
                'message' => 'Payment method not found'
            ]);
            exit();
        }

        // Start transaction
        $db->query("START TRANSACTION");

        // Generate unique payment reference
        $year = date('Y');
        $month = date('m');
        $random = strtoupper(substr(uniqid(), -8));
        $payment_ref = "PAY-{$year}{$month}-{$random}";

        // Calculate points earned (5 points per ₱100)
        $points_earned = floor($amount / 100) * 5;

        // Insert into payments table (not transactions)
        $db->query(
            "INSERT INTO payments (
                payment_reference, user_id, booking_type, booking_id, amount, 
                payment_method, payment_status, payment_date, created_at
            ) VALUES (
                :ref, :user_id, :booking_type, :booking_id, :amount, 
                :payment_method, 'completed', NOW(), NOW()
            )",
            [
                'ref' => $payment_ref,
                'user_id' => $_SESSION['user_id'],
                'booking_type' => $payment_type,
                'booking_id' => $booking_id,
                'amount' => $amount,
                'payment_method' => $paymentMethod['display_name']
            ]
        );

        $payment_id = $db->lastInsertId();

        // Update user loyalty points
        if ($points_earned > 0) {
            $db->query(
                "UPDATE users SET loyalty_points = loyalty_points + :points WHERE id = :user_id",
                [
                    'points' => $points_earned,
                    'user_id' => $_SESSION['user_id']
                ]
            );
        }

        // If this payment is for a booking, update booking status
        if ($booking_id) {
            // Determine booking type
            $booking_type = $payment_type; // 'hotel' or 'restaurant'

            if ($booking_type === 'hotel') {
                $db->query(
                    "UPDATE bookings SET 
                        payment_status = 'paid',
                        status = 'confirmed',
                        payment_date = NOW(),
                        payment_method = :payment_method,
                        payment_id = :payment_id
                     WHERE id = :booking_id AND user_id = :user_id",
                    [
                        'booking_id' => $booking_id,
                        'user_id' => $_SESSION['user_id'],
                        'payment_method' => $paymentMethod['display_name'],
                        'payment_id' => $payment_id
                    ]
                );
            } elseif ($booking_type === 'restaurant') {
                // Check if restaurant_reservations table exists and update it
                $db->query(
                    "UPDATE restaurant_reservations SET 
                        payment_status = 'paid',
                        status = 'confirmed',
                        payment_date = NOW(),
                        payment_method = :payment_method,
                        payment_id = :payment_id
                     WHERE id = :booking_id AND user_id = :user_id",
                    [
                        'booking_id' => $booking_id,
                        'user_id' => $_SESSION['user_id'],
                        'payment_method' => $paymentMethod['display_name'],
                        'payment_id' => $payment_id
                    ]
                );
            }
        }

        // Commit transaction
        $db->query("COMMIT");

        // Get updated user points
        $updatedUser = $db->query(
            "SELECT loyalty_points FROM users WHERE id = :id",
            ['id' => $_SESSION['user_id']]
        )->fetch_one();

        // Prepare receipt data
        $receipt = [
            'payment_id' => $payment_id,
            'reference' => $payment_ref,
            'amount' => $amount,
            'description' => $description,
            'payment_method' => $paymentMethod['display_name'],
            'date' => date('Y-m-d H:i:s'),
            'points_earned' => $points_earned,
            'new_balance' => $updatedUser['loyalty_points']
        ];

        echo json_encode([
            'success' => true,
            'message' => 'Payment processed successfully!',
            'receipt' => $receipt,
            'points_earned' => $points_earned,
            'new_points' => $updatedUser['loyalty_points']
        ]);

    } elseif ($action === 'add_payment_method') {
        $method_type = $_POST['method_type'] ?? '';
        $account_name = trim($_POST['account_name'] ?? '');
        $account_number = trim($_POST['account_number'] ?? '');
        $expiry_date = trim($_POST['expiry_date'] ?? '');

        // Validation
        $errors = [];

        if (empty($method_type)) {
            $errors[] = 'Payment type is required';
        }

        if (empty($account_name)) {
            $errors[] = 'Account name is required';
        }

        if ($method_type !== 'cash' && empty($account_number)) {
            $errors[] = 'Account number is required';
        }

        if (!empty($errors)) {
            echo json_encode([
                'success' => false,
                'errors' => $errors
            ]);
            exit();
        }

        // Check if this is the first method (make it default)
        $methodCount = $db->query(
            "SELECT COUNT(*) as count FROM payment_methods WHERE user_id = :user_id",
            ['user_id' => $_SESSION['user_id']]
        )->fetch_one();
        $is_default = ($methodCount['count'] == 0) ? 1 : 0;

        // Set display name based on type
        $display_name = '';
        switch ($method_type) {
            case 'gcash':
                $display_name = 'GCash';
                break;
            case 'visa':
                $display_name = 'Visa';
                break;
            case 'mastercard':
                $display_name = 'Mastercard';
                break;
            case 'cash':
                $display_name = 'Cash on arrival';
                break;
        }

        // Insert payment method
        $db->query(
            "INSERT INTO payment_methods (
                user_id, method_type, display_name, account_name, account_number, expiry_date, is_default
            ) VALUES (
                :user_id, :method_type, :display_name, :account_name, :account_number, :expiry_date, :is_default
            )",
            [
                'user_id' => $_SESSION['user_id'],
                'method_type' => $method_type,
                'display_name' => $display_name,
                'account_name' => $account_name,
                'account_number' => $account_number,
                'expiry_date' => $expiry_date,
                'is_default' => $is_default
            ]
        );

        $method_id = $db->lastInsertId();

        echo json_encode([
            'success' => true,
            'message' => 'Payment method added successfully!',
            'method_id' => $method_id,
            'is_default' => $is_default
        ]);

    } elseif ($action === 'delete_payment_method') {
        $method_id = intval($_POST['method_id'] ?? 0);

        if ($method_id <= 0) {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid payment method'
            ]);
            exit();
        }

        // Delete payment method (ensure it belongs to user)
        $db->query(
            "DELETE FROM payment_methods WHERE id = :id AND user_id = :user_id",
            [
                'id' => $method_id,
                'user_id' => $_SESSION['user_id']
            ]
        );

        // If we deleted the default method, set another as default
        if ($db->count() > 0) {
            // Check if there are other methods
            $remaining = $db->query(
                "SELECT id FROM payment_methods WHERE user_id = :user_id LIMIT 1",
                ['user_id' => $_SESSION['user_id']]
            )->fetch_one();

            if ($remaining) {
                $db->query(
                    "UPDATE payment_methods SET is_default = 1 WHERE id = :id",
                    ['id' => $remaining['id']]
                );
            }
        }

        echo json_encode([
            'success' => true,
            'message' => 'Payment method deleted successfully'
        ]);

    } elseif ($action === 'set_default_method') {
        $method_id = intval($_POST['method_id'] ?? 0);

        if ($method_id <= 0) {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid payment method'
            ]);
            exit();
        }

        // Remove default from all user's methods
        $db->query(
            "UPDATE payment_methods SET is_default = 0 WHERE user_id = :user_id",
            ['user_id' => $_SESSION['user_id']]
        );

        // Set new default
        $db->query(
            "UPDATE payment_methods SET is_default = 1 WHERE id = :id AND user_id = :user_id",
            [
                'id' => $method_id,
                'user_id' => $_SESSION['user_id']
            ]
        );

        echo json_encode([
            'success' => true,
            'message' => 'Default payment method updated'
        ]);
    }

} catch (Exception $e) {
    // Rollback on error
    $db->query("ROLLBACK");

    error_log("Payment error: " . $e->getMessage());

    echo json_encode([
        'success' => false,
        'message' => 'An error occurred. Please try again. Error: ' . $e->getMessage()
    ]);
}
exit();
?>