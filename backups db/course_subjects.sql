-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 26, 2022 at 06:06 AM
-- Server version: 10.4.22-MariaDB
-- PHP Version: 8.1.2

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `imperial college`
--

-- --------------------------------------------------------

--
-- Table structure for table `course_subjects`
--

CREATE TABLE `course_subjects` (
  `subject_code` varchar(10) NOT NULL,
  `subject_name` varchar(50) NOT NULL,
  `course_code` varchar(10) NOT NULL,
  `semester` int(10) NOT NULL,
  `credit_hours` int(5) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `course_subjects`
--

INSERT INTO `course_subjects` (`subject_code`, `subject_name`, `course_code`, `semester`, `credit_hours`) VALUES
('101', 'IT/PC', 'BCA', 1, 1),
('102', 'Business communication', 'BCA', 1, 1),
('1021', 'Botany-1', 'BSC', 1, 1),
('1022', 'Botany-2', 'BSC', 1, 1),
('1023', 'Botany-3', 'BSC', 1, 1),
('103', 'Problem Solving through C programming', 'BCA', 1, 1),
('104', 'Basic Physics', 'BCA', 1, 1),
('1041', 'Chemistry-1', 'BSC', 1, 1),
('1042', 'Chemistry-2', 'BSC', 1, 1),
('1043', 'Chemistry-3', 'BSC', 1, 1),
('105', 'Basic Mathametics', 'BCA', 1, 1),
('106', 'Computer Organization', 'BCA', 1, 1),
('1141', 'Mathematics-1', 'BSC', 1, 1),
('1142', 'Mathematics-2', 'BSC', 1, 1),
('1143', 'Mathematics-3', 'BSC', 1, 1),
('1161', 'Physics-1', 'BSC', 1, 1),
('1162', 'Physics-2', 'BSC', 1, 1),
('1163', 'Physics-3', 'BSC', 1, 1),
('1201', 'Zoology-1', 'BSC', 1, 1),
('1202', 'Zoology-2', 'BSC', 1, 1),
('1203', 'Zoology-3', 'BSC', 1, 1),
('1207', 'Elementary Computer Application', 'BSC', 1, 1),
('1209', 'Environmental Studies', 'BCA', 1, 1),
('1381', 'Hindi Lit-1', 'BA', 1, 1),
('1382', 'Hindi Lit-2', 'BA', 1, 1),
('1401', 'History-1', 'BA', 1, 1),
('1402', 'History-2', 'BA', 1, 1),
('1481', 'Foundations of Political Science', 'BA', 1, 1),
('1482', 'Indian Political Thought', 'BA', 1, 1),
('1704', 'General English', 'BCA', 1, 1),
('1705', 'General Hindi', 'BCA', 2, 1),
('1802', 'Financial Accounting', 'B.COM', 1, 1),
('1803', 'Business Statics', 'B.COM', 1, 1),
('1821', 'Principles of Business Management', 'B.COM', 1, 1),
('1822', 'Business Reg.Framework', 'B.COM', 1, 1),
('1842', 'Economics Environment', 'B.COM', 1, 1),
('1843', 'Business Economics', 'B.COM', 1, 1),
('201', 'Computer Communication and Networks', 'BCA', 2, 1),
('202', 'DataBase Management System', 'BCA', 2, 1),
('2021', 'Botany-1', 'BSC', 2, 1),
('2022', 'Botany-2', 'BSC', 2, 1),
('2023', 'Botany-3', 'BSC', 2, 1),
('203', 'Fundamental of Operating System', 'BCA', 2, 1),
('204', 'Data Structure', 'BCA', 2, 1),
('2041', 'Chemistry-1', 'BSC', 2, 1),
('2042', 'Chemistry-2', 'BSC', 2, 1),
('2043', 'Chemistry-3', 'BSC', 2, 1),
('205', 'System Analysis and Design', 'BCA', 2, 1),
('206', 'Object Oriented Programming using C++', 'BCA', 2, 1),
('2141', 'Mathematics-1', 'BSC', 2, 1),
('2142', 'Mathematics-2', 'BSC', 2, 1),
('2143', 'Mathematics-3', 'BSC', 2, 1),
('2161', 'Physics-1', 'BSC', 2, 1),
('2162', 'Physics-2', 'BSC', 2, 1),
('2163', 'Physics-3', 'BSC', 2, 1),
('2201', 'Zoology-1', 'BSC', 2, 1),
('2202', 'Zoology-2', 'BSC', 2, 1),
('2203', 'Zoology-3', 'BSC', 2, 1),
('2381', 'Hindi Lit-1', 'BA', 2, 1),
('2382', 'Hindi Lit-2', 'BA', 2, 1),
('2401', 'History-1', 'BA', 2, 1),
('2402', 'History-2', 'BA', 2, 1),
('2481', 'political science -1', 'BA', 2, 1),
('2482', 'political science - 2', 'BA', 2, 1),
('2801', 'Income Tax', 'B.COM', 2, 1),
('2802', 'Corporate Accounting', 'B.COM', 2, 1),
('2803', 'Cost Accounting', 'B.COM', 2, 1),
('2821', 'Company Law', 'B.COM', 2, 1),
('2822', 'Marketing Management', 'B.COM', 2, 1),
('2842', 'Money and Fin. System', 'B.COM', 2, 1),
('2843', 'Banking Law and Practice in India', 'B.COM', 2, 1),
('301', 'Java Programming', 'BCA', 3, 1),
('302', 'Information System', 'BCA', 3, 1),
('3021', 'Botany-1', 'BSC', 3, 1),
('3022', 'Botany-2', 'BSC', 3, 1),
('3023', 'Botany-3', 'BSC', 3, 1),
('303', 'Cloud Computing', 'BCA', 3, 1),
('304', 'Wireless and Mobile Computing', 'BCA', 3, 1),
('3041', 'Chemistry-1', 'BSC', 3, 1),
('3042', 'Chemistry-2', 'BSC', 3, 1),
('3043', 'Chemistry-3', 'BSC', 3, 1),
('305', 'Web Technology', 'BCA', 3, 1),
('3141', 'Mathematics-1', 'BSC', 3, 1),
('3142', 'Mathematics-2', 'BSC', 3, 1),
('3143', 'Mathematics-3(A)', 'BSC', 3, 1),
('3143A', 'Mathematics-3(A)', 'BSC', 3, 1),
('3143B', 'Mathematics-3(B)', 'BSC', 3, 1),
('3143C', 'Mathematics-3(C)', 'BSC', 3, 1),
('3161', 'Physics-1', 'BSC', 3, 1),
('3162', 'Physics-2', 'BSC', 3, 1),
('3163', 'Physics-3', 'BSC', 3, 1),
('3201', 'Zoology-1', 'BSC', 3, 1),
('3202', 'Zoology-2', 'BSC', 3, 1),
('3203', 'Zoology-3', 'BSC', 3, 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `course_subjects`
--
ALTER TABLE `course_subjects`
  ADD PRIMARY KEY (`subject_code`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
