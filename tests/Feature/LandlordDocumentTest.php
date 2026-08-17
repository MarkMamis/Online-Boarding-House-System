<?php

namespace Tests\Feature;

use App\Models\LandlordDocument;
use App\Models\LandlordProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LandlordDocumentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Keep feature tests isolated from the real R2 account while still
        // exercising the same configured-disk selection as production.
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

    protected function landlordUser(array $attributes = []): User
    {
        $user = User::create(array_merge([
            'full_name' => 'Test Landlord',
            'name' => 'Test Landlord',
            'email' => 'landlord' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'landlord',
            'contact_number' => '09171234567',
            'boarding_house_name' => 'Test Boarding House',
            'onboarding_complete' => true,
        ], $attributes));

        // email_verified_at is not mass-assignable, so mark verification explicitly.
        $user->forceFill(['email_verified_at' => now()])->save();

        return $user->refresh();
    }

    protected function adminUser(): User
    {
        $user = User::create([
            'full_name' => 'Test Admin',
            'name' => 'Test Admin',
            'email' => 'admin' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'contact_number' => '09171234567',
            'boarding_house_name' => 'Admin House',
            'onboarding_complete' => true,
        ]);

        $user->forceFill(['email_verified_at' => now()])->save();

        return $user->refresh();
    }

    protected function studentUser(): User
    {
        $user = User::create([
            'full_name' => 'Test Student',
            'name' => 'Test Student',
            'email' => 'student' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'student',
            'contact_number' => '09171234567',
            'boarding_house_name' => 'Student House',
            'onboarding_complete' => true,
        ]);

        $user->forceFill(['email_verified_at' => now()])->save();

        return $user->refresh();
    }

    protected function submitPayload(array $overrides = []): array
    {
        return array_merge([
            'document_type' => LandlordDocument::TYPE_BUSINESS_PERMIT,
            'document_number' => 'BP-2026-00123',
            'date_issued' => '2026-03-31',
            'expiration_date' => '2027-03-31',
            'file' => UploadedFile::fake()->create('permit.pdf', 100, 'application/pdf'),
        ], $overrides);
    }

    // ── Landlord upload ───────────────────────────────────────────

    public function test_landlord_can_upload_own_document(): void
    {
        Storage::fake('public');

        $landlord = $this->landlordUser();
        $this->actingAs($landlord)
            ->post(route('landlord.documents.store'), $this->submitPayload())
            ->assertRedirect();

        $document = LandlordDocument::where('landlord_id', $landlord->id)->first();

        $this->assertNotNull($document);
        $this->assertSame(LandlordDocument::TYPE_BUSINESS_PERMIT, $document->document_type);
        $this->assertSame('BP-2026-00123', $document->document_number);
        $this->assertStringStartsWith('landlords/' . $landlord->id . '/documents/business-permits/', $document->file_path);
        $this->assertStringNotContainsString('://', $document->file_path);
        Storage::disk('r2')->assertExists($document->file_path);
        Storage::disk('public')->assertMissing($document->file_path);
        $this->assertNotNull($document->submitted_at);
    }

    public function test_new_submission_defaults_to_pending(): void
    {
        Storage::fake('public');

        $landlord = $this->landlordUser();
        $this->actingAs($landlord)
            ->post(route('landlord.documents.store'), $this->submitPayload());

        $document = LandlordDocument::where('landlord_id', $landlord->id)->first();

        $this->assertSame(LandlordDocument::STATUS_PENDING, $document->verification_status);
        $this->assertNull($document->approved_by);
        $this->assertNull($document->approved_at);
    }

    public function test_pending_document_stays_pending_even_when_date_is_near_expiration(): void
    {
        Storage::fake('public');

        $landlord = $this->landlordUser();
        $document = LandlordDocument::create([
            'landlord_id' => $landlord->id,
            'document_type' => LandlordDocument::TYPE_SAFETY_CERTIFICATE,
            'file_path' => 'landlords/' . $landlord->id . '/documents/safety-certificates/old.pdf',
            'expiration_date' => now()->addDays(2)->toDateString(),
            'verification_status' => LandlordDocument::STATUS_PENDING,
            'submitted_at' => now(),
        ]);

        $expiration = $document->expirationInfo();

        $this->assertSame('expiring_soon', $expiration['status']);
        $this->assertSame('critical_7', $expiration['urgency']);
        // Verification state must remain untouched by expiration math.
        $this->assertSame(LandlordDocument::STATUS_PENDING, $document->fresh()->verification_status);
    }

    // ── Access control ────────────────────────────────────────────

    public function test_landlord_cannot_resubmit_another_landlord_document(): void
    {
        Storage::fake('public');

        $owner = $this->landlordUser(['full_name' => 'Owner Landlord']);
        $other = $this->landlordUser(['full_name' => 'Other Landlord']);

        $document = LandlordDocument::create([
            'landlord_id' => $owner->id,
            'document_type' => LandlordDocument::TYPE_BUSINESS_PERMIT,
            'file_path' => 'landlords/' . $owner->id . '/documents/business-permits/owner.pdf',
            'verification_status' => LandlordDocument::STATUS_REJECTED,
            'rejection_reason' => 'Illegible',
            'rejected_at' => now(),
            'submitted_at' => now(),
        ]);

        $this->actingAs($other)
            ->post(route('landlord.documents.resubmit', $document), $this->submitPayload())
            ->assertNotFound();

        $this->assertSame(LandlordDocument::STATUS_REJECTED, $document->fresh()->verification_status);
    }

    public function test_landlord_cannot_download_another_landlord_private_document(): void
    {
        Storage::fake('public');

        $owner = $this->landlordUser();
        $other = $this->landlordUser();

        $path = 'landlords/' . $owner->id . '/documents/business-permits/private.pdf';
        Storage::disk('r2')->put($path, 'secret-contents');

        LandlordDocument::create([
            'landlord_id' => $owner->id,
            'document_type' => LandlordDocument::TYPE_BUSINESS_PERMIT,
            'file_path' => $path,
            'verification_status' => LandlordDocument::STATUS_APPROVED,
            'submitted_at' => now(),
        ]);

        // Owner can view.
        $this->actingAs($owner)->get(route('files.show', ['path' => $path]))->assertOk();

        // Another landlord cannot.
        $this->actingAs($other)->get(route('files.show', ['path' => $path]))->assertForbidden();

        // A student cannot.
        $this->actingAs($this->studentUser())->get(route('files.show', ['path' => $path]))->assertForbidden();

        // Unauthenticated cannot (this app renders unauthenticated file access
        // as 403 via the exception handler rather than a login redirect).
        $this->get(route('files.show', ['path' => $path]))->assertForbidden();
    }

    // ── Admin verification ────────────────────────────────────────

    public function test_admin_can_approve_document(): void
    {
        $landlord = $this->landlordUser();
        $admin = $this->adminUser();

        $document = LandlordDocument::create([
            'landlord_id' => $landlord->id,
            'document_type' => LandlordDocument::TYPE_BUSINESS_PERMIT,
            'file_path' => 'landlords/' . $landlord->id . '/documents/business-permits/a.pdf',
            'verification_status' => LandlordDocument::STATUS_PENDING,
            'submitted_at' => now(),
        ]);

        $this->actingAs($admin)
            ->post(route('admin.documents.approve', $document))
            ->assertRedirect();

        $document->refresh();

        $this->assertSame(LandlordDocument::STATUS_APPROVED, $document->verification_status);
        $this->assertSame($admin->id, $document->approved_by);
        $this->assertNotNull($document->approved_at);
        $this->assertNull($document->rejection_reason);
    }

    public function test_admin_can_reject_document_with_reason(): void
    {
        $landlord = $this->landlordUser();
        $admin = $this->adminUser();

        $document = LandlordDocument::create([
            'landlord_id' => $landlord->id,
            'document_type' => LandlordDocument::TYPE_SAFETY_CERTIFICATE,
            'file_path' => 'landlords/' . $landlord->id . '/documents/safety-certificates/a.pdf',
            'verification_status' => LandlordDocument::STATUS_PENDING,
            'submitted_at' => now(),
        ]);

        $this->actingAs($admin)
            ->post(route('admin.documents.reject', $document), ['rejection_reason' => 'Document is unreadable.'])
            ->assertRedirect();

        $document->refresh();

        $this->assertSame(LandlordDocument::STATUS_REJECTED, $document->verification_status);
        $this->assertSame('Document is unreadable.', $document->rejection_reason);
        $this->assertNotNull($document->rejected_at);
        $this->assertNull($document->approved_by);
        $this->assertNull($document->approved_at);
    }

    public function test_reject_requires_reason(): void
    {
        $landlord = $this->landlordUser();
        $admin = $this->adminUser();

        $document = LandlordDocument::create([
            'landlord_id' => $landlord->id,
            'document_type' => LandlordDocument::TYPE_BUSINESS_PERMIT,
            'file_path' => 'x.pdf',
            'verification_status' => LandlordDocument::STATUS_PENDING,
            'submitted_at' => now(),
        ]);

        $this->actingAs($admin)
            ->from(route('admin.documents.verification'))
            ->post(route('admin.documents.reject', $document), ['rejection_reason' => ''])
            ->assertSessionHasErrors('rejection_reason');

        $this->assertSame(LandlordDocument::STATUS_PENDING, $document->fresh()->verification_status);
    }

    public function test_landlord_cannot_approve_own_document(): void
    {
        $landlord = $this->landlordUser();

        $document = LandlordDocument::create([
            'landlord_id' => $landlord->id,
            'document_type' => LandlordDocument::TYPE_BUSINESS_PERMIT,
            'file_path' => 'x.pdf',
            'verification_status' => LandlordDocument::STATUS_PENDING,
            'submitted_at' => now(),
        ]);

        // Landlord is not in the admin role group.
        $this->actingAs($landlord)
            ->post(route('admin.documents.approve', $document))
            ->assertForbidden();

        $this->assertSame(LandlordDocument::STATUS_PENDING, $document->fresh()->verification_status);
    }

    // ── Resubmission & replacement ────────────────────────────────

    public function test_rejected_document_can_be_resubmitted(): void
    {
        Storage::fake('public');

        $landlord = $this->landlordUser();
        $oldPath = 'landlords/' . $landlord->id . '/documents/business-permits/old.pdf';
        Storage::disk('public')->put($oldPath, 'old document');

        $document = LandlordDocument::create([
            'landlord_id' => $landlord->id,
            'document_type' => LandlordDocument::TYPE_BUSINESS_PERMIT,
            'file_path' => $oldPath,
            'document_number' => 'BP-OLD-1',
            'verification_status' => LandlordDocument::STATUS_REJECTED,
            'rejection_reason' => 'Blurry',
            'rejected_at' => now(),
            'submitted_at' => now()->subDay(),
        ]);

        $this->actingAs($landlord)
            ->post(route('landlord.documents.resubmit', $document), $this->submitPayload())
            ->assertRedirect();

        $document->refresh();
        $replacement = $landlord->landlordDocuments()->current()->first();

        $this->assertSame(LandlordDocument::STATUS_REJECTED, $document->verification_status);
        $this->assertFalse($document->is_current);
        $this->assertNotNull($document->superseded_at);
        $this->assertNotNull($replacement);
        $this->assertSame(LandlordDocument::STATUS_PENDING, $replacement->verification_status);
        $this->assertNull($replacement->rejection_reason);
        $this->assertNull($replacement->rejected_at);
        $this->assertNull($replacement->approved_by);
        $this->assertNull($replacement->approved_at);
        $this->assertNotNull($replacement->submitted_at);
        $this->assertNotSame($oldPath, $replacement->file_path);
        Storage::disk('public')->assertExists($oldPath);
        Storage::disk('r2')->assertExists($replacement->file_path);
    }

    public function test_approved_document_replacement_becomes_pending_again(): void
    {
        Storage::fake('public');

        $landlord = $this->landlordUser();
        $admin = $this->adminUser();

        $document = LandlordDocument::create([
            'landlord_id' => $landlord->id,
            'document_type' => LandlordDocument::TYPE_SAFETY_CERTIFICATE,
            'file_path' => 'landlords/' . $landlord->id . '/documents/safety-certificates/old.pdf',
            'verification_status' => LandlordDocument::STATUS_APPROVED,
            'approved_by' => $admin->id,
            'approved_at' => now()->subMonth(),
            'submitted_at' => now()->subMonth(),
        ]);

        $this->actingAs($landlord)
            ->post(route('landlord.documents.resubmit', $document), $this->submitPayload([
                'document_type' => LandlordDocument::TYPE_SAFETY_CERTIFICATE,
            ]))
            ->assertRedirect();

        $document->refresh();
        $replacement = $landlord->landlordDocuments()->current()->first();

        $this->assertSame(LandlordDocument::STATUS_APPROVED, $document->verification_status);
        $this->assertFalse($document->is_current);
        $this->assertNotNull($document->superseded_at);
        $this->assertNotNull($replacement);
        $this->assertSame(LandlordDocument::STATUS_PENDING, $replacement->verification_status);
        $this->assertNull($replacement->approved_by);
        $this->assertNull($replacement->approved_at);
    }

    // ── Validation ────────────────────────────────────────────────

    public function test_expiration_date_cannot_precede_date_issued(): void
    {
        Storage::fake('public');

        $landlord = $this->landlordUser();

        $this->actingAs($landlord)
            ->from(route('landlord.documents.index'))
            ->post(route('landlord.documents.store'), $this->submitPayload([
                'date_issued' => '2027-01-01',
                'expiration_date' => '2026-01-01',
            ]))
            ->assertSessionHasErrors('expiration_date');

        $this->assertNull(LandlordDocument::where('landlord_id', $landlord->id)->first());
    }

    public function test_unsupported_document_type_is_rejected(): void
    {
        Storage::fake('public');

        $landlord = $this->landlordUser();

        $this->actingAs($landlord)
            ->from(route('landlord.documents.index'))
            ->post(route('landlord.documents.store'), $this->submitPayload([
                'document_type' => 'sanitary_permit',
            ]))
            ->assertSessionHasErrors('document_type');
    }

    // ── Legacy compatibility ──────────────────────────────────────

    public function test_legacy_landlord_document_columns_still_work(): void
    {
        $landlord = $this->landlordUser();

        LandlordProfile::create([
            'user_id' => $landlord->id,
            'contact_number' => '09171234567',
            'boarding_house_name' => 'Sunny Dorm',
            'about' => 'A nice dorm.',
            'business_permit_path' => 'landlords/' . $landlord->id . '/business-permits/legacy.pdf',
            'business_permit_status' => 'approved',
            'safety_certificate_path' => 'landlords/' . $landlord->id . '/safety-certificates/legacy.pdf',
        ]);

        $profile = $landlord->landlordProfile()->first();

        $this->assertSame('landlords/' . $landlord->id . '/business-permits/legacy.pdf', $profile->business_permit_path);
        $this->assertSame('approved', $profile->business_permit_status);
        $this->assertSame('landlords/' . $landlord->id . '/safety-certificates/legacy.pdf', $profile->safety_certificate_path);
    }

    // ── Admin pages ───────────────────────────────────────────────

    public function test_admin_monitoring_page_lists_documents(): void
    {
        $landlord = $this->landlordUser();
        $admin = $this->adminUser();

        LandlordDocument::create([
            'landlord_id' => $landlord->id,
            'document_type' => LandlordDocument::TYPE_BUSINESS_PERMIT,
            'file_path' => 'landlords/' . $landlord->id . '/documents/business-permits/a.pdf',
            'document_number' => 'BP-00122',
            'expiration_date' => now()->addDays(15)->toDateString(),
            'verification_status' => LandlordDocument::STATUS_APPROVED,
            'approved_at' => now()->subDay(),
            'submitted_at' => now()->subDays(2),
        ]);

        $this->actingAs($admin)
            ->get(route('admin.documents.monitoring'))
            ->assertOk()
            ->assertSee('BP-00122')
            ->assertSee('Test Landlord')
            ->assertSee('Expiring Soon');
    }

    public function test_admin_monitoring_counts_come_from_database(): void
    {
        $admin = $this->adminUser();
        $landlordA = $this->landlordUser();
        $landlordB = $this->landlordUser();

        LandlordDocument::create(['landlord_id' => $landlordA->id, 'document_type' => LandlordDocument::TYPE_BUSINESS_PERMIT, 'file_path' => 'a.pdf', 'expiration_date' => now()->addDays(100)->toDateString(), 'verification_status' => LandlordDocument::STATUS_APPROVED, 'submitted_at' => now()]);
        LandlordDocument::create(['landlord_id' => $landlordA->id, 'document_type' => LandlordDocument::TYPE_SAFETY_CERTIFICATE, 'file_path' => 'b.pdf', 'expiration_date' => now()->addDays(15)->toDateString(), 'verification_status' => LandlordDocument::STATUS_APPROVED, 'submitted_at' => now()]);
        LandlordDocument::create(['landlord_id' => $landlordB->id, 'document_type' => LandlordDocument::TYPE_BUSINESS_PERMIT, 'file_path' => 'c.pdf', 'expiration_date' => now()->subDays(5)->toDateString(), 'verification_status' => LandlordDocument::STATUS_APPROVED, 'submitted_at' => now()]);
        LandlordDocument::create(['landlord_id' => $landlordB->id, 'document_type' => LandlordDocument::TYPE_SAFETY_CERTIFICATE, 'file_path' => 'd.pdf', 'expiration_date' => now()->addDays(100)->toDateString(), 'verification_status' => LandlordDocument::STATUS_PENDING, 'submitted_at' => now()]);

        $response = $this->actingAs($admin)->get(route('admin.documents.monitoring'));

        $response->assertOk();
        $response->assertSee('>1<', false); // valid
        $response->assertSee('>1<', false); // expiring soon
        $response->assertSee('>1<', false); // expired
        $response->assertSee('>1<', false); // pending verification
    }
}
