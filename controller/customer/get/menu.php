<?php
/**
 * GET Controller - Customer Menu/Order Food
 * Handles fetching menu items, user cart, and order history
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

// Get current user data
$user = $db->query(
    "SELECT id, full_name, first_name, last_name, email, phone, 
            loyalty_points, member_tier, avatar
     FROM users WHERE id = :id",
    ['id' => $_SESSION['user_id']]
)->fetch_one();

// Get menu items from database
$menuItems = $db->query(
    "SELECT id, name, description, price, category, 
            is_available, image_url, preparation_time
     FROM menu_items 
     WHERE is_available = 1
     ORDER BY 
        CASE category
            WHEN 'mains' THEN 1
            WHEN 'appetizers' THEN 2
            WHEN 'desserts' THEN 3
            WHEN 'beverages' THEN 4
            ELSE 5
        END,
        name ASC"
)->find() ?: [];

// If no menu items in DB, use default items
if (empty($menuItems)) {
    $menuItems = [
        [
            'id' => 1,
            'name' => 'Sinigang na Baboy',
            'description' => 'tamarind soup, pork, veggies',
            'price' => 320,
            'category' => 'mains',
            'is_available' => 1,
            'image_url' => null
        ],
        [
            'id' => 2,
            'name' => 'Sizzling Sisig',
            'description' => 'chopped pork, onion, egg',
            'price' => 290,
            'category' => 'mains',
            'is_available' => 1,
            'image_url' => null
        ],
        [
            'id' => 3,
            'name' => 'Crispy Pata',
            'description' => 'deep-fried pork knuckle',
            'price' => 550,
            'category' => 'mains',
            'is_available' => 1,
            'image_url' => null
        ],
        [
            'id' => 4,
            'name' => 'Halo-Halo',
            'description' => 'shaved ice, fruits, leche flan',
            'price' => 150,
            'category' => 'desserts',
            'is_available' => 1,
            'image_url' => null
        ],
        [
            'id' => 5,
            'name' => 'Fresh Buko Juice',
            'description' => 'with coconut pulp',
            'price' => 90,
            'category' => 'beverages',
            'is_available' => 1,
            'image_url' => null
        ],
        [
            'id' => 6,
            'name' => 'Garlic Rice',
            'description' => 'sinangag, plain',
            'price' => 50,
            'category' => 'mains',
            'is_available' => 1,
            'image_url' => null
        ]
    ];
}

// Get user's current active order (if any)
$activeOrder = $db->query(
    "SELECT id, order_reference, total_amount, status, created_at, items, order_type
     FROM food_orders 
     WHERE user_id = :user_id AND status IN ('pending', 'preparing')
     ORDER BY created_at DESC
     LIMIT 1",
    ['user_id' => $_SESSION['user_id']]
)->fetch_one();

// Get user's order history - ALL recent orders including pending
$orderHistory = $db->query(
    "SELECT id, order_reference, total_amount, status, created_at, 
            items, order_type, points_used
     FROM food_orders 
     WHERE user_id = :user_id 
     ORDER BY created_at DESC
     LIMIT 10",
    ['user_id' => $_SESSION['user_id']]
)->find() ?: [];

// Format order history for display
foreach ($orderHistory as &$order) {
    // Decode items JSON
    $order['items_array'] = json_decode($order['items'], true) ?: [];

    // Format date
    $created = new DateTime($order['created_at']);
    $now = new DateTime();
    $diff = $now->diff($created);

    if ($diff->days > 0) {
        if ($diff->days == 1) {
            $order['time_ago'] = 'yesterday';
        } else {
            $order['time_ago'] = $diff->days . ' days ago';
        }
    } elseif ($diff->h > 0) {
        if ($diff->h == 1) {
            $order['time_ago'] = '1 hour ago';
        } else {
            $order['time_ago'] = $diff->h . ' hours ago';
        }
    } elseif ($diff->i > 0) {
        if ($diff->i == 1) {
            $order['time_ago'] = '1 minute ago';
        } else {
            $order['time_ago'] = $diff->i . ' minutes ago';
        }
    } else {
        $order['time_ago'] = 'just now';
    }

    // Count items
    $order['item_count'] = array_sum(array_column($order['items_array'], 'quantity'));

    // Calculate points earned (for display only)
    $order['points_earned'] = floor($order['total_amount'] / 100) * 5;
}

// Get user's current balance from current_balance table
$balance = $db->query(
    "SELECT total_balance, pending_balance, available_balance 
     FROM current_balance 
     WHERE user_id = :user_id",
    ['user_id' => $_SESSION['user_id']]
)->fetch_one();

if (!$balance) {
    $balance = ['total_balance' => 0, 'pending_balance' => 0, 'available_balance' => 0];
}

// Check for outstanding balance (unpaid bookings and reservations)
$outstandingBookings = $db->query(
    "SELECT COALESCE(SUM(total_amount), 0) as total 
     FROM bookings 
     WHERE user_id = :user_id AND payment_status = 'unpaid' AND status != 'cancelled'",
    ['user_id' => $_SESSION['user_id']]
)->fetch_one();

$outstandingReservations = $db->query(
    "SELECT COALESCE(SUM(down_payment), 0) as total 
     FROM restaurant_reservations 
     WHERE user_id = :user_id AND payment_status = 'unpaid' AND status != 'cancelled'",
    ['user_id' => $_SESSION['user_id']]
)->fetch_one();

$totalOutstanding = ($outstandingBookings['total'] ?? 0) + ($outstandingReservations['total'] ?? 0);
$hasOutstandingBalance = $totalOutstanding > 0;

// Get unread notifications count
$unread_count = $db->query(
    "SELECT COUNT(*) as count FROM notifications 
     WHERE user_id = :user_id AND is_read = 0",
    ['user_id' => $_SESSION['user_id']]
)->fetch_one()['count'] ?? 0;

// Get user initials
$initials = 'G';
if ($user) {
    $first_name = $user['first_name'] ?? '';
    $last_name = $user['last_name'] ?? '';
    $full_name = $user['full_name'] ?? '';

    if (empty($first_name) && empty($last_name) && !empty($full_name)) {
        $name_parts = explode(' ', $full_name, 2);
        $first_name = $name_parts[0];
        $last_name = $name_parts[1] ?? '';
    }

    $initials = strtoupper(
        substr($first_name, 0, 1) .
        (isset($last_name) ? substr($last_name, 0, 1) : '')
    );
}

// Get user's default table or room for dine-in/delivery
$defaultTable = $db->query(
    "SELECT table_number FROM restaurant_reservations 
     WHERE user_id = :user_id AND reservation_date = CURDATE()
     AND status = 'confirmed'
     LIMIT 1",
    ['user_id' => $_SESSION['user_id']]
)->fetch_one();

$defaultRoom = $db->query(
    "SELECT room_name, room_id FROM bookings 
     WHERE user_id = :user_id AND status = 'confirmed'
     AND check_in <= CURDATE() AND check_out >= CURDATE()
     LIMIT 1",
    ['user_id' => $_SESSION['user_id']]
)->fetch_one();

// Set points variable for easy access
$points = $user['loyalty_points'] ?? 0;
$member_tier = $user['member_tier'] ?? 'bronze';

// Store data for view
$viewData = [
    'user' => $user,
    'menuItems' => $menuItems,
    'activeOrder' => $activeOrder,
    'orderHistory' => $orderHistory,
    'balance' => $balance,
    
    'initials' => $initials,
    'defaultTable' => $defaultTable,
    'defaultRoom' => $defaultRoom,
    'totalOutstanding' => $totalOutstanding,
    'hasOutstandingBalance' => $hasOutstandingBalance,
    'points' => $points,
    'member_tier' => $member_tier
];

// Extract variables for view
extract($viewData);
?>