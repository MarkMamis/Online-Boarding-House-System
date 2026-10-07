# 02 — Report Preset Matrix

## Overview
This matrix audits all six institutional report presets supported by `AdminReportingService.php` in the Online Boarding House System (OBHS).

---

## Preset Comparison Matrix

| Preset Key | Report Title | Service Method | Primary Model / Data Source | Output Schema (Columns) | Supported Filters | Table Preview Changes? | Global KPIs / Charts Change? | CSV Export Status | Print Report Status | Implementation Status |
|---|---|---|---|---|---|---|---|---|---|---|
| `active_boarders` | Active Boarded Students Report | `getActiveBoardersData` | `Booking` (where status = approved & stay dates overlap today) with `Student`, `Room.Property.Landlord` | `Student Name`, `Student ID`, `College`, `Program`, `Boarding House`, `Room`, `Check-In`, `Check-Out` | `property_id`, `college` | **YES** (Shows student record listing) | **NO** | Working (Produces sanitized CSV) | Broken (Prints entire 4-page dashboard) | **VERIFIED DISTINCT** |
| `property_distribution` | Boarding House Distribution Report | `getPropertyDistributionData` | `Property` (approved) with room counts & occupancy aggregates | `Boarding House`, `Landlord`, `Total Rooms`, `Occupied Rooms`, `Available Rooms`, `Bed Capacity`, `Occupied Beds`, `Occupancy Rate` | `property_id` | **YES** (Shows property-level capacity table) | **NO** | Working (Produces sanitized CSV) | Broken (Prints entire 4-page dashboard) | **VERIFIED DISTINCT** |
| `academic_distribution` | Academic Distribution Report | `getAcademicDistributionData` | `BoardingMonitoringService::getProgramDistribution` via `baseQuery` | `College Code`, `Program Name`, `Active Boarders` | `property_id`, `college`, `program`, `period` | **YES** (Shows academic program counts) | **NO** | Working (Produces sanitized CSV) | Broken (Prints entire 4-page dashboard) | **VERIFIED DISTINCT** |
| `occupancy_availability` *(Default)* | Occupancy and Availability Report | `getOccupancyAvailabilityData` | `Property` (approved) with room status breakdown & available slots | `Property Name`, `Address`, `Total Rooms`, `Occupied Rooms`, `Available Rooms`, `Available Slots`, `Room Occupancy Rate (%)`, `Bed Occupancy Rate (%)` | `property_id` | **YES** (Shows property room/bed inventory) | **NO** | Working (Produces sanitized CSV) | Broken (Prints entire 4-page dashboard) | **VERIFIED DISTINCT** |
| `compliance_summary` | Landlord Compliance Summary Report | `getComplianceSummaryData` | `AnalyticsService::getDocumentComplianceBreakdown` via `LandlordDocument` | `Document Type`, `Valid`, `Expiring Soon`, `Expired`, `Pending Review`, `Rejected`, `Total Submitted` | None (Global document snapshot) | **YES** (Shows permit & safety breakdown) | **NO** | Working (Produces sanitized CSV) | Broken (Prints entire 4-page dashboard) | **VERIFIED DISTINCT** |
| `incident_summary` | Incident Reports Summary | `getIncidentSummaryData` | `Report` model | `Incident Category / State`, `Total Cases` | None (Global report snapshot) | **YES** (Shows triage counts & priorities) | **NO** | Working (Produces sanitized CSV) | Broken (Prints entire 4-page dashboard) | **VERIFIED DISTINCT** |

---

## Detailed Preset Behavior Analysis

### 1. `active_boarders` (Active Boarded Students Report)
- **Selection Criteria:** Approved bookings where `check_in <= TODAY` and `(check_out IS NULL OR check_out > TODAY)`.
- **Deduplication:** Shows individual active booking stay rows; summary displays `count()` and `unique(student_id)`.
- **Columns:** Student Name, Student ID, College, Program, Boarding House, Room, Check-In, Check-Out.
- **Preview Behavior:** Generates student-level roster table.

### 2. `property_distribution` (Boarding House Distribution Report)
- **Selection Criteria:** Approved properties queried via `AnalyticsService::getPropertyOccupancyAnalytics`.
- **Metrics Computed:** Total rooms, occupied rooms (`status = 'occupied'`), available rooms, bed capacity, occupied beds (`capacity - slots_available`), and room occupancy rate.
- **Columns:** Boarding House, Landlord, Total Rooms, Occupied Rooms, Available Rooms, Bed Capacity, Occupied Beds, Occupancy Rate.

### 3. `academic_distribution` (Academic Distribution Report)
- **Selection Criteria:** Students mapped to active bookings via `BoardingMonitoringService::getProgramDistribution`.
- **Columns:** College Code, Program Name, Active Boarders.

### 4. `occupancy_availability` (Occupancy and Availability Report - Default)
- **Selection Criteria:** Approved properties queried via `AnalyticsService::getPropertyOccupancyAnalytics`.
- **Columns:** Property Name, Address, Total Rooms, Occupied Rooms, Available Rooms, Available Slots, Room Occupancy Rate (%), Bed Occupancy Rate (%).

### 5. `compliance_summary` (Landlord Compliance Summary Report)
- **Selection Criteria:** Landlord documents evaluated by `DocumentExpirationService`.
- **Columns:** Document Type, Valid, Expiring Soon, Expired, Pending Review, Rejected, Total Submitted.

### 6. `incident_summary` (Incident Reports Summary)
- **Selection Criteria:** Global aggregation on `Report` model (`status` and `priority`).
- **Columns:** Incident Category / State, Total Cases.
