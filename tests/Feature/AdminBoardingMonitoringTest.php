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
    // PHASE 6 TESTS: Access Control, Filtering, Search, Pagination
    // =========================================================================

    public function test_01_admin_can_access_boarding_monitoring(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)
            ->get(route('admin.boarding_monitoring.students'));

        $response->assertOk()
            ->assertSee('Boarding Monitoring')
            ->assertSee('Unique Students')
            ->assertSee('College &amp; Program Distribution', false);
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

    public function test_04_property_filter_isolates_records_for_selected_boarding_house(): void
    {
        $admin = $this->createAdmin();
        $landlord = $this->createLandlord('Test Landlord');

        $propA = $this->createProperty($landlord, 'Dorm Alpha');
        $propB = $this->createProperty($landlord, 'Dorm Beta');

        $roomA = $this->createRoom($propA, 'A-101');
        $roomB = $this->createRoom($propB, 'B-201');

        $studentA = $this->createStudent('Alice in Alpha');
        $studentB = $this->createStudent('Bob in Beta');

        $this->createBooking($studentA, $roomA, '2026-01-01', '2026-12-31', 'approved');
        $this->createBooking($studentB, $roomB, '2026-01-01', '2026-12-31', 'approved');

        $response = $this->actingAs($admin)
            ->get(route('admin.boarding_monitoring.students', ['boarding_house' => $propA->id]));

        $response->assertOk()
            ->assertSee('Alice in Alpha')
            ->assertDontSee('Bob in Beta');
    }

    public function test_05_college_filter_isolates_records_by_college(): void
    {
        $admin = $this->createAdmin();
        $room = $this->createStandardRoom();

        $studentCCS = $this->createStudent('CCS Student', 'CCS', 'BSIT');
        $studentCAS = $this->createStudent('CAS Student', 'CAS', 'BA English');

        $this->createBooking($studentCCS, $room, '2026-01-01', '2026-12-31', 'approved');
        $this->createBooking($studentCAS, $room, '2026-01-01', '2026-12-31', 'approved');

        $response = $this->actingAs($admin)
            ->get(route('admin.boarding_monitoring.students', ['college' => 'CCS']));

        $response->assertOk()
            ->assertSee('CCS Student')
            ->assertDontSee('CAS Student');
    }

    public function test_06_program_filter_isolates_records_by_academic_program(): void
    {
        $admin = $this->createAdmin();
        $room = $this->createStandardRoom();

        $studentIT = $this->createStudent('IT Student', 'CCS', 'Bachelor of Science in Information Technology');
        $studentCrim = $this->createStudent('Crim Student', 'CCJE', 'Bachelor of Science in Criminology');

        $this->createBooking($studentIT, $room, '2026-01-01', '2026-12-31', 'approved');
        $this->createBooking($studentCrim, $room, '2026-01-01', '2026-12-31', 'approved');

        $response = $this->actingAs($admin)
            ->get(route('admin.boarding_monitoring.students', ['program' => 'Bachelor of Science in Information Technology']));

        $response->assertOk()
            ->assertSee('IT Student')
            ->assertDontSee('Crim Student');
    }

    public function test_07_status_filter_isolates_active_and_checked_out_stays(): void
    {
        $admin = $this->createAdmin();
        $room = $this->createStandardRoom();
        $today = now();

        $activeStudent = $this->createStudent('Active Stay');
        $checkedOutStudent = $this->createStudent('Checked Out Stay');

        // Active stay
        $this->createBooking($activeStudent, $room, $today->copy()->subMonth()->toDateString(), $today->copy()->addMonth()->toDateString(), 'approved');
        // Past stay
        $this->createBooking($checkedOutStudent, $room, $today->copy()->subMonths(3)->toDateString(), $today->copy()->subMonth()->toDateString(), 'approved');

        $responseActive = $this->actingAs($admin)
            ->get(route('admin.boarding_monitoring.students', ['status' => 'active']));

        $responseActive->assertOk()
            ->assertSee('Active Stay')
            ->assertDontSee('Checked Out Stay');

        $responseCheckedOut = $this->actingAs($admin)
            ->get(route('admin.boarding_monitoring.students', ['status' => 'checked_out']));

        $responseCheckedOut->assertOk()
            ->assertSee('Checked Out Stay')
            ->assertDontSee('Active Stay');
    }

    public function test_08_search_filter_matches_student_name_id_room_and_property(): void
    {
        $admin = $this->createAdmin();
        $landlord = $this->createLandlord('Target Landlord');
        $prop = $this->createProperty($landlord, 'Sunflower Dorm');
        $room = $this->createRoom($prop, 'SF-404');

        $targetStudent = $this->createStudent('Samantha Cruz', 'CCS', 'BSIT', '2026-0099');
        $otherStudent = $this->createStudent('John Smith', 'CAS', 'BA English', '2026-0001');

        $this->createBooking($targetStudent, $room, '2026-01-01', '2026-12-31', 'approved');
        $this->createBooking($otherStudent, $room, '2026-01-01', '2026-12-31', 'approved');

        // Search by student ID
        $this->actingAs($admin)
            ->get(route('admin.boarding_monitoring.students', ['search' => '2026-0099']))
            ->assertOk()
            ->assertSee('Samantha Cruz')
            ->assertDontSee('John Smith');

        // Search by room number
        $this->actingAs($admin)
            ->get(route('admin.boarding_monitoring.students', ['search' => 'SF-404']))
            ->assertOk()
            ->assertSee('Sunflower Dorm')
            ->assertSee('SF-404');
    }

    public function test_09_combined_filters_narrow_results_correctly(): void
    {
        $admin = $this->createAdmin();
        $landlord = $this->createLandlord('Landlord Combined');
        $propA = $this->createProperty($landlord, 'Dorm Alpha Combined');
        $propB = $this->createProperty($landlord, 'Dorm Beta Combined');

        $roomA = $this->createRoom($propA, '101');
        $roomB = $this->createRoom($propB, '201');

        $studentTarget = $this->createStudent('Target In Alpha CCS', 'CCS', 'BSIT');
        $studentWrongCollege = $this->createStudent('Other In Alpha CAS', 'CAS', 'BA English');
        $studentWrongProp = $this->createStudent('Target In Beta CCS', 'CCS', 'BSIT');

        $this->createBooking($studentTarget, $roomA, '2026-01-01', '2026-12-31', 'approved');
        $this->createBooking($studentWrongCollege, $roomA, '2026-01-01', '2026-12-31', 'approved');
        $this->createBooking($studentWrongProp, $roomB, '2026-01-01', '2026-12-31', 'approved');

        $response = $this->actingAs($admin)->get(route('admin.boarding_monitoring.students', [
            'boarding_house' => $propA->id,
            'college' => 'CCS',
        ]));

        $response->assertOk()
            ->assertSee('Target In Alpha CCS')
            ->assertDontSee('Other In Alpha CAS')
            ->assertDontSee('Target In Beta CCS');
    }

    public function test_10_pagination_preserves_query_string(): void
    {
        $admin = $this->createAdmin();
        $room = $this->createStandardRoom();

        for ($i = 1; $i <= 25; $i++) {
            $s = $this->createStudent("Student {$i}", 'CCS', 'BSIT');
            $this->createBooking($s, $room, '2026-01-01', '2026-12-31', 'approved');
        }

        $response = $this->actingAs($admin)
            ->get(route('admin.boarding_monitoring.students', ['college' => 'CCS', 'page' => 2]));

        $response->assertOk()
            ->assertSee('college=CCS');
    }

    // =========================================================================
    // PHASE 7 TESTS: Month/Year Interval Overlap & Historical Cases
    // =========================================================================

    public function test_11_historical_stay_before_month_to_after_month_is_included(): void
    {
        // Case A: check_in 2026-02-15, check_out 2026-04-02 -> March 2026 INCLUDED
        $admin = $this->createAdmin();
        $room = $this->createStandardRoom();
        $student = $this->createStudent('Case A Student');
        $this->createBooking($student, $room, '2026-02-15', '2026-04-02', 'approved');

        $response = $this->actingAs($admin)
            ->get(route('admin.boarding_monitoring.students', ['month' => 3, 'year' => 2026]));

        $response->assertOk()->assertSee('Case A Student');
    }

    public function test_12_historical_stay_starting_during_month_is_included(): void
    {
        // Starts mid-March 2026
        $admin = $this->createAdmin();
        $room = $this->createStandardRoom();
        $student = $this->createStudent('Mid March Student');
        $this->createBooking($student, $room, '2026-03-10', '2026-05-15', 'approved');

        $response = $this->actingAs($admin)
            ->get(route('admin.boarding_monitoring.students', ['month' => 3, 'year' => 2026]));

        $response->assertOk()->assertSee('Mid March Student');
    }

    public function test_13_stay_starting_after_month_is_excluded(): void
    {
        // Case C: check_in 2026-04-01 -> March 2026 EXCLUDED
        $admin = $this->createAdmin();
        $room = $this->createStandardRoom();
        $student = $this->createStudent('Case C Student');
        $this->createBooking($student, $room, '2026-04-01', '2026-08-30', 'approved');

        $response = $this->actingAs($admin)
            ->get(route('admin.boarding_monitoring.students', ['month' => 3, 'year' => 2026]));

        $response->assertOk()->assertDontSee('Case C Student');
    }

    public function test_14_stay_ending_before_month_is_excluded(): void
    {
        // Case D: check_in 2026-01-01, check_out 2026-02-28 -> March 2026 EXCLUDED
        $admin = $this->createAdmin();
        $room = $this->createStandardRoom();
        $student = $this->createStudent('Case D Student');
        $this->createBooking($student, $room, '2026-01-01', '2026-02-28', 'approved');

        $response = $this->actingAs($admin)
            ->get(route('admin.boarding_monitoring.students', ['month' => 3, 'year' => 2026]));

        $response->assertOk()->assertDontSee('Case D Student');
    }

    public function test_15_same_day_boundary_stay_is_included(): void
    {
        // Case E: check_in 2026-03-31, check_out 2026-03-31 -> March 2026 INCLUDED
        $admin = $this->createAdmin();
        $room = $this->createStandardRoom();
        $student = $this->createStudent('Case E Boundary Student');
        $this->createBooking($student, $room, '2026-03-31', '2026-03-31', 'approved');

        $response = $this->actingAs($admin)
            ->get(route('admin.boarding_monitoring.students', ['month' => 3, 'year' => 2026]));

        $response->assertOk()->assertSee('Case E Boundary Student');
    }

    public function test_16_long_stay_appears_in_every_overlapping_month(): void
    {
        $admin = $this->createAdmin();
        $room = $this->createStandardRoom();
        $student = $this->createStudent('Semester Student');
        $this->createBooking($student, $room, '2026-01-15', '2026-06-15', 'approved');

        foreach ([1, 2, 3, 4, 5, 6] as $m) {
            $this->actingAs($admin)
                ->get(route('admin.boarding_monitoring.students', ['month' => $m, 'year' => 2026]))
                ->assertOk()
                ->assertSee('Semester Student');
        }

        $this->actingAs($admin)
            ->get(route('admin.boarding_monitoring.students', ['month' => 7, 'year' => 2026]))
            ->assertOk()
            ->assertDontSee('Semester Student');
    }

    public function test_17_null_checkout_defensive_case_is_included(): void
    {
        // Case B: check_in 2026-03-20, check_out NULL -> March 2026 INCLUDED
        $admin = $this->createAdmin();
        $room = $this->createStandardRoom();
        $student = $this->createStudent('Case B Null Checkout');
        $booking = $this->createBooking($student, $room, '2026-03-20', '2026-12-31', 'approved');
        $booking->update(['check_out' => null]);

        $response = $this->actingAs($admin)
            ->get(route('admin.boarding_monitoring.students', ['month' => 3, 'year' => 2026]));

        $response->assertOk()->assertSee('Case B Null Checkout');
    }

    public function test_18_leap_year_february_dates_are_handled_correctly(): void
    {
        $admin = $this->createAdmin();
        $room = $this->createStandardRoom();

        // 2028 is a leap year; stay on Feb 29, 2028
        $studentLeap = $this->createStudent('Leap Year Student');
        $this->createBooking($studentLeap, $room, '2028-02-29', '2028-02-29', 'approved');

        $this->actingAs($admin)
            ->get(route('admin.boarding_monitoring.students', ['month' => 2, 'year' => 2028]))
            ->assertOk()
            ->assertSee('Leap Year Student');
    }

    public function test_19_checked_out_stay_remains_included_in_valid_historical_month(): void
    {
        $admin = $this->createAdmin();
        $room = $this->createStandardRoom();

        // Stay from Jan 10, 2026 to Mar 20, 2026. As of today (future), status is Checked Out.
        $student = $this->createStudent('Historically Present Student');
        $this->createBooking($student, $room, '2026-01-10', '2026-03-20', 'approved');

        $response = $this->actingAs($admin)
            ->get(route('admin.boarding_monitoring.students', ['month' => 2, 'year' => 2026]));

        $response->assertOk()
            ->assertSee('Historically Present Student');
    }

    public function test_20_early_leave_cancelled_booking_appears_in_occupied_historical_months(): void
    {
        // Case F: check_in 2026-01-10, check_out 2026-03-15, status='cancelled'
        // Occupied Jan 10 through Mar 15.
        $admin = $this->createAdmin();
        $room = $this->createStandardRoom();
        $student = $this->createStudent('Case F Early Leave Student');
        $this->createBooking($student, $room, '2026-01-10', '2026-03-15', 'cancelled');

        // February 2026 -> INCLUDED
        $this->actingAs($admin)
            ->get(route('admin.boarding_monitoring.students', ['month' => 2, 'year' => 2026]))
            ->assertOk()
            ->assertSee('Case F Early Leave Student');

        // March 2026 -> INCLUDED
        $this->actingAs($admin)
            ->get(route('admin.boarding_monitoring.students', ['month' => 3, 'year' => 2026]))
            ->assertOk()
            ->assertSee('Case F Early Leave Student');
    }

    public function test_21_early_leave_booking_disappears_after_leave_month(): void
    {
        // Case F: left on 2026-03-15. April 2026 -> EXCLUDED
        $admin = $this->createAdmin();
        $room = $this->createStandardRoom();
        $student = $this->createStudent('Case F Excluded Later');
        $this->createBooking($student, $room, '2026-01-10', '2026-03-15', 'cancelled');

        $this->actingAs($admin)
            ->get(route('admin.boarding_monitoring.students', ['month' => 4, 'year' => 2026]))
            ->assertOk()
            ->assertDontSee('Case F Excluded Later');
    }

    public function test_22_pure_cancelled_before_stay_booking_is_excluded(): void
    {
        // Cancelled before check-in date: check_in 2026-05-01, check_out 2026-05-01, status='cancelled'
        $admin = $this->createAdmin();
        $room = $this->createStandardRoom();
        $student = $this->createStudent('Pure Cancelled Student');
        $this->createBooking($student, $room, '2026-05-01', '2026-05-01', 'cancelled');

        $this->actingAs($admin)
            ->get(route('admin.boarding_monitoring.students', ['month' => 5, 'year' => 2026]))
            ->assertOk()
            ->assertDontSee('Pure Cancelled Student');
    }

    // =========================================================================
    // PHASE 8 TESTS: College & Program Distribution & Distinct Counting
    // =========================================================================

    public function test_23_college_count_uses_distinct_student_headcounts(): void
    {
        $room = $this->createStandardRoom();
        $student = $this->createStudent('Double Booking Student', 'CCS', 'BSIT');

        // Student has two distinct booking stays in the same period
        $this->createBooking($student, $room, '2026-01-01', '2026-03-01', 'approved');
        $this->createBooking($student, $room, '2026-03-02', '2026-06-01', 'approved');

        $baseQuery = $this->service->buildBaseQuery();
        $distribution = $this->service->getCollegeDistribution($baseQuery);

        $ccs = $distribution->firstWhere('college_code', 'CCS');
        $this->assertNotNull($ccs);
        // De-duplicated student count is 1, even though 2 booking rows exist
        $this->assertEquals(1, $ccs->total_students);
        $this->assertEquals(2, $ccs->total_records);
    }

    public function test_24_program_count_uses_distinct_student_headcounts(): void
    {
        $room = $this->createStandardRoom();
        $student = $this->createStudent('Double Program Student', 'CCS', 'Bachelor of Science in Information Technology');

        $this->createBooking($student, $room, '2026-01-01', '2026-03-01', 'approved');
        $this->createBooking($student, $room, '2026-03-02', '2026-06-01', 'approved');

        $baseQuery = $this->service->buildBaseQuery();
        $distribution = $this->service->getProgramDistribution($baseQuery);

        $prog = $distribution->firstWhere('program_name', 'Bachelor of Science in Information Technology');
        $this->assertNotNull($prog);
        $this->assertEquals(1, $prog->total_students);
        $this->assertEquals(2, $prog->total_records);
    }

    public function test_25_room_change_does_not_double_count_student_headcount(): void
    {
        $landlord = $this->createLandlord('Landlord Trans');
        $prop = $this->createProperty($landlord, 'Transfer Dorm');
        $room1 = $this->createRoom($prop, '101');
        $room2 = $this->createRoom($prop, '102');

        $student = $this->createStudent('Transferred Student', 'CCS', 'BSIT');

        // Stay in room 1 ending mid-Feb, then room 2 starting mid-Feb
        $this->createBooking($student, $room1, '2026-01-01', '2026-02-15', 'approved');
        $this->createBooking($student, $room2, '2026-02-15', '2026-06-01', 'approved');

        $period = $this->service->resolveReportingPeriod(2, 2026);
        $baseQuery = $this->service->buildBaseQuery([
            'periodStart' => $period['periodStart'],
            'periodEnd' => $period['periodEnd'],
        ]);

        $metrics = $this->service->getSummaryMetrics($baseQuery);
        $this->assertEquals(1, $metrics['unique_students']);
        $this->assertEquals(2, $metrics['total_records']);
    }

    public function test_26_college_counts_respect_month_and_year_filters(): void
    {
        $room = $this->createStandardRoom();
        $studentMarch = $this->createStudent('March CCS', 'CCS', 'BSIT');
        $studentJuly = $this->createStudent('July CCS', 'CCS', 'BSIT');

        $this->createBooking($studentMarch, $room, '2026-03-01', '2026-03-31', 'approved');
        $this->createBooking($studentJuly, $room, '2026-07-01', '2026-07-31', 'approved');

        $period = $this->service->resolveReportingPeriod(3, 2026);
        $baseQuery = $this->service->buildBaseQuery([
            'periodStart' => $period['periodStart'],
            'periodEnd' => $period['periodEnd'],
        ]);

        $dist = $this->service->getCollegeDistribution($baseQuery);
        $ccs = $dist->firstWhere('college_code', 'CCS');

        $this->assertNotNull($ccs);
        $this->assertEquals(1, $ccs->total_students);
    }

    public function test_27_program_counts_respect_month_and_year_filters(): void
    {
        $room = $this->createStandardRoom();
        $studentMarch = $this->createStudent('March IT', 'CCS', 'BSIT');
        $studentJuly = $this->createStudent('July IT', 'CCS', 'BSIT');

        $this->createBooking($studentMarch, $room, '2026-03-01', '2026-03-31', 'approved');
        $this->createBooking($studentJuly, $room, '2026-07-01', '2026-07-31', 'approved');

        $period = $this->service->resolveReportingPeriod(3, 2026);
        $baseQuery = $this->service->buildBaseQuery([
            'periodStart' => $period['periodStart'],
            'periodEnd' => $period['periodEnd'],
        ]);

        $dist = $this->service->getProgramDistribution($baseQuery);
        $it = $dist->firstWhere('program_name', 'BSIT');

        $this->assertNotNull($it);
        $this->assertEquals(1, $it->total_students);
    }

    public function test_28_college_counts_respect_boarding_house_filter(): void
    {
        $landlord = $this->createLandlord('BH Landlord');
        $propA = $this->createProperty($landlord, 'Dorm Alpha');
        $propB = $this->createProperty($landlord, 'Dorm Beta');

        $roomA = $this->createRoom($propA, '101');
        $roomB = $this->createRoom($propB, '201');

        $studentA = $this->createStudent('Student A', 'CCS', 'BSIT');
        $studentB = $this->createStudent('Student B', 'CCS', 'BSIT');

        $this->createBooking($studentA, $roomA, '2026-01-01', '2026-12-31', 'approved');
        $this->createBooking($studentB, $roomB, '2026-01-01', '2026-12-31', 'approved');

        $baseQuery = $this->service->buildBaseQuery(['property_id' => $propA->id]);
        $dist = $this->service->getCollegeDistribution($baseQuery);
        $ccs = $dist->firstWhere('college_code', 'CCS');

        $this->assertNotNull($ccs);
        $this->assertEquals(1, $ccs->total_students);
    }

    public function test_29_program_counts_respect_boarding_house_filter(): void
    {
        $landlord = $this->createLandlord('BH Landlord 2');
        $propA = $this->createProperty($landlord, 'Dorm One');
        $propB = $this->createProperty($landlord, 'Dorm Two');

        $roomA = $this->createRoom($propA, '101');
        $roomB = $this->createRoom($propB, '201');

        $studentA = $this->createStudent('Student A', 'CCS', 'BSIT');
        $studentB = $this->createStudent('Student B', 'CCS', 'BSIT');

        $this->createBooking($studentA, $roomA, '2026-01-01', '2026-12-31', 'approved');
        $this->createBooking($studentB, $roomB, '2026-01-01', '2026-12-31', 'approved');

        $baseQuery = $this->service->buildBaseQuery(['property_id' => $propA->id]);
        $dist = $this->service->getProgramDistribution($baseQuery);
        $it = $dist->firstWhere('program_name', 'BSIT');

        $this->assertNotNull($it);
        $this->assertEquals(1, $it->total_students);
    }

    public function test_30_null_college_appears_as_not_specified(): void
    {
        $room = $this->createStandardRoom();
        $student = $this->createStudent('No College Student');
        $student->forceFill(['college' => null])->save();

        $this->createBooking($student, $room, '2026-01-01', '2026-12-31', 'approved');

        $baseQuery = $this->service->buildBaseQuery();
        $dist = $this->service->getCollegeDistribution($baseQuery);

        $unspecified = $dist->firstWhere('college_code', 'Not specified');
        $this->assertNotNull($unspecified);
        $this->assertEquals(1, $unspecified->total_students);
    }

    public function test_31_null_program_remains_included_in_overall_totals(): void
    {
        $room = $this->createStandardRoom();
        $student = $this->createStudent('No Program Student', 'CCS');
        $student->forceFill(['program' => null])->save();

        $this->createBooking($student, $room, '2026-01-01', '2026-12-31', 'approved');

        $baseQuery = $this->service->buildBaseQuery();
        $metrics = $this->service->getSummaryMetrics($baseQuery);

        $this->assertEquals(1, $metrics['unique_students']);
    }

    public function test_32_college_drilldown_produces_correct_filtered_records(): void
    {
        $admin = $this->createAdmin();
        $room = $this->createStandardRoom();

        $studentCCS = $this->createStudent('Drilldown CCS', 'CCS', 'BSIT');
        $studentCBM = $this->createStudent('Drilldown CBM', 'CBM', 'BSHM');

        $this->createBooking($studentCCS, $room, '2026-01-01', '2026-12-31', 'approved');
        $this->createBooking($studentCBM, $room, '2026-01-01', '2026-12-31', 'approved');

        $response = $this->actingAs($admin)
            ->get(route('admin.boarding_monitoring.students', ['college' => 'CCS']));

        $response->assertOk()
            ->assertSee('Drilldown CCS')
            ->assertDontSee('Drilldown CBM');
    }

    public function test_33_program_drilldown_produces_correct_filtered_records(): void
    {
        $admin = $this->createAdmin();
        $room = $this->createStandardRoom();

        $studentIT = $this->createStudent('Drilldown IT', 'CCS', 'BSIT');
        $studentCS = $this->createStudent('Drilldown CS', 'CCS', 'BSCS');

        $this->createBooking($studentIT, $room, '2026-01-01', '2026-12-31', 'approved');
        $this->createBooking($studentCS, $room, '2026-01-01', '2026-12-31', 'approved');

        $response = $this->actingAs($admin)
            ->get(route('admin.boarding_monitoring.students', ['college' => 'CCS', 'program' => 'BSIT']));

        $response->assertOk()
            ->assertSee('Drilldown IT')
            ->assertDontSee('Drilldown CS');
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
