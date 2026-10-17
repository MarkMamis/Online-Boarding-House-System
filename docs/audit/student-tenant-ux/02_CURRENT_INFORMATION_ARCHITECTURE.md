# Current Information Architecture & Technical Inventory

**Audit Date:** October 18, 2026  
**Scope:** Current Student & Tenant Routing, Controllers, Layouts, Blade Templates, and Data Flows  

---

## 1. Route Map & Middleware Pipeline

All authenticated student routes are guarded by a multi-layer middleware stack in `routes/web.php`:

```
Request 
  └── ['auth', 'verified']                      (Core authentication & MinSU email verification)
        └── ['role:student']                    (RBAC gate: user role must be 'student')
              └── Dedicated Setup Routes        (/student/setup, ID verification, recovery)
              └── ['student.setup']             (Gate: redirect to setup if incomplete)
                    └── Full Student Portal Routes
```

### Route Registry Table

| Method | URI | Controller Action | Route Name | Purpose |
|---|---|---|---|---|
| `GET` | `/student/setup` | `StudentSetupController@show` | `student.setup.show` | Guided 6-step verification onboarding |
| `PUT` | `/student/setup` | `StudentSetupController@update` | `student.setup.update` | Update setup fields & documents |
| `POST` | `/student/setup/check-student-id` | `StudentSetupController@checkStudentId` | `student.setup.check_student_id` | Real-time uniqueness check |
| `POST` | `/student/setup/recovery-request` | `StudentSetupController@storeRecoveryRequest` | `student.setup.recovery_request` | Dispute duplicate student ID |
| `POST` | `/student/verification-request/submit`| `StudentSetupController@submitVerificationRequestInfo` | `student.verification_request.submit` | Resubmit docs requested by Admin |
| `GET` | `/student/dashboard` | `AuthController@studentDashboard` | `student.dashboard` | General student monolithic dashboard |
| `GET` | `/student/tenant-dashboard` | `AuthController@studentTenantDashboard` | `student.tenant.dashboard` | Active resident tenant dashboard |
| `GET` | `/student/properties/map-data` | `StudentPropertyController@mapData` | `student.properties.map` | JSON GeoJSON endpoint for properties |
| `GET` | `/student/properties/map` | `StudentPropertyController@map` | `student.properties.map_view`| Standalone interactive property map |
| `GET` | `/student/properties/{property}/rooms-data` | `StudentPropertyController@roomsData` | `student.properties.rooms_data` | JSON vacancy per property |
| `GET` | `/student/properties/{property}` | `StudentPropertyController@show` | `student.properties.show` | Property profile & room listing |
| `GET` | `/student/profile` | `StudentProfileController@show` | `student.profile.show` | Student profile overview & edit form |
| `GET` | `/student/profile/edit` | `StudentProfileController@edit` | `student.profile.edit` | Profile edit view |
| `PUT` | `/student/profile` | `StudentProfileController@update` | `student.profile.update` | Profile save endpoint |
| `GET` | `/student/profile/change-password` | `StudentProfileController@editPassword`| `student.profile.change-password` | Password change form |
| `PUT` | `/student/profile/change-password` | `StudentProfileController@updatePassword`| `student.profile.update-password` | Password save endpoint |
| `GET` | `/student/rooms` | `RoomController@studentIndex` | `student.rooms.index` | Standalone room catalog & filters |
| `GET` | `/student/rooms/{room}` | `RoomController@studentShow` | `student.rooms.show` | Room detail page & inquiry |
| `GET` | `/student/rooms/{room}/feedback`| `RoomController@studentFeedback` | `student.rooms.feedback_page` | Room review submission page |
| `POST` | `/student/rooms/{room}/inquire` | `MessageController@storeInquiry` | `student.rooms.inquire` | Send inquiry message to landlord |
| `POST` | `/student/rooms/{room}/feedback`| `RoomFeedbackController@store` | `student.rooms.feedback` | Save star rating & sentiment |
| `GET` | `/student/rooms/{room}/book` | `BookingController@create` | `bookings.create` | Booking request form |
| `POST` | `/student/rooms/{room}/book` | `BookingController@store` | `bookings.store` | Submit booking request |
| `GET` | `/student/requests` | `BookingController@studentIndex` | `student.bookings.index` | View student booking requests |
| `POST` | `/student/requests/{booking}/cancel` | `BookingController@cancel` | `student.bookings.cancel` | Cancel pending request |
| `GET` | `/student/reports` | `ReportController@studentIndex` | `student.reports.index` | Student Help Center & tickets |
| `GET` | `/student/reports/create` | `ReportController@create` | `student.reports.create` | Create report ticket |
| `POST` | `/student/reports` | `ReportController@store` | `student.reports.store` | Save report ticket |
| `POST` | `/student/reports/{report}/mark-read` | `ReportController@markResponseRead` | `student.reports.mark_read` | Acknowledge admin reply |
| `GET` | `/student/onboarding` | `TenantOnboardingController@index` | `student.onboarding.index` | Onboarding records & timeline |
| `GET` | `/student/onboarding/{onboarding}` | `TenantOnboardingController@show` | `student.onboarding.show` | 4-step move-in workflow |
| `GET` | `/student/onboarding/{onboarding}/contract` | `TenantOnboardingController@viewContract` | `student.onboarding.contract` | Lease agreement preview |
| `GET` | `/student/onboarding/{onboarding}/contract-pdf` | `TenantOnboardingController@downloadContractPdf` | `student.onboarding.contract_pdf` | Stream DomPDF contract |
| `POST` | `/student/onboarding/{onboarding}/documents` | `TenantOnboardingController@uploadDocuments` | `student.onboarding.upload_documents` | Submit COR / ID / emergency contact |
| `POST` | `/student/onboarding/{onboarding}/sign-contract` | `TenantOnboardingController@signContract` | `student.onboarding.sign_contract` | Sign lease via digital canvas |
| `POST` | `/student/onboarding/{onboarding}/pay-deposit` | `TenantOnboardingController@payDeposit` | `student.onboarding.pay_deposit` | Upload deposit receipt |
| `POST` | `/student/leave-requests` | `StudentLeaveRequestController@store` | `student.leave_requests.store` | Submit absence/leave date |
| `POST` | `/student/leave-requests/{leaveRequest}/cancel` | `StudentLeaveRequestController@cancel` | `student.leave_requests.cancel` | Cancel pending leave |
| `GET` | `/student/payments` | `StudentPaymentController@index` | `student.payments.index` | Monthly rental ledger & submission |
| `POST` | `/student/payments` | `StudentPaymentController@store` | `student.payments.store` | Submit monthly rent payment |
| `GET` | `/messages` | `MessageController@index` | `messages.index` | Shared multi-thread messaging |
| `POST` | `/messages` | `MessageController@store` | `messages.store` | Send message in thread |
| `POST` | `/messages/{message}/read` | `MessageController@read` | `messages.read` | Mark message as read |
| `GET` | `/notifications` | `NotificationController@index` | `notifications.index` | Shared notification center |
| `POST` | `/notifications/read-all` | `NotificationController@markAllRead` | `notifications.read_all` | Mark all notifications read |
| `POST` | `/notifications/{id}/read` | `NotificationController@markRead` | `notifications.read` | Mark single notification read |

---

## 2. Layouts & View Architecture

The application has **two distinct layout templates** for students in `resources/views/layouts/`:

1. **`layouts.student.blade.php` (Legacy / Setup):**
   - File size: 6,407 bytes (130 lines).
   - Features: Fixed hero background (`mindoro-way-pic.jpg`), transparent dark top navbar, teal accent (`--brand: #0ea5a3`), simple top-nav dropdown.
   - Status: Used primarily for legacy pages or as a standalone setup container.

2. **`layouts.student_dashboard.blade.php` (Active Student Portal Layout):**
   - File size: 16,729 bytes (489 lines).
   - Features:
     - Top fixed navbar with MINSU logo, "Student Portal" title, right-hand meta: *"Workspace: Student Operations"*, notification bell with badge, and profile link.
     - Responsive offcanvas drawer (`#studentSidebar`) on `< 992px` viewports, fixed left sidebar (`--sidebar-w: 264px`) on `>= 992px`.
     - Sub-component: `<x-student-sidebar />` rendered inside the drawer.
     - Brand tokens: `--brand: #14532d` (MinSU dark green), `--brand-dark: #166534`, `--shell: #f8fafc`.

---

## 3. The Dual-Monolith Structural Conflict

Inspection of `resources/views/student/dashboard.blade.php` reveals a critical architectural anomaly:

```
resources/views/student/dashboard.blade.php (2,262 lines, 138,261 bytes)
├── [Panel: dashboard]      -> Welcome, 4 KPI Tiles, Mini Map, Next Action, Recent Activity, Rec Rooms
├── [Panel: browse-rooms]   -> Search filters, price inputs, Pax input, All Rooms grid
├── [Panel: property-map]   -> Full Leaflet map instance #propertiesMap
├── [Panel: requests]       -> Booking requests table and cancel forms
├── [Panel: messages]       -> 2-column chat thread list and message composer
├── [Panel: onboarding]     -> Move-in progress bar and 4-step checklist
├── [Panel: roommates]      -> Active roommates in approved room
├── [Panel: reports]        -> Support ticket list and creation form
├── [Panel: notifications]  -> Activity list and mark-read actions
└── [Panel: profile]        -> Profile credentials and academic document view
```

### Why this is problematic:
1. **Redundant Code:** Almost every single panel inside `dashboard.blade.php` is an older, duplicate copy of the code in dedicated views (`student/rooms/index.blade.php`, `student/bookings/index.blade.php`, `messages/index.blade.php`, etc.).
2. **Query Overload:** When `AuthController@studentDashboard` runs, it executes SQL queries to populate all 10 panels at once, regardless of whether the student ever views them.
3. **Broken Navigation Sync:** Clicking the sidebar links navigates the browser to dedicated URLs (e.g. `/student/rooms`), while clicking dashboard activity cards invokes JavaScript hash jumps (`#browse-rooms`).
4. **Maintenance Drift:** Bug fixes applied to standalone views (e.g., pricing model formatting in `student/rooms/index.blade.php`) are not synchronized with the embedded panels in `dashboard.blade.php`.
