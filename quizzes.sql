-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- May chu: 127.0.0.1
-- Thoi gian tao: Th3 24, 2026 luc 06:54 AM
-- Phien ban may chu: 10.4.32-MariaDB
-- Phien ban PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Co so du lieu: `edukhaitri`
--

-- --------------------------------------------------------

--
-- Cau truc bang `bai_trac_nghiem`
--

CREATE TABLE `bai_trac_nghiem` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `lesson_id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `passing_score` int(11) NOT NULL DEFAULT 70,
  `is_required` tinyint(1) NOT NULL DEFAULT 1,
  `max_attempts` int(11) NOT NULL DEFAULT 3,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Chỉ mục cho các bảng
--

--
-- Chi muc cho bang `bai_trac_nghiem`
--
ALTER TABLE `bai_trac_nghiem`
  ADD PRIMARY KEY (`id`),
  ADD KEY `bai_trac_nghiem_lesson_id_index` (`lesson_id`);

--
-- AUTO_INCREMENT cho các bảng
--

--
-- AUTO_INCREMENT cho bang `bai_trac_nghiem`
--
ALTER TABLE `bai_trac_nghiem`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Ràng buộc cho các bảng
--

--
-- Rang buoc cho bang `bai_trac_nghiem`
--
ALTER TABLE `bai_trac_nghiem`
  ADD CONSTRAINT `bai_trac_nghiem_lesson_id_foreign` FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
