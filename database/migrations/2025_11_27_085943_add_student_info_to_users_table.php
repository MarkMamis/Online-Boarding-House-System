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
            $table->string('student_id')->nullable()->after('role');
            $table->string('course')->nullable()->after('student_id');
            $table->string('year_level')->nullable()->after('course');
            $table->date('birth_date')->nullable()->after('year_level');
            $table->text('address')->nullable()->after('birth_date');
            $table->string('emergency_contact_name')->nullable()->after('address');
            $table->string('emergency_contact_number')->nullable()->after('emergency_contact_name');
            $table->string('emergency_contact_relationship')->nullable()->after('emergency_contact_number');
            $table->string('guardian_name')->nullable()->after('emergency_contact_relationship');
            $table->string('guardian_contact')->nullable()->after('guardian_name');
            $table->string('blood_type')->nullable()->after('guardian_contact');
            $table->text('allergies')->nullable()->after('blood_type');
            $table->text('medications')->nullable()->after('allergies');
            $table->text('medical_conditions')->nullable()->after('medications');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'student_id',
                'course',
                'year_level',
                'birth_date',
                'address',
                'emergency_contact_name',
                'emergency_contact_number',
                'emergency_contact_relationship',
                'guardian_name',
                'guardian_contact',
                'blood_type',
                'allergies',
                'medications',
                'medical_conditions'
            ]);
        });
    }
};
