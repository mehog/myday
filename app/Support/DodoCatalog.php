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
        if (! $tier->isPurchasable() && $tier !== PlanTier::Deluxe) {
            throw new InvalidArgumentException(
                "Plan tier {$tier->value} has no Dodo product ID."
            );
        }

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
                if ($tier === PlanTier::Free) {
                    continue;
                }

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
     * Purchasable paid plans for checkout / app pricing page.
     *
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

        foreach (PlanTier::purchasable() as $tier) {
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

    /**
     * Public marketing plans including Free (no product IDs).
     *
     * @return list<array{
     *     tier: PlanTier,
     *     price: int,
     *     currency: string,
     *     guest_limit: ?int,
     *     highlighted: bool
     * }>
     */
    public static function displayPlansForRegion(PricingRegion $region): array
    {
        $plans = [];

        foreach (PlanTier::orderedForDisplay() as $tier) {
            $plans[] = [
                'tier' => $tier,
                'price' => $region->priceFor($tier),
                'currency' => $region->currency(),
                'guest_limit' => $tier->guestLimit(),
                'highlighted' => $tier->isHighlighted(),
            ];
        }

        return $plans;
    }
}
