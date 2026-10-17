# Property Auto-Save Architecture

## 1. Lifecycle & Invariants
- **No Ghost Drafts:** Merely navigating to `/landlord/properties/create` does not write any record to the database.
- **Meaningful Input Trigger:** A draft is created on the server only when the landlord enters meaningful information (at least 2 characters in `name` or `address`, or uploads a cover photo).
- **Stable Draft Identifier:** When the initial draft is created, the server returns the persistent `property_id`. The client stores this in `#draftPropertyId` (`<input type="hidden" name="property_id">`). Subsequent debounced auto-saves send this ID and perform an `UPDATE` on the same record rather than inserting new rows.
- **Draft Resumption:**
  - Visiting `/landlord/properties/create` automatically checks if the authenticated landlord has an active draft (`approval_status = 'draft'`).
  - If found, the form restores the draft fields (`name`, `address`, `description`, `latitude`, `longitude`, `building_inclusions`, `house_rules`, and existing cover photo thumbnail).
  - A subtle alert notifies the user: *"Resumed draft: [Property Name]"* with a *"Start Fresh"* escape action (`?new=1`).
- **Final Submission Integration:** When the landlord clicks *"Submit for Review"*, the form sends `property_id` to `PropertyController@store`. Instead of creating a second property, `store` updates the existing draft record, transitions `approval_status` from `draft` to `pending`, and redirects to property details. No duplicate property records are ever created.

## 2. Timing & Event Handlers
- **Text Inputs & Textareas:** Debounced by 1000ms after user stops typing.
- **Checkboxes & Multi-select Chips:** Triggers auto-save after 300ms.
- **Map Pin Placement & Geolocation:** Triggers auto-save after 400ms.
- **Image Selection:** File input change immediately dispatches multipart `FormData` to upload the image, saves the file via `FileStorageService`, links `image_path` to the draft, and renders the thumbnail preview with status *"Saved in draft"*.
- **Navigation (Continue / Previous / Submit):** Flushes any pending debounced timers and executes `triggerAutoSaveNow()` before panel transition.
- **Beforeunload Guard:** A `window.beforeunload` listener warns the user if an auto-save network request is actively in-flight.
