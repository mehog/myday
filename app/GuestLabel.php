<?php

namespace App;

enum GuestLabel: string
{
    case Bride = 'bride';
    case Groom = 'groom';
    case Friend = 'friend';
    case Family = 'family';
    case Colleague = 'colleague';
    case Teenager = 'teenager';
    case Older = 'older';

    public function label(): string
    {
        return __('guests.label_'.$this->value);
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $case): array => [$case->value => $case->label()])
            ->all();
    }
}
