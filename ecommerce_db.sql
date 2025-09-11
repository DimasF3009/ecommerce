-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 09, 2025 at 05:55 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ecommerce_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` varchar(36) NOT NULL,
  `buyer_id` varchar(36) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`id`, `buyer_id`, `created_at`) VALUES
('3aca7f62-87f6-11f0-bd7a-41bbe446af9d', '45665c6c-840c-11f0-bd3e-58ce2aa3a006', '2025-09-02 19:13:26'),
('3e97f401-8cb2-11f0-82f0-58ce2aa3a006', '37d7ae23-8cb2-11f0-82f0-58ce2aa3a006', '2025-09-08 19:49:23');

-- --------------------------------------------------------

--
-- Table structure for table `cart_item`
--

CREATE TABLE `cart_item` (
  `id` varchar(36) NOT NULL,
  `cart_id` varchar(36) NOT NULL,
  `product_id` varchar(36) NOT NULL,
  `quantity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart_item`
--

INSERT INTO `cart_item` (`id`, `cart_id`, `product_id`, `quantity`) VALUES
('59078b64-8cb5-11f0-82f0-58ce2aa3a006', '3e97f401-8cb2-11f0-82f0-58ce2aa3a006', '16f21c04-8411-11f0-bd3e-58ce2aa3a006', 1);

-- --------------------------------------------------------

--
-- Table structure for table `category`
--

CREATE TABLE `category` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `category`
--

INSERT INTO `category` (`id`, `name`, `created_at`) VALUES
(5, 'kue kering', '2025-09-06 00:51:33'),
(6, 'kue basah', '2025-09-06 00:51:33'),
(7, 'Lain-lain', '2025-09-08 09:27:39');

-- --------------------------------------------------------

--
-- Table structure for table `order_item`
--

CREATE TABLE `order_item` (
  `id` varchar(36) NOT NULL,
  `transaction_id` varchar(36) NOT NULL,
  `product_id` varchar(36) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price_at_purchase` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_item`
--

INSERT INTO `order_item` (`id`, `transaction_id`, `product_id`, `quantity`, `price_at_purchase`) VALUES
('05dd4fd9-8cb5-11f0-82f0-58ce2aa3a006', '68bed57c5cea2', '16f21c04-8411-11f0-bd3e-58ce2aa3a006', 1, 2000.00),
('072d04f2-8b97-11f0-9796-58ce2aa3a006', '68bcf5aa7d957', '16f21c04-8411-11f0-bd3e-58ce2aa3a006', 5, 1000.00),
('280627a1-8cb2-11f0-82f0-58ce2aa3a006', '68bed0ad2f857', '16f21c04-8411-11f0-bd3e-58ce2aa3a006', 1, 2000.00),
('29663800-8c62-11f0-9788-b07022575292', '68be4a77b92b1', '16f21c04-8411-11f0-bd3e-58ce2aa3a006', 3, 2000.00),
('2967eb59-8c62-11f0-9788-b07022575292', '68be4a77b92b1', '63bf73ef-8411-11f0-bd3e-58ce2aa3a006', 3, 999.00),
('4216c2a3-8cb2-11f0-82f0-58ce2aa3a006', '68bed0d8e1e70', '16f21c04-8411-11f0-bd3e-58ce2aa3a006', 1, 2000.00),
('49781df5-8c6d-11f0-9788-b07022575292', '68be5d2208ebf', '63bf73ef-8411-11f0-bd3e-58ce2aa3a006', 5, 999.00),
('741442d6-8cb4-11f0-82f0-58ce2aa3a006', '68bed487c1d93', '16f21c04-8411-11f0-bd3e-58ce2aa3a006', 1, 2000.00),
('e8c9e361-8b96-11f0-9796-58ce2aa3a006', '68bcf5777ebae', '16f21c04-8411-11f0-bd3e-58ce2aa3a006', 5, 1000.00),
('e8cb21d5-8b96-11f0-9796-58ce2aa3a006', '68bcf5777ebae', '63bf73ef-8411-11f0-bd3e-58ce2aa3a006', 2, 999.00),
('ebad59b5-8c6c-11f0-9788-b07022575292', '68be5c84a3976', '16f21c04-8411-11f0-bd3e-58ce2aa3a006', 5, 2000.00),
('fe33eeab-8cb1-11f0-82f0-58ce2aa3a006', '68bed06706f32', '16f21c04-8411-11f0-bd3e-58ce2aa3a006', 1, 2000.00);

-- --------------------------------------------------------

--
-- Table structure for table `product`
--

CREATE TABLE `product` (
  `id` varchar(36) NOT NULL,
  `seller_id` varchar(36) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock` int(11) NOT NULL,
  `image_url` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product`
--

INSERT INTO `product` (`id`, `seller_id`, `category_id`, `name`, `description`, `price`, `stock`, `image_url`, `created_at`) VALUES
('16f21c04-8411-11f0-bd3e-58ce2aa3a006', '62a2f169-840f-11f0-bd3e-58ce2aa3a006', 6, 'kue basah', 'mantap1', 2000.00, 83, 'assets/img/68b0635ba740f.jpeg', '2025-08-28 20:15:38'),
('63bf73ef-8411-11f0-bd3e-58ce2aa3a006', '62a2f169-840f-11f0-bd3e-58ce2aa3a006', 5, 'kue kering', 'mantap bgt', 999.00, 85, 'assets/img/68b0644c5be13.jpeg', '2025-08-28 20:17:47');

-- --------------------------------------------------------

--
-- Table structure for table `rating`
--

CREATE TABLE `rating` (
  `id` varchar(36) NOT NULL,
  `product_id` varchar(36) NOT NULL,
  `buyer_id` varchar(36) NOT NULL,
  `rating` int(11) DEFAULT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `review` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rating`
--

INSERT INTO `rating` (`id`, `product_id`, `buyer_id`, `rating`, `review`, `created_at`) VALUES
('a161ff20-8caa-11f0-82f0-58ce2aa3a006', '16f21c04-8411-11f0-bd3e-58ce2aa3a006', '45665c6c-840c-11f0-bd3e-58ce2aa3a006', 5, 'bagus', '2025-09-08 18:54:52');

-- --------------------------------------------------------

--
-- Table structure for table `transaction`
--

CREATE TABLE `transaction` (
  `id` varchar(36) NOT NULL,
  `buyer_id` varchar(36) NOT NULL,
  `shipping_address` text DEFAULT NULL,
  `payment_method` enum('transfer','ewallet') DEFAULT 'transfer',
  `total_price` decimal(10,2) NOT NULL,
  `shipping_cost` decimal(10,2) DEFAULT 0.00,
  `status` enum('pending','dibayar','dikirim','selesai','dibatalkan') DEFAULT 'pending',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transaction`
--

INSERT INTO `transaction` (`id`, `buyer_id`, `shipping_address`, `payment_method`, `total_price`, `shipping_cost`, `status`, `created_at`) VALUES
('68bcf5777ebae', '45665c6c-840c-11f0-bd3e-58ce2aa3a006', NULL, 'transfer', 21998.00, 15000.00, 'selesai', '2025-09-07 10:01:11'),
('68bcf5aa7d957', '45665c6c-840c-11f0-bd3e-58ce2aa3a006', NULL, 'transfer', 20000.00, 15000.00, 'selesai', '2025-09-07 10:02:02'),
('68be4a77b92b1', '45665c6c-840c-11f0-bd3e-58ce2aa3a006', NULL, 'transfer', 23997.00, 15000.00, 'selesai', '2025-09-08 10:16:07'),
('68be5c84a3976', '45665c6c-840c-11f0-bd3e-58ce2aa3a006', 'jl aman 12', 'transfer', 25000.00, 15000.00, 'selesai', '2025-09-08 11:33:08'),
('68be5d2208ebf', '45665c6c-840c-11f0-bd3e-58ce2aa3a006', NULL, 'transfer', 19995.00, 15000.00, 'selesai', '2025-09-08 11:35:46'),
('68bed06706f32', '45665c6c-840c-11f0-bd3e-58ce2aa3a006', NULL, 'transfer', 17000.00, 15000.00, 'selesai', '2025-09-08 19:47:35'),
('68bed0ad2f857', '45665c6c-840c-11f0-bd3e-58ce2aa3a006', NULL, 'transfer', 17000.00, 15000.00, 'selesai', '2025-09-08 19:48:45'),
('68bed0d8e1e70', '37d7ae23-8cb2-11f0-82f0-58ce2aa3a006', NULL, 'transfer', 17000.00, 15000.00, 'selesai', '2025-09-08 19:49:28'),
('68bed487c1d93', '37d7ae23-8cb2-11f0-82f0-58ce2aa3a006', NULL, 'transfer', 17000.00, 15000.00, 'selesai', '2025-09-08 20:05:11'),
('68bed57c5cea2', '37d7ae23-8cb2-11f0-82f0-58ce2aa3a006', NULL, 'transfer', 17000.00, 15000.00, 'selesai', '2025-09-08 20:09:16');

-- --------------------------------------------------------

--
-- Table structure for table `transaction_log`
--

CREATE TABLE `transaction_log` (
  `id` int(11) NOT NULL,
  `transaction_id` varchar(36) NOT NULL,
  `old_status` enum('pending','dibayar','dikirim','selesai','dibatalkan') DEFAULT NULL,
  `new_status` enum('pending','dibayar','dikirim','selesai','dibatalkan') DEFAULT NULL,
  `changed_by` varchar(36) NOT NULL,
  `changed_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `id` varchar(36) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('pembeli','penjual','admin') NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `store_description` text DEFAULT NULL,
  `store_logo` varchar(255) DEFAULT NULL,
  `status` enum('active','suspended') DEFAULT 'active',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `name`, `email`, `password`, `role`, `phone`, `address`, `store_description`, `store_logo`, `status`, `created_at`) VALUES
('37d7ae23-8cb2-11f0-82f0-58ce2aa3a006', 'halo', 'test3@gmail.com', '$2y$10$zk0rYicmiTbh73XQ.01daeUe0WvAywuS.bYLzcP3BWuqBfOnjq9hu', 'pembeli', NULL, '', NULL, NULL, 'suspended', '2025-09-08 19:49:11'),
('45665c6c-840c-11f0-bd3e-58ce2aa3a006', 'test', 'test@gmail.com', '$2y$10$qKh9rLx6ECANtpkT0OyWCe5dbw769icwvRKHmXHoK8K78t0Dpu5hi', 'pembeli', NULL, '', NULL, NULL, 'active', '2025-08-28 19:41:08'),
('62a2f169-840f-11f0-bd3e-58ce2aa3a006', 'tes', 'test2@gmail.com', '$2y$10$kPkw87F0/irex5zGaQKDfOYy.QD11.bbjcinDSYhPFd4XzBKcd.iK', 'penjual', '123', 'jl kanjut12', 'menjual banyak makanan sehat', 'assets/img/store/68be47b181179.png', 'active', '2025-08-28 20:03:26'),
('7e4ce941-8a73-11f0-add2-58ce2aa3a006', 'admin', 'admin@gmail.com', '$2y$10$i5uCPmjPcPsITVvj10C0jOHERa1ZAGfmtB7YQ5YGXx8dcmQDLq27O', 'admin', NULL, NULL, NULL, NULL, 'active', '2025-09-05 23:15:09');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD KEY `buyer_id` (`buyer_id`);

--
-- Indexes for table `cart_item`
--
ALTER TABLE `cart_item`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cart_id` (`cart_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `order_item`
--
ALTER TABLE `order_item`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_order_item_transaction` (`transaction_id`),
  ADD KEY `fk_order_item_product` (`product_id`);

--
-- Indexes for table `product`
--
ALTER TABLE `product`
  ADD PRIMARY KEY (`id`),
  ADD KEY `seller_id` (`seller_id`),
  ADD KEY `fk_product_category` (`category_id`);

--
-- Indexes for table `rating`
--
ALTER TABLE `rating`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_rating` (`buyer_id`,`product_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `transaction`
--
ALTER TABLE `transaction`
  ADD PRIMARY KEY (`id`),
  ADD KEY `buyer_id` (`buyer_id`);

--
-- Indexes for table `transaction_log`
--
ALTER TABLE `transaction_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `transaction_id` (`transaction_id`),
  ADD KEY `changed_by` (`changed_by`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `category`
--
ALTER TABLE `category`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `transaction_log`
--
ALTER TABLE `transaction_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`buyer_id`) REFERENCES `user` (`id`);

--
-- Constraints for table `cart_item`
--
ALTER TABLE `cart_item`
  ADD CONSTRAINT `cart_item_ibfk_1` FOREIGN KEY (`cart_id`) REFERENCES `cart` (`id`),
  ADD CONSTRAINT `cart_item_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`);

--
-- Constraints for table `order_item`
--
ALTER TABLE `order_item`
  ADD CONSTRAINT `fk_order_item_product` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`),
  ADD CONSTRAINT `fk_order_item_transaction` FOREIGN KEY (`transaction_id`) REFERENCES `transaction` (`id`);

--
-- Constraints for table `product`
--
ALTER TABLE `product`
  ADD CONSTRAINT `fk_product_category` FOREIGN KEY (`category_id`) REFERENCES `category` (`id`),
  ADD CONSTRAINT `product_ibfk_1` FOREIGN KEY (`seller_id`) REFERENCES `user` (`id`);

--
-- Constraints for table `rating`
--
ALTER TABLE `rating`
  ADD CONSTRAINT `rating_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`),
  ADD CONSTRAINT `rating_ibfk_2` FOREIGN KEY (`buyer_id`) REFERENCES `user` (`id`);

--
-- Constraints for table `transaction`
--
ALTER TABLE `transaction`
  ADD CONSTRAINT `transaction_ibfk_1` FOREIGN KEY (`buyer_id`) REFERENCES `user` (`id`);

--
-- Constraints for table `transaction_log`
--
ALTER TABLE `transaction_log`
  ADD CONSTRAINT `transaction_log_ibfk_1` FOREIGN KEY (`transaction_id`) REFERENCES `transaction` (`id`),
  ADD CONSTRAINT `transaction_log_ibfk_2` FOREIGN KEY (`changed_by`) REFERENCES `user` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
