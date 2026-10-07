<?php

namespace Tests\Feature;

use App\Models\LandlordDocument;
use App\Models\LandlordProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FileSecurityAuthorizationTest extends TestCase
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
            'boarding_house_name' => $name . ' Dorm',
            'onboarding_complete' => true,
        ]);
        $user->forceFill(['email_verified_at' => now()])->save();

        LandlordProfile::create([
            'user_id' => $user->id,
            'contact_number' => '09180000001',
            'boarding_house_name' => $name . ' Dorm',
            'about' => 'Safe housing.',
            'preferred_payment_methods' => ['gcash'],
        ]);

        return $user->refresh();
    }

    protected function createAdmin(): User
    {
        $user = User::create([
            'name' => 'System Admin',
            'full_name' => 'System Admin Full',
            'email' => 'admin_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'contact_number' => '09190000001',
            'boarding_house_name' => 'Admin HQ',
            'onboarding_complete' => true,
        ]);
        $user->forceFill(['email_verified_at' => now()])->save();
        return $user->refresh();
    }

    public function test_unauthenticated_guest_cannot_access_files_route(): void
    {
        $response = $this->get('/files/students/1/school_id.jpg');
        $response->assertStatus(403);
    }

    public function test_student_can_view_own_student_files(): void
    {
        $student = $this->createStudent('Student A');
        $filePath = "students/{$student->id}/verification/school_id.jpg";

        Storage::disk('r2')->put($filePath, 'fake image content');

        $response = $this->actingAs($student)->get("/files/{$filePath}");
        $response->assertStatus(200);
    }

    public function test_student_cannot_view_another_students_files(): void
    {
        $studentA = $this->createStudent('Student A');
        $studentB = $this->createStudent('Student B');
        $filePathA = "students/{$studentA->id}/verification/school_id.jpg";

        Storage::disk('r2')->put($filePathA, 'fake image content');

        $response = $this->actingAs($studentB)->get("/files/{$filePathA}");
        $response->assertStatus(403);
    }

    public function test_landlord_can_view_own_compliance_document(): void
    {
        $landlord = $this->createLandlord('Landlord A');
        $filePath = "landlords/{$landlord->id}/documents/business_permit/permit.pdf";

        Storage::disk('r2')->put($filePath, '%PDF-fake-pdf');

        LandlordDocument::create([
            'landlord_id' => $landlord->id,
            'document_type' => 'business_permit',
            'file_path' => $filePath,
            'file_name' => 'permit.pdf',
            'file_size' => 1024,
            'mime_type' => 'application/pdf',
            'status' => 'pending',
            'submitted_at' => now(),
        ]);

        $response = $this->actingAs($landlord)->get("/files/{$filePath}");
        $response->assertStatus(200);
    }

    public function test_landlord_cannot_view_another_landlords_compliance_document(): void
    {
        $landlordA = $this->createLandlord('Landlord A');
        $landlordB = $this->createLandlord('Landlord B');
        $filePathA = "landlords/{$landlordA->id}/documents/business_permit/permit.pdf";

        Storage::disk('r2')->put($filePathA, '%PDF-fake-pdf');

        LandlordDocument::create([
            'landlord_id' => $landlordA->id,
            'document_type' => 'business_permit',
            'file_path' => $filePathA,
            'file_name' => 'permit.pdf',
            'file_size' => 1024,
            'mime_type' => 'application/pdf',
            'status' => 'pending',
            'submitted_at' => now(),
        ]);

        $response = $this->actingAs($landlordB)->get("/files/{$filePathA}");
        $response->assertStatus(403);
    }

    public function test_admin_can_access_any_user_document(): void
    {
        $admin = $this->createAdmin();
        $student = $this->createStudent('Student A');
        $filePath = "students/{$student->id}/verification/school_id.jpg";

        Storage::disk('r2')->put($filePath, 'fake image content');

        $response = $this->actingAs($admin)->get("/files/{$filePath}");
        $response->assertStatus(200);
    }
}
