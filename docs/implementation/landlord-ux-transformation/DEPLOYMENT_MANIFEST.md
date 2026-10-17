# Deployment Manifest & Hostinger Production Notes

**Document Reference:** `docs/implementation/landlord-ux-transformation/DEPLOYMENT_MANIFEST.md`  
**Host Target:** Hostinger Shared Hosting (Dual Dev/Production Parity)  
**Database Changes:** Zero migrations required (uses existing schema and columns).

---

## 1. Summary of Modified Code Files

### Controllers & Routing
- `routes/web.php` — Added `POST /landlord/properties/{property}/rooms/{room}/duplicate` (`landlord.properties.rooms.duplicate`).
- `app/Http/Controllers/AuthController.php` — Enriched `landlordDashboard` with urgent action queues and portfolio capacity statistics.
- `app/Http/Controllers/PropertyController.php` — Decoupled initial room creation; added draft property saving (`approval_status = 'draft'`).
- `app/Http/Controllers/RoomController.php` — Implemented safe 1-click room duplication with ownership authorization.
- `app/Http/Controllers/BookingController.php` — Re-directed booking approval directly to tenant move-in onboarding.
- `app/Http/Controllers/PaymentController.php` — Reconciled `markAsPaid` to generate approved `TenantPayment` entries for cash payments.

### Views & Layouts
- `resources/views/layouts/landlord.blade.php` — Cleaned up sidebar navigation; removed recursive dynamic room loop.
- `resources/views/landlord/dashboard.blade.php` — Rebuilt action-first Landlord Home with "Needs Your Attention Today" priority queues.
- `resources/views/landlord/properties/create.blade.php` — Made room creation optional; added "Save Draft" button and handler.
- `resources/views/landlord/properties/show.blade.php` — Added room duplication trigger buttons.
- `resources/views/landlord/rooms/index.blade.php` — Added room duplication trigger buttons.
- `resources/views/landlord/bookings/index.blade.php` — Added onboarding guidance alert and direct move-in step links.
- `resources/views/landlord/tenants/index.blade.php` — Added residency badges (`Active Resident`, `Move-in In Progress`) and residency filter.
- `resources/views/landlord/payments/index.blade.php` — Simplified financial summary KPI cards and tab navigation.
- `resources/views/landlord/maintenance/index.blade.php` — Clarified room availability and maintenance mode microcopy.
- `resources/views/landlord/leave_requests/index.blade.php` — Clarified future departure move-out policy and confirmation prompts.
- `resources/views/landlord/monitoring/properties.blade.php` — Supported draft property badge status in monitoring.

### Tests
- `tests/Feature/LandlordTransformationTest.php` — Automated test coverage for all transformation phases.

---

## 2. Production Deployment Steps (Shared Hosting)

1. **Pull Code:** Sync repository updates to production directory.
2. **Clear Caches:**
   ```bash
   php artisan config:clear
   php artisan route:clear
   php artisan view:clear
   ```
3. **Build Assets:**
   ```bash
   npm run build
   ```
4. **Zero Database Migrations:** No migrations or destructive operations are needed.
