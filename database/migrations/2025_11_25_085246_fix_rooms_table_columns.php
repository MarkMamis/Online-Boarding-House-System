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
        $hasRoomName   = Schema::hasColumn('rooms', 'room_name');
        $hasRoomNumber = Schema::hasColumn('rooms', 'room_number');

        Schema::table('rooms', function (Blueprint $table) use ($hasRoomName, $hasRoomNumber) {
            if ($hasRoomName) {
                $table->dropColumn('room_name');
            }
            if (! $hasRoomNumber) {
                $table->string('room_number')->after('landlord_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('rooms', 'room_number')) {
            Schema::table('rooms', function (Blueprint $table) {
                $table->dropColumn('room_number');
            });
        }
    }
};
