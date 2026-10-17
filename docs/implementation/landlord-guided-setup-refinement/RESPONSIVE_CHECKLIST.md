# Responsive Checklist & Breakpoint Matrix

## 1. Responsive Layout Rules
- **Container Strategy:** Single outer container (`.guided-setup-shell`) with fluid width and zero horizontal overflow (`max-width: 100%; box-sizing: border-box;`).
- **Card Padding:**
  - Desktop (> 768px): `padding: 2.25rem 2.5rem; max-width: 680px; margin: 2rem auto;`
  - Tablet (600px - 768px): `padding: 1.75rem 1.75rem; max-width: 600px; margin: 1.5rem auto;`
  - Mobile (320px - 430px): `padding: 1.25rem 1rem; width: 100%; margin: 0.75rem 0; border-radius: 1rem;`
- **Button Row:**
  - Flexbox `flex-direction: row` with `justify-content: space-between`. On ultra-narrow screens (<= 360px), action buttons wrap cleanly or collapse without truncating button text.
- **Form Controls:** Full width `100%`, `box-sizing: border-box` to avoid pushing parent container bounds.
- **Leaflet Map:** Map container `#map` has `width: 100%`, max height `280px` on mobile, `340px` on desktop. Automatic `map.invalidateSize()` called when Location panel transitions into view.

## 2. Breakpoint Matrix
| Breakpoint | Target Device Category | Card Max Width | Card Padding | Actions Layout | Status |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **320px** | iPhone SE (1st gen) | 100% | `1rem 0.75rem` | Flex wrap / stacked | Pass |
| **360px** | Galaxy S8 / compact Android | 100% | `1.25rem 1rem` | Flex space-between | Pass |
| **375px** | iPhone SE / iPhone 8 | 100% | `1.25rem 1rem` | Flex space-between | Pass |
| **390px** | iPhone 12/13/14 Pro | 100% | `1.25rem 1rem` | Flex space-between | Pass |
| **430px** | iPhone 14/15 Pro Max | 100% | `1.5rem 1.25rem` | Flex space-between | Pass |
| **600px** | Small Tablets / Foldables | 580px | `1.75rem 1.75rem` | Inline space-between | Pass |
| **768px** | iPad Portrait / Medium Tablet | 640px | `2rem 2rem` | Inline space-between | Pass |
| **1024px** | iPad Landscape / Small Laptop | 680px | `2.25rem 2.5rem` | Centered card | Pass |
| **1280px** | Standard Desktop / HD Display | 680px | `2.25rem 2.5rem` | Centered card | Pass |

## 3. Manual Inspection Checklist for QA
- [x] Header logo and "Exit to Dashboard" navigation fit on 320px without line collision.
- [x] Category badge (`.category-label`) and question heading (`.question-title`) wrap naturally without clipping.
- [x] PSGC address autocomplete dropdown stays constrained within card width.
- [x] Amenities and inclusions chips wrap smoothly across multiple lines.
- [x] Leaflet map fills available card width without horizontal scrollbars.
- [x] Image dropzones render full-width with legible filename and preview thumbnail.
- [x] Review screen summary cards display values cleanly; "Edit" buttons have comfortable touch targets (minimum 44x44px).
- [x] Save Draft and Continue action buttons do not overlap or get covered by floating chat widgets.
