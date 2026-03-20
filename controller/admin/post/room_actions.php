<?php
/**
 * POST Controller - Admin Room Actions
 * Handles updating room status, maintenance, assignments, and room management
 */

// Enable error logging but disable display
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

session_start();
require_once __DIR__ . '/../../../Class/Database.php';

// Set JSON header for AJAX response
header('Content-Type: application/json');

try {
    // Check if user is logged in
    if (!isset($_SESSION['user_id']) || !$_SESSION['logged_in']) {
        throw new Exception('Please login to continue');
    }

    $config = require __DIR__ . '/../../../config/config.php';
    $db = new Database($config['database']);

    // Get user role from database
    $user = $db->query(
        "SELECT role FROM users WHERE id = :id",
        ['id' => $_SESSION['user_id']]
    )->fetch_one();

    // Check if user has admin or staff role
    if (!$user || !in_array($user['role'], ['admin', 'staff'])) {
        throw new Exception('Unauthorized access');
    }

    // Check if it's a POST request
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    $action = $_POST['action'] ?? '';

    // GET ALL ROOMS (for quick actions)
    if ($action === 'get_all_rooms') {
        $rooms = $db->query(
            "SELECT id, name, price, is_available, max_occupancy 
             FROM rooms ORDER BY id ASC",
            []
        )->find() ?: [];

        echo json_encode([
            'success' => true,
            'rooms' => $rooms
        ]);
        exit();
    }

    // GET ROOM DETAILS
    elseif ($action === 'get_room_details') {
        $room_id = $_POST['room_id'] ?? '';

        if (empty($room_id)) {
            throw new Exception('Room ID required');
        }

        $room = $db->query(
            "SELECT r.*,
                    CONCAT(b.guest_first_name, ' ', b.guest_last_name) as current_guest,
                    b.check_in,
                    b.check_out,
                    b.booking_reference,
                    b.status as booking_status
             FROM rooms r
             LEFT JOIN bookings b ON r.id = b.room_id 
                AND b.status IN ('confirmed', 'checked-in')
                AND b.check_out >= CURDATE()
             WHERE r.id = :id",
            ['id' => $room_id]
        )->fetch_one();

        if (!$room) {
            throw new Exception('Room not found');
        }

        echo json_encode([
            'success' => true,
            'room' => $room
        ]);
        exit();
    }

    // UPDATE ROOM STATUS
    elseif ($action === 'update_room_status') {
        $room_id = $_POST['room_id'] ?? '';
        $status = $_POST['status'] ?? '';
        $housekeeping = $_POST['housekeeping'] ?? '';

        if (empty($room_id)) {
            throw new Exception('Room ID required');
        }

        // Update room availability
        $is_available = ($status === 'available') ? 1 : 0;

        $db->query(
            "UPDATE rooms SET is_available = :available WHERE id = :id",
            [
                'available' => $is_available,
                'id' => $room_id
            ]
        );

        // If there's housekeeping update, add to room_maintenance table
        if ($housekeeping === 'dirty' || $housekeeping === 'maintenance') {
            $db->query(
                "INSERT INTO room_maintenance (room_id, condition_status, reported_at, reported_by, notes)
                 VALUES (:room_id, :status, NOW(), :reported_by, :notes)",
                [
                    'room_id' => $room_id,
                    'status' => $housekeeping === 'dirty' ? 'minor' : 'maintenance',
                    'reported_by' => $_SESSION['user_id'],
                    'notes' => 'Status updated via room management'
                ]
            );
        }

        // Create notification
        $db->query(
            "INSERT INTO notifications (user_id, title, message, type, icon, created_at) 
             VALUES (:user_id, 'Room Status Updated', :message, 'info', 'fa-bed', NOW())",
            [
                'user_id' => $_SESSION['user_id'],
                'message' => "Room {$room_id} status changed to {$status}"
            ]
        );

        echo json_encode([
            'success' => true,
            'message' => 'Room status updated successfully'
        ]);
        exit();
    }

    // EDIT ROOM
    elseif ($action === 'edit_room') {
        $room_id = $_POST['room_id'] ?? '';
        $name = trim($_POST['name'] ?? '');
        $price = floatval($_POST['price'] ?? 0);
        $max_occupancy = intval($_POST['max_occupancy'] ?? 2);
        $beds = trim($_POST['beds'] ?? '');
        $view = trim($_POST['view'] ?? '');
        $amenities = trim($_POST['amenities'] ?? '');
        $is_available = isset($_POST['is_available']) ? 1 : 0;

        if (empty($room_id) || empty($name) || $price <= 0) {
            throw new Exception('Room ID, name, and valid price are required');
        }

        $db->query(
            "UPDATE rooms SET 
                name = :name,
                price = :price,
                max_occupancy = :max_occupancy,
                beds = :beds,
                view = :view,
                amenities = :amenities,
                is_available = :is_available
             WHERE id = :id",
            [
                'id' => $room_id,
                'name' => $name,
                'price' => $price,
                'max_occupancy' => $max_occupancy,
                'beds' => $beds,
                'view' => $view,
                'amenities' => $amenities,
                'is_available' => $is_available
            ]
        );

        // Create notification
        $db->query(
            "INSERT INTO notifications (user_id, title, message, type, icon, created_at) 
             VALUES (:user_id, 'Room Updated', :message, 'success', 'fa-pen-to-square', NOW())",
            [
                'user_id' => $_SESSION['user_id'],
                'message' => "Room {$room_id} details were updated"
            ]
        );

        echo json_encode([
            'success' => true,
            'message' => 'Room updated successfully'
        ]);
        exit();
    }

    // ADD NEW ROOM
    elseif ($action === 'add_new_room') {
        $room_id = strtoupper(trim($_POST['room_id'] ?? ''));
        $name = trim($_POST['name'] ?? '');
        $price = floatval($_POST['price'] ?? 0);
        $max_occupancy = intval($_POST['max_occupancy'] ?? 2);
        $beds = trim($_POST['beds'] ?? '');
        $view = trim($_POST['view'] ?? '');
        $amenities = trim($_POST['amenities'] ?? '');

        if (empty($room_id) || empty($name) || $price <= 0) {
            throw new Exception('Room ID, name, and valid price are required');
        }

        // Check if room ID already exists
        $existing = $db->query(
            "SELECT id FROM rooms WHERE id = :id",
            ['id' => $room_id]
        )->fetch_one();

        if ($existing) {
            throw new Exception('Room ID already exists');
        }

        $db->query(
            "INSERT INTO rooms (id, name, price, max_occupancy, beds, view, amenities, is_available, needs_cleaning, created_at)
             VALUES (:id, :name, :price, :max_occupancy, :beds, :view, :amenities, 1, 0, NOW())",
            [
                'id' => $room_id,
                'name' => $name,
                'price' => $price,
                'max_occupancy' => $max_occupancy,
                'beds' => $beds,
                'view' => $view,
                'amenities' => $amenities
            ]
        );

        // Create notification
        $db->query(
            "INSERT INTO notifications (user_id, title, message, type, icon, created_at) 
             VALUES (:user_id, 'New Room Added', :message, 'success', 'fa-plus-circle', NOW())",
            [
                'user_id' => $_SESSION['user_id'],
                'message' => "New room {$room_id} ({$name}) was added"
            ]
        );

        echo json_encode([
            'success' => true,
            'message' => 'New room added successfully',
            'room_id' => $room_id
        ]);
        exit();
    }

    // DELETE ROOM (Admin only - soft delete by marking unavailable)
    elseif ($action === 'delete_room') {
        $room_id = $_POST['room_id'] ?? '';

        if (empty($room_id)) {
            throw new Exception('Room ID required');
        }

        // Check if room has active bookings
        $activeBookings = $db->query(
            "SELECT COUNT(*) as count FROM bookings 
             WHERE room_id = :room_id AND status IN ('confirmed', 'checked-in')",
            ['room_id' => $room_id]
        )->fetch_one();

        if ($activeBookings['count'] > 0) {
            throw new Exception('Cannot delete room with active bookings');
        }

        // Soft delete by marking as unavailable and removing from active listings
        $db->query(
            "UPDATE rooms SET is_available = 0, name = CONCAT(name, ' (deleted)') WHERE id = :id",
            ['id' => $room_id]
        );

        echo json_encode([
            'success' => true,
            'message' => 'Room deactivated successfully'
        ]);
        exit();
    }

    // ADD MAINTENANCE
    elseif ($action === 'add_maintenance') {
        $room_id = $_POST['room_id'] ?? '';
        $issue = trim($_POST['issue'] ?? '');
        $priority = $_POST['priority'] ?? 'medium';
        $scheduled_date = $_POST['scheduled_date'] ?? '';

        if (empty($room_id) || empty($issue)) {
            throw new Exception('Room ID and issue description required');
        }

        $db->beginTransaction();

        $db->query(
            "INSERT INTO room_maintenance (room_id, condition_status, reported_at, reported_by, notes)
             VALUES (:room_id, :status, NOW(), :reported_by, :notes)",
            [
                'room_id' => $room_id,
                'status' => $priority === 'high' ? 'damage' : 'maintenance',
                'reported_by' => $_SESSION['user_id'],
                'notes' => $issue . ($scheduled_date ? " (Scheduled: $scheduled_date)" : "")
            ]
        );

        // Update room availability and mark as not available
        $db->query(
            "UPDATE rooms SET is_available = 0, needs_cleaning = 0 WHERE id = :id",
            ['id' => $room_id]
        );

        $db->commit();

        // Create notification
        $db->query(
            "INSERT INTO notifications (user_id, title, message, type, icon, created_at) 
             VALUES (:user_id, 'Maintenance Reported', :message, 'warning', 'fa-wrench', NOW())",
            [
                'user_id' => $_SESSION['user_id'],
                'message' => "Maintenance reported for room {$room_id}: {$issue}"
            ]
        );

        echo json_encode([
            'success' => true,
            'message' => 'Maintenance task added successfully'
        ]);
        exit();
    }

    // GET MAINTENANCE LIST
    elseif ($action === 'get_maintenance_list') {
        $maintenance = $db->query(
            "SELECT rm.*, r.id as room_number, u.full_name as reported_by_name
             FROM room_maintenance rm
             LEFT JOIN rooms r ON rm.room_id = r.id
             LEFT JOIN users u ON rm.reported_by = u.id
             WHERE rm.cleaned_at IS NULL
             ORDER BY 
                CASE 
                    WHEN rm.condition_status = 'damage' THEN 1
                    WHEN rm.condition_status = 'maintenance' THEN 2
                    ELSE 3
                END,
                rm.reported_at ASC",
            []
        )->find() ?: [];

        echo json_encode([
            'success' => true,
            'maintenance' => $maintenance
        ]);
        exit();
    }

    // COMPLETE MAINTENANCE
    elseif ($action === 'complete_maintenance') {
        $maintenance_id = intval($_POST['maintenance_id'] ?? 0);

        if (!$maintenance_id) {
            throw new Exception('Maintenance ID required');
        }

        $db->beginTransaction();

        // Get room_id before completing
        $maintenance = $db->query(
            "SELECT room_id FROM room_maintenance WHERE id = :id",
            ['id' => $maintenance_id]
        )->fetch_one();

        $db->query(
            "UPDATE room_maintenance SET cleaned_at = NOW() WHERE id = :id",
            ['id' => $maintenance_id]
        );

        // Check if room has any other pending maintenance
        $pending = $db->query(
            "SELECT COUNT(*) as count FROM room_maintenance 
             WHERE room_id = :room_id AND cleaned_at IS NULL",
            ['room_id' => $maintenance['room_id']]
        )->fetch_one();

        // If no other pending maintenance, make room available but needs cleaning
        if ($pending['count'] == 0) {
            $db->query(
                "UPDATE rooms SET is_available = 1, needs_cleaning = 1 WHERE id = :id",
                ['id' => $maintenance['room_id']]
            );
        }

        $db->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Maintenance marked as completed'
        ]);
        exit();
    }

    // ASSIGN ROOM TO GUEST
    elseif ($action === 'assign_room') {
        $room_id = $_POST['room_id'] ?? '';
        $guest_name = trim($_POST['guest_name'] ?? '');
        $check_in = $_POST['check_in'] ?? '';
        $check_out = $_POST['check_out'] ?? '';
        $adults = intval($_POST['adults'] ?? 1);
        $children = intval($_POST['children'] ?? 0);

        if (empty($room_id) || empty($guest_name) || empty($check_in) || empty($check_out)) {
            throw new Exception('All fields are required');
        }

        // Split guest name
        $name_parts = explode(' ', trim($guest_name), 2);
        $first_name = $name_parts[0];
        $last_name = $name_parts[1] ?? '';

        // Calculate nights
        $checkInDate = new DateTime($check_in);
        $checkOutDate = new DateTime($check_out);
        $nights = $checkInDate->diff($checkOutDate)->days;

        if ($nights < 1) {
            throw new Exception('Check-out must be after check-in');
        }

        // Get room price
        $room = $db->query(
            "SELECT price, name FROM rooms WHERE id = :id",
            ['id' => $room_id]
        )->fetch_one();

        // Calculate totals
        $subtotal = $room['price'] * $nights;
        $tax = $subtotal * 0.12;
        $total = $subtotal + $tax;
        $points_earned = floor($total / 100) * 5;

        // Generate booking reference
        $reference = 'HR' . date('Ymd') . strtoupper(substr(uniqid(), -6));

        $db->beginTransaction();

        // Create booking
        $db->query(
            "INSERT INTO bookings (
                booking_reference, guest_first_name, guest_last_name,
                check_in, check_out, nights, room_id, room_name,
                room_price, adults, children, subtotal, tax, total_amount,
                points_earned, status, payment_status, created_at, updated_at
            ) VALUES (
                :reference, :first_name, :last_name,
                :check_in, :check_out, :nights, :room_id, :room_name,
                :price, :adults, :children, :subtotal, :tax, :total,
                :points, 'confirmed', 'unpaid', NOW(), NOW()
            )",
            [
                'reference' => $reference,
                'first_name' => $first_name,
                'last_name' => $last_name,
                'check_in' => $check_in,
                'check_out' => $check_out,
                'nights' => $nights,
                'room_id' => $room_id,
                'room_name' => $room['name'],
                'price' => $room['price'],
                'adults' => $adults,
                'children' => $children,
                'subtotal' => $subtotal,
                'tax' => $tax,
                'total' => $total,
                'points' => $points_earned
            ]
        );

        // Update room availability and cleaning status
        $db->query(
            "UPDATE rooms SET is_available = 0, needs_cleaning = 0 WHERE id = :id",
            ['id' => $room_id]
        );

        // Create notification
        $db->query(
            "INSERT INTO notifications (user_id, title, message, type, icon, created_at) 
             VALUES (:user_id, 'Room Assigned', :message, 'success', 'fa-user-plus', NOW())",
            [
                'user_id' => $_SESSION['user_id'],
                'message' => "Room {$room_id} assigned to {$guest_name}"
            ]
        );

        $db->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Room assigned successfully',
            'booking_reference' => $reference
        ]);
        exit();
    }

    // ========== CHECK-IN GUEST (ADD THIS) ==========
    elseif ($action === 'checkin_guest') {
        $booking_id = $_POST['booking_id'] ?? '';
        $room_id = $_POST['room_id'] ?? '';

        if (empty($booking_id) || empty($room_id)) {
            throw new Exception('Booking ID and Room ID required');
        }

        $db->beginTransaction();

        // Get booking details
        $booking = $db->query(
            "SELECT * FROM bookings WHERE booking_reference = :reference OR id = :id",
            [
                'reference' => $booking_id,
                'id' => is_numeric($booking_id) ? $booking_id : 0
            ]
        )->fetch_one();

        if (!$booking) {
            throw new Exception('Booking not found');
        }

        // Update booking status to checked-in
        $db->query(
            "UPDATE bookings SET 
                status = 'checked-in',
                check_in_time = NOW(),
                updated_at = NOW()
             WHERE id = :id",
            ['id' => $booking['id']]
        );

        // Update room to occupied
        $db->query(
            "UPDATE rooms SET 
                is_available = 0,
                needs_cleaning = 0
             WHERE id = :id",
            ['id' => $room_id]
        );

        // Create notification for admin
        $db->query(
            "INSERT INTO notifications (user_id, title, message, type, icon, created_at) 
             VALUES (:user_id, 'Guest Checked In', :message, 'success', 'fa-calendar-check', NOW())",
            [
                'user_id' => $_SESSION['user_id'],
                'message' => "Guest checked in to room {$room_id}"
            ]
        );

        // If booking has a user, notify them
        if ($booking['user_id']) {
            $db->query(
                "INSERT INTO notifications (user_id, title, message, type, icon, link, created_at) 
                 VALUES (:user_id, 'Checked In', :message, 'success', 'fa-door-open', '/src/customer_portal/my_reservation.php', NOW())",
                [
                    'user_id' => $booking['user_id'],
                    'message' => "You have been checked in to room {$room_id}. Enjoy your stay!"
                ]
            );
        }

        $db->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Guest checked in successfully'
        ]);
        exit();
    }

    // CHECK-OUT GUEST
    elseif ($action === 'checkout_guest') {
        $booking_id = $_POST['booking_id'] ?? '';
        $room_id = $_POST['room_id'] ?? '';

        if (empty($booking_id) || empty($room_id)) {
            throw new Exception('Booking ID and Room ID required');
        }

        $db->beginTransaction();

        // Update booking status
        $db->query(
            "UPDATE bookings SET 
                status = 'completed',
                updated_at = NOW()
             WHERE booking_reference = :reference OR id = :id",
            [
                'reference' => $booking_id,
                'id' => is_numeric($booking_id) ? $booking_id : 0
            ]
        );

        // Mark room as needing cleaning but available
        $db->query(
            "UPDATE rooms SET 
                is_available = 1,
                needs_cleaning = 1
             WHERE id = :id",
            ['id' => $room_id]
        );

        $db->commit();

        // Create notification
        $db->query(
            "INSERT INTO notifications (user_id, title, message, type, icon, created_at) 
             VALUES (:user_id, 'Guest Checked Out', :message, 'info', 'fa-door-open', NOW())",
            [
                'user_id' => $_SESSION['user_id'],
                'message' => "Guest checked out from room {$room_id}. Room marked for cleaning."
            ]
        );

        echo json_encode([
            'success' => true,
            'message' => 'Guest checked out successfully. Room marked for cleaning.'
        ]);
        exit();
    }

    // MARK ROOM AS CLEAN
    elseif ($action === 'mark_as_clean') {
        $room_id = $_POST['room_id'] ?? '';

        if (empty($room_id)) {
            throw new Exception('Room ID required');
        }

        $db->query(
            "UPDATE rooms SET 
                needs_cleaning = 0
             WHERE id = :id",
            ['id' => $room_id]
        );

        // Create notification
        $db->query(
            "INSERT INTO notifications (user_id, title, message, type, icon, created_at) 
             VALUES (:user_id, 'Room Marked Clean', :message, 'success', 'fa-sparkles', NOW())",
            [
                'user_id' => $_SESSION['user_id'],
                'message' => "Room {$room_id} has been marked as clean and ready for guests."
            ]
        );

        echo json_encode([
            'success' => true,
            'message' => 'Room marked as clean'
        ]);
        exit();
    }

    // BULK UPDATE ROOMS
    elseif ($action === 'bulk_update') {
        $room_ids = $_POST['room_ids'] ?? '';
        $action_type = $_POST['bulk_action'] ?? '';
        $value = $_POST['value'] ?? '';

        if (empty($room_ids) || empty($action_type)) {
            throw new Exception('Rooms and action type required');
        }

        $ids = explode(',', $room_ids);
        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        switch ($action_type) {
            case 'set_available':
                $db->query(
                    "UPDATE rooms SET is_available = 1 WHERE id IN ($placeholders)",
                    $ids
                );
                $message = 'Selected rooms marked as available';
                break;

            case 'set_unavailable':
                $db->query(
                    "UPDATE rooms SET is_available = 0 WHERE id IN ($placeholders)",
                    $ids
                );
                $message = 'Selected rooms marked as unavailable';
                break;

            case 'update_price':
                if (empty($value) || !is_numeric($value)) {
                    throw new Exception('Valid price required');
                }
                $db->query(
                    "UPDATE rooms SET price = ? WHERE id IN ($placeholders)",
                    array_merge([$value], $ids)
                );
                $message = "Price updated to ₱" . number_format($value) . " for selected rooms";
                break;

            default:
                throw new Exception('Invalid bulk action');
        }

        // Create notification
        $db->query(
            "INSERT INTO notifications (user_id, title, message, type, icon, created_at) 
             VALUES (:user_id, 'Bulk Update Completed', :message, 'info', 'fa-layer-group', NOW())",
            [
                'user_id' => $_SESSION['user_id'],
                'message' => $message
            ]
        );

        echo json_encode([
            'success' => true,
            'message' => $message
        ]);
        exit();
    }

    // GET ROOM TYPES STATISTICS
    elseif ($action === 'get_room_types') {
        $types = $db->query(
            "SELECT 
                name as type,
                COUNT(*) as count,
                SUM(CASE WHEN is_available = 1 THEN 1 ELSE 0 END) as available,
                AVG(price) as avg_price
             FROM rooms
             GROUP BY name
             ORDER BY count DESC",
            []
        )->find() ?: [];

        echo json_encode([
            'success' => true,
            'types' => $types
        ]);
        exit();
    } else {
        throw new Exception('Invalid action');
    }

} catch (Exception $e) {
    // Rollback transaction if active
    if (isset($db)) {
        try {
            $db->rollBack();
        } catch (Exception $rollbackError) {
            // Ignore rollback errors
        }
    }

    // Log the error
    error_log('Room Actions Error: ' . $e->getMessage());
    error_log('POST Data: ' . print_r($_POST, true));

    // Return JSON error response
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
    exit();
}
?>