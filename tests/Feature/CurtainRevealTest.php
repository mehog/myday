<?php

namespace Tests\Feature;

use App\InvitationReveal;
use App\Models\User;
use App\Models\WeddingEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CurtainRevealTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_invitation_renders_curtain_reveal_stage(): void
    {
        $event = WeddingEvent::factory()->create([
            'is_active' => true,
            'reveal_animation' => InvitationReveal::Curtain,
            'bride_name' => 'Ana',
            'groom_name' => 'Marko',
        ]);

        $this->get(route('invitation.show', $event->slug))
            ->assertOk()
            ->assertSee('curtain-photo-stage', false)
            ->assertSee('curtain-photo-trigger', false)
            ->assertSee('nasdan-curtain-closed.webp', false)
            ->assertSee('nasdan-curtain-open.webp', false)
            ->assertSee('Marko &amp; Ana', false)
            ->assertSee('id="invitation-content"', false)
            ->assertSee('opacity:0;pointer-events:none', false);
    }

    public function test_preview_invitation_skips_curtain_reveal(): void
    {
        $owner = User::factory()->create();
        $event = WeddingEvent::factory()->for($owner)->inactive()->create([
            'reveal_animation' => InvitationReveal::Curtain,
        ]);

        $this->actingAs($owner)
            ->get(route('invitation.show', $event->slug))
            ->assertOk()
            ->assertDontSee('curtain-photo-stage', false)
            ->assertDontSee('opacity:0;pointer-events:none', false);
    }
}
