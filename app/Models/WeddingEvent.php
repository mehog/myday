<?php

namespace App\Models;

use App\InvitationReveal;
use App\InvitationTemplate;
use App\InvitationTheme;
use App\LinkMode;
use App\PlanTier;
use App\Support\MediaDisk;
use Database\Factories\WeddingEventFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class WeddingEvent extends Model
{
    /** @use HasFactory<WeddingEventFactory> */
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
        'template',
        'reveal_animation',
        'link_mode',
        'music_url',
        'hero_image',
        'rsvp_deadline',
        'is_active',
        'is_demo',
        'plan_tier',
        'guest_limit',
        'send_message',
        'motto',
        'seating_plan',
    ];

    protected function casts(): array
    {
        return [
            'wedding_date' => 'datetime',
            'rsvp_deadline' => 'date',
            'is_active' => 'boolean',
            'is_demo' => 'boolean',
            'plan_tier' => PlanTier::class,
            'guest_limit' => 'integer',
            'theme' => InvitationTheme::class,
            'template' => InvitationTemplate::class,
            'reveal_animation' => InvitationReveal::class,
            'link_mode' => LinkMode::class,
            'location_lat' => 'decimal:7',
            'location_lng' => 'decimal:7',
            'seating_plan' => 'array',
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

    public function dodoPayments(): HasMany
    {
        return $this->hasMany(DodoPayment::class);
    }

    public function getCoupleNamesAttribute(): string
    {
        return "{$this->groom_name} & {$this->bride_name}";
    }

    public function hasPaidPlan(): bool
    {
        return $this->plan_tier !== null;
    }

    public function activeGuestCount(): int
    {
        return $this->guests()->count();
    }

    public function remainingGuestSlots(): ?int
    {
        if (! $this->hasPaidPlan()) {
            return null;
        }

        if ($this->guest_limit === null) {
            return null;
        }

        return max(0, $this->guest_limit - $this->activeGuestCount());
    }

    public function canAddGuests(int $count = 1): bool
    {
        if ($count < 1) {
            return false;
        }

        if (! $this->hasPaidPlan()) {
            return true;
        }

        if ($this->guest_limit === null) {
            return true;
        }

        return ($this->activeGuestCount() + $count) <= $this->guest_limit;
    }

    public function requiredTierForCurrentGuests(): PlanTier
    {
        return PlanTier::minimumForGuestCount($this->activeGuestCount());
    }

    public function canPurchaseTier(PlanTier $tier): bool
    {
        if (! $tier->coversGuestCount($this->activeGuestCount())) {
            return false;
        }

        if ($this->plan_tier === null) {
            return true;
        }

        return $tier->sortOrder() > $this->plan_tier->sortOrder();
    }

    public function applyPlanTier(PlanTier $tier): void
    {
        if ($this->plan_tier !== null && ! $tier->isAtLeast($this->plan_tier)) {
            return;
        }

        $this->forceFill([
            'plan_tier' => $tier,
            'guest_limit' => $tier->guestLimit(),
            'is_active' => true,
        ])->save();
    }

    public function revokePaidAccess(): void
    {
        $this->forceFill([
            'is_active' => false,
        ])->save();
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

    public function isOwnedBy(?User $user): bool
    {
        return $user !== null
            && $this->user_id !== null
            && $this->user_id === $user->id;
    }

    public function canPreviewPublicLink(?User $user): bool
    {
        if ($user === null) {
            return false;
        }

        return $user->is_admin || $this->isOwnedBy($user);
    }

    public function canBeViewedBy(?User $user): bool
    {
        if ($this->is_active) {
            return true;
        }

        return $this->canPreviewPublicLink($user);
    }

    public function isWeddingDay(): bool
    {
        return now()->isSameDay($this->wedding_date);
    }

    public function hasEnded(): bool
    {
        return now()->greaterThan($this->wedding_date->copy()->endOfDay());
    }

    public function acceptsRsvps(): bool
    {
        if ($this->hasEnded()) {
            return false;
        }

        if ($this->rsvp_deadline !== null && now()->startOfDay()->greaterThan($this->rsvp_deadline->copy()->endOfDay())) {
            return false;
        }

        return true;
    }

    public function getHeroImageUrlAttribute(): ?string
    {
        return MediaDisk::url($this->hero_image);
    }

    public function getYoutubeVideoIdAttribute(): ?string
    {
        if (! $this->music_url) {
            return null;
        }

        $url = $this->music_url;

        if (preg_match('#youtube\.com/embed/([a-zA-Z0-9_-]{11})#', $url, $matches)) {
            return $matches[1];
        }

        if (preg_match('#youtu\.be/([a-zA-Z0-9_-]{11})#', $url, $matches)) {
            return $matches[1];
        }

        if (preg_match('#[?&]v=([a-zA-Z0-9_-]{11})#', $url, $matches)) {
            return $matches[1];
        }

        return null;
    }

    public function getYoutubeStartSecondsAttribute(): ?int
    {
        if (! $this->music_url) {
            return null;
        }

        $query = parse_url($this->music_url, PHP_URL_QUERY);

        if (! is_string($query) || $query === '') {
            return null;
        }

        parse_str($query, $params);

        if (! isset($params['t'])) {
            return null;
        }

        return $this->parseYoutubeTimestampParam((string) $params['t']);
    }

    public function getYoutubeEmbedUrlAttribute(): ?string
    {
        $videoId = $this->youtube_video_id;

        if (! $videoId) {
            return null;
        }

        $url = "https://www.youtube.com/embed/{$videoId}?autoplay=0&controls=1&rel=0&modestbranding=1&fs=0&iv_load_policy=3";

        if ($this->youtube_start_seconds) {
            $url .= '&start='.$this->youtube_start_seconds;
        }

        return $url;
    }

    private function parseYoutubeTimestampParam(string $value): ?int
    {
        if ($value === '') {
            return null;
        }

        if (ctype_digit($value)) {
            $seconds = (int) $value;

            return $seconds > 0 ? $seconds : null;
        }

        if (preg_match('/^(\d+)s$/', $value, $matches)) {
            $seconds = (int) $matches[1];

            return $seconds > 0 ? $seconds : null;
        }

        $seconds = 0;
        $hasMatch = false;

        if (preg_match_all('/(\d+)([hms])/', $value, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $hasMatch = true;
                $amount = (int) $match[1];

                match ($match[2]) {
                    'h' => $seconds += $amount * 3600,
                    'm' => $seconds += $amount * 60,
                    's' => $seconds += $amount,
                };
            }
        }

        if (! $hasMatch) {
            return null;
        }

        return $seconds > 0 ? $seconds : null;
    }
}
