<?php

namespace App\Models;

use App\Support\MediaDisk;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventPhoto extends Model
{
    use HasFactory;

    protected $fillable = [
        'wedding_event_id',
        'path',
        'sort_order',
    ];

    public function weddingEvent(): BelongsTo
    {
        return $this->belongsTo(WeddingEvent::class);
    }

    public function getUrlAttribute(): string
    {
        return MediaDisk::url($this->path) ?? '';
    }
}
