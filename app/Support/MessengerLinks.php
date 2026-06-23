<?php

namespace App\Support;

use App\Models\Guest;
use Illuminate\Support\Js;

class MessengerLinks
{
    public static function whatsApp(Guest $guest, string $message): string
    {
        $encodedMessage = rawurlencode($message);
        $phone = preg_replace('/\D/', '', $guest->phone ?? '');

        if ($phone !== '') {
            return "https://wa.me/{$phone}?text={$encodedMessage}";
        }

        return "https://wa.me/?text={$encodedMessage}";
    }

    public static function viber(string $message): string
    {
        return 'viber://forward?text='.rawurlencode($message);
    }

    public static function telegram(Guest $guest, string $message): string
    {
        return 'https://t.me/share/url?url='.rawurlencode($guest->personal_url).'&text='.rawurlencode($message);
    }

    public static function facebookMessenger(Guest $guest, string $message): string
    {
        return 'fb-messenger://share/?link='.rawurlencode($guest->personal_url);
    }

    public static function openInNewTab(string $url): string
    {
        $urlJs = Js::from($url);

        return "window.open({$urlJs}, '_blank')";
    }
}
