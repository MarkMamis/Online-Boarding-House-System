# Landlord Create/Edit UI Unification — Implementation Plan

## 1. Overview & Goal
Unify the Landlord Property and Room creation and editing interfaces by replacing the outdated administrative stepper wizards in `edit.blade.php` with the approved OBHS guided onboarding architecture established in `create.blade.php`.

## 2. Core Architecture
- Extract form logic and question panel markup into shared partials:
  - `resources/views/landlord/properties/partials/guided-form.blade.php`
  - `resources/views/landlord/rooms/partials/guided-form.blade.php`
- Clean wrapper shells:
  - `properties/create.blade.php` passes `mode => 'create'`, `property => $draftProperty`
  - `properties/edit.blade.php` passes `mode => 'edit'`, `property => $property`
  - `rooms/create.blade.php` passes `mode => 'create'`, `property => $property`
  - `rooms/edit.blade.php` passes `mode => 'edit'`, `property => $property`, `room => $room`

## 3. Key Improvements
1. **Removed Old Stepper Wizard:**
   - Eliminated numbered node progress timelines, `stepper-head`, `stepper-node`, `Step 1 of 3`, `Step 1 of 4`.
   - Replaced with focused question-based panels navigated via Previous / Continue / Save & Return to Review.
2. **Removed Redundant Context Cards:**
   - Removed the 3-card stack (`Property`, `Address`, `Price Range` / `Room`, `Current Pricing`, `Address`).
   - Integrated minimal header context with subtle back navigation.
3. **Data Pre-population:**
   - Standard Laravel precedence: `old('field', $model->field ?? $default)`.
   - Existing photos previewed without programmatic file input spoofing.
   - Preserves custom amenities and custom room inclusions cleanly.
   - Edit room pricing models (per-bed, per-room, hybrid) preserved faithfully.
4. **Safety & Invariants:**
   - Property draft auto-save strictly disabled in edit mode for existing approved properties.
   - Final review screen displays accurate values with direct jump-to-edit capability.
   - Room occupancy, tenant contracts, and pricing snapshots preserved.
