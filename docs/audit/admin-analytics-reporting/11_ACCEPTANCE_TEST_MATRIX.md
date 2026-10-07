# 11 — Acceptance Test Matrix

## Overview
This document specifies concrete acceptance criteria and verification scenarios for the future implementation phase of the Admin Analytics and Institutional Reporting system.

---

## Acceptance Test Scenarios

| Scenario ID | Test Description | Input / Trigger | Expected Verification Criteria | Current Baseline Status |
|---|---|---|---|---|
| `ACC-REP-001` | Active Boarded Students Preset Selection | Navigate to `/admin/analytics?report_preset=active_boarders` | Table preview renders de-duplicated student listing with columns: Student Name, Student ID, College, Program, Boarding House, Room, Check-In, Check-Out. | **PASS** (Table updates; UI context remains combined) |
| `ACC-REP-002` | Boarding House Distribution Preset Selection | Navigate to `/admin/analytics?report_preset=property_distribution` | Table preview renders property-level breakdown with total rooms, occupied rooms, available rooms, bed capacity, occupied beds, and occupancy rate. | **PASS** |
| `ACC-REP-003` | Academic Distribution Preset Selection | Navigate to `/admin/analytics?report_preset=academic_distribution` | Table preview renders breakdown by College Code and Program Name with active boarder counts. | **PASS** |
| `ACC-REP-004` | Occupancy and Availability Preset Selection | Navigate to `/admin/analytics?report_preset=occupancy_availability` | Table preview renders room inventory, vacant slots, room occupancy rate, and bed occupancy rate. | **PASS** |
| `ACC-REP-005` | Landlord Compliance Summary Preset Selection | Navigate to `/admin/analytics?report_preset=compliance_summary` | Table preview renders permit and safety certificate counts across valid, expiring, expired, and pending statuses. | **PASS** |
| `ACC-REP-006` | Incident Reports Summary Preset Selection | Navigate to `/admin/analytics?report_preset=incident_summary` | Table preview renders triage status counts and priority distribution. | **PASS** |
| `ACC-REP-007` | Dedicated Print Report Action | Click "Print Report" button | Opens dedicated print view without sidebar, topbar, filter forms, preset pills, or unrelated charts. Shows MinSU header, metadata box, and clean A4 data table. | **FAIL (Currently prints entire 4-page dashboard)** |
| `ACC-REP-008` | Filter Propagation in CSV Export | Apply date range + college filter and click "Export CSV" | Generated CSV reflects selected preset AND filtered date range, property, and college. Includes UTF-8 BOM and formula sanitization. | **PARTIAL (Date range currently dropped in CSV)** |
| `ACC-REP-009` | Role-Based Access Control on Print & Export | Non-admin accesses export/print routes | HTTP 403 Forbidden returned; unauthenticated redirected to login. | **PASS (Verified on existing routes)** |
| `ACC-REP-010` | Tabbed UI State Isolation | Switch between "Analytics Dashboard" and "Institutional Reports" tabs | Analytics tab shows charts and KPI cards; Institutional Reports tab isolates preset selector, table preview, and export/print actions. | **NOT IMPLEMENTED (Planned)** |
