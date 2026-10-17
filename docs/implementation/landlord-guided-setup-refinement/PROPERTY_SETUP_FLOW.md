# Property Setup Flow Specification

## 1. Overview
The Landlord Property Creation flow (`resources/views/landlord/properties/create.blade.php`) has been restructured from a rigid, vertical 4-step administrative wizard into a fluid, guided question experience without visual steppers.

## 2. Removal of the Visual Stepper
- **Removed Elements:**
  - `step-timeline` vertical rail.
  - Step counter indicators ("Step 1 of 4", "STEP 1").
  - "4 — First Room" panel, which was legacy and contradicted the decoupled property-draft architecture.
- **Replacement:** Minimal forward/backward navigation (`Previous`, `Continue`) with contextual category labels and an editable Review screen.

## 3. Logical Question Panels
The property setup contains 4 progressive disclosure panels:

### Panel 0: Property Basics
- **Category:** `PROPERTY BASICS`
- **Heading:** "Tell us about your boarding house."
- **Subtitle:** "Provide the primary identity and description of your property."
- **Fields:**
  - Boarding House Name (`name`)
  - Address (`address`) with integrated PSGC search/autocomplete dropdown
  - Description (`description`)
  - Main Cover Photo upload dropzone with preview, filename pill, and remove button (`cover_image`)
- **Footer Actions:** `Exit` | `Save Draft` | `Continue`

### Panel 1: Property Details
- **Category:** `AMENITIES & HOUSE RULES`
- **Heading:** "What amenities and house rules apply?"
- **Subtitle:** "Select standard features and house guidelines for boarding students."
- **Fields:**
  - Amenities categorized by group (Essential, Study & Living, Security, Kitchen & Dining) with multi-select chips (`amenities[]`)
  - Standard house rule suggestion pills with custom house rule input/textarea (`house_rules[]` / `house_rules`)
- **Footer Actions:** `Previous` | `Save Draft` | `Continue`

### Panel 2: Location & Map
- **Category:** `LOCATION ON MAP`
- **Heading:** "Pinpoint your boarding house location."
- **Subtitle:** "Drag the pin or click on the map to set your exact coordinates."
- **Fields:**
  - Leaflet Interactive Map (with automatic `map.invalidateSize()` triggered upon panel transition)
  - Geolocation helper button ("Use My Location")
  - Latitude (`latitude`) and Longitude (`longitude`) numeric inputs with validation
- **Footer Actions:** `Previous` | `Save Draft` | `Continue to Review`

### Panel 3: Review & Submit
- **Category:** `FINAL REVIEW`
- **Heading:** "Review your boarding house."
- **Subtitle:** "Double-check the information before submitting for administrator review."
- **Summary Cards with Direct Edit Buttons:**
  - Basic Information (Name, Address, Description) → Edit jumps to Panel 0
  - Cover Photo (Image thumbnail or placeholder) → Edit jumps to Panel 0
  - Amenities & Rules (Selected amenities, rules list) → Edit jumps to Panel 1
  - Location (Map pin coordinates) → Edit jumps to Panel 2
- **Footer Actions:** `Previous` | `Save Draft` | `Submit for Review`

## 4. Draft & First-Room Decoupling Verification
- Property saving supports both `Save Draft` (submits with relaxed validation; property saved with `approval_status = 'draft'`) and `Submit for Review` (`approval_status = 'pending'`).
- The First-Room creation is cleanly decoupled: upon saving, landlords are guided to their property room management (`route('landlord.properties.rooms.create', $property->id)`), eliminating forced room creation during basic property registration.
