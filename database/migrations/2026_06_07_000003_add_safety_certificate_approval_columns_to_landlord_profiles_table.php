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
            if (!Schema::hasColumn('landlord_profiles', 'safety_certificate_status')) {
                $table->string('safety_certificate_status', 20)
                    ->default('not_submitted')
                    ->after('safety_certificate_path');
            }

            if (!Schema::hasColumn('landlord_profiles', 'safety_certificate_reviewed_at')) {
                $table->timestamp('safety_certificate_reviewed_at')
                    ->nullable()
                    ->after('safety_certificate_status');
            }

            if (!Schema::hasColumn('landlord_profiles', 'safety_certificate_reviewed_by')) {
                $table->foreignId('safety_certificate_reviewed_by')
                    ->nullable()
                    ->after('safety_certificate_reviewed_at')
                    ->constrained('users')
                    ->nullOnDelete();
            }

            if (!Schema::hasColumn('landlord_profiles', 'safety_certificate_rejection_reason')) {
                $table->string('safety_certificate_rejection_reason', 500)
                    ->nullable()
                    ->after('safety_certificate_reviewed_by');
            }
        });

        if (Schema::hasColumn('landlord_profiles', 'safety_certificate_path')) {
            DB::table('landlord_profiles')->update([
                'safety_certificate_status' => DB::raw("CASE WHEN safety_certificate_path IS NULL OR safety_certificate_path = '' THEN 'not_submitted' ELSE 'pending' END"),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('landlord_profiles', function (Blueprint $table) {
            if (Schema::hasColumn('landlord_profiles', 'safety_certificate_reviewed_by')) {
                $table->dropConstrainedForeignId('safety_certificate_reviewed_by');
            }

            if (Schema::hasColumn('landlord_profiles', 'safety_certificate_rejection_reason')) {
                $table->dropColumn('safety_certificate_rejection_reason');
            }

            if (Schema::hasColumn('landlord_profiles', 'safety_certificate_reviewed_at')) {
                $table->dropColumn('safety_certificate_reviewed_at');
            }

            if (Schema::hasColumn('landlord_profiles', 'safety_certificate_status')) {
                $table->dropColumn('safety_certificate_status');
            }
        });
    }
};
