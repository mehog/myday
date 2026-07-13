<?php

namespace App\Filament\App\Resources\PushNotificationsResource\Pages;

use App\Filament\App\Resources\PushNotificationsResource;
use App\Jobs\SendGuestPushNotificationsJob;
use App\Models\Guest;
use App\Models\PushNotificationLog;
use App\PushNotificationRecipientType;
use App\PushNotificationStatus;
use App\Services\WeddingScheduledNotificationService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class CreatePushNotification extends CreateRecord
{
    protected static string $resource = PushNotificationsResource::class;

    protected function getRedirectUrl(): string
    {
        return PushNotificationsResource::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        unset($data['subscriber_count']);

        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        $weddingEvent = auth()->user()?->weddingEvent;

        if (! $weddingEvent) {
            Notification::make()
                ->title(__('app.push_notifications_no_wedding'))
                ->danger()
                ->send();

            $this->halt();
        }

        $guests = $this->resolveRecipients($weddingEvent->id, $data);

        if ($guests->isEmpty()) {
            Notification::make()
                ->title(__('app.push_notifications_no_subscribers'))
                ->warning()
                ->send();

            $this->halt();
        }

        $scheduledAt = filled($data['scheduled_at'] ?? null)
            ? Carbon::parse($data['scheduled_at'])
            : null;

        $log = PushNotificationLog::query()->create([
            'wedding_event_id' => $weddingEvent->id,
            'title' => $data['title'],
            'body' => $data['body'],
            'recipient_type' => $data['recipient_type'],
            'sent_to_count' => $guests->count(),
            'guest_ids' => $guests->pluck('id')->all(),
            'status' => PushNotificationStatus::Queued,
            'scheduled_at' => $scheduledAt,
        ]);

        $guestIds = $guests->pluck('id')->all();
        $user = auth()->user();

        if ($scheduledAt !== null && $scheduledAt->isFuture() && $user !== null) {
            app(WeddingScheduledNotificationService::class)->scheduleGuestPush(
                log: $log,
                user: $user,
                sendAt: $scheduledAt,
                guestIds: $guestIds,
            );

            return $log;
        }

        SendGuestPushNotificationsJob::dispatch(
            logId: $log->id,
            guestIds: $guestIds,
            title: $data['title'],
            body: $data['body'],
        );

        return $log;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return Collection<int, Guest>
     */
    protected function resolveRecipients(int $weddingEventId, array $data): Collection
    {
        $query = Guest::query()
            ->where('wedding_event_id', $weddingEventId)
            ->whereHas('pushSubscriptions');

        if ($data['recipient_type'] === PushNotificationRecipientType::Unanswered->value) {
            $query->whereNull('rsvp_status');
        }

        if ($data['recipient_type'] === PushNotificationRecipientType::Selected->value) {
            $query->whereIn('id', $data['selected_guest_ids'] ?? []);
        }

        return $query->get();
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        $scheduledAt = $this->data['scheduled_at'] ?? null;

        if (filled($scheduledAt) && Carbon::parse($scheduledAt)->isFuture()) {
            return __('app.push_notifications_scheduled_success');
        }

        return __('app.push_notifications_queued_success');
    }
}
