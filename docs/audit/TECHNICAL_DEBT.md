# Technical Debt & Architecture Refactoring Plan

This document catalogs identified code quality issues, oversized controllers, duplication, and architectural technical debt across the OBHS codebase.

---

## 1. Classification Framework

- **`NOW`**: Address immediately within the governance harness and regression testing baseline.
- **`NEXT`**: High-priority architectural improvements for the next dedicated feature cycle.
- **`LATER`**: Medium/long-term enhancements when expanding marketplace capabilities.
- **`DO_NOT_TOUCH_WITHOUT_PLAN`**: High-blast-radius foundational components that must not be casually altered.

---

## 2. Technical Debt Registry

### Category: NOW (Current Baseline)
| Item ID | Description | Location | Action |
| :--- | :--- | :--- | :--- |
| **DEBT-NOW-001** | Missing automated regression tests for student/landlord role boundaries | `tests/Feature/` | Add `AuthRoleAccessTest`, `BookingTenancyAuthorizationTest`, `PaymentAuthorizationTest`, `FileSecurityAuthorizationTest`. |
| **DEBT-NOW-002** | Hardcoded default secret key fallback in deployment helper | `public/hostinger-setup.php` | Document risk, package exclusion, and post-deployment deletion protocol. |
| **DEBT-NOW-003** | Lack of standardized development protocols and domain agent assignments | `.agents/` | Establish comprehensive development harness and skills. |

---

### Category: NEXT (Planned Near-Term Refactoring)
| Item ID | Description | Location | Planned Refactoring |
| :--- | :--- | :--- | :--- |
| **DEBT-NEXT-001** | Oversized `AuthController.php` (127 KB / ~1,200 lines) | [`AuthController.php`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/app/Http/Controllers/AuthController.php) | Split admin user management, admin approvals, and dashboard stats into dedicated `AdminUserController` and `AdminApprovalController`. |
| **DEBT-NEXT-002** | Heavy logic in `TenantOnboardingController.php` (41 KB) | [`TenantOnboardingController.php`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/app/Http/Controllers/TenantOnboardingController.php) | Extract contract generation and signature handling into a dedicated `LeaseAgreementService`. |
| **DEBT-NEXT-003** | Mixed payment logic between `PaymentController` and `StudentPaymentController` | [`PaymentController.php`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/app/Http/Controllers/PaymentController.php) | Create a unified `BillingLedgerService` for payment state transitions and reminders. |

---

### Category: LATER (Future Roadmap)
| Item ID | Description | Location | Notes |
| :--- | :--- | :--- | :--- |
| **DEBT-LATER-001** | Synchronous PDF generation via DomPDF | `TenantOnboardingController` | Consider caching generated contract PDFs in storage to avoid re-rendering HTML repeatedly. |
| **DEBT-LATER-002** | Legacy flat columns on `users` and `landlord_profiles` | `users`, `landlord_profiles` | Document legacy fields (`payment_gcash_qr_path`, etc.) vs new `landlord_documents` table relations. |

---

### Category: DO NOT TOUCH WITHOUT PLAN (High Blast Radius)
| Item ID | Description | Justification |
| :--- | :--- | :--- |
| **CRIT-CORE-001** | Apache Rewrite Rules in `.htaccess` & `public/.htaccess` | Critical for Hostinger shared-hosting routing. Any syntax error produces immediate HTTP 500 across the entire production site. |
| **CRIT-CORE-002** | Existing Database Migrations (0001 through 2026_09_*) | 73 migrations have already run in production. Changing historical migration files will cause migration checksum/status mismatches on deploy. Always use new migrations. |
| **CRIT-CORE-003** | Storage Fallback Chain in `FileStorageService` | Handles legacy paths across local disk, Supabase, and Cloudflare R2. Modifying order risks breaking image/document links. |
