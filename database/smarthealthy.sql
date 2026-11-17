-- ============================================================================
-- SmartHealthy Database Schema & Initial Demonstration Seed Data
-- Sistem Informasi Pelayanan Kesehatan & Antrean Puskesmas Berbasis Web (MVC)
-- Wilayah: Puskesmas Astanajapura, Kabupaten Cirebon
-- Dibuat untuk: Tugas UAS Tahap 3 (Konstruksi Kode, AI, Testing & CI/CD)
-- ============================================================================

CREATE DATABASE IF NOT EXISTS `smarthealthy` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `smarthealthy`;

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';

-- ----------------------------------------------------------------------------
-- Table structure for `villages`
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `villages`;
CREATE TABLE `villages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `district` varchar(100) NOT NULL DEFAULT 'Astanajapura',
  `postal_code` varchar(10) NOT NULL DEFAULT '45181',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table structure for `users`
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nik` varchar(16) DEFAULT NULL,
  `name` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(25) NOT NULL,
  `address` text DEFAULT NULL,
  `village_id` int(10) unsigned DEFAULT NULL,
  `gender` enum('L','P') NOT NULL DEFAULT 'L',
  `birth_date` date DEFAULT NULL,
  `role` enum('admin','user') NOT NULL DEFAULT 'user',
  `password` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `phone_verified_at` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `phone` (`phone`),
  UNIQUE KEY `nik` (`nik`),
  KEY `fk_users_village` (`village_id`),
  CONSTRAINT `fk_users_village` FOREIGN KEY (`village_id`) REFERENCES `villages` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table structure for `health_services`
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `health_services`;
CREATE TABLE `health_services` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `doctor_name` varchar(150) DEFAULT NULL,
  `quota_daily` int(11) NOT NULL DEFAULT 50,
  `icon` varchar(50) NOT NULL DEFAULT 'bi-heart-pulse',
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table structure for `schedules`
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `schedules`;
CREATE TABLE `schedules` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `health_service_id` int(10) unsigned NOT NULL,
  `day_of_week` enum('Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu') NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `max_quota` int(11) NOT NULL DEFAULT 50,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_schedules_service` (`health_service_id`),
  CONSTRAINT `fk_schedules_service` FOREIGN KEY (`health_service_id`) REFERENCES `health_services` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table structure for `registrations`
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `registrations`;
CREATE TABLE `registrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `registration_code` varchar(30) NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `health_service_id` int(10) unsigned NOT NULL,
  `schedule_id` int(10) unsigned DEFAULT NULL,
  `registration_date` date NOT NULL,
  `queue_number` int(11) NOT NULL,
  `status` enum('Pending','Confirmed','Completed','Cancelled') NOT NULL DEFAULT 'Pending',
  `complaint` text NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `registration_code` (`registration_code`),
  KEY `fk_registrations_schedule` (`schedule_id`),
  KEY `idx_reg_quota_check` (`health_service_id`,`schedule_id`,`registration_date`,`status`),
  KEY `idx_reg_user_active` (`user_id`,`health_service_id`,`registration_date`,`status`),
  CONSTRAINT `fk_registrations_schedule` FOREIGN KEY (`schedule_id`) REFERENCES `schedules` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_registrations_service` FOREIGN KEY (`health_service_id`) REFERENCES `health_services` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_registrations_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table structure for `health_categories`
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `health_categories`;
CREATE TABLE `health_categories` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(120) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table structure for `health_articles`
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `health_articles`;
CREATE TABLE `health_articles` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `category_id` int(10) unsigned NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `summary` text NOT NULL,
  `content` longtext NOT NULL,
  `thumbnail` varchar(255) DEFAULT NULL,
  `views_count` int(11) NOT NULL DEFAULT 0,
  `status` enum('published','draft') NOT NULL DEFAULT 'published',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `fk_articles_category` (`category_id`),
  CONSTRAINT `fk_articles_category` FOREIGN KEY (`category_id`) REFERENCES `health_categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table structure for `questions`
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `questions`;
CREATE TABLE `questions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `category_id` int(10) unsigned DEFAULT NULL,
  `question_text` text NOT NULL,
  `explanation` text DEFAULT NULL,
  `points` int(11) NOT NULL DEFAULT 10,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_questions_category` (`category_id`),
  CONSTRAINT `fk_questions_category` FOREIGN KEY (`category_id`) REFERENCES `health_categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table structure for `question_options`
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `question_options`;
CREATE TABLE `question_options` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `question_id` int(10) unsigned NOT NULL,
  `option_text` text NOT NULL,
  `is_correct` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_options_question` (`question_id`),
  CONSTRAINT `fk_options_question` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table structure for `quiz_attempts`
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `quiz_attempts`;
CREATE TABLE `quiz_attempts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `score` int(11) NOT NULL DEFAULT 0,
  `total_questions` int(11) NOT NULL DEFAULT 0,
  `correct_answers` int(11) NOT NULL DEFAULT 0,
  `completed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_attempts_user` (`user_id`),
  CONSTRAINT `fk_attempts_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table structure for `quiz_answers`
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `quiz_answers`;
CREATE TABLE `quiz_answers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `attempt_id` int(10) unsigned NOT NULL,
  `question_id` int(10) unsigned NOT NULL,
  `selected_option_id` int(10) unsigned DEFAULT NULL,
  `is_correct` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_answers_attempt` (`attempt_id`),
  KEY `fk_answers_question` (`question_id`),
  KEY `fk_answers_option` (`selected_option_id`),
  CONSTRAINT `fk_answers_attempt` FOREIGN KEY (`attempt_id`) REFERENCES `quiz_attempts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_answers_option` FOREIGN KEY (`selected_option_id`) REFERENCES `question_options` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_answers_question` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table structure for `notifications`
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `notifications`;
CREATE TABLE `notifications` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `type` varchar(50) NOT NULL COMMENT 'registration, schedule, account, education, announcement',
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `target_url` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `read_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_notif_user_read_created` (`user_id`,`is_read`,`created_at`),
  KEY `idx_notif_type` (`type`),
  CONSTRAINT `fk_notifications_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=102 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table structure for `notification_dedup`
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `notification_dedup`;
CREATE TABLE `notification_dedup` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `dedup_key` varchar(150) NOT NULL,
  `channel` varchar(30) NOT NULL DEFAULT 'app',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `dedup_key` (`dedup_key`),
  KEY `fk_notif_dedup_user` (`user_id`),
  KEY `idx_dedup_key` (`dedup_key`),
  CONSTRAINT `fk_notif_dedup_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=80 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table structure for `scheduled_reminders`
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `scheduled_reminders`;
CREATE TABLE `scheduled_reminders` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `registration_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `reminder_type` enum('H-1','H-2_HOURS') NOT NULL,
  `scheduled_for` datetime NOT NULL,
  `status` enum('pending','sent','cancelled','failed') NOT NULL DEFAULT 'pending',
  `sent_at` datetime DEFAULT NULL,
  `cancelled_at` datetime DEFAULT NULL,
  `cancel_reason` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_reg_type` (`registration_id`,`reminder_type`),
  KEY `idx_status_scheduled` (`status`,`scheduled_for`),
  KEY `idx_user_reminders` (`user_id`,`status`),
  CONSTRAINT `fk_sched_reg` FOREIGN KEY (`registration_id`) REFERENCES `registrations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_sched_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table structure for `user_verifications`
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `user_verifications`;
CREATE TABLE `user_verifications` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `type` enum('email','whatsapp_otp') NOT NULL,
  `token_or_code` varchar(255) NOT NULL,
  `attempts` int(11) NOT NULL DEFAULT 0,
  `max_attempts` int(11) NOT NULL DEFAULT 3,
  `expires_at` datetime NOT NULL,
  `verified_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_verification_lookup` (`user_id`,`type`,`verified_at`),
  CONSTRAINT `fk_verifications_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table structure for `user_notification_preferences`
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `user_notification_preferences`;
CREATE TABLE `user_notification_preferences` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `whatsapp_enabled` tinyint(1) NOT NULL DEFAULT 0,
  `notify_registration` tinyint(1) NOT NULL DEFAULT 1,
  `notify_status_change` tinyint(1) NOT NULL DEFAULT 1,
  `notify_reminder` tinyint(1) NOT NULL DEFAULT 1,
  `notify_schedule_change` tinyint(1) NOT NULL DEFAULT 1,
  `notify_announcement` tinyint(1) NOT NULL DEFAULT 1,
  `notify_cancellation` tinyint(1) NOT NULL DEFAULT 1,
  `notify_education` tinyint(1) NOT NULL DEFAULT 0,
  `whatsapp_consent_at` datetime DEFAULT NULL,
  `whatsapp_consent_revoked_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`),
  CONSTRAINT `fk_pref_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table structure for `whatsapp_settings`
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `whatsapp_settings`;
CREATE TABLE `whatsapp_settings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB AUTO_INCREMENT=209 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table structure for `whatsapp_message_logs`
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `whatsapp_message_logs`;
CREATE TABLE `whatsapp_message_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned DEFAULT NULL,
  `message_type` varchar(50) NOT NULL COMMENT 'registration, reminder, schedule_change, status_update, announcement, otp',
  `template_name` varchar(100) DEFAULT NULL,
  `message_id` varchar(150) DEFAULT NULL COMMENT 'Meta wamid / Mock ID',
  `recipient_number_masked` varchar(30) NOT NULL,
  `recipient_phone` varchar(30) DEFAULT NULL,
  `delivery_status` enum('queued','sent','delivered','read','failed','rejected','skipped') NOT NULL DEFAULT 'queued',
  `error_code` varchar(50) DEFAULT NULL,
  `error_message` text DEFAULT NULL,
  `payload_summary` text DEFAULT NULL,
  `sent_at` datetime DEFAULT NULL,
  `delivered_at` datetime DEFAULT NULL,
  `read_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `message_id` (`message_id`),
  KEY `idx_wa_user` (`user_id`),
  KEY `idx_wa_status` (`delivery_status`),
  KEY `idx_wa_created` (`created_at`),
  CONSTRAINT `fk_wa_logs_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=76 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table structure for `chat_conversations`
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `chat_conversations`;
CREATE TABLE `chat_conversations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned DEFAULT NULL,
  `session_id` varchar(100) DEFAULT NULL,
  `title` varchar(255) NOT NULL DEFAULT 'Percakapan Asisten AI',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_chat_user` (`user_id`),
  KEY `idx_chat_session` (`session_id`),
  CONSTRAINT `fk_chat_conversations_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table structure for `chat_messages`
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `chat_messages`;
CREATE TABLE `chat_messages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `conversation_id` int(10) unsigned NOT NULL,
  `role` enum('user','assistant','system') NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_chat_msg_conv` (`conversation_id`,`created_at`),
  CONSTRAINT `fk_chat_messages_conv` FOREIGN KEY (`conversation_id`) REFERENCES `chat_conversations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=115 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table structure for `ai_logs`
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `ai_logs`;
CREATE TABLE `ai_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned DEFAULT NULL,
  `feature_type` varchar(50) NOT NULL COMMENT 'e.g., symptom_checker, article_summary, triage_assist',
  `prompt_payload` longtext DEFAULT NULL,
  `response_payload` longtext DEFAULT NULL,
  `tokens_used` int(11) NOT NULL DEFAULT 0,
  `response_time_ms` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_ai_logs_user` (`user_id`),
  CONSTRAINT `fk_ai_logs_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=64 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table structure for `password_resets`
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `password_resets`;
CREATE TABLE `password_resets` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `email` varchar(150) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_resets_user` (`user_id`),
  KEY `idx_reset_token` (`token`),
  CONSTRAINT `fk_resets_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table structure for `rate_limits`
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `rate_limits`;
CREATE TABLE `rate_limits` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `rate_key` varchar(150) NOT NULL,
  `hits` int(11) NOT NULL DEFAULT 1,
  `last_attempt_at` int(11) NOT NULL,
  `decay_seconds` int(11) NOT NULL DEFAULT 60,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `rate_key` (`rate_key`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- Initial Demonstration Seed Data (Clean & Sanitized)
-- ============================================================================

-- ==========================================================
-- Seeder Data: SmartHealthy
-- Data Awal Puskesmas Astanajapura, Kabupaten Cirebon
-- ==========================================================

USE `smarthealthy`;

SET FOREIGN_KEY_CHECKS = 0;

-- 1. Seeder Desa Wilayah Astanajapura (12 Desa)
TRUNCATE TABLE `villages`;
INSERT INTO `villages` (`id`, `name`, `district`, `postal_code`) VALUES
(1, 'Astanajapura', 'Astanajapura', '45181'),
(2, 'Buntet', 'Astanajapura', '45181'),
(3, 'Japurabakti', 'Astanajapura', '45181'),
(4, 'Japuralor', 'Astanajapura', '45181'),
(5, 'Japura Kidul', 'Astanajapura', '45181'),
(6, 'Kanci', 'Astanajapura', '45181'),
(7, 'Kanci Kulon', 'Astanajapura', '45181'),
(8, 'Kendal', 'Astanajapura', '45181'),
(9, 'Mertapada Kulon', 'Astanajapura', '45181'),
(10, 'Mertapada Wetan', 'Astanajapura', '45181'),
(11, 'Munjul', 'Astanajapura', '45181'),
(12, 'Sidamulya', 'Astanajapura', '45181');

-- 2. Seeder Users (Admin, Petugas, dan Pasien Demo Terverifikasi)
TRUNCATE TABLE `users`;
INSERT INTO `users` (`id`, `nik`, `name`, `email`, `phone`, `address`, `village_id`, `gender`, `birth_date`, `role`, `password`, `email_verified_at`, `phone_verified_at`, `is_active`) VALUES
(1, '3209140101900001', 'Admin Puskesmas Astanajapura', 'admin@smarthealthy.id', '081234567890', 'Komplek Puskesmas Astanajapura', 1, 'L', '1990-01-01', 'admin', '$2y$10$rPwYHQ7zgSlM29jQpEOB9eHAIYqY/Rabs4ioazxc1ZBFmsFBnYO/a', '2026-01-01 08:00:00', '2026-01-01 08:00:00', 1),
(2, '3209141205950002', 'Ahmad Fauzi', 'pasien@smarthealthy.id', '085712345678', 'Blok Manis RT 02 RW 01, Desa Buntet', 2, 'L', '1995-05-12', 'user', '$2y$10$Y2GnlKE/9c1vfZkQ9kfygu76iXaumZU43drYGmwA6GhtMJHRW/lG2', '2026-01-01 09:00:00', '2026-01-01 09:00:00', 1),
(3, '3209144809980003', 'Siti Rahmawati', 'siti@smarthealthy.id', '087812345679', 'Blok Pahing RT 03 RW 02, Desa Mertapada Kulon', 9, 'P', '1998-09-08', 'user', '$2y$10$Y2GnlKE/9c1vfZkQ9kfygu76iXaumZU43drYGmwA6GhtMJHRW/lG2', '2026-01-01 09:30:00', '2026-01-01 09:30:00', 1),
(4, '3209141503800004', 'Budi Santoso', 'budi@smarthealthy.id', '081398765432', 'Blok Wage RT 01 RW 04, Desa Kanci', 6, 'L', '1980-03-15', 'user', '$2y$10$Y2GnlKE/9c1vfZkQ9kfygu76iXaumZU43drYGmwA6GhtMJHRW/lG2', NULL, NULL, 1);

-- 3. Seeder Layanan / Poli Kesehatan
TRUNCATE TABLE `health_services`;
INSERT INTO `health_services` (`id`, `code`, `name`, `description`, `doctor_name`, `quota_daily`, `icon`, `status`) VALUES
(1, 'POLI-UMUM', 'Poli Umum', 'Pemeriksaan kesehatan dasar, pengobatan umum, rujukan medis awal, dan konsultasi kesehatan keluarga.', 'dr. H. Rahmat Hidayat, M.Kes', 60, 'bi-hospital', 'active'),
(2, 'POLI-GIGI', 'Poli Gigi & Mulut', 'Pemeriksaan gigi, pencabutan gigi, penambalan, pembersihan karang gigi (scaling), dan pengobatan infeksi mulut.', 'drg. Nurul Annisa', 30, 'bi-emoji-smile', 'active'),
(3, 'POLI-KIA', 'Poli KIA & KB', 'Pelayanan Kesehatan Ibu dan Anak, pemeriksaan kehamilan (ANC), imunisasi bayi, KB, dan deteksi dini risiko tinggi kehamilan.', 'Bdn. Hj. Siti Maryam, S.ST', 40, 'bi-heart-pulse', 'active'),
(4, 'POLI-ANAK', 'Poli Anak & MTBS', 'Manajemen Terpadu Balita Sakit (MTBS), pemeriksaan tumbuh kembang balita, pengobatan infeksi saluran napas, dan diare.', 'dr. Farhan Maulana, Sp.A', 40, 'bi-person-hearts', 'active'),
(5, 'POLI-LANSIA', 'Poli Lansia & PTM', 'Pelayanan kesehatan khusus lanjut usia, pemantauan hipertensi, diabetes melitus, jantung, dan skrining penyakit tidak menular.', 'dr. Maya Sartika', 35, 'bi-person-walking', 'active'),
(6, 'POLI-GIZI', 'Poli Konseling Gizi', 'Konsultasi gizi balita kurus/stunting, diet hipertensi, diabetes, gizi ibu hamil, dan edukasi pola makan seimbang.', 'Nuraini, S.Gz', 25, 'bi-apple', 'active'),
(7, 'LAB-PUSKESMAS', 'Laboratorium Sederhana', 'Pemeriksaan darah lengkap, gula darah, asam urat, kolesterol, tes urine, tes kehamilan, dan pemeriksaan dahak TB.', 'Ahmad Sanusi, A.Md.AK', 50, 'bi-eyedropper', 'active'),
(8, 'FARMASI', 'Farmasi & Apotek', 'Pelayanan peresepan obat gratis standar JKN/BPJS dan pasien umum dengan edukasi aturan pakai obat.', 'Apt. Dian Permata, S.Farm', 100, 'bi-capsule', 'active');

-- 4. Seeder Jadwal Operasional Poli
TRUNCATE TABLE `schedules`;
INSERT INTO `schedules` (`health_service_id`, `day_of_week`, `start_time`, `end_time`, `max_quota`, `is_active`) VALUES
(1, 'Senin', '08:00:00', '12:00:00', 60, 1),
(1, 'Selasa', '08:00:00', '12:00:00', 60, 1),
(1, 'Rabu', '08:00:00', '12:00:00', 60, 1),
(1, 'Kamis', '08:00:00', '12:00:00', 60, 1),
(1, 'Jumat', '08:00:00', '11:00:00', 40, 1),
(1, 'Sabtu', '08:00:00', '11:30:00', 40, 1),
(2, 'Senin', '08:30:00', '12:00:00', 30, 1),
(2, 'Selasa', '08:30:00', '12:00:00', 30, 1),
(2, 'Rabu', '08:30:00', '12:00:00', 30, 1),
(2, 'Kamis', '08:30:00', '12:00:00', 30, 1),
(3, 'Senin', '08:00:00', '12:00:00', 40, 1),
(3, 'Rabu', '08:00:00', '12:00:00', 40, 1),
(3, 'Jumat', '08:00:00', '11:00:00', 30, 1),
(5, 'Selasa', '08:00:00', '11:30:00', 35, 1),
(5, 'Kamis', '08:00:00', '11:30:00', 35, 1);

-- 5. Seeder Kategori Edukasi Kesehatan
TRUNCATE TABLE `health_categories`;
INSERT INTO `health_categories` (`id`, `name`, `slug`, `description`) VALUES
(1, 'Kesehatan Ibu & Anak', 'kesehatan-ibu-anak', 'Panduan tumbuh kembang, imunisasi, gizi balita, dan pencegahan stunting di pedesaan.'),
(2, 'Pola Hidup Bersih & Sehat (PHBS)', 'phbs', 'Edukasi kebiasaan hidup sehat masyarakat Astanajapura, cuci tangan, dan sanitasi lingkungan.'),
(3, 'Penyakit Tidak Menular (PTM)', 'penyakit-tidak-menular', 'Pencegahan hipertensi, diabetes melitus, asam urat, dan stroke pada usia produktif serta lansia.'),
(4, 'Pencegahan Penyakit Menular', 'penyakit-menular', 'Antisipasi Demam Berdarah (DBD), TBC, Infeksi Saluran Pernapasan (ISPA), dan penyakit musiman.');

-- 6. Seeder Artikel Edukasi Kesehatan
TRUNCATE TABLE `health_articles`;
INSERT INTO `health_articles` (`id`, `category_id`, `title`, `slug`, `summary`, `content`, `views_count`, `status`) VALUES
(1, 1, 'Pencegahan Stunting pada 1000 Hari Pertama Kehidupan di Astanajapura', 'pencegahan-stunting-1000-hpk-astanajapura', 'Pentingnya nutrisi seimbang bagi ibu hamil dan baduta untuk mencetak generasi cerdas dan sehat di wilayah Cirebon.', '<p>Stunting adalah kondisi gagal tumbuh pada anak balita akibat kekurangan gizi kronis, terutama pada <strong>1.000 Hari Pertama Kehidupan (HPK)</strong> yaitu sejak janin dalam kandungan hingga anak berusia 2 tahun.</p><h4>Langkah Pencegahan Utama:</h4><ul><li>Pemeriksaan kehamilan rutin (minimal 6 kali) di Puskesmas Astanajapura atau Posyandu desa.</li><li>Konsumsi tablet tambah darah (TTD) bagi ibu hamil dan remaja putri.</li><li>Pemberian ASI Eksklusif selama 6 bulan pertama tanpa makanan tambahan.</li><li>Pemberian MP-ASI bergizi seimbang berbahan pangan lokal mulai usia 6 bulan.</li><li>Memastikan sanitasi air bersih dan jamban sehat di lingkungan rumah.</li></ul><p>Warga dapat berkonsultasi langsung secara gratis di <em>Poli Konseling Gizi Puskesmas Astanajapura</em> setiap hari kerja.</p>', 142, 'published'),
(2, 2, 'Gerakan PHBS: 5 Langkah Efektif Bebas Jentik Nyamuk DBD di Lingkungan Desa', 'gerakan-phbs-bebas-jentik-nyamuk-dbd', 'Kenali tanda bahaya Demam Berdarah Dengue dan terapkan metode 3M Plus di rumah Anda.', '<p>Memasuki musim penghujan, potensi perkembangbiakan nyamuk <em>Aedes aegypti</em> meningkat di lingkungan pemukiman. Puskesmas Astanajapura mengajak seluruh warga untuk aktif melakukan Gerakan Satu Rumah Satu Jumantik.</p><h4>Metode 3M Plus:</h4><ol><li><strong>Menguras</strong> tempat penampungan air seperti bak mandi dan tempayan secara berkala.</li><li><strong>Menutup</strong> rapat tempat-tempat penampungan air minum atau tandon.</li><li><strong>Mendaur ulang</strong> atau memanfaatkan kembali barang bekas yang berpotensi menampung air hujan.</li><li><strong>Plus:</strong> Menaburkan bubuk larvasida (abate) yang dapat diperoleh gratis di Puskesmas.</li></ol>', 98, 'published'),
(3, 3, 'Waspada Tekanan Darah Tinggi: Skrining Gratis Rutin di Posbindu PTM', 'waspada-tekanan-darah-tinggi-skrining-gratis', 'Hipertensi sering dijuluki Silent Killer. Mari ketahui batas normal tensi dan cara mengendalikannya.', '<p>Hipertensi terjadi ketika tekanan darah berada pada angka 140/90 mmHg atau lebih tinggi secara konsisten. Seringkali hipertensi tidak menunjukkan gejala awal, sehingga pemeriksaan rutin sangatlah penting.</p><h4>Tips Mengontrol Tekanan Darah dengan CERDIK:</h4><ul><li><strong>C:</strong> Cek kesehatan secara berkala di Puskesmas Astanajapura.</li><li><strong>E:</strong> Enyahkan asap rokok.</li><li><strong>R:</strong> Rajin aktivitas fisik minimal 30 menit per hari.</li><li><strong>D:</strong> Diet seimbang, batasi garam maksimal 1 sendok teh per hari.</li><li><strong>I:</strong> Istirahat cukup (7-8 jam per malam).</li><li><strong>K:</strong> Kelola stres dengan baik.</li></ul>', 175, 'published');

-- 7. Seeder Bank Soal Kuis Kesehatan
TRUNCATE TABLE `questions`;
INSERT INTO `questions` (`id`, `category_id`, `question_text`, `explanation`, `points`, `status`) VALUES
(1, 1, 'Berapa lama durasi ideal pemberian Air Susu Ibu (ASI) Eksklusif pada bayi?', 'ASI Eksklusif diberikan selama 6 bulan pertama tanpa tambahan makanan atau minuman lain, kemudian dilanjutkan bersama MP-ASI hingga anak berusia 2 tahun.', 20, 'active'),
(2, 2, 'Berapa langkah cuci tangan pakai sabun (CTPS) yang direkomendasikan oleh WHO?', 'WHO merekomendasikan 6 langkah cuci tangan dengan air mengalir dan sabun selama minimal 20-40 detik.', 20, 'active'),
(3, 3, 'Berapa batas maksimal konsumsi garam harian yang dianjurkan per orang?', 'Kementerian Kesehatan menganjurkan konsumsi garam maksimal 1 sendok teh (5 gram) per orang per hari untuk mencegah hipertensi.', 20, 'active'),
(4, 2, 'Apa kepanjangan dari prinsip 3M Plus dalam pencegahan DBD?', '3M Plus adalah Menguras, Menutup, dan Mendaur ulang barang bekas, plus langkah pencegahan gigitan nyamuk lainnya.', 20, 'active'),
(5, 1, 'Kapan periode emas 1000 Hari Pertama Kehidupan (HPK) berlangsung?', '1000 HPK dihitung sejak awal masa kehamilan (270 hari) hingga anak mencapai usia 2 tahun (730 hari).', 20, 'active');

-- 8. Seeder Opsi Jawaban Soal Kuis
TRUNCATE TABLE `question_options`;
INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`) VALUES
(1, '3 Bulan', 0),
(1, '6 Bulan', 1),
(1, '9 Bulan', 0),
(1, '12 Bulan', 0),
(2, '4 Langkah', 0),
(2, '6 Langkah', 1),
(2, '8 Langkah', 0),
(2, '10 Langkah', 0),
(3, '1 Sendok Teh (5 gram)', 1),
(3, '3 Sendok Teh (15 gram)', 0),
(3, '1 Sendok Makan', 0),
(3, 'Bebas tanpa batas', 0),
(4, 'Mencuci, Mengeringkan, Menggiling', 0),
(4, 'Menguras, Menutup, Mendaur ulang', 1),
(4, 'Menyapu, Mengepel, Membakar', 0),
(4, 'Melihat, Meraba, Menerawang', 0),
(5, 'Sejak lahir hingga usia 5 tahun', 0),
(5, 'Sejak awal kehamilan hingga anak usia 2 tahun', 1),
(5, 'Dari usia 1 tahun sampai 3 tahun', 0),
(5, 'Masa remaja usia 10-18 tahun', 0);

-- 9. Seeder Pendaftaran Antrean Demo (Status: Completed, Confirmed, Pending)
TRUNCATE TABLE `registrations`;
INSERT INTO `registrations` (`id`, `registration_code`, `user_id`, `health_service_id`, `schedule_id`, `registration_date`, `queue_number`, `status`, `complaint`, `notes`) VALUES
(1, 'REG-AST-20260918-001', 2, 1, 1, CURRENT_DATE(), 1, 'Completed', 'Demam dan flu sejak 2 hari yang lalu, badan terasa lemas.', 'Pasien telah diperiksa, diberikan paracetamol dan vitamin.'),
(2, 'REG-AST-20260918-002', 3, 3, 7, CURRENT_DATE(), 2, 'Confirmed', 'Pemeriksaan kehamilan trimester kedua rutin.', 'Menunggu di ruang konsultasi KIA.'),
(3, 'REG-AST-20260918-003', 4, 5, 9, CURRENT_DATE(), 3, 'Pending', 'Kontrol rutin tensi darah tinggi dan cek gula darah berkala.', 'Antrean nomor 003 Poli Lansia.');

-- 10. Seeder Notifikasi Pengguna & Pasien
TRUNCATE TABLE `notifications`;
INSERT INTO `notifications` (`id`, `user_id`, `type`, `title`, `message`, `target_url`, `is_read`, `created_at`, `read_at`) VALUES
(1, 2, 'account', 'Selamat Datang di Portal SmartHealthy', 'Halo Ahmad Fauzi, akun Anda telah berhasil didaftarkan di sistem antrean online Puskesmas Astanajapura. Nikmati kemudahan pendaftaran poli dan tiket digital.', '/profil', 1, DATE_SUB(NOW(), INTERVAL 3 DAY), DATE_SUB(NOW(), INTERVAL 3 DAY)),
(2, 2, 'registration', 'Pendaftaran Antrean Berhasil Disimpan', 'Pendaftaran Anda di Poli Umum untuk tanggal hari ini berhasil disimpan dengan Nomor Antrean #001. Kode Booking: REG-AST-20260918-001.', '/pendaftaran/tiket/REG-AST-20260918-001', 1, DATE_SUB(NOW(), INTERVAL 5 HOUR), DATE_SUB(NOW(), INTERVAL 4 HOUR)),
(3, 2, 'registration', 'Pemeriksaan Selesai', 'Pemeriksaan Anda di Poli Umum telah selesai. Terima kasih atas kunjungan Anda di Puskesmas Astanajapura. Simpan bukti e-Ticket untuk arsip Anda.', '/pendaftaran/tiket/REG-AST-20260918-001', 0, DATE_SUB(NOW(), INTERVAL 2 HOUR), NULL),
(4, 2, 'schedule', 'Pengingat Kunjungan Hari Ini: Poli Umum', 'Halo Ahmad Fauzi, hari ini adalah jadwal kunjungan Anda di Poli Umum dengan Nomor Antrean #001. Harap hadir 15 menit sebelum waktu pelayanan.', '/pendaftaran/tiket/REG-AST-20260918-001', 0, DATE_SUB(NOW(), INTERVAL 4 HOUR), NULL),
(5, 2, 'education', 'Materi Edukasi Kesehatan Baru: Pencegahan Stunting 1000 HPK', 'Puskesmas Astanajapura menerbitkan artikel edukasi baru mengenai Kesehatan Ibu & Anak. Simak panduan lengkap pencegahan stunting pada balita.', '/artikel/pencegahan-stunting-1000-hpk-astanajapura', 0, DATE_SUB(NOW(), INTERVAL 1 DAY), NULL),
(6, 3, 'account', 'Alamat Email Berhasil Diverifikasi', 'Alamat email akun SmartHealthy Anda telah berhasil diverifikasi. Akun Anda kini memiliki akses penuh ke seluruh fitur kesehatan.', '/profil', 1, DATE_SUB(NOW(), INTERVAL 2 DAY), DATE_SUB(NOW(), INTERVAL 2 DAY)),
(7, 3, 'registration', 'Pendaftaran Antrean Dikonfirmasi', 'Pendaftaran Anda di Poli KIA & KB (#002) telah DIKONFIRMASI oleh petugas Puskesmas Astanajapura. Silakan menunggu di ruang konsultasi KIA.', '/pendaftaran/tiket/REG-AST-20260918-002', 0, DATE_SUB(NOW(), INTERVAL 1 HOUR), NULL),
(8, 3, 'announcement', 'Pengumuman Pelayanan Imunisasi Balita', 'Puskesmas Astanajapura menyelenggarakan pelayanan imunisasi dasar lengkap serentak di Posyandu wilayah Desa Mertapada Kulon.', '/layanan', 0, DATE_SUB(NOW(), INTERVAL 6 HOUR), NULL),
(9, 1, 'announcement', 'Pemberitahuan Sistem SmartHealthy', 'Selamat datang di Panel Administrasi Petugas Puskesmas Astanajapura. Pantau antrean pasien dan publikasikan jadwal layanan secara berkala.', '/admin', 0, NOW(), NULL);

-- 11. Seeder Deduplication Keys
TRUNCATE TABLE `notification_dedup`;
INSERT INTO `notification_dedup` (`user_id`, `dedup_key`, `channel`, `created_at`) VALUES
(2, 'account_reg_2', 'app', DATE_SUB(NOW(), INTERVAL 3 DAY)),
(2, 'reg_created_1', 'app', DATE_SUB(NOW(), INTERVAL 5 HOUR)),
(3, 'email_verified_3', 'app', DATE_SUB(NOW(), INTERVAL 2 DAY));

SET FOREIGN_KEY_CHECKS = 1;


-- Seeder WhatsApp Gateway Settings (Sanitized & Pre-Configured)
TRUNCATE TABLE `whatsapp_settings`;
INSERT INTO `whatsapp_settings` (`id`, `setting_key`, `setting_value`, `description`, `created_at`, `updated_at`) VALUES
(1, 'whatsapp_enabled', '1', 'Master switch aktif/nonaktifkan notifikasi WhatsApp', NOW(), NOW()),
(2, 'provider', 'fonnte', 'Provider WhatsApp (fonnte, mock, cloud_api)', NOW(), NOW()),
(3, 'fonnte_token', 'your_fonnte_token_here', 'API Token Fonnte (Masukkan Token Akun Anda)', NOW(), NOW()),
(4, 'notify_registration_enabled', '1', 'Notifikasi saat pendaftaran berhasil', NOW(), NOW()),
(5, 'notify_status_change_enabled', '1', 'Notifikasi saat status pendaftaran berubah', NOW(), NOW()),
(6, 'notify_reminder_h1_enabled', '1', 'Pengingat kunjungan H-1 hari sebelum layanan', NOW(), NOW()),
(7, 'notify_reminder_h2_enabled', '1', 'Pengingat kunjungan H-2 jam sebelum layanan', NOW(), NOW()),
(8, 'notify_schedule_change_enabled', '1', 'Notifikasi saat jadwal poli/dokter berubah', NOW(), NOW()),
(9, 'notify_cancellation_enabled', '1', 'Notifikasi saat pendaftaran dibatalkan', NOW(), NOW()),
(10, 'notify_announcement_enabled', '1', 'Notifikasi broadcast pengumuman resmi admin', NOW(), NOW()),
(11, 'reminder_h1_time', '08:00', 'Jam pengiriman pengingat H-1 (format HH:mm WIB)', NOW(), NOW()),
(12, 'reminder_h2_offset_hours', '2', 'Offset jam pengingat sebelum layanan dimulai (jam)', NOW(), NOW()),
(13, 'timezone', 'Asia/Jakarta', 'Zona waktu sistem notifikasi', NOW(), NOW()),
(14, 'template_registration', 'Halo {nama_pasien}, pendaftaran Anda di {nama_poli} Puskesmas Astanajapura BERHASIL. No Antrean: {nomor_antrean}. Kode Booking: {kode_pendaftaran}. Jadwal: {tanggal_kunjungan} {jam_layanan}. Harap hadir 15 menit sebelum layanan.', 'Template notifikasi pendaftaran berhasil & nomor antrean terbit', NOW(), NOW()),
(15, 'template_status_change', 'Halo {nama_pasien}, status antrean Anda di {nama_poli} telah diperbarui menjadi: *{status_baru}*. Catatan: {catatan}', 'Template notifikasi pembaruan status pendaftaran', NOW(), NOW()),
(16, 'template_reminder_h1', 'Halo {nama_pasien}, ini pengingat resmi kunjungan BESOK di Puskesmas Astanajapura untuk poli {nama_poli}. No Antrean: {nomor_antrean}.', 'Template pengingat kunjungan H-1 hari', NOW(), NOW()),
(17, 'template_reminder_h2', 'Halo {nama_pasien}, antrean Anda di {nama_poli} akan dimulai dalam 2 JAM ke depan. Harap segera hadir di ruang tunggu.', 'Template pengingat kunjungan H-2 jam', NOW(), NOW()),
(18, 'template_schedule_change', 'Halo {nama_pasien}, terdapat perubahan jadwal pada poli {nama_poli}. Jadwal baru: {jadwal_baru}. Alasan: {alasan}', 'Template notifikasi perubahan jadwal dokter/layanan', NOW(), NOW()),
(19, 'template_cancellation', 'Halo {nama_pasien}, pendaftaran antrean Anda di {nama_poli} (Kode: {kode_pendaftaran}) telah DIBATALKAN. Alasan: {catatan}', 'Template notifikasi pembatalan antrean', NOW(), NOW()),
(20, 'template_announcement', 'Halo {nama_pasien}, pengumuman resmi dari Puskesmas Astanajapura: *{judul_pengumuman}*. {isi_pengumuman}. Info: {tautan}', 'Template siaran pengumuman penting admin', NOW(), NOW()),
(21, 'notify_ticket_enabled', '1', 'Notifikasi penerbitan tiket dan kode antrean', NOW(), NOW());

SET FOREIGN_KEY_CHECKS = 1;
-- ============================================================================
-- End of SmartHealthy Database Export
-- ============================================================================