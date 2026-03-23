-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3307
-- Generation Time: Mar 23, 2026 at 02:32 AM
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
-- Table structure for table `admin_notifications`
--

CREATE TABLE `admin_notifications` (
  `id` int(10) UNSIGNED NOT NULL,
  `admin_id` int(10) UNSIGNED DEFAULT NULL,
  `title` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `type` enum('info','success','warning','danger') NOT NULL DEFAULT 'info',
  `icon` varchar(50) DEFAULT NULL,
  `link` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `read_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `backup_history`
--

CREATE TABLE `backup_history` (
  `id` int(10) UNSIGNED NOT NULL,
  `backup_name` varchar(255) NOT NULL,
  `backup_size` varchar(50) DEFAULT NULL,
  `backup_type` enum('auto','manual') NOT NULL DEFAULT 'auto',
  `status` enum('pending','completed','failed') NOT NULL DEFAULT 'pending',
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `completed_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `backup_history`
--

INSERT INTO `backup_history` (`id`, `backup_name`, `backup_size`, `backup_type`, `status`, `created_by`, `created_at`, `completed_at`) VALUES
(1, 'backup_20260321_210516', '432 MB', 'manual', 'completed', 8, '2026-03-21 20:05:16', '2026-03-21 20:05:16');

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
  `status` enum('pending','Checked-in','confirmed','cancelled','completed') NOT NULL DEFAULT 'pending',
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
  `promo_code_id` int(10) UNSIGNED DEFAULT NULL,
  `promo_code` varchar(50) DEFAULT NULL,
  `discount_applied` decimal(10,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `booking_reference`, `user_id`, `guest_first_name`, `guest_last_name`, `guest_email`, `guest_phone`, `booking_type`, `check_in`, `check_in_time`, `check_out`, `check_out_time`, `nights`, `room_id`, `room_assigned`, `room_name`, `room_price`, `adults`, `children`, `subtotal`, `tax`, `total_amount`, `status`, `payment_status`, `payment_method`, `payment_date`, `special_requests`, `payment_id`, `points_earned`, `points_awarded`, `points_awarded_at`, `points_used`, `points_discount`, `promo_code_id`, `promo_code`, `discount_applied`, `created_at`, `updated_at`) VALUES
(82, 'HOT-20260320-49F326', 8, 'jzel', 'dols', 'janzeldol4@gmail.com', '+639565819969', 'hotel', '2026-03-21', '21:45:49', '2026-03-23', NULL, 2, '302', NULL, 'Ocean Suite', 6900.00, 2, 0, 13800.00, 1656.00, 15456.00, 'completed', 'paid', NULL, '2026-03-20 20:59:42', '', NULL, 770, 1, '2026-03-20 21:00:30', 0, 0.00, NULL, NULL, 0.00, '2026-03-20 12:57:08', '2026-03-20 13:46:44'),
(83, 'HOT-20260321-C53499', 8, 'jzel', 'dols', 'janzeldol4@gmail.com', '+639565819969', 'hotel', '2026-03-22', NULL, '2026-03-24', NULL, 2, '302', NULL, 'Ocean Suite', 6900.00, 2, 0, 13800.00, 1656.00, 15256.00, 'completed', 'paid', NULL, '2026-03-22 16:31:32', '', NULL, 760, 1, '2026-03-22 16:31:41', 0, 0.00, 5, '11', 200.00, '2026-03-21 19:23:56', '2026-03-22 08:38:19'),
(84, 'HR20260322277099', NULL, 'jhgjh', 'hghj', '', '', 'hotel', '2026-03-23', NULL, '2026-03-25', NULL, 2, '201', NULL, 'Deluxe Twin', 4200.00, 1, 0, 8400.00, 1008.00, 9408.00, 'Checked-in', 'paid', NULL, NULL, '', NULL, 470, 0, NULL, 0, 0.00, NULL, NULL, 0.00, '2026-03-22 08:41:54', '2026-03-22 08:43:11');

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
-- Table structure for table `campaigns`
--

CREATE TABLE `campaigns` (
  `id` int(10) UNSIGNED NOT NULL,
  `campaign_name` varchar(100) NOT NULL,
  `campaign_type` enum('discount','package','event','seasonal','flash_sale') NOT NULL DEFAULT 'discount',
  `description` text DEFAULT NULL,
  `discount_percent` decimal(5,2) DEFAULT NULL,
  `discount_amount` decimal(10,2) DEFAULT NULL,
  `start_date` datetime NOT NULL,
  `end_date` datetime NOT NULL,
  `status` enum('draft','active','scheduled','ended','cancelled') NOT NULL DEFAULT 'draft',
  `target_audience` enum('all','members','new','vip','loyalty') DEFAULT 'all',
  `redemption_limit` int(11) DEFAULT NULL,
  `redemptions_count` int(11) NOT NULL DEFAULT 0,
  `budget` decimal(10,2) DEFAULT NULL,
  `revenue_generated` decimal(10,2) DEFAULT 0.00,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `campaigns`
--

INSERT INTO `campaigns` (`id`, `campaign_name`, `campaign_type`, `description`, `discount_percent`, `discount_amount`, `start_date`, `end_date`, `status`, `target_audience`, `redemption_limit`, `redemptions_count`, `budget`, `revenue_generated`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'Summer Escape', 'seasonal', '20% off on all deluxe rooms', 20.00, NULL, '2025-05-01 00:00:00', '2025-05-31 23:59:59', 'active', 'all', 500, 0, 100000.00, 0.00, NULL, '2026-03-21 18:40:07', '2026-03-21 18:40:07'),
(3, 'Father\'s Day Special', 'event', '15% off + welcome drink for dad', 15.00, NULL, '2025-06-01 00:00:00', '2025-06-15 23:59:59', 'scheduled', 'all', 200, 0, 50000.00, 0.00, NULL, '2026-03-21 18:40:07', '2026-03-21 18:40:07'),
(4, 'Spa & Relax', 'discount', '20% off on all spa treatments', 20.00, NULL, '2025-05-01 00:00:00', '2025-05-30 23:59:59', 'active', 'vip', 200, 0, 40000.00, 0.00, NULL, '2026-03-21 18:40:07', '2026-03-21 18:40:07'),
(6, 'Spring Fling', 'flash_sale', '25% off on suites', 25.00, NULL, '2025-04-01 00:00:00', '2025-05-15 23:59:59', 'ended', 'all', 300, 0, 120000.00, 0.00, NULL, '2026-03-21 18:40:07', '2026-03-21 18:40:07');

-- --------------------------------------------------------

--
-- Table structure for table `campaign_redemptions`
--

CREATE TABLE `campaign_redemptions` (
  `id` int(10) UNSIGNED NOT NULL,
  `campaign_id` int(10) UNSIGNED NOT NULL,
  `promo_code_id` int(10) UNSIGNED DEFAULT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `booking_id` int(10) UNSIGNED DEFAULT NULL,
  `discount_amount` decimal(10,2) NOT NULL,
  `original_amount` decimal(10,2) NOT NULL,
  `final_amount` decimal(10,2) NOT NULL,
  `redeemed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `current_balance`
--

CREATE TABLE `current_balance` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `total_balance` decimal(10,2) UNSIGNED NOT NULL DEFAULT 0.00,
  `pending_balance` decimal(10,2) UNSIGNED NOT NULL DEFAULT 0.00,
  `available_balance` decimal(10,2) UNSIGNED NOT NULL DEFAULT 0.00,
  `admin_approval` enum('pending','approved','rejected') NOT NULL DEFAULT 'approved',
  `approved_by` int(10) UNSIGNED DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `rejection_reason` varchar(255) DEFAULT NULL,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `current_balance`
--

INSERT INTO `current_balance` (`id`, `user_id`, `total_balance`, `pending_balance`, `available_balance`, `admin_approval`, `approved_by`, `approved_at`, `rejection_reason`, `last_updated`) VALUES
(176, 8, 0.00, 0.00, 0.00, 'approved', NULL, NULL, NULL, '2026-03-22 08:31:32'),
(178, NULL, 7840.00, 0.00, 7840.00, 'approved', NULL, NULL, NULL, '2026-03-22 08:41:54');

--
-- Triggers `current_balance`
--
DELIMITER $$
CREATE TRIGGER `prevent_negative_balance_insert` BEFORE INSERT ON `current_balance` FOR EACH ROW BEGIN
    IF NEW.total_balance < 0 THEN
        SET NEW.total_balance = 0;
    END IF;
    IF NEW.available_balance < 0 THEN
        SET NEW.available_balance = 0;
    END IF;
    IF NEW.pending_balance < 0 THEN
        SET NEW.pending_balance = 0;
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `prevent_negative_balance_update` BEFORE UPDATE ON `current_balance` FOR EACH ROW BEGIN
    IF NEW.total_balance < 0 THEN
        SET NEW.total_balance = 0;
    END IF;
    IF NEW.available_balance < 0 THEN
        SET NEW.available_balance = 0;
    END IF;
    IF NEW.pending_balance < 0 THEN
        SET NEW.pending_balance = 0;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `email_templates`
--

CREATE TABLE `email_templates` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `subject` varchar(200) NOT NULL,
  `content` text NOT NULL,
  `type` enum('promo','newsletter','welcome','loyalty') DEFAULT 'promo',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `email_templates`
--

INSERT INTO `email_templates` (`id`, `name`, `subject`, `content`, `type`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Summer Promo', 'Summer Escape - 20% Off Deluxe Rooms!', 'Dear {name},\n\nEnjoy 20% off on all deluxe rooms this summer! Use code SUMMER20.\n\nBook now: {link}', 'promo', 1, '2026-03-21 18:40:07', '2026-03-21 18:40:07'),
(2, 'Weekend Special', 'Weekend Getaway - Free Breakfast!', 'Dear {name},\n\nBook a weekend stay and get free breakfast for 2!\n\nLimited slots available: {link}', 'promo', 1, '2026-03-21 18:40:07', '2026-03-21 18:40:07');

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
  `promo_code_id` int(10) UNSIGNED DEFAULT NULL,
  `promo_code` varchar(50) DEFAULT NULL,
  `discount_applied` decimal(10,2) DEFAULT 0.00,
  `status` enum('pending','urgent','preparing','ready','completed','cancelled') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `food_orders`
--

INSERT INTO `food_orders` (`id`, `order_reference`, `user_id`, `items`, `order_type`, `subtotal`, `service_fee`, `total_amount`, `points_used`, `points_earned`, `promo_code_id`, `promo_code`, `discount_applied`, `status`, `created_at`, `updated_at`) VALUES
(14, 'FOOD-202603-FBB1AA', 8, '[{\"name\":\"Crispy Pata\",\"price\":550,\"quantity\":1,\"isFree\":false}]', 'dine-in', 550.00, 27.50, 577.50, 0, 25, NULL, NULL, 0.00, 'urgent', '2026-03-21 18:31:43', '2026-03-21 18:33:33'),
(15, 'FOOD-202603-A8C2B3', 8, '[{\"name\":\"Garlic Rice\",\"price\":50,\"quantity\":1,\"isFree\":false}]', 'dine-in', 50.00, 2.50, 52.50, 0, 0, NULL, NULL, 0.00, 'pending', '2026-03-21 19:38:34', '2026-03-21 19:38:34'),
(16, 'FOOD-202603-DD1C1F', 8, '[{\"name\":\"Sinigang na Baboy\",\"price\":320,\"quantity\":1,\"isFree\":false}]', 'dine-in', 320.00, 16.00, 336.00, 0, 15, NULL, NULL, 0.00, 'urgent', '2026-03-22 08:46:05', '2026-03-22 08:46:48');

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

-- --------------------------------------------------------

--
-- Table structure for table `hr_staff_cache`
--

CREATE TABLE `hr_staff_cache` (
  `id` int(10) UNSIGNED NOT NULL,
  `employee_id` varchar(50) NOT NULL,
  `employee_number` varchar(50) DEFAULT NULL,
  `full_name` varchar(100) NOT NULL,
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `position` varchar(100) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `hourly_rate` decimal(10,2) DEFAULT 0.00,
  `shift_id` int(11) DEFAULT NULL,
  `shift_name` varchar(50) DEFAULT NULL,
  `shift_start` time DEFAULT NULL,
  `shift_end` time DEFAULT NULL,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(3, 'Beef', 'Meat', 5, 10, 'kg', '2026-03-16 16:35:46', '2026-03-22 08:47:19'),
(4, 'Chicken', 'Meat', 12, 10, 'kg', '2026-03-16 16:35:46', '2026-03-16 16:35:46'),
(5, 'Cooking Oil', 'Supply', 12, 10, 'bottles', '2026-03-16 16:35:46', '2026-03-21 17:52:14'),
(6, 'Bath Towels', 'Linens', 0, 50, 'pcs', '2026-03-21 17:40:11', '2026-03-21 17:56:20'),
(7, 'Toilet Paper', 'Housekeeping', 12, 10, 'cases', '2026-03-21 17:40:11', '2026-03-21 17:55:43'),
(8, 'Shampoo', 'Amenities', 5, 50, 'bottles', '2026-03-21 17:40:11', '2026-03-21 17:55:53'),
(9, 'Light Bulbs', 'Maintenance', 32, 20, 'pcs', '2026-03-21 17:40:11', '2026-03-21 17:40:11'),
(10, 'Hand Soap', 'Housekeeping', 35, 20, 'bottles', '2026-03-21 17:40:11', '2026-03-21 17:54:38'),
(11, 'Bed Sheets', 'Linens', 45, 30, 'sets', '2026-03-21 17:40:11', '2026-03-21 17:55:29'),
(12, 'Coffee Packets', 'Food', 202, 100, 'packs', '2026-03-21 17:40:11', '2026-03-21 18:26:27');

-- --------------------------------------------------------

--
-- Table structure for table `login_history`
--

CREATE TABLE `login_history` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `user_name` varchar(100) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `status` enum('success','failed') NOT NULL DEFAULT 'success',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `login_history`
--

INSERT INTO `login_history` (`id`, `user_id`, `user_name`, `ip_address`, `user_agent`, `status`, `created_at`) VALUES
(1, 7, 'Janzel', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'failed', '2026-03-21 20:18:20'),
(2, 8, 'jzel dols', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'success', '2026-03-21 20:18:27'),
(3, 8, 'jzel dols', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'success', '2026-03-21 20:25:47'),
(4, 8, 'jzel dols', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'success', '2026-03-22 04:48:32'),
(5, 8, 'jzel dols', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'success', '2026-03-22 05:26:30'),
(6, 8, 'jzel dols', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'success', '2026-03-22 08:35:17'),
(7, 8, 'jzel dols', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'success', '2026-03-22 08:36:57');

-- --------------------------------------------------------

--
-- Table structure for table `menu_items`
--

CREATE TABLE `menu_items` (
  `id` int(10) UNSIGNED NOT NULL,
  `item_code` varchar(20) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `cost` decimal(10,2) DEFAULT 0.00,
  `stock` int(11) DEFAULT 0,
  `category` enum('appetizers','mains','desserts','beverages') NOT NULL,
  `is_available` tinyint(1) NOT NULL DEFAULT 1,
  `status` enum('available','out_of_stock','special','disabled') DEFAULT 'available',
  `image_url` varchar(255) DEFAULT NULL,
  `preparation_time` int(11) DEFAULT 15,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `menu_items`
--

INSERT INTO `menu_items` (`id`, `item_code`, `name`, `description`, `price`, `cost`, `stock`, `category`, `is_available`, `status`, `image_url`, `preparation_time`, `created_at`, `updated_at`) VALUES
(1, 'M001', 'Sinigang na Baboy', 'tamarind soup, pork, veggies', 320.00, 160.00, 50, 'mains', 1, 'available', NULL, 20, '2026-03-16 14:11:26', '2026-03-19 17:08:33'),
(2, 'M002', 'Sizzling Sisig', 'chopped pork, onion, egg', 290.00, 145.00, 50, 'mains', 1, 'available', NULL, 15, '2026-03-16 14:11:26', '2026-03-18 15:40:46'),
(3, 'M003', 'Crispy Pata', 'deep-fried pork knuckle', 550.00, 275.00, 50, 'appetizers', 1, 'available', NULL, 25, '2026-03-16 14:11:26', '2026-03-19 17:08:10'),
(4, 'D004', 'Halo-Halo', 'shaved ice, fruits, leche flan', 150.00, 75.00, 50, 'desserts', 1, 'available', NULL, 10, '2026-03-16 14:11:26', '2026-03-19 17:08:42'),
(5, 'B005', 'Fresh Buko Juice', 'with coconut pulp', 90.00, 45.00, 50, 'beverages', 1, 'available', NULL, 5, '2026-03-16 14:11:26', '2026-03-19 17:08:27'),
(6, 'M006', 'Garlic Rice', 'sinangag, plain', 50.00, 25.00, 50, 'mains', 1, 'available', NULL, 5, '2026-03-16 14:11:26', '2026-03-18 15:37:29');

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
(385, 7, 'Payment Pending Approval', 'New payment of ₱19,040.00 from Guest needs approval', 'warning', 'fa-clock', '/src/admin/operations/billing_&_payment.php', 0, '2026-03-19 14:10:46', NULL),
(391, 7, 'Payment Pending Approval', 'New payment of ₱300.00 from Guest needs approval', 'warning', 'fa-clock', '/src/admin/operations/billing_&_payment.php', 0, '2026-03-19 14:13:09', NULL),
(396, 7, 'Payment Pending Approval', 'New payment of ₱19,040.00 needs approval', 'warning', 'fa-clock', '/src/admin/operations/billing_&_payment.php', 0, '2026-03-19 15:26:06', NULL),
(401, 4, 'd', 's', 'promo', 'fa-bullhorn', NULL, 0, '2026-03-19 16:04:59', NULL),
(408, 7, 'Payment Pending Approval', 'New payment of ₱15,456.00 from Guest needs approval', 'warning', 'fa-clock', '/src/admin/operations/billing_&_payment.php', 0, '2026-03-19 17:41:59', NULL),
(414, 7, 'Payment Pending Approval', 'New payment of ₱100.00 from Guest needs approval', 'warning', 'fa-clock', '/src/admin/operations/billing_&_payment.php', 0, '2026-03-19 18:03:05', NULL),
(419, 7, 'Payment Pending Approval', 'New payment of ₱100.00 from Guest needs approval', 'warning', 'fa-clock', '/src/admin/operations/billing_&_payment.php', 0, '2026-03-19 18:10:46', NULL),
(425, 7, 'Payment Pending Approval', 'New payment of ₱19,040.00 from Guest needs approval', 'warning', 'fa-clock', '/src/admin/operations/billing_&_payment.php', 0, '2026-03-19 18:13:46', NULL),
(433, 7, 'Payment Pending Approval', 'New payment of ₱19,040.00 needs approval', 'warning', 'fa-clock', '/src/admin/operations/billing_&_payment.php', 0, '2026-03-20 07:51:55', NULL),
(453, 7, 'Payment Pending Approval', 'New payment of ₱15,456.00 from Guest needs approval', 'warning', 'fa-clock', '/src/admin/operations/billing_&_payment.php', 0, '2026-03-20 12:54:39', NULL),
(457, 7, 'Payment Pending Approval', 'New payment of ₱15,456.00 from Guest needs approval', 'warning', 'fa-clock', '/src/admin/operations/billing_&_payment.php', 0, '2026-03-20 12:57:17', NULL),
(462, 7, 'Payment Pending Approval', 'New payment of ₱15,456.00 from Guest needs approval', 'warning', 'fa-clock', '/src/admin/operations/billing_&_payment.php', 0, '2026-03-20 12:59:33', NULL),
(475, 8, 'Booking Created', 'Your booking for Ocean Suite from 2026-03-22 to 2026-03-24 has been created. Total: ₱15,256.00 (₱200.00 discount applied) You\'ll earn 760 loyalty points after payment.', 'success', 'fa-hotel', '/src/customer_portal/my_reservation.php', 0, '2026-03-21 19:23:56', NULL),
(476, 8, 'Order Placed', 'Your order #FOOD-202603-A8C2B3 has been placed. Total: ₱52.50', 'success', 'fa-bag-shopping', '/src/customer_portal/order_food.php', 0, '2026-03-21 19:38:34', NULL),
(477, 8, 'Payment Pending Approval', 'Payment of ₱15,256.00 received and pending admin approval. You\'ll earn 760 loyalty points after approval.', 'info', 'fa-clock', '/src/customer_portal/payments.php', 0, '2026-03-22 08:30:07', NULL),
(478, 7, 'Payment Pending Approval', 'New payment of ₱15,256.00 from Guest needs approval', 'warning', 'fa-clock', '/src/admin/operations/billing_&_payment.php', 0, '2026-03-22 08:30:07', NULL),
(479, 8, 'Payment Pending Approval', 'New payment of ₱15,256.00 from Guest needs approval', 'warning', 'fa-clock', '/src/admin/operations/billing_&_payment.php', 0, '2026-03-22 08:30:07', NULL),
(480, 8, 'Payment Approved', 'Your payment of ₱15,256.00 has been approved. All your bookings are now confirmed. You earned 760 loyalty points!', 'success', 'fa-check-circle', '/src/customer_portal/payments.php', 0, '2026-03-22 08:31:32', NULL),
(481, 8, 'Payment Approved', 'Payment #PAY-202603-88FE5B90 approved. All unpaid bookings for user have been confirmed.', 'success', 'fa-check-circle', NULL, 0, '2026-03-22 08:31:32', NULL),
(482, 8, 'Loyalty Points Awarded!', 'You\'ve earned 760 loyalty points for your booking #HOT-20260321-C53499', 'loyalty', 'fa-star', '/src/customer_portal/loyalty_rewards.php', 0, '2026-03-22 08:31:41', NULL),
(483, 8, 'Points Awarded', 'Added 760 points to user for booking #HOT-20260321-C53499', 'success', 'fa-star', NULL, 0, '2026-03-22 08:31:41', NULL),
(484, 7, 'Reward Redemption', 'User redeemed: Free Coffee / Tea for 2400 points', 'info', 'fa-gift', '/src/admin/customer_management/loyalty_rewards.php', 0, '2026-03-22 08:33:48', NULL),
(485, 8, 'Reward Redemption', 'User redeemed: Free Coffee / Tea for 2400 points', 'info', 'fa-gift', '/src/admin/customer_management/loyalty_rewards.php', 0, '2026-03-22 08:33:48', NULL),
(486, 8, 'Booking Status Updated', 'Booking #HOT-20260321-C53499 status changed from confirmed to checked-in', 'info', 'fa-calendar-check', NULL, 0, '2026-03-22 08:38:09', NULL),
(487, 8, 'Booking Status Update', 'You have been checked in. Enjoy your stay! (Booking #HOT-20260321-C53499)', 'info', 'fa-info-circle', '/src/customer_portal/my_reservation.php', 0, '2026-03-22 08:38:09', NULL),
(488, 8, 'Guest Checked Out', 'Guest checked out from room 302. Room marked for cleaning.', 'info', 'fa-door-open', NULL, 0, '2026-03-22 08:38:19', NULL),
(489, 8, 'Cleaning Task Assigned', 'Cleaning task for room 302 assigned to bstmsn', 'success', 'fa-broom', NULL, 0, '2026-03-22 08:38:38', NULL),
(490, 8, 'Task Completed', 'Task #10 has been completed', 'success', 'fa-check-circle', NULL, 0, '2026-03-22 08:38:48', NULL),
(491, 8, 'Room Marked Clean', 'Room 302 has been marked as clean and ready for guests.', 'success', 'fa-sparkles', NULL, 0, '2026-03-22 08:39:06', NULL),
(492, 8, 'Maintenance Reported', 'Maintenance reported for room 101: hfh', 'warning', 'fa-wrench', NULL, 0, '2026-03-22 08:39:23', NULL),
(493, 8, 'Task Completed', 'Task #11 has been completed', 'success', 'fa-check-circle', NULL, 0, '2026-03-22 08:39:46', NULL),
(494, 8, 'Room Assigned', 'Room 101 assigned to jhgjh', 'success', 'fa-user-plus', NULL, 0, '2026-03-22 08:41:54', NULL),
(495, 8, 'Booking Updated', 'Booking #HR20260322277099 was updated', 'info', 'fa-pen-to-square', NULL, 0, '2026-03-22 08:43:11', NULL),
(496, 8, 'Order Placed', 'Your order #FOOD-202603-DD1C1F has been placed. Total: ₱336.00', 'success', 'fa-bag-shopping', '/src/customer_portal/order_food.php', 0, '2026-03-22 08:46:05', NULL),
(497, 8, 'Order Status Update', 'Order #FOOD-202603-DD1C1F status changed from pending to preparing', 'info', 'fa-utensils', NULL, 0, '2026-03-22 08:46:19', NULL);

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
(57, 'PAY-202603-42DB30E7', 8, '', 0, 15456.00, 'GCash', 'failed', 'rejected', 8, '2026-03-20 20:58:45', 'test', NULL, '2026-03-20 20:57:17', '2026-03-20 12:57:17'),
(58, 'PAY-202603-4B5659CE', 8, '', 0, 15456.00, 'GCash', 'completed', 'approved', 8, '2026-03-20 20:59:42', NULL, NULL, '2026-03-20 20:59:42', '2026-03-20 12:59:33'),
(59, 'PAY-202603-88FE5B90', 8, '', 0, 15256.00, 'GCash', 'completed', 'approved', 8, '2026-03-22 16:31:32', NULL, NULL, '2026-03-22 16:31:32', '2026-03-22 08:30:07');

--
-- Triggers `payments`
--
DELIMITER $$
CREATE TRIGGER `update_balance_on_payment_insert` AFTER INSERT ON `payments` FOR EACH ROW BEGIN
    -- Only handle pending payments
    IF NEW.payment_status = 'pending' AND NEW.approval_status = 'pending' THEN
        -- When payment is created as pending, move amount from available to pending
        UPDATE current_balance 
        SET pending_balance = pending_balance + NEW.amount,
            available_balance = available_balance - NEW.amount,
            last_updated = NOW()
        WHERE user_id = NEW.user_id;
        
    -- Handle pre-approved payments (if any)
    ELSEIF NEW.payment_status = 'completed' AND NEW.approval_status = 'approved' THEN
        -- When payment is already approved on insert
        UPDATE current_balance 
        SET total_balance = GREATEST(0, total_balance - NEW.amount),
            pending_balance = GREATEST(0, pending_balance - NEW.amount),
            last_updated = NOW()
        WHERE user_id = NEW.user_id;
        
        -- Update booking/reservation
        IF NEW.booking_type = 'hotel' THEN
            UPDATE bookings 
            SET payment_status = 'paid', 
                payment_date = NOW() 
            WHERE id = NEW.booking_id;
        ELSEIF NEW.booking_type = 'restaurant' THEN
            UPDATE restaurant_reservations 
            SET payment_status = 'paid', 
                payment_date = NOW() 
            WHERE id = NEW.booking_id;
        END IF;
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `update_balance_on_payment_update` AFTER UPDATE ON `payments` FOR EACH ROW BEGIN
    -- Handle approval/rejection status changes
    IF OLD.approval_status != NEW.approval_status OR OLD.payment_status != NEW.payment_status THEN
        
        -- Case 1: Payment was pending and is now APPROVED
        IF OLD.payment_status = 'pending' 
           AND OLD.approval_status = 'pending' 
           AND NEW.payment_status = 'completed' 
           AND NEW.approval_status = 'approved' 
        THEN
            -- When approved: Remove from pending and total balance
            UPDATE current_balance 
            SET total_balance = GREATEST(0, total_balance - NEW.amount),
                pending_balance = GREATEST(0, pending_balance - NEW.amount),
                last_updated = NOW()
            WHERE user_id = NEW.user_id;
            
            -- Update the booking/reservation payment status
            IF NEW.booking_type = 'hotel' THEN
                UPDATE bookings 
                SET payment_status = 'paid', 
                    payment_date = NOW() 
                WHERE id = NEW.booking_id;
            ELSEIF NEW.booking_type = 'restaurant' THEN
                UPDATE restaurant_reservations 
                SET payment_status = 'paid', 
                    payment_date = NOW() 
                WHERE id = NEW.booking_id;
            END IF;
            
        -- Case 2: Payment was pending and is now REJECTED
        ELSEIF OLD.payment_status = 'pending' 
               AND OLD.approval_status = 'pending' 
               AND NEW.payment_status = 'failed' 
               AND NEW.approval_status = 'rejected' 
        THEN
            -- When rejected: Return amount from pending back to available
            -- DO NOT touch total_balance (since payment wasn't completed)
            UPDATE current_balance 
            SET pending_balance = GREATEST(0, pending_balance - NEW.amount),
                available_balance = available_balance + NEW.amount,
                last_updated = NOW()
            WHERE user_id = NEW.user_id;
            
            -- Update booking/reservation back to unpaid
            IF NEW.booking_type = 'hotel' THEN
                UPDATE bookings 
                SET payment_status = 'unpaid' 
                WHERE id = NEW.booking_id;
            ELSEIF NEW.booking_type = 'restaurant' THEN
                UPDATE restaurant_reservations 
                SET payment_status = 'unpaid' 
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
(6, 8, 'gcash', 'GCash', 'sdasd', '09565819982', '2026-08', 1, '2026-03-19 14:10:40');

-- --------------------------------------------------------

--
-- Table structure for table `promo_codes`
--

CREATE TABLE `promo_codes` (
  `id` int(10) UNSIGNED NOT NULL,
  `campaign_id` int(10) UNSIGNED DEFAULT NULL,
  `code` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `discount_type` enum('percentage','fixed') NOT NULL DEFAULT 'percentage',
  `discount_value` decimal(10,2) NOT NULL,
  `min_purchase` decimal(10,2) DEFAULT 0.00,
  `max_discount` decimal(10,2) DEFAULT NULL,
  `valid_from` datetime NOT NULL,
  `valid_to` datetime NOT NULL,
  `usage_limit` int(11) DEFAULT NULL,
  `used_count` int(11) NOT NULL DEFAULT 0,
  `per_user_limit` int(11) DEFAULT 1,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `promo_codes`
--

INSERT INTO `promo_codes` (`id`, `campaign_id`, `code`, `description`, `discount_type`, `discount_value`, `min_purchase`, `max_discount`, `valid_from`, `valid_to`, `usage_limit`, `used_count`, `per_user_limit`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, 'SUMMER20', '20% off deluxe rooms', 'percentage', 20.00, 0.00, NULL, '2025-05-01 00:00:00', '2025-05-31 23:59:59', 500, 124, 1, 1, '2026-03-21 18:40:07', '2026-03-21 18:40:07'),
(2, NULL, 'BREAKFASTFREE', 'Free breakfast for 2', 'fixed', 500.00, 0.00, NULL, '2025-05-01 00:00:00', '2025-06-15 23:59:59', 300, 87, 1, 1, '2026-03-21 18:40:07', '2026-03-21 18:40:07'),
(3, 4, 'SPA20', '20% off spa treatments', 'percentage', 20.00, 0.00, NULL, '2025-05-01 00:00:00', '2025-05-30 23:59:59', 200, 56, 1, 1, '2026-03-21 18:40:07', '2026-03-21 18:40:07'),
(4, 1, '23232', 'd', 'fixed', 29.00, 1.00, 12.00, '2026-03-22 02:46:00', '2026-03-24 02:46:00', 2, 0, 1, 1, '2026-03-21 18:46:33', '2026-03-21 18:46:33'),
(5, NULL, '11', 'd', 'fixed', 200.00, 500.00, 500.00, '2026-03-21 16:00:00', '2026-04-20 16:00:00', 1, 1, 1, 1, '2026-03-21 19:23:33', '2026-03-21 19:23:56'),
(6, NULL, '2', 'd', 'percentage', 10.00, 200.00, 121.00, '2026-03-21 16:00:00', '2026-04-20 16:00:00', NULL, 0, 1, 1, '2026-03-21 19:38:19', '2026-03-21 19:38:19');

-- --------------------------------------------------------

--
-- Table structure for table `purchase_orders`
--

CREATE TABLE `purchase_orders` (
  `id` int(10) UNSIGNED NOT NULL,
  `po_number` varchar(50) NOT NULL,
  `supplier_id` int(10) UNSIGNED DEFAULT NULL,
  `order_date` date NOT NULL,
  `expected_delivery` date DEFAULT NULL,
  `status` enum('pending','approved','shipped','received','cancelled') NOT NULL DEFAULT 'pending',
  `total_amount` decimal(10,2) DEFAULT 0.00,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `purchase_orders`
--

INSERT INTO `purchase_orders` (`id`, `po_number`, `supplier_id`, `order_date`, `expected_delivery`, `status`, `total_amount`, `created_by`, `notes`, `created_at`, `updated_at`) VALUES
(1, 'PO-20260321-05B70E', 1, '2026-03-22', '2026-03-28', 'pending', 3000.00, 8, '', '2026-03-21 17:49:04', '2026-03-21 17:49:04');

-- --------------------------------------------------------

--
-- Table structure for table `purchase_order_items`
--

CREATE TABLE `purchase_order_items` (
  `id` int(10) UNSIGNED NOT NULL,
  `po_id` int(10) UNSIGNED NOT NULL,
  `inventory_id` int(10) UNSIGNED NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `received_quantity` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `purchase_order_items`
--

INSERT INTO `purchase_order_items` (`id`, `po_id`, `inventory_id`, `quantity`, `unit_price`, `total_price`, `received_quantity`, `created_at`) VALUES
(1, 1, 3, 50, 60.00, 3000.00, 0, '2026-03-21 17:49:04');

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
(23, 8, 'Free Coffee / Tea', 240, 'beverage', 'pending', '2026-03-19 13:40:50'),
(24, 8, 'Room Upgrade', 1200, 'hotel', 'pending', '2026-03-19 13:41:02'),
(25, 8, 'Free Coffee / Tea', 2400, 'beverage', 'pending', '2026-03-22 08:33:48');

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
(1, 'T1', 2, 'Window', 'available', '2026-03-16 10:28:37', '2026-03-19 10:29:50'),
(2, 'T2', 4, 'Center', 'available', '2026-03-16 10:28:37', '2026-03-18 15:11:12'),
(3, 'T3', 2, 'Window', 'available', '2026-03-16 10:28:37', '2026-03-18 14:06:23'),
(4, 'T4', 6, 'Private', 'available', '2026-03-16 10:28:37', '2026-03-19 10:29:54'),
(5, 'T5', 2, 'Bar', 'available', '2026-03-16 10:28:37', '2026-03-19 10:32:47'),
(6, 'T6', 4, 'Center', 'available', '2026-03-16 10:28:37', '2026-03-19 10:30:01'),
(7, 'T7', 2, 'Window', 'available', '2026-03-16 10:28:37', '2026-03-18 14:06:16'),
(8, 'T8', 8, 'Private', 'available', '2026-03-16 10:28:37', '2026-03-19 13:45:27');

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
(1, 'Free Coffee / Tea', 'any hot beverage at Azure Lounge', 2400, 'beverage', NULL, 1, NULL, 9, '2026-03-16 17:30:12', '2026-03-22 08:33:48'),
(2, 'Complimentary Breakfast', 'for one person at Azure Restaurant', 4800, 'dining', NULL, 1, NULL, 2, '2026-03-16 17:30:12', '2026-03-20 13:01:24'),
(3, 'Late Check-out (2pm)', 'subject to availability', 6000, 'hotel', NULL, 1, NULL, 1, '2026-03-16 17:30:12', '2026-03-20 13:01:29'),
(4, 'Room Upgrade', 'deluxe to suite (subject to availability)', 12000, 'hotel', NULL, 1, NULL, 2, '2026-03-16 17:30:12', '2026-03-20 13:01:32'),
(5, 'Free Coffee / Tea', 'any hot beverage at Azure Lounge', 2400, 'beverage', NULL, 1, 100, 0, '2026-03-16 18:23:40', '2026-03-20 13:01:37'),
(6, 'Complimentary Breakfast', 'for one person at Azure Restaurant', 4800, 'dining', NULL, 1, 50, 0, '2026-03-16 18:23:40', '2026-03-20 13:01:40'),
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
  `needs_cleaning` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rooms`
--

INSERT INTO `rooms` (`id`, `name`, `description`, `price`, `beds`, `view`, `amenities`, `max_occupancy`, `image_url`, `is_available`, `needs_cleaning`, `created_at`) VALUES
('101', 'Superior Double', NULL, 3500.00, '1 double bed', 'city view', 'Free WiFi, TV, Aircon', 2, NULL, 1, 0, '2026-03-17 12:34:03'),
('102', 'Superior Double', NULL, 3500.00, '1 double bed', 'city view', 'Free WiFi, TV, Aircon', 2, NULL, 1, 0, '2026-03-17 12:34:03'),
('201', 'Deluxe Twin', NULL, 4200.00, '2 single beds', 'city view', 'Free WiFi, TV, Aircon', 2, NULL, 0, 0, '2026-03-15 07:19:38'),
('202', 'Ocean Suite', NULL, 6900.00, '1 king bed', 'ocean view', 'Jacuzzi, Free WiFi, Mini Bar', 3, NULL, 1, 0, '2026-03-15 07:19:38'),
('203', 'Superior Double', NULL, 3500.00, 'double bed', 'city view', 'Free WiFi, TV', 2, NULL, 1, 0, '2026-03-15 07:19:38'),
('204', 'Family Room', NULL, 5500.00, '2 queen beds', 'pool view', 'Free WiFi, TV, Mini Fridge', 4, NULL, 1, 0, '2026-03-15 07:19:38'),
('205', 'Executive Suite', NULL, 8500.00, '1 king bed', 'ocean view', 'Jacuzzi, Living Area, Free WiFi', 2, NULL, 0, 0, '2026-03-15 07:19:38'),
('211', 'Rooftop', NULL, 2000.00, '1 double bed', 'city view', '', 10, NULL, 1, 0, '2026-03-19 10:21:28'),
('301', 'Ocean Suite', NULL, 6900.00, '1 king bed', 'ocean view', 'Jacuzzi, Free WiFi, Mini Bar', 3, NULL, 1, 0, '2026-03-17 12:34:03'),
('302', 'Ocean Suite', NULL, 6900.00, '1 king bed', 'ocean view', 'Jacuzzi, Free WiFi, Mini Bar', 3, NULL, 1, 0, '2026-03-17 12:34:03'),
('401', 'Family Room', NULL, 5500.00, '2 queen beds', 'pool view', 'Free WiFi, TV, Mini Fridge', 4, NULL, 1, 0, '2026-03-17 12:34:03'),
('402', 'Family Room', NULL, 5500.00, '2 queen beds', 'pool view', 'Free WiFi, TV, Mini Fridge', 4, NULL, 1, 0, '2026-03-17 12:34:03');

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
  `assigned_hr_employee_id` varchar(50) DEFAULT NULL,
  `employee_id` varchar(50) DEFAULT NULL,
  `cleaned_at` datetime DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `room_maintenance`
--

INSERT INTO `room_maintenance` (`id`, `room_id`, `condition_status`, `priority`, `reported_at`, `reported_by`, `assigned_to`, `assigned_hr_employee_id`, `employee_id`, `cleaned_at`, `completed_at`, `notes`, `created_at`, `updated_at`) VALUES
(10, '302', 'minor', 'medium', '2026-03-22 16:38:37', 8, NULL, 'EMP-060', NULL, '2026-03-22 16:38:48', '2026-03-22 16:38:48', 'Cleaning task: Room needs cleaning', '2026-03-22 08:38:37', '2026-03-22 08:38:48'),
(11, '101', 'damage', 'medium', '2026-03-22 16:39:23', 8, NULL, NULL, NULL, '2026-03-22 16:39:46', '2026-03-22 16:39:46', 'hfh (Scheduled: 2026-03-23)', '2026-03-22 08:39:23', '2026-03-22 08:39:46');

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
-- Table structure for table `staff_assignments`
--

CREATE TABLE `staff_assignments` (
  `id` int(10) UNSIGNED NOT NULL,
  `employee_id` varchar(50) NOT NULL,
  `assigned_tables` varchar(255) DEFAULT NULL,
  `assigned_by` int(10) UNSIGNED DEFAULT NULL,
  `assigned_date` datetime DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `staff_assignments`
--

INSERT INTO `staff_assignments` (`id`, `employee_id`, `assigned_tables`, `assigned_by`, `assigned_date`, `updated_at`) VALUES
(3, 'EMP-083', 'Tables 24', 8, '2026-03-19 18:09:44', '2026-03-19 10:09:49');

-- --------------------------------------------------------

--
-- Table structure for table `staff_notes`
--

CREATE TABLE `staff_notes` (
  `id` int(10) UNSIGNED NOT NULL,
  `employee_id` varchar(50) NOT NULL,
  `note` text NOT NULL,
  `rating` tinyint(1) DEFAULT NULL,
  `rating_type` enum('performance','attitude','punctuality','overall') DEFAULT 'overall',
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `staff_notes`
--

INSERT INTO `staff_notes` (`id`, `employee_id`, `note`, `rating`, `rating_type`, `created_by`, `created_at`) VALUES
(3, 'EMP-083', 'd', 3, 'attitude', 8, '2026-03-20 17:19:05'),
(4, 'EMP-083', 'f', 5, 'performance', 8, '2026-03-20 17:23:58');

-- --------------------------------------------------------

--
-- Table structure for table `staff_performance`
--

CREATE TABLE `staff_performance` (
  `id` int(10) UNSIGNED NOT NULL,
  `staff_id` int(10) UNSIGNED NOT NULL,
  `rating` decimal(3,2) NOT NULL,
  `feedback` text DEFAULT NULL,
  `reviewer_id` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `staff_status`
--

CREATE TABLE `staff_status` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `status` enum('on duty','break','off duty') NOT NULL DEFAULT 'off duty',
  `shift` varchar(50) DEFAULT NULL,
  `assigned_tables` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stock_movements`
--

CREATE TABLE `stock_movements` (
  `id` int(10) UNSIGNED NOT NULL,
  `inventory_id` int(10) UNSIGNED NOT NULL,
  `type` enum('in','out','adjustment') NOT NULL,
  `quantity` int(11) NOT NULL,
  `previous_stock` int(11) NOT NULL,
  `new_stock` int(11) NOT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `reference_type` enum('purchase_order','sale','adjustment','waste') DEFAULT NULL,
  `reference_id` int(10) UNSIGNED DEFAULT NULL,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `stock_movements`
--

INSERT INTO `stock_movements` (`id`, `inventory_id`, `type`, `quantity`, `previous_stock`, `new_stock`, `reason`, `reference_type`, `reference_id`, `created_by`, `created_at`) VALUES
(1, 3, 'in', 15, 8, 23, '', NULL, NULL, 8, '2026-03-21 17:50:46'),
(2, 5, 'in', 7, 5, 12, '', NULL, NULL, 8, '2026-03-21 17:52:14'),
(3, 10, 'in', 20, 15, 35, '', NULL, NULL, 8, '2026-03-21 17:54:38'),
(4, 7, 'out', 0, 6, 6, '', NULL, NULL, 8, '2026-03-21 17:55:21'),
(5, 11, 'out', 0, 45, 45, '', NULL, NULL, 8, '2026-03-21 17:55:29'),
(6, 7, 'in', 6, 6, 12, '', NULL, NULL, 8, '2026-03-21 17:55:43'),
(7, 8, 'in', 5, 0, 5, '', NULL, NULL, 8, '2026-03-21 17:55:53'),
(8, 6, 'out', 1, 124, 123, '', NULL, NULL, 8, '2026-03-21 17:56:10'),
(9, 6, 'out', 123, 123, 0, '', NULL, NULL, 8, '2026-03-21 17:56:20'),
(10, 12, 'in', 2, 200, 202, '', '', NULL, 8, '2026-03-21 18:26:27'),
(11, 3, 'out', 18, 23, 5, '', '', NULL, 8, '2026-03-22 08:47:19');

-- --------------------------------------------------------

--
-- Table structure for table `suppliers`
--

CREATE TABLE `suppliers` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `contact_person` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `suppliers`
--

INSERT INTO `suppliers` (`id`, `name`, `contact_person`, `phone`, `email`, `address`, `created_at`) VALUES
(1, 'Fresh Foods Inc.', 'Juan Dela Cruz', '0917 555 1234', 'juan@freshfoods.com', NULL, '2026-03-21 17:40:11'),
(2, 'Hotel Supplies Co.', 'Maria Santos', '0917 555 5678', 'maria@hotelsupplies.com', NULL, '2026-03-21 17:40:11'),
(3, 'Linens & More', 'Jose Rizal', '0917 555 9012', 'jose@linensandmore.com', NULL, '2026-03-21 17:40:11'),
(4, 'Kitchen Essentials', 'Ana Reyes', '0918 555 3456', 'ana@kitchenessentials.com', NULL, '2026-03-21 17:40:11');

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `id` int(10) UNSIGNED NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_type` enum('text','number','boolean','json') NOT NULL DEFAULT 'text',
  `category` enum('general','regional','notifications','taxes','backup','security') NOT NULL DEFAULT 'general',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `category`, `created_at`, `updated_at`) VALUES
(1, 'hotel_name', 'Hotel & Restaurant', 'text', 'general', '2026-03-21 19:41:33', '2026-03-21 19:41:33'),
(2, 'hotel_address', '123 Bonifacio St., Makati City', 'text', 'general', '2026-03-21 19:41:33', '2026-03-21 19:41:33'),
(3, 'hotel_contact', '+63 2 1234 5678', 'text', 'general', '2026-03-21 19:41:33', '2026-03-21 19:41:33'),
(4, 'hotel_email', 'test@gmail.com', 'text', 'general', '2026-03-21 19:41:33', '2026-03-21 20:28:26'),
(5, 'hotel_tax_id', '123-456-789-000', 'text', 'general', '2026-03-21 19:41:33', '2026-03-21 19:41:33'),
(6, 'hotel_timezone', 'Asia/Manila', 'text', 'general', '2026-03-21 19:41:33', '2026-03-21 19:41:33'),
(7, 'currency', 'PHP', 'text', 'regional', '2026-03-21 19:41:33', '2026-03-21 19:41:33'),
(8, 'currency_symbol', '₱', 'text', 'regional', '2026-03-21 19:41:33', '2026-03-21 19:41:33'),
(9, 'date_format', 'm/d/Y', 'text', 'regional', '2026-03-21 19:41:33', '2026-03-21 19:41:33'),
(10, 'time_format', '12', 'text', 'regional', '2026-03-21 19:41:33', '2026-03-21 19:41:33'),
(11, 'week_start', 'Monday', 'text', 'regional', '2026-03-21 19:41:33', '2026-03-21 19:41:33'),
(12, 'tax_vat', '12.1', 'number', 'taxes', '2026-03-21 19:41:33', '2026-03-21 20:06:05'),
(13, 'tax_service_charge', '10', 'number', 'taxes', '2026-03-21 19:41:33', '2026-03-21 19:41:33'),
(14, 'tax_city', '50', 'number', 'taxes', '2026-03-21 19:41:33', '2026-03-21 19:41:33'),
(15, 'tax_tourist', '0', 'number', 'taxes', '2026-03-21 19:41:33', '2026-03-21 19:41:33'),
(16, 'additional_fees', '[]', 'json', 'taxes', '2026-03-21 19:41:33', '2026-03-21 19:41:33'),
(17, 'backup_frequency', 'daily', 'text', 'backup', '2026-03-21 19:41:33', '2026-03-21 19:41:33'),
(18, 'backup_time', '03:00', 'text', 'backup', '2026-03-21 19:41:33', '2026-03-21 19:41:33'),
(19, 'backup_include_files', '1', 'boolean', 'backup', '2026-03-21 19:41:33', '2026-03-21 19:41:33'),
(20, 'backup_compress', '1', 'boolean', 'backup', '2026-03-21 19:41:33', '2026-03-21 19:41:33'),
(21, 'security_2fa', '1', 'boolean', 'security', '2026-03-21 19:41:33', '2026-03-21 19:41:33'),
(22, 'security_remember_me', '1', 'boolean', 'security', '2026-03-21 19:41:33', '2026-03-21 19:41:33'),
(23, 'security_session_timeout', '30', 'number', 'security', '2026-03-21 19:41:33', '2026-03-21 19:41:33'),
(24, 'security_min_length', '1', 'boolean', 'security', '2026-03-21 19:41:33', '2026-03-21 19:41:33'),
(25, 'security_uppercase', '1', 'boolean', 'security', '2026-03-21 19:41:33', '2026-03-21 19:41:33'),
(26, 'security_number', '1', 'boolean', 'security', '2026-03-21 19:41:33', '2026-03-21 19:41:33'),
(27, 'security_special', '1', 'boolean', 'security', '2026-03-21 19:41:33', '2026-03-21 19:52:30'),
(28, 'security_password_expiry', '90', 'number', 'security', '2026-03-21 19:41:33', '2026-03-21 19:41:33');

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
  `role_id` int(10) UNSIGNED DEFAULT NULL,
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

INSERT INTO `users` (`id`, `full_name`, `first_name`, `last_name`, `email`, `phone`, `alternative_phone`, `date_of_birth`, `gender`, `nationality`, `address`, `city`, `postal_code`, `country`, `preferred_language`, `loyalty_points`, `preferences`, `allergies`, `birthday`, `anniversary`, `role`, `role_id`, `status`, `email_verified`, `email_verification_token`, `email_verification_expires`, `phone_verified`, `notify_email`, `notify_sms`, `notify_promo`, `notify_loyalty`, `avatar`, `member_tier`, `join_date`, `password`, `remember_token`, `token_expires`, `created_at`, `updated_at`, `last_login`) VALUES
(4, 'Dolo dols', 'Dolo', 'dols', 'janzeldol1s@gmail.com', '+639565819961', '+639565819961', '2026-03-16', 'prefer not to say', 'ako ay', 'Sampaloc', 'caloocan city', 'NONE', 'Philippines', 'English', 0, '', '', '2026-03-18', '0000-00-00', 'customer', NULL, 'active', 0, NULL, NULL, 1, 1, 1, 1, 1, NULL, 'platinum', '2026-03-15 08:48:13', '$2y$12$LSPIJZd7kcJxavwyEteiEehuiwbeIZKh1oM1DRXKF2zIuvh5Fsxma', '5cb23af2f6febd413ce82c4e766863f3197872ae273e0c1864b766ef15ae12d7', '2026-04-14 09:58:38', '2026-03-15 08:48:13', '2026-03-19 12:36:36', NULL),
(7, 'Janzel', NULL, NULL, 'janzeldols@gmail.com', '+639565819964', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Philippines', 'English', 0, NULL, NULL, NULL, NULL, 'admin', NULL, 'active', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'bronze', '2026-03-17 02:41:59', '$2y$12$rAvNuAJvNvuKu.h.NhWoJutf4N9ZQ5z9mWFuHQazyx0gROkH9Wr2y', NULL, NULL, '2026-03-17 02:41:59', '2026-03-19 12:36:33', NULL),
(8, 'jzel dols', 'jzel', 'dols', 'janzeldol4@gmail.com', '+639565819969', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Philippines', 'English', 715, NULL, NULL, NULL, NULL, 'admin', 1, 'active', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'bronze', '2026-03-18 01:01:04', '$2y$12$l53nMaAIcd2Nq0NLFfLqwOlm4KP.6cVMjuMw8QQUAZftZT9KnnWQy', NULL, NULL, '2026-03-18 01:01:04', '2026-03-22 08:46:05', '2026-03-22 16:36:57');

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
-- Table structure for table `user_roles`
--

CREATE TABLE `user_roles` (
  `id` int(10) UNSIGNED NOT NULL,
  `role_name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `permissions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`permissions`)),
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_roles`
--

INSERT INTO `user_roles` (`id`, `role_name`, `description`, `permissions`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Administrator', '', '{\"full_access\":true,\"hotel\":true,\"restaurant\":true,\"customer\":true,\"operations\":true,\"reports\":true,\"system\":true}', 1, '2026-03-21 19:41:33', '2026-03-21 20:28:43'),
(2, 'Manager', 'All except system settings', '{\"full_access\": false, \"hotel\": true, \"restaurant\": true, \"customer\": true, \"operations\": true, \"reports\": true, \"system\": false}', 1, '2026-03-21 19:41:33', '2026-03-21 19:41:33'),
(3, 'Front Desk', 'Reservations, check-in/out', '{\"full_access\": false, \"hotel\": true, \"restaurant\": false, \"customer\": true, \"operations\": false, \"reports\": false, \"system\": false}', 1, '2026-03-21 19:41:33', '2026-03-21 19:41:33'),
(4, 'Housekeeping', 'Room status, tasks', '{\"full_access\": false, \"hotel\": true, \"restaurant\": false, \"customer\": false, \"operations\": false, \"reports\": false, \"system\": false}', 1, '2026-03-21 19:41:33', '2026-03-21 19:41:33'),
(5, 'Staff', 'Basic staff access', '{\"full_access\": false, \"hotel\": false, \"restaurant\": true, \"customer\": false, \"operations\": false, \"reports\": false, \"system\": false}', 1, '2026-03-21 19:41:33', '2026-03-21 19:41:33');

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
(4, 'Function Room C', 80, '3rd Floor', 'Ideal for birthday parties and social events', 'Stage, Sound System, Bar Area, Dance Floor', 'available', 8000.00, NULL, '2026-03-17 14:55:50', '2026-03-18 01:11:49'),
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
-- Indexes for table `admin_notifications`
--
ALTER TABLE `admin_notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admin_id` (`admin_id`),
  ADD KEY `is_read` (`is_read`);

--
-- Indexes for table `backup_history`
--
ALTER TABLE `backup_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

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
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_checkin_status` (`check_in`,`status`),
  ADD KEY `idx_promo_code` (`promo_code`),
  ADD KEY `fk_booking_promo_code` (`promo_code_id`);

--
-- Indexes for table `campaigns`
--
ALTER TABLE `campaigns`
  ADD PRIMARY KEY (`id`),
  ADD KEY `status` (`status`),
  ADD KEY `start_date` (`start_date`),
  ADD KEY `end_date` (`end_date`);

--
-- Indexes for table `campaign_redemptions`
--
ALTER TABLE `campaign_redemptions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `campaign_id` (`campaign_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `booking_id` (`booking_id`);

--
-- Indexes for table `current_balance`
--
ALTER TABLE `current_balance`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD KEY `idx_admin_approval` (`admin_approval`),
  ADD KEY `fk_current_balance_approved_by` (`approved_by`);

--
-- Indexes for table `email_templates`
--
ALTER TABLE `email_templates`
  ADD PRIMARY KEY (`id`);

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
  ADD KEY `status` (`status`),
  ADD KEY `idx_created_status` (`created_at`,`status`),
  ADD KEY `idx_food_promo_code` (`promo_code`),
  ADD KEY `fk_food_order_promo_code` (`promo_code_id`);

--
-- Indexes for table `guest_interactions`
--
ALTER TABLE `guest_interactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `admin_id` (`admin_id`),
  ADD KEY `fk_guest_interactions_assigned_to` (`assigned_to`);

--
-- Indexes for table `hr_staff_cache`
--
ALTER TABLE `hr_staff_cache`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `employee_id` (`employee_id`);

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `login_history`
--
ALTER TABLE `login_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `created_at` (`created_at`);

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
-- Indexes for table `promo_codes`
--
ALTER TABLE `promo_codes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `campaign_id` (`campaign_id`),
  ADD KEY `is_active` (`is_active`),
  ADD KEY `valid_to` (`valid_to`);

--
-- Indexes for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `po_number` (`po_number`),
  ADD KEY `supplier_id` (`supplier_id`);

--
-- Indexes for table `purchase_order_items`
--
ALTER TABLE `purchase_order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `po_id` (`po_id`),
  ADD KEY `inventory_id` (`inventory_id`);

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
  ADD KEY `idx_user_payment` (`user_id`,`payment_status`,`status`),
  ADD KEY `idx_date_status` (`reservation_date`,`status`);

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
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_needs_cleaning` (`needs_cleaning`);

--
-- Indexes for table `room_maintenance`
--
ALTER TABLE `room_maintenance`
  ADD PRIMARY KEY (`id`),
  ADD KEY `room_id` (`room_id`),
  ADD KEY `reported_by` (`reported_by`),
  ADD KEY `fk_room_maintenance_assigned_to` (`assigned_to`),
  ADD KEY `idx_status` (`cleaned_at`,`assigned_to`),
  ADD KEY `idx_employee_id` (`employee_id`),
  ADD KEY `idx_hr_employee_id` (`assigned_hr_employee_id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `staff_assignments`
--
ALTER TABLE `staff_assignments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_employee` (`employee_id`);

--
-- Indexes for table `staff_notes`
--
ALTER TABLE `staff_notes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employee_id` (`employee_id`);

--
-- Indexes for table `staff_performance`
--
ALTER TABLE `staff_performance`
  ADD PRIMARY KEY (`id`),
  ADD KEY `staff_id` (`staff_id`),
  ADD KEY `reviewer_id` (`reviewer_id`);

--
-- Indexes for table `staff_status`
--
ALTER TABLE `staff_status`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_date` (`user_id`,`date`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `stock_movements`
--
ALTER TABLE `stock_movements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `inventory_id` (`inventory_id`);

--
-- Indexes for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`),
  ADD KEY `category` (`category`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `phone` (`phone`),
  ADD UNIQUE KEY `email_2` (`email`),
  ADD UNIQUE KEY `phone_2` (`phone`),
  ADD KEY `role_id` (`role_id`);

--
-- Indexes for table `user_roles`
--
ALTER TABLE `user_roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `role_name` (`role_name`);

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
-- AUTO_INCREMENT for table `admin_notifications`
--
ALTER TABLE `admin_notifications`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `backup_history`
--
ALTER TABLE `backup_history`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=85;

--
-- AUTO_INCREMENT for table `campaigns`
--
ALTER TABLE `campaigns`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `campaign_redemptions`
--
ALTER TABLE `campaign_redemptions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `current_balance`
--
ALTER TABLE `current_balance`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=179;

--
-- AUTO_INCREMENT for table `email_templates`
--
ALTER TABLE `email_templates`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `food_orders`
--
ALTER TABLE `food_orders`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `guest_interactions`
--
ALTER TABLE `guest_interactions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `hr_staff_cache`
--
ALTER TABLE `hr_staff_cache`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `login_history`
--
ALTER TABLE `login_history`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `menu_items`
--
ALTER TABLE `menu_items`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=498;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT for table `payment_methods`
--
ALTER TABLE `payment_methods`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `promo_codes`
--
ALTER TABLE `promo_codes`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `purchase_order_items`
--
ALTER TABLE `purchase_order_items`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `redemptions`
--
ALTER TABLE `redemptions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `response_templates`
--
ALTER TABLE `response_templates`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `restaurant_reservations`
--
ALTER TABLE `restaurant_reservations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT for table `restaurant_tables`
--
ALTER TABLE `restaurant_tables`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `review_responses`
--
ALTER TABLE `review_responses`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `rewards`
--
ALTER TABLE `rewards`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `room_maintenance`
--
ALTER TABLE `room_maintenance`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `staff_assignments`
--
ALTER TABLE `staff_assignments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `staff_notes`
--
ALTER TABLE `staff_notes`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `staff_performance`
--
ALTER TABLE `staff_performance`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `staff_status`
--
ALTER TABLE `staff_status`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `stock_movements`
--
ALTER TABLE `stock_movements`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `user_roles`
--
ALTER TABLE `user_roles`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `venues`
--
ALTER TABLE `venues`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `waiting_list`
--
ALTER TABLE `waiting_list`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `backup_history`
--
ALTER TABLE `backup_history`
  ADD CONSTRAINT `fk_backup_history_user` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_booking_promo_code` FOREIGN KEY (`promo_code_id`) REFERENCES `promo_codes` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `campaign_redemptions`
--
ALTER TABLE `campaign_redemptions`
  ADD CONSTRAINT `campaign_redemptions_ibfk_1` FOREIGN KEY (`campaign_id`) REFERENCES `campaigns` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `campaign_redemptions_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `campaign_redemptions_ibfk_3` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `current_balance`
--
ALTER TABLE `current_balance`
  ADD CONSTRAINT `current_balance_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_current_balance_approved_by` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `events`
--
ALTER TABLE `events`
  ADD CONSTRAINT `events_ibfk_1` FOREIGN KEY (`venue_id`) REFERENCES `venues` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `food_orders`
--
ALTER TABLE `food_orders`
  ADD CONSTRAINT `fk_food_order_promo_code` FOREIGN KEY (`promo_code_id`) REFERENCES `promo_codes` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `food_orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `guest_interactions`
--
ALTER TABLE `guest_interactions`
  ADD CONSTRAINT `fk_guest_interactions_assigned_to` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `guest_interactions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `guest_interactions_ibfk_2` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `login_history`
--
ALTER TABLE `login_history`
  ADD CONSTRAINT `fk_login_history_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

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
-- Constraints for table `promo_codes`
--
ALTER TABLE `promo_codes`
  ADD CONSTRAINT `promo_codes_ibfk_1` FOREIGN KEY (`campaign_id`) REFERENCES `campaigns` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  ADD CONSTRAINT `purchase_orders_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `purchase_order_items`
--
ALTER TABLE `purchase_order_items`
  ADD CONSTRAINT `purchase_order_items_ibfk_1` FOREIGN KEY (`po_id`) REFERENCES `purchase_orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `purchase_order_items_ibfk_2` FOREIGN KEY (`inventory_id`) REFERENCES `inventory` (`id`) ON DELETE CASCADE;

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

--
-- Constraints for table `staff_performance`
--
ALTER TABLE `staff_performance`
  ADD CONSTRAINT `staff_performance_ibfk_1` FOREIGN KEY (`staff_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `staff_performance_ibfk_2` FOREIGN KEY (`reviewer_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `staff_status`
--
ALTER TABLE `staff_status`
  ADD CONSTRAINT `staff_status_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `stock_movements`
--
ALTER TABLE `stock_movements`
  ADD CONSTRAINT `stock_movements_ibfk_1` FOREIGN KEY (`inventory_id`) REFERENCES `inventory` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_users_role` FOREIGN KEY (`role_id`) REFERENCES `user_roles` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
