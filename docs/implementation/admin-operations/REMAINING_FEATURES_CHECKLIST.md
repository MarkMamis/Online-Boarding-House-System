# Remaining Features Checklist & Implementation Plan

**Date:** 2026-10-08  
**Scope:** Admin Operations Remaining Feature Completion  
**Target Areas:** Incident Reports, Institutional Reporting Presets/PDF, Notifications, User Accounts, MySQL/Hostinger Safety  

---

## Deliverables Matrix

| Item | Component | Current State | Missing Scope / Gap | Resolution Plan |
| :--- | :--- | :--- | :--- | :--- |
| **1** | **Incident Historical Context** | Derives property via student's live active booking | When student moves, incident should remember the property involved at report time without dynamic replacement. | Add optional property association at submission & display preserved accommodation context or clear "General/Historical" fallback without destructive migrations. |
| **2** | **Institutional Report Presets** | 1 generic Occupancy CSV export | 6 standard MinSU presets needed: Active Boarded Students, Boarding House Distribution, Academic Distribution, Occupancy and Availability, Compliance Summary, Incident Summary. | Introduce a unified `AdminReportingService` supporting preset data retrieval, consistent on-screen previews, filtered CSV exports, and printable/PDF layouts. |
| **3** | **Notification Action Routing** | Basic URL redirection | Needs structured action classification (Action Required vs Informational) and deep-linking to dedicated workflows. | Enhance `NotificationController` and shared view with type-aware action links and badge styling. |
| **4** | **User Accounts Status Clarity** | Detailed student and landlord views exist | Ensure distinct semantic separation of Account Active, Email Verified, Student Verified, Landlord Accredited, and Property Approved. | Standardize badges and management actions across user views. |
| **5** | **Production & MySQL Safety** | SQLite tests pass | Strict MySQL `ONLY_FULL_GROUP_BY` compliance and Hostinger shared hosting invariants must be strictly preserved. | Static query audit and Hostinger deployment checklist creation. |
