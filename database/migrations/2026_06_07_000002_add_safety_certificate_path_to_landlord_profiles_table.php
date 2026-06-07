<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('landlord_profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('landlord_profiles', 'safety_certificate_path')) {
                $table->string('safety_certificate_path')->nullable()->after('business_permit_path');
            }
        });
    }

    public function down(): void
    {
        Schema::table('landlord_profiles', function (Blueprint $table) {
            if (Schema::hasColumn('landlord_profiles', 'safety_certificate_path')) {
                $table->dropColumn('safety_certificate_path');
            }
        });
    }
};
