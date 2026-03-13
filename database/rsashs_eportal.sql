-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3307
-- Generation Time: Jan 25, 2026 at 12:55 PM
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
-- Database: `rsashs_eportal`
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
(1019, 0, 'admin', 'Logged in successfully', '2026-01-23 08:18:19'),
(1020, 0, 'admin', 'Visited dashboard', '2026-01-23 08:18:20'),
(1021, 0, 'admin', 'Posted Announcement', '2026-01-23 08:39:20'),
(1022, 0, 'admin', 'Posted Announcement', '2026-01-23 08:39:22'),
(1023, 0, 'admin', 'Posted Announcement', '2026-01-23 08:39:22'),
(1024, 0, 'admin', 'Visited dashboard', '2026-01-23 08:39:25'),
(1025, 0, 'admin', 'Visited activity logs page', '2026-01-23 08:39:29'),
(1026, 0, 'admin', 'Visited dashboard', '2026-01-23 08:39:34'),
(1027, 0, 'admin', 'Posted Announcement', '2026-01-23 08:39:36'),
(1028, 0, 'admin', 'Posted Announcement', '2026-01-23 08:39:51'),
(1029, 0, 'admin', 'Posted Announcement', '2026-01-23 08:39:51'),
(1030, 0, 'admin', 'Posted Announcement', '2026-01-23 08:39:55'),
(1031, 0, 'admin', 'Visited dashboard', '2026-01-23 08:39:56'),
(1032, 0, 'admin', 'Visited dashboard', '2026-01-23 08:40:00'),
(1033, 0, 'admin', 'Posted Announcement', '2026-01-23 08:40:07'),
(1034, 0, 'admin', 'Visited dashboard', '2026-01-23 08:40:08'),
(1035, 0, 'admin', 'Posted Announcement', '2026-01-23 08:40:09'),
(1036, 0, 'admin', 'Posted Announcement', '2026-01-23 08:40:23'),
(1037, 0, 'admin', 'Visited dashboard', '2026-01-23 08:40:24'),
(1038, 0, 'admin', 'Visited Manage Sections page', '2026-01-23 08:40:28'),
(1039, 0, 'admin', 'Visited dashboard', '2026-01-23 08:40:29'),
(1040, 0, 'admin', 'Visited dashboard', '2026-01-23 08:42:19'),
(1041, 0, 'admin', 'Posted Announcement: School Sports Fest 2026', '2026-01-23 08:42:35'),
(1042, 0, 'admin', 'Posted Announcement: School Sports Fest 2026', '2026-01-23 08:42:39'),
(1043, 0, 'admin', 'Visited dashboard', '2026-01-23 08:42:40'),
(1044, 0, 'admin', 'Posted Announcement: School Clinic Hours Extended', '2026-01-23 08:45:07'),
(1045, 0, 'admin', 'Visited dashboard', '2026-01-23 08:45:17'),
(1046, 0, 'admin', 'Visited dashboard', '2026-01-23 08:45:58'),
(1047, 0, 'admin', 'Logged out', '2026-01-23 08:47:13'),
(1048, 0, 'admin', 'Logged in successfully', '2026-01-23 08:47:21'),
(1049, 0, 'admin', 'Visited dashboard', '2026-01-23 08:47:21'),
(1050, 0, 'teacher', 'Logged in successfully', '2026-01-23 08:47:44'),
(1051, 2147483647, 'student', 'New student account created via signup.', '2026-01-23 08:49:09'),
(1052, 0, 'admin', 'Visited Manage Subjects page', '2026-01-23 08:49:20'),
(1053, 0, 'admin', 'Visited Manage Sections page', '2026-01-23 08:49:21'),
(1054, 0, 'admin', 'Visited Manage Subjects page', '2026-01-23 08:49:22'),
(1055, 0, 'admin', 'Visited Manage Subjects page', '2026-01-23 08:49:28'),
(1056, 0, 'admin', 'Added subject: English (Teacher ID: mmedrano905, Category: Core Subjects, Semester: 1st Semester)', '2026-01-23 08:49:28'),
(1057, 0, 'admin', 'Visited Manage Sections page', '2026-01-23 08:49:30'),
(1058, 0, 'admin', 'Visited Manage Sections page', '2026-01-23 08:49:37'),
(1059, 0, 'admin', 'Added section: HAHA (Grade 11 - STEM, 2025-2026)', '2026-01-23 08:49:37'),
(1060, 0, 'admin', 'Visited Manage Sections page', '2026-01-23 08:49:38'),
(1061, 0, 'admin', 'Assign mode: Viewed student list for section \'HAHA\' (Year Level: Grade 11) - 1 student(s) retrieved', '2026-01-23 08:49:39'),
(1062, 0, 'admin', 'Visited Manage Sections page', '2026-01-23 08:49:41'),
(1063, 0, 'admin', 'Assigned/Reassigned students and subjects for section: HAHA (Grade 11)', '2026-01-23 08:49:41'),
(1064, 0, 'admin', 'Visited Manage Sections page', '2026-01-23 08:49:42'),
(1065, 0, 'admin', 'Logged in successfully', '2026-01-23 09:02:23'),
(1066, 0, 'admin', 'Visited dashboard', '2026-01-23 09:02:23'),
(1067, 0, '', 'Forced logout from another session', '2026-01-23 09:02:37'),
(1068, 0, 'teacher', 'Logged in successfully', '2026-01-23 09:02:42'),
(1069, 0, 'admin', 'Visited Manage Subjects page', '2026-01-23 09:04:49'),
(1070, 0, 'admin', 'Visited Manage Sections page', '2026-01-23 09:04:51'),
(1071, 2147483647, 'student', 'Logged in successfully', '2026-01-23 09:09:06'),
(1072, 2147483647, 'student', 'Viewed report card', '2026-01-23 09:09:09'),
(1073, 2147483647, 'student', 'Viewed report card', '2026-01-23 09:09:11'),
(1074, 2147483647, 'student', 'Viewed report card', '2026-01-23 09:09:27'),
(1075, 0, '', 'Forced logout from another session', '2026-01-23 09:12:05'),
(1076, 0, 'teacher', 'Logged in successfully', '2026-01-23 09:12:12'),
(1077, 0, 'admin', 'Logged in successfully', '2026-01-23 09:15:24'),
(1078, 0, 'admin', 'Visited dashboard', '2026-01-23 09:15:24'),
(1079, 0, 'admin', 'Visited Manage Sections page', '2026-01-23 09:15:27'),
(1080, 0, 'admin', 'Visited Manage Subjects page', '2026-01-23 09:15:27'),
(1081, 0, 'admin', 'Visited Manage Sections page', '2026-01-23 09:18:25'),
(1082, 0, 'teacher', 'Logged in successfully', '2026-01-23 09:19:25'),
(1083, 0, 'admin', 'Logged in successfully', '2026-01-23 09:27:24'),
(1084, 0, 'admin', 'Visited dashboard', '2026-01-23 09:27:25'),
(1085, 0, 'admin', 'Visited Manage Sections page', '2026-01-23 09:27:26'),
(1086, 0, 'admin', 'Reassign mode: Viewed student list for section \'HAHA\' (Year Level: Grade 11) - 1 student(s) retrieved', '2026-01-23 09:27:34'),
(1087, 0, 'admin', 'Viewed subjects for section \'HAHA\' (Year Level: Grade 11) - 1 subject(s) retrieved', '2026-01-23 09:27:34'),
(1088, 0, 'admin', 'Visited Manage Sections page', '2026-01-23 09:27:39'),
(1089, 0, 'admin', 'Assigned/Reassigned students and subjects for section: HAHA (Grade 11)', '2026-01-23 09:27:39'),
(1090, 0, 'admin', 'Logged in successfully', '2026-01-23 09:27:47'),
(1091, 0, 'admin', 'Visited dashboard', '2026-01-23 09:27:47'),
(1092, 0, 'admin', 'Visited Manage Sections page', '2026-01-23 09:27:48'),
(1093, 0, 'admin', 'Reassign mode: Viewed student list for section \'HAHA\' (Year Level: Grade 11) - 1 student(s) retrieved', '2026-01-23 09:27:49'),
(1094, 0, 'admin', 'Viewed subjects for section \'HAHA\' (Year Level: Grade 11) - 1 subject(s) retrieved', '2026-01-23 09:27:49'),
(1095, 0, 'admin', 'Visited Manage Sections page', '2026-01-23 09:27:52'),
(1096, 0, 'admin', 'Assigned/Reassigned students and subjects for section: HAHA (Grade 11)', '2026-01-23 09:27:52'),
(1097, 0, 'admin', 'Visited Manage Sections page', '2026-01-23 09:27:53'),
(1098, 0, 'admin', 'Visited Manage Subjects page', '2026-01-23 09:29:48'),
(1099, 0, 'admin', 'Visited Manage Sections page', '2026-01-23 09:29:50'),
(1100, 0, 'admin', 'Reassign mode: Viewed student list for section \'HAHA\' (Year Level: Grade 11) - 1 student(s) retrieved', '2026-01-23 09:29:51'),
(1101, 0, 'admin', 'Viewed subjects for section \'HAHA\' (Year Level: Grade 11) - 1 subject(s) retrieved', '2026-01-23 09:29:51'),
(1102, 0, 'admin', 'Visited Manage Sections page', '2026-01-23 09:29:54'),
(1103, 0, 'admin', 'Assigned/Reassigned students, subjects, and adviser for section: HAHA (Grade 11)', '2026-01-23 09:29:54'),
(1104, 0, 'admin', 'Visited Manage Sections page', '2026-01-23 09:29:55'),
(1105, 0, 'admin', 'Visited Manage Sections page', '2026-01-23 09:30:01'),
(1106, 0, 'admin', 'Deleted section: HAHA (Grade 11)', '2026-01-23 09:30:01'),
(1107, 0, 'admin', 'Visited Manage Sections page', '2026-01-23 09:30:02'),
(1108, 0, 'admin', 'Visited Manage Sections page', '2026-01-23 09:30:07'),
(1109, 0, 'admin', 'Added section: HAHA (Grade 11 - ABM, 2025-2026)', '2026-01-23 09:30:07'),
(1110, 0, 'admin', 'Visited Manage Sections page', '2026-01-23 09:30:08'),
(1111, 0, 'admin', 'Assign mode: Viewed student list for section \'HAHA\' (Year Level: Grade 11) - 1 student(s) retrieved', '2026-01-23 09:30:10'),
(1112, 0, 'admin', 'Visited Manage Sections page', '2026-01-23 09:30:14'),
(1113, 0, 'admin', 'Assigned/Reassigned students, subjects, and adviser for section: HAHA (Grade 11)', '2026-01-23 09:30:14'),
(1114, 0, 'admin', 'Visited Manage Sections page', '2026-01-23 09:30:15'),
(1115, 0, 'admin', 'Visited Manage Sections page', '2026-01-23 09:30:36'),
(1116, 0, 'admin', 'Deleted section: HAHA (Grade 11)', '2026-01-23 09:30:37'),
(1117, 0, 'admin', 'Visited Manage Sections page', '2026-01-23 09:30:38'),
(1118, 0, 'admin', 'Logged in successfully', '2026-01-23 09:36:20'),
(1119, 0, 'admin', 'Visited dashboard', '2026-01-23 09:36:20'),
(1120, 0, 'admin', 'Visited Section Management Page', '2026-01-23 09:36:21'),
(1121, 0, 'admin', 'Visited Section Management Page', '2026-01-23 09:36:35'),
(1122, 0, 'admin', 'Visited Section Management Page', '2026-01-23 09:36:37'),
(1123, 0, 'admin', 'Visited Manage Sections page', '2026-01-23 09:36:42'),
(1124, 0, '', 'Forced logout from another session', '2026-01-24 12:35:20'),
(1125, 0, 'teacher', 'Logged in successfully', '2026-01-24 12:35:29'),
(1126, 0, '', 'Forced logout from another session', '2026-01-24 12:35:56'),
(1127, 0, 'admin', 'Logged in successfully', '2026-01-24 12:36:01'),
(1128, 0, 'admin', 'Visited dashboard', '2026-01-24 12:36:01'),
(1129, 0, 'admin', 'Visited Manage Subjects page', '2026-01-24 12:36:15'),
(1130, 0, 'admin', 'Visited Manage Subjects page', '2026-01-24 12:36:27'),
(1131, 0, 'admin', 'Added subject: English (Teacher ID: mmedrano905, Category: Core Subjects, Semester: 1st Semester)', '2026-01-24 12:36:27'),
(1132, 0, 'admin', 'Visited Manage Subjects page', '2026-01-24 12:36:32'),
(1133, 0, 'admin', 'Added subject: EPP (Teacher ID: mmedrano905, Category: Core Subjects, Semester: 2nd Semester)', '2026-01-24 12:36:32'),
(1134, 0, 'admin', 'Visited Manage Subjects page', '2026-01-24 12:36:37'),
(1135, 0, 'admin', 'Added subject: Math (Teacher ID: mmedrano905, Category: Core Subjects, Semester: 2nd Semester)', '2026-01-24 12:36:37'),
(1136, 0, 'admin', 'Visited Manage Subjects page', '2026-01-24 12:36:43'),
(1137, 0, 'admin', 'Added subject: Filipino (Teacher ID: mmedrano905, Category: Core Subjects, Semester: 1st Semester)', '2026-01-24 12:36:43'),
(1138, 0, 'admin', 'Visited Manage Subjects page', '2026-01-24 12:36:48'),
(1139, 0, 'admin', 'Added subject: English (Teacher ID: mmedrano905, Category: Applied and Specialized Subjects, Semester: 1st Semester)', '2026-01-24 12:36:48'),
(1140, 0, 'admin', 'Visited Manage Subjects page', '2026-01-24 12:36:54'),
(1141, 0, 'admin', 'Added subject: EPP (Teacher ID: mmedrano905, Category: Applied and Specialized Subjects, Semester: 1st Semester)', '2026-01-24 12:36:54'),
(1142, 0, 'admin', 'Visited Manage Subjects page', '2026-01-24 12:36:59'),
(1143, 0, 'admin', 'Added subject: Math (Teacher ID: mmedrano905, Category: Applied and Specialized Subjects, Semester: 1st Semester)', '2026-01-24 12:36:59'),
(1144, 0, 'admin', 'Visited Manage Subjects page', '2026-01-24 12:37:04'),
(1145, 0, 'admin', 'Added subject: EPP (Teacher ID: mmedrano905, Category: Core Subjects, Semester: 1st Semester)', '2026-01-24 12:37:04'),
(1146, 0, 'admin', 'Visited Manage Sections page', '2026-01-24 12:37:08'),
(1147, 0, 'admin', 'Visited Manage Subjects page', '2026-01-24 12:37:09'),
(1148, 0, 'admin', 'Visited Manage Sections page', '2026-01-24 12:37:10'),
(1149, 0, 'admin', 'Visited Manage Subjects page', '2026-01-24 12:37:11'),
(1150, 0, 'admin', 'Visited Manage Subjects page', '2026-01-24 12:37:16'),
(1151, 0, 'admin', 'Added subject: Filipino (Teacher ID: mmedrano905, Category: Applied and Specialized Subjects, Semester: 2nd Semester)', '2026-01-24 12:37:16'),
(1152, 0, 'admin', 'Visited Manage Subjects page', '2026-01-24 12:37:24'),
(1153, 0, 'admin', 'Added subject: Math (Teacher ID: mmedrano905, Category: Core Subjects, Semester: 2nd Semester)', '2026-01-24 12:37:24'),
(1154, 0, 'admin', 'Visited Manage Subjects page', '2026-01-24 12:37:30'),
(1155, 0, 'admin', 'Added subject: EPP (Teacher ID: mmedrano905, Category: Core Subjects, Semester: 2nd Semester)', '2026-01-24 12:37:30'),
(1156, 0, 'admin', 'Visited Manage Subjects page', '2026-01-24 12:37:43'),
(1157, 0, 'admin', 'Added subject: Math (Teacher ID: mmedrano905, Category: Core Subjects, Semester: 1st Semester)', '2026-01-24 12:37:43'),
(1158, 0, 'admin', 'Visited Manage Sections page', '2026-01-24 12:38:02'),
(1159, 0, 'admin', 'Visited dashboard', '2026-01-24 12:38:13'),
(1160, 0, 'admin', 'Visited Manage Subjects page', '2026-01-24 12:38:14'),
(1161, 0, 'admin', 'Visited Manage Sections page', '2026-01-24 12:38:14'),
(1162, 0, 'admin', 'Visited Manage Sections page', '2026-01-24 12:38:22'),
(1163, 0, 'admin', 'Added section: DANG (Grade 11 - STEM, 2025-2026)', '2026-01-24 12:38:22'),
(1164, 0, 'admin', 'Visited Manage Sections page', '2026-01-24 12:38:23'),
(1165, 0, 'admin', 'Assign mode: Viewed student list for section \'DANG\' (Year Level: Grade 11) - 1 student(s) retrieved', '2026-01-24 12:38:24'),
(1166, 0, 'admin', 'Visited Manage Sections page', '2026-01-24 12:38:36'),
(1167, 0, 'admin', 'Assigned/Reassigned students and subjects for section: DANG (Grade 11)', '2026-01-24 12:38:36'),
(1168, 0, 'admin', 'Visited Manage Sections page', '2026-01-24 12:38:37'),
(1169, 0, 'admin', 'Visited Manage Subjects page', '2026-01-24 12:38:41'),
(1170, 0, 'admin', 'Visited Manage Sections page', '2026-01-24 12:38:41'),
(1171, 0, 'admin', 'Visited Teachers page', '2026-01-24 12:38:42'),
(1172, 0, 'admin', 'Viewed subjects of teacher ID: mmedrano905', '2026-01-24 12:38:43'),
(1173, 0, 'admin', 'Fetched 1 subject(s) for teacher \'MARIANNE MEDRANO\' (ID: mmedrano905)', '2026-01-24 12:38:43'),
(1174, 0, 'admin', 'Viewed students and grades of section ID: 40 (DANG) for subject ID: 22 - 1 student(s) retrieved', '2026-01-24 12:38:45'),
(1175, 0, 'admin', 'Visited dashboard', '2026-01-24 12:38:52'),
(1176, 0, 'admin', 'Visited Manage Subjects page', '2026-01-24 12:38:58'),
(1177, 0, 'admin', 'Visited Manage Sections page', '2026-01-24 12:39:05'),
(1178, 0, 'admin', 'Reassign mode: Viewed student list for section \'DANG\' (Year Level: Grade 11) - 1 student(s) retrieved', '2026-01-24 12:39:07'),
(1179, 0, 'admin', 'Viewed subjects for section \'DANG\' (Year Level: Grade 11) - 1 subject(s) retrieved', '2026-01-24 12:39:07'),
(1180, 0, 'admin', 'Visited dashboard', '2026-01-24 12:39:16'),
(1181, 0, 'admin', 'Visited Manage Subjects page', '2026-01-24 12:39:22'),
(1182, 0, 'admin', 'Visited Manage Sections page', '2026-01-24 12:42:14'),
(1183, 0, 'admin', 'Logged in successfully', '2026-01-24 12:48:16'),
(1184, 0, 'admin', 'Visited dashboard', '2026-01-24 12:48:17'),
(1185, 0, 'admin', 'Visited Manage Sections page', '2026-01-24 12:48:19'),
(1186, 0, 'admin', 'Visited Manage Sections page', '2026-01-24 12:48:36'),
(1187, 0, 'admin', 'Added section: HAHA (Grade 11 - STEM, 2025-2026) with adviser ID 0', '2026-01-24 12:48:36'),
(1188, 0, 'admin', 'Visited Manage Sections page', '2026-01-24 12:48:37'),
(1189, 0, 'admin', 'Assign mode: Viewed student list for section \'HAHA\' (Year Level: Grade 11) - 0 student(s) retrieved', '2026-01-24 12:48:42'),
(1190, 0, 'admin', 'Visited Manage Sections page', '2026-01-24 12:50:45'),
(1191, 0, 'admin', 'Added section: Ney (Grade 11 - ABM, 2025-2026) with adviser ID 0', '2026-01-24 12:50:45'),
(1192, 0, 'admin', 'Visited Manage Sections page', '2026-01-24 12:50:46'),
(1193, 0, 'admin', 'Logged in successfully', '2026-01-24 13:04:19'),
(1194, 0, 'admin', 'Visited dashboard', '2026-01-24 13:04:19'),
(1195, 0, 'admin', 'Visited Manage Sections page', '2026-01-24 13:04:24'),
(1196, 0, 'admin', 'Visited Manage Sections page', '2026-01-24 13:04:32'),
(1197, 0, 'admin', 'Added section: nyenye (Grade 11 - STEM, 2025-2026) with adviser ID mmedrano905', '2026-01-24 13:04:32'),
(1198, 0, 'admin', 'Visited Manage Sections page', '2026-01-24 13:04:33'),
(1199, 0, 'admin', 'Visited Manage Sections page', '2026-01-24 13:04:42'),
(1200, 0, 'admin', 'Deleted section: DANG (Grade 11)', '2026-01-24 13:04:42'),
(1201, 0, 'admin', 'Visited Manage Sections page', '2026-01-24 13:04:43'),
(1202, 0, 'admin', 'Visited Manage Sections page', '2026-01-24 13:04:44'),
(1203, 0, 'admin', 'Deleted section: HAHA (Grade 11)', '2026-01-24 13:04:44'),
(1204, 0, 'admin', 'Visited Manage Sections page', '2026-01-24 13:04:47'),
(1205, 0, 'admin', 'Visited Manage Sections page', '2026-01-24 13:04:48'),
(1206, 0, 'admin', 'Deleted section: Ney (Grade 11)', '2026-01-24 13:04:48'),
(1207, 0, 'admin', 'Visited Manage Sections page', '2026-01-24 13:04:49'),
(1208, 0, 'admin', 'Visited Manage Sections page', '2026-01-24 13:04:51'),
(1209, 0, 'admin', 'Deleted section: nyenye (Grade 11)', '2026-01-24 13:04:51'),
(1210, 0, 'admin', 'Visited Manage Sections page', '2026-01-24 13:04:52'),
(1211, 0, 'admin', 'Visited Manage Sections page', '2026-01-24 13:05:35'),
(1212, 0, 'admin', 'Visited Manage Sections page', '2026-01-24 13:05:42'),
(1213, 0, 'admin', 'Added section: HAHA (Grade 11 - STEM, 2025-2026) with adviser ID mmedrano905', '2026-01-24 13:05:42'),
(1214, 0, 'admin', 'Visited Manage Sections page', '2026-01-24 13:05:43'),
(1215, 0, 'admin', 'Visited Manage Subjects page', '2026-01-24 13:06:00'),
(1216, 0, 'admin', 'Visited Manage Sections page', '2026-01-24 13:06:04'),
(1217, 0, 'admin', 'Visited Teachers page', '2026-01-24 13:06:05'),
(1218, 0, 'admin', 'Visited Manage Sections page', '2026-01-24 13:06:06'),
(1219, 0, 'admin', 'Visited Manage Sections page', '2026-01-24 13:07:19'),
(1220, 0, 'admin', 'Assign mode: Viewed student list for section \'HAHA\' (Year Level: Grade 11) - 1 student(s) retrieved', '2026-01-24 13:07:24'),
(1221, 0, 'admin', 'Assign mode: Viewed student list for section \'HAHA\' (Year Level: Grade 11) - 1 student(s) retrieved', '2026-01-24 13:07:40'),
(1222, 0, 'admin', 'Assign mode: Viewed student list for section \'HAHA\' (Year Level: Grade 11) - 1 student(s) retrieved', '2026-01-24 13:07:43'),
(1223, 0, 'admin', 'Visited Manage Sections page', '2026-01-24 13:07:46'),
(1224, 0, 'admin', 'Assigned/Reassigned students and subjects for section: HAHA (Grade 11)', '2026-01-24 13:07:46'),
(1225, 0, 'admin', 'Visited Manage Sections page', '2026-01-24 13:07:47'),
(1226, 0, '', 'Forced logout from another session', '2026-01-24 13:08:23'),
(1227, 0, 'teacher', 'Logged in successfully', '2026-01-24 13:08:30'),
(1228, 2147483647, '', 'Forced logout from another session', '2026-01-24 13:09:03'),
(1229, 2147483647, 'student', 'Logged in successfully', '2026-01-24 13:09:07'),
(1230, 2147483647, 'student', 'Viewed profile', '2026-01-24 13:09:15'),
(1231, 2147483647, 'student', 'Viewed report card', '2026-01-24 13:09:16'),
(1232, 0, '', 'Forced logout from another session', '2026-01-24 13:16:14'),
(1233, 0, 'teacher', 'Logged in successfully', '2026-01-24 13:16:19'),
(1234, 0, 'admin', 'Logged in successfully', '2026-01-24 13:54:59'),
(1235, 0, 'admin', 'Visited dashboard', '2026-01-24 13:54:59'),
(1236, 0, 'admin', 'Visited Manage Subjects page', '2026-01-24 13:55:03'),
(1237, 0, 'admin', 'Visited Manage Sections page', '2026-01-24 13:55:04'),
(1238, 0, 'admin', 'Reassign mode: Viewed student list for section \'HAHA\' (Year Level: Grade 11) - 1 student(s) retrieved', '2026-01-24 13:55:06'),
(1239, 0, 'admin', 'Viewed subjects for section \'HAHA\' (Year Level: Grade 11) - 1 subject(s) retrieved', '2026-01-24 13:55:06'),
(1240, 0, 'admin', 'Visited Manage Sections page', '2026-01-24 13:55:13'),
(1241, 0, 'admin', 'Assigned/Reassigned students and subjects for section: HAHA (Grade 11)', '2026-01-24 13:55:13'),
(1242, 0, 'admin', 'Visited Manage Sections page', '2026-01-24 13:55:14'),
(1243, 0, 'admin', 'Visited Manage Subjects page', '2026-01-24 13:56:05'),
(1244, 0, 'admin', 'Visited Manage Subjects page', '2026-01-24 13:56:10'),
(1245, 0, 'admin', 'Edited subject ID: 27 to English1 (Teacher ID: mmedrano905, Category: Applied and Specialized Subjects, Semester: 1st Semester)', '2026-01-24 13:56:10'),
(1246, 0, 'admin', 'Visited Manage Subjects page', '2026-01-24 13:56:16'),
(1247, 0, 'admin', 'Visited Manage Sections page', '2026-01-24 13:56:17'),
(1248, 0, 'admin', 'Reassign mode: Viewed student list for section \'HAHA\' (Year Level: Grade 11) - 1 student(s) retrieved', '2026-01-24 13:56:18'),
(1249, 0, 'admin', 'Viewed subjects for section \'HAHA\' (Year Level: Grade 11) - 4 subject(s) retrieved', '2026-01-24 13:56:18'),
(1250, 0, 'admin', 'Visited Manage Sections page', '2026-01-24 13:56:20'),
(1251, 0, 'admin', 'Assigned/Reassigned students and subjects for section: HAHA (Grade 11)', '2026-01-24 13:56:20'),
(1252, 0, 'admin', 'Visited Manage Sections page', '2026-01-24 13:56:21'),
(1253, 0, '', 'Forced logout from another session', '2026-01-24 14:05:56'),
(1254, 0, 'teacher', 'Logged in successfully', '2026-01-24 14:06:05'),
(1255, 2147483647, 'student', 'New student account created via signup.', '2026-01-24 14:09:01'),
(1256, 2147483647, '', 'Failed login attempt: incorrect password', '2026-01-24 14:09:09'),
(1257, 2147483647, '', 'Failed login attempt: incorrect password', '2026-01-24 14:09:17'),
(1258, 0, 'admin', 'Logged in successfully', '2026-01-24 14:09:32'),
(1259, 0, 'admin', 'Visited dashboard', '2026-01-24 14:09:32'),
(1260, 0, 'admin', 'Viewed student account: 103728080011', '2026-01-24 14:09:50'),
(1261, 2147483647, 'student', 'Logged in successfully', '2026-01-24 14:09:58'),
(1262, 2147483647, 'student', 'Viewed profile', '2026-01-24 14:10:03'),
(1263, 0, 'admin', 'Visited Manage Subjects page', '2026-01-24 14:10:16'),
(1264, 0, 'admin', 'Visited Manage Sections page', '2026-01-24 14:10:17'),
(1265, 0, 'admin', 'Reassign mode: Viewed student list for section \'HAHA\' (Year Level: Grade 11) - 2 student(s) retrieved', '2026-01-24 14:10:19'),
(1266, 0, 'admin', 'Viewed subjects for section \'HAHA\' (Year Level: Grade 11) - 5 subject(s) retrieved', '2026-01-24 14:10:19'),
(1267, 0, 'admin', 'Visited Manage Sections page', '2026-01-24 14:10:21'),
(1268, 0, 'admin', 'Assigned/Reassigned students and subjects for section: HAHA (Grade 11)', '2026-01-24 14:10:21'),
(1269, 0, 'admin', 'Visited Manage Sections page', '2026-01-24 14:10:22'),
(1270, 2147483647, 'student', 'Viewed report card', '2026-01-24 14:10:29'),
(1271, 2147483647, 'student', 'Viewed report card', '2026-01-24 14:10:30'),
(1272, 0, 'student', 'Logged out', '2026-01-24 14:10:32'),
(1273, 2147483647, '', 'Forced logout from another session', '2026-01-24 14:10:40'),
(1274, 2147483647, '', 'Failed login attempt: incorrect password', '2026-01-24 14:10:46'),
(1275, 2147483647, '', 'Failed login attempt: incorrect password', '2026-01-24 14:10:51'),
(1276, 2147483647, 'student', 'Logged in successfully', '2026-01-24 14:10:58'),
(1277, 2147483647, 'student', 'Viewed report card', '2026-01-24 14:11:00'),
(1278, 2147483647, 'student', 'Logged out', '2026-01-24 14:11:11'),
(1279, 0, '', 'Forced logout from another session', '2026-01-24 14:11:19'),
(1280, 0, 'teacher', 'Logged in successfully', '2026-01-24 14:11:26'),
(1281, 0, 'teacher', 'Logged out', '2026-01-24 14:14:03'),
(1282, 0, 'teacher', 'Logged in successfully', '2026-01-24 14:14:10'),
(1283, 0, '', 'Forced logout from another session', '2026-01-24 14:14:58'),
(1284, 0, 'teacher', 'Logged in successfully', '2026-01-24 14:15:04'),
(1285, 0, 'teacher', 'Logged out', '2026-01-24 14:20:24'),
(1286, 2147483647, '', 'Forced logout from another session', '2026-01-24 14:20:30'),
(1287, 2147483647, '', 'Failed login attempt: incorrect password', '2026-01-24 14:20:34'),
(1288, 2147483647, '', 'Failed login attempt: incorrect password (Attempt 1/5)', '2026-01-24 14:31:00'),
(1289, 2147483647, '', 'Failed login attempt: incorrect password (Attempt 2/5)', '2026-01-24 14:31:04'),
(1290, 2147483647, '', 'Failed login attempt: incorrect password (Attempt 3/5)', '2026-01-24 14:31:07'),
(1291, 2147483647, '', 'Failed login attempt: incorrect password (Attempt 4/5)', '2026-01-24 14:31:10'),
(1292, 2147483647, '', 'Account locked due to multiple failed login attempts', '2026-01-24 14:31:11');

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `status` enum('draft','published') NOT NULL DEFAULT 'draft',
  `image` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`id`, `title`, `message`, `status`, `image`, `created_at`) VALUES
(1, 'sample', 'sample', 'published', 'announcement_69732ed977df04.73234281.png', '2026-01-23 16:18:33'),
(2, 'Memorandum', 'Memo', 'published', 'announcement_69732fd6d0f155.88874875.png', '2026-01-23 16:22:46'),
(3, 'Enrollment Period for Senior High School 2026', 'The enrollment for Senior High School for the school year 2026-2027 is now open. Students are encouraged to submit their requirements online or visit the school registrar’s office. Early enrollment is highly recommended to secure slots in preferred tracks.', 'published', 'announcement_697331f82fad85.37101315.png', '2026-01-23 16:31:52'),
(4, 'National Science Quiz Winners', 'Congratulations to our Grade 11 and Grade 12 students who participated in the National Science Quiz. Our school brought home 3 medals, showcasing the talent and dedication of our learners and teachers. Special recognition to Mr. Cruz and Ms. Santos for mentoring the winning students.', 'published', 'announcement_697332144683e8.02673834.png', '2026-01-23 16:32:20'),
(5, 'Fire Drill Schedule', 'A mandatory fire drill is scheduled on February 5, 2026, at 9:00 AM. All students and staff are required to participate. Safety officers will guide students through evacuation routes and procedures. Please follow instructions carefully for a safe and smooth drill.', 'published', 'announcement_69733229a364a0.08219725.png', '2026-01-23 16:32:41'),
(6, 'School Library Renovation Completed', 'The school library has undergone a major renovation, providing more study spaces, new books, and improved digital resources. Students are encouraged to explore the upgraded library starting January 28, 2026. The opening ceremony will include a brief orientation for students on how to access online materials.', 'published', 'announcement_6973325129e123.05553521.png', '2026-01-23 16:33:21'),
(7, 'Parent-Teacher Conference', 'The annual Parent-Teacher Conference will be held on February 10–12, 2026. Parents are requested to schedule meetings with their child’s adviser to discuss academic performance and address concerns. Online appointment booking is available through the school portal.', 'published', 'announcement_69733270023125.01439593.png', '2026-01-23 16:33:52'),
(8, 'Anti-Bullying Campaign Launch', 'The school is launching an Anti-Bullying Campaign this February. Activities will include seminars, workshops, and poster-making contests to raise awareness among students. Everyone is encouraged to participate and promote a safe, respectful, and inclusive school environment.', 'published', NULL, '2026-01-23 16:36:49'),
(9, 'Scholarship Program Announcement', 'The Department of Education is offering new scholarship programs for deserving Grade 11 and 12 students. Interested applicants must submit their requirements to the guidance office by February 20, 2026. Shortlisted candidates will be notified via email and school announcements.', 'published', NULL, '2026-01-23 16:39:22'),
(10, 'Cultural Day Celebration', 'Cultural Day will be celebrated on February 15, 2026. Students are invited to showcase traditional Filipino costumes, dances, and performances. The event aims to promote awareness and appreciation of Philippine culture and heritage.', 'published', NULL, '2026-01-23 16:39:51'),
(11, 'School Sports Fest 2026', 'Get ready for the Annual School Sports Fest starting March 1, 2026. Competitions will include basketball, volleyball, and track and field events. Students are encouraged to join or support their classmates. Coaches will provide schedules and team assignments in advisory classes.', 'published', NULL, '2026-01-23 16:42:35'),
(12, 'School Clinic Hours Extended', 'The school clinic will now be open from 7:30 AM to 5:00 PM to better serve students and staff. Medical assistance and consultation services are available during these hours. Students are encouraged to report any health concerns promptly.', 'published', NULL, '2026-01-23 16:45:07');

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
  `overall_final` decimal(5,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `grades`
--

INSERT INTO `grades` (`grade_id`, `student_id`, `subject_id`, `school_year`, `q1`, `q2`, `first_sem_final`, `gwa_first_sem`, `q3`, `q4`, `second_sem_final`, `gwa_second_sem`, `final`, `overall_final`, `created_at`, `updated_at`) VALUES
(49, 103728080009, 22, '2025-2026', 87.00, 98.00, 92.50, 92.50, 79.00, 89.00, 84.00, 84.00, 88.25, NULL, '2026-01-23 09:09:24', '2026-01-24 12:41:53');

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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `adviser_id` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sections`
--

INSERT INTO `sections` (`section_id`, `year_level`, `strand`, `section_name`, `school_year`, `created_at`, `adviser_id`) VALUES
(44, 'Grade 11', 'STEM', 'HAHA', '2025-2026', '2026-01-24 13:05:42', 'mmedrano905');

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
(141, 44, 22),
(142, 44, 27),
(143, 44, 24),
(144, 44, 26),
(145, 44, 25);

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
(21, '103728080009', 'Marianne Louise', 'Valencia', 'Medrano', '', 'Female', '2003-10-24', '09067848215', 'medranomariannelouise83@gmail.com', 'Purok Malacañang East, Villanueva, San Manuel, Isabela', 'Sandiat National High School', 'Purok Malacañang East, Villanueva, San Manuel, Isabela', '2019', 'With Honors', 'Jun C. Medrano', 'Farmer', 'Elisa V. Medrano', 'Housewife', '', '', 'student_69733605691560.13267219.jpg', 'Grade 11', 'HAHA', '2025-2026', NULL, NULL, NULL),
(22, '103728080011', 'Karl', 'Valencia', 'Medrano', '', 'Male', '2005-11-02', '09067848215', 'medranokarl12345@gmail.com', 'Purok Malacañang East, Villanueva, San Manuel, Isabela', 'Sandiat National High School', 'Purok Malacañang East, Villanueva, San Manuel, Isabela', '2016', 'With Honors', 'Jun C. Medrano', 'Farmer', 'Elisa V. Medrano', 'Housewife', '', '', 'student_6974d27d6aa263.10812384.jpg', 'Grade 11', 'HAHA', '2025-2026', NULL, NULL, NULL);

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
(22, '', 'English', 'mmedrano905', NULL, NULL, 'Core Subjects', '1st Semester'),
(23, '', 'English', 'mmedrano905', NULL, NULL, 'Core Subjects', '1st Semester'),
(24, '', 'EPP', 'mmedrano905', NULL, NULL, 'Core Subjects', '2nd Semester'),
(25, '', 'Math', 'mmedrano905', NULL, NULL, 'Core Subjects', '2nd Semester'),
(26, '', 'Filipino', 'mmedrano905', NULL, NULL, 'Core Subjects', '1st Semester'),
(27, '', 'English1', 'mmedrano905', NULL, NULL, 'Applied and Specialized Subjects', '1st Semester'),
(28, '', 'EPP', 'mmedrano905', NULL, NULL, 'Applied and Specialized Subjects', '1st Semester'),
(29, '', 'Math', 'mmedrano905', NULL, NULL, 'Applied and Specialized Subjects', '1st Semester'),
(30, '', 'EPP', 'mmedrano905', NULL, NULL, 'Core Subjects', '1st Semester'),
(31, '', 'Filipino', 'mmedrano905', NULL, NULL, 'Applied and Specialized Subjects', '2nd Semester'),
(32, '', 'Math', 'mmedrano905', NULL, NULL, 'Core Subjects', '2nd Semester'),
(33, '', 'EPP', 'mmedrano905', NULL, NULL, 'Core Subjects', '2nd Semester'),
(34, '', 'Math', 'mmedrano905', NULL, NULL, 'Core Subjects', '1st Semester');

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
('mmedrano905', 'MARIANNE', 'LOUISE VALENCIA', 'MEDRANO', 'mmedrano905_1769158053.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('student','teacher','admin') DEFAULT 'student',
  `status` enum('logged_out','logged_in') NOT NULL DEFAULT 'logged_out'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `status`) VALUES
(37, 'admin', '$2y$10$9nqJUJIw3.G9JnBkPZ34suiZsD3odKh1snFjBf78JFQ/uRkXXwmQW', 'admin', 'logged_out'),
(41, 'mmedrano905', '$2y$10$rEjZ4GLnC93YwHpWEA51c.gKvupxssP2sXGhzamo6TSlDwam/7/5e', 'teacher', 'logged_out'),
(42, '103728080009', '$2y$10$r6ieli1oWql9cL.oehdCtOCmPIo2RrQSbvbL20MKtEusIxa8JHPje', 'student', 'logged_out'),
(43, '103728080011', '$2y$10$Y8cEyQAqr4y6jeEpvsyCfeNqVbV66L6Ni2iRMgO6iY3uiYlwFDeWO', 'student', 'logged_out');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`log_id`);

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`);

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
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1293;

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `grades`
--
ALTER TABLE `grades`
  MODIFY `grade_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `sections`
--
ALTER TABLE `sections`
  MODIFY `section_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT for table `section_students`
--
ALTER TABLE `section_students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `section_subjects`
--
ALTER TABLE `section_subjects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=146;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `subject_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

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
