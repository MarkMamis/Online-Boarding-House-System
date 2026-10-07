<?php

namespace Tests\Feature;

use App\Models\LandlordDocument;
use App\Models\LandlordProfile;
use App\Models\Property;
use App\Models\Room;
use App\Models\TenantOnboarding;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminMonitoringManagementSeparationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'filesystems.disks.r2.key' => 'test-r2-key',
            'filesystems.disks.r2.secret' => 'test-r2-secret',
            'filesystems.disks.r2.bucket' => 'test-r2-bucket',
            'filesystems.disks.r2.endpoint' => 'https://r2.test.invalid',
            'filesystems.disks.supabase.key' => 'test-supabase-key',
            'filesystems.disks.supabase.endpoint' => 'https://supabase.test.invalid',
        ]);

        Storage::fake('r2');
        Storage::fake('supabase');
        Storage::fake('public');
    }

    // =========================================================================
    // 1. MONITORING ACCESS
    // =========================================================================

    public function test_admin_can_access_boarding_house_monitoring(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get(route('admin.properties.index'));

        $response->assertOk()
            ->assertSee('Boarding Houses')
            ->assertSee('Total Properties');
    }

    public function test_admin_can_access_active_boarders(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get(route('admin.boarding_monitoring.students'));

        $response->assertOk()
            ->assertSee('Active Boarders')
            ->assertSee('Current Boarders');
    }

    public function test_admin_can_access_onboarding_pipeline(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get(route('admin.onboardings.index'));

        $response->assertOk()
            ->assertSee('Onboarding Pipeline');
    }

    public function test_admin_can_access_compliance_monitor(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get(route('admin.documents.monitoring'));

        $response->assertOk()
            ->assertSee('Compliance Monitor');
    }

    // =========================================================================
    // 2. MANAGEMENT ACCESS
    // =========================================================================

    public function test_admin_can_access_property_reviews(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get(route('admin.property_reviews.index'));

        $response->assertOk()
            ->assertSee('Property Reviews');
    }

    public function test_admin_can_access_student_verification(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get(route('admin.approvals.students'));

        $response->assertOk()
            ->assertSee('Student Verification');
    }

    public function test_admin_can_access_landlord_accreditation(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get(route('admin.approvals.landlords'));

        $response->assertOk()
            ->assertSee('Landlord Accreditation');
    }

    public function test_admin_can_access_document_review_queue(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get(route('admin.documents.verification'));

        $response->assertOk()
            ->assertSee('Document Review Queue');
    }

    // =========================================================================
    // 3. ROLE ISOLATION & ACCESS CONTROL
    // =========================================================================

    public function test_student_cannot_access_admin_monitoring_or_management(): void
    {
        $student = $this->createStudent('Juan Student');

        $this->actingAs($student)->get(route('admin.properties.index'))->assertForbidden();
        $this->actingAs($student)->get(route('admin.property_reviews.index'))->assertForbidden();
        $this->actingAs($student)->get(route('admin.approvals.students'))->assertForbidden();
        $this->actingAs($student)->get(route('admin.documents.verification'))->assertForbidden();
        $this->actingAs($student)->get(route('admin.documents.monitoring'))->assertForbidden();
    }

    public function test_landlord_cannot_access_admin_monitoring_or_management(): void
    {
        $landlord = $this->createLandlord('Pedro Landlord');

        $this->actingAs($landlord)->get(route('admin.properties.index'))->assertForbidden();
        $this->actingAs($landlord)->get(route('admin.property_reviews.index'))->assertForbidden();
        $this->actingAs($landlord)->get(route('admin.approvals.landlords'))->assertForbidden();
        $this->actingAs($landlord)->get(route('admin.documents.verification'))->assertForbidden();
        $this->actingAs($landlord)->get(route('admin.documents.monitoring'))->assertForbidden();
    }

    // =========================================================================
    // 4. PROPERTY REVIEW DECISION LOGIC
    // =========================================================================

    public function test_admin_can_review_property_using_existing_workflow(): void
    {
        $admin = $this->createAdmin();
        $landlord = $this->createLandlord('Landlord Owner');
        $property = Property::create([
            'landlord_id' => $landlord->id,
            'name' => 'Pending Review Dorm',
            'address' => 'Calapan City',
            'approval_status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.properties.approve', $property));

        $response->assertRedirect();
        $this->assertDatabaseHas('properties', [
            'id' => $property->id,
            'approval_status' => 'approved',
        ]);
    }

    public function test_non_admin_cannot_approve_property(): void
    {
        $landlord = $this->createLandlord('Landlord Owner');
        $property = Property::create([
            'landlord_id' => $landlord->id,
            'name' => 'Pending Review Dorm',
            'address' => 'Calapan City',
            'approval_status' => 'pending',
        ]);

        $this->actingAs($landlord)
            ->post(route('admin.properties.approve', $property))
            ->assertForbidden();

        $this->assertDatabaseHas('properties', [
            'id' => $property->id,
            'approval_status' => 'pending',
        ]);
    }

    // =========================================================================
    // 5. BACKWARD COMPATIBILITY
    // =========================================================================

    public function test_legacy_pending_properties_url_redirects_to_property_reviews(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get(route('admin.properties.pending'));

        $response->assertRedirect(route('admin.property_reviews.index', ['status' => 'pending']));
    }

    // =========================================================================
    // TEST HELPERS
    // =========================================================================

    protected function createAdmin(): User
    {
        $user = User::factory()->create([
            'role' => 'admin',
            'full_name' => 'OBHS Admin',
            'email' => 'admin-' . uniqid() . '@minsu.edu.ph',
        ]);
        $user->forceFill(['email_verified_at' => now()])->save();
        return $user;
    }

    protected function createStudent(string $name): User
    {
        $user = User::factory()->create([
            'role' => 'student',
            'full_name' => $name,
            'email' => 'student-' . uniqid() . '@minsu.edu.ph',
            'school_id_verification_status' => 'verified',
        ]);
        $user->forceFill(['email_verified_at' => now()])->save();
        return $user;
    }

    protected function createLandlord(string $name): User
    {
        $user = User::factory()->create([
            'role' => 'landlord',
            'full_name' => $name,
            'email' => 'landlord-' . uniqid() . '@example.com',
        ]);
        $user->forceFill(['email_verified_at' => now()])->save();

        LandlordProfile::create([
            'user_id' => $user->id,
            'business_permit_status' => 'approved',
            'safety_certificate_status' => 'approved',
        ]);

        return $user;
    }
}
