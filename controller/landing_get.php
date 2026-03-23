<?php
/**
 * GET Controller - Landing Page Data
 * Fetches dynamic content for the homepage
 */

session_start();

require_once __DIR__ . '../../Class/Database.php';

$config = require __DIR__ . '../../config/config.php';

// Pass the full config array (including username/password)
$db = new Database($config);

// Rest of your code remains exactly the same...
// Get featured rooms (limit to 3)
$featuredRooms = $db->query(
    "SELECT id, name, description, price, beds, view, amenities, image_url, max_occupancy 
     FROM rooms 
     WHERE is_available = 1 
     ORDER BY price DESC 
     LIMIT 3",
    []
)->find() ?: [];

// ... (keep all the rest of your code unchanged)

// If no rooms in DB, use default featured rooms
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
            'image_url' => 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80'
        ],
        [
            'id' => 2,
            'name' => 'Executive Room',
            'description' => 'Queen bed · City view',
            'price' => 4499,
            'beds' => '1 queen bed',
            'view' => 'city view',
            'amenities' => 'Free WiFi, Workspace',
            'image_url' => 'https://images.unsplash.com/photo-1578683010236-d716f9a3f461?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80'
        ],
        [
            'id' => 3,
            'name' => 'Family Suite',
            'description' => '2 bedrooms · Pool view',
            'price' => 6999,
            'beds' => '2 queen beds',
            'view' => 'pool view',
            'amenities' => 'Kids Friendly, Pool Access, Game Room',
            'image_url' => 'https://images.unsplash.com/photo-1566665797739-1674de7a421a?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80'
        ]
    ];
}

// Get menu items for dining section (limit to 4)
$menuItems = $db->query(
    "SELECT id, name, description, price, category, image_url 
     FROM menu_items 
     WHERE is_available = 1 
     ORDER BY category 
     LIMIT 4",
    []
)->find() ?: [];

// If no menu items, use defaults
if (empty($menuItems)) {
    $menuItems = [
        ['name' => 'Breakfast Buffet', 'description' => 'Start your day right', 'price' => 450, 'category' => 'breakfast', 'image_url' => null],
        ['name' => 'Lunch Specials', 'description' => 'Daily lunch specials', 'price' => 350, 'category' => 'lunch', 'image_url' => null],
        ['name' => 'Fine Dining Dinner', 'description' => 'Exquisite dinner experience', 'price' => 850, 'category' => 'dinner', 'image_url' => null],
        ['name' => '24/7 Room Service', 'description' => 'Anytime dining', 'price' => 0, 'category' => 'service', 'image_url' => null]
    ];
}

// Get active promos/offers
$activePromos = $db->query(
    "SELECT campaign_name, description, discount_percent, discount_amount, start_date, end_date 
     FROM campaigns 
     WHERE status = 'active' 
     AND start_date <= NOW() 
     AND end_date >= NOW()
     LIMIT 2",
    []
)->find() ?: [];

// If no promos, use defaults
if (empty($activePromos)) {
    $activePromos = [
        [
            'campaign_name' => 'Summer Sale',
            'description' => '20% off on all room bookings',
            'discount_percent' => 20,
            'discount_amount' => null
        ],
        [
            'campaign_name' => 'Free Breakfast',
            'description' => 'Book 2 nights, get free breakfast',
            'discount_percent' => null,
            'discount_amount' => null
        ]
    ];
}

// Get testimonials/reviews
$testimonials = $db->query(
    "SELECT u.full_name, r.rating, r.review_text, r.created_at 
     FROM reviews r
     LEFT JOIN users u ON r.user_id = u.id
     WHERE r.rating >= 4
     ORDER BY r.created_at DESC
     LIMIT 3",
    []
)->find() ?: [];

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
$settings = $db->query(
    "SELECT setting_key, setting_value FROM system_settings WHERE setting_key IN ('hotel_name', 'hotel_address', 'hotel_contact', 'hotel_email')",
    []
)->find() ?: [];

$hotelSettings = [];
foreach ($settings as $setting) {
    $hotelSettings[$setting['setting_key']] = $setting['setting_value'];
}

// Get real stats from database
$stats = [
    'total_guests' => $db->query("SELECT COUNT(*) as count FROM users WHERE role = 'customer'")->fetch_one()['count'] ?? 850,
    'total_rooms' => $db->query("SELECT COUNT(*) as count FROM rooms")->fetch_one()['count'] ?? 65,
    'total_menu_items' => $db->query("SELECT COUNT(*) as count FROM menu_items WHERE is_available = 1")->fetch_one()['count'] ?? 120
];

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