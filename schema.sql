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
  `full_name` varchar(255) NOT NULL,
  `organization_department` varchar(255) NOT NULL,
  `position` varchar(100) NOT NULL,
  `contact_no` varchar(20) NOT NULL,
  `email` varchar(255) NOT NULL,
  PRIMARY KEY (`applicant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `applicant`
--

LOCK TABLES `applicant` WRITE;
/*!40000 ALTER TABLE `applicant` DISABLE KEYS */;
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
  `status_name` varchar(50) NOT NULL,
  PRIMARY KEY (`status_id`),
  UNIQUE KEY `status_name` (`status_name`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `assignment_status`
--

LOCK TABLES `assignment_status` WRITE;
/*!40000 ALTER TABLE `assignment_status` DISABLE KEYS */;
INSERT INTO `assignment_status` VALUES (1,'Active'),(3,'Cancelled'),(4,'Completed'),(2,'Pending');
/*!40000 ALTER TABLE `assignment_status` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `driver`
--

DROP TABLE IF EXISTS `driver`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `driver` (
  `driver_id` int NOT NULL AUTO_INCREMENT,
  `full_name` varchar(255) NOT NULL,
  `age` int NOT NULL,
  `driver_license_no` varchar(50) NOT NULL,
  `contact_no` varchar(20) NOT NULL,
  `status_id` int NOT NULL,
  PRIMARY KEY (`driver_id`),
  UNIQUE KEY `driver_license_no` (`driver_license_no`),
  KEY `status_id` (`status_id`),
  CONSTRAINT `driver_ibfk_1` FOREIGN KEY (`status_id`) REFERENCES `driver_status` (`status_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
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
  `status_name` varchar(50) NOT NULL,
  PRIMARY KEY (`status_id`),
  UNIQUE KEY `status_name` (`status_name`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
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
  `departure_area` varchar(255) NOT NULL,
  `destination` varchar(255) NOT NULL,
  `departure_time` time NOT NULL,
  `return_time` time NOT NULL,
  `purpose` text NOT NULL,
  `status_id` int DEFAULT '1',
  PRIMARY KEY (`reservation_id`),
  KEY `applicant_id` (`applicant_id`),
  KEY `vehicle_id` (`vehicle_id`),
  KEY `status_id` (`status_id`),
  CONSTRAINT `reservation_ibfk_1` FOREIGN KEY (`applicant_id`) REFERENCES `applicant` (`applicant_id`),
  CONSTRAINT `reservation_ibfk_2` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicle` (`vehicle_id`),
  CONSTRAINT `reservation_ibfk_3` FOREIGN KEY (`status_id`) REFERENCES `reservation_status` (`status_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reservation`
--

LOCK TABLES `reservation` WRITE;
/*!40000 ALTER TABLE `reservation` DISABLE KEYS */;
/*!40000 ALTER TABLE `reservation` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reservation_status`
--

DROP TABLE IF EXISTS `reservation_status`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reservation_status` (
  `status_id` int NOT NULL AUTO_INCREMENT,
  `status_name` varchar(50) NOT NULL,
  PRIMARY KEY (`status_id`),
  UNIQUE KEY `status_name` (`status_name`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reserve_status_history`
--

LOCK TABLES `reserve_status_history` WRITE;
/*!40000 ALTER TABLE `reserve_status_history` DISABLE KEYS */;
/*!40000 ALTER TABLE `reserve_status_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `user_id` int NOT NULL AUTO_INCREMENT,
  `full_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('Admin','Applicant') NOT NULL DEFAULT 'Applicant',
  `applicant_id` int DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `username` (`username`),
  KEY `applicant_id` (`applicant_id`),
  CONSTRAINT `users_ibfk_1` FOREIGN KEY (`applicant_id`) REFERENCES `applicant` (`applicant_id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Marc','trst@gmail.com','idzty','$2y$10$Zi3VeLlcvXiUxyszzTdrguFsLxSbrjC4MaUGzmz08NyhccQPfikqa','Admin',NULL),(2,'Jai Azi Andade','asar@gmail.com','batoy','$2y$10$wuRyBqVFep31rpCNswQoFuAHrcbOG032Ni86cNJBw.MWhGPLIsUSe','Applicant',NULL),(3,'Marc','test@gmail.com','test123','$2y$10$jP2AT4v2KCvO.Kd7lfMRNeckvHHW2mqF98GEbRXTTyHdnJ7KyAhWi','Applicant',NULL),(4,'test23','test23@gmail.com','test3','$2y$10$IJZMF0QoGb4KvEeNCwJ/C.iaLsuYl4MncGW/XOxOkQG8uemaQKJvC','Applicant',NULL),(6,'test23','test2@gmail.com','test-admin','$2y$10$STirPXRbN8Oi8Pc9p8pSIOdqgpbS3kbZrGMOJ2Sre3rdAO7q6MYDq','Applicant',NULL),(7,'test4','test4@admin.com','test4','$2y$10$khd4Wr6eE64KlmNsLBkO2e2BU5OLoTX6rK5xfMrb2qUfxxBMY8e5S','Applicant',NULL),(8,'test5','test5@admin.org','test5','$2y$10$k7YSFiWWk4QiNiJf.drdT.A.T6KhbiyZ5rzlBLYcMVhO927Uy8mzC','Admin',NULL),(10,'test6','test6@admin.org','test6','$2y$10$zCR8HtOfviNYvGYS0xfoku43jwobuvxCFvGJagLeVfKwbAogvlj0i','Admin',NULL),(11,'test7','test7@gmail.com','test7','$2y$10$EQXT.kDgAn4Nl.fs/gKaqOC3PR6E8feoj0WP.k23JrvLYn744U56e','Applicant',NULL),(12,'jairus','jairusgae@company.com','gae123','$2y$10$fUmlJa9M7NrSnd4IaLiYJOZufQo0S3.12vIlCvgOEb4dpfXSyoeia','Admin',NULL),(13,'momo','momo@admin.org','momo123','$2y$10$OM82Mxv8IRi/3d/I77p6UuAmxpM/TIvgXBrTQz7BjA6VptmchLmPq','Admin',NULL);
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
  `plate_no` varchar(50) NOT NULL,
  `type_of_vehicle` varchar(100) NOT NULL,
  `capacity` int NOT NULL,
  `status_id` int NOT NULL DEFAULT '1',
  PRIMARY KEY (`vehicle_id`),
  UNIQUE KEY `plate_no` (`plate_no`),
  KEY `fk_vehicle_status` (`status_id`),
  CONSTRAINT `fk_vehicle_status` FOREIGN KEY (`status_id`) REFERENCES `vehicle_status` (`status_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vehicle`
--

LOCK TABLES `vehicle` WRITE;
/*!40000 ALTER TABLE `vehicle` DISABLE KEYS */;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
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
  `status_name` varchar(50) NOT NULL,
  PRIMARY KEY (`status_id`),
  UNIQUE KEY `status_name` (`status_name`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vehicle_status`
--

LOCK TABLES `vehicle_status` WRITE;
/*!40000 ALTER TABLE `vehicle_status` DISABLE KEYS */;
INSERT INTO `vehicle_status` VALUES (1,'Available'),(4,'Decommissioned'),(2,'In Use'),(3,'Under Maintenance');
/*!40000 ALTER TABLE `vehicle_status` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-02-27 20:35:16
