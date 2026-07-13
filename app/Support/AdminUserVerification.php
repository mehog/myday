<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Auth\Events\Verified;

class AdminUserVerification
{
    public static function verify(User $user): bool
    {
        if ($user->hasVerifiedEmail()) {
            return false;
        }

        $verified = $user->markEmailAsVerified();

        if ($verified) {
            event(new Verified($user));
        }

        return $verified;
    }

    public static function resend(User $user): void
    {
        if ($user->hasVerifiedEmail()) {
            return;
        }

        $user->sendEmailVerificationNotification();
    }
}
