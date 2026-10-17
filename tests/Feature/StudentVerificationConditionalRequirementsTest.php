<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StudentVerificationConditionalRequirementsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        Storage::fake('local');
    }

    protected function createStudentUser(array $attributes = []): User
    {
        $id = uniqid();
        $user = User::create(array_merge([
            'name' => 'Student '.$id,
            'full_name' => 'Student Full '.$id,
            'email' => 'student_'.$id.'@minsu.edu.ph',
            'password' => bcrypt('password123'),
            'role' => 'student',
            'contact_number' => '09171234567',
            'boarding_house_name' => 'N/A',
            'onboarding_complete' => false,
            'verification_status' => 'pending',
            'student_id' => null,
        ], $attributes));

        $user->forceFill(['email_verified_at' => now()])->save();

        return $user->refresh();
    }

    protected function createAdminUser(array $attributes = []): User
    {
        $id = uniqid();
        $user = User::create(array_merge([
            'name' => 'Admin '.$id,
            'full_name' => 'Admin Full '.$id,
            'email' => 'admin_'.$id.'@minsu.edu.ph',
            'password' => bcrypt('password123'),
            'role' => 'admin',
            'contact_number' => '09170000001',
            'boarding_house_name' => 'N/A',
            'onboarding_complete' => true,
        ], $attributes));

        $user->forceFill(['email_verified_at' => now()])->save();

        return $user->refresh();
    }

    protected function validSetupPayload(array $overrides = []): array
    {
        return array_merge([
            'full_name' => 'Juan Dela Cruz',
            'contact_number' => '09171234567',
            'college' => 'CCS',
            'program' => 'Bachelor of Science in Information Technology',
            'year_level' => '1st Year',
            'gender' => 'Male',
            'birth_date' => '2004-05-15',
            'address' => 'Calapan City, Oriental Mindoro',
            'emergency_contact_name' => 'Maria Dela Cruz',
            'emergency_contact_number' => '09181234567',
            'emergency_contact_relationship' => 'Mother',
            'parent_contact_name' => 'Pedro Dela Cruz',
            'parent_contact_number' => '09191234567',
            'parent_contact_address' => 'Calapan City, Oriental Mindoro',
            'profile_image' => UploadedFile::fake()->image('profile.jpg'),
            'enrollment_proof_type' => 'cor',
            'enrollment_proof_file' => UploadedFile::fake()->create('cor.pdf', 500, 'application/pdf'),
        ], $overrides);
    }

    // =========================================================================
    // A. Initial Student Setup
    // =========================================================================

    public function test_first_year_student_can_complete_setup_without_student_id_or_school_id()
    {
        $student = $this->createStudentUser();

        $response = $this->actingAs($student)->put(route('student.setup.update'), $this->validSetupPayload([
            'year_level' => '1st Year',
            'student_id' => '',
            // school_id_photo omitted
        ]));

        $response->assertRedirect(route('student.dashboard'));
        $student->refresh();
        $this->assertNull($student->student_id);
        $this->assertNull($student->school_id_path);
        $this->assertTrue($student->onboarding_complete);
        $this->assertEquals('pending', $student->school_id_verification_status);
    }

    public function test_higher_year_student_can_complete_setup_without_student_id_or_school_id()
    {
        $student = $this->createStudentUser();

        $response = $this->actingAs($student)->put(route('student.setup.update'), $this->validSetupPayload([
            'year_level' => '3rd Year',
            'student_id' => '',
            // school_id_photo omitted
        ]));

        $response->assertRedirect(route('student.dashboard'));
        $student->refresh();
        $this->assertNull($student->student_id);
        $this->assertNull($student->school_id_path);
        $this->assertTrue($student->onboarding_complete);
    }

    public function test_student_with_unique_student_id_is_saved()
    {
        $student = $this->createStudentUser();

        $response = $this->actingAs($student)->put(route('student.setup.update'), $this->validSetupPayload([
            'student_id' => 'MCC2026-00001',
        ]));

        $response->assertRedirect(route('student.dashboard'));
        $student->refresh();
        $this->assertEquals('MCC2026-00001', $student->student_id);
    }

    public function test_student_with_duplicate_student_id_is_rejected()
    {
        $this->createStudentUser(['student_id' => 'MCC2026-00001']);
        $student = $this->createStudentUser();

        $response = $this->actingAs($student)->from(route('student.setup.show'))->put(route('student.setup.update'), $this->validSetupPayload([
            'student_id' => 'MCC2026-00001',
        ]));

        $response->assertRedirect(route('student.setup.show'));
        $response->assertSessionHasErrors('student_id');
    }

    public function test_supplied_school_id_upload_is_validated_and_stored()
    {
        $student = $this->createStudentUser();

        $response = $this->actingAs($student)->put(route('student.setup.update'), $this->validSetupPayload([
            'school_id_photo' => UploadedFile::fake()->image('school_id.png'),
        ]));

        $response->assertRedirect(route('student.dashboard'));
        $student->refresh();
        $this->assertNotNull($student->school_id_path);
    }

    public function test_enrollment_proof_is_strictly_required()
    {
        $student = $this->createStudentUser();

        $payload = $this->validSetupPayload();
        unset($payload['enrollment_proof_file']);

        $response = $this->actingAs($student)->from(route('student.setup.show'))->put(route('student.setup.update'), $payload);

        $response->assertRedirect(route('student.setup.show'));
        $response->assertSessionHasErrors('enrollment_proof_file');
    }

    public function test_profile_photo_is_independently_required()
    {
        $student = $this->createStudentUser();

        $payload = $this->validSetupPayload();
        unset($payload['profile_image']);

        $response = $this->actingAs($student)->from(route('student.setup.show'))->put(route('student.setup.update'), $payload);

        $response->assertRedirect(route('student.setup.show'));
        $response->assertSessionHasErrors('profile_image');
    }

    // =========================================================================
    // B. Admin Request for Information
    // =========================================================================

    public function test_admin_can_request_student_id_only()
    {
        Notification::fake();
        $admin = $this->createAdminUser();
        $student = $this->createStudentUser(['onboarding_complete' => true]);

        $response = $this->actingAs($admin)->post(route('admin.student_verifications.request_information', $student), [
            'request_student_id' => '1',
            'instructions' => 'Please provide your Student ID issued by the registrar.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $student->refresh();
        $this->assertEquals('action_required', $student->school_id_verification_status);

        $activeRequest = $student->activeStudentVerificationRequest;
        $this->assertNotNull($activeRequest);
        $this->assertTrue($activeRequest->request_student_id);
        $this->assertFalse($activeRequest->request_school_id_photo);
        $this->assertEquals('Please provide your Student ID issued by the registrar.', $activeRequest->instructions);
        $this->assertEquals($admin->id, $activeRequest->requested_by);
    }

    public function test_admin_can_request_school_id_photo_only()
    {
        $admin = $this->createAdminUser();
        $student = $this->createStudentUser(['onboarding_complete' => true]);

        $response = $this->actingAs($admin)->post(route('admin.student_verifications.request_information', $student), [
            'request_school_id_photo' => '1',
            'instructions' => 'Please upload a clear photo of your school ID card.',
        ]);

        $response->assertRedirect();
        $student->refresh();

        $activeRequest = $student->activeStudentVerificationRequest;
        $this->assertNotNull($activeRequest);
        $this->assertFalse($activeRequest->request_student_id);
        $this->assertTrue($activeRequest->request_school_id_photo);
    }

    public function test_admin_can_request_both_items()
    {
        $admin = $this->createAdminUser();
        $student = $this->createStudentUser(['onboarding_complete' => true]);

        $response = $this->actingAs($admin)->post(route('admin.student_verifications.request_information', $student), [
            'request_student_id' => '1',
            'request_school_id_photo' => '1',
            'instructions' => 'Please provide both your Student ID and School ID photo.',
        ]);

        $response->assertRedirect();
        $student->refresh();

        $activeRequest = $student->activeStudentVerificationRequest;
        $this->assertNotNull($activeRequest);
        $this->assertTrue($activeRequest->request_student_id);
        $this->assertTrue($activeRequest->request_school_id_photo);
    }

    public function test_admin_request_requires_at_least_one_item()
    {
        $admin = $this->createAdminUser();
        $student = $this->createStudentUser(['onboarding_complete' => true]);

        $response = $this->actingAs($admin)->post(route('admin.student_verifications.request_information', $student), [
            'instructions' => 'No items selected.',
        ]);

        $response->assertSessionHasErrors(['requested_items']);
    }

    public function test_unauthorized_user_cannot_request_information()
    {
        $student = $this->createStudentUser(['onboarding_complete' => true]);
        $otherStudent = $this->createStudentUser(['onboarding_complete' => true]);

        $response = $this->actingAs($otherStudent)->post(route('admin.student_verifications.request_information', $student), [
            'request_student_id' => '1',
            'instructions' => 'Attacking other user.',
        ]);

        $response->assertStatus(403);
    }

    public function test_consecutive_admin_requests_safely_supersede_prior_requests()
    {
        $admin = $this->createAdminUser();
        $student = $this->createStudentUser(['onboarding_complete' => true]);

        // First request
        $this->actingAs($admin)->post(route('admin.student_verifications.request_information', $student), [
            'request_student_id' => '1',
            'instructions' => 'First request.',
        ]);

        $firstRequest = $student->activeStudentVerificationRequest;
        $this->assertNotNull($firstRequest);

        // Second request supersedes first
        $this->actingAs($admin)->post(route('admin.student_verifications.request_information', $student), [
            'request_school_id_photo' => '1',
            'instructions' => 'Second request with photo instead.',
        ]);

        $firstRequest->refresh();
        $this->assertEquals('cancelled', $firstRequest->status);

        $student->refresh();
        $secondRequest = $student->activeStudentVerificationRequest;
        $this->assertNotNull($secondRequest);
        $this->assertTrue($secondRequest->request_school_id_photo);
        $this->assertFalse($secondRequest->request_student_id);
    }

    // =========================================================================
    // C. Student Resubmission
    // =========================================================================

    public function test_student_resubmission_dynamically_enforces_only_requested_fields()
    {
        Notification::fake();
        $admin = $this->createAdminUser();
        $student = $this->createStudentUser(['onboarding_complete' => true]);

        // Request only Student ID
        $this->actingAs($admin)->post(route('admin.student_verifications.request_information', $student), [
            'request_student_id' => '1',
            'instructions' => 'Provide ID',
        ]);

        // Resubmit missing student_id fails
        $response = $this->actingAs($student)->post(route('student.verification_request.submit'), [
            'student_id' => '',
        ]);
        $response->assertSessionHasErrors('student_id');

        // Resubmit with valid student_id succeeds (without needing school_id_photo)
        $response = $this->actingAs($student)->post(route('student.verification_request.submit'), [
            'student_id' => 'MCC2026-99999',
        ]);
        $response->assertRedirect();
        $response->assertSessionHas('success');

        $student->refresh();
        $this->assertEquals('MCC2026-99999', $student->student_id);
        $this->assertEquals('pending', $student->school_id_verification_status);

        $request = $student->studentVerificationRequests()->latest()->first();
        $this->assertEquals('resubmitted', $request->status);
        $this->assertNotNull($request->resubmitted_at);
    }

    public function test_student_resubmission_enforces_school_id_photo_when_requested()
    {
        $admin = $this->createAdminUser();
        $student = $this->createStudentUser(['onboarding_complete' => true]);

        // Request only School ID photo
        $this->actingAs($admin)->post(route('admin.student_verifications.request_information', $student), [
            'request_school_id_photo' => '1',
            'instructions' => 'Upload ID photo',
        ]);

        // Resubmit without photo fails
        $response = $this->actingAs($student)->post(route('student.verification_request.submit'), []);
        $response->assertSessionHasErrors('school_id_photo');

        // Resubmit with photo succeeds
        $response = $this->actingAs($student)->post(route('student.verification_request.submit'), [
            'school_id_photo' => UploadedFile::fake()->image('school_id.png'),
        ]);
        $response->assertRedirect();
        $student->refresh();
        $this->assertNotNull($student->school_id_path);
    }

    public function test_student_resubmission_blocks_duplicate_student_id()
    {
        $this->createStudentUser(['student_id' => 'MCC2026-00001']);
        $admin = $this->createAdminUser();
        $student = $this->createStudentUser(['onboarding_complete' => true]);

        $this->actingAs($admin)->post(route('admin.student_verifications.request_information', $student), [
            'request_student_id' => '1',
            'instructions' => 'Provide ID',
        ]);

        $response = $this->actingAs($student)->post(route('student.verification_request.submit'), [
            'student_id' => 'MCC2026-00001',
        ]);

        $response->assertSessionHasErrors('student_id');
        $student->refresh();
        $this->assertNull($student->student_id);
    }

    // =========================================================================
    // D. Verification Decisions & Safeguards
    // =========================================================================

    public function test_admin_cannot_approve_student_with_unresolved_missing_requirements()
    {
        $admin = $this->createAdminUser();
        $student = $this->createStudentUser([
            'onboarding_complete' => true,
            'enrollment_proof_path' => 'students/test/enrollment-proof/cor.pdf',
        ]);

        // Admin requests Student ID
        $this->actingAs($admin)->post(route('admin.student_verifications.request_information', $student), [
            'request_student_id' => '1',
            'instructions' => 'Provide Student ID',
        ]);

        // Attempting to approve while Student ID is still missing fails
        $response = $this->actingAs($admin)->post(route('admin.student_verifications.approve', $student));
        $response->assertSessionHas('error');

        $student->refresh();
        $this->assertNotEquals('approved', $student->school_id_verification_status);
    }

    public function test_admin_can_approve_once_requested_items_are_satisfied()
    {
        $admin = $this->createAdminUser();
        $student = $this->createStudentUser([
            'onboarding_complete' => true,
            'enrollment_proof_path' => 'students/test/enrollment-proof/cor.pdf',
        ]);

        $this->actingAs($admin)->post(route('admin.student_verifications.request_information', $student), [
            'request_student_id' => '1',
            'instructions' => 'Provide Student ID',
        ]);

        // Student resubmits
        $this->actingAs($student)->post(route('student.verification_request.submit'), [
            'student_id' => 'MCC2026-12345',
        ]);

        // Admin can now approve
        $response = $this->actingAs($admin)->post(route('admin.student_verifications.approve', $student));
        $response->assertSessionHas('success');

        $student->refresh();
        $this->assertEquals('approved', $student->school_id_verification_status);
        $this->assertNotNull($student->school_id_verified_at);

        $activeRequest = $student->studentVerificationRequests()->latest()->first();
        $this->assertEquals('resolved', $activeRequest->status);
    }

    // =========================================================================
    // E. Data Consistency & Existing Accounts
    // =========================================================================

    public function test_student_id_updated_later_preserves_internal_user_id_and_relationships()
    {
        $student = $this->createStudentUser([
            'onboarding_complete' => true,
            'student_id' => null,
        ]);
        $originalId = $student->id;

        $admin = $this->createAdminUser();
        $this->actingAs($admin)->post(route('admin.student_verifications.request_information', $student), [
            'request_student_id' => '1',
            'instructions' => 'Provide ID',
        ]);

        $this->actingAs($student)->post(route('student.verification_request.submit'), [
            'student_id' => 'MCC2026-77777',
        ]);

        $student->refresh();
        $this->assertEquals($originalId, $student->id);
        $this->assertEquals('MCC2026-77777', $student->student_id);
    }
}
