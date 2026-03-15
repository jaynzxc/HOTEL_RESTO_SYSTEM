-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3307
-- Generation Time: Mar 15, 2026 at 04:34 PM
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
  `check_out` date NOT NULL,
  `nights` int(11) NOT NULL,
  `room_id` varchar(10) DEFAULT NULL,
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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `booking_reference`, `user_id`, `guest_first_name`, `guest_last_name`, `guest_email`, `guest_phone`, `booking_type`, `check_in`, `check_out`, `nights`, `room_id`, `room_name`, `room_price`, `adults`, `children`, `subtotal`, `tax`, `total_amount`, `status`, `payment_status`, `payment_method`, `payment_date`, `special_requests`, `payment_id`, `created_at`, `updated_at`) VALUES
(1, 'HOT-202603-51A05E', 4, 'Dolo', 'dols', 'janzeldols@gmail.com', '+639565819961', 'hotel', '2026-03-16', '2026-03-18', 2, '204', 'Family Room', 5500.00, 2, 2, 11000.00, 1320.00, 12320.00, 'pending', 'unpaid', NULL, NULL, 'hi there', NULL, '2026-03-15 15:26:45', '2026-03-15 15:26:45');
(6, 'roldan rexis tiu', 'roldan rexis', 'tiu', 'roldantiu@gmail.com', '0000000000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Philippines', 'English', 0, 'customer', 'active', 0, NULL, NULL, 0, 1, 1, 0, 1, NULL, 'bronze', '2026-03-15 16:14:49', '$2y$12$PLACEHOLDER_HASH_FOR_Testing123', NULL, NULL, '2026-03-15 16:14:49', '2026-03-15 16:14:49'),
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
  `transaction_id` varchar(100) DEFAULT NULL,
  `payment_date` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `total_amount` decimal(10,2) DEFAULT 0.00,
  `status` enum('pending','confirmed','cancelled','completed') NOT NULL DEFAULT 'pending',
  `payment_status` enum('unpaid','paid','refunded') NOT NULL DEFAULT 'unpaid',
  `payment_method` varchar(50) DEFAULT NULL,
  `payment_id` int(10) UNSIGNED DEFAULT NULL,
  `payment_date` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`id`, `user_id`, `experience`, `rating`, `review_text`, `detail`, `icon`, `created_at`) VALUES
(2, 4, 'Room service', 5, 'adsdsad', 'a', 'fa-pen', '2026-03-15 09:29:12'),
(3, 4, 'Restaurant · Azure', 3, 'hid', 'asdsa', 'fa-pen', '2026-03-15 09:31:38');

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
('201', 'Deluxe Twin', NULL, 4200.00, '2 single beds', 'city view', 'Free WiFi, TV, Aircon', 2, NULL, 1, '2026-03-15 15:19:38'),
('202', 'Ocean Suite', NULL, 6900.00, '1 king bed', 'ocean view', 'Jacuzzi, Free WiFi, Mini Bar', 3, NULL, 1, '2026-03-15 15:19:38'),
('203', 'Superior Double', NULL, 3500.00, 'double bed', 'city view', 'Free WiFi, TV', 2, NULL, 1, '2026-03-15 15:19:38'),
('204', 'Family Room', NULL, 5500.00, '2 queen beds', 'pool view', 'Free WiFi, TV, Mini Fridge', 4, NULL, 1, '2026-03-15 15:19:38'),
('205', 'Executive Suite', NULL, 8500.00, '1 king bed', 'ocean view', 'Jacuzzi, Living Area, Free WiFi', 2, NULL, 1, '2026-03-15 15:19:38');

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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `first_name`, `last_name`, `email`, `phone`, `alternative_phone`, `date_of_birth`, `gender`, `nationality`, `address`, `city`, `postal_code`, `country`, `preferred_language`, `loyalty_points`, `role`, `status`, `email_verified`, `email_verification_token`, `email_verification_expires`, `phone_verified`, `notify_email`, `notify_sms`, `notify_promo`, `notify_loyalty`, `avatar`, `member_tier`, `join_date`, `password`, `remember_token`, `token_expires`, `created_at`, `updated_at`) VALUES
(4, 'Dolo dols', 'Dolo', 'dols', 'janzeldols@gmail.com', '+639565819961', '+639565819961', '2026-03-16', 'prefer not to say', 'ako ay', 'Sampaloc', 'caloocan city', 'NONE', 'Philippines', 'English', 3830, 'customer', 'active', 0, NULL, NULL, 1, 1, 1, 1, 1, NULL, 'platinum', '2026-03-15 08:48:13', '$2y$12$LSPIJZd7kcJxavwyEteiEehuiwbeIZKh1oM1DRXKF2zIuvh5Fsxma', '5cb23af2f6febd413ce82c4e766863f3197872ae273e0c1864b766ef15ae12d7', '2026-04-14 09:58:38', '2026-03-15 08:48:13', '2026-03-15 15:26:45'),
(5, 'Janzel', 'Janzel', '', 'janzeldols1@gmail.com', '+639565819962', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Philippines', 'English', 0, 'customer', 'active', 0, NULL, NULL, 1, 1, 1, 0, 1, NULL, 'bronze', '2026-03-15 09:44:16', '$2y$12$u0dSR8ysUZ8qE8sWigLBo.eCHxREWFLVs4w5il8iWAE71YcModD3.', NULL, NULL, '2026-03-15 09:44:16', '2026-03-15 15:08:52');

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
  ADD KEY `check_in` (`check_in`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `payment_reference` (`payment_reference`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `booking_id` (`booking_id`),
  ADD KEY `booking_type` (`booking_type`);

--
-- Indexes for table `redemptions`
--
ALTER TABLE `redemptions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `restaurant_reservations`
--
ALTER TABLE `restaurant_reservations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `reservation_reference` (`reservation_reference`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `reservation_date` (`reservation_date`),
  ADD KEY `status` (`status`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `rooms`
--
ALTER TABLE `rooms`
  ADD PRIMARY KEY (`id`);

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
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `redemptions`
--
ALTER TABLE `redemptions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `restaurant_reservations`
--
ALTER TABLE `restaurant_reservations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

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
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- Table structure for table `hotel_bookings`
--

CREATE TABLE `hotel_bookings` (
  `booking_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `booking_reference` varchar(50) NOT NULL,
  `room_type` varchar(100) NOT NULL,
  `room_number` varchar(20) DEFAULT NULL,
  `check_in_date` date NOT NULL,
  `check_out_date` date NOT NULL,
  `number_of_guests` int(11) NOT NULL DEFAULT 1,
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `special_requests` text DEFAULT NULL,
  `booking_status` enum('pending','confirmed','checked_in','checked_out','cancelled') NOT NULL DEFAULT 'pending',
  `payment_status` enum('pending','completed','refunded','failed') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hotel_bookings`
--

INSERT INTO `hotel_bookings` (`booking_id`, `user_id`, `booking_reference`, `room_type`, `room_number`, `check_in_date`, `check_out_date`, `number_of_guests`, `total_amount`, `special_requests`, `booking_status`, `payment_status`, `created_at`, `updated_at`) VALUES
(1, 6, 'HL-ROLDAN-001', 'Deluxe Room', '305', '2026-03-20', '2026-03-22', 2, 6500.00, 'High floor room if possible.', 'cancelled', 'completed', '2026-03-15 16:16:42', '2026-03-15 17:03:12'),
(2, 6, 'HL-ROLDAN-PEND-001', 'Deluxe Room', '305', '2026-03-25', '2026-03-27', 2, 6500.00, 'Late check-in around 9 PM.', 'pending', 'pending', '2026-03-15 17:01:38', '2026-03-15 17:01:38');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `hotel_bookings`
--
ALTER TABLE `hotel_bookings`
  ADD PRIMARY KEY (`booking_id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `hotel_bookings`
--
ALTER TABLE `hotel_bookings`
  MODIFY `booking_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `hotel_bookings`
--
ALTER TABLE `hotel_bookings`
  ADD CONSTRAINT `hotel_bookings_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
