# Leaflet Map Behavior & Initialization Audit

**Document Reference:** `docs/implementation/landlord-property-details-refinement/MAP_BEHAVIOR.md`

---

## 1. Root Cause Analysis: Why Was the Map Gray / Blank?

In the previous version, the Leaflet map frequently appeared blank or with incomplete gray tiles on mobile and narrow viewports.

### Verified Root Causes
1. **Unconstrained Container Dimensions:**
   - The map container had inline `style="height:300px;"` inside an unconstrained card-body without an explicit `min-height` or display property in CSS.
   - When the browser rendered the card within a responsive flex/grid shell, Leaflet calculated container dimensions before styles and external webfonts fully computed, resulting in 0px or incomplete bounds.
2. **Missing `invalidateSize()` Call:**
   - Leaflet tile layer fetches tiles based on the container viewport at the time of `L.map()` initialization.
   - Without an asynchronous `map.invalidateSize()` invocation, Leaflet never refreshed its bounding box after fonts, images, or layout finalized.
3. **No Coordinates Null Validation:**
   - If a property had `0.0` or empty coordinates, Leaflet initialized with invalid lat/lng, resulting in gray tiles or ocean rendering.

---

## 2. Implemented Fixes

### CSS Dimensions Contract
```css
.property-map-container {
    height: 260px;
    min-height: 240px;
    width: 100%;
    display: block;
    background: #f1f5f9;
    z-index: 1;
}
```

### Leaflet Script & Timing Fix
```javascript
document.addEventListener('DOMContentLoaded', () => {
    const mapEl = document.getElementById('propertyMap');
    if (!mapEl) return;

    const propertyLatLng = [{{ $lat }}, {{ $lng }}];
    const map = L.map('propertyMap', {
        scrollWheelZoom: false
    }).setView(propertyLatLng, 16);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxNativeZoom: 19,
        maxZoom: 22,
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    const marker = L.marker(propertyLatLng).addTo(map);
    marker.bindPopup(`<strong>{{ addslashes($property->name) }}</strong><br>{{ addslashes($property->address) }}`);

    L.circle(propertyLatLng, {
        color: '#14532d',
        fillColor: '#14532d',
        fillOpacity: 0.08,
        radius: 100
    }).addTo(map);

    // Guaranteed tile rendering fix for Leaflet layout calculation
    setTimeout(() => {
        map.invalidateSize();
    }, 200);

    window.addEventListener('resize', () => {
        map.invalidateSize();
    });
});
```

### Unmapped State Fallback
When `$hasCoordinates` is false (`latitude === null || longitude === null || lat == 0`):
- Leaflet CSS and JS are **not** loaded.
- A clean, helpful empty-state panel is rendered:
  - Icon: `bi-geo`
  - Title: "Location not pinned yet."
  - Description: "GPS coordinates have not been set for this boarding house. Pin the location in property settings to display the interactive map."
  - Action button: `Pin Location` linking to `route('landlord.properties.edit', $property->id)`.
