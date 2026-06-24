<?php

namespace App\Models;

use App\InvitationTheme;
use App\LinkMode;
use App\Support\MediaDisk;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class WeddingEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
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
        'is_demo',
        'send_message',
        'motto',
    ];

    protected function casts(): array
    {
        return [
            'wedding_date' => 'datetime',
            'rsvp_deadline' => 'date',
            'is_active' => 'boolean',
            'is_demo' => 'boolean',
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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

    public function linkVisits(): HasMany
    {
        return $this->hasMany(LinkVisit::class);
    }

    public function pushNotificationLogs(): HasMany
    {
        return $this->hasMany(PushNotificationLog::class);
    }

    public function guestMessages(): HasMany
    {
        return $this->hasMany(GuestMessage::class)->latest();
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

    public function googleCalendarUrl(): string
    {
        $start = $this->wedding_date->format('Ymd');
        $end = $this->wedding_date->copy()->addDay()->format('Ymd');
        $text = urlencode(__('invitation.save_the_date').' — '.$this->couple_names);
        $loc = urlencode(trim("{$this->location_name} {$this->location_address}"));

        return 'https://calendar.google.com/calendar/render?action=TEMPLATE'
            ."&text={$text}&dates={$start}/{$end}&location={$loc}";
    }

    public function composeSendMessage(Guest $guest): string
    {
        return str_replace(
            ['{name}', '{link}'],
            [$guest->name, $guest->personal_url],
            $this->send_message ?? "Dragi {$guest->name},\n{$guest->personal_url}"
        );
    }

    public function requiresToken(): bool
    {
        return $this->link_mode === LinkMode::TokenOnly;
    }

    public function canBeViewedBy(?User $user): bool
    {
        if ($this->is_active) {
            return true;
        }

        if ($user === null) {
            return false;
        }

        if ($user->is_admin) {
            return true;
        }

        return $this->user_id !== null && $this->user_id === $user->id;
    }

    public function getHeroImageUrlAttribute(): ?string
    {
        return MediaDisk::url($this->hero_image);
    }

    public function getYoutubeEmbedUrlAttribute(): ?string
    {
        if (! $this->music_url) {
            return null;
        }

        $url = $this->music_url;
        $videoId = null;

        if (preg_match('#youtube\.com/embed/([a-zA-Z0-9_-]{11})#', $url, $matches)) {
            $videoId = $matches[1];
        } elseif (preg_match('#youtu\.be/([a-zA-Z0-9_-]{11})#', $url, $matches)) {
            $videoId = $matches[1];
        } elseif (preg_match('#[?&]v=([a-zA-Z0-9_-]{11})#', $url, $matches)) {
            $videoId = $matches[1];
        }

        if (! $videoId) {
            return null;
        }

        return "https://www.youtube.com/embed/{$videoId}?autoplay=0&controls=1&rel=0&modestbranding=1&fs=0&iv_load_policy=3";
    }
}
