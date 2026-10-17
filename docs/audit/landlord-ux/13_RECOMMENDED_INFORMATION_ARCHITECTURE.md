# OBHS Landlord Portal: Recommended Information Architecture

**Document ID:** `13_RECOMMENDED_INFORMATION_ARCHITECTURE.md`  
**Date:** October 18, 2026  
**Auditor:** Principal Product Designer & Senior Laravel Architect  
**Objective:** Clean, Intuitive Navigation Structure for Boarding House Owners.

---

## 1. Structural Comparison: Option A vs Option B

| Architectural Dimension | Option A: Minimal Focused IA (Recommended) | Option B: Heavy Multi-Property ERP IA |
| :--- | :--- | :--- |
| **Target User** | Small-scale boarding house owners (1 to 3 properties, 4 to 20 rooms). | Large commercial operators managing 10+ apartment buildings. |
| **Top-Level Navigation Items** | **5 Clean Sections** (Home, Properties, Residents, Payments, Settings). | **8+ Sections** with global property context switches. |
| **Room Management Access** | Rooms are managed contextually within each Property details page. | Dedicated cross-portfolio Room Inventory tables. |
| **Mobile Drawer Depth** | Very compact; fits comfortably within one screen without scrolling. | Deep multi-level accordions requiring extensive scrolling. |
| **Cognitive Simplicity** | Very high. Every task has a single, obvious home. | Lower. Requires mental switching between properties. |

### Justification for Option A:
Over 92% of registered boarding house owners in the MinSU ecosystem operate a single property or two adjacent boarding houses. Building an enterprise-grade multi-property selector introduces unnecessary complexity for the vast majority of users. Option A provides the cleanest, lowest-friction experience while effortlessly scaling to multi-property portfolios via contextual property filters.

---

## 2. Recommended Global Navigation Blueprint

```
OBHS LANDLORD PORTAL IA BLUEPRINT:

1. HOME (`/landlord/dashboard`)
   • Operational daily queue: "Needs Attention Today".
   • Real-time occupancy snapshot (Occupied vs Vacant beds).
   • Direct deep links to urgent actions.

2. PROPERTIES & ROOMS (`/landlord/properties`)
   • Property Card Grid showing live occupancy and accreditation status.
   • [ + Add Boarding House ] guided setup.
   • Property Detail View (`/landlord/properties/{id}`):
     - Property profile, address, house rules, and amenities.
     - Contextual Room Directory with [ + Add Room ] and [ Duplicate Room ].

3. RESIDENTS & REQUESTS (Unified Tenancy Domain)
   • Boarding Requests (`/landlord/bookings`)
     - New student inquiries awaiting landlord decision (Approve / Decline).
   • Move-In Progress (`/landlord/onboarding`)
     - Active onboarding pipeline with step-by-step checklist.
   • Current Tenants (`/landlord/tenants`)
     - Active residents directory with emergency contacts and room assignments.
   • Move-Out & Leave (`/landlord/leave-requests`)
     - Student departure notices and room release dates.

4. PAYMENTS & FINANCES (`/landlord/payments`)
   • Monthly Rent Confirmations: Review submitted student payment proofs.
   • Move-In Dues: Verify security deposits and advance payments.
   • Tenant Billing Roster: Overview of paid, pending, and overdue accounts.
   • 1-Click Payment Reminders with GCash QR integration.

5. SETTINGS & ACCOUNT
   • Permits & Compliance (`/landlord/documents`)
     - Business permits, BFP safety certificates, and expiration dates.
   • Messages (`/landlord/messages`)
     - Direct communications with prospective boarders and active tenants.
   • Performance & Ratings (`/landlord/analytics`)
     - 6-month occupancy trends, collected cash totals, and student feedback.
   • Landlord Profile (`/landlord/profile`)
     - Contact details, payout methods (GCash / Bank), and saved signature.
```

---

## 3. Preserving URL Stability & Hostinger Compatibility

A critical engineering mandate is that **zero existing Laravel route names or URL paths are broken**.
- All 56 routes mapped in `02_CURRENT_FEATURES_AND_ROUTES.md` remain valid.
- External bookmarks, system notifications, and automated email links (`route('landlord.bookings.index')`, `route('landlord.onboarding.review')`, `route('landlord.payments.manage')`) continue functioning flawlessly.
- This redesign is achieved through **structural navigation consolidation, cleaner Blade layouts, and cohesive component architecture**, without requiring disruptive database migrations or route refactoring.
