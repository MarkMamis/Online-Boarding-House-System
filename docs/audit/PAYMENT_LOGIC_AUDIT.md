# Payment & Financial Logic Audit

This document reviews financial tracking, billing state transitions, monetary calculation integrity, decimal precision, and authorization across all payment subsystems.

---

## 1. Subsystems Inspected

1. [`StudentPaymentController.php`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/app/Http/Controllers/StudentPaymentController.php): Student monthly rent payment submissions.
2. [`PaymentController.php`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/app/Http/Controllers/PaymentController.php): Landlord payment ledger, monthly record creation, status updates, reminders.
3. [`TenantOnboardingController.php`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/app/Http/Controllers/TenantOnboardingController.php): Initial move-in deposit and advance payment tracking.
4. [`NotifyOverduePayments.php`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/app/Console/Commands/NotifyOverduePayments.php): Daily scheduled command for payment due notifications.
5. Models: [`TenantPayment`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/app/Models/TenantPayment.php), [`Booking`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/app/Models/Booking.php), [`TenantOnboarding`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/app/Models/TenantOnboarding.php).

---

## 2. Financial Integrity & Invariants

### A. Server-Side Amount Derivation (No Client Trust)
- **Evaluation:** Pass.
- **Evidence:**
  In `StudentPaymentController@store`:
  ```php
  $monthlyRentAmount = is_numeric($booking->monthly_rent_amount)
      ? (float) $booking->monthly_rent_amount
      : (float) ($booking->room->price ?? 0);
  ```
  The submitted payment amount is derived directly from the authenticated student's approved booking and associated room pricing, rather than from any form input or client payload.

### B. Duplicate Payment Prevention
- **Evaluation:** Pass.
- **Evidence:**
  In `StudentPaymentController@store`:
  ```php
  $hasSubmittedForDue = TenantPayment::query()
      ->where('booking_id', $booking->id)
      ->whereDate('billing_for_date', $billingDate->toDateString())
      ->whereIn('status', ['submitted', 'approved'])
      ->exists();

  if ($hasSubmittedForDue) {
      return back()->with('error', 'A payment submission already exists for this billing month.');
  }
  ```
  Students cannot flood the landlord with duplicate pending payment submissions for the same billing cycle.

### C. Decimal Precision & Currency Semantics
- **Database Schema:**
  - `tenant_payments.amount_due`: `decimal(10, 2)`
  - `bookings.monthly_rent_amount`: `decimal(10, 2)`
  - `tenant_onboardings.deposit_amount`: `decimal(10, 2)`
  - `tenant_onboardings.advance_amount`: `decimal(10, 2)`
  - `rooms.price`: `decimal(10, 2)`
- **Rule:** Never convert financial amounts to unrounded IEEE 754 floats for equality comparisons or ledger persistence. Always preserve 2-decimal scale.

### D. Ownership & Role Isolation
- **Landlord Isolation:**
  `PaymentController` queries enforce:
  `whereHas('room.property', fn($q) => $q->where('landlord_id', $landlordId))`
  Landlords cannot view, approve, reject, or create records for bookings owned by other landlords.
- **Student Isolation:**
  `StudentPaymentController` enforces `where('student_id', $student->id)` and requires an approved booking with completed onboarding.

---

## 3. Key Findings & Recommendations

### PAY-LOGIC-001: Offline / Cash Payment Workflow Consistency
- **Observation:** When payment method is `cash`, reference number and payment proof are optional. The payment status is marked `submitted` until the landlord explicitly confirms receipt.
- **Recommendation:** Ensure landlords are notified when a cash payment is marked "handed over" so physical confirmation is recorded promptly.

### PAY-LOGIC-002: Overdue Notification Frequency
- **Observation:** `payments:notify-overdue` runs daily at 08:00 via `routes/console.php`. It updates `last_overdue_notified_at` to avoid multiple emails in the same cycle.
- **Status:** Correctly implemented and safe for Hostinger shared cron execution.
