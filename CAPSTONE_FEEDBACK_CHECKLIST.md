# Capstone Adviser Feedback - Implementation Checklist

**Generated:** April 4, 2026 | **Project:** Online Boarding House System (OBHS)

---

## 📋 Overview

This checklist tracks capstone adviser feedback across all modules: Student, Landlord, Admin, Chatbot, Homepage, and Landing Page.

---

## ✅ COMPLETED FEATURES

### **Student Module**
- [x] Room feedback system (comment + rating)
- [x] View room details with images, services, pricing
- [x] Booking workflow (submit → approve → onboard)
- [x] Message landlords
- [x] Access to chatbot
- [x] Profile creation with basic info
- [x] View active bookings and booking history

### **Landlord Module**
- [x] Property creation with images and coordinates
- [x] Room management CRUD (pricing, capacity, services)
- [x] Room image carousel with labels
- [x] Business permit upload
- [x] Payment method setup (bank details + GCash)
- [x] Message students
- [x] View bookings and tenant onboarding status
- [x] Dashboard with property/booking overview
- [x] Leave request management

### **Admin Module**
- [x] User management (landlords and students)
- [x] Property approval queue with status filtering (pending/rejected/approved/all)
- [x] Property details page with transparency (rooms, services, pricing)
- [x] Business permit approval workflow
- [x] Booking approval workflow
- [x] Tenant onboarding management
- [x] Report/complaint handling
- [x] Message students and landlords
- [x] Dashboard with stats (users, properties, bookings, pending counts)

### **Common Features**
- [x] Email verification on signup
- [x] Role-based access control (admin/landlord/student)
- [x] Messaging system with read indicators
- [x] Notifications system
- [x] Map integration (Leaflet.js) with property locations
- [x] Search and filter for rooms/properties
- [x] Payment method configuration
- [x] Chatbot integration for FAQs

---

## ❌ NOT YET IMPLEMENTED

### **🌟 Student Module - Missing Features**

- [ ] **Star Rating System for Rooms**
  - Allow students to rate rooms 1-5 stars (separate from feedback comments)
  - Display average star rating on room cards and detail page
  - Show rating distribution breakdown

- [ ] **Student Report Enhancements**
  - Require description field (currently may be optional)
  - Replace severity terms: Change "low/medium/high" to alternative terminology
    - Suggested: "Minor/Moderate/Severe" or "Issue/Warning/Critical"
  - Add status tracking (submitted → reviewed → resolved)

- [ ] **Student Profile - Emergency Contact**
  - Add parent contact information fields (name, phone, address)
  - Make viewable by admin and landlord (for emergency contact purposes)
  - Add parent photo/ID field (optional)

- [ ] **Star Rating Reminder/Prompt**
  - After booking ends or check-out, prompt student to rate the room
  - Allow rating through notification or email link

---

### **🏠 Landlord Module - Missing Features**

- [ ] **Building/Boarding House Inclusions**
  - Add detailed checkbox list of amenities:
    - Infrastructure: CCTV, Fire Extinguishers, Emergency Lights, WIFI range
    - Facilities: Parking, Kitchen, Laundry, Common Area
    - Safety: Locks, 24/7 Guard, Visitor Policy
  - Display inclusions prominently on property card and room details
  - Filter properties by inclusion/amenity

- [ ] **Advance Payment System**
  - Add advance payment option (1 month deposit/advance)
  - Track advance payment status (paid/pending/overdue)
  - Show advance payment as separate line item in invoice
  - Auto-calculate first month billing with advance deduction

- [ ] **Information Hiding (Privacy)**
  - Allow landlord to view student information excluding ID number
  - Hide sensitive ID fields in tenant profile view
  - Configurable privacy settings for tenant data visibility

- [ ] **Real-time Tenant Count Display**
  - Show total number of tenants on dashboard
  - On hover/click property card: display tenant list with names
  - Show occupancy rate: e.g., "5/10 students" with progress bar
  - Breakdown by room (Room 1: 2/2, Room 2: 1/3, etc.)

- [ ] **Payment Delay Notifications**
  - Auto-generate notification when payment is delayed (overdue X days)
  - Send email alert to landlord about unpaid bookings
  - Add payment status filter: "Paid / Pending / Overdue" on dashboard
  - Show warning badge on tenant name if payment overdue
  - QR Code generation for GCash payment reminders

- [ ] **Dashboard Tenant Analytics**
  - Add widget showing total active tenants
  - List recent check-ins with dates
  - Monthly occupancy trend chart

---

### **👨‍💼 Admin Module - Missing Features**

- [ ] **Student List with Demographics**
  - Create admin view listing all students:
    - Fields: Name, Course/Program, Year Level, Gender
    - Filterable and sortable by each field
    - Export to CSV/PDF option

- [ ] **Boarding House Analytics by Category**
  - Dashboard showing:
    - Total students per boarding house
    - Breakdown by location (campus, district, etc.)
    - Student names and count per boarding house
    - Cross-tab: Students per boarding house per program/course
    - Gender breakdown (Male/Female) per boarding house

- [ ] **Program-based Occupancy Report**
  - Show how many students from each program (BS Computer Science, etc.)
  - Show gender distribution per program
  - Identify over-concentration of specific programs in certain boarding houses

- [ ] **Data Privacy & Security**
  - Ensure student data is only visible to:
    - Admin (full access)
    - Assigned landlord (limited: name, contact, emergency info only)
    - Students themselves
  - No cross-landlord visibility (landlord A can't see landlord B's students)
  - Audit log for data access
  - Comply with data privacy regulations (mask sensitive fields)

- [ ] **Admin Dashboard Map**
  - Add interactive map showing all landlord locations
  - Color-code or size pins by:
    - Number of properties
    - Number of active tenants
    - Status (approved/pending/rejected)
  - Click to drill down to boarding house details

---

### **💬 Chatbot Module - Missing Features**

- [ ] **Message Status Indicators**
  - Label messages with status: "Sent" || "Delivered" || "Seen"
  - Update indicator when message is read by recipient
  - Timestamp for each message

- [ ] **Chat Identification**
  - Display name/avatar of person chat belongs to (at top of window)
  - Show role badge (Admin / Landlord / Student)
  - Profile link to view full user info

- [ ] **Chat History Improvements**
  - List all conversations with unread count badges
  - Search conversations by participant name
  - Mark conversations as archived/unread

---

### **🎨 Landing Page - Design & UX**

- [ ] **Homepage Background Image**
  - Replace generic background with actual photo of Calapan Campus
  - Ensure image is high quality and optimized for web
  - Add overlay/gradient for text readability

- [ ] **MSU Branding Elements**
  - Add "Mindoro State University, Calapan Campus" text prominently
  - Display OSSE (Office of Student Services and Engagement) logo
  - Position logo consistently with university branding guidelines

- [ ] **Featured Button Styling**
  - Review and adjust green outline buttons (feature button)
  - Ensure visibility and contrast
  - Test on mobile and desktop

- [ ] **Interactive Button Hover Effects**
  - All buttons should change color on hover
  - Apply consistent hover state (darker shade or accent color)
  - Add smooth transition animation (0.2-0.3s)
  - Test hover state on mobile (use active state instead)

- [ ] **"Get Started" Button CTA**
  - Underline the "Get Started" text to emphasize call-to-action
  - Apply hover effects (color change + underline enhancement)
  - Link to appropriate role-selection page

- [ ] **Logo Size and Placement**
  - Increase logo size for better visibility
  - Ensure proper positioning (not competing with other elements)
  - Maintain aspect ratio and clarity

- [ ] **Navigation Simplification**
  - Reduce number of clickable items in header/nav
  - Focus on 3-4 key actions:
    - Sign In
    - Register (with role selector)
    - Browse Properties
    - Help/Chatbot
  - Move less critical links to footer

---

### **📱 Profile & Data Collection**

- [ ] **Student Profile Picture**
  - Add photo upload to student profile
  - Display on profile view and tenant list
  - Use as avatar in messages/notifications

- [ ] **Emergency Contact Form**
  - Add fields: Parent/Guardian Name, Contact Number, Relationship, Address
  - Admin and Landlord can view (with privacy controls)
  - Include in onboarding flow

---

### **💳 Payment System Enhancements**

- [ ] **Advance Payment Tracking**
  - Add advance payment field to booking
  - Track payment status: "Pending / Verified / Released"
  - Deduct advance from final bill

- [ ] **Payment Delay Alerts**
  - Auto-notify landlord X days before due date
  - Auto-alert when payment becomes overdue
  - Email + in-app notification

- [ ] **QR Code-based Payments**
  - Generate unique GCash QR code for each payment
  - Include reference number for tracking
  - Update payment status when student submits proof of payment

---

### **📊 Dashboard Improvements**

### **Landlord Dashboard**
- [ ] Real-time occupancy widget (X/Y tenants)
- [ ] Tenant list with status (active/checked-out)
- [ ] Payment status overview (on-time/overdue/pending)
- [ ] Monthly revenue trend

### **Admin Dashboard**
- [ ] Map of all boarding houses by location
- [ ] Student count by program (pie chart or bar chart)
- [ ] Gender distribution (pie chart)
- [ ] Occupancy rate by region

---

## 📈 Priority Implementation Order

### **Phase 1 (High Priority - Core UI/UX)**
1. Landing page redesign (Calapan image, MSU branding, button styling)
2. Star rating system for rooms
3. Student emergency contact in profile
4. Real-time tenant count on landlord dashboard

### **Phase 2 (Medium Priority - Key Features)**
1. Building inclusions/amenities list
2. Payment delay notifications
3. Admin student list with demographics
4. Chat message status indicators (seen/delivered/sent)

### **Phase 3 (Lower Priority - Analytics)**
1. Advance payment system
2. Admin boarding house map
3. Program-based occupancy analytics
4. Data privacy audit logging

---

## 🔐 Data Privacy & Compliance Notes

### **Current Status**
- Email verification implemented ✓
- Role-based access control implemented ✓
- Policy-based authorization for models ✓

### **Todo**
- [ ] Add data privacy policy page
- [ ] Implement audit logging (who accessed what data and when)
- [ ] Add student consent form for data sharing (landlord visibility)
- [ ] Ensure GDPR/local data privacy law compliance
- [ ] Add forgotten/delete account feature
- [ ] Mask sensitive data in admin views (partial ID numbers, etc.)

---

## 🎯 Next Steps for Development Team

1. **UI Polish:** Start with landing page redesign (high impact, moderate effort)
2. **Core Student Features:** Add star ratings and emergency contact
3. **Landlord Analytics:** Implement real-time occupancy display
4. **Admin Features:** Build demographic reports and student list
5. **Payment Features:** Add advance payment and delay notifications
6. **Final Polish:** Chat status indicators, amenities list, data privacy

---

## 📝 Notes

- **Timeline Estimate:** 4-6 weeks for full implementation (depending on team size)
- **Database Changes:** Minimal for most features; only advance payment requires schema change
- **Testing:** Recommend testing each feature across all roles (admin/landlord/student)
- **Mobile Responsiveness:** Ensure all new features work on mobile (max-width: 576px)

---

**Last Updated:** April 4, 2026  
**Status:** Feedback Analysis Complete - Ready for Development
