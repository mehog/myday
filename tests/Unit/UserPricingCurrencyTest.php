<?php

namespace Tests\Unit;

use App\Models\User;
use App\PricingRegion;
use Tests\TestCase;

class UserPricingCurrencyTest extends TestCase
{
    public function test_bosnia_uses_bam_pricing(): void
    {
        $user = new User([
            'signup_ipstack' => (object) [
                'country_code' => 'BA',
            ],
        ]);

        $this->assertTrue($user->isFromThirdWorldCountry());
        $this->assertFalse($user->isFromFirstWorldCountry());
        $this->assertSame(PricingRegion::ThirdWorld, $user->pricingRegion());
        $this->assertSame('BAM', $user->pricingCurrency());
    }

    public function test_non_bosnia_countries_use_eur_pricing(): void
    {
        foreach (['HR', 'DE', 'US', 'RS', 'ME', 'IN', 'BR'] as $countryCode) {
            $user = new User([
                'signup_ipstack' => (object) [
                    'country_code' => $countryCode,
                ],
            ]);

            $this->assertFalse($user->isFromThirdWorldCountry());
            $this->assertTrue($user->isFromFirstWorldCountry());
            $this->assertSame(PricingRegion::FirstWorld, $user->pricingRegion());
            $this->assertSame('EUR', $user->pricingCurrency());
        }
    }

    public function test_missing_geo_defaults_to_eur(): void
    {
        $user = new User;

        $this->assertNull($user->signupCountryCode());
        $this->assertFalse($user->isFromThirdWorldCountry());
        $this->assertTrue($user->isFromFirstWorldCountry());
        $this->assertSame(PricingRegion::FirstWorld, $user->pricingRegion());
        $this->assertSame('EUR', $user->pricingCurrency());
    }

    public function test_visitor_region_uses_ip_country_not_locale(): void
    {
        $this->assertSame(PricingRegion::ThirdWorld, PricingRegion::fromCountryCode('BA'));
        $this->assertSame(PricingRegion::FirstWorld, PricingRegion::fromCountryCode('DE'));
        $this->assertSame(PricingRegion::FirstWorld, PricingRegion::fromCountryCode(null));

        $this->fakeVisitorCountry('BA');
        $this->assertSame(PricingRegion::ThirdWorld, PricingRegion::forVisitor(request()));

        $this->fakeVisitorCountry('US', '203.0.113.20');
        $this->assertSame(PricingRegion::FirstWorld, PricingRegion::forVisitor(request()));
    }
}
