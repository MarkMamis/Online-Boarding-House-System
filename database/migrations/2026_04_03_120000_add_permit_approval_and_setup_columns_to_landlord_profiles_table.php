<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('landlord_profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('landlord_profiles', 'business_permit_status')) {
                $table->string('business_permit_status', 20)
                    ->default('not_submitted')
                    ->after('business_permit_path');
            }

            if (!Schema::hasColumn('landlord_profiles', 'business_permit_reviewed_at')) {
                $table->timestamp('business_permit_reviewed_at')
                    ->nullable()
                    ->after('business_permit_status');
            }

            if (!Schema::hasColumn('landlord_profiles', 'business_permit_reviewed_by')) {
                $table->foreignId('business_permit_reviewed_by')
                    ->nullable()
                    ->after('business_permit_reviewed_at')
                    ->constrained('users')
                    ->nullOnDelete();
            }

            if (!Schema::hasColumn('landlord_profiles', 'business_permit_rejection_reason')) {
                $table->string('business_permit_rejection_reason', 500)
                    ->nullable()
                    ->after('business_permit_reviewed_by');
            }

            if (!Schema::hasColumn('landlord_profiles', 'profile_completed')) {
                $table->boolean('profile_completed')
                    ->default(false)
                    ->after('business_permit_rejection_reason');
            }

            if (!Schema::hasColumn('landlord_profiles', 'billing_completed')) {
                $table->boolean('billing_completed')
                    ->default(false)
                    ->after('profile_completed');
            }
        });

        DB::table('landlord_profiles')->update([
            'business_permit_status' => DB::raw("CASE WHEN business_permit_path IS NULL OR business_permit_path = '' THEN 'not_submitted' ELSE 'pending' END"),
            'profile_completed' => DB::raw("CASE
                WHEN COALESCE(NULLIF(TRIM(contact_number), ''), '') <> ''
                 AND COALESCE(NULLIF(TRIM(boarding_house_name), ''), '') <> ''
                 AND COALESCE(NULLIF(TRIM(about), ''), '') <> ''
                THEN 1 ELSE 0 END"),
        ]);
    }

    public function down(): void
    {
        Schema::table('landlord_profiles', function (Blueprint $table) {
            if (Schema::hasColumn('landlord_profiles', 'business_permit_reviewed_by')) {
                $table->dropConstrainedForeignId('business_permit_reviewed_by');
            }

            if (Schema::hasColumn('landlord_profiles', 'billing_completed')) {
                $table->dropColumn('billing_completed');
            }

            if (Schema::hasColumn('landlord_profiles', 'profile_completed')) {
                $table->dropColumn('profile_completed');
            }

            if (Schema::hasColumn('landlord_profiles', 'business_permit_rejection_reason')) {
                $table->dropColumn('business_permit_rejection_reason');
            }

            if (Schema::hasColumn('landlord_profiles', 'business_permit_reviewed_at')) {
                $table->dropColumn('business_permit_reviewed_at');
            }

            if (Schema::hasColumn('landlord_profiles', 'business_permit_status')) {
                $table->dropColumn('business_permit_status');
            }
        });
    }
};
