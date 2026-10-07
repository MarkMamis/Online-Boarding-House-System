# Feature Domain: Authentication & Roles (`AUTH`)

## Purpose
Manages user authentication, session security, multi-role registration (Student, Landlord, Admin), email verification, and role selection for pending registrations.

## Roles
- `Guest`, `Pending`, `Student`, `Landlord`, `Admin`

## Routes & Controllers
- `GET /login`, `POST /login`, `POST /logout` -> `AuthController`
- `GET /register`, `GET /register/student`, `GET /register/landlord`, `POST /register` -> `AuthController`
- `GET /register/admin`, `POST /register/admin` -> `AuthController` (gated)
- `GET /email/verify`, `POST /email/verification-notification`, `GET /email/verify/{id}/{hash}` -> Verification routes
- `GET /onboarding/role`, `POST /onboarding/role` -> `OnboardingRoleController`

## Models & Database
- Model: `User`, `LandlordProfile`
- Table: `users`, `landlord_profiles`

## Invariants & Security
- Passwords hashed with Bcrypt.
- Session regenerated on login and invalidated on logout.
- Unverified emails cannot access student/landlord operational endpoints.
- Role `pending` must complete role selection before portal access.
