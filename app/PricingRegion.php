<?php

namespace App;

enum PricingRegion: string
{
    case FirstWorld = 'first_world';
    case ThirdWorld = 'third_world';

    public function currency(): string
    {
        return (string) config('dodo.regions.'.$this->value.'.currency');
    }

    public function label(): string
    {
        return match ($this) {
            self::FirstWorld => '1st world',
            self::ThirdWorld => '3rd world',
        };
    }

    public function priceFor(PlanTier $tier): int
    {
        return (int) config('dodo.regions.'.$this->value.'.prices.'.$tier->value);
    }
}
