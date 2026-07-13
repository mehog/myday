<?php

namespace App\Notifications;

use App\Filament\App\Resources\MyWeddingResource;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CoupleActivationReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function shouldInterrupt(object $notifiable): bool
    {
        if (! $notifiable instanceof User) {
            return true;
        }

        $event = $notifiable->weddingEvent;

        return $event === null || $event->is_active;
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
        /** @var User $notifiable */
        $event = $notifiable->weddingEvent;
        $appUrl = MyWeddingResource::getUrl('edit', ['record' => $event->id], panel: 'app');

        return (new MailMessage)
            ->subject(__('notifications.couple_activation_subject'))
            ->greeting(__('notifications.couple_onboarding_greeting', [
                'name' => $notifiable->name,
            ]))
            ->line(__('notifications.couple_activation_body', [
                'couple' => $event->couple_names,
            ]))
            ->action(__('notifications.couple_onboarding_action'), $appUrl);
    }
}
