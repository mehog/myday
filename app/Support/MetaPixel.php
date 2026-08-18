<?php

namespace App\Support;

class MetaPixel
{
    public const EVENT_KEY = 'meta_pixel_event';

    public const PENDING_PURCHASE_KEY = 'meta_pixel_pending_purchase';

    public static function flashCompleteRegistration(): void
    {
        session()->flash(self::EVENT_KEY, [
            'name' => 'CompleteRegistration',
        ]);
    }

    public static function storePendingPurchase(int|float $value, string $currency, string $contentName): void
    {
        session([self::PENDING_PURCHASE_KEY => [
            'name' => 'Purchase',
            'params' => [
                'value' => $value,
                'currency' => $currency,
                'content_name' => $contentName,
                'content_type' => 'product',
            ],
        ]]);
    }

    public static function consumePendingPurchase(): void
    {
        $payload = session()->pull(self::PENDING_PURCHASE_KEY);

        if (! is_array($payload) || ! isset($payload['name'])) {
            return;
        }

        session()->now(self::EVENT_KEY, $payload);
    }

    public static function forgetPendingPurchase(): void
    {
        session()->forget(self::PENDING_PURCHASE_KEY);
    }

    public static function handleCheckoutQuery(?string $checkout): void
    {
        if ($checkout === 'return') {
            self::consumePendingPurchase();

            return;
        }

        if ($checkout === 'cancel') {
            self::forgetPendingPurchase();
        }
    }
}
