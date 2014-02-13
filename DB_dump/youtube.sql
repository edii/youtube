-- phpMyAdmin SQL Dump
-- version 3.5.8.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Feb 13, 2014 at 08:09 AM
-- Server version: 5.5.23
-- PHP Version: 5.4.11

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `youtube`
--

-- --------------------------------------------------------

--
-- Table structure for table `channel`
--

CREATE TABLE IF NOT EXISTS `channel` (
  `c_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `create_date` timestamp NULL DEFAULT NULL,
  `c_date` timestamp NULL DEFAULT NULL,
  `channel_address` varchar(255) NOT NULL,
  `channel_name` varchar(255) DEFAULT NULL,
  `count_rollers` int(11) DEFAULT '0',
  `count_views` int(11) DEFAULT '0',
  `count_subscribers` int(11) DEFAULT '0',
  `country` varchar(255) DEFAULT NULL,
  `keywords` text,
  PRIMARY KEY (`c_id`),
  KEY `c_country` (`country`),
  KEY `c_address` (`channel_address`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `rollers`
--

CREATE TABLE IF NOT EXISTS `rollers` (
  `r_id` int(11) NOT NULL AUTO_INCREMENT,
  `create_date` timestamp NULL DEFAULT NULL,
  `r_date` timestamp NULL DEFAULT NULL,
  `tags` varchar(255) DEFAULT NULL,
  `c_id` int(11) DEFAULT '1',
  `title` varchar(255) DEFAULT NULL,
  `description` text,
  `abstract` varchar(255) DEFAULT NULL,
  `activity` enum('1','2','3','4') DEFAULT '1',
  `category` varchar(255) DEFAULT NULL,
  `video` varchar(255) DEFAULT NULL,
  `duration` varchar(255) DEFAULT NULL,
  `view` int(11) DEFAULT '0',
  `rating` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`r_id`),
  KEY `channel_id` (`c_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT AUTO_INCREMENT=1 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
