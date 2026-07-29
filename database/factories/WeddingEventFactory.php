<?php

namespace Database\Factories;

use App\InvitationTemplate;
use App\InvitationTheme;
use App\LinkMode;
use App\Models\User;
use App\Models\WeddingEvent;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<WeddingEvent>
 */
class WeddingEventFactory extends Factory
{
    protected $model = WeddingEvent::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $groom = fake()->firstNameMale();
        $bride = fake()->firstNameFemale();

        return [
            'user_id' => User::factory(),
            'slug' => Str::slug("{$groom}-{$bride}-".fake()->unique()->numerify('###')),
            'bride_name' => $bride,
            'groom_name' => $groom,
            'wedding_date' => now()->addMonths(3)->setTime(16, 0),
            'location_name' => fake()->company(),
            'location_address' => fake()->address(),
            'theme' => InvitationTheme::AmberGold,
            'template' => InvitationTemplate::Classic,
            'link_mode' => LinkMode::Public,
            'rsvp_deadline' => now()->addMonths(2)->toDateString(),
            'is_active' => true,
            'is_demo' => false,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => [
            'is_active' => false,
        ]);
    }

    public function configure(): static
    {
        return $this->afterCreating(function (WeddingEvent $event): void {
            if ($event->locations()->exists()) {
                return;
            }

            $hasLocation = filled($event->location_name)
                || filled($event->location_address)
                || $event->location_lat !== null
                || $event->location_lng !== null;

            if (! $hasLocation) {
                return;
            }

            $event->locations()->create([
                'label' => null,
                'name' => $event->location_name,
                'address' => $event->location_address,
                'lat' => $event->location_lat,
                'lng' => $event->location_lng,
                'is_primary' => true,
                'sort_order' => 0,
            ]);
        });
    }
}
