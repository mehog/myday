<?php

namespace App;

enum DiscountEmailAudience: string
{
    case UnpaidVerified = 'unpaid_verified';
    case Unverified = 'unverified';
    case Paid = 'paid';
    case Manual = 'manual';
    case All = 'all';

    public function label(): string
    {
        return match ($this) {
            self::UnpaidVerified => __('discounts.audience_unpaid_verified'),
            self::Unverified => __('discounts.audience_unverified'),
            self::Paid => __('discounts.audience_paid'),
            self::Manual => __('discounts.audience_manual'),
            self::All => __('discounts.audience_all'),
        };
    }
}
