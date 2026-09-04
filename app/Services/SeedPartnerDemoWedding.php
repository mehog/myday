<?php

namespace App\Services;

use App\InvitationTemplate;
use App\InvitationTheme;
use App\LinkMode;
use App\Models\Guest;
use App\Models\User;
use App\Models\WeddingEvent;
use App\PlanTier;
use App\RsvpStatus;
use App\Support\Locale;
use Database\Seeders\MarketingDemoSeeder;
use Illuminate\Support\Str;

class SeedPartnerDemoWedding
{
    public const GUEST_COUNT = 10;

    public function __construct(
        private readonly EnsureWeddingMenuOptions $ensureWeddingMenuOptions,
    ) {}

    public function handle(User $user): ?WeddingEvent
    {
        if ($user->ownedWeddingEvent !== null) {
            return null;
        }

        $groom = 'Marko';
        $bride = 'Ana';
        $slug = $this->uniqueSlug("partner-demo-{$user->id}");

        $event = WeddingEvent::query()->create([
            'user_id' => $user->id,
            'slug' => $slug,
            'bride_name' => $bride,
            'groom_name' => $groom,
            'wedding_date' => now()->addMonths(4)->setTime(16, 0),
            'location_name' => 'Garden Venue',
            'location_address' => 'City Center',
            'theme' => InvitationTheme::AmberGold,
            'template' => InvitationTemplate::Classic,
            'link_mode' => LinkMode::Public,
            'hero_image' => MarketingDemoSeeder::HERO_IMAGE,
            'rsvp_deadline' => now()->addMonths(3)->toDateString(),
            'accommodation_enabled' => true,
            'is_active' => true,
            'is_demo' => false,
            'invitation_locale' => Locale::resolve($user->locale),
        ]);

        $event->applyPlanTier(PlanTier::Premium);

        $this->ensureWeddingMenuOptions->handle($event);

        $event->locations()->create([
            'label' => null,
            'name' => 'Garden Venue',
            'address' => 'City Center',
            'is_primary' => true,
            'sort_order' => 0,
        ]);

        $event->scheduleItems()->createMany([
            [
                'time' => '14:00',
                'title' => 'Guest arrival',
                'description' => 'Welcome drinks.',
                'sort_order' => 1,
            ],
            [
                'time' => '15:00',
                'title' => 'Ceremony',
                'description' => 'Wedding ceremony.',
                'sort_order' => 2,
            ],
            [
                'time' => '17:00',
                'title' => 'Reception',
                'description' => 'Dinner, dancing, and celebration.',
                'sort_order' => 3,
            ],
        ]);

        $this->seedGuests($event);

        return $event->fresh(['guests', 'scheduleItems', 'locations']);
    }

    private function seedGuests(WeddingEvent $event): void
    {
        $statuses = [
            RsvpStatus::Yes,
            RsvpStatus::Yes,
            RsvpStatus::Yes,
            RsvpStatus::Yes,
            RsvpStatus::Yes,
            RsvpStatus::No,
            RsvpStatus::No,
            null,
            null,
            null,
        ];

        foreach ($statuses as $index => $status) {
            $attributes = [
                'wedding_event_id' => $event->id,
                'name' => 'Guest '.($index + 1),
                'email' => "partner-demo-guest-{$event->id}-{$index}@example.com",
                'plus_one_allowed' => $index % 3 === 0,
                'token' => Str::random(32),
                'rsvp_status' => $status,
                'rsvp_responded_at' => $status !== null ? now()->subDays($index + 1) : null,
            ];

            Guest::query()->create($attributes);
        }
    }

    private function uniqueSlug(string $base): string
    {
        $slug = $base;

        if (! WeddingEvent::query()->where('slug', $slug)->exists()) {
            return $slug;
        }

        return $base.'-'.Str::lower(Str::random(4));
    }
}
