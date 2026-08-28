<?php

namespace App\Listeners;

use App\Support\UserSignupIp;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified as UserVerified;

class TrackUserIp
{
    public function handle(UserVerified|Registered $event): void
    {
        UserSignupIp::capture($event->user);
    }
}
