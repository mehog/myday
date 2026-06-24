<?php

namespace App\Models;

use App\GuestMessageType;
use App\Support\MediaDisk;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GuestMessage extends Model
{
    protected $fillable = [
        'wedding_event_id',
        'guest_id',
        'sender_name',
        'type',
        'content',
        'file_path',
        'file_paths',
    ];

    protected function casts(): array
    {
        return [
            'type' => GuestMessageType::class,
            'file_paths' => 'array',
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

    public function fileUrl(): ?string
    {
        return MediaDisk::url($this->file_path);
    }

    /**
     * @return array<int, string>
     */
    public function fileUrls(): array
    {
        return collect($this->file_paths ?? [])
            ->map(fn (string $path) => MediaDisk::url($path))
            ->filter()
            ->values()
            ->all();
    }
}
