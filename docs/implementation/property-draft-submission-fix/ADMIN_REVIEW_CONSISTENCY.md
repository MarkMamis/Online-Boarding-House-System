# Admin Review Consistency & Queue Segregation

## 1. Problem Resolved
In the Admin Property Reviews interface (`/admin/property-reviews`):
1. Draft properties appeared under review tabs and were labeled "Submitted for review".
2. Draft records displayed `created_at` timestamps disguised as submission timestamps.
3. Total submissions metrics included draft records.
4. Admins had enabled Approve and Reject modals for unsubmitted draft records.

---

## 2. Implemented Fixes

### A. Queue Filtering & Segregation
- **Pending Review Queue (`status=pending`):**
  - Strictly isolates properties where `approval_status = 'pending'`.
  - Excludes all `draft`, `approved`, and `rejected` records.
- **Dedicated Drafts Tab (`status=draft`):**
  - Allows administrators to monitor unsubmitted draft setups across the system without cluttering the actionable review queue.
- **All Submissions View (`status=all`):**
  - Strictly filters `whereIn('approval_status', ['pending', 'approved', 'rejected'])`.
  - Excludes draft properties from all submission counts and aggregates.

### B. Accurate Date Display
- Schema now includes `submitted_at` timestamp column.
- Table headers dynamically toggle between:
  - `"Submitted"` (when viewing pending, approved, or all submissions).
  - `"Last Saved / Created"` (when viewing draft listings).
- Subtitle indicators:
  - For submissions: `"Submitted for review"`.
  - For drafts: `"Not yet submitted"`.

### C. Backend & Frontend Decision Guardrails
- **Backend Authorization (`PropertyController::adminApprove` & `adminReject`):**
  - Added strict check:
    ```php
    if ($property->approval_status === 'draft') {
        if (request()->wantsJson() || request()->ajax()) {
            return response()->json(['message' => 'Draft properties cannot be approved.'], 422);
        }
        return back()->with('error', 'Cannot approve a draft property until it is submitted for review.');
    }
    ```
  - Rejects any direct HTTP/POST requests targeting draft properties with HTTP 422 / redirect error.
- **Blade View Template (`resources/views/admin/properties/pending.blade.php`):**
  - Strips Approve and Reject buttons for drafts.
  - Renders a read-only badge:
    `<span class="badge bg-secondary-subtle text-secondary border">Draft Only</span>`.
  - Suppresses Approve and Reject modal dialogs for draft rows.

### D. Metric Cards
- Metric card labeled `"Total Submissions"` accurately queries `Property::whereIn('approval_status', ['pending', 'approved', 'rejected'])->count()`.
- Draft records are counted separately under the `"Drafts"` workspace tab counter.
