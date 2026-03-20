<?php
/**
 * API - Staff Ratings (Minimal Version)
 */

// Completely reset everything
if (session_status() !== PHP_SESSION_NONE) {
    session_destroy();
}

// Clear any authentication headers that might be set
header_remove('Authorization');
header_remove('WWW-Authenticate');

// Set CORS and content headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: *');

// Handle OPTIONS preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Check if request is GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Only GET requests are allowed'
    ]);
    exit();
}

// Simple database connection
try {
    $host = 'localhost';
    $port = '3307';
    $dbname = 'hotelrestaurant';
    $username = 'root';
    $password = '';

    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get parameters
    $employee_id = $_GET['employee_id'] ?? null;
    $recent = isset($_GET['recent']) ? (int) $_GET['recent'] : null;
    $top = isset($_GET['top']) ? (int) $_GET['top'] : null;

    $response = ['success' => true, 'data' => []];

    // Simple queries
    if ($employee_id) {
        $stmt = $pdo->prepare("SELECT * FROM staff_notes WHERE employee_id = ? ORDER BY created_at DESC");
        $stmt->execute([$employee_id]);
        $ratings = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $pdo->prepare("SELECT AVG(rating) as avg_rating, COUNT(*) as total FROM staff_notes WHERE employee_id = ? AND rating IS NOT NULL");
        $stmt->execute([$employee_id]);
        $avg = $stmt->fetch(PDO::FETCH_ASSOC);

        $response['data'] = [
            'employee_id' => $employee_id,
            'ratings' => $ratings,
            'averages' => [
                'overall' => $avg['avg_rating'] ? round($avg['avg_rating'], 2) : null,
                'total_ratings' => (int) $avg['total']
            ]
        ];
    } elseif ($recent && $recent > 0) {
        $stmt = $pdo->prepare("SELECT * FROM staff_notes WHERE rating IS NOT NULL ORDER BY created_at DESC LIMIT ?");
        $stmt->execute([$recent]);
        $ratings = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $response['data'] = [
            'recent_ratings' => $ratings,
            'count' => count($ratings)
        ];
    } elseif ($top && $top > 0) {
        $stmt = $pdo->prepare("
            SELECT employee_id, AVG(rating) as avg_rating, COUNT(*) as total_ratings 
            FROM staff_notes 
            WHERE rating IS NOT NULL 
            GROUP BY employee_id 
            HAVING avg_rating >= 4.0 
            ORDER BY avg_rating DESC 
            LIMIT ?
        ");
        $stmt->execute([$top]);
        $topStaff = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $response['data'] = [
            'top_rated_staff' => $topStaff,
            'count' => count($topStaff)
        ];
    } else {
        // Get all ratings with statistics
        $stmt = $pdo->query("SELECT COUNT(*) as total, AVG(rating) as avg FROM staff_notes WHERE rating IS NOT NULL");
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);

        $stmt = $pdo->query("SELECT * FROM staff_notes WHERE rating IS NOT NULL ORDER BY created_at DESC LIMIT 100");
        $ratings = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $response['data'] = [
            'all_ratings' => $ratings,
            'statistics' => [
                'total_ratings' => (int) $stats['total'],
                'overall_average' => $stats['avg'] ? round($stats['avg'], 2) : null
            ],
            'total_records' => count($ratings)
        ];
    }

    echo json_encode($response, JSON_PRETTY_PRINT);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>