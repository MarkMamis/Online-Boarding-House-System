<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('landlord_profiles')) {
            return;
        }

        if (!Schema::hasColumn('landlord_profiles', 'tenant_privacy_settings')) {
            return;
        }

        Schema::table('landlord_profiles', function (Blueprint $table) {
            $table->dropColumn('tenant_privacy_settings');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('landlord_profiles')) {
            return;
        }

        if (Schema::hasColumn('landlord_profiles', 'tenant_privacy_settings')) {
            return;
        }

        Schema::table('landlord_profiles', function (Blueprint $table) {
            $table->json('tenant_privacy_settings')->nullable()->after('preferred_payment_methods');
        });
    }
};
