-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Aug 30, 2025 at 01:08 PM
-- Server version: 8.0.30
-- PHP Version: 8.2.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `jalan_aman`
--

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE `comments` (
  `id` int NOT NULL,
  `report_id` int NOT NULL,
  `user_id` int NOT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `comments`
--

INSERT INTO `comments` (`id`, `report_id`, `user_id`, `content`, `created_at`, `updated_at`) VALUES
(8, 11, 6, 'kenapa tuh', '2025-08-28 07:06:47', '2025-08-28 07:06:47'),
(9, 25, 3, 'wah gimana tuh kronologis nya', '2025-08-28 07:11:25', '2025-08-28 07:11:25'),
(10, 25, 4, 'wah saya juga jadi penasaran', '2025-08-28 13:42:46', '2025-08-28 13:42:46'),
(11, 18, 7, 'iya woy, saya juga pernah kena paku disini', '2025-08-29 02:12:49', '2025-08-29 02:12:49'),
(12, 18, 1, 'YAKANN', '2025-08-29 14:15:46', '2025-08-29 14:15:46'),
(14, 36, 1, 'asdasd', '2025-08-30 05:59:02', '2025-08-30 05:59:02'),
(16, 32, 5, 'wah bahaya nih', '2025-08-30 11:37:43', '2025-08-30 11:37:43');

-- --------------------------------------------------------

--
-- Table structure for table `likes`
--

CREATE TABLE `likes` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `report_id` int NOT NULL,
  `value` tinyint DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `likes`
--

INSERT INTO `likes` (`id`, `user_id`, `report_id`, `value`, `created_at`, `updated_at`) VALUES
(7, 1, 16, 1, '2025-08-27 02:59:34', '2025-08-27 02:59:34'),
(13, 1, 17, 1, '2025-08-27 03:41:10', '2025-08-27 03:41:10'),
(16, 3, 18, 1, '2025-08-27 04:52:34', '2025-08-27 04:52:34'),
(17, 3, 17, 1, '2025-08-27 04:52:37', '2025-08-27 04:52:37'),
(18, 3, 16, 1, '2025-08-27 04:58:50', '2025-08-27 04:58:50'),
(19, 6, 18, 1, '2025-08-27 05:10:52', '2025-08-27 05:10:52'),
(21, 6, 17, 1, '2025-08-27 05:10:59', '2025-08-27 05:10:59'),
(22, 6, 19, 1, '2025-08-27 05:15:13', '2025-08-27 05:15:13'),
(25, 6, 4, 1, '2025-08-28 06:36:31', '2025-08-28 06:36:31'),
(26, 6, 11, 1, '2025-08-28 07:06:36', '2025-08-28 07:06:36'),
(27, 7, 25, 1, '2025-08-29 02:12:31', '2025-08-29 02:12:31'),
(28, 7, 18, 1, '2025-08-29 02:12:37', '2025-08-29 02:12:37'),
(29, 1, 31, 1, '2025-08-30 04:54:17', '2025-08-30 04:54:17'),
(31, 1, 41, -1, '2025-08-30 07:52:19', '2025-08-30 07:52:19'),
(32, 5, 32, 1, '2025-08-30 11:37:33', '2025-08-30 11:37:33');

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

CREATE TABLE `reports` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `route_id` int NOT NULL,
  `latitude` decimal(10,8) NOT NULL,
  `longitude` decimal(11,8) NOT NULL,
  `type` enum('crime','accident','hazard','safe_spot','other') NOT NULL,
  `description` text NOT NULL,
  `photo_url` varchar(255) DEFAULT NULL,
  `file_name` varchar(255) DEFAULT NULL,
  `mime_type` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `edited_at` timestamp NULL DEFAULT NULL,
  `edit_count` int DEFAULT '0',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `reports`
--

INSERT INTO `reports` (`id`, `user_id`, `route_id`, `latitude`, `longitude`, `type`, `description`, `photo_url`, `file_name`, `mime_type`, `created_at`, `edited_at`, `edit_count`, `updated_at`) VALUES
(2, 1, 7, -6.21066351, 106.86356038, 'crime', 'banyak begal di jalan', NULL, NULL, NULL, '2025-08-25 11:35:55', NULL, 0, '2025-08-30 12:46:40'),
(3, 1, 8, -6.21145545, 106.86445624, 'crime', 'banyak orang suka tauran disini', NULL, NULL, NULL, '2025-08-25 11:36:16', NULL, 0, '2025-08-30 12:46:51'),
(4, 1, 8, -6.21145545, 106.86445624, 'hazard', 'sering pada jatoh', NULL, NULL, NULL, '2025-08-25 11:46:26', NULL, 0, '2025-08-25 11:46:26'),
(9, 3, 9, -6.21236471, 106.86163723, 'accident', 'sering jatoh disini huhuu', NULL, NULL, NULL, '2025-08-25 14:29:27', NULL, 0, '2025-08-25 14:29:27'),
(10, 1, 10, -6.20089670, 106.88228942, 'hazard', 'banyak batu', NULL, NULL, NULL, '2025-08-26 05:26:01', NULL, 0, '2025-08-26 05:26:01'),
(11, 1, 11, -6.40637814, 106.96774987, 'accident', 'hati hati sering ada kecelakaan', NULL, NULL, NULL, '2025-08-26 06:26:48', NULL, 0, '2025-08-26 06:26:48'),
(12, 1, 12, -6.40848372, 106.97622175, 'hazard', 'banyak batu batuan', NULL, NULL, NULL, '2025-08-26 06:37:20', NULL, 0, '2025-08-26 06:37:20'),
(14, 1, 14, -6.40327480, 106.97433293, 'crime', 'banyak copet hati hati', NULL, NULL, NULL, '2025-08-26 08:47:39', NULL, 0, '2025-08-26 08:47:39'),
(16, 1, 16, -6.45229586, 106.96956936, 'hazard', 'sering banjir', NULL, NULL, NULL, '2025-08-26 14:20:36', NULL, 0, '2025-08-26 14:20:36'),
(17, 1, 17, -6.40801399, 106.96189553, 'crime', 'hati hati banyak preman', NULL, NULL, NULL, '2025-08-27 03:26:17', NULL, 0, '2025-08-27 03:26:17'),
(18, 1, 19, -6.39775457, 107.00526953, 'hazard', 'banyak paku', NULL, NULL, NULL, '2025-08-27 04:03:58', NULL, 0, '2025-08-27 04:03:58'),
(19, 6, 20, -6.40647069, 106.96798146, 'hazard', 'banyak tukang gorengan di trotoarnya padahal kalau di tertibin pasti ga macet banget kalo saya mau berangkat ke kantor. ayo dong petugas di amankan itu pedagang nakal nyaaa yaaaa', NULL, NULL, NULL, '2025-08-27 05:14:16', '2025-08-27 06:53:47', 2, '2025-08-27 06:53:47'),
(25, 6, 21, -6.40381855, 106.98748649, 'hazard', 'banyak begal hati hati ya, saya pernah hampir kena begal disini...', NULL, NULL, NULL, '2025-08-28 07:10:22', NULL, 0, '2025-08-28 07:10:22'),
(30, 1, 18, -6.40636407, 106.97893023, 'hazard', 'kalo malem gaada lampu jadi gelap bgtt', NULL, NULL, NULL, '2025-08-29 02:22:33', NULL, 0, '2025-08-29 02:22:33'),
(31, 1, 23, -6.45334064, 106.96889609, 'hazard', 'banyak genangan air', '68b2814e43801_1756528974.jpeg', 'images.jpeg', 'image/jpeg', '2025-08-30 04:42:54', NULL, 0, '2025-08-30 04:42:54'),
(32, 1, 24, -6.45260771, 106.97240711, 'hazard', 'banyak begal kalo malem', NULL, NULL, NULL, '2025-08-30 05:19:12', NULL, 0, '2025-08-30 05:19:12'),
(33, 1, 24, -6.45260771, 106.97240711, 'crime', 'banyak pengamen yang maksa', NULL, NULL, NULL, '2025-08-30 05:19:57', '2025-08-30 06:01:03', 1, '2025-08-30 06:01:03'),
(36, 1, 29, -6.44838065, 106.97122157, 'accident', 'sering ada kecelakaan karna jalannya licin', NULL, NULL, NULL, '2025-08-30 05:57:18', NULL, 0, '2025-08-30 12:47:07'),
(39, 1, 32, -6.44951338, 106.97156221, 'accident', 'sering ada kecelakaan hati hati ya', NULL, NULL, NULL, '2025-08-30 06:44:49', '2025-08-30 06:47:38', 2, '2025-08-30 12:47:21'),
(41, 1, 34, -6.45070207, 106.97291404, 'safe_spot', 'aman ini guys', NULL, NULL, NULL, '2025-08-30 06:50:03', NULL, 0, '2025-08-30 12:47:27'),
(42, 1, 35, -6.45674235, 106.96877603, 'hazard', 'bahaya disini', '68b2d62875ec4_1756550696.jpg', '360_F_165345230_YBKFch5nMNb6QD9oW28khpgCKZ6oR4Mq.jpg', 'image/jpeg', '2025-08-30 10:44:56', NULL, 0, '2025-08-30 12:47:34');

-- --------------------------------------------------------

--
-- Table structure for table `routes`
--

CREATE TABLE `routes` (
  `id` int NOT NULL,
  `created_by` int NOT NULL,
  `start_latitude` decimal(10,8) NOT NULL,
  `start_longitude` decimal(11,8) NOT NULL,
  `end_latitude` decimal(10,8) NOT NULL,
  `end_longitude` decimal(11,8) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `polyline` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `routes`
--

INSERT INTO `routes` (`id`, `created_by`, `start_latitude`, `start_longitude`, `end_latitude`, `end_longitude`, `name`, `polyline`, `created_at`, `updated_at`) VALUES
(1, 1, -6.20486924, 106.87062263, -6.20520522, 106.87046170, 'depan masjid', '{\"type\": \"LineString\", \"coordinates\": [[106.870491, -6.205245], [106.870453, -6.205232]]}', '2025-08-25 11:17:41', '2025-08-30 12:47:50'),
(5, 1, -6.28152541, 106.91501319, -6.28221859, 106.91501319, 'deket warung bu salma', '{\"type\": \"LineString\", \"coordinates\": [[106.915014, -6.281525], [106.91501, -6.281759], [106.914981, -6.281762], [106.914966, -6.281777], [106.914961, -6.281795], [106.914968, -6.281889], [106.914971, -6.281986], [106.914996, -6.282141], [106.915005, -6.28216], [106.915023, -6.282167]]}', '2025-08-25 11:26:54', '2025-08-30 12:47:57'),
(6, 1, -6.21030354, 106.86535478, -6.21031953, 106.86491489, 'samping masjid', '{\"type\": \"LineString\", \"coordinates\": [[106.865354, -6.210288], [106.86503, -6.210307], [106.864915, -6.210317]]}', '2025-08-25 11:35:24', '2025-08-30 12:48:04'),
(7, 1, -6.21074084, 106.86374009, -6.21058618, 106.86338067, 'deket sekolah al iklas', '{\"type\": \"LineString\", \"coordinates\": [[106.863748, -6.21074], [106.86372, -6.210479], [106.863375, -6.210562]]}', '2025-08-25 11:35:50', '2025-08-30 12:48:10'),
(8, 1, -6.21155677, 106.86420679, -6.21135412, 106.86470568, 'deket ruko batagor', '{\"type\": \"LineString\", \"coordinates\": [[106.864208, -6.211564], [106.86446, -6.211534], [106.864471, -6.211476], [106.86448, -6.21143], [106.864491, -6.211417], [106.864499, -6.211412], [106.864508, -6.21141], [106.864543, -6.211401], [106.864708, -6.211361]]}', '2025-08-25 11:36:12', '2025-08-30 12:48:25'),
(9, 3, -6.21213806, 106.86199665, -6.21259136, 106.86127782, 'gang cermat', '{\"type\": \"LineString\", \"coordinates\": [[106.861991, -6.212129], [106.861525, -6.212427], [106.861273, -6.212584]]}', '2025-08-25 14:27:59', '2025-08-30 12:51:02'),
(10, 1, -6.20006445, 106.88234576, -6.20172894, 106.88223307, 'gang kuningan', '{\"type\": \"LineString\", \"coordinates\": [[106.88234, -6.200064], [106.882344, -6.200198], [106.882344, -6.200391], [106.882342, -6.200629], [106.882321, -6.200871], [106.882309, -6.201019], [106.882241, -6.201614], [106.88223, -6.201729]]}', '2025-08-26 05:25:30', '2025-08-30 12:50:53'),
(11, 1, -6.40619949, 106.96736081, -6.40655679, 106.96813893, 'depan bm3 banget', '{\"type\": \"LineString\", \"coordinates\": [[106.96736, -6.4062], [106.967428, -6.40623], [106.968087, -6.406517], [106.968146, -6.406542]]}', '2025-08-26 06:26:16', '2025-08-29 02:00:04'),
(12, 1, -6.40833706, 106.97614393, -6.40863037, 106.97629956, 'gang matahari', '{\"type\": \"LineString\", \"coordinates\": [[106.976144, -6.408338], [106.97637, -6.408472], [106.976373, -6.408483], [106.976374, -6.408495], [106.976373, -6.408506], [106.97637, -6.408517], [106.976299, -6.408629]]}', '2025-08-26 06:36:59', '2025-08-30 12:50:46'),
(14, 1, -6.40312286, 106.97431684, -6.40342673, 106.97434902, 'deket mall matahari', '{\"type\": \"LineString\", \"coordinates\": [[106.974318, -6.403119], [106.974319, -6.403119], [106.974433, -6.403159], [106.974361, -6.40343]]}', '2025-08-26 08:47:22', '2025-08-30 12:51:47'),
(16, 1, -6.45259170, 106.96942452, -6.45200002, 106.96971420, 'deket warteg', '{\"type\": \"LineString\", \"coordinates\": [[106.969433, -6.452598], [106.969447, -6.452578], [106.969533, -6.452474], [106.969598, -6.452364], [106.969666, -6.452248], [106.969686, -6.452131], [106.969718, -6.452001]]}', '2025-08-26 14:20:13', '2025-08-30 12:51:41'),
(17, 1, -6.40801132, 106.96137786, -6.40801665, 106.96241319, 'gang sawo', '{\"type\": \"LineString\", \"coordinates\": [[106.96139, -6.408009], [106.961407, -6.408118], [106.961553, -6.408095], [106.961666, -6.408091], [106.962042, -6.408059], [106.962313, -6.408029], [106.962413, -6.408021]]}', '2025-08-27 03:26:00', '2025-08-30 12:51:33'),
(18, 1, -6.40661462, 106.97869956, -6.40611352, 106.97916090, 'deket masjid annur', '{\"type\": \"LineString\", \"coordinates\": [[106.978701, -6.406615], [106.978721, -6.406559], [106.978823, -6.406231], [106.97892, -6.406176], [106.979034, -6.406126], [106.979129, -6.40611], [106.979156, -6.406106], [106.979161, -6.406106]]}', '2025-08-27 03:27:03', '2025-08-30 12:50:39'),
(19, 1, -6.39783986, 107.00503349, -6.39766927, 107.00550556, 'gang supri', '{\"type\": \"LineString\", \"coordinates\": [[107.005029, -6.397834], [107.005091, -6.397793], [107.00518, -6.397757], [107.005279, -6.397717], [107.005409, -6.397683], [107.005505, -6.397664]]}', '2025-08-27 04:03:47', '2025-08-30 12:51:24'),
(20, 6, -6.40636940, 106.96779907, -6.40657198, 106.96816385, 'gang mama', '{\"type\": \"LineString\", \"coordinates\": [[106.967791, -6.406388], [106.968087, -6.406517], [106.968172, -6.406553]]}', '2025-08-27 05:13:12', '2025-08-30 12:51:18'),
(21, 6, -6.40372526, 106.98712707, -6.40391184, 106.98784590, 'gang kedut', '{\"type\": \"LineString\", \"coordinates\": [[106.987135, -6.403716], [106.987135, -6.403716], [106.987161, -6.403727], [106.987243, -6.403751], [106.987472, -6.403808], [106.987535, -6.403808], [106.987602, -6.40382], [106.987652, -6.403827], [106.987709, -6.403843], [106.987848, -6.403908]]}', '2025-08-28 07:09:54', '2025-08-30 12:51:13'),
(22, 1, -6.45035825, 106.96808875, -6.45049685, 106.96852863, 'Rute 6°27\'S106°58\'E ke 6°27\'S106°58\'E', '{\"type\": \"LineString\", \"coordinates\": [[106.96809, -6.450356], [106.96838, -6.45044], [106.968469, -6.450469], [106.968532, -6.450487]]}', '2025-08-30 03:27:44', '2025-08-30 03:27:44'),
(23, 1, -6.45309810, 106.96908653, -6.45358317, 106.96870565, 'Rute 6°27\'S106°58\'E ke 6°27\'S106°58\'E', '{\"type\": \"LineString\", \"coordinates\": [[106.969082, -6.453095], [106.969021, -6.453184], [106.968853, -6.45339], [106.96885, -6.453403], [106.96885, -6.453419], [106.968857, -6.453432], [106.968771, -6.45347], [106.968726, -6.453537], [106.968705, -6.453582]]}', '2025-08-30 04:42:37', '2025-08-30 04:42:37'),
(24, 1, -6.45182946, 106.97264314, -6.45338595, 106.97217107, 'kampung', '{\"type\": \"LineString\", \"coordinates\": [[106.972602, -6.451819], [106.972599, -6.45183], [106.972581, -6.451934], [106.97256, -6.452039], [106.972551, -6.452092], [106.97254, -6.452155], [106.972518, -6.452279], [106.972498, -6.452407], [106.972489, -6.452493], [106.972467, -6.452624], [106.972432, -6.452797], [106.972425, -6.452822], [106.97239, -6.452952], [106.972379, -6.452991], [106.972372, -6.453024], [106.972366, -6.453054], [106.972349, -6.45312], [106.972334, -6.453189], [106.972292, -6.453252], [106.972244, -6.453305], [106.97217, -6.453385]]}', '2025-08-30 05:18:45', '2025-08-30 05:18:45'),
(26, 1, -6.44994248, 106.97124839, -6.45068874, 106.97082996, 'Rute 6°26\'S106°58\'E ke 6°27\'S106°58\'E', '{\"type\": \"LineString\", \"coordinates\": [[106.971262, -6.449952], [106.971223, -6.450005], [106.971098, -6.450166], [106.970983, -6.45037], [106.970945, -6.450427], [106.970821, -6.450685]]}', '2025-08-30 05:53:07', '2025-08-30 05:53:07'),
(27, 1, -6.44994248, 106.97124839, -6.45068874, 106.97082996, 'Rute 6°26\'S106°58\'E ke 6°27\'S106°58\'E', '{\"type\": \"LineString\", \"coordinates\": [[106.971262, -6.449952], [106.971223, -6.450005], [106.971098, -6.450166], [106.970983, -6.45037], [106.970945, -6.450427], [106.970821, -6.450685]]}', '2025-08-30 05:53:14', '2025-08-30 05:53:14'),
(28, 1, -6.45400427, 106.97177410, -6.45485714, 106.97091579, 'Rute 6°27\'S106°58\'E ke 6°27\'S106°58\'E', '{\"type\": \"LineString\", \"coordinates\": [[106.971785, -6.454011], [106.971699, -6.454155], [106.971592, -6.454383], [106.971543, -6.454496], [106.971495, -6.454629], [106.971422, -6.454737], [106.971362, -6.45478], [106.971306, -6.454799], [106.971236, -6.454812], [106.971042, -6.454832], [106.970915, -6.454853]]}', '2025-08-30 05:54:36', '2025-08-30 05:54:36'),
(29, 1, -6.44860453, 106.97094798, -6.44815677, 106.97149515, 'deket pom bensin mini', '{\"type\": \"LineString\", \"coordinates\": [[106.970946, -6.448602], [106.971061, -6.448507], [106.971256, -6.448324], [106.971436, -6.448186], [106.971483, -6.448144]]}', '2025-08-30 05:57:08', '2025-08-30 05:57:08'),
(31, 1, -6.44737319, 106.97255731, -6.44783161, 106.97202623, 'Rute 6°26\'S106°58\'E ke 6°26\'S106°58\'E', '{\"type\": \"LineString\", \"coordinates\": [[106.972553, -6.447368], [106.972443, -6.447459], [106.972231, -6.447647], [106.972031, -6.447847], [106.972021, -6.447836]]}', '2025-08-30 06:28:09', '2025-08-30 06:28:09'),
(32, 1, -6.44936679, 106.97166145, -6.44965996, 106.97146297, 'Rute 6°26\'S106°58\'E ke 6°26\'S106°58\'E', '{\"type\": \"LineString\", \"coordinates\": [[106.971663, -6.449368], [106.971644, -6.449391], [106.971602, -6.44948], [106.971469, -6.449665]]}', '2025-08-30 06:43:56', '2025-08-30 06:43:56'),
(33, 1, -6.44429215, 107.00327933, -6.44460133, 107.00266778, 'Rute 6°26\'S107°0\'E ke 6°26\'S107°0\'E', '{\"type\": \"LineString\", \"coordinates\": [[107.003277, -6.444289], [107.003109, -6.444383], [107.002975, -6.444456], [107.002769, -6.444557], [107.002671, -6.444607]]}', '2025-08-30 06:45:08', '2025-08-30 06:45:08'),
(34, 1, -6.45049151, 106.97303474, -6.45091262, 106.97279334, 'asad', '{\"type\": \"LineString\", \"coordinates\": [[106.973033, -6.450489], [106.973025, -6.450494], [106.972961, -6.450547], [106.972886, -6.450631], [106.972855, -6.45068], [106.972819, -6.450746], [106.972807, -6.450819], [106.972789, -6.450912]]}', '2025-08-30 06:49:56', '2025-08-30 06:49:56'),
(35, 1, -6.45655211, 106.96879148, -6.45693258, 106.96876058, 'Rute 6°27\'S106°58\'E ke 6°27\'S106°58\'E', '{\"type\": \"LineString\", \"coordinates\": [[106.968781, -6.456598], [106.968846, -6.456613], [106.968783, -6.456873], [106.968778, -6.456934]]}', '2025-08-30 10:44:34', '2025-08-30 10:44:34');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','admin') DEFAULT 'user',
  `avatar` varchar(255) DEFAULT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `bio` text,
  `reputation_score` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_expires` datetime DEFAULT NULL,
  `is_verified` tinyint(1) DEFAULT '0',
  `verify_token` varchar(255) DEFAULT NULL,
  `login_provider` varchar(50) DEFAULT 'email',
  `token_expires` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `username`, `email`, `password`, `role`, `avatar`, `phone_number`, `bio`, `reputation_score`, `created_at`, `updated_at`, `reset_token`, `reset_expires`, `is_verified`, `verify_token`, `login_provider`, `token_expires`) VALUES
(1, 'tian xuning', 'user1151', 'user1@gmail.com', '$2y$10$NTIVldqy3Q8FlcR93aLfIevRfniBx7X.yOnrGghXm6MzcfSUpJiR.', 'user', '1756353853_edd8f15c-d882-4c9b-9389-5604e20a24ef.jpeg', '', '', 0, '2025-08-22 02:18:22', '2025-08-28 04:04:13', NULL, NULL, 1, NULL, 'email', '2025-08-23 07:58:34'),
(3, 'dabao', 'wrwer362', 'user2@gmail.com', '$2y$10$w4yfsm6ln6nwccm3xc.KHuGc1aUYfS6PlJMu5L/d.Oo8Qp14obkRK', 'user', '1756269626_wu_suo_wei_icon___dabao_icon___revenged_love___.jpeg', '', '', 0, '2025-08-22 06:54:19', '2025-08-27 04:40:26', NULL, NULL, 1, NULL, 'manual', '2025-08-25 16:07:45'),
(4, 'william', 'william973', 'user3@gmail.com', '$2y$10$8QhQ7ux3Sya7kzv2rrV3heKO8BXQ2DpVJmzNcqCnW9JauZYq9xUpC', 'user', '1756389605_william_jakrapatr.jpeg', '', '', 0, '2025-08-22 07:01:22', '2025-08-28 14:00:05', NULL, NULL, 1, NULL, 'manual', '2025-08-29 13:41:00'),
(5, 'joss', 'satur949', 'user4@gmail.com', '$2y$10$OZcrnqwhz4xEQw7Z5aNILOgYScIHxUvMB0sXvqiQiniFUNzrt/z2m', 'user', '1756553640_5dbfe1a0-fa8c-445a-98b6-fe1c764b1e73.jpeg', '09723812234', 'saya suka basket', 0, '2025-08-22 07:06:40', '2025-08-30 12:29:17', 'a253871ccd1f7e1179e3e31005f74aeabe5dcd4fcc818fb9e114f6bc066976f9', '2025-08-30 13:29:17', 1, NULL, 'manual', '2025-08-23 07:06:40'),
(6, 'Guo Cheng Yu', 'qweqweqew452', 'user5@gmail.com', '$2y$10$8KlVAFNbPCmoZ5PUBRMLy.tFTp8FVRNHABr09RBBsKvA8ujQhDTvS', 'user', '1756271547_zhan_xuan_icon___revenged_love___bl_chino___guo___.jpeg', '85719706030', 'i like cooking', 0, '2025-08-27 05:09:58', '2025-08-27 05:12:27', NULL, NULL, 1, NULL, 'manual', '2025-08-28 05:09:58'),
(7, 'billie', 'billie446', 'user6@gmail.com', '$2y$10$ANbSv1ex5pKNgbr57W/IreMEwaIzGWP8UP98mSrqNApqMSYlKn4mC', 'user', '1756433537_9b339094-dad2-4821-895e-e38bce1dfce4.jpeg', '', '', 0, '2025-08-29 02:10:57', '2025-08-29 02:12:17', NULL, NULL, 1, NULL, 'manual', '2025-08-30 02:10:57'),
(8, 'cone head', 'conehead758', 'user7@gmail.com', '$2y$10$eYxOMMI7F0wPSmpGQ6dvz.zbtuRV7kC/DBbf5111arsualTzG3t6a', 'user', 'default.png', NULL, NULL, 0, '2025-08-30 12:27:15', '2025-08-30 12:27:25', NULL, NULL, 1, NULL, 'manual', NULL),
(9, 'sabrina carpenter', 'sabrinacarpenter737', 'user8@gmail.com', '$2y$10$I/TJi3oibJG6a.DADZeATud8ID9.dqHnfQFvL2SQaYrHHEUYTgYs2', 'user', 'default.png', NULL, NULL, 0, '2025-08-30 13:04:19', '2025-08-30 13:04:19', NULL, NULL, 1, NULL, 'manual', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `report_id` (`report_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `likes`
--
ALTER TABLE `likes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`,`report_id`),
  ADD KEY `report_id` (`report_id`);

--
-- Indexes for table `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `route_id` (`route_id`);

--
-- Indexes for table `routes`
--
ALTER TABLE `routes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `likes`
--
ALTER TABLE `likes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `reports`
--
ALTER TABLE `reports`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `routes`
--
ALTER TABLE `routes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`report_id`) REFERENCES `reports` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `likes`
--
ALTER TABLE `likes`
  ADD CONSTRAINT `likes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `likes_ibfk_2` FOREIGN KEY (`report_id`) REFERENCES `reports` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reports`
--
ALTER TABLE `reports`
  ADD CONSTRAINT `reports_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reports_ibfk_2` FOREIGN KEY (`route_id`) REFERENCES `routes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `routes`
--
ALTER TABLE `routes`
  ADD CONSTRAINT `routes_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
