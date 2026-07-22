<?php

namespace App\Services\Dodo;

use App\DodoPaymentStatus;
use App\Models\DodoPayment;
use App\Models\DodoWebhookEvent;
use App\Models\User;
use App\Models\WeddingEvent;
use App\PlanTier;
use App\PricingRegion;
use App\Support\DodoCatalog;
use Dodopayments\Webhooks\DisputeOpenedWebhookEvent;
use Dodopayments\Webhooks\PaymentFailedWebhookEvent;
use Dodopayments\Webhooks\PaymentSucceededWebhookEvent;
use Dodopayments\Webhooks\RefundSucceededWebhookEvent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class DodoWebhookProcessor
{
    public function process(DodoWebhookEvent $webhookEvent, object $event): void
    {
        try {
            match (true) {
                $event instanceof PaymentSucceededWebhookEvent => $this->handlePaymentSucceeded($event),
                $event instanceof PaymentFailedWebhookEvent => $this->handlePaymentFailed($event),
                $event instanceof RefundSucceededWebhookEvent => $this->handleRefundSucceeded($event),
                $event instanceof DisputeOpenedWebhookEvent => $this->handleDisputeOpened($event),
                default => null,
            };

            $webhookEvent->markProcessed();
        } catch (Throwable $e) {
            Log::error('Dodo webhook processing failed', [
                'webhook_id' => $webhookEvent->webhook_id,
                'event_type' => $webhookEvent->event_type,
                'message' => $e->getMessage(),
            ]);

            $webhookEvent->markFailed($e->getMessage());

            throw $e;
        }
    }

    private function handlePaymentSucceeded(PaymentSucceededWebhookEvent $event): void
    {
        $paymentData = $event->data;
        $productId = $paymentData->productCart[0]->productID ?? null;
        $metadata = $this->normalizeMetadata($paymentData->metadata ?? []);

        $resolved = is_string($productId) ? DodoCatalog::resolveProduct($productId) : null;

        $tier = isset($metadata['plan_tier'])
            ? PlanTier::tryFrom((string) $metadata['plan_tier'])
            : ($resolved['tier'] ?? null);

        $region = isset($metadata['pricing_region'])
            ? PricingRegion::tryFrom((string) $metadata['pricing_region'])
            : ($resolved['region'] ?? null);

        if ($tier === null || $region === null || ! is_string($productId)) {
            throw new \RuntimeException('Unable to resolve plan tier/region for succeeded payment.');
        }

        if ($resolved !== null && ($resolved['tier'] !== $tier || $resolved['region'] !== $region)) {
            throw new \RuntimeException('Payment product/region metadata mismatch.');
        }

        $userId = isset($metadata['user_id']) ? (int) $metadata['user_id'] : null;
        $weddingId = isset($metadata['wedding_event_id']) ? (int) $metadata['wedding_event_id'] : null;
        $localPaymentId = isset($metadata['local_payment_id']) ? (int) $metadata['local_payment_id'] : null;

        $user = $userId ? User::query()->find($userId) : null;
        $wedding = $weddingId
            ? WeddingEvent::query()->find($weddingId)
            : $user?->weddingEvent;

        if ($user === null || $wedding === null || $wedding->user_id !== $user->id) {
            throw new \RuntimeException('Succeeded payment could not be matched to a wedding.');
        }

        DB::transaction(function () use ($paymentData, $productId, $tier, $region, $user, $wedding, $localPaymentId, $metadata): void {
            $payment = null;

            if ($localPaymentId) {
                $payment = DodoPayment::query()
                    ->whereKey($localPaymentId)
                    ->where('user_id', $user->id)
                    ->lockForUpdate()
                    ->first();
            }

            if ($payment === null && filled($paymentData->paymentID ?? null)) {
                $payment = DodoPayment::query()
                    ->where('dodo_payment_id', $paymentData->paymentID)
                    ->lockForUpdate()
                    ->first();
            }

            if ($payment === null) {
                $payment = DodoPayment::query()->create([
                    'user_id' => $user->id,
                    'wedding_event_id' => $wedding->id,
                    'plan_tier' => $tier,
                    'pricing_region' => $region,
                    'currency' => strtoupper((string) ($paymentData->currency ?? $region->currency())),
                    'amount' => $this->amountFromMinorUnits((int) ($paymentData->totalAmount ?? 0)),
                    'status' => DodoPaymentStatus::Succeeded,
                    'dodo_product_id' => $productId,
                    'dodo_payment_id' => $paymentData->paymentID ?? null,
                    'dodo_checkout_session_id' => $paymentData->checkoutSessionID ?? null,
                    'dodo_customer_id' => $paymentData->customer->customerID ?? null,
                    'metadata' => $metadata,
                    'payload' => $this->toArray($paymentData),
                    'paid_at' => now(),
                ]);
            } else {
                if ($payment->status === DodoPaymentStatus::Succeeded) {
                    return;
                }

                $payment->forceFill([
                    'wedding_event_id' => $wedding->id,
                    'plan_tier' => $tier,
                    'pricing_region' => $region,
                    'currency' => strtoupper((string) ($paymentData->currency ?? $payment->currency)),
                    'amount' => $this->amountFromMinorUnits((int) ($paymentData->totalAmount ?? ($payment->amount * 100))),
                    'status' => DodoPaymentStatus::Succeeded,
                    'dodo_product_id' => $productId,
                    'dodo_payment_id' => $paymentData->paymentID ?? $payment->dodo_payment_id,
                    'dodo_checkout_session_id' => $paymentData->checkoutSessionID ?? $payment->dodo_checkout_session_id,
                    'dodo_customer_id' => $paymentData->customer->customerID ?? $payment->dodo_customer_id,
                    'metadata' => $metadata,
                    'payload' => $this->toArray($paymentData),
                    'paid_at' => now(),
                ])->save();
            }

            $wedding->applyPlanTier($tier);
        });
    }

    private function handlePaymentFailed(PaymentFailedWebhookEvent $event): void
    {
        $this->updatePaymentStatus($event->data, DodoPaymentStatus::Failed);
    }

    private function handleRefundSucceeded(RefundSucceededWebhookEvent $event): void
    {
        $paymentId = $event->data->paymentID ?? null;

        if (! is_string($paymentId) || $paymentId === '') {
            return;
        }

        DB::transaction(function () use ($paymentId, $event): void {
            $payment = DodoPayment::query()
                ->where('dodo_payment_id', $paymentId)
                ->lockForUpdate()
                ->first();

            if ($payment === null) {
                return;
            }

            $payment->forceFill([
                'status' => DodoPaymentStatus::Refunded,
                'payload' => $this->toArray($event->data),
            ])->save();

            $wedding = $payment->weddingEvent;

            if ($wedding !== null && ! $this->hasOtherSucceededPayments($wedding, $payment->id)) {
                $wedding->revokePaidAccess();
            }
        });
    }

    private function handleDisputeOpened(DisputeOpenedWebhookEvent $event): void
    {
        $paymentId = $event->data->paymentID ?? null;

        if (! is_string($paymentId) || $paymentId === '') {
            return;
        }

        DB::transaction(function () use ($paymentId, $event): void {
            $payment = DodoPayment::query()
                ->where('dodo_payment_id', $paymentId)
                ->lockForUpdate()
                ->first();

            if ($payment === null) {
                return;
            }

            $payment->forceFill([
                'status' => DodoPaymentStatus::Disputed,
                'payload' => $this->toArray($event->data),
            ])->save();

            $payment->weddingEvent?->revokePaidAccess();
        });
    }

    private function updatePaymentStatus(object $paymentData, DodoPaymentStatus $status): void
    {
        $paymentId = $paymentData->paymentID ?? null;

        if (! is_string($paymentId) || $paymentId === '') {
            return;
        }

        $payment = DodoPayment::query()->where('dodo_payment_id', $paymentId)->first();

        if ($payment === null) {
            $metadata = $this->normalizeMetadata($paymentData->metadata ?? []);
            $localPaymentId = isset($metadata['local_payment_id']) ? (int) $metadata['local_payment_id'] : null;

            if ($localPaymentId) {
                $payment = DodoPayment::query()->find($localPaymentId);
            }
        }

        if ($payment === null || $payment->status === DodoPaymentStatus::Succeeded) {
            return;
        }

        $payment->forceFill([
            'status' => $status,
            'dodo_payment_id' => $paymentId,
            'payload' => $this->toArray($paymentData),
        ])->save();
    }

    private function hasOtherSucceededPayments(WeddingEvent $wedding, int $exceptPaymentId): bool
    {
        return DodoPayment::query()
            ->where('wedding_event_id', $wedding->id)
            ->where('status', DodoPaymentStatus::Succeeded)
            ->whereKeyNot($exceptPaymentId)
            ->exists();
    }

    /**
     * @param  array<string, mixed>|object  $metadata
     * @return array<string, string>
     */
    private function normalizeMetadata(array|object $metadata): array
    {
        $normalized = [];

        foreach ((array) $metadata as $key => $value) {
            if (is_scalar($value) || $value === null) {
                $normalized[(string) $key] = (string) $value;
            }
        }

        return $normalized;
    }

    private function amountFromMinorUnits(int $minorUnits): int
    {
        return (int) round($minorUnits / 100);
    }

    /**
     * @return array<string, mixed>
     */
    private function toArray(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (is_object($value) && method_exists($value, 'toArray')) {
            return $value->toArray();
        }

        return json_decode(json_encode($value) ?: '{}', true) ?: [];
    }
}
