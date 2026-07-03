<?php

namespace App;

enum InvitationReveal: string
{
    case Envelope = 'envelope';
    case WaxSeal = 'wax-seal';
    case Curtain = 'curtain';

    public function label(): string
    {
        return match ($this) {
            self::Envelope => __('app.reveal_envelope'),
            self::WaxSeal => __('app.reveal_wax_seal'),
            self::Curtain => __('app.reveal_curtain'),
        };
    }
}
