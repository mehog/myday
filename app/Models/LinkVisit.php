<?php

namespace App\Models;

use App\LinkType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LinkVisit extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'wedding_event_id',
        'guest_id',
        'link_type',
        'ip_hash',
        'user_agent',
        'referer',
        'device_type',
        'browser',
        'os',
        'visited_at',
    ];

    protected function casts(): array
    {
        return [
            'link_type' => LinkType::class,
            'visited_at' => 'datetime',
        ];
    }

    public function weddingEvent(): BelongsTo
    {
        return $this->belongsTo(WeddingEvent::class);
    }

    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class);
    }
}
