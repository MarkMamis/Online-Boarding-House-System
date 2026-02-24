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
        // Check if room_name column exists and room_number doesn't
        $columns = DB::select("SHOW COLUMNS FROM rooms");
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
        
        if ($hasRoomName && !$hasRoomNumber) {
            // Rename room_name to room_number
            Schema::table('rooms', function (Blueprint $table) {
                $table->renameColumn('room_name', 'room_number');
            });
        } elseif (!$hasRoomName && !$hasRoomNumber) {
            // Add room_number column
            Schema::table('rooms', function (Blueprint $table) {
                $table->string('room_number')->after('landlord_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Check if room_number column exists
        $columns = DB::select("SHOW COLUMNS FROM rooms");
        $hasRoomNumber = false;
        
        foreach ($columns as $column) {
            if ($column->Field === 'room_number') {
                $hasRoomNumber = true;
                break;
            }
        }
        
        if ($hasRoomNumber) {
            Schema::table('rooms', function (Blueprint $table) {
                $table->dropColumn('room_number');
            });
        }
    }
};
