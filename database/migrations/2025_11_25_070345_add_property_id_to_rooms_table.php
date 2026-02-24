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
        Schema::table('rooms', function (Blueprint $table) {
            // Add property_id column after id
            $table->foreignId('property_id')->nullable()->after('id')->constrained('properties')->cascadeOnDelete();
        });
        
        // Migrate data: For each room with landlord_id, find or create a default property
        DB::statement('
            UPDATE rooms r
            INNER JOIN properties p ON r.landlord_id = p.landlord_id
            SET r.property_id = p.id
            WHERE r.property_id IS NULL
            LIMIT 1
        ');
        
        // Make property_id not nullable after migration
        Schema::table('rooms', function (Blueprint $table) {
            $table->foreignId('property_id')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropForeign(['property_id']);
            $table->dropColumn('property_id');
        });
    }
};
