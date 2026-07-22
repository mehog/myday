<?php

namespace Tests\Feature;

use App\InvitationReveal;
use App\Models\User;
use App\Models\WeddingEvent;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\RefreshInMemoryDatabase;
use Tests\TestCase;

class SunriseBloomRevealTest extends TestCase
{
    use RefreshInMemoryDatabase;

    public function test_active_invitation_renders_sunrise_bloom_reveal_stage(): void
    {
        $event = WeddingEvent::factory()->create([
            'is_active' => true,
            'reveal_animation' => InvitationReveal::SunriseBloom,
            'bride_name' => 'Ana',
            'groom_name' => 'Marko',
        ]);

        $this->get(route('invitation.show', $event->slug))
            ->assertOk()
            ->assertSee('bloom-photo-stage', false)
            ->assertSee('bloom-photo-canvas', false)
            ->assertSee('aspect-ratio: 9 / 16', false)
            ->assertSee('min(577px, 100vw, calc(100dvh * 9 / 16))', false)
            ->assertSee('bloom-photo-trigger', false)
            ->assertSee('bloom-vines', false)
            ->assertSee('bloom-petals', false)
            ->assertSee('nasdan-sunrise-bloom-closed.webp', false)
            ->assertSee('nasdan-sunrise-bloom-open.webp', false)
            ->assertSee('Marko &amp; Ana', false)
            ->assertSee('id="invitation-content"', false)
            ->assertSee('opacity:0;pointer-events:none', false)
            ->assertSee('--bloom-crossfade', false)
            ->assertSee('prefers-reduced-motion', false);
    }

    public function test_preview_invitation_skips_sunrise_bloom_reveal(): void
    {
        $owner = User::factory()->create();
        $event = WeddingEvent::factory()->for($owner)->inactive()->create([
            'reveal_animation' => InvitationReveal::SunriseBloom,
        ]);

        $this->actingAs($owner)
            ->get(route('invitation.show', $event->slug))
            ->assertOk()
            ->assertDontSee('bloom-photo-stage', false)
            ->assertDontSee('opacity:0;pointer-events:none', false);
    }

    public function test_legacy_starlit_constellation_session_renders_sunrise_bloom_reveal(): void
    {
        $event = WeddingEvent::factory()->create([
            'is_active' => true,
            'is_demo' => true,
            'reveal_animation' => InvitationReveal::SunriseBloom,
        ]);

        session()->put("demo_preview.{$event->id}", [
            'theme' => $event->theme->value,
            'template' => $event->template->value,
            'reveal' => 'starlit-constellation',
        ]);

        $this->get(route('invitation.show', $event->slug))
            ->assertOk()
            ->assertSee('bloom-photo-stage', false)
            ->assertSee('nasdan-sunrise-bloom-closed.webp', false);
    }

    public function test_starlit_constellation_reveal_values_are_migrated_to_sunrise_bloom(): void
    {
        $event = WeddingEvent::factory()->create([
            'reveal_animation' => InvitationReveal::Envelope,
        ]);

        $migration = 'database/migrations/2026_07_19_150000_rename_starlit_constellation_reveal_to_sunrise_bloom.php';

        $this->artisan('migrate:rollback', [
            '--path' => $migration,
            '--realpath' => true,
        ])->assertSuccessful();

        DB::table('wedding_events')
            ->where('id', $event->id)
            ->update(['reveal_animation' => 'starlit-constellation']);

        $this->artisan('migrate', [
            '--path' => $migration,
            '--realpath' => true,
        ])->assertSuccessful();

        $this->assertSame(
            'sunrise-bloom',
            DB::table('wedding_events')->where('id', $event->id)->value('reveal_animation')
        );
    }
}
