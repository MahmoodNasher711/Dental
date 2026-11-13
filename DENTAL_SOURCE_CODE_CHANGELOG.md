# DENTAL MANAGEMENT SYSTEM — SOURCE CODE CHANGELOG

**PROJECT:** DENTAL — Dental Management System  
**DATE:** 2026-11-13  
**EXECUTION MODE:** Static Inspection & Source Modification Only  
**RUNTIME STATUS:** **NOT RUNTIME TESTED — STATIC CODE REVIEW ONLY**

> **Execution Constraint Notice:** No PHP code, Apache/WampServer service, MySQL migration/query, or browser test was executed. All entries below reflect files inspected, created, and modified on disk during this phase.

---

## 1. SUMMARY OF FILES CREATED

| File | Role / Summary |
|---|---|
| `templates/partials/navbar.php` | Unified responsive horizontal top navbar component handling Guest, Authenticated Patient, and Authenticated Admin views with `_e()` localization and CSRF POST logout forms. |
| `DENTAL_AUTHENTICATION_BOOKING_REVIEW.md` | Source-level audit of mandatory authentication, redirect retention, anti-IDOR booking binding, concurrency/duplicate protections, and runtime testing items. |
| `DENTAL_ROLE_BASED_LAYOUT_REPORT.md` | Architecture and static audit report detailing removal of the patient sidebar, full-width patient layout, role-specific navbars, and admin-exclusive sidebar. |
| `DENTAL_SOURCE_CODE_CHANGELOG.md` | Complete inventory of source changes, inspected files, prepared SQL artifacts, and pending runtime verification items. |

---

## 2. SUMMARY OF FILES MODIFIED

### Core Security, Auth & Localization
- **`includes/auth.php`**: Added `auth_sanitize_internal_redirect()`, `auth_set_intended_url()`, `auth_get_intended_url()`, `auth_consume_intended_url()`, `auth_require_booking_login()`, and `auth_require_patient()` (routing admins to `/admin/index.php`).
- **`includes/localization.php`**: Added `translate_role(?string $role): string`.
- **`includes/security.php`**: Updated `csrf_verify()` to return localized `lang_raw('err_csrf_failed')` when localization is initialized (preserving English fallback).
- **`languages/en.php`** & **`languages/ar.php`**: Added 19 synchronized localization keys (`nav_account`, `nav_return_site`, `placeholder_srv_ar`, `placeholder_desc_ar`, `placeholder_price`, `placeholder_spec_ar`, `placeholder_dentist_ar`, `placeholder_bio_ar`, `booking_auth_required`, `booking_auth_notice_title`, `booking_duplicate_error`, `booking_nonce_error`, `booking_slots_loading`, `booking_slots_error`, `role_admin`, `role_user`, `current_session`, `patient_portal_badge`, `err_csrf_failed`), reaching 285 keys per language.

### Booking Engine & Booking Endpoints
- **`modules/appointments/booking_service.php`**: Required authenticated `$authUser` (`id` and `email`), eliminated client email/ID trust by binding strictly to `$authUser['email']`, hardened `patients` record matching (`user_id = :uid`, linking unowned email rows only when `user_id IS NULL`, rejecting ownership mismatch with `err_unauthorized`), and added per-patient overlapping active appointment detection (`booking_duplicate_error`) inside the locked transaction.
- **`booking.php`**: Enforced `auth_require_booking_login()` before GET/POST processing, added single-use `booking_nonce` validation against duplicate submission/resubmission, forced email field `readonly disabled` populated from `$authUser['email']`, bound submission email server-side, and added localized `data-loading-text`, `data-empty-text`, and `data-error-text` attributes on `#slotGridContainer`.
- **`api_slots.php`**: Added `auth_user()` gate returning HTTP `401` JSON (`error: 'auth_required'`, localized message, empty `slots`) to guests.
- **`appontment.php`** & **`userbooking.php`**: Replaced open/legacy handlers with `includes/bootstrap.php` + `auth_require_booking_login(DENTAL_BASE_URL . '/booking.php')`.
- **`booking_successfully.php`**: Removed guest `$isJustCreated` bypass; enforced `auth_require_login()` with strict owner/admin check before displaying receipt details.
- **`assets/js/luxury.js`**: Read localized loading/empty/error messages from `#slotGridContainer` `dataset` and handled HTTP `401` slot responses gracefully.

### Authentication Flows
- **`login.php`**: Captured/sanitized `?redirect=` via `auth_set_intended_url()`, preserved `?redirect=` on `register.php` links, displayed localized `booking_auth_required` notice banner when redirected from booking, and consumed `intended_url` post-login for non-admin users.
- **`register.php`**: Checked `auth_is_admin()` when redirecting already-authenticated users, preserved `?redirect=` / `intended_url` across `login.php` links and post-registration login redirect, and displayed the localized booking-auth notice.

### Role-Based Layouts, Portal & Styling
- **`templates/public/header.php`**: Added flex full-height body utilities (`d-flex flex-column min-vh-100`) and delegated `<nav>` rendering to `templates/partials/navbar.php`.
- **`templates/patient/layout_start.php`** & **`templates/patient/layout_end.php`**: Removed all sidebar markup (`.portal-shell`, `#portalSidebar`, `#portalSidebarToggle`, `#portalSidebarClose`, `.portal-main`), enforced `auth_require_patient()`, wrapped content in full-width container using `templates/public/header.php` + `templates/public/footer.php` and luxury portal header banner.
- **`patient/index.php`**, **`patient/appointments.php`**, **`patient/profile.php`**, **`patient/security.php`**, **`patient_home.php`**: Switched `auth_require_login()` to `auth_require_patient()`.
- **`assets/css/luxury.css`**: Tuned `.luxury-nav-link` sizing/padding (`white-space: nowrap`) and `.luxury-nav-link.active::after` (`inset-inline: 0.7rem`) for EN/AR horizontal top navbars.
- **`header.php`**, **`header_patient.php`**, **`footer.php`**, **`booking_header.php`**: Converted obsolete partial stubs into clean bootstrap redirects (enforcing `auth_require_patient()` / `auth_require_booking_login()` where applicable).

### Admin Localization & Security Fixes
- **`templates/admin/layout_start.php`**, **`admin/index.php`**, **`admin/users.php`**: Localized role labels via `translate_role()`, localized `'Current Session'` via `_e('current_session')`, and updated sidebar website link to `_e('nav_return_site')`.
- **`admin/services.php`**: Fixed inactive checkbox logic (`$active = isset($_POST['is_active']) ? 1 : 0;`) and replaced hardcoded placeholders with `__('placeholder_srv_ar')`, `__('placeholder_price')`, `__('placeholder_desc_ar')`.
- **`admin/dentists.php`**: Replaced `'Dentist profile'` fallback with `lang_raw('doctor_profile_title')` and replaced hardcoded placeholders with `__('placeholder_dentist_ar')`, `__('placeholder_spec_ar')`, `__('placeholder_bio_ar')`.
- **`admin/logout.php`**: Restricted logout to verified `POST` + `csrf_verify()` + `auth_logout()`, returning `405` redirect to `admin/index.php` on GET.

### Documentation Reports Updated
- **`DENTAL_BILINGUAL_AUDIT_REPORT.md`** & **`DENTAL_TRANSLATION_COVERAGE_REPORT.md`**: Replaced prior runtime claims with strictly observed static audit metrics (285/285 dictionary keys, 543 translation calls) and explicit `NOT RUNTIME TESTED — STATIC CODE REVIEW ONLY` markers.

---

## 3. FILES INSPECTED (NO CHANGES REQUIRED OR REVIEWED IN CONTEXT)

- Core: `includes/bootstrap.php`, `includes/config.php`, `includes/db.php`, `includes/validation.php`, `includes/upload.php`
- Public Pages: `index.php`, `about.php`, `services.php`, `doctors.php`, `dentist_info.php`, `contact.php`, `contact_u.php`, `contact_us.php`, `search.php`, `logout.php`
- Admin Pages & Handlers: `admin/appointments.php`, `admin/patients.php`, `admin/messages.php`, `admin/settings.php`, `admin/appointment.php`, `admin/patient.php`, `admin/doctors.php`, `admin/config.php`, `admin/delete-appointment.php`, `admin/delete-doctor.php`, `admin/delete-session.php`, `templates/admin/layout_end.php`
- SQL Artifacts (Read-Only): `database/002_schema_upgrade.sql`, `database/003_bilingual_upgrade.sql`, `database/003_bilingual_rollback.sql`

---

## 4. PREPARED / EXISTING SQL MIGRATIONS (NOT EXECUTED)

- `database/002_schema_upgrade.sql`
- `database/003_bilingual_upgrade.sql`
- `database/003_bilingual_rollback.sql`

No SQL migration or query was executed during this session.

---

## 5. ITEMS REQUIRING FUTURE RUNTIME TESTING

All runtime execution checks remain **`NOT RUNTIME TESTED — STATIC CODE REVIEW ONLY`**:
1. PHP syntax linting (`php -l`) across all 66 PHP files.
2. Unauthenticated vs Authenticated vs Admin routing across `booking.php`, `api_slots.php`, `booking_successfully.php`, `patient/*`, and `admin/*`.
3. Intended-destination redirect survival across `login.php` and `register.php` in both `lang=en` and `lang=ar`.
4. Anti-IDOR email binding, `booking_nonce` replay rejection, and concurrent/duplicate slot rejection in `booking_service.php`.
5. Live browser rendering (desktop/mobile, LTR/RTL) of the refactored full-width patient layout and horizontal top navbar.
6. Admin service active/inactive toggle creation (`admin/services.php`) and POST+CSRF admin logout (`admin/logout.php`).
