# Canonical Feature Registry

This is the authoritative registry of all implemented (LIVE) and future planned (PLANNED) features for the Online Boarding House System (OBHS). Feature IDs must remain stable across all future development tasks.

---

## 1. Authentication & Identity Management (`AUTH`)

| Feature ID | Feature Name | Status | Roles | Primary Routes | Controller / Service | Models | Policies / Middleware | Invariants & Security |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| **AUTH-001** | User Login & Session | `LIVE` | Guest, All | `GET /login`, `POST /login`, `POST /logout` | `AuthController` | `User` | `guest`, `auth` | Password hashed with bcrypt; session invalidated & regenerated on login/logout. |
| **AUTH-002** | Multi-Role Registration | `LIVE` | Guest | `GET /register`, `GET /register/student`, `GET /register/landlord`, `POST /register` | `AuthController` | `User` | `guest` | Default role set to `student` or `landlord`; initial role `pending` supported if unspecified. |
| **AUTH-003** | Email Verification | `LIVE` | Authenticated | `GET /email/verify`, `POST /email/verification-notification`, `GET /email/verify/{id}/{hash}` | `AuthController` | `User` | `auth`, `signed`, `throttle:6,1` | Verification hash validated with sha1; unverified users cannot access portal functions. |
| **AUTH-004** | Role Onboarding Selection | `LIVE` | Pending | `GET /onboarding/role`, `POST /onboarding/role` | `OnboardingRoleController` | `User` | `auth`, `verified` | Users with role `pending` must choose `student` or `landlord` before portal access. |
| **AUTH-005** | Admin Registration Gating | `LIVE` | Admin / Guest | `GET /register/admin`, `POST /register/admin` | `AuthController` | `User` | `role:admin` (or `config`) | Blocked from public access unless `auth.public_admin_registration` is explicitly `true`. |

---

## 2. Student Portal & Tenancy Lifecycle (`STU`, `BKG`, `TEN`)

| Feature ID | Feature Name | Status | Roles | Primary Routes | Controller / Service | Models | Policies / Middleware | Invariants & Security |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| **STU-001** | Student Setup & Verification | `LIVE` | Student | `GET /student/setup`, `PUT /student/setup` | `StudentSetupController`, `AcademicCatalogService` | `User` | `role:student`, `EnsureStudentSetupComplete` | Student ID, College, and Program required before entering full portal. |
| **STU-002** | Student Profile & Password | `LIVE` | Student | `GET /student/profile`, `PUT /student/profile`, `PUT /student/profile/change-password` | `StudentProfileController` | `User` | `role:student`, `student.setup` | Current password verification required for password changes; avatar uploaded privately. |
| **STU-003** | Student Room Browsing & Search | `LIVE` | Student | `GET /student/rooms`, `GET /student/rooms/{room}` | `RoomController` | `Room`, `Property` | `role:student`, `student.setup` | Only shows rooms belonging to approved properties. |
| **STU-004** | Student Interactive Map | `LIVE` | Student | `GET /student/properties/map`, `GET /student/properties/map-data` | `StudentPropertyController` | `Property`, `Room` | `role:student`, `student.setup` | Geocoded property coordinates rendered with vacancy filters. |
| **BKG-001** | Room Reservation Request | `LIVE` | Student | `GET /student/rooms/{room}/book`, `POST /student/rooms/{room}/book` | `BookingController` | `Booking`, `Room` | `BookingPolicy@create`, `role:student` | Room must have available slots (`slots_available > 0`) and property must be approved. |
| **BKG-002** | Student Booking Cancellation | `LIVE` | Student | `GET /student/requests`, `POST /student/requests/{booking}/cancel` | `BookingController` | `Booking` | `BookingPolicy@cancel` | Student can only cancel their own pending/approved bookings with valid reason. |
| **TEN-001** | Tenant Onboarding Workflow | `LIVE` | Student, Landlord | `GET /student/onboarding/{onboarding}`, `POST .../documents`, `POST .../sign-contract`, `POST .../pay-deposit` | `TenantOnboardingController` | `TenantOnboarding`, `Booking` | `TenantOnboardingPolicy` | Sequential progression: Document upload -> Landlord document review -> Student contract sign -> Landlord sign -> Deposit -> Completed. |
| **TEN-002** | Lease Agreement PDF Generator | `LIVE` | Student, Landlord, Admin | `GET /student/onboarding/{onboarding}/contract-pdf` | `TenantOnboardingController`, DomPDF | `TenantOnboarding` | `TenantOnboardingPolicy@view` | Dynamic legal lease agreement with digital signature embeds. |
| **TEN-003** | Student Leave & Move-Out | `LIVE` | Student | `POST /student/leave-requests`, `POST /student/leave-requests/{leaveRequest}/cancel` | `StudentLeaveRequestController` | `LeaveRequest`, `Booking` | `role:student`, `student.setup` | Must have active tenancy booking. |
| **TEN-004** | Incident Reporting | `LIVE` | Student | `GET /student/reports`, `POST /student/reports`, `POST /student/reports/{report}/mark-read` | `ReportController`, `ReportPriorityClassifier` | `Report`, `Booking` | `ReportPolicy` | Student must be an active or boarded tenant to file reports against property. |
| **TEN-005** | Room & Landlord Feedback | `LIVE` | Student | `GET /student/rooms/{room}/feedback`, `POST /student/rooms/{room}/feedback` | `RoomFeedbackController` | `RoomFeedback`, `Room` | `role:student` | Validates rating (1-5) and feedback sentiment. |

---

## 3. Landlord Operations & Property Management (`LND`, `DOC`)

| Feature ID | Feature Name | Status | Roles | Primary Routes | Controller / Service | Models | Policies / Middleware | Invariants & Security |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| **LND-001** | Landlord Setup & Profile | `LIVE` | Landlord | `GET /landlord/setup`, `PUT /landlord/setup`, `GET /landlord/profile` | `LandlordSetupController` | `User`, `LandlordProfile` | `role:landlord` | Contact number, boarding house name, and payment methods required. |
| **DOC-001** | Landlord Compliance Documents | `LIVE` | Landlord | `GET /landlord/documents`, `POST /landlord/documents`, `POST /landlord/documents/{document}/resubmit` | `LandlordDocumentController`, `DocumentExpirationService` | `LandlordDocument` | `role:landlord` | Validates permit/safety cert files, expiration dates; transitions to `pending` on upload. |
| **LND-002** | Property CRUD Operations | `LIVE` | Landlord | `GET /landlord/properties`, `POST /landlord/properties`, `PUT /landlord/properties/{property}`, `DELETE ...` | `PropertyController`, `GeocodingService` | `Property` | `PropertyPolicy`, `landlord.setup` | Landlords can only view/edit/delete properties where `landlord_id === auth()->id()`. |
| **LND-003** | Room CRUD & Availability | `LIVE` | Landlord | `GET /landlord/properties/{property}/rooms`, `POST .../rooms`, `PUT .../rooms/{room}` | `RoomController`, `RoomOccupancyRules` | `Room`, `Property` | `RoomPolicy`, `landlord.setup` | Room slots cannot exceed capacity; pricing models (`per_room`, `per_bed`, `hybrid`) enforced. |
| **LND-004** | Booking Approval & Rejection | `LIVE` | Landlord | `GET /landlord/bookings`, `POST /landlord/bookings/{booking}/approve`, `POST .../reject` | `BookingController` | `Booking`, `Room` | `BookingPolicy@approve` | Landlord can only approve bookings for owned properties; triggers onboarding record. |
| **LND-005** | Landlord Tenant Document Review | `LIVE` | Landlord | `GET /landlord/onboarding/{onboarding}/review`, `POST .../approve-documents`, `POST .../sign-contract` | `TenantOnboardingController` | `TenantOnboarding` | `TenantOnboardingPolicy` | Must belong to landlord's property; verifies student credentials before contract signing. |
| **LND-006** | Room Maintenance Gating | `LIVE` | Landlord | `GET /landlord/maintenance`, `POST /landlord/maintenance/set`, `POST .../complete` | `MaintenanceController` | `Room` | `RoomPolicy` | Prevents new bookings while under maintenance. |
| **LND-007** | Landlord Analytics & Occupancy | `LIVE` | Landlord | `GET /landlord/analytics` | `AnalyticsController` | `Property`, `Room`, `Booking` | `role:landlord` | Computes revenue, occupancy rate, and student demographics per property. |

---

## 4. Payment & Financial Tracking (`PAY`)

| Feature ID | Feature Name | Status | Roles | Primary Routes | Controller / Service | Models | Policies / Middleware | Invariants & Security |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| **PAY-001** | Student Monthly Rent Payment | `LIVE` | Student | `GET /student/payments`, `POST /student/payments` | `StudentPaymentController` | `TenantPayment`, `Booking` | `role:student`, `student.setup` | Amount calculated server-side; duplicate submission for same billing cycle blocked. |
| **PAY-002** | Landlord Payment Ledger & Records | `LIVE` | Landlord | `GET /landlord/payments`, `POST /landlord/payments/{booking}/paid`, `POST .../records` | `PaymentController` | `TenantPayment`, `Booking` | `role:landlord` | Landlord can log custom monthly payment records and mark cycle as paid/pending. |
| **PAY-003** | Automated Payment Reminders | `LIVE` | Landlord, CLI | `POST /landlord/payments/{booking}/remind`, `artisan payments:notify-overdue` | `PaymentController`, `NotifyOverduePayments` | `Booking`, `User` | `role:landlord` / Cron | Sends email and database notification with GCash details & QR link. |

---

## 5. Administration, Verification & Oversight (`ADM`)

| Feature ID | Feature Name | Status | Roles | Primary Routes | Controller / Service | Models | Policies / Middleware | Invariants & Security |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| **ADM-001** | Admin Executive Dashboard | `LIVE` | Admin | `GET /admin/dashboard`, `GET /admin/dashboard/stats` | `AuthController` | `User`, `Property`, `Booking` | `role:admin` | Real-time counts of pending approvals, active boarders, and system KPIs. |
| **ADM-002** | Student & Landlord Verification | `LIVE` | Admin | `GET /admin/approvals/students`, `POST .../student-verifications/{user}/approve`, `POST .../permits/{user}/approve` | `AuthController`, `AdminLandlordDocumentController` | `User`, `LandlordProfile`, `LandlordDocument` | `role:admin` | Approves or rejects student IDs and landlord business/safety permits with reasons. |
| **ADM-003** | Property Approval Workflow | `LIVE` | Admin | `GET /admin/properties`, `GET .../{property}/inspect`, `POST .../approve`, `POST .../reject` | `PropertyController` | `Property` | `role:admin` | Unapproved properties remain hidden from public search and student browsing. |
| **ADM-004** | MinSU Boarding Monitoring | `LIVE` | Admin | `GET /admin/boarding-monitoring/students`, `GET .../print` | `AdminBookingController`, `BoardingMonitoringService` | `Booking`, `User`, `Property` | `role:admin` | Institutional boarding census by College, Program, Gender, and Property; print-ready. |
| **ADM-005** | Institutional Analytics | `LIVE` | Admin | `GET /admin/analytics` | `AdminAnalyticsController`, `AnalyticsService` | `Booking`, `Property`, `User` | `role:admin` | Strict SQL aggregations of occupancy trends, gender breakdown, and capacity metrics. |
| **ADM-006** | Incident Report Moderation | `LIVE` | Admin | `GET /admin/reports`, `GET .../{report}`, `PUT .../{report}` | `ReportController` | `Report` | `role:admin` | Admin can update status (`in_progress`, `resolved`, `dismissed`) and record resolutions. |

---

## 6. Shared Capabilities & Infrastructure (`MSG`, `NOTIF`, `AI`, `FILE`, `DEP`)

| Feature ID | Feature Name | Status | Roles | Primary Routes | Controller / Service | Models | Policies / Middleware | Invariants & Security |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| **MSG-001** | Direct & Inquiry Messaging | `LIVE` | Authenticated | `GET /messages`, `POST /messages`, `POST /messages/{message}/read` | `MessageController` | `Message` | `MessagePolicy` | Participants must be sender or receiver; room inquiries attach contextual info. |
| **NOTIF-001** | System Notifications | `LIVE` | Authenticated | `GET /notifications`, `POST /notifications/read-all`, `POST /notifications/{id}/read` | `NotificationController` | `Notification` | `auth`, `verified` | Dual delivery via database table and SMTP email. |
| **AI-001** | AI Conversational Assistant | `LIVE` | Authenticated | `GET /chatbot/history`, `POST /chatbot/message` | `ChatbotController`, `AiAssistantService`, `AiToolRegistry` | `ChatbotConversation`, `ChatbotMessage` | `throttle:chatbot`, Role Gated | 20+ role-gated tools; outputs sanitized; prompt boundaries enforced. |
| **FILE-001** | Multi-Driver File Storage & Streaming | `LIVE` | Authenticated | `GET /files/{path}` | `FileController`, `FileStorageService` | `LandlordDocument`, `User`, `TenantOnboarding` | `auth`, `verified`, Prefix Auth | Streams private files via R2/Supabase/Local fallback without exposing credentials. |
| **DEP-001** | Hostinger Deployment Packaging | `LIVE` | CLI / Ops | `tools/package-hostinger.ps1`, `artisan deploy:check` | `DeploymentCheck` | N/A | CLI Only | Packages clean production ZIP excluding `.env`, dev dependencies, and temporary files. |

---

## 7. Planned Marketplace Extensions (`MKT` - PLANNED)

*The following features are formally registered for future Airbnb-style development. No database tables or controllers should be created for these until their dedicated implementation phase.*

| Feature ID | Feature Name | Status | Intended Role | Scope & Planned Boundaries | Owner Agent |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **MKT-001** | Advanced Marketplace Search & MinSU Distance Filter | `PLANNED` | Public / Student | Distance calculation from MinSU campus gates, pricing sliders, amenity checkboxes. | `obhs-marketplace` |
| **MKT-002** | Student Wishlist & Saved Listings | `PLANNED` | Student | Save favorite properties and rooms for side-by-side comparison. | `obhs-marketplace` |
| **MKT-003** | Boarding House Comparison Tool | `PLANNED` | Public / Student | Side-by-side comparison table of amenities, pricing, room sizes, and distance. | `obhs-marketplace` |
| **MKT-004** | Property Viewing / Visit Scheduler | `PLANNED` | Student, Landlord | Request scheduled physical inspection before placing a reservation. | `obhs-marketplace` |
| **MKT-005** | Verified Student Badge & Trust Marks | `PLANNED` | Public / Student | Visual badges indicating admin-verified permits, safety compliance, and verified reviews. | `obhs-marketplace` |
| **MKT-006** | Transparent Move-In Cost Breakdown | `PLANNED` | Student, Landlord | Transparent pricing display (monthly rent, deposit, advance rent, utility inclusions). | `obhs-marketplace` |
| **MKT-007** | Full-Room Waitlist & Vacancy Alerts | `PLANNED` | Student | Automated notification when an occupied room becomes vacant or opens for next semester. | `obhs-marketplace` |
| **MKT-008** | Preference-Based Recommendations | `PLANNED` | Student | Recommendation engine matching student college/major and budget with nearby listings. | `obhs-marketplace` |
