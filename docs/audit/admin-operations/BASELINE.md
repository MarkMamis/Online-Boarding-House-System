# OBHS Admin Operations Audit — Baseline

## 1. Environment & Git Context
- **Branch:** `main`
- **Starting Commit:** `25dbed2b9fb694661a9985463adbe588a38bf692`
- **Working Tree State:** Clean
- **PHP Version:** PHP 8.2+
- **Laravel Framework:** 11.x
- **Database Engine:** MySQL 8.x (Dual Parity: Local Dev & Hostinger Shared Hosting)
- **Frontend Stack:** Vanilla CSS + Tailwind v4 + Chart.js + Vite

---

## 2. Pages In Scope & Route Inventory

| Scope Area | Page Name | Route Name | HTTP Method | URL Path | Controller & Method |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **Monitoring** | Boarding Houses | `admin.properties` | `GET` | `/admin/properties` | `AuthController@adminProperties` |
| **Monitoring** | Boarded Students | `admin.boarding-monitoring.students` | `GET` | `/admin/boarding-monitoring/students` | `AdminBookingController@boardedStudents` |
| **Monitoring** | Onboardings | `admin.onboardings.index` | `GET` | `/admin/onboardings` | `TenantOnboardingController@adminIndex` |
| **Approvals** | Student Approvals | `admin.approvals.students` | `GET` | `/admin/approvals/students` | `AuthController@adminStudentApprovals` |
| **Approvals** | Landlord Approvals | `admin.approvals.landlords` | `GET` | `/admin/approvals/landlords` | `AuthController@adminLandlordApprovals` |
| **Compliance** | Document Verification | `admin.documents.verification` | `GET` | `/admin/documents/verification` | `AdminLandlordDocumentController@verification` |
| **Compliance** | Document Monitoring | `admin.documents.monitoring` | `GET` | `/admin/documents/monitoring` | `AdminLandlordDocumentController@monitoring` |

### Auxiliary & Connected Action Routes In Scope
- `/admin/properties/{property}/inspect` (`PropertyController@adminInspect`)
- `/admin/properties/{property}/approve` (`PropertyController@adminApprove`)
- `/admin/properties/{property}/reject` (`PropertyController@adminReject`)
- `/admin/boarding-monitoring/students/print` (`AdminBookingController@printBoardedStudents`)
- `/admin/onboardings/active` (`TenantOnboardingController@adminActive`)
- `/admin/onboardings/pending` (`TenantOnboardingController@adminPending`)
- `/admin/onboardings/completed` (`TenantOnboardingController@adminCompleted`)
- `/admin/onboardings/{id}` (`TenantOnboardingController@adminShow`)
- `/admin/onboardings/{id}/contract` (`TenantOnboardingController@viewContract`)
- `/admin/onboardings/{id}/contract/pdf` (`TenantOnboardingController@downloadContractPdf`)
- `/admin/approvals/students/{id}/approve` (`AuthController@adminApproveStudentVerification`)
- `/admin/approvals/students/{id}/reject` (`AuthController@adminRejectStudentVerification`)
- `/admin/approvals/landlords/{id}/approve-permit` (`AuthController@adminApproveLandlordPermit`)
- `/admin/approvals/landlords/{id}/reject-permit` (`AuthController@adminRejectLandlordPermit`)
- `/admin/approvals/landlords/{id}/approve-safety` (`AuthController@adminApproveLandlordSafetyCertificate`)
- `/admin/approvals/landlords/{id}/reject-safety` (`AuthController@adminRejectLandlordSafetyCertificate`)
- `/admin/documents/{id}/approve` (`AdminLandlordDocumentController@approve`)
- `/admin/documents/{id}/reject` (`AdminLandlordDocumentController@reject`)
- `/files/private/{path}` (`FileViewerController@showPrivateFile`)

---

## 3. Middleware & Security Boundaries
All 7 pages and their connected endpoints are strictly protected by:
- `auth`: Requires authenticated session.
- `role:admin`: Enforces `users.role === 'admin'`.

Private documents accessed from review queues are served via:
- `App\Http\Controllers\FileViewerController@showPrivateFile` requiring strict server-side authorization (`admin`, or student/landlord owning the record).

---

## 4. Models, Services, and Blade Views Involved

### Primary Models
- `App\Models\User` (Student/Landlord/Admin profiles, academic fields, school ID verification)
- `App\Models\LandlordProfile` (Business permits, safety certificates, verification statuses)
- `App\Models\Property` (Boarding house metadata, rooms, capacity, approval status, compliance)
- `App\Models\Room` (Capacity, price, bed slots)
- `App\Models\Booking` (Stay requests, statuses, check-in/out dates, price, active tenancy)
- `App\Models\TenantOnboarding` (Stages, document tracking, digital contracts, deposit status)
- `App\Models\LandlordDocument` (Multi-type compliance document registry, expiry dates, verification state)

### Services & Query Helpers
- `App\Services\AdminBoardingAnalyticsService`
- `App\Services\Chatbot\ChatbotAnalyticsService`
- `App\Services\Chatbot\LandlordComplianceLookupService`

### Blade Views
- `resources/views/admin/properties.blade.php`
- `resources/views/admin/boarding-monitoring/students.blade.php`
- `resources/views/admin/onboardings/index.blade.php`
- `resources/views/admin/onboardings/show.blade.php`
- `resources/views/admin/approvals/students.blade.php`
- `resources/views/admin/approvals/landlords.blade.php`
- `resources/views/admin/documents/verification.blade.php`
- `resources/views/admin/documents/monitoring.blade.php`
- `resources/views/layouts/admin.blade.php` (Navigation & Sidebar)

---

## 5. Tests Protecting In-Scope Workflows
- `tests/Feature/AdminBoardingMonitoringTest.php`
- `tests/Feature/AdminBoardingMonitoringRefinementTest.php`
- `tests/Feature/AdminAnalyticsTest.php`
- `tests/Feature/AdminDashboardGenderAndDocumentsTest.php`
- `tests/Feature/AdminDocumentVerificationFixesTest.php`
- `tests/Feature/LandlordDocumentTest.php`
- `tests/Feature/FileSecurityAuthorizationTest.php`
- `tests/Feature/BookingTenancyAuthorizationTest.php`
- `tests/Feature/TenantOnboardingTest.php`
