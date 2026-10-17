<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\LandlordProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UnifiedOnboardingExperienceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');

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
                    if (!Schema::hasColumn('landlord_profiles', $col)) {
                        $table->string($col)->nullable();
                    }
                }
            });
        }
    }

    protected function createUser(array $attributes = []): User
    {
        $id = uniqid();
        $user = User::create(array_merge([
            'name' => 'User ' . $id,
            'full_name' => 'Full Name ' . $id,
            'email' => 'user_' . $id . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'pending',
            'contact_number' => '09170000000',
            'boarding_house_name' => 'N/A',
            'onboarding_complete' => false,
        ], $attributes));

        $user->forceFill(['email_verified_at' => now()])->save();

        return $user->refresh();
    }

    public function test_role_selection_renders_question_based_layout_without_stepper(): void
    {
        $user = $this->createUser(['role' => 'pending']);

        $response = $this->actingAs($user)->get(route('onboarding.role.show'));

        $response->assertOk()
            ->assertSee('How will you use OBHS?')
            ->assertSee('Choose the experience that fits your needs.')
            ->assertSee('Student')
            ->assertSee('Landlord')
            ->assertSee('Continue')
            ->assertDontSee('Step 1 of')
            ->assertDontSee('stepper-track');
    }

    public function test_role_selection_submits_student_role_and_redirects_to_student_setup(): void
    {
        $user = $this->createUser(['role' => 'pending']);

        $response = $this->actingAs($user)->post(route('onboarding.role.store'), [
            'role' => 'student',
        ]);

        $response->assertRedirect(route('student.setup.show'));
        $this->assertEquals('student', $user->fresh()->role);
        $this->assertFalse((bool) $user->fresh()->onboarding_complete);
    }

    public function test_role_selection_submits_landlord_role_and_redirects_to_landlord_setup(): void
    {
        $user = $this->createUser(['role' => 'pending']);

        $response = $this->actingAs($user)->post(route('onboarding.role.store'), [
            'role' => 'landlord',
        ]);

        $response->assertRedirect(route('landlord.setup.show'));
        $this->assertEquals('landlord', $user->fresh()->role);
        $this->assertNotNull($user->fresh()->landlordProfile);
    }

    public function test_role_selection_rejects_invalid_role(): void
    {
        $user = $this->createUser(['role' => 'pending']);

        $response = $this->actingAs($user)->post(route('onboarding.role.store'), [
            'role' => 'admin',
        ]);

        $response->assertSessionHasErrors('role');
        $this->assertEquals('pending', $user->fresh()->role);
    }

    public function test_student_setup_renders_without_stepper_and_contains_all_fields(): void
    {
        $user = $this->createUser([
            'role' => 'student',
            'onboarding_complete' => false,
        ]);

        $response = $this->actingAs($user)->get(route('student.setup.show'));

        $response->assertOk()
            ->assertSee('What should we call you and how can we reach you?')
            ->assertSee('Tell us a bit more about yourself')
            ->assertSee('What is your academic standing at MinSU?')
            ->assertSee('Verify your student enrollment')
            ->assertSee('Who should we contact in case of emergency?')
            ->assertSee('Submit for Verification')
            ->assertDontSee('Step 1 of 3')
            ->assertDontSee('Step 2 of 3')
            ->assertDontSee('Step 3 of 3')
            ->assertDontSee('stepper-track')
            ->assertDontSee('step-chip');
    }

    public function test_student_setup_validates_required_fields(): void
    {
        $user = $this->createUser([
            'role' => 'student',
            'onboarding_complete' => false,
        ]);

        $response = $this->actingAs($user)->put(route('student.setup.update'), [
            'full_name' => '',
            'contact_number' => '1234',
        ]);

        $response->assertSessionHasErrors(['full_name', 'contact_number']);
    }

    public function test_student_setup_submits_and_completes_student_profile(): void
    {
        $user = $this->createUser([
            'role' => 'student',
            'onboarding_complete' => false,
        ]);

        $profilePhoto = UploadedFile::fake()->image('profile.jpg');
        $schoolIdPhoto = UploadedFile::fake()->image('school_id.jpg');
        $corFile = UploadedFile::fake()->create('cor.pdf', 500, 'application/pdf');

        $response = $this->actingAs($user)->put(route('student.setup.update'), [
            'full_name' => 'Juan Dela Cruz',
            'contact_number' => '09171234567',
            'birth_date' => '2003-05-15',
            'gender' => 'Male',
            'address' => 'Masipit, Calapan City, Oriental Mindoro',
            'college' => 'CCS',
            'program' => 'Bachelor of Science in Information Technology',
            'year_level' => '2nd Year',
            'student_id' => 'MCC2024-00123',
            'profile_image' => $profilePhoto,
            'school_id_photo' => $schoolIdPhoto,
            'enrollment_proof_type' => 'cor',
            'enrollment_proof_file' => $corFile,
            'parent_contact_name' => 'Maria Dela Cruz',
            'parent_contact_number' => '09179876543',
            'parent_contact_address' => 'Masipit, Calapan City, Oriental Mindoro',
            'emergency_contact_name' => 'Pedro Dela Cruz',
            'emergency_contact_number' => '09181112233',
            'emergency_contact_relationship' => 'Father',
        ]);

        $response->assertRedirect(route('student.dashboard'));
        $refreshed = $user->fresh();
        $this->assertTrue((bool) $refreshed->onboarding_complete);
        $this->assertEquals('Juan Dela Cruz', $refreshed->full_name);
        $this->assertEquals('09171234567', $refreshed->contact_number);
        $this->assertEquals('Bachelor of Science in Information Technology', $refreshed->program);
    }

    public function test_landlord_setup_renders_without_stepper_and_contains_all_fields(): void
    {
        $user = $this->createUser([
            'role' => 'landlord',
            'onboarding_complete' => false,
        ]);

        $user->landlordProfile()->create([
            'contact_number' => '09170000000',
            'boarding_house_name' => 'Sunshine Home',
            'business_permit_status' => 'not_submitted',
        ]);

        $response = $this->actingAs($user)->get(route('landlord.setup.show'));

        $response->assertOk()
            ->assertSee('Tell us about you and your boarding house')
            ->assertSee('Introduce your boarding house to students')
            ->assertSee('Upload your accreditation permits')
            ->assertSee('How would you like to receive rental payments?')
            ->assertSee('Complete Landlord Setup')
            ->assertDontSee('Step 1 of 3')
            ->assertDontSee('stepper-track')
            ->assertDontSee('step-chip');
    }

    public function test_landlord_setup_submits_and_completes_landlord_profile(): void
    {
        $user = $this->createUser([
            'role' => 'landlord',
            'onboarding_complete' => false,
        ]);

        $user->landlordProfile()->create([
            'contact_number' => '09170000000',
            'boarding_house_name' => 'Sunshine Home',
            'business_permit_status' => 'not_submitted',
        ]);

        $response = $this->actingAs($user)->put(route('landlord.setup.update'), [
            'full_name' => 'Maria Santos',
            'email' => $user->email,
            'contact_number' => '09187654321',
            'boarding_house_name' => 'Santos Boarding Haven',
            'address' => 'Salong, Calapan City, Oriental Mindoro',
            'about' => 'A safe, clean, and quiet boarding house near MinSU campus with complete amenities.',
            'preferred_payment_methods' => ['cash', 'gcash'],
            'payment_gcash_name' => 'Maria S.',
            'payment_gcash_number' => '09187654321',
        ]);

        $response->assertRedirect(route('landlord.dashboard'));
        $refreshed = $user->fresh();
        $this->assertEquals('Maria Santos', $refreshed->full_name);
        $this->assertEquals('Santos Boarding Haven', $refreshed->boarding_house_name);
        $this->assertTrue((bool) $refreshed->landlordProfile->profile_completed);
    }
}
