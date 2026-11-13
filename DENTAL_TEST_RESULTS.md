# DENTAL — VERIFICATION & TEST RESULTS REPORT

**Test Execution Date:** 2026-11-11 to 2026-11-12  
**PHP Engine:** PHP 8.3.28 (CLI & mod_fcgid)  
**Database Engine:** MySQL 8.4.7  
**Browser Engine:** Live Chromium Browser Subagent on `http://localhost/Dental/`  

---

## 1. PHP 8.3 Syntax Verification (`php -l`)
- **Command Executed**: Recursive `php -l` across all `.php` files in `D:\wamp64\www\Dental`.
- **Total Files Tested**: `65` PHP files
- **Failures**: `0`
- **Result**: **100% PASSED**

---

## 2. Database, Concurrency & Business Logic Suite (`appointment_system_test`)

### 2.1 Functional & Data Integrity Suite (`run_verification_tests.php`)
- **Assertions Executed**: `19`
- **Passed**: `19` | **Failed**: `0`
- **Verified Behaviors**:
  - Connected cleanly to `appointment_system_test`.
  - Preserved record counts verified (`dentists: 3`, `services: 2`, `patients: 1`, `appointments: 1`, `users: 2`).
  - Recurring booking: Two distinct bookings for the same email (`amina.recurring@example.com`) reused single `patient_id = 33` without duplicating patient records or violating email uniqueness.
  - International phone string `+967 770 123 456` saved intact with zero integer truncation.
  - Duplicate start slot (`09:00`) rejected with `err_slot_conflict`.
  - Overlapping interval (`09:15` inside `09:00–09:30`) rejected with `err_slot_conflict`.
  - Cancelling appointment released `active_slot_key` (`NULL`), allowing immediate re-booking of `09:00`.
  - `e('<script>alert(1)</script>')` safely escaped.
  - Unicode Arabic name validation (`محمود ناشر`) passed.
  - Upload validator rejected forged `.php.jpg` payload.
  - Existing admin account verified valid `$2y$` Bcrypt hash.

### 2.2 Parallel Concurrency Stress Test (`concurrent_booking_worker.php`)
- **Test Setup**: Launched **6 simultaneous parallel PHP CLI background jobs** (`Start-Job`) targeting Dentist `#2` on `2026-12-20` at `11:00`.
- **Observed Results**:
  ```
  Worker 1: SUCCESS (Appt #22)
  Worker 2: CONFLICT_REJECTED (err_slot_conflict)
  Worker 3: CONFLICT_REJECTED (err_slot_conflict)
  Worker 4: CONFLICT_REJECTED (err_slot_conflict)
  Worker 5: CONFLICT_REJECTED (err_slot_conflict)
  Worker 6: CONFLICT_REJECTED (err_slot_conflict)
  ActiveBookingsForSlot: 1
  ```
- **Result**: **100% PASSED** — Zero double bookings under concurrent race conditions.

### 2.3 Auth, RBAC & IDOR Verification (`run_auth_idor_tests.php`)
- **Assertions Executed**: `9`
- **Passed**: `9` | **Failed**: `0`
- **Verified Behaviors**:
  - Authenticated regular user (`role = 'user'`) returns `auth_is_admin() === false`.
  - IDOR attempt by User `#41` against unowned Appointment `#18` returns empty (blocked).
  - SQL injection payload `' UNION SELECT 1,username,email,password,5,6,7 FROM users-- -` treated strictly as literal search string (0 rows returned, zero SQL errors).
  - Audit log entries persisted cleanly.

---

## 3. Live Apache HTTP Security Regression Suite

### 3.1 Access Control, Directory Indexing & Exposed File Checks (31 Endpoints)
- **Public Pages** (`/index.php`, `/about.php`, `/services.php`, `/doctors.php`, `/dentist_info.php?id=1`, `/contact.php`, `/booking.php`, `/login.php`, `/register.php`, `/api_slots.php`): All returned **`200 OK`**.
- **Protected Admin Endpoints** (`/admin/index.php`, `/admin/appointments.php`, `/admin/patients.php`, `/admin/dentists.php`, `/admin/services.php`, `/admin/messages.php`, `/admin/users.php`, `/admin/settings.php`, `/admin/delete-doctor.php`, `/admin/delete-appointment.php`): All returned **`302` Redirect** with **`0` BodyBytes** when unauthenticated.
- **Protected Patient Portal Endpoints** (`/patient/index.php`, `/patient/appointments.php`, `/patient/profile.php`, `/patient/security.php`): All returned **`302` Redirect** with **`0` BodyBytes** when unauthenticated.
- **GET Logout Protection** (`/logout.php`): Returned **`302`** redirect without terminating session.
- **Restricted Files & Directories** (`/appointment_system.sql`, `/includes/db.php`, `/database/002_schema_upgrade.sql`, `/languages/en.php`, `/assets/uploads/`, `/login/uploaded_img/`): All returned **`403 Forbidden`**.

### 3.2 CSRF Blocking Verification (5 POST Endpoints Without Token)
- `/login.php`, `/register.php`, `/contact.php`, `/booking.php`, `/logout.php`: All returned **`403 Forbidden`** when `csrf_token` was omitted.

### 3.3 PHP Runtime Error Log Audit (`D:\wamp64\logs\php_error.log`)
- Inspected server PHP error log after all HTTP tests: **0 PHP warnings, notices, or fatal errors** occurred across all requests.

---

## 4. Live Browser UI, Localization & Responsiveness Verification

- **Desktop English (LTR) (`http://localhost/Dental/index.php`)**:
  - `200 OK`, 0 console errors, 7/7 images loaded cleanly (`naturalWidth > 0`), Deep Emerald / Champagne Gold / Warm Ivory luxury design verified.
- **Arabic RTL (`http://localhost/Dental/index.php?lang=ar`)**:
  - Verified `<html lang="ar" dir="rtl">`, mirrored navbar and CTAs, complete Arabic UI translation with 0 mixed-language controls, 0 broken images.
- **Public Subpages Checked**:
  - `about.php` (200 OK, 0 errors), `services.php` (200 OK, 0 errors), `doctors.php` (200 OK, 0 errors), `dentist_info.php?id=1` (200 OK, 0 errors), `contact.php` (200 OK, 0 errors), `login.php` (200 OK, 0 errors).
- **Dynamic Booking Widget (`booking.php`)**:
  - Selecting `Dr. BASSAM` and date `2026-12-22` dynamically fetched and rendered 16 available time slots cleanly via `api_slots.php`.
- **Mobile Responsive Viewport (`375x812`)**:
  - Verified zero horizontal overflow (`document.documentElement.scrollWidth <= window.innerWidth`).
  - Verified mobile hamburger menu opens and displays all navigation links cleanly (`final_step_25.png` visually inspected).
