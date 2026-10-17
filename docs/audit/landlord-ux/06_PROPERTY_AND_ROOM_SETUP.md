# OBHS Landlord Portal: Property & Room Setup Audit

**Document ID:** `06_PROPERTY_AND_ROOM_SETUP.md`  
**Date:** October 18, 2026  
**Auditors:** Property Management Specialist & Senior Laravel Architect  
**Focus:** Onboarding Friction, Multi-Step Wizards, Draft Persistence, Room Duplication, and Capacity Contracts.

---

## 1. Deep Audit: Current Property Setup (`properties/create.blade.php` & `PropertyController@store`)

Creating a boarding house in OBHS is currently one of the highest-friction workflows in the entire platform. At 1,109 lines of Blade (`49.7 KB`), the creation screen forces a landlord through four rigid steps:

### The 4 Existing Setup Steps:
1. **Step 1: Property Basics**
   - Property Name (Required)
   - Address with Barangay suggest (Required)
   - Property Cover Photo (Max 5MB)
   - Long Description (Textarea)
2. **Step 2: Inclusions & House Rules**
   - 3 Categories of standard building amenities (`config('property_amenities.categories')`).
   - Dynamic custom inclusion input chips with add/remove buttons.
   - 3 separate multi-line textareas for House Rules (Curfew, Visitors, Noise & Conduct).
3. **Step 3: Pin Location on Leaflet Map**
   - Interactive Leaflet.js map requiring clicking or entering exact decimal latitude/longitude.
   - Geocoding API integration (`GeocodingService`).
4. **Step 4: Mandatory First Room Creation**
   - Room Number, Capacity, Price (₱), and Status (`available`, `occupied`, `maintenance`).
   - Note: In `PropertyController@store:471-476`, all four room fields are strictly required (`Rule::requiredIf(!$isDashboardQuickCreate)`).

### Fatal Defect 1: Zero Draft Support (`P1 Usability Blocker`)
- The `properties` database table schema contains only: `name`, `address`, `latitude`, `longitude`, `description`, `image_path`, `building_inclusions`, `approval_status`, `house_rules`, etc.
- There is **no `draft` status** and **no partial data persistence**.
- If a landlord completes Steps 1 through 3 but doesn't have their room pricing finalized, or navigates away to check a photo on their phone, **100% of the entered address, amenities, and house rules are erased**.

### Fatal Defect 2: Inconsistent Controller Contract
- When submitted via `#addPropertyModal` (`from_dashboard=1`), `initial_room_number` is **optional**.
- When submitted via `/landlord/properties/create`, `initial_room_number` is **mandatory**.
- This creates two conflicting mental models and developer friction.

---

## 2. Deep Audit: Current Room Setup (`rooms/create.blade.php` & `RoomController@store`)

Adding a room to an existing property is similarly over-engineered. At 1,903 lines of Blade (`74.4 KB`), `rooms/create.blade.php` presents another 4-step wizard:
- **Step 1:** Room Number, Capacity, Initial Status, Inclusions checklist with 11 custom chips.
- **Step 2:** Main Room Photo + dynamic multi-image upload with custom detail labels.
- **Step 3:** Pricing Model (`per_room`, `per_bed`, `hybrid`), Per-room monthly rate, Per-bed monthly rate, Requires Advance Payment toggle.
- **Step 4:** Final Review and Submit.

### Fatal Defect 3: The "10-Room Repetition" Nightmare
In a typical Philippine boarding house near a university campus:
- A landlord builds 6 to 10 nearly identical rooms (e.g., Room 1 to Room 8: each 2-capacity, ₱2,500/month per bed, including WiFi, Water, and Fan).
- In the current system, the landlord must click through **4 steps × 10 rooms = 40 wizard steps**, typing the same inclusions, capacity, and pricing ten separate times.
- There is **zero room duplication** or template reuse in the codebase.

---

## 3. Capacity & Pricing Mathematical Contracts

The room occupancy model in OBHS supports three pricing modes (`Room.php:39-42`):
1. `per_room`: Entire room rented as an exclusive unit. Capacity represents total occupants allowed, but booking is exclusive (`isExclusiveOccupancy = true`).
2. `per_bed`: Bedspace model. Multiple independent students occupy separate bed slots up to `capacity`. `slots_available` decrements per approved booking.
3. `hybrid`: Room can be booked either as exclusive solo or per bed.

### Crucial Invariants to Preserve:
- Capacity math: `0 <= slots_available <= capacity`.
- Price precision: Monetary figures must preserve `decimal(10,2)` formatting.
- `slots_available` must never be manually overwritten when active bookings exist.

---

## 4. Recommended Simplified Setup Architecture

### A. Progressive Property Setup (Decoupled from Rooms)
1. **Decouple Room Creation:** Property setup must strictly register the property itself. Rooms should be created in the Property workspace.
2. **Simplified 3-Card Guided Flow:**
   - **Card 1: Basic Information:** Name, Barangay/Address, Cover Photo.
   - **Card 2: Amenities & House Rules:** Quick checkboxes for standard amenities (WiFi, Water, Kitchen, Study Table) + simple house rules textarea.
   - **Card 3: Pin Location:** Map with "Use Current Location" button and auto-geocoding fallback.
   - **Save & Submit for Review:** Automatically sets `approval_status = 'pending'`.
3. **Session-Based Draft Persistence:** If the user leaves the page before submitting, store partial form values in `session('property_setup_draft')` or `localStorage` to prevent lost data on mobile reloads.

### B. 1-Click "Duplicate Room" Action (`High-Value Feature`)
Add a non-destructive room duplication endpoint:
- **Route:** `POST /landlord/properties/{property}/rooms/{room}/duplicate`
- **Safe Fields Copied:** `capacity`, `pricing_model`, `price`, `price_per_room`, `price_per_bed`, `inclusions`, `requires_advance_payment`.
- **Fields Auto-Incremented/Reset:**
  - `room_number`: Automatically suggests the next sequential number (e.g. "Room 101" -> "Room 102").
  - `status`: Always resets to `'available'`.
  - `slots_available`: Always resets to full `capacity`.
- **Strictly Excluded from Duplication:**
  - ❌ Current tenants / bookings.
  - ❌ Room photos (unless landlord explicitly opts to reuse).
  - ❌ Historical payment ledgers.
  - ❌ Maintenance history.

This single feature reduces room configuration time for a 10-room boarding house from **25 minutes of repetitive typing to under 90 seconds**.
