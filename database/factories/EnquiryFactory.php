<?php

namespace Database\Factories;

use App\InvitationTheme;
use App\Models\Enquiry;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Enquiry>
 */
class EnquiryFactory extends Factory
{
    protected $model = Enquiry::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'groom_name' => fake()->firstNameMale(),
            'bride_name' => fake()->firstNameFemale(),
            'wedding_date' => now()->addMonths(4)->toDateString(),
            'theme' => InvitationTheme::AmberGold,
            'notes' => fake()->sentence(),
        ];
    }
}
