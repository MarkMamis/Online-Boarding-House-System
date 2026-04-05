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
        Schema::table('tenant_onboardings', function (Blueprint $table) {
            if (!Schema::hasColumn('tenant_onboardings', 'advance_amount')) {
                $table->decimal('advance_amount', 10, 2)->nullable()->after('deposit_amount');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenant_onboardings', function (Blueprint $table) {
            if (Schema::hasColumn('tenant_onboardings', 'advance_amount')) {
                $table->dropColumn('advance_amount');
            }
        });
    }
};
