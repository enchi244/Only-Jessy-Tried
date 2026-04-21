-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 21, 2026 at 04:19 PM
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
-- Database: `rms`
--

-- --------------------------------------------------------

--
-- Table structure for table `product_category_table`
--

CREATE TABLE `product_category_table` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(250) NOT NULL,
  `category_status` enum('Enable','Disable') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_ext`
--

CREATE TABLE `tbl_ext` (
  `id` int(11) NOT NULL,
  `researcherID` varchar(255) NOT NULL,
  `extension_project_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `proj_lead` varchar(255) NOT NULL,
  `assist_coordinators` varchar(255) NOT NULL,
  `period_implement` varchar(255) NOT NULL,
  `budget` varchar(255) NOT NULL,
  `fund_source` varchar(255) NOT NULL,
  `target_beneficiaries` varchar(255) NOT NULL,
  `partners` varchar(255) NOT NULL,
  `stat` varchar(255) NOT NULL,
  `attachments` varchar(255) NOT NULL,
  `a_link` varchar(255) NOT NULL,
  `status` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_extension_activity_links`
--

CREATE TABLE `tbl_extension_activity_links` (
  `id` int(11) NOT NULL,
  `extension_activity_id` int(11) NOT NULL,
  `extension_project_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_extension_files`
--

CREATE TABLE `tbl_extension_files` (
  `id` int(11) NOT NULL,
  `extension_id` int(11) NOT NULL,
  `file_category` enum('Terminal Report','MOA','SO','Financial Report','Other') NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `uploaded_on` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_extension_project_conducted`
--

CREATE TABLE `tbl_extension_project_conducted` (
  `id` int(11) NOT NULL,
  `researcherID` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `start_date` varchar(255) NOT NULL,
  `completed_date` varchar(255) NOT NULL,
  `funding_source` varchar(255) NOT NULL,
  `approved_budget` varchar(255) NOT NULL,
  `target_beneficiaries_communities` varchar(255) NOT NULL,
  `partners` varchar(255) NOT NULL,
  `status_exct` varchar(255) NOT NULL,
  `terminal_report` varchar(255) NOT NULL,
  `terminal_report_file` varchar(255) DEFAULT NULL,
  `moa_file` varchar(255) DEFAULT NULL,
  `has_files` enum('With','None') NOT NULL DEFAULT 'None',
  `status` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_extension_research_links`
--

CREATE TABLE `tbl_extension_research_links` (
  `id` int(11) NOT NULL,
  `extension_id` int(11) NOT NULL,
  `research_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_ip_collaborators`
--

CREATE TABLE `tbl_ip_collaborators` (
  `id` int(11) NOT NULL,
  `ip_id` int(11) NOT NULL,
  `researcher_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_ip_files`
--

CREATE TABLE `tbl_ip_files` (
  `id` int(11) NOT NULL,
  `ip_id` int(11) NOT NULL,
  `file_category` enum('Certificate','Application Document','MOA','Other') NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `uploaded_on` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_itelectualprop`
--

CREATE TABLE `tbl_itelectualprop` (
  `id` int(11) NOT NULL,
  `researcherID` varchar(255) NOT NULL,
  `lead_researcher_id` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `coauth` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `date_applied` varchar(255) NOT NULL,
  `date_granted` varchar(255) NOT NULL,
  `has_files` enum('With','None') NOT NULL DEFAULT 'None',
  `moa_file` varchar(255) DEFAULT NULL,
  `a_link` text DEFAULT NULL,
  `status` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_majordiscipline`
--

CREATE TABLE `tbl_majordiscipline` (
  `majorID` bigint(20) NOT NULL,
  `code` tinyint(4) NOT NULL,
  `major` varchar(100) NOT NULL,
  `disc_status` enum('Enable','Disable') NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_paperpresentation`
--

CREATE TABLE `tbl_paperpresentation` (
  `id` int(11) NOT NULL,
  `researcherID` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `conference_title` varchar(255) NOT NULL,
  `conference_venue` varchar(255) NOT NULL,
  `conference_organizer` varchar(255) NOT NULL,
  `date_paper` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `discipline` varchar(255) NOT NULL,
  `moa_file` varchar(255) DEFAULT NULL,
  `a_link` text DEFAULT NULL,
  `has_files` enum('With','None') NOT NULL DEFAULT 'None',
  `status` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_paper_collaborators`
--

CREATE TABLE `tbl_paper_collaborators` (
  `id` int(11) NOT NULL,
  `paper_id` int(11) DEFAULT NULL,
  `researcher_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_paper_files`
--

CREATE TABLE `tbl_paper_files` (
  `id` int(11) NOT NULL,
  `paper_id` int(11) NOT NULL,
  `file_category` enum('Certificate','Program','Presentation Document','MOA','Other') NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `uploaded_on` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_publication`
--

CREATE TABLE `tbl_publication` (
  `id` int(11) NOT NULL,
  `researcherID` varchar(255) NOT NULL,
  `lead_author_id` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `start` varchar(255) NOT NULL,
  `end` varchar(255) NOT NULL,
  `journal` varchar(255) NOT NULL,
  `vol_num_issue_num` varchar(255) NOT NULL,
  `issn_isbn` varchar(255) NOT NULL,
  `indexing` varchar(255) NOT NULL,
  `publication_date` varchar(255) NOT NULL,
  `has_files` enum('With','None') NOT NULL DEFAULT 'None',
  `moa_file` varchar(255) DEFAULT NULL,
  `status` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_publication_collaborators`
--

CREATE TABLE `tbl_publication_collaborators` (
  `id` int(11) NOT NULL,
  `publication_id` int(11) DEFAULT NULL,
  `researcher_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_publication_files`
--

CREATE TABLE `tbl_publication_files` (
  `id` int(11) NOT NULL,
  `publication_id` int(11) NOT NULL,
  `file_category` enum('Journal Document','MOA','Other') NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `uploaded_on` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_rde_agenda`
--

CREATE TABLE `tbl_rde_agenda` (
  `id` int(11) NOT NULL,
  `agenda` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_researchconducted`
--

CREATE TABLE `tbl_researchconducted` (
  `id` int(11) NOT NULL,
  `researcherID` int(11) DEFAULT NULL,
  `lead_researcher_id` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `research_agenda_cluster` varchar(255) NOT NULL,
  `sdgs` varchar(255) NOT NULL,
  `started_date` varchar(255) NOT NULL,
  `completed_date` varchar(255) NOT NULL,
  `funding_source` varchar(255) NOT NULL,
  `approved_budget` varchar(255) NOT NULL,
  `stat` varchar(255) NOT NULL,
  `has_files` enum('With','None') NOT NULL DEFAULT 'None',
  `terminal_report` varchar(255) NOT NULL,
  `moa_file` varchar(255) DEFAULT NULL,
  `status` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_researchdata`
--

CREATE TABLE `tbl_researchdata` (
  `id` int(11) NOT NULL,
  `researcherID` varchar(255) NOT NULL,
  `familyName` varchar(255) NOT NULL,
  `firstName` varchar(255) NOT NULL,
  `middleName` varchar(255) NOT NULL,
  `Suffix` varchar(255) NOT NULL,
  `department` varchar(255) NOT NULL,
  `academic_rank` varchar(255) DEFAULT NULL,
  `program` varchar(255) NOT NULL,
  `bachelor_degree` varchar(255) NOT NULL,
  `bachelor_institution` varchar(255) NOT NULL,
  `bachelor_YearGraduated` varchar(255) NOT NULL,
  `masterDegree` varchar(255) NOT NULL,
  `masterInstitution` varchar(255) NOT NULL,
  `masterYearGraduated` varchar(255) NOT NULL,
  `doctorateDegree` varchar(255) NOT NULL,
  `doctorateInstitution` varchar(255) NOT NULL,
  `doctorateYearGraduate` varchar(255) NOT NULL,
  `postDegree` varchar(255) NOT NULL,
  `postInstitution` varchar(255) NOT NULL,
  `postYearGraduate` varchar(255) NOT NULL,
  `status` int(11) NOT NULL,
  `user` varchar(255) NOT NULL,
  `user_created_on` datetime NOT NULL DEFAULT current_timestamp(),
  `so_file` varchar(255) DEFAULT NULL,
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_research_collaborators`
--

CREATE TABLE `tbl_research_collaborators` (
  `id` int(11) NOT NULL,
  `research_id` int(11) NOT NULL,
  `researcher_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_research_files`
--

CREATE TABLE `tbl_research_files` (
  `id` int(11) NOT NULL,
  `research_id` int(11) NOT NULL,
  `file_category` enum('SO','MOA','Terminal Report','PSE-PES','Financial Report','Other') NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `uploaded_on` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_research_researchers`
--

CREATE TABLE `tbl_research_researchers` (
  `id` int(11) NOT NULL,
  `research_id` int(11) NOT NULL,
  `researcher_id` int(11) NOT NULL,
  `role` varchar(100) DEFAULT 'Author'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_sdgs`
--

CREATE TABLE `tbl_sdgs` (
  `id` int(11) NOT NULL,
  `goal_name` varchar(255) NOT NULL,
  `description` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_trainingsattended`
--

CREATE TABLE `tbl_trainingsattended` (
  `id` int(11) NOT NULL,
  `researcherID` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `venue` varchar(255) NOT NULL,
  `date_train` varchar(255) NOT NULL,
  `lvl` varchar(255) NOT NULL,
  `type_learning_dev` varchar(255) NOT NULL,
  `sponsor_org` varchar(255) NOT NULL,
  `totnh` varchar(255) NOT NULL,
  `moa_file` varchar(255) DEFAULT NULL,
  `a_link` text DEFAULT NULL,
  `has_files` enum('With','None') NOT NULL DEFAULT 'None',
  `status` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_training_files`
--

CREATE TABLE `tbl_training_files` (
  `id` int(11) NOT NULL,
  `training_id` int(11) NOT NULL,
  `file_category` enum('Certificate','Program','MOA','Other') NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `uploaded_on` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_table`
--

CREATE TABLE `user_table` (
  `user_id` int(11) NOT NULL,
  `user_name` varchar(250) NOT NULL,
  `user_contact_no` varchar(30) NOT NULL,
  `user_email` varchar(30) NOT NULL,
  `user_password` varchar(250) NOT NULL,
  `user_profile` varchar(250) NOT NULL,
  `user_type` enum('Manager','Master','Researcher','Statistician') NOT NULL,
  `user_status` enum('Enable','Disable') NOT NULL,
  `user_created_on` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `product_category_table`
--
ALTER TABLE `product_category_table`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `tbl_ext`
--
ALTER TABLE `tbl_ext`
  ADD PRIMARY KEY (`id`),
  ADD KEY `extension_project_id` (`extension_project_id`);

--
-- Indexes for table `tbl_extension_activity_links`
--
ALTER TABLE `tbl_extension_activity_links`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_extension_files`
--
ALTER TABLE `tbl_extension_files`
  ADD PRIMARY KEY (`id`),
  ADD KEY `extension_id` (`extension_id`);

--
-- Indexes for table `tbl_extension_project_conducted`
--
ALTER TABLE `tbl_extension_project_conducted`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_extension_research_links`
--
ALTER TABLE `tbl_extension_research_links`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_extension_research` (`extension_id`,`research_id`);

--
-- Indexes for table `tbl_ip_collaborators`
--
ALTER TABLE `tbl_ip_collaborators`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_ip_files`
--
ALTER TABLE `tbl_ip_files`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ip_id` (`ip_id`);

--
-- Indexes for table `tbl_itelectualprop`
--
ALTER TABLE `tbl_itelectualprop`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_majordiscipline`
--
ALTER TABLE `tbl_majordiscipline`
  ADD PRIMARY KEY (`majorID`);

--
-- Indexes for table `tbl_paperpresentation`
--
ALTER TABLE `tbl_paperpresentation`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_paper_collaborators`
--
ALTER TABLE `tbl_paper_collaborators`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_paper_files`
--
ALTER TABLE `tbl_paper_files`
  ADD PRIMARY KEY (`id`),
  ADD KEY `paper_id` (`paper_id`);

--
-- Indexes for table `tbl_publication`
--
ALTER TABLE `tbl_publication`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_publication_collaborators`
--
ALTER TABLE `tbl_publication_collaborators`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_publication_files`
--
ALTER TABLE `tbl_publication_files`
  ADD PRIMARY KEY (`id`),
  ADD KEY `publication_id` (`publication_id`);

--
-- Indexes for table `tbl_rde_agenda`
--
ALTER TABLE `tbl_rde_agenda`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_researchconducted`
--
ALTER TABLE `tbl_researchconducted`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_researchdata`
--
ALTER TABLE `tbl_researchdata`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_research_collaborators`
--
ALTER TABLE `tbl_research_collaborators`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_research_files`
--
ALTER TABLE `tbl_research_files`
  ADD PRIMARY KEY (`id`),
  ADD KEY `research_id` (`research_id`);

--
-- Indexes for table `tbl_research_researchers`
--
ALTER TABLE `tbl_research_researchers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_pair` (`research_id`,`researcher_id`);

--
-- Indexes for table `tbl_sdgs`
--
ALTER TABLE `tbl_sdgs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_trainingsattended`
--
ALTER TABLE `tbl_trainingsattended`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_training_files`
--
ALTER TABLE `tbl_training_files`
  ADD PRIMARY KEY (`id`),
  ADD KEY `training_id` (`training_id`);

--
-- Indexes for table `user_table`
--
ALTER TABLE `user_table`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `product_category_table`
--
ALTER TABLE `product_category_table`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_ext`
--
ALTER TABLE `tbl_ext`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_extension_activity_links`
--
ALTER TABLE `tbl_extension_activity_links`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_extension_files`
--
ALTER TABLE `tbl_extension_files`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_extension_project_conducted`
--
ALTER TABLE `tbl_extension_project_conducted`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_extension_research_links`
--
ALTER TABLE `tbl_extension_research_links`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_ip_collaborators`
--
ALTER TABLE `tbl_ip_collaborators`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_ip_files`
--
ALTER TABLE `tbl_ip_files`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_itelectualprop`
--
ALTER TABLE `tbl_itelectualprop`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_majordiscipline`
--
ALTER TABLE `tbl_majordiscipline`
  MODIFY `majorID` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_paperpresentation`
--
ALTER TABLE `tbl_paperpresentation`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_paper_collaborators`
--
ALTER TABLE `tbl_paper_collaborators`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_paper_files`
--
ALTER TABLE `tbl_paper_files`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_publication`
--
ALTER TABLE `tbl_publication`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_publication_collaborators`
--
ALTER TABLE `tbl_publication_collaborators`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_publication_files`
--
ALTER TABLE `tbl_publication_files`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_rde_agenda`
--
ALTER TABLE `tbl_rde_agenda`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_researchconducted`
--
ALTER TABLE `tbl_researchconducted`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_researchdata`
--
ALTER TABLE `tbl_researchdata`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_research_collaborators`
--
ALTER TABLE `tbl_research_collaborators`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_research_files`
--
ALTER TABLE `tbl_research_files`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_research_researchers`
--
ALTER TABLE `tbl_research_researchers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_sdgs`
--
ALTER TABLE `tbl_sdgs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_trainingsattended`
--
ALTER TABLE `tbl_trainingsattended`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_training_files`
--
ALTER TABLE `tbl_training_files`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_table`
--
ALTER TABLE `user_table`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tbl_ext`
--
ALTER TABLE `tbl_ext`
  ADD CONSTRAINT `fk_activity_belongs_to_project` FOREIGN KEY (`extension_project_id`) REFERENCES `tbl_extension_project_conducted` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
