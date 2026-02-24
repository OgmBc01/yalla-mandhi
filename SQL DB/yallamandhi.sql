-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: mysql
-- Generation Time: Feb 24, 2026 at 06:54 PM
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

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `user_id`, `action_type`, `entity_type`, `entity_id`, `old_value`, `new_value`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 2, 'create', 'order', 1, NULL, '{\"order_number\":\"202602200001\",\"invoice_number\":\"INV-20260220-3410\",\"total\":195.5,\"payment_method\":\"card\"}', NULL, NULL, '2026-02-20 19:26:38'),
(2, 2, 'create', 'order', 2, NULL, '{\"order_number\":\"202602200002\",\"invoice_number\":\"INV-20260220-1061\",\"total\":15.75,\"payment_method\":\"cash\"}', NULL, NULL, '2026-02-20 19:27:27'),
(3, 2, 'create', 'order', 3, NULL, '{\"order_number\":\"202602200003\",\"invoice_number\":\"INV-20260220-2424\",\"total\":132.25,\"payment_method\":\"card\"}', NULL, NULL, '2026-02-20 21:42:14'),
(4, 2, 'create', 'order', 4, NULL, '{\"order_number\":\"202602200004\",\"invoice_number\":\"INV-20260220-2587\",\"total\":327.75,\"payment_method\":\"cash\"}', NULL, NULL, '2026-02-20 21:48:16'),
(5, 2, 'create', 'order', 5, NULL, '{\"order_number\":\"202602200005\",\"invoice_number\":\"INV-20260220-6821\",\"total\":34.5,\"payment_method\":\"card\"}', NULL, NULL, '2026-02-20 21:49:23'),
(6, 2, 'create', 'order', 6, NULL, '{\"order_number\":\"202602200006\",\"invoice_number\":\"INV-20260220-9335\",\"total\":401,\"payment_method\":\"card\"}', NULL, NULL, '2026-02-20 21:50:07'),
(7, 2, 'create', 'order', 7, NULL, '{\"order_number\":\"202602200007\",\"invoice_number\":\"INV-20260220-9049\",\"total\":21.5,\"payment_method\":\"card\"}', NULL, NULL, '2026-02-20 21:52:18'),
(8, 2, 'create', 'order', 8, NULL, '{\"order_number\":\"202602200008\",\"invoice_number\":\"INV-20260220-7985\",\"total\":5.75,\"payment_method\":\"card\"}', NULL, NULL, '2026-02-20 21:52:44'),
(9, 2, 'create', 'order', 9, NULL, '{\"order_number\":\"202602200009\",\"invoice_number\":\"INV-20260220-9752\",\"total\":97.75,\"payment_method\":\"card\"}', NULL, NULL, '2026-02-20 21:58:17'),
(10, 2, 'create', 'order', 10, NULL, '{\"order_number\":\"202602200010\",\"invoice_number\":\"INV-20260220-4484\",\"total\":5.75,\"payment_method\":\"online\"}', NULL, NULL, '2026-02-20 22:03:40'),
(11, 2, 'create', 'order', 11, NULL, '{\"order_number\":\"202602200011\",\"invoice_number\":\"INV-20260220-9993\",\"total\":34.5,\"payment_method\":\"card\"}', NULL, NULL, '2026-02-20 22:04:18'),
(12, 2, 'create', 'order', 12, NULL, '{\"order_number\":\"202602200012\",\"invoice_number\":\"INV-20260220-5556\",\"total\":51.75,\"payment_method\":\"online\"}', NULL, NULL, '2026-02-20 22:04:40'),
(13, 2, 'create', 'order', 13, NULL, '{\"order_number\":\"202602200013\",\"invoice_number\":\"INV-20260220-0880\",\"total\":5.75,\"payment_method\":\"online\"}', NULL, NULL, '2026-02-20 22:06:36'),
(14, 2, 'create', 'order', 14, NULL, '{\"order_number\":\"202602200014\",\"invoice_number\":\"INV-20260220-2605\",\"total\":115,\"payment_method\":\"online\"}', NULL, NULL, '2026-02-20 22:07:50'),
(15, 2, 'create', 'order', 15, NULL, '{\"order_number\":\"202602200015\",\"invoice_number\":\"INV-20260220-9303\",\"total\":15.75,\"payment_method\":\"card\"}', NULL, NULL, '2026-02-20 22:08:19'),
(16, 2, 'create', 'order', 16, NULL, '{\"order_number\":\"202602200016\",\"invoice_number\":\"INV-20260220-8797\",\"total\":17.25,\"payment_method\":\"cash\"}', NULL, NULL, '2026-02-20 22:10:02'),
(17, 2, 'create', 'order', 17, NULL, '{\"order_number\":\"202602200017\",\"invoice_number\":\"INV-20260220-9786\",\"total\":172.5,\"payment_method\":\"card\"}', NULL, NULL, '2026-02-20 22:13:24'),
(18, 2, 'create', 'order', 18, NULL, '{\"order_number\":\"202602200018\",\"invoice_number\":\"INV-20260220-9165\",\"total\":109.25,\"payment_method\":\"online\"}', NULL, NULL, '2026-02-20 22:15:18'),
(19, 2, 'create', 'order', 19, NULL, '{\"order_number\":\"202602200019\",\"invoice_number\":\"INV-20260220-9405\",\"total\":27.25,\"payment_method\":\"online\"}', NULL, NULL, '2026-02-20 22:16:43'),
(20, 2, 'create', 'order', 20, NULL, '{\"order_number\":\"202602200020\",\"invoice_number\":\"INV-20260220-1284\",\"total\":1512.25,\"payment_method\":\"card\"}', NULL, NULL, '2026-02-20 22:22:00'),
(21, 2, 'create', 'order', 21, NULL, '{\"order_number\":\"202602200021\",\"invoice_number\":\"INV-20260220-5196\",\"total\":17.25,\"payment_method\":\"cash\"}', NULL, NULL, '2026-02-20 23:09:19'),
(22, 2, 'create', 'order', 22, NULL, '{\"order_number\":\"202602200022\",\"invoice_number\":\"INV-20260220-7782\",\"total\":189.75,\"payment_method\":\"card\"}', NULL, NULL, '2026-02-20 23:09:47'),
(23, 2, 'create', 'order', 23, NULL, '{\"order_number\":\"202602200023\",\"invoice_number\":\"INV-20260220-0613\",\"total\":5.75,\"payment_method\":\"cash\"}', NULL, NULL, '2026-02-20 23:11:42'),
(24, 2, 'create', 'order', 24, NULL, '{\"order_number\":\"202602200024\",\"invoice_number\":\"INV-20260220-4141\",\"total\":23.8,\"payment_method\":\"card\"}', NULL, NULL, '2026-02-20 23:15:48'),
(25, 2, 'create', 'order', 25, NULL, '{\"order_number\":\"202602210001\",\"invoice_number\":\"INV-20260221-4535\",\"total\":5.75,\"payment_method\":\"online\"}', NULL, NULL, '2026-02-21 15:20:32'),
(26, 2, 'create', 'order', 26, NULL, '{\"order_number\":\"202602210002\",\"invoice_number\":\"INV-20260221-9217\",\"total\":23,\"payment_method\":\"card\"}', NULL, NULL, '2026-02-21 17:36:19'),
(27, 2, 'create', 'order', 27, NULL, '{\"order_number\":\"202602210003\",\"invoice_number\":\"INV-20260221-4550\",\"total\":142.25,\"payment_method\":\"online\"}', NULL, NULL, '2026-02-21 17:42:17'),
(28, 2, 'create', 'order', 28, NULL, '{\"order_number\":\"202602220001\",\"invoice_number\":\"INV-20260222-7761\",\"total\":5.75,\"payment_method\":\"cash\"}', NULL, NULL, '2026-02-22 16:50:06'),
(29, 2, 'create', 'order', 29, NULL, '{\"order_number\":\"202602220002\",\"invoice_number\":\"INV-20260222-0668\",\"total\":109.25,\"payment_method\":\"card\"}', NULL, NULL, '2026-02-22 17:35:26'),
(30, 2, 'create', 'order', 30, NULL, '{\"order_number\":\"202602220003\",\"invoice_number\":\"INV-20260222-0634\",\"total\":5.75,\"payment_method\":\"cash\"}', NULL, NULL, '2026-02-22 17:44:59'),
(31, 2, 'create', 'order', 31, NULL, '{\"order_number\":\"202602220004\",\"invoice_number\":\"INV-20260222-2633\",\"total\":5.75,\"payment_method\":\"cash\"}', NULL, NULL, '2026-02-22 17:48:35'),
(32, 2, 'create', 'order', 32, NULL, '{\"order_number\":\"202602220005\",\"invoice_number\":\"INV-20260222-7894\",\"total\":27.25,\"payment_method\":\"cash\"}', NULL, NULL, '2026-02-22 17:49:55'),
(33, 2, 'create', 'order', 33, NULL, '{\"order_number\":\"202602220006\",\"invoice_number\":\"INV-20260222-0738\",\"total\":224.25,\"payment_method\":\"cash\"}', NULL, NULL, '2026-02-22 17:52:06'),
(34, 2, 'create', 'order', 34, NULL, '{\"order_number\":\"202602220007\",\"invoice_number\":\"INV-20260222-2001\",\"total\":17.25,\"payment_method\":\"card\"}', NULL, NULL, '2026-02-22 17:56:44'),
(35, 2, 'create', 'order', 35, NULL, '{\"order_number\":\"202602220008\",\"invoice_number\":\"INV-20260222-2108\",\"total\":5.75,\"payment_method\":\"card\"}', NULL, NULL, '2026-02-22 18:02:05'),
(36, 2, 'create', 'order', 36, NULL, '{\"order_number\":\"202602220009\",\"invoice_number\":\"INV-20260222-9671\",\"total\":80.5,\"payment_method\":\"card\"}', NULL, NULL, '2026-02-22 18:04:49'),
(37, 2, 'create', 'order', 37, NULL, '{\"order_number\":\"202602220010\",\"invoice_number\":\"INV-20260222-7485\",\"total\":15.75,\"payment_method\":\"card\"}', NULL, NULL, '2026-02-22 18:17:17'),
(38, 2, 'create', 'order', 38, NULL, '{\"order_number\":\"202602220011\",\"invoice_number\":\"INV-20260222-2215\",\"total\":15.75,\"payment_method\":\"online\"}', NULL, NULL, '2026-02-22 18:17:38'),
(39, 2, 'create', 'order', 39, NULL, '{\"order_number\":\"202602220012\",\"invoice_number\":\"INV-20260222-9601\",\"total\":100,\"payment_method\":\"card\"}', NULL, NULL, '2026-02-22 18:20:53'),
(40, 2, 'create', 'order', 40, NULL, '{\"order_number\":\"202602220013\",\"invoice_number\":\"INV-20260222-9018\",\"total\":300,\"payment_method\":\"cash\"}', NULL, NULL, '2026-02-22 18:23:51'),
(41, 2, 'create', 'order', 41, NULL, '{\"order_number\":\"202602220014\",\"invoice_number\":\"INV-20260222-6002\",\"total\":150,\"payment_method\":\"card\"}', NULL, NULL, '2026-02-22 18:25:49'),
(42, 2, 'create', 'order', 42, NULL, '{\"order_number\":\"202602220015\",\"invoice_number\":\"INV-20260222-1383\",\"total\":320,\"payment_method\":\"card\"}', NULL, NULL, '2026-02-22 21:02:56'),
(43, 2, 'create', 'order', 43, NULL, '{\"order_number\":\"202602220016\",\"invoice_number\":\"INV-20260222-4418\",\"total\":15,\"payment_method\":\"card\"}', NULL, NULL, '2026-02-22 21:08:47'),
(44, 2, 'create', 'order', 44, NULL, '{\"order_number\":\"202602220017\",\"invoice_number\":\"INV-20260222-0653\",\"total\":5,\"payment_method\":\"card\"}', NULL, NULL, '2026-02-22 21:09:43'),
(45, 2, 'create', 'order', 45, NULL, '{\"order_number\":\"202602220018\",\"invoice_number\":\"INV-20260222-7753\",\"total\":15,\"payment_method\":\"card\"}', NULL, NULL, '2026-02-22 21:10:43'),
(46, 2, 'create', 'order', 46, NULL, '{\"order_number\":\"202602230001\",\"invoice_number\":\"INV-20260223-4984\",\"total\":120,\"payment_method\":\"cash\"}', NULL, NULL, '2026-02-23 13:37:31'),
(47, 2, 'create', 'order', 47, NULL, '{\"order_number\":\"202602230002\",\"invoice_number\":\"INV-20260223-3238\",\"total\":5,\"payment_method\":\"cash\"}', NULL, NULL, '2026-02-23 13:38:38'),
(48, 2, 'create', 'order', 48, NULL, '{\"order_number\":\"202602230003\",\"invoice_number\":\"INV-20260223-2417\",\"total\":95,\"payment_method\":\"online\"}', NULL, NULL, '2026-02-23 13:44:01'),
(49, 2, 'create', 'order', 49, NULL, '{\"order_number\":\"202602230004\",\"invoice_number\":\"INV-20260223-1716\",\"total\":17,\"payment_method\":\"card\"}', NULL, NULL, '2026-02-23 14:18:00'),
(50, 2, 'create', 'order', 50, NULL, '{\"order_number\":\"202602230005\",\"invoice_number\":\"INV-20260223-0369\",\"total\":130,\"payment_method\":\"cash\"}', NULL, NULL, '2026-02-23 17:34:35'),
(51, 2, 'create', 'order', 51, NULL, '{\"order_number\":\"202602230006\",\"invoice_number\":\"INV-20260223-5096\",\"total\":1625,\"payment_method\":\"card\"}', NULL, NULL, '2026-02-23 18:54:38'),
(52, 2, 'create', 'order', 52, NULL, '{\"order_number\":\"202602230007\",\"invoice_number\":\"INV-20260223-6622\",\"total\":5,\"payment_method\":\"online\"}', NULL, NULL, '2026-02-23 19:25:06'),
(53, 2, 'create', 'order', 53, NULL, '{\"order_number\":\"202602230008\",\"invoice_number\":\"INV-20260223-8019\",\"total\":10,\"payment_method\":\"cash\"}', NULL, NULL, '2026-02-23 19:25:16'),
(54, 2, 'create', 'order', 54, NULL, '{\"order_number\":\"202602230009\",\"invoice_number\":\"INV-20260223-5700\",\"total\":111,\"payment_method\":\"card\"}', NULL, NULL, '2026-02-23 19:30:09');

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
(7, 'Testing', 'New Item', 7, 100.00, 'menu_1770306904_7770.jpg', 1, 0, NULL, 0, 1, 0, NULL, 1.00, '2026-02-05 15:55:04', '2026-02-22 20:58:32'),
(8, 'Small Water', 'Small Masafi bottled water', 8, 5.00, 'menu_1771344153_3985.png', 1, 0, NULL, 0, 1, 0, NULL, 1.00, '2026-02-17 16:02:33', '2026-02-17 16:02:33'),
(9, 'Kunafa', '', 9, 15.00, 'menu_1771344378_3587.png', 1, 0, NULL, 0, 1, 0, NULL, 1.00, '2026-02-17 16:06:18', '2026-02-17 16:06:18'),
(10, 'Kunafa Cheese', '', 9, 15.00, 'menu_1771628729_9430.png', 1, 0, NULL, 0, 1, 0, NULL, 1.00, '2026-02-20 23:05:29', '2026-02-20 23:05:29');

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

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `order_number`, `invoice_number`, `customer_id`, `customer_name`, `customer_name_snapshot`, `customer_email`, `customer_phone`, `customer_phone_snapshot`, `customer_address`, `delivery_address_snapshot`, `order_type`, `delivery_source`, `table_number`, `branch_id`, `subtotal`, `discount_amount`, `tax_amount`, `delivery_fee`, `total_amount`, `item_count`, `order_status`, `payment_method`, `payment_status`, `payment_reference`, `is_draft`, `punched_by_admin_id`, `closed_by_admin_id`, `closed_at`, `last_updated_by`, `special_instructions`, `created_at`, `updated_at`, `num_customers`) VALUES
(1, '202602200001', 'INV-20260220-3410', 43, 'rrrr', 'rrrr', NULL, '66666', '66666', '', '', 'pickup', 'internal', NULL, 1, 170.00, 0.00, 0.00, 0.00, 195.50, 3, 'closed', 'card', 'paid', '', 0, 2, 2, '2026-02-20 19:26:37', NULL, NULL, '2026-02-20 19:26:37', '2026-02-22 18:00:57', NULL),
(2, '202602200002', 'INV-20260220-1061', NULL, '', '', NULL, '', '', '', '', 'delivery', 'noon', NULL, 1, 5.00, 0.00, 0.00, 10.00, 15.75, 1, 'closed', 'cash', 'paid', '', 0, 2, 2, '2026-02-20 19:27:27', NULL, NULL, '2026-02-20 19:27:27', '2026-02-22 18:00:57', NULL),
(3, '202602200003', 'INV-20260220-2424', 43, 'rrrrr', 'rrrrr', NULL, '66666', '66666', '', '', 'dine_in', 'internal', NULL, 1, 115.00, 0.00, 0.00, 0.00, 132.25, 2, 'closed', 'card', 'paid', '', 0, 2, 2, '2026-02-20 21:42:14', NULL, NULL, '2026-02-20 21:42:14', '2026-02-22 18:00:57', NULL),
(4, '202602200004', 'INV-20260220-2587', 44, 'Otaksi Clients', 'Otaksi Clients', NULL, '5555555', '5555555', '', '', 'pickup', 'internal', NULL, 1, 285.00, 0.00, 0.00, 0.00, 327.75, 4, 'closed', 'cash', 'paid', '', 0, 2, 2, '2026-02-20 21:48:16', NULL, NULL, '2026-02-20 21:48:16', '2026-02-22 18:00:57', NULL),
(5, '202602200005', 'INV-20260220-6821', 44, 'Well', 'Well', NULL, '5555555', '5555555', '', '', 'dine_in', 'internal', 'T4', 1, 30.00, 0.00, 0.00, 0.00, 34.50, 2, 'closed', 'card', 'paid', '', 0, 2, 2, '2026-02-20 21:49:23', NULL, NULL, '2026-02-20 21:49:23', '2026-02-22 18:00:57', 5),
(6, '202602200006', 'INV-20260220-9335', 44, 'Otaksi Clients', 'Otaksi Clients', NULL, '5555555', '5555555', '4444', '4444', 'delivery', 'keeta', NULL, 1, 340.00, 0.00, 0.00, 10.00, 401.00, 5, 'closed', 'card', 'paid', '', 0, 2, 2, '2026-02-20 21:50:07', NULL, NULL, '2026-02-20 21:50:07', '2026-02-22 18:00:57', NULL),
(7, '202602200007', 'INV-20260220-9049', 44, 'Otaksi Clients', 'Otaksi Clients', NULL, '5555555', '5555555', '4444', '4444', 'delivery', 'internal', NULL, 1, 10.00, 0.00, 0.00, 10.00, 21.50, 1, 'closed', 'card', 'paid', '', 0, 2, 2, '2026-02-20 21:52:18', NULL, NULL, '2026-02-20 21:52:18', '2026-02-22 18:00:57', NULL),
(8, '202602200008', 'INV-20260220-7985', 44, 'Otaksi Clients', 'Otaksi Clients', NULL, '5555555', '5555555', '', '', 'pickup', 'internal', NULL, 1, 5.00, 0.00, 0.00, 0.00, 5.75, 1, 'closed', 'card', 'paid', '', 0, 2, 2, '2026-02-20 21:52:44', NULL, NULL, '2026-02-20 21:52:44', '2026-02-22 18:00:57', NULL),
(9, '202602200009', 'INV-20260220-9752', 43, 'rrrr', 'rrrr', NULL, '66666', '66666', '', '', 'pickup', 'internal', NULL, 1, 85.00, 0.00, 0.00, 0.00, 97.75, 2, 'closed', 'card', 'paid', '', 0, 2, 2, '2026-02-20 21:58:17', NULL, NULL, '2026-02-20 21:58:17', '2026-02-22 18:00:57', NULL),
(10, '202602200010', 'INV-20260220-4484', 45, 'ttttt', 'ttttt', NULL, '777777', '777777', '', '', 'dine_in', 'internal', NULL, 1, 5.00, 0.00, 0.00, 0.00, 5.75, 1, 'closed', 'online', 'paid', '', 0, 2, 2, '2026-02-20 22:03:40', NULL, NULL, '2026-02-20 22:03:40', '2026-02-22 18:00:57', NULL),
(11, '202602200011', 'INV-20260220-9993', 46, 'eeee', 'eeee', NULL, '6666', '6666', '', '', 'dine_in', 'internal', NULL, 1, 30.00, 0.00, 0.00, 0.00, 34.50, 1, 'closed', 'card', 'paid', '', 0, 2, 2, '2026-02-20 22:04:18', NULL, NULL, '2026-02-20 22:04:18', '2026-02-22 18:00:57', NULL),
(12, '202602200012', 'INV-20260220-5556', 44, 'Otaksi Clients', 'Otaksi Clients', NULL, '5555555', '5555555', '', '', 'pickup', 'internal', NULL, 1, 45.00, 0.00, 0.00, 0.00, 51.75, 1, 'closed', 'online', 'paid', '', 0, 2, 2, '2026-02-20 22:04:40', NULL, NULL, '2026-02-20 22:04:40', '2026-02-22 18:00:57', NULL),
(13, '202602200013', 'INV-20260220-0880', 44, 'Otaksi Clients', 'Otaksi Clients', NULL, '5555555', '5555555', '', '', 'dine_in', 'internal', NULL, 1, 5.00, 0.00, 0.00, 0.00, 5.75, 1, 'closed', 'online', 'paid', '', 0, 2, 2, '2026-02-20 22:06:36', NULL, NULL, '2026-02-20 22:06:36', '2026-02-22 18:00:57', NULL),
(14, '202602200014', 'INV-20260220-2605', 44, 'Otaksi Clients', 'Otaksi Clients', NULL, '5555555', '5555555', '', '', 'pickup', 'internal', NULL, 1, 100.00, 0.00, 0.00, 0.00, 115.00, 2, 'closed', 'online', 'paid', '', 0, 2, 2, '2026-02-20 22:07:50', NULL, NULL, '2026-02-20 22:07:50', '2026-02-22 18:00:57', NULL),
(15, '202602200015', 'INV-20260220-9303', 44, 'Otaksi Clients', 'Otaksi Clients', NULL, '5555555', '5555555', '4444', '4444', 'delivery', 'deliveroo', NULL, 1, 5.00, 0.00, 0.00, 10.00, 15.75, 1, 'closed', 'card', 'paid', '', 0, 2, 2, '2026-02-20 22:08:19', NULL, NULL, '2026-02-20 22:08:19', '2026-02-22 18:00:57', NULL),
(16, '202602200016', 'INV-20260220-8797', 44, 'Me', 'Me', NULL, '5555555', '5555555', '', '', 'dine_in', 'internal', 'T1', 1, 15.00, 0.00, 0.00, 0.00, 17.25, 1, 'closed', 'cash', 'paid', '', 0, 2, 2, '2026-02-20 22:10:02', NULL, NULL, '2026-02-20 22:10:02', '2026-02-22 18:00:57', 55),
(17, '202602200017', 'INV-20260220-9786', 44, 'Abdullah', 'Abdullah', NULL, '5555555', '5555555', '', '', 'pickup', 'internal', NULL, 1, 150.00, 0.00, 0.00, 0.00, 172.50, 4, 'closed', 'card', 'paid', '', 0, 2, 2, '2026-02-20 22:13:24', NULL, NULL, '2026-02-20 22:13:24', '2026-02-22 18:00:57', NULL),
(18, '202602200018', 'INV-20260220-9165', 45, 'ttttt', 'ttttt', NULL, '777777', '777777', '', '', 'dine_in', 'internal', NULL, 1, 95.00, 0.00, 0.00, 0.00, 109.25, 1, 'closed', 'online', 'paid', '', 0, 2, 2, '2026-02-20 22:15:18', NULL, NULL, '2026-02-20 22:15:18', '2026-02-22 18:00:57', NULL),
(19, '202602200019', 'INV-20260220-9405', 43, 'Abdullah', 'Abdullah', NULL, '66666', '66666', '', '', 'delivery', 'smile', NULL, 1, 15.00, 0.00, 0.00, 10.00, 27.25, 1, 'closed', 'online', 'paid', '', 0, 2, 2, '2026-02-20 22:16:43', NULL, NULL, '2026-02-20 22:16:43', '2026-02-22 18:00:57', NULL),
(20, '202602200020', 'INV-20260220-1284', 44, 'Karuna', 'Karuna', NULL, '5555555', '5555555', '', '', 'dine_in', 'internal', 'T13', 1, 1315.00, 0.00, 0.00, 0.00, 1512.25, 2, 'closed', 'card', 'paid', '', 0, 2, 2, '2026-02-20 22:22:00', NULL, NULL, '2026-02-20 22:22:00', '2026-02-22 18:00:57', 100),
(21, '202602200021', 'INV-20260220-5196', 46, 'eeee', 'eeee', NULL, '6666', '6666', '', '', 'dine_in', 'internal', NULL, 1, 15.00, 0.00, 0.00, 0.00, 17.25, 1, 'closed', 'cash', 'paid', '', 0, 2, 2, '2026-02-20 23:09:19', NULL, NULL, '2026-02-20 23:09:19', '2026-02-22 18:00:57', NULL),
(22, '202602200022', 'INV-20260220-7782', 44, 'Otaksi Clients', 'Otaksi Clients', NULL, '5555555', '5555555', '', '', 'dine_in', 'internal', 'HALL', 1, 165.00, 0.00, 0.00, 0.00, 189.75, 2, 'closed', 'card', 'paid', '', 0, 2, 2, '2026-02-20 23:09:47', NULL, NULL, '2026-02-20 23:09:47', '2026-02-22 18:00:57', 5),
(23, '202602200023', 'INV-20260220-0613', NULL, '', '', NULL, '', '', '', '', 'pickup', 'internal', NULL, 1, 5.00, 0.00, 0.00, 0.00, 5.75, 1, 'closed', 'cash', 'paid', '', 0, 2, 2, '2026-02-20 23:11:42', NULL, NULL, '2026-02-20 23:11:42', '2026-02-22 18:00:57', NULL),
(24, '202602200024', 'INV-20260220-4141', 44, 'Malam', 'Malam', NULL, '5555555', '5555555', '4444', '4444', 'delivery', 'internal', NULL, 1, 15.00, 3.00, 0.00, 10.00, 23.80, 1, 'closed', 'card', 'paid', '', 0, 2, 2, '2026-02-20 23:15:48', NULL, NULL, '2026-02-20 23:15:48', '2026-02-22 18:00:57', NULL),
(25, '202602210001', 'INV-20260221-4535', 46, 'eeee', 'eeee', NULL, '6666', '6666', '', '', 'dine_in', 'internal', NULL, 1, 5.00, 0.00, 0.00, 0.00, 5.75, 1, 'closed', 'online', 'paid', '', 0, 2, 2, '2026-02-21 15:20:32', NULL, NULL, '2026-02-21 15:20:32', '2026-02-22 18:00:57', NULL),
(26, '202602210002', 'INV-20260221-9217', 44, 'Otak', 'Otak', NULL, '5555555', '5555555', '', '', 'dine_in', 'internal', 'T2', 1, 20.00, 0.00, 0.00, 0.00, 23.00, 2, 'closed', 'card', 'paid', '', 0, 2, 2, '2026-02-21 17:36:19', NULL, NULL, '2026-02-21 17:36:19', '2026-02-22 18:00:57', 3),
(27, '202602210003', 'INV-20260221-4550', 43, 'Custom', 'Custom', NULL, '66666', '66666', 'Nearby', 'Nearby', 'delivery', 'noon', NULL, 1, 115.00, 0.00, 0.00, 10.00, 142.25, 2, 'closed', 'online', 'paid', '', 0, 2, 2, '2026-02-21 17:42:17', NULL, NULL, '2026-02-21 17:42:17', '2026-02-22 18:00:57', NULL),
(28, '202602220001', 'INV-20260222-7761', 44, 'Otaksi Clients', 'Otaksi Clients', NULL, '5555555', '5555555', '', '', 'pickup', 'internal', NULL, 1, 5.00, 0.00, 0.00, 0.00, 5.75, 1, 'closed', 'cash', 'paid', '', 0, 2, 2, '2026-02-22 16:50:05', NULL, NULL, '2026-02-22 16:50:05', '2026-02-22 18:00:57', NULL),
(29, '202602220002', 'INV-20260222-0668', 44, 'Sajid', 'Sajid', NULL, '5555555', '5555555', '', '', 'dine_in', 'internal', 'T2', 1, 95.00, 0.00, 0.00, 0.00, 109.25, 1, 'closed', 'card', 'paid', '', 0, 2, 2, '2026-02-22 17:35:26', NULL, NULL, '2026-02-22 17:35:26', '2026-02-22 18:00:57', 5),
(30, '202602220003', 'INV-20260222-0634', 43, 'Mr Me', 'Mr Me', NULL, '66666', '66666', '', '', 'pickup', 'internal', NULL, 1, 5.00, 0.00, 0.00, 0.00, 5.75, 1, 'closed', 'cash', 'paid', '', 0, 2, 2, '2026-02-22 17:44:59', NULL, NULL, '2026-02-22 17:44:59', '2026-02-22 18:00:57', NULL),
(31, '202602220004', 'INV-20260222-2633', 44, 'Otaksi Clients', 'Otaksi Clients', NULL, '5555555', '5555555', '', '', 'pickup', 'internal', NULL, 1, 5.00, 0.00, 0.00, 0.00, 5.75, 1, 'closed', 'cash', 'paid', '', 0, 2, 2, '2026-02-22 17:48:35', NULL, NULL, '2026-02-22 17:48:35', '2026-02-22 18:00:57', NULL),
(32, '202602220005', 'INV-20260222-7894', 46, 'Mr Test', 'Mr Test', NULL, '6666', '6666', '4444', '4444', 'delivery', 'internal', NULL, 1, 15.00, 0.00, 0.00, 10.00, 27.25, 1, 'closed', 'cash', 'paid', '', 0, 2, 2, '2026-02-22 17:49:55', NULL, NULL, '2026-02-22 17:49:55', '2026-02-22 18:00:57', NULL),
(33, '202602220006', 'INV-20260222-0738', 43, 'Dee', 'Dee', NULL, '66666', '66666', '', '', 'pickup', 'internal', NULL, 1, 195.00, 0.00, 0.00, 0.00, 224.25, 1, 'closed', 'cash', 'paid', '', 0, 2, 2, '2026-02-22 17:52:06', NULL, NULL, '2026-02-22 17:52:06', '2026-02-22 18:00:57', NULL),
(34, '202602220007', 'INV-20260222-2001', 46, 'Changed', 'Changed', NULL, '6666', '6666', '', '', 'pickup', 'internal', NULL, 1, 15.00, 0.00, 0.00, 0.00, 17.25, 1, 'closed', 'card', 'paid', '', 0, 2, 2, '2026-02-22 17:56:44', NULL, NULL, '2026-02-22 17:56:44', '2026-02-22 18:00:57', NULL),
(35, '202602220008', 'INV-20260222-2108', 46, 'Hammod', 'Hammod', NULL, '6666', '6666', '', '', 'dine_in', 'internal', 'T2', 1, 5.00, 0.00, 0.75, 0.00, 5.75, 1, 'closed', 'card', 'paid', '', 0, 2, 2, '2026-02-22 18:02:05', NULL, NULL, '2026-02-22 18:02:05', '2026-02-22 18:02:05', 2),
(36, '202602220009', 'INV-20260222-9671', 43, 'Tax', 'Tax', NULL, '66666', '66666', '', '', 'pickup', 'internal', NULL, 1, 70.00, 0.00, 10.50, 0.00, 80.50, 2, 'closed', 'card', 'paid', '', 0, 2, 2, '2026-02-22 18:04:49', NULL, NULL, '2026-02-22 18:04:49', '2026-02-22 18:04:49', NULL),
(37, '202602220010', 'INV-20260222-7485', 44, 'New', 'New', NULL, '5555555', '5555555', '4444', '4444', 'delivery', 'deliveroo', NULL, 1, 5.00, 0.00, 0.75, 10.00, 15.75, 1, 'closed', 'card', 'paid', '', 0, 2, 2, '2026-02-22 18:17:17', NULL, NULL, '2026-02-22 18:17:17', '2026-02-22 18:17:17', NULL),
(38, '202602220011', 'INV-20260222-2215', 43, 'Tax2', 'Tax2', NULL, '66666', '66666', 'Nearby', 'Nearby', 'delivery', 'noon', NULL, 1, 5.00, 0.00, 0.75, 10.00, 15.75, 1, 'closed', 'online', 'paid', '', 0, 2, 2, '2026-02-22 18:17:38', NULL, NULL, '2026-02-22 18:17:38', '2026-02-22 18:17:38', NULL),
(39, '202602220012', 'INV-20260222-9601', 43, 'Hmm', 'Hmm', NULL, '66666', '66666', '', '', 'dine_in', 'internal', 'T3', 1, 100.00, 0.00, 0.00, 0.00, 100.00, 1, 'closed', 'card', 'paid', '', 0, 2, 2, '2026-02-22 18:20:53', NULL, NULL, '2026-02-22 18:20:53', '2026-02-22 18:20:53', 3),
(40, '202602220013', 'INV-20260222-9018', NULL, 'Toyo', 'Toyo', NULL, '', '', '4444', '4444', 'delivery', 'keeta', NULL, 1, 300.00, 0.00, 0.00, 0.00, 300.00, 1, 'closed', 'cash', 'paid', '', 0, 2, 2, '2026-02-22 18:23:51', NULL, NULL, '2026-02-22 18:23:51', '2026-02-22 18:23:51', NULL),
(41, '202602220014', 'INV-20260222-6002', 44, 'New', 'New', NULL, '5555555', '5555555', '4444', '4444', 'delivery', 'deliveroo', NULL, 1, 140.00, 0.00, 0.00, 10.00, 150.00, 3, 'closed', 'card', 'paid', '', 0, 2, 2, '2026-02-22 18:25:49', NULL, NULL, '2026-02-22 18:25:49', '2026-02-22 18:25:49', NULL),
(42, '202602220015', 'INV-20260222-1383', 44, 'Otaksi Clients', 'Otaksi Clients', NULL, '5555555', '5555555', '', '', 'pickup', 'internal', NULL, 1, 320.00, 0.00, 0.00, 0.00, 320.00, 3, 'closed', 'card', 'paid', '', 0, 2, 2, '2026-02-22 21:02:56', NULL, NULL, '2026-02-22 21:02:56', '2026-02-22 21:02:56', NULL),
(43, '202602220016', 'INV-20260222-4418', 44, 'Yawa', 'Yawa', NULL, '5555555', '5555555', '', '', 'dine_in', 'internal', 'T1', 1, 15.00, 0.00, 0.00, 0.00, 15.00, 1, 'closed', 'card', 'paid', '', 0, 2, 2, '2026-02-22 21:08:47', NULL, NULL, '2026-02-22 21:08:47', '2026-02-22 21:08:47', 90),
(44, '202602220017', 'INV-20260222-0653', 44, 'Hamdan', 'Hamdan', NULL, '5555555', '5555555', '', '', 'dine_in', 'internal', 'T10', 1, 5.00, 0.00, 0.00, 0.00, 5.00, 1, 'closed', 'card', 'paid', '', 0, 2, 2, '2026-02-22 21:09:43', NULL, NULL, '2026-02-22 21:09:43', '2026-02-22 21:09:43', 7),
(45, '202602220018', 'INV-20260222-7753', 44, 'Otaksi Clients', 'Otaksi Clients', NULL, '5555555', '5555555', '', '', 'dine_in', 'internal', 'T10', 1, 15.00, 0.00, 0.00, 0.00, 15.00, 1, 'closed', 'card', 'paid', '', 0, 2, 2, '2026-02-22 21:10:43', NULL, NULL, '2026-02-22 21:10:43', '2026-02-22 21:10:43', 6),
(46, '202602230001', 'INV-20260223-4984', 44, 'New Customer', 'New Customer', NULL, '5555555', '5555555', '', '', 'dine_in', 'internal', 'T3', 1, 120.00, 0.00, 0.00, 0.00, 120.00, 3, 'closed', 'cash', 'paid', '', 0, 2, 2, '2026-02-23 13:37:31', NULL, NULL, '2026-02-23 13:37:31', '2026-02-23 13:37:31', 3),
(47, '202602230002', 'INV-20260223-3238', 44, 'Otaksi Clients', 'Otaksi Clients', NULL, '5555555', '5555555', '4444', '4444', 'delivery', 'deliveroo', NULL, 1, 5.00, 0.00, 0.00, 0.00, 5.00, 1, 'closed', 'cash', 'paid', '', 0, 2, 2, '2026-02-23 13:38:38', NULL, NULL, '2026-02-23 13:38:38', '2026-02-23 13:38:38', NULL),
(48, '202602230003', 'INV-20260223-2417', 46, 'Otaksi Clients', 'Otaksi Clients', NULL, '6666', '6666', '', '', 'pickup', 'internal', NULL, 1, 95.00, 0.00, 0.00, 0.00, 95.00, 1, 'closed', 'online', 'paid', '', 0, 2, 2, '2026-02-23 13:44:01', NULL, NULL, '2026-02-23 13:44:01', '2026-02-23 13:44:01', NULL),
(49, '202602230004', 'INV-20260223-1716', 44, 'Well', 'Well', NULL, '5555555', '5555555', 'Nearby', 'Nearby', 'delivery', 'internal', NULL, 1, 5.00, 0.00, 0.00, 12.00, 17.00, 1, 'closed', 'card', 'paid', '', 0, 2, 2, '2026-02-23 14:18:00', NULL, NULL, '2026-02-23 14:18:00', '2026-02-23 14:18:00', NULL),
(50, '202602230005', 'INV-20260223-0369', 44, 'Otaksi Clients', 'Otaksi Clients', NULL, '5555555', '5555555', '', '', 'pickup', 'internal', NULL, 1, 130.00, 0.00, 0.00, 0.00, 130.00, 2, 'closed', 'cash', 'paid', '', 0, 2, 2, '2026-02-23 17:34:35', NULL, NULL, '2026-02-23 17:34:35', '2026-02-23 17:34:35', NULL),
(51, '202602230006', 'INV-20260223-5096', 46, 'Hang Lu', 'Hang Lu', NULL, '6666', '6666', '', '', 'dine_in', 'internal', 'T2', 1, 1625.00, 0.00, 0.00, 0.00, 1625.00, 1, 'completed', 'card', 'paid', '', 0, 2, 2, '2026-02-23 18:54:38', NULL, NULL, '2026-02-23 18:54:38', '2026-02-23 18:54:38', 4),
(52, '202602230007', 'INV-20260223-6622', 44, 'Sun Yi', 'Sun Yi', NULL, '5555555', '5555555', '', '', 'dine_in', 'internal', 'T11', 1, 5.00, 0.00, 0.00, 0.00, 5.00, 1, 'completed', 'online', 'paid', '', 0, 2, 2, '2026-02-23 19:25:06', NULL, NULL, '2026-02-23 19:25:06', '2026-02-23 19:25:06', 4),
(53, '202602230008', 'INV-20260223-8019', 44, 'Yuji', 'Yuji', NULL, '5555555', '5555555', '', '', 'dine_in', 'internal', 'T15', 1, 10.00, 0.00, 0.00, 0.00, 10.00, 1, 'completed', 'cash', 'paid', '', 0, 2, 2, '2026-02-23 19:25:16', NULL, NULL, '2026-02-23 19:25:16', '2026-02-23 19:25:16', 4),
(54, '202602230009', 'INV-20260223-5700', 44, 'Smile', 'Smile', NULL, '5555555', '5555555', '4444', '4444', 'delivery', 'smile', NULL, 1, 100.00, 0.00, 0.00, 11.00, 111.00, 1, 'completed', 'card', 'paid', '', 0, 2, 2, '2026-02-23 19:30:09', NULL, NULL, '2026-02-23 19:30:09', '2026-02-23 19:30:09', NULL);

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

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `menu_item_id`, `item_name_snapshot`, `unit_price_snapshot`, `menu_item_name`, `quantity`, `unit_price`, `total_price`, `special_instructions`) VALUES
(1, 1, 8, 'Small Water', 5.00, 'Small Water', 1, 5.00, 5.00, ''),
(2, 1, 7, 'Testing', 100.00, 'Testing', 1, 100.00, 100.00, ''),
(3, 1, 2, 'Chicken Mandhi', 65.00, 'Chicken Mandhi', 1, 65.00, 65.00, ''),
(4, 2, 8, 'Small Water', 5.00, 'Small Water', 1, 5.00, 5.00, ''),
(5, 3, 8, 'Small Water', 5.00, 'Small Water', 3, 5.00, 15.00, ''),
(6, 3, 7, 'Testing', 100.00, 'Testing', 1, 100.00, 100.00, ''),
(7, 4, 7, 'Testing', 100.00, 'Testing', 2, 100.00, 200.00, ''),
(8, 4, 9, 'Kunafa', 15.00, 'Kunafa', 1, 15.00, 15.00, ''),
(9, 4, 4, 'Chicken Kabsa', 65.00, 'Chicken Kabsa', 1, 65.00, 65.00, ''),
(10, 4, 8, 'Small Water', 5.00, 'Small Water', 1, 5.00, 5.00, ''),
(11, 5, 8, 'Small Water', 5.00, 'Small Water', 3, 5.00, 15.00, ''),
(12, 5, 9, 'Kunafa', 15.00, 'Kunafa', 1, 15.00, 15.00, ''),
(13, 6, 3, 'Mixed Grill Platter', 95.00, 'Mixed Grill Platter', 1, 95.00, 95.00, ''),
(14, 6, 4, 'Chicken Kabsa', 65.00, 'Chicken Kabsa', 1, 65.00, 65.00, ''),
(15, 6, 2, 'Chicken Mandhi', 65.00, 'Chicken Mandhi', 1, 65.00, 65.00, ''),
(16, 6, 9, 'Kunafa', 15.00, 'Kunafa', 1, 15.00, 15.00, ''),
(17, 6, 7, 'Testing', 100.00, 'Testing', 1, 100.00, 100.00, ''),
(18, 7, 8, 'Small Water', 5.00, 'Small Water', 2, 5.00, 10.00, ''),
(19, 8, 8, 'Small Water', 5.00, 'Small Water', 1, 5.00, 5.00, ''),
(20, 9, 8, 'Small Water', 5.00, 'Small Water', 2, 5.00, 10.00, ''),
(21, 9, 9, 'Kunafa', 15.00, 'Kunafa', 5, 15.00, 75.00, ''),
(22, 10, 8, 'Small Water', 5.00, 'Small Water', 1, 5.00, 5.00, ''),
(23, 11, 8, 'Small Water', 5.00, 'Small Water', 6, 5.00, 30.00, ''),
(24, 12, 9, 'Kunafa', 15.00, 'Kunafa', 3, 15.00, 45.00, ''),
(25, 13, 8, 'Small Water', 5.00, 'Small Water', 1, 5.00, 5.00, ''),
(26, 14, 8, 'Small Water', 5.00, 'Small Water', 1, 5.00, 5.00, ''),
(27, 14, 3, 'Mixed Grill Platter', 95.00, 'Mixed Grill Platter', 1, 95.00, 95.00, ''),
(28, 15, 8, 'Small Water', 5.00, 'Small Water', 1, 5.00, 5.00, ''),
(29, 16, 9, 'Kunafa', 15.00, 'Kunafa', 1, 15.00, 15.00, ''),
(30, 17, 8, 'Small Water', 5.00, 'Small Water', 1, 5.00, 5.00, ''),
(31, 17, 9, 'Kunafa', 15.00, 'Kunafa', 1, 15.00, 15.00, ''),
(32, 17, 2, 'Chicken Mandhi', 65.00, 'Chicken Mandhi', 1, 65.00, 65.00, ''),
(33, 17, 4, 'Chicken Kabsa', 65.00, 'Chicken Kabsa', 1, 65.00, 65.00, ''),
(34, 18, 3, 'Mixed Grill Platter', 95.00, 'Mixed Grill Platter', 1, 95.00, 95.00, ''),
(35, 19, 9, 'Kunafa', 15.00, 'Kunafa', 1, 15.00, 15.00, ''),
(36, 20, 9, 'Kunafa', 15.00, 'Kunafa', 1, 15.00, 15.00, ''),
(37, 20, 7, 'Testing', 100.00, 'Testing', 13, 100.00, 1300.00, ''),
(38, 21, 9, 'Kunafa', 15.00, 'Kunafa', 1, 15.00, 15.00, ''),
(39, 22, 7, 'Testing', 100.00, 'Testing', 1, 100.00, 100.00, ''),
(40, 22, 2, 'Chicken Mandhi', 65.00, 'Chicken Mandhi', 1, 65.00, 65.00, ''),
(41, 23, 8, 'Small Water', 5.00, 'Small Water', 1, 5.00, 5.00, ''),
(42, 24, 10, 'Kunafa Cheese', 15.00, 'Kunafa Cheese', 1, 15.00, 15.00, ''),
(43, 25, 8, 'Small Water', 5.00, 'Small Water', 1, 5.00, 5.00, ''),
(44, 26, 8, 'Small Water', 5.00, 'Small Water', 1, 5.00, 5.00, ''),
(45, 26, 10, 'Kunafa Cheese', 15.00, 'Kunafa Cheese', 1, 15.00, 15.00, ''),
(46, 27, 7, 'Testing', 100.00, 'Testing', 1, 100.00, 100.00, ''),
(47, 27, 9, 'Kunafa', 15.00, 'Kunafa', 1, 15.00, 15.00, ''),
(48, 28, 8, 'Small Water', 5.00, 'Small Water', 1, 5.00, 5.00, ''),
(49, 29, 3, 'Mixed Grill Platter', 95.00, 'Mixed Grill Platter', 1, 95.00, 95.00, ''),
(50, 30, 8, 'Small Water', 5.00, 'Small Water', 1, 5.00, 5.00, ''),
(51, 31, 8, 'Small Water', 5.00, 'Small Water', 1, 5.00, 5.00, ''),
(52, 32, 9, 'Kunafa', 15.00, 'Kunafa', 1, 15.00, 15.00, ''),
(53, 33, 2, 'Chicken Mandhi', 65.00, 'Chicken Mandhi', 3, 65.00, 195.00, ''),
(54, 34, 9, 'Kunafa', 15.00, 'Kunafa', 1, 15.00, 15.00, ''),
(55, 35, 8, 'Small Water', 5.00, 'Small Water', 1, 5.00, 5.00, ''),
(56, 36, 8, 'Small Water', 5.00, 'Small Water', 1, 5.00, 5.00, ''),
(57, 36, 4, 'Chicken Kabsa', 65.00, 'Chicken Kabsa', 1, 65.00, 65.00, ''),
(58, 37, 8, 'Small Water', 5.00, 'Small Water', 1, 5.00, 5.00, ''),
(59, 38, 8, 'Small Water', 5.00, 'Small Water', 1, 5.00, 5.00, ''),
(60, 39, 7, 'Testing', 100.00, 'Testing', 1, 100.00, 100.00, ''),
(61, 40, 7, 'Testing', 100.00, 'Testing', 3, 100.00, 300.00, ''),
(62, 41, 8, 'Small Water', 5.00, 'Small Water', 2, 5.00, 10.00, ''),
(63, 41, 4, 'Chicken Kabsa', 65.00, 'Chicken Kabsa', 1, 65.00, 65.00, ''),
(64, 41, 2, 'Chicken Mandhi', 65.00, 'Chicken Mandhi', 1, 65.00, 65.00, ''),
(65, 42, 3, 'Mixed Grill Platter', 95.00, 'Mixed Grill Platter', 2, 95.00, 190.00, ''),
(66, 42, 4, 'Chicken Kabsa', 65.00, 'Chicken Kabsa', 1, 65.00, 65.00, ''),
(67, 42, 2, 'Chicken Mandhi', 65.00, 'Chicken Mandhi', 1, 65.00, 65.00, ''),
(68, 43, 9, 'Kunafa', 15.00, 'Kunafa', 1, 15.00, 15.00, ''),
(69, 44, 8, 'Small Water', 5.00, 'Small Water', 1, 5.00, 5.00, ''),
(70, 45, 9, 'Kunafa', 15.00, 'Kunafa', 1, 15.00, 15.00, ''),
(71, 46, 8, 'Small Water', 5.00, 'Small Water', 1, 5.00, 5.00, ''),
(72, 46, 7, 'Testing', 100.00, 'Testing', 1, 100.00, 100.00, ''),
(73, 46, 10, 'Kunafa Cheese', 15.00, 'Kunafa Cheese', 1, 15.00, 15.00, ''),
(74, 47, 8, 'Small Water', 5.00, 'Small Water', 1, 5.00, 5.00, ''),
(75, 48, 3, 'Mixed Grill Platter', 95.00, 'Mixed Grill Platter', 1, 95.00, 95.00, ''),
(76, 49, 8, 'Small Water', 5.00, 'Small Water', 1, 5.00, 5.00, ''),
(77, 50, 4, 'Chicken Kabsa', 65.00, 'Chicken Kabsa', 1, 65.00, 65.00, ''),
(78, 50, 2, 'Chicken Mandhi', 65.00, 'Chicken Mandhi', 1, 65.00, 65.00, ''),
(79, 51, 4, 'Chicken Kabsa', 65.00, 'Chicken Kabsa', 25, 65.00, 1625.00, ''),
(80, 52, 8, 'Small Water', 5.00, 'Small Water', 1, 5.00, 5.00, ''),
(81, 53, 8, 'Small Water', 5.00, 'Small Water', 2, 5.00, 10.00, ''),
(82, 54, 7, 'Testing', 100.00, 'Testing', 1, 100.00, 100.00, '');

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
('ORD1771374549293', 2, '{\"id\": \"ORD1771374549293\", \"type\": \"delivery\", \"items\": [], \"customer\": {\"name\": \"Otaksi Clients\", \"phone\": \"5555555\", \"address\": \"4444\"}, \"delivery_source\": \"deliveroo\"}', 1, '2026-02-22 20:56:39', '2026-02-22 20:55:27', '2026-02-22 20:55:38', '2026-02-18 13:03:48'),
('ORD1771375150519', 2, '{\"id\": \"ORD1771375150519\", \"type\": \"delivery\", \"items\": [], \"customer\": {\"name\": \"Otaksi Clients\", \"phone\": \"6666\", \"address\": \"4444\"}, \"delivery_source\": \"internal\"}', 1, '2026-02-18 18:07:38', NULL, '2026-02-18 18:07:34', '2026-02-18 13:03:48'),
('ORD1771610011715', 2, '{\"id\": \"ORD1771610011715\", \"type\": \"pickup\", \"items\": [{\"id\": 7, \"qty\": 1, \"name\": \"Testing\", \"price\": 100}, {\"id\": 9, \"qty\": 1, \"name\": \"Kunafa\", \"price\": 15}], \"customer\": {\"name\": \"Tester\", \"phone\": \"\", \"address\": \"\"}, \"table_number\": null, \"num_customers\": null, \"delivery_source\": \"internal\"}', 1, '2026-02-23 17:39:02', '2026-02-23 17:38:55', '2026-02-23 21:38:55', '2026-02-20 17:53:32'),
('ORD1771855980331', 2, '{\"id\": \"ORD1771855980331\", \"type\": \"pickup\", \"items\": [{\"id\": 8, \"qty\": 1, \"name\": \"Small Water\", \"price\": 5}, {\"id\": 3, \"qty\": 1, \"name\": \"Mixed Grill Platter\", \"price\": 95}], \"customer\": {\"name\": \"Same\", \"phone\": \"5555555\", \"address\": \"\"}, \"table_number\": null, \"num_customers\": null, \"delivery_source\": \"internal\"}', 1, '2026-02-23 17:39:05', '2026-02-23 17:38:23', '2026-02-23 21:39:02', '2026-02-23 18:13:00'),
('ORD1771943840303', 2, '{\"id\": \"ORD1771943840303\", \"type\": \"delivery\", \"items\": [{\"id\": 8, \"qty\": 1, \"name\": \"Small Water\", \"price\": 5}, {\"id\": 10, \"qty\": 1, \"name\": \"Kunafa Cheese\", \"price\": 15}], \"customer\": {\"name\": \"Yallaaa\", \"phone\": \"5555555\", \"address\": \"4444\"}, \"delivery_fee\": 0, \"order_status\": \"in_preparation\", \"table_number\": null, \"num_customers\": null, \"delivery_source\": \"keeta\"}', 0, NULL, NULL, '2026-02-24 21:29:25', '2026-02-24 18:37:20');

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

--
-- Dumping data for table `printer_logs`
--

INSERT INTO `printer_logs` (`id`, `order_id`, `receipt_type`, `printed_by`, `printed_at`, `is_reprint`) VALUES
(1, 19, 'kitchen', 2, '2026-02-20 22:41:23', 0),
(2, 19, 'counter', 2, '2026-02-20 22:41:34', 0),
(3, 20, 'counter', 2, '2026-02-20 22:44:40', 0),
(4, 24, 'counter', 2, '2026-02-21 11:22:04', 0),
(5, 24, 'counter', 2, '2026-02-21 11:22:22', 0),
(6, 24, 'counter', 2, '2026-02-21 11:24:55', 0),
(7, 24, 'counter', 2, '2026-02-21 11:25:04', 0),
(8, 24, 'counter', 2, '2026-02-21 11:27:48', 0),
(9, 24, 'counter', 2, '2026-02-21 11:29:43', 0),
(10, 24, 'counter', 2, '2026-02-21 11:29:53', 0),
(11, 24, 'counter', 2, '2026-02-21 11:33:28', 0);

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
(2, 'New', NULL, 'abm@gmail.com', '$2y$10$lwUpy9pmKdGAIGF6IaEcPeKUMMouU45BjK02CstIo/Dp24WuBpSJq', 'Abdulla', '+971345264456', 'super-admin', 1, '2026-01-31 16:44:08', '2026-02-24 18:50:50', '2026-02-24 18:50:50', '06eb3d9d4bed5d240d0364b90f18978fadb719c2a4fdf00971f847bacf6a06fc', '2026-01-31 15:32:03', NULL, 1, 0, NULL, NULL, NULL, 0.00, NULL),
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
(14, 'R', '111', 'asmi@email.com', '$2y$10$AUAKwHEF2cruJv9r7YO3VeqDuWf3U04K2r4F4SYR8c8he1npADZG6', 'Ashmita', '+971345264456', 'employee', 1, '2026-02-07 17:28:53', '2026-02-23 13:15:46', NULL, NULL, NULL, 'Something', 1, 0, NULL, 'Cachier', 'Administration', 0.00, '2026-02-01'),
(43, 'cust_6998b56dd9575', NULL, 'auto_6998b56ded13f@noemail.local', '$2y$10$rYu/vLUmM1G1jeT3lmepxe9Sj6RvvAEbR2zVmO4CHA9igzZhZ/Ibq', 'rrrr', '66666', 'customer', 1, '2026-02-20 19:26:37', '2026-02-20 19:26:37', NULL, NULL, NULL, '', 1, 0, NULL, NULL, NULL, 0.00, NULL),
(44, 'cust_6998d6a05b9a3', NULL, 'auto_6998d6a079bda@noemail.local', '$2y$10$lSmspv5gQhR0XMGMlUtAZuGL1BCGt0n.0h0LgGww8SLaG3KziynLG', 'Otaksi Clients', '5555555', 'customer', 1, '2026-02-20 21:48:16', '2026-02-20 21:48:16', NULL, NULL, NULL, '', 1, 0, NULL, NULL, NULL, 0.00, NULL),
(45, 'cust_6998da3c98d1c', NULL, 'auto_6998da3cb1694@noemail.local', '$2y$10$rjSVV5vQU3FtkmgJGp0rV.j.o8CObLPNAfTuCeJbLrW27AsqgTM1i', 'ttttt', '777777', 'customer', 1, '2026-02-20 22:03:40', '2026-02-20 22:03:40', NULL, NULL, NULL, '', 1, 0, NULL, NULL, NULL, 0.00, NULL),
(46, 'cust_6998da6241d0b', NULL, 'auto_6998da625618e@noemail.local', '$2y$10$PPAUbBPqxaDzt4UVVI6igeXh/qStZzZX6zGhVo4JwnI4T3NaxFzJi', 'eeee', '6666', 'customer', 1, '2026-02-20 22:04:18', '2026-02-20 22:04:18', NULL, NULL, NULL, '', 1, 0, NULL, NULL, NULL, 0.00, NULL);

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
('86d1896e834ba19e8230200d2e70e9a0', 2, '2026-02-21 11:17:03', '2026-02-22 20:42:23', '172.20.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36'),
('8cb7c0a142e22019d1f7298282758e11', 2, '2026-02-15 21:50:12', '2026-02-16 13:08:42', '172.20.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36'),
('ba71539c40b0191b832d798fed1944dc', 2, '2026-02-24 18:50:50', '2026-02-24 18:50:50', '172.20.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36'),
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
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

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
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=83;

--
-- AUTO_INCREMENT for table `password_reset_logs`
--
ALTER TABLE `password_reset_logs`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `printer_logs`
--
ALTER TABLE `printer_logs`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

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
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

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
