# DENTAL — DATABASE MIGRATION & DATA INTEGRITY REPORT

**Database:** `appointment_system` (MySQL 8.4.7 / InnoDB / `utf8mb4_general_ci`)  
**Isolated Test Database:** `appointment_system_test`  
**Migration Scripts:** `database/002_schema_upgrade.sql` (UP), `database/002_schema_rollback.sql` (DOWN)  

---

## 1. Pre-Migration Backup & Isolation Protocol

1. **Original Phase 0 Baseline Preserved**:
   - `D:\wamp64\Dental_backups\Dental_backup_20261110_194253.zip` (SHA-256 verified)
   - `D:\wamp64\Dental_backups\appointment_system_backup_20261110_194253.sql` (SHA-256 verified)
2. **Pre-Execution Snapshot Created**:
   - `D:\wamp64\Dental_backups\Dental_pre_execution_20261111_193059.zip` (5,205,377 bytes)
   - `D:\wamp64\Dental_backups\appointment_system_pre_execution_20261111_193059.sql` (21,414 bytes)
3. **Isolated Test Cycle Verified**:
   - Restored backup into `appointment_system_test`.
   - Executed `002_schema_upgrade.sql` ➔ Verified `0` errors.
   - Executed `002_schema_rollback.sql` ➔ Verified clean schema reversion.
   - Re-executed `002_schema_upgrade.sql` ➔ Verified repeatable UP migration.
   - Tested 6 parallel concurrent appointment booking collisions against `appointment_system_test` before touching the live database.

---

## 2. Schema Modernization Summary

| Table | Column / Constraint Change | Technical Purpose | Data Preservation Result |
| :--- | :--- | :--- | :--- |
| **`users`** | `add_date` converted to `DATETIME NULL DEFAULT CURRENT_TIMESTAMP`; zero-dates `'0000-00-00 00:00:00'` normalized to `NULL`; added `UNIQUE INDEX idx_users_email` | MySQL 8.4 strict mode compatibility; prevents duplicate user email registration. | 2 existing users preserved (`admin@gmail.com`, `nashermahmood@gmail.com`) with Bcrypt hashes intact. |
| **`patients`** | Added nullable `user_id` FK (`ON DELETE SET NULL ON UPDATE CASCADE`); changed `phone_number` from `INT(11)` to `VARCHAR(20)`; changed `ppassword` to nullable `VARCHAR(255)`; **preserved `UNIQUE KEY email`**; added audit timestamps. | Eliminates 32-bit integer phone clamping; enables optional patient-user linking while supporting guest patients and recurring bookings via single `patient_id`. | 1 existing patient (`patient_id = 32`, `mahmood`) preserved without duplication. |
| **`dentists`** | Added `specialization VARCHAR(255)` and `is_active TINYINT(1) DEFAULT 1`. | Supports medical specialization badges and safe archival without breaking historical appointments. | All 3 dentists (`Sara'a`, `BASSAM`, `Ali`) and portraits (`1.jpg`, `2.jpg`, `4.jpg`) preserved. |
| **`services`** | Added `description TEXT`, `duration_minutes INT DEFAULT 30`, `is_active TINYINT(1) DEFAULT 1`. | Enables duration-based overlap prevention and service catalog management. | Both services (`cleaning 14$`, `surgery 50$`) preserved. |
| **`appointments`** | Converted `appointment_date` to `DATE`; converted `appointment_time` from auto-updating `TIMESTAMP` to `TIME NOT NULL`; added `duration_minutes`, `end_time`, `notes`, `created_at`, `updated_at`; normalized `services` to `service_id INT` with FK; added `VIRTUAL` generated column `active_slot_key` with `UNIQUE INDEX idx_unique_active_slot`. | Prevents clock overwrite bugs; links appointments cleanly to `services`; enforces database-level slot uniqueness while freeing cancelled/rejected slots (`NULL`). | Historical appointment `#18` preserved (`2024-02-14`, `13:20:17`, service `2`, `status = NULL`). |
| **`contact_us`** | Added `is_read TINYINT(1) DEFAULT 0` and `created_at`. | Powers the administrative inquiry inbox. | Existing table intact (0 rows). |
| **`about_us`** | Added `about_text_ar TEXT NULL`. | Supports editable bilingual clinic story. | Original English clinic story (`about_id = 3`) preserved intact. |
| **New Tables** | Created `login_attempts`, `audit_logs`, and `clinic_settings`. | Supports brute-force rate limiting, security audit trails, and clinic configuration. | Clean initialization without synthetic fake contact details. |

---

## 3. MySQL 8.4 Generated Column Engineering Finding

During test migration validation on `appointment_system_test`, MySQL 8.4 InnoDB rejected a `STORED` generated column on `appointments` (`ERROR 1215`) because `dentist_id` participates in a cascading foreign key (`ON UPDATE CASCADE ON DELETE CASCADE`). Converting `active_slot_key` to a **`VIRTUAL` generated column** with a `UNIQUE INDEX` resolved the MySQL 8.4 constraint cleanly, providing identical atomic slot collision blocking (`ERROR 1062 Duplicate entry`) without storing redundant disk bytes.

---

## 4. Before & After Record Count Verification (Live `appointment_system`)

| Table | Before Migration | After Migration | Status |
| :--- | :---: | :---: | :---: |
| `about_us` | 1 | 1 | **100% PRESERVED** |
| `appointments` | 1 | 1 | **100% PRESERVED** |
| `contact_us` | 0 | 0 | **100% PRESERVED** |
| `dentists` | 3 | 3 | **100% PRESERVED** |
| `patients` | 1 | 1 | **100% PRESERVED** |
| `services` | 2 | 2 | **100% PRESERVED** |
| `users` | 2 | 2 | **100% PRESERVED** |
