# Visual Design System: Brand Tokens, Component Styling & UI Restraint

**Audit Date:** October 18, 2026  
**Scope:** Review of CSS Design Tokens, Component Consistency, Typography, and University Branding  

---

## 1. Brand Tokens & Color Palette

The OBHS application is anchored to the official institutional identity of **Mindoro State University (MinSU)**. The visual system pairs a collegiate emerald green with crisp neutrals.

### Active Color Tokens:
```css
:root {
    /* Brand Green Spectrum */
    --brand: #14532d;         /* Deep Forest/Emerald Green (MinSU primary) */
    --brand-dark: #166534;    /* Hover State Deep Green */
    --brand-light: #dcfce7;   /* Emerald-100 Soft Tint for badges/chips */
    --brand-rgb: 20, 83, 45;

    /* Secondary Accents */
    --gold: #f59e0b;          /* MinSU Gold / Star Ratings */
    --premium-emerald: #10b981;

    /* Neutrals & Shell */
    --ink: #0f172a;           /* Primary text / Slate-900 */
    --muted: #64748b;         /* Secondary text / Slate-500 */
    --line: #e2e8f0;          /* Subtle card & divider borders */
    --surface: #ffffff;       /* Card backgrounds */
    --shell: #f8fafc;         /* Page canvas / Warm Slate-50 */
}
```

### Visual Audit:
- **Strengths:** The deep green (`#14532d`) is authoritative, distinctive, and aligns well with MinSU's agricultural and environmental heritage. Contrast on white surfaces exceeds WCAG AAA (contrast ratio > 9.5:1).
- **Weaknesses:** Inconsistent secondary tints. Some views use teal gradients (`linear-gradient(135deg, #065f46 0%, #0f766e 100%)`), others use translucent green overlays (`rgba(22,101,52,.1)`), while older views still hold legacy cyan tokens (`--brand: #0ea5a3`).

---

## 2. Typography

- **Font Family:** `Manrope`, system-ui, -apple-system, sans-serif.
- **Heading Styles:** `font-weight: 700` and `800`, with tight letter spacing (`letter-spacing: -0.02em`).
- **Audit:**
  - Manrope is modern, geometric, and readable on high-density mobile screens.
  - However, font sizes across headers are inconsistent. For instance, `student/rooms/index.blade.php` uses `font-size: clamp(1.55rem, 2.7vw, 2.3rem)` with `font-weight: 850`, whereas `student/bookings/index.blade.php` uses standard Bootstrap `h3.fw-bold`.

---

## 3. Card & Container Proliferation

One of the primary causes of visual clutter is the coexistence of **at least 6 different card container classes** across student views:

1. `.glass-card` (uses `backdrop-filter: blur`, white background, `box-shadow: 0 10px 26px rgba(2,8,20,.08)`).
2. `.section-card` (used in `dashboard.blade.php` with custom header borders).
3. `.metric-tile` (semi-transparent white with grey border and icon box).
4. `.room-browse-card` (heavy hover lift and shadow).
5. `.request-card` (plain white card with multiple sub-grid metrics).
6. `.tenant-pay-card` (card with grey header band).

### The "Boxes Inside Boxes" Anti-Pattern:
Currently, almost every view nests 3 to 4 layers of borders:
`Main Layout Shell -> Glass Card Container -> Section Card -> Metric Sub-grid Item -> Badge Chip`.
This creates a "caged" appearance that makes screens feel crowded and heavy.

---

## 4. Proposed Design System Refinement

To achieve a modern consumer hospitality look (similar to Airbnb and modern boutique booking engines) while preserving MinSU identity:

1. **Eliminate Unnecessary Nested Borders:** Use subtle background contrast (`#ffffff` card on `#f8fafc` canvas) and soft elevation (`box-shadow: 0 2px 8px rgba(15,23,42,.04)`) instead of hard 1px grey borders everywhere.
2. **Standardize Card Corners:** Standardize on `border-radius: 1rem` (16px) for major cards, and `0.75rem` (12px) for interactive pills.
3. **Restrain Badges:** Cap cards at a maximum of **2 status badges** (e.g. 1 primary tag + 1 rating pill). Avoid badge salads.
4. **Photo First:** Prioritize authentic, full-bleed or soft-rounded photography over icon illustrations.
