<?php

namespace App;

enum BudgetGuestMode: string
{
    case Manual = 'manual';
    case Confirmed = 'confirmed';
    case Invited = 'invited';

    public function label(): string
    {
        return match ($this) {
            self::Manual => __('budget.guest_mode_manual'),
            self::Confirmed => __('budget.guest_mode_confirmed'),
            self::Invited => __('budget.guest_mode_invited'),
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(
            fn (self $mode): array => [$mode->value => $mode->label()],
        )->all();
    }
}
