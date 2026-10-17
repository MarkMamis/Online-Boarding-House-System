# OBHS Landlord Portal: Booking Requests & Tenant Onboarding

**Document ID:** `07_BOOKING_AND_TENANT_ONBOARDING.md`  
**Date:** October 18, 2026  
**Auditor:** Senior UX Architect & Business Process Analyst  
**Focus:** Lifecycle Transitions, Role Responsibility Matrix, and Move-In Pipeline UX.

---

## 1. The Real Authoritative Move-In State Machine

In OBHS, student move-in is governed by two interconnected models: `Booking` and `TenantOnboarding`. A complete trace of the actual controllers reveals this sequential lifecycle:

```
[Student Submits Request]
         │
         ▼
[1. PENDING BOOKING] ──(Landlord Rejects)──► [Booking: Rejected]
         │
         │ (Landlord Approves: BookingController@approve:444-511)
         ▼
[2. BOOKING APPROVED & ONBOARDING INITIATED]
    • Booking status = 'approved'
    • TenantOnboarding record created (status = 'pending')
    • Required documents specified: ['student_id', 'proof_of_income', 'emergency_contact']
    • Deposit amount computed: 1 month rent + optional advance payment
         │
         │ (Student uploads files: TenantOnboardingController@uploadDocuments)
         ▼
[3. DOCUMENTS UPLOADED] (status = 'documents_uploaded')
         │
         │ (Landlord approves docs: TenantOnboardingController@approveDocuments:533)
         ▼
[4. DOCUMENTS APPROVED -> AWAITING CONTRACT SIGNATURES]
         │
         │ (Student signs: TenantOnboardingController@signContractStudent)
         ▼
[5. STUDENT SIGNED] (student_contract_signed = true)
         │
         │ (Landlord signs: TenantOnboardingController@signContractLandlord:503)
         ▼
[6. LEASE FULLY EXECUTED] (landlord_contract_signed = true, status = 'contract_signed')
         │
         │ (Student submits payment proof: TenantOnboardingController@payDeposit)
         ▼
[7. MOVE-IN PAYMENT SUBMITTED] (status = 'deposit_paid', deposit_paid = false)
         │
         │ (Landlord verifies payment: TenantOnboardingController@approveDocuments:554)
         ▼
[8. ONBOARDING COMPLETED & ACTIVE TENANCY ACTIVATED]
    • TenantOnboarding: status = 'completed', deposit_paid = true, digital_id = 'TID-XXXXXXXX'
    • Booking: payment_status = 'paid', payment_date = now(), next_payment_due_date = +1 month
```

---

## 2. Action Responsibility Matrix: Who Does What?

A primary source of user confusion is when a landlord sees a "Pending" badge without knowing whether **they** need to act or the **student** needs to act.

| Stage # | Stage Name | Canonical Status | Party Responsible | Specific Action Required | Primary Button / Interface |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **Stage 1** | New Booking Request | `booking.status = 'pending'` | **LANDLORD** | Review applicant details, stay dates, and choose to Approve or Decline. | `Review Request` (`/landlord/bookings`) |
| **Stage 2** | Awaiting Tenant Docs | `onboarding.status = 'pending'` | **STUDENT** | Student must upload emergency contact, proof of income, and ID. | Landlord sees: *"Waiting for student to upload documents"* |
| **Stage 3** | Review Submitted Docs | `onboarding.status = 'documents_uploaded'` | **LANDLORD** | Inspect uploaded proof and click Approve or Request Changes. | `Review Documents` (`/landlord/onboarding/{id}/review`) |
| **Stage 4** | Student Signature | `student_contract_signed = false` | **STUDENT** | Student reads digital lease and draws/attaches signature. | Landlord sees: *"Waiting for student to sign lease"* |
| **Stage 5** | Landlord Signature | `landlord_contract_signed = false` | **LANDLORD** | Landlord previews agreement and applies authorized digital signature. | `Sign Lease Agreement` (`/landlord/onboarding/{id}/contract`) |
| **Stage 6** | Move-In Payment Due | `onboarding.status = 'contract_signed'` | **STUDENT** | Student transfers security deposit / advance rent and uploads receipt. | Landlord sees: *"Waiting for student to transfer move-in deposit"* |
| **Stage 7** | Confirm Move-In Payment | `onboarding.status = 'deposit_paid'` | **LANDLORD** | Compare payment proof against bank/GCash and confirm receipt. | `Review Payment Proof` (`/landlord/onboarding/{id}/review`) |
| **Stage 8** | Active Boarder | `onboarding.status = 'completed'` | **SYSTEM** | Issue Digital Boarder Pass (`TID-XXXXXXXX`) and schedule monthly rent due dates. | Move to Active Tenants directory (`/landlord/tenants`) |

---

## 3. Critical Usability Defects in the Current Pipeline

### Defect 1: Premature Tenancy Activation
- Currently, when a landlord approves a booking (Stage 1), `booking.status` becomes `'approved'`.
- The landlord's "Tenants" page (`BookingController@landlordTenants`) queries `Booking::where('status', 'approved')`.
- As a result, students at Stage 2 (who have not submitted a single document or paid a peso) immediately appear in the Landlord's "Active Tenants" table!
- **Fix:** Filter `landlordTenants` to only display bookings where `tenantOnboarding.status === 'completed'` (or where onboarding is finished). Applicants in stages 1–7 must remain in **"Move-In Progress"**.

### Defect 2: Fragmented Multi-Page Jumping
- To onboard one student, the landlord currently visits:
  1. `/landlord/bookings` (to approve request)
  2. `/landlord/onboarding` (to see the onboarding queue)
  3. `/landlord/onboarding/{id}/review` (to approve documents)
  4. `/landlord/onboarding/{id}/contract` (to preview and sign lease)
  5. Back to `/landlord/onboarding/{id}/review` (to approve payment)
- That is **5 separate page loads** across 3 different URLs for a single applicant!

---

## 4. Proposed Redesign: Unified Move-In Checklist Card

Instead of scattering reviews across multiple pages, provide one clean **Move-In Hub**:

```
┌────────────────────────────────────────────────────────────────────────┐
│                      MOVE-IN PROGRESS: ROOM 102                        │
│ Student: Juan Dela Cruz (BS Information Technology - Year 2)           │
│ Stay: Nov 01, 2026 – Mar 31, 2027 (5 Months)                           │
├────────────────────────────────────────────────────────────────────────┤
│ CURRENT STATUS: 🟠 Awaiting Landlord Signature                         │
│                                                                        │
│ Step 1: Booking Approved ............................. [ COMPLETED ]  │
│ Step 2: Student Requirements Reviewed ................ [ COMPLETED ]  │
│ Step 3: Student Lease Signature ...................... [ COMPLETED ]  │
│ Step 4: Landlord Lease Signature ..................... [ ACTION REQUIRED ]│
│         -> [ Sign Lease Agreement (1-Click) ]                          │
│ Step 5: Move-in Deposit (₱3,500) ..................... [ WAITING FOR STUDENT ]│
│                                                                        │
│ Next Step: Once you sign, Juan will be prompted to transfer the       │
│ security deposit and move-in advance rent.                             │
└────────────────────────────────────────────────────────────────────────┘
```

This checklist clearly answers:
1. *What has been done?*
2. *What is blocked?*
3. *Who needs to act next?*
4. *What button do I click right now?*
