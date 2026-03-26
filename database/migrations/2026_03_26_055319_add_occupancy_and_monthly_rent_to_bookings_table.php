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
        Schema::table('bookings', function (Blueprint $table) {
            if (!Schema::hasColumn('bookings', 'occupancy_mode')) {
                $table->string('occupancy_mode', 20)->default('solo')->after('include_advance_payment');
            }

            if (!Schema::hasColumn('bookings', 'monthly_rent_amount')) {
                $table->decimal('monthly_rent_amount', 10, 2)->nullable()->after('occupancy_mode');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $dropColumns = [];
            if (Schema::hasColumn('bookings', 'monthly_rent_amount')) {
                $dropColumns[] = 'monthly_rent_amount';
            }
            if (Schema::hasColumn('bookings', 'occupancy_mode')) {
                $dropColumns[] = 'occupancy_mode';
            }
            if (!empty($dropColumns)) {
                $table->dropColumn($dropColumns);
            }
        });
    }
};
