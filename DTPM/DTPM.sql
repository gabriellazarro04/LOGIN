-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Sep 02, 2025 at 09:52 PM
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
-- Database: `DTPM`
--

-- --------------------------------------------------------

--
-- Table structure for table `compliance_records`
--

CREATE TABLE `compliance_records` (
  `compliance_id` int(11) NOT NULL,
  `driver_id` int(11) NOT NULL,
  `record_type` enum('License','Medical','Training','Vehicle Inspection') NOT NULL,
  `expiry_date` date NOT NULL,
  `status` enum('Valid','Expired','Pending Renewal') DEFAULT 'Valid',
  `document_path` text DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `drivers`
--

CREATE TABLE `drivers` (
  `driver_id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `license_number` varchar(20) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `status` enum('Active','On Leave','Inactive') DEFAULT 'Active',
  `hire_date` date DEFAULT curdate(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `drivers`
--

INSERT INTO `drivers` (`driver_id`, `first_name`, `last_name`, `license_number`, `email`, `phone`, `status`, `hire_date`, `created_at`, `updated_at`) VALUES
(12, 'ako', 'si', '091223', 'jajdd@gmail.com', '0912939213', 'Active', '2025-09-03', '2025-09-02 19:03:28', '2025-09-02 19:03:28');

-- --------------------------------------------------------

--
-- Table structure for table `maintenance_records`
--

CREATE TABLE `maintenance_records` (
  `maintenance_id` int(11) NOT NULL,
  `vehicle_id` int(11) NOT NULL,
  `maintenance_type` enum('Routine','Repair','Inspection') NOT NULL,
  `description` text NOT NULL,
  `cost` decimal(10,2) DEFAULT NULL,
  `maintenance_date` date NOT NULL,
  `next_due_date` date DEFAULT NULL,
  `service_provider` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `maintenance_records`
--

INSERT INTO `maintenance_records` (`maintenance_id`, `vehicle_id`, `maintenance_type`, `description`, `cost`, `maintenance_date`, `next_due_date`, `service_provider`, `notes`, `created_at`, `updated_at`) VALUES
(1, 1, 'Routine', 'Oil change and filter replacement', 250.00, '2023-09-15', '2024-03-15', NULL, NULL, '2025-09-02 17:35:37', '2025-09-02 17:35:37'),
(2, 1, 'Inspection', 'Annual safety inspection', 150.00, '2023-10-01', '2024-10-01', NULL, NULL, '2025-09-02 17:35:37', '2025-09-02 17:35:37'),
(3, 2, 'Routine', 'Brake system check and service', 450.00, '2023-08-20', '2024-02-20', NULL, NULL, '2025-09-02 17:35:37', '2025-09-02 17:35:37');

-- --------------------------------------------------------

--
-- Table structure for table `performance_metrics`
--

CREATE TABLE `performance_metrics` (
  `metric_id` int(11) NOT NULL,
  `driver_id` int(11) NOT NULL,
  `trip_id` int(11) DEFAULT NULL,
  `metric_date` date NOT NULL,
  `fuel_efficiency` decimal(5,2) DEFAULT NULL,
  `average_speed` decimal(5,2) DEFAULT NULL,
  `harsh_braking_count` int(11) DEFAULT 0,
  `harsh_acceleration_count` int(11) DEFAULT 0,
  `idling_time_minutes` int(11) DEFAULT 0,
  `safety_score` decimal(5,2) DEFAULT NULL CHECK (`safety_score` >= 0 and `safety_score` <= 100),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `trips`
--

CREATE TABLE `trips` (
  `trip_id` int(11) NOT NULL,
  `driver_id` int(11) NOT NULL,
  `vehicle_id` int(11) NOT NULL,
  `start_location` varchar(100) NOT NULL,
  `end_location` varchar(100) NOT NULL,
  `distance_km` decimal(8,2) NOT NULL,
  `duration_minutes` int(11) DEFAULT NULL,
  `start_time` datetime NOT NULL,
  `end_time` datetime DEFAULT NULL,
  `fuel_consumption_liters` decimal(8,2) DEFAULT NULL,
  `average_speed_kmh` decimal(5,2) DEFAULT NULL,
  `status` enum('Scheduled','In Progress','Completed','Cancelled') DEFAULT 'Completed',
  `rating` decimal(2,1) DEFAULT NULL CHECK (`rating` >= 0 and `rating` <= 5),
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `trips`
--

INSERT INTO `trips` (`trip_id`, `driver_id`, `vehicle_id`, `start_location`, `end_location`, `distance_km`, `duration_minutes`, `start_time`, `end_time`, `fuel_consumption_liters`, `average_speed_kmh`, `status`, `rating`, `notes`, `created_at`, `updated_at`) VALUES
(7, 12, 1, 'qc', 'us', 12.00, NULL, '2025-09-02 19:03:00', NULL, NULL, NULL, 'Completed', NULL, NULL, '2025-09-02 19:03:46', '2025-09-02 19:03:46'),
(8, 12, 2, 'qc', 'sm', 1.00, NULL, '2025-09-02 19:04:00', NULL, NULL, NULL, 'Completed', NULL, NULL, '2025-09-02 19:04:50', '2025-09-02 19:04:50'),
(9, 12, 2, 'qc', 'sm', 1.00, NULL, '2025-09-02 19:04:00', NULL, NULL, NULL, 'Completed', NULL, NULL, '2025-09-02 19:41:09', '2025-09-02 19:41:09'),
(10, 12, 2, 'qc', 'sm', 1.00, NULL, '2025-09-02 19:04:00', NULL, NULL, NULL, 'Completed', NULL, NULL, '2025-09-02 19:41:12', '2025-09-02 19:41:12'),
(11, 12, 2, 'qc', 'sm', 1.00, NULL, '2025-09-02 19:04:00', NULL, NULL, NULL, 'Completed', NULL, NULL, '2025-09-02 19:41:15', '2025-09-02 19:41:15');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `role` enum('Admin','Manager','User') DEFAULT 'User',
  `is_active` tinyint(1) DEFAULT 1,
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `password_hash`, `email`, `full_name`, `role`, `is_active`, `last_login`, `created_at`, `updated_at`) VALUES
(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@dtpm.com', 'System Administrator', 'Admin', 1, NULL, '2025-09-02 17:35:37', '2025-09-02 17:35:37'),
(2, 'manager', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'manager@dtpm.com', 'Fleet Manager', 'Manager', 1, NULL, '2025-09-02 17:35:37', '2025-09-02 17:35:37'),
(3, 'user', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user@dtpm.com', 'Standard User', 'User', 1, NULL, '2025-09-02 17:35:37', '2025-09-02 17:35:37');

-- --------------------------------------------------------

--
-- Table structure for table `vehicles`
--

CREATE TABLE `vehicles` (
  `vehicle_id` int(11) NOT NULL,
  `make` varchar(50) NOT NULL,
  `model` varchar(50) NOT NULL,
  `year` int(11) DEFAULT NULL,
  `license_plate` varchar(15) NOT NULL,
  `vin` varchar(17) DEFAULT NULL,
  `fuel_type` enum('Diesel','Gasoline','Electric','Hybrid') DEFAULT 'Diesel',
  `status` enum('Active','Maintenance','Inactive') DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vehicles`
--

INSERT INTO `vehicles` (`vehicle_id`, `make`, `model`, `year`, `license_plate`, `vin`, `fuel_type`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Yamaha', 'Bisaya Concept', 2021, '123-Bisayawa', '1FTNS2EWXCDA12345', 'Gasoline', 'Active', '2025-09-02 17:35:37', '2025-09-02 18:49:20'),
(2, 'Mercedes', 'Sprinter', 2021, 'XYZ-789', 'WD4PF0CD5MP123456', 'Diesel', 'Active', '2025-09-02 17:35:37', '2025-09-02 17:35:37'),
(3, 'Volvo', 'VNL', 2020, 'DEF-456', '4V4NC9EJXEN123456', 'Diesel', 'Active', '2025-09-02 17:35:37', '2025-09-02 17:35:37'),
(4, 'Freightliner', 'Cascadia', 2023, 'GHI-789', '1FUJGLDR2CL123456', 'Diesel', 'Active', '2025-09-02 18:46:40', '2025-09-02 18:46:40'),
(5, 'Honda', 'Civic', 2020, 'MFC-321', '187mobztaz', 'Gasoline', 'Active', '2025-09-02 18:48:25', '2025-09-02 18:48:25');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `compliance_records`
--
ALTER TABLE `compliance_records`
  ADD PRIMARY KEY (`compliance_id`),
  ADD KEY `driver_id` (`driver_id`);

--
-- Indexes for table `drivers`
--
ALTER TABLE `drivers`
  ADD PRIMARY KEY (`driver_id`),
  ADD UNIQUE KEY `license_number` (`license_number`);

--
-- Indexes for table `maintenance_records`
--
ALTER TABLE `maintenance_records`
  ADD PRIMARY KEY (`maintenance_id`),
  ADD KEY `vehicle_id` (`vehicle_id`);

--
-- Indexes for table `performance_metrics`
--
ALTER TABLE `performance_metrics`
  ADD PRIMARY KEY (`metric_id`),
  ADD KEY `driver_id` (`driver_id`),
  ADD KEY `trip_id` (`trip_id`);

--
-- Indexes for table `trips`
--
ALTER TABLE `trips`
  ADD PRIMARY KEY (`trip_id`),
  ADD KEY `driver_id` (`driver_id`),
  ADD KEY `vehicle_id` (`vehicle_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `vehicles`
--
ALTER TABLE `vehicles`
  ADD PRIMARY KEY (`vehicle_id`),
  ADD UNIQUE KEY `license_plate` (`license_plate`),
  ADD UNIQUE KEY `vin` (`vin`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `compliance_records`
--
ALTER TABLE `compliance_records`
  MODIFY `compliance_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `drivers`
--
ALTER TABLE `drivers`
  MODIFY `driver_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `maintenance_records`
--
ALTER TABLE `maintenance_records`
  MODIFY `maintenance_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `performance_metrics`
--
ALTER TABLE `performance_metrics`
  MODIFY `metric_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `trips`
--
ALTER TABLE `trips`
  MODIFY `trip_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `vehicles`
--
ALTER TABLE `vehicles`
  MODIFY `vehicle_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `compliance_records`
--
ALTER TABLE `compliance_records`
  ADD CONSTRAINT `compliance_records_ibfk_1` FOREIGN KEY (`driver_id`) REFERENCES `drivers` (`driver_id`) ON DELETE CASCADE;

--
-- Constraints for table `maintenance_records`
--
ALTER TABLE `maintenance_records`
  ADD CONSTRAINT `maintenance_records_ibfk_1` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`vehicle_id`) ON DELETE CASCADE;

--
-- Constraints for table `performance_metrics`
--
ALTER TABLE `performance_metrics`
  ADD CONSTRAINT `performance_metrics_ibfk_1` FOREIGN KEY (`driver_id`) REFERENCES `drivers` (`driver_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `performance_metrics_ibfk_2` FOREIGN KEY (`trip_id`) REFERENCES `trips` (`trip_id`) ON DELETE SET NULL;

--
-- Constraints for table `trips`
--
ALTER TABLE `trips`
  ADD CONSTRAINT `trips_ibfk_1` FOREIGN KEY (`driver_id`) REFERENCES `drivers` (`driver_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `trips_ibfk_2` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`vehicle_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
