<?php

namespace App;

enum InvitationTemplate: string
{
    case Classic = 'classic';
    case Editorial = 'editorial';

    public function label(): string
    {
        return match ($this) {
            self::Classic => __('app.template_classic'),
            self::Editorial => __('app.template_editorial'),
        };
    }
}
