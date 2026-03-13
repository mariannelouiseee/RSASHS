-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 11, 2026 at 11:49 AM
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
-- Database: `crud_sample`
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

-- --------------------------------------------------------

--
-- Table structure for table `grades`
--

CREATE TABLE `grades` (
  `grade_id` int(11) NOT NULL,
  `student_id` bigint(20) UNSIGNED NOT NULL,
  `subject_id` int(10) UNSIGNED NOT NULL,
  `school_year` varchar(20) NOT NULL,
  `q1` decimal(5,2) DEFAULT NULL,
  `q2` decimal(5,2) DEFAULT NULL,
  `first_sem_final` decimal(5,2) DEFAULT NULL,
  `gwa_first_sem` decimal(5,2) DEFAULT NULL,
  `q3` decimal(5,2) DEFAULT NULL,
  `q4` decimal(5,2) DEFAULT NULL,
  `second_sem_final` decimal(5,2) DEFAULT NULL,
  `gwa_second_sem` decimal(5,2) DEFAULT NULL,
  `final` decimal(5,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `grades`
--

INSERT INTO `grades` (`grade_id`, `student_id`, `subject_id`, `school_year`, `q1`, `q2`, `first_sem_final`, `gwa_first_sem`, `q3`, `q4`, `second_sem_final`, `gwa_second_sem`, `final`, `created_at`, `updated_at`) VALUES
(37, 103728080009, 14, '2025-2026', 78.00, 78.00, 78.00, 78.00, 78.00, 78.00, 78.00, 78.00, 78.00, '2025-12-06 13:32:39', '2025-12-16 01:44:17'),
(38, 20243110546, 14, '2025-2026', 78.00, 87.00, 82.50, 82.50, NULL, NULL, NULL, NULL, NULL, '2025-12-06 13:32:39', '2025-12-09 00:04:42'),
(44, 103728080009, 17, '2025-2026', 90.00, 87.00, 88.50, 88.50, NULL, NULL, NULL, NULL, NULL, '2025-12-09 00:05:15', '2025-12-09 00:05:15');

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
(103, 35, 14),
(104, 35, 15),
(105, 35, 17),
(106, 34, 14),
(107, 34, 17);

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
  `section_name` varchar(50) DEFAULT NULL,
  `category` enum('Core Subjects','Applied and Specialized Subjects') NOT NULL DEFAULT 'Core Subjects',
  `semester` enum('1st Semester','2nd Semester') NOT NULL DEFAULT '1st Semester'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subjects`
--

INSERT INTO `subjects` (`subject_id`, `subject_code`, `subject_name`, `teacher_id`, `year_level`, `section_name`, `category`, `semester`) VALUES
(14, '111', 'English', 'epira741', NULL, NULL, 'Applied and Specialized Subjects', '1st Semester'),
(15, '123', 'Filipino', 'nvidal627', NULL, NULL, 'Core Subjects', '1st Semester'),
(16, '', 'English', 'mmedrano485', NULL, NULL, 'Core Subjects', '2nd Semester'),
(17, '', 'Math', 'epira741', NULL, NULL, 'Applied and Specialized Subjects', '2nd Semester');

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
(28, '20243110546', '$2y$10$nfVZBJzMdZTCtpAtoDHLw.3iUnVzTyk9a5sfskzbBPYEG9/du.bfC', 'student'),
(29, 'nvidal627', '$2y$10$UCRDVjwouMb0sMj9TNBbEeKUulKa9kgcJ2nVgwhlZ7DZcHH0nDqL6', 'teacher'),
(31, '103728080009', '$2y$10$yMcK01u6I1Db8EfL4cTD8unsHV5HwdLIIwxT6UliljaCTaJBl1CmW', 'student'),
(32, 'mmedrano485', '$2y$10$atps5kBEVMkZV0S0DMpch.BQAu2kpdshTE/00pWwH2j9SKAUXPjsu', 'teacher'),
(33, 'epira741', '$2y$10$9FJ9bfTGTP.sVXEX6hkwReIpROkLjLDLfLbY6hHE5ewreSqOaI1W.', 'teacher'),
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
-- Indexes for table `grades`
--
ALTER TABLE `grades`
  ADD PRIMARY KEY (`grade_id`),
  ADD UNIQUE KEY `unique_grade` (`student_id`,`subject_id`,`school_year`),
  ADD UNIQUE KEY `unique_student_subject_year` (`student_id`,`subject_id`,`school_year`);

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
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=788;

--
-- AUTO_INCREMENT for table `grades`
--
ALTER TABLE `grades`
  MODIFY `grade_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=108;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `subject_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

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
