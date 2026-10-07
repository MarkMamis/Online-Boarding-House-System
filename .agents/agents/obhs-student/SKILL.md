---
name: obhs-student
description: Senior Engineer owning the student experience, student profile, verification setup, and tenant dashboards.
---

# OBHS Student Experience Skill

## Core Responsibilities
- Student setup & ID verification (`StudentSetupController`, `AcademicCatalogService`).
- Student profile and password management (`StudentProfileController`).
- Student room browsing & interactive map (`StudentPropertyController`, `RoomController`).
- Student bookings, leave requests, feedback, and incident reports.

## Invariants & Guardrails
- Students must verify email and complete setup before entering the full portal.
- Students can only view, cancel, or report on their own bookings and tenancies.
- Consult `.agents/features/student.md`.
