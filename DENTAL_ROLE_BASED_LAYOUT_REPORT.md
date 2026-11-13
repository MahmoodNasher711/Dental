# DENTAL MANAGEMENT SYSTEM — ROLE-BASED UI & LAYOUT ARCHITECTURE REPORT

**PROJECT:** DENTAL — Dental Management System  
**DATE:** 2026-11-13  
**SCOPE:** Guest vs Authenticated Patient vs Admin Navigation & Layout Separation  
**VERIFICATION STATUS:** **NOT RUNTIME TESTED — STATIC CODE REVIEW ONLY**

> **Execution Constraint Notice:** Layout changes were reviewed statically in PHP/HTML/CSS source code. Responsive breakpoints, RTL/LTR dropdown alignment, Bootstrap collapse behavior, and mobile drawer interactions were **NOT** executed in a live browser.

---

## 1. ARCHITECTURAL REQUIREMENT & PREVIOUS DEFECT

The required role-based UI architecture specifies strict structural separation across the three user states:

1. **Guest (`auth_user() === null`):** Standard horizontal website navigation bar (Brand Logo, Home, About, Services, Dentists, Contact, Language Switcher, Login, Register).
2. **Authenticated Patient (`role !== 'admin'`):** Horizontal website-style Top Navbar across **both** public pages and `patient/*` pages (`templates/patient/layout_start.php` + `templates/partials/navbar.php`), occupying full container width with **zero vertical sidebar** HTML/CSS.
3. **Administrator (`role === 'admin'`):** Exclusive vertical Admin Sidebar workspace (`templates/admin/layout_start.php` / `templates/admin/layout_end.php`), strictly restricted to admins, plus a convenient Admin Platform link when browsing public views.

**Defect Corrected:** Previously, `templates/patient/layout_start.php` rendered an admin-style `.portal-shell` with `<aside id="portalSidebar">`, `#portalSidebarToggle`, `#portalSidebarClose`, and `.portal-main`, exposing a sidebar layout to patients and duplicating navigation controls awkwardly.

---

## 2. IMPLEMENTED SOURCE-CODE CHANGES

### 2.1 Shared Role-Aware Top Navigation Bar (`templates/partials/navbar.php`)
Created a single source-of-truth navigation component included by public pages and patient portal pages (`<nav class="navbar navbar-expand-xl ...">`):

- **Guest View:**
  - Brand wordmark/logo linking to `index.php`
  - Links: Home (`index.php`), About (`about.php`), Services (`services.php`), Dentists (`doctors.php`), Contact (`contact.php`)
  - Language Switcher (`lang_switch_url('ar')` / `lang_switch_url('en')`)
  - Auth CTAs: Sign In (`login.php`), Register (`register.php`)
- **Authenticated Patient View (`!auth_is_admin()`):**
  - Brand wordmark/logo
  - Links: Home (`index.php`), About (`about.php`), Services (`services.php`), Dentists (`doctors.php`), Book Appointment (`booking.php`), My Appointments (`patient/appointments.php`), My Profile (`patient/profile.php`)
  - Language Switcher
  - Account Dropdown (`nav_account`): Dashboard (`patient/index.php`), My Appointments (`patient/appointments.php`), Profile (`patient/profile.php`), Security (`patient/security.php`), Sign Out (CSRF-protected POST form to `logout.php`), plus direct Sign Out button
- **Authenticated Admin on Public Pages (`auth_is_admin()`):**
  - Public links + Language Switcher + Admin Platform button (`admin/index.php`) + CSRF-protected Sign Out form

### 2.2 Public Header Integration (`templates/public/header.php`)
- Updated `<body>` classes to `d-flex flex-column min-vh-100`.
- Delegated `<nav>` rendering directly to `templates/partials/navbar.php` so public pages automatically adapt when a guest logs in as a patient or admin.

### 2.3 Patient Portal Full-Width Refactor (`templates/patient/layout_start.php`, `templates/patient/layout_end.php`)
- Completely removed `.portal-shell`, `<aside id="portalSidebar">`, `#portalSidebarToggle`, `#portalSidebarClose`, and `.portal-main`.
- Enforced `$portalUser = $portalUser ?? auth_require_patient();` inside `layout_start.php` alongside `auth_require_patient()` at the top of `patient/index.php`, `patient/appointments.php`, `patient/profile.php`, `patient/security.php`, and `patient_home.php`.
- Included `templates/public/header.php` (rendering the Patient Top Navbar), added a clean luxury full-width banner (`patient_portal_badge` + localized page title + quick action button to `booking.php`), opened `<main class="flex-grow-1 bg-ivory py-4 py-lg-5"><div class="container">`, and closed cleanly via `templates/public/footer.php` in `layout_end.php`.

### 2.4 Admin Sidebar Localization & Role Separation (`templates/admin/layout_start.php`, `admin/*`)
- Preserved the dedicated Admin Sidebar exclusively in `templates/admin/layout_start.php` (`admin/*` pages protected by `auth_require_admin()`).
- Updated the Admin sidebar profile role badge and `admin/index.php` / `admin/users.php` role badges & dropdown options to use `translate_role()`.
- Updated the Admin sidebar bottom site link to `_e('nav_return_site')`.

### 2.5 Navigation CSS Polish (`assets/css/luxury.css`)
- Refined `.luxury-nav-link` (`font-size: 0.9rem; padding: 0.5rem 0.7rem !important; white-space: nowrap;`) and `.luxury-nav-link.active::after` (`inset-inline: 0.7rem;`) to prevent multi-word Arabic and English navigation labels from wrapping onto two lines across desktop `xl` viewports.

### 2.6 Legacy Fragment Files Normalized
- Replaced outdated fragment files (`header.php`, `header_patient.php`, `footer.php`, `booking_header.php`) with safe redirects routing through current bootstrap guards rather than rendering broken unstyled HTML or legacy sidebars.

---

## 3. STATIC CODE REVIEW FINDINGS

Read-only `Select-String` search across `patient\*.php`, `templates\patient\*.php`, `templates\public\*.php`, `templates\partials\*.php`, and root `*.php` for legacy sidebar identifiers (`portalSidebar|portal-shell|admin-sidebar|portal-main`):
- **Total Matches Found in Patient / Public Templates:** **0** (`SIDEBAR_HITS_IN_PATIENT_PUBLIC=0`).
- **Admin Templates:** Vertical sidebar markup exists solely inside `templates/admin/layout_start.php` (`auth_require_admin()`).

---

## 4. PREPARED / EXISTING SQL ARTIFACTS (UNEXECUTED)

- `database/002_schema_upgrade.sql` enforces clean `users.role` distinction (`'admin'` vs `'user'`) backing role-based navigation branching in `auth_is_admin()`. *(Not executed in this static-only pass.)*

---

## 5. ITEMS REQUIRING FUTURE RUNTIME TESTING

All visual, responsive, and interactive behaviors below are **`NOT RUNTIME TESTED — STATIC CODE REVIEW ONLY`**:

- [ ] **Guest Navbar Visual & Responsive Audit** (`en` LTR & `ar` RTL across 1440px, 1200px, 992px, and 375px viewports, testing hamburger collapse) — `NOT RUNTIME TESTED — STATIC CODE REVIEW ONLY`.
- [ ] **Patient Portal Full-Width Layout & Navbar Verification** on `patient/index.php`, `patient/appointments.php`, `patient/profile.php`, and `patient/security.php` (confirming absence of sidebar, proper `.luxury-nav-link` active underline, working Account dropdown, and working POST Logout) — `NOT RUNTIME TESTED — STATIC CODE REVIEW ONLY`.
- [ ] **Admin Exclusive Sidebar & Redirect Verification** confirming Admin visits to `patient/*` route back to `admin/index.php`, while Patient visits to `admin/*` are denied — `NOT RUNTIME TESTED — STATIC CODE REVIEW ONLY`.
- [ ] **RTL Dropdown & Chevron Alignment** for the Account dropdown in `templates/partials/navbar.php` when `dir="rtl"` is active — `NOT RUNTIME TESTED — STATIC CODE REVIEW ONLY`.
