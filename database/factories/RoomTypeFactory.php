<?php

namespace Database\Factories;

use App\Models\RoomType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RoomType>
 */
class RoomTypeFactory extends Factory
{
    protected $model = RoomType::class;

    public function definition(): array
    {
        return [
            'hotel_id' => \App\Models\Hotel::factory(),
            'name' => fake()->randomElement(['Simple', 'Double', 'Suite']),
            'price' => fake()->numberBetween(5000, 50000),
            'capacity' => fake()->numberBetween(1, 4),
            'is_available' => true,
        ];
    }
}
