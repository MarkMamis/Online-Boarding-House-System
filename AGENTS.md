# OBHS Development & Architecture Directive

You are working on a **LIVE production application**: the **Online Boarding House System (OBHS)** for Mindoro State University (MinSU).

---

## 1. Production Context & Hosting
- **Production Host:** Hostinger Shared Web Hosting (Apache / MySQL 8.x / PHP 8.2+).
- **Invariants:** No root server access, no Supervisor/systemd, no Redis, dual development parity (Local + Hostinger).
- **Never Commit Secrets:** `.env` and production credentials must never be exposed or committed.

---

## 2. Mandatory Workflow for Coding Agents

Before touching any code:

1. **Read Harness Guide:** Inspect [`.agents/README.md`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/.agents/README.md).
2. **Identify Feature ID:** Match the task to [`docs/harness/FEATURE_REGISTRY.md`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/docs/harness/FEATURE_REGISTRY.md).
3. **Read Owning Agent Skill:** Read `.agents/agents/<owner-agent>/SKILL.md`.
4. **Consult Domain Reference:** Read `.agents/features/<domain>.md`.
5. **Enforce Invariants & Role Access:**
   - Review [`docs/harness/BUSINESS_INVARIANTS.md`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/docs/harness/BUSINESS_INVARIANTS.md).
   - Review [`docs/harness/ROLE_ACCESS_MATRIX.md`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/docs/harness/ROLE_ACCESS_MATRIX.md).
6. **Follow Strict Edit Protocol:** Adhere to [`.agents/protocols/strict-edit.md`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/.agents/protocols/strict-edit.md).
7. **Strict SQL:** NEVER disable `ONLY_FULL_GROUP_BY` or MySQL strict mode to resolve query errors.
8. **Server-Side Authorization:** NEVER rely on Blade UI hiding or client-supplied user IDs.
9. **Write Regression Tests:** Add automated tests in `tests/Feature/` or `tests/Unit/` using expressive naming.
10. **Verify & Handoff:** Run `php artisan test` and create a handoff in `.agents/handoffs/`.
