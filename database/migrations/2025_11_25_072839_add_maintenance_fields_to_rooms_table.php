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
        Schema::table('rooms', function (Blueprint $table) {
            if (! Schema::hasColumn('rooms', 'maintenance_reason')) {
                $table->string('maintenance_reason')->nullable()->after('status');
            }
            if (! Schema::hasColumn('rooms', 'maintenance_date')) {
                $table->timestamp('maintenance_date')->nullable()->after('maintenance_reason');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropColumn(['maintenance_reason', 'maintenance_date']);
        });
    }
};
