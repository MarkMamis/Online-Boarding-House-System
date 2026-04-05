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
            if (!Schema::hasColumn('tenant_onboardings', 'contract_signature_path')) {
                $table->string('contract_signature_path')->nullable()->after('contract_signed_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenant_onboardings', function (Blueprint $table) {
            if (Schema::hasColumn('tenant_onboardings', 'contract_signature_path')) {
                $table->dropColumn('contract_signature_path');
            }
        });
    }
};
