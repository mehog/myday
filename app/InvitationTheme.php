<?php

namespace App;

enum InvitationTheme: string
{
    case AmberGold = 'amber-gold';
    case RoyalWedding = 'royal-wedding';
    case LavenderDream = 'lavender-dream';
    case WinterMagic = 'winter-magic';

    public function label(): string
    {
        return match ($this) {
            self::AmberGold => 'Amber Gold',
            self::RoyalWedding => 'Royal Wedding',
            self::LavenderDream => 'Lavender Dream',
            self::WinterMagic => 'Winter Magic',
        };
    }
}
