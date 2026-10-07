# Protocol: Authorization & IDOR Defense

This protocol guides how authorization checks must be implemented and verified across all controllers and policies.

---

## 1. Dual-Layer Defense Requirement

Every state-changing or data-reading endpoint must enforce a double barrier:

1. **Layer 1 — Policy Evaluation:**
   ```php
   $this->authorize('update', $property);
   ```
2. **Layer 2 — Scoped Eloquent Query:**
   ```php
   $property = Property::where('id', $id)
       ->where('landlord_id', Auth::id())
       ->firstOrFail();
   ```

---

## 2. Policy Matrix Standards

- **`before(User $user)`:** Only return `true` for administrators when administrative override is intentional.
- **Resource Ownership:**
  - Student resources: Match `$model->student_id === $user->id` or `$model->user_id === $user->id`.
  - Landlord resources: Match `$model->room->property->landlord_id === $user->id` or `$model->landlord_id === $user->id`.
  - Shared resources (Messages, Contracts): Verify the user is either the initiating student or the assigned landlord.
