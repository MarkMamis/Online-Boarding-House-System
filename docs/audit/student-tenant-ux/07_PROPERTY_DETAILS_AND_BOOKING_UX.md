# Property Details, Room Details & Booking Request UX Audit

**Audit Date:** October 18, 2026  
**Scope:** Evaluation of Room Details (`student/rooms/show.blade.php`), Property Details (`student/properties/show.blade.php`), and Booking Request Flow (`student/bookings/create.blade.php`, `BookingController`)  

---

## 1. Room Details Experience (`student/rooms/show.blade.php`)

When a student clicks a listing card, they land on `/student/rooms/{room}`.

### Current Strengths:
- **Clean Breadcrumb Bar:** Provides clear backward navigation (`Browse Rooms / Room Details`).
- **Aspect Ratio Photography:** 16:6 hero banner with thumbnail gallery and full-screen lightbox modal.
- **Direct Landlord Chat:** Contains an in-page inquiry composer (`POST /student/rooms/{room}/inquire`) that sends a message directly to the landlord regarding that specific property.
- **Accurate Slot Calculations:** Displays live remaining bed slots (e.g., *"Available (2 slots)"*).

### UX Friction Points:
1. **Hero Banner Aspect Ratio (16:6 on Desktop):**
   - The hero image is cropped into an extreme panoramic letterbox (`aspect-ratio: 16/6`). Standard vertical or landscape smartphone photos uploaded by landlords are cropped awkwardly through the middle, cutting off bed frames and ceilings.
2. **Dual Identity Confusion (Property vs. Room):**
   - A student viewing the room needs to easily access the overall **Boarding House Profile** (e.g. landlord reputation, total capacity, building permit status, exterior street view). Currently, the property details are scattered across small info chips, and navigating to the parent property page is unintuitive.
3. **Inclusions vs. House Rules Formatting:**
   - House rules (e.g. curfew hours, visitor policies, cooking regulations) are listed in a plain text block without clear iconography or categoric grouping (Curfew, Visitors, Cooking, Utilities).
4. **Booking Call to Action (CTA) Discoverability:**
   - The "Request Room" button is situated in a right-hand sticky sidebar on desktop, but on mobile devices it is pushed far down the page beneath photo galleries and room specs. A floating or sticky mobile bottom bar with price and "Request Room" CTA is missing.

---

## 2. Booking Request Flow (`student/bookings/create.blade.php`)

Clicking "Request Room" navigates to `/student/rooms/{room}/book`.

### The Multi-Step Experience:
1. **Date Picker:** Check-in date and Check-out date.
   - Enforces check-in >= today, check-out > check-in.
2. **Occupancy Mode Selection:**
   - For hybrid rooms: student must choose between "Solo (Whole Room)" or "Shared (Bed-spacer)".
3. **Advance Payment & Notes:**
   - Optional toggle for advance payment and special requests note.
4. **House Rules Acknowledgment & Digital Signature:**
   - Renders house rules.
   - Requires student to open a modal and draw an e-signature on an HTML5 canvas.
5. **Confirmation Modal:**
   - Summary modal before final `POST` submission.

### Critical UX Deficiencies in the Booking Flow:

1. **Redundant Pre-Approval Signature:**
   - Requiring a drawn e-signature simply to submit an inquiry/reservation request creates significant drop-off friction, especially on smartphones where canvas touch drawing is clunky.
   - The formal, legally binding Residential Lease Agreement is signed later during **Tenant Onboarding** (Step 2) once the landlord actually approves the request. Requiring a signature twice confuses students.
2. **Lack of Instant Eligibility Feedback:**
   - `BookingController` enforces strict eligibility invariants:
     - Unverified academic status blocks booking (`school_id_verification_status !== 'approved'`).
     - Existing pending or approved booking blocks new requests.
   - However, if an unverified student clicks "Request Room", they are redirected back with a flash error. The button on the room detail page should disabled with an informative tooltip *before* they click it.
3. **Post-Submission Feedback Gap:**
   - After submitting a request, the student is redirected to `/student/requests` with a simple green toast alert: *"Booking requested successfully."*
   - There is no explanation of what happens next:
     - *"The landlord has been notified and usually responds within 24–48 hours."*
     - *"You will receive an email and notification once reviewed."*
     - *"If approved, you will be invited to complete move-in documents and contract signing."*

---

## 3. The Property Profile Page (`student/properties/show.blade.php`)

Located at `/student/properties/{property}`:

### Current Implementation:
- Displays property name, address, landlord name, and a list of all rooms inside that boarding house.
- Calculates total rooms and available rooms live.

### Deficiencies:
- **Missing Landlord Trust Signals:** Does not show landlord verification status (e.g. MinSU-inspected, business permit verified by Admin, average tenant review rating).
- **No Map Anchor:** Does not provide an immediate mini-map showing exact campus walking directions or coordinates for that specific property.
- **Room List Disconnect:** The rooms inside the property are rendered as an unstyled list rather than engaging room cards.
