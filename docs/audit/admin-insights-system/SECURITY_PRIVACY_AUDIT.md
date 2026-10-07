# Security & Privacy Audit — Insights and System Modules

## 1. Authentication & Role Boundaries
- **Admin Access Protection:** All `/admin/*` routes are protected by `role:admin` middleware or an explicit `Auth::user()->role === 'admin'` check in the controller. Non-admin users attempting to access these routes are rejected with HTTP 403.
- **Role Escalation Prevention:**
  - Neither student nor landlord update endpoints allow mutating the `role` field.
  - The `adminToggleLandlordStatus` endpoint only toggles the `is_active` boolean on landlords.
  - Registration forms strictly enforce role assignment.

---

## 2. Notification & Incident Report Isolation
- **Notification Data Isolation:** Users cannot query or mutate notifications belonging to another `notifiable_id`.
- **Reports Privacy:** Managed via `ReportPolicy`:
  - `create`: Student must have active booking and completed onboarding.
  - `update`: Admin only.
  - `markResponseRead`: Student must own the report (`$user->id === $report->user_id`).

---

## 3. Sensitive Data Handling
- **PII in User Accounts:**
  - Student medical conditions, blood types, emergency contacts, and parent details are restricted to authorized detail views (`/admin/users/students/{user}`) and not exposed in general list tables.
  - Passwords and tokens are never exposed in responses or API payloads.
