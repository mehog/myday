<?php

namespace App\Livewire\Dashboard\Concerns;

use App\Models\WeddingEvent;

trait ManagesWeddingSettings
{
    protected function wedding(): ?WeddingEvent
    {
        return auth()->user()?->accessibleWedding();
    }

    public function isLocked(): bool
    {
        $wedding = $this->wedding();

        return $wedding instanceof WeddingEvent && $wedding->isCoupleMutationLocked();
    }

    protected function requireEditableWedding(): WeddingEvent
    {
        $wedding = $this->wedding();
        abort_unless($wedding instanceof WeddingEvent, 404);
        abort_if($wedding->isCoupleMutationLocked(), 403);

        return $wedding;
    }
}
