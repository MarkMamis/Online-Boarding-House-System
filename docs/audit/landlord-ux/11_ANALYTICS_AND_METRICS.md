# OBHS Landlord Portal: Analytics, Metrics & Business Intelligence

**Document ID:** `11_ANALYTICS_AND_METRICS.md`  
**Date:** October 18, 2026  
**Auditor:** Senior Laravel Architect & Business Intelligence Analyst  
**Focus:** Mathematical Correctness, Meaningful Small-Business Reporting, and Data Scoping.

---

## 1. Analytics vs Monitoring: Clear Boundary Definition

| Operational Dimension | Monitoring View (`/landlord/properties` or `/landlord/payments`) | Analytics View (`/landlord/analytics`) |
| :--- | :--- | :--- |
| **Occupancy** | *"How many beds are occupied today?"* (e.g., 12 / 14 occupied right now) | *"Has our occupancy improved over the last 6 months?"* (Trend curve) |
| **Payments** | *"Who owes rent this week?"* (Specific student names & pending proofs) | *"How much total cash did we collect in August vs September?"* (Bar comparison) |
| **Tenancy** | *"Who is scheduled to move in this Friday?"* (Upcoming onboarding) | *"What is the average length of stay for boarders?"* (Duration metrics) |
| **Maintenance** | *"Which rooms are offline today?"* (Room status) | *"How many days per year do rooms sit in maintenance?"* (Downtime) |

---

## 2. Mathematical Audit of Current Analytics Queries

A code inspection of `AnalyticsController@index` revealed four critical calculation discrepancies:

### Discrepancy 1: Omitting Onboarding Payments from Revenue
```php
// AnalyticsController.php:52-62
$monthlyRevenue = (float) \App\Models\TenantPayment::where('status', 'approved')
    ->where(...)
    ->whereHas('booking.room.property', fn($q) => $q->where('landlord_id', $landlordId))
    ->sum('amount_due');
```
- **Error:** Only aggregates `TenantPayment`. It completely ignores `TenantOnboarding` deposit and advance rent payments!
- **Impact:** When a landlord onboards 4 new students who each pay ₱3,500 (₱14,000 total), `AnalyticsController` reports **₱0 monthly revenue**! This severely misrepresents landlord cash flow.
- **Fix:** Calculate collected revenue as:
  ```php
  $collectedMonthly = TenantPayment::where('status', 'approved')->...->sum('amount_due');
  $collectedOnboarding = TenantOnboarding::where('deposit_paid', true)->...->sum('deposit_amount');
  $totalCollectedCash = $collectedMonthly + $collectedOnboarding;
  ```

### Discrepancy 2: Inaccurate Expected Monthly Revenue
```php
// AnalyticsController.php:75-77
$totalExpected = (float) $approvedBookings->sum(function ($booking) {
    return (float) ($booking->room->price ?? 0);
});
```
- **Error:** Uses `$booking->room->price` as a flat fallback.
- **Impact:** If a room has a hybrid pricing model with `price_per_room = ₱5,000` and `price_per_bed = ₱2,500`, a student renting a bedspace will be erroneously calculated as generating ₱5,000/month expected rent!
- **Fix:** Sum `$booking->monthly_rent_amount`, which was authoritatively resolved and frozen upon booking approval (`BookingController:446`).

### Discrepancy 3: Undercounting Multi-Capacity Room Occupancy
```php
// AnalyticsController.php:28-40
'rooms as occupied_rooms' => function ($q) {
    $q->where('status', 'occupied');
}
```
- **Error:** Counts rooms where `rooms.status = 'occupied'`.
- **Impact:** In a 4-bed room where 2 students are actively living, the room status remains `'available'` (because 2 bed slots remain). Therefore, `AnalyticsController` counts this room as **completely vacant** (`available_rooms`), severely undercounting actual property occupancy!
- **Fix:** Compute bedspace occupancy based on total capacity vs active bookings:
  $$\text{Occupancy Rate} = \frac{\sum \text{Active Bookings}}{\sum \text{Room Capacity}} \times 100$$

---

## 3. Recommended Analytics Dashboard Layout

Boarding house owners do not need Wall Street financial charts. They need 4 clear, understandable business metrics:

```
┌────────────────────────────────────────────────────────────────────────┐
│                      BUSINESS PERFORMANCE OVERVIEW                     │
│ Reporting Period: [ Last 6 Months ▾ ] [ Property: All Properties ▾ ]   │
├────────────────────────────────────────────────────────────────────────┤
│ 4 CORE SMALL-BUSINESS KPIS:                                            │
│                                                                        │
│ [ 88% Average Occupancy ]        [ ₱145,000 Total Cash Collected ]     │
│   +4% compared to last semester     ₱28,500 / month average            │
│                                                                        │
│ [ 96% On-Time Rent Payment ]     [ 4.8 ★ Tenant Rating (18 Reviews) ]  │
│   Only 4% late payment rate         Positive feedback sentiment        │
├────────────────────────────────────────────────────────────────────────┤
│ MONTHLY CASH COLLECTION TREND (Clean Bar Chart)                        │
│ • Compares actual cash collected (Green) vs expected rent (Gray).      │
│ • Clear tooltip showing breakdown: Onboarding Dues vs Recurring Rent.  │
├────────────────────────────────────────────────────────────────────────┤
│ ROOM TYPE DEMAND & CAPACITY BREAKDOWN                                  │
│ • Solo Rooms: 100% Occupied (High Demand)                              │
│ • Bedspaces: 80% Occupied                                              │
│ Actionable Insight: "Solo rooms have the highest booking velocity."    │
└────────────────────────────────────────────────────────────────────────┘
```

---

## 4. Strict SQL & Privacy Invariants

- **Role Scoping:** Every analytics query must be strictly scoped to `Property::where('landlord_id', Auth::id())`.
- **MySQL Strict Mode Parity:** All aggregations must adhere to `ONLY_FULL_GROUP_BY` and MySQL 8.x standards (as demonstrated in `.agents/handoffs/2026-10-17-mysql-strict-analytics-fix.md`).
- **Anonymized Aggregations:** Analytics dashboards must present aggregate statistics without exposing sensitive student personal profiles.
