<?php
/**
 * POST Controller - Export Reports
 * Handles exporting booking reports in various formats
 */

session_start();
require_once __DIR__ . '/../../../../Class/Database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !$_SESSION['logged_in']) {
    header('Location: ../../view/auth/login.php');
    exit();
}

$config = require __DIR__ . '/../../../../config/config.php';
$db = new Database($config['database']);

// Get parameters
$type = $_GET['type'] ?? 'bookings';
$format = $_GET['format'] ?? 'csv';
$start_date = $_GET['start'] ?? date('Y-m-d', strtotime('-30 days'));
$end_date = $_GET['end'] ?? date('Y-m-d');

// Get data based on type
if ($type === 'bookings') {
    $data = $db->query(
        "SELECT 
            booking_reference,
            CONCAT(guest_first_name, ' ', guest_last_name) as guest_name,
            guest_email,
            guest_phone,
            room_name,
            check_in,
            check_out,
            nights,
            total_amount,
            status,
            payment_status,
            created_at
         FROM bookings 
         WHERE created_at BETWEEN :start_date AND :end_date + INTERVAL 1 DAY
         ORDER BY created_at DESC",
        ['start_date' => $start_date, 'end_date' => $end_date]
    )->find() ?: [];
}

// Export based on format
if ($format === 'csv') {
    $filename = 'bookings_export_' . date('Y-m-d') . '.csv';
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    $output = fopen('php://output', 'w');

    // Headers
    if (!empty($data)) {
        fputcsv($output, array_keys($data[0]));

        // Data
        foreach ($data as $row) {
            fputcsv($output, $row);
        }
    }
    fclose($output);
    exit();
} elseif ($format === 'json') {
    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="bookings_export_' . date('Y-m-d') . '.json"');
    echo json_encode($data, JSON_PRETTY_PRINT);
    exit();
} elseif ($format === 'excel') {
    // For Excel, we'll use CSV with .xls extension
    $filename = 'bookings_export_' . date('Y-m-d') . '.xls';
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    echo "<table border='1'>";
    // Headers
    if (!empty($data)) {
        echo "<tr>";
        foreach (array_keys($data[0]) as $header) {
            echo "<th>" . ucwords(str_replace('_', ' ', $header)) . "</th>";
        }
        echo "</tr>";

        // Data
        foreach ($data as $row) {
            echo "<tr>";
            foreach ($row as $cell) {
                echo "<td>" . htmlspecialchars($cell) . "</td>";
            }
            echo "</tr>";
        }
    }
    echo "</table>";
    exit();
} elseif ($format === 'pdf') {
    // For PDF, we'll redirect to a PDF generation service or use dompdf
    // This is a placeholder - you'd need to implement PDF generation
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit();
}
?>