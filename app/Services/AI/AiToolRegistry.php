<?php

namespace App\Services\AI;

use App\Services\AI\Tools\BookingTools;
use App\Services\AI\Tools\DashboardTools;
use App\Services\AI\Tools\DocumentTools;
use App\Services\AI\Tools\LandlordTools;
use App\Services\AI\Tools\OnboardingTools;
use App\Services\AI\Tools\PaymentTools;
use App\Services\AI\Tools\PropertyTools;
use App\Services\AI\Tools\ReportTools;
use App\Services\AI\Tools\RoomTools;
use App\Services\AI\Tools\StudentTools;
use App\Services\AI\Tools\UserTools;

class AiToolRegistry
{
    /**
     * Map of all registered system tools.
     *
     * @var array<string, array>
     */
    protected array $tools = [];

    public function __construct()
    {
        $this->registerDefaultTools();
    }

    /**
     * Register default read-only tools.
     */
    protected function registerDefaultTools(): void
    {
        // ---------------------------------------------------------------------
        // CURRENT USER & NOTIFICATIONS
        // ---------------------------------------------------------------------
        $this->register([
            'name' => 'get_current_user_summary',
            'description' => 'Retrieves profile and account summary for the currently logged-in user, such as full name, email, role, setup completion status, and membership date. Use when the user asks "What is my name?", "What role am I?", "Sino ako?", or about their account profile.',
            'parameters' => [
                'type' => 'object',
                'properties' => [],
            ],
            'allowed_roles' => ['student', 'landlord', 'admin'],
            'handler' => [UserTools::class, 'getCurrentUserSummary'],
            'read_only' => true,
        ]);

        $this->register([
            'name' => 'get_my_notifications',
            'description' => 'Retrieves recent unread and total notifications for the current authenticated user.',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'limit' => [
                        'type' => 'integer',
                        'description' => 'Number of recent notifications to retrieve (default: 5).',
                    ],
                ],
            ],
            'allowed_roles' => ['student', 'landlord', 'admin'],
            'handler' => [UserTools::class, 'getMyNotifications'],
            'read_only' => true,
        ]);

        // ---------------------------------------------------------------------
        // USERS & ADMIN USER MANAGEMENT
        // ---------------------------------------------------------------------
        $this->register([
            'name' => 'count_users',
            'description' => 'Returns the count of registered OBHS users. Can filter by role (student, landlord, admin) or account active status. Use when asked "How many users are registered?", "Ilan ang total users?", etc.',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'role' => [
                        'type' => 'string',
                        'enum' => ['student', 'landlord', 'admin', 'all'],
                        'description' => 'Filter by role.',
                    ],
                    'status' => [
                        'type' => 'string',
                        'enum' => ['active', 'inactive', 'all'],
                        'description' => 'Filter by active status.',
                    ],
                ],
            ],
            'allowed_roles' => ['admin'],
            'handler' => [UserTools::class, 'countUsers'],
            'read_only' => true,
        ]);

        $this->register([
            'name' => 'get_user_statistics',
            'description' => 'Returns system-wide user statistics, including counts by role, active vs inactive users, and verified email accounts. Use when comparing users across roles.',
            'parameters' => [
                'type' => 'object',
                'properties' => [],
            ],
            'allowed_roles' => ['admin'],
            'handler' => [UserTools::class, 'getUserStatistics'],
            'read_only' => true,
        ]);

        $this->register([
            'name' => 'search_users',
            'description' => 'Searches registered users by name or email. Returns safe public profile info (ID, name, email, role, active status). Passwords and sensitive data are excluded.',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'query' => [
                        'type' => 'string',
                        'description' => 'Name or email query to search.',
                    ],
                    'role' => [
                        'type' => 'string',
                        'enum' => ['student', 'landlord', 'admin', 'all'],
                        'description' => 'Optional role filter.',
                    ],
                    'limit' => [
                        'type' => 'integer',
                        'description' => 'Max number of results (default: 10).',
                    ],
                ],
            ],
            'allowed_roles' => ['admin'],
            'handler' => [UserTools::class, 'searchUsers'],
            'read_only' => true,
        ]);

        // ---------------------------------------------------------------------
        // STUDENTS
        // ---------------------------------------------------------------------
        $this->register([
            'name' => 'count_students',
            'description' => 'Counts registered students in OBHS with optional account status and date period filters (e.g. today, yesterday, this_week, this_month, or custom date range). Use when an admin asks "Ilan ang students?", "Ilan students today?", "How many students registered this week?", etc.',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'status' => [
                        'type' => 'string',
                        'enum' => ['active', 'inactive', 'all'],
                        'description' => 'Filter by account status.',
                    ],
                    'period' => [
                        'type' => 'string',
                        'enum' => ['today', 'yesterday', 'this_week', 'last_week', 'this_month', 'last_month', 'this_year', 'past_7_days', 'past_30_days', 'all'],
                        'description' => 'Semantic registration date period filter.',
                    ],
                    'date_from' => [
                        'type' => 'string',
                        'description' => 'Start date (YYYY-MM-DD).',
                    ],
                    'date_to' => [
                        'type' => 'string',
                        'description' => 'End date (YYYY-MM-DD).',
                    ],
                ],
            ],
            'allowed_roles' => ['admin'],
            'handler' => [StudentTools::class, 'countStudents'],
            'read_only' => true,
        ]);

        $this->register([
            'name' => 'get_student_statistics',
            'description' => 'Retrieves aggregate metrics and complete database-side grouped statistics on registered students in OBHS. Supports total registered student counts, ID verification statuses, actively boarded students, date period filtering (today, yesterday, this_week, this_month, or custom date range), and grouped breakdowns by college, program (or course), verification_status, year_level, or gender. For breakdown/group questions (e.g. "stats ng students based on college", "students per course", "breakdown by program"), pass group_by to receive full, reconciled database aggregates. Null or missing values are explicitly preserved as "Not specified" and never discarded.',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'group_by' => [
                        'type' => 'string',
                        'enum' => ['college', 'program', 'course', 'verification_status', 'year_level', 'gender', 'onboarding_status'],
                        'description' => 'Optional grouping dimension to break down student population into database-side aggregate counts (e.g. college, program, verification_status).',
                    ],
                    'period' => [
                        'type' => 'string',
                        'enum' => ['today', 'yesterday', 'this_week', 'last_week', 'this_month', 'last_month', 'this_year', 'past_7_days', 'past_30_days', 'all'],
                        'description' => 'Semantic registration date period filter.',
                    ],
                    'date_from' => [
                        'type' => 'string',
                        'description' => 'Start date (YYYY-MM-DD).',
                    ],
                    'date_to' => [
                        'type' => 'string',
                        'description' => 'End date (YYYY-MM-DD).',
                    ],
                ],
            ],
            'allowed_roles' => ['admin'],
            'handler' => [StudentTools::class, 'getStudentStatistics'],
            'read_only' => true,
        ]);

        $this->register([
            'name' => 'get_student_registration_trend',
            'description' => 'Returns historical student registration counts over time grouped by day, week, or month, along with deterministic trend metrics (recent 7/30-day pace, current/previous month, averages, trend direction, and data confidence level). Use when an admin asks "Ano ang registration trend?", "Can you predict/estimate upcoming students?", "Compare last month and this month", "Ilan average registrations?", etc. Provides observed historical data and deterministic pace metrics to safely ground trend answers and low-confidence estimates.',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'group_by' => [
                        'type' => 'string',
                        'enum' => ['month', 'week', 'day'],
                        'description' => 'Aggregation interval for historical series (default: month).',
                    ],
                    'period' => [
                        'type' => 'string',
                        'enum' => ['this_year', 'last_year', 'past_30_days', 'past_7_days', 'all'],
                        'description' => 'Optional date period filter.',
                    ],
                    'date_from' => [
                        'type' => 'string',
                        'description' => 'Start date (YYYY-MM-DD).',
                    ],
                    'date_to' => [
                        'type' => 'string',
                        'description' => 'End date (YYYY-MM-DD).',
                    ],
                ],
            ],
            'allowed_roles' => ['admin'],
            'handler' => [StudentTools::class, 'getStudentRegistrationTrend'],
            'read_only' => true,
        ]);

        $this->register([
            'name' => 'get_recent_students',
            'description' => 'Retrieves a list of recently registered students in OBHS, with optional date filtering (today, yesterday, this_week, this_month, or custom date range) and ID verification status. Use when an admin asks "Who registered today?", "Sino ang bagong students?", "List new students today", etc. Returns safe information: student ID, name, email, college, program, and registration date.',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'period' => [
                        'type' => 'string',
                        'enum' => ['today', 'yesterday', 'this_week', 'last_week', 'this_month', 'last_month', 'this_year', 'past_7_days', 'past_30_days', 'all'],
                        'description' => 'Registration date period filter.',
                    ],
                    'date_from' => [
                        'type' => 'string',
                        'description' => 'Start date (YYYY-MM-DD).',
                    ],
                    'date_to' => [
                        'type' => 'string',
                        'description' => 'End date (YYYY-MM-DD).',
                    ],
                    'verification_status' => [
                        'type' => 'string',
                        'enum' => ['all', 'pending', 'approved', 'rejected'],
                        'description' => 'Filter by ID verification status.',
                    ],
                    'limit' => [
                        'type' => 'integer',
                        'description' => 'Max number of students to return (default: 5).',
                    ],
                ],
            ],
            'allowed_roles' => ['admin'],
            'handler' => [StudentTools::class, 'getRecentStudents'],
            'read_only' => true,
        ]);

        $this->register([
            'name' => 'get_student_boarding_status',
            'description' => 'Checks whether a student has an active room boarding stay, upcoming reservation, or no accommodation. For students, checks their own status.',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'student_id' => [
                        'type' => 'integer',
                        'description' => 'Optional student ID (Admin only).',
                    ],
                ],
            ],
            'allowed_roles' => ['student', 'admin'],
            'handler' => [StudentTools::class, 'getStudentBoardingStatus'],
            'read_only' => true,
        ]);

        $this->register([
            'name' => 'get_student_booking_status',
            'description' => 'Retrieves the authenticated student\'s booking requests and current status (e.g. pending, approved, rejected). Use when a student asks "What is my booking status?", "May booking na ba ako?", etc.',
            'parameters' => [
                'type' => 'object',
                'properties' => [],
            ],
            'allowed_roles' => ['student'],
            'handler' => [StudentTools::class, 'getStudentBookingStatus'],
            'read_only' => true,
        ]);

        // ---------------------------------------------------------------------
        // LANDLORDS
        // ---------------------------------------------------------------------
        $this->register([
            'name' => 'count_landlords',
            'description' => 'Returns the total number of registered landlords in OBHS and business permit verification counts. Use when asked "Ilan ang landlords?", "How many landlords?", etc.',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'status' => [
                        'type' => 'string',
                        'enum' => ['active', 'inactive', 'all'],
                        'description' => 'Filter by account status.',
                    ],
                ],
            ],
            'allowed_roles' => ['admin'],
            'handler' => [LandlordTools::class, 'countLandlords'],
            'read_only' => true,
        ]);

        $this->register([
            'name' => 'get_landlord_statistics',
            'description' => 'Retrieves aggregate landlord metrics: total landlords, active landlords, landlords with properties, and permit statuses.',
            'parameters' => [
                'type' => 'object',
                'properties' => [],
            ],
            'allowed_roles' => ['admin'],
            'handler' => [LandlordTools::class, 'getLandlordStatistics'],
            'read_only' => true,
        ]);

        $this->register([
            'name' => 'get_landlord_properties',
            'description' => 'Returns properties owned by the current landlord. Shows property name, address, approval status, room count, and price range. Use when a landlord asks "How many properties do I have?", "Summarize my properties", etc.',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'landlord_id' => [
                        'type' => 'integer',
                        'description' => 'Optional landlord ID (Admin only).',
                    ],
                ],
            ],
            'allowed_roles' => ['landlord', 'admin'],
            'handler' => [LandlordTools::class, 'getLandlordProperties'],
            'read_only' => true,
        ]);

        $this->register([
            'name' => 'get_landlord_tenant_statistics',
            'description' => 'Returns tenant and occupancy metrics for the current landlord across all their properties: active tenants, occupied vs available slots, pending bookings, and occupancy percentage.',
            'parameters' => [
                'type' => 'object',
                'properties' => [],
            ],
            'allowed_roles' => ['landlord', 'admin'],
            'handler' => [LandlordTools::class, 'getLandlordTenantStatistics'],
            'read_only' => true,
        ]);

        $this->register([
            'name' => 'get_landlord_document_status',
            'description' => 'Returns the live compliance document status (Business Permit and Safety Certificate) for an individual landlord. Shows whether documents are missing, pending review, approved, or rejected. Landlords can view their own compliance status. Administrators can provide a landlord_id or name query to inspect any landlord.',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'landlord_id' => [
                        'type' => 'integer',
                        'description' => 'Optional landlord user ID to inspect (Admin only).',
                    ],
                    'query' => [
                        'type' => 'string',
                        'description' => 'Optional landlord name or email search query (Admin only).',
                    ],
                ],
            ],
            'allowed_roles' => ['landlord', 'admin'],
            'handler' => [LandlordTools::class, 'getLandlordDocumentStatus'],
            'read_only' => true,
        ]);

        // ---------------------------------------------------------------------
        // PROPERTIES
        // ---------------------------------------------------------------------
        $this->register([
            'name' => 'count_properties',
            'description' => 'Counts boarding house properties in OBHS. Can filter by approval status (approved, pending, rejected). For landlords, counts only their own properties.',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'status' => [
                        'type' => 'string',
                        'enum' => ['approved', 'pending', 'rejected', 'all'],
                        'description' => 'Approval status filter.',
                    ],
                ],
            ],
            'allowed_roles' => ['student', 'landlord', 'admin'],
            'handler' => [PropertyTools::class, 'countProperties'],
            'read_only' => true,
        ]);

        $this->register([
            'name' => 'search_properties',
            'description' => 'Searches boarding house properties by name, address, or price range. Students see only approved visible properties; landlords see their own.',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'query' => [
                        'type' => 'string',
                        'description' => 'Text search query for property name or address.',
                    ],
                    'max_price' => [
                        'type' => 'number',
                        'description' => 'Maximum price filter.',
                    ],
                    'limit' => [
                        'type' => 'integer',
                        'description' => 'Maximum results to return (default: 5).',
                    ],
                ],
            ],
            'allowed_roles' => ['student', 'landlord', 'admin'],
            'handler' => [PropertyTools::class, 'searchProperties'],
            'read_only' => true,
        ]);

        $this->register([
            'name' => 'get_property_details',
            'description' => 'Retrieves detailed information about a specific property: address, price range, room count, available slots, inclusions, house rules, and rating.',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'property_id' => [
                        'type' => 'integer',
                        'description' => 'Numeric ID of the property.',
                    ],
                    'property_name' => [
                        'type' => 'string',
                        'description' => 'Name of the property to look up.',
                    ],
                ],
            ],
            'allowed_roles' => ['student', 'landlord', 'admin'],
            'handler' => [PropertyTools::class, 'getPropertyDetails'],
            'read_only' => true,
        ]);

        $this->register([
            'name' => 'get_property_occupancy',
            'description' => 'Calculates room occupancy statistics (capacity, occupied slots, available slots, occupancy rate %) for a specific property, or identifies the highest/lowest occupancy properties across the system.',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'property_id' => [
                        'type' => 'integer',
                        'description' => 'Optional specific property ID.',
                    ],
                ],
            ],
            'allowed_roles' => ['landlord', 'admin'],
            'handler' => [PropertyTools::class, 'getPropertyOccupancy'],
            'read_only' => true,
        ]);

        // ---------------------------------------------------------------------
        // ROOMS
        // ---------------------------------------------------------------------
        $this->register([
            'name' => 'search_available_rooms',
            'description' => 'Searches for currently available rooms with free slots in approved boarding houses. Can filter by price and occupancy mode (solo or shared).',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'max_price' => [
                        'type' => 'number',
                        'description' => 'Maximum budget per month.',
                    ],
                    'occupancy_mode' => [
                        'type' => 'string',
                        'enum' => ['solo', 'shared', 'any'],
                        'description' => 'Preferred occupancy mode (solo room or shared bed).',
                    ],
                    'limit' => [
                        'type' => 'integer',
                        'description' => 'Max number of rooms to return (default: 5).',
                    ],
                ],
            ],
            'allowed_roles' => ['student', 'landlord', 'admin'],
            'handler' => [RoomTools::class, 'searchAvailableRooms'],
            'read_only' => true,
        ]);

        $this->register([
            'name' => 'get_room_details',
            'description' => 'Retrieves details about a specific room: price, pricing model, capacity, available slots, status, and amenities.',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'room_id' => [
                        'type' => 'integer',
                        'description' => 'ID of the room.',
                    ],
                ],
                'required' => ['room_id'],
            ],
            'allowed_roles' => ['student', 'landlord', 'admin'],
            'handler' => [RoomTools::class, 'getRoomDetails'],
            'read_only' => true,
        ]);

        $this->register([
            'name' => 'get_cheapest_available_rooms',
            'description' => 'Returns the cheapest available rooms currently open for booking in approved boarding houses. Use when asked "Ano ang pinakamurang room?", "Cheapest available room", "Lowest price room", etc.',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'limit' => [
                        'type' => 'integer',
                        'description' => 'Number of cheapest rooms to return (default: 3).',
                    ],
                ],
            ],
            'allowed_roles' => ['student', 'landlord', 'admin'],
            'handler' => [RoomTools::class, 'getCheapestAvailableRooms'],
            'read_only' => true,
        ]);

        $this->register([
            'name' => 'get_nearest_available_rooms',
            'description' => 'Finds the closest available rooms to the user based on geographic coordinates (latitude and longitude). Use when asked "Nearest boarding house", "Pinakamalapit na room", etc.',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'latitude' => [
                        'type' => 'number',
                        'description' => 'User latitude.',
                    ],
                    'longitude' => [
                        'type' => 'number',
                        'description' => 'User longitude.',
                    ],
                    'limit' => [
                        'type' => 'integer',
                        'description' => 'Number of rooms to return (default: 3).',
                    ],
                ],
            ],
            'allowed_roles' => ['student', 'landlord', 'admin'],
            'handler' => [RoomTools::class, 'getNearestAvailableRooms'],
            'read_only' => true,
        ]);

        // ---------------------------------------------------------------------
        // BOOKINGS
        // ---------------------------------------------------------------------
        $this->register([
            'name' => 'count_bookings',
            'description' => 'Counts bookings by status (pending, approved, cancelled, rejected). Admins see all; landlords see their properties; students see their own.',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'status' => [
                        'type' => 'string',
                        'enum' => ['pending', 'approved', 'cancelled', 'rejected', 'all'],
                        'description' => 'Booking status filter.',
                    ],
                ],
            ],
            'allowed_roles' => ['student', 'landlord', 'admin'],
            'handler' => [BookingTools::class, 'countBookings'],
            'read_only' => true,
        ]);

        $this->register([
            'name' => 'get_booking_statistics',
            'description' => 'Provides aggregate metrics on bookings: total requests, pending approvals, approved bookings, active stays, and cancellations.',
            'parameters' => [
                'type' => 'object',
                'properties' => [],
            ],
            'allowed_roles' => ['landlord', 'admin'],
            'handler' => [BookingTools::class, 'getBookingStatistics'],
            'read_only' => true,
        ]);

        $this->register([
            'name' => 'get_my_booking',
            'description' => 'Retrieves the authenticated student\'s active or most recent booking details (stay dates, property, room number, monthly rent, payment status).',
            'parameters' => [
                'type' => 'object',
                'properties' => [],
            ],
            'allowed_roles' => ['student'],
            'handler' => [BookingTools::class, 'getMyBooking'],
            'read_only' => true,
        ]);

        $this->register([
            'name' => 'search_bookings',
            'description' => 'Searches bookings by student name, property name, or status. Landlords only see bookings for their properties.',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'search' => [
                        'type' => 'string',
                        'description' => 'Search term for student name or property.',
                    ],
                    'status' => [
                        'type' => 'string',
                        'enum' => ['pending', 'approved', 'cancelled', 'rejected', 'all'],
                        'description' => 'Filter by booking status.',
                    ],
                    'limit' => [
                        'type' => 'integer',
                        'description' => 'Max results (default: 5).',
                    ],
                ],
            ],
            'allowed_roles' => ['landlord', 'admin'],
            'handler' => [BookingTools::class, 'searchBookings'],
            'read_only' => true,
        ]);

        // ---------------------------------------------------------------------
        // PAYMENTS
        // ---------------------------------------------------------------------
        $this->register([
            'name' => 'get_my_payment_status',
            'description' => 'Checks payment dues and overdue status for the logged-in student. Shows monthly rent amount, next payment due date, and whether payments are overdue. Use when asked "May unpaid balance ba ako?", "Overdue ba ako?", etc.',
            'parameters' => [
                'type' => 'object',
                'properties' => [],
            ],
            'allowed_roles' => ['student'],
            'handler' => [PaymentTools::class, 'getMyPaymentStatus'],
            'read_only' => true,
        ]);

        $this->register([
            'name' => 'get_payment_statistics',
            'description' => 'Returns aggregate payment stats: submitted payments, pending review queue, approved payments, and overdue tenant stays.',
            'parameters' => [
                'type' => 'object',
                'properties' => [],
            ],
            'allowed_roles' => ['landlord', 'admin'],
            'handler' => [PaymentTools::class, 'getPaymentStatistics'],
            'read_only' => true,
        ]);

        $this->register([
            'name' => 'get_overdue_payment_statistics',
            'description' => 'Retrieves overdue payment accounts for tenants whose due dates have elapsed without payment.',
            'parameters' => [
                'type' => 'object',
                'properties' => [],
            ],
            'allowed_roles' => ['landlord', 'admin'],
            'handler' => [PaymentTools::class, 'getOverduePaymentStatistics'],
            'read_only' => true,
        ]);

        // ---------------------------------------------------------------------
        // REPORTS
        // ---------------------------------------------------------------------
        $this->register([
            'name' => 'count_reports',
            'description' => 'Counts student concern and complaint reports by status (pending, in_progress, resolved), severity priority (high, medium, low), and date period (e.g. today, yesterday, this_week, this_month, or custom date range). Use when asked "Ilan ang reports today?", "Ilan ang pending reports this week?", "Ilan high priority reports kahapon?", etc.',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'status' => [
                        'type' => 'string',
                        'enum' => ['pending', 'in_progress', 'resolved', 'all'],
                        'description' => 'Report status filter.',
                    ],
                    'priority' => [
                        'type' => 'string',
                        'enum' => ['high', 'medium', 'low', 'all'],
                        'description' => 'Priority level filter.',
                    ],
                    'period' => [
                        'type' => 'string',
                        'enum' => ['today', 'yesterday', 'this_week', 'last_week', 'this_month', 'last_month', 'this_year', 'past_7_days', 'past_30_days', 'all'],
                        'description' => 'Semantic date period filter.',
                    ],
                    'date_from' => [
                        'type' => 'string',
                        'description' => 'Start date (YYYY-MM-DD or ISO 8601 string).',
                    ],
                    'date_to' => [
                        'type' => 'string',
                        'description' => 'End date (YYYY-MM-DD or ISO 8601 string).',
                    ],
                    'date_basis' => [
                        'type' => 'string',
                        'enum' => ['created_at', 'resolved_at', 'updated_at'],
                        'description' => 'Which date field to filter on (created_at for submission date default, resolved_at for resolution timestamp).',
                    ],
                ],
            ],
            'allowed_roles' => ['admin'],
            'handler' => [ReportTools::class, 'countReports'],
            'read_only' => true,
        ]);

        $this->register([
            'name' => 'get_pending_reports',
            'description' => 'Retrieves pending and high-priority unresolved reports needing administrator attention, optionally filtered by date period (e.g. today, this_week) and priority level.',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'priority' => [
                        'type' => 'string',
                        'enum' => ['high', 'medium', 'low', 'all'],
                        'description' => 'Filter by priority level.',
                    ],
                    'period' => [
                        'type' => 'string',
                        'enum' => ['today', 'yesterday', 'this_week', 'last_week', 'this_month', 'last_month', 'this_year', 'past_7_days', 'past_30_days', 'all'],
                        'description' => 'Semantic date period filter.',
                    ],
                    'date_from' => [
                        'type' => 'string',
                        'description' => 'Start date (YYYY-MM-DD).',
                    ],
                    'date_to' => [
                        'type' => 'string',
                        'description' => 'End date (YYYY-MM-DD).',
                    ],
                    'limit' => [
                        'type' => 'integer',
                        'description' => 'Max number of reports to retrieve (default: 5, max: 15).',
                    ],
                ],
            ],
            'allowed_roles' => ['admin'],
            'handler' => [ReportTools::class, 'getPendingReports'],
            'read_only' => true,
        ]);

        $this->register([
            'name' => 'get_report_statistics',
            'description' => 'Returns live aggregate report statistics from OBHS. Use this tool when an authorized user asks how many reports exist, including questions filtered by date period, report status, severity priority, or combinations of those filters. Date filters can be used for questions such as today, yesterday, this week, this month, or custom date ranges. The tool can distinguish: reports submitted during a date range, pending reports, in-progress reports, resolved reports, and high, medium, and low priority reports. When the user explicitly asks about a time period (e.g. today, kahapon, this week, this month), apply the requested date filter instead of returning only all-time statistics.',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'period' => [
                        'type' => 'string',
                        'enum' => ['today', 'yesterday', 'this_week', 'last_week', 'this_month', 'last_month', 'this_year', 'past_7_days', 'past_30_days', 'all'],
                        'description' => 'Semantic date period filter (e.g. today, yesterday, this_week, this_month).',
                    ],
                    'date_from' => [
                        'type' => 'string',
                        'description' => 'Explicit start date (e.g. 2026-09-11 or YYYY-MM-DD).',
                    ],
                    'date_to' => [
                        'type' => 'string',
                        'description' => 'Explicit end date (e.g. 2026-09-11 or YYYY-MM-DD).',
                    ],
                    'status' => [
                        'type' => 'string',
                        'enum' => ['pending', 'in_progress', 'resolved', 'all'],
                        'description' => 'Filter by report status.',
                    ],
                    'priority' => [
                        'type' => 'string',
                        'enum' => ['high', 'medium', 'low', 'all'],
                        'description' => 'Filter by priority level (high, medium, low).',
                    ],
                    'date_basis' => [
                        'type' => 'string',
                        'enum' => ['created_at', 'resolved_at', 'updated_at'],
                        'description' => 'Database timestamp basis (created_at for submission date default, resolved_at for resolution date).',
                    ],
                ],
            ],
            'allowed_roles' => ['admin'],
            'handler' => [ReportTools::class, 'getReportStatistics'],
            'read_only' => true,
        ]);

        $this->register([
            'name' => 'search_reports',
            'description' => 'Searches and lists submitted student reports with optional text query, status filter, priority level, and date period filtering (e.g. today, yesterday, this week, this month, or custom date range). Use when an admin asks "Show me today\'s high priority reports", "List reports submitted yesterday", "Sino ang nag-submit ng report today?", etc. Returns safe details including title, status, priority, student name, and submission time.',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'query' => [
                        'type' => 'string',
                        'description' => 'Search term matching report title, description, or student name.',
                    ],
                    'status' => [
                        'type' => 'string',
                        'enum' => ['pending', 'in_progress', 'resolved', 'all'],
                        'description' => 'Filter by status.',
                    ],
                    'priority' => [
                        'type' => 'string',
                        'enum' => ['high', 'medium', 'low', 'all'],
                        'description' => 'Filter by priority level.',
                    ],
                    'period' => [
                        'type' => 'string',
                        'enum' => ['today', 'yesterday', 'this_week', 'last_week', 'this_month', 'last_month', 'this_year', 'past_7_days', 'past_30_days', 'all'],
                        'description' => 'Date period filter.',
                    ],
                    'date_from' => [
                        'type' => 'string',
                        'description' => 'Start date (YYYY-MM-DD).',
                    ],
                    'date_to' => [
                        'type' => 'string',
                        'description' => 'End date (YYYY-MM-DD).',
                    ],
                    'date_basis' => [
                        'type' => 'string',
                        'enum' => ['created_at', 'resolved_at', 'updated_at'],
                        'description' => 'Timestamp basis (created_at or resolved_at).',
                    ],
                    'limit' => [
                        'type' => 'integer',
                        'description' => 'Max number of reports to return (default: 5, max: 15).',
                    ],
                ],
            ],
            'allowed_roles' => ['admin'],
            'handler' => [ReportTools::class, 'searchReports'],
            'read_only' => true,
        ]);

        $this->register([
            'name' => 'get_my_reports',
            'description' => 'Retrieves reports and concerns submitted by the authenticated student along with administrative responses. Can filter by date period or status.',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'status' => [
                        'type' => 'string',
                        'enum' => ['pending', 'in_progress', 'resolved', 'all'],
                        'description' => 'Filter by status.',
                    ],
                    'period' => [
                        'type' => 'string',
                        'enum' => ['today', 'yesterday', 'this_week', 'last_week', 'this_month', 'last_month', 'this_year', 'past_7_days', 'past_30_days', 'all'],
                        'description' => 'Semantic date period filter.',
                    ],
                    'date_from' => [
                        'type' => 'string',
                        'description' => 'Start date (YYYY-MM-DD).',
                    ],
                    'date_to' => [
                        'type' => 'string',
                        'description' => 'End date (YYYY-MM-DD).',
                    ],
                    'limit' => [
                        'type' => 'integer',
                        'description' => 'Max number of reports to retrieve (default: 5).',
                    ],
                ],
            ],
            'allowed_roles' => ['student'],
            'handler' => [ReportTools::class, 'getMyReports'],
            'read_only' => true,
        ]);

        // ---------------------------------------------------------------------
        // DOCUMENTS & VERIFICATIONS
        // ---------------------------------------------------------------------
        $this->register([
            'name' => 'get_landlord_document_statistics',
            'description' => 'Returns live aggregate compliance-document statistics for landlord accounts in OBHS. Use this tool when an authorized user asks about landlord Business Permits, Safety Certificates, missing documents, pending document reviews, approved or rejected compliance documents, document completeness, or the number of landlords with incomplete requirements. Missing means the document has not been uploaded. Pending means a document was submitted but has not yet been reviewed. Approved means the document was reviewed and accepted. Rejected means the document was reviewed and rejected. The tool distinguishes the number of missing document records (instances) from the number of landlords who have at least one missing document.',
            'parameters' => [
                'type' => 'object',
                'properties' => [],
            ],
            'allowed_roles' => ['admin'],
            'handler' => [DocumentTools::class, 'getLandlordDocumentStatistics'],
            'read_only' => true,
        ]);

        $this->register([
            'name' => 'get_landlords_with_missing_documents',
            'description' => 'Returns a live list of registered landlords who have at least one missing required compliance document (Business Permit, Safety Certificate, or both). Use when an admin asks "Sino ang missing ng documents?", "Which landlords have incomplete requirements?", "Sino walang safety certificate?", or "Sino walang business permit?". Returns landlord IDs, names, emails, and which specific documents are missing.',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'document_type' => [
                        'type' => 'string',
                        'enum' => ['all', 'business_permit', 'safety_certificate'],
                        'description' => 'Filter by missing document type (default: all).',
                    ],
                    'limit' => [
                        'type' => 'integer',
                        'description' => 'Max number of landlords to return (default: 15).',
                    ],
                ],
            ],
            'allowed_roles' => ['admin'],
            'handler' => [DocumentTools::class, 'getLandlordsWithMissingDocuments'],
            'read_only' => true,
        ]);

        $this->register([
            'name' => 'get_pending_landlord_documents',
            'description' => 'Returns landlords with submitted compliance documents currently waiting for administrator review and approval. Use when an admin asks "May pending permit ba?", "Ilan pending documents ng landlord?", or "Who has documents waiting for approval?". Note: pending is different from missing; pending means a document was uploaded and is awaiting approval.',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'document_type' => [
                        'type' => 'string',
                        'enum' => ['all', 'business_permit', 'safety_certificate'],
                        'description' => 'Filter by pending document type (default: all).',
                    ],
                    'limit' => [
                        'type' => 'integer',
                        'description' => 'Max number of landlords to return (default: 15).',
                    ],
                ],
            ],
            'allowed_roles' => ['admin'],
            'handler' => [DocumentTools::class, 'getPendingLandlordDocuments'],
            'read_only' => true,
        ]);

        $this->register([
            'name' => 'get_document_verification_statistics',
            'description' => 'Retrieves verification queue counts for student IDs and landlord business permits pending admin review.',
            'parameters' => [
                'type' => 'object',
                'properties' => [],
            ],
            'allowed_roles' => ['admin'],
            'handler' => [DocumentTools::class, 'getDocumentVerificationStatistics'],
            'read_only' => true,
        ]);

        $this->register([
            'name' => 'get_my_document_status',
            'description' => 'Returns the user\'s document verification status (e.g. school ID verification for students, or business permit / profile status for landlords).',
            'parameters' => [
                'type' => 'object',
                'properties' => [],
            ],
            'allowed_roles' => ['student', 'landlord'],
            'handler' => [DocumentTools::class, 'getMyDocumentStatus'],
            'read_only' => true,
        ]);

        // ---------------------------------------------------------------------
        // ONBOARDING
        // ---------------------------------------------------------------------
        $this->register([
            'name' => 'get_onboarding_statistics',
            'description' => 'Returns tenant onboarding statistics: total onboardings, completed, pending, and in progress.',
            'parameters' => [
                'type' => 'object',
                'properties' => [],
            ],
            'allowed_roles' => ['landlord', 'admin'],
            'handler' => [OnboardingTools::class, 'getOnboardingStatistics'],
            'read_only' => true,
        ]);

        $this->register([
            'name' => 'get_my_onboarding_status',
            'description' => 'Checks tenant onboarding progress for the current student: contract signed, documents uploaded, and deposit paid. Use when asked "Ano status ng onboarding ko?", etc.',
            'parameters' => [
                'type' => 'object',
                'properties' => [],
            ],
            'allowed_roles' => ['student'],
            'handler' => [OnboardingTools::class, 'getMyOnboardingStatus'],
            'read_only' => true,
        ]);

        // ---------------------------------------------------------------------
        // DASHBOARD & SYSTEM SUMMARY
        // ---------------------------------------------------------------------
        $this->register([
            'name' => 'get_dashboard_statistics',
            'description' => 'Retrieves comprehensive dashboard KPI statistics tailored to the authenticated user\'s role (e.g. admin overview, landlord property summary, or student stay status).',
            'parameters' => [
                'type' => 'object',
                'properties' => [],
            ],
            'allowed_roles' => ['student', 'landlord', 'admin'],
            'handler' => [DashboardTools::class, 'getDashboardStatistics'],
            'read_only' => true,
        ]);

        $this->register([
            'name' => 'get_system_summary',
            'description' => 'Returns a high-level snapshot summary of the entire OBHS platform (counts of students, landlords, properties, pending approvals, unresolved reports). Use when asked "Summarize the system status today", "System overview", etc.',
            'parameters' => [
                'type' => 'object',
                'properties' => [],
            ],
            'allowed_roles' => ['admin', 'landlord'],
            'handler' => [DashboardTools::class, 'getSystemSummary'],
            'read_only' => true,
        ]);

        $this->register([
            'name' => 'get_boarding_statistics',
            'description' => 'Retrieves verified boarding monitoring statistics: unique students, active boardings, active tenants, checked-out tenants, active rooms, and active properties using official monitoring metrics.',
            'parameters' => [
                'type' => 'object',
                'properties' => [],
            ],
            'allowed_roles' => ['admin'],
            'handler' => [DashboardTools::class, 'getBoardingStatistics'],
            'read_only' => true,
        ]);
    }

    /**
     * Register a new tool definition.
     */
    public function register(array $tool): void
    {
        $this->tools[$tool['name']] = $tool;
    }

    /**
     * Get all registered tools.
     */
    public function getAllTools(): array
    {
        return $this->tools;
    }

    /**
     * Find a tool by name.
     */
    public function getTool(string $name): ?array
    {
        return $this->tools[$name] ?? null;
    }

    /**
     * Check if a role is authorized to invoke a tool.
     */
    public function isAuthorized(string $toolName, string $role): bool
    {
        $tool = $this->getTool($toolName);
        if (!$tool) {
            return false;
        }

        $allowedRoles = (array) ($tool['allowed_roles'] ?? []);

        return in_array(strtolower($role), array_map('strtolower', $allowedRoles), true);
    }

    /**
     * Return OpenAI-compatible function calling schemas filtered by user role.
     */
    public function getToolsForRole(string $role): array
    {
        $output = [];

        foreach ($this->tools as $tool) {
            if (!$this->isAuthorized($tool['name'], $role)) {
                continue;
            }

            $properties = $tool['parameters']['properties'] ?? [];
            $required = $tool['parameters']['required'] ?? [];

            $output[] = [
                'type' => 'function',
                'function' => [
                    'name' => $tool['name'],
                    'description' => $tool['description'],
                    'parameters' => [
                        'type' => 'object',
                        'properties' => empty($properties) ? new \stdClass() : $properties,
                        'required' => $required,
                    ],
                ],
            ];
        }

        return $output;
    }
}
