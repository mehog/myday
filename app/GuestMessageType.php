<?php

namespace App;

enum GuestMessageType: string
{
    case Text = 'text';
    case Audio = 'audio';
    case Photo = 'photo';

    public function label(): string
    {
        return match ($this) {
            self::Text => __('invitation.message_type_text'),
            self::Audio => __('invitation.message_type_audio'),
            self::Photo => __('invitation.message_type_photo'),
        };
    }
}
