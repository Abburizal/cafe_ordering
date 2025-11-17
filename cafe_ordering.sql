-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Nov 17, 2025 at 06:24 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `cafe_ordering`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_tokens`
--

CREATE TABLE `admin_tokens` (
  `id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `token` varchar(255) NOT NULL,
  `device_type` varchar(50) DEFAULT NULL,
  `device_name` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `icon` varchar(50) DEFAULT NULL,
  `display_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`, `icon`, `display_order`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Makanan', 'Menu makanan utama', '🍽️', 1, 1, '2025-11-05 13:56:46', '2025-11-05 13:56:46'),
(2, 'Minuman', 'Minuman segar dan hangat', '🥤', 2, 1, '2025-11-05 13:56:46', '2025-11-05 13:56:46'),
(3, 'Snack', 'Cemilan ringan', '🍟', 3, 1, '2025-11-05 13:56:46', '2025-11-05 13:56:46'),
(4, 'Dessert', 'Pencuci mulut manis', '🍰', 4, 1, '2025-11-05 13:56:46', '2025-11-05 13:56:46'),
(5, 'Coffee', 'Kopi dan turunannya', '☕', 5, 1, '2025-11-05 13:56:46', '2025-11-05 13:56:46'),
(6, 'Special', 'Menu spesial / promo', '⭐', 6, 1, '2025-11-05 13:56:46', '2025-11-05 13:56:46');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `order_code` varchar(100) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `table_number` varchar(100) NOT NULL,
  `table_id` int(11) DEFAULT NULL,
  `total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `payment_method` enum('cash','qris','qris_mock') NOT NULL,
  `status` enum('pending','processing','done','cancelled') DEFAULT 'pending',
  `midtrans_id` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `order_code`, `user_id`, `table_number`, `table_id`, `total`, `payment_method`, `status`, `midtrans_id`, `created_at`, `updated_at`) VALUES
(2, 'ORD-20251017-720B37', NULL, '', NULL, 540000.00, 'qris_mock', 'cancelled', NULL, '2025-10-17 11:15:51', NULL),
(3, 'ORD-20251017-9E26E7', NULL, '', NULL, 20000.00, 'qris_mock', 'done', NULL, '2025-10-17 11:19:19', NULL),
(4, 'ORD-20251017-47B9B7', NULL, '', NULL, 50000.00, 'qris_mock', 'done', NULL, '2025-10-17 11:20:40', NULL),
(5, 'ORD-20251017-FC2B5A', NULL, '', NULL, 60000.00, 'qris_mock', 'done', NULL, '2025-10-17 11:21:12', NULL),
(6, 'ORD-20251017-5AB7E0', NULL, '', NULL, 20000.00, 'qris_mock', 'done', NULL, '2025-10-17 11:32:21', NULL),
(15, 'ORD-20251017-610FDC', NULL, '', NULL, 20000.00, 'qris_mock', 'done', NULL, '2025-10-17 11:52:58', NULL),
(21, 'ORD-20251105-FD4E9B', NULL, 'MEJA 1', NULL, 18000.00, 'qris', 'pending', NULL, '2025-11-05 17:19:59', NULL),
(22, 'ORD-20251106-B05E26', NULL, 'MEJA 1', 1, 18000.00, 'qris', 'pending', NULL, '2025-11-06 05:48:11', NULL),
(23, 'ORD-20251106-1B091F', NULL, 'MEJA 1', 1, 40000.00, 'qris', 'pending', NULL, '2025-11-06 05:48:49', NULL),
(24, 'ORD-20251106-25B8FA', NULL, 'MEJA 1', 1, 40000.00, 'qris', 'pending', NULL, '2025-11-06 05:51:14', NULL),
(25, 'ORD-20251108-B51178', NULL, 'MEJA 1', 1, 20000.00, 'qris', 'processing', NULL, '2025-11-08 10:14:19', NULL),
(26, 'ORD-20251108-7A459B', NULL, 'MEJA 1', 1, 20000.00, 'qris', 'processing', NULL, '2025-11-08 10:17:27', NULL),
(27, 'ORD-20251113-140F4F', NULL, 'MEJA 1', 1, 20000.00, 'qris', 'processing', NULL, '2025-11-13 06:03:29', NULL),
(28, 'ORD-20251114-308195', NULL, 'MEJA 1', 1, 20000.00, 'qris', 'processing', NULL, '2025-11-14 17:18:27', NULL),
(29, 'ORD-20251114-AC3606', NULL, 'MEJA 1', 1, 20000.00, 'qris', 'processing', NULL, '2025-11-14 18:01:30', NULL),
(30, 'ORD-20251114-F3F798', NULL, 'MEJA 2', 2, 40000.00, 'cash', 'done', NULL, '2025-11-14 19:24:58', '2025-11-14 19:37:13'),
(31, 'ORD-20251114-D25B5A', NULL, 'MEJA 2', 2, 40000.00, 'qris', 'done', NULL, '2025-11-14 19:25:33', '2025-11-14 19:37:06');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `qty` int(11) NOT NULL DEFAULT 1,
  `price` decimal(12,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `qty`, `price`) VALUES
(1, 2, 4, 10, 50000.00),
(2, 2, 5, 2, 20000.00),
(3, 3, 5, 1, 20000.00),
(4, 4, 4, 1, 50000.00),
(5, 5, 5, 3, 20000.00),
(6, 6, 5, 1, 20000.00),
(7, 15, 5, 1, 20000.00),
(10, 21, 17, 1, 18000.00),
(11, 22, 17, 1, 18000.00),
(12, 23, 11, 1, 22000.00),
(13, 23, 17, 1, 18000.00),
(14, 24, 11, 1, 22000.00),
(15, 24, 17, 1, 18000.00),
(16, 25, 5, 1, 20000.00),
(17, 26, 5, 1, 20000.00),
(18, 27, 5, 1, 20000.00),
(19, 28, 5, 1, 20000.00),
(20, 29, 5, 1, 20000.00),
(21, 30, 11, 1, 22000.00),
(22, 30, 17, 1, 18000.00),
(23, 31, 11, 1, 22000.00),
(24, 31, 17, 1, 18000.00);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `description` text DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `stock` int(11) DEFAULT 0,
  `image` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1 COMMENT 'Status aktif produk (1=aktif, 0=arsip)',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `price`, `description`, `category_id`, `stock`, `image`, `is_active`, `created_at`) VALUES
(4, 'Roti Bakar', 15000.00, '', NULL, 0, 'category-1.jpg', 1, '2025-10-17 08:25:46'),
(5, 'Oreo Cookies', 20000.00, '', NULL, 0, 'category-bakery-biscuits.jpg', 1, '2025-10-17 10:29:46'),
(9, 'Nasi Goreng Spesial', 25000.00, 'Nasi goreng dengan telur, ayam, dan sayuran', NULL, 50, 'nasi-goreng.jpg', 1, '2025-11-05 14:00:46'),
(10, 'Mie Goreng', 20000.00, 'Mie goreng pedas dengan topping ayam', NULL, 40, 'mie-goreng.jpg', 1, '2025-11-05 14:00:46'),
(11, 'Ayam Geprek', 22000.00, 'Ayam crispy dengan sambal geprek level 1-5', NULL, 30, 'ayam-geprek.jpg', 1, '2025-11-05 14:00:46'),
(12, 'Soto Ayam', 18000.00, 'Soto ayam kuah bening dengan nasi', NULL, 35, 'soto-ayam.jpg', 1, '2025-11-05 14:00:46'),
(13, 'Gado-Gado', 15000.00, 'Sayuran dengan saus kacang', NULL, 25, 'gado-gado.jpg', 1, '2025-11-05 14:00:46'),
(14, 'Es Teh Manis', 5000.00, 'Teh manis dingin segar', NULL, 100, 'es-teh.jpg', 1, '2025-11-05 14:00:46'),
(15, 'Es Jeruk', 8000.00, 'Jeruk peras asli dingin', NULL, 80, 'es-jeruk.jpg', 1, '2025-11-05 14:00:46'),
(16, 'Kopi Hitam', 10000.00, 'Kopi hitam premium', NULL, 60, 'kopi-hitam.jpg', 1, '2025-11-05 14:00:46'),
(17, 'Cappuccino', 18000.00, 'Kopi cappuccino dengan foam lembut', NULL, 50, 'cappuccino.jpg', 1, '2025-11-05 14:00:46'),
(18, 'Thai Tea', 12000.00, 'Thai tea original dengan susu', NULL, 70, 'thai-tea.jpg', 1, '2025-11-05 14:00:46'),
(19, 'Kentang Goreng', 12000.00, 'French fries crispy dengan saus', NULL, 40, 'kentang-goreng.jpg', 1, '2025-11-05 14:00:46'),
(20, 'Pisang Goreng', 10000.00, 'Pisang goreng crispy 5 pcs', NULL, 30, 'pisang-goreng.jpg', 1, '2025-11-05 14:00:46'),
(21, 'Es Krim Vanilla', 15000.00, 'Es krim vanilla premium 2 scoop', NULL, 25, 'es-krim.jpg', 1, '2025-11-05 14:00:46'),
(22, 'Pancake Coklat', 20000.00, 'Pancake dengan topping coklat dan strawberry', NULL, 20, '1763149169_Gemini_Generated_Image_rb7eq4rb7eq4rb7e.png', 1, '2025-11-05 14:00:46'),
(23, 'pakeet tripin', 4500000.00, 'PAKET THAILAND', NULL, 100, '1763149209_BKK-PTY-4D3N-600x600.jpg', 1, '2025-11-14 19:40:09'),
(24, 'Paket Thailand', 4500000.00, 'Liburan thailand', NULL, 100, '1763149954_Gemini_Generated_Image_rb7eq4rb7eq4rb7e.png', 1, '2025-11-14 19:52:34'),
(25, 'Paket Thailand', 4500000.00, 'Liburan thailand', NULL, 100, '1763149977_Gemini_Generated_Image_1ldenp1ldenp1lde.png', 1, '2025-11-14 19:52:42');

-- --------------------------------------------------------

--
-- Table structure for table `tables`
--

CREATE TABLE `tables` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `code` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tables`
--

INSERT INTO `tables` (`id`, `name`, `code`) VALUES
(1, 'MEJA 1', 'TBL-001'),
(2, 'MEJA 2', 'TBL-002'),
(3, 'MEJA 3', 'TBL-003'),
(4, 'MEJA 4', 'TBL-004'),
(5, 'MEJA 5', 'TBL-005'),
(6, 'MEJA 6', 'TBL-006'),
(7, 'MEJA 7', 'TBL-007'),
(8, 'MEJA 8', 'TBL-008'),
(9, 'MEJA 9', 'TBL-009'),
(10, 'MEJA 10', 'TBL-010'),
(11, 'MEJA 11', 'TBL-011'),
(22, 'VIP 1', 'TBL-VIP1'),
(23, 'VIP 2', 'TBL-VIP2'),
(24, 'TAKE AWAY', 'TBL-TAKEAWAY');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('customer','admin') NOT NULL DEFAULT 'customer',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'admin', 'admin@restoku.com', '$2y$10$mpT8.PUzchrTwZl7sKjyOOte3vkVeMvNlFm588mP8g4Ylq.vqLvxe', 'admin', '2025-10-17 07:05:18'),
(2, 'satriyo', 'satriyo@gmail.com', '$2y$12$x8VVInBgAlO8drfCGtDpW.0yT3wAsdAQmC8T6sIlgih4tfgTPSJGq', 'admin', '2025-10-17 07:25:24'),
(3, 'yana', 'yana@gmail.com', '$2y$12$5Yn26YmbmnyJnpGzaRh.6utwxTjB10cvSwDyZaEUn5RRsve8ayFSS', 'admin', '2025-10-17 07:29:32'),
(4, 'bahar', 'bahar@gmail.com', '$2y$12$brPaVG0R5I9Ml/oGZw/Ls.7J7OYHi8AEV7rPEGZDex5uLrQbcwrEm', 'admin', '2025-10-17 07:32:48'),
(5, 'kasir', 'kasir@gmail.com', '$2y$12$CpZaWXSd1tweFic7fajcCexgfzE9pqj/6SKztpkSryZ0clM7ZkuVm', 'admin', '2025-10-17 10:34:19'),
(6, 'achil', 'aburizal12345@gmail.com', '$2y$12$VHOBQRvOaVhtvXdQPYntl.mVyWr5jc..yXiwq4wSTFAzJo9TBd8LW', 'admin', '2025-10-23 09:45:49'),
(7, 'achil', 'achil123@gmail.com', '$2y$12$S94.WiDLQ41L1.LJ5/0iHO2nORkEpPzAbQkTmcPhbB/nDbgWykyNi', 'admin', '2025-10-23 09:46:52'),
(8, 'achil', 'aburizal@gmail.com', '$2y$12$GvJ8WPztPL2B7xfQiBqSduaYkrD7jyKI2gWXLRxpssgBJRQGg.DEK', 'admin', '2025-10-23 09:54:04');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_tokens`
--
ALTER TABLE `admin_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `fcm_token` (`token`),
  ADD UNIQUE KEY `token` (`token`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_code` (`order_code`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `table_id` (`table_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_products_active` (`is_active`);

--
-- Indexes for table `tables`
--
ALTER TABLE `tables`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_tokens`
--
ALTER TABLE `admin_tokens`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `tables`
--
ALTER TABLE `tables`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`table_id`) REFERENCES `tables` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
