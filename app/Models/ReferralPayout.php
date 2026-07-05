<?php

namespace App\Models;

use App\ReferralPayoutStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ReferralPayout extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'referrer_id',
        'amount',
        'currency',
        'period',
        'status',
        'paid_at',
        'payment_proof',
        'payment_link',
        'notes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'status' => ReferralPayoutStatus::class,
            'paid_at' => 'datetime',
        ];
    }

    public function referrer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', ReferralPayoutStatus::Pending);
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopePaid(Builder $query): Builder
    {
        return $query->where('status', ReferralPayoutStatus::Paid);
    }

    public function paymentProofUrl(): ?string
    {
        if ($this->payment_proof === null) {
            return null;
        }

        return Storage::disk(config('filesystems.media_disk'))->url($this->payment_proof);
    }
}
