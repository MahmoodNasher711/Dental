# DENTAL — DEPLOYMENT & OPERATIONS GUIDE

**Project:** DENTAL — Luxury Dental Clinic Management System  
**Target Stack:** Apache 2.4.65 (mod_fcgid / mod_headers / mod_rewrite) + PHP 8.3.28 + MySQL 8.4.7  

---

## 1. Prerequisites & Required PHP Extensions

Verify the following extensions are enabled in `php.ini`:
- `pdo_mysql` (Required for centralized PDO database access)
- `mbstring` (Required for Unicode Arabic/English string handling)
- `fileinfo` (Required for binary MIME verification during image uploads)
- `gd` (Required for image stream re-encoding and polyglot stripping)
- `openssl` / `random` (Required for cryptographic CSRF tokens and filenames)

Required Apache modules:
- `mod_headers` (HTTP security headers)
- `mod_rewrite` (Blocking external HTTP access to `includes/`, `database/`, `languages/`, `templates/`, `modules/`)

---

## 2. Local WampServer Setup & Configuration

1. **Database Configuration**:
   Database credentials are centralized in `includes/config.php`. For production deployments, update `DB_HOST`, `DB_NAME`, `DB_USER`, and `DB_PASS` with dedicated least-privilege database credentials (do not use `root` with an empty password in production).
2. **File Permissions**:
   Ensure the web server process has write permissions strictly on:
   - `assets/uploads/avatars/`
   - `assets/images/doctors/`
   All other directories (`includes/`, `database/`, `languages/`, `templates/`) should remain read-only for the web runtime user.
3. **Upload Directory Security**:
   Confirm `.htaccess` files inside `assets/uploads/`, `assets/images/doctors/`, and `login/uploaded_img/` remain intact to disable directory indexing and script execution.

---

## 3. Post-Deployment Owner Configuration Steps

Upon initial launch, sign in to the Executive Admin Platform (`http://localhost/Dental/login.php`) using an administrator account and navigate to **Clinic Settings** (`admin/settings.php`) to populate real operational details:
1. **Verified Contact Telephone**: Replaces the `[Owner verification pending]` badge in top header, contact page, and footer.
2. **Official Contact Email**: Public email address for patient inquiries.
3. **Physical Clinic Address (EN & AR)**: Street address displayed on Contact Us and Footer.
4. **Operating Hours Label (EN & AR)** & **Booking Window Start / End / Slot Interval**: Controls available appointment slots in `booking.php`.
5. **About Us Story (EN & AR)**: Optional Arabic translation of the clinic introduction.

---

## 4. Backup & Disaster Recovery Operations

- Verified baseline backups are stored outside the web root in `D:\wamp64\Dental_backups\`.
- Database schema upgrade and rollback scripts reside in:
  - `database/002_schema_upgrade.sql`
  - `database/002_schema_rollback.sql`
- Always execute a fresh `mysqldump` snapshot prior to any future upgrades.
