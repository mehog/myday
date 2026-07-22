<?php

namespace App\Services\Dodo;

use Dodopayments\Client;
use RuntimeException;

class DodoClientFactory
{
    public function make(): Client
    {
        $apiKey = (string) config('dodo.api_key');

        if ($apiKey === '') {
            throw new RuntimeException('DODO_PAYMENTS_API_KEY is not configured.');
        }

        return new Client(
            bearerToken: $apiKey,
            webhookKey: (string) config('dodo.webhook_secret'),
            baseUrl: (string) config('dodo.base_url'),
        );
    }
}
