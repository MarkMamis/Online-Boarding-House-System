<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\LandlordProfile;
use App\Models\Property;
use App\Models\Room;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Ensure exactly one admin account exists; do not create more than one.
        if (!User::where('role', 'admin')->exists()) {
            User::create([
                'full_name' => 'System Administrator',
                'name' => 'System Administrator',
                'email' => env('ADMIN_EMAIL', 'admin@example.com'),
                'password' => Hash::make(env('ADMIN_PASSWORD', 'admin12345')),
                'email_verified_at' => now(),
                'contact_number' => 'N/A',
                'boarding_house_name' => 'N/A',
                'role' => 'admin',
            ]);
        }

        // Create landlord profiles for existing landlord users lacking one.
        User::where('role','landlord')
            ->doesntHave('landlordProfile')
            ->get()
            ->each(function(User $landlord){
                LandlordProfile::create([
                    'user_id' => $landlord->id,
                    'contact_number' => $landlord->contact_number,
                    'boarding_house_name' => $landlord->boarding_house_name,
                ]);
            });

        // Create sample landlords and properties for demonstration
        $landlords = User::factory(3)->create(['role' => 'landlord']);
        foreach ($landlords as $landlord) {
            LandlordProfile::create([
                'user_id' => $landlord->id,
                'contact_number' => fake()->phoneNumber(),
                'boarding_house_name' => $landlord->boarding_house_name,
            ]);

            $properties = Property::factory(2)->create(['landlord_id' => $landlord->id]);
            foreach ($properties as $property) {
                Room::factory(3)->create(['property_id' => $property->id]);
            }
        }

        // Optionally generate demo users via factories if needed.
        // User::factory(5)->create();
    }
}
