# Code Ownership & Domain Agent Mapping

This document maps all repository paths, controllers, models, services, and views to their designated primary and secondary domain agents.

---

## 1. Domain Agent Directory Mapping

| Repository Path / File | Primary Owning Agent | Supporting / Secondary Agents |
| :--- | :--- | :--- |
| `routes/web.php` | `obhs-security-auth` | `obhs-architect` |
| `routes/console.php` | `obhs-database` | `obhs-hostinger` |
| `config/auth.php` | `obhs-security-auth` | `obhs-architect` |
| `config/filesystems.php` | `obhs-documents-storage` | `obhs-hostinger` |
| `config/database.php` | `obhs-database` | `obhs-hostinger` |
| `app/Http/Middleware/*` | `obhs-security-auth` | `obhs-architect` |
| `app/Policies/*` | `obhs-security-auth` | Domain-specific agents |

---

## 2. Controller Ownership

| Controller | Primary Agent | Secondary Agent | Notes |
| :--- | :--- | :--- | :--- |
| `AuthController.php` | `obhs-security-auth` | `obhs-admin` | Authentication, registrations, admin dashboard stats |
| `OnboardingRoleController.php` | `obhs-security-auth` | `obhs-student`, `obhs-landlord` | Role selection |
| `StudentSetupController.php` | `obhs-student` | `obhs-security-auth` | Student ID verification |
| `StudentProfileController.php` | `obhs-student` | `obhs-security-auth` | Student profile & password |
| `StudentPropertyController.php` | `obhs-student` | `obhs-marketplace` | Student map & property views |
| `StudentPaymentController.php` | `obhs-payments` | `obhs-student` | Student monthly payments |
| `StudentLeaveRequestController.php`| `obhs-student` | `obhs-booking-tenancy` | Move-out & leave requests |
| `LandlordSetupController.php` | `obhs-landlord` | `obhs-security-auth` | Landlord profile setup |
| `LandlordDocumentController.php` | `obhs-documents-storage`| `obhs-landlord` | Landlord permits & safety certs |
| `LandlordFeedbackController.php` | `obhs-landlord` | `obhs-ai` | Review sentiment analysis |
| `LandlordLeaveRequestController.php`| `obhs-landlord` | `obhs-booking-tenancy` | Landlord leave approvals |
| `PropertyController.php` | `obhs-landlord` | `obhs-marketplace`, `obhs-admin` | Property CRUD & admin inspection |
| `RoomController.php` | `obhs-landlord` | `obhs-marketplace`, `obhs-booking-tenancy` | Room CRUD, slots, pricing |
| `PublicPropertyMapController.php` | `obhs-marketplace` | `obhs-ui` | Public map discovery |
| `BookingController.php` | `obhs-booking-tenancy` | `obhs-student`, `obhs-landlord` | Booking creation, approval, cancellation |
| `TenantOnboardingController.php` | `obhs-booking-tenancy` | `obhs-documents-storage`, `obhs-payments` | Onboarding lifecycle, lease signing |
| `PaymentController.php` | `obhs-payments` | `obhs-landlord` | Landlord payment ledger & reminders |
| `MaintenanceController.php` | `obhs-landlord` | `obhs-booking-tenancy` | Room maintenance states |
| `ReportController.php` | `obhs-student` | `obhs-admin` | Incident reports & resolutions |
| `MessageController.php` | `obhs-messaging-notifications` | `obhs-security-auth` | Direct & inquiry messaging |
| `NotificationController.php` | `obhs-messaging-notifications` | `obhs-ui` | In-app notifications |
| `ChatbotController.php` | `obhs-ai` | `obhs-security-auth` | AI chatbot conversation endpoint |
| `FileController.php` | `obhs-documents-storage` | `obhs-security-auth` | Secure file access & authorization |
| `AdminBookingController.php` | `obhs-admin` | `obhs-booking-tenancy` | MinSU Boarding Monitoring |
| `AdminLandlordDocumentController.php`| `obhs-admin` | `obhs-documents-storage` | Compliance document verification |
| `AdminAnalyticsController.php` | `obhs-admin` | `obhs-database` | Administrative analytics |
| `AnalyticsController.php` | `obhs-landlord` | `obhs-database` | Landlord analytics |

---

## 3. Services & Domain Logic Ownership

| Service / Directory | Primary Agent | Secondary Agent |
| :--- | :--- | :--- |
| `app/Services/AI/*` | `obhs-ai` | `obhs-security-auth` |
| `app/Services/AcademicCatalogService.php` | `obhs-student` | `obhs-admin` |
| `app/Services/AnalyticsService.php` | `obhs-admin` | `obhs-database` |
| `app/Services/BoardingMonitoringService.php` | `obhs-admin` | `obhs-database` |
| `app/Services/DocumentExpirationService.php` | `obhs-documents-storage`| `obhs-landlord` |
| `app/Services/FileStorageService.php` | `obhs-documents-storage`| `obhs-hostinger` |
| `app/Services/GeocodingService.php` | `obhs-marketplace` | `obhs-landlord` |
| `app/Services/LandlordDocumentStatusService.php`| `obhs-documents-storage`| `obhs-admin` |
| `app/Services/ReportPriorityClassifier.php` | `obhs-admin` | `obhs-ai` |
| `app/Services/RoomOccupancyRules.php` | `obhs-booking-tenancy` | `obhs-landlord` |

---

## 4. Models & Database Ownership

| Model / Table | Primary Agent | Secondary Agent |
| :--- | :--- | :--- |
| `User` / `users` | `obhs-security-auth` | `obhs-student`, `obhs-landlord` |
| `LandlordProfile` / `landlord_profiles` | `obhs-landlord` | `obhs-security-auth` |
| `LandlordDocument` / `landlord_documents`| `obhs-documents-storage`| `obhs-admin` |
| `Property` / `properties` | `obhs-landlord` | `obhs-marketplace` |
| `Room` / `rooms` | `obhs-landlord` | `obhs-booking-tenancy` |
| `RoomImage` / `room_images` | `obhs-landlord` | `obhs-documents-storage` |
| `RoomFeedback` / `room_feedbacks` | `obhs-student` | `obhs-landlord` |
| `Booking` / `bookings` | `obhs-booking-tenancy` | `obhs-payments` |
| `TenantOnboarding` / `tenant_onboardings`| `obhs-booking-tenancy` | `obhs-documents-storage` |
| `TenantPayment` / `tenant_payments` | `obhs-payments` | `obhs-student`, `obhs-landlord` |
| `LeaveRequest` / `leave_requests` | `obhs-student` | `obhs-landlord` |
| `Report` / `reports` | `obhs-student` | `obhs-admin` |
| `Message` / `messages` | `obhs-messaging-notifications` | `obhs-security-auth` |
| `ChatbotConversation`, `ChatbotMessage` | `obhs-ai` | `obhs-security-auth` |
| `database/migrations/*` | `obhs-database` | `obhs-architect` |

---

## 5. Infrastructure & Deployment Ownership

| Component / Script | Primary Agent | Secondary Agent |
| :--- | :--- | :--- |
| `tools/package-hostinger.ps1` | `obhs-hostinger` | `obhs-architect` |
| `.htaccess` & `public/.htaccess` | `obhs-hostinger` | `obhs-security-auth` |
| `app/Console/Commands/DeploymentCheck.php` | `obhs-hostinger` | `obhs-qa` |
| `tests/*` | `obhs-qa` | Domain Owner Agents |
