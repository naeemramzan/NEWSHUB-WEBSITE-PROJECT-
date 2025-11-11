-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 27, 2025 at 10:03 AM
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
-- Database: `news_website`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `full_name` varchar(255) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('administrator','editor','author') NOT NULL DEFAULT 'author'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `full_name`, `bio`, `password`, `role`) VALUES
(1, 'admin', 'The Editor', NULL, '$2y$10$vzTGA/YXrYVpVM7Jkca5YewGKm3qjguZQ3.uBSamQXlPmmHyBd2ES', 'administrator');

-- --------------------------------------------------------

--
-- Table structure for table `articles`
--

CREATE TABLE `articles` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `category_id` int(11) NOT NULL,
  `author_id` int(11) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `admin_id` int(11) DEFAULT NULL,
  `view_count` int(11) NOT NULL DEFAULT 0,
  `is_featured` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `articles`
--

INSERT INTO `articles` (`id`, `title`, `content`, `category_id`, `author_id`, `image`, `created_at`, `admin_id`, `view_count`, `is_featured`) VALUES
(1, 'Pakistan condemns \'inhuman, cruel\' treatment of Palestinians, Kashmiris', 'After parliament, Iran\'s Guardian Council also approves bill to suspend cooperation with IAEA\r\nAfter parliament, Iran\'s Guardian Council also approves bill to suspend cooperation with IAEA\r\nSupreme Leader Ali Khamenei says Israel was \'crushed\' under Iran\'s blows in post-ceasefire message\r\n\r\nPakistan condemns \'inhuman, cruel\' treatment of Palestinians, Kashmiris\r\nPakistan condemns \'inhuman, cruel\' treatment of Palestinians, Kashmiris\r\nForeign Office says Islam opposes torture, pledges support for victims worldwide\r\n\r\nIndia refuses to sign SCO joint document mentioning terror activities in Balochistan\r\nIndia refuses to sign SCO joint document mentioning terror activities in Balochistan\r\nNew Delhi highlights peace, security and trust deficit as key regional challenges\r\n\r\nLive Fresh Israeli airstrikes, gunfire kill 21 Palestinians in Gaza\r\nFresh Israeli airstrikes, gunfire kill 21 Palestinians in Gaza\r\nNew deaths come as Arab mediators, backed by US, reach out to warring parties in bid to hold new ceasefire talks\r\n\r\nPakistan, US explore preferential trade agreement in ongoing talks\r\nPakistan, US explore preferential trade agreement in ongoing talks\r\nBoth sides expressed confidence in wrapping up trade negotiations next week\r\n\r\nProvincial govts to be consulted on suspension of mobile, internet services d', 3, NULL, '', '2025-06-26 11:53:12', 1, 40, 0),
(2, 'After parliament, Iran\'s Guardian Council also approves bill to suspend cooperation with IAEA', 'After parliament, Iran\'s Guardian Council also approves bill to suspend cooperation with IAEA\r\nAfter parliament, Iran\'s Guardian Council also approves bill to suspend cooperation with IAEA\r\nSupreme Leader Ali Khamenei says Israel was \'crushed\' under Iran\'s blows in post-ceasefire message\r\n\r\nPakistan condemns \'inhuman, cruel\' treatment of Palestinians, Kashmiris\r\nPakistan condemns \'inhuman, cruel\' treatment of Palestinians, Kashmiris\r\nForeign Office says Islam opposes torture, pledges support for victims worldwide\r\n\r\nIndia refuses to sign SCO joint document mentioning terror activities in Balochistan\r\nIndia refuses to sign SCO joint document mentioning terror activities in Balochistan\r\nNew Delhi highlights peace, security and trust deficit as key regional challenges\r\n\r\nLive Fresh Israeli airstrikes, gunfire kill 21 Palestinians in Gaza\r\nFresh Israeli airstrikes, gunfire kill 21 Palestinians in Gaza\r\nNew deaths come as Arab mediators, backed by US, reach out to warring parties in bid to hold new ceasefire talks\r\n\r\nPakistan, US explore preferential trade', 1, 1, '', '2025-06-27 07:40:24', NULL, 0, 1),
(3, 'PakistanWill Punjab suspend cellular services during Muharram? Will Punjab suspend cellular services during Muharram? Pakistan condemns \'inhuman, cruel\' treatment of Palestinians, Kashmiris', 'PakistanWill Punjab suspend cellular services during Muharram?', 4, 1, '', '2025-06-27 07:48:55', NULL, 5, 0);

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
(1, 'Top Stories'),
(2, 'Articals'),
(3, 'World'),
(4, 'Video'),
(5, 'Blogs');

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

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `status` enum('unread','read') NOT NULL DEFAULT 'unread',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contact_messages`
--

INSERT INTO `contact_messages` (`id`, `name`, `email`, `subject`, `message`, `status`, `created_at`) VALUES
(1, 'sas', 'admin@gmail.com', 'sasa', 'sas', 'unread', '2025-06-27 07:20:51');

-- --------------------------------------------------------

--
-- Table structure for table `subscribers`
--

CREATE TABLE `subscribers` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `videos`
--

CREATE TABLE `videos` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `youtube_embed_code` text NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `image` varchar(255) DEFAULT NULL,
  `admin_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `videos`
--

INSERT INTO `videos` (`id`, `title`, `youtube_embed_code`, `description`, `created_at`, `image`, `admin_id`) VALUES
(1, 'asda', 'aa', NULL, '2025-06-26 12:24:25', NULL, NULL),
(2, 'yt you', '<iframe src=\"https://www.youtube.com/embed/HgNHK5NTYSU\" frameborder=\"0\" allow=\"accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture\" allowfullscreen></iframe>', NULL, '2025-06-26 14:44:17', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `video_categories`
--

CREATE TABLE `video_categories` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `video_categories`
--

INSERT INTO `video_categories` (`id`, `name`) VALUES
(1, 'vvv'),
(2, 'ds');

-- --------------------------------------------------------

--
-- Table structure for table `video_category_map`
--

CREATE TABLE `video_category_map` (
  `video_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `video_category_map`
--

INSERT INTO `video_category_map` (`video_id`, `category_id`) VALUES
(2, 2);

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
  ADD KEY `category_id` (`category_id`),
  ADD KEY `fk_admin_author` (`admin_id`),
  ADD KEY `author_id` (`author_id`);

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
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `subscribers`
--
ALTER TABLE `subscribers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `videos`
--
ALTER TABLE `videos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_video_admin` (`admin_id`);

--
-- Indexes for table `video_categories`
--
ALTER TABLE `video_categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `video_category_map`
--
ALTER TABLE `video_category_map`
  ADD PRIMARY KEY (`video_id`,`category_id`),
  ADD KEY `category_id` (`category_id`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `subscribers`
--
ALTER TABLE `subscribers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `videos`
--
ALTER TABLE `videos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `video_categories`
--
ALTER TABLE `video_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `articles`
--
ALTER TABLE `articles`
  ADD CONSTRAINT `articles_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `articles_ibfk_2` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `articles_ibfk_3` FOREIGN KEY (`author_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_admin_author` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `videos`
--
ALTER TABLE `videos`
  ADD CONSTRAINT `fk_video_admin` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `video_category_map`
--
ALTER TABLE `video_category_map`
  ADD CONSTRAINT `video_category_map_ibfk_1` FOREIGN KEY (`video_id`) REFERENCES `videos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `video_category_map_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `video_categories` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
