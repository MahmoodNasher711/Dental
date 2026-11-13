# DENTAL — LUXURY DESIGN SPECIFICATION
**Brand Identity, Bilingual Design System & Experience Architecture**

**Document Version:** `2.1.0-VALIDATED`  
**Brand:** `DENTAL`  
**Brand Positioning:** Luxury Dental Care. Exceptional Smiles.  
**Tagline (EN):** Luxury Dental Care. Exceptional Smiles.  
**Tagline (AR):** رعاية أسنان فاخرة. ابتسامة استثنائية.  
**Default Language:** English (`en`, LTR) with instant toggle to Arabic (`ar`, RTL)  
**Status:** VALIDATED ARCHITECTURAL SPECIFICATION — DOCUMENTATION ONLY  

---

## 1. Brand Identity & Positioning

### 1.1 Brand Philosophy
`DENTAL` is conceived as an ultra-premium, internationally presentable commercial dental clinic brand. It departs completely from generic, hospital-template aesthetics, establishing an atmosphere of quiet luxury, clinical rigor, and concierge healthcare.

```
       ✦ LUXURY & EXCLUSIVITY     — Bespoke patient journeys, calm hospitality ambiance
       ✦ MEDICAL MASTERY          — Evidence-based dentistry, verified practitioner profiles
       ✦ TRUST & DATA PRIVACY     — Patient confidentiality, strict isolation, transparent fees
       ✦ BILINGUAL EQUIVALENCE    — Identical functionality and native typographic elegance in AR & EN
```

### 1.2 Truth-in-Information Standards
- **Zero Fabricated Content**: No invented clinical certifications, awards, patient volume counters, or unverified satisfaction percentages.
- **Genuine Database Content**: Grounded exclusively in verified database records from `appointment_system.sql` (verified dentists Dr. Sara'a, Dr. BASSAM, Dr. Ali, verified treatments, and actual clinic introduction).
- **Explicit Placeholders**: Any real-world operational information not present in the database dump is formally designated as `[OWNER_INPUT_REQUIRED]`.

---

## 2. Luxury Design System & Color Tokens

### 2.1 Color Palette & Semantic Tokens
The palette evokes restorative calm: deep sanctuary emeralds paired with refined champagne gold accents on a warm ivory canvas.

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                           DENTAL CORE COLOR PALETTE                         │
├──────────────────┬──────────────────┬──────────────────┬────────────────────┤
│ Deep Emerald     │ Forest Green     │ Champagne Gold   │ Soft Gold          │
│ #123D36          │ #1B5146          │ #C6A46A          │ #DCC69D           │
│ (Primary Dark)   │ (Primary Mid)    │ (Accent Gold)    │ (Gold Highlight)   │
├──────────────────┼──────────────────┼──────────────────┼────────────────────┤
│ Warm Ivory       │ Pure White       │ Charcoal         │ Muted Gray         │
│ #FAF8F3          │ #FFFFFF          │ #252927          │ #777C78            │
│ (Page Canvas)    │ (Surface White)  │ (Text Primary)   │ (Text Muted)       │
└──────────────────┴──────────────────┴──────────────────┴────────────────────┘
```

### 2.2 CSS Variable Definitions (`assets/css/variables.css`)
```css
:root {
  /* Brand Core */
  --dental-emerald-deep:    #123D36;
  --dental-emerald-forest:  #1B5146;
  --dental-emerald-light:   #236557;
  --dental-gold-champagne:  #C6A46A;
  --dental-gold-soft:       #DCC69D;
  --dental-gold-muted:      #EBDDC2;
  --dental-gold-dark:       #A3834C;

  /* Surfaces & Canvas */
  --dental-canvas-ivory:    #FAF8F3;
  --dental-surface-white:   #FFFFFF;
  --dental-border-subtle:   #E8E4DC;
  --dental-border-gold:     rgba(198, 164, 106, 0.35);

  /* Typography & Ink */
  --dental-text-primary:    #252927;
  --dental-text-secondary:  #4E5450;
  --dental-text-muted:      #777C78;
  --dental-text-inverted:   #FAF8F3;

  /* Functional Status */
  --dental-status-confirmed: #1B5146;
  --dental-status-pending:   #C6A46A;
  --dental-status-cancelled: #A03838;
  --dental-status-completed: #2A6E5F;

  /* Elevations */
  --dental-shadow-sm:   0 2px 8px rgba(18, 61, 54, 0.04);
  --dental-shadow-md:   0 8px 24px rgba(18, 61, 54, 0.08);
  --dental-shadow-lg:   0 16px 40px rgba(18, 61, 54, 0.12);
  --dental-shadow-gold: 0 8px 24px rgba(198, 164, 106, 0.20);

  /* Radii */
  --dental-radius-sm: 4px;
  --dental-radius-md: 8px;
  --dental-radius-lg: 16px;
  --dental-radius-full: 9999px;

  /* Motion */
  --dental-transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}
```

---

## 3. Dual Typography System (English Default & Arabic RTL)

### 3.1 Font Selection & Hierarchy
- **English Default**:
  - Headings / Display: `Cormorant Garamond` (Google Fonts) — Editorial, high-contrast serif.
  - Body / Micro-UI: `Plus Jakarta Sans` (Google Fonts) — Clean, geometric sans-serif.
- **Arabic Equivalent**:
  - Display & Headings: `Tajawal` (Google Fonts) — Modern, authoritative Kufic/Naskh proportions.
  - Body & UI Controls: `Cairo` (Google Fonts) — High legibility across all screen densities.

```css
/* Typography Scale & Family Rules */
:root {
  --dental-font-en-serif: 'Cormorant Garamond', 'Playfair Display', Georgia, serif;
  --dental-font-en-sans:  'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
  --dental-font-ar:       'Tajawal', 'Cairo', 'Segoe UI Arabic', sans-serif;
}

html[lang="en"] {
  font-family: var(--dental-font-en-sans);
  direction: ltr;
}
html[lang="en"] h1, 
html[lang="en"] h2, 
html[lang="en"] h3, 
html[lang="en"] .luxury-heading {
  font-family: var(--dental-font-en-serif);
  font-weight: 600;
  letter-spacing: -0.01em;
}

html[lang="ar"] {
  font-family: var(--dental-font-ar);
  direction: rtl;
  letter-spacing: normal;
}
html[lang="ar"] h1, 
html[lang="ar"] h2, 
html[lang="ar"] h3, 
html[lang="ar"] .luxury-heading {
  font-family: var(--dental-font-ar);
  font-weight: 700;
  line-height: 1.4;
}
```

---

## 4. UI Component Library & Visual Language

### 4.1 Buttons & Action Controls
- **Primary Luxury Action (`.btn-luxury-primary`)**:
  - Background: Deep Emerald (`#123D36`), Border: Champagne Gold (`#C6A46A`, 1px).
  - Text: Warm Ivory (`#FAF8F3`), Font: 14px bold with `0.15em` letter-spacing (EN).
  - Hover: Forest Green (`#1B5146`), Box-shadow: `0 8px 24px rgba(18, 61, 54, 0.25)`.
- **Secondary Gold Outline (`.btn-luxury-outline`)**:
  - Background: Transparent, Border: Champagne Gold (`#C6A46A`, 1px).
  - Text: Deep Emerald (`#123D36`).
  - Hover: Background Champagne Gold (`#C6A46A`), Text: Deep Emerald (`#123D36`).
- **RTL Directional Transformation**:
  - Directional icons (arrows, chevrons) automatically mirror via CSS:
    ```css
    [dir="rtl"] .arrow-icon {
      transform: scaleX(-1);
    }
    ```

### 4.2 Luxury Cards (`.luxury-card`)
- Solid pure white surface (`#FFFFFF`) with subtle 1px border (`#E8E4DC`).
- Restrained 8px corner radius (`--dental-radius-md`).
- Elevation on hover: `--dental-shadow-md` with top-border illumination in Champagne Gold.

### 4.3 Form Controls & Slot Pickers
- Minimalist Warm Ivory input backgrounds with subtle border.
- On focus: Forest Green border with soft Gold outer glow (`box-shadow: 0 0 0 3px rgba(198, 164, 106, 0.18)`).
- Time-slot selector chips: Toggleable radio pills displaying available clinical times, disabling booked slots with strike-through styling.

---

## 5. Bilingual Architecture & Localization

### 5.1 Language Resolution Flow
- **Default Language**: English (`en`).
- **Session & Cookie Persistence**:
  1. Checks `$_GET['lang']`. If provided and valid (`en` or `ar`), sets `$_SESSION['lang']` and cookie `dental_lang` (30 days, `SameSite=Lax`).
  2. Falls back to `$_SESSION['lang']`, then cookie `dental_lang`, then default `'en'`.
  3. Sets HTML attributes: `<html lang="en" dir="ltr">` or `<html lang="ar" dir="rtl">`.
  4. Conditionally includes `bootstrap.min.css` (LTR) or `bootstrap.rtl.min.css` (RTL).

### 5.2 Localization Helper Specifications (`includes/localization.php`)
```php
function __($key, $replacements = []) {
    global $translations;
    $text = $translations[$key] ?? $key;
    foreach ($replacements as $placeholder => $value) {
        $text = str_replace(':' . $placeholder, $value, $text);
    }
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

function _e($key, $replacements = []) {
    echo __($key, $replacements);
}
```

---

## 6. Public Website — Page Specifications

### 6.1 Luxury Homepage (`index.php`)
1. **Top Information Bar**:
   - Operating Hours: `[OWNER_INPUT_REQUIRED: CLINIC_OPERATING_HOURS]` (e.g. Saturday–Thursday, 9:00 AM – 9:00 PM).
   - Contact Telephone: `[OWNER_INPUT_REQUIRED: VERIFIED_PHONE_NUMBER]`.
   - Language Switcher: Simple pill toggle (`English` | `العربية`).
2. **Global Navigation Bar**:
   - Brand Wordmark: `DENTAL` with gold diamond emblem.
   - Links: Home, About Us, Services, Specialists, Contact.
   - Action: "Patient Portal" link + "Book Appointment" luxury button.
3. **Hero Editorial Section**:
   - Left Column: Champagne badge ("EXCELLENCE IN MODERN DENTISTRY"), Editorial headline ("Sculpting Timeless Smiles with Artistry & Precision"), brief introductory narrative, dual CTAs ("Reserve Your Visit" & "Explore Clinical Services").
   - Right Column: High-resolution architectural photography of clinical suite with floating credential card for lead doctor.
4. **Verified Clinical Services Section**:
   - Cards dynamically generated from database table `services`:
     * Service 1: Cleaning (`14$`)
     * Service 2: Surgery (`50$`)
     * Additional services: `[OWNER_INPUT_REQUIRED: EXPANDED_SERVICE_CATALOG]`
5. **Verified Medical Team Showcase**:
   - Profiles dynamically populated from database table `dentists`:
     * Dr. Sara'a (Cosmetic Dentistry Specialist, 20+ years field experience, Photo: `imgs/1.jpg`).
     * Dr. BASSAM (Orthodontist Specialist, Photo: `imgs/2.jpg`).
     * Dr. Ali (Family Dentistry Specialist, Fluent EN/ES, Photo: `imgs/4.jpg`).
6. **Patient Assurance Pillars**:
   - Three core clinical commitments: Digital Precision Imaging, Strict Autoclave Sterilization, and Private Concierge Suites.
7. **Reservation Invitation Banner & Footer**:
   - Deep emerald banner inviting appointment reservations.
   - Multi-column luxury footer with working navigation links, verified copyright ("Copyright © 2026 DENTAL. All rights reserved."), and privacy statement.

### 6.2 About Us (`about.php`)
- Genuine clinic introduction sourced directly from database table `about_us`.
- Medical philosophy emphasizing conservative, minimally invasive dental procedures.
- Clean presentation of sterilization and patient safety standards.

### 6.3 Services Catalog (`services.php`)
- Presentation of clinical treatments with transparent fees from table `services`.
- Each service features a direct "Book This Service" CTA pre-selecting the service in the booking engine.

### 6.4 Specialists Directory (`doctors.php` & `dentist_info.php`)
- Profiles of Dr. Sara'a, Dr. BASSAM, and Dr. Ali.
- Biography (`dentist_brief`), clinical focus, and dedicated doctor reservation CTA.

### 6.5 Guided Appointment Reservation (`booking.php`)
- Multi-step guided luxury booking interface:
  - Step 1: Select Treatment Service (from table `services`).
  - Step 2: Choose Specialist Doctor (from table `dentists`).
  - Step 3: Interactive Date & Available Time Slot (concurrency-protected).
  - Step 4: Patient Information (auto-filled if authenticated).
  - Step 5: Immediate Confirmed Reservation Receipt with appointment reference code.

### 6.6 Contact Us (`contact.php`)
- Contact form submitting securely to table `contact_us` via prepared statement.
- Verified clinic contact details: `[OWNER_INPUT_REQUIRED: CLINIC_ADDRESS, PHONE, EMAIL]`.
- Interactive map iframe placeholder: `[OWNER_INPUT_REQUIRED: GOOGLE_MAPS_EMBED_URL]`.

---

## 7. Patient Concierge Portal Specification

### 7.1 Architecture & Security Rules
- Isolated under `/patient/`.
- Pre-execution guard: `auth_require_login()`.
- **Tenant Isolation**: Every SQL query strictly enforces `WHERE user_id = :session_user_id`. Direct Object Reference (IDOR) on appointment IDs is blocked.

### 7.2 Portal Views
1. **Dashboard Overview (`patient/index.php`)**:
   - Welcome banner with patient name.
   - Next Upcoming Appointment highlight card with doctor name, date, time, and status.
   - Quick "Book New Appointment" button.
2. **My Appointments Schedule (`patient/appointments.php`)**:
   - Categorized tabs: "Upcoming", "Completed", "Cancelled".
   - Cards display appointment date, scheduled time, doctor, service fee, and status pill.
   - "Cancel Appointment" button with confirmation modal (only available for future appointments).
3. **Profile & Personal Details (`patient/profile.php`)**:
   - Update telephone number (`VARCHAR(20)`), age, and email.
   - Secure profile image upload via `includes/upload.php`.
4. **Security Center (`patient/security.php`)**:
   - Change password form validating current password via `password_verify()`.
   - New password hashed via Bcrypt (`PASSWORD_DEFAULT`).
   - Zero DOM password hash leakage.

---

## 8. Administrative Platform Specification

### 8.1 Visual Identity
- Refined dark-sidebar workspace layout: Deep Emerald sidebar (`#123D36`), Champagne Gold active indicators, Warm Ivory workspace background (`#FAF8F3`).
- Fully responsive on desktop, tablet, and mobile.

### 8.2 Operational Modules
1. **Executive Dashboard (`admin/index.php`)**:
   - KPI metric cards: Total Patients, Total Dentists, Total Appointments, Today's Bookings.
   - Real-time schedule table for today's visits.
   - Quick status transition buttons ("Check-In", "Complete", "Cancel").
2. **Appointment Operations (`admin/appointments.php`)**:
   - Registry with multi-column filtering by date, doctor, and status.
   - Reschedule modal with server-side conflict detection.
3. **Patient Registry (`admin/patients.php`)**:
   - Complete directory of patients, contact details, and booking history.
   - Parameterized PDO search replacing previous SQL injection vulnerability.
4. **Dentist Management (`admin/dentists.php`)**:
   - Specialist CRUD: Name, age, email, brief, and validated photo upload.
5. **Services Management (`admin/services.php`)**:
   - Procedure catalog and fee schedule management.
6. **Patient Inquiries Inbox (`admin/messages.php`)**:
   - View, review, and mark patient contact inquiries as read.
7. **Clinic Settings (`admin/settings.php`)**:
   - Configure working days, slot intervals (e.g., 30/60 min), and emergency contact numbers.

---

## 9. Final Engineering Review & Open Decisions

The following business parameters and content elements cannot be verified from the database and require explicit input from the clinic owner:

| Parameter | Current Status in Database | Required Action / Owner Decision |
| :--- | :--- | :--- |
| **Clinic Operating Hours** | Not present in schema or dump | Clinic owner must specify regular weekly working hours and weekend schedules. |
| **Verified Contact Telephone** | Old footer contains placeholder `+967 777 000 999` | Clinic owner must confirm the official commercial clinic telephone number. |
| **Official Email Address** | User table contains developer/admin emails | Clinic owner must confirm the public-facing contact email address. |
| **Physical Clinic Address** | Not present in database | Clinic owner must provide the street address, building, city, and optional Google Maps embed link. |
| **Expanded Clinical Services** | Dump contains only 2 services (Cleaning, Surgery) | Clinic owner should review and provide additional clinical procedures and prices if desired. |
| **Appointment Slot Duration** | Not present in database | Default recommended slot interval is 45 or 60 minutes. Clinic owner must confirm preference. |
| **Appointment Cancellation Policy**| Not present in database | Specify minimum hours before appointment when patient self-cancellation is permitted (e.g. 24h prior). |

---

> [!NOTE]
> **Specification Status**: This design specification has been updated to eliminate all fabricated metrics, enforce truth-in-information, establish English as default with Arabic RTL equivalence, and document open business decisions. Implementation remains paused pending explicit authorization.
