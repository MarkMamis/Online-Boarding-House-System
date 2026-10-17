# UI Component Reuse Specification

## 1. Design Reference Source
The Landlord Property and Room setup interfaces have been refactored to align directly with the approved OBHS guided onboarding architecture established in:
- `resources/views/student/setup/index.blade.php` (`/student/setup`)
- `resources/views/landlord/setup/index.blade.php` (`/landlord/setup`)
- `resources/views/auth/onboarding-role.blade.php` (`/onboarding/role`)

## 2. Reused Design Tokens & Visual Hierarchy
- **Shell Layout:** Dedicated, uncluttered onboarding shell with subtle neutral background (`#f8fafc`), clean brand logo/header, and clear escape link ("Exit to Dashboard" or "Back to Properties").
- **Form Card:** Centered white card (`.guided-setup-card`, max-width `680px`), rounded corners (`1.25rem` / `20px`), subtle layered shadow (`0 20px 40px -15px rgba(0,0,0,0.06), 0 0 1px 1px rgba(0,0,0,0.04)`), and responsive padding (`1.75rem 2.25rem` on desktop, `1.25rem 1rem` on mobile).
- **Context & Category Labels:** Compact, uppercase category pill/badge (`.category-label`) with tracking (`letter-spacing: 0.08em`, font-size `0.72rem`, muted emerald green `#059669`).
- **Headings & Subtitles:** Clean typography with high-contrast titles (`.question-title`, `1.45rem`, semi-bold `#0f172a`) and concise single-sentence helper descriptions (`.question-subtitle`, `0.88rem`, `#64748b`).
- **Form Controls:** Modern, rounded inputs (`.form-control`, `0.75rem` radius, border `#cbd5e1`), crisp focus ring with theme primary color (`#059669` emerald), floating/static readable labels (`0.82rem`, `#334155`).
- **Interactive Chips & Radio Cards:** Interactive amenity chips (`.chip-btn`) and pricing model radio cards (`.pricing-model-card`) that cleanly toggle active state with `#ecfdf5` background and `#059669` border.
- **Upload Dropzones:** Accessible, polished drag/click dropzones with file information pills, preview thumbnails, replace actions, and clear remove triggers (`.btn-remove-photo`).
- **Navigation Action Bar:** Responsive footer row (`.actions-row`) placing secondary actions (Previous, Save Draft) on the left/secondary slot and prominent green primary buttons (`.btn-continue`, `.btn-primary-action`, `#059669` gradient) on the right.
- **Editable Review Panels:** Summary cards (`.review-card`) grouping user inputs with individual "Edit" shortcuts (`.btn-review-edit`) that transition directly back to target question panels and activate "Save & Return to Review" buttons.

## 3. Avoidance of Unrelated Coupling
- Neither `PropertyController` nor `RoomController` were merged into onboarding setup controllers (`LandlordSetupController`, `StudentSetupController`).
- All backend routes, CSRF protections, validation rules, model events, and authorization checks remain strictly segregated.
