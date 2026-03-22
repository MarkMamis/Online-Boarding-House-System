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
            if (!Schema::hasColumn('users', 'parent_contact_name')) {
                $table->string('parent_contact_name')->nullable()->after('emergency_contact_relationship');
            }

            if (!Schema::hasColumn('users', 'parent_contact_number')) {
                $table->string('parent_contact_number')->nullable()->after('parent_contact_name');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $dropColumns = [];

            if (Schema::hasColumn('users', 'parent_contact_name')) {
                $dropColumns[] = 'parent_contact_name';
            }

            if (Schema::hasColumn('users', 'parent_contact_number')) {
                $dropColumns[] = 'parent_contact_number';
            }

            if (!empty($dropColumns)) {
                $table->dropColumn($dropColumns);
            }
        });
    }
};
