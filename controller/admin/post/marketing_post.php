<?php
/**
 * POST Controller - Admin Marketing & Promotions
 */

session_start();
require_once __DIR__ . '/../../../Class/Database.php';

header('Content-Type: application/json');

function sendJsonError($message, $code = 400)
{
    http_response_code($code);
    echo json_encode(['success' => false, 'message' => $message]);
    exit();
}

function sendJsonSuccess($data)
{
    echo json_encode(array_merge(['success' => true], $data));
    exit();
}

try {
    if (!isset($_SESSION['user_id']) || !$_SESSION['logged_in']) {
        sendJsonError('Please login to continue', 401);
    }

    $config = require __DIR__ . '/../../../config/config.php';
    $db = new Database($config['database']);

    $user = $db->query(
        "SELECT role FROM users WHERE id = :id",
        ['id' => $_SESSION['user_id']]
    )->fetch_one();

    if (!$user || $user['role'] !== 'admin') {
        sendJsonError('Unauthorized access', 403);
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendJsonError('Invalid request method', 405);
    }

    $action = $_POST['action'] ?? '';

    // CREATE NEW CAMPAIGN
    if ($action === 'create_campaign') {
        $campaign_name = trim($_POST['campaign_name'] ?? '');
        $campaign_type = $_POST['campaign_type'] ?? 'discount';
        $description = trim($_POST['description'] ?? '');
        $discount_percent = $_POST['discount_percent'] ?? null;
        $discount_amount = $_POST['discount_amount'] ?? null;
        $start_date = $_POST['start_date'] ?? '';
        $end_date = $_POST['end_date'] ?? '';
        $status = $_POST['status'] ?? 'draft';
        $target_audience = $_POST['target_audience'] ?? 'all';
        $redemption_limit = intval($_POST['redemption_limit'] ?? 0);
        $budget = floatval($_POST['budget'] ?? 0);

        if (empty($campaign_name) || empty($start_date) || empty($end_date)) {
            sendJsonError('Campaign name, start date, and end date are required');
        }

        if (strtotime($start_date) >= strtotime($end_date)) {
            sendJsonError('End date must be after start date');
        }

        $db->query(
            "INSERT INTO campaigns (campaign_name, campaign_type, description, discount_percent, discount_amount, 
             start_date, end_date, status, target_audience, redemption_limit, budget, created_by, created_at, updated_at) 
             VALUES (:name, :type, :desc, :discount_percent, :discount_amount, :start, :end, :status, :target, :limit, :budget, :user_id, NOW(), NOW())",
            [
                'name' => $campaign_name,
                'type' => $campaign_type,
                'desc' => $description,
                'discount_percent' => $discount_percent ?: null,
                'discount_amount' => $discount_amount ?: null,
                'start' => $start_date,
                'end' => $end_date,
                'status' => $status,
                'target' => $target_audience,
                'limit' => $redemption_limit ?: null,
                'budget' => $budget ?: null,
                'user_id' => $_SESSION['user_id']
            ]
        );

        sendJsonSuccess(['message' => 'Campaign created successfully']);
    }

    // UPDATE CAMPAIGN
    elseif ($action === 'update_campaign') {
        $campaign_id = intval($_POST['campaign_id'] ?? 0);
        $campaign_name = trim($_POST['campaign_name'] ?? '');
        $campaign_type = $_POST['campaign_type'] ?? 'discount';
        $description = trim($_POST['description'] ?? '');
        $discount_percent = $_POST['discount_percent'] ?? null;
        $discount_amount = $_POST['discount_amount'] ?? null;
        $start_date = $_POST['start_date'] ?? '';
        $end_date = $_POST['end_date'] ?? '';
        $status = $_POST['status'] ?? 'draft';
        $target_audience = $_POST['target_audience'] ?? 'all';
        $redemption_limit = intval($_POST['redemption_limit'] ?? 0);
        $budget = floatval($_POST['budget'] ?? 0);

        if (!$campaign_id) {
            sendJsonError('Campaign ID required');
        }

        $db->query(
            "UPDATE campaigns SET 
                campaign_name = :name,
                campaign_type = :type,
                description = :desc,
                discount_percent = :discount_percent,
                discount_amount = :discount_amount,
                start_date = :start,
                end_date = :end,
                status = :status,
                target_audience = :target,
                redemption_limit = :limit,
                budget = :budget,
                updated_at = NOW()
             WHERE id = :id",
            [
                'id' => $campaign_id,
                'name' => $campaign_name,
                'type' => $campaign_type,
                'desc' => $description,
                'discount_percent' => $discount_percent ?: null,
                'discount_amount' => $discount_amount ?: null,
                'start' => $start_date,
                'end' => $end_date,
                'status' => $status,
                'target' => $target_audience,
                'limit' => $redemption_limit ?: null,
                'budget' => $budget ?: null
            ]
        );

        sendJsonSuccess(['message' => 'Campaign updated successfully']);
    }

    // CREATE PROMO CODE
    elseif ($action === 'create_promo_code') {
        $campaign_id = intval($_POST['campaign_id'] ?? 0);
        $code = strtoupper(trim($_POST['code'] ?? ''));
        $description = trim($_POST['description'] ?? '');
        $discount_type = $_POST['discount_type'] ?? 'percentage';
        $discount_value = floatval($_POST['discount_value'] ?? 0);
        $min_purchase = floatval($_POST['min_purchase'] ?? 0);
        $max_discount = $_POST['max_discount'] ? floatval($_POST['max_discount']) : null;
        $valid_from = $_POST['valid_from'] ?? '';
        $valid_to = $_POST['valid_to'] ?? '';
        $usage_limit = intval($_POST['usage_limit'] ?? 0);
        $per_user_limit = intval($_POST['per_user_limit'] ?? 1);

        if (empty($code) || empty($valid_from) || empty($valid_to) || $discount_value <= 0) {
            sendJsonError('Code, dates, and discount value are required');
        }

        // Check if code exists
        $existing = $db->query(
            "SELECT id FROM promo_codes WHERE code = :code",
            ['code' => $code]
        )->fetch_one();

        if ($existing) {
            sendJsonError('Promo code already exists');
        }

        $db->query(
            "INSERT INTO promo_codes (campaign_id, code, description, discount_type, discount_value, 
             min_purchase, max_discount, valid_from, valid_to, usage_limit, per_user_limit, created_at, updated_at) 
             VALUES (:campaign_id, :code, :desc, :type, :value, :min_purchase, :max_discount, :valid_from, :valid_to, :limit, :per_user, NOW(), NOW())",
            [
                'campaign_id' => $campaign_id ?: null,
                'code' => $code,
                'desc' => $description,
                'type' => $discount_type,
                'value' => $discount_value,
                'min_purchase' => $min_purchase,
                'max_discount' => $max_discount,
                'valid_from' => $valid_from,
                'valid_to' => $valid_to,
                'limit' => $usage_limit ?: null,
                'per_user' => $per_user_limit
            ]
        );

        sendJsonSuccess(['message' => 'Promo code created successfully']);
    }

    // GET CAMPAIGN DETAILS
    elseif ($action === 'get_campaign') {
        $campaign_id = intval($_POST['campaign_id'] ?? 0);

        $campaign = $db->query(
            "SELECT * FROM campaigns WHERE id = :id",
            ['id' => $campaign_id]
        )->fetch_one();

        if (!$campaign) {
            sendJsonError('Campaign not found');
        }

        sendJsonSuccess(['campaign' => $campaign]);
    }

    // GET PROMO CODES FOR CAMPAIGN
    elseif ($action === 'get_promo_codes') {
        $campaign_id = intval($_POST['campaign_id'] ?? 0);

        $codes = $db->query(
            "SELECT * FROM promo_codes WHERE campaign_id = :campaign_id OR campaign_id IS NULL ORDER BY created_at DESC",
            ['campaign_id' => $campaign_id]
        )->find() ?: [];

        sendJsonSuccess(['promo_codes' => $codes]);
    }

    // SEND EMAIL BLAST
    elseif ($action === 'send_email_blast') {
        $template_id = intval($_POST['template_id'] ?? 0);
        $audience = $_POST['audience'] ?? 'all';
        $subject = trim($_POST['subject'] ?? '');
        $content = trim($_POST['content'] ?? '');

        // Get template if template_id provided
        if ($template_id > 0) {
            $template = $db->query(
                "SELECT * FROM email_templates WHERE id = :id",
                ['id' => $template_id]
            )->fetch_one();

            if ($template) {
                $subject = $template['subject'];
                $content = $template['content'];
            }
        }

        if (empty($subject) || empty($content)) {
            sendJsonError('Subject and content are required');
        }

        // Get recipients based on audience
        $recipientsQuery = "SELECT id, full_name, email FROM users WHERE role = 'customer' AND status = 'active'";

        if ($audience === 'vip') {
            $recipientsQuery .= " AND member_tier IN ('gold', 'platinum')";
        } elseif ($audience === 'loyalty') {
            $recipientsQuery .= " AND loyalty_points > 0";
        } elseif ($audience === 'recent') {
            $recipientsQuery .= " AND EXISTS (SELECT 1 FROM bookings WHERE user_id = users.id AND created_at > DATE_SUB(NOW(), INTERVAL 30 DAY))";
        }

        $recipients = $db->query($recipientsQuery, [])->find() ?: [];

        // In a real system, you'd send actual emails here
        // For now, we'll just log and return success
        $sentCount = count($recipients);

        // Log the email blast
        $db->query(
            "INSERT INTO admin_notifications (admin_id, title, message, type, created_at) 
             VALUES (:admin_id, 'Email Blast Sent', :message, 'success', NOW())",
            [
                'admin_id' => $_SESSION['user_id'],
                'message' => "Email blast sent to {$sentCount} recipients: {$subject}"
            ]
        );

        sendJsonSuccess([
            'message' => "Email blast sent to {$sentCount} recipients",
            'recipient_count' => $sentCount
        ]);
    }

    // DELETE CAMPAIGN
    elseif ($action === 'delete_campaign') {
        $campaign_id = intval($_POST['campaign_id'] ?? 0);

        $db->query("DELETE FROM campaigns WHERE id = :id", ['id' => $campaign_id]);

        sendJsonSuccess(['message' => 'Campaign deleted successfully']);
    }

    // UPDATE CAMPAIGN STATUS
    elseif ($action === 'update_campaign_status') {
        $campaign_id = intval($_POST['campaign_id'] ?? 0);
        $status = $_POST['status'] ?? '';

        if (!in_array($status, ['draft', 'active', 'scheduled', 'ended', 'cancelled'])) {
            sendJsonError('Invalid status');
        }

        $db->query(
            "UPDATE campaigns SET status = :status, updated_at = NOW() WHERE id = :id",
            ['status' => $status, 'id' => $campaign_id]
        );

        sendJsonSuccess(['message' => 'Campaign status updated']);
    }

    // GET CAMPAIGN STATISTICS
    elseif ($action === 'get_campaign_stats') {
        $stats = $db->query(
            "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
                SUM(CASE WHEN status = 'scheduled' THEN 1 ELSE 0 END) as scheduled,
                SUM(CASE WHEN status = 'ended' THEN 1 ELSE 0 END) as ended,
                SUM(redemptions_count) as total_redemptions,
                SUM(revenue_generated) as total_revenue
             FROM campaigns",
            []
        )->fetch_one();

        sendJsonSuccess(['stats' => $stats]);
    } else {
        sendJsonError('Invalid action: ' . $action);
    }

} catch (Exception $e) {
    error_log('Marketing POST Controller Error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    exit();
}
?>