-- ============================================================================
-- DENTAL Management System — Phase 2 Database Rollback (DOWN Migration)
-- WARNING: Intended for pre-production schema reversion only.
--          Reverting phone_number to INT(11) or appointment_time to TIMESTAMP
--          after production writes may truncate non-numeric phones or times.
-- ============================================================================

DROP TABLE IF EXISTS `clinic_settings`;
DROP TABLE IF EXISTS `audit_logs`;
DROP TABLE IF EXISTS `login_attempts`;

ALTER TABLE `about_us`
  DROP COLUMN `about_text_ar`;

ALTER TABLE `contact_us`
  DROP COLUMN `created_at`,
  DROP COLUMN `is_read`;

ALTER TABLE `appointments`
  DROP INDEX `idx_appointments_schedule_lookup`,
  DROP INDEX `idx_unique_active_slot`,
  DROP COLUMN `active_slot_key`,
  DROP FOREIGN KEY `fk_appointments_service_id`,
  DROP INDEX `idx_appointments_service_id`,
  CHANGE COLUMN `service_id` `services` VARCHAR(220) NOT NULL,
  DROP COLUMN `updated_at`,
  DROP COLUMN `created_at`,
  DROP COLUMN `notes`,
  DROP COLUMN `end_time`,
  DROP COLUMN `duration_minutes`,
  MODIFY COLUMN `appointment_status` VARCHAR(255) NULL DEFAULT NULL,
  MODIFY COLUMN `appointment_date` DATETIME NOT NULL,
  MODIFY COLUMN `appointment_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

ALTER TABLE `services`
  DROP COLUMN `is_active`,
  DROP COLUMN `duration_minutes`,
  DROP COLUMN `description`;

ALTER TABLE `dentists`
  DROP COLUMN `is_active`,
  DROP COLUMN `specialization`;

UPDATE `patients` SET `ppassword` = '' WHERE `ppassword` IS NULL;

ALTER TABLE `patients`
  DROP FOREIGN KEY `fk_patients_user_id`,
  DROP INDEX `idx_patients_user_id`,
  DROP COLUMN `updated_at`,
  DROP COLUMN `created_at`,
  MODIFY COLUMN `ppassword` VARCHAR(250) NOT NULL,
  MODIFY COLUMN `phone_number` INT NOT NULL,
  DROP COLUMN `user_id`;

UPDATE `users` SET `add_date` = '2024-02-12 00:00:00' WHERE `add_date` IS NULL;

ALTER TABLE `users`
  DROP INDEX `idx_users_email`,
  MODIFY COLUMN `add_date` DATETIME NOT NULL;
