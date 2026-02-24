<?php

namespace Database\Factories;

use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Property>
 */
class PropertyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $ratingsCount = fake()->numberBetween(0, 250);

        return [
            'landlord_id' => User::factory()->state(['role' => 'landlord']),
            'name' => fake()->company() . ' Boarding House',
            'address' => fake()->address(),
            'description' => fake()->paragraph(),
            'price_min' => fake()->numberBetween(1000, 3000),
            'price_max' => fake()->numberBetween(3000, 8000),
            'latitude' => fake()->latitude(),
            'longitude' => fake()->longitude(),
            'average_rating' => $ratingsCount === 0 ? 0 : fake()->randomFloat(1, 3.8, 5.0),
            'ratings_count' => $ratingsCount,
        ];
    }
}