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

class GuestRsvpReminderNotification extends Notification implements ShouldQueue
{
    use InterruptsGuestReminders, Queueable;

    public function __construct(
        public int $daysBeforeDeadline,
    ) {}

    public function shouldInterrupt(object $notifiable): bool
    {
        if (! $notifiable instanceof Guest) {
            return true;
        }

        if ($notifiable->weddingEvent?->rsvp_deadline === null) {
            return true;
        }

        return $this->guestReminderShouldInterrupt($notifiable, requireUnanswered: true);
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
        $deadline = $event->rsvp_deadline?->format('d.m.Y.') ?? '';

        return (new MailMessage)
            ->subject(__('notifications.rsvp_reminder_subject', [
                'couple' => $event->couple_names,
            ]))
            ->greeting(__('notifications.rsvp_reminder_greeting', [
                'name' => $notifiable->name,
            ]))
            ->line(__('notifications.rsvp_reminder_body', [
                'couple' => $event->couple_names,
                'days' => $this->daysBeforeDeadline,
                'deadline' => $deadline,
            ]))
            ->action(__('notifications.rsvp_reminder_action'), $notifiable->personal_url);
    }

    public function toWebPush(object $notifiable, Notification $notification): WebPushMessage
    {
        /** @var Guest $notifiable */
        $event = $notifiable->weddingEvent;

        return (new WebPushMessage)
            ->title(__('notifications.rsvp_reminder_push_title', [
                'couple' => $event->couple_names,
            ]))
            ->body(__('notifications.rsvp_reminder_push_body', [
                'days' => $this->daysBeforeDeadline,
            ]))
            ->data(['url' => $notifiable->personal_url]);
    }
}
