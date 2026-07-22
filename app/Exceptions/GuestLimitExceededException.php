<?php

namespace App\Exceptions;

use App\Models\WeddingEvent;
use RuntimeException;

class GuestLimitExceededException extends RuntimeException
{
    public function __construct(
        public readonly WeddingEvent $weddingEvent,
        public readonly int $attemptedCount = 1,
    ) {
        $limit = $weddingEvent->guest_limit;
        $current = $weddingEvent->activeGuestCount();

        parent::__construct(
            "Guest limit exceeded for wedding #{$weddingEvent->id}: {$current}/{$limit} (tried to add {$attemptedCount})."
        );
    }
}
