# Protocol: Audit Before Edit & Risk Classification

Every code modification must begin with an audit before making any edits.

---

## 1. Change Risk Classification

Every requested modification must be classified by risk:

| Risk Level | Description & Scope | Required Reviewers | Mandatory Verification |
| :--- | :--- | :--- | :--- |
| **`LOW`** | Isolated Blade copy, static styles, documentation. | Domain Owner | Visual check / Pint |
| **`MEDIUM`** | Listing views, non-sensitive filters, non-state-changing helpers. | Domain Owner + QA | Targeted feature tests |
| **`HIGH`** | Bookings, onboarding transitions, documents, messaging, notifications, analytics SQL. | Domain Owner + `obhs-booking-tenancy` / `obhs-database` + `obhs-qa` | Unit & Feature tests |
| **`CRITICAL`** | Auth, middleware, policies, payment logic, document streaming, production deployment, migrations, AI tools. | Domain Owner + `obhs-security-auth` + `obhs-hostinger` + `obhs-qa` | Full test suite + security audit |

---

## 2. Change Plan Pre-Execution Checklist

Before editing any code, answer these questions internally:
1. **Feature ID:** Which feature ID(s) does this change affect?
2. **Roles Impacted:** Which roles are affected?
3. **Owning Agent:** Who is the primary agent responsible?
4. **Current Implementation:** How does the existing code behave?
5. **Invariants:** What business invariants must NOT be broken?
6. **Authorization Check:** Does this change alter or touch user permissions?
7. **Database Impact:** Does this require a new migration? Is strict SQL preserved?
8. **Regression Tests:** What automated test will prove the change is safe?
