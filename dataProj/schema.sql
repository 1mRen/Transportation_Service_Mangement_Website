-- MySQL dump 10.13  Distrib 8.0.41, for Win64 (x86_64)
--
-- Host: localhost    Database: transpodb
-- ------------------------------------------------------
-- Server version	8.0.41

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `applicant`
--

DROP TABLE IF EXISTS `applicant`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `applicant` (
  `applicant_id` int NOT NULL AUTO_INCREMENT,
  `full_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `organization_department` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `position` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `contact_no` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `user_id` int DEFAULT NULL,
  PRIMARY KEY (`applicant_id`),
  KEY `idx_applicant_user_id` (`user_id`),
  CONSTRAINT `fk_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `applicant`
--

LOCK TABLES `applicant` WRITE;
/*!40000 ALTER TABLE `applicant` DISABLE KEYS */;
INSERT INTO `applicant` VALUES (1,'Test Name','Test Department','Manager','1234567890','test@example.com','2025-03-18 19:32:48','2025-03-18 19:32:48',3),(3,'Marc','test','Assoc','09610928279','marc@email.com','2025-03-18 19:59:38','2025-03-18 20:42:13',3),(4,'Jairus','IMS','Working Scholar','09610928279','andrade@email.com','2025-03-18 23:35:35','2025-03-19 02:35:09',4);
/*!40000 ALTER TABLE `applicant` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `assignment_status`
--

DROP TABLE IF EXISTS `assignment_status`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `assignment_status` (
  `status_id` int NOT NULL AUTO_INCREMENT,
  `status_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`status_id`),
  UNIQUE KEY `status_name` (`status_name`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `assignment_status`
--

LOCK TABLES `assignment_status` WRITE;
/*!40000 ALTER TABLE `assignment_status` DISABLE KEYS */;
INSERT INTO `assignment_status` VALUES (1,'Active'),(3,'Cancelled'),(4,'completed'),(2,'Pending');
/*!40000 ALTER TABLE `assignment_status` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
INSERT INTO `cache` VALUES ('laravel_cache_admin@gmail.com|127.0.0.1','i:1;',1743694922),('laravel_cache_admin@gmail.com|127.0.0.1:timer','i:1743694922;',1743694922);
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
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
-- Table structure for table `driver`
--

DROP TABLE IF EXISTS `driver`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `driver` (
  `driver_id` int NOT NULL AUTO_INCREMENT,
  `full_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `age` int NOT NULL,
  `driver_license_no` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `contact_no` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status_id` int NOT NULL,
  `profile_pic_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'URL path to driver profile picture',
  PRIMARY KEY (`driver_id`),
  UNIQUE KEY `driver_license_no` (`driver_license_no`),
  KEY `status_id` (`status_id`),
  CONSTRAINT `driver_ibfk_1` FOREIGN KEY (`status_id`) REFERENCES `driver_status` (`status_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `driver`
--

LOCK TABLES `driver` WRITE;
/*!40000 ALTER TABLE `driver` DISABLE KEYS */;
/*!40000 ALTER TABLE `driver` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `driver_status`
--

DROP TABLE IF EXISTS `driver_status`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `driver_status` (
  `status_id` int NOT NULL AUTO_INCREMENT,
  `status_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`status_id`),
  UNIQUE KEY `status_name` (`status_name`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `driver_status`
--

LOCK TABLES `driver_status` WRITE;
/*!40000 ALTER TABLE `driver_status` DISABLE KEYS */;
INSERT INTO `driver_status` VALUES (1,'Active'),(2,'On Leave'),(3,'Retired');
/*!40000 ALTER TABLE `driver_status` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=117 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'2025_03_13_042332_create_applicant_table',0),(2,'2025_03_13_042332_create_assignment_status_table',0),(3,'2025_03_13_042332_create_driver_table',0),(4,'2025_03_13_042332_create_driver_status_table',0),(5,'2025_03_13_042332_create_reservation_table',0),(6,'2025_03_13_042332_create_reservation_status_table',0),(7,'2025_03_13_042332_create_reserve_status_history_table',0),(8,'2025_03_13_042332_create_users_table',0),(9,'2025_03_13_042332_create_vehicle_table',0),(10,'2025_03_13_042332_create_vehicle_assignment_table',0),(11,'2025_03_13_042332_create_vehicle_status_table',0),(12,'2025_03_13_042334_create_DescribeAllTables_proc',0),(13,'2025_03_13_042334_create_ShowAllTableDescriptions_proc',0),(14,'2025_03_13_042335_add_foreign_keys_to_driver_table',0),(15,'2025_03_13_042335_add_foreign_keys_to_reservation_table',0),(16,'2025_03_13_042335_add_foreign_keys_to_reserve_status_history_table',0),(17,'2025_03_13_042335_add_foreign_keys_to_users_table',0),(18,'2025_03_13_042335_add_foreign_keys_to_vehicle_table',0),(19,'2025_03_13_042335_add_foreign_keys_to_vehicle_assignment_table',0),(20,'2025_03_13_043247_create_applicant_table',0),(21,'2025_03_13_043247_create_assignment_status_table',0),(22,'2025_03_13_043247_create_driver_table',0),(23,'2025_03_13_043247_create_driver_status_table',0),(24,'2025_03_13_043247_create_reservation_table',0),(25,'2025_03_13_043247_create_reservation_status_table',0),(26,'2025_03_13_043247_create_reserve_status_history_table',0),(27,'2025_03_13_043247_create_users_table',0),(28,'2025_03_13_043247_create_vehicle_table',0),(29,'2025_03_13_043247_create_vehicle_assignment_table',0),(30,'2025_03_13_043247_create_vehicle_status_table',0),(31,'2025_03_13_043249_create_DescribeAllTables_proc',0),(32,'2025_03_13_043249_create_ShowAllTableDescriptions_proc',0),(33,'2025_03_13_043250_add_foreign_keys_to_driver_table',0),(34,'2025_03_13_043250_add_foreign_keys_to_reservation_table',0),(35,'2025_03_13_043250_add_foreign_keys_to_reserve_status_history_table',0),(36,'2025_03_13_043250_add_foreign_keys_to_users_table',0),(37,'2025_03_13_043250_add_foreign_keys_to_vehicle_table',0),(38,'2025_03_13_043250_add_foreign_keys_to_vehicle_assignment_table',0),(39,'2025_03_13_082226_create_applicant_table',0),(40,'2025_03_13_082226_create_assignment_status_table',0),(41,'2025_03_13_082226_create_driver_table',0),(42,'2025_03_13_082226_create_driver_status_table',0),(43,'2025_03_13_082226_create_reservation_table',0),(44,'2025_03_13_082226_create_reservation_status_table',0),(45,'2025_03_13_082226_create_reserve_status_history_table',0),(46,'2025_03_13_082226_create_users_table',0),(47,'2025_03_13_082226_create_vehicle_table',0),(48,'2025_03_13_082226_create_vehicle_assignment_table',0),(49,'2025_03_13_082226_create_vehicle_status_table',0),(50,'2025_03_13_082228_create_DescribeAllTables_proc',0),(51,'2025_03_13_082228_create_ShowAllTableDescriptions_proc',0),(52,'2025_03_13_082229_add_foreign_keys_to_driver_table',0),(53,'2025_03_13_082229_add_foreign_keys_to_reservation_table',0),(54,'2025_03_13_082229_add_foreign_keys_to_reserve_status_history_table',0),(55,'2025_03_13_082229_add_foreign_keys_to_users_table',0),(56,'2025_03_13_082229_add_foreign_keys_to_vehicle_table',0),(57,'2025_03_13_082229_add_foreign_keys_to_vehicle_assignment_table',0),(58,'2025_03_13_085204_create_applicant_table',0),(59,'2025_03_13_085204_create_assignment_status_table',0),(60,'2025_03_13_085204_create_driver_table',0),(61,'2025_03_13_085204_create_driver_status_table',0),(62,'2025_03_13_085204_create_reservation_table',0),(63,'2025_03_13_085204_create_reservation_status_table',0),(64,'2025_03_13_085204_create_reserve_status_history_table',0),(65,'2025_03_13_085204_create_users_table',0),(66,'2025_03_13_085204_create_vehicle_table',0),(67,'2025_03_13_085204_create_vehicle_assignment_table',0),(68,'2025_03_13_085204_create_vehicle_status_table',0),(69,'2025_03_13_085206_create_DescribeAllTables_proc',0),(70,'2025_03_13_085206_create_ShowAllTableDescriptions_proc',0),(71,'2025_03_13_085207_add_foreign_keys_to_driver_table',0),(72,'2025_03_13_085207_add_foreign_keys_to_reservation_table',0),(73,'2025_03_13_085207_add_foreign_keys_to_reserve_status_history_table',0),(74,'2025_03_13_085207_add_foreign_keys_to_users_table',0),(75,'2025_03_13_085207_add_foreign_keys_to_vehicle_table',0),(76,'2025_03_13_085207_add_foreign_keys_to_vehicle_assignment_table',0),(96,'2025_03_13_115139_create_sessions_table',1),(97,'2025_03_13_115152_create_applicant_table',1),(98,'2025_03_13_115152_create_assignment_status_table',1),(99,'2025_03_13_115152_create_driver_status_table',1),(100,'2025_03_13_115152_create_driver_table',1),(101,'2025_03_13_115152_create_reservation_status_table',1),(102,'2025_03_13_115152_create_reservation_table',1),(103,'2025_03_13_115152_create_reserve_status_history_table',1),(104,'2025_03_13_115152_create_users_table',1),(105,'2025_03_13_115152_create_vehicle_assignment_table',1),(106,'2025_03_13_115152_create_vehicle_status_table',1),(107,'2025_03_13_115152_create_vehicle_table',1),(108,'2025_03_13_115154_create_DescribeAllTables_proc',1),(109,'2025_03_13_115154_create_ShowAllTableDescriptions_proc',1),(110,'2025_03_13_115155_add_foreign_keys_to_driver_table',1),(111,'2025_03_13_115155_add_foreign_keys_to_reservation_table',1),(112,'2025_03_13_115155_add_foreign_keys_to_reserve_status_history_table',1),(113,'2025_03_13_115155_add_foreign_keys_to_users_table',1),(114,'2025_03_13_115155_add_foreign_keys_to_vehicle_assignment_table',1),(115,'2025_03_13_115155_add_foreign_keys_to_vehicle_table',1),(116,'2025_04_03_154046_create_cache_table',2);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reservation`
--

DROP TABLE IF EXISTS `reservation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reservation` (
  `reservation_id` int NOT NULL AUTO_INCREMENT,
  `applicant_id` int NOT NULL,
  `vehicle_id` int NOT NULL,
  `date_of_use` date NOT NULL,
  `departure_area` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `destination` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `departure_time` time NOT NULL,
  `return_time` time NOT NULL,
  `purpose` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `status_id` int DEFAULT '1',
  PRIMARY KEY (`reservation_id`),
  KEY `applicant_id` (`applicant_id`),
  KEY `vehicle_id` (`vehicle_id`),
  KEY `status_id` (`status_id`),
  KEY `idx_reservation_applicant_id` (`applicant_id`),
  CONSTRAINT `reservation_ibfk_1` FOREIGN KEY (`applicant_id`) REFERENCES `applicant` (`applicant_id`),
  CONSTRAINT `reservation_ibfk_2` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicle` (`vehicle_id`),
  CONSTRAINT `reservation_ibfk_3` FOREIGN KEY (`status_id`) REFERENCES `reservation_status` (`status_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reservation`
--

LOCK TABLES `reservation` WRITE;
/*!40000 ALTER TABLE `reservation` DISABLE KEYS */;
/*!40000 ALTER TABLE `reservation` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reservation_documents`
--

DROP TABLE IF EXISTS `reservation_documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reservation_documents` (
  `document_id` int NOT NULL AUTO_INCREMENT,
  `reservation_id` int NOT NULL,
  `document_name` varchar(255) NOT NULL,
  `document_type` varchar(100) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_size` int NOT NULL,
  `uploaded_by` int NOT NULL,
  `upload_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `notes` text,
  PRIMARY KEY (`document_id`),
  KEY `uploaded_by` (`uploaded_by`),
  KEY `idx_reservation_documents` (`reservation_id`),
  CONSTRAINT `reservation_documents_ibfk_1` FOREIGN KEY (`reservation_id`) REFERENCES `reservation` (`reservation_id`) ON DELETE CASCADE,
  CONSTRAINT `reservation_documents_ibfk_2` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reservation_documents`
--

LOCK TABLES `reservation_documents` WRITE;
/*!40000 ALTER TABLE `reservation_documents` DISABLE KEYS */;
/*!40000 ALTER TABLE `reservation_documents` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reservation_status`
--

DROP TABLE IF EXISTS `reservation_status`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reservation_status` (
  `status_id` int NOT NULL AUTO_INCREMENT,
  `status_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`status_id`),
  UNIQUE KEY `status_name` (`status_name`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reservation_status`
--

LOCK TABLES `reservation_status` WRITE;
/*!40000 ALTER TABLE `reservation_status` DISABLE KEYS */;
INSERT INTO `reservation_status` VALUES (2,'Approved'),(4,'Completed'),(1,'Pending'),(3,'Rejected');
/*!40000 ALTER TABLE `reservation_status` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reserve_status_history`
--

DROP TABLE IF EXISTS `reserve_status_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reserve_status_history` (
  `history_id` int NOT NULL AUTO_INCREMENT,
  `reservation_id` int NOT NULL,
  `status_id` int NOT NULL,
  `updated_by` int NOT NULL,
  `timestamp` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`history_id`),
  KEY `reservation_id` (`reservation_id`),
  KEY `status_id` (`status_id`),
  KEY `updated_by` (`updated_by`),
  CONSTRAINT `reserve_status_history_ibfk_1` FOREIGN KEY (`reservation_id`) REFERENCES `reservation` (`reservation_id`),
  CONSTRAINT `reserve_status_history_ibfk_2` FOREIGN KEY (`status_id`) REFERENCES `reservation_status` (`status_id`),
  CONSTRAINT `reserve_status_history_ibfk_3` FOREIGN KEY (`updated_by`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reserve_status_history`
--

LOCK TABLES `reserve_status_history` WRITE;
/*!40000 ALTER TABLE `reserve_status_history` DISABLE KEYS */;
/*!40000 ALTER TABLE `reserve_status_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
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
INSERT INTO `sessions` VALUES ('LDKk9gv0hVfJeWvKNFdgPPRai6IFbwe895BHgXbm',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Safari/537.36 OPR/117.0.0.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoidmdTSm1WbXV6WlBZbVRySmIzTXM4VU5QejZISzRnb0t4RHl0T3BnSCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1743694892);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `user_id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('Admin','Applicant') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Applicant',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `profile_pic_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'URL path to user profile picture',
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Marc Lawrence Magadan','lawrence@gmail.com','lawrence','$2y$12$SmjMR/D8jDF6mttEQ/KoIO3mwQpUSTtvI3cy9El18PaMPkkCcBFoe','Applicant','2025-03-13 09:21:40','2025-03-13 09:21:40',NULL),(2,'testing-admin','admin@gmail.com','test-admin','$2y$12$l5pARGaxOoiQz6q9/.j9y.Z87Cb26PVM.hSX99NZGLU8Z4jcHuBrq','Admin','2025-03-13 09:47:27','2025-03-13 09:47:27',NULL),(3,'new','new@email.com','new1','$2y$12$.GAkRTyseYNGqSAJWFIGB.cowhgl1TXelc0rKRdrH2jUhy0jHgWCm','Applicant','2025-03-18 19:22:38','2025-03-18 19:22:38',NULL),(4,'Marc','test2@email.com','new2','$2y$12$WFJRPfCvEphEO8vQm.x/2eoH66N3pOdnomWb7bbPuPr6D8tgPW/ai','Applicant','2025-03-18 23:28:49','2025-03-18 23:28:49',NULL),(5,'Marc Lawrence Magadan','madmin@email.com','marc-admin','$2y$12$kzAJWCHpHua460izle5lfOuntqjCeCuuRFAyIilhMMgzQ7Cw7HrcG','Admin','2025-04-03 05:52:59','2025-04-03 05:52:59',NULL),(6,'testing100','testing100@admin.org','admin','$2y$10$stdJ.Ys4BnMJM2P.hVokU.6CzJoYkDo27Jf9SR7SSFRa9ZA8g5gru','Admin','2025-04-08 08:50:36','2025-04-08 08:49:42',NULL),(7,'user testing','usertest@gmail.com','test-user1','$2y$10$bCxxWSdLwU4ObiEGbu.DbebMRNZZsNrG48gaR5An55YQWr7vp6n2W','Applicant','2025-04-08 08:50:36','2025-04-08 08:49:42',NULL),(8,'User Testing2','usertest2@gmail.com','user-test2','$2y$10$aQrYg1xqcUJDM3Bx5VpmieB4HXikTRCeAZxLhiH59XZyqi/2LvXyq','Applicant','2025-04-08 08:52:04','2025-04-08 08:52:04',NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vehicle`
--

DROP TABLE IF EXISTS `vehicle`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `vehicle` (
  `vehicle_id` int NOT NULL AUTO_INCREMENT,
  `plate_no` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type_of_vehicle` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `capacity` int NOT NULL,
  `status_id` int NOT NULL DEFAULT '1',
  PRIMARY KEY (`vehicle_id`),
  UNIQUE KEY `plate_no` (`plate_no`),
  KEY `fk_vehicle_status` (`status_id`),
  CONSTRAINT `fk_vehicle_status` FOREIGN KEY (`status_id`) REFERENCES `vehicle_status` (`status_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vehicle`
--

LOCK TABLES `vehicle` WRITE;
/*!40000 ALTER TABLE `vehicle` DISABLE KEYS */;
INSERT INTO `vehicle` VALUES (1,'VAN 3190','Van',12,2),(2,'TEST 3999','Bus',55,3);
/*!40000 ALTER TABLE `vehicle` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vehicle_assignment`
--

DROP TABLE IF EXISTS `vehicle_assignment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `vehicle_assignment` (
  `assignment_id` int NOT NULL AUTO_INCREMENT,
  `vehicle_id` int NOT NULL,
  `driver_id` int NOT NULL,
  `assigned_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `status_id` int DEFAULT '1',
  PRIMARY KEY (`assignment_id`),
  KEY `vehicle_id` (`vehicle_id`),
  KEY `driver_id` (`driver_id`),
  KEY `status_id` (`status_id`),
  CONSTRAINT `vehicle_assignment_ibfk_1` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicle` (`vehicle_id`),
  CONSTRAINT `vehicle_assignment_ibfk_2` FOREIGN KEY (`driver_id`) REFERENCES `driver` (`driver_id`),
  CONSTRAINT `vehicle_assignment_ibfk_3` FOREIGN KEY (`status_id`) REFERENCES `assignment_status` (`status_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vehicle_assignment`
--

LOCK TABLES `vehicle_assignment` WRITE;
/*!40000 ALTER TABLE `vehicle_assignment` DISABLE KEYS */;
/*!40000 ALTER TABLE `vehicle_assignment` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vehicle_status`
--

DROP TABLE IF EXISTS `vehicle_status`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `vehicle_status` (
  `status_id` int NOT NULL AUTO_INCREMENT,
  `status_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`status_id`),
  UNIQUE KEY `status_name` (`status_name`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vehicle_status`
--

LOCK TABLES `vehicle_status` WRITE;
/*!40000 ALTER TABLE `vehicle_status` DISABLE KEYS */;
INSERT INTO `vehicle_status` VALUES (1,'Available'),(4,'Decommissioned'),(2,'In Use'),(3,'Under Maintenance');
/*!40000 ALTER TABLE `vehicle_status` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notifications` (
  `notification_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('info','success','warning','danger') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'info',
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`notification_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-04-29 11:12:18
