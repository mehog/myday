<?php

namespace App\Support;

use App\Models\WeddingEvent;

class DraftWeddingEvent extends WeddingEvent
{
    public ?string $draftHeroUrl = null;

    public function getHeroImageUrlAttribute(): ?string
    {
        if (filled($this->draftHeroUrl)) {
            return $this->draftHeroUrl;
        }

        return parent::getHeroImageUrlAttribute();
    }
}
