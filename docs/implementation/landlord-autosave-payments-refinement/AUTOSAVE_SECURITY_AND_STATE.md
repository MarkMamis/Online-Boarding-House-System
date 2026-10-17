# Auto-Save Security & State Invariants

## 1. Route & Authorization
- **Endpoint:** `POST /landlord/properties/autosave` (`landlord.properties.autosave`).
- **Middleware:** `['auth', 'verified', 'role:landlord', 'landlord.setup:profile_complete']`.
- **CSRF Protection:** Verified automatically via standard Laravel `VerifyCsrfToken` middleware through `X-CSRF-TOKEN` request header.

## 2. Protected Fields & Tamper Resistance
The auto-save payload accepts and sanitizes only safe property attributes:
- `name` (max 255 chars)
- `address` (max 255 chars)
- `description` (max 5000 chars)
- `latitude` (numeric, -90 to 90)
- `longitude` (numeric, -180 to 180)
- `building_inclusions` (array of allowed amenities from config)
- `house_rules` (categorized text lines)
- `image` (max 5MB, valid image MIME types)

### Explicitly Rejected Attributes:
- Any attempt to alter `approval_status`, `status`, `approved_by`, `approved_at`, or `rejection_reason` through the auto-save endpoint is ignored.
- The auto-save endpoint strictly verifies that target draft property has `approval_status === 'draft'`.
- If an existing property has already been submitted for review (`approval_status = 'pending'`) or approved (`approval_status = 'approved'`), the auto-save endpoint immediately rejects the request with HTTP 422:
  `"Cannot auto-save a property that has already been submitted for review."`
- Cross-landlord updates are strictly prevented: `Property::where('id', $propertyId)->where('landlord_id', Auth::id())->firstOrFail()` returns 404/403 if `property_id` belongs to another user.

## 3. Concurrency & Version Tracking
- Each draft response includes `draft_version` (timestamp from `updated_at`).
- Client submits `draft_version` with requests.
- Overlapping client saves are coalesced: if a network save is in progress, new edits mark `hasPendingSave = true` and dispatch a follow-up request immediately after the first resolves, guaranteeing the server always holds the newest form state.
