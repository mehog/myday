<?php

namespace Tests\Unit;

use App\Models\User;
use App\PricingRegion;
use Tests\TestCase;

class UserPricingCurrencyTest extends TestCase
{
    public function test_first_world_countries_use_eur_pricing(): void
    {
        foreach (User::FIRST_WORLD_COUNTRIES as $countryCode) {
            $user = new User([
                'signup_ipstack' => (object) [
                    'country_code' => $countryCode,
                ],
            ]);

            $this->assertTrue($user->isFromFirstWorldCountry());
            $this->assertSame(PricingRegion::FirstWorld, $user->pricingRegion());
            $this->assertSame('EUR', $user->pricingCurrency());
        }
    }

    public function test_third_world_countries_use_bam_pricing(): void
    {
        foreach (['BA', 'RS', 'ME', 'IN', 'BR'] as $countryCode) {
            $user = new User([
                'signup_ipstack' => (object) [
                    'country_code' => $countryCode,
                ],
            ]);

            $this->assertFalse($user->isFromFirstWorldCountry());
            $this->assertSame(PricingRegion::ThirdWorld, $user->pricingRegion());
            $this->assertSame('BAM', $user->pricingCurrency());
        }
    }

    public function test_missing_geo_defaults_to_third_world_bam(): void
    {
        $user = new User;

        $this->assertNull($user->signupCountryCode());
        $this->assertFalse($user->isFromFirstWorldCountry());
        $this->assertSame(PricingRegion::ThirdWorld, $user->pricingRegion());
        $this->assertSame('BAM', $user->pricingCurrency());
    }
}
