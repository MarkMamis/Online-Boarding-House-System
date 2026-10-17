# Payments Architecture & Ledger Structure

**Document Reference:** `docs/implementation/landlord-ux-transformation/PAYMENTS_ARCHITECTURE.md`  
**Modified Files:** `app/Http/Controllers/PaymentController.php`, `resources/views/landlord/payments/index.blade.php`

---

## 1. Context & Two-Tier Payment Model

The OBHS payment model spans two distinct financial phases:
1. **Move-in Dues (Onboarding Phase):** Managed via `tenant_onboardings` (`deposit_amount`, `deposit_paid`, `status`). Represents move-in deposits and initial downpayments.
2. **Monthly Rent & Utilities (Living Phase):** Managed via `tenant_payments` (`billing_for_date`, `due_date`, `amount_due`, `payment_method`, `payment_reference`, `proof_path`, `status`). Represents recurring monthly rent cycles.

---

## 2. Implemented Solutions

### 2.1 Cash Ledger Synchronization in `markAsPaid`

Previously, when a tenant paid monthly rent directly in cash, the landlord clicked "Mark as Paid". This action updated `bookings.payment_status = 'paid'`, but if no prior `TenantPayment` existed for the cycle, it exited with an error (`"No monthly payment record for this cycle yet"`).

**Fix in `PaymentController@markAsPaid`:**
When marking a booking cycle as paid without an existing `TenantPayment` entry:
```php
$monthlyRecord = TenantPayment::create([
    'booking_id' => $booking->id,
    'student_id' => $booking->student_id,
    'billing_for_date' => $cycleStart->toDateString(),
    'due_date' => $effectiveDueDate->toDateString(),
    'amount_due' => $billingAmount,
    'payment_method' => 'cash',
    'status' => 'approved',
    'submitted_at' => now(),
    'reviewed_at' => now(),
    'reviewed_by' => Auth::id(),
    'notes' => 'Confirmed in person by landlord (Cash payment).',
]);
```
This ensures:
- Financial analytics queries aggregating approved `TenantPayment` records accurately include cash payments.
- Zero phantom numbers or ledger desynchronization.
- Landlords have complete audit trails for physical cash collections.

### 2.2 Clear Summary KPI Cards

Replaced complex shared-ledger equations with 3 transparent, understandable totals:
- **Total Collections:** Verified revenue received (`onboardingPaidTotal + monthlyPaidTotal`).
- **Pending Verification:** Outstanding submissions or uncollected dues (`onboardingPendingTotal + monthlyPendingTotal`).
- **Total Expected:** Sum of total collections and pending dues for active contracts.
