# Shared Form Components & Partial Breakdown

## 1. Property Guided Form (`properties/partials/guided-form.blade.php`)
- **Panel 0: Property Basics**
  - Boarding House Name (`name`)
  - Address (`address`) with PSGC barangay autocomplete
  - Description (`description`)
  - Cover Photo with live preview and replace action (`image`)
- **Panel 1: Amenities & House Rules**
  - Categorized standard amenities chips (`building_inclusions[]`)
  - Custom amenity badges with input tag adder
  - Standard house rule toggles & custom rule adder
- **Panel 2: Location & Map**
  - Interactive Leaflet pin selector with auto-geocoding fallback
  - Manual latitude/longitude coordinate fields
- **Panel 3: Review Changes / Confirmation**
  - Summary cards for Basics, Amenities & Rules, Location
  - Quick "Edit" jump button returning to specific panel
  - "Save & Return to Review" workflow
  - Final Submit action ("Save Changes" vs "Submit for Review")

## 2. Room Guided Form (`rooms/partials/guided-form.blade.php`)
- **Panel 0: Room Details**
  - Room identifier / number (`room_number`)
  - Capacity / beds (`capacity`)
  - Availability status (`status`: Available, Under Maintenance, Occupied)
- **Panel 1: Rent & Pricing**
  - Pricing model selection cards (`per_bed`, `per_room`, `hybrid`)
  - Dynamic monthly rent fields (`price_per_bed`, `price_per_room`)
  - Advance payment toggle (`requires_advance_payment`)
- **Panel 2: Room Inclusions**
  - Quick inclusion toggle chips (Wifi, Water, Aircon, CR, etc.)
  - Custom inclusion badge manager
- **Panel 3: Room Photos**
  - Cover photo preview & replace zone (`image`)
  - Existing detail photo relabeling (`existing_labels[]`) & delete checkboxes (`delete_detail_images[]`)
  - Additional photo uploads (`detail_images[]`)
- **Panel 4: Review Changes / Confirmation**
  - Summary cards for Room Details, Rent & Pricing, Inclusions, Photos
  - Quick "Edit" jump button per card
  - Final Submit action ("Save Changes" vs "Add Room to Property")
  - Danger Zone (Edit mode only) for room deletion with identifier typing modal
