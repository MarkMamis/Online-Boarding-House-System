<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\LandlordProfile;
use App\Models\Property;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandlordPropertyDetailsRefinementTest extends TestCase
{
    use RefreshDatabase;

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
            'boarding_house_name' => $name.' House',
            'onboarding_complete' => true,
            'address' => 'Calapan City',
        ]);
        $user->forceFill(['email_verified_at' => now()])->save();

        LandlordProfile::create([
            'user_id' => $user->id,
            'boarding_house_name' => $name.' House',
            'about' => 'Clean boarding house near campus',
            'business_permit_status' => 'approved',
            'contact_number' => '09180000001',
        ]);

        return $user->refresh();
    }

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
        ]);
        $user->forceFill(['email_verified_at' => now()])->save();

        return $user->refresh();
    }

    public function test_landlord_can_view_own_property_details(): void
    {
        $landlord = $this->createLandlord('Owner');
        $property = Property::create([
            'landlord_id' => $landlord->id,
            'name' => 'Sunset Boarding House',
            'description' => 'A cozy boarding house for university students.',
            'address' => 'Tawiran, Calapan City',
            'latitude' => 13.4125,
            'longitude' => 121.1802,
            'approval_status' => 'approved',
            'status' => 'approved',
        ]);

        $response = $this->actingAs($landlord)->get(route('landlord.properties.show', $property));

        $response->assertOk();
        $response->assertSee('Sunset Boarding House');
        $response->assertSee('Tawiran, Calapan City');
        $response->assertSee('Approved');
        $response->assertSee('Properties'); // Subtle back link text
        $response->assertSee('Add Room'); // Primary action button
        $response->assertSee('Edit Property'); // Secondary action button
        $response->assertDontSee('Back to List'); // Redundant bulky button removed
    }

    public function test_unauthorized_landlord_cannot_view_another_landlords_property(): void
    {
        $landlordA = $this->createLandlord('Owner A');
        $landlordB = $this->createLandlord('Owner B');

        $property = Property::create([
            'landlord_id' => $landlordA->id,
            'name' => 'Secret Villa',
            'address' => 'Calapan City',
            'approval_status' => 'approved',
        ]);

        $response = $this->actingAs($landlordB)->get(route('landlord.properties.show', $property));
        $response->assertForbidden();
    }

    public function test_guest_cannot_view_property(): void
    {
        $landlord = $this->createLandlord('Owner');
        $property = Property::create([
            'landlord_id' => $landlord->id,
            'name' => 'Guest Blocked House',
            'address' => 'Calapan City',
            'approval_status' => 'approved',
        ]);

        $response = $this->get(route('landlord.properties.show', $property));
        $response->assertForbidden();
    }

    public function test_compact_availability_summary_displays_correct_occupancy_semantics(): void
    {
        $landlord = $this->createLandlord('Owner');
        $student = $this->createStudent('Tenant');
        $property = Property::create([
            'landlord_id' => $landlord->id,
            'name' => 'Campus Haven',
            'address' => 'Masipit, Calapan City',
            'approval_status' => 'approved',
        ]);

        // Room 1: Capacity 2, 2 available
        $room1 = Room::create([
            'property_id' => $property->id,
            'room_number' => '101',
            'capacity' => 2,
            'slots_available' => 2,
            'price' => 1800.0,
            'status' => 'available',
        ]);

        // Room 2: Capacity 4, per_bed pricing model, 1 active tenant booking (so 3 slots open)
        $room2 = Room::create([
            'property_id' => $property->id,
            'room_number' => '102',
            'capacity' => 4,
            'slots_available' => 3,
            'price' => 1500.0,
            'pricing_model' => 'per_bed',
            'status' => 'available',
        ]);

        Booking::create([
            'room_id' => $room2->id,
            'student_id' => $student->id,
            'check_in' => now()->subDay()->toDateString(),
            'check_out' => now()->addMonths(3)->toDateString(),
            'occupancy_mode' => 'shared',
            'status' => 'approved',
        ]);

        $response = $this->actingAs($landlord)->get(route('landlord.properties.show', $property));

        $response->assertOk();
        // Check compact availability strip
        $response->assertSee('2 rooms');
        $response->assertSee('(6 bed slots)');
        $response->assertSee('2 available');
        $response->assertSee('5 beds open');
        $response->assertSee('1 occupied');
        $response->assertSee('1 bed filled');

        // Verify old KPI cards were removed
        $response->assertDontSee('class="metric-card"', false);
    }

    public function test_rooms_are_scoped_to_property_and_display_correct_actions(): void
    {
        $landlord = $this->createLandlord('Owner');
        $property = Property::create([
            'landlord_id' => $landlord->id,
            'name' => 'Pine Grove Dorm',
            'address' => 'Bucayao, Calapan City',
            'approval_status' => 'approved',
        ]);

        $otherProperty = Property::create([
            'landlord_id' => $landlord->id,
            'name' => 'Other Dorm',
            'address' => 'Calapan City',
            'approval_status' => 'approved',
        ]);

        $room = Room::create([
            'property_id' => $property->id,
            'room_number' => 'Pine-A1',
            'capacity' => 2,
            'slots_available' => 2,
            'price' => 2500.0,
            'pricing_model' => 'per_room',
            'status' => 'available',
        ]);

        $otherRoom = Room::create([
            'property_id' => $otherProperty->id,
            'room_number' => 'Other-Z9',
            'capacity' => 1,
            'slots_available' => 1,
            'price' => 3000.0,
            'status' => 'available',
        ]);

        $response = $this->actingAs($landlord)->get(route('landlord.properties.show', $property));

        $response->assertOk();
        $response->assertSee('Pine-A1');
        $response->assertSee('₱2,500.00 / room / mo');
        $response->assertSee(route('landlord.properties.rooms.edit', [$property->id, $room->id]));
        $response->assertSee(route('landlord.properties.rooms.duplicate', [$property->id, $room->id]));
        $response->assertSee(route('landlord.properties.rooms.destroy', [$property->id, $room->id]));

        // Other property room should not be visible
        $response->assertDontSee('Other-Z9');
    }

    public function test_empty_room_state_when_property_has_no_rooms(): void
    {
        $landlord = $this->createLandlord('Owner');
        $property = Property::create([
            'landlord_id' => $landlord->id,
            'name' => 'Freshly Built Dorm',
            'address' => 'Calapan City',
            'approval_status' => 'approved',
        ]);

        $response = $this->actingAs($landlord)->get(route('landlord.properties.show', $property));

        $response->assertOk();
        $response->assertSee('No rooms yet');
        $response->assertSee('Add your first room to prepare your boarding house for bookings');
        $response->assertSee(route('landlord.properties.rooms.create', $property->id));
    }

    public function test_location_map_renders_with_coordinates_and_shows_fallback_when_missing(): void
    {
        $landlord = $this->createLandlord('Owner');

        // Property with valid coordinates
        $mappedProperty = Property::create([
            'landlord_id' => $landlord->id,
            'name' => 'Mapped Dorm',
            'address' => 'Calapan City',
            'latitude' => 13.4125,
            'longitude' => 121.1802,
            'approval_status' => 'approved',
        ]);

        $responseMapped = $this->actingAs($landlord)->get(route('landlord.properties.show', $mappedProperty));
        $responseMapped->assertOk();
        $responseMapped->assertSee('id="propertyMap"', false);
        $responseMapped->assertSee('leaflet@1.9.4');
        $responseMapped->assertSee('Open in Maps');

        // Property without coordinates
        $unmappedProperty = Property::create([
            'landlord_id' => $landlord->id,
            'name' => 'Unmapped Dorm',
            'address' => 'Calapan City',
            'latitude' => null,
            'longitude' => null,
            'approval_status' => 'approved',
        ]);

        $responseUnmapped = $this->actingAs($landlord)->get(route('landlord.properties.show', $unmappedProperty));
        $responseUnmapped->assertOk();
        $responseUnmapped->assertSee('Location not pinned yet.');
        $responseUnmapped->assertDontSee('id="propertyMap"', false);
    }

    public function test_building_amenities_and_house_rules_render_correctly(): void
    {
        $landlord = $this->createLandlord('Owner');

        $property = Property::create([
            'landlord_id' => $landlord->id,
            'name' => 'Feature Rich House',
            'address' => 'Calapan City',
            'approval_status' => 'approved',
            'building_inclusions' => ['cctv', 'wifi_range', 'parking'],
            'house_rules' => [
                'occupancy' => ['Curfew at 10 PM sharp.'],
            ],
        ]);

        $response = $this->actingAs($landlord)->get(route('landlord.properties.show', $property));
        $response->assertOk();
        $response->assertSee('CCTV');
        $response->assertSee('WIFI Range');
        $response->assertSee('Parking');
        $response->assertSee('Curfew at 10 PM sharp.');

        // Empty state when amenities and rules are not set
        $emptyProperty = Property::create([
            'landlord_id' => $landlord->id,
            'name' => 'Bare House',
            'address' => 'Calapan City',
            'approval_status' => 'approved',
            'building_inclusions' => null,
            'house_rules' => null,
        ]);

        $responseEmpty = $this->actingAs($landlord)->get(route('landlord.properties.show', $emptyProperty));
        $responseEmpty->assertOk();
        $responseEmpty->assertSee('No building amenities added yet.');
        $responseEmpty->assertSee('No house rules specified yet.');
    }

    public function test_booking_requests_rendered_with_actions(): void
    {
        $landlord = $this->createLandlord('Owner');
        $student = $this->createStudent('Applicant');

        $property = Property::create([
            'landlord_id' => $landlord->id,
            'name' => 'Student Villa',
            'address' => 'Calapan City',
            'approval_status' => 'approved',
        ]);

        $room = Room::create([
            'property_id' => $property->id,
            'room_number' => 'Suite-01',
            'capacity' => 2,
            'slots_available' => 2,
            'price' => 2000.0,
            'status' => 'available',
        ]);

        $booking = Booking::create([
            'room_id' => $room->id,
            'student_id' => $student->id,
            'check_in' => now()->toDateString(),
            'check_out' => now()->addMonths(5)->toDateString(),
            'status' => 'pending',
        ]);

        $response = $this->actingAs($landlord)->get(route('landlord.properties.show', $property));
        $response->assertOk();
        $response->assertSee('Suite-01');
        $response->assertSee('Applicant Full');
        $response->assertSee(route('landlord.bookings.approve', $booking->id));
        $response->assertSee(route('landlord.bookings.reject', $booking->id));
    }
}
