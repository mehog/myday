<?php

namespace Tests\Feature;

use App\Livewire\DemoExamplesPage;
use App\Livewire\LandingPage;
use App\Models\WeddingEvent;
use App\Support\DemoInvitationExamples;
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
     * @return array<string, array{0: string, 1: string}>
     */
    public static function localeDemoProvider(): array
    {
        return [
            'bosnian' => ['bs', ''],
            'english' => ['en', '-en'],
            'german' => ['de', '-de'],
            'croatian' => ['hr', '-hr'],
            'serbian' => ['sr_Latn', '-sr'],
        ];
    }

    #[DataProvider('localeDemoProvider')]
    public function test_landing_page_shows_featured_demo_examples_for_locale(
        string $locale,
        string $suffix,
    ): void {
        $this->seed(WeddingEventSeeder::class);

        Locale::set($locale, persistToUser: false);

        $slug = 'demo-islamsko'.$suffix;
        $guestToken = WeddingEvent::query()
            ->where('slug', $slug)
            ->firstOrFail()
            ->guests()
            ->value('token');

        $featured = DemoInvitationExamples::featured();
        $firstTitle = DemoInvitationExamples::title($featured[0]);

        Livewire::test(LandingPage::class)
            ->assertSee(__('landing.hero_title_emphasis'))
            ->assertSee(__('landing.trust_free'))
            ->assertSee(__('landing.value_1_text'))
            ->assertSee(__('landing.pillar_1_title'))
            ->assertSee(__('landing.story_invite_eyebrow'))
            ->assertSee(__('landing.step_4_title'))
            ->assertSee(__('landing.cta_title_emphasis'))
            ->assertSee(__('landing.mock_getting_married'))
            ->assertSee(__('landing.demo_title'))
            ->assertSee(__('landing.demo_show_all'))
            ->assertSee($firstTitle)
            ->assertSee('/e/'.$slug.'/'.$guestToken, false)
            ->assertSee('theme=amber-gold', false)
            ->assertSee('template=classic', false)
            ->assertSee('reveal=none', false)
            ->assertSee('landing-demo-slider', false)
            ->assertSee('invitation-preview-open', false)
            ->assertDontSee('rel="noopener noreferrer"', false)
            ->assertDontSee('demo-krscansko'.$suffix, false);
    }

    public function test_demo_examples_gallery_lists_twenty_examples(): void
    {
        $this->seed(WeddingEventSeeder::class);

        Locale::set('en', persistToUser: false);

        $gallery = DemoInvitationExamples::gallery();
        $this->assertCount(20, $gallery);

        $guestToken = WeddingEvent::query()
            ->where('slug', 'demo-islamsko-en')
            ->firstOrFail()
            ->guests()
            ->value('token');

        Livewire::test(DemoExamplesPage::class)
            ->assertSee(__('landing.demo_gallery_title'))
            ->assertSee(DemoInvitationExamples::title($gallery[0]))
            ->assertSee(DemoInvitationExamples::title($gallery[19]))
            ->assertSee('/e/demo-islamsko-en/'.$guestToken, false)
            ->assertSee('landing-demo-grid', false)
            ->assertSee('invitation-preview-open', false)
            ->assertDontSee('rel="noopener noreferrer"', false)
            ->assertDontSee('landing-demo-slider', false);
    }

    public function test_demo_examples_route_is_reachable(): void
    {
        $this->get(route('demo.examples'))
            ->assertOk()
            ->assertSee(__('landing.demo_gallery_title'));
    }

    public function test_supported_locales_include_croatian(): void
    {
        $this->assertContains('hr', Locale::supported());
    }

    public function test_supported_locales_include_serbian_latin(): void
    {
        $this->assertContains('sr_Latn', Locale::supported());
        $this->assertSame('Srpski (latinica)', Locale::options()['sr_Latn'] ?? null);
    }

    public function test_demo_slug_maps_serbian_latin_to_sr_suffix(): void
    {
        $this->assertSame('demo-islamsko-sr', DemoInvitationExamples::demoSlug('sr_Latn'));
        $this->assertSame('demo-islamsko-sr', DemoInvitationExamples::demoSlug('sr'));
        $this->assertSame('demo-islamsko', DemoInvitationExamples::demoSlug('bs'));
        $this->assertSame('demo-islamsko-en', DemoInvitationExamples::demoSlug('en'));
    }
}
