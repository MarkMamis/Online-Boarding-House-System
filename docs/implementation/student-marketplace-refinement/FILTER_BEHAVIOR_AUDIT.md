# Filter Behavior Audit & Corrections
**Second Pass Refinement — OBHS Student & Tenant Marketplace**

This document details the audit of search and filtering mechanisms across the student marketplace, documenting the bugs discovered, exact query corrections, and test verification.

---

### Audit & Bug Resolution Matrix

#### 1. Accommodation Search Scope
- **Displayed Filter:** Search input ("Search by boarding house name, address, or barangay...")
- **Actual Request Parameter:** `q` (and `search`)
- **Legacy Query Implementation:**
  ```php
  $q->whereHas('property', fn ($pq) => $pq->where('address', 'like', "%{$search}%"));
  ```
- **Expected Behavior:** Searching by boarding house name (e.g., "Haven") or room number (e.g., "101") or address matches accommodations.
- **Bug Found:** The legacy query ONLY searched the `address` column of the property table! Searching for a boarding house by name or room number returned zero results.
- **Fix Applied:**
  ```php
  $roomsBaseQuery->when($search !== '', function ($q) use ($search) {
      $q->where(function ($subQuery) use ($search) {
          $subQuery->where('room_number', 'like', "%{$search}%")
              ->orWhereHas('property', function ($pq) use ($search) {
                  $pq->where('name', 'like', "%{$search}%")
                     ->orWhere('address', 'like', "%{$search}%");
              });
      });
  });
  ```
- **Test Evidence:** Verified in `StudentMarketplaceExperienceTest::test_student_rooms_search_by_property_name_and_room_number`.

---

#### 2. Price Filter Bounds Clamping Bug
- **Displayed Filter:** Monthly Budget Range (`Min ₱` – `Max ₱`)
- **Actual Request Parameter:** `min_price`, `max_price`
- **Legacy Query Implementation:**
  ```php
  $minPrice = max((float) $priceBoundsMin, min((float) $priceBoundsMax, $minPrice));
  $maxPrice = max((float) $priceBoundsMin, min((float) $priceBoundsMax, $maxPrice));
  ```
- **Expected Behavior:** If a user specifies `max_price=1500`, and all rooms in the database are priced at ₱2,000+, the catalog should return 0 matching rooms.
- **Bug Found:** `max((float) $priceBoundsMin, ...)` clamped `$maxPrice` up to the minimum room price in the database! For example, if `$priceBoundsMin` was 2000 and user asked for `max_price=1500`, the filter forced `$maxPrice = 2000`, causing ₱2,000 rooms to be returned instead of showing 0 matching rooms!
- **Fix Applied:** Only constrain user-specified prices if explicitly filled in the request; do not clamp explicit user bounds:
  ```php
  $hasUserMinPrice = $request->filled('min_price') && is_numeric($request->query('min_price'));
  $hasUserMaxPrice = $request->filled('max_price') && is_numeric($request->query('max_price'));

  $userMinPrice = $hasUserMinPrice ? (float) $request->query('min_price') : null;
  $userMaxPrice = $hasUserMaxPrice ? (float) $request->query('max_price') : null;

  if ($userMinPrice !== null && $price < $userMinPrice) return false;
  if ($userMaxPrice !== null && $price > $userMaxPrice) return false;
  ```
- **Test Evidence:** Verified in `StudentMarketplaceExperienceTest::test_student_rooms_price_filter_does_not_clamp_out_of_range_requests`.

---

#### 3. Category Chip Parameter Mismatch
- **Displayed Filter:** "Solo Rooms" and "Bed-Spacer (Shared)" on Student Home
- **Legacy Parameters Passed:** `['capacity' => 1]` and `['capacity' => 2]`
- **Expected Behavior:** Clicking "Solo Rooms" filters by whole-room occupancy; clicking "Bed-Spacer" filters by per-bed shared occupancy.
- **Bug Found:** `RoomController@studentIndex` ignores `capacity` and exclusively filters by `occupancy=per_room` or `occupancy=per_bed`. The category chips on Home were completely ignored by the controller.
- **Fix Applied:** Updated category links in `resources/views/student/dashboard.blade.php`:
  - Solo Rooms: `route('student.rooms.index', ['occupancy' => 'per_room'])`
  - Bed-Spacer: `route('student.rooms.index', ['occupancy' => 'per_bed'])`
- **Test Evidence:** Verified in `StudentMarketplaceExperienceTest::test_student_home_renders_marketplace_for_searching_student`.

---

#### 4. Campus Map "View Rooms" Link Disconnect
- **Displayed Filter:** "View Rooms" link on Campus Map property cards
- **Legacy Parameters Passed:** `?property_id=X&amenity=Y`
- **Expected Behavior:** Clicking "View Rooms" filters the room catalog to rooms belonging to that property.
- **Bug Found:** `RoomController@studentIndex` did not check `property_id` in the query string, nor did it support `amenity` (singular, as sent by map).
- **Fix Applied:**
  ```php
  $propertyId = $request->query('property_id');
  if (is_numeric($propertyId) && (int) $propertyId > 0) {
      $roomsBaseQuery->where('property_id', (int) $propertyId);
  }
  $amenitiesInput = $request->query('amenities', $request->query('amenity', []));
  ```
- **Test Evidence:** Verified in `StudentMarketplaceExperienceTest::test_student_rooms_filters_by_property_id`.

---

#### 5. Reset Behavior
- **Expected Behavior:** Clicking "Reset all filters" or "Clear all filters" clears all query parameters and restores the complete catalog view.
- **Fix Applied:** Bound reset actions to `route('student.rooms.index')` without query parameters, clearing active filters cleanly.
