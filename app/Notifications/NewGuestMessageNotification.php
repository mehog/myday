<?php

namespace App\Notifications;

use App\Filament\App\Resources\GuestMessagesResource;
use App\Models\GuestMessage;
use Filament\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class NewGuestMessageNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly GuestMessage $message) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return FilamentNotification::make()
            ->title(__('app.notification_new_message_title'))
            ->body(__('app.notification_new_message_body', [
                'name' => $this->message->sender_name,
                'type' => $this->message->type->label(),
            ]))
            ->icon('heroicon-o-chat-bubble-left-right')
            ->iconColor('info')
            ->actions([
                Action::make('view')
                    ->label(__('app.notification_view_message'))
                    ->url(GuestMessagesResource::getUrl('view', ['record' => $this->message->id], panel: 'app'))
                    ->markAsRead(),
            ])
            ->getDatabaseMessage();
    }
}
