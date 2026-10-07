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

class BookingTenancyAuthorizationTest extends TestCase
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
            'boarding_house_name' => $name . ' Boarding House',
            'onboarding_complete' => true,
        ]);
        $user->forceFill(['email_verified_at' => now()])->save();

        LandlordProfile::create([
            'user_id' => $user->id,
            'contact_number' => '09180000001',
            'boarding_house_name' => $name . ' Boarding House',
            'about' => 'Reliable housing.',
            'preferred_payment_methods' => ['gcash'],
            'payment_gcash_name' => 'Landlord GCash',
            'business_permit_status' => 'approved',
        ]);

        return $user->refresh();
    }

    protected function createPropertyAndRoom(User $landlord): array
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
            'slots_available' => 2,
            'occupied_count' => 0,
            'status' => 'available',
        ]);

        return [$property, $room];
    }

    public function test_student_can_cancel_own_pending_booking(): void
    {
        $student = $this->createStudent('Student A');
        $landlord = $this->createLandlord('Landlord A');
        [$property, $room] = $this->createPropertyAndRoom($landlord);

        $booking = Booking::create([
            'student_id' => $student->id,
            'room_id' => $room->id,
            'check_in' => now()->toDateString(),
            'check_out' => now()->addMonths(5)->toDateString(),
            'status' => 'pending',
        ]);

        $response = $this->actingAs($student)
            ->post("/student/requests/{$booking->id}/cancel", [
                'cancel_reason' => 'Changed my semester schedule',
            ]);

        $response->assertRedirect();
        $this->assertEquals('cancelled', $booking->fresh()->status);
    }

    public function test_student_cannot_cancel_another_students_booking(): void
    {
        $studentA = $this->createStudent('Student A');
        $studentB = $this->createStudent('Student B');
        $landlord = $this->createLandlord('Landlord A');
        [$property, $room] = $this->createPropertyAndRoom($landlord);

        $bookingA = Booking::create([
            'student_id' => $studentA->id,
            'room_id' => $room->id,
            'check_in' => now()->toDateString(),
            'check_out' => now()->addMonths(5)->toDateString(),
            'status' => 'pending',
        ]);

        $response = $this->actingAs($studentB)
            ->post("/student/requests/{$bookingA->id}/cancel", [
                'cancel_reason' => 'Malicious cancellation attempt',
            ]);

        $response->assertStatus(403);
        $this->assertEquals('pending', $bookingA->fresh()->status);
    }

    public function test_landlord_can_approve_booking_for_owned_property(): void
    {
        $student = $this->createStudent('Student A');
        $landlord = $this->createLandlord('Landlord A');
        [$property, $room] = $this->createPropertyAndRoom($landlord);

        $booking = Booking::create([
            'student_id' => $student->id,
            'room_id' => $room->id,
            'occupancy_mode' => 'shared',
            'check_in' => now()->toDateString(),
            'check_out' => now()->addMonths(5)->toDateString(),
            'status' => 'pending',
        ]);

        $response = $this->actingAs($landlord)
            ->post("/landlord/bookings/{$booking->id}/approve");

        $response->assertRedirect();
        $this->assertEquals('approved', $booking->fresh()->status);
        $this->assertEquals(1, $room->fresh()->slots_available);
    }

    public function test_landlord_cannot_approve_booking_for_unowned_property(): void
    {
        $student = $this->createStudent('Student A');
        $landlordA = $this->createLandlord('Landlord A');
        $landlordB = $this->createLandlord('Landlord B');
        [$propertyA, $roomA] = $this->createPropertyAndRoom($landlordA);

        $bookingA = Booking::create([
            'student_id' => $student->id,
            'room_id' => $roomA->id,
            'check_in' => now()->toDateString(),
            'check_out' => now()->addMonths(5)->toDateString(),
            'status' => 'pending',
        ]);

        $response = $this->actingAs($landlordB)
            ->post("/landlord/bookings/{$bookingA->id}/approve");

        $response->assertStatus(403);
        $this->assertEquals('pending', $bookingA->fresh()->status);
        $this->assertEquals(2, $roomA->fresh()->slots_available);
    }

    public function test_student_cannot_view_another_students_onboarding_contract(): void
    {
        $studentA = $this->createStudent('Student A');
        $studentB = $this->createStudent('Student B');
        $landlord = $this->createLandlord('Landlord A');
        [$property, $room] = $this->createPropertyAndRoom($landlord);

        $booking = Booking::create([
            'student_id' => $studentA->id,
            'room_id' => $room->id,
            'check_in' => now()->toDateString(),
            'check_out' => now()->addMonths(5)->toDateString(),
            'status' => 'approved',
        ]);

        $onboarding = TenantOnboarding::create([
            'booking_id' => $booking->id,
            'status' => 'documents_uploaded',
            'step' => 2,
        ]);

        $response = $this->actingAs($studentB)
            ->get("/student/onboarding/{$onboarding->id}/contract");

        $response->assertStatus(403);
    }

    public function test_landlord_cannot_view_onboarding_review_for_another_landlords_tenant(): void
    {
        $student = $this->createStudent('Student A');
        $landlordA = $this->createLandlord('Landlord A');
        $landlordB = $this->createLandlord('Landlord B');
        [$propertyA, $roomA] = $this->createPropertyAndRoom($landlordA);

        $bookingA = Booking::create([
            'student_id' => $student->id,
            'room_id' => $roomA->id,
            'check_in' => now()->toDateString(),
            'check_out' => now()->addMonths(5)->toDateString(),
            'status' => 'approved',
        ]);

        $onboardingA = TenantOnboarding::create([
            'booking_id' => $bookingA->id,
            'status' => 'documents_uploaded',
            'step' => 2,
        ]);

        $response = $this->actingAs($landlordB)
            ->get("/landlord/onboarding/{$onboardingA->id}/review");

        $response->assertStatus(403);
    }
}
