<?php

namespace App\Notifications;

use App\Models\Enquiry;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminNewEnquiryNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Enquiry $enquiry,
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
        $enquiry = $this->enquiry;

        $message = (new MailMessage)
            ->subject(__('notifications.admin_new_enquiry_subject', [
                'name' => $enquiry->name,
            ]))
            ->greeting(__('notifications.admin_new_enquiry_greeting'))
            ->line(__('notifications.admin_new_enquiry_body', [
                'name' => $enquiry->name,
                'email' => $enquiry->email,
                'phone' => $enquiry->phone ?? '—',
            ]));

        if ($enquiry->groom_name || $enquiry->bride_name) {
            $message->line(__('notifications.admin_new_enquiry_couple', [
                'couple' => trim("{$enquiry->groom_name} & {$enquiry->bride_name}", ' &'),
            ]));
        }

        if ($enquiry->wedding_date) {
            $message->line(__('notifications.admin_new_enquiry_wedding_date', [
                'date' => $enquiry->wedding_date->format('d.m.Y.'),
            ]));
        }

        if ($enquiry->notes) {
            $message->line(__('notifications.admin_new_enquiry_notes', [
                'notes' => $enquiry->notes,
            ]));
        }

        return $message
            ->replyTo($enquiry->email, $enquiry->name)
            ->action(__('notifications.admin_new_enquiry_reply'), 'mailto:'.$enquiry->email);
    }
}
