<?php

namespace Database\Factories;

use App\Models\Guest;
use App\Models\WeddingEvent;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Guest>
 */
class GuestFactory extends Factory
{
    protected $model = Guest::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'wedding_event_id' => WeddingEvent::factory(),
            'name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'plus_one_allowed' => false,
            'token' => Str::random(32),
            'labels' => null,
        ];
    }

    /**
     * @param  list<\App\GuestLabel|string>  $labels
     */
    public function withLabels(array $labels): static
    {
        return $this->state(fn (): array => [
            'labels' => $labels,
        ]);
    }
}
