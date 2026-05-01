-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: edukhaitri_main
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `attendance_records`
--

DROP TABLE IF EXISTS `attendance_records`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `attendance_records` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `course_id` bigint(20) unsigned NOT NULL,
  `class_room_id` bigint(20) unsigned DEFAULT NULL,
  `class_schedule_id` bigint(20) unsigned DEFAULT NULL,
  `enrollment_id` bigint(20) unsigned DEFAULT NULL,
  `student_id` bigint(20) unsigned NOT NULL,
  `teacher_id` bigint(20) unsigned NOT NULL,
  `attendance_date` date NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'present',
  `note` text DEFAULT NULL,
  `recorded_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `attendance_records_course_student_date_unique` (`course_id`,`student_id`,`attendance_date`),
  UNIQUE KEY `attendance_records_class_schedule_student_date_unique` (`class_room_id`,`class_schedule_id`,`student_id`,`attendance_date`),
  KEY `attendance_records_enrollment_id_foreign` (`enrollment_id`),
  KEY `attendance_records_student_id_foreign` (`student_id`),
  KEY `attendance_records_teacher_id_attendance_date_index` (`teacher_id`,`attendance_date`),
  KEY `attendance_records_course_id_attendance_date_index` (`course_id`,`attendance_date`),
  KEY `attendance_records_class_schedule_id_foreign` (`class_schedule_id`),
  CONSTRAINT `attendance_records_class_room_id_foreign` FOREIGN KEY (`class_room_id`) REFERENCES `lop_hoc` (`id`) ON DELETE SET NULL,
  CONSTRAINT `attendance_records_class_schedule_id_foreign` FOREIGN KEY (`class_schedule_id`) REFERENCES `lich_hoc` (`id`) ON DELETE SET NULL,
  CONSTRAINT `attendance_records_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `khoa_hoc` (`id`) ON DELETE CASCADE,
  CONSTRAINT `attendance_records_enrollment_id_foreign` FOREIGN KEY (`enrollment_id`) REFERENCES `dang_ky` (`id`) ON DELETE SET NULL,
  CONSTRAINT `attendance_records_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `nguoi_dung` (`id`) ON DELETE CASCADE,
  CONSTRAINT `attendance_records_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `nguoi_dung` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `attendance_records`
--

LOCK TABLES `attendance_records` WRITE;
/*!40000 ALTER TABLE `attendance_records` DISABLE KEYS */;
/*!40000 ALTER TABLE `attendance_records` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bai_hoc`
--

DROP TABLE IF EXISTS `bai_hoc`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bai_hoc` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `module_id` bigint(20) unsigned NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `content` text NOT NULL,
  `order` int(11) NOT NULL DEFAULT 0,
  `duration` int(11) DEFAULT NULL,
  `video_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `bai_hoc_module_id_index` (`module_id`),
  CONSTRAINT `bai_hoc_module_id_foreign` FOREIGN KEY (`module_id`) REFERENCES `chuong_hoc` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bai_hoc`
--

LOCK TABLES `bai_hoc` WRITE;
/*!40000 ALTER TABLE `bai_hoc` DISABLE KEYS */;
INSERT INTO `bai_hoc` VALUES (1,1,'L','d','c',1,1,NULL,'2026-04-28 08:38:51','2026-04-28 08:38:51');
/*!40000 ALTER TABLE `bai_hoc` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bai_kiem_tra`
--

DROP TABLE IF EXISTS `bai_kiem_tra`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bai_kiem_tra` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `lesson_id` bigint(20) unsigned NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `passing_score` int(11) NOT NULL DEFAULT 70,
  `is_required` tinyint(1) NOT NULL DEFAULT 1,
  `max_attempts` int(11) NOT NULL DEFAULT 3,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `teacher_id` bigint(20) unsigned DEFAULT NULL,
  `course_id` bigint(20) unsigned DEFAULT NULL,
  `subject_id` bigint(20) unsigned DEFAULT NULL,
  `lop_hoc_id` bigint(20) unsigned DEFAULT NULL,
  `duration_minutes` smallint(5) unsigned DEFAULT NULL,
  `total_score` decimal(6,2) DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'draft',
  `published_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `bai_kiem_tra_lesson_id_index` (`lesson_id`),
  KEY `bai_kiem_tra_teacher_id_status_index` (`teacher_id`,`status`),
  KEY `bai_kiem_tra_course_id_status_index` (`course_id`,`status`),
  KEY `bai_kiem_tra_subject_id_status_index` (`subject_id`,`status`),
  KEY `bai_kiem_tra_lop_hoc_id_status_index` (`lop_hoc_id`,`status`),
  CONSTRAINT `bai_kiem_tra_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `khoa_hoc` (`id`) ON DELETE SET NULL,
  CONSTRAINT `bai_kiem_tra_lesson_id_foreign` FOREIGN KEY (`lesson_id`) REFERENCES `bai_hoc` (`id`) ON DELETE CASCADE,
  CONSTRAINT `bai_kiem_tra_lop_hoc_id_foreign` FOREIGN KEY (`lop_hoc_id`) REFERENCES `lop_hoc` (`id`) ON DELETE SET NULL,
  CONSTRAINT `bai_kiem_tra_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `mon_hoc` (`id`) ON DELETE SET NULL,
  CONSTRAINT `bai_kiem_tra_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `nguoi_dung` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bai_kiem_tra`
--

LOCK TABLES `bai_kiem_tra` WRITE;
/*!40000 ALTER TABLE `bai_kiem_tra` DISABLE KEYS */;
INSERT INTO `bai_kiem_tra` VALUES (1,1,'Q','d',100,1,3,'2026-04-28 08:38:51','2026-04-28 08:38:51',NULL,NULL,NULL,NULL,NULL,NULL,'draft',NULL);
/*!40000 ALTER TABLE `bai_kiem_tra` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `binh_luan`
--

DROP TABLE IF EXISTS `binh_luan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `binh_luan` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `lesson_id` bigint(20) unsigned DEFAULT NULL,
  `course_id` bigint(20) unsigned DEFAULT NULL,
  `parent_id` bigint(20) unsigned DEFAULT NULL,
  `content` text NOT NULL,
  `likes` int(11) NOT NULL DEFAULT 0,
  `type` enum('question','comment','feedback') NOT NULL DEFAULT 'comment',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `binh_luan_user_id_foreign` (`user_id`),
  KEY `binh_luan_course_id_foreign` (`course_id`),
  KEY `binh_luan_parent_id_foreign` (`parent_id`),
  KEY `binh_luan_lesson_id_course_id_index` (`lesson_id`,`course_id`),
  CONSTRAINT `binh_luan_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `khoa_hoc` (`id`) ON DELETE CASCADE,
  CONSTRAINT `binh_luan_lesson_id_foreign` FOREIGN KEY (`lesson_id`) REFERENCES `bai_hoc` (`id`) ON DELETE CASCADE,
  CONSTRAINT `binh_luan_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `binh_luan` (`id`) ON DELETE CASCADE,
  CONSTRAINT `binh_luan_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `nguoi_dung` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `binh_luan`
--

LOCK TABLES `binh_luan` WRITE;
/*!40000 ALTER TABLE `binh_luan` DISABLE KEYS */;
/*!40000 ALTER TABLE `binh_luan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cau_hoi`
--

DROP TABLE IF EXISTS `cau_hoi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cau_hoi` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `quiz_id` bigint(20) unsigned NOT NULL,
  `question` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `type` enum('multiple_choice','true_false','short_answer') NOT NULL DEFAULT 'multiple_choice',
  `order` int(11) NOT NULL DEFAULT 0,
  `points` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cau_hoi_quiz_id_index` (`quiz_id`),
  CONSTRAINT `cau_hoi_quiz_id_foreign` FOREIGN KEY (`quiz_id`) REFERENCES `bai_kiem_tra` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cau_hoi`
--

LOCK TABLES `cau_hoi` WRITE;
/*!40000 ALTER TABLE `cau_hoi` DISABLE KEYS */;
INSERT INTO `cau_hoi` VALUES (1,1,'C1',NULL,'multiple_choice',1,5,'2026-04-28 08:38:51','2026-04-28 08:38:51');
/*!40000 ALTER TABLE `cau_hoi` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `chung_chi`
--

DROP TABLE IF EXISTS `chung_chi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `chung_chi` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `course_id` bigint(20) unsigned NOT NULL,
  `certificate_number` varchar(255) NOT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `score` decimal(5,2) NOT NULL DEFAULT 0.00,
  `issued_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `status` enum('pending','issued','revoked') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `chung_chi_certificate_number_unique` (`certificate_number`),
  KEY `chung_chi_course_id_foreign` (`course_id`),
  KEY `chung_chi_user_id_course_id_index` (`user_id`,`course_id`),
  CONSTRAINT `chung_chi_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `khoa_hoc` (`id`) ON DELETE CASCADE,
  CONSTRAINT `chung_chi_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `nguoi_dung` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chung_chi`
--

LOCK TABLES `chung_chi` WRITE;
/*!40000 ALTER TABLE `chung_chi` DISABLE KEYS */;
/*!40000 ALTER TABLE `chung_chi` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `chuong_hoc`
--

DROP TABLE IF EXISTS `chuong_hoc`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `chuong_hoc` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `course_id` bigint(20) unsigned NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text DEFAULT NULL,
  `session_count` int(11) DEFAULT NULL,
  `duration` int(11) DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'published',
  `position` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `chuong_hoc_course_id_foreign` (`course_id`),
  CONSTRAINT `chuong_hoc_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `khoa_hoc` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chuong_hoc`
--

LOCK TABLES `chuong_hoc` WRITE;
/*!40000 ALTER TABLE `chuong_hoc` DISABLE KEYS */;
INSERT INTO `chuong_hoc` VALUES (1,1,'M','c',NULL,NULL,'published',1,'2026-04-28 08:38:51','2026-04-28 08:38:51');
/*!40000 ALTER TABLE `chuong_hoc` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `course_time_slots`
--

DROP TABLE IF EXISTS `course_time_slots`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `course_time_slots` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `subject_id` bigint(20) unsigned NOT NULL,
  `teacher_id` bigint(20) unsigned DEFAULT NULL,
  `room_id` bigint(20) unsigned DEFAULT NULL,
  `day_of_week` varchar(20) DEFAULT NULL,
  `slot_date` date DEFAULT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `registration_open_at` timestamp NULL DEFAULT NULL,
  `registration_close_at` timestamp NULL DEFAULT NULL,
  `min_students` int(10) unsigned NOT NULL DEFAULT 1,
  `max_students` int(10) unsigned NOT NULL DEFAULT 20,
  `status` varchar(30) NOT NULL DEFAULT 'pending_open',
  `note` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cts_subject_status_idx` (`subject_id`,`status`),
  KEY `cts_teacher_time_idx` (`teacher_id`,`day_of_week`,`start_time`,`end_time`),
  KEY `cts_room_time_idx` (`room_id`,`day_of_week`,`start_time`,`end_time`),
  CONSTRAINT `course_time_slots_room_id_foreign` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE SET NULL,
  CONSTRAINT `course_time_slots_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `mon_hoc` (`id`) ON DELETE CASCADE,
  CONSTRAINT `course_time_slots_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `nguoi_dung` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `course_time_slots`
--

LOCK TABLES `course_time_slots` WRITE;
/*!40000 ALTER TABLE `course_time_slots` DISABLE KEYS */;
/*!40000 ALTER TABLE `course_time_slots` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `custom_schedule_requests`
--

DROP TABLE IF EXISTS `custom_schedule_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `custom_schedule_requests` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `student_id` bigint(20) unsigned NOT NULL,
  `subject_id` bigint(20) unsigned NOT NULL,
  `course_id` bigint(20) unsigned DEFAULT NULL,
  `preferred_teacher_id` bigint(20) unsigned DEFAULT NULL,
  `requested_days` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`requested_days`)),
  `requested_time` varchar(255) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `reviewed_by` bigint(20) unsigned DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `custom_schedule_requests_course_id_foreign` (`course_id`),
  KEY `custom_schedule_requests_reviewed_by_foreign` (`reviewed_by`),
  KEY `custom_schedule_requests_student_id_status_index` (`student_id`,`status`),
  KEY `custom_schedule_requests_subject_id_status_index` (`subject_id`,`status`),
  KEY `custom_schedule_requests_preferred_teacher_id_status_index` (`preferred_teacher_id`,`status`),
  CONSTRAINT `custom_schedule_requests_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `khoa_hoc` (`id`) ON DELETE SET NULL,
  CONSTRAINT `custom_schedule_requests_preferred_teacher_id_foreign` FOREIGN KEY (`preferred_teacher_id`) REFERENCES `nguoi_dung` (`id`) ON DELETE SET NULL,
  CONSTRAINT `custom_schedule_requests_reviewed_by_foreign` FOREIGN KEY (`reviewed_by`) REFERENCES `nguoi_dung` (`id`) ON DELETE SET NULL,
  CONSTRAINT `custom_schedule_requests_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `nguoi_dung` (`id`) ON DELETE CASCADE,
  CONSTRAINT `custom_schedule_requests_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `mon_hoc` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `custom_schedule_requests`
--

LOCK TABLES `custom_schedule_requests` WRITE;
/*!40000 ALTER TABLE `custom_schedule_requests` DISABLE KEYS */;
/*!40000 ALTER TABLE `custom_schedule_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `dang_ky`
--

DROP TABLE IF EXISTS `dang_ky`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dang_ky` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `course_id` bigint(20) unsigned DEFAULT NULL,
  `lop_hoc_id` bigint(20) unsigned DEFAULT NULL,
  `preferred_schedule` varchar(255) DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `preferred_days` varchar(255) DEFAULT NULL COMMENT 'JSON array of days: ["Monday","Tuesday",...]',
  `is_submitted` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'true = học viên đã submit, chỉ có thể edit; false = chưa submit',
  `submitted_at` timestamp NULL DEFAULT NULL,
  `assigned_teacher_id` bigint(20) unsigned DEFAULT NULL,
  `schedule` varchar(255) DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `note` text DEFAULT NULL,
  `reviewed_by` bigint(20) unsigned DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `subject_id` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `dang_ky_user_lop_hoc_unique` (`user_id`,`lop_hoc_id`),
  KEY `dang_ky_course_id_foreign` (`course_id`),
  KEY `dang_ky_assigned_teacher_id_foreign` (`assigned_teacher_id`),
  KEY `dang_ky_reviewed_by_foreign` (`reviewed_by`),
  KEY `dang_ky_subject_id_foreign` (`subject_id`),
  KEY `dang_ky_lop_hoc_id_foreign` (`lop_hoc_id`),
  CONSTRAINT `dang_ky_assigned_teacher_id_foreign` FOREIGN KEY (`assigned_teacher_id`) REFERENCES `nguoi_dung` (`id`) ON DELETE SET NULL,
  CONSTRAINT `dang_ky_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `khoa_hoc` (`id`) ON DELETE CASCADE,
  CONSTRAINT `dang_ky_lop_hoc_id_foreign` FOREIGN KEY (`lop_hoc_id`) REFERENCES `lop_hoc` (`id`) ON DELETE SET NULL,
  CONSTRAINT `dang_ky_reviewed_by_foreign` FOREIGN KEY (`reviewed_by`) REFERENCES `nguoi_dung` (`id`) ON DELETE SET NULL,
  CONSTRAINT `dang_ky_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `mon_hoc` (`id`) ON DELETE SET NULL,
  CONSTRAINT `dang_ky_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `nguoi_dung` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dang_ky`
--

LOCK TABLES `dang_ky` WRITE;
/*!40000 ALTER TABLE `dang_ky` DISABLE KEYS */;
INSERT INTO `dang_ky` VALUES (1,2,1,NULL,NULL,NULL,NULL,NULL,1,'2026-04-28 08:38:51',NULL,'x','enrolled',NULL,NULL,NULL,'2026-04-28 08:38:51','2026-04-28 08:38:51',1);
/*!40000 ALTER TABLE `dang_ky` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `danh_gia`
--

DROP TABLE IF EXISTS `danh_gia`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `danh_gia` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `course_id` bigint(20) unsigned NOT NULL,
  `rating` int(10) unsigned NOT NULL,
  `comment` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `danh_gia_user_id_course_id_unique` (`user_id`,`course_id`),
  KEY `danh_gia_course_id_foreign` (`course_id`),
  CONSTRAINT `danh_gia_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `khoa_hoc` (`id`) ON DELETE CASCADE,
  CONSTRAINT `danh_gia_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `nguoi_dung` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `danh_gia`
--

LOCK TABLES `danh_gia` WRITE;
/*!40000 ALTER TABLE `danh_gia` DISABLE KEYS */;
/*!40000 ALTER TABLE `danh_gia` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `danh_muc`
--

DROP TABLE IF EXISTS `danh_muc`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `danh_muc` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `program` varchar(255) DEFAULT NULL,
  `level` varchar(255) DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'active',
  `order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `danh_muc_name_unique` (`name`),
  UNIQUE KEY `danh_muc_slug_unique` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `danh_muc`
--

LOCK TABLES `danh_muc` WRITE;
/*!40000 ALTER TABLE `danh_muc` DISABLE KEYS */;
INSERT INTO `danh_muc` VALUES (1,'Cat','cat',NULL,NULL,NULL,NULL,'active',0,'2026-04-28 08:38:51','2026-04-28 08:38:51');
/*!40000 ALTER TABLE `danh_muc` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `diem`
--

DROP TABLE IF EXISTS `diem`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `diem` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `enrollment_id` bigint(20) unsigned NOT NULL,
  `module_id` bigint(20) unsigned DEFAULT NULL,
  `class_room_id` bigint(20) unsigned DEFAULT NULL,
  `student_id` bigint(20) unsigned DEFAULT NULL,
  `teacher_id` bigint(20) unsigned DEFAULT NULL,
  `test_name` varchar(255) DEFAULT NULL,
  `score` decimal(5,2) DEFAULT NULL,
  `weight` tinyint(3) unsigned NOT NULL DEFAULT 1,
  `grade` varchar(255) DEFAULT NULL,
  `feedback` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `grades_class_student_test_unique` (`class_room_id`,`student_id`,`test_name`),
  KEY `diem_enrollment_id_foreign` (`enrollment_id`),
  KEY `diem_module_id_foreign` (`module_id`),
  KEY `diem_student_id_foreign` (`student_id`),
  KEY `diem_teacher_id_foreign` (`teacher_id`),
  CONSTRAINT `diem_class_room_id_foreign` FOREIGN KEY (`class_room_id`) REFERENCES `lop_hoc` (`id`) ON DELETE SET NULL,
  CONSTRAINT `diem_enrollment_id_foreign` FOREIGN KEY (`enrollment_id`) REFERENCES `dang_ky` (`id`) ON DELETE CASCADE,
  CONSTRAINT `diem_module_id_foreign` FOREIGN KEY (`module_id`) REFERENCES `chuong_hoc` (`id`) ON DELETE CASCADE,
  CONSTRAINT `diem_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `nguoi_dung` (`id`) ON DELETE SET NULL,
  CONSTRAINT `diem_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `nguoi_dung` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `diem`
--

LOCK TABLES `diem` WRITE;
/*!40000 ALTER TABLE `diem` DISABLE KEYS */;
/*!40000 ALTER TABLE `diem` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `don_ung_tuyen_giao_vien`
--

DROP TABLE IF EXISTS `don_ung_tuyen_giao_vien`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `don_ung_tuyen_giao_vien` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `experience` text DEFAULT NULL,
  `message` text DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `admin_note` text DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `reviewed_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `don_ung_tuyen_giao_vien`
--

LOCK TABLES `don_ung_tuyen_giao_vien` WRITE;
/*!40000 ALTER TABLE `don_ung_tuyen_giao_vien` DISABLE KEYS */;
/*!40000 ALTER TABLE `don_ung_tuyen_giao_vien` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `khoa_hoc`
--

DROP TABLE IF EXISTS `khoa_hoc`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `khoa_hoc` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `subject_id` bigint(20) unsigned NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `schedule` varchar(255) DEFAULT NULL,
  `teacher_id` bigint(20) unsigned DEFAULT NULL,
  `day_of_week` varchar(20) DEFAULT NULL,
  `meeting_days` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`meeting_days`)),
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `capacity` int(10) unsigned NOT NULL DEFAULT 20,
  `status` varchar(20) NOT NULL DEFAULT 'draft',
  PRIMARY KEY (`id`),
  KEY `khoa_hoc_subject_id_foreign` (`subject_id`),
  KEY `khoa_hoc_teacher_id_foreign` (`teacher_id`),
  CONSTRAINT `khoa_hoc_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `mon_hoc` (`id`) ON DELETE CASCADE,
  CONSTRAINT `khoa_hoc_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `nguoi_dung` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `khoa_hoc`
--

LOCK TABLES `khoa_hoc` WRITE;
/*!40000 ALTER TABLE `khoa_hoc` DISABLE KEYS */;
INSERT INTO `khoa_hoc` VALUES (1,1,'Course','d',0.00,'2026-04-28 08:38:51','2026-04-28 08:38:51',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,20,'active');
/*!40000 ALTER TABLE `khoa_hoc` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lich_hoc`
--

DROP TABLE IF EXISTS `lich_hoc`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lich_hoc` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `lop_hoc_id` bigint(20) unsigned NOT NULL,
  `teacher_id` bigint(20) unsigned DEFAULT NULL,
  `room_id` bigint(20) unsigned DEFAULT NULL,
  `day_of_week` varchar(20) NOT NULL COMMENT 'Monday, Tuesday, Wednesday, Thursday, Friday, Saturday, Sunday',
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lich_hoc_lop_hoc_id_foreign` (`lop_hoc_id`),
  KEY `lich_hoc_teacher_id_foreign` (`teacher_id`),
  KEY `lich_hoc_room_id_foreign` (`room_id`),
  CONSTRAINT `lich_hoc_lop_hoc_id_foreign` FOREIGN KEY (`lop_hoc_id`) REFERENCES `lop_hoc` (`id`) ON DELETE CASCADE,
  CONSTRAINT `lich_hoc_room_id_foreign` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE SET NULL,
  CONSTRAINT `lich_hoc_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `nguoi_dung` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lich_hoc`
--

LOCK TABLES `lich_hoc` WRITE;
/*!40000 ALTER TABLE `lich_hoc` DISABLE KEYS */;
/*!40000 ALTER TABLE `lich_hoc` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lop_hoc`
--

DROP TABLE IF EXISTS `lop_hoc`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lop_hoc` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `subject_id` bigint(20) unsigned NOT NULL,
  `course_id` bigint(20) unsigned DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `room_id` bigint(20) unsigned DEFAULT NULL,
  `teacher_id` bigint(20) unsigned DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `duration` int(10) unsigned DEFAULT NULL COMMENT 'Tháng học, kế thừa từ mon_hoc',
  `status` varchar(20) NOT NULL DEFAULT 'open' COMMENT 'open, full, closed, completed',
  `note` text DEFAULT NULL,
  `grade_weights` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`grade_weights`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lop_hoc_subject_id_foreign` (`subject_id`),
  KEY `lop_hoc_room_id_foreign` (`room_id`),
  KEY `lop_hoc_teacher_id_foreign` (`teacher_id`),
  KEY `lop_hoc_course_id_foreign` (`course_id`),
  CONSTRAINT `lop_hoc_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `khoa_hoc` (`id`) ON DELETE SET NULL,
  CONSTRAINT `lop_hoc_room_id_foreign` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE SET NULL,
  CONSTRAINT `lop_hoc_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `mon_hoc` (`id`) ON DELETE CASCADE,
  CONSTRAINT `lop_hoc_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `nguoi_dung` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lop_hoc`
--

LOCK TABLES `lop_hoc` WRITE;
/*!40000 ALTER TABLE `lop_hoc` DISABLE KEYS */;
/*!40000 ALTER TABLE `lop_hoc` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lua_chon`
--

DROP TABLE IF EXISTS `lua_chon`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lua_chon` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `question_id` bigint(20) unsigned NOT NULL,
  `option_text` text NOT NULL,
  `is_correct` tinyint(1) NOT NULL DEFAULT 0,
  `order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lua_chon_question_id_index` (`question_id`),
  CONSTRAINT `lua_chon_question_id_foreign` FOREIGN KEY (`question_id`) REFERENCES `cau_hoi` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lua_chon`
--

LOCK TABLES `lua_chon` WRITE;
/*!40000 ALTER TABLE `lua_chon` DISABLE KEYS */;
INSERT INTO `lua_chon` VALUES (1,1,'ok',1,1,'2026-04-28 08:38:51','2026-04-28 08:38:51');
/*!40000 ALTER TABLE `lua_chon` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ma_otp`
--

DROP TABLE IF EXISTS `ma_otp`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ma_otp` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `code` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ma_otp_email_index` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ma_otp`
--

LOCK TABLES `ma_otp` WRITE;
/*!40000 ALTER TABLE `ma_otp` DISABLE KEYS */;
/*!40000 ALTER TABLE `ma_otp` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=74 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'2026_03_16_050242_tao_bang_nguoi_dung',1),(2,'2026_03_16_050428_tao_bang_phien_dang_nhap',1),(3,'2026_03_16_051110_them_cot_ten_dang_nhap_so_dien_thoai_vao_nguoi_dung',1),(4,'2026_03_16_051356_sua_vai_tro_sang_enum_trong_nguoi_dung',1),(5,'2026_03_16_053000_tao_bang_ma_otp',1),(6,'2026_03_16_063859_tao_bang_mon_hoc',1),(7,'2026_03_16_063900_tao_bang_khoa_hoc',1),(8,'2026_03_16_070007_tao_bang_chuong_hoc',1),(9,'2026_03_16_070008_tao_bang_dang_ky_hoc',1),(10,'2026_03_16_070043_them_lich_hoc_va_giao_vien_vao_khoa_hoc',1),(11,'2026_03_16_075645_them_hoc_phi_va_hinh_anh_vao_mon_hoc',1),(12,'2026_03_16_080831_chinh_sua_hoc_phi_mon_hoc',1),(13,'2026_03_20_171415_tao_bang_danh_gia',1),(14,'2026_03_20_171424_tao_bang_diem_so',1),(15,'2026_03_21_cap_nhat_lich_dang_ky_hoc',1),(16,'2026_03_23_175842_tao_bang_don_ung_tuyen_giao_vien',1),(17,'2026_03_24_000001_tao_bang_bai_hoc',1),(18,'2026_03_24_000002_tao_bang_bai_kiem_tra',1),(19,'2026_03_24_000003_tao_bang_cau_hoi',1),(20,'2026_03_24_000004_tao_bang_lua_chon',1),(21,'2026_03_24_000005_tao_bang_tra_loi_kiem_tra',1),(22,'2026_03_24_000006_tao_bang_chung_chi',1),(23,'2026_03_24_000007_tao_bang_binh_luan',1),(24,'2026_03_24_000008_tao_bang_thong_bao',1),(25,'2026_03_24_000009_tao_bang_thong_bao_chung',1),(26,'2026_03_24_000010_tao_bang_tai_lieu_dinh_kem',1),(27,'2026_03_24_000011_tao_bang_tien_do_bai_hoc',1),(28,'2026_03_24_000012_tao_bang_danh_muc',1),(29,'2026_03_24_000013_tao_bang_mon_hoc_khoa_hoc_danh_muc',1),(30,'2026_03_24_101500_them_subject_id_vao_dang_ky',1),(31,'2026_03_24_125717_tao_bang_phien_ung_dung',1),(32,'2026_03_24_125835_tao_bang_bo_dem_ung_dung',1),(33,'2026_03_25_000001_sao_chep_khoa_hoc_sang_mon_hoc',1),(34,'2026_03_26_170500_them_cot_schedule_vao_dang_ky',1),(35,'2026_03_27_090000_them_trang_thai_vao_nguoi_dung',1),(36,'2026_03_27_090100_tao_bang_yeu_cau_doi_lich',1),(37,'2026_03_27_100000_cap_nhat_don_ung_tuyen_giao_vien_cho_giai_doan_4',1),(38,'2026_03_27_110000_bo_sung_thong_tin_nhom_hoc',1),(39,'2026_03_27_110000_cap_nhat_nhom_hoc_cho_giai_doan_5',1),(40,'2026_03_27_120000_bo_sung_thong_tin_khoa_hoc_cong_khai',1),(41,'2026_03_27_130000_bo_sung_thong_tin_hoc_phan',1),(42,'2026_03_27_140000_cap_nhat_dang_ky_hoc_cho_giai_doan_8',1),(43,'2026_03_27_150000_bo_sung_cau_hinh_lich_hoc_cho_lop',1),(44,'2026_03_28_090000_cap_nhat_yeu_cau_doi_lich_cho_giai_doan_10',1),(45,'2026_03_28_120000_tao_bang_diem_danh_giang_vien',1),(46,'2026_03_28_121000_tao_bang_phong_hoc',1),(47,'2026_03_28_122000_tao_bang_khung_gio_hoc_cho_khoa_hoc',1),(48,'2026_03_28_123000_tao_bang_dang_ky_nguyen_vong_khung_gio',1),(49,'2026_03_28_124000_tao_bang_lua_chon_khung_gio_dang_ky',1),(50,'2026_03_30_183113_them_hoc_phi_vao_khoa_hoc',1),(51,'2026_03_30_193127_them_loai_phong_vao_phong_hoc',1),(52,'2026_03_31_000001_tao_bang_vai_tro',1),(53,'2026_03_31_000002_them_vai_tro_id_vao_nguoi_dung_va_xoa_cot_vai_tro',1),(54,'2026_03_31_100001_tao_bang_lop_hoc',1),(55,'2026_03_31_100002_tao_bang_lich_hoc',1),(56,'2026_03_31_100003_them_lop_hoc_id_vao_dang_ky',1),(57,'2026_04_02_071353_tao_bang_phien_he_thong',1),(58,'2026_04_02_180000_them_lop_hoc_va_lich_hoc_vao_diem_danh',1),(59,'2026_04_02_180100_them_thong_tin_lop_hoc_vao_diem_so',1),(60,'2026_04_02_180200_tao_bang_danh_gia_giao_vien',1),(61,'2026_04_02_180300_mo_rong_yeu_cau_doi_lich_cho_lich_hoc',1),(62,'2026_04_02_190000_mo_rong_cau_truc_ghi_danh_va_lich_hoc',1),(63,'2026_04_03_090000_them_ngay_hoc_vao_khoa_hoc',1),(64,'2026_04_03_120000_them_phong_hoc_de_xuat_vao_yeu_cau_doi_lich',1),(65,'2026_04_03_170000_tao_bang_phong_ban_va_them_phong_ban_vao_nguoi_dung',1),(66,'2026_04_04_000001_them_unique_hoc_vien_lop_vao_dang_ky',1),(67,'2026_04_09_000001_them_so_buoi_vao_chuong_hoc',1),(68,'2026_04_16_000002_them_anh_dai_dien_vao_nguoi_dung',1),(69,'2026_04_16_000003_them_so_lan_kiem_tra_vao_mon_hoc',1),(70,'2026_04_16_000004_them_he_so_vao_bang_diem',1),(71,'2026_04_16_000005_them_he_so_diem_vao_lop_hoc',1),(72,'2026_04_17_000001_tao_bang_yeu_cau_xin_phep',1),(73,'2026_04_28_000001_mo_rong_bai_kiem_tra_cho_giao_vien',1);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mon_hoc`
--

DROP TABLE IF EXISTS `mon_hoc`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mon_hoc` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `price` decimal(13,2) NOT NULL DEFAULT 0.00,
  `duration` int(11) DEFAULT NULL,
  `test_count` tinyint(3) unsigned NOT NULL DEFAULT 3,
  `status` varchar(255) NOT NULL DEFAULT 'open',
  `image` varchar(255) DEFAULT NULL,
  `category_id` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `mon_hoc_category_id_foreign` (`category_id`),
  CONSTRAINT `mon_hoc_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `danh_muc` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mon_hoc`
--

LOCK TABLES `mon_hoc` WRITE;
/*!40000 ALTER TABLE `mon_hoc` DISABLE KEYS */;
INSERT INTO `mon_hoc` VALUES (1,'Sub',NULL,'2026-04-28 08:38:51','2026-04-28 08:38:51',1.00,NULL,3,'open',NULL,1);
/*!40000 ALTER TABLE `mon_hoc` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nguoi_dung`
--

DROP TABLE IF EXISTS `nguoi_dung`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nguoi_dung` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `role_id` bigint(20) unsigned NOT NULL DEFAULT 3,
  `department_id` bigint(20) unsigned DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'active',
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `avatar_path` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nguoi_dung_email_unique` (`email`),
  UNIQUE KEY `nguoi_dung_username_unique` (`username`),
  KEY `nguoi_dung_role_id_foreign` (`role_id`),
  KEY `nguoi_dung_department_id_foreign` (`department_id`),
  CONSTRAINT `nguoi_dung_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `phong_ban` (`id`) ON DELETE SET NULL,
  CONSTRAINT `nguoi_dung_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nguoi_dung`
--

LOCK TABLES `nguoi_dung` WRITE;
/*!40000 ALTER TABLE `nguoi_dung` DISABLE KEYS */;
INSERT INTO `nguoi_dung` VALUES (1,2,NULL,'Annamae Jacobson Jr.','guido.jacobs','kemmer.jakob@example.com','+1.925.314.3439','2026-04-28 08:38:50','$2y$12$l4NRJ41t7l6lW5nU5FxB/./x3q.YyddNW5ivyvibTsgqlZZVoBUz.','active','XhJiMKoDEz','2026-04-28 08:38:51','2026-04-28 08:38:51',NULL),(2,3,NULL,'Llewellyn Doyle','leannon.ben','lura.ernser@example.net','1-586-395-7159','2026-04-28 08:38:51','$2y$12$l4NRJ41t7l6lW5nU5FxB/./x3q.YyddNW5ivyvibTsgqlZZVoBUz.','active','a3VfnTTcXC','2026-04-28 08:38:51','2026-04-28 08:38:51',NULL);
/*!40000 ALTER TABLE `nguoi_dung` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phien`
--

DROP TABLE IF EXISTS `phien`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phien` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `phien_user_id_index` (`user_id`),
  KEY `phien_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phien`
--

LOCK TABLES `phien` WRITE;
/*!40000 ALTER TABLE `phien` DISABLE KEYS */;
/*!40000 ALTER TABLE `phien` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phong_ban`
--

DROP TABLE IF EXISTS `phong_ban`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phong_ban` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(30) NOT NULL,
  `name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `phong_ban_code_unique` (`code`),
  UNIQUE KEY `phong_ban_name_unique` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phong_ban`
--

LOCK TABLES `phong_ban` WRITE;
/*!40000 ALTER TABLE `phong_ban` DISABLE KEYS */;
INSERT INTO `phong_ban` VALUES (1,'DT','Phòng Đào tạo','Phụ trách chuyên môn đào tạo và phân công giảng dạy.','active','2026-04-28 08:38:49','2026-04-28 08:38:49'),(2,'KTCL','Phòng Khảo thí - Chất lượng','Theo dõi chất lượng giảng dạy, đánh giá và kiểm định nội bộ.','active','2026-04-28 08:38:49','2026-04-28 08:38:49'),(3,'CNTT','Phòng Công nghệ giáo dục','Vận hành nền tảng, tài nguyên số và hỗ trợ kỹ thuật học tập.','active','2026-04-28 08:38:49','2026-04-28 08:38:49');
/*!40000 ALTER TABLE `phong_ban` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `roles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_unique` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'admin','2026-04-28 08:38:46','2026-04-28 08:38:46'),(2,'teacher','2026-04-28 08:38:46','2026-04-28 08:38:46'),(3,'student','2026-04-28 08:38:46','2026-04-28 08:38:46');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rooms`
--

DROP TABLE IF EXISTS `rooms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rooms` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL DEFAULT 'theory',
  `location` varchar(255) DEFAULT NULL,
  `capacity` int(10) unsigned NOT NULL DEFAULT 20,
  `status` varchar(20) NOT NULL DEFAULT 'active',
  `note` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `rooms_code_unique` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rooms`
--

LOCK TABLES `rooms` WRITE;
/*!40000 ALTER TABLE `rooms` DISABLE KEYS */;
/*!40000 ALTER TABLE `rooms` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `schedule_change_requests`
--

DROP TABLE IF EXISTS `schedule_change_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `schedule_change_requests` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `teacher_id` bigint(20) unsigned NOT NULL,
  `course_id` bigint(20) unsigned DEFAULT NULL,
  `class_room_id` bigint(20) unsigned DEFAULT NULL,
  `class_schedule_id` bigint(20) unsigned DEFAULT NULL,
  `requested_room_id` bigint(20) unsigned DEFAULT NULL,
  `current_schedule` text DEFAULT NULL,
  `requested_day_of_week` varchar(20) DEFAULT NULL,
  `requested_date` date DEFAULT NULL,
  `requested_end_date` date DEFAULT NULL,
  `requested_start_time` time DEFAULT NULL,
  `requested_end_time` time DEFAULT NULL,
  `reason` text NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `admin_note` text DEFAULT NULL,
  `reviewed_by` bigint(20) unsigned DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `schedule_change_requests_teacher_id_foreign` (`teacher_id`),
  KEY `schedule_change_requests_course_id_foreign` (`course_id`),
  KEY `schedule_change_requests_reviewed_by_foreign` (`reviewed_by`),
  KEY `schedule_change_requests_class_room_id_foreign` (`class_room_id`),
  KEY `schedule_change_requests_class_schedule_id_foreign` (`class_schedule_id`),
  KEY `schedule_change_requests_requested_room_id_foreign` (`requested_room_id`),
  CONSTRAINT `schedule_change_requests_class_room_id_foreign` FOREIGN KEY (`class_room_id`) REFERENCES `lop_hoc` (`id`) ON DELETE SET NULL,
  CONSTRAINT `schedule_change_requests_class_schedule_id_foreign` FOREIGN KEY (`class_schedule_id`) REFERENCES `lich_hoc` (`id`) ON DELETE SET NULL,
  CONSTRAINT `schedule_change_requests_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `khoa_hoc` (`id`) ON DELETE SET NULL,
  CONSTRAINT `schedule_change_requests_requested_room_id_foreign` FOREIGN KEY (`requested_room_id`) REFERENCES `rooms` (`id`) ON DELETE SET NULL,
  CONSTRAINT `schedule_change_requests_reviewed_by_foreign` FOREIGN KEY (`reviewed_by`) REFERENCES `nguoi_dung` (`id`) ON DELETE SET NULL,
  CONSTRAINT `schedule_change_requests_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `nguoi_dung` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `schedule_change_requests`
--

LOCK TABLES `schedule_change_requests` WRITE;
/*!40000 ALTER TABLE `schedule_change_requests` DISABLE KEYS */;
/*!40000 ALTER TABLE `schedule_change_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `slot_registration_choices`
--

DROP TABLE IF EXISTS `slot_registration_choices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `slot_registration_choices` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `slot_registration_id` bigint(20) unsigned NOT NULL,
  `course_time_slot_id` bigint(20) unsigned NOT NULL,
  `priority` tinyint(3) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slot_reg_choice_unique` (`slot_registration_id`,`course_time_slot_id`),
  UNIQUE KEY `slot_reg_priority_unique` (`slot_registration_id`,`priority`),
  KEY `slot_reg_slot_priority_index` (`course_time_slot_id`,`priority`),
  CONSTRAINT `slot_registration_choices_course_time_slot_id_foreign` FOREIGN KEY (`course_time_slot_id`) REFERENCES `course_time_slots` (`id`) ON DELETE CASCADE,
  CONSTRAINT `slot_registration_choices_slot_registration_id_foreign` FOREIGN KEY (`slot_registration_id`) REFERENCES `slot_registrations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `slot_registration_choices`
--

LOCK TABLES `slot_registration_choices` WRITE;
/*!40000 ALTER TABLE `slot_registration_choices` DISABLE KEYS */;
/*!40000 ALTER TABLE `slot_registration_choices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `slot_registrations`
--

DROP TABLE IF EXISTS `slot_registrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `slot_registrations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `student_id` bigint(20) unsigned NOT NULL,
  `subject_id` bigint(20) unsigned NOT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'pending',
  `note` text DEFAULT NULL,
  `reviewed_by` bigint(20) unsigned DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `slot_registrations_reviewed_by_foreign` (`reviewed_by`),
  KEY `slot_registrations_student_id_status_index` (`student_id`,`status`),
  KEY `slot_registrations_subject_id_status_index` (`subject_id`,`status`),
  CONSTRAINT `slot_registrations_reviewed_by_foreign` FOREIGN KEY (`reviewed_by`) REFERENCES `nguoi_dung` (`id`) ON DELETE SET NULL,
  CONSTRAINT `slot_registrations_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `nguoi_dung` (`id`) ON DELETE CASCADE,
  CONSTRAINT `slot_registrations_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `mon_hoc` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `slot_registrations`
--

LOCK TABLES `slot_registrations` WRITE;
/*!40000 ALTER TABLE `slot_registrations` DISABLE KEYS */;
/*!40000 ALTER TABLE `slot_registrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tai_lieu_dinh_kem`
--

DROP TABLE IF EXISTS `tai_lieu_dinh_kem`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tai_lieu_dinh_kem` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `lesson_id` bigint(20) unsigned DEFAULT NULL,
  `quiz_id` bigint(20) unsigned DEFAULT NULL,
  `filename` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `mime_type` varchar(255) NOT NULL,
  `file_size` bigint(20) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tai_lieu_dinh_kem_quiz_id_foreign` (`quiz_id`),
  KEY `tai_lieu_dinh_kem_lesson_id_quiz_id_index` (`lesson_id`,`quiz_id`),
  CONSTRAINT `tai_lieu_dinh_kem_lesson_id_foreign` FOREIGN KEY (`lesson_id`) REFERENCES `bai_hoc` (`id`) ON DELETE CASCADE,
  CONSTRAINT `tai_lieu_dinh_kem_quiz_id_foreign` FOREIGN KEY (`quiz_id`) REFERENCES `bai_kiem_tra` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tai_lieu_dinh_kem`
--

LOCK TABLES `tai_lieu_dinh_kem` WRITE;
/*!40000 ALTER TABLE `tai_lieu_dinh_kem` DISABLE KEYS */;
/*!40000 ALTER TABLE `tai_lieu_dinh_kem` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `teacher_evaluations`
--

DROP TABLE IF EXISTS `teacher_evaluations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `teacher_evaluations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `class_room_id` bigint(20) unsigned NOT NULL,
  `student_id` bigint(20) unsigned NOT NULL,
  `teacher_id` bigint(20) unsigned NOT NULL,
  `rating` tinyint(3) unsigned NOT NULL,
  `comments` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `teacher_evaluations_class_student_unique` (`class_room_id`,`student_id`),
  KEY `teacher_evaluations_student_id_foreign` (`student_id`),
  KEY `teacher_evaluations_teacher_id_rating_index` (`teacher_id`,`rating`),
  CONSTRAINT `teacher_evaluations_class_room_id_foreign` FOREIGN KEY (`class_room_id`) REFERENCES `lop_hoc` (`id`) ON DELETE CASCADE,
  CONSTRAINT `teacher_evaluations_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `nguoi_dung` (`id`) ON DELETE CASCADE,
  CONSTRAINT `teacher_evaluations_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `nguoi_dung` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `teacher_evaluations`
--

LOCK TABLES `teacher_evaluations` WRITE;
/*!40000 ALTER TABLE `teacher_evaluations` DISABLE KEYS */;
/*!40000 ALTER TABLE `teacher_evaluations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `thong_bao`
--

DROP TABLE IF EXISTS `thong_bao`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `thong_bao` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `type` varchar(255) NOT NULL DEFAULT 'info',
  `link` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `thong_bao_user_id_is_read_index` (`user_id`,`is_read`),
  CONSTRAINT `thong_bao_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `nguoi_dung` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `thong_bao`
--

LOCK TABLES `thong_bao` WRITE;
/*!40000 ALTER TABLE `thong_bao` DISABLE KEYS */;
/*!40000 ALTER TABLE `thong_bao` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `thong_bao_chung`
--

DROP TABLE IF EXISTS `thong_bao_chung`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `thong_bao_chung` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `created_by` bigint(20) unsigned NOT NULL,
  `course_id` bigint(20) unsigned DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `is_pinned` tinyint(1) NOT NULL DEFAULT 0,
  `published_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `status` enum('draft','published') NOT NULL DEFAULT 'draft',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `thong_bao_chung_created_by_foreign` (`created_by`),
  KEY `thong_bao_chung_course_id_published_at_index` (`course_id`,`published_at`),
  CONSTRAINT `thong_bao_chung_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `khoa_hoc` (`id`) ON DELETE CASCADE,
  CONSTRAINT `thong_bao_chung_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `nguoi_dung` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `thong_bao_chung`
--

LOCK TABLES `thong_bao_chung` WRITE;
/*!40000 ALTER TABLE `thong_bao_chung` DISABLE KEYS */;
/*!40000 ALTER TABLE `thong_bao_chung` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tien_do_bai_hoc`
--

DROP TABLE IF EXISTS `tien_do_bai_hoc`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tien_do_bai_hoc` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `lesson_id` bigint(20) unsigned NOT NULL,
  `is_completed` tinyint(1) NOT NULL DEFAULT 0,
  `time_spent` int(11) NOT NULL DEFAULT 0,
  `started_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tien_do_bai_hoc_user_id_lesson_id_unique` (`user_id`,`lesson_id`),
  KEY `tien_do_bai_hoc_lesson_id_foreign` (`lesson_id`),
  KEY `tien_do_bai_hoc_user_id_is_completed_index` (`user_id`,`is_completed`),
  CONSTRAINT `tien_do_bai_hoc_lesson_id_foreign` FOREIGN KEY (`lesson_id`) REFERENCES `bai_hoc` (`id`) ON DELETE CASCADE,
  CONSTRAINT `tien_do_bai_hoc_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `nguoi_dung` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tien_do_bai_hoc`
--

LOCK TABLES `tien_do_bai_hoc` WRITE;
/*!40000 ALTER TABLE `tien_do_bai_hoc` DISABLE KEYS */;
/*!40000 ALTER TABLE `tien_do_bai_hoc` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tra_loi_kiem_tra`
--

DROP TABLE IF EXISTS `tra_loi_kiem_tra`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tra_loi_kiem_tra` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `quiz_id` bigint(20) unsigned NOT NULL,
  `question_id` bigint(20) unsigned NOT NULL,
  `option_id` bigint(20) unsigned DEFAULT NULL,
  `answer_text` text DEFAULT NULL,
  `is_correct` tinyint(1) NOT NULL DEFAULT 0,
  `attempt` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tra_loi_kiem_tra_quiz_id_foreign` (`quiz_id`),
  KEY `tra_loi_kiem_tra_question_id_foreign` (`question_id`),
  KEY `tra_loi_kiem_tra_option_id_foreign` (`option_id`),
  KEY `tra_loi_kiem_tra_user_id_quiz_id_index` (`user_id`,`quiz_id`),
  CONSTRAINT `tra_loi_kiem_tra_option_id_foreign` FOREIGN KEY (`option_id`) REFERENCES `lua_chon` (`id`) ON DELETE SET NULL,
  CONSTRAINT `tra_loi_kiem_tra_question_id_foreign` FOREIGN KEY (`question_id`) REFERENCES `cau_hoi` (`id`) ON DELETE CASCADE,
  CONSTRAINT `tra_loi_kiem_tra_quiz_id_foreign` FOREIGN KEY (`quiz_id`) REFERENCES `bai_kiem_tra` (`id`) ON DELETE CASCADE,
  CONSTRAINT `tra_loi_kiem_tra_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `nguoi_dung` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tra_loi_kiem_tra`
--

LOCK TABLES `tra_loi_kiem_tra` WRITE;
/*!40000 ALTER TABLE `tra_loi_kiem_tra` DISABLE KEYS */;
/*!40000 ALTER TABLE `tra_loi_kiem_tra` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `yeu_cau_xin_phep`
--

DROP TABLE IF EXISTS `yeu_cau_xin_phep`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `yeu_cau_xin_phep` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `student_id` bigint(20) unsigned NOT NULL,
  `teacher_id` bigint(20) unsigned DEFAULT NULL,
  `enrollment_id` bigint(20) unsigned DEFAULT NULL,
  `course_id` bigint(20) unsigned DEFAULT NULL,
  `class_room_id` bigint(20) unsigned DEFAULT NULL,
  `class_schedule_id` bigint(20) unsigned DEFAULT NULL,
  `attendance_date` date NOT NULL,
  `reason` text NOT NULL,
  `note` text DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `teacher_note` text DEFAULT NULL,
  `reviewed_by` bigint(20) unsigned DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `yeu_cau_xin_phep_enrollment_id_foreign` (`enrollment_id`),
  KEY `yeu_cau_xin_phep_course_id_foreign` (`course_id`),
  KEY `yeu_cau_xin_phep_class_schedule_id_foreign` (`class_schedule_id`),
  KEY `yeu_cau_xin_phep_reviewed_by_foreign` (`reviewed_by`),
  KEY `yeu_cau_xin_phep_student_id_status_index` (`student_id`,`status`),
  KEY `yeu_cau_xin_phep_teacher_id_status_index` (`teacher_id`,`status`),
  KEY `yeu_cau_xin_phep_class_room_id_attendance_date_index` (`class_room_id`,`attendance_date`),
  CONSTRAINT `yeu_cau_xin_phep_class_room_id_foreign` FOREIGN KEY (`class_room_id`) REFERENCES `lop_hoc` (`id`) ON DELETE CASCADE,
  CONSTRAINT `yeu_cau_xin_phep_class_schedule_id_foreign` FOREIGN KEY (`class_schedule_id`) REFERENCES `lich_hoc` (`id`) ON DELETE SET NULL,
  CONSTRAINT `yeu_cau_xin_phep_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `khoa_hoc` (`id`) ON DELETE CASCADE,
  CONSTRAINT `yeu_cau_xin_phep_enrollment_id_foreign` FOREIGN KEY (`enrollment_id`) REFERENCES `dang_ky` (`id`) ON DELETE CASCADE,
  CONSTRAINT `yeu_cau_xin_phep_reviewed_by_foreign` FOREIGN KEY (`reviewed_by`) REFERENCES `nguoi_dung` (`id`) ON DELETE SET NULL,
  CONSTRAINT `yeu_cau_xin_phep_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `nguoi_dung` (`id`) ON DELETE CASCADE,
  CONSTRAINT `yeu_cau_xin_phep_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `nguoi_dung` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `yeu_cau_xin_phep`
--

LOCK TABLES `yeu_cau_xin_phep` WRITE;
/*!40000 ALTER TABLE `yeu_cau_xin_phep` DISABLE KEYS */;
/*!40000 ALTER TABLE `yeu_cau_xin_phep` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-04-28 16:09:03
