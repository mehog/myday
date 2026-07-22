<?php

namespace App;

enum PlanTier: string
{
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

    public static function minimumForGuestCount(int $guestCount): self
    {
        foreach (self::ordered() as $tier) {
            if ($tier->coversGuestCount($guestCount)) {
                return $tier;
            }
        }

        return self::Deluxe;
    }
}
