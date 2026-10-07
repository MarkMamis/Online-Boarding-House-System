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

class PaymentAuthorizationTest extends TestCase
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
            'preferred_payment_methods' => ['cash', 'gcash'],
            'payment_gcash_name' => 'Landlord GCash',
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

    public function test_student_can_submit_monthly_payment_for_own_booking(): void
    {
        $student = $this->createStudent('Student A');
        $landlord = $this->createLandlord('Landlord A');
        [$property, $room, $booking, $onboarding] = $this->createActiveTenancy($student, $landlord);

        $response = $this->actingAs($student)
            ->post('/student/payments', [
                'booking_id' => $booking->id,
                'payment_method' => 'cash',
                'payment_notes' => 'Handed cash to landlord in person.',
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('tenant_payments', [
            'booking_id' => $booking->id,
            'student_id' => $student->id,
            'amount_due' => 2500.00,
            'payment_method' => 'cash',
            'status' => 'submitted',
        ]);
    }

    public function test_student_cannot_submit_payment_for_another_students_booking(): void
    {
        $studentA = $this->createStudent('Student A');
        $studentB = $this->createStudent('Student B');
        $landlord = $this->createLandlord('Landlord A');
        [$property, $room, $bookingA, $onboardingA] = $this->createActiveTenancy($studentA, $landlord);

        $response = $this->actingAs($studentB)
            ->post('/student/payments', [
                'booking_id' => $bookingA->id,
                'payment_method' => 'cash',
                'payment_notes' => 'Unauthorized submission attempt.',
            ]);

        $response->assertStatus(404);
    }

    public function test_duplicate_payment_submission_for_same_due_date_is_blocked(): void
    {
        $student = $this->createStudent('Student A');
        $landlord = $this->createLandlord('Landlord A');
        [$property, $room, $booking, $onboarding] = $this->createActiveTenancy($student, $landlord);

        TenantPayment::create([
            'booking_id' => $booking->id,
            'student_id' => $student->id,
            'billing_for_date' => now()->startOfDay()->toDateString(),
            'amount_due' => 2500.00,
            'payment_method' => 'cash',
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        $response = $this->actingAs($student)
            ->post('/student/payments', [
                'booking_id' => $booking->id,
                'payment_method' => 'cash',
                'payment_notes' => 'Duplicate attempt.',
            ]);

        $response->assertSessionHas('error', 'A payment submission already exists for this billing month.');
    }

    public function test_landlord_cannot_mark_as_paid_another_landlords_booking(): void
    {
        $student = $this->createStudent('Student A');
        $landlordA = $this->createLandlord('Landlord A');
        $landlordB = $this->createLandlord('Landlord B');
        [$property, $room, $bookingA, $onboardingA] = $this->createActiveTenancy($student, $landlordA);

        $response = $this->actingAs($landlordB)
            ->post("/landlord/payments/{$bookingA->id}/paid");

        $response->assertStatus(404);
        $this->assertEquals('pending', $bookingA->fresh()->payment_status);
    }
}
