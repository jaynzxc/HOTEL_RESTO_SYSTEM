<?php
/**
 * POST Controller - Admin Staff Actions
 * Handles local staff management (table assignments, notes, etc.)
 */

session_start();
require_once __DIR__ . '/../../../../Class/Database.php';

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

$config = require __DIR__ . '/../../../../config/config.php';
$db = new Database($config['database']);

// Get user role from database
$user = $db->query(
    "SELECT role FROM users WHERE id = :id",
    ['id' => $_SESSION['user_id']]
)->fetch_one();

// Check if user has admin or staff role
if (!$user || !in_array($user['role'], ['admin', 'staff'])) {
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

    // ASSIGN TABLES (local only)
    if ($action === 'assign_tables') {
        $employee_id = trim($_POST['employee_id'] ?? '');
        $tables = trim($_POST['tables'] ?? '');

        if (empty($employee_id) || empty($tables)) {
            throw new Exception('Employee ID and table assignment required');
        }

        // Create staff_assignments table if it doesn't exist
        $db->query("CREATE TABLE IF NOT EXISTS staff_assignments (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            employee_id VARCHAR(50) NOT NULL,
            assigned_tables VARCHAR(255),
            assigned_by INT UNSIGNED,
            assigned_date DATETIME,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY unique_employee (employee_id)
        )");

        // Store in local database
        $db->query(
            "INSERT INTO staff_assignments (employee_id, assigned_tables, assigned_by, assigned_date)
             VALUES (:emp_id, :tables, :user_id, NOW())
             ON DUPLICATE KEY UPDATE
             assigned_tables = :tables, assigned_by = :user_id, updated_at = NOW()",
            [
                'emp_id' => $employee_id,
                'tables' => $tables,
                'user_id' => $_SESSION['user_id']
            ]
        );

        echo json_encode([
            'success' => true,
            'message' => 'Tables assigned successfully'
        ]);
        exit();
    }

    // ADD NOTE TO STAFF
    elseif ($action === 'add_note') {
        $employee_id = trim($_POST['employee_id'] ?? '');
        $note = trim($_POST['note'] ?? '');

        if (empty($employee_id) || empty($note)) {
            throw new Exception('Employee ID and note required');
        }

        // Create staff_notes table if it doesn't exist
        $db->query("CREATE TABLE IF NOT EXISTS staff_notes (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            employee_id VARCHAR(50) NOT NULL,
            note TEXT NOT NULL,
            created_by INT UNSIGNED,
            created_at DATETIME,
            KEY employee_id (employee_id)
        )");

        $db->query(
            "INSERT INTO staff_notes (employee_id, note, created_by, created_at)
             VALUES (:emp_id, :note, :user_id, NOW())",
            [
                'emp_id' => $employee_id,
                'note' => $note,
                'user_id' => $_SESSION['user_id']
            ]
        );

        echo json_encode([
            'success' => true,
            'message' => 'Note added successfully'
        ]);
        exit();
    }

    // GET STAFF NOTES
    elseif ($action === 'get_notes') {
        $employee_id = trim($_POST['employee_id'] ?? '');

        if (empty($employee_id)) {
            throw new Exception('Employee ID required');
        }

        // Create table if not exists
        $db->query("CREATE TABLE IF NOT EXISTS staff_notes (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            employee_id VARCHAR(50) NOT NULL,
            note TEXT NOT NULL,
            created_by INT UNSIGNED,
            created_at DATETIME,
            KEY employee_id (employee_id)
        )");

        $notes = $db->query(
            "SELECT * FROM staff_notes 
             WHERE employee_id = :emp_id 
             ORDER BY created_at DESC 
             LIMIT 20",
            ['emp_id' => $employee_id]
        )->find() ?: [];

        echo json_encode([
            'success' => true,
            'notes' => $notes
        ]);
        exit();
    }

    // GET TABLE ASSIGNMENT
    elseif ($action === 'get_table_assignment') {
        $employee_id = trim($_POST['employee_id'] ?? '');

        if (empty($employee_id)) {
            throw new Exception('Employee ID required');
        }

        // Create table if not exists
        $db->query("CREATE TABLE IF NOT EXISTS staff_assignments (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            employee_id VARCHAR(50) NOT NULL,
            assigned_tables VARCHAR(255),
            assigned_by INT UNSIGNED,
            assigned_date DATETIME,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY unique_employee (employee_id)
        )");

        $assignment = $db->query(
            "SELECT * FROM staff_assignments WHERE employee_id = :emp_id",
            ['emp_id' => $employee_id]
        )->fetch_one();

        echo json_encode([
            'success' => true,
            'assignment' => $assignment
        ]);
        exit();
    }

    // REFRESH FROM HR API
    elseif ($action === 'refresh_hr_data') {
        echo json_encode([
            'success' => true,
            'message' => 'Refreshing data from HR system...'
        ]);
        exit();
    } else {
        throw new Exception('Invalid action');
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
exit();
?>