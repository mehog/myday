<?php

namespace Tests\Feature;

use App\Models\DodoPayment;
use App\Models\Guest;
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
            ->assertSee('BAM');
    }

    public function test_checkout_rejects_tier_too_small_for_guest_count(): void
    {
        $user = User::factory()->create();
        $wedding = WeddingEvent::factory()->inactive()->create(['user_id' => $user->id]);
        Guest::factory()->count(120)->create(['wedding_event_id' => $wedding->id]);

        $this->expectException(ValidationException::class);
        app(DodoCheckoutService::class)->createCheckout($user, PlanTier::Basic);
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
            ) use ($session, $user, $wedding) {
                $this->assertSame('pdt_fw_basic', $productId);
                $this->assertSame($user->id, $checkoutUser->id);
                $this->assertSame((string) $user->id, $metadata['user_id']);
                $this->assertSame((string) $wedding->id, $metadata['wedding_event_id']);
                $this->assertSame('basic', $metadata['plan_tier']);
                $this->assertSame('first_world', $metadata['pricing_region']);
                $this->assertSame('EUR', $billingCurrency);

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

    public function test_checkout_route_rejects_invalid_tier(): void
    {
        $user = User::factory()->create();
        WeddingEvent::factory()->inactive()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->post(route('dodo.checkout'), ['tier' => 'enterprise'])
            ->assertSessionHasErrors('tier');
    }

    public function test_landing_page_no_longer_includes_pricing_section(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertDontSee('id="cijene"', false);
    }
}
