# 08 — Security, Performance & Hostinger Compatibility Audit

## Executive Summary
This document reviews the security posture, role authorization, database performance, query efficiency, and Hostinger Shared Hosting constraints for `/admin/analytics`.

---

## 1. Security & Authorization Posture

| Security Check | Implementation | Status | Code Evidence / Risk Analysis |
|---|---|---|---|
| **Role Authorization** | Middleware + In-Controller check | **PASS** | Route has `role:admin` middleware; `AdminAnalyticsController::index` and `exportCsv` explicitly enforce `Auth::user()->role === 'admin'`. Non-admins receive `403 Forbidden`. |
| **SQL Injection Protection** | PDO parameter binding | **PASS** | All dynamic filters use Eloquent `whereKey`, `whereHas`, or parameterized subqueries. No raw unescaped string concatenations. |
| **CSV Formula Injection** | Sanitizes `=`, `+`, `-`, `@` prefixes | **PASS** | `AdminAnalyticsController.php:130` prefixes dangerous characters with single quotes. |
| **Student PII Protection** | Admin-restricted export | **PASS** | Sensitive student contact details (email, phone, student ID) are only exposed to authenticated administrative accounts. |
| **XSS in Blade Tables** | Blade `{{ $val }}` escaping | **PASS** | `index.blade.php:312` uses standard Blade curly braces, ensuring HTML entities are escaped. |

---

## 2. Performance & Database Query Analysis

### Query Load on Page Access
When `/admin/analytics` is requested, the controller executes:
1. `resolveReportingPeriod`: 0 queries (Carbon date math).
2. `buildBaseQuery`: 0 queries (Builder preparation).
3. `getKpis`:
   - `getSummaryMetrics`: 6 `COUNT` / `COUNT(DISTINCT)` queries on `bookings`.
   - `registeredStudents` & `registeredLandlords`: 2 queries on `users`.
   - `approvedProperties` & `totalProperties`: 2 queries on `properties`.
   - `roomCounts`: 1 aggregate query on `rooms`.
   - `pendingBookings` & `pendingDocuments`: 2 queries.
4. `getBoardingTrend`: 24 queries (12-month loop executing 2 queries per month).
5. `getStudentDemographics`: 4 queries (Colleges, Programs, Gender subqueries).
6. `getPropertyOccupancyAnalytics`: 1 query on `properties` with subquery counts on `rooms`.
7. `getBookingStatusBreakdown`: 1 query.
8. `getDocumentComplianceBreakdown`: 2 queries.
9. `getReportData`: 1–2 queries for the selected preset.

**Total Database Queries Per Page Load:** Approximately **45–50 queries**.

---

## 3. Hostinger Shared Hosting Invariants

| Hosting Constraint | Risk Assessment | Mitigation / Recommendation |
|---|---|---|
| **No Redis / In-Memory Cache** | Cache is file/database-based. Executing 50 queries synchronously per page load can cause 800ms–1.5s latency on shared MySQL. | Combine the 12-month loop in `getBoardingTrend` into a single `GROUP BY MONTH(check_in)` query. |
| **PHP Memory Limit (128MB–256MB)** | Low risk for CSV (uses `StreamedResponse` and chunked cursor). High risk if DomPDF is introduced for large student tables. | Avoid server-side DomPDF for large institutional reports; utilize dedicated printable Blade views streamed to the browser. |
| **MySQL `ONLY_FULL_GROUP_BY` Strict Mode** | High importance. The codebase already complies using subquery projections. | Maintain strict SQL compliance; never disable strict mode. |
| **No Supervisor / Workers** | Background queue processing is unavailable. All report generation must complete synchronously and quickly (< 3 seconds). | Keep queries lean and indexed on `bookings(status, check_in, check_out, student_id)`. |
