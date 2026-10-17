<?php

namespace Tests\Feature;

use App\Models\LandlordProfile;
use App\Models\Property;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LandlordCreateEditUiUnificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    protected function createLandlord(): User
    {
        $id = uniqid();
        $landlord = User::create([
            'name' => 'Landlord '.$id,
            'full_name' => 'Landlord '.$id,
            'email' => 'landlord_'.$id.'@example.com',
            'password' => bcrypt('password'),
            'role' => 'landlord',
            'contact_number' => '09171234567',
            'boarding_house_name' => 'Sunrise Haven',
            'onboarding_complete' => true,
        ]);
        $landlord->forceFill(['email_verified_at' => now()])->save();

        LandlordProfile::create([
            'user_id' => $landlord->id,
            'boarding_house_name' => 'Sunrise Haven',
            'about' => 'A wonderful boarding house near MinSU campus with complete amenities.',
            'id_type' => 'Driver License',
            'id_number' => 'N01-12-345678',
            'emergency_contact' => '09181234567',
            'onboarding_step' => 4,
            'onboarding_completed' => true,
        ]);

        return $landlord;
    }

    protected function createProperty(User $landlord, array $attributes = []): Property
    {
        return Property::create(array_merge([
            'landlord_id' => $landlord->id,
            'name' => 'Sunrise Villa',
            'address' => 'Poblacion, Tawiran, Calapan City',
            'description' => 'A cozy boarding house for students.',
            'latitude' => 13.4115,
            'longitude' => 121.1803,
            'building_inclusions' => ['Wifi', 'Water Bill', 'CCTV'],
            'house_rules' => ['No smoking', 'Curfew at 10 PM'],
            'approval_status' => 'approved',
            'status' => 'approved',
        ], $attributes));
    }

    protected function createRoom(Property $property, array $attributes = []): Room
    {
        return Room::create(array_merge([
            'property_id' => $property->id,
            'room_number' => 'Room 101',
            'capacity' => 2,
            'slots_available' => 2,
            'price' => 2500,
            'price_per_bed' => 2500,
            'price_per_room' => 5000,
            'pricing_model' => 'hybrid',
            'status' => 'available',
            'inclusions' => 'Wifi, Water Bill',
        ], $attributes));
    }

    /**
     * Test Property Create screen renders guided UI without visible numbered stepper.
     */
    public function test_property_create_renders_guided_onboarding_ui_without_stepper(): void
    {
        $landlord = $this->createLandlord();

        $response = $this->actingAs($landlord)->get(route('landlord.properties.create'));

        $response->assertStatus(200);
        $response->assertSee('Property Basics');
        $response->assertSee('Amenities & Rules');
        $response->assertSee('Location');
        $response->assertSee('Review &amp; Confirmation', false);
        $response->assertDontSee('stepper-head');
        $response->assertDontSee('Step 1 of 3');
    }

    /**
     * Test Property Edit screen renders guided UI without visible numbered stepper or 3-card summary.
     */
    public function test_property_edit_renders_guided_onboarding_ui_without_stepper(): void
    {
        $landlord = $this->createLandlord();
        $property = $this->createProperty($landlord);

        $response = $this->actingAs($landlord)->get(route('landlord.properties.edit', $property->id));

        $response->assertStatus(200);
        $response->assertSee('Property Details');
        $response->assertSee('Sunrise Villa');
        $response->assertSee($property->address);
        $response->assertSee('Review Changes');
        $response->assertSee('Save Changes');
        $response->assertDontSee('stepper-head');
        $response->assertDontSee('Step 1 of 3');
        $response->assertDontSee('Three large context cards');
    }

    /**
     * Test Property Edit pre-fills existing property values.
     */
    public function test_property_edit_prefills_existing_values(): void
    {
        $landlord = $this->createLandlord();
        $property = $this->createProperty($landlord, [
            'name' => 'Heritage Dormitory',
            'address' => 'Near MinSU Main Gate, Alcate',
            'description' => 'Quiet study atmosphere.',
        ]);

        $response = $this->actingAs($landlord)->get(route('landlord.properties.edit', $property->id));

        $response->assertStatus(200);
        $response->assertSee('Heritage Dormitory');
        $response->assertSee('Near MinSU Main Gate, Alcate');
        $response->assertSee('Quiet study atmosphere.');
    }

    /**
     * Test Authorized owner can update property and no duplicate property is created.
     */
    public function test_authorized_landlord_can_update_property_without_duplicate(): void
    {
        $landlord = $this->createLandlord();
        $property = $this->createProperty($landlord);
        $initialCount = Property::count();

        $response = $this->actingAs($landlord)->put(route('landlord.properties.update', $property->id), [
            'name' => 'Sunrise Villa Renovated',
            'address' => 'New Address, Calapan City',
            'description' => 'Updated modern description.',
            'latitude' => 13.4120,
            'longitude' => 121.1810,
            'building_inclusions' => ['cctv', 'wifi_range'],
            'building_inclusion_custom_present' => 1,
            'house_rules' => ['No pets'],
        ]);

        $response->assertRedirect(route('landlord.properties.index'));
        $this->assertEquals($initialCount, Property::count());

        $property->refresh();
        $this->assertEquals('Sunrise Villa Renovated', $property->name);
        $this->assertEquals('New Address, Calapan City', $property->address);
    }

    /**
     * Test Unauthorized landlord cannot edit someone else's property.
     */
    public function test_unauthorized_landlord_cannot_edit_another_property(): void
    {
        $owner = $this->createLandlord();
        $otherLandlord = $this->createLandlord();
        $property = $this->createProperty($owner);

        $response = $this->actingAs($otherLandlord)->get(route('landlord.properties.edit', $property->id));
        $response->assertStatus(403);
    }

    /**
     * Test Room Create screen renders guided onboarding UI without stepper.
     */
    public function test_room_create_renders_guided_onboarding_ui_without_stepper(): void
    {
        $landlord = $this->createLandlord();
        $property = $this->createProperty($landlord);

        $response = $this->actingAs($landlord)->get(route('landlord.properties.rooms.create', $property->id));

        $response->assertStatus(200);
        $response->assertSee('Room Details');
        $response->assertSee('Rent & Billing');
        $response->assertSee('Room Inclusions');
        $response->assertSee('Room Photos');
        $response->assertSee('Review & Confirmation');
        $response->assertDontSee('stepper-head');
        $response->assertDontSee('Step 1 of 4');
    }

    /**
     * Test Room Edit screen renders guided UI without stepper or 3-summary stack.
     */
    public function test_room_edit_renders_guided_onboarding_ui_without_stepper(): void
    {
        $landlord = $this->createLandlord();
        $property = $this->createProperty($landlord);
        $room = $this->createRoom($property);

        $response = $this->actingAs($landlord)->get(route('landlord.properties.rooms.edit', [$property->id, $room->id]));

        $response->assertStatus(200);
        $response->assertSee('Editing Room');
        $response->assertSee($room->room_number);
        $response->assertSee('Review Changes');
        $response->assertSee('Save Changes');
        $response->assertDontSee('stepper-head');
        $response->assertDontSee('create-summary');
        $response->assertDontSee('Step 1 of 4');
    }

    /**
     * Test Room Edit pre-fills existing room values including hybrid pricing and inclusions.
     */
    public function test_room_edit_prefills_existing_values(): void
    {
        $landlord = $this->createLandlord();
        $property = $this->createProperty($landlord);
        $room = $this->createRoom($property, [
            'room_number' => 'Deluxe Suite 202',
            'capacity' => 4,
            'price_per_bed' => 1800,
            'price_per_room' => 7000,
            'pricing_model' => 'hybrid',
            'inclusions' => 'Wifi, Aircon, Comfort Room',
        ]);

        $response = $this->actingAs($landlord)->get(route('landlord.properties.rooms.edit', [$property->id, $room->id]));

        $response->assertStatus(200);
        $response->assertSee('Deluxe Suite 202');
        $response->assertSee('value="4"', false);
        $response->assertSee('1800');
        $response->assertSee('7000');
    }

    /**
     * Test Authorized owner can update room and no duplicate room is created.
     */
    public function test_authorized_landlord_can_update_room_without_duplicate(): void
    {
        $landlord = $this->createLandlord();
        $property = $this->createProperty($landlord);
        $room = $this->createRoom($property);
        $initialRoomCount = Room::count();

        $response = $this->actingAs($landlord)->put(route('landlord.properties.rooms.update', [$property->id, $room->id]), [
            'room_number' => 'Room 101-A',
            'capacity' => 3,
            'status' => 'available',
            'pricing_model' => 'per_bed',
            'price_per_bed' => 2600,
            'inclusions' => 'Wifi, Water Bill, Study Table',
        ]);

        $response->assertRedirect(route('landlord.properties.rooms.edit', [$property->id, $room->id]));
        $this->assertEquals($initialRoomCount, Room::count());

        $room->refresh();
        $this->assertEquals('Room 101-A', $room->room_number);
        $this->assertEquals(3, $room->capacity);
        $this->assertEquals('per_bed', $room->pricing_model);
    }

    /**
     * Test Unauthorized landlord cannot edit someone else's room.
     */
    public function test_unauthorized_landlord_cannot_edit_another_room(): void
    {
        $owner = $this->createLandlord();
        $otherLandlord = $this->createLandlord();
        $property = $this->createProperty($owner);
        $room = $this->createRoom($property);

        // Accessing room under property owned by another landlord returns 404 from getOwnedProperty
        $response = $this->actingAs($otherLandlord)->get(route('landlord.properties.rooms.edit', [$property->id, $room->id]));
        $this->assertTrue(in_array($response->status(), [403, 404], true));
    }
}
