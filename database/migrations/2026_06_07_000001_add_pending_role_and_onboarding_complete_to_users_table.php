<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'onboarding_complete')) {
                $table->boolean('onboarding_complete')
                    ->default(false)
                    ->after('role');
            }
        });

        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'landlord', 'student', 'pending') NOT NULL DEFAULT 'pending'");

        DB::table('users')
            ->where('role', 'admin')
            ->update(['onboarding_complete' => true]);

        DB::table('users')
            ->where('role', 'student')
            ->update([
                'onboarding_complete' => DB::raw("CASE
                    WHEN COALESCE(NULLIF(TRIM(full_name), ''), '') <> ''
                     AND COALESCE(NULLIF(TRIM(profile_image_path), ''), '') <> ''
                     AND COALESCE(NULLIF(TRIM(contact_number), ''), '') <> ''
                     AND COALESCE(NULLIF(TRIM(college), ''), '') <> ''
                     AND COALESCE(NULLIF(TRIM(program), ''), '') <> ''
                     AND COALESCE(NULLIF(TRIM(year_level), ''), '') <> ''
                     AND COALESCE(NULLIF(TRIM(gender), ''), '') <> ''
                     AND COALESCE(NULLIF(TRIM(address), ''), '') <> ''
                     AND COALESCE(NULLIF(TRIM(emergency_contact_name), ''), '') <> ''
                     AND COALESCE(NULLIF(TRIM(emergency_contact_number), ''), '') <> ''
                     AND COALESCE(NULLIF(TRIM(emergency_contact_relationship), ''), '') <> ''
                     AND COALESCE(NULLIF(TRIM(parent_contact_name), ''), '') <> ''
                     AND COALESCE(NULLIF(TRIM(parent_contact_number), ''), '') <> ''
                     AND COALESCE(NULLIF(TRIM(parent_contact_address), ''), '') <> ''
                     AND (
                        (year_level = '1st Year'
                            AND COALESCE(NULLIF(TRIM(enrollment_proof_type), ''), '') <> ''
                            AND COALESCE(NULLIF(TRIM(enrollment_proof_path), ''), '') <> '')
                        OR
                        (year_level <> '1st Year'
                            AND COALESCE(NULLIF(TRIM(student_id), ''), '') <> ''
                            AND COALESCE(NULLIF(TRIM(school_id_path), ''), '') <> '')
                     )
                    THEN 1 ELSE 0 END"),
            ]);

        DB::table('users')
            ->where('role', 'landlord')
            ->update([
                'onboarding_complete' => DB::raw("CASE
                    WHEN COALESCE(NULLIF(TRIM(contact_number), ''), '') <> ''
                     AND COALESCE(NULLIF(TRIM(boarding_house_name), ''), '') <> ''
                     AND EXISTS (
                        SELECT 1
                        FROM landlord_profiles
                        WHERE landlord_profiles.user_id = users.id
                          AND COALESCE(NULLIF(TRIM(landlord_profiles.about), ''), '') <> ''
                          AND landlord_profiles.preferred_payment_methods IS NOT NULL
                          AND landlord_profiles.preferred_payment_methods <> '[]'
                     )
                    THEN 1 ELSE 0 END"),
            ]);
    }

    public function down(): void
    {
        DB::table('users')
            ->where('role', 'pending')
            ->update(['role' => 'student']);

        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'landlord', 'student') NOT NULL DEFAULT 'student'");

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'onboarding_complete')) {
                $table->dropColumn('onboarding_complete');
            }
        });
    }
};
