<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\LandlordDocument;
use App\Models\Property;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminPhase5Phase6Test extends TestCase
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

    public function test_landlord_details_shows_document_actions_and_history(): void
    {
        $admin = $this->user('admin', 'Admin User');
        $landlord = $this->user('landlord', 'History Landlord');

        $currentPath = "landlords/{$landlord->id}/documents/business-permits/bp-2026.pdf";
        $historyPath = "landlords/{$landlord->id}/documents/business-permits/bp-2025.pdf";
        Storage::disk('r2')->put($currentPath, 'current permit');
        Storage::disk('r2')->put($historyPath, 'old permit');

        LandlordDocument::create([
            'landlord_id' => $landlord->id,
            'document_type' => LandlordDocument::TYPE_BUSINESS_PERMIT,
            'document_number' => 'BP-2026-001',
            'file_path' => $currentPath,
            'date_issued' => '2026-01-01',
            'expiration_date' => '2026-12-31',
            'verification_status' => LandlordDocument::STATUS_APPROVED,
            'submitted_at' => now(),
            'approved_by' => $admin->id,
            'approved_at' => now(),
            'is_current' => true,
        ]);

        LandlordDocument::create([
            'landlord_id' => $landlord->id,
            'document_type' => LandlordDocument::TYPE_BUSINESS_PERMIT,
            'document_number' => 'BP-2025-001',
            'file_path' => $historyPath,
            'date_issued' => '2025-01-01',
            'expiration_date' => '2025-12-31',
            'verification_status' => LandlordDocument::STATUS_APPROVED,
            'submitted_at' => now()->subYear(),
            'approved_by' => $admin->id,
            'approved_at' => now()->subYear(),
            'is_current' => false,
            'superseded_at' => now()->subMonths(6),
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.users.landlords.show', $landlord));

        $response->assertOk()
            ->assertSee('Documents &amp; Requirements', false)
            ->assertSee('BP-2026-001')
            ->assertSee('View')
            ->assertSee('Download')
            ->assertSee('View document history')
            ->assertSee('BP-2025-001');

        $this->actingAs($admin)
            ->get(route('admin.documents.verification', [
                'landlord_id' => $landlord->id,
                'verification_status' => 'all',
            ]))
            ->assertOk()
            ->assertSee('BP-2026-001')
            ->assertSee('Download')
            ->assertSee('Reject');

        $this->actingAs($admin)
            ->get(route('admin.documents.verification', [
                'landlord_id' => $landlord->id,
                'verification_status' => 'all',
                'history' => 1,
            ]))
            ->assertOk()
            ->assertSee('BP-2025-001')
            ->assertSee('Historical version');
    }

    public function test_boarding_monitoring_lists_historical_students_and_applies_filters(): void
    {
        $admin = $this->user('admin', 'Admin User');
        $landlord = $this->user('landlord', 'Monitoring Landlord');
        $property = Property::create([
            'landlord_id' => $landlord->id,
            'name' => 'ABC Dormitory',
            'address' => '1 Monitoring Street',
        ]);
        $room = Room::create([
            'property_id' => $property->id,
            'room_number' => 'A-101',
            'capacity' => 4,
            'price' => 5000,
            'status' => 'available',
        ]);

        $active = $this->student('Active Student', 'CCS', 'BSIT');
        $checkedOut = $this->student('Checked Student', 'CCS', 'BSIT');
        $pending = $this->student('Pending Student', 'COE', 'BSCE');
        $cancelled = $this->student('Cancelled Student', 'CCS', 'BSIT');

        $today = Carbon::today();
        Booking::create([
            'room_id' => $room->id,
            'student_id' => $active->id,
            'status' => 'approved',
            'check_in' => $today->copy()->subDays(5),
            'check_out' => $today->copy()->addDays(5),
        ]);
        Booking::create([
            'room_id' => $room->id,
            'student_id' => $checkedOut->id,
            'status' => 'approved',
            'check_in' => $today->copy()->subDays(30),
            'check_out' => $today->copy()->subDays(2),
        ]);
        Booking::create([
            'room_id' => $room->id,
            'student_id' => $pending->id,
            'status' => 'pending',
            'check_in' => $today->copy()->addDays(10),
            'check_out' => $today->copy()->addDays(20),
        ]);
        Booking::create([
            'room_id' => $room->id,
            'student_id' => $cancelled->id,
            'status' => 'cancelled',
            'check_in' => $today->copy()->subDays(3),
            'check_out' => $today->copy()->addDays(3),
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.boarding_monitoring.students', [
                'boarding_house' => $property->id,
                'college' => 'CCS',
                'program' => 'BSIT',
                'month' => $today->month,
                'year' => $today->year,
            ]));

        $response->assertOk()
            ->assertSee('Active Student')
            ->assertSee('Checked Student')
            ->assertSee('Cancelled Student')
            ->assertDontSee('Pending Student')
            ->assertSee('Active')
            ->assertSee('Checked Out')
            ->assertSee('Cancelled');

        $this->actingAs($admin)
            ->get(route('admin.boarding_monitoring.students', ['status' => 'checked_out']))
            ->assertOk()
            ->assertSee('Checked Student')
            ->assertDontSee('Active Student');
    }

    protected function user(string $role, string $name): User
    {
        $user = User::create([
            'full_name' => $name,
            'name' => $name,
            'email' => strtolower(str_replace(' ', '.', $name)) . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => $role,
            'contact_number' => '09171234567',
            'boarding_house_name' => 'Test House',
            'onboarding_complete' => true,
        ]);

        $user->forceFill(['email_verified_at' => now()])->save();

        return $user->refresh();
    }

    protected function student(string $name, string $college, string $program): User
    {
        $student = $this->user('student', $name);
        $student->forceFill([
            'college' => $college,
            'program' => $program,
        ])->save();

        return $student->refresh();
    }
}
