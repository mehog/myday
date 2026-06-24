<?php

namespace App\Models;

use App\PushNotificationRecipientType;
use App\PushNotificationStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PushNotificationLog extends Model
{
    protected $fillable = [
        'wedding_event_id',
        'title',
        'body',
        'recipient_type',
        'sent_to_count',
        'status',
        'failed_reason',
        'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'recipient_type' => PushNotificationRecipientType::class,
            'status' => PushNotificationStatus::class,
            'sent_at' => 'datetime',
        ];
    }

    public function weddingEvent(): BelongsTo
    {
        return $this->belongsTo(WeddingEvent::class);
    }
}
