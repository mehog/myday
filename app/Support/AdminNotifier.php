<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;

class AdminNotifier
{
    /**
     * @return Collection<int, User|AnonymousNotifiable>
     */
    public static function recipients(): Collection
    {
        $admins = User::query()
            ->where('is_admin', true)
            ->get();

        if ($admins->isNotEmpty()) {
            return $admins;
        }

        return collect(config('notifications.admin_emails', []))
            ->map(fn (string $email): AnonymousNotifiable => (new AnonymousNotifiable)->route('mail', $email));
    }

    public static function notify(Notification $notification): void
    {
        foreach (self::recipients() as $recipient) {
            $recipient->notify($notification);
        }
    }

    public static function hasRecipients(): bool
    {
        return self::recipients()->isNotEmpty();
    }
}
