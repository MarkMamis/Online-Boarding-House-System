<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\LandlordProfile;
use App\Models\Property;
use App\Models\Room;
use App\Models\TenantOnboarding;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TenantMoveInTransformationTest extends TestCase
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
            'preferred_payment_methods' => ['gcash', 'cash'],
            'payment_gcash_name' => 'Landlord GCash',
            'business_permit_status' => 'approved',
        ]);

        return $user->refresh();
    }

    protected function createAdmin(): User
    {
        $id = uniqid();
        $user = User::create([
            'name' => 'Admin User',
            'full_name' => 'Admin Full',
            'email' => 'adm_' . $id . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'contact_number' => '09190000001',
            'boarding_house_name' => 'N/A',
            'onboarding_complete' => true,
        ]);
        $user->forceFill(['email_verified_at' => now()])->save();
        return $user->refresh();
    }

    protected function setupBookingAndOnboarding(User $student, User $landlord, string $status = 'pending'): array
    {
        $property = Property::create([
            'landlord_id' => $landlord->id,
            'name' => $landlord->name . ' Residence',
            'address' => 'Near MinSU Calapan',
            'status' => 'approved',
            'approval_status' => 'approved',
        ]);

        $room = Room::create([
            'property_id' => $property->id,
            'room_number' => '201',
            'pricing_model' => Room::PRICING_MODEL_PER_BED,
            'price' => 3000.00,
            'capacity' => 2,
            'slots_available' => 2,
            'occupied_count' => 0,
            'status' => 'available',
        ]);

        $booking = Booking::create([
            'student_id' => $student->id,
            'room_id' => $room->id,
            'check_in' => now()->toDateString(),
            'check_out' => now()->addMonths(5)->toDateString(),
            'status' => 'approved',
            'monthly_rent_amount' => 3000.00,
        ]);

        $onboarding = TenantOnboarding::create([
            'booking_id' => $booking->id,
            'status' => $status,
            'step' => 1,
        ]);

        return [$property, $room, $booking, $onboarding];
    }

    public function test_student_move_in_stage1_pending_renders_document_submission_ui(): void
    {
        $student = $this->createStudent('Alice');
        $landlord = $this->createLandlord('Bob');
        [$property, $room, $booking, $onboarding] = $this->setupBookingAndOnboarding($student, $landlord, 'pending');

        $response = $this->actingAs($student)->get(route('student.onboarding.show', $onboarding));

        $response->assertOk();
        $response->assertSee('Submit your move-in requirements');
        $response->assertSee($property->name);
        $response->assertSee('Room ' . $room->room_number);
        $response->assertSee('Submit Requirements');
    }

    public function test_student_move_in_stage2_documents_uploaded_renders_contract_and_signature_ui(): void
    {
        $student = $this->createStudent('Alice');
        $landlord = $this->createLandlord('Bob');
        [$property, $room, $booking, $onboarding] = $this->setupBookingAndOnboarding($student, $landlord, 'documents_uploaded');

        $response = $this->actingAs($student)->get(route('student.onboarding.show', $onboarding));

        $response->assertOk();
        $response->assertSee('Review & sign your lease contract');
        $response->assertSee('Sign Contract & Continue');
        $response->assertSee('Printed Full Name');
    }

    public function test_student_move_in_stage3_contract_signed_renders_payment_submission_ui(): void
    {
        $student = $this->createStudent('Alice');
        $landlord = $this->createLandlord('Bob');
        [$property, $room, $booking, $onboarding] = $this->setupBookingAndOnboarding($student, $landlord, 'contract_signed');

        $response = $this->actingAs($student)->get(route('student.onboarding.show', $onboarding));

        $response->assertOk();
        $response->assertSee('Submit your move-in payment');
        $response->assertSee('Total Amount Due');
        $response->assertSee('GCash');
        $response->assertSee('Cash on Arrival');
        $response->assertSee('Submit Move-in Payment');
    }

    public function test_student_move_in_stage4_deposit_paid_renders_waiting_for_landlord_ui(): void
    {
        $student = $this->createStudent('Alice');
        $landlord = $this->createLandlord('Bob');
        [$property, $room, $booking, $onboarding] = $this->setupBookingAndOnboarding($student, $landlord, 'deposit_paid');
        $onboarding->update([
            'payment_method' => 'gcash',
            'payment_reference' => 'GCASH-998877',
        ]);

        $response = $this->actingAs($student)->get(route('student.onboarding.show', $onboarding));

        $response->assertOk();
        $response->assertSee('Payment Submitted · Awaiting Landlord Confirmation');
        $response->assertSee('Pending Landlord Review');
        $response->assertSee('GCASH-998877');
    }

    public function test_student_move_in_stage5_completed_renders_digital_tenant_id_pass(): void
    {
        $student = $this->createStudent('Alice');
        $landlord = $this->createLandlord('Bob');
        [$property, $room, $booking, $onboarding] = $this->setupBookingAndOnboarding($student, $landlord, 'completed');
        $onboarding->update([
            'digital_id' => 'TID-123456',
        ]);

        $response = $this->actingAs($student)->get(route('student.onboarding.show', $onboarding));

        $response->assertOk();
        $response->assertSee('Welcome to Your Boarding House!');
        $response->assertSee('Digital Tenant Pass');
        $response->assertSee('TID-123456');
        $response->assertSee('Active Resident');
    }

    public function test_student_cannot_access_another_students_move_in(): void
    {
        $studentA = $this->createStudent('Alice');
        $studentB = $this->createStudent('BobStudent');
        $landlord = $this->createLandlord('Charlie');
        [$property, $room, $booking, $onboarding] = $this->setupBookingAndOnboarding($studentA, $landlord, 'pending');

        $response = $this->actingAs($studentB)->get(route('student.onboarding.show', $onboarding));

        $response->assertStatus(403);
    }

    public function test_landlord_can_view_own_incoming_tenant_review_workspace(): void
    {
        $student = $this->createStudent('Alice');
        $landlord = $this->createLandlord('Bob');
        [$property, $room, $booking, $onboarding] = $this->setupBookingAndOnboarding($student, $landlord, 'documents_uploaded');
        $onboarding->update([
            'uploaded_documents' => ['doc1.pdf'],
        ]);

        $response = $this->actingAs($landlord)->get(route('landlord.onboarding.review', $onboarding));

        $response->assertOk();
        $response->assertSee('Move-in Review · Alice Full');
        $response->assertSee('Action Required: Review Uploaded Documents');
        $response->assertSee('Approve Documents');
        $response->assertSee('Request Re-upload');
    }

    public function test_landlord_cannot_view_review_workspace_for_unowned_tenant(): void
    {
        $student = $this->createStudent('Alice');
        $landlordA = $this->createLandlord('Bob');
        $landlordB = $this->createLandlord('Charlie');
        [$property, $room, $booking, $onboarding] = $this->setupBookingAndOnboarding($student, $landlordA, 'documents_uploaded');

        $response = $this->actingAs($landlordB)->get(route('landlord.onboarding.review', $onboarding));

        $response->assertStatus(403);
    }

    public function test_landlord_can_approve_payment_and_finalize_move_in(): void
    {
        $student = $this->createStudent('Alice');
        $landlord = $this->createLandlord('Bob');
        [$property, $room, $booking, $onboarding] = $this->setupBookingAndOnboarding($student, $landlord, 'deposit_paid');
        $onboarding->update([
            'payment_method' => 'gcash',
            'payment_reference' => 'GCASH-998877',
            'deposit_amount' => 3000.00,
        ]);

        $response = $this->actingAs($landlord)->post(route('landlord.onboarding.approve_documents', $onboarding), [
            'action' => 'approve_payment',
        ]);

        $response->assertRedirect();
        $onboarding->refresh();
        $this->assertEquals('completed', $onboarding->status);
        $this->assertTrue((bool) $onboarding->deposit_paid);
        $this->assertNotNull($onboarding->digital_id);
    }

    public function test_admin_can_monitor_onboarding_details_in_read_only_mode(): void
    {
        $admin = $this->createAdmin();
        $student = $this->createStudent('Alice');
        $landlord = $this->createLandlord('Bob');
        [$property, $room, $booking, $onboarding] = $this->setupBookingAndOnboarding($student, $landlord, 'documents_uploaded');

        $response = $this->actingAs($admin)->get(route('admin.onboardings.show', $onboarding));

        $response->assertOk();
        $response->assertSee('Onboarding #' . $onboarding->id);
        $response->assertSee($student->full_name);
        $response->assertSee($property->name);
        $response->assertSee('Contract & Payment Information');
    }
}
