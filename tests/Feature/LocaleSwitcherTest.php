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
}
