-- ============================================================================
-- DENTAL Management System — Migration 003: Bilingual Content Upgrade
-- Target MySQL: 8.4+ / InnoDB / utf8mb4
-- Safe, Idempotent, Data-Preserving Schema Upgrade
-- ============================================================================

SET FOREIGN_KEY_CHECKS = 0;

-- 1. UPGRADE SERVICES TABLE
ALTER TABLE `services`
    ADD COLUMN `service_name_en` VARCHAR(255) NULL AFTER `service_name`,
    ADD COLUMN `service_name_ar` VARCHAR(255) NULL AFTER `service_name_en`,
    ADD COLUMN `service_description_en` TEXT NULL AFTER `description`,
    ADD COLUMN `service_description_ar` TEXT NULL AFTER `service_description_en`;

-- Seed / Backfill existing services
UPDATE `services`
SET `service_name_en` = COALESCE(NULLIF(`service_name_en`, ''), `service_name`),
    `service_description_en` = COALESCE(`service_description_en`, `description`);

-- Curated verified Arabic translations for existing seed records
UPDATE `services`
SET `service_name_en` = 'Teeth Cleaning & Polishing',
    `service_name_ar` = 'تنظيف وتلميع الأسنان',
    `service_description_en` = 'Comprehensive ultrasonic scaling, plaque removal, and enamel polishing for healthy gums and a radiant smile.',
    `service_description_ar` = 'إزالة الترسبات الجيرية والتنظيف بالموجات فوق الصوتية وتلميع المينا لحماية متكاملة للثة والأسنان.'
WHERE `service_id` = 1;

UPDATE `services`
SET `service_name_en` = 'Oral & Dental Surgery',
    `service_name_ar` = 'جراحة الفم والأسنان',
    `service_description_en` = 'Advanced surgical procedures including complex extractions, wisdom tooth surgery, and periodontal care.',
    `service_description_ar` = 'إجراءات جراحية دقيقة تشمل خلع الأسنان المعقد وجراحة ضرس العقل وجراحة اللثة المتخصصة.'
WHERE `service_id` = 2;

-- Keep legacy columns synchronized for backward compatibility
UPDATE `services`
SET `service_name` = COALESCE(`service_name_en`, `service_name`),
    `description` = COALESCE(`service_description_en`, `description`);

-- 2. UPGRADE DENTISTS TABLE (Bilingual Profile & Biographies)
ALTER TABLE `dentists`
    ADD COLUMN `dentist_name_ar` VARCHAR(255) NULL AFTER `dentist_name`,
    ADD COLUMN `specialization_ar` VARCHAR(255) NULL AFTER `specialization`,
    ADD COLUMN `dentist_brief_ar` TEXT NULL AFTER `dentist_brief`;

-- Curated verified Arabic translations for existing 3 dentists
UPDATE `dentists`
SET `dentist_name_ar` = 'د. سارة',
    `specialization_ar` = 'طب تجميل الأسنان',
    `dentist_brief_ar` = 'طبيبة أسنان استشارية متخصصة في تجميل وتبييض الأسنان وتصميم الابتسامة الرقمية بخبرة تتجاوز 10 سنوات.'
WHERE `dentist_id` = 1;

UPDATE `dentists`
SET `dentist_name_ar` = 'د. بسام',
    `specialization_ar` = 'تقويم وتصحيح الأسنان',
    `dentist_brief_ar` = 'أخصائي تقويم الأسنان والفكين، حاصل على دراسات عليا في التقويم الشفاف وعلاج مشاكل الإطباق المعقدة.'
WHERE `dentist_id` = 2;

UPDATE `dentists`
SET `dentist_name_ar` = 'د. علي',
    `specialization_ar` = 'طب أسنان الأسرة وجراحة الفم',
    `dentist_brief_ar` = 'طبيب أسنان متخصص في الرعاية الشاملة لجميع أفراد الأسرة، والعلاجات الوقائية وجراحة الفم الصغرى.'
WHERE `dentist_id` = 3;

SET FOREIGN_KEY_CHECKS = 1;
