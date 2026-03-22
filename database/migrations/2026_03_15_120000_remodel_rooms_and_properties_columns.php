<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('rooms', 'slots_available')) {
            Schema::table('rooms', function (Blueprint $table) {
                $table->unsignedInteger('slots_available')->default(0)->after('capacity');
            });

            // Initialize slot values for existing rows.
            DB::statement("UPDATE rooms SET slots_available = CASE WHEN status = 'available' THEN capacity ELSE 0 END");
        }

        if (Schema::hasColumn('rooms', 'landlord_id')) {
            $fkExists = DB::table('information_schema.KEY_COLUMN_USAGE')
                ->whereRaw('TABLE_SCHEMA = DATABASE()')
                ->where('TABLE_NAME', 'rooms')
                ->where('COLUMN_NAME', 'landlord_id')
                ->whereNotNull('REFERENCED_TABLE_NAME')
                ->exists();

            if ($fkExists) {
                DB::statement('ALTER TABLE rooms DROP FOREIGN KEY rooms_landlord_id_foreign');
            }

            Schema::table('rooms', function (Blueprint $table) {
                $table->dropColumn('landlord_id');
            });
        }

        Schema::table('properties', function (Blueprint $table) {
            $drop = [];

            if (Schema::hasColumn('properties', 'rooms_total')) {
                $drop[] = 'rooms_total';
            }

            if (Schema::hasColumn('properties', 'rooms_vacant')) {
                $drop[] = 'rooms_vacant';
            }

            if (!empty($drop)) {
                $table->dropColumn($drop);
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasColumn('rooms', 'landlord_id')) {
            Schema::table('rooms', function (Blueprint $table) {
                $table->foreignId('landlord_id')->nullable()->after('property_id')->constrained('users')->nullOnDelete();
            });
        }

        if (Schema::hasColumn('rooms', 'slots_available')) {
            Schema::table('rooms', function (Blueprint $table) {
                $table->dropColumn('slots_available');
            });
        }

        Schema::table('properties', function (Blueprint $table) {
            if (!Schema::hasColumn('properties', 'rooms_total')) {
                $table->unsignedInteger('rooms_total')->default(0);
            }

            if (!Schema::hasColumn('properties', 'rooms_vacant')) {
                $table->unsignedInteger('rooms_vacant')->default(0);
            }
        });
    }
};
