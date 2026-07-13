<?php

namespace App\Notifications;

use App\Models\Enquiry;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminEnquiryFollowUpNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $enquiryId,
        public ?Enquiry $enquiry = null,
    ) {}

    public function shouldInterrupt(object $notifiable): bool
    {
        $enquiry = $this->resolveEnquiry();

        if ($enquiry === null) {
            return true;
        }

        return User::query()->where('email', $enquiry->email)->exists();
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
        $enquiry = $this->resolveEnquiry() ?? Enquiry::query()->findOrFail($this->enquiryId);

        return (new MailMessage)
            ->subject(__('notifications.admin_enquiry_follow_up_subject', [
                'name' => $enquiry->name,
            ]))
            ->greeting(__('notifications.admin_enquiry_follow_up_greeting'))
            ->line(__('notifications.admin_enquiry_follow_up_body', [
                'name' => $enquiry->name,
                'email' => $enquiry->email,
                'days' => config('notifications.admin_enquiry_follow_up_days', 3),
            ]))
            ->replyTo($enquiry->email, $enquiry->name)
            ->action(__('notifications.admin_new_enquiry_reply'), 'mailto:'.$enquiry->email);
    }

    private function resolveEnquiry(): ?Enquiry
    {
        if ($this->enquiry !== null) {
            return $this->enquiry;
        }

        if ($this->enquiryId === 0) {
            return null;
        }

        return Enquiry::query()->find($this->enquiryId);
    }
}
