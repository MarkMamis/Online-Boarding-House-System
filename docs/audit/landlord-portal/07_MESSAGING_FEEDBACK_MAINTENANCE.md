# Landlord Portal — Messaging, Feedback & Maintenance Audit

**Modules Audited:** Messages, Feedback & Sentiment, Room Maintenance  
**Controllers:** `MessageController`, `LandlordFeedbackController`, `RoomFeedbackController`, `MaintenanceController`  
**Models:** `Message`, `RoomFeedback`, `Room`  
**Date:** 2026-10-08  

---

## 1. Direct Messaging System Audit (`MessageController`)

### A. Authorization & Recipient Gating
- **Role Isolation:**
  - Students can only initiate conversations with landlords of properties they have booked or inquired about (`MessageController.php:71–88, 227–256`).
  - Landlords can reply to prospective inquirers, current tenants, or former tenants (`lines 133–185`).
  - Self-messaging is blocked (`line 280`).
- **Participant Categorization:**
  - The landlord messages view automatically tags contacts as:
    - **Current Tenant** (active approved stay)
    - **Prospective Tenant** (pending booking request)
    - **Former Tenant** (past stay)
    - **Direct Inquiry** (pre-booking general inquiry)

### B. Technical & Infrastructure Findings:
1. **Hostinger Shared Hosting Compatibility:**
   - Messaging uses standard database persistence (`messages` table) without requiring WebSockets, Pusher, or persistent Node daemons.
   - Message threads are ordered by `created_at desc` with a 100-200 record query limit.
2. **Missing Real-Time Polling/Indicator:**
   - Messages do not auto-refresh without page reload; however, notification badges update on layout reload.

---

## 2. Feedback & AI Sentiment Analysis Audit (`LandlordFeedbackController`)

### A. Integrity Rules for Student Reviews (`RoomFeedbackController`):
1. **Verified Tenant Gating:** Only students with completed onboarding (`tenant_onboardings.status = completed`) on an active or past approved booking for that room can submit reviews (`RoomFeedbackController.php:24–35`).
2. **One Review per Room:** Students can only submit 1 review per room (`line 38`).
3. **Anonymity Option:** Students can check "Post Anonymously" (`line 101`), which nullifies `display_name` while keeping the review authenticated internally.
4. **No Landlord Tampering:** Landlords have **no edit or delete permissions** over student reviews. Reviews are immutable.

### B. Hugging Face AI Sentiment Integration:
- Uses Hugging Face Multilingual Sentiment model (`tabularisai/multilingual-sentiment-analysis`).
- Classifies feedback text as `positive`, `negative`, or `neutral` with confidence score.
- Includes a batch analyzer endpoint (`/landlord/feedback/analyze`) for unanalyzed legacy comments (`LandlordFeedbackController.php:30–64`).

---

## 3. Maintenance Module Audit (`MaintenanceController`)

### A. Current Implementation:
- **Room Status Toggle Only:**
  - Landlord visits `/landlord/maintenance`.
  - Selects a room and clicks "Set Maintenance Mode" with an optional reason.
  - `RoomController` updates `status = 'maintenance'` and clears `slots_available = 0`.
  - Landlord clicks "Complete Maintenance" -> sets `status = 'available'`.

### B. Workflow Gap & Inconsistency:
1. **No Tenant-Facing Maintenance Request Flow:**
   - Tenants have no UI to submit routine repair tickets (e.g. plumbing leak, light bulb replacement).
   - Tenants currently use either Direct Messages or the Admin Incident Report system.
2. **Missing Snapshot Sync on Completion:**
   - `MaintenanceController@completeMaintenance` (lines 74–93) sets `status = 'available'`, but **does not invoke `$room->syncAvailabilitySnapshot()`**.
   - If a room had existing approved occupants before being placed on maintenance, its availability slots are not dynamically re-evaluated on release.

---

## 4. Summary of Findings

| Finding ID | Severity | Module | Description | Recommended Action |
| :--- | :--- | :--- | :--- | :--- |
| **AUD-MSG-01** | **P3** | Messages | No asynchronous polling for new messages in active thread. | Add lightweight 15-second AJAX polling on open message threads. |
| **AUD-FDB-01** | **P3** | Feedback | Landlords cannot post a formal public response to reviews. | Add an optional "Landlord Response" field to feedback threads. |
| **AUD-MNT-01** | **P2** | Maintenance | Completing maintenance does not trigger `syncAvailabilitySnapshot()`. | Call `$room->syncAvailabilitySnapshot()` in `MaintenanceController@completeMaintenance`. |
| **AUD-MNT-02** | **P2** | Maintenance | Maintenance is disconnected from tenant operations. | Introduce a lightweight tenant repair ticket submission and tracking tab. |
