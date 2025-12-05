-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 05, 2025 at 02:29 PM
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

-- --------------------------------------------------------

--
-- Table structure for table `service_providers`
--

CREATE TABLE `service_providers` (
  `provider_id` int(100) NOT NULL,
  `user_id` int(100) NOT NULL,
  `business_name` varchar(100) NOT NULL,
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
  `criminal_check` tinyint(1) NOT NULL,
  `last_verification_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `service_providers`
--

INSERT INTO `service_providers` (`provider_id`, `user_id`, `business_name`, `bio`, `years_of_experience`, `hourly_rate`, `availability_status`, `rating_average`, `total_jobs_completed`, `verification_status`, `identity_document`, `background_check_status`, `identity_verified`, `criminal_check`, `last_verification_date`) VALUES
(0, 0, 'Carpentry', '', 5, 25000.00, 'available', 0.00, 10, 'verified', 'ID', 'clean', 0, 0, '0000-00-00');

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
  `user_type` enum('client','service_provider','admin','') NOT NULL,
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
(0, 'summer@gmail.com', '$2y$10$L4jHpo78qC6Mm5ePV0J.meU6.W4VcggkhAoHTw1qZ6GLszapyeQ6C', 786061493, 'Summer Winter', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 0);

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
