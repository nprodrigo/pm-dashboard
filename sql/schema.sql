-- =========================================================
-- Project Tracking & Progress Dashboard Schema
-- Database: project_tracker
-- Compatible with MySQL 5.7+ / MySQL 8.0+ / MariaDB
-- =========================================================

CREATE DATABASE IF NOT EXISTS `learnitc_pm_dashboard` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `learnitc_pm_dashboard`;

-- ---------------------------------------------------------
-- Table: categories
-- ---------------------------------------------------------
DROP TABLE IF EXISTS `pendings`;
DROP TABLE IF EXISTS `milestones`;
DROP TABLE IF EXISTS `projects`;
DROP TABLE IF EXISTS `categories`;

CREATE TABLE `categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `slug` VARCHAR(50) NOT NULL UNIQUE,
  `name` VARCHAR(100) NOT NULL,
  `icon` VARCHAR(50) NOT NULL DEFAULT 'folder',
  `description` VARCHAR(255) NULL,
  `color_code` VARCHAR(20) NOT NULL DEFAULT '#3b82f6',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------
-- Table: projects
-- ---------------------------------------------------------
CREATE TABLE `projects` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(150) NOT NULL,
  `category_id` INT NOT NULL,
  `description` TEXT NULL,
  `status` ENUM('Planning', 'In Progress', 'Under Review', 'On Hold', 'Needs Attention', 'Completed') NOT NULL DEFAULT 'In Progress',
  `priority` ENUM('Low', 'Medium', 'High', 'Critical') NOT NULL DEFAULT 'Medium',
  `progress_percent` TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `start_date` DATE NOT NULL,
  `target_completion_date` DATE NOT NULL,
  `actual_completion_date` DATE NULL,
  `owner_name` VARCHAR(100) NOT NULL,
  `needs_attention` TINYINT(1) NOT NULL DEFAULT 0,
  `attention_reason` TEXT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------
-- Table: milestones
-- ---------------------------------------------------------
CREATE TABLE `milestones` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `project_id` INT NOT NULL,
  `title` VARCHAR(150) NOT NULL,
  `due_date` DATE NOT NULL,
  `status` ENUM('Pending', 'In Progress', 'Completed', 'Delayed') NOT NULL DEFAULT 'Pending',
  `sort_order` INT NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`project_id`) REFERENCES `projects`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------
-- Table: pendings (Action Items / Blockers)
-- ---------------------------------------------------------
CREATE TABLE `pendings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `project_id` INT NOT NULL,
  `title` VARCHAR(200) NOT NULL,
  `description` TEXT NULL,
  `assigned_to` VARCHAR(100) NULL,
  `priority` ENUM('Low', 'Medium', 'High', 'Urgent') NOT NULL DEFAULT 'Medium',
  `status` ENUM('Open', 'In Review', 'Resolved') NOT NULL DEFAULT 'Open',
  `due_date` DATE NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `resolved_at` TIMESTAMP NULL,
  FOREIGN KEY (`project_id`) REFERENCES `projects`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- SEED DATA
-- =========================================================

-- Categories
INSERT INTO `categories` (`id`, `slug`, `name`, `icon`, `description`, `color_code`) VALUES
(1, 'software-dev', 'Software Development', 'code', 'Core software products, API services, and custom applications', '#6366f1'),
(2, 'software-impl', 'Software Implementation', 'layers', 'Enterprise ERP, CRM rollouts, client integrations, and SaaS onboarding', '#06b6d4'),
(3, 'infra-upgrade', 'Infrastructure Upgrades', 'server', 'Server migrations, network security, cloud architecture & hardware', '#ec4899'),
(4, 'documentation', 'Documentation Projects', 'file-text', 'API reference, user guides, compliance policies, and technical SOPs', '#10b981');

-- Projects
INSERT INTO `projects` (`id`, `title`, `category_id`, `description`, `status`, `priority`, `progress_percent`, `start_date`, `target_completion_date`, `owner_name`, `needs_attention`, `attention_reason`) VALUES
(1, 'ERP System Client Rollout', 2, 'Deploying Sapience ERP solution for North America client branches.', 'Needs Attention', 'Critical', 65, '2026-06-01', '2026-08-20', 'Sarah Jenkins', 1, 'Third-party API credentials delayed from client IT team. Data migration blocked.'),
(2, 'Customer Mobile App v3.0', 1, 'React Native mobile app rewrite featuring biometric auth and payment gateway.', 'In Progress', 'High', 80, '2026-05-15', '2026-08-15', 'Alex Rivera', 0, NULL),
(3, 'Cloud Datacenter Migration to AWS', 3, 'Migrating on-premise rack servers to AWS EC2 & RDS Multi-AZ clusters.', 'In Progress', 'Critical', 45, '2026-06-15', '2026-09-30', 'David Chen', 1, 'Spike in data transfer latency observed during pilot sync. Network team reviewing direct connect options.'),
(4, 'Developer Portal & API Specs', 4, 'Standardizing OpenAPI 3.0 specs and developer documentation site.', 'Under Review', 'Medium', 90, '2026-07-01', '2026-08-10', 'Elena Rostova', 0, NULL),
(5, 'HR Payroll Integration Module', 2, 'Automated payroll and attendance sync implementation for internal HR.', 'In Progress', 'Medium', 35, '2026-07-10', '2026-09-15', 'Marcus Vance', 0, NULL),
(6, 'Zero-Trust Network Firewall Upgrade', 3, 'Upgrading core edge routers and enforcing micro-segmentation policies.', 'Needs Attention', 'High', 55, '2026-06-01', '2026-08-28', 'David Chen', 1, 'Awaiting vendor replacement module for core router unit 2.'),
(7, 'AI Chatbot Customer Support Agent', 1, 'LLM-powered automated resolution agent integrated into web portal.', 'Planning', 'Medium', 15, '2026-07-20', '2026-10-15', 'Alex Rivera', 0, NULL),
(8, 'ISO 27001 Security Compliance Docs', 4, 'Drafting and auditing cybersecurity compliance documentation and disaster recovery runbooks.', 'In Progress', 'High', 70, '2026-06-10', '2026-08-25', 'Elena Rostova', 0, NULL);

-- Milestones
INSERT INTO `milestones` (`project_id`, `title`, `due_date`, `status`, `sort_order`) VALUES
(1, 'Database Schema & User Roles Provisioning', '2026-06-15', 'Completed', 1),
(1, 'Legacy Data Extraction & Transformation', '2026-07-10', 'Completed', 2),
(1, 'Third-party API Integration & Staging Test', '2026-08-05', 'Delayed', 3),
(1, 'User Acceptance Testing (UAT)', '2026-08-15', 'Pending', 4),
(1, 'Production Go-Live', '2026-08-20', 'Pending', 5),

(2, 'Wireframes & UI Component System', '2026-06-01', 'Completed', 1),
(2, 'Authentication & Security Module', '2026-06-25', 'Completed', 2),
(2, 'Payment Gateway & Wallet Features', '2026-07-20', 'Completed', 3),
(2, 'App Store & Play Store Submissions', '2026-08-10', 'In Progress', 4),

(3, 'Infrastructure-as-Code Terraform Scripts', '2026-07-01', 'Completed', 1),
(3, 'Staging DB Replication & Benchmark', '2026-07-25', 'Completed', 2),
(3, 'Production Database Failover Cutover', '2026-08-30', 'In Progress', 3),
(3, 'Legacy Server Decommissioning', '2026-09-30', 'Pending', 4),

(4, 'OpenAPI 3.0 Schema Definitions', '2026-07-15', 'Completed', 1),
(4, 'Code Sample Generation & Tutorials', '2026-07-28', 'Completed', 2),
(4, 'Security & Authentication Guide Review', '2026-08-08', 'In Progress', 3);

-- Pendings (Action Items / Blockers)
INSERT INTO `pendings` (`project_id`, `title`, `description`, `assigned_to`, `priority`, `status`, `due_date`) VALUES
(1, 'Client OAuth2 Secret Keys missing', 'Awaiting Production OAuth Credentials from Client IT Administrator.', 'Sarah Jenkins', 'Urgent', 'Open', '2026-08-04'),
(1, 'Sign-off on Custom Tax Report Schema', 'Finance team review of custom tax calculation rules in ERP.', 'Marcus Vance', 'High', 'In Review', '2026-08-06'),
(3, 'AWS DirectConnect BGP Route Bouncing', 'Investigate route flapping during high throughput load testing.', 'David Chen', 'Urgent', 'Open', '2026-08-05'),
(6, 'RMA Replacement Unit Shipment tracking', 'Vendor hardware dispatch pending tracking confirmation for edge router.', 'David Chen', 'High', 'Open', '2026-08-07'),
(2, 'Apple App Store Developer Account Renewal', 'Corporate credit card update required for Apple Developer program.', 'Alex Rivera', 'Medium', 'Open', '2026-08-08');
