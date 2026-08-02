<?php

namespace App\Models;

use App\DiscountType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class DiscountCode extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'code',
        'name',
        'type',
        'amount',
        'currency',
        'dodo_discount_id',
        'starts_at',
        'expires_at',
        'is_active',
        'notes',
        'created_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => DiscountType::class,
            'amount' => 'decimal:2',
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (DiscountCode $code): void {
            $code->code = strtoupper(trim($code->code));
        });
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function campaigns(): HasMany
    {
        return $this->hasMany(DiscountEmailCampaign::class);
    }

    public function recipients(): HasManyThrough
    {
        return $this->hasManyThrough(
            DiscountEmailRecipient::class,
            DiscountEmailCampaign::class,
            'discount_code_id',
            'campaign_id',
        );
    }

    public function discountLabel(): string
    {
        return match ($this->type) {
            DiscountType::Percentage => rtrim(rtrim(number_format((float) $this->amount, 2, '.', ''), '0'), '.').'%',
            DiscountType::Flat => trim(rtrim(rtrim(number_format((float) $this->amount, 2, '.', ''), '0'), '.').' '.($this->currency ?? '')),
        };
    }

    public function expiresLabel(): string
    {
        if ($this->expires_at === null) {
            return __('discounts.expires_never');
        }

        return $this->expires_at->timezone(config('app.timezone'))->format('Y-m-d');
    }
}
