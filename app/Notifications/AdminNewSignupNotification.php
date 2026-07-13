<?php

namespace App\Notifications;

use App\Filament\Resources\WeddingEvents\WeddingEventResource;
use App\Models\WeddingEvent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminNewSignupNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public WeddingEvent $event,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $event = $this->event;
        $user = $event->user;
        $adminUrl = WeddingEventResource::getUrl('view', ['record' => $event->id], panel: 'admin');

        return (new MailMessage)
            ->subject(__('notifications.admin_new_signup_subject', [
                'couple' => $event->couple_names,
            ]))
            ->greeting(__('notifications.admin_new_signup_greeting'))
            ->line(__('notifications.admin_new_signup_body', [
                'couple' => $event->couple_names,
                'name' => $user?->name ?? '—',
                'email' => $user?->email ?? '—',
                'date' => $event->wedding_date->format('d.m.Y.'),
            ]))
            ->action(__('notifications.admin_new_signup_action'), $adminUrl);
    }
}
