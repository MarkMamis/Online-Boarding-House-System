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
            $table->string('full_name');
            $table->string('contact_number');
            $table->string('boarding_house_name');
            $table->enum('role', ['admin', 'landlord', 'student'])->default('student');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['full_name', 'contact_number', 'boarding_house_name', 'role']);
        });
    }
};