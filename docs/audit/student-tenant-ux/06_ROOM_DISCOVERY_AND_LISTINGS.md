# Room Discovery, Marketplace Listings & Card Hierarchy Audit

**Audit Date:** October 18, 2026  
**Scope:** Evaluation of Room Catalog (`student/rooms/index.blade.php`), Recommended Carousel, and Room Card Visual Hierarchy  

---

## 1. Current Room Discovery Experience

Room browsing in OBHS exists in two implementations:
1. An embedded table and carousel inside the monolithic `student/dashboard.blade.php` (under the `browse-rooms` panel).
2. The standalone dedicated view `resources/views/student/rooms/index.blade.php` (rendered by `RoomController@studentIndex`).

Inspection of `student/rooms/index.blade.php` shows a fairly advanced set of filters and card elements, but it suffers from **information density overload, confusing terminology, and layout fragmentation**.

---

## 2. Recommended Room Card Audit: Current vs. Consumer Standard

### Current Card Anatomy in `student/rooms/index.blade.php`:

```
┌────────────────────────────────────────────────────────┐
│ [PHOTO: 4:3 Aspect Ratio]                              │
│ [Top-Left: "Accredited MinSU"]   [Top-Right: "Available"]│
├────────────────────────────────────────────────────────┤
│ Boarding House Name • MinSU Calapan                    │
│ [Rating Chip: ★ 4.8 (12)]   [Pill: "Hybrid Pricing"]    │
├────────────────────────────────────────────────────────┤
│ Room 102                                               │
│ Occupancy: Shared Bed-spacer                           │
│ Available Slots: [======    ] 2 of 4 beds available    │
├────────────────────────────────────────────────────────┤
│ Inclusions: [WiFi] [Water] [Aircon] [Kitchen] [CR]     │
│             [Cabinet] [Study Table] (+3 more)          │
├────────────────────────────────────────────────────────┤
│ Price Emphasis Box:                                    │
│ ₱ 1,800.00 /mo per bed                                 │
│ (Whole room: ₱3,600 /mo)                  [View Details]│
└────────────────────────────────────────────────────────┘
```

### Critical UX Deficiencies Identified:

1. **Information Density & Badge Clutter:**
   A single card currently displays up to **8 competing badges and metadata chips**:
   - Accreditation pill
   - Status badge ("Available")
   - Star rating pill
   - Pricing model pill ("Hybrid", "Per Room", "Per Bed")
   - Occupancy progress bar
   - Slots summary text
   - Multiple amenity chips
   - Price emphasis gradient box
   On a mobile device (360px–390px wide), this results in cards that are 450px to 520px tall, forcing students to scroll past just two cards per full viewport height.

2. **Confusion Between Property Title and Room Title:**
   - In MinSU boarding houses, students care about **which boarding house** they are living in first (e.g., *"Villa Milagrosa Dormitory"*), and **which specific room or bed** second (e.g., *"Room 3A (2-Pax)"*).
   - Currently, cards place the room number in a large bold heading while the boarding house name is in small, faint text above it. Prospective students often fail to notice which property owns the room.

3. **Pricing Model Ambiguity (Per Bed vs. Per Room vs. Hybrid):**
   - The OBHS backend supports 3 pricing models (`per_room`, `per_bed`, `hybrid`).
   - For `hybrid` rooms, the card currently displays an emerald gradient box with two prices: `Per bed: ₱1,500` and `Per room: ₱3,000`. While mathematically correct, the hierarchy is jarring. Students are unsure whether requesting the room commits them to the entire ₱3,000 or only ₱1,500.

4. **Inclusion Repetition:**
   - Both room-specific inclusions (e.g., "Study Table", "Foam Bed") and building-wide inclusions (e.g., "CCTV", "Fire Extinguisher", "Gated Security") are displayed as identical chips.
   - Showing safety/compliance items (like "Fire Extinguisher") on room cards adds clutter without aiding comfort comparison.

5. **Missing-Image Fallbacks:**
   - When a landlord uploads no room photo, cards show an empty grey rectangle with an icon: `[No Photo]`. This immediately lowers student trust. Properties should fall back gracefully to the verified property facade photo or an authentic campus dorm illustration.

---

## 3. Recommended Consumer Listing Card Hierarchy

Inspired by modern accommodation marketplaces (Airbnb, Booking.com) but tailored specifically for MinSU collegiate boarding:

```
┌────────────────────────────────────────────────────────┐
│ [High-Quality Photo (16:10 Aspect Ratio, Rounded-4)]   │
│ 🏷️ Verified MinSU Boarding    ❤️ Wishlist (Future)     │
│ 📍 350m from MinSU Gate 1                              │
├────────────────────────────────────────────────────────┤
│ Villa Milagrosa Boarding House                         │
│ Room 2B • 2-Bed Shared Room                            │
│                                                        │
│ 🛏️ 1 Bed Available (of 2)   ★ 4.8 (14 reviews)        │
│                                                        │
│ Inclusions: WiFi • Free Water • Own CR                 │
├────────────────────────────────────────────────────────┤
│ ₱1,800 / month per bed                    [View Room]  │
└────────────────────────────────────────────────────────┘
```

### Six Core Principles for the Redesigned Card:
1. **Photo Dominance (16:10 Aspect Ratio):** Clean, crisp photography with rounded corners and subtle hover zoom.
2. **Clear Geographic Context:** Distance to MinSU main campus gate (e.g. *"300m from Main Gate"* or *"Walking distance"*).
3. **Property First, Room Second:** Prominent property name, followed by room type.
4. **Transparent Price & Cadence:** Large, unambiguous monthly rent with the exact cadence (`₱1,800 / month per bed` or `₱3,500 / month entire room`).
5. **Restrained Amenities (Max 3):** Display only the 3 highest-priority student amenities (e.g. WiFi, Water inclusion, Attached bathroom).
6. **Prominent Tap Target:** The entire card should be clickable, with a clear primary CTA button for mobile accessibility.

---

## 4. Search and Filter Experience Audit

### What is Currently Supported:
- Search query (`q`): matches room number, property name, property address.
- Price bounds (`min_price`, `max_price`): dual range slider / numeric inputs.
- Occupancy mode: `all`, `per_room`, `per_bed`.
- Rating filter: `any`, `4_up`, `3_up`, `unrated`.
- Building inclusions (JSON array): matched using `orWhereJsonContains`.

### UX Friction Points:
1. **Collapsible Filter Panel:** On mobile, clicking "Filters" expands a massive dropdown with dozens of checkboxes that covers the entire screen, pushing room results completely off-view.
2. **Lack of Instant Filter Feedback:** Applying a filter reloads the entire page and resets scroll position.
3. **Empty Results State:** When filters produce zero matches, the screen shows a plain grey box with no suggestions on how to broaden the search (e.g. *"Try clearing amenity filters or raising max price"*).
4. **URL Query Pollution:** Filters generate long, messy query strings with redundant default parameters (`?rating=any&occupancy=all&min_price=0&max_price=10000`).
