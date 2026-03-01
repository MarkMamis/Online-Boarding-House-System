<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('rooms', 'landlord_id')) {
            return; // Column was removed in a later migration; nothing to fix.
        }

        // Drop the incorrect foreign key constraint that references landlord_profiles
        Schema::table('rooms', function (Blueprint $table) {
            try {
                $table->dropForeign(['landlord_id']);
            } catch (\Exception $e) {
                // Foreign key might not exist, continue
            }
        });

        // Add the correct foreign key constraint that references users
        Schema::table('rooms', function (Blueprint $table) {
            $table->foreign('landlord_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasColumn('rooms', 'landlord_id')) {
            return;
        }

        Schema::table('rooms', function (Blueprint $table) {
            try {
                $table->dropForeign(['landlord_id']);
            } catch (\Exception $e) {
                // Foreign key might not exist, continue
            }
        });

        // Re-add the incorrect foreign key (for rollback purposes)
        Schema::table('rooms', function (Blueprint $table) {
            $table->foreign('landlord_id')->references('id')->on('landlord_profiles')->cascadeOnDelete();
        });
    }
};
