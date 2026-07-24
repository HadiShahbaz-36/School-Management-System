-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 22, 2026 at 03:42 PM
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
-- Database: `students_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `username` varchar(20) NOT NULL,
  `email` varchar(80) NOT NULL,
  `password` varchar(60) NOT NULL,
  `user_type` enum('admin','fee_manager') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `username`, `email`, `password`, `user_type`) VALUES
(2, 'ahs', 'ahs@gmail.com', 'ahs2', 'fee_manager'),
(28, 'hadi', 'shahbazhadi20@gmail.com', 'hadi', 'admin');

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `id` int(11) NOT NULL,
  `student_id` varchar(100) NOT NULL,
  `status` varchar(20) NOT NULL,
  `attendance_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`id`, `student_id`, `status`, `attendance_date`) VALUES
(1, 'YOUR_ENROLL_HERE', 'Present', '2026-01-03'),
(2, '12305', 'Present', '2026-01-22');

-- --------------------------------------------------------

--
-- Table structure for table `fees`
--

CREATE TABLE `fees` (
  `fee_id` int(11) NOT NULL,
  `student_enrollment` varchar(255) DEFAULT NULL,
  `amount_paid` decimal(10,2) DEFAULT NULL,
  `payment_date` date DEFAULT NULL,
  `month_for` varchar(20) DEFAULT NULL,
  `status` enum('Paid','Pending') DEFAULT 'Paid'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `fees`
--

INSERT INTO `fees` (`fee_id`, `student_enrollment`, `amount_paid`, `payment_date`, `month_for`, `status`) VALUES
(2, '12305', 10150.00, NULL, 'January', 'Pending'),
(9, '10135', 10000.00, NULL, 'January', 'Pending'),
(11, '00000', 5000.00, NULL, 'January', 'Pending');

-- --------------------------------------------------------

--
-- Table structure for table `fee_adjustments`
--

CREATE TABLE `fee_adjustments` (
  `id` int(11) NOT NULL,
  `stu_enrollment_number` varchar(50) NOT NULL,
  `adj_type` enum('Fine','Concession') NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `reason` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` varchar(20) DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `fee_adjustments`
--

INSERT INTO `fee_adjustments` (`id`, `stu_enrollment_number`, `adj_type`, `amount`, `reason`, `created_at`, `status`) VALUES
(2, '12305', 'Fine', 400.00, 'Late comer', '2026-01-17 10:21:54', 'Applied'),
(3, '10135', 'Fine', 5000.00, 'Baal nai katwata', '2026-01-17 11:40:42', 'Applied'),
(4, '00000', 'Fine', 500.00, 'late', '2026-01-18 06:16:31', 'Applied');

-- --------------------------------------------------------

--
-- Table structure for table `fee_structure`
--

CREATE TABLE `fee_structure` (
  `id` int(11) NOT NULL,
  `wing_name` varchar(100) DEFAULT NULL,
  `category_name` varchar(50) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `fee_structure`
--

INSERT INTO `fee_structure` (`id`, `wing_name`, `category_name`, `amount`) VALUES
(1, 'Lower Primary', 'CIV', 5000.00),
(2, 'Lower Primary', 'PN CIV', 0.00),
(3, 'Lower Primary', 'PN SAILOR', 0.00),
(4, 'Lower Primary', 'ARMY', 0.00),
(5, 'Lower Primary', 'STAFF', 0.00),
(6, 'Lower Primary', 'PNET', 0.00),
(7, 'Lower Primary', 'PAF', 0.00),
(8, 'Lower Primary', 'SPD', 5000.00),
(9, 'Lower Primary', 'PN', 0.00),
(10, 'Lower Primary', 'Faculty', 0.00),
(11, 'Upper Primary', 'CIV', 0.00),
(12, 'Upper Primary', 'PN CIV', 0.00),
(13, 'Upper Primary', 'PN SAILOR', 0.00),
(14, 'Upper Primary', 'ARMY', 0.00),
(15, 'Upper Primary', 'STAFF', 0.00),
(16, 'Upper Primary', 'PNET', 0.00),
(17, 'Upper Primary', 'PAF', 0.00),
(18, 'Upper Primary', 'SPD', 0.00),
(19, 'Upper Primary', 'PN', 0.00),
(20, 'Upper Primary', 'Faculty', 0.00),
(21, 'Boys wing', 'CIV', 0.00),
(22, 'Boys wing', 'PN CIV', 0.00),
(23, 'Boys wing', 'PN SAILOR', 0.00),
(24, 'Boys wing', 'ARMY', 0.00),
(25, 'Boys wing', 'STAFF', 0.00),
(26, 'Boys wing', 'PNET', 0.00),
(27, 'Boys wing', 'PAF', 0.00),
(28, 'Boys wing', 'SPD', 0.00),
(29, 'Boys wing', 'PN', 0.00),
(30, 'Boys wing', 'Faculty', 0.00),
(31, 'Girls wing', 'CIV', 0.00),
(32, 'Girls wing', 'PN CIV', 0.00),
(33, 'Girls wing', 'PN SAILOR', 0.00),
(34, 'Girls wing', 'ARMY', 5000.00),
(35, 'Girls wing', 'STAFF', 0.00),
(36, 'Girls wing', 'PNET', 0.00),
(37, 'Girls wing', 'PAF', 0.00),
(38, 'Girls wing', 'SPD', 0.00),
(39, 'Girls wing', 'PN', 0.00),
(40, 'Girls wing', 'Faculty', 0.00),
(41, 'Cambridge', 'CIV', 13000.00),
(42, 'Cambridge', 'PN CIV', 0.00),
(43, 'Cambridge', 'PN SAILOR', 0.00),
(44, 'Cambridge', 'ARMY', 0.00),
(45, 'Cambridge', 'STAFF', 0.00),
(46, 'Cambridge', 'PNET', 0.00),
(47, 'Cambridge', 'PAF', 0.00),
(48, 'Cambridge', 'SPD', 0.00),
(49, 'Cambridge', 'PN', 0.00),
(50, 'Cambridge', 'Faculty', 0.00),
(51, 'Special education', 'CIV', 0.00),
(52, 'Special education', 'PN CIV', 0.00),
(53, 'Special education', 'PN SAILOR', 0.00),
(54, 'Special education', 'ARMY', 0.00),
(55, 'Special education', 'STAFF', 0.00),
(56, 'Special education', 'PNET', 0.00),
(57, 'Special education', 'PAF', 0.00),
(58, 'Special education', 'SPD', 0.00),
(59, 'Special education', 'PN', 0.00),
(60, 'Special education', 'Faculty', 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `scholarships`
--

CREATE TABLE `scholarships` (
  `id` int(11) NOT NULL,
  `stu_enrollment_number` varchar(255) DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `discount_percentage` int(11) DEFAULT NULL,
  `granted_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `scholarships`
--

INSERT INTO `scholarships` (`id`, `stu_enrollment_number`, `reason`, `discount_percentage`, `granted_at`) VALUES
(5, '12305', 'Sports', 25, '2026-01-17 10:21:31'),
(6, '00000', 'good marks ', 10, '2026-01-18 06:16:13');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `stu_enrollment_number` varchar(255) NOT NULL,
  `stu_name` varchar(50) NOT NULL,
  `stu_father_name` varchar(255) DEFAULT NULL,
  `stu_father_designation` varchar(255) DEFAULT NULL,
  `stu_contact` varchar(20) DEFAULT NULL,
  `password` varchar(255) DEFAULT '12345',
  `stu_wing` varchar(100) DEFAULT NULL,
  `fee_category` varchar(50) DEFAULT NULL,
  `stu_marks_status` varchar(20) NOT NULL,
  `stu_attendance` varchar(20) NOT NULL,
  `stu_fee_status` enum('Paid','Unpaid') NOT NULL DEFAULT 'Unpaid',
  `stu_result_pdf` varchar(255) DEFAULT NULL,
  `stu_email` varchar(100) DEFAULT NULL,
  `stu_cnic` varchar(20) DEFAULT NULL,
  `stu_religion` varchar(50) DEFAULT NULL,
  `stu_class` varchar(50) DEFAULT NULL,
  `father_cnic` varchar(20) DEFAULT NULL,
  `father_occupation` varchar(100) DEFAULT NULL,
  `father_contact` varchar(20) DEFAULT NULL,
  `mother_name` varchar(100) DEFAULT NULL,
  `mother_cnic` varchar(20) DEFAULT NULL,
  `mother_occupation` varchar(100) DEFAULT NULL,
  `mother_contact` varchar(20) DEFAULT NULL,
  `residential_address` text DEFAULT NULL,
  `blood_group` varchar(5) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `stu_photo` varchar(255) DEFAULT 'default.png'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`stu_enrollment_number`, `stu_name`, `stu_father_name`, `stu_father_designation`, `stu_contact`, `password`, `stu_wing`, `fee_category`, `stu_marks_status`, `stu_attendance`, `stu_fee_status`, `stu_result_pdf`, `stu_email`, `stu_cnic`, `stu_religion`, `stu_class`, `father_cnic`, `father_occupation`, `father_contact`, `mother_name`, `mother_cnic`, `mother_occupation`, `mother_contact`, `residential_address`, `blood_group`, `date_of_birth`, `stu_photo`) VALUES
('00000', 'dsfds', 'asdsad', NULL, 'sdfsdfds', 'peak', 'Lower Primary', 'CIV', '', '0', 'Unpaid', NULL, 'fsdf@sdf.com', 'sdfdsf', 'Islam', 'Class 2', 'dsfsdf', 'fsdfsd', 'sdfsdf', NULL, NULL, NULL, NULL, NULL, 'sdfds', '2021-05-06', '00000_1768716936.png'),
('10135', 'Muhammad Azan Chachar', 'Aon Muhammad', NULL, '03175724112', 'solid10secondswith13years', 'Girls wing', 'ARMY', '', '0', 'Unpaid', NULL, 'mazan3611@gmail.com', '31201-7607090-5', 'Islam', 'VI', '3434', 'LAWER', '030000000000', NULL, NULL, NULL, NULL, NULL, 'O-', '2009-06-12', '10135_1768649216.png'),
('12305', 'Abdul Hadi Shahbaz', 'Shahbaz Mahmood', 'OGRA', '0313-5444071', '12345', 'Cambridge', 'CIV', '99%', '99%', 'Unpaid', NULL, 'shahbazhadi20@gmail.com', '61101-3440293-5', 'ISLAM', 'Senior-2', '61101-3948119-1', 'OGRA', '0331-5945682', NULL, NULL, NULL, NULL, NULL, 'B+', '2010-09-03', '12305_1767431373.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `student_marks`
--

CREATE TABLE `student_marks` (
  `mark_id` int(11) NOT NULL,
  `enrollment_number` varchar(50) DEFAULT NULL,
  `term_name` varchar(50) DEFAULT NULL,
  `subject_name` varchar(100) DEFAULT NULL,
  `total_marks` int(11) DEFAULT NULL,
  `obtained_marks` int(11) DEFAULT NULL,
  `percentage` float DEFAULT NULL,
  `upload_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_marks`
--

INSERT INTO `student_marks` (`mark_id`, `enrollment_number`, `term_name`, `subject_name`, `total_marks`, `obtained_marks`, `percentage`, `upload_date`) VALUES
(1, '12305', 'Final Examination', 'ENGLISH ', 100, 95, 95, '2026-01-02 17:29:56'),
(2, '12305', 'Final Examination', 'URDU', 100, 86, 86, '2026-01-02 17:29:56'),
(3, '12305', 'Final Examination', 'MATHS ', 100, 75, 75, '2026-01-02 17:29:56'),
(4, '12305', 'Final Examination', 'COMPUTER', 20, 19, 95, '2026-01-02 17:29:56'),
(5, '10135', 'Final Examination', 'PAK STUDIES', 150, 90, 60, '2026-01-17 11:44:59'),
(6, '10135', 'Final Examination', 'Islamiyat', 100, 40, 40, '2026-01-17 11:44:59'),
(7, '10135', 'Final Examination', 'URDU', 100, 66, 66, '2026-01-17 11:44:59'),
(8, '12305', 'Final Examination', 'PAK STUDIES', 150, 120, 80, '2026-01-22 07:56:22'),
(9, '12305', 'Final Examination', 'ENGLISH ', 100, 50, 50, '2026-01-22 07:56:22');

-- --------------------------------------------------------

--
-- Table structure for table `teachers`
--

CREATE TABLE `teachers` (
  `teacher_id` int(11) NOT NULL,
  `teacher_name` varchar(100) NOT NULL,
  `teacher_email` varchar(100) NOT NULL,
  `teacher_contact` varchar(20) DEFAULT NULL,
  `teacher_wing` varchar(50) DEFAULT NULL,
  `teacher_class` varchar(50) DEFAULT NULL,
  `teacher_subject` varchar(50) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `teacher_photo` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `marital_status` enum('Married','Unmarried') DEFAULT 'Unmarried',
  `relation_name` varchar(100) DEFAULT NULL,
  `grading_scale` varchar(50) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `contact_no` varchar(20) DEFAULT NULL,
  `cnic` varchar(20) DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `matric_marks` varchar(50) DEFAULT NULL,
  `inter_marks` varchar(50) DEFAULT NULL,
  `highest_qualification` varchar(100) DEFAULT NULL,
  `university` varchar(100) DEFAULT NULL,
  `subject_specialization` varchar(100) DEFAULT NULL,
  `teaching_experience` varchar(50) DEFAULT NULL,
  `gender` varchar(20) DEFAULT NULL,
  `emergency_contact` varchar(20) DEFAULT NULL,
  `prev_institutes` text DEFAULT NULL,
  `joining_date` date DEFAULT NULL,
  `employment_type` varchar(50) DEFAULT NULL,
  `salary_amount` decimal(10,2) DEFAULT NULL,
  `bank_details` text DEFAULT NULL,
  `teacher_id_official` varchar(50) DEFAULT NULL,
  `designation` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `teachers`
--

INSERT INTO `teachers` (`teacher_id`, `teacher_name`, `teacher_email`, `teacher_contact`, `teacher_wing`, `teacher_class`, `teacher_subject`, `password`, `teacher_photo`, `created_at`, `marital_status`, `relation_name`, `grading_scale`, `address`, `contact_no`, `cnic`, `dob`, `matric_marks`, `inter_marks`, `highest_qualification`, `university`, `subject_specialization`, `teaching_experience`, `gender`, `emergency_contact`, `prev_institutes`, `joining_date`, `employment_type`, `salary_amount`, `bank_details`, `teacher_id_official`, `designation`) VALUES
(1, 'Taha Ali', 'taha.rock72@gmail.com', NULL, 'Cambridge', 'P-1, P-2, P-3, Senior-1, Senior-2, Senior-3', NULL, 'tahaali', 'TCH_1767459160_8514.jpg', '2026-01-01 09:21:49', 'Unmarried', 'Syed Abbas', NULL, 'Chak Shahzad', '03365570608', '61101-3440293-5', '2000-10-06', NULL, NULL, '', '', '', '0', 'Male', '', '', '2023-08-03', 'Full-time', 0.00, '', 'BCI-6967', 'Lecturer');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `fees`
--
ALTER TABLE `fees`
  ADD PRIMARY KEY (`fee_id`),
  ADD UNIQUE KEY `unique_fee` (`student_enrollment`,`month_for`);

--
-- Indexes for table `fee_adjustments`
--
ALTER TABLE `fee_adjustments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `fee_structure`
--
ALTER TABLE `fee_structure`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `wing_cat_pair` (`wing_name`,`category_name`);

--
-- Indexes for table `scholarships`
--
ALTER TABLE `scholarships`
  ADD PRIMARY KEY (`id`),
  ADD KEY `stu_enrollment_number` (`stu_enrollment_number`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`stu_enrollment_number`),
  ADD KEY `stu_enrollment_number` (`stu_enrollment_number`);

--
-- Indexes for table `student_marks`
--
ALTER TABLE `student_marks`
  ADD PRIMARY KEY (`mark_id`);

--
-- Indexes for table `teachers`
--
ALTER TABLE `teachers`
  ADD PRIMARY KEY (`teacher_id`),
  ADD UNIQUE KEY `teacher_email` (`teacher_email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `fees`
--
ALTER TABLE `fees`
  MODIFY `fee_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `fee_adjustments`
--
ALTER TABLE `fee_adjustments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `fee_structure`
--
ALTER TABLE `fee_structure`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=481;

--
-- AUTO_INCREMENT for table `scholarships`
--
ALTER TABLE `scholarships`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `student_marks`
--
ALTER TABLE `student_marks`
  MODIFY `mark_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `teachers`
--
ALTER TABLE `teachers`
  MODIFY `teacher_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `scholarships`
--
ALTER TABLE `scholarships`
  ADD CONSTRAINT `fk_scholarship_student` FOREIGN KEY (`stu_enrollment_number`) REFERENCES `students` (`stu_enrollment_number`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
