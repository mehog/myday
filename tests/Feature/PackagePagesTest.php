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
        $this->get('/paketi?locale=en')
            ->assertOk()
            ->assertSee('NašDan digital invitation packages', false)
            ->assertSee(route('packages.show', ['tier' => 'premium']), false)
            ->assertSee('application/ld+json', false)
            ->assertSee('hreflang="bs"', false)
            ->assertSee('hreflang="x-default"', false);
    }

    public function test_each_package_page_is_publicly_accessible(): void
    {
        foreach (['basic', 'plus', 'premium', 'deluxe'] as $tier) {
            $this->get('/paketi/'.$tier.'?locale=en')
                ->assertOk()
                ->assertSee(route('packages.index'), false)
                ->assertSee('Create for free', false)
                ->assertSee('"@type":"Product"', false)
                ->assertSee('"@type":"FAQPage"', false)
                ->assertSee('"@type":"BreadcrumbList"', false);
        }
    }

    public function test_unknown_package_tier_returns_not_found(): void
    {
        $this->get('/paketi/enterprise')->assertNotFound();
    }

    public function test_package_pages_render_in_bosnian_with_bam_prices(): void
    {
        $this->get('/paketi/premium?locale=bs')
            ->assertOk()
            ->assertSee('Premium paket digitalne pozivnice za vjenčanje', false)
            ->assertSee('240 BAM', false)
            ->assertSee('Kreiraj besplatno', false);
    }

    public function test_package_pages_render_in_croatian_and_german(): void
    {
        $this->get('/paketi?locale=hr')
            ->assertOk()
            ->assertSee(__('packages.index.heading', [], 'hr'), false);

        $this->get('/paketi/plus?locale=de')
            ->assertOk()
            ->assertSee(__('packages.tiers.plus.heading', [], 'de'), false)
            ->assertSee('160 EUR', false);
    }

    public function test_sitemap_and_robots_include_package_discovery_rules(): void
    {
        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertSee(route('packages.index'), false)
            ->assertSee(route('packages.show', ['tier' => 'basic']), false)
            ->assertSee(route('packages.show', ['tier' => 'deluxe']), false);

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
            ->assertSee('View package details', false);
    }
}
