-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3307
-- Generation Time: May 27, 2026 at 09:27 AM
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
-- Database: `school_website`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_users`
--

CREATE TABLE `admin_users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `email` varchar(255) DEFAULT NULL,
  `google_id` varchar(255) DEFAULT NULL,
  `profile_pic` varchar(500) DEFAULT NULL,
  `auth_type` enum('manual','google') DEFAULT 'manual',
  `status` enum('pending','approved','rejected') DEFAULT 'approved',
  `role` enum('admin','user') DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_users`
--

INSERT INTO `admin_users` (`id`, `username`, `password`, `created_at`, `email`, `google_id`, `profile_pic`, `auth_type`, `status`, `role`) VALUES
(1, 'admin', 'password123', '2026-05-19 10:38:11', NULL, NULL, NULL, 'manual', 'approved', 'admin'),
(2, 'manoj', '98765', '2026-05-19 10:51:41', NULL, NULL, NULL, 'manual', 'approved', 'admin'),
(3, 'mannu', '1234', '2026-05-19 10:52:24', NULL, NULL, NULL, 'manual', 'approved', 'admin'),
(4, 'harish', '987654321', '2026-05-20 10:17:01', NULL, NULL, NULL, 'manual', 'approved', 'admin'),
(5, 'skjB', '987654', '2026-05-21 09:02:26', NULL, NULL, NULL, 'manual', 'approved', 'admin'),
(6, 'gopi', '123456', '2026-05-22 03:57:12', NULL, NULL, NULL, 'manual', 'rejected', 'admin'),
(8, 'manojmelur2003', '', '2026-05-25 06:34:34', 'manojmelur2003@gmail.com', '103727578828839702706', 'https://lh3.googleusercontent.com/a/ACg8ocJnirUF0Z4_ywFWCqdYbeQY6jrwXKDpMCLUVuAKE20jGYCtxOZA=s96-c', 'google', 'approved', 'admin'),
(9, 'manojramesh2808', '', '2026-05-25 06:39:35', 'manojramesh2808@gmail.com', '115367275242331831276', 'https://lh3.googleusercontent.com/a/ACg8ocL6Immw9vrXCEBh5hOOsWGU8rNBQVNz-zUx_MNlaG6KKneRCg=s96-c', 'google', 'approved', 'admin'),
(10, 'manojramesh', '987654', '2026-05-26 04:33:41', NULL, NULL, NULL, 'manual', 'approved', 'user'),
(11, 'manojmanoj', '987654', '2026-05-26 04:37:26', NULL, NULL, NULL, 'manual', 'approved', 'admin'),
(12, 'manojmelur', '987654', '2026-05-26 04:52:53', NULL, NULL, NULL, 'manual', 'approved', 'user'),
(13, 'mano', '987654', '2026-05-26 04:56:41', NULL, NULL, NULL, 'manual', 'approved', 'admin'),
(14, 'manohar', '123456', '2026-05-26 05:05:15', NULL, NULL, NULL, 'manual', 'approved', 'user'),
(15, 'romeomanoj001', '', '2026-05-26 05:54:59', 'romeomanoj001@gmail.com', '106375030210900454960', 'https://lh3.googleusercontent.com/a/ACg8ocKf5g-mroRCuKm12zSeZfMJEEAm5wllrrKKp5O8NUUCBwnFXA=s96-c', 'google', 'approved', 'user'),
(16, 'maanmaan', '543210', '2026-05-26 06:06:03', NULL, NULL, NULL, 'manual', 'approved', 'admin');

-- --------------------------------------------------------

--
-- Table structure for table `admission_applications`
--

CREATE TABLE `admission_applications` (
  `id` int(11) NOT NULL,
  `school` enum('samacheer','cbse') NOT NULL,
  `student_name` varchar(150) NOT NULL,
  `date_of_birth` date NOT NULL,
  `class_applied` varchar(50) NOT NULL,
  `gender` varchar(20) NOT NULL,
  `parent_name` varchar(150) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `email` varchar(150) NOT NULL,
  `address` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admission_applications`
--

INSERT INTO `admission_applications` (`id`, `school`, `student_name`, `date_of_birth`, `class_applied`, `gender`, `parent_name`, `phone`, `email`, `address`, `created_at`) VALUES
(1, 'samacheer', 'Manoj', '2003-08-28', '6', 'Male', 'Ramesh', '9876543210', 'manoj@gmail.com', 'chennai', '2026-05-25 09:49:24'),
(2, 'cbse', 'Mannu', '2003-10-18', '10', 'Male', 'ramesh', '9876543210', 'manojmanoj@gmail.com', 'Madurai', '2026-05-27 04:45:46');

-- --------------------------------------------------------

--
-- Table structure for table `gallery_images`
--

CREATE TABLE `gallery_images` (
  `id` int(11) NOT NULL,
  `school` enum('samacheer','cbse') NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `caption` varchar(255) NOT NULL DEFAULT '',
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `uploaded_by` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `gallery_images`
--

INSERT INTO `gallery_images` (`id`, `school`, `image_path`, `caption`, `sort_order`, `is_active`, `created_at`, `uploaded_by`) VALUES
(1, 'samacheer', 'gallery/1.jpg', 'School Life 1', 1, 1, '2026-05-19 08:54:11', NULL),
(2, 'samacheer', 'gallery/2.jpg', 'School Life 2', 2, 1, '2026-05-19 08:54:11', NULL),
(3, 'samacheer', 'gallery/3.jpg', 'School Life 3', 3, 1, '2026-05-19 08:54:11', NULL),
(4, 'samacheer', 'gallery/4.jpg', 'School Life 4', 4, 1, '2026-05-19 08:54:11', NULL),
(5, 'samacheer', 'gallery/5.jpg', 'School Life 5', 5, 1, '2026-05-19 08:54:11', NULL),
(6, 'samacheer', 'gallery/6.jpg', 'School Life 6', 6, 1, '2026-05-19 08:54:11', NULL),
(7, 'samacheer', 'gallery/7.jpg', 'School Life 7', 7, 1, '2026-05-19 08:54:11', NULL),
(8, 'samacheer', 'gallery/8.jpg', 'School Life 8', 8, 1, '2026-05-19 08:54:11', NULL),
(9, 'samacheer', 'gallery/9.jpg', 'School Life 9', 9, 1, '2026-05-19 08:54:11', NULL),
(10, 'samacheer', 'gallery/10.jpg', 'School Life 10', 10, 1, '2026-05-19 08:54:11', NULL),
(11, 'samacheer', 'gallery/11.jpg', 'School Life 11', 11, 1, '2026-05-19 08:54:11', NULL),
(12, 'samacheer', 'gallery/12(1).jpg', 'School Life 12', 12, 1, '2026-05-19 08:54:11', NULL),
(13, 'cbse', 'gallery/1.jpg', 'Campus Life 1', 1, 1, '2026-05-19 08:54:32', NULL),
(14, 'cbse', 'gallery/2.jpg', 'Campus Life 2', 2, 1, '2026-05-19 08:54:32', NULL),
(15, 'cbse', 'gallery/3.jpg', 'Campus Life 3', 3, 1, '2026-05-19 08:54:32', NULL),
(16, 'cbse', 'gallery/4.jpg', 'Campus Life 4', 4, 1, '2026-05-19 08:54:32', NULL),
(17, 'cbse', 'gallery/5.jpg', 'Campus Life 5', 5, 1, '2026-05-19 08:54:32', NULL),
(18, 'cbse', 'gallery/6.jpg', 'Campus Life 6', 6, 1, '2026-05-19 08:54:32', NULL),
(19, 'cbse', 'gallery/7.jpg', 'Campus Life 7', 7, 1, '2026-05-19 08:54:32', NULL),
(20, 'cbse', 'gallery/8.jpg', 'Campus Life 8', 8, 1, '2026-05-19 08:54:32', NULL),
(21, 'cbse', 'gallery/9.jpg', 'Campus Life 9', 9, 1, '2026-05-19 08:54:32', NULL),
(22, 'cbse', 'gallery/10.jpg', 'Campus Life 10', 10, 1, '2026-05-19 08:54:32', NULL),
(23, 'cbse', 'gallery/11.jpg', 'Campus Life 11', 11, 1, '2026-05-19 08:54:32', NULL),
(24, 'cbse', 'gallery/12(1).jpg', 'Campus Life 12', 12, 1, '2026-05-19 08:54:32', NULL),
(26, 'cbse', 'gallery/gal_1779699892_774.png', 'example', 13, 0, '2026-05-25 09:04:52', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `hero_sections`
--

CREATE TABLE `hero_sections` (
  `id` int(11) NOT NULL,
  `school` enum('samacheer','cbse') NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hero_sections`
--

INSERT INTO `hero_sections` (`id`, `school`, `title`, `description`, `updated_at`) VALUES
(1, 'samacheer', 'Sri Sathya Sai Vidyalaya – Samacheer', 'Education with Human Values — moulding young minds since 24th April, 1987.', '2026-05-19 08:50:10'),
(2, 'cbse', 'Sri Sathya Sai Vidya Vihar', 'Education with Human Values. A modern CBSE institution dedicated to academic excellence, discipline, spirituality, and holistic development.', '2026-05-19 08:50:10');

-- --------------------------------------------------------

--
-- Table structure for table `hero_slides`
--

CREATE TABLE `hero_slides` (
  `id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `tag` varchar(80) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `btn_text` varchar(100) NOT NULL DEFAULT 'Learn More →',
  `btn_link` varchar(255) NOT NULL DEFAULT '#about',
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `uploaded_by` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hero_slides`
--

INSERT INTO `hero_slides` (`id`, `image_path`, `tag`, `title`, `description`, `btn_text`, `btn_link`, `sort_order`, `is_active`, `created_at`, `uploaded_by`) VALUES
(1, 'images/3.jpg', 'Welcome', 'A Sai School', '&ldquo;The students of today are the citizens of tomorrow who will shape the destiny of a nation. <strong>MY SCHOOLS</strong> have been established to achieve this purpose.&rdquo;<br><small style=\"color:rgba(255,255,255,0.65);\">— Bhagawan Sri Sathya Sai Baba</small>', 'Discover Our Schools ↓', '#about', 1, 1, '2026-05-19 08:53:07', NULL),
(2, 'images/2.jpg', 'Philosophy', 'Educare – Veda of the 21st Century', 'Education refers to collection of worldly facts. Educare is to bring out the latent Divinity in Man — Education is for a living; Educare is for life.', 'Learn More →', '#about', 2, 1, '2026-05-19 08:53:07', NULL),
(3, 'images/5.jpg', 'Values', 'Unity of Faiths', 'Fostering the belief &ldquo;God is one, Love is God&rdquo; inculcates Unity of Faiths among children — rooted in Sathya, Dharma, Santhi and Prema.', 'Our Curriculum →', '#curriculum', 3, 1, '2026-05-19 08:53:07', NULL),
(4, 'images/6.jpg', 'Technology', 'Sri Sathya Sai Vidya Vahini', 'Insightful, Inspiring, Enjoyable and Participative value-based Digital Classrooms — powered by Tata Consultancy Services.', 'Explore →', '#curriculum', 4, 1, '2026-05-19 08:53:07', NULL),
(5, 'images/9.jpg', 'Innovation', 'Synergistic Notebooks', 'Enabling students to be creative independent thinkers and writers — used from Grade 1 onwards to spark imagination and ownership of learning.', 'Student Stories →', '#voices', 5, 1, '2026-05-19 08:53:07', NULL),
(6, 'images/10.jpg', 'Smart School', 'Sailens – A Dedicated School App', 'The 1st school to introduce RF-enabled ID cards and a dedicated app in Thiruvottiyur. Attendance, GPS tracking, galleries and more — all in one place.', 'Get in Touch →', '#contact', 6, 1, '2026-05-19 08:53:07', NULL),
(7, 'images/11.jpg', 'Spirit', 'We are the Future…', 'Sathya Sai Schools, Thiruvottiyur — <em>Globally Focused, Distinctly Indian.</em>', 'Register 2025–26 →', 'samacheer.php#admissions', 7, 1, '2026-05-19 08:53:07', NULL),
(8, 'images/12.jpg', 'Admissions Open', 'School New Admissions 2025–2026', 'Your child\'s next step towards a bright future. Registration now open for the new academic year.', 'Register Now →', 'samacheer.php#admissions', 8, 1, '2026-05-19 08:53:07', NULL),
(9, 'images/slide_1779188253_419.jpg', 'values', 'dkhoshdocd', 'nsxhashiahcoiahochpwopfv', 'Learn More →', '#about', 0, 0, '2026-05-19 10:57:33', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `page_contents`
--

CREATE TABLE `page_contents` (
  `id` int(11) NOT NULL,
  `page_name` varchar(50) NOT NULL,
  `section_title` varchar(100) NOT NULL,
  `content_text` text NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `page_contents`
--

INSERT INTO `page_contents` (`id`, `page_name`, `section_title`, `content_text`, `updated_at`, `is_active`) VALUES
(3, 'home', 'about_tag', 'Our Schools', '2026-05-21 05:48:33', 1),
(4, 'home', 'about_title', 'Two Campuses, One Vision', '2026-05-21 05:48:33', 1),
(5, 'home', 'about_subtitle', 'Both schools are inspired, initiated and lovingly nurtured by Bhagawan Sri Sathya Sai Baba — dedicated to holistic, value-based education since 1987.', '2026-05-21 05:48:33', 1),
(6, 'home', 'samacheer_badge', 'Est. 1987', '2026-05-21 05:48:33', 1),
(7, 'home', 'samacheer_name', 'Sri Sathya Sai Vidyalaya', '2026-05-21 05:48:33', 1),
(8, 'home', 'samacheer_board', 'Samacheer School · Tamil Nadu State Board', '2026-05-21 05:48:33', 1),
(9, 'home', 'samacheer_desc', 'Embarked on its mission of moulding young minds on 24th April 1987, the Vidyalaya has been producing State Ranks and 100% pass percentage in board exams continuously, pursuing excellence under His guidance and loving care.', '2026-05-21 05:48:33', 1),
(10, 'home', 'cbse_badge', 'Est. 2016', '2026-05-21 05:48:33', 1),
(11, 'home', 'cbse_name', 'Sri Sathya Sai Vidya Vihar', '2026-05-21 05:48:33', 1),
(12, 'home', 'cbse_board', 'School · PreP to Grade 5', '2026-05-21 05:48:33', 1),
(13, 'home', 'cbse_desc', 'Launched in June 2016 in a sprawling green campus of more than 1 acre, with state-of-the-art infrastructure, spacious classrooms, modern labs, and high-end sports facilities — an elite value-based school in Thiruvottiyur.', '2026-05-21 05:48:33', 1),
(14, 'home', 'curriculum_tag', 'What We Offer', '2026-05-21 05:48:33', 1),
(15, 'home', 'curriculum_title', 'The Curriculum &amp; Beyond', '2026-05-21 05:48:33', 1),
(16, 'home', 'curriculum_desc', 'Sathya Sai provides a secure, challenging yet supportive learning environment in which students gain confidence both in curriculum and beyond — through a host of enriching activities.', '2026-05-21 05:48:33', 1),
(17, 'home', 'banner_title', 'Admissions Open – Academic Year 2025–2026', '2026-05-21 05:48:33', 1),
(18, 'home', 'banner_desc', 'Your child\'s next step towards a bright future starts here.', '2026-05-21 05:48:33', 1),
(19, 'home', 'banner_btn', 'Register Now →', '2026-05-21 05:48:33', 1),
(20, 'home', 'testi_tag', 'Heart to Heart', '2026-05-21 05:48:33', 1),
(21, 'home', 'testi_title', 'Why We Love Sathya Sai?', '2026-05-21 05:48:33', 1),
(22, 'home', 'testi_desc', 'Our open door policy is the foundation upon which we build excellent relationships. The person who benefits most from this positive collaboration is none other than the student.', '2026-05-21 05:48:33', 1),
(23, 'home', 'contact_tag', 'Get In Touch', '2026-05-21 05:48:33', 1),
(24, 'home', 'contact_title', 'Together We\'re Stronger', '2026-05-21 05:48:33', 1),
(25, 'home', 'contact_desc', 'Share our values, set your compass and start your journey with us. Together let\'s build exciting opportunities for our children.', '2026-05-21 05:48:33', 1),
(26, 'home', 'contact1_title', 'Sri Sathya Sai Vidyalaya', '2026-05-21 05:48:33', 1),
(27, 'home', 'contact1_details', '#3, Nadabai Garden<br>Thiruvottiyur, Chennai – 600 019<br><a href=\"tel:04425731075\">044 2573 1075 / 4554 3184</a>', '2026-05-21 05:48:33', 1),
(28, 'home', 'contact2_title', 'Sri Sathya Sai Vidya Vihar', '2026-05-21 05:48:33', 1),
(29, 'home', 'contact2_details', '#101, KCP Road<br>Thiruvottiyur, Chennai – 600 019<br><a href=\"tel:+919444080024\">+91 94440 80024</a>', '2026-05-21 05:48:33', 1),
(30, 'home', 'contact3_title', 'Email &amp; Social', '2026-05-21 05:48:33', 1),
(31, 'home', 'contact3_details', '<a href=\"mailto:contact@sathyasaischool.in\">contact@sathyasaischool.in</a><br><a href=\"mailto:contact@sathyasaischools.org\">contact@sathyasaischools.org</a>', '2026-05-21 05:48:33', 1),
(32, 'samacheer', 'about_tag', 'Samacheer School', '2026-05-21 05:48:33', 1),
(33, 'samacheer', 'about_title', 'Sri Sathya Sai Vidyalaya<br>Samacheer Syllabus', '2026-05-21 05:48:33', 1),
(34, 'samacheer', 'about_para1', 'The Sri Sathya Sai Vidyalaya is inspired, initiated and is lovingly nurtured by Bhagawan Sri Sathya Sai Baba. He is its Life and Soul, Motivation and Goal. Under His guidance and loving care, the school embarked on its mission of molding young minds on 24th April, 1987 and has been in pursuit of excellence since then.', '2026-05-21 05:48:33', 1),
(35, 'samacheer', 'about_para2', 'The school has been producing State Ranks and 100% pass percentage in board exams and as well as in levels all through. <strong>\"Character Development with Academic Excellence\"</strong> has been the governing principle of all endeavors in the school.', '2026-05-21 05:48:33', 1),
(36, 'samacheer', 'about_para3', 'The school at every step, big or small, is guided by the comprehensive educational philosophy of Bhagawan Baba who emphatically advocates <em>\"Education should be for life and not merely for a living.\"</em>', '2026-05-21 05:48:33', 1),
(37, 'samacheer', 'about_quote', '\"In every field, students must strive to become ideal leaders and guides. The students of today are the citizens of tomorrow who will shape the destiny of a nation.\"', '2026-05-21 05:48:33', 1),
(38, 'samacheer', 'about_quote_cite', '— Bhagawan Sri Sathya Sai Baba', '2026-05-21 05:48:33', 1),
(39, 'samacheer', 'values_tag', 'Our Foundation', '2026-05-21 05:48:33', 1),
(40, 'samacheer', 'values_title', 'Four Pillars of Sai Educare', '2026-05-21 05:48:33', 1),
(41, 'samacheer', 'value1_name', 'Sathya', '2026-05-21 05:48:33', 1),
(42, 'samacheer', 'value1_desc', 'Truth — the foundation of all learning and character. Students are encouraged to embrace honesty in thought, word and deed.', '2026-05-21 05:48:33', 1),
(43, 'samacheer', 'value2_name', 'Dharma', '2026-05-21 05:48:33', 1),
(44, 'samacheer', 'value2_desc', 'Righteous conduct — moral and ethical values integrated into every aspect of school life and the curriculum.', '2026-05-21 05:48:33', 1),
(45, 'samacheer', 'value3_name', 'Santhi', '2026-05-21 05:48:33', 1),
(46, 'samacheer', 'value3_desc', 'Peace — inner tranquility cultivated through meditation, yoga, and a calm, nurturing school environment.', '2026-05-21 05:48:33', 1),
(47, 'samacheer', 'value4_name', 'Prema', '2026-05-21 05:48:33', 1),
(48, 'samacheer', 'value4_desc', 'Love — unconditional love for all beings, fostering compassion, unity of faiths, and care for the community.', '2026-05-21 05:48:33', 1),
(49, 'samacheer', 'academics_tag', 'Academics', '2026-05-21 05:48:33', 1),
(50, 'samacheer', 'academics_title', 'Holistic Academic Programme', '2026-05-21 05:48:33', 1),
(51, 'samacheer', 'academics_para1', 'Sathya Sai School\'s Programme addresses all aspects of transformation at a school for each level. What content is taught, how it is taught, activities and materials needed to make hands-on interactive classrooms, teacher empowerment, school ethos and environment are all integrated as part of Sathya Sai\'s holistic approach.', '2026-05-21 05:48:33', 1),
(52, 'samacheer', 'approach_tag', 'Our Approach', '2026-05-21 05:48:33', 1),
(53, 'samacheer', 'approach_title', 'How We Teach', '2026-05-21 05:48:33', 1),
(54, 'samacheer', 'approach1_name', 'Multi-Age Grouping', '2026-05-21 05:48:33', 1),
(55, 'samacheer', 'approach1_desc', 'Flexible, vertical grouping strategies that allow students to learn at their own pace and support peers in collaborative environments.', '2026-05-21 05:48:33', 1),
(56, 'samacheer', 'approach2_name', 'Diagnostic Teaching', '2026-05-21 05:48:33', 1),
(57, 'samacheer', 'approach2_desc', 'Ongoing diagnostic tools and assessments inform teaching practices, ensuring every child receives targeted support and challenge.', '2026-05-21 05:48:33', 1),
(58, 'samacheer', 'approach3_name', 'Interactive Classrooms', '2026-05-21 05:48:33', 1),
(59, 'samacheer', 'approach3_desc', 'Sri Sathya Sai Vidya Vahini digital classrooms make learning insightful, inspiring, enjoyable, and participative.', '2026-05-21 05:48:33', 1),
(60, 'samacheer', 'approach4_name', 'Synergistic Notebooks', '2026-05-21 05:48:33', 1),
(61, 'samacheer', 'approach4_desc', 'Students express ideas, process information, and take ownership of their learning through creative Synergistic Notebooks from Grade 1.', '2026-05-21 05:48:33', 1),
(62, 'samacheer', 'approach5_name', 'Value Integration', '2026-05-21 05:48:33', 1),
(63, 'samacheer', 'approach5_desc', 'Every subject is taught with values embedded — truth, discipline, compassion and service are woven into daily learning activities.', '2026-05-21 05:48:33', 1),
(64, 'samacheer', 'approach6_name', 'Language Mastery', '2026-05-21 05:48:33', 1),
(65, 'samacheer', 'approach6_desc', 'Special emphasis on languages to enable students to master communication skills, enabling national and global competence.', '2026-05-21 05:48:33', 1),
(66, 'samacheer', 'gallery_tag', 'Gallery', '2026-05-21 05:48:33', 1),
(67, 'samacheer', 'gallery_title', 'Life at Sathya Sai', '2026-05-21 05:48:33', 1),
(68, 'samacheer', 'gallery_desc', 'Glimpses of our vibrant school community — classrooms, events, performances, and celebrations.', '2026-05-21 05:48:33', 1),
(69, 'samacheer', 'career_tag', 'Career With Us', '2026-05-21 05:48:33', 1),
(70, 'samacheer', 'career_title', 'Join the Sai Teaching Family', '2026-05-21 05:48:33', 1),
(71, 'cbse', 'about_tag', 'CBSE School', '2026-05-21 05:48:33', 1),
(72, 'cbse', 'about_title', 'Sri Sathya Sai Vidya Vihar <br>CBSE Syllabus', '2026-05-21 05:48:33', 1),
(73, 'cbse', 'about_para1', 'By the Divine Grace and Blessings of Bhagawan Sri Sathya Sai Baba, the Sri Sathya Sai Vidya Vihar, an elite value-based CBSE school, was launched in Thiruvottiyur in June 2016 for classes from Pre KG to Grade 10.', '2026-05-21 05:48:33', 1),
(74, 'cbse', 'about_para2', 'In a sprawling green campus with modern infrastructure, spacious classrooms, laboratories, sports facilities and activity centers, the school nurtures students to become physically strong, mentally balanced, morally upright, academically excellent and spiritually enlightened.', '2026-05-21 05:48:33', 1),
(75, 'cbse', 'about_quote', '\"Education is for life, not merely for a living.\"', '2026-05-21 05:48:33', 1),
(76, 'cbse', 'value1_name', 'Sathya', '2026-05-21 05:48:33', 1),
(77, 'cbse', 'value1_desc', 'Truth — the foundation of all learning and character. Students are encouraged to embrace honesty in thought, word and deed.', '2026-05-21 05:48:33', 1),
(78, 'cbse', 'value2_name', 'Dharma', '2026-05-21 05:48:33', 1),
(79, 'cbse', 'value2_desc', 'Righteous conduct — moral and ethical values integrated into every aspect of school life and the curriculum.', '2026-05-21 05:48:33', 1),
(80, 'cbse', 'value3_name', 'Santhi', '2026-05-21 05:48:33', 1),
(81, 'cbse', 'value3_desc', 'Peace — inner tranquility cultivated through meditation, yoga, and a calm, nurturing school environment.', '2026-05-21 05:48:33', 1),
(82, 'cbse', 'value4_name', 'Prema', '2026-05-21 05:48:33', 1),
(83, 'cbse', 'value4_desc', 'Love — unconditional love for all beings, fostering compassion, unity of faiths, and care for the community.', '2026-05-21 05:48:33', 1),
(84, 'cbse', 'academics_tag', 'Academics', '2026-05-21 05:48:33', 1),
(85, 'cbse', 'academics_title', 'Holistic Academic Programme', '2026-05-21 05:48:33', 1),
(86, 'cbse', 'academics_para1', 'Sri Sathya Sai Vidya Vihar offers a comprehensive CBSE curriculum designed to foster a love for learning. Our academic program is rigorous yet flexible, accommodating the diverse needs and learning styles of our students.', '2026-05-21 05:48:33', 1),
(87, 'cbse', 'academics_para2', 'We focus on building strong foundational skills while encouraging critical thinking, creativity, and problem-solving. Our dedicated faculty employs innovative teaching methodologies to make learning an engaging and meaningful experience.', '2026-05-21 05:48:33', 1),
(88, 'cbse', 'approach_title', 'Our Approach', '2026-05-21 05:48:33', 1),
(89, 'cbse', 'approach1_name', 'Integrated Learning', '2026-05-21 05:48:33', 1),
(90, 'cbse', 'approach1_desc', 'We blend subjects and values to create a holistic learning environment, ensuring students understand the interconnectedness of knowledge.', '2026-05-21 05:48:33', 1),
(91, 'cbse', 'approach2_name', 'Experiential Education', '2026-05-21 05:48:33', 1),
(92, 'cbse', 'approach2_desc', 'Hands-on activities, projects, and field trips bring concepts to life, helping students apply theoretical knowledge to real-world situations.', '2026-05-21 05:48:33', 1),
(93, 'cbse', 'approach3_name', 'Continuous Assessment', '2026-05-21 05:48:33', 1),
(94, 'cbse', 'approach3_desc', 'Regular evaluations and constructive feedback guide students\' progress, focusing on their overall growth rather than just grades.', '2026-05-21 05:48:33', 1),
(95, 'cbse', 'approach4_name', 'Technology in Classroom', '2026-05-21 05:48:33', 1),
(96, 'cbse', 'approach4_desc', 'Smart classrooms and digital resources are integrated into daily lessons to enhance understanding and prepare students for the future.', '2026-05-21 05:48:33', 1);

-- --------------------------------------------------------

--
-- Table structure for table `school_videos`
--

CREATE TABLE `school_videos` (
  `id` int(11) NOT NULL,
  `school` enum('samacheer','cbse') NOT NULL,
  `title` varchar(255) NOT NULL,
  `youtube_url` varchar(500) NOT NULL,
  `description` text DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `school_videos`
--

INSERT INTO `school_videos` (`id`, `school`, `title`, `youtube_url`, `description`, `sort_order`, `is_active`, `created_at`) VALUES
(1, 'samacheer', 'Annual Day 2024', 'https://youtu.be/Vax6CR7RgLU?si=SbHap4_c6cc9zV87', 'Annual day celebration highlights', 1, 1, '2026-05-21 06:39:38'),
(2, 'samacheer', 'Sports Day Highlights', 'https://youtu.be/CsU757PQSPk?si=dwWYmb5VG3bZNsNp', 'Sports meet 2024', 2, 1, '2026-05-21 06:39:38'),
(3, 'cbse', 'Science Exhibition', 'https://youtu.be/Vax6CR7RgLU?si=SbHap4_c6cc9zV87', 'CBSE Science fair 2024', 1, 1, '2026-05-21 06:39:38'),
(4, 'cbse', 'Cultural Programme', 'https://youtu.be/CsU757PQSPk?si=dwWYmb5VG3bZNsNp', 'Cultural event highlights', 2, 1, '2026-05-21 06:39:38');

-- --------------------------------------------------------

--
-- Table structure for table `teacher_applications`
--

CREATE TABLE `teacher_applications` (
  `id` int(11) NOT NULL,
  `school` enum('samacheer','cbse') NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `date_of_birth` date NOT NULL,
  `qualification` varchar(150) NOT NULL,
  `years_experience` int(11) NOT NULL DEFAULT 0,
  `subjects` varchar(200) NOT NULL,
  `gender` varchar(20) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `email` varchar(150) NOT NULL,
  `resume_path` varchar(300) DEFAULT NULL,
  `address` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `teacher_applications`
--

INSERT INTO `teacher_applications` (`id`, `school`, `full_name`, `date_of_birth`, `qualification`, `years_experience`, `subjects`, `gender`, `phone`, `email`, `resume_path`, `address`, `created_at`) VALUES
(1, 'samacheer', 'gopi', '1998-01-01', 'B.ed', 2, 'English', 'Male', '1234567890', 'gopi@gmail.com', 'uploads/resumes/resume_1779702871_6036b9d0.pdf', 'chennai', '2026-05-25 09:54:31'),
(2, 'cbse', 'vasanth Kumar', '1997-01-10', 'M.A, B.Ed', 3, 'Mathametics', 'Male', '1236547890', 'vasanthvasanth@gmail.com', 'uploads/resumes/resume_1779857263_96c0002c.pdf', 'Coimabatore', '2026-05-27 04:47:43');

-- --------------------------------------------------------

--
-- Table structure for table `testimonials`
--

CREATE TABLE `testimonials` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `role` varchar(150) DEFAULT NULL,
  `content` text NOT NULL,
  `avatar_path` varchar(300) DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `testimonials`
--

INSERT INTO `testimonials` (`id`, `name`, `role`, `content`, `avatar_path`, `sort_order`, `is_active`, `created_at`) VALUES
(1, 'manoj', 'student v11', 'best school', 'images/avatar_1779708319_656.jpg', 1, 0, '2026-05-25 11:25:19'),
(2, 'Shiyam M', 'Student, VII A2', 'When the word \'extra\' precedes \'ordinary\', it gives a whole new meaning. So does the word \'Sai\' to a \'Student\'. Am blessed to be a SAI STUDENT.', NULL, 1, 1, '2026-05-25 11:30:06'),
(3, 'Yuvashree P', 'Student, X A3', 'Sathya Sai is a great place to learn. There are many opportunities for once-in-a-lifetime experiences.', NULL, 2, 1, '2026-05-25 11:30:06'),
(4, 'Gopala Krishnan MS', 'Student, XII A', 'Lessons can very often be entertaining as well as educational because of the approach that teachers take.', NULL, 3, 1, '2026-05-25 11:30:06'),
(5, 'Elizabeth D', 'Student, X A2', 'Sathya Sai opens a wide variety of doors; I have discovered strengths I never knew I had.', NULL, 4, 1, '2026-05-25 11:30:06'),
(6, 'Aathira L J', 'Student, VIII A2', 'On Sunday nights, I never think \"Oh no, it\'s school tomorrow!\" I love Sathya Sai…', NULL, 5, 1, '2026-05-25 11:30:06'),
(7, 'Ms. Pemila Ramesh', 'Teacher, Class III', 'Sathya Sai is a community. Every day presents a new challenge and students and staff work together — it\'s what makes being a teacher here so rewarding!', NULL, 6, 1, '2026-05-25 11:30:06'),
(8, 'Ms. Jayasri S', 'Teacher, Class XI', 'It\'s the people inside Sathya Sai that make it a special place. Young people who are fun, brilliant, hardworking, and my amazing colleagues who inspire me every day.', NULL, 7, 1, '2026-05-25 11:30:06'),
(9, 'Ms. Fathima Shakira Ali', 'Teacher, Class IV', 'This is the school that helped shape my future when I studied here. Now I get to be part of the teaching team shaping the next generation.', NULL, 8, 1, '2026-05-25 11:30:06'),
(10, 'Ms. Usharani Bherusingh', 'Parent, VIII A2', 'The school achieves the right balance between working hard and having fun. My daughter thoroughly enjoys school — Sathya Sai is a great place to be, for every child.', NULL, 9, 1, '2026-05-25 11:30:06'),
(11, 'Ms. Nandini Krishnamoorthy', 'Parent', 'The behavior of students is exceptional both in and out of classrooms — welcoming, courteous and respectful. This is an outstanding school.', NULL, 10, 1, '2026-05-25 11:30:06'),
(12, 'Mr. Ramesh', 'Parent', 'What makes the school special is that as soon as you walk in, you get a burst of positive energy. The school works hand-in-hand with parents.', NULL, 11, 1, '2026-05-25 11:30:06'),
(13, 'Sameera Fathima M', 'Student, XII A', 'Our school\'s high academic standards and family atmosphere nurture a love for learning that brings out the best in all students.', NULL, 12, 1, '2026-05-25 11:30:06');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_users`
--
ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `admission_applications`
--
ALTER TABLE `admission_applications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `gallery_images`
--
ALTER TABLE `gallery_images`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `hero_sections`
--
ALTER TABLE `hero_sections`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `school` (`school`);

--
-- Indexes for table `hero_slides`
--
ALTER TABLE `hero_slides`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `page_contents`
--
ALTER TABLE `page_contents`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `school_videos`
--
ALTER TABLE `school_videos`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `teacher_applications`
--
ALTER TABLE `teacher_applications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `testimonials`
--
ALTER TABLE `testimonials`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `admission_applications`
--
ALTER TABLE `admission_applications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `gallery_images`
--
ALTER TABLE `gallery_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `hero_sections`
--
ALTER TABLE `hero_sections`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `hero_slides`
--
ALTER TABLE `hero_slides`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `page_contents`
--
ALTER TABLE `page_contents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=100;

--
-- AUTO_INCREMENT for table `school_videos`
--
ALTER TABLE `school_videos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `teacher_applications`
--
ALTER TABLE `teacher_applications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `testimonials`
--
ALTER TABLE `testimonials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
