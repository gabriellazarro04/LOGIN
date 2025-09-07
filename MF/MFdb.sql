-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Sep 03, 2025 at 10:46 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.0.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `MFdb`
--

-- --------------------------------------------------------

--
-- Table structure for table `communication_actions`
--

CREATE TABLE `communication_actions` (
  `id` int(11) NOT NULL,
  `action_name` varchar(100) NOT NULL,
  `action_type` enum('Broadcast','Alert','Request','Update') NOT NULL,
  `target_vehicle` varchar(100) NOT NULL,
  `priority_level` enum('Low','Normal','High','Emergency') DEFAULT 'Normal',
  `message_content` text NOT NULL,
  `status` enum('Active','Completed','Cancelled') DEFAULT 'Active',
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `communication_actions`
--

INSERT INTO `communication_actions` (`id`, `action_name`, `action_type`, `target_vehicle`, `priority_level`, `message_content`, `status`, `created_by`, `created_at`, `updated_at`) VALUES
(2, 'Fuel Status Request', 'Request', 'All', 'Normal', 'Please report current fuel levels for monthly tracking.', 'Completed', 2, '2025-09-03 18:20:49', '2025-09-03 18:20:49'),
(5, 'pota', 'Alert', 'Delivery Van #102', 'High', 'potaka', 'Active', 2, '2025-09-03 18:50:37', '2025-09-03 18:50:37');

-- --------------------------------------------------------

--
-- Table structure for table `conversations`
--

CREATE TABLE `conversations` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `vehicle_id` int(11) NOT NULL,
  `last_message_id` int(11) DEFAULT NULL,
  `unread_count` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `receiver_type` enum('user','vehicle') NOT NULL,
  `message_type_id` int(11) NOT NULL,
  `priority_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `status` enum('sent','delivered','read','failed') DEFAULT 'sent',
  `sent_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `delivered_at` datetime DEFAULT NULL,
  `read_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `receiver_type`, `message_type_id`, `priority_id`, `content`, `status`, `sent_at`, `delivered_at`, `read_at`) VALUES
(1, 3, 2, 'user', 1, 3, 'Hi dispatch, I\'m running about 15 minutes behind schedule due to traffic on the highway.', 'read', '2025-09-03 17:50:49', NULL, NULL),
(2, 2, 3, 'user', 2, 3, 'Understood. Please update when you\'re back on schedule.', 'delivered', '2025-09-03 17:52:49', NULL, NULL),
(3, 3, 2, 'user', 1, 3, 'Traffic is clearing now. ETA to next stop is 12 minutes.', 'read', '2025-09-03 17:57:49', NULL, NULL),
(4, 2, 1, 'vehicle', 2, 3, 'Please proceed to the next delivery point. Over.', 'delivered', '2025-09-03 19:01:08', NULL, NULL),
(5, 2, 1, 'vehicle', 4, 2, 'Route change: Avoid Main Street due to construction. Use Oak Street instead.', 'read', '2025-09-03 19:01:08', NULL, NULL),
(6, 2, 2, 'vehicle', 2, 3, 'Temperature check required. Please confirm refrigeration is working properly.', 'sent', '2025-09-03 19:01:08', NULL, NULL),
(7, 2, 4, 'vehicle', 1, 4, 'Maintenance completed. You are cleared for operation.', 'delivered', '2025-09-03 19:01:08', NULL, NULL),
(8, 2, 3, 'vehicle', 1, 3, 'oo na', 'sent', '2025-09-03 19:01:42', NULL, NULL),
(9, 2, 3, 'vehicle', 1, 3, 'tanginamo e', 'sent', '2025-09-03 19:01:51', NULL, NULL),
(10, 2, 1, 'vehicle', 1, 3, 'lipat', 'sent', '2025-09-03 19:02:01', NULL, NULL),
(11, 2, 1, 'vehicle', 1, 3, 'ptoa', 'sent', '2025-09-03 19:02:37', NULL, NULL),
(12, 2, 2, 'vehicle', 1, 3, 'hoy', 'sent', '2025-09-03 19:04:29', NULL, NULL),
(13, 2, 4, 'vehicle', 1, 3, 'shyet ka', 'sent', '2025-09-03 19:04:36', NULL, NULL),
(14, 2, 3, 'vehicle', 1, 3, 'a', 'sent', '2025-09-03 19:57:06', NULL, NULL),
(15, 2, 3, 'vehicle', 1, 3, 'a', 'sent', '2025-09-03 19:57:06', NULL, NULL),
(16, 2, 3, 'vehicle', 1, 3, 'a', 'sent', '2025-09-03 19:57:07', NULL, NULL),
(17, 2, 3, 'vehicle', 1, 3, 'a', 'sent', '2025-09-03 19:57:07', NULL, NULL),
(18, 2, 3, 'vehicle', 1, 3, 'asd', 'sent', '2025-09-03 19:57:16', NULL, NULL),
(19, 2, 3, 'vehicle', 1, 3, 'asd', 'sent', '2025-09-03 19:57:17', NULL, NULL),
(20, 2, 3, 'vehicle', 1, 3, 'asd', 'sent', '2025-09-03 19:57:17', NULL, NULL),
(21, 2, 3, 'vehicle', 1, 3, 'asd', 'sent', '2025-09-03 19:57:17', NULL, NULL),
(22, 2, 3, 'vehicle', 1, 3, 'asd', 'sent', '2025-09-03 19:57:18', NULL, NULL),
(23, 2, 3, 'vehicle', 1, 3, 'asd', 'sent', '2025-09-03 19:57:18', NULL, NULL),
(24, 2, 3, 'vehicle', 1, 3, 'a', 'sent', '2025-09-03 19:58:17', NULL, NULL),
(25, 2, 3, 'vehicle', 1, 3, 'a', 'sent', '2025-09-03 19:58:18', NULL, NULL),
(26, 2, 3, 'vehicle', 1, 3, 'a', 'sent', '2025-09-03 19:58:18', NULL, NULL),
(27, 2, 3, 'vehicle', 1, 3, 'a', 'sent', '2025-09-03 19:58:18', NULL, NULL),
(28, 2, 3, 'vehicle', 1, 3, 'a', 'sent', '2025-09-03 19:58:23', NULL, NULL),
(29, 2, 3, 'vehicle', 1, 3, 'a', 'sent', '2025-09-03 19:58:24', NULL, NULL),
(30, 2, 3, 'vehicle', 1, 3, 'asd', 'sent', '2025-09-03 19:58:34', NULL, NULL),
(31, 2, 3, 'vehicle', 1, 3, 'asd', 'sent', '2025-09-03 19:58:34', NULL, NULL),
(32, 2, 3, 'vehicle', 1, 3, 'as', 'sent', '2025-09-03 20:03:08', NULL, NULL),
(33, 2, 1, 'vehicle', 1, 3, 'asd', 'sent', '2025-09-03 20:03:25', NULL, NULL),
(34, 2, 1, 'vehicle', 1, 3, 'adasd', 'sent', '2025-09-03 20:03:39', NULL, NULL),
(35, 2, 3, 'vehicle', 1, 3, 'Uploaded file: 68b896177f8bb8.42922252.png [File ID: 6]', 'sent', '2025-09-03 20:10:44', NULL, NULL),
(36, 2, 3, 'vehicle', 1, 3, 'Uploaded file: 68b896177f8bb8.42922252.png [File ID: 7]', 'sent', '2025-09-03 20:11:54', NULL, NULL),
(37, 2, 3, 'vehicle', 1, 3, 'a', 'sent', '2025-09-03 20:12:05', NULL, NULL),
(38, 2, 1, 'vehicle', 1, 3, 'Uploaded file: 68b8a0c47d4539.10535205.png [File ID: 8]', 'sent', '2025-09-03 20:12:32', NULL, NULL),
(39, 2, 3, 'vehicle', 1, 3, 'Uploaded file: 68b8a0c47d4539.10535205.png [File ID: 9]', 'sent', '2025-09-03 20:12:44', NULL, NULL),
(40, 2, 3, 'vehicle', 1, 3, 'Uploaded file: HOUSE RULES.pdf [File ID: 10]', 'sent', '2025-09-03 20:12:53', NULL, NULL),
(41, 2, 3, 'vehicle', 1, 3, 'tangina ayaw mamessage', 'sent', '2025-09-03 20:14:31', NULL, NULL),
(42, 2, 3, 'vehicle', 1, 3, 'Uploaded file: viahale1.png [File ID: 11]', 'sent', '2025-09-03 20:20:38', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `message_types`
--

CREATE TABLE `message_types` (
  `id` int(11) NOT NULL,
  `type_name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `message_types`
--

INSERT INTO `message_types` (`id`, `type_name`, `description`) VALUES
(1, 'Status Update', 'General status update from vehicle or dispatcher'),
(2, 'Instruction', 'Specific instructions from dispatcher to driver'),
(3, 'Emergency', 'Emergency communication'),
(4, 'Routing', 'Route changes or updates');

-- --------------------------------------------------------

--
-- Table structure for table `priorities`
--

CREATE TABLE `priorities` (
  `id` int(11) NOT NULL,
  `level_name` varchar(20) NOT NULL,
  `severity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `priorities`
--

INSERT INTO `priorities` (`id`, `level_name`, `severity`) VALUES
(1, 'Emergency', 1),
(2, 'High', 2),
(3, 'Normal', 3),
(4, 'Low', 4);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `role` enum('admin','dispatcher','driver') DEFAULT 'dispatcher',
  `status` enum('active','inactive') DEFAULT 'active',
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `email`, `first_name`, `last_name`, `role`, `status`, `last_login`, `created_at`, `updated_at`) VALUES
(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@fleetcom.com', 'System', 'Administrator', 'admin', 'active', NULL, '2025-09-03 18:20:49', '2025-09-03 18:20:49'),
(2, 'dispatcher1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'dispatch@fleetcom.com', 'John', 'Dispatcher', 'dispatcher', 'active', NULL, '2025-09-03 18:20:49', '2025-09-03 18:20:49'),
(3, 'driver102', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'driver102@fleetcom.com', 'Mike', 'Driver', 'driver', 'active', NULL, '2025-09-03 18:20:49', '2025-09-03 18:20:49');

-- --------------------------------------------------------

--
-- Table structure for table `vehicles`
--

CREATE TABLE `vehicles` (
  `id` int(11) NOT NULL,
  `vehicle_name` varchar(100) NOT NULL,
  `vehicle_type` enum('delivery_van','refrigerator_truck','cargo_truck','van') NOT NULL,
  `identifier` varchar(50) NOT NULL,
  `status` enum('online','offline') DEFAULT 'offline',
  `operational_status` enum('in_transit','idle','maintenance','out_of_service') DEFAULT 'idle',
  `current_driver_id` int(11) DEFAULT NULL,
  `last_check_in` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vehicles`
--

INSERT INTO `vehicles` (`id`, `vehicle_name`, `vehicle_type`, `identifier`, `status`, `operational_status`, `current_driver_id`, `last_check_in`, `created_at`, `updated_at`) VALUES
(1, 'Delivery Van', 'delivery_van', '#102', 'online', 'in_transit', 3, NULL, '2025-09-03 18:20:49', '2025-09-03 18:20:49'),
(2, 'Refrigerator Truck', 'refrigerator_truck', '#205', 'online', 'idle', NULL, NULL, '2025-09-03 18:20:49', '2025-09-03 18:20:49'),
(3, 'Cargo Truck', 'cargo_truck', '#308', 'offline', 'out_of_service', NULL, NULL, '2025-09-03 18:20:49', '2025-09-03 18:20:49'),
(4, 'Van', 'van', '#411', 'online', 'maintenance', NULL, NULL, '2025-09-03 18:20:49', '2025-09-03 18:20:49');

-- --------------------------------------------------------

--
-- Table structure for table `vehicle_uploads`
--

CREATE TABLE `vehicle_uploads` (
  `id` int(11) NOT NULL,
  `vehicle_id` int(11) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `file_type` varchar(10) NOT NULL,
  `uploaded_by` int(11) NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `communication_actions`
--
ALTER TABLE `communication_actions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `conversations`
--
ALTER TABLE `conversations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `vehicle_id` (`vehicle_id`),
  ADD KEY `last_message_id` (`last_message_id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `message_type_id` (`message_type_id`),
  ADD KEY `priority_id` (`priority_id`);

--
-- Indexes for table `message_types`
--
ALTER TABLE `message_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `type_name` (`type_name`);

--
-- Indexes for table `priorities`
--
ALTER TABLE `priorities`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `level_name` (`level_name`),
  ADD UNIQUE KEY `severity` (`severity`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `vehicles`
--
ALTER TABLE `vehicles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `identifier` (`identifier`),
  ADD KEY `current_driver_id` (`current_driver_id`);

--
-- Indexes for table `vehicle_uploads`
--
ALTER TABLE `vehicle_uploads`
  ADD PRIMARY KEY (`id`),
  ADD KEY `vehicle_id` (`vehicle_id`),
  ADD KEY `uploaded_by` (`uploaded_by`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `communication_actions`
--
ALTER TABLE `communication_actions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `conversations`
--
ALTER TABLE `conversations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `message_types`
--
ALTER TABLE `message_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `priorities`
--
ALTER TABLE `priorities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `vehicles`
--
ALTER TABLE `vehicles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `vehicle_uploads`
--
ALTER TABLE `vehicle_uploads`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `communication_actions`
--
ALTER TABLE `communication_actions`
  ADD CONSTRAINT `communication_actions_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `conversations`
--
ALTER TABLE `conversations`
  ADD CONSTRAINT `conversations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `conversations_ibfk_2` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `conversations_ibfk_3` FOREIGN KEY (`last_message_id`) REFERENCES `messages` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`message_type_id`) REFERENCES `message_types` (`id`),
  ADD CONSTRAINT `messages_ibfk_3` FOREIGN KEY (`priority_id`) REFERENCES `priorities` (`id`);

--
-- Constraints for table `vehicles`
--
ALTER TABLE `vehicles`
  ADD CONSTRAINT `vehicles_ibfk_1` FOREIGN KEY (`current_driver_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `vehicle_uploads`
--
ALTER TABLE `vehicle_uploads`
  ADD CONSTRAINT `vehicle_uploads_ibfk_1` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `vehicle_uploads_ibfk_2` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
