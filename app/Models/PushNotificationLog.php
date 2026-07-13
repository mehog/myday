<?php

namespace App\Models;

use App\PushNotificationRecipientType;
use App\PushNotificationStatus;
use App\Services\WeddingScheduledNotificationService;
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
        'guest_ids',
        'status',
        'failed_reason',
        'sent_at',
        'scheduled_at',
    ];

    protected function casts(): array
    {
        return [
            'recipient_type' => PushNotificationRecipientType::class,
            'status' => PushNotificationStatus::class,
            'guest_ids' => 'array',
            'sent_at' => 'datetime',
            'scheduled_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::deleting(function (PushNotificationLog $log): void {
            if ($log->status === PushNotificationStatus::Scheduled) {
                app(WeddingScheduledNotificationService::class)->cancelScheduledPush($log, markCancelled: false);
            }
        });
    }

    public function weddingEvent(): BelongsTo
    {
        return $this->belongsTo(WeddingEvent::class);
    }
}
