-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- 主机： sql102.infinityfree.com
-- 生成日期： 2026-05-26 13:26:16
-- 服务器版本： 11.4.11-MariaDB
-- PHP 版本： 7.2.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- 数据库： `if0_41857411_sys_property_db`
--

-- --------------------------------------------------------

--
-- 表的结构 `accounts`
--

CREATE TABLE `accounts` (
  `account_id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('CUSTOMER','STAFF','ADMIN') NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 转存表中的数据 `accounts`
--

INSERT INTO `accounts` (`account_id`, `email`, `password_hash`, `role`, `created_at`) VALUES
(1, 'admin.ahmad@sysproperty.com.my', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'ADMIN', '2026-05-07 15:09:47'),
(2, 'admin.david@sysproperty.com.my', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'ADMIN', '2026-05-07 15:09:47'),
(3, 'staff.siti@sysproperty.com.my', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'STAFF', '2026-05-07 15:09:47'),
(4, 'staff.chong@sysproperty.com.my', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'STAFF', '2026-05-07 15:09:47'),
(5, 'staff.muthu@sysproperty.com.my', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'STAFF', '2026-05-07 15:09:47'),
(6, 'staff.nurul@sysproperty.com.my', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'STAFF', '2026-05-07 15:09:47'),
(7, 'staff.wong@sysproperty.com.my', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'STAFF', '2026-05-07 15:09:47'),
(8, 'staff.aisyah@sysproperty.com.my', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'STAFF', '2026-05-07 15:09:47'),
(9, 'staff.tan@sysproperty.com.my', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'STAFF', '2026-05-07 15:09:47'),
(10, 'staff.prakash@sysproperty.com.my', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'STAFF', '2026-05-07 15:09:47'),
(11, 'staff.fatima@sysproperty.com.my', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'STAFF', '2026-05-07 15:09:47'),
(12, 'lcw@gmail.com', '$2y$10$f3gtHluJ.waiaf6Zo0rRsu7SQ8hLTcaaiRG7aK8llCYYkQKRHDESG', 'STAFF', '2026-05-07 15:09:47'),
(13, 'staff.sivakumar@sysproperty.com.my', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'STAFF', '2026-05-07 15:09:47'),
(14, 'staff.aminah@sysproperty.com.my', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'STAFF', '2026-05-07 15:09:47'),
(15, 'staff.goh@sysproperty.com.my', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'STAFF', '2026-05-07 15:09:47'),
(16, 'staff.ravi@sysproperty.com.my', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'STAFF', '2026-05-07 15:09:47'),
(17, 'staff.ali@sysproperty.com.my', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'STAFF', '2026-05-07 15:09:47'),
(18, 'staff.chan@sysproperty.com.my', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'STAFF', '2026-05-07 15:09:47'),
(19, 'staff.kavita@sysproperty.com.my', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'STAFF', '2026-05-07 15:09:47'),
(20, 'staff.zulkifli@sysproperty.com.my', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'STAFF', '2026-05-07 15:09:47'),
(21, 'staff.swee@sysproperty.com.my', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'STAFF', '2026-05-07 15:09:47'),
(22, 'staff.sangeetha@sysproperty.com.my', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'STAFF', '2026-05-07 15:09:47'),
(23, 'staff.omar@sysproperty.com.my', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'STAFF', '2026-05-07 15:09:47'),
(24, 'staff.daren@sysproperty.com.my', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'STAFF', '2026-05-07 15:09:47'),
(25, 'staff.saadiah@sysproperty.com.my', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'STAFF', '2026-05-07 15:09:47'),
(26, 'staff.tze@sysproperty.com.my', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'STAFF', '2026-05-07 15:09:47'),
(27, 'staff.safiq@sysproperty.com.my', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'STAFF', '2026-05-07 15:09:47'),
(28, 'staff.teo@sysproperty.com.my', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'STAFF', '2026-05-07 15:09:47'),
(29, 'staff.arif@sysproperty.com.my', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'STAFF', '2026-05-07 15:09:47'),
(30, 'staff.ong@sysproperty.com.my', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'STAFF', '2026-05-07 15:09:47'),
(33, 'amiruddin@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'CUSTOMER', '2026-05-07 15:09:47'),
(34, 'weijian@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'CUSTOMER', '2026-05-07 15:09:47'),
(35, 'suresh@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'CUSTOMER', '2026-05-07 15:09:47'),
(36, 'hidayah@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'CUSTOMER', '2026-05-07 15:09:47'),
(37, 'siewmin@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'CUSTOMER', '2026-05-07 15:09:47'),
(38, 'karthik@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'CUSTOMER', '2026-05-07 15:09:47'),
(39, 'mohdfaiz@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'CUSTOMER', '2026-05-07 15:09:47'),
(40, 'rachel@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'CUSTOMER', '2026-05-07 15:09:47'),
(41, 'ahmadzaki@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'CUSTOMER', '2026-05-07 15:09:47'),
(43, 'gohchee@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'CUSTOMER', '2026-05-07 15:09:47'),
(44, 'priya@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'CUSTOMER', '2026-05-07 15:09:47'),
(45, 'hafizuddin@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'CUSTOMER', '2026-05-07 15:09:47'),
(46, 'tests@gmail.com', '$2y$10$fqbqVqwVDRNksgyypsdVKOsv/Li.RVZtJ5HxFtJmlKV0tK1eSnswK', 'STAFF', '2026-05-07 15:09:47'),
(47, 'testc@gmail.com', '$2y$10$rd2lqI0/QFm8n.FTXmPnbepM21DaiAQk75rUEcN.YtSq/xopGEQua', 'CUSTOMER', '2026-05-07 15:09:47'),
(49, 'test@gmail.com', '$2y$10$M6d4K9p/J31X2bEAKDehBejc/BLFFMTbP/HeUsKn8m/dsm.ZxXmS2', 'ADMIN', '2026-05-07 19:07:41'),
(50, 'khairunnisakamal22@gmail.com', '$2y$10$IgSBVi/W9YMNDckrByQeA.tZr.q19beQKhZCAkT6EoZFRcgqHT.cG', 'CUSTOMER', '2026-05-07 20:35:35'),
(51, 'win0621@gmail.com', '$2y$10$Dfwqgrg0NPMiUHllag.mLODHajSR/WsYlopmDRO650jp/dwL52Iky', 'CUSTOMER', '2026-05-08 06:37:59'),
(54, 'jason@gmail.com', '$2y$10$zGIYX1ldK.zmHgFpp4OsGuH7Cf3x8B5NYR3YQtuZ77AfAeNJO6PG.', 'ADMIN', '2026-05-08 14:43:37'),
(56, 'staff123@gmail.com', '$2y$10$aVNNeU1DWjfVJpBq398/vO4KJtts55TDTClJduvJ0k6cmjFEnU/Um', 'CUSTOMER', '2026-05-11 00:20:57'),
(57, '1234@gmail.com', '$2y$10$N.HyOUUN1AINVsad6WfmuuiPcEnadsNyK11DTL3XlB1qRkH/nwjeu', 'CUSTOMER', '2026-05-11 20:18:38'),
(59, 'kahjun@gmail.com', '$2y$10$Hb/rvth4Un0P1Qd06kDyAe73PrBj7XIcADwBNumhADMp/KXuhCBLi', 'CUSTOMER', '2026-05-14 18:58:42'),
(60, 'jasonkl@gmail.com', '$2y$10$5xLjpWiAw.lQ9DymZsAvQe/iHFDMXIFELdSyEYERqpW4MDeoMoBIG', 'STAFF', '2026-05-18 07:48:34');

-- --------------------------------------------------------

--
-- 表的结构 `admins`
--

CREATE TABLE `admins` (
  `admin_id` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 转存表中的数据 `admins`
--

INSERT INTO `admins` (`admin_id`, `full_name`) VALUES
(1, 'Ahmad Razali'),
(2, 'David Lee'),
(54, 'JASON');

-- --------------------------------------------------------

--
-- 表的结构 `affordable_housing_applications`
--

CREATE TABLE `affordable_housing_applications` (
  `application_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `property_id` int(11) NOT NULL,
  `status` enum('PENDING_REVIEW','APPROVED_FOR_DRAW','REJECTED','WINNER') NOT NULL DEFAULT 'PENDING_REVIEW',
  `reviewed_by_staff_id` int(11) DEFAULT NULL,
  `application_date` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 转存表中的数据 `affordable_housing_applications`
--

INSERT INTO `affordable_housing_applications` (`application_id`, `customer_id`, `property_id`, `status`, `reviewed_by_staff_id`, `application_date`) VALUES
(1, 38, 5, 'PENDING_REVIEW', NULL, '2026-05-01 08:30:00'),
(2, 39, 7, 'PENDING_REVIEW', NULL, '2026-05-02 09:15:00'),
(3, 40, 8, 'APPROVED_FOR_DRAW', 3, '2026-05-03 10:45:00'),
(4, 41, 20, 'APPROVED_FOR_DRAW', 5, '2026-05-04 11:20:00'),
(6, 51, 57, 'APPROVED_FOR_DRAW', 46, '2026-05-16 12:00:58'),
(7, 51, 177, 'PENDING_REVIEW', NULL, '2026-05-18 17:49:20'),
(8, 51, 57, 'REJECTED', 46, '2026-05-19 01:09:00'),
(10, 51, 93, 'PENDING_REVIEW', NULL, '2026-05-22 08:58:55');

-- --------------------------------------------------------

--
-- 表的结构 `appointments`
--

CREATE TABLE `appointments` (
  `appointment_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `property_id` int(11) NOT NULL,
  `assigned_staff_id` int(11) DEFAULT NULL,
  `service_type` enum('SHOWROOM_VIEWING','FINANCIAL_CONSULTATION') NOT NULL,
  `appointment_date` date NOT NULL,
  `appointment_time` time NOT NULL,
  `status` enum('REQUESTED','ASSIGNED','COMPLETED','NO_SHOW','CANCELLED') NOT NULL DEFAULT 'REQUESTED',
  `staff_remarks` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 转存表中的数据 `appointments`
--

INSERT INTO `appointments` (`appointment_id`, `customer_id`, `property_id`, `assigned_staff_id`, `service_type`, `appointment_date`, `appointment_time`, `status`, `staff_remarks`) VALUES
(1, 33, 1, 3, 'SHOWROOM_VIEWING', '2026-05-15', '10:00:00', 'ASSIGNED', NULL),
(2, 34, 2, 4, 'FINANCIAL_CONSULTATION', '2026-05-16', '11:30:00', 'ASSIGNED', NULL),
(3, 35, 10, 3, 'SHOWROOM_VIEWING', '2026-05-17', '14:00:00', 'ASSIGNED', NULL),
(4, 36, 13, 5, 'SHOWROOM_VIEWING', '2026-05-18', '15:30:00', 'ASSIGNED', NULL),
(5, 37, 25, 7, 'FINANCIAL_CONSULTATION', '2026-05-19', '09:00:00', 'ASSIGNED', NULL),
(6, 51, 59, 46, 'SHOWROOM_VIEWING', '2026-05-11', '16:00:00', 'COMPLETED', ''),
(10, 51, 52, NULL, 'FINANCIAL_CONSULTATION', '2026-05-13', '13:00:00', 'REQUESTED', NULL),
(12, 51, 51, NULL, 'SHOWROOM_VIEWING', '2026-05-23', '16:30:00', 'CANCELLED', NULL),
(15, 47, 49, 46, 'SHOWROOM_VIEWING', '2026-05-19', '17:00:00', 'ASSIGNED', NULL),
(16, 51, 173, NULL, 'SHOWROOM_VIEWING', '2026-05-22', '15:00:00', 'REQUESTED', NULL),
(18, 51, 171, NULL, 'SHOWROOM_VIEWING', '2026-05-24', '17:00:00', 'REQUESTED', NULL),
(19, 47, 170, 60, 'SHOWROOM_VIEWING', '2026-05-19', '15:00:00', 'ASSIGNED', NULL),
(20, 51, 55, NULL, 'SHOWROOM_VIEWING', '2026-05-31', '14:30:00', 'CANCELLED', NULL),
(21, 47, 169, NULL, 'SHOWROOM_VIEWING', '2026-05-26', '17:00:00', 'REQUESTED', NULL),
(23, 47, 59, NULL, 'SHOWROOM_VIEWING', '2026-05-27', '13:00:00', 'REQUESTED', NULL),
(24, 47, 51, 46, 'SHOWROOM_VIEWING', '2026-05-20', '10:30:00', 'ASSIGNED', NULL),
(25, 51, 174, NULL, 'SHOWROOM_VIEWING', '2026-05-31', '10:00:00', 'REQUESTED', NULL),
(27, 51, 51, NULL, 'SHOWROOM_VIEWING', '2026-05-30', '15:00:00', 'REQUESTED', NULL),
(28, 51, 52, 46, 'SHOWROOM_VIEWING', '2026-05-29', '13:00:00', 'COMPLETED', 'Dear WIN, we have receive your Financial Document. As I opinion your loan will be approved for this property.'),
(29, 51, 171, NULL, 'SHOWROOM_VIEWING', '2026-06-05', '16:00:00', 'REQUESTED', NULL),
(30, 51, 51, 56, 'SHOWROOM_VIEWING', '2026-06-03', '16:00:00', 'ASSIGNED', NULL),
(31, 51, 88, NULL, 'SHOWROOM_VIEWING', '2026-06-06', '19:30:00', 'REQUESTED', NULL);

-- --------------------------------------------------------

--
-- 表的结构 `audit_logs`
--

CREATE TABLE `audit_logs` (
  `log_id` int(11) NOT NULL,
  `account_id` int(11) DEFAULT NULL,
  `action_type` enum('DOCUMENT_PURGED','LUCKY_DRAW_EXECUTED','LEAD_ASSIGNED','STATUS_UPDATED') NOT NULL,
  `entity_type` varchar(50) NOT NULL,
  `entity_id` int(11) NOT NULL,
  `timestamp` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 转存表中的数据 `audit_logs`
--

INSERT INTO `audit_logs` (`log_id`, `account_id`, `action_type`, `entity_type`, `entity_id`, `timestamp`) VALUES
(1, 54, 'LEAD_ASSIGNED', 'appointment_id', 6, '2026-05-10 23:14:59'),
(2, 54, 'LEAD_ASSIGNED', 'appointment_id', 7, '2026-05-10 23:26:21'),
(3, 54, 'LEAD_ASSIGNED', 'appointment_id', 8, '2026-05-11 18:29:09'),
(4, 54, 'LEAD_ASSIGNED', 'appointment_id', 10, '2026-05-11 19:15:50'),
(5, 54, 'LEAD_ASSIGNED', 'appointment_id', 9, '2026-05-17 01:52:11'),
(6, 54, 'LEAD_ASSIGNED', 'appointment_id', 12, '2026-05-17 01:58:14'),
(7, 54, 'LEAD_ASSIGNED', 'appointment_id', 14, '2026-05-17 02:04:21'),
(8, 54, 'LEAD_ASSIGNED', 'appointment_id', 15, '2026-05-17 02:09:25'),
(9, 54, 'LEAD_ASSIGNED', 'appointment_id', 23, '2026-05-18 13:19:21'),
(10, 54, 'LEAD_ASSIGNED', 'appointment_id', 24, '2026-05-18 13:22:55'),
(11, 54, 'LEAD_ASSIGNED', 'appointment_id', 25, '2026-05-18 17:59:59'),
(12, 54, 'LEAD_ASSIGNED', 'appointment_id', 28, '2026-05-19 00:56:17'),
(13, 54, 'LEAD_ASSIGNED', 'appointment_id', 19, '2026-05-22 06:49:37'),
(14, 54, 'LEAD_ASSIGNED', 'appointment_id', 29, '2026-05-22 06:53:34'),
(15, 54, 'LEAD_ASSIGNED', 'appointment_id', 30, '2026-05-25 00:20:36'),
(16, NULL, 'DOCUMENT_PURGED', 'document_id', 30, '2026-05-26 08:31:58'),
(17, NULL, 'DOCUMENT_PURGED', 'document_id', 28, '2026-05-26 08:33:35'),
(18, NULL, 'DOCUMENT_PURGED', 'document_id', 32, '2026-05-26 08:39:02');

-- --------------------------------------------------------

--
-- 表的结构 `banks`
--

CREATE TABLE `banks` (
  `bank_id` int(11) NOT NULL,
  `bank_name` varchar(100) NOT NULL,
  `interest_rate` decimal(4,2) NOT NULL,
  `effective_quarter` varchar(10) DEFAULT 'Q1',
  `effective_year` int(4) DEFAULT 2026
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 转存表中的数据 `banks`
--

INSERT INTO `banks` (`bank_id`, `bank_name`, `interest_rate`, `effective_quarter`, `effective_year`) VALUES
(1, 'Maybank', '3.35', 'Q1', 2026),
(2, 'CIMB Bank', '3.40', 'Q1', 2026),
(3, 'Public Bank', '3.38', 'Q1', 2026),
(4, 'RHB Bank', '3.45', 'Q1', 2026),
(5, 'Hong Leong Bank', '3.39', 'Q1', 2026),
(6, 'AmBank', '3.50', 'Q1', 2026),
(7, 'UOB Bank', '3.42', 'Q1', 2026),
(8, 'Affin Bank', '3.75', 'Q1', 2026);

-- --------------------------------------------------------

--
-- 表的结构 `customers`
--

CREATE TABLE `customers` (
  `customer_id` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `phone_number` varchar(20) NOT NULL,
  `marital_status` enum('SINGLE','MARRIED') NOT NULL,
  `dependents_count` int(11) NOT NULL DEFAULT 0,
  `occupation` varchar(150) NOT NULL,
  `monthly_income` decimal(10,2) NOT NULL,
  `profile_image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 转存表中的数据 `customers`
--

INSERT INTO `customers` (`customer_id`, `full_name`, `phone_number`, `marital_status`, `dependents_count`, `occupation`, `monthly_income`, `profile_image`) VALUES
(33, 'Amiruddin Bin Bakar', '011-1234567', 'SINGLE', 0, 'Teacher', '3500.00', NULL),
(34, 'Lim Wei Jian', '012-1234567', 'MARRIED', 2, 'Engineer', '8500.00', NULL),
(35, 'Suresh a/l Ramasamy', '013-1234567', 'SINGLE', 0, 'Clerk', '2500.00', NULL),
(36, 'Nurul Hidayah', '014-1234567', 'MARRIED', 3, 'Business Owner', '6000.00', NULL),
(37, 'Tan Siew Min', '015-1234567', 'SINGLE', 1, 'Nurse', '4000.00', NULL),
(38, 'Karthik a/l Murugan', '016-1234567', 'MARRIED', 1, 'Technician', '3200.00', NULL),
(39, 'Mohd Faiz', '016-7654321', 'SINGLE', 0, 'Designer', '4500.00', NULL),
(40, 'Rachel Wong', '017-1234567', 'MARRIED', 4, 'Manager', '9500.00', NULL),
(41, 'Ahmad Zaki', '017-7654321', 'SINGLE', 0, 'Executive', '5000.00', NULL),
(43, 'Goh Chee Keong', '018-7654321', 'SINGLE', 0, 'Chef', '3000.00', NULL),
(44, 'Priya a/p Subramaniam', '019-1234567', 'MARRIED', 1, 'IT Specialist', '6800.00', NULL),
(45, 'Hafizuddin', '019-7654321', 'SINGLE', 0, 'Sales', '3800.00', NULL),
(46, 'Chong Mei Ling', '011-9876543', 'MARRIED', 2, 'Doctor', '12000.00', NULL),
(47, 'testcus', '012-9876543', 'SINGLE', 0, 'Student', '11000.00', '/storage/profile_images/customer_47_5993a459f668b573.jpg'),
(49, 'test', '1111', 'SINGLE', 0, 'Student', '0.00', NULL),
(50, 'KHAIRUNNISA KAMAL', '017283783893', 'SINGLE', 1, 'teacher', '4500.00', NULL),
(51, 'WIN', '017', 'SINGLE', 0, 'Student', '8000.00', NULL),
(54, '12', '12', '', 0, 'student', '0.00', NULL),
(57, '1234', '1234', 'SINGLE', 0, 'student', '0.00', NULL),
(59, 'kahjun', '999', 'SINGLE', 0, 'student', '5.00', NULL);

-- --------------------------------------------------------

--
-- 表的结构 `documents`
--

CREATE TABLE `documents` (
  `document_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `related_to_type` enum('APPLICATION','APPOINTMENT') NOT NULL,
  `related_to_id` int(11) NOT NULL,
  `document_type` enum('PAYSLIP_SUMMARY','EPF_STATEMENT_SUMMARY','INCOME_DECLARATION') NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `uploaded_at` datetime DEFAULT current_timestamp(),
  `is_purged` tinyint(1) NOT NULL DEFAULT 0,
  `purged_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 转存表中的数据 `documents`
--

INSERT INTO `documents` (`document_id`, `customer_id`, `related_to_type`, `related_to_id`, `document_type`, `file_path`, `uploaded_at`, `is_purged`, `purged_at`) VALUES
(1, 38, 'APPLICATION', 1, 'INCOME_DECLARATION', '/uploads/app_1_1715000000.pdf', '2026-05-07 15:09:47', 0, NULL),
(2, 39, 'APPLICATION', 2, 'EPF_STATEMENT_SUMMARY', '/uploads/app_2_1715000001.pdf', '2026-05-07 15:09:47', 0, NULL),
(3, 40, 'APPLICATION', 3, 'INCOME_DECLARATION', '/uploads/app_3_1715000002.pdf', '2026-05-07 15:09:47', 0, NULL),
(4, 41, 'APPLICATION', 4, 'EPF_STATEMENT_SUMMARY', '/uploads/app_4_1715000003.pdf', '2026-05-07 15:09:47', 0, NULL),
(6, 33, 'APPOINTMENT', 1, 'PAYSLIP_SUMMARY', '/uploads/appt_1_1715000005.pdf', '2026-05-07 15:09:47', 0, NULL),
(7, 34, 'APPOINTMENT', 2, 'PAYSLIP_SUMMARY', '/uploads/appt_2_1715000006.pdf', '2026-05-07 15:09:47', 0, NULL),
(8, 35, 'APPOINTMENT', 3, 'PAYSLIP_SUMMARY', '/uploads/appt_3_1715000007.pdf', '2026-05-07 15:09:47', 0, NULL),
(10, 51, 'APPLICATION', 6, 'EPF_STATEMENT_SUMMARY', '/storage/docs/app_6_1778958058.pdf', '2026-05-16 12:00:58', 0, NULL),
(11, 51, 'APPOINTMENT', 16, 'PAYSLIP_SUMMARY', '/storage/docs/appt_16_1779084239.pdf', '2026-05-17 23:03:59', 0, NULL),
(12, 51, 'APPOINTMENT', 18, 'PAYSLIP_SUMMARY', '/storage/docs/appt_18_1779084779.pdf', '2026-05-17 23:12:58', 0, NULL),
(13, 47, 'APPOINTMENT', 19, 'PAYSLIP_SUMMARY', '/storage/docs/appt_19_1779087060.pdf', '2026-05-17 23:51:00', 0, NULL),
(14, 51, 'APPOINTMENT', 20, 'PAYSLIP_SUMMARY', '/storage/docs/appt_20_1779113506.pdf', '2026-05-18 07:11:46', 0, NULL),
(15, 47, 'APPOINTMENT', 21, 'PAYSLIP_SUMMARY', 'storage/docs/appt_21_1779135392.pdf', '2026-05-18 13:16:31', 0, NULL),
(17, 47, 'APPOINTMENT', 24, 'PAYSLIP_SUMMARY', 'storage/docs/appt_24_1779135735.pdf', '2026-05-18 13:22:14', 0, NULL),
(22, 51, 'APPOINTMENT', 28, 'PAYSLIP_SUMMARY', 'storage/docs/appt_28_1779177344.pdf', '2026-05-19 00:55:44', 0, NULL),
(24, 51, 'APPLICATION', 8, 'EPF_STATEMENT_SUMMARY', 'storage/docs/app_8_1779373110.pdf', '2026-05-21 07:18:30', 0, NULL),
(25, 51, 'APPOINTMENT', 29, 'PAYSLIP_SUMMARY', 'storage/docs/appt_29_1779457953.pdf', '2026-05-22 06:52:33', 0, NULL),
(26, 51, 'APPLICATION', 7, 'EPF_STATEMENT_SUMMARY', 'storage/docs/app_7_1779458206.pdf', '2026-05-22 06:56:47', 0, NULL),
(28, 51, 'APPOINTMENT', 10, 'PAYSLIP_SUMMARY', 'storage/docs/appt_10_1779458397.pdf', '2026-05-22 06:59:57', 1, '2026-05-26 08:33:35'),
(29, 47, 'APPOINTMENT', 23, 'PAYSLIP_SUMMARY', 'storage/docs/appt_23_1779458475.pdf', '2026-05-22 07:01:14', 0, NULL),
(31, 51, 'APPLICATION', 10, 'EPF_STATEMENT_SUMMARY', 'storage/docs/app_10_1779465536.pdf', '2026-05-22 08:58:55', 0, NULL),
(32, 51, 'APPOINTMENT', 31, 'PAYSLIP_SUMMARY', 'storage/docs/appt_31_1779809856.pdf', '2026-05-26 08:37:36', 1, '2026-05-26 08:39:02');

-- --------------------------------------------------------

--
-- 表的结构 `properties`
--

CREATE TABLE `properties` (
  `property_id` int(11) NOT NULL,
  `property_code` varchar(20) NOT NULL,
  `project_name` varchar(255) NOT NULL,
  `state` varchar(100) NOT NULL,
  `property_type` enum('AFFORDABLE','TERRACE','BUNGALOW','COMMERCIAL','APARTMENT') NOT NULL,
  `price` decimal(12,2) NOT NULL,
  `total_units` int(11) NOT NULL,
  `built_up_sqft` int(11) NOT NULL,
  `income_limit_rm` decimal(10,2) DEFAULT NULL,
  `status` enum('ACTIVE','SOLD_OUT','ARCHIVED') NOT NULL DEFAULT 'ACTIVE',
  `image_filename` varchar(255) NOT NULL,
  `image_search_keyword` varchar(255) NOT NULL,
  `is_affordable` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 转存表中的数据 `properties`
--

INSERT INTO `properties` (`property_id`, `property_code`, `project_name`, `state`, `property_type`, `price`, `total_units`, `built_up_sqft`, `income_limit_rm`, `status`, `image_filename`, `image_search_keyword`, `is_affordable`) VALUES
(1, 'A-BG-IP001', 'Villa Ipoh Sanctuary', 'Perak', 'BUNGALOW', '1134000.00', 35, 4500, NULL, 'ACTIVE', 'A-BG-IP001.jpg', 'luxury bungalow exterior ipoh perak green landscape realistic', 0),
(2, 'A-BG-TP002', 'Taman Taiping Grandeur', 'Perak', 'BUNGALOW', '1008000.00', 40, 4200, NULL, 'ACTIVE', 'A-BG-TP002.jpg', 'modern detached house taiping tropical sunny real estate', 0),
(3, 'A-BG-LM003', 'Lumut Coastal Haven', 'Perak', 'BUNGALOW', '1260000.00', 30, 5100, NULL, 'ACTIVE', 'A-BG-LM003.jpg', 'coastal bungalow architecture lumut perak ocean view', 0),
(4, 'A-TR-IP001', 'Laman Ipoh Harmoni', 'Perak', 'TERRACE', '315000.00', 50, 1800, NULL, 'ACTIVE', 'A-TR-IP001.jpg', 'modern double storey terrace house ipoh perak residential', 0),
(5, 'A-TR-TP002', 'Taman Taiping Indah', 'Perak', 'TERRACE', '264600.00', 45, 1650, '7000.00', 'ACTIVE', 'A-TR-TP002.jpg', 'asian terrace house neighbourhood taiping family home', 1),
(6, 'A-TR-LM003', 'Residensi Lumut Bayu', 'Perak', 'TERRACE', '283500.00', 48, 1700, NULL, 'ACTIVE', 'A-TR-LM003.jpg', 'terrace homes lumut contemporary facade bright', 0),
(7, 'A-AP-IP001', 'Pangsapuri Kinta Sentral', 'Perak', 'APARTMENT', '201600.00', 120, 950, '5600.00', 'ACTIVE', 'A-AP-IP001.jpg', 'modern high rise apartment exterior ipoh city sky', 1),
(8, 'A-AP-TP002', 'Residensi Taiping Mewah', 'Perak', 'APARTMENT', '176400.00', 150, 850, '3500.00', 'ACTIVE', 'A-AP-TP002.jpg', 'affordable apartment building taiping real estate', 1),
(9, 'A-AP-LM003', 'Lumut Oceanview Suites', 'Perak', 'APARTMENT', '220500.00', 100, 1100, NULL, 'ACTIVE', 'A-AP-LM003.jpg', 'serviced apartment facade lumut contemporary living', 0),
(10, 'A-CM-IP001', 'Ipoh Trade Square', 'Perak', 'COMMERCIAL', '819000.00', 15, 2800, NULL, 'ACTIVE', 'A-CM-IP001.jpg', 'modern commercial shop lot ipoh perak bustling street', 0),
(11, 'A-CM-TP002', 'Pusat Perniagaan Taiping', 'Perak', 'COMMERCIAL', '693000.00', 20, 2500, NULL, 'ACTIVE', 'A-CM-TP002.jpg', 'shop office commercial building exterior taiping', 0),
(12, 'A-CM-LM003', 'Lumut Maritime Boulevard', 'Perak', 'COMMERCIAL', '945000.00', 12, 3200, NULL, 'ACTIVE', 'A-CM-LM003.jpg', 'premium retail shop lots lumut perak architecture', 0),
(13, 'B-BG-PJ001', 'Villa Petaling Jaya Elite', 'Selangor', 'BUNGALOW', '2835000.00', 30, 5500, NULL, 'ACTIVE', 'B-BG-PJ001.jpg', 'ultra luxury bungalow petaling jaya modern architectural design', 0),
(14, 'B-BG-SJ002', 'Subang Jaya Prestige Enclave', 'Selangor', 'BUNGALOW', '2520000.00', 35, 5200, NULL, 'ACTIVE', 'B-BG-SJ002.jpg', 'luxury detached house exterior subang jaya sunny day', 0),
(15, 'B-BG-SA003', 'Shah Alam Greenview Manor', 'Selangor', 'BUNGALOW', '2205000.00', 40, 4800, NULL, 'ACTIVE', 'B-BG-SA003.jpg', 'premium bungalow shah alam residential area realistic', 0),
(16, 'B-TR-PJ001', 'Taman Tropika Utama PJ', 'Selangor', 'TERRACE', '567000.00', 45, 2000, NULL, 'ACTIVE', 'B-TR-PJ001.jpg', 'modern terrace house petaling jaya exterior design', 0),
(17, 'B-TR-SJ002', 'Residensi Subang Harmoni', 'Selangor', 'TERRACE', '535500.00', 50, 1900, NULL, 'ACTIVE', 'B-TR-SJ002.jpg', 'contemporary double storey terrace subang jaya family', 0),
(18, 'B-TR-SA003', 'Laman Shah Alam Indah', 'Selangor', 'TERRACE', '472500.00', 48, 1850, NULL, 'ACTIVE', 'B-TR-SA003.jpg', 'terrace homes shah alam bright sunny day facade', 0),
(19, 'B-AP-PJ001', 'PJ Sentral Suites', 'Selangor', 'APARTMENT', '378000.00', 150, 1100, NULL, 'ACTIVE', 'B-AP-PJ001.jpg', 'luxury high rise apartment petaling jaya skyline', 0),
(20, 'B-AP-SJ002', 'Pangsapuri Subang Mewah', 'Selangor', 'APARTMENT', '315000.00', 120, 950, '7000.00', 'ACTIVE', 'B-AP-SJ002.jpg', 'modern apartment complex exterior subang jaya real estate', 1),
(21, 'B-AP-SA003', 'Residensi Shah Alam Vista', 'Selangor', 'APARTMENT', '283500.00', 100, 900, '5600.00', 'ACTIVE', 'B-AP-SA003.jpg', 'affordable serviced apartment building shah alam', 1),
(22, 'B-CM-PJ001', 'Petaling Jaya Business Hub', 'Selangor', 'COMMERCIAL', '1575000.00', 12, 3000, NULL, 'ACTIVE', 'B-CM-PJ001.jpg', 'premium commercial shop lot building petaling jaya', 0),
(23, 'B-CM-SJ002', 'Subang Avenue Commercial', 'Selangor', 'COMMERCIAL', '1386000.00', 15, 2800, NULL, 'ACTIVE', 'B-CM-SJ002.jpg', 'busy commercial street subang jaya shop offices', 0),
(24, 'B-CM-SA003', 'Pusat Komersial Shah Alam', 'Selangor', 'COMMERCIAL', '1260000.00', 20, 2500, NULL, 'ACTIVE', 'B-CM-SA003.jpg', 'modern commercial center shah alam exterior retail', 0),
(25, 'C-BG-KU001', 'Kuantan Coastal Villa', 'Pahang', 'BUNGALOW', '945000.00', 35, 4500, NULL, 'ACTIVE', 'C-BG-KU001.jpg', 'luxury bungalow exterior kuantan beachside modern', 0),
(26, 'C-BG-BT002', 'Bentong Highland Retreat', 'Pahang', 'BUNGALOW', '1071000.00', 30, 4800, NULL, 'ACTIVE', 'C-BG-BT002.jpg', 'detached house highland bentong nature modern architecture', 0),
(27, 'C-BG-CH003', 'Cameron Grandeur Estate', 'Pahang', 'BUNGALOW', '1386000.00', 30, 5000, NULL, 'ACTIVE', 'C-BG-CH003.jpg', 'luxury mountain bungalow cameron highlands misty morning', 0),
(28, 'C-TR-KU001', 'Taman Kuantan Bayu', 'Pahang', 'TERRACE', '252000.00', 45, 1600, NULL, 'ACTIVE', 'C-TR-KU001.jpg', 'double storey terrace kuantan tropical neighborhood', 0),
(29, 'C-TR-BT002', 'Laman Bentong Indah', 'Pahang', 'TERRACE', '239400.00', 50, 1550, '7000.00', 'ACTIVE', 'C-TR-BT002.jpg', 'modern terrace houses bentong green surroundings', 1),
(30, 'C-TR-CH003', 'Residensi Cameron Hills', 'Pahang', 'TERRACE', '315000.00', 40, 1800, NULL, 'ACTIVE', 'C-TR-CH003.jpg', 'premium terrace house cameron highlands architecture', 0),
(31, 'C-AP-KU001', 'Kuantan Seaview Suites', 'Pahang', 'APARTMENT', '189000.00', 100, 950, '5600.00', 'ACTIVE', 'C-AP-KU001.jpg', 'high rise apartment kuantan seaview modern', 1),
(32, 'C-AP-BT002', 'Pangsapuri Bentong Sentral', 'Pahang', 'APARTMENT', '157500.00', 120, 850, '3500.00', 'ACTIVE', 'C-AP-BT002.jpg', 'affordable apartment exterior bentong real estate', 1),
(33, 'C-AP-CH003', 'Cameron Alpine Residences', 'Pahang', 'APARTMENT', '252000.00', 90, 1050, NULL, 'ACTIVE', 'C-AP-CH003.jpg', 'luxury apartment building cameron highlands modern', 0),
(34, 'C-CM-KU001', 'Kuantan Trade Square', 'Pahang', 'COMMERCIAL', '630000.00', 18, 2400, NULL, 'ACTIVE', 'C-CM-KU001.jpg', 'commercial shop lots kuantan busy retail center', 0),
(35, 'C-CM-BT002', 'Bentong Business Boulevard', 'Pahang', 'COMMERCIAL', '567000.00', 15, 2200, NULL, 'ACTIVE', 'C-CM-BT002.jpg', 'modern shop office building bentong pahang', 0),
(36, 'C-CM-CH003', 'Cameron Commercial Hub', 'Pahang', 'COMMERCIAL', '819000.00', 10, 2800, NULL, 'ACTIVE', 'C-CM-CH003.jpg', 'tourist commercial retail space cameron highlands', 0),
(37, 'D-BG-KB001', 'Villa Kota Bharu Mutiara', 'Kelantan', 'BUNGALOW', '756000.00', 40, 4200, NULL, 'ACTIVE', 'D-BG-KB001.jpg', 'modern luxury bungalow kota bharu architecture', 0),
(38, 'D-BG-PM002', 'Pasir Mas Prestige Enclave', 'Kelantan', 'BUNGALOW', '630000.00', 45, 4000, NULL, 'ACTIVE', 'D-BG-PM002.jpg', 'detached house exterior pasir mas kelantan', 0),
(39, 'D-BG-TM003', 'Tanah Merah Greenview', 'Kelantan', 'BUNGALOW', '598500.00', 50, 4100, NULL, 'ACTIVE', 'D-BG-TM003.jpg', 'bungalow home tanah merah tropical garden', 0),
(40, 'D-TR-KB001', 'Taman Kota Bharu Sentosa', 'Kelantan', 'TERRACE', '201600.00', 50, 1500, NULL, 'ACTIVE', 'D-TR-KB001.jpg', 'malaysian terrace house kota bharu residential', 0),
(41, 'D-TR-PM002', 'Laman Pasir Mas Harmoni', 'Kelantan', 'TERRACE', '176400.00', 45, 1450, '5600.00', 'ACTIVE', 'D-TR-PM002.jpg', 'double storey terrace pasir mas affordable', 1),
(42, 'D-TR-TM003', 'Residensi Tanah Merah', 'Kelantan', 'TERRACE', '163800.00', 50, 1400, '3500.00', 'ACTIVE', 'D-TR-TM003.jpg', 'terrace neighborhood tanah merah kelantan bright', 1),
(43, 'D-AP-KB001', 'Residensi Mutiara KB', 'Kelantan', 'APARTMENT', '151200.00', 120, 850, '3500.00', 'ACTIVE', 'D-AP-KB001.jpg', 'apartment building kota bharu city skyline', 1),
(44, 'D-AP-PM002', 'Pangsapuri Pasir Mas', 'Kelantan', 'APARTMENT', '126000.00', 100, 800, '3500.00', 'ACTIVE', 'D-AP-PM002.jpg', 'affordable apartment complex pasir mas kelantan', 1),
(45, 'D-AP-TM003', 'Tanah Merah City Suites', 'Kelantan', 'APARTMENT', '138600.00', 110, 820, '3500.00', 'ACTIVE', 'D-AP-TM003.jpg', 'modern apartment exterior tanah merah residential', 1),
(46, 'D-CM-KB001', 'Pusat Perniagaan Kota Bharu', 'Kelantan', 'COMMERCIAL', '504000.00', 20, 2200, NULL, 'ACTIVE', 'D-CM-KB001.jpg', 'commercial shop lot kota bharu bustling area', 0),
(47, 'D-CM-PM002', 'Pasir Mas Trade Hub', 'Kelantan', 'COMMERCIAL', '441000.00', 15, 2000, NULL, 'ACTIVE', 'D-CM-PM002.jpg', 'shop office retail center pasir mas exterior', 0),
(48, 'D-CM-TM003', 'Tanah Merah Commercial Boulevard', 'Kelantan', 'COMMERCIAL', '409500.00', 15, 2100, NULL, 'ACTIVE', 'D-CM-TM003.jpg', 'modern retail shop lot tanah merah architecture', 0),
(49, 'J-BG-JB001', 'Villa Johor Bahru Grandeur', 'Johor', 'BUNGALOW', '2205000.00', 30, 5200, NULL, 'ACTIVE', 'J-BG-JB001.jpg', 'luxury bungalow exterior johor bahru modern design', 0),
(50, 'J-BG-IP002', 'Iskandar Puteri Prestige', 'Johor', 'BUNGALOW', '2520000.00', 35, 5500, NULL, 'ACTIVE', 'J-BG-IP002.jpg', 'ultra luxury detached house iskandar puteri sunny', 0),
(51, 'J-BG-SR003', 'Serom Estate Haven', 'Johor', 'BUNGALOW', '1260000.00', 40, 4800, NULL, 'ACTIVE', 'J-BG-SR003.jpg', 'premium bungalow serom johor real estate', 0),
(52, 'J-TR-JB001', 'Taman Johor Bahru Indah', 'Johor', 'TERRACE', '504000.00', 50, 1900, NULL, 'ACTIVE', 'J-TR-JB001.jpg', 'modern double storey terrace johor bahru neighborhood', 0),
(53, 'J-TR-IP002', 'Residensi Iskandar Harmoni', 'Johor', 'TERRACE', '567000.00', 45, 2000, NULL, 'ACTIVE', 'J-TR-IP002.jpg', 'luxury terrace house iskandar puteri exterior', 0),
(54, 'J-TR-SR003', 'Laman Serom Utama', 'Johor', 'TERRACE', '315000.00', 50, 1650, '6000.00', 'ACTIVE', 'J-TR-SR003.jpg', 'terrace homes serom johor bright day', 1),
(55, 'J-AP-JB001', 'JB Sentral City Suites', 'Johor', 'APARTMENT', '346500.00', 150, 1050, NULL, 'ACTIVE', 'J-AP-JB001.jpg', 'high rise luxury apartment johor bahru skyline', 0),
(56, 'J-AP-IP002', 'Iskandar Waterfront Residences', 'Johor', 'APARTMENT', '378000.00', 120, 1150, NULL, 'ACTIVE', 'J-AP-IP002.jpg', 'premium serviced apartment iskandar puteri modern', 0),
(57, 'J-AP-SR003', 'Pangsapuri Serom Mewah', 'Johor', 'APARTMENT', '189000.00', 100, 900, '5600.00', 'ACTIVE', 'J-AP-SR003.jpg', 'affordable apartment building serom johor', 1),
(58, 'J-CM-JB001', 'Johor Bahru Commercial Square', 'Johor', 'COMMERCIAL', '1449000.00', 20, 2800, NULL, 'ACTIVE', 'J-CM-JB001.jpg', 'busy commercial shop lot johor bahru street', 0),
(59, 'J-CM-IP002', 'Iskandar Trade Boulevard', 'Johor', 'COMMERCIAL', '1575000.00', 15, 3000, NULL, 'ACTIVE', 'J-CM-IP002.jpg', 'modern commercial building iskandar puteri architecture', 0),
(60, 'J-CM-SR003', 'Pusat Perniagaan Serom', 'Johor', 'COMMERCIAL', '693000.00', 12, 2400, NULL, 'ACTIVE', 'J-CM-SR003.jpg', 'shop office retail center serom johor', 0),
(61, 'K-BG-AS001', 'Villa Alor Setar Mutiara', 'Kedah', 'BUNGALOW', '882000.00', 35, 4500, NULL, 'ACTIVE', 'K-BG-AS001.jpg', 'luxury bungalow exterior alor setar modern facade', 0),
(62, 'K-BG-SP002', 'Sungai Petani Grandeur', 'Kedah', 'BUNGALOW', '819000.00', 40, 4200, NULL, 'ACTIVE', 'K-BG-SP002.jpg', 'detached house sungai petani tropical architecture', 0),
(63, 'K-BG-KL003', 'Kulim Elite Haven', 'Kedah', 'BUNGALOW', '945000.00', 30, 4600, NULL, 'ACTIVE', 'K-BG-KL003.jpg', 'premium bungalow kulim kedah residential', 0),
(64, 'K-TR-AS001', 'Taman Alor Setar Indah', 'Kedah', 'TERRACE', '252000.00', 50, 1600, NULL, 'ACTIVE', 'K-TR-AS001.jpg', 'double storey terrace alor setar bright sunny', 0),
(65, 'K-TR-SP002', 'Laman Sungai Petani', 'Kedah', 'TERRACE', '239400.00', 48, 1550, '7000.00', 'ACTIVE', 'K-TR-SP002.jpg', 'terrace house sungai petani neighborhood malaysian', 1),
(66, 'K-TR-KL003', 'Residensi Kulim Harmoni', 'Kedah', 'TERRACE', '264600.00', 45, 1700, NULL, 'ACTIVE', 'K-TR-KL003.jpg', 'modern terrace homes kulim exterior', 0),
(67, 'K-AP-AS001', 'Alor Setar Sentral Suites', 'Kedah', 'APARTMENT', '176400.00', 100, 900, '5600.00', 'ACTIVE', 'K-AP-AS001.jpg', 'apartment building alor setar city modern', 1),
(68, 'K-AP-SP002', 'Pangsapuri Mutiara SP', 'Kedah', 'APARTMENT', '157500.00', 120, 850, '3500.00', 'ACTIVE', 'K-AP-SP002.jpg', 'affordable apartment complex sungai petani', 1),
(69, 'K-AP-KL003', 'Kulim Heights Residences', 'Kedah', 'APARTMENT', '189000.00', 110, 950, '5600.00', 'ACTIVE', 'K-AP-KL003.jpg', 'high rise serviced apartment kulim kedah', 1),
(70, 'K-CM-AS001', 'Pusat Komersial Alor Setar', 'Kedah', 'COMMERCIAL', '567000.00', 15, 2400, NULL, 'ACTIVE', 'K-CM-AS001.jpg', 'commercial shop lot alor setar retail', 0),
(71, 'K-CM-SP002', 'Sungai Petani Business Avenue', 'Kedah', 'COMMERCIAL', '535500.00', 20, 2200, NULL, 'ACTIVE', 'K-CM-SP002.jpg', 'shop office building sungai petani exterior', 0),
(72, 'K-CM-KL003', 'Kulim Trade Center', 'Kedah', 'COMMERCIAL', '630000.00', 15, 2500, NULL, 'ACTIVE', 'K-CM-KL003.jpg', 'modern commercial space kulim kedah', 0),
(73, 'L-BG-VC001', 'Villa Victoria Offshore', 'Labuan', 'BUNGALOW', '1134000.00', 30, 4800, NULL, 'ACTIVE', 'L-BG-VC001.jpg', 'luxury bungalow victoria labuan ocean breeze', 0),
(74, 'L-BG-LL002', 'Layangan Coastal Retreat', 'Labuan', 'BUNGALOW', '1008000.00', 35, 4500, NULL, 'ACTIVE', 'L-BG-LL002.jpg', 'detached house layangan labuan tropical', 0),
(75, 'L-BG-KS003', 'Kiamsam Prestige Estate', 'Labuan', 'BUNGALOW', '945000.00', 40, 4200, NULL, 'ACTIVE', 'L-BG-KS003.jpg', 'modern bungalow kiamsam labuan architecture', 0),
(76, 'L-TR-VC001', 'Taman Victoria Utama', 'Labuan', 'TERRACE', '283500.00', 45, 1800, NULL, 'ACTIVE', 'L-TR-VC001.jpg', 'double storey terrace victoria labuan exterior', 0),
(77, 'L-TR-LL002', 'Laman Layangan Indah', 'Labuan', 'TERRACE', '252000.00', 50, 1600, '7000.00', 'ACTIVE', 'L-TR-LL002.jpg', 'terrace homes layangan labuan sunny', 1),
(78, 'L-TR-KS003', 'Residensi Kiamsam Harmoni', 'Labuan', 'TERRACE', '239400.00', 45, 1550, '7000.00', 'ACTIVE', 'L-TR-KS003.jpg', 'malaysian terrace house kiamsam labuan', 1),
(79, 'L-AP-VC001', 'Victoria Oceanview Suites', 'Labuan', 'APARTMENT', '220500.00', 100, 1000, NULL, 'ACTIVE', 'L-AP-VC001.jpg', 'high rise apartment victoria labuan ocean view', 0),
(80, 'L-AP-LL002', 'Pangsapuri Layangan Bayu', 'Labuan', 'APARTMENT', '176400.00', 120, 850, '3500.00', 'ACTIVE', 'L-AP-LL002.jpg', 'affordable apartment labuan layangan', 1),
(81, 'L-AP-KS003', 'Kiamsam Sentral Residences', 'Labuan', 'APARTMENT', '189000.00', 110, 900, '5600.00', 'ACTIVE', 'L-AP-KS003.jpg', 'serviced apartment kiamsam labuan modern', 1),
(82, 'L-CM-VC001', 'Victoria Financial Square', 'Labuan', 'COMMERCIAL', '819000.00', 15, 2600, NULL, 'ACTIVE', 'L-CM-VC001.jpg', 'commercial building victoria labuan retail', 0),
(83, 'L-CM-LL002', 'Layangan Trade Boulevard', 'Labuan', 'COMMERCIAL', '630000.00', 18, 2200, NULL, 'ACTIVE', 'L-CM-LL002.jpg', 'shop office layangan labuan exterior', 0),
(84, 'L-CM-KS003', 'Pusat Perniagaan Kiamsam', 'Labuan', 'COMMERCIAL', '598500.00', 20, 2000, NULL, 'ACTIVE', 'L-CM-KS003.jpg', 'commercial shop lot kiamsam labuan', 0),
(85, 'M-BG-MC001', 'Villa Melaka Heritage', 'Melaka', 'BUNGALOW', '1260000.00', 35, 5000, NULL, 'ACTIVE', 'M-BG-MC001.jpg', 'luxury bungalow melaka city modern traditional architecture', 0),
(86, 'M-BG-AK002', 'Ayer Keroh Grandeur', 'Melaka', 'BUNGALOW', '1134000.00', 40, 4800, NULL, 'ACTIVE', 'M-BG-AK002.jpg', 'detached house ayer keroh melaka sunny day', 0),
(87, 'M-BG-AG003', 'Alor Gajah Prestige Haven', 'Melaka', 'BUNGALOW', '945000.00', 45, 4500, NULL, 'ACTIVE', 'M-BG-AG003.jpg', 'premium bungalow alor gajah real estate', 0),
(88, 'M-TR-MC001', 'Taman Melaka Sentral', 'Melaka', 'TERRACE', '315000.00', 50, 1800, NULL, 'ACTIVE', 'M-TR-MC001.jpg', 'modern double storey terrace melaka city', 0),
(89, 'M-TR-AK002', 'Laman Ayer Keroh Indah', 'Melaka', 'TERRACE', '283500.00', 45, 1700, '7000.00', 'ACTIVE', 'M-TR-AK002.jpg', 'terrace house ayer keroh neighborhood', 1),
(90, 'M-TR-AG003', 'Residensi Alor Gajah', 'Melaka', 'TERRACE', '252000.00', 50, 1600, '5600.00', 'ACTIVE', 'M-TR-AG003.jpg', 'terrace homes alor gajah melaka bright', 1),
(91, 'M-AP-MC001', 'Melaka Riverview Suites', 'Melaka', 'APARTMENT', '220500.00', 120, 950, '5600.00', 'ACTIVE', 'M-AP-MC001.jpg', 'high rise apartment melaka river modern', 1),
(92, 'M-AP-AK002', 'Pangsapuri Ayer Keroh Mewah', 'Melaka', 'APARTMENT', '189000.00', 150, 850, '3500.00', 'ACTIVE', 'M-AP-AK002.jpg', 'affordable apartment building ayer keroh', 1),
(93, 'M-AP-AG003', 'Alor Gajah Heights', 'Melaka', 'APARTMENT', '176400.00', 100, 800, '3500.00', 'ACTIVE', 'M-AP-AG003.jpg', 'apartment complex alor gajah melaka', 1),
(94, 'M-CM-MC001', 'Melaka City Trade Centre', 'Melaka', 'COMMERCIAL', '945000.00', 15, 2600, NULL, 'ACTIVE', 'M-CM-MC001.jpg', 'commercial shop lot melaka city bustling', 0),
(95, 'M-CM-AK002', 'Ayer Keroh Business Hub', 'Melaka', 'COMMERCIAL', '819000.00', 20, 2400, NULL, 'ACTIVE', 'M-CM-AK002.jpg', 'shop office building ayer keroh melaka', 0),
(96, 'M-CM-AG003', 'Pusat Komersial Alor Gajah', 'Melaka', 'COMMERCIAL', '693000.00', 18, 2200, NULL, 'ACTIVE', 'M-CM-AG003.jpg', 'retail commercial space alor gajah', 0),
(97, 'N-BG-SR001', 'Villa Seremban Elite', 'Negeri Sembilan', 'BUNGALOW', '1260000.00', 35, 5000, NULL, 'ACTIVE', 'N-BG-SR001.jpg', 'luxury bungalow seremban modern facade architecture', 0),
(98, 'N-BG-PD002', 'Port Dickson Coastal Villa', 'Negeri Sembilan', 'BUNGALOW', '1386000.00', 30, 5200, NULL, 'ACTIVE', 'N-BG-PD002.jpg', 'detached house port dickson ocean view luxury', 0),
(99, 'N-BG-NL003', 'Nilai Grandeur Estate', 'Negeri Sembilan', 'BUNGALOW', '1134000.00', 40, 4800, NULL, 'ACTIVE', 'N-BG-NL003.jpg', 'premium bungalow nilai negeri sembilan sunny', 0),
(100, 'N-TR-SR001', 'Taman Seremban Indah', 'Negeri Sembilan', 'TERRACE', '315000.00', 50, 1800, NULL, 'ACTIVE', 'N-TR-SR001.jpg', 'modern double storey terrace seremban neighborhood', 0),
(101, 'N-TR-PD002', 'Laman Port Dickson Harmoni', 'Negeri Sembilan', 'TERRACE', '283500.00', 45, 1600, '7000.00', 'ACTIVE', 'N-TR-PD002.jpg', 'terrace house port dickson sunny day', 1),
(102, 'N-TR-NL003', 'Residensi Nilai Utama', 'Negeri Sembilan', 'TERRACE', '302400.00', 50, 1750, NULL, 'ACTIVE', 'N-TR-NL003.jpg', 'terrace homes nilai contemporary exterior', 0),
(103, 'N-AP-SR001', 'Seremban Sentral Residences', 'Negeri Sembilan', 'APARTMENT', '220500.00', 120, 950, '5600.00', 'ACTIVE', 'N-AP-SR001.jpg', 'high rise apartment seremban skyline modern', 1),
(104, 'N-AP-PD002', 'PD Seaview Suites', 'Negeri Sembilan', 'APARTMENT', '252000.00', 100, 1000, NULL, 'ACTIVE', 'N-AP-PD002.jpg', 'serviced apartment port dickson luxury', 0),
(105, 'N-AP-NL003', 'Pangsapuri Nilai Mewah', 'Negeri Sembilan', 'APARTMENT', '176400.00', 150, 850, '3500.00', 'ACTIVE', 'N-AP-NL003.jpg', 'affordable apartment building nilai', 1),
(106, 'N-CM-SR001', 'Seremban Business Square', 'Negeri Sembilan', 'COMMERCIAL', '945000.00', 15, 2600, NULL, 'ACTIVE', 'N-CM-SR001.jpg', 'commercial shop lot seremban retail center', 0),
(107, 'N-CM-PD002', 'Port Dickson Trade Boulevard', 'Negeri Sembilan', 'COMMERCIAL', '819000.00', 12, 2400, NULL, 'ACTIVE', 'N-CM-PD002.jpg', 'shop office port dickson commercial', 0),
(108, 'N-CM-NL003', 'Pusat Perniagaan Nilai', 'Negeri Sembilan', 'COMMERCIAL', '882000.00', 20, 2500, NULL, 'ACTIVE', 'N-CM-NL003.jpg', 'modern commercial space nilai retail', 0),
(109, 'P-BG-GT001', 'Villa Georgetown Heritage', 'Penang', 'BUNGALOW', '3150000.00', 30, 5500, NULL, 'ACTIVE', 'P-BG-GT001.jpg', 'ultra luxury bungalow georgetown penang historical modern', 0),
(110, 'P-BG-BL002', 'Bayan Lepas Coastal Haven', 'Penang', 'BUNGALOW', '2520000.00', 35, 5000, NULL, 'ACTIVE', 'P-BG-BL002.jpg', 'luxury detached house bayan lepas penang', 0),
(111, 'P-BG-BW003', 'Butterworth Elite Estate', 'Penang', 'BUNGALOW', '1575000.00', 40, 4800, NULL, 'ACTIVE', 'P-BG-BW003.jpg', 'premium bungalow butterworth mainland penang', 0),
(112, 'P-TR-GT001', 'Taman Georgetown Utama', 'Penang', 'TERRACE', '756000.00', 45, 2000, NULL, 'ACTIVE', 'P-TR-GT001.jpg', 'modern terrace house georgetown penang', 0),
(113, 'P-TR-BL002', 'Residensi Bayan Indah', 'Penang', 'TERRACE', '567000.00', 50, 1800, NULL, 'ACTIVE', 'P-TR-BL002.jpg', 'double storey terrace bayan lepas bright', 0),
(114, 'P-TR-BW003', 'Laman Butterworth Harmoni', 'Penang', 'TERRACE', '441000.00', 50, 1700, NULL, 'ACTIVE', 'P-TR-BW003.jpg', 'terrace homes butterworth penang neighborhood', 0),
(115, 'P-AP-GT001', 'Georgetown City Suites', 'Penang', 'APARTMENT', '504000.00', 150, 1100, NULL, 'ACTIVE', 'P-AP-GT001.jpg', 'luxury high rise apartment georgetown penang', 0),
(116, 'P-AP-BL002', 'Bayan Lepas Sentral Residences', 'Penang', 'APARTMENT', '378000.00', 120, 950, NULL, 'ACTIVE', 'P-AP-BL002.jpg', 'serviced apartment bayan lepas industrial park', 0),
(117, 'P-AP-BW003', 'Pangsapuri Butterworth Mewah', 'Penang', 'APARTMENT', '252000.00', 100, 850, '5600.00', 'ACTIVE', 'P-AP-BW003.jpg', 'affordable apartment building butterworth', 1),
(118, 'P-CM-GT001', 'Georgetown Commercial Square', 'Penang', 'COMMERCIAL', '1890000.00', 15, 3000, NULL, 'ACTIVE', 'P-CM-GT001.jpg', 'premium commercial shop lot georgetown penang', 0),
(119, 'P-CM-BL002', 'Bayan Lepas Tech Boulevard', 'Penang', 'COMMERCIAL', '1575000.00', 20, 2800, NULL, 'ACTIVE', 'P-CM-BL002.jpg', 'modern shop office bayan lepas commercial', 0),
(120, 'P-CM-BW003', 'Pusat Komersial Butterworth', 'Penang', 'COMMERCIAL', '1071000.00', 18, 2500, NULL, 'ACTIVE', 'P-CM-BW003.jpg', 'retail commercial space butterworth penang', 0),
(121, 'Q-BG-KC001', 'Villa Kuching Grandeur', 'Sarawak', 'BUNGALOW', '1134000.00', 35, 4800, NULL, 'ACTIVE', 'Q-BG-KC001.jpg', 'luxury bungalow kuching sarawak modern facade', 0),
(122, 'Q-BG-MR002', 'Miri Prestige Estate', 'Sarawak', 'BUNGALOW', '1071000.00', 40, 4500, NULL, 'ACTIVE', 'Q-BG-MR002.jpg', 'detached house miri tropical architecture', 0),
(123, 'Q-BG-BT003', 'Bintulu Coastal Haven', 'Sarawak', 'BUNGALOW', '945000.00', 30, 4200, NULL, 'ACTIVE', 'Q-BG-BT003.jpg', 'premium bungalow bintulu sarawak', 0),
(124, 'Q-TR-KC001', 'Taman Kuching Indah', 'Sarawak', 'TERRACE', '315000.00', 50, 1800, NULL, 'ACTIVE', 'Q-TR-KC001.jpg', 'double storey terrace kuching neighborhood', 0),
(125, 'Q-TR-MR002', 'Laman Miri Harmoni', 'Sarawak', 'TERRACE', '283500.00', 45, 1600, '7000.00', 'ACTIVE', 'Q-TR-MR002.jpg', 'terrace house miri bright sunny day', 1),
(126, 'Q-TR-BT003', 'Residensi Bintulu Utama', 'Sarawak', 'TERRACE', '252000.00', 50, 1550, '7000.00', 'ACTIVE', 'Q-TR-BT003.jpg', 'terrace homes bintulu sarawak residential', 1),
(127, 'Q-AP-KC001', 'Kuching Sentral Suites', 'Sarawak', 'APARTMENT', '220500.00', 120, 950, '5600.00', 'ACTIVE', 'Q-AP-KC001.jpg', 'high rise apartment kuching skyline', 1),
(128, 'Q-AP-MR002', 'Pangsapuri Miri Mewah', 'Sarawak', 'APARTMENT', '189000.00', 100, 850, '3500.00', 'ACTIVE', 'Q-AP-MR002.jpg', 'affordable apartment building miri', 1),
(129, 'Q-AP-BT003', 'Bintulu Oceanview Residences', 'Sarawak', 'APARTMENT', '201600.00', 110, 900, '5600.00', 'ACTIVE', 'Q-AP-BT003.jpg', 'serviced apartment bintulu sarawak', 1),
(130, 'Q-CM-KC001', 'Kuching Trade Centre', 'Sarawak', 'COMMERCIAL', '882000.00', 15, 2500, NULL, 'ACTIVE', 'Q-CM-KC001.jpg', 'commercial shop lot kuching retail', 0),
(131, 'Q-CM-MR002', 'Miri Business Boulevard', 'Sarawak', 'COMMERCIAL', '819000.00', 20, 2400, NULL, 'ACTIVE', 'Q-CM-MR002.jpg', 'shop office miri sarawak commercial', 0),
(132, 'Q-CM-BT003', 'Pusat Komersial Bintulu', 'Sarawak', 'COMMERCIAL', '756000.00', 18, 2200, NULL, 'ACTIVE', 'Q-CM-BT003.jpg', 'modern retail space bintulu', 0),
(133, 'R-BG-KG001', 'Villa Kangar Sanctuary', 'Perlis', 'BUNGALOW', '756000.00', 35, 4200, NULL, 'ACTIVE', 'R-BG-KG001.jpg', 'luxury bungalow kangar perlis architecture', 0),
(134, 'R-BG-AR002', 'Arau Prestige Enclave', 'Perlis', 'BUNGALOW', '693000.00', 40, 4000, NULL, 'ACTIVE', 'R-BG-AR002.jpg', 'detached house arau perlis sunny day', 0),
(135, 'R-BG-PB003', 'Padang Besar Greenview', 'Perlis', 'BUNGALOW', '630000.00', 30, 3800, NULL, 'ACTIVE', 'R-BG-PB003.jpg', 'premium bungalow padang besar perlis', 0),
(136, 'R-TR-KG001', 'Taman Kangar Indah', 'Perlis', 'TERRACE', '201600.00', 50, 1500, NULL, 'ACTIVE', 'R-TR-KG001.jpg', 'double storey terrace kangar neighborhood', 0),
(137, 'R-TR-AR002', 'Laman Arau Harmoni', 'Perlis', 'TERRACE', '189000.00', 45, 1450, '7000.00', 'ACTIVE', 'R-TR-AR002.jpg', 'terrace house arau perlis bright', 1),
(138, 'R-TR-PB003', 'Residensi Padang Besar', 'Perlis', 'TERRACE', '176400.00', 50, 1400, '5600.00', 'ACTIVE', 'R-TR-PB003.jpg', 'terrace homes padang besar perlis', 1),
(139, 'R-AP-KG001', 'Kangar City Suites', 'Perlis', 'APARTMENT', '157500.00', 100, 850, '3500.00', 'ACTIVE', 'R-AP-KG001.jpg', 'apartment building kangar perlis skyline', 1),
(140, 'R-AP-AR002', 'Pangsapuri Arau Mewah', 'Perlis', 'APARTMENT', '138600.00', 120, 800, '3500.00', 'ACTIVE', 'R-AP-AR002.jpg', 'affordable apartment arau perlis', 1),
(141, 'R-AP-PB003', 'Padang Besar Residences', 'Perlis', 'APARTMENT', '144900.00', 110, 820, '3500.00', 'ACTIVE', 'R-AP-PB003.jpg', 'serviced apartment padang besar perlis', 1),
(142, 'R-CM-KG001', 'Kangar Trade Square', 'Perlis', 'COMMERCIAL', '504000.00', 15, 2000, NULL, 'ACTIVE', 'R-CM-KG001.jpg', 'commercial shop lot kangar perlis', 0),
(143, 'R-CM-AR002', 'Arau Business Boulevard', 'Perlis', 'COMMERCIAL', '472500.00', 18, 1800, NULL, 'ACTIVE', 'R-CM-AR002.jpg', 'shop office arau perlis exterior', 0),
(144, 'R-CM-PB003', 'Pusat Komersial Padang Besar', 'Perlis', 'COMMERCIAL', '441000.00', 20, 1900, NULL, 'ACTIVE', 'R-CM-PB003.jpg', 'retail shop space padang besar', 0),
(145, 'S-BG-KK001', 'Villa Kota Kinabalu Elite', 'Sabah', 'BUNGALOW', '1260000.00', 35, 4800, NULL, 'ACTIVE', 'S-BG-KK001.jpg', 'luxury bungalow kota kinabalu sabah sunny', 0),
(146, 'S-BG-SD002', 'Sandakan Coastal Retreat', 'Sabah', 'BUNGALOW', '1008000.00', 40, 4500, NULL, 'ACTIVE', 'S-BG-SD002.jpg', 'detached house sandakan tropical architecture', 0),
(147, 'S-BG-TW003', 'Tawau Prestige Haven', 'Sabah', 'BUNGALOW', '945000.00', 30, 4200, NULL, 'ACTIVE', 'S-BG-TW003.jpg', 'premium bungalow tawau sabah real estate', 0),
(148, 'S-TR-KK001', 'Taman Kota Kinabalu Utama', 'Sabah', 'TERRACE', '378000.00', 50, 1800, NULL, 'ACTIVE', 'S-TR-KK001.jpg', 'double storey terrace kota kinabalu exterior', 0),
(149, 'S-TR-SD002', 'Laman Sandakan Harmoni', 'Sabah', 'TERRACE', '283500.00', 45, 1600, '7000.00', 'ACTIVE', 'S-TR-SD002.jpg', 'terrace house sandakan sabah neighborhood', 1),
(150, 'S-TR-TW003', 'Residensi Tawau Indah', 'Sabah', 'TERRACE', '252000.00', 50, 1550, '7000.00', 'ACTIVE', 'S-TR-TW003.jpg', 'terrace homes tawau sabah bright', 1),
(151, 'S-AP-KK001', 'KK Sentral Residences', 'Sabah', 'APARTMENT', '252000.00', 120, 950, '5600.00', 'ACTIVE', 'S-AP-KK001.jpg', 'high rise apartment kota kinabalu skyline', 1),
(152, 'S-AP-SD002', 'Pangsapuri Sandakan Mewah', 'Sabah', 'APARTMENT', '189000.00', 100, 850, '3500.00', 'ACTIVE', 'S-AP-SD002.jpg', 'affordable apartment building sandakan sabah', 1),
(153, 'S-AP-TW003', 'Tawau City Suites', 'Sabah', 'APARTMENT', '176400.00', 110, 800, '3500.00', 'ACTIVE', 'S-AP-TW003.jpg', 'serviced apartment tawau sabah', 1),
(154, 'S-CM-KK001', 'Kota Kinabalu Trade Centre', 'Sabah', 'COMMERCIAL', '1071000.00', 15, 2600, NULL, 'ACTIVE', 'S-CM-KK001.jpg', 'commercial shop lot kota kinabalu retail', 0),
(155, 'S-CM-SD002', 'Sandakan Business Hub', 'Sabah', 'COMMERCIAL', '756000.00', 20, 2200, NULL, 'ACTIVE', 'S-CM-SD002.jpg', 'shop office sandakan sabah commercial', 0),
(156, 'S-CM-TW003', 'Pusat Komersial Tawau', 'Sabah', 'COMMERCIAL', '693000.00', 18, 2000, NULL, 'ACTIVE', 'S-CM-TW003.jpg', 'modern retail space tawau sabah', 0),
(157, 'T-BG-KT001', 'Villa Kuala Terengganu Coastal', 'Terengganu', 'BUNGALOW', '882000.00', 35, 4500, NULL, 'ACTIVE', 'T-BG-KT001.jpg', 'luxury bungalow kuala terengganu ocean view', 0),
(158, 'T-BG-KM002', 'Kemaman Elite Estate', 'Terengganu', 'BUNGALOW', '819000.00', 40, 4200, NULL, 'ACTIVE', 'T-BG-KM002.jpg', 'detached house kemaman terengganu architecture', 0),
(159, 'T-BG-DG003', 'Dungun Prestige Haven', 'Terengganu', 'BUNGALOW', '756000.00', 30, 4000, NULL, 'ACTIVE', 'T-BG-DG003.jpg', 'premium bungalow dungun terengganu sunny', 0),
(160, 'T-TR-KT001', 'Taman Terengganu Indah', 'Terengganu', 'TERRACE', '252000.00', 50, 1600, NULL, 'ACTIVE', 'T-TR-KT001.jpg', 'double storey terrace kuala terengganu', 0),
(161, 'T-TR-KM002', 'Laman Kemaman Harmoni', 'Terengganu', 'TERRACE', '239400.00', 45, 1550, '7000.00', 'ACTIVE', 'T-TR-KM002.jpg', 'terrace house kemaman terengganu neighborhood', 1),
(162, 'T-TR-DG003', 'Residensi Dungun Utama', 'Terengganu', 'TERRACE', '220500.00', 50, 1500, '7000.00', 'ACTIVE', 'T-TR-DG003.jpg', 'terrace homes dungun terengganu bright', 1),
(163, 'T-AP-KT001', 'Terengganu City Suites', 'Terengganu', 'APARTMENT', '176400.00', 100, 900, '5600.00', 'ACTIVE', 'T-AP-KT001.jpg', 'high rise apartment kuala terengganu modern', 1),
(164, 'T-AP-KM002', 'Pangsapuri Kemaman Mewah', 'Terengganu', 'APARTMENT', '157500.00', 120, 850, '3500.00', 'ACTIVE', 'T-AP-KM002.jpg', 'affordable apartment building kemaman', 1),
(165, 'T-AP-DG003', 'Dungun Coastal Residences', 'Terengganu', 'APARTMENT', '144900.00', 110, 800, '3500.00', 'ACTIVE', 'T-AP-DG003.jpg', 'serviced apartment dungun terengganu', 1),
(166, 'T-CM-KT001', 'Terengganu Trade Square', 'Terengganu', 'COMMERCIAL', '567000.00', 15, 2200, NULL, 'ACTIVE', 'T-CM-KT001.jpg', 'commercial shop lot kuala terengganu', 0),
(167, 'T-CM-KM002', 'Kemaman Business Boulevard', 'Terengganu', 'COMMERCIAL', '535500.00', 20, 2000, NULL, 'ACTIVE', 'T-CM-KM002.jpg', 'shop office kemaman terengganu exterior', 0),
(168, 'T-CM-DG003', 'Pusat Komersial Dungun', 'Terengganu', 'COMMERCIAL', '504000.00', 18, 1900, NULL, 'ACTIVE', 'T-CM-DG003.jpg', 'retail commercial space dungun terengganu', 0),
(169, 'W-BG-BB001', 'Villa Bukit Bintang Imperial', 'Kuala Lumpur', 'BUNGALOW', '3780000.00', 30, 6000, NULL, 'ACTIVE', 'W-BG-BB001.jpg', 'ultra luxury bungalow bukit bintang kuala lumpur modern', 0),
(170, 'W-BG-MK002', 'Mont Kiara Grandeur Estate', 'Kuala Lumpur', 'BUNGALOW', '3465000.00', 35, 5500, NULL, 'ACTIVE', 'W-BG-MK002.jpg', 'premium detached house mont kiara exterior', 0),
(171, 'W-BG-CH003', 'Cheras Elite Haven', 'Kuala Lumpur', 'BUNGALOW', '2520000.00', 40, 5000, NULL, 'ACTIVE', 'W-BG-CH003.jpg', 'luxury bungalow cheras kuala lumpur architecture', 0),
(172, 'W-TR-BB001', 'Residensi Bintang Harmoni', 'Kuala Lumpur', 'TERRACE', '945000.00', 45, 2200, NULL, 'ACTIVE', 'W-TR-BB001.jpg', 'modern double storey terrace bukit bintang', 0),
(173, 'W-TR-MK002', 'Laman Mont Kiara Indah', 'Kuala Lumpur', 'TERRACE', '820000.00', 50, 2000, NULL, 'ACTIVE', 'W-TR-MK002.jpg', 'luxury terrace house mont kiara neighborhood', 0),
(174, 'W-TR-CH003', 'Taman Cheras Utama KL', 'Kuala Lumpur', 'TERRACE', '630000.00', 50, 1800, NULL, 'ACTIVE', 'W-TR-CH003.jpg', 'terrace homes cheras kuala lumpur bright', 0),
(175, 'W-AP-BB001', 'Bukit Bintang City Suites', 'Kuala Lumpur', 'APARTMENT', '567000.00', 150, 1100, NULL, 'ACTIVE', 'W-AP-BB001.jpg', 'luxury high rise apartment bukit bintang skyline', 0),
(176, 'W-AP-MK002', 'Mont Kiara Skyline Residences', 'Kuala Lumpur', 'APARTMENT', '504000.00', 120, 1000, NULL, 'ACTIVE', 'W-AP-MK002.jpg', 'premium serviced apartment mont kiara exterior', 0),
(177, 'W-AP-CH003', 'Pangsapuri Cheras Mewah KL', 'Kuala Lumpur', 'APARTMENT', '315000.00', 100, 900, NULL, 'ACTIVE', 'W-AP-CH003.jpg', 'affordable apartment building cheras kuala lumpur', 0),
(178, 'W-CM-BB001', 'Bukit Bintang Trade Centre', 'Kuala Lumpur', 'COMMERCIAL', '2520000.00', 12, 3200, NULL, 'ACTIVE', 'W-CM-BB001.jpg', 'premium commercial shop lot bukit bintang bustling', 0),
(179, 'W-CM-MK002', 'Mont Kiara Business Boulevard', 'Kuala Lumpur', 'COMMERCIAL', '2205000.00', 15, 3000, NULL, 'ACTIVE', 'W-CM-MK002.jpg', 'modern shop office mont kiara kuala lumpur', 0),
(180, 'W-CM-CH003', 'Pusat Komersial Cheras KL', 'Kuala Lumpur', 'COMMERCIAL', '1575000.00', 20, 2600, NULL, 'ACTIVE', 'W-CM-CH003.jpg', 'retail commercial space cheras kuala lumpur', 0),
(181, 'T-PR-TEST001', 'TEST PROPERTY', 'Johor', 'APARTMENT', '390000.00', 20, 1000, NULL, 'ARCHIVED', 'T-PR-TEST001.jpg', 'NA', 0),
(182, 'W-CM-KC001', 'KLCC TEST', 'Kuala Lumpur', 'COMMERCIAL', '10000000.00', 118, 8000, NULL, 'ACTIVE', 'Custom/W-CM-KC001_20260522113837_3d9cbf2b.jpg', 'NA', 0),
(183, 'W-CM-KC002', 'KL TOWER TEST', 'Kuala Lumpur', 'COMMERCIAL', '12000000.00', 56, 5000, NULL, 'ACTIVE', 'Custom/W-CM-KC002_20260526101828_e7950aa5.jpg', 'NA', 0);

-- --------------------------------------------------------

--
-- 表的结构 `staff`
--

CREATE TABLE `staff` (
  `staff_id` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `phone_number` varchar(20) NOT NULL,
  `assigned_state` varchar(100) NOT NULL,
  `profile_image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 转存表中的数据 `staff`
--

INSERT INTO `staff` (`staff_id`, `full_name`, `phone_number`, `assigned_state`, `profile_image`) VALUES
(3, 'Siti Nurhaliza', '011-10001111', 'Perak', NULL),
(4, 'Chong Wei', '011-10002222', 'Perak', NULL),
(5, 'Muthu Kumar', '012-20001111', 'Selangor', NULL),
(6, 'Nurul Huda', '012-20002222', 'Selangor', NULL),
(7, 'Wong Ken', '013-30001111', 'Pahang', NULL),
(8, 'Aisyah Binti Othman', '013-30002222', 'Pahang', NULL),
(9, 'Tan Mei Ling', '014-40001111', 'Kelantan', NULL),
(10, 'Prakash Rao', '014-40002222', 'Kelantan', NULL),
(11, 'Nur Fatima', '015-50001111', 'Johor', NULL),
(12, 'Lee Chong Wei', '015-50002222', 'Johor', NULL),
(13, 'Sivakumar', '016-60001111', 'Kedah', NULL),
(14, 'Aminah Jusoh', '016-60002222', 'Kedah', NULL),
(15, 'Goh Jin Wei', '017-70001111', 'Labuan', NULL),
(16, 'Ravi Chandran', '017-70002222', 'Labuan', NULL),
(17, 'Fatimah Ali', '018-80001111', 'Melaka', NULL),
(18, 'Chan Peng Soon', '018-80002222', 'Melaka', NULL),
(19, 'Kavita Kaur', '019-90001111', 'Negeri Sembilan', NULL),
(20, 'Zulkifli Majid', '019-90002222', 'Negeri Sembilan', NULL),
(21, 'Lim Swee', '011-20001111', 'Penang', NULL),
(22, 'Sangeetha', '011-20002222', 'Penang', NULL),
(23, 'Awangku Omar', '012-30001111', 'Sarawak', NULL),
(24, 'Liew Daren', '012-30002222', 'Sarawak', NULL),
(25, 'Halimaton Saadiah', '013-40001111', 'Perlis', NULL),
(26, 'Ng Tze Yong', '013-40002222', 'Perlis', NULL),
(27, 'Safiq Rahim', '014-50001111', 'Sabah', NULL),
(28, 'Teo Ee Yi', '014-50002222', 'Sabah', NULL),
(29, 'Arif Aiman', '015-60001111', 'Terengganu', NULL),
(30, 'Ong Yew Sin', '015-60002222', 'Terengganu', NULL),
(46, 'tests', '011-9876543', 'Johor', '/storage/profile_images/staff_46_b054e90bea8384de.jpg'),
(51, 'WIN', '012', 'NA', NULL),
(56, 'staff123', 'staff123', 'Johor', NULL),
(60, 'KL-Jason', '012', 'Kuala Lumpur', NULL);

-- --------------------------------------------------------

--
-- 表的结构 `system_settings`
--

CREATE TABLE `system_settings` (
  `setting_key` varchar(50) NOT NULL,
  `setting_value` varchar(255) NOT NULL,
  `description` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 转存表中的数据 `system_settings`
--

INSERT INTO `system_settings` (`setting_key`, `setting_value`, `description`) VALUES
('BASE_INTEREST_RATE', '3.85', 'Global interest rate parameter utilized for frontend dynamic loan calculator.'),
('DATA_RETENTION_DAYS', '7', 'PDPA compliance setting defining data retention window before sensitive files are purged.');

-- --------------------------------------------------------

--
-- 表的结构 `wishlists`
--

CREATE TABLE `wishlists` (
  `wishlist_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `property_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 转存表中的数据 `wishlists`
--

INSERT INTO `wishlists` (`wishlist_id`, `customer_id`, `property_id`, `created_at`) VALUES
(1, 33, 1, '2026-05-07 15:09:47'),
(2, 33, 2, '2026-05-07 15:09:47'),
(3, 34, 3, '2026-05-07 15:09:47'),
(4, 34, 4, '2026-05-07 15:09:47'),
(5, 35, 5, '2026-05-07 15:09:47'),
(6, 35, 6, '2026-05-07 15:09:47'),
(7, 36, 7, '2026-05-07 15:09:47'),
(8, 36, 8, '2026-05-07 15:09:47'),
(9, 37, 9, '2026-05-07 15:09:47'),
(10, 37, 10, '2026-05-07 15:09:47'),
(11, 38, 11, '2026-05-07 15:09:47'),
(12, 38, 12, '2026-05-07 15:09:47'),
(13, 39, 13, '2026-05-07 15:09:47'),
(14, 39, 14, '2026-05-07 15:09:47'),
(15, 40, 15, '2026-05-07 15:09:47'),
(16, 40, 16, '2026-05-07 15:09:47'),
(17, 41, 17, '2026-05-07 15:09:47'),
(18, 41, 18, '2026-05-07 15:09:47'),
(25, 51, 52, '2026-05-19 00:53:36');

--
-- 转储表的索引
--

--
-- 表的索引 `accounts`
--
ALTER TABLE `accounts`
  ADD PRIMARY KEY (`account_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- 表的索引 `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`admin_id`);

--
-- 表的索引 `affordable_housing_applications`
--
ALTER TABLE `affordable_housing_applications`
  ADD PRIMARY KEY (`application_id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `property_id` (`property_id`),
  ADD KEY `reviewed_by_staff_id` (`reviewed_by_staff_id`);

--
-- 表的索引 `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`appointment_id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `property_id` (`property_id`),
  ADD KEY `assigned_staff_id` (`assigned_staff_id`);

--
-- 表的索引 `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `account_id` (`account_id`);

--
-- 表的索引 `banks`
--
ALTER TABLE `banks`
  ADD PRIMARY KEY (`bank_id`);

--
-- 表的索引 `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`customer_id`);

--
-- 表的索引 `documents`
--
ALTER TABLE `documents`
  ADD PRIMARY KEY (`document_id`),
  ADD KEY `customer_id` (`customer_id`);

--
-- 表的索引 `properties`
--
ALTER TABLE `properties`
  ADD PRIMARY KEY (`property_id`),
  ADD UNIQUE KEY `property_code` (`property_code`);

--
-- 表的索引 `staff`
--
ALTER TABLE `staff`
  ADD PRIMARY KEY (`staff_id`);

--
-- 表的索引 `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`setting_key`);

--
-- 表的索引 `wishlists`
--
ALTER TABLE `wishlists`
  ADD PRIMARY KEY (`wishlist_id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `property_id` (`property_id`);

--
-- 在导出的表使用AUTO_INCREMENT
--

--
-- 使用表AUTO_INCREMENT `accounts`
--
ALTER TABLE `accounts`
  MODIFY `account_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- 使用表AUTO_INCREMENT `affordable_housing_applications`
--
ALTER TABLE `affordable_housing_applications`
  MODIFY `application_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- 使用表AUTO_INCREMENT `appointments`
--
ALTER TABLE `appointments`
  MODIFY `appointment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- 使用表AUTO_INCREMENT `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- 使用表AUTO_INCREMENT `banks`
--
ALTER TABLE `banks`
  MODIFY `bank_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- 使用表AUTO_INCREMENT `documents`
--
ALTER TABLE `documents`
  MODIFY `document_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- 使用表AUTO_INCREMENT `properties`
--
ALTER TABLE `properties`
  MODIFY `property_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=184;

--
-- 使用表AUTO_INCREMENT `wishlists`
--
ALTER TABLE `wishlists`
  MODIFY `wishlist_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- 限制导出的表
--

--
-- 限制表 `admins`
--
ALTER TABLE `admins`
  ADD CONSTRAINT `admins_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `accounts` (`account_id`) ON DELETE CASCADE;

--
-- 限制表 `affordable_housing_applications`
--
ALTER TABLE `affordable_housing_applications`
  ADD CONSTRAINT `affordable_housing_applications_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `affordable_housing_applications_ibfk_2` FOREIGN KEY (`property_id`) REFERENCES `properties` (`property_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `affordable_housing_applications_ibfk_3` FOREIGN KEY (`reviewed_by_staff_id`) REFERENCES `staff` (`staff_id`) ON DELETE SET NULL;

--
-- 限制表 `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `appointments_ibfk_2` FOREIGN KEY (`property_id`) REFERENCES `properties` (`property_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `appointments_ibfk_3` FOREIGN KEY (`assigned_staff_id`) REFERENCES `staff` (`staff_id`) ON DELETE SET NULL;

--
-- 限制表 `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`account_id`) ON DELETE SET NULL;

--
-- 限制表 `customers`
--
ALTER TABLE `customers`
  ADD CONSTRAINT `customers_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `accounts` (`account_id`) ON DELETE CASCADE;

--
-- 限制表 `documents`
--
ALTER TABLE `documents`
  ADD CONSTRAINT `documents_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`) ON DELETE CASCADE;

--
-- 限制表 `staff`
--
ALTER TABLE `staff`
  ADD CONSTRAINT `staff_ibfk_1` FOREIGN KEY (`staff_id`) REFERENCES `accounts` (`account_id`) ON DELETE CASCADE;

--
-- 限制表 `wishlists`
--
ALTER TABLE `wishlists`
  ADD CONSTRAINT `wishlists_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `wishlists_ibfk_2` FOREIGN KEY (`property_id`) REFERENCES `properties` (`property_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
