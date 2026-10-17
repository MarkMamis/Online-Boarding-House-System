# OBHS Landlord Portal: Mobile Responsiveness & Design System Audit

**Document ID:** `12_MOBILE_AND_VISUAL_CONSISTENCY.md`  
**Date:** October 18, 2026  
**Auditor:** Accessibility Specialist & Senior Frontend Engineer  
**Focus:** Viewport Breakpoints (320px–1024px), Touch Usability, Design Tokens, and Visual Polish.

---

## 1. Responsive Viewport Stress Test (320px to Desktop)

Because over 75% of landlords access OBHS via mobile browsers, the interface was evaluated across seven standard responsive breakpoints:

| Viewport Width | Typical Target Device | Observed Issues in Current Portal | Usability Impact |
| :--- | :--- | :--- | :--- |
| **320px** | iPhone SE (1st gen) / Budget Android | • Table horizontally scrolls over 600px with no indicator.<br>• Header "Add Property", "Add Room", "Requests" buttons wrap into 3 awkward vertical rows.<br>• Modal footer buttons clip off-screen. | **Severe:** Critical actions like "Confirm Payment" require side-scrolling. |
| **360px** | Samsung Galaxy A-series | • Setup wizard stepper nodes overlap and truncate text.<br>• Quick Modules grid has awkward 1-column card padding. | **Moderate:** High visual friction and cramped touch targets. |
| **390px** | iPhone 12/13/14/15 Pro | • Sidebar drawer opens with 20+ room accordion links, forcing excessive scrolling.<br>• Signature canvas in modal does not handle high-DPI scaling smoothly. | **Moderate:** Unnecessary scroll depth to reach settings or logout. |
| **430px** | iPhone 14/15 Pro Max / Galaxy S24+ | • Card borders and shadows look acceptable, but 5-column tables still require horizontal swiping. | **Mild:** Standard table overflow. |
| **768px** | iPad Mini / Portrait Tablets | • Offcanvas drawer activates, but main column margins leave excessive empty gutters. | **Mild:** Layout spacing imbalance. |
| **1024px** | iPad Pro / Small Laptops | • Fixed sidebar (`width: 264px`) takes 25% of viewport width, compressing main content tables into cramped horizontal space. | **Moderate:** Information density feels cramped. |
| **Desktop** | Standard 1080p Desktop Displays | • Excessive whitespace in forms; single input fields span 900px wide unnecessarily. | **Mild:** Cosmetic stretching. |

---

## 2. Key Mobile Defects & Concrete Remedies

### Defect 1: Wide Desktop Tables on Narrow Devices
- **Current State:** Pages like `/landlord/payments`, `/landlord/bookings`, and `/landlord/tenants` render traditional HTML `<table>` elements with 6 to 8 columns (Student, Room, Dates, Amount, Due Date, Status, Actions).
- **The Mobile Fix:** Implement responsive CSS card stacking:
  - On screens `< 768px`, table rows transform into self-contained cards:
    - Top line: Student Name + Room Number badge.
    - Middle line: Stay dates and Monthly Rent.
    - Bottom line: Big, accessible primary action button (e.g. `[ Confirm Payment ]`).

### Defect 2: Cramped Touch Targets (< 44px)
- **Current State:** Many table action buttons use `.btn-sm` with heights of 28px to 32px.
- **Accessibility Standard (WCAG 2.1 AA):** Touch targets must measure at least **44 × 44 pixels** to prevent accidental mis-taps on mobile touchscreens.
- **Fix:** Enforce minimum button heights of 44px for primary operational controls on mobile viewports.

### Defect 3: Digital Signature Pad Usability on Touch
- **Current State:** On mobile devices, touching the signature canvas can trigger page scrolling instead of drawing ink.
- **Fix:** Add CSS touch-action suppression to signature canvases:
  ```css
  .signature-pad-canvas {
      touch-action: none;
      user-select: none;
  }
  ```

---

## 3. MinSU Design System & Visual Hierarchy Standards

To align with the MinSU identity and maintain visual harmony with the Student Portal redesign:

### Design Tokens:
- **Primary Brand Green:** `#14532d` (Deep MinSU Forest Green) — Used for primary CTAs and active states.
- **Primary Brand Hover:** `#166534` (Mid Forest Green).
- **Mint Accent:** `#a7f3d0` (Soft Mint Green) — Used for active selection highlights and background tints.
- **Surface Background:** `#ffffff` (Crisp White).
- **Shell Background:** `#f8fafc` (Slate Off-White).
- **Border Line:** `#e2e8f0` (Subtle 1px gray borders; avoid heavy black outlines).
- **Typography:** `Manrope`, `'Segoe UI'`, sans-serif. Highly readable at small font sizes (14px–16px).

### Microcopy and Tone:
- **Tone:** Professional, encouraging, respectful, and plain-spoken.
- **Never Use Developer Jargon:**
  - ❌ *"Execute State Transition"* -> ✔️ *"Confirm Payment"*
  - ❌ *"Tenant Onboarding Pipeline"* -> ✔️ *"Move-In Progress"*
  - ❌ *"Pending Admin Accreditation"* -> ✔️ *"Waiting for MinSU Admin Review"*
  - ❌ *"Inventory Unit Unavailable"* -> ✔️ *"Room Occupied"*
