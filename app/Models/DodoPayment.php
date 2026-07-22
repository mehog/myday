<?php

namespace App\Models;

use App\DodoPaymentStatus;
use App\PlanTier;
use App\PricingRegion;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DodoPayment extends Model
{
    protected $fillable = [
        'user_id',
        'wedding_event_id',
        'plan_tier',
        'pricing_region',
        'currency',
        'amount',
        'status',
        'dodo_product_id',
        'dodo_payment_id',
        'dodo_checkout_session_id',
        'dodo_customer_id',
        'metadata',
        'payload',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'plan_tier' => PlanTier::class,
            'pricing_region' => PricingRegion::class,
            'status' => DodoPaymentStatus::class,
            'amount' => 'integer',
            'metadata' => 'array',
            'payload' => 'array',
            'paid_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function weddingEvent(): BelongsTo
    {
        return $this->belongsTo(WeddingEvent::class);
    }
}
