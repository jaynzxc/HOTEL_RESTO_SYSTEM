<?php
/**
 * POST Controller - Admin Table Reservation Actions
 * Handles updating reservation status, seating guests, and managing waitlist
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

    // CHECK TABLE AVAILABILITY
    if ($action === 'check_availability') {
        $guests = intval($_POST['guests'] ?? 0);

        // Find available tables that can accommodate the party size
        $availableTables = $db->query(
            "SELECT * FROM restaurant_tables 
             WHERE status = 'available' 
             AND capacity >= :guests
             ORDER BY capacity ASC",
            ['guests' => $guests]
        )->find() ?: [];

        // Count total available tables
        $totalAvailable = count($availableTables);

        echo json_encode([
            'success' => true,
            'available' => $totalAvailable > 0,
            'available_tables' => $availableTables,
            'count' => $totalAvailable,
            'message' => $totalAvailable > 0 ? 'Tables available' : 'No tables available for this party size'
        ]);
        exit();
    }

    // CREATE WALK-IN RESERVATION WITH AUTO WAITLIST
    elseif ($action === 'create_walkin') {
        $guest_name = trim($_POST['guest_name'] ?? '');
        $guest_phone = trim($_POST['guest_phone'] ?? '');
        $guest_email = trim($_POST['guest_email'] ?? '');
        $guests = intval($_POST['guests'] ?? 0);
        $reservation_time = $_POST['reservation_time'] ?? '';
        $special_requests = trim($_POST['special_requests'] ?? '');

        if (empty($guest_name) || $guests < 1 || empty($reservation_time)) {
            throw new Exception('Guest name, party size, and time required');
        }

        $db->beginTransaction();

        // Check for available tables that can accommodate the party
        $availableTables = $db->query(
            "SELECT * FROM restaurant_tables 
             WHERE status = 'available' 
             AND capacity >= :guests
             ORDER BY capacity ASC",
            ['guests' => $guests]
        )->find() ?: [];

        // Check if there are any available tables
        if (empty($availableTables)) {
            // No tables available - add to waitlist automatically
            $db->query(
                "INSERT INTO waiting_list (
                    guest_name, guest_phone, party_size, requested_time, 
                    wait_started_at, status, notes
                 ) VALUES (
                    :name, :phone, :size, :time, NOW(), 'waiting', :notes
                 )",
                [
                    'name' => $guest_name,
                    'phone' => $guest_phone,
                    'size' => $guests,
                    'time' => $reservation_time,
                    'notes' => 'Auto-added from walk-in - no tables available'
                ]
            );

            $db->commit();

            echo json_encode([
                'success' => true,
                'added_to_waitlist' => true,
                'message' => 'No tables available. Guest added to waitlist.',
                'waitlist' => true
            ]);
            exit();
        }

        // Tables are available - proceed with reservation
        // Use the smallest available table that fits
        $selectedTable = $availableTables[0];

        // Split name
        $name_parts = explode(' ', trim($guest_name), 2);
        $first_name = $name_parts[0];
        $last_name = $name_parts[1] ?? '';

        // Generate reference
        $reference = 'WALK-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

        // Calculate points to earn (1 point per ₱100 down payment - default ₱500 per guest)
        $down_payment = $guests * 500; // Default ₱500 per guest down payment
        $points_earned = floor($down_payment / 100) * 5;

        // Insert reservation
        $db->query(
            "INSERT INTO restaurant_reservations (
                reservation_reference, guest_first_name, guest_last_name,
                guest_email, guest_phone, reservation_date, reservation_time,
                guests, table_number, special_requests, down_payment, points_earned,
                status, payment_status, created_at, updated_at
             ) VALUES (
                :ref, :first, :last, :email, :phone, CURDATE(), :time,
                :guests, :table, :requests, :down_payment, :points,
                'confirmed', 'unpaid', NOW(), NOW()
             )",
            [
                'ref' => $reference,
                'first' => $first_name,
                'last' => $last_name,
                'email' => $guest_email,
                'phone' => $guest_phone,
                'time' => $reservation_time,
                'guests' => $guests,
                'table' => $selectedTable['table_number'],
                'requests' => $special_requests,
                'down_payment' => $down_payment,
                'points' => $points_earned
            ]
        );

        // Update table status to reserved
        $db->query(
            "UPDATE restaurant_tables SET status = 'reserved' WHERE id = :id",
            ['id' => $selectedTable['id']]
        );

        $db->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Walk-in reservation created successfully',
            'table_assigned' => $selectedTable['table_number'],
            'capacity' => $selectedTable['capacity'],
            'points_earned' => $points_earned,
            'down_payment' => $down_payment
        ]);
        exit();
    }

    // AWARD POINTS TO USER
    elseif ($action === 'award_points') {
        $reservation_id = intval($_POST['reservation_id'] ?? 0);

        if (!$reservation_id) {
            throw new Exception('Reservation ID required');
        }

        $db->beginTransaction();

        // Get reservation details
        $reservation = $db->query(
            "SELECT rr.*, u.id as user_id, u.loyalty_points 
             FROM restaurant_reservations rr
             LEFT JOIN users u ON rr.user_id = u.id
             WHERE rr.id = :id",
            ['id' => $reservation_id]
        )->fetch_one();

        if (!$reservation) {
            throw new Exception('Reservation not found');
        }

        if (!$reservation['user_id']) {
            throw new Exception('This reservation is not associated with a registered user');
        }

        if ($reservation['points_awarded']) {
            throw new Exception('Points already awarded for this reservation');
        }

        if ($reservation['payment_status'] !== 'paid') {
            throw new Exception('Cannot award points: Payment not yet completed');
        }

        // Add points to user
        $db->query(
            "UPDATE users SET loyalty_points = loyalty_points + :points WHERE id = :user_id",
            [
                'points' => $reservation['points_earned'],
                'user_id' => $reservation['user_id']
            ]
        );

        // Mark points as awarded
        $db->query(
            "UPDATE restaurant_reservations SET 
                points_awarded = 1, 
                points_awarded_at = NOW() 
             WHERE id = :id",
            ['id' => $reservation_id]
        );

        // Create notification for user
        $db->query(
            "INSERT INTO notifications (user_id, title, message, type, icon, link, created_at) 
             VALUES (:user_id, 'Loyalty Points Awarded!', :message, 'loyalty', 'fa-star', '/src/customer_portal/loyalty_rewards.php', NOW())",
            [
                'user_id' => $reservation['user_id'],
                'message' => "You've earned {$reservation['points_earned']} loyalty points for your reservation #{$reservation['reservation_reference']}"
            ]
        );

        // Create notification for admin
        $db->query(
            "INSERT INTO notifications (user_id, title, message, type, icon, created_at) 
             VALUES (:user_id, 'Points Awarded', :message, 'success', 'fa-star', NOW())",
            [
                'user_id' => $_SESSION['user_id'],
                'message' => "Added {$reservation['points_earned']} points to user for reservation #{$reservation['reservation_reference']}"
            ]
        );

        $db->commit();

        echo json_encode([
            'success' => true,
            'message' => "{$reservation['points_earned']} points awarded successfully",
            'points_earned' => $reservation['points_earned']
        ]);
        exit();
    }

    // SEAT FROM WAITLIST
    elseif ($action === 'seat_from_waitlist') {
        $waitlist_id = intval($_POST['waitlist_id'] ?? 0);
        $table_number = $_POST['table_number'] ?? '';

        if (!$waitlist_id || !$table_number) {
            throw new Exception('Waitlist ID and table number required');
        }

        $db->beginTransaction();

        // Get waitlist entry
        $waitlist = $db->query(
            "SELECT * FROM waiting_list WHERE id = :id",
            ['id' => $waitlist_id]
        )->fetch_one();

        if (!$waitlist) {
            throw new Exception('Waitlist entry not found');
        }

        // Get table details
        $table = $db->query(
            "SELECT * FROM restaurant_tables WHERE table_number = :table",
            ['table' => $table_number]
        )->fetch_one();

        if (!$table) {
            throw new Exception('Table not found');
        }

        // Check if table can accommodate party
        if ($table['capacity'] < $waitlist['party_size']) {
            throw new Exception('Table capacity is too small for this party');
        }

        // Split name for reservation
        $name_parts = explode(' ', trim($waitlist['guest_name']), 2);
        $first_name = $name_parts[0];
        $last_name = $name_parts[1] ?? '';

        // Generate reference
        $reference = 'WAIT-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

        // Calculate down payment and points
        $down_payment = $waitlist['party_size'] * 500;
        $points_earned = floor($down_payment / 100) * 5;

        // Create reservation from waitlist
        $db->query(
            "INSERT INTO restaurant_reservations (
                reservation_reference, guest_first_name, guest_last_name,
                guest_phone, reservation_date, reservation_time,
                guests, table_number, down_payment, points_earned,
                status, payment_status, created_at, updated_at
             ) VALUES (
                :ref, :first, :last, :phone, CURDATE(), :time,
                :guests, :table, :down_payment, :points,
                'seated', 'unpaid', NOW(), NOW()
             )",
            [
                'ref' => $reference,
                'first' => $first_name,
                'last' => $last_name,
                'phone' => $waitlist['guest_phone'],
                'time' => $waitlist['requested_time'] ?: date('H:i:s'),
                'guests' => $waitlist['party_size'],
                'table' => $table_number,
                'down_payment' => $down_payment,
                'points' => $points_earned
            ]
        );

        // Update waitlist status
        $db->query(
            "UPDATE waiting_list SET status = 'seated' WHERE id = :id",
            ['id' => $waitlist_id]
        );

        // Update table status
        $db->query(
            "UPDATE restaurant_tables SET status = 'occupied' WHERE id = :id",
            ['id' => $table['id']]
        );

        $db->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Guest seated successfully',
            'table_number' => $table_number,
            'points_earned' => $points_earned
        ]);
        exit();
    }

    // UPDATE RESERVATION STATUS WITH BALANCE CHECK
    elseif ($action === 'update_status') {
        $reservation_id = intval($_POST['reservation_id'] ?? 0);
        $status = $_POST['status'] ?? '';
        $table_number = isset($_POST['table_number']) ? $_POST['table_number'] : null;

        if (!$reservation_id) {
            throw new Exception('Reservation ID required');
        }

        if (!in_array($status, ['pending', 'confirmed', 'seated', 'cancelled', 'completed', 'no-show'])) {
            throw new Exception('Invalid status');
        }

        $db->beginTransaction();

        // Get reservation details with user balance
        $reservation = $db->query(
            "SELECT rr.*, u.id as user_id, COALESCE(cb.total_balance, 0) as current_balance
             FROM restaurant_reservations rr
             LEFT JOIN users u ON rr.user_id = u.id
             LEFT JOIN current_balance cb ON rr.user_id = cb.user_id
             WHERE rr.id = :id",
            ['id' => $reservation_id]
        )->fetch_one();

        if (!$reservation) {
            throw new Exception('Reservation not found');
        }

        $old_status = $reservation['status'];
        $old_table = $reservation['table_number'];

        // Check for outstanding balance before allowing certain actions
        if (in_array($status, ['seated', 'confirmed']) && $reservation['user_id'] && $reservation['current_balance'] > 0) {
            // If there's an outstanding balance, return warning but allow action with warning
            $warning = "Warning: Guest has outstanding balance of ₱" . number_format($reservation['current_balance'], 2);
        }

        // Update reservation status
        $query = "UPDATE restaurant_reservations SET 
                    status = :status,
                    updated_at = NOW()";

        $params = [
            'id' => $reservation_id,
            'status' => $status
        ];

        // Only update table_number if provided
        if ($table_number !== null && $table_number !== '') {
            $query .= ", table_number = :table_number";
            $params['table_number'] = $table_number;
        }

        $query .= " WHERE id = :id";

        $db->query($query, $params);

        // Handle table status changes
        if ($status === 'seated' && $table_number) {
            // Mark new table as occupied
            $db->query(
                "UPDATE restaurant_tables SET status = 'occupied' WHERE table_number = :table",
                ['table' => $table_number]
            );

            // If there was an old table and it's different, free it up
            if ($old_table && $old_table !== $table_number) {
                $db->query(
                    "UPDATE restaurant_tables SET status = 'available' WHERE table_number = :table",
                    ['table' => $old_table]
                );
            }
        } elseif (in_array($status, ['cancelled', 'completed', 'no-show']) && $old_table) {
            // Free up the table
            $db->query(
                "UPDATE restaurant_tables SET status = 'available' WHERE table_number = :table",
                ['table' => $old_table]
            );

            // Check if there are people waiting that can now be seated
            $waitingList = $db->query(
                "SELECT wl.*, rt.id as table_id, rt.table_number 
                 FROM waiting_list wl
                 JOIN restaurant_tables rt ON rt.capacity >= wl.party_size
                 WHERE wl.status = 'waiting' 
                 AND rt.status = 'available'
                 ORDER BY wl.wait_started_at ASC, rt.capacity ASC
                 LIMIT 1",
                []
            )->fetch_one();

            if ($waitingList) {
                // Auto-seat the next person in waitlist
                $name_parts = explode(' ', trim($waitingList['guest_name']), 2);
                $first_name = $name_parts[0];
                $last_name = $name_parts[1] ?? '';

                $ref = 'WAIT-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));
                $down_payment = $waitingList['party_size'] * 500;
                $points_earned = floor($down_payment / 100) * 5;

                $db->query(
                    "INSERT INTO restaurant_reservations (
                        reservation_reference, guest_first_name, guest_last_name,
                        guest_phone, reservation_date, reservation_time,
                        guests, table_number, down_payment, points_earned,
                        status, payment_status, created_at, updated_at
                     ) VALUES (
                        :ref, :first, :last, :phone, CURDATE(), :time,
                        :guests, :table, :down_payment, :points,
                        'seated', 'unpaid', NOW(), NOW()
                     )",
                    [
                        'ref' => $ref,
                        'first' => $first_name,
                        'last' => $last_name,
                        'phone' => $waitingList['guest_phone'],
                        'time' => $waitingList['requested_time'] ?: date('H:i:s'),
                        'guests' => $waitingList['party_size'],
                        'table' => $waitingList['table_number'],
                        'down_payment' => $down_payment,
                        'points' => $points_earned
                    ]
                );

                $db->query(
                    "UPDATE waiting_list SET status = 'seated' WHERE id = :id",
                    ['id' => $waitingList['id']]
                );

                $db->query(
                    "UPDATE restaurant_tables SET status = 'occupied' WHERE id = :id",
                    ['id' => $waitingList['table_id']]
                );
            }
        }

        // Create notification
        $notification_message = "Reservation #{$reservation['reservation_reference']} status changed from {$old_status} to {$status}";
        if (isset($warning)) {
            $notification_message .= " (Guest has outstanding balance)";
        }

        $db->query(
            "INSERT INTO notifications (user_id, title, message, type, icon, created_at) 
             VALUES (:user_id, 'Reservation Updated', :message, 'info', 'fa-calendar-check', NOW())",
            [
                'user_id' => $_SESSION['user_id'],
                'message' => $notification_message
            ]
        );

        $db->commit();

        $response = [
            'success' => true,
            'message' => 'Reservation status updated successfully',
            'old_status' => $old_status,
            'new_status' => $status
        ];

        // Add warning if there's outstanding balance
        if (isset($warning)) {
            $response['warning'] = $warning;
            $response['has_outstanding_balance'] = true;
            $response['balance_amount'] = $reservation['current_balance'];
        }

        echo json_encode($response);
        exit();
    }

    // ADD TO WAITLIST (Manual)
    elseif ($action === 'add_to_waitlist') {
        $guest_name = trim($_POST['guest_name'] ?? '');
        $guest_phone = trim($_POST['guest_phone'] ?? '');
        $party_size = intval($_POST['party_size'] ?? 0);
        $requested_time = $_POST['requested_time'] ?? '';

        if (empty($guest_name) || $party_size < 1) {
            throw new Exception('Guest name and party size required');
        }

        $db->query(
            "INSERT INTO waiting_list (guest_name, guest_phone, party_size, requested_time, wait_started_at, status)
             VALUES (:name, :phone, :size, :time, NOW(), 'waiting')",
            [
                'name' => $guest_name,
                'phone' => $guest_phone,
                'size' => $party_size,
                'time' => $requested_time
            ]
        );

        echo json_encode([
            'success' => true,
            'message' => 'Guest added to waitlist'
        ]);
        exit();
    }

    // REMOVE FROM WAITLIST
    elseif ($action === 'remove_from_waitlist') {
        $waitlist_id = intval($_POST['waitlist_id'] ?? 0);

        if (!$waitlist_id) {
            throw new Exception('Waitlist ID required');
        }

        $db->query(
            "DELETE FROM waiting_list WHERE id = :id",
            ['id' => $waitlist_id]
        );

        echo json_encode([
            'success' => true,
            'message' => 'Guest removed from waitlist'
        ]);
        exit();
    }

    // GET RESERVATION DETAILS
    elseif ($action === 'get_reservation_details') {
        $reservation_id = intval($_POST['reservation_id'] ?? 0);

        if (!$reservation_id) {
            throw new Exception('Reservation ID required');
        }

        $reservation = $db->query(
            "SELECT rr.*, u.member_tier, u.loyalty_points, COALESCE(cb.total_balance, 0) as current_balance
             FROM restaurant_reservations rr
             LEFT JOIN users u ON rr.user_id = u.id
             LEFT JOIN current_balance cb ON rr.user_id = cb.user_id
             WHERE rr.id = :id",
            ['id' => $reservation_id]
        )->fetch_one();

        if (!$reservation) {
            throw new Exception('Reservation not found');
        }

        echo json_encode([
            'success' => true,
            'reservation' => $reservation,
            'has_outstanding_balance' => $reservation['current_balance'] > 0,
            'outstanding_balance' => $reservation['current_balance']
        ]);
        exit();
    }

    // GET WAITLIST
    elseif ($action === 'get_waitlist') {
        $waitlist = $db->query(
            "SELECT * FROM waiting_list WHERE status = 'waiting' ORDER BY wait_started_at ASC",
            []
        )->find() ?: [];

        echo json_encode([
            'success' => true,
            'waitlist' => $waitlist
        ]);
        exit();
    }

    // CHECK AND AUTO-SEAT WAITLIST
    elseif ($action === 'check_and_seat_waitlist') {
        $db->beginTransaction();

        // Find any waiting guests that can be seated
        $waitingGuests = $db->query(
            "SELECT wl.*, rt.id as table_id, rt.table_number 
             FROM waiting_list wl
             JOIN restaurant_tables rt ON rt.capacity >= wl.party_size
             WHERE wl.status = 'waiting' 
             AND rt.status = 'available'
             ORDER BY wl.wait_started_at ASC, rt.capacity ASC",
            []
        )->find() ?: [];

        $seated = 0;
        $processedIds = [];

        foreach ($waitingGuests as $guest) {
            if (in_array($guest['id'], $processedIds))
                continue;

            // Seat this guest
            $name_parts = explode(' ', trim($guest['guest_name']), 2);
            $first_name = $name_parts[0];
            $last_name = $name_parts[1] ?? '';

            $ref = 'WAIT-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));
            $down_payment = $guest['party_size'] * 500;
            $points_earned = floor($down_payment / 100) * 5;

            $db->query(
                "INSERT INTO restaurant_reservations (
                    reservation_reference, guest_first_name, guest_last_name,
                    guest_phone, reservation_date, reservation_time,
                    guests, table_number, down_payment, points_earned,
                    status, payment_status, created_at, updated_at
                 ) VALUES (
                    :ref, :first, :last, :phone, CURDATE(), :time,
                    :guests, :table, :down_payment, :points,
                    'seated', 'unpaid', NOW(), NOW()
                 )",
                [
                    'ref' => $ref,
                    'first' => $first_name,
                    'last' => $last_name,
                    'phone' => $guest['guest_phone'],
                    'time' => $guest['requested_time'] ?: date('H:i:s'),
                    'guests' => $guest['party_size'],
                    'table' => $guest['table_number'],
                    'down_payment' => $down_payment,
                    'points' => $points_earned
                ]
            );

            $db->query(
                "UPDATE waiting_list SET status = 'seated' WHERE id = :id",
                ['id' => $guest['id']]
            );

            $db->query(
                "UPDATE restaurant_tables SET status = 'occupied' WHERE id = :id",
                ['id' => $guest['table_id']]
            );

            $seated++;
            $processedIds[] = $guest['id'];
        }

        $db->commit();

        echo json_encode([
            'success' => true,
            'seated' => $seated,
            'message' => $seated > 0 ? "$seated guest(s) seated from waitlist" : 'No guests could be seated'
        ]);
        exit();
    }

    // EXPORT RESERVATIONS
    elseif ($action === 'export_reservations') {
        $date = $_POST['date'] ?? date('Y-m-d');

        $reservations = $db->query(
            "SELECT 
                reservation_time as Time,
                CONCAT(guest_first_name, ' ', guest_last_name) as Guest,
                table_number as 'Table',
                guests as Pax,
                status as Status,
                special_requests as 'Special Requests',
                payment_status as 'Payment',
                CONCAT('₱', FORMAT(down_payment, 2)) as 'Down Payment',
                points_earned as 'Points'
             FROM restaurant_reservations
             WHERE reservation_date = :date
             ORDER BY reservation_time ASC",
            ['date' => $date]
        )->find() ?: [];

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="reservations_' . $date . '.csv"');

        $output = fopen('php://output', 'w');

        if (!empty($reservations)) {
            fputcsv($output, array_keys($reservations[0]));
            foreach ($reservations as $row) {
                fputcsv($output, $row);
            }
        }
        fclose($output);
        exit();
    } else {
        throw new Exception('Invalid action');
    }

} catch (Exception $e) {
    if (isset($db)) {
        $db->rollBack();
    }
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
exit();
?>