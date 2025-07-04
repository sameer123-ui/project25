-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jun 12, 2025 at 06:13 AM
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
-- Database: `restaurant_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `module` varchar(50) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `feedback`
--

INSERT INTO `feedback` (`id`, `user_id`, `module`, `message`, `created_at`) VALUES
(1, 15, 'payment', '111', '2025-06-12 03:35:24'),
(2, 15, 'payment', '111', '2025-06-12 03:38:18'),
(3, 15, 'payment', '111', '2025-06-12 03:38:39');

-- --------------------------------------------------------

--
-- Table structure for table `menu`
--

CREATE TABLE `menu` (
  `id` int(11) NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `category` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `menu`
--

INSERT INTO `menu` (`id`, `item_name`, `description`, `price`, `category`) VALUES
(1, 'Chicken Momo', 'Steam/ Fry/ Jhol', 150.00, 'Momo'),
(2, 'Buff Momo', 'Steam/ Fry/ Jhol', 120.00, 'Momo'),
(3, 'Veg Momo', 'Steam/ Fry/ Jhol', 100.00, 'Momo'),
(4, 'Americano', 'Cold/ Hot', 400.00, 'Coffee'),
(5, 'Capuccinio', 'Cold / Hot', 450.00, 'Coffee'),
(6, 'Cafe Latte', 'Cold', 250.00, 'Coffee'),
(7, 'Cafe Mocha', 'Cold', 250.00, 'Coffee'),
(8, 'Black Tea', 'Tea', 25.00, 'Tea'),
(9, 'Milk tea', 'Normal Tea', 30.00, 'Tea'),
(10, 'Masala Milk tea', 'Masala', 50.00, 'Tea'),
(11, 'Chicken Fried rice', 'Fried rice', 200.00, 'Fried rice'),
(12, 'Buff Fried rice', 'Fried rice', 180.00, 'Fried rice'),
(13, 'Veg Fried rice', 'Fried rice', 100.00, 'Fried rice'),
(14, 'Hukka', 'Mint \r\nDouble Apple\r\nBlueberry', 400.00, 'Hukka'),
(15, 'Veg Chowmein', '', 150.00, 'Chowmein'),
(16, 'Buff Chowmein', '', 170.00, 'Chowmein'),
(17, 'Chicken Chowmein', '', 180.00, 'Chowmein'),
(19, 'Virgin Mojito', 'Cold', 280.00, 'Drinks'),
(20, 'Ruslan Vodka', 'Vodka', 150.00, 'Hard Drinks'),
(21, 'Red label', 'Whiskey', 200.00, 'Hard Drinks'),
(22, 'Khurkuri Rum', 'Rum', 200.00, 'Hard Drinks'),
(23, 'Nepal Ice string', 'Beer', 150.00, 'Hard Drinks'),
(24, 'Tuborg', 'Beer', 150.00, 'Hard Drinks'),
(25, 'Coke/ Fanta/ Deu', '', 100.00, 'Soft Drinks'),
(26, 'Chicken Wings', 'Spicy/ Normal', 200.00, 'Wings'),
(27, 'Fried Chicken', '8 Pc', 1500.00, 'Bucket Chicken'),
(28, 'Fried Chicken', '4 Pc', 800.00, 'Bucket Chicken'),
(29, 'Chicken Burger', 'Nomal / Spicy', 200.00, 'Burger'),
(30, 'Veg Burger', 'Normal', 150.00, 'Burger'),
(31, 'Cheese Burger', 'Nomal', 210.00, 'Burger'),
(32, 'Buff / Chicken? Pork', '', 200.00, 'Sekuwa'),
(33, 'Chicken Sausage', 'Normal', 60.00, 'Sausage'),
(34, 'Chicken Sausage', 'Normal', 50.00, 'Sausage'),
(35, 'Mint lemonade', 'Normal', 110.00, 'Drinks'),
(36, 'Mint lemonade', 'With ice', 130.00, 'Drinks');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `order_date` datetime DEFAULT current_timestamp(),
  `order_details` text DEFAULT NULL,
  `total` decimal(10,2) NOT NULL,
  `status` varchar(50) DEFAULT NULL,
  `assigned_staff_id` int(11) DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `order_type` varchar(20) NOT NULL DEFAULT 'pickup',
  `delivery_address` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `order_date`, `order_details`, `total`, `status`, `assigned_staff_id`, `payment_method`, `order_type`, `delivery_address`) VALUES
(1, 5, '2025-05-15 13:28:22', '[{\"id\":1,\"name\":\"Chicken Momo\",\"quantity\":1,\"price\":\"150.00\",\"subtotal\":150}]', 150.00, 'completed', 6, NULL, 'pickup', NULL),
(2, 5, '2025-05-19 12:26:03', '[{\"id\":2,\"name\":\"Buff Momo\",\"quantity\":1,\"price\":\"120.00\",\"subtotal\":120},{\"id\":1,\"name\":\"Chicken Momo\",\"quantity\":1,\"price\":\"150.00\",\"subtotal\":150},{\"id\":3,\"name\":\"Veg Momo\",\"quantity\":1,\"price\":\"100.00\",\"subtotal\":100}]', 370.00, 'completed', 6, NULL, 'pickup', NULL),
(3, 5, '2025-05-19 12:26:44', '[{\"id\":2,\"name\":\"Buff Momo\",\"quantity\":1,\"price\":\"120.00\",\"subtotal\":120},{\"id\":1,\"name\":\"Chicken Momo\",\"quantity\":1,\"price\":\"150.00\",\"subtotal\":150},{\"id\":3,\"name\":\"Veg Momo\",\"quantity\":1,\"price\":\"100.00\",\"subtotal\":100}]', 370.00, 'completed', 6, NULL, 'pickup', NULL),
(4, 5, '2025-05-19 13:23:12', '[{\"id\":2,\"name\":\"Buff Momo\",\"quantity\":4,\"price\":\"120.00\",\"subtotal\":480}]', 480.00, 'completed', 7, 'Cash', 'pickup', NULL),
(5, 5, '2025-05-21 21:22:25', '[{\"id\":2,\"name\":\"Buff Momo\",\"quantity\":1,\"price\":\"120.00\",\"subtotal\":120},{\"id\":1,\"name\":\"Chicken Momo\",\"quantity\":1,\"price\":\"150.00\",\"subtotal\":150},{\"id\":3,\"name\":\"Veg Momo\",\"quantity\":3,\"price\":\"100.00\",\"subtotal\":300}]', 570.00, 'completed', 6, 'Cash', 'pickup', NULL),
(6, 5, '2025-05-21 21:26:32', '[{\"id\":2,\"name\":\"Buff Momo\",\"quantity\":5,\"price\":\"120.00\",\"subtotal\":600}]', 600.00, 'completed', 7, 'Cash', 'pickup', NULL),
(7, 5, '2025-05-21 21:29:13', '[{\"id\":2,\"name\":\"Buff Momo\",\"quantity\":4,\"price\":\"120.00\",\"subtotal\":480},{\"id\":1,\"name\":\"Chicken Momo\",\"quantity\":4,\"price\":\"150.00\",\"subtotal\":600},{\"id\":3,\"name\":\"Veg Momo\",\"quantity\":4,\"price\":\"100.00\",\"subtotal\":400}]', 1480.00, 'completed', 7, 'Cash', 'pickup', NULL),
(8, 5, '2025-05-21 21:32:19', '[{\"id\":3,\"name\":\"Veg Momo\",\"quantity\":1,\"price\":\"100.00\",\"subtotal\":100}]', 100.00, 'completed', 7, 'UPI', 'pickup', NULL),
(9, 5, '2025-05-21 21:36:45', '[{\"id\":2,\"name\":\"Buff Momo\",\"quantity\":1,\"price\":\"120.00\",\"subtotal\":120}]', 120.00, 'completed', 7, 'UPI', 'pickup', NULL),
(10, 5, '2025-05-21 21:43:46', '[{\"id\":2,\"name\":\"Buff Momo\",\"quantity\":3,\"price\":\"120.00\",\"subtotal\":360}]', 360.00, 'completed', 6, 'Cash', 'pickup', NULL),
(11, 11, '2025-05-30 07:30:09', '[{\"id\":5,\"name\":\"capuccinio\",\"quantity\":1,\"price\":\"450.00\",\"subtotal\":450}]', 450.00, 'preparing', 6, 'Cash', 'pickup', NULL),
(12, 14, '2025-05-30 07:48:14', '[{\"id\":4,\"name\":\"Americano\",\"quantity\":1,\"price\":\"400.00\",\"subtotal\":400}]', 400.00, 'cancelled', 13, 'Cash', 'pickup', NULL),
(13, 14, '2025-05-30 07:55:51', '[{\"id\":4,\"name\":\"Americano\",\"quantity\":1,\"price\":\"400.00\",\"subtotal\":400},{\"id\":2,\"name\":\"Buff Momo\",\"quantity\":1,\"price\":\"120.00\",\"subtotal\":120}]', 520.00, 'cancelled', 13, 'Cash', 'pickup', NULL),
(14, 15, '2025-06-03 12:24:46', '[{\"id\":4,\"name\":\"Americano\",\"quantity\":1,\"price\":\"400.00\",\"subtotal\":400}]', 400.00, 'completed', 17, 'Cash', 'pickup', NULL),
(15, 15, '2025-06-09 11:59:38', '[{\"id\":27,\"name\":\"Fried Chicken\",\"quantity\":1,\"price\":\"1500.00\",\"subtotal\":1500}]', 1500.00, 'completed', 20, 'Cash', 'pickup', NULL),
(16, 15, '2025-06-12 09:07:46', '[{\"id\":27,\"name\":\"Fried Chicken\",\"quantity\":1,\"price\":\"1500.00\",\"subtotal\":1500},{\"id\":31,\"name\":\"Cheese Burger\",\"quantity\":1,\"price\":\"210.00\",\"subtotal\":210}]', 1710.00, 'completed', 13, 'Cash', 'pickup', NULL),
(17, 15, '2025-06-12 09:13:13', '[{\"id\":31,\"name\":\"Cheese Burger\",\"quantity\":1,\"price\":\"210.00\",\"subtotal\":210}]', 210.00, 'pending', 13, 'Cash', 'delivery', 'Batisputali');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `menu_id` int(11) NOT NULL,
  `quantity` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `restaurant_tables`
--

CREATE TABLE `restaurant_tables` (
  `id` int(11) NOT NULL,
  `table_number` varchar(10) NOT NULL,
  `capacity` int(11) NOT NULL,
  `location` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

CREATE TABLE `staff` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `position` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `hire_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `staff`
--

INSERT INTO `staff` (`id`, `name`, `position`, `email`, `phone`, `hire_date`) VALUES
(1, 'John Doe', 'Waiter', 'john@example.com', '1234567890', '2023-01-15'),
(2, 'Jane Smith', 'Chef', 'jane@example.com', '0987654321', '2022-12-01'),
(3, 'Alice Brown', 'Manager', 'alice@example.com', NULL, '2021-06-20');

-- --------------------------------------------------------

--
-- Table structure for table `tables`
--

CREATE TABLE `tables` (
  `table_number` int(11) NOT NULL,
  `capacity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tables`
--

INSERT INTO `tables` (`table_number`, `capacity`) VALUES
(1, 2),
(2, 2),
(3, 4),
(4, 4),
(5, 6),
(6, 6),
(7, 8),
(8, 8),
(9, 10),
(10, 10);

-- --------------------------------------------------------

--
-- Table structure for table `table_bookings`
--

CREATE TABLE `table_bookings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `table_number` int(11) DEFAULT NULL,
  `booking_date` datetime NOT NULL,
  `status` varchar(50) DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `people_count` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `table_bookings`
--

INSERT INTO `table_bookings` (`id`, `user_id`, `table_number`, `booking_date`, `status`, `created_at`, `updated_at`, `people_count`) VALUES
(1, 5, 1, '2025-05-22 12:54:00', 'completed', '2025-05-22 07:10:15', '2025-06-03 06:37:21', 1),
(2, 11, 2, '2025-05-22 13:05:00', 'cancelled', '2025-05-22 07:20:42', '2025-05-22 07:21:04', 1),
(3, 14, 1, '2025-05-30 08:56:00', 'completed', '2025-05-30 02:11:13', '2025-06-03 06:37:17', 1),
(4, 14, 1, '2025-06-04 12:21:00', 'cancelled', '2025-06-03 06:36:21', '2025-06-03 06:37:25', 1),
(5, 14, 1, '2025-06-04 14:32:00', 'completed', '2025-06-04 07:47:52', '2025-06-04 08:07:15', 1),
(6, 15, 10, '2025-06-05 14:37:00', 'completed', '2025-06-04 07:53:08', '2025-06-05 01:40:47', 3),
(7, 14, 1, '2025-06-04 14:44:00', 'cancelled', '2025-06-04 07:59:58', '2025-06-04 08:01:38', 1),
(8, 14, 2, '2025-06-04 14:44:00', 'cancelled', '2025-06-04 08:00:46', '2025-06-04 08:01:44', 1),
(9, 15, 1, '2025-06-04 13:59:00', 'completed', '2025-06-04 08:08:21', '2025-06-04 08:14:11', 1),
(10, 15, 1, '2025-06-05 08:22:00', 'completed', '2025-06-05 01:38:02', '2025-06-05 01:40:50', 3);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','staff','user') DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`) VALUES
(5, 'kushal1', '$2y$10$VzVkcPD6vTN3fhfZ4BITS.ATzf5kpBIATnD.KTuz40JBr4Cf25Z26', 'user'),
(11, 'yalu', '$2y$10$eHWwjzFI4paIhRH937YgtOu0VZILjX7f5GAEkFkQI4zFZjEdTALW6', 'user'),
(12, 'sameer51', '6cb3e705f27a7937744f8bffa72c7a023d8e90ad6eb50db182855ea1cc69d33a', 'admin'),
(13, 'sushil2', '9801ef25a37feeaacea12157acb336a61367730b746bfd96c28d80072339a90e', 'staff'),
(14, 'yalu1', '85314765f0e2692d7d58df693f1b7fc81458dbeade336db6c497aac65b83c10a', 'user'),
(15, 'test', '85314765f0e2692d7d58df693f1b7fc81458dbeade336db6c497aac65b83c10a', 'user'),
(20, 'sahaj', '9801ef25a37feeaacea12157acb336a61367730b746bfd96c28d80072339a90e', 'staff'),
(22, 'test1', '85314765f0e2692d7d58df693f1b7fc81458dbeade336db6c497aac65b83c10a', 'user');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `menu`
--
ALTER TABLE `menu`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `menu_id` (`menu_id`);

--
-- Indexes for table `restaurant_tables`
--
ALTER TABLE `restaurant_tables`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `staff`
--
ALTER TABLE `staff`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tables`
--
ALTER TABLE `tables`
  ADD PRIMARY KEY (`table_number`);

--
-- Indexes for table `table_bookings`
--
ALTER TABLE `table_bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `menu`
--
ALTER TABLE `menu`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `restaurant_tables`
--
ALTER TABLE `restaurant_tables`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `staff`
--
ALTER TABLE `staff`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `table_bookings`
--
ALTER TABLE `table_bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `feedback`
--
ALTER TABLE `feedback`
  ADD CONSTRAINT `feedback_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`menu_id`) REFERENCES `menu` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `table_bookings`
--
ALTER TABLE `table_bookings`
  ADD CONSTRAINT `table_bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
