# Responsive Fixes & Collision Audits
**Second Pass Refinement — OBHS Student & Tenant Marketplace**

This document details layout, collision, and breakpoint refinements made to ensure smooth usability across all device widths from 320px to 1440px+.

---

### 1. Viewport Breakpoint Testing Matrix

| Breakpoint | Target Area | Issue Identified | Resolution Implemented |
| :--- | :--- | :--- | :--- |
| **320px – 390px** (Mobile Small) | Search bar in Hero & Catalog | Long placeholder text caused button clipping or flex wrapping. | Scaled search bar padding (`.25rem .35rem .25rem .85rem`) and font size (`0.88rem`). Search button fits comfortably without horizontal scroll. |
| **320px – 430px** (Mobile Phones) | Room Listing Cards | Inclusions chips created uneven card heights and badge collisions. | Removed noisy amenity chips from card face; standardized card container to photo-first 16:10 ratio with clean text hierarchy. |
| **320px – 768px** (Mobile Devices) | Global Chatbot Widget Collision | Floating circular chatbot button (`z-index: 9999`) sat directly on top of bottom action buttons and form submit buttons. | Scaled mobile chatbot toggle button to `46px` with safe margin (`bottom: 1.25rem; right: 1rem`) and added `.has-sticky-cta .chatbot-widget { bottom: 5.5rem !important; }`. |
| **320px – 768px** (Mobile Devices) | Filter Drawer on Explore Rooms | Large desktop padding caused drawer controls to stretch past screen bounds. | Replaced fixed column spacing with fluid Bootstrap row-cols grid (`col-12 col-md-6 col-lg-3`) with accessible touch labels. |
| **768px – 1024px** (Tablets) | Catalog Grid | 3 columns appeared crowded on smaller tablet viewports. | Responsive grid: 2 columns on tablet (`row-cols-md-2`), 3 columns on desktop (`row-cols-xl-3`), 4 columns on large displays (`row-cols-xxl-4`). |
| **1024px+** (Desktop) | Sidebar to Main Transition | Smooth transition with no content overlapping. | Fluid `main-col` grid alignment without hardcoded pixel overflows. |

---

### 2. Manual QA Checklist for Visual Inspection

1. **Mobile Drawer Navigation:**
   - Tap hamburger menu on top left (`< 992px`).
   - Offcanvas drawer slides smoothly from the left.
   - Tap any link: drawer automatically closes and body scroll is restored.
   - Tap backdrop or close (X) button: drawer closes with zero ghost overlay.
2. **Search Bar:**
   - On 360px screen, search input and "Search" pill button are aligned horizontally without wrapping or truncation.
3. **Card Touch Targets:**
   - On mobile, tapping anywhere on the room card navigates directly to Room Details.
4. **Chatbot Safe Zone:**
   - Open any page on mobile; verify the chatbot button sits comfortably in the bottom-right corner without blocking form buttons or navigation bars.
