<?php
/**
 * POST Controller - Admin Events Actions
 * Handles creating, updating, deleting events and managing venues
 */

session_start();
require_once __DIR__ . '/../../../Class/Database.php';

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

$config = require __DIR__ . '/../../../config/config.php';
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

    // CREATE NEW EVENT
    if ($action === 'create_event') {
        $event_name = trim($_POST['event_name'] ?? '');
        $event_type = $_POST['event_type'] ?? 'other';
        $venue_id = intval($_POST['venue_id'] ?? 0);
        $event_date = $_POST['event_date'] ?? '';
        $event_time = $_POST['event_time'] ?? '';
        $guests = intval($_POST['guests'] ?? 0);
        $status = $_POST['status'] ?? 'confirmed';
        $contact_person = trim($_POST['contact_person'] ?? '');
        $contact_phone = trim($_POST['contact_phone'] ?? '');
        $requirements = trim($_POST['requirements'] ?? '');

        // Validation
        if (empty($event_name)) {
            throw new Exception('Event name is required');
        }
        if (empty($event_date)) {
            throw new Exception('Event date is required');
        }
        if (empty($event_time)) {
            throw new Exception('Event time is required');
        }
        if ($guests < 1) {
            throw new Exception('Number of guests must be at least 1');
        }
        if (!$venue_id) {
            throw new Exception('Please select a venue');
        }

        $db->beginTransaction();

        // Check if venue is available on this date
        $conflictingEvents = $db->query(
            "SELECT COUNT(*) as count FROM events 
             WHERE venue_id = :venue_id 
                AND event_date = :date 
                AND status != 'cancelled'",
            [
                'venue_id' => $venue_id,
                'date' => $event_date
            ]
        )->fetch_one();

        if ($conflictingEvents['count'] > 0) {
            throw new Exception('This venue is already booked for the selected date');
        }

        // Insert event
        $db->query(
            "INSERT INTO events (
                event_name, event_type, venue_id, event_date, event_time,
                guests, status, contact_person, contact_phone, special_requirements,
                created_by, created_at, updated_at
             ) VALUES (
                :name, :type, :venue_id, :date, :time,
                :guests, :status, :contact_person, :contact_phone, :requirements,
                :created_by, NOW(), NOW()
             )",
            [
                'name' => $event_name,
                'type' => $event_type,
                'venue_id' => $venue_id,
                'date' => $event_date,
                'time' => $event_time,
                'guests' => $guests,
                'status' => $status,
                'contact_person' => $contact_person,
                'contact_phone' => $contact_phone,
                'requirements' => $requirements,
                'created_by' => $_SESSION['user_id']
            ]
        );

        $event_id = $db->lastInsertId();

        // Update venue status if needed
        if ($status === 'confirmed') {
            $db->query(
                "UPDATE venues SET status = 'occupied' WHERE id = :id",
                ['id' => $venue_id]
            );
        }

        // Create notification
        $db->query(
            "INSERT INTO notifications (user_id, title, message, type, icon, created_at) 
             VALUES (:user_id, 'New Event Created', :message, 'success', 'fa-calendar-plus', NOW())",
            [
                'user_id' => $_SESSION['user_id'],
                'message' => "Event '{$event_name}' created for {$event_date}"
            ]
        );

        $db->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Event created successfully',
            'event_id' => $event_id
        ]);
        exit();
    }

    // UPDATE EVENT
    elseif ($action === 'update_event') {
        $event_id = intval($_POST['event_id'] ?? 0);
        $event_name = trim($_POST['event_name'] ?? '');
        $event_type = $_POST['event_type'] ?? 'other';
        $venue_id = intval($_POST['venue_id'] ?? 0);
        $event_date = $_POST['event_date'] ?? '';
        $event_time = $_POST['event_time'] ?? '';
        $guests = intval($_POST['guests'] ?? 0);
        $status = $_POST['status'] ?? '';
        $contact_person = trim($_POST['contact_person'] ?? '');
        $contact_phone = trim($_POST['contact_phone'] ?? '');
        $requirements = trim($_POST['requirements'] ?? '');

        if (!$event_id) {
            throw new Exception('Event ID required');
        }

        // Get old event data
        $oldEvent = $db->query(
            "SELECT venue_id, status FROM events WHERE id = :id",
            ['id' => $event_id]
        )->fetch_one();

        $db->beginTransaction();

        // Update event
        $db->query(
            "UPDATE events SET 
                event_name = :name,
                event_type = :type,
                venue_id = :venue_id,
                event_date = :date,
                event_time = :time,
                guests = :guests,
                status = :status,
                contact_person = :contact_person,
                contact_phone = :contact_phone,
                special_requirements = :requirements,
                updated_at = NOW()
             WHERE id = :id",
            [
                'id' => $event_id,
                'name' => $event_name,
                'type' => $event_type,
                'venue_id' => $venue_id,
                'date' => $event_date,
                'time' => $event_time,
                'guests' => $guests,
                'status' => $status,
                'contact_person' => $contact_person,
                'contact_phone' => $contact_phone,
                'requirements' => $requirements
            ]
        );

        // Update venue statuses
        if ($oldEvent['venue_id'] != $venue_id) {
            // Old venue might become available
            $otherEvents = $db->query(
                "SELECT COUNT(*) as count FROM events 
                 WHERE venue_id = :venue_id 
                    AND status != 'cancelled'
                    AND event_date >= CURDATE()",
                ['venue_id' => $oldEvent['venue_id']]
            )->fetch_one();

            if ($otherEvents['count'] == 0) {
                $db->query(
                    "UPDATE venues SET status = 'available' WHERE id = :id",
                    ['id' => $oldEvent['venue_id']]
                );
            }

            // New venue becomes occupied if event is confirmed
            if ($status === 'confirmed') {
                $db->query(
                    "UPDATE venues SET status = 'occupied' WHERE id = :id",
                    ['id' => $venue_id]
                );
            }
        }

        $db->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Event updated successfully'
        ]);
        exit();
    }

    // DELETE EVENT
    elseif ($action === 'delete_event') {
        $event_id = intval($_POST['event_id'] ?? 0);

        if (!$event_id) {
            throw new Exception('Event ID required');
        }

        $db->beginTransaction();

        // Get event details
        $event = $db->query(
            "SELECT venue_id FROM events WHERE id = :id",
            ['id' => $event_id]
        )->fetch_one();

        // Delete event
        $db->query("DELETE FROM events WHERE id = :id", ['id' => $event_id]);

        // Check if venue has any other upcoming events
        if ($event) {
            $otherEvents = $db->query(
                "SELECT COUNT(*) as count FROM events 
                 WHERE venue_id = :venue_id 
                    AND event_date >= CURDATE()",
                ['venue_id' => $event['venue_id']]
            )->fetch_one();

            if ($otherEvents['count'] == 0) {
                $db->query(
                    "UPDATE venues SET status = 'available' WHERE id = :id",
                    ['id' => $event['venue_id']]
                );
            }
        }

        $db->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Event deleted successfully'
        ]);
        exit();
    }

    // GET VENUE AVAILABILITY
    elseif ($action === 'check_availability') {
        $date = $_POST['date'] ?? '';

        if (empty($date)) {
            throw new Exception('Date required');
        }

        // Get all venues with their booking status for the date
        $venues = $db->query(
            "SELECT 
                v.*,
                CASE 
                    WHEN e.id IS NOT NULL THEN 'booked'
                    ELSE 'available'
                END as booking_status,
                e.event_name,
                e.event_time
             FROM venues v
             LEFT JOIN events e ON v.id = e.venue_id 
                AND e.event_date = :date 
                AND e.status != 'cancelled'
             ORDER BY v.capacity DESC",
            ['date' => $date]
        )->find() ?: [];

        echo json_encode([
            'success' => true,
            'venues' => $venues,
            'date' => $date
        ]);
        exit();
    }

    // ADD VENUE
    elseif ($action === 'add_venue') {
        $venue_name = trim($_POST['venue_name'] ?? '');
        $capacity = intval($_POST['capacity'] ?? 0);
        $location = trim($_POST['location'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $amenities = trim($_POST['amenities'] ?? '');
        $price_per_hour = floatval($_POST['price_per_hour'] ?? 0);

        if (empty($venue_name) || $capacity < 1) {
            throw new Exception('Venue name and valid capacity required');
        }

        // Check if venue name already exists
        $existing = $db->query(
            "SELECT id FROM venues WHERE name = :name",
            ['name' => $venue_name]
        )->fetch_one();

        if ($existing) {
            throw new Exception('A venue with this name already exists');
        }

        $db->query(
            "INSERT INTO venues (name, capacity, location, description, amenities, price_per_hour, status)
             VALUES (:name, :capacity, :location, :description, :amenities, :price, 'available')",
            [
                'name' => $venue_name,
                'capacity' => $capacity,
                'location' => $location,
                'description' => $description,
                'amenities' => $amenities,
                'price' => $price_per_hour
            ]
        );

        echo json_encode([
            'success' => true,
            'message' => 'Venue added successfully'
        ]);
        exit();
    }

    // UPDATE VENUE STATUS
    elseif ($action === 'update_venue_status') {
        $venue_id = intval($_POST['venue_id'] ?? 0);
        $status = $_POST['status'] ?? '';

        if (!$venue_id) {
            throw new Exception('Venue ID required');
        }

        $db->query(
            "UPDATE venues SET status = :status WHERE id = :id",
            [
                'id' => $venue_id,
                'status' => $status
            ]
        );

        echo json_encode([
            'success' => true,
            'message' => 'Venue status updated'
        ]);
        exit();
    }

    // DELETE VENUE
    elseif ($action === 'delete_venue') {
        $venue_id = intval($_POST['venue_id'] ?? 0);

        if (!$venue_id) {
            throw new Exception('Venue ID required');
        }

        // Check if venue has upcoming events
        $events = $db->query(
            "SELECT COUNT(*) as count FROM events 
             WHERE venue_id = :venue_id 
                AND event_date >= CURDATE()
                AND status != 'cancelled'",
            ['venue_id' => $venue_id]
        )->fetch_one();

        if ($events['count'] > 0) {
            throw new Exception('Cannot delete venue with upcoming events');
        }

        $db->query("DELETE FROM venues WHERE id = :id", ['id' => $venue_id]);

        echo json_encode([
            'success' => true,
            'message' => 'Venue deleted successfully'
        ]);
        exit();
    }

    // GET ALL VENUES
    elseif ($action === 'get_venues') {
        $venues = $db->query(
            "SELECT * FROM venues ORDER BY name ASC",
            []
        )->find() ?: [];

        echo json_encode([
            'success' => true,
            'venues' => $venues
        ]);
        exit();
    }

    // EXPORT EVENTS
    elseif ($action === 'export_events') {
        $format = $_POST['format'] ?? 'json';

        $events = $db->query(
            "SELECT 
                e.*,
                v.name as venue_name
             FROM events e
             LEFT JOIN venues v ON e.venue_id = v.id
             ORDER BY e.event_date DESC",
            []
        )->find() ?: [];

        if ($format === 'json') {
            header('Content-Type: application/json');
            header('Content-Disposition: attachment; filename="events_export_' . date('Y-m-d') . '.json"');
            echo json_encode($events, JSON_PRETTY_PRINT);
            exit();
        } elseif ($format === 'csv') {
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="events_export_' . date('Y-m-d') . '.csv"');

            $output = fopen('php://output', 'w');
            if (!empty($events)) {
                fputcsv($output, array_keys($events[0]));
                foreach ($events as $event) {
                    fputcsv($output, $event);
                }
            }
            fclose($output);
            exit();
        }
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