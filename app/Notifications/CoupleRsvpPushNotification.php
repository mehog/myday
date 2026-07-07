<?php

namespace App\Notifications;

use App\Filament\App\Pages\AppDashboard;
use App\RsvpStatus;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class CoupleRsvpPushNotification extends Notification
{
    public function __construct(
        public string $guestName,
        public RsvpStatus $rsvpStatus,
        public ?string $rsvpNote = null,
    ) {}

    /**
     * @return array<int, class-string>
     */
    public function via(object $notifiable): array
    {
        return [WebPushChannel::class];
    }

    public function toWebPush(object $notifiable, Notification $notification): WebPushMessage
    {
        $bodyKey = $this->rsvpStatus === RsvpStatus::Yes
            ? 'app.couple_rsvp_notification_body_yes'
            : 'app.couple_rsvp_notification_body_no';

        $body = __($bodyKey, ['name' => $this->guestName]);

        if (filled($this->rsvpNote)) {
            $body .= ' '.__('app.couple_rsvp_notification_note_suffix', ['note' => $this->rsvpNote]);
        }

        return (new WebPushMessage)
            ->title(__('app.couple_rsvp_notification_title', ['name' => $this->guestName]))
            ->body($body)
            ->data(['url' => AppDashboard::getUrl(panel: 'app')]);
    }
}
