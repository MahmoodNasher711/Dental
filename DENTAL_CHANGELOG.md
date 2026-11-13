# DENTAL — SYSTEM CHANGELOG

## [2.0.0] — 2026-11-12 — Complete Security Remediation, Database Modernization & Luxury Redesign

### Security Added & Remediated
- **Relocated Public Database Dump**: Moved `appointment_system.sql` outside the web root to `D:\wamp64\Dental_backups\legacy_webroot_appointment_system.sql`.
- **Upload Defense-in-Depth (`includes/upload.php`)**: Eliminated arbitrary file upload / RCE by enforcing 2MB caps, extension allowlists, double-extension rejection, `finfo` MIME validation, `getimagesize()`, random hex naming, GD image stream re-encoding, and Apache `.htaccess` script denial.
- **Centralized RBAC Guards (`includes/auth.php`)**: Eliminated unauthenticated admin access and privilege escalation via `auth_require_login()` and `auth_require_admin()` with `exit;` termination.
- **SQL Injection Elimination**: Replaced all legacy mysqli/PDO string concatenations with strict PDO prepared statements (`ATTR_EMULATE_PREPARES = false`).
- **CSRF & XSS Protections (`includes/security.php`)**: Enforced 256-bit anti-CSRF tokens on every state-changing POST request; converted GET deletions/logout to POST; escaped all dynamic outputs with `e()`.
- **Password & Session Hardening**: Removed hidden DOM password hash leak; enforced `password_verify()` + `password_hash()`; added `session_regenerate_id(true)`, `HttpOnly`, `SameSite=Lax`, environment-aware `Secure` cookies, idle timeouts, and DB-backed login rate limiting (`login_attempts`).
- **Patient Record Isolation**: Scoped patient views and cancellation endpoints strictly to `user_id = :session_user_id` with audit logging (`audit_logs`).

### Database Changes (`database/002_schema_upgrade.sql`)
- Altered `patients.phone_number` from `INT(11)` to `VARCHAR(20)` to preserve international prefixes and leading zeros.
- Added nullable `patients.user_id` FK (`ON DELETE SET NULL`) preserving `patient_email` uniqueness for recurring 1:N appointments.
- Converted `appointments.appointment_date` to `DATE` and `appointments.appointment_time` from auto-updating `TIMESTAMP` to `TIME NOT NULL`.
- Normalized `appointments.services` to `service_id INT` with foreign key to `services(service_id)`.
- Added `duration_minutes`, `end_time`, and `VIRTUAL` generated column `active_slot_key` with `UNIQUE INDEX idx_unique_active_slot` for engine-level concurrency protection.
- Added `specialization` and `is_active` to `dentists`; added `description`, `duration_minutes`, and `is_active` to `services`; added `is_read` and `created_at` to `contact_us`; added `about_text_ar` to `about_us`.
- Created supporting tables `login_attempts`, `audit_logs`, and `clinic_settings`.

### Frontend, Design & Bilingual Localization
- **Luxury Design System (`assets/css/variables.css`, `assets/css/luxury.css`)**: Implemented Deep Emerald (`#123D36`), Forest Green (`#1B5146`), Champagne Gold (`#C6A46A`), Warm Ivory (`#FAF8F3`), and Charcoal (`#252927`) palette with Bootstrap 5.3.3 LTR & RTL support.
- **Dual Typography**: Paired `Cormorant Garamond` + `Plus Jakarta Sans` (EN) with `Tajawal` + `Cairo` (AR).
- **Bilingual Engine (`includes/localization.php`, `languages/en.php`, `languages/ar.php`)**: English default (`LTR`) with persistent one-click toggle to Arabic (`RTL`).
- **Complete Page & Portal Reconstruction**:
  - Rebuilt public pages: `index.php`, `about.php`, `services.php`, `doctors.php`, `dentist_info.php`, `contact.php`, `booking.php`, `booking_successfully.php`.
  - Built Patient Concierge Portal: `patient/index.php`, `patient/appointments.php`, `patient/profile.php`, `patient/security.php`.
  - Built Executive Admin Platform: `admin/index.php`, `admin/appointments.php`, `admin/patients.php`, `admin/dentists.php`, `admin/services.php`, `admin/messages.php`, `admin/users.php`, `admin/settings.php`.
  - Built real-time slot API (`api_slots.php`) and transactional concurrency-locked booking engine (`modules/appointments/booking_service.php`).
