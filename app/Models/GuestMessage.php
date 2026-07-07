<?php

namespace App\Models;

use App\GuestMessageType;
use App\GuestMessageVisitMatch;
use App\Support\MediaDisk;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GuestMessage extends Model
{
    protected $fillable = [
        'wedding_event_id',
        'guest_id',
        'sender_name',
        'ip_hash',
        'user_agent',
        'device_type',
        'browser',
        'os',
        'type',
        'content',
        'file_path',
        'file_paths',
        'seen_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => GuestMessageType::class,
            'file_paths' => 'array',
            'seen_at' => 'datetime',
        ];
    }

    public function scopeUnseen(Builder $query): Builder
    {
        return $query->whereNull('seen_at');
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

    public function hasFingerprint(): bool
    {
        return $this->ip_hash !== null
            || $this->user_agent !== null
            || $this->device_type !== null
            || $this->browser !== null
            || $this->os !== null;
    }

    public function deviceSummary(): ?string
    {
        $parts = array_filter([
            $this->browser,
            $this->os,
            $this->device_type,
        ]);

        return $parts === [] ? null : implode(' / ', $parts);
    }

    public function visitMatch(): GuestMessageVisitMatch
    {
        $visit = $this->guest?->latestPersonalLinkVisit;

        if (! $visit) {
            return GuestMessageVisitMatch::Unknown;
        }

        if (
            $this->user_agent !== null
            && $visit->user_agent !== null
            && $this->user_agent === $visit->user_agent
        ) {
            return GuestMessageVisitMatch::Match;
        }

        if (
            $this->browser !== null
            && $this->os !== null
            && $this->device_type !== null
            && $this->browser === $visit->browser
            && $this->os === $visit->os
            && $this->device_type === $visit->device_type
        ) {
            return GuestMessageVisitMatch::Soft;
        }

        if (
            $this->ip_hash !== null
            && $visit->ip_hash !== null
            && $this->ip_hash === $visit->ip_hash
        ) {
            return GuestMessageVisitMatch::Soft;
        }

        if ($this->hasFingerprint() && ($visit->ip_hash !== null || $visit->browser !== null)) {
            return GuestMessageVisitMatch::Mismatch;
        }

        return GuestMessageVisitMatch::Unknown;
    }
}
