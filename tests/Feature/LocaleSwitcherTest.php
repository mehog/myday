<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WeddingEvent;
use Tests\Concerns\RefreshInMemoryDatabase;
use Tests\TestCase;

class LocaleSwitcherTest extends TestCase
{
    use RefreshInMemoryDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_locale_picker_updates_locale_query_param_via_navigation(): void
    {
        $this->get('/plans?locale=en')
            ->assertOk()
            ->assertSee('id="locale-picker"', false)
            ->assertSee('window.nasdanSwitchLocale(this.value)', false)
            ->assertDontSee('wire:change="switchLocale', false);
    }

    public function test_package_page_applies_locale_query_and_keeps_selected_option(): void
    {
        $this->get('/plans?locale=de')
            ->assertOk()
            ->assertSee(__('packages.index.heading', [], 'de'), false)
            ->assertSee('value="de" selected', false)
            ->assertDontSee(__('packages.index.heading', [], 'en'), false);
    }

    public function test_lang_switch_redirects_back_with_locale_query_param(): void
    {
        $response = $this
            ->from('/plans?foo=1')
            ->post(route('lang.switch', ['locale' => 'hr']));

        $response->assertRedirect();
        $this->assertStringContainsString('/plans', $response->headers->get('Location'));
        $this->assertStringContainsString('locale=hr', $response->headers->get('Location'));
        $this->assertStringContainsString('foo=1', $response->headers->get('Location'));
        $this->assertSame('hr', session('locale'));
    }

    public function test_lang_switch_from_invitation_does_not_persist_to_user_profile(): void
    {
        $owner = User::factory()->create(['locale' => 'en']);
        $event = WeddingEvent::factory()->for($owner)->create([
            'invitation_locale' => 'bs',
            'is_active' => true,
            'slug' => 'locale-switch-invite',
        ]);

        $this->actingAs($owner)
            ->from('/e/'.$event->slug)
            ->post(route('lang.switch', ['locale' => 'de']))
            ->assertRedirect();

        $this->assertSame('en', $owner->fresh()->locale);
        $this->assertSame('de', session('locale'));
    }

    public function test_lang_switch_on_marketing_page_persists_authenticated_user_locale(): void
    {
        $user = User::factory()->create(['locale' => 'en']);

        $this->actingAs($user)
            ->from('/plans')
            ->post(route('lang.switch', ['locale' => 'bs']))
            ->assertRedirect();

        $this->assertSame('bs', $user->fresh()->locale);
        $this->assertSame('bs', session('locale'));
    }

    public function test_first_visit_defaults_to_english_without_accept_language(): void
    {
        $this->get('/plans')
            ->assertOk()
            ->assertSee(__('packages.index.heading', [], 'en'), false)
            ->assertSee('hreflang="x-default"', false)
            ->assertSee('locale=en', false)
            ->assertDontSee(__('packages.index.heading', [], 'bs'), false);
    }

    public function test_first_visit_uses_accept_language_when_supported(): void
    {
        $this->withHeader('Accept-Language', 'hr-HR,hr;q=0.9,en;q=0.8')
            ->get('/plans')
            ->assertOk()
            ->assertSee(__('packages.index.heading', [], 'hr'), false)
            ->assertSee('value="hr" selected', false)
            ->assertDontSee(__('packages.index.heading', [], 'en'), false);
    }

    public function test_query_locale_wins_over_accept_language(): void
    {
        $this->withHeader('Accept-Language', 'de-DE,de;q=0.9')
            ->get('/plans?locale=bs')
            ->assertOk()
            ->assertSee(__('packages.index.heading', [], 'bs'), false)
            ->assertSee('value="bs" selected', false);
    }

    public function test_unsupported_accept_language_falls_back_to_english(): void
    {
        $this->withHeader('Accept-Language', 'fr-FR,fr;q=0.9')
            ->get('/plans')
            ->assertOk()
            ->assertSee(__('packages.index.heading', [], 'en'), false)
            ->assertDontSee(__('packages.index.heading', [], 'bs'), false);
    }

    public function test_first_visit_maps_serbian_accept_language_to_latin(): void
    {
        $this->withHeader('Accept-Language', 'sr-RS,sr;q=0.9,en;q=0.8')
            ->get('/plans')
            ->assertOk()
            ->assertSee('value="sr_Latn" selected', false)
            ->assertSee('hreflang="sr-Latn"', false)
            ->assertSee('lang="sr-Latn"', false)
            ->assertSee(__('packages.index.compare_subheading', [], 'sr_Latn'), false);
    }

    public function test_first_visit_maps_sr_latn_accept_language(): void
    {
        $this->withHeader('Accept-Language', 'sr-Latn,sr;q=0.9')
            ->get('/plans')
            ->assertOk()
            ->assertSee('value="sr_Latn" selected', false)
            ->assertSee(__('packages.index.compare_subheading', [], 'sr_Latn'), false);
    }

    public function test_query_locale_sr_canonicalizes_to_sr_latn(): void
    {
        $this->get('/plans?locale=sr')
            ->assertOk()
            ->assertSee('value="sr_Latn" selected', false)
            ->assertSee(__('packages.index.compare_subheading', [], 'sr_Latn'), false);

        $this->assertSame('sr_Latn', session('locale'));
    }
}
