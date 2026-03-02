<?php

namespace Database\Factories;

use App\Models\Room;
use App\Models\RoomType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Room>
 */
class RoomFactory extends Factory
{
    protected $model = Room::class;

    public function definition(): array
    {
        return [
            'hotel_id' => \App\Models\Hotel::factory(),
            'room_type_id' => RoomType::factory(),
            'room_number' => (string) fake()->unique()->numberBetween(100, 999),
            'floor' => fake()->numberBetween(0, 5),
            'status' => 'available',
            'occupation_state' => 'free',
            'cleaning_state' => 'none',
            'technical_state' => 'normal',
        ];
    }
}
