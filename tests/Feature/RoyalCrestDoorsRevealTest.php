<?php

namespace Tests\Feature;

use App\InvitationReveal;
use App\Models\User;
use App\Models\WeddingEvent;
use Tests\Concerns\RefreshInMemoryDatabase;
use Tests\TestCase;

class RoyalCrestDoorsRevealTest extends TestCase
{
    use RefreshInMemoryDatabase;

    public function test_active_invitation_renders_royal_crest_doors_reveal_stage(): void
    {
        $event = WeddingEvent::factory()->create([
            'is_active' => true,
            'reveal_animation' => InvitationReveal::RoyalCrestDoors,
            'bride_name' => 'Ana',
            'groom_name' => 'Marko',
        ]);

        $this->get(route('invitation.show', $event->slug))
            ->assertOk()
            ->assertSee('crest-photo-stage', false)
            ->assertSee('crest-photo-canvas', false)
            ->assertSee('aspect-ratio: 2 / 3', false)
            ->assertSee('container-type: inline-size', false)
            ->assertSee('min(577px, 100vw, calc(100dvh * 2 / 3))', false)
            ->assertSee('width: 50%', false)
            ->assertSee('left: -100%', false)
            ->assertSee('crest-photo-trigger', false)
            ->assertSee('crest-panels', false)
            ->assertSee('crest-wax-overlay', false)
            ->assertSee('nasdan-royal-crest-doors-closed.webp', false)
            ->assertSee('nasdan-royal-crest-doors-open.webp', false)
            ->assertSee('Marko &amp; Ana', false)
            ->assertSee('id="invitation-content"', false)
            ->assertSee('opacity:0;pointer-events:none', false);
    }

    public function test_preview_invitation_skips_royal_crest_doors_reveal(): void
    {
        $owner = User::factory()->create();
        $event = WeddingEvent::factory()->for($owner)->inactive()->create([
            'reveal_animation' => InvitationReveal::RoyalCrestDoors,
        ]);

        $this->actingAs($owner)
            ->get(route('invitation.show', $event->slug))
            ->assertOk()
            ->assertDontSee('crest-photo-stage', false)
            ->assertDontSee('opacity:0;pointer-events:none', false);
    }
}
