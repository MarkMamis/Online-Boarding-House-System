<?php

namespace Tests\Feature;

use App\Models\IdentityRecoveryRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StudentIdentityProtectionTest extends TestCase
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

    // ==========================================
    // 1. EARLY STUDENT ID AVAILABILITY & MATCHING
    // ==========================================

    public function test_available_student_id_returns_clean_success(): void
    {
        $student = $this->createStudentUser();

        $response = $this->actingAs($student)->postJson(route('student.setup.check_student_id'), [
            'student_id' => 'MCC2026-00001',
        ]);

        $response->assertOk()
            ->assertJson([
                'available' => true,
                'status' => 'available',
            ]);
    }

    public function test_taken_student_id_returns_recovery_guidance_without_data_leakage(): void
    {
        // Existing student in database with sensitive information
        $existing = $this->createStudentUser([
            'full_name' => 'Existing Secret Student',
            'email' => 'secret_student@minsu.edu.ph',
            'contact_number' => '09189998888',
            'student_id' => 'MCC2026-99999',
        ]);

        $newStudent = $this->createStudentUser();

        $response = $this->actingAs($newStudent)->postJson(route('student.setup.check_student_id'), [
            'student_id' => 'MCC2026-99999',
        ]);

        $response->assertOk()
            ->assertJson([
                'available' => false,
                'status' => 'taken',
            ])
            ->assertJsonPath('recovery_url', route('password.request'));

        // Ensure zero personal data from the existing account holder is exposed
        $json = $response->getContent();
        $this->assertStringNotContainsString('Existing Secret Student', $json);
        $this->assertStringNotContainsString('secret_student@minsu.edu.ph', $json);
        $this->assertStringNotContainsString('09189998888', $json);
        $this->assertStringNotContainsString((string) $existing->id, $json);
    }

    public function test_student_checking_their_own_assigned_id_is_available(): void
    {
        $student = $this->createStudentUser([
            'student_id' => 'MCC2026-11111',
        ]);

        $response = $this->actingAs($student)->postJson(route('student.setup.check_student_id'), [
            'student_id' => 'MCC2026-11111',
        ]);

        $response->assertOk()
            ->assertJson([
                'available' => true,
                'status' => 'available',
            ]);
    }

    public function test_invalid_student_id_format_returns_422(): void
    {
        $student = $this->createStudentUser();

        $response = $this->actingAs($student)->postJson(route('student.setup.check_student_id'), [
            'student_id' => '!!INVALID@@',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'available' => false,
                'status' => 'invalid_format',
            ]);
    }

    // ==========================================
    // 2. ONBOARDING VIEW INTEGRATION
    // ==========================================

    public function test_student_setup_view_renders_early_student_id_question(): void
    {
        $student = $this->createStudentUser();

        $response = $this->actingAs($student)->get(route('student.setup.show'));

        $response->assertOk()
            ->assertSee('What is your Student ID?')
            ->assertSee('studentIdConflictCard')
            ->assertSee('Recover Account')
            ->assertSee('Request Assistance');
    }

    // ==========================================
    // 3. SECURE PASSWORD RECOVERY INTEGRATION
    // ==========================================

    public function test_forgot_password_page_renders_cleanly(): void
    {
        $response = $this->get(route('password.request'));

        $response->assertOk()
            ->assertSee('Recover account')
            ->assertSee('Send Recovery Link');
    }

    public function test_forgot_password_submission_provides_generic_response(): void
    {
        $student = $this->createStudentUser([
            'email' => 'juan@minsu.edu.ph',
        ]);

        $response = $this->post(route('password.email'), [
            'email' => 'juan@minsu.edu.ph',
        ]);

        $response->assertSessionHas('status');

        // Non-existent email should also receive generic response to prevent account enumeration
        $responseUnknown = $this->post(route('password.email'), [
            'email' => 'nonexistent@minsu.edu.ph',
        ]);

        $responseUnknown->assertSessionHas('status');
    }

    public function test_reset_password_page_renders_with_token(): void
    {
        $response = $this->get(route('password.reset', ['token' => 'test-token', 'email' => 'test@minsu.edu.ph']));

        $response->assertOk()
            ->assertSee('Set new password')
            ->assertSee('Reset Password');
    }

    public function test_reset_password_updates_credentials_with_valid_token(): void
    {
        $student = $this->createStudentUser([
            'email' => 'reset_me@minsu.edu.ph',
        ]);

        $token = Password::broker()->createToken($student);

        $response = $this->post(route('password.update'), [
            'token' => $token,
            'email' => 'reset_me@minsu.edu.ph',
            'password' => 'NewSecurePassword123!',
            'password_confirmation' => 'NewSecurePassword123!',
        ]);

        $response->assertRedirect(route('login'));
        $this->assertTrue(auth()->attempt([
            'email' => 'reset_me@minsu.edu.ph',
            'password' => 'NewSecurePassword123!',
        ]));
    }

    // ==========================================
    // 4. ACCOUNT ASSISTANCE REQUEST WORKFLOW
    // ==========================================

    public function test_student_can_submit_identity_assistance_request(): void
    {
        $student = $this->createStudentUser();
        $document = UploadedFile::fake()->create('id_card.jpg', 500, 'image/jpeg');

        $response = $this->actingAs($student)->postJson(route('student.setup.recovery_request'), [
            'claimed_student_id' => 'MCC2026-55555',
            'contact_email' => 'recovery_contact@example.com',
            'contact_phone' => '09191112222',
            'reason' => 'Lost access to my old email and need account recovery assistance.',
            'supporting_document' => $document,
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('identity_recovery_requests', [
            'user_id' => $student->id,
            'student_id' => 'MCC2026-55555',
            'contact_email' => 'recovery_contact@example.com',
            'status' => 'pending',
        ]);
    }

    // ==========================================
    // 5. ADMIN IDENTITY RECOVERY REVIEW
    // ==========================================

    public function test_guest_and_student_denied_from_admin_identity_reviews(): void
    {
        $student = $this->createStudentUser();

        // Guest denied or redirected
        $guestResponse = $this->get(route('admin.identity_recoveries.index'));
        $this->assertTrue(in_array($guestResponse->status(), [302, 401, 403], true));

        // Student forbidden
        $this->actingAs($student)->get(route('admin.identity_recoveries.index'))->assertForbidden();
    }

    public function test_admin_can_view_and_process_identity_recovery_requests(): void
    {
        $admin = $this->createAdminUser();
        $student = $this->createStudentUser();

        $recoveryRequest = IdentityRecoveryRequest::create([
            'user_id' => $student->id,
            'student_id' => 'MCC2026-77777',
            'contact_email' => 'claimant@example.com',
            'contact_phone' => '09175554444',
            'reason' => 'Previous account email lost.',
            'status' => 'pending',
        ]);

        // 1. Admin views index
        $indexResponse = $this->actingAs($admin)->get(route('admin.identity_recoveries.index'));
        $indexResponse->assertOk()
            ->assertSee('Identity Recovery Requests')
            ->assertSee('MCC2026-77777');

        // 2. Admin approves request
        $approveResponse = $this->actingAs($admin)->post(route('admin.identity_recoveries.approve', $recoveryRequest), [
            'admin_notes' => 'MinSU registrar cross-verified enrollment document.',
        ]);

        $approveResponse->assertRedirect();
        $this->assertDatabaseHas('identity_recovery_requests', [
            'id' => $recoveryRequest->id,
            'status' => 'approved',
            'reviewed_by' => $admin->id,
            'admin_notes' => 'MinSU registrar cross-verified enrollment document.',
        ]);
    }

    public function test_admin_can_reject_identity_recovery_request(): void
    {
        $admin = $this->createAdminUser();
        $student = $this->createStudentUser();

        $recoveryRequest = IdentityRecoveryRequest::create([
            'user_id' => $student->id,
            'student_id' => 'MCC2026-88888',
            'contact_email' => 'claimant2@example.com',
            'reason' => 'Suspicious duplicate attempt.',
            'status' => 'pending',
        ]);

        $rejectResponse = $this->actingAs($admin)->post(route('admin.identity_recoveries.reject', $recoveryRequest), [
            'admin_notes' => 'Document provided does not match student registry.',
        ]);

        $rejectResponse->assertRedirect();
        $this->assertDatabaseHas('identity_recovery_requests', [
            'id' => $recoveryRequest->id,
            'status' => 'rejected',
            'reviewed_by' => $admin->id,
            'admin_notes' => 'Document provided does not match student registry.',
        ]);
    }

    // ==========================================
    // 6. SERVER-SIDE FINAL SUBMISSION ENFORCEMENT
    // ==========================================

    public function test_final_setup_submission_enforces_student_id_uniqueness(): void
    {
        $existing = $this->createStudentUser([
            'student_id' => 'MCC2026-33333',
        ]);

        $newStudent = $this->createStudentUser();

        $response = $this->actingAs($newStudent)->put(route('student.setup.update'), [
            'full_name' => 'New Student Trying Duplicate',
            'contact_number' => '09178887777',
            'birth_date' => '2004-05-15',
            'gender' => 'Male',
            'address' => 'Masipit, Calapan City, Oriental Mindoro',
            'college' => 'CCS',
            'program' => 'BS in Information Technology',
            'year_level' => '2nd Year',
            'student_id' => 'MCC2026-33333', // Duplicate!
            'enrollment_proof_type' => 'cor',
            'parent_contact_name' => 'Parent Name',
            'parent_contact_number' => '09171112222',
            'parent_contact_address' => 'Calapan City',
            'emergency_contact_name' => 'Emergency Name',
            'emergency_contact_number' => '09173334444',
            'emergency_contact_relationship' => 'Mother',
        ]);

        $response->assertSessionHasErrors('student_id');
    }
}
