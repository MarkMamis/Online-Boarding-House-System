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
            // Rename contact to contact_number
            $table->renameColumn('contact', 'contact_number');
            
            // Drop address column and add boarding_house_name and about
            $table->dropColumn('address');
            $table->string('boarding_house_name')->nullable();
            $table->text('about')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('landlord_profiles', function (Blueprint $table) {
            $table->renameColumn('contact_number', 'contact');
            $table->string('address')->nullable();
            $table->dropColumn(['boarding_house_name', 'about']);
        });
    }
};
