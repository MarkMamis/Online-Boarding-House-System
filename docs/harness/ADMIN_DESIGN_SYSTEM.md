# OBHS Admin Design System Specification

## 1. Overview & Identity Anchor
The **Online Boarding House System (OBHS)** Admin Portal design system is designed for clarity, institutional trust, safety, and non-technical school administrator comfort.

- **Anchor Identity:** Mindoro State University (MinSU) Green (`#166534`)
- **Balance:** 85% neutral surfaces/backgrounds, 10% soft contextual/semantic surfaces, 5% strong action accents.
- **Aesthetic Direction:** Custom, Minimal, Calm, Institutional, Data-Driven, Predictable.

---

## 2. Design Tokens (CSS Variables)

```css
:root {
    /* Color System */
    --admin-bg: #f8fafc;
    --admin-surface: #ffffff;
    --admin-surface-subtle: #f8fafc;
    --admin-surface-hover: #f1f5f9;

    --admin-text: #0f172a;
    --admin-text-secondary: #475569;
    --admin-text-muted: #64748b;

    --admin-border: rgba(15, 23, 42, 0.08);
    --admin-border-strong: rgba(15, 23, 42, 0.16);

    --admin-primary: #166534;
    --admin-primary-hover: #14532d;
    --admin-primary-soft: rgba(22, 101, 52, 0.08);
    --admin-primary-border: rgba(22, 101, 52, 0.2);

    --admin-info: #0284c7;
    --admin-info-soft: rgba(2, 132, 199, 0.08);

    --admin-warning: #d97706;
    --admin-warning-soft: #fef3c7;

    --admin-danger: #dc2626;
    --admin-danger-soft: #fee2e2;

    --admin-success: #166534;
    --admin-success-soft: #dcfce7;

    /* Geometry & Spacing */
    --admin-radius-sm: 6px;
    --admin-radius-md: 10px;
    --admin-radius-lg: 14px;
    --admin-radius-xl: 18px;
    --admin-radius-pill: 9999px;

    /* Elevation */
    --admin-shadow-xs: 0 1px 2px rgba(15, 23, 42, 0.04);
    --admin-shadow-sm: 0 1px 3px rgba(15, 23, 42, 0.05), 0 1px 2px rgba(15, 23, 42, 0.03);
    --admin-shadow-md: 0 4px 16px rgba(15, 23, 42, 0.04), 0 1px 3px rgba(15, 23, 42, 0.02);

    /* Layout Dimensions */
    --sidebar-w: 286px;
    --sidebar-bg: #0d2818;
}
```

---

## 3. Typography Hierarchy
- **Page Titles:** 24–28px, 700 weight, deep navy charcoal (`#0f172a`), natural sentence case.
- **Page Descriptions / Subtitles:** 13–14px, 400/500 weight, muted slate (`#64748b`).
- **Section / Card Headers:** 15–17px, 600 weight, with consistent icon alignment.
- **Metric Values:** 28–34px, 700/800 weight, high contrast.
- **Table Headers:** 11–12px, 600/700 weight, soft uppercase with tracking.
- **Body & Row Text:** 13–14px, clear primary/secondary text layering.

---

## 4. Reusable Blade Components

### 4.1 `<x-admin.page-header>`
Provides standard breadcrumb hierarchy, natural-case title, explanatory subtitle, optional icon, and right-aligned action buttons/badges.
```html
<x-admin.page-header
    title="Property Reviews"
    subtitle="Review boarding house submissions requiring administrative approval."
    icon="bi-shield-check"
    :breadcrumbs="[
        ['label' => 'Management', 'url' => route('admin.dashboard')],
        ['label' => 'Property Reviews']
    ]"
>
    <x-slot name="actions">
        <a href="{{ route('admin.properties.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-buildings me-1"></i>Boarding Houses
        </a>
    </x-slot>
</x-admin.page-header>
```

### 4.2 `<x-admin.metric-card>`
Provides uniform KPI & operational count presentation across Monitoring and Management.
- Variants: `brand`, `warning`, `danger`, `info`, `neutral`
- Supports optional click-through `url` and descriptive `subtext`.

### 4.3 `<x-admin.status-badge>`
Maps domain status codes to semantic color schemes:
- `approved`, `active`, `completed`, `valid` → Soft green badge
- `pending`, `expiring_soon`, `review` → Soft amber badge
- `rejected`, `expired`, `non_compliant` → Soft red badge
- `inactive`, `unknown`, `cancelled` → Neutral slate badge

### 4.4 `<x-admin.empty-state>`
Replaces blank tables and unstyled alert paragraphs with an icon, descriptive heading, contextual explanation, and optional CTA button.

---

## 5. Standard Component Classes

| Class | Purpose |
|---|---|
| `.admin-page-shell` | Unified outer wrapper with standard max-width (1600px) and gutter padding |
| `.admin-toolbar` | Standardized filter surface with 42-44px controls, subtle borders, and consistent search inputs |
| `.filter-chip` | Segmented status filter pill with active/hover indicator |
| `.admin-table-card` | Clean white card container with header border and subtle outer shadow |
| `.admin-table` | Custom table styling with soft neutral header, comfortable padding, no vertical borders, and subtle hover states |
| `.admin-queue-strip` | Compact queue metric counters for management workflow pages |
| `.btn-brand` | Primary action button with MinSU green background, white text, and medium radius |
| `.btn-action-icon` | Standard square icon action button for toolbar resets and small actions |
| `.admin-tabs` | Shared navigation tab list for multi-view workspaces |

---

## 6. Information Architecture & Page Modes

### Monitoring Mode (Observational, Visual, Data-Oriented)
- **Boarding Houses** (`/admin/properties`)
- **Active Boarders** (`/admin/boarding-monitoring/students`)
- **Onboarding Pipeline** (`/admin/onboardings`)
- **Compliance Monitor** (`/admin/documents/monitoring`)
- Primary visual primitives: Visual KPI strips, map views, status filters, data tables, and analytics charts. Primary actions are *View*, *Inspect*, *Locate*.

### Management Mode (Queue-Oriented, Decisional, Actionable)
- **Property Reviews** (`/admin/property-reviews`)
- **Student Verification** (`/admin/approvals/students`)
- **Landlord Accreditation** (`/admin/approvals/landlords`)
- **Document Review Queue** (`/admin/documents/verification`)
- Primary visual primitives: Compact queue summary strips, status segmented filter tabs, queue table surfaces, and explicit *Review* action triggers.
