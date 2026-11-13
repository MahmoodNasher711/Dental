# DENTAL MANAGEMENT SYSTEM — BILINGUAL DATABASE ARCHITECTURE & MIGRATION REPORT

**PROJECT:** DENTAL — Luxury Dental Clinic Experience  
**SYSTEM VERSION:** 2.1 (Bilingual Architecture Edition)  
**DATABASE ENGINE:** MySQL 8.4.7 / InnoDB / utf8mb4  
**DATE OF EXECUTION:** 2026-11-13  
**AUDIT & MIGRATION STATUS:** FULLY APPLIED & VERIFIED (Test DB & Live DB)

---

## 1. EXECUTIVE SUMMARY

To fulfill the master directive requiring **ZERO untranslated user-facing content** and complete dynamic localization across Arabic (RTL) and English (LTR), the database schema was upgraded through Migration `003_bilingual_upgrade.sql`.

Prior to this migration:
- The `services` table stored service names and descriptions in English only (`service_name`, `description`).
- The `dentists` table stored specialties and biographies in English only (`specialization`, `dentist_brief`).
- The `clinic_settings` and `about_us` tables had already received initial bilingual keys in Phase 2, but service and clinical content lacked schema-level bilingual columns.

The migration successfully introduced dedicated, indexed bilingual fields for clinical services and dental specialists, preserved all existing primary keys, foreign keys, and pricing data, and synchronized legacy columns to prevent regressions in existing code paths.

---

## 2. DATABASE SCHEMA TRANSFORMATIONS

### 2.1 `services` Table Upgrade

#### Pre-Migration Schema
```sql
CREATE TABLE `services` (
  `service_id` int NOT NULL AUTO_INCREMENT,
  `service_name` varchar(255) NOT NULL,
  `service_price` text NOT NULL,
  `description` text,
  `duration_minutes` int NOT NULL DEFAULT '30',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`service_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

#### Post-Migration Schema
```sql
CREATE TABLE `services` (
  `service_id` int NOT NULL AUTO_INCREMENT,
  `service_name` varchar(255) NOT NULL,
  `service_name_en` varchar(255) DEFAULT NULL,
  `service_name_ar` varchar(255) DEFAULT NULL,
  `service_price` text NOT NULL,
  `description` text,
  `service_description_en` text,
  `service_description_ar` text,
  `duration_minutes` int NOT NULL DEFAULT '30',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`service_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

#### Field Specifications:
| Column | Type | Nullable | Purpose | Text Direction |
|---|---|:---:|---|:---:|
| `service_id` | `INT(11)` | NO | Primary Key (preserved) | N/A |
| `service_name` | `VARCHAR(255)` | NO | Legacy compatibility column (synced with EN) | LTR |
| `service_name_en` | `VARCHAR(255)` | YES | Primary English service title | LTR |
| `service_name_ar` | `VARCHAR(255)` | YES | Verified Arabic service title | RTL |
| `service_price` | `TEXT` | NO | Fee / Price string (preserved intact) | Neutral |
| `description` | `TEXT` | YES | Legacy compatibility description | LTR |
| `service_description_en` | `TEXT` | YES | Detailed English clinical description | LTR |
| `service_description_ar` | `TEXT` | YES | Detailed Arabic clinical description | RTL |
| `duration_minutes` | `INT(11)` | NO | Appointment slot duration (default 30) | Neutral |
| `is_active` | `TINYINT(1)` | NO | Publication flag (1=active, 0=inactive) | Neutral |

---

### 2.2 `dentists` Table Upgrade

#### Post-Migration Schema
```sql
CREATE TABLE `dentists` (
  `dentist_id` int NOT NULL AUTO_INCREMENT,
  `dentist_name` varchar(255) NOT NULL,
  `dentist_name_ar` varchar(255) DEFAULT NULL,
  `dentist_age` int NOT NULL,
  `dentist_email` varchar(255) NOT NULL,
  `specialization` varchar(255) DEFAULT NULL,
  `specialization_ar` varchar(255) DEFAULT NULL,
  `dentist_brief` text NOT NULL,
  `dentist_brief_ar` text,
  `image` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`dentist_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

---

## 3. MIGRATION EXECUTION & VERIFICATION PROTOCOL

### 3.1 Two-Phase Execution Sequence
1. **Test Environment Validation (`appointment_system_test`):**
   - Applied `database/003_bilingual_upgrade.sql`.
   - Verified column creation and data backfilling.
   - Executed `database/003_bilingual_rollback.sql` and verified full reversibility with zero data loss.
   - Re-applied upgrade cleanly.
2. **Production Database Application (`appointment_system`):**
   - Executed `003_bilingual_upgrade.sql` against the live MySQL 8.4.7 database.
   - Verified table row integrity and foreign key constraints on `appointments(service_id)`.

### 3.2 Live Database Content Verification

#### Verified `services` Table Rows:
```
+------------+----------------------------+---------------------------------+---------------+------------------+-----------+
| service_id | service_name_en            | service_name_ar                 | service_price | duration_minutes | is_active |
+------------+----------------------------+---------------------------------+---------------+------------------+-----------+
|          1 | Teeth Cleaning & Polishing | تنظيف وتلميع الأسنان            | 5000 ر.ي      |               30 |         1 |
|          2 | Oral & Dental Surgery      | جراحة الفم والأسنان             | 15000 ر.ي     |               30 |         1 |
+------------+----------------------------+---------------------------------+---------------+------------------+-----------+
```

#### Verified `dentists` Table Rows:
```
+------------+--------------+-----------------+--------------------+--------------------------------+
| dentist_id | dentist_name | dentist_name_ar | specialization     | specialization_ar              |
+------------+--------------+-----------------+--------------------+--------------------------------+
|          1 | Sara'a       | ساره            | Cosmetic Dentistry | طب الأسنان التجميلي            |
|          2 | BASSAM       | بسام            | Orthodontics       | تقويم الأسنان                  |
|          3 | Ali          | علي             | Family Dentistry   | طب أسنان الأسرة                |
+------------+--------------+-----------------+--------------------+--------------------------------+
```

#### Foreign Key & Relationship Integrity:
- `appointments` table has 2 active appointments referencing `service_id`:
  - Appointment #18: `service_id = 2` (`Oral & Dental Surgery` / `جراحة الفم والأسنان`) — **Intact**
  - Appointment #19: `service_id = 1` (`Teeth Cleaning & Polishing` / `تنظيف وتلميع الأسنان`) — **Intact**
- Zero foreign key violations or data truncation warnings occurred during migration.

---

## 4. LOCALIZATION HELPER & FALLBACK ARCHITECTURE

The application database layer is decoupled from template presentation through centralized localization functions defined in `includes/localization.php`:

```php
function service_name(mixed $service): string
{
    global $CURRENT_LANG;
    if (!$service) return '';
    $row = (array)$service;
    if ($CURRENT_LANG === 'ar') {
        if (!empty($row['service_name_ar'])) {
            return (string)$row['service_name_ar'];
        }
        return (string)($row['service_name_en'] ?? $row['service_name'] ?? '');
    }
    return (string)($row['service_name_en'] ?? $row['service_name'] ?? $row['service_name_ar'] ?? '');
}
```

### Fallback Guarantee:
1. **Primary Resolution:** When Arabic is active, the database returns `service_name_ar`.
2. **Missing Translation Graceful Fallback:** If `service_name_ar` is NULL or empty, the engine gracefully falls back to `service_name_en` or `service_name`, preventing blanks or fatal errors.
3. **Administrative Flagging:** In the admin interface (`admin/services.php`), services with missing Arabic translations display a distinctive warning badge (`Needs Arabic` / `بحاجة لترجمة`) alerting the clinic administrator to complete the localized copy.

---

## 5. REVERSIBILITY & ROLLBACK ASSURANCE

The migration includes a verified rollback script located at `database/003_bilingual_rollback.sql`:
- Safely removes `service_name_en`, `service_name_ar`, `service_description_en`, and `service_description_ar`.
- Reverts `dentists` table columns.
- Preserves primary keys, appointments, and original records.
- Verified on MySQL 8.4.7 with zero errors.
