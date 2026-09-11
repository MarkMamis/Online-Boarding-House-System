<?php

namespace Tests\Feature;

use App\Models\Property;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPropertiesRefinementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $landlord;
    protected User $student;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'name' => 'System Admin',
            'email' => 'admin@example.com',
        ]);

        $this->landlord = User::factory()->create([
            'role' => 'landlord',
            'name' => 'Juan Landlord',
            'full_name' => 'Juan Dela Cruz',
            'email' => 'juan.landlord@example.com',
        ]);

        $this->student = User::factory()->create([
            'role' => 'student',
            'name' => 'Maria Student',
            'email' => 'maria@example.com',
        ]);
    }

    public function test_admin_can_view_properties_overview_page(): void
    {
        $property = Property::create([
            'landlord_id' => $this->landlord->id,
            'name' => 'Sunset Boarding Villa',
            'address' => 'Camilmil, Calapan City',
            'approval_status' => 'approved',
            'latitude' => 13.4043,
            'longitude' => 121.1746,
        ]);

        Room::create([
            'property_id' => $property->id,
            'room_number' => '101',
            'capacity' => 2,
            'slots_available' => 2,
            'price' => 2500,
            'status' => 'available',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.properties.index'));

        $response->assertStatus(200);
        $response->assertSee('Registered Properties');
        $response->assertSee('Sunset Boarding Villa');
        $response->assertSee('Juan Dela Cruz');
        $response->assertSee('Camilmil, Calapan City');
        $response->assertSee('List View');
        $response->assertSee('Map View');
        $response->assertSee('Review Approvals');
        $response->assertDontSee('<!-- <a href='); // no commented dead code
    }

    public function test_non_admin_cannot_access_admin_properties(): void
    {
        $response = $this->actingAs($this->student)->get(route('admin.properties.index'));
        $response->assertStatus(403);

        $responseLandlord = $this->actingAs($this->landlord)->get(route('admin.properties.index'));
        $responseLandlord->assertStatus(403);
    }

    public function test_server_side_search_filters_by_property_and_landlord(): void
    {
        Property::create([
            'landlord_id' => $this->landlord->id,
            'name' => 'Pineapple Inn',
            'address' => 'Tawiran, Calapan City',
            'approval_status' => 'approved',
        ]);

        $anotherLandlord = User::factory()->create([
            'role' => 'landlord',
            'name' => 'Pedro Penduko',
            'full_name' => 'Pedro Penduko',
            'email' => 'pedro@example.com',
        ]);

        Property::create([
            'landlord_id' => $anotherLandlord->id,
            'name' => 'Coconut Manor',
            'address' => 'Balingayan, Calapan City',
            'approval_status' => 'approved',
        ]);

        // Search by property name
        $response = $this->actingAs($this->admin)->get(route('admin.properties.index', ['search' => 'Pineapple']));
        $response->assertStatus(200);
        $response->assertSee('Pineapple Inn');
        $response->assertDontSee('Coconut Manor');

        // Search by landlord name
        $responseLandlord = $this->actingAs($this->admin)->get(route('admin.properties.index', ['search' => 'Pedro']));
        $responseLandlord->assertStatus(200);
        $responseLandlord->assertSee('Coconut Manor');
        $responseLandlord->assertDontSee('Pineapple Inn');
    }

    public function test_status_filter_isolates_pending_properties(): void
    {
        Property::create([
            'landlord_id' => $this->landlord->id,
            'name' => 'Approved Haven',
            'address' => 'Camilmil',
            'approval_status' => 'approved',
        ]);

        Property::create([
            'landlord_id' => $this->landlord->id,
            'name' => 'Pending Castle',
            'address' => 'Tawiran',
            'approval_status' => 'pending',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.properties.index', ['status' => 'pending']));
        $response->assertStatus(200);
        $response->assertSee('Pending Castle');
        $response->assertDontSee('Approved Haven');
    }

    public function test_availability_filter_works_correctly(): void
    {
        $hasRooms = Property::create([
            'landlord_id' => $this->landlord->id,
            'name' => 'Available House',
            'address' => 'Camilmil',
            'approval_status' => 'approved',
        ]);

        Room::create([
            'property_id' => $hasRooms->id,
            'room_number' => '1',
            'capacity' => 2,
            'slots_available' => 2,
            'price' => 2000,
            'status' => 'available',
        ]);

        $noRooms = Property::create([
            'landlord_id' => $this->landlord->id,
            'name' => 'Empty Shell Property',
            'address' => 'Camilmil',
            'approval_status' => 'approved',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.properties.index', ['availability' => 'available']));
        $response->assertStatus(200);
        $response->assertSee('Available House');
        $response->assertDontSee('Empty Shell Property');

        $responseNoRooms = $this->actingAs($this->admin)->get(route('admin.properties.index', ['availability' => 'no_rooms']));
        $responseNoRooms->assertStatus(200);
        $responseNoRooms->assertSee('Empty Shell Property');
        $responseNoRooms->assertDontSee('Available House');
    }

    public function test_inline_quick_approve_post_button_is_removed(): void
    {
        Property::create([
            'landlord_id' => $this->landlord->id,
            'name' => 'Unverified Lodge',
            'address' => 'Camilmil',
            'approval_status' => 'pending',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.properties.index'));
        $response->assertStatus(200);

        // Pending properties should have a safe review link rather than direct POST approve form
        $response->assertDontSee('action="' . url('/admin/properties/1/approve') . '"', false);
        $response->assertSee('Review');
    }

    public function test_empty_state_rendered_when_no_match(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.properties.index', ['search' => 'NonExistentZXYZXY']));
        $response->assertStatus(200);
        $response->assertSee('No properties match your filters');
        $response->assertSee('Reset Filters');
    }

    public function test_property_row_action_buttons_and_contextual_links(): void
    {
        $approvedProperty = Property::create([
            'landlord_id' => $this->landlord->id,
            'name' => 'Approved Green Villa',
            'address' => 'Camilmil, Calapan City',
            'approval_status' => 'approved',
            'latitude' => 13.4043,
            'longitude' => 121.1746,
        ]);

        $pendingProperty = Property::create([
            'landlord_id' => $this->landlord->id,
            'name' => 'Pending Yellow Haven',
            'address' => 'Tawiran, Calapan City',
            'approval_status' => 'pending',
            'latitude' => 13.4100,
            'longitude' => 121.1800,
        ]);

        $rejectedProperty = Property::create([
            'landlord_id' => $this->landlord->id,
            'name' => 'Rejected Red Dorm',
            'address' => 'Salong, Calapan City',
            'approval_status' => 'rejected',
            'rejection_reason' => 'Missing permit',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.properties.index'));
        $response->assertStatus(200);

        // 1. Property names link directly to property show
        $response->assertSee(route('admin.properties.show', $approvedProperty), false);
        $response->assertSee(route('admin.properties.show', $pendingProperty), false);
        $response->assertSee(route('admin.properties.show', $rejectedProperty), false);

        // 2. Landlord name links directly to landlord profile
        $response->assertSee(route('admin.users.landlords.show', $this->landlord->id), false);

        // 3. Location column includes Locate on map for properties with coordinates
        $response->assertSee('Locate on map');

        // 4. Pending property shows single compact Review button opening inspector drawer
        $response->assertSee("openPropertyInspector({$pendingProperty->id}, 'review')", false);
        $response->assertSee('Review');

        // 5. Approved and Rejected show compact View button opening inspector drawer
        $response->assertSee("openPropertyInspector({$approvedProperty->id}, 'view')", false);
        $response->assertSee("openPropertyInspector({$rejectedProperty->id}, 'view')", false);
        $response->assertSee('View');

        // 6. No redundant overflow kebab menu or old multi-line button labels in property rows
        $response->assertDontSee('bi-three-dots-vertical');
        $response->assertDontSee('Review Submission');
        $response->assertDontSee('btn-outline-secondary fw-semibold px-2 py-1">View Details', false);
        $response->assertSee('btn-row-action-view', false);
        $response->assertSee('btn-row-action-review', false);
    }

    public function test_workspace_status_tabs_rendered_with_accurate_counts(): void
    {
        Property::create([
            'landlord_id' => $this->landlord->id,
            'name' => 'Approved Prop 1',
            'address' => 'Camilmil, Calapan City',
            'approval_status' => 'approved',
        ]);
        Property::create([
            'landlord_id' => $this->landlord->id,
            'name' => 'Approved Prop 2',
            'address' => 'Tawiran, Calapan City',
            'approval_status' => 'approved',
        ]);
        Property::create([
            'landlord_id' => $this->landlord->id,
            'name' => 'Pending Prop 1',
            'address' => 'Tibag, Calapan City',
            'approval_status' => 'pending',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.properties.index'));
        $response->assertStatus(200);

        // Check workspace status tabs
        $response->assertSee('All Properties');
        $response->assertSee('Pending Review');
        $response->assertSee('Approved');
        $response->assertSee('Rejected');
        $response->assertSee('id="tabCountAll">3<', false);
        $response->assertSee('id="tabCountApproved">2<', false);
        $response->assertSee('id="tabCountPending">1<', false);
    }

    public function test_inspector_api_endpoint_returns_data_for_admin_and_forbids_non_admin(): void
    {
        $property = Property::create([
            'landlord_id' => $this->landlord->id,
            'name' => 'Grand Boarding House',
            'address' => 'Camilmil, Calapan City',
            'approval_status' => 'pending',
            'description' => 'A serene house near the college.',
            'latitude' => 13.4043,
            'longitude' => 121.1746,
        ]);

        Room::create([
            'property_id' => $property->id,
            'room_number' => '101',
            'capacity' => 4,
            'slots_available' => 2,
            'price' => 3000,
            'status' => 'available',
        ]);

        // Admin can inspect
        $response = $this->actingAs($this->admin)->getJson(route('admin.properties.inspect', $property));
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'id',
            'name',
            'address',
            'description',
            'approval_status',
            'landlord' => ['id', 'name', 'email'],
            'compliance' => ['business_permit', 'safety_certificate', 'is_complete'],
            'quick_check' => ['image_uploaded', 'description_added', 'coordinates_pinned', 'rooms_added'],
            'rooms_summary' => ['total_rooms', 'total_capacity', 'min_price'],
            'rooms',
            'full_details_url',
            'approve_url',
            'reject_url',
        ]);

        $response->assertJson([
            'name' => 'Grand Boarding House',
            'approval_status' => 'pending',
            'quick_check' => [
                'description_added' => true,
                'coordinates_pinned' => true,
                'rooms_added' => true,
            ],
            'rooms_summary' => [
                'total_rooms' => 1,
                'min_price' => 3000,
            ],
        ]);

        // Student is forbidden
        $studentResponse = $this->actingAs($this->student)->getJson(route('admin.properties.inspect', $property));
        $studentResponse->assertStatus(403);
    }

    public function test_ajax_approve_property_updates_status_and_returns_json(): void
    {
        $property = Property::create([
            'landlord_id' => $this->landlord->id,
            'name' => 'Pending To Approve',
            'address' => 'Camilmil, Calapan City',
            'approval_status' => 'pending',
        ]);

        $response = $this->actingAs($this->admin)->postJson(route('admin.properties.approve', $property));
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'approval_status' => 'approved',
            'property_id' => $property->id,
        ]);

        $this->assertEquals('approved', $property->fresh()->approval_status);
        $this->assertNotNull($property->fresh()->approved_at);
    }

    public function test_ajax_reject_property_requires_reason_and_updates_status(): void
    {
        $property = Property::create([
            'landlord_id' => $this->landlord->id,
            'name' => 'Pending To Reject',
            'address' => 'Camilmil, Calapan City',
            'approval_status' => 'pending',
        ]);

        // Missing reason fails validation
        $failResponse = $this->actingAs($this->admin)->postJson(route('admin.properties.reject', $property), []);
        $failResponse->assertStatus(422);
        $failResponse->assertJsonValidationErrors(['rejection_reason']);

        // With valid reason
        $response = $this->actingAs($this->admin)->postJson(route('admin.properties.reject', $property), [
            'rejection_reason' => 'Missing legitimate fire safety certificate and unclear location.',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'approval_status' => 'rejected',
            'property_id' => $property->id,
        ]);

        $this->assertEquals('rejected', $property->fresh()->approval_status);
        $this->assertEquals('Missing legitimate fire safety certificate and unclear location.', $property->fresh()->rejection_reason);
    }

    public function test_property_approval_route_redirects_to_properties_workspace_pending(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/properties/approval');
        $response->assertRedirect(route('admin.properties.index', ['status' => 'pending']));
    }

    public function test_landlord_approval_properties_tab_redirects_to_properties_workspace_pending(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.approvals.landlords', ['tab' => 'properties']));
        $response->assertRedirect(route('admin.properties.index', ['status' => 'pending']));
    }

    public function test_property_details_page_includes_compliance_and_safe_modals(): void
    {
        $pendingProperty = Property::create([
            'landlord_id' => $this->landlord->id,
            'name' => 'Details Inspection Test Villa',
            'address' => 'Camilmil, Calapan City',
            'approval_status' => 'pending',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.properties.show', $pendingProperty));
        $response->assertStatus(200);

        // Landlord compliance summary card
        $response->assertSee('Landlord Compliance');
        $response->assertSee('Business Permit');
        $response->assertSee('Safety Certificate');

        // Contextual return link
        $response->assertSee('Back to Pending Reviews');

        // Safe Approve & Reject modals present
        $response->assertSee('id="showApproveModal"', false);
        $response->assertSee('id="showRejectModal"', false);
        $response->assertSee('Confirm Approval');
        $response->assertSee('Confirm Rejection');
    }
}

