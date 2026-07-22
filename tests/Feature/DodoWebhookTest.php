<?php

namespace Tests\Feature;

use App\DodoPaymentStatus;
use App\Models\DodoPayment;
use App\Models\DodoWebhookEvent;
use App\Models\User;
use App\Models\WeddingEvent;
use App\PlanTier;
use App\PricingRegion;
use App\Services\Dodo\DodoWebhookProcessor;
use Dodopayments\Payments\Payment;
use Dodopayments\Webhooks\DisputeOpenedWebhookEvent;
use Dodopayments\Webhooks\PaymentSucceededWebhookEvent;
use Dodopayments\Webhooks\RefundSucceededWebhookEvent;
use Tests\Concerns\RefreshInMemoryDatabase;
use Tests\TestCase;

class DodoWebhookTest extends TestCase
{
    use RefreshInMemoryDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'dodo.mode' => 'test',
            'dodo.products.test.first_world.basic' => 'pdt_fw_basic',
            'dodo.products.test.first_world.plus' => 'pdt_fw_plus',
            'dodo.products.test.third_world.basic' => 'pdt_tw_basic',
            'dodo.products.test.third_world.plus' => 'pdt_tw_plus',
            'dodo.products.test.third_world.premium' => 'pdt_tw_premium',
            'dodo.products.test.third_world.deluxe' => 'pdt_tw_deluxe',
            'dodo.products.test.first_world.premium' => 'pdt_fw_premium',
            'dodo.products.test.first_world.deluxe' => 'pdt_fw_deluxe',
        ]);
    }

    public function test_payment_succeeded_activates_wedding_and_is_idempotent(): void
    {
        $user = User::factory()->create();
        $wedding = WeddingEvent::factory()->inactive()->create([
            'user_id' => $user->id,
            'plan_tier' => null,
            'guest_limit' => null,
        ]);

        $payment = DodoPayment::query()->create([
            'user_id' => $user->id,
            'wedding_event_id' => $wedding->id,
            'plan_tier' => PlanTier::Basic,
            'pricing_region' => PricingRegion::FirstWorld,
            'currency' => 'EUR',
            'amount' => 80,
            'status' => DodoPaymentStatus::Pending,
            'dodo_product_id' => 'pdt_fw_basic',
            'metadata' => [
                'user_id' => (string) $user->id,
                'wedding_event_id' => (string) $wedding->id,
                'plan_tier' => 'basic',
                'pricing_region' => 'first_world',
                'local_payment_id' => '1',
            ],
        ]);

        $payment->metadata = array_merge($payment->metadata, [
            'local_payment_id' => (string) $payment->id,
        ]);
        $payment->save();

        $event = $this->paymentSucceededEvent(
            paymentId: 'pay_123',
            productId: 'pdt_fw_basic',
            metadata: [
                'user_id' => (string) $user->id,
                'wedding_event_id' => (string) $wedding->id,
                'plan_tier' => 'basic',
                'pricing_region' => 'first_world',
                'local_payment_id' => (string) $payment->id,
            ],
        );

        $webhookEvent = DodoWebhookEvent::query()->create([
            'webhook_id' => 'evt_1',
            'event_type' => 'payment.succeeded',
            'status' => 'received',
        ]);

        $processor = app(DodoWebhookProcessor::class);
        $processor->process($webhookEvent, $event);
        $processor->process($webhookEvent->fresh(), $event);

        $wedding->refresh();
        $payment->refresh();

        $this->assertTrue($wedding->is_active);
        $this->assertSame(PlanTier::Basic, $wedding->plan_tier);
        $this->assertSame(100, $wedding->guest_limit);
        $this->assertSame(DodoPaymentStatus::Succeeded, $payment->status);
        $this->assertSame('pay_123', $payment->dodo_payment_id);
        $this->assertSame(1, DodoPayment::query()->count());
        $this->assertSame('processed', $webhookEvent->fresh()->status);
    }

    public function test_upgrade_keeps_higher_tier(): void
    {
        $user = User::factory()->create();
        $wedding = WeddingEvent::factory()->create([
            'user_id' => $user->id,
            'plan_tier' => PlanTier::Basic,
            'guest_limit' => 100,
            'is_active' => true,
        ]);

        $event = $this->paymentSucceededEvent(
            paymentId: 'pay_upgrade',
            productId: 'pdt_fw_plus',
            metadata: [
                'user_id' => (string) $user->id,
                'wedding_event_id' => (string) $wedding->id,
                'plan_tier' => 'plus',
                'pricing_region' => 'first_world',
            ],
        );

        $webhookEvent = DodoWebhookEvent::query()->create([
            'webhook_id' => 'evt_upgrade',
            'event_type' => 'payment.succeeded',
            'status' => 'received',
        ]);

        app(DodoWebhookProcessor::class)->process($webhookEvent, $event);

        $wedding->refresh();

        $this->assertSame(PlanTier::Plus, $wedding->plan_tier);
        $this->assertSame(200, $wedding->guest_limit);
    }

    public function test_refund_deactivates_when_no_other_succeeded_payments(): void
    {
        $user = User::factory()->create();
        $wedding = WeddingEvent::factory()->create([
            'user_id' => $user->id,
            'plan_tier' => PlanTier::Basic,
            'guest_limit' => 100,
            'is_active' => true,
        ]);

        DodoPayment::query()->create([
            'user_id' => $user->id,
            'wedding_event_id' => $wedding->id,
            'plan_tier' => PlanTier::Basic,
            'pricing_region' => PricingRegion::FirstWorld,
            'currency' => 'EUR',
            'amount' => 80,
            'status' => DodoPaymentStatus::Succeeded,
            'dodo_product_id' => 'pdt_fw_basic',
            'dodo_payment_id' => 'pay_refund_me',
            'paid_at' => now(),
        ]);

        $refundEvent = RefundSucceededWebhookEvent::with(
            businessID: 'bus_1',
            data: [
                'brandID' => 'brand_1',
                'businessID' => 'bus_1',
                'createdAt' => now(),
                'customer' => [
                    'customerID' => 'cus_1',
                    'email' => 'test@example.com',
                    'name' => 'Test User',
                ],
                'isPartial' => false,
                'metadata' => [],
                'paymentID' => 'pay_refund_me',
                'refundID' => 'ref_1',
                'status' => 'succeeded',
            ],
            timestamp: now(),
        );

        $webhookEvent = DodoWebhookEvent::query()->create([
            'webhook_id' => 'evt_refund',
            'event_type' => 'refund.succeeded',
            'status' => 'received',
        ]);

        app(DodoWebhookProcessor::class)->process($webhookEvent, $refundEvent);

        $this->assertFalse($wedding->fresh()->is_active);
        $this->assertSame(DodoPaymentStatus::Refunded, DodoPayment::query()->first()->status);
    }

    public function test_dispute_deactivates_wedding(): void
    {
        $user = User::factory()->create();
        $wedding = WeddingEvent::factory()->create([
            'user_id' => $user->id,
            'plan_tier' => PlanTier::Basic,
            'guest_limit' => 100,
            'is_active' => true,
        ]);

        DodoPayment::query()->create([
            'user_id' => $user->id,
            'wedding_event_id' => $wedding->id,
            'plan_tier' => PlanTier::Basic,
            'pricing_region' => PricingRegion::FirstWorld,
            'currency' => 'EUR',
            'amount' => 80,
            'status' => DodoPaymentStatus::Succeeded,
            'dodo_product_id' => 'pdt_fw_basic',
            'dodo_payment_id' => 'pay_disputed',
            'paid_at' => now(),
        ]);

        $disputeEvent = DisputeOpenedWebhookEvent::with(
            businessID: 'bus_1',
            data: [
                'amount' => '8000',
                'businessID' => 'bus_1',
                'createdAt' => now(),
                'currency' => 'EUR',
                'disputeID' => 'dsp_1',
                'disputeStage' => 'pre_dispute',
                'disputeStatus' => 'dispute_opened',
                'paymentID' => 'pay_disputed',
            ],
            timestamp: now(),
        );

        $webhookEvent = DodoWebhookEvent::query()->create([
            'webhook_id' => 'evt_dispute',
            'event_type' => 'dispute.opened',
            'status' => 'received',
        ]);

        app(DodoWebhookProcessor::class)->process($webhookEvent, $disputeEvent);

        $this->assertFalse($wedding->fresh()->is_active);
        $this->assertSame(DodoPaymentStatus::Disputed, DodoPayment::query()->first()->status);
    }

    /**
     * @param  array<string, string>  $metadata
     */
    private function paymentSucceededEvent(string $paymentId, string $productId, array $metadata): PaymentSucceededWebhookEvent
    {
        $payment = Payment::with(
            billing: ['country' => 'DE'],
            brandID: 'brand_1',
            businessID: 'bus_1',
            createdAt: now(),
            currency: 'EUR',
            customer: [
                'customerID' => 'cus_1',
                'email' => 'test@example.com',
                'name' => 'Test User',
            ],
            digitalProductsDelivered: false,
            disputes: [],
            isUpdatePaymentMethod: false,
            metadata: $metadata,
            paymentID: $paymentId,
            paymentProvider: 'stripe',
            refunds: [],
            retryAttempt: 0,
            settlementAmount: 8000,
            settlementCurrency: 'EUR',
            totalAmount: 8000,
            productCart: [
                ['productID' => $productId, 'quantity' => 1],
            ],
            checkoutSessionID: 'cks_1',
        );

        return PaymentSucceededWebhookEvent::with(
            businessID: 'bus_1',
            data: $payment,
            timestamp: now(),
        );
    }
}
