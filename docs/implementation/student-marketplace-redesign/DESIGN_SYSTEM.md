# Student Portal Design System & Visual Tokens

This document specifies the design tokens, typography, surfaces, and component standards established for the OBHS Student Marketplace transformation.

---

## 1. Color Palette & Semantics

The palette honors the Mindoro State University (MinSU) identity using a rich forest-green primary accent against clean, accessible neutral surfaces.

```css
:root {
    /* Brand Primary Accent */
    --minsu-brand: #14532d;         /* Deep Forest Green (MinSU Identity) */
    --minsu-brand-hover: #166534;   /* Forest Green hover */
    --minsu-brand-light: #f0fdf4;   /* Pale mint tint for pill backgrounds */
    --minsu-brand-border: #bbf7d0;  /* Mint border */

    /* Neutral Canvas & Surfaces */
    --canvas-bg: #f8fafc;           /* Clean, modern off-white background */
    --surface-card: #ffffff;        /* Pure white for listing cards & panels */
    --border-subtle: #e2e8f0;       /* Restrained card & divider borders */
    --border-focus: #14532d;

    /* Typography / Ink */
    --text-primary: #0f172a;        /* Deep slate charcoal for titles & prices */
    --text-secondary: #475569;      /* Accessible secondary text (AA compliant) */
    --text-muted: #64748b;          /* Meta notes, timestamps, sub-labels */

    /* Semantic Feedback */
    --feedback-emerald: #10b981;    /* Verified status, success */
    --feedback-amber: #f59e0b;      /* Pending reviews, limited slots */
    --feedback-rose: #ef4444;       /* Urgency, rejected requests */
}
```

---

## 2. Typography Hierarchy

The portal standardizes on **Manrope** (with fallback to system sans-serif) for high legibility on mobile screens and modern geometric balance.

| Element | Size / Weight | Line Height | Usage |
| :--- | :--- | :--- | :--- |
| **Hero Display Title** | `2.25rem` (36px), Bold 700 | 1.2 | Student Home search hero ("Find your next place near MinSU") |
| **Section Headings (H2)**| `1.5rem` (24px), Semi-bold 600| 1.3 | Catalog sections, featured listings |
| **Card Property Title** | `1.05rem` (17px), Semi-bold 600| 1.25 | Accommodation name on room cards |
| **Card Price Headline** | `1.25rem` (20px), Bold 700 | 1.2 | Prominent monthly price |
| **Body / Description** | `0.95rem` (15px), Regular 400 | 1.5 | Room descriptions, stay details |
| **Meta / Badge Labels** | `0.75rem` (12px), Medium 500 | 1.0 | Capacity pills, category chips, status tags |

---

## 3. Micro-Interactions & Elevation

- **Card Hover:** `transform: translateY(-3px)` with transition `all 0.25s cubic-bezier(0.16, 1, 0.3, 1)`. Shadow expands to `0 12px 24px -6px rgba(0, 0, 0, 0.08)`.
- **Category Filter Chips:** Rounded pill (`border-radius: 9999px`) with 1px border. Active state: `#14532d` background with white text; Inactive state: `#ffffff` background with `#475569` text.
- **Search Hero Input:** Floating rounded-pill search box with inset glass border and search trigger button (`btn-success` with `#14532d`).

---

## 4. Accessibility Standards (WCAG 2.1 AA)

- **Contrast Ratios:** All text elements exceed the 4.5:1 minimum contrast requirement on their respective surfaces (Slate-900 `#0f172a` on White has a 16.5:1 contrast ratio; Forest Green `#14532d` on White has 9.8:1).
- **Interactive Targets:** Listing card hit targets occupy full card bounds. Filter chips maintain minimum touch target heights of `40px` on mobile.
- **Form Controls:** All filter inputs, price range sliders, and search inputs have explicit labels and semantic ARIA attributes.
