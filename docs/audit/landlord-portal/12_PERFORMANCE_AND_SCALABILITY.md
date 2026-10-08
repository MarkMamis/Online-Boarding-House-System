# Landlord Portal — Performance & Scalability Audit

**Hosting Target:** Hostinger Shared Hosting (Apache, MySQL 8.0, PHP 8.2+)  
**Audit Scope:** Query complexity, N+1 bottlenecks, eager loading, asset bundle sizes, caching  
**Date:** 2026-10-08  

---

## 1. Shared Hosting Invariants & Constraints

- **Hostinger Shared Environment Invariants:**
  - No root server access.
  - No background worker daemons (Supervisor / systemd).
  - No in-memory cache stores (Redis / Memcached); uses file-based caching.
  - Standard Apache PHP execution with FastCGI.
- **Audit Verification:**
  - Verified that all landlord controllers, services, and queues operate synchronously within standard HTTP request lifecycles.
  - No mandatory WebSocket or daemon dependencies exist.

---

## 2. Query Efficiency & Eager Loading Inspection

### A. Dashboard Query Analysis (`AuthController@landlordDashboard`):
- **Eager Loading:**
  - Eager loads `rooms.bookings.student` and `withCount` on rooms.
  - Aggregates payment watchlist with `Booking::with(['room.property', 'student'])`.
  - **Assessment:** Well-structured eager loading prevents N+1 queries on the main dashboard for landlords with up to 10 properties and 50 rooms.

### B. Payments Index Query Analysis (`PaymentController@index`):
- **Eager Loading:**
  - Loads `['room.property', 'student', 'tenantOnboarding', 'tenantPayments', 'latestSubmittedTenantPayment', 'latestTenantPayment']`.
  - In-memory collection iterations (`foreach ($bookings as $booking)`) calculate summary metrics without firing separate database queries per row.
  - **Assessment:** Efficient for typical landlords (< 200 total active stays). For larger portfolios, pagination should be applied to prevent unbounded memory growth.

### C. Sidebar Navigation Query Analysis (`layouts/landlord.blade.php:490–520`):
- **Finding:**
  - Every landlord layout request queries all properties and all child rooms to render the nested accordion links in the sidebar:
    ```php
    $landlordProperties = Property::where('landlord_id', Auth::id())
        ->withCount('rooms')
        ->with(['rooms' => fn ($q) => $q->select(['id', 'property_id', 'room_number'])->orderBy('room_number')])
        ->get();
    ```
  - While lightweight, querying rooms on every page load is redundant when a landlord is just viewing notifications or settings.
  - **Optimization:** Cache the landlord's property-room tree in session or only load it on property/room management pages.

---

## 3. Asset Bundle & Build Metrics

- **Vite Build Verification:**
  - CSS bundle: `40.66 kB` (gzipped: `9.54 kB`).
  - JS bundle: `36.69 kB` (gzipped: `14.75 kB`).
  - Static build time: `3.56s`.
  - **Assessment:** Extremely fast, lightweight, and well-optimized for low-bandwidth mobile connections in regional MinSU locations (Calapan City / Oriental Mindoro).
