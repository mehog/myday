<?php

namespace Tests\Feature;

use App\InvitationReveal;
use App\InvitationTemplate;
use App\InvitationTheme;
use App\Models\WeddingEvent;
use Tests\Concerns\RefreshInMemoryDatabase;
use Tests\TestCase;

class DemoInvitationUrlParamsTest extends TestCase
{
    use RefreshInMemoryDatabase;

    public function test_demo_invitation_applies_theme_template_and_reveal_from_query(): void
    {
        $event = WeddingEvent::factory()->create([
            'is_active' => true,
            'is_demo' => true,
            'theme' => InvitationTheme::AmberGold,
            'template' => InvitationTemplate::Classic,
            'reveal_animation' => null,
        ]);

        $this->get(route('invitation.show', [
            'slug' => $event->slug,
            'theme' => 'royal-wedding',
            'template' => 'editorial',
            'reveal' => 'envelope',
        ]))
            ->assertOk()
            ->assertSee('--color-bg: #0f1a2e', false)
            ->assertSee('--color-cta: #d4af37', false)
            ->assertSee('--color-on-cta: #0f1a2e', false)
            ->assertSee('--color-date: #e8d48a', false)
            ->assertSee('editorial-hero', false)
            ->assertSee('env-photo-envelope', false)
            ->assertDontSee('wire:model.live="previewTheme"', false)
            ->assertDontSee(__('invitation.demo_switcher_close'));
    }

    public function test_demo_invitation_reveal_none_disables_animation(): void
    {
        $event = WeddingEvent::factory()->create([
            'is_active' => true,
            'is_demo' => true,
            'theme' => InvitationTheme::AmberGold,
            'template' => InvitationTemplate::Classic,
            'reveal_animation' => InvitationReveal::Envelope,
        ]);

        $this->get(route('invitation.show', [
            'slug' => $event->slug,
            'theme' => 'amber-gold',
            'template' => 'classic',
            'reveal' => 'none',
        ]))
            ->assertOk()
            ->assertDontSee('env-photo-envelope', false)
            ->assertDontSee('opacity:0;pointer-events:none', false);
    }

    public function test_demo_invitation_ignores_invalid_query_params(): void
    {
        $event = WeddingEvent::factory()->create([
            'is_active' => true,
            'is_demo' => true,
            'theme' => InvitationTheme::AmberGold,
            'template' => InvitationTemplate::Classic,
            'reveal_animation' => InvitationReveal::Curtain,
        ]);

        $this->get(route('invitation.show', [
            'slug' => $event->slug,
            'theme' => 'not-a-theme',
            'template' => 'not-a-template',
            'reveal' => 'not-a-reveal',
        ]))
            ->assertOk()
            ->assertSee('--color-bg: #1a1208', false);
    }

    public function test_query_params_win_over_session_preview(): void
    {
        $event = WeddingEvent::factory()->create([
            'is_active' => true,
            'is_demo' => true,
            'theme' => InvitationTheme::AmberGold,
            'template' => InvitationTemplate::Classic,
            'reveal_animation' => null,
        ]);

        session()->put("demo_preview.{$event->id}", [
            'theme' => InvitationTheme::WinterMagic->value,
            'template' => InvitationTemplate::Story->value,
            'reveal' => InvitationReveal::Curtain->value,
        ]);

        $this->get(route('invitation.show', [
            'slug' => $event->slug,
            'theme' => 'dusty-rose',
            'template' => 'editorial',
            'reveal' => 'wax-seal',
        ]))
            ->assertOk()
            ->assertSee('--color-bg: #F9F1EE', false)
            ->assertSee('--color-date: #6B3D38', false)
            ->assertSee('editorial-hero', false)
            ->assertSee('seal-photo-stage', false);
    }

    public function test_non_demo_invitation_ignores_style_query_params(): void
    {
        $event = WeddingEvent::factory()->create([
            'is_active' => true,
            'is_demo' => false,
            'theme' => InvitationTheme::AmberGold,
            'template' => InvitationTemplate::Classic,
        ]);

        $this->get(route('invitation.show', [
            'slug' => $event->slug,
            'theme' => 'royal-wedding',
            'template' => 'editorial',
        ]))
            ->assertOk()
            ->assertSee('--color-bg: #1a1208', false)
            ->assertDontSee('--color-bg: #0f1a2e', false)
            ->assertDontSee('editorial-hero', false);
    }
}
