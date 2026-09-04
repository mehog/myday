<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class WeddingPartnerInvite extends Model
{
    public const EXPIRY_DAYS = 14;

    protected $fillable = [
        'wedding_event_id',
        'invited_by_user_id',
        'email',
        'token',
        'expires_at',
        'accepted_at',
        'accepted_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'accepted_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $invite): void {
            if (! filled($invite->token)) {
                $invite->token = Str::random(64);
            }

            if ($invite->expires_at === null) {
                $invite->expires_at = now()->addDays(self::EXPIRY_DAYS);
            }
        });
    }

    public function weddingEvent(): BelongsTo
    {
        return $this->belongsTo(WeddingEvent::class);
    }

    public function invitedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by_user_id');
    }

    public function acceptedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'accepted_by_user_id');
    }

    public function isPending(): bool
    {
        return $this->accepted_at === null;
    }

    public function isExpired(): bool
    {
        return $this->isPending() && $this->expires_at->isPast();
    }

    public function isUsable(): bool
    {
        return $this->isPending() && ! $this->isExpired();
    }

    public function acceptUrl(): string
    {
        return route('partner-invite.show', ['token' => $this->token]);
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->whereNull('accepted_at');
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeUsable(Builder $query): Builder
    {
        return $query
            ->pending()
            ->where('expires_at', '>', now());
    }
}
