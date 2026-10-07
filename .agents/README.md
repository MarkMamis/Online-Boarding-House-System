# OBHS Development Harness & Governance System

Welcome to the **Online Boarding House System (OBHS)** Agent Harness.

This harness enforces disciplined, secure, and regression-free software engineering on a **live production application** hosted on Hostinger Shared Hosting.

---

## 1. Golden Rules of Development

1. **Production Parity:** Production is live on Hostinger Shared Hosting. Local development and Hostinger production must remain 100% compatible.
2. **Audit Before Edit:** Never make speculative changes. Identify the Feature ID, role boundaries, and owning agent before modifying code.
3. **Strict MySQL Compatibility:** Never disable `ONLY_FULL_GROUP_BY` or strict mode. All queries must be fully strict-SQL compliant.
4. **Server-Side Authorization:** Never rely on Blade UI hiding or client-supplied IDs (`user_id`, `student_id`, `landlord_id`). Enforce permissions in controllers/policies.
5. **No Secret Commits:** Never commit `.env` or production credentials.

---

## 2. Directory Structure

```
.agents/
├── README.md               # This guide
├── AGENTS.md               # Master agent index & dispatch rules
├── registry.md             # Agent capability & domain registry
├── protocols/              # Mandatory operational protocols
│   ├── audit-before-edit.md
│   ├── strict-edit.md
│   ├── authorization.md
│   ├── database-change.md
│   ├── migration.md
│   ├── security.md
│   ├── testing.md
│   ├── deployment.md
│   ├── handoff.md
│   └── incident.md
├── agents/                 # 15 Specialized Domain Agent Skills
│   ├── obhs-architect/SKILL.md
│   ├── obhs-security-auth/SKILL.md
│   ├── obhs-student/SKILL.md
│   ├── obhs-landlord/SKILL.md
│   ├── obhs-admin/SKILL.md
│   ├── obhs-marketplace/SKILL.md
│   ├── obhs-booking-tenancy/SKILL.md
│   ├── obhs-payments/SKILL.md
│   ├── obhs-documents-storage/SKILL.md
│   ├── obhs-messaging-notifications/SKILL.md
│   ├── obhs-ai/SKILL.md
│   ├── obhs-database/SKILL.md
│   ├── obhs-hostinger/SKILL.md
│   ├── obhs-ui/SKILL.md
│   └── obhs-qa/SKILL.md
├── handoffs/               # Context handoffs for non-trivial tasks
│   └── README.md
└── features/               # Deep domain references
    ├── README.md
    ├── auth.md
    ├── student.md
    ├── landlord.md
    ├── admin.md
    ├── marketplace.md
    ├── bookings.md
    ├── tenancy.md
    ├── payments.md
    ├── documents.md
    ├── messaging.md
    ├── notifications.md
    ├── ai.md
    └── deployment.md
```

---

## 3. How to Execute Work

When assigned a task:
1. Identify the relevant **Feature ID** from [`docs/harness/FEATURE_REGISTRY.md`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/docs/harness/FEATURE_REGISTRY.md).
2. Read the corresponding **Domain Skill** in `.agents/agents/<agent-name>/SKILL.md`.
3. Check [`docs/harness/BUSINESS_INVARIANTS.md`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/docs/harness/BUSINESS_INVARIANTS.md) and [`docs/harness/ROLE_ACCESS_MATRIX.md`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/docs/harness/ROLE_ACCESS_MATRIX.md).
4. Follow [`.agents/protocols/strict-edit.md`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/.agents/protocols/strict-edit.md).
5. Add/update targeted automated regression tests.
6. Verify locally (`php artisan test`).
7. Create/update a handoff file in `.agents/handoffs/` for meaningful tasks.
