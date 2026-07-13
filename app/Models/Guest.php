<?php

namespace App\Models;

use App\InvitePlatform;
use App\LinkType;
use App\RsvpStatus;
use Database\Factories\GuestFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use NotificationChannels\WebPush\HasPushSubscriptions;
use Thomasjohnkane\Snooze\Traits\SnoozeNotifiable;

class Guest extends Model
{
    /** @use HasFactory<GuestFactory> */
    use HasFactory, HasPushSubscriptions, Notifiable, SnoozeNotifiable, SoftDeletes;

    protected $fillable = [
        'wedding_event_id',
        'name',
        'email',
        'phone',
        'plus_one_allowed',
        'plus_one_name',
        'token',
        'rsvp_status',
        'rsvp_responded_at',
        'rsvp_manual_override',
        'rsvp_note',
        'invite_sent_at',
        'invite_platform',
    ];

    protected function casts(): array
    {
        return [
            'rsvp_status' => RsvpStatus::class,
            'rsvp_responded_at' => 'datetime',
            'rsvp_manual_override' => 'boolean',
            'invite_sent_at' => 'datetime',
            'invite_platform' => InvitePlatform::class,
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Guest $guest) {
            if (empty($guest->token)) {
                $guest->token = Str::random(32);
            }
        });
    }

    public function weddingEvent(): BelongsTo
    {
        return $this->belongsTo(WeddingEvent::class);
    }

    public function linkVisits(): HasMany
    {
        return $this->hasMany(LinkVisit::class);
    }

    public function latestPersonalLinkVisit(): HasOne
    {
        return $this->hasOne(LinkVisit::class)
            ->where('link_type', LinkType::Personal->value)
            ->latestOfMany('visited_at');
    }

    public function guestMessages(): HasMany
    {
        return $this->hasMany(GuestMessage::class)->latest();
    }

    public function hasResponded(): bool
    {
        return $this->rsvp_status !== null;
    }

    public function getPersonalUrlAttribute(): string
    {
        return $this->weddingEvent->guestUrl($this);
    }

    public function routeNotificationForMail(): ?string
    {
        return $this->email;
    }
}
