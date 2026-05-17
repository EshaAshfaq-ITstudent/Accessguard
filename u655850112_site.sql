-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 03, 2026 at 08:27 AM
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
-- Database: `u655850112_site`
--

-- --------------------------------------------------------

--
-- Table structure for table `career_support`
--

CREATE TABLE `career_support` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `interest` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `consultations`
--

CREATE TABLE `consultations` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `topic` varchar(255) NOT NULL,
  `preferred_datetime` datetime NOT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `consultation_mode` varchar(50) NOT NULL,
  `message` text DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `consultations`
--

INSERT INTO `consultations` (`id`, `name`, `email`, `topic`, `preferred_datetime`, `submitted_at`, `consultation_mode`, `message`, `status`) VALUES
(1, 'Syeda Masooma ', 'syeda@email.com', 'Peace of mind', '2025-04-14 17:30:00', '2025-04-11 19:17:37', 'Google Meet', 'none', 'Completed');

-- --------------------------------------------------------

--
-- Table structure for table `digital_resources`
--

CREATE TABLE `digital_resources` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `experience_level` varchar(50) NOT NULL,
  `course_name` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `doctor_consultations`
--

CREATE TABLE `doctor_consultations` (
  `id` int(11) NOT NULL,
  `category` enum('mental','reproductive','nutrition') NOT NULL,
  `preferred_date` date NOT NULL,
  `preferred_time` time NOT NULL,
  `doctor_name` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `enrollments`
--

CREATE TABLE `enrollments` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `course` varchar(100) NOT NULL,
  `enrolled_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `father_name` varchar(255) DEFAULT NULL,
  `cnic` varchar(20) DEFAULT NULL,
  `university` varchar(255) DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `payment_screenshot` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `enrollments`
--

INSERT INTO `enrollments` (`id`, `name`, `email`, `course`, `enrolled_at`, `father_name`, `cnic`, `university`, `payment_method`, `payment_screenshot`) VALUES
(0, 'Esha Ashfaq', 'abc@gmail.com', 'Web Development Through AI', '2026-05-03 06:26:04', 'Ashfaq Ahmed', '42781-9038476-9', 'Bahria University', 'JazzCash', '1777789564_about image.jpg.webp');

-- --------------------------------------------------------

--
-- Table structure for table `forum_comments`
--

CREATE TABLE `forum_comments` (
  `id` int(11) NOT NULL,
  `thread_id` int(11) NOT NULL,
  `commenter_name` varchar(100) DEFAULT NULL,
  `comment_text` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `forum_comments`
--

INSERT INTO `forum_comments` (`id`, `thread_id`, `commenter_name`, `comment_text`, `created_at`) VALUES
(1, 1, 'Marium', 'what risks does screens have on children at a young age', '2025-04-10 20:39:00'),
(2, 1, '', 'yes it is ', '2025-04-10 20:40:28'),
(3, 1, 'Fatima', 'Screen time can be limited, it depends on the parent', '2025-04-10 21:21:09');

-- --------------------------------------------------------

--
-- Table structure for table `forum_threads`
--

CREATE TABLE `forum_threads` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `body` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `forum_threads`
--

INSERT INTO `forum_threads` (`id`, `title`, `body`, `created_at`) VALUES
(1, 'Screen Time for toddlers', 'Is it dangerous to provide screens for toddlers?', '2025-04-10 20:39:00');

-- --------------------------------------------------------

--
-- Table structure for table `legal_consultations`
--

CREATE TABLE `legal_consultations` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `legal_expert` varchar(100) NOT NULL,
  `consultation_date` date NOT NULL,
  `consultation_time` time NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `category` varchar(59) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `legal_resources`
--

CREATE TABLE `legal_resources` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `url` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mentorship_requests`
--

CREATE TABLE `mentorship_requests` (
  `id` int(11) NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `profession` varchar(100) NOT NULL,
  `reason` text NOT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `mentorship_requests`
--

INSERT INTO `mentorship_requests` (`id`, `fullname`, `email`, `profession`, `reason`, `submitted_at`) VALUES
(1, 'Syeda Masooma ', 'syeda@email.com', 'Student', 'Health', '2025-04-11 17:08:11'),
(2, 'Marium', 'marium@email.com', 'Lawyer', 'Lawyer', '2025-04-11 17:09:11'),
(3, 'Fatima', 'fatima@email.com', 'Psychologist', 'To check database', '2025-04-14 06:15:45');

-- --------------------------------------------------------

--
-- Table structure for table `parent_workshops`
--

CREATE TABLE `parent_workshops` (
  `id` int(11) NOT NULL,
  `workshop_name` varchar(255) NOT NULL,
  `parent_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `registered_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `parent_workshops`
--

INSERT INTO `parent_workshops` (`id`, `workshop_name`, `parent_name`, `email`, `registered_at`) VALUES
(1, 'Intro to Smart Devices', 'Masooma Raza', 'masooma@email.com', '2025-04-10 19:41:59'),
(2, 'Digital Tools for Homework Help', 'Marium', 'marium@email.com', '2025-04-10 19:42:52'),
(3, 'Safe Browsing for Kids', 'Fatima', 'fatima@email.com', '2025-04-10 19:51:08'),
(4, 'Intro to Smart Devices', 'Nimish', 'nimish@gmail.com', '2025-04-10 19:51:53'),
(5, 'Intro to Smart Devices', 'Fatima', 'fatima@email.com', '2025-04-10 21:20:20'),
(6, 'Intro to Smart Devices', 'Fatima', 'fatima@email.com', '2025-04-14 06:03:51');

-- --------------------------------------------------------

--
-- Table structure for table `registrations`
--

CREATE TABLE `registrations` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `school_college` varchar(255) NOT NULL,
  `contact_number` varchar(20) NOT NULL,
  `num_students` int(11) NOT NULL,
  `school_address` varchar(255) NOT NULL,
  `requirements` text DEFAULT NULL,
  `workshop` varchar(255) NOT NULL,
  `registered_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `registrations`
--

INSERT INTO `registrations` (`id`, `name`, `email`, `school_college`, `contact_number`, `num_students`, `school_address`, `requirements`, `workshop`, `registered_at`) VALUES
(1, 'Masooma Raza', 'masooma@web.com', '', '', 0, '', NULL, 'Video and Photo Editing', '2025-02-02 17:06:06'),
(2, 'Masooma Raza', 'masooma@web.com', '', '', 0, '', NULL, 'Video and Photo Editing', '2025-02-02 17:08:35'),
(4, 'Masooma Raza', 'masooma@web.com', 'xyz school', '00001111', 10, 'abc lane', 'no', 'Graphic Designing', '2025-02-02 18:29:10'),
(5, 'Masooma Raza', 'masooma@web.com', 'xyz school', '00001111', 10, 'abc lane', 'no', 'Graphic Designing', '2025-02-02 18:29:10'),
(7, 'Masooma ', 'masooma@web.com', 'abc school', '44447777', 15, 'xyz area', 'yes', 'Microsoft Office Management', '2025-02-03 19:21:11'),
(8, 'Marium', 'marium@web.com', 'yyy school', '111111111', 20, 'nazimabad', 'yes', 'Microsoft Office Management', '2025-02-07 04:51:46'),
(9, 'yusra saleem', 'yusrasaleem2005@gmail.com', 'Jinnah University', '0335-2556775', 1, 'nazimabad', 'yes,  ', 'Graphic Designing', '2025-02-07 06:08:35'),
(10, 'hafsa anees, abiha masood ', 'abihamasood125@gmail.com', 'Jinnah University', '0315-1194926', 2, 'nazimabad', 'yes,  ', 'Graphic Designing', '2025-02-07 06:09:28');

-- --------------------------------------------------------

--
-- Table structure for table `senior_engagements`
--

CREATE TABLE `senior_engagements` (
  `id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `type` enum('mentorship','event') NOT NULL,
  `description` text DEFAULT NULL,
  `event_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `senior_stories`
--

CREATE TABLE `senior_stories` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `story` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `senior_stories`
--

INSERT INTO `senior_stories` (`id`, `title`, `story`, `created_at`) VALUES
(1, 'My Work Journey ', 'This story is to check the database', '2025-04-11 15:54:23');

-- --------------------------------------------------------

--
-- Table structure for table `session_registrations`
--

CREATE TABLE `session_registrations` (
  `id` int(11) NOT NULL,
  `session_name` varchar(255) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `registered_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `session_registrations`
--

INSERT INTO `session_registrations` (`id`, `session_name`, `name`, `email`, `registered_at`) VALUES
(1, 'Digital Literacy for Seniors', 'Masooma Raza', 'masooma@email.com', '2025-04-11 16:11:14'),
(2, 'Digital Literacy for Seniors', 'Rubab', 'rubab@email.com', '2025-04-13 20:47:02'),
(3, 'Digital Literacy for Seniors', 'Aiman', 'aiman@email.com', '2025-05-08 05:36:11');

-- --------------------------------------------------------

--
-- Table structure for table `submissions`
--

CREATE TABLE `submissions` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `submission_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `submissions`
--

INSERT INTO `submissions` (`id`, `name`, `email`, `subject`, `message`, `submission_date`) VALUES
(1, 'Masooma Raza', 'masooma@web.com', 'Checking', 'checking if database is working', '2025-02-03 19:16:21'),
(2, 'Masooma Raza', 'masooma@web.com', 'Checking', 'checking if database is working', '2025-02-03 19:22:38');

-- --------------------------------------------------------

--
-- Table structure for table `tech_resources`
--

CREATE TABLE `tech_resources` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `video_url` varchar(255) NOT NULL,
  `article_url` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tech_resources`
--

INSERT INTO `tech_resources` (`id`, `title`, `video_url`, `article_url`, `description`, `created_at`) VALUES
(1, 'How to stay safe online', 'https://www.bing.com/videos/riverview/relatedvideo?q=information+on+how+to+stay+safe+online&&view=riverview&mmscn=mtsc&mid=574C2561DB8B76063AD6574C2561DB8B76063AD6&&aps=15&FORM=VMSOVR', 'https://www.antivirusguide.com/best-internet-protection/?lp=default&utm_source=microsoft&utm_medium=cpc&sgv_medium=search&utm_campaign=359709182&utm_content=1262239720424610&utm_term=%2Binternet%20%2Bprotection&cid=78890121018647&pl=&feeditemid=&targetid=', 'In this digital era here are some tips to stay safe online', '2025-04-10 21:09:32'),
(2, 'How to use social media', 'https://www.bing.com/videos/riverview/relatedvideo?q=how+to+use+social+media&&mid=666D6D71B6CB2D661AA7666D6D71B6CB2D661AA7&FORM=VCGVRP', 'https://greatergood.berkeley.edu/article/item/how_to_use_social_media_wisely_and_mindfully', 'Using social media mindfully in this digital age', '2025-04-10 21:23:16'),
(3, 'Connect to Gen-Z ', 'https://www.bing.com/videos/riverview/relatedvideo?&q=connect+to+gen+z+through+technology&&mid=320F44BD2DE6DA002DBD320F44BD2DE6DA002DBD&&FORM=VRDGAR', 'https://amberstudent.com/blog/post/how-gen-z-is-shaping-the-future-of-technology', 'how to minimize generation gap', '2025-05-07 20:24:16');

-- --------------------------------------------------------

--
-- Table structure for table `tech_training`
--

CREATE TABLE `tech_training` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `type` enum('student','institute') NOT NULL,
  `training_topic` varchar(255) DEFAULT NULL,
  `registration_date` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','admin') DEFAULT 'user',
  `phone` varchar(20) DEFAULT NULL,
  `Registration_time` timestamp(6) NOT NULL DEFAULT current_timestamp(6) ON UPDATE current_timestamp(6),
  `reset_token` varchar(255) DEFAULT NULL,
  `token_expiry` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `phone`, `Registration_time`, `reset_token`, `token_expiry`) VALUES
(1, 'Admin', 'admin@web.com', '$2y$10$VBTfVzwDAC/TxDBGRgvJXuNA4gGU01XJditArGvF2Ri.tW7dc5QUS', 'admin', NULL, '2025-04-13 21:28:27.942446', NULL, NULL),
(2, 'Masooma', 'masooma@example.com', '$2y$10$LycDdNSIK0HbsAL2yydVZuySFLLwutooRciHfjF1ui5wCWgFKvNC6', 'user', NULL, '2025-04-10 11:02:32.524560', NULL, NULL),
(4, 'Admin', 'admin@example.com', '$2y$10$WBTFJb0u0wQj2YUzrdme4OcAYGWZxidVEHWwiha0cc.79Jl21FJXK', 'admin', NULL, '2025-04-10 11:02:32.524560', NULL, NULL),
(5, 'Fatima', 'fatima@email.com', '$2y$10$dcN2cB6iBD1JgxBLCyljaelbHEt2XEpu4j2zlXCZKjfXgwP5YxJzC', '', NULL, '2025-04-10 11:02:32.524560', NULL, NULL),
(6, 'Marium', 'marium@email.com', '$2y$10$BGV45hFRfqiSJmK74O5TueGzCmWbB1pplaEPmGxDuf/lqn1UqdUP.', 'user', NULL, '2025-04-10 11:21:48.973429', NULL, NULL),
(7, 'Nimish', 'nimish@email.com', '$2y$10$3wCgRLjSdkgtAOs3rcPdqOt45Gax1j7cYRAni24gZpgc/gM3trT/G', 'user', NULL, '2025-04-10 22:17:08.667794', NULL, NULL),
(9, 'Aiman', 'aiman@email.com', '$2y$10$bDZDyyKDcAoxVVTmynT1MuEL66l/KrYN/qHGOLU5kU4CEG75KkBAu', '', NULL, '2025-05-07 20:35:39.825416', NULL, NULL),
(10, 'Taliya', 'taliya@email.com', '$2y$10$qwnvUfl7k1FYszPJDuVWoeE49HC2S.nryOlUSdTVOhe5TBLJRcr6.', 'user', NULL, '2025-05-10 20:15:05.785552', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_profiles`
--

CREATE TABLE `user_profiles` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `bio` text DEFAULT NULL,
  `expertise` text DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `women_consultation`
--

CREATE TABLE `women_consultation` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `consulted_type` enum('doctor','lawyer') NOT NULL,
  `consultation_topic` text DEFAULT NULL,
  `consultation_date` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `consultations`
--
ALTER TABLE `consultations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `forum_comments`
--
ALTER TABLE `forum_comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `thread_id` (`thread_id`);

--
-- Indexes for table `forum_threads`
--
ALTER TABLE `forum_threads`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `mentorship_requests`
--
ALTER TABLE `mentorship_requests`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `parent_workshops`
--
ALTER TABLE `parent_workshops`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `registrations`
--
ALTER TABLE `registrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `senior_engagements`
--
ALTER TABLE `senior_engagements`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `senior_stories`
--
ALTER TABLE `senior_stories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `session_registrations`
--
ALTER TABLE `session_registrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `submissions`
--
ALTER TABLE `submissions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tech_resources`
--
ALTER TABLE `tech_resources`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tech_training`
--
ALTER TABLE `tech_training`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `women_consultation`
--
ALTER TABLE `women_consultation`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `consultations`
--
ALTER TABLE `consultations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `forum_comments`
--
ALTER TABLE `forum_comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `forum_threads`
--
ALTER TABLE `forum_threads`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `mentorship_requests`
--
ALTER TABLE `mentorship_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `parent_workshops`
--
ALTER TABLE `parent_workshops`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `registrations`
--
ALTER TABLE `registrations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `senior_engagements`
--
ALTER TABLE `senior_engagements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `senior_stories`
--
ALTER TABLE `senior_stories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `session_registrations`
--
ALTER TABLE `session_registrations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `submissions`
--
ALTER TABLE `submissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tech_resources`
--
ALTER TABLE `tech_resources`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tech_training`
--
ALTER TABLE `tech_training`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `women_consultation`
--
ALTER TABLE `women_consultation`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `forum_comments`
--
ALTER TABLE `forum_comments`
  ADD CONSTRAINT `forum_comments_ibfk_1` FOREIGN KEY (`thread_id`) REFERENCES `forum_threads` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
