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
    case PaperInk = 'paper-ink';

    public function label(): string
    {
        return match ($this) {
            self::AmberGold => 'Amber Gold',
            self::RoyalWedding => 'Royal Wedding',
            self::LavenderDream => 'Lavender Dream',
            self::WinterMagic => 'Winter Magic',
            self::PearlWhite => 'Pearl White',
            self::DustyRose => 'Dusty Rose',
            self::PaperInk => 'Paper & Ink',
        };
    }

    /**
     * @return array{bg: string, text: string, accent: string}
     */
    public function placeCardColors(): array
    {
        return match ($this) {
            self::AmberGold => [
                'bg' => '#FDF8F0',
                'text' => '#2C1810',
                'accent' => '#C9A227',
            ],
            self::RoyalWedding => [
                'bg' => '#F0F4FF',
                'text' => '#1A237E',
                'accent' => '#7986CB',
            ],
            self::LavenderDream => [
                'bg' => '#F9F5FF',
                'text' => '#3D1A6E',
                'accent' => '#A78BFA',
            ],
            self::WinterMagic => [
                'bg' => '#F0F8FF',
                'text' => '#1A3A4A',
                'accent' => '#7EC8E3',
            ],
            self::PearlWhite => [
                'bg' => '#FAFAF8',
                'text' => '#2D2D2D',
                'accent' => '#C8A882',
            ],
            self::DustyRose => [
                'bg' => '#FFF0F3',
                'text' => '#4A1A2A',
                'accent' => '#C07080',
            ],
            self::PaperInk => [
                'bg' => '#F3EDE3',
                'text' => '#3A2E24',
                'accent' => '#9A7B4F',
            ],
        };
    }
}
