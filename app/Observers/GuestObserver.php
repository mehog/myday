<?php

namespace App\Observers;

use App\Exceptions\GuestLimitExceededException;
use App\Models\Guest;
use App\Models\WeddingEvent;
use App\Services\WeddingScheduledNotificationService;

class GuestObserver
{
    public function __construct(
        private readonly WeddingScheduledNotificationService $scheduledNotifications,
    ) {}

    public function creating(Guest $guest): void
    {
        $this->ensureWithinGuestLimit($guest);
    }

    public function restoring(Guest $guest): void
    {
        $this->ensureWithinGuestLimit($guest);
    }

    private function ensureWithinGuestLimit(Guest $guest): void
    {
        $wedding = $guest->weddingEvent;

        if ($wedding === null && $guest->wedding_event_id) {
            $wedding = WeddingEvent::query()->find($guest->wedding_event_id);
        }

        if ($wedding === null) {
            return;
        }

        if (! $wedding->canAddGuests()) {
            throw new GuestLimitExceededException($wedding);
        }
    }

    public function created(Guest $guest): void
    {
        $this->scheduledNotifications->syncGuest($guest);
    }

    public function updated(Guest $guest): void
    {
        if ($guest->wasChanged('rsvp_status')) {
            if ($guest->hasResponded()) {
                $this->scheduledNotifications->cancelRsvpRemindersForGuest($guest);
            } else {
                $this->scheduledNotifications->syncGuest($guest);
            }

            return;
        }

        if ($guest->wasChanged(['email', 'wedding_event_id'])) {
            $this->scheduledNotifications->syncGuest($guest);
        }
    }

    public function deleted(Guest $guest): void
    {
        $this->scheduledNotifications->cancelPendingForGuest($guest);
    }
}
