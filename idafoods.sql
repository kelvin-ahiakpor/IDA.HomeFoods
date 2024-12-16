-- phpMyAdmin SQL Dump
-- version 5.2.1deb3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Dec 16, 2024 at 03:52 PM
-- Server version: 8.0.40-0ubuntu0.24.04.1
-- PHP Version: 8.3.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `webtech_fall2024_kelvin_ahiakpor`
--

-- --------------------------------------------------------

--
-- Table structure for table `ida_admin_dashboard_logs`
--

CREATE TABLE `ida_admin_dashboard_logs` (
  `log_id` int NOT NULL,
  `admin_id` int NOT NULL,
  `action` text NOT NULL,
  `timestamp` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `affected_user_id` int DEFAULT NULL,
  `action_type` enum('Profile_Update','Status_Change','Certification_Approval','Certification_Rejection','Specialization_Approval','Specialization_Rejection','Account_Deletion','Client_Ban','Client_Unban','Client_Profile_Update','Client_Account_Deletion','Booking_Cancellation','Booking_Refund','Product_Addition','Product_Update','Product_Removal','Payment_Refund','Payment_Dispute_Resolution','System_Setting_Update','Price_Update','Consultant_Application','Consultant_Activation','Consultant_Rejection','Consultant_Deactivation','Booking_Creation','Booking_Completion','Rating_Submission','Consultant_Approval') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `details` json DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ida_availability`
--

CREATE TABLE `ida_availability` (
  `availability_id` int NOT NULL,
  `consultant_id` int NOT NULL,
  `date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ida_bookings`
--

CREATE TABLE `ida_bookings` (
  `booking_id` int NOT NULL,
  `client_id` int NOT NULL,
  `consultant_id` int NOT NULL,
  `booking_date` date NOT NULL,
  `time_slot` varchar(50) NOT NULL,
  `status` enum('Pending','Approved','Cancelled') DEFAULT 'Pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `notes` text,
  `completed_at` timestamp NULL DEFAULT NULL,
  `is_cancelled` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ida_cart`
--

CREATE TABLE `ida_cart` (
  `cart_id` int NOT NULL,
  `user_id` int NOT NULL,
  `product_id` int NOT NULL,
  `quantity` int NOT NULL DEFAULT '1',
  `added_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ida_consultants`
--

CREATE TABLE `ida_consultants` (
  `consultant_id` int NOT NULL,
  `expertise` text NOT NULL,
  `background` text,
  `hourly_rate` decimal(10,2) DEFAULT '0.00',
  `availability` json NOT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `status` enum('Active','Inactive','Pending') DEFAULT 'Pending',
  `bio` text NOT NULL,
  `last_active` timestamp NULL DEFAULT NULL,
  `total_clients` int DEFAULT '0',
  `rating` decimal(3,2) DEFAULT '0.00',
  `joined_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ida_consultant_applications`
--

CREATE TABLE `ida_consultant_applications` (
  `application_id` int NOT NULL,
  `user_id` int NOT NULL,
  `background` text,
  `hourly_rate` decimal(10,2) DEFAULT '0.00',
  `expertise` json DEFAULT NULL,
  `status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `submitted_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `reviewed_by` int DEFAULT NULL,
  `rejection_reason` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ida_consultant_certifications`
--

CREATE TABLE `ida_consultant_certifications` (
  `certification_id` int NOT NULL,
  `consultant_id` int DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `issuer` varchar(100) DEFAULT NULL,
  `year` varchar(4) DEFAULT NULL,
  `status` enum('Active','Pending','Rejected') DEFAULT 'Active',
  `proof_document` varchar(255) DEFAULT NULL,
  `submitted_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ida_consultant_metrics`
--

CREATE TABLE `ida_consultant_metrics` (
  `consultant_id` int NOT NULL,
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL,
  `total_clients` int DEFAULT '0',
  `active_clients_30d` int DEFAULT '0',
  `total_sessions` int DEFAULT '0',
  `completed_sessions` int DEFAULT '0',
  `ongoing_sessions` int DEFAULT '0',
  `upcoming_sessions` int DEFAULT '0',
  `average_rating` decimal(3,2) DEFAULT '0.00',
  `total_ratings` int DEFAULT '0',
  `joined_date` timestamp NULL DEFAULT NULL,
  `last_active` timestamp NULL DEFAULT NULL,
  `sessions_this_month` int DEFAULT '0',
  `sessions_last_month` int DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ida_consultant_sessions`
--

CREATE TABLE `ida_consultant_sessions` (
  `session_id` int NOT NULL,
  `consultant_id` int DEFAULT NULL,
  `client_id` int DEFAULT NULL,
  `session_type` varchar(100) DEFAULT NULL,
  `start_time` timestamp NULL DEFAULT NULL,
  `duration` int DEFAULT NULL,
  `status` enum('Scheduled','In Progress','Completed','Cancelled') DEFAULT 'Scheduled',
  `price` decimal(10,2) NOT NULL DEFAULT '0.00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ida_consultant_specializations`
--

CREATE TABLE `ida_consultant_specializations` (
  `specialization_id` int NOT NULL,
  `consultant_id` int DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `status` enum('Active','Pending','Rejected') DEFAULT 'Active',
  `description` text,
  `submitted_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ida_emails`
--

CREATE TABLE `ida_emails` (
  `email_id` int NOT NULL,
  `user_id` int NOT NULL,
  `email_subject` varchar(255) NOT NULL,
  `email_body` text NOT NULL,
  `sent_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ida_forum_comments`
--

CREATE TABLE `ida_forum_comments` (
  `comment_id` int NOT NULL,
  `post_id` int NOT NULL,
  `user_id` int NOT NULL,
  `comment` text NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ida_forum_posts`
--

CREATE TABLE `ida_forum_posts` (
  `post_id` int NOT NULL,
  `user_id` int NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ida_orders`
--

CREATE TABLE `ida_orders` (
  `order_id` int NOT NULL,
  `user_id` int NOT NULL,
  `status` enum('Pending','Processing','Delivered') DEFAULT 'Pending',
  `total_price` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ida_order_items`
--

CREATE TABLE `ida_order_items` (
  `order_item_id` int NOT NULL,
  `order_id` int NOT NULL,
  `product_id` int NOT NULL,
  `quantity` int NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ida_products`
--

CREATE TABLE `ida_products` (
  `product_id` int NOT NULL,
  `product_name` varchar(100) NOT NULL,
  `description` text,
  `price` decimal(10,2) NOT NULL,
  `stock_quantity` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ida_ratings`
--

CREATE TABLE `ida_ratings` (
  `rating_id` int NOT NULL,
  `product_id` int NOT NULL,
  `user_id` int NOT NULL,
  `rating` int DEFAULT NULL,
  `review` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ;

-- --------------------------------------------------------

--
-- Table structure for table `ida_session_ratings`
--

CREATE TABLE `ida_session_ratings` (
  `rating_id` int NOT NULL,
  `booking_id` int NOT NULL,
  `client_id` int NOT NULL,
  `consultant_id` int NOT NULL,
  `rating` decimal(2,1) NOT NULL,
  `feedback` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ida_testimonials`
--

CREATE TABLE `ida_testimonials` (
  `testimonial_id` int NOT NULL,
  `user_id` int NOT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `approved` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ida_users`
--

CREATE TABLE `ida_users` (
  `user_id` int NOT NULL,
  `role` enum('Admin','Consultant','Client') NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `is_active` tinyint(1) DEFAULT '1',
  `marketing_opt_in` tinyint(1) NOT NULL DEFAULT '0',
  `phone` varchar(15) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `ida_admin_dashboard_logs`
--
ALTER TABLE `ida_admin_dashboard_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `admin_id` (`admin_id`),
  ADD KEY `affected_user_id` (`affected_user_id`);

--
-- Indexes for table `ida_availability`
--
ALTER TABLE `ida_availability`
  ADD PRIMARY KEY (`availability_id`),
  ADD KEY `consultant_id` (`consultant_id`);

--
-- Indexes for table `ida_bookings`
--
ALTER TABLE `ida_bookings`
  ADD PRIMARY KEY (`booking_id`),
  ADD KEY `client_id` (`client_id`),
  ADD KEY `consultant_id` (`consultant_id`);

--
-- Indexes for table `ida_cart`
--
ALTER TABLE `ida_cart`
  ADD PRIMARY KEY (`cart_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `ida_consultants`
--
ALTER TABLE `ida_consultants`
  ADD PRIMARY KEY (`consultant_id`);

--
-- Indexes for table `ida_consultant_applications`
--
ALTER TABLE `ida_consultant_applications`
  ADD PRIMARY KEY (`application_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `reviewed_by` (`reviewed_by`);

--
-- Indexes for table `ida_consultant_certifications`
--
ALTER TABLE `ida_consultant_certifications`
  ADD PRIMARY KEY (`certification_id`),
  ADD KEY `consultant_id` (`consultant_id`);

--
-- Indexes for table `ida_consultant_metrics`
--
ALTER TABLE `ida_consultant_metrics`
  ADD PRIMARY KEY (`consultant_id`);

--
-- Indexes for table `ida_consultant_sessions`
--
ALTER TABLE `ida_consultant_sessions`
  ADD PRIMARY KEY (`session_id`),
  ADD KEY `consultant_id` (`consultant_id`),
  ADD KEY `client_id` (`client_id`);

--
-- Indexes for table `ida_consultant_specializations`
--
ALTER TABLE `ida_consultant_specializations`
  ADD PRIMARY KEY (`specialization_id`),
  ADD KEY `consultant_id` (`consultant_id`);

--
-- Indexes for table `ida_emails`
--
ALTER TABLE `ida_emails`
  ADD PRIMARY KEY (`email_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `ida_forum_comments`
--
ALTER TABLE `ida_forum_comments`
  ADD PRIMARY KEY (`comment_id`),
  ADD KEY `post_id` (`post_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `ida_forum_posts`
--
ALTER TABLE `ida_forum_posts`
  ADD PRIMARY KEY (`post_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `ida_orders`
--
ALTER TABLE `ida_orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `ida_order_items`
--
ALTER TABLE `ida_order_items`
  ADD PRIMARY KEY (`order_item_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `ida_products`
--
ALTER TABLE `ida_products`
  ADD PRIMARY KEY (`product_id`);

--
-- Indexes for table `ida_ratings`
--
ALTER TABLE `ida_ratings`
  ADD PRIMARY KEY (`rating_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `ida_session_ratings`
--
ALTER TABLE `ida_session_ratings`
  ADD PRIMARY KEY (`rating_id`),
  ADD KEY `booking_id` (`booking_id`),
  ADD KEY `client_id` (`client_id`),
  ADD KEY `consultant_id` (`consultant_id`);

--
-- Indexes for table `ida_testimonials`
--
ALTER TABLE `ida_testimonials`
  ADD PRIMARY KEY (`testimonial_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `ida_users`
--
ALTER TABLE `ida_users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `ida_admin_dashboard_logs`
--
ALTER TABLE `ida_admin_dashboard_logs`
  MODIFY `log_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ida_availability`
--
ALTER TABLE `ida_availability`
  MODIFY `availability_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ida_bookings`
--
ALTER TABLE `ida_bookings`
  MODIFY `booking_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ida_cart`
--
ALTER TABLE `ida_cart`
  MODIFY `cart_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ida_consultant_applications`
--
ALTER TABLE `ida_consultant_applications`
  MODIFY `application_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ida_consultant_certifications`
--
ALTER TABLE `ida_consultant_certifications`
  MODIFY `certification_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ida_consultant_sessions`
--
ALTER TABLE `ida_consultant_sessions`
  MODIFY `session_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ida_consultant_specializations`
--
ALTER TABLE `ida_consultant_specializations`
  MODIFY `specialization_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ida_emails`
--
ALTER TABLE `ida_emails`
  MODIFY `email_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ida_forum_comments`
--
ALTER TABLE `ida_forum_comments`
  MODIFY `comment_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ida_forum_posts`
--
ALTER TABLE `ida_forum_posts`
  MODIFY `post_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ida_orders`
--
ALTER TABLE `ida_orders`
  MODIFY `order_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ida_order_items`
--
ALTER TABLE `ida_order_items`
  MODIFY `order_item_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ida_products`
--
ALTER TABLE `ida_products`
  MODIFY `product_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ida_ratings`
--
ALTER TABLE `ida_ratings`
  MODIFY `rating_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ida_session_ratings`
--
ALTER TABLE `ida_session_ratings`
  MODIFY `rating_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ida_testimonials`
--
ALTER TABLE `ida_testimonials`
  MODIFY `testimonial_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ida_users`
--
ALTER TABLE `ida_users`
  MODIFY `user_id` int NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `ida_admin_dashboard_logs`
--
ALTER TABLE `ida_admin_dashboard_logs`
  ADD CONSTRAINT `ida_admin_dashboard_logs_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `ida_users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ida_admin_dashboard_logs_ibfk_2` FOREIGN KEY (`affected_user_id`) REFERENCES `ida_users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `ida_availability`
--
ALTER TABLE `ida_availability`
  ADD CONSTRAINT `ida_availability_ibfk_1` FOREIGN KEY (`consultant_id`) REFERENCES `ida_users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `ida_bookings`
--
ALTER TABLE `ida_bookings`
  ADD CONSTRAINT `ida_bookings_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `ida_users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ida_bookings_ibfk_2` FOREIGN KEY (`consultant_id`) REFERENCES `ida_consultants` (`consultant_id`) ON DELETE CASCADE;

--
-- Constraints for table `ida_cart`
--
ALTER TABLE `ida_cart`
  ADD CONSTRAINT `ida_cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `ida_users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ida_cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `ida_products` (`product_id`) ON DELETE CASCADE;

--
-- Constraints for table `ida_consultants`
--
ALTER TABLE `ida_consultants`
  ADD CONSTRAINT `ida_consultants_ibfk_1` FOREIGN KEY (`consultant_id`) REFERENCES `ida_users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `ida_consultant_applications`
--
ALTER TABLE `ida_consultant_applications`
  ADD CONSTRAINT `ida_consultant_applications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `ida_users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ida_consultant_applications_ibfk_2` FOREIGN KEY (`reviewed_by`) REFERENCES `ida_users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `ida_consultant_certifications`
--
ALTER TABLE `ida_consultant_certifications`
  ADD CONSTRAINT `ida_consultant_certifications_ibfk_1` FOREIGN KEY (`consultant_id`) REFERENCES `ida_consultants` (`consultant_id`) ON DELETE CASCADE;

--
-- Constraints for table `ida_consultant_metrics`
--
ALTER TABLE `ida_consultant_metrics`
  ADD CONSTRAINT `ida_consultant_metrics_ibfk_1` FOREIGN KEY (`consultant_id`) REFERENCES `ida_consultants` (`consultant_id`);

--
-- Constraints for table `ida_consultant_sessions`
--
ALTER TABLE `ida_consultant_sessions`
  ADD CONSTRAINT `ida_consultant_sessions_ibfk_1` FOREIGN KEY (`consultant_id`) REFERENCES `ida_consultants` (`consultant_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ida_consultant_sessions_ibfk_2` FOREIGN KEY (`client_id`) REFERENCES `ida_users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `ida_consultant_specializations`
--
ALTER TABLE `ida_consultant_specializations`
  ADD CONSTRAINT `ida_consultant_specializations_ibfk_1` FOREIGN KEY (`consultant_id`) REFERENCES `ida_consultants` (`consultant_id`) ON DELETE CASCADE;

--
-- Constraints for table `ida_emails`
--
ALTER TABLE `ida_emails`
  ADD CONSTRAINT `ida_emails_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `ida_users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `ida_forum_comments`
--
ALTER TABLE `ida_forum_comments`
  ADD CONSTRAINT `ida_forum_comments_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `ida_forum_posts` (`post_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ida_forum_comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `ida_users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `ida_forum_posts`
--
ALTER TABLE `ida_forum_posts`
  ADD CONSTRAINT `ida_forum_posts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `ida_users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `ida_orders`
--
ALTER TABLE `ida_orders`
  ADD CONSTRAINT `ida_orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `ida_users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `ida_order_items`
--
ALTER TABLE `ida_order_items`
  ADD CONSTRAINT `ida_order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `ida_orders` (`order_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ida_order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `ida_products` (`product_id`) ON DELETE CASCADE;

--
-- Constraints for table `ida_ratings`
--
ALTER TABLE `ida_ratings`
  ADD CONSTRAINT `ida_ratings_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `ida_products` (`product_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ida_ratings_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `ida_users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `ida_session_ratings`
--
ALTER TABLE `ida_session_ratings`
  ADD CONSTRAINT `ida_session_ratings_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `ida_bookings` (`booking_id`),
  ADD CONSTRAINT `ida_session_ratings_ibfk_2` FOREIGN KEY (`client_id`) REFERENCES `ida_users` (`user_id`),
  ADD CONSTRAINT `ida_session_ratings_ibfk_3` FOREIGN KEY (`consultant_id`) REFERENCES `ida_users` (`user_id`);

--
-- Constraints for table `ida_testimonials`
--
ALTER TABLE `ida_testimonials`
  ADD CONSTRAINT `ida_testimonials_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `ida_users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
