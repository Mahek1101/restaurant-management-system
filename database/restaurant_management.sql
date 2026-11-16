-- MySQL dump 10.13  Distrib 9.4.0, for macos15.4 (arm64)
--
-- Host: 127.0.0.1    Database: restaurant_management
-- ------------------------------------------------------
-- Server version	9.4.0

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
-- Current Database: `restaurant_management`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `restaurant_management` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;

USE `restaurant_management`;

--
-- Table structure for table `Chef`
--

DROP TABLE IF EXISTS `Chef`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Chef` (
  `employee_id` int NOT NULL,
  `specialization` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`employee_id`),
  CONSTRAINT `chef_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `Employee` (`employee_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Chef`
--

LOCK TABLES `Chef` WRITE;
/*!40000 ALTER TABLE `Chef` DISABLE KEYS */;
INSERT INTO `Chef` VALUES (4,'Italian Cuisine'),(5,'Indian Cuisine'),(6,'Desserts');
/*!40000 ALTER TABLE `Chef` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Customer`
--

DROP TABLE IF EXISTS `Customer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Customer` (
  `customer_id` int NOT NULL AUTO_INCREMENT,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`customer_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Customer`
--

LOCK TABLES `Customer` WRITE;
/*!40000 ALTER TABLE `Customer` DISABLE KEYS */;
INSERT INTO `Customer` VALUES (1,'John','Smith','555-0101','john.smith@example.com','2026-11-10 03:58:33'),(2,'Sarah','Johnson','555-0102','sarah.johnson@example.com','2026-11-10 03:58:33'),(3,'Michael','Brown','555-0103','michael.brown@example.com','2026-11-10 03:58:33'),(4,'Emily','Davis','555-0104','emily.davis@example.com','2026-11-10 03:58:33'),(5,'David','Wilson','555-0105','david.wilson@example.com','2026-11-10 03:58:33'),(6,'Alexander','Parker','574-456-2345','alex@gmail.com','2026-11-10 20:54:51');
/*!40000 ALTER TABLE `Customer` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `DiningTable`
--

DROP TABLE IF EXISTS `DiningTable`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `DiningTable` (
  `table_id` int NOT NULL AUTO_INCREMENT,
  `table_number` int NOT NULL,
  `capacity` int NOT NULL,
  `section` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`table_id`),
  UNIQUE KEY `table_number` (`table_number`),
  CONSTRAINT `diningtable_chk_1` CHECK ((`capacity` > 0))
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `DiningTable`
--

LOCK TABLES `DiningTable` WRITE;
/*!40000 ALTER TABLE `DiningTable` DISABLE KEYS */;
INSERT INTO `DiningTable` VALUES (1,1,2,'Indoor'),(2,2,4,'Indoor'),(3,3,6,'Indoor'),(4,4,8,'Indoor'),(5,5,2,'Outdoor'),(6,6,4,'Outdoor'),(7,7,6,'Patio'),(8,8,8,'Patio');
/*!40000 ALTER TABLE `DiningTable` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Employee`
--

DROP TABLE IF EXISTS `Employee`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Employee` (
  `employee_id` int NOT NULL AUTO_INCREMENT,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `hire_date` date NOT NULL,
  `employee_type` enum('Server','Chef') NOT NULL,
  PRIMARY KEY (`employee_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Employee`
--

LOCK TABLES `Employee` WRITE;
/*!40000 ALTER TABLE `Employee` DISABLE KEYS */;
INSERT INTO `Employee` VALUES (1,'James','Anderson','555-0201','james.anderson@example.com','2024-01-15','Server'),(2,'Olivia','Martinez','555-0202','olivia.martinez@example.com','2024-03-10','Server'),(3,'Daniel','Thomas','555-0203','daniel.thomas@example.com','2025-02-20','Server'),(4,'Sophia','Garcia','555-0204','sophia.garcia@example.com','2023-06-12','Chef'),(5,'William','Taylor','555-0205','william.taylor@example.com','2024-08-05','Chef'),(6,'Emma','Robinson','555-0206','emma.robinson@example.com','2025-01-18','Chef');
/*!40000 ALTER TABLE `Employee` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `FoodOrder`
--

DROP TABLE IF EXISTS `FoodOrder`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `FoodOrder` (
  `order_id` int NOT NULL AUTO_INCREMENT,
  `reservation_id` int NOT NULL,
  `server_id` int NOT NULL,
  `chef_id` int NOT NULL,
  `order_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `total_amount` decimal(10,2) NOT NULL,
  `payment_method` enum('Cash','Credit Card','Debit Card') NOT NULL,
  `order_status` enum('Pending','Preparing','Served','Completed') DEFAULT 'Pending',
  PRIMARY KEY (`order_id`),
  KEY `reservation_id` (`reservation_id`),
  KEY `server_id` (`server_id`),
  KEY `chef_id` (`chef_id`),
  CONSTRAINT `foodorder_ibfk_1` FOREIGN KEY (`reservation_id`) REFERENCES `Reservation` (`reservation_id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `foodorder_ibfk_2` FOREIGN KEY (`server_id`) REFERENCES `Server` (`employee_id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `foodorder_ibfk_3` FOREIGN KEY (`chef_id`) REFERENCES `Chef` (`employee_id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `foodorder_chk_1` CHECK ((`total_amount` >= 0))
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `FoodOrder`
--

LOCK TABLES `FoodOrder` WRITE;
/*!40000 ALTER TABLE `FoodOrder` DISABLE KEYS */;
INSERT INTO `FoodOrder` VALUES (1,1,1,4,'2026-11-09 22:06:53',65.50,'Credit Card','Completed'),(2,2,2,5,'2026-11-09 22:06:53',42.00,'Cash','Completed'),(3,3,3,6,'2026-11-09 22:06:53',98.75,'Debit Card','Pending'),(4,4,1,5,'2026-11-09 22:06:53',74.25,'Credit Card','Preparing'),(5,5,2,4,'2026-11-09 22:06:53',150.00,'Cash','Pending');
/*!40000 ALTER TABLE `FoodOrder` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Reservation`
--

DROP TABLE IF EXISTS `Reservation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Reservation` (
  `reservation_id` int NOT NULL AUTO_INCREMENT,
  `customer_id` int NOT NULL,
  `reservation_date` date NOT NULL,
  `reservation_time` time NOT NULL,
  `party_size` int NOT NULL,
  `status` enum('Pending','Confirmed','Cancelled','Completed') DEFAULT 'Pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`reservation_id`),
  KEY `customer_id` (`customer_id`),
  CONSTRAINT `reservation_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `Customer` (`customer_id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `reservation_chk_1` CHECK ((`party_size` > 0))
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Reservation`
--

LOCK TABLES `Reservation` WRITE;
/*!40000 ALTER TABLE `Reservation` DISABLE KEYS */;
INSERT INTO `Reservation` VALUES (1,1,'2026-11-15','19:00:00',4,'Confirmed','2026-11-10 04:04:23'),(2,2,'2026-11-16','18:30:00',2,'Confirmed','2026-11-10 04:04:23'),(3,3,'2026-11-17','20:00:00',6,'Pending','2026-11-10 04:04:23'),(4,4,'2026-11-18','19:30:00',4,'Confirmed','2026-11-10 04:04:23'),(5,5,'2026-11-19','18:00:00',8,'Pending','2026-11-10 04:04:23');
/*!40000 ALTER TABLE `Reservation` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ReservationTable`
--

DROP TABLE IF EXISTS `ReservationTable`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ReservationTable` (
  `reservation_id` int NOT NULL,
  `table_id` int NOT NULL,
  PRIMARY KEY (`reservation_id`,`table_id`),
  KEY `table_id` (`table_id`),
  CONSTRAINT `reservationtable_ibfk_1` FOREIGN KEY (`reservation_id`) REFERENCES `Reservation` (`reservation_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `reservationtable_ibfk_2` FOREIGN KEY (`table_id`) REFERENCES `DiningTable` (`table_id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ReservationTable`
--

LOCK TABLES `ReservationTable` WRITE;
/*!40000 ALTER TABLE `ReservationTable` DISABLE KEYS */;
INSERT INTO `ReservationTable` VALUES (2,1),(1,2),(3,3),(5,4),(4,6);
/*!40000 ALTER TABLE `ReservationTable` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Server`
--

DROP TABLE IF EXISTS `Server`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Server` (
  `employee_id` int NOT NULL,
  `assigned_section` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`employee_id`),
  CONSTRAINT `server_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `Employee` (`employee_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Server`
--

LOCK TABLES `Server` WRITE;
/*!40000 ALTER TABLE `Server` DISABLE KEYS */;
INSERT INTO `Server` VALUES (1,'Indoor'),(2,'Outdoor'),(3,'Patio');
/*!40000 ALTER TABLE `Server` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'restaurant_management'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-11-16 16:31:37
