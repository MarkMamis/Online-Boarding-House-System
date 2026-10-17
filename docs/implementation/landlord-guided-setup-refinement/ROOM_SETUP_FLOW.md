# Room Setup Flow Specification

## 1. Overview
The Landlord Room Creation flow (`resources/views/landlord/rooms/create.blade.php`) has been refactored from an administrative multi-card view with a large 4-step vertical stepper into a calm, guided question experience.

## 2. Contextual Property Header
- **Removed:** Three large separate informational boxes (Property Name, Default Status, Property Address) and redundant breadcrumbs.
- **Added:** A clean, compact contextual header directly inside the form card:
  - Sub-label: `ADDING ROOM TO`
  - Property Name: `{{ $property->name }}` (high contrast, bold)
  - Address: `{{ $property->address }}` (muted text with map pin icon)

## 3. Logical Question Panels
The room creation contains 5 progressive question panels:

### Panel 0: Room Details
- **Category:** `ROOM DETAILS`
- **Heading:** "Tell us about this room."
- **Subtitle:** "Identify the room name or number and designate maximum student capacity."
- **Fields:**
  - Room Name / Number (`room_number`, e.g., "Room 101", "Deluxe Double")
  - Maximum Capacity (`capacity`, numeric input with helper hint)
  - Initial Room Status (`status`, defaults to `available`)
- **Footer Actions:** `Exit` | `Continue`

### Panel 1: Rent & Pricing
- **Category:** `RENT & PRICING`
- **Heading:** "Set the monthly rent."
- **Subtitle:** "Choose how rent is calculated and specify billing requirements."
- **Fields:**
  - Pricing Model Radio Cards (`pricing_model`):
    - **Per Bed / Student:** Best for shared dorm-style occupancy.
    - **Entire Room:** Fixed flat monthly rate regardless of occupant count.
    - **Hybrid:** Supports both per-bed and full-room rental options.
  - Dynamic Price Inputs:
    - `price_per_bed` (₱ / bed / month) - visible when per_bed or hybrid is selected.
    - `price_per_room` (₱ / room / month) - visible when per_room or hybrid is selected.
  - Advance Rent Deposit checkbox/toggle (`requires_advance_rent` + `advance_rent_months`).
- **Footer Actions:** `Previous` | `Continue`

### Panel 2: Inclusions
- **Category:** `INCLUDED IN RENT`
- **Heading:** "What's included in the rent?"
- **Subtitle:** "Select utilities and furnishings included in the base rental fee."
- **Fields:**
  - Multi-select Quick Inclusion Chips: Wifi, Water Bill, Electricity Bill, Electric Fan, Aircon, Comfort Room, Kitchen Access, Balcony, Study Table, Kitchen Table, Tambayan.
  - Custom Inclusion Adder: Input field with "Add" button and keyboard Enter support, rendering custom tag pills with remove triggers.
  - Hidden input `inclusions` automatically kept synchronized as a comma-separated list.
- **Footer Actions:** `Previous` | `Continue`

### Panel 3: Room Photos
- **Category:** `ROOM PHOTOS`
- **Heading:** "Showcase your room."
- **Subtitle:** "Upload a clear cover photo and optional additional views."
- **Fields:**
  - Cover Photo Dropzone (`image`): Drag-or-browse area with real-time preview thumbnail, file name pill, file size badge, and remove button.
  - Detail Images (`detail_images[]`): Multi-file selector with thumbnail grid preview.
- **Footer Actions:** `Previous` | `Continue to Review`

### Panel 4: Review & Submit
- **Category:** `FINAL REVIEW`
- **Heading:** "Review your room details."
- **Subtitle:** "Confirm all settings before adding this room to your property."
- **Summary Cards with Direct Edit Buttons:**
  - Room Details (Number, Capacity, Status) → Edit jumps to Panel 0
  - Pricing (Model, Monthly Amount) → Edit jumps to Panel 1
  - Inclusions (List of included amenities/utilities) → Edit jumps to Panel 2
  - Photos (Cover preview thumbnail, additional count) → Edit jumps to Panel 3
- **Footer Actions:** `Previous` | `Add Room` (primary submit)

## 4. Room Duplication Compatibility
- Supports incoming query parameters from room duplication (`request('duplicate_from')` or pre-populated session/old input).
- When duplicating, fields (capacity, pricing model, rent, inclusions) populate seamlessly without validation errors or UI lockups.
