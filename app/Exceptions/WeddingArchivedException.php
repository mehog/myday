<?php

namespace App\Exceptions;

use App\Models\WeddingEvent;
use RuntimeException;

class WeddingArchivedException extends RuntimeException
{
    public function __construct(
        public readonly WeddingEvent $weddingEvent,
    ) {
        parent::__construct(
            "Wedding #{$weddingEvent->id} is archived and can no longer be modified by the couple."
        );
    }
}
