<?php

namespace App;

enum InvitePlatform: string
{
    case WhatsApp = 'whatsapp';
    case Viber = 'viber';
    case Telegram = 'telegram';
    case Manual = 'manual';

    public function label(): string
    {
        return match ($this) {
            self::WhatsApp => __('guests.platform_whatsapp', [], 'bs'),
            self::Viber => __('guests.platform_viber', [], 'bs'),
            self::Telegram => __('guests.platform_telegram', [], 'bs'),
            self::Manual => __('guests.platform_manual', [], 'bs'),
        };
    }
}
