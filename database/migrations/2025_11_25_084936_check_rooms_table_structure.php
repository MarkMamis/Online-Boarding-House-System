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

        if ($hasRoomName && ! $hasRoomNumber) {
            Schema::table('rooms', function (Blueprint $table) {
                $table->renameColumn('room_name', 'room_number');
            });
        } elseif (! $hasRoomName && ! $hasRoomNumber) {
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
        if (Schema::hasColumn('rooms', 'room_number')) {
            Schema::table('rooms', function (Blueprint $table) {
                $table->dropColumn('room_number');
            });
        }
    }
};
