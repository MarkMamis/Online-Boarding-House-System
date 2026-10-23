# Root Cause Analysis: Property Draft Submission Workflow & Admin Inconsistency

## 1. Problem Statement
In production on Hostinger Shared Hosting, properties such as Property ID 2 ("Lolita Valenzuela Boarding House") were observed in `draft` status despite the landlord having approved compliance documents and configured rooms. The landlord was unable to submit the property for administrative approval because neither the Property Details workspace (`/landlord/properties/{property}`) nor the Edit Property workflow (`/landlord/properties/{property}/edit`) provided a visible "Review & Submit" call to action.

Simultaneously, the Admin Property Reviews page (`/admin/property-reviews`):
- Displayed unsubmitted draft records with the subtitle "Submitted for review".
- Displayed `created_at` timestamps labeled as "Submitted".
- Counted draft records in the "Total Submissions" metric card (e.g. 3 drafts + 1 approved = 4 submissions).
- Rendered active "Approve" and "Reject" action buttons and modals for unsubmitted draft records.

---

## 2. Investigated Root Causes

### Root Cause 1: Missing Submission Endpoint and CTA in Edit/Details Workflow
- **Initial Implementation Flaw:** The guided property creation flow only offered a final "Submit for Review" button on the initial `create` form. Once a property record was created or autosaved as `draft`, reopening it in `/landlord/properties/{property}/edit` swapped the button text to a generic "Save Changes" (`<button type="submit" name="action" value="save">Save Changes</button>`).
- **No Transition Route:** The update route (`PUT /landlord/properties/{property}`) exclusively saved fields without checking for an explicit submission action or transitioning `approval_status` from `draft` to `pending`.
- **Details UI Void:** On `/landlord/properties/{property}`, there was no contextual alert banner or CTA for `draft` properties informing landlords that the listing had not been submitted, nor any button to initiate review.

### Root Cause 2: Absence of Explicit `submitted_at` Column in Schema
- **Database Schema Gap:** The `properties` table lacked a `submitted_at` timestamp column.
- **View Fallback Misrepresentation:** The Admin reviews view rendered `$property->created_at->format('M d, Y')` under a column titled "Submitted". Consequently, draft records created on any given date appeared to administrators as having been submitted on that date.

### Root Cause 3: Queue Aggregation Lumping Drafts into Submissions
- **Admin Query:** In `PropertyController::adminPending()`, the total submissions metric was computed using `Property::count()` or `approval_status !== 'pending'`, which inadvertently counted drafts alongside approved, pending, and rejected properties.
- **View Card Semantics:** The card was labeled "Total Submissions" but reflected the total count of all property records in the database, including drafts.

### Root Cause 4: Lack of Server-Side and UI Gates on Admin Decisions for Drafts
- **Missing Backend Guard:** In `PropertyController::adminApprove()` and `adminReject()`, there was no assertion verifying that `$property->approval_status === 'pending'`. The method directly mutated `approval_status` to `approved` or `rejected`, allowing unsubmitted drafts to be accidentally or maliciously approved.
- **Blade View Template:** `resources/views/admin/properties/pending.blade.php` rendered Approve/Reject modals for all rows where `approval_status !== 'approved'`, thereby presenting action triggers for drafts.

---

## 3. Did Auto-Save Cause the Issue?
Auto-save did **not** cause the status reversion.
- `PropertyController::autoSave` was already safeguarded with:
  `if ($property->approval_status !== 'draft') { return response()->json(['message' => 'Cannot auto-save a property that has already been submitted for review.'], 422); }`
- Furthermore, the auto-save payload updates allowed fields only (`name`, `address`, `description`, `latitude`, `longitude`, `image_path`, etc.) and does **not** include `approval_status`.
- However, auto-save exposed the lack of a submission path: when a landlord created a draft via auto-save and navigated away, returning to the property provided no path to transition it out of draft status.
