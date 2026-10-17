<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\LandlordProfile;
use App\Models\Property;
use App\Models\Room;
use App\Models\TenantOnboarding;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentMarketplaceExperienceTest extends TestCase
{
    use RefreshDatabase;

    protected function createStudent(string $name = 'Student'): User
    {
        $id = uniqid();
        $user = User::create([
            'name' => $name,
            'full_name' => $name.' Full',
            'email' => 'stu_'.$id.'@example.com',
            'password' => bcrypt('password'),
            'role' => 'student',
            'contact_number' => '09170000001',
            'boarding_house_name' => 'N/A',
            'onboarding_complete' => true,
            'birth_date' => '2004-01-01',
            'college' => 'College of Computer Studies',
            'program' => 'BS Information Technology',
            'year_level' => '2nd Year',
            'gender' => 'Male',
            'address' => 'Calapan City',
            'student_id' => '2022-'.rand(1000, 9999),
            'profile_image_path' => 'students/1/profile.jpg',
            'school_id_path' => 'students/1/school_id.jpg',
            'enrollment_proof_type' => 'COR',
            'enrollment_proof_path' => 'students/1/cor.pdf',
            'emergency_contact_name' => 'Emergency Name',
            'emergency_contact_number' => '09170000002',
            'emergency_contact_relationship' => 'Parent',
            'parent_contact_name' => 'Parent Name',
            'parent_contact_number' => '09170000003',
            'parent_contact_address' => 'Calapan City',
        ]);
        $user->forceFill(['email_verified_at' => now()])->save();

        return $user->refresh();
    }

    protected function createLandlord(string $name = 'Landlord'): User
    {
        $id = uniqid();
        $user = User::create([
            'name' => $name,
            'full_name' => $name.' Full',
            'email' => 'lnd_'.$id.'@example.com',
            'password' => bcrypt('password'),
            'role' => 'landlord',
            'contact_number' => '09180000001',
            'boarding_house_name' => $name.' Boarding House',
            'onboarding_complete' => true,
        ]);
        $user->forceFill(['email_verified_at' => now()])->save();

        LandlordProfile::create([
            'user_id' => $user->id,
            'contact_number' => '09180000001',
            'boarding_house_name' => $name.' Boarding House',
            'about' => 'Accredited student housing.',
            'preferred_payment_methods' => ['cash'],
            'business_permit_status' => 'approved',
            'billing_completed' => true,
        ]);

        return $user->refresh();
    }

    protected function createPropertyAndRoom(User $landlord): array
    {
        $property = Property::create([
            'landlord_id' => $landlord->id,
            'name' => 'MinSU Haven Dorm',
            'address' => 'Barangay Masipit, Calapan City',
            'status' => 'approved',
            'approval_status' => 'approved',
            'latitude' => 13.4125,
            'longitude' => 121.1802,
        ]);

        $room = Room::create([
            'property_id' => $property->id,
            'room_number' => '101',
            'pricing_model' => Room::PRICING_MODEL_PER_BED,
            'price' => 2000.00,
            'price_per_bed' => 2000.00,
            'capacity' => 2,
            'slots_available' => 2,
            'occupied_count' => 0,
            'status' => 'available',
        ]);

        return [$property, $room];
    }

    public function test_student_home_renders_marketplace_for_searching_student(): void
    {
        $student = $this->createStudent('Searching Student');
        $landlord = $this->createLandlord('Landlord Partner');
        [$property, $room] = $this->createPropertyAndRoom($landlord);

        $response = $this->actingAs($student)->get(route('student.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Find your next place near MinSU.');
        $response->assertSee('Available Accommodations');
        $response->assertSee('All Accommodations');
        $response->assertSee('Solo Rooms');
        $response->assertSee('Bed-Spacer (Shared)');
        $response->assertSee('MinSU Haven Dorm');
        $response->assertSee('Room 101');
        $response->assertSee('₱2,000');
        $response->assertSee('/ month per bed');

        // Verify absence of administrative KPI dashboard clutter
        $response->assertDontSee('Student Operations');
        $response->assertDontSee('Available Rooms KPI');
        $response->assertDontSee('Pending Requests KPI');
    }

    public function test_student_home_displays_pending_booking_banner(): void
    {
        $student = $this->createStudent('Pending Student');
        $landlord = $this->createLandlord('Host');
        [$property, $room] = $this->createPropertyAndRoom($landlord);

        Booking::create([
            'student_id' => $student->id,
            'room_id' => $room->id,
            'check_in' => now()->addDays(2),
            'check_out' => now()->addMonths(5),
            'status' => 'pending',
            'occupancy_mode' => 'shared',
            'monthly_rent_amount' => 2000,
        ]);

        $response = $this->actingAs($student)->get(route('student.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Under Review');
        $response->assertSee('Your reservation request for MinSU Haven Dorm');
        $response->assertSee('View Booking Details');
    }

    public function test_student_home_displays_move_in_pending_checklist_when_booking_approved(): void
    {
        $student = $this->createStudent('Approved Student');
        $landlord = $this->createLandlord('Host');
        [$property, $room] = $this->createPropertyAndRoom($landlord);

        $booking = Booking::create([
            'student_id' => $student->id,
            'room_id' => $room->id,
            'check_in' => now()->addDays(3),
            'check_out' => now()->addMonths(6),
            'status' => 'approved',
            'occupancy_mode' => 'shared',
            'monthly_rent_amount' => 2000,
        ]);

        TenantOnboarding::create([
            'booking_id' => $booking->id,
            'student_id' => $student->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($student)->get(route('student.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Booking Approved');
        $response->assertSee('Continue your move-in to MinSU Haven Dorm');
        $response->assertSee('Continue Move-in');
    }

    public function test_student_home_redirects_active_tenant_to_tenant_dashboard(): void
    {
        $student = $this->createStudent('Active Tenant Student');
        $landlord = $this->createLandlord('Host');
        [$property, $room] = $this->createPropertyAndRoom($landlord);

        $booking = Booking::create([
            'student_id' => $student->id,
            'room_id' => $room->id,
            'check_in' => now()->subDays(5),
            'check_out' => now()->addMonths(5),
            'status' => 'approved',
            'occupancy_mode' => 'shared',
            'monthly_rent_amount' => 2000,
        ]);

        TenantOnboarding::create([
            'booking_id' => $booking->id,
            'student_id' => $student->id,
            'status' => 'completed',
        ]);

        $response = $this->actingAs($student)->get(route('student.dashboard'));

        $response->assertRedirect(route('student.tenant.dashboard'));
    }

    public function test_student_rooms_catalog_renders_marketplace_grid_with_room_cards(): void
    {
        $student = $this->createStudent('Catalog Student');
        $landlord = $this->createLandlord('Host');
        [$property, $room] = $this->createPropertyAndRoom($landlord);

        $response = $this->actingAs($student)->get(route('student.rooms.index'));

        $response->assertStatus(200);
        $response->assertSee('Explore Rooms');
        $response->assertSee('Filters');
        $response->assertSee('MinSU Haven Dorm');
        $response->assertSee('Room 101');
        $response->assertSee('₱2,000');
        $response->assertSee('Accredited');
    }

    public function test_student_rooms_search_by_property_name_and_room_number(): void
    {
        $student = $this->createStudent('Search Student');
        $landlord = $this->createLandlord('Host');
        [$property, $room] = $this->createPropertyAndRoom($landlord);

        // Search by property name
        $responseName = $this->actingAs($student)->get(route('student.rooms.index', ['q' => 'Haven']));
        $responseName->assertStatus(200);
        $responseName->assertSee('MinSU Haven Dorm');

        // Search by room number
        $responseRoom = $this->actingAs($student)->get(route('student.rooms.index', ['q' => '101']));
        $responseRoom->assertStatus(200);
        $responseRoom->assertSee('Room 101');

        // Search for non-existent keyword
        $responseEmpty = $this->actingAs($student)->get(route('student.rooms.index', ['q' => 'NonExistentPlaceXYZ']));
        $responseEmpty->assertStatus(200);
        $responseEmpty->assertSee('No Accommodations Found');
    }

    public function test_student_rooms_price_filter_does_not_clamp_out_of_range_requests(): void
    {
        $student = $this->createStudent('Price Student');
        $landlord = $this->createLandlord('Host');
        [$property, $room] = $this->createPropertyAndRoom($landlord); // Room price is 2000

        // Filter max_price = 1500 (lower than any room in database)
        $response = $this->actingAs($student)->get(route('student.rooms.index', ['max_price' => 1500]));
        $response->assertStatus(200);
        $response->assertSee('No Accommodations Found');
        $response->assertDontSee('MinSU Haven Dorm');

        // Filter max_price = 2500 (covers room price 2000)
        $responseMatch = $this->actingAs($student)->get(route('student.rooms.index', ['max_price' => 2500]));
        $responseMatch->assertStatus(200);
        $responseMatch->assertSee('MinSU Haven Dorm');
    }

    public function test_student_rooms_filters_by_property_id(): void
    {
        $student = $this->createStudent('PropId Student');
        $landlord = $this->createLandlord('Host');
        [$property, $room] = $this->createPropertyAndRoom($landlord);

        $response = $this->actingAs($student)->get(route('student.rooms.index', ['property_id' => $property->id]));
        $response->assertStatus(200);
        $response->assertSee('MinSU Haven Dorm');

        $responseOther = $this->actingAs($student)->get(route('student.rooms.index', ['property_id' => 99999]));
        $responseOther->assertStatus(200);
        $responseOther->assertSee('No Accommodations Found');
    }

    public function test_my_bookings_and_stays_renders_active_and_past_tabs(): void
    {
        $student = $this->createStudent('Stays Student');
        $landlord = $this->createLandlord('Host');
        [$property, $room] = $this->createPropertyAndRoom($landlord);

        Booking::create([
            'student_id' => $student->id,
            'room_id' => $room->id,
            'check_in' => now()->addDays(5),
            'check_out' => now()->addMonths(4),
            'status' => 'pending',
            'occupancy_mode' => 'shared',
            'monthly_rent_amount' => 2000,
        ]);

        $response = $this->actingAs($student)->get(route('student.bookings.index'));

        $response->assertStatus(200);
        $response->assertSee('My Bookings & Stays');
        $response->assertSee('Active & Upcoming', false);
        $response->assertSee('Past Stays & History', false);
        $response->assertSee('MinSU Haven Dorm');
        $response->assertSee('Awaiting Landlord Review');

        // Confirm redundant 3rd "All Requests" tab is removed
        $response->assertDontSee('All Requests (');
    }

    public function test_campus_map_data_endpoint_computes_room_prices_without_tbd(): void
    {
        $student = $this->createStudent('Map Student');
        $landlord = $this->createLandlord('Host');
        [$property, $room] = $this->createPropertyAndRoom($landlord);

        $response = $this->actingAs($student)->getJson(route('student.properties.map'));

        $response->assertStatus(200);
        $data = $response->json('properties');
        $this->assertNotEmpty($data);
        $this->assertEquals('MinSU Haven Dorm', $data[0]['name']);
        $this->assertEquals(2000, $data[0]['price_min']);
    }
}
