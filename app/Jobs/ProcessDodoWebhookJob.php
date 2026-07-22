<?php

namespace App\Jobs;

use App\Models\DodoWebhookEvent;
use App\Services\Dodo\DodoClientFactory;
use App\Services\Dodo\DodoWebhookProcessor;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProcessDodoWebhookJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly int $webhookEventId,
        public readonly string $rawBody,
        /** @var array<string, string> */
        public readonly array $headers,
    ) {}

    public function handle(
        DodoClientFactory $clientFactory,
        DodoWebhookProcessor $processor,
    ): void {
        $webhookEvent = DodoWebhookEvent::query()->find($this->webhookEventId);

        if ($webhookEvent === null || $webhookEvent->status === 'processed') {
            return;
        }

        try {
            $client = $clientFactory->make();
            $event = $client->webhooks->unwrap(
                body: $this->rawBody,
                headers: $this->headers,
                secret: (string) config('dodo.webhook_secret'),
            );

            $webhookEvent->forceFill([
                'event_type' => $event->type ?? $webhookEvent->event_type,
            ])->save();

            $processor->process($webhookEvent, $event);
        } catch (Throwable $e) {
            Log::warning('Dodo webhook job failed', [
                'webhook_event_id' => $this->webhookEventId,
                'message' => $e->getMessage(),
            ]);

            $webhookEvent->markFailed($e->getMessage());

            throw $e;
        }
    }
}
