<?php

namespace App\Traits;

use App\Models\Referral;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

trait Referrable
{
    public function referrals(): HasMany
    {
        return $this->hasMany(Referral::class, 'referrer_id');
    }

    public function referralAccount(): HasOne
    {
        return $this->hasOne(Referral::class, 'user_id');
    }

    public function hasReferralAccount(): bool
    {
        return $this->referralAccount !== null;
    }

    public function getReferralLink(): string
    {
        if (! $this->hasReferralAccount()) {
            return '';
        }

        return url('/'.config('referral.route_prefix').'/'.$this->getReferralCode());
    }

    public function getReferralCode(): ?string
    {
        return $this->referralAccount?->referral_code;
    }

    public function createReferralAccount(?int $referrerId = null): void
    {
        if ($this->hasReferralAccount()) {
            return;
        }

        $prefix = (string) config('referral.ref_code_prefix', '_');
        $length = max(1, (int) config('referral.referral_length', 8));

        Referral::query()->create([
            'user_id' => $this->getKey(),
            'referrer_id' => $referrerId,
            'referral_code' => $this->generateUniqueReferralCode($prefix, $length),
        ]);
    }

    public function resetReferralCode(): string
    {
        if (! $this->hasReferralAccount()) {
            $this->createReferralAccount();
        }

        $prefix = (string) config('referral.ref_code_prefix', '_');
        $length = max(1, (int) config('referral.referral_length', 8));
        $code = $this->generateUniqueReferralCode($prefix, $length);

        return $this->setReferralCode($code);
    }

    public function setReferralCode(string $code): string
    {
        $code = self::normalizeReferralCode($code);

        if ($code === '') {
            throw new \InvalidArgumentException(__('referrals.admin_referral_code_required'));
        }

        $query = Referral::query()->where('referral_code', $code);

        if ($this->hasReferralAccount()) {
            $query->whereKeyNot($this->referralAccount->getKey());
        }

        if ($query->exists()) {
            throw new \InvalidArgumentException(__('referrals.admin_referral_code_taken'));
        }

        if (! self::isValidReferralCodeFormat($code)) {
            throw new \InvalidArgumentException(__('referrals.admin_referral_code_invalid'));
        }

        if (! $this->hasReferralAccount()) {
            Referral::query()->create([
                'user_id' => $this->getKey(),
                'referrer_id' => null,
                'referral_code' => $code,
            ]);
        } else {
            $this->referralAccount()->update(['referral_code' => $code]);
        }

        $this->unsetRelation('referralAccount');

        return $code;
    }

    public static function normalizeReferralCode(string $code): string
    {
        return strtolower(trim($code));
    }

    public static function isValidReferralCodeFormat(string $code): bool
    {
        return (bool) preg_match('/^[a-z0-9_-]{3,50}$/', $code);
    }

    private function generateUniqueReferralCode(string $prefix, int $length): string
    {
        $prefix = strtolower($prefix);

        do {
            $code = $prefix.strtolower(Str::random($length));
        } while (Referral::query()->where('referral_code', $code)->exists());

        return $code;
    }
}
