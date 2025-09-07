-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 06, 2025 at 09:39 AM
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
-- Database: `tms`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `admin_id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`admin_id`, `username`, `password`) VALUES
(1, 'Admin', '$2y$10$VPDv4aTd/MZ0uBsXw4DYNOpFOai58UN.Z1mpsIKYPjAjqp7s77SfS');

-- --------------------------------------------------------

--
-- Table structure for table `admin_details`
--

CREATE TABLE `admin_details` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `contact_number` varchar(15) NOT NULL,
  `email_address` varchar(255) NOT NULL,
  `date_of_birth` date NOT NULL,
  `gender` enum('Male','Female','Other') NOT NULL,
  `subjects_taught` text NOT NULL,
  `years_of_experience` int(11) NOT NULL,
  `certifications` text NOT NULL,
  `profile_photo` varchar(255) DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_details`
--

INSERT INTO `admin_details` (`id`, `admin_id`, `full_name`, `contact_number`, `email_address`, `date_of_birth`, `gender`, `subjects_taught`, `years_of_experience`, `certifications`, `profile_photo`, `logo`, `created_at`) VALUES
(1, 1, 'Mohommed Najads', '07690685345', 'najadmohommed34@gmail.com', '2025-01-18', 'Male', 'english, Tamil', 6, 'HND SE, Diploma', '../uploads/1737187435_IMG_1720.JPG', NULL, '2025-01-18 08:03:55');

-- --------------------------------------------------------

--
-- Table structure for table `admin_free_slots`
--

CREATE TABLE `admin_free_slots` (
  `id` int(11) NOT NULL,
  `day` varchar(10) NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_free_slots`
--

INSERT INTO `admin_free_slots` (`id`, `day`, `start_time`, `end_time`) VALUES
(25, 'Monday', '11:29:00', '11:29:00'),
(26, 'Tuesday', '11:29:00', '11:29:00');

-- --------------------------------------------------------

--
-- Table structure for table `broadcast_message`
--

CREATE TABLE `broadcast_message` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `message_type` enum('course','class') NOT NULL,
  `target_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `attachment` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `broadcast_message`
--

INSERT INTO `broadcast_message` (`id`, `admin_id`, `message_type`, `target_id`, `message`, `attachment`, `created_at`) VALUES
(5, 1, 'class', 6, 'asdasd', 'Tutor Management System(FULL).docx', '2025-01-26 14:08:58'),
(6, 1, 'class', 6, 'abbbbbbbbbbbbbbbbbb', '../uploads/messages/Tutor Management System(FULL).docx', '2025-01-26 14:14:26');

-- --------------------------------------------------------

--
-- Table structure for table `classes`
--

CREATE TABLE `classes` (
  `id` int(11) NOT NULL,
  `grade` int(11) DEFAULT NULL,
  `subject` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `fee` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `classes`
--

INSERT INTO `classes` (`id`, `grade`, `subject`, `title`, `fee`) VALUES
(6, 1, 1, 'abssas', 10000.00),
(7, 1, 2, 'Grammer Class', 1000.00),
(8, 3, 3, 'New Class', 5000.00),
(9, 3, 3, 'New', 2500.00);

-- --------------------------------------------------------

--
-- Table structure for table `class_attendance`
--

CREATE TABLE `class_attendance` (
  `id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `status` enum('present','absent','late') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `class_attendance`
--

INSERT INTO `class_attendance` (`id`, `class_id`, `student_id`, `date`, `status`, `created_at`, `updated_at`) VALUES
(2, 6, 2, '2025-01-23', 'present', '2025-01-23 04:59:18', '2025-01-23 04:59:50'),
(3, 7, 3, '2025-01-25', 'absent', '2025-01-25 10:23:29', '2025-01-25 10:23:29'),
(4, 7, 2, '2025-01-25', 'present', '2025-01-25 10:23:29', '2025-01-25 10:23:29'),
(5, 6, 3, '2025-01-25', 'present', '2025-01-25 10:23:31', '2025-01-25 10:23:37'),
(6, 6, 2, '2025-01-25', 'present', '2025-01-25 10:23:31', '2025-01-25 10:23:37'),
(7, 7, 3, '2025-01-27', 'present', '2025-01-27 09:08:14', '2025-01-27 09:15:09'),
(8, 7, 2, '2025-01-27', 'present', '2025-01-27 09:08:14', '2025-01-27 09:15:09'),
(9, 6, 3, '2025-01-27', 'absent', '2025-01-27 09:08:17', '2025-01-27 09:08:21'),
(10, 6, 2, '2025-01-27', 'present', '2025-01-27 09:08:17', '2025-01-27 09:08:21'),
(11, 6, 3, '2025-08-25', 'present', '2025-08-25 16:35:30', '2025-08-25 16:35:30'),
(12, 6, 2, '2025-08-25', 'present', '2025-08-25 16:35:30', '2025-08-25 16:35:30'),
(13, 6, 3, '2025-08-27', 'absent', '2025-08-27 06:29:37', '2025-08-27 14:18:54'),
(14, 6, 2, '2025-08-27', 'present', '2025-08-27 06:29:37', '2025-08-27 14:18:54'),
(15, 7, 3, '2025-08-27', 'late', '2025-08-27 08:20:32', '2025-08-27 14:26:20'),
(16, 7, 2, '2025-08-27', 'late', '2025-08-27 08:20:32', '2025-08-27 14:26:20');

-- --------------------------------------------------------

--
-- Table structure for table `class_days_times`
--

CREATE TABLE `class_days_times` (
  `id` int(11) NOT NULL,
  `class_id` int(11) DEFAULT NULL,
  `day` varchar(20) NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `class_days_times`
--

INSERT INTO `class_days_times` (`id`, `class_id`, `day`, `start_time`, `end_time`) VALUES
(10, 8, 'Monday', '15:41:00', '12:41:00'),
(11, 8, 'Tuesday', '12:41:00', '12:41:00'),
(14, 9, 'Tuesday', '21:42:00', '21:42:00'),
(18, 6, 'Monday', '11:34:00', '11:34:00'),
(19, 7, 'Monday', '10:30:00', '10:30:00');

-- --------------------------------------------------------

--
-- Table structure for table `class_materials`
--

CREATE TABLE `class_materials` (
  `id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `end_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `id` int(11) NOT NULL,
  `course_name` varchar(255) NOT NULL,
  `course_details` text NOT NULL,
  `price_range` varchar(255) NOT NULL,
  `duration` varchar(100) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`id`, `course_name`, `course_details`, `price_range`, `duration`, `start_date`, `end_date`, `start_time`, `end_time`) VALUES
(1, 'Mehendis', 'asdasdsssssssssssssssssss', 'Rs.20000', '3 monthss', '2025-01-18', '2025-03-18', '19:37:00', '12:37:00'),
(3, 'Cooking', 'sadaasdasdasdasd', 'Rs. 25000', '1 week', '2025-01-27', '2025-01-31', '14:27:00', '14:27:00');

-- --------------------------------------------------------

--
-- Table structure for table `course_attendance`
--

CREATE TABLE `course_attendance` (
  `id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `status` enum('present','absent','late') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `course_attendance`
--

INSERT INTO `course_attendance` (`id`, `course_id`, `student_id`, `date`, `status`, `created_at`, `updated_at`) VALUES
(2, 1, 6, '2025-01-25', 'present', '2025-01-25 10:23:02', '2025-01-25 10:23:02'),
(3, 1, 5, '2025-01-25', 'present', '2025-01-25 10:23:02', '2025-01-25 10:23:02'),
(4, 3, 6, '2025-01-27', 'present', '2025-01-27 09:23:23', '2025-01-27 09:23:23'),
(5, 3, 5, '2025-01-27', 'present', '2025-01-27 09:23:23', '2025-01-27 09:23:23'),
(6, 1, 6, '2025-01-28', 'present', '2025-01-28 15:53:20', '2025-01-28 15:55:46'),
(7, 1, 5, '2025-01-28', 'absent', '2025-01-28 15:53:20', '2025-01-28 15:55:46'),
(8, 3, 6, '2025-08-27', 'present', '2025-08-27 14:27:08', '2025-08-27 14:27:08'),
(9, 3, 5, '2025-08-27', 'present', '2025-08-27 14:27:08', '2025-08-27 14:27:08');

-- --------------------------------------------------------

--
-- Table structure for table `course_materials`
--

CREATE TABLE `course_materials` (
  `id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `upload_date` datetime DEFAULT current_timestamp(),
  `end_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `grades`
--

CREATE TABLE `grades` (
  `id` int(11) NOT NULL,
  `grade_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `grades`
--

INSERT INTO `grades` (`id`, `grade_name`) VALUES
(1, 'Grade 1'),
(3, 'Grade 2');

-- --------------------------------------------------------

--
-- Table structure for table `individual_messages`
--

CREATE TABLE `individual_messages` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `student_type` enum('course','class') NOT NULL,
  `message` text NOT NULL,
  `attachment` varchar(255) DEFAULT NULL,
  `sent_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `individual_messages`
--

INSERT INTO `individual_messages` (`id`, `student_id`, `admin_id`, `student_type`, `message`, `attachment`, `sent_at`) VALUES
(3, 2, 1, 'class', 'asdasdasd', 'Messagin System.docx', '2025-01-26 14:09:45'),
(4, 2, 1, 'class', 'aaaaaaaaaaaaaaaaaa', '../uploads/messages/Tutor Management System(FULL).docx', '2025-01-26 14:14:08');

-- --------------------------------------------------------

--
-- Table structure for table `message`
--

CREATE TABLE `message` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `program` enum('course','class') NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `message`
--

INSERT INTO `message` (`id`, `username`, `program`, `message`, `created_at`) VALUES
(1, 'ST2', 'class', 'asdasdasd', '2025-01-28 14:11:19'),
(2, 'ST9', 'course', 'aaaaaaaa', '2025-01-28 14:12:05'),
(3, 'ST9', 'course', 'ccccc', '2025-01-28 14:14:35');

-- --------------------------------------------------------

--
-- Table structure for table `student`
--

CREATE TABLE `student` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `program` enum('course','class') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student`
--

INSERT INTO `student` (`id`, `name`, `username`, `password`, `program`) VALUES
(2, 'Najads', 'ST2', '$2y$10$DVS3LaEBwcujUZZmDmiOdeMmbaVyruAuC9u1KhV1h7vQmYLpjlbl2', 'class'),
(3, 'Najad', 'ST3', '$2y$10$/xQO92ga3dYm47nHNeCCfeJ7JjhEBZCM75ZgGgRZ8j883s2IBevb.', 'class'),
(5, 'Najads', 'ST8', '$2y$10$x80efiIsLrzvO9ne15rg8un4IosUOxqfuCh.C5GTdbs0wqwgE3Twq', 'course'),
(6, 'Najad', 'ST9', '$2y$10$PKm5S7AO8ANbd/3fhgBGGONDut0PRPieZG9Buomm8U51UECdD/lgu', 'course'),
(7, 'Mohommed Najadsssssssss', 'najad', '$2y$10$QegV7VDPSPrG8zR5lbbp3Oziyzt8HBj.somqTVy594vZSNo8uKWPO', 'course'),
(8, 'AM', 'ZeeNaj', '$2y$10$0i9tdmqUeUfQxCjZ1HDxW.0Kl8X3x1CgzxGBhCYP1pzxG89kXmXMq', 'course');

-- --------------------------------------------------------

--
-- Table structure for table `student_classes`
--

CREATE TABLE `student_classes` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_classes`
--

INSERT INTO `student_classes` (`id`, `student_id`, `class_id`) VALUES
(8, 2, 6),
(9, 2, 7),
(3, 3, 6),
(4, 3, 7);

-- --------------------------------------------------------

--
-- Table structure for table `student_courses`
--

CREATE TABLE `student_courses` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_courses`
--

INSERT INTO `student_courses` (`id`, `student_id`, `course_id`) VALUES
(5, 5, 1),
(4, 6, 1),
(6, 7, 3),
(7, 8, 3);

-- --------------------------------------------------------

--
-- Table structure for table `student_details`
--

CREATE TABLE `student_details` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `age` int(11) NOT NULL,
  `dob` date NOT NULL,
  `nic` varchar(20) DEFAULT NULL,
  `gender` enum('Male','Female','Other') NOT NULL,
  `contact` varchar(15) NOT NULL,
  `email` varchar(100) NOT NULL,
  `mom_name` varchar(100) NOT NULL,
  `dad_name` varchar(100) NOT NULL,
  `parent_contact` varchar(15) NOT NULL,
  `parent_email` varchar(100) DEFAULT NULL,
  `street` varchar(255) NOT NULL,
  `city` varchar(100) NOT NULL,
  `state` varchar(100) NOT NULL,
  `postal_code` varchar(10) NOT NULL,
  `type` enum('course','class') NOT NULL,
  `profile_image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_details`
--

INSERT INTO `student_details` (`id`, `student_id`, `age`, `dob`, `nic`, `gender`, `contact`, `email`, `mom_name`, `dad_name`, `parent_contact`, `parent_email`, `street`, `city`, `state`, `postal_code`, `type`, `profile_image`) VALUES
(2, 2, 20, '2025-01-22', '2003225007321', 'Male', '0769068539', 'perfection09523@gmail.com', 'asdas', 'asdasd', '0769068538', 'perfection09543@gmail.com', 'River Side Roads', 'Ratnapuras', 'Sabaragamuwas', '70000', 'class', '../uploads/profile_images/BMKV3845.JPG'),
(5, 5, 21, '2025-01-23', '200322500732122352', 'Male', '076906853612233', 'perfection0953612234@gmail.com', 'asdas', 'asdasd', '076906853361223', 'perfection09534612232@gmail.com', 'River Side Road', 'Ratnapuraa', 'Sabaragamuwa', '70000', 'course', ''),
(6, 6, 20, '2025-01-23', '200322500732122351', 'Male', '076906853612233', 'perfection09536122343@gmail.com', 'asdas', 'asdasd', '076906853361223', 'perfection095346122325@gmail.com', 'River Side Road', 'Ratnapura', 'Sabaragamuwa', '70000', 'course', '../uploads/profile_images/ABMQ6173.JPG'),
(7, 7, 20, '2025-01-27', '2003225007', 'Male', '076906853', 'najadmhommed34@gmail.com', 'asdasd', 'asdasd', '079068534', 'najadmohomed34@gmail.com', '130/1, River Side Road, Ratnapura', 'Ratnapura', 'Sabaragamuwa', '70000', 'course', '../uploads/profile_images/67974bf0da35b_ADEJ5314.JPG'),
(8, 8, 22, '2025-08-25', '2003', 'Male', '072222222222222', 'na@gmail.com', 'ze', 'ji', '07333333333', 'na@gmail.com', '130/1, River Side Road, Ratnapura, Sri Lanka', 'Ratnapura', 'Sabaragamuwa', '70000', 'course', '');

-- --------------------------------------------------------

--
-- Table structure for table `subjects`
--

CREATE TABLE `subjects` (
  `id` int(11) NOT NULL,
  `subject_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subjects`
--

INSERT INTO `subjects` (`id`, `subject_name`) VALUES
(1, 'Sinhala'),
(2, 'English'),
(3, 'Islam');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `admin_details`
--
ALTER TABLE `admin_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `admin_free_slots`
--
ALTER TABLE `admin_free_slots`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `broadcast_message`
--
ALTER TABLE `broadcast_message`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `classes`
--
ALTER TABLE `classes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `grade` (`grade`),
  ADD KEY `subject` (`subject`);

--
-- Indexes for table `class_attendance`
--
ALTER TABLE `class_attendance`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_class_student_date` (`class_id`,`student_id`,`date`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `class_days_times`
--
ALTER TABLE `class_days_times`
  ADD PRIMARY KEY (`id`),
  ADD KEY `class_id` (`class_id`);

--
-- Indexes for table `class_materials`
--
ALTER TABLE `class_materials`
  ADD PRIMARY KEY (`id`),
  ADD KEY `class_id` (`class_id`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `course_attendance`
--
ALTER TABLE `course_attendance`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_course_student_date` (`course_id`,`student_id`,`date`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `course_materials`
--
ALTER TABLE `course_materials`
  ADD PRIMARY KEY (`id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `grades`
--
ALTER TABLE `grades`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `individual_messages`
--
ALTER TABLE `individual_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `message`
--
ALTER TABLE `message`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `student`
--
ALTER TABLE `student`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `student_classes`
--
ALTER TABLE `student_classes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_student_class` (`student_id`,`class_id`),
  ADD KEY `class_id` (`class_id`);

--
-- Indexes for table `student_courses`
--
ALTER TABLE `student_courses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_student_course` (`student_id`,`course_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `student_details`
--
ALTER TABLE `student_details`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `admin_details`
--
ALTER TABLE `admin_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `admin_free_slots`
--
ALTER TABLE `admin_free_slots`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `broadcast_message`
--
ALTER TABLE `broadcast_message`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `classes`
--
ALTER TABLE `classes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `class_attendance`
--
ALTER TABLE `class_attendance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `class_days_times`
--
ALTER TABLE `class_days_times`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `class_materials`
--
ALTER TABLE `class_materials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `course_attendance`
--
ALTER TABLE `course_attendance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `course_materials`
--
ALTER TABLE `course_materials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `grades`
--
ALTER TABLE `grades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `individual_messages`
--
ALTER TABLE `individual_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `message`
--
ALTER TABLE `message`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `student`
--
ALTER TABLE `student`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `student_classes`
--
ALTER TABLE `student_classes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `student_courses`
--
ALTER TABLE `student_courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `student_details`
--
ALTER TABLE `student_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin_details`
--
ALTER TABLE `admin_details`
  ADD CONSTRAINT `admin_details_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `admin` (`admin_id`);

--
-- Constraints for table `broadcast_message`
--
ALTER TABLE `broadcast_message`
  ADD CONSTRAINT `broadcast_message_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `admin` (`admin_id`) ON DELETE CASCADE;

--
-- Constraints for table `classes`
--
ALTER TABLE `classes`
  ADD CONSTRAINT `classes_ibfk_1` FOREIGN KEY (`grade`) REFERENCES `grades` (`id`),
  ADD CONSTRAINT `classes_ibfk_2` FOREIGN KEY (`subject`) REFERENCES `subjects` (`id`);

--
-- Constraints for table `class_attendance`
--
ALTER TABLE `class_attendance`
  ADD CONSTRAINT `class_attendance_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `class_attendance_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `student` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `class_days_times`
--
ALTER TABLE `class_days_times`
  ADD CONSTRAINT `class_days_times_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `class_materials`
--
ALTER TABLE `class_materials`
  ADD CONSTRAINT `class_materials_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`);

--
-- Constraints for table `course_attendance`
--
ALTER TABLE `course_attendance`
  ADD CONSTRAINT `course_attendance_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `course_attendance_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `student` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `course_materials`
--
ALTER TABLE `course_materials`
  ADD CONSTRAINT `course_materials_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `individual_messages`
--
ALTER TABLE `individual_messages`
  ADD CONSTRAINT `individual_messages_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `student` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `individual_messages_ibfk_2` FOREIGN KEY (`admin_id`) REFERENCES `admin` (`admin_id`) ON DELETE CASCADE;

--
-- Constraints for table `student_classes`
--
ALTER TABLE `student_classes`
  ADD CONSTRAINT `student_classes_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `student` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_classes_ibfk_2` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `student_courses`
--
ALTER TABLE `student_courses`
  ADD CONSTRAINT `student_courses_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `student` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_courses_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `student_details`
--
ALTER TABLE `student_details`
  ADD CONSTRAINT `student_details_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `student` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
