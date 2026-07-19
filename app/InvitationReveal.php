<?php

namespace App;

enum InvitationReveal: string
{
    case Envelope = 'envelope';
    case WaxSeal = 'wax-seal';
    case Curtain = 'curtain';
    case Storybook = 'storybook';
    case GardenGate = 'garden-gate';
    case SunriseBloom = 'sunrise-bloom';
    case RoyalCrestDoors = 'royal-crest-doors';

    public function label(): string
    {
        return match ($this) {
            self::Envelope => __('app.reveal_envelope'),
            self::WaxSeal => __('app.reveal_wax_seal'),
            self::Curtain => __('app.reveal_curtain'),
            self::Storybook => __('app.reveal_storybook'),
            self::GardenGate => __('app.reveal_garden_gate'),
            self::SunriseBloom => __('app.reveal_sunrise_bloom'),
            self::RoyalCrestDoors => __('app.reveal_royal_crest_doors'),
        };
    }
}
