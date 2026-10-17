# Listing Design Contract

This document outlines the visual and data contracts for accommodation listings across the OBHS Student Marketplace, specifically governing `resources/views/components/room-card.blade.php`, catalog views, and detail views.

---

## 1. Core Principles
- **Image-Led Hierarchy:** The accommodation photograph anchors the card. Students scan listings visually first, then assess location/pricing, then read specific capacity and amenities.
- **Accurate Availability:** Capacity and available bed slots reflect live database state (`capacity - active_bookings_count`). No fake "Only 1 left!" artificial scarcity.
- **Pricing Transparency:** Unit pricing must explicitly state whether the rate is per-room or per-bed to avoid unexpected surprise costs.

---

## 2. Visual & Layout Specifications

| Property | Value / Rule | Implementation Notes |
| :--- | :--- | :--- |
| **Aspect Ratio** | 16:10 (`aspect-ratio: 16/10`) | Standard modern card ratio; prevents letterboxing and keeps listing height balanced. |
| **Border Radius** | 16px (`rounded-3` / `border-radius: 1rem`) | Soft, consumer-grade card contours without excessive ballooning. |
| **Card Borders** | 1px solid `#e2e8f0` (`#cbd5e1` on hover) | Subtle definition against the `#f8fafc` canvas background. |
| **Card Hover Effect** | `translateY(-3px)`, shadow elevation `0 10px 25px -5px rgba(0,0,0,0.06)` | Gentle micro-animation signaling clickability. |
| **Verified Badge** | Top-left badge: Forest green background (`#14532d`), white text | Displayed only when the parent `Property` has approved business permit and verification status. |
| **Capacity Badge** | Top-right badge: Dark semi-transparent pill (`rgba(15, 23, 42, 0.82)`), blur 8px | Displays `"Available"` or `"X slot(s) left"`. Turns amber if 1 slot left. |

---

## 3. Data Binding Contract

### Room Card Attributes (`<x-room-card :room="$room" />`)

```php
@props([
    'room',        // App\Models\Room instance (eager-loaded with property, roomImages, feedbacks)
    'href' => null // Optional override link (defaults to route('student.rooms.show', $room))
])
```

### Image Resolution Fallback Cascade
1. **Primary Room Image:** First image in `room->roomImages` (or `room->images`).
2. **Property Cover Image:** First image in `room->property->propertyImages` / `room->property->images` / `cover_image`.
3. **Neutral Branded Fallback:** Inline SVG placeholder featuring MinSU forest-green accents with an architectural outline.
*Strict Invariant:* Under no circumstances will landlord identity documents, permits, or contracts be exposed as accommodation imagery.

### Price Display Logic
```blade
<div class="fw-bold fs-5 text-dark">
    ₱{{ number_format($room->price, 0) }}
    <span class="fs-6 fw-normal text-muted">/ month</span>
</div>
<span class="badge text-secondary border">
    {{ $room->effectivePricePerBed() ? '₱' . number_format($room->effectivePricePerBed(), 0) . ' / bed' : 'Whole room' }}
</span>
```

---

## 4. Responsive Grid Behavior

- **Mobile (< 768px):** 1 column full-width (`col-12`). Card images occupy full container width with 4:3 or 16:10 scaling.
- **Tablet (768px – 1024px):** 2 columns (`col-md-6`).
- **Desktop (≥ 1024px):** 3 columns (`col-lg-4`).
- **Touch Target:** The entire listing card is a clickable anchor (`<a href="..." class="d-block text-decoration-none">`) ensuring seamless tap targets without nested navigation collisions.
