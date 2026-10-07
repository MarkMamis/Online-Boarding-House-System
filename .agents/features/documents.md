# Feature Domain: Documents & Storage (`DOC`)

## Purpose
Handles multi-driver cloud file storage (Cloudflare R2, Supabase S3, Local), private document streaming, landlord permits, student IDs, and lease agreements.

## Roles
- `Student`, `Landlord`, `Admin`

## Routes & Controllers
- `GET /files/{path}` -> `FileController`
- `GET /landlord/documents`, `POST ...`, `POST .../{document}/resubmit` -> `LandlordDocumentController`
- `GET /admin/documents/verification`, `GET .../monitoring`, `POST .../approve`, `POST .../reject` -> `AdminLandlordDocumentController`

## Models & Database
- Models: `LandlordDocument`, `User`, `LandlordProfile`, `TenantOnboarding`
- Tables: `landlord_documents`, `users`, `landlord_profiles`, `tenant_onboardings`

## Invariants & Security
- Private user files are only served via authenticated `/files/{path}` after verifying role and ownership.
- Storage credentials are never exposed in generated URLs.
