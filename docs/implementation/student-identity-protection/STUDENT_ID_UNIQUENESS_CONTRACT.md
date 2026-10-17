# Student ID Uniqueness Contract & Canonical Equality Specification

## 1. Authoritative Identity Policy
Under the Mindoro State University (MinSU) identifier policy, each student enrolled at MinSU is assigned an official school-issued Student ID (e.g., `MCC2026-00001` or `CAL2025-10492`).

In the OBHS architecture:
1. **One Authoritative Student Identity = One Unique School-Issued Student ID.**
2. A single physical student must never hold multiple independent active student identities in OBHS.
3. Registering with an alternative personal email, phone number, updated year level, or altered home address **does not** create a second legitimate student identity.

---

## 2. Canonical Identifier Format
MinSU Student IDs follow the standard structure:
- **Format:** `[Campus/Prefix][Year]-[SequenceNumber]` (e.g., `MCC2026-00000`, `CAL2024-12039`).
- **Regular Expression:** `^[A-Za-z0-9\-]{3,50}$`
- **Canonicalization:**
  - Leading and trailing whitespace are trimmed (`trim($studentId)`).
  - Normalization preserves alphanumeric characters and hyphens.
  - Comparative lookups enforce case-insensitive canonical equality (`LOWER(TRIM(student_id)) = LOWER(TRIM(?))`).

---

## 3. Exact Matching vs. Prohibited Comparisons

### Strictly Prohibited:
- Wildcard lookups (`WHERE student_id LIKE 'MCC2024%'`)
- Prefix / Suffix matching
- Substring searches (`ILIKE '%1203%'`)
- Fuzzy string distance / similarity scoring
- Biometric or algorithmic heuristics

### Enforced Exact Equality:
```sql
SELECT id, full_name, email, role FROM users
WHERE id != :current_user_id
  AND student_id IS NOT NULL
  AND (student_id = :student_id OR LOWER(TRIM(student_id)) = LOWER(TRIM(:student_id)))
LIMIT 1;
```

### Match Scenarios:
| Stored Value | Entered Value | Match Result | Reason |
| :--- | :--- | :--- | :--- |
| `MCC2024-12039` | `MCC2024-1203` | **NO MATCH** | Different length and value; partial input is not an exact match. |
| `MCC2024-12039` | `mcc2024-12039` | **EXACT MATCH** | Same canonical sequence under case-insensitive comparison. |
| `MCC2024-12039` | `MCC2024-12039` | **EXACT MATCH** | Identical character sequence. |
| `CAL2025-00100` | `MCC2025-00100` | **NO MATCH** | Different campus prefix. |

---

## 4. Multi-Layer Enforcement

### 1. Client-Side Input Handling
- No AJAX network requests or availability queries are dispatched while the student is typing.
- Availability is queried strictly when the user clicks **Continue** (or on blur if a complete format is present).
- Any edit to the input field immediately invalidates prior verification states and clears cached results.

### 2. Application-Level Controller Validation
- `StudentSetupController::checkStudentId` validates the exact complete format before running any database queries.
- Ownership exemption: If the authenticated student already owns the requested `student_id`, it is immediately returned as available to prevent self-locking.
- Other accounts: Checks for any existing record holding the canonical ID.
- Final onboarding submission (`StudentSetupController::update`) re-validates uniqueness via `Rule::unique('users', 'student_id')->ignore($user->id)`.

### 3. Database Layer Uniqueness
- An additive unique index migration (`2026_10_17_200000_create_identity_recovery_requests_table.php` and schema blueprint) ensures concurrent submissions cannot create duplicate student records even under race conditions.
