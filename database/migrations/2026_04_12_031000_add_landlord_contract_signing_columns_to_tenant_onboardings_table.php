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
            if (!Schema::hasColumn('tenant_onboardings', 'landlord_contract_signed')) {
                $table->boolean('landlord_contract_signed')->default(false)->after('contract_signature_name');
            }

            if (!Schema::hasColumn('tenant_onboardings', 'landlord_contract_signed_at')) {
                $table->timestamp('landlord_contract_signed_at')->nullable()->after('landlord_contract_signed');
            }

            if (!Schema::hasColumn('tenant_onboardings', 'landlord_contract_signature_path')) {
                $table->string('landlord_contract_signature_path')->nullable()->after('landlord_contract_signed_at');
            }

            if (!Schema::hasColumn('tenant_onboardings', 'landlord_contract_signature_name')) {
                $table->string('landlord_contract_signature_name')->nullable()->after('landlord_contract_signature_path');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenant_onboardings', function (Blueprint $table) {
            if (Schema::hasColumn('tenant_onboardings', 'landlord_contract_signature_name')) {
                $table->dropColumn('landlord_contract_signature_name');
            }

            if (Schema::hasColumn('tenant_onboardings', 'landlord_contract_signature_path')) {
                $table->dropColumn('landlord_contract_signature_path');
            }

            if (Schema::hasColumn('tenant_onboardings', 'landlord_contract_signed_at')) {
                $table->dropColumn('landlord_contract_signed_at');
            }

            if (Schema::hasColumn('tenant_onboardings', 'landlord_contract_signed')) {
                $table->dropColumn('landlord_contract_signed');
            }
        });
    }
};
