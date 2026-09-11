<?php

return [
    /*
    |--------------------------------------------------------------------------
    | AI Assistant Execution Settings
    |--------------------------------------------------------------------------
    */
    'rate_limit_per_minute' => (int) env('CHATBOT_RATE_LIMIT_PER_MINUTE', 20),
    'max_tool_iterations' => (int) env('CHATBOT_MAX_TOOL_ITERATIONS', 4),
    'history_message_limit' => (int) env('CHATBOT_HISTORY_MESSAGE_LIMIT', 10),

    /*
    |--------------------------------------------------------------------------
    | System Routes by Role
    |--------------------------------------------------------------------------
    */
    'routes' => [
        'student' => [
            ['label' => 'Student setup', 'path' => '/student/setup'],
            ['label' => 'Browse rooms', 'path' => '/student/rooms'],
            ['label' => 'Room details', 'path' => '/student/rooms/{room}'],
            ['label' => 'Property map', 'path' => '/student/properties/map'],
            ['label' => 'Requests', 'path' => '/student/requests'],
            ['label' => 'Messages', 'path' => '/messages'],
            ['label' => 'Notifications', 'path' => '/notifications'],
            ['label' => 'Tenant onboarding', 'path' => '/student/onboarding'],
            ['label' => 'Tenant dashboard', 'path' => '/student/tenant-dashboard'],
            ['label' => 'Payments', 'path' => '/student/payments'],
            ['label' => 'Reports', 'path' => '/student/reports'],
            ['label' => 'Profile', 'path' => '/student/profile'],
            ['label' => 'Dashboard', 'path' => '/student/dashboard'],
        ],
        'landlord' => [
            ['label' => 'Dashboard', 'path' => '/landlord/dashboard'],
            ['label' => 'Setup', 'path' => '/landlord/setup'],
            ['label' => 'Properties', 'path' => '/landlord/properties'],
            ['label' => 'Rooms', 'path' => '/landlord/rooms'],
            ['label' => 'Booking requests', 'path' => '/landlord/bookings'],
            ['label' => 'Messages', 'path' => '/landlord/messages'],
            ['label' => 'Feedback', 'path' => '/landlord/feedback'],
            ['label' => 'Tenants', 'path' => '/landlord/tenants'],
            ['label' => 'Onboarding', 'path' => '/landlord/onboarding'],
            ['label' => 'Leave requests', 'path' => '/landlord/leave-requests'],
            ['label' => 'Maintenance', 'path' => '/landlord/maintenance'],
            ['label' => 'Payments', 'path' => '/landlord/payments'],
            ['label' => 'Analytics', 'path' => '/landlord/analytics'],
            ['label' => 'Profile', 'path' => '/landlord/profile'],
            ['label' => 'Notifications', 'path' => '/notifications'],
        ],
        'admin' => [
            ['label' => 'Dashboard', 'path' => '/admin/dashboard'],
            ['label' => 'Settings', 'path' => '/admin/settings'],
            ['label' => 'Users', 'path' => '/admin/users'],
            ['label' => 'Student verifications', 'path' => '/admin/student-verifications'],
            ['label' => 'Permits', 'path' => '/admin/permits'],
            ['label' => 'Properties', 'path' => '/admin/properties'],
            ['label' => 'Property approvals', 'path' => '/admin/properties/approval'],
            ['label' => 'Bookings', 'path' => '/admin/bookings'],
            ['label' => 'Boarded students', 'path' => '/admin/boarded-students'],
            ['label' => 'Onboardings', 'path' => '/admin/onboardings'],
            ['label' => 'Reports', 'path' => '/admin/reports'],
            ['label' => 'Notifications', 'path' => '/notifications'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | System Knowledge Base
    |--------------------------------------------------------------------------
    */
    'knowledge' => [
        'system_overview' => [
            'OBHS (Online Boarding House System) is a comprehensive web platform for student boarding house discovery, reservation, and tenancy management.',
            'User roles are student, landlord, and admin. Each role has specific permissions and scoped portals.',
        ],
        'role_definitions' => [
            'student' => 'Can browse and filter approved rooms, view properties on map, submit booking inquiries and requests, complete student profile verification, complete tenant onboarding, view payment dues and submit payment proofs, submit reports/concerns, and message landlords.',
            'tenant_student' => 'An active tenant who has an approved booking and is currently boarded. Can access Tenant Dashboard, view room details, submit monthly payments, view invoices, request leave, and submit feedback.',
            'landlord' => 'Can manage owned properties and rooms, set room prices and capacities, review and approve/reject booking requests, manage active tenants and leave requests, verify tenant onboarding documents, track tenant payments and send reminders, and view property occupancy analytics.',
            'admin' => 'System administrator who oversees all users, verifies student enrollment proofs and IDs, reviews optional landlord business permits, reviews and approves/rejects property listings, monitors all bookings, tracks boarded students across colleges, handles system-wide reports/issues, and manages system configurations.',
        ],
        'workflows' => [
            'Booking Workflow' => 'Student browses available rooms -> views room details -> clicks Request Booking -> landlord receives booking request in Landlord Portal -> landlord approves or rejects request.',
            'Onboarding Workflow' => 'Once booking is approved, student proceeds to Tenant Onboarding -> uploads required documents (e.g., proof of enrollment, valid ID) -> signs rental contract -> pays required advance/deposit -> landlord reviews and verifies onboarding documents.',
            'Payment Workflow' => 'Landlord creates monthly dues or system tracks monthly rent -> tenant views dues in Student Portal > Payments -> tenant submits payment proof/reference -> landlord reviews and verifies payment status.',
            'Property Approval Workflow' => 'Landlord registers and creates a property listing -> listing enters pending state -> admin reviews listing photos, permit, and details in Admin Portal > Property Approvals -> admin approves or rejects property listing.',
            'Student Verification Workflow' => 'Newly registered student submits enrollment documents (COR/COE) and school ID in Student Setup -> admin reviews documents under Student Verifications -> once approved, student gains full access to booking rooms.',
            'Reporting / Concerns Workflow' => 'Student or tenant submits a concern/complaint via Reports page -> AI classifies severity priority (low, medium, high) -> admin reviews, investigates, and responds via Admin Portal.',
        ],
        'policy_rules' => [
            'Role isolation: Students can only view their own personal records, bookings, payments, and public available properties.',
            'Landlords can only view their own properties, rooms, tenants, and bookings.',
            'Admins can access aggregate statistics, system monitoring, and administrative review queues.',
            'Students with an active approved booking stay cannot book another room simultaneously.',
            'Landlord business permits are optional for basic operations but subjected to admin review when provided.',
            'Room occupancy rules: Solo rooms accommodate 1 tenant exclusively; shared rooms can accommodate multiple occupants up to room capacity.',
        ],
        'page_dictionary' => [
            '/student/dashboard' => 'Student dashboard home with overview of requests and quick links.',
            '/student/tenant-dashboard' => 'Tenant stay dashboard with current room, landlord contact, and stay details.',
            '/student/rooms' => 'Browse and search available rooms with filters (price, occupancy, amenities).',
            '/student/properties/map' => 'Interactive map of boarding houses and properties.',
            '/student/requests' => 'List of booking requests submitted by the student.',
            '/student/onboarding' => 'Tenant onboarding steps: document upload, contract signing, and deposit submission.',
            '/student/payments' => 'Monthly rental payments, pending invoices, and payment proof submission.',
            '/student/reports' => 'Submit and view status of student reports and complaints.',
            '/student/profile' => 'Manage student profile, emergency contacts, and personal information.',
            '/student/setup' => 'Student verification and enrollment proof upload page.',
            '/landlord/dashboard' => 'Landlord overview: active properties, room occupancy, pending bookings.',
            '/landlord/properties' => 'Manage boarding house properties.',
            '/landlord/rooms' => 'Manage rooms, pricing, capacity, and maintenance status.',
            '/landlord/bookings' => 'Review, approve, or reject incoming booking requests.',
            '/landlord/tenants' => 'Manage active and past tenants residing in properties.',
            '/landlord/onboarding' => 'Review tenant onboarding documents and countersign contracts.',
            '/landlord/payments' => 'Track tenant monthly payments, verify receipts, and send reminders.',
            '/landlord/leave-requests' => 'Review move-out or temporary leave requests from tenants.',
            '/landlord/analytics' => 'Occupancy statistics and revenue analytics.',
            '/admin/dashboard' => 'Administrative dashboard and KPI summary.',
            '/admin/users' => 'Manage registered students, landlords, and admin accounts.',
            '/admin/student-verifications' => 'Review student IDs and enrollment documents.',
            '/admin/permits' => 'Review landlord business permits.',
            '/admin/properties/approval' => 'Queue for approving new boarding house property listings.',
            '/admin/properties' => 'Monitor all boarding houses and properties.',
            '/admin/bookings' => 'Monitor all booking activities across the platform.',
            '/admin/boarded-students' => 'Comprehensive boarded student registry and college breakdown.',
            '/admin/onboardings' => 'Track tenant onboarding completion status.',
            '/admin/reports' => 'Manage and respond to student reports, complaints, and safety concerns.',
            '/admin/settings' => 'System settings and configurations.',
        ],
        'faq' => [
            'How to browse rooms?' => 'Go to Student Portal > Browse Rooms (/student/rooms) to search available accommodations.',
            'How to book a room?' => 'Open any available room page from /student/rooms, verify details, and click Request Booking.',
            'Where to upload onboarding documents?' => 'Visit /student/onboarding once your booking request is approved.',
            'Where to settle payments?' => 'Visit /student/payments to see current dues and upload payment receipts.',
            'How to report an issue?' => 'Go to /student/reports to submit a concern directly to system administrators.',
        ],
    ],
];
