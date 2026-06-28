<?php

namespace App;

enum PushNotificationRecipientType: string
{
    case All = 'all';
    case Unanswered = 'unanswered';
    case Selected = 'selected';

    public function label(): string
    {
        return match ($this) {
            self::All => __('app.push_notifications_recipients_all'),
            self::Unanswered => __('app.push_notifications_recipients_unanswered'),
            self::Selected => __('app.push_notifications_recipients_selected'),
        };
    }
}
