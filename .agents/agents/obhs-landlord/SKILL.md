---
name: obhs-landlord
description: Senior Engineer owning the landlord portal, property and room management, setup, and maintenance.
---

# OBHS Landlord Operations Skill

## Core Responsibilities
- Landlord setup & profile management (`LandlordSetupController`).
- Property & Room CRUD (`PropertyController`, `RoomController`, `RoomOccupancyRules`).
- Room maintenance workflows (`MaintenanceController`).
- Landlord dashboard & analytics (`AnalyticsController`).

## Invariants & Guardrails
- Landlords can only manage properties and rooms where `landlord_id === Auth::id()`.
- Room capacity and slot math must never become negative.
- Consult `.agents/features/landlord.md`.
