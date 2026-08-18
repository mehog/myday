<?php

namespace Tests\Unit;

use App\Support\MetaPixel;
use Tests\TestCase;

class MetaPixelTest extends TestCase
{
    public function test_consume_pending_purchase_moves_payload_to_current_request(): void
    {
        $payload = [
            'name' => 'Purchase',
            'params' => [
                'value' => 80,
                'currency' => 'EUR',
                'content_name' => 'basic',
                'content_type' => 'product',
            ],
        ];

        session([MetaPixel::PENDING_PURCHASE_KEY => $payload]);

        MetaPixel::consumePendingPurchase();

        $this->assertNull(session(MetaPixel::PENDING_PURCHASE_KEY));
        $this->assertSame($payload, session(MetaPixel::EVENT_KEY));
    }

    public function test_consume_pending_purchase_is_noop_when_empty(): void
    {
        MetaPixel::consumePendingPurchase();

        $this->assertNull(session(MetaPixel::EVENT_KEY));
    }

    public function test_cancel_forgets_pending_purchase_without_event(): void
    {
        session([MetaPixel::PENDING_PURCHASE_KEY => [
            'name' => 'Purchase',
            'params' => [
                'value' => 80,
                'currency' => 'EUR',
                'content_name' => 'basic',
                'content_type' => 'product',
            ],
        ]]);

        MetaPixel::handleCheckoutQuery('cancel');

        $this->assertNull(session(MetaPixel::PENDING_PURCHASE_KEY));
        $this->assertNull(session(MetaPixel::EVENT_KEY));
    }
}
