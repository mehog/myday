<?php

namespace Tests\Unit;

use App\GuestMessageType;
use App\GuestMessageVisitMatch;
use App\LinkType;
use App\Models\Guest;
use App\Models\GuestMessage;
use App\Models\LinkVisit;
use App\Models\User;
use App\Models\WeddingEvent;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class GuestMessageVisitMatchTest extends TestCase
{
    use DatabaseTransactions;

    public function test_same_ip_different_browsers_is_soft_not_match(): void
    {
        [$event, $guest] = $this->createGuest();

        LinkVisit::query()->create([
            'wedding_event_id' => $event->id,
            'guest_id' => $guest->id,
            'link_type' => LinkType::Personal,
            'ip_hash' => hash('sha256', '127.0.0.1'),
            'user_agent' => 'Mozilla/5.0 Chrome/148.0.0.0 Safari/537.36',
            'device_type' => 'desktop',
            'browser' => 'Chrome',
            'os' => 'OS X',
            'visited_at' => now(),
        ]);

        $message = $this->createMessage($event, $guest, [
            'ip_hash' => hash('sha256', '127.0.0.1'),
            'user_agent' => 'Mozilla/5.0 Version/26.5 Safari/605.1.15',
            'device_type' => 'desktop',
            'browser' => 'Safari',
            'os' => 'OS X',
        ]);

        $this->assertSame(GuestMessageVisitMatch::Soft, $message->visitMatch());
    }

    public function test_matching_user_agent_is_match(): void
    {
        [$event, $guest] = $this->createGuest();
        $userAgent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36';

        LinkVisit::query()->create([
            'wedding_event_id' => $event->id,
            'guest_id' => $guest->id,
            'link_type' => LinkType::Personal,
            'ip_hash' => hash('sha256', '10.0.0.1'),
            'user_agent' => $userAgent,
            'device_type' => 'desktop',
            'browser' => 'Chrome',
            'os' => 'OS X',
            'visited_at' => now(),
        ]);

        $message = $this->createMessage($event, $guest, [
            'ip_hash' => hash('sha256', '127.0.0.1'),
            'user_agent' => $userAgent,
            'device_type' => 'desktop',
            'browser' => 'Chrome',
            'os' => 'OS X',
        ]);

        $this->assertSame(GuestMessageVisitMatch::Match, $message->visitMatch());
    }

    public function test_different_ip_and_browser_is_mismatch(): void
    {
        [$event, $guest] = $this->createGuest();

        LinkVisit::query()->create([
            'wedding_event_id' => $event->id,
            'guest_id' => $guest->id,
            'link_type' => LinkType::Personal,
            'ip_hash' => hash('sha256', '10.0.0.1'),
            'user_agent' => 'Mozilla/5.0 Chrome',
            'device_type' => 'desktop',
            'browser' => 'Chrome',
            'os' => 'OS X',
            'visited_at' => now(),
        ]);

        $message = $this->createMessage($event, $guest, [
            'ip_hash' => hash('sha256', '192.168.1.50'),
            'user_agent' => 'Mozilla/5.0 Firefox',
            'device_type' => 'mobile',
            'browser' => 'Firefox',
            'os' => 'Android',
        ]);

        $this->assertSame(GuestMessageVisitMatch::Mismatch, $message->visitMatch());
    }

    /**
     * @return array{0: WeddingEvent, 1: Guest}
     */
    private function createGuest(): array
    {
        $user = User::factory()->create();
        $event = WeddingEvent::query()->create([
            'user_id' => $user->id,
            'slug' => 'visit-match-'.$user->id,
            'bride_name' => 'Ana',
            'groom_name' => 'Marko',
            'wedding_date' => now()->addMonth(),
            'theme' => 'amber-gold',
            'template' => 'classic',
            'link_mode' => 'public',
            'is_active' => true,
        ]);
        $guest = Guest::query()->create([
            'wedding_event_id' => $event->id,
            'name' => 'Test Guest',
            'token' => 'visit-match-token-'.$user->id,
        ]);

        return [$event, $guest];
    }

    /**
     * @param  array<string, mixed>  $fingerprint
     */
    private function createMessage(WeddingEvent $event, Guest $guest, array $fingerprint): GuestMessage
    {
        $message = GuestMessage::query()->create([
            'wedding_event_id' => $event->id,
            'guest_id' => $guest->id,
            'sender_name' => $guest->name,
            'type' => GuestMessageType::Text,
            'content' => 'test',
            ...$fingerprint,
        ]);

        return $message->load('guest.latestPersonalLinkVisit');
    }
}
