<?php

namespace App\Notifications\Concerns;

use Illuminate\Notifications\Messages\MailMessage;

trait BuildsProductMail
{
    protected function withUnsubscribeLink(MailMessage $mail): MailMessage
    {
        $url = route('dashboard.profile');

        return $mail
            ->line(__('notifications.unsubscribe_prompt'))
            ->line('['.__('notifications.unsubscribe_action').']('.$url.')');
    }
}
