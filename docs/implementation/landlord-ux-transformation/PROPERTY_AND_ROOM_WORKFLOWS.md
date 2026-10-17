# Property & Room Workflows

**Document Reference:** `docs/implementation/landlord-ux-transformation/PROPERTY_AND_ROOM_WORKFLOWS.md`  
**Modified Files:** `app/Http/Controllers/PropertyController.php`, `app/Http/Controllers/RoomController.php`, `resources/views/landlord/properties/create.blade.php`, `resources/views/landlord/properties/show.blade.php`, `resources/views/landlord/rooms/index.blade.php`, `routes/web.php`

---

## 1. Problem Analysis

1. **Mandatory Room Creation in Property Wizard:** Landlords were forced to define an initial room in Step 4 when creating a property. If the landlord did not know all room dimensions or details yet, they could not create their property record.
2. **Zero Draft Persistence:** Incomplete properties could not be saved as drafts. Navigating away discarded all entered address, rule, and coordinate data.
3. **High Room Creation Friction:** In standard boarding houses with 10–20 identical rooms (e.g. 2-bed shared rooms at ₱2,500/month), the landlord had to manually type every room number, capacity, price, and amenities list 20 times.

---

## 2. Solutions Implemented

### 2.1 Decoupled Room Creation & Draft Saving

- In `resources/views/landlord/properties/create.blade.php`:
  - Step 4 room input fields (`initial_room_number`, `initial_capacity`, `initial_price`, `initial_status`) were made optional.
  - Added a prominent **"Save Draft"** button (`name="action"` `value="draft"` or `save_draft=1`).
  - Added frontend validation handling allowing landlords to save drafts validating only basic property identity (Step 1).
- In `app/Http/Controllers/PropertyController.php`:
  - Made room validation rules nullable when saving drafts or when rooms are added later.
  - If `$isDraft` is true, property is persisted with `approval_status = 'draft'` and `status = 'pending'`, redirecting directly to the property show screen with a success alert guiding the user to complete room setup when ready.

### 2.2 1-Click Room Duplication

- Route: `POST /landlord/properties/{property}/rooms/{room}/duplicate` (`landlord.properties.rooms.duplicate`).
- Controller Implementation (`RoomController@duplicate`):
  - Authorizes that the requesting landlord strictly owns the parent property via `getOwnedProperty()`.
  - Computes collision-free next room numbers (e.g. `Room 101 (Copy)`, `Room 101 (Copy 2)`).
  - Copies all core configuration: `room_type`, `capacity`, `price`, `description`, `inclusions`, and amenity associations.
  - Initializes state safely: `slots_available = capacity`, `status = 'available'`.
  - **Strict Security Isolation:** Strictly omits bookings, tenant relations, leave requests, payments, and ratings.
  - Redirects the landlord directly to `landlord.properties.rooms.edit` so they can adjust the room number or price with minimal effort.
- UI Integration:
  - Added duplicate buttons in `resources/views/landlord/properties/show.blade.php` and `resources/views/landlord/rooms/index.blade.php`.
