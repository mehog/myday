<?php

namespace Tests\Feature;

use App\Models\Guest;
use App\Models\GuestChild;
use App\Models\GuestMessage;
use App\Models\LinkVisit;
use App\Models\User;
use App\Models\WeddingEvent;
use Database\Seeders\MarketingDemoSeeder;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Concerns\RefreshInMemoryDatabase;
use Tests\TestCase;

class MarketingDemoSeederTest extends TestCase
{
    use RefreshInMemoryDatabase;

    /**
     * @return array<string, array{0: string, 1: string, 2: string, 3: string, 4: string}>
     */
    public static function localeProfileProvider(): array
    {
        return [
            'bosnian' => ['bs', 'jasmin-djordje@nasdan.ba', 'jasmina-djordje', 'Anes', 'Jasmina', 'mktbsfeaturedguesttoken000000001'],
            'croatian' => ['hr', 'marketing-hr@nasdan.ba', 'ivan-lucija', 'Ivan', 'Lucija', 'mkthrfeaturedguesttoken000000001'],
            'german' => ['de', 'marketing-de@nasdan.ba', 'lukas-sophie', 'Lukas', 'Sophie', 'mktdefaturedguesttoken000000001'],
            'english' => ['en', 'marketing-en@nasdan.ba', 'oliver-emily', 'Oliver', 'Emily', 'mktenfeaturedguesttoken000000001'],
        ];
    }

    #[DataProvider('localeProfileProvider')]
    public function test_marketing_demo_seeds_complete_localized_account(
        string $locale,
        string $email,
        string $slug,
        string $groom,
        string $bride,
        string $featuredToken,
    ): void {
        $seeder = new MarketingDemoSeeder;
        $seeder->overwrite = true;
        $seeder->onlyLocale = $locale;
        $seeder->run();

        $user = User::query()->where('email', $email)->first();
        $this->assertNotNull($user);
        $this->assertSame($locale, $user->locale);
        $this->assertNotNull($user->email_verified_at);


        $event = WeddingEvent::query()->where('slug', $slug)->first();
        $this->assertNotNull($event);
        $this->assertSame($groom, $event->groom_name);
        $this->assertSame($bride, $event->bride_name);
        $this->assertSame($locale, $event->invitation_locale);
        $this->assertSame(MarketingDemoSeeder::HERO_IMAGE, $event->hero_image);
        $this->assertTrue($event->accommodation_enabled);
        $this->assertGreaterThanOrEqual(2, $event->locations()->count());
        $this->assertGreaterThanOrEqual(3, $event->scheduleItems()->count());
        $this->assertGreaterThanOrEqual(4, $event->menuOptions()->count());
        $this->assertSame(MarketingDemoSeeder::GUEST_COUNT, $event->guests()->count());
        $this->assertSame(MarketingDemoSeeder::MESSAGE_COUNT, GuestMessage::query()->where('wedding_event_id', $event->id)->count());
        $this->assertGreaterThan(0, LinkVisit::query()->where('wedding_event_id', $event->id)->count());
        $this->assertNotEmpty($event->seating_plan['tables'] ?? []);

        $featured = Guest::query()
            ->where('wedding_event_id', $event->id)
            ->where('token', $featuredToken)
            ->first();
        $this->assertNotNull($featured);
        $this->assertSame($locale, $featured->invitation_locale);

        $children = GuestChild::query()
            ->whereIn('guest_id', $event->guests()->pluck('id'))
            ->count();
        $this->assertGreaterThan(0, $children);

        $menuAssignments = Guest::query()
            ->where('wedding_event_id', $event->id)
            ->whereNotNull('menu_option_id')
            ->count();
        $this->assertGreaterThan(0, $menuAssignments);

        $event->guests()->each(function (Guest $guest): void {
            $partySize = 1 + (filled($guest->plus_one_name) ? 1 : 0) + $guest->children()->count();
            $this->assertLessThanOrEqual($partySize, (int) $guest->accommodation_count);
        });

        $this->assertNotEmpty($seeder->seededSummaries);
        $summary = $seeder->seededSummaries[0];
        $this->assertStringNotContainsString('?locale='.$locale.'?locale=', $summary['invitation_url']);
        $this->assertStringContainsString($featuredToken, $summary['featured_guest_url']);
    }
}
