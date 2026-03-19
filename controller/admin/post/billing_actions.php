<?php
/**
 * POST Controller - Admin Billing Actions
 * Handles all billing and payment management actions with approval workflow
 */

// Enable error logging but disable display
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

session_start();
require_once __DIR__ . '/../../../Class/Database.php';

// Set JSON header for AJAX response
header('Content-Type: application/json');

try {
    // Check if user is logged in
    if (!isset($_SESSION['user_id']) || !$_SESSION['logged_in']) {
        throw new Exception('Please login to continue');
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
        throw new Exception('Unauthorized access');
    }

    // Check if it's a POST request
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    $action = $_POST['action'] ?? '';

    // ==================== CREATE INVOICE ====================
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
        $user_exists = null;
        if (!empty($guest_email)) {
            $user_exists = $db->query(
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
                'user_id' => $user_exists['id'] ?? null,
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

    // ==================== RECORD PAYMENT ====================
    elseif ($action === 'record_payment') {
        $booking_id = intval($_POST['booking_id'] ?? 0);
        $amount = floatval($_POST['amount'] ?? 0);
        $payment_method = $_POST['payment_method'] ?? '';
        $reference = trim($_POST['reference'] ?? '');
        $booking_type = $_POST['booking_type'] ?? 'hotel'; // 'hotel' or 'restaurant'

        if (!$booking_id || $amount <= 0 || empty($payment_method)) {
            throw new Exception('Booking ID, amount, and payment method are required');
        }

        $db->beginTransaction();

        // Get booking/reservation details based on type
        if ($booking_type === 'hotel') {
            $booking = $db->query(
                "SELECT * FROM bookings WHERE id = :id",
                ['id' => $booking_id]
            )->fetch_one();
        } else {
            $booking = $db->query(
                "SELECT *, down_payment as total_amount FROM restaurant_reservations WHERE id = :id",
                ['id' => $booking_id]
            )->fetch_one();
        }

        if (!$booking) {
            throw new Exception(ucfirst($booking_type) . ' booking not found');
        }

        // Generate payment reference
        $year = date('Y');
        $month = date('m');
        $random = strtoupper(substr(uniqid(), -8));
        $payment_ref = "PAY-{$year}{$month}-{$random}";

        // Calculate total paid so far
        $total_paid = $db->query(
            "SELECT COALESCE(SUM(amount), 0) as total FROM payments 
             WHERE booking_id = :booking_id AND booking_type = :type 
             AND payment_status = 'completed' AND approval_status = 'approved'",
            ['booking_id' => $booking_id, 'type' => $booking_type]
        )->fetch_one()['total'];

        $new_total = $total_paid + $amount;

        // Insert payment (pending approval)
        $db->query(
            "INSERT INTO payments (
                payment_reference, user_id, booking_type, booking_id,
                amount, payment_method, payment_status, approval_status,
                transaction_id, created_at
            ) VALUES (
                :ref, :user_id, :type, :booking_id,
                :amount, :method, 'pending', 'pending',
                :transaction_id, NOW()
            )",
            [
                'ref' => $payment_ref,
                'user_id' => $booking['user_id'],
                'type' => $booking_type,
                'booking_id' => $booking_id,
                'amount' => $amount,
                'method' => $payment_method,
                'transaction_id' => $reference ?: $payment_ref
            ]
        );

        // Update current_balance - move from available to pending
        if ($booking['user_id']) {
            // Check if balance record exists
            $balance = $db->query(
                "SELECT * FROM current_balance WHERE user_id = :user_id",
                ['user_id' => $booking['user_id']]
            )->fetch_one();

            if ($balance) {
                $db->query(
                    "UPDATE current_balance SET 
                        pending_balance = pending_balance + :amount,
                        available_balance = available_balance - :amount,
                        last_updated = NOW()
                     WHERE user_id = :user_id",
                    [
                        'amount' => $amount,
                        'user_id' => $booking['user_id']
                    ]
                );
            }
        }

        // Create notifications
        // For admin
        $db->query(
            "INSERT INTO notifications (user_id, title, message, type, icon, link, created_at) 
             VALUES (:user_id, 'Payment Pending Approval', :message, 'warning', 'fa-clock', '/src/admin/operations/billing_&_payment.php', NOW())",
            [
                'user_id' => $_SESSION['user_id'],
                'message' => "New payment of ₱" . number_format($amount, 2) . " from " . ($booking['guest_first_name'] ?? 'Guest') . " needs approval"
            ]
        );

        // For other admins (if any) - you might want to notify all admins
        $admins = $db->query(
            "SELECT id FROM users WHERE role = 'admin' AND id != :current_id",
            ['current_id' => $_SESSION['user_id']]
        )->find() ?: [];

        foreach ($admins as $admin) {
            $db->query(
                "INSERT INTO notifications (user_id, title, message, type, icon, link, created_at) 
                 VALUES (:user_id, 'Payment Pending Approval', :message, 'warning', 'fa-clock', '/src/admin/operations/billing_&_payment.php', NOW())",
                [
                    'user_id' => $admin['id'],
                    'message' => "New payment of ₱" . number_format($amount, 2) . " needs approval"
                ]
            );
        }

        // For customer
        if ($booking['user_id']) {
            $db->query(
                "INSERT INTO notifications (user_id, title, message, type, icon, link, created_at) 
                 VALUES (:user_id, 'Payment Pending Approval', :message, 'info', 'fa-clock', '/src/customer_portal/payments.php', NOW())",
                [
                    'user_id' => $booking['user_id'],
                    'message' => "Payment of ₱" . number_format($amount, 2) . " received and pending admin approval. You'll earn " . floor($amount / 100) * 5 . " loyalty points after approval."
                ]
            );
        }

        $db->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Payment recorded and pending approval',
            'payment_ref' => $payment_ref
        ]);
        exit();
    }

    // ==================== APPROVE PAYMENT ====================
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

        if ($payment['approval_status'] !== 'pending') {
            throw new Exception('This payment has already been processed');
        }

        // Calculate points to award
        $points_earned = floor($payment['amount'] / 100) * 5;

        // 1. Update payment status ONLY - let the trigger handle balance
        $db->query(
            "UPDATE payments SET 
            payment_status = 'completed', 
            approval_status = 'approved',
            approved_by = :admin_id,
            approved_at = NOW(),
            payment_date = NOW()
         WHERE id = :id",
            [
                'admin_id' => $_SESSION['user_id'],
                'id' => $payment_id
            ]
        );

        // 2. DO NOT manually update current_balance - the trigger will handle it
        // The trigger update_balance_on_payment_update will run automatically
        // and move amount from pending, reduce total balance

        // 3. Update ALL unpaid bookings for this user
        if ($payment['user_id']) {
            // Update hotel bookings - set payment_status to paid AND status to confirmed
            $db->query(
                "UPDATE bookings SET 
                payment_status = 'paid',
                status = 'confirmed',
                payment_date = NOW(),
                updated_at = NOW()
             WHERE user_id = :user_id 
                AND payment_status = 'unpaid'
                AND status != 'cancelled'",
                ['user_id' => $payment['user_id']]
            );

            // Update restaurant reservations - set payment_status to paid AND status to confirmed
            $db->query(
                "UPDATE restaurant_reservations SET 
                payment_status = 'paid',
                status = 'confirmed',
                payment_date = NOW(),
                updated_at = NOW()
             WHERE user_id = :user_id 
                AND payment_status = 'unpaid'
                AND status != 'cancelled'",
                ['user_id' => $payment['user_id']]
            );
        }

        // 4. Award loyalty points to user
        if ($payment['user_id'] && $points_earned > 0) {
            $db->query(
                "UPDATE users SET 
                loyalty_points = loyalty_points + :points,
                updated_at = NOW()
             WHERE id = :user_id",
                [
                    'points' => $points_earned,
                    'user_id' => $payment['user_id']
                ]
            );
        }

        // 5. Create notifications
        // For customer
        if ($payment['user_id']) {
            $db->query(
                "INSERT INTO notifications (user_id, title, message, type, icon, link, created_at) 
             VALUES (:user_id, 'Payment Approved', :message, 'success', 'fa-check-circle', '/src/customer_portal/payments.php', NOW())",
                [
                    'user_id' => $payment['user_id'],
                    'message' => "Your payment of ₱" . number_format($payment['amount'], 2) . " has been approved. All your bookings are now confirmed." . ($points_earned > 0 ? " You earned $points_earned loyalty points!" : "")
                ]
            );
        }

        // For admin who approved
        $db->query(
            "INSERT INTO notifications (user_id, title, message, type, icon, created_at) 
         VALUES (:user_id, 'Payment Approved', :message, 'success', 'fa-check-circle', NOW())",
            [
                'user_id' => $_SESSION['user_id'],
                'message' => "Payment #{$payment['payment_reference']} approved. All unpaid bookings for user have been confirmed."
            ]
        );

        $db->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Payment approved successfully. All unpaid bookings/reservations have been confirmed.',
            'points_awarded' => $points_earned
        ]);
        exit();
    }

    // ==================== REJECT PAYMENT ====================
    elseif ($action === 'reject_payment') {
        $payment_id = intval($_POST['payment_id'] ?? 0);
        $reason = trim($_POST['reason'] ?? '');

        if (!$payment_id) {
            throw new Exception('Payment ID required');
        }

        if (empty($reason)) {
            throw new Exception('Rejection reason is required');
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

        if ($payment['approval_status'] !== 'pending') {
            throw new Exception('This payment has already been processed');
        }

        // 1. Update payment status ONLY - let the trigger handle balance
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

        // 2. DO NOT manually update current_balance - the trigger will handle it
        // The trigger update_balance_on_payment_update will run automatically
        // and move amount from pending back to available

        // 3. Create notifications
        // For customer
        if ($payment['user_id']) {
            $db->query(
                "INSERT INTO notifications (user_id, title, message, type, icon, link, created_at) 
             VALUES (:user_id, 'Payment Rejected', :message, 'warning', 'fa-times-circle', '/src/customer_portal/payments.php', NOW())",
                [
                    'user_id' => $payment['user_id'],
                    'message' => "Your payment of ₱" . number_format($payment['amount'], 2) . " has been rejected. Reason: $reason"
                ]
            );
        }

        // For admin who rejected
        $db->query(
            "INSERT INTO notifications (user_id, title, message, type, icon, created_at) 
         VALUES (:user_id, 'Payment Rejected', :message, 'warning', 'fa-times-circle', NOW())",
            [
                'user_id' => $_SESSION['user_id'],
                'message' => "Payment #{$payment['payment_reference']} has been rejected: $reason"
            ]
        );

        $db->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Payment rejected successfully'
        ]);
        exit();
    }

    // ==================== GET INVOICE DETAILS ====================
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

    // ==================== GET RESERVATION DETAILS ====================
    elseif ($action === 'get_reservation_details') {
        $reservation_id = intval($_POST['reservation_id'] ?? 0);

        if (!$reservation_id) {
            throw new Exception('Reservation ID required');
        }

        $reservation = $db->query(
            "SELECT 
                r.*,
                COALESCE(SUM(p.amount), 0) as paid_amount
             FROM restaurant_reservations r
             LEFT JOIN payments p ON r.id = p.booking_id AND p.booking_type = 'restaurant' AND p.payment_status = 'completed' AND p.approval_status = 'approved'
             WHERE r.id = :id
             GROUP BY r.id",
            ['id' => $reservation_id]
        )->fetch_one();

        if (!$reservation) {
            throw new Exception('Reservation not found');
        }

        // Get payment history
        $payments = $db->query(
            "SELECT * FROM payments WHERE booking_id = :booking_id AND booking_type = 'restaurant' ORDER BY created_at DESC",
            ['booking_id' => $reservation_id]
        )->find() ?: [];

        echo json_encode([
            'success' => true,
            'reservation' => $reservation,
            'payments' => $payments,
            'balance' => $reservation['down_payment'] - $reservation['paid_amount']
        ]);
        exit();
    }

    // ==================== SEND REMINDER ====================
    elseif ($action === 'send_reminder') {
        $booking_id = intval($_POST['booking_id'] ?? 0);
        $booking_type = $_POST['booking_type'] ?? 'hotel';

        if (!$booking_id) {
            throw new Exception('Booking ID required');
        }

        if ($booking_type === 'hotel') {
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
        } else {
            $reservation = $db->query(
                "SELECT r.*, u.id as user_id 
                 FROM restaurant_reservations r
                 LEFT JOIN users u ON r.user_id = u.id
                 WHERE r.id = :id",
                ['id' => $booking_id]
            )->fetch_one();

            if (!$reservation) {
                throw new Exception('Reservation not found');
            }

            if ($reservation['user_id']) {
                $db->query(
                    "INSERT INTO notifications (user_id, title, message, type, icon, link, created_at) 
                     VALUES (:user_id, 'Payment Reminder', :message, 'warning', 'fa-clock', '/src/customer_portal/payments.php', NOW())",
                    [
                        'user_id' => $reservation['user_id'],
                        'message' => "This is a reminder that your down payment of ₱" . number_format($reservation['down_payment'], 2) . " is due on " . date('M d, Y', strtotime($reservation['reservation_date']))
                    ]
                );
            }
        }

        // Create notification for admin
        $db->query(
            "INSERT INTO notifications (user_id, title, message, type, icon, created_at) 
             VALUES (:user_id, 'Reminder Sent', :message, 'info', 'fa-bell', NOW())",
            [
                'user_id' => $_SESSION['user_id'],
                'message' => "Payment reminder sent for " . ($booking_type === 'hotel' ? 'invoice' : 'reservation') . " #" . $booking_id
            ]
        );

        echo json_encode([
            'success' => true,
            'message' => 'Reminder sent successfully'
        ]);
        exit();
    }

    // ==================== VOID INVOICE ====================
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

    // ==================== APPLY FILTER ====================
    elseif ($action === 'apply_filter') {
        $status = $_POST['status'] ?? 'all';
        $type = $_POST['type'] ?? 'all';
        $search = $_POST['search'] ?? '';

        $params = [];
        if ($status !== 'all')
            $params[] = "status=$status";
        if ($type !== 'all')
            $params[] = "type=$type";
        if (!empty($search))
            $params[] = "search=" . urlencode($search);

        $queryString = !empty($params) ? '?' . implode('&', $params) : '';

        echo json_encode([
            'success' => true,
            'redirect' => $queryString
        ]);
        exit();
    }

    // ==================== EXPORT INVOICES ====================
    elseif ($action === 'export_invoices') {
        $format = $_POST['format'] ?? 'csv';
        $filter = $_POST['filter'] ?? 'all';
        $type = $_POST['type'] ?? 'all';

        $hotelWhere = "1=1";
        $restaurantWhere = "1=1";

        if ($filter === 'paid') {
            $hotelWhere .= " AND payment_status = 'paid'";
            $restaurantWhere .= " AND payment_status = 'paid'";
        } elseif ($filter === 'pending') {
            $hotelWhere .= " AND payment_status = 'unpaid' AND check_out >= CURDATE()";
            $restaurantWhere .= " AND payment_status = 'unpaid' AND reservation_date >= CURDATE()";
        } elseif ($filter === 'overdue') {
            $hotelWhere .= " AND payment_status = 'unpaid' AND check_out < CURDATE()";
            $restaurantWhere .= " AND payment_status = 'unpaid' AND reservation_date < CURDATE()";
        }

        $allInvoices = [];

        if ($type === 'all' || $type === 'hotel') {
            $hotelInvoices = $db->query(
                "SELECT 
                    'Hotel' as type,
                    booking_reference as reference,
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
                 WHERE $hotelWhere
                 ORDER BY created_at DESC",
                []
            )->find() ?: [];
            $allInvoices = array_merge($allInvoices, $hotelInvoices);
        }

        if ($type === 'all' || $type === 'restaurant') {
            $restaurantInvoices = $db->query(
                "SELECT 
                    'Restaurant' as type,
                    reservation_reference as reference,
                    CONCAT(guest_first_name, ' ', guest_last_name) as guest_name,
                    guest_email,
                    guest_phone,
                    DATE(created_at) as invoice_date,
                    reservation_date as due_date,
                    down_payment as total_amount,
                    payment_status,
                    payment_method,
                    payment_date
                 FROM restaurant_reservations
                 WHERE $restaurantWhere
                 ORDER BY created_at DESC",
                []
            )->find() ?: [];
            $allInvoices = array_merge($allInvoices, $restaurantInvoices);
        }

        // Sort by date descending
        usort($allInvoices, function ($a, $b) {
            return strtotime($b['invoice_date']) - strtotime($a['invoice_date']);
        });

        if ($format === 'csv') {
            $filename = 'invoices_export_' . date('Y-m-d') . '.csv';
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="' . $filename . '"');

            $output = fopen('php://output', 'w');

            // Headers
            if (!empty($allInvoices)) {
                fputcsv($output, array_keys($allInvoices[0]));

                // Data
                foreach ($allInvoices as $row) {
                    fputcsv($output, $row);
                }
            }
            fclose($output);
            exit();
        } else {
            header('Content-Type: application/json');
            header('Content-Disposition: attachment; filename="invoices_export_' . date('Y-m-d') . '.json"');
            echo json_encode($allInvoices, JSON_PRETTY_PRINT);
            exit();
        }
    }

    // ==================== GENERATE REPORT ====================
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
                SUM(amount) as total_amount,
                SUM(CASE WHEN booking_type = 'hotel' THEN amount ELSE 0 END) as hotel_amount,
                SUM(CASE WHEN booking_type = 'restaurant' THEN amount ELSE 0 END) as restaurant_amount
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
    error_log('Billing Actions Error: ' . $e->getMessage());
    error_log('POST Data: ' . print_r($_POST, true));

    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
    exit();
}
?>