# Protocol: Automated Testing & Verification

This protocol governs test creation, test naming, and test verification standards for OBHS.

---

## 1. Test Naming Convention

All test methods must use snake_case and expressively describe the actor, behavior, and expected outcome:

- `student_cannot_view_another_students_booking()`
- `landlord_cannot_update_property_owned_by_another_landlord()`
- `student_cannot_access_another_students_onboarding_documents()`
- `landlord_can_approve_booking_for_owned_property()`
- `landlord_cannot_approve_booking_for_unowned_property()`
- `unverified_user_cannot_access_verified_routes()`
- `student_setup_must_be_completed_before_student_portal_access()`
- `ai_tool_cannot_read_data_outside_authenticated_users_scope()`

---

## 2. Test Execution Command

Run the test suite locally using:
```bash
php artisan test
```
To run a specific test suite:
```bash
php artisan test --filter=AuthRoleAccessTest
```
