# Financial Calculation Contract

## 1. Metrics & Data Sources

| Metric | Source Model / Attribute | Transaction Classification | Accounting Scope |
| :--- | :--- | :--- | :--- |
| **Confirmed Monthly Collections** | `TenantPayment` where `status = 'approved'` (or `effective_payment_status = 'paid'`) | Regular Rental Revenue | Recurring monthly rent cycles |
| **Pending Monthly Verification** | `TenantPayment` where `status = 'submitted'` | Unconfirmed Evidence | Submitted tenant proofs awaiting review |
| **Confirmed Move-in Dues** | `TenantOnboarding` where `deposit_paid = true` or `status = 'completed'` | Initial Deposit & Advance Rent | One-time onboarding obligation |
| **Pending Move-in Verification** | `TenantOnboarding` where `status = 'deposit_paid'` & `!deposit_paid` | Unconfirmed Evidence | Onboarding proof uploaded by tenant |

## 2. Invariants & Protections
- **No Double Counting:** Unconfirmed payment proofs (`status = 'submitted'`) are strictly excluded from confirmed collections totals (`monthlyPaidTotal` and `onboardingPaidTotal`).
- **Clear Distinction Between Deposit and Monthly Rent:** Move-in deposits and recurring rent remain in separate views and separate ledger lines.
- **Idempotency in Payment Confirmation:**
  - `PaymentController@markAsPaid` verifies onboarding completion before marking monthly rent.
  - Automatically checks if an existing `TenantPayment` for the billing period exists. If found, it updates the record; if none exists, it creates one with `payment_method = 'cash'`, avoiding duplicate accounting rows.
- **Authorization Scoping:** All queries are filtered through `whereHas('room.property', fn($q) => $q->where('landlord_id', Auth::id()))`. No landlord can access or view another landlord's transactions.
