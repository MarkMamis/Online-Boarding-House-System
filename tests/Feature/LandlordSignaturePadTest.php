<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\LandlordProfile;
use App\Models\Property;
use App\Models\Room;
use App\Models\TenantOnboarding;
use App\Models\User;
use App\Services\FileStorageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LandlordSignaturePadTest extends TestCase
{
    use RefreshDatabase;

    // A valid 10x10 PNG data URL for test fixtures
    protected string $validPngDataUrl = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAoAAAAKCAYAAACNMs+9AAAAFUlEQVR42mNk+M9QzwAEjDAGYzAEAB8uAQf+wM1RAAAAAElFTkSuQmCC';

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
                    if (! Schema::hasColumn('landlord_profiles', $col)) {
                        $table->string($col)->nullable();
                    }
                }
            });
        }
    }

    protected function createLandlord(string $name = 'Landlord Test'): User
    {
        $id = uniqid();
        $user = User::create([
            'name' => $name,
            'full_name' => $name.' Legal',
            'email' => 'landlord_'.$id.'@example.com',
            'password' => bcrypt('password'),
            'role' => 'landlord',
            'contact_number' => '09181234567',
            'boarding_house_name' => 'Sunrise Boarding House',
            'address' => 'Masipit, Calapan City, Oriental Mindoro',
            'onboarding_complete' => true,
        ]);
        $user->forceFill(['email_verified_at' => now()])->save();

        $profileData = [
            'user_id' => $user->id,
            'contact_number' => '09181234567',
            'boarding_house_name' => 'Sunrise Boarding House',
            'about' => 'Clean and accessible rooms for university students.',
            'preferred_payment_methods' => ['gcash'],
            'payment_gcash_name' => 'Maria Santos',
            'business_permit_status' => 'approved',
        ];
        if (\Illuminate\Support\Facades\Schema::hasColumn('landlord_profiles', 'payment_gcash_number')) {
            $profileData['payment_gcash_number'] = '09181234567';
        }
        LandlordProfile::create($profileData);

        return $user->refresh();
    }

    protected function createStudent(string $name = 'Student Test'): User
    {
        $id = uniqid();
        $user = User::create([
            'name' => $name,
            'full_name' => $name.' Full',
            'email' => 'student_'.$id.'@example.com',
            'password' => bcrypt('password'),
            'role' => 'student',
            'contact_number' => '09171234567',
            'boarding_house_name' => 'N/A',
            'onboarding_complete' => true,
        ]);
        $user->forceFill(['email_verified_at' => now()])->save();

        return $user->refresh();
    }

    public function test_landlord_can_complete_setup_without_a_signature(): void
    {
        $landlord = $this->createLandlord('Setup Landlord');

        $payload = [
            'full_name' => 'Maria Santos',
            'email' => $landlord->email,
            'contact_number' => '09181234567',
            'address' => 'Masipit, Calapan City, Oriental Mindoro',
            'boarding_house_name' => 'Sunrise Residence',
            'about' => 'Quality housing near university campus.',
            'preferred_payment_methods' => ['gcash'],
            'payment_gcash_name' => 'Maria Santos',
            'payment_gcash_number' => '09181234567',
            'contract_signature_data' => '', // Left blank (optional)
        ];

        $response = $this->actingAs($landlord)
            ->put(route('landlord.setup.update'), $payload);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $profile = $landlord->fresh()->landlordProfile;
        $this->assertNull($profile->contract_signature_path);
    }

    public function test_landlord_can_save_drawn_signature_via_dedicated_endpoint(): void
    {
        $landlord = $this->createLandlord('Pad Landlord');

        $response = $this->actingAs($landlord)
            ->postJson(route('landlord.signature.save'), [
                'signature_data' => $this->validPngDataUrl,
            ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'message' => 'Signature saved.',
        ]);

        $profile = $landlord->fresh()->landlordProfile;
        $this->assertNotNull($profile->contract_signature_path);
        $this->assertTrue(app(FileStorageService::class)->exists($profile->contract_signature_path));
    }

    public function test_landlord_can_submit_drawn_signature_during_setup_update(): void
    {
        $landlord = $this->createLandlord('Setup Signer');

        $payload = [
            'full_name' => 'Maria Santos',
            'email' => $landlord->email,
            'contact_number' => '09181234567',
            'address' => 'Masipit, Calapan City, Oriental Mindoro',
            'boarding_house_name' => 'Sunrise Residence',
            'about' => 'Quality housing near university campus.',
            'preferred_payment_methods' => ['gcash'],
            'payment_gcash_name' => 'Maria Santos',
            'payment_gcash_number' => '09181234567',
            'contract_signature_data' => $this->validPngDataUrl,
        ];

        $response = $this->actingAs($landlord)
            ->put(route('landlord.setup.update'), $payload);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $profile = $landlord->fresh()->landlordProfile;
        $this->assertNotNull($profile->contract_signature_path);
        $this->assertTrue(app(FileStorageService::class)->exists($profile->contract_signature_path));
    }

    public function test_blank_or_empty_signature_cannot_be_saved(): void
    {
        $landlord = $this->createLandlord('Blank Tester');

        $response = $this->actingAs($landlord)
            ->postJson(route('landlord.signature.save'), [
                'signature_data' => '   ',
            ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
        ]);
    }

    public function test_invalid_image_payload_is_rejected(): void
    {
        $landlord = $this->createLandlord('Invalid Payload Tester');

        $response = $this->actingAs($landlord)
            ->postJson(route('landlord.signature.save'), [
                'signature_data' => 'data:image/png;base64,bm90LWFuLWltYWdlLWRhdGE=',
            ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
        ]);
    }

    public function test_oversized_signature_payload_is_rejected(): void
    {
        $landlord = $this->createLandlord('Oversized Tester');

        // Create an oversized base64 payload (> 5MB)
        $oversizedData = 'data:image/png;base64,'.base64_encode(str_repeat('A', 6 * 1024 * 1024));

        $response = $this->actingAs($landlord)
            ->postJson(route('landlord.signature.save'), [
                'signature_data' => $oversizedData,
            ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
        ]);
    }

    public function test_unauthorized_user_cannot_save_landlord_signature(): void
    {
        $student = $this->createStudent('Student Attacker');

        $response = $this->actingAs($student)
            ->postJson(route('landlord.signature.save'), [
                'signature_data' => $this->validPngDataUrl,
            ]);

        $response->assertStatus(403);
    }

    public function test_existing_uploaded_signature_remains_accessible_and_preserved(): void
    {
        $landlord = $this->createLandlord('Existing Preserver');
        $profile = $landlord->landlordProfile;

        // Put a fake existing signature
        $existingPath = 'landlords/'.$landlord->id.'/signatures/existing_sig.png';
        app(FileStorageService::class)->put('landlords/'.$landlord->id.'/signatures', 'dummy_binary', 'png');
        $profile->update(['contract_signature_path' => $existingPath]);

        // Submit setup update without new signature
        $payload = [
            'full_name' => 'Maria Santos',
            'email' => $landlord->email,
            'contact_number' => '09181234567',
            'address' => 'Masipit, Calapan City, Oriental Mindoro',
            'boarding_house_name' => 'Sunrise Residence',
            'about' => 'Quality housing near university campus.',
            'preferred_payment_methods' => ['gcash'],
            'payment_gcash_name' => 'Maria Santos',
            'payment_gcash_number' => '09181234567',
            'contract_signature_data' => '',
        ];

        $response = $this->actingAs($landlord)
            ->put(route('landlord.setup.update'), $payload);

        $response->assertSessionHasNoErrors();
        $this->assertEquals($existingPath, $landlord->fresh()->landlordProfile->contract_signature_path);
    }

    public function test_replacing_saved_signature_updates_profile(): void
    {
        $landlord = $this->createLandlord('Replacer');

        // Initial save
        $firstSave = $this->actingAs($landlord)
            ->postJson(route('landlord.signature.save'), [
                'signature_data' => $this->validPngDataUrl,
            ]);
        $firstSave->assertOk();
        $firstPath = $landlord->fresh()->landlordProfile->contract_signature_path;

        // Second save (replace)
        $secondSave = $this->actingAs($landlord)
            ->postJson(route('landlord.signature.save'), [
                'signature_data' => $this->validPngDataUrl,
            ]);
        $secondSave->assertOk();
        $secondPath = $landlord->fresh()->landlordProfile->contract_signature_path;

        $this->assertNotNull($secondPath);
        $this->assertNotEquals($firstPath, $secondPath);
        $this->assertTrue(app(FileStorageService::class)->exists($secondPath));
    }

    public function test_saving_signature_does_not_mark_contract_as_accepted(): void
    {
        $landlord = $this->createLandlord('Contract Safety Landlord');
        $student = $this->createStudent('Contract Safety Student');

        $property = Property::create([
            'landlord_id' => $landlord->id,
            'name' => 'Safety Dorm',
            'address' => 'Near MinSU',
            'status' => 'approved',
            'approval_status' => 'approved',
        ]);

        $room = Room::create([
            'property_id' => $property->id,
            'room_number' => '101',
            'capacity' => 2,
            'price' => 2500,
            'status' => 'available',
        ]);

        $booking = Booking::create([
            'student_id' => $student->id,
            'room_id' => $room->id,
            'check_in' => now()->toDateString(),
            'check_out' => now()->addMonths(5)->toDateString(),
            'status' => 'approved',
        ]);

        $onboarding = TenantOnboarding::create([
            'booking_id' => $booking->id,
            'status' => 'documents_uploaded',
            'step' => 2,
            'landlord_contract_signed' => false,
            'landlord_contract_signature_path' => null,
        ]);

        // Landlord saves a reusable profile signature
        $response = $this->actingAs($landlord)
            ->postJson(route('landlord.signature.save'), [
                'signature_data' => $this->validPngDataUrl,
            ]);
        $response->assertOk();

        // The onboarding contract must NOT be automatically signed!
        $onboarding->refresh();
        $this->assertFalse((bool) $onboarding->landlord_contract_signed);
        $this->assertNull($onboarding->landlord_contract_signature_path);
    }

    public function test_historical_signed_contracts_remain_unchanged_when_signature_is_updated(): void
    {
        $landlord = $this->createLandlord('Historical Safety Landlord');
        $student = $this->createStudent('Historical Safety Student');

        $property = Property::create([
            'landlord_id' => $landlord->id,
            'name' => 'Historical Dorm',
            'address' => 'Near MinSU',
            'status' => 'approved',
            'approval_status' => 'approved',
        ]);

        $room = Room::create([
            'property_id' => $property->id,
            'room_number' => '102',
            'capacity' => 2,
            'price' => 2500,
            'status' => 'available',
        ]);

        $booking = Booking::create([
            'student_id' => $student->id,
            'room_id' => $room->id,
            'check_in' => now()->toDateString(),
            'check_out' => now()->addMonths(5)->toDateString(),
            'status' => 'approved',
        ]);

        $historicalContractSignaturePath = 'tenant-onboardings/historical-contract-sig.png';
        app(FileStorageService::class)->put('tenant-onboardings', 'historical_sig', 'png');

        $onboarding = TenantOnboarding::create([
            'booking_id' => $booking->id,
            'status' => 'contract_signed',
            'step' => 2,
            'landlord_contract_signed' => true,
            'landlord_contract_signed_at' => now()->subDays(10),
            'landlord_contract_signature_path' => $historicalContractSignaturePath,
        ]);

        // Landlord updates profile signature
        $response = $this->actingAs($landlord)
            ->postJson(route('landlord.signature.save'), [
                'signature_data' => $this->validPngDataUrl,
            ]);
        $response->assertOk();

        // Historical contract must remain unchanged
        $onboarding->refresh();
        $this->assertEquals($historicalContractSignaturePath, $onboarding->landlord_contract_signature_path);
        $this->assertTrue((bool) $onboarding->landlord_contract_signed);
    }
}
