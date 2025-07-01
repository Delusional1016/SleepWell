-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 29, 2025 at 05:09 PM
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
-- Database: `sleepwell_coffeeshop`
--

-- --------------------------------------------------------

--
-- Table structure for table `menu_items`
--

CREATE TABLE `menu_items` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `category` enum('food','beverage','appetizer') NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `menu_items`
--

INSERT INTO `menu_items` (`id`, `name`, `category`, `price`, `stock`) VALUES
(1, 'Croissant Sandwich', 'food', 6.50, 19),
(2, 'Avocado Toast', 'food', 8.00, 15),
(3, 'Pasta Primavera', 'food', 12.00, 10),
(4, 'Espresso', 'beverage', 3.50, 50),
(5, 'Cappuccino', 'beverage', 4.50, 40),
(6, 'Iced Latte', 'beverage', 5.00, 30),
(7, 'Mozzarella Sticks', 'appetizer', 5.50, 25),
(8, 'Bruschetta', 'appetizer', 6.00, 20),
(9, 'Stuffed Mushrooms', 'appetizer', 7.00, 14),
(10, 'Croissant', 'food', 3.50, 20),
(11, 'Grilled Cheese Sandwich', 'food', 5.99, 15),
(12, 'Chicken Caesar Wrap', 'food', 7.49, 12),
(13, 'Blueberry Muffin', 'food', 2.99, 25),
(14, 'Espresso', 'beverage', 2.99, 50),
(15, 'Matcha Latte', 'beverage', 4.50, 30),
(16, 'Iced Mocha', 'beverage', 4.99, 40),
(17, 'Mozzarella Sticks', 'appetizer', 4.99, 18),
(18, 'Bruschetta', 'appetizer', 5.49, 15),
(19, 'Stuffed Mushrooms', 'appetizer', 6.99, 10),
(20, 'Chai Latte', 'beverage', 4.25, 35);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `order_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `item_id`, `quantity`, `order_date`) VALUES
(1, 1, 1, 1, '2025-06-29 14:58:36'),
(2, 1, 9, 1, '2025-06-29 14:58:52'),
(3, 1, 5, 1, '2025-06-29 15:07:41');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `account_type` enum('user','admin') NOT NULL,
  `is_verified` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `account_type`, `is_verified`) VALUES
(1, 'user', '$2y$10$IJQkEo/kQd0riiuTCwopquP44D1m/2EePRg5Ggc0Eop7KR5kk8y62', 'user', 1),
(2, 'admin', '$2y$10$KekgSM3zlFEI4jtDmwm1PeWalcePMncOkEFkoMK0ELuxZj60Xu3mu', 'admin', 1),
(3, 'admin2', '$2y$10$MCDXlvDaTQo0q3MTwtV1EuaBhcGRhTyvZcGgyhAoSML9YaI28aEq2', 'admin', 1);

--
-- Indexes for dumped tables
--

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
  ADD KEY `user_id` (`user_id`),
  ADD KEY `item_id` (`item_id`);

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
-- AUTO_INCREMENT for table `menu_items`
--
ALTER TABLE `menu_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `menu_items` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
