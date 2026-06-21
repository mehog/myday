<?php

namespace App\Models;

use App\InvitationTheme;
use App\LinkMode;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class WeddingEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'bride_name',
        'groom_name',
        'wedding_date',
        'location_name',
        'location_address',
        'location_lat',
        'location_lng',
        'theme',
        'link_mode',
        'music_url',
        'hero_image',
        'rsvp_deadline',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'wedding_date' => 'datetime',
            'rsvp_deadline' => 'date',
            'is_active' => 'boolean',
            'theme' => InvitationTheme::class,
            'link_mode' => LinkMode::class,
            'location_lat' => 'decimal:7',
            'location_lng' => 'decimal:7',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (WeddingEvent $event) {
            if (empty($event->slug)) {
                $event->slug = Str::slug($event->groom_name.'-'.$event->bride_name);
            }
        });
    }

    public function guests(): HasMany
    {
        return $this->hasMany(Guest::class);
    }

    public function scheduleItems(): HasMany
    {
        return $this->hasMany(ScheduleItem::class)->orderBy('sort_order');
    }

    public function eventPhotos(): HasMany
    {
        return $this->hasMany(EventPhoto::class)->orderBy('sort_order');
    }

    public function getCoupleNamesAttribute(): string
    {
        return "{$this->groom_name} & {$this->bride_name}";
    }

    public function getPublicUrlAttribute(): string
    {
        return url("/e/{$this->slug}");
    }

    public function guestUrl(Guest $guest): string
    {
        return url("/e/{$this->slug}/{$guest->token}");
    }

    public function requiresToken(): bool
    {
        return $this->link_mode === LinkMode::TokenOnly;
    }
}
