-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 21, 2026 at 01:59 PM
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
-- Database: `inventoryy_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `items`
--

CREATE TABLE `items` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `category` varchar(50) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `uom` varchar(50) NOT NULL,
  `cost_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `selling_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `description` text DEFAULT NULL,
  `status` enum('approved','archived') DEFAULT 'approved',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `items`
--

INSERT INTO `items` (`id`, `name`, `category`, `quantity`, `uom`, `cost_price`, `selling_price`, `price`, `description`, `status`, `updated_at`) VALUES
(19, 'WinterMelon', 'Milktea', 20, '12oz', 39.00, 45.00, 0.00, '', 'approved', '2026-05-21 11:18:56'),
(20, 'WinterMelon', 'Milktea', 12, '16oz', 49.00, 55.00, 0.00, '', 'approved', '2026-05-21 11:29:54'),
(21, 'Macha', 'Milktea', 60, '12oz', 39.00, 45.00, 0.00, '', 'approved', '2026-05-21 11:50:40');

-- --------------------------------------------------------

--
-- Table structure for table `item_requests`
--

CREATE TABLE `item_requests` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `category` varchar(50) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `uom` varchar(50) NOT NULL,
  `cost_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `selling_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `user_id` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `status` enum('pending','approved') DEFAULT 'pending',
  `reason` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `admin_id`, `total_amount`, `created_at`) VALUES
(1, 1, 200.00, '2026-05-21 10:49:36'),
(2, 1, 280.00, '2026-05-21 10:49:36'),
(3, 1, 350.00, '2026-05-19 10:49:36'),
(4, 1, 420.00, '2026-05-17 10:49:36'),
(5, 1, 500.00, '2026-04-16 10:49:36');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `username` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('superadmin','admin','user') DEFAULT NULL,
  `status` enum('active','archived') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `username`, `email`, `password`, `role`, `status`) VALUES
(1, 'Super Admin', 'admin', NULL, '$2y$10$RgPfAHdE0WFzwtfqYvZttOTQNtihbeiAzbdWJCT9gpGpgED0/7mH.', 'superadmin', 'active'),
(2, 'angelo', 'angelo', 'angelo@gmail.com', 'angelo1234', 'superadmin', 'active'),
(5, 'Mark', 'Mark', NULL, '$2y$10$aLiM7zEkKCqF2n0nC54mf.d6K9B3rY/kxv4YLVcKjC/ivEqE4iAhm', 'user', 'active'),
(6, 'Melanie S.', 'enchang1', NULL, '$2y$10$XnofXHghCE8fBwy0TTaE6.VC8gcB7Rgn1JLEUAhqITm8Ir3y3njTC', 'admin', 'active'),
(7, 'Melanie S. Ocampo', 'enchang2', NULL, '$2y$10$r1ZG9Vea06kKVentuMAw1upQj8ctxCGEo0f2mER7IsALAVmBMkoJy', 'user', 'active'),
(8, 'Melanie S. Ocampo', 'enchang1', NULL, '$2y$10$PYYacBOWcP.xeQJ5J0iYAueg4I7qvSByBIS1x5XsZI0eJx8TZIMB2', 'user', 'active'),
(9, 'enchang ocampo', 'enchang1', NULL, '$2y$10$WEtF/VkNryf8xOyuy3kUVeGfevgZMZwvim5C0pGgbtzWcNBicvslG', 'user', 'active'),
(10, 'Saif Mohamed', 'saif', NULL, '$2y$10$d79p4cquO0OOwcqftYmm3e0sp/rXG.I1F3enRltP2ZYvFGBBZCCFq', 'user', 'active'),
(11, 'Saif', 'saif2', NULL, '$2y$10$DFug/mNpUAlmnMaHYpZquuRtiY3dLkziluoCq9WpJlFpQrO7br8qu', 'user', 'active'),
(12, 'mark', 'angelooo', NULL, '$2y$10$sX2pG7QdGoO779bov/FAre3DcNLGkNT89IyC6PrBWRpTxHaU06Nca', 'admin', 'active'),
(13, 'mark', 'angeloooo', NULL, '$2y$10$K9udK87hLQN7NVuGb8oHDeYRhpPABhbN6yxxdV38zhm3Un0us9w9y', 'admin', 'active'),
(14, 'ck', 'czk', NULL, '$2y$10$mCqGaVaqEIDjZ/Tq8xUUJeMGhyfY3Zp8XI3FNBMuDLm1tOOjdn4Pe', 'user', 'active'),
(15, 'czk', 'czkk', NULL, '$2y$10$8TluzaPZnNNxSn3I7t8goei4WNE2ZlC.AtRHYNQwyuFGa.fQQ5leW', 'admin', 'active'),
(16, 'markk', 'markkk', NULL, '$2y$10$VvDp/fFtFZxv/tMXdg/fLeaSf1vULbygPrQyWibxad1LEqqE3333C', 'user', 'active');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `items`
--
ALTER TABLE `items`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `item_requests`
--
ALTER TABLE `item_requests`
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
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `items`
--
ALTER TABLE `items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `item_requests`
--
ALTER TABLE `item_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
