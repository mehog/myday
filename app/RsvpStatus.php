<?php

namespace App;

enum RsvpStatus: string
{
    case Yes = 'yes';
    case No = 'no';

    public function label(): string
    {
        return match ($this) {
            self::Yes => __('invitation.rsvp_yes'),
            self::No => __('invitation.rsvp_no'),
        };
    }
}
