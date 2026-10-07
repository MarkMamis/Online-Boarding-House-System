# Feature Domain: Administration & Oversight (`ADM`)

## Purpose
Provides administrative monitoring of student boarding census for MinSU, document verification (permits, safety certs, student IDs), property reviews, landlord accreditation, incident report moderation, and institutional analytics.

## Information Architecture
- **Overview:** Dashboard (`/admin/dashboard`)
- **Monitoring (Observational):**
  - Boarding Houses (`/admin/properties`)
  - Active Boarders (`/admin/boarding-monitoring/students`)
  - Onboarding Pipeline (`/admin/onboardings`)
  - Compliance Monitor (`/admin/documents/monitoring`)
- **Management (Decision Inboxes):**
  - Property Reviews (`/admin/property-reviews`)
  - Student Verification (`/admin/approvals/students`)
  - Landlord Accreditation (`/admin/approvals/landlords`)
  - Document Review Queue (`/admin/documents/verification`)
- **Insights:** Reports (`/admin/reports`), Analytics (`/admin/analytics`)
- **System:** User Accounts (`/admin/users`), Notifications (`/notifications`)

## Roles
- `Admin`

## Routes & Controllers
- `GET /admin/dashboard`, `GET /admin/dashboard/stats` -> `AuthController`
- `GET /admin/properties` (Monitoring), `GET /admin/property-reviews` (Management Review Queue), `GET .../{property}/inspect`, `POST .../approve`, `POST .../reject` -> `PropertyController`
- `GET /admin/boarding-monitoring/students`, `GET .../print` -> `AdminBookingController`, `BoardingMonitoringService`
- `GET /admin/onboardings`, `GET .../{onboarding}` -> `AdminOnboardingController`
- `GET /admin/documents/monitoring` (Compliance Monitor), `GET /admin/documents/verification` (Document Review Queue), `POST .../approve`, `POST .../reject` -> `AdminLandlordDocumentController`
- `GET /admin/approvals/students`, `POST .../approve`, `POST .../reject` -> `AuthController`
- `GET /admin/approvals/landlords`, `POST .../approve`, `POST .../reject` -> `AuthController`
- `GET /admin/analytics` -> `AdminAnalyticsController`, `AnalyticsService`
- `GET /admin/reports`, `GET .../{report}`, `PUT .../{report}` -> `ReportController`

## Models & Database
- Models: `User`, `Property`, `Room`, `Booking`, `LandlordDocument`, `Report`, `StudentProfile`, `LandlordProfile`
- Tables: `users`, `properties`, `rooms`, `bookings`, `landlord_documents`, `reports`, `student_profiles`, `landlord_profiles`

## Invariants & Security
- All endpoints must enforce `role:admin`.
- Queries must adhere to `ONLY_FULL_GROUP_BY`.
- Management endpoints perform state transitions with mandatory validation and decision logs.

