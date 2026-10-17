# Form State & Validation Architecture

## 1. Single Canonical Form State
- Both `create.blade.php` implementations utilize a single standard HTML `<form>` wrapping all panels.
- Inputs in hidden panels remain present in the DOM (`display: none` applied via panel container), ensuring native form submission payloads contain all fields across all panels.
- Native browser `required` attributes are avoided on inactive panels to prevent Chrome/Firefox "An invalid form control with name='...' is not focusable" silent submission halts.

## 2. Progressive Client-Side Validation
Before advancing panels (`Continue` button), client-side validation executes for the active panel:
- **Property Basics:** Validates that Property Name and Address are populated.
- **Location:** Validates that Latitude and Longitude contain valid numeric coordinates.
- **Room Details:** Validates that Room Number is non-empty and Capacity is a positive integer.
- **Room Pricing:** Validates that the active pricing model input (`price_per_bed` or `price_per_room`) contains a non-negative number.
- Invalids highlight with the `.is-invalid` class and focus the offending input.

## 3. Editable Review & Return-to-Review Flow
- On the Review panel, clicking any `.btn-review-edit` button:
  1. Stores the target review step in state (`editingFromReview = true; reviewTargetStep = panelIndex`).
  2. Directly activates the chosen question panel (`showPanel(panelIndex)`).
  3. Displays a dedicated emerald button: `"Save & Return to Review"`.
  4. Hides normal "Continue" buttons during edit mode.
  5. Clicking "Save & Return to Review" performs panel validation, re-syncs the review summary data (`syncReviewData()`), and returns immediately to the Review screen without forcing the landlord through intermediate steps.

## 4. Server-Side Validation & Error Preservation
- Laravel server-side validation rules (`PropertyController@store`, `RoomController@store`) remain authoritative.
- If server validation fails:
  - Global error alerts render at the top of the card (`.alert-danger`).
  - Blade `@error('field')` directives render `.invalid-feedback` messages under the respective fields.
  - Old input values are pre-filled using `old('field', $default)`.
  - The script checks if validation errors exist in panels beyond Panel 0 and can activate the first panel with errors automatically.

## 5. Draft Validation vs. Final Submission
- **Save Draft:** Submits with `is_draft=1` or `action='draft'`. Controller applies relaxed validation rules (allowing partial data and saving with `approval_status = 'draft'`).
- **Submit for Review:** Submits with full required validation (saving with `approval_status = 'pending'`).
