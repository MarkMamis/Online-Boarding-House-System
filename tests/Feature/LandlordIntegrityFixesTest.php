<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\LandlordProfile;
use App\Models\LeaveRequest;
use App\Models\Property;
use App\Models\Room;
use App\Models\TenantPayment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandlordIntegrityFixesTest extends TestCase
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
            'birth_date' => '2004-01-01',
            'college' => 'College of Computer Studies',
            'program' => 'BS Information Technology',
            'year_level' => '2nd Year',
            'gender' => 'Male',
            'address' => 'Calapan City',
            'student_id' => '2022-' . rand(1000, 9999),
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
            'contact_number' => '09180000001',
        ]);

        return $user->refresh();
    }

    protected function createPropertyWithRoom(User $landlord, float $price = 2500.0, int $capacity = 1): array
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
    // 1. FINANCIAL ANALYTICS CORRECTNESS
    // ==========================================

    public function test_financial_analytics_calculates_actual_collected_and_monthly_expected_revenue(): void
    {
        $landlordA = $this->createLandlord('Landlord A');
        $landlordB = $this->createLandlord('Landlord B');

        [$propA, $roomA] = $this->createPropertyWithRoom($landlordA, 3000.00, 2);
        [$propB, $roomB] = $this->createPropertyWithRoom($landlordB, 4000.00, 1);

        $student1 = $this->createStudent('Student One');
        $student2 = $this->createStudent('Student Two');
        $studentB = $this->createStudent('Student B');

        // Active 6-month booking for Student 1 (180 days)
        $booking1 = Booking::create([
            'room_id' => $roomA->id,
            'student_id' => $student1->id,
            'check_in' => now()->subMonth()->toDateString(),
            'check_out' => now()->addMonths(5)->toDateString(),
            'status' => 'approved',
        ]);

        // Verified Payment of 3,000 for Booking 1
        TenantPayment::create([
            'booking_id' => $booking1->id,
            'student_id' => $student1->id,
            'billing_for_date' => now()->startOfMonth()->toDateString(),
            'due_date' => now()->toDateString(),
            'amount_due' => 3000.00,
            'payment_method' => 'gcash',
            'payment_reference' => 'GCASH-123456',
            'status' => 'approved',
            'submitted_at' => now()->subDays(5),
            'reviewed_at' => now()->subDays(4),
            'reviewed_by' => $landlordA->id,
        ]);

        // Unverified Submitted Payment of 3,000 for Booking 1 (should NOT be in collected revenue)
        TenantPayment::create([
            'booking_id' => $booking1->id,
            'student_id' => $student1->id,
            'billing_for_date' => now()->addMonth()->startOfMonth()->toDateString(),
            'due_date' => now()->addMonth()->toDateString(),
            'amount_due' => 3000.00,
            'payment_method' => 'gcash',
            'payment_reference' => 'GCASH-999999',
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        // Payment for Landlord B (should NOT leak to Landlord A)
        $bookingB = Booking::create([
            'room_id' => $roomB->id,
            'student_id' => $studentB->id,
            'check_in' => now()->toDateString(),
            'check_out' => now()->addMonths(6)->toDateString(),
            'status' => 'approved',
        ]);
        TenantPayment::create([
            'booking_id' => $bookingB->id,
            'student_id' => $studentB->id,
            'billing_for_date' => now()->startOfMonth()->toDateString(),
            'due_date' => now()->toDateString(),
            'amount_due' => 4000.00,
            'payment_method' => 'cash',
            'status' => 'approved',
            'submitted_at' => now()->subDays(2),
            'reviewed_at' => now()->subDays(1),
            'reviewed_by' => $landlordB->id,
        ]);

        $response = $this->actingAs($landlordA)->get(route('landlord.analytics.index'));
        $response->assertOk();

        // Monthly collected revenue (30d) must equal exactly 3,000.00 (not 3000 * 180 days = 540,000)
        $response->assertViewHas('monthlyRevenue', 3000.00);

        // Expected active monthly rent must equal 3,000.00 (the active monthly price of roomA)
        $response->assertViewHas('totalExpected', 3000.00);
    }

    // ==========================================
    // 2. FUTURE-DATED LEAVE REQUESTS
    // ==========================================

    public function test_future_dated_leave_approval_preserves_active_stay_and_room_occupancy(): void
    {
        $landlord = $this->createLandlord();
        [$property, $room] = $this->createPropertyWithRoom($landlord, 2000.0, 1);
        $student = $this->createStudent();

        $booking = Booking::create([
            'room_id' => $room->id,
            'student_id' => $student->id,
            'check_in' => now()->subMonth()->toDateString(),
            'check_out' => now()->addMonths(5)->toDateString(),
            'status' => 'approved',
        ]);
        $room->syncAvailabilitySnapshot();
        $this->assertEquals(0, $room->fresh()->slots_available);

        // Student files leave request scheduled for 20 days in the future
        $futureLeaveDate = now()->addDays(20)->toDateString();
        $leaveRequest = LeaveRequest::create([
            'booking_id' => $booking->id,
            'student_id' => $student->id,
            'landlord_id' => $landlord->id,
            'leave_date' => $futureLeaveDate,
            'reason' => 'Relocating next month',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($landlord)->post(route('landlord.leave_requests.approve', $leaveRequest), [
            'landlord_response' => 'Approved, take care.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Leave request is approved
        $this->assertEquals('approved', $leaveRequest->fresh()->status);

        // Booking status remains 'approved' and check_out is adjusted to future leave date
        $refreshedBooking = $booking->fresh();
        $this->assertEquals('approved', $refreshedBooking->status);
        $this->assertEquals($futureLeaveDate, $refreshedBooking->check_out->toDateString());

        // Room availability is preserved as occupied until actual leave date
        $this->assertEquals(0, $room->fresh()->slots_available);
    }

    public function test_immediate_leave_approval_concludes_stay_and_frees_room_slot(): void
    {
        $landlord = $this->createLandlord();
        [$property, $room] = $this->createPropertyWithRoom($landlord, 2000.0, 1);
        $student = $this->createStudent();

        $booking = Booking::create([
            'room_id' => $room->id,
            'student_id' => $student->id,
            'check_in' => now()->subMonth()->toDateString(),
            'check_out' => now()->addMonths(5)->toDateString(),
            'status' => 'approved',
        ]);
        $room->syncAvailabilitySnapshot();
        $this->assertEquals(0, $room->fresh()->slots_available);

        // Leave request for today
        $todayLeaveDate = now()->toDateString();
        $leaveRequest = LeaveRequest::create([
            'booking_id' => $booking->id,
            'student_id' => $student->id,
            'landlord_id' => $landlord->id,
            'leave_date' => $todayLeaveDate,
            'reason' => 'Moving out today',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($landlord)->post(route('landlord.leave_requests.approve', $leaveRequest), [
            'landlord_response' => 'Key received.',
        ]);

        $response->assertRedirect();
        $this->assertEquals('approved', $leaveRequest->fresh()->status);

        // Booking is cancelled and slot is freed
        $this->assertEquals('cancelled', $booking->fresh()->status);
        $this->assertEquals(1, $room->fresh()->slots_available);
    }

    // ==========================================
    // 3. PROPERTY & ROOM DELETION SAFEGUARDS
    // ==========================================

    public function test_property_with_active_or_pending_bookings_cannot_be_deleted(): void
    {
        $landlord = $this->createLandlord();
        [$property, $room] = $this->createPropertyWithRoom($landlord, 2000.0, 1);
        $student = $this->createStudent();

        Booking::create([
            'room_id' => $room->id,
            'student_id' => $student->id,
            'check_in' => now()->toDateString(),
            'check_out' => now()->addMonths(3)->toDateString(),
            'status' => 'approved',
        ]);

        $response = $this->actingAs($landlord)->delete(route('landlord.properties.destroy', $property));
        $response->assertRedirect();
        $response->assertSessionHas('error');

        // Property still exists
        $this->assertDatabaseHas('properties', ['id' => $property->id]);
    }

    public function test_room_with_active_or_pending_bookings_cannot_be_deleted(): void
    {
        $landlord = $this->createLandlord();
        [$property, $room] = $this->createPropertyWithRoom($landlord, 2000.0, 1);
        $student = $this->createStudent();

        Booking::create([
            'room_id' => $room->id,
            'student_id' => $student->id,
            'check_in' => now()->toDateString(),
            'check_out' => now()->addMonths(3)->toDateString(),
            'status' => 'pending',
        ]);

        $response = $this->actingAs($landlord)->delete(route('landlord.properties.rooms.destroy', [$property->id, $room->id]));
        $response->assertRedirect();
        $response->assertSessionHas('error');

        // Room still exists
        $this->assertDatabaseHas('rooms', ['id' => $room->id]);
    }

    public function test_empty_property_and_room_can_be_safely_deleted(): void
    {
        $landlord = $this->createLandlord();
        [$property, $room] = $this->createPropertyWithRoom($landlord, 2000.0, 1);

        $responseRoom = $this->actingAs($landlord)->delete(route('landlord.properties.rooms.destroy', [$property->id, $room->id]));
        $responseRoom->assertRedirect();
        $responseRoom->assertSessionHas('success');
        $this->assertDatabaseMissing('rooms', ['id' => $room->id]);

        $responseProp = $this->actingAs($landlord)->delete(route('landlord.properties.destroy', $property));
        $responseProp->assertRedirect(route('landlord.properties.index'));
        $responseProp->assertSessionHas('success');
        $this->assertDatabaseMissing('properties', ['id' => $property->id]);
    }

    // ==========================================
    // 4. SHARED-ROOM TENANT VISIBILITY
    // ==========================================

    public function test_shared_room_hydrates_and_displays_all_active_occupants(): void
    {
        $landlord = $this->createLandlord();
        [$property, $room] = $this->createPropertyWithRoom($landlord, 1500.0, 4);

        $studentA = $this->createStudent('Alice Cooper');
        $studentB = $this->createStudent('Bob Builder');
        $studentC = $this->createStudent('Charlie Chaplin');

        foreach ([$studentA, $studentB, $studentC] as $student) {
            Booking::create([
                'room_id' => $room->id,
                'student_id' => $student->id,
                'check_in' => now()->subDays(10)->toDateString(),
                'check_out' => now()->addMonths(3)->toDateString(),
                'status' => 'approved',
            ]);
        }

        // Check property show view
        $response = $this->actingAs($landlord)->get(route('landlord.properties.show', $property));
        $response->assertOk();
        $response->assertSee('Tenants (3)');
        $response->assertSee('Alice Cooper Full');
        $response->assertSee('Bob Builder Full');
        $response->assertSee('Charlie Chaplin Full');

        // Check room directory index view
        $responseRooms = $this->actingAs($landlord)->get(route('landlord.rooms.index'));
        $responseRooms->assertOk();
        $responseRooms->assertSee('Current Tenants (3)');
        $responseRooms->assertSee('Alice Cooper Full');
        $responseRooms->assertSee('Bob Builder Full');
        $responseRooms->assertSee('Charlie Chaplin Full');
    }
}
