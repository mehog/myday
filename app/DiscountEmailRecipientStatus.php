<?php

namespace App;

enum DiscountEmailRecipientStatus: string
{
    case Pending = 'pending';
    case Sent = 'sent';
    case Failed = 'failed';
    case Skipped = 'skipped';

    public function label(): string
    {
        return match ($this) {
            self::Pending => __('discounts.recipient_status_pending'),
            self::Sent => __('discounts.recipient_status_sent'),
            self::Failed => __('discounts.recipient_status_failed'),
            self::Skipped => __('discounts.recipient_status_skipped'),
        };
    }
}
