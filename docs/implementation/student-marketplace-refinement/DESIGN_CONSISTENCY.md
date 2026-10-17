# Design Consistency & Component Standards
**Second Pass Refinement — OBHS Student & Tenant Marketplace**

This document specifies the unified design tokens, button hierarchies, and typography standards applied across the Student and Tenant experience.

---

### 1. Typography & Hierarchy Standards

| Context | Typography Style | Purpose |
| :--- | :--- | :--- |
| **Page Title** | `h3` (`1.75rem`), Bold 700, `#0f172a` (Slate-900) | Primary view anchor across Home, Explore, Bookings, Current Stay. |
| **Section Subtitle** | `small` (`0.875rem`), Regular 400, `#64748b` (Slate-500) | Supportive context beneath every page heading. |
| **Card Accommodation Title** | `h6` (`1rem`), Bold 700, `#0f172a` | Clean, scannable property name on listing cards. |
| **Price Headline** | `fs-5` (`1.25rem`), Bold 700, `#0f172a` | High visual weight for monthly rent. |
| **Price Cadence Unit** | `small` (`0.85rem`), Regular 400, `#64748b` | Explicit unit (`/ month per bed` or `/ month whole room`). |

---

### 2. Button & Control Standards

- **Primary Actions:** Forest Green (`btn-success` with `#14532d`), rounded pill (`rounded-pill`), padding `px-4 py-2`, font-weight 600. Used for "Search", "Apply Filters", "Continue Move-in", "Submit Request".
- **Secondary Actions:** Neutral Outline (`btn-outline-dark` or `btn-outline-secondary`), rounded pill (`rounded-pill`), padding `px-3.5 py-2`. Used for "Room Details", "Filters", "Reset".
- **Touch Target Assurance:** All interactive buttons maintain a minimum hit area of 44px on mobile devices.

---

### 3. Surface & Card Design

- **Canvas Background:** `#f8fafc` (Slate-50 neutral canvas).
- **Cards:** Pure white `#ffffff`, `border-0`, `shadow-sm`, with soft rounded corners (`rounded-4` = 16px radius).
- **Listing Hover Effect:** Soft vertical elevation (`translateY(-3px)`) with subtle shadow expansion (`0 10px 25px -5px rgba(0,0,0,0.06)`).
