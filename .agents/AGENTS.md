# OBHS Master Agent Directive

You are working on the live production codebase of the **Online Boarding House System (OBHS)**.

---

## Master Instructions for AI Agents

Before writing or modifying any code in this repository:

1. **Production State:** This application is live in production on Hostinger Shared Hosting. Never make speculative changes.
2. **Identify Feature ID:** Look up the relevant feature in [`docs/harness/FEATURE_REGISTRY.md`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/docs/harness/FEATURE_REGISTRY.md).
3. **Read the Owning Agent's Skill:** Read `.agents/agents/<owner-agent>/SKILL.md` before making edits.
4. **Consult Domain Specifications:** Read `.agents/features/<domain>.md`.
5. **Enforce Invariants & Role Access:**
   - [`docs/harness/BUSINESS_INVARIANTS.md`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/docs/harness/BUSINESS_INVARIANTS.md)
   - [`docs/harness/ROLE_ACCESS_MATRIX.md`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/docs/harness/ROLE_ACCESS_MATRIX.md)
6. **Strict Edit Protocol:** Follow [`.agents/protocols/strict-edit.md`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/.agents/protocols/strict-edit.md) for all modifications.
7. **Write Tests:** Add regression protection tests in `tests/Feature/` or `tests/Unit/`.
8. **Document Handoff:** For non-trivial changes, record the modification in `.agents/handoffs/`.
