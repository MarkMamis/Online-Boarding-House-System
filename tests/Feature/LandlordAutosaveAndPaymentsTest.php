<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\LandlordProfile;
use App\Models\Property;
use App\Models\Room;
use App\Models\TenantOnboarding;
use App\Models\TenantPayment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandlordAutosaveAndPaymentsTest extends TestCase
{
    use RefreshDatabase;

    protected function createStudent(string $name = 'Student'): User
    {
        $id = uniqid();
        $user = User::create([
            'name' => $name,
            'full_name' => $name . ' Full',
            'email' => 'stu_' . $id . '@example.com',
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
            'student_id' => '2022-' . rand(1000, 9999),
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
            'full_name' => $name . ' Full',
            'email' => 'lnd_' . $id . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'landlord',
            'contact_number' => '09180000001',
            'boarding_house_name' => $name . ' House',
            'onboarding_complete' => true,
            'address' => 'Calapan City',
        ]);
        $user->forceFill(['email_verified_at' => now()])->save();

        LandlordProfile::create([
            'user_id' => $user->id,
            'boarding_house_name' => $name . ' House',
            'about' => 'Comfortable boarding house.',
            'contact_number' => '09180000001',
            'preferred_payment_methods' => ['cash', 'gcash'],
            'business_permit_status' => 'approved',
        ]);

        return $user->refresh();
    }

    protected function createActiveTenancy(User $student, User $landlord): array
    {
        $property = Property::create([
            'landlord_id' => $landlord->id,
            'name' => $landlord->name . ' Dorm',
            'address' => 'Near MinSU Calapan',
            'status' => 'approved',
            'approval_status' => 'approved',
        ]);

        $room = Room::create([
            'property_id' => $property->id,
            'room_number' => '101',
            'pricing_model' => Room::PRICING_MODEL_PER_BED,
            'price' => 2500.00,
            'capacity' => 2,
            'slots_available' => 1,
            'occupied_count' => 1,
            'status' => 'available',
        ]);

        $booking = Booking::create([
            'student_id' => $student->id,
            'room_id' => $room->id,
            'check_in' => now()->subMonth()->toDateString(),
            'check_out' => now()->addMonths(5)->toDateString(),
            'monthly_rent_amount' => 2500.00,
            'next_payment_due_date' => now()->startOfDay()->toDateString(),
            'status' => 'approved',
            'payment_status' => 'pending',
        ]);

        $onboarding = TenantOnboarding::create([
            'booking_id' => $booking->id,
            'status' => 'completed',
            'step' => 6,
            'deposit_paid' => true,
        ]);

        return [$property, $room, $booking, $onboarding];
    }

    // =========================================================================
    // PART 1: PROPERTY AUTO-SAVE TESTS
    // =========================================================================

    public function test_visiting_property_create_does_not_create_ghost_draft(): void
    {
        $landlord = $this->createLandlord('Visitor Landlord');

        $response = $this->actingAs($landlord)->get(route('landlord.properties.create'));
        $response->assertOk();
        $response->assertSee('All changes saved');

        $this->assertEquals(0, Property::where('landlord_id', $landlord->id)->count());
    }

    public function test_autosave_with_empty_payload_does_not_create_draft(): void
    {
        $landlord = $this->createLandlord('Empty Autosave Landlord');

        $response = $this->actingAs($landlord)->postJson(route('landlord.properties.autosave'), [
            'name' => '',
            'address' => '',
            'description' => '',
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'saved' => false,
        ]);

        $this->assertEquals(0, Property::where('landlord_id', $landlord->id)->count());
    }

    public function test_first_meaningful_autosave_creates_authorized_draft_property(): void
    {
        $landlord = $this->createLandlord('Draft Creator Landlord');

        $response = $this->actingAs($landlord)->postJson(route('landlord.properties.autosave'), [
            'name' => 'Sunset Dormitory Draft',
            'address' => 'Barangay Masipit, Calapan City',
            'description' => 'Walking distance to campus.',
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'saved' => true,
            'message' => 'All changes saved',
        ]);

        $propertyId = $response->json('property_id');
        $this->assertNotNull($propertyId);

        $this->assertDatabaseHas('properties', [
            'id' => $propertyId,
            'landlord_id' => $landlord->id,
            'name' => 'Sunset Dormitory Draft',
            'approval_status' => 'draft',
        ]);
    }

    public function test_subsequent_autosaves_update_the_same_draft_without_duplicates(): void
    {
        $landlord = $this->createLandlord('Updating Landlord');

        // First save
        $res1 = $this->actingAs($landlord)->postJson(route('landlord.properties.autosave'), [
            'name' => 'Initial Title',
            'address' => 'Calapan City',
        ]);
        $propId = $res1->json('property_id');

        // Second save (updating same property)
        $res2 = $this->actingAs($landlord)->postJson(route('landlord.properties.autosave'), [
            'property_id' => $propId,
            'name' => 'Updated Sunset Dormitory',
            'address' => 'Barangay Masipit, Calapan City, Oriental Mindoro',
            'description' => 'Updated description after second question.',
        ]);

        $res2->assertOk();
        $res2->assertJson([
            'success' => true,
            'saved' => true,
            'property_id' => $propId,
        ]);

        // Assert exactly 1 property exists for this landlord
        $this->assertEquals(1, Property::where('landlord_id', $landlord->id)->count());

        $this->assertDatabaseHas('properties', [
            'id' => $propId,
            'name' => 'Updated Sunset Dormitory',
            'approval_status' => 'draft',
        ]);
    }

    public function test_autosave_accepts_partial_draft_data(): void
    {
        $landlord = $this->createLandlord('Partial Landlord');

        // Saving only name before address is filled
        $response = $this->actingAs($landlord)->postJson(route('landlord.properties.autosave'), [
            'name' => 'Partial Boarding House',
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'saved' => true,
        ]);

        $this->assertDatabaseHas('properties', [
            'landlord_id' => $landlord->id,
            'name' => 'Partial Boarding House',
            'approval_status' => 'draft',
        ]);
    }

    public function test_autosave_rejects_invalid_latitude_longitude(): void
    {
        $landlord = $this->createLandlord('Invalid Geo Landlord');

        $response = $this->actingAs($landlord)->postJson(route('landlord.properties.autosave'), [
            'name' => 'Invalid Geo Property',
            'latitude' => 150.00, // Invalid: must be between -90 and 90
            'longitude' => 200.00, // Invalid: must be between -180 and 180
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['latitude', 'longitude']);
    }

    public function test_autosave_rejects_cross_landlord_draft_update(): void
    {
        $landlordA = $this->createLandlord('Landlord A');
        $landlordB = $this->createLandlord('Landlord B');

        $draftA = Property::create([
            'landlord_id' => $landlordA->id,
            'name' => 'Landlord A Draft',
            'address' => 'Calapan City',
            'approval_status' => 'draft',
        ]);

        $response = $this->actingAs($landlordB)->postJson(route('landlord.properties.autosave'), [
            'property_id' => $draftA->id,
            'name' => 'Malicious Hijack Attempt',
        ]);

        $response->assertStatus(404);
        $this->assertEquals('Landlord A Draft', $draftA->fresh()->name);
    }

    public function test_autosave_cannot_modify_already_submitted_property(): void
    {
        $landlord = $this->createLandlord('Pending Landlord');

        $submittedProp = Property::create([
            'landlord_id' => $landlord->id,
            'name' => 'Already Submitted Property',
            'address' => 'Calapan City',
            'approval_status' => 'pending',
        ]);

        $response = $this->actingAs($landlord)->postJson(route('landlord.properties.autosave'), [
            'property_id' => $submittedProp->id,
            'name' => 'Post-Submission Edit Attempt',
        ]);

        $response->assertStatus(422);
        $this->assertEquals('Already Submitted Property', $submittedProp->fresh()->name);
    }

    public function test_final_submission_reuses_draft_record_without_creating_duplicate(): void
    {
        $landlord = $this->createLandlord('Final Submit Landlord');

        // Create initial draft via autosave
        $res = $this->actingAs($landlord)->postJson(route('landlord.properties.autosave'), [
            'name' => 'Draft To Be Submitted',
            'address' => 'Barangay Masipit, Calapan City',
        ]);
        $propId = $res->json('property_id');

        // Final submission via standard POST to store
        $submitPayload = [
            'property_id' => $propId,
            'name' => 'Completed Boarding House',
            'address' => 'Barangay Masipit, Calapan City, Oriental Mindoro',
            'description' => 'Complete description for review.',
            'action' => 'submit',
        ];

        $finalRes = $this->actingAs($landlord)->post(route('landlord.properties.store'), $submitPayload);
        $finalRes->assertRedirect(route('landlord.properties.show', $propId));

        // Exactly 1 property in database, approval_status transitioned to pending
        $this->assertEquals(1, Property::where('landlord_id', $landlord->id)->count());
        $property = Property::find($propId);
        $this->assertEquals('pending', $property->approval_status);
        $this->assertEquals('Completed Boarding House', $property->name);
    }

    // =========================================================================
    // PART 2: PAYMENTS CENTER TESTS
    // =========================================================================

    public function test_payments_index_renders_simplified_ui_without_redundant_elements(): void
    {
        $landlord = $this->createLandlord('Payment Test Landlord');

        $response = $this->actingAs($landlord)->get(route('landlord.payments.index'));
        $response->assertOk();

        // New minimal elements
        $response->assertSee('Payments');
        $response->assertSee('Monthly Rent');
        $response->assertSee('Move-in Payments');

        // Absence of redundant elements
        $response->assertDontSee('View Tenants');
        $response->assertDontSee('Total Collections');
        $response->assertDontSee('Pending Verification');
        $response->assertDontSee('Total Expected');
        $response->assertDontSee('Onboarding Ledger');
    }

    public function test_monthly_and_movein_tabs_render_correct_records_scoped_to_landlord(): void
    {
        $studentA = $this->createStudent('Student A');
        $studentB = $this->createStudent('Student B');
        $landlordA = $this->createLandlord('Landlord A');
        $landlordB = $this->createLandlord('Landlord B');

        [$propA, $roomA, $bookingA, $onboardingA] = $this->createActiveTenancy($studentA, $landlordA);
        [$propB, $roomB, $bookingB, $onboardingB] = $this->createActiveTenancy($studentB, $landlordB);

        // Monthly tab: Landlord A sees Student A, not Student B
        $resMonthly = $this->actingAs($landlordA)->get(route('landlord.payments.index', ['tab' => 'monthly']));
        $resMonthly->assertOk();
        $resMonthly->assertSee($studentA->full_name);
        $resMonthly->assertDontSee($studentB->full_name);

        // Move-in tab: Landlord A sees Student A's move-in card
        $resMovein = $this->actingAs($landlordA)->get(route('landlord.payments.index', ['tab' => 'onboarding']));
        $resMovein->assertOk();
        $resMovein->assertSee($studentA->full_name);
        $resMovein->assertDontSee($studentB->full_name);
    }

    public function test_priority_review_banner_displays_when_payments_await_review(): void
    {
        $student = $this->createStudent('Student Review');
        $landlord = $this->createLandlord('Landlord Review');
        [$property, $room, $booking, $onboarding] = $this->createActiveTenancy($student, $landlord);

        // Create a submitted monthly payment
        TenantPayment::create([
            'booking_id' => $booking->id,
            'student_id' => $student->id,
            'billing_for_date' => now()->startOfDay()->toDateString(),
            'amount_due' => 2500.00,
            'payment_method' => 'gcash',
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        $response = $this->actingAs($landlord)->get(route('landlord.payments.index'));
        $response->assertOk();
        $response->assertSee('awaiting your review');
    }
}
