<?php

namespace Tests\Feature;

use App\LinkMode;
use App\Livewire\InvitationPage;
use App\Models\Guest;
use App\Models\User;
use App\Models\WeddingEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TokenOnlyInvitationPreviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_open_public_link_when_token_only(): void
    {
        $event = WeddingEvent::factory()->create([
            'link_mode' => LinkMode::TokenOnly,
            'is_active' => true,
        ]);

        $this->get(route('invitation.show', $event->slug))
            ->assertForbidden();
    }

    public function test_owner_can_preview_public_link_when_token_only(): void
    {
        $owner = User::factory()->create();
        $event = WeddingEvent::factory()->for($owner)->create([
            'link_mode' => LinkMode::TokenOnly,
            'is_active' => true,
            'bride_name' => 'Ana',
            'groom_name' => 'Marko',
        ]);

        $this->actingAs($owner)
            ->get(route('invitation.show', $event->slug))
            ->assertOk()
            ->assertSee(__('invitation.token_only_preview_banner'), false)
            ->assertSee(__('invitation.token_only_preview_rsvp'), false);
    }

    public function test_admin_can_preview_public_link_when_token_only(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $event = WeddingEvent::factory()->create([
            'link_mode' => LinkMode::TokenOnly,
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('invitation.show', $event->slug))
            ->assertOk()
            ->assertSee(__('invitation.token_only_preview_banner'), false);
    }

    public function test_other_user_cannot_open_public_link_when_token_only(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $event = WeddingEvent::factory()->for($owner)->create([
            'link_mode' => LinkMode::TokenOnly,
            'is_active' => true,
        ]);

        $this->actingAs($other)
            ->get(route('invitation.show', $event->slug))
            ->assertForbidden();
    }

    public function test_personal_guest_link_still_works_when_token_only(): void
    {
        $event = WeddingEvent::factory()->create([
            'link_mode' => LinkMode::TokenOnly,
            'is_active' => true,
        ]);
        $guest = Guest::factory()->for($event)->create([
            'name' => 'Guest',
        ]);

        $this->get(route('invitation.guest', [
            'slug' => $event->slug,
            'token' => $guest->token,
        ]))
            ->assertOk()
            ->assertDontSee(__('invitation.token_only_preview_banner'), false);
    }

    public function test_owner_cannot_rsvp_from_token_only_public_preview(): void
    {
        $owner = User::factory()->create();
        $event = WeddingEvent::factory()->for($owner)->create([
            'link_mode' => LinkMode::TokenOnly,
            'is_active' => true,
        ]);

        Livewire::actingAs($owner)
            ->test(InvitationPage::class, ['slug' => $event->slug])
            ->set('anonymousName', 'Should Not Save')
            ->call('respond', 'yes')
            ->assertSet('rsvpSubmitted', false);

        $this->assertDatabaseCount('guests', 0);
    }
}
