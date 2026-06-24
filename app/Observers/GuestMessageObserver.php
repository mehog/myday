<?php

namespace App\Observers;

use App\Models\GuestMessage;
use App\Notifications\NewGuestMessageNotification;

class GuestMessageObserver
{
    public function created(GuestMessage $message): void
    {
        $user = $message->weddingEvent->user;

        if ($user) {
            $user->notify(new NewGuestMessageNotification($message));
        }
    }
}
