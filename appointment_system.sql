-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 12, 2024 at 11:59 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `appointment_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `about_us`
--

CREATE TABLE `about_us` (
  `about_id` int(11) NOT NULL,
  `about_text` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `about_us`
--

INSERT INTO `about_us` (`about_id`, `about_text`) VALUES
(3, 'Welcome to “Dental Clinic Bookings”, we pride ourselves on providing dental clinic booking services in an easy and efficient way. At Dental Bookings, we combine convenience and technology to make the dental appointment booking experience easier. Our mission: Providing a reliable and easy-to-use platform that enables you to instantly book medical visits at dental clinics with ease and ease. What distinguishes us: \n1. *Ease of use:* A simplified and easy booking experience to save time and effort.\n 2. *A wide variety of clinics:* We offer you a variety of dental clinics to meet your specific needs.\n 3. *Dedicated medical team:* Deal with professional and experienced dentists who care about your oral health.\n 4. *Modern Technology:* We use the latest technologies to ensure that we provide the best services to our patients. We are here to make your dental visit a comfortable and streamlined experience. Join us on your dental health journey, as we put your health at the forefront of our concerns.\n\n\n');

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `appointment_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `dentist_id` int(11) NOT NULL,
  `appointment_date` datetime NOT NULL,
  `appointment_time` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `appointment_status` varchar(255) DEFAULT NULL,
  `services` varchar(220) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`appointment_id`, `patient_id`, `dentist_id`, `appointment_date`, `appointment_time`, `appointment_status`, `services`) VALUES
(18, 32, 2, '2024-02-14 00:00:00', '2024-02-12 10:20:17', NULL, '2');

-- --------------------------------------------------------

--
-- Table structure for table `contact_us`
--

CREATE TABLE `contact_us` (
  `u_id` int(11) NOT NULL,
  `cont_user_name` varchar(255) NOT NULL,
  `cont_user_email` varchar(255) NOT NULL,
  `cont_user_text` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `dentists`
--

CREATE TABLE `dentists` (
  `dentist_id` int(11) NOT NULL,
  `dentist_name` varchar(255) NOT NULL,
  `dentist_age` int(11) NOT NULL,
  `dentist_email` varchar(255) NOT NULL,
  `dentist_brief` text NOT NULL,
  `image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `dentists`
--

INSERT INTO `dentists` (`dentist_id`, `dentist_name`, `dentist_age`, `dentist_email`, `dentist_brief`, `image`) VALUES
(1, 'Sara\'a', 30, 'ood@gmail.com', 'Dr. Sara\'a is a highly experienced dentist with over 20 years in the field.\n   - Specializes in cosmetic dentistry and has a reputation for creating beautiful smiles.\n   - Known for her gentle and compassionate approach, making patients feel at ease.\n\n', '1.jpg'),
(2, 'BASSAM', 40, 'mnakjjgfsher28@gmail.com', ' - Dr. BASSAM is a skilled orthodontist with a focus on correcting dental misalignments.\n   - Actively involved in community outreach programs, promoting dental health education.\n   - Embraces the latest technology for efficient and comfortable orthodontic treatments.\n', '2.jpg'),
(3, 'Ali', 45, 'basslgfam@gmail.com', ' - Dr. Ali is a family dentist providing comprehensive care for patients of all ages.\n   - Passionate about preventive dentistry, emphasizing regular check-ups and oral hygiene.\n   - Fluent in English and Spanish, fostering a diverse and inclusive dental practice.\n', '4.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `patients`
--

CREATE TABLE `patients` (
  `patient_id` int(11) NOT NULL,
  `patient_name` varchar(255) NOT NULL,
  `patient_age` int(11) NOT NULL,
  `patient_email` varchar(255) NOT NULL,
  `ppassword` varchar(250) NOT NULL,
  `phone_number` int(11) NOT NULL,
  `patient_gender` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `patients`
--

INSERT INTO `patients` (`patient_id`, `patient_name`, `patient_age`, `patient_email`, `ppassword`, `phone_number`, `patient_gender`) VALUES
(32, 'mahmood', 56, 'bassccc@gmail.com', '', 2147483647, '1');

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `service_id` int(11) NOT NULL,
  `service_name` varchar(255) NOT NULL,
  `service_price` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`service_id`, `service_name`, `service_price`) VALUES
(1, 'cleaning', '14$'),
(2, 'surgery', '50$');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(100) NOT NULL,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `add_date` datetime NOT NULL,
  `active` bit(1) NOT NULL DEFAULT b'1',
  `role` varchar(255) NOT NULL DEFAULT 'user',
  `image` varchar(250) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `add_date`, `active`, `role`, `image`) VALUES
(36, 'admin', 'admin@gmail.com', '$2y$10$kFx6JdbkOHJQzHPZfsTdKOwusMF5N2HihFBv4zeF/vCvRtjoPbOPm', '0000-00-00 00:00:00', b'1', 'admin', 'dfe6fce2e0ce11d195d94bb44f23e2a9.jpg'),
(41, 'mahmood', 'nashermahmood@gmail.com', '$2y$10$ToCzKcNWMgW3BLoV0gSckuco6HzDMBfKH4z9a5RR3wJN/.wfAtH.y', '0000-00-00 00:00:00', b'1', 'user', '259a76b99b54639982060532413b2127.jpg');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `about_us`
--
ALTER TABLE `about_us`
  ADD PRIMARY KEY (`about_id`);

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`appointment_id`),
  ADD KEY `patient_id_fk` (`patient_id`),
  ADD KEY `dentist_id_fk` (`dentist_id`);

--
-- Indexes for table `contact_us`
--
ALTER TABLE `contact_us`
  ADD PRIMARY KEY (`u_id`);

--
-- Indexes for table `dentists`
--
ALTER TABLE `dentists`
  ADD PRIMARY KEY (`dentist_id`);

--
-- Indexes for table `patients`
--
ALTER TABLE `patients`
  ADD PRIMARY KEY (`patient_id`),
  ADD UNIQUE KEY `email` (`patient_email`),
  ADD KEY `patient_gender` (`patient_gender`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`service_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `about_us`
--
ALTER TABLE `about_us`
  MODIFY `about_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `appointment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `contact_us`
--
ALTER TABLE `contact_us`
  MODIFY `u_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `dentists`
--
ALTER TABLE `dentists`
  MODIFY `dentist_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `patients`
--
ALTER TABLE `patients`
  MODIFY `patient_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `service_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `dentist_id_fk` FOREIGN KEY (`dentist_id`) REFERENCES `dentists` (`dentist_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `patient_id_fk` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`patient_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
