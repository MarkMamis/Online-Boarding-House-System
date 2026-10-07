# Feature Domain: Payments & Financial Tracking (`PAY`)

## Purpose
Tracks student monthly rent payments, landlord payment receipts, deposit verification, overdue payment notifications, and billing status transitions.

## Roles
- `Student`, `Landlord`, `Admin`

## Routes & Controllers
- `GET /student/payments`, `POST /student/payments` -> `StudentPaymentController`
- `GET /landlord/payments`, `GET .../{booking}/manage`, `POST .../paid`, `POST .../records` -> `PaymentController`
- `Schedule: payments:notify-overdue` -> `NotifyOverduePayments`

## Models & Database
- Models: `TenantPayment`, `Booking`, `TenantOnboarding`, `LandlordProfile`
- Tables: `tenant_payments`, `bookings`, `tenant_onboardings`, `landlord_profiles`

## Invariants & Security
- Monthly rent amounts calculated server-side from approved booking records.
- Duplicate submissions for the same billing cycle are blocked.
- Exact `decimal(10,2)` monetary precision preserved.
