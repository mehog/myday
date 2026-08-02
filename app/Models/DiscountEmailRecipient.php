<?php

namespace App\Models;

use App\DiscountEmailRecipientStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiscountEmailRecipient extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'campaign_id',
        'user_id',
        'email',
        'locale',
        'status',
        'sent_at',
        'error',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => DiscountEmailRecipientStatus::class,
            'sent_at' => 'datetime',
        ];
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(DiscountEmailCampaign::class, 'campaign_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
