-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3307
-- Generation Time: Mar 17, 2026 at 04:26 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `hotelrestaurant`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(10) UNSIGNED NOT NULL,
  `booking_reference` varchar(20) NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `guest_first_name` varchar(50) NOT NULL,
  `guest_last_name` varchar(50) NOT NULL,
  `guest_email` varchar(100) NOT NULL,
  `guest_phone` varchar(20) NOT NULL,
  `booking_type` enum('hotel','restaurant') NOT NULL DEFAULT 'hotel',
  `check_in` date NOT NULL,
  `check_in_time` time DEFAULT NULL,
  `check_out` date NOT NULL,
  `check_out_time` time DEFAULT NULL,
  `nights` int(11) NOT NULL,
  `room_id` varchar(10) DEFAULT NULL,
  `room_assigned` varchar(10) DEFAULT NULL,
  `room_name` varchar(100) DEFAULT NULL,
  `room_price` decimal(10,2) NOT NULL,
  `adults` int(11) DEFAULT 2,
  `children` int(11) DEFAULT 0,
  `subtotal` decimal(10,2) NOT NULL,
  `tax` decimal(10,2) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('pending','confirmed','cancelled','completed') NOT NULL DEFAULT 'pending',
  `payment_status` enum('unpaid','paid','refunded') NOT NULL DEFAULT 'unpaid',
  `payment_method` varchar(50) DEFAULT NULL,
  `payment_date` datetime DEFAULT NULL,
  `special_requests` text DEFAULT NULL,
  `payment_id` int(10) UNSIGNED DEFAULT NULL,
  `points_earned` int(11) DEFAULT 0,
  `points_awarded` tinyint(1) DEFAULT 0,
  `points_awarded_at` datetime DEFAULT NULL,
  `points_used` int(11) DEFAULT 0,
  `points_discount` decimal(10,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `booking_reference`, `user_id`, `guest_first_name`, `guest_last_name`, `guest_email`, `guest_phone`, `booking_type`, `check_in`, `check_in_time`, `check_out`, `check_out_time`, `nights`, `room_id`, `room_assigned`, `room_name`, `room_price`, `adults`, `children`, `subtotal`, `tax`, `total_amount`, `status`, `payment_status`, `payment_method`, `payment_date`, `special_requests`, `payment_id`, `points_earned`, `points_awarded`, `points_awarded_at`, `points_used`, `points_discount`, `created_at`, `updated_at`) VALUES
(63, 'HOT-20260317-C7910C', 7, 'Janzel', 'Dolo', 'janzeldols@gmail.com', '+639565819964', 'hotel', '2026-03-18', NULL, '2026-03-20', NULL, 2, '204', NULL, 'Family Room', 5500.00, 2, 0, 11000.00, 1320.00, 12320.00, 'confirmed', 'unpaid', NULL, NULL, '', NULL, 615, 1, '2026-03-17 20:17:35', 0, 0.00, '2026-03-17 12:10:20', '2026-03-17 12:17:35'),
(64, 'HOT-20260317-0370E5', 7, 'Janzel', 'Dolo', 'janzeldols@gmail.com', '+639565819964', 'hotel', '2026-03-17', '23:21:16', '2026-03-20', NULL, 3, '201', 'Deluxe Twi', 'Deluxe Twin', 4200.00, 2, 0, 12600.00, 1512.00, 14112.00, '', 'unpaid', NULL, NULL, '', NULL, 705, 1, '2026-03-17 22:44:10', 0, 0.00, '2026-03-17 14:42:40', '2026-03-17 15:21:16');

--
-- Triggers `bookings`
--
DELIMITER $$
CREATE TRIGGER `update_balance_on_booking_insert` AFTER INSERT ON `bookings` FOR EACH ROW BEGIN
    -- Only add to balance if payment is unpaid and status is not cancelled
    IF NEW.payment_status = 'unpaid' AND NEW.status != 'cancelled' THEN
        INSERT INTO current_balance (user_id, total_balance, pending_balance, available_balance, last_updated)
        VALUES (NEW.user_id, NEW.total_amount, 0, NEW.total_amount, NOW())
        ON DUPLICATE KEY UPDATE
            total_balance = total_balance + NEW.total_amount,
            available_balance = available_balance + NEW.total_amount,
            last_updated = NOW();
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `update_balance_on_booking_update` AFTER UPDATE ON `bookings` FOR EACH ROW BEGIN
    -- Handle status or payment status changes
    IF OLD.payment_status != NEW.payment_status OR OLD.status != NEW.status THEN
        -- Remove old amount if it was unpaid and not cancelled
        IF OLD.payment_status = 'unpaid' AND OLD.status != 'cancelled' THEN
            UPDATE current_balance 
            SET total_balance = GREATEST(0, total_balance - OLD.total_amount),
                available_balance = GREATEST(0, available_balance - OLD.total_amount),
                last_updated = NOW()
            WHERE user_id = OLD.user_id;
        END IF;
        
        -- Add new amount if it's now unpaid and not cancelled
        IF NEW.payment_status = 'unpaid' AND NEW.status != 'cancelled' THEN
            INSERT INTO current_balance (user_id, total_balance, pending_balance, available_balance, last_updated)
            VALUES (NEW.user_id, NEW.total_amount, 0, NEW.total_amount, NOW())
            ON DUPLICATE KEY UPDATE
                total_balance = total_balance + NEW.total_amount,
                available_balance = available_balance + NEW.total_amount,
                last_updated = NOW();
        END IF;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `current_balance`
--

CREATE TABLE `current_balance` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `total_balance` decimal(10,2) NOT NULL DEFAULT 0.00,
  `pending_balance` decimal(10,2) NOT NULL DEFAULT 0.00,
  `available_balance` decimal(10,2) NOT NULL DEFAULT 0.00,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `current_balance`
--

INSERT INTO `current_balance` (`id`, `user_id`, `total_balance`, `pending_balance`, `available_balance`, `last_updated`) VALUES
(89, 7, 14112.00, 0.00, 14112.00, '2026-03-17 15:21:16');

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` int(10) UNSIGNED NOT NULL,
  `event_name` varchar(100) NOT NULL,
  `event_type` enum('wedding','meeting','conference','birthday','social','corporate','other') DEFAULT 'other',
  `event_date` date NOT NULL,
  `event_time` time DEFAULT NULL,
  `guests` int(11) DEFAULT 0,
  `status` enum('confirmed','pending','cancelled','completed') NOT NULL DEFAULT 'confirmed',
  `contact_person` varchar(100) DEFAULT NULL,
  `contact_phone` varchar(20) DEFAULT NULL,
  `special_requirements` text DEFAULT NULL,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `venue_id` int(10) UNSIGNED DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `event_name`, `event_type`, `event_date`, `event_time`, `guests`, `status`, `contact_person`, `contact_phone`, `special_requirements`, `created_by`, `location`, `venue_id`, `description`, `created_at`, `updated_at`) VALUES
(1, 'Wedding Rehearsal', 'other', '2026-03-17', '15:00:00', 0, 'confirmed', NULL, NULL, NULL, NULL, 'Grand Ballroom', NULL, 'Wedding rehearsal dinner', '2026-03-16 16:35:46', '2026-03-17 14:56:18'),
(2, 'Corporate Meeting', 'other', '2026-03-17', '10:00:00', 0, 'confirmed', NULL, NULL, NULL, NULL, 'Boardroom A', NULL, 'Executive board meeting', '2026-03-16 16:35:46', '2026-03-17 14:56:18'),
(3, 'test', 'meeting', '2026-03-17', '14:03:00', 150, 'confirmed', 'd', '09565819961', 's', 7, NULL, 1, NULL, '2026-03-17 15:05:35', '2026-03-17 15:05:35');

-- --------------------------------------------------------

--
-- Table structure for table `food_orders`
--

CREATE TABLE `food_orders` (
  `id` int(10) UNSIGNED NOT NULL,
  `order_reference` varchar(50) NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `items` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`items`)),
  `order_type` varchar(50) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `service_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(10,2) NOT NULL,
  `points_used` int(11) DEFAULT 0,
  `points_earned` int(11) DEFAULT 0,
  `status` enum('pending','preparing','ready','completed','cancelled') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `guest_interactions`
--

CREATE TABLE `guest_interactions` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `admin_id` int(10) UNSIGNED NOT NULL,
  `type` enum('email','sms','both','call','note') NOT NULL,
  `status` enum('pending','in-progress','done') NOT NULL DEFAULT 'pending',
  `priority` enum('low','medium','high') DEFAULT 'medium',
  `assigned_to` int(10) UNSIGNED DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `response` text DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `guest_interactions`
--

INSERT INTO `guest_interactions` (`id`, `user_id`, `admin_id`, `type`, `status`, `priority`, `assigned_to`, `subject`, `message`, `response`, `completed_at`, `created_at`, `updated_at`) VALUES
(3, 7, 7, 'note', 'done', 'medium', NULL, 'Guest Check-in', 'Guest checked in to room Deluxe Twin. ID: Passport - 12', NULL, NULL, '2026-03-17 15:21:16', '2026-03-17 15:21:16');

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `id` int(10) UNSIGNED NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `category` varchar(50) DEFAULT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `reorder_level` int(11) NOT NULL DEFAULT 10,
  `unit` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory`
--

INSERT INTO `inventory` (`id`, `item_name`, `category`, `stock`, `reorder_level`, `unit`, `created_at`, `updated_at`) VALUES
(1, 'Rice', 'Food', 27, 20, 'kg', '2026-03-16 16:35:46', '2026-03-17 14:09:02'),
(2, 'Pork', 'Meat', 15, 10, 'kg', '2026-03-16 16:35:46', '2026-03-16 16:35:46'),
(3, 'Beef', 'Meat', 8, 10, 'kg', '2026-03-16 16:35:46', '2026-03-16 16:35:46'),
(4, 'Chicken', 'Meat', 12, 10, 'kg', '2026-03-16 16:35:46', '2026-03-16 16:35:46'),
(5, 'Cooking Oil', 'Supply', 5, 5, 'bottles', '2026-03-16 16:35:46', '2026-03-16 16:35:46');

-- --------------------------------------------------------

--
-- Table structure for table `menu_items`
--

CREATE TABLE `menu_items` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `category` enum('appetizers','mains','desserts','beverages') NOT NULL,
  `is_available` tinyint(1) NOT NULL DEFAULT 1,
  `image_url` varchar(255) DEFAULT NULL,
  `preparation_time` int(11) DEFAULT 15,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `menu_items`
--

INSERT INTO `menu_items` (`id`, `name`, `description`, `price`, `category`, `is_available`, `image_url`, `preparation_time`, `created_at`, `updated_at`) VALUES
(1, 'Sinigang na Baboy', 'tamarind soup, pork, veggies', 320.00, 'mains', 1, NULL, 20, '2026-03-16 14:11:26', '2026-03-16 14:11:26'),
(2, 'Sizzling Sisig', 'chopped pork, onion, egg', 290.00, 'mains', 1, NULL, 15, '2026-03-16 14:11:26', '2026-03-16 14:11:26'),
(3, 'Crispy Pata', 'deep-fried pork knuckle', 550.00, 'mains', 1, NULL, 25, '2026-03-16 14:11:26', '2026-03-16 14:11:26'),
(4, 'Halo-Halo', 'shaved ice, fruits, leche flan', 150.00, 'desserts', 1, NULL, 10, '2026-03-16 14:11:26', '2026-03-16 14:11:26'),
(5, 'Fresh Buko Juice', 'with coconut pulp', 90.00, 'beverages', 1, NULL, 5, '2026-03-16 14:11:26', '2026-03-16 14:11:26'),
(6, 'Garlic Rice', 'sinangag, plain', 50.00, 'mains', 1, NULL, 5, '2026-03-16 14:11:26', '2026-03-16 14:11:26');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `title` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `type` enum('info','success','warning','promo','loyalty') NOT NULL DEFAULT 'info',
  `icon` varchar(50) DEFAULT NULL,
  `link` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `read_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `type`, `icon`, `link`, `is_read`, `created_at`, `read_at`) VALUES
(161, 7, 'Check-in Completed', 'Guest Janzel Dolo checked in to room Deluxe Twin', 'success', 'fa-calendar-check', NULL, 0, '2026-03-17 15:21:16', NULL),
(162, 7, 'Reminder from Front Desk', 'congrats', 'info', 'fa-bell', '/src/customer_portal/my_reservation.php', 0, '2026-03-17 15:21:28', NULL),
(163, 7, 'New Event Created', 'Event \'test\' created for 2026-03-19', 'success', 'fa-calendar-plus', NULL, 0, '2026-03-17 15:23:03', NULL);

-- --------------------------------------------------------

--
-- Stand-in structure for view `notification_unread_counts`
-- (See below for the actual view)
--
CREATE TABLE `notification_unread_counts` (
`user_id` int(10) unsigned
,`unread_count` bigint(21)
);

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(10) UNSIGNED NOT NULL,
  `payment_reference` varchar(50) NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `booking_type` enum('hotel','restaurant') NOT NULL,
  `booking_id` int(10) UNSIGNED NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `payment_status` enum('pending','completed','failed','refunded') NOT NULL DEFAULT 'pending',
  `approval_status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `approved_by` int(10) UNSIGNED DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `rejection_reason` varchar(255) DEFAULT NULL,
  `transaction_id` varchar(100) DEFAULT NULL,
  `payment_date` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `payment_reference`, `user_id`, `booking_type`, `booking_id`, `amount`, `payment_method`, `payment_status`, `approval_status`, `approved_by`, `approved_at`, `rejection_reason`, `transaction_id`, `payment_date`, `created_at`) VALUES
(24, 'PAY-202603-86F56B77', 7, '', 0, 26432.00, 'GCash', 'completed', 'pending', NULL, NULL, NULL, NULL, '2026-03-17 22:42:55', '2026-03-17 14:42:55');

--
-- Triggers `payments`
--
DELIMITER $$
CREATE TRIGGER `update_balance_on_payment_insert` AFTER INSERT ON `payments` FOR EACH ROW BEGIN
    IF NEW.payment_status = 'pending' THEN
        -- Add to pending balance
        UPDATE current_balance 
        SET pending_balance = pending_balance + NEW.amount,
            available_balance = available_balance - NEW.amount
        WHERE user_id = NEW.user_id;
    ELSEIF NEW.payment_status = 'completed' THEN
        -- Remove from total balance (payment completed)
        UPDATE current_balance 
        SET total_balance = total_balance - NEW.amount,
            available_balance = available_balance - NEW.amount
        WHERE user_id = NEW.user_id;
        
        -- Update the original booking/reservation payment status
        IF NEW.booking_type = 'hotel' THEN
            UPDATE bookings SET payment_status = 'paid', payment_date = NOW() 
            WHERE id = NEW.booking_id;
        ELSEIF NEW.booking_type = 'restaurant' THEN
            UPDATE restaurant_reservations SET payment_status = 'paid', payment_date = NOW() 
            WHERE id = NEW.booking_id;
        END IF;
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `update_balance_on_payment_update` AFTER UPDATE ON `payments` FOR EACH ROW BEGIN
    -- Handle status changes
    IF OLD.payment_status != NEW.payment_status THEN
        -- Remove old status effects
        IF OLD.payment_status = 'pending' THEN
            UPDATE current_balance 
            SET pending_balance = pending_balance - OLD.amount,
                available_balance = available_balance + OLD.amount
            WHERE user_id = OLD.user_id;
        ELSEIF OLD.payment_status = 'completed' THEN
            -- This shouldn't normally happen, but handle it
            UPDATE current_balance 
            SET total_balance = total_balance + OLD.amount,
                available_balance = available_balance + OLD.amount
            WHERE user_id = OLD.user_id;
        END IF;
        
        -- Add new status effects
        IF NEW.payment_status = 'pending' THEN
            UPDATE current_balance 
            SET pending_balance = pending_balance + NEW.amount,
                available_balance = available_balance - NEW.amount
            WHERE user_id = NEW.user_id;
        ELSEIF NEW.payment_status = 'completed' THEN
            UPDATE current_balance 
            SET total_balance = total_balance - NEW.amount,
                available_balance = available_balance - NEW.amount
            WHERE user_id = NEW.user_id;
            
            -- Update the original booking/reservation payment status
            IF NEW.booking_type = 'hotel' THEN
                UPDATE bookings SET payment_status = 'paid', payment_date = NOW() 
                WHERE id = NEW.booking_id;
            ELSEIF NEW.booking_type = 'restaurant' THEN
                UPDATE restaurant_reservations SET payment_status = 'paid', payment_date = NOW() 
                WHERE id = NEW.booking_id;
            END IF;
        END IF;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `payment_methods`
--

CREATE TABLE `payment_methods` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `method_type` enum('gcash','visa','mastercard','cash') NOT NULL,
  `display_name` varchar(50) NOT NULL,
  `account_name` varchar(100) NOT NULL,
  `account_number` varchar(50) DEFAULT NULL,
  `expiry_date` varchar(10) DEFAULT NULL,
  `is_default` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment_methods`
--

INSERT INTO `payment_methods` (`id`, `user_id`, `method_type`, `display_name`, `account_name`, `account_number`, `expiry_date`, `is_default`, `created_at`) VALUES
(2, 4, 'gcash', 'GCash', 'janzeldols', '09565819961', '2026-04', 1, '2026-03-15 15:45:36'),
(3, 7, 'gcash', 'GCash', 'sdasd', '123', '2026-04', 1, '2026-03-17 04:20:05');

-- --------------------------------------------------------

--
-- Table structure for table `redemptions`
--

CREATE TABLE `redemptions` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `reward_name` varchar(255) NOT NULL,
  `points_cost` int(11) NOT NULL,
  `experience` varchar(255) DEFAULT NULL,
  `status` enum('pending','completed','cancelled') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `redemptions`
--

INSERT INTO `redemptions` (`id`, `user_id`, `reward_name`, `points_cost`, `experience`, `status`, `created_at`) VALUES
(20, 7, 'Free Coffee / Tea', 240, 'beverage', 'pending', '2026-03-17 08:03:18'),
(21, 7, 'Complimentary Breakfast', 480, 'dining', 'pending', '2026-03-17 10:56:34');

-- --------------------------------------------------------

--
-- Table structure for table `response_templates`
--

CREATE TABLE `response_templates` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `template_text` text NOT NULL,
  `category` enum('positive','negative','neutral') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `response_templates`
--

INSERT INTO `response_templates` (`id`, `name`, `template_text`, `category`, `created_at`) VALUES
(1, 'Thank you for 5 stars! ⭐', 'Thank you so much for your wonderful review! We\'re thrilled to hear you enjoyed your experience and look forward to welcoming you again soon.', 'positive', '2026-03-16 16:56:01'),
(2, 'Apology for inconvenience', 'We sincerely apologize for the inconvenience you experienced. This is not the standard we strive for. Please contact us directly so we can make things right.', 'negative', '2026-03-16 16:56:01'),
(3, 'Thank you for feedback', 'Thank you for taking the time to share your feedback. We appreciate your input and will use it to improve our services.', 'neutral', '2026-03-16 16:56:01');

-- --------------------------------------------------------

--
-- Table structure for table `restaurant_reservations`
--

CREATE TABLE `restaurant_reservations` (
  `id` int(10) UNSIGNED NOT NULL,
  `reservation_reference` varchar(20) NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `guest_first_name` varchar(50) NOT NULL,
  `guest_last_name` varchar(50) NOT NULL,
  `guest_email` varchar(100) NOT NULL,
  `guest_phone` varchar(20) NOT NULL,
  `reservation_date` date NOT NULL,
  `reservation_time` time NOT NULL,
  `guests` int(11) NOT NULL,
  `table_number` varchar(10) DEFAULT NULL,
  `special_requests` text DEFAULT NULL,
  `occasion` varchar(50) DEFAULT NULL,
  `down_payment` decimal(10,2) DEFAULT 0.00,
  `points_earned` int(11) DEFAULT 0,
  `points_awarded` tinyint(1) DEFAULT 0,
  `points_awarded_at` datetime DEFAULT NULL,
  `status` enum('pending','confirmed','cancelled','completed') NOT NULL DEFAULT 'pending',
  `payment_status` enum('unpaid','paid','refunded') NOT NULL DEFAULT 'unpaid',
  `payment_method` varchar(50) DEFAULT NULL,
  `payment_date` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Triggers `restaurant_reservations`
--
DELIMITER $$
CREATE TRIGGER `update_balance_on_reservation_insert` AFTER INSERT ON `restaurant_reservations` FOR EACH ROW BEGIN
    -- Only add to balance if payment is unpaid, status not cancelled, and down payment > 0
    IF NEW.payment_status = 'unpaid' AND NEW.status != 'cancelled' AND NEW.down_payment > 0 THEN
        INSERT INTO current_balance (user_id, total_balance, pending_balance, available_balance, last_updated)
        VALUES (NEW.user_id, NEW.down_payment, 0, NEW.down_payment, NOW())
        ON DUPLICATE KEY UPDATE
            total_balance = total_balance + NEW.down_payment,
            available_balance = available_balance + NEW.down_payment,
            last_updated = NOW();
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `update_balance_on_reservation_update` AFTER UPDATE ON `restaurant_reservations` FOR EACH ROW BEGIN
    -- Handle status or payment status changes
    IF OLD.payment_status != NEW.payment_status OR OLD.status != NEW.status THEN
        -- Remove old down payment if it was unpaid and not cancelled
        IF OLD.payment_status = 'unpaid' AND OLD.status != 'cancelled' AND OLD.down_payment > 0 THEN
            UPDATE current_balance 
            SET total_balance = GREATEST(0, total_balance - OLD.down_payment),
                available_balance = GREATEST(0, available_balance - OLD.down_payment),
                last_updated = NOW()
            WHERE user_id = OLD.user_id;
        END IF;
        
        -- Add new down payment if it's now unpaid and not cancelled
        IF NEW.payment_status = 'unpaid' AND NEW.status != 'cancelled' AND NEW.down_payment > 0 THEN
            INSERT INTO current_balance (user_id, total_balance, pending_balance, available_balance, last_updated)
            VALUES (NEW.user_id, NEW.down_payment, 0, NEW.down_payment, NOW())
            ON DUPLICATE KEY UPDATE
                total_balance = total_balance + NEW.down_payment,
                available_balance = available_balance + NEW.down_payment,
                last_updated = NOW();
        END IF;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `restaurant_tables`
--

CREATE TABLE `restaurant_tables` (
  `id` int(10) UNSIGNED NOT NULL,
  `table_number` varchar(10) NOT NULL,
  `capacity` int(11) NOT NULL,
  `location` varchar(50) DEFAULT NULL,
  `status` enum('available','reserved','occupied') NOT NULL DEFAULT 'available',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `restaurant_tables`
--

INSERT INTO `restaurant_tables` (`id`, `table_number`, `capacity`, `location`, `status`, `created_at`, `updated_at`) VALUES
(1, 'T1', 2, 'Window', 'occupied', '2026-03-16 10:28:37', '2026-03-16 10:39:53'),
(2, 'T2', 4, 'Center', 'occupied', '2026-03-16 10:28:37', '2026-03-16 10:39:57'),
(3, 'T3', 2, 'Window', 'occupied', '2026-03-16 10:28:37', '2026-03-16 10:40:02'),
(4, 'T4', 6, 'Private', 'occupied', '2026-03-16 10:28:37', '2026-03-16 10:40:05'),
(5, 'T5', 2, 'Bar', 'reserved', '2026-03-16 10:28:37', '2026-03-16 10:40:09'),
(6, 'T6', 4, 'Center', 'occupied', '2026-03-16 10:28:37', '2026-03-16 10:40:12'),
(7, 'T7', 2, 'Window', 'reserved', '2026-03-16 10:28:37', '2026-03-16 10:40:17'),
(8, 'T8', 8, 'Private', 'occupied', '2026-03-16 10:28:37', '2026-03-16 10:42:01');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `experience` varchar(255) NOT NULL,
  `rating` tinyint(3) UNSIGNED NOT NULL CHECK (`rating` between 1 and 5),
  `review_text` text NOT NULL,
  `detail` varchar(255) DEFAULT NULL,
  `icon` varchar(50) DEFAULT 'fa-pen',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `review_responses`
--

CREATE TABLE `review_responses` (
  `id` int(10) UNSIGNED NOT NULL,
  `review_id` int(10) UNSIGNED NOT NULL,
  `response_text` text NOT NULL,
  `responded_by` int(10) UNSIGNED NOT NULL,
  `responded_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rewards`
--

CREATE TABLE `rewards` (
  `id` int(10) UNSIGNED NOT NULL,
  `reward_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `points_cost` int(11) NOT NULL,
  `category` enum('beverage','dining','hotel','spa','other') NOT NULL DEFAULT 'other',
  `image_url` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `stock_limit` int(11) DEFAULT NULL,
  `times_redeemed` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rewards`
--

INSERT INTO `rewards` (`id`, `reward_name`, `description`, `points_cost`, `category`, `image_url`, `is_active`, `stock_limit`, `times_redeemed`, `created_at`, `updated_at`) VALUES
(1, 'Free Coffee / Tea', 'any hot beverage at Azure Lounge', 240, 'beverage', NULL, 1, NULL, 7, '2026-03-16 17:30:12', '2026-03-17 08:03:18'),
(2, 'Complimentary Breakfast', 'for one person at Azure Restaurant', 480, 'dining', NULL, 1, NULL, 2, '2026-03-16 17:30:12', '2026-03-17 10:56:34'),
(3, 'Late Check-out (2pm)', 'subject to availability', 600, 'hotel', NULL, 1, NULL, 0, '2026-03-16 17:30:12', '2026-03-16 17:30:12'),
(4, 'Room Upgrade', 'deluxe to suite (subject to availability)', 1200, 'hotel', NULL, 1, NULL, 1, '2026-03-16 17:30:12', '2026-03-16 18:24:35'),
(5, 'Free Coffee / Tea', 'any hot beverage at Azure Lounge', 240, 'beverage', NULL, 1, 100, 0, '2026-03-16 18:23:40', '2026-03-16 18:23:40'),
(6, 'Complimentary Breakfast', 'for one person at Azure Restaurant', 480, 'dining', NULL, 1, 50, 0, '2026-03-16 18:23:40', '2026-03-16 18:23:40'),
(11, 'xx', 'd', 6600, 'dining', NULL, 1, NULL, 0, '2026-03-16 18:32:08', '2026-03-16 18:32:08');

-- --------------------------------------------------------

--
-- Table structure for table `rooms`
--

CREATE TABLE `rooms` (
  `id` varchar(10) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `beds` varchar(50) DEFAULT NULL,
  `view` varchar(50) DEFAULT NULL,
  `amenities` text DEFAULT NULL,
  `max_occupancy` int(11) DEFAULT 2,
  `image_url` varchar(255) DEFAULT NULL,
  `is_available` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rooms`
--

INSERT INTO `rooms` (`id`, `name`, `description`, `price`, `beds`, `view`, `amenities`, `max_occupancy`, `image_url`, `is_available`, `created_at`) VALUES
('101', 'Superior Double', NULL, 3500.00, '1 double bed', 'city view', 'Free WiFi, TV, Aircon', 2, NULL, 1, '2026-03-17 12:34:03'),
('102', 'Superior Double', NULL, 3500.00, '1 double bed', 'city view', 'Free WiFi, TV, Aircon', 2, NULL, 1, '2026-03-17 12:34:03'),
('201', 'Deluxe Twin', NULL, 4200.00, '2 single beds', 'city view', 'Free WiFi, TV, Aircon', 2, NULL, 0, '2026-03-15 07:19:38'),
('202', 'Ocean Suite', NULL, 6900.00, '1 king bed', 'ocean view', 'Jacuzzi, Free WiFi, Mini Bar', 3, NULL, 1, '2026-03-15 07:19:38'),
('203', 'Superior Double', NULL, 3500.00, 'double bed', 'city view', 'Free WiFi, TV', 2, NULL, 1, '2026-03-15 07:19:38'),
('204', 'Family Room', NULL, 5500.00, '2 queen beds', 'pool view', 'Free WiFi, TV, Mini Fridge', 4, NULL, 0, '2026-03-15 07:19:38'),
('205', 'Executive Suite', NULL, 8500.00, '1 king bed', 'ocean view', 'Jacuzzi, Living Area, Free WiFi', 2, NULL, 1, '2026-03-15 07:19:38'),
('301', 'Ocean Suite', NULL, 6900.00, '1 king bed', 'ocean view', 'Jacuzzi, Free WiFi, Mini Bar', 3, NULL, 1, '2026-03-17 12:34:03'),
('302', 'Ocean Suite', NULL, 6900.00, '1 king bed', 'ocean view', 'Jacuzzi, Free WiFi, Mini Bar', 3, NULL, 1, '2026-03-17 12:34:03'),
('401', 'Family Room', NULL, 5500.00, '2 queen beds', 'pool view', 'Free WiFi, TV, Mini Fridge', 4, NULL, 1, '2026-03-17 12:34:03'),
('402', 'Family Room', NULL, 5500.00, '2 queen beds', 'pool view', 'Free WiFi, TV, Mini Fridge', 4, NULL, 1, '2026-03-17 12:34:03');

-- --------------------------------------------------------

--
-- Table structure for table `room_maintenance`
--

CREATE TABLE `room_maintenance` (
  `id` int(10) UNSIGNED NOT NULL,
  `room_id` varchar(10) NOT NULL,
  `condition_status` enum('good','minor','maintenance','damage') NOT NULL DEFAULT 'good',
  `priority` enum('low','medium','high') DEFAULT 'medium',
  `reported_at` datetime NOT NULL,
  `reported_by` int(10) UNSIGNED NOT NULL,
  `assigned_to` int(10) UNSIGNED DEFAULT NULL,
  `cleaned_at` datetime DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `room_maintenance`
--

INSERT INTO `room_maintenance` (`id`, `room_id`, `condition_status`, `priority`, `reported_at`, `reported_by`, `assigned_to`, `cleaned_at`, `completed_at`, `notes`, `created_at`, `updated_at`) VALUES
(1, '204', 'maintenance', 'medium', '2026-03-17 21:50:10', 7, NULL, NULL, NULL, 'hi (Scheduled: 2026-03-18)', '2026-03-17 13:50:10', '2026-03-17 13:50:10');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(10) UNSIGNED NOT NULL,
  `setting_key` varchar(50) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `setting_key`, `setting_value`, `created_at`, `updated_at`) VALUES
(1, 'tier_bronze', '0', '2026-03-16 17:36:20', '2026-03-16 17:36:20'),
(2, 'tier_silver', '500', '2026-03-16 17:36:20', '2026-03-16 17:36:20'),
(3, 'tier_gold', '1000', '2026-03-16 17:36:20', '2026-03-16 17:36:20'),
(4, 'tier_platinum', '2000', '2026-03-16 17:36:20', '2026-03-16 17:36:20');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `alternative_phone` varchar(20) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('male','female','prefer not to say') DEFAULT NULL,
  `nationality` varchar(50) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(50) DEFAULT NULL,
  `postal_code` varchar(20) DEFAULT NULL,
  `country` varchar(50) DEFAULT 'Philippines',
  `preferred_language` varchar(30) DEFAULT 'English',
  `loyalty_points` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `preferences` text DEFAULT NULL,
  `allergies` text DEFAULT NULL,
  `birthday` date DEFAULT NULL,
  `anniversary` date DEFAULT NULL,
  `role` enum('customer','admin','staff') NOT NULL DEFAULT 'customer',
  `status` enum('active','inactive','suspended') NOT NULL DEFAULT 'active',
  `email_verified` tinyint(1) NOT NULL DEFAULT 0,
  `email_verification_token` varchar(64) DEFAULT NULL,
  `email_verification_expires` datetime DEFAULT NULL,
  `phone_verified` tinyint(1) DEFAULT NULL,
  `notify_email` tinyint(1) DEFAULT NULL,
  `notify_sms` tinyint(1) DEFAULT NULL,
  `notify_promo` tinyint(1) DEFAULT NULL,
  `notify_loyalty` tinyint(1) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `member_tier` enum('bronze','silver','gold','platinum') DEFAULT 'bronze',
  `join_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(64) DEFAULT NULL,
  `token_expires` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `last_login` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `first_name`, `last_name`, `email`, `phone`, `alternative_phone`, `date_of_birth`, `gender`, `nationality`, `address`, `city`, `postal_code`, `country`, `preferred_language`, `loyalty_points`, `preferences`, `allergies`, `birthday`, `anniversary`, `role`, `status`, `email_verified`, `email_verification_token`, `email_verification_expires`, `phone_verified`, `notify_email`, `notify_sms`, `notify_promo`, `notify_loyalty`, `avatar`, `member_tier`, `join_date`, `password`, `remember_token`, `token_expires`, `created_at`, `updated_at`, `last_login`) VALUES
(4, 'Dolo dols', 'Dolo', 'dols', 'janzeldol1s@gmail.com', '+639565819961', '+639565819961', '2026-03-16', 'prefer not to say', 'ako ay', 'Sampaloc', 'caloocan city', 'NONE', 'Philippines', 'English', 285, '', '', '2026-03-18', '0000-00-00', 'customer', 'active', 0, NULL, NULL, 1, 1, 1, 1, 1, NULL, 'platinum', '2026-03-15 08:48:13', '$2y$12$LSPIJZd7kcJxavwyEteiEehuiwbeIZKh1oM1DRXKF2zIuvh5Fsxma', '5cb23af2f6febd413ce82c4e766863f3197872ae273e0c1864b766ef15ae12d7', '2026-04-14 09:58:38', '2026-03-15 08:48:13', '2026-03-17 03:09:18', NULL),
(7, 'Janzel', NULL, NULL, 'janzeldols@gmail.com', '+639565819964', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Philippines', 'English', 1320, NULL, NULL, NULL, NULL, 'admin', 'active', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'bronze', '2026-03-17 02:41:59', '$2y$12$rAvNuAJvNvuKu.h.NhWoJutf4N9ZQ5z9mWFuHQazyx0gROkH9Wr2y', NULL, NULL, '2026-03-17 02:41:59', '2026-03-17 14:44:10', NULL);

-- --------------------------------------------------------

--
-- Stand-in structure for view `user_balance_summary`
-- (See below for the actual view)
--
CREATE TABLE `user_balance_summary` (
`user_id` int(10) unsigned
,`full_name` varchar(100)
,`email` varchar(100)
,`loyalty_points` int(10) unsigned
,`member_tier` enum('bronze','silver','gold','platinum')
,`total_balance` decimal(10,2)
,`pending_balance` decimal(10,2)
,`available_balance` decimal(10,2)
,`last_updated` timestamp
);

-- --------------------------------------------------------

--
-- Table structure for table `venues`
--

CREATE TABLE `venues` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `capacity` int(11) NOT NULL DEFAULT 0,
  `location` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `amenities` text DEFAULT NULL,
  `status` enum('available','occupied','maintenance','setup') NOT NULL DEFAULT 'available',
  `price_per_hour` decimal(10,2) DEFAULT 0.00,
  `image_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `venues`
--

INSERT INTO `venues` (`id`, `name`, `capacity`, `location`, `description`, `amenities`, `status`, `price_per_hour`, `image_url`, `created_at`, `updated_at`) VALUES
(1, 'Grand Ballroom', 300, 'Ground Floor', 'Perfect for weddings and large conferences', 'Stage, Sound System, Projector, Catering Kitchen, Dance Floor', 'occupied', 15000.00, NULL, '2026-03-17 14:55:50', '2026-03-17 15:05:35'),
(2, 'Boardroom A', 30, '2nd Floor', 'Executive meeting room', 'Whiteboard, TV Screen, Conference Phone, Wi-Fi', 'available', 3000.00, NULL, '2026-03-17 14:55:50', '2026-03-17 14:55:50'),
(3, 'Boardroom B', 20, '2nd Floor', 'Small meeting room', 'Whiteboard, TV Screen, Wi-Fi', 'available', 2000.00, NULL, '2026-03-17 14:55:50', '2026-03-17 14:55:50'),
(4, 'Function Room C', 80, '3rd Floor', 'Ideal for birthday parties and social events', 'Stage, Sound System, Bar Area, Dance Floor', 'available', 8000.00, NULL, '2026-03-17 14:55:50', '2026-03-17 14:55:50'),
(5, 'Garden Pavilion', 150, 'Outdoor Garden', 'Open-air venue for garden weddings', 'Garden Setting, Stage, Lighting, Backup Indoor Space', 'available', 12000.00, NULL, '2026-03-17 14:55:50', '2026-03-17 14:55:50'),
(6, 'Executive Lounge', 50, '12th Floor', 'Premium venue with city view', 'Bar, Lounge Seating, TV Screens, Private Balcony', 'available', 10000.00, NULL, '2026-03-17 14:55:50', '2026-03-17 14:55:50');

-- --------------------------------------------------------

--
-- Table structure for table `waiting_list`
--

CREATE TABLE `waiting_list` (
  `id` int(10) UNSIGNED NOT NULL,
  `guest_name` varchar(100) NOT NULL,
  `guest_phone` varchar(20) DEFAULT NULL,
  `party_size` int(11) NOT NULL,
  `requested_time` time DEFAULT NULL,
  `wait_started_at` timestamp NULL DEFAULT current_timestamp(),
  `estimated_wait_minutes` int(11) DEFAULT 15,
  `status` enum('waiting','seated','cancelled') NOT NULL DEFAULT 'waiting',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure for view `notification_unread_counts`
--
DROP TABLE IF EXISTS `notification_unread_counts`;

CREATE ALGORITHM=UNDEFINED DEFINER=`` SQL SECURITY DEFINER VIEW `notification_unread_counts`  AS SELECT `notifications`.`user_id` AS `user_id`, count(0) AS `unread_count` FROM `notifications` WHERE `notifications`.`is_read` = 0 GROUP BY `notifications`.`user_id` ;

-- --------------------------------------------------------

--
-- Structure for view `user_balance_summary`
--
DROP TABLE IF EXISTS `user_balance_summary`;

CREATE ALGORITHM=UNDEFINED DEFINER=`` SQL SECURITY DEFINER VIEW `user_balance_summary`  AS SELECT `u`.`id` AS `user_id`, `u`.`full_name` AS `full_name`, `u`.`email` AS `email`, `u`.`loyalty_points` AS `loyalty_points`, `u`.`member_tier` AS `member_tier`, coalesce(`cb`.`total_balance`,0) AS `total_balance`, coalesce(`cb`.`pending_balance`,0) AS `pending_balance`, coalesce(`cb`.`available_balance`,0) AS `available_balance`, coalesce(`cb`.`last_updated`,`u`.`updated_at`) AS `last_updated` FROM (`users` `u` left join `current_balance` `cb` on(`u`.`id` = `cb`.`user_id`)) ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `booking_reference` (`booking_reference`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `status` (`status`),
  ADD KEY `check_in` (`check_in`),
  ADD KEY `idx_user_payment` (`user_id`,`payment_status`,`status`),
  ADD KEY `idx_status_payment` (`status`,`payment_status`),
  ADD KEY `idx_dates` (`check_in`,`check_out`),
  ADD KEY `idx_user` (`user_id`);

--
-- Indexes for table `current_balance`
--
ALTER TABLE `current_balance`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `event_date` (`event_date`),
  ADD KEY `venue_id` (`venue_id`);

--
-- Indexes for table `food_orders`
--
ALTER TABLE `food_orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_reference` (`order_reference`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `status` (`status`);

--
-- Indexes for table `guest_interactions`
--
ALTER TABLE `guest_interactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `admin_id` (`admin_id`),
  ADD KEY `fk_guest_interactions_assigned_to` (`assigned_to`);

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `menu_items`
--
ALTER TABLE `menu_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category` (`category`),
  ADD KEY `is_available` (`is_available`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `is_read` (`is_read`),
  ADD KEY `created_at` (`created_at`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `payment_reference` (`payment_reference`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `booking_id` (`booking_id`),
  ADD KEY `booking_type` (`booking_type`),
  ADD KEY `idx_approval_status` (`approval_status`),
  ADD KEY `idx_payment_status` (`payment_status`),
  ADD KEY `idx_user_status` (`user_id`,`payment_status`);

--
-- Indexes for table `payment_methods`
--
ALTER TABLE `payment_methods`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `redemptions`
--
ALTER TABLE `redemptions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `response_templates`
--
ALTER TABLE `response_templates`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `restaurant_reservations`
--
ALTER TABLE `restaurant_reservations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `reservation_reference` (`reservation_reference`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `reservation_date` (`reservation_date`),
  ADD KEY `status` (`status`),
  ADD KEY `idx_user_payment` (`user_id`,`payment_status`,`status`);

--
-- Indexes for table `restaurant_tables`
--
ALTER TABLE `restaurant_tables`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `table_number` (`table_number`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `review_responses`
--
ALTER TABLE `review_responses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `review_id` (`review_id`),
  ADD KEY `responded_by` (`responded_by`);

--
-- Indexes for table `rewards`
--
ALTER TABLE `rewards`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `rooms`
--
ALTER TABLE `rooms`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `room_maintenance`
--
ALTER TABLE `room_maintenance`
  ADD PRIMARY KEY (`id`),
  ADD KEY `room_id` (`room_id`),
  ADD KEY `reported_by` (`reported_by`),
  ADD KEY `fk_room_maintenance_assigned_to` (`assigned_to`),
  ADD KEY `idx_status` (`cleaned_at`,`assigned_to`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `phone` (`phone`),
  ADD UNIQUE KEY `email_2` (`email`),
  ADD UNIQUE KEY `phone_2` (`phone`);

--
-- Indexes for table `venues`
--
ALTER TABLE `venues`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `venue_name` (`name`);

--
-- Indexes for table `waiting_list`
--
ALTER TABLE `waiting_list`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

--
-- AUTO_INCREMENT for table `current_balance`
--
ALTER TABLE `current_balance`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=95;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `food_orders`
--
ALTER TABLE `food_orders`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `guest_interactions`
--
ALTER TABLE `guest_interactions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `menu_items`
--
ALTER TABLE `menu_items`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=164;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `payment_methods`
--
ALTER TABLE `payment_methods`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `redemptions`
--
ALTER TABLE `redemptions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `response_templates`
--
ALTER TABLE `response_templates`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `restaurant_reservations`
--
ALTER TABLE `restaurant_reservations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `restaurant_tables`
--
ALTER TABLE `restaurant_tables`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `review_responses`
--
ALTER TABLE `review_responses`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `rewards`
--
ALTER TABLE `rewards`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `room_maintenance`
--
ALTER TABLE `room_maintenance`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `venues`
--
ALTER TABLE `venues`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `waiting_list`
--
ALTER TABLE `waiting_list`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `current_balance`
--
ALTER TABLE `current_balance`
  ADD CONSTRAINT `current_balance_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `events`
--
ALTER TABLE `events`
  ADD CONSTRAINT `events_ibfk_1` FOREIGN KEY (`venue_id`) REFERENCES `venues` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `food_orders`
--
ALTER TABLE `food_orders`
  ADD CONSTRAINT `food_orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `guest_interactions`
--
ALTER TABLE `guest_interactions`
  ADD CONSTRAINT `fk_guest_interactions_assigned_to` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `guest_interactions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `guest_interactions_ibfk_2` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payment_methods`
--
ALTER TABLE `payment_methods`
  ADD CONSTRAINT `payment_methods_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `redemptions`
--
ALTER TABLE `redemptions`
  ADD CONSTRAINT `redemptions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `restaurant_reservations`
--
ALTER TABLE `restaurant_reservations`
  ADD CONSTRAINT `restaurant_reservations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `review_responses`
--
ALTER TABLE `review_responses`
  ADD CONSTRAINT `review_responses_ibfk_1` FOREIGN KEY (`review_id`) REFERENCES `reviews` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `review_responses_ibfk_2` FOREIGN KEY (`responded_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `room_maintenance`
--
ALTER TABLE `room_maintenance`
  ADD CONSTRAINT `fk_room_maintenance_assigned_to` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
