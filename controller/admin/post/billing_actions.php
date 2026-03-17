<?php
/**
 * POST Controller - Admin Billing Actions
 * Handles all billing and payment management actions
 */

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

$config = require __DIR__ . '/../../../config/config.php';
$db = new Database($config['database']);

// Get user role from database
$user = $db->query(
    "SELECT role FROM users WHERE id = :id",
    ['id' => $_SESSION['user_id']]
)->fetch_one();

// Check if user has admin role
if (!$user || $user['role'] !== 'admin') {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access'
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

try {
    $action = $_POST['action'] ?? '';

    // CREATE INVOICE
    if ($action === 'create_invoice') {
        $guest_name = trim($_POST['guest_name'] ?? '');
        $guest_email = trim($_POST['guest_email'] ?? '');
        $guest_phone = trim($_POST['guest_phone'] ?? '');
        $amount = floatval($_POST['amount'] ?? 0);
        $due_date = $_POST['due_date'] ?? date('Y-m-d', strtotime('+7 days'));
        $description = trim($_POST['description'] ?? '');

        if (empty($guest_name) || $amount <= 0) {
            throw new Exception('Guest name and valid amount are required');
        }

        // Generate invoice number
        $year = date('Y');
        $month = date('m');
        $random = strtoupper(substr(uniqid(), -4));
        $invoice_number = "INV-{$year}-{$month}-{$random}";

        // Split name
        $name_parts = explode(' ', $guest_name, 2);
        $first_name = $name_parts[0];
        $last_name = $name_parts[1] ?? '';

        // Check if user exists by email
        $user = null;
        if (!empty($guest_email)) {
            $user = $db->query(
                "SELECT id FROM users WHERE email = :email",
                ['email' => $guest_email]
            )->fetch_one();
        }

        $db->beginTransaction();

        // Create booking/invoice
        $db->query(
            "INSERT INTO bookings (
                booking_reference, user_id, guest_first_name, guest_last_name,
                guest_email, guest_phone, booking_type, check_in, check_out,
                nights, room_name, room_price, adults, children,
                subtotal, tax, total_amount, status, payment_status,
                special_requests, created_at
            ) VALUES (
                :ref, :user_id, :first_name, :last_name,
                :email, :phone, 'hotel', CURDATE(), :due_date,
                1, 'Manual Invoice', :amount, 1, 0,
                :amount, 0, :amount, 'pending', 'unpaid',
                :description, NOW()
            )",
            [
                'ref' => $invoice_number,
                'user_id' => $user['id'] ?? null,
                'first_name' => $first_name,
                'last_name' => $last_name,
                'email' => $guest_email,
                'phone' => $guest_phone,
                'due_date' => $due_date,
                'amount' => $amount,
                'description' => $description
            ]
        );

        $booking_id = $db->lastInsertId();

        // Create notification for admin
        $db->query(
            "INSERT INTO notifications (user_id, title, message, type, icon, created_at) 
             VALUES (:user_id, 'Invoice Created', :message, 'success', 'fa-file-invoice', NOW())",
            [
                'user_id' => $_SESSION['user_id'],
                'message' => "Invoice $invoice_number created for $guest_name (₱" . number_format($amount, 2) . ")"
            ]
        );

        $db->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Invoice created successfully',
            'invoice_number' => $invoice_number,
            'booking_id' => $booking_id
        ]);
        exit();
    }

    // RECORD PAYMENT
    elseif ($action === 'record_payment') {
        $booking_id = intval($_POST['booking_id'] ?? 0);
        $amount = floatval($_POST['amount'] ?? 0);
        $payment_method = $_POST['payment_method'] ?? '';
        $reference = trim($_POST['reference'] ?? '');

        if (!$booking_id || $amount <= 0 || empty($payment_method)) {
            throw new Exception('Booking ID, amount, and payment method are required');
        }

        // Get booking details
        $booking = $db->query(
            "SELECT * FROM bookings WHERE id = :id",
            ['id' => $booking_id]
        )->fetch_one();

        if (!$booking) {
            throw new Exception('Booking not found');
        }

        $db->beginTransaction();

        // Generate payment reference
        $year = date('Y');
        $month = date('m');
        $random = strtoupper(substr(uniqid(), -8));
        $payment_ref = "PAY-{$year}{$month}-{$random}";

        // Calculate total paid so far
        $total_paid = $db->query(
            "SELECT COALESCE(SUM(amount), 0) as total FROM payments WHERE booking_id = :booking_id AND booking_type = 'hotel' AND payment_status = 'completed' AND approval_status = 'approved'",
            ['booking_id' => $booking_id]
        )->fetch_one()['total'];

        $new_total = $total_paid + $amount;

        // Determine payment status
        $payment_status = 'completed';
        $booking_status = 'paid';

        if ($new_total < $booking['total_amount']) {
            $booking_status = 'partial';
        }

        // Insert payment
        $db->query(
            "INSERT INTO payments (
                payment_reference, user_id, booking_type, booking_id,
                amount, payment_method, payment_status, approval_status,
                transaction_id, payment_date, created_at
            ) VALUES (
                :ref, :user_id, 'hotel', :booking_id,
                :amount, :method, 'completed', 'approved',
                :transaction_id, NOW(), NOW()
            )",
            [
                'ref' => $payment_ref,
                'user_id' => $booking['user_id'],
                'booking_id' => $booking_id,
                'amount' => $amount,
                'method' => $payment_method,
                'transaction_id' => $reference ?: $payment_ref
            ]
        );

        // Update booking payment status
        $db->query(
            "UPDATE bookings SET 
                payment_status = :status,
                payment_method = :method,
                payment_date = NOW(),
                updated_at = NOW()
             WHERE id = :id",
            [
                'status' => $booking_status,
                'method' => $payment_method,
                'id' => $booking_id
            ]
        );

        // Award loyalty points if payment is complete and user exists
        if ($booking['user_id']) {
            $points_earned = floor($amount / 100) * 5;
            if ($points_earned > 0) {
                $db->query(
                    "UPDATE users SET loyalty_points = loyalty_points + :points WHERE id = :user_id",
                    [
                        'points' => $points_earned,
                        'user_id' => $booking['user_id']
                    ]
                );
            }
        }

        // Create notification for admin
        $db->query(
            "INSERT INTO notifications (user_id, title, message, type, icon, created_at) 
             VALUES (:user_id, 'Payment Recorded', :message, 'success', 'fa-credit-card', NOW())",
            [
                'user_id' => $_SESSION['user_id'],
                'message' => "Payment of ₱" . number_format($amount, 2) . " recorded for invoice {$booking['booking_reference']}"
            ]
        );

        // Notify customer if they have user account
        if ($booking['user_id']) {
            $db->query(
                "INSERT INTO notifications (user_id, title, message, type, icon, link, created_at) 
                 VALUES (:user_id, 'Payment Received', :message, 'success', 'fa-circle-check', '/src/customer_portal/payments.php', NOW())",
                [
                    'user_id' => $booking['user_id'],
                    'message' => "Your payment of ₱" . number_format($amount, 2) . " has been received. Thank you!"
                ]
            );
        }

        $db->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Payment recorded successfully',
            'payment_ref' => $payment_ref
        ]);
        exit();
    }

    // APPROVE PAYMENT
    elseif ($action === 'approve_payment') {
        $payment_id = intval($_POST['payment_id'] ?? 0);

        if (!$payment_id) {
            throw new Exception('Payment ID required');
        }

        $db->beginTransaction();

        // Get payment details
        $payment = $db->query(
            "SELECT * FROM payments WHERE id = :id",
            ['id' => $payment_id]
        )->fetch_one();

        if (!$payment) {
            throw new Exception('Payment not found');
        }

        // Update payment status
        $db->query(
            "UPDATE payments SET 
                payment_status = 'completed', 
                approval_status = 'approved',
                approved_by = :admin_id,
                approved_at = NOW()
             WHERE id = :id",
            [
                'admin_id' => $_SESSION['user_id'],
                'id' => $payment_id
            ]
        );

        // Update booking
        $booking = $db->query(
            "SELECT * FROM bookings WHERE id = :id",
            ['id' => $payment['booking_id']]
        )->fetch_one();

        if ($booking) {
            // Calculate total paid
            $total_paid = $db->query(
                "SELECT COALESCE(SUM(amount), 0) as total FROM payments WHERE booking_id = :booking_id AND payment_status = 'completed' AND approval_status = 'approved'",
                ['booking_id' => $payment['booking_id']]
            )->fetch_one()['total'];

            $new_status = $total_paid >= $booking['total_amount'] ? 'paid' : 'partial';

            $db->query(
                "UPDATE bookings SET payment_status = :status, updated_at = NOW() WHERE id = :id",
                ['status' => $new_status, 'id' => $payment['booking_id']]
            );
        }

        // Award loyalty points
        if ($booking && $booking['user_id']) {
            $points_earned = floor($payment['amount'] / 100) * 5;
            if ($points_earned > 0) {
                $db->query(
                    "UPDATE users SET loyalty_points = loyalty_points + :points WHERE id = :user_id",
                    [
                        'points' => $points_earned,
                        'user_id' => $booking['user_id']
                    ]
                );
            }
        }

        // Create notification
        $db->query(
            "INSERT INTO notifications (user_id, title, message, type, icon, created_at) 
             VALUES (:user_id, 'Payment Approved', :message, 'success', 'fa-check-circle', NOW())",
            [
                'user_id' => $_SESSION['user_id'],
                'message' => "Payment #{$payment['payment_reference']} has been approved"
            ]
        );

        $db->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Payment approved successfully'
        ]);
        exit();
    }

    // REJECT PAYMENT
    elseif ($action === 'reject_payment') {
        $payment_id = intval($_POST['payment_id'] ?? 0);
        $reason = trim($_POST['reason'] ?? '');

        if (!$payment_id) {
            throw new Exception('Payment ID required');
        }

        $db->beginTransaction();

        $db->query(
            "UPDATE payments SET 
                payment_status = 'failed', 
                approval_status = 'rejected',
                rejection_reason = :reason,
                approved_by = :admin_id,
                approved_at = NOW()
             WHERE id = :id",
            [
                'reason' => $reason,
                'admin_id' => $_SESSION['user_id'],
                'id' => $payment_id
            ]
        );

        // Create notification
        $db->query(
            "INSERT INTO notifications (user_id, title, message, type, icon, created_at) 
             VALUES (:user_id, 'Payment Rejected', :message, 'warning', 'fa-times-circle', NOW())",
            [
                'user_id' => $_SESSION['user_id'],
                'message' => "Payment #{$payment_id} has been rejected" . ($reason ? ": $reason" : "")
            ]
        );

        $db->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Payment rejected successfully'
        ]);
        exit();
    }

    // SEND REMINDER
    elseif ($action === 'send_reminder') {
        $booking_id = intval($_POST['booking_id'] ?? 0);

        if (!$booking_id) {
            throw new Exception('Booking ID required');
        }

        $booking = $db->query(
            "SELECT b.*, u.id as user_id 
             FROM bookings b
             LEFT JOIN users u ON b.user_id = u.id
             WHERE b.id = :id",
            ['id' => $booking_id]
        )->fetch_one();

        if (!$booking) {
            throw new Exception('Booking not found');
        }

        // Create notification for user if they have account
        if ($booking['user_id']) {
            $db->query(
                "INSERT INTO notifications (user_id, title, message, type, icon, link, created_at) 
                 VALUES (:user_id, 'Payment Reminder', :message, 'warning', 'fa-clock', '/src/customer_portal/payments.php', NOW())",
                [
                    'user_id' => $booking['user_id'],
                    'message' => "This is a reminder that your payment of ₱" . number_format($booking['total_amount'], 2) . " is due on " . date('M d, Y', strtotime($booking['check_out']))
                ]
            );
        }

        // Create notification for admin
        $db->query(
            "INSERT INTO notifications (user_id, title, message, type, icon, created_at) 
             VALUES (:user_id, 'Reminder Sent', :message, 'info', 'fa-bell', NOW())",
            [
                'user_id' => $_SESSION['user_id'],
                'message' => "Payment reminder sent for invoice {$booking['booking_reference']}"
            ]
        );

        echo json_encode([
            'success' => true,
            'message' => 'Reminder sent successfully'
        ]);
        exit();
    }

    // GET INVOICE DETAILS
    elseif ($action === 'get_invoice') {
        $booking_id = intval($_POST['booking_id'] ?? 0);

        if (!$booking_id) {
            throw new Exception('Booking ID required');
        }

        $invoice = $db->query(
            "SELECT 
                b.*,
                COALESCE(SUM(p.amount), 0) as paid_amount
             FROM bookings b
             LEFT JOIN payments p ON b.id = p.booking_id AND p.booking_type = 'hotel' AND p.payment_status = 'completed' AND p.approval_status = 'approved'
             WHERE b.id = :id
             GROUP BY b.id",
            ['id' => $booking_id]
        )->fetch_one();

        if (!$invoice) {
            throw new Exception('Invoice not found');
        }

        // Get payment history
        $payments = $db->query(
            "SELECT * FROM payments WHERE booking_id = :booking_id AND booking_type = 'hotel' ORDER BY created_at DESC",
            ['booking_id' => $booking_id]
        )->find() ?: [];

        echo json_encode([
            'success' => true,
            'invoice' => $invoice,
            'payments' => $payments,
            'balance' => $invoice['total_amount'] - $invoice['paid_amount']
        ]);
        exit();
    }

    // VOID INVOICE
    elseif ($action === 'void_invoice') {
        $booking_id = intval($_POST['booking_id'] ?? 0);

        if (!$booking_id) {
            throw new Exception('Booking ID required');
        }

        $db->beginTransaction();

        $db->query(
            "UPDATE bookings SET status = 'cancelled', updated_at = NOW() WHERE id = :id",
            ['id' => $booking_id]
        );

        // Create notification
        $db->query(
            "INSERT INTO notifications (user_id, title, message, type, icon, created_at) 
             VALUES (:user_id, 'Invoice Voided', :message, 'warning', 'fa-file-circle-xmark', NOW())",
            [
                'user_id' => $_SESSION['user_id'],
                'message' => "Invoice #{$booking_id} has been voided"
            ]
        );

        $db->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Invoice voided successfully'
        ]);
        exit();
    }

    // APPLY FILTER
    elseif ($action === 'apply_filter') {
        $status = $_POST['status'] ?? 'all';
        $search = $_POST['search'] ?? '';

        $params = [];
        if ($status !== 'all')
            $params[] = "status=$status";
        if (!empty($search))
            $params[] = "search=" . urlencode($search);

        $queryString = !empty($params) ? '?' . implode('&', $params) : '';

        echo json_encode([
            'success' => true,
            'redirect' => $queryString
        ]);
        exit();
    }

    // EXPORT INVOICES
    elseif ($action === 'export_invoices') {
        $format = $_POST['format'] ?? 'csv';
        $filter = $_POST['filter'] ?? 'all';

        $where = "1=1";
        if ($filter === 'paid') {
            $where .= " AND payment_status = 'paid'";
        } elseif ($filter === 'pending') {
            $where .= " AND payment_status = 'unpaid' AND check_out >= CURDATE()";
        } elseif ($filter === 'overdue') {
            $where .= " AND payment_status = 'unpaid' AND check_out < CURDATE()";
        }

        $invoices = $db->query(
            "SELECT 
                booking_reference as invoice_number,
                CONCAT(guest_first_name, ' ', guest_last_name) as guest_name,
                guest_email,
                guest_phone,
                DATE(created_at) as invoice_date,
                check_out as due_date,
                total_amount,
                payment_status,
                payment_method,
                payment_date
             FROM bookings
             WHERE $where
             ORDER BY created_at DESC",
            []
        )->find() ?: [];

        if ($format === 'csv') {
            $filename = 'invoices_export_' . date('Y-m-d') . '.csv';
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="' . $filename . '"');

            $output = fopen('php://output', 'w');

            // Headers
            if (!empty($invoices)) {
                fputcsv($output, array_keys($invoices[0]));

                // Data
                foreach ($invoices as $row) {
                    fputcsv($output, $row);
                }
            }
            fclose($output);
            exit();
        } else {
            header('Content-Type: application/json');
            header('Content-Disposition: attachment; filename="invoices_export_' . date('Y-m-d') . '.json"');
            echo json_encode($invoices, JSON_PRETTY_PRINT);
            exit();
        }
    }

    // GENERATE REPORT
    elseif ($action === 'generate_report') {
        $period = $_POST['period'] ?? 'month';
        $type = $_POST['type'] ?? 'sales';

        $endDate = date('Y-m-d');
        $startDate = date('Y-m-d', strtotime('-30 days'));

        if ($period === 'quarter') {
            $startDate = date('Y-m-d', strtotime('-3 months'));
        } elseif ($period === 'year') {
            $startDate = date('Y-m-d', strtotime('-1 year'));
        }

        $data = $db->query(
            "SELECT 
                DATE(created_at) as date,
                COUNT(*) as transaction_count,
                SUM(amount) as total_amount
             FROM payments
             WHERE payment_status = 'completed' 
                AND approval_status = 'approved'
                AND DATE(created_at) BETWEEN :start AND :end
             GROUP BY DATE(created_at)
             ORDER BY date ASC",
            ['start' => $startDate, 'end' => $endDate]
        )->find() ?: [];

        echo json_encode([
            'success' => true,
            'report' => $data,
            'period' => $period,
            'start' => $startDate,
            'end' => $endDate
        ]);
        exit();
    } else {
        throw new Exception('Invalid action');
    }

} catch (Exception $e) {
    if (isset($db)) {
        $db->rollBack();
    }
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
exit();
?>