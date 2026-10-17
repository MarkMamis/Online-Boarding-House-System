# Security & Privacy Safeguards

## 1. Compliance with Data Privacy & Educational Records
OBHS processes personal and educational records of Mindoro State University students. The system strictly adheres to the Philippine Data Privacy Act of 2012 (RA 10173) and MinSU institutional data governance policies.

---

## 2. Anti-Enumeration & Zero-Data-Leakage Measures
1. **Generic Conflict Responses:**
   - When a Student ID is taken, the API response is strictly:
     ```json
     {
       "available": false,
       "valid": true,
       "status": "taken",
       "message": "We couldn't continue with this Student ID.",
       "recovery_url": "https://mcc-boardinghouse.online/forgot-password"
     }
     ```
   - Zero attributes of the existing account (name, email, phone number, birthdate, user ID) are exposed in the JSON payload or DOM.
2. **Generic Password Reset Responses:**
   - Regardless of whether an email address exists in `users`, the password recovery endpoint replies:
     > *"If an eligible account matches that email, we have sent a password reset link."*
   - Prevents attackers from verifying student email existence.
3. **Masked Administrative Previews:**
   - When administrators review competing claims, the existing account's email is masked (e.g., `j***@minsu.edu.ph`) to avoid unnecessary exposure across operator sessions.

---

## 3. Account Takeover & Integrity Protections
1. **Knowledge != Ownership:**
   - Possessing a student's public Student ID, name, program, or birthdate grants **no direct account access**.
2. **No Automatic Account Reassignment:**
   - An assistance request submission **never** releases or reassigns `users.student_id`.
   - Admin approval **never** automatically logs the claimant into the existing account or exposes existing credentials.
3. **Private Document Storage:**
   - Uploaded identity evidence is stored in private storage (`identity_recoveries/`).
   - Served strictly through authorized routes (`FileController`) verifying admin privilege.
   - Raw documents are never attached to public email notifications or stored in world-readable web roots.
4. **Rate Limiting:**
   - Student ID check: `throttle:60,1` (max 60 lookups/min).
   - Recovery request submission: `throttle:10,1` (max 10 submissions/min).
   - Password reset request: `throttle:6,1` (max 6 requests/min).
