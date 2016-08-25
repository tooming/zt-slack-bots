-- phpMyAdmin SQL Dump
-- version 4.0.10deb1
-- http://www.phpmyadmin.net
--
-- Generation Time: Jan 28, 2016 at 07:09 PM
-- Server version: 5.5.42-log
-- PHP Version: 5.5.9-1ubuntu4.11

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `slack`
--

-- --------------------------------------------------------

--
-- Table structure for table `standups`
--

CREATE TABLE IF NOT EXISTS `standups` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `team_id` varchar(16) NOT NULL,
  `channel_name` varchar(64) NOT NULL,
  `user_id` varchar(16) NOT NULL,
  `user_name` varchar(32) NOT NULL,
  `text` varchar(256) NOT NULL,
  `date` date NOT NULL,
  `added_time` datetime NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=23 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
