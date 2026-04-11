<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('users', 'course')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('course');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasColumn('users', 'course')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('course')->nullable()->after('student_id');
            });
        }
    }
};
