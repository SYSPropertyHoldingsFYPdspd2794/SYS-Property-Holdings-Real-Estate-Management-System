-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 11, 2026 at 04:00 PM
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
-- Database: `sys_property_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `properties`
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
-- Dumping data for table `properties`
--

INSERT INTO `properties` (`property_id`, `property_code`, `project_name`, `state`, `property_type`, `price`, `total_units`, `built_up_sqft`, `income_limit_rm`, `status`, `image_filename`, `image_search_keyword`, `is_affordable`) VALUES
(1, 'A-BG-IP001', 'Villa Ipoh Sanctuary', 'Perak', 'BUNGALOW', 1134000.00, 35, 4500, NULL, 'ACTIVE', 'A-BG-IP001.jpg', 'luxury bungalow exterior ipoh perak green landscape realistic', 0),
(2, 'A-BG-TP002', 'Taman Taiping Grandeur', 'Perak', 'BUNGALOW', 1008000.00, 40, 4200, NULL, 'ACTIVE', 'A-BG-TP002.jpg', 'modern detached house taiping tropical sunny real estate', 0),
(3, 'A-BG-LM003', 'Lumut Coastal Haven', 'Perak', 'BUNGALOW', 1260000.00, 30, 5100, NULL, 'ACTIVE', 'A-BG-LM003.jpg', 'coastal bungalow architecture lumut perak ocean view', 0),
(4, 'A-TR-IP001', 'Laman Ipoh Harmoni', 'Perak', 'TERRACE', 315000.00, 50, 1800, NULL, 'ACTIVE', 'A-TR-IP001.jpg', 'modern double storey terrace house ipoh perak residential', 0),
(5, 'A-TR-TP002', 'Taman Taiping Indah', 'Perak', 'TERRACE', 264600.00, 45, 1650, 7000.00, 'ACTIVE', 'A-TR-TP002.jpg', 'asian terrace house neighbourhood taiping family home', 1),
(6, 'A-TR-LM003', 'Residensi Lumut Bayu', 'Perak', 'TERRACE', 283500.00, 48, 1700, NULL, 'ACTIVE', 'A-TR-LM003.jpg', 'terrace homes lumut contemporary facade bright', 0),
(7, 'A-AP-IP001', 'Pangsapuri Kinta Sentral', 'Perak', 'APARTMENT', 201600.00, 120, 950, 5600.00, 'ACTIVE', 'A-AP-IP001.jpg', 'modern high rise apartment exterior ipoh city sky', 1),
(8, 'A-AP-TP002', 'Residensi Taiping Mewah', 'Perak', 'APARTMENT', 176400.00, 150, 850, 3500.00, 'ACTIVE', 'A-AP-TP002.jpg', 'affordable apartment building taiping real estate', 1),
(9, 'A-AP-LM003', 'Lumut Oceanview Suites', 'Perak', 'APARTMENT', 220500.00, 100, 1100, NULL, 'ACTIVE', 'A-AP-LM003.jpg', 'serviced apartment facade lumut contemporary living', 0),
(10, 'A-CM-IP001', 'Ipoh Trade Square', 'Perak', 'COMMERCIAL', 819000.00, 15, 2800, NULL, 'ACTIVE', 'A-CM-IP001.jpg', 'modern commercial shop lot ipoh perak bustling street', 0),
(11, 'A-CM-TP002', 'Pusat Perniagaan Taiping', 'Perak', 'COMMERCIAL', 693000.00, 20, 2500, NULL, 'ACTIVE', 'A-CM-TP002.jpg', 'shop office commercial building exterior taiping', 0),
(12, 'A-CM-LM003', 'Lumut Maritime Boulevard', 'Perak', 'COMMERCIAL', 945000.00, 12, 3200, NULL, 'ACTIVE', 'A-CM-LM003.jpg', 'premium retail shop lots lumut perak architecture', 0),
(13, 'B-BG-PJ001', 'Villa Petaling Jaya Elite', 'Selangor', 'BUNGALOW', 2835000.00, 30, 5500, NULL, 'ACTIVE', 'B-BG-PJ001.jpg', 'ultra luxury bungalow petaling jaya modern architectural design', 0),
(14, 'B-BG-SJ002', 'Subang Jaya Prestige Enclave', 'Selangor', 'BUNGALOW', 2520000.00, 35, 5200, NULL, 'ACTIVE', 'B-BG-SJ002.jpg', 'luxury detached house exterior subang jaya sunny day', 0),
(15, 'B-BG-SA003', 'Shah Alam Greenview Manor', 'Selangor', 'BUNGALOW', 2205000.00, 40, 4800, NULL, 'ACTIVE', 'B-BG-SA003.jpg', 'premium bungalow shah alam residential area realistic', 0),
(16, 'B-TR-PJ001', 'Taman Tropika Utama PJ', 'Selangor', 'TERRACE', 567000.00, 45, 2000, NULL, 'ACTIVE', 'B-TR-PJ001.jpg', 'modern terrace house petaling jaya exterior design', 0),
(17, 'B-TR-SJ002', 'Residensi Subang Harmoni', 'Selangor', 'TERRACE', 535500.00, 50, 1900, NULL, 'ACTIVE', 'B-TR-SJ002.jpg', 'contemporary double storey terrace subang jaya family', 0),
(18, 'B-TR-SA003', 'Laman Shah Alam Indah', 'Selangor', 'TERRACE', 472500.00, 48, 1850, NULL, 'ACTIVE', 'B-TR-SA003.jpg', 'terrace homes shah alam bright sunny day facade', 0),
(19, 'B-AP-PJ001', 'PJ Sentral Suites', 'Selangor', 'APARTMENT', 378000.00, 150, 1100, NULL, 'ACTIVE', 'B-AP-PJ001.jpg', 'luxury high rise apartment petaling jaya skyline', 0),
(20, 'B-AP-SJ002', 'Pangsapuri Subang Mewah', 'Selangor', 'APARTMENT', 315000.00, 120, 950, 7000.00, 'ACTIVE', 'B-AP-SJ002.jpg', 'modern apartment complex exterior subang jaya real estate', 1),
(21, 'B-AP-SA003', 'Residensi Shah Alam Vista', 'Selangor', 'APARTMENT', 283500.00, 100, 900, 5600.00, 'ACTIVE', 'B-AP-SA003.jpg', 'affordable serviced apartment building shah alam', 1),
(22, 'B-CM-PJ001', 'Petaling Jaya Business Hub', 'Selangor', 'COMMERCIAL', 1575000.00, 12, 3000, NULL, 'ACTIVE', 'B-CM-PJ001.jpg', 'premium commercial shop lot building petaling jaya', 0),
(23, 'B-CM-SJ002', 'Subang Avenue Commercial', 'Selangor', 'COMMERCIAL', 1386000.00, 15, 2800, NULL, 'ACTIVE', 'B-CM-SJ002.jpg', 'busy commercial street subang jaya shop offices', 0),
(24, 'B-CM-SA003', 'Pusat Komersial Shah Alam', 'Selangor', 'COMMERCIAL', 1260000.00, 20, 2500, NULL, 'ACTIVE', 'B-CM-SA003.jpg', 'modern commercial center shah alam exterior retail', 0),
(25, 'C-BG-KU001', 'Kuantan Coastal Villa', 'Pahang', 'BUNGALOW', 945000.00, 35, 4500, NULL, 'ACTIVE', 'C-BG-KU001.jpg', 'luxury bungalow exterior kuantan beachside modern', 0),
(26, 'C-BG-BT002', 'Bentong Highland Retreat', 'Pahang', 'BUNGALOW', 1071000.00, 30, 4800, NULL, 'ACTIVE', 'C-BG-BT002.jpg', 'detached house highland bentong nature modern architecture', 0),
(27, 'C-BG-CH003', 'Cameron Grandeur Estate', 'Pahang', 'BUNGALOW', 1386000.00, 30, 5000, NULL, 'ACTIVE', 'C-BG-CH003.jpg', 'luxury mountain bungalow cameron highlands misty morning', 0),
(28, 'C-TR-KU001', 'Taman Kuantan Bayu', 'Pahang', 'TERRACE', 252000.00, 45, 1600, NULL, 'ACTIVE', 'C-TR-KU001.jpg', 'double storey terrace kuantan tropical neighborhood', 0),
(29, 'C-TR-BT002', 'Laman Bentong Indah', 'Pahang', 'TERRACE', 239400.00, 50, 1550, 7000.00, 'ACTIVE', 'C-TR-BT002.jpg', 'modern terrace houses bentong green surroundings', 1),
(30, 'C-TR-CH003', 'Residensi Cameron Hills', 'Pahang', 'TERRACE', 315000.00, 40, 1800, NULL, 'ACTIVE', 'C-TR-CH003.jpg', 'premium terrace house cameron highlands architecture', 0),
(31, 'C-AP-KU001', 'Kuantan Seaview Suites', 'Pahang', 'APARTMENT', 189000.00, 100, 950, 5600.00, 'ACTIVE', 'C-AP-KU001.jpg', 'high rise apartment kuantan seaview modern', 1),
(32, 'C-AP-BT002', 'Pangsapuri Bentong Sentral', 'Pahang', 'APARTMENT', 157500.00, 120, 850, 3500.00, 'ACTIVE', 'C-AP-BT002.jpg', 'affordable apartment exterior bentong real estate', 1),
(33, 'C-AP-CH003', 'Cameron Alpine Residences', 'Pahang', 'APARTMENT', 252000.00, 90, 1050, NULL, 'ACTIVE', 'C-AP-CH003.jpg', 'luxury apartment building cameron highlands modern', 0),
(34, 'C-CM-KU001', 'Kuantan Trade Square', 'Pahang', 'COMMERCIAL', 630000.00, 18, 2400, NULL, 'ACTIVE', 'C-CM-KU001.jpg', 'commercial shop lots kuantan busy retail center', 0),
(35, 'C-CM-BT002', 'Bentong Business Boulevard', 'Pahang', 'COMMERCIAL', 567000.00, 15, 2200, NULL, 'ACTIVE', 'C-CM-BT002.jpg', 'modern shop office building bentong pahang', 0),
(36, 'C-CM-CH003', 'Cameron Commercial Hub', 'Pahang', 'COMMERCIAL', 819000.00, 10, 2800, NULL, 'ACTIVE', 'C-CM-CH003.jpg', 'tourist commercial retail space cameron highlands', 0),
(37, 'D-BG-KB001', 'Villa Kota Bharu Mutiara', 'Kelantan', 'BUNGALOW', 756000.00, 40, 4200, NULL, 'ACTIVE', 'D-BG-KB001.jpg', 'modern luxury bungalow kota bharu architecture', 0),
(38, 'D-BG-PM002', 'Pasir Mas Prestige Enclave', 'Kelantan', 'BUNGALOW', 630000.00, 45, 4000, NULL, 'ACTIVE', 'D-BG-PM002.jpg', 'detached house exterior pasir mas kelantan', 0),
(39, 'D-BG-TM003', 'Tanah Merah Greenview', 'Kelantan', 'BUNGALOW', 598500.00, 50, 4100, NULL, 'ACTIVE', 'D-BG-TM003.jpg', 'bungalow home tanah merah tropical garden', 0),
(40, 'D-TR-KB001', 'Taman Kota Bharu Sentosa', 'Kelantan', 'TERRACE', 201600.00, 50, 1500, NULL, 'ACTIVE', 'D-TR-KB001.jpg', 'malaysian terrace house kota bharu residential', 0),
(41, 'D-TR-PM002', 'Laman Pasir Mas Harmoni', 'Kelantan', 'TERRACE', 176400.00, 45, 1450, 5600.00, 'ACTIVE', 'D-TR-PM002.jpg', 'double storey terrace pasir mas affordable', 1),
(42, 'D-TR-TM003', 'Residensi Tanah Merah', 'Kelantan', 'TERRACE', 163800.00, 50, 1400, 3500.00, 'ACTIVE', 'D-TR-TM003.jpg', 'terrace neighborhood tanah merah kelantan bright', 1),
(43, 'D-AP-KB001', 'Residensi Mutiara KB', 'Kelantan', 'APARTMENT', 151200.00, 120, 850, 3500.00, 'ACTIVE', 'D-AP-KB001.jpg', 'apartment building kota bharu city skyline', 1),
(44, 'D-AP-PM002', 'Pangsapuri Pasir Mas', 'Kelantan', 'APARTMENT', 126000.00, 100, 800, 3500.00, 'ACTIVE', 'D-AP-PM002.jpg', 'affordable apartment complex pasir mas kelantan', 1),
(45, 'D-AP-TM003', 'Tanah Merah City Suites', 'Kelantan', 'APARTMENT', 138600.00, 110, 820, 3500.00, 'ACTIVE', 'D-AP-TM003.jpg', 'modern apartment exterior tanah merah residential', 1),
(46, 'D-CM-KB001', 'Pusat Perniagaan Kota Bharu', 'Kelantan', 'COMMERCIAL', 504000.00, 20, 2200, NULL, 'ACTIVE', 'D-CM-KB001.jpg', 'commercial shop lot kota bharu bustling area', 0),
(47, 'D-CM-PM002', 'Pasir Mas Trade Hub', 'Kelantan', 'COMMERCIAL', 441000.00, 15, 2000, NULL, 'ACTIVE', 'D-CM-PM002.jpg', 'shop office retail center pasir mas exterior', 0),
(48, 'D-CM-TM003', 'Tanah Merah Commercial Boulevard', 'Kelantan', 'COMMERCIAL', 409500.00, 15, 2100, NULL, 'ACTIVE', 'D-CM-TM003.jpg', 'modern retail shop lot tanah merah architecture', 0),
(49, 'J-BG-JB001', 'Villa Johor Bahru Grandeur', 'Johor', 'BUNGALOW', 2205000.00, 30, 5200, NULL, 'ACTIVE', 'J-BG-JB001.jpg', 'luxury bungalow exterior johor bahru modern design', 0),
(50, 'J-BG-IP002', 'Iskandar Puteri Prestige', 'Johor', 'BUNGALOW', 2520000.00, 35, 5500, NULL, 'ACTIVE', 'J-BG-IP002.jpg', 'ultra luxury detached house iskandar puteri sunny', 0),
(51, 'J-BG-SR003', 'Serom Estate Haven', 'Johor', 'BUNGALOW', 1260000.00, 40, 4800, NULL, 'ACTIVE', 'J-BG-SR003.jpg', 'premium bungalow serom johor real estate', 0),
(52, 'J-TR-JB001', 'Taman Johor Bahru Indah', 'Johor', 'TERRACE', 504000.00, 50, 1900, NULL, 'ACTIVE', 'J-TR-JB001.jpg', 'modern double storey terrace johor bahru neighborhood', 0),
(53, 'J-TR-IP002', 'Residensi Iskandar Harmoni', 'Johor', 'TERRACE', 567000.00, 45, 2000, NULL, 'ACTIVE', 'J-TR-IP002.jpg', 'luxury terrace house iskandar puteri exterior', 0),
(54, 'J-TR-SR003', 'Laman Serom Utama', 'Johor', 'TERRACE', 315000.00, 50, 1650, 7000.00, 'ACTIVE', 'J-TR-SR003.jpg', 'terrace homes serom johor bright day', 1),
(55, 'J-AP-JB001', 'JB Sentral City Suites', 'Johor', 'APARTMENT', 346500.00, 150, 1050, NULL, 'ACTIVE', 'J-AP-JB001.jpg', 'high rise luxury apartment johor bahru skyline', 0),
(56, 'J-AP-IP002', 'Iskandar Waterfront Residences', 'Johor', 'APARTMENT', 378000.00, 120, 1150, NULL, 'ACTIVE', 'J-AP-IP002.jpg', 'premium serviced apartment iskandar puteri modern', 0),
(57, 'J-AP-SR003', 'Pangsapuri Serom Mewah', 'Johor', 'APARTMENT', 189000.00, 100, 900, 5600.00, 'ACTIVE', 'J-AP-SR003.jpg', 'affordable apartment building serom johor', 1),
(58, 'J-CM-JB001', 'Johor Bahru Commercial Square', 'Johor', 'COMMERCIAL', 1449000.00, 20, 2800, NULL, 'ACTIVE', 'J-CM-JB001.jpg', 'busy commercial shop lot johor bahru street', 0),
(59, 'J-CM-IP002', 'Iskandar Trade Boulevard', 'Johor', 'COMMERCIAL', 1575000.00, 15, 3000, NULL, 'ACTIVE', 'J-CM-IP002.jpg', 'modern commercial building iskandar puteri architecture', 0),
(60, 'J-CM-SR003', 'Pusat Perniagaan Serom', 'Johor', 'COMMERCIAL', 693000.00, 12, 2400, NULL, 'ACTIVE', 'J-CM-SR003.jpg', 'shop office retail center serom johor', 0),
(61, 'K-BG-AS001', 'Villa Alor Setar Mutiara', 'Kedah', 'BUNGALOW', 882000.00, 35, 4500, NULL, 'ACTIVE', 'K-BG-AS001.jpg', 'luxury bungalow exterior alor setar modern facade', 0),
(62, 'K-BG-SP002', 'Sungai Petani Grandeur', 'Kedah', 'BUNGALOW', 819000.00, 40, 4200, NULL, 'ACTIVE', 'K-BG-SP002.jpg', 'detached house sungai petani tropical architecture', 0),
(63, 'K-BG-KL003', 'Kulim Elite Haven', 'Kedah', 'BUNGALOW', 945000.00, 30, 4600, NULL, 'ACTIVE', 'K-BG-KL003.jpg', 'premium bungalow kulim kedah residential', 0),
(64, 'K-TR-AS001', 'Taman Alor Setar Indah', 'Kedah', 'TERRACE', 252000.00, 50, 1600, NULL, 'ACTIVE', 'K-TR-AS001.jpg', 'double storey terrace alor setar bright sunny', 0),
(65, 'K-TR-SP002', 'Laman Sungai Petani', 'Kedah', 'TERRACE', 239400.00, 48, 1550, 7000.00, 'ACTIVE', 'K-TR-SP002.jpg', 'terrace house sungai petani neighborhood malaysian', 1),
(66, 'K-TR-KL003', 'Residensi Kulim Harmoni', 'Kedah', 'TERRACE', 264600.00, 45, 1700, NULL, 'ACTIVE', 'K-TR-KL003.jpg', 'modern terrace homes kulim exterior', 0),
(67, 'K-AP-AS001', 'Alor Setar Sentral Suites', 'Kedah', 'APARTMENT', 176400.00, 100, 900, 5600.00, 'ACTIVE', 'K-AP-AS001.jpg', 'apartment building alor setar city modern', 1),
(68, 'K-AP-SP002', 'Pangsapuri Mutiara SP', 'Kedah', 'APARTMENT', 157500.00, 120, 850, 3500.00, 'ACTIVE', 'K-AP-SP002.jpg', 'affordable apartment complex sungai petani', 1),
(69, 'K-AP-KL003', 'Kulim Heights Residences', 'Kedah', 'APARTMENT', 189000.00, 110, 950, 5600.00, 'ACTIVE', 'K-AP-KL003.jpg', 'high rise serviced apartment kulim kedah', 1),
(70, 'K-CM-AS001', 'Pusat Komersial Alor Setar', 'Kedah', 'COMMERCIAL', 567000.00, 15, 2400, NULL, 'ACTIVE', 'K-CM-AS001.jpg', 'commercial shop lot alor setar retail', 0),
(71, 'K-CM-SP002', 'Sungai Petani Business Avenue', 'Kedah', 'COMMERCIAL', 535500.00, 20, 2200, NULL, 'ACTIVE', 'K-CM-SP002.jpg', 'shop office building sungai petani exterior', 0),
(72, 'K-CM-KL003', 'Kulim Trade Center', 'Kedah', 'COMMERCIAL', 630000.00, 15, 2500, NULL, 'ACTIVE', 'K-CM-KL003.jpg', 'modern commercial space kulim kedah', 0),
(73, 'L-BG-VC001', 'Villa Victoria Offshore', 'Labuan', 'BUNGALOW', 1134000.00, 30, 4800, NULL, 'ACTIVE', 'L-BG-VC001.jpg', 'luxury bungalow victoria labuan ocean breeze', 0),
(74, 'L-BG-LL002', 'Layangan Coastal Retreat', 'Labuan', 'BUNGALOW', 1008000.00, 35, 4500, NULL, 'ACTIVE', 'L-BG-LL002.jpg', 'detached house layangan labuan tropical', 0),
(75, 'L-BG-KS003', 'Kiamsam Prestige Estate', 'Labuan', 'BUNGALOW', 945000.00, 40, 4200, NULL, 'ACTIVE', 'L-BG-KS003.jpg', 'modern bungalow kiamsam labuan architecture', 0),
(76, 'L-TR-VC001', 'Taman Victoria Utama', 'Labuan', 'TERRACE', 283500.00, 45, 1800, NULL, 'ACTIVE', 'L-TR-VC001.jpg', 'double storey terrace victoria labuan exterior', 0),
(77, 'L-TR-LL002', 'Laman Layangan Indah', 'Labuan', 'TERRACE', 252000.00, 50, 1600, 7000.00, 'ACTIVE', 'L-TR-LL002.jpg', 'terrace homes layangan labuan sunny', 1),
(78, 'L-TR-KS003', 'Residensi Kiamsam Harmoni', 'Labuan', 'TERRACE', 239400.00, 45, 1550, 7000.00, 'ACTIVE', 'L-TR-KS003.jpg', 'malaysian terrace house kiamsam labuan', 1),
(79, 'L-AP-VC001', 'Victoria Oceanview Suites', 'Labuan', 'APARTMENT', 220500.00, 100, 1000, NULL, 'ACTIVE', 'L-AP-VC001.jpg', 'high rise apartment victoria labuan ocean view', 0),
(80, 'L-AP-LL002', 'Pangsapuri Layangan Bayu', 'Labuan', 'APARTMENT', 176400.00, 120, 850, 3500.00, 'ACTIVE', 'L-AP-LL002.jpg', 'affordable apartment labuan layangan', 1),
(81, 'L-AP-KS003', 'Kiamsam Sentral Residences', 'Labuan', 'APARTMENT', 189000.00, 110, 900, 5600.00, 'ACTIVE', 'L-AP-KS003.jpg', 'serviced apartment kiamsam labuan modern', 1),
(82, 'L-CM-VC001', 'Victoria Financial Square', 'Labuan', 'COMMERCIAL', 819000.00, 15, 2600, NULL, 'ACTIVE', 'L-CM-VC001.jpg', 'commercial building victoria labuan retail', 0),
(83, 'L-CM-LL002', 'Layangan Trade Boulevard', 'Labuan', 'COMMERCIAL', 630000.00, 18, 2200, NULL, 'ACTIVE', 'L-CM-LL002.jpg', 'shop office layangan labuan exterior', 0),
(84, 'L-CM-KS003', 'Pusat Perniagaan Kiamsam', 'Labuan', 'COMMERCIAL', 598500.00, 20, 2000, NULL, 'ACTIVE', 'L-CM-KS003.jpg', 'commercial shop lot kiamsam labuan', 0),
(85, 'M-BG-MC001', 'Villa Melaka Heritage', 'Melaka', 'BUNGALOW', 1260000.00, 35, 5000, NULL, 'ACTIVE', 'M-BG-MC001.jpg', 'luxury bungalow melaka city modern traditional architecture', 0),
(86, 'M-BG-AK002', 'Ayer Keroh Grandeur', 'Melaka', 'BUNGALOW', 1134000.00, 40, 4800, NULL, 'ACTIVE', 'M-BG-AK002.jpg', 'detached house ayer keroh melaka sunny day', 0),
(87, 'M-BG-AG003', 'Alor Gajah Prestige Haven', 'Melaka', 'BUNGALOW', 945000.00, 45, 4500, NULL, 'ACTIVE', 'M-BG-AG003.jpg', 'premium bungalow alor gajah real estate', 0),
(88, 'M-TR-MC001', 'Taman Melaka Sentral', 'Melaka', 'TERRACE', 315000.00, 50, 1800, NULL, 'ACTIVE', 'M-TR-MC001.jpg', 'modern double storey terrace melaka city', 0),
(89, 'M-TR-AK002', 'Laman Ayer Keroh Indah', 'Melaka', 'TERRACE', 283500.00, 45, 1700, 7000.00, 'ACTIVE', 'M-TR-AK002.jpg', 'terrace house ayer keroh neighborhood', 1),
(90, 'M-TR-AG003', 'Residensi Alor Gajah', 'Melaka', 'TERRACE', 252000.00, 50, 1600, 5600.00, 'ACTIVE', 'M-TR-AG003.jpg', 'terrace homes alor gajah melaka bright', 1),
(91, 'M-AP-MC001', 'Melaka Riverview Suites', 'Melaka', 'APARTMENT', 220500.00, 120, 950, 5600.00, 'ACTIVE', 'M-AP-MC001.jpg', 'high rise apartment melaka river modern', 1),
(92, 'M-AP-AK002', 'Pangsapuri Ayer Keroh Mewah', 'Melaka', 'APARTMENT', 189000.00, 150, 850, 3500.00, 'ACTIVE', 'M-AP-AK002.jpg', 'affordable apartment building ayer keroh', 1),
(93, 'M-AP-AG003', 'Alor Gajah Heights', 'Melaka', 'APARTMENT', 176400.00, 100, 800, 3500.00, 'ACTIVE', 'M-AP-AG003.jpg', 'apartment complex alor gajah melaka', 1),
(94, 'M-CM-MC001', 'Melaka City Trade Centre', 'Melaka', 'COMMERCIAL', 945000.00, 15, 2600, NULL, 'ACTIVE', 'M-CM-MC001.jpg', 'commercial shop lot melaka city bustling', 0),
(95, 'M-CM-AK002', 'Ayer Keroh Business Hub', 'Melaka', 'COMMERCIAL', 819000.00, 20, 2400, NULL, 'ACTIVE', 'M-CM-AK002.jpg', 'shop office building ayer keroh melaka', 0),
(96, 'M-CM-AG003', 'Pusat Komersial Alor Gajah', 'Melaka', 'COMMERCIAL', 693000.00, 18, 2200, NULL, 'ACTIVE', 'M-CM-AG003.jpg', 'retail commercial space alor gajah', 0),
(97, 'N-BG-SR001', 'Villa Seremban Elite', 'Negeri Sembilan', 'BUNGALOW', 1260000.00, 35, 5000, NULL, 'ACTIVE', 'N-BG-SR001.jpg', 'luxury bungalow seremban modern facade architecture', 0),
(98, 'N-BG-PD002', 'Port Dickson Coastal Villa', 'Negeri Sembilan', 'BUNGALOW', 1386000.00, 30, 5200, NULL, 'ACTIVE', 'N-BG-PD002.jpg', 'detached house port dickson ocean view luxury', 0),
(99, 'N-BG-NL003', 'Nilai Grandeur Estate', 'Negeri Sembilan', 'BUNGALOW', 1134000.00, 40, 4800, NULL, 'ACTIVE', 'N-BG-NL003.jpg', 'premium bungalow nilai negeri sembilan sunny', 0),
(100, 'N-TR-SR001', 'Taman Seremban Indah', 'Negeri Sembilan', 'TERRACE', 315000.00, 50, 1800, NULL, 'ACTIVE', 'N-TR-SR001.jpg', 'modern double storey terrace seremban neighborhood', 0),
(101, 'N-TR-PD002', 'Laman Port Dickson Harmoni', 'Negeri Sembilan', 'TERRACE', 283500.00, 45, 1600, 7000.00, 'ACTIVE', 'N-TR-PD002.jpg', 'terrace house port dickson sunny day', 1),
(102, 'N-TR-NL003', 'Residensi Nilai Utama', 'Negeri Sembilan', 'TERRACE', 302400.00, 50, 1750, NULL, 'ACTIVE', 'N-TR-NL003.jpg', 'terrace homes nilai contemporary exterior', 0),
(103, 'N-AP-SR001', 'Seremban Sentral Residences', 'Negeri Sembilan', 'APARTMENT', 220500.00, 120, 950, 5600.00, 'ACTIVE', 'N-AP-SR001.jpg', 'high rise apartment seremban skyline modern', 1),
(104, 'N-AP-PD002', 'PD Seaview Suites', 'Negeri Sembilan', 'APARTMENT', 252000.00, 100, 1000, NULL, 'ACTIVE', 'N-AP-PD002.jpg', 'serviced apartment port dickson luxury', 0),
(105, 'N-AP-NL003', 'Pangsapuri Nilai Mewah', 'Negeri Sembilan', 'APARTMENT', 176400.00, 150, 850, 3500.00, 'ACTIVE', 'N-AP-NL003.jpg', 'affordable apartment building nilai', 1),
(106, 'N-CM-SR001', 'Seremban Business Square', 'Negeri Sembilan', 'COMMERCIAL', 945000.00, 15, 2600, NULL, 'ACTIVE', 'N-CM-SR001.jpg', 'commercial shop lot seremban retail center', 0),
(107, 'N-CM-PD002', 'Port Dickson Trade Boulevard', 'Negeri Sembilan', 'COMMERCIAL', 819000.00, 12, 2400, NULL, 'ACTIVE', 'N-CM-PD002.jpg', 'shop office port dickson commercial', 0),
(108, 'N-CM-NL003', 'Pusat Perniagaan Nilai', 'Negeri Sembilan', 'COMMERCIAL', 882000.00, 20, 2500, NULL, 'ACTIVE', 'N-CM-NL003.jpg', 'modern commercial space nilai retail', 0),
(109, 'P-BG-GT001', 'Villa Georgetown Heritage', 'Penang', 'BUNGALOW', 3150000.00, 30, 5500, NULL, 'ACTIVE', 'P-BG-GT001.jpg', 'ultra luxury bungalow georgetown penang historical modern', 0),
(110, 'P-BG-BL002', 'Bayan Lepas Coastal Haven', 'Penang', 'BUNGALOW', 2520000.00, 35, 5000, NULL, 'ACTIVE', 'P-BG-BL002.jpg', 'luxury detached house bayan lepas penang', 0),
(111, 'P-BG-BW003', 'Butterworth Elite Estate', 'Penang', 'BUNGALOW', 1575000.00, 40, 4800, NULL, 'ACTIVE', 'P-BG-BW003.jpg', 'premium bungalow butterworth mainland penang', 0),
(112, 'P-TR-GT001', 'Taman Georgetown Utama', 'Penang', 'TERRACE', 756000.00, 45, 2000, NULL, 'ACTIVE', 'P-TR-GT001.jpg', 'modern terrace house georgetown penang', 0),
(113, 'P-TR-BL002', 'Residensi Bayan Indah', 'Penang', 'TERRACE', 567000.00, 50, 1800, NULL, 'ACTIVE', 'P-TR-BL002.jpg', 'double storey terrace bayan lepas bright', 0),
(114, 'P-TR-BW003', 'Laman Butterworth Harmoni', 'Penang', 'TERRACE', 441000.00, 50, 1700, NULL, 'ACTIVE', 'P-TR-BW003.jpg', 'terrace homes butterworth penang neighborhood', 0),
(115, 'P-AP-GT001', 'Georgetown City Suites', 'Penang', 'APARTMENT', 504000.00, 150, 1100, NULL, 'ACTIVE', 'P-AP-GT001.jpg', 'luxury high rise apartment georgetown penang', 0),
(116, 'P-AP-BL002', 'Bayan Lepas Sentral Residences', 'Penang', 'APARTMENT', 378000.00, 120, 950, NULL, 'ACTIVE', 'P-AP-BL002.jpg', 'serviced apartment bayan lepas industrial park', 0),
(117, 'P-AP-BW003', 'Pangsapuri Butterworth Mewah', 'Penang', 'APARTMENT', 252000.00, 100, 850, 5600.00, 'ACTIVE', 'P-AP-BW003.jpg', 'affordable apartment building butterworth', 1),
(118, 'P-CM-GT001', 'Georgetown Commercial Square', 'Penang', 'COMMERCIAL', 1890000.00, 15, 3000, NULL, 'ACTIVE', 'P-CM-GT001.jpg', 'premium commercial shop lot georgetown penang', 0),
(119, 'P-CM-BL002', 'Bayan Lepas Tech Boulevard', 'Penang', 'COMMERCIAL', 1575000.00, 20, 2800, NULL, 'ACTIVE', 'P-CM-BL002.jpg', 'modern shop office bayan lepas commercial', 0),
(120, 'P-CM-BW003', 'Pusat Komersial Butterworth', 'Penang', 'COMMERCIAL', 1071000.00, 18, 2500, NULL, 'ACTIVE', 'P-CM-BW003.jpg', 'retail commercial space butterworth penang', 0),
(121, 'Q-BG-KC001', 'Villa Kuching Grandeur', 'Sarawak', 'BUNGALOW', 1134000.00, 35, 4800, NULL, 'ACTIVE', 'Q-BG-KC001.jpg', 'luxury bungalow kuching sarawak modern facade', 0),
(122, 'Q-BG-MR002', 'Miri Prestige Estate', 'Sarawak', 'BUNGALOW', 1071000.00, 40, 4500, NULL, 'ACTIVE', 'Q-BG-MR002.jpg', 'detached house miri tropical architecture', 0),
(123, 'Q-BG-BT003', 'Bintulu Coastal Haven', 'Sarawak', 'BUNGALOW', 945000.00, 30, 4200, NULL, 'ACTIVE', 'Q-BG-BT003.jpg', 'premium bungalow bintulu sarawak', 0),
(124, 'Q-TR-KC001', 'Taman Kuching Indah', 'Sarawak', 'TERRACE', 315000.00, 50, 1800, NULL, 'ACTIVE', 'Q-TR-KC001.jpg', 'double storey terrace kuching neighborhood', 0),
(125, 'Q-TR-MR002', 'Laman Miri Harmoni', 'Sarawak', 'TERRACE', 283500.00, 45, 1600, 7000.00, 'ACTIVE', 'Q-TR-MR002.jpg', 'terrace house miri bright sunny day', 1),
(126, 'Q-TR-BT003', 'Residensi Bintulu Utama', 'Sarawak', 'TERRACE', 252000.00, 50, 1550, 7000.00, 'ACTIVE', 'Q-TR-BT003.jpg', 'terrace homes bintulu sarawak residential', 1),
(127, 'Q-AP-KC001', 'Kuching Sentral Suites', 'Sarawak', 'APARTMENT', 220500.00, 120, 950, 5600.00, 'ACTIVE', 'Q-AP-KC001.jpg', 'high rise apartment kuching skyline', 1),
(128, 'Q-AP-MR002', 'Pangsapuri Miri Mewah', 'Sarawak', 'APARTMENT', 189000.00, 100, 850, 3500.00, 'ACTIVE', 'Q-AP-MR002.jpg', 'affordable apartment building miri', 1),
(129, 'Q-AP-BT003', 'Bintulu Oceanview Residences', 'Sarawak', 'APARTMENT', 201600.00, 110, 900, 5600.00, 'ACTIVE', 'Q-AP-BT003.jpg', 'serviced apartment bintulu sarawak', 1),
(130, 'Q-CM-KC001', 'Kuching Trade Centre', 'Sarawak', 'COMMERCIAL', 882000.00, 15, 2500, NULL, 'ACTIVE', 'Q-CM-KC001.jpg', 'commercial shop lot kuching retail', 0),
(131, 'Q-CM-MR002', 'Miri Business Boulevard', 'Sarawak', 'COMMERCIAL', 819000.00, 20, 2400, NULL, 'ACTIVE', 'Q-CM-MR002.jpg', 'shop office miri sarawak commercial', 0),
(132, 'Q-CM-BT003', 'Pusat Komersial Bintulu', 'Sarawak', 'COMMERCIAL', 756000.00, 18, 2200, NULL, 'ACTIVE', 'Q-CM-BT003.jpg', 'modern retail space bintulu', 0),
(133, 'R-BG-KG001', 'Villa Kangar Sanctuary', 'Perlis', 'BUNGALOW', 756000.00, 35, 4200, NULL, 'ACTIVE', 'R-BG-KG001.jpg', 'luxury bungalow kangar perlis architecture', 0),
(134, 'R-BG-AR002', 'Arau Prestige Enclave', 'Perlis', 'BUNGALOW', 693000.00, 40, 4000, NULL, 'ACTIVE', 'R-BG-AR002.jpg', 'detached house arau perlis sunny day', 0),
(135, 'R-BG-PB003', 'Padang Besar Greenview', 'Perlis', 'BUNGALOW', 630000.00, 30, 3800, NULL, 'ACTIVE', 'R-BG-PB003.jpg', 'premium bungalow padang besar perlis', 0),
(136, 'R-TR-KG001', 'Taman Kangar Indah', 'Perlis', 'TERRACE', 201600.00, 50, 1500, NULL, 'ACTIVE', 'R-TR-KG001.jpg', 'double storey terrace kangar neighborhood', 0),
(137, 'R-TR-AR002', 'Laman Arau Harmoni', 'Perlis', 'TERRACE', 189000.00, 45, 1450, 7000.00, 'ACTIVE', 'R-TR-AR002.jpg', 'terrace house arau perlis bright', 1),
(138, 'R-TR-PB003', 'Residensi Padang Besar', 'Perlis', 'TERRACE', 176400.00, 50, 1400, 5600.00, 'ACTIVE', 'R-TR-PB003.jpg', 'terrace homes padang besar perlis', 1),
(139, 'R-AP-KG001', 'Kangar City Suites', 'Perlis', 'APARTMENT', 157500.00, 100, 850, 3500.00, 'ACTIVE', 'R-AP-KG001.jpg', 'apartment building kangar perlis skyline', 1),
(140, 'R-AP-AR002', 'Pangsapuri Arau Mewah', 'Perlis', 'APARTMENT', 138600.00, 120, 800, 3500.00, 'ACTIVE', 'R-AP-AR002.jpg', 'affordable apartment arau perlis', 1),
(141, 'R-AP-PB003', 'Padang Besar Residences', 'Perlis', 'APARTMENT', 144900.00, 110, 820, 3500.00, 'ACTIVE', 'R-AP-PB003.jpg', 'serviced apartment padang besar perlis', 1),
(142, 'R-CM-KG001', 'Kangar Trade Square', 'Perlis', 'COMMERCIAL', 504000.00, 15, 2000, NULL, 'ACTIVE', 'R-CM-KG001.jpg', 'commercial shop lot kangar perlis', 0),
(143, 'R-CM-AR002', 'Arau Business Boulevard', 'Perlis', 'COMMERCIAL', 472500.00, 18, 1800, NULL, 'ACTIVE', 'R-CM-AR002.jpg', 'shop office arau perlis exterior', 0),
(144, 'R-CM-PB003', 'Pusat Komersial Padang Besar', 'Perlis', 'COMMERCIAL', 441000.00, 20, 1900, NULL, 'ACTIVE', 'R-CM-PB003.jpg', 'retail shop space padang besar', 0),
(145, 'S-BG-KK001', 'Villa Kota Kinabalu Elite', 'Sabah', 'BUNGALOW', 1260000.00, 35, 4800, NULL, 'ACTIVE', 'S-BG-KK001.jpg', 'luxury bungalow kota kinabalu sabah sunny', 0),
(146, 'S-BG-SD002', 'Sandakan Coastal Retreat', 'Sabah', 'BUNGALOW', 1008000.00, 40, 4500, NULL, 'ACTIVE', 'S-BG-SD002.jpg', 'detached house sandakan tropical architecture', 0),
(147, 'S-BG-TW003', 'Tawau Prestige Haven', 'Sabah', 'BUNGALOW', 945000.00, 30, 4200, NULL, 'ACTIVE', 'S-BG-TW003.jpg', 'premium bungalow tawau sabah real estate', 0),
(148, 'S-TR-KK001', 'Taman Kota Kinabalu Utama', 'Sabah', 'TERRACE', 378000.00, 50, 1800, NULL, 'ACTIVE', 'S-TR-KK001.jpg', 'double storey terrace kota kinabalu exterior', 0),
(149, 'S-TR-SD002', 'Laman Sandakan Harmoni', 'Sabah', 'TERRACE', 283500.00, 45, 1600, 7000.00, 'ACTIVE', 'S-TR-SD002.jpg', 'terrace house sandakan sabah neighborhood', 1),
(150, 'S-TR-TW003', 'Residensi Tawau Indah', 'Sabah', 'TERRACE', 252000.00, 50, 1550, 7000.00, 'ACTIVE', 'S-TR-TW003.jpg', 'terrace homes tawau sabah bright', 1),
(151, 'S-AP-KK001', 'KK Sentral Residences', 'Sabah', 'APARTMENT', 252000.00, 120, 950, 5600.00, 'ACTIVE', 'S-AP-KK001.jpg', 'high rise apartment kota kinabalu skyline', 1),
(152, 'S-AP-SD002', 'Pangsapuri Sandakan Mewah', 'Sabah', 'APARTMENT', 189000.00, 100, 850, 3500.00, 'ACTIVE', 'S-AP-SD002.jpg', 'affordable apartment building sandakan sabah', 1),
(153, 'S-AP-TW003', 'Tawau City Suites', 'Sabah', 'APARTMENT', 176400.00, 110, 800, 3500.00, 'ACTIVE', 'S-AP-TW003.jpg', 'serviced apartment tawau sabah', 1),
(154, 'S-CM-KK001', 'Kota Kinabalu Trade Centre', 'Sabah', 'COMMERCIAL', 1071000.00, 15, 2600, NULL, 'ACTIVE', 'S-CM-KK001.jpg', 'commercial shop lot kota kinabalu retail', 0),
(155, 'S-CM-SD002', 'Sandakan Business Hub', 'Sabah', 'COMMERCIAL', 756000.00, 20, 2200, NULL, 'ACTIVE', 'S-CM-SD002.jpg', 'shop office sandakan sabah commercial', 0),
(156, 'S-CM-TW003', 'Pusat Komersial Tawau', 'Sabah', 'COMMERCIAL', 693000.00, 18, 2000, NULL, 'ACTIVE', 'S-CM-TW003.jpg', 'modern retail space tawau sabah', 0),
(157, 'T-BG-KT001', 'Villa Kuala Terengganu Coastal', 'Terengganu', 'BUNGALOW', 882000.00, 35, 4500, NULL, 'ACTIVE', 'T-BG-KT001.jpg', 'luxury bungalow kuala terengganu ocean view', 0),
(158, 'T-BG-KM002', 'Kemaman Elite Estate', 'Terengganu', 'BUNGALOW', 819000.00, 40, 4200, NULL, 'ACTIVE', 'T-BG-KM002.jpg', 'detached house kemaman terengganu architecture', 0),
(159, 'T-BG-DG003', 'Dungun Prestige Haven', 'Terengganu', 'BUNGALOW', 756000.00, 30, 4000, NULL, 'ACTIVE', 'T-BG-DG003.jpg', 'premium bungalow dungun terengganu sunny', 0),
(160, 'T-TR-KT001', 'Taman Terengganu Indah', 'Terengganu', 'TERRACE', 252000.00, 50, 1600, NULL, 'ACTIVE', 'T-TR-KT001.jpg', 'double storey terrace kuala terengganu', 0),
(161, 'T-TR-KM002', 'Laman Kemaman Harmoni', 'Terengganu', 'TERRACE', 239400.00, 45, 1550, 7000.00, 'ACTIVE', 'T-TR-KM002.jpg', 'terrace house kemaman terengganu neighborhood', 1),
(162, 'T-TR-DG003', 'Residensi Dungun Utama', 'Terengganu', 'TERRACE', 220500.00, 50, 1500, 7000.00, 'ACTIVE', 'T-TR-DG003.jpg', 'terrace homes dungun terengganu bright', 1),
(163, 'T-AP-KT001', 'Terengganu City Suites', 'Terengganu', 'APARTMENT', 176400.00, 100, 900, 5600.00, 'ACTIVE', 'T-AP-KT001.jpg', 'high rise apartment kuala terengganu modern', 1),
(164, 'T-AP-KM002', 'Pangsapuri Kemaman Mewah', 'Terengganu', 'APARTMENT', 157500.00, 120, 850, 3500.00, 'ACTIVE', 'T-AP-KM002.jpg', 'affordable apartment building kemaman', 1),
(165, 'T-AP-DG003', 'Dungun Coastal Residences', 'Terengganu', 'APARTMENT', 144900.00, 110, 800, 3500.00, 'ACTIVE', 'T-AP-DG003.jpg', 'serviced apartment dungun terengganu', 1),
(166, 'T-CM-KT001', 'Terengganu Trade Square', 'Terengganu', 'COMMERCIAL', 567000.00, 15, 2200, NULL, 'ACTIVE', 'T-CM-KT001.jpg', 'commercial shop lot kuala terengganu', 0),
(167, 'T-CM-KM002', 'Kemaman Business Boulevard', 'Terengganu', 'COMMERCIAL', 535500.00, 20, 2000, NULL, 'ACTIVE', 'T-CM-KM002.jpg', 'shop office kemaman terengganu exterior', 0),
(168, 'T-CM-DG003', 'Pusat Komersial Dungun', 'Terengganu', 'COMMERCIAL', 504000.00, 18, 1900, NULL, 'ACTIVE', 'T-CM-DG003.jpg', 'retail commercial space dungun terengganu', 0),
(169, 'W-BG-BB001', 'Villa Bukit Bintang Imperial', 'Kuala Lumpur', 'BUNGALOW', 3780000.00, 30, 6000, NULL, 'ACTIVE', 'W-BG-BB001.jpg', 'ultra luxury bungalow bukit bintang kuala lumpur modern', 0),
(170, 'W-BG-MK002', 'Mont Kiara Grandeur Estate', 'Kuala Lumpur', 'BUNGALOW', 3465000.00, 35, 5500, NULL, 'ACTIVE', 'W-BG-MK002.jpg', 'premium detached house mont kiara exterior', 0),
(171, 'W-BG-CH003', 'Cheras Elite Haven', 'Kuala Lumpur', 'BUNGALOW', 2520000.00, 40, 5000, NULL, 'ACTIVE', 'W-BG-CH003.jpg', 'luxury bungalow cheras kuala lumpur architecture', 0),
(172, 'W-TR-BB001', 'Residensi Bintang Harmoni', 'Kuala Lumpur', 'TERRACE', 945000.00, 45, 2200, NULL, 'ACTIVE', 'W-TR-BB001.jpg', 'modern double storey terrace bukit bintang', 0),
(173, 'W-TR-MK002', 'Laman Mont Kiara Indah', 'Kuala Lumpur', 'TERRACE', 819000.00, 50, 2000, NULL, 'ACTIVE', 'W-TR-MK002.jpg', 'luxury terrace house mont kiara neighborhood', 0),
(174, 'W-TR-CH003', 'Taman Cheras Utama KL', 'Kuala Lumpur', 'TERRACE', 630000.00, 50, 1800, NULL, 'ACTIVE', 'W-TR-CH003.jpg', 'terrace homes cheras kuala lumpur bright', 0),
(175, 'W-AP-BB001', 'Bukit Bintang City Suites', 'Kuala Lumpur', 'APARTMENT', 567000.00, 150, 1100, NULL, 'ACTIVE', 'W-AP-BB001.jpg', 'luxury high rise apartment bukit bintang skyline', 0),
(176, 'W-AP-MK002', 'Mont Kiara Skyline Residences', 'Kuala Lumpur', 'APARTMENT', 504000.00, 120, 1000, NULL, 'ACTIVE', 'W-AP-MK002.jpg', 'premium serviced apartment mont kiara exterior', 0),
(177, 'W-AP-CH003', 'Pangsapuri Cheras Mewah KL', 'Kuala Lumpur', 'APARTMENT', 315000.00, 100, 900, 5600.00, 'ACTIVE', 'W-AP-CH003.jpg', 'affordable apartment building cheras kuala lumpur', 1),
(178, 'W-CM-BB001', 'Bukit Bintang Trade Centre', 'Kuala Lumpur', 'COMMERCIAL', 2520000.00, 12, 3200, NULL, 'ACTIVE', 'W-CM-BB001.jpg', 'premium commercial shop lot bukit bintang bustling', 0),
(179, 'W-CM-MK002', 'Mont Kiara Business Boulevard', 'Kuala Lumpur', 'COMMERCIAL', 2205000.00, 15, 3000, NULL, 'ACTIVE', 'W-CM-MK002.jpg', 'modern shop office mont kiara kuala lumpur', 0),
(180, 'W-CM-CH003', 'Pusat Komersial Cheras KL', 'Kuala Lumpur', 'COMMERCIAL', 1575000.00, 20, 2600, NULL, 'ACTIVE', 'W-CM-CH003.jpg', 'retail commercial space cheras kuala lumpur', 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `properties`
--
ALTER TABLE `properties`
  ADD PRIMARY KEY (`property_id`),
  ADD UNIQUE KEY `property_code` (`property_code`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `properties`
--
ALTER TABLE `properties`
  MODIFY `property_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=181;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
