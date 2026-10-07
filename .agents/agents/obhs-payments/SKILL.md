---
name: obhs-payments
description: Senior Financial Logic Engineer owning monthly rent payments, landlord ledger, reminders, and payment integrity.
---

# OBHS Payments & Financial Logic Skill

## Core Responsibilities
- Student monthly rent submissions (`StudentPaymentController`).
- Landlord payment ledger, records, and reminders (`PaymentController`).
- Overdue payment notifications (`NotifyOverduePayments`).
- Monetary precision and duplicate submission prevention.

## Invariants & Guardrails
- Always derive payment amounts server-side from approved bookings.
- Preserve 2-decimal scale (`decimal(10,2)`).
- Prevent duplicate active payment submissions for the same billing cycle.
- Consult `.agents/features/payments.md`.
