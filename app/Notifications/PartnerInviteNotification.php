<?php

namespace App\Notifications;

use App\Models\WeddingPartnerInvite;
use App\Notifications\Concerns\BuildsProductMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PartnerInviteNotification extends Notification implements ShouldQueue
{
    use BuildsProductMail;
    use Queueable;

    public function __construct(
        public WeddingPartnerInvite $invite,
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
        $wedding = $this->invite->weddingEvent;
        $inviter = $this->invite->invitedBy;

        return $this->withUnsubscribeLink(
            (new MailMessage)
                ->subject(__('dashboard.partner_invite_email_subject', [
                    'couple' => $wedding->couple_names,
                ]))
                ->greeting(__('dashboard.partner_invite_email_greeting'))
                ->line(__('dashboard.partner_invite_email_body', [
                    'inviter' => $inviter?->name ?? config('app.name'),
                    'couple' => $wedding->couple_names,
                ]))
                ->action(__('dashboard.partner_invite_email_action'), $this->invite->acceptUrl())
        );
    }
}
