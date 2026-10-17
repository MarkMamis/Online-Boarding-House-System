<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Property;
use App\Models\Room;
use App\Models\User;
use App\Services\BoardingMonitoringService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAnalyticsStrictSqlCompatibilityTest extends TestCase
{
    use RefreshDatabase;

    protected BoardingMonitoringService $monitoringService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->monitoringService = app(BoardingMonitoringService::class);
    }

    protected function createAdmin(): User
    {
        return User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);
    }

    protected function createPropertyAndRoom(string $propertyName = 'Sunrise Boarding House'): array
    {
        $landlord = User::factory()->create([
            'role' => 'landlord',
            'email_verified_at' => now(),
        ]);

        $property = Property::create([
            'landlord_id' => $landlord->id,
            'name' => $propertyName,
            'address' => 'Calapan City',
            'approval_status' => 'approved',
        ]);

        $room = Room::create([
            'property_id' => $property->id,
            'room_number' => '101',
            'capacity' => 4,
            'slots_available' => 2,
            'price' => 2000,
            'status' => 'available',
        ]);

        return [$property, $room];
    }

    protected function createStudentWithAcademic(
        string $name,
        ?string $college,
        ?string $program
    ): User {
        return User::factory()->create([
            'name' => $name,
            'role' => 'student',
            'college' => $college,
            'program' => $program,
            'email_verified_at' => now(),
        ]);
    }

    protected function createBooking(
        User $student,
        Room $room,
        string $checkIn,
        ?string $checkOut = null,
        string $status = 'approved'
    ): Booking {
        return Booking::create([
            'student_id' => $student->id,
            'room_id' => $room->id,
            'check_in' => $checkIn,
            'check_out' => $checkOut,
            'status' => $status,
        ]);
    }

    public function test_no_active_boarders_returns_empty_collections_cleanly(): void
    {
        $baseQuery = $this->monitoringService->buildBaseQuery([]);

        $colleges = $this->monitoringService->getCollegeDistribution($baseQuery);
        $programs = $this->monitoringService->getProgramDistribution($baseQuery);

        $this->assertTrue($colleges->isEmpty());
        $this->assertTrue($programs->isEmpty());
    }

    public function test_one_boarded_student_in_college(): void
    {
        [, $room] = $this->createPropertyAndRoom();
        $student = $this->createStudentWithAcademic('Juan Cruz', 'CCS', 'Bachelor of Science in Information Technology');
        $this->createBooking($student, $room, '2026-08-01', '2026-12-31');

        $baseQuery = $this->monitoringService->buildBaseQuery([]);

        $colleges = $this->monitoringService->getCollegeDistribution($baseQuery);
        $this->assertCount(1, $colleges);
        $this->assertEquals('CCS', $colleges->first()->college_code);
        $this->assertEquals(1, $colleges->first()->total_students);
        $this->assertEquals(1, $colleges->first()->total_records);

        $programs = $this->monitoringService->getProgramDistribution($baseQuery);
        $this->assertCount(1, $programs);
        $this->assertEquals('CCS', $programs->first()->college_code);
        $this->assertEquals('Bachelor of Science in Information Technology', $programs->first()->program_name);
        $this->assertEquals(1, $programs->first()->total_students);
        $this->assertEquals(1, $programs->first()->total_records);
    }

    public function test_multiple_students_in_one_college(): void
    {
        [, $room] = $this->createPropertyAndRoom();
        $student1 = $this->createStudentWithAcademic('Student 1', 'CCS', 'BSIT');
        $student2 = $this->createStudentWithAcademic('Student 2', 'CCS', 'BSCS');
        $this->createBooking($student1, $room, '2026-08-01', '2026-12-31');
        $this->createBooking($student2, $room, '2026-08-01', '2026-12-31');

        $baseQuery = $this->monitoringService->buildBaseQuery([]);

        $colleges = $this->monitoringService->getCollegeDistribution($baseQuery);
        $this->assertCount(1, $colleges);
        $this->assertEquals('CCS', $colleges->first()->college_code);
        $this->assertEquals(2, $colleges->first()->total_students);
        $this->assertEquals(2, $colleges->first()->total_records);

        $programs = $this->monitoringService->getProgramDistribution($baseQuery);
        $this->assertCount(2, $programs);
    }

    public function test_multiple_colleges_with_multiple_students_sorted_by_total_students_desc(): void
    {
        [, $room] = $this->createPropertyAndRoom();
        $s1 = $this->createStudentWithAcademic('S1', 'CCS', 'BSIT');
        $s2 = $this->createStudentWithAcademic('S2', 'CCS', 'BSIT');
        $s3 = $this->createStudentWithAcademic('S3', 'CAS', 'BS Psychology');

        $this->createBooking($s1, $room, '2026-08-01', '2026-12-31');
        $this->createBooking($s2, $room, '2026-08-01', '2026-12-31');
        $this->createBooking($s3, $room, '2026-08-01', '2026-12-31');

        $baseQuery = $this->monitoringService->buildBaseQuery([]);
        $colleges = $this->monitoringService->getCollegeDistribution($baseQuery);

        $this->assertCount(2, $colleges);
        $this->assertEquals('CCS', $colleges->first()->college_code);
        $this->assertEquals(2, $colleges->first()->total_students);
        $this->assertEquals('CAS', $colleges->last()->college_code);
        $this->assertEquals(1, $colleges->last()->total_students);
    }

    public function test_one_student_with_multiple_historical_stays_counts_distinct_student_once_and_records_correctly(): void
    {
        [, $room] = $this->createPropertyAndRoom();
        $student = $this->createStudentWithAcademic('Frequent Boarder', 'CCS', 'BSIT');

        // Student has 3 separate stays
        $this->createBooking($student, $room, '2026-01-01', '2026-03-31', 'approved');
        $this->createBooking($student, $room, '2026-04-01', '2026-06-30', 'approved');
        $this->createBooking($student, $room, '2026-07-01', '2026-12-31', 'approved');

        $baseQuery = $this->monitoringService->buildBaseQuery([]);
        $colleges = $this->monitoringService->getCollegeDistribution($baseQuery);

        $this->assertCount(1, $colleges);
        $this->assertEquals('CCS', $colleges->first()->college_code);
        // Student count should be deduplicated to 1
        $this->assertEquals(1, $colleges->first()->total_students);
        // Total stay/booking records should be 3
        $this->assertEquals(3, $colleges->first()->total_records);
    }

    public function test_null_empty_and_whitespace_variants_of_college_are_normalized_to_not_specified(): void
    {
        [, $room] = $this->createPropertyAndRoom();

        $studentNull = $this->createStudentWithAcademic('Null College', null, null);
        $studentEmpty = $this->createStudentWithAcademic('Empty College', '', '');
        $studentSpaces = $this->createStudentWithAcademic('Spaces College', '   ', '   ');
        $studentTabs = $this->createStudentWithAcademic('Tabs College', "\t\n", "\t\n");

        $this->createBooking($studentNull, $room, '2026-08-01', '2026-12-31');
        $this->createBooking($studentEmpty, $room, '2026-08-01', '2026-12-31');
        $this->createBooking($studentSpaces, $room, '2026-08-01', '2026-12-31');
        $this->createBooking($studentTabs, $room, '2026-08-01', '2026-12-31');

        $baseQuery = $this->monitoringService->buildBaseQuery([]);
        $colleges = $this->monitoringService->getCollegeDistribution($baseQuery);

        // All 4 students must be grouped under a single 'Not specified' group
        $this->assertCount(1, $colleges);
        $this->assertEquals('Not specified', $colleges->first()->college_code);
        $this->assertEquals('Not specified', $colleges->first()->college_name);
        $this->assertEquals(4, $colleges->first()->total_students);
        $this->assertEquals(4, $colleges->first()->total_records);
    }

    public function test_unknown_or_unlisted_college_code_is_preserved_and_grouped(): void
    {
        [, $room] = $this->createPropertyAndRoom();
        $student = $this->createStudentWithAcademic('External College', 'CUSTOM-COLLEGE', 'Custom Program');
        $this->createBooking($student, $room, '2026-08-01', '2026-12-31');

        $baseQuery = $this->monitoringService->buildBaseQuery([]);
        $colleges = $this->monitoringService->getCollegeDistribution($baseQuery);

        $this->assertCount(1, $colleges);
        $this->assertEquals('CUSTOM-COLLEGE', $colleges->first()->college_code);
        $this->assertEquals('CUSTOM-COLLEGE', $colleges->first()->college_name);
        $this->assertEquals(1, $colleges->first()->total_students);
    }

    public function test_program_distribution_infers_college_when_college_is_not_specified(): void
    {
        [, $room] = $this->createPropertyAndRoom();
        // Program is IT which belongs to CCS catalog, but college is blank
        $student = $this->createStudentWithAcademic('Inferred Student', '', 'Bachelor of Science in Information Technology');
        $this->createBooking($student, $room, '2026-08-01', '2026-12-31');

        $baseQuery = $this->monitoringService->buildBaseQuery([]);
        $programs = $this->monitoringService->getProgramDistribution($baseQuery);

        $this->assertCount(1, $programs);
        $this->assertEquals('CCS', $programs->first()->college_code);
        $this->assertEquals('Bachelor of Science in Information Technology', $programs->first()->program_name);
        $this->assertEquals(1, $programs->first()->total_students);
    }

    public function test_different_reporting_periods_filter_distribution_accurately(): void
    {
        [, $room] = $this->createPropertyAndRoom();
        $studentAug = $this->createStudentWithAcademic('Aug Student', 'CCS', 'BSIT');
        $studentNov = $this->createStudentWithAcademic('Nov Student', 'CAS', 'BS Psychology');

        // Aug only stay
        $this->createBooking($studentAug, $room, '2026-08-01', '2026-08-31');
        // Nov only stay
        $this->createBooking($studentNov, $room, '2026-11-01', '2026-11-30');

        // Query for August 2026
        $augQuery = $this->monitoringService->buildBaseQuery([
            'dateBasis' => 'stay',
            'periodStart' => Carbon::create(2026, 8, 1)->startOfDay(),
            'periodEnd' => Carbon::create(2026, 8, 31)->endOfDay(),
        ]);

        $augColleges = $this->monitoringService->getCollegeDistribution($augQuery);
        $this->assertCount(1, $augColleges);
        $this->assertEquals('CCS', $augColleges->first()->college_code);
        $this->assertEquals(1, $augColleges->first()->total_students);

        // Query for November 2026
        $novQuery = $this->monitoringService->buildBaseQuery([
            'dateBasis' => 'stay',
            'periodStart' => Carbon::create(2026, 11, 1)->startOfDay(),
            'periodEnd' => Carbon::create(2026, 11, 30)->endOfDay(),
        ]);

        $novColleges = $this->monitoringService->getCollegeDistribution($novQuery);
        $this->assertCount(1, $novColleges);
        $this->assertEquals('CAS', $novColleges->first()->college_code);
        $this->assertEquals(1, $novColleges->first()->total_students);
    }

    public function test_admin_analytics_http_get_returns_200_and_renders_demographics(): void
    {
        $admin = $this->createAdmin();
        [, $room] = $this->createPropertyAndRoom();
        $student = $this->createStudentWithAcademic('Active Boarder', 'CCS', 'BSIT');
        $this->createBooking($student, $room, Carbon::now()->subMonth()->toDateString(), Carbon::now()->addMonths(3)->toDateString());

        $response = $this->actingAs($admin)->get(route('admin.analytics.index'));

        $response->assertOk();
        $response->assertViewIs('admin.analytics.index');
        $response->assertViewHas('demographics');
        $demographics = $response->viewData('demographics');
        $this->assertCount(1, $demographics['colleges']);
        $this->assertEquals('CCS', $demographics['colleges']->first()->college_code);
        $response->assertSee('Admin Reports & Analytics');
        $response->assertSee('College Distribution');
    }

    public function test_admin_institutional_reports_academic_distribution_preset_returns_200(): void
    {
        $admin = $this->createAdmin();
        [, $room] = $this->createPropertyAndRoom();
        $student = $this->createStudentWithAcademic('Report Student', 'CCS', 'BSIT');
        $this->createBooking($student, $room, Carbon::now()->subMonth()->toDateString(), Carbon::now()->addMonths(3)->toDateString());

        $response = $this->actingAs($admin)->get('/admin/institutional-reports?report_preset=academic_distribution');

        $response->assertOk();
        $response->assertViewIs('admin.institutional_reports.index');
        $response->assertViewHas('reportData');
        $response->assertSee('Academic Distribution');
        $response->assertSee('CCS');
    }

    public function test_authorization_is_strictly_enforced_on_analytics_and_reports(): void
    {
        $student = User::factory()->create(['role' => 'student', 'email_verified_at' => now()]);
        $landlord = User::factory()->create(['role' => 'landlord', 'email_verified_at' => now()]);

        // Student cannot access
        $this->actingAs($student)->get(route('admin.analytics.index'))->assertForbidden();
        $this->actingAs($student)->get('/admin/institutional-reports')->assertForbidden();

        // Landlord cannot access
        $this->actingAs($landlord)->get(route('admin.analytics.index'))->assertForbidden();
        $this->actingAs($landlord)->get('/admin/institutional-reports')->assertForbidden();

        // Guest cannot access
        $this->get(route('admin.analytics.index'))->assertForbidden();
        $this->get('/admin/institutional-reports')->assertForbidden();
    }
}
