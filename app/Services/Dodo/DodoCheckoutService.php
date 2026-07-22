<?php

namespace App\Services\Dodo;

use App\Models\DodoPayment;
use App\Models\User;
use App\PlanTier;
use App\Support\DodoCatalog;
use Dodopayments\CheckoutSessions\CheckoutSessionResponse;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class DodoCheckoutService
{
    public function __construct(
        private readonly DodoClientFactory $clientFactory,
    ) {}

    /**
     * @return array{checkout_url: string, payment: DodoPayment}
     */
    public function createCheckout(User $user, PlanTier $tier): array
    {
        $wedding = $user->weddingEvent;

        if ($wedding === null) {
            throw ValidationException::withMessages([
                'tier' => __('pricing.error_no_wedding'),
            ]);
        }

        if (! $wedding->canPurchaseTier($tier)) {
            throw ValidationException::withMessages([
                'tier' => __('pricing.error_tier_unavailable'),
            ]);
        }

        $region = $user->pricingRegion();
        $productId = DodoCatalog::productId($region, $tier);
        $amount = $region->priceFor($tier);
        $currency = $region->currency();

        $returnUrl = config('dodo.return_url') ?: url('/app/pricing?checkout=return');
        $cancelUrl = config('dodo.cancel_url') ?: url('/app/pricing?checkout=cancel');

        $metadata = [
            'user_id' => (string) $user->id,
            'wedding_event_id' => (string) $wedding->id,
            'plan_tier' => $tier->value,
            'pricing_region' => $region->value,
        ];

        $payment = DodoPayment::query()->create([
            'user_id' => $user->id,
            'wedding_event_id' => $wedding->id,
            'plan_tier' => $tier,
            'pricing_region' => $region,
            'currency' => $currency,
            'amount' => $amount,
            'status' => 'pending',
            'dodo_product_id' => $productId,
            'metadata' => $metadata,
        ]);

        $session = $this->createSession(
            productId: $productId,
            user: $user,
            returnUrl: $returnUrl,
            cancelUrl: $cancelUrl,
            metadata: array_merge($metadata, [
                'local_payment_id' => (string) $payment->id,
            ]),
            billingCurrency: $currency,
        );

        $checkoutUrl = $session->checkoutURL;

        if (! is_string($checkoutUrl) || $checkoutUrl === '') {
            throw new RuntimeException('Dodo checkout session did not return a checkout URL.');
        }

        $payment->forceFill([
            'dodo_checkout_session_id' => $session->sessionID,
            'metadata' => array_merge($metadata, [
                'local_payment_id' => (string) $payment->id,
            ]),
        ])->save();

        return [
            'checkout_url' => $checkoutUrl,
            'payment' => $payment->fresh(),
        ];
    }

    /**
     * @param  array<string, string>  $metadata
     */
    protected function createSession(
        string $productId,
        User $user,
        string $returnUrl,
        string $cancelUrl,
        array $metadata,
        string $billingCurrency,
    ): CheckoutSessionResponse {
        $client = $this->clientFactory->make();

        return $client->checkoutSessions->create(
            productCart: [
                [
                    'productID' => $productId,
                    'quantity' => 1,
                ],
            ],
            customer: [
                'email' => $user->email,
                'name' => $user->name,
            ],
            billingCurrency: $billingCurrency,
            cancelURL: $cancelUrl,
            metadata: $metadata,
            returnURL: $returnUrl,
        );
    }
}
