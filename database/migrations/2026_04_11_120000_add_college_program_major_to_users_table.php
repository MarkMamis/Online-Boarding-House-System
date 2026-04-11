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
            if (!Schema::hasColumn('users', 'college')) {
                $table->string('college', 16)->nullable()->after('student_id');
            }

            if (!Schema::hasColumn('users', 'program')) {
                $table->string('program')->nullable()->after('college');
            }

            if (!Schema::hasColumn('users', 'major')) {
                $table->string('major')->nullable()->after('program');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'major')) {
                $table->dropColumn('major');
            }

            if (Schema::hasColumn('users', 'program')) {
                $table->dropColumn('program');
            }

            if (Schema::hasColumn('users', 'college')) {
                $table->dropColumn('college');
            }
        });
    }
};
