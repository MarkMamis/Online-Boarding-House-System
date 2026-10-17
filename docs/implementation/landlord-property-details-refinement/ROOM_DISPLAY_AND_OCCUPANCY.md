# Room Display & Occupancy Semantics

**Document Reference:** `docs/implementation/landlord-property-details-refinement/ROOM_DISPLAY_AND_OCCUPANCY.md`

---

## 1. Occupancy Semantics & Math Distinction

In OBHS boarding houses, student rooms may operate under single occupancy (`per_room`), shared bed occupancy (`per_bed`), or flexible hybrid models. The legacy dashboard cards conflated room counts with bed slots.

### Clear Semantic Separation
1. **Total Rooms:** Total physical rooms in this property (`$property->rooms->count()`).
2. **Total Bed Capacity:** Aggregate physical beds across all rooms (`$property->rooms->sum('capacity')`).
3. **Available Rooms:** Rooms with status `available` that have at least 1 bookable bed slot (`$room->getAvailableSlots() > 0`).
4. **Available Bed Slots:** Total remaining free beds available for booking (`$property->rooms->sum(fn($r) => $r->getAvailableSlots())`).
5. **Occupied Rooms:** Rooms currently harboring active approved tenant stays.
6. **Occupied Bed Slots:** Total beds occupied by verified students (`totalBedCapacity - availableBedSlots`).
7. **Maintenance Rooms:** Rooms undergoing repairs or maintenance, where available slots are temporarily 0.

### Rendered Summary Strip Format
```blade
2 rooms (6 bed slots) · 2 available (5 beds open) · 1 occupied (1 bed filled)
```
- If a property only has single-bed rooms (`totalBedCapacity === totalRooms`), the secondary bed slot clarification automatically hides to prevent redundancy.
- If maintenance rooms exist, an amber status tag is appended: `· 1 maintenance`.

---

## 2. Room Card Anatomy

Each room card within the primary `Rooms` section contains:
1. **Media Object / Thumbnail:**
   - Displays `$room->image_path ?: optional($room->roomImages->first())->image_path`.
   - Fallback icon `bi-door-closed` if no photo uploaded.
   - Fixed 100px x 75px on desktop/tablet, responsive full-width banner (140px) on mobile (< 576px).
2. **Identity & Status:**
   - `Room {number}` with status pill badge (`Available`, `Occupied`, `Maintenance`).
3. **Rate & Pricing Model:**
   - Accurate pricing unit: `₱{amount} / mo`, `₱{amount} / bed / mo`, or `₱{amount} / room / mo`.
4. **Capacity & Slots:**
   - `Capacity: {capacity} beds`, `Slots: {available}/{capacity}`.
5. **Tenant Attribution:**
   - Multi-tenant shared rooms: `Tenants ({count}): {Name 1}, {Name 2}` (strictly preserves regression assertion).
   - Single tenant: `Tenant: {Name} (ID hidden)`.
   - Vacant: `Tenant: No tenant`.
6. **Contextual Action Bar:**
   - `Edit` (`btn-outline-brand`)
   - `Duplicate` (`btn-outline-secondary` with `bi-copy`)
   - `Delete` (`btn-outline-danger` with JavaScript confirmation dialog)
