<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\LandlordDocument;
use App\Models\LandlordProfile;
use App\Models\Property;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminDetailPagesRefinementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $landlord;
    protected User $student;

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

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'full_name' => 'Super Admin',
            'email' => 'admin@test.com',
        ]);

        $this->landlord = User::factory()->create([
            'role' => 'landlord',
            'full_name' => 'Luna Wing',
            'email' => 'lunawing@test.com',
            'contact_number' => '09213778747',
            'boarding_house_name' => 'Luna Residences',
            'is_active' => true,
        ]);

        LandlordProfile::create([
            'user_id' => $this->landlord->id,
            'business_permit_status' => 'approved',
            'safety_certificate_status' => 'approved',
        ]);

        $this->student = User::factory()->create([
            'role' => 'student',
            'full_name' => 'John Student',
            'email' => 'john@student.test',
        ]);
    }

    public function test_admin_can_view_landlord_details_with_compact_header_and_chips(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.users.landlords.show', $this->landlord));

        $response->assertOk();
        $response->assertSee('Luna Wing');
        $response->assertSee('Active Account');
        $response->assertSee('lunawing@test.com');
        $response->assertSee('09213778747');
        $response->assertSee('Back to Landlords');

        // Metric chips
        $response->assertSee('Current Tenants');
        $response->assertSee('Occupied Rooms');
        $response->assertSee('Occupancy Rate');

        // Primary tabs
        $response->assertSee('Overview');
        $response->assertSee('Current Tenants');
        $response->assertSee('Portfolio');
        $response->assertSee('Documents &amp; Requirements', false);

        // Actions
        $response->assertSee('Send Message');
        $response->assertSee('More Actions');
        $response->assertSee('Deactivate Landlord Account');
    }

    public function test_non_admin_cannot_access_landlord_details(): void
    {
        $response = $this->actingAs($this->student)->get(route('admin.users.landlords.show', $this->landlord));
        $response->assertForbidden();
    }

    public function test_landlord_portfolio_tab_links_to_property_details(): void
    {
        $property = Property::create([
            'landlord_id' => $this->landlord->id,
            'name' => 'Board 12',
            'address' => 'Camilmil, Calapan City',
            'approval_status' => 'approved',
        ]);

        Room::create([
            'property_id' => $property->id,
            'room_number' => 'Room 101',
            'capacity' => 2,
            'slots_available' => 2,
            'price' => 3500,
            'status' => 'available',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.users.landlords.show', [
            'user' => $this->landlord,
            'tab' => 'portfolio',
        ]));

        $response->assertOk();
        $response->assertSee('Board 12');
        $response->assertSee('Camilmil, Calapan City');
        $response->assertSee(route('admin.properties.show', $property->id));
    }

    public function test_admin_can_view_property_details_with_compact_header_and_tabs(): void
    {
        $property = Property::create([
            'landlord_id' => $this->landlord->id,
            'name' => 'Board 12',
            'address' => 'Camilmil, Calapan City',
            'approval_status' => 'pending',
            'description' => 'A cozy student boarding house near campus.',
            'building_inclusions' => ['CCTV', 'Fire Extinguishers', 'Emergency Lights'],
            'latitude' => 13.404321,
            'longitude' => 121.174654,
        ]);

        $room = Room::create([
            'property_id' => $property->id,
            'room_number' => 'Room 201',
            'capacity' => 3,
            'slots_available' => 1,
            'price' => 4500,
            'status' => 'available',
            'inclusions' => 'WiFi, Aircon, Study Desk',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.properties.show', $property));

        $response->assertOk();
        $response->assertSee('Board 12');
        $response->assertSee('Pending');
        $response->assertSee('Camilmil, Calapan City');

        // Landlord cross-link
        $response->assertSee('Luna Wing');
        $response->assertSee(route('admin.users.landlords.show', $this->landlord->id));

        // Metric chips
        $response->assertSee('Approval Status');
        $response->assertSee('Configured Rooms');
        $response->assertSee('Occupancy Rate');
        $response->assertSee('Price Range');
        $response->assertSee('PHP 4,500 - 4,500');

        // Tabs
        $response->assertSee('Overview');
        $response->assertSee('Rooms');
        $response->assertSee('Compliance');
        $response->assertSee('Location');

        // Overview features
        $response->assertSee('Property Readiness');
        $response->assertSee('Landlord Compliance');
        $response->assertSee('CCTV');
        $response->assertSee('Fire Extinguishers');

        // Safe pending actions
        $response->assertSee('id="showApproveModal"', false);
        $response->assertSee('id="showRejectModal"', false);
        $response->assertSee('Confirm Approval');
        $response->assertSee('Confirm Rejection');
    }

    public function test_property_tabs_query_parameter_renders_correctly(): void
    {
        $property = Property::create([
            'landlord_id' => $this->landlord->id,
            'name' => 'Board 12',
            'address' => 'Camilmil, Calapan City',
            'approval_status' => 'approved',
        ]);

        // Rooms tab
        $responseRooms = $this->actingAs($this->admin)->get(route('admin.properties.show', [
            'property' => $property,
            'tab' => 'rooms',
        ]));
        $responseRooms->assertOk();
        $responseRooms->assertSee('No Rooms Configured');

        // Compliance tab
        $responseComp = $this->actingAs($this->admin)->get(route('admin.properties.show', [
            'property' => $property,
            'tab' => 'compliance',
        ]));
        $responseComp->assertOk();
        $responseComp->assertSee('Landlord Regulatory Compliance');
        $responseComp->assertSee('Property Listing Readiness');

        // Location tab
        $responseLoc = $this->actingAs($this->admin)->get(route('admin.properties.show', [
            'property' => $property,
            'tab' => 'location',
        ]));
        $responseLoc->assertOk();
        $responseLoc->assertSee('Property Mapped Location');
    }

    public function test_approved_property_does_not_show_decision_modals(): void
    {
        $approvedProperty = Property::create([
            'landlord_id' => $this->landlord->id,
            'name' => 'Approved Residence',
            'address' => 'Calapan City',
            'approval_status' => 'approved',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.properties.show', $approvedProperty));

        $response->assertOk();
        $response->assertSee('Decision: Approved');
        $response->assertDontSee('id="showApproveModal"', false);
        $response->assertDontSee('id="showRejectModal"', false);
    }
}
