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
        // Check current columns
        $columns = DB::select("SHOW COLUMNS FROM rooms LIKE 'room_%'");
        
        $hasRoomName = false;
        $hasRoomNumber = false;
        
        foreach ($columns as $column) {
            if ($column->Field === 'room_name') {
                $hasRoomName = true;
            }
            if ($column->Field === 'room_number') {
                $hasRoomNumber = true;
            }
        }
        
        Schema::table('rooms', function (Blueprint $table) use ($hasRoomName, $hasRoomNumber) {
            // Drop room_name if it exists
            if ($hasRoomName) {
                $table->dropColumn('room_name');
            }
            
            // Add room_number if it doesn't exist
            if (!$hasRoomNumber) {
                $table->string('room_number')->after('landlord_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            // Drop room_number if it exists
            $columns = DB::select("SHOW COLUMNS FROM rooms LIKE 'room_number'");
            if (count($columns) > 0) {
                $table->dropColumn('room_number');
            }
        });
    }
};
