# Protocol: Security Verification & Audit

This protocol defines mandatory security review procedures for all pull requests and code modifications.

---

## 1. Security Checkpoints

Before finalizing any task, verify:
- [ ] **Role Isolation:** Can a student access landlord routes? Can a landlord access student routes?
- [ ] **Ownership Gating:** Can User A modify or view User B's resource?
- [ ] **Mass Assignment:** Are all incoming request attributes explicitly validated and whitelisted in `$fillable`?
- [ ] **SQL Parameterization:** Are all raw query expressions parameterized with `?`?
- [ ] **XSS Escaping:** Are all Blade output expressions using `{{ $var }}`?
- [ ] **Private File Access:** Are sensitive user documents streamed only through authenticated, authorized endpoints?
- [ ] **AI Assistant Boundary:** Are newly created AI tools role-gated in `AiToolRegistry`?
