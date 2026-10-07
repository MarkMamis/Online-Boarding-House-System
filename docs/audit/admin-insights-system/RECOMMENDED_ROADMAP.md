# Recommended Implementation Roadmap

## Proposed Follow-Up Phases (Non-Destructive)

### Phase A: Security & Regression Test Hardening
- **Scope:** Add automated tests for notification ownership isolation and report state transition logic.
- **Estimated Complexity:** Small
- **Impact:** Ensures zero future regressions in notification and incident security boundaries.

### Phase B: Reports Workflow & Property Link Enhancement
- **Scope:** Add status quick-tabs (`All`, `Pending`, `In Progress`, `Resolved`) to `/admin/reports` and surface the associated boarding house/room context directly in the incident card.
- **Estimated Complexity:** Small to Medium
- **Impact:** Dramatically speeds up administrator triaging of student issues.

### Phase C: UI/UX Component & Filter Token Standardization
- **Scope:** Standardize custom cascading dropdowns in `admin.users.by_role` to use unified Admin Design System `.admin-toolbar` CSS tokens.
- **Estimated Complexity:** Small
- **Impact:** Eliminates inline CSS overrides and unifies visual look and feel.

### Phase D: Analytics Export Capabilities
- **Scope:** Provide downloadable CSV / formatted print report exports from the main Analytics page.
- **Estimated Complexity:** Small to Medium
- **Impact:** Supports institutional accreditation and university reporting needs.
