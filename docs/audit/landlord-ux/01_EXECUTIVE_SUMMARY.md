# OBHS Landlord Portal UX & Workflow Audit: Executive Summary

**Document ID:** `01_EXECUTIVE_SUMMARY.md`  
**Date:** October 18, 2026  
**Auditor Roles:** Principal Product Designer, Senior UX Architect, Property Management Specialist, Senior Laravel Architect, Accessibility Specialist, QA Lead  
**Application:** Online Boarding House System (OBHS) — Mindoro State University (MinSU)  
**Deployment Target:** Hostinger Shared Hosting (Live Production Parity)  
**Baseline Git Commit:** `553defaf6580143023461f815159f218b1a1837f` (Branch: `main`)  
**Automated Test Suite Baseline:** 341 tests passing (1,679 assertions)  

---

## 1. Audit Context & Primary Mandate

The Online Boarding House System (OBHS) is a live web application servicing students, landlords, and university administrators around Mindoro State University campuses. Following the successful consumer-facing marketplace transformation of the Student & Tenant Portal, this audit targets the **Landlord Portal** (`/landlord/*`).

### The Core Problem
Many boarding house owners operating in Calapan and adjacent MinSU campuses are non-technical, family-run small proprietors. They manage between 1 and 4 properties, frequently ranging from 3 to 15 rooms or bedspaces. They interact with OBHS primarily via smartphones (Android/iOS) over mobile internet.

The current Landlord Portal violates fundamental principles of usability for this user demographic:
1. **Administrative Over-Engineering:** It resembles a high-density enterprise ERP system rather than a friendly small-business workspace.
2. **Navigational Chaos & Redundancy:** 14+ top-level navigation items, deep dynamic room accordion trees in the sidebar rendering every single room in the portfolio, and up to 5 redundant links to the same feature across a single dashboard session.
3. **Severe Multi-Step Friction:** Adding a property requires a 4-step wizard with 30+ form inputs, custom amenity tags, Leaflet map coordinate pinning, and mandatory room creation, with **zero draft persistence** (loss of progress on exit). Adding a room requires another 1,900-line Blade 4-step wizard with no duplication capability.
4. **Financial Fragmentation:** Move-in payments (security deposit and advance rent handled in `TenantOnboardingController`) and recurring monthly rent payments (handled in `PaymentController` and `StudentPaymentController`) are governed by completely disjoint controllers, database models, status definitions, and confirmation screens.
5. **Blurry Lifecycle Boundaries:** The boundary between an approved booking, an onboarding tenant, an active boarder, and a departed resident is confusingly entangled across Booking, Onboarding, Tenants, and Dashboard views.
6. **Monitoring vs Management Confusion:** Landlords are confronted with separate "Monitoring" pages, "Management" pages, "Overview" pages, and "Insights" pages that present conflicting statistics, overlapping tables, and contradictory actions.

---

## 2. Key Audit Metrics & Repository Findings

| Metric / Dimension | Current Observed State | Target Simplified State |
| :--- | :--- | :--- |
| **Total Landlord Routes** | 56 routes registered in `routes/web.php` | 56 routes preserved (zero breaking URL changes; unified UI access) |
| **Sidebar Menu Density** | 14 root links + dynamic room tree rendering up to 50+ DOM nodes | 5 primary operational sections (Home, Operations, Residents, Finance, Settings) |
| **Property Setup Complexity** | 4 steps, ~35 form fields, mandatory first room, no drafts | 3 progressive sections (Basics, Amenities/Rules, Location), draft capability, decoupled room creation |
| **Room Creation Workload** | 4-step wizard (1,903 lines of Blade), zero room duplication | 1 clean modal/page with sensible defaults, 1-click "Duplicate Room" action |
| **Payment Workflows** | 2 separate models (`TenantOnboarding` vs `TenantPayment`), 3 confirmation endpoints | 1 Unified Payments Center with clear separation between Move-in Dues and Monthly Rent |
| **Mobile Accessibility (320px–430px)** | Severe table overflow, tiny buttons, cramped modal footers, nested collapses | Full single-column responsive stacking, 44px+ touch targets, card-based tables |
| **Daily Decision Clarity** | 60KB dashboard with 8 "Quick Modules", 2 Chart.js charts, and duplicate tables | Action-oriented Landlord Home: "Needs Attention Today" queue with 1-click deep links |

---

## 3. High-Impact Findings Summary

### P0 / P1 Critical Workflow & Data Integrity Findings
1. **P1 — Financial Double-Counting & Inconsistent Revenue Reporting:**
   - In `AnalyticsController.php:52-62`, monthly revenue only aggregates `TenantPayment::where('status', 'approved')`. It completely ignores `TenantOnboarding` move-in payments (deposits/advance rent).
   - In `PaymentController.php:74-110`, `$totalPaid` merges both `TenantOnboarding` and `TenantPayment`, but `AnalyticsController` and Dashboard metrics report contradictory sums.
   - In `PaymentController@markAsPaid`, marking a booking as paid advances the booking's `next_payment_due_date` by one month, but does **not** create a ledger record in `tenant_payments`, causing cash collection figures to diverge from transaction logs.
2. **P1 — Premature Tenancy Activation vs Onboarding Entanglement:**
   - Approving a booking (`BookingController@approve`) sets `booking.status = 'approved'`.
   - The landlord's Tenants directory (`BookingController@landlordTenants`) queries `Booking::where('status', 'approved')`, immediately displaying applicants who have **not yet submitted documents, not signed a lease, and not paid move-in dues** as current tenants.
3. **P1 — Zero Draft Persistence in High-Friction Property Registration:**
   - `PropertyController@store` validates all property basics, house rules, and first-room attributes in one synchronous POST request. If a landlord abandons the 4-step wizard midway (e.g., waiting to locate a photo or coordinates), the entire form state is lost.
4. **P1 — Uncontrolled Sidebar DOM Inflation:**
   - `resources/views/layouts/landlord.blade.php:640-682` loops through every property and every child room owned by the landlord to render individual sidebar navigation links. For landlords with 20+ rooms, this produces an unwieldy, scroll-locked menu that breaks mobile navigation.

### P2 Usability, Navigation & Cognitive Load Findings
5. **P2 — Redundant Dashboard "Quick Modules" Grid:**
   - `resources/views/landlord/dashboard.blade.php:214-341` renders an 8-card grid repeating the exact same links already present in the sidebar (Rooms, Requests, Messages, Tenants, Onboarding, Maintenance, Payments, Analytics).
6. **P2 — Misleading "Recommended Rooms" on Landlord Dashboard:**
   - `resources/views/landlord/dashboard.blade.php:487-524` queries the landlord's 6 cheapest available rooms under the heading "Recommended Rooms". This consumer marketplace feature serves no business purpose for the property owner.
7. **P2 — "Room Maintenance" Lacks Resident Ticket Context:**
   - `MaintenanceController.php` is merely a room status toggle (`status = 'maintenance'`). It has no relationship with student repair requests or incident reports, creating false expectations of a work-order system.

---

## 4. Proposed Architectural Direction: Monitoring vs Management vs Insights

To eliminate confusion, the Landlord Portal must strictly implement the three-tier operational taxonomy without replicating the heavy multi-portal structure of the Admin panel:

```
┌──────────────────────────────────────────────────────────────────────────┐
│                         LANDLORD PORTAL TAXONOMY                         │
├──────────────────┬───────────────────────┬───────────────────────────────┤
│    MONITORING    │      MANAGEMENT       │            INSIGHTS           │
│  "What is active │ "What requires action │ "How is the business          │
│    right now?"   │        today?"        │  performing over time?"       │
├──────────────────┼───────────────────────┼───────────────────────────────┤
│ • Bed Vacancy    │ • Review Bookings     │ • 6-Month Occupancy Trend     │
│ • Active Boarders│ • Complete Onboarding │ • Monthly Cash Collections    │
│ • Move-in Dues   │ • Verify Rent Proofs  │ • Arrears & Payment Rates     │
│ • Due Dates      │ • Add / Edit Rooms    │ • Property Rating Breakdown   │
│ • Permit Health  │ • Toggle Maintenance  │                               │
└──────────────────┴───────────────────────┴───────────────────────────────┘
```

---

## 5. Summary Implementation Roadmap (Staged & Low-Risk)

1. **Phase 1: Sidebar De-Cluttering & Action-Oriented Home:**
   - Strip dynamic room accordion from the sidebar; collapse into 5 clean sections.
   - Transform `dashboard.blade.php` into an actionable "Needs Your Attention" Home screen.
2. **Phase 2: Property & Room Setup Streamlining:**
   - Decouple room creation from property registration.
   - Introduce 1-click "Duplicate Room" action to eliminate repetitive room entry.
3. **Phase 3: Unified Move-In Checklist & Tenant Separation:**
   - Consolidate onboarding review into a single contextual checklist.
   - Strict separation between "Active Residents" and "Move-In in Progress".
4. **Phase 4: Unified Payments Center:**
   - Clean sub-navigation separating Move-in Payments (Onboarding Dues) from Monthly Rent.
   - Prevent desynchronization between booking due dates and payment records.
5. **Phase 5: Operational Simplification & Mobile Consistency:**
   - Mobile-first responsive card views replacing wide tabular layouts.
   - Plain-language microcopy replacing internal technical statuses.
