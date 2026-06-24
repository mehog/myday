<?php

namespace App\Jobs;

use App\Models\Guest;
use App\Models\PushNotificationLog;
use App\Notifications\GuestPushNotification;
use App\PushNotificationStatus;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Notification;
use Throwable;

class SendGuestPushNotificationsJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /**
     * @param  array<int>  $guestIds
     */
    public function __construct(
        public int $logId,
        public array $guestIds,
        public string $title,
        public string $body,
        public ?string $url,
    ) {}

    public function handle(): void
    {
        $log = PushNotificationLog::query()->findOrFail($this->logId);

        $guests = Guest::query()
            ->whereIn('id', $this->guestIds)
            ->whereHas('pushSubscriptions')
            ->get();

        Notification::send($guests, new GuestPushNotification(
            title: $this->title,
            body: $this->body,
            url: $this->url,
        ));

        $log->update([
            'status' => PushNotificationStatus::Sent,
            'sent_at' => now(),
        ]);
    }

    public function failed(?Throwable $exception): void
    {
        PushNotificationLog::query()
            ->whereKey($this->logId)
            ->update([
                'status' => PushNotificationStatus::Failed,
                'failed_reason' => $exception?->getMessage(),
            ]);
    }
}
