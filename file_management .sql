-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 23, 2025 at 12:10 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+01:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `file_management`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `CheckAndExpireRequests` ()   BEGIN
    -- Update expired requests
    UPDATE `folder_requests` 
    SET 
        `is_expired` = TRUE,
        `status` = 'expired'
    WHERE 
        `status` = 'approved' 
        AND `expiry_date` < NOW() 
        AND `is_expired` = FALSE;
    
    -- Return count of newly expired requests
    SELECT ROW_COUNT() as expired_count;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Stand-in structure for view `active_approved_requests`
-- (See below for the actual view)
--
CREATE TABLE `active_approved_requests` (
`id` int(11)
,`user_email` varchar(255)
,`requested_folder_name` varchar(255)
,`reason` text
,`status` enum('pending','approved','rejected')
,`admin_email` varchar(255)
,`assigned_folder_id` int(11)
,`request_date` timestamp
,`approval_date` datetime
,`expiry_date` datetime
,`is_expired` tinyint(1)
);

-- --------------------------------------------------------

--
-- Table structure for table `admin_file_logs`
--

CREATE TABLE `admin_file_logs` (
  `log_id` int(11) NOT NULL,
  `admin_email` varchar(255) NOT NULL COMMENT 'Email of the admin who performed the action',
  `file_id` varchar(50) NOT NULL COMMENT 'ID of the file that was affected',
  `file_name` varchar(255) NOT NULL COMMENT 'Name of the file',
  `folder_id` varchar(50) NOT NULL COMMENT 'ID of the folder containing the file',
  `action` enum('delete','download') NOT NULL COMMENT 'Type of action performed',
  `timestamp` datetime NOT NULL COMMENT 'When the action was performed',
  `ip_address` varchar(45) DEFAULT NULL COMMENT 'IP address of the admin'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_file_logs`
--

INSERT INTO `admin_file_logs` (`log_id`, `admin_email`, `file_id`, `file_name`, `folder_id`, `action`, `timestamp`, `ip_address`) VALUES
(6, 'deucestod@gmail.com', '16', 'sunziel (2).pdf', '17', 'download', '2025-05-20 00:03:26', '::1');

-- --------------------------------------------------------

--
-- Table structure for table `admin_login`
--

CREATE TABLE `admin_login` (
  `id` int(11) NOT NULL,
  `name` text NOT NULL,
  `admin_user` text NOT NULL,
  `admin_password` text NOT NULL,
  `admin_status` varchar(50) NOT NULL,
  `department` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `admin_login`
--

INSERT INTO `admin_login` (`id`, `name`, `admin_user`, `admin_password`, `admin_status`, `department`) VALUES
(13, 'Blessing Appiah Kubi', 'deucestod@gmail.com', '$2y$12$k10Xp06ckJCidzpMopWsjezy8wxg9y469iGLnQrltFnXq4HMmhCR6', 'Admin', 'Finance');

-- --------------------------------------------------------

--
-- Table structure for table `admin_logs`
--

CREATE TABLE `admin_logs` (
  `id` int(11) NOT NULL,
  `admin_user` varchar(100) NOT NULL,
  `action` text NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_logs`
--

INSERT INTO `admin_logs` (`id`, `admin_user`, `action`, `created_at`) VALUES
(0, '13', 'Previewed file: NCA-FORM-AP01B A PLUS MEDIA(FM).docx.pdf from folder: FM', '2025-05-19 14:18:31'),
(0, '13', 'Previewed file: sunziel (2).pdf from folder: Adom Tv', '2025-05-19 22:02:59');

-- --------------------------------------------------------

--
-- Stand-in structure for view `expired_requests`
-- (See below for the actual view)
--
CREATE TABLE `expired_requests` (
`id` int(11)
,`user_email` varchar(255)
,`requested_folder_name` varchar(255)
,`reason` text
,`status` enum('pending','approved','rejected')
,`admin_email` varchar(255)
,`assigned_folder_id` int(11)
,`request_date` timestamp
,`approval_date` datetime
,`expiry_date` datetime
,`is_expired` tinyint(1)
);

-- --------------------------------------------------------

--
-- Table structure for table `file_access_logs`
--

CREATE TABLE `file_access_logs` (
  `id` int(11) NOT NULL,
  `file_id` int(11) NOT NULL,
  `user_email` varchar(255) NOT NULL,
  `access_type` enum('preview','download') NOT NULL,
  `access_time` datetime NOT NULL,
  `acknowledgment` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `file_access_logs`
--

INSERT INTO `file_access_logs` (`id`, `file_id`, `user_email`, `access_type`, `access_time`, `acknowledgment`) VALUES
(171, 16, 'kwame@gmail.com', 'preview', '2025-05-20 00:10:59', NULL),
(172, 16, 'kwame@gmail.com', 'preview', '2025-05-20 00:11:00', NULL),
(173, 16, 'kwame@gmail.com', 'preview', '2025-05-21 19:20:22', NULL),
(174, 16, 'kwame@gmail.com', 'preview', '2025-05-21 19:20:23', NULL),
(175, 13, 'kwame@gmail.com', 'preview', '2025-05-23 11:18:22', NULL),
(176, 13, 'kwame@gmail.com', 'preview', '2025-05-23 11:18:23', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `folders`
--

CREATE TABLE `folders` (
  `folder_id` int(11) NOT NULL,
  `FOLDER_NAME` varchar(255) NOT NULL,
  `DESCRIPTION` text DEFAULT NULL,
  `PARENT_ID` int(11) DEFAULT NULL,
  `TIMERS` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `folders`
--

INSERT INTO `folders` (`folder_id`, `FOLDER_NAME`, `DESCRIPTION`, `PARENT_ID`, `TIMERS`) VALUES
(8, 'FM', NULL, NULL, '2025-04-18 19:37:17'),
(9, 'ISP', NULL, NULL, '2025-04-19 09:31:38'),
(10, 'TV', NULL, NULL, '2025-04-19 09:31:51'),
(11, 'PRE', NULL, NULL, '2025-04-19 09:32:29'),
(12, 'NEWMONT GHANA ', NULL, 11, '2025-04-19 09:33:16'),
(13, 'COMSYS', NULL, 9, '2025-04-19 11:05:07'),
(14, 'UNIQUE FM ', NULL, 8, '2025-04-19 11:07:45'),
(15, 'Hillaris', NULL, 13, '2025-04-19 13:32:24'),
(16, 'Vobbis', NULL, 13, '2025-04-19 13:33:26'),
(17, 'Adom Tv', NULL, 10, '2025-05-15 15:13:47'),
(18, 'VSAT', NULL, NULL, '2025-05-15 21:25:01');

-- --------------------------------------------------------

--
-- Table structure for table `folder_files`
--

CREATE TABLE `folder_files` (
  `id` int(11) NOT NULL,
  `folder_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `size` varchar(50) DEFAULT NULL,
  `file_type` varchar(100) DEFAULT NULL,
  `timers` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `folder_files`
--

INSERT INTO `folder_files` (`id`, `folder_id`, `name`, `file_path`, `size`, `file_type`, `timers`) VALUES
(10, 8, 'NCA-FORM-AP01B A PLUS MEDIA(FM).docx.pdf', '../uploads/folders/8/NCA-FORM-AP01B A PLUS MEDIA(FM).docx.pdf', '867753', 'pdf', '2025-04-19 10:08:33'),
(11, 8, 'A PLUS MEDIA TECHNICAL FEASIBILITY.pdf', '../uploads/8/A PLUS MEDIA TECHNICAL FEASIBILITY.pdf', '587123', 'pdf', '2025-04-19 11:10:25'),
(12, 9, 'NCA-FORM-AP03C (1)  A PLUS MEDIA.docx', '../uploads/9/NCA-FORM-AP03C (1)  A PLUS MEDIA.docx', '1095302', 'docx', '2025-04-19 12:59:28'),
(13, 14, 'a plus media app letter fm.pdf', '../uploads/folders/14/a plus media app letter fm.pdf', '397925', 'pdf', '2025-04-19 12:31:18'),
(14, 12, 'NCA-FORM-AP01B new.pdf', '../uploads/12/NCA-FORM-AP01B new.pdf', '223154', 'pdf', '2025-04-19 16:27:31'),
(16, 17, 'sunziel (2).pdf', '../uploads/folders/17/sunziel (2).pdf', '10948043', 'pdf', '2025-05-15 16:14:12'),
(19, 9, 'd0b799da-a51c-494c-92a9-5f9ae2d098f0_report (1).docx', '../uploads/9/d0b799da-a51c-494c-92a9-5f9ae2d098f0_report (1).docx', '53134', 'docx', '2025-05-16 23:16:16');

-- --------------------------------------------------------

--
-- Table structure for table `folder_requests`
--

CREATE TABLE `folder_requests` (
  `id` int(11) NOT NULL,
  `user_email` varchar(255) NOT NULL,
  `requested_folder_name` varchar(255) NOT NULL,
  `reason` text DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `admin_email` varchar(255) DEFAULT NULL,
  `assigned_folder_id` int(11) DEFAULT NULL,
  `request_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `approval_date` datetime DEFAULT NULL,
  `expiry_date` datetime DEFAULT NULL,
  `is_expired` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `folder_requests`
--

INSERT INTO `folder_requests` (`id`, `user_email`, `requested_folder_name`, `reason`, `status`, `admin_email`, `assigned_folder_id`, `request_date`, `approval_date`, `expiry_date`, `is_expired`) VALUES
(28, 'kwame@gmail.com', 'unique', 'iohs;fx', 'approved', 'deucestod@gmail.com', 14, '2025-05-23 11:09:09', '2025-05-23 11:09:25', '2025-05-24 11:09:28', 1),
(30, 'kwame@gmail.com', 'tv', 'jdhudfu', 'approved', 'deucestod@gmail.com', 10, '2025-05-23 11:56:18', '2025-05-23 11:56:38', '2025-05-25 11:56:38', 0);

-- --------------------------------------------------------

--
-- Table structure for table `history_log`
--

CREATE TABLE `history_log` (
  `log_id` int(11) NOT NULL,
  `id` int(11) NOT NULL,
  `email_address` text NOT NULL,
  `action` varchar(100) NOT NULL,
  `actions` varchar(200) NOT NULL DEFAULT 'Has LoggedOut the system at',
  `ip` text NOT NULL,
  `host` text NOT NULL,
  `login_time` varchar(200) NOT NULL,
  `logout_time` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `history_log`
--

INSERT INTO `history_log` (`log_id`, `id`, `email_address`, `action`, `actions`, `ip`, `host`, `login_time`, `logout_time`) VALUES
(0, 1, 'richardsarpong@gmail.com', 'Has LoggedIn the system at', 'Has LoggedOut the system at', '::1', 'richard', 'May-29-2024 02:36 PM', 'May-19-2025 07:54 AM'),
(0, 1, 'emilyquarshie@gmail.com', 'Has LoggedIn the system at', 'Has LoggedOut the system at', '::1', 'emily', 'May-30-2024 04:30 PM', 'May-19-2025 07:54 AM'),
(0, 0, 'obeng@gmail.com', 'Has LoggedIn the system at', 'Has LoggedOut the system at', '::1', 'DESKTOP-342LEGC', 'Jan-24-2025 03:07 PM', 'May-19-2025 06:50 AM'),
(0, 1, 'obeng@gmail.com', 'Has LoggedIn the system at', 'Has LoggedOut the system at', '::1', 'jnr', 'Apr-13-2025 07:28 PM', 'May-19-2025 07:54 AM'),
(0, 1, 'obeng@gmail.com', 'Has LoggedIn the system at', 'Has LoggedOut the system at', '::1', 'jnr', 'Apr-13-2025 07:58 PM', 'May-19-2025 07:54 AM'),
(0, 1, 'obeng@gmail.com', 'Has LoggedIn the system at', 'Has LoggedOut the system at', '::1', 'jnr', 'Apr-13-2025 08:00 PM', 'May-19-2025 07:54 AM'),
(0, 1, 'obeng@gmail.com', 'Has LoggedIn the system at', 'Has LoggedOut the system at', '::1', 'jnr', 'Apr-13-2025 08:05 PM', 'May-19-2025 07:54 AM'),
(0, 1, 'obeng@gmail.com', 'Has LoggedIn the system at', 'Has LoggedOut the system at', '::1', 'jnr', 'Apr-13-2025 08:19 PM', 'May-19-2025 07:54 AM'),
(0, 1, 'obeng@gmail.com', 'Has LoggedIn the system at', 'Has LoggedOut the system at', '::1', 'jnr', 'Apr-13-2025 08:23 PM', 'May-19-2025 07:54 AM'),
(0, 1, 'obeng@gmail.com', 'Has LoggedIn the system at', 'Has LoggedOut the system at', '::1', 'jnr', 'Apr-13-2025 08:31 PM', 'May-19-2025 07:54 AM'),
(0, 1, 'obeng@gmail.com', 'Has LoggedIn the system at', 'Has LoggedOut the system at', '::1', 'jnr', 'Apr-18-2025 05:45 PM', 'May-19-2025 07:54 AM'),
(0, 1, 'obeng@gmail.com', 'Has LoggedIn the system at', 'Has LoggedOut the system at', '::1', 'jnr', 'Apr-18-2025 06:47 PM', 'May-19-2025 07:54 AM'),
(0, 1, 'obeng@gmail.com', 'Has LoggedIn the system at', 'Has LoggedOut the system at', '::1', 'jnr', 'Apr-18-2025 06:49 PM', 'May-19-2025 07:54 AM'),
(0, 1, 'obeng@gmail.com', 'Has LoggedIn the system at', 'Has LoggedOut the system at', '::1', 'jnr', 'Apr-18-2025 06:54 PM', 'May-19-2025 07:54 AM'),
(0, 1, 'obeng@gmail.com', 'Has LoggedIn the system at', 'Has LoggedOut the system at', '::1', 'jnr', 'Apr-18-2025 06:55 PM', 'May-19-2025 07:54 AM'),
(0, 1, 'obeng@gmail.com', 'Has LoggedIn the system at', 'Has LoggedOut the system at', '::1', 'jnr', 'Apr-18-2025 08:13 PM', 'May-19-2025 07:54 AM'),
(0, 1, 'obeng@gmail.com', 'Has LoggedIn the system at', 'Has LoggedOut the system at', '::1', 'jnr', 'Apr-18-2025 08:16 PM', 'May-19-2025 07:54 AM'),
(0, 1, 'obeng@gmail.com', 'Has LoggedIn the system at', 'Has LoggedOut the system at', '::1', 'jnr', 'Apr-19-2025 10:37 AM', 'May-19-2025 07:54 AM'),
(0, 1, 'obeng@gmail.com', 'Has LoggedIn the system at', 'Has LoggedOut the system at', '::1', 'jnr', 'Apr-19-2025 10:47 AM', 'May-19-2025 07:54 AM'),
(0, 1, 'obeng@gmail.com', 'Has LoggedIn the system at', 'Has LoggedOut the system at', '::1', 'jnr', 'Apr-19-2025 11:11 AM', 'May-19-2025 07:54 AM'),
(0, 1, 'obeng@gmail.com', 'Has LoggedIn the system at', 'Has LoggedOut the system at', '::1', 'jnr', 'Apr-19-2025 11:11 AM', 'May-19-2025 07:54 AM'),
(0, 1, 'obeng@gmail.com', 'Has LoggedIn the system at', 'Has LoggedOut the system at', '::1', 'jnr', 'Apr-19-2025 11:12 AM', 'May-19-2025 07:54 AM'),
(0, 1, 'obeng@gmail.com', 'Has LoggedIn the system at', 'Has LoggedOut the system at', '::1', 'jnr', 'Apr-19-2025 04:29 PM', 'May-19-2025 07:54 AM'),
(0, 1, 'obeng@gmail.com', 'Has LoggedIn the system at', 'Has LoggedOut the system at', '::1', 'jnr', 'Apr-19-2025 07:39 PM', 'May-19-2025 07:54 AM'),
(0, 1, 'obeng@gmail.com', 'Has LoggedIn the system at', 'Has LoggedOut the system at', '::1', 'jnr', 'May-14-2025 12:37 PM', 'May-19-2025 07:54 AM'),
(0, 1, 'obeng@gmail.com', 'Has LoggedIn the system at', 'Has LoggedOut the system at', '::1', 'jnr', 'May-14-2025 12:37 PM', 'May-19-2025 07:54 AM'),
(0, 1, 'obeng@gmail.com', 'Has LoggedIn the system at', 'Has LoggedOut the system at', '::1', 'jnr', 'May-15-2025 07:51 AM', 'May-19-2025 07:54 AM'),
(0, 1, 'obeng@gmail.com', 'Has LoggedIn the system at', 'Has LoggedOut the system at', '::1', 'jnr', 'May-16-2025 07:52 AM', 'May-19-2025 07:54 AM'),
(0, 1, 'obeng@gmail.com', 'Has LoggedIn the system at', 'Has LoggedOut the system at', '::1', 'jnr', 'May-16-2025 05:05 PM', 'May-19-2025 07:54 AM'),
(0, 1, 'obeng@gmail.com', 'Has LoggedIn the system at', 'Has LoggedOut the system at', '::1', 'jnr', 'May-16-2025 05:25 PM', 'May-19-2025 07:54 AM'),
(0, 1, 'obeng@gmail.com', 'Has LoggedIn the system at', 'Has LoggedOut the system at', '::1', 'jnr', 'May-16-2025 05:25 PM', 'May-19-2025 07:54 AM'),
(0, 1, 'obeng@gmail.com', 'Has LoggedIn the system at', 'Has LoggedOut the system at', '::1', 'jnr', 'May-19-2025 07:01 AM', 'May-19-2025 07:54 AM'),
(0, 3, 'kwame@gmail.com', 'Has LoggedIn the system at', 'Has LoggedOut the system at', '::1', 'jnr', 'May-19-2025 07:54 AM', '');

-- --------------------------------------------------------

--
-- Table structure for table `history_log1`
--

CREATE TABLE `history_log1` (
  `log_id` int(11) NOT NULL,
  `id` int(11) NOT NULL,
  `admin_user` text NOT NULL,
  `action` varchar(100) NOT NULL,
  `actions` varchar(200) NOT NULL DEFAULT 'Has LoggedOut the system at',
  `ip` text NOT NULL,
  `host` text NOT NULL,
  `login_time` varchar(200) NOT NULL,
  `logout_time` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `history_log1`
--

INSERT INTO `history_log1` (`log_id`, `id`, `admin_user`, `action`, `actions`, `ip`, `host`, `login_time`, `logout_time`) VALUES
(0, 11, 'richard@gmail.com', 'Has LoggedIn the system at', 'Has LoggedOut the system at', '::1', 'buhayko-PC', 'May-29-2019 02:34 PM', 'May-29-2024 02:35 PM'),
(0, 12, 'richard@gmail.com', 'Has LoggedIn the system at', 'Has LoggedOut the system at', '::1', 'buhayko-PC', 'May-29-2019 02:35 PM', 'Mar-27-2024 10:59 PM'),
(0, 12, 'richard@gmail.com', 'Has LoggedIn the system at', 'Has LoggedOut the system at', '::1', 'buhayko-PC', 'May-29-2019 02:37 PM', 'Mar-27-2024 10:59 PM'),
(0, 12, 'emily@gmail.com', 'Has LoggedIn the system at', 'Has LoggedOut the system at', '::1', 'buhayko-PC', 'May-30-2019 04:33 PM', 'Mar-27-2021 10:59 PM'),
(0, 12, 'emily@gmail.com', 'Has LoggedIn the system at', 'Has LoggedOut the system at', '127.0.0.1', 'keystone', 'Mar-27-2021 10:56 PM', 'Mar-27-2021 10:59 PM'),
(0, 13, 'deucestod@gmail.com', 'Has LoggedIn the system at', 'Has LoggedOut the system at', '127.0.0.1', 'keyston', 'Mar-27-2021 10:59 PM', 'May-16-2025 07:52 AM'),
(0, 13, 'deucestod@gmail.com', 'Has LoggedIn the system at', 'Has LoggedOut the system at', '::1', 'DESKTOP-342LEGC', 'Jan-24-2025 09:38 AM', 'May-16-2025 07:52 AM'),
(0, 13, 'deucestod@gmail.com', 'Has LoggedIn the system at', 'Has LoggedOut the system at', '::1', 'DESKTOP-342LEGC', 'Jan-24-2025 09:39 AM', 'May-16-2025 07:52 AM'),
(0, 13, 'deucestod@gmail.com', 'Has LoggedIn the system at', 'Has LoggedOut the system at', '::1', 'DESKTOP-342LEGC', 'Jan-24-2025 03:06 PM', 'May-16-2025 07:52 AM'),
(0, 13, 'deucestod@gmail.com', 'Has LoggedIn the system at', 'Has LoggedOut the system at', '::1', 'DESKTOP-342LEGC', 'Jan-24-2025 05:39 PM', 'May-16-2025 07:52 AM'),
(0, 13, 'deucestod@gmail.com', 'Has LoggedIn the system at', 'Has LoggedOut the system at', '::1', 'DESKTOP-342LEGC', 'Jan-25-2025 05:29 PM', 'May-16-2025 07:52 AM'),
(0, 13, 'deucestod@gmail.com', 'Has LoggedIn the system at', 'Has LoggedOut the system at', '::1', 'DESKTOP-342LEGC', 'Jan-27-2025 07:45 AM', 'May-16-2025 07:52 AM'),
(0, 13, 'deucestod@gmail.com', 'Has LoggedIn the system at', 'Has LoggedOut the system at', '::1', 'DESKTOP-342LEGC', 'Feb-01-2025 01:09 AM', 'May-16-2025 07:52 AM'),
(0, 13, 'deucestod@gmail.com', 'Has LoggedIn the system at', 'Has LoggedOut the system at', '::1', 'DESKTOP-342LEGC', 'Feb-11-2025 10:47 PM', 'May-16-2025 07:52 AM'),
(0, 13, 'deucestod@gmail.com', 'Has LoggedIn the system at', 'Has LoggedOut the system at', '::1', 'jnr', 'Apr-13-2025 03:14 PM', 'May-16-2025 07:52 AM'),
(0, 13, 'deucestod@gmail.com', 'Has LoggedIn the system at', 'Has LoggedOut the system at', '::1', 'jnr', 'Apr-13-2025 05:37 PM', 'May-16-2025 07:52 AM'),
(0, 13, 'deucestod@gmail.com', 'Has LoggedIn the system at', 'Has LoggedOut the system at', '::1', 'jnr', 'Apr-13-2025 05:39 PM', 'May-16-2025 07:52 AM'),
(0, 13, 'deucestod@gmail.com', 'Has LoggedIn the system at', 'Has LoggedOut the system at', '::1', 'jnr', 'Apr-13-2025 05:39 PM', 'May-16-2025 07:52 AM'),
(0, 13, 'deucestod@gmail.com', 'Has LoggedIn the system at', 'Has LoggedOut the system at', '::1', 'jnr', 'Apr-13-2025 05:44 PM', 'May-16-2025 07:52 AM'),
(0, 13, 'deucestod@gmail.com', 'Has LoggedIn the system at', 'Has LoggedOut the system at', '::1', 'jnr', 'Apr-13-2025 06:14 PM', 'May-16-2025 07:52 AM'),
(0, 13, 'deucestod@gmail.com', 'Has LoggedIn the system at', 'Has LoggedOut the system at', '::1', 'jnr', 'Apr-13-2025 07:50 PM', 'May-16-2025 07:52 AM'),
(0, 13, 'deucestod@gmail.com', 'Has LoggedIn the system at', 'Has LoggedOut the system at', '::1', 'jnr', 'Apr-13-2025 08:39 PM', 'May-16-2025 07:52 AM'),
(0, 13, 'deucestod@gmail.com', 'Has LoggedIn the system at', 'Has LoggedOut the system at', '::1', 'jnr', 'Apr-14-2025 07:11 PM', 'May-16-2025 07:52 AM'),
(0, 13, 'deucestod@gmail.com', 'Has LoggedIn the system at', 'Has LoggedOut the system at', '::1', 'jnr', 'Apr-16-2025 10:07 PM', 'May-16-2025 07:52 AM'),
(0, 13, 'deucestod@gmail.com', 'Has LoggedIn the system at', 'Has LoggedOut the system at', '::1', 'jnr', 'Apr-16-2025 10:34 PM', 'May-16-2025 07:52 AM'),
(0, 13, 'deucestod@gmail.com', 'Has LoggedIn the system at', 'Has LoggedOut the system at', '::1', 'jnr', 'Apr-18-2025 11:28 AM', 'May-16-2025 07:52 AM'),
(0, 13, 'deucestod@gmail.com', 'Has LoggedIn the system at', 'Has LoggedOut the system at', '::1', 'jnr', 'Apr-18-2025 08:02 PM', 'May-16-2025 07:52 AM'),
(0, 13, 'deucestod@gmail.com', 'Has LoggedIn the system at', 'Has LoggedOut the system at', '::1', 'jnr', 'Apr-18-2025 08:13 PM', 'May-16-2025 07:52 AM'),
(0, 13, 'deucestod@gmail.com', 'Has LoggedIn the system at', 'Has LoggedOut the system at', '::1', 'jnr', 'Apr-18-2025 08:31 PM', 'May-16-2025 07:52 AM'),
(0, 13, 'deucestod@gmail.com', 'Has LoggedIn the system at', 'Has LoggedOut the system at', '::1', 'jnr', 'Apr-19-2025 10:52 AM', 'May-16-2025 07:52 AM'),
(0, 13, 'deucestod@gmail.com', 'Has LoggedIn the system at', 'Has LoggedOut the system at', '::1', 'jnr', 'Apr-19-2025 11:27 AM', 'May-16-2025 07:52 AM'),
(0, 13, 'deucestod@gmail.com', 'Has LoggedIn the system at', 'Has LoggedOut the system at', '::1', 'jnr', 'Apr-19-2025 04:30 PM', 'May-16-2025 07:52 AM'),
(0, 13, 'deucestod@gmail.com', 'Has LoggedIn the system at', 'Has LoggedOut the system at', '::1', 'jnr', 'Apr-19-2025 04:32 PM', 'May-16-2025 07:52 AM'),
(0, 13, 'deucestod@gmail.com', 'Has LoggedIn the system at', 'Has LoggedOut the system at', '::1', 'jnr', 'Apr-19-2025 07:24 PM', 'May-16-2025 07:52 AM'),
(0, 13, 'deucestod@gmail.com', 'Has LoggedIn the system at', 'Has LoggedOut the system at', '::1', 'jnr', 'May-14-2025 12:31 PM', 'May-16-2025 07:52 AM'),
(0, 13, 'deucestod@gmail.com', 'Has LoggedIn the system at', 'Has LoggedOut the system at', '::1', 'jnr', 'May-15-2025 08:04 AM', 'May-16-2025 07:52 AM'),
(0, 13, 'deucestod@gmail.com', 'Has LoggedIn the system at', 'Has LoggedOut the system at', '::1', 'jnr', 'May-16-2025 07:57 AM', ''),
(0, 13, 'deucestod@gmail.com', 'Has LoggedIn the system at', 'Has LoggedOut the system at', '::1', 'jnr', 'May-16-2025 10:11 PM', ''),
(0, 13, 'deucestod@gmail.com', 'Has LoggedIn the system at', 'Has LoggedOut the system at', '::1', 'jnr', 'May-17-2025 09:54 AM', ''),
(0, 13, 'deucestod@gmail.com', 'Has LoggedIn the system at', 'Has LoggedOut the system at', '::1', 'jnr', 'May-19-2025 07:00 AM', ''),
(0, 13, 'deucestod@gmail.com', 'Has LoggedIn the system at', 'Has LoggedOut the system at', '::1', 'jnr', 'May-19-2025 07:54 AM', '');

-- --------------------------------------------------------

--
-- Table structure for table `login_user`
--

CREATE TABLE `login_user` (
  `id` int(11) NOT NULL,
  `name` text NOT NULL,
  `email_address` text NOT NULL,
  `user_password` text NOT NULL,
  `user_status` varchar(50) NOT NULL,
  `department` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `login_user`
--

INSERT INTO `login_user` (`id`, `name`, `email_address`, `user_password`, `user_status`, `department`) VALUES
(1, 'Obeng Kwabena Emmanuel ', 'obeng@gmail.com', '$2y$12$GDI1Bh2pm4X5dnqrXO6mHO3YCcxrwAdylEm4QdLB5mHo1uQhEjeEu', 'Employee', 'IT'),
(3, 'hi love', 'kwame@gmail.com', '$2y$12$scNgSPfYKSqPLdob1NDmEOHNF6CyzQp5JwMXLtIwuWULFb7r5fOnW', '', 'IT');

-- --------------------------------------------------------

--
-- Table structure for table `security_logs`
--

CREATE TABLE `security_logs` (
  `id` int(11) NOT NULL,
  `file_id` int(11) NOT NULL,
  `user_email` varchar(255) NOT NULL,
  `event_type` varchar(50) NOT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text DEFAULT NULL,
  `event_time` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `security_logs`
--

INSERT INTO `security_logs` (`id`, `file_id`, `user_email`, `event_type`, `details`, `ip_address`, `user_agent`, `event_time`) VALUES
(47, 11, 'kwame@gmail.com', 'right_click', 'User attempted right-click', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-19 09:40:57'),
(48, 11, 'kwame@gmail.com', 'right_click', 'User attempted right-click', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-19 09:40:57'),
(49, 11, 'kwame@gmail.com', 'screenshot_attempt', 'User attempted Print Screen', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-19 09:41:04'),
(50, 11, 'kwame@gmail.com', 'screenshot_attempt', 'User attempted Print Screen', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-19 09:41:35'),
(51, 11, 'kwame@gmail.com', 'right_click', 'User attempted right-click', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-19 11:14:17'),
(52, 11, 'kwame@gmail.com', 'right_click', 'User attempted right-click', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-19 11:14:17'),
(53, 11, 'kwame@gmail.com', 'right_click', 'User attempted right-click', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-19 12:35:13'),
(54, 11, 'kwame@gmail.com', 'right_click', 'User attempted right-click', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-19 12:35:13'),
(55, 11, 'kwame@gmail.com', 'screenshot_attempt', 'User attempted Print Screen', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-19 12:35:19'),
(56, 16, 'kwame@gmail.com', 'right_click', 'User attempted right-click', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-20 00:11:05'),
(57, 16, 'kwame@gmail.com', 'right_click', 'User attempted right-click', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-20 00:11:05'),
(58, 16, 'kwame@gmail.com', 'screenshot_attempt', 'User attempted Print Screen', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-21 19:24:18');

-- --------------------------------------------------------

--
-- Table structure for table `upload_files`
--

CREATE TABLE `upload_files` (
  `ID` int(11) NOT NULL,
  `NAME` varchar(200) NOT NULL,
  `COMMENT` text DEFAULT NULL,
  `SIZE` varchar(200) NOT NULL,
  `DOWNLOAD` varchar(200) NOT NULL,
  `TIMERS` varchar(200) NOT NULL,
  `ADMIN_STATUS` varchar(300) NOT NULL,
  `EMAIL` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `upload_files`
--

INSERT INTO `upload_files` (`ID`, `NAME`, `COMMENT`, `SIZE`, `DOWNLOAD`, `TIMERS`, `ADMIN_STATUS`, `EMAIL`) VALUES
(6, 'NCA-FORM-AP03C (1)  A PLUS MEDIA.docx', NULL, '1095302', '2', 'Apr-13-2025 10:34 PM', 'Admin', 'Blessing Appiah Kubi'),
(7, 'NCA-FORM-AP03C (1)  A PLUS MEDIA.docx', NULL, '1095302', '0', 'Apr-13-2025 08:44 PM', 'Employee', 'Obeng Kwabena Emmanuel '),
(9, 'healthcare-flye_1744771796.zip', NULL, '580099', '3', 'Apr-17-2025 05:43 AM', 'Admin', 'Blessing Appiah Kubi'),
(11, 'sunziel.pdf', 'nhyira fm file', '10948043', '2', 'May-14-2025 01:12 PM', 'Employee', 'Obeng Kwabena Emmanuel ');

-- --------------------------------------------------------

--
-- Table structure for table `user_logs`
--

CREATE TABLE `user_logs` (
  `id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `action` text NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_logs`
--

INSERT INTO `user_logs` (`id`, `email`, `action`, `created_at`) VALUES
(1, 'obeng@gmail.com', 'Previewed file: NCA-FORM-AP01B new.pdf from folder: NEWMONT GHANA ', '2025-05-16 07:11:08'),
(2, 'obeng@gmail.com', 'Previewed file: NCA-FORM-AP01B new.pdf from folder: NEWMONT GHANA ', '2025-05-16 07:12:33');

-- --------------------------------------------------------

--
-- Structure for view `active_approved_requests`
--
DROP TABLE IF EXISTS `active_approved_requests`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `active_approved_requests`  AS SELECT `folder_requests`.`id` AS `id`, `folder_requests`.`user_email` AS `user_email`, `folder_requests`.`requested_folder_name` AS `requested_folder_name`, `folder_requests`.`reason` AS `reason`, `folder_requests`.`status` AS `status`, `folder_requests`.`admin_email` AS `admin_email`, `folder_requests`.`assigned_folder_id` AS `assigned_folder_id`, `folder_requests`.`request_date` AS `request_date`, `folder_requests`.`approval_date` AS `approval_date`, `folder_requests`.`expiry_date` AS `expiry_date`, `folder_requests`.`is_expired` AS `is_expired` FROM `folder_requests` WHERE `folder_requests`.`status` = 'approved' AND (`folder_requests`.`is_expired` = 0 OR `folder_requests`.`is_expired` is null) AND `folder_requests`.`expiry_date` > current_timestamp() ;

-- --------------------------------------------------------

--
-- Structure for view `expired_requests`
--
DROP TABLE IF EXISTS `expired_requests`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `expired_requests`  AS SELECT `folder_requests`.`id` AS `id`, `folder_requests`.`user_email` AS `user_email`, `folder_requests`.`requested_folder_name` AS `requested_folder_name`, `folder_requests`.`reason` AS `reason`, `folder_requests`.`status` AS `status`, `folder_requests`.`admin_email` AS `admin_email`, `folder_requests`.`assigned_folder_id` AS `assigned_folder_id`, `folder_requests`.`request_date` AS `request_date`, `folder_requests`.`approval_date` AS `approval_date`, `folder_requests`.`expiry_date` AS `expiry_date`, `folder_requests`.`is_expired` AS `is_expired` FROM `folder_requests` WHERE `folder_requests`.`status` = 'expired' OR `folder_requests`.`status` = 'approved' AND `folder_requests`.`is_expired` = 1 ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_file_logs`
--
ALTER TABLE `admin_file_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `idx_admin_email` (`admin_email`),
  ADD KEY `idx_file_id` (`file_id`),
  ADD KEY `idx_folder_id` (`folder_id`),
  ADD KEY `idx_action` (`action`),
  ADD KEY `idx_timestamp` (`timestamp`);

--
-- Indexes for table `admin_login`
--
ALTER TABLE `admin_login`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `file_access_logs`
--
ALTER TABLE `file_access_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `file_id` (`file_id`),
  ADD KEY `user_email` (`user_email`);

--
-- Indexes for table `folders`
--
ALTER TABLE `folders`
  ADD PRIMARY KEY (`folder_id`),
  ADD KEY `PARENT_ID` (`PARENT_ID`);

--
-- Indexes for table `folder_files`
--
ALTER TABLE `folder_files`
  ADD PRIMARY KEY (`id`),
  ADD KEY `folder_id` (`folder_id`);

--
-- Indexes for table `folder_requests`
--
ALTER TABLE `folder_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_assigned_folder` (`assigned_folder_id`),
  ADD KEY `idx_expiry_status` (`status`,`expiry_date`,`is_expired`);

--
-- Indexes for table `login_user`
--
ALTER TABLE `login_user`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `security_logs`
--
ALTER TABLE `security_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `upload_files`
--
ALTER TABLE `upload_files`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `user_logs`
--
ALTER TABLE `user_logs`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_file_logs`
--
ALTER TABLE `admin_file_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `file_access_logs`
--
ALTER TABLE `file_access_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=177;

--
-- AUTO_INCREMENT for table `folders`
--
ALTER TABLE `folders`
  MODIFY `folder_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `folder_files`
--
ALTER TABLE `folder_files`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `folder_requests`
--
ALTER TABLE `folder_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `login_user`
--
ALTER TABLE `login_user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `security_logs`
--
ALTER TABLE `security_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=59;

--
-- AUTO_INCREMENT for table `upload_files`
--
ALTER TABLE `upload_files`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `user_logs`
--
ALTER TABLE `user_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `folder_files`
--
ALTER TABLE `folder_files`
  ADD CONSTRAINT `folder_files_ibfk_1` FOREIGN KEY (`folder_id`) REFERENCES `folders` (`folder_id`) ON DELETE CASCADE;

--
-- Constraints for table `folder_requests`
--
ALTER TABLE `folder_requests`
  ADD CONSTRAINT `fk_assigned_folder` FOREIGN KEY (`assigned_folder_id`) REFERENCES `folders` (`folder_id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
