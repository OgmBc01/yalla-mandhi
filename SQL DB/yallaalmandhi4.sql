-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 11, 2026 at 02:30 PM
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
-- Database: `yallaalmandhi`
--

-- --------------------------------------------------------

--
-- Table structure for table `branches`
--

CREATE TABLE `branches` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `address` text NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `opening_hours` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `branches`
--

INSERT INTO `branches` (`id`, `name`, `address`, `phone`, `email`, `opening_hours`, `is_active`, `created_at`) VALUES
(1, 'Al Barsha (Flagship)', 'Shop 12, Al Barsha Mall, Sheikh Mohammed Bin Zayed Road, Dubai, UAE', '+971 4 123 4567', 'albarsha@yallaalmandhi.com', 'Daily: 12:00 PM - 12:00 AM', 1, '2026-02-03 14:04:43'),
(5, 'DIP Branch', 'New office opened', '+971501234567', 'test@example.com', '', 1, '2026-02-06 17:45:13');

-- --------------------------------------------------------

--
-- Table structure for table `catering_inquiries`
--

CREATE TABLE `catering_inquiries` (
  `id` int(11) NOT NULL,
  `customer_name` varchar(100) NOT NULL,
  `customer_email` varchar(100) NOT NULL,
  `customer_phone` varchar(20) NOT NULL,
  `event_type` varchar(50) NOT NULL,
  `event_date` date NOT NULL,
  `number_of_guests` int(11) NOT NULL,
  `service_type` varchar(50) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `status` enum('pending','contacted','booked','cancelled') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `menu_categories`
--

CREATE TABLE `menu_categories` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `menu_categories`
--

INSERT INTO `menu_categories` (`id`, `name`, `description`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 'Mandhi', 'Hello description', 1, 1, '2026-02-05 11:05:55', '2026-02-05 12:37:28'),
(2, 'Grills1', 'Its nice', 1, 2, '2026-02-05 11:05:55', '2026-02-05 14:10:50'),
(3, 'Appetizers', NULL, 1, 3, '2026-02-05 11:05:55', '2026-02-05 11:05:55'),
(7, 'New Cat', 'Testing add', 1, 0, '2026-02-05 12:38:18', '2026-02-05 12:38:18');

-- --------------------------------------------------------

--
-- Table structure for table `menu_items`
--

CREATE TABLE `menu_items` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `category_id` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `is_available` tinyint(1) DEFAULT 1,
  `is_featured` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `menu_items`
--

INSERT INTO `menu_items` (`id`, `name`, `description`, `category_id`, `price`, `image_url`, `is_available`, `is_featured`, `created_at`, `updated_at`) VALUES
(2, 'Chicken Mandhi', 'Juicy chicken marinated in Syrian spices, cooked with fragrant basmati rice. Edited!', 1, 65.00, 'menu_1770306848_7674.jpg', 1, 1, '2026-02-03 14:04:43', '2026-02-05 15:54:08'),
(3, 'Mixed Grill Platter', 'Assortment of grilled lamb chops, chicken tikka, kofta, and shish tawook', 2, 95.00, NULL, 1, 1, '2026-02-03 14:04:43', '2026-02-06 18:57:37'),
(4, 'Chicken Kabsa', 'Fragrant rice with tender chicken, nuts, and authentic Arabic spices', 1, 65.00, 'menu_1770308897_5765.png', 1, 0, '2026-02-03 14:04:43', '2026-02-05 16:28:17'),
(7, 'Testing', 'New Item', 7, 100.00, 'menu_1770306904_7770.jpg', 1, 1, '2026-02-05 15:55:04', '2026-02-05 15:55:04');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `order_number` varchar(20) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `customer_name` varchar(100) NOT NULL,
  `customer_email` varchar(100) DEFAULT NULL,
  `customer_phone` varchar(20) NOT NULL,
  `customer_address` text DEFAULT NULL,
  `order_type` enum('delivery','pickup','dine_in') NOT NULL,
  `branch_id` int(11) DEFAULT 1,
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('pending','confirmed','preparing','ready','delivered','cancelled') DEFAULT 'pending',
  `payment_method` enum('cash','card','online') DEFAULT 'cash',
  `payment_status` enum('pending','paid','failed') DEFAULT 'pending',
  `special_instructions` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `menu_item_id` int(11) DEFAULT NULL,
  `menu_item_name` varchar(100) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `special_instructions` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_logs`
--

CREATE TABLE `password_reset_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `reset_requested_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `reset_completed_at` timestamp NULL DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `password_reset_logs`
--

INSERT INTO `password_reset_logs` (`id`, `user_id`, `reset_requested_at`, `reset_completed_at`, `ip_address`) VALUES
(1, 2, '2026-01-31 17:12:21', NULL, '::1'),
(2, 3, '2026-01-31 17:19:25', NULL, '::1'),
(3, 3, '2026-01-31 17:19:46', NULL, '::1'),
(4, 2, '2026-01-31 17:32:03', NULL, '::1');

-- --------------------------------------------------------

--
-- Table structure for table `promotions`
--

CREATE TABLE `promotions` (
  `id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `subtitle` varchar(200) DEFAULT NULL,
  `description` text NOT NULL,
  `short_description` text DEFAULT NULL,
  `badge_text` varchar(50) DEFAULT 'Limited Offer',
  `badge_color` varchar(50) DEFAULT 'var(--color-red)',
  `image_url` varchar(255) DEFAULT NULL,
  `original_price` decimal(10,2) DEFAULT NULL,
  `offer_price` decimal(10,2) NOT NULL,
  `discount_percent` decimal(5,2) DEFAULT NULL,
  `currency` varchar(10) DEFAULT 'AED',
  `valid_from` date DEFAULT NULL,
  `valid_until` date DEFAULT NULL,
  `time_slot` varchar(100) DEFAULT NULL,
  `requirements` text DEFAULT NULL,
  `min_persons` int(3) DEFAULT NULL,
  `max_persons` int(3) DEFAULT NULL,
  `min_order_amount` decimal(10,2) DEFAULT NULL,
  `offer_type` enum('family','business','early_bird','birthday','takeaway','student','seasonal','other') DEFAULT 'other',
  `is_active` tinyint(1) DEFAULT 1,
  `is_featured` tinyint(1) DEFAULT 0,
  `is_highlighted` tinyint(1) DEFAULT 0,
  `cta_text` varchar(100) DEFAULT 'Book Now',
  `cta_link` varchar(255) DEFAULT 'contact.php',
  `cta_icon` varchar(50) DEFAULT 'bi-calendar-check',
  `display_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reservations`
--

CREATE TABLE `reservations` (
  `id` int(11) NOT NULL,
  `customer_name` varchar(100) NOT NULL,
  `customer_email` varchar(100) NOT NULL,
  `customer_phone` varchar(20) NOT NULL,
  `reservation_date` date NOT NULL,
  `reservation_time` time NOT NULL,
  `branch_name` varchar(256) NOT NULL,
  `number_of_guests` int(11) NOT NULL,
  `branch_id` int(11) DEFAULT 1,
  `special_requests` text DEFAULT NULL,
  `status` enum('pending','confirmed','cancelled','completed') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reservations`
--

INSERT INTO `reservations` (`id`, `customer_name`, `customer_email`, `customer_phone`, `reservation_date`, `reservation_time`, `branch_name`, `number_of_guests`, `branch_id`, `special_requests`, `status`, `created_at`, `updated_at`) VALUES
(6, 'Higg', 'hi@email.com', '+97123456789', '2026-02-12', '11:30:00', '', 10, 1, 'Hello new test.', 'pending', '2026-02-05 17:12:20', '2026-02-06 13:24:22');

-- --------------------------------------------------------

--
-- Table structure for table `shifts`
--

CREATE TABLE `shifts` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `shift_date` date NOT NULL,
  `shift_type` enum('morning','afternoon','evening','night') NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `location` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `shifts`
--

INSERT INTO `shifts` (`id`, `employee_id`, `shift_date`, `shift_type`, `start_time`, `end_time`, `location`, `notes`, `is_active`, `created_at`, `updated_at`) VALUES
(2, 1, '2026-02-10', 'morning', '09:00:00', '17:00:00', 'Takeaway Counter', 'New shift.', 1, '2026-02-10 09:11:03', '2026-02-10 09:11:03');

-- --------------------------------------------------------

--
-- Table structure for table `testimonials`
--

CREATE TABLE `testimonials` (
  `id` int(11) NOT NULL,
  `customer_name` varchar(100) NOT NULL,
  `customer_image` varchar(255) DEFAULT NULL,
  `rating` int(11) DEFAULT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `review` text NOT NULL,
  `is_approved` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `testimonials`
--

INSERT INTO `testimonials` (`id`, `customer_name`, `customer_image`, `rating`, `review`, `is_approved`, `created_at`) VALUES
(1, 'New22', '1770401004_69862cec71ac5.png', 1, 'I like the restaurant.', 1, '2026-02-06 18:03:24'),
(2, 'New', '1770401143_69862d77a522d.png', 1, 'I like the restaurant.', 1, '2026-02-06 18:05:43'),
(3, 'New', '1770401146_69862d7ac6473.png', 1, 'I like the restaurant.', 0, '2026-02-06 18:05:46'),
(5, 'New11111', '1770401387_69862e6b1f705.png', 5, 'I like the restaurant.', 0, '2026-02-06 18:09:47');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `employee_id` varchar(50) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `role` enum('customer','admin','super-admin','employee') DEFAULT 'customer',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `last_login` timestamp NULL DEFAULT NULL,
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_token_expiry` timestamp NULL DEFAULT NULL,
  `address` text DEFAULT NULL,
  `preferred_branch` int(11) DEFAULT 1,
  `loyalty_points` int(11) DEFAULT 0,
  `last_order_date` date DEFAULT NULL,
  `position` varchar(100) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `salary` decimal(10,2) DEFAULT 0.00,
  `hire_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `employee_id`, `email`, `password_hash`, `full_name`, `phone`, `role`, `is_active`, `created_at`, `updated_at`, `last_login`, `reset_token`, `reset_token_expiry`, `address`, `preferred_branch`, `loyalty_points`, `last_order_date`, `position`, `department`, `salary`, `hire_date`) VALUES
(1, 'admin', NULL, 'admin@yallaalmandhi.com', '$2y$10$YourHashedPasswordHere', 'System Administrator', NULL, 'admin', 1, '2026-01-31 13:34:06', '2026-01-31 13:34:06', NULL, NULL, NULL, NULL, 1, 0, NULL, NULL, NULL, 0.00, NULL),
(2, 'New', NULL, 'abm@gmail.com', '$2y$10$lwUpy9pmKdGAIGF6IaEcPeKUMMouU45BjK02CstIo/Dp24WuBpSJq', 'Abdulla', '+971345264456', 'super-admin', 1, '2026-01-31 16:44:08', '2026-02-10 10:26:11', '2026-02-10 10:26:11', '06eb3d9d4bed5d240d0364b90f18978fadb719c2a4fdf00971f847bacf6a06fc', '2026-01-31 15:32:03', NULL, 1, 0, NULL, NULL, NULL, 0.00, NULL),
(3, 'deleted_user_1770383913_3', NULL, 'deleted_1770383913_3@deleted.com', '', 'Deleted User', '', 'customer', 1, '2026-01-31 17:19:01', '2026-02-06 13:30:48', NULL, NULL, NULL, '', 1, 0, NULL, NULL, NULL, 0.00, NULL),
(4, 'ABM', NULL, 'abdaullahbalam@gmail.com', '$2y$10$S3mCZCYHzqUiIp.L/G/dQeqk2A34T.Y25myFeI/5Ltth/xPFRegbG', 'Doe', '+971501234567', 'customer', 0, '2026-01-31 18:06:54', '2026-02-06 13:30:58', '2026-01-31 18:06:54', NULL, NULL, '', 1, 0, NULL, NULL, NULL, 0.00, NULL),
(5, 'Test', '', 'test@example.com', '$2y$10$j7sIFix7TgNBTbrQ.PvCuu6ihow.RUbNuCtHSdp0hGpPzBZyaFtki', 'Test User22', '+971582551785', 'super-admin', 1, '2026-02-03 15:11:27', '2026-02-07 17:19:41', '2026-02-03 18:51:27', NULL, NULL, 'DIP Building, Somewhere Street, Earth.', 1, 0, NULL, '', '', 0.00, '2026-02-07'),
(6, 'Joiner', NULL, 'test1@example.com', '$2y$10$llTzSu8l5sHzuUxQOvatz.ebGSQnx7Pk6Zyw2.EUxRdQ8gSTSaW3O', 'Test Userr', '+971345264456', 'customer', 1, '2026-02-06 12:06:02', '2026-02-06 12:06:02', NULL, NULL, NULL, 'DIP', 1, 2, NULL, NULL, NULL, 0.00, NULL),
(7, 'Yo', NULL, 'jose95@example.com', '$2y$10$D1SWZJNVmEoQT.r.DBTyvel5psVjkyRWlHJj9m/EPZye68J/bOwhC', 'Doe', '+971501234567', 'customer', 1, '2026-02-06 12:10:19', '2026-02-06 12:10:19', NULL, NULL, NULL, 'Dip 2', 1, 2, NULL, NULL, NULL, 0.00, NULL),
(8, 'Doo', NULL, 'jose953@example.com', '$2y$10$4HiL0OuKfkn/DK4znzSmOeVWTQtWjUFpWEuctNX8RJrnbH7yMAYIa', 'John', '+971501234567', 'customer', 1, '2026-02-06 12:13:09', '2026-02-06 12:13:09', NULL, NULL, NULL, 'Dip 2', 1, 2, NULL, NULL, NULL, 0.00, NULL),
(9, '1770383963_9', NULL, '1770383963_9@deleted.com', '', 'Our User1', '', 'customer', 1, '2026-02-06 12:20:24', '2026-02-06 13:28:40', NULL, NULL, NULL, '', 1, 0, NULL, NULL, NULL, 0.00, NULL),
(10, '1770383929_10', NULL, 'deleted_1770383929_10@deleted.com', '', 'Once Deleted', '', 'customer', 1, '2026-02-06 12:32:23', '2026-02-06 13:26:03', NULL, NULL, NULL, 'Some address.', 1, 0, NULL, NULL, NULL, 0.00, NULL),
(11, 'Admin1', NULL, 'jose9r5@example.com', '$2y$10$KiOBX4H0jhTVOD//HqdicO9ZRtRLpKq5o9HOf6HU4m.OuyBxUhRYe', 'Danir', '+971345264456', 'customer', 1, '2026-02-06 13:21:58', '2026-02-06 13:21:58', NULL, NULL, NULL, 'New location.', 1, 7, NULL, NULL, NULL, 0.00, NULL),
(12, 'New1', NULL, 'jose95@exampl1e.com', '$2y$10$7LdKzcl5l6K.CpOm7MAZF.o2SdoSbdH1fcvvSiB06fwbPhnMH2kkW', 'Test Userr', '+971345264456', 'customer', 1, '2026-02-07 15:30:25', '2026-02-07 15:30:25', NULL, NULL, NULL, '', 1, 0, NULL, NULL, NULL, 0.00, NULL),
(14, 'R', '111', 'asmi@email.com', '$2y$10$AUAKwHEF2cruJv9r7YO3VeqDuWf3U04K2r4F4SYR8c8he1npADZG6', 'Ashmita', '+971345264456', 'employee', 1, '2026-02-07 17:28:53', '2026-02-07 17:31:23', NULL, NULL, NULL, 'Something', 1, 0, NULL, 'Cachier', 'Administration', 0.00, '2026-02-01');

-- --------------------------------------------------------

--
-- Table structure for table `user_sessions`
--

CREATE TABLE `user_sessions` (
  `session_id` varchar(128) NOT NULL,
  `user_id` int(11) NOT NULL,
  `login_time` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_activity` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_sessions`
--

INSERT INTO `user_sessions` (`session_id`, `user_id`, `login_time`, `last_activity`, `ip_address`, `user_agent`) VALUES
('19k12eg374lg3su5p319edus0l', 2, '2026-02-06 13:15:00', '2026-02-06 13:15:00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36'),
('c3lm8urjfo8qi9kpl1b3p898li', 2, '2026-02-03 14:53:56', '2026-02-03 14:53:56', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36'),
('c3m6ii021fr9qgekach53ko0j8', 2, '2026-02-06 17:12:31', '2026-02-10 10:26:11', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36'),
('jjvljkq80d4kksklr51mp16i6s', 5, '2026-02-03 15:18:20', '2026-02-03 15:18:20', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36'),
('jsjet30qib08ldle5ekcniqj4l', 2, '2026-02-03 15:01:52', '2026-02-03 15:01:52', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36'),
('mar5iope6o4u393t0hqls82o91', 2, '2026-02-03 15:04:12', '2026-02-03 15:04:12', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36'),
('sh7008oh0ied7r7ainctou8pq5', 5, '2026-02-03 15:21:10', '2026-02-03 15:21:10', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36'),
('sqj6p9n8uhulocut2uc0pbi46q', 2, '2026-02-03 15:06:39', '2026-02-03 15:06:39', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `branches`
--
ALTER TABLE `branches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `catering_inquiries`
--
ALTER TABLE `catering_inquiries`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `menu_categories`
--
ALTER TABLE `menu_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_category_name` (`name`);

--
-- Indexes for table `menu_items`
--
ALTER TABLE `menu_items`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_number` (`order_number`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `branch_id` (`branch_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `order_items_ibfk_1` (`menu_item_id`);

--
-- Indexes for table `password_reset_logs`
--
ALTER TABLE `password_reset_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `promotions`
--
ALTER TABLE `promotions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `reservations`
--
ALTER TABLE `reservations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `branch_id` (`branch_id`);

--
-- Indexes for table `shifts`
--
ALTER TABLE `shifts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_shift_date` (`shift_date`),
  ADD KEY `idx_employee_shift` (`employee_id`,`shift_date`);

--
-- Indexes for table `testimonials`
--
ALTER TABLE `testimonials`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_username` (`username`),
  ADD KEY `idx_role` (`role`),
  ADD KEY `idx_is_active` (`is_active`),
  ADD KEY `preferred_branch` (`preferred_branch`);

--
-- Indexes for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD PRIMARY KEY (`session_id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `branches`
--
ALTER TABLE `branches`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `catering_inquiries`
--
ALTER TABLE `catering_inquiries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `menu_categories`
--
ALTER TABLE `menu_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `menu_items`
--
ALTER TABLE `menu_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `password_reset_logs`
--
ALTER TABLE `password_reset_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `promotions`
--
ALTER TABLE `promotions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `reservations`
--
ALTER TABLE `reservations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `shifts`
--
ALTER TABLE `shifts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `testimonials`
--
ALTER TABLE `testimonials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`menu_item_id`) REFERENCES `menu_items` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`menu_item_id`) REFERENCES `menu_items` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `password_reset_logs`
--
ALTER TABLE `password_reset_logs`
  ADD CONSTRAINT `password_reset_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reservations`
--
ALTER TABLE `reservations`
  ADD CONSTRAINT `reservations_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `shifts`
--
ALTER TABLE `shifts`
  ADD CONSTRAINT `shifts_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`preferred_branch`) REFERENCES `branches` (`id`);

--
-- Constraints for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD CONSTRAINT `user_sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
