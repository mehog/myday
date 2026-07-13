<?php

namespace App\Observers;

use App\Models\Guest;
use App\Services\WeddingScheduledNotificationService;

class GuestObserver
{
    public function __construct(
        private readonly WeddingScheduledNotificationService $scheduledNotifications,
    ) {}

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
