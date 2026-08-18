<?php

namespace App;

enum PricingRegion: string
{
    case FirstWorld = 'first_world';
    case ThirdWorld = 'third_world';

    /**
     * Countries billed in BAM. Everyone else is EUR.
     *
     * @var list<string>
     */
    public const BAM_COUNTRIES = ['BA'];

    public function currency(): string
    {
        return (string) config('dodo.regions.'.$this->value.'.currency');
    }

    public function label(): string
    {
        return match ($this) {
            self::FirstWorld => 'EUR (international)',
            self::ThirdWorld => 'BAM (Bosnia)',
        };
    }

    public function priceFor(PlanTier $tier): int
    {
        return (int) config('dodo.regions.'.$this->value.'.prices.'.$tier->value);
    }

    public static function fromCountryCode(?string $code): self
    {
        $normalized = is_string($code) && $code !== '' ? strtoupper($code) : null;

        return $normalized !== null && in_array($normalized, self::BAM_COUNTRIES, true)
            ? self::ThirdWorld
            : self::FirstWorld;
    }
}
