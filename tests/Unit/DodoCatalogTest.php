<?php

namespace Tests\Unit;

use App\PlanFeature;
use App\PlanTier;
use App\PricingRegion;
use App\Support\DodoCatalog;
use InvalidArgumentException;
use Tests\TestCase;

class DodoCatalogTest extends TestCase
{
    public function test_resolves_purchasable_and_legacy_deluxe_products(): void
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
            foreach ([PlanTier::Basic, PlanTier::Plus, PlanTier::Premium, PlanTier::Deluxe] as $tier) {
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

    public function test_plans_for_region_excludes_free_and_deluxe(): void
    {
        $plans = DodoCatalog::plansForRegion(PricingRegion::FirstWorld);

        $this->assertCount(3, $plans);
        $this->assertSame(
            [PlanTier::Basic, PlanTier::Plus, PlanTier::Premium],
            array_map(fn (array $plan): PlanTier => $plan['tier'], $plans),
        );
        $this->assertSame(250, $plans[1]['guest_limit']);
        $this->assertNull($plans[2]['guest_limit']);
    }

    public function test_display_plans_include_free(): void
    {
        $plans = DodoCatalog::displayPlansForRegion(PricingRegion::FirstWorld);

        $this->assertCount(4, $plans);
        $this->assertSame(PlanTier::Free, $plans[0]['tier']);
        $this->assertSame(0, $plans[0]['price']);
        $this->assertSame(50, $plans[0]['guest_limit']);
    }

    public function test_free_has_no_product_id(): void
    {
        $this->expectException(InvalidArgumentException::class);

        DodoCatalog::productId(PricingRegion::FirstWorld, PlanTier::Free);
    }

    public function test_regional_prices_and_currencies(): void
    {
        $this->assertSame('EUR', PricingRegion::FirstWorld->currency());
        $this->assertSame('BAM', PricingRegion::ThirdWorld->currency());
        $this->assertSame(80, PricingRegion::FirstWorld->priceFor(PlanTier::Basic));
        $this->assertSame(0, PricingRegion::FirstWorld->priceFor(PlanTier::Free));
        $this->assertSame(320, PricingRegion::ThirdWorld->priceFor(PlanTier::Deluxe));
    }

    public function test_unknown_product_id_returns_null(): void
    {
        $this->assertNull(DodoCatalog::resolveProduct('pdt_unknown'));
    }

    public function test_plan_feature_gates(): void
    {
        $this->assertFalse(PlanTier::Free->hasFeature(PlanFeature::SeatingPdfExport));
        $this->assertFalse(PlanTier::Free->hasFeature(PlanFeature::PushSend));
        $this->assertFalse(PlanTier::Free->hasFeature(PlanFeature::QrPhotoAlbum));
        $this->assertTrue(PlanTier::Basic->hasFeature(PlanFeature::SeatingPdfExport));
        $this->assertTrue(PlanTier::Premium->hasFeature(PlanFeature::PushSend));
        $this->assertTrue(PlanTier::Deluxe->hasFeature(PlanFeature::QrPhotoAlbum));
    }
}
