<?php

namespace App;

enum DiscountEmailCampaignStatus: string
{
    case Draft = 'draft';
    case Sending = 'sending';
    case Sent = 'sent';
    case Failed = 'failed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Draft => __('discounts.campaign_status_draft'),
            self::Sending => __('discounts.campaign_status_sending'),
            self::Sent => __('discounts.campaign_status_sent'),
            self::Failed => __('discounts.campaign_status_failed'),
            self::Cancelled => __('discounts.campaign_status_cancelled'),
        };
    }
}
