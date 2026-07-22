<?php

namespace App;

enum DodoPaymentStatus: string
{
    case Pending = 'pending';
    case Succeeded = 'succeeded';
    case Failed = 'failed';
    case Refunded = 'refunded';
    case Disputed = 'disputed';

    public function label(): string
    {
        return match ($this) {
            self::Pending => __('pricing.payment_status_pending'),
            self::Succeeded => __('pricing.payment_status_succeeded'),
            self::Failed => __('pricing.payment_status_failed'),
            self::Refunded => __('pricing.payment_status_refunded'),
            self::Disputed => __('pricing.payment_status_disputed'),
        };
    }

    public function isSuccessful(): bool
    {
        return $this === self::Succeeded;
    }
}
