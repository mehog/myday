<?php

namespace App\Notifications;

use App\Models\PushNotificationLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class DispatchScheduledGuestPushNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $pushNotificationLogId,
    ) {}

    public function shouldInterrupt(object $notifiable): bool
    {
        $log = PushNotificationLog::query()->find($this->pushNotificationLogId);

        if ($log === null) {
            return true;
        }

        $event = $log->weddingEvent;

        return $event === null || ! $event->is_active || $event->is_demo;
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['dispatch-scheduled-push'];
    }
}
