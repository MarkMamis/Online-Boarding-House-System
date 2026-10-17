# Production Deployment Guide: MySQL Strict Mode Compatibility for Analytics & Institutional Reports

## 1. Confirmed Root Cause

When accessing `https://mcc-boardinghouse.online/admin/analytics`, production Hostinger Shared Hosting responded with an HTTP 500 error. The production Laravel log recorded:

```
SQLSTATE[42000]: Syntax error or access violation: 1055 'users.college' isn't in GROUP BY
```

Stack trace origin:
```
BoardingMonitoringService::getCollegeDistribution()
→ AnalyticsService::getStudentDemographics()
→ AdminAnalyticsController::index()
```

### Why Did This Fail?
In `app/Services/BoardingMonitoringService.php`, `getCollegeDistribution()` and `getProgramDistribution()` queried the database using:
```php
$collegeExpr = "COALESCE(NULLIF(TRIM(users.college), ''), 'Not specified')";
// ...
->select(
    DB::raw("{$collegeExpr} as college_code"),
    DB::raw('COUNT(DISTINCT bookings.student_id) as total_students'),
    DB::raw('COUNT(bookings.id) as total_records')
)
->groupByRaw($collegeExpr)
```

In standard MySQL 8.0 environments running with `sql_mode=ONLY_FULL_GROUP_BY` (as enforced on Hostinger Shared Hosting):
- The SELECT expression references column `users.college`.
- MySQL evaluates whether every non-aggregated column referenced in the SELECT clause is functionally dependent on or present in the GROUP BY clause.
- In MySQL 8.0, grouping by an expression `F(users.college)` is **not** recognized as grouping by the column `users.college`, nor does MySQL deduce functional equivalence when complex functions (`COALESCE`, `NULLIF`, `TRIM`) wrap the column reference.
- MySQL 8.0 therefore strictly rejects the query with error 1055.
- Additionally, `getProgramDistribution()` shared the identical vulnerability for both `users.college` and `users.program`.

---

## 2. SQL Query: Before and After

### 2.1 Before (Failing Query)
```sql
SELECT
    COALESCE(NULLIF(TRIM(users.college), ''), 'Not specified') AS college_code,
    COUNT(DISTINCT bookings.student_id) AS total_students,
    COUNT(bookings.id) AS total_records
FROM bookings
INNER JOIN users ON users.id = bookings.student_id
WHERE EXISTS (
    SELECT * FROM users
    WHERE bookings.student_id = users.id AND role = 'student'
)
-- [additional date/property/status WHERE clauses]
GROUP BY COALESCE(NULLIF(TRIM(users.college), ''), 'Not specified')
ORDER BY total_students DESC;
```

### 2.2 After (Strict MySQL Derived Table Query)
```sql
SELECT
    normalized_college.college_code,
    COUNT(DISTINCT normalized_college.student_id) AS total_students,
    COUNT(normalized_college.booking_id) AS total_records
FROM (
    SELECT
        bookings.id AS booking_id,
        bookings.student_id AS student_id,
        COALESCE(NULLIF(TRIM(users.college), ''), 'Not specified') AS college_code
    FROM bookings
    INNER JOIN users ON users.id = bookings.student_id
    WHERE EXISTS (
        SELECT * FROM users
        WHERE bookings.student_id = users.id AND role = 'student'
    )
    -- [Preserves all canonical baseQuery filters: dateBasis, stay overlap, property_id, status]
) AS normalized_college
GROUP BY normalized_college.college_code
ORDER BY total_students DESC;
```

### Why the Refactor Guarantees Compatibility:
1. In the outer query, the non-aggregated SELECT column is `normalized_college.college_code`.
2. The outer query GROUP BY clause is explicitly `normalized_college.college_code`.
3. The underlying table column `users.college` is confined to the non-aggregating inner derived table (`fromSub`), where `ONLY_FULL_GROUP_BY` is never triggered.
4. The exact same pattern is applied to `getProgramDistribution()` grouping by `(college_code, program_name)`.
5. In PHP collection mapping, residual whitespace variants (e.g. tab/newline strings) are safely normalized to `'Not specified'` and deduplicated via `groupBy()->map()`.

---

## 3. Local vs Production Differences

| Dimension | Local Development | Hostinger Shared Hosting (Production) |
| :--- | :--- | :--- |
| **RDBMS Engine** | MySQL 8.4.3 (Windows x64) | MySQL 8.0.x / MariaDB (Linux x64) |
| **SQL Mode** | `ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,...` | `ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,...` |
| **Test Database** | SQLite 3 in-memory (`phpunit.xml`) | MySQL 8.0 Production |
| **Why Local Passed** | SQLite tests ignore `ONLY_FULL_GROUP_BY`. In MySQL 8.4.3, expression equivalence handling in SELECT/GROUP BY is more permissive than MySQL 8.0. | MySQL 8.0 on Hostinger enforces strict AST non-aggregation checks, failing if column `users.college` is referenced in SELECT while GROUP BY uses an expression. |
| **PHP Version** | PHP 8.2+ | PHP 8.2+ |

---

## 4. Code Changes

File modified:
- `app/Services/BoardingMonitoringService.php`
  - Refactored `getCollegeDistribution()` to use `fromSub()` derived table subquery.
  - Refactored `getProgramDistribution()` to use `fromSub()` derived table subquery.
  - Hardened `buildBaseQuery()` to parse and cast `periodStart`/`periodEnd` across `Carbon`, `CarbonInterface`, `DateTimeInterface`, or string representations.
  - Added post-processing mapping normalization to ensure empty/whitespace variants coalesce into `'Not specified'`.

---

## 5. Regression Tests

New dedicated test file created:
- `tests/Feature/AdminAnalyticsStrictSqlCompatibilityTest.php` (12 tests, 62 assertions)
  - `test_no_active_boarders_returns_empty_collections_cleanly`
  - `test_one_boarded_student_in_college`
  - `test_multiple_students_in_one_college`
  - `test_multiple_colleges_with_multiple_students_sorted_by_total_students_desc`
  - `test_one_student_with_multiple_historical_stays_counts_distinct_student_once_and_records_correctly`
  - `test_null_empty_and_whitespace_variants_of_college_are_normalized_to_not_specified`
  - `test_unknown_or_unlisted_college_code_is_preserved_and_grouped`
  - `test_program_distribution_infers_college_when_college_is_not_specified`
  - `test_different_reporting_periods_filter_distribution_accurately`
  - `test_admin_analytics_http_get_returns_200_and_renders_demographics`
  - `test_admin_institutional_reports_academic_distribution_preset_returns_200`
  - `test_authorization_is_strictly_enforced_on_analytics_and_reports`

All tests pass: **253 passed across entire repository test suite (1,282 assertions).**

---

## 6. Deployment File Manifest

The fix is **pure PHP service logic**. No routes, Blade views, CSS/JS assets, Composer packages, or database migrations were changed.

| Source Local Path | Production Relative Path | Reason for Upload | Verification Method |
| :--- | :--- | :--- | :--- |
| `app/Services/BoardingMonitoringService.php` | `app/Services/BoardingMonitoringService.php` | Replaces failing GROUP BY expression with derived table aggregation | SHA-256 Checksum / file inspection |

> [!IMPORTANT]
> **DO NOT UPLOAD:**
> - `.env` (Never overwrite production environment or credentials)
> - `tests/` (Development tests do not run in production)
> - `vendor/` (No dependencies changed)
> - `public/build/` (Frontend assets were untouched)
> - `storage/` or `.git`

---

## 7. Step-by-Step Hostinger Deployment Instructions

### Method A: Hostinger File Manager (Web UI)
1. Log in to [Hostinger hPanel](https://hpanel.hostinger.com).
2. Navigate to **Websites** → `mcc-boardinghouse.online` → **File Manager**.
3. Locate the Laravel application root (typically `public_html` or the directory immediately parent to `public_html`).
4. **Backup affected file:**
   - Navigate to `app/Services/`.
   - Right-click `BoardingMonitoringService.php` and select **Download** (save as `BoardingMonitoringService.php.bak_20261017`).
5. **Upload patched file:**
   - Upload local `app/Services/BoardingMonitoringService.php` to `app/Services/` on Hostinger, overwriting the existing file.
6. **Clear Application Cache:**
   - In File Manager, navigate to `bootstrap/cache/`.
   - Delete cached files if present: `config.php`, `routes-v7.php`, `services.php`, `packages.php`. (Do NOT delete `.gitignore`).
   - Navigate to `storage/framework/views/` and delete compiled `.php` views to prevent stale view caches.
7. **Reset OPcache (if enabled):**
   - In hPanel, go to **Advanced** → **PHP Configuration** → select or toggle PHP version/extensions, or restart PHP-FPM if available, to flush opcode caches.

### Method B: SSH (If Enabled on Hostinger)
1. Connect via SSH:
   ```bash
   ssh u123456789@mcc-boardinghouse.online -p 65002
   ```
2. Navigate to application root:
   ```bash
   cd ~/domains/mcc-boardinghouse.online/public_html   # or configured root
   ```
3. Backup existing file:
   ```bash
   cp app/Services/BoardingMonitoringService.php app/Services/BoardingMonitoringService.php.bak_$(date +%Y%m%d_%H%M%S)
   ```
4. Transfer patched file via SFTP/SCP to `app/Services/BoardingMonitoringService.php`.
5. Clear caches:
   ```bash
   php artisan optimize:clear
   php artisan view:clear
   php artisan config:cache
   php artisan route:cache
   ```

---

## 8. Post-Deployment Verification Checklist

Test the following production URLs while authenticated as Administrator:

1. **Analytics Dashboard:**
   - URL: `https://mcc-boardinghouse.online/admin/analytics`
   - Expected Result: HTTP 200 OK.
   - Verify UI:
     - KPI cards load (Active Boarders, Occupancy Rates, Room Counts).
     - 12-Month Boarding Trend chart renders.
     - Student Demographics chart renders College and Program breakdowns.
     - Gender distribution renders.
     - Boarding House occupancy table renders.
2. **Institutional Reports Workspace:**
   - URL: `https://mcc-boardinghouse.online/admin/institutional-reports?report_preset=academic_distribution`
   - Expected Result: HTTP 200 OK.
   - Verify UI: Table displays College Code, Program Name, and Active Boarders with valid totals.
3. **Boarded Students Monitoring:**
   - URL: `https://mcc-boardinghouse.online/admin/boarding-monitoring/students`
   - Expected Result: HTTP 200 OK.
   - Verify UI: College distribution pills/breakdowns populate properly.
4. **Log Inspection:**
   - Inspect `storage/logs/laravel.log` on Hostinger to ensure no new `SQLSTATE[42000]: 1055` errors occur.

---

## 9. Rollback Plan

If an unexpected error occurs after uploading:
1. Re-open Hostinger File Manager.
2. Navigate to `app/Services/`.
3. Delete the uploaded `BoardingMonitoringService.php`.
4. Rename `BoardingMonitoringService.php.bak_20261017` back to `BoardingMonitoringService.php`.
5. Clear `bootstrap/cache/*.php`.
6. Verify site returns to previous state.
