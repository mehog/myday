<?php

namespace App\Support;

class Referral
{
    public static function cookieExpiryDays(): int
    {
        return (int) floor((int) config('referral.cookie_expiry') / 1440);
    }
}
