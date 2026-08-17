<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Property;
use App\Models\Room;
use App\Models\User;
use App\Services\BoardingMonitoringService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AdminBoardingMonitoringTest extends TestCase
{
    use RefreshDatabase;

    protected BoardingMonitoringService $service;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'filesystems.disks.r2.key' => 'test-r2-key',
            'filesystems.disks.r2.secret' => 'test-r2-secret',
            'filesystems.disks.r2.bucket' => 'test-r2-bucket',
            'filesystems.disks.r2.endpoint' => 'https://r2.test.invalid',
            'filesystems.disks.supabase.key' => 'test-supabase-key',
            'filesystems.disks.supabase.endpoint' => 'https://supabase.test.invalid',
        ]);

        \Illuminate\Support\Facades\Storage::fake('r2');
        \Illuminate\Support\Facades\Storage::fake('supabase');
        \Illuminate\Support\Facades\Storage::fake('public');

        $this->service = app(BoardingMonitoringService::class);
    }

    // =========================================================================
    // ACCESS CONTROL & GENERAL SCREEN MONITORING
    // =========================================================================

    public function test_01_admin_can_access_boarding_monitoring(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)
            ->get(route('admin.boarding_monitoring.students'));

        $response->assertOk()
            ->assertSee('Boarding Monitoring')
            ->assertSee('Unique Students')
            ->assertSee('Print Report');
    }

    public function test_02_student_cannot_access_boarding_monitoring(): void
    {
        $student = $this->createStudent('Student User');

        $response = $this->actingAs($student)
            ->get(route('admin.boarding_monitoring.students'));

        $response->assertForbidden();
    }

    public function test_03_landlord_cannot_access_admin_boarding_monitoring(): void
    {
        $landlord = $this->createLandlord('Landlord User');

        $response = $this->actingAs($landlord)
            ->get(route('admin.boarding_monitoring.students'));

        $response->assertForbidden();
    }

    // =========================================================================
    // BOARDING HOUSE FILTER (ALL VS SPECIFIC PROPERTY)
    // =========================================================================

    public function test_04_all_boarding_houses_view_returns_students_from_multiple_properties(): void
    {
        $admin = $this->createAdmin();
        $landlord = $this->createLandlord('Test Landlord');

        $propA = $this->createProperty($landlord, 'Alpha House');
        $propB = $this->createProperty($landlord, 'Beta Dorm');

        $roomA = $this->createRoom($propA, 'A-101');
        $roomB = $this->createRoom($propB, 'B-201');

        $studentA = $this->createStudent('Student Alpha');
        $studentB = $this->createStudent('Student Beta');

        $this->createBooking($studentA, $roomA, '2026-01-01', '2026-12-31', 'approved');
        $this->createBooking($studentB, $roomB, '2026-01-01', '2026-12-31', 'approved');

        // No property filter specified -> All Boarding Houses
        $response = $this->actingAs($admin)
            ->get(route('admin.boarding_monitoring.students'));

        $response->assertOk()
            ->assertSee('Student Alpha')
            ->assertSee('Student Beta')
            ->assertSee('Alpha House')
            ->assertSee('Beta Dorm');
    }

    public function test_05_specific_boarding_house_filter_isolates_records_to_that_property(): void
    {
        $admin = $this->createAdmin();
        $landlord = $this->createLandlord('Test Landlord');

        $propA = $this->createProperty($landlord, 'Alpha House');
        $propB = $this->createProperty($landlord, 'Beta Dorm');

        $roomA = $this->createRoom($propA, 'A-101');
        $roomB = $this->createRoom($propB, 'B-201');

        $studentA = $this->createStudent('Student Alpha');
        $studentB = $this->createStudent('Student Beta');

        $this->createBooking($studentA, $roomA, '2026-01-01', '2026-12-31', 'approved');
        $this->createBooking($studentB, $roomB, '2026-01-01', '2026-12-31', 'approved');

        $response = $this->actingAs($admin)
            ->get(route('admin.boarding_monitoring.students', ['boarding_house' => $propA->id]));

        $response->assertOk()
            ->assertSee('Student Alpha')
            ->assertDontSee('Student Beta');
    }

    public function test_06_property_id_query_param_preselects_boarding_house(): void
    {
        $admin = $this->createAdmin();
        $landlord = $this->createLandlord('Test Landlord');

        $prop = $this->createProperty($landlord, 'Direct Linked Dorm');
        $room = $this->createRoom($prop, 'DL-1');
        $student = $this->createStudent('Direct Linked Student');
        $this->createBooking($student, $room, '2026-01-01', '2026-12-31', 'approved');

        $response = $this->actingAs($admin)
            ->get(route('admin.boarding_monitoring.students', ['property_id' => $prop->id]));

        $response->assertOk()
            ->assertSee('Direct Linked Dorm')
            ->assertSee('Direct Linked Student');
    }

    // =========================================================================
    // REPORT BASIS 1: STAYED DURING PERIOD (OCCUPANCY OVERLAP)
    // =========================================================================

    public function test_07_stayed_during_period_includes_mid_stay_overlap(): void
    {
        // Student stayed July 15 to Sept 10. August 2026 report basis=stay -> INCLUDED
        $admin = $this->createAdmin();
        $room = $this->createStandardRoom();

        $student = $this->createStudent('Stayed July to Sept');
        $this->createBooking($student, $room, '2026-07-15', '2026-09-10', 'approved');

        $response = $this->actingAs($admin)->get(route('admin.boarding_monitoring.students', [
            'date_basis' => 'stay',
            'month' => 8,
            'year' => 2026,
        ]));

        $response->assertOk()->assertSee('Stayed July to Sept');
    }

    public function test_08_stayed_during_period_excludes_outside_spans(): void
    {
        // Student stayed Sept 1 to Dec 31. August 2026 report basis=stay -> EXCLUDED
        $admin = $this->createAdmin();
        $room = $this->createStandardRoom();

        $student = $this->createStudent('Started in September');
        $this->createBooking($student, $room, '2026-09-01', '2026-12-31', 'approved');

        $response = $this->actingAs($admin)->get(route('admin.boarding_monitoring.students', [
            'date_basis' => 'stay',
            'month' => 8,
            'year' => 2026,
        ]));

        $response->assertOk()->assertDontSee('Started in September');
    }

    // =========================================================================
    // REPORT BASIS 2: STARTED BOARDING DURING PERIOD (CHECK-IN DATE)
    // =========================================================================

    public function test_09_started_boarding_basis_excludes_earlier_checkin_even_if_still_staying(): void
    {
        // Student check-in was July 15 (ends Sept 10).
        // August 2026 report basis=check_in -> EXCLUDED because they started in July!
        $admin = $this->createAdmin();
        $room = $this->createStandardRoom();

        $studentJuly = $this->createStudent('July Move-In');
        $this->createBooking($studentJuly, $room, '2026-07-15', '2026-09-10', 'approved');

        $response = $this->actingAs($admin)->get(route('admin.boarding_monitoring.students', [
            'date_basis' => 'check_in',
            'month' => 8,
            'year' => 2026,
        ]));

        $response->assertOk()->assertDontSee('July Move-In');
    }

    public function test_10_started_boarding_basis_includes_checkin_inside_month(): void
    {
        $admin = $this->createAdmin();
        $room = $this->createStandardRoom();

        $studentAugStart = $this->createStudent('August 1 Move-In');
        $studentAugMid = $this->createStudent('August 15 Move-In');
        $studentAugEnd = $this->createStudent('August 31 Move-In');
        $studentSept = $this->createStudent('September 1 Move-In');

        $this->createBooking($studentAugStart, $room, '2026-08-01', '2026-12-31', 'approved');
        $this->createBooking($studentAugMid, $room, '2026-08-15', '2026-12-31', 'approved');
        $this->createBooking($studentAugEnd, $room, '2026-08-31', '2026-12-31', 'approved');
        $this->createBooking($studentSept, $room, '2026-09-01', '2026-12-31', 'approved');

        $response = $this->actingAs($admin)->get(route('admin.boarding_monitoring.students', [
            'date_basis' => 'check_in',
            'month' => 8,
            'year' => 2026,
        ]));

        $response->assertOk()
            ->assertSee('August 1 Move-In')
            ->assertSee('August 15 Move-In')
            ->assertSee('August 31 Move-In')
            ->assertDontSee('September 1 Move-In');
    }

    // =========================================================================
    // CUSTOM DATE RANGE FILTERING
    // =========================================================================

    public function test_11_custom_date_range_filters_correctly(): void
    {
        // Custom range: 2026-08-10 to 2026-08-25
        $admin = $this->createAdmin();
        $room = $this->createStandardRoom();

        $studentAug09 = $this->createStudent('Student Aug 9');
        $studentAug10 = $this->createStudent('Student Aug 10');
        $studentAug20 = $this->createStudent('Student Aug 20');
        $studentAug25 = $this->createStudent('Student Aug 25');
        $studentAug26 = $this->createStudent('Student Aug 26');

        $this->createBooking($studentAug09, $room, '2026-08-09', '2026-12-31', 'approved');
        $this->createBooking($studentAug10, $room, '2026-08-10', '2026-12-31', 'approved');
        $this->createBooking($studentAug20, $room, '2026-08-20', '2026-12-31', 'approved');
        $this->createBooking($studentAug25, $room, '2026-08-25', '2026-12-31', 'approved');
        $this->createBooking($studentAug26, $room, '2026-08-26', '2026-12-31', 'approved');

        $response = $this->actingAs($admin)->get(route('admin.boarding_monitoring.students', [
            'date_basis' => 'check_in',
            'date_from' => '2026-08-10',
            'date_to' => '2026-08-25',
        ]));

        $response->assertOk()
            ->assertDontSee('Student Aug 9')
            ->assertSee('Student Aug 10')
            ->assertSee('Student Aug 20')
            ->assertSee('Student Aug 25')
            ->assertDontSee('Student Aug 26');
    }

    public function test_12_custom_date_range_takes_priority_over_month_and_year(): void
    {
        $admin = $this->createAdmin();
        $room = $this->createStandardRoom();

        $studentAug = $this->createStudent('Student in August');
        $studentDec = $this->createStudent('Student in December');

        $this->createBooking($studentAug, $room, '2026-08-15', '2026-12-31', 'approved');
        $this->createBooking($studentDec, $room, '2026-12-05', '2026-12-31', 'approved');

        // Pass month=8 but custom date_from=2026-12-01, date_to=2026-12-31
        $response = $this->actingAs($admin)->get(route('admin.boarding_monitoring.students', [
            'date_basis' => 'check_in',
            'month' => 8,
            'year' => 2026,
            'date_from' => '2026-12-01',
            'date_to' => '2026-12-31',
        ]));

        $response->assertOk()
            ->assertSee('Student in December')
            ->assertDontSee('Student in August');
    }

    // =========================================================================
    // EARLY LEAVE HISTORICAL OCCUPANCY RULES
    // =========================================================================

    public function test_13_early_leave_cancelled_stay_appears_in_occupied_window(): void
    {
        // Early leave: check_in 2026-01-10, check_out 2026-03-15, status='cancelled'
        $admin = $this->createAdmin();
        $room = $this->createStandardRoom();
        $student = $this->createStudent('Early Leave Occupant');
        $this->createBooking($student, $room, '2026-01-10', '2026-03-15', 'cancelled');

        // February 2026 -> INCLUDED in stay basis
        $this->actingAs($admin)->get(route('admin.boarding_monitoring.students', [
            'date_basis' => 'stay',
            'month' => 2,
            'year' => 2026,
        ]))->assertOk()->assertSee('Early Leave Occupant');

        // April 2026 -> EXCLUDED
        $this->actingAs($admin)->get(route('admin.boarding_monitoring.students', [
            'date_basis' => 'stay',
            'month' => 4,
            'year' => 2026,
        ]))->assertOk()->assertDontSee('Early Leave Occupant');
    }

    public function test_14_pure_cancellation_before_stay_is_excluded(): void
    {
        $admin = $this->createAdmin();
        $room = $this->createStandardRoom();
        $student = $this->createStudent('Cancelled No Show');
        $this->createBooking($student, $room, '2026-05-01', '2026-05-01', 'cancelled');

        $this->actingAs($admin)->get(route('admin.boarding_monitoring.students', [
            'date_basis' => 'stay',
            'month' => 5,
            'year' => 2026,
        ]))->assertOk()->assertDontSee('Cancelled No Show');
    }

    // =========================================================================
    // COLLEGE & PROGRAM REPORTING WITH PROPERTY & TIME FILTERS
    // =========================================================================

    public function test_15_academic_distributions_respect_all_active_filters(): void
    {
        $admin = $this->createAdmin();
        $landlord = $this->createLandlord('Academic Landlord');
        $propA = $this->createProperty($landlord, 'CCS Dorm Alpha');
        $propB = $this->createProperty($landlord, 'CAS Dorm Beta');

        $roomA = $this->createRoom($propA, '101');
        $roomB = $this->createRoom($propB, '201');

        $studentA = $this->createStudent('CCS Alpha Student', 'CCS', 'Bachelor of Science in Information Technology');
        $studentB = $this->createStudent('CAS Beta Student', 'CAS', 'Bachelor of Arts in English Language');

        $this->createBooking($studentA, $roomA, '2026-08-01', '2026-12-31', 'approved');
        $this->createBooking($studentB, $roomB, '2026-08-01', '2026-12-31', 'approved');

        $baseQuery = $this->service->buildBaseQuery([
            'property_id' => $propA->id,
            'dateBasis' => 'check_in',
            'periodStart' => Carbon::create(2026, 8, 1)->startOfDay(),
            'periodEnd' => Carbon::create(2026, 8, 31)->endOfDay(),
        ]);

        $dist = $this->service->getCollegeDistribution($baseQuery);
        $this->assertCount(1, $dist);
        $this->assertEquals('CCS', $dist->first()->college_code);
        $this->assertEquals(1, $dist->first()->total_students);
    }

    // =========================================================================
    // PRINTABLE REPORT TESTS
    // =========================================================================

    public function test_16_admin_can_access_printable_report(): void
    {
        $admin = $this->createAdmin();
        $room = $this->createStandardRoom();
        $student = $this->createStudent('Printable Student');
        $this->createBooking($student, $room, '2026-01-01', '2026-12-31', 'approved');

        $response = $this->actingAs($admin)
            ->get(route('admin.boarding_monitoring.students.print'));

        $response->assertOk()
            ->assertSee('Boarding House Student Report')
            ->assertSee('Printable Student')
            ->assertSee('All Boarding Houses');
    }

    public function test_17_non_admin_cannot_access_printable_report(): void
    {
        $student = $this->createStudent('Student Only');

        $response = $this->actingAs($student)
            ->get(route('admin.boarding_monitoring.students.print'));

        $response->assertForbidden();
    }

    public function test_18_print_report_includes_complete_unpaginated_filtered_dataset(): void
    {
        $admin = $this->createAdmin();
        $landlord = $this->createLandlord('Large Dorm Landlord');
        $prop = $this->createProperty($landlord, 'Grand Dormitory');
        $room = $this->createRoom($prop, 'G-100');

        // Create 25 students (which exceeds single screen page of 20)
        for ($i = 1; $i <= 25; $i++) {
            $s = $this->createStudent("Grand Student {$i}", 'CCS', 'BSIT');
            $this->createBooking($s, $room, '2026-08-01', '2026-12-31', 'approved');
        }

        $response = $this->actingAs($admin)->get(route('admin.boarding_monitoring.students.print', [
            'boarding_house' => $prop->id,
            'month' => 8,
            'year' => 2026,
        ]));

        $response->assertOk()
            ->assertSee('Grand Dormitory')
            ->assertSee('Grand Student 1')
            ->assertSee('Grand Student 25')
            ->assertSee('Total Boarding Records')
            ->assertSee('25');
    }

    public function test_19_print_report_respects_all_selected_filters(): void
    {
        $admin = $this->createAdmin();
        $landlord = $this->createLandlord('Target Landlord');
        $propA = $this->createProperty($landlord, 'Target House');
        $propB = $this->createProperty($landlord, 'Ignored House');

        $roomA = $this->createRoom($propA, '101');
        $roomB = $this->createRoom($propB, '201');

        $studentTarget = $this->createStudent('Target Print Student', 'CCS', 'BSIT');
        $studentIgnored = $this->createStudent('Ignored Print Student', 'CAS', 'BA English');

        $this->createBooking($studentTarget, $roomA, '2026-08-15', '2026-12-31', 'approved');
        $this->createBooking($studentIgnored, $roomB, '2026-08-15', '2026-12-31', 'approved');

        $response = $this->actingAs($admin)->get(route('admin.boarding_monitoring.students.print', [
            'boarding_house' => $propA->id,
            'college' => 'CCS',
            'date_basis' => 'check_in',
            'month' => 8,
            'year' => 2026,
        ]));

        $response->assertOk()
            ->assertSee('Target Print Student')
            ->assertDontSee('Ignored Print Student')
            ->assertSee('Target House');
    }

    // =========================================================================
    // HELPER METHODS
    // =========================================================================

    protected function createAdmin(): User
    {
        $admin = User::create([
            'name' => 'Admin User',
            'full_name' => 'Admin User',
            'email' => 'admin_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'onboarding_complete' => true,
        ]);
        $admin->forceFill(['email_verified_at' => now()])->save();
        return $admin;
    }

    protected function createLandlord(string $name): User
    {
        $landlord = User::create([
            'name' => $name,
            'full_name' => $name,
            'email' => 'landlord_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'landlord',
            'contact_number' => '09171234567',
            'boarding_house_name' => $name . ' Dorm',
            'onboarding_complete' => true,
        ]);
        $landlord->forceFill(['email_verified_at' => now()])->save();
        return $landlord;
    }

    protected function createStudent(
        string $name,
        ?string $college = 'CCS',
        ?string $program = 'BSIT',
        ?string $studentId = null
    ): User {
        $student = User::create([
            'name' => $name,
            'full_name' => $name,
            'email' => 'student_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'student',
            'student_id' => $studentId ?: 'ID-' . uniqid(),
            'college' => $college,
            'program' => $program,
            'contact_number' => '09181234567',
            'onboarding_complete' => true,
        ]);
        $student->forceFill(['email_verified_at' => now()])->save();
        return $student;
    }

    protected function createProperty(User $landlord, string $name = 'Test Property'): Property
    {
        return Property::create([
            'landlord_id' => $landlord->id,
            'name' => $name,
            'address' => '123 Main Street',
            'approval_status' => 'approved',
        ]);
    }

    protected function createRoom(Property $property, string $roomNumber = '101'): Room
    {
        return Room::create([
            'property_id' => $property->id,
            'room_number' => $roomNumber,
            'capacity' => 4,
            'slots_available' => 4,
            'price' => 3500,
            'status' => 'available',
        ]);
    }

    protected function createStandardRoom(): Room
    {
        $landlord = $this->createLandlord('Default Landlord');
        $property = $this->createProperty($landlord, 'Standard Dorm');
        return $this->createRoom($property, '101');
    }

    protected function createBooking(
        User $student,
        Room $room,
        string $checkIn,
        string $checkOut,
        string $status = 'approved'
    ): Booking {
        return Booking::create([
            'student_id' => $student->id,
            'room_id' => $room->id,
            'status' => $status,
            'check_in' => $checkIn,
            'check_out' => $checkOut,
            'monthly_rent_amount' => 3500,
        ]);
    }
}
