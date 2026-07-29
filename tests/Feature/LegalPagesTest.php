<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WeddingEvent;
use Tests\Concerns\RefreshInMemoryDatabase;
use Tests\TestCase;

class LegalPagesTest extends TestCase
{
    use RefreshInMemoryDatabase;

    public function test_legal_pages_are_publicly_accessible(): void
    {
        foreach ([
            '/terms' => 'Terms of Service',
            '/privacy' => 'Privacy Policy',
            '/refund-policy' => 'Refund Policy',
            '/faq' => 'Frequently asked questions',
        ] as $path => $heading) {
            $this->get($path.'?locale=en')
                ->assertOk()
                ->assertSee($heading, false)
                ->assertSee(route('legal.terms'), false)
                ->assertSee(config('legal.support_email'), false)
                ->assertDontSee('[OPERATOR', false)
                ->assertDontSee('REPLACE]', false);
        }
    }

    public function test_activation_copy_does_not_hedge_with_usually(): void
    {
        $this->get('/faq?locale=en')
            ->assertOk()
            ->assertSee('your plan is activated automatically', false)
            ->assertDontSee('typically activated', false);

        $this->get('/faq?locale=bs')
            ->assertOk()
            ->assertSee('plan se aktivira automatski', false)
            ->assertDontSee('obično aktivira', false);

        $this->get('/faq?locale=de')
            ->assertOk()
            ->assertSee('wird der Tarif automatisch aktiviert', false)
            ->assertDontSee('typischerweise automatisch', false);

        $this->get('/faq?locale=hr')
            ->assertOk()
            ->assertSee('plan se aktivira automatski', false)
            ->assertDontSee('obično aktivira', false);
    }

    public function test_footer_and_homepage_expose_compliance_links_and_pricing(): void
    {
        $this->get('/?locale=en')
            ->assertOk()
            ->assertSee(route('legal.terms'), false)
            ->assertSee(route('legal.privacy'), false)
            ->assertSee(route('legal.refund'), false)
            ->assertSee(route('legal.faq'), false)
            ->assertSee('id="cijene"', false)
            ->assertSee('One-time payment', false)
            ->assertSee('After payment is confirmed', false);
    }

    public function test_sitemap_includes_legal_pages(): void
    {
        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertSee(route('legal.terms'), false)
            ->assertSee(route('legal.privacy'), false)
            ->assertSee(route('legal.refund'), false)
            ->assertSee(route('legal.faq'), false);
    }

    public function test_legal_pages_render_in_bosnian_locale(): void
    {
        $this->get('/terms?locale=bs')
            ->assertOk()
            ->assertSee('Uslovi korištenja', false);
    }

    public function test_legal_pages_render_in_croatian_locale(): void
    {
        $this->get('/terms?locale=hr')
            ->assertOk()
            ->assertSee(__('legal.terms.heading', [], 'hr'), false);
    }

    public function test_pricing_page_shows_merchant_of_record_and_policy_links(): void
    {
        $user = User::factory()->create([
            'locale' => 'en',
            'signup_ipstack' => (object) ['country_code' => 'BA'],
        ]);
        WeddingEvent::factory()->inactive()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->get('/app/pricing')
            ->assertOk()
            ->assertSee('Merchant of Record', false)
            ->assertSee(route('legal.terms'), false)
            ->assertSee(route('legal.refund'), false)
            ->assertSee(route('legal.faq'), false);
    }
}
