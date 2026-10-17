# Payments UI Structure Specification

## 1. Eliminated Redundant Components
The previous Payments & Billing screen contained excessive visual hierarchy, duplicate navigation links, and heavy modal wrappers:
- **Removed "View Tenants" Header Button:** Tenants are accessible directly from the main sidebar.
- **Removed Triplicate KPI Cards:** Total Collections, Pending Verification, and Total Expected grid removed.
- **Removed Onboarding Summary Cards:** Onboarding Ledger, Onboarding Paid, and Onboarding Pending card cluster removed.
- **Removed Zero-Status Badge Row:** The `0 paid`, `0 pending`, `0 overdue` badge strip was eliminated.
- **Removed Per-Booking Mobile Modals:** 800+ lines of duplicate DOM elements (`#mobilePaymentDetailModal{{ $booking->id }}`) were deleted in favor of a single responsive card layout and a single shared action confirmation modal.

## 2. Streamlined Information Hierarchy

### A. Page Header
- Kicker: `FINANCE`
- Title: `Payments`
- Subtitle: `Review and track payments from your tenants.`

### B. Priority Review Banner
- Appears only when `totalPendingReviewCount > 0`.
- Highlights the count of tenant submissions awaiting landlord review with an hourglass badge and quick-filter button: `View Pending`.

### C. Payment Type Selector (Segmented Control)
- **Monthly Rent:** Direct view for recurring monthly tenant collections. Badged with pending review count when > 0.
- **Move-in Payments:** Direct view for initial onboarding deposits and advance rent obligations. Badged with pending count when > 0.

### D. Compact Type Summary & Status Filter Bar
- Inline summary of confirmed collections for the selected payment category.
- Clean dropdown filter: `All Statuses`, `Pending Review`, `Paid`, `Overdue` (for monthly).
- Quick filter reset button appears only when filtering is active.

### E. Responsive Record Cards
- Tenant avatar circle with initial letter.
- High-contrast tenant name and room/property chips.
- Due date, payment method, reference, and amount.
- Direct primary action button (`Mark Paid`, `Manage`, `Proof`, `Undo`).

### F. Empty States
- Compact, contextual empty states explaining why the view is empty based on the selected tab and active filter.
