<?php

namespace App\Jobs;

use App\Models\Guest;
use App\Notifications\CoupleRsvpPushNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendCoupleRsvpNotificationJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $guestId) {}

    public function handle(): void
    {
        $guest = Guest::query()
            ->with('weddingEvent.user')
            ->find($this->guestId);

        $user = $guest?->weddingEvent?->user;

        if ($user === null || $guest->weddingEvent->is_demo) {
            return;
        }

        if (! $user->pushSubscriptions()->exists()) {
            return;
        }

        $user->notify(new CoupleRsvpPushNotification(
            guestName: $guest->name,
            rsvpStatus: $guest->rsvp_status,
            rsvpNote: $guest->rsvp_note,
        ));
    }
}
