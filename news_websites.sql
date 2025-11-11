-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 23, 2025 at 03:58 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `news_websites`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('administrator','editor','author') NOT NULL DEFAULT 'author'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`, `role`) VALUES
(1, 'admin', '$2y$10$rTB.hlT2M2C9vP0x6g1y7.KCXjXZQIxgE8TaSd/cQIHuFzlTMIkAu', 'administrator'),
(2, 'ad', '1234567', 'author');

-- --------------------------------------------------------

--
-- Table structure for table `articles`
--

CREATE TABLE `articles` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `category_id` int(11) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `view_count` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `articles`
--

INSERT INTO `articles` (`id`, `title`, `content`, `category_id`, `image`, `created_at`, `view_count`) VALUES
(1, 'sa', 'sasa', 1, '1750667020_single_article.php', '2025-06-23 08:23:40', 4),
(2, 'daf', 'dsfdsfdsfdsfdsfdsfdsfdsfdsfdsfdsfdsfdsfdsfdsfdsfdsfdsfdsfdsfdsfdsfdsfdsfdsfdsfdsfdsfdsfdsfdsfdsfdsfdsfdsfdsfdsfdsfdsfdsfdsfdsfdsfdsfdsfdsfdsfdsfdsfdsfdsfdsfdsfdsfdsfdsfdsfdsfdsfdsfdsfdsfdsfdsfdsfdsfdsfdsfdsfdsfdsfdsfdsf', 2, '1750667070_news_website.sql', '2025-06-23 08:24:30', 0),
(3, 'swafywqefish', 'swafywqefishswafywqefishswafywqefishswafywqefishswafywqefishswafywqefishswafywqefishswafywqefishswafywqefishswafywqefishswafywqefishswafywqefishswafywqefishswafywqefishswafywqefishswafywqefishswafywqefishswafywqefishswafywqefishswafywqefishswafywqefishswafywqefishswafywqefishswafywqefishswafywqefishswafywqefishswafywqefishswafywqefishswafywqefishswafywqefishswafywqefishswafywqefishswafywqefishswafywqefishswafywqefishswafywqefishswafywqefishswafywqefishswafywqefishswafywqefish', 3, '1750667088_res.php', '2025-06-23 08:24:48', 5),
(4, 'ewafw', 'afsafsfsdfavasmnbmhsdbjfabdfjafsafsfsdfavasmnbmhsdbjfabdfjafsafsfsdfavasmnbmhsdbjfabdfjafsafsfsdfavasmnbmhsdbjfabdfjafsafsfsdfavasmnbmhsdbjfabdfjafsafsfsdfavasmnbmhsdbjfabdfjafsafsfsdfavasmnbmhsdbjfabdfjafsafsfsdfavasmnbmhsdbjfabdfjafsafsfsdfavasmnbmhsdbjfabdfjafsafsfsdfavasmnbmhsdbjfabdfjafsafsfsdfavasmnbmhsdbjfabdfjafsafsfsdfavasmnbmhsdbjfabdfjafsafsfsdfavasmnbmhsdbjfabdfjafsafsfsdfavasmnbmhsdbjfabdfjafsafsfsdfavasmnbmhsdbjfabdfjafsafsfsdfavasmnbmhsdbjfabdfjafsafsfsdfavasmnbmhsdbjfabdfjafsafsfsdfavasmnbmhsdbjfabdfjafsafsfsdfavasmnbmhsdbjfabdfjafsafsfsdfavasmnbmhsdbjfabdfjafsafsfsdfavasmnbmhsdbjfabdfjafsafsfsdfavasmnbmhsdbjfabdfjafsafsfsdfavasmnbmhsdbjfabdfjafsafsfsdfavasmnbmhsdbjfabdfjafsafsfsdfavasmnbmhsdbjfabdfjafsafsfsdfavasmnbmhsdbjfabdfjafsafsfsdfavasmnbmhsdbjfabdfjafsafsfsdfavasmnbmhsdbjfabdfjafsafsfsdfavasmnbmhsdbjfabdfjafsafsfsdfavasmnbmhsdbjfabdfjafsafsfsdfavasmnbmhsdbjfabdfjafsafsfsdfavasmnbmhsdbjfabdfjafsafsfsdfavasmnbmhsdbjfabdfjafsafsfsdfavasmnbmhsdbjfabdfjafsafsfsdfavasmnbmhsdbjfabdfjafsafsfsdfavasmnbmhsdbjfabdfjafsafsfsdfavasmnbmhsdbjfabdfjafsafsfsdfavasmnbmhsdbjfabdfjafsafsfsdfavasmnbmhsdbjfabdfjafsafsfsdfavasmnbmhsdbjfabdfjafsafsfsdfavasmnbmhsdbjfabdfjafsafsfsdfavasmnbmhsdbjfabdfjafsafsfsdfavasmnbmhsdbjfabdfjafsafsfsdfavasmnbmhsdbjfabdfjafsafsfsdfavasmnbmhsdbjfabdfjafsafsfsdfavasmnbmhsdbjfabdfjafsafsfsdfavasmnbmhsdbjfabdfjafsafsfsdfavasmnbmhsdbjfabdfjafsafsfsdfavasmnbmhsdbjfabdfjafsafsfsdfavasmnbmhsdbjfabdfjafsafsfsdfavasmnbmhsdbjfabdfjafsafsfsdfavasmnbmhsdbjfabdfjafsafsfsdfavasmnbmhsdbjfabdfjafsafsfsdfavasmnbmhsdbjfabdfjafsafsfsdfavasmnbmhsdbjfabdfjafsafsfsdfavasmnbmhsdbjfabdfjafsafsfsdfavasmnbmhsdbjfabdfjafsafsfsdfavasmnbmhsdbjfabdfjafsafsfsdfavasmnbmhsdbjfabdfjafsafsfsdfavasmnbmhsdbjfabdfjafsafsfsdfavasmnbmhsdbjfabdfjafsafsfsdfavasmnbmhsdbjfabdfjafsafsfsdfavasmnbmhsdbjfabdfjafsafsfsdfavasmnbmhsdbjfabdfjafsafsfsdfavasmnbmhsdbjfabdfjafsafsfsdfavasmnbmhsdbjfabdfjafsafsfsdfavasmnbmhsdbjfabdfj', 4, '1750673913_app.png', '2025-06-23 08:43:17', 6);

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`) VALUES
(1, 'asa'),
(2, 'bs'),
(3, 'cd'),
(4, 'a1'),
(5, 'a2'),
(6, 'a3');

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE `comments` (
  `id` int(11) NOT NULL,
  `article_id` int(11) NOT NULL,
  `author` varchar(255) NOT NULL,
  `comment` text NOT NULL,
  `status` enum('pending','approved') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `comments`
--

INSERT INTO `comments` (`id`, `article_id`, `author`, `comment`, `status`, `created_at`) VALUES
(1, 3, 'asad', 'sadas', 'approved', '2025-06-23 09:01:48'),
(2, 3, 'asad', 'sadas', 'pending', '2025-06-23 09:02:13'),
(3, 3, 'asad', 'sadas', 'pending', '2025-06-23 09:03:43'),
(4, 4, 'sad', 'sadada', 'pending', '2025-06-23 09:57:53');

-- --------------------------------------------------------

--
-- Table structure for table `subscribers`
--

CREATE TABLE `subscribers` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subscribers`
--

INSERT INTO `subscribers` (`id`, `email`, `created_at`) VALUES
(1, 'saa@gmail.com', '2025-06-23 10:03:08'),
(2, 'megasif111@finfave.com', '2025-06-23 10:04:44');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `articles`
--
ALTER TABLE `articles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `article_id` (`article_id`);

--
-- Indexes for table `subscribers`
--
ALTER TABLE `subscribers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `articles`
--
ALTER TABLE `articles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `subscribers`
--
ALTER TABLE `subscribers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `articles`
--
ALTER TABLE `articles`
  ADD CONSTRAINT `articles_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
