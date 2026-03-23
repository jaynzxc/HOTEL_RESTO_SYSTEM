<?php
/**
 * GET Controller - Landing Page Data
 * Fetches dynamic content for the homepage
 */

session_start();

require_once __DIR__ . '/../Class/Database.php';

// Load configuration
$config = require __DIR__ . '/../config/config.php';

// Get database configuration from config file
$dbConfig = $config['database'];

// Create database connection with error handling
try {
    $db = new Database(
        [
            'host' => $dbConfig['host'],
            'port' => $dbConfig['port'],
            'dbname' => $dbConfig['dbname']
        ],
        $dbConfig['username'],
        $dbConfig['password']
    );
} catch (PDOException $e) {
    // Log error and use fallback data
    error_log("Database connection failed: " . $e->getMessage());
    $db = null;
}

// Get featured rooms (limit to 3)
$featuredRooms = [];
if ($db) {
    try {
        $featuredRooms = $db->query(
            "SELECT id, name, description, price, beds, view, amenities, image_url, max_occupancy 
             FROM rooms 
             WHERE is_available = 1 
             ORDER BY price DESC 
             LIMIT 3",
            []
        )->find() ?: [];
    } catch (PDOException $e) {
        error_log("Featured rooms query failed: " . $e->getMessage());
    }
}

// If no rooms in DB or DB connection failed, use default featured rooms
if (empty($featuredRooms)) {
    $featuredRooms = [
        [
            'id' => 1,
            'name' => 'Deluxe Suite',
            'description' => 'King bed · Ocean view',
            'price' => 5999,
            'beds' => '1 king bed',
            'view' => 'ocean view',
            'amenities' => 'Free WiFi, Breakfast, Smart TV',
            'image_url' => 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
            'max_occupancy' => 2
        ],
        [
            'id' => 2,
            'name' => 'Executive Room',
            'description' => 'Queen bed · City view',
            'price' => 4499,
            'beds' => '1 queen bed',
            'view' => 'city view',
            'amenities' => 'Free WiFi, Workspace',
            'image_url' => 'https://images.unsplash.com/photo-1578683010236-d716f9a3f461?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
            'max_occupancy' => 2
        ],
        [
            'id' => 3,
            'name' => 'Family Suite',
            'description' => '2 bedrooms · Pool view',
            'price' => 6999,
            'beds' => '2 queen beds',
            'view' => 'pool view',
            'amenities' => 'Kids Friendly, Pool Access, Game Room',
            'image_url' => 'https://images.unsplash.com/photo-1566665797739-1674de7a421a?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
            'max_occupancy' => 4
        ]
    ];
}

// Get menu items for dining section (limit to 4)
$menuItems = [];
if ($db) {
    try {
        $menuItems = $db->query(
            "SELECT id, name, description, price, category, image_url 
             FROM menu_items 
             WHERE is_available = 1 
             ORDER BY category 
             LIMIT 4",
            []
        )->find() ?: [];
    } catch (PDOException $e) {
        error_log("Menu items query failed: " . $e->getMessage());
    }
}

// If no menu items, use defaults
if (empty($menuItems)) {
    $menuItems = [
        ['id' => 1, 'name' => 'Breakfast Buffet', 'description' => 'Start your day right', 'price' => 450, 'category' => 'breakfast', 'image_url' => null],
        ['id' => 2, 'name' => 'Lunch Specials', 'description' => 'Daily lunch specials', 'price' => 350, 'category' => 'lunch', 'image_url' => null],
        ['id' => 3, 'name' => 'Fine Dining Dinner', 'description' => 'Exquisite dinner experience', 'price' => 850, 'category' => 'dinner', 'image_url' => null],
        ['id' => 4, 'name' => '24/7 Room Service', 'description' => 'Anytime dining', 'price' => 0, 'category' => 'service', 'image_url' => null]
    ];
}

// Get active promos/offers
$activePromos = [];
if ($db) {
    try {
        $activePromos = $db->query(
            "SELECT campaign_name, description, discount_percent, discount_amount, start_date, end_date 
             FROM campaigns 
             WHERE status = 'active' 
             AND start_date <= NOW() 
             AND end_date >= NOW()
             LIMIT 2",
            []
        )->find() ?: [];
    } catch (PDOException $e) {
        error_log("Promos query failed: " . $e->getMessage());
    }
}

// If no promos, use defaults
if (empty($activePromos)) {
    $activePromos = [
        [
            'campaign_name' => 'Summer Sale',
            'description' => '20% off on all room bookings',
            'discount_percent' => 20,
            'discount_amount' => null,
            'start_date' => date('Y-m-d'),
            'end_date' => date('Y-m-d', strtotime('+30 days'))
        ],
        [
            'campaign_name' => 'Free Breakfast',
            'description' => 'Book 2 nights, get free breakfast',
            'discount_percent' => null,
            'discount_amount' => null,
            'start_date' => date('Y-m-d'),
            'end_date' => date('Y-m-d', strtotime('+60 days'))
        ]
    ];
}

// Get testimonials/reviews
$testimonials = [];
if ($db) {
    try {
        $testimonials = $db->query(
            "SELECT u.full_name, r.rating, r.review_text, r.created_at 
             FROM reviews r
             LEFT JOIN users u ON r.user_id = u.id
             WHERE r.rating >= 4
             ORDER BY r.created_at DESC
             LIMIT 3",
            []
        )->find() ?: [];
    } catch (PDOException $e) {
        error_log("Testimonials query failed: " . $e->getMessage());
    }
}

// If no testimonials, use defaults
if (empty($testimonials)) {
    $testimonials = [
        [
            'full_name' => 'Maria Santos',
            'rating' => 5,
            'review_text' => 'Amazing experience! The room was spotless and the staff were very accommodating.',
            'created_at' => date('Y-m-d H:i:s')
        ],
        [
            'full_name' => 'John Reyes',
            'rating' => 5,
            'review_text' => 'The restaurant food is exceptional! Loved the breakfast buffet.',
            'created_at' => date('Y-m-d H:i:s')
        ],
        [
            'full_name' => 'Anna Cruz',
            'rating' => 5,
            'review_text' => 'Perfect venue for our anniversary. The staff went above and beyond.',
            'created_at' => date('Y-m-d H:i:s')
        ]
    ];
}

// Get system settings for stats
$settings = [];
if ($db) {
    try {
        $settings = $db->query(
            "SELECT setting_key, setting_value FROM system_settings WHERE setting_key IN ('hotel_name', 'hotel_address', 'hotel_contact', 'hotel_email')",
            []
        )->find() ?: [];
    } catch (PDOException $e) {
        error_log("Settings query failed: " . $e->getMessage());
    }
}

$hotelSettings = [];
foreach ($settings as $setting) {
    $hotelSettings[$setting['setting_key']] = $setting['setting_value'];
}

// Set default hotel settings if not found
$hotelSettings = array_merge([
    'hotel_name' => 'Luxury Hotel',
    'hotel_address' => '123 Beach Avenue, City',
    'hotel_contact' => '+1234567890',
    'hotel_email' => 'info@luxuryhotel.com'
], $hotelSettings);

// Get real stats from database
$stats = [
    'total_guests' => 850,
    'total_rooms' => 65,
    'total_menu_items' => 120
];

if ($db) {
    try {
        $guestCount = $db->query("SELECT COUNT(*) as count FROM users WHERE role = 'customer'")->fetch_one();
        $stats['total_guests'] = $guestCount['count'] ?? 850;
    } catch (PDOException $e) {
        error_log("Guest count query failed: " . $e->getMessage());
    }
    
    try {
        $roomCount = $db->query("SELECT COUNT(*) as count FROM rooms")->fetch_one();
        $stats['total_rooms'] = $roomCount['count'] ?? 65;
    } catch (PDOException $e) {
        error_log("Room count query failed: " . $e->getMessage());
    }
    
    try {
        $menuCount = $db->query("SELECT COUNT(*) as count FROM menu_items WHERE is_available = 1")->fetch_one();
        $stats['total_menu_items'] = $menuCount['count'] ?? 120;
    } catch (PDOException $e) {
        error_log("Menu count query failed: " . $e->getMessage());
    }
}

// Get user session data
$isLoggedIn = isset($_SESSION['user_id']) && $_SESSION['logged_in'] === true;
$userName = $_SESSION['user_name'] ?? '';
$userRole = $_SESSION['role'] ?? '';

// Store data for view
$viewData = [
    'featuredRooms' => $featuredRooms,
    'menuItems' => $menuItems,
    'activePromos' => $activePromos,
    'testimonials' => $testimonials,
    'hotelSettings' => $hotelSettings,
    'stats' => $stats,
    'isLoggedIn' => $isLoggedIn,
    'userName' => $userName,
    'userRole' => $userRole
];

extract($viewData);
?>