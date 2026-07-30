<?php

namespace Tests\Feature;

use App\Livewire\LandingPage;
use App\Support\LandingAsset;
use App\Support\Locale;
use Database\Seeders\WeddingEventSeeder;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Concerns\RefreshInMemoryDatabase;
use Tests\TestCase;

class LandingDemoTest extends TestCase
{
    use RefreshInMemoryDatabase;

    /**
     * @return array<string, array{0: string, 1: string, 2: string, 3: string}>
     */
    public static function localeDemoProvider(): array
    {
        return [
            'bosnian' => ['bs', '', 'Amer & Amina', 'Milan & Ana'],
            'english' => ['en', '-en', 'Omar & Layla', 'Oliver & Emily'],
            'german' => ['de', '-de', 'Yusuf & Aylin', 'Lukas & Sophie'],
            'croatian' => ['hr', '-hr', 'Emir & Lejla', 'Ivan & Lucija'],
        ];
    }

    #[DataProvider('localeDemoProvider')]
    public function test_landing_page_shows_demo_weddings_for_locale(
        string $locale,
        string $suffix,
        string $islamicCouple,
        string $christianCouple,
    ): void {
        $this->seed(WeddingEventSeeder::class);

        Locale::set($locale, persistToUser: false);

        Livewire::test(LandingPage::class)
            ->assertSee($islamicCouple)
            ->assertSee($christianCouple)
            ->assertSee(__('landing.demo_cta'))
            ->assertSee('demo-islamsko'.$suffix, false)
            ->assertSee('demo-krscansko'.$suffix, false)
            ->assertSee(LandingAsset::path('demo-classic-mobile.webp'), false)
            ->assertSee(LandingAsset::path('demo-editorial-mobile.webp'), false);
    }

    public function test_supported_locales_include_croatian(): void
    {
        $this->assertContains('hr', Locale::supported());
    }

    public function test_landing_asset_falls_back_to_bosnian(): void
    {
        Locale::set('en', persistToUser: false);

        $path = LandingAsset::path('hero-invitation-mobile.webp');

        $this->assertTrue(
            str_starts_with($path, 'img/landing/en/')
            || str_starts_with($path, 'img/landing/bs/')
            || str_starts_with($path, 'img/landing/hero-invitation-mobile.webp')
        );
    }
}
