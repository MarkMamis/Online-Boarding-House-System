<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\LandlordProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthRoleAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function baseUser(array $attributes = []): User
    {
        $id = uniqid();
        $user = User::create(array_merge([
            'name' => 'User ' . $id,
            'full_name' => 'Full Name ' . $id,
            'email' => 'user_' . $id . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'student',
            'contact_number' => '09170000000',
            'boarding_house_name' => 'N/A',
            'onboarding_complete' => false,
        ], $attributes));

        return $user;
    }

    protected function createCompleteStudent(array $attributes = []): User
    {
        $user = $this->baseUser(array_merge([
            'name' => 'Complete Student',
            'full_name' => 'Complete Student Full',
            'role' => 'student',
            'onboarding_complete' => true,
            'contact_number' => '09170000001',
            'birth_date' => '2004-01-01',
            'college' => 'College of Computer Studies',
            'program' => 'BS Information Technology',
            'year_level' => '2nd Year',
            'gender' => 'Male',
            'address' => 'Calapan City',
            'student_id' => '2022-00123',
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
        ], $attributes));

        $user->forceFill(['email_verified_at' => now()])->save();
        return $user->refresh();
    }

    protected function createCompleteLandlord(array $attributes = []): User
    {
        $user = $this->baseUser(array_merge([
            'name' => 'Complete Landlord',
            'full_name' => 'Complete Landlord Full',
            'role' => 'landlord',
            'contact_number' => '09180000001',
            'boarding_house_name' => 'Sunshine Dormitory',
            'onboarding_complete' => true,
        ], $attributes));

        $user->forceFill(['email_verified_at' => now()])->save();

        LandlordProfile::create([
            'user_id' => $user->id,
            'contact_number' => '09180000001',
            'boarding_house_name' => 'Sunshine Dormitory',
            'about' => 'Clean and safe boarding house for students.',
            'preferred_payment_methods' => ['gcash', 'cash'],
            'payment_gcash_name' => 'Landlord GCash',
            'business_permit_status' => 'approved',
        ]);

        return $user->refresh();
    }

    protected function createAdmin(): User
    {
        $user = $this->baseUser([
            'name' => 'System Admin',
            'full_name' => 'System Admin Full',
            'role' => 'admin',
            'onboarding_complete' => true,
        ]);

        $user->forceFill(['email_verified_at' => now()])->save();
        return $user->refresh();
    }

    public function test_guest_cannot_access_student_dashboard(): void
    {
        $response = $this->get('/student/dashboard');
        $response->assertStatus(403);
    }

    public function test_guest_cannot_access_landlord_dashboard(): void
    {
        $response = $this->get('/landlord/dashboard');
        $response->assertStatus(403);
    }

    public function test_guest_cannot_access_admin_dashboard(): void
    {
        $response = $this->get('/admin/dashboard');
        $response->assertStatus(403);
    }

    public function test_student_cannot_access_landlord_dashboard(): void
    {
        $student = $this->createCompleteStudent();

        $response = $this->actingAs($student)->get('/landlord/dashboard');
        $response->assertStatus(403);
    }

    public function test_student_cannot_access_admin_dashboard(): void
    {
        $student = $this->createCompleteStudent();

        $response = $this->actingAs($student)->get('/admin/dashboard');
        $response->assertStatus(403);
    }

    public function test_landlord_cannot_access_student_dashboard(): void
    {
        $landlord = $this->createCompleteLandlord();

        $response = $this->actingAs($landlord)->get('/student/dashboard');
        $response->assertStatus(403);
    }

    public function test_landlord_cannot_access_admin_dashboard(): void
    {
        $landlord = $this->createCompleteLandlord();

        $response = $this->actingAs($landlord)->get('/admin/dashboard');
        $response->assertStatus(403);
    }

    public function test_user_with_pending_role_is_redirected_to_role_selection(): void
    {
        $pendingUser = $this->baseUser([
            'name' => 'Pending User',
            'full_name' => 'Pending User Full',
            'role' => 'pending',
        ]);
        $pendingUser->forceFill(['email_verified_at' => now()])->save();

        $response = $this->actingAs($pendingUser)->get('/student/dashboard');
        $response->assertStatus(403);

        $landingResponse = $this->actingAs($pendingUser)->get('/');
        $landingResponse->assertRedirect(route('onboarding.role.show'));
    }

    public function test_student_with_incomplete_setup_is_redirected_to_student_setup(): void
    {
        $incompleteStudent = $this->baseUser([
            'name' => 'Incomplete Student',
            'full_name' => 'Incomplete Student Full',
            'role' => 'student',
            'onboarding_complete' => false,
        ]);
        $incompleteStudent->forceFill(['email_verified_at' => now()])->save();

        $response = $this->actingAs($incompleteStudent)->get('/student/dashboard');
        $response->assertRedirect(route('student.setup.show'));
    }

    public function test_unverified_student_cannot_access_verified_routes(): void
    {
        $unverifiedStudent = $this->baseUser([
            'name' => 'Unverified Student',
            'full_name' => 'Unverified Student Full',
            'role' => 'student',
            'onboarding_complete' => true,
        ]);
        // email_verified_at is null

        $response = $this->actingAs($unverifiedStudent)->get('/student/dashboard');
        $response->assertRedirect(route('verification.notice'));
    }
}
