<?php

namespace Tests\Feature;

use App\Livewire\LandingPage;
use App\Livewire\StartLandingPage;
use App\Support\Locale;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Concerns\RefreshInMemoryDatabase;
use Tests\TestCase;

class StartLandingPageTest extends TestCase
{
    use RefreshInMemoryDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function startLocaleProvider(): array
    {
        return [
            'english' => ['en'],
            'bosnian' => ['bs'],
            'german' => ['de'],
            'croatian' => ['hr'],
            'serbian' => ['sr_Latn'],
        ];
    }

    public function test_start_page_is_a_sales_variant_with_reviews(): void
    {
        $this->get('/start')
            ->assertOk()
            ->assertSee(config('app.name'), false)
            ->assertSee(__('start.hero_title_emphasis'), false)
            ->assertSee(__('start.hero_subtitle'), false)
            ->assertSee(__('start.empathy_title_emphasis'), false)
            ->assertSee(__('start.future_title_emphasis'), false)
            ->assertSee(__('start.pricing_title'), false)
            ->assertSee(__('start.pricing_plan_plus_name'), false)
            ->assertSee(__('start.faq_title'), false)
            ->assertSee(__('start.faq_q1'), false)
            ->assertSee(__('start.guarantee_text', [
                'days' => config('legal.refund_window_days'),
            ]), false)
            ->assertSee(__('start.testimonial_1_quote'), false)
            ->assertSee(__('start.testimonial_2_quote'), false)
            ->assertSee(__('start.testimonial_3_quote'), false)
            ->assertDontSee(__('start.testimonials_empty'), false)
            ->assertSee('noindex, nofollow', false)
            ->assertSee('rel="canonical"', false)
            ->assertSee(url('/start'), false)
            ->assertSee('hreflang="x-default"', false)
            ->assertDontSee(__('landing.hero_title_emphasis'), false);

        $this->assertNull(session('locale'));
    }

    public function test_start_route_name_points_to_the_sales_page(): void
    {
        $this->get(route('start'))
            ->assertOk()
            ->assertSee(__('start.hero_title_lead'), false);
    }

    public function test_homepage_control_is_unchanged(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee(__('landing.hero_title_emphasis'), false)
            ->assertSee(__('landing.hero_title_lead'), false)
            ->assertDontSee(__('start.hero_title_emphasis'), false)
            ->assertDontSee('noindex, nofollow', false);

        Livewire::test(LandingPage::class)
            ->assertSee(__('landing.hero_title_emphasis'))
            ->assertSee(__('landing.trust_free'));
    }

    public function test_visiting_start_without_locale_query_does_not_persist_locale(): void
    {
        $this->assertNull(session('locale'));

        $this->withSession(['locale' => 'de'])
            ->get('/start')
            ->assertOk()
            ->assertSee(__('start.hero_title_emphasis', [], 'de'), false)
            ->assertSee(__('start.testimonial_1_quote', [], 'de'), false);

        $this->assertSame('de', session('locale'));
    }

    #[DataProvider('startLocaleProvider')]
    public function test_start_page_shows_translated_copy_and_reviews(string $locale): void
    {
        $this->get('/start?locale='.$locale)
            ->assertOk()
            ->assertSee(__('start.hero_title_emphasis', [], $locale), false)
            ->assertSee(__('start.testimonial_1_quote', [], $locale), false)
            ->assertSee(__('start.testimonial_2_quote', [], $locale), false)
            ->assertSee(__('start.testimonial_3_quote', [], $locale), false)
            ->assertSee(__('start.faq_q3', [], $locale), false)
            ->assertSee('id="locale-picker"', false);
    }

    public function test_start_livewire_page_follows_current_locale(): void
    {
        Locale::set('hr', persistToUser: false);

        Livewire::test(StartLandingPage::class)
            ->assertSee(__('start.hero_title_emphasis', [], 'hr'))
            ->assertSee(__('start.testimonial_3_quote', [], 'hr'))
            ->assertDontSee(__('landing.hero_title_emphasis', [], 'hr'));

        $this->assertSame('hr', session('locale'));
    }
}
