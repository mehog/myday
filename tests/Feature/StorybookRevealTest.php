<?php

namespace Tests\Feature;

use App\InvitationReveal;
use App\Models\User;
use App\Models\WeddingEvent;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\RefreshInMemoryDatabase;
use Tests\TestCase;

class StorybookRevealTest extends TestCase
{
    use RefreshInMemoryDatabase;

    public function test_active_invitation_renders_storybook_reveal_stage(): void
    {
        $event = WeddingEvent::factory()->create([
            'is_active' => true,
            'reveal_animation' => InvitationReveal::Storybook,
            'bride_name' => 'Ana',
            'groom_name' => 'Marko',
        ]);

        $this->get(route('invitation.show', $event->slug))
            ->assertOk()
            ->assertSee('story-photo-stage', false)
            ->assertSee('story-photo-canvas', false)
            ->assertSee('aspect-ratio: 9 / 16', false)
            ->assertSee('min(577px, 100vw, calc(100dvh * 9 / 16))', false)
            ->assertSee('story-photo-trigger', false)
            ->assertSee('nasdan-storybook-closed.webp', false)
            ->assertSee('nasdan-storybook-open.webp', false)
            ->assertSee('Marko &amp; Ana', false)
            ->assertSee('id="invitation-content"', false)
            ->assertSee('opacity:0;pointer-events:none', false);
    }

    public function test_preview_invitation_skips_storybook_reveal(): void
    {
        $owner = User::factory()->create();
        $event = WeddingEvent::factory()->for($owner)->inactive()->create([
            'reveal_animation' => InvitationReveal::Storybook,
        ]);

        $this->actingAs($owner)
            ->get(route('invitation.show', $event->slug))
            ->assertOk()
            ->assertDontSee('story-photo-stage', false)
            ->assertDontSee('opacity:0;pointer-events:none', false);
    }

    public function test_legacy_polaroid_session_renders_storybook_reveal(): void
    {
        $event = WeddingEvent::factory()->create([
            'is_active' => true,
            'is_demo' => true,
            'reveal_animation' => InvitationReveal::Storybook,
        ]);

        session()->put("demo_preview.{$event->id}", [
            'theme' => $event->theme->value,
            'template' => $event->template->value,
            'reveal' => 'polaroid',
        ]);

        $this->get(route('invitation.show', $event->slug))
            ->assertOk()
            ->assertSee('story-photo-stage', false)
            ->assertSee('nasdan-storybook-closed.webp', false);
    }

    public function test_polaroid_reveal_values_are_migrated_to_storybook(): void
    {
        $event = WeddingEvent::factory()->create([
            'reveal_animation' => InvitationReveal::Envelope,
        ]);

        $migration = 'database/migrations/2026_07_19_140000_rename_polaroid_reveal_to_storybook.php';

        $this->artisan('migrate:rollback', [
            '--path' => $migration,
            '--realpath' => true,
        ])->assertSuccessful();

        DB::table('wedding_events')
            ->where('id', $event->id)
            ->update(['reveal_animation' => 'polaroid']);

        $this->artisan('migrate', [
            '--path' => $migration,
            '--realpath' => true,
        ])->assertSuccessful();

        $this->assertSame(
            'storybook',
            DB::table('wedding_events')->where('id', $event->id)->value('reveal_animation')
        );
    }
}
