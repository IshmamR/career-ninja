-- MySQL dump 10.13  Distrib 8.0.32, for Linux (x86_64)
--
-- Host: localhost    Database: careeer
-- ------------------------------------------------------
-- Server version	8.0.32-0ubuntu0.20.04.2

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
-- Table structure for table `admins`
--

DROP TABLE IF EXISTS `admins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `admins` (
  `id` varchar(36) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admins`
--

LOCK TABLES `admins` WRITE;
/*!40000 ALTER TABLE `admins` DISABLE KEYS */;
/*!40000 ALTER TABLE `admins` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `applicant_skills`
--

DROP TABLE IF EXISTS `applicant_skills`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `applicant_skills` (
  `applicantId` varchar(36) NOT NULL,
  `skillId` varchar(36) NOT NULL,
  PRIMARY KEY (`applicantId`,`skillId`),
  KEY `skillId` (`skillId`),
  CONSTRAINT `applicant_skills_ibfk_1` FOREIGN KEY (`applicantId`) REFERENCES `applicants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `applicant_skills_ibfk_2` FOREIGN KEY (`skillId`) REFERENCES `skills` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `applicant_skills`
--

LOCK TABLES `applicant_skills` WRITE;
/*!40000 ALTER TABLE `applicant_skills` DISABLE KEYS */;
/*!40000 ALTER TABLE `applicant_skills` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `applicants`
--

DROP TABLE IF EXISTS `applicants`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `applicants` (
  `id` varchar(36) NOT NULL,
  `name` varchar(123) NOT NULL,
  `gender` char(1) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(32) NOT NULL,
  `password` char(64) NOT NULL,
  `address` tinytext,
  `resume` tinytext,
  `bio` tinytext,
  `image` tinytext,
  `website` tinytext,
  `education` tinytext,
  `experience` tinytext,
  `interests` tinytext,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `phone` (`phone`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `applicants`
--

LOCK TABLES `applicants` WRITE;
/*!40000 ALTER TABLE `applicants` DISABLE KEYS */;
INSERT INTO `applicants` VALUES ('6c28da74-e774-49a9-bf8f-6ab82df39c44','Ishmam Rahmanur ','m','ishmam785@gmail.com','01775415092','$2b$10$nAbMYhB4KPkWHP1KBPbZhu3nqxcLx9wARSXkPM.bdHZPd9ULjNdHa',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),('7bb906b8-cb78-414a-ba50-9e745b036f95','Ishmam Rahman','','ishmam791@gmail.com','01775415093','$2b$10$.Mfw5WU38Fb3xvc50Qnb7OGONjrYZ1laEu0rSx4dRjZFFaiD2T6MG','Tilpapara,Khilgaon','','helloooo','','ishmam-r.web.app','','',''),('fe248386-56fe-4445-9d4c-a59c57c12b2e','Nabil','','mehedinabil13@gmail.com','01533379952','$2b$10$yvW0ivO2oB7fQAQvjb0HP.wMo2ySTcco.uUjaOnGp0ZObOyA9uNlK','','','Hi! nice to meet you','','','','','');
/*!40000 ALTER TABLE `applicants` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb3 */ ;
/*!50003 SET character_set_results = utf8mb3 */ ;
/*!50003 SET collation_connection  = utf8mb3_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'IGNORE_SPACE,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `before_insert_applicant` BEFORE INSERT ON `applicants` FOR EACH ROW BEGIN
        IF new.gender NOT IN('m', 'f', 'o') THEN
          SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='valid gender required';
        END IF;
      END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Temporary view structure for view `applicants_view`
--

DROP TABLE IF EXISTS `applicants_view`;
/*!50001 DROP VIEW IF EXISTS `applicants_view`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `applicants_view` AS SELECT 
 1 AS `id`,
 1 AS `name`,
 1 AS `gender`,
 1 AS `email`,
 1 AS `phone`,
 1 AS `address`,
 1 AS `resume`,
 1 AS `bio`,
 1 AS `image`,
 1 AS `website`*/;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `applications`
--

DROP TABLE IF EXISTS `applications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `applications` (
  `circularId` varchar(36) NOT NULL,
  `applicantId` varchar(36) NOT NULL,
  `body` text NOT NULL,
  `status` varchar(16) NOT NULL DEFAULT 'active',
  PRIMARY KEY (`circularId`,`applicantId`),
  KEY `applicantId` (`applicantId`),
  CONSTRAINT `applications_ibfk_1` FOREIGN KEY (`circularId`) REFERENCES `circulars` (`id`) ON DELETE CASCADE,
  CONSTRAINT `applications_ibfk_2` FOREIGN KEY (`applicantId`) REFERENCES `applicants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `applications`
--

LOCK TABLES `applications` WRITE;
/*!40000 ALTER TABLE `applications` DISABLE KEYS */;
INSERT INTO `applications` VALUES ('100','fe248386-56fe-4445-9d4c-a59c57c12b2e','Me want job. Hire me','active'),('104','6c28da74-e774-49a9-bf8f-6ab82df39c44','Me want job. Hire me','active');
/*!40000 ALTER TABLE `applications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `circulars`
--

DROP TABLE IF EXISTS `circulars`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `circulars` (
  `id` varchar(36) NOT NULL,
  `companyId` varchar(36) NOT NULL,
  `fieldId` varchar(36) NOT NULL,
  `title` varchar(125) NOT NULL,
  `description` text,
  `role` varchar(64) DEFAULT NULL,
  `location` tinytext,
  `vacancy` int NOT NULL DEFAULT '1',
  `isActive` tinyint(1) DEFAULT '1',
  `salaryRange` tinytext,
  `createdAt` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `companyId` (`companyId`),
  KEY `fieldId` (`fieldId`),
  CONSTRAINT `circulars_ibfk_1` FOREIGN KEY (`companyId`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `circulars_ibfk_2` FOREIGN KEY (`fieldId`) REFERENCES `fields` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `circulars`
--

LOCK TABLES `circulars` WRITE;
/*!40000 ALTER TABLE `circulars` DISABLE KEYS */;
INSERT INTO `circulars` VALUES ('100','1234','5678','hiring a manager','A nice oppurtunity to join us.We are one of the top organization In Bangladesh. We are looking for some manager to help us grow','Manager','Gulshan,Dhaka',2,1,'40000-50000','2023-01-14 09:15:26'),('102','7890','12345','We are hiring Marketing Manager','H. J. Russell & Company, founded over 60 years ago, is a vertically integrated service provider specializing in real estate development, construction, program management, and property management','Marketing Manager','Banani,Dhaka',3,1,'40000-50000','2023-01-14 10:19:28'),('103','7890','23456','We are Looking for HR ','Labbno beauty care is one of the most growing company at right now in Bangladesh.Every member in our company is our family member.We beleive in you','HR','Banani,Dhaka',3,1,'30000-35000','2023-01-14 10:19:28'),('104','1234','5678','We are hiring Software Engineer','we are a  software based comapny, we looking for some young, fresh, energetic software enginineer.Grab the opportunity','Software Engineer','Banani,Dhaka',3,1,'40000-50000','2023-01-14 10:19:28'),('105','1234','12345','We are hiring App developer','Tick tech is a app developing based campany. It founded in 2010. one of the most renowned technology based company in Bangadesh','App developer','Banani,Dhaka',3,1,'40000-50000','2023-01-14 10:21:15'),('106','7890','23456','We are hiring Data analysit','Dhaka tigers is a soprts team.This club played in locally in Bangladesh.For the upcoming tournament we need one data analist','Data analist','Uttara,Dhaka',1,1,'40000-50000','2023-01-14 10:21:15'),('107','1234','5678','We are hiring Sales Manager','Shopno is the top ranked grocery seller in Bangladesh.We have Branch in all the place of Bangladesh.We are looking for some talented sales manager','sales Manager','Banani,Dhaka',3,1,'40000-50000','2023-01-14 10:21:15'),('108','7890','5678','We are hiring customer service representative','Grammenphone is one of the leading organization in Bangladesh.We have founded in 1980.We are looking for some young, talented people to help us','Customer servvice Representative','Uttara,Dhaka',3,1,'40000-50000','2023-01-14 10:21:15'),('109','1234','5678','We are hiring office assitance','Law for all is a law based company.We are one of the most biggest law farm in Bangladeh.','Office Assistance','Banani,Dhaka',3,1,'10000-15000','2023-01-14 10:21:15'),('110','1234','23456','We are hiring Software Engineer','we are a  software based comapny, we looking for some young, fresh, energetic software enginineer.Grab the opportunity','Software Engineering','Banani,Dhaka',3,1,'40000-50000','2023-01-14 10:21:15');
/*!40000 ALTER TABLE `circulars` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `companies`
--

DROP TABLE IF EXISTS `companies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `companies` (
  `id` varchar(36) NOT NULL,
  `name` varchar(123) NOT NULL,
  `address` tinytext,
  `contact` varchar(32) NOT NULL,
  `email` varchar(255) NOT NULL,
  `description` tinytext,
  `photo` tinytext,
  `website` tinytext,
  PRIMARY KEY (`id`),
  UNIQUE KEY `contact` (`contact`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `companies`
--

LOCK TABLES `companies` WRITE;
/*!40000 ALTER TABLE `companies` DISABLE KEYS */;
INSERT INTO `companies` VALUES ('1234','Raj pharma','Rampura, Dhaka','01533379952','rajpharma13@gmail.com','We are a good company. Join us.','https://images.unsplash.com/photo-1560179707-f14e90ef3623?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxzZWFyY2h8Nnx8Y29tcGFueXxlbnwwfHwwfHw%3D&w=1000&q=80','https://raj-pharma.com'),('7890','Star tech','Elephant road','01967827480','startech31@gmail.com','We are a large computer seller company in Bangladesh.','https://images.unsplash.com/photo-1617526738882-1ea945ce3e56?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxzZWFyY2h8MjB8fGNvbXBhbnl8ZW58MHx8MHx8&w=1000&q=80','https://startech.com');
/*!40000 ALTER TABLE `companies` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `company_admins`
--

DROP TABLE IF EXISTS `company_admins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `company_admins` (
  `id` varchar(36) NOT NULL,
  `companyId` varchar(36) NOT NULL,
  `username` varchar(123) NOT NULL,
  `password` char(64) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  KEY `companyId` (`companyId`),
  CONSTRAINT `company_admins_ibfk_1` FOREIGN KEY (`companyId`) REFERENCES `companies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `company_admins`
--

LOCK TABLES `company_admins` WRITE;
/*!40000 ALTER TABLE `company_admins` DISABLE KEYS */;
/*!40000 ALTER TABLE `company_admins` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fields`
--

DROP TABLE IF EXISTS `fields`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fields` (
  `id` varchar(36) NOT NULL,
  `title` varchar(64) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `title` (`title`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fields`
--

LOCK TABLES `fields` WRITE;
/*!40000 ALTER TABLE `fields` DISABLE KEYS */;
INSERT INTO `fields` VALUES ('5678','Business'),('12345','Computer Science'),('23456','Mechanics');
/*!40000 ALTER TABLE `fields` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `skills`
--

DROP TABLE IF EXISTS `skills`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `skills` (
  `id` varchar(36) NOT NULL,
  `fieldId` varchar(36) NOT NULL,
  `title` tinytext NOT NULL,
  `logo` tinytext,
  PRIMARY KEY (`id`),
  KEY `fieldId` (`fieldId`),
  CONSTRAINT `skills_ibfk_1` FOREIGN KEY (`fieldId`) REFERENCES `skills` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `skills`
--

LOCK TABLES `skills` WRITE;
/*!40000 ALTER TABLE `skills` DISABLE KEYS */;
/*!40000 ALTER TABLE `skills` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tests`
--

DROP TABLE IF EXISTS `tests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tests` (
  `id` varchar(36) NOT NULL,
  `name` varchar(123) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tests`
--

LOCK TABLES `tests` WRITE;
/*!40000 ALTER TABLE `tests` DISABLE KEYS */;
/*!40000 ALTER TABLE `tests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary view structure for view `tests_view`
--

DROP TABLE IF EXISTS `tests_view`;
/*!50001 DROP VIEW IF EXISTS `tests_view`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `tests_view` AS SELECT 
 1 AS `name`*/;
SET character_set_client = @saved_cs_client;

--
-- Final view structure for view `applicants_view`
--

/*!50001 DROP VIEW IF EXISTS `applicants_view`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb3 */;
/*!50001 SET character_set_results     = utf8mb3 */;
/*!50001 SET collation_connection      = utf8mb3_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `applicants_view` AS select `applicants`.`id` AS `id`,`applicants`.`name` AS `name`,`applicants`.`gender` AS `gender`,`applicants`.`email` AS `email`,`applicants`.`phone` AS `phone`,`applicants`.`address` AS `address`,`applicants`.`resume` AS `resume`,`applicants`.`bio` AS `bio`,`applicants`.`image` AS `image`,`applicants`.`website` AS `website` from `applicants` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `tests_view`
--

/*!50001 DROP VIEW IF EXISTS `tests_view`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb3 */;
/*!50001 SET character_set_results     = utf8mb3 */;
/*!50001 SET collation_connection      = utf8mb3_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `tests_view` AS select `tests`.`name` AS `name` from `tests` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2023-03-21 10:52:02
