<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'school_id_verification_status')) {
                $table->string('school_id_verification_status', 20)
                    ->default('not_submitted')
                    ->after('school_id_path');
            }

            if (!Schema::hasColumn('users', 'school_id_verified_at')) {
                $table->timestamp('school_id_verified_at')
                    ->nullable()
                    ->after('school_id_verification_status');
            }

            if (!Schema::hasColumn('users', 'school_id_verified_by')) {
                $table->unsignedBigInteger('school_id_verified_by')
                    ->nullable()
                    ->after('school_id_verified_at');
            }

            if (!Schema::hasColumn('users', 'school_id_rejection_reason')) {
                $table->text('school_id_rejection_reason')
                    ->nullable()
                    ->after('school_id_verified_by');
            }
        });

        if (Schema::hasColumn('users', 'school_id_verified_by')) {
            Schema::table('users', function (Blueprint $table) {
                $table->foreign('school_id_verified_by')
                    ->references('id')
                    ->on('users')
                    ->nullOnDelete();
            });
        }

        DB::table('users')
            ->where('role', 'student')
            ->whereNotNull('school_id_path')
            ->where(function ($query) {
                $query->whereNull('school_id_verification_status')
                    ->orWhere('school_id_verification_status', 'not_submitted');
            })
            ->update([
                'school_id_verification_status' => 'pending',
                'school_id_verified_at' => null,
                'school_id_verified_by' => null,
                'school_id_rejection_reason' => null,
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'school_id_verified_by')) {
                $table->dropForeign(['school_id_verified_by']);
            }

            $dropColumns = array_filter([
                Schema::hasColumn('users', 'school_id_verification_status') ? 'school_id_verification_status' : null,
                Schema::hasColumn('users', 'school_id_verified_at') ? 'school_id_verified_at' : null,
                Schema::hasColumn('users', 'school_id_verified_by') ? 'school_id_verified_by' : null,
                Schema::hasColumn('users', 'school_id_rejection_reason') ? 'school_id_rejection_reason' : null,
            ]);

            if (!empty($dropColumns)) {
                $table->dropColumn($dropColumns);
            }
        });
    }
};
