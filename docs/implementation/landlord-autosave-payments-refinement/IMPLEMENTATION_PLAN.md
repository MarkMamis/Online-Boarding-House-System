# Landlord Property Auto-Save & Payments UI Refinement — Implementation Plan

## 1. Context & Objectives
This refinement delivers two major user experience upgrades to the OBHS Landlord Portal without altering underlying business rules or shared hosting constraints:
1. **Property Auto-Save & Manual "Save Draft" Removal:**
   - Transition from manual Save Draft buttons in the guided property setup (`/landlord/properties/create`) to transparent, automatic server-side saving.
   - Provide a calm, accurate save status indicator (`Saving...`, `All changes saved`, `Couldn't save. Retry`).
   - Prevent ghost drafts on mere page load; establish stable draft persistence on first meaningful input.
   - Clean up dense Amenities & House Rules question on mobile by converting suggested rules into collapsible drawers.
2. **Payments Center Simplification (`/landlord/payments`):**
   - Streamline the cluttered Payments & Billing interface into a task-focused center.
   - Introduce a compact priority summary for payments awaiting review.
   - Establish a clean two-option segmented control (`Monthly Rent` vs `Move-in Payments`).
   - Remove redundant general financial KPI cards, duplicated status badges, and 800+ lines of redundant mobile detail modals.
   - Ensure mobile comfort (320px to 1280px) and prevent chat widget overlap.

## 2. Architecture & Design Principles
- **No Redis / Supervisor Dependency:** Operates synchronously and asynchronously via standard Laravel CSRF fetch calls compatible with Hostinger shared hosting.
- **Single Canonical Form State:** Native form input values remain preserved during autosave and step transitions.
- **Strict Data Segregation:** Monthly rent and move-in payments remain strictly separated in calculation and review flows.
- **Idempotent Updates:** Reusing draft IDs prevents record duplication across multiple auto-saves or final submission.
