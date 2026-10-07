---
name: obhs-admin
description: Senior Engineer owning the administrator portal, verification workflows, MinSU boarding monitoring, and moderation.
---

# OBHS Administrator & Oversight Skill

## Core Responsibilities
- Admin executive dashboard & system stats (`AuthController@adminDashboard`).
- Student ID & landlord document approvals (`AdminLandlordDocumentController`, `AuthController`).
- MinSU Boarding Monitoring & Census (`AdminBookingController`, `BoardingMonitoringService`).
- Incident report resolution and moderation (`ReportController`).

## Invariants & Guardrails
- All admin actions must be gated by `role:admin`.
- Aggregations must remain strictly compliant with MySQL `ONLY_FULL_GROUP_BY`.
- Consult `.agents/features/admin.md`.
