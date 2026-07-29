<?php

namespace Database\Factories;

use App\Models\WeddingEvent;
use App\Models\WeddingLocation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WeddingLocation>
 */
class WeddingLocationFactory extends Factory
{
    protected $model = WeddingLocation::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'wedding_event_id' => WeddingEvent::factory(),
            'label' => fake()->optional()->randomElement(['Ceremony', 'Reception', 'Municipality']),
            'name' => fake()->company(),
            'address' => fake()->address(),
            'lat' => fake()->latitude(),
            'lng' => fake()->longitude(),
            'is_primary' => false,
            'sort_order' => 0,
        ];
    }

    public function primary(): static
    {
        return $this->state(fn (): array => [
            'is_primary' => true,
            'sort_order' => 0,
        ]);
    }
}
