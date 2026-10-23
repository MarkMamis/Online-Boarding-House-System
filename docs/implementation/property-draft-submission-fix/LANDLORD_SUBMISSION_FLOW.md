# Landlord Property Submission Workflow

## 1. Overview
The landlord property submission workflow enables landlords to save in-progress listing setup details while providing an explicit, validated path to submit the property for administrative review.

---

## 2. Touchpoints & User Experience

### A. Property Workspace / Details (`/landlord/properties/{property}`)
When viewing an existing property in `draft` status:
1. **Contextual Alert Banner:** A compact warning banner informs the landlord:
   `"Your property is saved but hasn't been submitted for approval."`
2. **Primary CTA:** A prominent button `"Review & Submit"` deep-links to `/landlord/properties/{property}/edit?panel=3`.
3. **Secondary CTA:** `"Edit Property"` allows general editing.
4. **Pending Notice:** When in `pending` status, an informative pill renders:
   `"Waiting for Admin review. Your property submission is queued for administrative review."`
   Duplicate submit buttons are prevented.

### B. Landlord Properties List (`/landlord/properties`)
On property cards:
1. **Status Badge:** Renders `"Draft / Not submitted"`.
2. **Contextual Primary Action:**
   - If property is complete for submission (`isCompleteForSubmission() === true`): renders primary CTA `"Review & Submit"`.
   - If missing required fields: renders primary CTA `"Continue Setup"`.
3. **Secondary Actions:** `"Manage Property"`, `"Add Room"`, and `"Delete Property"` remain accessible.

### C. Edit Property Form (`/landlord/properties/{property}/edit`)
In the guided form:
1. **Panel Support:** Deep linking via `?panel=3` or `?review=1` opens the final review section directly.
2. **Review Checklist (Panel 3):**
   - Displays a dynamic checklist of required items (Property Name, Street Address, Cover Photo, Location Coordinates).
   - If any item is missing, an actionable link (`#section-basic`, `#section-photos`, `#section-location`) allows immediate one-click navigation to the input section.
3. **Dual CTAs for Draft Properties:**
   - `"Save Changes"` (`action="save"`): Persists updates without altering `draft` status.
   - `"Submit for Review"` (`action="submit"`): Validates prerequisites and transitions the property to `pending`.

---

## 3. Dedicated Submission Endpoint
- **Route:** `POST /landlord/properties/{property}/submit` (`landlord.properties.submit`)
- **Controller Action:** `PropertyController::submit(Property $property)`
- **Server-Side Authorization:**
  - Requires authenticated landlord role (`$this->ensureLandlord()`).
  - Enforces ownership policy (`$this->authorize('update', $property)`).
- **Validation:**
  - Checks `$property->getMissingSubmissionRequirements()`.
  - If invalid, redirects back to Panel 3 with validation errors and section deep-links.
- **Atomic Transition:**
  - Updates `approval_status = 'pending'`.
  - Sets `submitted_at = now()`.
  - Triggers asynchronous `SystemNotification` to administrators.
- **Idempotency:** If the property is already in `pending` status, redirects with an informational message without duplicating notifications or resetting timestamps.
