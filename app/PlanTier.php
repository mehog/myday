<?php

namespace App;

enum PlanTier: string
{
    case Free = 'free';
    case Basic = 'basic';
    case Plus = 'plus';
    case Premium = 'premium';
    case Deluxe = 'deluxe';

    public function label(): string
    {
        return __('pricing.tier_'.$this->value.'_name');
    }

    public function guestsLabel(): string
    {
        return __('pricing.tier_'.$this->value.'_guests');
    }

    public function guestLimit(): ?int
    {
        $limit = config('dodo.tiers.'.$this->value.'.guest_limit');

        return $limit === null ? null : (int) $limit;
    }

    public function sortOrder(): int
    {
        return (int) config('dodo.tiers.'.$this->value.'.sort', 0);
    }

    public function isHighlighted(): bool
    {
        return (bool) config('dodo.tiers.'.$this->value.'.highlighted', false);
    }

    public function isPaid(): bool
    {
        return $this !== self::Free;
    }

    public function isPurchasable(): bool
    {
        return match ($this) {
            self::Basic, self::Plus, self::Premium => true,
            self::Free, self::Deluxe => false,
        };
    }

    public function hasFeature(PlanFeature $feature): bool
    {
        if ($this === self::Free) {
            return false;
        }

        return true;
    }

    public function isAtLeast(self $other): bool
    {
        return $this->sortOrder() >= $other->sortOrder();
    }

    public function coversGuestCount(int $guestCount): bool
    {
        $limit = $this->guestLimit();

        return $limit === null || $guestCount <= $limit;
    }

    /**
     * @return list<self>
     */
    public static function ordered(): array
    {
        $cases = self::cases();

        usort($cases, fn (self $a, self $b): int => $a->sortOrder() <=> $b->sortOrder());

        return $cases;
    }

    /**
     * Tiers shown on public pricing pages (Free + purchasable paid).
     *
     * @return list<self>
     */
    public static function orderedForDisplay(): array
    {
        return array_values(array_filter(
            self::ordered(),
            fn (self $tier): bool => $tier === self::Free || $tier->isPurchasable(),
        ));
    }

    /**
     * Paid tiers that can be bought at checkout.
     *
     * @return list<self>
     */
    public static function purchasable(): array
    {
        return array_values(array_filter(
            self::ordered(),
            fn (self $tier): bool => $tier->isPurchasable(),
        ));
    }

    public static function minimumForGuestCount(int $guestCount): self
    {
        foreach (self::orderedForDisplay() as $tier) {
            if ($tier->coversGuestCount($guestCount)) {
                return $tier;
            }
        }

        return self::Premium;
    }
}
