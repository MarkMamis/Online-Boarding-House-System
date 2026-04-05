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
            if (!Schema::hasColumn('tenant_onboardings', 'payment_method')) {
                $table->string('payment_method', 20)->nullable()->after('deposit_amount');
            }

            if (!Schema::hasColumn('tenant_onboardings', 'payment_reference')) {
                $table->string('payment_reference', 120)->nullable()->after('payment_method');
            }

            if (!Schema::hasColumn('tenant_onboardings', 'payment_proof_path')) {
                $table->string('payment_proof_path')->nullable()->after('payment_reference');
            }

            if (!Schema::hasColumn('tenant_onboardings', 'payment_notes')) {
                $table->text('payment_notes')->nullable()->after('payment_proof_path');
            }

            if (!Schema::hasColumn('tenant_onboardings', 'payment_submitted_at')) {
                $table->timestamp('payment_submitted_at')->nullable()->after('payment_notes');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenant_onboardings', function (Blueprint $table) {
            $columns = [
                'payment_method',
                'payment_reference',
                'payment_proof_path',
                'payment_notes',
                'payment_submitted_at',
            ];

            $dropColumns = array_values(array_filter($columns, function ($column) {
                return Schema::hasColumn('tenant_onboardings', $column);
            }));

            if (!empty($dropColumns)) {
                $table->dropColumn($dropColumns);
            }
        });
    }
};
