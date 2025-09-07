-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost: 3306
-- Generation Time: Sep 07, 2025 at 09:39 PM
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
-- Database: `log2`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `booking_id` int(11) NOT NULL,
  `request_id` int(11) DEFAULT NULL,
  `customer_id` int(11) NOT NULL,
  `pickup_location` varchar(255) NOT NULL,
  `dropoff_location` varchar(255) NOT NULL,
  `pickup_time` datetime NOT NULL,
  `passengers` int(11) DEFAULT 1,
  `vehicle_type` enum('car','motor','suv','van') DEFAULT 'car',
  `special_requests` text DEFAULT NULL,
  `status` enum('pending','scheduled','dispatched','in_progress','completed','cancelled') DEFAULT 'scheduled',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `compliance_records`
--

CREATE TABLE `compliance_records` (
  `compliance_id` int(11) NOT NULL,
  `driver_id` int(11) NOT NULL,
  `record_type` enum('License','Medical','Training','Vehicle Inspection') NOT NULL,
  `expiry_date` date NOT NULL,
  `status` enum('Valid','Expired','Pending Renewal') NOT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cost_optimization_reports`
--

CREATE TABLE `cost_optimization_reports` (
  `id` int(11) NOT NULL,
  `report_id` varchar(20) NOT NULL,
  `category` varchar(100) NOT NULL,
  `period` varchar(50) NOT NULL,
  `status` enum('Implemented','In Progress','Pending Review') DEFAULT 'Pending Review',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cost_optimization_reports`
--

INSERT INTO `cost_optimization_reports` (`id`, `report_id`, `category`, `period`, `status`, `notes`, `created_at`, `updated_at`) VALUES
(1, 'RPT-001', 'Fuel Efficiency', 'Q3 2023', 'In Progress', 'Reduced idle time by 15%', '2025-09-07 19:37:07', '2025-09-07 19:37:45'),
(2, 'RPT-002', 'Route Optimization', 'Q3 2023', 'In Progress', 'New routes being tested', '2025-09-07 19:37:07', '2025-09-07 19:37:07'),
(3, 'RPT-003', 'Maintenance Schedule', 'Q3 2023', 'Pending Review', 'Preventive maintenance plan', '2025-09-07 19:37:07', '2025-09-07 19:37:07');

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `customer_id` int(99) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `phone` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`customer_id`, `first_name`, `last_name`, `phone`, `email`, `created_at`) VALUES
(1, 'Joana', 'Darna', 920022, 'joana@gmail.com', '0000-00-00 00:00:00'),
(2, 'efsqfe', '', 0, '', '2025-09-07 03:11:40'),
(3, 'asfdgfh', '', 98524352, '', '2025-09-07 15:48:32'),
(4, 'qweds', '', 13243232, '', '2025-09-07 15:55:24'),
(5, 'qwre', '', 123412, '', '2025-09-07 16:16:26'),
(6, 'gagoo', '', 123456, '', '2025-09-07 16:40:30'),
(7, 'Gabriel', '', 2147483647, '', '2025-09-07 17:18:19');

-- --------------------------------------------------------

--
-- Table structure for table `dispatches`
--

CREATE TABLE `dispatches` (
  `dispatch_id` int(11) NOT NULL,
  `trip_id` int(11) DEFAULT NULL,
  `booking_id` int(11) DEFAULT NULL,
  `driver_id` int(11) NOT NULL,
  `vehicle_id` int(11) NOT NULL,
  `dispatch_time` datetime NOT NULL,
  `estimated_arrival` datetime DEFAULT NULL,
  `actual_arrival` datetime DEFAULT NULL,
  `status` enum('dispatched','in_progress','completed','cancelled') DEFAULT 'dispatched',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
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
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `status` enum('available','on_trip','off_duty') DEFAULT 'available',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `drivers`
--

INSERT INTO `drivers` (`driver_id`, `first_name`, `last_name`, `license_number`, `phone`, `email`, `status`, `created_at`) VALUES
(8, 'afasfask', 'asffas', '13243525', '09243589324', 'Afasfas@gmail.com', 'available', '2025-09-07 15:41:16');

-- --------------------------------------------------------

--
-- Table structure for table `fuel_usage`
--

CREATE TABLE `fuel_usage` (
  `id` int(11) NOT NULL,
  `date` date NOT NULL,
  `vehicle_id` int(11) NOT NULL,
  `fuel_type` enum('gasoline','diesel','electric','hybrid','cng') NOT NULL,
  `liters` decimal(10,2) NOT NULL,
  `cost` decimal(10,2) NOT NULL,
  `odometer_reading` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `maintenance`
--

CREATE TABLE `maintenance` (
  `maintenance_id` int(11) NOT NULL,
  `vehicle_id` int(11) NOT NULL,
  `maintenance_type` enum('routine','repair','inspection','tire_replacement','oil_change','brake_service') NOT NULL,
  `scheduled_date` date NOT NULL,
  `completed_date` date DEFAULT NULL,
  `cost` decimal(10,2) DEFAULT 0.00,
  `description` text DEFAULT NULL,
  `status` enum('scheduled','in_progress','completed','cancelled') DEFAULT 'scheduled',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `maintenance`
--

INSERT INTO `maintenance` (`maintenance_id`, `vehicle_id`, `maintenance_type`, `scheduled_date`, `completed_date`, `cost`, `description`, `status`, `created_at`) VALUES
(3, 3, 'routine', '2025-09-05', '0000-00-00', 21.00, 'ewqe', 'in_progress', '2025-09-05 09:40:26');

-- --------------------------------------------------------

--
-- Table structure for table `schedules`
--

CREATE TABLE `schedules` (
  `schedule_id` int(11) NOT NULL,
  `vehicle_id` int(11) NOT NULL,
  `driver_id` int(11) NOT NULL,
  `start_time` datetime NOT NULL,
  `end_time` datetime NOT NULL,
  `purpose` text NOT NULL,
  `status` enum('scheduled','in_progress','completed','cancelled') DEFAULT 'scheduled',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `schedules`
--

INSERT INTO `schedules` (`schedule_id`, `vehicle_id`, `driver_id`, `start_time`, `end_time`, `purpose`, `status`, `created_at`) VALUES
(2, 2, 2, '2025-09-05 09:39:00', '2025-09-05 10:39:00', 'secret', 'in_progress', '2025-09-05 09:39:44');

-- --------------------------------------------------------

--
-- Table structure for table `toll_fees`
--

CREATE TABLE `toll_fees` (
  `id` int(11) NOT NULL,
  `toll_name` varchar(100) NOT NULL,
  `vehicle_type` varchar(50) NOT NULL,
  `fee_amount` decimal(10,2) NOT NULL,
  `effective_date` date NOT NULL,
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `trips`
--

CREATE TABLE `trips` (
  `trip_id` int(11) NOT NULL,
  `customer` int(11) NOT NULL,
  `pickup_location` varchar(255) NOT NULL,
  `dropoff_location` varchar(255) NOT NULL,
  `pickup_time` datetime NOT NULL,
  `passengers` int(11) DEFAULT 1,
  `special_requests` text DEFAULT NULL,
  `status` enum('pending','scheduled','dispatched','in_progress','completed','cancelled') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `trip_expenses`
--

CREATE TABLE `trip_expenses` (
  `id` int(11) NOT NULL,
  `trip_id` varchar(20) NOT NULL,
  `driver_id` int(11) NOT NULL,
  `expense_type` enum('Toll Fee','Parking','Maintenance','Emergency Repair','Cleaning','Other') NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `expense_date` date NOT NULL,
  `status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','driver','dispatcher','fleet-manager','maintenance','compliance') NOT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `driver_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `email`, `password`, `role`, `first_name`, `last_name`, `phone`, `driver_id`, `created_at`, `updated_at`) VALUES
(1, 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'System', 'Administrator', '555-0100', NULL, '2025-09-07 07:04:30', '2025-09-07 07:04:30'),
(2, 'driver1@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'driver', 'John', 'Doe', '555-1234', 1, '2025-09-07 07:04:30', '2025-09-07 07:04:30'),
(3, 'driver2@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'driver', 'Jane', 'Smith', '555-5678', 2, '2025-09-07 07:04:30', '2025-09-07 07:04:30'),
(4, 'dispatcher@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'dispatcher', 'Robert', 'Johnson', '555-9012', NULL, '2025-09-07 07:04:30', '2025-09-07 07:04:30'),
(5, 'fleet@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'fleet-manager', 'Sarah', 'Williams', '555-3456', NULL, '2025-09-07 07:04:30', '2025-09-07 07:04:30'),
(6, 'maintenance@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'maintenance', 'Michael', 'Brown', '555-7890', NULL, '2025-09-07 07:04:30', '2025-09-07 07:04:30'),
(7, 'compliance@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'compliance', 'Lisa', 'Taylor', '555-2345', NULL, '2025-09-07 07:04:30', '2025-09-07 07:04:30');

-- --------------------------------------------------------

--
-- Table structure for table `vehicles`
--

CREATE TABLE `vehicles` (
  `vehicle_id` int(11) NOT NULL,
  `make` varchar(50) NOT NULL,
  `model` varchar(50) NOT NULL,
  `year` year(4) DEFAULT NULL,
  `license_plate` varchar(15) NOT NULL,
  `capacity` int(11) NOT NULL,
  `vehicle_type` enum('car','motor','suv','van') DEFAULT 'car',
  `fuel_type` varchar(99) NOT NULL,
  `status` enum('available','in_use','maintenance') DEFAULT 'available',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vehicles`
--

INSERT INTO `vehicles` (`vehicle_id`, `make`, `model`, `year`, `license_plate`, `capacity`, `vehicle_type`, `fuel_type`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Toyota', 'Camry', '2020', 'ABC123', 4, 'car', 'gasoline', 'in_use', '2025-09-03 06:39:39', NULL),
(2, 'Honda', 'Accord', '2019', 'XYZ789', 5, 'car', 'gasoline', 'maintenance', '2025-09-03 06:39:39', NULL),
(3, 'Ford', 'Escape', '2021', 'DEF456', 5, 'suv', '', 'in_use', '2025-09-03 06:39:39', NULL),
(4, 'Chevrolet', 'Malibu', '2020', 'GHI789', 5, 'car', 'gasoline', 'in_use', '2025-09-03 06:39:39', NULL),
(9, 'qweqwr', 'qwrqwrwrqwr', '2002', 'qwe215', 10, 'van', 'gasoline', 'available', '2025-09-07 02:58:43', '2025-09-07 02:58:43');

-- --------------------------------------------------------

--
-- Table structure for table `vehicle_assignments`
--

CREATE TABLE `vehicle_assignments` (
  `assignment_id` int(11) NOT NULL,
  `vehicle_id` int(11) NOT NULL,
  `driver_id` int(11) NOT NULL,
  `assignment_date` date NOT NULL,
  `expected_return_date` date NOT NULL,
  `actual_return_date` date DEFAULT NULL,
  `purpose` text NOT NULL,
  `status` enum('active','completed','cancelled') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vehicle_assignments`
--

INSERT INTO `vehicle_assignments` (`assignment_id`, `vehicle_id`, `driver_id`, `assignment_date`, `expected_return_date`, `actual_return_date`, `purpose`, `status`, `created_at`) VALUES
(1, 3, 2, '2025-09-05', '2025-09-06', NULL, 'w', 'active', '2025-09-05 10:33:42');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`booking_id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `request_id` (`request_id`);

--
-- Indexes for table `compliance_records`
--
ALTER TABLE `compliance_records`
  ADD PRIMARY KEY (`compliance_id`),
  ADD KEY `driver_id` (`driver_id`);

--
-- Indexes for table `cost_optimization_reports`
--
ALTER TABLE `cost_optimization_reports`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `report_id` (`report_id`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`customer_id`);

--
-- Indexes for table `dispatches`
--
ALTER TABLE `dispatches`
  ADD PRIMARY KEY (`dispatch_id`),
  ADD KEY `request_id` (`trip_id`),
  ADD KEY `booking_id` (`booking_id`),
  ADD KEY `driver_id` (`driver_id`),
  ADD KEY `vehicle_id` (`vehicle_id`);

--
-- Indexes for table `drivers`
--
ALTER TABLE `drivers`
  ADD PRIMARY KEY (`driver_id`),
  ADD UNIQUE KEY `license_number` (`license_number`);

--
-- Indexes for table `fuel_usage`
--
ALTER TABLE `fuel_usage`
  ADD PRIMARY KEY (`id`),
  ADD KEY `vehicle_id` (`vehicle_id`);

--
-- Indexes for table `maintenance`
--
ALTER TABLE `maintenance`
  ADD PRIMARY KEY (`maintenance_id`),
  ADD KEY `vehicle_id` (`vehicle_id`);

--
-- Indexes for table `schedules`
--
ALTER TABLE `schedules`
  ADD PRIMARY KEY (`schedule_id`),
  ADD KEY `vehicle_id` (`vehicle_id`),
  ADD KEY `driver_id` (`driver_id`);

--
-- Indexes for table `toll_fees`
--
ALTER TABLE `toll_fees`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `trips`
--
ALTER TABLE `trips`
  ADD PRIMARY KEY (`trip_id`),
  ADD KEY `customer_id` (`customer`);

--
-- Indexes for table `trip_expenses`
--
ALTER TABLE `trip_expenses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `driver_id` (`driver_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `driver_id` (`driver_id`);

--
-- Indexes for table `vehicles`
--
ALTER TABLE `vehicles`
  ADD PRIMARY KEY (`vehicle_id`),
  ADD UNIQUE KEY `license_plate` (`license_plate`);

--
-- Indexes for table `vehicle_assignments`
--
ALTER TABLE `vehicle_assignments`
  ADD PRIMARY KEY (`assignment_id`),
  ADD KEY `vehicle_id` (`vehicle_id`),
  ADD KEY `driver_id` (`driver_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `booking_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `compliance_records`
--
ALTER TABLE `compliance_records`
  MODIFY `compliance_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `cost_optimization_reports`
--
ALTER TABLE `cost_optimization_reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `customer_id` int(99) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `dispatches`
--
ALTER TABLE `dispatches`
  MODIFY `dispatch_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `drivers`
--
ALTER TABLE `drivers`
  MODIFY `driver_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `fuel_usage`
--
ALTER TABLE `fuel_usage`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `maintenance`
--
ALTER TABLE `maintenance`
  MODIFY `maintenance_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `schedules`
--
ALTER TABLE `schedules`
  MODIFY `schedule_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `toll_fees`
--
ALTER TABLE `toll_fees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `trips`
--
ALTER TABLE `trips`
  MODIFY `trip_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `trip_expenses`
--
ALTER TABLE `trip_expenses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `vehicles`
--
ALTER TABLE `vehicles`
  MODIFY `vehicle_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `vehicle_assignments`
--
ALTER TABLE `vehicle_assignments`
  MODIFY `assignment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`request_id`) REFERENCES `trips` (`trip_id`) ON DELETE SET NULL;

--
-- Constraints for table `compliance_records`
--
ALTER TABLE `compliance_records`
  ADD CONSTRAINT `compliance_records_ibfk_1` FOREIGN KEY (`driver_id`) REFERENCES `drivers` (`driver_id`) ON DELETE CASCADE;

--
-- Constraints for table `dispatches`
--
ALTER TABLE `dispatches`
  ADD CONSTRAINT `dispatches_ibfk_1` FOREIGN KEY (`trip_id`) REFERENCES `trips` (`trip_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `dispatches_ibfk_2` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`booking_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `dispatches_ibfk_3` FOREIGN KEY (`driver_id`) REFERENCES `drivers` (`driver_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `dispatches_ibfk_4` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`vehicle_id`) ON DELETE CASCADE;

--
-- Constraints for table `fuel_usage`
--
ALTER TABLE `fuel_usage`
  ADD CONSTRAINT `fuel_usage_ibfk_1` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`vehicle_id`) ON DELETE CASCADE;

--
-- Constraints for table `maintenance`
--
ALTER TABLE `maintenance`
  ADD CONSTRAINT `maintenance_ibfk_1` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`vehicle_id`) ON DELETE CASCADE;

--
-- Constraints for table `schedules`
--
ALTER TABLE `schedules`
  ADD CONSTRAINT `schedules_ibfk_1` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`vehicle_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `schedules_ibfk_2` FOREIGN KEY (`driver_id`) REFERENCES `drivers` (`driver_id`) ON DELETE CASCADE;

--
-- Constraints for table `trips`
--
ALTER TABLE `trips`
  ADD CONSTRAINT `trips_ibfk_1` FOREIGN KEY (`customer`) REFERENCES `customers` (`customer_id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`driver_id`) REFERENCES `drivers` (`driver_id`) ON DELETE SET NULL;

--
-- Constraints for table `vehicle_assignments`
--
ALTER TABLE `vehicle_assignments`
  ADD CONSTRAINT `vehicle_assignments_ibfk_1` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`vehicle_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `vehicle_assignments_ibfk_2` FOREIGN KEY (`driver_id`) REFERENCES `drivers` (`driver_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
