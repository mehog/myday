<?php

namespace App\Notifications\Preview;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushMessage;

class PushMirrorPreviewNotification extends Notification
{
    public function __construct(
        public string $scenarioLabel,
        public WebPushMessage $message,
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
        $payload = $this->message->toArray();

        $mail = (new MailMessage)
            ->subject('[Push preview] '.$this->scenarioLabel.': '.($payload['title'] ?? ''))
            ->line($payload['body'] ?? '');

        $url = is_array($payload['data'] ?? null) ? ($payload['data']['url'] ?? null) : null;

        if (filled($url)) {
            $mail->action('Open URL', $url);
        }

        return $mail;
    }
}
