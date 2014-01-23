-- MySQL dump 10.13  Distrib 5.5.31, for Linux (i686)
--
-- Host: localhost    Database: olcs
-- ------------------------------------------------------
-- Server version	5.5.31

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `case_category_link`
--

DROP TABLE IF EXISTS `case_category_link`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `case_category_link` (
  `vcase` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  PRIMARY KEY (`vcase`,`category_id`),
  KEY `IDX_DADE60EEE9FCA46A` (`vcase`),
  CONSTRAINT `FK_DADE60EEE9FCA46A` FOREIGN KEY (`vcase`) REFERENCES `t_case` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `case_category_link`
--

LOCK TABLES `case_category_link` WRITE;
/*!40000 ALTER TABLE `case_category_link` DISABLE KEYS */;
/*!40000 ALTER TABLE `case_category_link` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kvpstore`
--

DROP TABLE IF EXISTS `kvpstore`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kvpstore` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `keyName` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `value` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `version` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kvpstore`
--

LOCK TABLES `kvpstore` WRITE;
/*!40000 ALTER TABLE `kvpstore` DISABLE KEYS */;
/*!40000 ALTER TABLE `kvpstore` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `licence`
--

DROP TABLE IF EXISTS `licence`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `licence` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `licenceNumber` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `licenceStatus` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `licenceType` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `addressLine1` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `addressLine2` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `addressTown` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `addressPostcode` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `startDate` date NOT NULL,
  `reviewDate` date NOT NULL,
  `endDate` date NOT NULL,
  `fabsReference` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `operatorId` int(11) DEFAULT NULL,
  `version` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `IDX_1DAAE64840970B15` (`operatorId`),
  CONSTRAINT `FK_1DAAE64840970B15` FOREIGN KEY (`operatorId`) REFERENCES `operator` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;


--
-- Table structure for table `operator`
--

DROP TABLE IF EXISTS `operator`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `operator` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `operatorId` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `operatorName` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `companyNumber` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `caseNumber` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `tradingName` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `address` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `town` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `postcode` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `addressType` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `licenceNumber` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `licenceStatus` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `transportManagerId` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `vehicleRegistrationMark` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `discSerialNumber` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `fabsReference` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `version` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;


--
-- Table structure for table `person`
--

DROP TABLE IF EXISTS `person`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `person` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `firstName` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `lastName` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `dateOfBirth` date NOT NULL,
  `personType` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `operatorId` int(11) DEFAULT NULL,
  `version` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `IDX_34DCD17640970B15` (`operatorId`),
  CONSTRAINT `FK_34DCD17640970B15` FOREIGN KEY (`operatorId`) REFERENCES `operator` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `person`
--


--
-- Table structure for table `t_case`
--

DROP TABLE IF EXISTS `t_case`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `t_case` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `licence` int(11) DEFAULT NULL,
  `description` longtext COLLATE utf8_unicode_ci NOT NULL,
  `ecms` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `openTime` datetime NOT NULL,
  `owner` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `version` int(11) NOT NULL DEFAULT '1',
  `caseNumber` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_C27EF0761DAAE648` (`licence`),
  CONSTRAINT `FK_C27EF0761DAAE648` FOREIGN KEY (`licence`) REFERENCES `licence` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;


/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2013-09-17 12:51:43
