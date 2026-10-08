# Landlord Portal — End-to-End Workflow Map

**System:** Online Boarding House System (OBHS)  
**Lifecycle Flow:** Landlord Registration → Property Setup → Booking → Onboarding → Active Tenancy → Payments → Departure  
**Date:** 2026-10-08  

---

## 1. Visual Workflow Map

```mermaid
graph TD
    %% 1. Registration & Compliance
    subgraph Step 1: Registration & Compliance
        Reg[1. Landlord Registration /register/landlord] --> Setup[2. Complete Setup Wizard /landlord/setup]
        Setup --> UploadBP[3. Upload Business Permit & Safety Cert]
        UploadBP --> AdminAccred[4. Admin Reviews & Approves Accreditation]
    end

    %% 2. Property & Room Setup
    subgraph Step 2: Property & Inventory Setup
        AdminAccred --> AddProp[5. Add Property /landlord/properties/create]
        AddProp --> Geocode[Auto-Geocoding + Photo Upload]
        Geocode --> AdminPropRev[6. Admin Reviews Property]
        AdminPropRev --> AddRooms[7. Setup Rooms with Pricing Models /landlord/rooms]
        AddRooms --> PubMarket[8. Published in Student Marketplace]
    end

    %% 3. Booking & Onboarding
    subgraph Step 3: Booking & Onboarding
        PubMarket --> StudentReq[9. Student Requests Room /bookings/store]
        StudentReq --> LandlordDecision{10. Landlord Decision}
        LandlordDecision -- Reject --> ReqRejected[Booking Rejected]
        LandlordDecision -- Approve --> OnboardingInit[11. Onboarding Created]
        OnboardingInit --> DocUpload[12. Student Uploads Documents]
        DocUpload --> ContractSign[13. Student & Landlord Sign Lease Agreement]
        ContractSign --> PayDeposit[14. Student Pays Deposit / Advance]
        PayDeposit --> ConfirmDeposit[15. Landlord Approves Payment -> Onboarding Completed]
    end

    %% 4. Active Tenancy & Operations
    subgraph Step 4: Active Tenancy & Financials
        ConfirmDeposit --> ActiveStay[16. Active Tenancy Enabled]
        ActiveStay --> MonthlyBilling[17. Monthly Rent Due Tracking]
        MonthlyBilling --> StudentPay[18. Student Submits Monthly Payment]
        StudentPay --> ReviewPay[19. Landlord Approves / Reconciles Record]
        ActiveStay --> MsgComm[20. Direct In-App Messaging]
        ActiveStay --> Feedb[21. Verified Room Review & Sentiment]
    end

    %% 5. Move-Out & Departure
    subgraph Step 5: Move-Out & Historical Record
        ActiveStay --> LeaveReq[22. Student Files Leave Request]
        LeaveReq --> LandlordLeave[23. Landlord Approves Leave Request]
        LandlordLeave --> MoveOutFinal[24. Move-Out Date Reached: Booking Closed]
        MoveOutFinal --> History[25. Archived to Historical Boarders]
    end
```

---

## 2. Transition State Specifications

| Step # | Transition Name | Triggering Actor | Preconditions | Database Updates | Capacity / Operational Effect |
| :---: | :--- | :--- | :--- | :--- | :--- |
| **1–4** | **Landlord Accreditation** | Landlord / Admin | Email verified, profile completed, valid permits uploaded. | `users`, `landlord_profiles`, `landlord_documents` (`verification_status = approved`). | Unlocks full management operations in UI. |
| **5–8** | **Property & Room Publishing** | Landlord / Admin | Property approved by Admin; at least 1 available room with pricing model. | `properties.approval_status = approved`, `rooms.status = available`, `slots_available > 0`. | Appears on student map and search results. |
| **9–10** | **Booking Approval** | Landlord | Student academic verification approved; room has available slots. | `bookings.status = approved`, `occupancy_mode` set, overlapping pending requests auto-rejected if full. | Room available slots decrement via `RoomOccupancyRules`. |
| **11–15** | **Tenant Onboarding Completion** | Student / Landlord | Documents uploaded, lease agreement e-signed by both parties, deposit paid & approved. | `tenant_onboardings.status = completed`, `deposit_paid = true`, `bookings.payment_status = paid`. | Student tenant dashboard unlocked; digital ID issued. |
| **16–19** | **Monthly Rent Cycle** | Student / Landlord | Active tenancy; monthly due date reached. | `tenant_payments.status = approved`, `bookings.next_payment_due_date` advanced by 1 month. | Ledger balance updated; student receives receipt notification. |
| **22–25** | **Move-Out & Departure** | Student / Landlord | Active booking exists; leave date specified. | `leave_requests.status = approved`, `bookings.check_out = leave_date`. | Room slots restored for future booking dates once move-out finalized. |
