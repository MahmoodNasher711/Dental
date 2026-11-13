# DENTAL — Luxury Dental Clinic Management System

> Bilingual (English / Arabic) Appointment Booking, Patient Portal, and Administrative Clinic Platform Built on Modular Native PHP & MySQL

![PHP 8.3](https://img.shields.io/badge/PHP-8.3-777BB4?style=flat-square&logo=php&logoColor=white)
![MySQL 8.4](https://img.shields.io/badge/MySQL-8.4-4479A1?style=flat-square&logo=mysql&logoColor=white)
![PDO](https://img.shields.io/badge/Database-PDO%20Prepared%20Statements-0D5045?style=flat-square)
![Bootstrap 5](https://img.shields.io/badge/Frontend-Bootstrap%205-7952B3?style=flat-square&logo=bootstrap&logoColor=white)
![Languages](https://img.shields.io/badge/Languages-English%20(LTR)%20%7C%20Arabic%20(RTL)-C6A46A?style=flat-square)
![Verification](https://img.shields.io/badge/Verification-Static%20Inspection%20Only-important?style=flat-square)

**DENTAL** is a modular procedural PHP web application designed for dental clinic operations, combining an editorial public clinic website, authenticated appointment scheduling, a patient self-service portal, and a role-restricted administrative dashboard with full **English (LTR)** and **Arabic (RTL)** localization.

**Current Status:** Architecture refactoring, security hardening, role-based layout separation, and bilingual parity updates have been reviewed at source level (**`NOT RUNTIME VERIFIED — STATIC INSPECTION ONLY`**). Runtime end-to-end validation in a live WampServer / Apache / MySQL environment must be completed before production deployment.

---

## Table of Contents

- [Project Overview](#project-overview)
- [Screenshots](#screenshots)
- [Features](#features)
- [Technology Stack](#technology-stack)
- [Architecture and Directory Structure](#architecture-and-directory-structure)
- [Requirements and Installation](#requirements-and-installation)
- [Configuration and Security](#configuration-and-security)
- [User Roles](#user-roles)
- [Localization (English / Arabic)](#localization-english--arabic)
- [Security Notes](#security-notes)
- [Development Status and Verification](#development-status-and-verification)
- [License](#license)
- [Contribution and Support](#contribution-and-support)

---

## Project Overview

DENTAL serves three distinct user audiences through clear route guards and dedicated layouts:

- **Visitors (Guests):** Browse the public clinic website (clinic philosophy, dental treatments catalog with durations/fees, specialist profiles, and contact information) using the public top navigation bar in English or Arabic.
- **Registered Patients:** Sign in or register to unlock appointment slot discovery (`api_slots.php`) and authenticated booking (`booking.php`), inspect visit confirmations, track/cancel upcoming appointments, manage personal profile details/avatars, and update account passwords via a clean, full-width horizontal navbar portal (`patient/*`).
- **Administrators:** Manage appointments, patient records, dentist profiles (bilingual EN/AR), services catalog (bilingual EN/AR), contact messages, clinic operating hours/settings, and user roles inside an exclusive vertical-sidebar administrative platform (`admin/*`).

---

## Screenshots

No up-to-date, sanitized screenshots of the refactored post-authentication and role-based UI currently exist inside `docs/screenshots/`, as live runtime execution is suspended during static preparation.

- Generated clinic hero/lounge imagery and historical pre-refactor captures are intentionally **not** substituted as UI screenshots.
- The complete capture checklist, target filenames, and privacy redaction rules are documented in [docs/SCREENSHOT_REQUIREMENTS.md](docs/SCREENSHOT_REQUIREMENTS.md).

Once live runtime execution is authorized, sanitized screenshots will be placed at:

```text
docs/screenshots/
├── homepage.png
├── services.png
├── doctors.png
├── booking.png
├── patient-dashboard.png
├── admin-dashboard.png
├── admin-services.png
└── arabic-rtl.png
```

---

## Features

All features listed below are verified by source-code inspection (**pending runtime execution**):

### Public Website
- Responsive homepage (`index.php`), about story (`about.php`), treatments catalog (`services.php`), specialists roster (`doctors.php`), practitioner profile details (`dentist_info.php`), and contact form (`contact.php`).
- Dynamic clinic address, phone, email, and operating hours powered by `clinic_settings`.

### Authenticated Appointment Booking
- **Mandatory Login Gate:** Guest attempts to access `booking.php`, legacy booking redirects (`appontment.php`, `userbooking.php`), `booking_successfully.php`, or `api_slots.php` (`401 Unauthorized` JSON) are blocked and redirected to `login.php` with sanitized `?redirect=` preservation across login and registration.
- **Session-Bound Identity (Anti-IDOR):** Patient identity and email are derived strictly from the authenticated server session (`$authUser['email']`) inside `booking.php` and `modules/appointments/booking_service.php`.
- **Conflict & Duplicate Prevention:** Row-level dentist locking (`SELECT ... FOR UPDATE`), `idx_unique_active_slot` schema uniqueness, single-use session `booking_nonce`, and per-patient overlapping active slot detection prevent double bookings and duplicate POST submissions (`NOT RUNTIME VERIFIED — STATIC INSPECTION ONLY`).

### Patient Portal (`patient/*`)
- Full-width layout with horizontal top navigation (`templates/partials/navbar.php` + `templates/patient/layout_start.php`) and zero sidebar clutter.
- Patient dashboard (`patient/index.php`), appointment history and cancellation (`patient/appointments.php`), profile & avatar management (`patient/profile.php`), and password update (`patient/security.php`).

### Administrative Platform (`admin/*`)
- Exclusive Admin Sidebar layout (`templates/admin/layout_start.php`) restricted by `auth_require_admin()`.
- Dashboard KPIs (`admin/index.php`), filterable appointments (`admin/appointments.php`), patient search & editing (`admin/patients.php`), bilingual dentists manager (`admin/dentists.php`), bilingual services manager (`admin/services.php`), contact inbox (`admin/messages.php`), user role/status & password reset controls (`admin/users.php`), and clinic settings (`admin/settings.php`).
- CSRF-verified `POST` logout (`admin/logout.php`).

### Localization & Security Controls
- Symmetric **English (LTR)** and **Arabic (RTL)** dictionaries (`285` keys each in `languages/en.php` and `languages/ar.php`) plus bilingual DB helpers in `includes/localization.php`.
- Automatic Bootstrap CSS switching (`bootstrap.min.css` vs `bootstrap.rtl.min.css`).
- Password hashing (`password_hash` / `password_verify`), CSRF tokens (`csrf_verify()`), login rate limiting (`login_attempts`), audit logging (`audit_logs`), and GD image re-encoding (`includes/upload.php`).

---

## Technology Stack

| Layer | Technology / Details |
|---|---|
| **Backend** | Native Procedural PHP (targeted for PHP 8.3) |
| **Database** | MySQL (InnoDB, `utf8mb4_general_ci`, targeted for MySQL 8.4) |
| **Database Access** | PHP PDO (`ATTR_EMULATE_PREPARES => false`, parameterized prepared statements) |
| **Frontend Markup & Logic** | HTML5, CSS3, Vanilla JavaScript (`assets/js/luxury.js`) |
| **UI & Icon Framework** | Bootstrap 5 (`bootstrap.min.css` / `bootstrap.rtl.min.css`), Bootstrap Icons |
| **Localization** | Custom PHP array dictionaries (`en` / `ar`) + localized database helpers |

---

## Architecture and Directory Structure

```text
Dental/
├── admin/                  # Admin-only dashboard & CRUD modules (sidebar layout)
├── assets/
│   ├── css/                # Bootstrap 5 LTR/RTL, Bootstrap Icons, design tokens & luxury styles
│   ├── images/             # Brand SVG emblem, clinic visuals, and dentist portraits
│   ├── js/                 # Bootstrap bundle and slot-picker script (luxury.js)
│   └── uploads/            # User avatar uploads (gitignored; protected by .htaccess)
├── database/               # Incremental schema & bilingual SQL migration/rollback scripts
│   ├── 002_schema_upgrade.sql
│   ├── 002_schema_rollback.sql
│   ├── 003_bilingual_upgrade.sql
│   └── 003_bilingual_rollback.sql
├── docs/                   # Visual showcase checklist (SCREENSHOT_REQUIREMENTS.md) & future screenshots/
├── includes/               # Core infrastructure: config, PDO db, security, validation, auth, upload, i18n
├── languages/              # Symmetric translation dictionaries (en.php, ar.php)
├── modules/
│   └── appointments/       # Transactional booking and slot-availability engine (booking_service.php)
├── patient/                # Authenticated patient portal pages (full-width layout)
├── templates/
│   ├── admin/              # Admin sidebar shell (layout_start.php / layout_end.php)
│   ├── partials/           # Unified role-aware horizontal Top Navbar (navbar.php)
│   ├── patient/            # Patient portal header banner and full-width wrapper
│   └── public/             # Shared HTML <head>, navbar integration, and footer
├── .gitignore              # Excludes secrets, uploads, dumps, backups, logs, and temp clutter
├── .htaccess               # Directory listing disable, sensitive file blocking, security headers
├── README.md               # Project documentation
├── index.php               # Public homepage (plus about, services, doctors, contact, booking, auth routes)
│
└── [Legacy Compatibility Assets Retained on Disk]
    ├── css/, img/, imgs/, js.js, login/   # Pre-redesign fallback assets & route bridges
    └── DENTAL_*.md                        # Project engineering specifications, audits & changelogs
```

---

## Requirements and Installation

### Prerequisites
- **Web Server:** Apache 2.4+ with `mod_rewrite` and `mod_headers` enabled (e.g., WampServer on Windows).
- **PHP:** PHP 8.3+ with extensions verified in source code:
  - `pdo_mysql` (`includes/db.php`)
  - `gd` (`imagecreatefromjpeg` / `imagecreatefrompng` in `includes/upload.php`)
  - `fileinfo` (`new finfo(FILEINFO_MIME_TYPE)` in `includes/upload.php`)
  - `mbstring` (`mb_strlen` / `mb_substr` across `includes/validation.php`, `admin/`)
  - `session`, `json`
- **Database:** MySQL 8.4+ (`InnoDB`, `utf8mb4`).

### Database Setup Note
Inspecting `database/` confirms **incremental migration scripts** (`002_schema_upgrade.sql` and `003_bilingual_upgrade.sql`) are included in this repository, whereas the **initial baseline `appointment_system` schema SQL is not bundled in `database/`**.

1. Create the empty database in MySQL:
   ```sql
   CREATE DATABASE appointment_system CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
   ```
2. Import your baseline `appointment_system` schema (`users`, `patients`, `dentists`, `services`, `appointments`, `about_us`, `contact_us`).
3. Apply the incremental repository migrations in sequence (`NOT RUNTIME VERIFIED — STATIC INSPECTION ONLY`):
   - `database/002_schema_upgrade.sql`
   - `database/003_bilingual_upgrade.sql`

<<<<<<< HEAD
### Local Configuration Setup
1. Place the project in your Apache document root (e.g., `D:\wamp64\www\Dental` served at `/Dental`, matching default auto-detection in `includes/config.php`).
2. Copy `includes/config.example.php` to `includes/config.local.php` (gitignored):
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'appointment_system');
   define('DB_USER', 'your_database_user');
   define('DB_PASS', 'your_database_password');
   define('DB_CHARSET', 'utf8mb4');
   ```
3. Ensure the web server has write access to upload targets:
   - `assets/uploads/avatars/`
   - `assets/images/doctors/`
=======
```bash
# 1. Clone the repository
git clone https://github.com/MahmoodNasher711/Dental.git

# 2. Copy to XAMPP htdocs
cp -r just-smile-dental/ C:/xampp/htdocs/Dental
```

3. Start **Apache** and **MySQL** from XAMPP Control Panel.

4. Open **phpMyAdmin** → create database `appointment_system` → import `appointment_system.sql`.

5. If your MySQL credentials differ from `root` / no password, edit `config.php`.

6. Open `http://localhost/Dental/` in your browser.
>>>>>>> 9a9f6b1539c804f3ccdc249cfa947e342ee30874

---

## Configuration and Security

- **Credential Isolation:** `includes/config.php` automatically loads `includes/config.local.php` if present before defining fallback constants. `.gitignore` excludes `includes/config.local.php`, `.env*`, `*.key`, `*.pem`, and cookie artifacts so real credentials remain outside version control.
- **Directory & File Hardening (`.htaccess`):**
  - Disables directory indexing (`Options -Indexes`).
  - Denies HTTP access to `.sql`, `.log`, `.ini`, `.env`, `.bak`, `.sh`, `.bat`, and `.md` files.
  - Blocks direct web routing into `includes/`, `database/`, `languages/`, `templates/`, `modules/`, and `migrations/`.
  - Blocks PHP/script execution inside `assets/uploads/.htaccess`, `assets/images/doctors/.htaccess`, and `login/uploaded_img/.htaccess`.
- **Session Parameters (`includes/auth.php`):** Uses strict mode, `HttpOnly`, `SameSite=Lax`, automatic `Secure` flag under HTTPS, session regeneration on login, and a `1800`-second idle timeout (`SESSION_IDLE_TIMEOUT`).
- **Privacy Recommendation for GitHub:** Keep any repository **PRIVATE** unless historical audit markdown reports (`DENTAL_FINAL_REVIEW_REPORT.md`, `DENTAL_FINAL_IMPLEMENTATION_REPORT.md`, `DENTAL_DATABASE_MIGRATION_REPORT.md`, `DENTAL_IMPLEMENTATION_ROADMAP.md`) have their seed emails/phone records sanitized and local uploads remain excluded.

---

## User Roles

| Role | Navigation & Layout | Access & Capabilities |
|---|---|---|
| **Guest** (`auth_user() === null`) | Public Top Navbar (`Home`, `About`, `Services`, `Specialists`, `Contact`, `Language`, `Sign In`, `Register`) | Browse clinic info, services, and dentists. Redirected to `login.php` with intended URL preservation when attempting to book or query slots. |
| **Patient** (`role !== 'admin'`) | Full-width layout with horizontal Patient Top Navbar (`Book Appointment`, `My Appointments`, `My Profile`, `Account` dropdown, `Sign Out`) — **no sidebar** | Book authenticated appointments, view booking confirmation, manage/cancel personal visits, update profile/avatar, change password. |
| **Administrator** (`role === 'admin'`) | Exclusive vertical Admin Sidebar (`admin/*`) + Admin Platform shortcut on public pages | Full management of appointments, patients, bilingual dentists, bilingual services, inbox messages, users/roles, and clinic settings. Automatically routed away from `patient/*` to `admin/index.php`. |

---

## Localization (English / Arabic)

- **Default Language:** English (`en`, `dir="ltr"`).
- **Resolution Order (`includes/localization.php`):** Query parameter `?lang=en|ar` -> `$_SESSION['lang']` -> `$_COOKIE['lang']` (`dental_lang`) -> default `'en'`.
- **In-Place Switching:** `lang_switch_url()` keeps the user on their current route and query string while toggling LTR/RTL.
- **Bilingual Database Content:** After applying `database/003_bilingual_upgrade.sql` and `002_schema_upgrade.sql`, `services`, `dentists`, `about_us`, and `clinic_settings` store parallel English and Arabic content rendered through `service_name()`, `service_description()`, `dentist_name()`, `dentist_specialization()`, `dentist_bio()`, `about_us_content()`, `clinic_address()`, and `clinic_hours()`. Static UI strings reside across `285` keys in `languages/en.php` and `languages/ar.php`.

---

## Security Notes

Implemented security mechanisms observed in static review include:
- Parameterized PDO queries (`PDO::ATTR_EMULATE_PREPARES => false`).
- `password_hash()` / `password_verify()` authentication checks.
- Session CSRF protection (`csrf_field()`, `csrf_verify()`) across state-changing forms and POST logouts.
- Output escaping helper (`e()`) across templates.
- MIME, `getimagesize()`, extension whitelist, random naming, and GD image re-encoding for uploaded images (`includes/upload.php`).
- Brute-force throttling (`rate_limit_*()` via `login_attempts`) and audit event recording (`audit_logs`).

> **Disclaimer:** Do not treat this codebase as production-hardened or penetration-tested until full runtime security testing, dependency audits, HTTPS configuration, and server hardening are executed.

---

## Development Status and Verification

All recent refactoring, bilingual completion, authentication enforcement, and cleanup steps were checked through **static code inspection only** (`NOT RUNTIME VERIFIED — STATIC INSPECTION ONLY`). The following must be executed once runtime access is enabled:

1. Run `php -l` across all PHP files to confirm zero parse errors.
2. Verify database connectivity and execute `002_schema_upgrade.sql` and `003_bilingual_upgrade.sql` against a backed-up MySQL 8.4 instance.
3. End-to-end test unauthenticated booking redirects (`booking.php`, `api_slots.php`, `appontment.php`, `userbooking.php`, `booking_successfully.php`), `?redirect=` survival across `login.php` and `register.php`, and anti-IDOR email binding.
4. Test concurrent slot locking, `booking_nonce` duplicate rejection, and patient overlapping slot rejection.
5. Inspect Guest Top Navbar, Patient full-width Top Navbar, Admin Sidebar isolation, and LTR/RTL layouts across desktop and mobile viewports, then populate `docs/screenshots/`.

Detailed static reports are maintained in the repository root:
- [DENTAL_BILINGUAL_AUDIT_REPORT.md](DENTAL_BILINGUAL_AUDIT_REPORT.md)
- [DENTAL_TRANSLATION_COVERAGE_REPORT.md](DENTAL_TRANSLATION_COVERAGE_REPORT.md)
- [DENTAL_AUTHENTICATION_BOOKING_REVIEW.md](DENTAL_AUTHENTICATION_BOOKING_REVIEW.md)
- [DENTAL_ROLE_BASED_LAYOUT_REPORT.md](DENTAL_ROLE_BASED_LAYOUT_REPORT.md)
- [DENTAL_SOURCE_CODE_CHANGELOG.md](DENTAL_SOURCE_CODE_CHANGELOG.md)
- [DENTAL_CLEANUP_REPORT.md](DENTAL_CLEANUP_REPORT.md)

---

## License

License: Not yet specified.

Repository owners should choose and add an explicit license file before any public distribution.

---

## Contribution and Support

1. Fork or branch cleanly from the local repository without committing `includes/config.local.php`, `.env*`, patient uploads, or database dumps.
2. Maintain parity between `languages/en.php` and `languages/ar.php` for any UI additions, and test both `dir="ltr"` and `dir="rtl"` layouts.
3. Keep database operations parameterized with PDO, protect state-changing requests with `csrf_verify()`, and escape template output via `e()`.
4. Document any schema updates with matching upgrade and rollback scripts inside `database/`.
