<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('landlord_profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('landlord_profiles', 'contract_signature_path')) {
                $table->string('contract_signature_path')->nullable()->after('payment_gcash_qr_path');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('landlord_profiles', function (Blueprint $table) {
            if (Schema::hasColumn('landlord_profiles', 'contract_signature_path')) {
                $table->dropColumn('contract_signature_path');
            }
        });
    }
};
