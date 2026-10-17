# Responsive UX & Beginner-Friendly Design System

**Document Reference:** `docs/implementation/landlord-ux-transformation/RESPONSIVE_UX.md`  
**Modified Views:** All Landlord Portal Blade Templates

---

## 1. Visual Hierarchy & Beginner-Friendly Principles

The landlord user persona typically comprises property owners who manage their boarding houses directly, frequently on mobile phones or tablets, and may not have advanced digital literacy.

To optimize their experience:
1. **Calm, Green-Accented Design:** Uses MinSU brand greens (`#16a34a`, `#14532d`, `#a7f3d0`) with soft neutral backgrounds (`#f8fafc` to `#fbfdfc`), avoiding aggressive or chaotic alert colors.
2. **Action-First Task Queuing:** Landlords are never presented with a blank wall of forms. Urgent pending tasks (new bookings, move-in steps, rent verifications) are presented as clean action cards with single-click destinations.
3. **Transparent Microcopy:** Jargon such as "reconciliation ledger formulas" or "shared tenancy submodules" was eliminated in favor of direct, plain English:
   - "Room Availability & Maintenance Mode" (clearly explaining that rooms in maintenance are hidden from student search).
   - "Scheduled Departures Policy" (explaining that future leave approvals do not immediately release room slots).
   - "Move-in In Progress" (clearly distinguishing applicants completing paperwork from physical residents).

---

## 2. Mobile & Touch Responsive Guidelines

- **44px Touch Targets:** Buttons and action triggers meet accessibility guidelines for mobile thumb interaction.
- **Responsive Tables:** All data tables are wrapped in `.table-responsive` containers or replaced with mobile-friendly card lists (`.leave-item`, `.maintenance-item`, `.payment-item`).
- **Zero Viewport Overflow:** The elimination of the recursive dynamic room loop in the sidebar guarantees that the mobile offcanvas navigation drawer scrolls naturally without clipping or overflowing.
