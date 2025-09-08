-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 07, 2025 at 10:08 AM
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
-- Database: `eam_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `attendance_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `check_in_time` datetime NOT NULL,
  `check_out_time` datetime DEFAULT NULL,
  `remark` enum('Present','Late','Absent','Excused') NOT NULL,
  `penalty` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `enrollment`
--

CREATE TABLE `enrollment` (
  `enrollment_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `section_id` int(11) NOT NULL,
  `enrollment_date` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `enrollment`
--

INSERT INTO `enrollment` (`enrollment_id`, `student_id`, `section_id`, `enrollment_date`, `updated_at`, `status`) VALUES
(8, 18, 1, '2025-09-07 00:00:00', '2025-09-07 08:01:28', 'Inactive'),
(9, 18, 1, '2025-09-07 00:00:00', '2025-09-07 08:06:42', 'Inactive');

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `event_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `event_description` text DEFAULT NULL,
  `event_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `location` varchar(255) NOT NULL,
  `event_type` enum('Open','Exclusive') DEFAULT 'Open',
  `event_status` enum('Upcoming','Ongoing','Finished') NOT NULL DEFAULT 'Upcoming',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_by` int(11) NOT NULL,
  `creator_role` varchar(20) NOT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `abs_penalty` int(11) NOT NULL DEFAULT 0,
  `approval_status` enum('Pending','Approved','Rejected') NOT NULL DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`event_id`, `title`, `event_description`, `event_date`, `start_time`, `end_time`, `location`, `event_type`, `event_status`, `created_at`, `created_by`, `creator_role`, `updated_by`, `updated_at`, `abs_penalty`, `approval_status`) VALUES
(3, 'New', 'New One', '2025-09-06', '15:22:00', '20:23:00', 'Sagbayan Gymnasium', 'Exclusive', 'Upcoming', '2025-09-06 05:23:34', 3, 'admin', 3, '2025-09-07 06:40:47', 90, 'Approved');

-- --------------------------------------------------------

--
-- Table structure for table `event_section`
--

CREATE TABLE `event_section` (
  `event_id` int(11) NOT NULL,
  `section_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `event_section`
--

INSERT INTO `event_section` (`event_id`, `section_id`) VALUES
(3, 1);

-- --------------------------------------------------------

--
-- Table structure for table `excuse_letter`
--

CREATE TABLE `excuse_letter` (
  `letter_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `section_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `attachment` varchar(255) DEFAULT NULL,
  `status` enum('Pending','Reviewed','Approved','Rejected') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `logs`
--

CREATE TABLE `logs` (
  `log_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `user_role` varchar(20) NOT NULL,
  `log_action` text NOT NULL,
  `logged_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notification`
--

CREATE TABLE `notification` (
  `notification_id` int(11) NOT NULL,
  `recipient_id` int(11) DEFAULT NULL,
  `recipient_role` varchar(20) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_by` int(11) NOT NULL,
  `creator_role` varchar(20) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `section`
--

CREATE TABLE `section` (
  `section_id` int(11) NOT NULL,
  `grade` varchar(10) NOT NULL,
  `section` varchar(20) NOT NULL,
  `description` text DEFAULT NULL,
  `teacher_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `section`
--

INSERT INTO `section` (`section_id`, `grade`, `section`, `description`, `teacher_id`, `created_at`, `updated_at`) VALUES
(1, '12', 'ICT', 'TVL - Information and Communication Technology Miss you', 16, '2025-09-06 14:07:18', '2025-09-07 07:26:08'),
(13, '12', 'STEM', 'Sayang Tanan Evening Moments', 17, '2025-09-06 14:50:07', '2025-09-07 07:27:10');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `student_id` int(11) NOT NULL,
  `lrn` char(12) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `mis_id` char(6) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`student_id`, `lrn`, `created_at`, `mis_id`) VALUES
(18, '118398140001', '2025-09-06 15:14:21', '429023');

-- --------------------------------------------------------

--
-- Table structure for table `teacher`
--

CREATE TABLE `teacher` (
  `teacher_id` int(11) NOT NULL,
  `course` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `teacher`
--

INSERT INTO `teacher` (`teacher_id`, `course`) VALUES
(16, '12-Philosophy'),
(17, '12-Physics'),
(19, '11-Probability and Statistics');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','student','teacher','sslg') DEFAULT NULL,
  `profile_photo` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status` enum('Pending','Approved','Rejected') NOT NULL DEFAULT 'Pending',
  `code` int(11) NOT NULL,
  `verification_status` enum('notverified','verified') NOT NULL,
  `firstname` varchar(50) NOT NULL,
  `lastname` varchar(50) NOT NULL,
  `gender` enum('Male','Female') NOT NULL,
  `updated_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `email`, `password`, `role`, `profile_photo`, `created_at`, `updated_at`, `status`, `code`, `verification_status`, `firstname`, `lastname`, `gender`, `updated_by`) VALUES
(3, 'nherrera@sanagustinnhs.net', '$2y$10$N/2o2bjkkrjeU6vRo7q6SOwGhXN/tz7jrWO7NB8aj4V0SfB6lcLRO', 'admin', NULL, '2025-08-30 05:39:57', '2025-09-02 14:50:25', 'Approved', 0, 'verified', 'Nelson', 'Herrera', 'Male', 0),
(16, 'pr1researchers@gmail.com', '$2y$10$9TqGrd.kBRZuaW7JKC3fYOA85jNz/..Yo6vO2qEAFwPDgNgTJVwa.', 'teacher', NULL, '2025-09-06 09:02:08', '2025-09-07 06:00:52', 'Approved', 401206, 'verified', 'John', 'Doe', 'Male', 3),
(17, 'nelsonherrera920815@gmail.com', '$2y$10$RgdvMrxd7KpSsTVkYgpP1u9rT/ECcKo53S0/YpBbzbfXCSiTIvxRq', 'teacher', NULL, '2025-09-06 14:52:26', '2025-09-06 14:54:03', 'Approved', 0, 'verified', 'Default', 'Teacher', 'Female', 3),
(18, 'nelsonherrera9208@gmail.com', '$2y$10$NGyKeb8IRSv4feEtKsQPdu977qHW2EiUJx3e2h2BoI87hqdVXhDry', 'student', NULL, '2025-09-06 15:14:21', '2025-09-06 15:46:40', 'Approved', 0, 'verified', 'Jave', 'Herrera', 'Male', NULL),
(19, 'hvitorillo@sanagustinnhs.net', '$2y$10$SCGvcDo5f6oDTrl7trUS3uoqajlWlx0yStU3HcI/ADOQer7zdNlwm', 'teacher', NULL, '2025-09-07 06:00:22', '2025-09-07 06:00:22', 'Pending', 532121, 'notverified', 'Jack', 'Sparrow', 'Male', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`attendance_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `event_id` (`event_id`);

--
-- Indexes for table `enrollment`
--
ALTER TABLE `enrollment`
  ADD PRIMARY KEY (`enrollment_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `section_id` (`section_id`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`event_id`);

--
-- Indexes for table `event_section`
--
ALTER TABLE `event_section`
  ADD PRIMARY KEY (`event_id`,`section_id`),
  ADD KEY `section_id` (`section_id`);

--
-- Indexes for table `excuse_letter`
--
ALTER TABLE `excuse_letter`
  ADD PRIMARY KEY (`letter_id`),
  ADD KEY `fk_excuse_student` (`student_id`),
  ADD KEY `fk_excuse_section` (`section_id`);

--
-- Indexes for table `logs`
--
ALTER TABLE `logs`
  ADD PRIMARY KEY (`log_id`);

--
-- Indexes for table `notification`
--
ALTER TABLE `notification`
  ADD PRIMARY KEY (`notification_id`);

--
-- Indexes for table `section`
--
ALTER TABLE `section`
  ADD PRIMARY KEY (`section_id`),
  ADD KEY `teacher_id` (`teacher_id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`student_id`),
  ADD UNIQUE KEY `lrn` (`lrn`),
  ADD UNIQUE KEY `unique_lrn` (`lrn`),
  ADD UNIQUE KEY `lrn_unique` (`lrn`);

--
-- Indexes for table `teacher`
--
ALTER TABLE `teacher`
  ADD PRIMARY KEY (`teacher_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `attendance_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `enrollment`
--
ALTER TABLE `enrollment`
  MODIFY `enrollment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `event_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `excuse_letter`
--
ALTER TABLE `excuse_letter`
  MODIFY `letter_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `logs`
--
ALTER TABLE `logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notification`
--
ALTER TABLE `notification`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `section`
--
ALTER TABLE `section`
  MODIFY `section_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `student` (`student_id`),
  ADD CONSTRAINT `attendance_ibfk_2` FOREIGN KEY (`event_id`) REFERENCES `events` (`event_id`);

--
-- Constraints for table `enrollment`
--
ALTER TABLE `enrollment`
  ADD CONSTRAINT `enrolled_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`),
  ADD CONSTRAINT `enrollment_ibfk_2` FOREIGN KEY (`section_id`) REFERENCES `section` (`section_id`);

--
-- Constraints for table `event_section`
--
ALTER TABLE `event_section`
  ADD CONSTRAINT `event_section_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`event_id`),
  ADD CONSTRAINT `event_section_ibfk_2` FOREIGN KEY (`section_id`) REFERENCES `section` (`section_id`);

--
-- Constraints for table `excuse_letter`
--
ALTER TABLE `excuse_letter`
  ADD CONSTRAINT `fk_excuse_section` FOREIGN KEY (`section_id`) REFERENCES `section` (`section_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_excuse_student` FOREIGN KEY (`student_id`) REFERENCES `student` (`student_id`) ON DELETE CASCADE;

--
-- Constraints for table `section`
--
ALTER TABLE `section`
  ADD CONSTRAINT `section_ibfk_1` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `teacher`
--
ALTER TABLE `teacher`
  ADD CONSTRAINT `teacher_ibfk_1` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
