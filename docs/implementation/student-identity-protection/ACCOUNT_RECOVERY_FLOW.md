# Account Recovery & Identity Claim Flow

## 1. Context & User Pathways
When a student inputs a Student ID that conflicts with an existing registered account, OBHS halts further onboarding progression and renders a non-enumerating recovery card:

> **"We couldn't continue with this Student ID."**  
> *"If you've used OBHS before, you can recover your account. If you believe this is a mistake, request assistance."*

The student is offered three actions:
1. **Recover Account:** For students who know or can access their registered email.
2. **Request Assistance:** For students who lost access to their registered email or encounter an identity collision.
3. **Back:** To dismiss the recovery panel and correct a typographical error in the Student ID input.

```
+-------------------------------------------------------------------+
|                        Student Onboarding                         |
|                   [Panel 0: Enter Student ID]                     |
+---------------------------------+---------------------------------+
                                  |
                           Clicks Continue
                                  |
                    +-------------+-------------+
                    |                           |
               No Conflict                   Conflict
                    |                           |
                    v                           v
             Continue Setup            Render Recovery Card:
           (Personal Details)         - [Recover Account]
                                      - [Request Assistance]
                                      - [Back]
```

---

## 2. Channel A: Self-Service Password Reset (Known Email)

### Workflow:
1. Clicking **Recover Account** directs the student to `/forgot-password` (`password.request`).
2. Student submits their registered email address.
3. Controller (`App\Http\Controllers\Auth\PasswordResetController::sendResetLinkEmail`):
   - Throttled at 6 requests per minute per IP.
   - Dispatches a secure, time-limited reset token via Laravel's native password broker.
   - Always returns a generic response regardless of whether the email exists in the database:
     > *"If an eligible account matches that email, we have sent a password reset link."*
   - Prevents account enumeration attacks.
4. Student clicks the secure one-time link in their email:
   - Directs to `/reset-password/{token}` (`password.reset`).
   - Token signature and expiration are verified.
   - Password must meet minimum entropy criteria (min 8 characters).
5. Upon successful reset, the student logs in and resumes their existing account without duplicating their university identity.

---

## 3. Channel B: Lost Registered Email (Assistance Request)

### Security Imperative:
- A user claiming an existing Student ID **must not** automatically receive access to that account.
- Knowing a Student ID, legal name, or birthdate is **insufficient** proof of ownership.
- The system must never reassign the account or transfer the Student ID without authorized administrative verification.

### Workflow:
1. Clicking **Request Assistance** opens the inline assistance form directly below the conflict card.
2. The form collects minimal essential review data:
   - **Claimed Student ID:** Automatically prefilled and read-only.
   - **Contact Email:** Active email where the applicant can be reached.
   - **Contact Phone:** Optional mobile number for verification correspondence.
   - **Reason for Recovery:** Textual justification (e.g. lost SIM/email, changed university email).
   - **Supporting Document:** Optional upload of official MinSU Student ID or Certificate of Registration (COR/COE) (JPG, PNG, WEBP, PDF up to 4MB).
3. The request is submitted via AJAX to `POST /student/setup/recovery-request` (rate limited at 10 requests per minute).
4. File is saved to private storage (`identity_recoveries/{user_id}/`).
5. An `IdentityRecoveryRequest` record is created in `pending` state with a reference number.
6. The user is presented with a success notification:
   > *"Your assistance request has been submitted for administrator review."*
7. No credentials are reset, and the existing account owner's identity is completely protected until an administrator conducts an authorized review.
