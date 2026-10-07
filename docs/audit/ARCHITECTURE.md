# OBHS Full Architecture Map

This document provides a comprehensive map of the actual, implemented architecture of the Online Boarding House System (OBHS).

---

## 1. Request Lifecycle & Routing Architecture

```
HTTP Request
  │
  ├──► Root Apache Web Server (.htaccess)
  │      └──► Routes non-file requests to public/index.php
  │
  ├──► Laravel 12 Application Bootstrap (bootstrap/app.php)
  │      ├── Middleware Aliases:
  │      │     'role'             => App\Http\Middleware\RoleMiddleware
  │      │     'role.selected'    => App\Http\Middleware\EnsureRoleSelected
  │      │     'student.setup'    => App\Http\Middleware\EnsureStudentSetupComplete
  │      │     'landlord.setup'   => App\Http\Middleware\EnsureLandlordSetupStep
  │      │     'verified'         => Illuminate\Auth\Middleware\EnsureEmailIsVerified
  │      │
  │      └── Global Exception Handling (403, 404, Unauthorized views)
  │
  └──► Route Dispatcher (routes/web.php & routes/console.php)
         ├── Public Endpoints (Landing, Browse Map, Public Room, Login/Register)
         ├── Auth & Verification Endpoints (/email/verify, /onboarding/role)
         ├── Shared Authenticated Endpoints (/messages, /notifications, /files/{path})
         ├── Admin Portal (/admin/*)
         ├── Landlord Portal (/landlord/*)
         └── Student Portal (/student/*)
```

---

## 2. Subsystem Mapping

### A. Authentication & Onboarding
- **Entry Routes:**
  - `GET /login`, `POST /login`, `POST /logout`
  - `GET /register`, `GET /register/student`, `GET /register/landlord`, `POST /register`
  - `GET /register/admin`, `POST /register/admin` (role-gated or config-gated)
  - `GET /onboarding/role`, `POST /onboarding/role`
  - `GET /email/verify`, `POST /email/verification-notification`, `GET /email/verify/{id}/{hash}`
- **Middleware:** `auth`, `verified`, `role`, `role.selected`
- **Controllers:** [`AuthController`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/app/Http/Controllers/AuthController.php), [`OnboardingRoleController`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/app/Http/Controllers/OnboardingRoleController.php)
- **Models:** [`User`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/app/Models/User.php), [`LandlordProfile`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/app/Models/LandlordProfile.php)
- **Database Tables:** `users`, `landlord_profiles`
- **Blade Views:** `resources/views/auth/*` (`login.blade.php`, `register.blade.php`, `verify-email.blade.php`, `onboarding-role.blade.php`)

### B. Student Setup & Profile
- **Entry Routes:**
  - `GET /student/setup`, `PUT /student/setup`
  - `GET /student/profile`, `GET /student/profile/edit`, `PUT /student/profile`
  - `GET /student/profile/change-password`, `PUT /student/profile/change-password`
- **Middleware:** `auth`, `verified`, `role:student`
- **Controllers:** [`StudentSetupController`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/app/Http/Controllers/StudentSetupController.php), [`StudentProfileController`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/app/Http/Controllers/StudentProfileController.php)
- **Services:** [`AcademicCatalogService`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/app/Services/AcademicCatalogService.php), [`FileStorageService`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/app/Services/FileStorageService.php)
- **Models:** [`User`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/app/Models/User.php)
- **Database Tables:** `users` (student fields: `student_id_number`, `college`, `program`, `major`, `school_id_path`, `school_id_verification_status`, `enrollment_proof_path`, etc.)
- **Blade Views:** `resources/views/student/setup.blade.php`, `resources/views/student/profile/*`

### C. Landlord Setup & Profile
- **Entry Routes:**
  - `GET /landlord/setup`, `PUT /landlord/setup`
  - `GET /landlord/profile`, `PUT /landlord/profile`
  - `GET /landlord/documents`, `POST /landlord/documents`, `POST /landlord/documents/{document}/resubmit`
- **Middleware:** `auth`, `verified`, `role:landlord`
- **Controllers:** [`LandlordSetupController`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/app/Http/Controllers/LandlordSetupController.php), [`LandlordDocumentController`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/app/Http/Controllers/LandlordDocumentController.php)
- **Services:** [`FileStorageService`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/app/Services/FileStorageService.php), [`DocumentExpirationService`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/app/Services/DocumentExpirationService.php), [`LandlordDocumentStatusService`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/app/Services/LandlordDocumentStatusService.php)
- **Models:** [`LandlordProfile`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/app/Models/LandlordProfile.php), [`LandlordDocument`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/app/Models/LandlordDocument.php)
- **Database Tables:** `landlord_profiles`, `landlord_documents`
- **Blade Views:** `resources/views/landlord/setup.blade.php`, `resources/views/landlord/documents/index.blade.php`, `resources/views/landlord/profile.blade.php`

### D. Public Marketplace & Browsing
- **Entry Routes:**
  - `GET /` (`landing`)
  - `GET /rooms/{room}` (`rooms.public.show`)
  - `GET /browse-map`, `GET /browse-map/data`, `GET /browse-map/suggestions`, `GET /browse-map/properties/{property}/rooms`
- **Middleware:** `web` (Public)
- **Controllers:** [`RoomController`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/app/Http/Controllers/RoomController.php), [`PublicPropertyMapController`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/app/Http/Controllers/PublicPropertyMapController.php)
- **Models:** [`Property`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/app/Models/Property.php), [`Room`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/app/Models/Room.php), [`RoomFeedback`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/app/Models/RoomFeedback.php)
- **Database Tables:** `properties`, `rooms`, `room_feedbacks`, `users`, `landlord_profiles`
- **Blade Views:** `resources/views/landing.blade.php`, `resources/views/rooms/public-show.blade.php`, `resources/views/public/properties/map.blade.php`

### E. Student Housing Discovery & Booking
- **Entry Routes:**
  - `GET /student/dashboard`, `GET /student/tenant-dashboard`
  - `GET /student/properties/map`, `GET /student/properties/map-data`, `GET /student/properties/{property}`
  - `GET /student/rooms`, `GET /student/rooms/{room}`, `POST /student/rooms/{room}/inquire`
  - `GET /student/rooms/{room}/book`, `POST /student/rooms/{room}/book`
  - `GET /student/requests`, `POST /student/requests/{booking}/cancel`
  - `GET /student/rooms/{room}/feedback`, `POST /student/rooms/{room}/feedback`
- **Middleware:** `auth`, `verified`, `role:student`, `student.setup`
- **Policies:** [`BookingPolicy`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/app/Policies/BookingPolicy.php), [`RoomPolicy`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/app/Policies/RoomPolicy.php), [`PropertyPolicy`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/app/Policies/PropertyPolicy.php)
- **Controllers:** [`AuthController`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/app/Http/Controllers/AuthController.php), [`StudentPropertyController`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/app/Http/Controllers/StudentPropertyController.php), [`RoomController`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/app/Http/Controllers/RoomController.php), [`BookingController`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/app/Http/Controllers/BookingController.php), [`RoomFeedbackController`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/app/Http/Controllers/RoomFeedbackController.php)
- **Models:** [`Booking`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/app/Models/Booking.php), [`Room`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/app/Models/Room.php), [`Property`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/app/Models/Property.php), [`TenantOnboarding`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/app/Models/TenantOnboarding.php)
- **Database Tables:** `bookings`, `rooms`, `properties`, `tenant_onboardings`, `room_feedbacks`
- **Blade Views:** `resources/views/student/dashboard.blade.php`, `resources/views/student/tenant-dashboard.blade.php`, `resources/views/student/rooms/*`, `resources/views/student/bookings/*`

### F. Landlord Property, Room, and Booking Management
- **Entry Routes:**
  - `GET /landlord/dashboard`
  - `GET /landlord/properties`, `GET /landlord/properties/create`, `POST /landlord/properties`, `GET /landlord/properties/{property}`, `GET /landlord/properties/{property}/edit`, `PUT /landlord/properties/{property}`, `DELETE /landlord/properties/{property}`
  - `GET /landlord/properties/{property}/rooms`, `GET /landlord/properties/{property}/rooms/create`, `POST /landlord/properties/{property}/rooms`, `GET /landlord/properties/{property}/rooms/{room}/edit`, `PUT /landlord/properties/{property}/rooms/{room}`, `DELETE /landlord/properties/{property}/rooms/{room}`, `POST /landlord/dashboard/properties/{property}/rooms`
  - `GET /landlord/rooms`
  - `GET /landlord/bookings`, `POST /landlord/bookings/{booking}/approve`, `POST /landlord/bookings/{booking}/reject`
  - `GET /landlord/tenants`
  - `GET /landlord/maintenance`, `POST /landlord/maintenance/set`, `POST /landlord/maintenance/{room}/complete`
  - `GET /landlord/analytics`
  - `GET /landlord/feedback`, `POST /landlord/feedback/analyze`
- **Middleware:** `auth`, `verified`, `role:landlord`, `landlord.setup:profile_complete`
- **Policies:** [`PropertyPolicy`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/app/Policies/PropertyPolicy.php), [`RoomPolicy`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/app/Policies/RoomPolicy.php), [`BookingPolicy`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/app/Policies/BookingPolicy.php)
- **Controllers:** [`PropertyController`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/app/Http/Controllers/PropertyController.php), [`RoomController`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/app/Http/Controllers/RoomController.php), [`BookingController`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/app/Http/Controllers/BookingController.php), [`MaintenanceController`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/app/Http/Controllers/MaintenanceController.php), [`AnalyticsController`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/app/Http/Controllers/AnalyticsController.php), [`LandlordFeedbackController`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/app/Http/Controllers/LandlordFeedbackController.php)
- **Database Tables:** `properties`, `rooms`, `room_images`, `bookings`, `tenant_onboardings`

### G. Tenancy, Contracts & Onboarding Workflow
- **Entry Routes:**
  - Student: `GET /student/onboarding`, `GET /student/onboarding/{onboarding}`, `GET /student/onboarding/{onboarding}/contract`, `GET /student/onboarding/{onboarding}/contract-pdf`, `POST /student/onboarding/{onboarding}/documents`, `POST /student/onboarding/{onboarding}/sign-contract`, `POST /student/onboarding/{onboarding}/pay-deposit`
  - Landlord: `GET /landlord/onboarding`, `GET /landlord/onboarding/{onboarding}/review`, `GET /landlord/onboarding/{onboarding}/contract`, `GET /landlord/onboarding/{onboarding}/contract-pdf`, `POST /landlord/onboarding/{onboarding}/sign-contract`, `POST /landlord/onboarding/{onboarding}/approve-documents`
  - Admin: `GET /admin/onboardings`, `GET /admin/onboardings/active`, `GET /admin/onboardings/pending`, `GET /admin/onboardings/completed`, `GET /admin/onboardings/{onboarding}`, `GET /admin/onboardings/{onboarding}/contract`, `GET /admin/onboardings/{onboarding}/contract-pdf`
- **Middleware:** `auth`, `verified`, role-specific middleware
- **Policies:** [`TenantOnboardingPolicy`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/app/Policies/TenantOnboardingPolicy.php)
- **Controllers:** [`TenantOnboardingController`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/app/Http/Controllers/TenantOnboardingController.php)
- **Services:** `Barryvdh\DomPDF\Facade\Pdf`, [`FileStorageService`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/app/Services/FileStorageService.php), [`RoomOccupancyRules`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/app/Services/RoomOccupancyRules.php)
- **Models:** [`TenantOnboarding`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/app/Models/TenantOnboarding.php), [`Booking`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/app/Models/Booking.php), [`Room`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/app/Models/Room.php), [`User`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/app/Models/User.php)
- **Database Tables:** `tenant_onboardings`, `bookings`, `rooms`

### H. Payments & Financial Tracking
- **Entry Routes:**
  - Student: `GET /student/payments`, `POST /student/payments`
  - Landlord: `GET /landlord/payments`, `GET /landlord/payments/{booking}/manage`, `POST /landlord/payments/{booking}/paid`, `POST /landlord/payments/{booking}/pending`, `POST /landlord/payments/{booking}/remind`, `POST /landlord/payments/{booking}/records`, `POST /landlord/payments/{booking}/records/{record}/status`
- **Middleware:** `auth`, `verified`, role-specific middleware
- **Controllers:** [`StudentPaymentController`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/app/Http/Controllers/StudentPaymentController.php), [`PaymentController`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/app/Http/Controllers/PaymentController.php)
- **Models:** [`TenantPayment`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/app/Models/TenantPayment.php), [`Booking`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/app/Models/Booking.php), [`TenantOnboarding`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/app/Models/TenantOnboarding.php)
- **Database Tables:** `tenant_payments`, `bookings`, `tenant_onboardings`

### I. Messaging, Inquiries & Notifications
- **Entry Routes:**
  - `GET /messages`, `POST /messages`, `POST /messages/{message}/read`
  - `GET /notifications`, `POST /notifications/read-all`, `POST /notifications/{id}/read`
- **Middleware:** `auth`, `verified`, `role.selected`, `student.setup`
- **Policies:** [`MessagePolicy`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/app/Policies/MessagePolicy.php)
- **Controllers:** [`MessageController`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/app/Http/Controllers/MessageController.php), [`NotificationController`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/app/Http/Controllers/NotificationController.php)
- **Models:** [`Message`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/app/Models/Message.php), `Notification` (database notifications)
- **Notifications:** [`SystemNotification`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/app/Notifications/SystemNotification.php) (channels: `database`, `mail`)

### J. AI Assistant & Chatbot
- **Entry Routes:**
  - `GET /chatbot/history`, `POST /chatbot/message` (throttled)
- **Middleware:** `auth`, `verified`, `role.selected`, `student.setup`, `throttle:chatbot`
- **Controllers:** [`ChatbotController`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/app/Http/Controllers/ChatbotController.php)
- **Services:**
  - [`AiAssistantService`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/app/Services/AI/AiAssistantService.php): Multi-turn reasoning, tool calling loop, HuggingFace inference
  - [`AiPromptBuilder`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/app/Services/AI/AiPromptBuilder.php): System prompt construction with role guardrails and local datetime
  - [`AiToolRegistry`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/app/Services/AI/AiToolRegistry.php): Catalog of all approved tools with strict `allowed_roles`
  - [`AiToolExecutor`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/app/Services/AI/AiToolExecutor.php): Server-side execution and output sanitization
  - [`AiConversationService`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/app/Services/AI/AiConversationService.php): Conversation persistence and memory
- **Models:** [`ChatbotConversation`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/app/Models/ChatbotConversation.php), [`ChatbotMessage`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/app/Models/ChatbotMessage.php)
- **Database Tables:** `chatbot_conversations`, `chatbot_messages`

### K. Admin Monitoring, Moderation & Reporting
- **Entry Routes:**
  - Dashboard: `GET /admin/dashboard`, `GET /admin/dashboard/stats`
  - User & Approval Management: `GET /admin/users`, `GET /admin/users/students`, `GET /admin/users/landlords`, `GET /admin/approvals/students`, `GET /admin/approvals/landlords`
  - Document Verification & Monitoring: `GET /admin/documents/verification`, `GET /admin/documents/monitoring`, `POST /admin/documents/{document}/approve`, `POST /admin/documents/{document}/reject`
  - Property Approval: `GET /admin/properties`, `GET /admin/properties/{property}/inspect`, `POST /admin/properties/{property}/approve`, `POST /admin/properties/{property}/reject`
  - Boarding Monitoring: `GET /admin/bookings`, `GET /admin/boarding-monitoring/students`, `GET /admin/boarding-monitoring/students/print`
  - Analytics: `GET /admin/analytics`
  - Incident Reports: `GET /admin/reports`, `GET /admin/reports/{report}`, `PUT /admin/reports/{report}`
- **Middleware:** `role:admin`
- **Controllers:** [`AuthController`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/app/Http/Controllers/AuthController.php), [`AdminBookingController`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/app/Http/Controllers/AdminBookingController.php), [`AdminLandlordDocumentController`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/app/Http/Controllers/AdminLandlordDocumentController.php), [`AdminAnalyticsController`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/app/Http/Controllers/AdminAnalyticsController.php), [`PropertyController`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/app/Http/Controllers/PropertyController.php), [`ReportController`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/app/Http/Controllers/ReportController.php), [`TenantOnboardingController`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/app/Http/Controllers/TenantOnboardingController.php)
- **Services:** [`BoardingMonitoringService`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/app/Services/BoardingMonitoringService.php), [`AnalyticsService`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/app/Services/AnalyticsService.php), [`LandlordDocumentStatusService`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/app/Services/LandlordDocumentStatusService.php)
- **Database Tables:** `users`, `landlord_profiles`, `landlord_documents`, `properties`, `rooms`, `bookings`, `tenant_onboardings`, `reports`, `tenant_payments`

### L. File Storage Subsystem
- **Entry Route:** `GET /files/{path}`
- **Middleware:** `auth`, `verified`
- **Controller:** [`FileController`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/app/Http/Controllers/FileController.php)
- **Service:** [`FileStorageService`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/app/Services/FileStorageService.php)
- **Storage Strategy:**
  1. Writes prefer Cloudflare R2 (S3-compatible) when configured; falls back to Supabase S3 or local `public` disk.
  2. Reads resolve via fallback chain: R2 -> Supabase -> Local public disk.
  3. Authorization is enforced dynamically by prefix (`landlords/{id}/*`, `students/{id}/*`, `tenant-onboardings/{id}/*`, `payments/{id}/*`, etc.) and verified against database ownership records.

---

## 3. External Integrations Inventory

| Service | Protocol / Client | Integration Point | Fallback Behavior |
| :--- | :--- | :--- | :--- |
| **Cloudflare R2** | AWS S3 SDK / Flysystem | [`FileStorageService`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/app/Services/FileStorageService.php) | Falls back to Supabase S3 / local disk |
| **Supabase Storage** | AWS S3 SDK / Flysystem | [`FileStorageService`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/app/Services/FileStorageService.php) | Falls back to local public disk |
| **Hugging Face Inference API** | HTTP POST (`Qwen/Qwen2.5-Coder-32B-Instruct` or similar) | [`AiAssistantService`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/app/Services/AI/AiAssistantService.php) | Graceful error reply + retry on transient errors |
| **Nominatim / Geocoder** | HTTP API / OpenStreetMap | [`GeocodingService`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/app/Services/GeocodingService.php) | Defaults to Calapan City coordinates (13.4115, 121.1803) |
| **DomPDF** | Barryvdh DomPDF facade | [`TenantOnboardingController`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/app/Http/Controllers/TenantOnboardingController.php) | Renders formatted HTML lease agreement |
| **SMTP Mail** | Laravel Mailer | Notifications & reminders | Array driver in test; SMTP in production |
