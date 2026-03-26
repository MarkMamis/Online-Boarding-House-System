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
            if (!Schema::hasColumn('landlord_profiles', 'payment_gcash_qr_path')) {
                $table->string('payment_gcash_qr_path')->nullable()->after('payment_gcash_number');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('landlord_profiles', function (Blueprint $table) {
            if (Schema::hasColumn('landlord_profiles', 'payment_gcash_qr_path')) {
                $table->dropColumn('payment_gcash_qr_path');
            }
        });
    }
};
