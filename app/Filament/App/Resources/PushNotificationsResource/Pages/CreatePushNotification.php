<?php

namespace App\Filament\App\Resources\PushNotificationsResource\Pages;

use App\Filament\App\Resources\PushNotificationsResource;
use App\Jobs\SendGuestPushNotificationsJob;
use App\Models\Guest;
use App\Models\PushNotificationLog;
use App\PushNotificationRecipientType;
use App\PushNotificationStatus;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
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

        $log = PushNotificationLog::query()->create([
            'wedding_event_id' => $weddingEvent->id,
            'title' => $data['title'],
            'body' => $data['body'],
            'recipient_type' => $data['recipient_type'],
            'sent_to_count' => $guests->count(),
            'status' => PushNotificationStatus::Queued,
        ]);

        SendGuestPushNotificationsJob::dispatch(
            logId: $log->id,
            guestIds: $guests->pluck('id')->all(),
            title: $data['title'],
            body: $data['body'],
            url: $weddingEvent->public_url,
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

        if ($data['recipient_type'] === PushNotificationRecipientType::Selected->value) {
            $query->whereIn('id', $data['selected_guest_ids'] ?? []);
        }

        return $query->get();
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return __('app.push_notifications_queued_success');
    }
}
