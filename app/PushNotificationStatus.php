<?php

namespace App;

enum PushNotificationStatus: string
{
    case Scheduled = 'scheduled';
    case Queued = 'queued';
    case Sent = 'sent';
    case Failed = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::Scheduled => __('app.push_notifications_status_scheduled'),
            self::Queued => __('app.push_notifications_status_queued'),
            self::Sent => __('app.push_notifications_status_sent'),
            self::Failed => __('app.push_notifications_status_failed'),
        };
    }
}
