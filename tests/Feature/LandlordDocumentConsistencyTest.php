<?php

namespace Tests\Feature;

use App\Models\LandlordDocument;
use App\Models\LandlordProfile;
use App\Models\User;
use App\Services\LandlordDocumentStatusService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class LandlordDocumentConsistencyTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $landlord;
    protected LandlordProfile $profile;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        $this->landlord = User::factory()->create([
            'role' => 'landlord',
            'full_name' => 'Test Landlord Consistency',
            'email' => 'landlord.consistency@example.com',
            'contact_number' => '09123456789',
            'boarding_house_name' => 'Consistent Haven',
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        $this->profile = LandlordProfile::create([
            'user_id' => $this->landlord->id,
            'boarding_house_name' => 'Consistent Haven',
            'business_permit_path' => 'documents/test_permit.pdf',
            'business_permit_status' => 'approved',
            'safety_certificate_path' => 'documents/test_safety.pdf',
            'safety_certificate_status' => 'pending',
        ]);
    }

    /**
     * TEST A: PENDING DOCUMENT
     * When Safety Certificate is Pending, all views must reflect Pending verification.
     */
    public function test_pending_document_is_consistent_across_all_admin_views(): void
    {
        // 1. Landlord Profile Overview
        $resProfile = $this->actingAs($this->admin)->get(route('admin.users.landlords.show', $this->landlord));
        $resProfile->assertOk();
        $resProfile->assertSee('Test Landlord Consistency');
        $resProfile->assertSee('Pending Review');

        // 2. Landlord Profile Documents tab
        $resDocsTab = $this->actingAs($this->admin)->get(route('admin.users.landlords.show', ['user' => $this->landlord, 'tab' => 'documents']));
        $resDocsTab->assertOk();
        $resDocsTab->assertSee('Pending');

        // 3. Landlord Accreditation page
        $resAccreditation = $this->actingAs($this->admin)->get(route('admin.approvals.landlords', ['tab' => 'permits']));
        $resAccreditation->assertOk();
        $resAccreditation->assertSee('Consistent Haven');

        // 4. Centralized Status Service check
        $service = app(LandlordDocumentStatusService::class);
        $this->assertEquals(LandlordDocumentStatusService::STATUS_APPROVED, $service->resolveDocumentStatus($this->landlord, LandlordDocumentStatusService::DOC_BUSINESS_PERMIT));
        $this->assertEquals(LandlordDocumentStatusService::STATUS_PENDING, $service->resolveDocumentStatus($this->landlord, LandlordDocumentStatusService::DOC_SAFETY_CERTIFICATE));
    }

    /**
     * TEST B: APPROVED DOCUMENT
     * When Safety Certificate is Approved, no page continues to display stale Pending.
     */
    public function test_approved_document_is_consistent_across_all_admin_views(): void
    {
        // Create active LandlordDocument record
        $doc = LandlordDocument::create([
            'landlord_id' => $this->landlord->id,
            'document_type' => LandlordDocument::TYPE_SAFETY_CERTIFICATE,
            'file_path' => 'documents/test_safety.pdf',
            'document_number' => 'SC-2026-999',
            'verification_status' => LandlordDocument::STATUS_PENDING,
            'date_issued' => Carbon::now()->subDays(10),
            'expiration_date' => Carbon::now()->addYear(),
            'is_current' => true,
        ]);

        // Admin approves document via Document Verification workflow
        $response = $this->actingAs($this->admin)->post(route('admin.documents.approve', $doc));
        $response->assertSessionHas('success');

        $doc->refresh();
        $this->assertEquals(LandlordDocument::STATUS_APPROVED, $doc->verification_status);

        // Verify across Landlord Profile Overview
        $resOverview = $this->actingAs($this->admin)->get(route('admin.users.landlords.show', $this->landlord));
        $resOverview->assertOk();

        // Verify across Landlord Accreditation view (Crucial test for the property_exists fix!)
        $resAccreditation = $this->actingAs($this->admin)->get(route('admin.approvals.landlords', ['tab' => 'permits']));
        $resAccreditation->assertOk();
        $resAccreditation->assertSee('Approved');

        // Verify centralized service returns Approved
        $service = app(LandlordDocumentStatusService::class);
        $this->assertEquals(LandlordDocumentStatusService::STATUS_APPROVED, $service->resolveDocumentStatus($this->landlord, LandlordDocumentStatusService::DOC_SAFETY_CERTIFICATE));
    }

    /**
     * TEST C: REJECTED DOCUMENT
     * When a document is rejected with reason, all views display rejection status.
     */
    public function test_rejected_document_displays_rejection_reason_and_status(): void
    {
        $doc = LandlordDocument::create([
            'landlord_id' => $this->landlord->id,
            'document_type' => LandlordDocument::TYPE_SAFETY_CERTIFICATE,
            'file_path' => 'documents/test_safety.pdf',
            'document_number' => 'SC-2026-001',
            'verification_status' => LandlordDocument::STATUS_PENDING,
            'date_issued' => Carbon::now()->subDays(5),
            'expiration_date' => Carbon::now()->addMonths(6),
            'is_current' => true,
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.documents.reject', $doc), [
            'rejection_reason' => 'Blurry certificate scan, please re-upload.',
        ]);
        $response->assertSessionHas('success');

        $doc->refresh();
        $this->assertEquals(LandlordDocument::STATUS_REJECTED, $doc->verification_status);
        $this->assertEquals('Blurry certificate scan, please re-upload.', $doc->rejection_reason);

        // Check Landlord Profile Documents tab shows rejected status
        $resDocs = $this->actingAs($this->admin)->get(route('admin.users.landlords.show', ['user' => $this->landlord, 'tab' => 'documents']));
        $resDocs->assertOk();
        $resDocs->assertSee('Rejected');
        $resDocs->assertSee('Blurry certificate scan');
    }

    /**
     * TEST D: APPROVED BUT EXPIRING SOON
     * Verification is Approved, Expiration condition is Expiring Soon across all modules.
     */
    public function test_approved_but_expiring_soon_condition(): void
    {
        $doc = LandlordDocument::create([
            'landlord_id' => $this->landlord->id,
            'document_type' => LandlordDocument::TYPE_BUSINESS_PERMIT,
            'file_path' => 'documents/test_permit.pdf',
            'document_number' => 'BP-2026-EXP',
            'verification_status' => LandlordDocument::STATUS_APPROVED,
            'date_issued' => Carbon::today()->subMonths(11),
            'expiration_date' => Carbon::today()->addDays(20), // Within 60-day window
            'is_current' => true,
        ]);

        // 1. Expiration info check
        $info = $doc->expirationInfo();
        $this->assertEquals('expiring_soon', $info['status']);
        $this->assertStringContainsString('Expiring in 20 days', $info['label']);
        $this->assertEquals(20, $info['days_remaining']);

        // 2. Documents tab view
        $resDocs = $this->actingAs($this->admin)->get(route('admin.users.landlords.show', ['user' => $this->landlord, 'tab' => 'documents']));
        $resDocs->assertOk();
        $resDocs->assertSee('Expiring in 20 days');
        $resDocs->assertSee('Approved');

        // 3. Document Monitoring view
        $resMon = $this->actingAs($this->admin)->get(route('admin.documents.monitoring', ['expiration_status' => 'expiring_soon']));
        $resMon->assertOk();
        $resMon->assertSee('BP-2026-EXP');
    }

    /**
     * TEST E: APPROVED BUT EXPIRED
     * Verification status remains Approved in history, while lifecycle condition is Expired.
     */
    public function test_approved_but_expired_condition(): void
    {
        $doc = LandlordDocument::create([
            'landlord_id' => $this->landlord->id,
            'document_type' => LandlordDocument::TYPE_BUSINESS_PERMIT,
            'file_path' => 'documents/test_permit_expired.pdf',
            'document_number' => 'BP-2025-PAST',
            'verification_status' => LandlordDocument::STATUS_APPROVED,
            'date_issued' => Carbon::today()->subYears(2),
            'expiration_date' => Carbon::today()->subDays(10), // Expired in past
            'is_current' => true,
        ]);

        $info = $doc->expirationInfo();
        $this->assertEquals('expired', $info['status']);
        $this->assertStringContainsString('Expired 10 days ago', $info['label']);

        $resDocs = $this->actingAs($this->admin)->get(route('admin.users.landlords.show', ['user' => $this->landlord, 'tab' => 'documents']));
        $resDocs->assertOk();
        $resDocs->assertSee('Expired 10 days ago');
    }

    /**
     * TEST F: PENDING RENEWAL / MULTIPLE VERSIONS
     * Previous version remains Approved in history, new version is Pending.
     */
    public function test_pending_renewal_preserves_historical_version_distinction(): void
    {
        // 1. Previous approved version
        $oldDoc = LandlordDocument::create([
            'landlord_id' => $this->landlord->id,
            'document_type' => LandlordDocument::TYPE_BUSINESS_PERMIT,
            'document_number' => 'BP-V1-2025',
            'file_path' => 'documents/v1.pdf',
            'verification_status' => LandlordDocument::STATUS_APPROVED,
            'date_issued' => Carbon::today()->subYear(),
            'expiration_date' => Carbon::today()->addDays(5),
            'is_current' => false,
            'superseded_at' => Carbon::now(),
        ]);

        // 2. New renewal submission
        $newDoc = LandlordDocument::create([
            'landlord_id' => $this->landlord->id,
            'document_type' => LandlordDocument::TYPE_BUSINESS_PERMIT,
            'document_number' => 'BP-V2-2026',
            'file_path' => 'documents/v2.pdf',
            'verification_status' => LandlordDocument::STATUS_PENDING,
            'date_issued' => Carbon::today(),
            'expiration_date' => Carbon::today()->addYear(),
            'is_current' => true,
        ]);

        // Documents tab renders both current pending version and historical version
        $resDocs = $this->actingAs($this->admin)->get(route('admin.users.landlords.show', ['user' => $this->landlord, 'tab' => 'documents']));
        $resDocs->assertOk();
        $resDocs->assertSee('BP-V2-2026');
        $resDocs->assertSee('BP-V1-2025');

        // Document Review queue shows current version
        $resQueue = $this->actingAs($this->admin)->get(route('admin.documents.verification', ['verification_status' => 'pending']));
        $resQueue->assertOk();
        $resQueue->assertSee('BP-V2-2026');
    }

    /**
     * TEST H: ACTION CENTER COUNTS
     * Action center correctly counts pending documents and expiring compliance items without query conflict.
     */
    public function test_action_center_metrics_and_expiring_counts(): void
    {
        // Create 1 pending document
        LandlordDocument::create([
            'landlord_id' => $this->landlord->id,
            'document_type' => LandlordDocument::TYPE_SAFETY_CERTIFICATE,
            'file_path' => 'documents/safety_pending.pdf',
            'verification_status' => LandlordDocument::STATUS_PENDING,
            'is_current' => true,
        ]);

        // Create 1 approved document expiring soon
        LandlordDocument::create([
            'landlord_id' => $this->landlord->id,
            'document_type' => LandlordDocument::TYPE_BUSINESS_PERMIT,
            'file_path' => 'documents/permit_exp.pdf',
            'verification_status' => LandlordDocument::STATUS_APPROVED,
            'expiration_date' => Carbon::today()->addDays(15),
            'is_current' => true,
        ]);

        $resActionCenter = $this->actingAs($this->admin)->get(route('admin.action_center.index'));
        $resActionCenter->assertOk();
        $resActionCenter->assertSee('Document Reviews');
        $resActionCenter->assertSee('Landlord Document Review Queue');
    }

    /**
     * TEST I: ROLE ISOLATION
     * Non-admin roles (guest, student, landlord) cannot access or perform admin review actions.
     */
    public function test_role_isolation_guards_admin_document_review_actions(): void
    {
        $student = User::factory()->create(['role' => 'student', 'email_verified_at' => now()]);

        $doc = LandlordDocument::create([
            'landlord_id' => $this->landlord->id,
            'document_type' => LandlordDocument::TYPE_BUSINESS_PERMIT,
            'file_path' => 'documents/permit.pdf',
            'verification_status' => LandlordDocument::STATUS_PENDING,
            'is_current' => true,
        ]);

        // Guest access denied
        $this->post(route('admin.documents.approve', $doc))->assertForbidden();

        // Student access denied (403 forbidden)
        $this->actingAs($student)->post(route('admin.documents.approve', $doc))->assertForbidden();

        // Landlord cannot approve own document
        $this->actingAs($this->landlord)->post(route('admin.documents.approve', $doc))->assertForbidden();
    }

    /**
     * TEST J: STALE / NON-CURRENT DOCUMENT ACTION
     * Attempting to approve or reject a superseded historical version returns 404.
     */
    public function test_cannot_mutate_historical_superseded_document(): void
    {
        $supersededDoc = LandlordDocument::create([
            'landlord_id' => $this->landlord->id,
            'document_type' => LandlordDocument::TYPE_BUSINESS_PERMIT,
            'file_path' => 'documents/permit_old.pdf',
            'verification_status' => LandlordDocument::STATUS_APPROVED,
            'is_current' => false,
            'superseded_at' => now(),
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.documents.approve', $supersededDoc));
        $response->assertNotFound();
    }
}
