<?php

namespace App\Models;

use App\Traits\Referrable;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use NotificationChannels\WebPush\HasPushSubscriptions;

#[Fillable(['name', 'email', 'password', 'is_admin', 'locale', 'referral_fee_percentage', 'paypal_email', 'bank_account_info'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser, HasAvatar, MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasPushSubscriptions, Notifiable, Referrable;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'referral_fee_percentage' => 'decimal:2',
        ];
    }

    public function weddingEvent(): HasOne
    {
        return $this->hasOne(WeddingEvent::class);
    }

    public function referralPayouts(): HasMany
    {
        return $this->hasMany(ReferralPayout::class, 'referrer_id');
    }

    public function referralFeePercentage(): float
    {
        return (float) ($this->referral_fee_percentage ?? config('referral.default_fee', 10));
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return match ($panel->getId()) {
            'admin' => $this->is_admin,
            'app' => ! $this->is_admin,
            default => false,
        };
    }

    public function getFilamentAvatarUrl(): ?string
    {
        return 'https://ui-avatars.com/api/?name='.urlencode($this->name).'&background=f43f5e&color=fff&size=128';
    }
}
