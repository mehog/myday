<?php

namespace App\Notifications;

use App\Models\Guest;
use App\Notifications\Concerns\InterruptsGuestReminders;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class GuestPhotoUploadReminderNotification extends Notification implements ShouldQueue
{
    use InterruptsGuestReminders, Queueable;

    public function __construct(
        public string $variant,
    ) {}

    public function shouldInterrupt(object $notifiable): bool
    {
        if (! $notifiable instanceof Guest) {
            return true;
        }

        if ($this->guestReminderShouldInterrupt($notifiable, requirePushSubscription: true)) {
            return true;
        }

        $event = $notifiable->weddingEvent;
        $start = $event->wedding_date->copy()->startOfDay();
        $end = $event->wedding_date->copy()->addDays(30)->endOfDay();

        return ! now()->between($start, $end);
    }

    /**
     * @return array<int, class-string>
     */
    public function via(object $notifiable): array
    {
        if (! $notifiable instanceof Guest) {
            return [];
        }

        if (! $notifiable->pushSubscriptions()->exists()) {
            return [];
        }

        return [WebPushChannel::class];
    }

    public function toWebPush(object $notifiable, Notification $notification): WebPushMessage
    {
        /** @var Guest $notifiable */
        $event = $notifiable->weddingEvent;
        $contactUrl = route('invitation.contact.guest', [$event->slug, $notifiable->token]);

        return (new WebPushMessage)
            ->title(__('notifications.photo_reminder_push_title', [
                'couple' => $event->couple_names,
            ]))
            ->body(__("notifications.photo_reminder_push_body_{$this->variant}"))
            ->data(['url' => $contactUrl]);
    }
}
