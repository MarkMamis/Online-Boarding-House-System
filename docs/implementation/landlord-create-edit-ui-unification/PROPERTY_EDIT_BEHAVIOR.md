# Property Edit Behavior

## 1. Safety & Approval Preservation
- Existing approved properties are updated via `PropertyController@update`.
- No automatic draft mutation or silent unpublishing occurs.
- Changes require explicit user submission via the final "Save Changes" action.
- Automatic background auto-saving (`landlord.properties.autosave`) is strictly deactivated during edit mode.

## 2. Inclusions & Rules Handling
- Standard amenities are validated against `config('property_amenities.flat')`.
- Custom amenities are submitted through `building_inclusion_custom[General][]` with `building_inclusion_custom_present = 1`.
- Existing custom amenities are populated as removable chips.
- Existing house rules are loaded into category checkboxes or custom text inputs.

## 3. Cover Photo & Map
- Existing cover photo renders with file name and size hint.
- Selecting a new photo swaps the preview. Clicking remove restores the upload prompt without altering database state until save.
- Leaflet map loads existing `latitude` and `longitude` coordinates with interactive draggable pin marker.
