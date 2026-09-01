<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'email_notifications_enabled' => true,
            'backfill_onboarding_emails' => false,
        ];
    }

    public function optedOutOfProductEmail(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_notifications_enabled' => false,
        ]);
    }

    public function markedForOnboardingBackfill(): static
    {
        return $this->state(fn (array $attributes) => [
            'backfill_onboarding_emails' => true,
        ]);
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function verificationGraceExpired(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
            'created_at' => now()->subHours(49),
            'updated_at' => now()->subHours(49),
        ]);
    }
}
