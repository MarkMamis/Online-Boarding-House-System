# Student Move-in Experience (`STUDENT_MOVE_IN_UX.md`)

## Key Design Improvements
1. **Approved Setup Shell Reuse:**
   - Adopts the `.setup-shell` and question card layout from `student/setup/index.blade.php`.
   - Uses `.question-kicker`, `.question-title`, `.question-subtitle`, and `.btn-brand` primary action buttons.
2. **Removed Clutter:**
   - Eliminated the 4-step circle progress stepper with connecting lines.
   - Removed the 4 large KPI summary boxes that duplicated accommodation data.
   - Replaced multiple nested containers with a single clean accommodation context banner.
3. **Accommodation Context Banner:**
   - Displays property name, room number, lease dates, total due, and a canonical status pill.
   - Remains compact and mobile-responsive across all screen sizes.
4. **Interactive File Selection & Dropzone:**
   - Drag-and-drop or file browser input with dynamic file tags displaying file name and size in MB.
   - Validates supported extensions (PDF, JPG, PNG) and file size (max 5MB).
5. **E-Signature Drawing Pad:**
   - Integrated HTML5 canvas with touch and mouse event listeners.
   - Real-time status indicator ("Signature captured" in green vs "No signature captured yet").
   - Clear & Redraw control.
   - Explicit agreement checkbox: *"I have read, understood, and accept all the terms, policies, and house rules stated in this lease agreement."*
6. **Move-in Payment Submission:**
   - Clear cost breakdown (Security deposit + optional advance rent).
   - Dynamic payment method selector (GCash, Bank Transfer, Cash on Arrival) based on landlord profile settings.
   - Interactive landlord details pane (GCash account name/number + QR code, Bank account name/number/bank name).
   - Live toggling of reference number and payment proof file upload requirements (disabled for Cash).
7. **Digital Tenant Pass:**
   - Renders upon completion with a high-end emerald gradient pass displaying the student's name, assigned boarding house, room, check-in date, and unique Digital Tenant ID (`TID-XXXXXX`).
