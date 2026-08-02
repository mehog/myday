<?php

namespace App;

enum DiscountType: string
{
    case Percentage = 'percentage';
    case Flat = 'flat';

    public function label(): string
    {
        return match ($this) {
            self::Percentage => __('discounts.type_percentage'),
            self::Flat => __('discounts.type_flat'),
        };
    }
}
