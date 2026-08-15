<?php

namespace Tests\Feature;

use App\Models\DodoPayment;
use App\Models\Guest;
use App\Models\Referral;
use App\Models\User;
use App\Models\WeddingEvent;
use App\PlanTier;
use App\Services\Dodo\DodoCheckoutService;
use App\Services\Dodo\DodoClientFactory;
use Dodopayments\CheckoutSessions\CheckoutSessionResponse;
use Illuminate\Validation\ValidationException;
use Mockery;
use Tests\Concerns\RefreshInMemoryDatabase;
use Tests\TestCase;

class DodoCheckoutTest extends TestCase
{
    use RefreshInMemoryDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'dodo.mode' => 'test',
            'dodo.api_key' => 'test_key',
            'dodo.webhook_secret' => 'whsec_test',
            'dodo.products.test.third_world.basic' => 'pdt_tw_basic',
            'dodo.products.test.third_world.plus' => 'pdt_tw_plus',
            'dodo.products.test.third_world.premium' => 'pdt_tw_premium',
            'dodo.products.test.third_world.deluxe' => 'pdt_tw_deluxe',
            'dodo.products.test.first_world.basic' => 'pdt_fw_basic',
            'dodo.products.test.first_world.plus' => 'pdt_fw_plus',
            'dodo.products.test.first_world.premium' => 'pdt_fw_premium',
            'dodo.products.test.first_world.deluxe' => 'pdt_fw_deluxe',
        ]);
    }

    public function test_authenticated_pricing_page_is_visible(): void
    {
        $user = User::factory()->create([
            'signup_ipstack' => (object) ['country_code' => 'BA'],
        ]);
        WeddingEvent::factory()->inactive()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->get('/app/pricing')
            ->assertOk()
            ->assertSee('Basic')
            ->assertSee('BAM')
            ->assertSee('Merchant of Record', false)
            ->assertSee(route('legal.terms'), false)
            ->assertSee(route('legal.refund'), false);
    }

    public function test_checkout_rejects_tier_too_small_for_guest_count(): void
    {
        $user = User::factory()->create();
        $wedding = WeddingEvent::factory()->create([
            'user_id' => $user->id,
            'plan_tier' => PlanTier::Premium,
            'guest_limit' => null,
            'is_active' => true,
        ]);
        Guest::factory()->count(120)->create(['wedding_event_id' => $wedding->id]);

        $wedding->forceFill([
            'plan_tier' => PlanTier::Free,
            'guest_limit' => 50,
        ])->save();

        $this->expectException(ValidationException::class);
        app(DodoCheckoutService::class)->createCheckout($user->fresh(), PlanTier::Basic);
    }

    public function test_checkout_creates_pending_payment_with_trusted_metadata(): void
    {
        $user = User::factory()->create([
            'signup_ipstack' => (object) ['country_code' => 'DE'],
        ]);
        $wedding = WeddingEvent::factory()->inactive()->create(['user_id' => $user->id]);

        $session = CheckoutSessionResponse::with(
            sessionID: 'cks_test_123',
            checkoutURL: 'https://test.dodopayments.com/checkout/cks_test_123',
        );

        $service = Mockery::mock(DodoCheckoutService::class, [app(DodoClientFactory::class)])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $service->shouldReceive('createSession')
            ->once()
            ->andReturnUsing(function (
                string $productId,
                User $checkoutUser,
                string $returnUrl,
                string $cancelUrl,
                array $metadata,
                string $billingCurrency,
                ?string $discountCode = null,
            ) use ($session, $user, $wedding) {
                $this->assertSame('pdt_fw_basic', $productId);
                $this->assertSame($user->id, $checkoutUser->id);
                $this->assertSame((string) $user->id, $metadata['user_id']);
                $this->assertSame((string) $wedding->id, $metadata['wedding_event_id']);
                $this->assertSame('basic', $metadata['plan_tier']);
                $this->assertSame('first_world', $metadata['pricing_region']);
                $this->assertSame('EUR', $billingCurrency);
                $this->assertNull($discountCode);
                $this->assertArrayNotHasKey('referral_discount_code', $metadata);

                return $session;
            });

        $this->app->instance(DodoCheckoutService::class, $service);

        $this->actingAs($user)
            ->post(route('dodo.checkout'), ['tier' => 'basic'])
            ->assertRedirect('https://test.dodopayments.com/checkout/cks_test_123');

        $payment = DodoPayment::query()->first();

        $this->assertNotNull($payment);
        $this->assertSame($user->id, $payment->user_id);
        $this->assertSame($wedding->id, $payment->wedding_event_id);
        $this->assertSame(PlanTier::Basic, $payment->plan_tier);
        $this->assertSame('EUR', $payment->currency);
        $this->assertSame('pdt_fw_basic', $payment->dodo_product_id);
        $this->assertSame('cks_test_123', $payment->dodo_checkout_session_id);
        $this->assertSame((string) $user->id, $payment->metadata['user_id'] ?? null);
    }

    public function test_checkout_applies_referral_discount_code_for_referred_user(): void
    {
        config([
            'referral.buyer_discount_code' => 'REFERRAL15',
            'referral.buyer_discount_percent' => 15,
        ]);

        $referrer = User::factory()->create();
        $referrer->createReferralAccount();

        $user = User::factory()->create([
            'signup_ipstack' => (object) ['country_code' => 'DE'],
        ]);
        WeddingEvent::factory()->inactive()->create(['user_id' => $user->id]);
        Referral::query()->create([
            'user_id' => $user->id,
            'referrer_id' => $referrer->id,
            'referral_code' => '_referred1',
        ]);

        $session = CheckoutSessionResponse::with(
            sessionID: 'cks_ref_123',
            checkoutURL: 'https://test.dodopayments.com/checkout/cks_ref_123',
        );

        $service = Mockery::mock(DodoCheckoutService::class, [app(DodoClientFactory::class)])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $service->shouldReceive('createSession')
            ->once()
            ->andReturnUsing(function (
                string $productId,
                User $checkoutUser,
                string $returnUrl,
                string $cancelUrl,
                array $metadata,
                string $billingCurrency,
                ?string $discountCode = null,
            ) use ($session) {
                $this->assertSame('REFERRAL15', $discountCode);
                $this->assertSame('REFERRAL15', $metadata['referral_discount_code'] ?? null);

                return $session;
            });

        $this->app->instance(DodoCheckoutService::class, $service);

        $this->actingAs($user)
            ->post(route('dodo.checkout'), ['tier' => 'basic'])
            ->assertRedirect('https://test.dodopayments.com/checkout/cks_ref_123');

        $payment = DodoPayment::query()->first();
        $this->assertSame('REFERRAL15', $payment?->metadata['referral_discount_code'] ?? null);
    }

    public function test_checkout_skips_referral_discount_when_code_not_configured(): void
    {
        config([
            'referral.buyer_discount_code' => null,
            'referral.buyer_discount_percent' => 15,
        ]);

        $referrer = User::factory()->create();
        $referrer->createReferralAccount();

        $user = User::factory()->create([
            'signup_ipstack' => (object) ['country_code' => 'DE'],
        ]);
        WeddingEvent::factory()->inactive()->create(['user_id' => $user->id]);
        Referral::query()->create([
            'user_id' => $user->id,
            'referrer_id' => $referrer->id,
            'referral_code' => '_referred2',
        ]);

        $session = CheckoutSessionResponse::with(
            sessionID: 'cks_ref_nocode',
            checkoutURL: 'https://test.dodopayments.com/checkout/cks_ref_nocode',
        );

        $service = Mockery::mock(DodoCheckoutService::class, [app(DodoClientFactory::class)])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $service->shouldReceive('createSession')
            ->once()
            ->andReturnUsing(function (
                string $productId,
                User $checkoutUser,
                string $returnUrl,
                string $cancelUrl,
                array $metadata,
                string $billingCurrency,
                ?string $discountCode = null,
            ) use ($session) {
                $this->assertNull($discountCode);
                $this->assertArrayNotHasKey('referral_discount_code', $metadata);

                return $session;
            });

        $this->app->instance(DodoCheckoutService::class, $service);

        $this->actingAs($user)
            ->post(route('dodo.checkout'), ['tier' => 'basic'])
            ->assertRedirect('https://test.dodopayments.com/checkout/cks_ref_nocode');
    }

    public function test_pricing_page_shows_referral_discount_for_referred_user(): void
    {
        config([
            'referral.buyer_discount_percent' => 15,
            'referral.buyer_discount_code' => 'REFERRAL15',
        ]);

        $referrer = User::factory()->create();
        $referrer->createReferralAccount();

        $user = User::factory()->create([
            'signup_ipstack' => (object) ['country_code' => 'DE'],
        ]);
        WeddingEvent::factory()->inactive()->create(['user_id' => $user->id]);
        Referral::query()->create([
            'user_id' => $user->id,
            'referrer_id' => $referrer->id,
            'referral_code' => '_referred3',
        ]);

        $this->actingAs($user)
            ->get('/app/pricing')
            ->assertOk()
            ->assertSee(__('pricing.referral_discount_applied', ['percent' => 15]), false)
            ->assertSee('68 EUR', false)
            ->assertSee('80 EUR', false);
    }

    public function test_checkout_route_rejects_invalid_tier(): void
    {
        $user = User::factory()->create();
        WeddingEvent::factory()->inactive()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->post(route('dodo.checkout'), ['tier' => 'enterprise'])
            ->assertSessionHasErrors('tier');
    }

    public function test_landing_page_includes_public_pricing_section(): void
    {
        $this->get('/?locale=en')
            ->assertOk()
            ->assertSee('id="cijene"', false)
            ->assertSee('One-time payment', false)
            ->assertSee('Free forever', false)
            ->assertSee('Up to 50 guests', false);
    }
}
