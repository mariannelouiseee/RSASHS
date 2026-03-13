-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 16, 2025 at 03:43 AM
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
-- Database: `rsashs_portal`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `log_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `role` enum('admin','teacher','student') DEFAULT NULL,
  `description` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`log_id`, `user_id`, `role`, `description`, `created_at`) VALUES
(1, 2147483647, 'student', 'Viewed dashboard', '2025-09-10 10:15:24'),
(2, 2147483647, 'student', 'Viewed dashboard', '2025-09-10 10:15:29'),
(3, 2147483647, 'student', 'Viewed dashboard', '2025-09-10 10:15:52'),
(4, 2147483647, 'student', 'Logged in successfully', '2025-09-10 10:32:36'),
(5, 2147483647, 'student', 'Viewed profile', '2025-09-10 10:32:49'),
(6, 2147483647, 'student', 'Viewed subjects', '2025-09-10 10:32:59'),
(7, 2147483647, 'student', 'Logged out', '2025-09-10 10:33:14'),
(8, 0, '', 'Failed login attempt: incorrect password', '2025-09-10 10:33:21'),
(9, 0, '', 'Failed login attempt: incorrect password', '2025-09-10 10:33:41'),
(10, 0, '', 'Failed login attempt: incorrect password', '2025-09-10 10:35:14'),
(11, 0, '', 'Failed login attempt: incorrect password', '2025-09-10 10:36:18'),
(12, 0, '', 'Failed login attempt: incorrect password', '2025-09-10 10:36:25'),
(13, 0, '', 'Failed login attempt: incorrect password', '2025-09-10 10:36:34'),
(14, 0, '', 'Failed login attempt: incorrect password', '2025-09-10 10:36:41'),
(15, 2147483647, 'student', 'Logged in successfully', '2025-09-10 10:37:10'),
(16, 2147483647, 'student', 'Logged out', '2025-09-10 10:37:12'),
(17, 0, 'admin', 'Logged in successfully', '2025-09-10 10:38:14'),
(18, 0, '', 'Failed login attempt: incorrect password', '2025-09-10 10:38:57'),
(19, 0, '', 'Failed login attempt: incorrect password', '2025-09-10 10:39:10'),
(20, 0, '', 'Failed login attempt: incorrect password', '2025-09-10 10:39:22'),
(21, 0, 'admin', 'Logged in successfully', '2025-09-10 10:39:31'),
(22, 0, 'admin', 'Logged in successfully', '2025-09-10 10:41:35'),
(23, 0, 'admin', 'Logged in successfully', '2025-09-10 10:43:32'),
(24, 0, 'admin', 'Logged in successfully', '2025-09-10 10:45:32'),
(25, 0, 'admin', 'Visited dashboard', '2025-09-10 10:45:33'),
(26, 0, 'admin', 'Visited activity logs page', '2025-09-10 10:45:38'),
(27, 0, 'admin', 'Visited activity logs page', '2025-09-10 10:45:41'),
(28, 0, 'admin', 'Visited activity logs page', '2025-09-10 10:45:44'),
(29, 0, 'admin', 'Visited activity logs page', '2025-09-10 10:45:47'),
(30, 0, 'admin', 'Visited dashboard', '2025-09-10 10:45:50'),
(31, 0, 'admin', 'Visited Manage Subjects page', '2025-09-10 10:45:54'),
(32, 0, 'admin', 'Visited dashboard', '2025-09-10 10:46:11'),
(33, 0, 'admin', 'Visited dashboard', '2025-09-10 10:48:08'),
(34, 0, 'admin', 'Visited dashboard', '2025-09-10 10:48:47'),
(35, 0, 'admin', 'Visited dashboard', '2025-09-10 10:49:46'),
(36, 0, 'admin', 'Visited dashboard', '2025-09-10 10:50:13'),
(37, 0, 'admin', 'Visited dashboard', '2025-09-10 10:52:16'),
(38, 0, 'admin', 'Visited dashboard', '2025-09-10 10:53:58'),
(39, 0, 'admin', 'Visited Manage Subjects page', '2025-09-10 10:54:09'),
(40, 0, 'admin', 'Visited dashboard', '2025-09-10 10:54:10'),
(41, 0, 'admin', 'Visited dashboard', '2025-09-10 10:55:30'),
(42, 0, 'admin', 'Visited activity logs page', '2025-09-10 10:55:33'),
(43, 0, 'admin', 'Visited activity logs page', '2025-09-10 10:55:37'),
(44, 0, 'admin', 'Visited activity logs page', '2025-09-10 10:55:39'),
(45, 0, 'admin', 'Visited activity logs page', '2025-09-10 10:55:42'),
(46, 0, 'admin', 'Visited activity logs page', '2025-09-10 10:55:44'),
(47, 0, 'admin', 'Visited activity logs page', '2025-09-10 10:55:49'),
(48, 0, 'admin', 'Visited activity logs page', '2025-09-10 10:56:43'),
(49, 0, 'admin', 'Visited activity logs page', '2025-09-10 10:56:47'),
(50, 0, 'admin', 'Visited activity logs page', '2025-09-10 10:56:47'),
(51, 0, 'admin', 'Visited activity logs page', '2025-09-10 10:56:48'),
(52, 0, 'admin', 'Visited activity logs page', '2025-09-10 10:56:49'),
(53, 0, 'admin', 'Visited dashboard', '2025-09-10 10:56:50'),
(54, 0, 'admin', 'Viewed student account: 20243110546', '2025-09-10 10:58:06'),
(55, 0, 'admin', 'Viewed student account: 20243110546', '2025-09-10 10:59:42'),
(56, 0, 'admin', 'Viewed student account: 20243110546', '2025-09-10 11:02:23'),
(57, 0, 'admin', 'Viewed student account: 103728080009', '2025-09-10 11:02:27'),
(58, 0, 'admin', 'Visited dashboard', '2025-09-10 11:02:29'),
(59, 0, 'admin', 'Visited Teachers page', '2025-09-10 11:02:42'),
(60, 0, 'admin', 'Viewed subjects of teacher ID: epira741', '2025-09-10 11:02:43'),
(61, 0, 'admin', 'Fetched 0 subject(s) for teacher \'Emerjane Pira\' (ID: epira741)', '2025-09-10 11:02:43'),
(62, 0, 'admin', 'Viewed subjects of teacher ID: mmedrano485', '2025-09-10 11:02:45'),
(63, 0, 'admin', 'Fetched 4 subject(s) for teacher \'Marianne Medrano\' (ID: mmedrano485)', '2025-09-10 11:02:45'),
(64, 0, 'admin', 'Viewed students of section ID: 34 (HAHA) - 1 student(s) retrieved', '2025-09-10 11:02:46'),
(65, 0, 'admin', 'Visited dashboard', '2025-09-10 11:02:48'),
(66, 0, 'admin', 'Visited activity logs page', '2025-09-10 11:02:58'),
(67, 0, 'admin', 'Visited dashboard', '2025-09-10 11:03:05'),
(68, 0, 'admin', 'Logged out', '2025-09-10 11:03:07'),
(69, 0, 'admin', 'Logged in successfully', '2025-09-13 08:00:03'),
(70, 0, 'admin', 'Visited dashboard', '2025-09-13 08:00:04'),
(71, 0, 'admin', 'Viewed student account: 20243110546', '2025-09-13 08:00:22'),
(72, 2147483647, 'student', 'Logged in successfully', '2025-09-13 08:02:25'),
(73, 2147483647, 'student', 'Viewed profile', '2025-09-13 08:02:30'),
(74, 2147483647, 'student', 'Viewed subjects', '2025-09-13 08:02:36'),
(75, 2147483647, 'student', 'Logged out', '2025-09-13 08:02:41'),
(76, 0, 'admin', 'Logged in successfully', '2025-09-13 08:02:51'),
(77, 0, 'admin', 'Visited dashboard', '2025-09-13 08:02:51'),
(78, 0, 'admin', 'Visited dashboard', '2025-09-13 08:02:57'),
(79, 0, 'admin', 'Viewed student account: 20243110546', '2025-09-13 08:03:04'),
(80, 0, 'admin', 'Viewed teacher account: epira741', '2025-09-13 08:03:14'),
(81, 0, 'admin', 'Viewed teacher account: nvidal627', '2025-09-13 08:03:18'),
(82, 0, 'admin', 'Visited Manage Subjects page', '2025-09-13 08:03:21'),
(83, 0, 'admin', 'Visited Manage Subjects page', '2025-09-13 08:03:33'),
(84, 0, 'admin', 'Edited subject ID: 14 to 111 - English (Teacher ID: epira741)', '2025-09-13 08:03:33'),
(85, 0, 'admin', 'Visited Manage Sections page', '2025-09-13 08:03:37'),
(86, 0, 'admin', 'Visited Manage Subjects page', '2025-09-13 08:03:40'),
(87, 0, 'admin', 'Visited Manage Sections page', '2025-09-13 08:03:43'),
(88, 0, 'admin', 'Reassign mode: Viewed student list for section \'HAHA\' (Year Level: Grade 11) - 1 student(s) retrieved', '2025-09-13 08:03:45'),
(89, 0, 'admin', 'Viewed subjects for section \'HAHA\' (Year Level: Grade 11) - 2 subject(s) retrieved', '2025-09-13 08:03:45'),
(90, 0, 'admin', 'Visited Manage Sections page', '2025-09-13 08:03:51'),
(91, 0, 'admin', 'Assigned/Reassigned students and subjects for section: HAHA (Grade 11)', '2025-09-13 08:03:51'),
(92, 0, 'admin', 'Visited Manage Sections page', '2025-09-13 08:03:53'),
(93, 0, 'admin', 'Visited Teachers page', '2025-09-13 08:03:59'),
(94, 0, 'admin', 'Viewed subjects of teacher ID: epira741', '2025-09-13 08:04:03'),
(95, 0, 'admin', 'Fetched 1 subject(s) for teacher \'Emerjane Pira\' (ID: epira741)', '2025-09-13 08:04:03'),
(96, 0, 'admin', 'Viewed students of section ID: 34 (HAHA) - 1 student(s) retrieved', '2025-09-13 08:04:05');

-- --------------------------------------------------------

--
-- Table structure for table `sections`
--

CREATE TABLE `sections` (
  `section_id` int(10) UNSIGNED NOT NULL,
  `year_level` varchar(50) NOT NULL,
  `strand` varchar(100) NOT NULL,
  `section_name` varchar(100) NOT NULL,
  `school_year` varchar(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sections`
--

INSERT INTO `sections` (`section_id`, `year_level`, `strand`, `section_name`, `school_year`, `created_at`) VALUES
(34, 'Grade 11', 'ABM', 'HAHA', '2025-2026', '2025-09-10 06:37:49'),
(35, 'Grade 12', 'STEM', 'DANG', '2025-2026', '2025-09-10 07:18:57');

-- --------------------------------------------------------

--
-- Table structure for table `section_students`
--

CREATE TABLE `section_students` (
  `id` int(11) NOT NULL,
  `section_id` int(10) UNSIGNED NOT NULL,
  `student_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `section_subjects`
--

CREATE TABLE `section_subjects` (
  `id` int(11) NOT NULL,
  `section_id` int(10) UNSIGNED NOT NULL,
  `subject_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `section_subjects`
--

INSERT INTO `section_subjects` (`id`, `section_id`, `subject_id`) VALUES
(75, 35, 12),
(76, 35, 13),
(77, 34, 12),
(78, 34, 14),
(79, 34, 15);

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `student_id` varchar(20) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `middle_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `extension_name` varchar(20) DEFAULT NULL,
  `gender` varchar(10) NOT NULL,
  `birthday` date NOT NULL,
  `contact` varchar(20) NOT NULL,
  `email` varchar(100) NOT NULL,
  `address` text NOT NULL,
  `last_school` varchar(100) NOT NULL,
  `school_address` varchar(150) NOT NULL,
  `date_attended` varchar(50) NOT NULL,
  `honors_received` varchar(100) DEFAULT NULL,
  `father_name` varchar(100) DEFAULT NULL,
  `father_occupation` varchar(100) DEFAULT NULL,
  `mother_name` varchar(100) DEFAULT NULL,
  `mother_occupation` varchar(100) DEFAULT NULL,
  `guardian_name` varchar(100) DEFAULT NULL,
  `guardian_contact` varchar(20) DEFAULT NULL,
  `student_image` varchar(255) DEFAULT NULL,
  `year_level` varchar(20) DEFAULT NULL,
  `section_name` varchar(50) DEFAULT NULL,
  `school_year` varchar(20) DEFAULT NULL,
  `subjects` text DEFAULT NULL,
  `teacher_id` int(11) DEFAULT NULL,
  `subject_teachers` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `student_id`, `first_name`, `middle_name`, `last_name`, `extension_name`, `gender`, `birthday`, `contact`, `email`, `address`, `last_school`, `school_address`, `date_attended`, `honors_received`, `father_name`, `father_occupation`, `mother_name`, `mother_occupation`, `guardian_name`, `guardian_contact`, `student_image`, `year_level`, `section_name`, `school_year`, `subjects`, `teacher_id`, `subject_teachers`) VALUES
(17, '20243110546', 'Russell', 'Alzate', 'Taguinin', '', 'Male', '2002-08-15', '09067832145', 'RussellT@gmail.com', 'Nuesa, Roxas, Isabela', 'Roxas Stand Alone Senior High School', 'Roxas, Isabela', '2021', 'With High Honors', 'Noriel Taguinin', 'Teacher', 'Hanna Taguinin', 'Nurse', '', '', 'student_68ade73524c5e1.64275620.jpg', 'Grade 12', 'DANG', '2025-2026', NULL, NULL, NULL),
(19, '103728080009', 'fesf', 'sfsd', 'dsfs', '', 'Male', '2006-07-24', '09067848215', 'medranoguia12345@gmail.com', 'ewwesd', 'sd', 'fdsf', '2016', 'sddfs', 'sdfsf', 'dfs', 'sdfs', 'sfds', '', '', 'student_68bc669a17d460.28121149.png', 'Grade 11', 'HAHA', '2025-2026', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `subjects`
--

CREATE TABLE `subjects` (
  `subject_id` int(10) UNSIGNED NOT NULL,
  `subject_code` varchar(50) NOT NULL,
  `subject_name` varchar(255) NOT NULL,
  `teacher_id` varchar(50) DEFAULT NULL,
  `year_level` varchar(20) DEFAULT NULL,
  `section_name` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subjects`
--

INSERT INTO `subjects` (`subject_id`, `subject_code`, `subject_name`, `teacher_id`, `year_level`, `section_name`) VALUES
(12, '555', 'English', 'mmedrano485', NULL, NULL),
(13, '666', 'Science', 'mmedrano485', NULL, NULL),
(14, '111', 'English', 'epira741', NULL, NULL),
(15, '123', 'Filipino', 'nvidal627', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `teachers`
--

CREATE TABLE `teachers` (
  `teacher_id` varchar(50) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) NOT NULL,
  `teacher_image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `teachers`
--

INSERT INTO `teachers` (`teacher_id`, `first_name`, `middle_name`, `last_name`, `teacher_image`) VALUES
('epira741', 'Emerjane', 'Daliri', 'Pira', 'epira741_1757496096.png'),
('mmedrano485', 'Marianne', 'Valencia', 'Medrano', 'mmedrano485_1757231250.png'),
('nvidal627', 'Noriel', 'Dolado', 'Vidal', 'nvidal627_1756227458.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('student','teacher','admin') DEFAULT 'student'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`) VALUES
(28, '20243110546', '$2y$10$ErQkQrFEx2S3glG8c72MdOYRv733qSlzo8XWRL8hDLNV0PnMW1yIK', 'student'),
(29, 'nvidal627', '$2y$10$MzME80zyjCWvhikj9t35jOOulcYSFtq9w1KQ4kvxCyoedHPwJlJAO', 'teacher'),
(31, '103728080009', '$2y$10$mOQJf5H3LlQTqIehjMztKO0Zg/nWzgTtd3ZnrYYmeXkVEybte3dpi', 'student'),
(32, 'mmedrano485', '$2y$10$atps5kBEVMkZV0S0DMpch.BQAu2kpdshTE/00pWwH2j9SKAUXPjsu', 'teacher'),
(33, 'epira741', '$2y$10$PeVhHjXHYe/a264HXQ1idOPQMLbXvQjKBP8nXrjxhK2Ru35vzQYU6', 'teacher'),
(37, 'admin', '$2y$10$9nqJUJIw3.G9JnBkPZ34suiZsD3odKh1snFjBf78JFQ/uRkXXwmQW', 'admin');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`log_id`);

--
-- Indexes for table `sections`
--
ALTER TABLE `sections`
  ADD PRIMARY KEY (`section_id`);

--
-- Indexes for table `section_students`
--
ALTER TABLE `section_students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_section_student` (`section_id`,`student_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `section_subjects`
--
ALTER TABLE `section_subjects`
  ADD PRIMARY KEY (`id`),
  ADD KEY `section_id` (`section_id`),
  ADD KEY `subject_id` (`subject_id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`subject_id`),
  ADD KEY `fk_teacher` (`teacher_id`);

--
-- Indexes for table `teachers`
--
ALTER TABLE `teachers`
  ADD PRIMARY KEY (`teacher_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=97;

--
-- AUTO_INCREMENT for table `sections`
--
ALTER TABLE `sections`
  MODIFY `section_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `section_students`
--
ALTER TABLE `section_students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `section_subjects`
--
ALTER TABLE `section_subjects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=80;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `subject_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `section_students`
--
ALTER TABLE `section_students`
  ADD CONSTRAINT `section_students_ibfk_1` FOREIGN KEY (`section_id`) REFERENCES `sections` (`section_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `section_students_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `section_subjects`
--
ALTER TABLE `section_subjects`
  ADD CONSTRAINT `section_subjects_ibfk_1` FOREIGN KEY (`section_id`) REFERENCES `sections` (`section_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `section_subjects_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`subject_id`) ON DELETE CASCADE;

--
-- Constraints for table `subjects`
--
ALTER TABLE `subjects`
  ADD CONSTRAINT `fk_teacher` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`teacher_id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
