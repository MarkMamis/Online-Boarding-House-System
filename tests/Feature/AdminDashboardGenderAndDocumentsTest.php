<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\LandlordDocument;
use App\Models\LandlordProfile;
use App\Models\Property;
use App\Models\Room;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminDashboardGenderAndDocumentsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    public function test_admin_dashboard_shows_registered_student_gender_counts_when_no_active_boarders(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        // Create registered students without any active bookings
        User::factory()->create(['role' => 'student', 'gender' => 'Male', 'email_verified_at' => now()]);
        User::factory()->create(['role' => 'student', 'gender' => 'male', 'email_verified_at' => now()]);
        User::factory()->create(['role' => 'student', 'gender' => 'Female', 'email_verified_at' => now()]);
        User::factory()->create(['role' => 'student', 'gender' => 'Other', 'email_verified_at' => now()]);
        User::factory()->create(['role' => 'student', 'gender' => null, 'email_verified_at' => now()]);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertOk();
        $genderCounts = $response->viewData('genderCounts');
        $registeredGenderCounts = $response->viewData('registeredGenderCounts');

        // Total males: 2, females: 1, unspecified/other: 2
        $this->assertEquals(2, $genderCounts['male']);
        $this->assertEquals(1, $genderCounts['female']);
        $this->assertEquals(2, $genderCounts['unspecified']);

        $this->assertEquals(2, $registeredGenderCounts['male']);
        $this->assertEquals(1, $registeredGenderCounts['female']);
        $this->assertEquals(2, $registeredGenderCounts['unspecified']);
    }

    public function test_admin_dashboard_shows_active_boarders_gender_when_boarders_exist(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $landlord = User::factory()->create(['role' => 'landlord', 'email_verified_at' => now()]);
        $property = Property::create([
            'landlord_id' => $landlord->id,
            'name' => 'Sunrise Villa',
            'address' => '123 College Ave',
            'approval_status' => 'approved',
        ]);
        $room = Room::create([
            'property_id' => $property->id,
            'room_number' => '101',
            'capacity' => 2,
            'price' => 4500,
            'status' => 'available',
        ]);

        $maleStudent = User::factory()->create(['role' => 'student', 'gender' => 'Male', 'email_verified_at' => now()]);
        $femaleStudent = User::factory()->create(['role' => 'student', 'gender' => 'Female', 'email_verified_at' => now()]);
        $otherStudent = User::factory()->create(['role' => 'student', 'gender' => 'Other', 'email_verified_at' => now()]);

        // Only male and female students have active bookings
        Booking::create([
            'student_id' => $maleStudent->id,
            'room_id' => $room->id,
            'status' => 'approved',
            'check_in' => Carbon::today()->subDays(5)->toDateString(),
            'check_out' => Carbon::today()->addDays(20)->toDateString(),
        ]);

        Booking::create([
            'student_id' => $femaleStudent->id,
            'room_id' => $room->id,
            'status' => 'approved',
            'check_in' => Carbon::today()->subDays(2)->toDateString(),
            'check_out' => Carbon::today()->addDays(15)->toDateString(),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertOk();
        $genderCounts = $response->viewData('genderCounts');
        $this->assertEquals(1, $genderCounts['male']);
        $this->assertEquals(1, $genderCounts['female']);
        $this->assertEquals(0, $genderCounts['unspecified']);

        $registeredCounts = $response->viewData('registeredGenderCounts');
        $this->assertEquals(1, $registeredCounts['unspecified']);
    }

    public function test_landlord_permit_approval_in_auth_controller_syncs_to_landlord_documents(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $landlord = User::factory()->create([
            'role' => 'landlord',
            'email_verified_at' => now(),
        ]);

        $profile = LandlordProfile::create([
            'user_id' => $landlord->id,
            'contact_number' => '09171234567',
            'boarding_house_name' => 'Sunset Dorm',
            'about' => 'Cozy boarding house',
            'business_permit_path' => 'landlords/' . $landlord->id . '/business-permits/test.pdf',
            'business_permit_status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.permits.approve', $landlord));
        $response->assertRedirect();

        $profile->refresh();
        $this->assertEquals('approved', $profile->business_permit_status);

        $document = LandlordDocument::where('landlord_id', $landlord->id)
            ->where('document_type', LandlordDocument::TYPE_BUSINESS_PERMIT)
            ->first();

        $this->assertNotNull($document);
        $this->assertEquals('approved', $document->verification_status);
        $this->assertEquals('landlords/' . $landlord->id . '/business-permits/test.pdf', $document->file_path);
    }

    public function test_landlord_document_approval_syncs_back_to_landlord_profile(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $landlord = User::factory()->create([
            'role' => 'landlord',
            'email_verified_at' => now(),
        ]);

        $profile = LandlordProfile::create([
            'user_id' => $landlord->id,
            'contact_number' => '09171234567',
            'boarding_house_name' => 'Sunrise House',
            'about' => 'Peaceful place',
            'business_permit_path' => 'landlords/' . $landlord->id . '/business-permits/doc.pdf',
            'business_permit_status' => 'pending',
        ]);

        $doc = LandlordDocument::create([
            'landlord_id' => $landlord->id,
            'document_type' => LandlordDocument::TYPE_BUSINESS_PERMIT,
            'file_path' => 'landlords/' . $landlord->id . '/business-permits/doc.pdf',
            'verification_status' => LandlordDocument::STATUS_PENDING,
            'is_current' => true,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.documents.approve', $doc));
        $response->assertRedirect();

        $doc->refresh();
        $this->assertEquals(LandlordDocument::STATUS_APPROVED, $doc->verification_status);

        $profile->refresh();
        $this->assertEquals('approved', $profile->business_permit_status);
    }

    public function test_document_monitoring_displays_approved_permits(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $landlord = User::factory()->create([
            'role' => 'landlord',
            'full_name' => 'Maria Santos',
            'email_verified_at' => now(),
        ]);

        LandlordDocument::create([
            'landlord_id' => $landlord->id,
            'document_type' => LandlordDocument::TYPE_BUSINESS_PERMIT,
            'document_number' => 'BP-2026-999',
            'file_path' => 'landlords/' . $landlord->id . '/business-permits/sample.pdf',
            'verification_status' => LandlordDocument::STATUS_APPROVED,
            'expiration_date' => Carbon::today()->addMonths(6)->toDateString(),
            'is_current' => true,
            'approved_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.documents.monitoring'));
        $response->assertOk();
        $response->assertSee('Maria Santos');
        $response->assertSee('BP-2026-999');
    }

    public function test_admin_dashboard_renders_successfully_when_migrations_are_pending(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        // Request dashboard and explicitly verify that the pending migrations view contract does not throw
        $response = $this->actingAs($admin)->get(route('admin.dashboard'));
        $response->assertOk();

        // Also test rendering the view directly with hasPendingMigrations set to true
        $view = view('admin.dashboard', [
            'hasPendingMigrations' => true,
            'roleCounts' => collect(['student' => 0, 'landlord' => 0, 'admin' => 1]),
            'totalUsers' => 1,
            'todayNew' => 1,
            'last7DaysNew' => 1,
            'growthPct' => 100,
            'recentUsers' => collect([$admin]),
            'systemStatus' => ['queue' => 'sync', 'cache' => 'file', 'mail' => 'log', 'version' => '12.0'],
            'totalReports' => 0,
            'pendingReports' => 0,
            'totalOnboardings' => 0,
            'pendingOnboardings' => 0,
            'completedOnboardings' => 0,
            'activeOnboardings' => 0,
            'totalProperties' => 0,
            'activeProperties' => 0,
            'pendingApprovals' => 0,
            'totalBookings' => 0,
            'pendingBookings' => 0,
            'approvedBookings' => 0,
            'pendingPermitApprovals' => 0,
            'approvedPermitApprovals' => 0,
            'rejectedPermitApprovals' => 0,
            'activeBoardedStudents' => 0,
            'boardedByBoardingHouse' => collect(),
            'boardedByAcademic' => collect(),
            'genderCounts' => ['male' => 0, 'female' => 0, 'unspecified' => 0],
            'registeredGenderCounts' => ['male' => 0, 'female' => 0, 'unspecified' => 0],
            'boarderGenderCounts' => ['male' => 0, 'female' => 0, 'unspecified' => 0],
            'landlordMapPoints' => collect(),
        ]);

        $rendered = $view->render();
        $this->assertStringContainsString('Database Migrations Pending / Incomplete', $rendered);
        $this->assertStringContainsString('Run Migrations Now', $rendered);
    }

    public function test_admin_can_run_migrations_via_web_route(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($admin)->post(route('admin.system.migrate'));
        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    public function test_non_admin_cannot_run_migrations_via_web_route(): void
    {
        $student = User::factory()->create([
            'role' => 'student',
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($student)->post(route('admin.system.migrate'));
        $response->assertForbidden();
    }
}
