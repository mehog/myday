<?php

namespace Tests\Feature;

use Tests\Concerns\RefreshInMemoryDatabase;
use Tests\TestCase;

class PackagePagesTest extends TestCase
{
    use RefreshInMemoryDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_packages_index_is_publicly_accessible(): void
    {
        $this->get('/plans?locale=en')
            ->assertOk()
            ->assertSee('Nuptoria plans', false)
            ->assertSee(route('packages.show', ['tier' => 'free']), false)
            ->assertSee(route('packages.show', ['tier' => 'premium']), false)
            ->assertSee('application/ld+json', false)
            ->assertSee('hreflang="bs"', false)
            ->assertSee('hreflang="x-default"', false);
    }

    public function test_each_package_page_is_publicly_accessible(): void
    {
        foreach (['free', 'basic', 'plus', 'premium'] as $tier) {
            $this->get('/plans/'.$tier.'?locale=en')
                ->assertOk()
                ->assertSee(route('packages.index'), false)
                ->assertSee('Create for free', false)
                ->assertSee('"@type":"Product"', false)
                ->assertSee('"@type":"FAQPage"', false)
                ->assertSee('"@type":"BreadcrumbList"', false);
        }
    }

    public function test_deluxe_package_page_returns_not_found(): void
    {
        $this->get('/plans/deluxe')->assertNotFound();
    }

    public function test_unknown_package_tier_returns_not_found(): void
    {
        $this->get('/plans/enterprise')->assertNotFound();
    }

    public function test_package_pages_render_in_bosnian_with_eur_prices_by_default(): void
    {
        $this->get('/plans/premium?locale=bs')
            ->assertOk()
            ->assertSee('Premium paket za vjenčanje', false)
            ->assertSee('240 EUR', false)
            ->assertDontSee('240 BAM', false)
            ->assertSee('Kreiraj besplatno', false);
    }

    public function test_package_pages_show_bam_prices_for_bosnia_visitors_regardless_of_locale(): void
    {
        $this->fakeVisitorCountry('BA');

        $this->get('/plans/premium?locale=en')
            ->assertOk()
            ->assertSee('240 BAM', false)
            ->assertDontSee('240 EUR', false);
    }

    public function test_package_pages_show_eur_for_non_bosnia_visitors_in_bosnian(): void
    {
        $this->fakeVisitorCountry('DE');

        $this->get('/plans?locale=bs')
            ->assertOk()
            ->assertSee('80 EUR', false)
            ->assertDontSee('80 BAM', false);
    }

    public function test_package_pages_render_in_croatian_and_german(): void
    {
        $this->get('/plans?locale=hr')
            ->assertOk()
            ->assertSee(__('packages.index.heading', [], 'hr'), false);

        $this->get('/plans/plus?locale=de')
            ->assertOk()
            ->assertSee(__('packages.tiers.plus.heading', [], 'de'), false)
            ->assertSee('160 EUR', false)
            ->assertSee('Plus-Hochzeitsplan', false);
    }

    public function test_sitemap_and_robots_include_package_discovery_rules(): void
    {
        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertSee(route('packages.index'), false)
            ->assertSee(route('packages.show', ['tier' => 'basic']), false)
            ->assertSee(route('packages.show', ['tier' => 'free']), false)
            ->assertDontSee(route('packages.show', ['tier' => 'deluxe']), false);

        $robots = $this->get('/robots.txt')->assertOk()->getContent();

        $this->assertStringContainsString("User-agent: OAI-SearchBot\nAllow: /", $robots);
        $this->assertStringContainsString("User-agent: GPTBot\nDisallow: /", $robots);
    }

    public function test_homepage_and_footer_link_to_package_pages(): void
    {
        $this->get('/?locale=en')
            ->assertOk()
            ->assertSee(route('packages.index'), false)
            ->assertSee(route('packages.show', ['tier' => 'plus']), false)
            ->assertSee('View plan details', false)
            ->assertSee('80 EUR', false);
    }

    public function test_homepage_shows_bam_prices_for_bosnia_visitors(): void
    {
        $this->fakeVisitorCountry('BA');

        $this->get('/?locale=en')
            ->assertOk()
            ->assertSee('80 BAM', false)
            ->assertDontSee('80 EUR', false);
    }
}
