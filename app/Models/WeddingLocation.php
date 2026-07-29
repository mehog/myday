<?php

namespace App\Models;

use Database\Factories\WeddingLocationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeddingLocation extends Model
{
    /** @use HasFactory<WeddingLocationFactory> */
    use HasFactory;

    protected $fillable = [
        'wedding_event_id',
        'label',
        'name',
        'address',
        'lat',
        'lng',
        'is_primary',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'lat' => 'decimal:7',
            'lng' => 'decimal:7',
            'is_primary' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function weddingEvent(): BelongsTo
    {
        return $this->belongsTo(WeddingEvent::class);
    }

    public function displayName(): ?string
    {
        return filled($this->name) ? $this->name : (filled($this->label) ? $this->label : null);
    }

    public function mapQuery(): string
    {
        return urlencode((string) ($this->address ?: $this->name ?: $this->label ?: ''));
    }

    public function mapEmbedUrl(): string
    {
        if ($this->lat !== null && $this->lng !== null) {
            return "https://maps.google.com/maps?q={$this->lat},{$this->lng}&z=15&output=embed";
        }

        return "https://maps.google.com/maps?q={$this->mapQuery()}&z=15&output=embed";
    }

    public function directionsUrl(): ?string
    {
        if ($this->lat !== null && $this->lng !== null) {
            return "https://www.google.com/maps/dir/?api=1&destination={$this->lat},{$this->lng}";
        }

        $query = $this->address ?: $this->name ?: $this->label;

        if (! filled($query)) {
            return null;
        }

        return 'https://www.google.com/maps/dir/?api=1&destination='.urlencode((string) $query);
    }

    public function calendarLocation(): string
    {
        return trim(collect([$this->name, $this->address])->filter()->implode(' '));
    }

    public function hasMapContent(): bool
    {
        return filled($this->name)
            || filled($this->address)
            || filled($this->label)
            || $this->lat !== null
            || $this->lng !== null;
    }
}
