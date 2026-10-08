<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\LandlordDocument;
use App\Models\LandlordProfile;
use App\Models\Property;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandlordMonitoringManagementTest extends TestCase
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
            'address' => 'Calapan City',
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
            'boarding_house_name' => $name . ' House',
            'onboarding_complete' => true,
            'address' => 'Calapan City',
        ]);
        $user->forceFill(['email_verified_at' => now()])->save();

        LandlordProfile::create([
            'user_id' => $user->id,
            'boarding_house_name' => $name . ' House',
            'about' => 'Clean boarding house',
            'business_permit_status' => 'approved',
            'business_permit_path' => 'permits/' . $id . '.pdf',
            'contact_number' => '09180000001',
        ]);

        return $user->refresh();
    }

    protected function createPropertyWithRoom(User $landlord, float $price = 2500.0, int $capacity = 2): array
    {
        $property = Property::create([
            'landlord_id' => $landlord->id,
            'name' => 'Sunset Hall ' . uniqid(),
            'description' => 'Great place',
            'address' => 'Calapan City',
            'gender_preference' => 'all',
            'status' => 'approved',
            'min_price' => $price,
            'max_price' => $price,
        ]);

        $room = Room::create([
            'property_id' => $property->id,
            'room_number' => 'Room-' . rand(100, 999),
            'capacity' => $capacity,
            'slots_available' => $capacity,
            'price' => $price,
            'status' => 'available',
        ]);

        return [$property, $room];
    }

    // ==========================================
    // 1. MONITORING ACCESS & ISOLATION
    // ==========================================

    public function test_landlord_can_view_own_property_and_room_monitoring(): void
    {
        $landlordA = $this->createLandlord('Landlord A');
        $landlordB = $this->createLandlord('Landlord B');

        [$propA, $roomA] = $this->createPropertyWithRoom($landlordA);
        [$propB, $roomB] = $this->createPropertyWithRoom($landlordB);

        $response = $this->actingAs($landlordA)->get(route('landlord.monitoring.properties'));
        $response->assertOk();
        $response->assertSee($propA->name);
        $response->assertDontSee($propB->name);
        $response->assertSee('Property &amp; Room Status', false);
        $response->assertSee('Manage Property');
    }

    public function test_landlord_can_view_own_compliance_monitoring(): void
    {
        $landlord = $this->createLandlord('Accredited Landlord');

        LandlordDocument::create([
            'landlord_id' => $landlord->id,
            'document_type' => LandlordDocument::TYPE_BUSINESS_PERMIT,
            'file_path' => 'documents/permit.pdf',
            'verification_status' => 'approved',
            'issued_date' => now()->subMonths(2)->toDateString(),
            'expiration_date' => now()->addMonths(10)->toDateString(),
            'is_current' => true,
        ]);

        $response = $this->actingAs($landlord)->get(route('landlord.monitoring.compliance'));
        $response->assertOk();
        $response->assertSee('Compliance &amp; Accreditation Status', false);
        $response->assertSee('Business Permit');
        $response->assertSee('Approved');
    }

    public function test_student_and_guest_cannot_access_landlord_monitoring(): void
    {
        $student = $this->createStudent();

        $this->get(route('landlord.monitoring.properties'))->assertForbidden();
        $this->get(route('landlord.monitoring.compliance'))->assertForbidden();

        $this->actingAs($student)->get(route('landlord.monitoring.properties'))->assertForbidden();
        $this->actingAs($student)->get(route('landlord.monitoring.compliance'))->assertForbidden();
    }

    // ==========================================
    // 2. NAVIGATION & PRESERVED ROUTES
    // ==========================================

    public function test_landlord_navigation_sidebar_renders_all_sections(): void
    {
        $landlord = $this->createLandlord();
        $this->createPropertyWithRoom($landlord);

        $response = $this->actingAs($landlord)->get(route('landlord.dashboard'));
        $response->assertOk();

        // Check 6 Sections
        $response->assertSee('Overview');
        $response->assertSee('Monitoring');
        $response->assertSee('Management');
        $response->assertSee('Communication');
        $response->assertSee('Insights');
        $response->assertSee('Account');

        // Check Specific Labels
        $response->assertSee('Property &amp; Room Status', false);
        $response->assertSee('Tenants &amp; Stays', false);
        $response->assertSee('Compliance Status', false);
        $response->assertSee('Room Directory', false);
        $response->assertSee('Boarding Requests', false);
        $response->assertSee('Tenant Onboarding', false);
        $response->assertSee('Rent &amp; Payments', false);
        $response->assertSee('Leave Requests', false);
        $response->assertSee('Room Maintenance', false);
        $response->assertSee('Document Renewals', false);
        $response->assertSee('Business Analytics', false);
        $response->assertSee('Feedback &amp; Ratings', false);
    }

    public function test_preserved_management_routes_remain_functional(): void
    {
        $landlord = $this->createLandlord();
        [$prop, $room] = $this->createPropertyWithRoom($landlord);

        $this->actingAs($landlord)->get(route('landlord.properties.index'))->assertOk();
        $this->actingAs($landlord)->get(route('landlord.rooms.index'))->assertOk();
        $this->actingAs($landlord)->get(route('landlord.bookings.index'))->assertOk();
        $this->actingAs($landlord)->get(route('landlord.onboarding.index'))->assertOk();
        $this->actingAs($landlord)->get(route('landlord.payments.index'))->assertOk();
        $this->actingAs($landlord)->get(route('landlord.leave_requests.index'))->assertOk();
        $this->actingAs($landlord)->get(route('landlord.maintenance.index'))->assertOk();
        $this->actingAs($landlord)->get(route('landlord.documents.index'))->assertOk();
        $this->actingAs($landlord)->get(route('landlord.analytics.index'))->assertOk();
        $this->actingAs($landlord)->get(route('landlord.feedback.index'))->assertOk();
        $this->actingAs($landlord)->get(route('landlord.tenants.index'))->assertOk();
    }
}
