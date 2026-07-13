<?php

namespace App\Notifications\Concerns;

use App\Models\Guest;
use App\RsvpStatus;

trait InterruptsGuestReminders
{
    protected function guestReminderShouldInterrupt(Guest $guest, bool $requireUnanswered = false, bool $requirePushSubscription = false): bool
    {
        $event = $guest->weddingEvent;

        if ($event === null || ! $event->is_active || $event->is_demo) {
            return true;
        }

        if ($requireUnanswered && $guest->hasResponded()) {
            return true;
        }

        if ($guest->rsvp_status === RsvpStatus::No) {
            return true;
        }

        if ($requirePushSubscription && ! $guest->pushSubscriptions()->exists()) {
            return true;
        }

        return false;
    }
}
