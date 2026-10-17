# Responsive Testing & Viewport Checklist

## 1. Viewport Matrix

| Viewport Width | Device Category | Property Setup Behavior | Payments Center Behavior | Status |
| :--- | :--- | :--- | :--- | :--- |
| **320px** | iPhone SE (1st gen) | Autosave badge wraps cleanly; house rule suggestions collapsed; full-width inputs. | Type segmented buttons wrap or flex 50%; card actions stack vertically; no overflow. | Pass |
| **360px** | Compact Android | Save badge fits header; map fits frame; comfortable padding. | Tenant cards render cleanly; amount clearly visible; action buttons touch-accessible. | Pass |
| **375px** | iPhone SE / 8 | Centered card (100% width with 1rem padding); chips wrap naturally. | Priority banner renders with stacked button; status pills readable. | Pass |
| **390px** | iPhone 12/13/14 | Clean typography; Leaflet map touch-draggable without horizontal scrolling. | Tenant name and room tags wrap cleanly; actions touch target > 44px. | Pass |
| **430px** | iPhone Pro Max | Comfortable margins; photo dropzone preview crisp. | Segmented buttons inline; filter dropdown full width or inline. | Pass |
| **768px** | iPad Portrait | Centered card (640px max width); 2-column review grid. | Card layout transitions to horizontal layout with right-aligned amount and buttons. | Pass |
| **1024px** | iPad Landscape / Laptop | Centered card (680px max width); map at 300px height. | Generous breathing room; inline filters; clear collections summary. | Pass |
| **1280px+** | Desktop HD | Centered guided card; subtle shadow; clean desktop focus rings. | Max-width 1040px shell; action buttons cleanly aligned; no chat widget interference. | Pass |

## 2. Accessibility & Interaction Verification
- **Touch Targets:** All primary buttons (`Continue`, `Mark Paid`, `View Details`) exceed 44x44px touch requirements.
- **Screen Reader Announcements:** The autosave badge uses `aria-live="polite"` so screen-reader users hear save confirmation without jarring interruptions.
- **Collapsible Suggestions:** Suggested house rules have explicit `data-target` mappings and toggle state aria attributes.
- **No Chatbot Widget Interference:** The `.payments-shell` container includes `padding-bottom: 3rem`, guaranteeing that floating widgets do not obstruct payment review buttons or modals.
