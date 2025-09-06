-- Fixed EAM System Database Schema
-- This file contains the corrected database schema with proper field names and constraints

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `eam_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','student','teacher','sslg') DEFAULT NULL,
  `profile_photo` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status` enum('Pending','Approved','Rejected') NOT NULL DEFAULT 'Pending',
  `code` int(11) NOT NULL DEFAULT 0,
  `verification_status` enum('notverified','verified') NOT NULL DEFAULT 'notverified',
  `firstname` varchar(50) NOT NULL,
  `lastname` varchar(50) NOT NULL,
  `gender` enum('Male','Female') NOT NULL,
  `lrn` varchar(20) DEFAULT NULL,
  `course` varchar(100) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `event_id` int(11) NOT NULL AUTO_INCREMENT,
  `event_name` varchar(255) NOT NULL,
  `event_description` text DEFAULT NULL,
  `event_date` date NOT NULL,
  `event_time` time NOT NULL,
  `event_location` varchar(255) DEFAULT NULL,
  `event_type` enum('open','exclusive') NOT NULL DEFAULT 'open',
  `status` enum('upcoming','ongoing','finished') NOT NULL DEFAULT 'upcoming',
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`event_id`),
  KEY `created_by` (`created_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `attendance_id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `check_in_time` datetime NOT NULL,
  `check_out_time` datetime DEFAULT NULL,
  `remark` enum('Present','Late','Absent','Excused') NOT NULL DEFAULT 'Present',
  `penalty` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`attendance_id`),
  KEY `student_id` (`student_id`),
  KEY `event_id` (`event_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `section`
--

CREATE TABLE `section` (
  `section_id` int(11) NOT NULL AUTO_INCREMENT,
  `section_name` varchar(100) NOT NULL,
  `grade_level` varchar(20) NOT NULL,
  `school_year` varchar(20) NOT NULL,
  `adviser_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`section_id`),
  KEY `adviser_id` (`adviser_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `logs`
--

CREATE TABLE `logs` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `action` varchar(255) NOT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`log_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Insert sample admin user
--

INSERT INTO `users` (`user_id`, `email`, `password`, `role`, `profile_photo`, `created_at`, `updated_at`, `status`, `code`, `verification_status`, `firstname`, `lastname`, `gender`, `lrn`, `course`, `updated_by`) VALUES
(1, 'admin@sanagustinnhs.net', '$2y$10$N/2o2bjkkrjeU6vRo7q6SOwGhXN/tz7jrWO7NB8aj4V0SfB6lcLRO', 'admin', NULL, '2025-01-01 00:00:00', '2025-01-01 00:00:00', 'Approved', 0, 'verified', 'System', 'Administrator', 'Male', NULL, NULL, NULL);

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

