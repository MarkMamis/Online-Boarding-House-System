# Feature Domain: Tenancy & Onboarding Workflow (`TEN`)

## Purpose
Governs tenant onboarding after booking approval, student document submission, landlord document review, digital lease contract execution, PDF generation, and deposit verification.

## Roles
- `Student`, `Landlord`, `Admin`

## Routes & Controllers
- `GET /student/onboarding/{onboarding}`, `POST .../documents`, `POST .../sign-contract`, `POST .../pay-deposit` -> `TenantOnboardingController`
- `GET /landlord/onboarding/{onboarding}/review`, `POST .../approve-documents`, `POST .../sign-contract` -> `TenantOnboardingController`
- `GET /admin/onboardings`, `GET .../{onboarding}` -> `TenantOnboardingController`
- Contract PDFs: `GET .../contract-pdf` -> `TenantOnboardingController` (DomPDF)

## Models & Database
- Models: `TenantOnboarding`, `Booking`, `Room`, `Property`, `User`
- Tables: `tenant_onboardings`, `bookings`, `rooms`, `properties`, `users`

## Invariants & Security
- `TenantOnboardingPolicy` strictly protects student documents and contracts from unauthorized cross-user access.
- Linear state machine enforced: Upload -> Landlord Review -> Student Sign -> Landlord Sign -> Deposit -> Complete.
