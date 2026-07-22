<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WeddingEvent;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;
use Tests\Concerns\RefreshInMemoryDatabase;
use Tests\TestCase;

class TrackUserIpOnVerificationTest extends TestCase
{
    use RefreshInMemoryDatabase;

    public function test_production_verification_stores_ipstack_data(): void
    {
        Config::set('app.env', 'production');
        Config::set('services.ipstack.access_key', 'test-access-key');

        $ip = '203.0.113.10';

        Http::fake([
            'api.ipstack.com/*' => Http::response([
                'ip' => $ip,
                'country_code' => 'US',
                'country_name' => 'United States',
                'city' => 'New York',
                'location' => [
                    'country_flag_emoji' => '🇺🇸',
                ],
            ]),
        ]);

        $user = User::factory()->unverified()->create();
        WeddingEvent::factory()->inactive()->create(['user_id' => $user->id]);

        $this->withServerVariables(['REMOTE_ADDR' => $ip])
            ->get($this->verificationUrl($user))
            ->assertRedirect('/app/pricing');

        $user->refresh();

        $this->assertSame($ip, $user->signup_ip);
        $this->assertSame('US', $user->signupCountryCode());
        $this->assertSame('EUR', $user->pricingCurrency());
        $this->assertSame('United States', $user->signup_ipstack->country_name ?? null);

        Http::assertSent(fn ($request): bool => str_contains($request->url(), 'api.ipstack.com/'.$ip));
    }

    public function test_non_production_verification_skips_ipstack_api(): void
    {
        Config::set('app.env', 'local');
        Config::set('services.ipstack.access_key', 'test-access-key');

        Http::fake();

        $user = User::factory()->unverified()->create();
        WeddingEvent::factory()->inactive()->create(['user_id' => $user->id]);

        $this->get($this->verificationUrl($user))->assertRedirect('/app/pricing');

        $user->refresh();

        $this->assertNull($user->signup_ip);
        $this->assertNull($user->signup_ipstack);
        $this->assertSame('BAM', $user->pricingCurrency());

        Http::assertNothingSent();
    }

    public function test_production_verification_saves_ip_when_api_fails(): void
    {
        Config::set('app.env', 'production');
        Config::set('services.ipstack.access_key', 'test-access-key');

        $ip = '203.0.113.20';

        Http::fake([
            'api.ipstack.com/*' => Http::response(['error' => 'invalid'], 500),
        ]);

        $user = User::factory()->unverified()->create();
        WeddingEvent::factory()->inactive()->create(['user_id' => $user->id]);

        $this->withServerVariables(['REMOTE_ADDR' => $ip])
            ->get($this->verificationUrl($user))
            ->assertRedirect('/app/pricing');

        $user->refresh();

        $this->assertSame($ip, $user->signup_ip);
        $this->assertNull($user->signup_ipstack);
        $this->assertSame('BAM', $user->pricingCurrency());
    }

    private function verificationUrl(User $user): string
    {
        return URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => $user->getKey(),
                'hash' => sha1($user->getEmailForVerification()),
            ],
        );
    }
}
