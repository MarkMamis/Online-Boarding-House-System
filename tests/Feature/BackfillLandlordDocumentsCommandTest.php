<?php

namespace Tests\Feature;

use App\Models\LandlordDocument;
use App\Models\LandlordProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BackfillLandlordDocumentsCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_backfill_creates_records_from_legacy_columns(): void
    {
        $landlord = User::create([
            'full_name' => 'Legacy Landlord',
            'name' => 'Legacy Landlord',
            'email' => 'legacy@example.com',
            'password' => bcrypt('password'),
            'role' => 'landlord',
            'contact_number' => '09171234567',
            'boarding_house_name' => 'Legacy House',
            'email_verified_at' => now(),
        ]);

        LandlordProfile::create([
            'user_id' => $landlord->id,
            'contact_number' => '09171234567',
            'boarding_house_name' => 'Legacy Dorm',
            'about' => 'About',
            'business_permit_path' => 'landlords/' . $landlord->id . '/business-permits/legacy-permit.pdf',
            'business_permit_status' => 'approved',
            'business_permit_reviewed_by' => null,
            'safety_certificate_path' => 'landlords/' . $landlord->id . '/safety-certificates/legacy-safety.pdf',
            'safety_certificate_status' => 'pending',
        ]);

        $this->artisan('landlord-documents:backfill')
            ->expectsOutputToContain('Backfill complete')
            ->assertExitCode(0);

        $permit = LandlordDocument::where('landlord_id', $landlord->id)
            ->where('document_type', LandlordDocument::TYPE_BUSINESS_PERMIT)
            ->first();

        $safety = LandlordDocument::where('landlord_id', $landlord->id)
            ->where('document_type', LandlordDocument::TYPE_SAFETY_CERTIFICATE)
            ->first();

        $this->assertNotNull($permit);
        $this->assertSame('landlords/' . $landlord->id . '/business-permits/legacy-permit.pdf', $permit->file_path);
        $this->assertSame('approved', $permit->verification_status);

        $this->assertNotNull($safety);
        $this->assertSame('landlords/' . $landlord->id . '/safety-certificates/legacy-safety.pdf', $safety->file_path);
        $this->assertSame('pending', $safety->verification_status);

        // Legacy columns must remain untouched.
        $profile = $landlord->landlordProfile()->first();
        $this->assertSame('landlords/' . $landlord->id . '/business-permits/legacy-permit.pdf', $profile->business_permit_path);
    }

    public function test_backfill_is_idempotent(): void
    {
        $landlord = User::create([
            'full_name' => 'Idem Landlord',
            'name' => 'Idem Landlord',
            'email' => 'idem@example.com',
            'password' => bcrypt('password'),
            'role' => 'landlord',
            'contact_number' => '09171234567',
            'boarding_house_name' => 'Idem House',
            'email_verified_at' => now(),
        ]);

        LandlordProfile::create([
            'user_id' => $landlord->id,
            'contact_number' => '09171234567',
            'boarding_house_name' => 'Idem Dorm',
            'about' => 'About',
            'business_permit_path' => 'landlords/' . $landlord->id . '/business-permits/idem.pdf',
        ]);

        $this->artisan('landlord-documents:backfill')->assertExitCode(0);
        $this->artisan('landlord-documents:backfill')->assertExitCode(0);

        $this->assertSame(1, LandlordDocument::where('landlord_id', $landlord->id)
            ->where('document_type', LandlordDocument::TYPE_BUSINESS_PERMIT)
            ->count());
    }

    public function test_backfill_dry_run_does_not_write(): void
    {
        $landlord = User::create([
            'full_name' => 'Dry Landlord',
            'name' => 'Dry Landlord',
            'email' => 'dry@example.com',
            'password' => bcrypt('password'),
            'role' => 'landlord',
            'contact_number' => '09171234567',
            'boarding_house_name' => 'Dry House',
            'email_verified_at' => now(),
        ]);

        LandlordProfile::create([
            'user_id' => $landlord->id,
            'contact_number' => '09171234567',
            'boarding_house_name' => 'Dry Dorm',
            'about' => 'About',
            'business_permit_path' => 'landlords/' . $landlord->id . '/business-permits/dry.pdf',
        ]);

        $this->artisan('landlord-documents:backfill', ['--dry-run' => true])
            ->expectsOutputToContain('would be created')
            ->assertExitCode(0);

        $this->assertSame(0, LandlordDocument::where('landlord_id', $landlord->id)->count());
    }
}
