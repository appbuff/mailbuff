-- phpMyAdmin SQL Dump
-- version 5.0.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Dec 12, 2020 at 08:28 PM
-- Server version: 5.7.31
-- PHP Version: 7.3.21

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `mailbuff`
--

-- --------------------------------------------------------

--
-- Table structure for table `email_category`
--

DROP TABLE IF EXISTS `email_category`;
CREATE TABLE IF NOT EXISTS `email_category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `e_type` varchar(255) NOT NULL,
  `catch_all_check` int(11) NOT NULL DEFAULT '1',
  `user_id` varchar(255) NOT NULL DEFAULT 'all',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `site_options`
--

DROP TABLE IF EXISTS `site_options`;
CREATE TABLE IF NOT EXISTS `site_options` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `logo` varchar(15) CHARACTER SET utf8 DEFAULT NULL,
  `site_title` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
  `scan_time_out` int(10) DEFAULT NULL,
  `scan_port` int(10) DEFAULT NULL,
  `scan_mail` varchar(100) CHARACTER SET utf8 DEFAULT NULL,
  `estimated_cost` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
  `cost_per_scan` decimal(10,3) DEFAULT NULL,
  `validation` varchar(10) CHARACTER SET utf8 DEFAULT NULL,
  `registration_action` varchar(250) CHARACTER SET utf8 DEFAULT NULL,
  `script_path` varchar(250) CHARACTER SET utf8 DEFAULT NULL,
  `script_url` varchar(250) CHARACTER SET utf8 DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `site_options`
--

INSERT INTO `site_options` (`id`, `logo`, `site_title`, `scan_time_out`, `scan_port`, `scan_mail`, `estimated_cost`, `cost_per_scan`, `validation`, `registration_action`, `script_path`, `script_url`) VALUES
(1, NULL, NULL, 10, 25, NULL, '0.005', '0.005', 'true', 'false', 'C:wamp64wwwemail-verifier-pro', 'http://localhost');

-- --------------------------------------------------------

--
-- Table structure for table `task`
--

DROP TABLE IF EXISTS `task`;
CREATE TABLE IF NOT EXISTS `task` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `csv_name` varchar(255) NOT NULL,
  `status` varchar(100) NOT NULL DEFAULT 'running',
  `user_id` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `timer`
--

DROP TABLE IF EXISTS `timer`;
CREATE TABLE IF NOT EXISTS `timer` (
  `id` int(255) NOT NULL AUTO_INCREMENT,
  `user_id` int(255) NOT NULL,
  `e_range` int(100) NOT NULL,
  `time_range` int(100) DEFAULT NULL,
  `last_send` int(100) DEFAULT NULL,
  `time_count` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `timer`
--

INSERT INTO `timer` (`id`, `user_id`, `e_range`, `time_range`, `last_send`, `time_count`) VALUES
(1, 1, 100, 60, 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fname` varchar(30) NOT NULL,
  `lname` varchar(30) NOT NULL,
  `email` varchar(30) NOT NULL,
  `verify_email_token` varchar(255) DEFAULT NULL,
  `reset_pass_token` varchar(255) DEFAULT NULL,
  `email_change_token` varchar(255) DEFAULT NULL,
  `password` varchar(50) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `status` varchar(15) NOT NULL DEFAULT 'unverified',
  `category` varchar(15) NOT NULL DEFAULT 'user',
  `join_date` date NOT NULL,
  `last_update_date` date DEFAULT NULL,
  `user_ip` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `fname`, `lname`, `email`, `verify_email_token`, `reset_pass_token`, `email_change_token`, `password`, `image`, `status`, `category`, `join_date`, `last_update_date`, `user_ip`) VALUES
(1, 'appbuff', 'llc', 'info@appbuff.net', NULL, NULL, NULL, 'e10adc3949ba59abbe56e057f20f883e', 'appbuff_1.png', 'active', 'admin', '2020-12-10', NULL, '::1');

-- --------------------------------------------------------

--
-- Table structure for table `user_email_list`
--

DROP TABLE IF EXISTS `user_email_list`;
CREATE TABLE IF NOT EXISTS `user_email_list` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `csv_file_name` varchar(255) NOT NULL,
  `email_name` varchar(100) NOT NULL,
  `email_status` varchar(100) NOT NULL,
  `email_type` varchar(100) DEFAULT NULL,
  `safe_to_send` varchar(100) DEFAULT NULL,
  `verification_response` varchar(100) DEFAULT NULL,
  `score` varchar(100) DEFAULT NULL,
  `bounce_type` varchar(100) DEFAULT NULL,
  `email_acc` varchar(100) DEFAULT NULL,
  `email_dom` varchar(100) DEFAULT NULL,
  `create_date` date DEFAULT NULL,
  `user_id` int(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
