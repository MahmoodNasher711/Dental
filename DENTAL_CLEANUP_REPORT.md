# DENTAL — SAFE PROJECT CLEANUP & REPOSITORY PRIVACY REPORT

**DATE:** 2026-11-13  
**PROJECT ROOT:** `D:\wamp64\www\Dental`  
**MODE:** Static Inspection & Safe Targeted Cleanup Only (`NOT RUNTIME VERIFIED — STATIC INSPECTION ONLY`)

---

## 1. REMOVED FILES (CONFIRMED TEMPORARY DEBUGGING ARTIFACTS)

Before removing any file, static reference scans confirmed **zero** application imports, links, or dynamic dependencies (`0` references outside `scratch/`). The following 14 temporary test/audit files and session cookie artifact inside the temporary `scratch/` folder were removed individually, followed by the empty `scratch/` directory:

| Removed Path | Classification | Reason for Removal |
|---|---|---|
| `scratch/admin_cookie.txt` | Session cookie artifact | Temporary cURL authentication cookie jar posing credential/session leakage risk if committed. |
| `scratch/audit_translation_coverage.php` | Temporary audit script | One-off localization check script replaced by documentation reports; unreferenced by app. |
| `scratch/find_hardcoded.php` | Temporary audit script | One-off regex scanner for unlocalized strings; unreferenced by app. |
| `scratch/list_38_files.php` | Temporary audit script | Ad-hoc helper listing workspace templates; unreferenced by app. |
| `scratch/test_about_ar.php` | Temporary test script | Ad-hoc Arabic locale endpoint probe; unreferenced by app. |
| `scratch/test_admin_endpoints.php` | Temporary test script | Ad-hoc HTTP probe script; unreferenced by app. |
| `scratch/test_admin_service_actions.php` | Temporary test script | Ad-hoc service POST test script; unreferenced by app. |
| `scratch/test_admin_service_edit.php` | Temporary test script | Ad-hoc modal/edit test script; unreferenced by app. |
| `scratch/test_booking_locale.php` | Temporary test script | Ad-hoc Arabic booking locale test script; unreferenced by app. |
| `scratch/test_booking_locale_en.php` | Temporary test script | Ad-hoc English booking locale test script; unreferenced by app. |
| `scratch/test_dentists_ar.php` | Temporary test script | Ad-hoc Arabic doctors probe; unreferenced by app. |
| `scratch/test_dentists_en.php` | Temporary test script | Ad-hoc English doctors probe; unreferenced by app. |
| `scratch/test_http_endpoints.php` | Temporary test script | Ad-hoc endpoint status probe; unreferenced by app. |
| `scratch/test_services_bilingual.php` | Temporary test script | Ad-hoc bilingual service test script; unreferenced by app. |

*(Workspace scan confirmed zero `.DS_Store`, `Thumbs.db`, `desktop.ini`, `*.tmp`, `*.bak`, or `*.log` files exist in the project root.)*

---

## 2. PRESERVED APPLICATION, DATABASE & ASSET FILES

All required runtime modules, uploads, migrations, security rules, and documentation were preserved:

- **Application Core & Entrypoints:** `.htaccess`, `includes/*.php`, `modules/appointments/booking_service.php`, `languages/*.php`, `templates/**/*.php`, `patient/*.php`, `admin/*.php` (including functional handlers such as `admin/delete-appointment.php`, `admin/delete-doctor.php`, `admin/logout.php`), and all public root PHP routes (`index.php`, `about.php`, `services.php`, `doctors.php`, `dentist_info.php`, `contact.php`, `booking.php`, `booking_successfully.php`, `api_slots.php`, `login.php`, `register.php`, `logout.php`, plus route-redirect stubs).
- **Database Migrations:** `database/002_schema_upgrade.sql`, `database/002_schema_rollback.sql`, `database/003_bilingual_upgrade.sql`, `database/003_bilingual_rollback.sql`.
- **Active Brand, Clinic & Doctor Assets:** `assets/images/brand/logo-emblem.svg`, `assets/images/clinic/*.jpg`, `assets/images/doctors/*`, `assets/uploads/*`, `assets/css/*`, `assets/js/*`.
- **Historical Documentation:** All existing `DENTAL_*.md` specification, roadmap, backup, security, migration, bilingual, and changelog reports were retained intact.

---

## 3. LEGACY & UNCERTAIN FILES RETAINED

Per cleanup safety rules, legacy/uncertain files were **preserved** (never deleted speculatively) because static searches uncovered active fallback references or uncertain legacy roles:

| Path | Reference Inspection Finding | Action Taken |
|---|---|---|
| `imgs/1.jpg`, `imgs/2.jpg`, `imgs/4.jpg` | Referenced by `auth_dentist_image_url()` fallback in `includes/auth.php`. | **Preserved** |
| `login/images/default-avatar.png`, `login/uploaded_img/*` | Referenced by `auth_avatar_url()` and `auth_dentist_image_url()` fallbacks in `includes/auth.php`. | **Preserved on disk** (`login/uploaded_img/*` excluded from Git except `.htaccess`) |
| `css/*.css`, `css/fonts/inter/*`, `img/**` | Legacy stylesheets (`css/main.css`, `css/index.css`, `css/admin.css`) reference `img/` icons/backgrounds; unused by `templates/` luxury layout but kept for safety. | **Preserved** |
| `js.js` (173 KB), `admin/css/custom.css`, `admin/webfonts/*` | No direct references found in PHP templates, likely pre-redesign Bootstrap/FontAwesome assets; treated as uncertain legacy assets. | **Preserved** |
| `login/*.php`, legacy root redirect stubs (`contact_u.php`, `contact_us.php`, `search.php`, `patient_home.php`, `appontment.php`, `userbooking.php`, `header*.php`, `footer.php`, `booking_header.php`, `config.php`, `admin/appointment.php`, `admin/patient.php`, `admin/doctors.php`, `admin/config.php`, `admin/delete-session.php`) | Compatibility bridges/redirects protecting legacy URLs from 404s or open endpoints. | **Preserved** |

---

## 4. GITHUB PRIVACY & `.gitignore` ASSESSMENT

### Current Git Repository State
- Checked `D:\wamp64\www\Dental` for `.git`: **No `.git` directory is initialized**, and **no files or commits are currently tracked or pushed to any remote**.

### `.gitignore` Exclusions Configured
Created `.gitignore` excluding:
- Local secrets & overrides (`includes/config.local.php`, `.env*`, `*.key`, `*.pem`, `*cookie*.txt`).
- Patient/user uploaded files (`assets/uploads/*` except `.htaccess`, `assets/uploads/avatars/*`, `login/uploaded_img/*` except `.htaccess`).
- Backups, archives & database dumps (`*.zip`, `*.bak`, `backups/`, `Dental_backups/`, `appointment_system.sql`, `*backup*.sql`, `*dump*.sql`, while explicitly keeping `!database/*.sql` migration scripts tracked).
- Logs, temporary caches, and IDE/OS clutter (`*.log`, `*.tmp`, `scratch/`, `.DS_Store`, `Thumbs.db`, `.vscode/`, `.idea/`).

### Credential Separation Support Added
- Updated `includes/config.php` to optionally load an untracked `includes/config.local.php` before falling back to local development defaults (`DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`, `DB_CHARSET`).
- Added sanitized `includes/config.example.php` for future developers.

### Repository Privacy Recommendation: **KEEP PRIVATE**
Static privacy scan of root `*.md` files found **pre-existing local user, patient, and practitioner emails, numeric IDs, and phone records** documented inside historical audit/roadmap reports (`DENTAL_FINAL_REVIEW_REPORT.md`, `DENTAL_FINAL_IMPLEMENTATION_REPORT.md`, `DENTAL_DATABASE_MIGRATION_REPORT.md`, `DENTAL_IMPLEMENTATION_ROADMAP.md`). Because historical documentation is preserved under project rules:
- **Recommendation:** Keep any GitHub repository **PRIVATE** unless the owner explicitly redacts personal/clinic seed emails and phone records from historical `DENTAL_*.md` reports, reviews uploaded portrait files in `assets/images/doctors/`, and moves any environment-specific settings out of `includes/config.php` into gitignored `includes/config.local.php`.

---

## 5. RECOMMENDED FUTURE CLEANUP (ONLY AFTER RUNTIME VERIFICATION)

1. Confirm in a live environment whether legacy folders (`css/`, `img/`, `js.js`, `admin/css/`, `admin/webfonts/`, `login/*.php`) can be archived after verifying zero residual routes or DB avatar paths rely on them.
2. Migrate any legacy avatar filenames from `login/uploaded_img/` into `assets/uploads/avatars/` (or remove unused ones) and deprecate legacy fallback paths in `includes/auth.php`.
3. Redact historical sample emails/phone numbers from archived `DENTAL_*.md` reports prior to any future public open-sourcing.
