# OBHS Landlord Portal: Current Features and Route Inventory

**Document ID:** `02_CURRENT_FEATURES_AND_ROUTES.md`  
**Date:** October 18, 2026  
**Auditor:** Senior Laravel Architect & QA Lead  
**Scope:** Exhaustive catalog of all landlord-facing endpoints, controllers, methods, database models, policies, and Blade templates.

---

## 1. Complete Route Inventory (`routes/web.php`)

All landlord endpoints are protected by the `auth`, `verified`, and role-gating middleware stack (`role:landlord` or policy authorization).

| HTTP Method | URI Pattern | Route Name | Controller & Method | Primary Models | Primary Blade View |
| :--- | :--- | :--- | :--- | :--- | :--- |
| `GET` | `/landlord/dashboard` | `landlord.dashboard` | `AuthController@landlordDashboard` | `Property`, `Room`, `Booking`, `Message`, `LandlordDocument` | `resources/views/landlord/dashboard.blade.php` |
| `POST` | `/landlord/dashboard/properties/{property}/rooms` | `landlord.dashboard.rooms.quick_store` | `RoomController@quickStore` | `Room`, `Property` | Redirects to `landlord.dashboard` |
| `GET` | `/landlord/setup` | `landlord.setup.show` | `LandlordSetupController@show` | `User`, `LandlordProfile` | `resources/views/landlord/setup/index.blade.php` |
| `PUT` | `/landlord/setup` | `landlord.setup.update` | `LandlordSetupController@update` | `User`, `LandlordProfile` | Redirects to `landlord.setup.show` |
| `POST` | `/landlord/signature` | `landlord.signature.save` | `LandlordSetupController@saveSignature` | `User`, `LandlordProfile` | JSON response / redirect |
| `GET` | `/landlord/profile` | `landlord.profile.edit` | `AuthController@landlordProfile` | `User`, `LandlordProfile` | `resources/views/landlord/profile/edit.blade.php` |
| `PUT` | `/landlord/profile` | `landlord.profile.update` | `AuthController@updateLandlordProfile` | `User`, `LandlordProfile` | Redirects to `landlord.profile.edit` |
| `GET` | `/landlord/properties` | `landlord.properties.index` | `PropertyController@index` | `Property`, `Room` | `resources/views/landlord/properties/index.blade.php` |
| `GET` | `/landlord/properties/create` | `landlord.properties.create` | `PropertyController@create` | `Property` | `resources/views/landlord/properties/create.blade.php` |
| `POST` | `/landlord/properties` | `landlord.properties.store` | `PropertyController@store` | `Property`, `Room` | Redirects to `landlord.properties.show` |
| `GET` | `/landlord/properties/{property}` | `landlord.properties.show` | `PropertyController@show` | `Property`, `Room`, `Booking` | `resources/views/landlord/properties/show.blade.php` |
| `GET` | `/landlord/properties/{property}/edit` | `landlord.properties.edit` | `PropertyController@edit` | `Property` | `resources/views/landlord/properties/edit.blade.php` |
| `PUT` | `/landlord/properties/{property}` | `landlord.properties.update` | `PropertyController@update` | `Property` | Redirects to `landlord.properties.index` |
| `DELETE` | `/landlord/properties/{property}` | `landlord.properties.destroy` | `PropertyController@destroy` | `Property` | Redirects to `landlord.properties.index` |
| `GET` | `/landlord/rooms` | `landlord.rooms.index` | `RoomController@landlordIndex` | `Room`, `Property` | `resources/views/landlord/rooms/landlord_index.blade.php` |
| `GET` | `/landlord/properties/{property}/rooms` | `landlord.properties.rooms.index` | `RoomController@index` | `Room`, `Property` | `resources/views/landlord/rooms/index.blade.php` |
| `GET` | `/landlord/properties/{property}/rooms/create` | `landlord.properties.rooms.create` | `RoomController@create` | `Room`, `Property` | `resources/views/landlord/rooms/create.blade.php` |
| `POST` | `/landlord/properties/{property}/rooms` | `landlord.properties.rooms.store` | `RoomController@store` | `Room`, `Property` | Redirects to `landlord.properties.rooms.index` |
| `GET` | `/landlord/properties/{property}/rooms/{room}/edit` | `landlord.properties.rooms.edit` | `RoomController@edit` | `Room`, `Property`, `RoomImage` | `resources/views/landlord/rooms/edit.blade.php` |
| `PUT` | `/landlord/properties/{property}/rooms/{room}` | `landlord.properties.rooms.update` | `RoomController@update` | `Room`, `Property`, `RoomImage` | Redirects to `landlord.properties.rooms.index` |
| `DELETE` | `/landlord/properties/{property}/rooms/{room}` | `landlord.properties.rooms.destroy` | `RoomController@destroy` | `Room`, `Property` | Redirects to `landlord.properties.rooms.index` |
| `GET` | `/landlord/bookings` | `landlord.bookings.index` | `BookingController@landlordIndex` | `Booking`, `Room`, `Property`, `User` | `resources/views/landlord/bookings/index.blade.php` |
| `POST` | `/landlord/bookings/{booking}/approve` | `landlord.bookings.approve` | `BookingController@approve` | `Booking`, `Room`, `TenantOnboarding` | Redirects back with flash message |
| `POST` | `/landlord/bookings/{booking}/reject` | `landlord.bookings.reject` | `BookingController@reject` | `Booking`, `Room` | Redirects back with flash message |
| `GET` | `/landlord/tenants` | `landlord.tenants.index` | `BookingController@landlordTenants` | `Booking`, `Room`, `Property`, `User` | `resources/views/landlord/tenants/index.blade.php` |
| `GET` | `/landlord/onboarding` | `landlord.onboarding.index` | `TenantOnboardingController@landlordIndex` | `TenantOnboarding`, `Booking` | `resources/views/landlord/onboarding/index.blade.php` |
| `GET` | `/landlord/onboarding/{onboarding}/review` | `landlord.onboarding.review` | `TenantOnboardingController@reviewDocuments` | `TenantOnboarding`, `Booking` | `resources/views/landlord/onboarding/review.blade.php` |
| `POST` | `/landlord/onboarding/{onboarding}/approve-documents` | `landlord.onboarding.approve_documents` | `TenantOnboardingController@approveDocuments` | `TenantOnboarding`, `Booking` | Redirects back with flash message |
| `GET` | `/landlord/onboarding/{onboarding}/contract` | `landlord.onboarding.contract` | `TenantOnboardingController@viewContract` | `TenantOnboarding`, `Booking` | `resources/views/onboarding/contract_view.blade.php` |
| `POST` | `/landlord/onboarding/{onboarding}/sign-contract` | `landlord.onboarding.sign_contract` | `TenantOnboardingController@signContractLandlord` | `TenantOnboarding` | Redirects back with flash message |
| `GET` | `/landlord/onboarding/{onboarding}/contract-pdf` | `landlord.onboarding.contract_pdf` | `TenantOnboardingController@downloadContractPdf` | `TenantOnboarding` | DomPDF stream / download response |
| `GET` | `/landlord/payments` | `landlord.payments.index` | `PaymentController@index` | `Booking`, `TenantPayment`, `TenantOnboarding` | `resources/views/landlord/payments/index.blade.php` |
| `GET` | `/landlord/payments/{booking}/manage` | `landlord.payments.manage` | `PaymentController@manageMonthly` | `Booking`, `TenantPayment` | `resources/views/landlord/payments/manage_monthly.blade.php` |
| `POST` | `/landlord/payments/{booking}/paid` | `landlord.payments.mark_paid` | `PaymentController@markAsPaid` | `Booking` | Redirects back with flash message |
| `POST` | `/landlord/payments/{booking}/pending` | `landlord.payments.mark_pending` | `PaymentController@markAsPending` | `Booking` | Redirects back with flash message |
| `POST` | `/landlord/payments/{booking}/records` | `landlord.payments.records.store` | `PaymentController@storeMonthlyRecord` | `TenantPayment`, `Booking` | Redirects back with flash message |
| `POST` | `/landlord/payments/{booking}/records/{record}/status` | `landlord.payments.records.status` | `PaymentController@updateRecordStatus` | `TenantPayment`, `Booking` | Redirects back with flash message |
| `POST` | `/landlord/payments/{booking}/remind` | `landlord.payments.remind` | `PaymentController@sendReminder` | `Booking`, `User` | Redirects back with flash message |
| `GET` | `/landlord/documents` | `landlord.documents.index` | `LandlordDocumentController@index` | `LandlordDocument`, `User` | `resources/views/landlord/documents/index.blade.php` |
| `POST` | `/landlord/documents` | `landlord.documents.store` | `LandlordDocumentController@store` | `LandlordDocument`, `User` | Redirects back with flash message |
| `POST` | `/landlord/documents/{document}/resubmit` | `landlord.documents.resubmit` | `LandlordDocumentController@resubmit` | `LandlordDocument`, `User` | Redirects back with flash message |
| `GET` | `/landlord/monitoring/properties` | `landlord.monitoring.properties` | `LandlordMonitoringController@properties` | `Property`, `Room`, `Booking` | `resources/views/landlord/monitoring/properties.blade.php` |
| `GET` | `/landlord/monitoring/compliance` | `landlord.monitoring.compliance` | `LandlordMonitoringController@compliance` | `LandlordDocument`, `User` | `resources/views/landlord/monitoring/compliance.blade.php` |
| `GET` | `/landlord/maintenance` | `landlord.maintenance.index` | `MaintenanceController@index` | `Room`, `Property` | `resources/views/landlord/maintenance/index.blade.php` |
| `POST` | `/landlord/maintenance/set` | `landlord.maintenance.set` | `MaintenanceController@setMaintenance` | `Room` | Redirects back with flash message |
| `POST` | `/landlord/maintenance/{room}/complete` | `landlord.maintenance.complete` | `MaintenanceController@completeMaintenance` | `Room` | Redirects back with flash message |
| `GET` | `/landlord/leave-requests` | `landlord.leave_requests.index` | `LandlordLeaveRequestController@index` | `LeaveRequest`, `Booking` | `resources/views/landlord/leave_requests/index.blade.php` |
| `POST` | `/landlord/leave-requests/{leaveRequest}/approve` | `landlord.leave_requests.approve` | `LandlordLeaveRequestController@approve` | `LeaveRequest`, `Booking`, `Room` | Redirects back with flash message |
| `POST` | `/landlord/leave-requests/{leaveRequest}/reject` | `landlord.leave_requests.reject` | `LandlordLeaveRequestController@reject` | `LeaveRequest` | Redirects back with flash message |
| `GET` | `/landlord/analytics` | `landlord.analytics.index` | `AnalyticsController@index` | `Property`, `Room`, `Booking`, `TenantPayment` | `resources/views/landlord/analytics/index.blade.php` |
| `GET` | `/landlord/feedback` | `landlord.feedback.index` | `LandlordFeedbackController@index` | `RoomFeedback`, `Room`, `Property` | `resources/views/landlord/feedback/index.blade.php` |
| `POST` | `/landlord/feedback/analyze` | `landlord.feedback.analyze` | `LandlordFeedbackController@analyzePending` | `RoomFeedback` | Redirects back with flash message |
| `GET` | `/landlord/messages` | `landlord.messages.index` | `MessageController@index` | `Message`, `User` | `resources/views/landlord/messages/index.blade.php` |
| `GET` | `/documents/{onboarding}/{filename}` | `documents.view` | `TenantOnboardingController@viewDocument` | `TenantOnboarding` | Stream file response |

---

## 2. Shared Layout & Component Assets

- **Base Layout:** `resources/views/layouts/landlord.blade.php` (801 lines, 36.9 KB)
  - Contains inline CSS design system tokens (`--brand: #14532d`, `--shell: #f8fafc`, etc.).
  - Hardcodes a 14-item navigation menu with nested property and room collapse accordions.
  - Implements an offcanvas mobile drawer (`#landlordSidebar`).
- **Dashboard Modals:** `resources/views/landlord/dashboard.blade.php`
  - Embedded `#addPropertyModal` (lines 675–746).
  - Embedded `#addRoomModal` (lines 748–820).
  - Embedded `#landlordSetupReminderModal` (lines 601–673).
- **Frontend Dependencies (Vite & CDN):**
  - Bootstrap 5.3.2 + Bootstrap Icons 1.11.3 + FontAwesome 6.4.0 (CDN).
  - Leaflet 1.9.4 for map coordinates (`properties/create.blade.php`).
  - Chart.js 4.4.1 for analytics charts (`dashboard.blade.php`, `analytics/index.blade.php`).
  - Signature Pad for canvas-based digital signatures (`LandlordSetupController`).

---

## 3. Policy & Authorization Boundary Map

| Entity | Policy Class | Enforced Restriction | Controller Evidence |
| :--- | :--- | :--- | :--- |
| `Property` | `App\Policies\PropertyPolicy` | `landlord_id === Auth::id()` | `PropertyController@create, store, edit, update, destroy` |
| `Room` | `App\Policies\RoomPolicy` | `room->property->landlord_id === Auth::id()` | `RoomController@getOwnedProperty()` |
| `Booking` | `App\Policies\BookingPolicy` | `booking->room->property->landlord_id === Auth::id()` | `BookingController@approve, reject` |
| `TenantOnboarding` | `App\Policies\TenantOnboardingPolicy` | Must belong to landlord's property or paying student | `TenantOnboardingController@approveDocuments, signContractLandlord` |
| `LandlordDocument` | Controller-scoped query | `landlord_id === Auth::id()` | `LandlordDocumentController@index, store` |
| `LeaveRequest` | Controller-scoped check | `leaveRequest->landlord_id === Auth::id()` | `LandlordLeaveRequestController@approve, reject` |
