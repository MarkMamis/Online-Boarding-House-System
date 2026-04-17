<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            if (!Schema::hasColumn('rooms', 'pricing_model')) {
                $table->string('pricing_model', 20)->default('hybrid')->after('price');
            }

            if (!Schema::hasColumn('rooms', 'price_per_room')) {
                $table->decimal('price_per_room', 10, 2)->nullable()->after('pricing_model');
            }

            if (!Schema::hasColumn('rooms', 'price_per_bed')) {
                $table->decimal('price_per_bed', 10, 2)->nullable()->after('price_per_room');
            }
        });

        DB::statement("UPDATE rooms SET pricing_model = COALESCE(NULLIF(pricing_model, ''), 'hybrid')");
        DB::statement('UPDATE rooms SET price_per_room = COALESCE(price_per_room, price)');
        DB::statement('UPDATE rooms SET price_per_bed = COALESCE(price_per_bed, CASE WHEN capacity > 0 THEN ROUND(price / capacity, 2) ELSE price END)');
        DB::statement('UPDATE rooms SET price = CASE pricing_model WHEN \'per_room\' THEN COALESCE(price_per_room, price) WHEN \'per_bed\' THEN COALESCE(price_per_bed, price) ELSE LEAST(COALESCE(price_per_room, price), COALESCE(price_per_bed, price)) END');
    }

    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $dropColumns = [];

            if (Schema::hasColumn('rooms', 'price_per_bed')) {
                $dropColumns[] = 'price_per_bed';
            }

            if (Schema::hasColumn('rooms', 'price_per_room')) {
                $dropColumns[] = 'price_per_room';
            }

            if (Schema::hasColumn('rooms', 'pricing_model')) {
                $dropColumns[] = 'pricing_model';
            }

            if (!empty($dropColumns)) {
                $table->dropColumn($dropColumns);
            }
        });
    }
};
