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
            if (!Schema::hasColumn('landlord_profiles', 'preferred_payment_methods')) {
                $table->json('preferred_payment_methods')->nullable()->after('payment_instructions');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('landlord_profiles', function (Blueprint $table) {
            if (Schema::hasColumn('landlord_profiles', 'preferred_payment_methods')) {
                $table->dropColumn('preferred_payment_methods');
            }
        });
    }
};
