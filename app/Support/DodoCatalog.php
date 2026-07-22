<?php

namespace App\Support;

use App\PlanTier;
use App\PricingRegion;
use InvalidArgumentException;

class DodoCatalog
{
    public static function mode(): string
    {
        $mode = (string) config('dodo.mode', 'test');

        return in_array($mode, ['test', 'live'], true) ? $mode : 'test';
    }

    public static function productId(PricingRegion $region, PlanTier $tier, ?string $mode = null): string
    {
        $mode ??= self::mode();
        $productId = config("dodo.products.{$mode}.{$region->value}.{$tier->value}");

        if (! is_string($productId) || $productId === '') {
            throw new InvalidArgumentException(
                "Missing Dodo product ID for {$mode}/{$region->value}/{$tier->value}."
            );
        }

        return $productId;
    }

    /**
     * @return array{tier: PlanTier, region: PricingRegion}|null
     */
    public static function resolveProduct(string $productId, ?string $mode = null): ?array
    {
        $mode ??= self::mode();
        $catalog = config("dodo.products.{$mode}", []);

        foreach (PricingRegion::cases() as $region) {
            foreach (PlanTier::cases() as $tier) {
                if (($catalog[$region->value][$tier->value] ?? null) === $productId) {
                    return [
                        'tier' => $tier,
                        'region' => $region,
                    ];
                }
            }
        }

        return null;
    }

    /**
     * @return list<array{
     *     tier: PlanTier,
     *     product_id: string,
     *     price: int,
     *     currency: string,
     *     guest_limit: ?int,
     *     highlighted: bool
     * }>
     */
    public static function plansForRegion(PricingRegion $region): array
    {
        $plans = [];

        foreach (PlanTier::ordered() as $tier) {
            $plans[] = [
                'tier' => $tier,
                'product_id' => self::productId($region, $tier),
                'price' => $region->priceFor($tier),
                'currency' => $region->currency(),
                'guest_limit' => $tier->guestLimit(),
                'highlighted' => $tier->isHighlighted(),
            ];
        }

        return $plans;
    }
}
