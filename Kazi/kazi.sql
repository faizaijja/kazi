-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 05, 2025 at 05:17 PM
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
-- Database: `kazi`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `booking_id` int(100) NOT NULL,
  `job_id` int(100) NOT NULL,
  `client_id` int(100) NOT NULL,
  `provider_id` int(100) NOT NULL,
  `scheduled_date` date NOT NULL,
  `scheduled_time` datetime NOT NULL,
  `final_price` decimal(10,2) NOT NULL,
  `status` enum('confirmed','in_progress','completed','canceled') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_applications`
--

CREATE TABLE `job_applications` (
  `application_id` int(100) NOT NULL,
  `job_id` int(100) NOT NULL,
  `provider_id` int(100) NOT NULL,
  `proposed_price` decimal(10,2) NOT NULL,
  `estimated_duration` int(100) NOT NULL,
  `message` text NOT NULL,
  `status` enum('pending','accepted','rejected','withdrawn') NOT NULL,
  `applied_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_requests`
--

CREATE TABLE `job_requests` (
  `job_id` int(100) NOT NULL,
  `client_id` int(100) NOT NULL,
  `category_id` int(100) NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` varchar(100) NOT NULL,
  `location_id` int(5) NOT NULL,
  `budget_min` int(11) NOT NULL,
  `budget_max` int(100) NOT NULL,
  `preferred_date` date NOT NULL,
  `preferred_time` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `urgency` enum('low','medium','high','emergency') NOT NULL,
  `status` enum('open','assigned','in_progress','completed','canceled') NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `locations`
--

CREATE TABLE `locations` (
  `location_id` int(100) NOT NULL,
  `user_id` int(100) NOT NULL,
  `address` varchar(100) NOT NULL,
  `city` varchar(100) NOT NULL,
  `state` varchar(50) NOT NULL,
  `is_primary` varchar(5) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `message_id` int(100) NOT NULL,
  `sender_id` int(100) NOT NULL,
  `receiver_id` int(100) NOT NULL,
  `job_id` int(100) NOT NULL,
  `message_text` text NOT NULL,
  `sent_at` datetime NOT NULL,
  `is_read` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` int(100) NOT NULL,
  `user_id` int(100) NOT NULL,
  `type` varchar(100) NOT NULL,
  `title` text NOT NULL,
  `message` text NOT NULL,
  `is_read` datetime NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `payment_id` int(100) NOT NULL,
  `booking_id` int(100) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` enum('cash','momo','bank transfer','') NOT NULL,
  `transaction_id` int(100) NOT NULL,
  `status` enum('pending','completed','failed','refunded') NOT NULL,
  `paid_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `portfolio`
--

CREATE TABLE `portfolio` (
  `portfolio_id` int(100) NOT NULL,
  `provider_id` int(100) NOT NULL,
  `title` varchar(55) NOT NULL,
  `description` text NOT NULL,
  `image_url` varchar(100) NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `provider_services`
--

CREATE TABLE `provider_services` (
  `id` int(100) NOT NULL,
  `provider_id` int(100) NOT NULL,
  `category_id` int(100) NOT NULL,
  `specialization` varchar(100) NOT NULL,
  `is_primary_service` varchar(5) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `review_id` int(100) NOT NULL,
  `booking_id` int(100) NOT NULL,
  `reviewer_id` int(100) NOT NULL,
  `reviewed_id` int(100) NOT NULL,
  `rating` int(5) NOT NULL,
  `comment` varchar(100) NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `service_categories`
--

CREATE TABLE `service_categories` (
  `category_id` int(100) NOT NULL,
  `category_name` varchar(50) NOT NULL,
  `description` text NOT NULL,
  `icon_url` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `service_categories`
--

INSERT INTO `service_categories` (`category_id`, `category_name`, `description`, `icon_url`) VALUES
(1, 'Plumbing', 'Water systems, pipes, and drainage services', ''),
(2, 'Electrical', 'Electrical installations and repairs', ''),
(3, 'Landscaping', 'Garden and outdoor maintenance', ''),
(4, 'Carpentry', 'Wood working and furniture services', ''),
(5, 'Cleaning', 'House and office cleaning services', ''),
(6, 'Painting', 'Interior and exterior painting', ''),
(7, 'IT Support', 'Computer and technology services', ''),
(8, 'Appliance Repair', 'Home appliance maintenance', ''),
(9, 'Auto Mechanic', 'Vehicle repair and maintenance', ''),
(10, 'Security', 'Security system installation', '');

-- --------------------------------------------------------

--
-- Table structure for table `service_providers`
--

CREATE TABLE `service_providers` (
  `provider_id` int(100) NOT NULL,
  `user_id` int(100) NOT NULL,
  `business_name` varchar(100) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `bio` text NOT NULL,
  `years_of_experience` int(10) NOT NULL,
  `hourly_rate` decimal(10,2) NOT NULL,
  `availability_status` varchar(10) NOT NULL,
  `rating_average` decimal(10,2) NOT NULL,
  `total_jobs_completed` int(10) NOT NULL,
  `verification_status` varchar(10) NOT NULL,
  `identity_document` varchar(10) NOT NULL,
  `background_check_status` varchar(10) NOT NULL,
  `identity_verified` tinyint(1) NOT NULL,
  `criminal_check` varchar(100) NOT NULL,
  `last_verification_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `service_providers`
--

INSERT INTO `service_providers` (`provider_id`, `user_id`, `business_name`, `category_id`, `bio`, `years_of_experience`, `hourly_rate`, `availability_status`, `rating_average`, `total_jobs_completed`, `verification_status`, `identity_document`, `background_check_status`, `identity_verified`, `criminal_check`, `last_verification_date`) VALUES
(0, 100, 'Elite Plumbing Services', NULL, 'Professional plumber with expertise in residential and commercial plumbing. Specialized in emergency repairs, installations, and maintenance.', 8, 15000.00, 'available', 4.80, 156, 'verified', 'ID_PL_001.', 'passed', 1, '0', '2024-11-15'),
(1, 101, 'Swift Electricians', 1, 'Licensed electrician offering complete electrical solutions including wiring, installations, and troubleshooting. Available for emergency calls.', 12, 18000.00, 'available', 4.90, 203, 'verified', 'ID_EL_002.', 'passed', 1, '0', '2024-11-20'),
(2, 102, 'Green Thumb Landscaping', 2, 'Transform your outdoor space with professional landscaping services. Garden design, lawn maintenance, and plant care specialist.', 6, 12000.00, 'busy', 4.70, 142, 'verified', 'ID_LS_003.', 'passed', 1, '0', '2024-11-10'),
(3, 103, 'Master Carpentry Works', 3, 'Skilled carpenter with experience in custom furniture, door/window installations, and home renovations. Quality workmanship guaranteed.', 15, 20000.00, 'available', 4.90, 287, 'verified', 'ID_CP_004.', 'passed', 1, '0', '2024-11-25'),
(4, 104, 'Sparkle Clean Services', 4, 'Professional house cleaning and deep cleaning services. Eco-friendly products, attention to detail, and reliable service.', 5, 10000.00, 'available', 4.60, 198, 'verified', 'ID_CL_005.', 'passed', 1, '0', '2024-11-18'),
(5, 105, 'ProPaint Solutions', 5, 'Expert painting services for interior and exterior projects. Color consultation, preparation, and finishing with premium materials.', 10, 16000.00, 'available', 4.80, 176, 'verified', 'ID_PT_006.', 'passed', 1, '0', '2024-11-22'),
(6, 106, 'TechFix IT Support', 6, 'Computer repair and IT support specialist. Hardware troubleshooting, software installation, network setup, and data recovery services.', 7, 14000.00, 'busy', 4.70, 134, 'verified', 'ID_IT_007.', 'passed', 1, '0', '2024-11-12'),
(7, 107, 'HomeCare Appliance Repair', 7, 'Certified technician for all home appliance repairs - refrigerators, washing machines, ovens, and more. Same-day service available.', 9, 13000.00, 'available', 4.80, 221, 'verified', 'ID_AP_008.', 'passed', 1, '0', '2024-11-28'),
(8, 108, 'AutoPro Mechanics', 8, 'Mobile mechanic service bringing expert car repairs to your location. Engine diagnostics, oil changes, brake service, and general maintenance.', 11, 17000.00, 'available', 4.90, 195, 'verified', 'ID_MC_009.', 'passed', 1, '0', '2024-11-14'),
(9, 109, 'SafeGuard Security Systems', 9, 'Security system installation and maintenance expert. CCTV cameras, alarm systems, access control, and smart home integration.', 8, 19000.00, 'unavailabl', 4.70, 112, 'verified', 'ID_SC_010.', 'passed', 1, '0', '2024-11-19');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL,
  `phone_number` int(10) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `user_type` enum('client','provider','admin','') NOT NULL,
  `profile_picture` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_verified` tinyint(1) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `email`, `password`, `phone_number`, `full_name`, `user_type`, `profile_picture`, `created_at`, `updated_at`, `is_verified`, `is_active`) VALUES
(100, 'jean.plumbing@kazi.rw', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2147483647, 'Jean Pierre Mugabo', 'provider', 'avatar_plumber.jpg', '2025-12-05 15:14:05', '2025-12-05 15:52:46', 1, 1),
(101, 'marie.electric@kazi.rw', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2147483647, 'Marie Claire Uwase', 'provider', 'avatar_electrician.jpg', '2025-12-05 15:14:05', '2025-12-05 15:52:46', 1, 1),
(102, 'patrick.landscape@kazi.rw', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2147483647, 'Patrick Nkunda', 'provider', 'avatar_landscaper.jpg', '2025-12-05 15:14:05', '2025-12-05 15:52:46', 1, 1),
(103, 'emmanuel.carpentry@kazi.rw', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2147483647, 'Emmanuel Habimana', 'provider', 'avatar_carpenter.jpg', '2025-12-05 15:14:05', '2025-12-05 15:52:46', 1, 1),
(104, 'grace.cleaning@kazi.rw', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2147483647, 'Grace Mukamana', 'provider', 'avatar_cleaner.jpg', '2025-12-05 15:14:05', '2025-12-05 15:52:46', 1, 1),
(105, 'david.painting@kazi.rw', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2147483647, 'David Ntirenganya', 'provider', 'avatar_painter.jpg', '2025-12-05 15:14:05', '2025-12-05 15:52:46', 1, 1),
(106, 'sandra.tech@kazi.rw', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2147483647, 'Sandra Uwineza', 'provider', 'avatar_tech.jpg', '2025-12-05 15:14:05', '2025-12-05 15:52:46', 1, 1),
(107, 'joseph.appliance@kazi.rw', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2147483647, 'Joseph Bizimana', 'provider', 'avatar_appliance.jpg', '2025-12-05 15:14:05', '2025-12-05 15:52:46', 1, 1),
(108, 'frank.mechanic@kazi.rw', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2147483647, 'Frank Mutabazi', 'provider', 'avatar_mechanic.jpg', '2025-12-05 15:14:05', '2025-12-05 15:52:46', 1, 1),
(109, 'eric.security@kazi.rw', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2147483647, 'Eric Kayitare', 'provider', 'avatar_security.jpg', '2025-12-05 15:14:05', '2025-12-05 15:52:46', 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `verification_documents`
--

CREATE TABLE `verification_documents` (
  `document_id` int(100) NOT NULL,
  `provider_id` int(100) NOT NULL,
  `document_type` varchar(100) NOT NULL,
  `document_url` varchar(100) NOT NULL,
  `issue_date` date NOT NULL,
  `verification_status` enum('pending','approved','rejected','expired') NOT NULL,
  `verified_by` enum('admin','','','') NOT NULL,
  `verified_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`booking_id`),
  ADD KEY `fk_job_id` (`job_id`),
  ADD KEY `fk_provider_id` (`provider_id`),
  ADD KEY `client_id` (`client_id`);

--
-- Indexes for table `job_applications`
--
ALTER TABLE `job_applications`
  ADD PRIMARY KEY (`application_id`),
  ADD KEY `job_id` (`job_id`),
  ADD KEY `provider_id` (`provider_id`);

--
-- Indexes for table `job_requests`
--
ALTER TABLE `job_requests`
  ADD PRIMARY KEY (`job_id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `location_id` (`location_id`),
  ADD KEY `client_id` (`client_id`);

--
-- Indexes for table `locations`
--
ALTER TABLE `locations`
  ADD PRIMARY KEY (`location_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`message_id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `receiver_id` (`receiver_id`),
  ADD KEY `job_id` (`job_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `booking_id` (`booking_id`);

--
-- Indexes for table `portfolio`
--
ALTER TABLE `portfolio`
  ADD PRIMARY KEY (`portfolio_id`);

--
-- Indexes for table `provider_services`
--
ALTER TABLE `provider_services`
  ADD PRIMARY KEY (`id`),
  ADD KEY `provider_id` (`provider_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`review_id`),
  ADD KEY `booking_id` (`booking_id`),
  ADD KEY `reviewed_id` (`reviewed_id`),
  ADD KEY `reviewer_id` (`reviewer_id`);

--
-- Indexes for table `service_categories`
--
ALTER TABLE `service_categories`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `service_providers`
--
ALTER TABLE `service_providers`
  ADD PRIMARY KEY (`provider_id`),
  ADD KEY `fk_user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `verification_documents`
--
ALTER TABLE `verification_documents`
  ADD PRIMARY KEY (`document_id`),
  ADD KEY `provider_id` (`provider_id`);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `fk_job_id` FOREIGN KEY (`job_id`) REFERENCES `job_requests` (`job_id`),
  ADD CONSTRAINT `fk_provider_id` FOREIGN KEY (`provider_id`) REFERENCES `service_providers` (`provider_id`);

--
-- Constraints for table `job_applications`
--
ALTER TABLE `job_applications`
  ADD CONSTRAINT `job_applications_ibfk_1` FOREIGN KEY (`job_id`) REFERENCES `job_requests` (`job_id`),
  ADD CONSTRAINT `job_applications_ibfk_2` FOREIGN KEY (`provider_id`) REFERENCES `service_providers` (`provider_id`);

--
-- Constraints for table `job_requests`
--
ALTER TABLE `job_requests`
  ADD CONSTRAINT `job_requests_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `service_categories` (`category_id`),
  ADD CONSTRAINT `job_requests_ibfk_2` FOREIGN KEY (`location_id`) REFERENCES `locations` (`location_id`),
  ADD CONSTRAINT `job_requests_ibfk_3` FOREIGN KEY (`client_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `locations`
--
ALTER TABLE `locations`
  ADD CONSTRAINT `locations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `messages_ibfk_3` FOREIGN KEY (`job_id`) REFERENCES `job_requests` (`job_id`);

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`booking_id`);

--
-- Constraints for table `portfolio`
--
ALTER TABLE `portfolio`
  ADD CONSTRAINT `portfolio_ibfk_1` FOREIGN KEY (`portfolio_id`) REFERENCES `service_providers` (`provider_id`);

--
-- Constraints for table `provider_services`
--
ALTER TABLE `provider_services`
  ADD CONSTRAINT `provider_services_ibfk_1` FOREIGN KEY (`provider_id`) REFERENCES `service_providers` (`provider_id`),
  ADD CONSTRAINT `provider_services_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `service_categories` (`category_id`);

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`booking_id`),
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`reviewed_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `reviews_ibfk_3` FOREIGN KEY (`reviewer_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `service_providers`
--
ALTER TABLE `service_providers`
  ADD CONSTRAINT `fk_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `verification_documents`
--
ALTER TABLE `verification_documents`
  ADD CONSTRAINT `verification_documents_ibfk_1` FOREIGN KEY (`provider_id`) REFERENCES `service_providers` (`provider_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
