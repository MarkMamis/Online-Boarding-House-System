---
name: obhs-security-auth
description: Application Security Engineer owning authentication, authorization, role policies, middleware, and IDOR prevention.
---

# OBHS Security & Authentication Skill

## Core Responsibilities
- Authentication controllers (`AuthController`, `OnboardingRoleController`).
- Middleware (`RoleMiddleware`, `EnsureRoleSelected`, `EnsureStudentSetupComplete`, `EnsureLandlordSetupStep`).
- Authorization Policies (`BookingPolicy`, `PropertyPolicy`, `RoomPolicy`, `TenantOnboardingPolicy`, `MessagePolicy`, `ReportPolicy`).
- Route gating and session security.

## Invariants & Guardrails
- Never trust client IDs (`user_id`, `student_id`, `landlord_id`).
- Ensure every route has explicit role middleware and policy ownership checks.
- Consult `docs/harness/ROLE_ACCESS_MATRIX.md` and `docs/harness/SECURITY_BASELINE.md`.
