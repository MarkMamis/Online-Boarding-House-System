# Route Compatibility & Backward-Compatibility Verification

**Repository:** Online Boarding House System (OBHS)  
**Date:** 2026-10-08  

---

## 1. Preserved Production Routes

All existing production routes have been preserved with identical URI paths, HTTP methods, route names, and controller actions:

| Route Name | Method | URI | Controller Action | Backward-Compatible |
| :--- | :--- | :--- | :--- | :---: |
| `landlord.dashboard` | GET | `/landlord/dashboard` | `AuthController@landlordDashboard` | YES |
| `landlord.properties.index` | GET | `/landlord/properties` | `PropertyController@index` | YES |
| `landlord.properties.create` | GET | `/landlord/properties/create` | `PropertyController@create` | YES |
| `landlord.properties.store` | POST | `/landlord/properties` | `PropertyController@store` | YES |
| `landlord.properties.show` | GET | `/landlord/properties/{property}` | `PropertyController@show` | YES |
| `landlord.properties.edit` | GET | `/landlord/properties/{property}/edit` | `PropertyController@edit` | YES |
| `landlord.properties.update` | PUT | `/landlord/properties/{property}` | `PropertyController@update` | YES |
| `landlord.properties.destroy` | DELETE | `/landlord/properties/{property}` | `PropertyController@destroy` | YES |
| `landlord.properties.rooms.index` | GET | `/landlord/properties/{property}/rooms` | `RoomController@index` | YES |
| `landlord.properties.rooms.create` | GET | `/landlord/properties/{property}/rooms/create` | `RoomController@create` | YES |
| `landlord.properties.rooms.store` | POST | `/landlord/properties/{property}/rooms` | `RoomController@store` | YES |
| `landlord.properties.rooms.edit` | GET | `/landlord/properties/{property}/rooms/{room}/edit` | `RoomController@edit` | YES |
| `landlord.properties.rooms.update` | PUT | `/landlord/properties/{property}/rooms/{room}` | `RoomController@update` | YES |
| `landlord.properties.rooms.destroy` | DELETE | `/landlord/properties/{property}/rooms/{room}` | `RoomController@destroy` | YES |
| `landlord.rooms.index` | GET | `/landlord/rooms` | `RoomController@landlordIndex` | YES |
| `landlord.bookings.index` | GET | `/landlord/bookings` | `BookingController@landlordIndex` | YES |
| `landlord.bookings.approve` | POST | `/landlord/bookings/{booking}/approve` | `BookingController@approve` | YES |
| `landlord.bookings.reject` | POST | `/landlord/bookings/{booking}/reject` | `BookingController@reject` | YES |
| `landlord.tenants.index` | GET | `/landlord/tenants` | `BookingController@landlordTenants` | YES |
| `landlord.onboarding.index` | GET | `/landlord/onboarding` | `TenantOnboardingController@landlordIndex` | YES |
| `landlord.onboarding.review` | GET | `/landlord/onboarding/{onboarding}/review` | `TenantOnboardingController@reviewDocuments` | YES |
| `landlord.onboarding.contract` | GET | `/landlord/onboarding/{onboarding}/contract` | `TenantOnboardingController@viewContract` | YES |
| `landlord.onboarding.sign_contract` | POST | `/landlord/onboarding/{onboarding}/sign-contract` | `TenantOnboardingController@signContractAsLandlord` | YES |
| `landlord.onboarding.approve_documents` | POST | `/landlord/onboarding/{onboarding}/approve-documents` | `TenantOnboardingController@approveDocuments` | YES |
| `landlord.payments.index` | GET | `/landlord/payments` | `PaymentController@index` | YES |
| `landlord.payments.manage` | GET | `/landlord/payments/{booking}/manage` | `PaymentController@manageMonthly` | YES |
| `landlord.leave_requests.index` | GET | `/landlord/leave-requests` | `LandlordLeaveRequestController@index` | YES |
| `landlord.leave_requests.approve` | POST | `/landlord/leave-requests/{leaveRequest}/approve` | `LandlordLeaveRequestController@approve` | YES |
| `landlord.leave_requests.reject` | POST | `/landlord/leave-requests/{leaveRequest}/reject` | `LandlordLeaveRequestController@reject` | YES |
| `landlord.maintenance.index` | GET | `/landlord/maintenance` | `MaintenanceController@index` | YES |
| `landlord.maintenance.set` | POST | `/landlord/maintenance/set` | `MaintenanceController@setMaintenance` | YES |
| `landlord.documents.index` | GET | `/landlord/documents` | `LandlordDocumentController@index` | YES |
| `landlord.documents.store` | POST | `/landlord/documents` | `LandlordDocumentController@store` | YES |
| `landlord.messages.index` | GET | `/landlord/messages` | `MessageController@index` | YES |
| `landlord.feedback.index` | GET | `/landlord/feedback` | `LandlordFeedbackController@index` | YES |
| `landlord.analytics.index` | GET | `/landlord/analytics` | `AnalyticsController@index` | YES |
| `landlord.profile.edit` | GET | `/landlord/profile` | `AuthController@landlordProfile` | YES |
| `notifications.index` | GET | `/notifications` | `NotificationController@index` | YES |

---

## 2. New Dedicated Monitoring Routes

| Route Name | Method | URI | Controller Action | Purpose |
| :--- | :--- | :--- | :--- | :--- |
| `landlord.monitoring.properties` | GET | `/landlord/monitoring/properties` | `LandlordMonitoringController@properties` | Read-only Property & Room Status overview |
| `landlord.monitoring.compliance` | GET | `/landlord/monitoring/compliance` | `LandlordMonitoringController@compliance` | Read-only Compliance & Accreditation Status |
