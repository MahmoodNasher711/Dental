# DENTAL MANAGEMENT SYSTEM — TRANSLATION COVERAGE REPORT

**PROJECT:** DENTAL — Dental Management System  
**DATE:** 2026-11-13  
**DICTIONARY SCOPE:** `languages/en.php` & `languages/ar.php`  
**VERIFICATION METHOD:** Read-Only Static Source Inspection (`Select-String` / Regex Audit)  
**RUNTIME STATUS:** **NOT RUNTIME TESTED — STATIC CODE REVIEW ONLY**

---

## 1. IMPLEMENTED SOURCE-CODE CHANGES AFFECTING COVERAGE

To eliminate residual untranslated strings identified during the updated authentication and role-based UI review, 19 translation keys were added symmetrically to `languages/en.php` and `languages/ar.php`:

- **Navigation & Portal Labels:** `nav_account`, `nav_return_site`, `patient_portal_badge`
- **Role & Session Labels:** `role_admin`, `role_user`, `current_session`
- **Booking Auth & Slot States:** `booking_auth_required`, `booking_auth_notice_title`, `booking_duplicate_error`, `booking_nonce_error`, `booking_slots_loading`, `booking_slots_error`
- **CSRF Error Localization:** `err_csrf_failed`
- **Admin Bilingual Placeholders:** `placeholder_srv_ar`, `placeholder_desc_ar`, `placeholder_price`, `placeholder_spec_ar`, `placeholder_dentist_ar`, `placeholder_bio_ar`

Additionally:
- `includes/localization.php` received `translate_role(?string $role): string`.
- `includes/security.php` was updated so `csrf_verify()` resolves `lang_raw('err_csrf_failed')` when localization is initialized.
- `booking.php` + `assets/js/luxury.js` pass localized slot status messages via `data-loading-text`, `data-empty-text`, and `data-error-text`.

---

## 2. STATIC CODE REVIEW FINDINGS: DICTIONARY & CALL-SITE METRICS

Executed read-only static inspection over `D:\wamp64\www\Dental` (excluding scratch/backup paths, zero PHP execution):

| Metric | Static Result | Notes |
|---|---|---|
| English Dictionary Keys (`languages/en.php`) | **285** | Distinct key count matching `'key' =>` |
| Arabic Dictionary Keys (`languages/ar.php`) | **285** | Distinct key count matching `'key' =>` |
| Keys Missing in `ar.php` | **0** | 100% key parity verified statically |
| Keys Missing in `en.php` | **0** | 100% key parity verified statically |
| `.php` Files Scanned | **66** | Workspace PHP source files |
| Static `__()`, `_e()`, `lang_raw()` Key References | **543** | Literal single-quoted key invocations |
| Unmatched Literal Key References | **0 literal / 1 dynamic** | Only `localization.php:status_` matched regex because `translate_status()` concatenates `'status_' . $normalized` dynamically (`status_pending`, `status_confirmed`, `status_completed`, `status_cancelled` all present in both dictionaries) |
| Remaining Static HTML `placeholder="..."` Attributes | **1** | `booking.php:157` (`placeholder="+..."` universal phone prefix hint) |

---

## 3. MODULE-BY-MODULE STATIC COVERAGE SUMMARY

| Module / Component | Localization Mechanism Used | Static Gaps Found | Runtime Status |
|---|---|---|---|
| Shared Top Navbar (`templates/partials/navbar.php`) | `_e()` keys for Guest, Patient, Admin, and Lang Switcher | None | `NOT RUNTIME TESTED — STATIC CODE REVIEW ONLY` |
| Public Pages (`index.php`, `about.php`, `services.php`, `doctors.php`, `dentist_info.php`, `contact.php`, `search.php`) | `_e()` + `service_name()`, `service_description()`, `dentist_name()`, `dentist_specialization()`, `dentist_bio()`, `about_us_content()`, `clinic_address()`, `clinic_hours()` | None | `NOT RUNTIME TESTED — STATIC CODE REVIEW ONLY` |
| Auth Flow (`login.php`, `register.php`, `logout.php`) | `_e()` keys + localized `booking_auth_required` banner | None | `NOT RUNTIME TESTED — STATIC CODE REVIEW ONLY` |
| Booking Flow (`booking.php`, `booking_successfully.php`, `api_slots.php`, `modules/appointments/booking_service.php`, `assets/js/luxury.js`) | `_e()` / `lang_raw()`, JS `data-*` attributes, localized error codes in JSON and service returns | `placeholder="+..."` on phone input (`booking.php:157`) | `NOT RUNTIME TESTED — STATIC CODE REVIEW ONLY` |
| Patient Portal (`patient/index.php`, `appointments.php`, `profile.php`, `security.php`, `templates/patient/layout_start.php`, `layout_end.php`) | `_e()` + `translate_status()`, `format_localized_date()`, `service_name()`, `dentist_name()` | None | `NOT RUNTIME TESTED — STATIC CODE REVIEW ONLY` |
| Admin Platform (`admin/index.php`, `appointments.php`, `patients.php`, `dentists.php`, `services.php`, `messages.php`, `users.php`, `settings.php`, `templates/admin/layout_start.php`) | `_e()` + `translate_role()`, `translate_status()`, localized placeholders, localized modals | Legacy endpoints (`admin/appointment.php`, `patient.php`, `doctors.php`, `delete-*.php`) are POST/redirect stubs without rendered UI | `NOT RUNTIME TESTED — STATIC CODE REVIEW ONLY` |
| Security Core (`includes/security.php`) | `csrf_verify()` calls `lang_raw('err_csrf_failed')` when loaded | Fallback English text remains if called before `localization.php` | `NOT RUNTIME TESTED — STATIC CODE REVIEW ONLY` |

---

## 4. PREPARED / EXISTING SQL ARTIFACTS (UNEXECUTED)

- `database/002_schema_upgrade.sql` — Schema support for multilingual clinic settings and About Us columns.
- `database/003_bilingual_upgrade.sql` — Multilingual columns on `services` and `dentists`.
- `database/003_bilingual_rollback.sql` — Rollback DDL for Migration 003.

*(Documented strictly as existing source artifacts; no SQL statements were executed during this review.)*

---

## 5. ITEMS REQUIRING FUTURE RUNTIME TESTING

All runtime behaviors below are **`NOT RUNTIME TESTED — STATIC CODE REVIEW ONLY`**:

- [ ] End-to-end visual inspection of all public, patient, and admin screens in `lang=ar` (`dir="rtl"`) and `lang=en` (`dir="ltr"`) for any dynamically generated DB strings or missed edge-case messages.
- [ ] Triggering validation errors, CSRF mismatch errors (`err_csrf_failed`), login throttling errors, and duplicate booking rejections in both languages to confirm localized rendering.
- [ ] Verifying `assets/js/luxury.js` displays localized Arabic and English messages when loading slots, receiving empty slots, receiving HTTP `401` (`auth_required`), or encountering network errors.
- [ ] Verifying PHP date formatting (`format_localized_date()`) and status/role translations (`translate_status()`, `translate_role()`) in live MySQL-backed tables.
