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
            if (!Schema::hasColumn('users', 'parent_contact_address')) {
                $table->string('parent_contact_address', 500)->nullable()->after('parent_contact_number');
            }

            if (!Schema::hasColumn('users', 'parent_contact_photo_path')) {
                $table->string('parent_contact_photo_path')->nullable()->after('parent_contact_address');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'parent_contact_photo_path')) {
                $table->dropColumn('parent_contact_photo_path');
            }

            if (Schema::hasColumn('users', 'parent_contact_address')) {
                $table->dropColumn('parent_contact_address');
            }
        });
    }
};
