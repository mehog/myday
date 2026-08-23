<?php

namespace App;

enum WeddingTaskPeriod: string
{
    case NineToTwelveMonths = '9_12_months';
    case SixToNineMonths = '6_9_months';
    case ThreeToSixMonths = '3_6_months';
    case OneToThreeMonths = '1_3_months';
    case TwoToFourWeeks = '2_4_weeks';
    case LastWeek = 'last_week';
    case Custom = 'custom';

    public function label(): string
    {
        return __('checklist.periods.'.$this->value);
    }

    public function sortOrder(): int
    {
        return match ($this) {
            self::NineToTwelveMonths => 10,
            self::SixToNineMonths => 20,
            self::ThreeToSixMonths => 30,
            self::OneToThreeMonths => 40,
            self::TwoToFourWeeks => 50,
            self::LastWeek => 60,
            self::Custom => 70,
        };
    }
}
