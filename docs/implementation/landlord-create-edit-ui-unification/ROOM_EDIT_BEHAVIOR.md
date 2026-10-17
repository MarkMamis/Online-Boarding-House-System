# Room Edit Behavior

## 1. Safety & Occupancy Preservation
- Editing an existing room never alters existing contracts, active bookings, or historical payment snapshots.
- Capacity constraints preserve existing tenant allocations.
- Availability status (`available`, `maintenance`, `occupied`) remains synchronized with `syncAvailabilitySnapshot()`.

## 2. Pricing Architecture
- Supports all three canonical pricing modes:
  - `per_bed`: Billed per occupant bedslot
  - `per_room`: Fixed monthly charge for private whole-room lease
  - `hybrid`: Dual support for per-bed slots and whole-room rate
- Prepopulates `effectivePricePerBed()` and `effectivePricePerRoom()` without conversion loss.

## 3. Photo Management
- Cover photo preview retains existing image without forcing replacement.
- Existing detail photos can be relabeled via `existing_labels[{id}]` or flagged for authorized removal via `delete_detail_images[]`.
- New detail photos can be added simultaneously via `detail_images[]`.
