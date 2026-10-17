# OBHS Landlord Portal: UI Blueprints & Text Wireframes

**Document ID:** `14_UI_BLUEPRINTS.md`  
**Date:** October 18, 2026  
**Auditor:** Principal Product Designer & Senior Frontend Architect  
**Scope:** 16 Implementation-Ready Text Wireframes covering all Landlord Touchpoints.

---

## Blueprint 1: Landlord Home (`/landlord/dashboard`)
- **Primary Goal:** Give the owner an instant, unambiguous daily summary of what requires action today.
- **Primary CTA:** Dynamic action button inside top priority card (e.g. `[ Review 2 Payments ]`).
- **Secondary Actions:** `[ View All Tenants ]`, `[ Check Messages ]`.
- **Wireframe Structure:**
```
┌────────────────────────────────────────────────────────────────────────┐
│ [MINSU] Landlord Workspace                     🔔 (3)  👤 Maria Santos │
├────────────────────────────────────────────────────────────────────────┤
│ Good morning, Maria!                                                   │
│ Your Portfolio: 2 Properties • 12 of 14 Beds Occupied (86% Occupancy)  │
├────────────────────────────────────────────────────────────────────────┤
│ ⚡ NEEDS ATTENTION TODAY (3)                                           │
│ ┌────────────────────────────────────────────────────────────────────┐ │
│ │ 🔴 2 Monthly Payments Submitted for Verification                   │ │
│ │    Juan Dela Cruz (Room 101) & Ana Cruz (Room 104) uploaded GCash   │ │
│ │    [ Confirm Payments -> ]                                         │ │
│ └────────────────────────────────────────────────────────────────────┘ │
│ ┌────────────────────────────────────────────────────────────────────┐ │
│ │ 🟠 1 Move-In Requirement Ready for Review                          │ │
│ │    Pedro Penduko submitted ID & Proof of Income for Room 202       │ │
│ │    [ Review Documents -> ]                                         │ │
│ └────────────────────────────────────────────────────────────────────┘ │
│ ┌────────────────────────────────────────────────────────────────────┐ │
│ │ 🟡 1 New Boarding Request                                          │ │
│ │    Carla Reyes requested Solo Room 103 for Nov 2026                 │ │
│ │    [ Review Request -> ]                                           │ │
│ └────────────────────────────────────────────────────────────────────┘ │
├────────────────────────────────────────────────────────────────────────┤
│ YOUR BOARDING HOUSES                                                   │
│ ┌─────────────────────────────────┐ ┌────────────────────────────────┐ │
│ │ Sunshine Dormitory (Masipit)    │ │ Green Villa (San Vicente)      │ │
│ │ Status: Verified & Approved 🟢  │ │ Status: Verified & Approved 🟢 │ │
│ │ 8 of 8 Beds Occupied [ Full ]   │ │ 4 of 6 Beds Occupied [ 2 Open ]│ │
│ │ [ Manage Rooms ]                │ │ [ Manage Rooms ]  [+ Add Room] │ │
│ └─────────────────────────────────┘ └────────────────────────────────┘ │
└────────────────────────────────────────────────────────────────────────┘
```
- **Mobile Behavior:** Single-column stacked cards. Touch targets 48px high.
- **Empty State:** *"All clear! No tasks need your attention right now."* with a link to view active tenants.

---

## Blueprint 2: Properties List (`/landlord/properties`)
- **Primary Goal:** Overview of all owned boarding houses with occupancy health and accreditation status.
- **Primary CTA:** `[ + Add Boarding House ]`.
- **Wireframe Structure:**
```
┌────────────────────────────────────────────────────────────────────────┐
│ PROPERTIES & BOARDING HOUSES                     [ + Add Boarding House ]
├────────────────────────────────────────────────────────────────────────┤
│ Filter: [ All Properties (2) ] [ Approved (2) ] [ Pending Review (0) ] │
├────────────────────────────────────────────────────────────────────────┤
│ ┌────────────────────────────────────────────────────────────────────┐ │
│ │ [ Cover Photo ]  Sunshine Dormitory                                │ │
│ │                  Masipit, Calapan City, Oriental Mindoro           │ │
│ │                  🟢 Approved by MinSU Admin • 8 Rooms              │ │
│ │                  Occupancy: 8/8 Beds (100%) [ Full ]               │ │
│ │                  Price Range: ₱2,500 - ₱4,000 / month              │ │
│ │                  [ View Rooms & Details ]  [ Edit ]  [ More ▾ ]    │ │
│ └────────────────────────────────────────────────────────────────────┘ │
│ ┌────────────────────────────────────────────────────────────────────┐ │
│ │ [ Cover Photo ]  Green Villa Boarding House                        │ │
│ │                  San Vicente, Calapan City                         │ │
│ │                  🟢 Approved by MinSU Admin • 6 Rooms              │ │
│ │                  Occupancy: 4/6 Beds (67%) [ 2 Available ]         │ │
│ │                  Price Range: ₱2,000 - ₱3,500 / month              │ │
│ │                  [ View Rooms & Details ]  [ Edit ]  [ More ▾ ]    │ │
│ └────────────────────────────────────────────────────────────────────┘ │
└────────────────────────────────────────────────────────────────────────┘
```

---

## Blueprint 3: Property Details & Room Directory (`/landlord/properties/{id}`)
- **Primary Goal:** Single command center for one boarding house: view rooms, add rooms, duplicate rooms, see occupants.
- **Primary CTA:** `[ + Add Room ]`.
- **Secondary Action:** `[ ⧉ Duplicate Room ]`.
- **Wireframe Structure:**
```
┌────────────────────────────────────────────────────────────────────────┐
│ ‹ Back to Properties       Sunshine Dormitory            [ Edit Property ]
│ Masipit, Calapan City  •  8 Rooms Total  •  8 Active Boarders          │
├────────────────────────────────────────────────────────────────────────┤
│ [ Rooms & Availability ]   [ Amenities & Rules ]   [ Property Details ]│
├────────────────────────────────────────────────────────────────────────┤
│ ROOM DIRECTORY (8 Rooms)                                 [ + Add Room ]│
│ ┌────────────────────────────────────────────────────────────────────┐ │
│ │ Room 101  •  Capacity: 2 Beds  •  ₱2,500 / bed                     │ │
│ │ Status: Occupied 🔴 (2/2 Active Tenants)                           │ │
│ │ Tenants: Juan Dela Cruz (Due Oct 25), Mark Perez (Due Oct 28)      │ │
│ │ [ View Room ]  [ Edit ]  [ ⧉ Duplicate ]  [ Set Maintenance ]      │ │
│ └────────────────────────────────────────────────────────────────────┘ │
│ ┌────────────────────────────────────────────────────────────────────┐ │
│ │ Room 102  •  Capacity: 1 Bed (Solo)  •  ₱4,000 / month             │ │
│ │ Status: Available 🟢 (1 Bed Open)                                  │ │
│ │ [ View Room ]  [ Edit ]  [ ⧉ Duplicate ]  [ Set Maintenance ]      │ │
│ └────────────────────────────────────────────────────────────────────┘ │
└────────────────────────────────────────────────────────────────────────┘
```

---

## Blueprint 4: Add / Edit Room Modal (`RoomController@create / store`)
- **Primary Goal:** Minimal, low-friction room setup without a 4-step wizard.
- **Primary CTA:** `[ Save Room ]`.
- **Wireframe Structure:**
```
┌────────────────────────────────────────────────────────┐
│ Add Room to Sunshine Dormitory                      [X]│
├────────────────────────────────────────────────────────┤
│ Room Number:        [ Room 103                       ] │
│ Capacity:           [ 2 ] Beds                         │
│ Pricing Model:      (•) Per Bed   ( ) Whole Room       │
│ Monthly Rent:       ₱ [ 2,500.00 ] per bed             │
│ Initial Status:     [ Available ▾                    ] │
│                                                        │
│ Amenities Included in Rent (Check all that apply):     │
│ [x] WiFi       [x] Water Bill     [x] Electric Fan     │
│ [ ] Aircon     [ ] Study Table    [x] Common CR        │
│                                                        │
│ Room Photo (Optional): [ Choose File: room103.jpg    ] │
├────────────────────────────────────────────────────────┤
│ [ Cancel ]                                [ Save Room ]│
└────────────────────────────────────────────────────────┘
```

---

## Blueprint 5: Booking Requests (`/landlord/bookings`)
- **Primary Goal:** Review student reservation inquiries with clear applicant context and stay dates.
- **Primary CTA:** `[ Approve Request ]` (Green).
- **Secondary CTA:** `[ Decline ]` (Neutral Gray).
- **Wireframe Structure:**
```
┌────────────────────────────────────────────────────────────────────────┐
│ BOARDING REQUESTS                                                      │
│ Filter: [ Pending Review (1) ]  [ Approved (12) ]  [ Declined (3) ]    │
├────────────────────────────────────────────────────────────────────────┤
│ ┌────────────────────────────────────────────────────────────────────┐ │
│ │ 🟡 Carla Reyes (BS Criminology - 2nd Year)                         │ │
│ │    Requested: Sunshine Dormitory - Room 103 (Solo Bed)              │ │
│ │    Stay Duration: Nov 01, 2026 – Mar 31, 2027 (5 Months)            │ │
│ │    Monthly Rent: ₱2,500.00 / month                                  │ │
│ │    Message: "Good day ma'am, I am quiet and study late at night."   │ │
│ │    Room Availability: 1 Bed Open right now                          │ │
│ │                                                                    │ │
│ │    [ Approve Request ]   [ Decline Request ]   [ Message Carla ]    │ │
│ └────────────────────────────────────────────────────────────────────┘ │
└────────────────────────────────────────────────────────────────────────┘
```

---

## Blueprint 6: Move-In Progress (`/landlord/onboarding`)
- **Primary Goal:** Track approved students through document submission, contract signing, and move-in payment.
- **Primary CTA:** Dynamic next step (e.g. `[ Sign Contract ]` or `[ Confirm Move-In Payment ]`).
- **Wireframe Structure:**
```
┌────────────────────────────────────────────────────────────────────────┐
│ MOVE-IN PROGRESS (Tenant Onboarding)                                   │
│ Active Move-Ins: 2 Students Awaiting Completion                        │
├────────────────────────────────────────────────────────────────────────┤
│ ┌────────────────────────────────────────────────────────────────────┐ │
│ │ Pedro Penduko  •  Green Villa - Room 202                            │ │
│ │ Step 3 of 4: Awaiting Landlord Lease Signature                     │ │
│ │                                                                    │ │
│ │ [✓] 1. Booking Approved                                            │ │
│ │ [✓] 2. Documents Verified (Student ID & Emergency Contact)          │ │
│ │ [✓] 3. Student Signed Lease Agreement                              │ │
│ │ [●] 4. Landlord Signature: [ Sign Agreement Now (1-Click) ]         │ │
│ │ [ ] 5. Move-in Deposit (₱3,500): Waiting for student to transfer   │ │
│ └────────────────────────────────────────────────────────────────────┘ │
└────────────────────────────────────────────────────────────────────────┘
```

---

## Blueprint 7: Unified Payments Center (`/landlord/payments`)
- **Primary Goal:** Review submitted proofs for both Move-In deposits and monthly rent in one consistent interface.
- **Primary CTA:** `[ Confirm Payment ]`.
- **Secondary CTA:** `[ Reject Proof ]` (requires reason).
- **Wireframe Structure:**
```
┌────────────────────────────────────────────────────────────────────────┐
│ PAYMENTS & RENT MANAGEMENT                                             │
├────────────────────────────────────────────────────────────────────────┤
│ [ ₱28,000 Collected (Oct 2026) ]  [ ₱5,000 Pending Verification (2) ]  │
│ [ ₱2,500 Overdue (1 Tenant) ]     [ ₱35,500 Total Expected ]           │
├────────────────────────────────────────────────────────────────────────┤
│ TABS: [ Rent Confirmations (2) ]  [ Move-In Dues (1) ]  [ All Tenants ]│
├────────────────────────────────────────────────────────────────────────┤
│ RENT CONFIRMATIONS AWAITING YOUR REVIEW:                               │
│ ┌────────────────────────────────────────────────────────────────────┐ │
│ │ Juan Dela Cruz  •  Sunshine Dorm - Room 101                         │ │
│ │ Amount: ₱2,500.00 (October 2026 Rent)                               │ │
│ │ Payment Method: GCash • Ref: 9021-8491-8291 • Submitted: 2 hrs ago │ │
│ │ Attachment: [ 📄 View GCash Screenshot ]                            │ │
│ │                                                                    │ │
│ │ [ Confirm Payment (1-Click) ]   [ Reject Proof ]                    │ │
│ └────────────────────────────────────────────────────────────────────┘ │
└────────────────────────────────────────────────────────────────────────┘
```

---

## Blueprint 8: Active Tenants Directory (`/landlord/tenants`)
- **Primary Goal:** Clear roster of who is currently living in the boarding house, stay end-dates, and emergency contacts.
- **Primary CTA:** `[ Send Message ]`.
- **Wireframe Structure:**
```
┌────────────────────────────────────────────────────────────────────────┐
│ CURRENT RESIDENTS                                                      │
│ Filter: [ Sunshine Dormitory (8) ▾ ]  Search: [ Find student...      ] │
├────────────────────────────────────────────────────────────────────────┤
│ ┌────────────────────────────────────────────────────────────────────┐ │
│ │ 👤 Juan Dela Cruz                                                  │ │
│ │    Room: Room 101 (Bed 1)  •  BS Information Technology (Year 3)    │ │
│ │    Contact: 0917-123-4567  •  Emergency: 0918-987-6543 (Mother)     │ │
│ │    Stay Period: Aug 15, 2026 – May 30, 2027                         │ │
│ │    Rent: ₱2,500 / month  •  Billing: Paid for Oct 🟢                │ │
│ │    Digital Boarder ID: TID-84920194                                  │ │
│ │    [ Send Message ]  [ View Lease PDF ]  [ Payment History ]        │ │
│ └────────────────────────────────────────────────────────────────────┘ │
└────────────────────────────────────────────────────────────────────────┘
```

---

## Blueprint 9: Permits & Compliance Hub (`/landlord/documents`)
- **Primary Goal:** Manage regulatory permits with plain-language status cards and 1-click renewal uploads.
- **Primary CTA:** `[ Upload Renewal Document ]`.
- **Wireframe Structure:**
```
┌────────────────────────────────────────────────────────────────────────┐
│ COMPLIANCE & PERMITS                                                   │
├────────────────────────────────────────────────────────────────────────┤
│ ┌────────────────────────────────────────────────────────────────────┐ │
│ │ 🟢 Business / Mayor's Permit                                        │ │
│ │    Status: Approved by MinSU Admin • Valid until Dec 31, 2026       │ │
│ │    Document #: BP-2026-08492  •  File: business_permit_2026.pdf     │ │
│ │    [ Replace / Update Document ]                                    │ │
│ └────────────────────────────────────────────────────────────────────┘ │
│ ┌────────────────────────────────────────────────────────────────────┐ │
│ │ 🟠 BFP Fire Safety Inspection Certificate (FSIC)                    │ │
│ │    Status: Expires in 14 Days (Nov 01, 2026)                        │ │
│ │    Action: Please upload this year's renewed certificate.            │ │
│ │    [ Upload Renewed Certificate ]                                   │ │
│ └────────────────────────────────────────────────────────────────────┘ │
└────────────────────────────────────────────────────────────────────────┘
```

---

## Blueprint 10: Business Analytics (`/landlord/analytics`)
- **Primary Goal:** Track real occupancy curves and monthly cash collections over time.
- **Primary CTA:** Time range filter: `[ Last 6 Months ▾ ]`.
- **Wireframe Structure:**
```
┌────────────────────────────────────────────────────────────────────────┐
│ BUSINESS ANALYTICS                                [ Last 6 Months ▾ ]  │
├────────────────────────────────────────────────────────────────────────┤
│ [ 88% Average Occupancy ]       [ ₱145,000 Total Cash Collected ]      │
│ [ 96% On-Time Payment Rate ]    [ 4.8 ★ Average Student Rating ]       │
├────────────────────────────────────────────────────────────────────────┤
│ MONTHLY CASH COLLECTION TREND                                          │
│ [Bar Chart: Actual Cash Collected vs Expected Monthly Rent]            │
│  May: ₱26,000 | Jun: ₱28,000 | Jul: ₱32,000 | Aug: ₱35,000 | Sep: ₱35k│
├────────────────────────────────────────────────────────────────────────┤
│ OCCUPANCY BREAKDOWN BY PROPERTY                                        │
│ • Sunshine Dorm: 8/8 Beds (100%)                                       │
│ • Green Villa:   4/6 Beds (67%)                                        │
└────────────────────────────────────────────────────────────────────────┘
```
