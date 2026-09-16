<?php

namespace Tests\Feature;

use App\Models\LandlordDocument;
use App\Models\LandlordProfile;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminDocumentVerificationFixesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    /**
     * TEST 1 — Preview authorization
     */
    public function test_admin_preview_authorization_returns_200_with_inline_disposition(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $landlord = User::factory()->create([
            'role' => 'landlord',
            'email_verified_at' => now(),
        ]);

        $filePath = "landlords/{$landlord->id}/documents/business_permit/permit_test.jpg";
        Storage::disk('public')->put($filePath, 'dummy-image-binary-data');

        $doc = LandlordDocument::create([
            'landlord_id' => $landlord->id,
            'document_type' => LandlordDocument::TYPE_BUSINESS_PERMIT,
            'file_path' => $filePath,
            'verification_status' => LandlordDocument::STATUS_PENDING,
            'is_current' => true,
        ]);

        $previewUrl = file_download_url($doc->file_path);

        $response = $this->actingAs($admin)->get($previewUrl);

        $response->assertOk();
        $disposition = (string) $response->headers->get('content-disposition');
        $this->assertStringContainsString('inline', $disposition);
        $this->assertStringContainsString('permit_test.jpg', $disposition);
    }

    /**
     * TEST 2 — Download
     */
    public function test_admin_download_returns_200_with_attachment_disposition_and_safe_filename(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $landlord = User::factory()->create([
            'role' => 'landlord',
            'email_verified_at' => now(),
        ]);

        $filePath = "landlords/{$landlord->id}/documents/business_permit/permit_download.jpg";
        Storage::disk('public')->put($filePath, 'dummy-image-binary-data');

        $doc = LandlordDocument::create([
            'landlord_id' => $landlord->id,
            'document_type' => LandlordDocument::TYPE_BUSINESS_PERMIT,
            'file_path' => $filePath,
            'verification_status' => LandlordDocument::STATUS_PENDING,
            'is_current' => true,
        ]);

        $downloadUrl = file_download_url($doc->file_path, true);

        $response = $this->actingAs($admin)->get($downloadUrl);

        $response->assertOk();
        $disposition = (string) $response->headers->get('content-disposition');
        $this->assertStringContainsString('attachment', $disposition);
        $this->assertStringContainsString('permit_download.jpg', $disposition);
        $this->assertStringContainsString('no-cache', (string) $response->headers->get('cache-control'));
    }

    /**
     * TEST 3 — Unauthorized file access
     */
    public function test_unauthorized_user_is_forbidden_from_viewing_or_downloading_private_file(): void
    {
        $otherLandlord = User::factory()->create([
            'role' => 'landlord',
            'email_verified_at' => now(),
        ]);

        $student = User::factory()->create([
            'role' => 'student',
            'email_verified_at' => now(),
        ]);

        $landlord = User::factory()->create([
            'role' => 'landlord',
            'email_verified_at' => now(),
        ]);

        $filePath = "landlords/{$landlord->id}/documents/business_permit/secret_doc.pdf";
        Storage::disk('public')->put($filePath, '%PDF-dummy');

        LandlordDocument::create([
            'landlord_id' => $landlord->id,
            'document_type' => LandlordDocument::TYPE_BUSINESS_PERMIT,
            'file_path' => $filePath,
            'verification_status' => LandlordDocument::STATUS_PENDING,
            'is_current' => true,
        ]);

        $previewUrl = file_download_url($filePath);
        $downloadUrl = file_download_url($filePath, true);

        // Another landlord accessing this landlord's document -> 403
        $this->actingAs($otherLandlord)->get($previewUrl)->assertForbidden();
        $this->actingAs($otherLandlord)->get($downloadUrl)->assertForbidden();

        // Student accessing landlord private document -> 403
        $this->actingAs($student)->get($previewUrl)->assertForbidden();
        $this->actingAs($student)->get($downloadUrl)->assertForbidden();

        // Unauthenticated guest -> 403 Forbidden
        $this->get($previewUrl)->assertForbidden();
    }

    /**
     * TEST 4 — Verification queue returns/displays BOTH records
     */
    public function test_verification_queue_displays_both_business_permit_and_safety_certificate_when_both_exist(): void
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

        $permitPath = "landlords/{$landlord->id}/business-permits/bp_test.jpg";
        $safetyPath = "landlords/{$landlord->id}/safety-certificates/sc_test.pdf";
        Storage::disk('public')->put($permitPath, 'bp-data');
        Storage::disk('public')->put($safetyPath, 'sc-data');

        // Landlord profile with both files present, safety certificate status initially not_submitted/pending
        LandlordProfile::create([
            'user_id' => $landlord->id,
            'business_permit_path' => $permitPath,
            'business_permit_status' => 'pending',
            'safety_certificate_path' => $safetyPath,
            'safety_certificate_status' => 'not_submitted',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.documents.verification'));

        $response->assertOk();
        $documents = $response->viewData('documents');

        $this->assertCount(2, $documents);
        $types = $documents->pluck('document_type')->all();
        $this->assertContains(LandlordDocument::TYPE_BUSINESS_PERMIT, $types);
        $this->assertContains(LandlordDocument::TYPE_SAFETY_CERTIFICATE, $types);

        $response->assertSee('Business Permit');
        $response->assertSee('Safety Certificate');
        $response->assertSee('Maria Santos');
    }

    /**
     * TEST 5 — Dashboard Review Permits link points to admin.documents.verification
     */
    public function test_admin_dashboard_review_permits_link_points_to_admin_documents_verification(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertOk();
        $verificationRoute = route('admin.documents.verification');
        $response->assertSee('href="' . $verificationRoute . '"', false);
        $response->assertDontSee('href="' . route('admin.permits.index') . '"', false);
    }

    /**
     * TEST 6 — Existing Document Monitoring
     */
    public function test_existing_document_monitoring_remains_functional(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $landlord = User::factory()->create([
            'role' => 'landlord',
            'full_name' => 'Juan Dela Cruz',
            'email_verified_at' => now(),
        ]);

        $filePath = "landlords/{$landlord->id}/documents/business_permit/approved_permit.pdf";
        Storage::disk('public')->put($filePath, 'pdf-data');

        LandlordDocument::create([
            'landlord_id' => $landlord->id,
            'document_type' => LandlordDocument::TYPE_BUSINESS_PERMIT,
            'file_path' => $filePath,
            'verification_status' => LandlordDocument::STATUS_APPROVED,
            'expiration_date' => Carbon::now()->addMonths(6),
            'is_current' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.documents.monitoring'));

        $response->assertOk();
        $response->assertSee('Approved');
        $response->assertSee('Juan Dela Cruz');
    }
}
