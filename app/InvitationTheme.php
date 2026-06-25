<?php

namespace App;

enum InvitationTheme: string
{
    case AmberGold = 'amber-gold';
    case RoyalWedding = 'royal-wedding';
    case LavenderDream = 'lavender-dream';
    case WinterMagic = 'winter-magic';
    case PearlWhite = 'pearl-white';
    case DustyRose = 'dusty-rose';

    public function label(): string
    {
        return match ($this) {
            self::AmberGold => 'Amber Gold',
            self::RoyalWedding => 'Royal Wedding',
            self::LavenderDream => 'Lavender Dream',
            self::WinterMagic => 'Winter Magic',
            self::PearlWhite => 'Pearl White',
            self::DustyRose => 'Dusty Rose',
        };
    }
}
