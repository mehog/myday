<?php

namespace Tests\Feature;

use App\InvitationReveal;
use App\Models\User;
use App\Models\WeddingEvent;
use Tests\TestCase;
use Tests\Concerns\RefreshInMemoryDatabase;

class WaxSealRevealTest extends TestCase
{
    use RefreshInMemoryDatabase;

    public function test_active_invitation_renders_wax_seal_reveal_stage(): void
    {
        $event = WeddingEvent::factory()->create([
            'is_active' => true,
            'reveal_animation' => InvitationReveal::WaxSeal,
            'bride_name' => 'Ana',
            'groom_name' => 'Marko',
        ]);

        $this->get(route('invitation.show', $event->slug))
            ->assertOk()
            ->assertSee('seal-photo-stage', false)
            ->assertSee('seal-photo-canvas', false)
            ->assertSee('aspect-ratio: 9 / 16', false)
            ->assertSee('min(577px, 100vw, calc(100dvh * 9 / 16))', false)
            ->assertSee('seal-photo-trigger', false)
            ->assertSee('nasdan-wax-seal-closed.webp', false)
            ->assertSee('nasdan-wax-seal-open.webp', false)
            ->assertSee('seal-wax-overlay', false)
            ->assertSee('Marko &amp; Ana', false)
            ->assertSee('id="invitation-content"', false)
            ->assertSee('opacity:0;pointer-events:none', false);
    }

    public function test_preview_invitation_skips_wax_seal_reveal(): void
    {
        $owner = User::factory()->create();
        $event = WeddingEvent::factory()->for($owner)->inactive()->create([
            'reveal_animation' => InvitationReveal::WaxSeal,
        ]);

        $this->actingAs($owner)
            ->get(route('invitation.show', $event->slug))
            ->assertOk()
            ->assertDontSee('seal-photo-stage', false)
            ->assertDontSee('opacity:0;pointer-events:none', false);
    }
}
