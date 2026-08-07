<?php

namespace App\Support;

class Referral
{
    public static function cookieExpiryDays(): int
    {
        return (int) floor((int) config('referral.cookie_expiry') / 1440);
    }

    public static function buyerDiscountPercent(): int
    {
        return max(0, (int) config('referral.buyer_discount_percent', 15));
    }
}
