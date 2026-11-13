# DENTAL MANAGEMENT SYSTEM — AUTHENTICATION & BOOKING SECURITY REVIEW

**PROJECT:** DENTAL — Dental Management System  
**DATE:** 2026-11-13  
**REVIEW SCOPE:** Authentication Guards, Redirect Continuity, Anti-IDOR Session Binding, Concurrency & Duplicate Booking Prevention  
**VERIFICATION STATUS:** **NOT RUNTIME TESTED — STATIC CODE REVIEW ONLY**

> **Execution Constraint Notice:** No live HTTP requests, PHP execution (`php -l`), database transactions, or browser flows were executed during this review. All assessments reflect source-level inspection only.

---

## 1. PREVIOUS VULNERABILITIES IDENTIFIED & REMEDIATED

Static code inspection prior to remediation uncovered critical authentication and identity-binding gaps in the appointment workflow:

1. **Unauthenticated Guest Booking Allowed:** `booking.php`, `appontment.php`, `userbooking.php`, and `api_slots.php` permitted anonymous visitors to view live dentist availability and submit appointments without logging in.
2. **Email / Patient Record Spoofing Risk (IDOR):** `modules/appointments/booking_service.php` previously accepted `$authUser = null` and trusted client-submitted email/identifiers when resolving or creating records in `patients`.
3. **Unauthenticated Confirmation Access:** `booking_successfully.php` relied on a guest `$isJustCreated` session bypass flag instead of enforcing authenticated ownership verification.
4. **Lost Booking Destination Across Auth:** Guests clicking booking CTAs had no standardized, open-redirect-safe mechanism to return to `booking.php` (including selected query parameters) after signing in or registering.
5. **Admin / Legacy Entry Exposure:** `register.php` redirected logged-in users uniformly without checking `auth_is_admin()`, and `admin/logout.php` lacked POST + CSRF enforcement.

---

## 2. IMPLEMENTED SOURCE-CODE CHANGES

### 2.1 Centralized Auth & Redirect Helpers (`includes/auth.php`)
- Added `auth_sanitize_internal_redirect(?string $url): ?string`: Restricts redirect targets strictly to safe relative/internal `/Dental/...` application paths (rejecting external hosts, protocol-relative URLs, or header injection vectors).
- Added `auth_set_intended_url(?string $url)`, `auth_get_intended_url()`, and `auth_consume_intended_url()`: Stores, retrieves, and atomically clears the validated post-login destination in session.
- Added `auth_require_booking_login(?string $targetUrl = null): array`: Enforces authentication before booking access. If unauthenticated, stores the intended booking URL, sets the localized flash/notice banner (`booking_auth_required`), and redirects cleanly to `login.php?redirect=...`.
- Added `auth_require_patient(): array`: Enforces authenticated non-admin patient session on patient portal routes, redirecting admin users to `/admin/index.php`.

### 2.2 Mandatory Authentication on Booking & Legacy Endpoints
| Endpoint | Enforcement Implemented |
|---|---|
| `booking.php` | Calls `$authUser = auth_require_booking_login();` at top before both GET rendering and POST submission handling. Generates and validates single-use `$_SESSION['booking_nonce']` (`booking_nonce_error`) to block accidental duplicate submissions or page reloads. Displays `$authUser['email']` as `readonly disabled` and strips any client-supplied `patient_id`, `user_id`, or altered email by binding `$authUser['email']` server-side. |
| `api_slots.php` | Checks `auth_user()`. Unauthenticated callers receive HTTP `401 Unauthorized` JSON (`error: 'auth_required'`, localized message, empty `slots` array). |
| `appontment.php` | Bootstraps `includes/bootstrap.php` and enforces `auth_require_booking_login(DENTAL_BASE_URL . '/booking.php')`, eliminating direct guest booking bypass. |
| `userbooking.php` | Bootstraps `includes/bootstrap.php` and enforces `auth_require_booking_login(DENTAL_BASE_URL . '/booking.php')`. |
| `booking_header.php` | Replaced unauthenticated partial HTML with `auth_require_booking_login(DENTAL_BASE_URL . '/booking.php')` redirect. |
| `booking_successfully.php` | Removed guest `$isJustCreated` bypass; now enforces `$authUser = auth_require_login()`, strictly verifying appointment ownership against the authenticated patient (or admin role) before displaying the confirmation receipt. |

### 2.3 Redirect Preservation in `login.php` & `register.php`
- **`login.php`:**
  - Captures and sanitizes `?redirect=` via `auth_set_intended_url()`.
  - Displays a localized informational banner (`booking_auth_notice_title` + `booking_auth_required`) when the visitor arrived via booking redirection.
  - Preserves sanitized `?redirect=` onto the `register.php` link.
  - On successful authentication: admins route to `/admin/index.php`; patients consume `auth_consume_intended_url()` (e.g., returning directly to `booking.php` with preserved parameters) or default to `/patient/index.php`.
- **`register.php`:**
  - Checks `auth_is_admin()` when an already-logged-in user hits registration so admins route to `/admin/index.php` instead of `/patient/index.php`.
  - Preserves `?redirect=` / `intended_url` across `login.php` links and after registration completion, requiring login before redirecting back to the booking flow.
  - Displays the `booking_auth_required` notice when arriving from booking.

### 2.4 Service-Layer Identity Binding & Duplicate Guard (`modules/appointments/booking_service.php`)
- Enforces non-null authenticated `$authUser` with non-empty `id` and `email`.
- Forces `$patientEmail` exclusively from `$authUser['email']`, discarding untrusted client-submitted values.
- Tightens `patients` resolution to match `user_id = :uid`, linking an unowned matching email record **only** when `user_id IS NULL`; mismatched `user_id` ownership is rejected with `err_unauthorized`.
- Preserves existing dentist row locking (`FOR UPDATE` on `dentists` + unique constraint `idx_unique_active_slot`) and adds an in-transaction overlapping active appointment check for the patient, returning localized `booking_duplicate_error`.

### 2.5 Admin Logout Hardening (`admin/logout.php`)
- Enforces `POST` method + `csrf_verify()` + `auth_logout()`, redirecting unauthenticated GET requests with HTTP `405` back to `admin/index.php`.

---

## 3. STATIC CODE REVIEW FINDINGS

- Static `Select-String` inspection confirmed `auth_require_booking_login` or `booking_service.php` / `auth_user()` gating across `booking.php`, `appontment.php`, `userbooking.php`, `booking_header.php`, and `api_slots.php`.
- Static check of `patient/index.php`, `patient/appointments.php`, `patient/profile.php`, `patient/security.php`, `patient_home.php`, and `templates/patient/layout_start.php` confirmed all 6 entry points invoke `auth_require_patient()`.
- **Static Observation on `admin/delete-*.php`:** `admin/delete-appointment.php` and `admin/delete-doctor.php` reference `$conn` supplied via `includes/auth.php` -> `includes/db.php` (`$conn = db();`). Confirm at runtime that auth guards succeed before DELETE queries execute.

---

## 4. PREPARED / EXISTING SQL ARTIFACTS (UNEXECUTED)

- `database/002_schema_upgrade.sql` defines relational FKs (`patients.user_id`, `appointments.patient_id`), user role enums, and `idx_unique_active_slot` supporting concurrent double-booking prevention at the storage engine level. *(Not executed during this static-only phase.)*

---

## 5. ITEMS REQUIRING FUTURE RUNTIME TESTING

All scenarios below are **`NOT RUNTIME TESTED — STATIC CODE REVIEW ONLY`**:

- [ ] **Unauthenticated Access Tests:** Visiting `booking.php`, `appontment.php`, `userbooking.php`, `booking_successfully.php`, and calling `api_slots.php` anonymously should trigger login redirects or `401` JSON without leaking slots or creating records — `NOT RUNTIME TESTED — STATIC CODE REVIEW ONLY`.
- [ ] **Redirect Continuity Tests:** Visiting `booking.php?service_id=1&dentist_id=2` while logged out -> `login.php` -> `register.php` -> creating account -> `login.php` -> logging in -> verifying final landing back on `booking.php` with preserved context — `NOT RUNTIME TESTED — STATIC CODE REVIEW ONLY`.
- [ ] **Anti-IDOR & Tampering Tests:** Modifying hidden or DOM form fields / POST body (`patient_email`, `patient_id`, `user_id`) in `booking.php` and verifying server-side insertion binds strictly to `$_SESSION` user — `NOT RUNTIME TESTED — STATIC CODE REVIEW ONLY`.
- [ ] **Duplicate & Concurrent Booking Tests:** Submitting `booking.php` twice (stale `booking_nonce`), refreshing POST confirmation, attempting overlapping slot booking for same patient (`booking_duplicate_error`), and testing simultaneous slot contention — `NOT RUNTIME TESTED — STATIC CODE REVIEW ONLY`.
- [ ] **Role Separation Tests:** Attempting to access `patient/*` as Admin (should redirect to `admin/index.php`) and `admin/*` as Patient (should reject/redirect) — `NOT RUNTIME TESTED — STATIC CODE REVIEW ONLY`.
- [ ] **CSRF Failure Localization & Admin POST Logout Tests:** Submitting invalid CSRF token on booking/profile/admin logout in `ar` and `en` sessions — `NOT RUNTIME TESTED — STATIC CODE REVIEW ONLY`.
