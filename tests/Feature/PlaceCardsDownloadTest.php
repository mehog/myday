<?php

namespace Tests\Feature;

use App\InvitationTheme;
use App\Models\Guest;
use App\Models\User;
use App\Models\WeddingEvent;
use App\RsvpStatus;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class PlaceCardsDownloadTest extends TestCase
{
    use DatabaseTransactions;

    public function test_guest_cannot_download_place_cards(): void
    {
        $this->get(route('guests.place-cards.download'))
            ->assertRedirect(route('login'));
    }

    public function test_unverified_user_cannot_download_place_cards(): void
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)
            ->get(route('guests.place-cards.download'))
            ->assertRedirect(route('verification.notice'));
    }

    public function test_user_without_wedding_event_gets_not_found(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('guests.place-cards.download'))
            ->assertNotFound();
    }

    public function test_user_without_confirmed_guests_gets_not_found(): void
    {
        $user = User::factory()->create();
        $event = $this->createWeddingEventFor($user);

        Guest::query()->create([
            'wedding_event_id' => $event->id,
            'name' => 'Pending Guest',
            'token' => 'pending-guest-token-1234567890ab',
            'rsvp_status' => null,
        ]);

        $this->actingAs($user)
            ->get(route('guests.place-cards.download'))
            ->assertNotFound();
    }

    public function test_user_with_confirmed_guests_can_download_place_cards_pdf(): void
    {
        $user = User::factory()->create();
        $event = $this->createWeddingEventFor($user);

        Guest::query()->create([
            'wedding_event_id' => $event->id,
            'name' => 'Ana Kovačević',
            'token' => 'confirmed-guest-token-1234567890',
            'rsvp_status' => RsvpStatus::Yes,
            'plus_one_name' => 'Marko Kovač',
        ]);

        $response = $this->actingAs($user)
            ->get(route('guests.place-cards.download', [
                'bg' => '#FDF8F0',
                'text' => '#2C1810',
                'accent' => '#C9A227',
            ]));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
        $this->assertStringStartsWith('%PDF', $response->getContent() ?: '');
    }

    public function test_invalid_color_params_fall_back_to_theme_defaults(): void
    {
        $user = User::factory()->create();
        $event = $this->createWeddingEventFor($user, InvitationTheme::DustyRose);

        Guest::query()->create([
            'wedding_event_id' => $event->id,
            'name' => 'Guest One',
            'token' => 'guest-one-token-12345678901234',
            'rsvp_status' => RsvpStatus::Yes,
        ]);

        $response = $this->actingAs($user)
            ->get(route('guests.place-cards.download', [
                'bg' => 'not-a-color',
                'text' => '#ZZZZZZ',
                'accent' => 'gold',
            ]));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }

    private function createWeddingEventFor(User $user, InvitationTheme $theme = InvitationTheme::AmberGold): WeddingEvent
    {
        return WeddingEvent::query()->create([
            'user_id' => $user->id,
            'slug' => 'test-wedding-'.$user->id,
            'bride_name' => 'Ana',
            'groom_name' => 'Marko',
            'wedding_date' => now()->addMonth(),
            'theme' => $theme,
            'template' => 'classic',
            'link_mode' => 'public',
            'is_active' => true,
        ]);
    }
}
