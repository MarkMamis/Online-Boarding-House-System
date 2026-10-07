# MinSU OBHS Institutional Report Definitions

**Document Version:** 1.0  
**Domain:** Institutional Analytics & Accreditation Reporting  
**Target Audience:** MinSU Housing Board, University Administration, OSAS, and OBHS Portal Administrators  

---

## 1. Preset Definitions & Data Contracts

### Preset 1: Active Boarded Students Report (`active_boarders`)
- **Purpose:** De-duplicated list of enrolled MinSU students actively staying in accredited boarding houses.
- **Source Models:** `Booking`, `User`, `Room`, `Property`.
- **Inclusion Filter:** `bookings.status = 'approved'`, `check_in <= NOW()`, `check_out > NOW()`.
- **Columns:** Student Name, Student ID, College, Program, Boarding House, Room, Check-In, Check-Out.
- **Privacy Notice:** Excludes student contact numbers, home addresses, and private uploaded credentials.

### Preset 2: Boarding House Distribution Report (`property_distribution`)
- **Purpose:** Aggregate breakdown of accommodations, capacity, and bed occupancy by accredited boarding house.
- **Source Models:** `Property`, `Room`, `User`.
- **Columns:** Boarding House, Landlord, Total Rooms, Occupied Rooms, Available Rooms, Bed Capacity, Occupied Beds, Occupancy Rate.
- **Aggregation Rules:** De-duplicated per property; bed capacity vs room count kept strictly distinct.

### Preset 3: Academic Distribution Report (`academic_distribution`)
- **Purpose:** Housing distribution across MinSU colleges and academic programs.
- **Source Models:** `Booking`, `User` (Student).
- **Columns:** College Code, Program Name, Active Boarders.

### Preset 4: Occupancy and Availability Report (`occupancy_availability`)
- **Purpose:** Room availability and bed vacancy tracking across properties for prospective students.
- **Source Models:** `Property`, `Room`.
- **Columns:** Property Name, Address, Total Rooms, Occupied Rooms, Available Rooms, Available Slots, Room Occupancy Rate (%), Bed Occupancy Rate (%).

### Preset 5: Landlord Compliance Summary Report (`compliance_summary`)
- **Purpose:** Verification status of business permits and safety certificates.
- **Source Models:** `LandlordDocument`, `User` (Landlord).
- **Columns:** Document Type, Valid, Expiring Soon, Expired, Pending Review, Rejected, Total Submitted.

### Preset 6: Incident Reports Summary (`incident_summary`)
- **Purpose:** Aggregate metrics on student reported issues, urgency levels, and resolution performance.
- **Source Models:** `Report`.
- **Columns:** Incident Category / State, Total Cases.
