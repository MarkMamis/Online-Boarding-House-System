# Admin Operations Improvement Roadmap — Implementation Plan

## 1. Project Context & Objectives
- **Target Application:** Online Boarding House System (OBHS)
- **Deployment Host:** Hostinger Shared Web Hosting (Apache, MySQL 8.x, PHP 8.2+)
- **Primary Goal:** Execute a structured, phase-based improvement initiative for safety, incident resolution, administrative action overview, account administration, notifications, and institutional reporting.

---

## 2. Phase-by-Phase Roadmap & Dependencies

### Phase 1: Security & Workflow Foundations (Highest Priority)
- **Scope:**
  - Active session termination for deactivated accounts (middleware level).
  - Notification recipient isolation regression protection tests.
  - Role escalation safeguards on account updates.
  - Report status transition validation & unauthorized status modification protection.
- **Dependencies:** None.
- **Affected Files:**
  - `app/Http/Middleware/RoleMiddleware.php` / `app/Http/Middleware/EnsureAccountIsActive.php`
  - `bootstrap/app.php`
  - `tests/Feature/AdminSecurityFoundationsTest.php`

### Phase 2: Incident Management & Resolution Workflow
- **Scope:**
  - Reposition Reports navigation from Insights to **Management -> Incident Reports** in sidebar.
  - Refine `/admin/reports` with status quick-tabs (`All`, `Pending`, `In Progress`, `Resolved`) and priority indicators.
  - Surface associated boarding house / room derived context directly on incident cards and details.
  - Preserve `ReportPolicy` authorization and notification alerts.
- **Dependencies:** Phase 1.
- **Affected Files:**
  - `resources/views/layouts/admin.blade.php`
  - `resources/views/admin/reports/index.blade.php`
  - `resources/views/admin/reports/show.blade.php`
  - `app/Http/Controllers/ReportController.php`
  - `tests/Feature/AdminIncidentReportsTest.php`

### Phase 3: Admin Action Center Workspace
- **Scope:**
  - Implement `/admin/action-center` (`admin.action_center.index`) as a central "What needs attention today?" workspace.
  - Aggregate pending reviews: Property Reviews, Student Verifications, Landlord Accreditations, Document Queue, and Urgent Incidents.
  - Deep-link into existing management workflows without duplicate review/approval logic.
- **Dependencies:** Phases 1 & 2.
- **Affected Files:**
  - `routes/web.php`
  - `app/Http/Controllers/AdminActionCenterController.php`
  - `resources/views/admin/action_center/index.blade.php`
  - `resources/views/layouts/admin.blade.php`
  - `tests/Feature/AdminActionCenterTest.php`

### Phase 4: Account Administration & Notification Ergonomics
- **Scope:**
  - Standardize cascading filter dropdown styling in `admin.users.by_role` to use unified `.admin-toolbar` tokens.
  - Enhance notification activity cards with actionable deep links and role-specific categorization.
- **Dependencies:** Phases 1–3.
- **Affected Files:**
  - `resources/views/admin/users/by_role.blade.php`
  - `resources/views/notifications/index.blade.php`
  - `tests/Feature/NotificationWorkflowTest.php`

### Phase 5: Institutional Reporting & CSV Exports
- **Scope:**
  - Add institutional CSV export capabilities to `/admin/analytics` and `/admin/boarding-monitoring/students` for official university reporting.
  - Implement streaming/safe CSV generation with formula injection sanitization.
- **Dependencies:** Phases 1–4.
- **Affected Files:**
  - `app/Http/Controllers/AdminAnalyticsController.php`
  - `resources/views/admin/analytics/index.blade.php`
  - `tests/Feature/AdminInstitutionalReportingTest.php`
