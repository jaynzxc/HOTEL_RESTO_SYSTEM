<?php
/**
 * API - Staff Ratings
 * Endpoint to get staff ratings and averages
 * NO AUTHENTICATION REQUIRED - Open API for HR system
 * 
 * Usage:
 * - GET /src/api/staff-ratings.php - Get all ratings with averages
 * - GET /src/api/staff-ratings.php?employee_id=EMP-001 - Get ratings for specific employee
 * - GET /src/api/staff-ratings.php?recent=10 - Get most recent ratings
 * - GET /src/api/staff-ratings.php?top=5 - Get top rated staff
 * - GET /src/api/staff-ratings.php?rating_type=performance - Filter by rating type
 * - GET /src/api/staff-ratings.php?start_date=2026-03-01&end_date=2026-03-31 - Date range filter
 */

// Set headers for API - NO AUTHENTICATION REQUIRED
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Expose-Headers: *');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed. Only GET requests are accepted.',
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    exit();
}

// Include database connection
require_once __DIR__ . '/../../Class/Database.php';

// Load configuration
$config = require __DIR__ . '/../../config/config.php';
$db = new Database($config['database']);

// Create tables if they don't exist (for first-time setup)
try {
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

    $db->query("CREATE TABLE IF NOT EXISTS staff_assignments (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        employee_id VARCHAR(50) NOT NULL,
        assigned_tables VARCHAR(255),
        assigned_by INT UNSIGNED,
        assigned_date DATETIME,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY unique_employee (employee_id)
    )");
} catch (Exception $e) {
    // Tables might already exist, continue
}

// Get query parameters
$employee_id = isset($_GET['employee_id']) ? trim($_GET['employee_id']) : null;
$recent = isset($_GET['recent']) ? (int) $_GET['recent'] : null;
$top = isset($_GET['top']) ? (int) $_GET['top'] : null;
$rating_type = isset($_GET['rating_type']) ? $_GET['rating_type'] : null;
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : null;
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : null;

try {
    // Response structure
    $response = [
        'success' => true,
        'data' => [],
        'metadata' => [
            'timestamp' => date('Y-m-d H:i:s'),
            'timezone' => date_default_timezone_get(),
            'endpoint' => 'staff-ratings'
        ]
    ];

    // Case 1: Get ratings for specific employee
    if ($employee_id) {
        // Build query for specific employee
        $query = "SELECT 
                    n.id,
                    n.employee_id,
                    n.note,
                    n.rating,
                    n.rating_type,
                    n.created_by,
                    n.created_at,
                    u.full_name as created_by_name
                  FROM staff_notes n
                  LEFT JOIN users u ON n.created_by = u.id
                  WHERE n.employee_id = :employee_id";

        $params = ['employee_id' => $employee_id];

        // Add rating type filter if specified
        if ($rating_type) {
            $query .= " AND n.rating_type = :rating_type";
            $params['rating_type'] = $rating_type;
        }

        // Add date range filters
        if ($start_date) {
            $query .= " AND DATE(n.created_at) >= :start_date";
            $params['start_date'] = $start_date;
        }
        if ($end_date) {
            $query .= " AND DATE(n.created_at) <= :end_date";
            $params['end_date'] = $end_date;
        }

        $query .= " ORDER BY n.created_at DESC";

        $ratings = $db->query($query, $params)->find() ?: [];

        // Get average rating for this employee
        $avgQuery = "SELECT 
                        AVG(rating) as avg_rating,
                        COUNT(*) as total_ratings,
                        COUNT(DISTINCT rating_type) as rating_types_count
                     FROM staff_notes 
                     WHERE employee_id = :employee_id 
                     AND rating IS NOT NULL";

        $avgParams = ['employee_id' => $employee_id];

        if ($rating_type) {
            $avgQuery .= " AND rating_type = :rating_type";
            $avgParams['rating_type'] = $rating_type;
        }

        $averages = $db->query($avgQuery, $avgParams)->fetch_one();

        // Get ratings breakdown by type
        $breakdown = $db->query(
            "SELECT 
                rating_type,
                AVG(rating) as avg_rating,
                COUNT(*) as count
             FROM staff_notes 
             WHERE employee_id = :employee_id 
             AND rating IS NOT NULL
             GROUP BY rating_type",
            ['employee_id' => $employee_id]
        )->find() ?: [];

        $response['data'] = [
            'employee_id' => $employee_id,
            'ratings' => $ratings,
            'averages' => [
                'overall' => $averages['avg_rating'] ? round($averages['avg_rating'], 2) : null,
                'total_ratings' => (int) $averages['total_ratings'],
                'by_type' => $breakdown
            ]
        ];
    }
    // Case 2: Get most recent ratings
    elseif ($recent && $recent > 0) {
        $limit = min($recent, 100); // Max 100 records

        $query = "SELECT 
                    n.id,
                    n.employee_id,
                    n.note,
                    n.rating,
                    n.rating_type,
                    n.created_by,
                    n.created_at,
                    u.full_name as created_by_name
                  FROM staff_notes n
                  LEFT JOIN users u ON n.created_by = u.id
                  WHERE n.rating IS NOT NULL";

        $params = [];

        if ($rating_type) {
            $query .= " AND n.rating_type = :rating_type";
            $params['rating_type'] = $rating_type;
        }

        if ($start_date) {
            $query .= " AND DATE(n.created_at) >= :start_date";
            $params['start_date'] = $start_date;
        }
        if ($end_date) {
            $query .= " AND DATE(n.created_at) <= :end_date";
            $params['end_date'] = $end_date;
        }

        $query .= " ORDER BY n.created_at DESC LIMIT :limit";
        $params['limit'] = $limit;

        $ratings = $db->query($query, $params)->find() ?: [];

        $response['data'] = [
            'recent_ratings' => $ratings,
            'count' => count($ratings),
            'limit' => $limit
        ];
    }
    // Case 3: Get top rated staff
    elseif ($top && $top > 0) {
        $limit = min($top, 50); // Max 50 records

        $topStaff = $db->query(
            "SELECT 
                employee_id,
                AVG(rating) as avg_rating,
                COUNT(*) as total_ratings,
                MAX(rating) as highest_rating,
                MIN(rating) as lowest_rating,
                MAX(created_at) as last_rated
             FROM staff_notes 
             WHERE rating IS NOT NULL
             GROUP BY employee_id
             HAVING avg_rating >= 4.0
             ORDER BY avg_rating DESC, total_ratings DESC
             LIMIT :limit",
            ['limit' => $limit]
        )->find() ?: [];

        // Get additional details for each top staff member
        $enrichedStaff = [];
        foreach ($topStaff as $staff) {
            // Get recent notes for this staff
            $recentNotes = $db->query(
                "SELECT note, rating, rating_type, created_at 
                 FROM staff_notes 
                 WHERE employee_id = :emp_id AND rating IS NOT NULL
                 ORDER BY created_at DESC 
                 LIMIT 3",
                ['emp_id' => $staff['employee_id']]
            )->find() ?: [];

            // Get rating distribution
            $distribution = $db->query(
                "SELECT 
                    rating,
                    COUNT(*) as count
                 FROM staff_notes 
                 WHERE employee_id = :emp_id AND rating IS NOT NULL
                 GROUP BY rating
                 ORDER BY rating DESC",
                ['emp_id' => $staff['employee_id']]
            )->find() ?: [];

            $staff['recent_notes'] = $recentNotes;
            $staff['rating_distribution'] = $distribution;
            $enrichedStaff[] = $staff;
        }

        $response['data'] = [
            'top_rated_staff' => $enrichedStaff,
            'count' => count($enrichedStaff),
            'threshold' => '4.0 stars and above'
        ];
    }
    // Case 4: Get all ratings with comprehensive statistics
    else {
        // Get all ratings
        $query = "SELECT 
                    n.id,
                    n.employee_id,
                    n.note,
                    n.rating,
                    n.rating_type,
                    n.created_by,
                    n.created_at,
                    u.full_name as created_by_name
                  FROM staff_notes n
                  LEFT JOIN users u ON n.created_by = u.id
                  WHERE n.rating IS NOT NULL";

        $params = [];

        if ($rating_type) {
            $query .= " AND n.rating_type = :rating_type";
            $params['rating_type'] = $rating_type;
        }

        if ($start_date) {
            $query .= " AND DATE(n.created_at) >= :start_date";
            $params['start_date'] = $start_date;
        }
        if ($end_date) {
            $query .= " AND DATE(n.created_at) <= :end_date";
            $params['end_date'] = $end_date;
        }

        $query .= " ORDER BY n.created_at DESC";

        $allRatings = $db->query($query, $params)->find() ?: [];

        // Get overall statistics
        $statsQuery = "SELECT 
                        COUNT(*) as total_ratings,
                        AVG(rating) as overall_average,
                        MAX(rating) as highest_rating,
                        MIN(rating) as lowest_rating,
                        COUNT(DISTINCT employee_id) as unique_employees,
                        COUNT(DISTINCT rating_type) as rating_types_count
                      FROM staff_notes 
                      WHERE rating IS NOT NULL";

        $statsParams = [];

        if ($rating_type) {
            $statsQuery .= " AND rating_type = :rating_type";
            $statsParams['rating_type'] = $rating_type;
        }

        $stats = $db->query($statsQuery, $statsParams)->fetch_one();

        // Get rating distribution
        $distribution = $db->query(
            "SELECT 
                rating,
                COUNT(*) as count,
                ROUND((COUNT(*) * 100.0 / (SELECT COUNT(*) FROM staff_notes WHERE rating IS NOT NULL)), 2) as percentage
             FROM staff_notes 
             WHERE rating IS NOT NULL
             GROUP BY rating
             ORDER BY rating DESC",
            []
        )->find() ?: [];

        // Get ratings by type
        $byType = $db->query(
            "SELECT 
                rating_type,
                COUNT(*) as count,
                AVG(rating) as avg_rating
             FROM staff_notes 
             WHERE rating IS NOT NULL
             GROUP BY rating_type",
            []
        )->find() ?: [];

        // Get top performers by month
        $monthlyTop = $db->query(
            "SELECT 
                DATE_FORMAT(created_at, '%Y-%m') as month,
                employee_id,
                AVG(rating) as avg_rating,
                COUNT(*) as ratings_count
             FROM staff_notes 
             WHERE rating IS NOT NULL
             GROUP BY month, employee_id
             HAVING avg_rating >= 4.5
             ORDER BY month DESC, avg_rating DESC
             LIMIT 10",
            []
        )->find() ?: [];

        $response['data'] = [
            'all_ratings' => $allRatings,
            'statistics' => [
                'total_ratings' => (int) $stats['total_ratings'],
                'overall_average' => $stats['overall_average'] ? round($stats['overall_average'], 2) : null,
                'highest_rating' => (float) ($stats['highest_rating'] ?? 0),
                'lowest_rating' => (float) ($stats['lowest_rating'] ?? 0),
                'unique_employees' => (int) $stats['unique_employees'],
                'rating_types' => (int) $stats['rating_types_count']
            ],
            'distribution' => $distribution,
            'ratings_by_type' => $byType,
            'monthly_top_performers' => $monthlyTop,
            'total_records' => count($allRatings)
        ];
    }

    // Add response metadata
    $response['metadata']['query_time'] = date('Y-m-d H:i:s');
    $response['metadata']['parameters'] = [
        'employee_id' => $employee_id,
        'recent' => $recent,
        'top' => $top,
        'rating_type' => $rating_type,
        'start_date' => $start_date,
        'end_date' => $end_date
    ];

    echo json_encode($response, JSON_PRETTY_PRINT);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage(),
        'metadata' => [
            'timestamp' => date('Y-m-d H:i:s'),
            'error_type' => get_class($e)
        ]
    ]);
    exit();
}
?>