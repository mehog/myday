<?php

namespace Tests\Feature;

use App\InvitationReveal;
use App\InvitationTemplate;
use App\InvitationTheme;
use App\Models\Guest;
use App\Models\WeddingEvent;
use Tests\Concerns\RefreshInMemoryDatabase;
use Tests\TestCase;

class DemoConversionCtaTest extends TestCase
{
    use RefreshInMemoryDatabase;

    public function test_demo_invitation_shows_create_sticky_instead_of_rsvp_nudge(): void
    {
        $event = WeddingEvent::factory()->create([
            'is_active' => true,
            'is_demo' => true,
            'theme' => InvitationTheme::AmberGold,
            'template' => InvitationTemplate::Classic,
            'reveal_animation' => InvitationReveal::Envelope,
        ]);

        $guest = Guest::factory()->for($event)->create();

        $this->get(route('invitation.guest', [
            'slug' => $event->slug,
            'token' => $guest->token,
            'theme' => 'dusty-rose',
            'template' => 'editorial',
            'reveal' => 'wax-seal',
        ]))
            ->assertOk()
            ->assertSee('data-demo-create-sticky', false)
            ->assertSee(__('invitation.demo_create_sticky_text'))
            ->assertSee(__('invitation.demo_create_sticky_cta'))
            ->assertSee('/onboarding?', false)
            ->assertSee('theme=dusty-rose', false)
            ->assertSee('template=editorial', false)
            ->assertSee('reveal=wax-seal', false)
            ->assertDontSee(__('invitation.rsvp_nudge_sticky_text'))
            ->assertDontSee(__('invitation.hero_rsvp_question', [
                'name' => explode(' ', trim($guest->name), 2)[0],
            ]));
    }

    public function test_public_invitation_does_not_show_hero_rsvp_buttons(): void
    {
        $event = WeddingEvent::factory()->create([
            'is_active' => true,
            'is_demo' => false,
            'theme' => InvitationTheme::AmberGold,
            'template' => InvitationTemplate::Classic,
        ]);

        Guest::factory()->for($event)->create(['name' => 'James Thompson']);

        $this->get(route('invitation.show', ['slug' => $event->slug]))
            ->assertOk()
            ->assertDontSee(__('invitation.hero_rsvp_question', ['name' => 'James']));
    }

    public function test_non_demo_personal_link_still_shows_rsvp_sticky_nudge(): void
    {
        $event = WeddingEvent::factory()->create([
            'is_active' => true,
            'is_demo' => false,
            'theme' => InvitationTheme::AmberGold,
            'template' => InvitationTemplate::Classic,
        ]);

        $guest = Guest::factory()->for($event)->create();

        $this->get(route('invitation.guest', [
            'slug' => $event->slug,
            'token' => $guest->token,
        ]))
            ->assertOk()
            ->assertSee(__('invitation.rsvp_nudge_sticky_text'))
            ->assertSee(__('invitation.hero_rsvp_question', [
                'name' => explode(' ', trim($guest->name), 2)[0],
            ]))
            ->assertSee(__('invitation.yes_attending'))
            ->assertDontSee('data-demo-create-sticky', false)
            ->assertDontSee(__('invitation.demo_create_sticky_text'));
    }
}
