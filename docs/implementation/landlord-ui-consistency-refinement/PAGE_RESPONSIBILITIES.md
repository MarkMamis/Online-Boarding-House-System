# Landlord Portal Page Responsibilities & Hierarchy

## 1. Architectural Distinction
To avoid turning every management screen into an overwhelming dashboard, OBHS enforces strict functional separation between sections:

- **Monitoring Hub (`Monitoring`):** Read-only inspection of operational and institutional status ("What is happening right now?"). Read-only metrics, occupancy rates, accreditation statuses, and compliance conditions.
- **Operations & Management (`Management`):** Action-oriented workspaces ("What needs my attention? What do I need to update?"). Application reviews, move-in checklists, leave notices, property registration, room editing, maintenance toggles.
- **Finance & Accounting (`Finance`):** Payment verification, ledger histories, deposit confirmation, and rent tracking.
- **Insights & Performance (`Insights`):** Longitudinal analytics, occupancy trends, historical financial revenue calculations.

---

## 2. Refined Page Standards

### A. Current Tenants (`landlord.tenants.index`)
- **Core Purpose:** View current residents, verify stay dates, inspect emergency contact information, and follow up on payment statuses.
- **Layout Pattern:** Page header with resident count summary strip $\rightarrow$ Instant search & residency/payment filters $\rightarrow$ Tenant cards $\rightarrow$ Quick communication action (`Message`) and property context link.

### B. Property & Room Monitoring (`landlord.monitoring.properties`)
- **Core Purpose:** Read-only inspection of overall portfolio occupancy, available bed slots, and active residents per room.
- **Layout Pattern:** Page header with live occupancy summary strip $\rightarrow$ Property sections $\rightarrow$ Dual presentation: clean compact table on desktop, responsive card rows on mobile. No embedded mutation forms.

### C. Compliance & Accreditation (`landlord.monitoring.compliance`)
- **Core Purpose:** Read-only audit of institutional accreditation state and document verification milestones.
- **Layout Pattern:** Page header with verification summary $\rightarrow$ Accreditation banner $\rightarrow$ Required documents checklist with status badges, upload flags, and expiration conditions.

### D. Properties (`landlord.properties.index`)
- **Core Purpose:** Central property directory to manage boarding houses, inspect bed capacities, and configure listings.
- **Layout Pattern:** Page header with `Add Property` button $\rightarrow$ Compact search/filter bar $\rightarrow$ Secondary collapsible map ("View Map") $\rightarrow$ Property listing cards with clear `Manage Property` primary action.

### E. Room Directory (`landlord.rooms.index` & `landlord.rooms.landlord_index`)
- **Core Purpose:** Fast room inventory management across all properties.
- **Layout Pattern:** Page header with `Add Room` modal trigger $\rightarrow$ Filter bar (search, property, status) $\rightarrow$ Room cards featuring room photo, multi-bed occupancy indicators ("Occupied beds: X of Y"), price, status, and direct `Edit Room` action.

### F. Boarding Requests (`landlord.bookings.index`)
- **Core Purpose:** Review and decide upon incoming student applications.
- **Layout Pattern:** Page header $\rightarrow$ Status filter (Pending, Approved, Rejected, Cancelled) $\rightarrow$ Actionable requests list with clear `Approve` and `Decline` controls $\rightarrow$ Seamless redirection to move-in pipeline on approval.

### G. Move-in Management (`landlord.onboarding.index`)
- **Core Purpose:** Track incoming tenants through move-in requirements (document verification, contract signing, deposit payment).
- **Layout Pattern:** Page header $\rightarrow$ Action required summary $\rightarrow$ Move-in queue cards showing student, room, progress percentage, and clear contextual next action button (`Review Documents`, `Review Payment`, `View Details`).

### H. Departure & Leave Notices (`landlord.leave_requests.index`)
- **Core Purpose:** Review student early move-out notices and schedule departure dates without prematurely terminating active occupancy.
- **Layout Pattern:** Page header $\rightarrow$ Collapsible policy guidance $\rightarrow$ Status filter $\rightarrow$ Request cards with `Approve` (future vs immediate notice) and `Decline` controls.

### I. Room Availability & Maintenance (`landlord.maintenance.index`)
- **Core Purpose:** Manage temporary offline maintenance status to hide rooms from the student marketplace during cleaning/repairs.
- **Layout Pattern:** Page header $\rightarrow$ Collapsible availability notice $\rightarrow$ Rooms currently under maintenance list with `Complete Maintenance` action $\rightarrow$ Clean set form.
