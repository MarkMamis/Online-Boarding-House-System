# OBHS Student & Tenant Experience Audit: Executive Summary

**Project:** Online Boarding House System (OBHS) — Mindoro State University (MinSU)  
**Environment:** Laravel 12 / Blade / Tailwind & Vanilla CSS / MySQL 8.x (Hostinger Shared Hosting Parity)  
**Audit Scope:** End-to-End Customer Experience, Room Discovery, Booking Journey, Active Tenancy, Mobile Usability, and Architectural Redundancy  
**Task Type:** Diagnostic UI/UX & Information Architecture Audit Only (No runtime changes applied)  
**Audit Completion Date:** October 18, 2026  

---

## 1. Context & The Core Problem

The Online Boarding House System (OBHS) serves Mindoro State University students searching for safe, accredited, and affordable accommodations around the campus, as well as students actively residing in accredited boarding houses.

While the administrative and landlord portals have matured to handle compliance, verification, and property operations, **the Student Portal currently operates with an administrative dashboard mentality rather than a consumer hospitality accommodation experience**:

1. **Dashboard-First vs. Customer-First:**
   When a student logs in, they are greeted by a generic control panel labeled *"Student Operations"*, displaying administrative KPI stat cards (*"Available Rooms"*, *"Pending Requests"*, *"Active Boarding Requests"*, *"Messages"*), a static map preview with *"Coverage"* and *"Tip"* counters, and long vertical scroll stacks. MinSU students do not manage boarding operations—they are consumers looking for a bed or tenants managing their daily residence.

2. **The "Split-Personality" Architectural Defect:**
   The codebase contains an extreme dual-architecture:
   - **Monolithic SPA Dashboard (`resources/views/student/dashboard.blade.php`):** A single 2,262-line file (138 KB) that embeds 10 complete sub-panels (`dashboard`, `browse-rooms`, `property-map`, `requests`, `messages`, `onboarding`, `roommates`, `reports`, `notifications`, `profile`) switched via JavaScript `data-student-panel` attributes.
   - **Standalone Dedicated Views:** Parallel routes and standalone Blade views exist for `/student/rooms`, `/student/properties/map`, `/student/requests`, `/messages`, `/student/onboarding`, `/student/reports`, `/notifications`, and `/student/profile`.
   - On every load of the dashboard, `AuthController::studentDashboard` executes over **18 complex database queries** (fetching 200 messages, thread aggregations, leave requests, roommates, report histories, all rooms, all properties), creating heavy execution overhead on Hostinger Shared Hosting.

3. **Lifecycle Disconnect:**
   A student's needs drastically change across their accommodation journey:
   - *Searching student:* Needs prominent search, visual cards, price clarity, filter chips, and campus distance.
   - *Applicant with pending request:* Needs real-time request status, landlord response expectation, and cancellation controls.
   - *Approved applicant awaiting move-in:* Needs a clear checklist for lease signing and deposit submission without getting lost.
   - *Active resident tenant:* Needs room info, roommate list, landlord quick-dial, monthly rent due date, payment submission, and leave requests.
   - *Departed tenant:* Needs deposit settlement history and prompt review submission.

   Currently, these states are awkwardly shoehorned into either the monolithic dashboard or a separate `student/tenant-dashboard.blade.php`, with jarring sidebar rules (e.g. students with approved bookings have "Browse Rooms" completely hidden from the sidebar).

4. **Map "TBD" Placeholder Bug:**
   On both the Dashboard map preview and full property map, marker pills frequently display `"TBD"` or `"Price TBD"`. Code inspection confirms this occurs because properties without priced rooms or with unsynchronized `price_min` / `price_max` columns fall back to `"TBD"`, creating a broken, placeholder appearance in production.

---

## 2. Key Findings Summary Table

| Category | Finding | Severity | User Impact |
|---|---|---|---|
| **Architecture** | 138 KB Monolithic dashboard duplicates 10 standalone routes | P1 | Severe bloat, query overhead, maintenance hazards |
| **Home UX** | Administrative KPI tiles dominate the first viewport | P2 | Intimidating, unhelpful for room discovery |
| **Room Discovery** | Cards crowd amenities and hide landlord credibility | P2 | Hard to compare accommodation value |
| **Map Experience** | "TBD" price pins appear when property range is null | P1 | Looks unverified or broken to students |
| **Booking Journey** | Approved booking provides no direct CTA to Tenant Onboarding | P1 | Students stall at move-in preparation |
| **Active Tenancy** | Fragmented between tenant dashboard, feedback page, and payment modal | P2 | Residents must navigate multiple disjointed screens |
| **Navigation** | Sidebar hides "Browse Rooms" when booking is approved | P2 | Traps students if plans change or backup is needed |
| **Mobile Layout** | Narrow viewports experience extreme vertical scrolling fatigue | P2 | Crucial actions buried under analytics widgets |
| **Privacy & Safety** | Help center reports lack clear emergency vs general ticketing distinction | P0 | Risk of students submitting urgent safety issues into slow admin queues |

---

## 3. High-Level Transformation Vision

```
CURRENT (Admin Control Panel)           PROPOSED (Consumer Hospitality StayHub)
┌─────────────────────────────────┐     ┌─────────────────────────────────┐
│ Top: "Student Operations"       │     │ Top: MinSU StayHub • Student    │
│ KPIs: [Avail: 12] [Reqs: 1]     │     │ Context-Aware Greeting & State  │
│ [Pending: 0] [Msgs: 3]          │     ├─────────────────────────────────┤
├─────────────────────────────────┤     │ SEARCHING: Search Bar + Cards   │
│ Map Preview with "TBD" pins     │     │ PENDING: Track Request Banner   │
├─────────────────────────────────┤     │ APPROVED: 4-Step Move-in Prep   │
│ 10 Hidden Tab Panels            │     │ TENANT: Active Stay & Quick Pay │
│ Heavy monolithic queries (18+)  │     ├─────────────────────────────────┤
└─────────────────────────────────┘     │ Clean Dedicated Sub-pages       │
                                        └─────────────────────────────────┘
```

---

## 4. Deliverable Structure in this Audit

This audit provides a comprehensive 17-document evaluation located in `docs/audit/student-tenant-ux/`:

- `01_EXECUTIVE_SUMMARY.md`: Strategic vision, core defects, and transformation goals.
- `02_CURRENT_INFORMATION_ARCHITECTURE.md`: Complete routing, layout, and controller inventory.
- `03_STUDENT_AND_TENANT_PERSONAS.md`: Personas A through H across the accommodation lifecycle.
- `04_END_TO_END_USER_JOURNEYS.md`: Step-by-step clicks, dead ends, and friction points across 13 user journeys.
- `05_HOME_DASHBOARD_AUDIT.md`: In-depth analysis of the current dashboard and redundant KPI widgets.
- `06_ROOM_DISCOVERY_AND_LISTINGS.md`: Browse rooms, filters, carousel, and card hierarchy evaluation.
- `07_PROPERTY_DETAILS_AND_BOOKING_UX.md`: Room detail pages, pricing transparency, and the booking request workflow.
- `08_ACTIVE_TENANT_EXPERIENCE.md`: Resident hub, roommates, house rules, payments, and leave requests.
- `09_MESSAGING_NOTIFICATIONS_REVIEWS.md`: Communication channels, unread badges, and completed-stay review prompts.
- `10_NAVIGATION_AND_MOBILE_AUDIT.md`: Mobile drawer, touch targets, viewport responsiveness (320px–430px).
- `11_VISUAL_DESIGN_SYSTEM.md`: Typography, color tokens, card treatments, and MinSU branding alignment.
- `12_ACCESSIBILITY_PRIVACY_PERFORMANCE.md`: WCAG contrast, screen reader labels, data privacy, and Hostinger query optimization.
- `13_REDUNDANCY_AND_FEATURE_RESPONSIBILITY.md`: Separation of Admin vs. Landlord vs. Student responsibilities.
- `14_RECOMMENDED_INFORMATION_ARCHITECTURE.md`: Proposed sitemap, routing structure, and lifecycle state-machine.
- `15_PAGE_BLUEPRINTS.md`: 10 detailed structural blueprints and text wireframes.
- `16_IMPLEMENTATION_ROADMAP.md`: Staged, low-risk, 6-phase implementation plan.
- `FEATURE_AUDIT_MATRIX.md`: Scorecard across all 15 modules against 11 UX dimensions.
