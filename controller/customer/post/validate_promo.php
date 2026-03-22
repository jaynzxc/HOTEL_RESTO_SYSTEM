<?php
/**
 * POST Controller - Validate Promo Code
 * Checks if a promo code is valid and calculates discount
 */

session_start();
require_once __DIR__ . '/../../../Class/Database.php';

header('Content-Type: application/json');

try {
    // Check if user is logged in
    if (!isset($_SESSION['user_id']) || !$_SESSION['logged_in']) {
        echo json_encode([
            'success' => false,
            'message' => 'Please login to continue'
        ]);
        exit();
    }

    $config = require __DIR__ . '/../../../config/config.php';
    $db = new Database($config['database']);

    $promoCode = strtoupper(trim($_POST['promo_code'] ?? ''));
    $subtotal = floatval($_POST['subtotal'] ?? 0);
    $user_id = $_SESSION['user_id'];

    if (empty($promoCode)) {
        echo json_encode([
            'success' => false,
            'message' => 'Please enter a promo code'
        ]);
        exit();
    }

    // Get promo code details
    $promo = $db->query(
        "SELECT pc.*, c.campaign_name, c.target_audience
         FROM promo_codes pc
         LEFT JOIN campaigns c ON pc.campaign_id = c.id
         WHERE pc.code = :code AND pc.is_active = 1",
        ['code' => $promoCode]
    )->fetch_one();

    if (!$promo) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid promo code'
        ]);
        exit();
    }

    // Check if promo is within valid date range
    $now = new DateTime();
    $validFrom = new DateTime($promo['valid_from']);
    $validTo = new DateTime($promo['valid_to']);

    if ($now < $validFrom) {
        echo json_encode([
            'success' => false,
            'message' => 'This promo code is not yet active. Valid from ' . $validFrom->format('M d, Y')
        ]);
        exit();
    }

    if ($now > $validTo) {
        echo json_encode([
            'success' => false,
            'message' => 'This promo code has expired'
        ]);
        exit();
    }

    // Check usage limit
    if ($promo['usage_limit'] !== null && $promo['used_count'] >= $promo['usage_limit']) {
        echo json_encode([
            'success' => false,
            'message' => 'This promo code has reached its usage limit'
        ]);
        exit();
    }

    // Check per user limit
    $userUsage = $db->query(
        "SELECT COUNT(*) as count FROM bookings 
         WHERE promo_code_id = :promo_id AND user_id = :user_id",
        ['promo_id' => $promo['id'], 'user_id' => $user_id]
    )->fetch_one();

    if ($userUsage && $userUsage['count'] >= $promo['per_user_limit']) {
        echo json_encode([
            'success' => false,
            'message' => 'You have already used this promo code the maximum number of times'
        ]);
        exit();
    }

    // Check target audience
    if ($promo['target_audience'] && $promo['target_audience'] !== 'all') {
        $user = $db->query(
            "SELECT member_tier, loyalty_points FROM users WHERE id = :id",
            ['id' => $user_id]
        )->fetch_one();

        if ($promo['target_audience'] === 'vip' && !in_array($user['member_tier'], ['gold', 'platinum'])) {
            echo json_encode([
                'success' => false,
                'message' => 'This promo code is for VIP members only'
            ]);
            exit();
        }

        if ($promo['target_audience'] === 'members' && $user['member_tier'] === 'bronze' && $user['loyalty_points'] < 100) {
            echo json_encode([
                'success' => false,
                'message' => 'This promo code is for members only'
            ]);
            exit();
        }

        if ($promo['target_audience'] === 'loyalty' && $user['loyalty_points'] < 500) {
            echo json_encode([
                'success' => false,
                'message' => 'This promo code requires loyalty points'
            ]);
            exit();
        }
    }

    // Check minimum purchase
    if ($promo['min_purchase'] > 0 && $subtotal < $promo['min_purchase']) {
        echo json_encode([
            'success' => false,
            'message' => 'Minimum purchase of ₱' . number_format($promo['min_purchase'], 2) . ' required for this promo code'
        ]);
        exit();
    }

    // Calculate discount
    $discount = 0;
    $discountType = $promo['discount_type'];
    $discountValue = floatval($promo['discount_value']);

    if ($discountType === 'percentage') {
        $discount = $subtotal * ($discountValue / 100);
        // Apply max discount if set
        if ($promo['max_discount'] && $discount > $promo['max_discount']) {
            $discount = $promo['max_discount'];
        }
    } else {
        $discount = $discountValue;
        // Don't discount more than subtotal
        if ($discount > $subtotal) {
            $discount = $subtotal;
        }
    }

    $discount = round($discount, 2);
    $newSubtotal = $subtotal - $discount;
    $newTax = $newSubtotal * 0.12;
    $newTotal = $newSubtotal + $newTax;

    echo json_encode([
        'success' => true,
        'message' => 'Promo code applied!',
        'discount' => $discount,
        'discount_type' => $discountType,
        'discount_value' => $discountValue,
        'new_subtotal' => $newSubtotal,
        'new_tax' => $newTax,
        'new_total' => $newTotal,
        'promo_code_id' => $promo['id'],
        'promo_code' => $promo['code'],
        'campaign_name' => $promo['campaign_name']
    ]);

} catch (Exception $e) {
    error_log('Promo validation error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred. Please try again.'
    ]);
}
?>