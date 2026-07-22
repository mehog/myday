<?php

namespace Tests\Unit;

use App\PlanTier;
use App\PricingRegion;
use App\Support\DodoCatalog;
use Tests\TestCase;

class DodoCatalogTest extends TestCase
{
    public function test_resolves_all_eight_test_catalog_choices(): void
    {
        config([
            'dodo.mode' => 'test',
            'dodo.products.test' => [
                'first_world' => [
                    'basic' => 'pdt_fw_basic',
                    'plus' => 'pdt_fw_plus',
                    'premium' => 'pdt_fw_premium',
                    'deluxe' => 'pdt_fw_deluxe',
                ],
                'third_world' => [
                    'basic' => 'pdt_tw_basic',
                    'plus' => 'pdt_tw_plus',
                    'premium' => 'pdt_tw_premium',
                    'deluxe' => 'pdt_tw_deluxe',
                ],
            ],
        ]);

        $plans = [];

        foreach (PricingRegion::cases() as $region) {
            foreach (PlanTier::cases() as $tier) {
                $productId = DodoCatalog::productId($region, $tier);
                $resolved = DodoCatalog::resolveProduct($productId);

                $this->assertNotNull($resolved);
                $this->assertSame($region, $resolved['region']);
                $this->assertSame($tier, $resolved['tier']);
                $plans[] = $productId;
            }
        }

        $this->assertCount(8, $plans);
        $this->assertCount(8, array_unique($plans));
    }

    public function test_regional_prices_and_currencies(): void
    {
        $this->assertSame('EUR', PricingRegion::FirstWorld->currency());
        $this->assertSame('BAM', PricingRegion::ThirdWorld->currency());
        $this->assertSame(80, PricingRegion::FirstWorld->priceFor(PlanTier::Basic));
        $this->assertSame(320, PricingRegion::ThirdWorld->priceFor(PlanTier::Deluxe));
    }

    public function test_unknown_product_id_returns_null(): void
    {
        $this->assertNull(DodoCatalog::resolveProduct('pdt_unknown'));
    }
}
