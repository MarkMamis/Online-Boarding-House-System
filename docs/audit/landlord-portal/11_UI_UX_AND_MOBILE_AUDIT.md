# Landlord Portal — UI/UX & Mobile Usability Audit

**Design Tokens:** Manrope Typography, Green Brand Accents (`#14532d`), Light Slate Surface (`#f8fafc`), Clean Cards  
**Layout:** `resources/views/layouts/landlord.blade.php`  
**Audit Scope:** Information Architecture, Visual Design, Responsive Tables, Mobile Drawer, Touch Targets  
**Date:** 2026-10-08  

---

## 1. Information Architecture & Navigation Audit

### Current Navigation Structure (14 Top-Level Items):
```
MAIN
  ├── Dashboard (/landlord/dashboard)
  └── Documents & Requirements (/landlord/documents)

MANAGEMENT
  ├── Properties (Dropdown: All, Per-Property, Add)
  └── Room Directory (Dropdown: All, Per-Property, Per-Room)

OPERATIONS
  ├── Boarding Requests (/landlord/bookings) [Badge]
  ├── Messages (/landlord/messages)
  ├── Feedback (/landlord/feedback)
  ├── Tenants (/landlord/tenants)
  ├── Onboarding (/landlord/onboarding) [Badge]
  ├── Leave Requests (/landlord/leave-requests)
  └── Maintenance (/landlord/maintenance)

FINANCE
  ├── Payments (/landlord/payments)
  └── Analytics (/landlord/analytics)

ACTIVITY
  └── Notifications (/notifications) [Badge]

ACCOUNT
  ├── Profile (/landlord/profile)
  └── Logout (/logout)
```

### Usability Evaluation:
1. **High Visual Density:** 14 distinct navigation links create significant visual clutter for boarding house owners, many of whom are non-technical.
2. **Excessive Nested Accordions:** The sidebar dynamically renders nested accordions for every property and every individual room inside the sidebar (`layouts/landlord.blade.php:611–658`). For landlords with 10+ rooms, the sidebar becomes hundreds of lines tall and difficult to navigate.
3. **Recommended Streamlining:** Group into 5 operational clusters:
   - **Overview:** Dashboard, Attention Queue
   - **Portfolio:** Properties, Room Directory
   - **Operations:** Bookings, Tenants, Onboarding, Leave Requests, Messages
   - **Finance:** Payments, Analytics
   - **Compliance & Account:** Documents, Profile, Settings

---

## 2. Mobile Experience Audit

### A. Current Mobile Navigation Architecture:
- Uses a slide-out Bootstrap offcanvas drawer (`#landlordSidebar`) triggered by the top burger button (`layouts/landlord.blade.php:526–535`).
- Unlike the Admin Portal (which has a dedicated full-page mobile nav screen and bottom mobile bar), the Landlord Portal relies entirely on the slide-out offcanvas drawer.

### B. Mobile Findings & Usability Friction:
1. **Table Overflow on Narrow Screens:**
   - Pages like `/landlord/payments`, `/landlord/tenants`, and `/landlord/properties` use dense multi-column Bootstrap tables (`table-responsive`).
   - On mobile screens (< 420px), users must scroll horizontally back and forth to inspect tenant information and reach action buttons.
2. **Form Usability on Mobile:**
   - Multi-field forms like `properties/create.blade.php` and `rooms/create.blade.php` contain dense multi-tab layouts with small checkbox lists for amenities and house rules.
   - Stepped forms or accordion-card layouts would significantly reduce mobile cognitive load.
3. **Touch Targets:**
   - Desktop icon buttons (`icon-btn`, 34x34px) are slightly smaller than the recommended 44–48px mobile touch target guideline.

---

## 3. Design System Consistency

- **Strengths:** 
  - Consistent color palette (MinSU Green `--brand: #14532d`, Mint `--mint: #a7f3d0`).
  - Standardized card shells (`glass-card`, `metric-tile`).
  - Cohesive badge styles across status indicators.
- **Weaknesses:**
  - Inconsistent button sizes across pages (some pages use `btn-sm`, others use `btn-md` with custom padding).
  - Empty states in some views lack actionable call-to-action buttons (e.g. "Add your first room").
