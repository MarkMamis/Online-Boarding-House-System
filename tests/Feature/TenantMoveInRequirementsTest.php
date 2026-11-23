<?php

namespace Tests\Feature;

use App\Http\Middleware\EnsureStudentSetupComplete;
use App\Models\Booking;
use App\Models\LandlordProfile;
use App\Models\Property;
use App\Models\Room;
use App\Models\TenantOnboarding;
use App\Models\User;
use App\Services\FileStorageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TenantMoveInRequirementsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('r2');
        Storage::fake('supabase');
        Storage::fake('public');
    }

    protected function createStudent(array $attributes = []): User
    {
        $id = uniqid();
        $fileStorage = app(FileStorageService::class);
        $corPath = 'students/' . $id . '/cor.pdf';
        $schoolIdPath = 'students/' . $id . '/school_id.jpg';

        $fileStorage->put('students/' . $id, '%PDF-1.4 test COR', 'pdf');
        $fileStorage->put('students/' . $id, 'test image binary', 'jpg');

        $user = User::create(array_merge([
            'name' => 'Student ' . $id,
            'full_name' => 'Student Full ' . $id,
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
            'profile_image_path' => 'students/' . $id . '/profile.jpg',
            'school_id_path' => $schoolIdPath,
            'enrollment_proof_type' => 'COR',
            'enrollment_proof_path' => $corPath,
            'emergency_contact_name' => 'Emergency Parent',
            'emergency_contact_number' => '09170000002',
            'emergency_contact_relationship' => 'Parent',
            'parent_contact_name' => 'Parent Name',
            'parent_contact_number' => '09170000003',
            'parent_contact_address' => 'Calapan City',
        ], $attributes));

        $user->forceFill(['email_verified_at' => now()])->save();
        return $user->refresh();
    }

    protected function createLandlord(array $attributes = []): User
    {
        $id = uniqid();
        $user = User::create(array_merge([
            'name' => 'Landlord ' . $id,
            'full_name' => 'Landlord Full ' . $id,
            'email' => 'lnd_' . $id . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'landlord',
            'contact_number' => '09180000001',
            'boarding_house_name' => 'Test Boarding House',
            'onboarding_complete' => true,
        ], $attributes));

        $user->forceFill(['email_verified_at' => now()])->save();

        LandlordProfile::create([
            'user_id' => $user->id,
            'contact_number' => '09180000001',
            'boarding_house_name' => 'Test Boarding House',
            'about' => 'Safe housing.',
            'preferred_payment_methods' => ['gcash', 'cash'],
            'payment_gcash_name' => 'Test GCash',
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

    protected function setupBookingAndOnboarding(User $student, User $landlord, array $onboardingAttributes = []): array
    {
        $property = Property::create([
            'landlord_id' => $landlord->id,
            'name' => 'MinSU Heights',
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

        $booking = Booking::create([
            'student_id' => $student->id,
            'room_id' => $room->id,
            'check_in' => now()->toDateString(),
            'check_out' => now()->addMonths(5)->toDateString(),
            'status' => 'approved',
            'monthly_rent_amount' => 2500.00,
        ]);

        $onboarding = TenantOnboarding::create(array_merge([
            'booking_id' => $booking->id,
            'status' => 'pending',
            'required_documents' => ['enrollment_proof', 'emergency_contact'],
            'deposit_amount' => 2500.00,
        ], $onboardingAttributes));

        return [$property, $room, $booking, $onboarding];
    }

    public function test_contract_pdf_renders_for_student_landlord_and_admin(): void
    {
        $student = $this->createStudent();
        $landlord = $this->createLandlord();
        $admin = $this->createAdmin();

        [$property, $room, $booking, $onboarding] = $this->setupBookingAndOnboarding($student, $landlord, [
            'status' => 'contract_signed',
            'contract_signed' => true,
            'contract_signed_at' => now(),
            'digital_id' => 'TID-TEST999',
        ]);

        // Student preview
        $stuResponse = $this->actingAs($student)->get(route('student.onboarding.contract_pdf', $onboarding));
        $stuResponse->assertOk();
        $stuResponse->assertHeader('Content-Type', 'application/pdf');

        // Landlord download
        $lndResponse = $this->actingAs($landlord)->get(route('landlord.onboarding.contract_pdf', [
            'onboarding' => $onboarding,
            'download' => 1,
        ]));
        $lndResponse->assertOk();
        $lndResponse->assertHeader('Content-Type', 'application/pdf');

        // Admin preview
        $admResponse = $this->actingAs($admin)->get(route('admin.onboardings.contract_pdf', $onboarding));
        $admResponse->assertOk();
        $admResponse->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_student_can_reuse_existing_profile_documents_via_physical_copies(): void
    {
        $fileStorage = app(FileStorageService::class);

        $corContent = '%PDF-1.4 test COR content';
        $corPath = $fileStorage->put('users/1/enrollment', $corContent, 'pdf');

        $schoolIdContent = 'fake image binary data for school id';
        $schoolIdPath = $fileStorage->put('users/1/school-id', $schoolIdContent, 'jpg');

        $student = $this->createStudent([
            'enrollment_proof_path' => $corPath,
            'enrollment_proof_type' => 'COR',
            'school_id_path' => $schoolIdPath,
        ]);
        $landlord = $this->createLandlord();

        [$property, $room, $booking, $onboarding] = $this->setupBookingAndOnboarding($student, $landlord, [
            'status' => 'pending',
        ]);

        $response = $this->actingAs($student)->post(route('student.onboarding.upload_documents', $onboarding), [
            'reuse_documents' => ['enrollment_proof', 'school_id'],
        ]);

        $response->assertRedirect();
        $onboarding->refresh();

        $this->assertEquals('documents_uploaded', $onboarding->status);
        $this->assertFalse($onboarding->areDocumentsApproved());
        $uploaded = (array) $onboarding->uploaded_documents;
        $this->assertCount(2, $uploaded);

        // Check that files are physically copied into tenant-onboardings/{id}/documents
        $corCopy = collect($uploaded)->first(fn ($p) => str_contains(basename($p), 'cor-coe'));
        $schoolIdCopy = collect($uploaded)->first(fn ($p) => str_contains(basename($p), 'school-id'));

        $this->assertNotNull($corCopy);
        $this->assertNotNull($schoolIdCopy);

        $this->assertStringStartsWith('tenant-onboardings/' . $onboarding->id . '/documents/', $corCopy);
        $this->assertStringStartsWith('tenant-onboardings/' . $onboarding->id . '/documents/', $schoolIdCopy);

        // Check extension preservation
        $this->assertStringEndsWith('.pdf', $corCopy);
        $this->assertStringEndsWith('.jpg', $schoolIdCopy);

        // Check content preservation
        $this->assertEquals($corContent, $fileStorage->get($corCopy));
        $this->assertEquals($schoolIdContent, $fileStorage->get($schoolIdCopy));
    }

    public function test_profile_upload_replacement_does_not_delete_move_in_copies(): void
    {
        $fileStorage = app(FileStorageService::class);

        $corContent = '%PDF-1.4 original COR';
        $corPath = $fileStorage->put('users/1/enrollment', $corContent, 'pdf');

        $student = $this->createStudent([
            'enrollment_proof_path' => $corPath,
            'enrollment_proof_type' => 'COR',
        ]);
        $landlord = $this->createLandlord();

        [$property, $room, $booking, $onboarding] = $this->setupBookingAndOnboarding($student, $landlord);

        // Move-in copies the file
        $this->actingAs($student)->post(route('student.onboarding.upload_documents', $onboarding), [
            'reuse_documents' => ['enrollment_proof'],
        ]);

        $onboarding->refresh();
        $moveInCopyPath = $onboarding->uploaded_documents[0];
        $this->assertTrue($fileStorage->exists($moveInCopyPath));

        // Now replace profile file
        $newUploadedFile = UploadedFile::fake()->create('new_cor.pdf', 100, 'application/pdf');
        $newProfilePath = $fileStorage->replace($student->enrollment_proof_path, $newUploadedFile, 'users/1/enrollment');
        $student->update(['enrollment_proof_path' => $newProfilePath]);

        // Original profile path is deleted by replace(), but move-in copy MUST still exist
        $this->assertFalse($fileStorage->exists($corPath));
        $this->assertTrue($fileStorage->exists($moveInCopyPath));
        $this->assertEquals($corContent, $fileStorage->get($moveInCopyPath));
    }

    public function test_repeated_submission_does_not_duplicate_document_copies(): void
    {
        $fileStorage = app(FileStorageService::class);

        $corPath = $fileStorage->put('users/1/enrollment', '%PDF-1.4 COR', 'pdf');
        $student = $this->createStudent(['enrollment_proof_path' => $corPath]);
        $landlord = $this->createLandlord();

        [$property, $room, $booking, $onboarding] = $this->setupBookingAndOnboarding($student, $landlord);

        // First submission
        $this->actingAs($student)->post(route('student.onboarding.upload_documents', $onboarding), [
            'reuse_documents' => ['enrollment_proof'],
        ]);
        $onboarding->refresh();
        $this->assertCount(1, $onboarding->uploaded_documents);

        // Landlord rejects and asks for re-upload
        $this->actingAs($landlord)->post(route('landlord.onboarding.approve_documents', $onboarding), [
            'action' => 'reject',
        ]);
        $onboarding->refresh();
        $this->assertEquals('pending', $onboarding->status);

        // Student submits reuse again
        $this->actingAs($student)->post(route('student.onboarding.upload_documents', $onboarding), [
            'reuse_documents' => ['enrollment_proof'],
        ]);
        $onboarding->refresh();
        $this->assertCount(1, $onboarding->uploaded_documents);
    }

    public function test_ownership_isolation_prevents_submitting_other_student_onboarding(): void
    {
        $studentA = $this->createStudent(['name' => 'Student A']);
        $studentB = $this->createStudent(['name' => 'Student B']);
        $landlord = $this->createLandlord();

        [$property, $room, $booking, $onboarding] = $this->setupBookingAndOnboarding($studentA, $landlord);

        // Student B tries to upload documents to Student A's onboarding
        $response = $this->actingAs($studentB)->post(route('student.onboarding.upload_documents', $onboarding), [
            'reuse_documents' => ['enrollment_proof'],
        ]);

        $response->assertStatus(403);
    }

    public function test_emergency_contact_verified_from_profile_without_demanding_file(): void
    {
        $fileStorage = app(FileStorageService::class);
        $corPath = $fileStorage->put('users/1/enrollment', '%PDF-1.4 COR', 'pdf');

        // Student with no emergency contact details
        $student = $this->createStudent([
            'emergency_contact_name' => null,
            'emergency_contact_number' => null,
            'parent_contact_name' => null,
            'parent_contact_number' => null,
            'enrollment_proof_path' => $corPath,
        ]);
        $landlord = $this->createLandlord();

        [$property, $room, $booking, $onboarding] = $this->setupBookingAndOnboarding($student, $landlord);

        $response = $this->withoutMiddleware(EnsureStudentSetupComplete::class)
            ->actingAs($student)
            ->post(route('student.onboarding.upload_documents', $onboarding), [
                'reuse_documents' => ['enrollment_proof'],
            ]);

        // Blocked because emergency contact is missing from profile
        $response->assertSessionHas('error');
        $onboarding->refresh();
        $this->assertEquals('pending', $onboarding->status);

        // Update profile with emergency contact
        $student->update([
            'emergency_contact_name' => 'Mom',
            'emergency_contact_number' => '09171234567',
            'emergency_contact_relationship' => 'Mother',
        ]);

        $response2 = $this->actingAs($student)->post(route('student.onboarding.upload_documents', $onboarding), [
            'reuse_documents' => ['enrollment_proof'],
        ]);

        $response2->assertSessionHas('success');
        $onboarding->refresh();
        $this->assertEquals('documents_uploaded', $onboarding->status);
    }

    public function test_legacy_pending_records_do_not_require_proof_of_income_or_three_files(): void
    {
        $fileStorage = app(FileStorageService::class);
        $corPath = $fileStorage->put('users/1/enrollment', '%PDF-1.4 COR', 'pdf');

        $student = $this->createStudent([
            'enrollment_proof_path' => $corPath,
        ]);
        $landlord = $this->createLandlord();

        // Legacy record carrying hardcoded defaults: ['student_id', 'proof_of_income', 'emergency_contact']
        [$property, $room, $booking, $onboarding] = $this->setupBookingAndOnboarding($student, $landlord, [
            'required_documents' => ['student_id', 'proof_of_income', 'emergency_contact'],
            'status' => 'pending',
        ]);

        // Student only reuses enrollment proof (1 document)
        $response = $this->actingAs($student)->post(route('student.onboarding.upload_documents', $onboarding), [
            'reuse_documents' => ['enrollment_proof'],
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $onboarding->refresh();
        $this->assertEquals('documents_uploaded', $onboarding->status);
    }

    public function test_student_cannot_sign_contract_until_landlord_approves_documents(): void
    {
        $student = $this->createStudent();
        $landlord = $this->createLandlord();

        [$property, $room, $booking, $onboarding] = $this->setupBookingAndOnboarding($student, $landlord, [
            'status' => 'documents_uploaded',
            'uploaded_documents' => ['tenant-onboardings/1/documents/cor.pdf'],
            'required_documents' => ['_documents_approved' => false],
        ]);

        $response = $this->actingAs($student)->post(route('student.onboarding.sign_contract', $onboarding), [
            'signature_data' => 'data:image/png;base64,' . base64_encode('fake signature png'),
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $onboarding->refresh();
        $this->assertFalse((bool) $onboarding->contract_signed);

        // Landlord approves documents
        $this->actingAs($landlord)->post(route('landlord.onboarding.approve_documents', $onboarding), [
            'action' => 'approve',
        ]);
        $onboarding->refresh();
        $this->assertTrue($onboarding->areDocumentsApproved());

        // Now student can sign contract
        $response2 = $this->actingAs($student)->post(route('student.onboarding.sign_contract', $onboarding), [
            'signature_data' => 'data:image/png;base64,' . base64_encode('fake signature png'),
        ]);

        $response2->assertSessionHas('success');
        $onboarding->refresh();
        $this->assertTrue((bool) $onboarding->contract_signed);
        $this->assertEquals('contract_signed', $onboarding->status);
    }

    public function test_document_resubmission_blocked_after_contract_signed(): void
    {
        $student = $this->createStudent();
        $landlord = $this->createLandlord();

        [$property, $room, $booking, $onboarding] = $this->setupBookingAndOnboarding($student, $landlord, [
            'status' => 'contract_signed',
            'contract_signed' => true,
            'contract_signed_at' => now(),
            'uploaded_documents' => ['tenant-onboardings/1/documents/cor.pdf'],
        ]);

        $response = $this->actingAs($student)->post(route('student.onboarding.upload_documents', $onboarding), [
            'reuse_documents' => ['enrollment_proof'],
        ]);

        $response->assertSessionHas('error');
        $onboarding->refresh();
        // State remains contract_signed
        $this->assertEquals('contract_signed', $onboarding->status);
    }

    public function test_completion_requires_student_signature_landlord_countersignature_and_payment(): void
    {
        $student = $this->createStudent();
        $landlord = $this->createLandlord();

        [$property, $room, $booking, $onboarding] = $this->setupBookingAndOnboarding($student, $landlord, [
            'status' => 'deposit_paid',
            'contract_signed' => true,
            'contract_signature_path' => 'signatures/student.png',
            'landlord_contract_signed' => false, // Landlord has not countersigned
            'deposit_amount' => 2500.00,
        ]);

        // Attempting to approve payment before landlord countersignature
        $response = $this->actingAs($landlord)->post(route('landlord.onboarding.approve_documents', $onboarding), [
            'action' => 'approve_payment',
        ]);

        $response->assertSessionHas('error');
        $onboarding->refresh();
        $this->assertNotEquals('completed', $onboarding->status);

        // Landlord countersigns
        $onboarding->update([
            'landlord_contract_signed' => true,
            'landlord_contract_signature_path' => 'signatures/landlord.png',
        ]);

        // Now approve payment succeeds
        $response2 = $this->actingAs($landlord)->post(route('landlord.onboarding.approve_documents', $onboarding), [
            'action' => 'approve_payment',
        ]);

        $response2->assertSessionHas('success');
        $onboarding->refresh();
        $this->assertEquals('completed', $onboarding->status);
        $this->assertTrue((bool) $onboarding->deposit_paid);
        $this->assertNotNull($onboarding->digital_id);
    }

    public function test_missing_source_files_handled_gracefully(): void
    {
        // Student references a file that does not exist on disk
        $student = $this->createStudent([
            'enrollment_proof_path' => 'nonexistent/path/cor.pdf',
        ]);
        $landlord = $this->createLandlord();

        [$property, $room, $booking, $onboarding] = $this->setupBookingAndOnboarding($student, $landlord);

        $response = $this->actingAs($student)->post(route('student.onboarding.upload_documents', $onboarding), [
            'reuse_documents' => ['enrollment_proof'],
        ]);

        $response->assertSessionHas('error');
        $onboarding->refresh();
        $this->assertEquals('pending', $onboarding->status);
    }

    public function test_large_document_exceeding_previous_5mb_limit_is_accepted(): void
    {
        $student = $this->createStudent();
        $landlord = $this->createLandlord();

        [$property, $room, $booking, $onboarding] = $this->setupBookingAndOnboarding($student, $landlord);

        // Upload an 8MB PDF file (previously rejected by max:5120)
        $largePdf = UploadedFile::fake()->create('large_academic_doc.pdf', 8192, 'application/pdf');

        $response = $this->actingAs($student)->post(route('student.onboarding.upload_documents', $onboarding), [
            'documents' => [$largePdf],
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertSessionHas('success');

        $onboarding->refresh();
        $this->assertEquals('documents_uploaded', $onboarding->status);
        $this->assertNotEmpty($onboarding->uploaded_documents);
        $storedPath = $onboarding->uploaded_documents[0];
        $this->assertTrue(app(FileStorageService::class)->exists($storedPath));
    }

    public function test_large_deposit_payment_proof_exceeding_previous_5mb_limit_is_accepted(): void
    {
        $student = $this->createStudent();
        $landlord = $this->createLandlord();

        [$property, $room, $booking, $onboarding] = $this->setupBookingAndOnboarding($student, $landlord);

        $onboarding->update([
            'status' => 'contract_signed',
            'contract_agreed' => true,
            'contract_agreed_at' => now(),
            'contract_signature_path' => 'signatures/student.png',
        ]);

        // Upload a 7MB JPG payment receipt (previously rejected by max:5120)
        $largeReceipt = UploadedFile::fake()->create('large_receipt.jpg', 7168, 'image/jpeg');

        $response = $this->actingAs($student)->post(route('student.onboarding.pay_deposit', $onboarding), [
            'payment_method' => 'gcash',
            'payment_reference' => 'GCASH-987654321',
            'payment_proof' => $largeReceipt,
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertSessionHas('success');

        $onboarding->refresh();
        $this->assertEquals('deposit_paid', $onboarding->status);
        $this->assertNotNull($onboarding->payment_proof_path);
        $this->assertTrue(app(FileStorageService::class)->exists($onboarding->payment_proof_path));
    }

    public function test_invalid_mime_type_document_is_rejected(): void
    {
        $student = $this->createStudent();
        $landlord = $this->createLandlord();

        [$property, $room, $booking, $onboarding] = $this->setupBookingAndOnboarding($student, $landlord);

        // Upload an invalid executable file
        $invalidFile = UploadedFile::fake()->create('malicious.exe', 500, 'application/x-msdownload');

        $response = $this->actingAs($student)->post(route('student.onboarding.upload_documents', $onboarding), [
            'documents' => [$invalidFile],
        ]);

        $response->assertSessionHasErrors(['documents.0']);
        $onboarding->refresh();
        $this->assertEquals('pending', $onboarding->status);
    }

    public function test_invalid_payment_proof_mime_type_is_rejected(): void
    {
        $student = $this->createStudent();
        $landlord = $this->createLandlord();

        [$property, $room, $booking, $onboarding] = $this->setupBookingAndOnboarding($student, $landlord);

        $onboarding->update([
            'status' => 'contract_signed',
            'contract_agreed' => true,
            'contract_agreed_at' => now(),
            'contract_signature_path' => 'signatures/student.png',
        ]);

        $invalidFile = UploadedFile::fake()->create('notes.txt', 200, 'text/plain');

        $response = $this->actingAs($student)->post(route('student.onboarding.pay_deposit', $onboarding), [
            'payment_method' => 'gcash',
            'payment_reference' => 'GCASH-987654321',
            'payment_proof' => $invalidFile,
        ]);

        $response->assertSessionHasErrors(['payment_proof']);
        $onboarding->refresh();
        $this->assertEquals('contract_signed', $onboarding->status);
        $this->assertNull($onboarding->payment_proof_path);
    }
}
