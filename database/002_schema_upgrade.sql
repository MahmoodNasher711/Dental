-- ============================================================================
-- DENTAL Management System — Phase 2 Database Modernization (UP Migration)
-- Target Engine: MySQL 8.4 (InnoDB, utf8mb4_general_ci)
-- Data Preservation Guarantee: Preserves 100% of existing rows and history.
-- ============================================================================

-- 1. Fix zero-dates in users safely under MySQL 8.4 and enforce email uniqueness
ALTER TABLE `users`
  MODIFY COLUMN `add_date` DATETIME NULL DEFAULT CURRENT_TIMESTAMP;

UPDATE `users`
SET `add_date` = NULL
WHERE CAST(`add_date` AS CHAR) = '0000-00-00 00:00:00';

ALTER TABLE `users`
  ADD UNIQUE INDEX `idx_users_email` (`email`);

-- 2. Modernize patients table: string phone_number, optional user_id FK, nullable ppassword
--    Preserves UNIQUE KEY email (patient_email) to enforce 1 patient record per person.
ALTER TABLE `patients`
  ADD COLUMN `user_id` INT NULL DEFAULT NULL AFTER `patient_id`,
  MODIFY COLUMN `phone_number` VARCHAR(20) NOT NULL,
  MODIFY COLUMN `ppassword` VARCHAR(255) NULL DEFAULT NULL,
  ADD COLUMN `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  ADD COLUMN `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  ADD INDEX `idx_patients_user_id` (`user_id`),
  ADD CONSTRAINT `fk_patients_user_id`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
    ON DELETE SET NULL ON UPDATE CASCADE;

-- Reconcile existing patients to users only where emails match cleanly
UPDATE `patients` p
INNER JOIN `users` u ON p.`patient_email` = u.`email`
SET p.`user_id` = u.`id`
WHERE p.`user_id` IS NULL;

-- 3. Enhance dentists table: specialization & active status (preserving all profiles & photos)
ALTER TABLE `dentists`
  ADD COLUMN `specialization` VARCHAR(255) NULL DEFAULT NULL AFTER `dentist_email`,
  ADD COLUMN `is_active` TINYINT(1) NOT NULL DEFAULT 1 AFTER `image`;

UPDATE `dentists` SET `specialization` = 'Cosmetic Dentistry' WHERE `dentist_id` = 1 AND `specialization` IS NULL;
UPDATE `dentists` SET `specialization` = 'Orthodontics'       WHERE `dentist_id` = 2 AND `specialization` IS NULL;
UPDATE `dentists` SET `specialization` = 'Family Dentistry'   WHERE `dentist_id` = 3 AND `specialization` IS NULL;

-- 4. Enhance services table: slot duration, description & active flag
ALTER TABLE `services`
  ADD COLUMN `description` TEXT NULL DEFAULT NULL AFTER `service_price`,
  ADD COLUMN `duration_minutes` INT NOT NULL DEFAULT 30 AFTER `description`,
  ADD COLUMN `is_active` TINYINT(1) NOT NULL DEFAULT 1 AFTER `duration_minutes`;

-- 5. Modernize appointments table:
--    - Convert appointment_date DATETIME -> DATE
--    - Decouple appointment_time from ON UPDATE CURRENT_TIMESTAMP -> TIME NOT NULL
--    - Normalize services VARCHAR -> service_id INT with Foreign Key
--    - Add duration_minutes, end_time, notes, created_at, updated_at
--    - Add concurrency-safe generated column active_slot_key with UNIQUE index
ALTER TABLE `appointments`
  MODIFY COLUMN `appointment_date` DATE NOT NULL,
  MODIFY COLUMN `appointment_time` TIME NOT NULL,
  MODIFY COLUMN `appointment_status` VARCHAR(50) NULL DEFAULT 'Pending',
  ADD COLUMN `duration_minutes` INT NOT NULL DEFAULT 30 AFTER `appointment_time`,
  ADD COLUMN `end_time` TIME NULL DEFAULT NULL AFTER `duration_minutes`,
  ADD COLUMN `notes` TEXT NULL DEFAULT NULL AFTER `services`,
  ADD COLUMN `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  ADD COLUMN `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- Backfill end_time for existing records based on appointment_time + 30 min
UPDATE `appointments`
SET `end_time` = ADDTIME(`appointment_time`, '00:30:00')
WHERE `end_time` IS NULL;

ALTER TABLE `appointments`
  MODIFY COLUMN `end_time` TIME NOT NULL;

-- Normalize services column to integer service_id
ALTER TABLE `appointments`
  CHANGE COLUMN `services` `service_id` INT NOT NULL,
  ADD INDEX `idx_appointments_service_id` (`service_id`),
  ADD CONSTRAINT `fk_appointments_service_id`
    FOREIGN KEY (`service_id`) REFERENCES `services` (`service_id`)
    ON DELETE RESTRICT ON UPDATE CASCADE;

-- Add concurrency-safe active slot uniqueness + overlap lookup index
ALTER TABLE `appointments`
  ADD COLUMN `active_slot_key` VARCHAR(64) GENERATED ALWAYS AS (
    IF(
      COALESCE(`appointment_status`, 'Pending') IN ('Cancelled', 'Rejected'),
      NULL,
      CONCAT(`dentist_id`, '_', DATE_FORMAT(`appointment_date`, '%Y-%m-%d'), '_', TIME_FORMAT(`appointment_time`, '%H:%i:%s'))
    )
  ) VIRTUAL,
  ADD UNIQUE INDEX `idx_unique_active_slot` (`active_slot_key`),
  ADD INDEX `idx_appointments_schedule_lookup` (`dentist_id`, `appointment_date`, `appointment_time`, `end_time`);

-- 6. Enhance contact_us table: read tracking & submission timestamp
ALTER TABLE `contact_us`
  ADD COLUMN `is_read` TINYINT(1) NOT NULL DEFAULT 0 AFTER `cont_user_text`,
  ADD COLUMN `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `is_read`;

-- 7. Enhance about_us table: optional Arabic narrative column
ALTER TABLE `about_us`
  ADD COLUMN `about_text_ar` TEXT NULL DEFAULT NULL AFTER `about_text`;

-- 8. Create supporting security, audit, and clinic configuration tables
CREATE TABLE IF NOT EXISTS `login_attempts` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `ip_address` VARCHAR(45) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `attempted_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_login_attempts_lookup` (`ip_address`, `email`, `attempted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `audit_logs` (
  `log_id` INT NOT NULL AUTO_INCREMENT,
  `user_id` INT NULL DEFAULT NULL,
  `action` VARCHAR(100) NOT NULL,
  `details` TEXT NULL,
  `ip_address` VARCHAR(45) NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`log_id`),
  INDEX `idx_audit_user` (`user_id`),
  INDEX `idx_audit_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `clinic_settings` (
  `setting_key` VARCHAR(100) NOT NULL,
  `setting_value` TEXT NULL,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Seed neutral operational defaults (unverified commercial contact details remain empty for owner input)
INSERT IGNORE INTO `clinic_settings` (`setting_key`, `setting_value`) VALUES
  ('clinic_name', 'DENTAL'),
  ('slot_duration_minutes', '30'),
  ('work_hours_start', '09:00'),
  ('work_hours_end', '17:00'),
  ('contact_phone', ''),
  ('contact_email', ''),
  ('clinic_address_en', ''),
  ('clinic_address_ar', ''),
  ('operating_hours_en', ''),
  ('operating_hours_ar', '');
