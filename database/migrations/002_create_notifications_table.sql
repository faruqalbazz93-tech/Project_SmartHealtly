-- ==========================================================
-- Database Migration: Notifications & Deduplication Tables
-- SmartHealthy Puskesmas Astanajapura
-- ==========================================================

USE `smarthealthy`;

SET FOREIGN_KEY_CHECKS = 0;

-- 18. Tabel notifications (Notifikasi Pasien & Pengguna)
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `type` VARCHAR(50) NOT NULL COMMENT 'registration, schedule, account, education, announcement',
  `title` VARCHAR(255) NOT NULL,
  `message` TEXT NOT NULL,
  `target_url` VARCHAR(255) NULL,
  `is_read` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `read_at` TIMESTAMP NULL DEFAULT NULL,
  CONSTRAINT `fk_notifications_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  INDEX `idx_notif_user_read_created` (`user_id`, `is_read`, `created_at`),
  INDEX `idx_notif_type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 19. Tabel notification_dedup (Pencegahan Pengingat & Notifikasi Ganda)
CREATE TABLE IF NOT EXISTS `notification_dedup` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `dedup_key` VARCHAR(150) NOT NULL UNIQUE,
  `channel` VARCHAR(30) NOT NULL DEFAULT 'app',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_notif_dedup_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  INDEX `idx_dedup_key` (`dedup_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
