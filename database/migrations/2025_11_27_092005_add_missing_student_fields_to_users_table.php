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
        Schema::table('users', function (Blueprint $table) {
            $table->string('emergency_contact_relationship')->nullable()->after('emergency_contact_number');
            $table->string('blood_type')->nullable()->after('guardian_contact');
            $table->text('allergies')->nullable()->after('blood_type');
            $table->text('medications')->nullable()->after('allergies');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'emergency_contact_relationship',
                'blood_type',
                'allergies',
                'medications'
            ]);
        });
    }
};
