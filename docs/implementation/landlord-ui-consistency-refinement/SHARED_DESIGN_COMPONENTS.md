# Landlord Shared Design Components & Tokens

## 1. UI Tokens & Styling Architecture
All landlord portal views share unified design tokens embedded in `resources/views/layouts/landlord.blade.php`:

### Color Palette & Variables
- `--brand`: `#14532d` (Forest green primary accent)
- `--brand-dark`: `#166534` (Deep forest hover)
- `--mint`: `#a7f3d0` (Mint highlight)
- `--surface`: `#ffffff` (Card and panel surface)
- `--shell`: `#f8fafc` (Background shell)
- `--line`: `#e5e7eb` (Subtle neutral border)
- `--muted`: `#6b7280` (Muted metadata and labels)

---

## 2. Shared Landlord Utility Classes

### `.landlord-page-shell`
Outer container for all landlord portal management pages. Replaces deeply nested gradient panels with clean layout flow.

### `.landlord-header`
Standardized header pattern:
```html
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
    <div>
        <div class="landlord-header-eyebrow">Operations</div>
        <h1 class="landlord-header-title mb-1">Page Title</h1>
        <div class="landlord-header-subtitle">One concise descriptive sentence.</div>
    </div>
    <div class="d-flex align-items-center gap-2 flex-wrap">
        <!-- Compact Summary Strip and/or Primary Action -->
    </div>
</div>
```

### `.landlord-summary-strip`
Replaces bulky stacked KPI metric cards. Provides instant contextual status in a single unobtrusive capsule:
```html
<span class="landlord-summary-strip">
    <span class="landlord-summary-dot"></span>
    <span><strong>12</strong> Active Residents</span>
    <span class="text-muted">·</span>
    <span class="text-warning-emphasis fw-bold">2 pending payments</span>
</span>
```

### `.landlord-card`
Universal card container for records and tables:
- Background: `#ffffff`
- Border: `1px solid rgba(2, 8, 20, 0.08)`
- Border radius: `1rem`
- Box shadow: `0 4px 14px rgba(2, 8, 20, 0.03)`
- Overflow: `hidden`

### `.landlord-filter-bar`
Responsive filter bar container:
- Search field with icon input (`.landlord-filter-search`, 40px height, 0.88rem font size)
- Select dropdowns (`.landlord-filter-select`, 40px height, consistent border `#cbd5e1`)

### `.landlord-empty-state`
Standardized, friendly empty state:
- Clean 50px rounded icon container with subtle gray background
- One bold heading (1.05rem)
- One short explanatory sentence
- Contextual call to action button (only when actionable)
- Eliminates 4-card zero metric stacks when collections are empty
