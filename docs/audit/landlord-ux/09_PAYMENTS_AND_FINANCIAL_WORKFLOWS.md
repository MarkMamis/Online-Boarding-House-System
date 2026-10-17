# OBHS Landlord Portal: Payments & Financial Workflows (Special Deliverable)

**Document ID:** `09_PAYMENTS_AND_FINANCIAL_WORKFLOWS.md`  
**Date:** October 18, 2026  
**Auditor:** Senior Laravel Architect, Financial Integrity Analyst & QA Lead  
**Priority:** P0 / Highest Priority Architectural Deliverable

---

## 1. Executive Problem Statement: Financial Dualism

The most confusing and error-prone domain in the current Landlord Portal is **Payment Management**. In OBHS, financial operations currently exist across **two completely separate database schemas, two separate controllers, and three independent confirmation screens**:

1. **Move-In / Onboarding Payments:** Managed via `TenantOnboardingController` and stored on the `tenant_onboardings` table (`deposit_amount`, `deposit_paid`, `payment_proof_path`).
2. **Recurring Monthly Rent Payments:** Managed via `PaymentController` and stored on the `tenant_payments` table (`amount_due`, `billing_month`, `status`, `proof_path`).
3. **Booking Due Dates & Status Flags:** Stored directly on the `bookings` table (`monthly_rent_amount`, `payment_status`, `payment_date`, `next_payment_due_date`).

Because these systems evolved separately, landlords are exposed to disjointed UIs, inconsistent status badges, and potential double-counting of rental revenue.

---

## 2. Comprehensive Financial Responsibility Matrix

| Dimension | Move-in / Onboarding Payment | Monthly Recurring Rent Payment |
| :--- | :--- | :--- |
| **Financial Purpose** | Covers move-in prerequisite charges: security deposit (damage bond) and/or 1 month advance rent. | Covers ongoing accommodation for a specific calendar month / billing cycle. |
| **Originating Model** | `TenantOnboarding` (linked to `Booking`) | `TenantPayment` (linked to `Booking`) |
| **Initiated By** | System triggers obligation upon Landlord Booking Approval. | Scheduled monthly due date or Student proactive upload. |
| **Amount Calculation** | Base monthly rent + optional advance payment (`BookingController:506`). | `booking.monthly_rent_amount` or room rate. |
| **Payer & Recipient** | Student pays -> Landlord receives. | Student pays -> Landlord receives. |
| **Proof Submission** | Student uploads receipt in `TenantOnboardingController@payDeposit`. | Student uploads receipt in `StudentPaymentController@store`. |
| **Proof Storage Field** | `tenant_onboardings.payment_proof_path` | `tenant_payments.proof_path` |
| **Verification Route** | `POST /landlord/onboarding/{id}/approve-documents` (`action=approve_payment`) | `POST /landlord/payments/{booking}/records/{record}/status` (`status=approved`) |
| **State When Verified** | `onboarding.deposit_paid = true`, `onboarding.status = 'completed'` | `tenant_payments.status = 'approved'`, `paid_at = now()` |
| **Side Effects on Booking** | Sets `booking.payment_status = 'paid'`, `next_payment_due_date = +1 month`. | Sets `booking.payment_status = 'paid'`, `next_payment_due_date = +1 month`. |

---

## 3. High-Risk Data Integrity Bugs Discovered in Code

### Bug 1: Desynchronization via Manual "Mark as Paid" (`PaymentController:388-422`)
- In `PaymentController@markAsPaid`, a landlord can click a button to mark a tenant's rent as paid.
- **The Code:**
  ```php
  $booking->update([
      'payment_status' => 'paid',
      'payment_date' => now(),
      'next_payment_due_date' => $newDueDate,
  ]);
  ```
- **The Defect:** It updates `bookings.payment_status`, but does **NOT create a record in `tenant_payments`**!
- **Consequence:** In `PaymentController:97-99` and `AnalyticsController:52-62`, revenue calculations query `TenantPayment::where('status', 'approved')`. Because no `TenantPayment` row was written, the recorded revenue is **₱0**, even though the booking displays as "Paid"!

### Bug 2: Advance Rent Double-Counting Risk
- When an onboarding payment includes both deposit + 1st month advance rent, `TenantOnboarding::deposit_amount` stores the combined total (e.g. ₱5,000 for a ₱2,500 room).
- In `PaymentController:75-108`:
  ```php
  $onboardingPaidTotal += $onboardingAmount; // ₱5,000
  $monthlyPaidTotal += $tenantPayment->amount_due; // ₱2,500
  $totalPaid = $onboardingPaidTotal + $monthlyPaidTotal; // ₱7,500!
  ```
- If a tenant's advance rent is logged as both an onboarding deposit and later recorded as a monthly payment for Month 1, the system reports **double income**.
- **Rule Required:** Clarify ledger credit: Advance rent collected during onboarding must be automatically credited against the Month 1 billing cycle, with zero duplicate cash logging.

### Bug 3: Conflicting Statuses Across Pages
- In `PaymentController`, effective payment status is computed by `Booking::derivedPaymentStatus()`.
- On the dashboard, an overdue alert shows 3 overdue payments.
- But inside `landlord.payments.index`, filtering by `status=overdue` uses `derivedPaymentStatus()`, which evaluates whether `next_payment_due_date < now()`. If a tenant has uploaded a payment proof awaiting verification, `derivedPaymentStatus()` may still flag them as "overdue", alarming both tenant and landlord!

---

## 4. The Solution: Unified Payments Center Architecture

Landlords should not have to visit Onboarding to review move-in payments and visit Payments to review monthly rent. We propose consolidating all financial management into a single **Payments Center** (`/landlord/payments`):

### Recommended Layout & Sub-Navigation:
```
┌────────────────────────────────────────────────────────────────────────┐
│                        PAYMENTS & FINANCIAL CENTER                     │
├────────────────────────────────────────────────────────────────────────┤
│ FINANCIAL OVERVIEW (Monitoring Cards)                                  │
│ [ ₱32,500 Collected (This Month) ] [ ₱5,000 Pending Proof Review ]    │
│ [ ₱2,500 Overdue (1 Tenant) ]     [ ₱40,000 Total Expected ]          │
├────────────────────────────────────────────────────────────────────────┤
│ TABS:                                                                  │
│  [ Rent Confirmations (2) ]  [ Move-in Payments (1) ]  [ History / Logs]│
├────────────────────────────────────────────────────────────────────────┤
│ TAB 1: RENT CONFIRMATIONS (Management Action Queue)                    │
│ List of submitted monthly payment proofs waiting for verification:    │
│ • Juan Dela Cruz (Room 101) - ₱2,500 (Oct 2026 Rent)                   │
│   Proof: GCash Reference #902184918291 [View Receipt]                 │
│   [ Confirm Payment (1-Click) ]  [ Reject Proof ]                      │
├────────────────────────────────────────────────────────────────────────┤
│ TAB 2: MOVE-IN ONBOARDING DUES (Management Action Queue)               │
│ List of new move-in deposits waiting for verification:                 │
│ • Maria Santos (Room 204) - ₱5,000 (1 Mo Advance + 1 Mo Deposit)      │
│   Proof: Bank Deposit Slip [View Slip]                                 │
│   [ Confirm Move-in Payment ]    [ Reject Proof ]                      │
├────────────────────────────────────────────────────────────────────────┤
│ TAB 3: TENANT BILLING STATUS & REMINDERS                               │
│ Comprehensive roster of all active tenants, current billing status:    │
│ • Pedro Penduko (Room 103) - Due Oct 25, 2026 - [Paid]                │
│ • Ana Cruz (Room 105) - Due Oct 10, 2026 - [Overdue: 8 Days]          │
│   [ Send Reminder with GCash QR ]                                      │
└────────────────────────────────────────────────────────────────────────┘
```

---

## 5. Architectural Safeguards for Implementation

1. **Automatic Ledger Synchronization:**
   - Any manual "Mark as Paid" action must atomically write an approved `tenant_payments` row with `payment_method = 'cash'` or `'manual'` to guarantee revenue reports match reality.
2. **Single Canonical Verification Modal:**
   - Reusable Blade component: `<x-payment-proof-modal :payment="$payment" />` used across both move-in and monthly rent screens.
3. **Immutable History:**
   - Every confirmed or rejected payment logs timestamp, reviewer ID, reference number, and previous balance.
