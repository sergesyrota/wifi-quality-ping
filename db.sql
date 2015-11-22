-- phpMyAdmin SQL Dump
-- version 4.4.13
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Nov 22, 2015 at 11:27 AM
-- Server version: 5.5.37
-- PHP Version: 5.6.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Database: `wifi-ping`
--

-- --------------------------------------------------------

--
-- Table structure for table `hosts`
--

CREATE TABLE IF NOT EXISTS `hosts` (
  `id` int(11) NOT NULL,
  `hostname` varchar(100) NOT NULL,
  `description` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `ping-result`
--

CREATE TABLE IF NOT EXISTS `ping-result` (
  `id` int(11) NOT NULL,
  `host_id` int(11) NOT NULL,
  `time` datetime NOT NULL,
  `ping_ms` decimal(6,3) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `hosts`
--
ALTER TABLE `hosts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ping-result`
--
ALTER TABLE `ping-result`
  ADD PRIMARY KEY (`id`),
  ADD KEY `host_id` (`host_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `hosts`
--
ALTER TABLE `hosts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `ping-result`
--
ALTER TABLE `ping-result`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
