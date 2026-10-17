<?php

namespace Tests\Feature;

use App\Models\LandlordProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OnboardingFinalReviewTest extends TestCase
{
    use RefreshDatabase;

    protected string $validPngDataUrl = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAoAAAAKCAYAAACNMs+9AAAAFUlEQVR42mNk+M9QzwAEjDAGYzAEAB8uAQf+wM1RAAAAAElFTkSuQmCC';

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        Storage::fake('local');

        if (Schema::hasTable('landlord_profiles')) {
            $columnsToAdd = [
                'payment_bank_name',
                'payment_account_name',
                'payment_account_number',
                'payment_gcash_number',
                'payment_instructions',
                'payment_gcash_qr_path',
            ];
            Schema::table('landlord_profiles', function ($table) use ($columnsToAdd) {
                foreach ($columnsToAdd as $col) {
                    if (! Schema::hasColumn('landlord_profiles', $col)) {
                        $table->string($col)->nullable();
                    }
                }
            });
        }
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

    protected function createLandlordUser(array $attributes = []): User
    {
        $id = uniqid();
        $user = User::create(array_merge([
            'name' => 'Landlord '.$id,
            'full_name' => 'Landlord Full '.$id,
            'email' => 'landlord_'.$id.'@example.com',
            'password' => bcrypt('password123'),
            'role' => 'landlord',
            'contact_number' => '09181234567',
            'boarding_house_name' => 'Sunrise Boarding House',
            'address' => 'Masipit, Calapan City, Oriental Mindoro',
            'onboarding_complete' => false,
        ], $attributes));

        $user->forceFill(['email_verified_at' => now()])->save();

        return $user->refresh();
    }

    // ==========================================
    // STUDENT ONBOARDING FINAL REVIEW TESTS
    // ==========================================

    public function test_student_setup_renders_review_screen_panel_and_edit_controls(): void
    {
        $student = $this->createStudentUser();

        $response = $this->actingAs($student)->get(route('student.setup.show'));

        $response->assertOk();

        // Check Review Panel 6 exists
        $response->assertSee('data-question-panel="6"', false);
        $response->assertSee('Review your information');
        $response->assertSee('Make sure your details are correct before submitting.');

        // Check Review Sections
        $response->assertSee('Student Identity');
        $response->assertSee('Personal Details');
        $response->assertSee('Demographics & Address', false);
        $response->assertSee('Academic Information');
        $response->assertSee('Academic Verification');
        $response->assertSee('Emergency Contact');

        // Check Edit buttons for sections
        $response->assertSee('data-edit-panel="0"', false);
        $response->assertSee('data-edit-panel="1"', false);
        $response->assertSee('data-edit-panel="2"', false);
        $response->assertSee('data-edit-panel="3"', false);
        $response->assertSee('data-edit-panel="4"', false);
        $response->assertSee('data-edit-panel="5"', false);

        // Check Save & Return to Review button exists on question panels
        $response->assertSee('Save & Return to Review', false);

        // Check Final Submit button is on Review Panel
        $response->assertSee('Submit for Verification');
        $response->assertSee('id="submitFormBtn"', false);
    }

    public function test_student_setup_review_displays_not_provided_for_blank_student_id(): void
    {
        $student = $this->createStudentUser(['student_id' => null]);

        $response = $this->actingAs($student)->get(route('student.setup.show'));

        $response->assertOk();
        $response->assertSee('id="review_student_id"', false);
        $response->assertSee('Not provided');
    }

    public function test_student_setup_submission_from_review_succeeds_with_valid_data(): void
    {
        $student = $this->createStudentUser();

        $corFile = UploadedFile::fake()->create('cor_proof.pdf', 500, 'application/pdf');
        $profileImg = UploadedFile::fake()->image('profile.jpg', 200, 200);

        $payload = [
            'full_name' => 'Maria Dela Cruz',
            'contact_number' => '09171234567',
            'birth_date' => '2003-05-15',
            'gender' => 'Female',
            'address' => 'Masipit, Calapan City, Oriental Mindoro',
            'college' => 'CCS',
            'program' => 'Bachelor of Science in Information Technology',
            'year_level' => '2nd Year',
            'enrollment_proof_type' => 'cor',
            'enrollment_proof_file' => $corFile,
            'profile_image' => $profileImg,
            'parent_contact_name' => 'Juan Dela Cruz',
            'parent_contact_number' => '09181234567',
            'parent_contact_address' => 'Masipit, Calapan City, Oriental Mindoro',
            'emergency_contact_name' => 'Elena Dela Cruz',
            'emergency_contact_number' => '09191234567',
            'emergency_contact_relationship' => 'Mother',
        ];

        $response = $this->actingAs($student)->put(route('student.setup.update'), $payload);

        $response->assertRedirect(route('student.dashboard'));
        $student->refresh();
        $this->assertEquals('Maria Dela Cruz', $student->full_name);
        $this->assertEquals('2nd Year', $student->year_level);
        $this->assertNull($student->student_id);
    }

    public function test_student_setup_server_validation_rejects_invalid_data_and_preserves_input(): void
    {
        $student = $this->createStudentUser();

        $response = $this->actingAs($student)
            ->from(route('student.setup.show'))
            ->put(route('student.setup.update'), [
                'full_name' => '',
                'contact_number' => 'invalid-number',
            ]);

        $response->assertRedirect(route('student.setup.show'));
        $response->assertSessionHasErrors(['full_name', 'contact_number']);
    }

    // ==========================================
    // LANDLORD ONBOARDING FINAL REVIEW TESTS
    // ==========================================

    public function test_landlord_setup_renders_review_screen_panel_and_edit_controls(): void
    {
        $landlord = $this->createLandlordUser();

        $response = $this->actingAs($landlord)->get(route('landlord.setup.show'));

        $response->assertOk();

        // Check Review Panel 4 exists
        $response->assertSee('data-question-panel="4"', false);
        $response->assertSee('Review your information');
        $response->assertSee('Make sure your details are correct before submitting.');

        // Check Review Sections
        $response->assertSee('Landlord Details');
        $response->assertSee('Profile & Signature', false);
        $response->assertSee('Accreditation Permits');
        $response->assertSee('Payment Preferences');

        // Check Edit buttons for sections
        $response->assertSee('data-edit-panel="0"', false);
        $response->assertSee('data-edit-panel="1"', false);
        $response->assertSee('data-edit-panel="2"', false);
        $response->assertSee('data-edit-panel="3"', false);

        // Check Save & Return to Review button exists
        $response->assertSee('Save & Return to Review', false);

        // Check Final Submit button is on Review Panel
        $response->assertSee('Complete Landlord Setup');
        $response->assertSee('id="submitLandlordBtn"', false);
    }

    public function test_landlord_setup_submission_from_review_succeeds_with_drawn_signature(): void
    {
        $landlord = $this->createLandlordUser();

        $payload = [
            'full_name' => 'Don Juan Santos',
            'email' => $landlord->email,
            'contact_number' => '09181234567',
            'boarding_house_name' => 'Santos Boarding House',
            'address' => 'San Vicente, Calapan City, Oriental Mindoro',
            'about' => 'Providing comfortable student boarding since 2018.',
            'contract_signature_data' => $this->validPngDataUrl,
            'preferred_payment_methods' => ['cash', 'gcash'],
            'payment_gcash_name' => 'Don Juan S.',
            'payment_gcash_number' => '09181234567',
            'payment_instructions' => 'Please message via app after paying.',
        ];

        $response = $this->actingAs($landlord)->put(route('landlord.setup.update'), $payload);

        $response->assertRedirect(route('landlord.dashboard'));
        $landlord->refresh();
        $this->assertEquals('Don Juan Santos', $landlord->full_name);
        $this->assertTrue($landlord->onboarding_complete);

        $profile = LandlordProfile::where('user_id', $landlord->id)->first();
        $this->assertNotNull($profile);
        $this->assertNotNull($profile->contract_signature_path);
        $this->assertTrue(app(\App\Services\FileStorageService::class)->exists($profile->contract_signature_path));
    }

    public function test_landlord_setup_submission_from_review_succeeds_without_signature(): void
    {
        $landlord = $this->createLandlordUser();

        $payload = [
            'full_name' => 'Don Juan Santos',
            'email' => $landlord->email,
            'contact_number' => '09181234567',
            'boarding_house_name' => 'Santos Boarding House',
            'address' => 'San Vicente, Calapan City, Oriental Mindoro',
            'about' => 'Providing comfortable student boarding.',
            'contract_signature_data' => '',
            'preferred_payment_methods' => ['cash'],
        ];

        $response = $this->actingAs($landlord)->put(route('landlord.setup.update'), $payload);

        $response->assertRedirect(route('landlord.dashboard'));
        $landlord->refresh();
        $this->assertTrue($landlord->onboarding_complete);
    }
}
