<?php

namespace Tests\Feature;

use App\Http\Controllers\DownloadPlaceCardsController;
use App\InvitationTheme;
use App\Models\Guest;
use App\Models\User;
use App\Models\WeddingEvent;
use App\RsvpStatus;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use ReflectionMethod;
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

    public function test_qr_data_uri_is_png_data_uri(): void
    {
        $controller = new DownloadPlaceCardsController;
        $method = new ReflectionMethod($controller, 'qrDataUri');
        $method->setAccessible(true);

        $dataUri = $method->invoke($controller, 'https://example.test/e/demo/token/contact?qr-code=true');

        $this->assertStringStartsWith('data:image/png;base64,', $dataUri);
        // Scale 16 + quiet zone produces a crisp PNG suitable for print.
        $this->assertGreaterThan(4000, strlen($dataUri));

        $binary = base64_decode(substr($dataUri, strlen('data:image/png;base64,')), true);
        $this->assertNotFalse($binary);
        $size = getimagesizefromstring($binary);
        $this->assertIsArray($size);
        $this->assertGreaterThanOrEqual(300, $size[0]);
        $this->assertSame($size[0], $size[1]);
    }

    public function test_place_cards_blade_keeps_guest_name_panel(): void
    {
        $user = User::factory()->create();
        $event = $this->createWeddingEventFor($user);

        $html = view('pdf.place-cards', [
            'cards' => [[
                'name' => 'Ana Kovačević',
                'plus_one' => 'Marko',
                'children' => [],
                'qr' => 'data:image/png;base64,aaa',
            ]],
            'colors' => ['bg' => '#FDF8F0', 'text' => '#2C1810', 'accent' => '#C9A227'],
            'weddingEvent' => $event,
            'siteUrl' => 'example.test',
        ])->render();

        $this->assertStringContainsString('card-back', $html);
        $this->assertStringContainsString('Ana Kovačević', $html);
        $this->assertStringContainsString(__('guests.place_cards_scan_cta'), $html);
        $this->assertStringContainsString('card-grid-wrap', $html);
        $this->assertStringContainsString('cut-guide-v1', $html);
        $this->assertStringContainsString('cut-guide-h', $html);
        $this->assertStringNotContainsString('share-heading', $html);
        $this->assertStringNotContainsString('front-meta', $html);
    }

    public function test_place_cards_pack_six_per_page(): void
    {
        $user = User::factory()->create();
        $event = $this->createWeddingEventFor($user);

        $cards = collect(range(1, 7))->map(fn (int $i): array => [
            'name' => "Guest {$i}",
            'plus_one' => null,
            'children' => [],
            'qr' => 'data:image/png;base64,aaa',
        ])->all();

        $html = view('pdf.place-cards', [
            'cards' => $cards,
            'colors' => ['bg' => '#FDF8F0', 'text' => '#2C1810', 'accent' => '#C9A227'],
            'weddingEvent' => $event,
            'siteUrl' => 'example.test',
        ])->render();

        $this->assertSame(2, substr_count($html, '<div class="page">'));
        $this->assertSame(2, substr_count($html, '<div class="card-grid-wrap">'));
        $this->assertStringContainsString('Guest 1', $html);
        $this->assertStringContainsString('Guest 7', $html);
    }

    public function test_six_guests_fit_on_one_page(): void
    {
        $user = User::factory()->create();
        $event = $this->createWeddingEventFor($user);

        $cards = collect(range(1, 6))->map(fn (int $i): array => [
            'name' => "Guest {$i}",
            'plus_one' => null,
            'children' => [],
            'qr' => 'data:image/png;base64,aaa',
        ])->all();

        $html = view('pdf.place-cards', [
            'cards' => $cards,
            'colors' => ['bg' => '#FDF8F0', 'text' => '#2C1810', 'accent' => '#C9A227'],
            'weddingEvent' => $event,
            'siteUrl' => 'example.test',
        ])->render();

        $this->assertSame(1, substr_count($html, '<div class="page">'));
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
