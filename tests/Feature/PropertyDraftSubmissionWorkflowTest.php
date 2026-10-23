<?php

namespace Tests\Feature;

use App\Models\LandlordProfile;
use App\Models\Property;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PropertyDraftSubmissionWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $landlord;

    protected User $otherLandlord;

    protected User $student;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

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
            'contact_number' => '09181234567',
        ]);

        LandlordProfile::create([
            'user_id' => $this->landlord->id,
            'boarding_house_name' => 'Juan Dorm',
            'about' => 'Clean and safe boarding house.',
            'contact_number' => '09181234567',
            'business_permit_status' => 'approved',
            'billing_completed' => true,
        ]);

        $this->otherLandlord = User::factory()->create([
            'role' => 'landlord',
            'name' => 'Pedro Landlord',
            'full_name' => 'Pedro Penduko',
            'email' => 'pedro.landlord@example.com',
            'onboarding_complete' => true,
        ]);
        $this->otherLandlord->forceFill(['email_verified_at' => now()])->save();

        LandlordProfile::create([
            'user_id' => $this->otherLandlord->id,
            'boarding_house_name' => 'Pedro Dorm',
            'about' => 'Another boarding house.',
            'contact_number' => '09191234567',
            'business_permit_status' => 'approved',
            'billing_completed' => true,
        ]);

        $this->student = User::create([
            'name' => 'Maria Student',
            'full_name' => 'Maria Santos Student',
            'email' => 'maria.student@example.com',
            'password' => bcrypt('password'),
            'role' => 'student',
            'contact_number' => '09170000001',
            'boarding_house_name' => 'N/A',
            'onboarding_complete' => true,
            'birth_date' => '2004-01-01',
            'college' => 'College of Computer Studies',
            'program' => 'BS Information Technology',
            'year_level' => '2nd Year',
            'gender' => 'Female',
            'address' => 'Calapan City',
            'student_id' => '2022-9999',
            'profile_image_path' => 'profiles/student.jpg',
            'enrollment_proof_type' => 'COR',
            'enrollment_proof_path' => 'proofs/cor.pdf',
            'emergency_contact_name' => 'Emergency Name',
            'emergency_contact_number' => '09170000002',
            'emergency_contact_relationship' => 'Parent',
            'parent_contact_name' => 'Parent Name',
            'parent_contact_number' => '09170000003',
            'parent_contact_address' => 'Calapan City',
        ]);
        $this->student->forceFill(['email_verified_at' => now()])->save();
    }

    // ==========================================
    // A. NEW PROPERTY
    // ==========================================

    public function test_autosave_creates_draft_and_preserves_draft_status(): void
    {
        $response = $this->actingAs($this->landlord)
            ->postJson(route('landlord.properties.autosave'), [
                'name' => 'Draft Sunset Villa',
                'description' => 'A cozy boarding house near MinSU.',
            ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'saved' => true,
            ]);

        $propertyId = $response->json('property_id');
        $this->assertNotNull($propertyId);

        $property = Property::find($propertyId);
        $this->assertEquals('draft', $property->approval_status);
        $this->assertNull($property->submitted_at);
        $this->assertEquals('Draft Sunset Villa', $property->name);
    }

    public function test_final_submit_on_creation_transitions_draft_to_pending(): void
    {
        $response = $this->actingAs($this->landlord)
            ->post(route('landlord.properties.store'), [
                'name' => 'Complete Sunset Boarding House',
                'address' => 'Barangay Masipit, Calapan City',
                'description' => 'Full amenities, 5 minutes walk from campus.',
                'latitude' => 13.4045,
                'longitude' => 121.1750,
                'image' => UploadedFile::fake()->image('property.jpg', 600, 400),
                'action' => 'submit',
            ]);

        $response->assertRedirect();
        $property = Property::where('name', 'Complete Sunset Boarding House')->first();
        $this->assertNotNull($property);
        $this->assertEquals('pending', $property->approval_status);
        $this->assertNotNull($property->submitted_at);
        $response->assertRedirect(route('landlord.properties.show', $property));
    }

    public function test_admin_sees_pending_submission_and_can_review_it(): void
    {
        $property = Property::create([
            'landlord_id' => $this->landlord->id,
            'name' => 'Pending Boarding House',
            'address' => 'Barangay San Vicente, Calapan City',
            'description' => 'Great location for students.',
            'latitude' => 13.4100,
            'longitude' => 121.1800,
            'approval_status' => 'pending',
            'submitted_at' => now(),
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.property_reviews.index'));
        $response->assertOk();
        $response->assertSee('Pending Boarding House');
        $response->assertSee('Submitted for review');

        // Admin inspect
        $inspectResponse = $this->actingAs($this->admin)
            ->getJson(route('admin.properties.inspect', $property));
        $inspectResponse->assertOk()
            ->assertJson([
                'can_review' => true,
            ]);

        // Admin approve
        $approveResponse = $this->actingAs($this->admin)
            ->post(route('admin.properties.approve', $property));
        $approveResponse->assertRedirect();

        $property->refresh();
        $this->assertEquals('approved', $property->approval_status);
    }

    // ==========================================
    // B. EXISTING DRAFT
    // ==========================================

    public function test_landlord_can_open_property_details_and_see_review_and_submit(): void
    {
        $draft = Property::create([
            'landlord_id' => $this->landlord->id,
            'name' => 'Lolita Valenzuela Boarding House',
            'address' => 'Barangay Masipit, Calapan City',
            'description' => 'Spacious rooms for college students.',
            'latitude' => 13.4040,
            'longitude' => 121.1760,
            'image_path' => 'properties/cover.jpg',
            'approval_status' => 'draft',
        ]);

        $room = Room::create([
            'property_id' => $draft->id,
            'room_number' => 'Room 1',
            'capacity' => 2,
            'slots_available' => 2,
            'price' => 2500,
            'status' => 'available',
        ]);

        $response = $this->actingAs($this->landlord)
            ->get(route('landlord.properties.show', $draft));

        $response->assertOk();
        $response->assertSee("Your property is saved but hasn't been submitted for approval.", false);
        $response->assertSee('Review & Submit');
        $response->assertSee('Edit Property');
    }

    public function test_existing_draft_edit_view_shows_save_changes_and_submit_actions(): void
    {
        $draft = Property::create([
            'landlord_id' => $this->landlord->id,
            'name' => 'Existing Draft Boarding House',
            'address' => 'Barangay Masipit, Calapan City',
            'description' => 'A cozy draft house.',
            'latitude' => 13.4040,
            'longitude' => 121.1760,
            'image_path' => 'properties/cover.jpg',
            'approval_status' => 'draft',
        ]);

        $response = $this->actingAs($this->landlord)
            ->get(route('landlord.properties.edit', ['property' => $draft, 'panel' => 3]));

        $response->assertOk();
        $response->assertSee('Save Changes');
        $response->assertSee('Submit for Review');
    }

    public function test_updating_existing_draft_with_save_action_preserves_draft(): void
    {
        $draft = Property::create([
            'landlord_id' => $this->landlord->id,
            'name' => 'Initial Draft Name',
            'address' => 'Barangay Masipit, Calapan City',
            'description' => 'Initial draft description.',
            'latitude' => 13.4040,
            'longitude' => 121.1760,
            'approval_status' => 'draft',
        ]);

        $response = $this->actingAs($this->landlord)
            ->put(route('landlord.properties.update', $draft), [
                'name' => 'Updated Draft Name',
                'address' => 'Barangay Masipit, Calapan City',
                'description' => 'Updated draft description.',
                'latitude' => 13.4040,
                'longitude' => 121.1760,
                'action' => 'save',
            ]);

        $response->assertRedirect(route('landlord.properties.show', $draft));

        $draft->refresh();
        $this->assertEquals('Updated Draft Name', $draft->name);
        $this->assertEquals('draft', $draft->approval_status);
        $this->assertNull($draft->submitted_at);
    }

    public function test_updating_existing_draft_with_submit_action_transitions_to_pending(): void
    {
        $draft = Property::create([
            'landlord_id' => $this->landlord->id,
            'name' => 'Lolita Valenzuela Boarding House',
            'address' => 'Barangay Masipit, Calapan City',
            'description' => 'A fully equipped boarding house.',
            'latitude' => 13.4040,
            'longitude' => 121.1760,
            'image_path' => 'properties/existing_cover.jpg',
            'approval_status' => 'draft',
        ]);

        $room = Room::create([
            'property_id' => $draft->id,
            'room_number' => 'Room 101',
            'capacity' => 2,
            'slots_available' => 2,
            'price' => 2500,
            'status' => 'available',
        ]);

        $response = $this->actingAs($this->landlord)
            ->put(route('landlord.properties.update', $draft), [
                'name' => 'Lolita Valenzuela Boarding House',
                'address' => 'Barangay Masipit, Calapan City',
                'description' => 'A fully equipped boarding house.',
                'latitude' => 13.4040,
                'longitude' => 121.1760,
                'action' => 'submit',
            ]);

        $response->assertRedirect(route('landlord.properties.show', $draft));

        $draft->refresh();
        $this->assertEquals('pending', $draft->approval_status);
        $this->assertNotNull($draft->submitted_at);
        $this->assertCount(1, $draft->rooms);
    }

    public function test_dedicated_submit_endpoint_transitions_complete_draft_to_pending(): void
    {
        $draft = Property::create([
            'landlord_id' => $this->landlord->id,
            'name' => 'Dedicated Submit House',
            'address' => 'Barangay Masipit, Calapan City',
            'description' => 'A complete property ready for review.',
            'latitude' => 13.4040,
            'longitude' => 121.1760,
            'image_path' => 'properties/cover.jpg',
            'approval_status' => 'draft',
        ]);

        $room = Room::create([
            'property_id' => $draft->id,
            'room_number' => 'Room 1',
            'capacity' => 2,
            'slots_available' => 2,
            'price' => 2500,
            'status' => 'available',
        ]);

        $response = $this->actingAs($this->landlord)
            ->post(route('landlord.properties.submit', $draft));

        $response->assertRedirect(route('landlord.properties.show', $draft));
        $response->assertSessionHas('success');

        $draft->refresh();
        $this->assertEquals('pending', $draft->approval_status);
        $this->assertNotNull($draft->submitted_at);
    }

    // ==========================================
    // C. INVALID SUBMISSION
    // ==========================================

    public function test_missing_requirements_block_submission_and_remain_in_draft(): void
    {
        // Property missing image and map coordinates
        $incompleteDraft = Property::create([
            'landlord_id' => $this->landlord->id,
            'name' => 'Incomplete Boarding House',
            'address' => 'Barangay Masipit, Calapan City',
            'description' => 'Missing photo and map pin.',
            'latitude' => null,
            'longitude' => null,
            'image_path' => null,
            'approval_status' => 'draft',
        ]);

        $response = $this->actingAs($this->landlord)
            ->post(route('landlord.properties.submit', $incompleteDraft));

        $response->assertRedirect(route('landlord.properties.edit', ['property' => $incompleteDraft, 'panel' => 3]));
        $response->assertSessionHasErrors(['image', 'location']);

        $incompleteDraft->refresh();
        $this->assertEquals('draft', $incompleteDraft->approval_status);
        $this->assertNull($incompleteDraft->submitted_at);
    }

    // ==========================================
    // D. AUTO-SAVE & SECURITY SAFETY
    // ==========================================

    public function test_autosave_cannot_revert_pending_property_to_draft(): void
    {
        $pendingProperty = Property::create([
            'landlord_id' => $this->landlord->id,
            'name' => 'Submitted Pending House',
            'address' => 'Barangay Masipit, Calapan City',
            'description' => 'Awaiting admin review.',
            'latitude' => 13.4040,
            'longitude' => 121.1760,
            'approval_status' => 'pending',
            'submitted_at' => now(),
        ]);

        $response = $this->actingAs($this->landlord)
            ->postJson(route('landlord.properties.autosave'), [
                'property_id' => $pendingProperty->id,
                'name' => 'Malicious or Stale Draft Attempt',
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'approval_status' => 'pending',
            ]);

        $pendingProperty->refresh();
        $this->assertEquals('pending', $pendingProperty->approval_status);
        $this->assertNotNull($pendingProperty->submitted_at);
        $this->assertEquals('Submitted Pending House', $pendingProperty->name);
    }

    public function test_autosave_cannot_revert_approved_property_to_draft(): void
    {
        $approvedProperty = Property::create([
            'landlord_id' => $this->landlord->id,
            'name' => 'Approved Golden House',
            'address' => 'Barangay Masipit, Calapan City',
            'description' => 'Already verified and live.',
            'latitude' => 13.4040,
            'longitude' => 121.1760,
            'approval_status' => 'approved',
            'submitted_at' => now()->subDays(5),
        ]);

        $response = $this->actingAs($this->landlord)
            ->postJson(route('landlord.properties.autosave'), [
                'property_id' => $approvedProperty->id,
                'name' => 'Revert Approved Attempt',
            ]);

        $response->assertStatus(422);

        $approvedProperty->refresh();
        $this->assertEquals('approved', $approvedProperty->approval_status);
    }

    public function test_cross_landlord_submission_is_forbidden(): void
    {
        $draft = Property::create([
            'landlord_id' => $this->landlord->id,
            'name' => 'Juan Private House',
            'address' => 'Barangay Masipit, Calapan City',
            'description' => 'A nice place.',
            'approval_status' => 'draft',
        ]);

        $response = $this->actingAs($this->otherLandlord)
            ->post(route('landlord.properties.submit', $draft));

        $response->assertForbidden();
    }

    public function test_repeat_submission_is_idempotent(): void
    {
        $pending = Property::create([
            'landlord_id' => $this->landlord->id,
            'name' => 'Already Pending House',
            'address' => 'Barangay Masipit, Calapan City',
            'description' => 'A complete property.',
            'latitude' => 13.4040,
            'longitude' => 121.1760,
            'image_path' => 'properties/cover.jpg',
            'approval_status' => 'pending',
            'submitted_at' => now()->subHour(),
        ]);

        $originalSubmittedAt = $pending->submitted_at->toIso8601String();

        $response = $this->actingAs($this->landlord)
            ->post(route('landlord.properties.submit', $pending));

        $response->assertRedirect(route('landlord.properties.show', $pending));

        $pending->refresh();
        $this->assertEquals('pending', $pending->approval_status);
        $this->assertEquals($originalSubmittedAt, $pending->submitted_at->toIso8601String());
    }

    // ==========================================
    // E. ADMIN PROPERTY REVIEWS
    // ==========================================

    public function test_admin_property_reviews_excludes_draft_from_total_submissions_and_pending_tab(): void
    {
        // 1 Approved
        Property::create([
            'landlord_id' => $this->landlord->id,
            'name' => 'Approved House',
            'address' => 'Calapan City',
            'approval_status' => 'approved',
            'submitted_at' => now()->subDays(2),
        ]);

        // 3 Drafts
        for ($i = 1; $i <= 3; $i++) {
            Property::create([
                'landlord_id' => $this->landlord->id,
                'name' => "Draft House {$i}",
                'address' => 'Calapan City',
                'approval_status' => 'draft',
                'submitted_at' => null,
            ]);
        }

        // Pending tab should be empty (0 items)
        $response = $this->actingAs($this->admin)->get(route('admin.property_reviews.index', ['status' => 'pending']));
        $response->assertOk();
        $response->assertDontSee('Draft House 1');
        $response->assertSee('No properties found for this queue');

        // All tab should show 1 Total Submissions, NOT 4
        $responseAll = $this->actingAs($this->admin)->get(route('admin.property_reviews.index', ['status' => 'all']));
        $responseAll->assertOk();
        $responseAll->assertSee('Approved House');
        $responseAll->assertDontSee('Draft House 1');

        // Drafts tab should show the drafts with "Not yet submitted"
        $responseDrafts = $this->actingAs($this->admin)->get(route('admin.property_reviews.index', ['status' => 'draft']));
        $responseDrafts->assertOk();
        $responseDrafts->assertSee('Draft House 1');
        $responseDrafts->assertSee('Not yet submitted');
    }

    public function test_admin_cannot_approve_or_reject_draft_property(): void
    {
        $draft = Property::create([
            'landlord_id' => $this->landlord->id,
            'name' => 'Draft Unsubmitted Property',
            'address' => 'Calapan City',
            'approval_status' => 'draft',
        ]);

        // Attempt approve
        $approveResponse = $this->actingAs($this->admin)
            ->postJson(route('admin.properties.approve', $draft));
        $approveResponse->assertStatus(422);

        $draft->refresh();
        $this->assertEquals('draft', $draft->approval_status);

        // Attempt reject
        $rejectResponse = $this->actingAs($this->admin)
            ->postJson(route('admin.properties.reject', $draft), [
                'rejection_reason' => 'Invalid attempt',
            ]);
        $rejectResponse->assertStatus(422);

        $draft->refresh();
        $this->assertEquals('draft', $draft->approval_status);
    }

    // ==========================================
    // F. MARKETPLACE VISIBILITY
    // ==========================================

    public function test_marketplace_excludes_draft_and_pending_properties(): void
    {
        $approved = Property::create([
            'landlord_id' => $this->landlord->id,
            'name' => 'Live Approved House',
            'address' => 'Barangay Masipit, Calapan City',
            'latitude' => 13.4040,
            'longitude' => 121.1760,
            'approval_status' => 'approved',
            'submitted_at' => now()->subDay(),
        ]);

        $draft = Property::create([
            'landlord_id' => $this->landlord->id,
            'name' => 'Hidden Draft House',
            'address' => 'Barangay Masipit, Calapan City',
            'latitude' => 13.4040,
            'longitude' => 121.1760,
            'approval_status' => 'draft',
            'submitted_at' => null,
        ]);

        $pending = Property::create([
            'landlord_id' => $this->landlord->id,
            'name' => 'Hidden Pending House',
            'address' => 'Barangay Masipit, Calapan City',
            'latitude' => 13.4040,
            'longitude' => 121.1760,
            'approval_status' => 'pending',
            'submitted_at' => now(),
        ]);

        // Student map
        $mapResponse = $this->actingAs($this->student)->get(route('student.properties.map_view'));
        $mapResponse->assertOk();
        $mapResponse->assertSee('Live Approved House');
        $mapResponse->assertDontSee('Hidden Draft House');
        $mapResponse->assertDontSee('Hidden Pending House');

        // Student show page for draft aborts with 404
        $draftShowResponse = $this->actingAs($this->student)
            ->get(route('student.properties.show', $draft));
        $draftShowResponse->assertNotFound();

        // Student show page for pending aborts with 404
        $pendingShowResponse = $this->actingAs($this->student)
            ->get(route('student.properties.show', $pending));
        $pendingShowResponse->assertNotFound();

        // Student show page for approved succeeds
        $approvedShowResponse = $this->actingAs($this->student)
            ->get(route('student.properties.show', $approved));
        $approvedShowResponse->assertOk();
    }
}
