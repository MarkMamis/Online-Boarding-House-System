# Redundancy Removal Matrix
**Second Pass Refinement — OBHS Student & Tenant Marketplace**

This document details all redundant controls, duplicate shortcuts, promotional banners, and superfluous navigation elements removed across the Student and Tenant experience to achieve a clean, minimalist consumer marketplace.

---

| Page | Control Removed | Why Redundant | Existing Alternative Access | Functionality Preserved |
| :--- | :--- | :--- | :--- | :--- |
| **Student Home** (`/student/dashboard`) | Dark gradient "Campus Vicinity Exploration Banner" | Large promotional banner occupying prime space and duplicating the primary navigation. | Sidebar item "Campus Map" (`/student/properties/map`). | Full interactive Leaflet map remains directly accessible via sidebar and category links. |
| **Student Home** (`/student/dashboard`) | 3-Card "Safety, Accreditation & Support Notice" footer widgets | Statically repeated claims that duplicate information already present on cards and in the sidebar. | Sidebar item "Help & Safety", hero accreditation badge, and room card badges. | Incident reporting and accreditation verification remain 100% active. |
| **Student Home** (`/student/dashboard`) | "Near MinSU Gates" category chip | Misleading category chip that was not backed by geolocation distance data and merely linked to the map. | Sidebar item "Campus Map". | Students can view geographic distribution on the Campus Map without false distance promises. |
| **Student Home** (`/student/dashboard`) | "Explore all (X)" header link | Repeated action directly next to the featured room cards. | Clicking any room card or sidebar "Explore Rooms". | Direct room catalog browsing. |
| **Explore Rooms** (`/student/rooms`) | Header "Campus Map" button | Top-level shortcut duplicating the persistent navigation sidebar. | Persistent sidebar "Campus Map" link. | Campus map browsing remains unchanged. |
| **Explore Rooms** (`/student/rooms`) | Room card `Details ->` micro-button | Repeated click action on an already fully-clickable card anchor. | Entire room card is a clickable touch/mouse target. | Full keyboard and screen reader accessibility to room details. |
| **Explore Rooms** (`/student/rooms`) | Inclusions pill chips on card face | Created uneven vertical card heights and visual noise on dense catalog grids. | Detailed Inclusions section on Room Details page (`/student/rooms/{id}`). | Full amenity and inclusion inspection preserved on details page. |
| **My Bookings & Stays** (`/student/requests`) | Header "Explore More Rooms" button | Duplicated primary sidebar navigation item. | Persistent sidebar item "Explore Rooms". | Catalog exploration remains one click away. |
| **My Bookings & Stays** (`/student/requests`) | 3rd Tab: "All Requests (X)" | Pure union/duplicate of "Active & Upcoming" and "Past Stays & History", adding unnecessary tab switching. | Filtered views: "Active & Upcoming" and "Past Stays & History". | Complete chronological history is preserved across the two clean, mutually exclusive tabs. |
| **Tenant Dashboard** (`/student/tenant-dashboard`) | 4 Admin KPI metric tiles (Rent, Due date, Occupancy, Updates) | Administrative control aesthetic that cluttered the top of the resident's stay page. | Clean, compact resident stay card header displaying rent, due date, and landlord contact. | Transparent billing and tenancy information preserved. |
| **Tenant Dashboard** (`/student/tenant-dashboard`) | Giant 16:9 Google Maps iframe | Occupied 8 columns of prime space showing a static map of where the resident already lives. | Room details and landlord address summary. | Property address and landlord contact details clearly displayed. |
| **Tenant Dashboard** (`/student/tenant-dashboard`) | "Messages" & "Help Center" buttons in Quick Actions | Duplicated persistent sidebar navigation links. | Persistent sidebar items "Messages" and "Help & Safety". | Direct communication and reporting remains fully accessible. |
| **Tenant Dashboard** (`/student/tenant-dashboard`) | Statistical "Reports Overview" & "Feedback Overview" tiles | Cluttered admin dashboard widgets duplicating dedicated student pages. | Dedicated Help & Safety center (`/student/reports`) and Room Feedback page. | Confidential incident reporting and room feedback remain fully supported. |
| **Room Details** (`/student/rooms/{id}`) | Obsolete hash-based panel switcher script | Leftover legacy script from monolithic dashboard that hijacked clicks to `#panel` anchors. | Standalone RESTful routes. | Seamless navigation without URL hash collisions. |
