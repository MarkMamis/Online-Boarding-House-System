# User Accounts Module Audit (`/admin/users`, `/admin/users/students`, `/admin/users/landlords`)

## 1. Scope and Responsibilities
- **All Users (`/admin/users`):**
  - General directory of registered users across student and landlord roles.
  - Quick role filter and keyword search.
- **Students (`/admin/users/students`):**
  - Student profile directory equipped with MinSU academic catalog cascading filters (College -> Program -> Major).
  - Links to student details (`/admin/users/students/{user}`) and profile editing (`/admin/users/students/{user}/edit`).
- **Landlords (`/admin/users/landlords`):**
  - Landlord account directory displaying boarding house association, current tenants, onboarding counts, and room occupancy.
  - Links to landlord 360-degree profile (`/admin/users/landlords/{user}`) and activation status toggle.

---

## 2. Distinction from Verification / Management Modules
- **Separation Status:**
  - `System -> User Accounts`: Focuses purely on account lifecycle, credentials, and profile records.
  - `Management -> Student Verification`: Dedicated to reviewing uploaded School IDs / CORs and approving booking eligibility.
  - `Management -> Landlord Accreditation`: Dedicated to reviewing municipal business permits, fire safety inspections, and legal operating status.
- **Evaluation:** Separation is clean and functional. Account views provide cross-links to management queues without embedding approval buttons inappropriately.

---

## 3. Account Actions & Privilege Security
1. **Student Account Update (`PUT /admin/users/students/{user}`):**
   - Allows admin to update contact information, academic details, and medical/emergency records.
   - Guarded by `role:admin` middleware and strict email uniqueness validation.
2. **Landlord Account Update (`PUT /admin/users/landlords/{user}`):**
   - Allows admin to update full name, email, contact number, and boarding house name.
3. **Landlord Account Status Toggle (`POST /admin/users/landlords/{user}/status`):**
   - Safely toggles `is_active`. When deactivated, it revokes the user's remember token so subsequent requests force re-authentication.
4. **Privilege Escalation Safeguards:**
   - Account edit controllers do NOT expose the `role` attribute in validation rules or update payloads.
   - Public registration endpoints strictly hardcode or restrict the assigned role (student/landlord).
   - `/register/admin` is gated behind `role:admin` middleware in standard production mode unless explicitly enabled via environment config.
