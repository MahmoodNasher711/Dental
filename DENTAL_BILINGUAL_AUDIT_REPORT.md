# DENTAL MANAGEMENT SYSTEM — SYSTEM-WIDE BILINGUAL AUDIT REPORT

**PROJECT:** DENTAL — Dental Management System  
**AUDIT TYPE:** Static Source Code Inspection & Bilingual Review  
**PRODUCT POSITIONING:** Luxury Dental Clinic Experience  
**SUPPORTED LANGUAGES:** English (`en`, LTR) & Arabic (`ar`, RTL)  
**DATE:** 2026-11-13  
**VERIFICATION STATUS:** **NOT RUNTIME TESTED — STATIC CODE REVIEW ONLY**

> **Execution Constraint Notice:** Under the strict execution boundary governing this phase, Apache, MySQL, WampServer, PHP runtime execution (`php -l`), CLI database queries, and browser rendering were **NOT** executed. All findings below are derived strictly from static source-code reading and read-only pattern inspection.

---

## 1. AUDIT OBJECTIVES & SCOPE

This report audits the bilingual architecture, template localization, JavaScript messaging, security messages, and SQL schema artifacts across `D:\wamp64\www\Dental`.

1. **Implemented Source-Code Changes:** Source fixes applied directly to PHP, JS, CSS, and template files during this pass.
2. **Static Code Review Findings:** Observed structure, localization helpers, and remaining caveats across public, patient, and admin modules.
3. **Prepared SQL Migrations (Source Artifacts Only):** Unexecuted SQL files documented for schema inspection without running MySQL queries.
4. **Future Runtime Testing Matrix:** Checks explicitly marked `NOT RUNTIME TESTED — STATIC CODE REVIEW ONLY` for later execution.

---

## 2. IMPLEMENTED SOURCE-CODE CHANGES (BILINGUAL COMPLETION)

During static code review of the updated authentication, role-based UI, and admin/public views, additional hardcoded or unlocalized strings and logic defects were identified and fixed in source code:

| Area | File | Change Implemented |
|---|---|---|
| Role Localization | `includes/localization.php` | Added `translate_role(?string $role): string` mapping `'admin'` -> `lang_raw('role_admin')` and `'user'`/`'patient'` -> `lang_raw('role_user')`. |
| Language Dictionaries | `languages/en.php`, `languages/ar.php` | Added 19 synchronized keys (`nav_account`, `nav_return_site`, `placeholder_srv_ar`, `placeholder_desc_ar`, `placeholder_price`, `placeholder_spec_ar`, `placeholder_dentist_ar`, `placeholder_bio_ar`, `booking_auth_required`, `booking_auth_notice_title`, `booking_duplicate_error`, `booking_nonce_error`, `booking_slots_loading`, `booking_slots_error`, `role_admin`, `role_user`, `current_session`, `patient_portal_badge`, `err_csrf_failed`), raising each dictionary to 285 keys. |
| Security Messages | `includes/security.php` | Updated `csrf_verify()` to use `lang_raw('err_csrf_failed')` when localization is loaded, retaining an English fallback if called prior to `init_localization()`. |
| Slot Loader JS UI | `booking.php`, `assets/js/luxury.js` | Bound localized `data-loading-text`, `data-empty-text`, and `data-error-text` attributes onto `#slotGridContainer` in `booking.php` and consumed them in `assets/js/luxury.js`, plus graceful handling for HTTP `401` JSON auth errors. |
| Admin Role & Session UI | `templates/admin/layout_start.php`, `admin/index.php`, `admin/users.php` | Replaced raw `'admin'`/`'user'` output with `translate_role()`, replaced hardcoded `'Current Session'` in `admin/users.php` with `_e('current_session')`, and updated the sidebar return link to `_e('nav_return_site')`. |
| Admin Service & Dentist Placeholders | `admin/services.php`, `admin/dentists.php` | Replaced hardcoded Arabic/mixed input placeholders with localized calls (`__('placeholder_srv_ar')`, `__('placeholder_price')`, `__('placeholder_desc_ar')`, `__('placeholder_dentist_ar')`, `__('placeholder_spec_ar')`, `__('placeholder_bio_ar')`); replaced `'Dentist profile'` fallback in `admin/dentists.php` with `lang_raw('doctor_profile_title')`. |
| Active Checkbox Bug Fix | `admin/services.php` | Fixed `$active = isset($_POST['is_active']) ? 1 : 0;` (previously `1 : 1` on service creation). |
| Legacy Partial Cleanup | `header.php`, `header_patient.php`, `footer.php`, `booking_header.php` | Replaced obsolete, unlocalized HTML fragments with clean bootstrap redirects (`header_patient.php` -> `auth_require_patient()` -> `patient/index.php`; `booking_header.php` -> `auth_require_booking_login()` -> `booking.php`; `header.php`/`footer.php` -> `index.php`). |

---

## 3. STATIC CODE REVIEW FINDINGS

### 3.1 Localization Lifecycle (`includes/localization.php`)
- `init_localization()` resolves locale in order: validated `$_GET['lang']` (`en` or `ar`) -> `$_SESSION['lang']` -> `$_COOKIE['lang']` (`dental_lang`) -> default `'en'`.
- `lang_switch_url()` preserves the current path and query string when toggling between English and Arabic.
- Dynamic database content uses dedicated helpers: `service_name()`, `service_description()`, `dentist_name()`, `dentist_specialization()`, `dentist_bio()`, `about_us_content()`, `clinic_address()`, `clinic_hours()`, `translate_status()`, `translate_role()`, and `format_localized_date()`.

### 3.2 Directionality & Layout Parity
- `templates/public/header.php`, `templates/patient/layout_start.php` (now delegating to `templates/public/header.php` + `templates/partials/navbar.php`), and `templates/admin/layout_start.php` set `<html lang="..." dir="...">` from the active locale.
- Role-based navigation in `templates/partials/navbar.php` renders all Guest, Patient, and Admin links exclusively via `_e(...)` keys and adds `.luxury-nav-link` styling (`white-space: nowrap`) in `assets/css/luxury.css` to keep horizontal items cleanly aligned in both LTR and RTL.

### 3.3 Static Scan Observations
- Static PowerShell `Select-String` inspection across 66 `.php` files identified 543 `__()` / `_e()` / `lang_raw()` key calls; only 1 regex hit (`localization.php:status_`) corresponded to dynamic concatenation (`'status_' . $normalized`). All static keys exist in both `languages/en.php` and `languages/ar.php`.
- Placeholder scan across public, patient, admin, and template `.php` files showed only 1 non-PHP static placeholder: `booking.php` line 157 `placeholder="+..."` (symbolic phone country-code cue).

---

## 4. PREPARED / EXISTING SQL MIGRATIONS (NOT EXECUTED IN THIS REVIEW)

The following SQL migration and rollback scripts exist as files in `database/` and were inspected statically only (no queries run against MySQL):

1. `database/002_schema_upgrade.sql` — Modernization schema script (roles, FK linkages, active slot uniqueness index `idx_unique_active_slot`, bilingual `clinic_settings` / `about_us` structures).
2. `database/003_bilingual_upgrade.sql` — Adds bilingual columns to `services` (`service_name_en`, `service_name_ar`, `service_description_en`, `service_description_ar`) and `dentists` (`dentist_name_ar`, `specialization_ar`, `dentist_brief_ar`).
3. `database/003_bilingual_rollback.sql` — Reverts `003_bilingual_upgrade.sql` by dropping the added `_en` / `_ar` columns while retaining base columns.

---

## 5. ITEMS REQUIRING FUTURE RUNTIME TESTING

All items below are **`NOT RUNTIME TESTED — STATIC CODE REVIEW ONLY`**:

- [ ] **PHP Syntax & Fatal Error Check (`php -l`)** across all modified PHP files (`includes/`, `templates/`, `patient/`, `admin/`, root endpoints) — `NOT RUNTIME TESTED — STATIC CODE REVIEW ONLY`.
- [ ] **Live LTR / RTL Rendering (`en` vs `ar`)** in Chrome/Edge/Firefox on desktop and mobile widths, checking Bootstrap RTL asset loading, `.luxury-nav-link` wrapping, and dropdown alignment — `NOT RUNTIME TESTED — STATIC CODE REVIEW ONLY`.
- [ ] **Language Toggle Persistence** across `booking.php` redirects (`booking.php` -> `login.php?redirect=...` -> `register.php` -> `login.php` -> `booking.php`) — `NOT RUNTIME TESTED — STATIC CODE REVIEW ONLY`.
- [ ] **Database Column Verification** confirming `database/002_schema_upgrade.sql` and `database/003_bilingual_upgrade.sql` columns exist in live `appointment_system` before rendering localized service/dentist fields — `NOT RUNTIME TESTED — STATIC CODE REVIEW ONLY`.
- [ ] **JavaScript Slot Fetch & `401` Error Rendering** in `assets/js/luxury.js` with `ar` and `en` active sessions — `NOT RUNTIME TESTED — STATIC CODE REVIEW ONLY`.
- [ ] **Admin Services Inactive Creation (`is_active = 0`) & Bilingual Modals** (`admin/services.php`, `admin/dentists.php`) under POST submission with CSRF validation — `NOT RUNTIME TESTED — STATIC CODE REVIEW ONLY`.
