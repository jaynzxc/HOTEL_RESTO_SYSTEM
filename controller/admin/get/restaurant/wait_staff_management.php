<?php
/**
 * GET Controller - Admin Wait Staff Management
 * Handles fetching staff members from HR API with ratings
 */

session_start();
require_once __DIR__ . '/../../../../Class/Database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !$_SESSION['logged_in']) {
    header('Location: ../../../../src/login-register/login_form.php');
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
    header('Location: ../../view/customer_portal/dashboard.php');
    exit();
}

// Get admin user data
$admin = $db->query(
    "SELECT id, full_name, first_name, last_name, email, role, avatar
     FROM users WHERE id = :id",
    ['id' => $_SESSION['user_id']]
)->fetch_one();

// Create staff_notes table if it doesn't exist
$db->query("CREATE TABLE IF NOT EXISTS staff_notes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employee_id VARCHAR(50) NOT NULL,
    note TEXT NOT NULL,
    rating TINYINT(1) DEFAULT NULL,
    rating_type ENUM('performance','attitude','punctuality','overall') DEFAULT 'overall',
    created_by INT UNSIGNED,
    created_at DATETIME,
    KEY employee_id (employee_id),
    KEY created_by (created_by)
)");

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

// HR API Configuration
define('HR_API_BASE', 'https://humanresource.up.railway.app/api');
define('HR_API_KEY', 'core_system_2026_key_54321');

// Function to call HR API with better error handling
function callHrApi($endpoint, $params = [])
{
    $url = HR_API_BASE . $endpoint . '?api_key=' . HR_API_KEY;

    foreach ($params as $key => $value) {
        if (!empty($value)) {
            $url .= '&' . urlencode($key) . '=' . urlencode($value);
        }
    }

    error_log("Calling HR API: " . $url);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    error_log("HR API Response Code: " . $httpCode);

    if ($curlError) {
        error_log("HR API CURL Error: " . $curlError);
        return null;
    }

    if ($httpCode !== 200) {
        error_log("HR API Error: HTTP $httpCode - " . substr($response, 0, 500));
        return null;
    }

    $decoded = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("HR API JSON Error: " . json_last_error_msg());
        error_log("Response preview: " . substr($response, 0, 500));
        return null;
    }

    return $decoded;
}

// Set timezone to Asia/Manila
date_default_timezone_set('Asia/Manila');

// Get filter parameters
$department = isset($_GET['department']) ? $_GET['department'] : '';
$statusFilter = isset($_GET['status']) ? $_GET['status'] : 'all';
$searchFilter = isset($_GET['search']) ? $_GET['search'] : '';
$date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

error_log("Using local date: " . $date);

// Try to get staff from different departments
$departmentsToTry = ['Restaurant', 'Food and Beverage', 'F&B', 'Kitchen', 'Service'];
$allStaff = [];
$hrData = null;

foreach ($departmentsToTry as $dept) {
    $apiParams = [
        'department' => $dept,
        'date' => $date
    ];

    $hrData = callHrApi('/employee-attendance.php', $apiParams);

    if ($hrData && isset($hrData['data']['employees']) && !empty($hrData['data']['employees'])) {
        error_log("Found data for department: " . $dept);
        $allStaff = $hrData['data']['employees'];
        break;
    }
}

// If still no data, try without department filter
if (empty($allStaff)) {
    error_log("Trying without department filter");
    $hrData = callHrApi('/employee-attendance.php', ['date' => $date]);
    if ($hrData && isset($hrData['data']['employees'])) {
        $allStaff = $hrData['data']['employees'];
    }
}

// Process staff data and fetch ratings from local DB
$restaurantStaff = [];

if (!empty($allStaff)) {
    foreach ($allStaff as $staff) {
        $emp = $staff['employee'] ?? [];
        $dept = strtolower($emp['department'] ?? '');
        $position = strtolower($emp['position'] ?? '');
        $employeeNumber = $emp['employee_number'] ?? '';

        // Include if related to restaurant/food service
        if (
            strpos($dept, 'restaurant') !== false ||
            strpos($dept, 'food') !== false ||
            strpos($dept, 'beverage') !== false ||
            strpos($dept, 'kitchen') !== false ||
            strpos($dept, 'service') !== false ||
            strpos($position, 'waiter') !== false ||
            strpos($position, 'server') !== false ||
            strpos($position, 'bartender') !== false ||
            strpos($position, 'chef') !== false ||
            strpos($position, 'cook') !== false
        ) {
            // Get average rating from local database
            $avgRating = $db->query(
                "SELECT AVG(rating) as avg_rating, COUNT(*) as rating_count 
                 FROM staff_notes 
                 WHERE employee_id = :emp_id 
                 AND rating IS NOT NULL",
                ['emp_id' => $employeeNumber]
            )->fetch_one();

            $staff['avg_rating'] = $avgRating['avg_rating'] ? round($avgRating['avg_rating'], 1) : null;
            $staff['rating_count'] = $avgRating['rating_count'] ?? 0;

            // Get table assignments
            $assignment = $db->query(
                "SELECT assigned_tables FROM staff_assignments WHERE employee_id = :emp_id",
                ['emp_id' => $employeeNumber]
            )->fetch_one();

            $staff['assigned_tables'] = $assignment['assigned_tables'] ?? null;

            $restaurantStaff[] = $staff;
        }
    }
}

// If still no restaurant staff, use first 10 employees as sample
if (empty($restaurantStaff) && !empty($allStaff)) {
    $restaurantStaff = array_slice($allStaff, 0, 10);
}

// Apply search filter
if (!empty($searchFilter) && !empty($restaurantStaff)) {
    $filteredStaff = [];
    foreach ($restaurantStaff as $staff) {
        $emp = $staff['employee'] ?? [];
        if (
            stripos($emp['full_name'] ?? '', $searchFilter) !== false ||
            stripos($emp['position'] ?? '', $searchFilter) !== false ||
            stripos($emp['department'] ?? '', $searchFilter) !== false
        ) {
            $filteredStaff[] = $staff;
        }
    }
    $restaurantStaff = $filteredStaff;
}

// Apply status filter
if ($statusFilter !== 'all' && !empty($restaurantStaff)) {
    $filteredStaff = [];
    foreach ($restaurantStaff as $staff) {
        $status = $staff['status']['status'] ?? 'absent';
        if ($status === $statusFilter) {
            $filteredStaff[] = $staff;
        }
    }
    $restaurantStaff = $filteredStaff;
}

// Calculate pagination
$totalStaff = count($restaurantStaff);
$totalPages = $totalStaff > 0 ? ceil($totalStaff / $limit) : 1;
$paginatedStaff = $totalStaff > 0 ? array_slice($restaurantStaff, $offset, $limit) : [];

// Get recent ratings for the dashboard
$recentRatings = $db->query(
    "SELECT 
        n.*,
        u.full_name as created_by_name,
        n.employee_id,
        n.rating,
        n.rating_type,
        n.note,
        n.created_at
     FROM staff_notes n
     LEFT JOIN users u ON n.created_by = u.id
     WHERE n.rating IS NOT NULL
     ORDER BY n.created_at DESC
     LIMIT 10",
    []
)->find() ?: [];

// Get top rated staff (average rating > 4)
$topRatedStaff = $db->query(
    "SELECT 
        employee_id,
        AVG(rating) as avg_rating,
        COUNT(*) as total_ratings,
        MAX(created_at) as last_rated
     FROM staff_notes 
     WHERE rating IS NOT NULL
     GROUP BY employee_id
     HAVING avg_rating >= 4.0
     ORDER BY avg_rating DESC, total_ratings DESC
     LIMIT 5",
    []
)->find() ?: [];

// Get staff with most ratings
$mostRatedStaff = $db->query(
    "SELECT 
        employee_id,
        COUNT(*) as rating_count,
        AVG(rating) as avg_rating
     FROM staff_notes 
     WHERE rating IS NOT NULL
     GROUP BY employee_id
     ORDER BY rating_count DESC, avg_rating DESC
     LIMIT 5",
    []
)->find() ?: [];

// Get statistics from HR API summary or calculate from our data
if ($hrData && isset($hrData['data']['summary'])) {
    $summary = $hrData['data']['summary'];
} else {
    // Calculate summary from our filtered data
    $present = 0;
    $absent = 0;
    $late = 0;
    $no_schedule = 0;
    $with_attendance = 0;

    foreach ($restaurantStaff as $staff) {
        if ($staff['status']['present'])
            $present++;
        if (!$staff['status']['present'] && $staff['status']['has_schedule'])
            $absent++;
        if ($staff['status']['is_late'])
            $late++;
        if (!$staff['status']['has_schedule'])
            $no_schedule++;
        if ($staff['status']['has_attendance'])
            $with_attendance++;
    }

    $summary = [
        'total_employees' => $totalStaff,
        'present' => $present,
        'absent' => $absent,
        'late' => $late,
        'no_schedule' => $no_schedule,
        'with_attendance' => $with_attendance,
        'completion_rate' => $totalStaff > 0 ? round(($with_attendance / $totalStaff) * 100, 2) : 0
    ];
}

// Process shift data for schedule summary
$morningStaff = [];
$afternoonStaff = [];
$eveningStaff = [];

foreach ($restaurantStaff as $staff) {
    $shift = $staff['shift'] ?? null;
    $emp = $staff['employee'] ?? [];

    if ($shift && isset($shift['start_time'])) {
        $startTime = $shift['start_time'];
        $hour = 0;

        if (is_string($startTime)) {
            if (strpos($startTime, 'AM') !== false || strpos($startTime, 'PM') !== false) {
                $hour = date('H', strtotime($startTime));
            } else {
                $hour = (int) substr($startTime, 0, 2);
            }

            if ($hour >= 5 && $hour < 12) {
                $morningStaff[] = $emp['full_name'] ?? explode(' ', $emp['full_name'] ?? '')[0];
            } elseif ($hour >= 12 && $hour < 17) {
                $afternoonStaff[] = $emp['full_name'] ?? explode(' ', $emp['full_name'] ?? '')[0];
            } elseif ($hour >= 17 || $hour < 5) {
                $eveningStaff[] = $emp['full_name'] ?? explode(' ', $emp['full_name'] ?? '')[0];
            }
        }
    }
}

$shiftSummary = [
    'morning_count' => count($morningStaff),
    'morning_staff' => implode(', ', array_slice($morningStaff, 0, 3)) . (count($morningStaff) > 3 ? '...' : ''),
    'afternoon_count' => count($afternoonStaff),
    'afternoon_staff' => implode(', ', array_slice($afternoonStaff, 0, 3)) . (count($afternoonStaff) > 3 ? '...' : ''),
    'evening_count' => count($eveningStaff),
    'evening_staff' => implode(', ', array_slice($eveningStaff, 0, 3)) . (count($eveningStaff) > 3 ? '...' : '')
];

// Calculate tables assigned from local DB
$tablesAssigned = $db->query(
    "SELECT COUNT(DISTINCT employee_id) as assigned_count FROM staff_assignments"
)->fetch_one();
$tablesAssigned = $tablesAssigned['assigned_count'] ?? 0;
$totalTables = 24;

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
    'staffMembers' => $paginatedStaff,
    'allStaff' => $restaurantStaff,
    'recentRatings' => $recentRatings,
    'topRatedStaff' => $topRatedStaff,
    'mostRatedStaff' => $mostRatedStaff,
    'shiftSummary' => $shiftSummary,
    'summary' => $summary,
    'tablesAssigned' => $tablesAssigned,
    'totalTables' => $totalTables,
    'totalStaff' => $totalStaff,
    'totalPages' => $totalPages,
    'currentPage' => $page,
    'statusFilter' => $statusFilter,
    'searchFilter' => $searchFilter,
    'selectedDate' => $date,
    'selectedDepartment' => $department,
    'today' => date('F j, Y'),
    'todayDay' => date('j'),
    'todayMonth' => date('F'),
    'hrApiConnected' => ($hrData !== null)
];

// Extract variables for view
extract($viewData);
?>