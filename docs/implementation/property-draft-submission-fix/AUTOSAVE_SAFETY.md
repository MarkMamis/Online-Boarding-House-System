# Auto-Save Safety & Concurrency Protection

## 1. Safety Directives
The property auto-save mechanism (`POST /landlord/properties/autosave`) is strictly restricted to in-flight draft creation and draft editing. It must never alter review states or mutate properties in non-draft statuses.

---

## 2. Invariants & Guardrails

### A. Non-Draft Mutation Gate
`PropertyController::autoSave` verifies the target property's approval status prior to applying any field updates:
```php
if ($property->approval_status !== 'draft') {
    return response()->json([
        'success' => false,
        'approval_status' => $property->approval_status,
        'message' => 'Cannot auto-save a property that has already been submitted for review.',
    ], 422);
}
```
- If a client sends a delayed or out-of-order auto-save request after submitting the property for review, the request is rejected with HTTP 422.
- The `pending`, `approved`, or `rejected` status remains completely untouched.

### B. Payload Isolation
The data array updated by `autoSave` is restricted to permitted listing attributes:
- `name`, `address`, `description`, `latitude`, `longitude`, `building_inclusions`, `house_rules`, and `image_path`.
- The `approval_status`, `submitted_at`, `approved_at`, `rejected_at`, and `rejection_reason` columns are **never** present in the update payload.

### C. First Meaningful Input Validation
To prevent phantom/ghost drafts from being created when a user simply opens the form, `autoSave` checks for meaningful input:
```php
$hasName = filled(trim((string) $request->input('name', '')));
$hasAddress = filled(trim((string) $request->input('address', '')));
$hasDescription = filled(trim((string) $request->input('description', '')));
$hasImage = $request->hasFile('image');

if (! $hasName && ! $hasAddress && ! $hasDescription && ! $hasImage) {
    return response()->json([
        'success' => true,
        'saved' => false,
        'message' => 'No meaningful content to draft yet',
    ]);
}
```

### D. Multi-Device & Race Condition Safety
- Auto-save tracks `draft_version` increments.
- Stale updates cannot revert `submitted_at` or `approval_status`.
- Repeat submissions are idempotent: if a landlord double-clicks "Submit for Review", the second submission detects that the property is already `pending` and exits safely without duplicate notification or state corruption.
