# Hostinger Deployment Manifest: Property Draft Submission Workflow Fix

## 1. Release Metadata
- **Target Host:** Hostinger Shared Hosting (Apache / MySQL 8.x / PHP 8.2+)
- **Scope:** Property Draft Submission & Admin Review Inconsistency Fix
- **Risk Level:** Low (Backward-compatible schema addition; no destructive database operations)

---

## 2. Modified & Created Files

### Database Migrations
- `database/migrations/2026_10_23_000001_add_submitted_at_to_properties_table.php`

### Models
- `app/Models/Property.php`

### Controllers
- `app/Http/Controllers/PropertyController.php`
- `app/Http/Controllers/AuthController.php`

### Routes
- `routes/web.php`

### Blade Views
- `resources/views/landlord/properties/show.blade.php`
- `resources/views/landlord/properties/index.blade.php`
- `resources/views/landlord/properties/partials/guided-form.blade.php`
- `resources/views/admin/properties/pending.blade.php`
- `resources/views/admin/properties/index.blade.php`

### Tests
- `tests/Feature/PropertyDraftSubmissionWorkflowTest.php`

---

## 3. Hostinger Deployment Execution Steps

```bash
# 1. Pull approved commit
git pull origin main

# 2. Run additive database migration
php artisan migrate --force

# 3. Clear and regenerate route, config, and view caches
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 4. Compile frontend assets if running node locally or sync public/build
npm run build
```

---

## 4. Production Verification Steps (Smoke Test)
1. **Landlord View (Draft Property):**
   - Log in as the landlord owning Property ID 2 ("Lolita Valenzuela Boarding House").
   - Navigate to `/landlord/properties/2`.
   - Verify the amber notice: *"Your property is saved but hasn't been submitted for approval."*
   - Verify the primary CTA: *"Review & Submit"*.
   - Click "Review & Submit" and verify redirection to Panel 3 review screen.
   - Verify both "Save Changes" and "Submit for Review" are accessible.
2. **Admin Review Queue:**
   - Log in as System Administrator.
   - Navigate to `/admin/property-reviews`.
   - Verify the "Pending Review" tab only lists properties actually submitted.
   - Verify Drafts tab displays unsubmitted drafts with badge *"Not yet submitted"* and NO Approve/Reject buttons.
   - Verify Total Submissions card does NOT include unsubmitted drafts.
3. **Student Marketplace:**
   - Verify draft and pending properties remain hidden on `/browse-map` and `/student/properties/map`.
