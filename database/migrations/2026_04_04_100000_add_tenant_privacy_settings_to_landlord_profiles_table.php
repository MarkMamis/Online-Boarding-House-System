<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('landlord_profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('landlord_profiles', 'tenant_privacy_settings')) {
                $table->json('tenant_privacy_settings')->nullable()->after('preferred_payment_methods');
            }
        });
    }

    public function down(): void
    {
        Schema::table('landlord_profiles', function (Blueprint $table) {
            if (Schema::hasColumn('landlord_profiles', 'tenant_privacy_settings')) {
                $table->dropColumn('tenant_privacy_settings');
            }
        });
    }
};
