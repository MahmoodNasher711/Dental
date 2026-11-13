-- ============================================================================
-- DENTAL Management System — Rollback 003: Bilingual Content Rollback
-- Target MySQL: 8.4+ / InnoDB / utf8mb4
-- Reverts Migration 003 safely while preserving data in primary columns
-- ============================================================================

SET FOREIGN_KEY_CHECKS = 0;

-- 1. ROLLBACK DENTISTS TABLE
ALTER TABLE `dentists`
    DROP COLUMN `dentist_name_ar`,
    DROP COLUMN `specialization_ar`,
    DROP COLUMN `dentist_brief_ar`;

-- 2. ROLLBACK SERVICES TABLE
ALTER TABLE `services`
    DROP COLUMN `service_name_en`,
    DROP COLUMN `service_name_ar`,
    DROP COLUMN `service_description_en`,
    DROP COLUMN `service_description_ar`;

SET FOREIGN_KEY_CHECKS = 1;
