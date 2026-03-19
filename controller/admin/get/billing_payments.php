<?php
/**
 * GET Controller - Admin Billing & Payments
 * Handles fetching all billing and payment data
 */

session_start();
require_once __DIR__ . '/../../../Class/Database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !$_SESSION['logged_in']) {
    header('Location: ../../view/auth/login.php');
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
    header('Location: ../../view/customer_portal/dashboard.php');
    exit();
}

// Get admin user data
$admin = $db->query(
    "SELECT id, full_name, first_name, last_name, email, role, avatar
     FROM users WHERE id = :id",
    ['id' => $_SESSION['user_id']]
)->fetch_one();

// Get filter parameters
$statusFilter = isset($_GET['status']) ? $_GET['status'] : 'all';
$dateFilter = isset($_GET['date']) ? $_GET['date'] : '';
$searchFilter = isset($_GET['search']) ? $_GET['search'] : '';

// STATISTICS CARDS
$today = date('Y-m-d');
$firstDayOfMonth = date('Y-m-01');
$lastDayOfMonth = date('Y-m-t');

// Today's revenue (only approved payments)
$todayRevenue = $db->query(
    "SELECT COALESCE(SUM(amount), 0) as total 
     FROM payments 
     WHERE DATE(created_at) = :today 
        AND payment_status = 'completed' 
        AND approval_status = 'approved'",
    ['today' => $today]
)->fetch_one()['total'] ?? 0;

// Pending payments (payments pending approval)
$pendingPayments = $db->query(
    "SELECT COALESCE(SUM(amount), 0) as total 
     FROM payments 
     WHERE payment_status = 'pending' AND approval_status = 'pending'",
    []
)->fetch_one()['total'] ?? 0;

// Overdue payments (bookings with check_out < today and unpaid)
$overduePayments = $db->query(
    "SELECT COALESCE(SUM(b.total_amount), 0) as total 
     FROM bookings b
     WHERE b.payment_status = 'unpaid' 
        AND b.check_out < :today 
        AND b.status != 'cancelled'",
    ['today' => $today]
)->fetch_one()['total'] ?? 0;

// Transactions today (all payment attempts)
$transactionsToday = $db->query(
    "SELECT COUNT(*) as count 
     FROM payments 
     WHERE DATE(created_at) = :today",
    ['today' => $today]
)->fetch_one()['count'] ?? 0;

// Monthly total (only approved payments)
$monthlyTotal = $db->query(
    "SELECT COALESCE(SUM(amount), 0) as total 
     FROM payments 
     WHERE DATE(created_at) BETWEEN :start AND :end 
        AND payment_status = 'completed' 
        AND approval_status = 'approved'",
    ['start' => $firstDayOfMonth, 'end' => $lastDayOfMonth]
)->fetch_one()['total'] ?? 0;

// PAYMENT METHOD BREAKDOWN (only approved payments)
$paymentMethods = $db->query(
    "SELECT 
        payment_method,
        COALESCE(SUM(amount), 0) as total,
        COUNT(*) as count,
        ROUND(SUM(amount) * 100.0 / (SELECT COALESCE(SUM(amount), 1) FROM payments WHERE payment_status = 'completed' AND approval_status = 'approved'), 1) as percentage
     FROM payments 
     WHERE payment_status = 'completed' AND approval_status = 'approved'
     GROUP BY payment_method
     ORDER BY total DESC",
    []
)->find() ?: [];

// Ensure all methods are represented
$methodColors = [
    'GCash' => ['bg' => 'bg-blue-50', 'border' => 'border-blue-200', 'icon' => 'fa-brands fa-gcash', 'color' => 'text-blue-600'],
    'Visa' => ['bg' => 'bg-green-50', 'border' => 'border-green-200', 'icon' => 'fa-brands fa-cc-visa', 'color' => 'text-green-600'],
    'Mastercard' => ['bg' => 'bg-green-50', 'border' => 'border-green-200', 'icon' => 'fa-brands fa-cc-mastercard', 'color' => 'text-green-600'],
    'Credit card' => ['bg' => 'bg-green-50', 'border' => 'border-green-200', 'icon' => 'fas fa-credit-card', 'color' => 'text-green-600'],
    'Cash' => ['bg' => 'bg-amber-50', 'border' => 'border-amber-200', 'icon' => 'fa-solid fa-money-bill-wave', 'color' => 'text-amber-600'],
    'Bank transfer' => ['bg' => 'bg-purple-50', 'border' => 'border-purple-200', 'icon' => 'fas fa-building', 'color' => 'text-purple-600']
];

// Build WHERE clause for invoices - FIXED: Added table alias for payment_status
$whereConditions = ["1=1"];

if ($statusFilter !== 'all') {
    if ($statusFilter === 'paid') {
        $whereConditions[] = "b.payment_status = 'paid'";
    } elseif ($statusFilter === 'pending') {
        $whereConditions[] = "b.payment_status = 'unpaid' AND b.check_out >= CURDATE()";
    } elseif ($statusFilter === 'overdue') {
        $whereConditions[] = "b.payment_status = 'unpaid' AND b.check_out < CURDATE()";
    } elseif ($statusFilter === 'partial') {
        // Partial payments - check if payments exist but total < amount
        $whereConditions[] = "EXISTS (SELECT 1 FROM payments p WHERE p.booking_id = b.id AND p.booking_type = 'hotel') AND b.payment_status != 'paid'";
    }
}

if (!empty($searchFilter)) {
    $searchFilter = $db->escape($searchFilter);
    $whereConditions[] = "(b.booking_reference LIKE '%$searchFilter%' OR b.guest_first_name LIKE '%$searchFilter%' OR b.guest_last_name LIKE '%$searchFilter%')";
}

$whereClause = implode(' AND ', $whereConditions);

// INVOICES - combining bookings and payments
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Get total count
$countResult = $db->query(
    "SELECT COUNT(*) as total FROM bookings b WHERE $whereClause",
    []
)->fetch_one();
$totalInvoices = $countResult['total'];
$totalPages = ceil($totalInvoices / $limit);

$invoices = $db->query(
    "SELECT 
        b.id,
        b.booking_reference as invoice_number,
        CONCAT(b.guest_first_name, ' ', b.guest_last_name) as guest_name,
        b.guest_email,
        b.guest_phone,
        b.created_at as invoice_date,
        b.check_out as due_date,
        b.total_amount as amount,
        b.payment_status as status,
        b.payment_method,
        b.payment_date,
        COALESCE((SELECT SUM(amount) FROM payments WHERE booking_id = b.id AND booking_type = 'hotel' AND payment_status = 'completed' AND approval_status = 'approved'), 0) as paid_amount,
        (SELECT COUNT(*) FROM payments WHERE booking_id = b.id AND booking_type = 'hotel') as payment_count,
        p.id as payment_id,
        p.payment_reference,
        p.approval_status
     FROM bookings b
     LEFT JOIN payments p ON b.id = p.booking_id AND p.booking_type = 'hotel' AND p.payment_status = 'completed' AND p.approval_status = 'approved'
     WHERE $whereClause
     ORDER BY b.created_at DESC
     LIMIT $limit OFFSET $offset",
    []
)->find() ?: [];

// RECENT TRANSACTIONS (only approved payments)
$recentTransactions = $db->query(
    "SELECT 
        p.id,
        p.payment_reference,
        p.amount,
        p.payment_method,
        p.payment_status,
        p.approval_status,
        p.created_at,
        CONCAT(b.guest_first_name, ' ', b.guest_last_name) as guest_name,
        b.booking_reference as invoice_number
     FROM payments p
     LEFT JOIN bookings b ON p.booking_id = b.id AND p.booking_type = 'hotel'
     WHERE p.payment_status = 'completed' AND p.approval_status = 'approved'
     ORDER BY p.created_at DESC
     LIMIT 10",
    []
)->find() ?: [];

// PENDING APPROVALS
$pendingApprovals = $db->query(
    "SELECT 
        p.id,
        p.payment_reference,
        p.amount,
        p.payment_method,
        p.created_at,
        CONCAT(b.guest_first_name, ' ', b.guest_last_name) as guest_name,
        b.booking_reference as invoice_number
     FROM payments p
     LEFT JOIN bookings b ON p.booking_id = b.id AND p.booking_type = 'hotel'
     WHERE p.payment_status = 'pending' AND p.approval_status = 'pending'
     ORDER BY p.created_at DESC
     LIMIT 5",
    []
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

// Get unread notifications count
try {
    $unread_result = $db->query(
        "SELECT COUNT(*) as count FROM notifications 
         WHERE user_id = :user_id AND is_read = 0",
        ['user_id' => $_SESSION['user_id']]
    )->fetch_one();
    $unread_count = $unread_result['count'] ?? 0;
} catch (Exception $e) {
    $unread_count = 0;
}

// Store data for view
$viewData = [
    'admin' => $admin,
    'initials' => $initials,
    'todayRevenue' => $todayRevenue,
    'pendingPayments' => $pendingPayments,
    'overduePayments' => $overduePayments,
    'transactionsToday' => $transactionsToday,
    'monthlyTotal' => $monthlyTotal,
    'paymentMethods' => $paymentMethods,
    'methodColors' => $methodColors,
    'invoices' => $invoices,
    'recentTransactions' => $recentTransactions,
    'pendingApprovals' => $pendingApprovals,
    'currentPage' => $page,
    'totalPages' => $totalPages,
    'totalInvoices' => $totalInvoices,
    'statusFilter' => $statusFilter,
    'searchFilter' => $searchFilter,
    'unread_count' => $unread_count,
    'today' => date('F j, Y')
];

// Extract variables for view
extract($viewData);
?>