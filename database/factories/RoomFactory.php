<?php

namespace Database\Factories;

use App\Models\Property;
use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Room>
 */
class RoomFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'property_id' => Property::factory(),
            'room_number' => fake()->numberBetween(1, 100),
            'capacity' => fake()->numberBetween(1, 4),
            'price' => fake()->numberBetween(1500, 5000),
            'status' => fake()->randomElement(['available', 'occupied', 'maintenance']),
        ];
    }
}