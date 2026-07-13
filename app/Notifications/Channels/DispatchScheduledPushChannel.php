<?php

namespace App\Notifications\Channels;

use App\Jobs\SendGuestPushNotificationsJob;
use App\Models\PushNotificationLog;
use App\Notifications\DispatchScheduledGuestPushNotification;
use App\PushNotificationStatus;
use Illuminate\Notifications\Notification;

class DispatchScheduledPushChannel
{
    public function send(object $notifiable, Notification $notification): void
    {
        if (! $notification instanceof DispatchScheduledGuestPushNotification) {
            return;
        }

        $log = PushNotificationLog::query()->find($notification->pushNotificationLogId);

        if ($log === null || $log->status !== PushNotificationStatus::Scheduled) {
            return;
        }

        $log->update(['status' => PushNotificationStatus::Queued]);

        SendGuestPushNotificationsJob::dispatch(
            logId: $log->id,
            guestIds: $log->guest_ids ?? [],
            title: $log->title,
            body: $log->body,
        );
    }
}
