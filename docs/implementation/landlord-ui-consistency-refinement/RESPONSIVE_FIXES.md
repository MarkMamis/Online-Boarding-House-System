# Responsive Breakpoint Fixes & Mobile Usability

## 1. Audit of Mobile Viewports
The existing implementation exhibited several mobile issues on narrow screens (320px, 360px, 375px, 390px, 430px):
1. **Horizontal Table Overflow:** Desktop tables in Property & Room Status Monitoring and Compliance Monitoring were forcing viewport horizontal scroll bars, cutting off action buttons and badge indicators.
2. **5-to-6 Card Mobile Stacks:** Metric boxes wrapped into full-width cards on narrow devices, resulting in up to 1,200px of scrolling before reaching the first tenant or room record.
3. **Competing Action Buttons:** Property cards displayed 4 full-width or wrapping buttons ("Property Overview", "Manage Rooms", "Add Room", dropdown), overwhelming the card footer.
4. **Clipped Filter Toolbars:** Inputs and select controls had disparate heights and broke across awkward break lines.

---

## 2. Implemented Responsive Solutions

### A. Dual Responsive Presentation (Desktop Table vs. Mobile Row Cards)
- Implemented in `landlord/monitoring/properties.blade.php` and `landlord/monitoring/compliance.blade.php`.
- **Desktop ($\ge 768px$):** Renders `.d-none.d-md-block.table-responsive` with clean table layout, spacious padding, and full column alignment.
- **Mobile ($< 768px$):** Renders `.d-md-none.divide-y` with touch-friendly cards displaying room number, occupancy slots, monthly price, status badge, occupant pills, and full-width touch actions.
- Zero horizontal overflow on 320px–430px screens; no `overflow-x: hidden` hacks used.

### B. Single-Column Card Alignment
- In `Current Tenants`, `Boarding Requests`, `Move-in Management`, and `Departure Notices`:
  - Desktop: 2-column flex/grid (`[Tenant/Request Info] [Status Pill + Actions]`).
  - Mobile ($< 768px$): Stacks neatly with a subtle dashed separator, placing action buttons in a comfortable thumb-friendly bottom bar.
  - Buttons expand to full width or 2-column grid (`repeat(2, 1fr)`) on mobile ($< 576px$) to prevent text truncation.

### C. Collapsible Property Map
- On `Properties` (`landlord.properties.index`):
  - Desktop & Mobile: Leaflet map is placed behind a secondary "View Map" collapse toggle.
  - On narrow screens, the map no longer pushes property listings hundreds of pixels down.
  - When toggled, Bootstrap collapse event fires `map.invalidateSize()` after transition, preventing gray/unrendered map tile defects.

### D. Standardized Input & Control Heights
- All search fields and filter dropdowns standardized to `40px` height with `.rounded-3` or `.rounded-pill`, consistent left search icon padding (`2.25rem`), and legible font sizing (`0.88rem` / `14px`).
