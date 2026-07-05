<?php

namespace App;

enum ReferralPayoutStatus: string
{
    case Pending = 'pending';
    case Paid = 'paid';

    public function label(): string
    {
        return match ($this) {
            self::Pending => __('referrals.payout_status_pending'),
            self::Paid => __('referrals.payout_status_paid'),
        };
    }
}
