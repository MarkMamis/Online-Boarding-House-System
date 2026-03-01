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
        Schema::table('landlord_profiles', function (Blueprint $table) {
            if (Schema::hasColumn('landlord_profiles', 'contact') && ! Schema::hasColumn('landlord_profiles', 'contact_number')) {
                $table->renameColumn('contact', 'contact_number');
            }

            if (Schema::hasColumn('landlord_profiles', 'address')) {
                $table->dropColumn('address');
            }

            if (! Schema::hasColumn('landlord_profiles', 'boarding_house_name')) {
                $table->string('boarding_house_name')->nullable();
            }

            if (! Schema::hasColumn('landlord_profiles', 'about')) {
                $table->text('about')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('landlord_profiles', function (Blueprint $table) {
            if (Schema::hasColumn('landlord_profiles', 'contact_number') && ! Schema::hasColumn('landlord_profiles', 'contact')) {
                $table->renameColumn('contact_number', 'contact');
            }
            if (! Schema::hasColumn('landlord_profiles', 'address')) {
                $table->string('address')->nullable();
            }
            if (Schema::hasColumn('landlord_profiles', 'boarding_house_name')) {
                $table->dropColumn('boarding_house_name');
            }
            if (Schema::hasColumn('landlord_profiles', 'about')) {
                $table->dropColumn('about');
            }
        });
    }
};
