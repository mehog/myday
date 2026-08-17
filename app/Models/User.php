<?php

namespace App\Models;

use App\PricingRegion;
use App\Support\Locale;
use App\Traits\Referrable;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Contracts\Translation\HasLocalePreference;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use NotificationChannels\WebPush\HasPushSubscriptions;
use NotificationChannels\WebPush\PushSubscription;
use Rappasoft\LaravelAuthenticationLog\Traits\AuthenticationLoggable;
use Thomasjohnkane\Snooze\Traits\SnoozeNotifiable;

#[Fillable(['name', 'email', 'password', 'is_admin', 'locale', 'signup_ipstack', 'signup_ip', 'referral_fee_percentage', 'paypal_email', 'bank_account_info'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser, HasAvatar, HasLocalePreference, MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use AuthenticationLoggable, HasFactory, HasPushSubscriptions, Notifiable, Referrable, SnoozeNotifiable;

    /**
     * Countries that receive third-world (BAM) pricing. Everyone else pays in EUR.
     *
     * @var list<string>
     */
    public const THIRD_WORLD_COUNTRIES = ['BA'];

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
            'signup_ipstack' => 'object',
        ];
    }

    public function weddingEvent(): HasOne
    {
        return $this->hasOne(WeddingEvent::class);
    }

    public function dodoPayments(): HasMany
    {
        return $this->hasMany(DodoPayment::class);
    }

    public function referralPayouts(): HasMany
    {
        return $this->hasMany(ReferralPayout::class, 'referrer_id');
    }

    public function discountEmailRecipients(): HasMany
    {
        return $this->hasMany(DiscountEmailRecipient::class);
    }

    public function referralFeePercentage(): float
    {
        return (float) ($this->referral_fee_percentage ?? config('referral.default_fee', 10));
    }

    public function signupCountryCode(): ?string
    {
        $code = $this->signup_ipstack->country_code ?? null;

        return is_string($code) && $code !== '' ? strtoupper($code) : null;
    }

    public function isFromThirdWorldCountry(): bool
    {
        $code = $this->signupCountryCode();

        return $code !== null && in_array($code, self::THIRD_WORLD_COUNTRIES, true);
    }

    public function isFromFirstWorldCountry(): bool
    {
        return ! $this->isFromThirdWorldCountry();
    }

    public function pricingRegion(): PricingRegion
    {
        return $this->isFromFirstWorldCountry()
            ? PricingRegion::FirstWorld
            : PricingRegion::ThirdWorld;
    }

    public function pricingCurrency(): string
    {
        return $this->pricingRegion()->currency();
    }

    public function hasPaidPlan(): bool
    {
        return $this->weddingEvent?->hasPaidPlan() ?? false;
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

    public function preferredLocale(): string
    {
        return Locale::resolve($this->locale);
    }

    public function ownsDemoInvitation(): bool
    {
        return $this->weddingEvent?->suppressesOutboundMail() === true;
    }

    public function ownsPushSubscription(PushSubscription $subscription): bool
    {
        return $subscription->subscribable_type === $this->getMorphClass()
            && (int) $subscription->subscribable_id === (int) $this->getKey();
    }

    /**
     * Admin-only login audit — never notify users via this package.
     *
     * @return list<string>
     */
    public function notifyAuthenticationLogVia(): array
    {
        return [];
    }
}
