# Reports Module Audit (`/admin/reports`)

## 1. Concept and Purpose
The current **Reports** module represents **Student Incident & Complaint Oversight**:
- Submitted by active/verified student boarders regarding property issues, landlord disputes, or safety concerns.
- Automatically classified with AI priority heuristics (`minor`, `moderate`, `critical`) using `ReportPriorityClassifier`.
- Allows the administrator to review the complaint, communicate an administrative resolution response, and update lifecycle state.

---

## 2. Report Workflow & Status Progression
- **Submission Gate:** Only verified student boarders with an approved booking and completed tenant onboarding can submit reports.
- **Statuses Supported in Database/Controller:**
  1. `pending`: Initial status upon student submission.
  2. `in_progress`: Administrator is currently investigating or has engaged with the student/landlord.
  3. `resolved`: Administrator has addressed the issue and provided an `admin_response`. Sets `resolved_at = now()`.
- **Response Notification:** When an admin provides an `admin_response`, `response_read` is marked `false` so the student receives an alert in their portal.
- **Policy Enforcement:** Managed via `App\Policies\ReportPolicy`. Admin can view/update all reports; students can only view their own and mark responses as read.

---

## 3. Data Integrity & Relationship Audit
- `Report` model links directly to `User` via `user_id`.
- **Gaps Identified:**
  1. **No Property Association:** Reports do not currently store a direct `property_id` foreign key. To know which property is involved, administrators must inspect the text or look up the student's active booking.
  2. **No Attachment Support:** Reports only accept `title` and `description` text fields; there is no image or document attachment handling yet.
  3. **No Reopening Workflow:** Once resolved, there is no formal "reopen with comment" status, although the admin can change the status back to `in_progress`.

---

## 4. Architectural Placement Evaluation: Insights vs. Management
- **Current Location:** `Insights -> Reports` (`/admin/reports`).
- **Evaluation:**
  - The module is an **active decision and incident resolution queue** rather than an institutional analytical report.
  - School administrators must take action (investigate, reply, resolve) just like in Student Verification or Landlord Accreditation.
- **Recommendation:**
  - Long-term: Relocate or rename under **Management -> Incident Reports** (`/admin/incident-reports`), while keeping aggregate statistical reporting inside **Insights -> Analytics**.
  - Current state: Retain existing route and navigation to avoid breaking live production links until scheduled IA realignment.
