<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Property;
use App\Models\Room;
use App\Models\User;
use App\Services\BoardingMonitoringService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminBoardingMonitoringRefinementTest extends TestCase
{
    use RefreshDatabase;

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

        Storage::fake('r2');
        Storage::fake('supabase');
        Storage::fake('public');
    }

    public function test_default_view_is_current_boarders_showing_only_active_stays(): void
    {
        $admin = $this->createAdmin();
        $landlord = $this->createLandlord('Landlord Alpha');
        $property = Property::create([
            'landlord_id' => $landlord->id,
            'name' => 'Sunrise Villa',
            'address' => '123 College Ave',
        ]);
        $room = Room::create([
            'property_id' => $property->id,
            'room_number' => '101',
            'capacity' => 2,
            'price' => 4500,
            'status' => 'available',
        ]);

        $activeStudent = $this->createStudent('Active Student One', 'CAS', 'BA Psychology');
        $checkedOutStudent = $this->createStudent('Checked Out Student', 'CCS', 'BSCS');

        $today = Carbon::today();

        // Active booking
        Booking::create([
            'room_id' => $room->id,
            'student_id' => $activeStudent->id,
            'status' => 'approved',
            'check_in' => $today->copy()->subDays(10),
            'check_out' => $today->copy()->addDays(20),
        ]);

        // Past checked out booking
        Booking::create([
            'room_id' => $room->id,
            'student_id' => $checkedOutStudent->id,
            'status' => 'approved',
            'check_in' => $today->copy()->subDays(60),
            'check_out' => $today->copy()->subDays(10),
        ]);

        // Access root monitoring without parameters -> defaults to tab=current
        $response = $this->actingAs($admin)->get(route('admin.boarding_monitoring.students'));

        $response->assertOk()
            ->assertViewHas('activeTab', 'current')
            ->assertSee('Active Student One')
            ->assertDontSee('Checked Out Student')
            ->assertSee('Sunrise Villa')
            ->assertSee('101')
            ->assertSee('BA Psychology')
            ->assertSee(route('admin.users.students.show', $activeStudent->id))
            ->assertSee(route('admin.properties.show', $property->id));
    }

    public function test_current_boarders_empty_state_shows_helpful_guidance(): void
    {
        $admin = $this->createAdmin();
        $landlord = $this->createLandlord('Landlord Beta');
        $property = Property::create([
            'landlord_id' => $landlord->id,
            'name' => 'Sunset Dorm',
            'address' => '456 University Way',
        ]);
        $room = Room::create([
            'property_id' => $property->id,
            'room_number' => '202',
            'capacity' => 2,
            'price' => 3500,
            'status' => 'available',
        ]);

        $checkedOut = $this->createStudent('Former Boarder', 'CBM', 'BSBA');
        $today = Carbon::today();

        Booking::create([
            'room_id' => $room->id,
            'student_id' => $checkedOut->id,
            'status' => 'approved',
            'check_in' => $today->copy()->subDays(40),
            'check_out' => $today->copy()->subDays(5),
        ]);

        // When visiting Current Boarders tab with 0 active boarders
        $response = $this->actingAs($admin)->get(route('admin.boarding_monitoring.students', ['tab' => 'current']));

        $response->assertOk()
            ->assertSee('No students are currently boarding')
            ->assertSee('View Stay History')
            ->assertDontSee('Former Boarder');
    }

    public function test_stay_history_tab_displays_all_records_and_represented_property_metrics(): void
    {
        $admin = $this->createAdmin();
        $landlord = $this->createLandlord('Landlord Gamma');
        $property = Property::create([
            'landlord_id' => $landlord->id,
            'name' => 'Hillside Manor',
            'address' => '789 Summit St',
        ]);
        $room = Room::create([
            'property_id' => $property->id,
            'room_number' => '303',
            'capacity' => 3,
            'price' => 4000,
            'status' => 'available',
        ]);

        $studentA = $this->createStudent('History Student A', 'CCS', 'BSIT');
        $studentB = $this->createStudent('History Student B', 'CAS', 'BA Political Science');

        $today = Carbon::today();

        // Checked out
        Booking::create([
            'room_id' => $room->id,
            'student_id' => $studentA->id,
            'status' => 'approved',
            'check_in' => $today->copy()->subDays(50),
            'check_out' => $today->copy()->subDays(20),
        ]);

        // Cancelled
        Booking::create([
            'room_id' => $room->id,
            'student_id' => $studentB->id,
            'status' => 'cancelled',
            'check_in' => $today->copy()->subDays(10),
            'check_out' => $today->copy()->addDays(5),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.boarding_monitoring.students', [
            'tab' => 'history',
            'period_preset' => 'all',
        ]));

        $response->assertOk()
            ->assertViewHas('activeTab', 'history')
            ->assertSee('History Student A')
            ->assertSee('History Student B')
            ->assertSee('Hillside Manor')
            ->assertSee('Checked Out')
            ->assertSee('Cancelled')
            ->assertSee('Print Report');
    }

    public function test_period_preset_resolves_dates_correctly(): void
    {
        $service = app(BoardingMonitoringService::class);

        $now = Carbon::now();

        // Preset this_month
        $resolved = $service->resolveReportingPeriod(
            periodPreset: 'this_month'
        );
        $this->assertEquals($now->copy()->startOfMonth()->toDateString(), $resolved['periodStart']->toDateString());
        $this->assertEquals($now->copy()->endOfMonth()->toDateString(), $resolved['periodEnd']->toDateString());

        // Preset last_month
        $resolvedLast = $service->resolveReportingPeriod(
            periodPreset: 'last_month'
        );
        $lastMonth = $now->copy()->subMonthNoOverflow();
        $this->assertEquals($lastMonth->copy()->startOfMonth()->toDateString(), $resolvedLast['periodStart']->toDateString());
        $this->assertEquals($lastMonth->copy()->endOfMonth()->toDateString(), $resolvedLast['periodEnd']->toDateString());

        // Preset specific_month
        $resolvedSpecific = $service->resolveReportingPeriod(
            month: 4,
            year: 2026,
            periodPreset: 'specific_month'
        );
        $this->assertEquals('2026-04-01', $resolvedSpecific['periodStart']->toDateString());
        $this->assertEquals('2026-04-30', $resolvedSpecific['periodEnd']->toDateString());

        // Preset custom
        $resolvedCustom = $service->resolveReportingPeriod(
            dateFrom: '2026-02-10',
            dateTo: '2026-03-15',
            periodPreset: 'custom'
        );
        $this->assertEquals('2026-02-10', $resolvedCustom['periodStart']->toDateString());
        $this->assertEquals('2026-03-15', $resolvedCustom['periodEnd']->toDateString());

        // Preset all
        $resolvedAll = $service->resolveReportingPeriod(
            periodPreset: 'all'
        );
        $this->assertNull($resolvedAll['periodStart']);
        $this->assertNull($resolvedAll['periodEnd']);
    }

    public function test_analytics_tab_renders_distributions_and_preserves_tab(): void
    {
        $admin = $this->createAdmin();
        $landlord = $this->createLandlord('Landlord Delta');
        $property = Property::create([
            'landlord_id' => $landlord->id,
            'name' => 'Metro Living',
            'address' => '999 Boulevard',
        ]);
        $room = Room::create([
            'property_id' => $property->id,
            'room_number' => '404',
            'capacity' => 4,
            'price' => 5000,
            'status' => 'available',
        ]);

        $student = $this->createStudent('Analytics Scholar', 'CCS', 'BSCS');
        $today = Carbon::today();

        Booking::create([
            'room_id' => $room->id,
            'student_id' => $student->id,
            'status' => 'approved',
            'check_in' => $today->copy()->subDays(5),
            'check_out' => $today->copy()->addDays(25),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.boarding_monitoring.students', [
            'tab' => 'analytics',
            'period_preset' => 'all',
        ]));

        $response->assertOk()
            ->assertViewHas('activeTab', 'analytics')
            ->assertSee('Students by College')
            ->assertSee('Students by Program')
            ->assertSee('Students by Boarding House')
            ->assertSee('Metro Living')
            ->assertSee('CCS');
    }

    public function test_print_route_works_with_history_parameters(): void
    {
        $admin = $this->createAdmin();
        $landlord = $this->createLandlord('Landlord Echo');
        $property = Property::create([
            'landlord_id' => $landlord->id,
            'name' => 'Royal Court',
            'address' => '100 King St',
        ]);
        $room = Room::create([
            'property_id' => $property->id,
            'room_number' => '505',
            'capacity' => 1,
            'price' => 6000,
            'status' => 'available',
        ]);

        $student = $this->createStudent('Printable Student', 'CAS', 'BA History');
        $today = Carbon::today();

        Booking::create([
            'room_id' => $room->id,
            'student_id' => $student->id,
            'status' => 'approved',
            'check_in' => $today->copy()->subDays(2),
            'check_out' => $today->copy()->addDays(10),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.boarding_monitoring.students.print', [
            'boarding_house' => $property->id,
            'college' => 'CAS',
            'period_preset' => 'all',
        ]));

        $response->assertOk()
            ->assertSee('Boarding House Student Report')
            ->assertSee('Printable Student')
            ->assertSee('Royal Court');
    }

    public function test_get_available_years_returns_distinct_integers(): void
    {
        $service = app(BoardingMonitoringService::class);
        $years = $service->getAvailableYears();

        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $years);
        $this->assertNotEmpty($years);
        foreach ($years as $year) {
            $this->assertIsInt($year);
        }
    }

    protected function createAdmin(): User
    {
        return $this->createUser('admin', 'Admin Staff');
    }

    protected function createLandlord(string $name): User
    {
        return $this->createUser('landlord', $name);
    }

    protected function createStudent(string $name, string $college, string $program): User
    {
        $user = $this->createUser('student', $name);
        $user->forceFill([
            'college' => $college,
            'program' => $program,
            'student_id' => 'STU-' . rand(1000, 9999),
        ])->save();

        return $user->refresh();
    }

    protected function createUser(string $role, string $name): User
    {
        $user = User::create([
            'full_name' => $name,
            'name' => $name,
            'email' => strtolower(str_replace(' ', '.', $name)) . '_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => $role,
            'contact_number' => '0917000' . rand(1000, 9999),
            'boarding_house_name' => 'Demo House',
            'onboarding_complete' => true,
        ]);

        $user->forceFill(['email_verified_at' => now()])->save();

        return $user->refresh();
    }
}
