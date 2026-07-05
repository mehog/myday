<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Referral extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'referrer_id',
        'referral_code',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function referrer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }

    public static function userByReferralCode(string $code): ?User
    {
        $referral = self::query()->where('referral_code', $code)->first();

        if ($referral === null) {
            return null;
        }

        return User::query()->find($referral->user_id);
    }
}
