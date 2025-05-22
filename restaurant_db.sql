-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: May 22, 2025 at 09:42 AM
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
(5, 'capuccinio', 'Cold / Hot', 450.00, 'Coffee');

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
  `payment_method` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `order_date`, `order_details`, `total`, `status`, `assigned_staff_id`, `payment_method`) VALUES
(1, 5, '2025-05-15 13:28:22', '[{\"id\":1,\"name\":\"Chicken Momo\",\"quantity\":1,\"price\":\"150.00\",\"subtotal\":150}]', 150.00, 'completed', 6, NULL),
(2, 5, '2025-05-19 12:26:03', '[{\"id\":2,\"name\":\"Buff Momo\",\"quantity\":1,\"price\":\"120.00\",\"subtotal\":120},{\"id\":1,\"name\":\"Chicken Momo\",\"quantity\":1,\"price\":\"150.00\",\"subtotal\":150},{\"id\":3,\"name\":\"Veg Momo\",\"quantity\":1,\"price\":\"100.00\",\"subtotal\":100}]', 370.00, 'completed', 6, NULL),
(3, 5, '2025-05-19 12:26:44', '[{\"id\":2,\"name\":\"Buff Momo\",\"quantity\":1,\"price\":\"120.00\",\"subtotal\":120},{\"id\":1,\"name\":\"Chicken Momo\",\"quantity\":1,\"price\":\"150.00\",\"subtotal\":150},{\"id\":3,\"name\":\"Veg Momo\",\"quantity\":1,\"price\":\"100.00\",\"subtotal\":100}]', 370.00, 'completed', 6, NULL),
(4, 5, '2025-05-19 13:23:12', '[{\"id\":2,\"name\":\"Buff Momo\",\"quantity\":4,\"price\":\"120.00\",\"subtotal\":480}]', 480.00, 'completed', 7, 'Cash'),
(5, 5, '2025-05-21 21:22:25', '[{\"id\":2,\"name\":\"Buff Momo\",\"quantity\":1,\"price\":\"120.00\",\"subtotal\":120},{\"id\":1,\"name\":\"Chicken Momo\",\"quantity\":1,\"price\":\"150.00\",\"subtotal\":150},{\"id\":3,\"name\":\"Veg Momo\",\"quantity\":3,\"price\":\"100.00\",\"subtotal\":300}]', 570.00, 'completed', 6, 'Cash'),
(6, 5, '2025-05-21 21:26:32', '[{\"id\":2,\"name\":\"Buff Momo\",\"quantity\":5,\"price\":\"120.00\",\"subtotal\":600}]', 600.00, 'completed', 7, 'Cash'),
(7, 5, '2025-05-21 21:29:13', '[{\"id\":2,\"name\":\"Buff Momo\",\"quantity\":4,\"price\":\"120.00\",\"subtotal\":480},{\"id\":1,\"name\":\"Chicken Momo\",\"quantity\":4,\"price\":\"150.00\",\"subtotal\":600},{\"id\":3,\"name\":\"Veg Momo\",\"quantity\":4,\"price\":\"100.00\",\"subtotal\":400}]', 1480.00, 'completed', 7, 'Cash'),
(8, 5, '2025-05-21 21:32:19', '[{\"id\":3,\"name\":\"Veg Momo\",\"quantity\":1,\"price\":\"100.00\",\"subtotal\":100}]', 100.00, 'completed', 7, 'UPI'),
(9, 5, '2025-05-21 21:36:45', '[{\"id\":2,\"name\":\"Buff Momo\",\"quantity\":1,\"price\":\"120.00\",\"subtotal\":120}]', 120.00, 'completed', 7, 'UPI'),
(10, 5, '2025-05-21 21:43:46', '[{\"id\":2,\"name\":\"Buff Momo\",\"quantity\":3,\"price\":\"120.00\",\"subtotal\":360}]', 360.00, 'completed', 6, 'Cash');

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
-- Table structure for table `table_bookings`
--

CREATE TABLE `table_bookings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `table_number` int(11) NOT NULL,
  `booking_date` datetime NOT NULL,
  `status` varchar(50) DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `table_bookings`
--

INSERT INTO `table_bookings` (`id`, `user_id`, `table_number`, `booking_date`, `status`, `created_at`, `updated_at`) VALUES
(1, 5, 1, '2025-05-22 12:54:00', 'confirmed', '2025-05-22 07:10:15', '2025-05-22 07:12:48'),
(2, 11, 2, '2025-05-22 13:05:00', 'cancelled', '2025-05-22 07:20:42', '2025-05-22 07:21:04');

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
(4, 'sameer12', '$2y$10$B4vTQa6knm4jztXa05FfXO/eXFiLyeKamHbbDYQzBHKo2sxEnz32e', 'admin'),
(5, 'kushal1', '$2y$10$VzVkcPD6vTN3fhfZ4BITS.ATzf5kpBIATnD.KTuz40JBr4Cf25Z26', 'user'),
(6, 'sushil', '$2y$10$oXva3oTuwjafZsAPMuKusudQmYUgdm1lC0S4KR1gU5pVPkm0KrFFS', 'staff'),
(7, 'sahaj', '$2y$10$s5dOukW8R.FdWDN3CpQ0te9wKW1RO2IXynCgozjcIaMr0LbsrPGLK', 'staff'),
(11, 'yalu', '$2y$10$eHWwjzFI4paIhRH937YgtOu0VZILjX7f5GAEkFkQI4zFZjEdTALW6', 'user');

--
-- Indexes for dumped tables
--

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
-- Indexes for table `staff`
--
ALTER TABLE `staff`
  ADD PRIMARY KEY (`id`);

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
-- AUTO_INCREMENT for table `menu`
--
ALTER TABLE `menu`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `staff`
--
ALTER TABLE `staff`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `table_bookings`
--
ALTER TABLE `table_bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `table_bookings`
--
ALTER TABLE `table_bookings`
  ADD CONSTRAINT `table_bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
