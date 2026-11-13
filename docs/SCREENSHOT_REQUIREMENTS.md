# DENTAL — Screenshot & Visual Asset Inventory

**DATE:** 2026-11-13  
**STATUS:** **NOT RUNTIME VERIFIED — STATIC INSPECTION ONLY**

---

## 1. Existing Asset Classification

Static inspection of all image, font, icon, and brand files in `D:\wamp64\www\Dental` found the following asset categories:

| Category | Workspace Paths | Purpose / Status |
|---|---|---|
| **Application UI Screenshots** | *(None inside workspace)* | 3 historical browser captures exist outside the workspace in session artifacts (one modal-overlap bug snapshot and two pre-refactor captures before role-based navbar updates). None were placed into `docs/screenshots/` because up-to-date, sanitized UI screenshots cannot be captured while runtime execution is prohibited. |
| **Clinic Visual Assets** | `assets/images/clinic/hero-suite.jpg`, `assets/images/clinic/about-lounge.jpg` | Active hero and lounge visuals used on public views (not UI screenshots). |
| **Dentist Portraits** | `assets/images/doctors/1.jpg`, `2.jpg`, `4.jpg`, plus uploaded JPGs (`0809d9...`, `663a13...`, `c91e47...`); fallback copies in `imgs/` (`1.jpg`, `2.jpg`, `4.jpg`) | Referenced by dentist profiles (`includes/auth.php`). |
| **Logos & Icons** | `assets/images/brand/logo-emblem.svg`, `assets/css/bootstrap-icons.min.css`, `assets/css/fonts/bootstrap-icons.*` | Active brand emblem SVG and Bootstrap Icons webfont pack. |
| **Fonts** | `assets/css/fonts/bootstrap-icons.woff`, `.woff2`; legacy `css/fonts/inter/*` and `admin/webfonts/*` | Icon fonts actively used; `Inter` and FontAwesome files reside in legacy directories retained for safety. |
| **User Avatar Uploads** | `assets/uploads/avatars/4a0cbcc48f04e16f52ebe6350288f335.jpg`, `login/uploaded_img/*`, default `login/images/default-avatar.png` | Referenced via `auth_avatar_url()` / legacy fallbacks (`includes/auth.php`); user uploads excluded via `.gitignore`. |
| **Legacy Icons & Backgrounds** | `img/*.svg`, `img/*.jpg`, `img/icons/*.svg` | Referenced by legacy CSS (`css/main.css`, `css/index.css`, `css/admin.css`); retained cleanly on disk. |

---

## 2. Required Screenshots to Capture (Once Runtime Access Is Authorized)

When launching the local application is permitted, capture sanitized, realistic screenshots of the current interface and store them under `docs/screenshots/` matching the `README.md` targets:

| Priority | Target Path | View & Requirements |
|---|---|---|
| 1 | `docs/screenshots/homepage.png` | Desktop Guest Public Homepage (`index.php`, `lang=en`) showing `Sign In` / `Register` top navbar, hero section, treatments, and specialists without private data. |
| 2 | `docs/screenshots/services.png` | Services catalog (`services.php`, `lang=en`) displaying service cards, durations, fees, and booking CTAs. |
| 3 | `docs/screenshots/doctors.png` | Specialists directory (`doctors.php`, `lang=en`) displaying active dentist profiles. |
| 4 | `docs/screenshots/booking.png` | Authenticated Appointment Booking (`booking.php`, `lang=en`) showing selected service, dentist, interactive slot grid, and locked session email field (using sanitized demo email). |
| 5 | `docs/screenshots/patient-dashboard.png` | Patient Dashboard (`patient/index.php`, `lang=en`) demonstrating the horizontal patient Top Navbar, full-width container layout, and **no sidebar** (using sanitized demo account data). |
| 6 | `docs/screenshots/admin-dashboard.png` | Executive Admin Dashboard (`admin/index.php`, `lang=en`) showing the exclusive Admin Sidebar and KPI summary with sanitized demo records. |
| 7 | `docs/screenshots/admin-services.png` | Admin Services Management (`admin/services.php`) showing the bilingual EN/AR service table and centered edit modal without clipping or private data. |
| 8 | `docs/screenshots/arabic-rtl.png` | Arabic RTL public or portal screen (`?lang=ar`, `dir="rtl"`) demonstrating mirrored layout, Arabic typography, and translated controls. |

---

## 3. Sanitization Rules Before Committing Screenshots

Before adding any file to `docs/screenshots/`:
1. Replace any real personal names, phone numbers, patient emails, appointment notes, or admin credentials with clearly synthetic demo data (`patient.demo@example.com`, `+000-000-0000`).
2. Never capture database admin tools (`phpMyAdmin`), `.env` / `config.php` contents, PHP error traces, session tokens, or real cookie headers.
3. Verify visual parity after clearing browser cache in both `1440px` desktop and `375px` mobile viewports.
