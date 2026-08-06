<?php

namespace App;

enum BudgetCalculationType: string
{
    case Fixed = 'fixed';
    case PerPerson = 'per_person';

    public function label(): string
    {
        return match ($this) {
            self::Fixed => __('budget.calculation_fixed'),
            self::PerPerson => __('budget.calculation_per_person'),
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(
            fn (self $type): array => [$type->value => $type->label()],
        )->all();
    }
}
