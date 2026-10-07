# Feature Domain: Administration & Oversight (`ADM`)

## Purpose
Provides administrative monitoring of student boarding census for MinSU, document verification (permits, safety certs, student IDs), property inspections, incident report moderation, and institutional analytics.

## Roles
- `Admin`

## Routes & Controllers
- `GET /admin/dashboard`, `GET /admin/dashboard/stats` -> `AuthController`
- `GET /admin/boarding-monitoring/students`, `GET .../print` -> `AdminBookingController`, `BoardingMonitoringService`
- `GET /admin/documents/verification`, `GET .../monitoring`, `POST .../approve`, `POST .../reject` -> `AdminLandlordDocumentController`
- `GET /admin/approvals/students`, `POST .../approve`, `POST .../reject` -> `AuthController`
- `GET /admin/properties`, `GET .../{property}/inspect`, `POST .../approve`, `POST .../reject` -> `PropertyController`
- `GET /admin/analytics` -> `AdminAnalyticsController`, `AnalyticsService`
- `GET /admin/reports`, `GET .../{report}`, `PUT .../{report}` -> `ReportController`

## Models & Database
- Models: `User`, `Property`, `Room`, `Booking`, `LandlordDocument`, `Report`
- Tables: `users`, `properties`, `rooms`, `bookings`, `landlord_documents`, `reports`

## Invariants & Security
- All endpoints must enforce `role:admin`.
- Queries must adhere to `ONLY_FULL_GROUP_BY`.
