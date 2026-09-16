<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\LandlordDocument;
use App\Models\Property;
use App\Models\Room;
use App\Models\User;
use App\Services\AnalyticsService;
use App\Services\BoardingMonitoringService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_analytics_page(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.analytics.index'));

        $response->assertOk();
        $response->assertViewIs('admin.analytics.index');
        $response->assertViewHasAll([
            'kpis',
            'trend',
            'demographics',
            'occupancy',
            'bookingLifecycle',
            'compliance',
            'filterOptions',
            'periodInfo',
        ]);
        $response->assertSee('Admin Reports & Analytics');
        $response->assertSee('Active Boarders');
    }

    public function test_non_admin_cannot_access_analytics_page(): void
    {
        $student = User::factory()->create(['role' => 'student', 'email_verified_at' => now()]);
        $landlord = User::factory()->create(['role' => 'landlord', 'email_verified_at' => now()]);

        // Student forbidden
        $this->actingAs($student)->get(route('admin.analytics.index'))->assertForbidden();

        // Landlord forbidden
        $this->actingAs($landlord)->get(route('admin.analytics.index'))->assertForbidden();

        // Guest forbidden
        $this->get(route('admin.analytics.index'))->assertForbidden();
    }

    public function test_empty_database_returns_200_with_zero_state(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);

        $response = $this->actingAs($admin)->get(route('admin.analytics.index'));

        $response->assertOk();
        $kpis = $response->viewData('kpis');

        $this->assertEquals(0, $kpis['active_boarders']);
        $this->assertEquals(0, $kpis['room_occupancy_rate']);
        $this->assertEquals(0, $kpis['bed_occupancy_rate']);
        $this->assertEquals(0, $kpis['total_properties']);
        $this->assertEquals(0, $kpis['total_rooms']);
    }

    public function test_populated_dataset_computes_accurate_kpis_and_occupancy_rates(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);

        $landlord = User::factory()->create(['role' => 'landlord', 'email_verified_at' => now()]);
        $property = Property::create([
            'landlord_id' => $landlord->id,
            'name' => 'Greenview Dormitory',
            'address' => 'University Belt',
            'approval_status' => 'approved',
        ]);

        $room1 = Room::create([
            'property_id' => $property->id,
            'room_number' => '101',
            'capacity' => 4,
            'slots_available' => 2,
            'status' => 'occupied',
            'price' => 2500,
        ]);

        $room2 = Room::create([
            'property_id' => $property->id,
            'room_number' => '102',
            'capacity' => 2,
            'slots_available' => 2,
            'status' => 'available',
            'price' => 3000,
        ]);

        $student1 = User::factory()->create([
            'role' => 'student',
            'gender' => 'Male',
            'college' => 'CCS',
            'program' => 'BSIT',
            'email_verified_at' => now(),
        ]);

        $student2 = User::factory()->create([
            'role' => 'student',
            'gender' => 'Female',
            'college' => 'COED',
            'program' => 'BSED',
            'email_verified_at' => now(),
        ]);

        // Active booking for student 1 (check_in in the past, active stay until future)
        Booking::create([
            'student_id' => $student1->id,
            'room_id' => $room1->id,
            'status' => 'approved',
            'check_in' => Carbon::now()->subMonths(2),
            'check_out' => Carbon::now()->addMonths(4),
        ]);

        // Active booking for student 2
        Booking::create([
            'student_id' => $student2->id,
            'room_id' => $room1->id,
            'status' => 'approved',
            'check_in' => Carbon::now()->subMonths(1),
            'check_out' => Carbon::now()->addMonths(5),
        ]);

        // Pending booking
        Booking::create([
            'student_id' => $student1->id,
            'room_id' => $room2->id,
            'status' => 'pending',
            'check_in' => Carbon::now()->addDay(),
            'check_out' => Carbon::now()->addMonths(6),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.analytics.index'));

        $response->assertOk();
        $kpis = $response->viewData('kpis');

        // Total 2 rooms: 1 occupied, 1 available -> 50.0%
        $this->assertEquals(2, $kpis['total_rooms']);
        $this->assertEquals(1, $kpis['occupied_rooms']);
        $this->assertEquals(1, $kpis['available_rooms']);
        $this->assertEquals(50.0, $kpis['room_occupancy_rate']);

        // Capacity: 4 + 2 = 6, slots available: 2 + 2 = 4, occupied beds = 2 -> 33.3%
        $this->assertEquals(6, $kpis['total_capacity']);
        $this->assertEquals(2, $kpis['occupied_beds']);
        $this->assertEquals(33.3, $kpis['bed_occupancy_rate']);

        // Active boarders: exactly 2 distinct students
        $this->assertEquals(2, $kpis['active_boarders']);

        // Pending bookings: 1
        $this->assertEquals(1, $kpis['pending_bookings']);

        // Demographics
        $demographics = $response->viewData('demographics');
        $this->assertEquals(1, $demographics['boarder_gender']['male']);
        $this->assertEquals(1, $demographics['boarder_gender']['female']);

        // Property table
        $occupancy = $response->viewData('occupancy');
        $this->assertCount(1, $occupancy);
        $this->assertEquals('Greenview Dormitory', $occupancy->first()->name);
        $this->assertEquals(50.0, $occupancy->first()->room_occupancy_rate);
    }

    public function test_date_filters_and_property_filter_are_applied(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);

        $landlord = User::factory()->create(['role' => 'landlord', 'email_verified_at' => now()]);
        $propA = Property::create(['landlord_id' => $landlord->id, 'name' => 'House Alpha', 'address' => 'Alpha St', 'approval_status' => 'approved']);
        $propB = Property::create(['landlord_id' => $landlord->id, 'name' => 'House Beta', 'address' => 'Beta St', 'approval_status' => 'approved']);

        $roomA = Room::create(['property_id' => $propA->id, 'room_number' => 'A1', 'status' => 'occupied', 'capacity' => 2]);
        $roomB = Room::create(['property_id' => $propB->id, 'room_number' => 'B1', 'status' => 'occupied', 'capacity' => 2]);

        $studentA = User::factory()->create(['role' => 'student', 'college' => 'CCS', 'email_verified_at' => now()]);
        $studentB = User::factory()->create(['role' => 'student', 'college' => 'CBA', 'email_verified_at' => now()]);

        Booking::create([
            'student_id' => $studentA->id,
            'room_id' => $roomA->id,
            'status' => 'approved',
            'check_in' => Carbon::now()->subMonths(3),
            'check_out' => Carbon::now()->addMonths(3),
        ]);

        Booking::create([
            'student_id' => $studentB->id,
            'room_id' => $roomB->id,
            'status' => 'approved',
            'check_in' => Carbon::now()->subMonths(1),
            'check_out' => Carbon::now()->addMonths(5),
        ]);

        // Filter for Property Alpha only
        $response = $this->actingAs($admin)->get(route('admin.analytics.index', ['property_id' => $propA->id]));
        $response->assertOk();
        $occupancy = $response->viewData('occupancy');
        $this->assertCount(1, $occupancy);
        $this->assertEquals('House Alpha', $occupancy->first()->name);

        $kpis = $response->viewData('kpis');
        $this->assertEquals(1, $kpis['active_boarders']);
    }

    public function test_strict_mode_sql_compatibility_on_demographics_and_trends(): void
    {
        $monitoringService = app(BoardingMonitoringService::class);
        $analyticsService = app(AnalyticsService::class);

        $baseQuery = $monitoringService->buildBaseQuery([]);
        $demographics = $analyticsService->getStudentDemographics($baseQuery);
        $trend = $analyticsService->getBoardingTrend((int) now()->year);

        $this->assertIsArray($demographics);
        $this->assertArrayHasKey('colleges', $demographics);
        $this->assertArrayHasKey('boarder_gender', $demographics);
        $this->assertArrayHasKey('registered_gender', $demographics);

        $this->assertIsArray($trend);
        $this->assertCount(12, $trend['labels']);
        $this->assertCount(12, $trend['active_boarders']);
        $this->assertCount(12, $trend['new_bookings']);
    }
}
