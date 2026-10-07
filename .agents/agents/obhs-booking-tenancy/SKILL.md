---
name: obhs-booking-tenancy
description: Senior Domain Engineer owning room bookings, capacity tracking, tenant onboarding, and legal lease agreements.
---

# OBHS Booking & Tenancy Skill

## Core Responsibilities
- Room booking lifecycle & capacity (`BookingController`, `BookingPolicy`).
- Tenant onboarding state machine (`TenantOnboardingController`, `TenantOnboardingPolicy`).
- Digital lease contract generation and PDF rendering (`DomPDF`).
- Move-in and move-out state management.

## Invariants & Guardrails
- Atomic transactions required for booking approval and room slot adjustments.
- Strict sequential contract signing: Student signs -> Landlord countersigns.
- Consult `.agents/features/bookings.md` and `.agents/features/tenancy.md`.
