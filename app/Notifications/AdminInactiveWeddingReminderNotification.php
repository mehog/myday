<?php

namespace App\Notifications;

use App\Filament\Resources\WeddingEvents\WeddingEventResource;
use App\Models\WeddingEvent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminInactiveWeddingReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $weddingEventId,
    ) {}

    public function shouldInterrupt(object $notifiable): bool
    {
        $event = WeddingEvent::query()->find($this->weddingEventId);

        if ($event === null) {
            return true;
        }

        return $event->is_active || $event->is_demo;
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $event = WeddingEvent::query()->findOrFail($this->weddingEventId);
        $user = $event->user;
        $adminUrl = WeddingEventResource::getUrl('view', ['record' => $event->id], panel: 'admin');

        return (new MailMessage)
            ->subject(__('notifications.admin_inactive_wedding_subject', [
                'couple' => $event->couple_names,
                'days' => config('notifications.admin_inactive_wedding_days_before', 14),
            ]))
            ->greeting(__('notifications.admin_inactive_wedding_greeting'))
            ->line(__('notifications.admin_inactive_wedding_body', [
                'couple' => $event->couple_names,
                'date' => $event->wedding_date->format('d.m.Y.'),
                'days' => config('notifications.admin_inactive_wedding_days_before', 14),
                'email' => $user?->email ?? '—',
            ]))
            ->action(__('notifications.admin_inactive_wedding_action'), $adminUrl);
    }
}
