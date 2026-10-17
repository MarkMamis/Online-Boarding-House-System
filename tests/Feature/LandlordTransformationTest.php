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

class LandlordTransformationTest extends TestCase
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
            'about' => 'Clean boarding house',
            'business_permit_status' => 'approved',
            'contact_number' => '09180000001',
        ]);

        return $user->refresh();
    }

    protected function createPropertyWithRoom(User $landlord, float $price = 2500.0, int $capacity = 2): array
    {
        $property = Property::create([
            'landlord_id' => $landlord->id,
            'name' => 'Seaside Villa ' . uniqid(),
            'description' => 'Comfortable place',
            'address' => 'Calapan City',
            'gender_preference' => 'all',
            'status' => 'approved',
            'approval_status' => 'approved',
            'min_price' => $price,
            'max_price' => $price,
        ]);

        $room = Room::create([
            'property_id' => $property->id,
            'room_number' => 'Room 101',
            'capacity' => $capacity,
            'slots_available' => $capacity,
            'price' => $price,
            'status' => 'available',
            'room_type' => 'shared',
            'description' => 'Spacious room',
        ]);

        return [$property, $room];
    }

    public function test_landlord_can_save_property_as_draft_without_rooms(): void
    {
        $landlord = $this->createLandlord('Draft Landlord');

        $payload = [
            'name' => 'Draft Boarding House',
            'description' => 'Initial draft description',
            'address' => 'Barangay Masipit, Calapan City',
            'gender_preference' => 'all',
            'action' => 'save_draft',
        ];

        $response = $this->actingAs($landlord)->post(route('landlord.properties.store'), $payload);

        $property = Property::where('name', 'Draft Boarding House')->first();
        $this->assertNotNull($property);
        $response->assertRedirect(route('landlord.properties.show', $property));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('properties', [
            'name' => 'Draft Boarding House',
            'landlord_id' => $landlord->id,
            'approval_status' => 'draft',
        ]);

        $this->assertEquals(0, $property->rooms()->count());
    }

    public function test_landlord_can_duplicate_room_safely(): void
    {
        $landlord = $this->createLandlord('Duplication Landlord');
        [$property, $room] = $this->createPropertyWithRoom($landlord, 3200.00, 3);

        $response = $this->actingAs($landlord)->post(
            route('landlord.properties.rooms.duplicate', [$property->id, $room->id])
        );

        $duplicated = Room::where('property_id', $property->id)->where('room_number', 'Room 101 (Copy)')->first();
        $this->assertNotNull($duplicated);

        $response->assertRedirect(route('landlord.properties.rooms.edit', [$property->id, $duplicated->id]));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('rooms', [
            'property_id' => $property->id,
            'room_number' => 'Room 101 (Copy)',
            'capacity' => 3,
            'slots_available' => 3,
            'price' => 3200.00,
            'status' => 'available',
        ]);

        // Unauthorized landlord cannot duplicate another landlord's room
        $otherLandlord = $this->createLandlord('Other Landlord');
        $forbiddenResponse = $this->actingAs($otherLandlord)->post(
            route('landlord.properties.rooms.duplicate', [$property->id, $room->id])
        );
        $this->assertContains($forbiddenResponse->getStatusCode(), [403, 404]);
    }

    public function test_booking_approval_redirects_to_onboarding_pipeline(): void
    {
        $landlord = $this->createLandlord('Approval Landlord');
        $student = $this->createStudent('Student Applicant');
        [$property, $room] = $this->createPropertyWithRoom($landlord, 2800.00, 2);

        $booking = Booking::create([
            'room_id' => $room->id,
            'student_id' => $student->id,
            'check_in' => now()->toDateString(),
            'check_out' => now()->addMonths(6)->toDateString(),
            'status' => 'pending',
            'payment_status' => 'pending',
        ]);

        $response = $this->actingAs($landlord)->post(route('landlord.bookings.approve', $booking));

        $response->assertRedirect(route('landlord.onboarding.index'));
        $response->assertSessionHas('success');

        $this->assertEquals('approved', $booking->fresh()->status);
    }

    public function test_mark_as_paid_creates_tenant_payment_record_if_none_exists(): void
    {
        $landlord = $this->createLandlord('Finance Landlord');
        $student = $this->createStudent('Direct Pay Student');
        [$property, $room] = $this->createPropertyWithRoom($landlord, 3500.00, 1);

        $booking = Booking::create([
            'room_id' => $room->id,
            'student_id' => $student->id,
            'check_in' => now()->subDays(10)->toDateString(),
            'check_out' => now()->addMonths(5)->toDateString(),
            'status' => 'approved',
            'payment_status' => 'pending',
        ]);

        $this->assertDatabaseMissing('tenant_payments', [
            'booking_id' => $booking->id,
        ]);

        $response = $this->actingAs($landlord)->post(route('landlord.payments.mark_paid', $booking));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertEquals('paid', $booking->fresh()->payment_status);

        $this->assertDatabaseHas('tenant_payments', [
            'booking_id' => $booking->id,
            'student_id' => $student->id,
            'status' => 'approved',
            'payment_method' => 'cash',
            'reviewed_by' => $landlord->id,
        ]);
    }

    public function test_landlord_dashboard_computes_urgent_action_queues(): void
    {
        $landlord = $this->createLandlord('Dashboard Landlord');
        [$property, $room] = $this->createPropertyWithRoom($landlord, 2500.00, 2);

        $response = $this->actingAs($landlord)->get(route('landlord.dashboard'));

        $response->assertOk();
        $response->assertViewHas('pendingBookingsList');
        $response->assertViewHas('pendingOnboardingsList');
        $response->assertViewHas('pendingMonthlyPaymentsList');
        $response->assertViewHas('totalActionCount');
    }

    public function test_landlord_tenants_page_shows_residency_distinctions(): void
    {
        $landlord = $this->createLandlord('Tenants Landlord');
        $studentA = $this->createStudent('Active Resident Student');
        $studentB = $this->createStudent('Pre-Arrival Student');
        [$property, $room] = $this->createPropertyWithRoom($landlord, 2500.00, 4);

        // Booking 1: Completed onboarding -> Active Resident
        $bookingA = Booking::create([
            'room_id' => $room->id,
            'student_id' => $studentA->id,
            'check_in' => now()->subMonth()->toDateString(),
            'check_out' => now()->addMonths(5)->toDateString(),
            'status' => 'approved',
            'payment_status' => 'paid',
        ]);
        TenantOnboarding::create([
            'booking_id' => $bookingA->id,
            'student_id' => $studentA->id,
            'status' => 'completed',
        ]);

        // Booking 2: Pending onboarding -> Move-in In Progress
        $bookingB = Booking::create([
            'room_id' => $room->id,
            'student_id' => $studentB->id,
            'check_in' => now()->addDays(5)->toDateString(),
            'check_out' => now()->addMonths(6)->toDateString(),
            'status' => 'approved',
            'payment_status' => 'pending',
        ]);
        TenantOnboarding::create([
            'booking_id' => $bookingB->id,
            'student_id' => $studentB->id,
            'status' => 'documents_uploaded',
        ]);

        $response = $this->actingAs($landlord)->get(route('landlord.tenants.index'));

        $response->assertOk();
        $response->assertSee('Active Resident');
        $response->assertSee('Move-in In Progress');
    }

    public function test_landlord_property_create_renders_guided_setup_without_stepper(): void
    {
        $landlord = $this->createLandlord('Guided Property Landlord');

        $response = $this->actingAs($landlord)->get(route('landlord.properties.create'));

        $response->assertOk();
        // Asserts guided onboarding shell
        $response->assertSee('guided-setup-wrapper');
        $response->assertSee('Property Basics');
        $response->assertSee('Tell us about your boarding house.');
        $response->assertSee('Save &amp; Return to Review', false);
        $response->assertSee('Review your boarding house.');

        // Asserts absence of old stepper elements
        $response->assertDontSee('property-stepper');
        $response->assertDontSee('stepper-head');
        $response->assertDontSee('stepper-node');
        $response->assertDontSee('First Room');
    }

    public function test_landlord_room_create_renders_guided_setup_without_stepper(): void
    {
        $landlord = $this->createLandlord('Guided Room Landlord');
        [$property, $room] = $this->createPropertyWithRoom($landlord, 2500.00, 2);

        $response = $this->actingAs($landlord)->get(route('landlord.properties.rooms.create', $property->id));

        $response->assertOk();
        // Asserts clean contextual header
        $response->assertSee('Adding Room To');
        $response->assertSee($property->name);
        $response->assertSee('Tell us about this room.');
        $response->assertSee('Set the monthly rent.');
        $response->assertSee("What's included in the rent?", false);
        $response->assertSee('Save &amp; Return to Review', false);
        $response->assertSee('Review your room details.');

        // Asserts absence of old stepper and summary elements
        $response->assertDontSee('room-stepper');
        $response->assertDontSee('stepper-head');
        $response->assertDontSee('stepper-node');
        $response->assertDontSee('create-summary-item');
    }

    public function test_landlord_can_create_room_with_guided_payload(): void
    {
        $landlord = $this->createLandlord('Room Creator Landlord');
        [$property, $room] = $this->createPropertyWithRoom($landlord, 2500.00, 2);

        $payload = [
            'room_number' => 'Deluxe Room 201',
            'capacity' => 2,
            'status' => 'available',
            'pricing_model' => 'per_bed',
            'price_per_bed' => 2600.00,
            'inclusions' => 'Wifi, Water Bill, Aircon',
        ];

        $response = $this->actingAs($landlord)->post(
            route('landlord.properties.rooms.store', $property->id),
            $payload
        );

        $response->assertRedirect(route('landlord.properties.rooms.index', $property->id));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('rooms', [
            'property_id' => $property->id,
            'room_number' => 'Deluxe Room 201',
            'capacity' => 2,
            'status' => 'available',
            'price' => 2600.00,
            'inclusions' => 'Wifi, Water Bill, Aircon',
        ]);
    }
}

