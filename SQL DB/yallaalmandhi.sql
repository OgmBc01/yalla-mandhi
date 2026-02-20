-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: mysql
-- Generation Time: Feb 18, 2026 at 05:48 PM
-- Server version: 8.0.45
-- PHP Version: 8.3.26

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
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `id` int NOT NULL,
  `shift_id` int NOT NULL,
  `employee_id` int NOT NULL,
  `attendance_date` date NOT NULL,
  `check_in_time` datetime DEFAULT NULL,
  `check_out_time` datetime DEFAULT NULL,
  `status` enum('present','absent','late','half_day','leave','holiday') DEFAULT 'absent',
  `late_minutes` int DEFAULT '0',
  `overtime_minutes` int DEFAULT '0',
  `notes` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `action_type` varchar(50) NOT NULL,
  `entity_type` varchar(50) NOT NULL,
  `entity_id` int NOT NULL,
  `old_value` text,
  `new_value` text,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `branches`
--

CREATE TABLE `branches` (
  `id` int NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `address` text COLLATE utf8mb4_general_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `opening_hours` text COLLATE utf8mb4_general_ci,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
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
  `id` int NOT NULL,
  `customer_name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `customer_email` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `customer_phone` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `event_type` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `event_date` date NOT NULL,
  `number_of_guests` int NOT NULL,
  `service_type` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `message` text COLLATE utf8mb4_general_ci,
  `status` enum('pending','contacted','booked','cancelled') COLLATE utf8mb4_general_ci DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inventory_items`
--

CREATE TABLE `inventory_items` (
  `id` int NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `unit_of_measure` varchar(20) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `quantity_available` decimal(10,2) NOT NULL DEFAULT '0.00',
  `total_cost` decimal(10,2) NOT NULL DEFAULT '0.00',
  `supplier` varchar(100) DEFAULT NULL,
  `purchase_date` date DEFAULT NULL,
  `min_stock_level` decimal(10,2) DEFAULT '0.00',
  `max_stock_level` decimal(10,2) DEFAULT '0.00',
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inventory_transactions`
--

CREATE TABLE `inventory_transactions` (
  `id` int NOT NULL,
  `inventory_item_id` int NOT NULL,
  `quantity_added` decimal(10,2) DEFAULT '0.00',
  `quantity_used` decimal(10,2) DEFAULT '0.00',
  `transaction_type` enum('purchase','usage','adjustment','return') NOT NULL,
  `reference_id` int DEFAULT NULL COMMENT 'Order ID for usage transactions',
  `reference_type` varchar(20) DEFAULT NULL COMMENT 'orders, adjustments, etc.',
  `notes` text,
  `created_by` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `menu_categories`
--

CREATE TABLE `menu_categories` (
  `id` int NOT NULL,
  `name` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `description` text COLLATE utf8mb4_general_ci,
  `is_active` tinyint(1) DEFAULT '1',
  `sort_order` int DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `menu_categories`
--

INSERT INTO `menu_categories` (`id`, `name`, `description`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 'Mandhi', 'Hello description', 1, 1, '2026-02-05 11:05:55', '2026-02-05 12:37:28'),
(2, 'Grills1', 'Its nice', 1, 2, '2026-02-05 11:05:55', '2026-02-05 14:10:50'),
(3, 'Appetizers', NULL, 1, 3, '2026-02-05 11:05:55', '2026-02-05 11:05:55'),
(7, 'New Cat', 'Testing add', 1, 0, '2026-02-05 12:38:18', '2026-02-05 12:38:18'),
(8, 'Beverage', '', 1, 0, '2026-02-17 16:01:37', '2026-02-17 16:01:37'),
(9, 'Sweets', '', 1, 0, '2026-02-17 16:05:32', '2026-02-17 16:05:32');

-- --------------------------------------------------------

--
-- Table structure for table `menu_items`
--

CREATE TABLE `menu_items` (
  `id` int NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `description` text COLLATE utf8mb4_general_ci,
  `category_id` int NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `image_url` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `is_available` tinyint(1) DEFAULT '1',
  `is_featured` tinyint(1) DEFAULT '0',
  `available_quantity` int DEFAULT NULL,
  `is_daily_limited` tinyint(1) DEFAULT '0',
  `auto_unavailable` tinyint(1) DEFAULT '1',
  `track_inventory` tinyint(1) DEFAULT '0',
  `inventory_item_id` int DEFAULT NULL,
  `inventory_quantity_per_unit` decimal(10,2) DEFAULT '1.00',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `menu_items`
--

INSERT INTO `menu_items` (`id`, `name`, `description`, `category_id`, `price`, `image_url`, `is_available`, `is_featured`, `available_quantity`, `is_daily_limited`, `auto_unavailable`, `track_inventory`, `inventory_item_id`, `inventory_quantity_per_unit`, `created_at`, `updated_at`) VALUES
(2, 'Chicken Mandhi', 'Juicy chicken marinated in Syrian spices, cooked with fragrant basmati rice. Edited!', 1, 65.00, 'menu_1770306848_7674.jpg', 1, 1, NULL, 0, 1, 0, NULL, 1.00, '2026-02-03 14:04:43', '2026-02-05 15:54:08'),
(3, 'Mixed Grill Platter', 'Assortment of grilled lamb chops, chicken tikka, kofta, and shish tawook', 2, 95.00, NULL, 1, 1, NULL, 0, 1, 0, NULL, 1.00, '2026-02-03 14:04:43', '2026-02-06 18:57:37'),
(4, 'Chicken Kabsa', 'Fragrant rice with tender chicken, nuts, and authentic Arabic spices', 1, 65.00, 'menu_1770308897_5765.png', 1, 0, NULL, 0, 1, 0, NULL, 1.00, '2026-02-03 14:04:43', '2026-02-05 16:28:17'),
(7, 'Testing', 'New Item', 7, 100.00, 'menu_1770306904_7770.jpg', 1, 1, NULL, 0, 1, 0, NULL, 1.00, '2026-02-05 15:55:04', '2026-02-05 15:55:04'),
(8, 'Small Water', 'Small Masafi bottled water', 8, 5.00, 'menu_1771344153_3985.png', 1, 0, NULL, 0, 1, 0, NULL, 1.00, '2026-02-17 16:02:33', '2026-02-17 16:02:33'),
(9, 'Kunafa', '', 9, 15.00, 'menu_1771344378_3587.png', 1, 0, NULL, 0, 1, 0, NULL, 1.00, '2026-02-17 16:06:18', '2026-02-17 16:06:18');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int NOT NULL,
  `order_number` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `invoice_number` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `customer_id` int DEFAULT NULL,
  `customer_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `customer_name_snapshot` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `customer_email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `customer_phone` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `customer_phone_snapshot` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `customer_address` text COLLATE utf8mb4_unicode_ci,
  `delivery_address_snapshot` text COLLATE utf8mb4_unicode_ci,
  `order_type` enum('delivery','pickup','dine_in') COLLATE utf8mb4_unicode_ci NOT NULL,
  `delivery_source` enum('internal','noon','deliveroo','keeta','smile') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `table_number` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `branch_id` int DEFAULT '1',
  `subtotal` decimal(10,2) NOT NULL DEFAULT '0.00',
  `discount_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `tax_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `delivery_fee` decimal(10,2) NOT NULL DEFAULT '0.00',
  `total_amount` decimal(10,2) NOT NULL,
  `item_count` int DEFAULT NULL,
  `order_status` enum('draft','pending','confirmed','in_preparation','ready','out_for_delivery','completed','cancelled','refunded','closed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `payment_method` enum('cash','card','online') COLLATE utf8mb4_unicode_ci DEFAULT 'cash',
  `payment_status` enum('pending','paid','failed') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `payment_reference` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_draft` tinyint(1) DEFAULT '0',
  `punched_by_admin_id` int DEFAULT NULL,
  `closed_by_admin_id` int DEFAULT NULL,
  `closed_at` timestamp NULL DEFAULT NULL,
  `last_updated_by` int DEFAULT NULL,
  `special_instructions` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `num_customers` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int NOT NULL,
  `order_id` int NOT NULL,
  `menu_item_id` int DEFAULT NULL,
  `item_name_snapshot` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `unit_price_snapshot` decimal(10,2) NOT NULL,
  `menu_item_name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `quantity` int NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `special_instructions` text COLLATE utf8mb4_general_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_logs`
--

CREATE TABLE `password_reset_logs` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `reset_requested_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `reset_completed_at` timestamp NULL DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_general_ci DEFAULT NULL
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
-- Table structure for table `pos_order_drafts`
--

CREATE TABLE `pos_order_drafts` (
  `id` varchar(32) NOT NULL,
  `user_id` int NOT NULL,
  `data` json NOT NULL,
  `is_deleted` tinyint(1) DEFAULT '0',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `restored_at` timestamp NULL DEFAULT NULL,
  `updated_at` datetime NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `pos_order_drafts`
--

INSERT INTO `pos_order_drafts` (`id`, `user_id`, `data`, `is_deleted`, `deleted_at`, `restored_at`, `updated_at`, `created_at`) VALUES
('ORD1771355885854', 2, '{\"id\": \"ORD1771355885854\", \"type\": \"pickup\", \"items\": [{\"id\": 8, \"qty\": 2, \"name\": \"Small Water\", \"price\": 5}, {\"id\": 9, \"qty\": 5, \"name\": \"Kunafa\", \"price\": 15}], \"status\": \"on_hold\", \"customer\": {\"name\": \"rrrr\", \"phone\": \"66666\", \"address\": \"\"}, \"delivery_source\": \"internal\"}', 0, NULL, NULL, '2026-02-18 17:46:01', '2026-02-18 13:03:48'),
('ORD1771355896711', 2, '{\"id\": \"ORD1771355896711\", \"type\": \"pickup\", \"items\": [{\"id\": 8, \"qty\": 1, \"name\": \"Small Water\", \"price\": 5}, {\"id\": 7, \"qty\": 1, \"name\": \"Testing\", \"price\": 100}, {\"id\": 2, \"qty\": 1, \"name\": \"Chicken Mandhi\", \"price\": 65}], \"customer\": {\"name\": \"rrrr\", \"phone\": \"66666\", \"address\": \"\"}, \"delivery_source\": \"internal\"}', 0, NULL, NULL, '2026-02-18 17:46:01', '2026-02-18 13:03:48'),
('ORD1771355951093', 2, '{\"id\": \"ORD1771355951093\", \"type\": \"dine_in\", \"items\": [{\"id\": 8, \"qty\": 1, \"name\": \"Small Water\", \"price\": 5}], \"customer\": {\"name\": \"ttttt\", \"phone\": \"777777\", \"address\": \"\"}, \"delivery_source\": \"internal\"}', 0, NULL, NULL, '2026-02-18 17:46:01', '2026-02-18 13:03:48'),
('ORD1771356002164', 2, '{\"id\": \"ORD1771356002164\", \"type\": \"dine_in\", \"items\": [], \"notes\": [{\"text\": \"He is my friend\", \"timestamp\": \"2026-02-18T02:02:10.740Z\"}], \"status\": \"cancelled\", \"customer\": {\"name\": \"ttttt\", \"phone\": \"777777\", \"address\": \"\"}, \"is_deleted\": 1, \"delivery_source\": \"internal\"}', 0, NULL, '2026-02-18 02:45:30', '2026-02-18 17:46:01', '2026-02-18 13:03:48'),
('ORD1771356249445', 2, '{\"id\": \"ORD1771356249445\", \"type\": \"dine_in\", \"items\": [], \"customer\": {\"name\": \"eeee\", \"phone\": \"6666\", \"address\": \"\"}, \"is_deleted\": 1, \"delivery_source\": \"internal\"}', 0, NULL, '2026-02-18 02:46:08', '2026-02-18 17:46:01', '2026-02-18 13:03:48'),
('ORD1771356253572', 2, '{\"id\": \"ORD1771356253572\", \"type\": \"dine_in\", \"items\": [{\"id\": 8, \"qty\": 6, \"name\": \"Small Water\", \"price\": 5}], \"customer\": {\"name\": \"eeee\", \"phone\": \"6666\", \"address\": \"\"}, \"delivery_source\": \"internal\"}', 0, NULL, NULL, '2026-02-18 17:46:01', '2026-02-18 13:03:48'),
('ORD1771356301625', 2, '{\"id\": \"ORD1771356301625\", \"type\": \"pickup\", \"items\": [], \"customer\": {\"name\": \"\", \"phone\": \"\", \"address\": \"\"}, \"is_deleted\": 1, \"delivery_source\": \"internal\"}', 0, NULL, '2026-02-18 02:46:27', '2026-02-18 17:46:01', '2026-02-18 13:03:48'),
('ORD1771356398202', 2, '{\"id\": \"ORD1771356398202\", \"type\": \"pickup\", \"items\": [{\"id\": 9, \"qty\": 6, \"name\": \"Kunafa\", \"price\": 15}], \"customer\": {\"name\": \"Otaksi Clients\", \"phone\": \"5555555\", \"address\": \"\"}, \"is_deleted\": 1, \"delivery_source\": \"internal\"}', 0, NULL, '2026-02-18 02:54:12', '2026-02-18 17:46:01', '2026-02-18 13:03:48'),
('ORD1771356436457', 2, '{\"id\": \"ORD1771356436457\", \"type\": \"pickup\", \"items\": [{\"id\": 9, \"qty\": 3, \"name\": \"Kunafa\", \"price\": 15}], \"customer\": {\"name\": \"Otaksi Clients\", \"phone\": \"5555555\", \"address\": \"\"}, \"delivery_source\": \"internal\"}', 0, NULL, NULL, '2026-02-18 17:46:01', '2026-02-18 13:03:48'),
('ORD1771356534938', 2, '{\"id\": \"ORD1771356534938\", \"type\": \"pickup\", \"items\": [{\"id\": 8, \"qty\": 1, \"name\": \"Small Water\", \"price\": 5}], \"customer\": {\"name\": \"Otaksi Clients\", \"phone\": \"5555555\", \"address\": \"\"}, \"delivery_source\": \"internal\"}', 0, NULL, NULL, '2026-02-18 17:46:01', '2026-02-18 13:03:48'),
('ORD1771356549617', 2, '{\"id\": \"ORD1771356549617\", \"type\": \"delivery\", \"items\": [{\"id\": 3, \"qty\": 1, \"name\": \"Mixed Grill Platter\", \"price\": 95}, {\"id\": 4, \"qty\": 1, \"name\": \"Chicken Kabsa\", \"price\": 65}, {\"id\": 2, \"qty\": 1, \"name\": \"Chicken Mandhi\", \"price\": 65}, {\"id\": 9, \"qty\": 1, \"name\": \"Kunafa\", \"price\": 15}, {\"id\": 7, \"qty\": 1, \"name\": \"Testing\", \"price\": 100}], \"customer\": {\"name\": \"Otaksi Clients\", \"phone\": \"5555555\", \"address\": \"4444\"}, \"delivery_source\": \"keeta\"}', 0, NULL, NULL, '2026-02-18 17:46:01', '2026-02-18 13:03:48'),
('ORD1771356599746', 2, '{\"id\": \"ORD1771356599746\", \"type\": \"pickup\", \"items\": [{\"id\": 7, \"qty\": 2, \"name\": \"Testing\", \"price\": 100}, {\"id\": 9, \"qty\": 1, \"name\": \"Kunafa\", \"price\": 15}, {\"id\": 4, \"qty\": 1, \"name\": \"Chicken Kabsa\", \"price\": 65}, {\"id\": 8, \"qty\": 1, \"name\": \"Small Water\", \"price\": 5}], \"customer\": {\"name\": \"Otaksi Clients\", \"phone\": \"5555555\", \"address\": \"\"}, \"delivery_source\": \"internal\"}', 0, NULL, NULL, '2026-02-18 17:46:01', '2026-02-18 13:03:48'),
('ORD1771356700814', 2, '{\"id\": \"ORD1771356700814\", \"type\": \"dine_in\", \"items\": [{\"id\": 8, \"qty\": 3, \"name\": \"Small Water\", \"price\": 5}, {\"id\": 7, \"qty\": 1, \"name\": \"Testing\", \"price\": 100}], \"customer\": {\"name\": \"rrrrr\", \"phone\": \"66666\", \"address\": \"\"}, \"delivery_source\": \"internal\"}', 0, NULL, NULL, '2026-02-18 17:46:01', '2026-02-18 13:03:48'),
('ORD1771356716285', 2, '{\"id\": \"ORD1771356716285\", \"type\": \"delivery\", \"items\": [{\"id\": 8, \"qty\": 1, \"name\": \"Small Water\", \"price\": 5}], \"customer\": {\"name\": \"\", \"phone\": \"\", \"address\": \"\"}, \"is_deleted\": 1, \"delivery_source\": \"noon\"}', 0, NULL, '2026-02-18 03:30:48', '2026-02-18 17:46:00', '2026-02-18 13:03:48'),
('ORD1771356842442', 2, '{\"id\": \"ORD1771356842442\", \"type\": \"pickup\", \"items\": [], \"customer\": {\"name\": \"Otaksi Clients\", \"phone\": \"5555555\", \"address\": \"\"}, \"is_deleted\": 1, \"delivery_source\": \"internal\"}', 0, NULL, '2026-02-18 03:02:28', '2026-02-18 17:46:01', '2026-02-18 13:03:48'),
('ORD1771362061296', 2, '{\"id\": \"ORD1771362061296\", \"type\": \"delivery\", \"items\": [], \"customer\": {\"name\": \"New\", \"phone\": \"5555555\", \"address\": \"4444\"}, \"is_deleted\": 1, \"delivery_source\": \"deliveroo\"}', 0, NULL, '2026-02-18 02:50:33', '2026-02-18 17:46:01', '2026-02-18 13:03:48'),
('ORD1771374549293', 2, '{\"id\": \"ORD1771374549293\", \"type\": \"delivery\", \"items\": [], \"customer\": {\"name\": \"Otaksi Clients\", \"phone\": \"5555555\", \"address\": \"4444\"}, \"delivery_source\": \"deliveroo\"}', 0, NULL, NULL, '2026-02-18 17:46:01', '2026-02-18 13:03:48'),
('ORD1771374825973', 2, '{\"id\": \"ORD1771374825973\", \"type\": \"pickup\", \"items\": [], \"customer\": {\"name\": \"Otaksi Clients\", \"phone\": \"5555555\", \"address\": \"\"}, \"delivery_source\": \"internal\"}', 0, NULL, NULL, '2026-02-18 17:46:01', '2026-02-18 13:03:48'),
('ORD1771375150519', 2, '{\"id\": \"ORD1771375150519\", \"type\": \"delivery\", \"items\": [], \"customer\": {\"name\": \"Otaksi Clients\", \"phone\": \"6666\", \"address\": \"4444\"}, \"delivery_source\": \"internal\"}', 0, NULL, NULL, '2026-02-18 17:46:01', '2026-02-18 13:03:48'),
('ORD1771375483405', 2, '{\"id\": \"ORD1771375483405\", \"type\": \"dine_in\", \"items\": [{\"id\": 8, \"qty\": 1, \"name\": \"Small Water\", \"price\": 5}], \"customer\": {\"name\": \"Otaksi Clients\", \"phone\": \"5555555\", \"address\": \"\"}, \"delivery_source\": \"internal\"}', 0, NULL, NULL, '2026-02-18 17:46:01', '2026-02-18 13:03:48'),
('ORD1771375489724', 2, '{\"id\": \"ORD1771375489724\", \"type\": \"pickup\", \"items\": [{\"id\": 8, \"qty\": 1, \"name\": \"Small Water\", \"price\": 5}, {\"id\": 3, \"qty\": 1, \"name\": \"Mixed Grill Platter\", \"price\": 95}], \"customer\": {\"name\": \"Otaksi Clients\", \"phone\": \"5555555\", \"address\": \"\"}, \"delivery_source\": \"internal\"}', 0, NULL, NULL, '2026-02-18 17:46:01', '2026-02-18 13:03:48'),
('ORD1771378275020', 2, '{\"id\": \"ORD1771378275020\", \"type\": \"delivery\", \"items\": [{\"id\": 8, \"qty\": 1, \"name\": \"Small Water\", \"price\": 5}], \"customer\": {\"name\": \"Otaksi Clients\", \"phone\": \"5555555\", \"address\": \"4444\"}, \"table_number\": null, \"num_customers\": null, \"delivery_source\": \"deliveroo\"}', 0, NULL, NULL, '2026-02-18 17:46:01', '2026-02-18 13:03:48'),
('ORD1771378714632', 2, '{\"id\": \"ORD1771378714632\", \"type\": \"dine_in\", \"items\": [{\"id\": 7, \"qty\": 1, \"name\": \"Testing\", \"price\": 100}, {\"id\": 2, \"qty\": 1, \"name\": \"Chicken Mandhi\", \"price\": 65}], \"customer\": {\"name\": \"Otaksi Clients\", \"phone\": \"5555555\", \"address\": \"\"}, \"table_number\": \"HALL\", \"num_customers\": \"5\", \"delivery_source\": \"internal\"}', 0, NULL, NULL, '2026-02-18 17:46:01', '2026-02-18 13:03:48'),
('ORD1771383823013', 2, '{\"id\": \"ORD1771383823013\", \"type\": \"dine_in\", \"items\": [], \"customer\": {\"name\": \"Yawa\", \"phone\": \"5555555\", \"address\": \"\"}, \"is_deleted\": 1, \"table_number\": \"T1\", \"num_customers\": \"90\", \"delivery_source\": \"internal\"}', 0, NULL, NULL, '2026-02-18 17:46:01', '2026-02-18 13:04:05'),
('ORD1771384709323', 2, '{\"id\": \"ORD1771384709323\", \"type\": \"dine_in\", \"items\": [{\"id\": 9, \"qty\": 1, \"name\": \"Kunafa\", \"price\": 15}], \"customer\": {\"name\": \"Me\", \"phone\": \"5555555\", \"address\": \"\"}, \"table_number\": \"T1\", \"num_customers\": \"55\", \"delivery_source\": \"internal\"}', 0, NULL, NULL, '2026-02-18 17:46:01', '2026-02-18 13:04:05'),
('ORD1771385296636', 2, '{\"id\": \"ORD1771385296636\", \"type\": \"delivery\", \"items\": [{\"id\": 8, \"qty\": 2, \"name\": \"Small Water\", \"price\": 5}], \"customer\": {\"name\": \"Otaksi Clients\", \"phone\": \"5555555\", \"address\": \"4444\"}, \"table_number\": null, \"num_customers\": null, \"delivery_source\": \"internal\"}', 0, NULL, NULL, '2026-02-18 17:46:00', '2026-02-18 13:04:05'),
('ORD1771414334620', 2, '{\"id\": \"ORD1771414334620\", \"type\": \"dine_in\", \"items\": [{\"id\": 8, \"qty\": 3, \"name\": \"Small Water\", \"price\": 5}, {\"id\": 9, \"qty\": 1, \"name\": \"Kunafa\", \"price\": 15}], \"customer\": {\"name\": \"Well\", \"phone\": \"5555555\", \"address\": \"\"}, \"table_number\": \"T4\", \"num_customers\": \"5\", \"delivery_source\": \"internal\"}', 0, NULL, NULL, '2026-02-18 17:46:01', '2026-02-18 13:04:05');

-- --------------------------------------------------------

--
-- Table structure for table `printer_logs`
--

CREATE TABLE `printer_logs` (
  `id` int NOT NULL,
  `order_id` int NOT NULL,
  `receipt_type` enum('kitchen','counter') NOT NULL,
  `printed_by` int NOT NULL,
  `printed_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `is_reprint` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `promotions`
--

CREATE TABLE `promotions` (
  `id` int NOT NULL,
  `title` varchar(200) COLLATE utf8mb4_general_ci NOT NULL,
  `subtitle` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_general_ci NOT NULL,
  `short_description` text COLLATE utf8mb4_general_ci,
  `badge_text` varchar(50) COLLATE utf8mb4_general_ci DEFAULT 'Limited Offer',
  `badge_color` varchar(50) COLLATE utf8mb4_general_ci DEFAULT 'var(--color-red)',
  `image_url` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `original_price` decimal(10,2) DEFAULT NULL,
  `offer_price` decimal(10,2) NOT NULL,
  `discount_percent` decimal(5,2) DEFAULT NULL,
  `currency` varchar(10) COLLATE utf8mb4_general_ci DEFAULT 'AED',
  `valid_from` date DEFAULT NULL,
  `valid_until` date DEFAULT NULL,
  `time_slot` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `requirements` text COLLATE utf8mb4_general_ci,
  `min_persons` int DEFAULT NULL,
  `max_persons` int DEFAULT NULL,
  `min_order_amount` decimal(10,2) DEFAULT NULL,
  `offer_type` enum('family','business','early_bird','birthday','takeaway','student','seasonal','other') COLLATE utf8mb4_general_ci DEFAULT 'other',
  `is_active` tinyint(1) DEFAULT '1',
  `is_featured` tinyint(1) DEFAULT '0',
  `is_highlighted` tinyint(1) DEFAULT '0',
  `cta_text` varchar(100) COLLATE utf8mb4_general_ci DEFAULT 'Book Now',
  `cta_link` varchar(255) COLLATE utf8mb4_general_ci DEFAULT 'contact.php',
  `cta_icon` varchar(50) COLLATE utf8mb4_general_ci DEFAULT 'bi-calendar-check',
  `display_order` int DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reservations`
--

CREATE TABLE `reservations` (
  `id` int NOT NULL,
  `customer_name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `customer_email` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `customer_phone` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `reservation_date` date NOT NULL,
  `reservation_time` time NOT NULL,
  `branch_name` varchar(256) COLLATE utf8mb4_general_ci NOT NULL,
  `number_of_guests` int NOT NULL,
  `branch_id` int DEFAULT '1',
  `special_requests` text COLLATE utf8mb4_general_ci,
  `status` enum('pending','confirmed','cancelled','completed') COLLATE utf8mb4_general_ci DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
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
  `id` int NOT NULL,
  `employee_id` int NOT NULL,
  `shift_date` date NOT NULL,
  `shift_type` enum('morning','afternoon','evening','night') COLLATE utf8mb4_general_ci NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `location` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_general_ci,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
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
  `id` int NOT NULL,
  `customer_name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `customer_image` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `rating` int DEFAULT NULL,
  `review` text COLLATE utf8mb4_general_ci NOT NULL,
  `is_approved` tinyint(1) DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ;

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
  `id` int NOT NULL,
  `username` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `employee_id` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `full_name` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `phone` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `role` enum('customer','admin','super-admin','employee') COLLATE utf8mb4_general_ci DEFAULT 'customer',
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_login` timestamp NULL DEFAULT NULL,
  `reset_token` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `reset_token_expiry` timestamp NULL DEFAULT NULL,
  `address` text COLLATE utf8mb4_general_ci,
  `preferred_branch` int DEFAULT '1',
  `loyalty_points` int DEFAULT '0',
  `last_order_date` date DEFAULT NULL,
  `position` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `department` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `salary` decimal(10,2) DEFAULT '0.00',
  `hire_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `employee_id`, `email`, `password_hash`, `full_name`, `phone`, `role`, `is_active`, `created_at`, `updated_at`, `last_login`, `reset_token`, `reset_token_expiry`, `address`, `preferred_branch`, `loyalty_points`, `last_order_date`, `position`, `department`, `salary`, `hire_date`) VALUES
(1, 'admin', NULL, 'admin@yallaalmandhi.com', '$2y$10$YourHashedPasswordHere', 'System Administrator', NULL, 'admin', 1, '2026-01-31 13:34:06', '2026-01-31 13:34:06', NULL, NULL, NULL, NULL, 1, 0, NULL, NULL, NULL, 0.00, NULL),
(2, 'New', NULL, 'abm@gmail.com', '$2y$10$lwUpy9pmKdGAIGF6IaEcPeKUMMouU45BjK02CstIo/Dp24WuBpSJq', 'Abdulla', '+971345264456', 'super-admin', 1, '2026-01-31 16:44:08', '2026-02-18 16:55:49', '2026-02-18 16:55:49', '06eb3d9d4bed5d240d0364b90f18978fadb719c2a4fdf00971f847bacf6a06fc', '2026-01-31 15:32:03', NULL, 1, 0, NULL, NULL, NULL, 0.00, NULL),
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
  `session_id` varchar(128) COLLATE utf8mb4_general_ci NOT NULL,
  `user_id` int NOT NULL,
  `login_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_activity` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `ip_address` varchar(45) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_general_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_sessions`
--

INSERT INTO `user_sessions` (`session_id`, `user_id`, `login_time`, `last_activity`, `ip_address`, `user_agent`) VALUES
('19k12eg374lg3su5p319edus0l', 2, '2026-02-06 13:15:00', '2026-02-06 13:15:00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36'),
('4550d52e77ade236482646766e886faa', 2, '2026-02-16 15:32:48', '2026-02-16 15:32:48', '172.20.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36'),
('8cb7c0a142e22019d1f7298282758e11', 2, '2026-02-15 21:50:12', '2026-02-16 13:08:42', '172.20.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36'),
('c3lm8urjfo8qi9kpl1b3p898li', 2, '2026-02-03 14:53:56', '2026-02-03 14:53:56', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36'),
('c3m6ii021fr9qgekach53ko0j8', 2, '2026-02-06 17:12:31', '2026-02-10 10:26:11', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36'),
('cefaf6e4b77b449773c6eb07a78431e1', 2, '2026-02-16 15:59:35', '2026-02-18 11:30:44', '172.20.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36'),
('fa63af08ff03d2847b8436e688455a59', 2, '2026-02-18 16:55:49', '2026-02-18 16:55:49', '172.20.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36'),
('jjvljkq80d4kksklr51mp16i6s', 5, '2026-02-03 15:18:20', '2026-02-03 15:18:20', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36'),
('jsjet30qib08ldle5ekcniqj4l', 2, '2026-02-03 15:01:52', '2026-02-03 15:01:52', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36'),
('mar5iope6o4u393t0hqls82o91', 2, '2026-02-03 15:04:12', '2026-02-03 15:04:12', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36'),
('rf732l4u9sd496h0btojja73mp', 2, '2026-02-15 15:00:40', '2026-02-15 15:00:40', '172.20.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36'),
('sh7008oh0ied7r7ainctou8pq5', 5, '2026-02-03 15:21:10', '2026-02-03 15:21:10', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36'),
('sqj6p9n8uhulocut2uc0pbi46q', 2, '2026-02-03 15:06:39', '2026-02-03 15:06:39', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_attendance_date` (`attendance_date`),
  ADD KEY `idx_employee_attendance` (`employee_id`,`attendance_date`),
  ADD KEY `idx_shift_attendance` (`shift_id`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_entity` (`entity_type`,`entity_id`),
  ADD KEY `idx_action` (`action_type`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_created` (`created_at`);

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
-- Indexes for table `inventory_items`
--
ALTER TABLE `inventory_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_supplier` (`supplier`),
  ADD KEY `idx_is_active` (`is_active`);

--
-- Indexes for table `inventory_transactions`
--
ALTER TABLE `inventory_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `inventory_item_id` (`inventory_item_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_transaction_type` (`transaction_type`),
  ADD KEY `idx_reference` (`reference_id`,`reference_type`);

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
  ADD PRIMARY KEY (`id`),
  ADD KEY `inventory_item_id` (`inventory_item_id`),
  ADD KEY `idx_inventory_tracking` (`track_inventory`,`is_daily_limited`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_number` (`order_number`),
  ADD KEY `fk_orders_updated_by` (`last_updated_by`),
  ADD KEY `idx_order_number` (`order_number`),
  ADD KEY `idx_customer_id` (`customer_id`),
  ADD KEY `idx_branch_id` (`branch_id`),
  ADD KEY `idx_order_status` (`order_status`),
  ADD KEY `idx_payment_status` (`payment_status`),
  ADD KEY `idx_delivery_source` (`delivery_source`),
  ADD KEY `idx_closed_by` (`closed_by_admin_id`),
  ADD KEY `idx_punched_by` (`punched_by_admin_id`),
  ADD KEY `idx_created_at` (`created_at`);

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
-- Indexes for table `pos_order_drafts`
--
ALTER TABLE `pos_order_drafts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_is_deleted` (`is_deleted`);

--
-- Indexes for table `printer_logs`
--
ALTER TABLE `printer_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `printed_by` (`printed_by`),
  ADD KEY `idx_order` (`order_id`),
  ADD KEY `idx_type` (`receipt_type`);

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
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `branches`
--
ALTER TABLE `branches`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `catering_inquiries`
--
ALTER TABLE `catering_inquiries`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inventory_items`
--
ALTER TABLE `inventory_items`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inventory_transactions`
--
ALTER TABLE `inventory_transactions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `menu_categories`
--
ALTER TABLE `menu_categories`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `menu_items`
--
ALTER TABLE `menu_items`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `password_reset_logs`
--
ALTER TABLE `password_reset_logs`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `printer_logs`
--
ALTER TABLE `printer_logs`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `promotions`
--
ALTER TABLE `promotions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `reservations`
--
ALTER TABLE `reservations`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `shifts`
--
ALTER TABLE `shifts`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `testimonials`
--
ALTER TABLE `testimonials`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`shift_id`) REFERENCES `shifts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `attendance_ibfk_2` FOREIGN KEY (`employee_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `inventory_transactions`
--
ALTER TABLE `inventory_transactions`
  ADD CONSTRAINT `inventory_transactions_ibfk_1` FOREIGN KEY (`inventory_item_id`) REFERENCES `inventory_items` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `inventory_transactions_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `menu_items`
--
ALTER TABLE `menu_items`
  ADD CONSTRAINT `menu_items_ibfk_1` FOREIGN KEY (`inventory_item_id`) REFERENCES `inventory_items` (`id`);

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_orders_closed_by` FOREIGN KEY (`closed_by_admin_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_orders_customer` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_orders_punched_by` FOREIGN KEY (`punched_by_admin_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_orders_updated_by` FOREIGN KEY (`last_updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

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
-- Constraints for table `printer_logs`
--
ALTER TABLE `printer_logs`
  ADD CONSTRAINT `printer_logs_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `printer_logs_ibfk_2` FOREIGN KEY (`printed_by`) REFERENCES `users` (`id`);

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
