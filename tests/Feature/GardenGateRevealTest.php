<?php

namespace Tests\Feature;

use App\InvitationReveal;
use App\Models\User;
use App\Models\WeddingEvent;
use Tests\TestCase;
use Tests\Concerns\RefreshInMemoryDatabase;

class GardenGateRevealTest extends TestCase
{
    use RefreshInMemoryDatabase;

    public function test_active_invitation_renders_garden_gate_reveal_stage(): void
    {
        $event = WeddingEvent::factory()->create([
            'is_active' => true,
            'reveal_animation' => InvitationReveal::GardenGate,
            'bride_name' => 'Ana',
            'groom_name' => 'Marko',
        ]);

        $this->get(route('invitation.show', $event->slug))
            ->assertOk()
            ->assertSee('gate-photo-stage', false)
            ->assertSee('gate-photo-canvas', false)
            ->assertSee('aspect-ratio: 2 / 3', false)
            ->assertSee('min(577px, 100vw, calc(100dvh * 2 / 3))', false)
            ->assertSee('width: 50%', false)
            ->assertSee('left: -100%', false)
            ->assertSee('gate-photo-trigger', false)
            ->assertSee('gate-panels', false)
            ->assertSee('nasdan-garden-gate-closed.webp', false)
            ->assertSee('nasdan-garden-gate-open.webp', false)
            ->assertSee('Marko &amp; Ana', false)
            ->assertSee('id="invitation-content"', false)
            ->assertSee('opacity:0;pointer-events:none', false);
    }

    public function test_preview_invitation_skips_garden_gate_reveal(): void
    {
        $owner = User::factory()->create();
        $event = WeddingEvent::factory()->for($owner)->inactive()->create([
            'reveal_animation' => InvitationReveal::GardenGate,
        ]);

        $this->actingAs($owner)
            ->get(route('invitation.show', $event->slug))
            ->assertOk()
            ->assertDontSee('gate-photo-stage', false)
            ->assertDontSee('opacity:0;pointer-events:none', false);
    }
}
