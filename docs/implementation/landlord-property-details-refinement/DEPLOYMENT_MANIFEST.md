# Deployment Manifest

**Document Reference:** `docs/implementation/landlord-property-details-refinement/DEPLOYMENT_MANIFEST.md`  
**Feature Scope:** LND-002, LND-003  
**Target Environment:** Hostinger Shared Hosting (Dual Dev Parity)

---

## 1. Files Modified & Added

| File Path | Type | Nature of Modification |
| :--- | :--- | :--- |
| `resources/views/landlord/properties/show.blade.php` | Modified | Complete redesign: room-first layout, availability strip, header simplification, Leaflet timing fix, fallback states. |
| `app/Http/Controllers/PropertyController.php` | Modified | Line 955: Eager loaded `'roomImages'` in `show()` to prevent N+1 query when displaying room thumbnails. |
| `tests/Feature/LandlordPropertyDetailsRefinementTest.php` | Added | 9 comprehensive automated tests verifying authorization, room listing, availability math, empty states, and map behavior. |
| `docs/implementation/landlord-property-details-refinement/*` | Added | 7 implementation and audit documentation files. |
| `.agents/handoffs/2026-10-18-landlord-property-details-refinement.md` | Added | Master agent handoff log. |

---

## 2. Invariants Preserved
- No database migrations required.
- No destructive SQL executed.
- No routes created, renamed, or destroyed.
- Server-side authorization in `PropertyController::ensureLandlord()` and `PropertyPolicy::view()` completely preserved.
- Dual development parity maintained (standard Blade + vanilla CSS without Tailwind or Node daemon dependencies).
- Hostinger shared hosting environment fully compatible.

---

## 3. Post-Deployment Steps (for Hostinger)
1. Git pull on production branch.
2. Run `php artisan view:clear && php artisan view:cache`.
3. Run `php artisan config:cache`.
4. Ensure upload storage permissions remain `755` for directories and `644` for files.
