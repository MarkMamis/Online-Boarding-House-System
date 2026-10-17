# Responsive Design Verification Checklist (`RESPONSIVE_CHECKLIST.md`)

## Viewport Contracts Tested

| Breakpoint | Target Devices | Student Move-in Show | Landlord Move-in Review | Admin Pipeline | Verified Pass |
|---|---|---|---|---|---|
| **320px** | Ultra-compact Mobile (e.g. iPhone SE 1st gen) | Stacked action buttons, no horizontal scroll, wrapped accommodation metadata | Single column review cards, full-width action buttons | Responsive table scroll, stacked filter inputs | PASS |
| **360px** | Small Android (e.g. Galaxy S8/A-series) | Comfortable touch targets (minimum 44x44px), legible typography | Document preview pills wrap cleanly | Clean metrics and search bar | PASS |
| **390px** | Standard iPhone (iPhone 12/13/14/15) | Question cards fill container smoothly, canvas touch drawing active | Action buttons stack gracefully, payment proof thumbnail centered | Toolbar elements align neatly | PASS |
| **430px** | Large Mobile (iPhone Pro Max, Pixel XL) | Two-column payment method cards where supported, clean spacing | Review cards display two-column detail rows | Compact badges and status chips | PASS |
| **600px - 768px** | Tablets / Foldables | Side-by-side action buttons, structured breakdown tables | Two-column student & stay summary card | Multi-column filter toolbar | PASS |
| **1024px+** | Desktop Displays | Centered setup shell (max-width: 840px), optimal reading line length | Focused workspace (max-width: 980px), full document review grid | Full admin monitoring table with all columns | PASS |

## Anti-Breakage Protections
- **No Overflow Masks:** Avoided artificial `overflow-x: hidden` masks; addressed root flexbox and grid constraints.
- **Floating Chatbot Clearance:** Bottom padding of 2.5rem prevents sticky footer or fixed-position action conflicts with floating assistants.
- **Canvas Scaling:** Device pixel ratio (DPR) scaled HTML5 canvas prevents blurry lines on high-density Retina and OLED screens.
