-- phpMyAdmin SQL Dump
-- version 4.0.10deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Nov 08, 2016 at 01:45 PM
-- Server version: 5.5.53-0ubuntu0.14.04.1
-- PHP Version: 5.5.9-1ubuntu4.20

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
-- Table structure for table `pushups`
--

CREATE TABLE IF NOT EXISTS `pushups` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `team_id` varchar(16) NOT NULL,
  `channel_name` varchar(64) NOT NULL,
  `user_id` varchar(16) NOT NULL,
  `user_name` varchar(32) NOT NULL,
  `count` int(11) NOT NULL,
  `added_time` datetime NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=157 ;

-- --------------------------------------------------------

--
-- Table structure for table `pushup_targets`
--

CREATE TABLE IF NOT EXISTS `pushup_targets` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `team_id` varchar(16) NOT NULL,
  `channel_name` varchar(64) NOT NULL,
  `target` int(11) NOT NULL,
  `added_time` datetime NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=102 ;

-- --------------------------------------------------------

--
-- Table structure for table `standups`
--

CREATE TABLE IF NOT EXISTS `standups` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `team_id` varchar(16) NOT NULL,
  `channel_id` varchar(16) NOT NULL,
  `channel_name` varchar(64) NOT NULL,
  `user_id` varchar(16) NOT NULL,
  `user_name` varchar(32) NOT NULL,
  `text` text NOT NULL,
  `date` date NOT NULL,
  `added_time` datetime NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=606 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
