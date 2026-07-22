<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessDodoWebhookJob;
use App\Models\DodoWebhookEvent;
use App\Services\Dodo\DodoClientFactory;
use Dodopayments\Core\Exceptions\WebhookException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Throwable;

class DodoWebhookController extends Controller
{
    public function __invoke(Request $request, DodoClientFactory $clientFactory): Response
    {
        $rawBody = $request->getContent();
        $headers = [
            'webhook-id' => (string) $request->header('webhook-id', ''),
            'webhook-signature' => (string) $request->header('webhook-signature', ''),
            'webhook-timestamp' => (string) $request->header('webhook-timestamp', ''),
        ];

        $webhookId = $headers['webhook-id'];

        if ($webhookId === '') {
            return response('Missing webhook-id', 400);
        }

        try {
            $clientFactory->make()->webhooks->unwrap(
                body: $rawBody,
                headers: $headers,
                secret: (string) config('dodo.webhook_secret'),
            );
        } catch (WebhookException $e) {
            Log::warning('Rejected Dodo webhook signature', [
                'webhook_id' => $webhookId,
                'message' => $e->getMessage(),
                ...$this->webhookFailureDiagnostics($e, $request, $rawBody, $headers),
            ]);

            return response('Invalid signature', 401);
        } catch (Throwable $e) {
            Log::warning('Rejected Dodo webhook', [
                'webhook_id' => $webhookId,
                'message' => $e->getMessage(),
                ...$this->webhookFailureDiagnostics($e, $request, $rawBody, $headers),
            ]);

            return response('Invalid webhook', 400);
        }

        $existing = DodoWebhookEvent::query()->where('webhook_id', $webhookId)->first();

        if ($existing?->status === 'processed') {
            return response('OK', 200);
        }

        $payload = json_decode($rawBody, true);
        $eventType = is_array($payload) ? ($payload['type'] ?? null) : null;

        $webhookEvent = $existing ?? DodoWebhookEvent::query()->create([
            'webhook_id' => $webhookId,
            'event_type' => is_string($eventType) ? $eventType : null,
            'status' => 'received',
            'payload' => is_array($payload) ? $payload : null,
        ]);

        ProcessDodoWebhookJob::dispatch(
            $webhookEvent->id,
            $rawBody,
            $headers,
        );

        return response('OK', 200);
    }

    /**
     * @param  array{webhook-id: string, webhook-signature: string, webhook-timestamp: string}  $headers
     * @return array<string, mixed>
     */
    private function webhookFailureDiagnostics(
        Throwable $e,
        Request $request,
        string $rawBody,
        array $headers,
    ): array {
        $previous = $e->getPrevious();
        $secret = (string) config('dodo.webhook_secret');
        $envSecret = (string) env('DODO_PAYMENTS_WEBHOOK_SECRET', '');
        $signature = $headers['webhook-signature'];
        $timestamp = $headers['webhook-timestamp'];
        $timestampInt = ctype_digit($timestamp) ? (int) $timestamp : null;

        return [
            'previous_message' => $previous?->getMessage(),
            'previous_class' => $previous !== null ? $previous::class : null,
            'has_webhook_id' => $headers['webhook-id'] !== '',
            'has_webhook_timestamp' => $timestamp !== '',
            'has_webhook_signature' => $signature !== '',
            'webhook_timestamp' => $timestamp !== '' ? $timestamp : null,
            'timestamp_age_seconds' => $timestampInt !== null ? time() - $timestampInt : null,
            'signature_starts_with_v1' => str_starts_with($signature, 'v1,'),
            'signature_version_count' => $signature === '' ? 0 : count(array_filter(explode(' ', $signature))),
            'body_length' => strlen($rawBody),
            'content_type' => $request->header('content-type'),
            'body_looks_like_json' => str_starts_with(ltrim($rawBody), '{'),
            'secret_configured' => $secret !== '',
            'secret_length' => strlen($secret),
            'secret_has_whsec_prefix' => str_starts_with($secret, 'whsec_'),
            'secret_has_whitespace' => $secret !== '' && preg_match('/\s/', $secret) === 1,
            'secret_equals_env' => $secret === $envSecret,
        ];
    }
}
