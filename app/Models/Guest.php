<?php

namespace App\Models;

use App\InvitePlatform;
use App\RsvpStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Guest extends Model
{
    use HasFactory;

    protected $fillable = [
        'wedding_event_id',
        'name',
        'email',
        'phone',
        'token',
        'rsvp_status',
        'rsvp_responded_at',
        'invite_sent_at',
        'invite_platform',
    ];

    protected function casts(): array
    {
        return [
            'rsvp_status' => RsvpStatus::class,
            'rsvp_responded_at' => 'datetime',
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

    public function hasResponded(): bool
    {
        return $this->rsvp_status !== null;
    }

    public function getPersonalUrlAttribute(): string
    {
        return $this->weddingEvent->guestUrl($this);
    }
}
