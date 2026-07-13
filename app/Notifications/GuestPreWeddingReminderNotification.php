<?php

namespace App\Notifications;

use App\Models\Guest;
use App\Notifications\Concerns\InterruptsGuestReminders;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class GuestPreWeddingReminderNotification extends Notification implements ShouldQueue
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

        return $this->guestReminderShouldInterrupt($notifiable);
    }

    /**
     * @return array<int, class-string|string>
     */
    public function via(object $notifiable): array
    {
        if (! $notifiable instanceof Guest) {
            return [];
        }

        $channels = [];

        if (filled($notifiable->email)) {
            $channels[] = 'mail';
        }

        if ($notifiable->pushSubscriptions()->exists()) {
            $channels[] = WebPushChannel::class;
        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        /** @var Guest $notifiable */
        $event = $notifiable->weddingEvent;

        return (new MailMessage)
            ->subject(__('notifications.pre_wedding_subject', [
                'couple' => $event->couple_names,
            ]))
            ->greeting(__('notifications.pre_wedding_greeting', [
                'name' => $notifiable->name,
            ]))
            ->line(__("notifications.pre_wedding_body_{$this->variant}", [
                'couple' => $event->couple_names,
                'date' => $event->wedding_date->format('d.m.Y.'),
                'location' => $event->location_name ?? $event->location_address ?? '',
            ]))
            ->action(__('notifications.pre_wedding_action'), $notifiable->personal_url);
    }

    public function toWebPush(object $notifiable, Notification $notification): WebPushMessage
    {
        /** @var Guest $notifiable */
        $event = $notifiable->weddingEvent;

        return (new WebPushMessage)
            ->title(__('notifications.pre_wedding_push_title', [
                'couple' => $event->couple_names,
            ]))
            ->body(__("notifications.pre_wedding_push_body_{$this->variant}"))
            ->data(['url' => $notifiable->personal_url]);
    }
}
