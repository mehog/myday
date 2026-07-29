<?php

namespace App\Models;

use App\PlatformMenu;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeddingMenuOption extends Model
{
    protected $fillable = [
        'wedding_event_id',
        'platform_key',
        'label',
        'is_visible',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'platform_key' => PlatformMenu::class,
            'is_visible' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function weddingEvent(): BelongsTo
    {
        return $this->belongsTo(WeddingEvent::class);
    }

    public function isPlatform(): bool
    {
        return $this->platform_key !== null;
    }

    public function isCustom(): bool
    {
        return ! $this->isPlatform();
    }

    public function displayLabel(): string
    {
        if ($this->platform_key instanceof PlatformMenu) {
            return $this->platform_key->label();
        }

        return (string) ($this->label ?? '');
    }
}
