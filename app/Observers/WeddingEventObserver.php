<?php

namespace App\Observers;

use App\Models\WeddingEvent;
use App\Services\WeddingScheduledNotificationService;

class WeddingEventObserver
{
    public function __construct(
        private readonly WeddingScheduledNotificationService $scheduledNotifications,
    ) {}

    public function created(WeddingEvent $event): void
    {
        $this->scheduledNotifications->syncEvent($event);
        $this->scheduledNotifications->syncCoupleOnboarding($event);
        $this->scheduledNotifications->syncAdminAlertsForEvent($event);
        $this->scheduledNotifications->notifyAdminsOfNewSignup($event);
    }

    public function updated(WeddingEvent $event): void
    {
        if ($event->wasChanged(['rsvp_deadline', 'wedding_date', 'is_active', 'is_demo'])) {
            $this->scheduledNotifications->syncEvent($event);
        }

        if ($event->wasChanged(['is_active', 'wedding_date', 'user_id', 'is_demo'])) {
            $this->scheduledNotifications->syncCoupleOnboarding($event);
            $this->scheduledNotifications->syncAdminAlertsForEvent($event);
        }
    }
}
