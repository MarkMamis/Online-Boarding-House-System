# Protocol: Development Handoff & Session Tracking

For non-trivial tasks (touching business logic, auth, routes, migrations, or storage), create or update a handoff document in `.agents/handoffs/`.

---

## 1. Handoff File Naming

Format: `.agents/handoffs/YYYY-MM-DD-<feature-slug>.md`
Example: `.agents/handoffs/2026-10-08-audit-harness-baseline.md`

---

## 2. Standard Handoff Format

```markdown
# Task: <Task Title>
# Status: COMPLETED | IN_PROGRESS | BLOCKED
# Owner Agent: <Agent Name>
# Supporting Agents: <Agent Names>
# Last Modified: <YYYY-MM-DD HH:MM>
# Git Branch: <Branch Name>
# Starting Commit: <Commit Hash>
# Ending Commit: <Commit Hash>
# Feature IDs: <Feature IDs>

## 1. User Request & Scope
...

## 2. Files Changed
...

## 3. Business Invariants & Authorization Verified
...

## 4. Tests Added / Updated
...

## 5. Verification Commands Executed & Results
...

## 6. Risks & Deferred Technical Debt
...

## 7. Next Recommended Step
...
```
