# OBHS Admin Operations — Audit Matrix

| Page | URL Route | Purpose Clarity | Data Accuracy | Security & Authorization | Workflow Integrity | UX Rating | Performance & Query Safety | Monitoring Value | Risk Level | Recommended Redesign Priority |
| :--- | :--- | :---: | :---: | :---: | :---: | :---: | :---: | :---: | :---: | :---: |
| **Boarding Houses** | `/admin/properties` | 4/5 | 5/5 | 5/5 | 4/5 | 3.5/5 | 5/5 | Critical | Low | **Priority 2** (Refine Monitoring Layout & Map) |
| **Boarded Students** | `/admin/boarding-monitoring/students` | 5/5 | 5/5 | 5/5 | 5/5 | 4.5/5 | 5/5 | Critical | Low | **Priority 5** (Already Refined, Polish Print) |
| **Onboardings** | `/admin/onboardings` | 4/5 | 5/5 | 5/5 | 4/5 | 3.8/5 | 5/5 | High | Low | **Priority 4** (Add Bottleneck Indicators) |
| **Student Approvals** | `/admin/approvals/students` | 5/5 | 5/5 | 5/5 | 5/5 | 3.6/5 | 5/5 | High | Low | **Priority 3** (Modernize Review Card UI) |
| **Landlord Approvals** | `/admin/approvals/landlords` | 3/5 | 4/5 | 5/5 | 3.5/5 | 3.2/5 | 5/5 | High | Medium | **Priority 1** (Clarify Accreditation vs Document Verification) |
| **Document Verification** | `/admin/documents/verification` | 5/5 | 5/5 | 5/5 | 5/5 | 3.7/5 | 5/5 | High | Low | **Priority 1B** (Align with Landlord Accreditation) |
| **Document Monitoring** | `/admin/documents/monitoring` | 5/5 | 5/5 | 5/5 | 5/5 | 4.0/5 | 5/5 | Critical | Low | **Priority 3B** (Batch Alert Actions) |

---

## Detailed Evaluation Criteria Breakdown

### 1. Purpose Clarity
- **5/5:** Unambiguous, single responsibility that any non-technical user immediately understands.
- **3-4/5:** Clear purpose, but minor naming ambiguity or overlap with an adjacent queue.
- **1-2/5:** Confusing, overloaded page mixing disparate responsibilities.

### 2. Data Accuracy
- **5/5:** Authoritative distinct counting, strict MySQL group-by compatibility, exact date-range windowing.
- **3-4/5:** Accurate under typical conditions, but edge cases in date filtering or multiple joins exist.
- **1-2/5:** Flawed metrics, duplicate counted rows, or incorrect status inclusions.

### 3. Security & Authorization
- **5/5:** Strict `auth` + `role:admin`, server-side ownership/role verification for private files, CSRF on all state mutations, no IDOR.
- **3-4/5:** Protected by middleware, but missing fine-grained file streaming authorization.
- **1-2/5:** Direct URL exposure or missing CSRF/permission checks.

### 4. Workflow Integrity
- **5/5:** Complete forward/reverse transitions with rejection reasons, audit timestamps, and non-blocking resubmission.
- **3-4/5:** Functional transitions, but duplicate entry points exist in adjacent menus.
- **1-2/5:** Broken transitions, dead-end statuses, or untraceable mutations.

### 5. UX Rating
- **5/5:** Calm, minimal, institutional aesthetic, intuitive visual hierarchy, clear empty states, responsive.
- **3-4/5:** Usable and organized, but dense tables or legacy Bootstrap form controls present.
- **1-2/5:** Cluttered, overwhelming for non-technical users, weak hierarchy.
