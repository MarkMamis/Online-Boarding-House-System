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
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'enrollment_proof_type')) {
                $table->string('enrollment_proof_type', 20)
                    ->nullable()
                    ->after('school_id_path');
            }

            if (!Schema::hasColumn('users', 'enrollment_proof_path')) {
                $table->string('enrollment_proof_path')
                    ->nullable()
                    ->after('enrollment_proof_type');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $dropColumns = array_filter([
                Schema::hasColumn('users', 'enrollment_proof_path') ? 'enrollment_proof_path' : null,
                Schema::hasColumn('users', 'enrollment_proof_type') ? 'enrollment_proof_type' : null,
            ]);

            if (!empty($dropColumns)) {
                $table->dropColumn($dropColumns);
            }
        });
    }
};
